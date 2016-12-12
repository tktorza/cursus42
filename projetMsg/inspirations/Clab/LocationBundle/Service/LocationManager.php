<?php

namespace Clab\LocationBundle\Service;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Response;
use Ivory\GoogleMap\Services\Geocoding\Geocoder;
use Ivory\GoogleMap\Services\Geocoding\GeocoderProvider;
use Geocoder\HttpAdapter\CurlHttpAdapter;

use Clab\ApiBundle\Entity\Session;
use Clab\LocationBundle\Entity\Address;

class LocationManager
{
    protected $container;
    protected $em;
    protected $geocoder;

    public function __construct(ContainerInterface $container, EntityManager $em)
    {
        $this->container = $container;
        $this->em = $em;

        $this->geocoder = new Geocoder();
        $this->geocoder->registerProviders(array(
            new GeocoderProvider(new CurlHttpAdapter()),
        ));
    }

    public function getGeocoder()
    {
        return $this->geocoder;
    }

    public function search($query)
    {
        $response = $this->geocoder->geocode($query . ', France');

        if ($response->getStatus() == 'OK') {
            $results = $response->getResults();
        } else {
            $results = array();
        }

        $addresses = array();
        foreach ($results as $result) {
            $addresses[] = array('value' => $result->getFormattedAddress());
        }

        return $addresses;
    }

    public function getCoordinateFromAddress($address)
    {
        if (is_object($address)) {
            $address = $address->verbose();
        }

        $response = $this->geocoder->geocode($address);

        $error = false;

        if ($response->getStatus() == 'OK') {
            $results = $response->getResults();
            $location = $results[0];
        } else {
            $error = true;
        }

        if (isset($location) && $location) {
            $coordinates = $location->getGeometry()->getLocation();

            $latitude = $coordinates->getLatitude();
            $longitude = $coordinates->getLongitude();
        } else {
            $error = true;
        }

        if ($error) {
            return null;
        } else {
            return array('latitude' => $latitude, 'longitude' => $longitude);
        }
    }

    public function updateAddressCoordinates($address)
    {
        $response = $this->geocoder->geocode($address->verbose());

        $error = false;

        if ($response->getStatus() == 'OK') {
            $results = $response->getResults();
            $location = $results[0];
        } else {
            $error = true;
        }

        if (isset($location) && $location) {
            $coordinates = $location->getGeometry()->getLocation();

            $latitude = $coordinates->getLatitude();
            $longitude = $coordinates->getLongitude();

            $address->setLatitude($latitude);
            $address->setLongitude($longitude);

            $this->em->flush();

            return array('latitude' => $latitude, 'longitude' => $longitude);
        } else {
            return new Response('ko');
        }
    }

    public function updateCoordinates($address)
    {
        $response = $this->geocoder->geocode($address->getStreet() . ' ' . $address->getZip() . ' ' . $address->getCity());

        if ($response->getStatus() == 'OK') {
            $results = $response->getResults();
            $location = $results[0];
        }

        if (isset($location) && $location) {
            $coordinates = $location->getGeometry()->getLocation();

            $latitude = $coordinates->getLatitude();
            $longitude = $coordinates->getLongitude();

            $address->setLatitude($latitude);
            $address->setLongitude($longitude);
        }

        $this->em->flush();
    }

    public function getMapFromCoordinate($latitude, $longitude)
    {
        $map = $this->container->get('ivory_google_map.map');

        // Configure your map options
        $map->setPrefixJavascriptVariable('map_');
        $map->setHtmlContainerId('map_canvas');

        $map->setAsync(false);

        $map->setAutoZoom(true);

        $map->setCenter($latitude, $longitude, true);
        $map->setMapOption('zoom', 12);

        $marker = $this->container->get('ivory_google_map.marker');
        $marker->setPrefixJavascriptVariable('marker_');
        $marker->setPosition($latitude, $longitude, true);

        $map->addMarker($marker);

        $map->setStylesheetOption('width', '300px');
        $map->setStylesheetOption('height', '300px');

        $map->setLanguage('fr');

        return $map;
    }

    public function transformAddress($query)
    {
        $address = new Address();

        $response = $this->geocoder->geocode($query);

        if ($response && $response->getResults() && count($response->getResults()) > 0) {
            $location = $response->getResults()[0];
            $street = '';

            foreach ($location->getAddressComponents() as $component) {
                if (in_array('street_number', $component->getTypes())) {
                    $street .= $component->getLongName() . ' ';
                }

                if (in_array('route', $component->getTypes())) {
                    $street .= $component->getLongName();
                    $address->setStreet($street);
                }

                if (in_array('postal_code', $component->getTypes())) {
                    $address->setZip($component->getLongName());
                }

                if (in_array('locality', $component->getTypes())) {
                    $address->setCity($component->getLongName());
                }
            }
        }

        return $address;
    }

    public function reverseAddress($latitude, $longitude)
    {
        $address = new Address();

        $response = $this->container->get('ivory_google_map.geocoder')->reverse($latitude, $longitude);

        if ($response && $response->getResults() && count($response->getResults()) > 0) {
            $location = $response->getResults()[0];
            $street = '';

            foreach ($location->getAddressComponents() as $component) {
                if (in_array('street_number', $component->getTypes())) {
                    $street .= $component->getLongName() . ' ';
                }

                if (in_array('route', $component->getTypes())) {
                    $street .= $component->getLongName();
                    $address->setStreet($street);
                }

                if (in_array('postal_code', $component->getTypes())) {
                    $address->setZip($component->getLongName());
                }

                if (in_array('locality', $component->getTypes())) {
                    $address->setCity($component->getLongName());
                }
            }
        }

        return $address;
    }
}
