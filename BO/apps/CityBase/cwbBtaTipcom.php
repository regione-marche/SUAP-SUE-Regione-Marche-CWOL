<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

function cwbBtaTipcom() {
    $cwbBtaTipcom = new cwbBtaTipcom();
    $cwbBtaTipcom->parseEvent();
    return;
}

class cwbBtaTipcom extends cwbBpaGenTab {

    function initVars() {
        $this->GRID_NAME = 'gridBtaTipcom';
        $this->TIPO_COM = "TIPO_COM";
        $this->AUTOR_MODULO = 'BTA';
        $this->AUTOR_NUMERO = 27;
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BTA();
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }
    
    protected function preParseEvent() {
        switch ($_POST['event']) {
// * Blocca il Cancella le comunicazioni dei servizi economici che sono già state utilizzate in BTA_SOGINV?
//      Il Modifica viene bloccato dal Validator solo per il cambi tipologia comunicazione FLAG_TIPCO
            case 'delRowInline':
            case 'delGridRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_' . $this->GRID_NAME:
                        $TIPO_COM = $this->formData['rowid'];
                        $filtri = array();
                        $filtri['TIPO_COM'] = $TIPO_COM;
                        $btaSoginv = $this->libDB->leggiGeneric('BTA_SOGINV', $filtri);

                        if (empty(!$btaSoginv) && count($btaSoginv)>0){
                            Out::msgStop("Attenzione!", "Non è possibile cancellare il Tipo comunicazione, comunicazione già utilizzata.");
                            $this->setBreakEvent(true);
                        }
                        break;
                }
                break;
        }
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TIPO_COM':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_TIPO_COM'], $this->nameForm . '_TIPO_COM');
                        break;
                    case $this->nameForm . '_BTA_TIPCOM[TIPO_COM]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['TIPO_COM'], $this->nameForm . '_BTA_TIPCOM[TIPO_COM]');
                        break;
                }
                break;
        }
    }

    protected function setGridFilters() {
        $this->gridFilters = array();
        if ($_POST['TIPO_COM_formatted'] != '') {
            $this->gridFilters['TIPO_COM'] = $this->formData['TIPO_COM_formatted'];
        }
        if ($_POST['DES_TIPCOM'] != '') {
            $this->gridFilters['DES_TIPCOM'] = $this->formData['DES_TIPCOM'];
        }
        if ($_POST['POSIZ_FILE'] != '') {
            $this->gridFilters['POSIZ_FILE'] = $this->formData['POSIZ_FILE'];
        }
        if ($_POST['DES_TIPCOM'] != '') {
            $this->gridFilters['DES_TIPCOM'] = $this->formData['DES_TIPCOM'];
        }
        if ($_POST['DES_TIPCOM'] != '') {
            $this->gridFilters['DES_TIPCOM'] = $this->formData['DES_TIPCOM'];
        }
    }

    protected function postApriForm() {
        $this->initComProv();
        $this->initFlagTipco();
        $this->initPosFile();
        Out::setFocus("", $this->nameForm . '_DES_TIPCOM');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DES_TIPCOM');
    }

    protected function postNuovo() {
        Out::attributo($this->nameForm . '_BTA_TIPCOM[TIPO_COM]', 'readonly', '1');
        Out::css($this->nameForm . '_BTA_TIPCOM[TIPO_COM]', 'background-color', '#FFFFFF');
        Out::setFocus("", $this->nameForm . '_BTA_TIPCOM[TIPO_COM]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BTA_TIPCOM[TIPO_COM]');
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BTA_TIPCOM[TIPO_COM]', 'readonly', '0');
        Out::css($this->nameForm . '_BTA_TIPCOM[TIPO_COM]', 'background-color', '#FFFFE0');
        Out::setFocus('', $this->nameForm . '_BTA_TIPCOM[DES_TIPCOM]');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_BTA_TIPCOM[DES_TIPCOM]', trim($this->CURRENT_RECORD['DES_TIPCOM']));
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['TIPO_COM'] = trim($this->formData[$this->nameForm . '_TIPO_COM']);
        $filtri['DES_TIPCOM'] = trim($this->formData[$this->nameForm . '_DES_TIPCOM']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBtaTipcom($filtri, true, $sqlParams);
    }

    public function sqlDettaglio($index, &$sqlParams = array()) {
        $this->SQL = $this->libDB->getSqlLeggiBtaTipcomChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['TIPO_COM_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['TIPO_COM']);

            //imposto manualmente la Provenienza Comunicazione nella grid
            if ($Result_tab[$key]['COMPROV'] == 0 || $Result_tab[$key]['COMPROV'] == 1) { // per replicare funzionamento del vecchio, 
                $Result_tab[$key]['COMPROV_formatted'] = '1-Tutte';                     // se COMPROV Ã¨ 0, metto comunque "Tutte".
            }

            if ($Result_tab[$key]['COMPROV'] == 2) {
                $Result_tab[$key]['COMPROV_formatted'] = '2-Tributi';
            }

            if ($Result_tab[$key]['COMPROV'] == 3) {
                $Result_tab[$key]['COMPROV_formatted'] = '3-Recupero Crediti';
            }

            if ($Result_tab[$key]['COMPROV'] == 4) {
                $Result_tab[$key]['COMPROV_formatted'] = '4-Anagrafe';
            }
            
            if ($Result_tab[$key]['COMPROV'] == 5) {
                $Result_tab[$key]['COMPROV_formatted'] = '5-Serv.Economici';
            }

            if ($Result_tab[$key]['POSIZ_FILE'] == 0) {
                $Result_tab[$key]['POSIZ_FILE_formatted'] = 'Interna';
            }

            if ($Result_tab[$key]['POSIZ_FILE'] == 1) {
                $Result_tab[$key]['POSIZ_FILE_formatted'] = 'Esterna';
            }
            
            //imposto manualmente la Tipologia Comunicazione nella grid
            if ($Result_tab[$key]['FLAG_TIPCO'] == 0) {
                $Result_tab[$key]['FLAG_TIPCO_formatted'] = '0-Comunicaz.generica';
            }
            if ($Result_tab[$key]['FLAG_TIPCO'] == 1) {
                $Result_tab[$key]['FLAG_TIPCO_formatted'] = '1-Primo estratto conto';
            }
            if ($Result_tab[$key]['FLAG_TIPCO'] == 2) {
                $Result_tab[$key]['FLAG_TIPCO_formatted'] = '2-Secondo estratto conto';
            }
            
            if ($Result_tab[$key]['FLAG_TIPCO'] == 3) {
                $Result_tab[$key]['FLAG_TIPCO_formatted'] = '3-Primo sollecito';
            }

            if ($Result_tab[$key]['FLAG_TIPCO'] == 4) {
                $Result_tab[$key]['FLAG_TIPCO_formatted'] = '4-Secondo sollecito';
            }

            if ($Result_tab[$key]['FLAG_TIPCO'] == 7) {
                $Result_tab[$key]['FLAG_TIPCO_formatted'] = '7-Recupero crediti via mail Demanio';
            }
            if ($Result_tab[$key]['FLAG_TIPCO'] == 8) {
                $Result_tab[$key]['FLAG_TIPCO_formatted'] = '8-Recupero crediti via mail Legale';
            }
            
            if ($Result_tab[$key]['FLAG_TIPCO'] == 9) {
                $Result_tab[$key]['FLAG_TIPCO_formatted'] = '9-Procedura recupero crediti';
            }
        }
        return $Result_tab;
    }

    private function initComProv() {
        // Provenienza Comunicazione
        Out::select($this->nameForm . '_BTA_TIPCOM[COMPROV]', 1, "1", 0, "1-Tutte");
        Out::select($this->nameForm . '_BTA_TIPCOM[COMPROV]', 1, "2", 0, "2-Tributi");
        Out::select($this->nameForm . '_BTA_TIPCOM[COMPROV]', 1, "3", 0, "3-Recupero Crediti");
        Out::select($this->nameForm . '_BTA_TIPCOM[COMPROV]', 1, "4", 0, "4-Anagrafe");
        Out::select($this->nameForm . '_BTA_TIPCOM[COMPROV]', 1, "5", 0, "5-Serv.Economici");
    }
    
    private function initFlagTipco() {
        // Tipologia Comunicazione
        Out::select($this->nameForm . '_BTA_TIPCOM[FLAG_TIPCO]', 1, "0", 0, "0 - Generica");
        Out::select($this->nameForm . '_BTA_TIPCOM[FLAG_TIPCO]', 1, "1", 0, "1 - primo estratto conto");
        Out::select($this->nameForm . '_BTA_TIPCOM[FLAG_TIPCO]', 1, "2", 0, "2 - secondo estratto conto");
        Out::select($this->nameForm . '_BTA_TIPCOM[FLAG_TIPCO]', 1, "3", 0, "3 - primo sollecito");
        Out::select($this->nameForm . '_BTA_TIPCOM[FLAG_TIPCO]', 1, "4", 0, "4 - secondo sollecito");
        Out::select($this->nameForm . '_BTA_TIPCOM[FLAG_TIPCO]', 1, "7", 0, "7 - Recupero crediti via mail Demanio");
        Out::select($this->nameForm . '_BTA_TIPCOM[FLAG_TIPCO]', 1, "8", 0, "8 - Recupero crediti via mail Legale");
        Out::select($this->nameForm . '_BTA_TIPCOM[FLAG_TIPCO]', 1, "9", 0, "9 - Procedura recupero crediti");
    }

    private function initPosFile() {
        // Posizione File
        Out::select($this->nameForm . '_BTA_TIPCOM[POSIZ_FILE]', 1, "0", 1, "Interna ");
        Out::select($this->nameForm . '_BTA_TIPCOM[POSIZ_FILE]', 1, "1", 0, "Esterna");
    }
}

