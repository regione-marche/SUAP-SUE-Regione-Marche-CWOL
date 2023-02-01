<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Protocollo Conservazione DigiP Marche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    16.06.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibConservazione.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibGiornaliero.class.php';
include_once ITA_BASE_PATH . '/apps/Sviluppo/devLib.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibAllegati.class.php';

class proDigiPMarche {

    const CLASSE_PARAMETRI = 'DIGIP_MARCHE';
    const Versione = '1.4';
    const VersioneDatiSpecifici = '1.0';

    public $PROT_DB;
    public $ITALWEB_DB;
    public $devLib;
    public $proLib;
    public $proLibAllegati;
    private $errCode;
    private $errMessage;
    private $xmlRichiesta;
    private $xmlResponso;
    private $datiMinimiEsitoVersamento;

    function __construct() {
        $this->devLib = new devLib();
        $this->proLib = new proLib();
        $this->proLibAllegati = new proLibAllegati();
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

    public function setPROTDB($PROT_DB) {
        $this->PROT_DB = $PROT_DB;
    }

    public function getPROTDB() {
        if (!$this->PROT_DB) {
            try {
                $this->PROT_DB = ItaDB::DBOpen('PROT');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->PROT_DB;
    }

    public function getDatiMinimiEsitoVersamento() {
        return $this->datiMinimiEsitoVersamento;
    }

    public function setDatiMinimiEsitoVersamento($datiMinimiEsitoVersamento) {
        $this->datiMinimiEsitoVersamento = $datiMinimiEsitoVersamento;
    }

    public function getXmlRichiesta() {
        return $this->xmlRichiesta;
    }

    public function setXmlRichiesta($xmlRichiesta) {
        $this->xmlRichiesta = $xmlRichiesta;
    }

    public function getXmlResponso() {
        return $this->xmlResponso;
    }

    public function setXmlResponso($xmlResponso) {
        $this->xmlResponso = $xmlResponso;
    }

    public function setITALWEBDB($ITALWEB_DB) {
        $this->ITALWEB_DB = $ITALWEB_DB;
    }

    public function getITALWEBDB() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    public function GetParametri() {
        $Parametri = array();
        $EnvParametri = $this->devLib->getEnv_config(self::CLASSE_PARAMETRI, 'codice', '', true);
        foreach ($EnvParametri as $key => $Parametro) {
            $Parametri[$Parametro['CHIAVE']] = $Parametro['CONFIG'];
        }

        return $Parametri;
    }

    /*
     *  Versamento registro di protocollo
     */

    public function versaDGIPSincrono($rowid_Anapro) {
        $Anapro_rec = $this->proLib->GetAnapro($rowid_Anapro, 'rowid');
        $Parametri = $this->GetParametri();
        $unitaValida = false;
        /*
         * Da Rivedere
         * 
         * 
         */
        if ($Anapro_rec['PROPAR'] == 'C' && $Anapro_rec['PROCODTIPODOC'] == 'STRG') {
            $unitaValida = true;
        }
        if (!$unitaValida) {
            $this->setErrCode(-1);
            $this->setErrMessage("Documento non valido per la conservazione");
            return false;
        }
        $Anaogg_rec = $this->proLib->GetAnaogg($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        

        /*
         * Rivedere per le pec gli allegati da inserire (ricevute acc. e cons.)
         * 
         * Per le PEC in arrivo salvare solo eml oppure tutto lo sbustato?
         * 
         */
        
        $where = ' AND DOCSERVIZIO=0 ORDER BY DOCTIPO ASC,ROWID ASC';
        $Anadoc_tab = $this->proLib->GetAnadoc($Anapro_rec['PRONUM'], 'protocollo', true, $Anapro_rec['PROPAR'], $where);
        $xml = $this->creaXmlUnitaDocumentaria($Anapro_rec, $Anaogg_rec, $Anadoc_tab);

        $files = array();

        /* Nuova Versione di Copia e Lettura Allegato. */
        $subPath = "proDocAllegati-work-" . md5(microtime());
        $tempPath = itaLib::createAppsTempPath($subPath);
        foreach ($Anadoc_tab as $Anadoc_rec) {
            $ID = $Anadoc_rec['ROWID'];
            $filecontent = $this->proLibAllegati->CopiaDocAllegato($Anadoc_rec['ROWID'], $tempPath . '/' . $Anadoc_rec['DOCFIL']);
            $files[$ID] = array("filecontent" => $filecontent, "filename" => $Anadoc_rec['DOCNAME']);
        }

//        Versione precedente - 10/11/16
//        $files = array();
//        $path = $this->proLib->SetDirectory($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
//        foreach ($Anadoc_tab as $Anadoc_rec) {
//            $ID = $Anadoc_rec['ROWID'];
//            $files[$ID] = array("filecontent" => $path . '/' . $Anadoc_rec['DOCFIL'], "filename" => $Anadoc_rec['DOCNAME']);
//            //$files[$ID] = $path . '/' . $Anadoc_rec['DOCFIL'];
//        }


        $assoc = array(
            'VERSIONE' => self::Versione,
            'LOGINNAME' => $Parametri['DIGIP_USERID'],
            'PASSWORD' => $Parametri['DIGIP_PASSWORD'],
            'XMLSIP' => utf8_encode($xml)
        );
//        file_put_contents("/users/tmp/miki.log", $xml);
// inizio
        $data = array(
            "FIELDS" => $assoc,
            "FILES" => $files
        );
        $this->setXmlRichiesta(utf8_encode($xml));
        $this->setXmlResponso('');
        include_once ITA_LIB_PATH . '/itaPHPRestClient/itaRestClient.class.php';
        $restClient = new itaRestClient();
        /*
         * Parametrico?
         * 
         */
        $restClient->setTimeout(10);
        $restClient->setCurlopt_url($Parametri['DIGIP_URLSERVIZIO']);
        if ($restClient->postMultipart('', $data)) {
            $this->setXmlResponso($restClient->getResult());
            if (!$this->parseEsitoVersamento($restClient->getResult(), true)) {
                $this->setErrCode(-1);
                //$this->setErrMessage("Errore Analisi xml responso versamento DIGIP");
                return false;
            }
        } else {
            $this->setErrCode(-1);
            $this->setErrMessage($restClient->getErrMessage());
            return false;
        }
        return true;
    }

    /**
     * Crea XML Metadati Unita Documentaria Ambiante test generica
     * 
     * @param type $Anapro_rec Recod di protocollo interessato
     * @param type $tipoUnitaDocumentaria Ancora non ustao
     * @return mixed false se errore, stringa xml se tutto ok
     */
    public function creaXmlUnitaDocumentariaTest($Anapro_rec, $Anaogg_rec, $Anadoc_tab) {
        /*
         * Lettura Parametri
         */
        $Parametri = $this->GetParametri();
        /*
         * Predisposizione Variabili
         */
        $Numero = intval(substr($Anapro_rec['PRONUM'], 4));
        $Anno = substr($Anapro_rec['PRONUM'], 0, 4);
        $Data = date("Y-m-d", strtotime($Anapro_rec['PRODAR']));
        $Oggetto = $Anaogg_rec['OGGOGG'];
        switch ($Anapro_rec['PROPAR']) {
            case 'C':
                $TipoRegistro = $this->proLib->GetCodiceRegistroDocFormali();
                break;
            case 'A':
            case 'P':
                $TipoRegistro = $this->proLib->GetCodiceRegistroProtocollo();
                break;
        }
        $AnadocDocPrincipale = $Anadoc_tab[0];
        $NomeFile = $AnadocDocPrincipale['DOCNAME'];
        $Estensione = $this->GetEstensione($NomeFile);
        $FormatoFile = $Estensione;
        $NumeroAllegati = count($Anadoc_tab) - 1;
        /*
         * Scrivo l'xml
         */
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                    <UnitaDocumentaria>
                            <Intestazione>
                                <Versione>' . self::Versione . '</Versione>
                                <Versatore>
                                    <Ambiente>' . $Parametri['DIGIP_AMBIENTE'] . '</Ambiente>
                                    <Ente>' . $Parametri['DIGP_ENTE'] . '</Ente>
                                    <Struttura>' . $Parametri['DIGIP_STRUTTURA'] . '</Struttura>
                                    <UserID>' . $Parametri['DIGIP_USERID'] . '</UserID>
                                </Versatore>
                                <Chiave>
                                    <Numero>' . $Numero . '</Numero>
                                    <Anno>' . $Anno . '</Anno>
                                    <TipoRegistro>' . $TipoRegistro . '</TipoRegistro>
                                </Chiave>
                                <TipologiaUnitaDocumentaria>Documento protocollato</TipologiaUnitaDocumentaria>
                            </Intestazione>
                            <Configurazione>
                                <TipoConservazione>SOSTITUTIVA</TipoConservazione>
                                <ForzaAccettazione>true</ForzaAccettazione>
                                <ForzaConservazione>true</ForzaConservazione>
                            </Configurazione>
                            <ProfiloUnitaDocumentaria>
                                <Oggetto>' . $Oggetto . '</Oggetto>
                                <Data>' . $Data . '</Data>
                            </ProfiloUnitaDocumentaria>
                            <NumeroAllegati>' . $NumeroAllegati . '</NumeroAllegati>
                            <DocumentoPrincipale>
                                <IDDocumento>ANADOC-' . $AnadocDocPrincipale['ROWID'] . '</IDDocumento>
                                <TipoDocumento>Documento protocollato</TipoDocumento>
                                <DatiSpecifici>
                                    <VersioneDatiSpecifici>' . self::VersioneDatiSpecifici . '</VersioneDatiSpecifici>
                                    <TipoDocumento>' . $Anapro_rec['PROCODTIPODOC'] . '</TipoDocumento>
                                    <Origine>' . $Anapro_rec['PROPAR'] . '</Origine>
                                </DatiSpecifici> 
                                <StrutturaOriginale>
                                    <TipoStruttura>DocumentoGenerico</TipoStruttura>
                                        <Componenti>
                                            <Componente>
                                            <ID>' . $AnadocDocPrincipale['ROWID'] . '</ID>
                                            <OrdinePresentazione>1</OrdinePresentazione>
                                            <TipoComponente>CONTENUTO</TipoComponente>
                                            <TipoSupportoComponente>FILE</TipoSupportoComponente>
                                            <NomeComponente>' . $NomeFile . '</NomeComponente>
                                            <FormatoFileVersato>' . $FormatoFile . '</FormatoFileVersato>
                                        </Componente>
                                    </Componenti>
                                </StrutturaOriginale>
                            </DocumentoPrincipale>';
        $xmlAllegati = $this->getXmlAllegati($Anapro_rec, $Anadoc_tab);
        $xml.= $xmlAllegati;
        $xml.='</UnitaDocumentaria>';
        return $xml;
    }

    /**
     * Crea XML Metadati Unita Documentaria disciplinare concordato per registro di protocollo italsoft
     * 
     * @param type $Anapro_rec Recod di protocollo interessato
     * @param type $tipoUnitaDocumentaria Ancora non ustao
     * @return mixed false se errore, stringa xml se tutto ok
     */
    public function creaXmlUnitaDocumentaria($Anapro_rec, $Anaogg_rec, $Anadoc_tab) {
        /*
         * Lettura Parametri
         */
        $Parametri = $this->GetParametri();

        /*
         * Campi Attivi di Tabdag
         */
        $campoDataRegistro = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_DATA_REGISTRO];
        $campoDataIniziale = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_DATA_INIZIO_REGISTRAZIONE];
        $campoDataFinale = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_DATA_INIZIO_REGISTRAZIONE];
        $campoPrimoNumero = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_NUMERO_INIZIALE];
        $campoUltimoNumero = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_NUMERO_FINALE];
        $campoSoggettoProduttore = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_SOGGETTO_PRODUTTORE];
        $campoSoggettoResponsabile = proLibGiornaliero::FONTE_DATI_REGISTRO . "_" . proLibGiornaliero::$ElencoChiaviAttiveTabDag[proLibGiornaliero::CHIAVE_RESPONSABILE];

        /*
         * Lettura Metadati
         */
        $proLibGiornaliero = new proLibGiornaliero();
        $metaDati = $proLibGiornaliero->getAnaproAndMetadati($Anapro_rec['ROWID']);
        
        /*
         * Predisposizione Variabili Standard Unità documnetaria
         */
        $Numero = intval(substr($Anapro_rec['PRONUM'], 4));
        $Anno = substr($Anapro_rec['PRONUM'], 0, 4);
        $Data = date("Y-m-d", strtotime($Anapro_rec['PRODAR']));
        $Oggetto = $Anaogg_rec['OGGOGG'];
        switch (substr($Anapro_rec['PROPAR'], 0, 1)) {
            case 'C':
                $TipoRegistro = $this->proLib->GetCodiceRegistroDocFormali();
                break;
            case 'A':
            case 'P':
                $TipoRegistro = $this->proLib->GetCodiceRegistroProtocollo();
                break;
        }
        
        /*
         * Metadati Specifici Registri protocollo
         */
        $DataIniziale = date("Y-m-d", strtotime($metaDati[$campoDataRegistro]));
        $DataFinale = date("Y-m-d", strtotime($metaDati[$campoDataRegistro]));
        if ($metaDati[$campoDataIniziale]) {
            $DataIniziale = date("Y-m-d", strtotime($metaDati[$campoDataIniziale]));
        }
        if ($metaDati[$campoDataFinale]) {
            $DataFinale = date("Y-m-d", strtotime($metaDati[$campoDataFinale]));
        }
        $PrimoNumero = 0;
        $UltimoNumero = 0;
        if ($metaDati[$campoPrimoNumero]) {
            $PrimoNumero = $metaDati[$campoPrimoNumero];
        }
        if ($metaDati[$campoUltimoNumero]) {
            $UltimoNumero = $metaDati[$campoUltimoNumero];
        }
        $SoggettoProduttore = $metaDati[$campoSoggettoProduttore];
        $SoggettoResponsabile = $metaDati[$campoSoggettoResponsabile];
        /*
         * Fine Metadati specifici
         */

        
        $AnadocDocPrincipale = $Anadoc_tab[0];
        $NomeFile = $AnadocDocPrincipale['DOCNAME'];
        $Estensione = $this->GetEstensione($NomeFile);
        $FormatoFile = $Estensione;
        $NumeroAllegati = count($Anadoc_tab) - 1;
        
        /*
         * Dati per unita documentaria specifica.
         */
        $TipologiaUnitaDocumentaria = 'Registro di Protocollo';
        $TipoDocumento = 'Registro di Protocollo';
        $TipoStruttura = 'DocumentoGenerico';
        $TipoConservazione = 'SOSTITUTIVA';

        /*
         * Dati standard per Allegato
         */
        $path = $this->proLib->SetDirectory($Anapro_rec['PRONUM'], $Anapro_rec['PROPAR']);
        $hashVersatoPrincipale = $this->proLibAllegati->GetHashDocAllegato($AnadocDocPrincipale['ROWID'], 'sha256');
        
        /*
         * Scrivo l'xml
         * 
         * Possibile implementazione centralizzata --------
         * 
         */
        
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                    <UnitaDocumentaria>
                            <Intestazione>
                                <Versione>' . self::Versione . '</Versione>
                                <Versatore>
                                    <Ambiente>' . $Parametri['DIGIP_AMBIENTE'] . '</Ambiente>
                                    <Ente>' . $Parametri['DIGP_ENTE'] . '</Ente>
                                    <Struttura>' . $Parametri['DIGIP_STRUTTURA'] . '</Struttura>
                                    <UserID>' . $Parametri['DIGIP_USERID'] . '</UserID>
                                </Versatore>
                                <Chiave>
                                    <Numero>' . $Numero . '</Numero>
                                    <Anno>' . $Anno . '</Anno>
                                    <TipoRegistro>' . $TipoRegistro . '</TipoRegistro>
                                </Chiave>
                                <TipologiaUnitaDocumentaria>' . $TipologiaUnitaDocumentaria . '</TipologiaUnitaDocumentaria>
                            </Intestazione>
                            <ProfiloUnitaDocumentaria>
                                <Oggetto>' . $Oggetto . '</Oggetto>
                                <Data>' . $Data . '</Data>
                            </ProfiloUnitaDocumentaria>
                            <NumeroAllegati>' . $NumeroAllegati . '</NumeroAllegati>
                            <DocumentoPrincipale>
                                <IDDocumento>ANADOC-' . $AnadocDocPrincipale['ROWID'] . '</IDDocumento>
                                <TipoDocumento>' . $TipoDocumento . '</TipoDocumento>
                                <DatiSpecifici>
                                    <TipoDocumento>' . $Anapro_rec['PROCODTIPODOC'] . '</TipoDocumento>                                        
                                    <IdUnivoco>ANADOC-' . $AnadocDocPrincipale['ROWID'] . '</IdUnivoco>                                        
                                    <Origine>' . $Anapro_rec['PROPAR'] . '</Origine>
                                    <DataChiusura>' . $Data . '</DataChiusura>    
                                    <SoggettoProduttore>' . $SoggettoProduttore . '</SoggettoProduttore>    
                                    <Responsabile>' . $SoggettoResponsabile . '</Responsabile>    
                                    <NumeroPrimaRegistrazione>' . $PrimoNumero . '</NumeroPrimaRegistrazione>    
                                    <NumeroUltimaRegistrazione>' . $UltimoNumero . '</NumeroUltimaRegistrazione>    
                                    <DataPrimaRegistrazione>' . $DataIniziale . '</DataPrimaRegistrazione>    
                                    <DataUltimaRegistrazione>' . $DataFinale . '</DataUltimaRegistrazione>    
                                </DatiSpecifici> 
                                <StrutturaOriginale>
                                        <TipoStruttura>' . $TipoStruttura . '</TipoStruttura>
                                        <Componenti>
                                            <Componente>
                                            <ID>' . $AnadocDocPrincipale['ROWID'] . '</ID>
                                            <OrdinePresentazione>1</OrdinePresentazione>
                                            <TipoComponente>CONTENUTO</TipoComponente>
                                            <TipoSupportoComponente>FILE</TipoSupportoComponente>
                                            <NomeComponente>' . $NomeFile . '</NomeComponente>
                                            <FormatoFileVersato>' . $FormatoFile . '</FormatoFileVersato>
                                            <HashVersato>' . $hashVersatoPrincipale . '</HashVersato>
                                            <IDComponenteVersato>ANADOC-' . $AnadocDocPrincipale['ROWID'] . '</IDComponenteVersato>
					    <UtilizzoDataFirmaPerRifTemp>false</UtilizzoDataFirmaPerRifTemp>                                                
                                        </Componente>
                                    </Componenti>
                                </StrutturaOriginale>
                            </DocumentoPrincipale>';
        $xmlAllegati = $this->getXmlAllegati($Anapro_rec, $Anadoc_tab, $Oggetto, $SoggettoProduttore);
        $xml.= $xmlAllegati;
        $xml.='</UnitaDocumentaria>';
        return $xml;
    }

    public function getXmlAllegati($Anapro_rec, $Anadoc_tab, $oggetto = '', $produttore = '') {
        $xmlAllegati = '';
        $ID = 1;
        

        if (count($Anadoc_tab) > 1) {
            $xmlAllegati.='<Allegati>';
            foreach ($Anadoc_tab as $key => $Anadoc_rec) {
                if ($key == 0) {
                    continue;
                }
                $ID++;
                /*
                 * Implementazione della sanitizzazione del nome file
                 * (verificare se necessario applicare prima dell'inizion della creazione dell'unita con conseguente
                 *  aggiornamento di ANADOC)
                 * 
                 */
                $NomeFile = $Anadoc_rec['DOCNAME'];
                $Estensione = $this->GetEstensione($NomeFile);
                $FormatoFile = $Estensione;
                $hashVersatoAllegato = $this->proLibAllegati->GetHashDocAllegato($Anadoc_rec['ROWID'], 'sha256');

                /*
                 * Rendere Parametrico
                 * 
                 */
                $TipoDocumento = 'Registro di Protocollo';
                $TipoStruttura = 'DocumentoGenerico';
                $xmlAllegati.='<Allegato>
                                   <IDDocumento>ANADOC-' . $Anadoc_rec['ROWID'] . '</IDDocumento>
                                   <TipoDocumento>' . $TipoDocumento . '</TipoDocumento>
                                     <DatiSpecifici>
                                       <VersioneDatiSpecifici>' . self::VersioneDatiSpecifici . '</VersioneDatiSpecifici>  
                                       <TipoDocumento>' . $Anapro_rec['PROCODTIPODOC'] . '</TipoDocumento>
                                       <Origine>' . $Anapro_rec['PROPAR'] . '</Origine>
                                   </DatiSpecifici>             
                                    <StrutturaOriginale>
                                     <TipoStruttura>' . $TipoStruttura . '</TipoStruttura> 
                                       <Componenti>
                                          <Componente>
                                            <ID>' . $Anadoc_rec['ROWID'] . '</ID>
                                            <OrdinePresentazione>' . $ID . '</OrdinePresentazione>
                                            <TipoComponente>CONTENUTO</TipoComponente>
                                            <TipoSupportoComponente>FILE</TipoSupportoComponente>
                                            <NomeComponente>' . $NomeFile . '</NomeComponente>
                                            <FormatoFileVersato>' . $FormatoFile . '</FormatoFileVersato>
                                            <HashVersato>' . $hashVersatoAllegato . '</HashVersato>
                                            <IDComponenteVersato>ANADOC-' . $Anadoc_rec['ROWID'] . '</IDComponenteVersato>
					    <UtilizzoDataFirmaPerRifTemp>false</UtilizzoDataFirmaPerRifTemp>                                                   
                                          </Componente>
                                       </Componenti>
                                    </StrutturaOriginale>
                               </Allegato> ';
            }
            $xmlAllegati.='</Allegati>';
            return $xmlAllegati;
        }
    }

    public function GetEstensione($NomeFile) {
        // SEMPLIFICARE?
        $Estensione = '';
        $Ctr = 1;
        while (true) {
            $ext = pathinfo($NomeFile, PATHINFO_EXTENSION);
            $NomeFile = pathinfo($NomeFile, PATHINFO_FILENAME);
            if ($ext == '' || $Ctr == 3) {
                break;
            }
            $Estensione = '.' . $ext . $Estensione;
            $Ctr++;
        }
        $Estensione = substr($Estensione, 1);
        return $Estensione;
    }

    public function parseEsitoVersamento($xmlString) {
        $xmlObj = new itaXML;
        $retXml = $xmlObj->setXmlFromString($xmlString);
        if (!$retXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("File XML . Impossibile leggere il contenuto del Messaggio xml.");
            return false;
        }
        $arrayXml = $xmlObj->toArray($xmlObj->asObject());
        if (!$arrayXml) {
            $this->setErrCode(-1);
            $this->setErrMessage("Lettura XML. Impossibile estrarre i dati Messaggio xml.");
            return false;
        }
        $this->datiMinimiEsitoVersamento = array();
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CONSERVATORE] = self::CLASSE_PARAMETRI;
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_VERSIONE] = utf8_decode($arrayXml['Versione'][0]['@textNode']);
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_DATAVERSAMENTO] = utf8_decode($arrayXml['DataVersamento'][0]['@textNode']);
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_ESITO] = utf8_decode($arrayXml['EsitoGenerale'][0]['CodiceEsito'][0]['@textNode']);
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CODICEERRORE] = utf8_decode($arrayXml['EsitoGenerale'][0]['CodiceErrore'][0]['@textNode']);
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_MESSAGGIOERRORE] = utf8_decode($arrayXml['EsitoGenerale'][0]['MessaggioErrore'][0]['@textNode']);
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CHIAVEVERSAMENTO] = "Ambiente:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Versatore'][0]['Ambiente'][0]['@textNode']) . ",";
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CHIAVEVERSAMENTO] .= "Ente:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Versatore'][0]['Ente'][0]['@textNode']) . ",";
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CHIAVEVERSAMENTO] .= "Struttura:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Versatore'][0]['Struttura'][0]['@textNode']) . ",";
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CHIAVEVERSAMENTO] .= "Numero:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Chiave'][0]['Numero'][0]['@textNode']) . ",";
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CHIAVEVERSAMENTO] .= "Anno:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Chiave'][0]['Anno'][0]['@textNode']) . ",";
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_CHIAVEVERSAMENTO] .= "TipoRegistro:" . utf8_decode($arrayXml['UnitaDocumentaria'][0]['Chiave'][0]['TipoRegistro'][0]['@textNode']);
        // Qui potrebbe servire per utilizzo conservazione versione precedente a 1.4? Chiave potrebbe dare errore se non valorizzata?
        $this->datiMinimiEsitoVersamento[prolibConservazione::CHIAVE_ESITO_IDVERSAMENTO] .= utf8_decode($arrayXml['IdSIP'][0]['@textNode']);
        return true;
    }

}

?>
