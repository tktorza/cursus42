<?php

namespace Clab\RestaurantBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="clab_restaurant_deal")
 * @ORM\Entity(repositoryClass="Clab\RestaurantBundle\Entity\Repository\DealRepository")
 */
class Deal
{
    /**
     * Interest :
     * 0 non renseigné
     * 1 interessé
     * 2 non interessé.
     */
    const DEAL_INTEREST_NULL = 0;
    const DEAL_INTEREST_ON = 1;
    const DEAL_INTEREST_OFF = 2;

    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

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
     * @ORM\Column(name="hold", type="boolean")
     */
    private $hold;

    /**
     * @ORM\Column(name="holdDate", type="datetime", nullable=true)
     */
    private $holdDate;

    /**
     * @ORM\Column(name="holdComment", type="text", nullable=true)
     */
    private $holdComment;

    /**
     * @ORM\Column(name="ready", type="boolean")
     */
    private $ready;

    /**
     * @ORM\Column(name="readyDate", type="datetime", nullable=true)
     */
    private $readyDate;

    /**
     * @ORM\Column(name="statusHistory", type="text", nullable=true)
     */
    private $statusHistory;

    /**
     * @ORM\Column(name="discover", type="text", nullable=true)
     */
    private $discover;

    /**
     * @ORM\Column(name="menuSent", type="boolean")
     */
    private $menuSent;

    /**
     * @ORM\Column(name="interestedInOrder", type="integer", nullable=true)
     */
    private $interestedInOrder;

    /**
     * @ORM\Column(name="interestedInTakeaway", type="integer", nullable=true)
     */
    private $interestedInTakeaway;

    /**
     * @ORM\Column(name="interestedInDelivery", type="integer", nullable=true)
     */
    private $interestedInDelivery;

    /**
     * @ORM\Column(name="interestedInWebsite", type="integer", nullable=true)
     */
    private $interestedInWebsite;

    /**
     * @ORM\Column(name="hasWebsite", type="boolean")
     */
    private $hasWebsite;

    /**
     * @ORM\Column(name="interestedInEmbedOrder", type="integer", nullable=true)
     */
    private $interestedInEmbedOrder;

    /**
     * @ORM\Column(name="hasEmbedOrder", type="boolean")
     */
    private $hasEmbedOrder;

    /**
     * @ORM\Column(name="interestedInFacebookOrder", type="integer", nullable=true)
     */
    private $interestedInFacebookOrder;

    /**
     * @ORM\Column(name="hasFacebookOrder", type="boolean")
     */
    private $hasFacebookOrder;

    /**
     * @ORM\Column(name="interestedInApp", type="integer", nullable=true)
     */
    private $interestedInApp;

    /**
     * @ORM\Column(name="hasApp", type="boolean")
     */
    private $hasApp;

    /**
     * @ORM\Column(name="interestedInFoodtruckEmbed", type="integer", nullable=true)
     */
    private $interestedInFoodtruckEmbed;

    /**
     * @ORM\Column(name="hasFoodtruckEmbed", type="boolean")
     */
    private $hasFoodtruckEmbed;

    /**
     * @ORM\Column(name="interestedInFoodtruckEmbedFacebook", type="integer", nullable=true)
     */
    private $interestedInFoodtruckEmbedFacebook;

    /**
     * @ORM\Column(name="hasFoodtruckEmbedFacebook", type="boolean")
     */
    private $hasFoodtruckEmbedFacebook;

    /**
     * @ORM\Column(name="interestedInFoodtruckPlanningEmbed", type="integer", nullable=true)
     */
    private $interestedInFoodtruckPlanningEmbed;

    /**
     * @ORM\Column(name="hasFoodtruckPlanningEmbed", type="boolean")
     */
    private $hasFoodtruckPlanningEmbed;

    /**
     * @ORM\Column(name="interestedInFoodtruckPlanningEmbedFacebook", type="integer", nullable=true)
     */
    private $interestedInFoodtruckPlanningEmbedFacebook;

    /**
     * @ORM\Column(name="hasFoodtruckPlanningEmbedFacebook", type="boolean")
     */
    private $hasFoodtruckPlanningEmbedFacebook;

    /**
     * @ORM\Column(name="comment", type="text", nullable=true)
     */
    private $comment;

    /**
     * @ORM\Column(name="sticker", type="boolean")
     */
    private $sticker;

    /**
     * @ORM\Column(name="photo", type="boolean")
     */
    private $photo;

    /**
     * @ORM\Column(name="plv", type="boolean")
     */
    private $plv;

    /**
     * @ORM\Column(name="discount", type="boolean")
     */
    private $discount;

    /**
     * @ORM\Column(name="blog", type="boolean")
     */
    private $blog;

    /**
     * @ORM\Column(name="sss", type="boolean")
     */
    private $sss;

    /**
     * @ORM\Column(name="sssGame", type="boolean")
     */
    private $sssGame;

    /**
     * @ORM\Column(name="sssInfo", type="boolean")
     */
    private $sssInfo;

    /**
     * @ORM\OneToOne(targetEntity="Clab\RestaurantBundle\Entity\Restaurant", mappedBy="deal")
     */
    protected $restaurant;

    public function __toString()
    {
        return $this->id.' - '.$this->restaurant;
    }

    public function __construct()
    {
        $this->setIsOnline(true);
        $this->setIsDeleted(false);
        $this->created = new \DateTime();
        $this->updated = new \DateTime();

        $this->setHold(false);
        $this->setReady(false);
        $this->setMenuSent(false);
        $this->setInterestedInOrder(false);
        $this->setInterestedInTakeaway(false);
        $this->setInterestedInDelivery(false);
        $this->setInterestedInWebsite(false);
        $this->setHasWebsite(false);
        $this->setInterestedInEmbedOrder(false);
        $this->setHasEmbedOrder(false);
        $this->setInterestedInFacebookOrder(false);
        $this->setHasFacebookOrder(false);
        $this->setInterestedInApp(false);
        $this->setHasApp(false);
        $this->setInterestedInFoodtruckEmbed(false);
        $this->setHasFoodtruckEmbed(false);
        $this->setInterestedInFoodtruckEmbedFacebook(false);
        $this->setHasFoodtruckEmbedFacebook(false);
        $this->setInterestedInFoodtruckPlanningEmbed(false);
        $this->setHasFoodtruckPlanningEmbed(false);
        $this->setInterestedInFoodtruckPlanningEmbedFacebook(false);
        $this->setHasFoodtruckPlanningEmbedFacebook(false);
        $this->setSticker(false);
        $this->setPhoto(false);
        $this->setPlv(false);
        $this->setDiscount(false);
        $this->setBlog(false);

        $this->setSss(false);
        $this->setSSSGame(false);
        $this->setSSSInfo(false);
    }

    public function isAllowed(\Clab\UserBundle\Entity\User $user)
    {
        return true;
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

    public static function getDiscoverTypes()
    {
        return array(
            'Equipe Commerciale' => 'Equipe Commerciale',
            'Site Pro' => 'Site Pro',
            'Google' => 'Google',
            'Facebook' => 'Facebook',
            'Blog' => 'Blog',
            'Newsletter' => 'Newsletter',
            'Autre' => 'Autre',
        );
    }

    public static function getInterestChoices()
    {
        return array(
            self::DEAL_INTEREST_ON => 'Intéressé',
            self::DEAL_INTEREST_NULL => 'Non renseigné',
            self::DEAL_INTEREST_OFF => 'Pas intéressé',
        );
    }

    public function getHistoryDate($status)
    {
        foreach ($this->getStatusHistory() as $history) {
            if ($history['status'] == $status) {
                $date = new \Datetime();
                $date->setTimestamp($history['time']);

                return $date;
            }
        }

        return;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setDiscover($discover)
    {
        $this->discover = $discover;

        return $this;
    }

    public function getDiscover()
    {
        return $this->discover;
    }

    public function setHold($hold)
    {
        $this->hold = $hold;

        return $this;
    }

    public function getHold()
    {
        return $this->hold;
    }

    public function setHoldDate($holdDate)
    {
        $this->holdDate = $holdDate;

        return $this;
    }

    public function getHoldDate()
    {
        return $this->holdDate;
    }

    public function setHoldComment($holdComment)
    {
        $this->holdComment = $holdComment;

        return $this;
    }

    public function getHoldComment()
    {
        return $this->holdComment;
    }

    public function setReady($ready)
    {
        $this->ready = $ready;

        return $this;
    }

    public function getReady()
    {
        return $this->ready;
    }

    public function setReadyDate($readyDate)
    {
        $this->readyDate = $readyDate;

        return $this;
    }

    public function getReadyDate()
    {
        return $this->readyDate;
    }

    public function setStatusHistory(array $statusHistory)
    {
        $this->statusHistory = serialize($statusHistory);

        return $this;
    }

    public function getStatusHistory()
    {
        if ($this->statusHistory) {
            return unserialize($this->statusHistory);
        } else {
            return array();
        }
    }

    public function addStatusHistory($status, $time = null)
    {
        $history = $this->getStatusHistory();

        if (!$time) {
            $time = time();
        }

        $history[] = array('status' => $status, 'time' => $time);

        $this->setStatusHistory($history);
    }

    public function setMenuSent($menuSent)
    {
        $this->menuSent = $menuSent;

        return $this;
    }

    public function getMenuSent()
    {
        return $this->menuSent;
    }

    public function setInterestedInOrder($interestedInOrder)
    {
        $this->interestedInOrder = $interestedInOrder;

        return $this;
    }

    public function getInterestedInOrder()
    {
        return $this->interestedInOrder;
    }

    public function setInterestedInTakeaway($interestedInTakeaway)
    {
        $this->interestedInTakeaway = $interestedInTakeaway;

        return $this;
    }

    public function getInterestedInTakeaway()
    {
        return $this->interestedInTakeaway;
    }

    public function setInterestedInDelivery($interestedInDelivery)
    {
        $this->interestedInDelivery = $interestedInDelivery;

        return $this;
    }

    public function getInterestedInDelivery()
    {
        return $this->interestedInDelivery;
    }

    public function setInterestedInWebsite($interestedInWebsite)
    {
        $this->interestedInWebsite = $interestedInWebsite;

        return $this;
    }

    public function getInterestedInWebsite()
    {
        return $this->interestedInWebsite;
    }

    public function setHasWebsite($hasWebsite)
    {
        $this->hasWebsite = $hasWebsite;

        return $this;
    }

    public function getHasWebsite()
    {
        return $this->hasWebsite;
    }

    public function setInterestedInEmbedOrder($interestedInEmbedOrder)
    {
        $this->interestedInEmbedOrder = $interestedInEmbedOrder;

        return $this;
    }

    public function getInterestedInEmbedOrder()
    {
        return $this->interestedInEmbedOrder;
    }

    public function setHasEmbedOrder($hasEmbedOrder)
    {
        $this->hasEmbedOrder = $hasEmbedOrder;

        return $this;
    }

    public function getHasEmbedOrder()
    {
        return $this->hasEmbedOrder;
    }

    public function setInterestedInFacebookOrder($interestedInFacebookOrder)
    {
        $this->interestedInFacebookOrder = $interestedInFacebookOrder;

        return $this;
    }

    public function getInterestedInFacebookOrder()
    {
        return $this->interestedInFacebookOrder;
    }

    public function setHasFacebookOrder($hasFacebookOrder)
    {
        $this->hasFacebookOrder = $hasFacebookOrder;

        return $this;
    }

    public function getHasFacebookOrder()
    {
        return $this->hasFacebookOrder;
    }

    public function setInterestedInApp($interestedInApp)
    {
        $this->interestedInApp = $interestedInApp;

        return $this;
    }

    public function getInterestedInApp()
    {
        return $this->interestedInApp;
    }

    public function setHasApp($hasApp)
    {
        $this->hasApp = $hasApp;

        return $this;
    }

    public function getHasApp()
    {
        return $this->hasApp;
    }

    public function setInterestedInFoodtruckEmbed($interestedInFoodtruckEmbed)
    {
        $this->interestedInFoodtruckEmbed = $interestedInFoodtruckEmbed;

        return $this;
    }

    public function getInterestedInFoodtruckEmbed()
    {
        return $this->interestedInFoodtruckEmbed;
    }

    public function setInterestedInFoodtruckEmbedFacebook($interestedInFoodtruckEmbedFacebook)
    {
        $this->interestedInFoodtruckEmbedFacebook = $interestedInFoodtruckEmbedFacebook;

        return $this;
    }

    public function getInterestedInFoodtruckEmbedFacebook()
    {
        return $this->interestedInFoodtruckEmbedFacebook;
    }

    public function setInterestedInFoodtruckPlanningEmbed($interestedInFoodtruckPlanningEmbed)
    {
        $this->interestedInFoodtruckPlanningEmbed = $interestedInFoodtruckPlanningEmbed;

        return $this;
    }

    public function getInterestedInFoodtruckPlanningEmbed()
    {
        return $this->interestedInFoodtruckPlanningEmbed;
    }

    public function setInterestedInFoodtruckPlanningEmbedFacebook($interestedInFoodtruckPlanningEmbedFacebook)
    {
        $this->interestedInFoodtruckPlanningEmbedFacebook = $interestedInFoodtruckPlanningEmbedFacebook;

        return $this;
    }

    public function getInterestedInFoodtruckPlanningEmbedFacebook()
    {
        return $this->interestedInFoodtruckPlanningEmbedFacebook;
    }

    public function setRestaurant(\Clab\RestaurantBundle\Entity\Restaurant $restaurant = null)
    {
        $this->restaurant = $restaurant;

        return $this;
    }

    public function getRestaurant()
    {
        return $this->restaurant;
    }

    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    public function getComment()
    {
        return $this->comment;
    }

    public function setHasFoodtruckEmbed($hasFoodtruckEmbed)
    {
        $this->hasFoodtruckEmbed = $hasFoodtruckEmbed;

        return $this;
    }

    public function getHasFoodtruckEmbed()
    {
        return $this->hasFoodtruckEmbed;
    }

    public function setHasFoodtruckEmbedFacebook($hasFoodtruckEmbedFacebook)
    {
        $this->hasFoodtruckEmbedFacebook = $hasFoodtruckEmbedFacebook;

        return $this;
    }

    public function getHasFoodtruckEmbedFacebook()
    {
        return $this->hasFoodtruckEmbedFacebook;
    }

    public function setHasFoodtruckPlanningEmbed($hasFoodtruckPlanningEmbed)
    {
        $this->hasFoodtruckPlanningEmbed = $hasFoodtruckPlanningEmbed;

        return $this;
    }

    public function getHasFoodtruckPlanningEmbed()
    {
        return $this->hasFoodtruckPlanningEmbed;
    }

    public function setHasFoodtruckPlanningEmbedFacebook($hasFoodtruckPlanningEmbedFacebook)
    {
        $this->hasFoodtruckPlanningEmbedFacebook = $hasFoodtruckPlanningEmbedFacebook;

        return $this;
    }

    public function getHasFoodtruckPlanningEmbedFacebook()
    {
        return $this->hasFoodtruckPlanningEmbedFacebook;
    }

    public function setSticker($sticker)
    {
        $this->sticker = $sticker;

        return $this;
    }

    public function getSticker()
    {
        return $this->sticker;
    }

    public function setPhoto($photo)
    {
        $this->photo = $photo;

        return $this;
    }

    public function getPhoto()
    {
        return $this->photo;
    }

    public function setPlv($plv)
    {
        $this->plv = $plv;

        return $this;
    }

    public function getPlv()
    {
        return $this->plv;
    }

    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    public function getDiscount()
    {
        return $this->discount;
    }

    public function setBlog($blog)
    {
        $this->blog = $blog;

        return $this;
    }

    public function getBlog()
    {
        return $this->blog;
    }

    public function setSss($sss)
    {
        $this->sss = $sss;

        return $this;
    }

    public function getSss()
    {
        return $this->sss;
    }

    public function setSssGame($sssGame)
    {
        $this->sssGame = $sssGame;

        return $this;
    }

    public function getSssGame()
    {
        return $this->sssGame;
    }

    public function setSssInfo($sssInfo)
    {
        $this->sssInfo = $sssInfo;

        return $this;
    }

    public function getSssInfo()
    {
        return $this->sssInfo;
    }
}
