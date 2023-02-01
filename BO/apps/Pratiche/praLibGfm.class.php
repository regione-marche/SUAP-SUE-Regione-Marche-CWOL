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
include_once ITA_BASE_PATH . '/apps/Gafiere/gfmLib.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIntegrazioni.class.php';

class praLibGfm extends itaModel {

    /**
     * Libreria di funzioni Generiche e Utility per Integrazione SUAP/FIERE
     */
    public $praLib;
    public $gfmLib;
    public $devLib;
    public $PRAM_DB;
    public $GAFIERE_DB;
    private $errMessage;
    private $errCode;
    public $eqAudit;

    function __construct() {
        parent::__construct();
        $this->praLib = new praLib();
        $this->gfmLib = new gfmLib();
        $this->devLib = new devLib();
        $this->eqAudit = new eqAudit();
        $this->PRAM_DB = $this->praLib->getPRAMDB();
        $this->GAFIERE_DB = $this->gfmLib->getGAFIEREDB();
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

    public function CollegaFiere($praDati, $praDatiPratica, $currGesnum, $rowidFiera = false, $arrSelezionate = array()) {
        /*
         * Gestione delle importazioni differenziata per tipologia 
         * default: "italsoft"
         */

        $Parametri = $this->devLib->getEnv_config('GAFIERE', 'codice', 'GFMTIPOSUAP', false);
        $tipo_import = $Parametri['CONFIG'];
        if (!$tipo_import) {
            $tipo_import = "italsoft";
        }
        switch ($tipo_import) {
            case 'italsoft':
                if (!$this->CollegaFiereItalsoft($praDati, $praDatiPratica, $currGesnum, $rowidFiera, $arrSelezionate)) {
                    return false;
                }
                break;
            case "I462":
                if (!$this->CollegaFiereSassuolo($praDati, $praDatiPratica, $currGesnum, $rowidFiera, $arrSelezionate)) {
                    return false;
                }
                break;

            default:
                $this->setErrMessage("Tipo di importazione domande fiere non configurato correttamente. Importazione non eseguita.");
                return false;
                break;
        }
        return true;
    }

    public function CollegaFiereAltroSUAP($dittaGAFIERE, $dittaSUAP, $praDati, $praDatiPratica, $currGesnum, $rowidFiera = false, $arrSelezionate = array()) {
        if (!$dittaGAFIERE) {
            $this->setErrMessage("Codice ente GAFIERE non configurato.");
            return false;
        }
        if (!$dittaSUAP) {
            $this->setErrMessage("Codice ente SUAP non configurato.");
            return false;
        }
        try {
            $GAFIERE_DB = ItaDB::DBOpen('GAFIERE', $dittaGAFIERE);
        } catch (Exception $exc) {
            $this->setErrMessage("Errore apertura database GAFIERE.");
            return false;
        }
        try {
            $PRAM_DB = ItaDB::DBOpen('PRAM', $dittaSUAP);
        } catch (Exception $exc) {
            $this->setErrMessage("Errore apertura database PRAM.");
            return false;
        }
        try {
            $GAFIERE_SUAP = ItaDB::DBOpen('GAFIERE', $dittaSUAP);
        } catch (Exception $exc) {
            $this->setErrMessage("Errore apertura database GAFIERE SUAP.");
            return false;
        }

        //tutte le tipologie di importazione DEVONO avere i codici delle fiere selezionate in DENOM_FIERA
        $prodag_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DENOM_FIERA'", false);
        if (!$prodag_rec && !$arrSelezionate) {
            if (!$rowidFiera) {
                $this->setErrMessage("Codici Fiera non trovati");
                return false;
            }
        }
        //POSTEGGI_FIERA è solo per alcune domande, controllo solo la presenza
        $prodag_rec_posto = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='POSTEGGI_FIERA'", false);
        $arrayCodFiere = unserialize($prodag_rec['DAGVAL']);
        if (!$arrayCodFiere) {
            if ($rowidFiera) {
//                $Fiere_rApp::$utente->getKey('ditta');ec = ItaDB::DBSQLSelect($this->GAFIERE_DB, "SELECT * FROM FIERE WHERE ROWID = $rowidFiera", false);
//                $arrayCodFiere = array($Fiere_rec['FIERA'] => 1);
            } else {
                if ($arrSelezionate) {
                    $arrayCodFiere = $arrSelezionate;
                } else {
                    $this->setErrMessage("Codici Fiera non trovati: " . $prodag_rec['DAGVAL']);
                    return false;
                }
            }
        }
        //cerco tutte le fiere a cui si è scelto di partecipare
        $fiere_tab = array();
        foreach ($arrayCodFiere as $rowid => $valore) {
            if ($valore == 0 || $valore = '') {
                continue;
            }
//            $sql = "SELECT * FROM FIERE WHERE FIERA = '$codFiera' AND DATATERMINE >= '" . $proric_rec['RICDAT'] . "'";
//            $sql = "SELECT * FROM FIERE WHERE FIERA = '$codFiera' AND DATATERMINE >= '" . $proric_rec['RICDAT'] . "' AND DECENNALE = 0"; //non posso inserire domande per fiere decennali
            $sql = "SELECT * FROM FIERE WHERE ROWID = $rowid";
            if ($rowidFiera) {
                $sql = "SELECT * FROM FIERE WHERE ROWID = $rowidFiera";
                $fiere_rec = ItaDB::DBSQLSelect($GAFIERE_SUAP, $sql, false); //ci può essere al più una fiera creata per tipo
                $fiere_tab[] = $fiere_rec;
                break;
            }
            $fiere_rec = ItaDB::DBSQLSelect($GAFIERE_SUAP, $sql, false); //ci può essere al più una fiera creata per tipo
            $fiere_tab[] = $fiere_rec;
        }


        //cerco la data di invio domanda
        //$sql = "SELECT * FROM PRORIC WHERE RICNUM = '" . $_POST[$this->nameForm . '_PROGES']['GESPRA'] . "'";
//        $proges_rec = $this->praLib->GetProges($currGesnum);
        $proges_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PROGES WHERE GESNUM = '$currGesnum'", false);
        $sql = "SELECT * FROM PRORIC WHERE RICNUM = '" . $proges_rec['GESPRA'] . "'";
        $proric_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);

        foreach ($praDatiPratica as $dato) {
            if ($dato['DAGKEY'] == "ESIBENTE_PEC") {
                $pec = $dato['DAGVAL'];
            }
        }
        $impIndividuale = $Legale = false;
        foreach ($praDati as $dato) {
            if ($dato['DAGKEY'] == "DICHIARANTE_NATURALEGA_RADIO") {
                if ($dato['DAGVAL'] == "T") {
                    $impIndividuale = true;
                } elseif ($dato['DAGVAL'] == "L") {
                    $Legale = true;
                }
            }
            if ($dato['DAGKEY'] == "DICHIARANTE_CODICEFISCALE_CFI")
                $cfDichiarante = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NOME")
                $nome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_COGNOME")
                $cognome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_SESSO_SEX")
                $sesso = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITADATA_DATA")
                $dataNascita = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITACOMUNE")
                $comuneNascita = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITACOMUNE")
                $comuneNascita = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZAVIA")
                $indirizzoResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACOMUNE")
                $comuneResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACIVICO")
                $civicoResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACAP_CAP")
                $capResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZAPROVINCIA_PV")
                $provinciaResidenza = strtoupper($dato['DAGVAL']);
            //dati della licenza
//            if ($dato['DAGKEY'] == "FIERE_TIPOLICENZA")
//                $tipoLic = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "FIERE_NUMEROLICENZA")
                $numeroLic = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "FIERE_DATARILASCIO")
                $rilascioLic = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "FIERE_LICENZA_SETTOREALIM")
                $alimentare = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "FIERE_LICENZA_SETTORENONALIM")
                $nonAlimentare = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "FIERE_COMUNERILASCIO")
                $comuneLic = strtoupper($dato['DAGVAL']);
        }

        $fiscaleIndividuale = "";
        if ($impIndividuale == true) {
            foreach ($praDati as $dato) {
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PARTITAIVA_PIVA")
                    $fiscaleIndividuale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECOMUNE")
                    $comuneSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDEVIA")
                    $indirizzoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECIVICO")
                    $civicoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECAP")
                    $capSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDEPROVINCIA_PV")
                    $provinciaSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_TELEFONO")
                    $telefonoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_NUMREGISTROIMPRESE")
                    $numRegImprese = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_DATAREGISTROIMPRESE")
                    $dataRegImprese = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_CCIAA")
                    $comuneCCIAA = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PEC")
                    $pecIndividuale = $dato['DAGVAL'];
            }
        }

        $fiscaleLegale = $PivaLegale = "";
        if ($Legale == true) {
            foreach ($praDati as $dato) {
                if ($dato['DAGKEY'] == "IMPRESA_CODICEFISCALE_CFI")
                    $fiscaleLegale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_PARTITAIVA_PIVA")
                    $PivaLegale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_RAGIONESOCIALE")
                    $ragSoc = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECOMUNE")
                    $comuneSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDEVIA")
                    $indirizzoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECIVICO")
                    $civicoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECAP")
                    $capSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDEPROVINCIA_PV")
                    $provinciaSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_TELEFONO")
                    $telefonoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_NUMREGISTROIMPRESE")
                    $numRegImprese = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_COSTITUZIONE_DATA")
                    $dataRegImprese = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
                if ($dato['DAGKEY'] == "IMPRESA_CCIAA")
                    $comuneCCIAA = strtoupper($dato['DAGVAL']);
            }
        }

        foreach ($fiere_tab as $fiereSel_rec) {
            /*
             * PER OGNI FIERA CERCO L'ENTE ASSOCIATO
             */
//            $Anafiere_rec = $this->gfmLib->GetAnafiere($fiereSel_rec['FIERA']);
            $sqlAF = "SELECT * FROM ANAFIERE WHERE TIPO = '" . $fiereSel_rec['FIERA'] . "'";
            $Anafiere_rec = ItaDB::DBSQLSelect($GAFIERE_SUAP, $sqlAF, false);
            if (!$Anafiere_rec) {
                $this->setErrMessage("Anagrafica fiera " . $fiereSel_rec['FIERA'] . " non trovata. Procedura interrotta");
                return false;
            }
            //do per scontato che l'ente da cui si importa è diverso dall'ente di origine
            //cerco la fiera dentro l'ente collegato, se non c'è esco
            $AnafiereCheck_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT * FROM ANAFIERE WHERE TIPO = '" . $Anafiere_rec['CODICEFIERAENTE'] . "'", false);
            if (!$AnafiereCheck_rec) {
                $this->setErrMessage("Anagrafica fiera collegata " . $Anafiere_rec['CODICEFIERAENTE'] . " non trovata. Procedura interrotta.");
                return false;
            }
            //cerco la fiera con la stessa data, se c'è seleziono quella, altrimenti la creo.
            $sql = "SELECT * FROM FIERE WHERE FIERA = '" . $AnafiereCheck_rec['TIPO'] . "' AND DATA = '" . $fiereSel_rec['DATA'] . "' AND ASSEGNAZIONE = '" . $fiereSel_rec['ASSEGNAZIONE'] . "' AND DECENNALE = '" . $fiereSel_rec['DECENNALE'] . "' AND TIPONS = '" . $fiereSel_rec['TIPONS'] . "'";
            $fiereCheck_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if ($fiereCheck_rec) {
                $fiere_rec = $fiereCheck_rec;
            } else {
                //se non l'ho trovata la inserisco
                $new_fiera = $fiereSel_rec;
                unset($new_fiera['ROWID']);
                $new_fiera['FIERA'] = $AnafiereCheck_rec['FIERA'];
                $this->insertRecord($GAFIERE_DB, "FIERE", $new_fiera, "Inserimento nuova fiera da SUAP " . $new_fiera['FIERA'] . " - " . $new_fiera['DATA']);
                //rileggo il record appena inserito
                $sql = "SELECT * FROM FIERE WHERE FIERA = '" . $AnafiereCheck_rec['TIPO'] . "' AND DATA = '" . $fiereSel_rec['DATA'] . "' AND ASSEGNAZIONE = '" . $fiereSel_rec['ASSEGNAZIONE'] . "' AND DECENNALE = '" . $fiereSel_rec['DECENNALE'] . "' AND TIPONS = '" . $fiereSel_rec['TIPONS'] . "'";
                $fiere_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                if (!$fiere_rec) {
                    $this->setErrMessage("Fiera non inserita correttamente <br>" . print_r($fiere_rec, true));
                    return false;
                }
            }

            /*
             * RICERCA ED EVENTUALE INSERIMENTO DI DITTA E LICENZA
             */
            $anaditta_rec = array();
            if ($fiscaleIndividuale != "") {
                $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '" . addslashes($fiscaleIndividuale) . "' OR PIVA = '" . addslashes($fiscaleIndividuale) . "')";
                $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            }
            if (!$anaditta_rec) {
                if ($cfDichiarante != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '" . addslashes($cfDichiarante) . "' OR PIVA = '" . addslashes($cfDichiarante) . "')";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                }
            }
            if (!$anaditta_rec) {
                if ($fiscaleLegale != "") {
                    $sql = "SELECT 
                        ANADITTA.* 
                    FROM 
                        ANADITTA 
                     JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                     WHERE 
                        DITTESOGG.CF = '" . addslashes($fiscaleLegale) . "'";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '" . $fiscaleLegale . "' OR PIVA = '" . $fiscaleLegale . "')";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
            }
            if (!$anaditta_rec) {
                if ($PivaLegale != "") {
                    $sql = "SELECT 
                        ANADITTA.* 
                    FROM 
                        ANADITTA 
                     JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                     WHERE 
                        DITTESOGG.PIVA = '" . addslashes($PivaLegale) . "'";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE (PIVA = '" . addslashes($PivaLegale) . "' OR CODICEFISCALE = '" . addslashes($PivaLegale) . "')";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
            }
            $Parametri = $this->devLib->getEnv_config('GAFIERE', 'codice', 'GFMBLOCCAANAGDAPRA', false);
            $bloccaIns = $Parametri['CONFIG'];
            //Se non trovato e se non bloccato lo inserisco
            if (!$anaditta_rec && !$bloccaIns) {
                $max_codice_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT MAX(CODICE) AS MASSIMO FROM ANADITTA", false);
                $anaditta_rec['CODICE'] = $max_codice_rec['MASSIMO'] + 1;
                $anaditta_rec['DENOMINAZIONE'] = $impIndividuale ? $cognome . " " . $nome : $ragSoc;
                $anaditta_rec['SESSO'] = $sesso;
                $anaditta_rec['DATANASCITA'] = $dataNascita;
                $anaditta_rec['COMUNENASCITA'] = $comuneNascita;
                $anaditta_rec['COMUNE'] = $comuneSede;
                $anaditta_rec['INDIRIZZO'] = $indirizzoSede;
                $anaditta_rec['NUMEROCIVICO'] = $civicoSede;
                $anaditta_rec['CAP'] = $capSede;
                $anaditta_rec['PROVINCIA'] = $provinciaSede;
                $anaditta_rec['CODICEFISCALE'] = $impIndividuale ? $fiscaleIndividuale : $fiscaleLegale;
                $anaditta_rec['PIVA'] = $PivaLegale;
                $anaditta_rec['TELEFONO'] = $telefonoSede;
                //$this->eqAudit->logEqEvent($this, array('Operazione' => "SCARICO FOTOGRAMMI SERV:" . $servizio_rec['CODICESERV'] . DIRECTORY_SEPARATOR . $servizio_rec['ANNOSERV']));
                $insert_Info = 'Oggetto: Inserimento Nuova Ditta Codice ' . $anaditta_rec['CODICE'];
                if (!$this->insertRecord($GAFIERE_DB, 'ANADITTA', $anaditta_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento su ANADITTE per la ditta codice " . $anaditta_rec['CODICE']);
                    return false;
                }
                //$anaditta_rec = $this->gfmLib->GetAnaditta($anaditta_rec['CODICE']);
                $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT * FROM ANADITTA WHERE CODICE = " . $anaditta_rec['CODICE'], false);

                //Se c'è il legale rappresentante inseriesco anche il soggetto
                if ($Legale == true) {
                    $dittesogg_rec = array();
                    $dittesogg_rec['CODICE'] = $anaditta_rec['CODICE'];
                    $dittesogg_rec['RUOCOD'] = "0014";
                    $dittesogg_rec['NOMINATIVO'] = $cognome . " " . $nome;
                    $dittesogg_rec['DATANASCITA'] = $dataNascita;
                    $dittesogg_rec['COMUNENASCITA'] = $comuneNascita;
                    $dittesogg_rec['INDIRIZZO'] = $indirizzoResidenza;
                    $dittesogg_rec['CIVICO'] = $civicoResidenza;
                    $dittesogg_rec['COMUNE'] = $comuneResidenza;
                    $dittesogg_rec['CAP'] = $capResidenza;
                    $dittesogg_rec['PROVINCIA'] = $provinciaResidenza;
                    $dittesogg_rec['TELEFONO'] = $telefonoSede;
                    $dittesogg_rec['CF'] = $cfDichiarante;
                    $dittesogg_rec['PIVA'] = $PivaLegale;
                    $insert_Info = 'Oggetto: Inserimento Nuovo Soggetto ditta ' . $anaditta_rec['CODICE'] . " - cf $cfDichiarante";
                    if (!$this->insertRecord($GAFIERE_DB, 'DITTESOGG', $dittesogg_rec, $insert_Info)) {
                        $this->setErrMessage("Errore in inserimento Nuovo Soggetto ditta " . $anaditta_rec['CODICE'] . " - cf $cfDichiarante");
                        return false;
                    }
                }
            }
            //se non ho ancora la ditta restituisco messaggio di errore
            if (!$anaditta_rec) {
                $this->setErrMessage("Ditta non trovata o inserimento impossibile. Procedura interrotta.");
                return false;
            }

            /*
             * ricerca licenza - eventuale inserimento
             */
            $dittelic_rec = array();
            //non considero il tipo perchè potrebbero esserci incongruenze - quasi impossibile che qualcuno abbia due licenze con stesso numero e tipo diverso
            if ($numeroLic == "") {
                $this->setErrMessage("1 - Numero licenza non specificato");
                return false;
            }
            $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND NUMERO = '" . addslashes($numeroLic) . "'";
            $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if (!$dittelic_rec) {
                $tipoLic = "B";
                //inserisco la licenza
                $dittelic_rec['CODICE'] = $anaditta_rec['CODICE'];
                $dittelic_rec['TIPOAUTORIZZAZIONE'] = $tipoLic;
                $dittelic_rec['NUMERO'] = $numeroLic;
                $dittelic_rec['DATARILASCIO'] = $dittelic_rec['DATARILASCIOLICENZA'] = $rilascioLic;
                $dittelic_rec['COMUNE'] = $comuneLic;
                if ($alimentare && !$nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "ALIMENTARE";
                }
                if (!$alimentare && $nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "NON ALIMENTARE";
                }
                if ($alimentare && $nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "MISTO";
                }
                $dittelic_rec['NUMEROISCRREGD'] = $numRegImprese;
                $dittelic_rec['DATAISCRLICENZA'] = $dataRegImprese;
                $dittelic_rec['DATAISCRIZIONEREGD'] = $dataRegImprese;
                $dittelic_rec['CCIAA'] = $comuneCCIAA;
                $insert_Info = 'Oggetto: Inserimento Nuova licenza ditta ' . $anaditta_rec['CODICE'] . " - lic $numeroLic";
                if (!$this->insertRecord($GAFIERE_DB, 'DITTELIC', $dittelic_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento Nuova licenza ditta " . $anaditta_rec['CODICE'] . " - lic $numeroLic");
                    return false;
                }
                //rileggo il record appena inserito
                $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND TIPOAUTORIZZAZIONE = '" . $dittelic_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . addslashes($dittelic_rec['NUMERO']) . "'";
                $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            }
            if ($dittelic_rec['DATAINIZIOATTIVITALIC'] == '') {
                $dittelic_rec['DATAINIZIOATTIVITALIC'] = $rilascioLic;
            }
            if ($dittelic_rec['DATAINIZIOATTIVITA'] == '') {
                $dittelic_rec['DATAINIZIOATTIVITA'] = $rilascioLic;
            }
            if (!$dittelic_rec) {
                $this->setErrMessage("Licenza non trovata o inserimento impossibile. Procedura interrotta!");
                return false;
            }
            /*
             * FINE RICERCA/INSERIMENTO DITTE E LICENZE
             */



            $fierecom_rec = array();
            $fierecom_rec['FIERA'] = $fiere_rec['FIERA'];
//            $fierecom_rec['FIERA'] = $Anafiere_rec['FIERA'];
            $fierecom_rec['DATA'] = $fiere_rec['DATA'];
            $fierecom_rec['ASSEGNAZIONE'] = $fiere_rec['ASSEGNAZIONE'];
            $fierecom_rec['TIPOATTIVITA'] = $anaditta_rec['TIPOATTIVITA'];
            $fierecom_rec['CODICE'] = $anaditta_rec['CODICE'];
            $fierecom_rec['TIPOAUTORIZZAZIONE'] = $dittelic_rec['TIPOAUTORIZZAZIONE'];
            $fierecom_rec['NUMERO'] = $dittelic_rec['NUMERO'];
            $fierecom_rec['DENOMINAZIONE'] = $anaditta_rec['DENOMINAZIONE'];
            $fierecom_rec['DATADOMANDA'] = $proric_rec['RICDAT'];
            $fierecom_rec['TIPONS'] = "NORMALE";
            $fierecom_rec['PEC'] = $pec;
            if ($prodag_rec_posto) {
                $fierecom_rec['POSTO'] = $prodag_rec_posto['DAGVAL'];
            } else {
                //cerco se c'è in anagrafica della fiera
                $sql = "SELECT * FROM FIEREPOS WHERE TIPO = '" . $fiere_rec['FIERA'] . "' AND CODICEDITTA = " . $anaditta_rec['CODICE'];
                $fierepos_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                if ($fierepos_rec) {
                    $fierecom_rec['POSTO'] = $fierepos_rec['POSTO'];
                    $fierecom_rec['CODICEVIA'] = $fierepos_rec['CODICEVIA'];
                }
            }
            //$fierecom_rec['IDFASCICOLO'] = $proges_rec['ROWID'];
            //verifico che la domanda non sia già presente
            $sql = "SELECT * FROM FIERECOM WHERE FIERA = '" . $fierecom_rec['FIERA'] . "' AND DATA = '" . $fierecom_rec['DATA'] . "' AND CODICE = " . $fierecom_rec['CODICE'] . ""
                    . " AND TIPOAUTORIZZAZIONE = '" . $fierecom_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $fierecom_rec['NUMERO'] . "'";
            $sql .= " AND CODICEVIA = '" . $fierecom_rec['CODICEVIA'] . "'";
            $check_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);

            if ($check_rec) {
                $fierecom_rec = $check_rec;
                $Anafiere = $this->gfmLib->GetAnafiere($fierecom_rec['FIERA']);
                $this->setErrMessage("Domanda per la fiera " . $Anafiere['FIERA'] . " già presente! Procedura interrotta.<br>");
                return false;
            }
            $insert_Info = "Oggetto: Inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'];
            try {
                ItaDB::DBInsert($GAFIERE_DB, "FIERECOM", "ROWID", $fierecom_rec);
            } catch (Exception $exc) {
                $this->setErrMessage("Errore in inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'] . "<br><br>" . $exc->getMessage());
                return false;
            }
//            if (!$this->insertRecord($GAFIERE_DB, 'FIERECOM', $fierecom_rec, $insert_Info)) {
//                $this->setErrMessage("Errore in inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA']);
//                return false;
//            }
            //rileggo il record di fierecom
            $sql = "SELECT * FROM FIERECOM WHERE FIERA = '" . $fierecom_rec['FIERA'] . "' AND DATA = '" . $fierecom_rec['DATA'] . "' AND CODICE = " . $fierecom_rec['CODICE'] . ""
                    . " AND TIPOAUTORIZZAZIONE = '" . $fierecom_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $fierecom_rec['NUMERO'] . "'";
            //come condizione impongo anche il codice via
            $sql .= " AND CODICEVIA = '" . $fierecom_rec['CODICEVIA'] . "'";

            $fierecom_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if (!$fierecom_rec) {
                $this->setErrMessage("Non trovo la domanda appena inserita, procedura interrotta. Controllare!<br>" . print_r($fierecom_rec, true));
                return false;
            }
            //inserimento tabella di collegamento
            $fieresuap_rec = array();
            $fieresuap_rec['IDFIERECOM'] = $fierecom_rec['ROWID'];
            $fieresuap_rec['SUAPID'] = $proges_rec['ROWID'];
            $fieresuap_rec['SUAKEY'] = $dittaSUAP;
            $insert_Info = "Oggetto: Inserimento collegamento suap-fierecom " . $fierecom_rec['ROWID'] . " - pratica: " . $proges_rec['ROWID'];
            if (!$this->insertRecord($GAFIERE_DB, 'FIERESUAP', $fieresuap_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento collegamento suap-fierecom " . $fierecom_rec['ROWID'] . " - pratica: " . $proges_rec['ROWID']);
                return false;
            }

            /*
             * Per ogni domanda aggiungo i documenti allegati in FIEREDOC - prendo il rapporto completo
             */
//            $model = 'praFascicolo.class';
//            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//            $praFascicolo = new praFascicolo($currGesnum);
//            $allegati = $praFascicolo->getAllegatiProtocollaPratica();
            $allegati = $this->getAllegatoPrincipaleAltroSUAP($dittaSUAP, $proges_rec);
            if ($allegati['Principale']['Stream']) {
                //dato che ho il base64 posso creare il file dentro la directory delle domande
                $randName = md5(rand() * time()) . ".pdf.p7m";
                $doc_path = $this->gfmLib->SetDirectoryGafiere($fierecom_rec['FIERA'], "FIEREDOC", true, $Anafiere_rec['ENTEFIERE']);
                $fieredoc_rec = array();
                $fieredoc_rec['DOCUMENTO'] = $allegati['Principale']['Nome'];
                $fieredoc_rec['NOTE'] = $allegati['Principale']['Descrizione'];
                $fieredoc_rec['DATAPRESENTAZIONE'] = $proric_rec['RICDAT'];
                $fieredoc_rec['ID_DOMANDA'] = $fierecom_rec['ROWID'];
                $fieredoc_rec['NECESSARIO'] = "N";
                $fieredoc_rec['FILE'] = $randName;
//                $pathDest = $doc_path . DIRECTORY_SEPARATOR . $randName;
                file_put_contents($doc_path . DIRECTORY_SEPARATOR . $randName, $allegati['Principale']['Stream']);
                $insert_Info = "Oggetto: Inserimento nuovo documento ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'];
                if (!$this->insertRecord($GAFIERE_DB, 'FIEREDOC', $fieredoc_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento nuovo documento ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA']);
                    return false;
                }
            }
        }

        return true;
    }

    public function CollegaFiereItalsoft($praDati, $praDatiPratica, $currGesnum, $rowidFiera, $arrSelezionate = array()) {
        //tutte le tipologie di importazione DEVONO avere i codici delle fiere selezionate in DENOM_FIERA
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DENOM_FIERA'", false);
        if (!$prodag_rec && !$arrSelezionate) {
            if (!$rowidFiera) {
                $this->setErrMessage("Codici Fiera non trovati");
                return false;
            }
        }
        //POSTEGGI_FIERA è solo per alcune domande, controllo solo la presenza
        $prodag_rec_posto = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='POSTEGGI_FIERA'", false);
        $arrayCodFiere = unserialize($prodag_rec['DAGVAL']);
        if (!$arrayCodFiere) {
            if ($rowidFiera) {
//                $Fiere_rApp::$utente->getKey('ditta');ec = ItaDB::DBSQLSelect($this->GAFIERE_DB, "SELECT * FROM FIERE WHERE ROWID = $rowidFiera", false);
//                $arrayCodFiere = array($Fiere_rec['FIERA'] => 1);
            } else {
                if ($arrSelezionate) {
                    $arrayCodFiere = $arrSelezionate;
                } else {
                    $this->setErrMessage("Codici Fiera non trovati: " . $prodag_rec['DAGVAL']);
                    return false;
                }
            }
        }
        //cerco tutte le fiere a cui si è scelto di partecipare
        $fiere_tab = array();
        foreach ($arrayCodFiere as $rowid => $valore) {
            if ($valore == 0 || $valore = '') {
                continue;
            }
//            $sql = "SELECT * FROM FIERE WHERE FIERA = '$codFiera' AND DATATERMINE >= '" . $proric_rec['RICDAT'] . "'";
//            $sql = "SELECT * FROM FIERE WHERE FIERA = '$codFiera' AND DATATERMINE >= '" . $proric_rec['RICDAT'] . "' AND DECENNALE = 0"; //non posso inserire domande per fiere decennali
            $sql = "SELECT * FROM FIERE WHERE ROWID = $rowid";
            if ($rowidFiera) {
                $sql = "SELECT * FROM FIERE WHERE ROWID = $rowidFiera";
            }
            $fiere_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false); //ci può essere al più una fiera creata per tipo
            $fiere_tab[] = $fiere_rec;
        }


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
        foreach ($praDati as $dato) {
            if ($dato['DAGKEY'] == "DICHIARANTE_NATURALEGA_RADIO") {
                if ($dato['DAGVAL'] == "T") {
                    $impIndividuale = true;
                } elseif ($dato['DAGVAL'] == "R") {
                    $Legale = true;
                }
            }
            if ($dato['DAGKEY'] == "DICHIARANTE_CODICEFISCALE_CFI")
                $cfDichiarante = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NOME")
                $nome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_COGNOME")
                $cognome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_SESSO_SEX")
                $sesso = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITADATA_DATA")
                $dataNascita = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITACOMUNE")
                $comuneNascita = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITACOMUNE")
                $comuneNascita = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZAVIA")
                $indirizzoResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACOMUNE")
                $comuneResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACIVICO")
                $civicoResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACAP_CAP")
                $capResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZAPROVINCIA_PV")
                $provinciaResidenza = strtoupper($dato['DAGVAL']);
            //dati della licenza
//            if ($dato['DAGKEY'] == "FIERE_TIPOLICENZA")
//                $tipoLic = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "FIERE_NUMEROLICENZA")
                $numeroLic = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "FIERE_DATARILASCIO")
                $rilascioLic = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "FIERE_LICENZA_SETTOREALIM")
                $alimentare = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "FIERE_LICENZA_SETTORENONALIM")
                $nonAlimentare = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "FIERE_COMUNERILASCIO")
                $comuneLic = strtoupper($dato['DAGVAL']);
        }

        $fiscaleIndividuale = "";
        if ($impIndividuale == true) {
            foreach ($praDati as $dato) {
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PARTITAIVA_PIVA")
                    $fiscaleIndividuale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECOMUNE")
                    $comuneSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDEVIA")
                    $indirizzoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECIVICO")
                    $civicoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECAP")
                    $capSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDEPROVINCIA_PV")
                    $provinciaSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_TELEFONO")
                    $telefonoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_NUMREGISTROIMPRESE")
                    $numRegImprese = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_DATAREGISTROIMPRESE")
                    $dataRegImprese = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_CCIAA")
                    $comuneCCIAA = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PEC")
                    $pecIndividuale = $dato['DAGVAL'];
            }
        }

        $fiscaleLegale = $PivaLegale = "";
        if ($Legale == true) {
            foreach ($praDati as $dato) {
                if ($dato['DAGKEY'] == "IMPRESA_CODICEFISCALE_CFI")
                    $fiscaleLegale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_PARTITAIVA_PIVA")
                    $PivaLegale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_RAGIONESOCIALE")
                    $ragSoc = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECOMUNE")
                    $comuneSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDEVIA")
                    $indirizzoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECIVICO")
                    $civicoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECAP")
                    $capSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDEPROVINCIA_PV")
                    $provinciaSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_TELEFONO")
                    $telefonoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_NUMREGISTROIMPRESE")
                    $numRegImprese = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_COSTITUZIONE_DATA")
                    $dataRegImprese = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
                if ($dato['DAGKEY'] == "IMPRESA_CCIAA")
                    $comuneCCIAA = strtoupper($dato['DAGVAL']);
            }
        }

        foreach ($fiere_tab as $fiereSel_rec) {
            /*
             * PER OGNI FIERA CERCO L'ENTE ASSOCIATO
             */
            $Anafiere_rec = $this->gfmLib->GetAnafiere($fiereSel_rec['FIERA']);
            if (!$Anafiere_rec) {
                $this->setErrMessage("Anagrafica fiera " . $fiereSel_rec['FIERA'] . " non trovata. Procedura interrotta");
                return false;
            }
            $codiceFiera = "";
//            $dittaFiere = App::$utente->getKey('ditta');
            if (!$Anafiere_rec['ENTEFIERE'] || $Anafiere_rec['ENTEFIERE'] == App::$utente->getKey('ditta')) {
                //se l'ente non è configurato o è lo stesso dell'utente prendo direttamente la fiera selezionata
//                $GAFIERE_DB = $this->GAFIERE_DB;
                $GAFIERE_DB = ItaDB::DBOpen('GAFIERE');
                $fiere_rec = $fiereSel_rec;
                $codiceFiera = $Anafiere_rec['TIPO'];
            } else {
//                $this->setErrMessage("Trovata ditta diversa codice " . $Anafiere_rec['ENTEFIERE']);
//                return false;
                try {
                    $GAFIERE_DB = ItaDB::DBOpen('GAFIERE', $Anafiere_rec['ENTEFIERE']);
                } catch (Exception $exc) {
                    $this->setErrMessage("Impossibile aprire il database GAFIERE" . $Anafiere_rec['ENTEFIERE'] . ". Procedura interrotta.");
                    return false;
                }
                //cerco la fiera dentro l'ente collegato, se non c'è esco
                $AnafiereCheck_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT * FROM ANAFIERE WHERE TIPO = '" . $Anafiere_rec['CODICEFIERAENTE'] . "'", false);
                if (!$AnafiereCheck_rec) {
                    $this->setErrMessage("Anagrafica fiera collegata " . $Anafiere_rec['CODICEFIERAENTE'] . " non trovata. Procedura interrotta.");
                    return false;
                }
                //cerco la fiera con la stessa data, se c'è seleziono quella, altrimenti la creo.
                $sql = "SELECT * FROM FIERE WHERE FIERA = '" . $AnafiereCheck_rec['TIPO'] . "' AND DATA = '" . $fiereSel_rec['DATA'] . "' AND ASSEGNAZIONE = '" . $fiereSel_rec['ASSEGNAZIONE'] . "' AND DECENNALE = '" . $fiereSel_rec['DECENNALE'] . "' AND TIPONS = '" . $fiereSel_rec['TIPONS'] . "'";
                $fiereCheck_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                if ($fiereCheck_rec) {
                    $fiere_rec = $fiereCheck_rec;
                } else {
                    //se non l'ho trovata la inserisco
                    $new_fiera = $fiereSel_rec;
                    unset($new_fiera['ROWID']);
                    $new_fiera['FIERA'] = $AnafiereCheck_rec['TIPO'];
                    $this->insertRecord($GAFIERE_DB, "FIERE", $new_fiera, "Inserimento nuova fiera da SUAP " . $new_fiera['FIERA'] . " - " . $new_fiera['DATA']);
                    //rileggo il record appena inserito
                    $sql = "SELECT * FROM FIERE WHERE FIERA = '" . $AnafiereCheck_rec['TIPO'] . "' AND DATA = '" . $fiereSel_rec['DATA'] . "' AND ASSEGNAZIONE = '" . $fiereSel_rec['ASSEGNAZIONE'] . "' AND DECENNALE = '" . $fiereSel_rec['DECENNALE'] . "' AND TIPONS = '" . $fiereSel_rec['TIPONS'] . "'";
                    $fiere_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$fiere_rec) {
                        $this->setErrMessage("Fiera non inserita correttamente <br>" . print_r($fiere_rec, true));
                        return false;
                    }
                }
            }


            /*
             * RICERCA ED EVENTUALE INSERIMENTO DI DITTA E LICENZA
             */


            /*
             * ricerca ditta - eventuale inserimento
             */
            $anaditta_rec = array();
            if ($fiscaleIndividuale != "") {
                $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '" . addslashes($fiscaleIndividuale) . "' OR PIVA = '" . addslashes($fiscaleIndividuale) . "')";
                $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            }
            if (!$anaditta_rec) {
                if ($cfDichiarante != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '" . addslashes($cfDichiarante) . "' OR PIVA = '" . addslashes($cfDichiarante) . "')";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                }
            }
            if (!$anaditta_rec) {
                if ($fiscaleLegale != "") {
                    $sql = "SELECT 
                        ANADITTA.* 
                    FROM 
                        ANADITTA 
                     JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                     WHERE 
                        DITTESOGG.CF = '" . addslashes($fiscaleLegale) . "'";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '" . $fiscaleLegale . "' OR PIVA = '" . $fiscaleLegale . "')";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
            }
            if (!$anaditta_rec) {
                if ($PivaLegale != "") {
                    $sql = "SELECT 
                        ANADITTA.* 
                    FROM 
                        ANADITTA 
                     JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                     WHERE 
                        DITTESOGG.PIVA = '" . addslashes($PivaLegale) . "'";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE (PIVA = '" . addslashes($PivaLegale) . "' OR CODICEFISCALE = '" . addslashes($PivaLegale) . "')";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
            }
            $Parametri = $this->devLib->getEnv_config('GAFIERE', 'codice', 'GFMBLOCCAANAGDAPRA', false);
            $bloccaIns = $Parametri['CONFIG'];
            //Se non trovato e se non bloccato lo inserisco
            if (!$anaditta_rec && !$bloccaIns) {

                $anaditta_rec = array();
                $max_codice_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT MAX(CODICE) AS MASSIMO FROM ANADITTA", false);
                $anaditta_rec['CODICE'] = $max_codice_rec['MASSIMO'] + 1;
                if ($impIndividuale && ($cognome != '' || $nome != '')) {
                    $anaditta_rec['DENOMINAZIONE'] = $cognome . " " . $nome;
                } else {
                    if ($ragSoc != '') {
                        $anaditta_rec['DENOMINAZIONE'] = $ragSoc;
                    } else {
                        $anaditta_rec['DENOMINAZIONE'] = $cognome . " " . $nome;
                    }
                }
                $anaditta_rec['SESSO'] = $sesso;
                $anaditta_rec['DATANASCITA'] = $dataNascita;
                $anaditta_rec['COMUNENASCITA'] = $comuneNascita;
                $anaditta_rec['COMUNE'] = $comuneSede;
                $anaditta_rec['INDIRIZZO'] = $indirizzoSede;
                $anaditta_rec['NUMEROCIVICO'] = $civicoSede;
                $anaditta_rec['CAP'] = $capSede;
                $anaditta_rec['PROVINCIA'] = $provinciaSede;
                if ($impIndividuale && $fiscaleIndividuale != '') {
                    $anaditta_rec['CODICEFISCALE'] = $fiscaleIndividuale;
                } else {
                    if ($fiscaleLegale != '') {
                        if (strlen($fiscaleLegale) == 16) {
                            $anaditta_rec['CODICEFISCALE'] = $fiscaleLegale;
                        } else {
                            $anaditta_rec['PIVA'] = $fiscaleLegale;
                        }
                    } else {
                        if (strlen($fiscaleIndividuale) == 16) {
                            $anaditta_rec['CODICEFISCALE'] = $fiscaleIndividuale;
                        } else {
                            $anaditta_rec['PIVA'] = $fiscaleIndividuale;
                        }
                    }
                }
                if ($PivaLegale != '') {
                    $anaditta_rec['PIVA'] = $PivaLegale;
                }
                $anaditta_rec['TELEFONO'] = $telefonoSede;
                $anaditta_rec['EMAIL'] = $pecIndividuale != '' ? $pecIndividuale : $pec;
                //$this->eqAudit->logEqEvent($this, array('Operazione' => "SCARICO FOTOGRAMMI SERV:" . $servizio_rec['CODICESERV'] . DIRECTORY_SEPARATOR . $servizio_rec['ANNOSERV']));
                $insert_Info = 'Oggetto: Inserimento Nuova Ditta Codice ' . $anaditta_rec['CODICE'];
                if (!$this->insertRecord($GAFIERE_DB, 'ANADITTA', $anaditta_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento su ANADITTE per la ditta codice " . $anaditta_rec['CODICE']);
                    return false;
                }
                //$anaditta_rec = $this->gfmLib->GetAnaditta($anaditta_rec['CODICE']);
                $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT * FROM ANADITTA WHERE CODICE = " . $anaditta_rec['CODICE'], false);

                //Se c'è il legale rappresentante inseriesco anche il soggetto
                if ($Legale == true) {
                    $dittesogg_rec = array();
                    $dittesogg_rec['CODICE'] = $anaditta_rec['CODICE'];
                    $dittesogg_rec['RUOCOD'] = "0014";
                    $dittesogg_rec['NOMINATIVO'] = $cognome . " " . $nome;
                    $dittesogg_rec['DATANASCITA'] = $dataNascita;
                    $dittesogg_rec['COMUNENASCITA'] = $comuneNascita;
                    $dittesogg_rec['INDIRIZZO'] = $indirizzoResidenza;
                    $dittesogg_rec['CIVICO'] = $civicoResidenza;
                    $dittesogg_rec['COMUNE'] = $comuneResidenza;
                    $dittesogg_rec['CAP'] = $capResidenza;
                    $dittesogg_rec['PROVINCIA'] = $provinciaResidenza;
                    $dittesogg_rec['TELEFONO'] = $telefonoSede;
                    $dittesogg_rec['CF'] = $cfDichiarante;
                    $dittesogg_rec['PIVA'] = $PivaLegale;
                    $insert_Info = 'Oggetto: Inserimento Nuovo Soggetto ditta ' . $anaditta_rec['CODICE'] . " - cf $cfDichiarante";
                    if (!$this->insertRecord($GAFIERE_DB, 'DITTESOGG', $dittesogg_rec, $insert_Info)) {
                        $this->setErrMessage("Errore in inserimento Nuovo Soggetto ditta " . $anaditta_rec['CODICE'] . " - cf $cfDichiarante");
                        return false;
                    }
                }
            }
            //se non ho ancora la ditta restituisco messaggio di errore
            if (!$anaditta_rec) {
                $this->setErrMessage("Ditta non trovata o inserimento impossibile. Procedura interrotta.");
                return false;
            }

            /*
             * ricerca licenza - eventuale inserimento
             */
            $dittelic_rec = array();
            //non considero il tipo perchè potrebbero esserci incongruenze - quasi impossibile che qualcuno abbia due licenze con stesso numero e tipo diverso
            if ($numeroLic == "") {
                $this->setErrMessage("2 - Numero licenza non specificato");
                return false;
            }
            $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND NUMERO = '" . addslashes($numeroLic) . "'";
            $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if (!$dittelic_rec) {
                $tipoLic = "B";
                //inserisco la licenza
                $dittelic_rec['CODICE'] = $anaditta_rec['CODICE'];
                $dittelic_rec['TIPOAUTORIZZAZIONE'] = $tipoLic;
                $dittelic_rec['NUMERO'] = $numeroLic;
                $dittelic_rec['DATARILASCIO'] = $dittelic_rec['DATARILASCIOLICENZA'] = $rilascioLic;
                $dittelic_rec['COMUNE'] = $comuneLic;
                if ($alimentare && !$nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "ALIMENTARE";
                }
                if (!$alimentare && $nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "NON ALIMENTARE";
                }
                if ($alimentare && $nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "MISTO";
                }
                $dittelic_rec['NUMEROISCRREGD'] = $numRegImprese;
                $dittelic_rec['DATAISCRLICENZA'] = $dataRegImprese;
                $dittelic_rec['DATAISCRIZIONEREGD'] = $dataRegImprese;
                $dittelic_rec['CCIAA'] = $comuneCCIAA;
                $insert_Info = 'Oggetto: Inserimento Nuova licenza ditta ' . $anaditta_rec['CODICE'] . " - lic $numeroLic";
                if (!$this->insertRecord($GAFIERE_DB, 'DITTELIC', $dittelic_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento Nuova licenza ditta " . $anaditta_rec['CODICE'] . " - lic $numeroLic");
                    return false;
                }
                //rileggo il record appena inserito
                $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND TIPOAUTORIZZAZIONE = '" . $dittelic_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . addslashes($dittelic_rec['NUMERO']) . "'";
                $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            }
            if ($dittelic_rec['DATAINIZIOATTIVITALIC'] == '') {
                $dittelic_rec['DATAINIZIOATTIVITALIC'] = $rilascioLic;
            }
            if ($dittelic_rec['DATAINIZIOATTIVITA'] == '') {
                $dittelic_rec['DATAINIZIOATTIVITA'] = $rilascioLic;
            }
            if (!$dittelic_rec) {
                $this->setErrMessage("Licenza non trovata o inserimento impossibile. Procedura interrotta!");
                return false;
            }
            /*
             * FINE RICERCA/INSERIMENTO DITTE E LICENZE
             */

            $fierecom_rec = array();
            $fierecom_rec['FIERA'] = $fiere_rec['FIERA'];
//            $fierecom_rec['FIERA'] = $Anafiere_rec['FIERA'];
            $fierecom_rec['DATA'] = $fiere_rec['DATA'];
            $fierecom_rec['ASSEGNAZIONE'] = $fiere_rec['ASSEGNAZIONE'];
            $fierecom_rec['TIPOATTIVITA'] = $anaditta_rec['TIPOATTIVITA'];
            $fierecom_rec['CODICE'] = $anaditta_rec['CODICE'];
            $fierecom_rec['TIPOAUTORIZZAZIONE'] = $dittelic_rec['TIPOAUTORIZZAZIONE'];
            $fierecom_rec['NUMERO'] = $dittelic_rec['NUMERO'];
            $fierecom_rec['DENOMINAZIONE'] = $anaditta_rec['DENOMINAZIONE'];
            $fierecom_rec['DATADOMANDA'] = $proric_rec['RICDAT'];
            $fierecom_rec['PROTOCOLLO'] = substr($proges_rec['GESNPR'], 4);
            $fierecom_rec['DATAPROTOCOLLO'] = $proges_rec['GESDRI'];

            $fierecom_rec['TIPONS'] = "NORMALE";
            $fierecom_rec['PEC'] = $pec;
            if ($prodag_rec_posto) {
                $fierecom_rec['POSTO'] = $prodag_rec_posto['DAGVAL'];
            } else {
                //cerco se c'è in anagrafica della fiera
                $sql = "SELECT * FROM FIEREPOS WHERE TIPO = '" . $fiere_rec['FIERA'] . "' AND CODICEDITTA = " . $anaditta_rec['CODICE'];
                $fierepos_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                if ($fierepos_rec) {
                    $fierecom_rec['POSTO'] = $fierepos_rec['POSTO'];
                    $fierecom_rec['CODICEVIA'] = $fierepos_rec['CODICEVIA'];
                }
            }
            //$fierecom_rec['IDFASCICOLO'] = $proges_rec['ROWID'];
            //verifico che la domanda non sia già presente
            $sql = "SELECT * FROM FIERECOM WHERE FIERA = '" . $fierecom_rec['FIERA'] . "' AND DATA = '" . $fierecom_rec['DATA'] . "' AND CODICE = " . $fierecom_rec['CODICE'] . ""
                    . " AND TIPOAUTORIZZAZIONE = '" . $fierecom_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $fierecom_rec['NUMERO'] . "'";
            $sql .= " AND CODICEVIA = '" . $fierecom_rec['CODICEVIA'] . "'";
            $check_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if ($check_rec) {
                $fierecom_rec = $check_rec;
                $Anafiere = $this->gfmLib->GetAnafiere($fierecom_rec['FIERA']);
                $this->setErrMessage("Domanda per la fiera " . $Anafiere['FIERA'] . " già presente! Procedura interrotta.<br>");
                return false;
            }

            $metaDati = proIntegrazioni::GetMetedatiProt($proges_rec['GESNUM']);
            if (isset($metaDati['Data']) && $proges_rec['GESNPR'] != 0) {
                $fierecom_rec['PROTOCOLLO'] = substr($proges_rec['GESNPR'], 4);
                $fierecom_rec['DATAPROTOCOLLO'] = $metaDati['Data'];
            }
            $insert_Info = "Oggetto: Inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'];
            try {
                ItaDB::DBInsert($GAFIERE_DB, "FIERECOM", "ROWID", $fierecom_rec);
            } catch (Exception $exc) {
                $this->setErrMessage("Errore in inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'] . "<br><br>" . $exc->getMessage());
                return false;
            }
//            if (!$this->insertRecord($GAFIERE_DB, 'FIERECOM', $fierecom_rec, $insert_Info)) {
//                $this->setErrMessage("Errore in inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA']);
//                return false;
//            }
            //rileggo il record di fierecom
            $sql = "SELECT * FROM FIERECOM WHERE FIERA = '" . $fierecom_rec['FIERA'] . "' AND DATA = '" . $fierecom_rec['DATA'] . "' AND CODICE = " . $fierecom_rec['CODICE'] . ""
                    . " AND TIPOAUTORIZZAZIONE = '" . $fierecom_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $fierecom_rec['NUMERO'] . "'";
            //come condizione impongo anche il codice via
            $sql .= " AND CODICEVIA = '" . $fierecom_rec['CODICEVIA'] . "'";

            $fierecom_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if (!$fierecom_rec) {
                $this->setErrMessage("Non trovo la domanda appena inserita, procedura interrotta. Controllare!<br>" . print_r($fierecom_rec, true));
                return false;
            }
            //inserimento tabella di collegamento
            $fieresuap_rec = array();
            $fieresuap_rec['IDFIERECOM'] = $fierecom_rec['ROWID'];
            $fieresuap_rec['SUAPID'] = $proges_rec['ROWID'];
            $fieresuap_rec['SUAKEY'] = App::$utente->getKey('ditta');
            $insert_Info = "Oggetto: Inserimento collegamento suap-fierecom " . $fierecom_rec['ROWID'] . " - pratica: " . $proges_rec['ROWID'];
            if (!$this->insertRecord($GAFIERE_DB, 'FIERESUAP', $fieresuap_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento collegamento suap-fierecom " . $fierecom_rec['ROWID'] . " - pratica: " . $proges_rec['ROWID']);
                return false;
            }

            /*
             * Per ogni domanda aggiungo i documenti allegati in FIEREDOC - prendo il rapporto completo
             */
            $model = 'praFascicolo.class';
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $praFascicolo = new praFascicolo($currGesnum);
//            $allegati = $praFascicolo->getAllegatiProtocollaPratica();
            //cambio la chiamata per ignorare se il documento è stato già protocollato
            $allegati = $praFascicolo->getAllegatiProtocollaPratica('Paleo', true, false, array(), true);
            if ($allegati['Principale']['Stream']) {
                //dato che ho il base64 posso creare il file dentro la directory delle domande
                $randName = md5(rand() * time()) . ".pdf.p7m";
                if (!$Anafiere_rec['ENTEFIERE'] || $Anafiere_rec['ENTEFIERE'] == App::$utente->getKey('ditta')) {
                    $doc_path = $this->gfmLib->SetDirectoryGafiere($fierecom_rec['FIERA'], "FIEREDOC");
                } else {
                    $doc_path = $this->gfmLib->SetDirectoryGafiere($fierecom_rec['FIERA'], "FIEREDOC", true, $Anafiere_rec['ENTEFIERE']);
                }
                $fieredoc_rec = array();
                $fieredoc_rec['DOCUMENTO'] = $allegati['Principale']['Nome'];
                $fieredoc_rec['NOTE'] = $allegati['Principale']['Descrizione'];
                $fieredoc_rec['DATAPRESENTAZIONE'] = $proric_rec['RICDAT'];
                $fieredoc_rec['ID_DOMANDA'] = $fierecom_rec['ROWID'];
                $fieredoc_rec['NECESSARIO'] = "N";
                $fieredoc_rec['FILE'] = $randName;
//                $pathDest = $doc_path . DIRECTORY_SEPARATOR . $randName;
                file_put_contents($doc_path . DIRECTORY_SEPARATOR . $randName, $allegati['Principale']['Stream']);
                $insert_Info = "Oggetto: Inserimento nuovo documento ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'];
                if (!$this->insertRecord($GAFIERE_DB, 'FIEREDOC', $fieredoc_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento nuovo documento ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA']);
                    return false;
                }
            }
        }



        /*
         * inserimento record fierecom per ogni fiera selezionata
         */



        //far visualizzare direttamente la domanda?? Se ne sono più di una?
        //apro dettaglio ultima domanda
//        $model = 'gfmFierePosti';
//        $_POST = array();
//        $_POST['event'] = 'openform';
//        $_POST['rowidFierecom'] = $fierecom_rec['ROWID'];
//        $_POST['data'] = $fiere_rec['DATA'];
//        $_POST['fiera'] = $fiere_rec['FIERA'];
//        $_POST['rowidFiera'] = $fiere_rec['ROWID'];
//        $_POST['assegnazione'] = $fiere_rec['ASSEGNAZIONE'];
//        itaLib::openForm($model);
//        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
//        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
//        $model();

        return true;
    }

    public function GetAnaditta($currGesnum) {
        $GAFIERE_DB = ItaDB::DBOpen('GAFIERE');
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM LIKE '$currGesnum%'";
        $praDati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        foreach ($praDati as $dato) {
            if ($dato['DAGVAL'] == '') {
                continue;
            }
            if ($dato['DAGKEY'] == "DICHIARANTE_NATURALEGA_RADIO") {
                if ($dato['DAGVAL'] == "T") { // TITOLARE OMONIMA IMPRESA
                    $NaturaLegale1 = "T";
                } elseif ($dato['DAGVAL'] == "R") {  // LEGALE RAPPRESENTANTE
                    $NaturaLegale1 = "R";
                }
            }

            if ($dato['DAGKEY'] == "DICHIARANTE_CODICEFISCALE_CFI")
                $cfDichiarante1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PARTITAIVA_PIVA")   // CASO T $impIndividuale
                $ImpIndivPI1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESA_PARTITAIVA_PIVA")   // CASO R $Legale
                $LegaleRappPI1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESA_CODICEFISCALE_CFI")  // CASO R $Legale
                $LegaleRappCF1 = strtoupper($dato['DAGVAL']);
        }
        $anaditta_rec = array();
        switch ($NaturaLegale1) {
            case 'T':
                if ($cfDichiarante1 != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($cfDichiarante1)) . "' ";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($cfDichiarante1)) . "' ";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
                if (!$anaditta_rec) {
                    if ($ImpIndivPI1 != '') {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($ImpIndivPI1)) . "' ";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        if (!$anaditta_rec) {
                            $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($ImpIndivPI1)) . "' ";
                            $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        }
                    }
                }
                break;
            case 'R':
                if ($LegaleRappCF1 != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($LegaleRappCF1)) . "' ";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($LegaleRappCF1)) . "' ";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
                if (!$anaditta_rec) {
                    if ($LegaleRappPI1 != '') {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($LegaleRappPI1)) . "' ";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        if (!$anaditta_rec) {
                            $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($LegaleRappPI1)) . "' ";
                            $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        }
                    }
                }
                break;

            default:
                $this->setErrMessage('Dichiarante 1 Natura Legale Radio Mancante - Procedura interrotta ');
                return false;
                break;
        }
        return $anaditta_rec;
    }

    public function GiustificaAssenze($currGesnum) {

        $GAFIERE_DB = ItaDB::DBOpen('GAFIERE');
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM LIKE '$currGesnum%'";
        $praDati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $Licenze_rec = array();
        foreach ($praDati as $dato) {
            if ($dato['DAGVAL'] == '') {
                continue;
            }
            if ($dato['DAGKEY'] == "DICHIARANTE_NATURALEGA_RADIO") {
                if ($dato['DAGVAL'] == "T") { // TITOLARE OMONIMA IMPRESA
                    $NaturaLegale1 = "T";
                } elseif ($dato['DAGVAL'] == "R") {  // LEGALE RAPPRESENTANTE
                    $NaturaLegale1 = "R";
                }
            }

            if ($dato['DAGKEY'] == "DICHIARANTE_NOME")
                $Nome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_COGNOME")
                $Cognome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_CODICEFISCALE_CFI")
                $cfDichiarante1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PARTITAIVA_PIVA")   // CASO T $impIndividuale
                $ImpIndivPI1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESA_PARTITAIVA_PIVA")   // CASO R $Legale
                $LegaleRappPI1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESA_CODICEFISCALE_CFI")  // CASO R $Legale
                $LegaleRappCF1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "POSTO1")
                $Posteggio1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "AUTORIZZAZIONE_01" && $dato['DAGVAL'])
                $Licenze_rec[] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "AUTORIZZAZIONE_02" && $dato['DAGVAL'])
                $Licenze_rec[] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "AUTORIZZAZIONE_03" && $dato['DAGVAL'])
                $Licenze_rec[] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "AUTORIZZAZIONE_04" && $dato['DAGVAL'])
                $Licenze_rec[] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "AUTORIZZAZIONE_05" && $dato['DAGVAL'])
                $Licenze_rec[] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "AUTORIZZAZIONE_06" && $dato['DAGVAL'])
                $Licenze_rec[] = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "GIUSTIFICAZIONE_DAL")
                $DaData = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "GIUSTIFICAZIONE_AL")
                $AData = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "MOTIVO_ASSENZA" && $dato['DAGVAL'] == 1)
                $Note = 'per Malattia';
            if ($dato['DAGKEY'] == "MOTIVO_ASSENZA" && $dato['DAGVAL'] == 2)
                $Note = 'per Gravidanza';
            if ($dato['DAGKEY'] == "MOTIVO_ASSENZA" && $dato['DAGVAL'] == 3)
                $Note = "Permessi di cui alla legge 5 febbraio 1992, n. 104 (Legge-quadro per l?assistenza, l?integrazione sociale e i diritti delle persone handicappate)";
        }


        if (!$DaData || !$AData) {
            $this->setErrMessage('Periodo data Assenze da Giustificare non definito<br>Procedura interrotta');
            return false;
        }

        /*
         * RICERCO ANAGRAFICA SOGGETTO
         */
        $anaditta_rec = array();
        switch ($NaturaLegale1) {
            case 'T':
                if ($cfDichiarante1 != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($cfDichiarante1)) . "' ";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($cfDichiarante1)) . "' ";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
                if (!$anaditta_rec) {
                    if ($ImpIndivPI1 != '') {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($ImpIndivPI1)) . "' ";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        if (!$anaditta_rec) {
                            $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($ImpIndivPI1)) . "' ";
                            $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        }
                    }
                }
                break;
            case 'R':
                if ($LegaleRappCF1 != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($LegaleRappCF1)) . "' ";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($LegaleRappCF1)) . "' ";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
                if (!$anaditta_rec) {
                    if ($LegaleRappPI1 != '') {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($LegaleRappPI1)) . "' ";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        if (!$anaditta_rec) {
                            $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($LegaleRappPI1)) . "' ";
                            $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        }
                    }
                }
                break;

            default:
                $this->setErrMessage('Dichiarante 1 Natura Legale Radio Mancante - Procedura interrotta ');
                return false;
                break;
        }
        if (!$anaditta_rec) {
            $this->setErrMessage('Nessuna Anagrafica Soggetto' . $Cognome . ' ' . $Nome . ' Trovata per  ' . $cfDichiarante1 . ' ' . $ImpIndivPI1 . ' ' . $LegaleRappPI1 . ' ' . $LegaleRappCF1 . '<br>Procedura interrotta ');
            return false;
        }


        /*
         * RICERCA DITTE LIC SE NON C'è ESCI
         */
        foreach ($Licenze_rec as $Licenza) {
            $ditteLic = $this->getLicenza($anaditta_rec['CODICE'], $Licenza);
            if (!$ditteLic) {
                $this->setErrMessage('Licenza ' . $Licenza . ' Non Trovata per Soggetto ' . $anaditta_rec['DENOMINAZIONE'] . '<br>Procedura interrotta ');
                return false;
            }
        }


        /*
         *  RICERCO SU PRESENZE COLLEGATE A NUMERO LICENZA SU FIERA
         */
        $appoggio1 = explode('/', $DaData);
        $appoggio2 = explode('/', $AData);
        $DaData = $appoggio1[2] . $appoggio1[1] . $appoggio1[0];
        $AData = $appoggio2[2] . $appoggio2[1] . $appoggio2[0];
        $pressF_tab = $pressM_tab = array();

        foreach ($Licenze_rec as $Licenza) {
            $sql = "SELECT * FROM DITTEPRE 
            WHERE CODICE = " . $anaditta_rec['CODICE'] . " 
            AND DATA >= $DaData 
            AND DATA <= $AData AND NUMERO = '" . addslashes($Licenza) . "' AND CODICEPRESENZA <> 'P'";
            $pressF_tab[$Licenza] = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, true);
        }

        foreach ($Licenze_rec as $Licenza) {
            $sql = "SELECT * FROM DITTEPRM 
            WHERE CODICE = " . $anaditta_rec['CODICE'] . " 
            AND DATA >= $DaData 
            AND DATA <= $AData AND NUMERO = '" . addslashes($Licenza) . "' AND CODICEPRESENZA <> 'P'";
            $pressM_tab[$Licenza] = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, true);
        }
        foreach ($pressF_tab as $pressF_rec) {
            foreach ($pressF_rec as $presenza) {
                $presenza['CODICEPRESENZA'] = 'G';
                $presenza['TIPOLOGIAPRESENZA'] = 'P';
                if ($presenza['NUMEROASSENZE'] > 0) {
                    $presenza['NUMEROPRESENZE'] = $presenza['NUMEROASSENZE'];
                    $presenza['NUMEROASSENZE'] = 0;
                }
                $update_Info = 'Oggetto: Giustifico Assenza DITTEPRE rowid ' . $presenza['ROWID'];
                if (!$this->updateRecord($GAFIERE_DB, 'DITTEPRE', $presenza, $update_Info)) {
                    $this->setErrMessage('Errore in aggiornamento presenze DITTEPRE<br>Procedura interrotta ');
                    return false;
                }
            }
        }

        foreach ($pressM_tab as $key => $pressM_rec) {
            foreach ($pressM_rec as $presenza) {
                $presenza['CODICEPRESENZA'] = 'G';
                $presenza['TIPOLOGIAPRESENZA'] = 'P';
                if ($presenza['NUMEROASSENZE'] > 0) {
                    $presenza['NUMEROPRESENZE'] = $presenza['NUMEROASSENZE'];
                    $presenza['NUMEROASSENZE'] = 0;
                }
                $update_Info = 'Oggetto: Giustifico Assenza DITTEPRM rowid ' . $presenza['ROWID'];
                if (!$this->updateRecord($GAFIERE_DB, 'DITTEPRM', $presenza, $update_Info)) {
                    $this->setErrMessage('Errore in aggi ornamento presenze DITTEPRE<br>Procedura interrotta ');
                    return false;
                }
            }
        }
        /*
         * INSERIMENTO DEL DOCUMENTO DI GIUSTIFICAZIONE PER OGNI LICENZA 
         */
        foreach ($Licenze_rec as $Licenza) {
            $ditteLic = $this->getLicenza($anaditta_rec['CODICE'], $Licenza);
            if (!$ditteLic) {
                $this->setErrMessage('Licenza ' . $Licenza . ' Non Trovata per Soggetto ' . $anaditta_rec['DENOMINAZIONE'] . '<br>Procedura interrotta ');
                return false;
            }

            $Dittedoc_rec = array();
            $Dittedoc_rec['CODICE'] = $anaditta_rec['CODICE'];
            $Dittedoc_rec['TIPODOCUMENTO'] = 'GIUSTIFICAZIONE';
            $Dittedoc_rec['DATA'] = date('Ymd');
            $sqlMP = "SELECT MAX(PROGRESSIVO) AS MAXPROG FROM DITTEDOC WHERE CODICE = " . $anaditta_rec['CODICE'];
            $prog_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sqlMP, false);
            $Dittedoc_rec['PROGRESSIVO'] = $prog_rec['MAXPROG'] + 1;
            $NFascicolo = $this->praLib->ElaboraProgesSerie($currGesnum);
            $Dittedoc_rec['NOTE'] = $Note . ' RICHIESTA SUAP ' . $NFascicolo;
            $Dittedoc_rec['GIUSTIFICATODAL'] = $DaData;
            $Dittedoc_rec['GIUSTIFICATOAL'] = $AData;
            $Dittedoc_rec['TIPOAUTORIZZAZIONE'] = $ditteLic['TIPOAUTORIZZAZIONE'];
            $Dittedoc_rec['NUMERO'] = $ditteLic['NUMERO'];
            if (!$this->insertRecord($GAFIERE_DB, "DITTEDOC", $Dittedoc_rec, "Inserita giustificazione ditta " . $anaditta_rec['CODICE'] . " dal $DaData al $AData")) {
                $this->setErrMessage('Errore nella creazione DITTEDOC');
                return false;
            }
        }

        /*
         * aggiungo record su FIERESUPA  rowid fascicolo per non far più vedere il buttn
         */
        $Proges_rec = $this->praLib->GetProges($currGesnum);
        $FiereSuap_rec = array('SUAPID' => $Proges_rec['ROWID']);
        ItaDB::DBInsert($GAFIERE_DB, 'FIERESUAP', 'ROWID', $FiereSuap_rec);
        Out::msgInfo('', "Giustificazione inserita con successo ditta " . $anaditta_rec['DENOMINAZIONE']);
        return true;
    }

    public function ScambiaPosteggio($currGesnum, $rowidMercati) {

        $sql = "SELECT * FROM PRODAG WHERE DAGNUM LIKE '$currGesnum%'"; // mi ricarico i dati aggiuntivi invece di passarli in modo che per correzioni non si deve chiudere e riaprire il fascicolo
        $praDati = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if (!$rowidMercati || !$praDati) {
            $this->setErrMessage("Codici Mercato non trovato");
            return false;
        }
        /*
         * Ricerco il mercato
         */
        $Anamerc_rec = array();
        $Mercati_rec = $this->gfmLib->GetMercato($rowidMercati, 'Rowid');
        $Anamerc_rec = $this->gfmLib->GetAnamerc($Mercati_rec['MERCATO']);
        if (!$Anamerc_rec) {
            $this->setErrMessage("Anagrafica mercato " . $Anamerc_rec['MERCATO'] . " non trovata. Procedura interrotta");
            return false;
        }

        $codiceMercato = "";
        if (!$Anamerc_rec['ENTEFIERE'] || $Anamerc_rec['ENTEFIERE'] == App::$utente->getKey('ditta')) {
            //se l'ente non è configurato o è lo stesso dell'utente prendo direttamente la fiera selezionata
            $GAFIERE_DB = ItaDB::DBOpen('GAFIERE');
            $codiceMercato = $Anamerc_rec['CODICE'];
        } else {
            try {
                $GAFIERE_DB = ItaDB::DBOpen('GAFIERE', $Anamerc_rec['ENTEFIERE']);
            } catch (Exception $exc) {
                $this->setErrMessage("Impossibile aprire il database GAFIERE" . $Anamerc_rec['ENTEFIERE'] . ". Procedura interrotta.");
                return false;
            }
            //cerco la fiera dentro l'ente collegato, se non c'è esco
            $AnafiereCheck_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT * FROM ANAFIERE WHERE TIPO = '" . $Anafiere_rec['CODICEFIERAENTE'] . "'", false);
            if (!$AnafiereCheck_rec) {
                $this->setErrMessage("Anagrafica fiera collegata " . $Anafiere_rec['CODICEFIERAENTE'] . " non trovata. Procedura interrotta.");
                return false;
            }
        }

        $NaturaLegale1 = $NaturaLegale2 = '';
        foreach ($praDati as $dato) {
            if ($dato['DAGVAL'] == '') {
                continue;
            }
            if ($dato['DAGKEY'] == "DICHIARANTE_NATURALEGA_RADIO") { // SOGGETTO 1
                if ($dato['DAGVAL'] == "T") { // TITOLARE OMONIMA IMPRESA
                    $NaturaLegale1 = "T";
                } elseif ($dato['DAGVAL'] == "R") {  // LEGALE RAPPRESENTANTE
                    $NaturaLegale1 = "R";
                }
            }
            if ($dato['DAGKEY'] == "DICHIARANTE_NATURALEGA_RADIO2") { // SOGGETTO2
                if ($dato['DAGVAL'] == "T") { // TITOLARE OMONIMA IMPRESA
                    $NaturaLegale2 = "T";
                } elseif ($dato['DAGVAL'] == "R") {  // LEGALE RAPPRESENTANTE
                    $NaturaLegale2 = "R";
                }
            }
            // SOGGETTO 1
            if ($dato['DAGKEY'] == "DICHIARANTE_NOME")
                $Nome1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_COGNOME")
                $Cognome1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_CODICEFISCALE_CFI")
                $cfDichiarante1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PARTITAIVA_PIVA")   // CASO T $impIndividuale
                $ImpIndivPI1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESA_PARTITAIVA_PIVA")   // CASO R $Legale
                $LegaleRappPI1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESA_CODICEFISCALE_CFI")  // CASO R $Legale
                $LegaleRappCF1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LICENZA_NUMERO")
                $numeroLic1 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "POSTO1")
                $Posteggio1 = strtoupper($dato['DAGVAL']);

            // SOGGETTO 2
            if ($dato['DAGKEY'] == "DICHIARANTE_NOME2")
                $Nome2 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_COGNOME2")
                $Cognome2 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_CODICEFISCALE_CFI2")
                $cfDichiarante2 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PARTITAIVA_PIVA2")   // CASO T $impIndividuale
                $ImpIndivPI2 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESA_PARTITAIVA_PIVA2")   // CASO R $Legale
                $LegaleRappPI2 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "IMPRESA_CODICEFISCALE_CFI2")  // CASO R $Legale
                $LegaleRappCF2 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LICENZA_NUMERO2")
                $numeroLic2 = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "POSTO2")
                $Posteggio2 = strtoupper($dato['DAGVAL']);
        }

        /*
         * RICAVO IL MERCATO
         */


        /*
         * RICERCO ANAGRAFICHE DITTE COINVOLTE
         */

        //    per SOGGETTO 1
        $anaditta_rec1 = array();
        switch ($NaturaLegale1) {
            case 'T':
                if ($cfDichiarante1 != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($cfDichiarante1)) . "' ";
                    $anaditta_rec1 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec1) {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($cfDichiarante1)) . "' ";
                        $anaditta_rec1 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
                if (!$anaditta_rec1) {
                    if ($ImpIndivPI1 != '') {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($ImpIndivPI1)) . "' ";
                        $anaditta_rec1 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        if (!$anaditta_rec1) {
                            $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($ImpIndivPI1)) . "' ";
                            $anaditta_rec1 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        }
                    }
                }
                break;
            case 'R':
                if ($LegaleRappCF1 != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($LegaleRappCF1)) . "' ";
                    $anaditta_rec1 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec1) {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($LegaleRappCF1)) . "' ";
                        $anaditta_rec1 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
                if (!$anaditta_rec1) {
                    if ($LegaleRappPI1 != '') {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($LegaleRappPI1)) . "' ";
                        $anaditta_rec1 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        if (!$anaditta_rec1) {
                            $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($LegaleRappPI1)) . "' ";
                            $anaditta_rec1 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        }
                    }
                }
                break;

            default:
                $this->setErrMessage('Dichiarante 1 Natura Legale Radio Mancante - Procedura interrotta ');
                return false;
                break;
        }
        if (!$anaditta_rec1) {
            $this->setErrMessage('Nessuna Anagrafica Relativa a ' . $Cognome1 . ' ' . $Nome1 . ' Trovata per  ' . $cfDichiarante1 . ' ' . $ImpIndivPI1 . ' ' . $LegaleRappPI1 . ' ' . $LegaleRappCF1 . '<br>Procedura interrotta ');
            return false;
        }

        //    per SOGGETTO 2
        $anaditta_rec2 = array();
        switch ($NaturaLegale2) {
            case 'T':
                if ($cfDichiarante2 != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($cfDichiarante2)) . "' ";
                    $anaditta_rec2 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec2) {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($cfDichiarante2)) . "' ";
                        $anaditta_rec2 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
                if (!$anaditta_rec2) {
                    if ($ImpIndivPI2 != '') {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($ImpIndivPI2)) . "' ";
                        $anaditta_rec2 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        if (!$anaditta_rec2) {
                            $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($ImpIndivPI2)) . "' ";
                            $anaditta_rec2 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        }
                    }
                }
                break;
            case 'R':
                if ($LegaleRappCF2 != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($LegaleRappCF2)) . "' ";
                    $anaditta_rec2 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec2) {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($LegaleRappCF2)) . "' ";
                        $anaditta_rec2 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
                if (!$anaditta_rec2) {
                    if ($LegaleRappPI2 != '') {
                        $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('CODICEFISCALE') . " = '" . addslashes(strtoupper($LegaleRappPI2)) . "' ";
                        $anaditta_rec2 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        if (!$anaditta_rec2) {
                            $sql = "SELECT * FROM ANADITTA WHERE " . $GAFIERE_DB->strUpper('PIVA') . " = '" . addslashes(strtoupper($LegaleRappPI2)) . "' ";
                            $anaditta_rec2 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                        }
                    }
                }
                break;

            default:
                $this->setErrMessage('Dichiarante 2 Natura Legale Radio Mancante - Procedura interrotta ');
                return false;
                break;
        }
        if (!$anaditta_rec2) {
            $this->setErrMessage('Nessuna Anagrafica Relativa a ' . $Cognome2 . ' ' . $Nome2 . ' Trovata per  ' . $cfDichiarante2 . ' ' . $ImpIndivPI2 . ' ' . $LegaleRappPI2 . ' ' . $LegaleRappCF2 . '<br>Procedura interrotta ');
            return false;
        }


        /*
         * RICERCO LICENZE COLLEGATE
         */
        $sql = "SELECT * FROM DITTELIC  WHERE NONATTIVA = 0 AND CODICE = '" . $anaditta_rec1['CODICE'] . "' AND NUMERO = '" . addslashes($numeroLic1) . "'";
        $ditteLic_rec1 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
        if (!$ditteLic_rec1) {
            $this->setErrMessage('Nessuna Licenza trovata per ' . $anaditta_rec1['DENOMINAZIONE'] . ' : ' . $numeroLic1 . '<br>Procedura interrotta <br>' . $sql);
            return false;
        }
        $sql = "SELECT * FROM DITTELIC  WHERE NONATTIVA = 0 AND CODICE = '" . $anaditta_rec2['CODICE'] . "' AND NUMERO = '" . addslashes($numeroLic2) . "'";

        $ditteLic_rec2 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
        if (!$ditteLic_rec2) {
            $this->setErrMessage('Nessuna Licenza trovata per ' . $anaditta_rec2['DENOMINAZIONE'] . ' : ' . $numeroLic2 . '<br>Procedura interrotta ');
            return false;
        }




        //2) effetto il cambio della ditta e licenza in anagrafica mercato   -->MERCAPOS
        $sql = "SELECT * FROM MERCAPOS  WHERE TIPO = '" . $Anamerc_rec['CODICE'] . "' AND CODICEDITTA = '" . $anaditta_rec1['CODICE'] . "' AND POSTO = '" . addslashes($Posteggio1) . "' AND NUMERO = '" . addslashes(strtoupper($numeroLic1)) . "'";
        $MercaPos_rec1 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
        if (!$MercaPos_rec1) {
            $this->setErrMessage("Posteggio " . $anaditta_rec1['DENOMINAZIONE'] . " in Anagrafica Mercato non trovato<br>Procedura interrotta.<br> Codice Ditta " . $anaditta_rec1['CODICE'] . " Posto" . $Posteggio1 . " Licenza NUMERO " . $numeroLic1 . "<br>Nel " . $Anamerc_rec['MERCATO']);
            return false;
        }
        $sql = "SELECT * FROM MERCAPOS  WHERE TIPO = '" . $Anamerc_rec['CODICE'] . "' AND CODICEDITTA = '" . $anaditta_rec2['CODICE'] . "' AND  POSTO = '" . addslashes($Posteggio2) . "' AND NUMERO = '" . addslashes(strtoupper($numeroLic2)) . "'";
        $MercaPos_rec2 = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
        if (!$MercaPos_rec2) {
            $this->setErrMessage("Posteggio " . $anaditta_rec2['DENOMINAZIONE'] . " in Anagrafica Mercato non trovato<br>Procedura interrotta.<br> Codice Ditta " . $anaditta_rec2['CODICE'] . " Posto" . $Posteggio2 . " Licenza NUMERO " . $numeroLic2 . "<br>Nel " . $Anamerc_rec['MERCATO']);
            return false;
        }
        // 1)  effettuo il cambio dei posteggi sulla licenza   da verificare
        $update_rec1 = array(
            'ROWID' => $MercaPos_rec1['ROWID'],
            'CODICEDITTA' => $anaditta_rec2['CODICE'],
            'TIPOAUTORIZZAZIONE' => $ditteLic_rec2['TIPOAUTORIZZAZIONE'],
            'NUMERO' => $ditteLic_rec2['NUMERO'],
        );
        $update_rec2 = array(
            'ROWID' => $MercaPos_rec2['ROWID'],
            'CODICEDITTA' => $anaditta_rec1['CODICE'],
            'TIPOAUTORIZZAZIONE' => $ditteLic_rec1['TIPOAUTORIZZAZIONE'],
            'NUMERO' => $ditteLic_rec1['NUMERO'],
        );
        $update_Info = 'Effettuo Scambio posteggio per Soggetto';
        if (!$this->updateRecord($GAFIERE_DB, 'MERCAPOS', $update_rec1, $update_Info)) {
            $this->setErrMessage("ERRORE problema nell'aggiornamento - " . $anaditta_rec1['DENOMINAZIONE'] . " in Anagrafica Mercato <br>Procedura interrotta.");
            return false;
        }
        if (!$this->updateRecord($GAFIERE_DB, 'MERCAPOS', $update_rec2, $update_Info)) {
            $this->setErrMessage("ERRORE problema nell'aggiornamento - " . $anaditta_rec2['DENOMINAZIONE'] . " in Anagrafica Mercato <br>Procedura interrotta.");
            return false;
        }
        $NFascicolo = $this->praLib->ElaboraProgesSerie($currGesnum);

        if (!$ditteLic_rec2['POSTO']) {
            $ditteLic_rec2['POSTO'] = $ditteLic_rec2;
        }
        if (!$ditteLic_rec1['POSTO']) {
            $ditteLic_rec1['POSTO'] = $ditteLic_rec1;
        }
        $InfoLic1 = array(
            'ROWID' => $ditteLic_rec1['ROWID'],
            'POSTO' => $ditteLic_rec2['POSTO'],
            'LETTERA' => $ditteLic_rec2['LETTERA'],
            'CODICEVIA' => $ditteLic_rec2['CODICEVIA'],
            'NOTE' => $ditteLic_rec1['NOTE'] . ''
            . '----- Effettuato Cambio Posto Per Richiesta SUAP ' . $NFascicolo . ' Con Soggetto ' . $anaditta_rec2['DENOMINAZIONE'] . ' in data ' . date("d/m/Y")
        );
        if (!$this->updateRecord($GAFIERE_DB, 'DITTELIC', $InfoLic1, '')) {
            $this->setErrMessage("ERRORE problema nell'aggiornamento - Soggetto 2 in Anagrafica Mercato <br>Procedura interrotta.");
            return false;
        }
        $InfoLic2 = array(
            'ROWID' => $ditteLic_rec2['ROWID'],
            'POSTO' => $ditteLic_rec1['POSTO'],
            'LETTERA' => $ditteLic_rec1['LETTERA'],
            'CODICEVIA' => $ditteLic_rec1['CODICEVIA'],
            'NOTE' => $ditteLic_rec2['NOTE'] . ''
            . '----- Effettuato Cambio Posto Per Richiesta SUAP ' . $NFascicolo . ' Con Soggetto ' . $anaditta_rec1['DENOMINAZIONE'] . ' in data ' . date("d/m/Y")
        );
        if (!$this->updateRecord($GAFIERE_DB, 'DITTELIC', $InfoLic2, '')) {
            $this->setErrMessage("ERRORE problema nell'aggiornamento - Soggetto " . $anaditta_rec2['DENOMINAZIONE'] . " in Anagrafica Mercato <br>Procedura interrotta.");
            return false;
        }
        /*
         * aggiungo record su FIERESUPA  rowid fascicolo per non far più vedere il buttn  per effettuare lo scambio
         */
        $Proges_rec = $this->praLib->GetProges($currGesnum);
        $FiereSuap_rec = array('SUAPID' => $Proges_rec['ROWID'], 'IDFIERECOM' => $ditteLic_rec1['ROWID'], 'IDMERCACOM' => $ditteLic_rec2['ROWID']);
        ItaDB::DBInsert($GAFIERE_DB, 'FIERESUAP', 'ROWID', $FiereSuap_rec);
        Out::msgInfo('ESEGUITO', 'Procedura Scambio Posteggio Eseguito Correttamente<br>Soggetto 1  ' . $anaditta_rec1['DENOMINAZIONE'] . ' con licenza ' . $ditteLic_rec1['NUMERO'] . $ditteLic_rec1['TIPOAUTORIZZAZIONE'] . ' aggiornato nel posteggio mercato Numero' . $Posteggio2 .
                '<br>' . 'Soggetto 2 ' . $anaditta_rec2['DENOMINAZIONE'] . ' con licenza ' . $ditteLic_rec2['NUMERO'] . $ditteLic_rec2['TIPOAUTORIZZAZIONE'] . ' aggiornato nel posteggio mercato Numero' . $Posteggio1);
        return true;
    }

    public function CollegaFiereSassuolo($praDati, $praDatiPratica, $currGesnum, $rowidFiera, $arrSelezionate = array()) {

        //tutte le tipologie di importazione DEVONO avere i codici delle fiere selezionate in DENOM_FIERA
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DENOM_FIERA'", false);
        if (!$prodag_rec && !$arrSelezionate) {
            if (!$rowidFiera) {
                $this->setErrMessage("Codici Fiera non trovati");
                return false;
            }
        }
        $arrayCodFiere = unserialize($prodag_rec['DAGVAL']);
        if (!$arrayCodFiere) {
            if ($rowidFiera) {
//                $Fiere_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, "SELECT * FROM FIERE WHERE ROWID = $rowidFiera", false);
//                $arrayCodFiere = array($Fiere_rec['FIERA'] => 1);
            } else {
                if ($arrSelezionate) {
                    $arrayCodFiere = $arrSelezionate;
                } else {
                    $this->setErrMessage("Codici Fiera non trovati: " . $prodag_rec['DAGVAL']);
                    return false;
                }
            }
        }
        foreach ($praDatiPratica as $dato) {
            if ($dato['DAGKEY'] == "ESIBENTE_PEC") {
                $pec = $dato['DAGVAL'];
            }
            if ($dato['DAGKEY'] == "ESIBENTE_CODICEFISCALE_CFI") {
                $cf_esibente = $dato['DAGVAL'];
            }
        }
        //pulizia cf_esibente
        if ($cf_esibente) {
            $san_cf = "";
            for ($index = 0; $index < strlen($cf_esibente); $index++) { //da 0 a 15 max
                $char = substr($cf_esibente, $index, 1);
                if (is_numeric($char)) {
                    $san_cf .= $char; //in questo modo elimino i vari P.Iva:1234567891, p.I.:1234567891 ecc
                }
            }
            $cf_esibente = $san_cf;
        }
        //personalizzazione per Sassuolo: dal modello viene sempre considerata "la ditta" che presenta la domanda "nella persona di"
        $impIndividuale = false;
        $Legale = true;
        $posti_confermati = array();
        $vie_proposte = array();
        foreach ($praDati as $dato) {
            if ($dato['DAGVAL'] == '') {
                continue;
            }
            if ($dato['DAGKEY'] == "DICHIARANTE_COGNOME_NOME")
                $cognome_nome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITADATA_DATA")
                $dataNascita = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITACOMUNE")
                $comuneNascita = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITAPROVINCIA_PV")
                $provinciaNascita = strtoupper($dato['DAGVAL']); //non usato
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZAVIA")
                $indirizzoResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACIVICO")
                $civicoResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACAP_CAP")
                $capResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACOMUNE")
                $comuneResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZAPROVINCIA_PV")
                $provinciaResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_CODICEFISCALE_CFI")
                $cfDichiarante = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_TELEFONO")
                $telefonoDichiarante = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_CELLULARE")
                $cellulareDichiarante = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_FAX")
                $faxDichiarante = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_EMAIL")
                $mailDichiarante = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "ESPOSIZIONE_MQ")
                $mq = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "ESPOSIZIONE_LUNGHEZZA_METRI")
                $lunghezza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "ESPOSIZIONE_LARGHEZZA_METRI")
                $larghezza = strtoupper($dato['DAGVAL']);
            //se riesco prendo anche il flag tutto il giorno/solo pomeriggio
            if ($dato['DAGKEY'] == "MOD_ISCRIZIONE_OPERATORI_ECON_F_030")
                if ($dato['DAGVAL'] == 1) {
                    $GiornataIntera = true;
                } else {
                    $GiornataIntera = false;
                }
            //PRENDO I DATI DEI POSTEGGI CONFERMATI
            if ($dato['DAGKEY'] == "DOMANDA_POSTEGGIO" && $dato['DAGVAL'] == "Si") {
                $sqlFieraConf = "SELECT * FROM PRODAG WHERE DAGKEY = 'FIERA_ROWID' AND DAGSET = '" . $dato['DAGSET'] . "'";
                $prodag_conf = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlFieraConf, false);
                if ($prodag_conf['DAGVAL'] != '') {
                    $posti_confermati[] = $prodag_conf['DAGVAL'];
                }
            }
            //PRENDO I DATI DELLE VIE PROPOSTE
            if ($dato['DAGKEY'] == "VIA1") {
                $sqlFieraVia = "SELECT * FROM PRODAG WHERE DAGKEY = 'ROWIDFIERA' AND DAGSET = '" . $dato['DAGSET'] . "'";
                $prodag_via = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlFieraVia, false);
                if ($prodag_via['DAGVAL'] != '') {
                    $vie_proposte[$prodag_via['DAGVAL']]['VIA1'] = $dato['DAGVAL'];
                }
            }
            if ($dato['DAGKEY'] == "VIA2") {
                $sqlFieraVia = "SELECT * FROM PRODAG WHERE DAGKEY = 'ROWIDFIERA' AND DAGSET = '" . $dato['DAGSET'] . "'";
                $prodag_via = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlFieraVia, false);
                if ($prodag_via['DAGVAL'] != '') {
                    $vie_proposte[$prodag_via['DAGVAL']]['VIA2'] = $dato['DAGVAL'];
                }
            }
            if ($dato['DAGKEY'] == "VIA3") {
                $sqlFieraVia = "SELECT * FROM PRODAG WHERE DAGKEY = 'ROWIDFIERA' AND DAGSET = '" . $dato['DAGSET'] . "'";
                $prodag_via = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlFieraVia, false);
                if ($prodag_via['DAGVAL'] != '') {
                    $vie_proposte[$prodag_via['DAGVAL']]['VIA3'] = $dato['DAGVAL'];
                }
            }
            $materiale = "";
            if ($dato['DAGKEY'] == "Q_TAVOLO_80X80") {
                if ($dato['DAGVAL'] != '') {
                    $materiale .= "TAVOLO PLASTICA 80X80: " . $dato['DAGVAL'] . "\r\n";
                }
            }
            if ($dato['DAGKEY'] == "Q_TAVOLO_70X150") {
                if ($dato['DAGVAL'] != '') {
                    $materiale .= "TAVOLO BLU 70X150: " . $dato['DAGVAL'] . "\r\n";
                }
            }
            if ($dato['DAGKEY'] == "Q_TAVOLO_80X220") {
                if ($dato['DAGVAL'] != '') {
                    $materiale .= "TAVOLO LEGNO GIALLO 80X220: " . $dato['DAGVAL'] . "\r\n";
                }
            }
            if ($dato['DAGKEY'] == "Q_TAVOLO_LEGNO_PANCHE") {
                if ($dato['DAGVAL'] != '') {
                    $materiale .= "TAVOLO LEGNO GIALLO CON PANCHE: " . $dato['DAGVAL'] . "\r\n";
                }
            }
            if ($dato['DAGKEY'] == "Q_PANCA") {
                if ($dato['DAGVAL'] != '') {
                    $materiale .= "PANCA LEGNO: " . $dato['DAGVAL'] . "\r\n";
                }
            }
            if ($dato['DAGKEY'] == "Q_SEDIA") {
                if ($dato['DAGVAL'] != '') {
                    $materiale .= "SEDIA PLASTICA: " . $dato['DAGVAL'] . "\r\n";
                }
            }
            if ($dato['DAGKEY'] == "Q_GAZEBO_3X3") {
                if ($dato['DAGVAL'] != '') {
                    $materiale .= "GAZEBO 3X3 DA MONTARE: " . $dato['DAGVAL'] . "\r\n";
                }
            }
            if ($dato['DAGKEY'] == "Q_TRANSENNA") {
                if ($dato['DAGVAL'] != '') {
                    $materiale .= "TRANSENNA STRADALE: " . $dato['DAGVAL'] . "\r\n";
                }
            }
            if ($materiale != '') {
                $materiale .= "\r\nGIORNI NOLEGGIO RICHIESTI:\r\n";
                if ($dato['DAGKEY'] == "NOLEGGO_OTT1") {
                    if ($dato['DAGVAL'] != '') {
                        $materiale .= "PRIMA DOMENICA\n";
                    }
                }
                if ($dato['DAGKEY'] == "NOLEGGO_OTT2") {
                    if ($dato['DAGVAL'] != '') {
                        $materiale .= "SECONDA DOMENICA\n";
                    }
                }
                if ($dato['DAGKEY'] == "NOLEGGO_OTT3") {
                    if ($dato['DAGVAL'] != '') {
                        $materiale .= "TERZA DOMENICA\n";
                    }
                }
                if ($dato['DAGKEY'] == "NOLEGGO_OTT4") {
                    if ($dato['DAGVAL'] != '') {
                        $materiale .= "QUARTA DOMENICA\n";
                    }
                }
                if ($dato['DAGKEY'] == "NOLEGGO_OTT5") {
                    if ($dato['DAGVAL'] != '') {
                        $materiale .= "QUINTA DOMENICA\n";
                    }
                }
            }
            //valori automercato
            if ($dato['DAGKEY'] == "N_SPAZI_NUOVIVEIC") {
                if ($dato['DAGVAL'] != '') {
                    $materiale .= "SPAZI NUOVI VEICOLI: " . $dato['DAGVAL'] . "";
                    if ($dato['DAGKEY'] == "GG_NUOVIVEIC") {
                        if ($dato['DAGVAL'] != '') {
                            $materiale .= " X GG: " . $dato['DAGVAL'] . "";
                        }
                    }
                    $materiale . "\r\n";
                }
            }
            if ($dato['DAGKEY'] == "N_SPAZI_USATO") {
                if ($dato['DAGVAL'] != '') {
                    $materiale .= "SPAZI VEICOLI USATI: " . $dato['DAGVAL'] . "";
                    if ($dato['DAGKEY'] == "GG_USATO") {
                        if ($dato['DAGVAL'] != '') {
                            $materiale .= " X GG: " . $dato['DAGVAL'] . "";
                        }
                    }
                    $materiale . "\r\n";
                }
            }
            //dati della licenza - per Sassuolo sono forzati a X/codice_ditta, non è un dato significativo per loro
//            $tipoLic = "X";
        }
        if ($dato['DAGKEY'] == "TIPOLOGIA_DICHIARAZIONE") {
            if ($dato['DAGVAL'] != '') {
                $tipoAttivita = $dato['DAGVAL'];
            }
        }

//        $fiscaleIndividuale = "";
        if ($impIndividuale == true) {
            //non c'è per Sassuolo
        }

        $fiscaleLegale = $PivaLegale = "";
        if ($Legale == true) {
            foreach ($praDati as $dato) {
                if ($dato['DAGKEY'] == "IMPRESA_CODICEFISCALE_CFI")
                    $fiscaleLegale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_PARTITAIVA_PIVA")
                    $PivaLegale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_RAGIONESOCIALE")
                    $ragSoc = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECOMUNE")
                    $comuneSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDEVIA")
                    $indirizzoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECIVICO")
                    $civicoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECAP")
                    $capSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDEPROVINCIA_PV")
                    $provinciaSede = strtoupper($dato['DAGVAL']);
            }
        }

        /*
         * ricerca ditta - eventuale inserimento
         * fare ricerca per $cf_esibente, $fiscaleLegale, $PivaLegale, $cfDichiarante
         */
        $anaditta_rec = array();
        if ($cf_esibente != "") {
            $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '" . addslashes($cf_esibente) . "' OR PIVA = '" . addslashes($cf_esibente) . "')";
            $anaditta_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
        }
        if (!$anaditta_rec) {
            if ($cfDichiarante != '') {
                $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '$cfDichiarante' OR PIVA = '$cfDichiarante')";
                $anaditta_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
            }
        }
        if (!$anaditta_rec) {
            if ($fiscaleLegale != "") {
                $sql = "SELECT 
                        ANADITTA.* 
                    FROM 
                        ANADITTA 
                     JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                     WHERE 
                        DITTESOGG.CF = '$fiscaleLegale'";
                $anaditta_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
                if (!$anaditta_rec) {
                    $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '$fiscaleLegale' OR PIVA = '$fiscaleLegale')";
                    $anaditta_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
                }
            }
        }
        if (!$anaditta_rec) {
            if ($PivaLegale != "") {
                $sql = "SELECT 
                        ANADITTA.* 
                    FROM 
                        ANADITTA 
                     JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                     WHERE 
                        DITTESOGG.PIVA = '$PivaLegale'";
                $anaditta_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
                if (!$anaditta_rec) {
                    $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '$PivaLegale' OR PIVA = '$PivaLegale')";
                    $anaditta_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
                }
            }
        }
        $Parametri = $this->devLib->getEnv_config('GAFIERE', 'codice', 'GFMBLOCCAANAGDAPRA', false);
        $bloccaIns = $Parametri['CONFIG'];
        //Se non trovato e se non bloccato lo inserisco
        if (!$anaditta_rec && !$bloccaIns) {
            $max_codice_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, "SELECT MAX(CODICE) AS MASSIMO FROM ANADITTA", false);
            $anaditta_rec['CODICE'] = $max_codice_rec['MASSIMO'] + 1;
            $anaditta_rec['DENOMINAZIONE'] = $ragSoc;
//            $anaditta_rec['SESSO'] = $sesso;
            $anaditta_rec['DATANASCITA'] = $dataNascita;
            $anaditta_rec['COMUNENASCITA'] = $comuneNascita;
            $anaditta_rec['COMUNE'] = $comuneSede;
            $anaditta_rec['INDIRIZZO'] = $indirizzoSede;
            $anaditta_rec['NUMEROCIVICO'] = $civicoSede;
            $anaditta_rec['CAP'] = $capSede;
            $anaditta_rec['PROVINCIA'] = $provinciaSede;
            $anaditta_rec['CODICEFISCALE'] = $fiscaleLegale ? $fiscaleLegale : $cf_esibente;
            $anaditta_rec['PIVA'] = $PivaLegale ? $PivaLegale : "";
            $anaditta_rec['TELEFONO'] = $telefonoDichiarante;
            $anaditta_rec['CELLULARE1'] = $cellulareDichiarante;
            $anaditta_rec['FAX'] = $faxDichiarante;
            //$this->eqAudit->logEqEvent($this, array('Operazione' => "SCARICO FOTOGRAMMI SERV:" . $servizio_rec['CODICESERV'] . DIRECTORY_SEPARATOR . $servizio_rec['ANNOSERV']));
//            Out::msgStop("praDati", print_r($praDati, true));
//            return false;

            $insert_Info = 'Oggetto: Inserimento Nuova Ditta Codice ' . $anaditta_rec['CODICE'];
            if (!$this->insertRecord($this->GAFIERE_DB, 'ANADITTA', $anaditta_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento su ANADITTE per la ditta codice " . $anaditta_rec['CODICE']);
                return false;
            }
            $anaditta_rec = $this->gfmLib->GetAnaditta($anaditta_rec['CODICE']);

            //Se c'è il legale rappresentante inseriesco anche il soggetto
            if ($Legale == true) {
                $dittesogg_rec = array();
                $dittesogg_rec['CODICE'] = $anaditta_rec['CODICE'];
                $dittesogg_rec['RUOCOD'] = "0014";
                $dittesogg_rec['NOMINATIVO'] = $cognome_nome;
                $dittesogg_rec['DATANASCITA'] = $dataNascita;
                $dittesogg_rec['COMUNENASCITA'] = $comuneNascita;
                $dittesogg_rec['INDIRIZZO'] = $indirizzoResidenza;
                $dittesogg_rec['CIVICO'] = $civicoResidenza;
                $dittesogg_rec['COMUNE'] = $comuneResidenza;
                $dittesogg_rec['CAP'] = $capResidenza;
                $dittesogg_rec['PROVINCIA'] = $provinciaResidenza;
                $dittesogg_rec['TELEFONO'] = $telefonoDichiarante;
                $dittesogg_rec['CF'] = $cfDichiarante;
                $dittesogg_rec['PIVA'] = $PivaLegale;
                $insert_Info = 'Oggetto: Inserimento Nuovo Soggetto ditta ' . $anaditta_rec['CODICE'] . " - cf $cfDichiarante";
                if (!$this->insertRecord($this->GAFIERE_DB, 'DITTESOGG', $dittesogg_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento Nuovo Soggetto ditta " . $anaditta_rec['CODICE'] . " - cf $cfDichiarante");
                    return false;
                }
                //aggiorno il rappresentante legale sulla ditta
                $anaditta_rec['RAPLEGALE'] = $dittesogg_rec['NOMINATIVO'];
                $anaditta_rec['RDATANASCITA'] = $dittesogg_rec['DATANASCITA'];
                $anaditta_rec['RCOMUNENASCITA'] = $dittesogg_rec['COMUNENASCITA'];
                $anaditta_rec['RAPINDIRIZZO'] = $dittesogg_rec['INDIRIZZO'];
                $anaditta_rec['RAPNUM'] = $dittesogg_rec['CIVICO'];
                $anaditta_rec['RAPCOMUNE'] = $dittesogg_rec['COMUNE'];
                $anaditta_rec['RAPCAP'] = $dittesogg_rec['CAP'];
                $anaditta_rec['RAPPROV'] = $dittesogg_rec['PROVINCIA'];
                $anaditta_rec['RAPTELEFONO'] = $dittesogg_rec['TELEFONO'];
                $anaditta_rec['CODFISRAP'] = $dittesogg_rec['CF'];
                $anaditta_rec['PIVARAP'] = $dittesogg_rec['PIVA'];
                try {
                    ItaDB::DBUpdate($this->GAFIERE_DB, "ANADITTA", "ROWID", $anaditta_rec);
                } catch (Exception $exc) {
                    $this->processEnd();
                    Out::msgStop("Errore", "Errore in aggiornamento rappresentante legale ditta <br>" . print_r($anaditta_rec, true) . "<br><br>" . $exc->getMessage());
                    return false;
                }
            }
        }
        //se non ho ancora la ditta restituisco messaggio di errore
        if (!$anaditta_rec) {
            $this->setErrMessage("Ditta non trovata o inserimento impossibile. Procedura interrotta.");
            return false;
        }

        //inserisco la riga in dittemail
        $sqlMail = "SELECT * FROM DITTEMAIL WHERE CODICE = '" . $anaditta_rec['CODICE'] . "' AND INDIRIZZO = '" . $pec . "'";
        $dittemail_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sqlMail, false);
        if (!$dittemail_rec) {
            $dittemail_rec = array();
            $dittemail_rec['CODICE'] = $anaditta_rec['CODICE'];
            $dittemail_rec['INDIRIZZO'] = $pec;
            if (!$this->insertRecord($this->GAFIERE_DB, 'DITTEMAIL', $dittemail_rec, "Inserimento mail $pec da portale")) {
                $this->setErrMessage("Errore in inserimento nuova mail ditta " . $anaditta_rec['CODICE'] . " - mail $pec");
                return false;
            }
        }
        if ($mailDichiarante) {
            $sqlMail = "SELECT * FROM DITTEMAIL WHERE CODICE = '" . $anaditta_rec['CODICE'] . "' AND INDIRIZZO = '" . $mailDichiarante . "'";
            $dittemail_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sqlMail, false);
            if (!$dittemail_rec) {
                $dittemail_rec = array();
                $dittemail_rec['CODICE'] = $anaditta_rec['CODICE'];
                $dittemail_rec['INDIRIZZO'] = $mailDichiarante;
                if (!$this->insertRecord($this->GAFIERE_DB, 'DITTEMAIL', $dittemail_rec, "Inserimento mail $mailDichiarante da portale")) {
                    $this->setErrMessage("Errore in inserimento nuova mail ditta " . $anaditta_rec['CODICE'] . " - mail $mailDichiarante");
                    return false;
                }
            }
        }

        /*
         * ricerca licenza - eventuale inserimento
         */
        $dittelic_rec = array();
        //per Sassuolo licenze fittizie X/CodiceDitta
        $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND NUMERO = '" . $anaditta_rec['CODICE'] . "' AND TIPOAUTORIZZAZIONE = 'X'";
        $dittelic_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
        if (!$dittelic_rec) {
            //inserisco la licenza
            $dittelic_rec['CODICE'] = $anaditta_rec['CODICE'];
            $dittelic_rec['TIPOAUTORIZZAZIONE'] = "X";
            $dittelic_rec['NUMERO'] = $anaditta_rec['CODICE'];
            $insert_Info = 'Oggetto: Inserimento Nuova licenza ditta ' . $anaditta_rec['CODICE'] . " - lic " . $anaditta_rec['CODICE'];
            if (!$this->insertRecord($this->GAFIERE_DB, 'DITTELIC', $dittelic_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento Nuova licenza ditta " . $anaditta_rec['CODICE'] . " - lic $numeroLic");
                return false;
            }
            //rileggo il record appena inserito
            $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND TIPOAUTORIZZAZIONE = '" . $dittelic_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $dittelic_rec['NUMERO'] . "'";
            $dittelic_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
        }
        if (!$dittelic_rec) {
            $this->setErrMessage("Licenza non trovata o inserimento impossibile. Procedura interrotta!");
            return false;
        }
        /*
         * inserimento record fierecom per ogni fiera selezionata
         */
        //cerco tutte le fiere a cui si è scelto di partecipare
        $fiere_tab = array();
        //cerco la data di invio domanda
        //$sql = "SELECT * FROM PRORIC WHERE RICNUM = '" . $_POST[$this->nameForm . '_PROGES']['GESPRA'] . "'";
        $proges_rec = $this->praLib->GetProges($currGesnum);
        $sql = "SELECT * FROM PRORIC WHERE RICNUM = '" . $proges_rec['GESPRA'] . "'";
        $proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        foreach ($arrayCodFiere as $rowid => $valore) {
            if ($valore == 0 || $valore = '') {
                continue;
            }
            $sql = "SELECT * FROM FIERE WHERE ROWID = '$rowid'";
            if ($rowidFiera) {
                $sql = "SELECT * FROM FIERE WHERE ROWID = $rowidFiera";
            }
            $fiere_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false); //ci può essere al più una fiera creata per tipo
            if (!$fiere_rec) {
                $sql = "SELECT * FROM FIERE WHERE FIERA = '$rowid' AND DATAAGGIORNAMENTO = ''"; //ricerca per codice per versioni non allineate
                $fiere_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
            }
            $fiere_tab[] = $fiere_rec;
        }

        $proges_rec = $this->praLib->GetProges($currGesnum);
        $rowid_parent = 0;
        foreach ($fiere_tab as $fiere_rec) {
            $fierecom_rec = array();
            $fierecom_rec['FIERA'] = $fiere_rec['FIERA'];
            $fierecom_rec['DATA'] = $fiere_rec['DATA'];
            $fierecom_rec['ASSEGNAZIONE'] = $fiere_rec['ASSEGNAZIONE'];
            $fierecom_rec['TIPOATTIVITA'] = $anaditta_rec['TIPOATTIVITA'];
            $fierecom_rec['CODICE'] = $anaditta_rec['CODICE'];
            $fierecom_rec['TIPOAUTORIZZAZIONE'] = $dittelic_rec['TIPOAUTORIZZAZIONE'];
            $fierecom_rec['NUMERO'] = $dittelic_rec['NUMERO'];
            $fierecom_rec['DENOMINAZIONE'] = $anaditta_rec['DENOMINAZIONE'];
            $fierecom_rec['DATADOMANDA'] = $proric_rec['RICDAT'];
            $fierecom_rec['PROTOCOLLO'] = substr($proges_rec['GESNPR'], 4);
            $fierecom_rec['DATAPROTOCOLLO'] = $proges_rec['GESDRI'];
            $fierecom_rec['TIPONS'] = "NORMALE";
            $fierecom_rec['PEC'] = $pec;
            $comunicazioni = "";
            foreach ($posti_confermati as $rowidConf) {
                $fiereConf_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, "SELECT * FROM FIERE WHERE ROWID = " . $rowidConf, false);
                if (!$fiereConf_rec) {
                    continue;
                }
                if ($fiereConf_rec['ROWID'] == $fiere_rec['ROWID']) {
                    $comunicazioni .= "CONFERMATO POSTEGGIO PER ANNO PRECEDENTE\n";
                }
            }
            if (!$posti_confermati) {
                $comunicazioni .= "Non ci sono posteggi confermati.";
            }
            $comunicazioni .= "VIE PROPOSTE:\n";
            foreach ($vie_proposte as $rowidFieraVia => $vie) {
                $fiereVie_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, "SELECT * FROM FIERE WHERE ROWID = " . $rowidFieraVia, false);
                if (!$fiereVie_rec) {
                    continue;
                }
                if ($fiereVie_rec['ROWID'] == $fiere_rec['ROWID']) {
                    foreach ($vie as $via) {
                        $comunicazioni .= "$via\n";
                    }
                }
            }
            //materiale
            if ($materiale != '') {
                $comunicazioni .= "\r\nMATERIALE SCELTO:\n";
                $comunicazioni .= $materiale;
            }

            $fierecom_rec['COMUNICAZIONEFIERA'] = $comunicazioni;

            //1 = "A", 2 = "B", 3 = "C"
            switch ($tipoAttivita) {
                case 1:
                    $fierecom_rec['TIPOATTIVITA'] = "A";
                    break;
                case 2:
                    $fierecom_rec['TIPOATTIVITA'] = "B";
                    break;
                case 3:
                    $fierecom_rec['TIPOATTIVITA'] = "C";
                    break;
                default:
                    $fierecom_rec['TIPOATTIVITA'] = "X";
                    break;
            }

            //verifico che la domanda non sia già presente
            $sql = "SELECT * FROM FIERECOM WHERE FIERA = '" . $fierecom_rec['FIERA'] . "' AND DATA = '" . $fierecom_rec['DATA'] . "' AND CODICE = " . $fierecom_rec['CODICE'] . ""
                    . " AND TIPOAUTORIZZAZIONE = '" . $fierecom_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $fierecom_rec['NUMERO'] . "'";
            $check_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
            if ($check_rec) {
                //$fierecom_rec = $check_rec;
                //$this->setErrMessage("Domanda per la fiera " . $fierecom_rec['FIERA'] . " già presente! Procedura interrotta.");
                //return false;
            }
            if ($rowid_parent != 0) {
                $fierecom_rec['ROWID_PARENT'] = $rowid_parent;
            }
            $insert_Info = "Oggetto: Inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'];
            if (!$this->insertRecord($this->GAFIERE_DB, 'FIERECOM', $fierecom_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA']);
                return false;
            }
            //rileggo il record di fierecom
            $sql = "SELECT * FROM FIERECOM WHERE FIERA = '" . $fierecom_rec['FIERA'] . "' AND DATA = '" . $fierecom_rec['DATA'] . "' AND CODICE = " . $fierecom_rec['CODICE'] . ""
                    . " AND TIPOAUTORIZZAZIONE = '" . $fierecom_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $fierecom_rec['NUMERO'] . "'";
            $fierecom_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
            if (!$fierecom_rec) {
                $this->setErrMessage("Non trovo la domanda appena inserita, procedura interrotta. Controllare!");
                return false;
            }
            if ($rowid_parent == 0) {
                $rowid_parent = $fierecom_rec['ROWID'];
            }
            //inserimento tabella di collegamento
            $fieresuap_rec = array();
            $fieresuap_rec['IDFIERECOM'] = $fierecom_rec['ROWID'];
            $fieresuap_rec['SUAPID'] = $proges_rec['ROWID'];
            $fieresuap_rec['SUAKEY'] = "";
            $insert_Info = "Oggetto: Inserimento collegamento suap-fierecom " . $fierecom_rec['ROWID'] . " - pratica: " . $proges_rec['ROWID'];
            if (!$this->insertRecord($this->GAFIERE_DB, 'FIERESUAP', $fieresuap_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento collegamento suap-fierecom " . $fierecom_rec['ROWID'] . " - pratica: " . $proges_rec['ROWID']);
                return false;
            }

            /*
             * Per ogni domanda aggiungo i documenti allegati in FIEREDOC - prendo il rapporto completo
             */
            $pathAllegatiRichiste = str_replace('$ditta', App::$utente->getKey('ditta'), Config::getPath('general.itaCms'));
            $listAllegati = $this->praLib->GetFileList($pathAllegatiRichiste . "attachments/" . $proges_rec['GESPRA']);
            if ($listAllegati) {
                foreach ($listAllegati as $allegato) {
//                    if (strpos($allegato['FILENAME'], "rapporto.pdf") !== false) {
                    if (strtolower(pathinfo($allegato['FILEPATH'], PATHINFO_EXTENSION)) == 'pdf' || strtolower(pathinfo($allegato['FILEPATH'], PATHINFO_EXTENSION)) == 'p7m') {
                        $randName = md5(rand() * time()) . ".pdf";
                        $doc_path = $this->gfmLib->SetDirectoryGafiere($fierecom_rec['FIERA'], "FIEREDOC");
                        $fieredoc_rec = array();
                        $fieredoc_rec['DOCUMENTO'] = $allegato['FILENAME'];
                        $fieredoc_rec['NOTE'] = "";
                        $fieredoc_rec['DATAPRESENTAZIONE'] = $proric_rec['RICDAT'];
                        $fieredoc_rec['ID_DOMANDA'] = $fierecom_rec['ROWID'];
                        $fieredoc_rec['NECESSARIO'] = "N";
                        $fieredoc_rec['FILE'] = $randName;
                        copy($allegato['FILEPATH'], $doc_path . DIRECTORY_SEPARATOR . $randName);
                        $insert_Info = "Oggetto: Inserimento nuovo documento ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'];
                        if (!$this->insertRecord($this->GAFIERE_DB, 'FIEREDOC', $fieredoc_rec, $insert_Info)) {
                            $this->setErrMessage("Errore in inserimento nuovo documento ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA']);
                            return false;
                        }
                    }
                }
            }
        }

        //AGGIUNGO PER OGNI DOMANDA UNA RIGA DI COSAP
        $cosap_rec = array();
        $cosap_rec['TIPO'] = "OTT%";
        $cosap_rec['ANNO'] = date('Y');
        $cosap_rec['DATA'] = date('Ymd');
        $cosap_rec['CODICEDITTA'] = $anaditta_rec['CODICE'];
        $cosap_rec['DENOMINAZIONE'] = $anaditta_rec['DENOMINAZIONE'];
        $cosap_rec['RATA'] = 'UNICA';
        $cosap_rec['MQ'] = $mq;
        $cosap_rec['LUNGHEZZA'] = $lunghezza;
        $cosap_rec['LARGHEZZA'] = $larghezza;
        $cosap_rec['GIORNATAINTERA'] = $GiornataIntera;
        $cosap_rec['FONTECOSAP'] = 'FIERA';
        //prima di inserire la COSAP faccio il controllo se esiste
        $sql = "SELECT * FROM COSAP WHERE TIPO = '" . addslashes($cosap_rec['TIPO']) . "' AND ANNO = " . $cosap_rec['ANNO'] . " AND DATA = '" . $cosap_rec['DATA'] . "' AND CODICEDITTA = " . $cosap_rec['CODICEDITTA'];
        $check_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false);
        if ($check_rec) {
            Out::msgInfo("Attenzione", "Domande caricate, ma risulta già inserita la riga di COSAP per l'anno " . $cosap_rec['ANNO'] . " per la ditta " . $anaditta_rec['DENOMINAZIONE']);
            return true;
        }


        $insert_Info = "Oggetto: Inserimento COSAP da domanda online ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'];
        if (!$this->insertRecord($this->GAFIERE_DB, 'COSAP', $cosap_rec, $insert_Info)) {
            $this->setErrMessage("Errore in inserimento COSAP ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA']);
            return false;
        }
        //far visualizzare direttamente la domanda?? Se ne sono più di una?
        //apro dettaglio ultima domanda
        $model = 'gfmFierePosti';
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['rowidFierecom'] = $fierecom_rec['ROWID'];
        $_POST['data'] = $fiere_rec['DATA'];
        $_POST['fiera'] = $fiere_rec['FIERA'];
        $_POST['rowidFiera'] = $fiere_rec['ROWID'];
        $_POST['assegnazione'] = $fiere_rec['ASSEGNAZIONE'];
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();

        return true;
    }

    public function CollegaBandiFiere($praDati, $praDatiPratica, $currGesnum, $rowidFiera, $arrSelezionate = array()) {
        //tutte le tipologie di importazione DEVONO avere i codici delle fiere selezionate in DENOM_FIERA_BANDO
//        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DENOM_FIERA_BANDO'";
        $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGTIP='Denom_FieraBando' AND DAGVAL <> ''";
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        if (!$prodag_rec) {
            //cerco fiera pluriennale
//            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DENOM_FIERAP_BANDO'";
            $sql = "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGTIP='Denom_FieraPBando' AND DAGVAL <> ''";
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        }
        if (!is_numeric($prodag_rec['DAGVAL'])) {
            $prodag_rec = array();
        }
        if (!$prodag_rec && !$arrSelezionate) {
            if (!$rowidFiera) {
                $this->setErrMessage("1 - Codici Fiera non trovati.<br/>$sql");
                return false;
            }
        }
        //POSTEGGI_FIERA è solo per alcune domande, controllo solo la presenza
        $prodag_rec_posto = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='FIERA_NUMEROPOSTO' AND DAGVAL <> ''", false);
//        $arrayCodFiere = unserialize($prodag_rec['DAGVAL']);
        //di fatto torna un valore unico, lo ricostruisco come array con un elemento per avere gestione allineata
        if ($prodag_rec['DAGVAL']) {
            $arrayCodFiere = array($prodag_rec['DAGVAL'] => 1);
        }
        if (!$arrayCodFiere) {
            if ($rowidFiera) {
//                $Fiere_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, "SELECT * FROM FIERE WHERE ROWID = $rowidFiera", false);
//                $arrayCodFiere = array($Fiere_rec['FIERA'] => 1);
            } else {
                if ($arrSelezionate) {
                    $arrayCodFiere = $arrSelezionate;
                } else {
                    $this->setErrMessage("2 - Codici Fiera non trovati: " . $prodag_rec['DAGVAL']);
                    return false;
                }
            }
        }
        //cerco tutte le fiere a cui si è scelto di partecipare - ne dovrebbe essere una soltanto
        $fiere_tab = array();
        foreach ($arrayCodFiere as $rowid => $valore) {
            if ($valore == 0 || $valore = '') {
                continue;
            }
//            $sql = "SELECT * FROM FIERE WHERE FIERA = '$codFiera' AND DATATERMINE >= '" . $proric_rec['RICDAT'] . "'";
//            $sql = "SELECT * FROM FIERE WHERE FIERA = '$codFiera' AND DATATERMINE >= '" . $proric_rec['RICDAT'] . "' AND DECENNALE = 0"; //non posso inserire domande per fiere decennali
            $sql = "SELECT * FROM FIERE WHERE ROWID = $rowid";
            if ($rowidFiera) {
                $sql = "SELECT * FROM FIERE WHERE ROWID = $rowidFiera";
            }
            $fiere_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false); //ci può essere al più una fiera creata per tipo
            $fiere_tab[] = $fiere_rec;
        }

        //se non c'è nemmeno una fiera restituisco false
        if (!$fiere_tab) {
            $this->setErrMessage("Impossibile collegare la domanda con la procedura automatica. Procedere manualmente.");
            return false;
        }

        //cerco la data di invio domanda
        //$sql = "SELECT * FROM PRORIC WHERE RICNUM = '" . $_POST[$this->nameForm . '_PROGES']['GESPRA'] . "'";
        $proges_rec = $this->praLib->GetProges($currGesnum);
        $sql = "SELECT * FROM PRORIC WHERE RICNUM = '" . $proges_rec['GESPRA'] . "'";
        $proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        $proges_rec = $this->praLib->GetProges($currGesnum);

        foreach ($praDatiPratica as $dato) {
            if ($dato['DAGVAL'] == '')
                continue;
            if ($dato['DAGKEY'] == "ESIBENTE_PEC") {
                $pec = $dato['DAGVAL'];
            }
        }
        $impIndividuale = $Legale = false;
        $check_licenza = $check_concessione = $check_subingresso = $check_graduatoria = false;
        $fiscaleIndividuale = "";
        foreach ($praDati as $dato) {
            if ($dato['DAGVAL'] == '')
                continue;
            if ($dato['DAGKEY'] == "DICHIARANTE_NATURALEGA_RADIO") {
                if ($dato['DAGVAL'] == "T") {
                    $impIndividuale = true;
                } elseif ($dato['DAGVAL'] == "R") {
                    $Legale = true;
                }
            }
            if ($dato['DAGKEY'] == "DICHIARANTE_CODICEFISCALE_CFI")
                $cfDichiarante = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_PARTITAIVA_PIVA")
                $pivaDichiarante = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NOME")
                $nome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_COGNOME")
                $cognome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_COGNOME_NOME") {
                $cognome = strtoupper($dato['DAGVAL']);
                $nome = "";
            }
            if ($dato['DAGKEY'] == "DICHIARANTE_SESSO_SEX")
                $sesso = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITADATA_DATA")
                $dataNascita = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITACOMUNE")
                $comuneNascita = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITACOMUNE")
                $comuneNascita = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZAVIA")
                $indirizzoResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACOMUNE")
                $comuneResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACIVICO")
                $civicoResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACAP_CAP")
                $capResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZAPROVINCIA_PV")
                $provinciaResidenza = strtoupper($dato['DAGVAL']);
            //dati della licenza
//            if ($dato['DAGKEY'] == "FIERE_TIPOLICENZA")
//                $tipoLic = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LICENZA_NUMERO")
                $numeroLic = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LICENZA_DATARILASCIO")
                $rilascioLic = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "LICENZA_SETTOREALIM")
                $alimentare = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LICENZA_SETTORENONALIM")
                $nonAlimentare = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LICENZA_COMUNERILASCIO")
                $comuneLic = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CHECK_LICENZA")
                $check_licenza = $dato['DAGVAL'] == "On" ? true : false;
            if ($dato['DAGKEY'] == "CHECK_CONCESSIONE")
                $check_concessione = $dato['DAGVAL'] == "On" ? true : false;
            if ($dato['DAGKEY'] == "LICENZA_PRECEDENTE_NUMERO")
                $numeroLicPre = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "CHECK_SUBINGRESSO")
                $check_subingresso = $dato['DAGVAL'] == "On" ? true : false;
            if ($dato['DAGKEY'] == "CHECK_GRADUATORIA")
                $check_graduatoria = $dato['DAGVAL'] == "On" ? true : false;
            if ($dato['DAGKEY'] == "FIERA_RADIOPUNTI")
                $punti_anzianita = $dato['DAGVAL'];
            if ($dato['DAGKEY'] == "LICENZA_PRESENZEFIERA")
                $presenze_fiera = $dato['DAGVAL'];
        }

        if ($impIndividuale == true) {
            foreach ($praDati as $dato) {
                if ($dato['DAGVAL'] == '')
                    continue;
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PARTITAIVA_PIVA")
                    $fiscaleIndividuale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECOMUNE")
                    $comuneSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDEVIA")
                    $indirizzoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECIVICO")
                    $civicoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECAP")
                    $capSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDEPROVINCIA_PV")
                    $provinciaSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_TELEFONO")
                    $telefonoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_NUMREGISTROIMPRESE")
                    $numRegImprese = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_DATAREGISTROIMPRESE")
                    $dataRegImprese = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_CCIAA")
                    $comuneCCIAA = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PEC")
                    $pecIndividuale = $dato['DAGVAL'];
            }
        }

        $fiscaleLegale = $PivaLegale = "";
        if ($Legale == true) {
            foreach ($praDati as $dato) {
                if ($dato['DAGVAL'] == '')
                    continue;
                if ($dato['DAGKEY'] == "IMPRESA_CODICEFISCALE_CFI")
                    $fiscaleLegale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_PARTITAIVA_PIVA")
                    $PivaLegale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_RAGIONESOCIALE")
                    $ragSoc = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECOMUNE")
                    $comuneSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDEVIA")
                    $indirizzoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECIVICO")
                    $civicoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECAP")
                    $capSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDEPROVINCIA_PV")
                    $provinciaSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_TELEFONO")
                    $telefonoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_NUMREGISTROIMPRESE")
                    $numRegImprese = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_COSTITUZIONE_DATA")
                    $dataRegImprese = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
                if ($dato['DAGKEY'] == "IMPRESA_CCIAA")
                    $comuneCCIAA = strtoupper($dato['DAGVAL']);
            }
        }

        foreach ($praDati as $dato) {
            if ($dato['DAGVAL'] == '')
                continue;
            if (!$dataRegImprese) {
                if ($dato['DAGKEY'] == "DICHIARANTE_DATAREGISTROIMPRESE1")
                    $dataRegImprese = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            }
            if (!$numRegImprese) {
                if ($dato['DAGKEY'] == "DICHIARANTE_NUMEROREGISTROIMPRESE1")
                    $numRegImprese = $dato['DAGVAL'];
            }
            if (!$comuneCCIAA) {
                if ($dato['DAGKEY'] == "DICHIARANTE_CCIAA1")
                    $comuneCCIAA = $dato['DAGVAL'];
            }
        }

        foreach ($fiere_tab as $fiereSel_rec) {
            /*
             * PER OGNI FIERA CERCO L'ENTE ASSOCIATO
             */
            $Anafiere_rec = $this->gfmLib->GetAnafiere($fiereSel_rec['FIERA']);
            if (!$Anafiere_rec) {
                $this->setErrMessage("Anagrafica fiera " . $fiereSel_rec['FIERA'] . " non trovata. Procedura interrotta");
                return false;
            }
            $codiceFiera = "";
//            $dittaFiere = App::$utente->getKey('ditta');
            if (!$Anafiere_rec['ENTEFIERE'] || $Anafiere_rec['ENTEFIERE'] == App::$utente->getKey('ditta')) {
                //se l'ente non è configurato o è lo stesso dell'utente prendo direttamente la fiera selezionata
//                $GAFIERE_DB = $this->GAFIERE_DB;
                $GAFIERE_DB = ItaDB::DBOpen('GAFIERE');
                $fiere_rec = $fiereSel_rec;
                $codiceFiera = $Anafiere_rec['TIPO'];
            } else {
//                $this->setErrMessage("Trovata ditta diversa codice " . $Anafiere_rec['ENTEFIERE']);
//                return false;
                try {
                    $GAFIERE_DB = ItaDB::DBOpen('GAFIERE', $Anafiere_rec['ENTEFIERE']);
                } catch (Exception $exc) {
                    $this->setErrMessage("Impossibile aprire il database GAFIERE" . $Anafiere_rec['ENTEFIERE'] . ". Procedura interrotta.");
                    return false;
                }
                //cerco la fiera dentro l'ente collegato, se non c'è esco
                $AnafiereCheck_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT * FROM ANAFIERE WHERE TIPO = '" . $Anafiere_rec['CODICEFIERAENTE'] . "'", false);
                if (!$AnafiereCheck_rec) {
                    $this->setErrMessage("Anagrafica fiera collegata " . $Anafiere_rec['CODICEFIERAENTE'] . " non trovata. Procedura interrotta.");
                    return false;
                }
                //cerco la fiera con la stessa data, se c'è seleziono quella, altrimenti la creo.
                $sql = "SELECT * FROM FIERE WHERE FIERA = '" . $AnafiereCheck_rec['TIPO'] . "' AND DATA = '" . $fiereSel_rec['DATA'] . "' AND ASSEGNAZIONE = '" . $fiereSel_rec['ASSEGNAZIONE'] . "' AND DECENNALE = '" . $fiereSel_rec['DECENNALE'] . "' AND TIPONS = '" . $fiereSel_rec['TIPONS'] . "'"
                        . " AND BANDO = 1";
                $fiereCheck_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                if ($fiereCheck_rec) {
                    $fiere_rec = $fiereCheck_rec;
                } else {
                    //se non l'ho trovata la inserisco
                    $new_fiera = $fiereSel_rec;
                    unset($new_fiera['ROWID']);
                    $new_fiera['FIERA'] = $AnafiereCheck_rec['TIPO'];
                    $this->insertRecord($GAFIERE_DB, "FIERE", $new_fiera, "Inserimento nuova fiera da SUAP " . $new_fiera['FIERA'] . " - " . $new_fiera['DATA']);
                    //rileggo il record appena inserito
                    $sql = "SELECT * FROM FIERE WHERE FIERA = '" . $AnafiereCheck_rec['TIPO'] . "' AND DATA = '" . $fiereSel_rec['DATA'] . "' AND ASSEGNAZIONE = '" . $fiereSel_rec['ASSEGNAZIONE'] . "' AND DECENNALE = '" . $fiereSel_rec['DECENNALE'] . "' AND TIPONS = '" . $fiereSel_rec['TIPONS'] . "'";
                    $fiere_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$fiere_rec) {
                        $this->setErrMessage("Fiera non inserita correttamente <br>" . print_r($fiere_rec, true));
                        return false;
                    }
                }
            }


            /*
             * RICERCA ED EVENTUALE INSERIMENTO DI DITTA E LICENZA
             */


            /*
             * ricerca ditta - eventuale inserimento
             */
            $anaditta_rec = array();
            if ($fiscaleIndividuale != "") {
                $sql = "SELECT * FROM ANADITTA WHERE (" . $GAFIERE_DB->strUpper("CODICEFISCALE") . " = '" . addslashes(strtoupper($fiscaleIndividuale)) . "' OR " . $GAFIERE_DB->strUpper("PIVA") . " = '" . addslashes(strtoupper($fiscaleIndividuale)) . "')";
                $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            }
            if (!$anaditta_rec) {
                if ($cfDichiarante != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE (" . $GAFIERE_DB->strUpper("CODICEFISCALE") . " = '" . addslashes(strtoupper($cfDichiarante)) . "' OR " . $GAFIERE_DB->strUpper("PIVA") . " = '" . addslashes(strtoupper($cfDichiarante)) . "')";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                }
            }
            if (!$anaditta_rec) {
                if ($fiscaleLegale != "") {
                    $sql = "SELECT 
                        ANADITTA.* 
                    FROM 
                        ANADITTA 
                     JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                     WHERE 
                        DITTESOGG.CF = '" . addslashes($fiscaleLegale) . "'";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE (" . $GAFIERE_DB->strUpper("CODICEFISCALE") . " = '" . addslashes(strtoupper($fiscaleLegale)) . "' OR " . $GAFIERE_DB->strUpper("PIVA") . " = '" . addslashes(strtoupper($fiscaleLegale)) . "')";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
            }
            if (!$anaditta_rec) {
                if ($PivaLegale != "") {
                    $sql = "SELECT 
                        ANADITTA.* 
                    FROM 
                        ANADITTA 
                     JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                     WHERE 
                        DITTESOGG.PIVA = '" . addslashes(strtoupper($PivaLegale)) . "'";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE (" . $GAFIERE_DB->strUpper("PIVA") . " = '" . addslashes(strtoupper($PivaLegale)) . "' OR " . $GAFIERE_DB->strUpper("CODICEFISCALE") . " = '" . addslashes(strtoupper($PivaLegale)) . "')";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
            }
            //cerco per PIVA
            if (!$anaditta_rec) {
                if ($pivaDichiarante != "") {
                    $sql = "SELECT * FROM ANADITTA WHERE (" . $GAFIERE_DB->strUpper("CODICEFISCALE") . " = '" . addslashes(strtoupper($pivaDichiarante)) . "' OR " . $GAFIERE_DB->strUpper("PIVA") . " = '" . addslashes(strtoupper($pivaDichiarante)) . "')";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                }
            }
            if (!$anaditta_rec) {
                if ($PivaLegale != "") {
                    $sql = "SELECT * FROM ANADITTA WHERE (" . $GAFIERE_DB->strUpper("CODICEFISCALE") . " = '" . addslashes(strtoupper($PivaLegale)) . "' OR " . $GAFIERE_DB->strUpper("PIVA") . " = '" . addslashes(strtoupper($PivaLegale)) . "')";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                }
            }
            $Parametri = $this->devLib->getEnv_config('GAFIERE', 'codice', 'GFMBLOCCAANAGDAPRA', false);
            $bloccaIns = $Parametri['CONFIG'];
            //Se non trovato e se non bloccato lo inserisco
            if (!$anaditta_rec && !$bloccaIns) {
                $anaditta_rec = array();
                $max_codice_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT MAX(CODICE) AS MASSIMO FROM ANADITTA", false);
                $anaditta_rec['CODICE'] = $max_codice_rec['MASSIMO'] + 1;
                if ($impIndividuale && ($cognome != '' || $nome != '')) {
                    $anaditta_rec['DENOMINAZIONE'] = $cognome . " " . $nome;
                } else {
                    if ($ragSoc != '') {
                        $anaditta_rec['DENOMINAZIONE'] = $ragSoc;
                    } else {
                        $anaditta_rec['DENOMINAZIONE'] = $cognome . " " . $nome;
                    }
                }
                $anaditta_rec['SESSO'] = $sesso;
                $anaditta_rec['DATANASCITA'] = $dataNascita;
                $anaditta_rec['COMUNENASCITA'] = $comuneNascita;
                $anaditta_rec['COMUNE'] = $comuneSede != '' ? $comuneSede : $comuneResidenza;
                $anaditta_rec['INDIRIZZO'] = $indirizzoSede != '' ? $indirizzoSede : $indirizzoResidenza;
                $anaditta_rec['NUMEROCIVICO'] = $civicoSede;
                $anaditta_rec['CAP'] = $capSede != '' ? $capSede : $capResidenza;
                $anaditta_rec['PROVINCIA'] = $provinciaSede != '' ? $provinciaSede : $provinciaResidenza;
                if ($impIndividuale && $fiscaleIndividuale != '') {
                    $anaditta_rec['CODICEFISCALE'] = $fiscaleIndividuale;
                } else {
                    if ($fiscaleLegale != '') {
                        if (strlen($fiscaleLegale) == 16) {
                            $anaditta_rec['CODICEFISCALE'] = $fiscaleLegale;
                        } else {
                            $anaditta_rec['PIVA'] = $fiscaleLegale;
                        }
                    } else {
                        if (strlen($fiscaleIndividuale) == 16) {
                            $anaditta_rec['CODICEFISCALE'] = $fiscaleIndividuale;
                        } else {
                            $anaditta_rec['PIVA'] = $fiscaleIndividuale;
                        }
                    }
                }
                if (!isset($anaditta_rec['CODICEFISCALE']) || $anaditta_rec['CODICEFISCALE'] == '') {
                    $anaditta_rec['CODICEFISCALE'] = $cfDichiarante;
                }
                if ($PivaLegale != '') {
                    $anaditta_rec['PIVA'] = $PivaLegale;
                }
                if (!isset($anaditta_rec['PIVA']) || $anaditta_rec['PIVA'] == '') {
                    $anaditta_rec['PIVA'] = $pivaDichiarante;
                }
                $anaditta_rec['TELEFONO'] = $telefonoSede;
                $anaditta_rec['EMAIL'] = $pecIndividuale != '' ? $pecIndividuale : $pec;
                //$this->eqAudit->logEqEvent($this, array('Operazione' => "SCARICO FOTOGRAMMI SERV:" . $servizio_rec['CODICESERV'] . DIRECTORY_SEPARATOR . $servizio_rec['ANNOSERV']));
                $insert_Info = 'Oggetto: Inserimento Nuova Ditta Codice ' . $anaditta_rec['CODICE'];
                if (!$this->insertRecord($GAFIERE_DB, 'ANADITTA', $anaditta_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento su ANADITTE per la ditta codice " . $anaditta_rec['CODICE']);
                    return false;
                }
                //$anaditta_rec = $this->gfmLib->GetAnaditta($anaditta_rec['CODICE']);
                $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT * FROM ANADITTA WHERE CODICE = " . $anaditta_rec['CODICE'], false);

                //Se c'è il legale rappresentante inseriesco anche il soggetto
                if ($Legale == true) {
                    $dittesogg_rec = array();
                    $dittesogg_rec['CODICE'] = $anaditta_rec['CODICE'];
                    $dittesogg_rec['RUOCOD'] = "0014";
                    $dittesogg_rec['NOMINATIVO'] = $cognome . " " . $nome;
                    $dittesogg_rec['DATANASCITA'] = $dataNascita;
                    $dittesogg_rec['COMUNENASCITA'] = $comuneNascita;
                    $dittesogg_rec['INDIRIZZO'] = $indirizzoResidenza;
                    $dittesogg_rec['CIVICO'] = $civicoResidenza;
                    $dittesogg_rec['COMUNE'] = $comuneResidenza;
                    $dittesogg_rec['CAP'] = $capResidenza;
                    $dittesogg_rec['PROVINCIA'] = $provinciaResidenza;
                    $dittesogg_rec['TELEFONO'] = $telefonoSede;
                    $dittesogg_rec['CF'] = $cfDichiarante;
                    $dittesogg_rec['PIVA'] = $PivaLegale;
                    $insert_Info = 'Oggetto: Inserimento Nuovo Soggetto ditta ' . $anaditta_rec['CODICE'] . " - cf $cfDichiarante";
                    if (!$this->insertRecord($GAFIERE_DB, 'DITTESOGG', $dittesogg_rec, $insert_Info)) {
                        $this->setErrMessage("Errore in inserimento Nuovo Soggetto ditta " . $anaditta_rec['CODICE'] . " - cf $cfDichiarante");
                        return false;
                    }
                }
            }
            //se non ho ancora la ditta restituisco messaggio di errore
            if (!$anaditta_rec) {
                $this->setErrMessage("Ditta non trovata o inserimento impossibile. Procedura interrotta.");
                return false;
            }

            /*
             * ricerca licenza - eventuale inserimento
             */
            $dittelic_rec = array();
            //non considero il tipo perchè potrebbero esserci incongruenze - quasi impossibile che qualcuno abbia due licenze con stesso numero e tipo diverso
            if ($numeroLic == "" && !$check_subingresso) {
                $this->setErrMessage("3 - Numero licenza non specificato");
                return false;
            }

            if ($numeroLicPre && !$numeroLic) {
                //cerco eventualmente col numero di licenza da subingresso
                if ($check_subingresso) {
                    $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND NUMAUT_I = '" . addslashes($numeroLicPre) . "'";
                    $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$dittelic_rec) {
                        $this->setErrMessage("3 - Numero licenza non trovato");
                        return false;
                    }
                    $numeroLic = $dittelic_rec['NUMERO'];
                }
            }

            //a questo punto se non ho un numero licenza blocco la procedura
            if (!$numeroLic) {
                $this->setErrMessage("3 - Numero licenza non trovato");
                return false;
            }

            $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND NUMERO = '" . addslashes($numeroLic) . "'";
            $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if (!$dittelic_rec) {
                //provo a cercare un numero con l'anno
                $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND NUMERO LIKE '" . addslashes($numeroLic) . "/%'";
                $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            }
            if (!$dittelic_rec) {
                $tipoLic = "B";
                //inserisco la licenza
                $dittelic_rec['CODICE'] = $anaditta_rec['CODICE'];
                $dittelic_rec['TIPOAUTORIZZAZIONE'] = $tipoLic;
                $dittelic_rec['NUMERO'] = $numeroLic;
                $dittelic_rec['DATARILASCIO'] = $dittelic_rec['DATARILASCIOLICENZA'] = $rilascioLic;
                $dittelic_rec['COMUNE'] = $comuneLic;
                if ($alimentare && !$nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "ALIMENTARE";
                }
                if (!$alimentare && $nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "NON ALIMENTARE";
                }
                if ($alimentare && $nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "MISTO";
                }
                $dittelic_rec['NUMEROISCRREGD'] = $numRegImprese;
                $dittelic_rec['DATAISCRLICENZA'] = $dataRegImprese;
                $dittelic_rec['DATAISCRIZIONEREGD'] = $dataRegImprese;
                $dittelic_rec['CCIAA'] = $comuneCCIAA;
                $insert_Info = 'Oggetto: Inserimento Nuova licenza ditta ' . $anaditta_rec['CODICE'] . " - lic $numeroLic";
                if (!$this->insertRecord($GAFIERE_DB, 'DITTELIC', $dittelic_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento Nuova licenza ditta " . $anaditta_rec['CODICE'] . " - lic $numeroLic");
                    return false;
                }
                //rileggo il record appena inserito
                $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND TIPOAUTORIZZAZIONE = '" . $dittelic_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . addslashes($dittelic_rec['NUMERO']) . "'";
                $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);

                //se non esiste la licenza provo ad inserire le presenze lette dal modello
                if ($presenze_fiera > 0) {
                    $dittepre_rec = array();
                    $dittepre_rec['CODICE'] = $anaditta_rec['CODICE'];
                    $dittepre_rec['TIPOFIERA'] = $fiereSel_rec['FIERA'];
                    $dittepre_rec['TIPOAUTORIZZAZIONE'] = $dittelic_rec['TIPOAUTORIZZAZIONE'];
                    $dittepre_rec['NUMERO'] = $dittelic_rec['NUMERO'];
                    $year_prev = (int) date('Y') - 1;
                    $dittepre_rec['DATA'] = $year_prev . "1231"; //metto la data al 31/12 dell'anno precedente
                    $dittepre_rec['NUMEROPRESENZE'] = $presenze_fiera;
                    $dittepre_rec['CODICEPRESENZA'] = "P";
                    $dittepre_rec['TIPOLOGIAPRESENZA'] = "P";
                    $insert_Info = 'Inserimento Presenze Nuova licenza ditta ' . $anaditta_rec['CODICE'] . " - lic $numeroLic";
                    if (!$this->insertRecord($GAFIERE_DB, 'DITTEPRE', $dittepre_rec, $insert_Info)) {
                        $this->setErrMessage("Errore in inserimento DITTEPRE ditta " . $anaditta_rec['CODICE'] . " - lic $numeroLic");
                        return false;
                    }
                }
            }
            if ($dittelic_rec['DATAINIZIOATTIVITALIC'] == '') {
                $dittelic_rec['DATAINIZIOATTIVITALIC'] = $rilascioLic;
            }
            if ($dittelic_rec['DATAINIZIOATTIVITA'] == '') {
                $dittelic_rec['DATAINIZIOATTIVITA'] = $rilascioLic;
            }
            if (!$dittelic_rec) {
                $this->setErrMessage("Licenza non trovata o inserimento impossibile. Procedura interrotta!");
                return false;
            }
            /*
             * FINE RICERCA/INSERIMENTO DITTE E LICENZE
             */

            $fierecom_rec = array();
            $fierecom_rec['FIERA'] = $fiere_rec['FIERA'];
//            $fierecom_rec['FIERA'] = $Anafiere_rec['FIERA'];
            $fierecom_rec['DATA'] = $fiere_rec['DATA'];
            $fierecom_rec['ASSEGNAZIONE'] = $fiere_rec['ASSEGNAZIONE'];
            $fierecom_rec['TIPOATTIVITA'] = $anaditta_rec['TIPOATTIVITA'];
            $fierecom_rec['CODICE'] = $anaditta_rec['CODICE'];
            $fierecom_rec['TIPOAUTORIZZAZIONE'] = $dittelic_rec['TIPOAUTORIZZAZIONE'];
            $fierecom_rec['NUMERO'] = $dittelic_rec['NUMERO'];
            $fierecom_rec['DENOMINAZIONE'] = $anaditta_rec['DENOMINAZIONE'];
            $fierecom_rec['DATADOMANDA'] = $proric_rec['RICDAT'];
            $fierecom_rec['TIPONS'] = "NORMALE";
            $fierecom_rec['PEC'] = $pec;
            if ($prodag_rec_posto) {
                $fierecom_rec['POSTO'] = $prodag_rec_posto['DAGVAL'];
                //cerco se c'è in anagrafica della fiera un soplo posteggio con quel numero
                $sql = "SELECT * FROM FIEREPOS WHERE TIPO = '" . $fiere_rec['FIERA'] . "' AND POSTO = '" . addslashes($fierecom_rec['POSTO']) . "'";
                $fierepos_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, true);
                if ($fierepos_rec) {
                    if (count($fierepos_rec) == 1) {
                        $fierecom_rec['POSTO'] = $fierepos_rec[0]['POSTO'];
                        $fierecom_rec['CODICEVIA'] = $fierepos_rec[0]['CODICEVIA'];
                        $fierecom_rec['LETTERA'] = $fierepos_rec[0]['LETTERA'];
                    }
                }
            } else {
                //cerco se c'è in anagrafica della fiera
                $sql = "SELECT * FROM FIEREPOS WHERE TIPO = '" . $fiere_rec['FIERA'] . "' AND CODICEDITTA = " . $anaditta_rec['CODICE'];
                $fierepos_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, true);
                if ($fierepos_rec) {
                    if (count($fierepos_rec) == 1) {
                        $fierecom_rec['POSTO'] = $fierepos_rec[0]['POSTO'];
                        $fierecom_rec['CODICEVIA'] = $fierepos_rec[0]['CODICEVIA'];
                        $fierecom_rec['LETTERA'] = $fierepos_rec[0]['LETTERA'];
                    }
                }
            }

            //$fierecom_rec['IDFASCICOLO'] = $proges_rec['ROWID'];
            //verifico che la domanda non sia già presente
            $sql = "SELECT * FROM FIERECOM WHERE FIERA = '" . $fierecom_rec['FIERA'] . "' AND DATA = '" . $fierecom_rec['DATA'] . "' AND CODICE = " . $fierecom_rec['CODICE'] . ""
                    . " AND TIPOAUTORIZZAZIONE = '" . $fierecom_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $fierecom_rec['NUMERO'] . "'";
            $sql .= " AND CODICEVIA = '" . $fierecom_rec['CODICEVIA'] . "'";
            $check_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if ($check_rec) {
                $fierecom_rec = $check_rec;
                $Anafiere = $this->gfmLib->GetAnafiere($fierecom_rec['FIERA']);
                $this->setErrMessage("Domanda per la fiera " . $Anafiere['FIERA'] . " già presente! Procedura interrotta.<br>");
                return false;
            }

            $fierecom_rec['DECENNALE'] = $fiereSel_rec['DECENNALE'];
            $fierecom_rec['TIPOATTIVITA'] = $fiereSel_rec['TIPOATTIVITA'];

            //calcolo dei punteggi - caso fiera decennale
            if ($fiereSel_rec['DECENNALE']) {
                if ($check_concessione || $check_subingresso) {
                    $fierecom_rec['PUNTEGGIO2'] = 40;
                }
            } else {
                if ($check_graduatoria) {
                    $fierecom_rec['PUNTEGGIO2'] = 40;
                }
            }
            if (!$punti_anzianita) {
                $punti_anzianita = 0;
                //calcolo anni da $dataRegImprese
                include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';
                /* @var $itaDate itaDate */
                $itaDate = new itaDate();
                $anni = $itaDate->dateDiffYears(date('Ymd'), $dataRegImprese);
                if ($anni <= 5) {
                    $punti_anzianita = 40;
                }
                if ($anni > 5 && $anni <= 10) {
                    $punti_anzianita = 50;
                }
                if ($anni > 10) {
                    $punti_anzianita = 60;
                }
            }

            $fierecom_rec['PUNTEGGIO1'] = $punti_anzianita;
            $fierecom_rec['PUNTEGGIOTOTALE'] = $fierecom_rec['PUNTEGGIO1'] + $fierecom_rec['PUNTEGGIO2'];

            $insert_Info = "Oggetto: Inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'];
            try {
                ItaDB::DBInsert($GAFIERE_DB, "FIERECOM", "ROWID", $fierecom_rec);
            } catch (Exception $exc) {
                $this->setErrMessage("Errore in inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'] . "<br><br>" . $exc->getMessage());
                return false;
            }
//            if (!$this->insertRecord($GAFIERE_DB, 'FIERECOM', $fierecom_rec, $insert_Info)) {
//                $this->setErrMessage("Errore in inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA']);
//                return false;
//            }
            //rileggo il record di fierecom
            $sql = "SELECT * FROM FIERECOM WHERE FIERA = '" . $fierecom_rec['FIERA'] . "' AND DATA = '" . $fierecom_rec['DATA'] . "' AND CODICE = " . $fierecom_rec['CODICE'] . ""
                    . " AND TIPOAUTORIZZAZIONE = '" . $fierecom_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $fierecom_rec['NUMERO'] . "'";
            //come condizione impongo anche il codice via
            $sql .= " AND CODICEVIA = '" . $fierecom_rec['CODICEVIA'] . "'";

            $fierecom_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if (!$fierecom_rec) {
                $this->setErrMessage("Non trovo la domanda appena inserita, procedura interrotta. Controllare!<br>" . print_r($fierecom_rec, true));
                return false;
            }
            //inserimento tabella di collegamento
            $fieresuap_rec = array();
            $fieresuap_rec['IDFIERECOM'] = $fierecom_rec['ROWID'];
            $fieresuap_rec['SUAPID'] = $proges_rec['ROWID'];
            $fieresuap_rec['SUAKEY'] = App::$utente->getKey('ditta');
            $insert_Info = "Oggetto: Inserimento collegamento suap-fierecom " . $fierecom_rec['ROWID'] . " - pratica: " . $proges_rec['ROWID'];
            if (!$this->insertRecord($GAFIERE_DB, 'FIERESUAP', $fieresuap_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento collegamento suap-fierecom " . $fierecom_rec['ROWID'] . " - pratica: " . $proges_rec['ROWID']);
                return false;
            }

            /*
             * Per ogni domanda aggiungo i documenti allegati in FIEREDOC - prendo il rapporto completo
             */
            $model = 'praFascicolo.class';
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $praFascicolo = new praFascicolo($currGesnum);
//            $allegati = $praFascicolo->getAllegatiProtocollaPratica();
            //cambio la chiamata per ignorare se il documento è stato già protocollato
            $allegati = $praFascicolo->getAllegatiProtocollaPratica('Paleo', true, false, array(), true);
            if ($allegati['Principale']['Stream']) {
                //dato che ho il base64 posso creare il file dentro la directory delle domande
                $randName = md5(rand() * time()) . ".pdf.p7m";
                if (!$Anafiere_rec['ENTEFIERE'] || $Anafiere_rec['ENTEFIERE'] == App::$utente->getKey('ditta')) {
                    $doc_path = $this->gfmLib->SetDirectoryGafiere($fierecom_rec['FIERA'], "FIEREDOC");
                } else {
                    $doc_path = $this->gfmLib->SetDirectoryGafiere($fierecom_rec['FIERA'], "FIEREDOC", true, $Anafiere_rec['ENTEFIERE']);
                }
                $fieredoc_rec = array();
                $fieredoc_rec['DOCUMENTO'] = $allegati['Principale']['Nome'];
                $fieredoc_rec['NOTE'] = $allegati['Principale']['Descrizione'];
                $fieredoc_rec['DATAPRESENTAZIONE'] = $proric_rec['RICDAT'];
                $fieredoc_rec['ID_DOMANDA'] = $fierecom_rec['ROWID'];
                $fieredoc_rec['NECESSARIO'] = "N";
                $fieredoc_rec['FILE'] = $randName;
                $pathDest = $doc_path . DIRECTORY_SEPARATOR . $randName;
                //elimino i doppi slash
                $pathDest = str_replace("//", "/", $pathDest);
                $pathDest = str_replace("\\\\", "\\", $pathDest);
                file_put_contents($pathDest, $allegati['Principale']['Stream']);
                $insert_Info = "Oggetto: Inserimento nuovo documento ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA'];
                if (!$this->insertRecord($GAFIERE_DB, 'FIEREDOC', $fieredoc_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento nuovo documento ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $fiere_rec['FIERA'] . " - data: " . $fiere_rec['DATA']);
                    return false;
                }
            }
        }

        return true;
    }

    public function CollegaBandiMercati($praDati, $praDatiPratica, $currGesnum, $rowidMercato, $arrSelezionati = array()) {
        //tutte le tipologie di importazione DEVONO avere i codici delle fiere selezionate in DENOM_MERCATO_BANDO
//        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DENOM_MERCATO_BANDO'", false);
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGTIP='Denom_MercatoBando' AND DAGVAL <> ''", false);
        if (!$prodag_rec) {
            //cerco fiera pluriennale
//            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='DENOM_PI_BANDO'", false);
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGTIP='Denom_PIBando' AND DAGVAL <> ''", false);
        }
        if (!is_numeric($prodag_rec['DAGVAL'])) {
            $prodag_rec = array();
        }
        if (!$prodag_rec && !$arrSelezionati) {
            if (!$rowidMercato) {
                $this->setErrMessage("Codici Mercato non trovati");
                return false;
            }
        }
        //POSTEGGI_MERCATO è solo per alcune domande, controllo solo la presenza
        $prodag_rec_posto = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='MERCATO_NUMEROPOSTO' AND DAGVAL <> ''", false);
        $prodag_rec_pi_via = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM='$currGesnum' AND DAGKEY='POSTEGGIISOLATI_VIA' AND DAGVAL <> ''", false);
        if ($prodag_rec) {
            $arrayCodMercati = array($prodag_rec['DAGVAL'] => 1);
        }
        if (!$arrayCodMercati) {
            if ($rowidMercato) {
//                $BandiM_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, "SELECT * FROM BANDIM WHERE ROWID = $rowidMercato", false);
//                $arrayCodMercati = array($BandiM_rec['FIERA'] => 1);
            } else {
                if ($arrSelezionati) {
                    $arrayCodMercati = $arrSelezionati;
                } else {
                    $this->setErrMessage("Codici Mercato non trovati: " . $prodag_rec['DAGVAL']);
                    return false;
                }
            }
        }
        //cerco tutti i mercati a cui si è scelto di partecipare - ne dovrebbe essere uno soltanto
        $bandim_tab = array();
        foreach ($arrayCodMercati as $rowid => $valore) {
            if ($valore == 0 || $valore = '') {
                continue;
            }
//            $sql = "SELECT * FROM BANDIM WHERE FIERA = '$codMercato' AND DATATERMINE >= '" . $proric_rec['RICDAT'] . "'";
//            $sql = "SELECT * FROM BANDIM WHERE FIERA = '$codMercato' AND DATATERMINE >= '" . $proric_rec['RICDAT'] . "' AND DECENNALE = 0"; //non posso inserire domande per fiere decennali
            $sql = "SELECT * FROM BANDIM WHERE ROWID = $rowid";
            if ($rowidMercato) {
                $sql = "SELECT * FROM BANDIM WHERE ROWID = $rowidMercato";
            }
            $bandim_rec = ItaDB::DBSQLSelect($this->GAFIERE_DB, $sql, false); //ci può essere al più una fiera creata per tipo
            $bandim_tab[] = $bandim_rec;
        }

        //se non c'è nemmeno una fiera restituisco false
        if (!$bandim_tab) {
            $this->setErrMessage("Impossibile collegare la domanda con la procedura automatica. Procedere manualmente.");
            return false;
        }

        //cerco la data di invio domanda
        //$sql = "SELECT * FROM PRORIC WHERE RICNUM = '" . $_POST[$this->nameForm . '_PROGES']['GESPRA'] . "'";
        $proges_rec = $this->praLib->GetProges($currGesnum);
        $sql = "SELECT * FROM PRORIC WHERE RICNUM = '" . $proges_rec['GESPRA'] . "'";
        $proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
        $proges_rec = $this->praLib->GetProges($currGesnum);

        foreach ($praDatiPratica as $dato) {
            if ($dato['DAGVAL'] == '')
                continue;
            if ($dato['DAGKEY'] == "ESIBENTE_PEC") {
                $pec = $dato['DAGVAL'];
            }
        }
        $impIndividuale = $Legale = false;
        $check_licenza = $check_concessione = $check_subingresso = $check_graduatoria = false;
        foreach ($praDati as $dato) {
            if ($dato['DAGVAL'] == '')
                continue;
            if ($dato['DAGKEY'] == "DICHIARANTE_NATURALEGA_RADIO") {
                if ($dato['DAGVAL'] == "T") {
                    $impIndividuale = true;
                } elseif ($dato['DAGVAL'] == "R") {
                    $Legale = true;
                }
            }
            if ($dato['DAGKEY'] == "DICHIARANTE_CODICEFISCALE_CFI")
                $cfDichiarante = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NOME")
                $nome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_COGNOME")
                $cognome = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_SESSO_SEX")
                $sesso = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITADATA_DATA")
                $dataNascita = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITACOMUNE")
                $comuneNascita = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_NASCITACOMUNE")
                $comuneNascita = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZAVIA")
                $indirizzoResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACOMUNE")
                $comuneResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACIVICO")
                $civicoResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZACAP_CAP")
                $capResidenza = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "DICHIARANTE_RESIDENZAPROVINCIA_PV")
                $provinciaResidenza = strtoupper($dato['DAGVAL']);
            //dati della licenza
//            if ($dato['DAGKEY'] == "BANDIM_TIPOLICENZA")
//                $tipoLic = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LICENZA_NUMERO")
                $numeroLic = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LICENZA_DATARILASCIO")
                $rilascioLic = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            if ($dato['DAGKEY'] == "LICENZA_SETTOREALIM")
                $alimentare = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LICENZA_SETTORENONALIM")
                $nonAlimentare = strtoupper($dato['DAGVAL']);
            if ($dato['DAGKEY'] == "LICENZA_COMUNERILASCIO")
                if ($dato['DAGKEY'] == "CHECK_LICENZA")
                    $check_licenza = $dato['DAGVAL'];
            if ($dato['DAGKEY'] == "CHECK_CONCESSIONE")
                $check_concessione = $dato['DAGVAL'];
            if ($dato['DAGKEY'] == "CHECK_SUBINGRESSO")
                $check_subingresso = $dato['DAGVAL'];
            if ($dato['DAGKEY'] == "CHECK_GRADUATORIA")
                $check_graduatoria = $dato['DAGVAL'];
            if ($dato['DAGKEY'] == "MERCATO_RADIOPUNTI")
                $punti_anzianita = $dato['DAGVAL'];
        }

        $fiscaleIndividuale = "";
        if ($impIndividuale == true) {
            foreach ($praDati as $dato) {
                if ($dato['DAGVAL'] == '')
                    continue;
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PARTITAIVA_PIVA")
                    $fiscaleIndividuale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECOMUNE")
                    $comuneSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDEVIA")
                    $indirizzoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECIVICO")
                    $civicoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDECAP")
                    $capSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_SEDEPROVINCIA_PV")
                    $provinciaSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_TELEFONO")
                    $telefonoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_NUMREGISTROIMPRESE")
                    $numRegImprese = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_DATAREGISTROIMPRESE")
                    $dataRegImprese = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_CCIAA")
                    $comuneCCIAA = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESAINDIVIDUALE_PEC")
                    $pecIndividuale = $dato['DAGVAL'];
            }
        }

        $fiscaleLegale = $PivaLegale = "";
        if ($Legale == true) {
            foreach ($praDati as $dato) {
                if ($dato['DAGVAL'] == '')
                    continue;
                if ($dato['DAGKEY'] == "IMPRESA_CODICEFISCALE_CFI")
                    $fiscaleLegale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_PARTITAIVA_PIVA")
                    $PivaLegale = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_RAGIONESOCIALE")
                    $ragSoc = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECOMUNE")
                    $comuneSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDEVIA")
                    $indirizzoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECIVICO")
                    $civicoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDECAP")
                    $capSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_SEDEPROVINCIA_PV")
                    $provinciaSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_TELEFONO")
                    $telefonoSede = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_NUMREGISTROIMPRESE")
                    $numRegImprese = strtoupper($dato['DAGVAL']);
                if ($dato['DAGKEY'] == "IMPRESA_COSTITUZIONE_DATA")
                    $dataRegImprese = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
                if ($dato['DAGKEY'] == "IMPRESA_CCIAA")
                    $comuneCCIAA = strtoupper($dato['DAGVAL']);
            }
        }

        foreach ($praDati as $dato) {
            if ($dato['DAGVAL'] == '')
                continue;
            if (!$dataRegImprese) {
                if ($dato['DAGKEY'] == "DICHIARANTE_DATAREGISTROIMPRESE1")
                    $dataRegImprese = substr($dato['DAGVAL'], 6, 4) . substr($dato['DAGVAL'], 3, 2) . substr($dato['DAGVAL'], 0, 2);
            }
            if (!$numRegImprese) {
                if ($dato['DAGKEY'] == "DICHIARANTE_NUMEROREGISTROIMPRESE1")
                    $numRegImprese = $dato['DAGVAL'];
            }
            if (!$comuneCCIAA) {
                if ($dato['DAGKEY'] == "DICHIARANTE_CCIAA1")
                    $comuneCCIAA = $dato['DAGVAL'];
            }
        }

        foreach ($bandim_tab as $bandimSel_rec) {
            /*
             * PER OGNI FIERA CERCO L'ENTE ASSOCIATO
             */
            $Anamerc_rec = $this->gfmLib->GetAnamerc($bandimSel_rec['FIERA']);
            if (!$Anamerc_rec) {
                $this->setErrMessage("Anagrafica mercato " . $bandimSel_rec['FIERA'] . " non trovato. Procedura interrotta");
                return false;
            }
            $codiceMercato = "";
//            $dittaFiere = App::$utente->getKey('ditta');
            if (!$Anamerc_rec['ENTEFIERE'] || $Anamerc_rec['ENTEFIERE'] == App::$utente->getKey('ditta')) {
                //se l'ente non è configurato o è lo stesso dell'utente prendo direttamente la fiera selezionata
//                $GAFIERE_DB = $this->GAFIERE_DB;
                $GAFIERE_DB = ItaDB::DBOpen('GAFIERE');
                $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
                $bandim_rec = $bandimSel_rec;
                $codiceMercato = $Anamerc_rec['TIPO'];
            } else {
//                $this->setErrMessage("Trovata ditta diversa codice " . $Anamerc_rec['ENTEFIERE']);
//                return false;
                try {
                    $GAFIERE_DB = ItaDB::DBOpen('GAFIERE', $Anamerc_rec['ENTEFIERE']);
                } catch (Exception $exc) {
                    $this->setErrMessage("Impossibile aprire il database GAFIERE" . $Anamerc_rec['ENTEFIERE'] . ". Procedura interrotta.");
                    return false;
                }
                try {
                    $ITALWEB_DB = ItaDB::DBOpen('ITALWEB', $Anamerc_rec['ENTEFIERE']);
                } catch (Exception $exc) {
                    $this->setErrMessage("Impossibile aprire il database GAFIERE" . $Anamerc_rec['ENTEFIERE'] . ". Procedura interrotta.");
                    return false;
                }
                //cerco il mercato dentro l'ente collegato, se non c'è esco
                $AnamercCheck_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT * FROM ANAMERC WHERE CODICE = '" . $Anamerc_rec['CODICEFIERAENTE'] . "'", false);
                if (!$AnamercCheck_rec) {
                    $this->setErrMessage("Anagrafica mercato collegato " . $Anamerc_rec['CODICEFIERAENTE'] . " non trovato. Procedura interrotta.");
                    return false;
                }
                //cerco il mercato con la stessa data, se c'è seleziono quella, altrimenti la creo.
                $sql = "SELECT * FROM BANDIM WHERE FIERA = '" . $AnamercCheck_rec['CODICE'] . "' AND DATA = '" . $bandimSel_rec['DATA'] . "' AND ASSEGNAZIONE = '" . $bandimSel_rec['ASSEGNAZIONE'] . "' AND DECENNALE = '" . $bandimSel_rec['DECENNALE'] . "' AND TIPONS = '" . $bandimSel_rec['TIPONS'] . "'"
                        . " AND BANDO = 1";
                $bandimCheck_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                if ($bandimCheck_rec) {
                    $bandim_rec = $bandimCheck_rec;
                } else {
                    //se non l'ho trovato lo inserisco
                    $new_bandom = $bandimSel_rec;
                    unset($new_bandom['ROWID']);
                    $new_bandom['FIERA'] = $AnamercCheck_rec['CODICE'];
                    $this->insertRecord($GAFIERE_DB, "BANDIM", $new_bandom, "Inserimento nuovo bando mercato da SUAP " . $new_bandom['FIERA'] . " - " . $new_bandom['DATA']);
                    //rileggo il record appena inserito
                    $sql = "SELECT * FROM BANDIM WHERE FIERA = '" . $AnamercCheck_rec['TIPO'] . "' AND DATA = '" . $bandimSel_rec['DATA'] . "' AND ASSEGNAZIONE = '" . $bandimSel_rec['ASSEGNAZIONE'] . "' AND DECENNALE = '" . $bandimSel_rec['DECENNALE'] . "' AND TIPONS = '" . $bandimSel_rec['TIPONS'] . "'";
                    $bandim_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$bandim_rec) {
                        $this->setErrMessage("Mercato non inserita correttamente <br>" . print_r($bandim_rec, true));
                        return false;
                    }
                }
            }


            /*
             * RICERCA ED EVENTUALE INSERIMENTO DI DITTA E LICENZA
             */


            /*
             * ricerca ditta - eventuale inserimento
             */
            $anaditta_rec = array();
            if ($fiscaleIndividuale != "") {
                $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '" . addslashes($fiscaleIndividuale) . "' OR PIVA = '" . addslashes($fiscaleIndividuale) . "')";
                $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            }
            if (!$anaditta_rec) {
                if ($cfDichiarante != '') {
                    $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '" . addslashes($cfDichiarante) . "' OR PIVA = '" . addslashes($cfDichiarante) . "')";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                }
            }
            if (!$anaditta_rec) {
                if ($fiscaleLegale != "") {
                    $sql = "SELECT 
                        ANADITTA.* 
                    FROM 
                        ANADITTA 
                     JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                     WHERE 
                        DITTESOGG.CF = '" . addslashes($fiscaleLegale) . "'";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE (CODICEFISCALE = '" . $fiscaleLegale . "' OR PIVA = '" . $fiscaleLegale . "')";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
            }
            if (!$anaditta_rec) {
                if ($PivaLegale != "") {
                    $sql = "SELECT 
                        ANADITTA.* 
                    FROM 
                        ANADITTA 
                     JOIN DITTESOGG ON ANADITTA.CODICE=DITTESOGG.CODICE
                     WHERE 
                        DITTESOGG.PIVA = '" . addslashes($PivaLegale) . "'";
                    $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    if (!$anaditta_rec) {
                        $sql = "SELECT * FROM ANADITTA WHERE (PIVA = '" . addslashes($PivaLegale) . "' OR CODICEFISCALE = '" . addslashes($PivaLegale) . "')";
                        $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
                    }
                }
            }
            $Parametri = $this->devLib->getEnv_config('GAFIERE', 'codice', 'GFMBLOCCAANAGDAPRA', false);
            $bloccaIns = $Parametri['CONFIG'];
            //Se non trovato e se non bloccato lo inserisco
            if (!$anaditta_rec && !$bloccaIns) {
                $anaditta_rec = array();
                $max_codice_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT MAX(CODICE) AS MASSIMO FROM ANADITTA", false);
                $anaditta_rec['CODICE'] = $max_codice_rec['MASSIMO'] + 1;
                if ($impIndividuale && ($cognome != '' || $nome != '')) {
                    $anaditta_rec['DENOMINAZIONE'] = $cognome . " " . $nome;
                } else {
                    if ($ragSoc != '') {
                        $anaditta_rec['DENOMINAZIONE'] = $ragSoc;
                    } else {
                        $anaditta_rec['DENOMINAZIONE'] = $cognome . " " . $nome;
                    }
                }
                $anaditta_rec['SESSO'] = $sesso;
                $anaditta_rec['DATANASCITA'] = $dataNascita;
                $anaditta_rec['COMUNENASCITA'] = $comuneNascita;
                $anaditta_rec['COMUNE'] = $comuneSede;
                $anaditta_rec['INDIRIZZO'] = $indirizzoSede;
                $anaditta_rec['NUMEROCIVICO'] = $civicoSede;
                $anaditta_rec['CAP'] = $capSede;
                $anaditta_rec['PROVINCIA'] = $provinciaSede;
                if ($impIndividuale && $fiscaleIndividuale != '') {
                    $anaditta_rec['CODICEFISCALE'] = $fiscaleIndividuale;
                } else {
                    if ($fiscaleLegale != '') {
                        if (strlen($fiscaleLegale) == 16) {
                            $anaditta_rec['CODICEFISCALE'] = $fiscaleLegale;
                        } else {
                            $anaditta_rec['PIVA'] = $fiscaleLegale;
                        }
                    } else {
                        if (strlen($fiscaleIndividuale) == 16) {
                            $anaditta_rec['CODICEFISCALE'] = $fiscaleIndividuale;
                        } else {
                            $anaditta_rec['PIVA'] = $fiscaleIndividuale;
                        }
                    }
                }
                if ($PivaLegale != '') {
                    $anaditta_rec['PIVA'] = $PivaLegale;
                }
                $anaditta_rec['TELEFONO'] = $telefonoSede;
                $anaditta_rec['EMAIL'] = $pecIndividuale != '' ? $pecIndividuale : $pec;
                //$this->eqAudit->logEqEvent($this, array('Operazione' => "SCARICO FOTOGRAMMI SERV:" . $servizio_rec['CODICESERV'] . DIRECTORY_SEPARATOR . $servizio_rec['ANNOSERV']));
                $insert_Info = 'Oggetto: Inserimento Nuova Ditta Codice ' . $anaditta_rec['CODICE'];
                if (!$this->insertRecord($GAFIERE_DB, 'ANADITTA', $anaditta_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento su ANADITTE per la ditta codice " . $anaditta_rec['CODICE']);
                    return false;
                }
                //$anaditta_rec = $this->gfmLib->GetAnaditta($anaditta_rec['CODICE']);
                $anaditta_rec = ItaDB::DBSQLSelect($GAFIERE_DB, "SELECT * FROM ANADITTA WHERE CODICE = " . $anaditta_rec['CODICE'], false);

                //Se c'è il legale rappresentante inseriesco anche il soggetto
                if ($Legale == true) {
                    $dittesogg_rec = array();
                    $dittesogg_rec['CODICE'] = $anaditta_rec['CODICE'];
                    $dittesogg_rec['RUOCOD'] = "0014";
                    $dittesogg_rec['NOMINATIVO'] = $cognome . " " . $nome;
                    $dittesogg_rec['DATANASCITA'] = $dataNascita;
                    $dittesogg_rec['COMUNENASCITA'] = $comuneNascita;
                    $dittesogg_rec['INDIRIZZO'] = $indirizzoResidenza;
                    $dittesogg_rec['CIVICO'] = $civicoResidenza;
                    $dittesogg_rec['COMUNE'] = $comuneResidenza;
                    $dittesogg_rec['CAP'] = $capResidenza;
                    $dittesogg_rec['PROVINCIA'] = $provinciaResidenza;
                    $dittesogg_rec['TELEFONO'] = $telefonoSede;
                    $dittesogg_rec['CF'] = $cfDichiarante;
                    $dittesogg_rec['PIVA'] = $PivaLegale;
                    $insert_Info = 'Oggetto: Inserimento Nuovo Soggetto ditta ' . $anaditta_rec['CODICE'] . " - cf $cfDichiarante";
                    if (!$this->insertRecord($GAFIERE_DB, 'DITTESOGG', $dittesogg_rec, $insert_Info)) {
                        $this->setErrMessage("Errore in inserimento Nuovo Soggetto ditta " . $anaditta_rec['CODICE'] . " - cf $cfDichiarante");
                        return false;
                    }
                }
            }
            //se non ho ancora la ditta restituisco messaggio di errore
            if (!$anaditta_rec) {
                $this->setErrMessage("Ditta non trovata o inserimento impossibile. Procedura interrotta.");
                return false;
            }

            /*
             * ricerca licenza - eventuale inserimento
             */
            $dittelic_rec = array();
            //non considero il tipo perchè potrebbero esserci incongruenze - quasi impossibile che qualcuno abbia due licenze con stesso numero e tipo diverso
            if ($numeroLic == "") {
                $this->setErrMessage("4 - Numero licenza non specificato");
                return false;
            }
            $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND NUMERO = '" . addslashes($numeroLic) . "'";
            $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if (!$dittelic_rec) {
                //provo a cercare un numero con l'anno
                $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND NUMERO LIKE '" . addslashes($numeroLic) . "/%'";
                $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            }
            if (!$dittelic_rec) {
                $tipoLic = "B";
                //inserisco la licenza
                $dittelic_rec['CODICE'] = $anaditta_rec['CODICE'];
                $dittelic_rec['TIPOAUTORIZZAZIONE'] = $tipoLic;
                $dittelic_rec['NUMERO'] = $numeroLic;
                $dittelic_rec['DATARILASCIO'] = $dittelic_rec['DATARILASCIOLICENZA'] = $rilascioLic;
                $dittelic_rec['COMUNE'] = $comuneLic;
                if ($alimentare && !$nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "ALIMENTARE";
                }
                if (!$alimentare && $nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "NON ALIMENTARE";
                }
                if ($alimentare && $nonAlimentare) {
                    $dittelic_rec['SETTOREMERCEOLOGICO'] = "MISTO";
                }
                $dittelic_rec['NUMEROISCRREGD'] = $numRegImprese;
                $dittelic_rec['DATAISCRLICENZA'] = $dataRegImprese;
                $dittelic_rec['DATAISCRIZIONEREGD'] = $dataRegImprese;
                $dittelic_rec['CCIAA'] = $comuneCCIAA;
                $insert_Info = 'Oggetto: Inserimento Nuova licenza ditta ' . $anaditta_rec['CODICE'] . " - lic $numeroLic";
                if (!$this->insertRecord($GAFIERE_DB, 'DITTELIC', $dittelic_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento Nuova licenza ditta " . $anaditta_rec['CODICE'] . " - lic $numeroLic");
                    return false;
                }
                //rileggo il record appena inserito
                $sql = "SELECT * FROM DITTELIC WHERE CODICE = " . $anaditta_rec['CODICE'] . " AND TIPOAUTORIZZAZIONE = '" . $dittelic_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . addslashes($dittelic_rec['NUMERO']) . "'";
                $dittelic_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);

                //se non esiste la licenza provo ad inserire le presenze lette dal modello
                if ($presenze_fiera > 0) {
                    $dittepre_rec = array();
                    $dittepre_rec['CODICE'] = $anaditta_rec['CODICE'];
                    $dittepre_rec['TIPOFIERA'] = $bandimSel_rec['FIERA'];
                    $dittepre_rec['TIPOAUTORIZZAZIONE'] = $dittelic_rec['TIPOAUTORIZZAZIONE'];
                    $dittepre_rec['NUMERO'] = $dittelic_rec['NUMERO'];
                    $year_prev = (int) date('Y') - 1;
                    $dittepre_rec['DATA'] = $year_prev . "1231"; //metto la data al 31/12 dell'anno precedente
                    $dittepre_rec['NUMEROPRESENZE'] = $presenze_fiera;
                    $dittepre_rec['CODICEPRESENZA'] = "P";
                    $dittepre_rec['TIPOLOGIAPRESENZA'] = "P";
                    $insert_Info = 'Inserimento Presenze Nuova licenza ditta ' . $anaditta_rec['CODICE'] . " - lic $numeroLic";
                    if (!$this->insertRecord($GAFIERE_DB, 'DITTEPRE', $dittepre_rec, $insert_Info)) {
                        $this->setErrMessage("Errore in inserimento DITTEPRE ditta " . $anaditta_rec['CODICE'] . " - lic $numeroLic");
                        return false;
                    }
                }
            }
            if ($dittelic_rec['DATAINIZIOATTIVITALIC'] == '') {
                $dittelic_rec['DATAINIZIOATTIVITALIC'] = $rilascioLic;
            }
            if ($dittelic_rec['DATAINIZIOATTIVITA'] == '') {
                $dittelic_rec['DATAINIZIOATTIVITA'] = $rilascioLic;
            }
            if (!$dittelic_rec) {
                $this->setErrMessage("Licenza non trovata o inserimento impossibile. Procedura interrotta!");
                return false;
            }
            /*
             * FINE RICERCA/INSERIMENTO DITTE E LICENZE
             */

            $mercacom_rec = array();
            $mercacom_rec['FIERA'] = $bandim_rec['FIERA'];
//            $mercacom_rec['FIERA'] = $Anamerc_rec['FIERA'];
            $mercacom_rec['DATA'] = $bandim_rec['DATA'];
            $mercacom_rec['ASSEGNAZIONE'] = $bandim_rec['ASSEGNAZIONE'];
            $mercacom_rec['TIPOATTIVITA'] = $anaditta_rec['TIPOATTIVITA'];
            $mercacom_rec['CODICE'] = $anaditta_rec['CODICE'];
            $mercacom_rec['TIPOAUTORIZZAZIONE'] = $dittelic_rec['TIPOAUTORIZZAZIONE'];
            $mercacom_rec['NUMERO'] = $dittelic_rec['NUMERO'];
            $mercacom_rec['DENOMINAZIONE'] = $anaditta_rec['DENOMINAZIONE'];
            $mercacom_rec['DATADOMANDA'] = $proric_rec['RICDAT'];
            $mercacom_rec['TIPONS'] = "NORMALE";
            $mercacom_rec['PEC'] = $pec;
            if ($prodag_rec_posto) {
                $mercacom_rec['POSTO'] = $prodag_rec_posto['DAGVAL'];
                //cerco se c'è in anagrafica della fiera un soplo posteggio con quel numero
                $sql = "SELECT * FROM MERCAPOS WHERE TIPO = '" . $bandim_rec['FIERA'] . "' AND POSTO = '" . addslashes($mercacom_rec['POSTO']) . "'";
                $fierepos_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, true);
                if ($fierepos_rec) {
                    if (count($fierepos_rec) == 1) {
                        $mercacom_rec['POSTO'] = $fierepos_rec[0]['POSTO'];
                        $mercacom_rec['CODICEVIA'] = $fierepos_rec[0]['CODICEVIA'];
                        $mercacom_rec['LETTERA'] = $fierepos_rec[0]['LETTERA'];
                    }
                }
            } else {
                //cerco se c'è in anagrafica della fiera
                $sql = "SELECT * FROM MERCAPOS WHERE TIPO = '" . $bandim_rec['FIERA'] . "' AND CODICEDITTA = " . $anaditta_rec['CODICE'];
                $Mercapos_tab = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, true);
                if ($Mercapos_tab) {
                    if (count($Mercapos_tab) == 1) {
                        $mercacom_rec['POSTO'] = $Mercapos_tab[0]['POSTO'];
                        $mercacom_rec['CODICEVIA'] = $Mercapos_tab[0]['CODICEVIA'];
                        $mercacom_rec['LETTERA'] = $Mercapos_tab[0]['LETTERA'];
                    }
                }
            }
            if ($prodag_rec_pi_via && $prodag_rec_pi_via != '') {
                $sql = "SELECT * FROM ANA_COMUNE WHERE ANACAT = 'VIE' AND " . $ITALWEB_DB->strUpper('ANADES') . " = '%" . addslashes($prodag_rec_pi_via) . "%'";
                $vie_rec = ItaDB::DBSQLSelect($ITALWEB_DB, $sql, true);
                if ($vie_rec) {
                    $codicevia = $vie_rec['ANACOD'];
                    //cerco in anagrafica di quel mercato se è il solo posteggio presente per quella via
                    $sql = "SELECT * FROM MERCAPOS WHERE TIPO = '" . addslashes($mercacom_rec['FIERA']) . "' AND CODICEVIA = '$codicevia'";
                    $Mercapos_tab = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, true);
                    if (count($Mercapos_tab) == 1) {
                        $mercacom_rec['POSTO'] = $Mercapos_tab[0]['POSTO'];
                        $mercacom_rec['CODICEVIA'] = $Mercapos_tab[0]['CODICEVIA'];
                        $mercacom_rec['LETTERA'] = $Mercapos_tab[0]['LETTERA'];
                    }
                }
            }

            //$mercacom_rec['IDFASCICOLO'] = $proges_rec['ROWID'];
            //verifico che la domanda non sia già presente
            $sql = "SELECT * FROM MERCACOM WHERE FIERA = '" . $mercacom_rec['FIERA'] . "' AND DATA = '" . $mercacom_rec['DATA'] . "' AND CODICE = " . $mercacom_rec['CODICE'] . ""
                    . " AND TIPOAUTORIZZAZIONE = '" . $mercacom_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $mercacom_rec['NUMERO'] . "'";
            $sql .= " AND CODICEVIA = '" . $mercacom_rec['CODICEVIA'] . "'";
            $check_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if ($check_rec) {
                $mercacom_rec = $check_rec;
                $this->setErrMessage("Domanda per il mercato " . $mercacom_rec['FIERA'] . " già presente! Procedura interrotta.");
                return false;
            }
            $mercacom_rec['DECENNALE'] = $bandimSel_rec['DECENNALE'];
            $mercacom_rec['TIPOATTIVITA'] = $bandimSel_rec['TIPOATTIVITA'];

            //calcolo dei punteggi - caso fiera decennale
            if ($bandimSel_rec['DECENNALE']) {
                if ($check_concessione || $check_subingresso) {
                    $mercacom_rec['PUNTEGGIO2'] = 40;
                }
            } else {
                if ($check_graduatoria) {
                    $mercacom_rec['PUNTEGGIO2'] = 40;
                }
            }
            if (!$punti_anzianita) {
                $punti_anzianita = 0;
                //calcolo anni da $dataRegImprese
                include_once ITA_BASE_PATH . '/lib/itaPHPCore/itaDate.class.php';
                /* @var $itaDate itaDate */
                $itaDate = new itaDate();
                $anni = $itaDate->dateDiffYears(date('Ymd'), $dataRegImprese);
                if ($anni <= 5) {
                    $punti_anzianita = 40;
                }
                if ($anni > 5 && $anni <= 10) {
                    $punti_anzianita = 50;
                }
                if ($anni > 10) {
                    $punti_anzianita = 60;
                }
            }

            $mercacom_rec['PUNTEGGIO1'] = $punti_anzianita;
            $mercacom_rec['PUNTEGGIOTOTALE'] = $mercacom_rec['PUNTEGGIO1'] + $mercacom_rec['PUNTEGGIO2'];

            $insert_Info = "Oggetto: Inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $bandim_rec['FIERA'] . " - data: " . $bandim_rec['DATA'];
            try {
                ItaDB::DBInsert($GAFIERE_DB, "MERCACOM", "ROWID", $mercacom_rec);
            } catch (Exception $exc) {
                $this->setErrMessage("Errore in inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $bandim_rec['FIERA'] . " - data: " . $bandim_rec['DATA'] . "<br><br>" . $exc->getMessage());
                return false;
            }
//            if (!$this->insertRecord($GAFIERE_DB, 'MERCACOM', $mercacom_rec, $insert_Info)) {
//                $this->setErrMessage("Errore in inserimento nuova domanda ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $bandim_rec['FIERA'] . " - data: " . $bandim_rec['DATA']);
//                return false;
//            }
            //rileggo il record di mercacom
            $sql = "SELECT * FROM MERCACOM WHERE FIERA = '" . $mercacom_rec['FIERA'] . "' AND DATA = '" . $mercacom_rec['DATA'] . "' AND CODICE = " . $mercacom_rec['CODICE'] . ""
                    . " AND TIPOAUTORIZZAZIONE = '" . $mercacom_rec['TIPOAUTORIZZAZIONE'] . "' AND NUMERO = '" . $mercacom_rec['NUMERO'] . "'";
            //come condizione impongo anche il codice via
            $sql .= " AND CODICEVIA = '" . $mercacom_rec['CODICEVIA'] . "'";

            $mercacom_rec = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            if (!$mercacom_rec) {
                $this->setErrMessage("Non trovo la domanda appena inserita, procedura interrotta. Controllare!<br>" . print_r($mercacom_rec, true));
                return false;
            }
            //inserimento tabella di collegamento
            $fieresuap_rec = array();
            $fieresuap_rec['IDMERCACOM'] = $mercacom_rec['ROWID'];
            $fieresuap_rec['SUAPID'] = $proges_rec['ROWID'];
            $fieresuap_rec['SUAKEY'] = App::$utente->getKey('ditta');
            $insert_Info = "Oggetto: Inserimento collegamento suap-mercacom " . $mercacom_rec['ROWID'] . " - pratica: " . $proges_rec['ROWID'];
            if (!$this->insertRecord($GAFIERE_DB, 'FIERESUAP', $fieresuap_rec, $insert_Info)) {
                $this->setErrMessage("Errore in inserimento collegamento suap-mercacom " . $mercacom_rec['ROWID'] . " - pratica: " . $proges_rec['ROWID']);
                return false;
            }

            /*
             * Per ogni domanda aggiungo i documenti allegati in MERCADOC - prendo il rapporto completo
             */
            $model = 'praFascicolo.class';
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $praFascicolo = new praFascicolo($currGesnum);
//            $allegati = $praFascicolo->getAllegatiProtocollaPratica();
            //cambio la chiamata per ignorare se il documento è stato già protocollato
            $allegati = $praFascicolo->getAllegatiProtocollaPratica('Paleo', true, false, array(), true);
            if ($allegati['Principale']['Stream']) {
                //dato che ho il base64 posso creare il file dentro la directory delle domande
                $randName = md5(rand() * time()) . ".pdf.p7m";
                if (!$Anamerc_rec['ENTEFIERE'] || $Anamerc_rec['ENTEFIERE'] == App::$utente->getKey('ditta')) {
                    $doc_path = $this->gfmLib->SetDirectoryGafiere($mercacom_rec['FIERA'], "MERCADOC");
                } else {
                    $doc_path = $this->gfmLib->SetDirectoryGafiere($mercacom_rec['FIERA'], "MERCADOC", true, $Anamerc_rec['ENTEFIERE']);
                }
                $mercadoc_rec = array();
                $mercadoc_rec['DOCUMENTO'] = $allegati['Principale']['Nome'];
                $mercadoc_rec['NOTE'] = $allegati['Principale']['Descrizione'];
                $mercadoc_rec['DATAPRESENTAZIONE'] = $proric_rec['RICDAT'];
                $mercadoc_rec['ID_DOMANDA'] = $mercacom_rec['ROWID'];
                $mercadoc_rec['NECESSARIO'] = "N";
                $mercadoc_rec['FILE'] = $randName;
//                $pathDest = $doc_path . DIRECTORY_SEPARATOR . $randName;
                file_put_contents($doc_path . DIRECTORY_SEPARATOR . $randName, $allegati['Principale']['Stream']);
                $insert_Info = "Oggetto: Inserimento nuovo documento ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $bandim_rec['FIERA'] . " - data: " . $bandim_rec['DATA'];
                if (!$this->insertRecord($GAFIERE_DB, 'MERCADOC', $mercadoc_rec, $insert_Info)) {
                    $this->setErrMessage("Errore in inserimento nuovo documento ditta " . $anaditta_rec['CODICE'] . " - fiera: " . $bandim_rec['FIERA'] . " - data: " . $bandim_rec['DATA']);
                    return false;
                }
            }
        }

        return true;
    }

    public function getAllegatoPrincipaleAltroSUAP($dittaSUAP, $proges_rec) {
        try {
            $PRAM_DB = ItaDB::DBOpen('PRAM', $dittaSUAP);
        } catch (Exception $exc) {
            $this->setErrMessage("Errore apertura database PRAM: " . $exc->getMessage());
            return false;
        }
        $passi_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM='" . $proges_rec['GESNUM'] . "' AND PROPUB=1 ORDER BY PROSEQ", true);

        //$passi_tab = $this->praLib->GetPropas($this->codicePratica, 'codice', true);
        $passi_tab = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM='" . $proges_rec['GESNUM'] . "' AND PROPUB=1 ORDER BY PROSEQ", true);
        $sqlDoc = "SELECT * FROM PASDOC WHERE PASKEY = '" . $proges_rec['GESNUM'] . "'";
        $pasdoc_tab = ItaDB::DBSQLSelect($PRAM_DB, $sqlDoc, true);
        //
        $arrayfile = array();
        $flag_principale = false;
        $flag_Comunica = false;
        $flag_scegli = false;
        $arrayDoc = array();
        //se c'è il numero di richiesta on line, cerco il documento che è il rapporto completo come documento principale
        if ($proges_rec['GESPRA'] != '') {
            $pratPath = $this->praLib->SetDirectoryPratiche(substr($proges_rec['GESNUM'], 0, 4), $proges_rec['GESNUM'], 'PROGES', false, $dittaSUAP);
            $arrayfile = $this->elencaFiles($pratPath);
            //scansione di pas_doc per cercare 'INFOCAMERE' dentro PASCLA
            $index = 0;
            foreach ($pasdoc_tab as $pasdoc_rec) {
                if (stristr($pasdoc_rec['PASCLA'], 'INFOCAMERE') !== false) {
                    $flag_Comunica = true;
                    $sorgFile = $pratPath . DIRECTORY_SEPARATOR . $pasdoc_rec['PASFIL'];
                    if (!file_exists($sorgFile)) {
                        continue;
                    }
                    $infoFile = pathinfo($sorgFile);
                    if ($infoFile['extension'] == 'info') {
                        continue;
                    }
                    $fp = @fopen($sorgFile, "rb", 0);
                    if ($fp) {
                        $rowid_rec = array('ROWID' => $pasdoc_rec['ROWID']);
//                        $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                        $binFile = fread($fp, filesize($sorgFile));
                        $base64File = base64_encode($binFile);
                        //l'estensione SUAP.PDF.P7M indica un allegato per Comunica.
                        if (stristr($pasdoc_rec['PASNAME'], 'SUAP.PDF.P7M') !== false) {
                            $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                            $flag_principale = true;
                            $arrayDoc['Principale']['Nome'] = $pasdoc_rec['PASNAME'];
                            $arrayDoc['Principale']['Stream'] = $base64File;
                            $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $pasdoc_rec['PASCLA'] . ' - ' . $pasdoc_rec['PASNOT'];
                            $arrayDoc['Principale']['ROWID'] = $pasdoc_rec['ROWID'];
                        }
                    } else {
                        $this->message = "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile;
                        return false;
                    }
                }
                fclose($fp);
            }

            if (!$flag_Comunica) {
                //pratica da sportello on line, ma non da Comunica, cerco il rapporto completo dentro i passi
                $arrayDoc = array(); //svuoto l'array perchè ci potrebbero essere allegati caricati
                $index = 0;
                foreach ($passi_tab as $passo) {
                    //scansione dei passi
                    $pramPath = $this->praLib->SetDirectoryPratiche(substr($passo['PROPAK'], 0, 4), $passo['PROPAK'], 'PASSO', false, $dittaSUAP);
                    $lista = $this->elencaFiles($pramPath);
                    //scorro i file dentro il passo
                    foreach ($lista as $fileName) {
                        $sorgFile = $pramPath . DIRECTORY_SEPARATOR . $fileName;
                        //query su pasdoc per recuperare il nome originale del file
                        $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $passo['PROPAK'] . "' AND PASFIL = '" . $fileName . "'";
                        $file_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
                        if (!file_exists($sorgFile)) {
                            continue;
                        }
                        $infoFile = pathinfo($sorgFile);
                        if ($infoFile['extension'] != 'p7m' && $infoFile['extension'] != 'P7M') {
                            continue;
                        }
                        $fp = @fopen($sorgFile, "rb", 0);
                        if ($fp) {
                            $rowid_rec = array('ROWID' => $file_rec['ROWID']);
//                            $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                            $binFile = fread($fp, filesize($sorgFile));
                            $base64File = base64_encode($binFile);
                            if ($flag_scegli) {
                                $arrayDoc['pasdoc_rec'][] = $rowid_rec;
                                $flag_scegli = false;
                                $flag_principale = true;
                                $arrayDoc['Principale']['Nome'] = $file_rec['PASNAME'];
                                $arrayDoc['Principale']['Stream'] = $base64File;
                                $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
                                $arrayDoc['Principale']['ROWID'] = $file_rec['ROWID'];
                            }
                        } else {
                            $this->message = "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile;
                            //Out::msgStop("Errore", "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile . "");
                            break;
                        }
                        fclose($fp);
                    }
                    if ($passo['PRODRR'] != 0) {
                        $flag_scegli = true;
                    }
                }
            }
        }


//        if ($proges_rec['GESPRA'] != '') {
//            $pratPath = $this->praLib->SetDirectoryPratiche(substr($proges_rec['GESNUM'], 0, 4), $proges_rec['GESNUM'], 'PROGES', false, $dittaSUAP);
//            $arrayfile = $this->elencaFiles($pratPath);
//            //scansione di pas_doc per cercare 'INFOCAMERE' dentro PASCLA
//            $index = 0;
//            //pratica da sportello on line, ma non da Comunica, cerco il rapporto completo dentro i passi
//            $arrayDoc = array(); //svuoto l'array perchè ci potrebbero essere allegati caricati
//            $index = 0;
//            foreach ($passi_tab as $passo) {
//                //scansione dei passi
//                $pramPath = $this->praLib->SetDirectoryPratiche(substr($passo['PROPAK'], 0, 4), $passo['PROPAK'], 'PASSO', false, $dittaSUAP);
//                $lista = $this->elencaFiles($pramPath);
//                //scorro i file dentro il passo
//                foreach ($lista as $fileName) {
//                    $sorgFile = $pramPath . DIRECTORY_SEPARATOR . $fileName;
//                    //query su pasdoc per recuperare il nome originale del file
//                    $sql = "SELECT * FROM PASDOC WHERE PASKEY = '" . $passo['PROPAK'] . "' AND PASFIL = '" . $fileName . "'";
//                    $file_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
//                    if ($file_rec['PASPRTCLASS'] != '') {
//                        continue;
//                    }
//                    if (!file_exists($sorgFile)) {
//                        continue;
//                    }
//                    $infoFile = pathinfo($sorgFile);
//                    if ($infoFile['extension'] != 'p7m' && $infoFile['extension'] != 'P7M') {
//                        continue;
//                    }
//                    $fp = @fopen($sorgFile, "rb", 0);
//                    if ($fp) {
//                        $rowid_rec = array('ROWID' => $file_rec['ROWID']);
////                            $arrayDoc['pasdoc_rec'][] = $rowid_rec;
//                        $binFile = fread($fp, filesize($sorgFile));
//                        $base64File = base64_encode($binFile);
//                        if ($flag_scegli) {
//                            $arrayDoc['pasdoc_rec'][] = $rowid_rec;
//                            $flag_scegli = false;
//                            $flag_principale = true;
//                            $arrayDoc['Principale']['Nome'] = $file_rec['PASNAME'];
//                            $arrayDoc['Principale']['Stream'] = $base64File;
//                            $arrayDoc['Principale']['Descrizione'] = 'Documento principale ' . $file_rec['PASCLA'] . ' - ' . $file_rec['PASNOT'];
//                            $arrayDoc['Principale']['ROWID'] = $file_rec['ROWID'];
//                        }
//                    } else {
//                        $this->setErrMessage("Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile);
//                        //Out::msgStop("Errore", "Errore in fase di trasferimento file. Procedura interrotta<br>" . $sorgFile . "");
//                        break;
//                    }
//                    fclose($fp);
//                }
//                if ($passo['PRODRR'] != 0) {
//                    $flag_scegli = true;
//                }
//            }
//        }
        //
        //se non ho trovato un documento principale non seleziono nessun documento da allegare
        //        
        if (!$flag_principale) {
            $arrayDoc = array();
        }
        return $arrayDoc;
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

    private function getLicenza($codice, $numero) {
        if (!$codice) {
            return false;
        }
        if (!$numero) {
            return false;
        }
        try {
            $GAFIERE_DB = ItaDB::DBOpen('GAFIERE');
        } catch (Exception $exc) {
            $this->setErrMessage('Errore apertura DB GAFIERE: ' . $exc->getMessage());
            return false;
        }
        $sql = "SELECT * FROM DITTELIC  WHERE NONATTIVA = 0 AND CODICE = '" . $codice . "' AND NUMERO = '" . addslashes($numero) . "'";
        $ditteLic = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
        if (!$ditteLic) {
            $sql = "SELECT * FROM DITTELIC  WHERE NONATTIVA = 0 AND CODICE = '" . $codice . "' AND NUMERO LIKE '" . addslashes($numero) . "/%'";
            $ditteLic = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
        }
        if (!$ditteLic) {
            if (strpos($numero, "/") !== false) {
                list($num, $anno) = explode("/", $numero);
                $sql = "SELECT * FROM DITTELIC  WHERE NONATTIVA = 0 AND CODICE = '" . $codice . "' AND NUMERO = '" . addslashes($num) . "'";
                $ditteLic = ItaDB::DBSQLSelect($GAFIERE_DB, $sql, false);
            }
        }
        return $ditteLic;
    }

}
