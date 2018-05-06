<?php

namespace App\Entity\Access;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * User
 *
 * @ORM\Table(name="user",
 *       uniqueConstraints={@ORM\UniqueConstraint(name="username_canonical_UNIQUE",columns={"username_canonical"}),
 *                          @ORM\UniqueConstraint(name="email_canonical_UNIQUE", columns={"email_canonical"}),
 *                          @ORM\UniqueConstraint(name="confirmation_token_UNIQUE", columns={"confirmation_token"})},
 *       indexes={@ORM\Index(name="authenticator_UNIQUE", columns={"authenticator", "authenticator_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Access\UserRepository")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="bigint", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="username", type="string", length=180, nullable=false)
     */
    private $username;

    /**
     * @var string
     * @ORM\Column(name="username_canonical", type="string", length=180, nullable=false)
     */
    private $usernameCanonical;

    /**
     * @var string
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(name="email", type="string", length=180, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_canonical", type="string", length=180, nullable=false)
     */
    private $emailCanonical;

    /**
     * @var bool
     * @ORM\Column(name="enabled", type="boolean", nullable=false)
     */
    private $enabled = false;

    /**
     * @var string|null
     *
     * @ORM\Column(name="salt", type="string", length=255, nullable=true)
     */
    private $salt;

    /**
     * @var string
     * @ORM\Column(name="password", type="string", length=255, nullable=false)
     */
    private $password;


    /**
     * @var string
     * @Assert\NotBlank()
     */
    private $plainPassword;


    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="last_login", type="datetime", nullable=true)
     */

    private $lastLogin;

    /**
     * @var array
     *
     * @ORM\Column(name="roles", type="array", nullable=false)
     */
    private $roles;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="password_requested_at", type="datetime", nullable=true)
     */
    private $passwordRequestedAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="title", type="string", length=4, nullable=true)
     */
    private $title;

    /**
     * @var string|null
     * @Assert\NotBlank()
     * @ORM\Column(name="first", type="string", length=20, nullable=true)
     */
    private $first;

    /**
     * @var string|null
     *
     * @ORM\Column(name="middle", type="string", length=20, nullable=true)
     */
    private $middle;

    /**
     * @var string
     * @Assert\NotBlank()
     * @ORM\Column(name="last", type="string", length=20, nullable=false)
     */
    private $last;

    /**
     * @var string|null
     *
     * @ORM\Column(name="suffix", type="string", length=4, nullable=true)
     */
    private $suffix;

    /**
     * @var string|null
     * @Assert\NotBlank()
     * @ORM\Column(name="mobile", type="string", length=15, nullable=true)
     */
    private $mobile;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="expire_at", type="datetime", nullable=true)
     */
    private $expireAt;

    /**
     * @var string|null
     *
     * @ORM\Column(name="authenticator", type="string", length=20, nullable=true)
     */
    private $authenticator;

    /**
     * @var string|null
     *
     * @ORM\Column(name="authenticator_id", type="string", length=60, nullable=true)
     */
    private $authenticatorId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="confirmation_token", type="string", length=255, nullable=true)
     */
    private $confirmationToken;

    /**
     * @var string|null
     *
     * @ORM\Column(name="access_token", type="string", length=255, nullable=true)
     */
    private $accessToken;

    /**
     * @var string|null
     *
     * @ORM\Column(name="refresh_token", type="string", length=255, nullable=true)
     */
    private $refreshToken;

    /**
     * @var \DateTime|null
     *
     * @ORM\Column(name="created_at", type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var ArrayCollection
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Access\Controller", inversedBy="user")
     * @ORM\JoinTable(name="user_has_controller",
     *   joinColumns={
     *     @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     *   },
     *   inverseJoinColumns={
     *     @ORM\JoinColumn(name="controller_id", referencedColumnName="id")
     *   }
     * )
     */
    private $controllers;



    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Access\UserHasChannel", mappedBy="user")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $channels;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Access\UserHasWorkarea", mappedBy="user")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $workareas;

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getUsername(): ?string
    {
        return $this->username;
    }

    /**
     * @param string $username
     * @return User
     */
    public function setUsername(string $username): User
    {
        $this->username = $username;
        $this->usernameCanonical=strtolower($username);
        return $this;
    }

    /**
     * @return string
     */
    public function getUsernameCanonical(): string
    {
        return $this->usernameCanonical;
    }

    /**
     * @param string $usernameCanonical
     * @return $this;
     */
    public function setUsernameCanonical(string $usernameCanonical): User
    {
        $this->usernameCanonical = $usernameCanonical;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return User
     */
    public function setEmail(string $email): User
    {
        $this->emailCanonical = strtolower($email);
        $this->email = $email;
        return $this;
    }

    /**
     * @return string
     */
    public function getEmailCanonical(): string
    {
        return $this->emailCanonical;
    }

    /**
     * @param string $emailCanonical
     * @return User
     */
    public function setEmailCanonical(string $emailCanonical): User
    {
        $this->emailCanonical = $emailCanonical;
        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled?true:false;
    }

    /**
     * @param bool $enabled
     * @return User
     */
    public function setEnabled(bool $enabled): User
    {
        $this->enabled = $enabled;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSalt(): ?string
    {
        return $this->salt;
    }

    /**
     * @param null|string $salt
     * @return User
     */
    public function setSalt(?string $salt): User
    {
        $this->salt = $salt;
        return $this;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * @param mixed $plainPassword
     */
    public function setPlainPassword($plainPassword): User
    {
        $this->plainPassword = $plainPassword;
        // guarantees that the entity looks "dirty" to Doctrine
        // when changing the plainPassword
        $this->password = null;
        return $this;
    }




    /**
     * @return mixed
     */
    public function getPlainPassword()
    {
        return $this->plainPassword;
    }


    /**
     * @param string $password
     * @return User
     */
    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getLastLogin(): ?\DateTime
    {
        return $this->lastLogin;
    }

    /**
     * @param \DateTime|null $lastLogin
     * @return User
     */
    public function setLastLogin(?\DateTime $lastLogin): User
    {
        $this->lastLogin = $lastLogin;
        return $this;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     * @return User
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getPasswordRequestedAt(): ?\DateTime
    {
        return $this->passwordRequestedAt;
    }

    /**
     * @param \DateTime|null $passwordRequestedAt
     * @return User
     */
    public function setPasswordRequestedAt(?\DateTime $passwordRequestedAt): User
    {
        $this->passwordRequestedAt = $passwordRequestedAt;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getTitle(): ?string
    {
        return $this->title;
    }

    /**
     * @param null|string $title
     * @return User
     */
    public function setTitle(?string $title): User
    {
        $this->title = $title;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getFirst(): ?string
    {
        return $this->first;
    }

    /**
     * @param null|string $first
     * @return User
     */
    public function setFirst(?string $first): User
    {
        $this->first = $first;
        return $this;

    }

    /**
     * @return null|string
     */
    public function getMiddle(): ?string
    {
        return $this->middle;
    }

    /**
     * @param null|string $middle
     * @return User
     */
    public function setMiddle(?string $middle): User
    {
        $this->middle = $middle;
        return $this;
    }

    /**
     * @return string
     */
    public function getLast(): ?string
    {
        return $this->last;
    }

    /**
     * @param string $last
     * @return User
     */
    public function setLast(string $last): User
    {
        $this->last = $last;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getSuffix(): ?string
    {
        return $this->suffix;
    }

    /**
     * @param null|string $suffix
     * @return User
     */
    public function setSuffix(?string $suffix): User
    {
        $this->suffix = $suffix;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getMobile(): ?string
    {
        return $this->mobile;
    }

    /**
     * @param null|string $mobile
     * @return User
     */
    public function setMobile(?string $mobile): User
    {
        $this->mobile = $mobile;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getExpireAt(): ?\DateTime
    {
        return $this->expireAt;
    }

    /**
     * @param \DateTime|null $expireAt
     * @return User
     */
    public function setExpireAt(?\DateTime $expireAt): User
    {
        $this->expireAt = $expireAt;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAuthenticator(): ?string
    {
        return $this->authenticator;
    }

    /**
     * @param null|string $authenticator
     * @return User
     */
    public function setAuthenticator(?string $authenticator): User
    {
        $this->authenticator = $authenticator;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAuthenticatorId(): ?string
    {
        return $this->authenticatorId;
    }

    /**
     * @param null|string $authenticatorId
     * @return User
     */
    public function setAuthenticatorId(?string $authenticatorId): User
    {
        $this->authenticatorId = $authenticatorId;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getConfirmationToken(): ?string
    {
        return $this->confirmationToken;
    }

    /**
     * @param null|string $confirmationToken
     * @return User
     */
    public function setConfirmationToken(?string $confirmationToken): User
    {
        $this->confirmationToken = $confirmationToken;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getAccessToken(): ?string
    {
        return $this->accessToken;
    }

    /**
     * @param null|string $accessToken
     * @return User
     */
    public function setAccessToken(?string $accessToken): User
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    /**
     * @return null|string
     */
    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    /**
     * @param null|string $refreshToken
     * @return User
     */
    public function setRefreshToken(?string $refreshToken): User
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    /**
     * @param \DateTime|null $createdAt
     * @return User
     */
    public function setCreatedAt(?\DateTime $createdAt): User
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getControllers(): ArrayCollection
    {
        if (is_null ($this->controllers)){
            return new ArrayCollection();
        }else{
            $collection=$this->controllers->unwrap();
            return $collection;
        }
    }

    /**
     * @param ArrayCollection $controllers
     * @return User
     */
    public function setControllers(ArrayCollection $controllers): User
    {
        $this->controllers = $controllers;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getChannels(): ArrayCollection
    {
        if(is_null($this->channels)){
            $collection = new ArrayCollection();
            return $collection;
        } else {
            $collection=$this->channels->unwrap();
            return $collection;
        }
    }

    /**
     * @param ArrayCollection $channels
     */
    public function setChannels(ArrayCollection $channels): void
    {
        $this->channels = $channels;
    }

    /**
     * @return ArrayCollection
     */
    public function getWorkareas(): ArrayCollection
    {
        $this->workareas->initialize();
        $collection=$this->workareas->unwrap();
        return $collection;
    }

    /**
     * @param ArrayCollection $workareas
     */
    public function setWorkareas(ArrayCollection $workareas): void
    {
        $this->workareas = $workareas;
    }

    /**
     * Checks whether the user's account has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw an AccountExpiredException and prevent login.
     *
     * @return bool true if the user's account is non expired, false otherwise
     *
     * @see AccountExpiredException
     */
    public function isAccountNonExpired()
    {
        if (is_null($this->expireAt)) {return true;}
        return date_diff(new \DateTime('now'), $this->expireAt,true)?true:false;
    }

    /**
     * Checks whether the user is locked.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a LockedException and prevent login.
     *
     * @return bool true if the user is not locked, false otherwise
     *
     * @see LockedException
     */
    public function isAccountNonLocked()
    {
        return $this->enabled;
    }

    /**
     * Checks whether the user's credentials (password) has expired.
     *
     * Internally, if this method returns false, the authentication system
     * will throw a CredentialsExpiredException and prevent login.
     *
     * @return bool true if the user's credentials are non expired, false otherwise
     *
     * @see CredentialsExpiredException
     */
    public function isCredentialsNonExpired()
    {
        return true;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
    }

    /**
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     * @since 5.1.0
     */
    public function serialize()
    {
        return serialize([
            $this->id,
            $this->username,
            $this->usernameCanonical,
            $this->salt,
            $this->password,
            $this->lastLogin,
            $this->title,
            $this->first,
            $this->middle,
            $this->last,
            $this->suffix,
            $this->email,
            $this->emailCanonical,
            $this->mobile,
            $this->expireAt,
            $this->authenticator,
            $this->authenticatorId,
            $this->roles,
            $this->confirmationToken,
            $this->accessToken,
            $this->refreshToken,
            $this->channels,
            $this->controllers,
            $this->workareas

        ]);
    }

    /**
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     * @since 5.1.0
     */
    public function unserialize($serialized)
    {
        list($this->id,
            $this->username,
            $this->usernameCanonical,
            $this->salt,
            $this->password,
            $this->lastLogin,
            $this->title,
            $this->first,
            $this->middle,
            $this->last,
            $this->suffix,
            $this->email,
            $this->emailCanonical,
            $this->mobile,
            $this->expireAt,
            $this->authenticator,
            $this->authenticatorId,
            $this->roles,
            $this->confirmationToken,
            $this->accessToken,
            $this->refreshToken,
            $this->channels,
            $this->controllers,
            $this->workareas
            )=unserialize($serialized);
    }

    public function __toString()
    {
        return 'App/Entity/Access/User';
    }


}
