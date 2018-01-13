<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ObservatiiCurs
 *
 * @ORM\Table(name="observatii_curs", indexes={@ORM\Index(name="observatii_curs_curs_fk_idx", columns={"IdCurs"}), @ORM\Index(name="observatii_curs_evaluator_fk_idx", columns={"EvaluatorId"})})
 * @ORM\Entity
 */
class ObservatiiCurs
{
    /**
     * @var string
     *
     * @ORM\Column(name="Text", type="string", length=1000, nullable=true)
     */
    private $text;

    /**
     * @var integer
     *
     * @ORM\Column(name="Rating", type="integer", nullable=true)
     */
    private $rating;

    /**
     * @var integer
     *
     * @ORM\Column(name="Id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \AppBundle\Entity\Curs
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Curs")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="IdCurs", referencedColumnName="CursId")
     * })
     */
    private $idcurs;

    /**
     * @var \AppBundle\Entity\Profile
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="EvaluatorId", referencedColumnName="ProfileId")
     * })
     */
    private $evaluatorid;

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText(string $text)
    {
        $this->text = $text;
    }

    /**
     * @return int
     */
    public function getRating(): int
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     */
    public function setRating(int $rating)
    {
        $this->rating = $rating;
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
    public function setId(int $id)
    {
        $this->id = $id;
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

    /**
     * @return Profile
     */
    public function getEvaluatorid(): Profile
    {
        return $this->evaluatorid;
    }

    /**
     * @param Profile $evaluatorid
     */
    public function setEvaluatorid(Profile $evaluatorid)
    {
        $this->evaluatorid = $evaluatorid;
    }


}

