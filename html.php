<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-7-17
 * Time: 上午8:57
 * To change this template use File | Settings | File Templates.
 */

namespace ant;

class html
{
    static function js($path)
    {
        $realPath = AUTOLOAD_ROOT . APP_NAMESPACE_ROOT . '/wwwroot/static/js/' . $path;
        $m = filemtime($realPath);
        echo '<script type="text/javascript" src="' . $path . '?' . $m . '"></script>' . "\r\n";
    }

    static function css($path)
    {
        $realPath = AUTOLOAD_ROOT . APP_NAMESPACE_ROOT . '/wwwroot/static/css/' . $path;
        $m = filemtime($realPath);
        echo '<link rel="stylesheet" type="text/css" href="' . $path . '?' . $m . '">' . "\r\n";
    }

    static function jsVar($name, $val)
    {
        echo "var $name=" . json_encode($val) . "\n";
    }

    public static function loadTpl($path, $data = [])
    {
        extract($data);
        include(AUTOLOAD_ROOT . APP_NAMESPACE_ROOT . '/html/' . $path . '.php');
    }
}