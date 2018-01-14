<?php
/**
 * @author Casian Marc      <marccasiannicolae@gmail.com>
 * @authro Lucaciu Mircea   <lucaciumircea5@gmail.com>
 * Class CursController
 * @package AppBundle\Controller
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Abonament;
use AppBundle\Entity\Curs;
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
        $errors = $this->checkIfNull($startDate, $endDate, $places, $level, $type);
        if ($errors){
            return $utils->createResponse(403, array(
                'errors' => $errors,
            ));
        }
        if (!filter_var($level, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Level must be integer",
            ));
        }
        if (!filter_var($places, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Places must be integer",
            ));
        }

        if ($places < 0){
            return $utils->createResponse(403, array(
                'errors' => "Places must be positive number",
            ));
        }
        $startDate = new \DateTime(DateTime::createFromFormat('Y-m-d', $startDate)->format('Y-m-d'));
        $endDate = new \DateTime(DateTime::createFromFormat('Y-m-d', $endDate)->format('Y-m-d'));
        if ($startDate > $endDate){
            return $utils->createResponse(403, array(
                'errors' => "Start date must be before end date",
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
    private function checkIfNull($startDate, $endDate, $places, $level, $type)
    {
        $errors = '';

        if (is_null($startDate)) {
            $errors .= 'Missing start date;';
        }

        if (is_null($level)) {
            $errors .= 'Missing level;';
        }

        if (is_null($type)) {
            $errors .= 'Missing type;';
        }

        if (is_null($endDate)) {
            $errors .= 'Missing end date;';
        }
        if (is_null($places)) {
            $errors .= 'Missing places;';
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
            $startDate = $request->request->get('startDate');
            $endDate = $request->request->get('endDate');

            $errors = $this->checkIfNull($startDate, $endDate, $places, $level, $type);

            if ($errors) {
                return $utils->createResponse(404, array(
                    'errors' => $errors,
                ));
            }

            if (!filter_var($level, FILTER_VALIDATE_INT)) {
                return $utils->createResponse(403, array(
                    'errors' => "Level must be integer;",
                ));
            }

            if (!filter_var($places, FILTER_VALIDATE_INT)) {
                return $utils->createResponse(403, array(
                    'errors' => "Price must be integer;",
                ));
            }
            $startDate = new \DateTime(DateTime::createFromFormat('Y-m-d', $startDate)->format('Y-m-d'));
            $endDate = new \DateTime(DateTime::createFromFormat('Y-m-d', $endDate)->format('Y-m-d'));
            if ($startDate > $endDate){
                return $utils->createResponse(403, array(
                    'errors' => "Start date must be before end date;",
                ));
            }
            $curs->setLevel($level);
            $curs->setPlaces($places);
            $curs->setType($type);
            $curs->setStartdate($startDate);
            $curs->setEnddate($endDate);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($curs);
            $manager->flush();

            return $utils->createResponse(200, array(
                'courseId' => $curs->getCursid(),
                'level' => $level,
                'places' => $places,
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
            foreach ($cursIds as $item => $idCurs)
            {
                /** @var $curs Curs*/
                $curs = $repositoryCurs->findOneBy(array(
                    'cursid' => $idCurs,
                ));
                $cursuri[] = [
                    'cursId'    => $idCurs,
                    'startDate' => $curs->getStartdate(),
                    'endDate'   => $curs->getEnddate(),
                    'places'    => $curs->getPlaces(),
                    'level'     => $curs->getLevel(),
                    'type'      => $curs->getType()
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

}