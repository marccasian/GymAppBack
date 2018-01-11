<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 1/11/2018
 * Time: 4:55 PM
 */

namespace AppBundle\Controller;


use AppBundle\Utils\Functions;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use AppBundle\Entity\Rol;
use Doctrine\DBAL\Driver\PDOException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\User;
use AppBundle\Entity\Profile;

class ProfileController extends Controller
{

    /**
     * @Route("/profile/getProfile/{username}", name = "get_user_profile")
     * @Method({"GET"})
     *
     */
    public function getUserProfile($username){

        $utils = new Functions();

        $repoUser = $this->getDoctrine()->getManager()->getRepository(User::class);

        $user = $repoUser->findOneBy(array(
            'username' => $username,
        ));

        if($user){

            $repoProfile = $this->getDoctrine()->getManager()->getRepository(Profile::class);

            $profile = $repoProfile->findOneBy(array(
               'username' => $user,
            ));

            if($profile){

                return $utils->createRespone(200, array(
                   'username'  => $user->getUsername(),
                    'email' => $user -> getEmail(),
                    'fullname' => $profile->getFullname(),
                    'sex' => $profile->getSex(),
                    'age' => $profile->getVarsta(),
                ));
            }

            //nu are profil, dar are username si parola
            return $utils->createRespone(203, array(
                'username'  => $user->getUsername(),
                'email' => $user -> getEmail(),
            ));

        }
        else{
            return $utils->createRespone(404, array(
               'errors' => "No user with this username in the db.",
            ));
        }

    }

    /**
     * @Route("/profile/editProfile", name = "edit_profile")
     * @Method({"POST"})
     *
     */
    public function editProfile(Request $request)
    {

        $utils = new Functions();

        $username = $request->request->get('username');
        $fullname = $request->request->get('fullname');
        $sex = $request->request->get('sex');
        $varsta = $request->request->get('age');

        if(!filter_var($varsta, FILTER_VALIDATE_INT))
            return $utils->createRespone(404, array(
                'errors' => "Age must be an integer.",
            ));


        if ($username) {
            //daca am primit ceva prin post
            $repoUser = $this->getDoctrine()->getManager()->getRepository(User::class);

            $user = $repoUser->findOneBy(array(
                'username' => $username,
            ));

            if ($user) {
                //daca exista user-ul
                $repoProfile = $this->getDoctrine()->getManager()->getRepository(Profile::class);

                $newProfile = $repoProfile->findOneBy(array(
                    'username' => $user,
                ));

                $newProfile->setFullname($fullname);
                $newProfile->setSex($sex);
                $newProfile->setVarsta($varsta);

                $em = $this->getDoctrine()->getManager();
                try{
                    $em->persist($newProfile);
                    $em->flush();
                    return $utils->createRespone(200, array(
                        'message' => "Profile updated.",
                    ));
                }
                catch (Exception $e){
                    return $utils->createRespone(409, array(
                        'errors' => $e->getMessage(),
                    ));
                }
                catch (UniqueConstraintViolationException  $e) {
                    return $utils->createRespone(409, array(
                        'errors' => $e->getMessage(),
                    ));
                }
                catch (PDOException  $e) {
                    return $utils->createRespone(409, array(
                        'errors' => $e->getMessage(),
                    ));
                }



            } else {
                return $utils->createRespone(404, array(
                    'errors' => "No user with this username in the db.",
                ));
            }

        } else {
            return $utils->createRespone(206, array(
               'errors' => "Partial data.",
            ));

        }
    }


}