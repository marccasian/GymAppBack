<?php

/**
 * Created by PhpStorm.
 * User: cmarc
 * Date: 1/14/2018
 * Time: 5:32 PM
 */

namespace AppBundle\Controller;
header("Access-Control-Allow-Origin: *");
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
    public function getProfileIdFromUsername($get)
    {
        $profile = $this->getProfileFromUsername($get);
        if ($profile){
            return $profile->getProfileid();
        }
        return null;
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

        if(!$username){
            return $utils->createResponse(400, array(
                'errors' => "Missing username;",
            ));
        }
        if(is_null($abonamentId)){
            return $utils->createResponse(400, array(
                'errors' => "Missing subscription id;",
            ));
        }

        $profile = $this->getProfileFromUsername($username);

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
                $this->unsetAllUserSubscriptions($profile->getProfileid());
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
                    'username' => $username,
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

    /**
     * @Route("/subscription/setPay", name = "subscription_set_pay")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function setPayForSubscription(Request $request)
    {
        $utils = new Functions();
        $username = $request->request->get('username');
        $abonamentId = $request->request->get('abonamentId');

        if(!$username){
            return $utils->createResponse(400, array(
                'errors' => "Missing username;",
            ));
        }
        if(!$abonamentId){
            return $utils->createResponse(400, array(
                'errors' => "Missing subscription id;",
            ));
        }
        if(!filter_var($abonamentId, FILTER_VALIDATE_INT))
        {

            return $utils->createResponse(404, array(
                'errors' => 'Abonament id must be integer!',
            ));
        }

        $profileId = $this->getProfileIdFromUsername($username);
        if (!$profileId){
            return $utils->createResponse(400, array(
                'errors' => "Profile not found for given user;",
            ));
        }
        $startDate = new DateTime();
        $endDate = new DateTime("+1 month");
        $startDateStr = $startDate->format("Y-m-d H:i:s");
        $endDateStr = $endDate->format("Y-m-d H:i:s");
        $sql = "UPDATE user_abonament 
                SET AbonamentStartDate='$startDateStr', AbonamentEndDate='$endDateStr', Platit=1 WHERE idabonament = $abonamentId 
                AND Platit = 0 AND iduser = $profileId AND activ = 1;";
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        if ($stmt->rowCount() == 0){
            return $utils->createResponse(400, array(
                'errors' => "Fail to mark subscription as paid! Possible causes: Subscriptions doesn't exist, subscription is already paid or inactive;",
            ));
        }
        return $utils->createResponse(200, array());
    }

    /**
     * @Route("/subscription/get_paid_users", name = "get_paid_users")
     * @Method({"GET"})
     * @return Response
     * @internal param Request $request
     */
    public function getPaidUsers()
    {
        $utils = new Functions();

        $repo = $this->getDoctrine()->getManager()->getRepository(UserAbonament::class);
        $all = $repo->findAll();

        $res = [];
        foreach ($all as $item){
            /** @var $item UserAbonament */
            if($item->getPlatit() == AllMyConstants::PLATIT_TRUE && $item->getActiv()==AllMyConstants::ACTIV_TRUE){
                $res[]=[
                    'username' => $item->getIduser()->getUsername()->getUsername(),
                    'type' => $item->getIdabonament()->getType()
                ];
            }
        }

        return $utils->createResponse(200, $res);
    }

    /**
     * @Route("/subscription/get_unpaid_users", name = "get_unpaid_users")
     * @Method({"GET"})
     * @return Response
     * @internal param Request $request
     */
    public function getUnPaidUsers()
    {
        $utils = new Functions();

        $repo = $this->getDoctrine()->getManager()->getRepository(UserAbonament::class);
        $all = $repo->findAll();

        $res = [];
        foreach ($all as $item){
            /** @var $item UserAbonament */
            if($item->getPlatit() == AllMyConstants::PLATIT_FALSE &&  $item->getActiv()==AllMyConstants::ACTIV_TRUE){
                $res[]=[
                    'username' => $item->getIduser()->getUsername()->getUsername(),
                    'type' => $item->getIdabonament()->getType()
                ];
            }
        }

        return $utils->createResponse(200, $res);
    }
}