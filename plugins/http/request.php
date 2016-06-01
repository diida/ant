<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-1-25
 * Time: 下午12:46
 * To change this template use File | Settings | File Templates.
 */


namespace ant\Plugins\http;

class request
{
    public $options = [
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_CONNECTTIMEOUT_MS => 1000
    ];
    public $ch;
    public $isProxy = false;

    static public function get($url, $params = [], $timeout = 0)
    {
        $r = new self();
        $r->init('get', $url, $params, $timeout);
        return $r->exec();
    }

    public function init($method, $url, $params = [], $connectTimeoutMs = 0, $requestTimeoutMs = 0)
    {
        if ($connectTimeoutMs) {
            $this->options[CURLOPT_CONNECTTIMEOUT_MS] = $connectTimeoutMs;
        }

        if ($requestTimeoutMs) {
            $this->options[CURLOPT_TIMEOUT_MS] = $requestTimeoutMs;
        }


        if ($method == 'post') {
            $this->options[CURLOPT_POST] = 1;
            if (!empty($params)) {
                $this->options[CURLOPT_POSTFIELDS] = $params;
            }
        } else if ($method == 'get') {
            if (!empty($params)) {
                $query = [];
                foreach ($params as $k => $v) {
                    $query[] = $k . '=' . urlencode($v);
                }
                $query = implode('&', $query);
                if (strpos($url, '?') !== false) {
                    $url .= '&' . $query;
                } else {
                    $url .= '?' . $query;
                }
            }
        } else {
            $this->options[CURLOPT_CUSTOMREQUEST] = strtoupper($method);
            if (!empty($params)) {
                $this->options[CURLOPT_POSTFIELDS] = $params;
            }
        }

        $this->ch = curl_init($url);
    }

    public function exec()
    {
        if (isset($this->options[CURLOPT_POSTFIELDS])) {
            if (is_string($this->options[CURLOPT_POSTFIELDS])) {
                curl_setopt($this->ch, CURLOPT_HTTPHEADER,
                    [
                        'Content-Length: ' . strlen($this->options[CURLOPT_POSTFIELDS])
                    ]
                );
            }
        }
        curl_setopt_array($this->ch, $this->options);
        $response = curl_exec($this->ch);
        if ($this->isProxy) {
            $headerSize = curl_getinfo($this->ch, CURLINFO_HEADER_SIZE);
            $header = substr($response, 0, $headerSize);
            $body = substr($response, $headerSize);
            return [$header, $body];
        }
        return $response;
    }

    public function asProxy()
    {
        $this->options[CURLOPT_HEADER] = true;
        $this->options[CURLOPT_NOBODY] = false;
        $this->isProxy = true;
    }

    static public function post($url, $params = [], $timeout = 0)
    {
        $r = new self();
        $r->init('post', $url, $params, $timeout);
        return $r->exec();
    }

    public static function put($url, $params = [], $timeout = 0)
    {
        $r = new self();
        $r->init('PUT', $url, $params, $timeout);
        return $r->exec();
    }

    public static function delete($url, $params = [], $timeout = 0)
    {
        $r = new self();
        $r->init('DELETE', $url, $params, $timeout);
        return $r->exec();
    }
}