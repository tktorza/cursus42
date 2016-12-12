<?php

namespace Clab\RestaurantBundle\Repository;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\EntityRepository;
use Clab\RestaurantBundle\Entity\Restaurant;

class RestaurantRepository extends EntityRepository
{
    public function findOneActive($slug)
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->where('restaurant.slug = :slug')
            ->andWhere('restaurant.status >= :activeStatus')
            ->setParameter('slug', $slug)
            ->setParameter('activeStatus', Restaurant::STORE_STATUS_ACTIVE)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function findFiveStared($latitude, $longitude)
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->select('restaurant', 'address', 'GEO_DISTANCE(:latitude, :longitude, address.latitude, address.longitude) AS distance')
            ->having('distance < :distanceMax')
            ->leftJoin('restaurant.address', 'address')
            ->where('restaurant.isPromoted = :true')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('true', true)
            ->setParameter('distanceMax', '2')
            ->andWhere('restaurant.isMobile = false')
            ->orderBy('distance')
            ->setMaxResults('5');

        return $qb->getQuery()->getResult();
    }

    public function findByStatus($min, $max)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r', 'address', 'deal', 'gallery', 'images', 'tags')
            ->where('r.status >= :min')
            ->andWhere('r.status <= :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->leftJoin('r.address', 'address')
            ->leftJoin('r.deal', 'deal')
            ->leftJoin('r.gallery', 'gallery')
            ->leftJoin('gallery.images', 'images')
            ->leftJoin('r.tags', 'tags')
            ->orderBy('r.name', 'asc')
        ;

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    public function findLightByStatus($min, $max, array $parameters = array())
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r.id', 'r.name', 'r.slug')
            ->where('r.status >= :min')
            ->andWhere('r.status <= :max')
            ->setParameter('min', $min)
            ->setParameter('max', $max)
            ->orderBy('r.name', 'asc')
        ;

        if (isset($parameters['address']) && $parameters['address']) {
            $qb->addSelect('address.street', 'address.zip', 'address.city', 'address.latitude', 'address.longitude')
                ->leftJoin('r.address', 'address');
        }

        if (isset($parameters['status']) && $parameters['status']) {
            $qb->addSelect('r.status');
        }

        if (isset($parameters['search']) && $parameters['search']) {
            $qb->andWhere('r.name LIKE :search')
                ->setParameter('search', '%'.$parameters['search'].'%');
        }

        $results = $qb->getQuery()->getResult();

        return $results;
    }

    // API
    public function findAllFiltered($options = array())
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->select('restaurant');

        if (isset($options['status_min']) && isset($options['status_max'])) {
            $qb
                ->andWhere('restaurant.status >= :min')
                ->andWhere('restaurant.status <= :max')
                ->setParameter('min', $options['status_min'])
                ->setParameter('max', $options['status_max'])
            ;
        }

        if (isset($options['service'])) {
            switch ($options['service']) {
                case 'clickeat':
                    $qb->andWhere('restaurant.isClickeat = true');
                    break;
                case 'tttruck':
                    $qb->andWhere('restaurant.isTtt = true');
                    break;
                default:
                    break;
            }
        }

        return $qb->getQuery()->getResult();
    }

    public function findNearby($latitude, $longitude, $distanceMin = 0, $distanceMax = 1, $options = array())
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->select('restaurant', 'address', 'tags', 'subscriptions', 'GEO_DISTANCE(:latitude, :longitude, address.latitude, address.longitude) AS distance')
            ->having('distance >= :distanceMin AND distance < :distanceMax')
            ->leftJoin('restaurant.address', 'address')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('distanceMin', $distanceMin)
            ->setParameter('distanceMax', $distanceMax)
            ->andWhere('restaurant.isMobile = false')
            ->orderBy('distance');

        if (isset($options['status_min']) && isset($options['status_max'])) {
            $qb
                ->andWhere('restaurant.status >= :min')
                ->andWhere('restaurant.status <= :max')
                ->setParameter('min', $options['status_min'])
                ->setParameter('max', $options['status_max'])
            ;
        }

        if (isset($options['service'])) {
            switch ($options['service']) {
                case 'clickeat':
                    $qb->andWhere('restaurant.isClickeat = true');
                    break;
                case 'tttruck':
                    $qb->andWhere('restaurant.isTtt = true');
                    break;
                default:
                    break;
            }
        }

        $qb
            ->leftJoin('restaurant.tags', 'tags')
        ;

        return $qb->getQuery()->getResult();
    }

    public function findNearbyPaginated($latitude, $longitude, $options = array())
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->select('restaurant', 'address',
                'GEO_DISTANCE(:latitude, :longitude, address.latitude, address.longitude) AS distance')
            ->having('distance < 2')
            ->leftJoin('restaurant.address', 'address')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->andWhere('restaurant.isMobile = false')
            ->addOrderBy('distance', 'asc')
            ->andWhere('restaurant.flatTimeSheet IS NOT NULL');

        if (isset($options['status_min']) && isset($options['status_max'])) {
            $qb
                ->andWhere('restaurant.status >= :min')
                ->andWhere('restaurant.status <= :max')
                ->setParameter('min', $options['status_min'])
                ->setParameter('max', $options['status_max']);
        }

        if (isset($options['service'])) {
            switch ($options['service']) {
                case 'clickeat':
                    $qb
                        ->andWhere('restaurant.isClickeat = true');
                    break;
                case 'tttruck':
                    $qb->andWhere('restaurant.isTtt = true');
                    break;
                case 'clickspot':
                    $qb->andWhere('restaurant.isTtt = false');
                    break;
                default:
                    break;
            }
        }

        if (isset($options['promoted']) && $options['promoted']) {
            $qb->andWhere('restaurant.is_promoted = 1');
        }

        if (isset($options['limit'])) {
            $qb
                ->setMaxResults($options['limit']);
        }

        if (isset($options['offset'])) {
            $qb
                ->setFirstResult($options['offset']);
        }

        if (isset($options['categories'])) {
            $qb
                ->leftJoin('restaurant.tags', 'tag')
                ->andWhere('REGEXP(tag.slug,:categories) = 1')
                ->setParameter('categories', $options['categories'])
            ;
        }
        if (isset($options['regimes'])) {
            $qb
                ->leftJoin('restaurant.extraTags', 'extraTag')
                ->andWhere('REGEXP(extraTag.slug,:regimes) = 1')
                ->setParameter('regimes', $options['regimes'])
            ;
        }
        if (isset($options['type'])) {
            if ('delivery' == $options['type']) {
                $qb->andWhere('restaurant.isOpenDelivery = true');
            }
        }

        return $qb->getQuery()->getResult(AbstractQuery::HYDRATE_ARRAY);
    }

    public function findNearbyPaginatedWithTag($latitude, $longitude, $tag = null, $options = array())
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->select('restaurant', 'address', 'GEO_DISTANCE(:latitude, :longitude, address.latitude, address.longitude) AS distance')
            ->having('distance < 2')
            ->leftJoin('restaurant.tags', 'tags')
            ->leftJoin('restaurant.address', 'address')
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->andWhere('restaurant.isMobile = false')

            ->addOrderBy('distance', 'asc');

        if (!is_null($tag)) {
            $qb
                ->andWhere('tags = :t')
                ->setParameter('t', $tag)
            ;
        }

        if (isset($options['status_min']) && isset($options['status_max'])) {
            $qb
                ->andWhere('restaurant.status >= :min')
                ->andWhere('restaurant.status <= :max')
                ->setParameter('min', $options['status_min'])
                ->setParameter('max', $options['status_max'])
            ;
        }
        
        
        if (isset($options['type'])) {
            if ('delivery' == $options['type']) {
                $qb->andWhere('restaurant.isOpenDelivery = true');
            }
        }

        if (isset($options['service'])) {
            switch ($options['service']) {
                case 'clickeat':
                    $qb
                        ->andWhere('restaurant.isClickeat = true')
                        ->andWhere('restaurant.isOpen = true')
                    ;
                    break;
                case 'tttruck':
                    $qb->andWhere('restaurant.isTtt = true');
                    break;
                default:
                    break;
            }
        }

        if (isset($options['categories']) && is_array($options['categories'])) {
            $qb
                ->innerJoin('restaurant.tags', 'tags')
                ->andWhere('tags.slug IN(:categories)')
                ->setParameter('categories', $options['categories'])
            ;
        }

        if (isset($options['extraCategories']) && is_array($options['extraCategories'])) {
            $qb
                ->innerJoin('restaurant.extraTags', 'extraTags')
                ->andWhere('extraTags.slug IN(:extraCategories)')
                ->setParameter('extraCategories', $options['extraCategories'])
            ;
        }

        if (isset($options['discountOnly']) && $options['discountOnly']) {
            $qb
                ->innerJoin('restaurant.discounts', 'discounts')
                ->andWhere('discounts.is_online = 1')
                ->andWhere('discounts.is_deleted = 0')
            ;
        }

        if (isset($options['promoted']) && $options['promoted']) {
            $qb->andWhere('restaurant.is_promoted = 1');
        }

        return $qb->getQuery()->getResult();
    }

    public function findDelivery($options = array())
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->select('restaurant', 'address', 'tags', 'subscriptions')
            ->leftJoin('restaurant.address', 'address')
            ->andWhere('restaurant.isOpenDelivery = true');
        //@todo meilleur filtre

        if (isset($options['status_min']) && isset($options['status_max'])) {
            $qb
                ->andWhere('restaurant.status >= :min')
                ->andWhere('restaurant.status <= :max')
                ->setParameter('min', $options['status_min'])
                ->setParameter('max', $options['status_max'])
            ;
        }

        if (isset($options['service'])) {
            switch ($options['service']) {
                case 'clickeat':
                    $qb->andWhere('restaurant.isClickeat = true');
                    break;
                case 'tttruck':
                    $qb->andWhere('restaurant.isTtt = true');
                    break;
                default:
                    break;
            }
        }

        $qb
            ->leftJoin('restaurant.subscriptions', 'subscriptions')
            ->leftJoin('restaurant.tags', 'tags')
        ;

        return $qb->getQuery()->getResult();
    }

    public function loadProfile($slug, $options = array())
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->select('restaurant', 'address', 'tags', 'subscriptions')
            ->where('restaurant.slug = :slug')
            ->setParameter('slug', $slug);

        if (isset($options['status_min']) && isset($options['status_max'])) {
            $qb
                ->andWhere('restaurant.status >= :min')
                ->andWhere('restaurant.status <= :max')
                ->setParameter('min', $options['status_min'])
                ->setParameter('max', $options['status_max'])
            ;
        }

        if (isset($options['service'])) {
            switch ($options['service']) {
                case 'clickeat':
                    $qb->andWhere('restaurant.isClickeat = true');
                    break;
                case 'tttruck':
                    $qb->andWhere('restaurant.isTtt = true');
                    break;
                default:
                    break;
            }
        }

        $qb
            ->leftJoin('restaurant.subscriptions', 'subscriptions')
            ->leftJoin('restaurant.tags', 'tags')
            ->leftJoin('restaurant.address', 'address')
        ;

        return $qb->getQuery()->getSingleResult();
    }

    public function loadCatalog($slug, $options = array())
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->select('restaurant', 'categories', 'products', 'productsTax', 'meals', 'mealsTax', 'sale', 'mealSale')
            ->where('restaurant.slug = :slug')
            ->setParameter('slug', $slug);

        $qb
            ->leftJoin('restaurant.productCategories', 'categories')
            ->leftJoin('categories.products', 'products')
            ->leftJoin('products.sale', 'sale')
            ->leftJoin('products.tax', 'productsTax')
            ->leftJoin('restaurant.meals', 'meals')
            ->leftJoin('meals.sale', 'mealSale')
            ->leftJoin('meals.tax', 'mealsTax')
        ;

        return $qb->getQuery()->getSingleResult();
    }

    public function findByAddress($address)
    {
        $qb = $this->createQueryBuilder('r')
            ->leftJoin('r.address', 'a')
            ->where('a.zip = :zip')
            ->andWhere('LOWER(a.street) = :street')
            ->andWhere('r.status < :trashStatus')
            ->setParameter('trashStatus', Restaurant::STORE_STATUS_TRASH)
            ->setParameter('zip', $address->getZip())
            ->setParameter('street', strtolower($address->getStreet()));

        return $qb->getQuery()->getResult();
    }

    // ttt
    public function foodtruckSearch($latitude, $longitude, $distanceMin = 0, $distanceMax = 1)
    {
        $qb = $this->createQueryBuilder('r')
            ->select('r', 'GEO_DISTANCE(:latitude, :longitude, address.latitude, address.longitude) AS distance')
            ->having('distance >= :distanceMin AND distance < :distanceMax')
            ->andWhere('r.isTtt = 1')
            ->leftJoin('r.timesheets', 'timesheets')
            ->innerJoin('timesheets.address', 'address')
            ->andWhere('timesheets.endDate >= :day OR timesheets.endDate IS NULL')
            ->setParameter('day', date_create('today'))
            ->setParameter('latitude', $latitude)
            ->setParameter('longitude', $longitude)
            ->setParameter('distanceMin', $distanceMin)
            ->setParameter('distanceMax', $distanceMax);

        return $qb->getQuery()->getResult();
    }

    public function findFoodtruckInArea($address, $distance)
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->select('restaurant', 'GEO_DISTANCE(:latitude, :longitude, address.latitude, address.longitude) AS distance')
            ->having('distance <= :distance')
            ->leftJoin('restaurant.legalAddress', 'address')
            ->setParameter('latitude', $address->getLatitude())
            ->setParameter('longitude', $address->getLongitude())
            ->setParameter('distance', $distance)
            ->where('restaurant.isTtt = true');

        return $qb->getQuery()->getResult();
    }

    public function findNearestFoodtruck($address, $limit = 3)
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->select('restaurant', 'GEO_DISTANCE(:latitude, :longitude, address.latitude, address.longitude) AS distance')
            ->leftJoin('restaurant.legalAddress', 'address')
            ->setParameter('latitude', $address->getLatitude())
            ->setParameter('longitude', $address->getLongitude())
            ->where('restaurant.isTtt = true')
            ->orderBy('distance', 'asc')
            ->setMaxResults($limit);

        return $qb->getQuery()->getResult();
    }

    public function findOnlineByClient($client)
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->andWhere('restaurant.client = :client')
            ->andWhere('restaurant.status >= :min')
            ->andWhere('restaurant.status < :max')
            ->setParameter('min', Restaurant::STORE_STATUS_ACTIVE)
            ->setParameter('max', Restaurant::STORE_STATUS_TRASH)
            ->setParameter('client', $client);

        return $qb->getQuery()->getResult();
    }

    public function findAllWithFacebookPage()
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->andWhere('restaurant.status >= :min')
            ->andWhere('restaurant.status < :max')
            ->andWhere('restaurant.facebookPage IS NOT NULL')
            ->setParameter('min', Restaurant::STORE_STATUS_ACTIVE)
            ->setParameter('max', Restaurant::STORE_STATUS_TRASH);

        return $qb->getQuery()->getResult();
    }

    public function findByNameAndAddress($name, $street, $city, $zip)
    {
        $qb = $this->createQueryBuilder('restaurant')
            ->leftJoin('restaurant.address', 'address')
            ->andWhere('LOWER(restaurant.name) like :name')
            ->andWhere('LOWER(address.street) like :street')
            ->andWhere('LOWER(address.city) like :city')
            ->andWhere('address.zip = :zip')
            ->setParameter('name', strtolower($name))
            ->setParameter('street', strtolower($street))
            ->setParameter('city', strtolower($city))
            ->setParameter('zip', $zip);

        return $qb->getQuery()->getResult();
    }

    public function findByIds($ids, $location = null) {
        $qb = $this->createQueryBuilder('r');
        $inInIds = $qb->expr()->in('r.id', ':ids');

        if ($location) {
            $qb
                ->select('r', 'GEO_DISTANCE(:latitude, :longitude, address.latitude, address.longitude) AS distance')
                ->leftJoin('r.address', 'address')
                ->setParameter('latitude', $location['lat'])
                ->setParameter('longitude', $location['lng'])
                ->orderBy('distance')
            ;
        }

        $qb
            ->where($inInIds)
            ->setParameter('ids', $ids)
        ;

        return $qb->getQuery()->getResult();
    }
}
