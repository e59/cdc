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
 * @subpackage Cdc_Rule
 */

namespace Cdc\Rule;

use \Nette\Utils\Arrays as A;

class FileType extends Rule {

    private $files;
    private $extensions;

    public function __construct($files, $extensions) {
        $this->files = $files;
        $this->extensions = $extensions;
    }

    public function check($index, &$row, $definition = null, $rowset = array()) {


        if (!$this->extensions) {
            return false;
        }

        if (!$this->files) {
            return false;
        }

        if (!array_key_exists($index, $this->files)) {
            return array();
        }


        $fl = A::get($this->files, $index);

        if (!is_array($fl)) {
            $fl = array($fl);
        }

        $result = array();
        foreach ($fl as $file) {

            if (is_object($file)) {
                $fileName = $file->getName(); // nette fileupload
            } else {
                $fileName = A::get((array) $file, 'name', null);
            }


            if (!$fileName) {
                continue;
            }

            $pathInfo = pathinfo($fileName);
            $ext = A::get($pathInfo, 'extension');
            if (!in_array(mb_strtolower($ext, 'UTF-8'), $this->extensions)) {
                $result[] = 'Extensão inválida: ' . $ext;
            }
        }

        return $result;
    }

}
