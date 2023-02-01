<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft srl
 * @license
 * @version    14.02.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPQws/itaPHPQwsClient.class.php');

class itaQwsManager {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaQwsManager();
            $managerObj->setClientParam($clientParam);
            return $managerObj;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getClientParam() {
        return $this->clientParam;
    }

    public function setClientParam($clientParam) {
        $this->clientParam = $clientParam;
    }

    private function setClientConfig($client) {
        $client->setEndpoint($this->clientParam['QWSENDPOINT']);
        $client->setPassword($this->clientParam['QWSPASSWORD']);
    }

    /**
     * 
     * @param type $elementi 
     * @return type
     */
    function getCatasto($elementi) {
        $ritorno = array();
        //
        $itaQwsClient = new itaPHPQwsClient();
        $this->setClientConfig($itaQwsClient);
        //
        $msg = "";
        $ret = array();
        foreach ($elementi['raccolta'] as $key => $raccolta) {
            $param = array();
            $param['alias'] = $this->getAlias($raccolta['IMM_TIPO'], $elementi['tipo_default'], $raccolta['IMM_SUBALTERNO']);
            $param['parametri'] = array(
                "C" => $elementi['codice'],
                "F" => $raccolta['IMM_FOGLIO'],
                "N" => $raccolta['IMM_PARTICELLA'],
                "S" => $raccolta['IMM_SUBALTERNO'],
            );
            $retCatasto = $itaQwsClient->getCatasto($param);
            $risultato = json_decode($retCatasto, true);
            //
            if (!$risultato) {
                $ret[$key]["Status"] = "3";
                $ret[$key]["Message"] = "Errore ws: <br>" . print_r($itaQwsClient->getErrMessage(), true);
                continue;
            }
            if ($risultato['success'] != 1) {
                $ret[$key]["Status"] = "3";
                $ret[$key]["Message"] = "Error: <br>" . $risultato['d'][1];
                continue;
            }

            if ($risultato['d'][0] != 1) {
                $ret[$key]["Status"] = "3";
                $ret[$key]["Message"] = "Error: <br>" . $risultato['d'][1];
                continue;
            }

            if (count($risultato['d'][2]) == 0) {
                $ret[$key]["Status"] = "2";
                $ret[$key]["Message"] = "Attenzione i dati catastali non sono stati validati. Verificare.";
                $msg .= "Attenzione i dati catastali della maschera $key non sono stati validati. Verificare.<br>";
            } else {
                $ret[$key]["Status"] = "1";
                $ret[$key]["Message"] = "I dati catastali sono stati validati.";
            }
        }

        $ritorno["Status"] = "0";
        $ritorno["Message"] = $msg;
        $ritorno["RetValue"] = $ret;
//        print_r("<pre>");
//        print_r($ritorno);
//        print_r("</pre>");
//        exit();
        return $ritorno;
    }

    function getAlias($tipo, $defaultTipo, $sub) {
        switch ($tipo) {
            case "F":
                $alias = "cat_urb_cst_mc_cfn";
                if ($sub) {
                    $alias = "cat_urb_cst_mc_cfns";
                }
                break;
            case "T":
                $alias = "cat_terr_cst_mc_cfn";
                break;
            default:
                if ($defaultTipo == "F") {
                    $alias = "cat_urb_cst_mc_cfn";
                    if ($sub) {
                        $alias = "cat_urb_cst_mc_cfns";
                    }
                } elseif ($defaultTipo == "T") {
                    $alias = "cat_terr_cst_mc_cfn";
                } else {
                    $alias = "cat_urb_cst_mc_cfn";
                }
                break;
        }
        return $alias;
    }

}
