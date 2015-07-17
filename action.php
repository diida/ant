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

    public function exec($path)
    {
        $this->init($path);
        $ajax = request::isAjax();
        switch (request::server('REQUEST_METHOD')->val()) {
            case 'GET':
                return $ajax ? $this->ajaxGet() : $this->get();
            case 'POST':
                return $ajax ? $this->ajaxPost() : $this->post();
            case 'PUT':
                return $ajax ? $this->ajaxPut() : $this->put();
            case 'DELETE':
                return $ajax ? $this->ajaxDelete() : $this->delete();
        }
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