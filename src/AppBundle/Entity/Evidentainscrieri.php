<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Evidentainscrieri
 *
 * @ORM\Table(name="evidentainscrieri", indexes={@ORM\Index(name="evidenta_schedule_idx", columns={"ScheduleId"}), @ORM\Index(name="evidenta_profile_idx", columns={"ProfileId"})})
 * @ORM\Entity
 */
class Evidentainscrieri
{
    /**
     * @var integer
     *
     * @ORM\Column(name="idevidentainscrieri", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $idevidentainscrieri;

    /**
     * @var \AppBundle\Entity\Profile
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Profile")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ProfileId", referencedColumnName="ProfileId")
     * })
     */
    private $profileid;

    /**
     * @var \AppBundle\Entity\Schedule
     *
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\Schedule")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="ScheduleId", referencedColumnName="Id")
     * })
     */
    private $scheduleid;


}

