<?php

namespace AppBundle\Utils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Created by PhpStorm.
 * User: User
 * Date: 1/7/2018
 * Time: 3:49 PM
 */
class Functions
{

    /**
     * Functions constructor.
     */
    public function __construct()
    {
    }

    public function createRespone($statusCode, $content){

        $respone = new Response();
        $respone->headers->set('Content-Type', 'application/json');
        $respone->setStatusCode($statusCode);
        $respone->setContent(json_encode($content));
        return $respone;
    }

}