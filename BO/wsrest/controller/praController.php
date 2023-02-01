<?php

require_once('RestController.class.php');

require_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
require_once ITA_BASE_PATH . '/apps/Pratiche/praRestAgent.class.php';

class praController extends RestController {

    /**
     * 
     * @param array $params     Codice Procedura
     * @return array            Array associativo
     * esito    boolean
     * return
     *      pratica_dati_simple array
     *
     *
     *
     * 
     *
     */
    public function praGetPraticaDatiPerProcedura($params) {
        $this->resetLastError();

        $restAgent = new praRestAgent();
        if ($params)
            $ret = $restAgent->GetPraticaDatiPerProcedura($params['procedura']);
        if ($ret) {
            $esito = true;
        } else {
            $esito = false;
        }
        $toReturn = array(
            'esito' => $esito,
            'return' => array(
                'pratica_dati' => $ret
            ),
            'params' => array(),
        );
        if ($params != null) {
            $toReturn['params'] = $params;
        }
        return $toReturn;
    }

}

?>