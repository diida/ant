<?php
/**
 * Created by JetBrains PhpStorm.
 * User: xmyj-0104
 * Date: 14-3-14
 * Time: 下午2:39
 * To change this template use File | Settings | File Templates.
 */

namespace ant\plugins\nosql;

class redis extends \redis
{
    static $instances = array();
    public $connected = false;

    /**
     * @param $config
     * @param string $filename
     * @return \ant\plugins\nosql\redis
     */
    static function getInstance($config,$filename ='config')
    {
        $c = $config . '\config\\'.$filename;
        $info = $c::redis();
        $host = $info['host'];
        $port = $info['port'];
        $db = $info['db'];

        $key = $config;

        if (!isset(self::$instances[$key])) {
            $redis = new self();
            $ret = false;
            try {
                $ret = $redis->connect($host, $port);
            } catch (\RedisException $e) {

            } catch (\Exception $e) {

            }

            if ($ret) {
                $redis->connected = true;
            }
            self::$instances[$key] = $redis;
        }
        return self::$instances[$key];
    }
}