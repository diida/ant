<?php
namespace ant\plugins\db;

class db
{
    protected static $instance = array();
    /**
     * @var \PDO $pdo
     */
    public $pdo;
    public $lastQueryStat;
    /**
     * @var self $obj
     */
    protected $obj;

    //用于获取mypdo实例；
    /**
     * @param bool|string $key
     * @param string $fileName
     * @return self
     */
    static function getInstance($key = false, $fileName = 'config', $type = 'mysql')
    {
        if (!$key) return false;
        $c = '\\' . $key . '\config\\' . $fileName;

        $key = $key . '_' . $fileName;
        if (!isset(self::$instance[$key])) {
            $a = $c::db();
            $c = '\\ant\plugins\\db\\' . $type;
            self::$instance[$key] = new $c($a);
        }

        return self::$instance[$key];
    }

    function __construct($a)
    {
        $this->config = $a;
    }

    function connect($db)
    {
    }

    //主动传入参数用于判断语句
    final function query()
    {
        $this->connect($this);
        $args = func_get_args();
        if (count($args) == 2 && is_array($args[1])) {
            $sql = $args[0];
            $args = $args[1];
        } else {
            $sql = array_shift($args);
        }

        $stmt = $this->pdo->prepare($sql);

        if (!$stmt) {
            if (defined('DEBUG')) {
                echo("Failed to prepare:" . $sql . "<br>");
                print_r($this->pdo->errorInfo());
                die();
            }
            return false;
        }

        if (!$stmt->execute($args)) {
            if (defined('DEBUG')) {
                echo("Failed to execute:" . $sql . "<br>");
                print_r($stmt->errorInfo());
                die();
            }
            $this->lastQueryStat = false;
            return false;
        }

        $this->lastQueryStat = true;
        return $stmt;
    }

    function getSuccess()
    {
        return $this->lastQueryStat;
    }

    final function select()
    {
        $args = func_get_args();
        $stmt = call_user_func_array(array($this, 'query'), $args);
        if ($stmt == false) {
            return array();
        }
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    //只取一条数据
    final function selectOne()
    {
        $args = func_get_args();
        $r = call_user_func_array(array($this, 'select'), $args);
        return (!empty($r) ? $r[0] : false);
    }

    //只取一条数据和它的第一列
    final function getOne()
    {
        $args = func_get_args();
        $r = call_user_func_array(array($this, 'selectOne'), $args);
        if ($r) {
            return array_shift($r);
        }
        return false;
    }

    function t($name)
    {
        return $this->t($name);
    }

    //插入数据 return bool || id
    final function insert()
    {
        $args = func_get_args();
        $stmt = call_user_func_array(array($this, 'query'), $args);
        if ($stmt == false) {
            return false;
        }
        return $this->pdo->lastInsertId();
    }

    //插入数据 return bool || id
    final function replace()
    {
        $args = func_get_args();
        $stmt = call_user_func_array(array($this, 'query'), $args);
        if ($stmt == false) {
            return false;
        }
        //replace 无论是否成功，都是返回成功
        return true;
    }


    //插入数据 return bool || id
    final function replaceEx($a, $t)
    {
        return $this->insertEx($a, $t, 'replace');
    }

    function insertEx($t, $a, $type = 'insert')
    {
        return $this->insertEx($t, $a, $type);
    }

    function updateEx($t, $a, $where, $params = array())
    {
        return $this->updateEx($t, $a, $where, $params);
    }

    final function delete()
    {
        $args = func_get_args();
        $stmt = call_user_func_array(array($this, 'query'), $args);
        return ($stmt ? $stmt->rowCount() : false);
    }

    final function update()
    {
        $args = func_get_args();
        $stmt = call_user_func_array(array($this, 'query'), $args);
        return ($stmt ? $stmt->rowCount() : false);
    }

    function begin()
    {
        $this->pdo->beginTransaction();
    }

    function commit()
    {
        $this->pdo->commit();
    }

    function rollback()
    {
        $this->pdo->rollBack();
    }
}