<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 12/11/2017
 * Time: 2:37 PM
 **
 * @author Grozescu Rares <grozescurares@yahoo.com>
 *
 */

namespace AppBundle\Controller;


use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use AppBundle\Entity\Rol;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Utils\Functions;
use AppBundle\Utils\AllMyConstants;
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

                if($user->getRolid()->getDescription() == AllMyConstants::NUME_ANTRENOR){

                    //403 forbidden, user-ul este deja antrenor

                    return $utils->createResponse(403, array('errors' => "This user is already a trainer"));

                }
                else {

                    //obtin repository Rol
                    $repoRol = $this->getDoctrine()->getRepository(Rol::class);
                    $normalUser = $repoRol->findOneBy(array(
                        'description' => AllMyConstants::NUME_ANTRENOR
                    ));

                    //setez rolul la antrenor
                    $user->setRolid($normalUser);

                    //salvez in baza de date
                    try {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();
                    }
                    catch (Exception $e){
                        return $utils->createResponse(409, array(
                           'errors' => $e->getMessage(),
                        ));
                    }
                    catch (UniqueConstraintViolationException  $e) {
                        return $utils->createResponse(409, array(
                            'errors' => $e->getMessage(),
                        ));
                    }
                    catch (PDOException  $e) {
                        return $utils->createResponse(409, array(
                            'errors' => $e->getMessage(),
                        ));
                    }

                    return $utils->createResponse(200, array(
                        'username' => $username,
                        'message' => "Successfully added new trainer",
                    ));
                }

            } else {
                //nu exista user-ul in baza de date

                return $utils->createResponse(404, array(
                   'errors' => "Username not found",
                ));
            }
        }
        else{
            //nu s-a trimis nimic prin POST
            return $utils->createResponse(206, array(
                'errors' => "Partial data.",
            ));
        }
    }

    /**
     * @Route("/trainer/deleteTrainer/{username}", name = "trainer_delete")
     * @Method({"GET"})
     *
     */
    public function deleteTrainer($username){

        $utils = new Functions();
        $flag = true;

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
                if($user->getRolid()->getDescription() == AllMyConstants::NUME_ANTRENOR) {

                    $repoRol = $this->getDoctrine()->getRepository(Rol::class);
                    $normalUser = $repoRol->findOneBy(array(
                        'description' => 'user'
                    ));

                    //setez rolul la user
                    $user->setRolid($normalUser);

                    //salvez in bd
                    try {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();
                    }
                    catch (Exception $e){
                        return $utils->createResponse(409, array(
                           'errors' => $e->getMessage(),
                        ));
                    }
                    catch (UniqueConstraintViolationException  $e) {
                        return $utils->createResponse(409, array(
                            'errors' => $e->getMessage(),
                        ));
                    }
                    catch (PDOException  $e) {
                        return $utils->createResponse(409, array(
                            'errors' => $e->getMessage(),
                        ));
                    }


                    return $utils->createResponse(200, array(
                        'username' => $username,
                        'message'  => $username . " is no longer a trainer",
                    ));

                }
                else{

                    //altfel, username-ul nu este antrenor, deci nu are de ce sa fie modificat

                    return $utils->createResponse(403, array(
                        'errors' => $username . " is not a trainer",
                    ));
                }

            } else {

                //nu exista username-ul in bd
                return $utils->createResponse(404, array(
                    'errors' => "Username not found",
                ));
            }
        }
        else{
            //nu s-a primit nimic prin post
            return $utils->createResponse(206, array(
                'errors' => "Partial data",
            ));
        }
    }


    /**
     * @Route("/trainer/getAllTrainers", name = "get_all_trainers")
     * @Method({"GET"})
     *
     */
    public function getAllTrainers(){

        $utils = new Functions();

        $repoRol = $this->getDoctrine()->getManager()->getRepository(Rol::class);
        $rol = $repoRol->findOneBy(array(
           'description' => AllMyConstants::NUME_ANTRENOR,
        ));

        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);

        $users = $repository->findBy(array(
           'rolid' =>  $rol,
        ));


        $result = array();
        if(count($users)){

            foreach ($users as $trainer){
                $result[] = array(
                    'username' => $trainer->getUsername(),
                    'email' => $trainer -> getEmail(),

                );
            }
            return $utils->createResponse(200, array(
                'trainers' => $result,
            ));
        }
        else{
            return $utils->createResponse(404, array(
                'errors' => "There are no trainers.",
            ));
        }

    }


    /**
     * @Route("/user/getAllUsers", name = "get_all_users")
     * @Method({"GET"})
     *
     */
    public function getAllUsers(){

        $utils = new Functions();

        $repoRol = $this->getDoctrine()->getManager()->getRepository(Rol::class);
        $rol = $repoRol->findOneBy(array(
            'description' => "user",
        ));

        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);

        $users = $repository->findBy(array(
            'rolid' =>  $rol,
        ));


        $result = array();
        if(count($users)){

            foreach ($users as $trainer){
                $result[] = array(
                    'username' => $trainer->getUsername(),
                    'email' => $trainer -> getEmail(),

                );
            }
            return $utils->createResponse(200, array(
                'users' => $result,
            ));
        }
        else{
            return $utils->createResponse(404, array(
                'errors' => "There are no normal users.",
            ));
        }

    }


    /**
     * @Route("/trainer/getTrainer/{username}", name = "trainer_get")
     * @Method({"GET"})
     *
     */
    public function getTrainer($username)
    {
        $utils = new Functions();

        $repoRol = $this->getDoctrine()->getManager()->getRepository(Rol::class);
        $rol = $repoRol->findOneBy(array(
            'description' => AllMyConstants::NUME_ANTRENOR,
        ));

        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);

        $users = $repository->findOneBy(array(
            'rolid'     =>  $rol,
            'username'  =>  $username
        ));


        $result = [];
        if(!is_null($users)){

        $result[] = [
            'username' => $users->getUsername(),
            'email' => $users -> getEmail(),

        ];

            return $utils->createResponse(200, array(
                'trainer' => $result,
            ));
        }
        else{
            return $utils->createResponse(404, array(
                'errors' => "There are no trainers.",
            ));
        }


    }
}