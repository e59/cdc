<?php

/**
 * Cdc Toolkit
 *
 * Copyright 2012 Eduardo Marinho
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * 	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Eduardo Marinho
 * @package Cdc
 */

namespace Cdc;

/**
 * Consulta a conjuntos de definições.
 *
 * Uma definição é um conjunto de atributos que descrevem como uma determinada
 * estrutura de dados deve se comportar em diversas situações comuns em sistemas,
 * como formulário, filtro e validação de dados, consulta em bancos de dados,
 * ou qualquer outra coisa que pareça útil e que faça sentido ser definida em
 * apenas um lugar. As constantes de tipo são apenas lembretes e facilitadores,
 * usadas internamente pelo toolkit, e não o conjunto fechado de tipos.
 *
 * Uma definição é apenas um arquivo de configuração programável.
 *
 */
use \Nette\Utils\Arrays as A;

class Definition {

    /**
     * Índice para definições específicas de operação.
     */
    const OPERATION = 'operation';

    /**
     * Coluna.
     */
    const TYPE_COLUMN = 'column';

    /**
     * Relação.
     */
    const TYPE_RELATION = 'relation';

    /**
     * Anexo.
     */
    const TYPE_ATTACHMENT = 'attachment';

    /**
     * Regras de filtro e/ou validação.
     */
    const TYPE_RULE = 'rule';

    /**
     * Instruções para geração de elementos de entrada de dados.
     */
    const TYPE_WIDGET = 'widget';

    /**
     * Retornar apenas nomes das colunas.
     */
    const MODE_KEY_ONLY = 'key_only';

    /**
     * Retornar apenas o nome da primeira coluna do resultado.
     */
    const MODE_SINGLE = 'single';

    /**
     * Retornar a definição completa, após o cascateamento das definições específicas de operação.
     */
    const MODE_FULL = 'full';

    /**
     * Chave para formatters
     */
    const FORMATTER = 'formatter';

    /**
     * Chave para providers
     */
    const PROVIDER = 'provider';

    //
    const STATEMENT_SELECT = 'select';

    //
    const STATEMENT_INSERT = 'insert';

    //
    const STATEMENT_UPDATE = 'update';

    //
    const STATEMENT_DELETE = 'delete';

    //
    const RELATION_MANY_TO_MANY = 'many_to_many';

    //
    const RELATION_ONE_TO_MANY = 'one_to_many';

    //
    const RELATION_HAS_AND_BELONGS_TO = 'has_belongs_to';

    /**
     * Definição atual.
     *
     * @var array
     */
    protected $definition = array();

    /**
     * Operação atual.
     *
     * @var string
     */
    protected $operation;

    /**
     * Resultado de consulta.
     *
     * @var array
     */
    private $query = array();

    /**
     *
     * @param type $operation Operação atual
     */
    public function __construct($operation = DEFAULT_OPERATION) {
        $this->setOperation($operation);
    }

    /**
     * Obter a estrutura de definição atual.
     *
     * @return array Definição
     */
    public function getDefinition() {
        if ($this->definition) {
            return $this->definition;
        }
        return $this->setDefinition($this->buildDefinition());
    }

    protected function buildDefinition() {
        return array();
    }

    /**
     * Atribui uma definição nova.
     *
     * Isto limpará a consulta atual.
     * @param array $definition
     */
    public function setDefinition(array $definition) {
        $this->reset();
        $this->definition = $definition;
        return $this->definition;
    }

    /**
     * Obter o nome da operação atual.
     *
     * @return string Operação
     */
    public function getOperation() {
        return $this->operation;
    }

    /**
     * Configura a operação.
     *
     * Isto limpará a consulta atual.
     *
     * @param type $operation
     */
    public function setOperation($operation) {
        $this->reset();
        $this->operation = $operation;
        return $this;
    }

    /**
     * Inicia uma consulta à definição.
     *
     * @return \Cdc\Definition
     *
     * O tipo é obrigatório para normalizar as definições e simplificar o processamento.
     *
     */
    public function query(/* , .. */) {
        $types = func_get_args();
        $this->query = array();
        $d = $this->getDefinition();
        $type = null;
        foreach ($d as $key => $value) {
            if (!$this->checkValueInOperation($value)) {
                continue;
            }

            foreach ($types as $type) {
                if (array_key_exists('type', $value)) {
                    if ($value['type'] == $type) {
                        $this->query[$key] = $value;
                    }
                }
                // @TODO: Melhorar esta lógica
                if (array_key_exists($type, $value)) {
                    $this->query[$key] = $value;
                }
            }
        }
        if (count($types) < 2 && ($type == self::TYPE_RELATION) && (count($this->query) > 1)) {
            throw new \Cdc\Definition\Exception\MultipleRelationsFound;
        }

        return $this;
    }

    /**
     * Especifica uma chave para ser utilizada na consulta.
     *
     * @param string $term Chave para a consulta.
     * @return \Cdc\Definition
     */
    public function byKey($term) {
        foreach ((array) $this->query as $key => $value) {
            if (array_key_exists($term, (array) $value[self::OPERATION][$this->getOperation()])) { // manter?
                if ($value[self::OPERATION][$this->getOperation()][$term] === null) { // remove null de dentro
                    unset($this->query[$key]);
                }
            } elseif (!array_key_exists($term, $value)) { // não manter
                unset($this->query[$key]);
            } elseif ($value[$term] === null) { // remove null de fora
                unset($this->query[$key]);
            }
        }
        return $this;
    }

    /**
     * Especifica uma tag para ser utilizada na consulta.
     *
     * @param string $tag Tag para a consulta.
     * @return \Cdc\Definition
     */
    public function byTag($tag) {
        foreach ((array) $this->query as $key => $value) {

            $innerTags = A::get((array) $value[self::OPERATION][$this->getOperation()], 'tags', array());

            $tags = A::get($value, 'tags', array());

            $outerTags = A::get($tags, array());

            $exists = false !== array_search($tag, $innerTags);
            if (!$exists) {
                $exists = false !== array_search($tag, $outerTags);
            }

            if (!$exists) {
                unset($this->query[$key]);
            }
        }
        return $this;
    }

    /**
     * Especifica uma tag para excluir da consulta.
     *
     * O resultado incluirá todos os elementos que não possuem esta tag.
     *
     * @param string $term Tag com filtro de exclusão para a consulta.
     * @return \Cdc\Definition
     */
    public function byTagRemoval($tag) {
        foreach ((array) $this->query as $key => $value) {

            $innerTags = A::get((array) $value[self::OPERATION][$this->getOperation()], 'tags', array());

            $tags = A::get($value, 'tags', array());

            $outerTags = A::get($tags, array());

            $exists = false !== array_search($tag, $innerTags);
            if (!$exists) {
                $exists = false !== array_search($tag, $outerTags);
            }

            if ($exists) {
                unset($this->query[$key]);
            }
        }
        return $this;
    }

    /**
     * Especifica uma chave para excluir da consulta.
     *
     * O resultado incluirá todos os elementos que não possuem esta chave.
     *
     * @param string $term Chave com filtro de exclusão para a consulta.
     * @return \Cdc\Definition
     */
    public function byKeyRemoval($term) {
        $return = array();
        foreach ((array) $this->query as $key => $value) {
            if (array_key_exists($term, $value[self::OPERATION][$this->getOperation()])) { // manter?
                if ($value[self::OPERATION][$this->getOperation()][$term] === null) { // remove null de dentro
                    $return[$key] = $this->query[$key];
                }
            } elseif (!array_key_exists($term, $value)) { // não manter
                $return[$key] = $this->query[$key];
            } elseif ($value[$term] === null) { // remove null de fora
                $return[$key] = $this->query[$key];
            }
        }

        $this->query = $return;

        return $this;
    }

    /**
     * Verifica se existe definição para o valor na operação atual.
     *
     * A definição é obrigatória para normalizar as definições e simplificar o processamento.
     *
     * @param array $value
     * @return boolean
     * @throws \Cdc\Definition_Exception_NoOperationSpecified Caso a coluna não possua definições de operação.
     */
    private function checkValueInOperation($value) {
        if (!array_key_exists(self::OPERATION, $value)) {
            throw new \Cdc\Definition\Exception\NoOperationSpecified;
        }
        return array_key_exists($this->getOperation(), $value[self::OPERATION]) && null !== $value[self::OPERATION][$this->getOperation()];
    }

    /**
     * Retorna o resultado da consulta atual.
     *
     * @param string $mode Modo de retorno (MODE_FULL ou MODE_KEY_ONLY)
     * @return array
     * @throws \Cdc\Definition_Exception_UninitializedQuery Caso nenhuma consulta seja especificada.
     */
    public function fetch($mode = self::MODE_FULL) {
        $result = $this->query;
        // why i wrote this?
//        if (null === $result) {
//            throw new \Cdc\Definition\Exception\UninitializedQuery;
//        }
        if ($mode === self::MODE_FULL) {
            foreach ($result as $key => $value) {
                $result[$key] = A::mergeTree($value[self::OPERATION][$this->getOperation()], $value);

                unset($result[$key][self::OPERATION]);
            }
            return $result;
        } elseif ($mode === self::MODE_SINGLE) {
            return key($result);
        }
        return array_keys($result);
    }

    /**
     * Limpa a consulta atual.
     */
    public function reset() {
        $this->query = array();
    }

    /**
     *
     * @param type $defaults
     * @param type $newValues
     * @todo WRITE THIS
     */
    public function mergeInput($data, $input) {

        $primary = $this->query(self::TYPE_COLUMN)->byTag('primary')->fetch(self::MODE_SINGLE);
        unset($data[$primary]);

        if (!$input) {
            return $data;
        }


        $merge = array_merge($data, $input);

        $diff = array_diff_key($data, $input);

        $skel = array_combine(array_keys($merge), array_fill(0, count($merge), null));

        $clearer = array_intersect_key($skel, $diff);

        $data = array_merge($merge, $clearer);

        return $data;
    }

    public function format($rowset, $args = array()) {
        $tags = $this->query(self::TYPE_COLUMN)->byKey('tags')->fetch();
        $formatters = $this->query(self::TYPE_COLUMN)->byKey('output_formatter')->fetch();

        $result = array();
        foreach ($rowset as $k => $data) {
            $result[$k] = $data;
            foreach ($result[$k] as $key => $value) {
                if (isset($formatters[$key]['output_formatter'])) {
                    $callback = $formatters[$key]['output_formatter'][0];

                    $custom = A::get($formatters[$key]['output_formatter'], 1, array());

                    $args = A::mergeTree($custom, $args);

                    call_user_func_array($callback, array(&$result[$k], $rowset, $key, $args));
                } elseif (isset($tags[$key]['tags'])) {
                    foreach ($tags[$key]['tags'] as $tag) {
                        if (method_exists('\Cdc\Widget\Formatter', $tag)) {
                            call_user_func_array(array('\Cdc\Widget\Formatter', $tag), array(&$result[$k], $rowset, $key, $args));
                        }
                    }
                }
            }
        }

        return $result;
    }

    public function hydrated(\Cdc\Sql\Select $sql, array $values = array()) {
        $result = $sql->stmt($values)->fetchAll();

        if (!$result) {
            return array();
        }

        $presets = $this->getPresets();

        $attachmentsSql = $sql->attach;
        $localAttachments = $this->query(self::TYPE_ATTACHMENT)->fetch();

        $attachments = A::mergeTree($attachmentsSql, $localAttachments);

        foreach ($attachments as $col => $a) {

            if (array_key_exists($col, $presets)) {
                $preset = $presets[$col]['id'];
            } else {
                $preset = false;
            }

            $parent = A::get($a, 'parent');
            $local = A::get($a, 'local');
            $id = A::get($a, 'id');
            $l = explode('.', $local);
            $local_column = end($l);
            $q = A::get($a, 'query');

            if (is_array($q)) {
                $_q = new \Cdc\Sql\Select($sql->getPdo());
                foreach ($q as $defi => $nition) {
                    $_q->$defi = $nition;
                }
                $q = $_q;
            }

            $ids = \Cdc\ArrayHelper::pluck($result, $parent);

            $where = array(
                $local . ' in' => $ids,
            );

            if ($preset) {
                $where['preset_id ='] = $preset;
            }


            $w['and'] = array_filter(array_merge($q->where, $where));

            $q->where = $w;

            $r = $q->stmt()->fetchAll();

            $att = array();

            foreach ($r as $item) {
                $att[$item[$local_column]][$item[$id]] = $item;
            }


            foreach ($result as &$value) {
                if (isset($att[$value[$parent]])) {
                    $value[$col] = $att[$value[$parent]];
                } else {
                    $value[$col] = array();
                }
            }
        }

        return $result;
    }

    public function prepareInput($input) {
        return $input;
    }

    public function search($data, $def = null) {
        if (null === $def) {
            $def = \Cdc\Definition\MetadataFactory::search($this);
        }
        $result = array();
        foreach ($data as $key => $value) {
            if ($value === '') {
                continue;
            }
            if ($def[$key]['type'] == self::TYPE_RELATION) {
                $result['fts @@'] = new \Cdc\Sql\Parameter($key, "plainto_tsquery('" . \C::$pg_fts_regconfig . "', %s)", $value);
            } else {
                    $result[$key . ' ='] = $value;
            }
        }

        return $result;
    }

}
