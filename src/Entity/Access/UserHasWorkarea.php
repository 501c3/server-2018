<?php

namespace App\Entity\Access;

use Doctrine\ORM\Mapping as ORM;

/**
 * UserHasWorkarea
 *
 * @ORM\Table(name="user_has_workarea",
 *            indexes={@ORM\Index(name="fk_user_has_workarea_user1_idx", columns={"user_id"})})
 * @ORM\Entity(repositoryClass="App\Repository\Access\UserHasWorkareaRepository")
 */
class UserHasWorkarea
{
    /**
     * @var int
     *
     * @ORM\Column(name="workarea_id", type="integer", nullable=false, options={"unsigned"=true})
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $workareaId;

    /**
     * @var User
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     * @ORM\ManyToOne(targetEntity="App\Entity\Access\User", inversedBy="workareas")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     * })
     */
    private $user;

    /**
     * @return int
     */
    public function getWorkareaId(): int
    {
        return $this->workareaId;
    }

    /**
     * @param int $workareaId
     */
    public function setWorkareaId(int $workareaId): void
    {
        $this->workareaId = $workareaId;
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
     */
    public function setUser(User $user): void
    {
        $this->user = $user;
    }


}
