<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12/11/2017
 * Time: 2:37 PM
 */

namespace AppBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rol;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Utils\Functions;

class TrainerController extends Controller
{

    /**
     * @Route("/trainer/createTrainer", name = "trainer_create")
     * @Method({"POST"})
     *
     */
    public function createTrainer(Request $request){

        $utils = new Functions();

        $flag = true;
        $username = $request->request->get('username');

        if(!$username)
            $flag = false;


        if($flag) {

            //obtine repository User
            $repository = $this->getDoctrine()->getRepository(User::class);

            //caut user-ul cu username-ul primit prin post
            $user = $repository->findOneBy(array(
                'username' => $username,
            ));

            if ($user) {

                if($user->getRolid()->getDescription() == "antrenor"){

                    //403 forbidden, user-ul este deja antrenor



                    return $utils->createRespone(403, array('errors' => "This user is already a trainer"));

                }
                else {

                    //obtin repository Rol
                    $repoRol = $this->getDoctrine()->getRepository(Rol::class);
                    $normalUser = $repoRol->findOneBy(array(
                        'description' => 'antrenor'
                    ));

                    //setez rolul la antrenor
                    $user->setRolid($normalUser);

                    //salvez in baza de date
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                    return $utils->createRespone(200, array(
                        'username' => $username,
                        'message' => "Successfully added new trainer",
                    ));
                }

            } else {
                //nu exista user-ul in baza de date

                return $utils->createRespone(404, array(
                   'errors' => "Username not found",
                ));
            }
        }
        else{
            //nu s-a trimis nimic prin POST
            return $utils->createRespone(206, array(
                'errors' => "Partial data.",
            ));
        }
    }

    /**
     * @Route("/trainer/deleteTrainer", name = "trainer_delete")
     * @Method({"POST"})
     *
     */
    public function deleteTrainer(Request $request){

        $utils = new Functions();
        $flag = true;
        $username = $request->request->get('username');

        if(!$username)
            $flag = false;


        if($flag) {

            //obtin repository User
            $repository = $this->getDoctrine()->getRepository(User::class);

            //caut user cu username-ul primit prin POST
            $user = $repository->findOneBy(array(
                'username' => $username,
            ));

            if ($user) {

                //daca exista user si este antrenor, il fac la loc user
                if($user->getRolid()->getDescription() == "antrenor") {


                    $repoRol = $this->getDoctrine()->getRepository(Rol::class);
                    $normalUser = $repoRol->findOneBy(array(
                        'description' => 'user'
                    ));

                    //setez rolul la user
                    $user->setRolid($normalUser);

                    //salvez in bd
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();


                    return $utils->createRespone(200, array(
                        'username' => $username,
                        'message'  => $username . " is no longer a trainer",
                    ));

                }
                else{

                    //altfel, username-ul nu este antrenor, deci nu are de ce sa fie modificat

                    return $utils->createRespone(403, array(
                        'errors' => $username . " is not a trainer",
                    ));
                }

            } else {

                //nu exista username-ul in bd
                return $utils->createRespone(404, array(
                    'errors' => "Username not found",
                ));
            }
        }
        else{
            //nu s-a primit nimic prin post
            return $utils->createRespone(206, array(
                'errors' => "Partial data",
            ));
        }
    }


}