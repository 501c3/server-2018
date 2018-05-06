<?php

namespace App\Entity\Access;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserHasChannel
 *
 * @ORM\Table(name="user_has_channel",
 *            indexes={@ORM\Index(name="fk_user_has_channel_user1_idx", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Access\UserHasChannelRepository")
 */
class UserHasChannel
{
    /**
     * @var int
     *
     * @ORM\Column(name="channel_id", type="smallint", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $channelId;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="App\Entity\Access\User", inversedBy="channels")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @return int
     */
    public function getChannelId(): int
    {
        return $this->channelId;
    }

    /**
     * @param int $channelId
     * @return UserHasChannel
     */
    public function setChannelId(int $channelId): UserHasChannel
    {
        $this->channelId = $channelId;
        return $this;
    }

    public function getChannel()
    {
        // TODO: Load channel from alternate database
    }

    /**
     * @return User
     */
    public function getUser(): User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return UserHasChannel
     */
    public function setUser(User $user): UserHasChannel
    {
        $this->user = $user;
        return $this;
    }
}
