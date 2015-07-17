<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-7-16
 * Time: 下午6:54
 * To change this template use File | Settings | File Templates.
 */

namespace ant;

class request
{
    private $server = null;
    private $get = null;
    private $put = null;
    private $post = null;
    private $cookie = null;

    private $key = null;
    private $val = null;
    private $type = null;

    static function rawData($key)
    {
        return self::$instance->$key;
    }

    /**
     * @var self
     */
    static $instance = null;

    static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->init();
        }
        return self::$instance;
    }

    function init()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->cookie = $_COOKIE;

        if (!isset($_SERVER['REQUEST_METHOD'])) return;

        if (strtolower($_SERVER['REQUEST_METHOD']) == 'put' || strtolower($_SERVER['REQUEST_METHOD']) == 'delete') {
            $raw = '';
            $fp = fopen('php://input', 'r');
            while ($kb = fread($fp, 1024)) {
                $raw .= $kb;
            }
            fclose($fp);
            $this->put['put'] = $raw;
        }

        unset($_GET);
        unset($_POST);
        unset($_SERVER);
        unset($_COOKIE);

        if (isset($this->cookie['PHPSESSID'])) {
            $_COOKIE['PHPSESSID'] = $this->cookie['PHPSESSID'];
        }
    }

    static function get($key)
    {
        return self::$instance->request('get', $key);
    }

    static function getInt($key)
    {
        return self::get($key)->int()->val();
    }

    static function post($key)
    {
        return self::$instance->request('post', $key);
    }

    static function cookie($key)
    {
        return self::$instance->request('cookie', $key);
    }

    static function server($key)
    {
        return self::$instance->request('server', $key);
    }

    private function request($type, $key)
    {
        if (in_array($type, array('post', 'get', 'server', 'cookie', 'put'))) {
            self::$instance->type = $type;
            self::$instance->key = $key;
            $arr = $this->$type;

            if (isset($arr[$key])) {
                self::$instance->val = $arr[$key];
            } else {
                self::$instance->val = null;
            }

        }
        return $this;
    }

    private function save()
    {
        $type = $this->type;
        $arr = & $this->$type;
        $arr[$this->key] = $this->val;
    }

    function int($focus = true)
    {
        if (!is_int($this->val)) {
            if ($focus) {
                $this->val = intval($this->val);
            } else {
                $this->val = null;
            }
        }
        $this->save();
        return $this;
    }

    function clear()
    {
        $this->val = null;
        $this->save();
        return $this;
    }

    function trim()
    {
        if (is_null($this->val)) return $this;
        $this->val = trim($this->val);
        $this->save();
        return $this;
    }

    /**
     * @param $v
     * @param string $type NULL 或者 empty
     * @return $this
     */
    function setDefault($v, $type = 'NULL')
    {
        if ($type == 'NULL' && is_null($this->val)) {
            $this->val = $v;
        } else if ($type == 'empty' && empty($this->val)) {
            $this->val = $v;
        }
        $this->save();
        return $this;
    }

    function val()
    {
        return $this->val;
    }

    function min($int)
    {
        if ($this->val < $int) {
            $this->val = $int;
            $this->save();
        }
        return $this;
    }

    static function isAjax()
    {
        if (self::server('HTTP_X_REQUESTED_WITH')->val() == 'XMLHttpRequest') {
            return true;
        }
        return false;
    }

    static function put()
    {
        return self::$instance->request('put', 'put');
    }
}