<?php

namespace Clab\ReviewBundle\Entity;

use Clab\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_review_review")
 * @ORM\Entity(repositoryClass="Clab\ReviewBundle\Repository\ReviewRepository")
 */
class Review
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
    protected $isOnline;

    /**
     * @ORM\Column(name="is_deleted", type="boolean")
     */
    protected $isDeleted;

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
     * @ORM\Column(name="source", type="string", length=255, nullable=true)
     */
    protected $source;

    /**
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @ORM\Column(name="body", type="text", nullable=true)
     */
    protected $body;

    /**
     * @ORM\Column(name="cook_score", type="integer")
     */
    protected $cookScore;

    /**
     * @ORM\Column(name="service_score", type="integer")
     */
    protected $serviceScore;

    /**
     * @ORM\Column(name="quality_score", type="integer")
     */
    protected $qualityScore;

    /**
     * @ORM\Column(name="hygiene_score", type="integer")
     */
    protected $hygieneScore;

    /**
     * @ORM\Column(name="order_score", type="integer", nullable = true)
     */
    protected $orderScore;

    /**
     * @ORM\Column(name="score", type="float")
     */
    protected $score;

    /**
     * @ORM\Column(name="authorName", type="string", length=255, nullable = true)
     */
    protected $authorName;

    /**
     * @ORM\Column(name="url", type="string", length=255, nullable = true)
     */
    protected $url;

    /**
     * @ORM\Column(name="is_recommended", type="boolean", length=255, nullable = true)
     */
    protected $isRecommended;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $image;

    /**
     * @ORM\Column(name="up_count", type="integer")
     */
    protected $upCount;

    /**
     * @ORM\Column(name="down_count", type="integer")
     */
    protected $downCount;

    /**
     * @ORM\Column(name="authorFacebookId", type="string", length=255, nullable=true)
     */
    protected $authorFacebookId;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", inversedBy="reviews")
     * @ORM\JoinColumn(name="restaurant_id", referencedColumnName="id")
     **/
    protected $restaurant;

    /**
     * @ORM\ManyToOne(targetEntity="Clab\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id", nullable=true)
     */
    protected $profile;

    /**
     * @ORM\OneToMany(targetEntity="Clab\ReviewBundle\Entity\Vote", mappedBy="review")
     */
    protected $votes;

    /**
     * @ORM\Column(name="response", type="text", nullable=true)
     */
    protected $response;

    /**
     * @ORM\Column(name="isRead", type="boolean")
     */
    protected $isRead;

    protected $author;

    public function __construct()
    {
        $this->created = new \DateTime();
        $this->updated = new \DateTime();
        $this->votes = new ArrayCollection();
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->setScore(1);
        $this->setIsRead(false);
        $this->setUpCount(0);
        $this->setDownCount(0);
    }

    /**
     * @return mixed
     */
    public function getIsRead()
    {
        return $this->isRead;
    }

    /**
     * @param mixed $isRead
     *
     * @return $this
     */
    public function setIsRead($isRead)
    {
        $this->isRead = $isRead;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     *
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

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

    /**
     * @return mixed
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param mixed $url
     *
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsRecommended()
    {
        return $this->isRecommended;
    }

    /**
     * @param mixed $isRecommended
     *
     * @return $this
     */
    public function setIsRecommended($isRecommended)
    {
        $this->isRecommended = $isRecommended;

        return $this;
    }

    public function isOnline()
    {
        return $this->getIsOnline();
    }
    public function isDeleted()
    {
        return $this->getIsDeleted();
    }

    public function getAuthor()
    {
        $this->author = array(
            'name' => $this->getProfile() ? $this->getProfile()->getFullName() : $this->getAuthorName(),
            'facebookCover' => $this->getAuthorFacebookId() ? 'https://graph.facebook.com/v2.3/'.$this->getAuthorFacebookId().'/picture' : null,
            'cover' => $this->getProfile() ? $this->getProfile()->getCover() : 'images/blankuser.png',
            'apiCover' => $this->getProfile() ? $this->getProfile()->getCover() : 'images/blankuser.png',
        );

        return $this->author;
    }

    public function getUploadRootDir()
    {
        // absolute path to your directory where images must be saved
        return __DIR__.'/../../../../web/'.$this->getUploadDir();
    }

    public function getUploadDir()
    {
        return 'gallery/review/images';
    }

    public function getAbsolutePath()
    {
        return null === $this->image ? null : $this->getUploadRootDir().'/'.$this->image;
    }

    public function getWebPath()
    {
        return null === $this->image ? null : '/'.$this->getUploadDir().'/'.$this->image;
    }

    /**
     * @return mixed
     */
    public function getOrderScore()
    {
        return $this->orderScore;
    }

    /**
     * @param mixed $orderScore
     *
     * @return $this
     */
    public function setOrderScore($orderScore)
    {
        $this->orderScore = $orderScore;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHygieneScore()
    {
        return $this->hygieneScore;
    }

    /**
     * @param mixed $hygieneScore
     *
     * @return $this
     */
    public function setHygieneScore($hygieneScore)
    {
        $this->hygieneScore = $hygieneScore;

        return $this;
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

    /**
     * @return mixed
     */
    public function getRestaurant()
    {
        return $this->restaurant;
    }

    /**
     * @param mixed $restaurant
     *
     * @return $this
     */
    public function setRestaurant($restaurant)
    {
        $this->restaurant = $restaurant;

        return $this;
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
     * @return mixed
     */
    public function getCookScore()
    {
        return $this->cookScore;
    }

    /**
     * @param mixed $cookScore
     *
     * @return $this
     */
    public function setCookScore($cookScore)
    {
        $this->cookScore = $cookScore;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getServiceScore()
    {
        return $this->serviceScore;
    }

    /**
     * @param mixed $serviceScore
     *
     * @return $this
     */
    public function setServiceScore($serviceScore)
    {
        $this->serviceScore = $serviceScore;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getQualityScore()
    {
        return $this->qualityScore;
    }

    /**
     * @param mixed $qualityScore
     *
     * @return $this
     */
    public function setQualityScore($qualityScore)
    {
        $this->qualityScore = $qualityScore;

        return $this;
    }

    /**
     * Set isOnline.
     *
     * @param bool $isOnline
     *
     * @return Review
     */
    public function setIsOnline($isOnline)
    {
        $this->isOnline = $isOnline;

        return $this;
    }

    /**
     * Get isOnline.
     *
     * @return bool
     */
    public function getIsOnline()
    {
        return $this->isOnline;
    }

    /**
     * Set isDeleted.
     *
     * @param bool $isDeleted
     *
     * @return Review
     */
    public function setIsDeleted($isDeleted)
    {
        $this->isDeleted = $isDeleted;

        return $this;
    }

    /**
     * Get isDeleted.
     *
     * @return bool
     */
    public function getIsDeleted()
    {
        return $this->isDeleted;
    }

    /**
     * Set created.
     *
     * @param \DateTime $created
     *
     * @return Review
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created.
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set updated.
     *
     * @param \DateTime $updated
     *
     * @return Review
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Get updated.
     *
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Set source.
     *
     * @param string $source
     *
     * @return Review
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
     * Set title.
     *
     * @param string $title
     *
     * @return Review
     */
    public function setTitle($title)
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get title.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set body.
     *
     * @param string $body
     *
     * @return Review
     */
    public function setBody($body)
    {
        $this->body = $body;

        return $this;
    }

    /**
     * Get body.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Set score.
     *
     * @param float $score
     *
     * @return Review
     */
    public function setScore()
    {
        $score = $this->cookScore + $this->hygieneScore + $this->qualityScore + $this->serviceScore;
        $this->score = ceil($score / 5);

        return $this;
    }

    /**
     * Get score.
     *
     * @return float
     */
    public function getScore()
    {
        return $this->score;
    }

    /**
     * Set authorName.
     *
     * @param string $authorName
     *
     * @return Review
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;

        return $this;
    }

    /**
     * Get authorName.
     *
     * @return string
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * Set authorFacebookId.
     *
     * @param string $authorFacebookId
     *
     * @return Review
     */
    public function setAuthorFacebookId($authorFacebookId)
    {
        $this->authorFacebookId = $authorFacebookId;

        return $this;
    }

    /**
     * Get authorFacebookId.
     *
     * @return string
     */
    public function getAuthorFacebookId()
    {
        return $this->authorFacebookId;
    }

    /**
     * Set profile.
     *
     * @param \Clab\PeopleBundle\Entity\Profile $profile
     *
     * @return Review
     */
    public function setProfile(User $profile = null)
    {
        $this->profile = $profile;

        return $this;
    }

    /**
     * Get profile.
     *
     * @return \Clab\PeopleBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }
}
