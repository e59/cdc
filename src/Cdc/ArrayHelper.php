<?php

namespace Cdc;

class ArrayHelper {

    /**
     * Equivalente a função current() do php com um iterador de sequencia que realiza a operação n vezes
     * @param array $array O array a ser recuperado o ponteiro corrente
     * @param integer $repeat Quantidade de iterações da operação current
     * @return array O resultado será o conteúdo do endereço do fim das iterações do current
     */
    public static function current($array, $repeat = 1) {
        $c = $array;

        for ($x = 0; $x < $repeat; $x++) {
            $c = current($c);

            if (false == $c) {
                return array();
            }
        }

        return $c;
    }

    /**
     * Remove um determinado valor de um array
     * @param array $haystack array que será procurado e removido o índice
     * @param integer|string $needle valor a ser removido
     */
    public static function remove($needle, array $haystack) {
        $index = array_search($needle, $haystack);
        if (false !== $index) {
            unset($haystack[$index]);
        }
        return $haystack;
    }

    public static function flatten($array, $prefix = '') {
        $result = array();

        foreach ($array as $key => $value) {
            $new_key = $prefix . (empty($prefix) ? '' : '[') . $key . (empty($prefix) ? '' : ']');

            if (is_array($value)) {
                $result = array_merge($result, self::flatten($value, $new_key));
            } else {
                $result[$new_key] = $value;
            }
        }

        return $result;
    }

    public static function unflatten($array) {
        $keys = array_keys($array);
        $result = array();
        $vars = get_defined_vars();
        foreach ($array as $key => $value) {
            $varName = str_replace(array('[', ']'), array('[\'', '\']'), $key);
            eval("\$$varName = \$value;");
        }
        unset($value, $key, $varName);
        $result = array_diff_key(get_defined_vars(), $vars);
        unset($result['vars']);
        return $result;
    }

    public static function pluck($result, $key) {
        $return = array();
        foreach ($result as $k => $item) {
            $return[$k] = $item[$key];
        }

        return $return;
    }

    public static function mappedPluck($result, $key, $map) {
        $return = array();
        foreach ($result as $k => $item) {
            $return[$k] = $map[$item[$key]];
        }

        return $return;
    }

    /**
     * Dá um PDO::FETCH_KEY_PAIR
     * @param mixed $result
     * @param string $key nome da coluna
     * @return array
     */
    public static function keyPair($result, $key = null, $value = null) {
        if (!$result) {
            return array();
        }

        $first = reset($result);
        if (!is_array($first)) {
            return array($first => $result);
        }


        if (!$key) {
            $key = key($first);
        }

        $return = array();
        foreach ($result as $t) {
            if ($value) {
                $return[$t[$key]] = $t[$value];
                continue;
            }
            $return[$t[$key]] = $t;
        }
        return $return;
    }

    /**
     * array_merge_recursive does indeed merge arrays, but it converts values with duplicate
     * keys to arrays rather than overwriting the value in the first array with the duplicate
     * value in the second array, as array_merge does. I.e., with array_merge_recursive,
     * this happens (documented behavior):
     *
     * array_merge_recursive(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => array('org value', 'new value'));
     *
     * array_merge_recursive_distinct does not change the datatypes of the values in the arrays.
     * Matching keys' values in the second array overwrite those in the first array, as is the
     * case with array_merge, i.e.:
     *
     * array_merge_recursive_distinct(array('key' => 'org value'), array('key' => 'new value'));
     *     => array('key' => 'new value');
     *
     * Parameters are passed by reference, though only for performance reasons. They're not
     * altered by this function.
     *
     * @param array $array1
     * @param mixed $array2
     * @author daniel@danielsmedegaardbuus.dk
     * @deprecated Nette\Utils\Arrays::mergeTree
     * @return array
     */
    public static function array_merge_recursive_distinct(array &$array1, &$array2 = null) {
        $merged = $array1;

        if (is_array($array2))
            foreach ($array2 as $key => $val)
                if (is_array($array2[$key]))
                    $merged[$key] = (isset($merged[$key]) && is_array($merged[$key])) ? self::array_merge_recursive_distinct($merged[$key], $array2[$key]) : $array2[$key];
                else
                    $merged[$key] = $val;

        return $merged;
    }

    public static function arrayAsAttributes(array $haystack) {
        ob_start();
        if (!empty($haystack)) {
            foreach ($haystack as $key => $value) {
                echo $key . '="' . $value . '" ';
            }
        }
        return ob_get_clean();
    }

    public static function arrayRegroupedByKey($array, $key) {
        $return = array();

        foreach ($array as $item) {
            $return[$item[$key]][] = $item;
        }

        return $return;
    }

    public static function array_search2d_by_field($needle, $haystack, $field) {
        foreach ($haystack as $index => $innerArray) {
            if (isset($innerArray[$field]) && $innerArray[$field] == $needle) {
                $retorno = array(
                    'index' => $index,
                    'element' => $innerArray
                );
                return $retorno;
            }
        }
        return false;
    }

    /**
     * Retorna $source apenas com as colunas que estão em $orderedKeys,
     * na mesma ordem de $orderedKeys
     * @param array $source
     * @param array $orderedKeys
     */
    public static function orderedArrayIntersection($source, $orderedKeys) {
        $flip = array_flip($orderedKeys);
        $intersection = array_intersect_key($source, $flip);

        return array_merge($flip, $intersection);
    }

    public static function changeKeyName($array, $old_key, $new_key) {
        if (!(is_string($old_key) || is_integer($old_key))) {
            return $array;
        }

        if (!array_key_exists($old_key, $array)) {
            return $array;
        }

        $keys = array_keys($array);
        $keys[array_search($old_key, $keys)] = $new_key;

        return array_combine($keys, $array);
    }

    public static function changeSqlArray($array, $col_name, $new_col_name) {
        $index = array_search($col_name, $array);
        $array = self::changeKeyName($array, $index, $new_col_name);
        return $array;
    }

}
