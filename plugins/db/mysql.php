<?php
namespace ant\plugins\db;

class mysql extends db
{
    public $connected = false;
    public $config = false;

    function connect($db)
    {
        if ($this->connected) return true;

        $a = $this->config;
        try {
            $this->pdo = new \PDO('mysql:host=' . $a['host'] . ';port=' . $a['port'] . ';dbname=' . $a['name'], $a['user'], $a['pass']);
            $this->pdo->setAttribute(\PDO::ATTR_CASE, \PDO::CASE_LOWER);
            $this->pdo->query("set names " . $a['charset']);
            $this->connected = true;
            $db->pdo = $this->pdo;
        } catch (\PDOException $e) {
            echo("Failed to connect to DB: " . $e->getMessage());
            die();
        }
    }

    function t($name)
    {
        if (isset($this->config['DB_PREFIX'])) {
            return '`' . $this->config['DB_PREFIX'] . "_$name`";
        } else {
            return "`$name`";
        }
    }

    function insertEx($t, $a, $type = 'insert')
    {
        if ($type != 'insert') {
            $type = 'replace';
        }

        if (!isset($a[0])) {
            $a = array($a);
        }
        $t = $this->t($t);

        $params = array();
        foreach ($a as $data) {
            foreach ($data as $v)
                $params[] = $v;
        }

        $vals = array_fill(0, count($a[0]), "?");
        $vals = array_fill(0, count($a), "(" . implode(",", $vals) . ")");
        $vals = implode(',', $vals);

        $keys = array_keys($a[0]);
        $sql = $type . " into $t(`" . implode("`,`", $keys) . "`) values $vals";

        array_unshift($params, $sql);
        return call_user_func_array(array($this, $type), $params);
    }

    function insertExSqlAndParams($a, $t, $type = 'insert')
    {
        if ($type != 'insert') {
            $type = 'replace';
        }

        if (!isset($a[0])) {
            $a = array($a);
        }
        $t = $this->t($t);

        $params = array();
        foreach ($a as $data) {
            foreach ($data as $v)
                $params[] = $v;
        }

        $vals = array_fill(0, count($a[0]), "?");
        $vals = array_fill(0, count($a), "(" . implode(",", $vals) . ")");
        $vals = implode(',', $vals);

        $keys = array_keys($a[0]);
        $sql = $type . " into $t(`" . implode("`,`", $keys) . "`) values $vals";
        return [$sql, $params];
    }

    //$t table name,
    function updateEx($t, $a, $where, $params = array())
    {
        $t = $this->t($t);
        $sql = "UPDATE $t SET ";
        $SetWord = '';

        foreach ($a as $k => $v) {
            if (is_numeric($k) && !empty($v)) {

                $SetWord .= $v . ',';
                unset($a[$k]);
                continue;
            }
            $SetWord .= "`$k`=? ";
            $SetWord .= ',';
        }
        if (!empty($where)) {
            $where = ' where ' . $where;
        }
        $SetWord = substr($SetWord, 0, strlen($SetWord) - 1);

        $sql = $sql . $SetWord . ' ' . $where;
        $a = array_merge($a, $params);
        array_unshift($a, $sql);
        return call_user_func_array(array($this, 'update'), $a);
    }

    /**
     * 读取某个表的所有字段名信息
     */
    function getColumnsName($table)
    {
        $sql = "select column_name from information_schema.COLUMNS where table_name = ?";
        $datas = $this->select($sql, $table);
        $names = array();
        foreach ($datas as $data) {
            $names[] = $data['column_name'];
        }
        return $names;
    }

    public function mkArrayCond($condArr)
    {
        $cond = [];
        $params = [];
        foreach ($condArr as $k => $v) {
            if (is_numeric($k)) {
                $cond[] = $v;

            } else {
                if (preg_match('/^\%.*\%$/', $v)) {
                    $cond[] = $k . ' like ?';
                } else {
                    $cond[] = $k . '=?';
                }
                $params[] = $v;
            }
        }

        $cond = implode(' and ', $cond);
        return [$cond, $params];
    }

    /**
     * 读取某个表的字段信息
     */
    public function readFields($tableName, $fields = 'column_name')
    {
        $db = $this->config['name'];
        $sql = "select $fields from information_schema.columns
            where table_schema = ? and table_name = ?";

        return $this->select($sql, $db, $tableName);
    }
}
