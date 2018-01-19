<?php
header("Access-Control-Allow-Origin: *");
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


use AppBundle\Entity\Profile;
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
use Symfony\Component\Validator\Constraints\All;

class TrainerController extends Controller
{

    /**
     * @Route("/trainer/create_trainer", name = "create_trainer")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function create_trainer(Request $request){
        $utils = new Functions();
        $username = $request->request->get('username');

        if($username) {
            $repository = $this->getDoctrine()->getRepository(User::class);
            /** @var  $user User*/
            $user = $repository->findOneBy(array(
                'username' => $username,
            ));

            if ($user) {
                if($user->getRolid()->getDescription() == AllMyConstants::NUME_ANTRENOR){
                    return $utils->createResponse(403, array('errors' => "This user is already a trainer"));
                }
                else
                {
                    $repoRol = $this->getDoctrine()->getRepository(Rol::class);
                    /** @var  $antrenorUserType Rol*/
                    $antrenorUserType = $repoRol->findOneBy(array(
                        'description' => AllMyConstants::NUME_ANTRENOR
                    ));
                    $user->setRolid($antrenorUserType);
                    try {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();
                    }
                    catch (Exception $e){
                        error_log($e->getMessage());
                        return $utils->createResponse(409, array(
                           'errors' => "Something went wrong ...",
                        ));
                    }
                    catch (PDOException  $e) {
                        error_log($e->getMessage());
                        return $utils->createResponse(409, array(
                            'errors' => "Something went wrong ...",
                        ));
                    }

                    return $utils->createResponse(200, array(
                        'username' => $username,
                        'rolid' => $user->getRolid()->getRolid(),
                    ));
                }

            } else {
                return $utils->createResponse(404, array(
                   'errors' => "Username not found",
                ));
            }
        }
        else{
            return $utils->createResponse(403, array(
                'errors' => "Partial data.",
            ));
        }
    }

    /**
     * @Route("/trainer/delete_trainer/{username}", name = "delete_trainer")
     * @Method({"GET"})
     * @param $username
     * @return Response
     */
    public function delete_trainer($username){

        $utils = new Functions();

        if($username) {
            $repository = $this->getDoctrine()->getRepository(User::class);
            /** @var  $user User*/
            $user = $repository->findOneBy(array(
                'username' => $username,
            ));

            if ($user) {
                //daca exista user si este antrenor, il fac la loc user
                if($user->getRolid()->getDescription() == AllMyConstants::NUME_ANTRENOR) {
                    $repoRol = $this->getDoctrine()->getRepository(Rol::class);
                    /** @var  $normalUser Rol*/
                    $normalUser = $repoRol->findOneBy(array(
                        'description' => AllMyConstants::NUME_USER
                    ));
                    $user->setRolid($normalUser);
                    try {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();
                    }
                    catch (Exception $e){
                        error_log($e->getMessage());
                        return $utils->createResponse(409, array(
                           'errors' => "Something went wrong ...",
                        ));
                    }
                    catch (PDOException  $e) {
                        error_log($e->getMessage());
                        return $utils->createResponse(409, array(
                            'errors' => "Something went wrong ...",
                        ));
                    }


                    return $utils->createResponse(200, array(
                        'username' => $username,
                        'rolid' => $user->getRolid()->getRolid()
                    ));

                }
                else{
                    //altfel, username-ul nu este antrenor, deci nu are de ce sa fie modificat
                    return $utils->createResponse(403, array(
                        'errors' => $username . " is not a trainer",
                    ));
                }

            } else {
                return $utils->createResponse(404, array(
                    'errors' => "Username not found",
                ));
            }
        }
        else{
            //nu s-a primit nimic prin post
            return $utils->createResponse(403, array(
                'errors' => "Partial data",
            ));
        }
    }


    /**
     * @Route("/trainer/get_all_trainers", name = "get_all_trainers")
     * @Method({"GET"})
     *
     */
    public function get_all_trainers(){

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
        /** @var  $trainer User*/
        foreach ($users as $trainer){
            $result[] = array(
                'username' => $trainer->getUsername(),
                'email' => $trainer -> getEmail(),
            );
        }
        return $utils->createResponse(200, $result);
    }


    /**
     * @Route("/user/get_all_users", name = "get_all_users")
     * @Method({"GET"})
     *
     */
    public function get_all_users(){

        $utils = new Functions();

        $repoRol = $this->getDoctrine()->getManager()->getRepository(Rol::class);
        $rol = $repoRol->findOneBy(array(
            'description' => AllMyConstants::NUME_USER,
        ));

        $repository = $this->getDoctrine()->getManager()->getRepository(User::class);

        $users = $repository->findBy(array(
            'rolid' =>  $rol,
        ));


        $result = array();
        /** @var  $user User*/
        foreach ($users as $user){
            $result[] = array(
                'username' => $user->getUsername(),
                'email' => $user -> getEmail(),
            );
        }
        return $utils->createResponse(200, $result);
    }


    /**
     * @Route("/trainer/get_trainer/{username}", name = "get_trainer")
     * @Method({"GET"})
     * @param $username
     * @return Response
     */
    public function get_trainer($username)
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
            return $utils->createResponse(200, $result);
        }
        else{
            return $utils->createResponse(404, array(
                'errors' => "There isn't any trainer with given id.",
            ));
        }


    }

    /**
     * @Route("/trainer/checkTrainer/{id}", name = "check_trainer")
     * @Method({"GET"})
     * @param $id
     * @return Response
     */

    public function checkTrainer($id){
        $utils = new Functions();

        if (!filter_var($id, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Id must be integer",
            ));
        }

        if($id < 0){
            return $utils->createResponse(403, array(
                'errors' => "Id must be positive",
            ));
        }

        $profile = $this->getDoctrine()->getRepository(Profile::class)->findOneBy(array(
           'profileid' => $id
        ));

        if($profile){
            return $utils->createResponse(200, array(
               'username' => $profile->getUsername()->getUsername()
            ));
        }
        else{
            return $utils->createResponse(404, array(
               'errors' => 'There is no user with this profile id.'
            ));
        }

    }
}