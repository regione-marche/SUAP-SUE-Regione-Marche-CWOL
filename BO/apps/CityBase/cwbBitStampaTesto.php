<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BIT.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibTestiMain.class.php';
include_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibDanNuoviEventiUtils.class.php';

function cwbBitStampaTesto() {
    $cwbBitStampaTesto = new cwbBitStampaTesto();
    $cwbBitStampaTesto->parseEvent();
    return;
}

class cwbBitStampaTesto extends cwbBpaGenTab {

    private $libANPR;

    protected function postConstruct() {
        $this->arrayTesti = cwbParGen::getFormSessionVar($this->nameForm, 'arrayTesti');
        $this->masterRecord = cwbParGen::getFormSessionVar($this->nameForm, 'masterRecord');
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, 'arrayTesti', $this->arrayTesti);
            cwbParGen::setFormSessionVar($this->nameForm, 'masterRecord', $this->masterRecord);
        }
    }

    protected function initVars() {
        $this->GRID_NAME = 'gridDtaTesti';
        $this->searchOpenElenco = true;
        $this->libDB = new cwbLibDB_BIT();
        $this->noCrud = true;
        $this->cwdLibDanNuoviEventiUtils = new cwdLibDanNuoviEventiUtils();
    }

    protected function postApriForm() {
        Out::setFocus("", $this->nameForm . $this->GRID_NAME);
    }

    protected function postSqlElenca($filtri, &$sqlParams) {
        $filtri['CODITER'] = $this->externalParams['CODITER'];
        $filtri['FLAG_DIS'] = 0;
        $this->compilaFiltri($filtri);
        $this->SQL = $this->libDB->getSqlLeggiBitIterTesti($filtri, true, $sqlParams);
    }

    protected function sqlDettaglio($index, &$sqlParams) {
        $this->SQL = $this->libDB->getSqlLeggiBitIterTestiChiave($index, $sqlParams);
    }

    protected function elaboraRecords($Result_tab) {
        //eseguo la select su bit_iter per decodificare Iter
        $rowIter = $this->libDB->leggiBitIterChiave($this->externalParams['CODITER']);
        out::valore($this->nameForm . '_descrizionePasso', $rowIter['DESITER']);

        foreach ($Result_tab as &$value) {
            $value['tdStampa'] = ' ';
        }

        $this->arrayTesti = $Result_tab;
        //tdStampa
        return $Result_tab;
    }

    public function customParseEvent() {
        switch ($_POST['event']) {
            case 'onClick':
                switch ($this->elementId) {
                    case $this->nameForm . '_Stampa':
                        $this->stampaTesti();
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->nameForm . '_gridDtaTesti':
                        switch ($_POST['colName']) {
                            case 'tdStampa':
                                $this->setChecktdStampaTesti();
                                break;
                        }
                        break;
                }
                break;
        }
    }

    private function setChecktdStampaTesti() {
        $dati = $this->arrayTesti;
        $riga = $_POST['rowid'] - 1;
        if (strpos($_POST['cellContent'], 'check') === false) {
            $valore = 1;
            Out::setCellValue($this->nameForm . '_' . $this->GRID_NAME, $_POST['rowid'], 'tdStampa', '<span class="ui-icon ui-icon-check" style="display: inline-block;"></span>');
        } else {
            $valore = ' ';
            Out::setCellValue($this->nameForm . '_' . $this->GRID_NAME, $_POST['rowid'], 'tdStampa', '&nbsp;');
        }

        $dati[$riga]['tdStampa'] = $valore;
        $this->arrayTesti = $dati;
    }

    protected function stampaTesti() {

        foreach ($this->arrayTesti as $key => $value) {
            if ($value['tdStampa'] == 1) {
                $file = '';
                //allora posso eseguire la stampa del testo indicato

                $file = $this->stampaPraticaDaIter($value['CODTESTO']);
                if ($file) {
                    $arrayFile[]['NOME'] = $file[0]['NOME'];
//                    $nomefile = $file[0]['NOME'];
//                    // Apre il pdf nell'editor interno
//                    cwbLib::apriVisualizzatoreDocumenti($file);
                }
            } else {
                $conta++;
            }
        }
        if (is_array($arrayFile)) {
            // Apre il pdf nell'editor interno 
            cwbLib::apriVisualizzatoreDocumenti($arrayFile);
        } else {
            if (count($this->arrayTesti) == $conta) {
                Out::msgStop("ATTENZIONE ", "Non è stato selezionato nessun Testo per la Stampa. Selezionarli per procedere con la Stampa.");
            } else {
                Out::msgStop("Errore", "Errore stampa testo");
            }
        }
    }

    public function stampaPraticaDaIter($testo) {

        // Pulisco la lista perchè se è già stata utilizzata fa del casino
        $lista_testi = false;
        $libTestiMain = new cwbLibTestiMain();

        // Determino la lista guida del testo per sapere se devo caricare la lista guida PRATI o ANA o PRATIM
        $libTestiMain->setCdoTesto($testo);

        $loc_lista_stampa = '';
        $loc_usa = $libTestiMain->esistenza_lista('PRATI');
        if ($loc_usa == 1) {     // Significa che la lista è GUIDA
            $loc_sql = '';
            $loc_sql .= '   SELECT * FROM DAN_PRATI_V01';
            $loc_sql .= ' WHERE CODPRAT=' . $this->masterRecord['CODPRAT'];
            $loc_Esito = $this->libDB->leggi($loc_sql, $loc_lista_stampa);
            if (!$loc_Esito) {
                Out::msgStop('Stampa Pratica da Iter', 'Non è stato possibile caricare i dati  del soggetto per la stampa DAN_PRATI_V01');
                return false;
            }

            // Definisco quale lista guida devo utilizzare
            //Imposto i parametri per la stampa
            $methodArgs[0] = $this->masterRecord['CODPRAT']; //Chiave per Lista Guida
            $methodArgs[1] = $loc_lista_stampa; //Lista guida ANA2, se la carica con la chiave
            $methodArgs[2] = $testo; //Testo da stampare
            $methodArgs[3] = 1; //Copie
            $methodArgs[4] = 'PRATI'; //Lista
            $methodArgs[5] = 'GUIDA'; //TipoLista
            $methodArgs[6] = 'DAN_PRATI_V01'; //Vista
        }

        $loc_usa = $libTestiMain->esistenza_lista('ANA2');
        if ($loc_usa == 1) {     // Significa che la lista è GUIDA
            //Prima devo reperire le matricole poi eseguo la query 
            $loc_sql = '';
            $loc_sql .= '   SELECT pm.progsogg FROM DAN_PRATI p, dan_pratim pm ';
            $loc_sql .= "  WHERE p.codprat = " . $this->masterRecord['CODPRAT'] . " AND p.pr_pratich = pm.pr_pratich and pm.comp_stato in (' ', 'P', 'C')";
            $this->libDB->leggi($loc_sql, $loc_lista_prog);

            $loc_sql = '';
            $loc_sql .= '   SELECT a.* FROM DAN_ANAGRA_V02 a ';
            $loc_sql .= "  WHERE a.progsogg in (";
            $virgola = '';
            foreach ($loc_lista_prog as $value) {
                $loc_sql .= $virgola . " " . $value;
                $virgola = ',';
            }
            $loc_sql .= " )";
            $this->libDB->leggi($loc_sql, $loc_lista_stampa);

            $listProg = array();
            foreach ($loc_lista_stampa as $value) {
                $listProg[]['PROGSOGG'] = $value;
            }

            // Definisco quale lista guida devo utilizzare
            //Imposto i parametri per la stampa
            $methodArgs[0] = $this->masterRecord['CODPRAT']; //CODICE PR_PRATICH
            $methodArgs[1] = $loc_lista_stampa; //Lista guida ANA2, se la carica con la chiave
            $methodArgs[2] = $testo; //Testo da stampare
            $methodArgs[3] = 1; //Copie
            $methodArgs[4] = 'ANA2'; //Lista
            $methodArgs[5] = 'GUIDA'; //TipoLista
            $methodArgs[6] = 'DAN_ANAGRA_V02'; //Vista
        }

        $loc_usa = $libTestiMain->esistenza_lista('PRATIM3');
        if ($loc_usa == 1) {     // Significa che la lista è GUIDA
            //Prima devo reperire le matricole poi eseguo la query 
            $loc_sql = '';
            $loc_sql .= '   SELECT pm.*, p.codprat FROM DAN_PRATIM_V03 pm, dan_prati p ';
            $loc_sql .= "  WHERE p.pr_pratich = pm.pr_pratich and p.codprat = " . $this->masterRecord['CODPRAT'] . " AND pm.comp_stato in (' ', 'P', 'C')";
            $loc_Esito = $this->libDB->leggi($loc_sql, $loc_lista_stampa);
            if (!$loc_Esito) {
                Out::msgStop('Stampa Pratica da Iter', 'Non è stato possibile caricare i dati  del soggetto per la stampa DAN_PRATI_V01');
                return false;
            }

            // Definisco quale lista guida devo utilizzare
            //Imposto i parametri per la stampa
            $methodArgs[0] = $this->masterRecord['CODPRAT']; //CODICE PR_PRATICH
            $methodArgs[1] = $loc_lista_stampa; //Lista guida ANA2, se la carica con la chiave
            $methodArgs[2] = $testo; //Testo da stampare
            $methodArgs[3] = 1; //Copie
            $methodArgs[4] = 'PRATIM3'; //Lista
            $methodArgs[5] = 'GUIDA'; //TipoLista
            $methodArgs[6] = 'DAN_PRATIM_V03'; //Vista
        }

        if (!is_null($methodArgs)) {
            return $this->stampaTesto($methodArgs);
        } else {
            out::msgStop('Stampa Pratica da Iter', 'il soggetto non soddisfa i requisiti per la stampa di questo testo - ANA2');
            return false;
        }

        Return false;
    }

    protected function stampaTesto($methodArgs) {
        $methodArgs[7] = 'PDF'; // TODO verificare parametri
        $methodArgs[8] = cwbParGen::getNomeUte(); // TODO Utente Corrente
        $result = $this->cwdLibDanNuoviEventiUtils->stampa_PraticaDaIter($methodArgs);
        $file = $this->returnTestoRisolto($result);

        return $file;
    }

    private function returnTestoRisolto($result) {
        if ($result['RESULT']['EXITCODE'] == 'S') {
            //Converto il file da esadecimale a binario
            $attachments = pack("H*", $result['RESULT']['LIST']['ROW']['TESTO']);
            $fileName = array_pop(explode('\\', $result['RESULT']['MESSAGE']));

            //Verifico che esistano le sottocartelle
            $pathDest = App::getPath('temporary.appsPath') . '/' . 'APR';
            if (!file_exists($pathDest)) {
                mkdir($pathDest, 0777, true);
            }
            $pathDest = App::getPath('temporary.appsPath') . '/' . 'APR' . '/attachments/';
            if (!file_exists($pathDest)) {
                mkdir($pathDest, 0777, true);
            }
            $pathDest = App::getPath('temporary.appsPath') . '/' . 'APR' . '/attachments/' . $fileName;

            //Creo il file
            $myfile = fopen($pathDest, "w");
            fwrite($myfile, $attachments);
            fclose($myfile);

            $files[count($files)] = array('NOME' => $pathDest);

            //Apro il visualizzatore dei documenti
            if (count($files) > 0) {
                return $files;
            }
        } else {
            Out::msgStop('Stampa Pratica da Iter', "Errore " . $result['RESULT']['EXITCODE'] . ' - Motivo: ' . $result['RESULT']['MESSAGE']);
        }
        return false;
    }

    public function setMasterRecord($masterRecord) {
        $this->masterRecord = $masterRecord;
    }

    protected function nessunRecordMessage() {
        Out::msgStop("Selezione Testi da stampare", "Nessun testo relativo all'iter indicato.");
    }

    protected function preParseEvent() {
        switch ($_POST['event']) {
            case 'dbClickRow':
                if ($_POST['id'] == $this->nameForm . '_' . $this->GRID_NAME) {
                    //termina l'esecuzione dell'evento sul doppio click della grid
                    $this->setBreakEvent(true);
                }
                break;
        }
    }

}

?>