<?php
/**
 * @author Lucaciu Mircea <lucaciumircea5@gmail.com>
 * Class AbonamentController
 * @package AppBundle\Controller
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Abonament;
use AppBundle\Utils\Functions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\DBAL\Driver\PDOException;

class AbonamentController extends Controller
{
    /**
     * @Route("/abonament/create_abonament", name = "create_abonament")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     *
     */
    public function createAbonament(Request $request)
    {
        $utils = new Functions();

        $price = $request->request->get('price');
        $level = $request->request->get('level');
        $type = $request->request->get('type');
        $description = $request->request->get('description');

        $errors = $this->   checkIfNull($level, $price, $type, $description);

        if ($errors) {
            return $utils->createResponse(403, array(
                'errors' => $errors,
            ));
        }
        if (!filter_var($level, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Level must be integer",
            ));
        }

        if (!filter_var($price, FILTER_VALIDATE_FLOAT)) {
            return $utils->createResponse(403, array(
                'errors' => "Price must be float",
            ));
        }

        try {
            $manager = $this->getDoctrine()->getManager();
            $abonament = new Abonament();
            $abonament->setLevel($level);
            $abonament->setPrice($price);
            $abonament->setType($type);
            $abonament->setDescription($description);

            $manager->persist($abonament);
            $manager->flush();
        } catch (Exception $e) {
            return $utils->createResponse(500, array(
                'errors' => $e->getMessage(),
            ));
        } catch (PDOException  $e) {
            return $utils->createResponse(500, array(
                'errors' => $e->getMessage(),
            ));
        }

        return $utils->createResponse(200, array(
            'abonamentId' => $abonament->getAbonamentid(),
            'level' => $level,
            'price' => $price,
            'type' => $type,
            'description' => $description
        ));


    }

    /**
     * Check if parameters from request are null
     * @param $level
     * @param $price
     * @param $type
     * @param $description
     * @return string
     */
    private function checkIfNull($level, $price, $type, $description)
    {
        $errors = '';

        if (is_null($price)) {
            $errors .= 'Missing price;';
        }

        if (is_null($level)) {
            $errors .= 'Missing level';
        }

        if (is_null($type)) {
            $errors .= 'Missing type;';
        }

        if (is_null($description)) {
            $errors .= 'Missing description;';
        }

        return $errors;
    }



    /**
     * @Route("/abonament/get_all", name = "get_all_abonamente")
     * @Method({"GET"})
     * @return Response
     *
     */
    public function getAll()
    {
        $utils = new Functions();
        $repoAbonamente = $this->getDoctrine()->getManager()->getRepository(Abonament::class);
        $abonamente = $repoAbonamente->findAll();
        $results = [];
        /** @var  $item Abonament */
        foreach ($abonamente as $item) {
            $results[] = [
                'abonamentId' => $item->getAbonamentid(),
                'price' => $item->getPrice(),
                'level' => $item->getLevel(),
                'type' => $item->getType(),
                'description' => $item->getDescription()
            ];

        }
        return $utils->createResponse(200, $results);
    }

    /**
     * @Route("/abonament/delete_abonament/{abonamentId}", name = "delete_abonament")
     * @Method({"GET"})
     * @param $abonamentId
     * @return Response
     * @internal param Request $request
     */
    public function deleteAbonament($abonamentId)
    {
        $utils = new Functions();

        if (is_null($abonamentId)) {
            return $utils->createResponse(403, array(
                'errors' => "Missing subscription id",
            ));
        }
        if (!filter_var($abonamentId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Subscription id must be integer",
            ));
        }
        $repository = $this->getDoctrine()->getRepository(Abonament::class);
        /** @var $abonament Abonament*/
        $abonament = $repository->findOneBy(array(
            'abonamentid' => $abonamentId,
        ));


        if ($abonament) {
            $abonamentId = $abonament->getAbonamentid();
            $level = $abonament->getLevel();
            $price = $abonament->getPrice();
            $type = $abonament->getType();
            $description = $abonament->getDescription();
            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($abonament);
                $em->flush();
            } catch (Exception $e) {
                return $utils->createResponse(409, array(
                    'errors' => $e->getMessage(),
                ));
            } catch (PDOException  $e) {
                return $utils->createResponse(409, array(
                    'errors' => $e->getMessage(),
                ));
            }
            return $utils->createResponse(200, array(
                'abonamentId' => $abonamentId,
                'level' => $level,
                'price' => $price,
                'type' => $type,
                'description' => $description
            ));

        } else {
            return $utils->createResponse(404, array(
                'errors' => "Unable to delete subscription because there isn't any subscription with given id!",
            ));
        }
    }

    /**
     * @Route("/abonament/get_abonament/{abonamentId}", name = "get_abonament")
     * @Method({"GET"})
     * @param $abonamentId
     * @return Response
     */
    public function getAbonament($abonamentId)
    {
        $utils = new Functions();

        if (is_null($abonamentId)) {
            return $utils->createResponse(403, array(
                'errors' => "Missing subscription id",
            ));
        }
        if (!filter_var($abonamentId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Subscription id must be integer",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(Abonament::class);

        $abonament = $repository->findOneBy(array(
            'abonamentid' => $abonamentId,
        ));

        /** @var $abonament Abonament */
        if ($abonament) {
            return $utils->createResponse(200, array(
                'abonamentId' => $abonament->getAbonamentid(),
                'price' => $abonament->getPrice(),
                'type' => $abonament->getType(),
                'description' => $abonament->getDescription(),
                'level' => $abonament->getLevel(),
            ));
        } else {
            return $utils->createResponse(404, array(
                'errors' => "Unable to get subscription because there isn't any subscription with given id!",
            ));
        }
    }

    /**
     * @Route("/abonament/update_abonament/{abonamentId}", name = "update_abonament")
     * @Method({"POST"})
     * @param $abonamentId
     * @param Request $request
     * @return Response
     */
    public function updateAbonament($abonamentId,Request $request)
    {
        $utils = new Functions();

        $bodyAbonamentId = $request->request->get('abonamentId');

        if($bodyAbonamentId != $abonamentId){
            return $utils->createResponse(403, array(
                'errors' => "Mismatch between url id and body id",
            ));
        }

        if (is_null($bodyAbonamentId)) {
            return $utils->createResponse(403, array(
                'errors' => "Missing subscription id from body",
            ));
        }
        if (!filter_var($bodyAbonamentId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Subscription id from body must be integer",
            ));
        }

        if (is_null($abonamentId)) {
            return $utils->createResponse(403, array(
                'errors' => "Missing subscription id",
            ));
        }
        if (!filter_var($abonamentId, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Subscription id must be integer",
            ));
        }

        $repository = $this->getDoctrine()->getManager()->getRepository(Abonament::class);

        /** @var $abonament Abonament*/
        $abonament = $repository->findOneBy(array(
            'abonamentid' => $abonamentId,
        ));

        if($abonament){
            $price = $request->request->get('price');
            $level = $request->request->get('level');
            $type = $request->request->get('type');
            $description = $request->request->get('description');

            $errors = $this->checkIfNull($level,$price,$type,$description);

            if($errors){
                return $utils->createResponse(404, array(
                    'errors' => $errors,
                ));
            }

            if (!filter_var($level, FILTER_VALIDATE_INT)) {
                return $utils->createResponse(403, array(
                    'errors' => "Level must be integer",
                ));
            }

            if (!filter_var($price, FILTER_VALIDATE_FLOAT)) {
                return $utils->createResponse(403, array(
                    'errors' => "Price must be float",
                ));
            }

            $abonament->setLevel($level);
            $abonament->setPrice($price);
            $abonament->setType($type);
            $abonament->setDescription($description);

            $manager = $this->getDoctrine()->getManager();
            $manager->persist($abonament);
            $manager->flush();

            //succes
            return $utils->createResponse(200, array(
                'abonamentId' => $abonament->getAbonamentid(),
                'level' => $level,
                'price' => $price,
                'type' => $type,
                'description' => $description
            ));

        }
        else
        {
            return $utils->createResponse(404, array(
                'errors' => "Unable to update subscription because there isn't any subscription with given id!",
            ));
        }
    }
}