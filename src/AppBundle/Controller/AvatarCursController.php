<?php
/**
 * @author Grozescu Rares
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Curs;
use AppBundle\Entity\CursAvatar;
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

class AvatarCursController extends Controller
{

    /**
     * @Route("/avatarcurs/uploadAvatarCurs", name = "upload_avatar_curs")
     * @Method({"POST"})
     * @param $request
     * @return Response
     */

    public function uploadAvatarCurs(Request $request){



        $idCurs = $request->request->get('idCurs');
        $file = $request->files->get('fileName');
        $utils = new Functions();




        if(!$idCurs or !$file){
            return $utils->createResponse(206, array(
                'errors' => 'Partial data.'
            ));
        }

        if (!filter_var($idCurs, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Id must be integer",
            ));
        }

        if ($idCurs < 0) {
            return $utils->createResponse(403, array(
                'errors' => "Id has to be positive",
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

        $repoCurs = $this->getDoctrine()->getManager()->getRepository(Curs::class);
        $curs = $repoCurs->findOneBy(array(
            'cursid' => $idCurs
        ));

        if($curs){

            $repoAvatar = $this->getDoctrine()->getManager()->getRepository(CursAvatar::class);
            $avatar = $repoAvatar->findOneBy(array(
                'idcurs' => $curs,
            ));

            $em = $this->getDoctrine()->getManager();
            if($avatar){
                $avatar->setFile("curs".$idCurs.'Avatar.'.$file->guessExtension());
                try {
                    $em->persist($avatar);
                    $em->flush();



                    $file->move(
                        $this->getParameter('images_location'), "curs".$idCurs.'Avatar.'.$file->guessExtension()
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


            $newAvatar = new CursAvatar();
            $newAvatar->setIdcurs($curs);
            $newAvatar->setFile("curs".$idCurs.'Avatar.'.$file->guessExtension());


            try{
                $em->persist($newAvatar);
                $em->flush();
                $file->move(
                    $this->getParameter('images_location'), "curs".$idCurs.'Avatar.'.$file->guessExtension()
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
                'errors' => "There is no curs with this id in the db.",
            ));
        }


    }

    /**
     * @Route("/avatarcurs/getAvatarCurs/{idCurs}", name = "get_avatar_curs")
     * @Method({"GET"})
     * @param $idCurs
     * @return Response
     */

    public function getAvatarCurs($idCurs){

        $utils = new Functions();

        //mergem mai departe

        if (!filter_var($idCurs, FILTER_VALIDATE_INT)) {
            return $utils->createResponse(403, array(
                'errors' => "Id must be integer",
            ));
        }

        if ($idCurs < 0) {
            return $utils->createResponse(403, array(
                'errors' => "Id has to be positive",
            ));
        }

        $repoCurs = $this->getDoctrine()->getManager()->getRepository(Curs::class);
        $curs = $repoCurs->findOneBy(array(
            'cursid' => $idCurs
        ));

        if($curs) {


            $repoAvatarCurs = $this->getDoctrine()->getManager()->getRepository(CursAvatar::class);
            $avatar = $repoAvatarCurs->findOneBy(array(
                'idcurs' => $curs,
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
                    'errors' => 'This curs has no avatar yet.',
                ));
            }


        }
        else{
            return $utils->createResponse(404, array(
                'errors' => 'There is no curs with this id in the db.',
            ));
        }
    }




}
