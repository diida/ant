<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-7-16
 * Time: 下午6:50
 * To change this template use File | Settings | File Templates.
 */

namespace ant;

class action
{

    public $path;
    public $view = true;

    public function init($path)
    {
        $this->path = $path;
    }

    /**
     * 你可以访问的函数包括
     * get post put delete ajaxGet ajaxPost ajaxDelete ajaxPut
     * 以上面这些词开头的函数
     * @param $path
     * @param $last
     * @param $extra
     * @return bool
     */
    public function exec($path, $last = false, $extra = [])
    {
        $this->init($path);
        $ajax = request::isAjax();
        $methodName = $ajax ? 'ajax' : '';
        $requestMethod = request::server('REQUEST_METHOD')->val();
        $methodName .= $ajax ? ucfirst($requestMethod) : strtolower($requestMethod);

        if ($ajax) {
            $this->view = false;
        }
        
        if ($last) {
            $last = substr($last, 1);
            $this->path .= '/' . $last;
            $methodName .= ucfirst($last);
        }

        $this->$methodName();
        return false;
    }

    public function get()
    {
        return true;
    }

    public function delete()
    {
        return true;
    }

    public function ajaxDelete()
    {
        return true;
    }

    public function put()
    {
        return true;
    }

    public function ajaxPut()
    {
        return true;
    }

    public function post()
    {
        return true;
    }

    public function ajaxPost()
    {
        return true;
    }

    public function ajaxGet()
    {
        return true;
    }

    public $tplData = [];

    public function assign($key, $val)
    {
        $this->tplData[$key] = $val;
    }

    public function display()
    {
        if ($this->view) {
            self::loadTpl($this->tplData, $this->path);
        }
    }

    public static function loadTpl(&$data, $path)
    {
        extract($data);
        $appNameSpace = str_replace('\\', '/', APP_NAMESPACE_ROOT);
        $file = AUTOLOAD_ROOT . $appNameSpace . '/html/' . $path . '.php';
        if (strpos($file, '..') !== false) {
            error::throwError(error::TEMPLATE_NO_EXISTS);
        }

        if (is_file($file)) {
            include($file);
        }
    }

    public static function location($uri, $code = 302)
    {
        header("Location:$uri");
    }
}