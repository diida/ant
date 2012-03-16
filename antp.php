<?php
/**
 * 模板类，用于视图展示
 * antp
 * @author diida
 */
class antp
{
    public $act;
    public $rs;
    public $a;

    function __construct($rs, $act)
    {
        $this->init($rs, $act);
    }

    function init($rs, $act)
    {
        $this->rs = $rs;
        $this->act = $act;
    }

    function loadData($a)
    {
        $this->a = $a;
    }

    function sDisplay()
    {
        ob_start();
        $this->loadTpl();
        $str = ob_get_contents();
        ob_end_clean();
        return $str;
    }

    function loadTpl()
    {
        if (!antl::getInstance()->load('tpl', $this->rs, $this->act, $this->a)) {
            ant::E('TEMPLETE_NOT_FOUND', array('rs' => $this->rs, 'act' => $this->act));
            return false;
        }
        return true;
    }

    function display()
    {
        return $this->loadTpl();
    }

    /**
     * 用于框架本身打印调试信息
     * @static
     * @param string $type
     * @param string $title
     * @param string $info
     * @param string $detail
     * @return void
     */
    static function info($type = "wrong", $title = "Ant 内部错误", $info = "", $detail = "")
    {
        include(PATH_ANT . 'tpl/info.php');
    }
}
