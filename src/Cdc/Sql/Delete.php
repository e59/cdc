<?php

namespace Cdc\Sql;

class Delete extends Statement {

    public function __toString() {
        $sql = 'delete '
                . $this->buildFrom()
                . $this->buildWhere();
        return $sql;
    }

    public function stmt($values = array()) {
        $params = array();
        $sql = 'delete '
                . $this->buildFrom()
                . $this->buildWhere(array(), $params);
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
            $result = 'from ' . substr($result, 0, -2);
        }
        return $result;
    }

}
