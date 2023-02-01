<?php

/**
 *
 * Raccolta di funzioni per il web service delle pratiche
 *
 * PHP Version 5
 *
 * @category   wsModel
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2012 Italsoft Srl
 * @license
 * @version    17.09.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once(ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proLibTitolario.class.php');
include_once(ITA_BASE_PATH . '/apps/Segreteria/segLibDocumenti.class.php');
include_once(ITA_BASE_PATH . '/apps/Protocollo/proWsAgent.php');
include_once(ITA_LIB_PATH . '/QXml/QXml.class.php');

class proWsAgentProtocollo extends proWsAgent {

    function __construct() {
        parent::__construct();
    }

    public function PutDocumentoAllaFirma($TokenKey, $dati = array()) {

        /**
         * Controllo di blocco
         */
        $retCheck = $this->checkManutenzione();
        if ($retCheck !== false) {
            return $retCheck;
        }

        $this->clearResult();

        $proLib = new proLib();

        $dizionario = array(
            'PROGRESSIVO' => '',
            'ANNO' => date("Y"),
            'ORGANO' => '  ',
            'PREFISSO' => 'D_'
        );
        $elementi['dizionario'] = $dizionario;

        $elementi['TipoDocumento'] = segLibDocumenti::TIPODOC_DOCUMENTO;
        $elementi['anapro']['PRODOCTIPO'] = $dati['tipoDocumento'];
        $elementi['anapro']['PROUOF'] = $dati['ufficioOperatore'];

        $elementi['INDICE']['INDPREPAR'] = $dati['tipoProtocollo'];
        $elementi['INDICE']['IOGGETTO'] = $dati['oggetto'];
        $elementi['INDICE']['IDATDE'] = date('Ymd');
        $elementi['ANNO'] = date("Y");

        if (isset($dati['firmatari']['firmatario'][0])) {
            $firmatari = $dati['firmatari']['firmatario'];
        } else {
            $firmatari = $dati['firmatari'];
        }
        $elencoFirmatari = array();
        foreach ($dati['firmatari'] as $firmatario) {
            $elencoFirmatari[] = array(
                'DESCOD' => $firmatario['codice'],
                'DESCUF' => $firmatario['ufficio']
            );
        }

        $elementi['firmatario'] = $elencoFirmatari;
        $elementi['DESCOD'] = $elencoFirmatari[0]['DESCOD'];
        $elementi['DESCUF'] = $elencoFirmatari[0]['DESCUF'];


        $elementi['classificazione'] = $dati['classificazione'];

        if (isset($dati['destinatari']['mittenteDestinatario'][0])) {
            $Destinatari = $dati['destinatari']['mittenteDestinatario'];
        } else {
            $Destinatari = $dati['destinatari'];
        }
        if ($Destinatari) {
            $i = 0;
            $altriDestinatari = array();
            foreach ($Destinatari as $Destinatario) {
                $i++;
                if ($i == 1) {
                    /*
                     * Destinatario Principale
                     */
                    $elementi['anapro']['PROCON'] = $Destinatario['codice'];
                    $elementi['anapro']['PRONOM'] = $Destinatario['denominazione'];
                    $elementi['anapro']['PROIND'] = $Destinatario['indirizzo'];
                    $elementi['anapro']['PROCAP'] = $Destinatario['cap'];
                    $elementi['anapro']['PROCIT'] = $Destinatario['citta'];
                    $elementi['anapro']['PROPRO'] = $Destinatario['prov'];
                    $elementi['anapro']['PROMAIL'] = $Destinatario['email'];
                    $elementi['anapro']['PROFIS'] = $Destinatario['codiceFiscale'];
                } else {
                    $altriDestinatari[] = array(
                        'DESCOD' => $Destinatario['codice'],
                        'DESNOM' => $Destinatario['denominazione'],
                        'DESIND' => $Destinatario['indirizzo'],
                        'DESCAP' => $Destinatario['cap'],
                        'DESCIT' => $Destinatario['citta'],
                        'DESPRO' => $Destinatario['prov'],
                        'DESMAIL' => $Destinatario['email'],
                        'DESFIS' => $Destinatario['codiceFiscale']
                    );
                }
            }
            $elementi['altriDestinatari'] = $altriDestinatari;
        }

        $trasmissioni = array();
        if (isset($dati['trasmissioniInterne']['trasmissione'][0])) {
            $trasmissioni = $dati['trasmissioniInterne']['trasmissione'];
        } else {
            $trasmissioni = $dati['trasmissioniInterne'];
        }

        foreach ($trasmissioni as $key => $trasmissione) {
            if ($trasmissione['codiceDestinatario']) {
                $anamed_rec = $proLib->GetAnamed($trasmissione['codiceDestinatario']);
                if (!$anamed_rec) {
                    $messageResult['tipoRisultato'] = 'Error';
                    $messageResult['descrizione'] = "Codice " . $trasmissione['codiceDestinatario'] . " non trovato nell'anagrafica dei mittenti.";
                    $result['messageResult'] = $messageResult;
                    return $result;
                }
            } else {
                $anamed_rec = array();
                $anamed_rec['MEDNOM'] = 'TRASMISSIONE A INTERO UFFICIO';
            }
            $trasmissioniInterne[] = array(
                'DESCOD' => $trasmissione['codiceDestinatario'],
                'DESNOM' => $anamed_rec['MEDNOM'],
                'DESANN' => $trasmissione['oggettoTrasmissione'],
                'DESCUF' => $trasmissione['codiceUfficio'],
                'DESGES' => $trasmissione['gestione'],
                'DESTIPO' => 'T'
            );
        }
        $elementi['destinatariInterni'] = $trasmissioniInterne;


        /*
         * allegato principale e precaricati
         */

        $elementi['allegato'] = $dati['allegato'];
        $elementi['allegatiPrecaricati'] = $dati['allegatiPrecaricati'];


        /*
         * NORMALIZZAZIONE DEI DATI PER PutDocumento alla firma estensione dei dato proProtocolla con i dati di indice
         * 
         * ATTENZIONE AL MECCANISMO DEI BINARI PRECARICATI
         *  
         */

        $classDocumento = segLibDocumenti::$ElencoClassDatiIndice[$elementi['TipoDocumento']];
        $appRoute = App::getPath('appRoute.' . substr($classDocumento, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $classDocumento . '.class.php';

        $segDatiIndice = new $classDocumento();

        $ret_id = $segDatiIndice->assegnaDatiDaElementi($elementi);
        if ($ret_id === false) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = $segDatiIndice->getTitle() . " " . $segDatiIndice->getmessage();
            $result['messageResult'] = $messageResult;
            return $result;
        }

        /*
         * VERIFICA DEI PRE-REQUISITI
         * 
         */
        if (!$segDatiIndice->controllaDati()) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = $segDatiIndice->getTitle() . " " . $segDatiIndice->getmessage();
            $result['messageResult'] = $messageResult;
            return $result;
        }

        /*
         * PRGRESSIVO (SE NON PASSATO)
         */
        if ($elementi['TipoDocumento'] != segLibDocumenti::TIPODOC_DETERMINA) {
            if (!$elementi['dizionario']['PROGRESSIVO'] || (is_numeric($elementi['dizionario']['PROGRESSIVO']) && intval($elementi['dizionario']['PROGRESSIVO']) == 0)) {
                $progressivo = $segDatiIndice->PrendiProgressivoDocumentale();
                $elementi['dizionario']['PROGRESSIVO'] = $progressivo;
            }
        }
        $segLib = new segLib();
        $elementi['codice'] = $segLib->getIdelibFormDizionario($elementi['TipoDocumento'], $elementi['dizionario']);

        $indice_rec = $segLib->GetIndice($elementi['codice']);
        if ($indice_rec) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = 'Codice Documento ' . $elementi['codice'] . ' già presente.';
            $result['messageResult'] = $messageResult;
            return $result;
        }

        $segDatiIndice->assegnaDatiDaElementi($elementi);

//        echo(print_r($segDatiIndice, true));
//        exit;
        /*
         * FINALIZZAZIONE DEGLI INSERIMENTI
         */
        $model = 'segIndice.class';
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $segIndice = new segIndice();
        $Indice_rec = $segIndice->registraIndice($segDatiIndice);
        if ($Indice_rec === false) {
            $messageResult['tipoRisultato'] = 'Error';
            $messageResult['descrizione'] = $segDatiIndice->getTitle() . " " . $segDatiIndice->getmessage();
            $result['messageResult'] = $messageResult;
            return $result;
        }
        $Anapro_rec = $proLib->GetAnapro($Indice_rec['INDPRO'], 'codice', $Indice_rec['INDPAR']);
        
        
        $this->result['messageResult'] = array(
            'tipoRisultato' => "Info",
            'descrizione' => "Documento Creato Correttamente."
        );

        $this->result['datiDocumento']['rowidDocumento'] = $Indice_rec['ROWID'];
        $this->result['datiDocumento']['annoDocumento'] = substr($Anapro_rec['PRONUM'], 0, 4);
        $this->result['datiDocumento']['numeroDocumento'] = $Indice_rec['IDELIB'];

        return $this->result;
 
    }
    
    

}