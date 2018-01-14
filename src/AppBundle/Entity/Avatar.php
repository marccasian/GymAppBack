<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Avatar
 *
 * @ORM\Table(name="avatar", indexes={@ORM\Index(name="user_avatar_idx", columns={"Username"})})
 * @ORM\Entity
 */
class Avatar
{

    /**
     * @Assert\Image()
     * @var
     */

    private $image;

    /**
     * Sets file.
     *
     * @param UploadedFile $file
     */
    public function setImage(UploadedFile $image = null)
    {
        $this->image = $image;
    }

    /**
     * Get file.
     *
     * @return UploadedFile
     */
    public function getImage()
    {
        return $this->image;
    }
    /**
     * @var string
     *
     * @ORM\Column(name="File", type="string", length=255, nullable=false)
     */
    private $file;

    /**
     * @var integer
     *
     * @ORM\Column(name="idavatar", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idavatar;

    /**
     * @var \AppBundle\Entity\User
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\User")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="Username", referencedColumnName="Username")
     * })
     */
    private $username;

    /**
     * Avatar constructor.
     */
    public function __construct()
    {
    }

    /**
     * @return string
     */
    public function getFile(): string
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile(string $file)
    {
        $this->file = $file;
    }

    /**
     * @return int
     */
    public function getIdavatar(): int
    {
        return $this->idavatar;
    }

    /**
     * @param int $idavatar
     */
    public function setIdavatar(int $idavatar)
    {
        $this->idavatar = $idavatar;
    }

    /**
     * @return User
     */
    public function getUsername(): User
    {
        return $this->username;
    }

    /**
     * @param User $username
     */
    public function setUsername(User $username)
    {
        $this->username = $username;
    }




}

