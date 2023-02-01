<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Simone Franchi <simone.franchi@italsoft.eu>
 * @author     
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    26.06.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */

include_once ITA_BASE_PATH . '/apps/Pratiche/praLibCart.class.php';


class praLibCart {

    public $ITALWEB;
    public $PRAM_DB;

    function __construct($ditta = '') {
        try {
            if ($ditta) {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ditta);
                $this->ITALWEB = ItaDB::DBOpen('ITALWEB', $ditta);
            } else {
                $this->PRAM_DB = ItaDB::DBOpen('PRAM');
                $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
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

    function getITALWEB() {
        return $this->ITALWEB;
    }

    function setITALWEB($ITALWEB) {
        $this->ITALWEB = $ITALWEB;
    }

    public function getCart_stimolo($Codice, $tipoCodice = 'idmessaggio', $multi = false) {
        if ($tipoCodice == 'idmessaggio') {
            $sql = "SELECT * FROM CART_STIMOLO WHERE IDMESSAGGIO='" . $Codice . "'";
        } else if ($tipoCodice == 'idpratica') {
            $sql = "SELECT * FROM CART_STIMOLO WHERE IDPRATICA='" . $Codice . "'";
        } /* else if ($tipoCodice == 'protocollo') {
          $sql = "SELECT * FROM PROGES WHERE GESNPR='" . $Codice . "'";
          } else if ($tipoCodice == 'antecedente') {
          $sql = "SELECT * FROM PROGES WHERE GESPRE='" . $Codice . "'";
          } else if ($tipoCodice == 'codiceProcedimento') {
          $sql = "SELECT * FROM PROGES WHERE GESCODPROC='" . $Codice . "'";
          } */ else {
            $sql = "SELECT * FROM CART_STIMOLO WHERE ROW_ID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function getCart_invio($Codice, $tipoCodice = 'idmessaggio', $multi = false) {
        if ($tipoCodice == 'idmessaggio') {
            $sql = "SELECT * FROM CART_INVIO WHERE IDMESSAGGIO='" . $Codice . "'";
        } /* else if ($tipoCodice == 'protocollo') {
          $sql = "SELECT * FROM PROGES WHERE GESNPR='" . $Codice . "'";
          } else if ($tipoCodice == 'antecedente') {
          $sql = "SELECT * FROM PROGES WHERE GESPRE='" . $Codice . "'";
          } else if ($tipoCodice == 'codiceProcedimento') {
          $sql = "SELECT * FROM PROGES WHERE GESCODPROC='" . $Codice . "'";
          } */ else {
            $sql = "SELECT * FROM CART_INVIO WHERE ROW_ID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function getCart_stimoloFile($Codice, $tipoCodice = 'idFile', $multi = false) {
        if ($tipoCodice == 'idFile') {
            $sql = "SELECT * FROM CART_STIMOLOFILE WHERE IDFILE='" . $Codice . "'";
        } else if ($tipoCodice == 'id') {
            $sql = "SELECT * FROM CART_STIMOLOFILE WHERE ID='" . $Codice . "'";
        } else if ($tipoCodice == 'idMessaggioCart') {
          $sql = "SELECT * FROM CART_STIMOLOFILE WHERE IDMSGCARTSTIMOLO='" . $Codice . "'";
        } else if ($tipoCodice == 'file') {
          $sql = "SELECT * FROM CART_STIMOLOFILE WHERE IDMSGCARTSTIMOLO='" . $Codice['IDCARTSTIMOLO'] . "' AND NOMEFILE = '" . $Codice['FILENAME'] . "'" ;
        } else if ($tipoCodice == 'modulo') {
          $sql = "SELECT * FROM CART_STIMOLOFILE WHERE IDMSGCARTSTIMOLO='" . $Codice['IDCARTSTIMOLO'] . "' AND TIPOFILE = '" . $Codice['TIPOFILE'] . "'"
                  . " AND USOMODELLO = '" . $Codice['USOMODELLO'] . "'" ;
        }  else {
            $sql = "SELECT * FROM CART_STIMOLOFILE WHERE ROW_ID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function getCart_attivita($Codice, $tipoCodice = 'codice', $multi = false) {
        if ($tipoCodice == 'codice') {
            $sql = "SELECT * FROM CART_ATTIVITA WHERE CODICE='" . $Codice . "'";
        } /* else if ($tipoCodice == 'protocollo') {
          $sql = "SELECT * FROM PROGES WHERE GESNPR='" . $Codice . "'";
          } */ else {
            $sql = "SELECT * FROM CART_ATTIVITA WHERE ROW_ID='$Codice'";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }
    
    
    public function getLastId($Tabella, $CampoId = 'ID') {
        $ultimoId = 1;
        $sql = "SELECT * FROM " . $Tabella . " ORDER BY " . $CampoId . " DESC";
        $record = ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
        if ($record) {
            $ultimoId = $record[$CampoId] + 1;
        }

        return $ultimoId;
    }

    public function getRecordCart($tabella, $condizione, $multi = false) {

        $sql = "SELECT * FROM " . $tabella . " " . $condizione;
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function getDurationGiorni($xsDuration = 'P0Y0M1DT0H0M0S') {

        $dt = new DateTime();
        $dt->add(new DateInterval($xsDuration));
        $interval = $dt->diff(new DateTime())->days;
        return $interval;
    }

    public function SetDirectoryCart($codicePraticaCart, $tipo = "STIMOLO", $crea = true, $ditta = '') {
        if ($ditta == '') {
            $ditta = App::$utente->getKey('ditta');
        }
        switch ($tipo) {
            case "STIMOLO":
                $d_nome = $tipo . '/' . $codicePraticaCart ;
                break;
            case "INVIO":
                $d_nome = $tipo . '/' . $codicePraticaCart ;
                break;
        }
        $d_dir = Config::getPath('general.itaCARTWsData') . 'ente' . $ditta . '/';
        if (!is_dir($d_dir . $d_nome)) {
            if ($crea == true) {
                if (!mkdir($d_dir . $d_nome, 0777, true)) {
                    return false;
                }
            }
        }
        return $d_dir . $d_nome;
    }

    public function getDatetimeNow($datetime = ''){
        $tz_object = new DateTimeZone('Europe/Rome');

        if (!$datetime){
            $datetime = new DateTime();
        }
        $datetime->setTimezone($tz_object);
        return $datetime->format('Y-m-d\TH:i:s');

    }

    public function GetIconCartInvio($idCart, $chiavePasso, $tipo = "") {

        //$icon['vediMail'] = "<span title=\"Vedi Mail\" class=\"ui-icon ui-icon-mail-closed\" style=\"display:inline-block;\"></span>";
        $icon['vediMail'] = "<span title=\"Vedi Mail\" class=\"ui-icon ui-icon-feed\" style=\"display:inline-block;\"></span>";

        $icon['accDest'] = "<span title=\"Accettazione non Prevista per il CART\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>";
        $icon['conDest'] = "<span title=\"Consegna non Ricevuta\" class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;\"></span>";
        $icon['sboccaDest'] = "<span title=\"Annulla invio CART a Destinatario\" class=\"ui-icon ui-icon-unlocked\" style=\"display:inline-block;\"></span>";

        $sql = "SELECT * FROM CART_INVIO WHERE CART_INVIO.IDMESSAGGIO = '" . $idCart . "'";
        $cart_invio_rec = ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
        if ($cart_invio_rec) {
            
            if ($cart_invio_rec['DATARICEZIONE']){
                $icon['conDest'] = "<span title=\"Consegna Ricevuta\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>";
                
            }

            if ($cart_invio_rec['IDMSGANNULLATO']){
                $icon['sboccaDest'] = "<span title=\"Messaggio Annullato\" class=\"ui-icon ui-icon-circle-close\" style=\"display:inline-block;\"></span>";
            }
            
        }
        
/*

        $praMail_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRAMAIL WHERE COMIDMAIL='$idMail' AND COMPAK='$chiavePasso' AND ISRICEVUTA=1", true);
        $icon = array();

        if ($tipo == "PASSO") {
            $icon['accettazione'] = "<div style=\"display:inline-block;\">
                                         <span class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;vertical-align:middle;\"></span>
                                         <div style=\"display:inline-block;vertical-align:middle;\">Accettazione non ricevuta</div> 
                                     </div>";
            $descAcc = "Accettazione Ricevuta";
            $icon['consegna'] = "<div style=\"display:inline-block;\">
                                         <span class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;vertical-align:middle;\"></span>
                                         <div style=\"display:inline-block;vertical-align:middle;\">Consegna non ricevuta</div> 
                                 </div>";
            $descCon = "Avvenuta Consegna";
        }

//
//Se c'è l'id mail metto la lentina su tabella destinatari
//
        if ($idMail) {
            $icon['vediMail'] = "<span title=\"Vedi Mail\" class=\"ui-icon ui-icon-mail-closed\" style=\"display:inline-block;\"></span>";
        }

//
// Quando la mail è inviata e non sono ancora collegate le ricevute metto la X
//
        if (!$praMail_tab) {
            if ($idMail) {
                $icon['accDest'] = "<span title=\"Accettazione non Ricevuta\" class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;\"></span>";
                $icon['conDest'] = "<span title=\"Consegna non Ricevuta\" class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;\"></span>";
                $icon['sboccaDest'] = "<span title=\"Sblocca invio a Destinatario\" class=\"ui-icon ui-icon-unlocked\" style=\"display:inline-block;\"></span>";
            }
        }

//
//Mi scorro PRAMAIL per trovare l' accettazione e mettere i bottoni nel dettaglio del destinatario e le icone nella tabela
//
        foreach ($praMail_tab as $praMail_rec) {
            if ($praMail_rec['TIPORICEVUTA'] == emlMessage::PEC_TIPO_ACCETTAZIONE) {
                if ($tipo == "PASSO") {
                    $icon['accettazione'] = "<button id=\"praGestDestinatari_VediMailAcc\" class=\"ita-button ita-element-animate ui-corner-all ui-state-default\"
                                               title=\"Vedi Mail Accettazione\" name=\"praPasso_VediMailAcc\" type=\"button\">
                                              <div id=\"praPasso_acc_icon_left\" class=\"ita-button-element
                                              ita-button-icon-left ui-icon ui-icon-check\" style=\"\"></div>
                                         </button>$descAcc";
                    $icon['IDMAILACC'] = $praMail_rec['IDMAIL'];
                } else {
                    $icon['accettazione'] = "<span title=\"Accettazione Ricevuta\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>$descAcc";
                    $icon['accDest'] = "<span title=\"Accettazione Ricevuta\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>";
                    $icon['sboccaDest'] = "<span title=\"Sblocca invio a Destinatario\" class=\"ui-icon ui-icon-unlocked\" style=\"display:inline-block;\"></span>";
                }
                break;
            }
        }

//
//Mi scorro PRAMAIL per trovare la consegna e mettere i bottoni nel dettaglio del destinatario e le icone nella tabela. Se non c'è metto la X
//
        foreach ($praMail_tab as $praMail_rec) {
            if ($praMail_rec['TIPORICEVUTA'] == emlMessage::PEC_TIPO_AVVENUTA_CONSEGNA) {
                if ($tipo == "PASSO") {
                    $icon['consegna'] = "<button id=\"praGestDestinatari_VediMailCons\" class=\"ita-button ita-element-animate ui-corner-all ui-state-default\"
                                               title=\"Vedi Mail Consegna\" name=\"praPasso_VediMailCons\" type=\"button\">
                                              <div id=\"praPasso_cons_icon_left\" class=\"ita-button-element
                                              ita-button-icon-left ui-icon ui-icon-check\" style=\"\"></div>
                                         </button>$descCon";
                    $icon['IDMAILCON'] = $praMail_rec['IDMAIL'];
                } else {
                    $icon['consegna'] = "<span title=\"Avvenuta Consegna\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>$descCon";
                    $icon['conDest'] = "<span title=\"Avvenuta Consegna\" class=\"ui-icon ui-icon-check\" style=\"display:inline-block;\"></span>";
                    $icon['sboccaDest'] = "<span title=\"Sblocca invio a Destinatario\" class=\"ui-icon ui-icon-unlocked\" style=\"display:inline-block;\"></span>";
                }
                break;
            } else {
                $icon['conDest'] = "<span title=\"Consegna non ricevuta\" class=\"ui-icon ui-icon-closethick\" style=\"display:inline-block;\"></span>";
            }
        }
        */
        
        return $icon;
    }

    public function getTipoProcedimento($gesnum){
        $tipoProcedimento = '';
        $codice = array();
        $codice['GESNUM'] = $gesnum;
        $codice['PROPAK'] = '';

        $praLib = new praLib();
        
        $praFoList_rec = $praLib->GetPrafolist($codice, 'gesnum', false);
        if ($praFoList_rec) {
            $cart_stimolo_rec = $this->getCart_stimolo($praFoList_rec['FOPRAKEY']);
            if ($cart_stimolo_rec) {
                $tipoProcedimento = $cart_stimolo_rec['TIPOPROCEDIMENTO'];
            }

        }
        return $tipoProcedimento;
    }
    
    
}
