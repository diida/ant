<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-7-16
 * Time: 下午3:07
 * To change this template use File | Settings | File Templates.
 */
namespace ant;

use ant\error;

class entry
{
    function __construct()
    {

    }

    /**
     * 初始化一些配置项和常量
     *
     */
    public static function init()
    {
        @ini_set('magic_quotes_gpc', 'Off');
        !defined('DS') && define('DS', "/");

        !defined('PATH_ANT') && define('PATH_ANT', __DIR__ . DS);

        /**
         * 自动加载类需要
         * app_name\rs\...
         */
        !defined('AUTOLOAD_ROOT') && define('AUTOLOAD_ROOT', dirname(__DIR__) . DS);

        /**
         * 项目要定义一个名称,对于一个url请求，很可能我们不知道项目根目录，也不想让人知道
         */
        if (!defined('APP_NAMESPACE_ROOT')) {
            echo "请设置 APP_NAMESPACE_ROOT 作为项目名/项目根目录名";
            return false;
        }
        define('APP_NAMESPACE_ROOT_PATH', str_replace('\\', '/', APP_NAMESPACE_ROOT));
        return true;
    }

    /**
     * 框架入口
     */
    public static function run($paths = [])
    {
        if (!self::init()) {
            return false;
        }

        //默认自动加载
        spl_autoload_register(['\\ant\\entry', 'autoLoad']);
        //初始化request
        request::getInstance();
        //初始化访问路径信息和请求参数
        list($paths, $last, $extra) = self::getPaths();

        $c = APP_NAMESPACE_ROOT . '\\rs\\' . implode('\\', $paths);

        try {
            request::getInstance();
            /**
             * @var $act \ant\action
             */
            $act = new $c;
            $act->exec(implode('/', $paths), $last, $extra);
            $act->display();
        } catch (error $e) {
            $e->output();
        }
        return false;
    }

    /**
     * todo:错误捕捉
     */
    public static function autoLoad($c)
    {
        $path = str_replace('\\', '/', $c);
        if (strpos($c, 'ant') === 0) {
            $file = PATH_ANT . str_replace('ant/', '', $path) . '.php';
        } else {
            $file = AUTOLOAD_ROOT . $path . '.php';
        }

        if (is_file($file)) {
            require_once($file);
            if (!class_exists($c)) {
                error::throwError(error::CLASS_NO_EXISTS, $c);
            }
        } else {
            error::throwError(error::CLASS_FILE_NO_EXISTS, $file . ':' . $c);
        }
    }

    private static function getPaths()
    {
        if (empty($paths)) {
            $path = request::get('path')->trim()->val();
            if (empty($path)) {
                $path = request::server('DOCUMENT_URI')->val();
            }

            if (defined('APP_URL_PREFIX')) {
                $path = str_replace(APP_URL_PREFIX, '', $path);
            }
            $paths = explode('/', $path);
            $paths = array_filter($paths);
        }

        $tmp = [];
        $params = false;
        $key = false;
        $last = false;
        $extra = [];
        foreach ($paths as $k => $path) {
            if ($path[0] == '_') {
                if (strlen($path) > 1) {
                    $last = $path;
                }
                $params = true;
            } else if (is_numeric($path)) {
                request::get('id')->setDefault($path, 'empty');
                $params = true;
            } else if (!$params) {
                $tmp[] = $path;
            } else {
                if ($key) {
                    request::get($key)->setDefault($path, 'empty');
                    $extra[] = $path;
                    $key = false;
                } else if (is_numeric($path)) {
                    request::get('id')->setDefault($path, 'empty');
                    $params = true;
                } else {
                    $key = $path;
                    $extra[] = $key;
                }
            }
        }

        $tmp = array_filter($tmp, function ($v) {
            return preg_match('/^[\w]+$/', $v) && !empty($v);
        });

        if (empty($tmp)) {
            $tmp[] = 'index';
        }

        return [$tmp, $last, $extra];
    }
}