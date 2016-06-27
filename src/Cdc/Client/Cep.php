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
 *	 http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * @author Eduardo Marinho
 * @package Cdc
 * @subpackage Cdc_Client
 */

namespace Cdc\Client;
use \DomDocument;

/**
 * Obtém informações sobre um CEP diretamente da página dos correios.
 */
class Cep
{

    const RESULT_CEP      = 'cep';
    const RESULT_UF       = 'uf';
    const RESULT_CIDADE   = 'cidade';
    const RESULT_BAIRRO   = 'bairro';
    const RESULT_ENDERECO = 'endereco';

    /**
     *
     * @param string $cep CEP
     * @return array Array com os índices RESULT_* desta classe.
     */
    public static function query($cep)
    {

        $curl_handle = curl_init();

        curl_setopt($curl_handle, CURLOPT_URL, 'http://www.buscacep.correios.com.br/sistemas/buscacep/resultadoBuscaCepEndereco.cfm');

        curl_setopt($curl_handle, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($curl_handle, CURLOPT_HEADER, true);
        curl_setopt($curl_handle, CURLOPT_ENCODING, "");
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_POST, true);


        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query(array(
                    'relaxation'   => $cep,
                    'tipoCEP'      => 'ALL',
                    'semelhante'   => 'N',
                )));


        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

        if (!$buffer) {
            return false;
        }

        $document = new DomDocument;

        libxml_use_internal_errors(true); // Cheio de warning interpretando a página dos correios
        $document->loadHTML($buffer);
        libxml_use_internal_errors(false);

        $elements = $document->getElementsByTagName('table');

        if (!$elements->length) {
            return false;

        }


        $endereco = $elements->item(0)->childNodes->item(1)->childNodes;


        $logradouro = trim(strip_tags($document->saveXML($endereco->item(0))));
        $bairro     = trim(strip_tags($document->saveXML($endereco->item(2))));
        $cidade_estado     = explode('/', trim(strip_tags($document->saveXML($endereco->item(4)))));
        $cidade = trim(reset($cidade_estado));
        $estado = trim(end($cidade_estado));
        $cep_2      = trim(preg_replace('#\D#', '', strip_tags($document->saveXML($endereco->item(6)))));

        if ($cep == $cep_2)
        {
            $result = array(
                self::RESULT_CEP      => $cep_2,
                self::RESULT_UF       => $estado,
                self::RESULT_CIDADE   => $cidade,
                self::RESULT_BAIRRO   => $bairro,
                self::RESULT_ENDERECO => $logradouro,
            );

            foreach ($result as $key => $value) {
                $result[$key] = preg_replace('/^[\pZ\pC]+|[\pZ\pC]+$/u', '', $value);
            }

            return $result;
        }
    }

}

