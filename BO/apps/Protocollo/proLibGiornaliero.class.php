<?php

include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proIter.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proDigiPMarche.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerFactory.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proConservazioneManagerHelper.class.php';

class proLibGiornaliero {

    /**
     * Libreria del registro giornaliero
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    const FONTE_DATI_REGISTRO = 'REGISTRO_PROT';
    const FONTE_DATI_ESITO_CONSERVAZIONE = 'ESITO_CONSERVAZIONE';
    const CHIAVE_DATA_REGISTRO = 'DataRegistro';
    const CHIAVE_DATA_CREAZIONE = 'DataCreazione';
    const CHIAVE_DATA_FIRMA = 'DataFirma';
    const CHIAVE_EXPORT_DATA = 'DataExport';
    const CHIAVE_EXPORT_ORA = 'OraExport';
    const CHIAVE_EXPORT_SHA2 = 'Sha2Export';
    const CHIAVE_EXPORT_REPOS = 'ReposExport';
    const CHIAVE_NUMERO_INIZIALE = 'NumeroIniziale';
    const CHIAVE_NUMERO_FINALE = 'NumeroFinale';
    const CHIAVE_DATA_INIZIO_REGISTRAZIONE = 'DataInizioRegistrazione';
    const CHIAVE_DATA_FINE_REGISTRAZIONE = 'DataFineRegistrazione';
    const CHIAVE_RESPONSABILE = 'Responsabile';
    const CHIAVE_CF_RESPONSABILE = 'CodiceFiscaleResponsabile';
    const CHIAVE_NUMERO_DOCUMENTI_REGISTRATI = 'NumeroDocumentiRegistrati';
    const CHIAVE_SOGGETTO_PRODUTTORE = 'SoggettoProduttore';
    const CHIAVE_CF_SOGGETTO_PRODUTTORE = 'CodiceFiscaleSoggettoProduttore';
// Chiavi Controlli Anomalie
    const CTR_GIORNI_MANCANTI = 'GIORNI_MANCANTI';
    const CTR_ALLEGATO_MANCANTE = 'ALLEGATO_MANCANTE';
    const CTR_ALLEGATO_DA_FIRMARE = 'ALLEGATO_DA_FIRMARE';
    const CTR_ALLEGATO_NON_ALLA_FIRMA = 'ALLEGATO_NON_ALLA_FIRMA';
    const CTR_ALLEGATO_NON_ESPORTATO = 'ALLEGATO_NON_ESPORTATO';
    const CTR_ALLEGATO_NON_CONSERVATO = 'ALLEGATO_NON_CONSERVATO';
    const CTR_ALLEGATO_HASH_ERRATO = 'ALLEGATO_HASH_ERRATO';
    const CTR_ALLEGATO_NO_METADATI = 'METADATI_MANCANTI';
    const CTR_DOCUMENTO_NON_CONSERVABILE = 'DOCUMENTO_NON_CONSERVABILE';
    const CTR_DOCUMENTO_ANNULLATO = 'DOCUMENTO_ANNULLATO';
    const CTR_ALLEGATO_NONCONS = 'ALLEGATO_NOCONS';

    //const CTR_REGISTRO_DA_CONSERVARE = 'REGISTRO_DA_CONSERVARE';

    /*
     * Elenco delle fonti per Registro Giornaliero
     */

    public static $ElencoFontiRegistroGio = array(
        self::FONTE_DATI_REGISTRO => 'Dati Registro',
        self::FONTE_DATI_ESITO_CONSERVAZIONE => 'Dati Esito Conservazione'
    );

    /*
     * Elenco Alias Chiavi Attive su TabDag per la fonte FONTE_DATI_REGISTRO
     */
    public static $ElencoChiaviAttiveTabDag = array(
        self::CHIAVE_DATA_REGISTRO => 'DATAREGISTRO',
        self::CHIAVE_DATA_CREAZIONE => 'DATACREAZIONE',
        self::CHIAVE_DATA_FIRMA => 'DATAFIRMA',
        self::CHIAVE_EXPORT_DATA => 'EXPORTDATA',
        self::CHIAVE_SOGGETTO_PRODUTTORE => 'SOGGETTOPROD',
        self::CHIAVE_RESPONSABILE => 'SOGGETTORESPONSABILE',
        self::CHIAVE_NUMERO_INIZIALE => 'NUMEROINIZIALE',
        self::CHIAVE_NUMERO_FINALE => 'NUMEROFINALE',
        self::CHIAVE_DATA_INIZIO_REGISTRAZIONE => 'DATAINIZIOREGISTRAZIONE',
        self::CHIAVE_DATA_FINE_REGISTRAZIONE => 'DATAFINEREGISTRAZIONE',
        self::CHIAVE_CF_SOGGETTO_PRODUTTORE => 'CFSOGGETTOPRODUTTORE',
        self::CHIAVE_CF_RESPONSABILE => 'CFRESPONSABILE',
        self::CHIAVE_NUMERO_DOCUMENTI_REGISTRATI => 'NUMERO_DOCUMENTI_REGISTRATI',
    );
    public static $ElencoChiaviAttiveTabDagEsito = array(
        proLibConservazione::CHIAVE_ESITO_ESITO => 'ESITO'
    );

    /*
     * Elenco Descrittivo Controlli Anomalie 
     */
    public static $ElencoDescrCtrAnomalie = array(
        self::CTR_GIORNI_MANCANTI => 'Giorni Mancanti',
        self::CTR_ALLEGATO_MANCANTE => 'Allegato Mancante',
        self::CTR_ALLEGATO_DA_FIRMARE => 'Allegato da Firmare',
        self::CTR_ALLEGATO_NON_ALLA_FIRMA => 'Allegato non alla Firma',
        self::CTR_ALLEGATO_NON_ESPORTATO => 'Allegato non Esportato',
        self::CTR_ALLEGATO_NON_CONSERVATO => 'Allegato non Conservato',
        self::CTR_ALLEGATO_HASH_ERRATO => 'Allegato Hash file incongruente',
        self::CTR_ALLEGATO_NO_METADATI => 'Metadati Mancanti',
        self::CTR_DOCUMENTO_ANNULLATO => 'Registro Annullato',
        self::CTR_ALLEGATO_NONCONS => 'Allegato non conservabile'
    );

    /*
     * red
     */
    public static $ElencoColoriCtrAnomalie = array(
        self::CTR_ALLEGATO_MANCANTE => '#FFFF00',
        self::CTR_ALLEGATO_NO_METADATI => '#CCFFFF',
        self::CTR_ALLEGATO_DA_FIRMARE => '#DF7401',
        self::CTR_ALLEGATO_NON_ALLA_FIRMA => '#DF0101',
        self::CTR_ALLEGATO_NON_ESPORTATO => '#DF01A5',
        self::CTR_ALLEGATO_HASH_ERRATO => '#00BFFF',
        self::CTR_DOCUMENTO_ANNULLATO => '#6F00FF',
        self::CTR_ALLEGATO_NONCONS => '#6F0123',
        self::CTR_ALLEGATO_NON_CONSERVATO => '#0101DF' // Deve rimanre ultimo
    );
    public $proLib;
    public $proLibConservazione;
    public $proDigiPMarche;
    public $proLibAllegati;
    private $errCode;
    private $errMessage;

    function __construct() {
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
        $this->proLibConservazione = new proLibConservazione();
        $this->proDigiPMarche = new proDigiPMarche();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getParametriRegistro() {
        $anaent_41 = $this->proLib->GetAnaent('41');
        $anaent_42 = $this->proLib->GetAnaent('42');
        $parametri = array();
        $Firmatario = $this->getRegistroFirmatario();
        $Responsabile = $this->getRegistroResponsabile();

        $parametri['TIPODOCUMENTO'] = $anaent_41['ENTVAL'];
        $parametri['FIRMATARIO'] = $Firmatario['CODICE'];
        $parametri['UFFICIO_FIRMATARIO'] = $Firmatario['UFFICIO'];
        $parametri['RESPONSABILE'] = $Responsabile['CODICE'];
        $parametri['UFFICIO_RESPONSABILE'] = $Responsabile['UFFICIO'];
        $parametri['REPOSITORY'] = $anaent_41['ENTDE3'];
        $parametri['DATALIMITEINIZIALE'] = $anaent_41['ENTDE4'];
        if (!$parametri['DATALIMITEINIZIALE']) {
            $parametri['DATALIMITEINIZIALE'] = '20151012';
        }
        $parametri['CATEGORIA'] = $anaent_42['ENTVAL'];
        $parametri['CLASSIFICAZIONE'] = $anaent_41['ENTDE5'];
        $parametri['SOTTOCLASSE'] = $anaent_41['ENTDE6'];
        $parametri['METTIALLAFIRMA'] = $anaent_42['ENTDE1'];
        $parametri['NOREGISTROVUOTO'] = $anaent_42['ENTDE4'];
        $parametri['REGISTROMODIFICHE'] = $anaent_42['ENTDE5'];
        $parametri['TIPOCONSERVAZIONE'] = $anaent_42['ENTDE6'];
        // Qui controllo se Firmatario mancante allora = a Responsabile?

        return $parametri;
    }

    public function getAnaproAndMetadati($rowid) {
        $sql = $this->getSqlElencoRegistro(" AND ANAPRO.ROWID=$rowid ");
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    /**
     * 
     * @return array
     * CODICE
     * UFFICIO
     * 
     */
    public function getRegistroFirmatario() {
        $ret = array();
        $anaent_41 = $this->proLib->GetAnaent('41');
        if ($anaent_41['ENTDE1']) {
            $ret['CODICE'] = $anaent_41['ENTDE1'];
            $ret['UFFICIO'] = $anaent_41['ENTDE2'];
        } else {
            $anaent_42 = $this->proLib->GetAnaent('42');
            $ret['CODICE'] = $anaent_42['ENTDE2'];
            $ret['UFFICIO'] = $anaent_42['ENTDE3'];
        }
        return $ret;
    }

    /**
     * 
     * @return array
     *    CODICE
     *    UFFICIO
     */
    public function getRegistroResponsabile() {
        $ret = array();
        $anaent_42 = $this->proLib->GetAnaent('42');
        if ($anaent_42['ENTDE2']) {
            $ret['CODICE'] = $anaent_42['ENTDE2'];
            $ret['UFFICIO'] = $anaent_42['ENTDE3'];
        } else {
            $anaent_41 = $this->proLib->GetAnaent('41');
            $ret['CODICE'] = $anaent_41['ENTDE1'];
            $ret['UFFICIO'] = $anaent_41['ENTDE2'];
        }
        return $ret;
    }

    public function getSqlElencoRegistro($where = '') {
        $parametriRegistro = $this->getParametriRegistro();
        $sql = $this->proLib->getSqlRegistro();
        $sql.=" LEFT OUTER JOIN ANADES ANADES ON ANAPRO.PRONUM=ANADES.DESNUM AND ANAPRO.PROPAR=ANADES.DESPAR";


        $sql.=" WHERE ANAPRO.PROPAR='C' ";
        $sql.=$where;
        if ($parametriRegistro['TIPODOCUMENTO']) {
            $sql.=" AND ANAPRO.PROCODTIPODOC='" . $parametriRegistro['TIPODOCUMENTO'] . "' ";
        }

        $sqlbase = '';
        $CampiTabDag = $this->getCampiTabDag();
        $JoinTabDag = $this->getJoinCampiTabDag('REGISTRO_PROTO');
        $sqlbase = "SELECT REGISTRO_PROTO.*
                        $CampiTabDag
                        FROM ($sql) AS REGISTRO_PROTO
                        $JoinTabDag 
                    GROUP BY REGISTRO_PROTO.ROWID ";
        return $sqlbase;
    }

    /*
     * LA FUNZIONE, USATA INSIEME A getCampiTabDag PERMETTE DI 
     * CREARE AUTOMATICAMENTE LA JOIN CON LA TABELLA TABDAG
     * PER PRENDERE IL VALORE DI UN METADATO
     */

    public function getJoinCampiTabDag($Tabella = 'REGISTRO_PROTO') {
        $sqlJoin = '';
        foreach (self::$ElencoChiaviAttiveTabDag as $key => $valore) {
            if ($valore) {
                $valore = self::FONTE_DATI_REGISTRO . '_' . $valore;
                $sqlJoin.="
                        LEFT OUTER JOIN
                            TABDAG AS TABDAG_$valore 
                        ON 
                            TABDAG_$valore.TDCLASSE='ANAPRO' AND    
                            $Tabella.ROWID=TABDAG_$valore.TDROWIDCLASSE AND 
                            TABDAG_$valore.TDAGCHIAVE= '" . $key . "' AND 
                            TABDAG_$valore.TDAGFONTE='" . self::FONTE_DATI_REGISTRO . "' ";
            }
        }
        /*
         * Campi virtuali esito
         */
        foreach (self::$ElencoChiaviAttiveTabDagEsito as $key => $valore) {
            if ($valore) {
                $valore = self::FONTE_DATI_ESITO_CONSERVAZIONE . '_' . $valore;
                $sqlJoin.="
                        LEFT OUTER JOIN
                            TABDAG AS TABDAG_$valore 
                        ON 
                            TABDAG_$valore.TDCLASSE='ANAPRO' AND    
                            $Tabella.ROWID=TABDAG_$valore.TDROWIDCLASSE AND 
                            TABDAG_$valore.TDAGCHIAVE= '" . $key . "' AND 
                            TABDAG_$valore.TDAGFONTE='" . self::FONTE_DATI_ESITO_CONSERVAZIONE . "' ";
            }
        }

        return $sqlJoin;
    }

    /*
     * LA FUNZIONE, USATA INSIEME A getJoinCampiTabDag PERMETTE DI 
     * CREARE AUTOMATICAMENTE L'ELENCO DEI CAMPI DI TABDAG
     * PER PRENDERE IL VALORE DI UN METADATO
     */

    public function getCampiTabDag() {
        $sqlCampi = '';
        foreach (self::$ElencoChiaviAttiveTabDag as $key => $ValoreNomeCampo) {
            if ($ValoreNomeCampo) {
                $ValoreNomeCampo = self::FONTE_DATI_REGISTRO . '_' . $ValoreNomeCampo;
                $sqlCampi.=",TABDAG_$ValoreNomeCampo.TDAGVAL AS $ValoreNomeCampo ";
            }
        }
        /*
         * Nuovi campi virtuali esito
         */
        foreach (self::$ElencoChiaviAttiveTabDagEsito as $key => $ValoreNomeCampo) {
            if ($ValoreNomeCampo) {
                $ValoreNomeCampo = self::FONTE_DATI_ESITO_CONSERVAZIONE . '_' . $ValoreNomeCampo;
                $sqlCampi.=",TABDAG_$ValoreNomeCampo.TDAGVAL AS $ValoreNomeCampo ";
            }
        }
        return $sqlCampi;
    }

    public function getSqlElencoProtocolliGiornaliero($Giorno) {
        if (!$Giorno) {
            return '';
        }
        $anaent_48 = $this->proLib->GetAnaent('48');
        $CodiceRegistro = $this->proLib->GetCodiceRegistroProtocollo();
        $sql = $this->proLib->getSqlRegistro();
        $sql.=" LEFT OUTER JOIN ANADES ANADES ON ANAPRO.PRONUM=ANADES.DESNUM AND ANAPRO.PROPAR=ANADES.DESPAR";
        /* Controllo se parametro Doc Formali Unico Progressivo Attivo 
         * Nel caso in cui è attivo, occorre prendere anche le C e CA
         */
        if ($anaent_48['ENTDE4']) {
            $sql.=" WHERE (ANAPRO.PROPAR='A' OR ANAPRO.PROPAR='P' OR ANAPRO.PROPAR='C') ";
        } else {
            $sql.=" WHERE (ANAPRO.PROPAR='A' OR ANAPRO.PROPAR='P' ) "; // Rimossi C e CA, non servono 
        }
        $sql.=" AND ANAPRO.PRODAR='" . $Giorno . "' ";
        $sql.=" ORDER BY ANAPRO.PRONUM ";
        $sqlBase = "SELECT PROTOCOLLI.*,
                            '$CodiceRegistro' AS PROREGISTRO,
                            (SELECT DOCSHA2 
                                FROM ANADOC 
                                WHERE DOCNUM=PROTOCOLLI.PRONUM AND 
                                DOCPAR=PROTOCOLLI.PROPAR AND DOCTIPO = '' AND
                                DOCSERVIZIO = 0 LIMIT 1 ) AS DOCSHA2
                    FROM ( $sql ) PROTOCOLLI   
                 ";
        return $sqlBase;
    }

    public function getSqlElencoProtocolliModificatiGiorno($Giorno) {
        if (!$Giorno) {
            return '';
        }
        $anaent_48 = $this->proLib->GetAnaent('48');
        $CodiceRegistro = $this->proLib->GetCodiceRegistroProtocollo();
        $sql = $this->proLib->getSqlRegistro();
        $sql.=" LEFT OUTER JOIN ANADES ANADES ON ANAPRO.PRONUM=ANADES.DESNUM AND ANAPRO.PROPAR=ANADES.DESPAR";
        /* Controllo se parametro Doc Formali Unico Progressivo Attivo 
         * Nel caso in cui è attivo, occorre prendere anche le C e CA
         */
        if ($anaent_48['ENTDE4']) {
            $sql.=" WHERE (ANAPRO.PROPAR='A' OR ANAPRO.PROPAR='P' OR ANAPRO.PROPAR='C') ";
        } else {
            $sql.=" WHERE (ANAPRO.PROPAR='A' OR ANAPRO.PROPAR='P' ) "; // Rimossi C e CA, non servono 
        }
        $sql.=" AND ANAPRO.PRORDA='" . $Giorno . "' AND ANAPRO.PRODAR <>'" . $Giorno . "' ";
        $sql.=" GROUP BY ANAPRO.ROWID ORDER BY ANAPRO.PRONUM "; //Group non necessario?..
        $sqlBase = "SELECT PROTOCOLLI.*,
                            '$CodiceRegistro' AS PROREGISTRO,
                            (SELECT DOCSHA2 
                                FROM ANADOC 
                                WHERE DOCNUM=PROTOCOLLI.PRONUM AND 
                                DOCPAR=PROTOCOLLI.PROPAR AND DOCTIPO = '' AND
                                DOCSERVIZIO = 0 LIMIT 1 ) AS DOCSHA2
                    FROM ( $sql ) PROTOCOLLI   
                 ";
        return $sqlBase;
    }

    public function GeneraPDFGiornaliero($Giorno) {
        $Anaent_rec = $this->proLib->GetAnaent('2');
        $Anaent_26 = $this->proLib->GetAnaent('26');

        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
        $itaJR = new itaJasperReport();
        $sql = $this->getSqlElencoProtocolliGiornaliero($Giorno);
        $parametriRegistro = $this->getParametriRegistro();
        /*
         * Questo parametro è per stampare il registro giornaliero
         * insieme al registro delle modifiche in un unico pdf.
         * Parametro utile se c'è qualche problema nel inviare in conservazione
         * più di un allegato.
         * 
         */
        $ReportUnico = false;
        if ($ReportUnico) {
//            $RegistroMod = '0';
//            if ($parametriRegistro['REGISTROMODIFICHE']) {
            $RegistroMod = '1';
//            }
            $sqlMod = $this->getSqlElencoProtocolliModificatiGiorno($Giorno);
            $parameters = array("Sql" => $sql,
                "Titolo" => "REGISTRO GIORNALIERO DEL PROTOCOLLO DEL ",
                "COD_AMM" => $Anaent_26['ENTDE1'] . ' - ' . $Anaent_26['ENTDE2'],
                "Ente" => $Anaent_rec['ENTDE1'],
                "Giorno" => date("d/m/Y", strtotime($Giorno)),
                "RegistroModifiche" => $RegistroMod, //Nuovo per reg modifiche
                "Data" => $Giorno, //Nuovo per reg modifiche
                "SqlMod" => $sqlMod, //Nuovo per reg modifiche
                "TitoloMod" => "REGISTRO DELLE MODIFICHE DEI PROTOCOLLI EFFETTUATE IL " //Nuovo per reg modifiche
            );
            $ContenutoFile = $itaJR->getSQLReportPDF($this->proLib->getPROTDB(), 'proGiornaliero_1', $parameters); //Nuovo per reg modifiche
        } else {
            $parameters = array("Sql" => $sql,
                "Titolo" => "REGISTRO GIORNALIERO DEL PROTOCOLLO DEL ",
                "COD_AMM" => $Anaent_26['ENTDE1'] . ' - ' . $Anaent_26['ENTDE2'],
                "Ente" => $Anaent_rec['ENTDE1'],
                "Giorno" => date("d/m/Y", strtotime($Giorno))
            );
            $ContenutoFile = $itaJR->getSQLReportPDF($this->proLib->getPROTDB(), 'proGiornaliero', $parameters);
        }
        if (!$ContenutoFile) {
            return false;
        }
        $subPath = "proGiornaliero-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);
        $destFile = $tempPath . '/REGISTRO_' . $Giorno . '_' . md5(rand() * time()) . ".pdf";

        $ptr = fopen($destFile, 'wb');
        fwrite($ptr, $ContenutoFile);
        fclose($ptr);
        return $destFile;
    }

    public function GeneraPDFModificheGiorno($Giorno) {
        $Anaent_rec = $this->proLib->GetAnaent('2');
        $Anaent_26 = $this->proLib->GetAnaent('26');

        include_once(ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php');
        $itaJR = new itaJasperReport();
        $sql = $this->getSqlElencoProtocolliModificatiGiorno($Giorno);

        $parameters = array("Sql" => $sql,
            "Titolo" => "REGISTRO DELLE MODIFICHE DEI PROTOCOLLI EFFETTUATE IL ",
            "COD_AMM" => $Anaent_26['ENTDE1'] . ' - ' . $Anaent_26['ENTDE2'],
            "Ente" => $Anaent_rec['ENTDE1'],
            "Giorno" => date("d/m/Y", strtotime($Giorno)),
            "Data" => $Giorno
        );
        $ContenutoFile = $itaJR->getSQLReportPDF($this->proLib->getPROTDB(), 'proGiornalieroModifiche', $parameters);
        if (!$ContenutoFile) {
            return false;
        }
        $subPath = "proGiornaliero-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);
        $destFile = $tempPath . '/MODIFICHE_REGISTRO_' . $Giorno . '_' . md5(rand() * time()) . ".pdf";

        $ptr = fopen($destFile, 'wb');
        fwrite($ptr, $ContenutoFile);
        fclose($ptr);
        return $destFile;
    }

    public function ProtocollaRegistroGiornaliero($Giorno, $FileAllegato, $ExtraDati = array()) {
        $ParametriRegistro = $this->getParametriRegistro();
        if (!$ParametriRegistro['TIPODOCUMENTO']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Tipo Documento Registro Giornaliero NON definito.");
            return false;
        }
        $profilo = proSoggetto::getProfileFromIdUtente();
        if (!$profilo) {
            return false;
        }

        /*
         * Controllo permessi di scrittura:
         */
        $AnnoCheck = date('Y');
        if (!$this->proLib->CheckDirectory($AnnoCheck . '000000', 'C')) {
            $this->setErrCode(-1);
            $this->setErrMessage("Non si dispone dei permessi necessari di scrittura nella cartella di protocollazione.");
            return false;
        }

        /*
         * Oggetto
         */
        $sql = $this->getSqlElencoProtocolliGiornaliero($Giorno);
        $Anapro_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        if ($Anapro_tab) {
            $Iniziale = reset($Anapro_tab);
            $Finale = end($Anapro_tab);

            $DescrizioneRange = ' DAL NUMERO ' . intval(substr($Iniziale['PRONUM'], 4)) . ' AL NUMERO  ' . intval(substr($Finale['PRONUM'], 4));
        } else {
            $DescrizioneRange = ' NESSUN PROTOCOLLO PRESENTE ';
        }

        $DatiOggetto = array();
        $AnaTipoDoc_rec = $this->proLib->GetAnaTipoDoc($ParametriRegistro['TIPODOCUMENTO'], 'codice');
        $Oggetto = 'REGISTRO GIORNALIERO DEL PROTOCOLLO DEL ';
        if ($AnaTipoDoc_rec['OGGASSOCIATO']) {
            $anadog_rec = $this->proLib->GetAnadog($AnaTipoDoc_rec['OGGASSOCIATO'], 'codice');
            if ($anadog_rec) {
                $DatiOggetto = $this->DecodificaOggetto($AnaTipoDoc_rec['OGGASSOCIATO'], 'codice');
            }
        }
        $Oggetto.=' ' . date("d/m/Y", strtotime($Giorno)) . $DescrizioneRange;
        /**
         * Assegno meta-elementi per oggetto dati protocollo
         */
        $elementi = array();
        /**
         * Dati principali
         */
        $elementi['tipo'] = 'C';
        $elementi['dati'] = array();
        $elementi['dati']['TipoDocumento'] = $ParametriRegistro['TIPODOCUMENTO'];
        $elementi['dati']['Oggetto'] = $Oggetto;
        $elementi['dati']['NumeroAntecedente'] = '';
        $elementi['dati']['AnnoAntecedente'] = '';
        $elementi['dati']['TipoAntecedente'] = '';
        $elementi['dati']['ProtocolloMittente']['Numero'] = '';
        $elementi['dati']['ProtocolloMittente']['Data'] = '';
        $elementi['dati']['DataArrivo'] = date('Ymd');
        $elementi['dati']['ProtocolloEmergenza'] = '';
        /**
         * Ufficio Operatore di protocollazione
         *
         */
        $codiceOperatore = proSoggetto::getCodiceSoggettoFromIdUtente();
        if (!$codiceOperatore) {
            $this->setErrCode(-1);
            $this->setErrMessage("Utente senza profilo protocollazione.");
            return false;
        }
        /*
         * Firmatario FIRMATARIO UFFICIO_FIRMATARIO
         */
        $Firmatario = $ParametriRegistro['FIRMATARIO'];
        $FirmatarioUfficio = $ParametriRegistro['UFFICIO_FIRMATARIO'];
        if (!$Firmatario || !$FirmatarioUfficio) {
            $this->setErrCode(-1);
            $this->setErrMessage("Dati Firmatario mancanti o incompleti.");
            return false;
        }
        $anamed_rec = $this->proLib->GetAnamed($Firmatario);
        if (!$anamed_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Codice " . $Firmatario . " non trovato nell'anagrafica dei mittenti/destinatari.");
            return false;
        }
        /*
         * Controlli profilo
         */
        App::log($Firmatario);
        $ruoli = proSoggetto::getRuoliFromCodiceSoggetto($Firmatario);
        if (!$ruoli) {
            $this->setErrCode(-1);
            $this->setErrMessage('Nessun ufficio trovato per il soggetto  ' . $Firmatario);
            return false;
        }
        $trovato = false;
        foreach ($ruoli as $ruolo) {
            if ($FirmatarioUfficio == $ruolo['CODICEUFFICIO']) {
                $trovato = true;
            }
        }
        if (!$trovato) {
            $this->setErrCode(-1);
            $this->setErrMessage('Nessun ufficio corrispondente per il firmatario ' . $Firmatario);
            return false;
        }
        $elementi['firmatari']['firmatario'][0]['CodiceDestinatario'] = $Firmatario;
        $elementi['firmatari']['firmatario'][0]['Denominazione'] = $anamed_rec['MEDNOM'];
        $elementi['firmatari']['firmatario'][0]['Ufficio'] = $FirmatarioUfficio;
        /**
         * Classificazione
         */
        $Titolario = '';
        if ($ParametriRegistro['CATEGORIA']) {
            $Titolario = $ParametriRegistro['CATEGORIA'];
            if ($ParametriRegistro['CLASSIFICAZIONE']) {
                $Titolario.='.' . $ParametriRegistro['CLASSIFICAZIONE'];
                if ($ParametriRegistro['SOTTOCLASSE']) {
                    $Titolario.='.' . $ParametriRegistro['SOTTOCLASSE'];
                }
            }
        }
        $elementi['dati']['Classificazione'] = $Titolario;
        /**
         * Dati Spedizione in partenza
         */
        $elementi['dati']['TipoSpedizione'] = $ExtraDati['tipoSpedizione'];
        $elementi['dati']['Spedizioni']['TipoSpedizione'] = $ExtraDati['tipoSpedizione'];
        $elementi['dati']['Spedizioni']['NumeroRaccomandata'] = $ExtraDati['numeroRaccomandata'];
        $elementi['dati']['Spedizioni']['Grammi'] = $ExtraDati['grammi'];
        $elementi['dati']['Spedizioni']['Quantita'] = $ExtraDati['quantita'];
        $elementi['dati']['Spedizioni']['DataSpedizione'] = $ExtraDati['dataSpedizione'];
        /*
         * Trasmissioni interne dall'oggetto.
         */
        $trasmissioni = array();
        if ($DatiOggetto) {
            $trasmissioni = $DatiOggetto['Trasmissioni'];
        }
        foreach ($trasmissioni as $key => $trasmissione) {
            $anamed_rec = $this->proLib->GetAnamed($trasmissione['codiceDestinatario']);
            if (!$anamed_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Codice " . $trasmissione['codiceDestinatario'] . " non trovato nell'anagrafica dei mittenti.");
                return false;
            }
            $elementi['destinatari'][$key]['CodiceDestinatario'] = $trasmissione['codiceDestinatario'];
            $elementi['destinatari'][$key]['Denominazione'] = $anamed_rec['MEDNOM'];
            $elementi['destinatari'][$key]['Indirizzo'] = $anamed_rec['MEDIND'];
            $elementi['destinatari'][$key]['CAP'] = $anamed_rec['MEDCAP'];
            $elementi['destinatari'][$key]['Citta'] = $anamed_rec['MEDCIT'];
            $elementi['destinatari'][$key]['Provincia'] = $anamed_rec['MEDPRO'];
            $elementi['destinatari'][$key]['Annotazioni'] = $trasmissione['oggettoTrasmissione'];
            $elementi['destinatari'][$key]['Email'] = $anamed_rec['MEDEMA'];
            $elementi['destinatari'][$key]['Ufficio'] = $trasmissione['codiceUfficio'];
            $elementi['destinatari'][$key]['Responsabile'] = $trasmissione['responsabile'];
            $elementi['destinatari'][$key]['Gestione'] = $trasmissione['gestione'];
            /**
             * Controlli profilo
             */
            $ruoli = proSoggetto::getRuoliFromCodiceSoggetto($trasmissione['codiceDestinatario']);
            if (!$ruoli) {
                $this->setErrCode(-1);
                $this->setErrMessage('Nessun ufficio trovato per il soggetto ' . $trasmissione['codiceDestinatario']);
                return false;
            }
            $trovato = false;
            foreach ($ruoli as $ruolo) {
                if ($trasmissione['codiceUfficio'] == $ruolo['CODICEUFFICIO']) {
                    $trovato = true;
                }
            }
            if (!$trovato) {
                $this->setErrCode(-1);
                $this->setErrMessage('Nessun ufficio corrispondente per il soggetto ' . $trasmissione['codiceDestinatario']);
                return false;
            }
        }
        /*
         * allegato
         */

        $fh = fopen($FileAllegato, 'rb');
        if (!$fh) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore nell'estrarre il file binario dell'allegato. $FileAllegato");
            return false;
        }
        $binary = fread($fh, filesize($FileAllegato));
        fclose($fh);
        $binary = base64_encode($binary);
        $NomeFile = pathinfo($FileAllegato, PATHINFO_BASENAME);

        $elementi['allegati']['Principale'] = array();
        $elementi['allegati']['Principale']['Nome'] = $NomeFile;
        $elementi['allegati']['Principale']['Stream'] = $binary;
        $elementi['allegati']['Principale']['Descrizione'] = 'REGISTRO PROTOCOLLO DEL ' . date("d-m-Y", strtotime($Giorno));
        /*
         * Controllo se ci sono altri allegati:
         */
        if ($ExtraDati['AltriAllegati']) {
            foreach ($ExtraDati['AltriAllegati'] as $AllegatoExtra) {
                $fh = fopen($AllegatoExtra['FILEPATH'], 'rb');
                if (!$fh) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore nell'estrarre il file binario dell'allegato. $FileAllegato");
                    return false;
                }
                $binary = fread($fh, filesize($AllegatoExtra['FILEPATH']));
                fclose($fh);
                $binary = base64_encode($binary);
                $elementoAllegato = array();
                $elementoAllegato['Documento']['Nome'] = $AllegatoExtra['NOMEFILE'];
                $elementoAllegato['Documento']['Stream'] = $binary;
                $elementoAllegato['Documento']['Descrizione'] = $AllegatoExtra['NOMEFILE'];
                $elementi['allegati']['Allegati'][] = $elementoAllegato;
            }
        }

        /**
         * Istanza Oggetto ProtoDatiProtocollo
         */
        $model = 'proDatiProtocollo.class'; // @TODO cambiata per provare nuove funzioni senza creare problemi al SUAP
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proDatiProtocollo = new proDatiProtocollo();
        $ret_id = $proDatiProtocollo->assegnaDatiDaElementi($elementi);
        if ($ret_id === false) {
            $this->setErrCode(-1);
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            return false;
        }
        /**
         * Attiva Controlli su proDatiProtocollo
         */
        if (!$proDatiProtocollo->controllaDati()) {
            $this->setErrCode(-1);
            $this->setErrMessage($proDatiProtocollo->getTitle() . " " . $proDatiProtocollo->getmessage());
            return false;
        }
        /**
         * Lancia il protocollatore con i dati impostati
         */
        $model = 'proProtocolla.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $proProtocolla = new proProtocolla();
        $ret_id = $proProtocolla->registraPro('Aggiungi', '', $proDatiProtocollo);
        if ($ret_id === false) {
            $this->setErrCode(-1);
            $this->setErrMessage($proProtocolla->getTitle() . " " . $proProtocolla->getmessage());
            return false;
        }
        $Anapro_rec = $this->proLib->GetAnapro($ret_id, 'rowid');

        $result = array();
        $result['datiProtocollo']['rowidProtocollo'] = $Anapro_rec['ROWID'];
        $result['datiProtocollo']['annoProtocollo'] = substr($Anapro_rec['PRONUM'], 0, 4);
        $result['datiProtocollo']['numeroProtocollo'] = substr($Anapro_rec['PRONUM'], 4);
        $result['datiProtocollo']['tipoProtocollo'] = $Anapro_rec['PROPAR'];
        $result['datiProtocollo']['dataProtocollo'] = $Anapro_rec['PRODAR'];
        $result['datiProtocollo']['segnatura'] = $Anapro_rec['PROSEG'];
        return $result;
    }

    private function DecodificaOggetto($codice, $tipo = 'codice') {
        $Dati = array();
        $Trasmissioni_tab = array();
        $anadog_rec = $this->proLib->GetAnadog($codice, $tipo);
        if ($anadog_rec) {
            if ($anadog_rec['DOGUFF']) {
                $uffici = explode("|", $anadog_rec['DOGUFF']);
                foreach ($uffici as $ufficio_soggetto) {
                    list($ufficio, $soggetto) = explode('@', $ufficio_soggetto);
                    $anauff_rec = $this->proLib->GetAnauff($ufficio);
                    if (!$soggetto) {
                        $retTrasmissioni_tab = $this->caricaUfficio($anauff_rec, $Trasmissioni_tab);
// SE TORNA FALSE PERCHE UFFICIO SCADUTO O ANNULLATO O ANAMED TRASM INTERNA INESISTENTE
// DEVO AVVISARE E FERMARE L'INTERO PROGRAMMA?
                        if ($retTrasmissioni_tab) {
                            $Trasmissioni_tab = $retTrasmissioni_tab;
                        }
                    } else {
                        $uffdes_rec = $this->proLib->GetUffdes(array('UFFKEY' => $soggetto, 'UFFCOD' => $ufficio), 'ruolo', true, '', true);
                        if ($uffdes_rec) {
                            $retTrasmissioni_tab = $this->caricaTrasmissioneInterna($soggetto, 'codice', $ufficio, '', $uffdes_rec['UFFFI1__1'], $Trasmissioni_tab);
// SE TORNA FALSE PERCHE ANAMED DELLA TRASM INTERNA NON PRESENTE
// DEVO AVVISARE E FERMARE L'INTERO PROGRAMMA?
                            if ($retTrasmissioni_tab) {
                                $Trasmissioni_tab = $retTrasmissioni_tab;
                            }
                        }
                    }
                }
            }
        }
        $Dati['Trasmissioni'] = $Trasmissioni_tab;
        return $Dati;
    }

    private function caricaTrasmissioneInterna($codice, $tipo = 'codice', $uffcod = '', $responsabile = '', $gestisci = '', $Trasmissioni_tab = array()) {
        $anamed_rec = $this->proLib->GetAnamed($codice, $tipo, 'no', false, true);
        if (!$anamed_rec) {
            return false;
        }
        $anauff_rec = $this->proLib->GetAnauff($uffcod);
        if ($anauff_rec['UFFRES'] == $anamed_rec['MEDCOD']) {
            $responsabile = true;
        }
        $presente = false;
        $presenteKey = null;
        foreach ($Trasmissioni_tab as $key => $value) {
            if ($anamed_rec['MEDCOD'] == $value['codiceDestinatario'] && $uffcod == $value['codiceUfficio']) {
                $presente = true;
                $presenteKey = $key;
                break;
            }
        }
        /*
         *  Carico la trasmissione interna:
         */
        if (!$presente) {
            $Trasmissione = array();
            $Trasmissione['codiceDestinatario'] = $anamed_rec['MEDCOD'];
            $Trasmissione['oggettoTrasmissione'] = ''; // OggettO?
            $Trasmissione['codiceUfficio'] = $uffcod;
            $Trasmissione['responsabile'] = $responsabile;
            $Trasmissione['gestione'] = $gestisci;
            $Trasmissioni_tab[] = $Trasmissione;
        }
        return $Trasmissioni_tab;
    }

    private function caricaUfficio($anauff_rec, $Trasmissioni_tab) {
        if (!$anauff_rec) {
            return false;
        }
        if ($anauff_rec['UFFANN'] == 1) {
            return false;
        }
        $uffdes_tab = $this->proLib->GetUffdes($anauff_rec['UFFCOD'], 'uffcod', true, ' ORDER BY UFFFI1__3 DESC', true);
        foreach ($uffdes_tab as $uffdes_rec) {
            $retTrasmissioni_tab = $this->caricaTrasmissioneInterna($uffdes_rec['UFFKEY'], 'codice', $uffdes_rec['UFFCOD'], '', $uffdes_rec['UFFFI1__1'], $Trasmissioni_tab);
            if ($retTrasmissioni_tab) {
                $Trasmissioni_tab = $retTrasmissioni_tab;
            }
        }
        return $Trasmissioni_tab;
    }

    /**
     * Estrae i giorni per i quali si necessita la stampa del registro giornaliero
     */
    public function getGiorniRegistro() {
        $arrGiorni = array();
        $parametriRegistro = $this->getParametriRegistro();
        $Classe = 'ANAPRO';
        $Fonte = proLibGiornaliero::FONTE_DATI_REGISTRO;
        $ChiaveData = proLibGiornaliero::CHIAVE_DATA_REGISTRO;
        $sqlRegistro = "
               SELECT DISTINCT(TDAGVAL) AS GIORNO 
                    FROM TABDAG 
                LEFT OUTER JOIN ANAPRO ANAPRO ON TABDAG.TDROWIDCLASSE=ANAPRO.ROWID 
                WHERE ANAPRO.PROPAR='C' AND
                ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " AND
                TDCLASSE = '$Classe' AND
                TDAGFONTE = '$Fonte' AND 
                TDAGCHIAVE='$ChiaveData' AND 
                TDAGVAL >= '{$parametriRegistro['DATALIMITEINIZIALE']}' 
            ";
        $Tabdag_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sqlRegistro, true);
        foreach ($Tabdag_tab as $Tabdag_rec) {
            $ElencoGiorniRegistro[] = $Tabdag_rec['GIORNO'];
        }

        $Oggi = date('Ymd');
        $tmpDate = $parametriRegistro['DATALIMITEINIZIALE'];
        $ggDiff = itaDate::dateDiffDays($Oggi, $parametriRegistro['DATALIMITEINIZIALE']);

        for ($i = 1; $i <= $ggDiff; $i++) {
            if (!in_array($tmpDate, $ElencoGiorniRegistro)) {
                $arrGiorni[] = $tmpDate;
            }
            $tmpDate = itaDate::addDays($tmpDate, 1);
        }
        /*
         * Controllo se parametro No Registri Vuoti
         * E nel caso continuo:
         */
        foreach ($arrGiorni as $key => $Giorno) {
            if ($parametriRegistro['NOREGISTROVUOTO']) {
                // Qui controllo se sono state fatte modifiche:
                // Ne testo solo 1: 
                $sqlDelGiorno = $this->getSqlElencoProtocolliGiornaliero($Giorno);
                $AnaproDelGiorno_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sqlDelGiorno, false);
                if (!$AnaproDelGiorno_rec) {
                    // Se sono presenti modifiche nel giorno, lo genero lo stesso REGISTROMODIFICHE
                    if ($parametriRegistro['REGISTROMODIFICHE']) {
                        $sqlModificheDelGiorno = $this->getSqlElencoProtocolliModificatiGiorno($Giorno);
                        $AnaproModificheDelGiorno_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sqlModificheDelGiorno, false);
                        if ($AnaproModificheDelGiorno_rec) {
                            continue;
                        }
                    }
                    unset($arrGiorni[$key]);
                }
            }
        }
        return $arrGiorni;
    }

    public function MettiAllegatiAllaFirma($Anapro_rec) {
        $parametriRegistro = $this->getParametriRegistro();
        if (!$parametriRegistro['FIRMATARIO'] || !$parametriRegistro['UFFICIO_FIRMATARIO']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Dati mancati per il Firmatario.");
            return false;
        }
        $Anadoc_tab = $this->proLib->GetAnadoc($Anapro_rec['PRONUM'], 'codice', true, $Anapro_rec['PROPAR']);
        foreach ($Anadoc_tab as $Anadoc_rec) {
            if (!$this->DaFirmare($Anadoc_rec, $parametriRegistro['FIRMATARIO'], $parametriRegistro['UFFICIO_FIRMATARIO'])) {
                return false;
            }
// Sincronizzo l'iter
            $iter = proIter::getInstance($this->proLib, $Anapro_rec);
            if (!$iter->sincronizzaIterFirma('aggiungi')) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in sincronizzazione Iter. " . $iter->getLastMessage());
                return false;
            }
        }
        return true;
    }

    /*
     * Funzione copiata da proLibAllegati perchè utilizza out msg.
     */

    public function DaFirmare($Anadoc_rec, $firmatario, $ufficio_firmatario) {
        $docfirma_tab = $this->proLibAllegati->GetDocfirma($Anadoc_rec['ROWID'], 'rowidanadoc', true);
        if ($docfirma_tab) {
            $this->setErrCode(-1);
            $this->setErrMessage("Verifica la richiesta di firma. ID: " . $Anadoc_rec['ROWID']);
            return false;
        }
        $docfirma_rec = array();
        $profilo = proSoggetto::getProfileFromIdUtente();
        $docfirma_rec['FIRCODRICH'] = $profilo['COD_SOGGETTO'];
        $docfirma_rec['FIRDATARICH'] = date('Ymd');
        $docfirma_rec['FIRCOD'] = $firmatario;
        $docfirma_rec['FIRUFF'] = $ufficio_firmatario;
        $docfirma_rec['ROWIDANADOC'] = $Anadoc_rec['ROWID'];
        $docfirma_rec['ROWIDARCITE'] = 0;
        $docfirma_rec['FIRDATA'] = "";
        $docfirma_rec['FIRORA'] = "";
        try {
            ItaDB::DBInsert($this->proLib->getPROTDB(), 'DOCFIRMA', 'ROWID', $docfirma_rec);
        } catch (Exception $exc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Richiesta di firma non inserita." . $exc->getMessage());
            return false;
        }
        return true;
    }

    /**
     *
     * @param <type> ExportFileRegistroGiornaliero
     * @return <type>
     */
    public function ExportFileRegistrioGiornaliero($anapro_rec, $verbose = false) {
        $parametriRegistro = $this->getParametriRegistro();
        $TipoProt = $anapro_rec['PROPAR'];
        if ($TipoProt != 'C') {
            return true;
        }
        /*
         * Dati repository di destinazione
         */
        $anaent_41 = $this->proLib->GetAnaent('41');
        $dest_param = trim($anaent_41['ENTDE3']);
        if (!$dest_param) {
            return true;
        }
        // Controllo nei metadati:
        // Data Firma
        // Data Registro
        $proLibTabDag = new proLibTabDag();

        /**
         * Data Registro Giornaliero
         */
        $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rec['ROWID'], self::CHIAVE_DATA_REGISTRO, '', false, '', self::FONTE_DATI_REGISTRO);
        if (!$TabDag_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Attenzione, impossibile trovare il metadato " . self::CHIAVE_DATA_REGISTRO . " del registro giornaliero.");
            return false;
        }
        $DataRegistro = $TabDag_rec['TDAGVAL'];
        if (!$DataRegistro) {
            $this->setErrCode(-1);
            $this->setErrMessage("Data del registro non valorizzata.");
            return false;
        }
        /**
         * Data Firma
         */
        if ($parametriRegistro['METTIALLAFIRMA']) {
            $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $anapro_rec['ROWID'], self::CHIAVE_DATA_FIRMA, '', false, '', self::FONTE_DATI_REGISTRO);
            if (!$TabDag_rec) {
                $this->setErrCode(-1);
                $this->setErrMessage("Attenzione, impossibile trovare il metadato " . self::CHIAVE_DATA_FIRMA . " del registro giornaliero.");
                return false;
            }
            $DataFirma = $TabDag_rec['TDAGVAL'];
            if (!$DataFirma) {
                $this->setErrCode(-1);
                $this->setErrMessage("Data della firma non valorizzata.");
                return false;
            }
        }
        /*
         * Scorro gli allegati e li preparo per l'esportazione.
         */
        $anadoc_tab = $this->proLib->GetAnadoc($anapro_rec['PRONUM'], 'codice', true, $anapro_rec['PROPAR'], ' AND DOCSERVIZIO=0');
        $Anadoc_export = array();
        foreach ($anadoc_tab as $key => $anadoc_rec) {
            $FilePathSource = $this->proLibAllegati->CopiaDocAllegato($anadoc_rec['ROWID'], '', true);
            $Anadoc_export[] = array(
                "ROWID_ANADOC" => $anadoc_rec['ROWID'],
                "SOURCE" => $FilePathSource,
                "DEST" => $anadoc_rec['DOCNAME'],
                "SHA2" => hash_file('sha256', $FilePathSource)
            );

//            $protPath = $this->proLib->SetDirectory($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
//            $protPath = $this->proLib->SetDirectory($anapro_rec['PRONUM'], $anapro_rec['PROPAR']);
//            $Anadoc_export[] = array(
//                "ROWID_ANADOC" => $anadoc_rec['ROWID'],
//                "SOURCE" => $protPath . "/" . $anadoc_rec['DOCFIL'],
//                "DEST" => $anadoc_rec['DOCNAME'],
//                "SHA2" => hash_file('sha256', $protPath . "/" . $anadoc_rec['DOCFIL'])
//            );
        }

        if (!$Anadoc_export) {
            return true;
        }

        $urlSchema = parse_url($dest_param);
        switch (strtolower($urlSchema['scheme'])) {
            /**
             * file url
             */
            case 'file':
                $dest_path = $urlSchema['path'];
                if (!is_writable($dest_path)) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Cartella di destinazione inesistente.");
                    return false;
                }
                foreach ($Anadoc_export as $export) {
//                    if ($export['FORZACARICAMENTO']) {
//                        if (!@copy($export['SOURCE'], $dest_path . "/" . $export['DEST'])) {
//                            $this->setErrCode(-1);
//                            $this->setErrMessage("Copia export: {$export['DEST']}... File di Registro Giornaliero fallita");
//                            return false;
//                        }
//                        continue;
//                    }
                    if (!@copy($export['SOURCE'], $dest_path . "/" . $export['DEST'])) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Copia export: {$export['DEST']}... File di Registro Giornaliero fallita");
                        return false;
                    }
                    // 
                    // Metadati Info
                    // 
                    $ArrDati = array();
                    $ArrDati[self::CHIAVE_EXPORT_DATA] = date("Ymd");
                    $ArrDati[self::CHIAVE_EXPORT_ORA] = date('H:i:s');
                    $ArrDati[self::CHIAVE_EXPORT_SHA2] = $export['SHA2'];
                    $ArrDati[self::CHIAVE_EXPORT_REPOS] = $dest_param;
                    //InserisciTabDagGiornaliero
                    if (!$proLibTabDag->AggiornamentoTagGiornaliero($anapro_rec, $ArrDati)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore in aggiornamento metadati." . $proLibTabDag->getErrMessage());
                        return false;
                    }
                }
                return true;
                break;

            /**
             * ftp url
             */
            case 'ftp':
                if (!$urlSchema['host']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: host mancante");
                    return false;
                }

                if (!$urlSchema['user']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: utente mancante");
                    return false;
                }

                if (!$urlSchema['pass']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: password mancante");
                    return false;
                }

                if (!$urlSchema['path']) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("url ftp: cartella destinazione mancante");
                    return false;
                }
                $connId = ftp_connect($urlSchema['host']);
                if (!$connId) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Connessione ftp export file registro giornaliero fallita.");
                    return false;
                }
                if (!ftp_login($connId, $urlSchema['user'], $urlSchema['pass'])) {
                    ftp_close($connId);
                    $this->setErrCode(-1);
                    $this->setErrMessage("Accesso ftp export file registro giornaliero fallita");
                    return false;
                }
                if (!ftp_chdir($connId, $urlSchema['path'])) {
                    ftp_close($connId);
                    $this->setErrCode(-1);
                    $this->setErrMessage("Cartella di destinazione export file registro giornaliero fallita.");
                    return false;
                }
                foreach ($Anadoc_export as $export) {
//                    if ($export['FORZACARICAMENTO']) {
//                        if (!ftp_put($connId, $export['DEST'], $export['SOURCE'], FTP_BINARY)) {
//                            ftp_close($connId);
//                            $this->setErrCode(-1);
//                            $this->setErrMessage("Trasferimento Ftp file registro giornaliero : " . pathinfo($export['SOURCE'], PATHINFO_BASENAME) . " fallita");
//                            return false;
//                        }
//                        continue;
//                    }
                    if (!ftp_put($connId, $export['DEST'], $export['SOURCE'], FTP_BINARY)) {
                        ftp_close($connId);
                        $this->setErrCode(-1);
                        $this->setErrMessage("Trasferimento Ftp file registro giornaliero: " . pathinfo($export['SOURCE'], PATHINFO_BASENAME) . " fallita");
                        return false;
                    }
                    // Metadati Info
                    // 
                    $ArrDati = array();
                    $ArrDati[self::CHIAVE_EXPORT_DATA] = date("Ymd");
                    $ArrDati[self::CHIAVE_EXPORT_ORA] = date('H:i:s');
                    $ArrDati[self::CHIAVE_EXPORT_SHA2] = $export['SHA2'];
                    $ArrDati[self::CHIAVE_EXPORT_REPOS] = $dest_param;
                    //InserisciTabDagGiornaliero
                    if (!$proLibTabDag->AggiornamentoTagGiornaliero($anapro_rec, $ArrDati)) {
                        $this->setErrCode(-1);
                        $this->setErrMessage("Errore in aggiornamento metadati." . $proLibTabDag->getErrMessage());
                        return false;
                    }
                }
                ftp_close($connId);
                return true;
                break;
            default:
                $this->setErrCode(-1);
                $this->setErrMessage("Risorsa di destinazione non riconosciuta.");
                return false;
                break;
        }


        return false;
    }

    public function GeneraRegistri() {
        /*
         * Controlli Dati Fondamentali:
         */
        if (!$this->ControllaPresenzaDatiFondamentali()) {
            return false;
        }

        $DescrGiorni = 'Registro Giornaliero Generato per i giorni: ';
        $ElencoGiorni = $this->getGiorniRegistro();
        foreach ($ElencoGiorni as $Giorno) {
            $retGiorno = $this->GeneraSingoloGiorno($Giorno);
            if (!$retGiorno) {
                $MessaggioErr = 'Errore in Generazione del Giorno: ' . date("d/m/Y", strtotime($Giorno)) . '. ';
                $MessaggioErr.= $this->getErrMessage();
                $this->setErrMessage($MessaggioErr);
                return false;
            }
            $DescrGiorni.= date("d/m/Y", strtotime($Giorno)) . ', ';
        }
        return $DescrGiorni;
    }

    public function GeneraSingoloGiorno($Giorno) {
        // 1 Protocollo
        // 2 Creo il File Allegato
        // 3 
        //$Giorno = '20150924';
        $parametriRegistro = $this->getParametriRegistro();
        $altriAllegati = array();
        $retFile = $this->GeneraPDFGiornaliero($Giorno);
        if (!$retFile) {
            $this->setErrCode(-1);
            $Data = date("d/m/Y", strtotime($Giorno));
            $this->setErrMessage("Errore in generazione PDF proGiornaliero per il giorno $Data.");
            return false;
        }
        /*  Se Abilitato Registro Modifiche:
         *  Qui creo pdf modifiche del giorno e lo allego:
         * 
         */
        if ($parametriRegistro['REGISTROMODIFICHE']) {
//            $sqlModificheDelGiorno = $this->getSqlElencoProtocolliModificatiGiorno($Giorno);
//            $AnaproModificheDelGiorno_rec = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sqlModificheDelGiorno, false);
//            if ($AnaproModificheDelGiorno_rec) {
            $retFileMod = $this->GeneraPDFModificheGiorno($Giorno);
            if (!$retFileMod) {
                $this->setErrCode(-1);
                $Data = date("d/m/Y", strtotime($Giorno));
                $this->setErrMessage("Errore in generazione PDF Modifiche proGiornaliero per il giorno $Data.");
                return false;
            }
            $NomeFile = 'MODIFICHE_REGISTRO_' . $Giorno . '.pdf';
            $altriAllegati[] = array('FILEPATH' => $retFileMod, 'NOMEFILE' => $NomeFile);
//            }
        }
        /*
         * Protocollo:
         */
        $ExtraDati['AltriAllegati'] = $altriAllegati;
        $retProto = $this->ProtocollaRegistroGiornaliero($Giorno, $retFile, $ExtraDati);
        if (!$retProto) {
            return false;
        }
        $Anapro_rec = $this->proLib->GetAnapro($retProto['datiProtocollo']['rowidProtocollo'], 'rowid');
        $ArrDati = $this->GetArrMetaDati($Giorno, $Anapro_rec);
        /*
         * Metto alla firma l'allegato:
         */
        if ($parametriRegistro['METTIALLAFIRMA']) {
            if (!$this->MettiAllegatiAllaFirma($Anapro_rec)) {
                return false;
            }
        }

//        else {
//            if ($parametriRegistro['REPOSITORY']) {
//                if (!$this->ExportFileRegistrioGiornaliero($Anapro_rec)) {
//                    return false;
//                }
//            }
//        }
        include_once ITA_BASE_PATH . '/apps/Protocollo/proLibTabDag.class.php';
        $proLibTabDag = new proLibTabDag();
        if (!$proLibTabDag->InserisciTabDagGiornaliero($Anapro_rec, $ArrDati)) {
            $this->setErrCode(-1);
            $this->setErrMessage($proLibTabDag->getErrMessage());
            return false;
        }
// Nuova aggiunta per export file.      
//       if(!$parametriRegistro['METTIALLAFIRMA']){
//            if ($parametriRegistro['REPOSITORY']) {
//                if (!$this->ExportFileRegistrioGiornaliero($Anapro_rec)) {
//                    return false;
//                }
//            }
//        }
        return true;
    }

    public function GetArrMetaDati($Giorno, $Anapro_rec) {
        $accLib = new accLib();
        $sql = $this->getSqlElencoProtocolliGiornaliero($Giorno);
        $Anapro_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        $ParametriRegistro = $this->getParametriRegistro();
        $anamedResp_rec = $this->proLib->GetAnamed($ParametriRegistro['RESPONSABILE']); // Qui uso parametro del RESPONSABILE
        $Responsabile = $anamedResp_rec['MEDNOM'];
        $CF_Responsabile = $anamedResp_rec['MEDFIS'];
        $NumeroRegistri = count($Anapro_tab);

        $NumeroIniziale = $NumeroFinale = 0;
        $DataIniziale = $DataFinale = '';
        if ($Anapro_tab) {
            $Iniziale = reset($Anapro_tab);
            $Finale = end($Anapro_tab);
            $DataIniziale = $Iniziale['PRODAR'];
            $DataFinale = $Finale['PRODAR'];
            $NumeroIniziale = intval(substr($Iniziale['PRONUM'], 4));
            $NumeroFinale = intval(substr($Finale['PRONUM'], 4));
        }

        $utenti_rec = $accLib->GetUtenti($Anapro_rec['PROUTE'], 'utelog');
        $anamedProdut_rec = $this->proLib->GetAnamed($utenti_rec['UTEANA__1']);
        $SoggettoProduttore = $anamedProdut_rec['MEDNOM'];
        $CF_SoggettoProduttore = $anamedProdut_rec['MEDFIS'];

        $ArrDati = array();
        $ArrDati[proLibGiornaliero::CHIAVE_DATA_REGISTRO] = $Giorno;
        $ArrDati[proLibGiornaliero::CHIAVE_DATA_CREAZIONE] = date('Ymd');
        $ArrDati[proLibGiornaliero::CHIAVE_DATA_FIRMA] = '';
        $ArrDati[proLibGiornaliero::CHIAVE_EXPORT_DATA] = '';
        $ArrDati[proLibGiornaliero::CHIAVE_EXPORT_ORA] = '';
        $ArrDati[proLibGiornaliero::CHIAVE_EXPORT_SHA2] = '';
        $ArrDati[proLibGiornaliero::CHIAVE_EXPORT_REPOS] = '';
        $ArrDati[proLibGiornaliero::CHIAVE_NUMERO_INIZIALE] = $NumeroIniziale;
        $ArrDati[proLibGiornaliero::CHIAVE_NUMERO_FINALE] = $NumeroFinale;
        $ArrDati[proLibGiornaliero::CHIAVE_DATA_INIZIO_REGISTRAZIONE] = $DataIniziale;
        $ArrDati[proLibGiornaliero::CHIAVE_DATA_FINE_REGISTRAZIONE] = $DataFinale;
        $ArrDati[proLibGiornaliero::CHIAVE_RESPONSABILE] = $Responsabile;
        $ArrDati[proLibGiornaliero::CHIAVE_CF_RESPONSABILE] = $CF_Responsabile;
        $ArrDati[proLibGiornaliero::CHIAVE_NUMERO_DOCUMENTI_REGISTRATI] = $NumeroRegistri;
        $ArrDati[proLibGiornaliero::CHIAVE_SOGGETTO_PRODUTTORE] = $SoggettoProduttore;
        $ArrDati[proLibGiornaliero::CHIAVE_CF_SOGGETTO_PRODUTTORE] = $CF_SoggettoProduttore;

        return $ArrDati;
    }

    public function ControlliRegistri($Registri_tab = array()) {
        $retControlli = array();
        if (!$Registri_tab) {
            $sqlRegistri = $this->getSqlElencoRegistro();
            $Registri_tab = $Tabdag_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sqlRegistri, true);
        }
        $ArrGiorniMancanti = $this->getGiorniRegistro();
        /*
         *  Controllo Allegati Mancanti
         */
        $retControlli[self::CTR_GIORNI_MANCANTI] = $ArrGiorniMancanti;

        $retControlliAllegati = $this->ControlliSuAnapro($Registri_tab);
        $retControlli = array_merge($retControlli, $retControlliAllegati);
        return $retControlli;
    }

    private function ControlliSuAnapro($Anapro_tab) {
        $ParametriRegistro = $this->getParametriRegistro();

        /*
         * Inizializzo retArrControlli
         */
        $retArrControlli[self::CTR_ALLEGATO_MANCANTE] = array();
        $retArrControlli[self::CTR_ALLEGATO_DA_FIRMARE] = array();
        $retArrControlli[self::CTR_ALLEGATO_NON_ALLA_FIRMA] = array();
        $retArrControlli[self::CTR_ALLEGATO_NON_ESPORTATO] = array();
        $retArrControlli[self::CTR_ALLEGATO_HASH_ERRATO] = array();
        $retArrControlli[self::CTR_ALLEGATO_NO_METADATI] = array();
        $retArrControlli[self::CTR_ALLEGATO_NON_CONSERVATO] = array();
        $retArrControlli[self::CTR_DOCUMENTO_NON_CONSERVABILE] = array();

        foreach ($Anapro_tab as $key => $Anapro_rec) {
            /*
             * Estrazione allegati tramite funzione del manager
             */
            $Anadoc_tab = proConservazioneManagerHelper::GetAnadocDaConservare($Anapro_rec);
            if (proConservazioneManagerHelper::getAllegatiNonConservabili()) {
                $retArrControlli[self::CTR_ALLEGATO_NONCONS][$Anapro_rec['ROWID']] = 1;
            }


            /*
             * Controllo METADATI MANCANTI
             */
            $CampoDataRegistro = self::FONTE_DATI_REGISTRO . '_' . self::$ElencoChiaviAttiveTabDag[self::CHIAVE_DATA_REGISTRO];
            if (is_null($Anapro_rec[$CampoDataRegistro])) {
                $retArrControlli[self::CTR_ALLEGATO_NO_METADATI][$Anapro_rec['ROWID']] = 1;
//                continue;
            }

            //$Anadoc_tab = $this->proLib->GetAnadoc($Anapro_rec['PRONUM'], 'codice', true, $Anapro_rec['PROPAR']);

            /*
             * Cerco Allegati Mancanti
             */
            $DocumentoPrincipaleMancante = true;
            $DocumentoAllegatoMancante = false;
            if ($ParametriRegistro['REGISTROMODIFICHE']) {
                $DocumentoAllegatoMancante = true;
            }

            foreach ($Anadoc_tab as $Anadoc_rec) {
                if ($Anadoc_rec['DOCTIPO'] == '') {
                    $DocumentoPrincipaleMancante = false;
                }
                if ($Anadoc_rec['DOCTIPO'] == 'ALLEGATO' && $Anadoc_rec['DOCSERVIZIO'] == 0) {
                    $DocumentoAllegatoMancante = false;
                }
                if ($DocumentoPrincipaleMancante === false && $DocumentoAllegatoMancante === false) {
                    break;
                }
            }

            /*
             * Cerco documenti non alla firma
             */
            $DocumentoPrincipaleNonAllaFirma = true;
            $DocumentoAllegatoNonAllaFirma = false;
            if ($ParametriRegistro['REGISTROMODIFICHE']) {
                $DocumentoAllegatoNonAllaFirma = false;
            }
            foreach ($Anadoc_tab as $Anadoc_rec) {
                $docfirma_tab = $this->proLibAllegati->GetDocfirma($Anadoc_rec['ROWID'], 'rowidanadoc', true);
                if ($docfirma_tab) {
                    if ($Anadoc_rec['DOCTIPO'] == '') {
                        $DocumentoPrincipaleNonAllaFirma = false;
                    }
                    if ($Anadoc_rec['DOCTIPO'] == 'ALLEGATO' && $Anadoc_rec['DOCSERVIZIO'] == 0) {
                        $DocumentoAllegatoNonAllaFirma = false;
                    }
                }
                if ($DocumentoPrincipaleNonAllaFirma === false && $DocumentoAllegatoNonAllaFirma === false) {
                    break;
                }
            }

            /*
             * Cerco documenti firmati
             */
            $DocumentoPrincipaleFirmato = false;
            $DocumentoAllegatoFirmato = false;
            foreach ($Anadoc_tab as $Anadoc_rec) {
                if ($Anadoc_rec['DOCTIPO'] == '') {
                    if (strtolower(pathinfo($Anadoc_rec['DOCFIL'], PATHINFO_EXTENSION)) == 'p7m') {
                        $DocumentoPrincipaleFirmato = true;
                    }
                }
                if ($Anadoc_rec['DOCTIPO'] == 'ALLEGATO' && $Anadoc_rec['DOCSERVIZIO'] == 0) {
                    if (strtolower(pathinfo($Anadoc_rec['DOCFIL'], PATHINFO_EXTENSION)) == 'p7m') {
                        $DocumentoAllegatoFirmato = true;
                    }
                }
            }


            /*
             * Cerco documenti da firmare
             */
            $DocumentoPrincipaleDaFirmare = true;
            $DocumentoAllegatoDaFirmare = false;
            if ($ParametriRegistro['REGISTROMODIFICHE']) {
                $DocumentoAllegatoDaFirmare = true;
            }
            foreach ($Anadoc_tab as $Anadoc_rec) {
                if ($Anadoc_rec['DOCTIPO'] == '') {
                    if (strtolower(pathinfo($Anadoc_rec['DOCFIL'], PATHINFO_EXTENSION)) == 'p7m') {
                        $DocumentoPrincipaleDaFirmare = false;
                    }
                }
                if ($Anadoc_rec['DOCTIPO'] == 'ALLEGATO' && $Anadoc_rec['DOCSERVIZIO'] == 0) {
                    if (strtolower(pathinfo($Anadoc_rec['DOCFIL'], PATHINFO_EXTENSION)) == 'p7m') {
                        $DocumentoAllegatoDaFirmare = false;
                    }
                }
            }

            /*
             * CONTROLLI SU CONSERVAZIONE
             */
            $RegistroDaConservare = false;
            if ($ParametriRegistro['TIPOCONSERVAZIONE'] !== '') {
                $EsitoConservazione = $this->proLibConservazione->GetEsitoConservazioneValore($Anapro_rec['ROWID'], proLibConservazione::CHIAVE_ESITO_ESITO);
                App::log('$EsitoConservazione - ' . $Anapro_rec['PRONUM']);
                App::log($EsitoConservazione);
                if (!$EsitoConservazione) {
                    $RegistroDaConservare = true;
                }
                if ($EsitoConservazione == proLibConservazione::ESITO_NEGATIVO) {
                    $RegistroDaConservare = true;
                }
            }
            /*
             *  Allegati Mancanti
             */
            if ($DocumentoPrincipaleMancante && $DocumentoAllegatoMancante) {
                $retArrControlli[self::CTR_ALLEGATO_MANCANTE][$Anapro_rec['ROWID']] = 1;
//                continue;
            }


            /*
             *  Registro da Conservare
             */
            if ($RegistroDaConservare) {
                $retArrControlli[self::CTR_ALLEGATO_NON_CONSERVATO][$Anapro_rec['ROWID']] = 1;
//                continue;
            }

            /*
             *  Allegato non alla firma:
             */
            if ($ParametriRegistro['METTIALLAFIRMA']) {
                if ($DocumentoPrincipaleDaFirmare == true || $DocumentoAllegatoDaFirmare == true) {
                    if ($DocumentoPrincipaleNonAllaFirma || $DocumentoAllegatoNonAllaFirma) {
                        $retArrControlli[self::CTR_ALLEGATO_NON_ALLA_FIRMA][$Anapro_rec['ROWID']] = 1;
//                        continue;
                    }
                }
            }

            /*
             *  Allegato da firmare:
             */
            if ($ParametriRegistro['METTIALLAFIRMA']) {
                if ($DocumentoPrincipaleDaFirmare || $DocumentoAllegatoDaFirmare) {
                    $retArrControlli[self::CTR_ALLEGATO_DA_FIRMARE][$Anapro_rec['ROWID']] = 1;
//                    continue;
                }
            }
//            if ($ParametriRegistro['METTIALLAFIRMA']) {
//                $CampoDaFirmare = self::FONTE_DATI_REGISTRO . '_' . self::$ElencoChiaviAttiveTabDag[self::CHIAVE_DATA_FIRMA];
//                if (!$Anapro_rec[$CampoDaFirmare]) {
//                    $retArrControlli[self::CTR_ALLEGATO_DA_FIRMARE][$Anapro_rec['ROWID']] = 1;
//                    continue;
//                }
//            }
            /*
             * Allegato non presente su Repository
             * Se definito parametro repository
             */
            if ($ParametriRegistro['REPOSITORY']) {
                $CampoExportData = self::FONTE_DATI_REGISTRO . '_' . self::$ElencoChiaviAttiveTabDag[self::CHIAVE_EXPORT_DATA];
                if (!$Anapro_rec[$CampoExportData]) {
                    $retArrControlli[self::CTR_ALLEGATO_NON_ESPORTATO][$Anapro_rec['ROWID']] = 1;
//                    continue;
                }
            }
            /*
             * Controllo Hash del file:
             */
            //
            $DocumentoPrincipaleHashKO = false;
            $DocumentoAllegatoHashKO = false;
            if ($ParametriRegistro['REGISTROMODIFICHE']) {
                $DocumentoAllegatoDaFirmare = true;
            }
            foreach ($Anadoc_tab as $Anadoc_rec) {
                $hasFile = $this->proLibAllegati->GetHashDocAllegato($Anadoc_rec['ROWID'], 'sha256');
//                $protPath = $this->proLib->SetDirectory($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
//                $hasFile = hash_file('sha256', $protPath . "/" . $Anadoc_rec['DOCFIL']);
                // Out::msginfo('aa', 'confronto' . $hasFile . ' con  ' . $Anadoc_rec['DOCSHA2']);
                if ($Anadoc_rec['DOCTIPO'] == '' || $Anadoc_rec['DOCTIPO'] == 'PRINCIPALE') {
                    if ($hasFile != $Anadoc_rec['DOCSHA2']) {
                        $DocumentoPrincipaleHashKO = true;
                    }
                }
                if ($Anadoc_rec['DOCTIPO'] == 'ALLEGATO') {
                    if ($hasFile != $Anadoc_rec['DOCSHA2']) {
                        $DocumentoAllegatoHashKO = true;
                    }
                }
            }
            if ($DocumentoPrincipaleHashKO == true || $DocumentoAllegatoHashKO == true) {
                $retArrControlli[self::CTR_ALLEGATO_HASH_ERRATO][$Anapro_rec['ROWID']] = 1;
            }
            if ($Anapro_rec['PROSTATOPROT'] == proLib::PROSTATO_ANNULLATO) {
                $retArrControlli[self::CTR_DOCUMENTO_ANNULLATO][$Anapro_rec['ROWID']] = 1;
            }

            /*
             * Documenti non conservabili
             */
            if ($retArrControlli[self::CTR_ALLEGATO_MANCANTE][$Anapro_rec['ROWID']]) {
                $retArrControlli[self::CTR_DOCUMENTO_NON_CONSERVABILE][$Anapro_rec['ROWID']] = self::$ElencoDescrCtrAnomalie[self::CTR_ALLEGATO_MANCANTE];
            } else if ($retArrControlli[self::CTR_ALLEGATO_NO_METADATI][$Anapro_rec['ROWID']]) {
                $retArrControlli[self::CTR_DOCUMENTO_NON_CONSERVABILE][$Anapro_rec['ROWID']] = self::$ElencoDescrCtrAnomalie[self::CTR_ALLEGATO_NO_METADATI];
            } else if ($retArrControlli[self::CTR_ALLEGATO_DA_FIRMARE][$Anapro_rec['ROWID']]) {
                $retArrControlli[self::CTR_DOCUMENTO_NON_CONSERVABILE][$Anapro_rec['ROWID']] = self::$ElencoDescrCtrAnomalie[self::CTR_ALLEGATO_DA_FIRMARE];
            } else if ($retArrControlli[self::CTR_ALLEGATO_HASH_ERRATO][$Anapro_rec['ROWID']]) {
                $retArrControlli[self::CTR_DOCUMENTO_NON_CONSERVABILE][$Anapro_rec['ROWID']] = self::$ElencoDescrCtrAnomalie[self::CTR_ALLEGATO_HASH_ERRATO];
            } else if ($retArrControlli[self::CTR_DOCUMENTO_ANNULLATO][$Anapro_rec['ROWID']]) {
                $retArrControlli[self::CTR_DOCUMENTO_NON_CONSERVABILE][$Anapro_rec['ROWID']] = self::$ElencoDescrCtrAnomalie[self::CTR_DOCUMENTO_ANNULLATO];
            } else if ($retArrControlli[self::CTR_ALLEGATO_NONCONS][$Anapro_rec['ROWID']]) {
                $retArrControlli[self::CTR_DOCUMENTO_NON_CONSERVABILE][$Anapro_rec['ROWID']] = self::$ElencoDescrCtrAnomalie[self::CTR_ALLEGATO_NONCONS];
            }
        }
        App::log('$retArrControlli');
        App::log($retArrControlli);
        return $retArrControlli;
    }

    public function SendMail($Errore, $subject = null, $bodyHeader = null) {
        //CENTRALIZZARE SENDMAIL
    }

    public function SendMailErrore($Errore, $subject = null, $bodyHeader = null) {
        $Account = '';
        $devLib = new devLib();
        $ItaEngine_mail_rec = $devLib->getEnv_config('ITAENGINE_EMAIL', 'codice', 'ACCOUNT', false);
        //$ItaEngine_mail = $devLib->getEnv_config_global_ini('ITAENGINE_EMAIL');
        $anaent_2 = $this->proLib->GetAnaent('2');
        if ($subject === null) {
            $subject = 'Errore in Generazione Registro Giornaliero.';
        }
        if ($bodyHeader === null) {
            $bodyHeader.= ' si è verificato un errore durante la procedura automatica di Generazione del Registro Giornaliero.<br>';
            $bodyHeader.= 'Errore riscontrato:<br>';
        }
        $body = 'PROTOCOLLO ' . $anaent_2['ENTDE1'] . ' - ENTE ' . App::$utente->getKey('ditta') . '<br>';
        $body.= 'In data ' . date('d/m/Y') . ' alle ore ' . date('H:i:s') . "<br/>";
        $body.= $bodyHeader;
        $body.=$Errore;
        $anaent_26 = $this->proLib->GetAnaent('26');
        $anaent_37 = $this->proLib->GetAnaent('37');
        if ($ItaEngine_mail_rec) {
            $Account = $ItaEngine_mail_rec['CONFIG'];
        }
        if (!$Account) {
            if ($anaent_37) {
                $Account = $anaent_37['ENTDE2'];
            } else if ($anaent_26) {
                $Account = $anaent_26['ENTDE4'];
            }
            if (!$Account) {
                $this->setErrCode(-1);
                $this->setErrMessage('Nessun account di invio configurato.');
                return false;
            }
        }

        include_once ITA_BASE_PATH . '/apps/Mail/emlMailBox.class.php';
        $emlMailBox = emlMailBox::getInstance($Account);

        if (!$emlMailBox) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile accedere alle funzioni dell\'account');
            return false;
        }

        $outgoingMessage = $emlMailBox->newEmlOutgoingMessage();
        if (!$outgoingMessage) {
            $this->setErrCode(-1);
            $this->setErrMessage('Impossibile creare un nuovo messaggio in uscita');
            return false;
        }
        $anaent_43 = $this->proLib->GetAnaent('43');
        $ElencoEmail = unserialize($anaent_43['ENTVAL']);
        if (!$ElencoEmail) {
            $this->setErrCode(-1);
            $this->setErrMessage('Nessuna Destinatario configurato per ivio della mail.');
            return false;
        }
        $mailDest = '';
        foreach ($ElencoEmail as $Mail) {
            $mailDest = $Mail['EMAIL'];
            $outgoingMessage->setSubject($subject);
            $outgoingMessage->setBody($body);
            $outgoingMessage->setEmail($mailDest);
            $mailSent = $emlMailBox->sendMessage($outgoingMessage, false, false);
            if ($mailSent) {
                continue;
            } else {
                $this->setErrCode(-1);
                $this->setErrMessage($emlMailBox->getLastMessage());
                return false;
            }
        }
        return true;
    }

    public function ControllaPresenzaDatiFondamentali() {
        $CodiceRegistro = $this->proLib->GetCodiceRegistroProtocollo();
        if (!$CodiceRegistro) {
            $this->setErrCode(-1);
            $this->setErrMessage("Parametro del Codice Registro Protocollo mancante. <br>Non è possibile procedere con la stampa del Registro Giornaliero.");
            return false;
        }
        $CodiceDocFormali = $this->proLib->GetCodiceRegistroDocFormali();
        if (!$CodiceDocFormali) {
            $this->setErrCode(-1);
            $this->setErrMessage("Parametro del Codice Registro Documenti Formali mancante. <br>Non è possibile procedere con la stampa del Registro Giornaliero.");
            return false;
        }
        /* Data Limite Iniziale è un Parametro obbligatorio   */
        $parametriRegistro = $this->getParametriRegistro();
        App::log($parametriRegistro);
        if (!$parametriRegistro['DATALIMITEINIZIALE']) {
            $this->setErrCode(-1);
            $this->setErrMessage("Parametro della Data Limite Iniziale Registro Giornaliero Mancante. <br>Non è possibile procedere con la stampa del Registro Giornaliero.");
            return false;
        }
        // Controllo sulla validità del titolario?

        return true;
    }

    public function getMenuFunzioni($nameForm, $rowidAnapro) {
        $arrBottoni = array();
        $ParametriRegistro = $this->getParametriRegistro();

        if ($ParametriRegistro['REPOSITORY']) {
            $arrBottoni['Esporta Allegati Non Esportati'] = array('id' => $nameForm . '_Esporta', "style" => "width:280px;height:40px;", 'metaData' => "iconLeft:'ita-icon-upload-32x32'", 'model' => $nameForm);
        }
        if ($ParametriRegistro['TIPOCONSERVAZIONE'] != '') {
            $arrBottoni['Riversa in Conservazione'] = array('id' => $nameForm . '_Conserva', "style" => "width:280px;height:40px;", 'metaData' => "iconLeft:'ita-icon-arrow-green-dx-32x32'", 'model' => $nameForm);
        }
        //
        if ($ParametriRegistro['TIPOCONSERVAZIONE'] == proConservazioneManagerHelper::MANAGER_ASMEZDOC) {
            $esito_rec = $this->proLibConservazione->GetEsitoConservazione($rowidAnapro);
            if ($esito_rec['Esito'] == proLibConservazione::ESITO_DAVERIFICARE) {
                $arrBottoni['Imposta esito conservazione'] = array('id' => $nameForm . '_ImpostaEsitoConservazione', "style" => "width:280px;height:40px;", 'metaData' => "iconLeft:'ita-icon-edit-32x32'", 'model' => $nameForm);
            }
        }

        return $arrBottoni;
    }

    public function isRegistroGiornaliero($Anapro_rec) {
        $ParametriRegistro = $this->getParametriRegistro();
        if ($ParametriRegistro['TIPODOCUMENTO'] && $Anapro_rec['PROCODTIPODOC'] == $ParametriRegistro['TIPODOCUMENTO']) {
            $proLibTabDag = new proLibTabDag();
            $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $Anapro_rec['ROWID'], self::CHIAVE_DATA_REGISTRO, '', false, '', self::FONTE_DATI_REGISTRO);
            if ($TabDag_rec) {
                return true;
            }
        }
        return false;
    }

    public function conservaRegistroAuto($send = false) {
        /*
         * Reset elenchi warning ed errori
         */
        $elenco_errori = array();
        $elenco_warning = array();
        $elenco_negativo = array();
        $elenco_positivo = array();

        /*
         * Sql di base dei registri di protocollo non annullati con aggiunta di metadati tabdag
         */
        $where = " AND PROPAR='C' AND ANAPRO.PROSTATOPROT <> " . proLib::PROSTATO_ANNULLATO . " ";
        $sql = $this->getSqlElencoRegistro($where);

        /*
         * Estrazione elenco dei registri di protocollo senza Esito di conservazione o con esito negativo
         */
//        $sqlConserva = "
//            SELECT
//                *
//            FROM
//                ($sql) AS REGISTRO_CONSERVA
//            WHERE
//                ESITO_CONSERVAZIONE_ESITO='" . proLibConservazione::ESITO_NEGATIVO . "' OR 
//                ESITO_CONSERVAZIONE_ESITO IS NULL";

        /*
         * Estrazione elenco dei registri di protocollo senza Esito di conservazione
         */
        $sqlConserva = "
            SELECT
                *
            FROM
                ($sql) AS REGISTRO_CONSERVA
            WHERE
                ESITO_CONSERVAZIONE_ESITO IS NULL";

        $Anapro_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sqlConserva, true);

        /*
         * Controlli su conservabilità dei registri estratti
         * 
         * Ritorno di un array dei protocollo di registro conservabili
         * 
         */
        $retControlli = $this->ControlliRegistri($Anapro_tab);
        $Anapro_da_conservare_tab = array();
        foreach ($Anapro_tab as $Anapro_rec) {
            if ($retControlli[proLibGiornaliero::CTR_ALLEGATO_NON_CONSERVATO][$Anapro_rec['ROWID']]) {
                if ($retControlli[proLibGiornaliero::CTR_DOCUMENTO_NON_CONSERVABILE][$Anapro_rec['ROWID']]) {
                    $Anaogg_rec = $this->proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
                    $elenco_warning[] = $Anapro_rec["PRONUM"] . "/" . $Anapro_rec["PROPAR"] . " [ATTENZIONE][" . proLibGiornaliero::CTR_DOCUMENTO_NON_CONSERVABILE . "] " . $Anaogg_rec['OGGOGG'];
                    continue;
                }
                $Anapro_da_conservare_tab[] = $Anapro_rec;
            }
        }


        /*
         * Cliclo di chiamate al conservatore
         */
        $proLibTabDag = new proLibTabDag();
        $proLibConservazione = new proLibConservazione();
        $eqAudit = new eqAudit();

        $Audit = 'Inizio chiamata conservazione registro giornaliero auto. Totale Anapro da conservare: ' . count($Anapro_da_conservare_tab);
        $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));

        $vv = 0;
        foreach ($Anapro_da_conservare_tab as $Anapro_da_conservare_rec) {
            $param = array();
            $parametriRegistro = $this->getParametriRegistro();
            $rowid = $Anapro_da_conservare_rec['ROWID'];
            $TabDag_rec = $proLibTabDag->GetTabdag('ANAPRO', 'chiave', $rowid, proLibGiornaliero::CHIAVE_DATA_REGISTRO, '', false, '', proLibGiornaliero::FONTE_DATI_REGISTRO);
            if ($TabDag_rec) {
                $DataRegistro = $TabDag_rec['TDAGVAL'];
                $datetime_esito = date('Ymd_His');

                //$param['NOMEFILEESITO'] = 'ESITO_CONSERVAZIONE_REG_' . $DataRegistro . '_' . $datetime_esito . '.xml';
            }
            $param['TIPOCONSERVAZIONE'] = $parametriRegistro['TIPOCONSERVAZIONE'];

            /* Inserimento audit conserva anapro e predisposizione AuditEsito */
//            $Audit = 'Inizio Chiamata conservazione Prot. rowid: ' . $Anapro_da_conservare_rec['ROWID'] . ' - Numero: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"];
//            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $Audit));
            //@TODO cambia nome alla funzione conservaAnaproRegistroProtocollo
            $retConservazione = $proLibConservazione->conservaAnapro($rowid, $param);
            if (!$retConservazione) {
                $AuditEsito = 'Chiamata conservazione registro goirnaliero conclusa con errori Prot. rowid: ' . $Anapro_da_conservare_rec['ROWID'] . ' - Numero: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"];
                $AuditEsito.= '. Esito: ' . $proLibConservazione->getErrCode() . ' ' . $proLibConservazione->getErrMessage();
                $elenco_errori[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " [ERRORE][" . $proLibConservazione->getErrMessage() . "] ";
            } else {
                $retEsito = $proLibConservazione->getRetEsito();
                if ($retEsito[proLibConservazione::CHIAVE_ESITO_ESITO] == proLibConservazione::ESITO_NEGATIVO) {
                    $elenco_negativo[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " [ESITO NEGATIVO][" . $retEsito[proLibConservazione::CHIAVE_ESITO_MESSAGGIOERRORE] . "] " . $Anapro_da_conservare_rec['OGGOGG'];
                }
                if ($retEsito[proLibConservazione::CHIAVE_ESITO_ESITO] == proLibConservazione::ESITO_WARNING) {
                    $elenco_warning[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " [ESITO WARNING][" . $retEsito[proLibConservazione::CHIAVE_ESITO_MESSAGGIOERRORE] . "] " . $Anapro_da_conservare_rec['OGGOGG'];
                }
                if ($retEsito[proLibConservazione::CHIAVE_ESITO_ESITO] == proLibConservazione::ESITO_POSTITIVO) {
                    $elenco_positivo[] = $Anapro_da_conservare_rec["PRONUM"] . "/" . $Anapro_da_conservare_rec["PROPAR"] . " [ESITO POSITIVO][] " . $Anapro_da_conservare_rec['OGGOGG'];
                }

                $esitoMsg = $retEsito[proLibConservazione::CHIAVE_ESITO_ESITO];
                $AuditEsito = 'Chiamata conservazione registro giornaliero conclusa senza errori Prot. rowid: ' . $Anapro_da_conservare_rec['ROWID'] . ' ';
                $AuditEsito.='- Numero: ' . $Anapro_da_conservare_rec['PRONUM'] . "/" . $Anapro_da_conservare_rec["PROPAR"] . '. Esito: ' . $esitoMsg;
            }

            /* Loggo Risultato Conservazione
             * Se non ha dato errore AuditEsito è ancora vuoto */
            $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $AuditEsito));
            $vv = $vv + 1;
            /*
             * PER LIMITARE MASSIMO 5 CONSERVAZIONI (FASE DI RECUPERO VECCHI REGISTRI DA CONSERVARE IN PRE-PRODUZIONE E/O PRODUZIONE
             */
            if ($vv === 2) {
                break;
            }
        }
        /* Terminato conservaauto */
        $MessaggioAudit = 'Terminata chiamata conservazione registro giornaliero auto.';
        $MessaggioAudit.=' Errori: ' . count($elenco_errori) . '.';
        $MessaggioAudit.=' Negativo: ' . count($elenco_negativo) . '.';
        $MessaggioAudit.=' Warning: ' . count($elenco_warning) . '.';
        $MessaggioAudit.=' Positivi: ' . count($elenco_positivo) . '.';
        $eqAudit->logEqEvent($this, array('Operazione' => eqAudit::OP_MISC_AUDIT, 'Estremi' => $MessaggioAudit));

        return array(
            'STATUS' => true,
            'DA_CONSERVARE' => $Anapro_da_conservare_tab,
            'ERRORI' => $elenco_errori,
            'NEGATIVO' => $elenco_negativo,
            'WARNING' => $elenco_warning,
            'POSITIVO' => $elenco_positivo
        );
    }

}

?>