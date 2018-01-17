<?php
/**
 * @author Lucaciu Mircea <lucaciumircea5@gmail.com>
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
     * @param $username
     * @return Response
     */
    public function getUserProfile($username)
    {
        $utils = new Functions();
        $repoUser = $this->getDoctrine()->getManager()->getRepository(User::class);
        /** @var  $user User*/
        $user = $repoUser->findOneBy(array(
            'username' => $username,
        ));

        if ($user) {
            $repoProfile = $this->getDoctrine()->getManager()->getRepository(Profile::class);
            $profile = $repoProfile->findOneBy(array(
                'username' => $user,
            ));

            if ($profile) {

                return $utils->createResponse(200, array(
                    'profileid'      => $profile->getProfileid(),
                    'username'       => $user->getUsername(),
                    'email'          => $user->getEmail(),
                    'fullname'       => $profile->getFullname(),
                    'sex'            => $profile->getSex(),
                    'age'            => $profile->getVarsta(),
                ));
            }

        } else {
            return $utils->createResponse(404, array(
                'errors' => "No user with this username in the db.",
            ));
        }
        return $utils->createResponse(404, array(
            'errors' => "Something went wrong.",
        ));
    }

    /**
     * @Route("/profile/editProfile", name = "edit_profile")
     * @Method({"POST"})
     * @param Request $request
     * @return Response
     */
    public function editProfile(Request $request)
    {
        $utils = new Functions();

        $username = $request->request->get('username');
        $fullname = $request->request->get('fullname');
        $sex = $request->request->get('sex');
        $varsta = $request->request->get('age');

        if (!filter_var($varsta, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(404, array(
                'errors' => "Age must be an integer.",
            ));
        }

        if (count($fullname) > 255) {
            return $utils->createResponse(404, array(
                'errors' => "Full name too long.",
            ));
        }
        $repository = $this->getDoctrine()->getRepository(User::class);

        $user = $repository->findOneBy(array(
            'username' => $username
        ));

        //if user exist
        if ($user) {

            $repoProfile = $this->getDoctrine()->getManager()->getRepository(Profile::class);
            $newProfile = $repoProfile->findOneBy(array(
                'username' => $user,
            ));

            $newProfile->setFullname($fullname);
            $newProfile->setSex($sex);
            $newProfile->setVarsta($varsta);

            $em = $this->getDoctrine()->getManager();
            try {
                $em->persist($newProfile);
                $em->flush();
                return $utils->createResponse(200, array(
                    'profileid'      => $newProfile->getProfileid(),
                    'username'        => $newProfile->getUsername()->getUserName(),
                    'fullname'        => $newProfile->getFullname(),
                    'sex'             => $newProfile->getSex(),
                    'age'             => $newProfile->getVarsta(),
                ));
            } catch (Exception $e) {
                error_log($e->getMessage());
                return $utils->createResponse(409, array(
                    'errors' => 'Something went wrong',
                ));
            } catch (UniqueConstraintViolationException  $e) {
                error_log($e->getMessage());
                return $utils->createResponse(409, array(
                    'errors' => 'Something went wrong',
                ));
            } catch (PDOException  $e) {
                error_log($e->getMessage());
                return $utils->createResponse(409, array(
                    'errors' => 'Something went wrong',
                ));
            }

        } else {
            return $utils->createResponse(404, array(
                'errors' => "Invalid username",
            ));

        }
    }


}