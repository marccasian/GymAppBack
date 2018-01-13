<?php
/**
 * @author Casian Marc <marccasiannicolae@gmail.com>
 * Class FeedbackController
 * @package AppBundle\Controller
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Feedback;
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

class FeedbackController extends Controller
{

    /**
     * @Route("/feedback/create_feedback", name = "create_feedback")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */

    public function createFeedback(Request $request){
        $utils = new Functions();

        $evaluatorId = $request->request->get('evaluatorId');
        $evaluatId = $request->request->get('evaluatId');
        $text= $request->request->get('text');
        $rating = $request->request->get('rating');
        $errors = $this->checkIfNull($evaluatorId, $evaluatId, $text, $rating);
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

            /** @var  $evaluat Profile*/
            $evaluat = $repoProfile->findOneBy(array(
                'profileid' => $evaluatId,
            ));

            /** @var  $evaluator Profile*/
            $evaluator = $repoProfile->findOneBy(array(
                'profileid' => $evaluatorId,
            ));

            $manager = $this->getDoctrine()->getManager();
            $feedback = new Feedback();
            $feedback->setEvaluatid($evaluat);
            $feedback->setEvaluatorid($evaluator);
            $feedback->setText($text);
            $feedback->setRating($rating);
            $manager->persist($feedback);
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
                'evaluatedId' => $feedback->getEvaluatid()->getUsername()->getUsername(),
                'evaluatorId' => $feedback->getEvaluatorid()->getUsername()->getUsername(),
                'rating' => $feedback->getRating(),
                'text' => $feedback->getText()
            ]
        ));

    }

    /**
     * Check if parameters from request are null
     * @param $evaluatorId
     * @param $evaluatId
     * @param $text
     * @param $rating
     * @return string
     */
    private function checkIfNull($evaluatorId, $evaluatId, $text, $rating)
    {
        $errors = '';

        if (is_null($evaluatorId)) {
            $errors .= 'Missing evaluator user;';
        }

        if (is_null($evaluatId)) {
            $errors .= 'Missing evaluated user;';
        }

        if (is_null($text)) {
            $errors .= 'Missing feedback text;';
        }

        if (is_null($rating)) {
            $errors .= 'Missing rating;';
        }

        return $errors;
    }

    /**
     * @Route("/feedback/get_all", name = "get_all_feedback")
     * @Method({"GET"})
     * @return Response
     *
     */
    public function getAll()
    {
        $utils = new Functions();
        $repoCurs = $this->getDoctrine()->getManager()->getRepository(Feedback::class);
        $feedback_entries = $repoCurs->findAll();
        $result = [];
        if (count($feedback_entries)) {
            /** @var  $item Feedback */
            foreach ($feedback_entries as $item) {
                $result[] = [
                    'evaluatedId' => $item->getEvaluatid()->getUsername()->getUsername(),
                    'evaluatorId' => $item->getEvaluatorid()->getUsername()->getUsername(),
                    'rating' => $item->getRating(),
                    'text' => $item->getText()
                ];

            }
            return $utils->createRespone(200, $result);
        } else {
            return $utils->createRespone(404, array(
                'errors' => "There isn't any feedback!",
            ));
        }
    }

    /**
     * @Route("/feedback/get_all_by_ratings", name = "get_all_by_ratings")
     * @Method({"GET"})
     * @return Response
     *
     */
    public function getAllByRating()
    {
        $utils = new Functions();
        $repoCurs = $this->getDoctrine()->getManager()->getRepository(Feedback::class);
        $sql = " 
                    SELECT 
                        profile.username, AVG(feedback.rating) AS Rating
                    FROM
                        feedback
                            JOIN
                        profile ON profile.ProfileId = feedback.EvaluatId
                    GROUP BY EvaluatId
                ";

        $conn = $this->getDoctrine()->getConnection();

        $stmt = $conn->prepare($sql);
        $stmt->execute();

        $result = $stmt->fetchAll();
        return $utils->createRespone(200, $result);
    }

    /**
     * @Route("/feedback/delete_feedback/{feedbackId}", name = "delete_feedback")
     * @Method({"GET"})
     * @param $feedbackId
     * @return Response
     * @internal param Request $request
     */
    public function deleteFeedback($feedbackId)
    {
        $utils = new Functions();

        if (is_null($feedbackId)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing feedback id",
            ));
        }
        if (!filter_var($feedbackId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Feedback if must be integer",
            ));
        }
        $repository = $this->getDoctrine()->getRepository(Feedback::class);

        $feedback = $repository->findOneBy(array(
            'id' => $feedbackId,
        ));


        if ($feedback) {

            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($feedback);
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
                'message' => "Feedback successfully deleted!",
            ));

        } else {
            return $utils->createRespone(404, array(
                'errors' => "Feedback doesn't exist!",
            ));
        }
    }

    /**
     * @Route("/feedback/get_feedback_evaluator/{evaluator}", name = "get_feedback_evaluator")
     * @Method({"GET"})
     * @param $evaluator
     * @return Response
     */
    public function getFeedbackByEvaluator($evaluator)
    {
        $utils = new Functions();

        if (is_null($evaluator)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing feedback id",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(Feedback::class);

        $feedback_entries = $repository->findBy(array(
            'evaluatorid' => $evaluator,
        ));

        /** @var $feedback Feedback */
        $response_array = [];
        foreach ($feedback_entries as $feedback){
            $response_array[] = [
                    'id' => $feedback->getId(),
                    'evaluatId' => $feedback->getEvaluatid()->getUsername()->getUsername(),
                    'evaluatorId' => $feedback->getEvaluatorid()->getUsername()->getUsername(),
                    'text' => $feedback->getText(),
                    'rating' => $feedback->getRating()
                ];
        }
        return $utils->createRespone(200, $response_array);
    }

    /**
     * @Route("/feedback/get_feedback_evaluated/{evaluated}", name = "get_feedback_evaluated")
     * @Method({"GET"})
     * @param $evaluated
     * @return Response
     */
    public function getFeedbackByEvaluated($evaluated)
    {
        $utils = new Functions();

        if (is_null($evaluated)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing feedback id",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(Feedback::class);

        $feedback_entries = $repository->findBy(array(
            'evaluatid' => $evaluated,
        ));

        /** @var $feedback Feedback */
        $response_array = [];
        foreach ($feedback_entries as $feedback){
            $response_array[] = [
                'id' => $feedback->getId(),
                'evaluatId' => $feedback->getEvaluatid()->getUsername()->getUsername(),
                'evaluatorId' => $feedback->getEvaluatorid()->getUsername()->getUsername(),
                'text' => $feedback->getText(),
                'rating' => $feedback->getRating()
            ];
        }
        return $utils->createRespone(200, $response_array);
    }

    /**
     * @Route("/feedback/get_feedback/{feedbackId}", name = "get_feedback")
     * @Method({"GET"})
     * @param $feedbackId
     * @return Response
     */
    public function getFeedback($feedbackId)
    {
        $utils = new Functions();

        if (is_null($feedbackId)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing feedback id",
            ));
        }
        if (!filter_var($feedbackId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Feedback id must be integer",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(Feedback::class);

        $feedback = $repository->findOneBy(array(
            'id' => $feedbackId,
        ));

        /** @var $feedback Feedback */
        if ($feedback) {
            return $utils->createRespone(200, array(
                'id' => $feedback->getId(),
                'evaluatId' => $feedback->getEvaluatid()->getUsername()->getUsername(),
                'evaluatorId' => $feedback->getEvaluatorid()->getUsername()->getUsername(),
                'text' => $feedback->getText(),
                'rating' => $feedback->getRating()
            ));
        } else {
            return $utils->createRespone(404, array(
                'errors' => "Given id doesn't exists",
            ));
        }
    }


    /**
     * @Route("/feedback/update_feedback/{feedbackId}", name = "update_feedback")
     * @Method({"POST"})
     * @param $feedbackId
     * @param Request $request
     * @return Response
     */
    public function updateFeedback($feedbackId, Request $request)
    {
        $utils = new Functions();

        $bodyFeedbackId = $request->request->get('feedbackId');

        if ($bodyFeedbackId != $feedbackId) {
            return $utils->createRespone(403, array(
                'errors' => "Mismatch between url id and body id",
            ));
        }

        if (is_null($bodyFeedbackId)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing course id from body!;",
            ));
        }
        if (!filter_var($bodyFeedbackId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Feedback id from body must be integer;",
            ));
        }

        if (is_null($feedbackId)) {
            return $utils->createRespone(403, array(
                'errors' => "Missing feedback id;",
            ));
        }
        if (!filter_var($feedbackId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Feedback id must be integer;",
            ));
        }

        $repository = $this->getDoctrine()->getManager()->getRepository(Feedback::class);

        /** @var $feedback Feedback */
        $feedback = $repository->findOneBy(array(
            'id' => $feedbackId,
        ));

        if ($feedback) {
            $text = $request->request->get('text');
            $rating = $request->request->get('rating');
            $evaluatorId = $request->request->get('evaluatorId');
            $evaluatId = $request->request->get('evaluatedId');

            $errors = $this->checkIfNull($evaluatorId, $evaluatId, $text, $rating);

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
            try{
                /** @var  $evaluat Profile*/
                $evaluat = $repoProfile->findOneBy(array(
                    'profileid' => $evaluatId,
                ));

                /** @var  $evaluator Profile*/
                $evaluator = $repoProfile->findOneBy(array(
                    'profileid' => $evaluatorId,
                ));

                $feedback->setEvaluatorid($evaluator);
                $feedback->setEvaluatid($evaluat);
                $feedback->setText($text);
                $feedback->setRating($rating);

                $manager = $this->getDoctrine()->getManager();
                $manager->persist($feedback);
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
                    'feedbackId' => $feedback->getId(),
                    'evaluatorId' => $evaluatorId,
                    'evaluatedId' => $evaluatId,
                    'text' => $text,
                    'rating' => $rating
                ]
            ));

        } else {
            return $utils->createRespone(404, array(
                'errors' => "There isn't any feedback with given id;",
            ));
        }


        return $utils->createRespone(403, array(
            'errors' => "An unexpected error occurred!;",
        ));

    }


}