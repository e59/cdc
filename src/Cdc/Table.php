<?php

class Cdc_Table {

    /**
     * Cria uma tabela
     *
     * @param <type> $caption Caption da tabela
     * @param <type> $lista Lista de resultados
     * @param <type> $pager Paginador, normalmente o resultado de Cdc_Pager::render
     * @param array $options Opções para a coluna opções, um array de callbacks que recebem a linha atual
     * @param array $formatters Formatadores de coluna, no formato coluna => callback que recebe a linha atual *E A LISTA*
     * @param array $provider Provedores de dados, no formato coluna => callback que recebe a linha atual E A LISTA e retorna a mesma linha com os dados adicionais
     * @param array $skip Colunas ignoradas na exibição
     * @param string $indexCheck Índice que será usado como valor num checkbox que será colocado em cada linha. O nome é o próprio índice.
     * @param string $indexTrClass Índice da coluna que será adicionada como classe ao TR atual
     * @param string $subTable Array que indica quais colunas serão exibidas na tabela (as colunas id e options são exibidas por padrão). Caso este array seja não-nulo, adiciona uma lista com uma subtabela abaixo da linha
     * @return string Tabela
     */
    public static function render($caption, $lista, $pager = null, array $options = array(), array $formatters = array(), array $provider = array(), array $skip = array(), $indexCheck = null, $indexTrClass = null, array $subTable = array()) {
        $item = array();
        $tbody = '<tbody>';
        $alt = 0;
        if ($lista instanceof PDOStatement) {
            $lista = $lista->fetchAll();
        }

        if (!$lista) {
            $lista = array();
        }

        if ($provider) {
            $provider = current($provider);
            $p_func = reset($provider);

            if (isset($provider[1])) {
                $p_params = $provider[1];
            } else {
                $p_params = array();
            }

            $lista = call_user_func_array($p_func, array($lista, $p_params));
        }

        $sample = array();
        $decrease_colspan = array();
        foreach ($lista as $offset => $item) {
            // @todo implementar subtable aqui
            $origin_item = $item;
            $item = ((empty($subTable)) ? $item : array_intersect_key($item, array_flip(array_merge(['id'], $subTable))));
            $sample = $item;

            if ($indexTrClass) {
                $trClass = 'trclass_' . $item[$indexTrClass] . ' ';
            } else {
                $trClass = '';
            }

            $styles = [
                $trClass . ($alt++ % 2 ? 'par' : 'impar'),
            ];

            if (!empty($subTable)) {
                $styles[] = 'tr-subtable';
            }

            $tbody .= '<tr class="' . implode(' ', $styles) . '">';

            if ($indexCheck) {
                $index = $indexCheck;
                // $tbody .= '<td class="check"><label class="checkbox"><input type="checkbox" class="checkbox" name="' . $index . '[]" value="' . $item[$index] . '"></label></td>';
                //$tbody .= '<td>&nbsp;</td>';
            }

            $options_html = '';
            if ($options) {
                $options_html = '<td class="options">';

                $o_func = reset($options);
                if (isset($options[1])) {
                    $o_params = $options[1];
                } else {
                    $o_params = array();
                }

                $options_callback_result = call_user_func_array($o_func, array($origin_item, $lista, $o_params));

                if (false === $options_callback_result) {
                    continue;
                }

                $options_html .= $options_callback_result;

                $options_html .= '</td>';
            }

            if (array_key_exists('massive', $options)) {
                $options_massive_html = '<td class="chck-options"> <input name="' . $index . '[]" type="checkbox" value="' . $item[$index] . '"> </td>';
                $tbody .= $options_massive_html;
            }

            foreach ($item as $key => $value) {
                $cell = '';

                if (false !== array_search($key, $skip)) {
                    continue;
                }

                if (isset($formatters[$key])) {
                    $f_func = reset($formatters[$key]);

                    if (isset($formatters[$key][1])) {
                        $f_params = $formatters[$key][1];
                    } else {
                        $f_params = array();
                    }

                    $cell = call_user_func_array($f_func, array($item, $lista, $key, $f_params));
                } else {
                    if (!array_key_exists($key, $item)) {
                        continue;
                    }

                    if (is_array($item[$key])) {
                        $decrease_colspan[$key] = true;
                        unset($sample[$key]);
                    } else {
                        $cell = '<td class="' . $key . '"><div>' . $item[$key] . '</div></td>';
                    }
                }
                $tbody .= $cell;
            }

            $tbody .= $options_html . '</tr>';

            if (!empty($subTable)) {
                $colspan_sub = count($subTable) + ($options ? 1 : 0) + (key_exists('massive', $options) ? 1 : 0);

                $unique = array_merge(array_diff_key($lista[$offset], array_flip($subTable)), array_diff_key(array_flip($subTable), $lista[$offset]));

                $tbody .= '<tr style="display: none" class="subtable"><td colspan="' . $colspan_sub . '">';
                $tbody .= self::renderHorizontal(null, $unique, $formatters, $provider, $skip, ['table-horizontal', 'table', 'table-condensed', 'table-bordered', 'subtable-subtable']);
                $tbody .= '</td></tr>';
            }
        }
        $tbody .= '</tbody>';

        $colspan = count($item) - count($decrease_colspan);

        $options_massive = array();
        $options_massive_html = '';
        if (array_key_exists('massive', $options)) {
            $options_massive = $options['massive'];

            $o_func = reset($options_massive);

            if (isset($options_massive[1])) {
                $o_params = $options_massive[1];
            } else {
                $o_params = array();
            }

            $options_callback_result = call_user_func_array($o_func, array($lista, $o_params));

            if (false === $options_callback_result) {
                continue;
            }

            $options_massive_html .= $options_callback_result;
        }

        $thead = '<thead><tr>';
        if (!empty($options_massive)) {
            $thead .= '<th class="chck-options"> <input type="checkbox" id="chckall" name="chck-options[]"> </th>';
        }

        if ($indexCheck) {
            $colspan++;
            // $thead .= '<th><input class="checkbox select_all" type="checkbox" rel="tooltip" title="Alternar seleção das caixas"></th>';
            //$thead .= '<th>&nbsp;</th>';
        }
        foreach ($sample as $key => $ignore_me) {
            if (false !== array_search($key, $skip)) {
                continue;
            }
            $thead .= '<th class="' . $key . '">' . label($key) . '</th>';
        }
        if (!empty($options)) {

            $thead .= '<th class="th-options">Opções</th>';
            $colspan++;

        }

        $thead .= '</tr></thead>';

        $tfoot = '';
        if ($pager) {
            $tfoot = '<tfoot><tr><td colspan="' . $colspan . '">' . $pager . '</td></tr></tfoot>';
        } else {
            $tfoot = '';
        }

        $class = [
            'table',
            'table-striped',
            'table-bordered',
            'table-hover',
        ];

        if (!empty($subTable)) {
            $class[] = 'table-subtable';
        }

        $table_id = sha1($tbody);
        return '<table id="' . $table_id . '" class="' . implode(' ', $class) . '">' . $options_massive_html . '<caption>' . $caption . '</caption>' . $thead . $tfoot . $tbody . '</table>';
    }

    /**
     * Cria uma tabela
     *
     * @param <type> $caption Caption da tabela
     * @param <type> $lista Lista de resultados
     * @param array $formatters Formatadores de coluna, no formato coluna => callback que recebe a linha atual *E A LISTA*
     * @param array $providers Provedores de dados, no formato coluna => callback que recebe a linha atual E A LISTA e retorna a mesma linha com os dados adicionais
     * @param array $skip Colunas ignoradas na exibição
     * @return string Tabela
     */
    public static function renderHorizontal($caption, $lista, array $formatters = array(), array $provider = array(), array $skip = array(), array $class = array()) {

        $class = (empty($class) ? ['table-horizontal', 'table', 'table-striped', 'table-bordered', 'table-condensed'] : $class);

        $tbody = '<tbody>';
        $alt = 0;
        if ($lista instanceof PDOStatement) {
            $lista = $lista->fetchAll();
        }

        if (!$lista) {
            $lista = array();
        }

        if ($provider) {
            $provider = current($provider);
            $p_func = reset($provider);

            if (isset($provider[1])) {
                $p_params = $provider[1];
            } else {
                $p_params = array();
            }

            $lista = call_user_func_array($p_func, array($lista, $p_params));
        }

        foreach ($lista as $key => $value) {

            $tbody .= '<tr class="' . ($alt++ % 2 ? 'par' : 'impar') . '">';

            $cell = '';
            if (false !== array_search($key, $skip)) {
                continue;
            }

            if (isset($formatters[$key])) {
                $f_func = reset($formatters[$key]);

                if (isset($formatters[$key][1])) {
                    $f_params = $formatters[$key][1];
                } else {
                    $f_params = array();
                }

                $cell = call_user_func_array($f_func, array($lista, array($lista), $key, $f_params));
            } else {
                $cell = '<td class="' . $key . '">' . $value . '</td>';
            }
            $tbody .= '<th>' . label($key) . '</th>' . $cell;
            $tbody .= '</tr>';
        }

        $tbody .= '</tbody>';

        $table_id = sha1($tbody);
        return '<table id="' . $table_id . '" class="' . implode(' ', $class) . '"><caption class="la-bel">' . $caption . '</caption>' . $tbody . '</table>';
    }

}
