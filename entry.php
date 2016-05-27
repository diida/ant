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

        define('PATH_ANT', __DIR__ . DS);

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
        return true;
    }

    /**
     * 框架入口
     */
    public static function run()
    {
        if (!self::init()) {
            return false;
        }

        //默认自动加载
        spl_autoload_register(['\\ant\\entry', 'autoLoad']);
        //初始化request
        request::getInstance();

        $path = request::get('path')->trim()->val();
        if (empty($path)) {
            $path = request::server('QUERY_STRING')->val();
        }

        $paths = explode('/', $path);
        $paths = array_filter($paths, function ($v) {
            return preg_match('/^\w+$/', $v) && !empty($v);
        });

        $len = count($paths);
        if ($len == 0) {
            $paths[] = 'index';
            $len = 1;
        }

        $last = $paths[$len - 1];

        if (strpos($last, '_') === 0) {
            unset($paths[$len - 1]);
        } else {
            $last = false;
        }

        $c = APP_NAMESPACE_ROOT . '\\rs\\' . implode('\\', $paths);

        try {
            request::getInstance();
            /**
             * @var $act \ant\action
             */
            $act = new $c;
            $act->exec(implode('/', $paths), $last);
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
                error::setError('类：' . $c . '找不到');
            }
        } else {
            error::setError('类文件：' . $file . '找不到');
        }
    }
}