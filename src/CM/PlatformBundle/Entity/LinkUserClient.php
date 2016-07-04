<?php

namespace CM\PlatformBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * LinkUserClient
 *
 * @ORM\Table()
 * @ORM\Entity(repositoryClass="CM\PlatformBundle\Entity\LinkUserClientRepository")
 */
class LinkUserClient
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var integer
     *
     * @ORM\Column(name="userId", type="integer")
     */
    private $userId;

    /**
     * @var integer
     * @ORM\ManyToOne(targetEntity="CM\UserBundle\Entity\User", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $userClientId;


    

    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userId
     *
     * @param integer $userId
     * @return LinkUserClient
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;

        return $this;
    }

    /**
     * Get userId
     *
     * @return integer 
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * Set userClientId
     *
     * @param \CM\UserBundle\Entity\User $userClientId
     * @return LinkUserClient
     */
    public function setUserClientId(\CM\UserBundle\Entity\User $userClientId)
    {
        $this->userClientId = $userClientId;

        return $this;
    }

    /**
     * Get userClientId
     *
     * @return \CM\UserBundle\Entity\User 
     */
    public function getUserClientId()
    {
        return $this->userClientId;
    }
}
