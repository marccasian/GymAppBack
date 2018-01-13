<?php

/**
 * @author Grozescu Rares <grozescurares@yahoo.com>
 *
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Rol;
use AppBundle\Utils\Functions;
use Doctrine\DBAL\Driver\PDOException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use AppBundle\Repository\UserRepository;
use AppBundle\Entity\User;

class HomeController extends Controller
{
    /**
     * @Route("/home/login", name = "home_login")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function logInAction(Request $request){

        $utils = new Functions();
        $flag = true;
        $username = $request->request->get('username');
        $password = $request->request->get('password');


        if(!$username or !$password)
            $flag = false;

        if($flag) {

            $repository = $this->getDoctrine()->getRepository(User::class);


            $user = $repository->findOneBy(array(
                'username' => $username,
                'password' => $password
            ));

            if ($user) {

                #return new Response(Response::HTTP_OK); #status code 200

                return $utils->createRespone(200, array(
                    'username' => $username,
                    'role' => $user->getRolid()->getRolid(),
                ));

            } else {

                #return new Response(Response::HTTP_NOT_FOUND); #status code 404
                return $utils->createRespone(404, array(
                    'errors' => 'Incorrect username or password.'
                ));
            }
        }
        else{
            #return new Response(Response::HTTP_PARTIAL_CONTENT); #status code 206
            $errors = "";
            if(!$username)
                $errors .= "Please enter the username";
            if(!$password) {
                $errors .= ";";
                $errors .= "Please enter the password";
            }
            return $utils->createRespone(206, array(
                'errors' => $errors
            ));
        }

    }

    /**
     * @Route("/home/register", name = "home_register")
     * @Method({"POST"})
     *
     */
    public function registerAction(Request $request){

        $utils = new Functions();
        $flag = true;
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $email = $request->request->get('email');
        $confirmPassword = $request->request->get('confirmPassword');
        error_log($username);
        error_log($password);
        error_log($email);
        error_log($confirmPassword);
        $errors = "";

        if(!$username or !$password or !$email or !$confirmPassword)
            $flag = false;
        if (strlen($password) < 6)
            $flag = false;
        if (!$this->isValidEmail($email))
            $flag = false;

        if($flag) {

            if($password === $confirmPassword) {


                $user = new User();
                $user->setUsername($username);
                $user->setPassword($password);
                $user->setEmail($email);


                $repoRol = $this->getDoctrine()->getRepository(Rol::class);
                $normalUser = $repoRol->findOneBy(array(
                    'description' => 'user'
                ));
                $user->setRolid($normalUser);

                try {

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();
                    error_log(0);
                    error_log($user->getUsername());
                    error_log($user->__toString());

                    error_log(1);

                    #return new Response(Response::HTTP_OK); # status code 200

                    $request = Request::create('home_login', "POST", array(
                        'username' => $username,
                        'password' => $password
                    ));
                    error_log(2);
                    $request->headers->set('Content-Type', 'application/json');
                    error_log(3);
                    return $this->logInAction($request);


                } catch (\Exception $e) {
                    error_log($e->getMessage());
                    #return new Response(Response::HTTP_IM_USED); #status code 226
                    $errors = "";
                    $repo = $this->getDoctrine()->getRepository(User::class);
                    if ($repo->findOneBy(array(
                        'username' => $username
                    ))
                    )
                        $errors .= 'Username is already used';
                    if ($repo->findOneBy(array(
                        'email' => $email
                    ))
                    ) {
                        $errors .= ';';
                        $errors .= 'Email is already used';
                    }
                    return $utils->createRespone(226, array(
                       'errors' => $errors,
                    ));

                }
            }
            else{

                return $utils->createRespone(409, array(
                    'errors' => "The password fields don't match.",
                ));

            }
        }
        else{
            #return new Response(Response::HTTP_PARTIAL_CONTENT); #status code 206

            $errors = "";
            if(!$username) {
                $errors .= 'Please enter the username';
            }
            if(!$password) {
                if (strlen($errors) > 0)
                    $errors .= ';';
                $errors .= 'Please enter the password';
            }
            elseif (strlen($password) < 6) {
                if (strlen($errors) > 0)
                    $errors .= ';';
                $errors .= 'Please use a longer password. Minimum of 6 characters';
            }
            if(!$email) {
                if (strlen($errors) > 0)
                    $errors .= ';';
                $errors .= 'Please enter the email';
            }
            elseif (!$this->isValidEmail($email)){
                if (strlen($errors) > 0)
                    $errors .= ';';
                $errors .= 'Invalid email format. Please enter a valid email';
            }

            return $utils->createRespone(206, array(
                'errors' => $errors,
            ));
        }
    }

    function isValidEmail($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

}