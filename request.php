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

    const TYPE_INT = 1;
    const TYPE_STRING = 2;
    const TYPE_ENUM = 3;

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

    static function get($key, $type)
    {
        return self::$instance->request('get', $key, $type);
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

    private function request($type, $key, $type)
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

    static public function getIp($number = 0)
    {
        static $ip = NULL;

        $me = self::getInstance();
        $server = $me->server;

        if (empty($ip)) {
            $keys = array('HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_REMOTE_HOST', 'HTTP_CLIENT_IP', 'REMOTE_ADDR');
            $found = FALSE;
            foreach ($keys as $key) {
                if (!isset($server[$key])) continue;
                $theIp = trim($server[$key]);
                $ips = array();
                if (preg_match_all('/(\d{1,3}(?:\.\d{1,3}){3})/is', $theIp, $ips)) {
                    foreach ($ips[1] as $ip) {
                        if (!self::isPersist($ip)) {
                            $found = TRUE;
                            break;
                        }
                    }
                }
                if ($found) {
                    break;
                }
            }
        }
        if ($number) {
            return self::ip2long($ip);
        }
        return $ip;
    }

    /**
     * 判断是否是无效地址，广播地址
     * @param string $ip
     * @return boolean
     */
    static public function isPersist($ip)
    {
        $ip = self::ip2long($ip);
        $persist = explode(",", "167772160,184549375,2130706433,2130706433,2886729728,2887778303,3232235520,3232301055");
        $len = count($persist);
        for ($i = 0; $i < $len; $i += 2) {
            if ($ip >= $persist[$i] && $ip <= $persist[$i + 1]) {
                return TRUE;
            }
        }
        return FALSE;
    }

    /**
     * 将IP转换成整型字符串
     * @param string $ip
     * @return int
     */
    static public function ip2long($ip)
    {
        if ($ip && !is_int($ip)) {
            $ip = sprintf("%u", ip2long($ip));
        }
        return intval($ip);
    }
}