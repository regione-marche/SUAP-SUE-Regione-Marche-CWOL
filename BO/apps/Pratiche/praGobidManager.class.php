<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Antimo Panetta <andimo.panetta@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    10.11.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php');
include_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

class praGobidManager {

    /**
     * Libreria di funzioni Generiche e Utility per Pratiche
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $PRAM_DB;
    public $devLib;
    private $errMessage;
    private $errCode;

    function __construct($ditta = '') {
        try {
            if ($ditta) {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ditta);
            } else {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            }
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setPRAMDB($PRAM_DB) {
        $this->PRAM_DB = $PRAM_DB;
    }

    public function getPRAMDB() {
        return $this->PRAM_DB;
    }

    public function getITALWEBDB() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEBDB', "");
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    public function leggiParametro($nomeParametro) {
        $this->devLib = new devLib();
        $parametro = $this->devLib->getEnv_config('GOBIDREST', 'codice', $nomeParametro, false);
        return $parametro['CONFIG'];
    }

    public function output($codiceProcedura, $returnSource = false) {
        $url = $this->leggiParametro('RESTURL');
        $path = "output.php?v=t&i=$codiceProcedura";
        $resultRest = $this->getCall($url, $path);
        if (!$resultRest) {
            return false;
        }
        if ($returnSource == true){
            return $resultRest;
        }
        $result = json_decode($resultRest, true);
        if (!$result) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile interpretari dati Json.');
            return false;
        }
        //
        // json_decod riuscito
        //
        $sen = $inv = $per = $fot = 0;
        foreach ($result as $risultato) {
            $impresa = $risultato['impresa'];
            $curatore = $risultato['curatore'];
            foreach ($curatore as $key => $value) {
                if (is_int($key)) {
                    unset($curatore[$key]);
                }
            }
            $documenti = $risultato['documenti'];
            foreach ($documenti as $documento) {
                if ($documento['TIPO'] == 'Sentenza') {
                    foreach ($documento as $key => $doc) {
                        $sentenza[$sen][$key] = $doc;
                    }
                    $sen++;
                }
                if ($documento['TIPO'] == 'Inventario') {
                    foreach ($documento as $key => $doc) {
                        $inventario[$inv][$key] = $doc;
                    }
                    $inv++;
                }
                if ($documento['TIPO'] == 'Perizia') {
                    foreach ($documento as $key => $doc) {
                        $perizia[$per][$key] = $doc;
                    }
                    $per++;
                }
                if ($documento['TIPO'] == 'Foto') {
                    foreach ($documento as $key => $doc) {
                        $foto[$fot][$key] = $doc;
                    }
                    $fot++;
                }
            }
        }
        $result['IMPRESA'] = $impresa;
        $result['CURATORE'] = $curatore;
        $result['DOCUMENTI']['SENTENZA'] = $sentenza;
        $result['DOCUMENTI']['INVENTARIO'] = $inventario;
        $result['DOCUMENTI']['PERIZIA'] = $perizia;
        $result['DOCUMENTI']['FOTO'] = $foto;

        return $result;
    }

    public function getCall($url, $path) {
        $restClient = new itaRestClient();
        $restClient->setTimeout(10);
        $restClient->setCurlopt_url($url);
        if ($restClient->get($path)) {
            if ($restClient->getHttpStatus() !== 200) {
                $this->setErrCode(-1);
                $this->setErrMessage("Chiamata non riuscita Status:" . $restClient->getHttpStatus());
                return false;
            }
        } else {

            $this->setErrCode(-1);
            $this->setErrMessage($restClient->getErrMessage());
            return false;
        }
        return $restClient->getResult();
    }

    public function getCallFile($url, $path) {
        $restClient = new itaRestClient();
        $restClient->setTimeout(10);
        $restClient->setCurlopt_url($url);
        $restClient->setCurlopt_followlocation(true);
        $restClient->setCurlopt_header(true);
        if ($restClient->get($path)) {
            if ($restClient->getHttpStatus() !== 200) {
                $this->setErrCode(-1);
                $this->setErrMessage("Chiamata non riuscita Status:" . $restClient->getHttpStatus());
                return false;
            }
        } else {

            $this->setErrCode(-1);
            $this->setErrMessage($restClient->getErrMessage());
            return false;
        }

        return array(
            'headers' => $restClient->getHeaders(),
            'content' => $restClient->getResult()
        );
    }

}

?>
