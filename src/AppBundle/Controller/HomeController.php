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

        $flag = true;
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $content_dict = json_decode($request->getContent());
        if (!$username){
            $key = "username";
            $username = $content_dict->$key;
        }
        if (!$password){
            $key = "password";
            $password = $content_dict->$key;
        }

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

                $r = new Response();
                $r->setStatusCode(200);

                $r->setContent(json_encode(array(
                    'username' => $username,
                    'fullname' => $user->getFullname()
                )));

                $r->headers->set('Content-Type', 'application/json');
                return $r;

            } else {

                #return new Response(Response::HTTP_NOT_FOUND); #status code 404
                $r = new Response();
                $r->setStatusCode(404);
                $r->setContent(json_encode(array(
                    'errors' => 'Incorrect username or password.'
                )));

                $r->headers->set('Content-Type', 'application/json');
                return $r;
            }
        }
        else{
            #return new Response(Response::HTTP_PARTIAL_CONTENT); #status code 206
            $r = new Response();
            $r->setStatusCode(206);
            $errors = "";
            if(!$username)
                $errors .= "Please enter the username";
            if(!$password) {
                $errors .= ";";
                $errors .= "Please enter the password";
            }
            $r->setContent(json_encode(array(
                'errors' => $errors
            )));

            $r->headers->set('Content-Type', 'application/json');
            return $r;
        }

    }

    /**
     * @Route("/home/register", name = "home_register")
     * @Method({"POST"})
     *
     */
    public function registerAction(Request $request){

        $flag = true;
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $email = $request->request->get('email');
        $fullname = $request->request->get('fullname');
        $content_dict = json_decode($request->getContent());
        if (!$username){
            $key = "username";
            $username = $content_dict->$key;
        }
        if (!$password){
            $key = "password";
            $password = $content_dict->$key;
        }
        if (!$email){
            $key = "email";
            $email = $content_dict->$key;
        }
        if (!$fullname){
            $key = "fullname";
            $fullname = $content_dict->$key;
        }

        if(!$username or !$password or !$email or !$fullname)
            $flag = false;
        if (strlen($password) < 6)
            $flag = false;
        if (!$this->isValidEmail($email))
            $flag = false;

        if($flag) {
            $em = $this->getDoctrine()->getManager();

            $user = new User();
            $user->setUsername($username);
            $user->setPassword($password);
            $user->setEmail($email);
            $user->setFullname($fullname);


            $repoRol = $this->getDoctrine()->getRepository(Rol::class);
            $normalUser = $repoRol->findOneBy(array(
               'description' => 'user'
            ));
            $user->setRolid($normalUser);


            $em->persist($user);
            try {
                $em->flush();

                #return new Response(Response::HTTP_OK); # status code 200

                $request = Request::create('home_login', "POST", array(
                   'username' => $username,
                    'password' =>$password
                ));

                $request->headers->set('Content-Type', 'application/json');

                return $this->logInAction($request);



            }
            catch (\Exception $e){

                #return new Response(Response::HTTP_IM_USED); #status code 226

                $r = new Response();
                $r->setStatusCode(226);

                $errors = "";
                $repo = $this->getDoctrine()->getRepository(User::class);
                if($repo->findOneBy(array(
                    'username' => $username
                )))
                    $errors .= 'Username is already used';
                if($repo->findOneBy(array(
                    'email' => $email
                ))) {
                    $errors .= ';';
                    $errors .= 'Email is already used';
                }

                $r->setContent(json_encode(array(
                    'errors' => $errors
                )));

                $r->headers->set('Content-Type', 'application/json');
                return $r;

            }
        }
        else{
            #return new Response(Response::HTTP_PARTIAL_CONTENT); #status code 206
            $r = new Response();
            $r->setStatusCode(206);
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
            if(!$fullname) {
                if (strlen($errors) > 0)
                    $errors .= ';';
                $errors .= 'Please enter the fullname';
            }
            $r->setContent(json_encode(array(
                'errors' => $errors
            )));

            $r->headers->set('Content-Type', 'application/json');
            return $r;
        }
    }

    function isValidEmail($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

}