<?php
/**
 * Created by PhpStorm.
 * User: cmarc
 * Date: 1/14/2018
 * Time: 5:32 PM
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Abonament;
use AppBundle\Entity\Curs;
use AppBundle\Entity\Profile;
use AppBundle\Entity\UserAbonament;
use AppBundle\Utils\AllMyConstants;
use AppBundle\Utils\Functions;
use DateTime;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\Rol;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use AppBundle\Repository\UserRepository;
use AppBundle\Entity\User;

class UserAbonamentController extends Controller
{

    /**
     * @Route("/subscription/buy_subscription", name = "buy_subscription")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function buySubscription(Request $request)
    {
        $utils = new Functions();
        $username =    $request->request->get('username');
        $abonamentId =  $request->request->get('abonamentId');

        if(is_null($username)){
            return $utils->createResponse(400, array(
                'errors' => "Missing username;",
            ));
        }
        if(is_null($abonamentId)){
            return $utils->createResponse(400, array(
                'errors' => "Missing subscription id;",
            ));
        }

        $repository = $this->getDoctrine()->getManager()->getRepository(Profile::class);
        /** @var $profile Profile */
        $profile = $repository->findOneBy(array(
            'username' => $username,
        ));

        if($profile){
            $repositoryAbonament = $this->getDoctrine()->getManager()->getRepository(Abonament::class);
            /** @var $abonament Abonament */
            $abonament = $repositoryAbonament->findOneBy(array(
                'abonamentid' => $abonamentId,
            ));

            if($abonament){
                $manager = $this->getDoctrine()->getManager();
                $userAbonament = new UserAbonament();
                $userAbonament->setIduser($profile);
                $userAbonament->setIdabonament($abonament);
                $userAbonament->setActiv(AllMyConstants::ACTIV_TRUE);
//                if ($platit == 0){
                $userAbonament->setPlatit(AllMyConstants::PLATIT_FALSE);
//                }
//                else
//                {
//                    $userAbonament->setPlatit(AllMyConstants::PLATIT_TRUE);
//                    $startDate = new DateTime();
//                    $endDate = new DateTime("+1 month");
//                    $userAbonament->setAbonamentstartdate($startDate);
//                    $userAbonament->setAbonamentenddate($endDate);
//                }
                $this->unsetAllUserSubscriptions($username);
                $manager->persist($userAbonament);
                $manager->flush();
//                if ($platit) {
//                    return $utils->createResponse(200, [
//                        'profilId' => $username,
//                        'abonamentId' => $abonamentId,
//                        'platit' => $platit,
//                        'activ' => $userAbonament->getActiv(),
//                        'abonamentStartDate' => $userAbonament->getAbonamentstartdate()->format('Y-m-d'),
//                        'abonamentEndDate' => $userAbonament->getAbonamentenddate()->format('Y-m-d'),
//                    ]);
//                }
//                else {
                return $utils->createResponse(200, [
                    'profilId' => $username,
                    'abonamentId' => $abonamentId,
                    'platit' => $userAbonament->getPlatit(),
                    'activ' => $userAbonament->getActiv(),
                ]);
//                }
            }
            else
            {
                return $utils->createResponse(404, [
                    'errors' => "No subscription existing with given ID;",
                ]);
            }
        }else{
            return $utils->createResponse(400, [
                'errors' => "No profile existing with given username;",
            ]);
        }
    }

    private function unsetAllUserSubscriptions($profileId)
    {
        $em = $this->getDoctrine()->getManager();
        $qb = $em->createQueryBuilder();
        $q = $qb->update(UserAbonament::class, 'u')
                ->set('u.activ', AllMyConstants::ACTIV_FALSE)
            ->where('u.iduser = '.$profileId)
            ->getQuery();
        $q->execute();
    }
}