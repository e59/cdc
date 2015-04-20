<?php

namespace Cdc\Sql;

class Parameter {

    public $index;

    public $value = null;

    public $template = null;

    public function __construct($index, $template = null, $value = null) {
        $this->index = $index;
        $this->template = $template;
        $this->value = $value;
    }

    public function getPlaceholderTemplate() {
        if (null === $this->template) {
            return '?';
        }

        return sprintf($this->template, '?');
    }

    public function getValue($values) {
        if (array_key_exists($this->index, $values)) {
            return $values[$this->index];
        }

        return $this->value;
    }

}
