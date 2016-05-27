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
     * @return bool
     */
    public function exec($path, $last)
    {
        $this->init($path);
        $ajax = request::isAjax();
        $methodName = $ajax ? 'ajax' : '';
        $requestMethod = request::server('REQUEST_METHOD')->val();
        $methodName .= $ajax ? ucfirst($requestMethod) : strtolower($requestMethod);

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
        extract($this->tplData);
        $file = AUTOLOAD_ROOT . APP_NAMESPACE_ROOT . '/html/' . $this->path . '.php';
        if (file_exists($file)) {
            include($file);
        }
    }
}