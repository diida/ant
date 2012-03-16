<?php
/**
 * ant loader   负责加载框架文件
 * 其他文件使用autoLoad加载
 * @name        antl
 * @author      王钟凯 (diida)
 * @version     4.0
 */
class antl
{
    /**
     * @var antl
     */
    static $ins = NULL;
    public $display_data;

    /**
     * @static
     * @return antl|null
     */
    static function getInstance()
    {
        if (self::$ins == NULL) self::$ins = new self();
        return self::$ins;
    }

    /**
     * load
     * 用于生成控制器，模块，视图的载入地址并载入,也规定了框架和项目的目录结构
     * @name        load
     * @param    $__type 字符串,取值为 act,tpl,cache,request,module
     * @param    $rs 资源
     * @param    $act 控制器
     * @access    public
     * @return     绝对路径，失败返回false
     * antl::getInstance()->load('file','file/gameconfig/212_interface.php');
     */
    public function load($__type, $rs, $act = '', $__data = array())
    {
        $fn = $this->pathFix($__type, $rs, $act);
        if (file_exists($fn)) {
            if ($__type == 'tpl') {
                if ($__data) extract($__data);
                $this->display_data = $__data;
                $displayParam = $__data;
            }
            include_once($fn);
            return $fn;
        }
        return false;
    }

    public function pathFix($__type, $rs, $act = '')
    {
        switch ($__type) {
            case 'act':
                return PATH_RS . $rs . DS . $act . '.php';
            case 'tpl':
                if ($rs === null)
                    return PATH_TPL . $act . '.php';
                else
                    return PATH_TPL . $rs . DS . $act . '.php';
            case 'request':
                return PATH_REQUEST . $rs . DS . $act . '.php';
            case 'file':
                return PATH_ROOT . $rs;
            default:
                return false;
        }
    }

    function loadTpl($rs, $act, $data = null)
    {
        return $this->load('tpl', $rs, $act, $data);
    }

    /**
     * autoLoad
     * @static
     * @param  $c
     * @return void
     */
    static function autoload($c)
    {
        $c = strtolower($c);
        $path = str_replace('_', '/', $c);
        if (file_exists(PATH_ROOT . $path . '.php'))
            require_once(PATH_ROOT . $path . '.php');
    }

    static function useAutoload()
    {
        spl_autoload_register(array('antl', 'autoload'));
    }
}
