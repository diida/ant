<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-7-16
 * Time: 下午6:29
 * To change this template use File | Settings | File Templates.
 */

namespace base\rs;

use \ant\action;
use ant\request;

class index extends action
{
    function get()
    {
        echo '<pre>';
        print_r(request::get('*'));
        echo 'index';
    }

    function getColumn()
    {

        echo '<pre>';
        print_r(request::get('*'));
        echo 'here is colume<br>';
    }
}