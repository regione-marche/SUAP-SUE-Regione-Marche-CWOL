<?php

class praDoc extends itaModelFO {

    public $praLib;
    public $praErr;
    public $PRAM_DB;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);

            /*
             * Caricamento database.
             */
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
        } catch (Exception $e) {
            
        }
    }

    public function parseEvent() {
        output::$html_out = '';

        $userName = frontOfficeApp::$cmsHost->getUserName();
        $userFiscale = frontOfficeApp::$cmsHost->getCodFisFromUtente();

        if ($userFiscale == "") {
            $alertMessage = sprintf('L\'utente %s non è adatto per accedere alla consultazione dei documenti perchè manca il codice fiscale nel suo profilo.', $userName);
            output::addAlert($alertMessage, 'Attenzione', 'warning');
            return output::$html_out;
        }

        /*
         * Istanzio il template se c'è
         */
        $templateClass = 'praHtmlDoc';
        if ($this->config['template']) {
            if (file_exists(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/' . $this->config['template'] . '.class.php')) {
                $templateClass = $this->config['template'];
            }
        }

        require_once ITA_PRATICHE_PATH . "/PRATICHE_italsoft/$templateClass.class.php";
        $praHtmlDoc = new $templateClass;


        switch ($this->request['event']) {
            case "gestioneAllegato":
                $dati = $extraParams = array();
                $dati['fileId'] = $this->request['fileId'];
                $dati['operation'] = $this->request['operation'];
                $extraParams['PRAM_DB'] = $this->PRAM_DB;
                if (!$praHtmlDoc->GestioneAllegato($dati, $extraParams)) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0010', $praHtmlDoc->getErrMessage(), __CLASS__);
                }
                break;

            case "dettaglio":
                $dati = $extraParams = array();
                $dati['ricnum'] = $this->request['ricnum'];
                $dati['propak'] = $this->request['propak'];
                $dati['docnumber'] = $this->request['docnumber'];
                $dati['istat'] = $this->request['istat'];
                $extraParams['online_page'] = $this->config['online_page'];
                $extraParams['PRAM_DB'] = $this->PRAM_DB;
                if (!$praHtmlDoc->Dettaglio($dati, $extraParams)) {
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0091', $praHtmlDoc->getErrMessage(), __CLASS__);
                }
                break;
            case "ricerca":
            default:
                $dati = $extraParams = array();
                $dati['ricnum'] = $this->request['ricnum'];
                $dati['parent'] = $this->request['parent'];
                $dati['fiscale'] = $userFiscale;
                $extraParams['online_page'] = $this->config['online_page'];
                $extraParams['PRAM_DB'] = $this->PRAM_DB;
                if(!$praHtmlDoc->DisegnaPagina($dati, $extraParams)){
                    output::$html_out = $this->praErr->parseError(__FILE__, 'E0012', $praHtmlDoc->getErrMessage(), __CLASS__);
                }
                break;
        }

        return output::$html_out;
    }

}
