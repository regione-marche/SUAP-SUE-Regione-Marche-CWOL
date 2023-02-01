<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';

function cwbBorAoo() {
    $cwbBorAoo = new cwbBorAoo();
    $cwbBorAoo->parseEvent();
    return;
}

class cwbBorAoo extends cwbBpaGenTab {
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBorAoo';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    function initVars() {
        $this->GRID_NAME = 'gridBorAoo';
        $this->libDB = new cwbLibDB_BOR();
        $this->AUTOR_MODULO = 'BOR';
        $this->AUTOR_NUMERO = 14;
        $this->elencaAutoAudit = true;
        $this->elencaAutoFlagDis = true;
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_BOR_AOO[IDORGAN]_butt':
                        cwbLib::apriFinestraRicerca('cwbBorOrgan', $this->nameForm, 'returnFromBorOrgan', $_POST['id'], true);
                        break;
                }
                break;
            case 'returnFromBorOrgan':
                switch ($this->elementId) {
                    case $this->nameForm . '_BOR_AOO[IDORGAN]_butt':
                        $this->setIdorgan($this->formData['returnData']);
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_IDAOO':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_IDAOO'], $this->nameForm . '_IDAOO');
                        break;
                    case $this->nameForm . '_BOR_AOO[IDUTRUOR_P]':
                        cwbLibCheckInput::checkNumeric($_POST[$this->nameForm . '_' . $this->TABLE_NAME]['IDUTRUOR_P'], $this->nameForm . '_BOR_AOO[IDUTRUOR_P]');
                        break;
                    case $this->nameForm . '_BOR_AOO[IDORGAN]':
                        $this->setIdorganFromDB($_POST[$this->nameForm . '_BOR_AOO']['IDORGAN']);
                        break;
                }
                break;
        }
    }
    
    private function setIdorgan($data){
        Out::valore($this->nameForm . '_BOR_AOO[IDORGAN]', $data['IDORGAN']);
        Out::valore($this->nameForm . '_DESPORG_decod', $data['DESPORG']);
    }
    
    private function setIdorganFromDB($idorgan){
        if(empty($idorgan)){
            $data = null;
        }
        else{
            $filtri = array(
                'IDORGAN'=>$idorgan
            );
            $data = $this->libDB->leggiBorOrgan($filtri, false);
        }
        $this->setIdorgan($data);
    }

    protected function postApriForm() {
        $this->initComboNatura();
        $this->initComboEnti();
        Out::setFocus("", $this->nameForm . '_DESAOO');
    }

    protected function postAltraRicerca() {
        Out::setFocus("", $this->nameForm . '_DESAOO');
    }

    protected function postNuovo() {
        Out::hide($this->nameForm . '_BOR_AOO[IDAOO]_field');
        Out::setFocus("", $this->nameForm . '_BOR_AOO[CODAOOIPA]');
    }

    protected function preDettaglio() {
        $this->pulisciCampi();
    }

    protected function postDettaglio($index) {
        Out::attributo($this->nameForm . '_BOR_AOO[IDAOO]', 'readonly', '0');
        Out::setFocus('', $this->nameForm . '_BOR_AOO[DESAOO]');
        Out::show($this->nameForm . '_BOR_AOO[IDAOO]_field');
        // toglie gli spazi del char
        Out::valore($this->nameForm . '_AOO_IDAOO', trim($this->CURRENT_RECORD['AOO_IDAOO']));
        Out::valore($this->nameForm . '_AOO_DOCER', trim($this->CURRENT_RECORD['AOO_DOCER']));
        Out::valore($this->nameForm . '_BOR_AOO[DESAOO]', trim($this->CURRENT_RECORD['DESAOO']));
        $this->setIdorganFromDB($this->CURRENT_RECORD['IDORGAN']);
    }

    public function postSqlElenca($filtri, &$sqlParams = array()) {
        $filtri['IDAOO'] = trim($this->formData[$this->nameForm . '_IDAOO']);
        $filtri['DESAOO'] = trim($this->formData[$this->nameForm . '_DESAOO']);
        $filtri['CODAOOIPA'] = trim($this->formData[$this->nameForm . '_CODAOOIPA']);
        $filtri['PROGENTE'] = trim($this->formData[$this->nameForm . '_PROGENTE']);
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBorAoo($filtri, true, $sqlParams);
    }

    protected function postPulisciCampi() {
        Out::valore($this->nameForm . '_DESPORG_decod', '');
        Out::valore($this->nameForm . '_AOO_DOCER', '');
    }

    public function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBorAooChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        foreach ($Result_tab as $key => $Result_rec) {
            $Result_tab[$key]['CODAREAMA_formatted'] = cwbLibHtml::formatDataGridCod($Result_tab[$key]['CODAREAMA']);
        }
        return $Result_tab;
    }

    protected function caricaDatiAggiuntivi($tipoOperazione) {
        //One-To-One
        $tableData = array("operation" => $tipoOperazione,
            "data" => $this->popolaCampiAooDc()
        );

        $this->modelData->addRelationOneToOne("BOR_AOODC", $tableData, "", array("IDAOO" => "IDAOO"));
    }

    private function popolaCampiAooDc() {
        // valorizzare in lettura anche la chiave primaria nel caso leggo il record con il dettaglio 
        // + l'unico modo che ho per sapere se devo fare update\delete 
        return array(
            'AOO_DOCER' => $_POST[$this->nameForm . '_AOO_DOCER'],
            'IDAOO' => $this->CURRENT_RECORD['IDAOO'],
            'TIMEOPER' => $this->CURRENT_RECORD['TIMEOPER'],
            'DATAOPER' => $this->CURRENT_RECORD['DATAOPER'],
            'CODUTE' => $this->CURRENT_RECORD['CODUTE']
        );
    }

    protected function postAggiorna() {
        Out::setFocus("", $this->nameForm . '_BOR_AOO[CODAOOIPA]');
    }

    protected function postAggiungi() {
        Out::setFocus("", $this->nameForm . '_BOR_AOO[CODAOOIPA]');
    }

    protected function postConfermaCancella() {
        // cancella su tabella 1:1 entedc
        $this->deleteRecord($this->MAIN_DB, 'BOR_AOODC', $this->CURRENT_RECORD[$this->PK], $this->RECORD_INFO, $this->PK);
    }

    private function initComboEnti() {
        $this->initComboEntiGest();
        $this->initComboEntiRic();
    }

    private function initComboEntiRic() {
        // Azzera combo
        Out::html($this->nameForm . '_PROGENTE', '');
        Out::select($this->nameForm . '_PROGENTE', 1, '', 1, "--- TUTTI ---");


        // Carica lista aree
        $enti = $this->libDB->leggiBorEnti(array());
        $elementi = count($enti); // conto numero di enti presenti
        if ($elementi == 1) {      // Se ho solamente un ente, nascondo la combo
            Out::hide($this->nameForm . '_PROGENTE_field');
            return;
        }

        // Popola combo in funzione dei dati caricati da db
        foreach ($enti as $ente) {
            Out::select($this->nameForm . '_PROGENTE', 1, $ente['PROGENTE'], 0, trim($ente['PROGENTE'] . ' - ' . $ente['DES_BREVE']));
        }
    }

    private function initComboEntiGest() {

        // Azzera combo
        Out::html($this->nameForm . '_BOR_AOO[PROGENTE]', '');

        // Carica lista aree
        $enti = $this->libDB->leggiBorEnti(array());
        $elementi = count($enti); // conto numero di enti presenti
        if ($elementi == 1) {      // Se ho solamente un ente, nascondo la combo
            Out::hide($this->nameForm . '_BOR_AOO[PROGENTE]_field');
            return;
        }

        // Popola combo in funzione dei dati caricati da db
        foreach ($enti as $ente) {
            Out::select($this->nameForm . '_BOR_AOO[PROGENTE]', 1, $ente['PROGENTE'], 0, trim($ente['PROGENTE'] . ' - ' . $ente['DES_BREVE']));
        }
    }

    private function initComboNatura() {
        // Natura
        Out::select($this->nameForm . '_BOR_AOO[NATURA]', 1, "1", 1, "1=AOO");
        Out::select($this->nameForm . '_BOR_AOO[NATURA]', 1, "2", 0, "2=NOAOO");
        Out::select($this->nameForm . '_BOR_AOO[NATURA]', 1, "3", 0, "3=AOOP");
        Out::select($this->nameForm . '_BOR_AOO[NATURA]', 1, "4", 0, "4=AOOT");
        Out::select($this->nameForm . '_BOR_AOO[NATURA]', 1, "5", 0, "5=ALTRO");
    }

}

