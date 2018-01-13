<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feedback
 *
 * @ORM\Table(name="feedback", indexes={@ORM\Index(name="feedback_evaluator_fk_idx", columns={"EvaluatorId"}), @ORM\Index(name="feedback_evaluat_fk_idx", columns={"EvaluatId"})})
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
     * @var \AppBundle\Entity\Profile
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="EvaluatId", referencedColumnName="ProfileId")
     * })
     */
    private $evaluatid;

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
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return Profile
     */
    public function getEvaluatid()
    {
        return $this->evaluatid;
    }

    /**
     * @param Profile $evaluatid
     */
    public function setEvaluatid($evaluatid)
    {
        $this->evaluatid = $evaluatid;
    }

    /**
     * @return Profile
     */
    public function getEvaluatorid()
    {
        return $this->evaluatorid;
    }

    /**
     * @param Profile $evaluatorid
     */
    public function setEvaluatorid($evaluatorid)
    {
        $this->evaluatorid = $evaluatorid;
    }


}

