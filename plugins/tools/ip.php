<?php
/**
 * Created by JetBrains PhpStorm.
 * User: kevin
 * Date: 15-7-17
 * Time: 上午8:30
 * To change this template use File | Settings | File Templates.
 */

namespace ant\plugins\tools;

class ip
{

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


    static public function getIp($number = 0)
    {
        static $ip = NULL;

        $server = \ant\request::rawData('server');

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
}