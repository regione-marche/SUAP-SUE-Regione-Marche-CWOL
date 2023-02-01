<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2014 Italsoft srl
 * @license
 * @version    03.10.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/ZTL/ztlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';

class praLibZTL extends itaModel {

    /**
     * Libreria di funzioni Generiche e Utility per Integrazione SUAP/FIERE
     */
    public $praLib;
    public $ztlLib;
    public $devLib;
    public $PRAM_DB;
    public $ISOLA_DB;
    private $errMessage;
    private $errCode;
    public $eqAudit;

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
        $this->ztlLib = new ztlLib();
        $this->devLib = new devLib();
        $this->eqAudit = new eqAudit();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
        $this->ISOLA_DB = $this->ztlLib->getISOLADB();
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function CollegaPermessi($praDati, $praDatiPratica, $currGesnum) {
        /*
         * Gestione delle importazioni differenziata per tipologia 
         * default: "italsoft"
         */

        $Parametri = $this->devLib->getEnv_config('ZTL', 'codice', 'ZTLTIPOSUAP', false);
        $tipo_import = $Parametri['CONFIG'];
        if (!$tipo_import) {
            $tipo_import = "italsoft";
        }
        switch ($tipo_import) {
            case 'italsoft':
                if (!$this->CollegaPermessiItalsoft($praDati, $praDatiPratica, $currGesnum)) {
                    return false;
                }
                break;
            case 'OSIMO':
                if (!$this->CollegaPermessiItalsoft($praDati, $praDatiPratica, $currGesnum)) {
                    return false;
                }
                break;
            default:
                $this->setErrMessage("Tipo di importazione permessi non configurato correttamente. Importazione non eseguita.");
                return false;
                break;
        }
        return true;
    }

    public function RinnovaPermessi($praDati, $praDatiPratica, $currGesnum) {
        /*
         * Gestione delle importazioni differenziata per tipologia 
         * default: "italsoft"
         */

        $Parametri = $this->devLib->getEnv_config('ZTL', 'codice', 'ZTLTIPOSUAP', false);
        $tipo_import = $Parametri['CONFIG'];
        if (!$tipo_import) {
            $tipo_import = "italsoft";
        }
        switch ($tipo_import) {
            case 'italsoft':
                if (!$this->RinnovaPermessiItalsoft($praDati, $praDatiPratica, $currGesnum)) {
                    return false;
                }
                break;
            case 'OSIMO':
                if (!$this->RinnovaPermessoOsimo($currGesnum)) {
                    return false;
                }
                break;
            default:
                $this->setErrMessage("Tipo di importazione permessi non configurato correttamente. Importazione non eseguita.");
                return false;
                break;
        }
        return true;
    }

    public function CollegaPermessiItalsoft($praDati, $praDatiPratica, $currGesnum) {
//        $Parametri = $this->devLib->getEnv_config('ZTL', 'codice', 'NPERM_MULTI', false);
//        $n_multi = $Parametri['CONFIG'];
        //tutte le tipologie di importazione DEVONO avere i codici delle fiere selezionate in DENOM_FIERA
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='TipoPermesso' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $TipoPermesso = trim($prodag_rec['DAGVAL']);
        } else {
            $this->setErrMessage("Tipo Permesso non trovato");
            return false;
        }
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='NPeriodi' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $NPeriodi = trim($prodag_rec['DAGVAL']);
        } else {
            $NPeriodi = 1;
        }
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='TipoAvviso' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $TipoAvviso = trim($prodag_rec['DAGVAL']);
        }
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='MULTITARGA' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $Multitarga = trim($prodag_rec['DAGVAL']);
        } else {
            $Multitarga = false;
        }
        $NAutorizzazioni = 1;
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='NAutorizzazioni' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $NAutorizzazioni = trim($prodag_rec['DAGVAL']);
        }
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='CONTATTO' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $Contatto = trim($prodag_rec['DAGVAL']);
        }
        $DataPermesso = "";
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DataPermesso' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $DataPermesso = substr($prodag_rec['DAGVAL'], 6, 4) . substr($prodag_rec['DAGVAL'], 3, 2) . substr($prodag_rec['DAGVAL'], 0, 2);
        }
        $DataScadenza = "";
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DataScadenza' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $DataScadenza = substr($prodag_rec['DAGVAL'], 6, 4) . substr($prodag_rec['DAGVAL'], 3, 2) . substr($prodag_rec['DAGVAL'], 0, 2);
        }
        $OraDecorrenza = "";
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DalleOre' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $OraDecorrenza = substr($prodag_rec['DAGVAL'], 0, 5);
        }
        $OraScadenza = "";
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='AlleOre' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $OraScadenza = substr($prodag_rec['DAGVAL'], 0, 5);
        }

        $DescZona = "";
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='ZONA' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $DescZona = trim($prodag_rec['DAGVAL']);
        }
        $sqlZ = "SELECT * FROM ZONE WHERE DESCRIZIONEZONA = '" . $DescZona . "'";
        $Zone_rec = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sqlZ, false);

        $targhe_arr = array();
        $veicoli_arr = array();
        for ($i = 1; $i <= 10; $i++) {
            $n = str_pad($i, 2, "0", STR_PAD_LEFT);
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='TARGA_" . $n . "' AND DAGVAL <> ''";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $targhe_arr[] = trim(strtoupper($prodag_rec['DAGVAL']));
            }
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='VEICOLO_" . $n . "' AND DAGVAL <> ''";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $veicoli_arr[] = trim(strtoupper($prodag_rec['DAGVAL']));
            }
        }
        $arr_targhe_altre = array();
        $arr_veicoli_altre = array();
        for ($i = 0; $i < 10; $i++) {
            for ($it = 1; $it <= 10; $it++) {
                $st = str_pad($it, 2, "0", STR_PAD_LEFT);
                $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='P" . $i . "_TARGA_" . $st . "' AND DAGVAL <> ''";
                $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                if ($prodag_rec) {
                    $arr_targhe_altre[$i][] = trim(strtoupper($prodag_rec['DAGVAL']));
                }
                $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='P" . $i . "_VEICOLO_" . $st . "' AND DAGVAL <> ''";
                $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                if ($prodag_rec) {
                    $arr_veicoli_altre[$i][] = trim(strtoupper($prodag_rec['DAGVAL']));
                }
            }
        }


        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='Matrimonio' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $Matrimonio = trim($prodag_rec['DAGVAL']);
        } else {
            $Matrimonio = false;
        }
        $Anaperm_rec = $this->ztlLib->getAnaperm($TipoPermesso);

        //cerco la data di invio domanda
        //$sql = "SELECT * FROM PRORIC WHERE RICNUM = '" . $_POST[$this->nameForm . '_PROGES']['GESPRA'] . "'";
        $proges_rec = $this->praLib->GetProges($currGesnum);
        $sql = "SELECT * FROM PRORIC WHERE RICNUM = '" . $proges_rec['GESPRA'] . "'";
        $proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        $proges_rec = $this->praLib->GetProges($currGesnum);

        foreach ($praDatiPratica as $dato) {
            if ($dato['DAGKEY'] == "ESIBENTE_PEC") {
                $pec = $dato['DAGVAL'];
            }
        }
        $impIndividuale = $Legale = false;
        $arr_dati = array();
        $arr_dati['MOTIVO'] = $TipoPermesso;
        foreach ($praDati as $dato) {
            /*
             * DATI INTESTATARIO
             */
            if ($dato['DAGKEY'] == "NOME")
                $arr_dati['NOME'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "INTCOGNOME" || $dato['DAGKEY'] == 'DICHIARANTE_COGNOME')
                $arr_dati['INTCOGNOME'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "INTNOME" || $dato['DAGKEY'] == 'DICHIARANTE_NOME')
                $arr_dati['INTNOME'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "SESSO" || $dato['DAGKEY'] == 'DICHIARANTE_SESSO_SEX')
                $arr_dati['SESSO'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CODICEFISCALE" || $dato['DAGKEY'] == 'DICHIARANTE_CODICEFISCALE_CFI')
                $arr_dati['CODICEFISCALE'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LGNASCITA")
                $arr_dati['LGNASCITA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "PROVNASCITA")
                $arr_dati['PROVNASCITA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DATANASCITA" || $dato['DAGKEY'] == 'DICHIARANTE_NASCITADATA_DATA')
                $arr_dati['DATANASCITA'] = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "VIA" || $dato['DAGKEY'] == 'DICHIARANTE_RESIDENZAVIA')
                $arr_dati['VIA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "NUMERO" || $dato['DAGKEY'] == 'DICHIARANTE_RESIDENZACIVICO')
                $arr_dati['NUMERO'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LETTERA")
                $arr_dati['LETTERA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CAPRESIDENZA" || $dato['DAGKEY'] == 'DICHIARANTE_RESIDENZACAP')
                $arr_dati['CAPRESIDENZA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CITTA" || $dato['DAGKEY'] == 'DICHIARANTE_RESIDENZACOMUNE')
                $arr_dati['CITTA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "PROVRESIDENZA" || $dato['DAGKEY'] == 'DICHIARANTE_RESIDENZAPROVINCIA_PV')
                $arr_dati['PROVRESIDENZA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "TELEFINTESTA")
                $arr_dati['TELEFINTESTA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CELLINTESTA")
                $arr_dati['CELLINTESTA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "INTMAIL" || $dato['DAGKEY'] == 'DICHIARANTE_PEC')
                $arr_dati['INTMAIL'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "TIPOLOGIAINTESTATARIO")
                $arr_dati['TIPOLOGIAINTESTATARIO'] = strtoupper($dato['DAGVAL']);

            /*
             * DATI PROPRIETARIO
             */
            if ($dato['DAGKEY'] == "NOMEPROP")
                $arr_dati['NOMEPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "SESSOP")
                $arr_dati['SESSOP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CODICEFISCALEPROP")
                $arr_dati['CODICEFISCALEPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LGNASCITAPROP")
                $arr_dati['LGNASCITAPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LGNASCITAPROP")
                $arr_dati['LGNASCITAPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "PROVNASCITAPROP")
                $arr_dati['PROVNASCITAPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DATANASCITAPROP")
                $arr_dati['DATANASCITAPROP'] = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "VIAPROP")
                $arr_dati['VIAPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "NUMEROPROP")
                $arr_dati['NUMEROPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LETTERAPROP")
                $arr_dati['LETTERAPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CAPRESIDENZAPROP")
                $arr_dati['CAPRESIDENZAPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CITTAPROP")
                $arr_dati['CITTAPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "PROVRESIDENZAPROP")
                $arr_dati['PROVRESIDENZAPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "TELEFPROP")
                $arr_dati['TELEFPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CELLPROP")
                $arr_dati['CELLPROP'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "MAILPROP")
                $arr_dati['MAILPROP'] = strtoupper($dato['DAGVAL']);

            /*
             * DATI DITTA
             */
            if ($dato['DAGKEY'] == "RAGIONESOCIALE")
                $arr_dati['RAGIONESOCIALE'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "PIVA")
                $arr_dati['PIVA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CFDITTA")
                $arr_dati['CFDITTA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "UBICAZIONE")
                $arr_dati['UBICAZIONE'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "NUMCIVICO")
                $arr_dati['NUMCIVICO'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LETTAZIENDA")
                $arr_dati['LETTAZIENDA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CAPAZIENDA")
                $arr_dati['CAPAZIENDA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CITTAAZIENDA")
                $arr_dati['CITTAAZIENDA'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "PROVAZIENDA")
                $arr_dati['PROVAZIENDA'] = strtoupper($dato['DAGVAL']);

            /*
             * DATI GARAGE
             */
            if ($dato['DAGKEY'] == "VIAGARAGE")
                $arr_dati['VIAGARAGE'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "NUMEROGARAGE")
                $arr_dati['NUMEROGARAGE'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LETTERAGARAGE")
                $arr_dati['LETTERAGARAGE'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CAPGARAGE")
                $arr_dati['CAPGARAGE'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CITTAGARAGE")
                $arr_dati['CITTAGARAGE'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "PROVGARAGE")
                $arr_dati['PROVGARAGE'] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "TELEFGARAGE")
                $arr_dati['TELEFGARAGE'] = strtoupper($dato['DAGVAL']);
        }

        /*
         * DATI RICHIESTA
         */
        $arr_dati['NUMERODOMANDA'] = intval(substr($proges_rec['GESPRA'], 4));
        $arr_dati['DATADOMANDA'] = $proges_rec['GESDRI'];
        $arr_dati['NUMPROTOCOLLO'] = intval(substr($proges_rec['GESNPR'], 4));
        $metaDati = proIntegrazioni::GetMetedatiProt($proges_rec['GESNUM']);
        if (isset($metaDati['Data']) && $proges_rec['GESNPR'] != 0) {
            $arr_dati['DATAPROTOCOLLO'] = $metaDati['Data'];
        }
        if (!$arr_dati['DATAPROTOCOLLO']) {
            $arr_dati['DATAPROTOCOLLO'] = date('Ymd');
        }
//        $arr_dati['VERIFICA'] = $TipoAvviso;

        switch ($TipoAvviso) {
            case 'SMS':
                $arr_dati['VERIFICA'] = "S";
                if ($arr_dati['CELLINTESTA'] == '') {
                    $arr_dati['CELLINTESTA'] = $Contatto;
                }
                break;
            case 'EMAIL':
                $arr_dati['VERIFICA'] = "E";
                if ($arr_dati['INTMAIL'] == '') {
                    $arr_dati['INTMAIL'] = $Contatto;
                }
                break;
            case 'ENTRAMBI':
                $arr_dati['VERIFICA'] = "T";
                if ($arr_dati['INTMAIL'] == '') {
                    $arr_dati['INTMAIL'] = $Contatto;
                }
                break;
            case 'NESSUNA':
                $arr_dati['VERIFICA'] = "N";
                if ($arr_dati['INTMAIL'] == '') {
                    $arr_dati['INTMAIL'] = $Contatto;
                }
                break;
            default:
                break;
        }

        if (!isset($arr_dati['NOME']) || trim($arr_dati['NOME']) == '') {
            $arr_dati['NOME'] = trim($arr_dati['INTCOGNOME'] . ' ' . $arr_dati['INTNOME']);
        }

        if (!$Multitarga) {
            if ($targhe_arr) {
                $n_permessi = count($targhe_arr);
            }
        } else {
//            $Parametri = $this->devLib->getEnv_config('ZTL', 'codice', 'NPERM_MULTI', false);
//            $n_permessi = $Parametri['CONFIG'];
            $n_permessi = $Anaperm_rec['MAXMULTITARGA'];
        }
        if (!$n_permessi || $n_permessi == 0) {
            if ($Anaperm_rec['FLNOVEIC']) {
                $n_permessi = 1;
            }
        }

        for ($ext_i = 0; $ext_i < $NAutorizzazioni; $ext_i++) {
            if ($arr_targhe_altre) {
                $n_permessi = 0;
                $targhe_arr = array();
                foreach ($arr_targhe_altre[$ext_i + 1] as $targa) {
                    $targhe_arr[] = $targa;
                }
                foreach ($arr_veicoli_altre[$ext_i + 1] as $veicolo) {
                    $veicoli_arr[] = $veicolo;
                }
                $n_permessi = count($targhe_arr);
            }

            for ($index = 0; $index < $n_permessi; $index++) {
                //creo un permesso per ogni targa
                $Isola_rec = array();
                /*
                 * prevedere eventuale inserimento dei residenti
                 */
                $Isola_rec = $arr_dati; //precarico tutti i dati trovati
                $Isola_rec['DATARILASCIO'] = date('Ymd');
                $Isola_rec['ORARILASCIO'] = date('H:i');
                $Isola_rec['PERIODI'] = $NPeriodi;
                $Isola_rec['DATADECORRENZA'] = $DataPermesso != '' ? $DataPermesso : $proges_rec['GESDRI'];
//                $Isola_rec['ORADECORRENZA'] = $proges_rec['GESORA'];
                $Isola_rec['ORADECORRENZA'] = $OraDecorrenza != '' ? $OraDecorrenza : '00:00';
                $retScad = $this->ztlLib->CalcolaScadenza($Isola_rec);
                if ($DataScadenza) {
                    $Isola_rec['DATASCADENZA'] = $DataScadenza;
                } else {
                    $Isola_rec['DATASCADENZA'] = $retScad['DataScadenza'];
                }
                $Isola_rec['ORASCADENZA'] = $OraScadenza != '' ? $OraScadenza : $retScad['OraScadenza'];
                $Isola_rec['DATASCADENZATEMP'] = $retScad['DataScadenzaTemp'];
                $Ult_cod = $this->ztlLib->progressivoPermessi($Anaperm_rec);
                $Isola_rec['CODICE'] = $Ult_cod;
                $Isola_rec['ANNOCODICE'] = $Isola_rec['DATADECORRENZA'] != '' ? substr($Isola_rec['DATADECORRENZA'], 0, 4) : date('Y');
                $ult_prog = $this->ztlLib->progressivoMotivi($TipoPermesso);
                $Isola_rec['PROGMOTIVO'] = $ult_prog;
                $Isola_rec['DATAINSERIMENTO'] = date('Ymd');
//            $Isola_rec['PIN'] = $this->ztlLib->GeneraPIN();
                if (!$Multitarga) {
                    $Isola_rec['TARGA'] = $targhe_arr[$index];
                    $Isola_rec['VEICOLO'] = $veicoli_arr[$index];
                } else {
                    if ($arr_targhe_altre) {
                        $Isola_rec['TARGA'] = $arr_targhe_altre[$ext_i + 1][0];
                        $Isola_rec['VEICOLO'] = $arr_veicoli_altre[$ext_i + 1][0];
                        $Isola_rec['TARGA1'] = $arr_targhe_altre[$ext_i + 1][1];
                        $Isola_rec['VEICOLO1'] = $arr_veicoli_altre[$ext_i + 1][1];
                        $Isola_rec['TARGA2'] = $arr_targhe_altre[$ext_i + 1][2];
                        $Isola_rec['VEICOLO2'] = $arr_veicoli_altre[$ext_i + 1][2];
                        $Isola_rec['TARGA3'] = $arr_targhe_altre[$ext_i + 1][3];
                        $Isola_rec['VEICOLO3'] = $arr_veicoli_altre[$ext_i + 1][3];
                        $Isola_rec['TARGA4'] = $arr_targhe_altre[$ext_i + 1][4];
                        $Isola_rec['VEICOLO4'] = $arr_veicoli_altre[$ext_i + 1][4];
                        $Isola_rec['TARGA5'] = $arr_targhe_altre[$ext_i + 1][5];
                        $Isola_rec['VEICOLO5'] = $arr_veicoli_altre[$ext_i + 1][5];
                        $Isola_rec['TARGA6'] = $arr_targhe_altre[$ext_i + 1][6];
                        $Isola_rec['VEICOLO6'] = $arr_veicoli_altre[$ext_i + 1][6];
                        $Isola_rec['TARGA7'] = $arr_targhe_altre[$ext_i + 1][7];
                        $Isola_rec['VEICOLO7'] = $arr_veicoli_altre[$ext_i + 1][7];
                        $Isola_rec['TARGA8'] = $arr_targhe_altre[$ext_i + 1][8];
                        $Isola_rec['VEICOLO8'] = $arr_veicoli_altre[$ext_i + 1][8];
                        $Isola_rec['TARGA9'] = $arr_targhe_altre[$ext_i + 1][9];
                        $Isola_rec['VEICOLO9'] = $arr_veicoli_altre[$ext_i + 1][9];
                    } else {
                        $Isola_rec['TARGA'] = $targhe_arr[0];
                        $Isola_rec['VEICOLO'] = $veicoli_arr[0];
                        $Isola_rec['TARGA1'] = $targhe_arr[1];
                        $Isola_rec['VEICOLO1'] = $veicoli_arr[1];
                        $Isola_rec['TARGA2'] = $targhe_arr[2];
                        $Isola_rec['VEICOLO2'] = $veicoli_arr[2];
                        $Isola_rec['TARGA3'] = $targhe_arr[3];
                        $Isola_rec['VEICOLO3'] = $veicoli_arr[3];
                        $Isola_rec['TARGA4'] = $targhe_arr[4];
                        $Isola_rec['VEICOLO4'] = $veicoli_arr[4];
                        $Isola_rec['TARGA5'] = $targhe_arr[5];
                        $Isola_rec['VEICOLO5'] = $veicoli_arr[5];
                        $Isola_rec['TARGA6'] = $targhe_arr[6];
                        $Isola_rec['VEICOLO6'] = $veicoli_arr[6];
                        $Isola_rec['TARGA7'] = $targhe_arr[7];
                        $Isola_rec['VEICOLO7'] = $veicoli_arr[7];
                        $Isola_rec['TARGA8'] = $targhe_arr[8];
                        $Isola_rec['VEICOLO8'] = $veicoli_arr[8];
                        $Isola_rec['TARGA9'] = $targhe_arr[9];
                        $Isola_rec['VEICOLO9'] = $veicoli_arr[9];
                    }
                }
//                $Isola_rec['VEICOLO'] = $veicoli_arr[$k];
                $Isola_rec['FLCARPOOL'] = $Multitarga;
                if ($Anaperm_rec['FLCTRRIMESSA']) {
                    $Isola_rec['FLPOSTOAUTO'] = true;
                }
                $Isola_rec['CODICEFASCIA'] = $Anaperm_rec['PROFILOORARIO'];
                //decodifica della zona a partire dalla via
                $via = "";
                if ($Anaperm_rec['FLCTRRES']) {
                    $via = $Isola_rec['VIA'];
                }
                if ($Anaperm_rec['FLCTRDITTA']) {
                    $via = $Isola_rec['UBICAZIONE'];
                }
                if ($Anaperm_rec['FLCTRRIMESSA']) {
                    $via = $Isola_rec['VIAGARAGE'];
                }
                if ($via != '') {
                    $sql = "SELECT * FROM VIE WHERE LUOGOINFRAZIONE = '" . addslashes($via) . "'";
                    $Vie_rec = ItaDB::DBSQLSelect($this->ISOLA_DB, $sql, false);
                    if ($Vie_rec['ZONA'] != '') {
                        $Zona = $Vie_rec['ZONA'];
                    }
                }
                $sqlZ = "SELECT * FROM ZONE WHERE DESCRIZIONEZONA = '" . $DescZona . "'";
                $Zone_rec = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sqlZ, false);
//                $Zona = substr($Zona, 0, 2); //zona è al massimo due caratteri
                if ($Zone_rec) {
                    $Zona = $Zone_rec['ZONA'];
                }
                if (!$Zona) {
                    $Zona = $DescZona;
                }
                $Isola_rec['ZONA'] = $Zona;
                $Isola_rec['DAPAGARE'] = $this->ztlLib->LeggiListino($Isola_rec) * $Isola_rec['PERIODI'];
                if (!$this->controllaQuantiPass($Isola_rec)) {
                    return false;
                }
                if ($Isola_rec['CODICEFISCALE'] == '') {
                    $this->setErrMessage("Attenzione: codice fiscale mancante");
                    return false;
                }
                //inserisco i metadati
                $Isola_rec['ISOLAMETA'] = $proges_rec['GESMETA'];

                $insert_Info = 'Oggetto: Inserimento nuovo permesso richiesta ' . $Isola_rec['NUMERODOMANDA'];
                if (!$this->insertRecord($this->ISOLA_DB, 'ISOLA', $Isola_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento su ISOLA per la richiesta " . $Isola_rec['NUMERODOMANDA']);
                    return false;
                }
                //rileggo il record inserito
                $lastId = $this->ISOLA_DB->getLastId();

                //inserisco i dati in ISOLASUAP
                $IsolaSuap_rec = array();
                $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
                $IsolaSuap_rec['TABELLA'] = 'ISOLA';
                $IsolaSuap_rec['ID'] = $lastId;
                $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserito permesso da suap pratica " . $IsolaSuap_rec['SUAPRA']);

                //inserisco i dati del soggetto nella tabella RESIDENTI (usata per tutti i soggetti), in caso li aggiorno
                $this->ztlLib->AggiornaSoggetti($Isola_rec);

                /*
                 * Per ogni domanda aggiungo i documenti allegati
                 */
                $Pasdoc_tab = $this->praLib->GetPasdoc($proges_rec['GESNUM'], 'pratica', true);
                $n = 0;
                foreach ($Pasdoc_tab as $Pasdoc_rec) {
                    $n++;
                    $pramPath = $this->praLib->SetDirectoryPratiche(substr($Pasdoc_rec['PASKEY'], 0, 4), $Pasdoc_rec['PASKEY'], 'PASSO', false);
                    $filePath = $pramPath . "/" . $Pasdoc_rec['PASFIL'];
                    $isoladoc_rec = array();
                    $isoladoc_rec['ANNOCODICE'] = $Isola_rec['ANNOCODICE'];
                    $isoladoc_rec['CODICE'] = $Isola_rec['CODICE'];
                    $isoladoc_rec['NUMERODOMANDA'] = $Isola_rec['NUMERODOMANDA'];
                    $isoladoc_rec['DATADOMANDA'] = $Isola_rec['DATADOMANDA'];
                    $isoladoc_rec['NUMERODOMANDAV'] = $Isola_rec['NUMERODOMANDA'];
                    $isoladoc_rec['DATADOMANDAV'] = $Isola_rec['DATADOMANDA'];
                    $isoladoc_rec['NUMPROT'] = $Isola_rec['NUMPROTOCOLLO'];
                    $isoladoc_rec['ANNOPROT'] = substr($Isola_rec['DATAPROTOCOLLO'], 0, 4);
                    $isoladoc_rec['PROGRESSIVO'] = $n;
                    $isoladoc_rec['CODICEDOCUMENTO'] = $Pasdoc_rec['PASNAME'];
                    $isoladoc_rec['NOTE'] = $Pasdoc_rec['PASNAME'];
//                    $isoladoc_rec['FILE'] = $randName;
                    $isoladoc_rec['FILE'] = $Pasdoc_rec['PASFIL'];
                    copy($filePath, $this->ztlLib->SetDirectoryZTL($Isola_rec['CODICE'], "PERMESSI", $Isola_rec['ANNOCODICE']) . DIRECTORY_SEPARATOR . $isoladoc_rec['FILE']);
                    $insert_Info = "Oggetto: Inserimento nuovo documento permesso " . $Isola_rec['CODICE'] . " - " . $Isola_rec['ANNOCODICE'];
                    if (!$this->insertRecord($this->ISOLA_DB, 'ISOLADOC', $isoladoc_rec, $insert_Info)) {
                        $this->setErrMessage("Errore in inserimento nuovo documento richiesta " . $Isola_rec['NUMERODOMANDA']);
                        return false;
                    }
                    //rileggo il record inserito
                    $lastId = $this->ISOLA_DB->getLastId();

                    //inserisco i dati in ISOLASUAP
                    $IsolaSuap_rec = array();
                    $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
                    $IsolaSuap_rec['TABELLA'] = 'ISOLADOC';
                    $IsolaSuap_rec['ID'] = $lastId;
                    $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserito documento da suap pratica " . $IsolaSuap_rec['SUAPRA']);
                }



                if (!$Multitarga && isset($Zone_rec) && $Zone_rec['ZONALB'] != '') {
                    /*
                     * per ogni targa inserisco il record della variazione
                     */
                    $sql = "SELECT MAX(PRG_VAR) AS NEWPROG FROM VARIAZIONI";
                    $Max_prog = ItaDB::DBSQLSelect($this->ISOLA_DB, $sql, false);
                    $new_prog = $Max_prog['NEWPROG'] + 1;
                    $Variazioni_rec = array();
                    $Variazioni_rec['PRG_VAR'] = $new_prog;
                    $Variazioni_rec['CODICE'] = $Isola_rec['CODICE'];
                    $Variazioni_rec['ANNOCODICE'] = $Isola_rec['ANNOCODICE'];
                    $Variazioni_rec['NUMERODOMANDA'] = $Isola_rec['NUMERODOMANDA'];
                    $Variazioni_rec['DATADOMANDA'] = $Isola_rec['DATADOMANDA'];
                    $Variazioni_rec['NUMERODOMANDAV'] = $Isola_rec['NUMERODOMANDA'];
                    $Variazioni_rec['DATADOMANDAV'] = $Isola_rec['DATADOMANDA'];
                    $Variazioni_rec['NUMPROT'] = $Isola_rec['NUMPROTOCOLLO'];
                    $Variazioni_rec['ANNOPROT'] = substr($Isola_rec['DATAPROTOCOLLO'], 0, 4);
                    $Variazioni_rec['TARGA'] = $Isola_rec['TARGA'];
                    $Tipofascia_rec = $this->ztlLib->getTipoFascia($Anaperm_rec['PROFILOORARIO']);
                    $Variazioni_rec['CODICEFASCIA'] = $Tipofascia_rec['CODICELB'];
                    $Variazioni_rec['ZONA'] = $Zone_rec['ZONALB'];
                    $Variazioni_rec['DATADECORRENZA'] = $Isola_rec['DATADECORRENZA'];
                    $Variazioni_rec['ORADECORRENZA'] = $Isola_rec['ORADECORRENZA'];
                    $Variazioni_rec['DATASCADENZA'] = $Isola_rec['DATASCADENZATEMP'] != '' ? $Isola_rec['DATASCADENZATEMP'] : $Isola_rec['DATASCADENZA'];
                    /*
                     * se il permesso è gratuito prendo sempre la data di fine validità del pass
                     */
                    if ($Isola_rec['DAPAGARE'] == 0) {
                        $Variazioni_rec['DATASCADENZA'] = $Isola_rec['DATASCADENZA'];
                    }
                    $Variazioni_rec['ORASCADENZA'] = '23:59';
                    $Variazioni_rec['DATAVARIAZIONE'] = date('Ymd');
                    $Variazioni_rec['ORAVARIAZIONE'] = date('H:i:s');
                    $this->insertRecord($this->ISOLA_DB, "VARIAZIONI", $Variazioni_rec, "Inserimento prima Variazione targa $targa permesso " . $Isola_rec['CODICE']);
                    //rileggo il record inserito
                    $lastId = $this->ISOLA_DB->getLastId();

                    //inserisco i dati in ISOLASUAP
                    $IsolaSuap_rec = array();
                    $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
                    $IsolaSuap_rec['TABELLA'] = 'VARIAZIONI';
                    $IsolaSuap_rec['ID'] = $lastId;
                    $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserita variazione da suap pratica " . $IsolaSuap_rec['SUAPRA']);
                }
            }
        }


        if ($Multitarga && isset($Zone_rec) && $Zone_rec['ZONALB'] != '') {
            foreach ($targhe_arr as $targa) {
                /*
                 * per ogni targa inserisco il record della variazione
                 */
                $sql = "SELECT MAX(PRG_VAR) AS NEWPROG FROM VARIAZIONI";
                $Max_prog = ItaDB::DBSQLSelect($this->ISOLA_DB, $sql, false);
                $new_prog = $Max_prog['NEWPROG'] + 1;
                $Variazioni_rec = array();
                $Variazioni_rec['PRG_VAR'] = $new_prog;
                $Variazioni_rec['CODICE'] = $Isola_rec['CODICE']; //viene preso l'ultimo permesso inserito
                $Variazioni_rec['ANNOCODICE'] = $Isola_rec['ANNOCODICE'];
                $Variazioni_rec['NUMERODOMANDA'] = $Isola_rec['NUMERODOMANDA'];
                $Variazioni_rec['DATADOMANDA'] = $Isola_rec['DATADOMANDA'];
                $Variazioni_rec['NUMERODOMANDAV'] = $Isola_rec['NUMERODOMANDA'];
                $Variazioni_rec['DATADOMANDAV'] = $Isola_rec['DATADOMANDA'];
                $Variazioni_rec['NUMPROT'] = $Isola_rec['NUMPROTOCOLLO'];
                $Variazioni_rec['ANNOPROT'] = substr($Isola_rec['DATAPROTOCOLLO'], 0, 4);
                $Variazioni_rec['TARGA'] = $targa;
                $Tipofascia_rec = $this->ztlLib->getTipoFascia($Anaperm_rec['PROFILOORARIO']);
                $Variazioni_rec['CODICEFASCIA'] = $Tipofascia_rec['CODICELB'];
                $Variazioni_rec['ZONA'] = $Zone_rec['ZONALB'];
                $Variazioni_rec['DATADECORRENZA'] = $proges_rec['GESDRI'];
//                $Variazioni_rec['ORADECORRENZA'] = $proges_rec['GESORA'];
                $Variazioni_rec['ORADECORRENZA'] = '00:00:00';
                $Variazioni_rec['DATASCADENZA'] = $Isola_rec['DATASCADENZATEMP'] != '' ? $Isola_rec['DATASCADENZATEMP'] : $Isola_rec['DATASCADENZA'];
                /*
                 * se il permesso è gratuito prendo sempre la data di fine validità del pass
                 */
                if ($Isola_rec['DAPAGARE'] == 0) {
                    $Variazioni_rec['DATASCADENZA'] = $Isola_rec['DATASCADENZA'];
                }
                $Variazioni_rec['ORASCADENZA'] = '23:59:59';
                $Variazioni_rec['DATAVARIAZIONE'] = date('Ymd');
                $Variazioni_rec['ORAVARIAZIONE'] = date('H:i:s');
                $this->insertRecord($this->ISOLA_DB, "VARIAZIONI", $Variazioni_rec, "Inserimento prima Variazione targa $targa permesso " . $Isola_rec['CODICE']);
                //rileggo il record inserito
                $lastId = $this->ISOLA_DB->getLastId();

                //inserisco i dati in ISOLASUAP
                $IsolaSuap_rec = array();
                $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
                $IsolaSuap_rec['TABELLA'] = 'VARIAZIONI';
                $IsolaSuap_rec['ID'] = $lastId;
                $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserita variazione da suap pratica " . $IsolaSuap_rec['SUAPRA']);
            }
        }

        return true;
    }

    private function elencaFiles($dirname) {
        $arrayfiles = Array();
        if (file_exists($dirname)) {
            $handle = opendir($dirname);
            while (false !== ($file = readdir($handle))) {
                if (is_file($dirname . DIRECTORY_SEPARATOR . $file)) {
                    array_push($arrayfiles, $file);
                }
            }
            closedir($handle);
        }
        sort($arrayfiles);  //ordinamento alfabetico
        return $arrayfiles;
    }

    public function controllaQuantiPass($Isola_rec) {
        $data_inf = $Isola_rec['DATADECORRENZA'];
        $data_sup = $Isola_rec['DATASCADENZA'];
        $Storno_quanti = 1;
        $Storno_imm = 1;
        $Storno_targa = 1;
        $cod_fis = $Isola_rec['CODICEFISCALE'];
        $Sz_imm = $Isola_rec['SEZIONEIMM'];
        $Fg_imm = $Isola_rec['FOGLIOIMM'];
        $Nm_imm = $Isola_rec['NUMIMM'];
        $Sb_imm = $Isola_rec['SUBIMM'];
        $ok_quanti = true;
        $Anaperm_rec = $this->ztlLib->getAnaperm($Isola_rec['MOTIVO'], 'codice');
        if ($cod_fis != '') {
            $tipo_p = $Isola_rec['MOTIVO'];
            $n_perm = $this->ztlLib->ContaPermessi($tipo_p, $data_inf, $data_sup, $cod_fis, "", "");
            $n_perm = $n_perm - $Storno_quanti;
            if ($n_perm >= $Anaperm_rec['MAXPERSOG'] && $Anaperm_rec['MAXPERSOG'] != 0) {
                $this->setErrMessage("Il soggetto possiede già " . $n_perm . " permessi attivi nel periodo
                    dal " . substr($data_inf, 6, 2) . "/" . substr($data_inf, 4, 2) . "/" . substr($data_inf, 0, 4) . "
                    al " . substr($data_sup, 6, 2) . "/" . substr($data_sup, 4, 2) . "/" . substr($data_sup, 0, 4) . "
                    Il numero massimo di permessi è " . $Anaperm_rec['MAXPERSOG']);
                $ok_quanti = false;
                return $ok_quanti;
            }
            if ($n_perm >= $Anaperm_rec['MAXPERSOGPA'] && $Anaperm_rec['MAXPERSOGPA'] != 0 && $Isola_rec['FLPOSTOAUTO']) {
                $this->setErrMessage("Il soggetto possiede già " . $n_perm . " permessi attivi nel periodo
                    dal " . substr($data_inf, 6, 2) . "/" . substr($data_inf, 4, 2) . "/" . substr($data_inf, 0, 4) . "
                    al " . substr($data_sup, 6, 2) . "/" . substr($data_sup, 4, 2) . "/" . substr($data_sup, 0, 4) . "
                    Il numero massimo di permessi per chi possiede un garage è " . $Anaperm_rec['MAXPERSOGPA']);
                $ok_quanti = false;
                return $ok_quanti;
            }
            $n_perm_fam = $this->ztlLib->ContaPermessiFam($tipo_p, $data_inf, $data_sup, $cod_fis, "", "");
            $n_perm_fam = $n_perm_fam - $Storno_quanti;
            if ($n_perm_fam >= $Anaperm_rec['MAXPERFAM'] && $Anaperm_rec['MAXPERFAM'] != 0) {
                $this->setErrMessage("La famiglia del soggetto possiede già " . $n_perm_fam . " permessi attivi nel periodo
                    dal " . substr($data_inf, 6, 2) . "/" . substr($data_inf, 4, 2) . "/" . substr($data_inf, 0, 4) . "
                    al " . substr($data_sup, 6, 2) . "/" . substr($data_sup, 4, 2) . "/" . substr($data_sup, 0, 4) . "
                    Il numero massimo di permessi è " . $Anaperm_rec['MAXPERFAM']);
                $ok_quanti = false;
                return $ok_quanti;
            }
            if ($n_perm_fam >= $Anaperm_rec['MAXPERFAMPA'] && $Anaperm_rec['MAXPERFAMPA'] != 0 && $Isola_rec['FLPOSTOAUTO']) {
                $this->setErrMessage("La famiglia del soggetto possiede già " . $n_perm_fam . " permessi attivi nel periodo
                    dal " . substr($data_inf, 6, 2) . "/" . substr($data_inf, 4, 2) . "/" . substr($data_inf, 0, 4) . "
                    al " . substr($data_sup, 6, 2) . "/" . substr($data_sup, 4, 2) . "/" . substr($data_sup, 0, 4) . "
                    Il numero massimo di permessi per chi possiede un garage è " . $Anaperm_rec['MAXPERFAM']);
                $ok_quanti = false;
                return $ok_quanti;
            }
        } else {
            if (!$Anaperm_rec['FLNOANA']) {
                Out::msgStop("Attenzione", "Soggetto mancante del Codice Fiscale. Non riesco a contare i permessi già in possesso. Puoi continuare o annullare l'inserimento.");
            }
        }
        if ($Sz_imm != '' || $Fg_imm != '' || $Nm_imm != '' || $Sb_imm != '') {
            $tipo_p = $Isola_rec['MOTIVO'];
            $n_perm_imm = $this->ztlLib->ContaPermessiImm($tipo_p, $data_inf, $data_sup, $Sz_imm, $Fg_imm, $Nm_imm, $Sb_imm, "", "");
            $n_perm_imm = $n_perm_imm - $Storno_imm;
            if ($n_perm_imm >= $Anaperm_rec['MAXPERIMM'] && $Anaperm_rec['MAXPERIMM'] != 0) {
//                Out::msgStop("Attenzione", "L'immobile possiede già " . $n_perm_imm . "
//                        permessi attivi nel periodo dal " . $data_inf . " al " . $data_sup . " 
//                        Il numero massimo di permessi è " . $Anaperm_rec['MAXPERIMM']);
                $this->setErrMessage("La famiglia del soggetto possiede già " . $n_perm_imm . " permessi attivi nel periodo
                    dal " . substr($data_inf, 6, 2) . "/" . substr($data_inf, 4, 2) . "/" . substr($data_inf, 0, 4) . "
                    al " . substr($data_sup, 6, 2) . "/" . substr($data_sup, 4, 2) . "/" . substr($data_sup, 0, 4) . "
                    Il numero massimo di permessi è " . $Anaperm_rec['MAXPERFAM']);
                $ok_quanti = false;
                return $ok_quanti;
            }
        }
//conto i permessi per targa
        if ($Anaperm_rec['MAXPERTARGA'] != 0) {
//            $whereRowid='';
//            if($Isola_rec['ROWID']){
//                $whereRowid = " AND ROWID != " . $Isola_rec['ROWID'];
//            }
//            $sql = "SELECT CODICE FROM ISOLA WHERE TARGA = '" . $Isola_rec['TARGA'] . "' ".$whereRowid;// AND ROWID != " . $Isola_rec['ROWID'];
//            $tab = ItaDB::DBSQLSelect($this->ISOLA_DB, $sql, true);
//            $quanti = count($tab);
            $n_perm_targa = $this->ztlLib->ContaPermessiTarga($Isola_rec['MOTIVO'], $data_inf, $data_sup, $Isola_rec['TARGA']);
            $n_perm_targa = $n_perm_targa - $Storno_targa;
            if ($n_perm_targa >= $Anaperm_rec['MAXPERTARGA'] && $Anaperm_rec['MAXPERTARGA'] != 0) {
                $msg = "Il numero massimo di permessi con la targa inserita è  " . $Anaperm_rec['MAXPERTARGA'] . ". La targa inserita risulta già presente nei seguenti permessi:<br>";
                $sql = "SELECT CODICE FROM ISOLA WHERE TARGA = '" . $Isola_rec['TARGA'] . "' ";
                $tab = ItaDB::DBSQLSelect($this->ISOLA_DB, $sql, true);
                foreach ($tab as $rec) {
                    $msg .= $rec['CODICE'] . "<br>";
                }
                $this->setErrMessage($msg);
                return false;
            }
        }
        return $ok_quanti;
    }

    public function RinnovaPermessiItalsoft($praDati, $praDatiPratica, $currGesnum) {
        include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';
//        $Parametri = $this->devLib->getEnv_config('ZTL', 'codice', 'NPERM_MULTI', false);
//        $n_multi = $Parametri['CONFIG'];

        $proges_rec = $this->praLib->GetProges($currGesnum);

        //tutte le tipologie di importazione DEVONO avere i codici delle fiere selezionate in DENOM_FIERA
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='NAutorizzazione' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $NAutorizzazione = trim($prodag_rec['DAGVAL']);
        } else {
            $this->setErrMessage("Autorizzazione non trovata");
            return false;
        }
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='AnnoPermesso' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $Anno = trim($prodag_rec['DAGVAL']);
        } else {
            $this->setErrMessage("Anno non trovato");
            return false;
        }

        $sql = "SELECT * FROM ISOLA WHERE NUMERODOMANDA = '$NAutorizzazione' AND ANNOCODICE = $Anno AND DATACESSAZIONE = '' AND DATAANNULLAMENTO = ''";
        $Isola_tab = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sql, true);
        $targhe_arr = array();
        foreach ($Isola_tab as $Isola_rec) {
            $Anaperm_rec = $this->ztlLib->getAnaperm($Isola_rec['MOTIVO']);
            $NewIsola_rec = $Isola_rec;
            /*
             * NUOVI DATI
             * 
             * Codice, annocodice
             * Protocollo, data protocollo
             * numerodomanda, datadomanda
             * fattura, datafattura
             * 
             * datadecorrenza, datascadenza, datascadenzatemp
             * 
             */
            unset($NewIsola_rec['ROWID']);
            unset($NewIsola_rec['CODICE']);
            unset($NewIsola_rec['ANNOCODICE']);
            unset($NewIsola_rec['NUMPROTOCOLLO']);
            unset($NewIsola_rec['DATAPROTOCOLLO']);
            unset($NewIsola_rec['FATTURA']);
            unset($NewIsola_rec['DATAFATTURA']);
            unset($NewIsola_rec['NUMERODOMANDA']);
            unset($NewIsola_rec['DATADOMANDA']);
            unset($NewIsola_rec['IMPORTOQ']);
            unset($NewIsola_rec['DATAQ']);
            $Anaperm_rec = $this->ztlLib->getAnaperm($Isola_rec['MOTIVO']);
            $n_multi = $Anaperm_rec['MAXMULTITARGA'];
            if ($Anaperm_rec['TIPO'] == 'STAGIONALE') {
                $dataInizio_rec = $this->devLib->getEnv_config('ZTL', 'codice', 'INIZIOSTAGIONE', false);

                $NewIsola_rec['DATARILASCIO'] = date('Ymd');
                $NewIsola_rec['ORARILASCIO'] = date('H:i');
                $DataDecorrenza = $dataInizio_rec['CONFIG'];
                $NewIsola_rec['DATADECORRENZA'] = $dataInizio_rec['CONFIG'];
                $NewIsola_rec['ORADECORRENZA'] = $Isola_rec['ORADECORRENZA'];
                $retScad = $this->ztlLib->CalcolaScadenza($NewIsola_rec);
                $NewIsola_rec['DATASCADENZA'] = $retScad['DataScadenza'];
                $NewIsola_rec['ORASCADENZA'] = $retScad['OraScadenza'];
                $NewIsola_rec['DATASCADENZATEMP'] = $retScad['DataScadenzaTemp'];
            } else {
                $NewIsola_rec['DATARILASCIO'] = date('Ymd');
                $NewIsola_rec['ORARILASCIO'] = date('H:i');
                $DataDecorrenza = itaDate::addDays($Isola_rec['DATASCADENZA'], 1);
                $NewIsola_rec['DATADECORRENZA'] = $DataDecorrenza;
                $NewIsola_rec['ORADECORRENZA'] = $Isola_rec['ORADECORRENZA'];
                $retScad = $this->ztlLib->CalcolaScadenza($NewIsola_rec);
                $NewIsola_rec['DATASCADENZA'] = $retScad['DataScadenza'];
                $NewIsola_rec['ORASCADENZA'] = $retScad['OraScadenza'];
                $NewIsola_rec['DATASCADENZATEMP'] = $retScad['DataScadenzaTemp'];
            }

            $Ult_cod = $this->ztlLib->progressivoPermessi($Anaperm_rec);
            $NewIsola_rec['CODICE'] = $Ult_cod;
            $NewIsola_rec['ANNOCODICE'] = $NewIsola_rec['DATADECORRENZA'] != '' ? substr($NewIsola_rec['DATADECORRENZA'], 0, 4) : date('Y');
            $NewIsola_rec['NUMERODOMANDA'] = intval(substr($proges_rec['GESPRA'], 4));
            $NewIsola_rec['DATADOMANDA'] = $proges_rec['GESDRI'];
            $NewIsola_rec['NUMPROTOCOLLO'] = intval(substr($proges_rec['GESNPR'], 4));
            $metaDati = proIntegrazioni::GetMetedatiProt($proges_rec['GESNUM']);
            if (isset($metaDati['Data']) && $proges_rec['GESNPR'] != 0) {
                $NewIsola_rec['DATAPROTOCOLLO'] = $metaDati['Data'];
            }
            $ult_prog = $this->ztlLib->progressivoMotivi($NewIsola_rec['MOTIVO']);
            $NewIsola_rec['PROGMOTIVO'] = $ult_prog;
            $NewIsola_rec['DAPAGARE'] = $this->ztlLib->LeggiListino($NewIsola_rec) * $NewIsola_rec['PERIODI'];
            if ($Anaperm_rec['RINNOVOAUTOMATICO']) {
                //@TODO: prevedere casistica per cui non venga generato un nuovo permesso
                $Isola_rec['DATASCADENZA'] = $retScad['DataScadenza'];
                $Isola_rec['ORASCADENZA'] = $retScad['OraScadenza'];
                $this->updateRecord($this->ISOLA_DB, "ISOLA", $Isola_rec, "Rinnovato permesso " . $Isola_rec['CODICE'] . "/" . $Isola_rec['ANNOCODICE']);
                //inserisco i dati in ISOLASUAP
                $IsolaSuap_rec = array();
                $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
                $IsolaSuap_rec['TABELLA'] = 'ISOLA';
                $IsolaSuap_rec['ID'] = $Isola_rec['ROWID'];
                $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserito permesso da suap pratica " . $IsolaSuap_rec['SUAPRA']);
            }

            $insert_Info = 'Oggetto: Inserimento nuovo permesso richiesta ' . $NewIsola_rec['NUMERODOMANDA'];
            if (!$this->insertRecord($this->ISOLA_DB, 'ISOLA', $NewIsola_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento su ISOLA per la richiesta " . $Isola_rec['NUMERODOMANDA']);
                return false;
            }
            //rileggo il record inserito
            $lastId = $this->ISOLA_DB->getLastId();

            //inserisco i dati in ISOLASUAP
            $IsolaSuap_rec = array();
            $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
            $IsolaSuap_rec['TABELLA'] = 'ISOLA';
            $IsolaSuap_rec['ID'] = $lastId;
            $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserito permesso da suap pratica " . $IsolaSuap_rec['SUAPRA']);

            if (!$NewIsola_rec['FLCARPOOL']) {
                /*
                 * per ogni targa inserisco il record della variazione
                 */
                $sql = "SELECT MAX(PRG_VAR) AS NEWPROG FROM VARIAZIONI";
                $Max_prog = ItaDB::DBSQLSelect($this->ISOLA_DB, $sql, false);
                $new_prog = $Max_prog['NEWPROG'] + 1;
                $Variazioni_rec = array();
                $Variazioni_rec['PRG_VAR'] = $new_prog;
                $Variazioni_rec['CODICE'] = $NewIsola_rec['CODICE'];
                $Variazioni_rec['ANNOCODICE'] = $NewIsola_rec['ANNOCODICE'];
                $Variazioni_rec['NUMERODOMANDA'] = $NewIsola_rec['NUMERODOMANDA'];
                $Variazioni_rec['DATADOMANDA'] = $NewIsola_rec['DATADOMANDA'];
                $Variazioni_rec['NUMERODOMANDAV'] = $NewIsola_rec['NUMERODOMANDA'];
                $Variazioni_rec['DATADOMANDAV'] = $NewIsola_rec['DATADOMANDA'];
                $Variazioni_rec['NUMPROT'] = $NewIsola_rec['NUMPROTOCOLLO'];
                $Variazioni_rec['ANNOPROT'] = substr($NewIsola_rec['DATAPROTOCOLLO'], 0, 4);
                $Variazioni_rec['TARGA'] = $NewIsola_rec['TARGA'];
                $Tipofascia_rec = $this->ztlLib->getTipoFascia($Anaperm_rec['PROFILOORARIO']);
                $Variazioni_rec['CODICEFASCIA'] = $Tipofascia_rec['CODICELB'];
                $Variazioni_rec['ZONA'] = $NewIsola_rec['ZONA'];
                $Variazioni_rec['DATADECORRENZA'] = $NewIsola_rec['DATADECORRENZA'];
                $Variazioni_rec['ORADECORRENZA'] = $NewIsola_rec['ORADECORRENZA'];
                if ($NewIsola_rec['DAPAGARE'] > 0) {
                    $Variazioni_rec['DATASCADENZA'] = $NewIsola_rec['DATASCADENZATEMP'] != '' ? $NewIsola_rec['DATASCADENZATEMP'] : $NewIsola_rec['DATASCADENZA'];
                } else {
                    $Variazioni_rec['DATASCADENZA'] = $NewIsola_rec['DATASCADENZA'];
                }
                $Variazioni_rec['ORASCADENZA'] = '23:59';
                $Variazioni_rec['DATAVARIAZIONE'] = date('Ymd');
                $Variazioni_rec['ORAVARIAZIONE'] = date('H:i:s');
                $this->insertRecord($this->ISOLA_DB, "VARIAZIONI", $Variazioni_rec, "Inserimento prima Variazione targa $targa permesso " . $NewIsola_rec['CODICE']);
                //rileggo il record inserito
                $lastId = $this->ISOLA_DB->getLastId();

                //inserisco i dati in ISOLASUAP
                $IsolaSuap_rec = array();
                $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
                $IsolaSuap_rec['TABELLA'] = 'VARIAZIONI';
                $IsolaSuap_rec['ID'] = $lastId;
                $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserita variazione da suap pratica " . $IsolaSuap_rec['SUAPRA']);
            }

            /*
             * Per ogni domanda aggiungo i documenti allegati
             */
            $Pasdoc_tab = $this->praLib->GetPasdoc($proges_rec['GESNUM'], 'pratica', true);
            $n = 0;
            foreach ($Pasdoc_tab as $Pasdoc_rec) {
                $n++;
                $pramPath = $this->praLib->SetDirectoryPratiche(substr($Pasdoc_rec['PASKEY'], 0, 4), $Pasdoc_rec['PASKEY'], 'PASSO', false);
                $filePath = $pramPath . "/" . $Pasdoc_rec['PASFIL'];
                $isoladoc_rec = array();
                $isoladoc_rec['ANNOCODICE'] = $NewIsola_rec['ANNOCODICE'];
                $isoladoc_rec['CODICE'] = $NewIsola_rec['CODICE'];
                $isoladoc_rec['NUMERODOMANDA'] = $Isola_rec['NUMERODOMANDA'];
                $isoladoc_rec['DATADOMANDA'] = $Isola_rec['DATADOMANDA'];
                $isoladoc_rec['NUMERODOMANDAV'] = $Isola_rec['NUMERODOMANDA'];
                $isoladoc_rec['DATADOMANDAV'] = $Isola_rec['DATADOMANDA'];
                $isoladoc_rec['NUMPROT'] = $Isola_rec['NUMPROTOCOLLO'];
                $isoladoc_rec['ANNOPROT'] = $Isola_rec['DATAPROTOCOLLO'];
                $isoladoc_rec['PROGRESSIVO'] = $n;
                $isoladoc_rec['CODICEDOCUMENTO'] = $Pasdoc_rec['PASNAME'];
                $isoladoc_rec['NOTE'] = $Pasdoc_rec['PASNAME'];
//                    $isoladoc_rec['FILE'] = $randName;
                $isoladoc_rec['FILE'] = $Pasdoc_rec['PASFIL'];
                copy($filePath, $this->ztlLib->SetDirectoryZTL($NewIsola_rec['CODICE'], "PERMESSI", $NewIsola_rec['ANNOCODICE']) . DIRECTORY_SEPARATOR . $isoladoc_rec['FILE']);
                $insert_Info = "Oggetto: Inserimento nuovo documento permesso " . $Isola_rec['CODICE'] . " - " . $Isola_rec['ANNOCODICE'];
                if (!$this->insertRecord($this->ISOLA_DB, 'ISOLADOC', $isoladoc_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento nuovo documento richiesta " . $Isola_rec['NUMERODOMANDA']);
                    return false;
                }
                //rileggo il record inserito
                $lastId = $this->ISOLA_DB->getLastId();

                //inserisco i dati in ISOLASUAP
                $IsolaSuap_rec = array();
                $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
                $IsolaSuap_rec['TABELLA'] = 'ISOLADOC';
                $IsolaSuap_rec['ID'] = $lastId;
                $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserito documento da suap pratica " . $IsolaSuap_rec['SUAPRA']);
            }
        }
        //nel caso di un permesso multitarga prendo le targhe dell'ultimo permesso inserito e metto le variazioni
        if ($NewIsola_rec['FLCARPOOL']) {
            $targhe_arr = array();
            if ($NewIsola_rec['TARGA'] != '') {
                $targhe_arr[] = $NewIsola_rec['TARGA'];
            }
            if ($NewIsola_rec['TARGA1'] != '') {
                $targhe_arr[] = $NewIsola_rec['TARGA1'];
            }
            if ($NewIsola_rec['TARGA2'] != '') {
                $targhe_arr[] = $NewIsola_rec['TARGA2'];
            }
            if ($NewIsola_rec['TARGA3'] != '') {
                $targhe_arr[] = $NewIsola_rec['TARGA3'];
            }
            if ($NewIsola_rec['TARGA4'] != '') {
                $targhe_arr[] = $NewIsola_rec['TARGA4'];
            }
            if ($NewIsola_rec['TARGA5'] != '') {
                $targhe_arr[] = $NewIsola_rec['TARGA5'];
            }
            if ($NewIsola_rec['TARGA6'] != '') {
                $targhe_arr[] = $NewIsola_rec['TARGA6'];
            }
            if ($NewIsola_rec['TARGA7'] != '') {
                $targhe_arr[] = $NewIsola_rec['TARGA7'];
            }
            if ($NewIsola_rec['TARGA8'] != '') {
                $targhe_arr[] = $NewIsola_rec['TARGA8'];
            }
            if ($NewIsola_rec['TARGA9'] != '') {
                $targhe_arr[] = $NewIsola_rec['TARGA9'];
            }
            foreach ($targhe_arr as $targa) {
                /*
                 * per ogni targa inserisco il record della variazione
                 */
                $sql = "SELECT MAX(PRG_VAR) AS NEWPROG FROM VARIAZIONI";
                $Max_prog = ItaDB::DBSQLSelect($this->ISOLA_DB, $sql, false);
                $new_prog = $Max_prog['NEWPROG'] + 1;
                $Variazioni_rec = array();
                $Variazioni_rec['PRG_VAR'] = $new_prog;
                $Variazioni_rec['CODICE'] = $NewIsola_rec['CODICE']; //viene preso l'ultimo permesso inserito
                $Variazioni_rec['ANNOCODICE'] = $NewIsola_rec['ANNOCODICE'];
                $Variazioni_rec['NUMERODOMANDA'] = $NewIsola_rec['NUMERODOMANDA'];
                $Variazioni_rec['DATADOMANDA'] = $NewIsola_rec['DATADOMANDA'];
                $Variazioni_rec['NUMERODOMANDAV'] = $NewIsola_rec['NUMERODOMANDA'];
                $Variazioni_rec['DATADOMANDAV'] = $NewIsola_rec['DATADOMANDA'];
                $Variazioni_rec['NUMPROT'] = $NewIsola_rec['NUMPROTOCOLLO'];
                $Variazioni_rec['ANNOPROT'] = substr($NewIsola_rec['DATAPROTOCOLLO'], 0, 4);
                $Variazioni_rec['TARGA'] = $targa;
                $Tipofascia_rec = $this->ztlLib->getTipoFascia($Anaperm_rec['PROFILOORARIO']);
                $Variazioni_rec['CODICEFASCIA'] = $Tipofascia_rec['CODICELB'];
                $Variazioni_rec['ZONA'] = $NewIsola_rec['ZONA'];
                $Variazioni_rec['DATADECORRENZA'] = $NewIsola_rec['DATADECORRENZA'];
                $Variazioni_rec['ORADECORRENZA'] = $NewIsola_rec['ORADECORRENZA'];
                if ($NewIsola_rec['DAPAGARE'] > 0) {
                    $Variazioni_rec['DATASCADENZA'] = $NewIsola_rec['DATASCADENZATEMP'] != '' ? $NewIsola_rec['DATASCADENZATEMP'] : $NewIsola_rec['DATASCADENZA'];
                } else {
                    $Variazioni_rec['DATASCADENZA'] = $NewIsola_rec['DATASCADENZA'];
                }
                $Variazioni_rec['ORASCADENZA'] = '23:59';
                $Variazioni_rec['DATAVARIAZIONE'] = date('Ymd');
                $Variazioni_rec['ORAVARIAZIONE'] = date('H:i:s');
                $this->insertRecord($this->ISOLA_DB, "VARIAZIONI", $Variazioni_rec, "Inserimento prima Variazione targa $targa permesso " . $NewIsola_rec['CODICE']);
                //rileggo il record inserito
                $lastId = $this->ISOLA_DB->getLastId();

                //inserisco i dati in ISOLASUAP
                $IsolaSuap_rec = array();
                $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
                $IsolaSuap_rec['TABELLA'] = 'VARIAZIONI';
                $IsolaSuap_rec['ID'] = $lastId;
                $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserita variazione da suap pratica " . $IsolaSuap_rec['SUAPRA']);
            }
        }
        return true;
    }

    public function RinnovaPermessoOsimo($currGesnum) {
        include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';
//        $Parametri = $this->devLib->getEnv_config('ZTL', 'codice', 'NPERM_MULTI', false);
//        $n_multi = $Parametri['CONFIG'];

        $proges_rec = $this->praLib->GetProges($currGesnum);

        //tutte le tipologie di importazione DEVONO avere i codici delle fiere selezionate in DENOM_FIERA
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='BADGE' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $NBadge = trim($prodag_rec['DAGVAL']);
        } else {
            $this->setErrMessage("Numero Badge non trovato");
            return false;
        }

        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='AnnoPermesso' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $Anno = trim($prodag_rec['DAGVAL']);
        } else {
            $this->setErrMessage("Anno non trovato");
            return false;
        }

        /*
         * Ricerco il permesso
         */
        $sql = "SELECT * FROM ISOLA WHERE ROWID IN (SELECT I.ROWID FROM ISOLA I 
                                JOIN STORICOBADGE S ON I.CODICE = S.CODICE AND I.ANNOCODICE = S.ANNOCODICE 
                                WHERE S.FINEVALIDITA = '' AND S.NUMEROBADGE = '$NBadge' AND S.NONVALIDO <> 1)";

        $Isola_tab = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sql, true);
        $result = count($Isola_tab);
        if ($result > 1) {
            $this->setErrMessage("Trovati più permessi attivi per badge numero " . $NBadge);
            return false;
        }

        foreach ($Isola_tab as $Isola_rec) {
            $Anaperm_rec = $this->ztlLib->getAnaperm($Isola_rec['MOTIVO']);
            $NewIsola_rec = $Isola_rec;
            /*
             * NUOVI DATI
             * 
             * Codice, annocodice
             * Protocollo, data protocollo
             * numerodomanda, datadomanda
             * fattura, datafattura
             * 
             * datadecorrenza, datascadenza, datascadenzatemp
             * 
             * TO DO METTO DATA SCADENZA SU STORICO BADGE SU PERMESSO_OLD
             */
            unset($NewIsola_rec['ROWID']);
            unset($NewIsola_rec['CODICE']);
            unset($NewIsola_rec['ANNOCODICE']);
            unset($NewIsola_rec['NUMPROTOCOLLO']);
            unset($NewIsola_rec['DATAPROTOCOLLO']);
            unset($NewIsola_rec['FATTURA']);
            unset($NewIsola_rec['DATAFATTURA']);
            unset($NewIsola_rec['NUMERODOMANDA']);
            unset($NewIsola_rec['DATADOMANDA']);
            unset($NewIsola_rec['IMPORTOQ']);
            unset($NewIsola_rec['DATAQ']);
            $Anaperm_rec = $this->ztlLib->getAnaperm($Isola_rec['MOTIVO']);

            //$n_multi = $Anaperm_rec['MAXMULTITARGA'];
            switch ($Anaperm_rec['TIPO']) {
                case 'STAGIONALE':
                    $dataInizio_rec = $this->devLib->getEnv_config('ZTL', 'codice', 'INIZIOSTAGIONE', false);
                    $NewIsola_rec['DATARILASCIO'] = date('Ymd');
                    $NewIsola_rec['ORARILASCIO'] = date('H:i');
                    $DataDecorrenza = $dataInizio_rec['CONFIG'];
                    $NewIsola_rec['DATADECORRENZA'] = $dataInizio_rec['CONFIG'];
                    $NewIsola_rec['ORADECORRENZA'] = '00:00';
                    $retScad = $this->ztlLib->CalcolaScadenza($NewIsola_rec);
                    $NewIsola_rec['DATASCADENZA'] = $retScad['DataScadenza'];
                    $NewIsola_rec['ORASCADENZA'] = '23:59';
                    $NewIsola_rec['DATASCADENZATEMP'] = $retScad['DataScadenzaTemp'];
                    break;
                case 'ANNUALE':
                    $NewIsola_rec['DATADECORRENZA'] = $NewIsola_rec['DATAQ'] = $NewIsola_rec['DATARILASCIO'] = date('Ymd');
                   // $NewIsola_rec['DATARILASCIO'] = date('Ymd');
                    $NewIsola_rec['ORARILASCIO'] = date('H:i');
                    $AnnoDecorrenza = intval(substr($Isola_rec['DATASCADENZA'], 0, 4)) + 1;
                   // $NewIsola_rec['DATADECORRENZA'] = $AnnoDecorrenza . '0101';
                    $NewIsola_rec['ORADECORRENZA'] = '00:00';
                    $retScad = $this->ztlLib->CalcolaScadenza($NewIsola_rec);
                    $NewIsola_rec['DATASCADENZA'] = $AnnoDecorrenza . '1231';
                    $NewIsola_rec['ORASCADENZA'] = '23:59';
                    $NewIsola_rec['DATASCADENZATEMP'] = $retScad['DataScadenzaTemp'];
                    break;

                default:
                    //Out::msgStop('ATTENZIONE', 'Durata Permesso non configurato');
                    //return false;
                    $NewIsola_rec['DATADECORRENZA'] = $NewIsola_rec['DATAQ'] = $NewIsola_rec['DATARILASCIO'] = date('Ymd');
                    $NewIsola_rec['ORARILASCIO'] = date('H:i');
                    $AnnoDecorrenza = intval(substr($Isola_rec['DATASCADENZA'], 0, 4)) + 1;
                    // $NewIsola_rec['DATADECORRENZA'] = $AnnoDecorrenza . '0101';
                    $NewIsola_rec['ORADECORRENZA'] = '00:00';
                    $retScad = $this->ztlLib->CalcolaScadenza($NewIsola_rec);
                    $NewIsola_rec['DATASCADENZA'] = $AnnoDecorrenza . '1231';
                    $NewIsola_rec['ORASCADENZA'] = '23:59';
                    $NewIsola_rec['DATASCADENZATEMP'] = $retScad['DataScadenzaTemp'];
                    break;
            }


            $Ult_cod = $this->ztlLib->progressivoPermessi($Anaperm_rec);
            $NewIsola_rec['CODICE'] = $Ult_cod;
            $NewIsola_rec['ANNOCODICE'] = $NewIsola_rec['DATADECORRENZA'] != '' ? substr($NewIsola_rec['DATADECORRENZA'], 0, 4) : date('Y');
            $NewIsola_rec['NUMERODOMANDA'] = intval(substr($proges_rec['GESPRA'], 4));
            $NewIsola_rec['DATADOMANDA'] = $proges_rec['GESDRI'];
            $NewIsola_rec['NUMPROTOCOLLO'] = intval(substr($proges_rec['GESNPR'], 4));
            $metaDati = proIntegrazioni::GetMetedatiProt($proges_rec['GESNUM']);
            if (isset($metaDati['Data']) && $proges_rec['GESNPR'] != 0) {
                $NewIsola_rec['DATAPROTOCOLLO'] = $metaDati['Data'];
            }
            $ult_prog = $this->ztlLib->progressivoMotivi($NewIsola_rec['MOTIVO']);
            $NewIsola_rec['PROGMOTIVO'] = $ult_prog;        //???????????????????
            $NewIsola_rec['DAPAGARE'] = $this->ztlLib->LeggiListino($NewIsola_rec) * $NewIsola_rec['PERIODI'];

            $insert_Info = 'Oggetto: Inserimento nuovo permesso richiesta ' . $NewIsola_rec['NUMERODOMANDA'];
            if (!$this->insertRecord($this->ISOLA_DB, 'ISOLA', $NewIsola_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento su ISOLA per la richiesta " . $Isola_rec['NUMERODOMANDA']);
                return false;
            }
            //rileggo il record inserito
            $lastId = $this->ISOLA_DB->getLastId();

            /*
             * metto data scadenza sul  badge
             * vecchio permesso
             * TO DO metto foreach???
             */

            $sqlB = "SELECT * FROM STORICOBADGE "
                    . "WHERE CODICE = " . $Isola_rec['CODICE'] . " AND ANNOCODICE = " . $Isola_rec['ANNOCODICE'] .
                    " AND FINEVALIDITA = '' AND NONVALIDO <> 1 AND NUMEROBADGE = '$NBadge'";

            $StoricoBadgeOLD_rec = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sqlB, false);
            if (!$StoricoBadgeOLD_rec) {
                Out::msgStop('ATTENZIONE', 'ATTENZIONE annullamento badge su vecchio permesso n ' . $Isola_rec['CODICE'] . '/' . $Isola_rec['ANNOCODICE'] . ' non riuscito');
            }
            $update_Info = 'Rinnovo permesso da fascicolo - cessato badge ' . $NBadge . ' su permesso ' . $Isola_rec['CODICE'] . '/' . $Isola_rec['ANNOCODICE'];
            $StoricoBadgeOLD_rec['FINEVALIDITA'] = date('Ymd');
            $this->updateRecord($this->ztlLib->getISOLADB(), 'STORICOBADGE', $StoricoBadgeOLD_rec, $update_Info);

            /*
             * aggancio il badge alla domanda creata
             */
            $StoricoBadgeNEW_rec = array(
                'NUMEROBADGE' => $NBadge,
                'INIZIOVALIDITA' => $NewIsola_rec['DATADECORRENZA'],
                'CODICE' => $NewIsola_rec['CODICE'],
                'ANNOCODICE' => $NewIsola_rec['ANNOCODICE'],
            );
            $insertBadge = $this->insertRecord($this->ztlLib->getISOLADB(), "STORICOBADGE", $StoricoBadgeNEW_rec, " Badge " . $NBadge . " da suap pratica su permesso " . $NewIsola_rec['CODICE'] . "/" . $NewIsola_rec['ANNOCODICE']);
            if (!$insertBadge) {
                Out::msgStop('ATTENZIONE', 'ATTENZIONE non sono riuscito a creare il badge ' . $NBadge . ' su permesso ' . $NewIsola_rec['CODICE'] . "/" . $NewIsola_rec['ANNOCODICE']);
            }

            //inserisco i dati in ISOLASUAP
            $IsolaSuap_rec = array();
            $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
            $IsolaSuap_rec['TABELLA'] = 'ISOLA';
            $IsolaSuap_rec['ID'] = $lastId;
            $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserito permesso da suap pratica " . $IsolaSuap_rec['SUAPRA']);

            /*
             * Per ogni domanda aggiungo i documenti allegati
             */
            $Pasdoc_tab = $this->praLib->GetPasdoc($proges_rec['GESNUM'], 'pratica', true);
            $n = 0;
            foreach ($Pasdoc_tab as $Pasdoc_rec) {
                $n++;
                $pramPath = $this->praLib->SetDirectoryPratiche(substr($Pasdoc_rec['PASKEY'], 0, 4), $Pasdoc_rec['PASKEY'], 'PASSO', false);
                $filePath = $pramPath . "/" . $Pasdoc_rec['PASFIL'];
                $isoladoc_rec = array();
                $isoladoc_rec['ANNOCODICE'] = $NewIsola_rec['ANNOCODICE'];
                $isoladoc_rec['CODICE'] = $NewIsola_rec['CODICE'];
                $isoladoc_rec['NUMERODOMANDA'] = $Isola_rec['NUMERODOMANDA'];
                $isoladoc_rec['DATADOMANDA'] = $Isola_rec['DATADOMANDA'];
                $isoladoc_rec['NUMERODOMANDAV'] = $Isola_rec['NUMERODOMANDA'];
                $isoladoc_rec['DATADOMANDAV'] = $Isola_rec['DATADOMANDA'];
                $isoladoc_rec['NUMPROT'] = $Isola_rec['NUMPROTOCOLLO'];
                $isoladoc_rec['ANNOPROT'] = $Isola_rec['DATAPROTOCOLLO'];
                $isoladoc_rec['PROGRESSIVO'] = $n;
                $isoladoc_rec['CODICEDOCUMENTO'] = $Pasdoc_rec['PASNAME'];
                $isoladoc_rec['NOTE'] = $Pasdoc_rec['PASNAME'];
//                    $isoladoc_rec['FILE'] = $randName;
                $isoladoc_rec['FILE'] = $Pasdoc_rec['PASFIL'];
                copy($filePath, $this->ztlLib->SetDirectoryZTL($NewIsola_rec['CODICE'], "PERMESSI", $NewIsola_rec['ANNOCODICE']) . DIRECTORY_SEPARATOR . $isoladoc_rec['FILE']);
                $insert_Info = "Oggetto: Inserimento nuovo documento permesso " . $Isola_rec['CODICE'] . " - " . $Isola_rec['ANNOCODICE'];
                if (!$this->insertRecord($this->ISOLA_DB, 'ISOLADOC', $isoladoc_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento nuovo documento richiesta " . $Isola_rec['NUMERODOMANDA']);
                    return false;
                }
                //rileggo il record inserito
                $lastId = $this->ISOLA_DB->getLastId();

                //inserisco i dati in ISOLASUAP
                $IsolaSuap_rec = array();
                $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
                $IsolaSuap_rec['TABELLA'] = 'ISOLADOC';
                $IsolaSuap_rec['ID'] = $lastId;
                $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserito documento da suap pratica " . $IsolaSuap_rec['SUAPRA']);
            }
        }
        //INSERISCO RECORD VARIAZIONI
        //
        //nel caso di un permesso multitarga prendo le targhe dell'ultimo permesso inserito e metto le variazioni
        //  if ($NewIsola_rec['FLCARPOOL']) {
        $targhe_arr = array();
        if ($NewIsola_rec['TARGA'] != '') {
            $targhe_arr[] = $NewIsola_rec['TARGA'];
        }
        if ($NewIsola_rec['TARGA1'] != '') {
            $targhe_arr[] = $NewIsola_rec['TARGA1'];
        }
        if ($NewIsola_rec['TARGA2'] != '') {
            $targhe_arr[] = $NewIsola_rec['TARGA2'];
        }
        if ($NewIsola_rec['TARGA3'] != '') {
            $targhe_arr[] = $NewIsola_rec['TARGA3'];
        }
        if ($NewIsola_rec['TARGA4'] != '') {
            $targhe_arr[] = $NewIsola_rec['TARGA4'];
        }
        if ($NewIsola_rec['TARGA5'] != '') {
            $targhe_arr[] = $NewIsola_rec['TARGA5'];
        }
        if ($NewIsola_rec['TARGA6'] != '') {
            $targhe_arr[] = $NewIsola_rec['TARGA6'];
        }
        if ($NewIsola_rec['TARGA7'] != '') {
            $targhe_arr[] = $NewIsola_rec['TARGA7'];
        }
        if ($NewIsola_rec['TARGA8'] != '') {
            $targhe_arr[] = $NewIsola_rec['TARGA8'];
        }
        if ($NewIsola_rec['TARGA9'] != '') {
            $targhe_arr[] = $NewIsola_rec['TARGA9'];
        }
        foreach ($targhe_arr as $targa) {
            /*
             * per ogni targa inserisco il record della variazione
             */
            $sql = "SELECT MAX(PRG_VAR) AS NEWPROG FROM VARIAZIONI";
            $Max_prog = ItaDB::DBSQLSelect($this->ISOLA_DB, $sql, false);
            $new_prog = $Max_prog['NEWPROG'] + 1;
            $Variazioni_rec = array();
            $Variazioni_rec['PRG_VAR'] = $new_prog;
            $Variazioni_rec['CODICE'] = $NewIsola_rec['CODICE']; //viene preso l'ultimo permesso inserito
            $Variazioni_rec['ANNOCODICE'] = $NewIsola_rec['ANNOCODICE'];
            $Variazioni_rec['NUMERODOMANDA'] = $NewIsola_rec['NUMERODOMANDA'];
            $Variazioni_rec['DATADOMANDA'] = $NewIsola_rec['DATADOMANDA'];
            $Variazioni_rec['NUMERODOMANDAV'] = $NewIsola_rec['NUMERODOMANDA'];
            $Variazioni_rec['DATADOMANDAV'] = $NewIsola_rec['DATADOMANDA'];
            $Variazioni_rec['NUMPROT'] = $NewIsola_rec['NUMPROTOCOLLO'];
            $Variazioni_rec['ANNOPROT'] = substr($NewIsola_rec['DATAPROTOCOLLO'], 0, 4);
            $Variazioni_rec['TARGA'] = $targa;
            $Tipofascia_rec = $this->ztlLib->getTipoFascia($Anaperm_rec['PROFILOORARIO']);
            $Variazioni_rec['CODICEFASCIA'] = $Tipofascia_rec['CODICELB'];
            $Variazioni_rec['ZONA'] = $NewIsola_rec['ZONA'];
            $Variazioni_rec['DATADECORRENZA'] = $NewIsola_rec['DATADECORRENZA'];
            $Variazioni_rec['ORADECORRENZA'] = $NewIsola_rec['ORADECORRENZA'];
            if ($NewIsola_rec['DAPAGARE'] > 0) {
                $Variazioni_rec['DATASCADENZA'] = $NewIsola_rec['DATASCADENZATEMP'] != '' ? $NewIsola_rec['DATASCADENZATEMP'] : $NewIsola_rec['DATASCADENZA'];
            } else {
                $Variazioni_rec['DATASCADENZA'] = $NewIsola_rec['DATASCADENZA'];
            }
            $Variazioni_rec['ORASCADENZA'] = '23:59';
            $Variazioni_rec['DATAVARIAZIONE'] = date('Ymd');
            $Variazioni_rec['ORAVARIAZIONE'] = date('H:i:s');
            $this->insertRecord($this->ISOLA_DB, "VARIAZIONI", $Variazioni_rec, "Inserimento prima Variazione targa $targa permesso " . $NewIsola_rec['CODICE']);
            //rileggo il record inserito
            $lastId = $this->ISOLA_DB->getLastId();

            //inserisco i dati in ISOLASUAP
            $IsolaSuap_rec = array();
            $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
            $IsolaSuap_rec['TABELLA'] = 'VARIAZIONI';
            $IsolaSuap_rec['ID'] = $lastId;
            $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserita variazione da suap pratica " . $IsolaSuap_rec['SUAPRA']);
        }
        //  }
        return true;
    }

    public function VariaTarghe($praDati, $praDatiPratica, $currGesnum) {
//        $Parametri = $this->devLib->getEnv_config('ZTL', 'codice', 'NPERM_MULTI', false);
//        $n_multi = $Parametri['CONFIG'];

        $proges_rec = $this->praLib->GetProges($currGesnum);
        $fl_inserisci = false;
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='FL_INSERISCI' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if ($prodag_rec) {
            $fl_inserisci = trim($prodag_rec['DAGVAL']);
        }
        $Isola_tab = array();
        //verifico nel caso in cui fl_inserisci=true se il permesso è già stato inserito
        if ($fl_inserisci) {
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='TipoPermesso' AND DAGVAL <> ''";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $TipoPermesso = trim($prodag_rec['DAGVAL']);
            } else {
                $this->setErrMessage("Tipo Permesso non trovato");
                return false;
            }
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='PROGMOTIVO' AND DAGVAL <> ''";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $ProgMotivo = trim($prodag_rec['DAGVAL']);
            }
            $sqlChk = "SELECT * FROM ISOLA WHERE MOTIVO = '$TipoPermesso' AND PROGMOTIVO = '$ProgMotivo'";
            $Isola_tab = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sqlChk, true);
            if ($Isola_tab) {
                $fl_inserisci = false;
            }
        }

        if (!$fl_inserisci) {
            if (!$Isola_tab) {
                $Parametr_Badge = $this->devLib->getEnv_config('ZTL', 'codice', 'ZTLBADGE', false);
                if ($Parametr_Badge['CONFIG']) {
                    /*
                     * Vedo se è attiva la gestione Badge
                     * 
                     */
                    $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='BADGE' AND DAGVAL <> ''";
                    $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                    if ($prodag_rec) {
                        $Nbadge = trim($prodag_rec['DAGVAL']);
                    } else {
                        $this->setErrMessage("Badge non trovato");
                        return false;
                    }
                    //  $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='AnnoPermesso' AND DAGVAL <> ''";
                    // $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);


                    $sql = "SELECT * FROM ISOLA WHERE ROWID IN (SELECT I.ROWID FROM ISOLA I 
                                JOIN STORICOBADGE S ON I.CODICE = S.CODICE AND I.ANNOCODICE = S.ANNOCODICE 
                                WHERE S.FINEVALIDITA = '' AND NUMEROBADGE = '$Nbadge')";

                    $Isola_tab = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sql, true);
                    $result = count($Isola_tab);
                    if ($result > 1) {
                        $this->setErrMessage("Trovati due permessi attivi per badge numero " . $Nbadge);
                        return false;
                    }
                } else {
                    $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='NAutorizzazione' AND DAGVAL <> ''";
                    $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                    if ($prodag_rec) {
                        $NAutorizzazione = trim($prodag_rec['DAGVAL']);
                    } else {
                        $this->setErrMessage("Autorizzazione non trovata");
                        return false;
                    }
                    $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='AnnoPermesso' AND DAGVAL <> ''";
                    $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                    if ($prodag_rec) {
                        $Anno = trim($prodag_rec['DAGVAL']);
                        $sql = "SELECT * FROM ISOLA WHERE NUMERODOMANDA = '$NAutorizzazione' AND ANNOCODICE = $Anno";
                    } else {
                        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='TipoPermesso' AND DAGVAL <> ''";
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
                        if ($prodag_rec) {
                            $TipoPermesso = trim($prodag_rec['DAGVAL']);
                            $sql = "SELECT * FROM ISOLA WHERE MOTIVO = '$TipoPermesso' AND PROGMOTIVO = '$NAutorizzazione' ORDER BY ANNOCODICE DESC"; //prendo l'ultimo anno disponibile
                        } else {
                            $this->setErrMessage("Anno non trovato");
                            return false;
                        }
                    }
                    $Isola_tab = ItaDB::DBSQLSelect($this->ztlLib->getISOLADB(), $sql, true);
                }
            }
        } else {
            /*
             * gestire inserimento del nuovo permesso
             * casistica INVALIDI_NR
             */
            $Isola_tab = array();
            $Isola_rec = array();
            $arr_dati = array();
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='TipoPermesso' AND DAGVAL <> ''";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $TipoPermesso = trim($prodag_rec['DAGVAL']);
            } else {
                $this->setErrMessage("Tipo Permesso non trovato");
                return false;
            }
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='PROGMOTIVO' AND DAGVAL <> ''";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $ProgMotivo = trim($prodag_rec['DAGVAL']);
            }
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='MULTITARGA' AND DAGVAL <> ''";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $Multitarga = trim($prodag_rec['DAGVAL']);
            }
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='ZONA' AND DAGVAL <> ''";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $Zona = trim($prodag_rec['DAGVAL']);
            }
            $arr_dati['MOTIVO'] = $TipoPermesso;
            $Anaperm_rec = $this->ztlLib->getAnaperm($TipoPermesso);

            $DataScadenza = "";
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DataScadenza' AND DAGVAL <> ''";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $DataScadenza = substr($prodag_rec['DAGVAL'], 6, 4) . substr($prodag_rec['DAGVAL'], 3, 2) . substr($prodag_rec['DAGVAL'], 0, 2);
            }
            foreach ($praDati as $dato) {

                /*
                 * DATI INTESTATARIO
                 */
                if ($dato['DAGKEY'] == "NOME")
                    $arr_dati['NOME'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "INTCOGNOME" || $dato['DAGKEY'] == 'DICHIARANTE_COGNOME')
                    $arr_dati['INTCOGNOME'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "INTNOME" || $dato['DAGKEY'] == 'DICHIARANTE_NOME')
                    $arr_dati['INTNOME'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "SESSO" || $dato['DAGKEY'] == 'DICHIARANTE_SESSO_SEX')
                    $arr_dati['SESSO'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "CODICEFISCALE" || $dato['DAGKEY'] == 'DICHIARANTE_CODICEFISCALE_CFI')
                    $arr_dati['CODICEFISCALE'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "LGNASCITA")
                    $arr_dati['LGNASCITA'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "PROVNASCITA")
                    $arr_dati['PROVNASCITA'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "DATANASCITA" || $dato['DAGKEY'] == 'DICHIARANTE_NASCITADATA_DATA')
                    $arr_dati['DATANASCITA'] = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
                if ($dato['DAGKEY'] == "VIA" || $dato['DAGKEY'] == 'DICHIARANTE_RESIDENZAVIA')
                    $arr_dati['VIA'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "NUMERO" || $dato['DAGKEY'] == 'DICHIARANTE_RESIDENZACIVICO')
                    $arr_dati['NUMERO'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "LETTERA")
                    $arr_dati['LETTERA'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "CAPRESIDENZA" || $dato['DAGKEY'] == 'DICHIARANTE_RESIDENZACAP')
                    $arr_dati['CAPRESIDENZA'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "CITTA" || $dato['DAGKEY'] == 'DICHIARANTE_RESIDENZACOMUNE')
                    $arr_dati['CITTA'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "PROVRESIDENZA" || $dato['DAGKEY'] == 'DICHIARANTE_RESIDENZAPROVINCIA_PV')
                    $arr_dati['PROVRESIDENZA'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "TELEFINTESTA")
                    $arr_dati['TELEFINTESTA'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "CELLINTESTA")
                    $arr_dati['CELLINTESTA'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "INTMAIL")
                    $arr_dati['INTMAIL'] = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "TIPOLOGIAINTESTATARIO")
                    $arr_dati['TIPOLOGIAINTESTATARIO'] = strtoupper($dato['DAGVAL']);
            }
            $Isola_rec = $arr_dati; //precarico tutti i dati trovati
            if (!$Isola_rec['NOME']) {
                $Isola_rec['NOME'] = trim($Isola_rec['INTCOGNOME'] . " " . $Isola_rec['INTNOME']);
            }
            $Isola_rec['DATARILASCIO'] = date('Ymd');
            $Isola_rec['ORARILASCIO'] = date('H:i');
            $Isola_rec['PERIODI'] = $NPeriodi;
            $Isola_rec['DATADECORRENZA'] = $DataPermesso != '' ? $DataPermesso : $proges_rec['GESDRI'];
//                $Isola_rec['ORADECORRENZA'] = $proges_rec['GESORA'];
            $Isola_rec['ORADECORRENZA'] = '00:00';
            $retScad = $this->ztlLib->CalcolaScadenza($Isola_rec);
            if ($DataScadenza) {
                $Isola_rec['DATASCADENZA'] = $DataScadenza;
                $Isola_rec['DATASCADENZATEMP'] = $DataScadenza;
            } else {
                $Isola_rec['DATASCADENZA'] = $retScad['DataScadenza'];
                $Isola_rec['DATASCADENZATEMP'] = $retScad['DataScadenzaTemp'];
            }

            $Isola_rec['ORASCADENZA'] = $retScad['OraScadenza'];
            $Ult_cod = $this->ztlLib->progressivoPermessi($Anaperm_rec);
            $Isola_rec['CODICE'] = $Ult_cod;
            $Isola_rec['ANNOCODICE'] = $Isola_rec['DATADECORRENZA'] != '' ? substr($Isola_rec['DATADECORRENZA'], 0, 4) : date('Y');
            if ($ProgMotivo) {
                $Isola_rec['PROGMOTIVO'] = $ProgMotivo;
            } else {
                $ult_prog = $this->ztlLib->progressivoMotivi($TipoPermesso);
                $Isola_rec['PROGMOTIVO'] = $ult_prog;
            }
            $Isola_rec['DATAINSERIMENTO'] = date('Ymd');
            //non importo nessuna targa, trattandosi di procedimento di variazione

            $Isola_rec['FLCARPOOL'] = $Multitarga;
            if ($Anaperm_rec['FLCTRRIMESSA']) {
                $Isola_rec['FLPOSTOAUTO'] = true;
            }
            $Isola_rec['CODICEFASCIA'] = $Anaperm_rec['PROFILOORARIO'];
            //decodifica della zona a partire dalla via
            $via = "";
            if ($Anaperm_rec['FLCTRRES']) {
                $via = $Isola_rec['VIA'];
            }
            if ($Anaperm_rec['FLCTRDITTA']) {
                $via = $Isola_rec['UBICAZIONE'];
            }
            if ($Anaperm_rec['FLCTRRIMESSA']) {
                $via = $Isola_rec['VIAGARAGE'];
            }
            if ($via != '') {
                $sql = "SELECT * FROM VIE WHERE LUOGOINFRAZIONE = '" . addslashes($via) . "'";
                $Vie_rec = ItaDB::DBSQLSelect($this->ISOLA_DB, $sql, false);
                if ($Vie_rec['ZONA'] != '') {
                    $Zona = $Vie_rec['ZONA'];
                }
            }
            $Zona = substr($Zona, 0, 2); //zona è al massimo due caratteri
            $Isola_rec['ZONA'] = $Zona;
            $Isola_rec['DAPAGARE'] = $this->ztlLib->LeggiListino($Isola_rec) * $Isola_rec['PERIODI'];
            if (!$this->controllaQuantiPass($Isola_rec)) {
                return false;
            }
            if ($Isola_rec['CODICEFISCALE'] == '') {
                $this->setErrMessage("Attenzione: codice fiscale mancante");
                return false;
            }
            $insert_Info = 'Oggetto: Inserimento nuovo permesso richiesta ' . $Isola_rec['NUMERODOMANDA'];
            if (!$this->insertRecord($this->ISOLA_DB, 'ISOLA', $Isola_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento su ISOLA per la richiesta " . $Isola_rec['NUMERODOMANDA']);
                return false;
            }
            //rileggo il record inserito
            $lastId = $this->ISOLA_DB->getLastId();

            //inserisco i dati in ISOLASUAP
            $IsolaSuap_rec = array();
            $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
            $IsolaSuap_rec['TABELLA'] = 'ISOLA';
            $IsolaSuap_rec['ID'] = $lastId;
            $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserito permesso da suap pratica " . $IsolaSuap_rec['SUAPRA']);


            //inserisco i dati del soggetto nella tabella RESIDENTI (usata per tutti i soggetti), in caso li aggiorno
            $sqlS = "SELECT * FROM RESIDENTI WHERE CODICEFISCALE = '" . $Isola_rec['CODICEFISCALE'] . "'";
            $Soggetto_rec = ItaDB::DBSQLSelect($this->ISOLA_DB, $sqlS, false);
            if ($Soggetto_rec) {
                if ($Isola_rec['VIA'] != '') {
                    $Soggetto_rec['INDIRIZZO'] = $Isola_rec['VIA'];
                }
                if ($Isola_rec['NUMERO'] != '') {
                    $Soggetto_rec['CIVICO'] = $Isola_rec['NUMERO'];
                }
                if ($Isola_rec['LETTERA'] != '') {
                    $Soggetto_rec['LETTERA'] = $Isola_rec['LETTERA'];
                }
                if ($Anaperm_rec['FLCTRRES']) {
                    $Soggetto_rec['RESIDENTE'] = true;
                }
                $this->updateRecord($this->ISOLA_DB, "RESIDENTI", $Soggetto_rec, "Aggiornato soggetto " . $Soggetto_rec['CODICEFISCALE']);
            } else {
                $Soggetto_rec = array();
                $Soggetto_rec['CODICEFISCALE'] = $Isola_rec['CODICEFISCALE'];
                $Soggetto_rec['COGNOME'] = $arr_dati['INTCOGNOME'];
                $Soggetto_rec['NOME'] = $arr_dati['INTNOME'];
                $Soggetto_rec['DENOMINAZIONE'] = $Isola_rec['NOME'];
                $Soggetto_rec['SESSO'] = $Isola_rec['SESSO'];
                $Soggetto_rec['DATANASCITA'] = $Isola_rec['DATANASCITA'];
                $Soggetto_rec['COMUNENASCITA'] = $Isola_rec['LGNASCITA'];
                $Soggetto_rec['COMUNENASCITA'] = $Isola_rec['LGNASCITA'];
                $Soggetto_rec['INDIRIZZO'] = $Isola_rec['VIA'];
                $Soggetto_rec['CIVICO'] = $Isola_rec['NUMERO'];
                $Soggetto_rec['LETTERA'] = $Isola_rec['LETTERA'];
                $Soggetto_rec['DATAINSERIMENTO'] = date('Ymd');
                if ($Anaperm_rec['FLCTRRES']) {
                    $Soggetto_rec['RESIDENTE'] = true;
                }
                $Soggetto_rec['EMAIL'] = $Isola_rec['INTMAIL'];
                $Soggetto_rec['CELLULARE'] = $Isola_rec['CELLINTESTA'];
                $codice = $this->ztlLib->getLastCodiceSoggetti();
                $Soggetto_rec['CODICE'] = $codice + 1;
                $this->insertRecord($this->ISOLA_DB, "RESIDENTI", $Soggetto_rec, "Inserito soggetto " . $Soggetto_rec['CODICEFISCALE']);
            }
            //rilettura del record ISOLA
            $sqlI = "SELECT * FROM ISOLA WHERE MOTIVO = '" . $Isola_rec['MOTIVO'] . "' AND PROGMOTIVO = " . $Isola_rec['PROGMOTIVO'];
            $Isola_tab = ItaDB::DBSQLSelect($this->ISOLA_DB, $sqlI, true);
        }

        //lista targhe inserite
        for ($i = 1; $i <= 10; $i++) {
            $n = str_pad($i, 2, "0", STR_PAD_LEFT);
//            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='OLD_TARGA_" . $n . "' AND DAGVAL <> ''";
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='OLD_TARGA_" . $n . "'";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $old_targhe_arr[$i - 1] = trim(strtoupper($prodag_rec['DAGVAL']));
            }
//            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='NEW_TARGA_" . $n . "' AND DAGVAL <> ''";
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='NEW_TARGA_" . $n . "'";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($prodag_rec) {
                $new_targhe_arr[$i - 1] = trim(strtoupper($prodag_rec['DAGVAL']));
//                if (!isset($old_targhe_arr[$i-1])){
//                    $old_targhe_arr[$i-1] = '';
//                }
            }
        }
        if (count($new_targhe_arr) != count($old_targhe_arr)) {
            $this->setErrMessage("Errore nel numero di targhe comunicato.");
            return false;
        }

        $Parametr_rec = $this->ztlLib->GetParametr();
        $variazione = $cambiotarga = false;
        foreach ($Isola_tab as $Isola_rec) {
            $old_dati_perm = $Isola_rec;
            foreach ($old_targhe_arr as $k => $TargaOld) {
                for ($i = 1; $i <= 10; $i++) {
                    if ($i == 1) {
                        $t = "";
                    } else {
                        $t = $i - 1;
                    }
                    if ($TargaOld == $Isola_rec['TARGA' . $t]) {
                        if (!isset($new_targhe_arr[$k]) || $new_targhe_arr[$k] == '') {
                            continue;
                        }
                        $Isola_rec['TARGA' . $t] = $new_targhe_arr[$k];
                        $cambiotarga = true;
//                        continue 2;
                        break; //una volta salvata la nuova targa cerco la prossima
                    }
                }
            }
            if (!$cambiotarga) {
                Out::msgStop('ATTENZIONE', 'Nessuna modifica targa su permesso riuscita<br>Controllare se è stato superato il numero massimo di targhe');
                return false;
            }
            $ok_log_varia = $this->ztlLib->CheckVariazioni($Parametr_rec['TIPOVARCHI'], $Isola_rec['CODICE'], "CONTROLLA", $old_dati_perm, $Isola_rec);
            if (!$ok_log_varia) {
                continue;
            }
            $variazione = true;
            try {
                ItaDB::DBUpdate($this->ISOLA_DB, "ISOLA", "ROWID", $Isola_rec);
            } catch (Exception $exc) {
                $this->setErrMessage("Errore in aggiornamento ISOLA: " . $exc->getMessage());
                return false;
            }

            /*
             * Per ogni domanda aggiungo i documenti allegati
             */
            $Pasdoc_tab = $this->praLib->GetPasdoc($proges_rec['GESNUM'], 'pratica', true);
            $n = 0;

            foreach ($Pasdoc_tab as $Pasdoc_rec) {
                $sqlProg = "SELECT MAX(PROGRESSIVO) AS ULTIMO FROM ISOLADOC WHERE CODICE = " . $Isola_rec['CODICE'] . " AND ANNOCODICE = " . $Isola_rec['ANNOCODICE'];
                $ult_rec = ItaDB::DBSQLSelect($this->ISOLA_DB, $sqlProg, false);
                $pramPath = $this->praLib->SetDirectoryPratiche(substr($Pasdoc_rec['PASKEY'], 0, 4), $Pasdoc_rec['PASKEY'], 'PASSO', false);
                $filePath = $pramPath . "/" . $Pasdoc_rec['PASFIL'];
                $isoladoc_rec = array();
                $isoladoc_rec['ANNOCODICE'] = $Isola_rec['ANNOCODICE'];
                $isoladoc_rec['CODICE'] = $Isola_rec['CODICE'];
                $isoladoc_rec['NUMERODOMANDA'] = $Isola_rec['NUMERODOMANDA'];
                $isoladoc_rec['DATADOMANDA'] = $Isola_rec['DATADOMANDA'];
                $isoladoc_rec['NUMERODOMANDAV'] = intval(substr($proges_rec['GESPRA'], 4));
                $isoladoc_rec['DATADOMANDAV'] = $proges_rec['GESDRI'];
                $isoladoc_rec['NUMPROT'] = intval(substr($proges_rec['GESNPR'], 4));
                $isoladoc_rec['ANNOPROT'] = intval(substr($proges_rec['GESNPR'], 0, 4));
                $isoladoc_rec['PROGRESSIVO'] = $ult_rec['ULTIMO'] + 1;
                $isoladoc_rec['CODICEDOCUMENTO'] = $Pasdoc_rec['PASNAME'];
                $isoladoc_rec['NOTE'] = $Pasdoc_rec['PASNAME'];
//                    $isoladoc_rec['FILE'] = $randName;
                $isoladoc_rec['FILE'] = $Pasdoc_rec['PASFIL'];
                copy($filePath, $this->ztlLib->SetDirectoryZTL($Isola_rec['CODICE'], "PERMESSI", $Isola_rec['ANNOCODICE']) . DIRECTORY_SEPARATOR . $isoladoc_rec['FILE']);
                $insert_Info = "Oggetto: Inserimento nuovo documento permesso " . $Isola_rec['CODICE'] . " - " . $Isola_rec['ANNOCODICE'];
                if (!$this->insertRecord($this->ISOLA_DB, 'ISOLADOC', $isoladoc_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento nuovo documento richiesta " . $Isola_rec['NUMERODOMANDA']);
                    return false;
                }
                //rileggo il record inserito
                $lastId = $this->ISOLA_DB->getLastId();

                //inserisco i dati in ISOLASUAP
                $IsolaSuap_rec = array();
                $IsolaSuap_rec['SUAPRA'] = $proges_rec['GESNUM'];
                $IsolaSuap_rec['TABELLA'] = 'ISOLADOC';
                $IsolaSuap_rec['ID'] = $lastId;
                $this->insertRecord($this->ztlLib->getISOLADB(), "ISOLASUAP", $IsolaSuap_rec, "Inserito documento da suap pratica " . $IsolaSuap_rec['SUAPRA']);
            }

            $param = array();
            $param['dataVariazione'] = $proges_rec['GESDRI'];
            $param['oraVariazione'] = $proges_rec['GESORA'];
            $param['NUMERODOMANDAV'] = intval(substr($proges_rec['GESPRA'], 4));
            $param['DATADOMANDAV'] = $proges_rec['GESDRI'];
            $param['NUMPROT'] = intval(substr($proges_rec['GESNPR'], 4));
            $param['ANNOPROT'] = intval(substr($proges_rec['GESNPR'], 0, 4));

            $ret_var = $this->ztlLib->LogVariazioni($Parametr_rec['TIPOVARCHI'], $Isola_rec['CODICE'], 'SCRIVI', $Isola_rec['ANNOCODICE'], $param);
            if ($ret_var['Status'] == true) {
                if ($ret_var['prg'] == 0 || $ret_var['prg'] === false) {
                    $this->setErrMessage("Dati Variazioni non Registrati.! Le modifiche al Pass non saranno passate al sistema varchi! Verificare! ... ");
                    return false;
                }
            }
        }
        if (!$variazione) {
            $this->setErrMessage("Non risultano targhe da variare o variazione già effettuata");
            return false;
        }
        //nel caso di un permesso multitarga prendo le targhe dell'ultimo permesso inserito e metto le variazioni

        return true;
    }

}
