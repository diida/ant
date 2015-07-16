<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-7-16
 * Time: 下午6:30
 * To change this template use File | Settings | File Templates.
 */

namespace ant;

class error extends \Exception
{
    public static function setError($err)
    {
        throw new error($err);
    }

    public function output()
    {
        if (defined('DEBUG')) {
            echo $this->getMessage();
        }
    }
}