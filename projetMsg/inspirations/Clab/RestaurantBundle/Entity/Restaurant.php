<?php

namespace Clab\RestaurantBundle\Entity;

use Clab\BoardBundle\Entity\ClientConfiguration;
use Clab\BoardBundle\Entity\OrderStatement;
use Clab\BoardBundle\Entity\Subscription;
use Clab\BoardBundle\Entity\SubscriptionTerms;
use Clab\LocationBundle\Entity\Address;
use Clab\MediaBundle\Entity\Album;
use Clab\MediaBundle\Entity\Gallery;
use Clab\RestaurantBundle\Entity\Deal;
use Clab\ReviewBundle\Entity\Review;
use Clab\ShopBundle\Entity\Coupon;
use Clab\ShopBundle\Entity\Discount;
use Clab\ShopBundle\Entity\OrderDetail;
use Clab\ShopBundle\Entity\OrderType;
use Clab\ShopBundle\Entity\PaymentMethod;
use Clab\SocialBundle\Entity\SocialFacebookPage;
use Clab\SocialBundle\Entity\SocialPost;
use Clab\SocialBundle\Entity\SocialProfile;
use Clab\TaxonomyBundle\Entity\Term;
use Clab\UserBundle\Entity\Pincode;
use Clab\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Clab\MediaBundle\Entity\GalleryOwnerInterface;
use Clab\ReviewBundle\Entity\ReviewObservableInterface;
use Clab\BoardBundle\Entity\Client;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="clickeat_restaurant")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\RestaurantRepository")
 * @Vich\Uploadable
 * @ORM\HasLifecycleCallbacks
 */
class Restaurant implements GalleryOwnerInterface, ReviewObservableInterface, \Serializable
{
    const STORE_STATUS_NEW = 0;
    const STORE_STATUS_NEW_INVITED = 10;
    const STORE_STATUS_NEW_INVITED_ONBOARD = 50;
    const STORE_STATUS_PROSPECT = 500;
    const STORE_STATUS_TEST = 1000;
    const STORE_STATUS_WAITING = 2000;
    const STORE_STATUS_WAITING_PLANS = 2010;
    const STORE_STATUS_WAITING_INFOS = 2020;
    const STORE_STATUS_WAITING_TERMS = 2030;
    const STORE_STATUS_WAITING_BILLING = 2040;
    const STORE_STATUS_ACTIVE = 3000;
    const STORE_STATUS_ACTIVE_INCOMPLETE = 3010;
    const STORE_STATUS_TRASH = 7000;

    const STORE_UPDATE_STATUS_NEW = 0;
    const STORE_UPDATE_STATUS_IN_VALIDATION = 100;
    const STORE_UPDATE_STATUS_VALIDATE = 200;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    protected $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    protected $updated;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $lastSyncCatalog;

    /**
     * @ORM\Column(name="status", type="integer")
     */
    protected $status;

    /**
     * @ORM\Column(name="update_status", type="integer", nullable = true)
     */
    protected $updateStatus;

    /**
     * @ORM\Column(name="is_public", type="boolean")
     */
    protected $isPublic;

    /**
     * @ORM\Column(name="is_clickeat", type="boolean")
     */
    protected $isClickeat;

    /**
     * @ORM\Column(name="is_ttt", type="boolean")
     */
    protected $isTtt;

    /**
     * @ORM\Column(name="is_promoted", type="boolean")
     */
    protected $isPromoted;

    /**
     * @ORM\Column(name="autoprint", type="boolean")
     */
    protected $autoPrint;

    /**
     * @ORM\Column(name="web_notification", type="boolean", nullable = true)
     */
    protected $webNotification;

    /**
     * @ORM\Column(name="footerPrint", type="string" , nullable = true)
     */
    protected $footerPrint;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="clab_restaurant_print_image", fileNameProperty="printImageName")
     *
     * @var File
     */
    private $printImageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $printImageName;

    /**
     * @ORM\Column(name="is_open", type="integer", nullable= true)
     */
    protected $isOpen;

    /**
     * @ORM\Column(name="is_open_delivery", type="boolean")
     */
    protected $isOpenDelivery;

    /**
     * @ORM\Column(name="admin_comment", type="text", nullable=true)
     */
    protected $adminComment;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(name="nearest_subway", type="array", nullable=true)
     */
    protected $nearestSubways;

    /**
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="avg_review_score", type="float", nullable=true)
     */
    protected $avgReviewScore;

    /**
     * @ORM\Column(name="avg_price_score", type="float", nullable=true)
     */
    protected $avgPriceScore;

    /**
     * @ORM\Column(name="avg_clean_score", type="float", nullable=true)
     */
    protected $avgCleanScore;

    /**
     * @ORM\Column(name="avg_service_score", type="float", nullable=true)
     */
    protected $avgServiceScore;

    /**
     * @ORM\Column(name="avg_cook_score", type="float", nullable=true)
     */
    protected $avgCookScore;

    /**
     * @ORM\Column(name="small_description", type="string", length=140, nullable=true)
     */
    protected $smallDescription;

    /**
     * @ORM\Column(name="phone", type="phone_number", length=255, nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(name="email", type="string", length=255, nullable=true)
     */
    protected $email;

    /**
     * @ORM\Column(name="email_payment", type="string", length=255, nullable=true)
     */
    protected $emailPayment;

    /**
     * @ORM\Column(name="website", type="string", length=255, nullable=true)
     */
    protected $website;

    /**
     * @ORM\Column(name="primaryColor", type="string", length=255, nullable=true)
     */
    protected $primaryColor;

    /**
     * @ORM\Column(name="secondaryColor", type="string", length=255, nullable=true)
     */
    protected $secondaryColor;

    /**
     * @ORM\Column(name="cart_color", type="string", length=255, nullable=true)
     */
    protected $cartColor;

    /**
     * @ORM\Column(name="button_color", type="string", length=255, nullable=true)
     */
    protected $buttonColor;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="clab_restaurant_logo_mc", fileNameProperty="logoMcName")
     *
     * @var File
     */
    private $logoMcFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $logoMcName;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="clab_restaurant_rib", fileNameProperty="ribName")
     *
     * @var File
     */
    private $ribFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $ribName;

    /**
     * @ORM\Column(name="managerFirstName", type="string", length=255, nullable=true)
     */
    protected $managerFirstName;

    /**
     * @ORM\Column(name="managerName", type="string", length=255, nullable=true)
     */
    protected $managerName;

    /**
     * @ORM\Column(name="managerPhone", type="phone_number", length=255, nullable=true)
     */
    protected $managerPhone;

    /**
     * @ORM\Column(name="managerEmail", type="string", length=255, nullable=true)
     */
    protected $managerEmail;

    /**
     * @ORM\Column(name="orderDelay", type="integer")
     */
    protected $orderDelay;

    /**
     * @ORM\Column(name="orderStart", type="integer", nullable=true)
     */
    protected $orderStart;

    /**
     * @ORM\Column(name="maxOrderBySlot", type="integer")
     */
    protected $maxOrderBySlot;

    /**
     * @ORM\Column(name="maxOrderPriceBySlot", type="integer", nullable=true)
     */
    protected $maxOrderPriceBySlot;

    /**
     * @ORM\Column(name="averagePrice", type="integer", nullable=true)
     */
    protected $averagePrice;

    /**
     * @ORM\Column(name="average_menu_price", type="float", nullable=true)
     */
    protected $averageMenuPrice;

    /**
     * @ORM\Column(name="is_mobile", type="boolean")
     */
    protected $isMobile;

    /**
     * @ORM\Column(name="is_gold", type="boolean", nullable=true)
     */
    protected $isGold;

    /**
     * @ORM\Column(name="has_caisse", type="boolean", nullable=true)
     */
    protected $hasCaisse;

    /**
     * @ORM\Column(name="source", type="string", length=255, nullable=true)
     */
    protected $source;

    /**
     * @ORM\Column(name="pipedriveId", type="integer", nullable=true)
     */
    protected $pipedriveId;

    /**
     * @ORM\Column(name="pipedriveDealId", type="integer", nullable=true)
     */
    protected $pipedriveDealId;

    /**
     * @ORM\Column(name="pipedriveContactId", type="integer", nullable=true)
     */
    protected $pipedriveContactId;

    /**
     * @ORM\Column(name="restoflashId", type="integer", nullable=true)
     */
    protected $restoflashId;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address", cascade={"all"})
     * @ORM\JoinColumn(name="address_id", referencedColumnName="id", nullable=true)
     */
    protected $address;

    /**
     * @ORM\OneToMany(targetEntity="TimeSheet", mappedBy="restaurant")
     * @ORM\OrderBy({"type" = "desc", "start" = "asc", "id" = "desc"})
     */
    protected $timesheets;

    /**
     * @ORM\Column(name="flatTimeSheet", type="json_array", nullable=true)
     */
    protected $flatTimeSheet;

    /**
     * @ORM\Column(name="bestReview", type="json_array", nullable=true)
     */
    protected $bestReview;

    /**
     * @ORM\OneToMany(targetEntity="TimesheetValidation", mappedBy="restaurant")
     * @ORM\OrderBy({"start" = "asc"})
     */
    protected $timesheetValidations;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\Client", inversedBy="restaurants")
     * @ORM\JoinColumn(name="client_id", referencedColumnName="id", nullable=true)
     */
    protected $client;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\ClientConfiguration", inversedBy="restaurants")
     * @ORM\JoinColumn(name="client_configuration_id", referencedColumnName="id", nullable=true)
     */
    protected $clientConfiguration;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="owner_id", referencedColumnName="id", nullable=true)
     */
    protected $owner;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="commercial_id", referencedColumnName="id", nullable=true)
     */
    protected $commercial;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\UserBundle\Entity\User", inversedBy="restaurants")
     * @ORM\JoinTable(name="clickeat_restaurants_managers",
     *                joinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")})
     */
    protected $managers;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\ShopBundle\Entity\PaymentMethod")
     * @ORM\JoinTable(name="clickeat_restaurants_paymentmethod",
     *      joinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="paymentmethod_id", referencedColumnName="id")}
     *      )
     */
    protected $paymentMethods;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\ShopBundle\Entity\PaymentMethod")
     * @ORM\JoinTable(name="clickeat_restaurants_storepaymentmethod",
     *                joinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="paymentmethod_id", referencedColumnName="id")})
     */
    protected $storePaymentMethods;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\ShopBundle\Entity\OrderType")
     * @ORM\JoinTable(name="clickeat_restaurants_ordertype",
     *                joinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="ordertype_id", referencedColumnName="id")})
     */
    protected $orderTypes;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected $gallery;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_big_id", referencedColumnName="id")
     */
    protected $galleryBig;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_menu_id", referencedColumnName="id")
     */
    protected $galleryMenu;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="public_gallery", referencedColumnName="id")
     */
    protected $publicGallery;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\MediaBundle\Entity\Album")
     * @ORM\JoinTable(name="clickeat_restaurants_albums",
     *                joinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="album_id", referencedColumnName="id")})
     */
    protected $albums;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\SubscriptionTerms", inversedBy="restaurants", cascade={"all"})
     * @ORM\JoinColumn(name="subscription_terms_id", referencedColumnName="id", nullable=true)
     */
    protected $subscriptionTerms;

    /**
     * @ORM\OneToMany(targetEntity="Clab\BoardBundle\Entity\Subscription", mappedBy="restaurant")
     * @ORM\OrderBy({"created" = "asc"})
     */
    protected $subscriptions;

    /**
     * @ORM\Column(name="discoverFeatures", type="text", nullable=true)
     */
    protected $discoverFeatures;

    /**
     * @ORM\OneToMany(targetEntity="StaffMember", mappedBy="restaurant")
     * @ORM\OrderBy({"position" = "asc"})
     */
    protected $staffMembers;

    /**
     * @ORM\Column(name="notification_mails", type="text", nullable=true)
     */
    protected $notificationMails;

    /**
     * @ORM\Column(name="tttEventValidationMail", type="text", nullable=true)
     */
    protected $tttEventValidationMail;

    /**
     * @ORM\Column(name="storeCreation", type="datetime", nullable=true)
     */
    protected $storeCreation;

    /**
     * @ORM\Column(name="storeClosing", type="datetime", nullable=true)
     */
    protected $storeClosing;

    /**
     * @ORM\Column(name="legalType", type="integer", nullable=true)
     */
    protected $legalType;

    /**
     * @ORM\Column(name="legalName", type="string", length=255, nullable=true)
     */
    protected $legalName;

    /**
     * @ORM\Column(name="legalPerson", type="string", length=255, nullable=true)
     */
    protected $legalPerson;

    /**
     * @ORM\Column(name="clickspotter", type="string", length=255, nullable=true)
     */
    protected $clickSpotter;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address", cascade={"all"})
     * @ORM\JoinColumn(name="legal_address_id", referencedColumnName="id", nullable=true)
     */
    protected $legalAddress;

    /**
     * @ORM\Column(name="siret", type="string", length=500, nullable=true)
     * @Assert\Luhn(message = "Veuillez indiquer un SIRET valide.")
     */
    protected $siret;

    /**
     * @ORM\Column(name="capital", type="string", length=500, nullable=true)
     */
    protected $capital;

    /**
     * @ORM\OneToMany(targetEntity="\Clab\BoardBundle\Entity\OrderStatement", mappedBy="restaurant")
     * @ORM\OrderBy({"startDate" = "desc"})
     */
    protected $orderStatements;

    /**
     * @ORM\OneToMany(targetEntity="\Clab\ShopBundle\Entity\OrderDetail", mappedBy="restaurant")
     */
    protected $orders;

    /**
     * @ORM\OneToMany(targetEntity="\Clab\ShopBundle\Entity\Discount", mappedBy="restaurant")
     * @ORM\OrderBy({"name" = "asc"})
     */
    protected $discounts;

    /**
     * @ORM\Column(name="active_discount", type="array", nullable=true)
     */
    protected $activeDiscount;

    /**
     * @ORM\OneToMany(targetEntity="\Clab\ShopBundle\Entity\Coupon", mappedBy="restaurant")
     */
    protected $coupons;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\RestaurantBundle\Entity\RestaurantService", inversedBy="restaurants")
     * @ORM\JoinTable(name="clickeat_restaurants_services",
     *                joinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="service_id", referencedColumnName="id")})
     */
    protected $services;

    /**
     * @ORM\OneToMany(targetEntity="Clab\ReviewBundle\Entity\Review", mappedBy="restaurant")
     **/
    protected $reviews;

    /**
     * @ORM\Column(name="lastFacebookRatingCheck", type="datetime", nullable=true)
     */
    protected $lastFacebookRatingCheck;

    /**
     * @ORM\OneToOne(targetEntity="Clab\SocialBundle\Entity\SocialProfile", cascade={"all"})
     * @ORM\JoinColumn(name="social_profile_id", referencedColumnName="id", nullable=true)
     */
    protected $socialProfile;


    /**
     * @ORM\OneToOne(targetEntity="Clab\RestaurantBundle\Entity\Deal", inversedBy="restaurant", cascade={"all"})
     * @ORM\JoinColumn(name="deal_id", referencedColumnName="id", nullable=true)
     */
    protected $deal;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\SocialBundle\Entity\SocialFacebookPage", inversedBy="restaurants")
     * @ORM\JoinColumn(name="facebook_page_id", referencedColumnName="id", nullable=true)
     */
    protected $facebookPage;

    /**
     * @ORM\OneToMany(targetEntity="Clab\SocialBundle\Entity\SocialPost", mappedBy="restaurant")
     * @ORM\OrderBy({"created" = "asc"})
     */
    protected $socialPosts;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\TaxonomyBundle\Entity\Term", inversedBy="restaurants")
     * @ORM\JoinTable(name="clickeat_restaurant_tags",
     *                joinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="term_id", referencedColumnName="id")})
     */
    protected $tags;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\TaxonomyBundle\Entity\Term")
     * @ORM\JoinTable(name="clickeat_restaurant_tags_extra",
     *                joinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="term_id", referencedColumnName="id")})
     */
    protected $extraTags;

    /**
     * /**
     * @ORM\Column(name="flatTags", type="array", nullable=true)
     */
    protected $flatTags;

    /**
     * @ORM\Column(name="iframeNoClient", type="boolean", nullable=true)
     */
    protected $iframeNoClient;

    /**
     * @ORM\Column(name="tttHidePrices", type="boolean", nullable=true)
     */
    protected $tttHidePrices;

    /**
     * @ORM\OneToMany(targetEntity="RestaurantMenu", mappedBy="restaurant",cascade={"persist", "remove"})
     */
    protected $restaurantMenus;

    /**
     * @ORM\OneToMany(targetEntity="ProductCategory", mappedBy="restaurant")
     * @ORM\OrderBy({"position" = "asc"})
     */
    protected $productCategories;

    /**
     * @ORM\OneToMany(targetEntity="ProductOption", mappedBy="restaurant")
     */
    protected $options;

    /**
     * @ORM\OneToMany(targetEntity="MealSlot", mappedBy="restaurant")
     */
    protected $mealSlots;

    /**
     * @ORM\Column(type="string", length=255, name="stripe_customer_id", nullable=true)
     */
    protected $stripeCustomerId;

    /**
     * @ORM\Column(type="boolean", name="is_stripe_customer_active", nullable=true)
     */
    protected $isStripeCustomerActive;

    /**
     * @ORM\Column(type="string", length=255, name="stripe_access_token", nullable=true)
     */
    protected $stripeAccessToken;

    /**
     * @ORM\Column(type="string", length=255, name="stripe_publishable_key", nullable=true)
     */
    protected $stripePublishableKey;

    /**
     * @ORM\OneToMany(targetEntity="Clab\UserBundle\Entity\Pincode", mappedBy="restaurant")
     */
    protected $pincodes;

    /**
     * @ORM\ManyToMany(targetEntity="App")
     * @ORM\JoinTable(name="restaurant_apps",
     *      joinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="app_id", referencedColumnName="id")}
     *      )
     */
    protected $apps;

    /**
     * @ORM\ManyToMany(targetEntity="TokenDevice")
     * @ORM\JoinTable(name="restaurant_devices",
     *      joinColumns={@ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="device_id", referencedColumnName="id")}
     *      )
     */
    private $tokenDevices;

    /**
     * @ORM\OneToMany(targetEntity="Clab\BoardBundle\Entity\AdditionalSale", mappedBy="restaurant",cascade={"remove"})
     */
    private $additionalSales;

    /**
     * @ORM\OneToMany(targetEntity="Clab\DeliveryBundle\Entity\DeliverySchedule", mappedBy="restaurant",cascade={"remove"})
     */
    private $deliverySchedules;

    /**
     * @ORM\Column(type="json_array", name="checker_board_config", nullable=true)
     */
    private $checkerBoardConfig;

    /**
     * @ORM\Column(type="boolean", name="has_ticket_resto_scan", nullable=true)
     */
    private $hasTicketRestoScan;

    /**
     * @ORM\Column(type="json_array", name="caisse_discounts_labels", nullable=true)
     */
    private $caisseDiscountsLabels;

    /**
     * @ORM\Column(type="json_array", name="caisse_tags", nullable=true)
     */
    private $caisseTags;

    /**
     * @ORM\Column(type="json_array", name="caisse_printer_labels", nullable=true)
     */
    private $caissePrinterLabels;


    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->name,
            $this->slug,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list(
            $this->id,
            $this->name,
            $this->slug) = unserialize($serialized);
    }

    public function __sleep()
    {
        $ref = new \ReflectionClass(__CLASS__);
        $props = $ref->getProperties(\ReflectionProperty::IS_PROTECTED);

        $serialize_fields = array();

        foreach ($props as $prop) {
            $serialize_fields[] = $prop->name;
        }

        return $serialize_fields;
    }

    // timesheet model
    protected $planning;
    protected $todayPlanning;
    protected $preorderSlots;
    protected $currentEvent = null;

    protected $distance;

    protected $coverSmall;
    protected $cover;
    protected $coverFull;
    protected $coverBig;
    protected $coverBigDefault;

    protected $reviewData = null;
    protected $newsData = null;
    protected $discountData = null;
    protected $deliveryCarts = null;
    protected $openingTimesheets;
    protected $preorderTimesheets;
    protected $deliveryTimesheets;
    protected $nextSchedule;
    protected $nextDeliverySchedule;

    protected $printers;

    public function getAvailableTimesheets()
    {
        $timesheets = array();
        $now = date_create('yesterday');
        foreach ($this->getTimesheets() as $timesheet) {
            if (!$timesheet->getEndDate() || $timesheet->getEndDate() >= $now) {
                $timesheets[] = $timesheet;
            }
        }

        return $timesheets;
    }

    public function getOpeningTimesheets()
    {
        $timesheets = array();
        foreach ($this->getAvailableTimesheets() as $timesheet) {
            if ($timesheet->isOpening() && $timesheet->getType() == 1) {
                $timesheets[] = array(
                    'days' => $timesheet->renderDays(),
                    'start' => $timesheet->getStart(),
                    'end' => $timesheet->getEnd(),
                );
            }
        }

        return $timesheets;
    }

    public function getTimesheetsOpening()
    {
        $timesheets = array();
        foreach ($this->getAvailableTimesheets() as $timesheet) {
            if ($timesheet->getType() == 1) {
                $timesheets[] = $timesheet;
            }
        }

        return $timesheets;
    }

    /**
     * @return mixed
     */
    public function getPublicGallery()
    {
        return $this->publicGallery;
    }

    /**
     * @param mixed $publicGallery
     *
     * @return $this
     */
    public function setPublicGallery($publicGallery)
    {
        $this->publicGallery = $publicGallery;

        return $this;
    }

    /**
     * @param mixed $publicGallery
     *
     * @return $this
     */
    public function setGalleryPublic($publicGallery)
    {
        $this->publicGallery = $publicGallery;

        return $this;
    }

    public function __toString()
    {
        return $this->getName();
    }

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setStatus(self::STORE_STATUS_NEW);
        $this->setIsPublic(true);
        $this->setIsClickeat(false);
        $this->setIsTtt(false);
        $this->setIsOpen(0);
        $this->setIsPromoted(false);
        $this->setHasCaisse(false);
        $this->setIsOpenDelivery(false);
        $this->setLegalType(1);
        $this->setMaxOrderBySlot(5);
        $this->setMaxOrderPriceBySlot(0);
        $this->setOrderDelay(15);
        $this->setOrderStart(0);
        $this->setIsMobile(false);
        $this->setDistance(null);
        $this->setSource('self');
        $this->setAutoPrint(false);
        $menuDefault = new RestaurantMenu();
        $menuDefault->setRestaurant($this);
        $menuDefault->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DEFAULT);
        $menuDefault->setName('Carte classique');

        $menuDelivery = new RestaurantMenu();
        $menuDelivery->setRestaurant($this);
        $menuDelivery->setType(RestaurantMenu::RESTAURANT_MENU_TYPE_DELIVERY);
        $menuDelivery->setName('Carte livraison');

        $this->reviews = new ArrayCollection();
        $this->productCategories = new ArrayCollection();
        $this->discounts = new ArrayCollection();
        $this->managers = new ArrayCollection();
        $this->paymentMethods = new ArrayCollection();
        $this->storePaymentMethods = new ArrayCollection();
        $this->orderTypes = new ArrayCollection();
        $this->mealSlots = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->timesheets = new ArrayCollection();
        $this->timesheetValidations = new ArrayCollection();
        $this->subscriptions = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->albums = new ArrayCollection();
        $this->pincodes = new ArrayCollection();
        $this->apps = new ArrayCollection();
        $this->tokenDevices = new ArrayCollection();
        $this->deliverySchedules = new ArrayCollection();

        $this->hasCaisse = true;
        $this->caisseDiscountsLabels = array();
        $this->caisseTags = array();
        $this->caissePrinterLabels = array();
    }

    /**
     * @return mixed
     */
    public function getHasCaisse()
    {
        return $this->hasCaisse;
    }

    /**
     * @param mixed $hasCaisse
     *
     * @return $this
     */
    public function setHasCaisse($hasCaisse)
    {
        $this->hasCaisse = $hasCaisse;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNearestSubways()
    {
        return $this->nearestSubways;
    }

    public function addNearestSubway($subway)
    {
        $this->nearestSubways[] = $subway;
    }

    public function setNearestSubway($subways)
    {
        $this->nearestSubways = $subways;
        foreach ($subways as $subway) {
            $this->addNearestSubway($subway);
        }

        return $this;
    }

    public function getTokenDevices()
    {
        return $this->tokenDevices;
    }

    public function addTokenDevice(TokenDevice $token)
    {
        $this->tokenDevices[] = $token;
    }

    public function removeTokenDevice(TokenDevice $token)
    {
        $this->tokenDevices->removeElement($token);
    }

    /**
     * @return mixed
     */
    public function getApps()
    {
        return $this->apps;
    }

    public function addApp(App $app)
    {
        $this->apps[] = $app;
    }

    public function setApps($apps)
    {
        $this->apps = $apps;
        foreach ($apps as $app) {
            $this->addApp($app);
        }

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCartColor()
    {
        return $this->cartColor;
    }

    /**
     * @param mixed $cartColor
     *
     * @return $this
     */
    public function setCartColor($cartColor)
    {
        $this->cartColor = $cartColor;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getButtonColor()
    {
        return $this->buttonColor;
    }

    /**
     * @param mixed $buttonColor
     *
     * @return $this
     */
    public function setButtonColor($buttonColor)
    {
        $this->buttonColor = $buttonColor;

        return $this;
    }

    /**
     * Remove app.
     */
    public function removeApp(App $app)
    {
        $this->apps->removeElement($app);
    }

    /**
     * @return File
     */
    public function getRibFile()
    {
        return $this->ribFile;
    }

    /**
     * @param File $ribFile
     *
     * @return $this
     */
    public function setRibFile($image)
    {
        $this->ribFile = $image;

        if ($image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updated = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getRibName()
    {
        return $this->ribName;
    }

    /**
     * @param string $ribName
     *
     * @return $this
     */
    public function setRibName($ribName)
    {
        $this->ribName = $ribName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPinCodes()
    {
        return $this->pincodes;
    }

    public function addPincode($pincode)
    {
        $this->pincodes[] = $pincode;
    }

    public function setPincodes($pincodes)
    {
        $this->pincodes = $pincodes;
        foreach ($pincodes as $pincode) {
            $this->addPincode($pincode);
        }

        return $this;
    }

    /**
     * Remove pincode.
     */
    public function removePincode(Pincode $pincode)
    {
        $this->pincodes->removeElement($pincode);
    }

    /**
     * @return mixed
     */
    public function getAutoPrint()
    {
        return $this->autoPrint;
    }

    /**
     * @param mixed $autoPrint
     *
     * @return $this
     */
    public function setAutoPrint($autoPrint)
    {
        $this->autoPrint = $autoPrint;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWebNotification()
    {
        return $this->webNotification;
    }

    /**
     * @param mixed $webNotification
     *
     * @return $this
     */
    public function setWebNotification($webNotification)
    {
        $this->webNotification = $webNotification;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFooterPrint()
    {
        return $this->footerPrint;
    }

    /**
     * @param mixed $footerPrint
     *
     * @return $this
     */
    public function setFooterPrint($footerPrint)
    {
        $this->footerPrint = $footerPrint;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvgReviewScore()
    {
        return $this->avgReviewScore;
    }

    /**
     * @param mixed $avgReviewScore
     *
     * @return $this
     */
    public function setAvgReviewScore($avgReviewScore)
    {
        $this->avgReviewScore = $avgReviewScore;

        return $this;
    }

    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStripeCustomerId()
    {
        return $this->stripeCustomerId;
    }

    /**
     * @param mixed $stripeCustomerId
     *
     * @return $this
     */
    public function setStripeCustomerId($stripeCustomerId)
    {
        $this->stripeCustomerId = $stripeCustomerId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsStripeCustomerActive()
    {
        return $this->isStripeCustomerActive;
    }

    /**
     * @param mixed $isStripeCustomerActive
     *
     * @return $this
     */
    public function setIsStripeCustomerActive($isStripeCustomerActive)
    {
        $this->isStripeCustomerActive = $isStripeCustomerActive;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStripeAccessToken()
    {
        return $this->stripeAccessToken;
    }

    /**
     * @param mixed $stripeAccessToken
     *
     * @return $this
     */
    public function setStripeAccessToken($stripeAccessToken)
    {
        $this->stripeAccessToken = $stripeAccessToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getStripePublishableKey()
    {
        return $this->stripePublishableKey;
    }

    /**
     * @param mixed $stripePublishableKey
     *
     * @return $this
     */
    public function setStripePublishableKey($stripePublishableKey)
    {
        $this->stripePublishableKey = $stripePublishableKey;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAverageMenuPrice()
    {
        return $this->averageMenuPrice;
    }

    /**
     * @param mixed $averageMenuPrice
     *
     * @return $this
     */
    public function setAverageMenuPrice($averageMenuPrice)
    {
        $this->averageMenuPrice = $averageMenuPrice;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClickSpotter()
    {
        return $this->clickSpotter;
    }

    /**
     * @param mixed $clickSpotter
     *
     * @return $this
     */
    public function setClickSpotter($clickSpotter)
    {
        $this->clickSpotter = $clickSpotter;

        return $this;
    }

    public function getUpdated()
    {
        return $this->updated;
    }

    public function setCurrentEvent($event)
    {
        $this->currentEvent = $event;
    }

    /**
     * @return mixed
     */
    public function getAdminComment()
    {
        return $this->adminComment;
    }

    /**
     * @param mixed $adminComment
     *
     * @return $this
     */
    public function setAdminComment($adminComment)
    {
        $this->adminComment = $adminComment;

        return $this;
    }

    public function isMobile()
    {
        return $this->getIsMobile();
    }

    public function isTTT()
    {
        return $this->getIsTtt();
    }

    /**
     * @return mixed
     */
    public function getIsGold()
    {
        return $this->isGold;
    }

    /**
     * @param mixed $isGold
     *
     * @return $this
     */
    public function setIsGold($isGold)
    {
        $this->isGold = $isGold;

        return $this;
    }

    /* A migrer manager */
    public static function getLegalTypeChoices()
    {
        return array(
            1 => 'SARL',
            2 => 'EURL',
            3 => 'SAS',
            4 => 'Entreprise Individuelle',
            5 => 'Auto Entrepreneur',
        );
    }

    public function verboseLegalType()
    {
        if (array_key_exists($this->getLegalType(), $this->getLegalTypeChoices())) {
            return $this->getLegalTypeChoices()[$this->getLegalType()];
        }

        return $this->getLegalTypeChoices()[1];
    }

    public function hasManager(User $user)
    {
        if ($user->hasRole('ROLE_SUPER_ADMIN') || $user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_COMMERCIAL')) {
            return true;
        }

        foreach ($this->getManagers() as $manager) {
            if ($user->getId() == $manager->getId()) {
                return true;
            }
        }

        if ($this->getClient()) {
            foreach ($this->getClient()->getManagers() as $manager) {
                if ($user->getId() == $manager->getId()) {
                    return true;
                }
            }
        }

        return false;
    }

    /* A virer */
    public function hasOrderType($id)
    {
        foreach ($this->getOrderTypes() as $type) {
            if ($type->getId() == $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return mixed
     */
    public function getAvgPriceScore()
    {
        return $this->avgPriceScore;
    }

    /**
     * @param mixed $avgPriceScore
     *
     * @return $this
     */
    public function setAvgPriceScore($avgPriceScore)
    {
        $this->avgPriceScore = $avgPriceScore;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvgCleanScore()
    {
        return $this->avgCleanScore;
    }

    /**
     * @param mixed $avgCleanScore
     *
     * @return $this
     */
    public function setAvgCleanScore($avgCleanScore)
    {
        $this->avgCleanScore = $avgCleanScore;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvgServiceScore()
    {
        return $this->avgServiceScore;
    }

    /**
     * @param mixed $avgServiceScore
     *
     * @return $this
     */
    public function setAvgServiceScore($avgServiceScore)
    {
        $this->avgServiceScore = $avgServiceScore;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvgCookScore()
    {
        return $this->avgCookScore;
    }

    /**
     * @param mixed $avgCookScore
     *
     * @return $this
     */
    public function setAvgCookScore($avgCookScore)
    {
        $this->avgCookScore = $avgCookScore;

        return $this;
    }

    public function getCoverSmall()
    {
        return $this->coverSmall;
    }

    public function setCoverSmall($coverSmall)
    {
        $this->coverSmall = $coverSmall;

        return $this;
    }

    public function getCover()
    {
        return $this->cover;
    }

    public function setCover($cover)
    {
        $this->cover = $cover;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdateStatus()
    {
        return $this->updateStatus;
    }

    /**
     * @param mixed $updateStatus
     *
     * @return $this
     */
    public function setUpdateStatus($updateStatus)
    {
        $this->updateStatus = $updateStatus;

        return $this;
    }

    public function getCoverFull()
    {
        return $this->coverFull;
    }

    public function setCoverFull($coverFull)
    {
        $this->coverFull = $coverFull;

        return $this;
    }

    public function getCoverBig()
    {
        return $this->coverBig;
    }

    public function setCoverBig($coverBig)
    {
        $this->coverBig = $coverBig;

        return $this;
    }

    public function getCoverBigDefault()
    {
        return $this->coverBigDefault;
    }

    public function setCoverBigDefault($coverBigDefault)
    {
        $this->coverBigDefault = $coverBigDefault;

        return $this;
    }

    public function getCoverPicturePath()
    {
        if ($this->getCoverPicture()) {
            return $this->getCoverPicture()->getWebPath();
        } else {
            return $this->getGallery()->getCover()->getWebPath();
        }
    }

    public function getProfilePicturePath()
    {
        if ($this->getProfilePicture()) {
            return $this->getProfilePicture()->getWebPath();
        } else {
            return $this->getGallery()->getCover()->getWebPath();
        }
    }

    public function getDistance()
    {
        return $this->distance;
    }

    public function setDistance($distance)
    {
        $this->distance = $distance;

        return $this;
    }

    public function getPreorderSlots()
    {
        return $this->preorderSlots;
    }

    public function setPreorderSlots($preorderSlots)
    {
        $this->preorderSlots = $preorderSlots;

        return $this;
    }

    public function getPlanning()
    {
        return $this->planning;
    }

    public function setPlanning($planning)
    {
        $this->planning = $planning;

        return $this;
    }

    public function getTodayPlanning()
    {
        return $this->todayPlanning;
    }

    public function setTodayPlanning($todayPlanning)
    {
        $this->todayPlanning = $todayPlanning;

        return $this;
    }

    /**
     * @param mixed $timesheets
     *
     * @return $this
     */
    public function setTimesheets($timesheets)
    {
        $this->timesheets = $timesheets;

        return $this;
    }

    /**
     * @param mixed $timesheetValidations
     *
     * @return $this
     */
    public function setTimesheetValidations($timesheetValidations)
    {
        $this->timesheetValidations = $timesheetValidations;

        return $this;
    }

    /**
     * @param mixed $validationRequests
     *
     * @return $this
     */
    public function setValidationRequests($validationRequests)
    {
        $this->validationRequests = $validationRequests;

        return $this;
    }

    /**
     * @param mixed $planningPrints
     *
     * @return $this
     */
    public function setPlanningPrints($planningPrints)
    {
        $this->planningPrints = $planningPrints;

        return $this;
    }

    /**
     * @param mixed $managers
     *
     * @return $this
     */
    public function setManagers($managers)
    {
        $this->managers = $managers;

        return $this;
    }

    /**
     * @param mixed $paymentMethods
     *
     * @return $this
     */
    public function setPaymentMethods($paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;

        return $this;
    }

    /**
     * @param mixed $storePaymentMethods
     *
     * @return $this
     */
    public function setStorePaymentMethods($storePaymentMethods)
    {
        $this->storePaymentMethods = $storePaymentMethods;

        return $this;
    }

    /**
     * @param mixed $orderTypes
     *
     * @return $this
     */
    public function setOrderTypes($orderTypes)
    {
        $this->orderTypes = $orderTypes;

        return $this;
    }

    /**
     * @param mixed $albums
     *
     * @return $this
     */
    public function setAlbums($albums)
    {
        $this->albums = $albums;

        return $this;
    }

    /**
     * @param mixed $subscriptions
     *
     * @return $this
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;

        return $this;
    }

    /**
     * @param mixed $staffMembers
     *
     * @return $this
     */
    public function setStaffMembers($staffMembers)
    {
        $this->staffMembers = $staffMembers;

        return $this;
    }

    /**
     * @param mixed $orderStatements
     *
     * @return $this
     */
    public function setOrderStatements($orderStatements)
    {
        $this->orderStatements = $orderStatements;

        return $this;
    }

    /**
     * @param mixed $menuFile
     *
     * @return $this
     */
    public function setMenuFile($menuFile)
    {
        $this->menuFile = $menuFile;

        return $this;
    }

    /**
     * @param mixed $userBookmarks
     *
     * @return $this
     */
    public function setUserBookmarks($userBookmarks)
    {
        $this->userBookmarks = $userBookmarks;

        return $this;
    }

    /**
     * @param mixed $orders
     *
     * @return $this
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;

        return $this;
    }

    /**
     * @param mixed $discounts
     *
     * @return $this
     */
    public function setDiscounts($discounts)
    {
        $this->discounts = $discounts;

        return $this;
    }

    /**
     * @param mixed $coupons
     *
     * @return $this
     */
    public function setCoupons($coupons)
    {
        $this->coupons = $coupons;

        return $this;
    }

    /**
     * @param mixed $services
     *
     * @return $this
     */
    public function setServices($services)
    {
        $this->services = $services;

        return $this;
    }

    /**
     * @param mixed $reviews
     *
     * @return $this
     */
    public function setReviews($reviews)
    {
        $this->reviews = $reviews;

        return $this;
    }

    /**
     * @param mixed $socialPosts
     *
     * @return $this
     */
    public function setSocialPosts($socialPosts)
    {
        $this->socialPosts = $socialPosts;

        return $this;
    }

    /**
     * @param mixed $tags
     *
     * @return $this
     */
    public function setTags($tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @param mixed $extraTags
     *
     * @return $this
     */
    public function setExtraTags($extraTags)
    {
        $this->extraTags = $extraTags;

        return $this;
    }

    /**
     * @param mixed $restaurantMenus
     *
     * @return $this
     */
    public function setRestaurantMenus($restaurantMenus)
    {
        $this->restaurantMenus = $restaurantMenus;

        return $this;
    }

    /**
     * @param mixed $productCategories
     *
     * @return $this
     */
    public function setProductCategories($productCategories)
    {
        $this->productCategories = $productCategories;

        return $this;
    }

    /**
     * @param mixed $options
     *
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;

        return $this;
    }

    /**
     * @param mixed $mealSlots
     *
     * @return $this
     */
    public function setMealSlots($mealSlots)
    {
        $this->mealSlots = $mealSlots;

        return $this;
    }

    /**
     * @param mixed $openingTimesheets
     *
     * @return $this
     */
    public function setOpeningTimesheets($openingTimesheets)
    {
        $this->openingTimesheets = $openingTimesheets;

        return $this;
    }

    /**
     * @param mixed $preorderTimesheets
     *
     * @return $this
     */
    public function setPreorderTimesheets($preorderTimesheets)
    {
        $this->preorderTimesheets = $preorderTimesheets;

        return $this;
    }

    /**
     * @param mixed $deliveryTimesheets
     *
     * @return $this
     */
    public function setDeliveryTimesheets($deliveryTimesheets)
    {
        $this->deliveryTimesheets = $deliveryTimesheets;

        return $this;
    }

    /**
     * @param mixed $nextSchedule
     *
     * @return $this
     */
    public function setNextSchedule($nextSchedule)
    {
        $this->nextSchedule = $nextSchedule;

        return $this;
    }

    /**
     * @param mixed $nextDeliverySchedule
     *
     * @return $this
     */
    public function setNextDeliverySchedule($nextDeliverySchedule)
    {
        $this->nextDeliverySchedule = $nextDeliverySchedule;

        return $this;
    }

    public function getReviewData()
    {
        return $this->reviewData;
    }

    public function setReviewData($reviewData)
    {
        $this->reviewData = $reviewData;

        return $this;
    }

    public function getNewsData()
    {
        return $this->newsData;
    }

    public function setNewsData($newsData)
    {
        $this->newsData = $newsData;

        return $this;
    }

    public function getDiscountData()
    {
        return $this->discountData;
    }

    public function setDiscountData($discountData)
    {
        $this->discountData = $discountData;

        return $this;
    }

    public function getDeliveryCarts()
    {
        return $this->deliveryCarts;
    }

    public function setDeliveryCarts($deliveryCarts)
    {
        $this->deliveryCarts = $deliveryCarts;

        return $this;
    }

    public function isPublic()
    {
        return $this->getIsPublic();
    }

    public function isClickeat()
    {
        return $this->getIsClickeat();
    }

    public function setDiscoverFeatures(array $discoverFeatures)
    {
        $this->discoverFeatures = serialize($discoverFeatures);

        return $this;
    }

    public function getDiscoverFeatures()
    {
        if ($this->discoverFeatures) {
            return unserialize($this->discoverFeatures);
        } else {
            return array();
        }
    }

    public function addDiscoverFeature($feature)
    {
        $features = $this->getDiscoverFeatures();
        if (!in_array($feature, $features)) {
            $features[] = $feature;
        }
        $this->setDiscoverFeatures($features);
    }

    public function removeDiscoverFeature($feature)
    {
        $features = $this->getDiscoverFeatures();
        foreach ($features as $key => $currentFeature) {
            if ($feature == $currentFeature) {
                unset($features[$key]);
            }
        }
        $this->setDiscoverFeatures($features);
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set status.
     *
     * @param int $status
     *
     * @return Restaurant
     */
    public function setStatus($status)
    {
        $this->status = $status;

        return $this;
    }

    /**
     * Get status.
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set isPublic.
     *
     * @param bool $isPublic
     *
     * @return Restaurant
     */
    public function setIsPublic($isPublic)
    {
        $this->isPublic = $isPublic;

        return $this;
    }

    /**
     * Get isPublic.
     *
     * @return bool
     */
    public function getIsPublic()
    {
        return $this->isPublic;
    }

    /**
     * Set isClickeat.
     *
     * @param bool $isClickeat
     *
     * @return Restaurant
     */
    public function setIsClickeat($isClickeat)
    {
        $this->isClickeat = $isClickeat;

        return $this;
    }

    /**
     * Get isClickeat.
     *
     * @return bool
     */
    public function getIsClickeat()
    {
        return $this->isClickeat;
    }

    /**
     * Set isTtt.
     *
     * @param bool $isTtt
     *
     * @return Restaurant
     */
    public function setIsTtt($isTtt)
    {
        $this->isTtt = $isTtt;

        return $this;
    }

    /**
     * Get isTtt.
     *
     * @return bool
     */
    public function getIsTtt()
    {
        return $this->isTtt;
    }

    /**
     * Set isPromoted.
     *
     * @param bool $isPromoted
     *
     * @return Restaurant
     */
    public function setIsPromoted($isPromoted)
    {
        $this->isPromoted = $isPromoted;

        return $this;
    }

    /**
     * Get isPromoted.
     *
     * @return bool
     */
    public function getIsPromoted()
    {
        return $this->isPromoted;
    }

    /**
     * Set isOpen.
     *
     * @param bool $isOpen
     *
     * @return Restaurant
     */
    public function setIsOpen($isOpen)
    {
        $this->isOpen = $isOpen;

        return $this;
    }

    /**
     * Get isOpen.
     *
     * @return bool
     */
    public function getIsOpen()
    {
        return $this->isOpen;
    }

    /**
     * Set isOpenDelivery.
     *
     * @param bool $isOpenDelivery
     *
     * @return Restaurant
     */
    public function setIsOpenDelivery($isOpenDelivery)
    {
        $this->isOpenDelivery = $isOpenDelivery;

        return $this;
    }

    /**
     * Get isOpenDelivery.
     *
     * @return bool
     */
    public function getIsOpenDelivery()
    {
        return $this->isOpenDelivery;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return Restaurant
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set slug.
     *
     * @param string $slug
     *
     * @return Restaurant
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    /**
     * Get slug.
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * Set description.
     *
     * @param string $description
     *
     * @return Restaurant
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get description.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set smallDescription.
     *
     * @param string $smallDescription
     *
     * @return Restaurant
     */
    public function setSmallDescription($smallDescription)
    {
        $this->smallDescription = $smallDescription;

        return $this;
    }

    /**
     * Get smallDescription.
     *
     * @return string
     */
    public function getSmallDescription()
    {
        return $this->smallDescription;
    }

    /**
     * Set phone.
     *
     * @param phone_number $phone
     *
     * @return Restaurant
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * Get phone.
     *
     * @return phone_number
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set email.
     *
     * @param string $email
     *
     * @return Restaurant
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email.
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set emailPayment.
     *
     * @param string $emailPayment
     *
     * @return Restaurant
     */
    public function setEmailPayment($emailPayment)
    {
        $this->emailPayment = $emailPayment;

        return $this;
    }

    /**
     * Get emailPayment.
     *
     * @return string
     */
    public function getEmailPayment()
    {
        return $this->emailPayment;
    }

    /**
     * Set website.
     *
     * @param string $website
     *
     * @return Restaurant
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * Get website.
     *
     * @return string
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * Set primaryColor.
     *
     * @param string $primaryColor
     *
     * @return Restaurant
     */
    public function setPrimaryColor($primaryColor)
    {
        $this->primaryColor = $primaryColor;

        return $this;
    }

    /**
     * Get primaryColor.
     *
     * @return string
     */
    public function getPrimaryColor()
    {
        return $this->primaryColor;
    }

    /**
     * Set secondaryColor.
     *
     * @param string $secondaryColor
     *
     * @return Restaurant
     */
    public function setSecondaryColor($secondaryColor)
    {
        $this->secondaryColor = $secondaryColor;

        return $this;
    }

    /**
     * Get secondaryColor.
     *
     * @return string
     */
    public function getSecondaryColor()
    {
        return $this->secondaryColor;
    }

    /**
     * Set managerFirstName.
     *
     * @param string $managerFirstName
     *
     * @return Restaurant
     */
    public function setManagerFirstName($managerFirstName)
    {
        $this->managerFirstName = $managerFirstName;

        return $this;
    }

    /**
     * Get managerFirstName.
     *
     * @return string
     */
    public function getManagerFirstName()
    {
        return $this->managerFirstName;
    }

    /**
     * Set managerName.
     *
     * @param string $managerName
     *
     * @return Restaurant
     */
    public function setManagerName($managerName)
    {
        $this->managerName = $managerName;

        return $this;
    }

    /**
     * Get managerName.
     *
     * @return string
     */
    public function getManagerName()
    {
        return $this->managerName;
    }

    /**
     * Set managerPhone.
     *
     * @param phone_number $managerPhone
     *
     * @return Restaurant
     */
    public function setManagerPhone($managerPhone)
    {
        $this->managerPhone = $managerPhone;

        return $this;
    }

    /**
     * Get managerPhone.
     *
     * @return phone_number
     */
    public function getManagerPhone()
    {
        return $this->managerPhone;
    }

    /**
     * Set managerEmail.
     *
     * @param string $managerEmail
     *
     * @return Restaurant
     */
    public function setManagerEmail($managerEmail)
    {
        $this->managerEmail = $managerEmail;

        return $this;
    }

    /**
     * Get managerEmail.
     *
     * @return string
     */
    public function getManagerEmail()
    {
        return $this->managerEmail;
    }

    /**
     * Set orderDelay.
     *
     * @param int $orderDelay
     *
     * @return Restaurant
     */
    public function setOrderDelay($orderDelay)
    {
        $this->orderDelay = $orderDelay;

        return $this;
    }

    /**
     * Get orderDelay.
     *
     * @return int
     */
    public function getOrderDelay()
    {
        return $this->orderDelay;
    }

    /**
     * Set orderStart.
     *
     * @param int $orderStart
     *
     * @return Restaurant
     */
    public function setOrderStart($orderStart)
    {
        $this->orderStart = $orderStart;

        return $this;
    }

    /**
     * Get orderStart.
     *
     * @return int
     */
    public function getOrderStart()
    {
        return $this->orderStart;
    }

    /**
     * Set maxOrderBySlot.
     *
     * @param int $maxOrderBySlot
     *
     * @return Restaurant
     */
    public function setMaxOrderBySlot($maxOrderBySlot)
    {
        $this->maxOrderBySlot = $maxOrderBySlot;

        return $this;
    }

    /**
     * Get maxOrderBySlot.
     *
     * @return int
     */
    public function getMaxOrderBySlot()
    {
        return $this->maxOrderBySlot;
    }

    /**
     * Set maxOrderPriceBySlot.
     *
     * @param int $maxOrderPriceBySlot
     *
     * @return Restaurant
     */
    public function setMaxOrderPriceBySlot($maxOrderPriceBySlot)
    {
        $this->maxOrderPriceBySlot = $maxOrderPriceBySlot;

        return $this;
    }

    /**
     * Get maxOrderPriceBySlot.
     *
     * @return int
     */
    public function getMaxOrderPriceBySlot()
    {
        return $this->maxOrderPriceBySlot;
    }

    /**
     * Set averagePrice.
     *
     * @param int $averagePrice
     *
     * @return Restaurant
     */
    public function setAveragePrice($averagePrice)
    {
        $this->averagePrice = $averagePrice;

        return $this;
    }

    /**
     * Get averagePrice.
     *
     * @return int
     */
    public function getAveragePrice()
    {
        return $this->averagePrice;
    }

    /**
     * Set isMobile.
     *
     * @param bool $isMobile
     *
     * @return Restaurant
     */
    public function setIsMobile($isMobile)
    {
        $this->isMobile = $isMobile;

        return $this;
    }

    /**
     * Get isMobile.
     *
     * @return bool
     */
    public function getIsMobile()
    {
        return $this->isMobile;
    }

    /**
     * Set source.
     *
     * @param string $source
     *
     * @return Restaurant
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * Get source.
     *
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * Set pipedriveId.
     *
     * @param int $pipedriveId
     *
     * @return Restaurant
     */
    public function setPipedriveId($pipedriveId)
    {
        $this->pipedriveId = $pipedriveId;

        return $this;
    }

    /**
     * Get pipedriveId.
     *
     * @return int
     */
    public function getPipedriveId()
    {
        return $this->pipedriveId;
    }

    /**
     * Set pipedriveDealId.
     *
     * @param int $pipedriveDealId
     *
     * @return Restaurant
     */
    public function setPipedriveDealId($pipedriveDealId)
    {
        $this->pipedriveDealId = $pipedriveDealId;

        return $this;
    }

    /**
     * Get pipedriveDealId.
     *
     * @return int
     */
    public function getPipedriveDealId()
    {
        return $this->pipedriveDealId;
    }

    /**
     * Set pipedriveContactId.
     *
     * @param int $pipedriveContactId
     *
     * @return Restaurant
     */
    public function setPipedriveContactId($pipedriveContactId)
    {
        $this->pipedriveContactId = $pipedriveContactId;

        return $this;
    }

    /**
     * Get pipedriveContactId.
     *
     * @return int
     */
    public function getPipedriveContactId()
    {
        return $this->pipedriveContactId;
    }

    /**
     * Set restoflashId.
     *
     * @param int $restoflashId
     *
     * @return Restaurant
     */
    public function setRestoflashId($restoflashId)
    {
        $this->restoflashId = $restoflashId;

        return $this;
    }

    /**
     * Get restoflashId.
     *
     * @return int
     */
    public function getRestoflashId()
    {
        return $this->restoflashId;
    }

    /**
     * Set notificationMails.
     *
     * @param string $notificationMails
     *
     * @return Restaurant
     */
    public function setNotificationMails($notificationMails)
    {
        $this->notificationMails = $notificationMails;

        return $this;
    }

    /**
     * Get notificationMails.
     *
     * @return string
     */
    public function getNotificationMails()
    {
        return $this->notificationMails;
    }

    /**
     * Set storeCreation.
     *
     * @param \DateTime $storeCreation
     *
     * @return Restaurant
     */
    public function setStoreCreation($storeCreation)
    {
        $this->storeCreation = $storeCreation;

        return $this;
    }

    /**
     * Get storeCreation.
     *
     * @return \DateTime
     */
    public function getStoreCreation()
    {
        return $this->storeCreation;
    }

    /**
     * Set storeClosing.
     *
     * @param \DateTime $storeClosing
     *
     * @return Restaurant
     */
    public function setStoreClosing($storeClosing)
    {
        $this->storeClosing = $storeClosing;

        return $this;
    }

    /**
     * Get storeClosing.
     *
     * @return \DateTime
     */
    public function getStoreClosing()
    {
        return $this->storeClosing;
    }

    /**
     * Set legalType.
     *
     * @param int $legalType
     *
     * @return Restaurant
     */
    public function setLegalType($legalType)
    {
        $this->legalType = $legalType;

        return $this;
    }

    /**
     * Get legalType.
     *
     * @return int
     */
    public function getLegalType()
    {
        return $this->legalType;
    }

    /**
     * Set legalName.
     *
     * @param string $legalName
     *
     * @return Restaurant
     */
    public function setLegalName($legalName)
    {
        $this->legalName = $legalName;

        return $this;
    }

    /**
     * Get legalName.
     *
     * @return string
     */
    public function getLegalName()
    {
        return $this->legalName;
    }

    /**
     * Set legalPerson.
     *
     * @param string $legalPerson
     *
     * @return Restaurant
     */
    public function setLegalPerson($legalPerson)
    {
        $this->legalPerson = $legalPerson;

        return $this;
    }

    /**
     * Get legalPerson.
     *
     * @return string
     */
    public function getLegalPerson()
    {
        return $this->legalPerson;
    }

    /**
     * Set siret.
     *
     * @param string $siret
     *
     * @return Restaurant
     */
    public function setSiret($siret)
    {
        $this->siret = $siret;

        return $this;
    }

    /**
     * Get siret.
     *
     * @return string
     */
    public function getSiret()
    {
        return $this->siret;
    }

    /**
     * Set capital.
     *
     * @param string $capital
     *
     * @return Restaurant
     */
    public function setCapital($capital)
    {
        $this->capital = $capital;

        return $this;
    }

    /**
     * Get capital.
     *
     * @return string
     */
    public function getCapital()
    {
        return $this->capital;
    }

    /**
     /**
     * Set lastFacebookRatingCheck.
     *
     * @param \DateTime $lastFacebookRatingCheck
     *
     * @return Restaurant
     */
    public function setLastFacebookRatingCheck($lastFacebookRatingCheck)
    {
        $this->lastFacebookRatingCheck = $lastFacebookRatingCheck;

        return $this;
    }

    /**
     * Get lastFacebookRatingCheck.
     *
     * @return \DateTime
     */
    public function getLastFacebookRatingCheck()
    {
        return $this->lastFacebookRatingCheck;
    }

    /**
     * Set iframeNoClient.
     *
     * @param bool $iframeNoClient
     *
     * @return Restaurant
     */
    public function setIframeNoClient($iframeNoClient)
    {
        $this->iframeNoClient = $iframeNoClient;

        return $this;
    }

    /**
     * Get iframeNoClient.
     *
     * @return bool
     */
    public function getIframeNoClient()
    {
        return $this->iframeNoClient;
    }

    /**
     * Set address.
     *
     * @param \Clab\LocationBundle\Entity\Address $address
     *
     * @return Restaurant
     */
    public function setAddress(\Clab\LocationBundle\Entity\Address $address = null)
    {
        $this->address = $address;

        return $this;
    }

    /**
     * Get address.
     *
     * @return \Clab\LocationBundle\Entity\Address
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Add timesheet.
     *
     * @param \Clab\RestaurantBundle\Entity\TimeSheet $timesheet
     *
     * @return Restaurant
     */
    public function addTimesheet(TimeSheet $timesheet)
    {
        $this->timesheets[] = $timesheet;

        return $this;
    }

    /**
     * Remove timesheet.
     *
     * @param \Clab\RestaurantBundle\Entity\TimeSheet $timesheet
     */
    public function removeTimesheet(TimeSheet $timesheet)
    {
        $this->timesheets->removeElement($timesheet);
    }

    /**
     * Get timesheets.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTimesheets()
    {
        return $this->timesheets;
    }

    /**
     * Add timesheetValidation.
     *
     * @param \Clab\RestaurantBundle\Entity\TimesheetValidation $timesheetValidation
     *
     * @return Restaurant
     */
    public function addTimesheetValidation(TimesheetValidation $timesheetValidation)
    {
        $this->timesheetValidations[] = $timesheetValidation;

        return $this;
    }

    /**
     * Remove timesheetValidation.
     *
     * @param \Clab\RestaurantBundle\Entity\TimesheetValidation $timesheetValidation
     */
    public function removeTimesheetValidation(TimesheetValidation $timesheetValidation)
    {
        $this->timesheetValidations->removeElement($timesheetValidation);
    }

    /**
     * Get timesheetValidations.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTimesheetValidations()
    {
        return $this->timesheetValidations;
    }

    /**
     * Get validationRequests.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getValidationRequests()
    {
        return $this->validationRequests;
    }

     /**
     * Get planningPrints.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPlanningPrints()
    {
        return $this->planningPrints;
    }

    /**
     * Set client.
     *
     * @param \Clab\BoardBundle\Entity\Client $client
     *
     * @return Restaurant
     */
    public function setClient(Client $client = null)
    {
        $this->client = $client;

        return $this;
    }

    /**
     * Get client.
     *
     * @return \Clab\BoardBundle\Entity\Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * Set clientConfiguration.
     *
     * @param \Clab\BoardBundle\Entity\ClientConfiguration $clientConfiguration
     *
     * @return Restaurant
     */
    public function setClientConfiguration(ClientConfiguration $clientConfiguration = null)
    {
        $this->clientConfiguration = $clientConfiguration;

        return $this;
    }

    /**
     * Get clientConfiguration.
     *
     * @return \Clab\BoardBundle\Entity\ClientConfiguration
     */
    public function getClientConfiguration()
    {
        return $this->clientConfiguration;
    }

    /**
     * Set owner.
     *
     * @param \Clab\UserBundle\Entity\User $owner
     *
     * @return Restaurant
     */
    public function setOwner(User $owner = null)
    {
        $this->owner = $owner;

        return $this;
    }

    /**
     * Get owner.
     *
     * @return \Clab\UserBundle\Entity\User
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set commercial.
     *
     * @param \Clab\UserBundle\Entity\User $commercial
     *
     * @return Restaurant
     */
    public function setCommercial(User $commercial = null)
    {
        $this->commercial = $commercial;

        return $this;
    }

    /**
     * Get commercial.
     *
     * @return \Clab\UserBundle\Entity\User
     */
    public function getCommercial()
    {
        return $this->commercial;
    }

    /**
     * Add manager.
     *
     * @param \Clab\UserBundle\Entity\User $manager
     *
     * @return Restaurant
     */
    public function addManager(User $manager)
    {
        $this->managers[] = $manager;

        return $this;
    }

    /**
     * Remove manager.
     *
     * @param \Clab\UserBundle\Entity\User $manager
     */
    public function removeManager(User $manager)
    {
        $this->managers->removeElement($manager);
    }

    /**
     * Get managers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getManagers()
    {
        return $this->managers;
    }

    /**
     * Add paymentMethod.
     *
     * @param \Clab\ShopBundle\Entity\PaymentMethod $paymentMethod
     *
     * @return Restaurant
     */
    public function addPaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethods[] = $paymentMethod;

        return $this;
    }

    /**
     * Remove paymentMethod.
     *
     * @param \Clab\ShopBundle\Entity\PaymentMethod $paymentMethod
     */
    public function removePaymentMethod(PaymentMethod $paymentMethod)
    {
        $this->paymentMethods->removeElement($paymentMethod);
    }

    /**
     * Get paymentMethods.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPaymentMethods()
    {
        return $this->paymentMethods;
    }

    /**
     * Add storePaymentMethod.
     *
     * @param \Clab\ShopBundle\Entity\PaymentMethod $storePaymentMethod
     *
     * @return Restaurant
     */
    public function addStorePaymentMethod(PaymentMethod $storePaymentMethod)
    {
        $this->storePaymentMethods[] = $storePaymentMethod;

        return $this;
    }

    /**
     * Remove storePaymentMethod.
     *
     * @param \Clab\ShopBundle\Entity\PaymentMethod $storePaymentMethod
     */
    public function removeStorePaymentMethod(PaymentMethod $storePaymentMethod)
    {
        $this->storePaymentMethods->removeElement($storePaymentMethod);
    }

    /**
     * Get storePaymentMethods.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStorePaymentMethods()
    {
        return $this->storePaymentMethods;
    }

    /**
     * Add orderType.
     *
     * @param \Clab\ShopBundle\Entity\OrderType $orderType
     *
     * @return Restaurant
     */
    public function addOrderType(OrderType $orderType)
    {
        if (!$this->orderTypes->contains($orderType)) {
            $this->orderTypes[] = $orderType;
        }

        return $this;
    }

    /**
     * Remove orderType.
     *
     * @param \Clab\ShopBundle\Entity\OrderType $orderType
     */
    public function removeOrderType(OrderType $orderType)
    {
        $this->orderTypes->removeElement($orderType);
    }

    /**
     * Get orderTypes.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderTypes()
    {
        return $this->orderTypes;
    }

    /**
     * Set gallery.
     *
     * @param \Clab\MediaBundle\Entity\Gallery $gallery
     *
     * @return Restaurant
     */
    public function setGallery(Gallery $gallery = null)
    {
        $this->gallery = $gallery;

        return $this;
    }

    /**
     * Get gallery.
     *
     * @return \Clab\MediaBundle\Entity\Gallery
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * @return mixed
     */
    public function getGalleryMenu()
    {
        return $this->galleryMenu;
    }

    /**
     * @param mixed $galleryMenu
     *
     * @return $this
     */
    public function setGalleryMenu($galleryMenu)
    {
        $this->galleryMenu = $galleryMenu;

        return $this;
    }

    /**
     * Set galleryBig.
     *
     * @param \Clab\MediaBundle\Entity\Gallery $galleryBig
     *
     * @return Restaurant
     */
    public function setGalleryBig(Gallery $galleryBig = null)
    {
        $this->galleryBig = $galleryBig;

        return $this;
    }

    /**
     * Get galleryBig.
     *
     * @return \Clab\MediaBundle\Entity\Gallery
     */
    public function getGalleryBig()
    {
        return $this->galleryBig;
    }

    /**
     * Add album.
     *
     * @param \Clab\MediaBundle\Entity\Album $album
     *
     * @return Restaurant
     */
    public function addAlbum(Album $album)
    {
        $this->albums[] = $album;

        return $this;
    }

    /**
     * Remove album.
     *
     * @param \Clab\MediaBundle\Entity\Album $album
     */
    public function removeAlbum(Album $album)
    {
        $this->albums->removeElement($album);
    }

    /**
     * Get albums.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAlbums()
    {
        return $this->albums;
    }

    /**
     * Set subscriptionTerms.
     *
     * @param \Clab\BoardBundle\Entity\SubscriptionTerms $subscriptionTerms
     *
     * @return Restaurant
     */
    public function setSubscriptionTerms(SubscriptionTerms $subscriptionTerms = null)
    {
        $this->subscriptionTerms = $subscriptionTerms;

        return $this;
    }

    /**
     * Get subscriptionTerms.
     *
     * @return \Clab\BoardBundle\Entity\SubscriptionTerms
     */
    public function getSubscriptionTerms()
    {
        return $this->subscriptionTerms;
    }

    /**
     * Add subscription.
     *
     * @param \Clab\BoardBundle\Entity\Subscription $subscription
     *
     * @return Restaurant
     */
    public function addSubscription(Subscription $subscription)
    {
        $this->subscriptions[] = $subscription;

        return $this;
    }

    /**
     * Remove subscription.
     *
     * @param \Clab\BoardBundle\Entity\Subscription $subscription
     */
    public function removeSubscription(Subscription $subscription)
    {
        $this->subscriptions->removeElement($subscription);
    }

    /**
     * Get subscriptions.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * Add staffMember.
     *
     * @param \Clab\RestaurantBundle\Entity\StaffMember $staffMember
     *
     * @return Restaurant
     */
    public function addStaffMember(\Clab\RestaurantBundle\Entity\StaffMember $staffMember)
    {
        $this->staffMembers[] = $staffMember;

        return $this;
    }

    /**
     * Remove staffMember.
     *
     * @param \Clab\RestaurantBundle\Entity\StaffMember $staffMember
     */
    public function removeStaffMember(StaffMember $staffMember)
    {
        $this->staffMembers->removeElement($staffMember);
    }

    /**
     * Get staffMembers.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getStaffMembers()
    {
        return $this->staffMembers;
    }

    /**
     * Set legalAddress.
     *
     * @param \Clab\LocationBundle\Entity\Address $legalAddress
     *
     * @return Restaurant
     */
    public function setLegalAddress(Address $legalAddress = null)
    {
        $this->legalAddress = $legalAddress;

        return $this;
    }

    /**
     * Get legalAddress.
     *
     * @return \Clab\LocationBundle\Entity\Address
     */
    public function getLegalAddress()
    {
        return $this->legalAddress;
    }

    /**
     * Add orderStatement.
     *
     * @param \Clab\BoardBundle\Entity\OrderStatement $orderStatement
     *
     * @return Restaurant
     */
    public function addOrderStatement(OrderStatement $orderStatement)
    {
        $this->orderStatements[] = $orderStatement;

        return $this;
    }

    /**
     * Remove orderStatement.
     *
     * @param \Clab\BoardBundle\Entity\OrderStatement $orderStatement
     */
    public function removeOrderStatement(OrderStatement $orderStatement)
    {
        $this->orderStatements->removeElement($orderStatement);
    }

    /**
     * Get orderStatements.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrderStatements()
    {
        return $this->orderStatements;
    }

    /**
     * Add order.
     *
     * @param \Clab\ShopBundle\Entity\OrderDetail $order
     *
     * @return Restaurant
     */
    public function addOrder(OrderDetail $order)
    {
        $this->orders[] = $order;

        return $this;
    }

    /**
     * Remove order.
     *
     * @param \Clab\ShopBundle\Entity\OrderDetail $order
     */
    public function removeOrder(OrderDetail $order)
    {
        $this->orders->removeElement($order);
    }

    /**
     * Get orders.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Add discount.
     *
     * @param \Clab\ShopBundle\Entity\Discount $discount
     *
     * @return Restaurant
     */
    public function addDiscount(Discount $discount)
    {
        $this->discounts[] = $discount;

        return $this;
    }

    /**
     * Remove discount.
     *
     * @param \Clab\ShopBundle\Entity\Discount $discount
     */
    public function removeDiscount(Discount $discount)
    {
        $this->discounts->removeElement($discount);
    }

    /**
     * Get discounts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * Add coupon.
     *
     * @param \Clab\ShopBundle\Entity\Coupon $coupon
     *
     * @return Restaurant
     */
    public function addCoupon(Coupon $coupon)
    {
        $this->coupons[] = $coupon;

        return $this;
    }

    /**
     * Remove coupon.
     *
     * @param \Clab\ShopBundle\Entity\Coupon $coupon
     */
    public function removeCoupon(Coupon $coupon)
    {
        $this->coupons->removeElement($coupon);
    }

    /**
     * Get coupons.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getCoupons()
    {
        return $this->coupons;
    }

    /**
     * Add service.
     *
     * @param \Clab\RestaurantBundle\Entity\RestaurantService $service
     *
     * @return Restaurant
     */
    public function addService(RestaurantService $service)
    {
        $this->services[] = $service;

        return $this;
    }

    /**
     * Remove service.
     *
     * @param \Clab\RestaurantBundle\Entity\RestaurantService $service
     */
    public function removeService(RestaurantService $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Add review.
     *
     * @param \Clab\ReviewBundle\Entity\Review $review
     *
     * @return Restaurant
     */
    public function addReview(Review $review)
    {
        $this->reviews[] = $review;

        return $this;
    }

    /**
     * Remove review.
     *
     * @param \Clab\ReviewBundle\Entity\Review $review
     */
    public function removeReview(Review $review)
    {
        $this->reviews->removeElement($review);
    }

    /**
     * Get reviews.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    /**
     * Set socialProfile.
     *
     * @param \Clab\SocialBundle\Entity\SocialProfile $socialProfile
     *
     * @return Restaurant
     */
    public function setSocialProfile(SocialProfile $socialProfile = null)
    {
        $this->socialProfile = $socialProfile;

        return $this;
    }

    /**
     * Get socialProfile.
     *
     * @return \Clab\SocialBundle\Entity\SocialProfile
     */
    public function getSocialProfile()
    {
        return $this->socialProfile;
    }

    /**
     * Set facebookPage.
     *
     * @param \Clab\SocialBundle\Entity\SocialFacebookPage $facebookPage
     *
     * @return Restaurant
     */
    public function setFacebookPage(SocialFacebookPage $facebookPage = null)
    {
        $this->facebookPage = $facebookPage;

        return $this;
    }

    /**
     * Get facebookPage.
     *
     * @return \Clab\SocialBundle\Entity\SocialFacebookPage
     */
    public function getFacebookPage()
    {
        return $this->facebookPage;
    }

    /**
     * Add socialPost.
     *
     * @param \Clab\SocialBundle\Entity\SocialPost $socialPost
     *
     * @return Restaurant
     */
    public function addSocialPost(SocialPost $socialPost)
    {
        $this->socialPosts[] = $socialPost;

        return $this;
    }

    /**
     * Remove socialPost.
     *
     * @param \Clab\SocialBundle\Entity\SocialPost $socialPost
     */
    public function removeSocialPost(SocialPost $socialPost)
    {
        $this->socialPosts->removeElement($socialPost);
    }

    /**
     * Get socialPosts.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getSocialPosts()
    {
        return $this->socialPosts;
    }

    /**
     * Add tag.
     *
     * @param \Clab\TaxonomyBundle\Entity\Term $tag
     *
     * @return Restaurant
     */
    public function addTag(Term $tag)
    {
        $this->tags[] = $tag;

        return $this;
    }

    /**
     * Remove tag.
     *
     * @param \Clab\TaxonomyBundle\Entity\Term $tag
     */
    public function removeTag(Term $tag)
    {
        $this->tags->removeElement($tag);
    }

    /**
     * Get tags.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Add extraTag.
     *
     * @param \Clab\TaxonomyBundle\Entity\Term $extraTag
     *
     * @return Restaurant
     */
    public function addExtraTag(Term $extraTag)
    {
        $this->extraTags[] = $extraTag;

        return $this;
    }

    /**
     * Remove extraTag.
     *
     * @param \Clab\TaxonomyBundle\Entity\Term $extraTag
     */
    public function removeExtraTag(Term $extraTag)
    {
        $this->extraTags->removeElement($extraTag);
    }

    /**
     * Get extraTags.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getExtraTags()
    {
        return $this->extraTags;
    }

    /**
     * Add restaurantMenu.
     *
     * @param \Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu
     *
     * @return Restaurant
     */
    public function addRestaurantMenu(RestaurantMenu $restaurantMenu)
    {
        $this->restaurantMenus[] = $restaurantMenu;

        return $this;
    }

    /**
     * Remove restaurantMenu.
     *
     * @param \Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu
     */
    public function removeRestaurantMenu(RestaurantMenu $restaurantMenu)
    {
        $this->restaurantMenus->removeElement($restaurantMenu);
    }

    /**
     * Get restaurantMenus.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getRestaurantMenus()
    {
        return $this->restaurantMenus;
    }

    /**
     * Add productCategory.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductCategory $productCategory
     *
     * @return Restaurant
     */
    public function addProductCategory(ProductCategory $productCategory)
    {
        $this->productCategories[] = $productCategory;

        return $this;
    }

    /**
     * Remove productCategory.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductCategory $productCategory
     */
    public function removeProductCategory(ProductCategory $productCategory)
    {
        $this->productCategories->removeElement($productCategory);
    }

    /**
     * Get productCategories.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getProductCategories()
    {
        return $this->productCategories;
    }

    /**
     * Add option.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductOption $option
     *
     * @return Restaurant
     */
    public function addOption(ProductOption $option)
    {
        $this->options[] = $option;

        return $this;
    }

    /**
     * Remove option.
     *
     * @param \Clab\RestaurantBundle\Entity\ProductOption $option
     */
    public function removeOption(ProductOption $option)
    {
        $this->options->removeElement($option);
    }

    /**
     * Get options.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * Add mealSlot.
     *
     * @param \Clab\RestaurantBundle\Entity\MealSlot $mealSlot
     *
     * @return Restaurant
     */
    public function addMealSlot(MealSlot $mealSlot)
    {
        $this->mealSlots[] = $mealSlot;

        return $this;
    }

    /**
     * Remove mealSlot.
     *
     * @param \Clab\RestaurantBundle\Entity\MealSlot $mealSlot
     */
    public function removeMealSlot(MealSlot $mealSlot)
    {
        $this->mealSlots->removeElement($mealSlot);
    }

    /**
     * Get mealSlots.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getMealSlots()
    {
        return $this->mealSlots;
    }

    public function setPrintImageFile(File $image = null)
    {
        $this->printImageFile = $image;

        if ($image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updated = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return File
     */
    public function getPrintImageFile()
    {
        return $this->printImageFile;
    }

    /**
     * @return File
     */
    public function getLogoMcFile()
    {
        return $this->logoMcFile;
    }

    /**
     * @param File $logoMc
     *
     * @return $this
     */
    public function setLogoMcFile($image)
    {
        $this->logoMcFile = $image;

        if ($image) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updated = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getLogoMcName()
    {
        return $this->logoMcName;
    }

    /**
     * @param string $logoMcName
     *
     * @return $this
     */
    public function setLogoMcName($logoMcName)
    {
        $this->logoMcName = $logoMcName;

        return $this;
    }

    /**
     * @param string $imageName
     *
     * @return Product
     */
    public function setPrintImageName($imageName)
    {
        $this->printImageName = $imageName;

        return $this;
    }

    /**
     * @return string
     */
    public function getPrintImageName()
    {
        return $this->printImageName;
    }

    /**
     * Set nearestSubways.
     *
     * @param array $nearestSubways
     *
     * @return Restaurant
     */
    public function setNearestSubways($nearestSubways)
    {
        $this->nearestSubways = $nearestSubways;

        return $this;
    }

    /**
     * Add additionalSale.
     *
     * @param \Clab\BoardBundle\Entity\AdditionalSale $additionalSale
     *
     * @return Restaurant
     */
    public function addAdditionalSale(\Clab\BoardBundle\Entity\AdditionalSale $additionalSale)
    {
        $this->additionalSales[] = $additionalSale;

        return $this;
    }

    /**
     * Remove additionalSale.
     *
     * @param \Clab\BoardBundle\Entity\AdditionalSale $additionalSale
     */
    public function removeAdditionalSale(\Clab\BoardBundle\Entity\AdditionalSale $additionalSale)
    {
        $this->additionalSales->removeElement($additionalSale);
    }

    /**
     * Get additionalSales.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAdditionalSales()
    {
        return $this->additionalSales;
    }

    /**
     * Add deliverySchedule.
     *
     * @param \Clab\DeliveryBundle\Entity\DeliverySchedule $deliverySchedule
     *
     * @return Restaurant
     */
    public function addDeliveryschedule(\Clab\DeliveryBundle\Entity\DeliverySchedule $deliverySchedule)
    {
        $this->deliverySchedules[] = $deliverySchedule;

        return $this;
    }

    /**
     * Remove deliverySchedule.
     *
     * @param \Clab\DeliveryBundle\Entity\DeliverySchedule
     */
    public function removeDeliveryschedule(\Clab\DeliveryBundle\Entity\DeliverySchedule $deliverySchedule)
    {
        $this->deliverySchedules->removeElement($deliverySchedule);
    }

    /**
     * Get deliverySchedules.
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDeliverySchedules()
    {
        return $this->deliverySchedules;
    }

    /**
     * @return mixed
     */
    public function getFlatTimeSheet()
    {
        return $this->flatTimeSheet;
    }

    /**
     * @param mixed $flatTimeSheet
     */
    public function setFlatTimeSheet(array $flatTimeSheet)
    {
        $this->flatTimeSheet = $flatTimeSheet;
    }

    /**
     * @return mixed
     */
    public function getFlatTags()
    {
        return $this->flatTags;
    }

    /**
     * @param mixed $flatTags
     */
    public function setFlatTags(array $flatTags)
    {
        $this->flatTags = $flatTags;
    }

    /**
     * @return mixed
     */
    public function getActiveDiscount()
    {
        return $this->activeDiscount;
    }

    /**
     * @param mixed $activeDiscount
     */
    public function setActiveDiscount(array $activeDiscount)
    {
        $this->activeDiscount = $activeDiscount;
    }

    public function getBestReview()
    {
        return $this->bestReview;
    }

    /**
     * @param mixed $bestReview
     */
    public function setBestReview($bestReview)
    {
        $this->bestReview = $bestReview;
    }

    /**
     * Set deal.
     *
     * @param \Clab\RestaurantBundle\Entity\Deal $deal
     *
     * @return Restaurant
     */
    public function setDeal(Deal $deal = null)
    {
        $this->deal = $deal;
        return $this;
    }
    /**
     * Get deal.
     *
     * @return \Clab\RestaurantBundle\Entity\Deal
     */
    public function getDeal()
    {
        return $this->deal;
    }

    public function setLastSyncCatalog($lastSyncCatalog){
        $this->lastSyncCatalog = $lastSyncCatalog;
    }
    public function getLastSyncCatalog(){
        return $this->lastSyncCatalog;
    }

    /**
     * @return string
     */
    public function getApiId()
    {
        return $this->apiId;
    }

    /**
     * @param string $apiId
     */
    public function setApiId($apiId)
    {
        $this->apiId = $apiId;
    }

    public function getCheckerBoardConfig()
    {
        return empty($this->checkerBoardConfig) ? [] : $this->checkerBoardConfig;
    }

    public function setCheckerBoardConfig(array $checkerBoardConfig)
    {
        $this->checkerBoardConfig = $checkerBoardConfig;

        return $this;
    }

    public function getHasTicketRestoScan()
    {
        return $this->hasTicketRestoScan;
    }

    public function setHasTicketRestoScan($hasTicketRestoScan)
    {
        $this->hasTicketRestoScan = $hasTicketRestoScan;
    }

    public function getCaisseTags()
    {
        if (empty($this->caisseTags)) {
            return null;
        }

        return $this->caisseTags;
    }

    public function setCaisseTags($caisseTags)
    {
        if (!is_array($caisseTags)) {
            $caisseTags = json_decode($caisseTags);
        }

        $this->caisseTags = $caisseTags;
    }

    public function getCaisseDiscountsLabels()
    {
        if (empty($this->caisseDiscountsLabels)) {
            return null;
        }

        return $this->caisseDiscountsLabels;
    }

    public function setCaisseDiscountsLabels($caisseDiscountsLabels)
    {
        if (!is_array($caisseDiscountsLabels)) {
            $caisseDiscountsLabels = json_decode($caisseDiscountsLabels);
        }
        $this->caisseDiscountsLabels = $caisseDiscountsLabels;
    }

    public function getCaissePrinterLabels()
    {
        if (empty($this->caissePrinterLabels)) {
            return null;
        }

        return $this->caissePrinterLabels;
    }

    public function getFormatedCaissePrinterLabels()
    {
        if (empty($this->caissePrinterLabels)) {
            return null;
        }
        $return = array();
        foreach ($this->caissePrinterLabels as $printerLabel) {
            $return[] = [
                "printerLabel" => $printerLabel['printerLabel'],
                "printerId" => $printerLabel['printerId']
            ];
        }
        return $return;
    }

    public function setCaissePrinterLabels($caissePrinterLabels)
    {
        if (!is_array($caissePrinterLabels)) {
            $caissePrinterLabels = json_decode($caissePrinterLabels);
        }

        $this->caissePrinterLabels = $caissePrinterLabels;
    }
}
