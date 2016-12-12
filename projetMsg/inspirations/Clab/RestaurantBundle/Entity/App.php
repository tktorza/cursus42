<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @ORM\Table(name="clickeat_app")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Repository\AppRepository")
 * @Vich\Uploadable
 * @ORM\HasLifecycleCallbacks
 */
class App
{
    const APP_TYPE_FREE = 0;
    const APP_TYPE_PLAN = 10;
    const APP_TYPE_PAYMENT = 20;

    const APP_PLATFORM_CLICKEAT = 0;
    const APP_PLATFORM_TTT = 10;
    const APP_PLATFORM_BOTH = 20;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    private $isOnline;

    /**
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @ORM\Column(name="name", type="string", length=255)
     */
    protected $name;

    /**
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(length=128, unique=true)
     */
    protected $slug;

    /**
     * @ORM\Column(name="short_description", type="string", nullable=true)
     */
    protected $shortDescription;

    /**
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="type", type="integer", nullable=true)
     */
    protected $type;

    /**
     * @ORM\Column(name="platform", type="integer", nullable=true)
     */
    protected $platform;

    /**
     * @ORM\Column(name="price", type="float", nullable = true)
     */
    protected $price;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     *
     * @Vich\UploadableField(mapping="clab_app_image", fileNameProperty="imageName")
     *
     * @var File
     */
    private $imageFile;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     *
     * @var string
     */
    private $imageName;

    /**
     * @ORM\Column(name="call_to_action", type="string", nullable=true)
     */
    protected $callToAction;

    /**
     * @ORM\Column(name="is_on_dashboard", type="boolean", nullable=true)
     */
    protected $isOnDashboard;

    /**
     * @ORM\ManyToMany(targetEntity="Plan")
     * @ORM\JoinTable(name="restaurant_apps_plan",
     *      joinColumns={@ORM\JoinColumn(name="app_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="plan_id", referencedColumnName="id")}
     *      )
     */
    protected $plans;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->plans = new ArrayCollection();

        $this->setIsOnline(true);
        $this->setPrice(0);
    }

    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     *
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlatform()
    {
        return $this->platform;
    }

    /**
     * @param mixed $platform
     *
     * @return $this
     */
    public function setPlatform($platform)
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getPlans()
    {
        return $this->plans;
    }

    public function addPlan(Plan $plan)
    {
        $this->plans[] = $plan;
    }

    public function setApps($plans)
    {
        $this->plans = $plans;
        foreach ($plans as $plan) {
            $this->addPlan($plans);
        }

        return $this;
    }

    /**
     * Remove app.
     */
    public function removePlan(Plan $plan)
    {
        $this->plans->removeElement($plan);
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * @param mixed $isOnline
     *
     * @return $this
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param mixed $created
     *
     * @return $this
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param mixed $updated
     *
     * @return $this
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param mixed $slug
     *
     * @return $this
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;

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

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     *
     * @return $this
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return File
     */
    public function getImageFile()
    {
        return $this->imageFile;
    }

    /**
     * @param File $imageFile
     *
     * @return $this
     */
    public function setImageFile($imageFile)
    {
        $this->imageFile = $imageFile;
        if ($imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updated = new \DateTime('now');
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getImageName()
    {
        return $this->imageName;
    }

    /**
     * @param string $imageName
     *
     * @return $this
     */
    public function setImageName($imageName)
    {
        $this->imageName = $imageName;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param mixed $shortDescription
     *
     * @return $this
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCallToAction()
    {
        return $this->callToAction;
    }

    /**
     * @param mixed $callToAction
     *
     * @return $this
     */
    public function setCallToAction($callToAction)
    {
        $this->callToAction = $callToAction;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsOnDashboard()
    {
        return $this->isOnDashboard;
    }

    /**
     * @param mixed $isOnDashboard
     *
     * @return $this
     */
    public function setIsOnDashboard($isOnDashboard)
    {
        $this->isOnDashboard = $isOnDashboard;

        return $this;
    }
}
