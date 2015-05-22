<?php

namespace Cdc;

use \Cdc\Definition as D;

class Form {

    protected $_def;
    protected $_options;
    protected $_input;
    protected $_widgets;
    protected $_fm;
    protected $template;
    public $quote_mode = ENT_QUOTES;
    public static $defaultTemplate = null;

    public function __construct($def = array(), $options = array(), $input = array(), $quote_mode = ENT_QUOTES) {
        $this->_def = $def;
        $this->_options = $options;
        $this->_input = $input;
        $this->quote_mode = $quote_mode;
        $this->_buildWidgets($this->_def, $options, $input);
    }

    public function setTemplate($template) {
        $this->template = $template;
    }

    public function getTemplate() {
        return $this->template;
    }

    /**
     * Não lembro o motivo desta função está aqui, mas para todos os propósitos, deve ser igual e ter a mesma assinatura de required()
     *
     * @param array $def Definition
     * @param type $requiredText Texto para o indicador de required
     * @return string
     */
    public static function required($def, $requiredText = '<span class="required_token"> *</span>') {
        return required($def, $requiredText);
    }

    protected function _buildWidgets($def = array(), $_options = array(), $input = array()) {
        $widgets = array();
        foreach ($def as $k => $v_full) {
            if (!array_key_exists(D::TYPE_WIDGET, $v_full)) {
                continue;
            }

            if (!array_key_exists('attributes', $v_full[D::TYPE_WIDGET])) {
                $v_full[D::TYPE_WIDGET]['attributes'] = array();
            }
            $v = $v_full[D::TYPE_WIDGET]['attributes'];

            if (isset($v_full[D::TYPE_WIDGET]['callback'])) {
                $v['callback'] = $v_full[D::TYPE_WIDGET]['callback'];
            }

            if (isset($v_full[D::TYPE_WIDGET]['options'])) {
                $v['options'] = $v_full[D::TYPE_WIDGET]['options'];
            }

            $v['name'] = $k;
            if (!isset($v['id'])) {
                $v['id'] = $k;
            }
            $v['id'] = str_ireplace(array('[', ']'), array('_', ''), $v['id']);
            if (!isset($v['class'])) {
                $v['class'] = '';
            }

            if (array_key_exists('widget', $v_full[D::TYPE_WIDGET])) {
                $v['type'] = $v_full[D::TYPE_WIDGET]['widget'];
            }

            if (!isset($v['type'])) {
                $v['type'] = 'text';
            } elseif ($v['type'] == 'none') {
                continue;
            }

            // ajustes para que o mesmo formulário sirva para fazer busca
            if (isset($_options['search_form'])) {
                $_realNameSuffix = '';
                if (in_array($v['type'], array('textarea', 'rich'))) {
                    $v['type'] = 'text';
                } elseif (in_array($v['type'], array('multiselect'))) {
                    $_realNameSuffix = '[]';
                }
                unset($v['required']);
                $v['_realName'] = $_options['search_form'] . '[' . $v['name'] . ']' . $_realNameSuffix;
            } else {
                $v['_realName'] = $v['name'];
            }

            $v['value'] = self::obterValor($v, $this->_input, $v_full);

            // Adicionar o form-control do bootstrap em todos os widgets, exceto os inline e ocultos
            if (!in_array($v['type'], array('file', 'multifile', 'hidden', 'boolean', 'checkboxes', 'radio', 'textlabel'))) {
                $v['class'] .= ' form-control';
            }

            switch ($v['type']) {
                case 'datetime':
                    $v['class'] .= ' datetime';
                    $v['type'] = 'text';
                    $widgets[$k] = '<input ' . $this->_attribs($v) . '>';
                    break;
                case 'date':
                    $v['class'] .= ' date';
                    $v['type'] = 'text';
                    $widgets[$k] = '<input ' . $this->_attribs($v) . '>';
                    break;
                case 'password':
                    $v['class'] .= ' password';
                    unset($v['value']);
                    $_attrs = $this->_attribs($v, true);
                    $widgets[$k] = '<input ' . $_attrs['html'] . '>';
                    break;
                case 'email':
                case 'number':
                case 'text':
                    $v['class'] .= ' text';
                    if (isset($v['maxlength'])) {
                        if ($v['maxlength'] >= 100) {
                            $v['class'] .= ' large';
                        } elseif ($v['maxlength'] >= 25) {
                            $v['class'] .= ' medium';
                        }
                    }
                case 'hidden':
                    $widgets[$k] = '<input ' . $this->_attribs($v) . '>';
                    break;
                case 'multifile':
                    $v['type'] = 'file';
                    $v['name'] = $v['name'] . '[]';
                    $v['class'] .= ' multifile';
                    $_attrs = $this->_attribs($v, true);
                    $widgets[$k] = '<input ' . $_attrs['html'] . '>';
                    break;
                case 'file':
                    $v['class'] .= ' singlefile';
                    $_attrs = $this->_attribs($v, true);
                    $widgets[$k] = '<input ' . $_attrs['html'] . '>';
                    break;
                case 'url':
                    $v['class'] .= ' text';
                    $v['class'] .= ' large';
                    $widgets[$k] = '<input ' . $this->_attribs($v) . '>';
                    break;

                case 'rich':
                    $v['class'] .= ' rich';
                case 'textarea':
                    unset($v['type']);
                    $attrs = $this->_attribs($v, true);
                    $widgets[$k] = '<textarea ' . $attrs['html'] . '>' . $attrs['value'] . '</textarea>';
                    break;
                case 'textlabel':
                    unset($v['type']);
                    $v['class'] .= ' textlabel';
                    $attrs = $this->_attribs($v, true);
                    $widgets[$k] = '<div ' . $attrs['html'] . '>' . $attrs['value'] . '</div>';
                    break;
                case 'boolean':
                    $valor = self::obterValor($v, $this->_input, $v_full);
                    if ($valor === 't' || $valor === true || $valor === 'true' || $valor === 1 || $valor === '1') { // gambiarra...
                        $v['checked'] = 'checked';
                    } else {
                        $valor = 'false';
                    }
                    $v['type'] = 'checkbox';
                    $vattribs = $this->_attribs($v, true);
                    unset($v['checked']);
                    $v['type'] = 'hidden';
                    $v['id'] .= '_hidden';
                    $vhattribs = $this->_attribs($v, true);
                    $widgets[$k] = '<input value="0" ' . $vhattribs['html'] . '>';
                    $widgets[$k] .= '<input value="1" ' . $vattribs['html'] . '>';
                    break;
                case 'select':
                    unset($v['type']);

                    if (isset($v['multiple'])) {
                        $v['name'] .= '[]';
                    }

                    $attrs = $this->_attribs($v, true);


                    $options = self::obterOpcoes($v);

                    $emptyOptionNotSelected = '';
                    $emptyOptionSelected = '';

                    $widgets[$k] = '<select ' . $attrs['html'] . '>';
                    if (!isset($v['required']) || !$v['required']) {
                        if (array_key_exists('empty_label', $v)) {
                            $emptySelectLabel = $v['empty_label'];
                        } else {
                            $emptySelectLabel = '[Escolha uma opção...]';
                        }

                        $emptyOptionNotSelected = '<option value="">' . label($emptySelectLabel) . '</option>';
                        $emptyOptionSelected = '<option value="" selected="selected">' . label($emptySelectLabel) . '</option>';
                        unset($v['required']);
                    } else {
                        if (array_key_exists('disabled_option', $v)) {
                            $emptyOptionNotSelected = '<option disabled="disabled"> Escolha uma opção </option>';
                            $emptyOptionSelected = '<option disabled="disabled" selected="selected"> Escolha uma opção </option>';
                        }
                    }
                    $optionsString = '';

                    $emptyOption = $emptyOptionSelected;

                    if (isset($v['multiple'])) {
                        $emptyOption = '';
                        if ($v['value']) {
                            $val = array_keys($v['value']);
                        } else {
                            $val = array();
                        }
                        foreach ($options as $opt => $label) {
                            $checked = '';
                            $valor = htmlspecialchars($opt, $this->quote_mode, 'UTF-8');
                            if ($val) {
                                $optIndex = array_search($opt, $val);
                                if (false !== $optIndex) {
                                    $checked = ' selected="selected"';
                                    // $hidden_fields .= '<input type="hidden" name="' . $v['name'] . '[previous]" value="' . $valor . '">';
                                }
                            }
                            $optionsString .= '<option value="' . $valor . '"' . $checked . '>' . $label . '</option>';
                        }
                    } else {
                        foreach ($options as $col => $colval) {
                            if ((string) $col === (string) $attrs['value']) {
                                $selected = ' selected="selected"';
                                $emptyOption = $emptyOptionNotSelected;
                            } else {
                                $selected = '';
                            }
                            $optionsString .= '<option value="' . $col . '"' . $selected . '>' . $colval . '</option>';
                        }

                    }
                    $widgets[$k] .= $emptyOption . $optionsString . '</select>';
                    break;
                case 'checkboxes':
                    $val = $v['value'];
                    unset($v['value']);

                    $v['type'] = 'checkbox';
                    $v['name'] .= '[]';
                    $options = self::obterOpcoes($v);
                    unset($v['options']);
                    unset($v['value']);
                    //$widgets[$k] = '<input type="hidden" value="" name="' . $v['name'] . '">';
                    $widgets[$k] = '';

                    $id = false;
                    if (isset($v['id'])) {
                        $id = $v['id'];
                        unset($v['id']);
                    }

                    $opt_count = 1;
                    foreach ($options as $opt => $label) {
                        $checked = '';
                        if (is_null($val) !== true) {
                            $optIndex = array_search($opt, $val);
                            if (false !== $optIndex) {
                                $checked = ' checked="checked"';
                            }
                        }
                        $attribs = $this->_attribs($v, true);
                        $widgets[$k] .= '<label class="checkbox">' . '<input ' . ($id ? 'id="' . $id . '-' . $opt_count . '"' : '') . ' value="' . htmlspecialchars($opt, $this->quote_mode, 'UTF-8') . '" ' . $attribs['html'] . $checked . '>' . $label . '</label>';
                        $opt_count++;
                    }
                    break;
                case 'multiselect':
                    if ($v['value']) {
                        $val = array_keys($v['value']);
                    } else {
                        $val = array();
                    }

                    unset($v['type']);
                    unset($v['value']);

                    $options = self::obterOpcoes($v);
                    unset($v['options']);
                    unset($v['value']);

                    $v['name'] .= '[]';

                    $v['multiple'] = 'multiple';

                    $v['class'] .= ' multiselect';

                    $attrs = $this->_attribs($v, true);

                    //$widgets[$k] = '<input type="hidden" value="" name="' . $v['name'] . '">';
                    $hidden_fields = '';
                    $widgets[$k] = '<select ' . $attrs['html'] . '>';
                    foreach ($options as $opt => $label) {
                        $checked = '';
                        $valor = htmlspecialchars($opt, $this->quote_mode, 'UTF-8');
                        if ($val) {
                            $optIndex = array_search($opt, $val);
                            if (false !== $optIndex) {
                                $checked = ' selected="selected"';
                                // $hidden_fields .= '<input type="hidden" name="' . $v['name'] . '[previous]" value="' . $valor . '">';
                            }
                        }
                        $widgets[$k] .= '<option value="' . $valor . '"' . $checked . '>' . $label . '</option>';
                    }
                    $widgets[$k] .= '</select>';

                    ob_start();
                    ?>
                    <div class="element clearfix">
                        <div class="row">
                            <div class="col-sm-0">
                            </div>
                            <div class="col-sm-6">
                                <input id="" type="button" class="btn btn-info selectall" value="Adicionar Todos">
                                <input id="" type="button" class="btn btn-danger deselectall" value="Remover Todos">
                            </div>
                        </div>
                    </div>
                    <?php
                    $selectDeselectAll = ob_get_clean();

                    $widgets[$k] = $hidden_fields . $widgets[$k] . $selectDeselectAll;
                    break;

                /*    */
                case 'radio':
                    $val = $v['value'];

                    unset($v['value']);

                    $v['type'] = 'radio';
                    // $v['name'] .= '[]';
                    $options = self::obterOpcoes($v);

                    if (!f($v, 'required')) {
                        $options = array_merge(array('' => 'Não'), $options);
                    }

                    unset($v['options']);
                    unset($v['value']);
                    $widgets[$k] = '<input type="hidden" value="" name="' . $v['name'] . '">';

                    $id = false;
                    if (isset($v['id'])) {
                        $id = $v['id'];
                        unset($v['id']);
                    }

                    $opt_count = 1;
                    foreach ($options as $opt => $label) {
                        $checked = '';

                        //if( $val )
                        //{
                        if ((string) $opt === (string) $val) {
                            $checked = ' checked="checked"';
                        }
                        //}
                        $attribs = $this->_attribs($v, true);
                        $widgets[$k] .= '<div class="radio radioitem"><label>' . '<input ' . ($id ? 'id="' . $id . '-' . $opt_count . '"' : '') . ' value="' . htmlspecialchars($opt, $this->quote_mode, 'UTF-8') . '" ' . $attribs['html'] . $checked . '>' . $label . '</label></div>';
                        $opt_count++;
                    }
                    break;
                case 'integer':
                    $v['class'] .= ' integer';
                    $v['type'] = 'text';

                    $widgets[$k] = '<input ' . $this->_attribs($v) . '/>';
                    break;
                case 'money':
                    $v['class'] .= ' money';
                    $v['type'] = 'text';

                    $widgets[$k] = '<input ' . $this->_attribs($v) . '/>';
                    break;
                case 'autocomplete':
                    $v['class'] .= ' hidden';
                    $v['name'] .= '[]';
                    $v['type'] = 'hidden';
                    $orig_values = array();

                    if (array_key_exists('value', $v)) {
                        $orig_values = $v['value'];
                        $val = implode(', ', $v['value']);
                        unset($v['value']);
                        $v['value'] = $val;
                    }

                    $options = self::obterOpcoes($v);
                    unset($v['options']);

                    $source = '';
                    $src_script = '<ul class="source" style="display: none;">';
                    foreach ($options as $opt => $label) {
                        $src_script .= '<li><span class="value">' . $opt . '</span><span class="label">' . $label . '</span></li>';
                        if (in_array($opt, $orig_values)) {
                            $source .= $label . ', ';
                        }
                    }
                    $src_script .= '</ul>';

                    $widgets[$k] = '<input ' . $this->_attribs($v) . '/>' .
                            '<input type="text" name="' . $k . '" value="' . $source . '" id="' . $k . '_labels" class="text medium autocomplete" />' .
                            $src_script;
                    break;
            }
        }
        $this->_widgets = $widgets;
    }

    protected function _reBuildWidgets() {
        $def = $this->_def;
        $options = $this->_options;
        $input = $this->_input;
        $this->_buildWidgets($def, $options, $input);
    }

    public static function obterOpcoes($v) {
        if (!isset($v['options'])) {
            return call_user_func_array($v['callback'][0], $v['callback'][1]);
        }
        return $v['options'];
    }

    public static function obterValor($attribs, $input, $def, $encode = true) {
        $value = null;

        //@TODO: Deixar isto mais útil
        if (isset($attribs['value'])) {
            $value = $attribs['value'];
        } elseif (isset($attribs['default'])) {
            $value = $attribs['default'];
        }

        if (array_key_exists($attribs['name'], $input)) {
            // @TODO: Resolver este problema de impedância infinita de dados
            $value = $input[$attribs['name']];

            if (is_array($value) && $value) {
                $v = current($value);

                if (is_array($v)) {
                    $value = array_combine(array_keys($value), array_keys($value));
                } else {
                    $value = array_combine($value, $value);
                }
            }
        }

        return $value;
    }

    protected function _attribs($attribs, $troncho = false) {

        $value = f($attribs, 'value');
        unset($attribs['crud']);
        unset($attribs['n2n']);
        unset($attribs['value']);
        unset($attribs['unique']);
        unset($attribs['callback']);
        unset($attribs['options']);
        unset($attribs['table']);
        unset($attribs['filter']);
        unset($attribs['default']);
        unset($attribs['search']);
        unset($attribs['container_class']);
        unset($attribs['input_keys']);
        unset($attribs['empty_label']);
        unset($attribs['template']);
        unset($attribs['attachment_relation']);

        $html = '';


        if ($attribs['_realName'] && isset($this->_options['search_form']) && $this->_options['search_form']) {
            $attribs['name'] = $attribs['_realName'];
        }

        if (!$troncho) {
            $html .= 'value="' . htmlspecialchars($value, $this->quote_mode, 'UTF-8') . '" ';
        }

        unset($attribs['_realName']);

        foreach ($attribs as $k => $v) {
            $html .= $k . '="' . htmlspecialchars($v, $this->quote_mode, 'UTF-8') . '" ';
        }

        $html = substr($html, 0, -1);

        $result = array();
        if ($troncho) {
            $result['html'] = $html;
            if (!is_array($value)) {
                $result['value'] = htmlspecialchars($value, $this->quote_mode, 'UTF-8');
            } else {
                $result['value'] = $value;
            }

            return $result;
        }
        return $html;
    }

    public function render($template = null, $search = false) {
        if (array_key_exists('search_form', $this->_options) && $this->_options['search_form']) {
            $search = true;
        }

        $widgets = $this->_widgets;

        $def = $this->_def;

        $input = $this->_input;

        $options = $this->_options;
        if (!array_key_exists('submit_text', $options)) {
            $options['submitText'] = 'Enviar';
        } else {
            $options['submitText'] = $options['submit_text'];
        }

        if (array_key_exists('submitTemplate', $options)) {
            $submitTemplateFile = $options['submitTemplate'];
        } else {
            $submitTemplateFile = dirname(__FILE__) . '/Form/Template/Widget/submit.phtml';
        }

        $renderedWidgets = array();

        $legend = f($options, 'legend');

        extract($options);

        ob_start();
        include $submitTemplateFile;
        $submit = ob_get_clean();

        foreach ($widgets as $key => $widget) {
            if (isset($this->_def[$key]['widget']['widget'])) {
                $type = $this->_def[$key]['widget']['widget'];
            } else {
                $type = 'default';
            }


            if (array_key_exists('template', $this->_def[$key])) {
                $widgetTemplate = $this->_def[$key]['template'];
                if ($widgetTemplate === false) {
                    $renderedWidgets[$key] = $widget;
                } else {
                    ob_start();

                    include $widgetTemplate;

                    $renderedWidgets[$key] = ob_get_clean();
                }
            } else {
                $fn = dirname(__FILE__) . '/Form/Template/Widget/' . $type . '.phtml';
                $fnDefault = dirname(__FILE__) . '/Form/Template/Widget/default.phtml';

                if (file_exists($fn)) {
                    $tpl = $fn;
                } else {
                    $tpl = $fnDefault;
                }
                ob_start();
                include $tpl;
                $renderedWidgets[$key] = ob_get_clean();
            }
            $renderedWidgets[$key] .= PHP_EOL;
        }

        if (!$template) {
            $template = $this->getTemplate();
        }


        if (!$template || !file_exists($template)) {
            if ($search) {
                $template = dirname(__FILE__) . '/Form/Template/search.phtml';
            } else {
                $template = dirname(__FILE__) . '/Form/Template/default.phtml';
            }
        }
        ob_start();
        require $template;
        return ob_get_clean();
    }

}
