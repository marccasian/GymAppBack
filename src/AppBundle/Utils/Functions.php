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

    public function createResponse($statusCode, $content){

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setStatusCode($statusCode);
        $response->setContent(json_encode($content));
        return $response;
    }

}