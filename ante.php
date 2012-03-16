<?php
/**
 * 处理错误的一个类
 * 将错误保存入一个堆栈进行管理方便追踪错误来源
 * 适合追踪逻辑错误
 * why not try...catch？
 * try...catch 不易封装
 * 有时候没有对错误进行记录，
 * 不是因为不知到错误可能发生,而是(忘了，麻烦，我就不想写)
 * @name            ante
 * @author          diida
 * @version         4.0
 */
class ante
{
    function __construct(&$errorStack = array())
    {
        $this->es = &$errorStack;
    }

    function getError($key = null)
    {
        $e = $this->getErrorInfo($key);
        if ($e)
            return $e['error'];
        return false;
    }

    function setError($error, $errno = '', $key = null)
    {
        if ($key == null) {
            $this->es[] = array('error' => $error, 'errno' => $errno);
        } else {
            $this->es[$key] = array('error' => $error, 'errno' => $errno);
        }
    }

    function &getErrorInfo($key = null)
    {
        $flag = false;
        if (empty($this->es)) return $flag;
        if ($key == null)
            return $this->es[count($this->es) - 1];
        else
            return $this->es[$key];
    }

    function getErrorStack()
    {
        return $this->es;
    }

    function formatErrorStack($type = 'html', $withKey = true)
    {
        $w = "\n";
        $s = '';
        foreach ($this->es as $k => $e) {
            if ($e['errno']) {
                $s = "Errno:{$e['errno']} - {$e['error']}{$w}" . $s;
            } else {
                $s = "{$e['error']}{$w}" . $s;
            }

            if ($withKey) {
                $s = "[ $k ] " . $s;
            }
        }

        if ($type == 'html') {
            $s = htmlspecialchars($s);
            return str_replace("\n", '<br/>', $s);
        }

        return $s;
    }

    function __toString()
    {
        return $this->formatErrorStack();
    }

    function printErrorStack()
    {
        antp::info('wrong', 'Ant 内部错误', '输出错误栈', $this->formatErrorStack('none'));
    }
}
