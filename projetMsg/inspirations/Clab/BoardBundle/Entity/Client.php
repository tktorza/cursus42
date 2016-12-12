<?php

namespace Clab\BoardBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Clab\MediaBundle\Entity\GalleryOwnerInterface;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

/**
 * Clab\BoardBundle\Entity\Client.
 * @ORM\Table(name="client")
 * @ORM\Entity(repositoryClass="Clab\BoardBundle\Entity\ClientRepository")
 * @Vich\Uploadable
 */
class Client implements GalleryOwnerInterface
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    protected $is_online;

    /**
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    protected $is_deleted;

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
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @ORM\Column(name="has_caisse", type="boolean", nullable=true)
     */
    protected $hasCaisse;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="forcedPricing", type="boolean")
     */
    protected $forcedPricing;

    /**
     * @ORM\Column(name="forced_is_online", type="boolean")
     */
    protected $forcedIsOnline;

    /**
     * @ORM\Column(name="forced_add", type="boolean")
     */
    protected $forcedAdd;

    /**
     * @ORM\Column(name="client_payment", type="boolean")
     */
    protected $clientPayment;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="commercial_id", referencedColumnName="id")
     */
    protected $commercial;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="clab_client_logo", fileNameProperty="logoName")
     *
     * @var File
     */
    protected $logoFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    protected $logoName;

    /**
     * @ORM\ManyToMany(targetEntity="Clab\UserBundle\Entity\User", inversedBy="clients")
     * @ORM\JoinTable(name="clickeat_clients_managers",
     *                joinColumns={@ORM\JoinColumn(name="client_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")})
     */
    protected $managers;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", mappedBy="client")
     */
    protected $restaurants;

    /**
     * @ORM\OneToMany(targetEntity="ClientConfiguration", mappedBy="client")
     */
    protected $configurations;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\ProductCategory", mappedBy="client")
     * @ORM\OrderBy({"position" = "asc"})
     */
    protected $productCategories;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\OptionChoice", mappedBy="client")
     * @ORM\OrderBy({"position" = "asc"})
     */
    protected $choices;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\ProductOption", mappedBy="client")
     */
    protected $options;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\MealSlot", mappedBy="client")
     */
    protected $mealSlots;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\MediaBundle\Entity\Gallery")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id")
     */
    protected $gallery;

    /**
     * @ORM\OneToOne(targetEntity="Clab\SocialBundle\Entity\SocialProfile", cascade={"all"})
     * @ORM\JoinColumn(name="social_profile_id", referencedColumnName="id", nullable=true)
     */
    protected $socialProfile;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\SocialBundle\Entity\SocialFacebookPage", inversedBy="clients")
     * @ORM\JoinColumn(name="facebook_page_id", referencedColumnName="id", nullable=true)
     */
    protected $facebookPage;

    /**
     * @ORM\OneToMany(targetEntity="Clab\RestaurantBundle\Entity\RestaurantMenu", mappedBy="chainStore")
     */
    protected $restaurantMenus;

    protected $coverSmall;
    protected $cover;
    protected $coverFull;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->setHasCaisse(false);
        $this->setForcedPricing(false);
        $this->setForcedAdd(false);
        $this->setForcedIsOnline(false);

        $this->managers = new \Doctrine\Common\Collections\ArrayCollection();
        $this->restaurants = new \Doctrine\Common\Collections\ArrayCollection();
        $this->products = new \Doctrine\Common\Collections\ArrayCollection();
        $this->meals = new \Doctrine\Common\Collections\ArrayCollection();
        $this->productCategories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->choices = new \Doctrine\Common\Collections\ArrayCollection();
        $this->options = new \Doctrine\Common\Collections\ArrayCollection();
        $this->mealSlots = new \Doctrine\Common\Collections\ArrayCollection();
        $this->tags = new ArrayCollection();
    }

    /**
     * @return File
     */
    public function getLogoFile() {

        return $this->logoFile;
    }

    /**
     * @param File $logoMc
     *
     * @return $this
     */
    public function setLogoFile($image)
    {
        $this->logoFile = $image;

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
    public function getLogoName() {

        return $this->logoName;
    }

    /**
     * @param string $logoName
     * @return $this
     */
    public function setLogoName($logoName) {

        $this->logoName = $logoName;
        return $this;
    }



    /**
     * @return mixed
     */
    public function getForcedIsOnline() {

        return $this->forcedIsOnline;
    }

    /**
     * @param mixed $forcedIsOnline
     * @return $this
     */
    public function setForcedIsOnline($forcedIsOnline) {

        $this->forcedIsOnline = $forcedIsOnline;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getForcedAdd() {

        return $this->forcedAdd;
    }

    /**
     * @param mixed $forcedAdd
     * @return $this
     */
    public function setForcedAdd($forcedAdd) {

        $this->forcedAdd = $forcedAdd;
        return $this;
    }



    public function __toString()
    {
        return (string) $this->name;
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

    public function isAvailable()
    {
        return ($this->isOnline() && !$this->isDeleted()) ? true : false;
    }

    public function remove()
    {
        $this->setIsOnline(false);
        $this->setIsDeleted(true);
    }

    public function setIsOnline($isOnline)
    {
        $this->is_online = $isOnline;

        return $this;
    }

    public function getIsOnline()
    {
        return $this->is_online;
    }

    public function isOnline()
    {
        return $this->getIsOnline();
    }

    public function setIsDeleted($isDeleted)
    {
        $this->is_deleted = $isDeleted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getClientPayment() {

        return $this->clientPayment;
    }

    /**
     * @param mixed $clientPayment
     * @return $this
     */
    public function setClientPayment($clientPayment) {

        $this->clientPayment = $clientPayment;
        return $this;
    }



    public function getIsDeleted()
    {
        return $this->is_deleted;
    }

    public function isDeleted()
    {
        return $this->getIsDeleted();
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

    public function getUpdated()
    {
        return $this->updated;
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

    public function getCoverFull()
    {
        return $this->coverFull;
    }

    public function setCoverFull($coverFull)
    {
        $this->coverFull = $coverFull;

        return $this;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setCommercial(\Clab\UserBundle\Entity\User $commercial = null)
    {
        $this->commercial = $commercial;

        return $this;
    }

    public function getCommercial()
    {
        return $this->commercial;
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

    public function addManager(\Clab\UserBundle\Entity\User $manager)
    {
        if (!$this->managers->contains($manager)) {
            $this->managers->add($manager);
        }

        return $this;
    }

    public function removeManager(\Clab\UserBundle\Entity\User $managers)
    {
        $this->managers->removeElement($managers);
    }

    public function getManagers()
    {
        return $this->managers;
    }

    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function addProductCategorie(\Clab\RestaurantBundle\Entity\ProductCategory $productCategories)
    {
        $this->productCategories[] = $productCategories;

        return $this;
    }

    public function removeProductCategorie(\Clab\RestaurantBundle\Entity\ProductCategory $productCategories)
    {
        $this->productCategories->removeElement($productCategories);
    }

    public function getProductCategories()
    {
        return $this->productCategories;
    }

    public function addChoice(\Clab\RestaurantBundle\Entity\OptionChoice $choices)
    {
        $this->choices[] = $choices;

        return $this;
    }

    public function removeChoice(\Clab\RestaurantBundle\Entity\OptionChoice $choices)
    {
        $this->choices->removeElement($choices);
    }

    public function getChoices()
    {
        $choices = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($this->choices as $choice) {
            if (!$choice->getIsDeleted()) {
                $choices->add($choice);
            }
        }

        return $choices;
    }

    public function addProductCategory(\Clab\RestaurantBundle\Entity\ProductCategory $productCategories)
    {
        $this->productCategories[] = $productCategories;

        return $this;
    }

    public function removeProductCategory(\Clab\RestaurantBundle\Entity\ProductCategory $productCategories)
    {
        $this->productCategories->removeElement($productCategories);
    }

    public function addOption(\Clab\RestaurantBundle\Entity\ProductOption $options)
    {
        $this->options[] = $options;

        return $this;
    }

    public function removeOption(\Clab\RestaurantBundle\Entity\ProductOption $options)
    {
        $this->options->removeElement($options);
    }

    public function getOptions()
    {
        $options = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($this->options as $option) {
            if (!$option->getIsDeleted()) {
                $options->add($option);
            }
        }

        return $options;
    }

    public function addMealSlot(\Clab\RestaurantBundle\Entity\MealSlot $mealSlots)
    {
        $this->mealSlots[] = $mealSlots;

        return $this;
    }

    public function removeMealSlot(\Clab\RestaurantBundle\Entity\MealSlot $mealSlots)
    {
        $this->mealSlots->removeElement($mealSlots);
    }

    public function getMealSlots()
    {
        return $this->mealSlots;
    }

    public function addMeal(\Clab\RestaurantBundle\Entity\Meal $meals)
    {
        $this->meals[] = $meals;

        return $this;
    }

    public function removeMeal(\Clab\RestaurantBundle\Entity\Meal $meals)
    {
        $this->meals->removeElement($meals);
    }

    public function getMeals()
    {
        $meals = new \Doctrine\Common\Collections\ArrayCollection();

        foreach ($this->meals as $meal) {
            if (!$meal->getIsDeleted()) {
                $meals->add($meal);
            }
        }

        return $meals;
    }

    public function addConfiguration(\Clab\BoardBundle\Entity\ClientConfiguration $configurations)
    {
        $this->configurations[] = $configurations;

        return $this;
    }

    public function removeConfiguration(\Clab\BoardBundle\Entity\ClientConfiguration $configurations)
    {
        $this->configurations->removeElement($configurations);
    }

    public function getConfigurations()
    {
        return $this->configurations;
    }

    public function setGallery(\Clab\MediaBundle\Entity\Gallery $gallery = null)
    {
        $this->gallery = $gallery;

        return $this;
    }

    public function getGallery()
    {
        return $this->gallery;
    }

    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setSocialProfile(\Clab\SocialBundle\Entity\SocialProfile $socialProfile = null)
    {
        $this->socialProfile = $socialProfile;

        return $this;
    }

    public function getSocialProfile()
    {
        return $this->socialProfile;
    }

    public function setFacebookPage(\Clab\SocialBundle\Entity\SocialFacebookPage $facebookPage = null)
    {
        $this->facebookPage = $facebookPage;

        return $this;
    }

    public function getFacebookPage()
    {
        return $this->facebookPage;
    }

    public function setForcedPricing($forcedPricing)
    {
        $this->forcedPricing = $forcedPricing;

        return $this;
    }

    public function getForcedPricing()
    {
        return $this->forcedPricing;
    }

    /**
     * Add restaurantMenu.
     *
     * @param \Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu
     *
     * @return Client
     */
    public function addRestaurantMenu(\Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu)
    {
        $this->restaurantMenus[] = $restaurantMenu;

        return $this;
    }

    /**
     * Remove restaurantMenu.
     *
     * @param \Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu
     */
    public function removeRestaurantMenu(\Clab\RestaurantBundle\Entity\RestaurantMenu $restaurantMenu)
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
}
