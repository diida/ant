<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-7-16
 * Time: 下午3:34
 * To change this template use File | Settings | File Templates.
 */

include_once("../../../entry.php");
define('APP_NAMESPACE_ROOT', 'base');
define('AUTOLOAD_ROOT', dirname(dirname(__DIR__)) . '/');
define('DEBUG', 1);

\ant\entry::run();