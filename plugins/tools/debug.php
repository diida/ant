<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-7-16
 * Time: 下午5:15
 * To change this template use File | Settings | File Templates.
 */

namespace ant\plugins\tools;

class debug
{
    public static function output($msg)
    {
        if (defined('DEBUG')) {
            $msg = str_replace('<br/>', "\n", $msg);

            if (isset($_SERVER['HTTP_USER_AGENT'])) {
                header("Content-type:text/text");
            }

            echo $msg;
            return true;
        }

        return false;
    }
}