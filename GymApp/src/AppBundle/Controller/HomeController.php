<?php

/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/30/2017
 * Time: 2:49 PM
 */

namespace AppBundle\Controller;

use Doctrine\DBAL\Driver\PDOException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use AppBundle\Repository\UserRepository;
use AppBundle\Entity\User;

class HomeController extends Controller
{
    /**
     * @Route("/home/login", name = "home_login")
     * @Method({"POST"})
     *
     */
    public function logInAction(SessionInterface $session){

        $flag = true;

        $request = Request::createFromGlobals();

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
                $session->set("user", $username);

                return new Response(Response::HTTP_OK); #status code 200

            } else {

                return new Response(Response::HTTP_NOT_FOUND); #status code 404
            }
        }
        else{
            return new Response(Response::HTTP_PARTIAL_CONTENT); #status code 206

        }

    }

    /**
     * @Route("/home/register", name = "home_register")
     * @Method({"POST"})
     *
     */
    public function registerAction(){

        $flag = true;
        $request = Request::createFromGlobals();

        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $email = $request->request->get('email');

        if(!$username or !$password or !$email)
            $flag = false;

        if($flag) {
            $em = $this->getDoctrine()->getManager();

            $user = new User();
            $user->setUsername($username);
            $user->setPassword($password);
            $user->setEmail($email);

            $em->persist($user);
            try {
                $em->flush();
            }
            catch (\Exception $e){

                return new Response(Response::HTTP_IM_USED); #status code 226

            }

            return new Response(Response::HTTP_OK); # status code 200
        }
        else{
            return new Response(Response::HTTP_PARTIAL_CONTENT); #status code 206
        }
    }

}