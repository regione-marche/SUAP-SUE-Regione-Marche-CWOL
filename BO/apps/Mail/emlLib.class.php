<?php

/**
 *
 * LIBRERIA EMAIL
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Email
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    26.09.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
class emlLib {

    const TIPOMSG_SEGNATURA = 'Segnatura';
    const TIPOMSG_CONFERMA = 'Conferma';
    const TIPOMSG_ANNULLAMENTO = 'Annullamento';
    const TIPOMSG_AGGIORNAMENTO = 'Aggiornamento';
    const TIPOMSG_ECCEZIONE = 'Eccezione';
    const CUST_HEAD_PEC_TIPO_RICEVUTA = "X-TipoRicevuta";
    const CUST_HEAD_PEC_TIPO_RICEVUTA_BREVE = 'breve';

    public $ITALWEB;

    public function setITALWEB($ITALWEB) {
        $this->ITALWEB = $ITALWEB;
    }

    public function getITALWEB() {
        if (!$this->ITALWEB) {
            try {
                $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB;
    }

    public function getGenericTab($sql, $multi = true) {
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    /**
     * Elenca gli account disponibili
     * @param string $domain filtro per dominio di appartenza
     * @return array
     */
    public function getMailAccountList($domain = '') {
        $sql = "SELECT MAIL_ACCOUNT.ROWID AS ROWID, 
                MAIL_ACCOUNT.NAME AS NAME,
                MAIL_ACCOUNT.MAILADDR AS MAILADDR,
                MAIL_ACCOUNT.USER AS USER,
                MAIL_ACCOUNT.PASSWORD AS PASSWORD,
                MAIL_ACCOUNT.DOMAIN AS DOMAIN,
                MAIL_DOMAIN.SMTPHOST AS SMTPHOST,
                MAIL_DOMAIN.POP3HOST AS POP3HOST,
                MAIL_DOMAIN.SMTPPORT AS SMTPPORT,
                MAIL_DOMAIN.POP3PORT AS POP3PORT,
                MAIL_DOMAIN.SMTPSECURE AS SMTPSECURE,
                MAIL_DOMAIN.POP3SECURE AS POP3SECURE,
                MAIL_DOMAIN.POP3REALM AS POP3REALM,
                MAIL_DOMAIN.POP3AUTHM AS POP3AUTHM,
                MAIL_DOMAIN.POP3WORKST AS POP3WORKST,
                MAIL_DOMAIN.DELMSG AS DELMSG,
                MAIL_DOMAIN.DELWAIT AS DELWAIT
                FROM MAIL_ACCOUNT LEFT OUTER JOIN MAIL_DOMAIN 
                ON MAIL_ACCOUNT.DOMAIN = MAIL_DOMAIN.NAME";
        if ($domain) {
            $sql .= " WHERE DOMAIN='$domain'";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, true);
    }

    /**
     * Fornisce i dati di un account collegando le tabelle MAIL_ACCOUTN e MAIL_DOMAIN
     * @param string $codice Indirizzo mail identificativo dell'account o rowid nella tabella MAIL_ACCOUNT
     * @param string $tipo 'indirizzo' cerca per indirizzo mail, 'rowid' cerca per CAMPO ROWID della tabella MAIL_ACCOUNT
     * @return array record con i dati del account
     */
    public function getMailAccount($codice, $tipo = 'indirizzo') {
        $sql = "SELECT MAIL_ACCOUNT.ROWID AS ROWID, 
                MAIL_ACCOUNT.NAME AS NAME,
                MAIL_ACCOUNT.MAILADDR AS MAILADDR,
                MAIL_ACCOUNT.USER AS USER,
                MAIL_ACCOUNT.PASSWORD AS PASSWORD,
                MAIL_ACCOUNT.CUSTOMHEADERS AS CUSTOMHEADERS,
                MAIL_ACCOUNT.DOMAIN AS DOMAIN,
                MAIL_ACCOUNT.SPOOLSENDDELAY AS SPOOLSENDDELAY,
                MAIL_DOMAIN.SMTPHOST AS SMTPHOST,
                MAIL_DOMAIN.POP3HOST AS POP3HOST,
                MAIL_DOMAIN.SMTPPORT AS SMTPPORT,
                MAIL_DOMAIN.POP3PORT AS POP3PORT,
                MAIL_DOMAIN.SMTPSECURE AS SMTPSECURE,
                MAIL_DOMAIN.POP3SECURE AS POP3SECURE,
                MAIL_DOMAIN.POP3REALM AS POP3REALM,
                MAIL_DOMAIN.POP3AUTHM AS POP3AUTHM,
                MAIL_DOMAIN.POP3WORKST AS POP3WORKST,
                MAIL_DOMAIN.DELMSG AS DELMSG,
                MAIL_DOMAIN.DELWAIT AS DELWAIT,
                MAIL_DOMAIN.ISPEC AS ISPEC
                FROM MAIL_ACCOUNT LEFT OUTER JOIN MAIL_DOMAIN 
                ON MAIL_ACCOUNT.DOMAIN = MAIL_DOMAIN.NAME";
        if ($tipo == 'indirizzo') {
            $sql .= " WHERE MAILADDR='$codice'";
        } else {
            $sql .= " WHERE MAIL_ACCOUNT.ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
    }

    public function getMailDomain($codice, $tipo = 'name') {
        if ($tipo == 'name') {
            $sql = "SELECT * FROM MAIL_DOMAIN WHERE NAME='$codice'";
        } else {
            $sql = "SELECT * FROM MAIL_DOMAIN WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
    }

    public function getMailFiltri($codice, $tipo = 'account', $multi = false) {
        if ($tipo == 'account') {
            $sql = "SELECT * FROM MAIL_FILTRI WHERE ACCOUNT='$codice' ORDER BY SEQUENZA";
        } else {
            $sql = "SELECT * FROM MAIL_FILTRI WHERE ROWID=$codice";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function getMailArchivio($codice, $tipo = 'id', $where = '', $multi = false) {
        if ($tipo == 'id') {
            $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE IDMAIL='$codice'";
        } else if ($tipo == 'where') {
            $sql = "SELECT * FROM MAIL_ARCHIVIO $where";
        } else if ($tipo == 'msgid') {
            $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE MSGID='$codice'";
        } else if ($tipo == 'idmailpadre') {
            $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE IDMAILPADRE='$codice'";
            $multi = true;
        } else {
            $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE ROWID='$codice'";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function SetDirectorySpooler($idPackage, $ditta = '') {
        if (ctype_digit($idPackage) === false) {
            return false;
        }
        if ($ditta == '') {
            $ditta = App::$utente->getKey('ditta');
        }

        $d_dir = Config::getPath('general.itaMailSpooler') . 'ente' . $ditta . '/' . $idPackage . "/";
        if (!is_dir($d_dir)) {
            if (!@mkdir($d_dir, 0777, true)) {
                return false;
            }
        }
        return $d_dir;
    }

    public function SetDirectory($account, $ditta = '') {
        if ($ditta == '')
            $ditta = App::$utente->getKey('ditta');

        $d_dir = Config::getPath('general.itaMailData') . 'ente' . $ditta . '/' . $account . "/";
        if (!is_dir($d_dir)) {
            if (!@mkdir($d_dir, 0777, true)) {
                return false;
            }
        }
        return $d_dir;
    }

    public function formatFileSize($a_bytes) {
        if ($a_bytes < 1024) {
            return $a_bytes . ' B';
        } elseif ($a_bytes < 1048576) {
            return round($a_bytes / 1024, 2) . ' KiB';
        } elseif ($a_bytes < 1073741824) {
            return round($a_bytes / 1048576, 2) . ' MiB';
        } elseif ($a_bytes < 1099511627776) {
            return round($a_bytes / 1073741824, 2) . ' GiB';
        } elseif ($a_bytes < 1125899906842624) {
            return round($a_bytes / 1099511627776, 2) . ' TiB';
        } elseif ($a_bytes < 1152921504606846976) {
            return round($a_bytes / 1125899906842624, 2) . ' PiB';
        } elseif ($a_bytes < 1180591620717411303424) {
            return round($a_bytes / 1152921504606846976, 2) . ' EiB';
        } elseif ($a_bytes < 1208925819614629174706176) {
            return round($a_bytes / 1180591620717411303424, 2) . ' ZiB';
        } else {
            return round($a_bytes / 1208925819614629174706176, 2) . ' YiB';
        }
    }

    public function DecodificaControllo($ctr) {
        $msgCtr = '';
        if ($ctr) {
            $arrayControlli = unserialize($ctr);
            $controlli = $arrayControlli['CONDIZIONI'];
            foreach ($controlli as $campo) {
                switch ($campo['CONDIZIONE']) {
                    case '==':
                        $condizione = "è uguale a ";
                        break;
                    case '!=':
                        $condizione = "è diverso da ";
                        break;
                    case '>':
                        $condizione = "è maggiore a ";
                        break;
                    case '<':
                        $condizione = "è minore a ";
                        break;
                    case '>=':
                        $condizione = "è maggiore-uguale a ";
                        break;
                    case '<=':
                        $condizione = "è minore-uguale a ";
                        break;
                    case 'LIKE':
                        $condizione = "contiene ";
                        break;
                }
                if ($campo['VALORE'] == '') {
                    $valore = "vuoto";
                } else {
                    $valore = $campo['VALORE'];
                }
                switch ($campo['OPERATORE']) {
                    case 'AND':
                        $operatore = 'e ';
                        break;
                    case 'OR':
                        $operatore = 'oppure ';
                }
                $msgCtr = $msgCtr . $operatore . 'il campo ' . $campo['CAMPO'] . '  ' . $condizione . $valore . chr(10);
            }
        }
        return $msgCtr;
    }

    function ordinaFiltri($account) {
        if ($account) {
            $new_seq = 0;
            $mailFiltri_tab = $this->getMailFiltri($account, "account", true);
            if (!$mailFiltri_tab) {
                return false;
            }
            foreach ($mailFiltri_tab as $mailFiltri_rec) {
                $new_seq += 10;
                $mailFiltri_rec['SEQUENZA'] = $new_seq;
                try {
                    $nrow = ItaDB::DBUpdate($this->getITALWEB(), "MAIL_FILTRI", "ROWID", $mailFiltri_rec);
                    if ($nrow == -1) {
                        return false;
                    }
                } catch (Exception $exc) {
                    Out::msgStop("Errore", $exc->getMessage());
                    return false;
                }
            }
            return true;
        }
    }

    public function VisualizzaFirme($file, $fileORiginale) {
        $model = "utiP7m";
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $_POST['event'] = "openform";
        $_POST['file'] = $file;
        $_POST['fileOriginale'] = $fileORiginale;
        $model();
    }

    public function GetMailAutorizzazioni($codice, $tipo = 'login', $soloValidi = false, $multi = false, $dataValidita = '', $mailAccount = '') {
        switch ($tipo) {
            case'login':
                $sql = "SELECT * FROM MAIL_AUTORIZZAZIONI WHERE LOGIN = '$codice' ";
                break;
            case'mail':
                $sql = "SELECT * FROM MAIL_AUTORIZZAZIONI WHERE MAIL = '$codice' ";
                break;
            default:
            case 'rowid':
                $sql = "SELECT * FROM MAIL_AUTORIZZAZIONI WHERE ROWID = $codice ";
                break;
        }
        if ($soloValidi) {
            if (!$dataValidita) {
                $dataValidita = date('Ymd');
            }
            $sql .= " AND DADATA <='$dataValidita' AND (ADATA >= '$dataValidita' OR ADATA = '') ";
        }
        if ($mailAccount) {
            $sql .= " AND MAIL = '$mailAccount' ";
        }
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function getMailPackages() {
        $sql = "SELECT * FROM MAIL_PACKAGES WHERE PKGFLAGACTIVATION = " . emlSpoolManager::PKG_ACTIVATION_STATUS_ACTIVE . " ORDER BY PKGDATE, PKGTIME";
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, true);
    }

    public function getMailPackageByID($id, $multi = false) {
        $sql = "SELECT * FROM MAIL_PACKAGES WHERE ROW_ID = " . $id;
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, $multi);
    }

    public function getMailEnvelopes($idPackages, $filters = array()) {
        $addFilters = '';
        if ($filters) {
            foreach ($filters as $key => $value) {
                $addFilters .= ' AND ' . $key . " = " . $value;
            }
        }
        $sql = "SELECT * FROM MAIL_ENVELOPES WHERE EVPSTATUS NOT IN (2,3) AND PACKAGES_ROWID = " . $idPackages . $addFilters . " ORDER BY EVPDATE, EVPTIME";
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, true);
    }

    public function countMailEnvelopes($idPackages, $evpMailRowId = null) {
        $addFilters = '';
        if ($evpMailRowId !== null) {
            $addFilters .= ' AND  EVPMAIL_ROWID != ' . $evpMailRowId;
        }
        $sql = "SELECT COUNT(*) COUNT FROM MAIL_ENVELOPES WHERE EVPSTATUS NOT IN (2,3) AND PACKAGES_ROWID = " . $idPackages . $addFilters;
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
    }

    public function getEnvelope($idEnvelope) {
        $sql = "SELECT  
                    MAIL_ENVELOPES.ROW_ID AS ROW_ID,
                    MAIL_ENVELOPES.PACKAGES_ROWID AS PACKAGES_ROWID,
                    MAIL_ENVELOPES.EVPDATE AS EVPDATE,
                    MAIL_ENVELOPES.EVPTIME AS EVPTIME,
                    MAIL_ENVELOPES.EVPMAIL_ROWID AS EVPMAIL_ROWID,
                    MAIL_ENVELOPES.EVPMAILTO AS EVPMAILTO,
                    MAIL_ENVELOPES.EVPMAIL_ID AS EVPMAIL_ID,
                    MAIL_ENVELOPES.EVPXMLDATA AS EVPXMLDATA,
                    MAIL_ENVELOPES.EVPSTATUS AS EVPSTATUS,
                    MAIL_ENVELOPES.EVPLASTMESSAGE AS EVPLASTMESSAGE,
                    MAIL_ENVELOPES.FLAG_DIS AS FLAG_DIS,
                    MAIL_ENVELOPES.FLAG_DIS_UTE AS FLAG_DIS_UTE,
                    MAIL_ENVELOPES.FLAG_DIS_DATA AS FLAG_DIS_DATA,
                    MAIL_ENVELOPES.FLAG_DIS_TIME AS FLAG_DIS_TIME,
                    MAIL_PACKAGES.PKGDATE AS PKGDATE,
                    MAIL_PACKAGES.PKGTIME AS PKGTIME,
                    MAIL_PACKAGES.PKGMAILACCOUNT AS PKGMAILACCOUNT,
                    MAIL_PACKAGES.PKGCLOSEDATE AS PKGCLOSEDATE,
                    MAIL_PACKAGES.PKGCLOSETIME AS PKGCLOSETIME,
                    MAIL_PACKAGES.PKGNOTE AS PKGNOTE,
                    MAIL_PACKAGES.PKGAPPCONTEXT AS PKGAPPCONTEXT,
                    MAIL_PACKAGES.PKGAPPKEY AS PKGAPPKEY,
                    MAIL_PACKAGES.PKGFLAGACTIVATION AS PKGFLAGACTIVATION
                FROM MAIL_ENVELOPES 
                    LEFT OUTER JOIN MAIL_PACKAGES ON MAIL_ENVELOPES.PACKAGES_ROWID = MAIL_PACKAGES.ROW_ID
                WHERE MAIL_ENVELOPES.ROW_ID = $idEnvelope";
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
    }

    public function getEnvelopeReceipt($evpmail_id) {
//        $sql = "SELECT ROWID, IDMAIL, PECTIPO, PECERRORE, PECERROREESTESO FROM MAIL_ARCHIVIO WHERE IDMAILPADRE = '$evpmail_id'";
        $sql = "SELECT ROWID,
                       IDMAIL, 
                       PECTIPO, 
                       IF (PECERROREESTESO <> '', PECERROREESTESO, PECERRORE) AS PECERRORE,
                       MSGDATE
                FROM MAIL_ARCHIVIO 
                WHERE IDMAILPADRE = '$evpmail_id'";
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, true);
    }

    public function getPackage($idPackage) {
        $sql = "SELECT  
                    MAIL_PACKAGES.ROW_ID AS ROW_ID,
                    MAIL_PACKAGES.PKGDATE AS PKGDATE,
                    MAIL_PACKAGES.PKGTIME AS PKGTIME,
                    MAIL_PACKAGES.PKGMAILACCOUNT AS PKGMAILACCOUNT,
                    MAIL_PACKAGES.PKGCLOSEDATE AS PKGCLOSEDATE,
                    MAIL_PACKAGES.PKGCLOSETIME AS PKGCLOSETIME,
                    MAIL_PACKAGES.PKGNOTE AS PKGNOTE,
                    MAIL_PACKAGES.PKGAPPCONTEXT AS PKGAPPCONTEXT,
                    MAIL_PACKAGES.PKGAPPKEY AS PKGAPPKEY,
                    MAIL_PACKAGES.PKGFLAGACTIVATION AS PKGFLAGACTIVATION
                FROM MAIL_PACKAGES 
                WHERE MAIL_PACKAGES.ROW_ID = $idPackage";
        return ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
    }

    public function disattivaEnvelope($row_id) {
        $sql = "SELECT * FROM MAIL_ENVELOPES WHERE ROW_ID = $row_id";
        $envelope = ItaDB::DBSQLSelect($this->getITALWEB(), $sql, false);
        if (!$envelope) {
            return false;
        }
        $envelope['FLAG_DIS'] = 1;
        $envelope['FLAG_DIS_UTE'] = App::$utente->getKey('nomeUtente');
        $envelope['FLAG_DIS_DATA'] = date('Ymd');
        $envelope['FLAG_DIS_TIME'] = date('H:i:s');
        try {
            ItaDB::DBUpdate($this->ITALWEB, 'MAIL_ENVELOPES', 'ROW_ID', $envelope);
        } catch (Exception $exc) {
            return false;
        }
        return true;
    }

}
