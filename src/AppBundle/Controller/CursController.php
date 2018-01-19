<?php

/**
 * @author Casian Marc      <marccasiannicolae@gmail.com>
 * @authro Lucaciu Mircea   <lucaciumircea5@gmail.com>
 * Class CursController
 * @package AppBundle\Controller
 */

namespace AppBundle\Controller;
header("Access-Control-Allow-Origin: *");

use AppBundle\Entity\Abonament;
use AppBundle\Entity\Curs;
use AppBundle\Entity\Profile;
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

class CursController extends Controller
{
    /**
     * @Route("/course/create_course", name = "create_course")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function createCourse(Request $request){
        $utils = new Functions();

        $startDate = $request->request->get('startDate');
        $endDate = $request->request->get('endDate');
        $places= $request->request->get('places');
        $level = $request->request->get('level');
        $type = $request->request->get('type');
        $description = $request->request->get('description');
        $errors = $this->checkRequestData($startDate, $endDate, $places, $level, $type, $description);
        if ($errors){
            return $utils->createResponse(403, array(
                'errors' => $errors,
            ));
        }

        try {
            $manager = $this->getDoctrine()->getManager();
            $curs = new Curs();
            $curs->setLevel($level);
            $curs->setPlaces($places);
            $curs->setType($type);
            $curs->setStartdate($startDate);
            $curs->setEnddate($endDate);
            $curs->setDescription($description);

            $manager->persist($curs);
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
            'cursId' => $curs->getCursid(),
            'level' => $level,
            'places' => $places,
            'type' => $type,
            'description' => $description,
            'startDate' => $startDate->format('Y-m-d'),
            'endDate' => $endDate->format('Y-m-d')
        ));

    }

    /**
     * Check if parameters from request are null
     * @param $startDate
     * @param $endDate
     * @param $places
     * @param $level
     * @param $type
     * @return string
     */
    private function checkRequestData($startDate, $endDate, $places, $level, $type, $description)
    {
        $errors = '';

        if (is_null($startDate)) {
            $errors .= 'Missing start date;';
        }
        elseif ($startDate == '')
        {
            $errors .= 'Start Date must be not empty;';
        }
        else
        {
            try {
                $formattedStartDate = DateTime::createFromFormat('Y-m-d', $startDate);
                if (is_bool($formattedStartDate))
                {
                    throw new Exception();
                }
                $startDate = new DateTime($formattedStartDate->format('Y-m-d'));
            } catch (Exception $e) {
                $errors .= "Invalid format of Start Date, the format must be Y-m-d.;";
            }
        }

        if (is_null($endDate)) {
            $errors .= 'Missing end date;';
        }
        elseif ($endDate == '')
        {
            $errors .= 'End Date must be not empty;';
        }
        else
        {
            try {
                $formattedEndDate = DateTime::createFromFormat('Y-m-d', $endDate);
                if (is_bool($formattedEndDate))
                {
                    throw new Exception();
                }
                $endDate = new DateTime($formattedEndDate->format('Y-m-d'));
            } catch (Exception $e) {
                $errors .= "Invalid format of End Date, the format must be Y-m-d.;";
            }
        }

        if (is_null($level)) {
            $errors .= 'Missing level;';
        }
        elseif (!filter_var($level, FILTER_VALIDATE_INT)) {
            $errors .= 'Level must be integer;';
        }

        if (is_null($type)) {
            $errors .= 'Missing type;';
        }

        if (is_null($description)) {
            $errors .= 'Missing description;';
        }

        if (is_null($places)) {
            $errors .= 'Missing places;';
        }
        elseif (!filter_var($places, FILTER_VALIDATE_INT)) {
            $errors .= 'Places must be integer;';
        }
        elseif ($places < 0) {
            $errors .= 'Places must be positive number;';
        }

        if (!is_null($startDate) && !is_null($endDate) && $startDate >= $endDate){
            $errors .= 'Start date must be before end date;';
        }

        return $errors;
    }

    /**
     * @Route("/course/get_all", name = "get_all_courses")
     * @Method({"GET"})
     * @return Response
     *
     */
    public function getAll()
    {
        $utils = new Functions();
        $repoCurs = $this->getDoctrine()->getManager()->getRepository(Curs::class);
        $cursuri = $repoCurs->findAll();
        $result = [];
        /** @var  $item Curs */
        foreach ($cursuri as $item) {
            $result[] = [
                'cursId' => $item->getCursid(),
                'places' => $item->getPlaces(),
                'level' => $item->getLevel(),
                'type' => $item->getType(),
                'description' => $item->getDescription(),
                'startDate' => $item->getStartdate()->format('Y-m-d'),
                'endDate' => $item->getEnddate()->format('Y-m-d')
            ];

        }
        return $utils->createResponse(200, array(
            'cursuri' => $result,
        ));
    }

    /**
     * @Route("/course/delete_course/{cursId}", name = "delete_course")
     * @Method({"GET"})
     * @param $cursId
     * @return Response
     * @internal param Request $request
     */
    public function deleteCurs($cursId)
    {
        $utils = new Functions();

        if (is_null($cursId)) {
            return $utils->createResponse(403, array(
                'errors' => "Missing course id;",
            ));
        }
        if (!filter_var($cursId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Course id must be integer;",
            ));
        }
        $repository = $this->getDoctrine()->getRepository(Curs::class);
        /** @var  $curs Curs*/
        $curs = $repository->findOneBy(array(
            'cursid' => $cursId,
        ));


        if ($curs) {
            $cursId = $curs->getCursid();
            $level = $curs->getLevel();
            $places = $curs->getPlaces();
            $type = $curs->getType();
            $startDate = $curs->getStartdate()->format('Y-m-d');
            $endDate = $curs->getEnddate()->format('Y-m-d');
            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($curs);
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
                'cursId' => $cursId,
                'level' => $level,
                'places' => $places,
                'type' => $type,
                'startDate' => $startDate,
                'endDate' => $endDate
            ));

        } else {
            return $utils->createResponse(404, array(
                'errors' => "Course doesn't exist!",
            ));
        }
    }

    /**
     * @Route("/course/get_course/{cursId}", name = "get_course")
     * @Method({"GET"})
     * @param $cursId
     * @return Response
     */
    public function getCurs($cursId)
    {
        $utils = new Functions();

        if (is_null($cursId)) {
            return $utils->createResponse(403, array(
                'errors' => "Missing course id;",
            ));
        }
        if (!filter_var($cursId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Course id must be integer;",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(Curs::class);

        $curs = $repository->findOneBy(array(
            'cursid' => $cursId,
        ));

        /** @var $curs Curs */
        if ($curs) {
            return $utils->createResponse(200, array(
                'cursId' => $curs->getCursid(),
                'places' => $curs->getPlaces(),
                'type' => $curs->getType(),
                'description' => $curs->getDescription(),
                'startDate' => $curs->getStartdate()->format('Y-m-d'),
                'endDate' => $curs->getEnddate()->format('Y-m-d'),
                'level' => $curs->getLevel(),
            ));
        } else {
            return $utils->createResponse(404, array(
                'errors' => "Given id doesn't exists",
            ));
        }
    }

    /**
     * @Route("/course/update_course/{cursId}", name = "update_course")
     * @Method({"POST"})
     * @param $cursId
     * @param Request $request
     * @return Response
     */
    public function updateCurs($cursId, Request $request)
    {
        $utils = new Functions();

        $bodyCursId = $request->request->get('courseId');

        if ($bodyCursId != $cursId) {
            return $utils->createResponse(403, array(
                'errors' => "Mismatch between url id and body id",
            ));
        }

        if (is_null($bodyCursId)) {
            return $utils->createResponse(403, array(
                'errors' => "Missing course id from body!;",
            ));
        }
        if (!filter_var($bodyCursId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Course id from body must be integer;",
            ));
        }

        if (is_null($cursId)) {
            return $utils->createResponse(403, array(
                'errors' => "Missing course id;",
            ));
        }
        if (!filter_var($cursId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Course id must be integer;",
            ));
        }

        $repository = $this->getDoctrine()->getManager()->getRepository(Curs::class);

        /** @var $curs Curs */
        $curs = $repository->findOneBy(array(
            'cursid' => $cursId,
        ));

        if ($curs) {
            $places = $request->request->get('places');
            $level = $request->request->get('level');
            $type = $request->request->get('type');
            $description = $request->request->get('description');
            $startDate = $request->request->get('startDate');
            $endDate = $request->request->get('endDate');

            $errors = $this->checkRequestData($startDate, $endDate, $places, $level, $type, $description);

            if ($errors) {
                return $utils->createResponse(404, array(
                    'errors' => $errors,
                ));
            }

            $curs->setLevel($level);
            $curs->setPlaces($places);
            $curs->setType($type);
            $curs->setDescription($description);
            $curs->setStartdate($startDate);
            $curs->setEnddate($endDate);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($curs);
            $manager->flush();

            return $utils->createResponse(200, array(
                'courseId' => $curs->getCursid(),
                'level' => $level,
                'places' => $places,
                'description' => $description,
                'type' => $type,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d')
            ));

        } else {
            return $utils->createResponse(404, array(
                'errors' => "There isn't any course with given id;",
            ));
        }
    }


    /**
     * @Route("/course/assign_course", name = "assign_course")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function assignCourse(Request $request)
    {
        $utils = new Functions();
        $cursId =       $request->request->get('courseId');
        $abonamentId =  $request->request->get('abonamentId');

        if(is_null($cursId)){
            return $utils->createResponse(404, array(
                'errors' => "Course ID cannot be null;",
            ));
        }
        if(is_null($abonamentId)){
            return $utils->createResponse(404, array(
                'errors' => "Subscription ID cannot be null;",
            ));
        }

        $repository = $this->getDoctrine()->getManager()->getRepository(Curs::class);
        /** @var $curs Curs */
        $curs = $repository->findOneBy(array(
            'cursid' => $cursId,
        ));

        if($curs){
            $repositoryAbonament = $this->getDoctrine()->getManager()->getRepository(Abonament::class);
            /** @var $abonament Abonament */
            $abonament = $repositoryAbonament->findOneBy(array(
                'abonamentid' => $abonamentId,
            ));

            if($abonament){
                $em = $this->getDoctrine()->getManager();
                /** @var $curss Curs*/
                $curss = $em->find('AppBundle\Entity\Curs', $cursId);
                /** @var $abonamentt Abonament*/
                $abonamentt = $em->find('AppBundle\Entity\Abonament', $abonamentId);
                $curss->getIdabonament()->add($abonamentt);
                $abonamentt->getIdcurs()->add($curss);
                $em->flush();

                return $utils->createResponse(200, [
                    'courseId'      => $cursId,
                    'abonamentId'   => $abonamentId
                ]);

            }else{
                return $utils->createResponse(404, [
                    'errors' => "No subscription existing with given ID;",
                ]);
            }
        }else{
            return $utils->createResponse(404, [
                'errors' => "No course existing with given ID;",
            ]);
        }

    }

    /**
     * @Route("/course/delete_course_subscription", name = "delete_course_subscription")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function deleteCourseSubscription(Request $request)
    {
        $utils = new Functions();
        $cursId =       $request->request->get('courseId');
        $abonamentId =  $request->request->get('abonamentId');

        if(is_null($cursId)){
            return $utils->createResponse(404, array(
                'errors' => "Course ID cannot be null;",
            ));
        }
        if(is_null($abonamentId)){
            return $utils->createResponse(404, array(
                'errors' => "Subscription ID cannot be null;",
            ));
        }

        $repository = $this->getDoctrine()->getManager()->getRepository(Curs::class);
        /** @var $curs Curs */
        $curs = $repository->findOneBy(array(
            'cursid' => $cursId,
        ));

        if($curs){
            $repositoryAbonament = $this->getDoctrine()->getManager()->getRepository(Abonament::class);
            /** @var $abonament Abonament */
            $abonament = $repositoryAbonament->findOneBy(array(
                'abonamentid' => $abonamentId,
            ));

            if($abonament) {
                $em = $this->getDoctrine()->getManager();
                /** @var $curss Curs*/
                $curss = $em->find('AppBundle\Entity\Curs', $cursId);
                /** @var $abonamentt Abonament*/
                $abonamentt = $em->find('AppBundle\Entity\Abonament', $abonamentId);


                $curss->getIdabonament()->removeElement($abonament);
                $abonamentt->getIdcurs()->removeElement($curs);
                $em->flush();
                return $utils->createResponse(200, [
                    'courseId'      => $cursId,
                    'abonamentId'   => $abonamentId
                ]);
            }else{
                return $utils->createResponse(404, [
                    'errors' => "No subscription existing with given ID;",
                ]);
            }
        }else{
            return $utils->createResponse(404, [
                'errors' => "No course existing with given ID;",
            ]);
        }
    }

    /**
     * @Route("/course/get_all_course_subscription", name = "get_all_course_subscription")
     * @Method({"GET"})
     * @return Response
     */
    public function getAllCourseSubscription()
    {
        $utils = new Functions();
        try {
            $sql = " SELECT * FROM curs_abonament";

            $conn = $this->getDoctrine()->getConnection();

            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetchAll();
           
            return $utils->createResponse(200, $result);

        }catch(Exception $e){
            error_log($e->getMessage());
            return $utils->createResponse(404, [
                'errors' => "Something went wrong;",
            ]);
        }

    }

    /**
     * @Route("/course/get_course_subscription_by_subscription/{id}", name = "get_course_subscription_by_subscription")
     * @Method({"GET"})
     * @param $id
     * @return Response
     */
    public function getCourseSubscriptionBySubscription($id)
    {
        $utils = new Functions();

        if(is_null($id)){
            return $utils->createResponse(404, [
                'errors' => "Id is null;",
            ]);
        }

        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Id has to be integer",
            ));
        }

        $repositoryAbonament = $this->getDoctrine()->getManager()->getRepository(Abonament::class);
        /** @var $abonament Abonament */
        $abonament = $repositoryAbonament->findOneBy(array(
            'abonamentid' => $id,
        ));

        if($abonament){
            //$id is validated
            $sql = " SELECT IdCurs FROM curs_abonament WHERE IdAbonament = $id";

            $conn = $this->getDoctrine()->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $cursIds = $stmt->fetchAll();

            $repositoryCurs= $this->getDoctrine()->getManager()->getRepository(Curs::class);

            $cursuri = [];
            /**
             * @var  $item Curs
             * @var  $idCurs int
             */
            foreach ($cursIds as $item => $idCurs)
            {

                /** @var $curs Curs*/
                $curs = $repositoryCurs->findOneBy(array(
                    'cursid' => $idCurs,
                ));
                $trainers = $this->getTrainersForCourse($curs->getCursid());
                $cursuri[] = [
                    'cursId'    => $curs->getCursid(),  
                    'startDate' => $curs->getStartdate()->format('Y-m-d'),
                    'endDate'   => $curs->getEnddate()->format('Y-m-d'),
                    'places'    => $curs->getPlaces(),
                    'level'     => $curs->getLevel(),
                    'type'      => $curs->getType(),
                    'trainers'  => $trainers,
                    'description'      => $curs->getDescription()
                ];
            }
            return $utils->createResponse(200, $cursuri);


        }else{
            return $utils->createResponse(404, [
                'errors' => "No subscription with that id;",
            ]);
        }


    }

    /**
     * @Route("/course/get_course_subscription_by_course/{id}", name = "get_course_subscription_by_course")
     * @Method({"GET"})
     * @param $id
     * @return Response
     */
    public function getCourseSubscriptionByCourse($id)
    {
        $utils = new Functions();

        if(is_null($id)){
            return $utils->createResponse(404, [
                'errors' => "Id is null;",
            ]);
        }

        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Id has to be integer",
            ));
        }

        $repositoryCurs = $this->getDoctrine()->getManager()->getRepository(Curs::class);
        /** @var $curs Curs */
        $curs = $repositoryCurs->findOneBy(array(
            'cursid' => $id,
        ));

        if($curs){
            //$id is validated
            $sql = " SELECT IdAbonament FROM curs_abonament WHERE IdCurs = $id";

            $conn = $this->getDoctrine()->getConnection();
            $stmt = $conn->prepare($sql);
            $stmt->execute();

            $abonamenteIds = $stmt->fetchAll();

            $repositoryAbonament= $this->getDoctrine()->getManager()->getRepository(Abonament::class);

            $abonamente = [];
            foreach ($abonamenteIds as $item => $idAbonament)
            {
                /** @var $abonament Abonament*/
                $abonament = $repositoryAbonament->findOneBy(array(
                    'abonamentid' => $idAbonament,
                ));
                $abonamente[] = [
                    'cursId'        => $idAbonament,
                    'price'         => $abonament->getPrice(),
                    'level'         => $abonament->getLevel(),
                    'type'          => $abonament->getType(),
                    'description'   => $abonament->getDescription()
                ];
            }
            return $utils->createResponse(200, $abonamente);


        }else{
            return $utils->createResponse(404, [
                'errors' => "No subscription with that id;",
            ]);
        }


    }

    private function getTrainersForCourse($idCurs)
    {
        $sql = " SELECT DISTINCT IdTrainer FROM schedule WHERE IdCurs = $idCurs";

        $conn = $this->getDoctrine()->getConnection();
        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $trainerIds = $stmt->fetchAll();
        $trainers = [];
        foreach ($trainerIds as $item){
            $trainers[]= $this->getUsernameByProfileId($item["IdTrainer"]);
//            die(var_dump($item["IdTrainer"]));
        }
        return $trainers;
    }

    private function getUsernameByProfileId($idProfile)
    {
        $repository = $this->getDoctrine()->getRepository(Profile::class);
        /** @var  $profile Profile*/
        $profile = $repository->findOneBy(array(
            'profileid' => $idProfile
        ));
        return $profile->getUsername()->getUsername();
    }

}