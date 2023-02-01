<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    24.04.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
class proItalsoft {

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Italsoft
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public function protocollazione($elementi) {

        $msg = "Funzione disabilitata. Configurare la protocollazione tramite Web Service.";
        $ritorno = array('value' => '', 'status' => false, 'msg' => $msg);
        return $ritorno;

        $model = 'proArri';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proArri = new proArri();
        $_POST = array();
        $proArri->AzzeraVariabili();
        $proArri->tipoProt = $elementi['tipo'];
        $proArri->assegnaDati($elementi['dati']);
        $proArri->assegnaDestinatari($elementi['destinatari']);
        //$proArri->assegnaUffici($elementi['uffici']);
        $rowid = $proArri->registraPro("Aggiungi");
        if ($rowid == false) {
            $msg = "Errore in registrazione dati. Rowid is false.";
            $ritorno = array('value' => '', 'status' => false, 'msg' => $msg);
            return $ritorno;
        } else if ($rowid == 'Error') {
            $msg = "Errore in registrazione dati. Rowid in Error.";
            $ritorno = array('value' => '', 'status' => false, 'msg' => $msg);
            return $ritorno;
        }
        $anapro_rec = $proArri->proLib->GetAnapro($rowid, 'rowid');
        $ritorno = array('value' => $anapro_rec['PRONUM'], 'status' => true, 'msg' => '');
        return $ritorno;
    }

    public function aggiungiAllegatiProtocollo($dati) {
        if (!isset($dati['allegati'])) {
            return false;
        }
        $model = 'proArri';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proArri = new proArri();
        $_POST = array();
//        $proArri->AzzeraVariabili();
        if (!itaLib::createPrivateUploadPath()) {
            Out::msgStop("Gestione Allega da PEC", "Creazione ambiente di lavoro temporaneo fallita");
            return;
        }
        $destFile = itaLib::getPrivateUploadPath();
        $proArri->proArriAlle = array();
        foreach ($dati['allegati'] as $elemento) {
            $randName = md5(rand() * time()) . "." . pathinfo($elemento['DATAFILE'], PATHINFO_EXTENSION);
            @copy($elemento['FILE'], $destFile . '/' . $randName);
            $tipoAlle = 'ALLEGATO';
            $proArri->proArriAlle[] = Array(
                'ROWID' => 0,
                'FILEPATH' => $destFile . '/' . $randName,
                'FILENAME' => $randName,
                'DOCNAME' => $randName,
                'FILEINFO' => $elemento['DATAFILE'],
                'DOCFDT' => date("Ymd"),
                'DOCSERVIZIO' => 1,
                'DOCTIPO' => $tipoAlle,
                'DOCIDMAIL' => $elemento['DOCIDMAIL']
            );
        }
        $codice = $dati['dati']['PRONUM'];
        $_POST[$model . '_ANAPRO'] = $dati['dati'];
        $proArri->tipoProt = $dati['dati']['PROPAR'];
        return $proArri->GestioneAllegati($codice);
    }

}

?>