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

    public function sendEmail($post){
        $ch = curl_init('http://elephpants.000webhostapp.com/sendEmail.php');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post);

        $response = curl_exec($ch);
        curl_close($ch);


        if($response == "OK")
        {
            return true;
        }
        else{
            return false;
        }


    }

    public function generateRandomCode($length = 5) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

}