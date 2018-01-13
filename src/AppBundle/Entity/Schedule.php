<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Schedule
 *
 * @ORM\Table(name="schedule", indexes={@ORM\Index(name="schedule_curs_fk_idx", columns={"IdCurs"}), @ORM\Index(name="schedule_trainer_fk_idx", columns={"IdTrainer"})})
 * @ORM\Entity
 */
class Schedule
{
    /**
     * @var \DateTime
     *
     * @ORM\Column(name="StartDate", type="datetime", nullable=true)
     */
    private $startdate;

    /**
     * @var integer
     *
     * @ORM\Column(name="WeekDay", type="integer", nullable=true)
     */
    private $weekday;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="StartTime", type="datetime", nullable=true)
     */
    private $starttime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="EndTime", type="datetime", nullable=true)
     */
    private $endtime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="PeriodStartDate", type="datetime", nullable=true)
     */
    private $periodstartdate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="PeriodEndDate", type="datetime", nullable=true)
     */
    private $periodenddate;

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
     *   @ORM\JoinColumn(name="IdTrainer", referencedColumnName="ProfileId")
     * })
     */
    private $idtrainer;


}

