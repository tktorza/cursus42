<?php

namespace Clab\MediaBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Entity
 * @ORM\Table(name="media_image")
 * @ORM\HasLifecycleCallbacks
 */
class Image
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="is_online", type="boolean")
     */
    private $is_online;

    /**
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    private $is_deleted;

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
     * @ORM\ManyToOne(targetEntity="Image", inversedBy="childrens")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id", nullable=true)
     */
    protected $parent;

    /**
     * @ORM\OneToMany(targetEntity="Image", mappedBy="parent")
     */
    protected $childrens;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $code;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $path;

    /**
     * @Assert\File(maxSize="6000000")
     */
    protected $file;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_promoted", type="boolean")
     */
    private $is_promoted;

    /**
     * @ORM\Column(name="is_generic", type="boolean")
     */
    private $is_generic;

    /**
     * @ORM\ManyToOne(targetEntity="Gallery", inversedBy="images")
     * @ORM\JoinColumn(name="gallery_id", referencedColumnName="id", nullable=true)
     */
    protected $gallery;

    /**
     * @ORM\ManyToOne(targetEntity="\Clab\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id", nullable=true)
     */
    protected $profile;

    /**
     * @ORM\ManyToMany(targetEntity="\Clab\TaxonomyBundle\Entity\Term", inversedBy="images")
     * @ORM\JoinTable(name="media_image_tags",
     *                joinColumns={@ORM\JoinColumn(name="image_id", referencedColumnName="id")},
     *                inverseJoinColumns={@ORM\JoinColumn(name="term_id", referencedColumnName="id")})
     */
    protected $tags;

    protected $webPath;
    protected $mobilePath;

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->setIsPromoted(false);
        $this->setIsGeneric(false);
        $this->tags = new \Doctrine\Common\Collections\ArrayCollection();
        $this->childrens = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function __toString()
    {
        return (string) $this->getId();
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

    /**
     * @return mixed
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param mixed $profile
     *
     * @return $this
     */
    public function setProfile($profile)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMobilePath()
    {
        return $this->mobilePath;
    }

    /**
     * @param mixed $mobilePath
     *
     * @return $this
     */
    public function setMobilePath($mobilePath)
    {
        $this->mobilePath = $mobilePath;

        return $this;
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

    public function isAllowed(\Clab\UserBundle\Entity\User $user)
    {
        return true;
    }

    public function upload()
    {
        if (null === $this->file) {
            return;
        } else {
            $dir = str_replace($this->getName(), '', $this->getAbsolutePath());
            $this->file->move($dir, $this->getName());
            unset($this->file);
        }
    }

    public function getAbsolutePath()
    {
        return null === $this->path
            ? null
            : $this->getUploadRootDir().'/'.$this->path;
    }

    public function getWebPath()
    {
        return null === $this->path
            ? null
            : $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        // the absolute directory path where uploaded
        // documents should be saved
         return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    protected function getUploadDir()
    {
        return 'files';
    }

    public function labellizeState()
    {
        if ($this->isOnline()) {
            return '<span class="label label-success">Online</span>';
        } else {
            return '<span class="label label-warning">Offline</span>';
        }
    }

    public function labellizePromote()
    {
        if ($this->isPromoted()) {
            return '<span class="label label-info">En tête</span>';
        } else {
            return;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;

        return $this;
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

    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    public function getCode()
    {
        return $this->code;
    }

    public function setPath($path)
    {
        $this->path = $path;

        return $this;
    }

    public function getPath()
    {
        return $this->path;
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

    public function setIsPromoted($isPromoted)
    {
        $this->is_promoted = $isPromoted;

        return $this;
    }

    public function getIsPromoted()
    {
        return $this->is_promoted;
    }

    public function isPromoted()
    {
        return $this->getIsPromoted();
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setIsGeneric($isGeneric)
    {
        $this->is_generic = $isGeneric;

        return $this;
    }

    public function getIsGeneric()
    {
        return $this->is_generic;
    }

    public function addTag(\Clab\TaxonomyBundle\Entity\Term $tags)
    {
        $this->tags[] = $tags;

        return $this;
    }

    public function removeTag(\Clab\TaxonomyBundle\Entity\Term $tags)
    {
        $this->tags->removeElement($tags);
    }

    public function getTags()
    {
        return $this->tags;
    }

    public function setParent(\Clab\MediaBundle\Entity\Image $parent = null)
    {
        $this->parent = $parent;
        $parent->addChildren($this);

        return $this;
    }

    public function getParent()
    {
        return $this->parent;
    }

    public function addChildren(\Clab\MediaBundle\Entity\Image $childrens)
    {
        $this->childrens[] = $childrens;

        return $this;
    }

    public function removeChildren(\Clab\MediaBundle\Entity\Image $childrens)
    {
        $this->childrens->removeElement($childrens);
    }

    public function getChildrens()
    {
        return $this->childrens;
    }

    public function setChildrens(ArrayCollection $childrens = null)
    {
        $this->childrens = $childrens;

        return $this;
    }

    public function addMultisiteCoverPicture(\Clab\MultisiteBundle\Entity\Multisite $multisiteCoverPicture)
    {
        $this->multisiteCoverPicture[] = $multisiteCoverPicture;

        return $this;
    }

    public function removeMultisiteCoverPicture(\Clab\MultisiteBundle\Entity\Multisite $multisiteCoverPicture)
    {
        $this->multisiteCoverPicture->removeElement($multisiteCoverPicture);
    }

    public function getMultisiteCoverPicture()
    {
        return $this->multisiteCoverPicture;
    }
}
