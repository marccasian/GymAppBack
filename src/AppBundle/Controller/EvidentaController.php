<?php

/**
 * Created by PhpStorm.
 * User: Mircea
 * Date: 1/14/2018
 * Time: 6:01 PM
 */

namespace AppBundle\Controller;
header("Access-Control-Allow-Origin: *");

use AppBundle\Entity\Evidentainscrieri;
use AppBundle\Entity\Profile;
use AppBundle\Entity\Schedule;
use AppBundle\Utils\AllMyConstants;
use AppBundle\Utils\Functions;
use PDOException;
use SensioLabs\Security\SecurityChecker;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
class EvidentaController extends Controller
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
     * @Route("/evidenta/enroll", name = "enroll")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function enroll(Request $request)
    {
        $utils = new Functions();

        $username = $request->request->get('username');
        $scheduleId = $request->request->get('scheduleId');

        if (!$username) {
            return $utils->createResponse(403, [
                'errors' => "Username missing;",
            ]);
        }

        if (!$scheduleId) {
            return $utils->createResponse(403, [
                'errors' => "Schedule id missing missing;",
            ]);
        }

        if (!filter_var($scheduleId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, [
                'errors' => "Schedule ID must be integer",
            ]);
        }

        $profileId = $this->getProfileIdFromUsername($username);
        if (!$profileId){
            return $utils->createResponse(403, array(
                'errors' => "Fail to find evaluator profile;",
            ));
        }

        $repo = $this->getDoctrine()->getManager()->getRepository(Schedule::class);
        $sch = $repo->findOneBy([
            'id' => $scheduleId,
        ]);
        if(!$sch){
            return $utils->createResponse(403, [
                'errors' => "Schedule ID incorect ",
            ]);
        }

        $repo = $this->getDoctrine()->getManager()->getRepository(Evidentainscrieri::class);
        $evi = $repo->findOneBy([
            'profileid' => $profileId,
            'scheduleid' => $scheduleId
        ]);
        if($evi){
            return $utils->createResponse(403, [
                'errors' => "Already enrolled to this course;",
            ]);
        }


        try {
            $manager = $this->getDoctrine()->getManager();
            $em = $this->getDoctrine()->getManager();
            /** @var $profile Profile*/
            $profile = $em->find('AppBundle\Entity\Profile', $profileId);
            /** @var $schedule Schedule*/
            $schedule = $em->find('AppBundle\Entity\Schedule', $scheduleId);

            $evidenta = new Evidentainscrieri();
            $evidenta->setProfileid($profile);
            $evidenta->setScheduleid($schedule);
            $manager->persist($evidenta);
            $manager->flush();
            return $utils->createResponse(200, [
                'profileid' => $profileId,
                'scheduleid'=> $scheduleId
            ]);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return $utils->createResponse(403, [
                'errors' => "Something went wrong ...",
            ]);
        } catch (PDOException  $e) {
            error_log($e->getMessage());
            return $utils->createResponse(403, [
                'errors' => "Something went wrong ...",
            ]);
        }

    }

    /**
     * @Route("/evidenta/retreat", name = "retreat")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function retreat(Request $request)
    {
        $utils = new Functions();

        $username = $request->request->get('username');
        $scheduleId = $request->request->get('scheduleId');


        if (!$username) {
            return $utils->createResponse(403, [
                'errors' => "Username missing;",
            ]);
        }

        if (!$scheduleId) {
            return $utils->createResponse(403, [
                'errors' => "Schedule id missing missing;",
            ]);
        }

        if (!filter_var($scheduleId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, [
                'errors' => "Schedule ID must be integer",
            ]);
        }

        $profile = $this->getProfileFromUsername($username);
        if (!$profile){
            return $utils->createResponse(403, array(
                'errors' => "Fail to find evaluator profile;",
            ));
        }

        $repo = $this->getDoctrine()->getManager()->getRepository(Schedule::class);
        $sch = $repo->findOneBy([
            'id' => $scheduleId,
        ]);
        if(!$sch){
            return $utils->createResponse(403, [
                'errors' => "Schedule ID incorect ",
            ]);
        }
        $rep = $this->getDoctrine()->getManager()->getRepository(Evidentainscrieri::class);
        $evident = $rep->findOneBy([
            'profileid' => $profile,
            'scheduleid' => $sch
        ]);
        try {
            $manager = $this->getDoctrine()->getManager();

            $manager->remove($evident);
            $manager->flush();
            return $utils->createResponse(200, [
                'profileid' => $profile->getProfileid(),
                'scheduleid'=> $scheduleId
            ]);

        } catch (Exception $e) {
            error_log($e->getMessage());
            return $utils->createResponse(403, [
                'errors' => "Something went wrong ...",
            ]);
        } catch (PDOException  $e) {
            error_log($e->getMessage());
            return $utils->createResponse(403, [
                'errors' => "Something went wrong ...",
            ]);
        }
    }

    /**
     * @Route("/course/get_schedules/{profileId}", name = "get_schedules")
     * @Method({"GET"})
     * @param profileId
     * @return Response
     */
    public function getSchedules($profileId)
    {
        $utils = new Functions();

        if (!$profileId) {
            return $utils->createResponse(403, array(
                'errors' => "Profile id incorrect;",
            ));
        }
        if (!filter_var($profileId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Profile id must be integer;",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(Profile::class);

        $profile = $repository->findOneBy(array(
            'profileid' => $profileId,
        ));

        /** @var $profileId Profile */
        if ($profile) {
            $repository = $this->getDoctrine()->getManager()->getRepository(Evidentainscrieri::class);
            $schedulesIds = $repository->findBy(array(
                'profileid' => $profileId,
            ));
            $res = [];
            foreach ($schedulesIds as $evidenta){
                /** @var $evidenta Evidentainscrieri */
                $res[] = [
                    'startTime' => $evidenta->getScheduleid()->getStarttime(),
                    'endTime' => $evidenta->getScheduleid()->getEndtime(),
                    'weekDay' => $evidenta->getScheduleid()->getWeekday(),
                    'periodStartDate' => $evidenta->getScheduleid()->getPeriodstartdate(),
                    'periodEndDate' => $evidenta->getScheduleid()->getPeriodenddate(),
                    'idCourse' => $evidenta->getScheduleid()->getIdcurs()->getCursid()
                ];
            }

            return $utils->createResponse(200, array(
                'schedules' => $res,
            ));
        } else {
            return $utils->createResponse(404, array(
                'errors' => "Given id doesn't exists",
            ));
        }
    }

    /**
     * @Route("/course/get_profiles/{scheduleId}", name = "get_schedlus")
     * @Method({"GET"})
     * @param profileId
     * @return Response
     */
    public function getProfiles($scheduleId)
    {
        $utils = new Functions();

        if (!$scheduleId) {
            return $utils->createResponse(403, array(
                'errors' => "SChedule id incorrect;",
            ));
        }
        if (!filter_var($scheduleId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Schedule id must be integer;",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(Schedule::class);

        $schedule = $repository->findOneBy(array(
            'id' => $scheduleId,
        ));

        /** @var $scheduleId Schedule */
        if ($schedule) {
            $repository = $this->getDoctrine()->getManager()->getRepository(Evidentainscrieri::class);
            $schedulesIds = $repository->findBy(array(
                'scheduleid' => $scheduleId,
            ));
            $res = [];
            foreach ($schedulesIds as $evidenta){
                /** @var $evidenta Evidentainscrieri */
                $res[] = [
                    'profileid' => $evidenta->getProfileid()->getProfileid(),
                    'sex' => $evidenta->getProfileid()->getSex(),
                    'fullname' => $evidenta->getProfileid()->getFullname(),
                    'varsta' => $evidenta->getProfileid()->getVarsta(),
                    'username' => $evidenta->getProfileid()->getUsername()->getUsername()
                ];
            }

            return $utils->createResponse(200, array(
                'profiles' => $res,
            ));
        } else {
            return $utils->createResponse(404, array(
                'errors' => "Given id doesn't exists",
            ));
        }
    }

}