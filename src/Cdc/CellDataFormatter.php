<?php

namespace Cdc;

class CellDataFormatter {

    public static function mask($row, $rowset, $index, $args) {
        return '<td class="' . $index . '">' . mask($args[0], $row[$index]) . '</td>';
    }

    public static function boolean($row, $rowset, $index) {
        return '<td class="' . $index . '">' . ($row[$index] ? '<span class="label label-success">Sim</span>' : '<span class="label label-danger">Não</span>') . '</td>';
    }

    public static function phone($row, $rowset, $index) {
        return '<td class="' . $index . '">' . get_masked_phone($row[$index]) . '</td>';
    }

    public static function url($row, $rowset, $index, $args) {
        $text = $url = f($row, $index);

        // option 1: label
        $label = isset($args[0]) ? $args[0] : false;
        if (is_array($label)) {
            $__text = array_shift($label);
            $__type = array_shift($label);
            if (!$__type)
                $__type = 'column';
            if ($__text) {
                $text = $__type == 'static' ? $__text : $row[$__text];
            }
        }

        // option 2: icon
        $icon = f($args, 1);
        if ($icon) {
            $icon = '<i class="' . $icon . '"></i>';
        } else {
            $icon = '';
        }

        // option 3: target
        $target = f($args, 2);
        if (!$target)
            $target = '_top';

        $link = '<a href="' . $url . '" target="' . $target . '">' . $text . ' ' . $icon . '</a>';
        if (!$url)
            $link = '';

        return '<td class="' . $index . '">' . $link . '</td>';
    }

    public static function shortenName($row, $rowset, $index, $args) {
        $arg_max = f($args, 0);
        $default_max = 3;
        $maximo = $arg_max ? $arg_max : $default_max;

        $nome = $row[$index];

        return '<td class="' . $index . '">' . shorten($nome, $maximo) . '</td>';
    }

    public static function arrayValue($row, $rowset, $index, $args) {
        if (!isset($args[0][$row[$index]])) {
            $value = '&nbsp;';
        } else {
            $value = $args[0][$row[$index]];
        }
        if (!isset($args[1][$row[$index]])) {
            return '<td class="' . $index . '">' . $value . '</td>';
        } else {
            return '<td class="' . $index . '"><span class="label label-' . $args[1][$row[$index]] . '">' . $value . '</span>';
        }
    }

    public static function formatMappedArray($row, $rowset, $index, $args) {
        if (array_key_exists(1, $args)) {
            $separator = $args[1];
        } else {
            $separator = '; ';
        }

        $map = $args[2];

        if ($row[$index]) {
            $titles = Cdc_ArrayHelper::mappedPluck(current($row[$index]), $args[0], $map);
            $data = implode($separator, $titles);
        } else {
            $data = '';
        }


        return '<td class="' . $index . '">' . $data . '</td>';
    }

    public static function formatArray($row, $rowset, $index, $args) {
        if (array_key_exists(1, $args)) {
            $separator = $args[1];
        } else {
            $separator = '; ';
        }
        if ($row[$index]) {
            $titles = Cdc_ArrayHelper::pluck(current($row[$index]), $args[0]);
            $data = implode($separator, $titles);
        } else {
            $data = '';
        }

        return '<td class="' . $index . '">' . $data . '</td>';
    }

    /**
     * Args: format
     *
     */
    public static function formatDate($row, $rowset, $index, $args) {
        $data = '';
        if (!is_null($row[$index])) {
            $data = date(current($args), strtotime(str_replace('/', '-', $row[$index])));
        }
        return '<td class="' . $index . '">' . $data . '</td>';
    }

    /**
     * Args: prefix, thousand, decimal
     *
     */
    public static function formatNumber($row, $rowset, $index, $args) {
        if (isset($args[0])) {
            $prefix = $args[0];
        } else {
            $prefix = '';
        }
        if (isset($args[1])) {
            $decimals = $args[1];
        } else {
            $decimals = 2;
        }
        if (isset($args[2])) {
            $dec_point = $args[2];
        } else {
            $dec_point = ',';
        }

        if (isset($args[3])) {
            $thousands_sep = $args[3];
        } else {
            $thousands_sep = '.';
        }

        if (isset($args[4])) {
            $suffix = $args[4];
        } else {
            $suffix = '';
        }

        if (isset($args[5])) {
            $align = $args[5];
        } else {
            $align = 'left';
        }

        $data = $prefix . number_format($row[$index], $decimals, $dec_point, $thousands_sep) . $suffix;

        return '<td class="' . $index . '" align="' . $align . '">' . $data . '</td>';
    }

    public static function formatImage($row, $rowset, $index, $args) {
        $valid_img_formats = array(
            'jpg', 'gif', 'png', 'jpeg'
        );
        if (@$file_infos = getimagesize(C::$upload_abs . $row[$index])) {
            foreach ($valid_img_formats as $valid_type) {
                if (preg_match('/' . $valid_type . '/', $file_infos['mime'])) {
                    $row['name'] = (isset($args['title']) ? $row[$args['title']] : $row['name']);
                    return '<td><a href="' . $args[0] . $row[$index] . '" class="preview" data-rel="bootbox" title="' . $row['name'] . '"><img style="height: ' . $args[1] . 'px; width:' . $args[2] . 'px" src="' . $args[0] . $row[$index] . '"</td></a>';
                }
            }
            return '<td><img src="http://placehold.it/40x40"></td>';
        } else {
            return '<td><img src="http://placehold.it/40x40"></td>';
        }
    }

    public static function formatImageAttachment($row, $rowset, $index, $args) {
        $valid_img_formats = array(
            'jpg', 'gif', 'png', 'jpeg'
        );

        $arquivo = null;

        if (is_array($row[$index]) && $row[$index]) {
            $_row = Cdc_ArrayHelper::current($row[$index], 2);
            $arquivo = C::$upload_abs . $_row['regiao'] . DIRECTORY_SEPARATOR . $_row['nome'];
            $preview_path = C::$upload . $_row['regiao'] . DIRECTORY_SEPARATOR . '__thumbnail__' . DIRECTORY_SEPARATOR . $_row['nome'];
            ;
            $arquivo_path = C::$upload . $_row['regiao'] . DIRECTORY_SEPARATOR . $_row['nome'];
            ;
        }

        if (!$arquivo || !is_file($arquivo)) {
            return '<td><img src="http://placehold.it/40x40"></td>';
        }

        if ($file_infos = getimagesize($arquivo)) {
            foreach ($valid_img_formats as $valid_type) {
                if (preg_match('/' . $valid_type . '/', $file_infos['mime'])) {
                    $row['name'] = (isset($args['title']) ? $row[$args['title']] : $_row['titulo']);
                    return '<td><a href="' . f($args, 0) . $arquivo_path . '" class="preview" data-rel="bootbox" title="' . $_row['titulo'] . '"><img style="height: ' . f($args, 1) . 'px; width:' . f($args, 2) . 'px" src="' . f($args, 0) . $preview_path . '"</td></a>';
                }
            }
            return '<td><img src="http://placehold.it/40x40"></td>';
        }
    }

}
