<?php

namespace App\Entity\Access;

use Doctrine\ORM\Mapping as ORM;

/**
 * Log
 *
 * @ORM\Table(name="log", indexes={@ORM\Index(name="IDX_activity_controller", columns={"controller"}), @ORM\Index(name="IDX_activity_action", columns={"action"}), @ORM\Index(name="IDX_activity_ip", columns={"ip"}), @ORM\Index(name="fk_log_user1_idx", columns={"user_id"})})
 * @ORM\Entity
 */
class Log
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="application", type="string", length=40, nullable=false)
     */
    private $application;

    /**
     * @var string
     *
     * @ORM\Column(name="bundle", type="string", length=40, nullable=false)
     */
    private $bundle;

    /**
     * @var string
     *
     * @ORM\Column(name="controller", type="string", length=40, nullable=false)
     */
    private $controller;

    /**
     * @var string
     *
     * @ORM\Column(name="action", type="string", length=40, nullable=false)
     */
    private $action;

    /**
     * @var string
     *
     * @ORM\Column(name="sid", type="string", length=80, nullable=false, options={"comment"="Session identifier"})
     */
    private $sid;

    /**
     * @var string
     *
     * @ORM\Column(name="ip", type="string", length=20, nullable=false, options={"comment"="Either IPv4 or IPv6"})
     */
    private $ip;

    /**
     * @var string
     *
     * @ORM\Column(name="referrer", type="string", length=255, nullable=false)
     */
    private $referrer;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=false, options={"default"="CURRENT_TIMESTAMP"})
     */
    private $createdAt = 'CURRENT_TIMESTAMP';

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="App\Entity\Access\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;


}
