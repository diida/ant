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
    static function path($path)
    {
        if (!defined('CDN_ROOT')) {
            define('CDN_ROOT', '');
        }
        return CDN_ROOT . $path;
    }

    static function js($path)
    {
        $realPath = AUTOLOAD_ROOT . APP_NAMESPACE_ROOT_PATH . '/wwwroot/static/js/' . $path;
        $m = filemtime($realPath);
        $path = self::path('static/js/' . $path . '?' . $m);
        return '<script type="text/javascript" src="' . $path . '"></script>' . "\r\n";
    }

    static function css($path, $package = false)
    {
        $prefix = 'static/css/';
        if ($package) {
            $prefix = '';
        }

        $realPath = AUTOLOAD_ROOT . APP_NAMESPACE_ROOT_PATH . '/wwwroot/' . $prefix . $path;
        $m = filemtime($realPath);

        $path = self::path($prefix . $path . '?' . $m);
        return '<link rel="stylesheet" type="text/css" href="' . $path . '">' . "\r\n";
    }

    static function libJs($lib, $path)
    {
        $realPath = AUTOLOAD_ROOT . APP_NAMESPACE_ROOT_PATH . '/wwwroot/static/lib/' . $lib . '/' . $path;
        $m = filemtime($realPath);
        $path = self::path('static/lib/' . $lib . '/' . $path . '?' . $m);
        return '<script type="text/javascript" src="' . $path . '"></script>' . "\r\n";
    }

    static function libCss($lib, $path)
    {
        $realPath = AUTOLOAD_ROOT . APP_NAMESPACE_ROOT_PATH . '/wwwroot/static/lib/' . $lib . '/' . $path;
        $m = filemtime($realPath);
        $path = self::path('static/lib/' . $lib . '/' . $path . '?' . $m);
        return '<link rel="stylesheet" type="text/css" href="' . $path . '">' . "\r\n";
    }

    static function jsVar($name, $val)
    {
        return "var $name=" . json_encode($val) . ";\n";
    }

    public static function loadTpl($path, $data = [])
    {
        extract($data);
        include(AUTOLOAD_ROOT . APP_NAMESPACE_ROOT_PATH . '/html/' . $path . '.php');
    }
}