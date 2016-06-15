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

    /**
     * 多组JSON数据的情况下，数据会暂存在这里
     * @var bool
     */
    public $jsonDataList = false;
    public $jsonDataOffset = 0;
    public $jsonDataSize = 0;

    /**
     * 一些前端框架将JSON放在请求体中
     */
    static function initJson($desType = '')
    {
        $me = self::$instance;
        if ($me->jsonDataList) {
            if ($me->jsonDataOffset < $me->jsonDataSize) {
                $data = $me->jsonDataList[$me->jsonDataOffset++];
            } else {
                $data = false;
            }
        } else {
            $type = self::server('REQUEST_METHOD')->enum(['GET', 'PUT', 'POST', 'DELETE'])->val();
            $type = strtolower($type);

            if (empty($me->put['put'])) {
                $json = "";
                $fp = fopen('php://input', 'r');
                while ($kb = fread($fp, 1024)) {
                    $json .= $kb;
                }
                fclose($fp);
            } else {
                $json = $me->put['put'];
                unset($me->put['put']);
            }

            $data = json_decode($json, 1);
            if (isset($data[0])) {
                $me->jsonDataList = $data;
                $me->jsonDataOffset = 1;
                $me->jsonDataSize = count($data);
                $data = $data[0];
            } else {
                $me->jsonDataList = [$data];
                $me->jsonDataOffset = 1;
                $me->jsonDataSize = 1;
            }
        }

        if (json_last_error()) {
            $me->$type = [];
        } else {
            if ($desType != '') {
                $me->$desType = $data;
            } else {
                $me->$type = $data;
            }
        }
        return $data;
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
                if ($key == '*') {
                    return $arr;
                }
                self::$instance->val = null;
            }

        }
        return $this;
    }

    private function save()
    {
        $type = $this->type;
        $arr = &$this->$type;
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

    function enum($enum = [], $focus = true)
    {
        if (!in_array($this->val, $enum)) {
            if ($focus) {
                $this->val = $enum[0];
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

    static function put($key = 'put')
    {
        return self::$instance->request('put', $key);
    }
}