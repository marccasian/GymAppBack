<?php
/**
 * Created by PhpStorm.
 * User: Mircea
 * Date: 1/13/2018
 * Time: 3:54 PM
 */


namespace AppBundle\Utils;
use Symfony\Component\HttpFoundation\Response;

class AllMyConstants
{
    const NUME_ANTRENOR = 'trainer';
    const NUME_USER = 'user';
    const PLATIT_TRUE = 1;
    const PLATIT_FALSE = 0;
    const ACTIV_TRUE = 1;
    const ACTIV_FALSE = 0;
    const WEEK_DAY = [
        0 => "Sun",
        1 => "Mon",
        2 => "Tue",
        3 => "Wed",
        4 => "Thu",
        5 => "Fri",
        6 => "Sat"];
}