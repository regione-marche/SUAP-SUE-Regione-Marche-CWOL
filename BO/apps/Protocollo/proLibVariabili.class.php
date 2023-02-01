<?php

/**
 *
 * GESTIONE VARIABILI APPLICATIVO PROTOCOLLO
 *
 * PHP Version 5
 *
 * @category
 * @package    Protocollo
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    18.09.2013
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once (ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';

class proLibVariabili {

    private $variabiliSegnatura;
    private $variabiliProtocollo;
    private $variabiliAllegati;
    private $codiceProtocollo;
    private $tipoProtocollo;
    private $anapro_rec;
    private $codicePratica;
    private $chiavePasso;
    private $registroGenerale;
    private $registroDocFormali;
    private $frontOfficeFlag = false;
    private $pronumAllegati;
    private $proparAllegati;

    public function getCodiceProtocollo() {
        return $this->codiceProtocollo;
    }

    public function setCodiceProtocollo($codiceProtocollo) {
        $this->codiceProtocollo = $codiceProtocollo;
    }

    public function getTipoProtocollo() {
        return $this->tipoProtocollo;
    }

    public function setTipoProtocollo($tipoProtocollo) {
        $this->tipoProtocollo = $tipoProtocollo;
    }

    public function getAnapro_rec() {
        return $this->anapro_rec;
    }

    public function setAnapro_rec($anapro_rec) {
        $this->anapro_rec = $anapro_rec;
    }

    public function setFrontOfficeFlag($frontOfficeFlag) {
        $this->frontOfficeFlag = $frontOfficeFlag;
    }

    /**
     * Assegna il codice pratica per il calcolo variabili
     * @param type $codicePratica
     */
    public function setCodicePratica($codicePratica) {
        $this->codicePratica = $codicePratica;
    }

    /**
     * Assegna la chiave passo per il calcolo delle variabili
     * @param type $chiavePasso
     */
    public function setChiavePasso($chiavePasso) {
        $this->chiavePasso = $chiavePasso;
    }

    /**
     * Ritorna una legenda di campi per le pratiche on-line in formato adjacency per
     * I campi non sono riferiti a procedimento o pratica
     * @param type $tipo
     * @param type $markup
     * @return type
     */
    public function getLegendaSegnatura($tipo = "adjacency", $markup = 'smarty') {
        return $this->getVariabiliSegnatura()->exportAdjacencyModel($markup);
    }

    public function getVariabiliSegnatura() {
        $this->variabiliSegnatura = new itaDictionary();

        $i = 1;
        //
        // Variabili Sportello on Line
        //
        $this->variabiliSegnatura->addField('ANNO', 'Prefisso anno protocollo', $i++, 'base');
        $this->variabiliSegnatura->addField('NUMERO', 'Numero Protocollo', $i++, 'base');
        $this->variabiliSegnatura->addField('NUMEROSTR', 'Numero Protocollo Stringa', $i++, 'base');
        $this->variabiliSegnatura->addField('REGISTRO', 'Codice Registro', $i++, 'base');
        $this->variabiliSegnatura->addField('TIPO', 'Tipo protocollo', $i++, 'base');
        $this->variabiliSegnatura->addField('REG_DATA', 'Data registrazione (gg/mm/aaaa)', $i++, 'base');
        $this->variabiliSegnatura->addField('REG_GIORNO', 'Giorno registrazione', $i++, 'base');
        $this->variabiliSegnatura->addField('REG_MESE', 'Mese registrazione', $i++, 'base');
        $this->variabiliSegnatura->addField('REG_ANNO', 'Anno registrazione', $i++, 'base');
        $this->variabiliSegnatura->addField('CLASSIFICAZIONE', 'Classificazione', $i++, 'base');
        $this->variabiliSegnatura->addField('FASCICOLO', 'Fascicolo', $i++, 'base');
        $this->variabiliSegnatura->addField('REG_UFFICIO', 'Ufficio Inserimento', $i++, 'base');
        $this->variabiliSegnatura->addField('AMM_CODICE', 'Codice Amministrazione', $i++, 'base');
        $this->variabiliSegnatura->addField('AMM_AOO', 'Area Organizzativa Omogenea', $i++, 'base');
        $this->variabiliSegnatura->addField('AMM_UOR', 'Uor', $i++, 'base');
        // Variabili Copia Analogica.
        $this->variabiliSegnatura->addField('CA_LOGNAME', 'Logname Utente - Copia Analogica', $i++, 'base');
        $this->variabiliSegnatura->addField('CA_UTENTE', 'Utente - Copia Analogica', $i++, 'base');
        $this->variabiliSegnatura->addField('CA_DATA', 'Data Copia Analogica', $i++, 'base');
        $this->variabiliSegnatura->addField('CA_ORA', 'Ora Copia Analogica', $i++, 'base');
        $this->variabiliSegnatura->addField('IRIS', 'Info Prot. Riservato', $i++, 'base');

        $proLib = new proLib();
        if ($this->anapro_rec) {
            include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
            $anapro_rec = $this->anapro_rec;
            if ($anapro_rec) {
                $this->variabiliSegnatura->addFieldData('ANNO', substr($anapro_rec['PRONUM'], 0, 4));
                $this->variabiliSegnatura->addFieldData('NUMERO', (int) substr($anapro_rec['PRONUM'], 4));
                $this->variabiliSegnatura->addFieldData('NUMEROSTR', str_pad(substr($anapro_rec['PRONUM'], 4), 7, '0', STR_PAD_LEFT));
                if ($anapro_rec['PROPAR'] == 'A' || $anapro_rec['PROPAR'] == 'P') {
                    $this->variabiliSegnatura->addFieldData('REGISTRO', $proLib->GetCodiceRegistroProtocollo());
                } elseif ($anapro_rec['PROPAR'] == 'C') {
                    $this->variabiliSegnatura->addFieldData('REGISTRO', $proLib->GetCodiceRegistroDocFormali());
                } else {
                    $this->variabiliSegnatura->addFieldData('REGISTRO', 'I');
                }
                $this->variabiliSegnatura->addFieldData('TIPO', $anapro_rec['PROPAR']);
                $this->variabiliSegnatura->addFieldData('REG_DATA', substr($anapro_rec['PRODAR'], 6) . "/" . substr($anapro_rec['PRODAR'], 4, 2) . "/" . substr($anapro_rec['PRODAR'], 0, 4));
                $this->variabiliSegnatura->addFieldData('REG_GIORNO', substr($anapro_rec['PRODAR'], 6));
                $this->variabiliSegnatura->addFieldData('REG_MESE', substr($anapro_rec['PRODAR'], 4, 2));
                $this->variabiliSegnatura->addFieldData('REG_ANNO', substr($anapro_rec['PRODAR'], 0, 4));
                $this->variabiliSegnatura->addFieldData('CLASSIFICAZIONE', $anapro_rec['PROCCF']);
                $this->variabiliSegnatura->addFieldData('FASCICOLO', $anapro_rec['PROARG']);
                $this->variabiliSegnatura->addFieldData('REG_UFFICIO', $anapro_rec['PROUOF']);

                $anaent_rec = $proLib->GetAnaent('26');
                $this->variabiliSegnatura->addFielddata('AMM_CODICE', $anaent_rec['ENTDE1']);
                $this->variabiliSegnatura->addFielddata('AMM_AOO', $anaent_rec['ENTDE2']);
                $this->variabiliSegnatura->addFielddata('AMM_UOR', $anaent_rec['ENTDE3']);
                $this->variabiliSegnatura->addFielddata('IRIS', '');
                if ($anapro_rec['PRORISERVA']) {
                    $this->variabiliSegnatura->addFielddata('IRIS', 'RIS');
                }
            }
        }

        /* Valorizzazione Copia Analogica. */
        $this->variabiliSegnatura->addFielddata('CA_LOGNAME', App::$utente->getKey('nomeUtente'));
        $codiceUtente = proSoggetto::getCodiceSoggettoFromIdUtente();
        $Anamed_rec = $proLib->GetAnamed($codiceUtente, 'codice');
        $this->variabiliSegnatura->addFielddata('CA_UTENTE', $Anamed_rec['MEDNOM']);
        $this->variabiliSegnatura->addFielddata('CA_DATA', date('d/m/Y'));
        $this->variabiliSegnatura->addFielddata('CA_ORA', date('H:i:s'));
        $this->variabiliSegnatura->addFielddata('NUM_DOCUMENTO', '');
        $this->variabiliSegnatura->addFielddata('COD_DOCUMENTO', '');
        if ($anapro_rec['PROPAR'] == 'I') {
            include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
            $segLib = new segLib();
            $Indice_rec = $segLib->GetIndice($anapro_rec['PRONUM'], 'anapro', false, $anapro_rec['PROPAR']);
            $this->variabiliSegnatura->addFielddata('NUM_DOCUMENTO', substr($Indice_rec['IDELIB'], 2));
            $this->variabiliSegnatura->addFielddata('COD_DOCUMENTO', $Indice_rec['IDELIB']);
        }

        return $this->variabiliSegnatura;
    }

    public function getVariabiliPratica($all = false) {
        $this->variabiliPratica = new itaDictionary();
        if ($this->codicePratica) {
            $this->variabiliPratica->addField('PRABASE', 'Variabili Base Pratica', 1, 'itaDictionary', $this->getVariabiliBasePratica());
            if ($this->frontOfficeFlag == false) {
                $this->variabiliPratica->addField('PRAPASSO', 'Variabili Passo Pratica', 2, 'itaDictionary', $this->getVariabiliAzioneFascicolo());
            }
            $ret = $this->getVariabiliCampiAggiuntiviAzione();
            if ($ret) {
                $this->variabiliPratica->addField('PRAAGGIUNTIVI', 'Variabili Campi Aggiuntivi Passi', 4, 'itaDictionary', $ret);
            }
        }
        if ($this->codiceProtocollo) {
            $this->variabiliPratica->addField('PROTOCOLLO', 'Variabili Protocollo', 1, 'itaDictionary', $this->getVariabiliBaseProtocollo());
        }
        return $this->variabiliPratica;
    }

    public function getVariabiliBasePratica() {
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $proLib = new proLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $proLibPratica = new proLibPratica();
        $PROT_DB = $proLib->getPROTDB();
        $Proges_rec = $proLibPratica->GetProges($this->codicePratica);
        if ($Proges_rec) {
            $Anapra_rec = $praLib->GetAnapra($Proges_rec['GESPRO']);
            $Anaset_rec = $praLib->GetAnaset($Proges_rec['GESSTT']);
            $Anaatt_rec = $praLib->GetAnaatt($Proges_rec['GESATT']);
            $Anatip_rec = $praLib->GetAnatip($Proges_rec['GESTIP']);
            $Anatsp_rec = $praLib->GetAnatsp($Proges_rec['GESTSP']);
            $Ananom_tspres_rec = $praLib->GetAnanom($Anatsp_rec['TSPRES']);
            $Anaspa_rec = $praLib->GetAnaspa($Proges_rec['GESSPA']);
            $Ananom_spares_rec = $praLib->GetAnanom($Anaspa_rec['SPARES']);
            $Ananom_prares_rec = $praLib->GetAnanom($Proges_rec['PRAPRES']);
            $Anades_rec = $praLib->GetAnades($Proges_rec['GESNUM']);
            $i = 1;
            $dizionario = $this->getCampiFascicolo();
            $dizionario->addFieldData('GESNUM', $Proges_rec['GESNUM']);
            $dizionario->addFieldData('GESNUM_FORMATTED', substr($Proges_rec['GESNUM'], 14, 6) . "/" . substr($Proges_rec['GESNUM'], 10, 4));
            $dizionario->addFieldData('GESKEY', $Proges_rec['GESKEY']);
            $dizionario->addFieldData('GESDRI', date("d/m/Y", strtotime($Proges_rec['GESDRI'])));
            $dizionario->addFieldData('GESDRE', date("d/m/Y", strtotime($Proges_rec['GESDRE'])));
            $dizionario->addFieldData('RICNUM', $Proges_rec['GESPRA']);
            $dizionario->addFieldData('PRADES', $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . $Anapra_rec['PRADES__4']);
            $dizionario->addFieldData('TIPDES', $Anatip_rec['TIPDES']);
            $dizionario->addFieldData('SETDES', $Anaset_rec['SETDES']);
            $dizionario->addFieldData('ATTDES', $Anaset_rec['ATTDES']);
            $dizionario->addFieldData('GESOGG', $Proges_rec['GESOGG']);
        } else {
            //
            // Usato solo come descrizione su parametri vari per oggetto protocollo
            //
            $dizionario = $this->getCampiFascicolo();
        }
        return $dizionario;
    }

    public function getVariabiliBaseProtocollo() {
//        $praLib = new praLib();
//        $PRAM_DB = $praLib->getPRAMDB();
//        $PRAM_DB = $praLib->getPRAMDB();
//        $proLibPratica = new proLibPratica();
        $proLib = new proLib();
        $PROT_DB = $proLib->getPROTDB();
        $emlLib = new emlLib();
        $ITALWEBDB = $emlLib->getITALWEB();

        $Anapro_rec = $proLib->GetAnapro($this->codiceProtocollo, 'codice', $this->tipoProtocollo);

        if ($Anapro_rec) {
            $i = 1;
            $dizionario = $this->getCampiProtocollo();
            $anaent_2 = $proLib->GetAnaent('2');
            $anaent_26 = $proLib->GetAnaent('26');
            $anaogg_rec = $proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
            $OggettoMail = "";
            if ($Anapro_rec['PROIDMAILDEST']) {
                $sql = "SELECT * FROM MAIL_ARCHIVIO WHERE IDMAIL = '" . $Anapro_rec['PROIDMAILDEST'] . "'";
                $MailArchivio_rec = ItaDB::DBSQLSelect($ITALWEBDB, $sql, false);
                $OggettoMail = $MailArchivio_rec['SUBJECT'];
            }

            $dizionario->addFieldData('PRONUM', $Anapro_rec['PRONUM']);
            $dizionario->addFieldData('PRONUM_FORMATTED', str_pad(substr($Anapro_rec['PRONUM'], 4, 6), 7, '0', STR_PAD_LEFT) . "/" . substr($Anapro_rec['PRONUM'], 0, 4));
            $dizionario->addFieldData('PROTOCOLLO_NUM', intval(substr($Anapro_rec['PRONUM'], 4, 6)));
            $dizionario->addFieldData('PROTOCOLLO_ANNO', substr($Anapro_rec['PRONUM'], 0, 4));
            $dizionario->addFieldData('PROPAR', $Anapro_rec['PROPAR']);
            $dizionario->addFieldData('PROSEG', $Anapro_rec['PROSEG']);
            $dizionario->addFieldData('PROFASKEY', $Anapro_rec['PROFASKEY']);
            $dizionario->addFieldData('DESC_FASCICOLO', $Anapro_rec['PROFASKEY']);
            $Annullato = '';
            if ($Anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
                $Annullato = 'Annullato'; // Annullato il, da, motivo?
            }
            $dizionario->addFieldData('PROTANN', $Annullato);
            // 
            $dizionario->addFieldData('OGGETTO_MAIL', $OggettoMail);
            $dizionario->addFieldData('OGGETTO', htmlspecialchars_decode($anaogg_rec['OGGOGG'])); //htmlspecialchars_decode?
            $dizionario->addFieldData('AMM_CODICE', $anaent_26['ENTDE1']);
            $dizionario->addFieldData('AMM_DESCR', $anaent_2['ENTDE1']);
            $dizionario->addFieldData('AMM_AOO', $anaent_26['ENTDE2']);
            $dizionario->addFieldData('MITTENTE', $Anapro_rec['PRONOM']);
            $dizionario->addFieldData('MITTENTE_MAIL', $Anapro_rec['PROMAIL']);

            $dizionario->addFieldData('REG_DATA', date('d/m/Y', strtotime($Anapro_rec['PRODAR'])));
            $dizionario->addFieldData('DATA_ARRIVO', date('d/m/Y', strtotime($Anapro_rec['PRODAA'])));
            $dizionario->addFieldData('PROT_MITTENTE', $Anapro_rec['PRONPA']);
            $dizionario->addFieldData('DATA_PROT_MITTENTE', $Anapro_rec['PRODAS']);
            // Valorizzazione nuove variabili

            $dizionario->addFieldData('INDIRIZZO', $Anapro_rec['PROIND']);
            $dizionario->addFieldData('CITTA', $Anapro_rec['PROCIT']);
            $dizionario->addFieldData('PROVINCIA', $Anapro_rec['PROPRO']);
            $dizionario->addFieldData('CAP', $Anapro_rec['PROCAP']);
            $dizionario->addFieldData('EMAIL', $Anapro_rec['PROMAIL']);

            $dizionario->addFieldData('FIRMATARIO_COD', '');
            $dizionario->addFieldData('FIRMATARIO', '');
            $dizionario->addFieldData('FIRMATARIO_UFF', '');
            if ($Anapro_rec['PROPAR'] == 'P' || $Anapro_rec['PROPAR'] == 'C') {
                $anades_mitt = $proLib->GetAnades($Anapro_rec['PRONUM'], 'codice', false, $Anapro_rec['PROPAR'], 'M');
                if ($anades_mitt) {
                    $anauff_rec = $proLib->GetAnauff($anades_mitt['DESCUF'], 'codice');
                    $dizionario->addFieldData('FIRMATARIO_COD', $anades_mitt['DESCOD']);
                    $dizionario->addFieldData('FIRMATARIO', $anades_mitt['DESNOM']);
                    $dizionario->addFieldData('FIRMATARIO_UFF', $anauff_rec['UFFDES']);
                }
            }
            $dizionario->addFieldData('DESTINATARIO', $Anapro_rec['PRONOM']);
            $dizionario->addFieldData('DATA_INVIO', date('d/m/Y', strtotime($Anapro_rec['PRODAA'])));
            $dizionario->addFieldData('CODTIPO_DOC', $Anapro_rec['PROCODTIPODOC']);

            if ($Anapro_rec['PROCODTIPODOC']) {
                $AnaTipoDoc_rec = $proLib->GetAnaTipoDoc($Anapro_rec['PROCODTIPODOC'], 'codice');
                $dizionario->addFieldData('TIPO_DOC', $AnaTipoDoc_rec['DESCRIZIONE']);
            }

            /* Precedente Classificazione/Descrizione Titolario.. - Quale è piu corrett? */
            $DesCla = '';
            if ($Anapro_rec['PROCAT']) {
                $Anacat_rec = $proLib->GetAnacat($Anapro_rec['VERSIONE_T'], $Anapro_rec['PROCAT'], 'codice');
                $DesCla.= $Anacat_rec['CATDES'];
            }
            if ($Anapro_rec['PROCCA']) {
                $Anacla_rec = $proLib->GetAnacla($Anapro_rec['VERSIONE_T'], $Anapro_rec['PROCCA'], 'codice');
                $DesCla.=' - ' . $Anacla_rec['CLADE1'] . $Anacla_rec['CLADE2'];
            }
            if ($Anapro_rec['PROCCF']) {
                $Anafas_rec = $proLib->GetAnafas($Anapro_rec['VERSIONE_T'], $Anapro_rec['PROCCF'], 'fasccf');
                if ($Anafas_rec['FASDES']) {
                    $DesCla.= ' - ' . $Anafas_rec['FASDES'];
                }
            }

            $dizionario->addFieldData('CLASSIFICAZIONE', $Anapro_rec['PROCCF']);
            $dizionario->addFieldData('DES_CLASSIFICAZIONE', $DesCla);
            /* Nuovo Titolario.. - Quale è piu corrett? */
            $Titolario = $DescTitolario = '';
            if ($Anapro_rec['PROCCF']) {
                $anafas_rec = $proLib->GetAnafas($Anapro_rec['VERSIONE_T'], $Anapro_rec['PROCCF'], 'fasccf');
                if ($anafas_rec) {
                    $Titolario = substr($anafas_rec['FASCCA'], 0, 4) . '.' . substr($anafas_rec['FASCCA'], 4) . '.' . $anafas_rec['FASCOD'];
                    $DescTitolario = $anafas_rec['FASDES'];
                }
            } else if ($Anapro_rec['PROCCA']) {
                $anacla_rec = $proLib->GetAnacla($Anapro_rec['VERSIONE_T'], $Anapro_rec['PROCCA'], 'codice');
                if ($anacla_rec) {
                    $Titolario = $anacla_rec['CLACAT'] . '.' . $anacla_rec['CLACOD'];
                    $DescTitolario = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
                }
            } else if ($Anapro_rec['PROCAT']) {
                $anacat_rec = $proLib->GetAnacat($Anapro_rec['VERSIONE_T'], $Anapro_rec['PROCAT'], 'codice');
                if ($anacat_rec) {
                    $Titolario = $anacat_rec['CATCOD'];
                    $DescTitolario = $anacat_rec['CATDES'];
                }
            }
            $dizionario->addFieldData('TITOLARIO', $Titolario);
            $dizionario->addFieldData('DESC_TITOLARIO', $DescTitolario);

            $ProAnnoPre = $ProNumPre = '';
            if ($Anapro_rec['PROPRE']) {
                $ProAnnoPre = substr($Anapro_rec['PROPRE'], 0, 4);
                $ProNumPre = substr($Anapro_rec['PROPRE'], 4);
            }

            $dizionario->addFieldData('NUME_PROT_COLL', $ProNumPre);
            $dizionario->addFieldData('ANNO_PROT_COLL', $ProAnnoPre);
            $dizionario->addFieldData('TIPO_PROT_COLL', $Anapro_rec['PROPARPRE']);
            $dizionario->addFieldData('NUM_DOCUMENTO', '');
            $dizionario->addFieldData('COD_DOCUMENTO', '');
            if ($Anapro_rec['PROPAR'] == 'I') {
                include_once ITA_BASE_PATH . '/apps/Segreteria/segLib.class.php';
                $segLib = new segLib();
                $Indice_rec = $segLib->GetIndice($Anapro_rec['PRONUM'], 'anapro', false, $Anapro_rec['PROPAR']);
                $dizionario->addFieldData('NUM_DOCUMENTO', substr($Indice_rec['IDELIB'], 2));
                $dizionario->addFieldData('COD_DOCUMENTO', $Indice_rec['IDELIB']);
            }



            /* Tabelle Allegati */
            $templateHtml = $this->getAllegatiTabellaTemplate();

            $htmltTab = '';
            $DatiAlleProto = $this->loadDatiAllegati($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
            if ($DatiAlleProto) {
                $htmltTab = $this->elaboraTabella($templateHtml, $DatiAlleProto);
            }
            $dizionario->addFieldData('PROTOALLEGATI', $htmltTab);

            $htmltTab = '';
            if ($Anapro_rec['PROPRE']) {
                $DatiAlleProtoColle = $this->loadDatiAllegati($Anapro_rec['PROPRE'], $Anapro_rec['PROPARPRE']);
                if ($DatiAlleProtoColle) {
                    $htmltTab = $this->elaboraTabella($templateHtml, $DatiAlleProtoColle);
                }
                $dizionario->addFieldData('PROTOALLEGATI_COLL', $htmltTab);
            }
        } else {
            $dizionario = $this->getCampiProtocollo();
        }

        return $dizionario;
    }

    public function getVariabiliAzioneFascicolo() {
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $proLib = new proLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $proLibPratica = new proLibPratica();
        $PROT_DB = $proLib->getPROTDB();
        $Proges_rec = $proLibPratica->GetProges($this->codicePratica);
        if ($Proges_rec) {
            $Propas_rec = $proLibPratica->GetPropas($this->chiavePasso);
            $i = 1;
            $dizionario = $this->getCampiAzione();

            $dizionario->addFieldData('PRODTP', $Propas_rec['PRODTP']);
            $dizionario->addFieldData('PRODPA', $Propas_rec['PRODPA']);
        } else {
            $dizionario = $this->getCampiAzione();
        }
        $this->variabiliPasso = $dizionario;
        return $this->variabiliPasso;
    }

    public function getCampiAzione() {
        $i = 1;
        $dizionario = new itaDictionary();
        //
        // Variabili dei Passo
        //
        $dizionario->addField('PRODTP', 'Tipo di Passo', $i++, 'base');
        $dizionario->addField('PRODPA', 'Descrizione Passo', $i++, 'base');
        $this->variabiliPasso = $dizionario;
        return $this->variabiliPasso;
    }

    public function getVariabiliCampiAggiuntiviAzione() {
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $proLib = new proLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $proLibPratica = new proLibPratica();
        $PROT_DB = $proLib->getPROTDB();
        $Proges_rec = $proLibPratica->GetProges($this->codicePratica);
        if (!$Proges_rec) {
            return null;
        }
        $Propas_rec = $proLibPratica->GetPropas($this->chiavePasso);
        if (!$Propas_rec) {
            return null;
        }
        $variabiliCampiAggiuntivi = new itaDictionary();
        $currSeq = $Propas_rec['PROSEQ'];
        $sql = "
            SELECT
                PRODAG.DAGKEY AS DAGKEY,
                PRODAG.DAGPAK AS DAGPAK,
                PRODAG.DAGDES AS DAGDES,
                PRODAG.DAGTIP AS DAGTIP,
                PROPAS.PROSEQ AS PROSEQ,
                PRODAG.DAGVAL AS DAGVAL                
            FROM
                PROPAS PROPAS
            LEFT OUTER JOIN
                PRODAG PRODAG
            ON
                PROPAS.PROPAK=PRODAG.DAGPAK
            WHERE DAGTIP = ''  AND PROPAS.PRONUM='" . $this->codicePratica . "' AND PROSEQ <= $currSeq ORDER BY PROSEQ";
        $Prodag_tab = ItaDB::DBSQLSelect($PROT_DB, $sql, true);
        $i = 0;
        if ($Prodag_tab) {
            foreach ($Prodag_tab as $Prodag_rec) {
                $descrizioneCampo = $Prodag_rec['DAGDES'] ? $Prodag_rec['DAGDES'] : $Prodag_rec['DAGKEY'];
                $variabiliCampiAggiuntivi->addField($Prodag_rec['DAGKEY'], $descrizioneCampo, $i++, 'base', $Prodag_rec['DAGVAL']);
            }
            $this->variabiliCampiAggiuntivi = $variabiliCampiAggiuntivi;
        } else {
            $this->variabiliCampiAggiuntivi = null;
        }
        return $this->variabiliCampiAggiuntivi;
    }

    public function getLegendaFascicolo($tipo = "adjacency", $markup = 'smarty') {
        return $this->getVariabiliPratica()->exportAdjacencyModel($markup);
    }

    public function getCampiFascicolo() {
        $i = 1;
        $dizionario = new itaDictionary();
        //
        // Dati Procedimento
        //
        $dizionario->addField('GESNUM', 'Numero Pratica', $i++, 'base');
        $dizionario->addField('GESNUM_FORMATTED', 'Numero Pratica N/AAAA', $i++, 'base');
        $dizionario->addField('GESKEY', 'Codice fascicolo', $i++, 'base');
        $dizionario->addField('GESDRE', 'Data Registrazione', $i++, 'base');
        $dizionario->addField('GESDRI', 'Data Ricezione On-Line', $i++, 'base');
        $dizionario->addField('GESOGG', 'Oggetto Fascicolo', $i++, 'base');
        return $dizionario;
    }

    public function getCampiProtocollo() {
        $i = 1;
        $dizionario = new itaDictionary();
        $dizionario->addField('PRONUM', 'Numero Protocollo nella forma AAAANNNNNN', $i++, 'base');
        $dizionario->addField('PRONUM_FORMATTED', 'Numero Protocollo nella forma  N/AAAA', $i++, 'base');
        $dizionario->addField('PROTOCOLLO_NUM', 'Numero Protocollo nella forma  N', $i++, 'base');
        $dizionario->addField('PROTOCOLLO_ANNO', 'Anno Protocollo nella forma  AAAA', $i++, 'base');
        $dizionario->addField('PROPAR', 'Tipo Protocollo', $i++, 'base');
        $dizionario->addField('PROSEG', 'Segnatura Protocollo', $i++, 'base');
        $dizionario->addField('REG_DATA', 'Data Registrazione del Protocollo', $i++, 'base');
        $dizionario->addField('DATA_ARRIVO', 'Data di Arrivo del Protocollo', $i++, 'base');
        $dizionario->addField('PROTANN', 'Stato protocollo: Annullato', $i++, 'base');

        $dizionario->addField('PROFASKEY', 'Fascicolo di Appartenenza', $i++, 'base');
        $dizionario->addField('DESC_FASCICOLO', 'Descrizione Fascicolo di Appartenenza', $i++, 'base');
        $dizionario->addField('OGGETTO', 'Oggetto del Protocollo', $i++, 'base');
        $dizionario->addField('MITTENTE', 'Descrizione Mittente', $i++, 'base');
        $dizionario->addField('MITTENTE_MAIL', 'Mail Mittente', $i++, 'base');
        $dizionario->addField('OGGETTO_MAIL', 'Oggetto Mail Protocollata', $i++, 'base');
        $dizionario->addField('AMM_CODICE', 'Codice Amministrazione', $i++, 'base');
        $dizionario->addField('AMM_DESCR', 'Descrizione Amministrazione', $i++, 'base');
        $dizionario->addField('AMM_AOO', 'Codice AOO', $i++, 'base');
        $dizionario->addField('CLASSIFICAZIONE', 'Classificazione', $i++, 'base');
        $dizionario->addField('DES_CLASSIFICAZIONE', 'Descrizione Classificazione', $i++, 'base');
        $dizionario->addField('PROT_MITTENTE', 'Protocollo del Mittente', $i++, 'base');
        $dizionario->addField('DATA_PROT_MITTENTE', 'Data del protocollo mittente', $i++, 'base');
        // Nuove Variabili
        $dizionario->addField('INDIRIZZO', 'Indirizzo Mitt/Dest del protocollo', $i++, 'base');
        $dizionario->addField('CITTA', 'Città Mitt/Dest del protocollo', $i++, 'base');
        $dizionario->addField('PROVINCIA', 'Provincia del Mitt/Dest del protocollo', $i++, 'base');
        $dizionario->addField('CAP', 'CAP del Mitt/Dest del protocollo', $i++, 'base');
        $dizionario->addField('EMAIL', 'Mail del Mitt/Dest del protocollo', $i++, 'base');
        $dizionario->addField('FIRMATARIO_COD', 'Codice Firmatario del Protocollo', $i++, 'base');
        $dizionario->addField('FIRMATARIO', 'Firmatario del Protocollo', $i++, 'base');
        $dizionario->addField('FIRMATARIO_UFF', 'Ufficio del Firmatario del Protocollo', $i++, 'base');
        $dizionario->addField('DESTINATARIO', 'Data di Invio del Protocollo', $i++, 'base');
        $dizionario->addField('DATA_INVIO', 'Data di Invio del Protocollo', $i++, 'base');
        $dizionario->addField('CODTIPO_DOC', 'Codice Tipologia Documento', $i++, 'base');
        $dizionario->addField('TIPO_DOC', 'Tipologia Documento', $i++, 'base');
        $dizionario->addField('DESC_TITOLARIO', 'Descrizione del Titolario del protocollo', $i++, 'base');
        $dizionario->addField('TITOLARIO', 'Titolario del protocollo', $i++, 'base');
        $dizionario->addField('NUME_PROT_COLL', 'Numero Protocollo Collegato', $i++, 'base');
        $dizionario->addField('ANNO_PROT_COLL', 'Anno Protocollo Collegato', $i++, 'base');
        $dizionario->addField('TIPO_PROT_COLL', 'Tipo Protocollo Collegato', $i++, 'base');
        $dizionario->addField('NUM_DOCUMENTO', 'Numero Documento Indice', $i++, 'base');

        $dizionario->addField('PROTOALLEGATI', 'Elenco Allegati del protocollo', $i++, 'base', '@{$PROTOALLEGATI}@', false, '"type":"html"');
        $dizionario->addField('PROTOALLEGATI_COLL', 'Elenco Allegati del protocollo collegato', $i++, 'base', '@{$PROTOALLEGATI_COLL}@', false, '"type":"html"');

        /*
         * Prevedere:
         * - Nome Elenco Allegati 
         * - Destinatari * (?)
         */

        return $dizionario;
    }

    public function getVariabiliProtocollo($all = false) {
        $this->variabiliProtocollo = new itaDictionary();
        $this->variabiliProtocollo = $this->getLegendaCampiProtocollo();
        if ($this->codiceProtocollo) {
            $this->variabiliProtocollo = $this->getVariabiliBaseProtocollo();
        }
        return $this->variabiliProtocollo;
    }

    public function getLegendaCampiProtocollo($tipo = "adjacency", $markup = 'smarty') {
        return $this->getCampiProtocollo()->exportAdjacencyModel($markup);
    }

    public function loadDatiAllegati($pronum, $propar) {
        $proLib = new proLib();
        $where = " AND DOCSERVIZIO = '' ";
        $Anadoc_tab = $proLib->GetAnadoc($pronum, 'protocollo', true, $propar, $where);
        $Allegati = array();
        $cc = 1;
        foreach ($Anadoc_tab as $Anadoc_rec) {
            $Allegati[$cc]['ALLEGATI_PROTO']['NOMEFILE'] = $Anadoc_rec['DOCNAME'];
            $Allegati[$cc]['ALLEGATI_PROTO']['DESCFILE'] = $Anadoc_rec['DOCNOT'];
            $TipoFile = $Anadoc_rec['DOCTIPO'];
            if ($Anadoc_rec['DOCTIPO'] == '') {
                $TipoFile = 'PRINCIPALE';
            }
            $Allegati[$cc]['ALLEGATI_PROTO']['TIPOFILE'] = $TipoFile;
            $Allegati[$cc]['ALLEGATI_PROTO']['DATAFILE'] = date("d/m/Y", strtotime($Anadoc_rec['DOCFDT']));
            $Allegati[$cc]['ALLEGATI_PROTO']['SHAFILE'] = $Anadoc_rec['DOCSHA2'];
            $cc++;
        }
        return $Allegati;
    }

    public function getAllegatiTabellaTemplate() {
        $html = '<table class="ita-table-template" style="width: 100%; border: 1px solid windowtext; height: 45px;" border="1" cellspacing="0" cellpadding="0">
                <tbody>
                <tr class="ita-table-header">
                <td class="mceSelected" style="border: 1pt solid #c1c1c1; background-color: #ffffcc; padding: 1.5pt; width: 15%; text-align: center;"><span style="font-size: 10pt;">Nome File</span></td>
                <td class="mceSelected" style="border: 1pt solid #c1c1c1; background-color: #ffffcc; padding: 1.5pt; width: 15%; text-align: center;"><span style="font-size: 10pt;">Tipo</span></td>
                <td class="mceSelected" style="border: 1pt solid #c1c1c1; background-color: #ffffcc; padding: 1.5pt; width: 15%; text-align: center;"><span style="font-size: 10pt;">Data</span></td>
                <td class="mceSelected" style="border: 1pt solid #c1c1c1; background-color: #ffffcc; padding: 1.5pt; width: 15%; text-align: center;"><span style="font-size: 10pt;">Impronta</span></td>
                </tr>
                <tr align="center">
                <td class="mceSelected" style="border: 1pt solid #c1c1c1; padding: 1.5pt;"><span style="font-size: 8pt; ">@{$ALLEGATI_PROTO.NOMEFILE}@</span></td>
                <td class="mceSelected" style="border: 1pt solid #c1c1c1; padding: 1.5pt;"><span style="font-size: 8pt; ">@{$ALLEGATI_PROTO.TIPOFILE}@</span></td>
                <td class="mceSelected" style="border: 1pt solid #c1c1c1; padding: 1.5pt;"><span style="font-size: 8pt; ">@{$ALLEGATI_PROTO.DATAFILE}@</span></td>
                <td class="mceSelected" style="border: 1pt solid #c1c1c1; padding: 1.5pt;"><span style="font-size: 8pt; ">@{$ALLEGATI_PROTO.SHAFILE}@</span></td>
                </tr>
                </tbody>
                </table>';
        return $html;
    }

    function elaboraTabella($html, $dati) {
        $dom = new DOMDocument;

        /*
         * Nuovo metodo
         * Carlo Iesari, 11.10.2015
         */

        /*
         * Carico l'HTML
         */
        @$dom->loadHTML($html);

        /*
         * Prendo tutte le tabelle
         */
        $tables = $dom->getElementsByTagName('table');

        /* @var $table DOMNode */
        foreach ($tables as $table) {
            if (!$table->getAttribute('class') == 'ita-table-template') {
                /*
                 * Se la tabella non è .ita-table-template non la elaboro
                 */
                continue;
            }

            /*
             * Prendo tutte le righe all'interno della tabella
             */
            $rows = $dom->getElementsByTagName('tr');

            /*
             * Array temporanei per headers, contenuti e footers
             */
            $ita_headers = array();
            $ita_rows = array();
            $ita_footers = array();

            /* @var $row DOMNode */
            foreach ($rows as $row) {
                if ($row->getAttribute('class') == 'ita-table-header') {
                    /*
                     * Se la riga è .ita-table-header, salvo e proseguo alla prossima
                     */
                    $ita_headers[] = $row;
                    continue;
                }

                if ($row->getAttribute('class') == 'ita-table-footer') {
                    /*
                     * Se la riga è .ita-table-footer, salvo e proseguo alla prossima
                     */
                    $ita_footers[] = $row;
                    continue;
                }

                /*
                 * Se sono arrivato fin qui, la riga non è né .ita-table-header né .ita-table-footer
                 */

                /*
                 * Salvo l'HTML della riga
                 */
//                $row_html = $dom->saveXML($row);
                $row_html = utf8_decode($dom->saveXML($row));
                foreach ($dati as $record) {
//                    /*
//                     * Creo un nuovo frammento dove inserire l'HTML della riga elaborato
//                     * per ogni dato
//                     */
//                    $fragment = $dom->createDocumentFragment();
//                    $fragment->appendXML($this->sostituisciVariabili($row_html, $record));
//                    
//                    $ita_rows[] = $fragment;

                    /*
                     * Creo il nuovo HTML con le variabili sostituite
                     */

                    $new_html = $this->sostituisciVariabili($row_html, $record);

                    /*
                     * Utilizzo un DOMDocument temporaneo per importare l'HTML (->loadHTML)
                     * e tramite esso importarlo nel DOMDocument principale (->importNode)
                     */
                    $tmp_dom = new DOMDocument;
                    $tmp_dom->loadHTML($new_html);
                    $tr_node = $tmp_dom->getElementsByTagName('tr')->item(0);
                    $ita_rows[] = $dom->importNode($tr_node, true);
                }
            }

            /*
             * Elimino tutte le righe della tabella
             */
            while ($rows->length > 0) {
                $rows->item(0)->parentNode->removeChild($rows->item(0));
            }

            /*
             * Reintroduco le righe salvate in ordine (header, righe elaborate e footer)
             */
            foreach ($ita_headers as $row) {
                $table->appendChild($row);
            }

            foreach ($ita_rows as $row) {
                $table->appendChild($row);
            }

            foreach ($ita_footers as $row) {
                $table->appendChild($row);
            }
        }

        /*
         * Ritorno l'HTML risultante
         * Pulisco l'output dal doctype + tag html/body
         */
        $return_html = preg_replace('~<(?:!DOCTYPE|/?(?:html|body))[^>]*>\s*~i', '', $dom->saveHTML());
        return $return_html;
    }

    public function sostituisciVariabili($expr, $dict) {
        $itaSmarty = new itaSmarty();
        $itaSmarty->force_compile = true;
        foreach ($dict as $key => $valore) {
            $itaSmarty->assign($key, $valore);
        }
        $documentoTmp = App::getPath('temporary.uploadPath') . '/' . App::$utente->getKey('TOKEN') . '-documentoTmp.tpl';
        if (!$this->writeFile($documentoTmp, $expr)) {
            return false;
        }
        $contenuto = $itaSmarty->fetch($documentoTmp);
        @unlink($documentoTmp);
        return $contenuto;
    }

    private function writeFile($file, $string) {
        $fpw = fopen($file, 'w');
        if (!@fwrite($fpw, $string)) {
            fclose($fpw);
            return false;
        }
        fclose($fpw);
        return true;
    }

}

?>