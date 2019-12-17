<?php
/**
 * Created by PhpStorm.
 * User: Sahand
 * Date: 12/13/19
 * Time: 9:01 PM
 */

namespace App;


class Num2En
{

    public static function en($string){

        $en_num = array('0','1','2','3','4','5','6','7','8','9');
        $fa_num = array('۰','۱','۲','۳','۴','۵','۶','۷','۸','۹');
        return str_replace($fa_num,$en_num,$string);
    }
}