<?php

/**
 * @author Grozescu Rares
 */

namespace AppBundle\Controller;
header("Access-Control-Allow-Origin: *");

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
use AppBundle\Entity\Avatar;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Validator\Constraints\Image;

class AvatarController extends Controller
{

    /**
     * @Route("/avatar/uploadAvatar", name = "upload_avatar")
     * @Method({"POST"})
     * @param $request
     * @return Response
     */

    public function uploadAvatar(Request $request){



        $username = $request->request->get('username');
        $file = $request->files->get('fileName');
        $utils = new Functions();



        if(!$username or !$file){
            return $utils->createResponse(206, array(
               'errors' => 'Partial data.'
            ));
        }

        if($file->guessExtension() == "png" or $file->guessExtension() == "jpg" or $file->guessExtension() == "jpeg" ){

        }
        else{
            return $utils->createResponse(403, array(
                'errors' => 'The file that is uploaded must have png, jpg or jpeg format.',
            ));
        }


        //mergem mai departe

        $repoUser = $this->getDoctrine()->getManager()->getRepository(User::class);
        $user = $repoUser->findOneBy(array(
            'username' => $username
        ));

        if($user){

            $repoAvatar = $this->getDoctrine()->getManager()->getRepository(Avatar::class);
            $avatar = $repoAvatar->findOneBy(array(
               'username' => $user,
            ));

            $em = $this->getDoctrine()->getManager();
            if($avatar){
                $avatar->setFile($username.'Avatar.'.$file->guessExtension());
                try {
                    $em->persist($avatar);
                    $em->flush();



                    $file->move(
                        $this->getParameter('images_location'), $username.'Avatar.'.$file->guessExtension()
                    );
                    return $utils->createResponse(202, array(
                       'message' => 'Avatar updated.',
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
            }


            $newAvatar = new Avatar();
            $newAvatar->setUsername($user);
            $newAvatar->setFile($username.'Avatar.'.$file->guessExtension());


            try{
                $em->persist($newAvatar);
                $em->flush();
                $file->move(
                    $this->getParameter('images_location'), $username.'Avatar.'.$file->guessExtension()
                );
                return $utils->createResponse(200, array(
                   'message' => 'Avatar uploaded.'
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

        }
        else{
            return $utils->createResponse(404, array(
               'errors' => "There is no user with this username in the db.",
            ));
        }


    }

    /**
     * @Route("/avatar/getAvatar/{username}", name = "get_avatar")
     * @Method({"GET"})
     * @param $username
     * @return Response
     */

    public function getAvatar($username){

        $utils = new Functions();

        //mergem mai departe

        $repoUser = $this->getDoctrine()->getManager()->getRepository(User::class);
        $user = $repoUser->findOneBy(array(
            'username' => $username
        ));

        if($user) {

            $repoAvatar = $this->getDoctrine()->getManager()->getRepository(Avatar::class);
            $avatar = $repoAvatar->findOneBy(array(
                'username' => $user,
            ));

            if ($avatar) {

                $filePath = $this->getParameter('images_location').$avatar->getFile();


                $response = new Response();
                $disposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $avatar->getFile());
                $response->headers->set('Content-Disposition', $disposition);
                $response->headers->set('Content-Type', 'image/png');
                $response->headers->set('Content-Type', 'image/jpg');
                $response->headers->set('Content-Type', 'image/jpeg');
                $response->setStatusCode(200);
                $response->setContent(file_get_contents($filePath));

                return $response;

            }
            else{
                return $utils->createResponse(404, array(
                   'errors' => 'This user has no avatar yet.',
                ));
            }


        }
        else{
            return $utils->createResponse(404, array(
               'errors' => 'There is no user with this username in the db.',
            ));
        }
    }




}
