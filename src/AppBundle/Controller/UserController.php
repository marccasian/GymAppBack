<?php
/**
 * Created by PhpStorm.
 * User: cmarc
 * Date: 1/18/2018
 * Time: 9:33 PM
 */

namespace AppBundle\Controller;
header("Access-Control-Allow-Origin: *");
use AppBundle\Entity\ActivationCode;
use AppBundle\Entity\Profile;
use AppBundle\Entity\Rol;
use AppBundle\Utils\AllMyConstants;
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

class UserController extends Controller
{
    /**
     * @Route("/user/getAllUsers", name = "get_all_users")
     * @Method({"GET"})
     * @param Request $request
     * @return Response
     */
    public function getAllUsers(Request $request)
    {
        $utils = new Functions();
        $repoUsers = $this->getDoctrine()->getManager()->getRepository(User::class);
        $users = $repoUsers->findBy(array(
            'rolid' => $this->getUserRolId(AllMyConstants::NUME_USER),
        ));
        $results = [];
        /** @var  $item User */
        foreach ($users as $item) {
            $results[] = [
                'username' => $item->getUsername(),
                'rolId' => $item->getRolid()->getRolid(),
                'rolDescription' => $item->getRolid()->getDescription()
            ];
        }
        return $utils->createResponse(200, $results);
    }

    private function getUserRolId($NUME_USER)
    {
        $repo = $this->getDoctrine()->getManager()->getRepository(Rol::class);
        /** @var  $rol Rol*/
        $rol = $repo->findOneBy(array(
            'description' => $NUME_USER
        ));
        return $rol->getRolid();
    }
}