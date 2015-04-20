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
 * @subpackage Cdc_Client
 */
/**
 * Obtém informações sobre um CEP diretamente da página dos correios.
 */

namespace Cdc\Client;

class Cep {

    const RESULT_CEP = 'cep';
    const RESULT_UF = 'uf';
    const RESULT_CIDADE = 'cidade';
    const RESULT_BAIRRO = 'bairro';
    const RESULT_ENDERECO = 'endereco';

    /**
     *
     * @param string $cep CEP
     * @param mixed $extraParams Peculiaridades de cada forma de obtenção de endereço
     * @return array Array com os índices RESULT_* desta classe.
     */
    public static function query($cep, $extraParams = array()) {
        if ($extraParams) {
            return Cdc_Client_Cep_Edne::query($cep, $extraParams);
        }


        $curl_handle = curl_init();

        curl_setopt($curl_handle, CURLOPT_URL, 'http://www.buscacep.correios.com.br/servicos/dnec/consultaLogradouroAction.do');

        curl_setopt($curl_handle, CURLOPT_HEADER, false);
        curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl_handle, CURLOPT_POST, true);


        curl_setopt($curl_handle, CURLOPT_POSTFIELDS, http_build_query(array(
            'Metodo' => 'listaLogradouro',
            'TipoConsulta' => 'cep',
            'StartRow' => 1,
            'EndRow' => 10,
            'CEP' => $cep,
        )));



        $buffer = curl_exec($curl_handle);
        curl_close($curl_handle);

        $document = new DomDocument;

        libxml_use_internal_errors(true); // Cheio de warning interpretando a página dos correios
        $document->loadHTML($buffer);
        libxml_use_internal_errors(false);

        $elements = $document->getElementsByTagName('table');

        $endereco = $elements->item(2)->childNodes->item(0)->childNodes;


        $logradouro = strip_tags($document->saveXML($endereco->item(0)));
        $bairro = strip_tags($document->saveXML($endereco->item(2)));
        $cidade = strip_tags($document->saveXML($endereco->item(4)));
        $estado = strip_tags($document->saveXML($endereco->item(6)));
        $cep_2 = preg_replace('#\D#', '', strip_tags($document->saveXML($endereco->item(8))));

        if ($cep == $cep_2) {
            $result = array(
                self::RESULT_CEP => $cep,
                self::RESULT_UF => $estado,
                self::RESULT_CIDADE => $cidade,
                self::RESULT_BAIRRO => $bairro,
                self::RESULT_ENDERECO => $logradouro,
            );

            return $result;
        }

        return array();
    }

}
