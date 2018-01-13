<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Feedback
 *
 * @ORM\Table(name="feedback", indexes={@ORM\Index(name="feedback_evaluator_fk_idx", columns={"EvaluatorId"}), @ORM\Index(name="feedback_evaluat_fk_idx", columns={"EvaluatId"})})
 * @ORM\Entity
 */
class Feedback
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


}

