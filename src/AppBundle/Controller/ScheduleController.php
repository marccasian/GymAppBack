<?php
/**
 * Created by PhpStorm.
 * User: Mircea
 * Date: 1/13/2018
 * Time: 4:30 PM
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Curs;
use AppBundle\Entity\Profile;
use AppBundle\Entity\Schedule;
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
                    'courseId' => $item->getIdcurs(),
                    'weekDay' => $item->getWeekday(),
                    'startTime' => $item->getStarttime(),
                    'endTime' => $item->getEndtime(),
                    'periodEndDate' => $item->getPeriodenddate(),
                    'periodStartDate' => $item->getPeriodstartdate(),
                    'trainerId' => $item->getIdtrainer(),
                ];

            }
            return $utils->createResponse(200, $result);
        } else {
            return $utils->createResponse(404, array(
                'errors' => "No schedules in db.",
            ));
        }
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
}
