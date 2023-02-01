<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/Mail
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license 
 * @version    02.02.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlRic.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiEnte.class.php';

function emlArchivio() {
    $emlArchivio = new emlArchivio();
    $emlArchivio->parseEvent();
    return;
}

class emlArchivio extends itaModel {

    public $ITALWEB;
    public $nameForm = "emlArchivio";
    public $gridArchivio = "emlArchivio_gridArchivio";
    public $proLib;
    public $emlLib;

    function __construct() {
        parent::__construct();
        try {
            $this->proLib = new proLib();
            $this->emlLib = new emlLib();
            $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->creaSelect();
                $this->openRicerca();
                break;
            case 'dbClickRow':
            case 'editGridRow':
                switch ($_POST['id']) {
                    case $this->gridArchivio:
                        $model = 'emlViewer';
                        $_POST['event'] = 'openform';
                        $_POST['codiceMail'] = $_POST['rowid'];
                        $_POST['tipo'] = 'rowid';
                        $_POST['abilitaStampa'] = true;
                        itaLib::openForm($model);
                        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                        $model();
                        break;
                }
                break;

            case 'onClickTablePager':
                $id = $_POST['id'];
                switch ($id) {
                    case $this->gridArchivio:
                        TableView::clearGrid($id);
                        $gridScheda = new TableView($id, array(
                            'sqlDB' => $this->ITALWEB,
                            'sqlQuery' => $this->gridArchivioSQL()
                        ));
                        $gridScheda->setPageNum($_POST['page']);
                        $gridScheda->setPageRows($_POST['rows']);
                        $gridScheda->setSortIndex('MSGDATE');
                        $gridScheda->setSortOrder($_POST['sord']);
                        $gridScheda->getDataPageFromArray('json', $this->gridArchivioElabora($gridScheda->getDataArray()));
                        break;
                }
                break;

            case 'printTableToHTML':
                $utiEnte = new utiEnte();
                $ParametriEnte_rec = $utiEnte->GetParametriEnte();
                include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
                $itaJR = new itaJasperReport();
                $parameters = array("Sql" => $this->gridArchivioSQL() . ' ORDER BY MSGDATE', "Ente" => $ParametriEnte_rec['DENOMINAZIONE']);
                $itaJR->runSQLReportPDF($this->ITALWEB, 'emlArchivio', $parameters);
                break;

            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->gridArchivio:
                        emlRic::emlRicCollegate($this->nameForm, 'returnFromEmlRicCollegate', $this->emlLib, $_POST['rowid'], 'rowid');
                        break;
                }
                break;

            case 'onBlur':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elenca':
                        $this->openRisultato();
                        break;
                    case $this->nameForm . '_AltraRicerca':
                        $this->openRicerca();
                        break;

                    case $this->nameForm . '_SvuotaRicerca':
                        Out::clearFields($this->nameForm);
                        
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;

            case 'returnFromEmlRicCollegate':
                if (!$_POST['retKey']) {
                    break;
                }

                $model = 'emlViewer';
                $_POST['event'] = 'openform';
                $_POST['codiceMail'] = $_POST['retKey'];
                $_POST['tipo'] = 'rowid';
                $_POST['abilitaStampa'] = true;
                itaLib::openForm($model);
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $model();
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

    public function gridArchivioSQL() {
        $sql = "SELECT
                    *
                FROM
                    MAIL_ARCHIVIO
                WHERE
                    1 = 1";

        $search_fields = array(
            'SENDREC' => 'SENDREC',
            'READED' => 'READED',
            'ACCOUNT' => 'ACCOUNT',
            'MSGID' => 'MSGID',
            'OGGETTO' => 'SUBJECT',
            'FROMADDR' => 'FROMADDR',
            'TOADDR' => 'TOADDR',
            'CC' => 'CCADDR',
            'BCC' => 'BCCADDR',
            'ATTACHMENTS' => 'ATTACHMENTS'
        );

        foreach ($search_fields as $field => $dbfield) {
            if ($_POST[$this->nameForm . '_' . $field] != '') {
                $sql .= " AND " . $this->ITALWEB->strUpper($dbfield) . " LIKE '%" . strtoupper($_POST[$this->nameForm . '_' . $field]) . "%' ";
            }
        }
        if ($_POST[$this->nameForm . '_PECTIPO']) {
            if ($_POST[$this->nameForm . '_PECTIPO'] == 'normale') {
                $sql .= " AND PECTIPO = '' ";
            } else {
                $sql .= " AND " . $this->ITALWEB->strUpper('PECTIPO') . " = '" . strtoupper($_POST[$this->nameForm . '_PECTIPO']) . "' ";
            }
        }


        if ($_POST[$this->nameForm . '_DADATA']) {
            $sql .= " AND " . $this->ITALWEB->subString('MSGDATE', 1, 10) . " >= " . intval($_POST[$this->nameForm . '_DADATA']) . " ";
        }

        if ($_POST[$this->nameForm . '_ADATA']) {
            $sql .= " AND " . $this->ITALWEB->subString('MSGDATE', 1, 10) . " <= " . intval($_POST[$this->nameForm . '_ADATA']) . " ";
        }

        if ($_POST[$this->nameForm . '_TIPOINTEROPERABILE']) {
            $sql .= " AND TIPOINTEROPERABILE = '" . $_POST[$this->nameForm . '_TIPOINTEROPERABILE'] . "' ";
        }
        App::log($sql);

        return $sql;
    }

    public function gridArchivioElabora($result_tab) {
        $ArrFileErr = array();
        foreach ($result_tab as $key => $mail) {
            $metadata = unserialize($mail["METADATA"]);
            $ini_tag = "<p style = 'font-weight:lighter;'>";
            $fin_tag = "</p>";
            $icon_mail = "<span title = \"Messaggio letto.\" class=\"ita-icon ita-icon-apertagray-24x24\" style = \"float:left;display:inline-block;\"></span>";
            if ($mail['READED'] == 0) {
                $ini_tag = "<p style = 'font-weight:900;'>";
                $fin_tag = "</p>";
                $icon_mail = "<span title = \"Messaggio da leggere.\" class=\"ita-icon ita-icon-chiusagray-24x24\" style = \"float:left;display:inline-block;\"></span>";
            }

            if ($result_tab[$key]['INTEROPERABILE'] > 0) {
                $result_tab[$key]['SEGNATURA'] = '<span title ="Interoperabilità" class="ita-icon ita-icon-flag-it-24x24"></span>';
            }
            $FattElett = false;
            if (in_array($result_tab[$key]['FROMADDR'], $this->elencoMailSdi) && $result_tab[$key]['PECTIPO'] == 'posta-certificata') {
                $result_tab[$key]['SEGNATURA'] = '<span title ="Fattura Elettronica" class="ita-icon ita-icon-euro-blue-24x24"></span>';
                $FattElett = true;
            } else {
                if ($metadata['emlStruct']['ita_PEC_info'] != "N/A") {
                    if ($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']) {
                        if (in_array($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'], $this->elencoMailSdi) &&
                                $result_tab[$key]['PECTIPO'] == 'posta-certificata') {
                            $result_tab[$key]['SEGNATURA'] = '<span title ="Fattura Elettronica" class="ita-icon ita-icon-euro-blue-24x24"></span>';
                            $FattElett = true;
                        }
                    }
                }
            }

            $result_tab[$key]["DATA"] = date('d/m/Y', strtotime(substr($mail['MSGDATE'], 0, 8)));
            $result_tab[$key]["ORA"] = substr($mail['MSGDATE'], 8);
            $pec = $result_tab[$key]['PECTIPO'];
            if ($pec != '') {
                $icon_mail = "<span title = \"PEC " . $pec . ", letto.\" class=\"ita-icon ita-icon-apertagreen-24x24\" style = \"float:left;display:inline-block;\"></span>";
                if ($mail['READED'] == 0) {
                    $icon_mail = "<span title = \"PEC " . $pec . ", da leggere.\" class=\"ita-icon ita-icon-chiusagreen-24x24\" style = \"float:left;display:inline-block;\"></span>";
                }
                $result_tab[$key]["PEC"] = $pec;
            }
            $result_tab[$key]['PRESALLEGATI'] = $icon_mail;
            if ($result_tab[$key]['CLASS'] == '@INOLTRATA_PROTOCOLLO@') {
                $result_tab[$key]['PRESALLEGATI'] = $this->CaricaInoltrato($mail);
            }
            if ($mail['ATTACHMENTS'] != '') {
                $result_tab[$key]['PRESALLEGATI'] .= '<span title = "Presenza Allegati" style="margin:2px; display:inline-block; vertical-align:middle;" class="ita-icon ita-icon-clip-16x16" ></span>';
            }
            if ($metadata['emlStruct']['ita_PEC_info'] != "N/A") {
                if ($metadata['emlStruct']['ita_PEC_info']['dati_certificazione']) {
                    $result_tab[$key]['FROMADDRORIG'] = '<p style="background:lightgreen;color: darkgreen;">' . $metadata['emlStruct']['ita_PEC_info']['dati_certificazione']['mittente'] . '</p>';
                }
            }

            $opacity = "";
            if ($mail['GIORNI'] && $FattElett == true) {
                $opacity1 = (($mail["GIORNI"] <= 15) ? $mail["GIORNI"] * (100 / 15) : 100) / 100;
                $opacity = "background:rgba(255,0,0,$opacity1);";
            }
            $result_tab[$key]["GIORNI"] = '<div style="height:100%;padding-left:2px;text-align:center;' . $opacity . '"><span style="vertical-align:middle;opacity:1.00;">' . $result_tab[$key]["GIORNI"] . '</span></div>';

            $result_tab[$key]['FROMADDR'] = $ini_tag . $result_tab[$key]['FROMADDR'] . $fin_tag;
            $result_tab[$key]['SUBJECT'] = $ini_tag . $result_tab[$key]['SUBJECT'] . $fin_tag;
            $result_tab[$key]['ACCOUNT'] = $ini_tag . $result_tab[$key]['ACCOUNT'] . $fin_tag;
            $result_tab[$key]['DATA'] = $ini_tag . $result_tab[$key]['DATA'] . $fin_tag;
            $result_tab[$key]['ORA'] = $ini_tag . $result_tab[$key]['ORA'] . $fin_tag;
            $result_tab[$key]['PEC'] = $ini_tag . $result_tab[$key]['PEC'] . $fin_tag;

            $sql = sprintf('SELECT * FROM MAIL_ARCHIVIO WHERE IDMAILPADRE = "%s" OR IDMAIL = "%s"', $result_tab[$key]['IDMAIL'], $result_tab[$key]['IDMAILPADRE']);
            $mailarchivio_tab = itaDB::DBSQLSelect($this->ITALWEB, $sql);
            if (count($mailarchivio_tab)) {
                $result_tab[$key]['COLLEGATE'] = '<i class="ui-icon ui-icon-structure"></i>';
            }


//            $fileTest = $this->emlLib->SetDirectory($mail['ACCOUNT']) . $mail['DATAFILE'];
//            if (!file_exists($fileTest)) {
//                $ArrTmp = array('ROWID' => $result_tab[$key]['ROWID'], 'STRUID' => $result_tab[$key]['STRUID'], 'MSGDATE' => $result_tab[$key]['MSGDATE'], 'IDMAIL' => $result_tab[$key]['IDMAIL'], 'DATAFILE' => $fileTest);
//                $ArrFileErr[] = $ArrTmp;
//                $result_tab[$key]['PRESALLEGATI'] = $ini_tag . '<div style="background-color:red;" >***</div>' . $fin_tag;
//            }
        }
        //Out::msgInfo('ErrMail', print_r($ArrFileErr, true));
        return $result_tab;
    }

    private function CaricaInoltrato($MailArchivio_rec) {
        $Inoltrato = '';
        $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE IDMAILPADRE = '" . $MailArchivio_rec['IDMAIL'] . "' ";
        $mail_tab = $this->emlLib->getGenericTab($sql);
        foreach ($mail_tab as $mail_rec) {
            $BustaMailConsegna = $BustaMailAccettaz = '';
            $Inoltrato = '<div class="ita-html" style="display:inline-block;">';
            $iconabusta = 'ita-icon-chiusagray-24x24';
            if ($mail_rec['PECTIPO']) {
                $iconabusta = 'ita-icon-chiusagreen-24x24';
                //Controllo se il messaggio è stato inoltrato o accettato
                $retRic = $this->proLib->checkMailRic($mail_rec['IDMAIL']);
                if ($retRic['ACCETTAZIONE']) {
                    $BustaMailAccettaz = '<a href="#" id="' . $retRic['ACCETTAZIONE'] . '" class="ita-hyperlink {event:\'apriMailInoltrata\'}"><span title="Accettata" class="ui-icon ui-icon-check"></a>' . '';
                }
                if ($retRic['CONSEGNA']) {
                    $BustaMailConsegna = '<a href="#" id="' . $retRic['CONSEGNA'] . '" class="ita-hyperlink {event:\'apriMailInoltrata\'}"><span title="Consegnata" class="ui-icon ui-icon-check"></a>' . '';
                }
            }
            $DataInoltro = date('d/m/Y', strtotime(substr($mail_rec['MSGDATE'], 0, 8)));
            $OraInoltro = substr($mail_rec['MSGDATE'], 8);
            $icon_mail = "<span  class=\"ita-icon $iconabusta\" style = \"display:inline-block;\"></span>";
            $InoltratoA = 'Email inoltrata a: <span style="text-transform: lowercase">' . $mail_rec['TOADDR'] . '</span>' .
                    "<br>Il giorno <b>$DataInoltro</b> alle ore <b>$OraInoltro</b> ";
            $BustaMailInoltrata = $icon_mail . '<span title ="' . htmlspecialchars($InoltratoA) . '" style="display:inline-block; position:relative; margin-left:-12px; top:2px; " class="ita-tooltip ita-icon ita-icon-arrow-green-dx-16x16"></span>';
            $BustaMailInoltrata = '<a href="#" id="' . $mail_rec['IDMAIL'] . '" class="ita-hyperlink {event:\'apriMailInoltrata\'}">' . $BustaMailInoltrata . '</a>' . '';
            //Busta Inoltrata
            $Inoltrato .= '<span style="display:inline-block; vertical-align:middle;">' . $BustaMailInoltrata . ' </span>' .
                    '<span style="display:inline-block; vertical-align:middle;">' . $BustaMailAccettaz . '</span>' .
                    '<span style="display:inline-block; vertical-align:middle;">' . $BustaMailConsegna . '</span>';

            $Inoltrato .= '</div>';
        }
        return $Inoltrato;
    }

    public function mostraForm($div) {
        Out::hide($this->nameForm . '_divRicerca');
        Out::hide($this->nameForm . '_divRisultato');
        Out::show($this->nameForm . '_' . $div);
    }

    public function mostraButtonBar($buttons = array()) {
        Out::hide($this->nameForm . '_Elenca');
        Out::hide($this->nameForm . '_AltraRicerca');
        Out::hide($this->nameForm . '_SvuotaRicerca');
        foreach ($buttons as $button) {
            Out::show($this->nameForm . '_' . $button);
        }
    }

    public function creaSelect() {
        Out::select($this->nameForm . '_SENDREC', 1, '', 1, 'Tutte');
        Out::select($this->nameForm . '_SENDREC', 1, 'S', 0, 'Inviate');
        Out::select($this->nameForm . '_SENDREC', 1, 'R', 0, 'Ricevute');

        Out::select($this->nameForm . '_READED', 1, '', 1, 'Tutte');
        Out::select($this->nameForm . '_READED', 1, '1', 0, 'Lette');
        Out::select($this->nameForm . '_READED', 1, '0', 0, 'Non lette');

        Out::select($this->nameForm . '_TIPOINTEROPERABILE', 1, '', 1, 'Tutti');
        Out::select($this->nameForm . '_TIPOINTEROPERABILE', 1, emlLib::TIPOMSG_SEGNATURA, 0, 'Protocollo Interoperabile');
        Out::select($this->nameForm . '_TIPOINTEROPERABILE', 1, emlLib::TIPOMSG_CONFERMA, 0, 'Notifica di Coferma');
        Out::select($this->nameForm . '_TIPOINTEROPERABILE', 1, emlLib::TIPOMSG_AGGIORNAMENTO, 0, 'Notifica di Aggiornamento');
        Out::select($this->nameForm . '_TIPOINTEROPERABILE', 1, emlLib::TIPOMSG_ECCEZIONE, 0, 'Notifica di Eccezione');
        Out::select($this->nameForm . '_TIPOINTEROPERABILE', 1, emlLib::TIPOMSG_ANNULLAMENTO, 0, 'Notifica di Annullamento');

//        $mail_account_tab = ItaDB::DBSQLSelect($this->ITALWEB, "SELECT * FROM MAIL_ACCOUNT");
        $sql = "( SELECT MAILADDR FROM MAIL_ACCOUNT )
                UNION DISTINCT
                ( SELECT DISTINCT(ACCOUNT) AS MAILADDR FROM MAIL_ARCHIVIO )";

        $mail_account_tab = ItaDB::DBSQLSelect($this->ITALWEB, $sql);

        Out::select($this->nameForm . '_ACCOUNT', 1, '', 1, '');
        foreach ($mail_account_tab as $mail_account_rec) {
            Out::select($this->nameForm . '_ACCOUNT', 1, $mail_account_rec['MAILADDR'], 0, $mail_account_rec['MAILADDR']);
        }

        // Mail Tipo
        Out::select($this->nameForm . '_PECTIPO', 1, '', 1, 'Tutti');
        Out::select($this->nameForm . '_PECTIPO', 1, 'normale', 1, 'Posta Normale');
        Out::select($this->nameForm . '_PECTIPO', 1, 'posta-certificata', 1, 'Posta Certificata');
        Out::select($this->nameForm . '_PECTIPO', 1, 'accettazione', 1, 'Accettazioni');
        Out::select($this->nameForm . '_PECTIPO', 1, 'non-accettazione', 1, 'Non Accettazioni');
        Out::select($this->nameForm . '_PECTIPO', 1, 'presa-in-carico', 1, 'Presa in carico');
        Out::select($this->nameForm . '_PECTIPO', 1, 'avvenuta-consegna', 1, 'Avvenuta Consegna');
        Out::select($this->nameForm . '_PECTIPO', 1, 'errore-consegna', 1, 'Errore di consegna');
        Out::select($this->nameForm . '_PECTIPO', 1, 'preavviso-errore-consegna', 1, 'Preavviso Errore di Consegna');
        Out::select($this->nameForm . '_PECTIPO', 1, 'rilevazione-virus', 1, 'Rilevazione Virus');
    }

    public function openRicerca() {
        $this->mostraForm('divRicerca');
        $this->mostraButtonBar(array('Elenca', 'SvuotaRicerca'));

        TableView::disableEvents($this->gridArchivio);
        TableView::clearGrid($this->gridArchivio);

        Out::setFocus($this->nameForm, $this->nameForm . '_SENDREC');
    }

    public function openRisultato() {
        $this->mostraForm('divRisultato');
        $this->mostraButtonBar(array('AltraRicerca'));

        TableView::enableEvents($this->gridArchivio);
        TableView::reload($this->gridArchivio);
    }

    public function ControllaElencoMail() {
        $sql = $this->gridArchivioSQL();
        $mail_account_tab = ItaDB::DBSQLSelect($this->ITALWEB, $sql, true);
        foreach ($mail_account_tab as $mail) {
            $fileTest = $this->emlLib->SetDirectory($mail['ACCOUNT']) . $mail['DATAFILE'];
            if (!file_exists($fileTest)) {
                $ArrTmp = array('ROWID' => $mail['ROWID'], 'STRUID' => $mail['STRUID'], 'MSGDATE' => $mail['MSGDATE'], 'IDMAIL' => $mail['IDMAIL'], 'DATAFILE' => $fileTest);
                $ArrFileErr[] = $ArrTmp;
            }
        }
        Out::msgInfo('ErrMail', print_r($ArrFileErr, true));
    }

}
