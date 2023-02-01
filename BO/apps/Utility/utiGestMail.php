<?php

/**
 *
 * Form controllo invio mail
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Utility
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    10.05.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

function utiGestMail() {
    $utiGestMail = new utiGestMail();
    $utiGestMail->parseEvent();
    return;
}

class utiGestMail extends itaModel {

    public $nameForm = "utiGestMail";
    public $gridAllegati = "utiGestMail_gridAllegati";
    public $gridDestinatari = "utiGestMail_gridDestinatari";
    public $tipo;
    public $praLib;
    public $allegati;
    public $returnModel;
    public $returnEvent;
    public $valori;
    public $sizeAllegati;
    public $returnEventOnClose;
    public $destinatari = array();
    public $daMail = array();
    public $obbligoInvioMail;

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
        try {
            // DATI SALVATI IN SESSION //
            $this->tipo = App::$utente->getKey($this->nameForm . "_tipo");
            $this->allegati = App::$utente->getKey($this->nameForm . "_allegati");
            $this->returnModel = App::$utente->getKey($this->nameForm . "_returnModel");
            $this->returnEvent = App::$utente->getKey($this->nameForm . "_returnEvent");
            $this->valori = App::$utente->getKey($this->nameForm . "_valori");
            $this->sizeAllegati = App::$utente->getKey($this->nameForm . "_sizeAllegati");
            $this->returnEventOnClose = App::$utente->getKey($this->nameForm . "_returnEventOnClose");
            $this->destinatari = App::$utente->getKey($this->nameForm . "_destinatari");
            $this->daMail = App::$utente->getKey($this->nameForm . "_daMail");
            $this->obbligoInvioMail = App::$utente->getKey($this->nameForm . "_obbligoInvioMail");
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    public function getObbligoInvioMail() {
        return $this->obbligoInvioMail;
    }

    public function setObbligoInvioMail($obbligoInvioMail) {
        $this->obbligoInvioMail = $obbligoInvioMail;
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . "_tipo", $this->tipo);
            App::$utente->setKey($this->nameForm . "_allegati", $this->allegati);
            App::$utente->setKey($this->nameForm . "_returnModel", $this->returnModel);
            App::$utente->setKey($this->nameForm . "_returnEvent", $this->returnEvent);
            App::$utente->setKey($this->nameForm . "_valori", $this->valori);
            App::$utente->setKey($this->nameForm . "_sizeAllegati", $this->sizeAllegati);
            App::$utente->setKey($this->nameForm . "_returnEventOnClose", $this->returnEventOnClose);
            App::$utente->setKey($this->nameForm . "_destinatari", $this->destinatari);
            App::$utente->setKey($this->nameForm . "_daMail", $this->daMail);
            App::$utente->setKey($this->nameForm . "_obbligoInvioMail", $this->obbligoInvioMail);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->valori = $_POST['valori'];
                $this->sizeAllegati = $_POST['sizeAllegati'];
                $this->returnEventOnClose = $_POST['returnEventOnClose'];
                $this->tipo = $_POST['tipo'];
                $this->allegati = $_POST['allegati'];
                $this->returnModel = $_POST['returnModel'];
                $this->returnEvent = $_POST['returnEvent'];
                $this->destinatari = $_POST['valori']['Destinatari'];
                $this->daMail = $_POST['ElencoDaMail'];
                $this->obbligoInvioMail = $_POST['obbligoInvioMail'];
                Out::show($this->nameForm);
                $this->Dettaglio();
                break;
            case 'delGridRow':
                if (array_key_exists($_POST['rowid'], $this->allegati) == true) {
                    unset($this->allegati[$_POST['rowid']]);
                }
                $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                Out::codice('tinyActivate("' . $this->nameForm . '_Corpo");');
                break;
            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        //$this->CaricaGriglia($this->gridAllegati, $this->allegati, '2');
                        $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
                        break;
                }
                break;
            case 'editGridRow':
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->gridAllegati:
                        if (array_key_exists($_POST['rowid'], $this->allegati) == true) {
                            Out::openDocument(
                                    utiDownload::getUrl(
                                            $this->allegati[$_POST['rowid']]['FILEORIG'], $this->allegati[$_POST['rowid']]['FILEPATH']
                                    )
                            );
                        }
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Conferma':
                        if (!$this->checkDestinatari()) {
                            break;
                        }
                        if ($_POST[$this->nameForm . '_Corpo'] == '') {
                            Out::msgQuestion("Invio Comunicazione", "Attenzione!!! La Mail è vuota. Inviarla comunque?", array(
                                'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaInvio', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaInvio', 'model' => $this->nameForm, 'shortCut' => "f5")
                                    )
                            );
                            break;
                        }

                    case $this->nameForm . '_ConfermaInvio':
                        if ($this->tipo != 'inoltra') {
                            $destinatari = $_POST[$this->gridDestinatari]['gridParam']['selarrrow'];
                            $destinatario = $_POST[$this->nameForm . '_Destinatario'];
                            if ($destinatari == '' && $destinatario == '') {
                                Out::msgStop("Attenzione!", "Selezionare almeno un Destinatario!");
                                break;
                            }
                            if ($this->tipo == 'protocollo') {
                                $tot = count($this->valori['Destinatari']);
                                $sel = count(explode(',', $destinatari));

                                if ($tot != $sel) {
                                    Out::msgQuestion("Attenzione!", "<br>Non tutti gli indirizzi sono selezionati, vuoi proseguire?", array(
                                        'F8-Annulla' => array('id' => $this->nameForm . '_AnnullaInvio', 'model' => $this->nameForm, 'shortCut' => "f8"),
                                        'F5-Conferma' => array('id' => $this->nameForm . '_ConfermaInvio2', 'model' => $this->nameForm, 'shortCut' => "f5")
                                            )
                                    );
                                    break;
                                }
                            }
                        }

                    case $this->nameForm . '_ConfermaInvio2':
                        $destinatari = $_POST[$this->gridDestinatari]['gridParam']['selarrrow'];
                        if ($this->sizeAllegati) {
                            foreach ($this->allegati as $allegato) {
                                $size = $size + filesize($allegato['FILEPATH']) / 1048576;
                            }
                            $size = round($size, 2);
                            if ($size > $this->sizeAllegati) {
                                Out::msgStop('Errore Allegati Mail', "Il limite di memoria di $this->sizeAllegati MB per gli allegati è stato superato.<br>Si consiglia di fare più passi comunicazione");
                                break;
                            }
                        }

                        $corpo = $_POST[$this->nameForm . '_Corpo'];
                        //
                        $email = $_POST[$this->nameForm . '_Email'];
                        $destinatario = $_POST[$this->nameForm . '_Destinatario'];
                        if ($this->destinatari[0]["MAIL"]) {
                            //if ($_POST['valori']['Destinatario']) {
                            $email = $this->destinatari[0]["MAIL"];
                            $destinatario = $this->destinatari[0]['NOME'];
                        }
                        //
                        $oggetto = $_POST[$this->nameForm . '_Oggetto'];
                        $DaMail = $_POST[$this->nameForm . '_DaMail'];
                        $model = $this->returnModel;
                        $_POST = array();
                        $_POST['rowid'] = $this->valori['rowidChiamante'];
                        $_POST['valori'] = $this->valori;
                        if ($corpo) {
                            $_POST['valori']['Corpo'] = $corpo;
                        } else {
                            $_POST['valori']['Corpo'] = $corpo = 'Mail Vuota';
                        }
                        $_POST['valori']['Email'] = $email;
                        $_POST['valori']['Destinatario'] = $destinatario;

                        $_POST['valori']['Destinatari'] = $destinatari;
                        $_POST['valori']['DestinatariOriginari'] = $this->destinatari;

                        $_POST['valori']['Oggetto'] = $oggetto;
                        $_POST['allegati'] = $this->allegati;
                        $_POST['valori']['Inviata'] = 1;
                        if ($DaMail) {
                            $_POST['valori']['ForzaDaMail'] = $DaMail;
                        }
                        $objModel = itaModel::getInstance($model);
                        $objModel->setEvent($this->returnEvent);
                        $objModel->parseEvent();
                        $this->returnToParent();
                        break;

                    case $this->nameForm . '_AddMittDest':
                        $where = " WHERE MEDUFF <> '' AND MEDEMA <> '' ";
                        proRic::proRicAnamed($this->nameForm, $where, '', '', 'returnanamedDestinatario');
                        break;

                    case 'before-close-portlet':
                        if ($this->obbligoInvioMail === true) {
                            Out::msgStop("Attenzione", "É obbligatorio l'invio di almeno una PEC/Mail. Confermare l'invio per poter procedere.");
                            break;
                        }
                    case 'close-portlet':
                        if ($this->returnEventOnClose != '') {
                            $model = $this->returnModel;
                            $_POST = array();
                            $_POST['rowid'] = $this->valori['rowidChiamante'];
                            $_POST['event'] = $this->returnEvent;
                            $_POST['valori']['Inviata'] = 0;
                            $phpURL = App::getConf('modelBackEnd.php');
                            $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
                            $model();
                        }
                        $this->returnToParent(true);
                        break;
                }
                break;
            case 'onBlur':
                switch ($_POST['id']) {
                    
                }
                break;

            case 'returnanamedDestinatario':
                $proLib = new proLib();
                $anamed_rec = $proLib->GetAnamed($_POST['retKey'], 'rowid', 'si');
                Out::valore($this->nameForm . "_Destinatario", $anamed_rec["MEDNOM"]);
                Out::valore($this->nameForm . "_Email", $anamed_rec["MEDEMA"]);
                break;
        }
    }

    public function close() {
        App::$utente->removeKey($this->nameForm . '_allegati');
        App::$utente->removeKey($this->nameForm . '_tipo');
        App::$utente->removeKey($this->nameForm . '_returnEvent');
        App::$utente->removeKey($this->nameForm . '_returnModel');
        App::$utente->removeKey($this->nameForm . '_valori');
        App::$utente->removeKey($this->nameForm . '_sizeAllegati');
        App::$utente->removeKey($this->nameForm . '_returnEventOnClose');
        App::$utente->removeKey($this->nameForm . '_destinatari');
        App::$utente->removeKey($this->nameForm . '_daMail');
        App::$utente->removeKey($this->nameForm . '_obbligoInvioMail');
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
        Out::show($this->returnModel);
    }

    function checkDestinatari() {
        $nomi = "";
        foreach ($this->destinatari as $dest) {
            $trovato = false;
            if ($dest['MAIL'] == "") {
                $nomi .= "<b>" . $dest['NOME'] . "</b><br>";
                Out::msgInfo("Attenzione", "Controllare le seguenti mail perchè sembrano essere vuote:<br>$nomi");
                $trovato = true;
            }
        }
        if ($trovato) {
            return false;
        } else {
            return true;
        }
    }

    function Dettaglio() {
        foreach ($this->allegati as $key => $allegato) {
            if ($allegato['FILEORIG'] == '' || isset($allegato['FILEORIG']) === false) {
                $this->allegati[$key]['FILEORIG'] = $allegato['FILENAME'];
            }
        }
        Out::show($this->nameForm . '_divDestinatari');
        Out::hide($this->nameForm . '_divGridDestinatari');
        Out::hide($this->nameForm . '_AddMittDest');
        Out::hide($this->nameForm . '_divDaMail');
        switch ($this->tipo) {
            case 'passo':
                Out::hide($this->nameForm . '_divDestinatari');
                Out::delClass($this->nameForm . '_Email', "required");
                Out::show($this->nameForm . '_divGridDestinatari');
                if ($_POST['valori']['Destinatari']) {
                    $this->CaricaGriglia($this->gridDestinatari, $this->destinatari, '1', '2000', true);
                }
                if ($_POST['valori']['Destinatario']) {
                    $this->destinatari = array();
                    $this->destinatari[0]['MAIL'] = $_POST['valori']['Email'];
                    $this->destinatari[0]['NOME'] = $_POST['valori']['Destinatario'];
                    $this->CaricaGriglia($this->gridDestinatari, $this->destinatari, '1', '2000', true);
                }
                Out::valore($this->nameForm . '_Oggetto', $_POST['valori']['Oggetto']);
                Out::valore($this->nameForm . '_Procedimento', $_POST['valori']['Procedimento']);
                Out::valore($this->nameForm . '_Seq', $_POST['valori']['Seq']);
                Out::valore($this->nameForm . '_rowidChiamante', $_POST['valori']['rowidChiamante']);
                Out::attributo($this->nameForm . '_Destinatario', 'readonly', '0');
                Out::attributo($this->nameForm . '_Email', 'readonly', '0');
                Out::valore($this->nameForm . '_Corpo', $_POST['valori']['Corpo']);
                Out::codice('tinyActivate("' . $this->nameForm . '_Corpo");');
                Out::hide($this->gridAllegati . "_delGridRow");
                break;
            case 'ordineGiorno':
                Out::hide($this->nameForm . '_divPasso');
                Out::valore($this->nameForm . '_Destinatario', $_POST['valori']['Destinatario']);
                Out::valore($this->nameForm . '_Email', $_POST['valori']['Email']);
                Out::valore($this->nameForm . '_Oggetto', $_POST['valori']['Oggetto']);
                Out::valore($this->nameForm . '_Corpo', $_POST['valori']['Corpo']);
                Out::codice('tinyActivate("' . $this->nameForm . '_Corpo");');
                Out::attributo($this->nameForm . '_Destinatario', 'readonly', '1');
                Out::attributo($this->nameForm . '_Email', 'readonly', '1');
                Out::hide($this->gridAllegati . "_delGridRow");
                break;
            case 'protocollo':
                Out::hide($this->nameForm . '_divPasso');
                Out::hide($this->nameForm . '_divDestinatari');
                Out::show($this->nameForm . '_divGridDestinatari');
                if ($_POST['valori']['Destinatari']) {
                    $this->CaricaGriglia($this->gridDestinatari, $_POST['valori']['Destinatari'], '1', '2000', true);
                }
                /* Deselezione richiesta da civitanova 04/05/2016 */
                foreach ($this->destinatari as $key => $Destinatario) {
                    if ($Destinatario['SELEMAN'] === true) {
                        TableView::setSelection($this->gridDestinatari, $key);
                    }
                }
                Out::valore($this->nameForm . '_Oggetto', $_POST['valori']['Oggetto']);
                Out::valore($this->nameForm . '_Corpo', $_POST['valori']['Corpo']);
                if (App::$clientEngine == 'itaEngine') {
                    Out::codice('tinyActivate("' . $this->nameForm . '_Corpo");');
                }
                Out::delClass($this->nameForm . '_Email', "required");
                Out::hide($this->gridAllegati . "_delGridRow");
                /* Da Mail */
                if ($this->daMail) {
                    Out::show($this->nameForm . '_divDaMail');
                    $this->CreaComboDaMail();
                }
                break;
            case 'InviaProtocollo':
                Out::valore($this->nameForm . '_Destinatario', $_POST['valori']['Destinatario']);
                Out::valore($this->nameForm . '_Procedimento', $_POST['valori']['Procedimento']);
                Out::valore($this->nameForm . '_Email', $_POST['valori']['Email']);
                Out::valore($this->nameForm . '_Oggetto', $_POST['valori']['Oggetto']);
                Out::valore($this->nameForm . '_Corpo', $_POST['valori']['Corpo']);
                Out::codice('tinyActivate("' . $this->nameForm . '_Corpo");');
                Out::attributo($this->nameForm . '_Destinatario', 'readonly', '0');
                Out::attributo($this->nameForm . '_Email', 'readonly', '0');
                Out::attributo($this->nameForm . '_Oggetto', 'readonly', '0');
                Out::show($this->gridAllegati . "_delGridRow");
                break;
            case 'inoltra':
                Out::hide($this->nameForm . '_divPasso');
                Out::valore($this->nameForm . '_Oggetto', $_POST['valori']['Oggetto']);
                Out::valore($this->nameForm . '_Corpo', $_POST['valori']['Corpo']);
                Out::codice('tinyActivate("' . $this->nameForm . '_Corpo");');
                Out::hide($this->gridAllegati . "_delGridRow");
                Out::show($this->nameForm . '_AddMittDest');
                /* Da Mail */
                if ($this->daMail) {
                    Out::show($this->nameForm . '_divDaMail');
                    $this->CreaComboDaMail();
                }
                break;
            case 'rispondi':
                Out::hide($this->nameForm . '_divPasso');
                Out::valore($this->nameForm . '_Oggetto', $_POST['valori']['Oggetto']);
                Out::valore($this->nameForm . '_Corpo', $_POST['valori']['Corpo']);
                Out::valore($this->nameForm . '_Email', $_POST['valori']['Destinatario']);
                Out::valore($this->nameForm . '_Destinatario', $_POST['valori']['Destinatario']);
                Out::codice('tinyActivate("' . $this->nameForm . '_Corpo");');
                Out::hide($this->gridAllegati . "_delGridRow");
                Out::show($this->nameForm . '_AddMittDest');
                break;
            default :
                Out::valore($this->nameForm . '_Destinatario', $_POST['valori']['Destinatario']);
                Out::valore($this->nameForm . '_Email', $_POST['valori']['Email']);
                Out::valore($this->nameForm . '_Oggetto', $_POST['valori']['Oggetto']);
                Out::valore($this->nameForm . '_Corpo', $_POST['valori']['Corpo']);
                Out::attributo($this->nameForm . '_Destinatario', 'readonly', '0');
                Out::attributo($this->nameForm . '_Email', 'readonly', '0');
                Out::codice('tinyActivate("' . $this->nameForm . '_Corpo");');
                Out::hide($this->gridAllegati . "_delGridRow");
                break;
        }
        $this->CaricaGriglia($this->gridAllegati, $this->elaboraArrayAllegati());
    }

    function AggiungiDatiProtocollo() {
        $html = "";
        $html .= "<span style=\"font-size:20px;text-decoration:underline;\">N. Protocollo in Partenza:</span><span><b>  " . $_POST['valori']['ProtRic'] . "</b></span>";
        $html .= "<br><br>";
        $html .= "<table id=table1 border = 0>";
        $html .= "<td style=\"vertical-align:top;font-size:20px;text-decoration:underline\">Oggetto:</td>";
        $html .= "<td style=\"vertical-align:top;\"><b>  " . $_POST['valori']['OggettoProt'] . "</b></td>";
        $html .= "</table>";

        Out::html($this->nameForm . "_Corpo", $html);
    }

    function elaboraArrayAllegati() {
        foreach ($this->allegati as $key => $allegato) {
            $fileOrig = $allegato['FILEORIG'];
            $icon = utiIcons::getExtensionIconClass($allegato['FILENAME'], 32);
            $fileSize = $this->praLib->formatFileSize(filesize($allegato['FILEPATH']));
            $this->allegati[$key]['FILEORIG'] = str_replace("'", " ", $fileOrig);
            $this->allegati[$key]["FileIcon"] = "<span style = \"margin:2px;\" class=\"$icon\"></span>";
            $this->allegati[$key]["FileSize"] = $fileSize;
        }
        return $this->allegati;
    }

    function CaricaGriglia($_griglia, $_appoggio, $_tipo = '1', $pageRows = '1000', $selectAll = false) {
        $ita_grid01 = new TableView(
                $_griglia, array('arrayTable' => $_appoggio,
            'rowIndex' => 'idx')
        );
        if ($_tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($_griglia);
        TableView::clearGrid($_griglia);
        $ita_grid01->getDataPage('json');
        if ($selectAll === true) {
            TableView::setSelectAll($_griglia);
        }

        return;
    }

    public function CreaComboDaMail() {
        if (count($this->daMail) === 1) {
            Out::html($this->nameForm . '_DaMail', '');
            Out::select($this->nameForm . '_DaMail', 1, $this->daMail[0], 1, $this->daMail[0]);
        } else {
            Out::html($this->nameForm . '_DaMail', '');
            Out::select($this->nameForm . '_DaMail', 1, "", 1, "Account Predefinito");
            foreach ($this->daMail as $mail) {
                Out::select($this->nameForm . '_DaMail', 1, $mail, 0, $mail);
            }
        }
    }

}
