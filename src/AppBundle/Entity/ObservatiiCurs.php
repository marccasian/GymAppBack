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


}

