<?php
/**
 * @author Casian Marc <marccasiannicolae@gmail.com>
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
     * @Route("/course/create_course", name = "create_abonament")
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
//        $endDate = DateTime::createFromFormat('Y-m-d', $endDate)->format('Y-m-d');

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
            return $utils->createResponse(403, array(
                'errors' => $e->getMessage(),
            ));
        } catch (UniqueConstraintViolationException  $e) {
            return $utils->createResponse(403, array(
                'errors' => $e->getMessage(),
            ));
        } catch (PDOException  $e) {
            return $utils->createResponse(403, array(
                'errors' => $e->getMessage(),
            ));
        }

        return $utils->createResponse(200, array(
            'success' => true,
            'data' => [
                'cursId' => $curs->getCursid(),
                'level' => $level,
                'place' => $places,
                'type' => $type,
                'startDate' => $startDate->format('Y-m-d'),
                'endDate' => $endDate->format('Y-m-d')
            ]
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
            $errors .= 'Start date nu poate fi null;';
        }

        if (is_null($level)) {
            $errors .= 'Levelul nu poate fi null;';
        }

        if (is_null($type)) {
            $errors .= 'Tipul nu poate fi null;';
        }

        if (is_null($endDate)) {
            $errors .= 'End date nu poate fi null;';
        }
        if (is_null($places)) {
            $errors .= 'Places nu poate fi null;';
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
        if (count($cursuri)) {
            /** @var  $item Curs */
            foreach ($cursuri as $item) {
                $result[] = [
                    'cursId' => $item->getCursid(),
                    'place' => $item->getPlaces(),
                    'level' => $item->getLevel(),
                    'type' => $item->getType(),
                    'startDate' => $item->getStartdate()->format('Y-m-d'),
                    'endDate' => $item->getEnddate()->format('Y-m-d')
                ];

            }
            return $utils->createResponse(200, array(
                'cursuri' => $result,
            ));
        } else {
            return $utils->createResponse(404, array(
                'errors' => "Nu exista cursuri",
            ));
        }
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
                'errors' => "Curs Id este null",
            ));
        }
        if (!filter_var($cursId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Id-ul cursului trebuie sa fie integer",
            ));
        }
        $repository = $this->getDoctrine()->getRepository(Curs::class);

        $curs = $repository->findOneBy(array(
            'cursid' => $cursId,
        ));


        if ($curs) {

            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($curs);
                $em->flush();
            } catch (Exception $e) {
                return $utils->createResponse(409, array(
                    'errors' => $e->getMessage(),
                ));
            } catch (UniqueConstraintViolationException  $e) {
                return $utils->createResponse(409, array(
                    'errors' => $e->getMessage(),
                ));
            } catch (PDOException  $e) {
                return $utils->createResponse(409, array(
                    'errors' => $e->getMessage(),
                ));
            }
            return $utils->createResponse(200, array(
                'succes' => true,
                'message' => "Cursul a fost sters",
            ));

        } else {
            //nu exista cursul in bd
            return $utils->createResponse(404, array(
                'errors' => "Course doesn't exist!",
            ));
        }
    }

    /**
     * @Route("/course/get_course/{cursId}", name = "get_course")
     * @Method({"GET"})
     *
     */
    public function getCurs($cursId)
    {
        $utils = new Functions();

        if (is_null($cursId)) {
            return $utils->createResponse(403, array(
                'errors' => "Curs Id can't be null",
            ));
        }
        if (!filter_var($cursId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Course id must be integer",
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

            //succes
            return $utils->createResponse(200, array(
                'succes' => true,
                'data' => [
                    'courseId' => $curs->getCursid(),
                    'level' => $level,
                    'places' => $places,
                    'type' => $type,
                    'startDate' => $startDate->format('Y-m-d'),
                    'endDate' => $endDate->format('Y-m-d')
                ]
            ));

        } else {
            return $utils->createResponse(404, array(
                'errors' => "There isn't any course with given id;",
            ));
        }


        return $utils->createRespone(403, array(
            'errors' => "An unexpected error occurred!;",
        ));

    }

}