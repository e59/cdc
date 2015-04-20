<?php

namespace Cdc\Sql;

class Update extends Statement {

    public $cols = array();

    public function cols($cols = array()) {
        $this->cols = $cols;
        return $this;
    }

    public function __toString() {
        $sql = 'update '
                . $this->buildFrom()
                . $this->buildColumns()
                . $this->buildWhere();
        return $sql;
    }

    public function stmt($values = array()) {
        $params = array();
        $sql = 'update '
                . $this->buildFrom()
                . $this->buildColumns(array(), $params)
                . $this->buildWhere(array(), $params);

        $stmt = $this->_bindValues($sql, $params, $values);

        $stmt->execute();
        return $stmt;
    }

    public function buildFrom($params = array(), &$values = false) {
        if (!$params) {
            $params = $this->from;
        }
        $result = '';

        foreach ($this->from as $k => $v) {
            $result .= $v . ', ';
        }
        if ($result) {
            $result = substr($result, 0, -2);
        }
        return $result;
    }

    public function buildColumns($params = array(), &$values = false) {
        // @CodeCoverageIgnoreStart
        if (!$params) {
            $params = $this->cols;
        }
        // @CodeCoverageIgnoreEnd
        $result = '';
        foreach ($params as $k => $v) {
            $result .= $k . ' = ?, ';
            $values[] = $v;
        }
        if ($result) {
            $result = ' set ' . substr($result, 0, -2);
        }
        return $result;
    }

}
