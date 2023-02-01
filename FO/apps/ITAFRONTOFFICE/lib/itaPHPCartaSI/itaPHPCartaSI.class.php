<?php

class itaPHPCartaSI {

    public static $codiceEsito = array(
        0 => 'Autorizzazione concessa',
        20 => 'Ordine non presente',
        101 => 'Parametri errati o mancanti',
        102 => 'PAN errato',
        103 => "Autorizzazione negata dall'emittente della carta",
        104 => "Errore generico",
        108 => "Ordine già registrato",
        109 => "Errore tecnico",
        110 => "Numero contratto già presente",
        111 => "Mac errato",
        112 => "Transazione negata per autenticazione VBV/SC fallita o non possibile",
        113 => "Numero contratto non presente in archivio",
        114 => "Merchant non abilitato al pagamento multiplo sul gruppo",
        115 => "Codice Gruppo non presente",
        116 => "3D Secure annullato da utente",
        117 => "Carta non autorizzata causa applicazione regole BIN Table",
        118 => "Controllo Blacklist (oppure Controllo PAN oppure Controllo CF oppure Controllo CF/PAN) -> esito riservato all?applicazione dei filtri",
        119 => "Esercente non abilitato ad operare in questa modalità",
        120 => "Circuito non accettato, nel messaggio di richiesta è stato indicato di accettare il pagamento con un circuito mentre il pan della carta è di altro circuito",
        121 => "Transazione chiusa per timeout",
        122 => "Numero di tentativi di retry sul mesesimo codTrans esauriti",
        400 => "Auth. Denied",
        401 => "expired card",
        402 => "restricted card",
        403 => "invalid merchant",
        404 => "transaction not permitted",
        405 => "not sufficient funds",
        406 => "Technical Problem",
        407 => "Host not found"
    );
    public $err;

    /**
     * Libreria di funzioni Generiche e Utility per Pratiche
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    function __construct($libErr = null) {
        if (!$libErr) {
            //$this->cdsErr = new sueErr();
        } else {
            $this->err = $libErr;
        }
    }

    function __destruct() {
        
    }

    function getPath() {
        return $this->path;
    }

    function setPath($path) {
        $this->path = $path;
    }

    static function interrogazioneOrdine($path, $alias, $codTrans, $id_op, $type_op, $user, $chiave_mac) {
        $mac = strtoupper(sha1($alias . $codTrans . $id_op . $type_op . $user . $chiave_mac));
        $xml = '<?xml version="1.0" encoding="ISO-8859-15"?>
                <VPOSREQ>
                    <alias>' . $alias . '</alias>
                    <INTREQ>
                        <codTrans>' . $codTrans . '</codTrans>
                        <id_op>' . $id_op . '</id_op>
                        <type_op>' . $type_op . '</type_op>
                    </INTREQ>
                    <user>' . $user . '</user>
                    <mac>' . $mac . '</mac>
                </VPOSREQ>';
        require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
        $itaRestClient = new itaRestClient();
        if (!$itaRestClient->post($path, false, array(), utf8_encode($xml), 'text/xml')) {
            //errore
            return false;
        }
        return $itaRestClient->getResult();
    }

    static function interrogazioneOrdineNexi($path, $alias, $codTrans, $chiave_mac) {
//        $mac = strtoupper(sha1($alias . $codTrans . $id_op . $type_op . $user . $chiave_mac));
        $timeStamp = (time()) * 1000;
        // Calcolo MAC
        $mac = sha1('apiKey=' . $alias . 'codiceTransazione=' . $codTrans . 'timeStamp=' . $timeStamp . $chiave_mac);
        require_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';

        $parametri = array(
            'apiKey' => $alias,
            'codiceTransazione' => $codTrans,
            'timeStamp' => $timeStamp,
            'mac' => $mac
        );
        $itaRestClient = new itaRestClient();
        if (!$itaRestClient->post($path, false, array(), json_encode($parametri), 'application/json')) {
            //errore
            return false;
        }
        return $itaRestClient->getResult();
    }

}
