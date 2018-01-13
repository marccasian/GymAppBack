<?php
/**
 * @author Lucaciu Mircea <lucaciumircea5@gmail.com>
 * Class AbonamentController
 * @package AppBundle\Controller
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Abonament;
use AppBundle\Utils\Functions;
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

        $errors = $this->checkIfNull($level, $price, $type, $description);

        if ($errors) {
            return $utils->createRespone(403, array(
                'errors' => $errors,
            ));
        }
        if (!filter_var($level, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Levelul trebuie sa fie integer",
            ));
        }

        if (!filter_var($price, FILTER_VALIDATE_FLOAT)) {
            return $utils->createRespone(403, array(
                'errors' => "Pretul trebuie sa fie float",
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
            return $utils->createRespone(500, array(
                'errors' => $e->getMessage(),
            ));
        } catch (UniqueConstraintViolationException  $e) {
            return $utils->createRespone(500, array(
                'errors' => $e->getMessage(),
            ));
        } catch (PDOException  $e) {
            return $utils->createRespone(500, array(
                'errors' => $e->getMessage(),
            ));
        }

        return $utils->createRespone(200, array(
            'succes' => true,
            'data' => [
                'abonamentId' => $abonament->getAbonamentid(),
                'level' => $level,
                'price' => $price,
                'type' => $type,
                'description' => $description
            ]
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
            $errors .= 'Pretul nu poate fi null;';
        }

        if (is_null($level)) {
            $errors .= 'Levelul nu poate fi null;';
        }

        if (is_null($type)) {
            $errors .= 'Tipul nu poate fi null;';
        }

        if (is_null($description)) {
            $errors .= 'Descrierea nu poate fi null;';
        }

        return $errors;
    }


    //TODO : update by ID

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
        $result = [];
        if (count($abonamente)) {
            /** @var  $item Abonament */
            foreach ($abonamente as $item) {
                $result[] = [
                    'abonamentId' => $item->getAbonamentid(),
                    'price' => $item->getPrice(),
                    'level' => $item->getLevel(),
                    'type' => $item->getType(),
                    'description' => $item->getDescription()
                ];

            }
            return $utils->createRespone(200, array(
                'abonamente' => $result,
            ));
        } else {
            return $utils->createRespone(404, array(
                'errors' => "Nu exista abonamente",
            ));
        }
    }

    /**
     * @Route("/abonament/delete_abonament/{abonamentId}", name = "delete_abonament")
     * @Method({"POST"})
     * @param $abonamentId
     * @return Response
     * @internal param Request $request
     */
    public function deleteAbonament($abonamentId)
    {
        $utils = new Functions();

        if (is_null($abonamentId)) {
            return $utils->createRespone(403, array(
                'errors' => "Abonament Id este null",
            ));
        }
        if (!filter_var($abonamentId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Abonamentul trebuie sa fie integer",
            ));
        }
        $repository = $this->getDoctrine()->getRepository(Abonament::class);

        $abonament = $repository->findOneBy(array(
            'abonamentid' => $abonamentId,
        ));


        if ($abonament) {

            try {
                $em = $this->getDoctrine()->getManager();
                $em->remove($abonament);
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
                'message' => "Abonamentul a fost sters",
            ));

        } else {
            //nu exista abonamentul-ul in bd
            return $utils->createRespone(404, array(
                'errors' => "Nu exista abonament",
            ));
        }
    }

    /**
     * @Route("/abonament/get_abonament/{abonamentId}", name = "get_abonament")
     * @Method({"GET"})
     *
     */
    public function getAbonament($abonamentId)
    {
        $utils = new Functions();

        if (is_null($abonamentId)) {
            return $utils->createRespone(403, array(
                'errors' => "Abonament Id este null",
            ));
        }
        if (!filter_var($abonamentId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Abonamentul trebuie sa fie integer",
            ));
        }
        $repository = $this->getDoctrine()->getManager()->getRepository(Abonament::class);

        $abonament = $repository->findOneBy(array(
            'abonamentid' => $abonamentId,
        ));

        /** @var $abonament Abonament */
        if ($abonament) {
            return $utils->createRespone(200, array(
                'abonamentId' => $abonament->getAbonamentid(),
                'price' => $abonament->getPrice(),
                'type' => $abonament->getType(),
                'description' => $abonament->getDescription(),
                'level' => $abonament->getLevel(),
            ));
        } else {
            return $utils->createRespone(404, array(
                'errors' => "Nu exista abonament cu id-ul dat",
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
            return $utils->createRespone(403, array(
                'errors' => "Id-urile nu sunt identice",
            ));
        }

        if (is_null($bodyAbonamentId)) {
            return $utils->createRespone(403, array(
                'errors' => "Abonament Id este null",
            ));
        }
        if (!filter_var($bodyAbonamentId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Abonamentul trebuie sa fie integer",
            ));
        }

        if (is_null($abonamentId)) {
            return $utils->createRespone(403, array(
                'errors' => "Abonament Id este null",
            ));
        }
        if (!filter_var($abonamentId, FILTER_VALIDATE_INT)) {
            return $utils->createRespone(403, array(
                'errors' => "Abonamentul trebuie sa fie integer",
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
                return $utils->createRespone(404, array(
                    'errors' => $errors,
                ));
            }

            if (!filter_var($level, FILTER_VALIDATE_INT)) {
                return $utils->createRespone(403, array(
                    'errors' => "Levelul trebuie sa fie integer",
                ));
            }

            if (!filter_var($price, FILTER_VALIDATE_FLOAT)) {
                return $utils->createRespone(403, array(
                    'errors' => "Pretul trebuie sa fie float",
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
            return $utils->createRespone(200, array(
                'succes' => true,
                'data' => [
                    'abonamentId' => $abonament->getAbonamentid(),
                    'level' => $level,
                    'price' => $price,
                    'type' => $type,
                    'description' => $description
                ]
            ));

        }else {
            return $utils->createRespone(404, array(
                'errors' => "Nu exista abonament cu id-ul dat",
            ));
        }


        return $utils->createRespone(500, array(
            'errors' => "A intervenit o eroare",
        ));

    }



}