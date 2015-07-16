<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-7-16
 * Time: 下午3:28
 * To change this template use File | Settings | File Templates.
 */


register_shutdown_function(function () use ($http) {
    $error = error_get_last();
    if (isset($error['type'])) {
        switch ($error['type']) {
            case E_ERROR :
            case E_PARSE :
            case E_DEPRECATED:
            case E_CORE_ERROR :
            case E_COMPILE_ERROR :
                $message = $error['message'];
                $file = $error['file'];
                $line = $error['line'];
                $log = "$message ($file:$line)\nStack trace:\n";
                $trace = debug_backtrace();
                foreach ($trace as $i => $t) {
                    if (!isset($t['file'])) {
                        $t['file'] = 'unknown';
                    }
                    if (!isset($t['line'])) {
                        $t['line'] = 0;
                    }
                    if (!isset($t['function'])) {
                        $t['function'] = 'unknown';
                    }
                    $log .= "#$i {$t['file']}({$t['line']}): ";
                    if (isset($t['object']) && is_object($t['object'])) {
                        $log .= get_class($t['object']) . '->';
                    }
                    $log .= "{$t['function']}()\n";
                }
                if (isset($_SERVER['REQUEST_URI'])) {
                    $log .= '[QUERY] ' . $_SERVER['REQUEST_URI'];
                }
                error_log($log);
                $http->send($this->currentFd, $log);
        }
    }
});

include_once("../../entry.php");
$http = new swoole_http_server("127.0.0.1", 9501);
$http->on('request', function ($request, $response) {
    ob_start();

    $s = ob_get_contents();
    ob_end_clean();
    $response->end($s);
});

//$http->set(array('worker_num' => 4, 'daemonize' => true));
$http->start();