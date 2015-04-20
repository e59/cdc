<?php

namespace Cdc\Sql;

use \Nette\Utils\Arrays as A;

class Select extends Statement {

    public $cols = array('*');

    public $join = array();

    public $group = array();

    public $having = array();

    public $order = array();

    public $limit = array();

    public $distinct = false;

    public $attach = array();

    public function distinct($distinct = true) {
        $this->distinct = $distinct;
        return $this;
    }

    public function cols($cols = array()) {
        $this->cols = $cols;
        return $this;
    }

    public function attach($attach = array()) {
        $this->attach = $attach;
        return $this;
    }

    public function join($join = array()) {
        $this->join = $join;
        return $this;
    }

    public function group($group = array()) {
        $this->group = $group;
        return $this;
    }

    public function having($having = array()) {
        $this->having = $having;
        return $this;
    }

    public function order($order = array()) {
        $this->order = $order;
        return $this;
    }

    public function limit($limit = array()) {
        $this->limit = $limit;
        return $this;
    }

    public function __toString() {
        $sql = 'select '
                . $this->buildDistinct()
                . $this->buildColumns()
                . $this->buildFrom()
                . $this->buildJoin()
                . $this->buildWhere()
                . $this->buildGroup()
                . $this->buildHaving()
                . $this->buildOrder()
                . $this->buildLimit();
        return $sql;
    }

    public function stmt($values = array()) {
        $params = array();
        $sql = 'select '
                . $this->buildDistinct()
                . $this->buildColumns()
                . $this->buildFrom()
                . $this->buildJoin()
                . $this->buildWhere(array(), $params)
                . $this->buildGroup(array(), $params)
                . $this->buildHaving(array(), $params)
                . $this->buildOrder()
                . $this->buildLimit();

        $stmt = $this->_bindValues($sql, $params, $values);

        $stmt->execute();
        return $stmt;
    }

    public function stmtLazy($values) {
        $params = array();
        $sql = 'select '
                . $this->buildDistinct()
                . $this->buildColumns()
                . $this->buildFrom()
                . $this->buildJoin()
                . $this->buildWhere(array(), $params)
                . $this->buildGroup(array(), $params)
                . $this->buildHaving(array(), $params)
                . $this->buildOrder()
                . $this->buildLimit();

        return $this->_bindLazy($sql, $params, $values);
    }

    public function buildDistinct() {
        if ($this->distinct) {
            return 'distinct ';
        }
    }

    public function buildOrder($params = array(), &$values = array()) {
        if (!$params) {
            $params = $this->order;
        }
        $result = '';

        foreach ($params as $k => $v) {
            if (is_numeric($k)) {
                $result .= $v . ', ';
            } else {
                $result .= $k . ' ' . $v . ', ';
            }
        }

        if ($result) {
            $result = ' order by ' . substr($result, 0, -2);
        }
        return $result;
    }

    public function buildGroup($params = array(), &$values = array()) {

        if (!$params) {
            $params = $this->group;
        }

        $result = '';
        foreach ($params as $k => $v) {
            $result .= ' ' . $v . ', ';
        }

        if ($result) {
            $result = ' group by ' . substr($result, 0, -2);
        }
        return $result;
    }

    public function buildHaving($params = array(), &$values = array()) {
        return $this->buildWhere($params, $values, 'having');
    }

    public function buildJoin($params = array(), &$values = array()) {
        // $join = array('teste' => array('left' => array(WHERE)))

        if (!$params) {
            $params = $this->join;
        }

        if (!$params) {
            return '';
        }

        $result = array();
        foreach ($this->join as $k => $v) {
            if (is_numeric($k)) {
                $join_type = 'inner';
                $table = $this->key($v);
            } else {
                $join_type = key($v);
                $table = $k;
            }
            $where = reset($v);
            $result[] = $join_type . ' join ' . $table . $this->buildWhere($where, $values, 'on');
        }
        if ($result) {
            return ' ' . implode(' ', $result);
        }
        return '';
    }

    public static function pageToOffset($page, $limit) {
        $limit = self::normalizeLimit($limit);
        if (!$limit) {
            throw new Cdc_Exception_Limit;
        }
        $page = self::normalizePage($page);
        return ($page - 1) * $limit;
    }

    public static function normalizeOffset($offset) {
        $offset = (int) $offset;
        if ($offset < 0) {
            $offset = 0;
        }
        return $offset;
    }

    public static function normalizePage($page) {
        $page = (int) $page;
        if (!$page) {
            $page = 1;
        }
        return $page;
    }

    public static function normalizeLimit($limit) {
        $limit = (int) $limit;
        if ($limit < 0) {
            $limit = 0;
        }
        return $limit;
    }

    public function buildLimit($params = array(), &$values = array()) {
        if (!$params) {
            $params = $this->limit;
        }
        if (!$params) {
            return '';
        }

        $result = array();

        if (isset($params['limit'])) {
            $limit = self::normalizeLimit($params['limit']);
            if ($limit) {
                $result[] = 'limit ' . $limit;
                if (isset($params['page'])) {
                    $offset = self::pageToOffset($params['page'], $limit);
                    if ($offset) {
                        $result[] = 'offset ' . $offset;
                    }
                }
            }
        }
        if (isset($params['offset'])) {
            $offset = self::normalizeOffset($params['offset']);
            if ($offset) {
                $result[] = 'offset ' . $offset;
            }
        }
        if ($result) {
            return ' ' . implode(' ', $result);
        }
        return '';
    }

    public function buildFrom($params = array(), &$values = array()) {
        if (!$params) {
            $params = $this->from;
        }
        $result = '';

        foreach ($this->from as $k => $v) {
            if (is_numeric($k)) {
                $result .= $v . ', ';
            } else {
                $result .= $k . ' as ' . $v . ', ';
            }
        }
        if ($result) {
            $result = ' from ' . substr($result, 0, -2);
        }
        return $result;
    }

    public function buildColumns($params = array(), &$values = array()) {
        if (!$params) {
            $params = $this->cols;
        }
        $result = '';
        foreach ($this->cols as $k => $v) {
            if (is_numeric($k)) {
                $result .= $v . ', ';
            } else {
                $result .= $k . ' as ' . $v . ', ';
            }
        }
        if ($result) {
            $result = substr($result, 0, -2);
        }
        return $result;
    }

}
