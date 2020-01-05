<?php
/**
 * Created by PhpStorm.
 * User: Sahand
 * Date: 1/5/20
 * Time: 5:47 PM
 */

namespace App\Repo;


class IpFinder
{

    public static function find(){

        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
                $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
}