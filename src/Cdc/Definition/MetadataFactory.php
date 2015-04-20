<?php

namespace Cdc\Definition;

use \Cdc\Definition as D;
use Nette\Utils\Arrays as A;

class MetadataFactory {

    public static function search(D $definition, $operation = null) {
        $oldOp = $definition->getOperation();
        if ($operation) {
            $definition->setOperation($operation);
        }
        $def = $definition->query(D::TYPE_WIDGET, D::TYPE_RELATION)->byTag('search')->fetch();

        foreach ($def as $key => $value) {
            if ($value['type'] == D::TYPE_RELATION) {
                $def[$key][D::TYPE_WIDGET]['widget'] = 'text';
                $def[$key][D::TYPE_WIDGET]['attributes']['class'] = 'search-general';
                break;
            }
        }

        $definition->setOperation($oldOp);
        return $def;
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
