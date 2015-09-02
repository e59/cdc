<?php

namespace Cdc\Definition;

use \Cdc\Definition as D;
use Nette\Utils\Arrays as A;

class MetadataFactory {

    public static function search(D $definition, $operation = null) {
        $oldOp = $definition->getOperation();
        if (!$operation) {
            $operation = DEFAULT_OPERATION;
        }
        
        $definition->setOperation($operation);

        $def = $definition->query(D::TYPE_WIDGET, D::TYPE_RELATION)->fetch();

        $result = array();

        foreach ($def as $key => $value) {
            $matches = self::hasTag($value, 'search');
            if ($matches) {
                if ($value['type'] == D::TYPE_RELATION) {
                    $value[D::TYPE_WIDGET]['widget'] = 'text';
                    $value[D::TYPE_WIDGET]['attributes']['class'] = 'search-general';
                } else {
                    $match = current($matches);
                    $tokens = array();
                    preg_match('#search\[(.*)\]#', $match, $tokens);
                    $term = end($tokens);
                    if ($term) {
                        $value['search-operator'] = $term;
                    }
                }

            if (self::hasTag($value, 'boolean')) {
                $value[D::TYPE_WIDGET]['widget'] = 'radio';
                $value[D::TYPE_WIDGET]['options'] = array(
                    '' => '[Não filtrar]',
                    '1' => 'Sim',
                    '0' => 'Não',
                );
                $value[D::TYPE_WIDGET]['attributes']['default'] = '';
                $result[$key] = $value;
            }



                $result[$key] = $value;
                continue;
            }

            if (self::hasTag($value, '^related-.*')) {
                $value[D::TYPE_WIDGET]['widget'] = 'none';
                $result[$key] = $value;
            }
        }

        $definition->setOperation($oldOp);
        return $result;
    }

    public static function form(D $definition, $operation = null) {
        $oldOp = $definition->getOperation();
        if ($operation) {
            $definition->setOperation($operation);
        }
        $def = $definition->query(D::TYPE_WIDGET)->fetch();
        foreach ($def as $key => $value) {
            if (self::hasTag($value, 'metadata')) {
                unset($def[$key]);
            }
        }

        $definition->setOperation($oldOp);
        return $def;
    }

    public static function rules(D $definition, $operation = null) {
        $oldOp = $definition->getOperation();
        if ($operation) {
            $definition->setOperation($operation);
        }
        $def = $definition->query(D::TYPE_RULE)->fetch();

        $definition->setOperation($oldOp);
        return $def;
    }

    public static function order(D $definition, $operation = null) {
        $oldOp = $definition->getOperation();
        if ($operation) {
            $definition->setOperation($operation);
        }
        $def = $definition->query(D::TYPE_COLUMN)->fetch();
        $result = array();

        $last = array();
        foreach ($def as $key => $value) {
            if ($r = self::hasTag($value, '^\d+ order asc')) {
                $result[$key] = 'asc';
            } elseif ($r = self::hasTag($value, '^\d+ order desc')) {
                $result[$key] = 'desc';
            } elseif (self::hasTag($value, '^order asc')) {
                $last[$key] = 'asc';
            } elseif (self::hasTag($value, '^order desc')) {
                $last[$key] = 'desc';
            }
        }

        ksort($result);

        $definition->setOperation($oldOp);
        return $result + $last;
    }


    public static function table(D $definition, $operation = null) {
        $oldOp = $definition->getOperation();
        if ($operation) {
            $definition->setOperation($operation);
        }
        $def = $definition->query(D::TYPE_RELATION)->fetch(D::MODE_KEY_ONLY);
        $definition->setOperation($oldOp);
        return reset($def);
    }

    public static function columns(D $definition, $operation = null) {
        $oldOp = $definition->getOperation();
        if ($operation) {
            $definition->setOperation($operation);
        }
        $def = $definition->query(D::TYPE_COLUMN)->byTagRemoval('metadata')->byTagRemoval('placeholder')->fetch(D::MODE_KEY_ONLY);
        $definition->setOperation($oldOp);
        return $def;
    }

    public static function hasTag($field, $tag) {
        $tags = A::get($field, 'tags', array());

        return A::grep($tags, '#' . $tag . '#');
    }

}

class Source extends \Nette\Object {

    private $config;

    /** @var \Nette\Database\Context */
    private $database;

    public function __construct($config, \Nette\Database\Context $database) {
        $this->config = $config;
        $this->database = $database;
    }

    public function form($relation, $operation = null) {

        $struct = $this->getRelationData($relation, $operation);


        $db = $this->database;
        $form = new \App\AdminModule\Form;

        foreach ($struct as $name => $field) {
            $label = A::get($field, 'label', $name);

            if ($this->hasTag($field, 'metadata')) {
                continue;
            }

            $type = A::get($field, 'type', 'text');
            $list = array();
            $default = A::get($field, 'default', null);
            $attributes = A::get($field, 'attributes', array());

            if ($type == 'boolean') {
                $type = 'checkbox';
            }
            $method = 'add' . $type;

            if (method_exists($form, $method)) {

                if ($type == 'select') {
                    $options = A::get($field, 'options', array());
                    if ($options) {
                        $list = $options;
                    } else {
                        $md = $this->getBelongsToMetadata($relation, $name);
                        $fkInfo = $md['fk'];
                        $primary = $md['primary'];
                        $title = $md['title'];
                        $list = array("" => "") + $db->table($fkInfo['table'])->select("$primary, $title")->fetchPairs($primary, $title);
                    }
                }
                $el = $form->$method($name, $label, $list)->setDefaultValue($default);
            } else {
                $el = $form->addText($name, $label)->setType($type)->setDefaultValue($default);
            }

            foreach ($attributes as $key => $set) {
                $el->setAttribute($key, $set);
            }
        }

        $form->addSubmit('send', 'OK');
        return $form;
    }

    private function getBelongsToMetadata($relation, $name) {
        $ref = $this->database->getDatabaseReflection();
        $fk_ = $ref->getBelongsToReference($relation, $name);
        $fk['table'] = $fk_[0];
        $fk['column'] = $fk_[1];

        $primary = $ref->getPrimary($relation);
        $title = $this->getColumnByTag($this->getRelationData($fk['table']), 'title');

        return compact('fk', 'primary', 'title');
    }

    private function getRelationData($relation, $operation = null) {
        $data = A::get($this->config, $relation);

        $base = A::get($data, 'base', array());

        $ext = A::get($data, $operation, array());

        return array_filter(A::mergeTree($ext, $base));
    }

    public function getListingMetadata($relation, $operation = null) {

        $data = $this->getRelationData($relation, $operation);

        $labels = array();
        $belongsTo = array();
        foreach ($data as $name => $col) {
            $label = A::get($col, 'label', $name);
            $labels[$name] = $label;
            if ($this->hasTag($col, 'belongsTo')) {
                $belongsTo[$name] = $this->getBelongsToMetadata($relation, $name);
            }
        }
        return compact('labels', 'belongsTo');
    }

    private function getColumnByTag($data, $tag) {

        foreach ($data as $name => $col) {
            $tags = A::get($col, 'tags', array());
            if ($this->hasTag($col, $tag)) {
                return $name;
            }
        }
        throw new \Nette\InvalidArgumentException;
    }

    private function hasTag($field, $tag) {
        $tags = A::get($field, 'tags', array());

        return A::grep($tags, '#' . $tag . '#');
    }

}
