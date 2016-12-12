<?php

namespace Clab\SocialBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="clab_social_profile")
 * @ORM\Entity
 */
class SocialProfile
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(name="service", type="string", nullable=true)
     */
    private $service;

    /**
     * @ORM\Column(name="facebook_id", type="string", nullable=true)
     */
    private $facebook_id;

    /**
     * @ORM\Column(name="facebook_access_token", type="string", length=500, nullable=true)
     */
    private $facebook_access_token;

    /**
     * @ORM\Column(name="facebook_access_token_expire", type="datetime", nullable=true)
     */
    private $facebook_access_token_expire;

    /**
     * @ORM\Column(name="facebook_data", type="text", nullable=true)
     */
    private $facebook_data;

    /**
     * @ORM\OneToMany(targetEntity="SocialFacebookPage", mappedBy="social_profile", cascade={"persist", "remove"})
     */
    protected $facebook_pages;

     /**
     * @ORM\Column(name="twitter_id", type="string", nullable=true)
     */
    private $twitter_id;

    /**
     * @ORM\Column(name="twitter_access_token", type="string", length=500, nullable=true)
     */
    private $twitter_access_token;

    /**
     * @ORM\Column(name="twitter_access_secret", type="string", length=500, nullable=true)
     */
    private $twitter_access_secret;

    /**
     * @ORM\Column(name="twitter_data", type="text", nullable=true)
     */
    private $twitter_data;

    /**
     * @ORM\Column(name="staticFacebookId", type="string", length=255, nullable=true)
     */
    private $staticFacebookId;

    /**
     * @ORM\Column(name="staticTwitterId", type="string", length=255, nullable=true)
     */
    private $staticTwitterId;

    /**
     * @ORM\Column(name="staticFoursquareId", type="string", length=255, nullable=true)
     */
    private $staticFoursquareId;

    /**
     * @ORM\Column(name="staticTumblrId", type="string", length=255, nullable=true)
     */
    private $staticTumblrId;

    /**
     * @ORM\Column(name="staticInstagramId", type="string", length=255, nullable=true)
     */
    private $staticInstagramId;

    /**
     * @ORM\Column(name="hashtag", type="string", length=255, nullable=true)
     */
    private $hashtag;

    /**
     * @ORM\Column(name="tttEventValidationMessage", type="text", nullable=true)
     */
    private $tttEventValidationMessage;

    /**
     * @ORM\Column(name="tttEventAnnulationMessage", type="text", nullable=true)
     */
    private $tttEventAnnulationMessage;

    /**
     * @ORM\Column(name="tttEventToFacebook", type="boolean", nullable=true)
     */
    private $tttEventToFacebook;

    /**
     * @ORM\Column(name="tttEventToTwitter", type="boolean", nullable=true)
     */
    private $tttEventToTwitter;

    /**
     * @ORM\Column(name="facebookSynch", type="boolean", nullable=true)
     */
    private $facebookSynch;

    public function __construct()
    {
        $this->facebook_pages = new \Doctrine\Common\Collections\ArrayCollection();
        $this->setTttEventToFacebook(false);
        $this->setTttEventToTwitter(false);
        $this->setFacebookSynch(false);
        $this->setService('clickeat');
    }

    public function hasPage($id)
    {
    	foreach ($this->getFacebookPages() as $page) {
    	    if($page->getFacebookId() == $id) { return $page; }
    	}
    	return false;
    }

    public function facebookDataDecoded()
    {
	   return unserialize($this->getFacebookData());
    }

    public function twitterDataDecoded()
    {
       return unserialize($this->getTwitterData());
    }

    public function getFacebookPage()
    {
        foreach ($this->getFacebookPages() as $page) {
            if($page->isAvailable()) {
                return $page;
            }
        }

        return null;
    }

    public function getOnlineFacebookPages()
    {
        $pages = array();

        foreach ($this->getFacebookPages() as $page) {
            if($page->isAvailable()) {
                $pages[] = $page;
            }
        }

        return $pages;
    }

    public function getCurrentFacebookPage()
    {
        $find = false;
        $currentPage = null;
        foreach ($this->getFacebookPages() as $page) {
            if($page->isAvailable() && !$find) {
                $currentPage = $page;
                $find = true;
            } else {
                $page->setIsOnline(false);
            }
        }
        return $currentPage;
    }

    public function getId()
    {
	   return $this->id;
    }

    public function setService($service)
    {
        $this->service = $service;
        return $this;
    }

    public function getService()
    {
        return $this->service;
    }

    public function setFacebookAccessToken($facebookAccessToken)
    {
    	$this->facebook_access_token = $facebookAccessToken;

    	return $this;
    }

    public function getFacebookAccessToken()
    {
	   return $this->facebook_access_token;
    }

    public function setFacebookAccessTokenExpire($facebookAccessTokenExpire)
    {
    	$this->facebook_access_token_expire = $facebookAccessTokenExpire;

    	return $this;
    }

    public function getFacebookAccessTokenExpire()
    {
	   return $this->facebook_access_token_expire;
    }

    public function addFacebookPage(\Clab\SocialBundle\Entity\SocialFacebookPage $facebookPages)
    {
    	$this->facebook_pages[] = $facebookPages;
    	$facebookPages->setSocialProfile($this);
    	return $this;
    }

    public function removeFacebookPage(\Clab\SocialBundle\Entity\SocialFacebookPage $facebookPages)
    {
	   $this->facebook_pages->removeElement($facebookPages);
    }

    public function getFacebookPages()
    {
	   return $this->facebook_pages;
    }

    public function setFacebookData($facebookData)
    {
	   $this->facebook_data = $facebookData;
	   return $this;
    }

    public function getFacebookData()
    {
	   return $this->facebook_data;
    }

    public function setFacebookId($facebookId)
    {
	   $this->facebook_id = $facebookId;
	   return $this;
    }

    public function getFacebookId()
    {
	   return $this->facebook_id;
    }

    public function setStaticFacebookId($staticFacebookId)
    {
        $this->staticFacebookId = $staticFacebookId;
        return $this;
    }

    public function getStaticFacebookId()
    {
        return $this->staticFacebookId;
    }

    public function setStaticTwitterId($staticTwitterId)
    {
        $this->staticTwitterId = $staticTwitterId;
        return $this;
    }

    public function getStaticTwitterId()
    {
        return $this->staticTwitterId;
    }

    public function setStaticFoursquareId($staticFoursquareId)
    {
        $this->staticFoursquareId = $staticFoursquareId;
        return $this;
    }

    public function getStaticFoursquareId()
    {
        return $this->staticFoursquareId;
    }

    public function setStaticTumblrId($staticTumblrId)
    {
        $this->staticTumblrId = $staticTumblrId;
        return $this;
    }

    public function getStaticTumblrId()
    {
        return $this->staticTumblrId;
    }

    public function setStaticInstagramId($staticInstagramId)
    {
        $this->staticInstagramId = $staticInstagramId;
        return $this;
    }

    public function getStaticInstagramId()
    {
        return $this->staticInstagramId;
    }

    public function setHashtag($hashtag)
    {
        $this->hashtag = $hashtag;
        return $this;
    }

    public function getHashtag()
    {
        return $this->hashtag;
    }

    public function setTttEventValidationMessage($tttEventValidationMessage)
    {
        $this->tttEventValidationMessage = $tttEventValidationMessage;
        return $this;
    }

    public function getTttEventValidationMessage()
    {
        return $this->tttEventValidationMessage;
    }

    public function setTttEventAnnulationMessage($tttEventAnnulationMessage)
    {
        $this->tttEventAnnulationMessage = $tttEventAnnulationMessage;
        return $this;
    }

    public function getTttEventAnnulationMessage()
    {
        return $this->tttEventAnnulationMessage;
    }

    public function setTttEventToFacebook($tttEventToFacebook)
    {
        $this->tttEventToFacebook = $tttEventToFacebook;
        return $this;
    }

    public function getTttEventToFacebook()
    {
        return $this->tttEventToFacebook;
    }

    public function setTwitterId($twitterId)
    {
        $this->twitter_id = $twitterId;
        return $this;
    }

    public function getTwitterId()
    {
        return $this->twitter_id;
    }

    public function setTwitterAccessToken($twitterAccessToken)
    {
        $this->twitter_access_token = $twitterAccessToken;
        return $this;
    }

    public function getTwitterAccessToken()
    {
        return $this->twitter_access_token;
    }

    public function setTwitterAccessSecret($twitterAccessSecret)
    {
        $this->twitter_access_secret = $twitterAccessSecret;
        return $this;
    }

    public function getTwitterAccessSecret()
    {
        return $this->twitter_access_secret;
    }

    public function setTwitterData($twitterData)
    {
        $this->twitter_data = $twitterData;
        return $this;
    }

    public function getTwitterData()
    {
        return $this->twitter_data;
    }

    public function setTttEventToTwitter($tttEventToTwitter)
    {
        $this->tttEventToTwitter = $tttEventToTwitter;
        return $this;
    }

    public function getTttEventToTwitter()
    {
        return $this->tttEventToTwitter;
    }

    public function setFacebookSynch($facebookSynch)
    {
        $this->facebookSynch = $facebookSynch;
        return $this;
    }

    public function getFacebookSynch()
    {
        return $this->facebookSynch;
    }
}
