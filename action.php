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

    public function exec()
    {
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'GET':
                return $this->get();
            case 'POST':
                return $this->post();
            case 'PUT':
                return $this->put();
            case 'DELETE':
                return $this->delete();
        }
    }

    public function get()
    {
        return true;
    }
}