<?php
/**
 * Core of Ant
 * @name            ant.php
 * @author          王钟凯 (diida)
 * @E-mail          kevinaccess@live.cn
 * @version         4.0
 */
$thisPath = dirname(__FILE__);
require($thisPath . '/const.php');
require($thisPath . '/ante.php');
require($thisPath . '/antl.php');
require($thisPath . '/antc.php');
require($thisPath . '/antp.php');
require($thisPath . '/antr.php');
/**
 * @name ant
 */
class ant
{
    /**
     * @var ant
     */
    static $instance = null;
    /**
     * @var callback function
     */
    static $authFunction = null;
    /**
     * @var Function
     */
    static $msgFunction = null;
    /**
     * @var ante
     */
    static $error = null;

    function __construct()
    {
        ini_set('magic_quotes_gpc', 'Off');
        if (!defined('DS')) define('DS', "/");
        if (!defined('PATH_ROOT')) define('PATH_ROOT', dirname($_SERVER['SCRIPT_FILENAME']) . DS);
        if (!defined('PATH_RS')) define('PATH_RS', PATH_ROOT . 'rs' . DS);
        if (!defined('PATH_CACHE')) define('PATH_CACHE', PATH_ROOT . 'cache' . DS);
        if (!defined('PATH_REQUEST')) define('PATH_REQUEST', PATH_ROOT . 'request' . DS);
        if (defined('VIEW_NAME'))
            $view_name = VIEW_NAME;
        else
            $view_name = 'view';
        if (!defined('PATH_VIEW')) define('PATH_VIEW', PATH_ROOT . $view_name . DS);
        if (!defined('PATH_TPL')) define('PATH_TPL', PATH_VIEW . 'html' . DS);
        if (!defined('URL_ROOT')) define('URL_ROOT', '');

        define('PATH_ANT', dirname(__FILE__) . DS);
        define('ANT_ENTER', basename($_SERVER['SCRIPT_FILENAME']));
    }

    /**
     * 从这里开始运行
     * @return mix
     */
    function run()
    {
        if (!empty($_SERVER['PATH_INFO'])) {
            $p = explode('/', $_SERVER['PATH_INFO']);
            array_shift($p);
            $rs = isset($p[0]) ? trim($p[0]) : '';
            $act = isset($p[1]) ? trim($p[1]) : '';
            for ($i = 2, $l = count($p); $i < $l; $i += 2) {
                $_GET[$p[$i]] = isset($p[$i + 1]) ? $p[$i + 1] : '';
            }
        } else {
            $act = isset($_GET['act']) ? trim($_GET['act']) : '';
            $rs = isset($_GET['rs']) ? trim($_GET['rs']) : '';
        }
        if (empty($act) && empty($rs)) {
            $act = isset($_POST['act']) ? trim($_POST['act']) : 'index';
            $rs = isset($_POST['rs']) ? trim($_POST['rs']) : 'index';
        }

        $act = empty($act) ? 'index' : $act;
        $rs = empty($rs) ? 'index' : $rs;

        return self::action($rs, $act, array(), $_SERVER['REQUEST_METHOD']);
    }

    /**
     * action
     * 控制器执行模块，可以独立运行
     * @name        action
     * @param       $rs 资源
     * @param       $act 控制器
     * @param       $displayParam 需要在模板中展示的参数
     * @param       $type 指定请求类型
     * @access      static
     */
    static function action($rs, $act, $displayParam = array(), $type = 'GET')
    {
        $rs   = strtolower($rs);
        $act  = strtolower($act);
        $type = strtolower($type);
        
        if ((self::$authFunction === null) || (self::$authFunction && call_user_func_array(self::$authFunction, array($rs, $act)))) {
            $c = 'rs_' . $rs . '_' . $act;
            if (antl::getInstance()->load('act', $rs, $act)) {
                if (class_exists($c)) {
                    /**
                     * @var antc $c
                     */
                    $c = new $c(false);
                    $c->init($rs,$act);
                    $c->displayParam = $displayParam;

                    $r = self::getRequest($c);
                    $ret = true;
                    if ($c->useCache($r) == false) {
                        if ($type == 'get')
                            $ret = $c->exec($r);
                        else
                            $ret = $c->post($r);
                        $c->display();
                    }
                    return $ret;
                }
            }

            //仅视图页面使用更加轻量的对象.
            $c = new antp($rs, $act);
            $c->loadData($displayParam);
            if (!$c->display())
                self::E('CONTROLLER_NOT_FOUND');
            return false;
        }
    }

    /**
     * getRequest
     * 将过滤器载入，过滤器中"仅"保存过滤后的内容
     * @name    getRequest
     * @param   antc $o
     * @access  static
     * @return  antr     request请求对象,失败返回false
     */
    static function getRequest($o)
    {
        /**
         * @var antr $r
         */
        $rs = $o->requestAct[0];
        $act = $o->requestAct[1];
        if (antl::getInstance()->load('request', $rs, $act)) {
            $c = "request_{$rs}_$act";
            if (class_exists($c)) {
                $r = new $c();
                $r->findAllErrors = false;
            } else
                $r = new antr();
        } else {
            $r = new antr();
        }
        $o->request = $r;
        $r->act = $o;
        $r->run();
        return $r;
    }

    /**
     * @return ant
     */
    static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    static function E($err = "", $params = array())
    {
        if (defined('DEBUG')) {
            $title = $GLOBALS['ant']['core_error'][$err]['title'];
            $detail = $GLOBALS['ant']['core_error'][$err]['detail'];
            extract($params);
            $detail = eval('return "' . $detail . '";');
            antp::info("wrong", "Ant core error!", $title, $detail);
        }
    }

    function registerAuthFunction($func)
    {
        self::$authFunction = $func;
    }

    function registerMsgFunction($func = null)
    {
        self::$msgFunction = $func;
    }

    /**
     * 自动调用注册过的消息函数，并阻止外层控制器展示视图
     * @static
     * @param  $msg
     * @param int $status
     * @return
     */
    static function message($msg, $status = 200)
    {
        $a = debug_backtrace();

        if (self::$msgFunction == null) {
            return;
        }

        if (isset($a[1]['object']) && $a[1]['object'] instanceof antc) {
            $a[1]['object']->noview();
        }
        call_user_func_array(self::$msgFunction, array($msg, $status));
    }

    /**
     * DEBUG
     * @static
     * @param  $error
     * @param null $errno
     * @param null $key
     * @return void
     */
    static function setError($error, $errno = null, $key = null)
    {
        if (self::$error == null) self::$error = new ante();
        self::$error->setError($error, $errno, $key);
    }

    static function getError($key = null)
    {
        if (self::$error == null) self::$error = new ante();
        return self::$error->getError($key);
    }

    static function getErrorInfo($key = null)
    {
        if (self::$error == null) self::$error = new ante();
        return self::$error->getErrorInfo($key);
    }

    static function printErrorStack()
    {
        self::$error->printErrorStack();
    }

}
