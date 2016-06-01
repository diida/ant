<?php
/**
 * Elasticsearch 客户端
 * Created by PhpStorm.
 * User: kevin
 * Date: 16-5-17
 * Time: 上午9:50
 */

namespace ant\plugins\nosql;

use ant\plugins\http\request;

class es
{
    /**
     * 服务器配置
     * @var array
     */
    public $host;
    /**
     * 查询模板的数组
     * @var array
     */
    public $query;

    public $index;

    static function build($host)
    {
        $o = new self();
        $o->host = $host;
        $o->query = [];
        return $o;
    }

    public function limit($from, $size)
    {
        $this->query['size'] = $size;
        $this->query['from'] = $from;
        return $this;
    }

    public function index($index)
    {
        $this->index = $index;
        return $this;
    }

    public function sort($field, $dir)
    {
        if (!isset($this->query['sort'])) {
            $this->query['sort'] = [];
        }
        $this->query['sort'][$field] = $dir;
    }

    public function fields($field, $_ = null)
    {
        $this->query['fields'] = func_get_args();
        return $this;
    }

    public function query($queryArray)
    {
        $this->query['query'] = $queryArray;
        return $this;
    }

    public function filtered($queryArray)
    {
        $this->query['filtered'] = $queryArray;
        return $this;
    }

    public function exec()
    {
        if (empty($this->index) || $this->index == '*') {
            return \ante::setError("需要设置index", []);
        }
        $index = $this->index;
        $h = new request();
        $h->init('POST', 'http://' . $this->host['host'] . ':' . $this->host['port'] . '/' . $index . '/_search',
            json_encode($this->query), 1000, 300000);
        $ret = $h->exec();
        $ret = json_decode($ret, 1);
        return $ret;
    }
}