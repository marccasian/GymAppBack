<?php

/**
 * Created by PhpStorm.
 * User: Mircea
 * Date: 1/13/2018
 * Time: 4:30 PM
 */

namespace AppBundle\Controller;
header("Access-Control-Allow-Origin: *");

use AppBundle\Entity\Curs;
use AppBundle\Entity\Profile;
use AppBundle\Entity\Schedule;
use AppBundle\Utils\AllMyConstants;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Utils\Functions;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
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

class ScheduleController extends Controller
{
    /**
     * @Route("/schedule/create_schedule", name = "create_schedule")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     *
     */
    public function createSchedule(Request $request)
    {
        $utils = new Functions();

        $courseId = $request->request->get('courseId');
        $trainerId = $request->request->get('trainerId');
        $weekDay = $request->request->get('weekDay');
        $startTime = $request->request->get('startTime');
        $endTime = $request->request->get('endTime');
        $periodStartDate = $request->request->get('periodStartDate');
        $periodEndDate = $request->request->get('periodEndDate');

        $errors = $this->checkIfNull($courseId, $trainerId, $weekDay, $startTime, $endTime, $periodStartDate, $periodEndDate);

        if ($errors) {
            return $utils->createResponse(403, array(
                'errors' => $errors,
            ));
        }
        if (!filter_var($weekDay, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Weekday must be integer",
            ));
        }

        //check if trainer Id is trainer


        $periodStartDate = new \DateTime(DateTime::createFromFormat('Y-m-d', $periodStartDate)->format('Y-m-d'));
        $periodEndDate = new \DateTime(DateTime::createFromFormat('Y-m-d', $periodEndDate)->format('Y-m-d'));

        $startTime = new \DateTime(DateTime::createFromFormat('H:i:s', $startTime)->format('H:i:s'));
        $endTime = new \DateTime(DateTime::createFromFormat('H:i:s', $endTime)->format('H:i:s'));

        if ($periodStartDate > $periodEndDate) {
            return $utils->createResponse(403, array(
                'errors' => "Start date must be before end date",
            ));
        }

        try {

            $repoCurs = $this->getDoctrine()->getManager()->getRepository(Curs::class);
            /** @var  $curs Curs*/
            $curs = $repoCurs->findOneBy(array(
                'cursid' => $courseId,
            ));

            if (!$curs) {
                return $utils->createResponse(403, array(
                    'errors' => "CourseId invalid",
                ));
            }
            $repoProfile = $this->getDoctrine()->getManager()->getRepository(Profile::class);
            /** @var  $profile Profile*/
            $profile = $repoProfile->findOneBy(array(
                'profileid' => $trainerId,
            ));
            if (!$profile) {
                return $utils->createResponse(403, array(
                    'errors' => "TrainerId invalid",
                ));
            }

            $manager = $this->getDoctrine()->getManager();
            $schedule = new Schedule();
            $schedule->setIdcurs($curs);
            $schedule->setWeekday($weekDay);
            $schedule->setStarttime($startTime);
            $schedule->setEndtime($endTime);
            $schedule->setPeriodenddate($periodEndDate);
            $schedule->setPeriodstartdate($periodStartDate);
            $schedule->setIdtrainer($profile);

            $manager->persist($schedule);
            $manager->flush();

        } catch (Exception $e) {
            error_log($e->getMessage());
            return $utils->createResponse(403, array(
                'errors' => "Something went wrong ...",
            ));
        } catch (PDOException  $e) {
            error_log($e->getMessage());
            return $utils->createResponse(403, array(
                'errors' => "Something went wrong ...",
            ));
        }

        return $utils->createResponse(200, array(
            'courseId' => $schedule->getIdcurs(),
            'weekDay' => $schedule->getWeekday(),
            'startTime' => $schedule->getStarttime(),
            'endTime' => $schedule->getEndtime(),
            'periodEndDate' => $schedule->getPeriodenddate(),
            'periodStartDate' => $schedule->getPeriodstartdate(),
            'trainerId' => $schedule->getIdtrainer(),
        ));


    }

    /**
     * Check if parameters from request are null
     * @param $courseId
     * @param $trainerId
     * @param $weekDay
     * @param $startTime
     * @param $endTime
     * @param $periodStartDate
     * @param $periodEndDate
     * @return string
     */
    private function checkIfNull($courseId, $trainerId, $weekDay, $startTime, $endTime, $periodStartDate, $periodEndDate)
    {
        $errors = '';

        if (is_null($courseId)) {
            $errors .= 'Course Id cannot be null;';
        }

        if (is_null($trainerId)) {
            $errors .= 'Trainer id cannot be null;';
        }
        if (is_null($weekDay)) {
            $errors .= 'Weekday cannot be null;';
        }
        if (is_null($startTime)) {
            $errors .= 'Start time cannot be null;';
        }
        if (is_null($endTime)) {
            $errors .= 'End time cannot be null;';
        }
        if (is_null($periodStartDate)) {
            $errors .= 'Period start date cannot be  null;';
        }
        if (is_null($periodEndDate)) {
            $errors .= 'Period end date cannot be null;';
        }

        return $errors;
    }

    /**
     * @Route("/schedule/getAllSchedule", name = "get_all_schedule")
     * @Method({"GET"})
     *
     */
    public function getAllSchedule()
    {
        $utils = new Functions();
        $repo = $this->getDoctrine()->getManager()->getRepository(Schedule::class);
        $schedules = $repo->findAll();
        $result = [];
        if (count($schedules)) {
            /** @var  $item Schedule */
            foreach ($schedules as $item) {
                $result[] = [
                    'id' => $item->getId(),
                    'courseId' => $item->getIdcurs()->getCursid(),
                    'weekDay' => $item->getWeekday(),
                    'startTime' => $item->getStarttime(),
                    'endTime' => $item->getEndtime(),
                    'periodEndDate' => $item->getPeriodenddate(),
                    'periodStartDate' => $item->getPeriodstartdate(),
                    'trainerId' => $item->getIdtrainer()->getUsername()->getUsername(),
                ];

            }
            return $utils->createResponse(200, $result);
        } else {
            return $utils->createResponse(404, array(
                'errors' => "No schedules in db.",
            ));
        }
    }

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
     * @Route("/schedule/getAllScheduleOfGymByAbonamentUser/{username}", name = "get_all_schedule_of_gym_by_abonament_user")
     * @Method({"GET"})
     * @param $username
     * @return Response
     */
    public function getAllScheduleOfGymByAbonamentUser($username)
    {
        $utils = new Functions();
        error_log(1);
        $userAbonament = $this->getUserAbonament($username);
        error_log($userAbonament);
        if ($userAbonament == -1){
            return $utils->createResponse(404, array(
                'errors' => "Given user doesn't have a subscription or user doesn't exists;"
            ));
        }
        error_log(2);
        $schedules = $this->getGymScheduleByAbonamentId($userAbonament);
//        error_log($schedules->len);
        error_log(21);
        foreach ($schedules as &$item) {
            error_log("sch");
            error_log($item["id"]);
            error_log($item["WeekDay"]);
            error_log(intval($item["WeekDay"]));
            $dayOftheWeek = AllMyConstants::WEEK_DAY[$item["WeekDay"]];
            error_log($dayOftheWeek);
            $starttime = date_create_from_format('Y-m-d H:i:s', $item["StartTime"])->format('H:i');
            $endtime = date_create_from_format('Y-m-d H:i:s', $item["EndTime"])->format('H:i');
            $item["interval"] = $dayOftheWeek." ".$starttime."-".$endtime;
            error_log($item["interval"]);
        }
        error_log(3);
        return $utils->createResponse(200, $schedules);
    }

    /**
     * @Route("/schedule/getMySchedule/{username}", name = "get_my_schedule")
     * @Method({"GET"})
     * @param $username
     * @return Response
     */
    public function getMySchedule($username)
    {
        $utils = new Functions();
        $userAbonament = $this->getProfileIdFromUsername($username);
        if (!$userAbonament){
            return $utils->createResponse(404,array(
                "errors" => "Didn't find a profile for user with given username"
            ));
        }
        $schedules = $this->getMyScheduleByProfileId($userAbonament);
        foreach ($schedules as &$item) {
            $dayOftheWeek = AllMyConstants::WEEK_DAY[$item["WeekDay"]];
            $starttime = date_create_from_format('Y-m-d H:i:s', $item["StartTime"])->format('H:i');
            $endtime = date_create_from_format('Y-m-d H:i:s', $item["EndTime"])->format('H:i');
            $item["interval"] = $dayOftheWeek." ".$starttime."-".$endtime;
        }
        return $utils->createResponse(200, $schedules);
    }


    /**
     * @Route("/schedule/get_schedule/{id}", name = "get_schedule")
     * @Method({"GET"})
     * @param $id
     * @return Response
     */
    public function getSchedule($id)
    {
        $utils = new Functions();

        if (is_null($id)) {
            return $utils->createResponse(403, array(
                'errors' => "Id cannot be null",
            ));
        }
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Id has to be integer",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(Schedule::class);

        $schedule = $repository->findOneBy(array(
            'id' => $id,
        ));

        /** @var $schedule Schedule */
        if ($schedule) {
            return $utils->createResponse(200, array(
                'id' => $schedule->getId(),
                'courseId' => $schedule->getIdcurs(),
                'weekDay' => $schedule->getWeekday(),
                'startTime' => $schedule->getStarttime(),
                'endTime' => $schedule->getEndtime(),
                'periodEndDate' => $schedule->getPeriodenddate(),
                'periodStartDate' => $schedule->getPeriodstartdate(),
                'trainerId' => $schedule->getIdtrainer(),
            ));
        } else {
            return $utils->createResponse(404, array(
                'errors' => "No schedules with given id",
            ));
        }
    }

    /**
     * @Route("/schedule/delete_schedule/{id}", name = "delete_schedule")
     * @Method({"GET"})
     * @param $id
     * @return Response
     * @internal param $scheduleId
     * @internal param Request $request
     */
    public function deleteSchedule($id)
    {
        $utils = new Functions();

        if (is_null($id)) {
            return $utils->createResponse(403, array(
                'errors' => "Id cannot be null",
            ));
        }
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Id has to be integer",
            ));
        }
        $repository = $this->getDoctrine()->getRepository(Schedule::class);

        $schedule = $repository->findOneBy(array(
            'id' => $id,
        ));


        if ($schedule) {
            $courseId = $schedule->getIdcurs();
            $weekDay = $schedule->getWeekday();
            $startTime = $schedule->getStarttime();
            $endTime = $schedule->getEndtime();
            $periodEndDate = $schedule->getPeriodenddate();
            $periodStartDate = $schedule->getPeriodstartdate();
            $trainerId = $schedule->getIdtrainer();

            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($schedule);
                $em->flush();
            } catch (Exception $e) {
                error_log($e->getMessage());
                return $utils->createResponse(409, array(
                    'errors' => "Something went wrong ...",
                ));
            } catch (PDOException  $e) {
                error_log($e->getMessage());
                return $utils->createResponse(409, array(
                    'errors' => "Something went wrong ...",
                ));
            }
            return $utils->createResponse(200, array(
                'courseId' => $courseId,
                'weekDay' => $weekDay,
                'startTime' => $startTime,
                'endTime' => $endTime,
                'periodEndDate' => $periodEndDate,
                'periodStartDate' => $periodStartDate,
                'trainerId' => $trainerId,
            ));

        } else {

            return $utils->createResponse(404, array(
                'errors' => "No schedule with given id.",
            ));
        }
    }

    /**
     * @Route("/schedule/update_schedule/{id}", name = "update_schedule")
     * @Method({"POST"})
     * @param $id
     * @param Request $request
     * @return Response
     */
    public function updateSchedule($id, Request $request)
    {
        $utils = new Functions();

        $courseId = $request->request->get('courseId');
        $trainerId = $request->request->get('trainerId');
        $weekDay = $request->request->get('weekDay');
        $startTime = $request->request->get('startTime');
        $endTime = $request->request->get('endTime');
        $periodStartDate = $request->request->get('periodStartDate');
        $periodEndDate = $request->request->get('periodEndDate');

        $bodyScheduleId = $request->request->get('id');

        if ($bodyScheduleId != $id) {
            return $utils->createResponse(403, array(
                'errors' => "Ids are not equal",
            ));
        }

        if (is_null($bodyScheduleId)) {
            return $utils->createResponse(403, array(
                'errors' => "Id is null",
            ));
        }
        if (!filter_var($bodyScheduleId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Id has to be integer",
            ));
        }

        if (is_null($id)) {
            return $utils->createResponse(403, array(
                'errors' => "Id is null",
            ));
        }
        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Id has to be integer",
            ));
        }

        $repository = $this->getDoctrine()->getManager()->getRepository(Schedule::class);

        /** @var $schedule Schedule */
        $schedule = $repository->findOneBy(array(
            'id' => $id,
        ));

        if ($schedule) {

            $errors = $this->checkIfNull($courseId, $trainerId, $weekDay, $startTime, $endTime, $periodStartDate, $periodEndDate);
            if ($errors) {
                return $utils->createResponse(404, array(
                    'errors' => $errors,
                ));
            }

            if (!filter_var($weekDay, FILTER_VALIDATE_INT)) {
                return $utils->createResponse(403, array(
                    'errors' => "Weekday must be integer",
                ));
            }

            //check if trainer Id is trainer

            $periodStartDate = new \DateTime(DateTime::createFromFormat('Y-m-d', $periodStartDate)->format('Y-m-d'));
            $periodEndDate = new \DateTime(DateTime::createFromFormat('Y-m-d', $periodEndDate)->format('Y-m-d'));

            $startTime = new \DateTime(DateTime::createFromFormat('H:i:s', $startTime)->format('H:i:s'));
            $endTime = new \DateTime(DateTime::createFromFormat('H:i:s', $endTime)->format('H:i:s'));
            if ($periodStartDate > $periodEndDate) {
                return $utils->createResponse(403, array(
                    'errors' => "Start date must be before end date",
                ));
            }

            try {
                $repoCurs = $this->getDoctrine()->getManager()->getRepository(Curs::class);
                $curs = $repoCurs->findOneBy(array(
                    'cursid' => $courseId,
                ));
                /** @var $curs Curs */
                if (!$curs) {
                    return $utils->createResponse(403, array(
                        'errors' => "CourseId invalid",
                    ));
                }
                $repoProfile = $this->getDoctrine()->getManager()->getRepository(Profile::class);
                $profile = $repoProfile->findOneBy(array(
                    'profileid' => $trainerId,
                ));
                /** @var $profile Profile */
                if (!$profile) {
                    return $utils->createResponse(403, array(
                        'errors' => "TrainerId invalid",
                    ));
                }

                $manager = $this->getDoctrine()->getManager();
                $schedule->setIdcurs($curs);
                $schedule->setWeekday($weekDay);
                $schedule->setStarttime($startTime);
                $schedule->setEndtime($endTime);
                $schedule->setPeriodenddate($periodEndDate);
                $schedule->setPeriodstartdate($periodStartDate);
                $schedule->setIdtrainer($profile);

                $manager->persist($schedule);
                $manager->flush();

            } catch (Exception $e) {
                error_log($e->getMessage());
                return $utils->createResponse(403, array(
                    'errors' => "Something went wrong ...",
                ));
            } catch (PDOException  $e) {
                error_log($e->getMessage());
                return $utils->createResponse(403, array(
                    'errors' => "Something went wrong ...",
                ));
            }
            return $utils->createResponse(200, array(
                'id'                 =>$schedule->getId(),
                'courseId'           => $schedule->getIdcurs(),
                'weekDay'            => $schedule->getWeekday(),
                'startTime'          => $schedule->getStarttime(),
                'endTime'            => $schedule->getEndtime(),
                'periodEndDate'      => $schedule->getPeriodenddate(),
                'periodStartDate'    => $schedule->getPeriodstartdate(),
                'trainerId'          => $schedule->getIdtrainer(),
            ));

        } else {
            return $utils->createResponse(404, array(
                'errors' => "No schedule with given id",
            ));
        }


    }

    private function getUserAbonament($username)
    {
        /** @var $profile Profile */
        $profileId = $this->getProfileIdFromUsername($username);
        if (!$profileId){
            return -1;
        }
        $sql = " SELECT IdAbonament FROM elephpants_new.user_abonament where Activ = 1 and IdUser = $profileId;";
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $abonamentId = $stmt->fetchAll();
        if (count($abonamentId) == 0){
            return -1;
        }
        return $abonamentId[0]["IdAbonament"];
    }

    private function getGymScheduleByAbonamentId($userAbonament)
    {

        $sql = " SELECT schedule.id, curs.Type, profile.username as trainer, WeekDay, StartTime, EndTime, PeriodStartDate, PeriodEndDate 
                 FROM curs_abonament 
                 JOIN schedule on curs_abonament.IdCurs = schedule.IdCurs 
                 JOIN curs on curs_abonament.IdCurs = curs.CursId 
                 JOIN profile ON profile.ProfileId = schedule.IdTrainer
                 WHERE IdAbonament = $userAbonament  order by schedule.WeekDay,schedule.starttime;";
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $scheduleEntries = $stmt->fetchAll();
        return $scheduleEntries;
    }


    private function getMyScheduleByProfileId($profileId)
    {

        $sql = " SELECT schedule.id, curs.Type, WeekDay, p.username as Trainer, StartTime, EndTime, PeriodStartDate, PeriodEndDate 
                FROM schedule 
                JOIN evidentainscrieri on evidentainscrieri.ScheduleId = schedule.Id 
                JOIN curs on curs.CursId = schedule.IdCurs
                JOIN profile ON profile.ProfileId = evidentainscrieri.ProfileId
                JOIN profile p ON p.ProfileId = schedule.IdTrainer 
                WHERE profile.ProfileId = $profileId
                order by schedule.WeekDay,schedule.starttime;";
        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $scheduleEntries = $stmt->fetchAll();
        return $scheduleEntries;
    }
}
