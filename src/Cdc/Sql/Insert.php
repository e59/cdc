<?php

namespace Cdc\Sql;

class Insert extends Statement {

    public $cols = array();

    public function cols($cols = array()) {
        $this->cols = $cols;
        return $this;
    }

    public function __toString() {
        $sql = 'insert into '
                . $this->buildFrom()
                . $this->buildColumns();
        return $sql;
    }

    public function stmt($values = array()) {
        $params = array();
        $sql = 'insert into '
                . $this->buildFrom()
                . $this->buildColumns(array(), $params);

        $stmt = $this->_bindValues($sql, $params, $values);

        $stmt->execute();
        return $stmt;
    }

    public function buildFrom($params = array(), &$values = array()) {
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

    public function buildColumns($params = array(), &$values = array()) {
        if (!$params) {
            $params = $this->cols;
        }
        // @CodeCoverageIgnoreStart
        if (!$params) {
            return '';
        }
        // @CodeCoverageIgnoreEnd
        $result = '';

        $sample = current($params);

        if (is_array($sample)) {
            $vals = array();
            $keys = array_keys($sample);
            foreach ($params as $v) {
                $vals[] = '(' . implode(', ', array_fill(1, count($v), '?')) . ')';
                foreach ($v as $c) {
                    $values[] = $c;
                }
            }
            $vals = implode(', ', $vals);
        } else {
            $keys = array_keys($params);
            foreach ($params as $v) {
                $values[] = $v;
            }
            $vals = '(' . implode(', ', array_fill(1, count($params), '?')) . ')';
        }

        $result .= ' (' . implode(', ', $keys) . ') values ' . $vals;

        return $result;
    }

}
