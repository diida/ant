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
    public static $baseCode = 1000;

    const CLASS_NO_EXISTS = 2;
    const CLASS_FILE_NO_EXISTS = 3;
    const TEMPLATE_NO_EXISTS = 4;

    static $error = [
        self::CLASS_FILE_NO_EXISTS => '类文件 %s 不存在',
        self::CLASS_NO_EXISTS => '类 %s 不存在',
        self::TEMPLATE_NO_EXISTS => '模板不存在',
    ];

    public static function throwError($errno)
    {

        $error = self::$error[$errno];
        $args = func_get_args();

        array_shift($args);
        array_unshift($args, $error);
        if (count($args) > 1) {
            $error = call_user_func_array('sprintf', $args);
        }

        $errno += self::$baseCode;
        throw new error($error, $errno);
    }

    public function output()
    {
        if (defined('DEBUG')) {
            echo $this->getMessage();
        }
    }
}