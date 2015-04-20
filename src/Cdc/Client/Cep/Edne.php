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
class Cdc_Client_Cep_Edne extends Cdc_Client_Cep
{

    /**
     *
     * @param string $cep CEP
     * @return array Array com os índices RESULT_* desta classe.
     */
    public static function query($cep, $extraParams = array())
    {

        $pdo = $extraParams;

        $stmt = $pdo->prepare('select cep, ufe_sg as estado, loc_no as cidade, bai_no as bairro, log_no as logradouro from correios.cep where cep = ?');
        $stmt->bindValue(1, $cep);
        $stmt->execute();
        $result = $stmt->fetch();

        if ($result)
        {
            $result = array(
                self::RESULT_CEP => $result['cep'],
                self::RESULT_UF => $result['estado'],
                self::RESULT_CIDADE => $result['cidade'],
                self::RESULT_BAIRRO => $result['bairro'],
                self::RESULT_ENDERECO => $result['logradouro'],
            );

            return $result;
        }

        return array();
    }

}
