<?php

namespace Clab\UserBundle\Entity;

use Clab\RestaurantBundle\Entity\Product;
use Clab\RestaurantBundle\Entity\Restaurant;
use Clab\ReviewBundle\Entity\Vote;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Clab\MediaBundle\Entity\GalleryOwnerInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 * @ORM\HasLifecycleCallbacks
 * @ORM\Entity(repositoryClass="Clab\UserBundle\Repository\UserRepository")
 */
class User extends BaseUser implements GalleryOwnerInterface
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="fastmag_id", type="integer", nullable=true)
     */
    protected $fastmagId;

    /**
     * @ORM\OneToMany(targetEntity="Clab\ApiBundle\Entity\Session", mappedBy="user")
     */
    protected $sessions;

    /**
     * @ORM\Column(name="facebookId", type="string", length=255, nullable=true)
     */
    protected $facebookId;

    /**
     * @ORM\Column(name="facebookAccessToken", type="string", length=500, nullable=true)
     */
    protected $facebookAccessToken;

    /**
     * @ORM\Column(name="instagramAccessToken", type="string", nullable=true)
     */
    protected $instagramAccessToken;

    /**
     * @ORM\Column(name="instagramUserId", type="string", nullable=true)
     */
    protected $instagramUserId;

    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="sponsored")
     * @ORM\JoinColumn(name="sponsor_id", referencedColumnName="id", nullable=true)
     */
    protected $sponsor;

    /**
     * @ORM\OneToMany(targetEntity="User", mappedBy="sponsor")
     */
    protected $sponsored;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", mappedBy="managers")
     */
    protected $restaurants;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\BoardBundle\Entity\Client", mappedBy="managers")
     */
    protected $clients;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\LocationBundle\Entity\Place", mappedBy="managers")
     */
    protected $places;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\LocationBundle\Entity\Event", mappedBy="managers")
     */
    protected $events;

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
     * @ORM\Column(name="pipedriveId", type="integer", nullable=true)
     */
    protected $pipedriveId;

    /**
     * @ORM\Column(type="string", length=255, name="image", nullable=true)
     */
    protected $image;

    protected $file;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected $gallery;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $favorites;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $favoriteProducts;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    protected $discounts;

    /**
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $first_name;

    /**
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $last_name;

    /**
     * @ORM\Column(name="is_male", type="boolean", nullable=true)
     */
    protected $is_male;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $phone;

    /**
     * @ORM\Column(name="birthday", type="date", nullable=true)
     */
    protected $birthday;

    /**
     * @ORM\Column(name="zipcode", type="integer", nullable=true)
     */
    protected $zipcode;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="home_address_id", referencedColumnName="id", nullable=true)
     */
    protected $homeAddress;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\LocationBundle\Entity\Address", cascade={"persist", "remove"})
     * @ORM\JoinColumn(name="job_address_id", referencedColumnName="id", nullable=true)
     */
    protected $jobAddress;

    /**
     * @ORM\OneToMany(targetEntity="Clab\LocationBundle\Entity\Address", mappedBy="user")
     * @ORM\OrderBy({"id" = "desc"})
     */
    protected $addresses;

    /**
     * @ORM\OneToMany(targetEntity="Clab\ShopBundle\Entity\OrderDetail", mappedBy="profile")
     * @ORM\OrderBy({"id" = "desc"})
     */
    protected $orders;

    /**
     * @ORM\OneToMany(targetEntity="Clab\ShopBundle\Entity\Loyalty", mappedBy="user")
     * @ORM\OrderBy({"validUntil" = "asc"})
     */
    protected $loyalties;

    /**
     * @ORM\Column(name="subscribed_newsletter", type="boolean", nullable=true)
     */
    protected $subscribed_newsletter;

    /**
     * @ORM\Column(name="newsletterClickeat", type="boolean", nullable=true)
     */
    protected $newsletterClickeat;

    /**
     * @ORM\Column(name="newsletterTTT", type="boolean", nullable=true)
     */
    protected $newsletterTTT;

    /**
     * @ORM\Column(name="tttEventNotifications", type="boolean", nullable=true)
     */
    protected $tttEventNotifications;

    /**
     * @ORM\Column(name="tttEventNotificationsBookmarks", type="boolean", nullable=true)
     */
    protected $tttEventNotificationsBookmarks;

    /**
     * @ORM\Column(name="admin_validation", type="boolean", nullable=true)
     */
    protected $admin_validation;

    /**
     * @ORM\Column(name="admin_comment", type="text", nullable=true)
     */
    protected $admin_comment;

    /**
     * @ORM\Column(name="source", type="string", nullable=true)
     */
    protected $source;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\SocialBundle\Entity\SocialFacebookPage")
     * @ORM\JoinColumn(name="source_facebook_page_id", referencedColumnName="id", nullable=true)
     */
    protected $sourceFacebookPage;

    /**
     * @ORM\OneToOne(targetEntity="Clab\RestoflashBundle\Entity\RestoflashToken", cascade={"all"})
     * @ORM\JoinColumn(name="restoflash_token_id", referencedColumnName="id")
     */
    protected $restoflashToken;

    /**
     * @ORM\Column(name="up_count", type="integer")
     */
    protected $upCount;

    /**
     * @ORM\Column(name="down_count", type="integer")
     */
    protected $downCount;

    /**
     * @ORM\Column(name="review_count", type="integer")
     */
    protected $reviewCount;

    /**
     * @ORM\Column(name="description", type="string", nullable = true)
     */
    protected $description;

    /**
     * @ORM\Column(name="website", type="string", nullable = true)
     */
    protected $website;

    /**
     * @ORM\OneToMany(targetEntity="Clab\ReviewBundle\Entity\Vote", mappedBy="user")
     */
    protected $votes;

    protected $cover;

    /**
     * @var string
     * @ORM\Column(name="login_token", type="string", length=255, nullable=true)
     */
    private $loginToken;

    /**
     * @ORM\Column(type="string", length=255, name="stripe_customer_id", nullable=true)
     */
    protected $stripeCustomerId;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="myFriends")
     */
    private $friendsWithMe;
    
    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="friendsWithMe")
     * @ORM\JoinTable(name="friends",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="friend_user_id", referencedColumnName="id")}
     *      )
     */
    private $myFriends;

    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="followed")
     */
    private $followers;

    /**
     * @ORM\ManyToMany(targetEntity="User", inversedBy="followers")
     * @ORM\JoinTable(name="followers",
     *      joinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="follower_user_id", referencedColumnName="id")}
     *      )
     */
    private $followed;

    /**
     * @ORM\Column(type="array", name="last_search", nullable=true)
     */
    protected $lastSearch;

    /**
     * @ORM\Column(type="array", name="favorite_search", nullable=true)
     */
    protected $favoriteSearch;
    
    /**
     * @ORM\Column(name="parameters", type="json_array", nullable=true)
     */
    protected $parameters;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\BoardBundle\Entity\Company", inversedBy="users")
     * @ORM\JoinColumn(name="company_id", referencedColumnName="id", nullable=true)
     */
    protected $company;

    /**
     * @ORM\Column(name="business", type="string", nullable=true)
     */
    protected $business;

    /**
     * @ORM\OneToMany(targetEntity="Clab\ApiBundle\Entity\SessionCaisse", mappedBy="user")
     */
    private $sessionsCaisse;

    protected $reviews;
    protected $coverDefault;
    protected $apiCover;
    private $countFollowers;
    private $countFollowed;
    private $countFriends;
    private $countFriendsWithMe;
    private $countFavorites;
    private $countPhotos;
    private $countReviews;
    private $favoritesRestaurants;

    private $mail;

    /**
     * @ORM\PrePersist()
     * @ORM\PreUpdate()
     */
    public function preUpload()
    {
        if (null === $this->loginToken) {
            $this->loginToken = sha1(uniqid(mt_rand(), true)).$this->id;
        }
    }

    public function getFastmagId()
    {
        return $this->fastmagId;
    }

    public function setFastmagId($fastmagId)
    {
        $this->fastmagId = $fastmagId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getWebsite()
    {
        return $this->website;
    }

    /**
     * @param mixed $website
     *
     * @return $this
     */
    public function setWebsite($website)
    {
        $this->website = $website;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     *
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getNumberOfOrders()
    {
        if (is_null($this->orders)) {
            return 0;
        } else {
            return $this->orders->count();
        }
    }

    public function __construct()
    {
        parent::__construct();
        $this->setUpCount(0);
        $this->setDownCount(0);
        $this->setReviewCount(0);
        $this->restaurants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->clients = new \Doctrine\Common\Collections\ArrayCollection();
        $this->carts = new \Doctrine\Common\Collections\ArrayCollection();
        $this->orders = new \Doctrine\Common\Collections\ArrayCollection();
        $this->votes = new ArrayCollection();
        $this->loginToken = sha1(uniqid(mt_rand(), true)).$this->id;
        $this->friendsWithMe = new ArrayCollection();
        $this->myFriends = new ArrayCollection();
        $this->followers = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->favorites = array();
        $this->lastSearch = array();
        $this->favoriteSearch = array();
        $this->reviews = array();
        $this->loyalties = new ArrayCollection();
        $this->updated = new \DateTime();
    }

    /**
     * @return mixed
     */
    public function getReviewCount()
    {
        return $this->reviewCount;
    }

    /**
     * Add a friend to myFriends.
     *
     * @return User
     */
    public function addMyFriend($newFriend)
    {
        $this->myFriends[] = $newFriend;

        return $this;
    }

    /**
     * Remove a friend from myFriends.
     */
    public function removeMyFriend(User $myFriends)
    {
        $this->myFriends->removeElement($myFriends);
    }

    /**
     * @return mixed
     */
    public function getMyFriends()
    {
        return $this->myFriends;
    }

    /**
     * @param mixed $myFriends
     *
     * @return $this
     */
    public function setMyFriends($myFriends)
    {
        $this->myFriends = $myFriends;

        return $this;
    }

    /**
     * Add a friend to myFriends.
     *
     * @return User
     */
    public function addFollower($newFollower)
    {
        $this->followers[] = $newFollower;

        return $this;
    }

    /**
     * Remove a friend from Followers.
     */
    public function removeFollower(User $myFollowers)
    {
        $this->followers->removeElement($myFollowers);
    }

    /**
     * @return mixed
     */
    public function getFollowers()
    {
        return $this->followers;
    }

    /**
     * @param mixed $myFollowers
     *
     * @return $this
     */
    public function setFollowers($followed)
    {
        $this->followers = $followed;

        return $this;
    }
    
    /**
     * Add a friend to myFriends.
     *
     * @return User
     */
    public function addFollowed($newFollower)
    {
        $this->followed[] = $newFollower;

        return $this;
    }

    /**
     * Remove a friend from myFollowers.
     */
    public function removeFollowed(User $followed)
    {
        $this->followed->removeElement($followed);
    }

    /**
     * @return mixed
     */
    public function getFollowed()
    {
        return $this->followed;
    }

    /**
     * @param mixed $followed
     *
     * @return $this
     */
    public function setFollowed($followed)
    {
        $this->followed = $followed;

        return $this;
    }
    
    /**
     * @return mixed
     */
    public function getCountFollowers()
    {
        return count($this->followers);
    }

    /**
     * @return mixed
     */
    public function getCountFollowed()
    {
        return count($this->followed);
    }
    
    /**
     * Add friendsWithMe.
     *
     * @return User
     */
    public function addFriendsWithMe(User $friendsWithMe)
    {
        $this->friendsWithMe[] = $friendsWithMe;

        return $this;
    }

    /**
     * Remove friendsWithMe.
     */
    public function removeFriendsWithMe(User $friendsWithMe)
    {
        $this->friendsWithMe->removeElement($friendsWithMe);
    }

    /**
     * @return mixed
     */
    public function getFriendsWithMe()
    {
        return $this->friendsWithMe;
    }

    /**
     * @param mixed $friendsWithMe
     *
     * @return $this
     */
    public function setFriendsWithMe($friendsWithMe)
    {
        $this->friendsWithMe = $friendsWithMe;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCountFriends()
    {
        return count($this->myFriends);
    }

    /**
     * @return mixed
     */
    public function getCountFriendsWithMe()
    {
        return count($this->friendsWithMe);
    }

    /**
     * @param mixed $reviewCount
     *
     * @return $this
     */
    public function setReviewCount($reviewCount)
    {
        $this->reviewCount = $reviewCount;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getVotes()
    {
        return $this->votes;
    }

    public function addVote(Vote $vote)
    {
        $this->votes[] = $vote;
    }

    public function removeVote(Vote $vote)
    {
        $this->votes->removeElement($vote);
    }

    /**
     * @return mixed
     */
    public function getUpCount()
    {
        return $this->upCount;
    }

    /**
     * @param mixed $upCount
     *
     * @return $this
     */
    public function setUpCount($upCount)
    {
        $this->upCount = $upCount;

        return $this;
    }

    /**
     * @return string
     */
    public function getLoginToken()
    {
        return $this->loginToken;
    }

    /**
     * @param string $loginToken
     *
     * @return $this
     */
    public function setLoginToken($loginToken)
    {
        $this->loginToken = $loginToken;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDownCount()
    {
        return $this->downCount;
    }

    /**
     * @param mixed $downCount
     *
     * @return $this
     */
    public function setDownCount($downCount)
    {
        $this->downCount = $downCount;

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

    public function getFullName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    public function getUploadRootDir()
    {
        // absolute path to your directory where images must be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'gallery/user/'.$this->id.'/images';
    }

    public function getAbsolutePath()
    {
        return null === $this->image ? null : $this->getUploadRootDir().'/'.$this->image;
    }

    public function getWebPath()
    {
        return null === $this->image ? null : '/'.$this->getUploadDir().'/'.$this->image;
    }

    public function addCart(\Clab\ShopBundle\Entity\Cart $carts)
    {
        $this->carts[] = $carts;
    }

    public function getCarts()
    {
        return $this->carts;
    }

    public function addOrderDetail(\Clab\ShopBundle\Entity\OrderDetail $orders)
    {
        $this->orders[] = $orders;
    }

    public function getOrders()
    {
        return $this->orders;
    }

    public function removeCart(\Clab\ShopBundle\Entity\Cart $carts)
    {
        $this->carts->removeElement($carts);
    }

    public function addOrder(\Clab\ShopBundle\Entity\OrderDetail $orders)
    {
        $this->orders[] = $orders;

        return $this;
    }

    public function removeOrder(\Clab\ShopBundle\Entity\OrderDetail $orders)
    {
        $this->orders->removeElement($orders);
    }

    public function setLoyalties($loyalties)
    {
        $this->loyalties = $loyalties;

        return $this;
    }

    public function getLoyalties()
    {
        return $this->loyalties;
    }

    public function addLoyalty(\Clab\ShopBundle\Entity\Loyalty $loyalty)
    {
        $this->loyalties[] = $loyalty;

        return $this;
    }

    public function removeLoyalty(\Clab\ShopBundle\Entity\Loyalty $loyalty)
    {
        $this->loyalties->removeElement($loyalty);
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     *
     * @return $this
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    public function addGallery($gal)
    {
        $this->gallery[] = $gal;
    }

    public function setGallery($gals)
    {
        $this->gallery = $gals;

        foreach ($gals as $gal) {
            $this->addGallery($gal);
        }

        return $this;
    }

    public function removeGallery($gal)
    {
        $this->gallery->removeElement($gal);
    }

    public function addFavorite(Restaurant $restaurant)
    {
        $this->favorites[$restaurant->getId()] = $restaurant->getSlug();

        return $this;
    }

    public function setFavorites($favorites)
    {
        $this->favorites = $favorites;

        return $this;
    }
    public function removeFavorite(Restaurant $restaurant)
    {
        unset($this->favorites[$restaurant->getId()]);

        return $this;
    }

    public function addFavoriteProduct(Product $favoriteProduct)
    {
        $this->favoriteProducts[] = $favoriteProduct;
    }
    public function setFavoriteProducts($favoriteProducts)
    {
        $this->favoriteProducts = $favoriteProducts;

        return $this;
    }
    public function removeFavoriteProduct($favoriteProduct)
    {
        $this->favoriteProducts->removeElement($favoriteProduct);
    }

    public function addDiscount($discount)
    {
        $this->discounts[] = $discount;
    }
    public function setDiscounts($discounts)
    {
        $this->discounts = $discounts;
        foreach ($discounts as $discount) {
            $this->addFavoriteProduct($discount);
        }

        return $this;
    }
    public function removeDiscount($discount)
    {
        $this->discounts->removeElement($discount);
    }

    /**
     * @return mixed
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * @return mixed
     */
    public function getFavoriteProducts()
    {
        return $this->favoriteProducts;
    }

    /**
     * @return mixed
     */
    public function getFavorites()
    {
        return $this->favorites;
    }

    /**
     * @return mixed
     */
    public function getReviews()
    {
        return $this->reviews;
    }

    public function setReviews($reviews)
    {
        $this->reviews = $reviews;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getGallery()
    {
        return $this->gallery;
    }

    /**
     * @return mixed
     */
    public function getInstagramUserId()
    {
        return $this->instagramUserId;
    }

    /**
     * @param mixed $instagramUserId
     *
     * @return $this
     */
    public function setInstagramUserId($instagramUserId)
    {
        $this->instagramUserId = $instagramUserId;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInstagramAccessToken()
    {
        return $this->instagramAccessToken;
    }

    /**
     * @param mixed $instagramAccessToken
     *
     * @return $this
     */
    public function setInstagramAccessToken($instagramAccessToken)
    {
        $this->instagramAccessToken = $instagramAccessToken;

        return $this;
    }

    public function isAvailable()
    {
        return true;
    }

    public function __toString()
    {
        return $this->getEmail();
    }

    public function isAllowed(User $user)
    {
        if ($user->hasRole('ROLE_COMMERCIAL') || $user->hasRole('ROLE_ADMIN') || $user->hasRole('ROLE_SUPER_ADMIN')) {
            return true;
        }

        if ($this->hasRole('ROLE_COMMERCIAL') || $this->hasRole('ROLE_ADMIN') || $this->hasRole('ROLE_SUPER_ADMIN')) {
            return false;
        }

        foreach ($this->getClients() as $client) {
            if ($client->hasManager($user)) {
                return true;
            }
        }

        foreach ($this->getRestaurants() as $restaurant) {
            if ($restaurant->hasManager($user)) {
                return true;
            }
        }

        return false;
    }

    public function getAllowedRestaurants()
    {
        $restaurants = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($this->restaurants as $restaurant) {
            $restaurants->add($restaurant);
        }

        foreach ($this->clients as $client) {
            foreach ($client->getRestaurants() as $restaurant) {
                if (!$restaurants->contains($restaurant)) {
                    $restaurants->add($restaurant);
                }
            }
        }

        return $restaurants;
    }

    /**
     * @param Array
     */
    public function setFBData($fbdata)
    {
        if (isset($fbdata['id'])) {
            $this->setFacebookId($fbdata['id']);
            $this->addRole('ROLE_FACEBOOK');
        }
        if (isset($fbdata['email']) && !$this->getEmail()) {
            $this->setEmail($fbdata['email']);
        }
        if (isset($fbdata['first_name'])) {
            $this->setFirstName($fbdata['first_name']);
        }
        if (isset($fbdata['last_name'])) {
            $this->setLastName($fbdata['last_name']);
        }

        if (isset($fbdata['gender'])) {
            if ($fbdata['gender'] == 'male') {
                $this->setIsMale(true);
            } elseif ($fbdata['gender'] == 'female') {
                $this->setIsMale(false);
            }
        }

        if (isset($fbdata['birthday'])) {
            $this->setBirthday(date_create_from_format('m/d/Y', $fbdata['birthday']));
        }
    }

    public function isConfirmed()
    {
        return $this->hasRole('ROLE_MEMBER_CONFIRMED');
    }
    public function isManager()
    {
        return $this->hasRole('ROLE_MANAGER');
    }
    public function isManager2()
    {
        return $this->hasRole('ROLE_MANAGER_2');
    }
    public function isSeller()
    {
        return $this->hasRole('ROLE_SELLER');
    }
    public function isDeliveryman()
    {
        return $this->hasRole('ROLE_DELIVERYMAN');
    }

    public function getId()
    {
        return $this->id;
    }

    public function serialize()
    {
        return serialize(array($this->facebookId, parent::serialize()));
    }

    public function unserialize($data)
    {
        list($this->facebookId, $parentData) = unserialize($data);
        parent::unserialize($parentData);
    }

    public function setEmail($email)
    {
        parent::setEmail($email);
        $this->username = $email;
    }

    public function setEmailCanonical($emailCanonical)
    {
        parent::setEmailCanonical($emailCanonical);
        $this->usernameCanonical = $emailCanonical;
    }

    public function getPrettyRoles()
    {
        $myRoles = array();
        $roles = self::getAllRoles();

        foreach ($this->getRoles() as $role) {
            if (in_array($role, array_keys($roles))) {
                $myRoles[] = $roles[$role];
            }
        }

        return $myRoles;
    }

    public static function getAllRoles()
    {
        $roles = array(
            'ROLE_MEMBER_CONFIRMED' => 'Utilisateur validé',
            'ROLE_MEMBER' => 'Utilisateur',
        );

        return array_merge($roles, self::getServiceRoles());
    }

    public static function getServiceRoles()
    {
        $roles = array(
            'ROLE_SUPER_ADMIN' => 'Superman',
            'ROLE_ADMIN' => 'Admin',
            'ROLE_COMMERCIAL' => 'Commercial',
        );

        return array_merge($roles, self::getCommercialRoles());
    }

    public static function getCommercialRoles()
    {
        $roles = array(
            //'ROLE_COMPANY' => 'Entreprises',
            //'ROLE_TTT' => 'Track the Truck',
            //'ROLE_CLICKEAT' => 'Clickeat',
            'ROLE_COMPANY_MANAGER' => 'Gérant entreprise',
            'ROLE_EVENT_MANAGER' => 'Gérant évènement',
        );

        return array_merge($roles, self::getManagerRoles());
    }

    public static function getManagerRoles()
    {
        return array(
            'ROLE_MANAGER' => 'Gérant',
            'ROLE_MANAGER_2' => 'Manageur',
            //'ROLE_CATALOG' => 'Gestion catalogue',
            //'ROLE_DISCOUNT' => 'Offres',
            'ROLE_SELLER' => 'Vendeur',
            'ROLE_DELIVERYMAN' => 'Livreur',
        );
    }

    public static function getManager2Roles()
    {
        return array(
            'ROLE_SELLER' => 'Vendeur',
            'ROLE_DELIVERYMAN' => 'Livreur',
        );
    }

    public function setSponsor(\Clab\UserBundle\Entity\User $sponsor = null)
    {
        $this->sponsor = $sponsor;

        return $this;
    }

    public function getSponsor()
    {
        return $this->sponsor;
    }

    public function addSponsored(\Clab\UserBundle\Entity\User $sponsored)
    {
        $this->sponsored[] = $sponsored;

        return $this;
    }

    public function removeSponsored(\Clab\UserBundle\Entity\User $sponsored)
    {
        $this->sponsored->removeElement($sponsored);
    }

    public function getSponsored()
    {
        return $this->sponsored;
    }

    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
        $this->setUsername($facebookId);
    }

    public function getFacebookId()
    {
        return $this->facebookId;
    }

    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;

        return $this;
    }

    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
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

    public function addRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurants)
    {
        $this->restaurants[] = $restaurants;

        return $this;
    }

    public function removeRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurants)
    {
        $this->restaurants->removeElement($restaurants);
    }

    public function getRestaurants()
    {
        return $this->restaurants;
    }

    public function addClient(\Clab\BoardBundle\Entity\Client $clients)
    {
        $this->clients[] = $clients;

        return $this;
    }

    public function removeClient(\Clab\BoardBundle\Entity\Client $clients)
    {
        $this->clients->removeElement($clients);
    }

    public function getClients()
    {
        return $this->clients;
    }

    public function addPlace(\Clab\LocationBundle\Entity\Place $places)
    {
        $this->places[] = $places;

        return $this;
    }

    public function removePlace(\Clab\LocationBundle\Entity\Place $places)
    {
        $this->places->removeElement($places);
    }

    public function getPlaces()
    {
        return $this->places;
    }

    public function addEvent(\Clab\LocationBundle\Entity\Event $events)
    {
        $this->events[] = $events;

        return $this;
    }

    public function removeEvent(\Clab\LocationBundle\Entity\Event $events)
    {
        $this->events->removeElement($events);
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function setPipedriveId($pipedriveId)
    {
        $this->pipedriveId = $pipedriveId;

        return $this;
    }

    public function getPipedriveId()
    {
        return $this->pipedriveId;
    }

    public function addSession(\Clab\ApiBundle\Entity\Session $sessions)
    {
        $this->sessions[] = $sessions;

        return $this;
    }

    public function removeSession(\Clab\ApiBundle\Entity\Session $sessions)
    {
        $this->sessions->removeElement($sessions);
    }

    public function getSessions()
    {
        return $this->sessions;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @param mixed $first_name
     *
     * @return $this
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @param mixed $last_name
     *
     * @return $this
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsMale()
    {
        return $this->is_male;
    }

    /**
     * @param mixed $is_male
     *
     * @return $this
     */
    public function setIsMale($is_male)
    {
        $this->is_male = $is_male;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param mixed $phone
     *
     * @return $this
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBirthday()
    {
        return $this->birthday;
    }

    /**
     * @param mixed $birthday
     *
     * @return $this
     */
    public function setBirthday($birthday)
    {
        $this->birthday = $birthday;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getZipcode()
    {
        return $this->zipcode;
    }

    /**
     * @param mixed $zipcode
     *
     * @return $this
     */
    public function setZipcode($zipcode)
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param mixed $user
     *
     * @return $this
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHomeAddress()
    {
        return $this->homeAddress;
    }

    /**
     * @param mixed $homeAddress
     *
     * @return $this
     */
    public function setHomeAddress($homeAddress)
    {
        $this->homeAddress = $homeAddress;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getJobAddress()
    {
        return $this->jobAddress;
    }

    /**
     * @param mixed $jobAddress
     *
     * @return $this
     */
    public function setJobAddress($jobAddress)
    {
        $this->jobAddress = $jobAddress;

        return $this;
    }
    /**
     * @param mixed $carts
     *
     * @return $this
     */
    public function setCarts($carts)
    {
        $this->carts = $carts;

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
     * @return mixed
     */
    public function getSubscribedNewsletter()
    {
        return $this->subscribed_newsletter;
    }

    /**
     * @param mixed $subscribed_newsletter
     *
     * @return $this
     */
    public function setSubscribedNewsletter($subscribed_newsletter)
    {
        $this->subscribed_newsletter = $subscribed_newsletter;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewsletterClickeat()
    {
        return $this->newsletterClickeat;
    }

    /**
     * @param mixed $newsletterClickeat
     *
     * @return $this
     */
    public function setNewsletterClickeat($newsletterClickeat)
    {
        $this->newsletterClickeat = $newsletterClickeat;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNewsletterTTT()
    {
        return $this->newsletterTTT;
    }

    /**
     * @param mixed $newsletterTTT
     *
     * @return $this
     */
    public function setNewsletterTTT($newsletterTTT)
    {
        $this->newsletterTTT = $newsletterTTT;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTttEventNotifications()
    {
        return $this->tttEventNotifications;
    }

    /**
     * @param mixed $tttEventNotifications
     *
     * @return $this
     */
    public function setTttEventNotifications($tttEventNotifications)
    {
        $this->tttEventNotifications = $tttEventNotifications;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTttEventNotificationsBookmarks()
    {
        return $this->tttEventNotificationsBookmarks;
    }

    /**
     * @param mixed $tttEventNotificationsBookmarks
     *
     * @return $this
     */
    public function setTttEventNotificationsBookmarks($tttEventNotificationsBookmarks)
    {
        $this->tttEventNotificationsBookmarks = $tttEventNotificationsBookmarks;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminValidation()
    {
        return $this->admin_validation;
    }

    /**
     * @param mixed $admin_validation
     *
     * @return $this
     */
    public function setAdminValidation($admin_validation)
    {
        $this->admin_validation = $admin_validation;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAdminComment()
    {
        return $this->admin_comment;
    }

    /**
     * @param mixed $admin_comment
     *
     * @return $this
     */
    public function setAdminComment($admin_comment)
    {
        $this->admin_comment = $admin_comment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param mixed $source
     *
     * @return $this
     */
    public function setSource($source)
    {
        $this->source = $source;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSourceFacebookPage()
    {
        return $this->sourceFacebookPage;
    }

    /**
     * @param mixed $sourceFacebookPage
     *
     * @return $this
     */
    public function setSourceFacebookPage($sourceFacebookPage)
    {
        $this->sourceFacebookPage = $sourceFacebookPage;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getRestoflashToken()
    {
        return $this->restoflashToken;
    }

    /**
     * @return mixed
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param mixed $file
     *
     * @return $this
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @param mixed $restoflashToken
     *
     * @return $this
     */
    public function setRestoflashToken($restoflashToken)
    {
        $this->restoflashToken = $restoflashToken;

        return $this;
    }

    public function setRole($role)
    {
        $this->setRoles(array($role));
    }
    public function getRole()
    {
        if (isset($this->roles[0])) {
            $role = $this->roles[0];

            return $role;
        }
    }
    public function upload()
    {
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // use the original file name here but you should
        // sanitize it at least to avoid any security issues

        // move takes the target directory and then the
        // target filename to move to
        $this->getFile()->move(
            $this->getUploadRootDir(),
            $this->getFile()->getClientOriginalName()
        );

        // set the path property to the filename where you've saved the file
        $this->image = $this->getFile()->getClientOriginalName();

        // clean up the file property as you won't need it anymore
        $this->file = null;
    }

    /**
     * @param string $imageName
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;
    }

    /**
     * Set updated
     *
     * @param \DateTime $updated
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
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
    public function getCountFavorites()
    {
        return count($this->favorites);
    }

    /**
     * @param mixed $countFavorites
     */
    public function setCountFavorites($countFavorites)
    {
        $this->countFavorites = $countFavorites;
    }

    /**
     * @return mixed
     */
    public function getCountPhotos()
    {
        if (!is_null($this->gallery)) {
            return count($this->gallery->getImages());
        } else {
            return 0;
        }
    }

    /**
     * @param mixed $countPhotos
     */
    public function setCountPhotos($countPhotos)
    {
        $this->countPhotos = $countPhotos;
    }

    /**
     * @return mixed
     */
    public function getCountReviews()
    {
        return $this->reviewCount;
    }

    /**
     * @param mixed $countReviews
     */
    public function setCountReviews($countReviews)
    {
        $this->countReviews = $countReviews;
    }

    public function addLastSearch(array $lastSearch)
    {
        if (!$this->lastSearch) {
            $this->lastSearch = array();
        }

        $lastSearch['date'] = date('d-m-Y H:i:s');

        array_unshift($this->lastSearch, $lastSearch);

        $this->lastSearch = array_slice($this->lastSearch, 0, 10);

        return $this;
    }

    public function getLastSearch()
    {
        return $this->lastSearch;
    }

    public function addFavoriteSearch($searchKey, array $search)
    {
        $search['date'] = date('d-m-Y H:i:s');

        $this->favoriteSearch[$searchKey] = $search;

        return $this;
    }

    public function setFavoritesSearch(array $favoriteSearch)
    {
        $this->favoriteSearch = $favoriteSearch;

        return $this;
    }

    public function removeFavoriteSearch($searchKey)
    {
        unset($this->favoriteSearch[$searchKey]);

        return $this;
    }

    public function getFavoriteSearch()
    {
        return $this->favoriteSearch;
    }

    public function getOneFavoriteSearch($searchKey)
    {
        return $this->favoriteSearch[$searchKey];
    }
    
     /**
     * @return mixed
     */
    public function setParameters(array $array)
    {
        $this->parameters = $array;
    }
    
    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }
    
    public function setParameter($key, $value) {
        $this->parameters[$key] = $value;
    }
    
    public function hasParameter($key) {
        return isset($key, $this->parameters);
    }
    
    public function getParameter($key) {
        if (!$this->hasParameter($key)) {
            throw new ParameterNotFoundException(sprintf('%s not found on User', $key));
        }
        return $this->parameters[$key];
    }
    
    public function removeParameter($key) {
        unset($this->parameters[$key]);
    }

    /**
     * @return mixed
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param mixed $addresses
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
    }

    public function addAddress($address) {
        $this->addresses[] = $address;
    }

    /**
     * @return mixed
     */
    public function getCompany()
    {
        return $this->company;
    }

    public function setCompany($company) {
        $this->company = $company;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBusiness()
    {
        return $this->business;
    }

    public function setBusiness($business) {
        $this->business = $business;

        return $this;
    }

    public function getSessionsCaisse()
    {
        return $this->sessionsCaisse;
    }

    public function setSessionsCaisse($sessionsCaisse)
    {
        $this->sessionsCaisse = $sessionsCaisse;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCoverDefault()
    {
        return $this->coverDefault;
    }

    /**
     * @param mixed $coverDefault
     */
    public function setCoverDefault($coverDefault)
    {
        $this->coverDefault = $coverDefault;
    }

    /**
     * @return mixed
     */
    public function getApiCover()
    {
        return $this->apiCover;
    }

    /**
     * @param mixed $apiCover
     */
    public function setApiCover($apiCover)
    {
        $this->apiCover = $apiCover;
    }

    /**
     * @return mixed
     */
    public function getFavoritesRestaurants()
    {
        return $this->favoritesRestaurants;
    }

    /**
     * @param mixed $favoritesRestaurants
     */
    public function setFavoritesRestaurants($favoritesRestaurants)
    {
        $this->favoritesRestaurants = $favoritesRestaurants;
    }
}
