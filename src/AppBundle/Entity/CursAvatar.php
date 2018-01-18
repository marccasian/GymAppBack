<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * CursAvatar
 *
 * @ORM\Table(name="curs_avatar", indexes={@ORM\Index(name="id_curs_avatar_idx", columns={"idCurs"})})
 * @ORM\Entity
 */
class CursAvatar
{
    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=255, nullable=false)
     */
    private $file;

    /**
     * @var integer
     *
     * @ORM\Column(name="idcurs_avatar", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idcursAvatar;

    /**
     * @var \AppBundle\Entity\Curs
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Curs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="idCurs", referencedColumnName="CursId")
     * })
     */
    private $idcurs;

    /**
     * CursAvatar constructor.
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
    public function getIdcursAvatar(): int
    {
        return $this->idcursAvatar;
    }

    /**
     * @param int $idcursAvatar
     */
    public function setIdcursAvatar(int $idcursAvatar)
    {
        $this->idcursAvatar = $idcursAvatar;
    }

    /**
     * @return Curs
     */
    public function getIdcurs(): Curs
    {
        return $this->idcurs;
    }

    /**
     * @param Curs $idcurs
     */
    public function setIdcurs(Curs $idcurs)
    {
        $this->idcurs = $idcurs;
    }




}

