<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 1/9/2018
 * Time: 1:23 PM
 */

namespace AppBundle\Controller;


use AppBundle\Entity\Abonament;
use AppBundle\Entity\Abonamenttype;
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
use Symfony\Component\Validator\Constraints\DateTime;


class MembershipController extends Controller
{

    /**
     * @Route("/membership/createOrExtend", name = "create_or_extend_membership")
     * @Method({"POST"})
     *
     */
    public function createOrExtendMembership(Request $request)
    {

        $utils = new Functions();

        $flag = true;
        $username = $request->request->get('username');
        $stardDate = $request->request->get('startDate');
        $price = $request->request->get('price');
        $level = $request->request->get('level');


        if (!$username || !$level)
            $flag = false;


        if ($flag) {

            //obtine repository User
            $repository = $this->getDoctrine()->getRepository(User::class);

            //caut user-ul cu username-ul primit prin post
            $user = $repository->findOneBy(array(
                'username' => $username,
            ));

            //obtin repository AbonamentType
            $repoAbonamentType = $this->getDoctrine()->getRepository(Abonamenttype::class);

            $nivel = $repoAbonamentType->findOneBy(array(
                'abonamenttypeid' => $level,
            ));

            if ($user and $nivel) {

                //obtin repository abonament
                $repoAbonament = $this->getDoctrine()->getRepository(Abonament::class);

                $abonament = $repoAbonament->findOneBy(array(
                    'username' => $username,
                    'level' => $level,
                    'active' => 1,
                ));



                if ($abonament) {


                    //inseamna ca exista deja un abonament de acest nivel alocat acestui user, si este activ il prelungim

                    $exEndDate = $abonament->getEnddate();

                    if ($exEndDate instanceof \DateTime) {
                        $exEndDate->modify('+1 months');

                        $newMemberhip = new Abonament();
                        $newMemberhip->setAbonamentid($abonament->getAbonamentid());
                        $newMemberhip->setUsername($user);
                        $newMemberhip->setPrice($abonament->getPrice());
                        $newMemberhip->setStartdate($abonament->getStartdate());
                        $newMemberhip->setLevel($level);
                        $newMemberhip->setAbonamenttypeid($nivel);
                        $newMemberhip->setEnddate($exEndDate);
                        $newMemberhip->setActive(1);

                        //salvez in baza de date
                        try {
                            $em = $this->getDoctrine()->getManager();

                            $em->remove($abonament);
                            $em->flush();
                            $em->persist($newMemberhip);
                            $em->flush();
                            return $utils->createRespone(202, array(
                                'message' => "The membership has been extended with one month.",
                            ));

                        } catch (Exception $e) {
                            return $utils->createRespone(409, array(
                                'errors' => $e,
                            ));
                        }

                    }

                } else {
                    //creeam un nou abonament

                    $newMemberhip = new Abonament();
                    $newMemberhip->setUsername($user);
                    $newMemberhip->setPrice($price);
                    $newMemberhip->setStartdate(new \DateTime($stardDate));
                    $newMemberhip->setLevel($level);
                    $newMemberhip->setAbonamenttypeid($nivel);
                    $newMemberhip->setActive(1);


                    $futureEndDate = new \DateTime($stardDate);
                    if ($futureEndDate instanceof \DateTime) {
                        $futureEndDate->modify('+1 months');
                        $newMemberhip->setEnddate($futureEndDate);
                    }

                    try {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($newMemberhip);
                        $em->flush();
                        return $utils->createRespone(200, array(
                            'message' => "The membership has been created.",
                        ));
                    } catch (Exception $e) {
                        return $utils->createRespone(409, array(
                            'errors' => $e,
                        ));
                    }
                }


            } else {
                //nu exista user-ul in baza de date

                if (!$user and !$nivel) {
                    return $utils->createRespone(404, array(
                        'errors' => "Username and membership doesn't exist.",
                    ));

                } elseif (!$nivel) {
                    return $utils->createRespone(404, array(
                        'errors' => "The memberhip with level " . $level . " doesn't exist.",
                    ));

                } else {
                    return $utils->createRespone(404, array(
                        'errors' => "Username not found",
                    ));
                }
            }
        } else {
            //nu s-a trimis nimic prin POST
            return $utils->createRespone(206, array(
                'errors' => "Partial data.",
            ));
        }
    }

    /**
     * @Route("/membership/getActiveMemberships", name = "getActiveMemberships")
     * @Method({"POST"})
     *
     */
    public function getActiveMemberships(Request $request)
    {

        $utils = new Functions();

        $flag = true;
        $username = $request->request->get('username');


        if (!$username) {
            $flag = false;
        }


        if ($flag) {

            //obtine repository User
            $repository = $this->getDoctrine()->getRepository(User::class);

            //caut user-ul cu username-ul primit prin post
            $user = $repository->findOneBy(array(
                'username' => $username,
            ));


            if ($user) {

                //obtin repository abonament
                $repoAbonament = $this->getDoctrine()->getRepository(Abonament::class);

                $abonamente = $repoAbonament->findBy(array(
                    'username' => $username,
                ));

                if (count($abonamente) > 0) {


                    $noTime = new \DateTime();
                    $result = array();

                    foreach ($abonamente as $abonament) {


                        if ($abonament->getEnddate() > $noTime) {


                            $result[] = array(
                                'startDate' => $abonament->getStartdate(),
                                'endDate' => $abonament->getEnddate(),
                                'level' => $abonament->getLevel(),
                                'price' => $abonament->getPrice(),
                            );
                        } else {
                            $abonament->setActive(0);
                            try {
                                $em = $this->getDoctrine()->getManager();
                                $em->persist($abonament);
                                $em->flush();
                            } catch (Exception $e) {
                                return $utils->createRespone(409, array(
                                    'errors' => $e,
                                ));
                            }
                        }
                    }

                    if (count($result) > 0) {
                        return $utils->createRespone(200, array(
                            'memberships' => $result,
                        ));
                    }
                }


                return $utils->createRespone(404, array(
                    'errors' => "No membership.",
                ));

            } else {
                //nu exista user-ul in baza de date

                if (!$user) {
                    return $utils->createRespone(404, array(
                        'errors' => "Username not found",
                    ));
                }
            }
        }

        else
            {
                //nu s-a trimis nimic prin POST
                return $utils->createRespone(206, array(
                    'errors' => "Partial data.",
                ));
            }
        }


    }