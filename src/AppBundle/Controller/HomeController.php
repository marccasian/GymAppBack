<?php

/**
 * @author Grozescu Rares <grozescurares@yahoo.com>
 *
 */

namespace AppBundle\Controller;

use AppBundle\Entity\ActivationCode;
use AppBundle\Entity\Profile;
use AppBundle\Entity\Rol;
use AppBundle\Utils\Functions;
use Doctrine\DBAL\Driver\PDOException;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
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
                return $utils->createResponse(200, array(
                    'username' => $username,
                    'role' => $user->getRolid()->getRolid(),
                ));
            }
            else
            {
                return $utils->createResponse(404, array(
                    'errors' => 'Incorrect username or password.'
                ));
            }
        }
        else
        {
            $errors = "";
            if(!$username)
                $errors .= "Please enter the username";
            if(!$password) {
                $errors .= ";";
                $errors .= "Please enter the password";
            }
            return $utils->createResponse(404, array(
                'errors' => $errors
            ));
        }
    }

    /**
     * @Route("/home/register", name = "home_register")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function registerAction(Request $request)
    {

        $utils = new Functions();
        $flag = true;
        $username = $request->request->get('username');
        $password = $request->request->get('password');
        $email = $request->request->get('email');
        $confirmPassword = $request->request->get('confirmPassword');

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
                /** @var  $normalUser Rol*/
                $normalUser = $repoRol->findOneBy(array(
                    'description' => 'user'
                ));
                $user->setRolid($normalUser);

                try {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($user);
                    $em->flush();

                    //update profile table
                    $profile = new Profile();
                    $profile->setFullname('-');
                    $profile->setSex('-');
                    $profile->setUsername($user);
                    $profile->setVarsta(18);

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($profile);
                    $em->flush();



                    $message = "Hello ".$username.",\nThank you for registering your GymApp account!\n\nYour access data:\nUsername: ".$username."\nEmail address: ".$email."\nPassword: ".$password."\n\nThank you,\nElePHPants Team";

                    $post = [
                        'email' => $email,
                        'message' => $message,
                    ];

                    $utils->sendEmail($post);

                    $request = Request::create('home_login', "POST", array(
                        'username' => $username,
                        'password' => $password,

                    ));
                    $request->headers->set('Content-Type', 'application/json');
                    return $this->logInAction($request);
                } catch (\Exception $e) {
                    error_log($e->getMessage());
                    $errors = "";
                    $repo = $this->getDoctrine()->getRepository(User::class);
                    if ($repo->findOneBy(array(
                            'username' => $username
                            ))
                        )
                    {
                        $errors .= 'Username is already used';
                    }

                    if ($repo->findOneBy(array(
                        'email' => $email
                    ))
                    ) {
                        $errors .= ';';
                        $errors .= 'Email is already used';
                    }
                    return $utils->createResponse(404, array(
                       'errors' => $errors,
                    ));
                }
            }
            else{
                return $utils->createResponse(409, array(
                    'errors' => "The password fields don't match.",
                ));

            }
        }
        else{
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
            if(!$confirmPassword) {
                if (strlen($errors) > 0)
                    $errors .= ';';
                $errors .= 'Please confirm password';
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

            return $utils->createResponse(404, array(
                'errors' => $errors,
            ));
        }
    }

    /**
     * @param $email
     * @return bool
     */
    function isValidEmail($email)
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }



    /**
     * @Route("/home/sendResetCode", name = "home_send_reset_code")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function sendResetCode(Request $request){

        $utils = new Functions();
        $email = $request->request->get('email');

        if($email){

            if(!$this->isValidEmail($email)){
                return $utils->createResponse(404, array(
                   'errors' => 'Please enter a valid email address.',
                ));
            }

            $repoUser = $this->getDoctrine()->getManager()->getRepository(User::class);

            $user = $repoUser->findOneBy(array(
                'email' => $email,
            ));

            if(!$user){
                return $utils->createResponse(404, array(
                   'message' => 'There is no user with this email.',
                ));
            }

            $code = $utils->generateRandomCode();
            $message = "Your reset code : ".$code;
            $post = [
              "email" => $email,
                "message" => $message,
            ];

            if ($utils->sendEmail($post))
            {

                $repoReset = $this->getDoctrine()->getManager()->getRepository(ActivationCode::class);

                $resetCode = $repoReset->findOneBy(array(
                   'email' => $email,
                ));

                $em = $this->getDoctrine()->getManager();
                if($resetCode){
                    $resetCode->setCode($code);
                    $resetCode->setUsed(0);
                    try{
                        $em->persist($resetCode);
                        $em->flush();
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                        return $utils->createResponse(409, array(
                            'errors' => "Something went wrong ...",
                        ));
                    } catch (PDOException  $e) {
                        error_log($e->getMessage());
                        return $utils->createResponse(409, array(
                            'errors' => "Something went wrong ...",
                        ));
                    }

                }
                else{
                    $rCode = new ActivationCode();
                    $rCode->setEmail($email);
                    $rCode->setCode($code);
                    $resetCode->setUsed(0);
                    try{
                        $em->persist($rCode);
                        $em->flush();
                    } catch (Exception $e) {
                        error_log($e->getMessage());
                        return $utils->createResponse(409, array(
                            'errors' => "Something went wrong ...",
                        ));
                    } catch (PDOException  $e) {
                        error_log($e->getMessage());
                        return $utils->createResponse(409, array(
                            'errors' => "Something went wrong ...",
                        ));
                    }

                }



                return $utils->createResponse(200, array(
                   'message' => 'A reset code was sent to your email address.',
                ));
            }
            else{
                return $utils->createResponse(404, array(
                   'errors' => 'Something went wrong.',
                ));
            }
        }
        else{
            return $utils->createResponse(206, array(
               'errors' => 'Partial data.',
            ));
        }

    }

    /**
     * @Route("/home/resetPassword", name = "home_reset_password")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function resetPassword(Request $request){

        $utils = new Functions();
        $email = $request->request->get('email');
        $code = $request->request->get('code');



        if($email and $code){



            if(!$this->isValidEmail($email)){
                return $utils->createResponse(404, array(
                    'errors' => 'Please enter a valid email address.',
                ));
            }



            $repoUser = $this->getDoctrine()->getManager()->getRepository(User::class);

            $user = $repoUser->findOneBy(array(
                'email' => $email,
            ));

            if($user){
                $repoReset = $this->getDoctrine()->getManager()->getRepository(ActivationCode::class);

                $reset = $repoReset->findOneBy(array(
                    'email' => $email,
                    'code' => $code,
                    'used' => 0,
                ));

                if($reset) {

                    $newPassword = $utils->generateRandomCode(7);
                    $message = "Hello " . $user->getUsername() . ",\nHere is your new password: " . $newPassword . "\n\nHave a nice day,\nElePHPants Team";
                    $post = [
                        "email" => $email,
                        "message" => $message,
                    ];

                    if ($utils->sendEmail($post)) {
                        $em = $this->getDoctrine()->getManager();
                        $user->setPassword($newPassword);
                        try{
                            $em->persist($user);
                            $em->flush();

                            $reset->setUsed(1);
                            $em->persist($reset);
                            $em->flush();

                            return $utils->createResponse(200, array(
                               'message' => 'An email was sent to the address with the new password.',
                            ));

                        } catch (Exception $e) {
                            error_log($e->getMessage());
                            return $utils->createResponse(409, array(
                                'errors' => "Something went wrong ...",
                            ));
                        } catch (PDOException  $e) {
                            error_log($e->getMessage());
                            return $utils->createResponse(409, array(
                                'errors' => "Something went wrong ...",
                            ));
                        }

                    } else
                    {
                        return $utils->createResponse(404, array(
                           'errors' => 'Something went wrong',
                        ));
                    }
                }
                else{
                    return $utils->createResponse(404, array(
                        'errors' => 'Invalid reset code',
                    ));
                }

            }
            else{
                return $utils->createResponse(404, array(
                   'message' => 'There is no user with this email.',
                ));
            }

        }
        else{
            return $utils->createResponse(206, array(
                'errors' => 'Partial data.',
            ));
        }

    }


}