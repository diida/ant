<?php
/**
 * antc             资源基类，控制模块和视图部分
 * @name            antc
 * @author          diida
 * @version         4.0
 */
class antc
{
    /**
     * 自执行返回值
     */
    public $selfExecuteResult = false;
    /**
     * 是否拥有视图，一些AJAX请求或者接口，往往没有视图
     * @var bool
     */
    protected $hasView = true;
    /**
     * 保存需要展示的数据
     * @var array
     */
    public $displayParam = array();
    /**
     * 模板的 rs 用于指定模板位置，不同的控制器可以使用相同的模板
     * @var null
     */
    public $tprs = null;
    public $tpact = null;
    /**
     * 指定request的位置
     * @var array|null
     */
    public $requestAct = null;
    /**
     * @var antp
     */
    public $tp;
    /**
     * @var antr
     */
    public $request;
    /**
     * 每个缓存目录中的文件数量
     * @var int
     */
    public $cachePage = 50;
    /**
     * 缓存时间长短
     * @var int
     */
    public $cache = 0;
    /**
     * 缓存文件名
     * @var null
     */
    public $cacheFileName = '';
    /**
     * 是否允许写缓存
     * @var bool
     */
    public $writeAble = true;

    public $rs;
    public $act;

    /**
     * 由于静态函数run的实现过于复杂，对现有代码改动很大，所以最终采用冗余生成对象的方式来实现这个功能
     * 通过:
     * new rs_index_help(true);来自执行rs=index&act=help这个控制器对象
     * @param bool $selfExecute
     * @param array $displayParam
     */
    function __construct($selfExecute = false,$displayParam = array())
    {
        if($selfExecute) {
            $name = get_class($this);
            $namePieces = explode('_',$name);
            $this->init(strtolower($namePieces[1]),strtolower($namePieces[2]));

            $r = ant::getRequest($this);
            $this->displayParam = $displayParam;
            $type = isset($_SERVER['REQUEST_METHOD']) ? $_SERVER['REQUEST_METHOD'] : 'GET';
            if($type == 'GET')
                $this->selfExecuteResult = $this->exec($r);
            else
                $this->selfExecuteResult = $this->post($r);
            $this->display();
        }
    }

    function init($rs,$act)
    {
        $this->rs = $rs;
        $this->act = $act;
        if ($this->requestAct === null)
            $this->requestAct = array($this->rs, $this->act);
    }

    function noView()
    {
        $this->hasView = false;
    }

    function useView()
    {
        $this->hasView = true;
    }

    function exec(antr $r)
    {

    }

    function post(antr $r)
    {

    }

    function assign($name, $value)
    {
        $this->displayParam[$name] = $value;
    }

    function getAssign($name)
    {
        if (isset($this->displayParam[$name]))
            return $this->displayParam[$name];
        else
            return null;
    }

    function display()
    {
        $this->tp = new antp($this->rs, $this->act);

        if ($this->hasView == false) return $this->tp;
        if ($this->tpact) $this->tp->act = $this->tpact;
        if ($this->tprs) $this->tp->rs = $this->tprs;
        $this->displayParam['r'] = $this->request;
        $this->tp->loadData($this->displayParam);

        if ($this->cache > 0 && $this->writeAble) {
            $s = $this->tp->sdisplay();
            $fp = fopen($this->cacheFileName, 'w');
            fwrite($fp, $s);
            fclose($fp);
            echo $s;
        } else {
            $this->tp->display();
        }

        return $this->tp;
    }

    function useCache(antr $r)
    {
        if ($this->cache <= 0) return false;

        $id = $r->getValue('cache_id');
        $cache_id = get_class($this);

        if ($id !== null) {
            if (is_numeric($id)) {
                $d = ceil($id / $this->cachePage);
                $dir = PATH_CACHE . $cache_id . DS . $d . DS;
            } else {
                $dir = PATH_CACHE . $cache_id . DS;
            }

            $fn = $dir . $id . '.html';
        } else {
            $dir = PATH_CACHE;
            $fn = $dir . $cache_id . '.html';
        }

        $this->cacheFileName = $fn;

        if (file_exists($dir) == false) {
            if (!mkdir($dir, 0755, true)) {
                $this->cache = 0;
            }
        }

        if (file_exists($fn)) {
            $fp = fopen($fn, 'r');
            if (flock($fp, LOCK_EX | LOCK_NB)) {
                if ($r->get('ant_clear_cache')->value() == 1) {
                    //后台来的清缓存的访问
                } else {
                    $time = time() - filemtime($fn);
                    if (($time / 60) <= $this->cache) {
                        echo file_get_contents($fn);
                        return true;
                    }
                }
                fclose($fp);
                @unlink($fn);
                $this->writeAble = true; //授权写
            } else {
                $this->writeAble = false; //不授权写
                echo file_get_contents($fn); //只管读
                return true;
            }
        }

        return false;
    }

    function forceTp($act, $rs = null)
    {
        $this->tpact = $act;
        if ($rs) $this->tprs = $rs;
    }

    /**
     * 调用此函数，确保JSON对象格式固定
     * @param  $success
     * @param string $data
     * @param null $message
     * @return void
     */
    function jsonResult($success, $data = '', $message = null)
    {
        $this->noView();
        $message = iconv('gbk', 'utf-8', $message);
        $c = new stdClass();
        /** @noinspection PhpUndefinedFieldInspection */
        $c->success = $success;
        $c->data = $data;
        if ($message !== null) {
            /** @noinspection PhpUndefinedFieldInspection */
            $c->message = $message;
        }
        $s = json_encode($c);
        if (isset($_GET['callback'])) {
            $s = addslashes($s);
            echo $_GET['callback'] . "(\"$s\")";
        } else
            echo $s;
    }

    function jsonError()
    {
        $e = ant::getErrorInfo();
        if ($e) {
            $this->jsonResult(false, $e['errno'], $e['error']);
        } else {
            $this->jsonResult(true, '', 'no error');
        }
    }

    /**
     * 获取IP
     * @static
     * @return string
     */
    static function returnIp()
    {
        $ip = "-1";
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip_a = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
            for ($i = 0; $i < count($ip_a); $i++) { //
                $tmp = trim($ip_a[$i]);
                if ($tmp == 'unknown' || $tmp == '127.0.0.1' || strncmp($tmp, '10.', 3) == 0 || strncmp($tmp, '172', 3) == 0 || strncmp($tmp, '192', 3) == 0)
                    continue;
                $ip = $tmp;
                break;
            }
        } else if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = trim($_SERVER['HTTP_CLIENT_IP']);
        } else if (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = trim($_SERVER['REMOTE_ADDR']);
        }

        return $ip;
    }

    /**
     * 返回格式化后的标准url
     * 很多时候我们会在url中不写index，但是这里会补上
     * @static
     * @return string
     */
    static function returnUrl()
    {
        $uri = '';
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $rs = isset($_GET['rs']) ? $_GET['rs'] : 'index';
            $act = isset($_GET['act']) ? $_GET['act'] : 'index';

            ksort($_GET);
            $uri .= '/index.php?rs=' . $rs . '&act=' . $act;
            if (!empty($_GET)) {
                foreach ($_GET as $k => $v) {
                    if (in_array($k, array('rs', 'act'))) continue;
                    $uri .= "&$k=$v";
                }
            }
        }
        return $uri;
    }

    /**
     * ant支持这样调用一个控制器
     * ant::action('index','help');//调用帮助页面,但是这个页面的控制器在哪里无法定位，尤其是(IDE,项目组的新人)
     * 这里提供一种更加容易阅读的代码书写方式
     * rs_index_help::run();//代码在rs/index/help.php
     * 两种方法各有好处，但是在控制器命名不会改变的情况下，第二种将更加友好
     * 但是你必须复制这个代码到每个控制器中，否则__CLASS__无法正常使用，希望之后PHP能够提供更好的支持
     *
     * @static
     * @param array $displayParam
     * @param string $type
     * @return bool
     */
    static function run($displayParam = array(),$type = 'GET')
    {
        $name = __CLASS__;
        $namePieces = explode('_',$name);
        return ant::action($namePieces[1],$namePieces[2],$displayParam,$type);
    }
}
