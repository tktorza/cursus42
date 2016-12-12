<?php

namespace Clab\WhiteBundle\Manager;

use Clab\BoardBundle\Entity\Client;
use Clab\MediaBundle\Entity\Image;
use Clab\RestaurantBundle\Entity\App;
use Clab\RestaurantBundle\Entity\Plan;
use Clab\RestaurantBundle\Entity\RestaurantMenu;
use Clab\RestaurantBundle\Entity\Tax;
use Clab\ShopBundle\Entity\OrderType;
use Clab\ShopBundle\Entity\PaymentMethod;
use Clab\SocialBundle\Entity\SocialProfile;
use Clab\SocialBundle\Service\SocialManager;
use Clab\UserBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Router;

class CoreManager {

    protected $em;

    public function __construct(EntityManager $em) {

        $this->em = $em;
    }

    public function createChainStore($name, User $user, $clientPayment) {

        $client = new Client();
        $client->setName($name);
        $client->setClientPayment($clientPayment);
        $menuDefault = new RestaurantMenu();
        $menuDefault->setChainStore($client);
        $menuDefault->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT);
        $menuDefault->setName('Carte classique');
        $menuDelivery = new RestaurantMenu();
        $menuDelivery->setChainStore($client);
        $menuDelivery->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY);
        $menuDelivery->setName('Carte livraison');
        $this->em->persist($menuDefault);
        $this->em->persist($menuDelivery);
        $this->em->persist($client);
        $this->em->flush();
        $socialProfile = new SocialProfile();
        $client->setSocialProfile($socialProfile);
        $this->em->persist($socialProfile);
        $this->em->flush();
        return true;
    }

    public function createApps()
    {
        $app1 = new App();
        $app1->setPrice(0);
        $app1->setName('Coupon');
        $app1->setSlug('coupon');
        $app1->setIsOnline(1);
        $app1->setCallToAction('coupons/library');
        $app1->setType(0);
        $app1->setPlatform(20);
        $app1->setImageName('coupon.png');
        $app1->setShortDescription('Offrez des codes de réduction à vos clients');

        $app2 = new App();
        $app2->setPrice(0);
        $app2->setName('Offre');
        $app2->setSlug('offre');
        $app2->setIsOnline(1);
        $app2->setCallToAction('discounts/library');
        $app2->setType(0);
        $app2->setPlatform(20);
        $app2->setImageName('offre.png');
        $app2->setShortDescription('Créez des offres pour inciter le client à commander quand ça vous arrange');

        $app3 = new App();
        $app3->setPrice(0);
        $app3->setName('Vente additionnelle');
        $app3->setSlug('vente-additionnelle');
        $app3->setIsOnline(1);
        $app3->setCallToAction('additionalsale/library');
        $app3->setType(0);
        $app3->setPlatform(20);
        $app3->setImageName('vente-add.png');
        $app3->setShortDescription('Suggérez à vos clients un petit plus à leur commande');

        $app4 = new App();
        $app4->setPrice(0);
        $app4->setName('Gestion des impressions');
        $app4->setSlug('gestion-des-impressions');
        $app4->setIsOnline(1);
        $app4->setCallToAction('impression');
        $app4->setType(0);
        $app4->setPlatform(20);
        $app4->setImageName('impression.png');
        $app4->setShortDescription('Personnalisez vos tickets de commandes en ligne');

        $this->em->persist($app1);
        $this->em->persist($app2);
        $this->em->persist($app3);
        $this->em->persist($app4);
        $this->em->flush();
        return true;
    }

    public function createPlan($chainstore, $price,$idStripe)
    {
        $plan = new Plan();
        $plan->setIsOnline(1);
        $plan->setCreated(new \DateTime("now"));
        $plan->setUpdated(new \DateTime("now"));
        $plan->setName("Abonnement " .$chainstore);
        $plan->setPrice($price);
        $plan->setStripePlanId($idStripe);
        $this->em->persist($plan);
        $this->em->flush();
        return true;
    }

    public function createTaxes() {

        $tax1 = new Tax();
        $tax1->setName('TVA 20%');
        $tax1->setValue(20);
        $tax1->setIsOnline(1);
        $tax1->setRank(1);

        $tax2 = new Tax();
        $tax2->setName('TVA 5.5%');
        $tax2->setValue(5.5);
        $tax2->setIsOnline(1);
        $tax2->setRank(2);

        $tax3 = new Tax();
        $tax3->setName('TVA 10%');
        $tax3->setValue(10);
        $tax3->setIsOnline(1);
        $tax3->setRank(3);

        $this->em->persist($tax1);
        $this->em->persist($tax2);
        $this->em->persist($tax3);
        $this->em->flush();

        return true;
    }

    public function createOrderTypes() {

        $type1 = new OrderType();
        $type1->setIsOnline(1);
        $type1->setName('Pré-commande');
        $type1->setSlug('preorder');
        $type1->setIsDeleted(0);

        $type2 = new OrderType();
        $type2->setIsOnline(1);
        $type2->setName('En file');
        $type2->setSlug('takeaway');
        $type2->setIsDeleted(0);

        $type3 = new OrderType();
        $type3->setIsOnline(1);
        $type3->setName('Livraison');
        $type3->setSlug('delivery');
        $type3->setIsDeleted(0);

        $this->em->persist($type1);
        $this->em->persist($type2);
        $this->em->persist($type3);
        $this->em->flush();

        return true;
    }

    public function createPaymentMethods() {

        $method1 = new PaymentMethod();
        $method1->setIsOnline(1);
        $method1->setName('Carte bancaire');
        $method1->setSlug('credit-card');
        $method1->setIcon('credit-card');
        $method1->setAvailableForOrder(0);
        $method1->setMinimum('5');

        $method2 = new PaymentMethod();
        $method2->setIsOnline(1);
        $method2->setName('Espèces');
        $method2->setSlug('money');
        $method2->setIcon('money');
        $method2->setAvailableForOrder(0);
        $method2->setMinimum('0');

        $method3 = new PaymentMethod();
        $method3->setIsOnline(1);
        $method3->setName('Ticket Resto');
        $method3->setSlug('ticket');
        $method3->setIcon('ticket');
        $method3->setAvailableForOrder(0);
        $method3->setMinimum('0');

        $method4 = new PaymentMethod();
        $method4->setIsOnline(1);
        $method4->setName('Chèques');
        $method4->setSlug('cheque');
        $method4->setIcon('cheque');
        $method4->setAvailableForOrder(0);
        $method4->setMinimum('0');

        $this->em->persist($method1);
        $this->em->persist($method2);
        $this->em->persist($method3);
        $this->em->persist($method4);
        $this->em->flush();

        return true;
    }


    public function createDefaultImage() {

        $image = new Image();
        $image->setIsOnline(1);
        $image->setCreated(new \DateTime("now"));
        $image->setUpdated(new \DateTime("now"));
        $image->setName('blank.png');
        $image->setPath('blank.png');
        $image->setIsPromoted(0);
        $image->setIsGeneric(0);
        $this->em->persist($image);
        $this->em->flush();
        return true;
    }


}
