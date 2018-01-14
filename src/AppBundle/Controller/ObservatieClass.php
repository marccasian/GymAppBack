<?php
/**
 * @author Casian Marc <marccasiannicolae@gmail.com>
 * Class ObservatieController
 * @package AppBundle\Controller
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Curs;
use AppBundle\Entity\ObservatiiCurs;
use AppBundle\Entity\Profile;
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

class ObservatieClass extends Controller
{

    /**
     * @Route("/observation/create_observation", name = "create_observation")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */

    public function createObservatie(Request $request){
        $utils = new Functions();

        $evaluatorId = $request->request->get('evaluatorId');
        $idCurs = $request->request->get('idCurs');
        $text= $request->request->get('text');
        $rating = $request->request->get('rating');
        $errors = $this->checkIfNull($evaluatorId, $idCurs, $text, $rating);
        if ($errors){
            return $utils->createRespone(403, array(
                'errors' => $errors,
            ));
        }
        if (!filter_var($rating, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Rating must be integer",
            ));
        }

        if ($rating < 0){
            return $utils->createRespone(403, array(
                'errors' => "Rating must be a positive number",
            ));
        }
        try {
            $repoProfile = $this->getDoctrine()->getManager()->getRepository(Profile::class);
            $repoCurs = $this->getDoctrine()->getManager()->getRepository(Curs::class);

            /** @var  $curs Curs*/
            $curs = $repoCurs->findOneBy(array(
                'cursid' => $idCurs,
            ));

            /** @var  $evaluator Profile*/
            $evaluator = $repoProfile->findOneBy(array(
                'profileid' => $evaluatorId,
            ));

            $manager = $this->getDoctrine()->getManager();
            $observatie = new ObservatiiCurs();
            $observatie->setIdcurs($curs);
            $observatie->setEvaluatorid($evaluator);
            $observatie->setText($text);
            $observatie->setRating($rating);
            $manager->persist($observatie);
            $manager->flush();
        } catch (Exception $e) {
            return $utils->createRespone(403, array(
                'errors' => $e->getMessage(),
            ));
        } catch (UniqueConstraintViolationException  $e) {
            return $utils->createRespone(403, array(
                'errors' => $e->getMessage(),
            ));
        } catch (PDOException  $e) {
            return $utils->createRespone(500, array(
                'errors' => $e->getMessage(),
            ));
        }

        return $utils->createRespone(200, array(
            'success' => true,
            'data' => [
                'idCurs' => $observatie->getIdcurs()->getCursid(),
                'evaluatorId' => $observatie->getEvaluatorid()->getUsername()->getUsername(),
                'rating' => $observatie->getRating(),
                'text' => $observatie->getText()
            ]
        ));

    }

    /**
     * Check if parameters from request are null
     * @param $evaluatorId
     * @param $idCurs
     * @param $text
     * @param $rating
     * @return string
     */
    private function checkIfNull($evaluatorId, $idCurs, $text, $rating)
    {
        $errors = '';

        if (is_null($evaluatorId)) {
            $errors .= 'Missing evaluator user;';
        }

        if (is_null($idCurs)) {
            $errors .= 'Missing curs id;';
        }

        if (is_null($text)) {
            $errors .= 'Missing observation text;';
        }

        if (is_null($rating)) {
            $errors .= 'Missing rating;';
        }

        return $errors;
    }

    /**
     * @Route("/observation/get_all", name = "get_all_observation")
     * @Method({"GET"})
     * @return Response
     *
     */
    public function getAll()
    {
        $utils = new Functions();
        $repoCurs = $this->getDoctrine()->getManager()->getRepository(ObservatiiCurs::class);
        $observations = $repoCurs->findAll();
        $result = [];
        /** @var  $item ObservatiiCurs */
        foreach ($observations as $item) {
            $result[] = [
                'idCurs' => $item->getIdcurs()->getCursid(),
                'evaluatorId' => $item->getEvaluatorid()->getUsername()->getUsername(),
                'rating' => $item->getRating(),
                'text' => $item->getText()
            ];

        }
        return $utils->createRespone(200, $result);
    }

    /**
     * @Route("/observations/get_all_by_ratings", name = "get_all_by_ratings")
     * @Method({"GET"})
     * @return Response
     *
     */
    public function getAllByRating()
    {
        $utils = new Functions();
        $repoCurs = $this->getDoctrine()->getManager()->getRepository(ObservatiiCurs::class);
        $sql = " 
                    SELECT 
                        curs.type, AVG(curs_observatii.rating) AS Rating
                    FROM
                        curs_observatii
                            JOIN
                        curs ON curs.CursId = curs_observatii.IdCurs
                    GROUP BY curs.type
                ";

        $conn = $this->getDoctrine()->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll();
        return $utils->createRespone(200, $result);
    }

    /**
     * @Route("/observation/delete_observation/{observationId}", name = "delete_observation")
     * @Method({"GET"})
     * @param $observationId
     * @return Response
     * @internal param Request $request
     */
    public function deleteFeedback($observationId)
    {
        $utils = new Functions();

        if (is_null($observationId)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing observation id",
            ));
        }
        if (!filter_var($observationId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Observation id must be integer",
            ));
        }
        $repository = $this->getDoctrine()->getRepository(ObservatiiCurs::class);

        $observation = $repository->findOneBy(array(
            'id' => $observationId,
        ));


        if ($observation) {

            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($observation);
                $em->flush();
            } catch (Exception $e) {
                return $utils->createRespone(409, array(
                    'errors' => $e->getMessage(),
                ));
            } catch (UniqueConstraintViolationException  $e) {
                return $utils->createRespone(409, array(
                    'errors' => $e->getMessage(),
                ));
            } catch (PDOException  $e) {
                return $utils->createRespone(409, array(
                    'errors' => $e->getMessage(),
                ));
            }
            return $utils->createRespone(200, array(
                'succes' => true,
                'message' => "Observation successfully deleted!",
            ));

        } else {
            return $utils->createRespone(404, array(
                'errors' => "Observation doesn't exist!",
            ));
        }
    }

    /**
     * @Route("/observation/get_observation_evaluator/{evaluator}", name = "get_observation_evaluator")
     * @Method({"GET"})
     * @param $evaluator
     * @return Response
     */
    public function getObservationByEvaluator($evaluator)
    {
        $utils = new Functions();

        if (is_null($evaluator)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing observation id",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(ObservatiiCurs::class);

        $observation_entries = $repository->findBy(array(
            'evaluatorid' => $evaluator,
        ));

        /** @var $observation ObservatiiCurs */
        $response_array = [];
        foreach ($observation_entries as $observation){
            $response_array[] = [
                'id' => $observation->getId(),
                'idCurs' => $observation->getIdcurs()->getCursid(),
                'evaluatorId' => $observation->getEvaluatorid()->getUsername()->getUsername(),
                'text' => $observation->getText(),
                'rating' => $observation->getRating()
            ];
        }
        return $utils->createRespone(200, $response_array);
    }

    /**
     * @Route("/observation/get_observation_course/{evaluated}", name = "get_observation_course")
     * @Method({"GET"})
     * @param $course
     * @return Response
     */
    public function getFeedbackByEvaluated($course)
    {
        $utils = new Functions();

        if (is_null($course)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing observation id",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(ObservatiiCurs::class);

        $observations = $repository->findBy(array(
            'cursid' => $course,
        ));

        /** @var $observation ObservatiiCurs */
        $response_array = [];
        foreach ($observations as $observation){
            $response_array[] = [
                'id' => $observation->getId(),
                'idCurs' => $observation->getIdcurs()->getCursid(),
                'evaluatorId' => $observation->getEvaluatorid()->getUsername()->getUsername(),
                'text' => $observation->getText(),
                'rating' => $observation->getRating()
            ];
        }
        return $utils->createRespone(200, $response_array);
    }

    /**
     * @Route("/observation/get_observation/{observationId}", name = "get_observation")
     * @Method({"GET"})
     * @param $observationId
     * @return Response
     */
    public function getFeedback($observationId)
    {
        $utils = new Functions();

        if (is_null($observationId)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing observation id",
            ));
        }
        if (!filter_var($observationId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Observation id must be integer",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(ObservatiiCurs::class);

        $observation = $repository->findOneBy(array(
            'id' => $observationId,
        ));

        /** @var $observation ObservatiiCurs */
        if ($observation) {
            return $utils->createRespone(200, array(
                'id' => $observation->getId(),
                'idCurs' => $observation->getIdcurs()->getCursid(),
                'evaluatorId' => $observation->getEvaluatorid()->getUsername()->getUsername(),
                'text' => $observation->getText(),
                'rating' => $observation->getRating()
            ));
        } else {
            return $utils->createRespone(404, array(
                'errors' => "Given id doesn't exists",
            ));
        }
    }


    /**
     * @Route("/observation/update_observation/{observationId}", name = "update_observation")
     * @Method({"POST"})
     * @param $observationId
     * @param Request $request
     * @return Response
     */
    public function updateFeedback($observationId, Request $request)
    {
        $utils = new Functions();

        $bodyObservationId = $request->request->get('observationId');

        if ($bodyObservationId != $observationId) {
            return $utils->createRespone(403, array(
                'errors' => "Mismatch between url id and body id",
            ));
        }

        if (is_null($bodyObservationId)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing course id from body!;",
            ));
        }
        if (!filter_var($bodyObservationId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Observation id from body must be integer;",
            ));
        }

        if (is_null($observationId)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing observation id;",
            ));
        }
        if (!filter_var($observationId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Observation id must be integer;",
            ));
        }

        $repository = $this->getDoctrine()->getManager()->getRepository(ObservatiiCurs::class);

        /** @var $observation ObservatiiCurs */
        $observation = $repository->findOneBy(array(
            'id' => $observationId,
        ));

        if ($observation) {
            $text = $request->request->get('text');
            $rating = $request->request->get('rating');
            $evaluatorId = $request->request->get('evaluatorId');
            $cursId = $request->request->get('idCurs');

            $errors = $this->checkIfNull($evaluatorId, $cursId, $text, $rating);

            if ($errors) {
                return $utils->createRespone(404, array(
                    'errors' => $errors,
                ));
            }

            if (!filter_var($rating, FILTER_VALIDATE_INT)) {
                return $utils->createRespone(403, array(
                    'errors' => "Rating must be integer;",
                ));
            }


            $repoProfile = $this->getDoctrine()->getManager()->getRepository(Profile::class);
            $repoCurs = $this->getDoctrine()->getManager()->getRepository(Curs::class);
            try{
                /** @var  $curs Curs*/
                $curs = $repoProfile->findOneBy(array(
                    'cursid' => $cursId,
                ));

                /** @var  $evaluator Profile*/
                $evaluator = $repoProfile->findOneBy(array(
                    'profileid' => $evaluatorId,
                ));

                $observation->setEvaluatorid($evaluator);
                $observation->setIdcurs($curs);
                $observation->setText($text);
                $observation->setRating($rating);

                $manager = $this->getDoctrine()->getManager();
                $manager->persist($observation);
                $manager->flush();

            } catch (Exception $e) {
                return $utils->createRespone(403, array(
                    'errors' => $e->getMessage(),
                ));
            } catch (UniqueConstraintViolationException  $e) {
                return $utils->createRespone(403, array(
                    'errors' => $e->getMessage(),
                ));
            } catch (PDOException  $e) {
                return $utils->createRespone(403, array(
                    'errors' => $e->getMessage(),
                ));
            }

            return $utils->createRespone(200, array(
                'succes' => true,
                'data' => [
                    'feedbackId' => $observation->getId(),
                    'evaluatorId' => $evaluatorId,
                    'idCurs' => $cursId,
                    'text' => $text,
                    'rating' => $rating
                ]
            ));

        } else {
            return $utils->createRespone(404, array(
                'errors' => "There isn't any observation with given id;",
            ));
        }


        return $utils->createRespone(403, array(
            'errors' => "An unexpected error occurred!;",
        ));

    }


}