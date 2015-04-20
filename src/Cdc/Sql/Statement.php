<?php

namespace Cdc\Sql;

class Statement extends \Cdc\Pdo\Container {

    public $where = array();

    public $from = array();

    public $returning = array();

    public static function instance($pdo = null, $args = null) {
        return new static($pdo, $args);
    }

    public function returning($returning = array()) {
        $this->returning = $returning;
        return $this;
    }

    public function where($where = array()) {
        $this->where = $where;
        return $this;
    }

    public function from($from = array()) {
        $this->from = $from;
        return $this;
    }

    protected function _bindLazy($sql, $params, $values) {
        $tok = strtok($sql, '?');
        $pieces = array();
        $i = 1;
        $realParams = array();
        foreach ($params as $v) {
            $pieces[] = $tok;
            if (is_null($v)) {
                $realParams[$i++] = array($v, \PDO::PARAM_NULL);
                $pieces[] = '?';
            } elseif (is_bool($v)) {
                $realParams[$i++] = array($v, \PDO::PARAM_BOOL);
                $pieces[] = '?';
            } elseif ($v instanceof Parameter) {
                $realParams[$i++] = array($v->getValue($values), \PDO::PARAM_STR);
                $pieces[] = $v->getPlaceholderTemplate();
            } elseif ($v instanceof Select) {
                $bindResult = $v->stmtLazy($values);
                foreach ($bindResult['params'] as $_p) {
                    $realParams[$i++] = $_p;
                }
                $pieces[] = '(' . $bindResult['sql'] . ')';
            } else {
                $realParams[$i++] = array($v, \PDO::PARAM_STR);
                $pieces[] = '?';
            }
            $tok = strtok('?');
        }

        if ($pieces) {
            $pieces[] = trim(strrchr($sql, '?'), '?');
        } else {
            $pieces[] = $sql;
        }

        $finalSql = implode('', $pieces);

        return array('sql' => $finalSql, 'params' => $realParams);
    }

    protected function _bindValues($sql, $params, $values) {

        $result = $this->_bindLazy($sql, $params, $values);

        $stmt = $this->getPdo()->prepare($result['sql']);

        foreach ($result['params'] as $k => $v) {
            $stmt->bindValue($k, $v[0], $v[1]);
        }

        return $stmt;
    }

//    protected function _bindValues($stmt, $values)
//    {
//        foreach ($values as $k => $v)
//        {
//            if (is_null($v))
//            {
//                $stmt->bindValue(':' . $k, $v, PDO::PARAM_NULL);
//            }
//            elseif (is_bool($v))
//            {
//                $stmt->bindValue(':' . $k, $v, PDO::PARAM_BOOL);
//            }
//            else
//            {
//                $stmt->bindValue(':' . $k, $v);
//            }
//        }
//    }

    private function _where($params = array(), $root = null, &$values = array()) {
        $values = (array) $values;
        if (!$root) {
            $root = 'and';
        }

        if (count($params) == 1) {
            $_possible_root = trim(key($params));
            $value = reset($params);
            if ($this->_isRoot($_possible_root)) {
                $root = $_possible_root;
                $params = $value;
                $return = array();
                if (is_array($params)) {
                    foreach ($params as $kc => $kv) {
                        if (is_numeric($kc)) {
                            $kc = 'and';
                        }
                        $return[] = $this->_where(array($kc => $kv), $root, $values);
                    }
                } else {
                    $return[] = $value;
                }

                return '(' . implode(' ' . $root . ' ', $return) . ')';
            }
            if (is_array($value)) {
// empty array
                if (!$value) {
                    throw new \Cdc\Sql\Exception\EmptyListInCondition($_possible_root);
                }

                $values = array_merge($values, $value);
                return '(' . $_possible_root . ' (' . implode(', ', array_fill(0, count($value), '?')) . '))';
            } else {
                if (is_numeric($_possible_root)) {
                    return '(' . $value . ')';
                }
                $values[] = $value;
                return '(' . $_possible_root . ' ?)';
            }
        }

        $params = array($root => $params);

        return $this->_where($params, $root, $values);
    }

    private function _isRoot($value) {
        return in_array(strtolower($value), array('and', 'or'));
    }

    protected function buildWhere($params = array(), &$values = array(), $prefix = 'where') {
        $prefix = trim($prefix);

        if (!$params && isset($this->$prefix)) {
            $params = $this->$prefix;
        }

// @codeCoverageIgnoreStart
        if (!$params) {
            return '';
        }
// @codeCoverageIgnoreEnd

        $params = array_filter($params, array($this, 'filterWhere'));

        $where = $this->_where($params, null, $values);

        if ($where == '()') {
            return '';
        }

        if ($where) {
            $where = ' ' . $prefix . ' ' . $where;
        }

        return $where;
    }

    public function filterWhere($param) {
        return $param !== '';
    }

}
