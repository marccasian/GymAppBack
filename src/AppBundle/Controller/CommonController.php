<?php
/**
 * Created by PhpStorm.
 * User: cmarc
 * Date: 1/19/2018
 * Time: 12:49 AM
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Feedback;
use AppBundle\Entity\Profile;
use AppBundle\Entity\User;
use AppBundle\Utils\Functions;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;


class CommonController extends Controller
{


    public function getProfileIdFromUsername($get)
    {
        return $this->getProfileFromUsername($get)->getProfileid();
    }

    public function getProfileFromUsername($get)
    {
        $repository = $this->getDoctrine()->getRepository(Profile::class);
        /** @var  $profile Profile*/
        $profile = $repository->findOneBy(array(
            'username' => $get
        ));
        return $profile;
    }
}