<?php

require_once(ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php');

class praVars {

    private $dati;
    private $variabiliRichiesta;
    private $variabiliBase;
    private $variabiliBaseAggiuntivi;
    private $variabiliGenerali;
    private $variabiliCampiAggiuntivi;
    private $variabiliTipiAggiuntivi;
    private $variabiliTemplateUpload;
    private $variabiliUpload;
    private $variabiliDistinta;
    private $variabiliDistintaAllegati;
    private $variabiliEsibente;
    private $variabiliTariffe;
    private $variabiliAmbiente;
    private $variabiliParent;
    private $variabiliRichiesteAccorpate;
    private $variabiliTipologiaPassi;
    private $variabiliSoggetti;
    private $variabiliInformativa;
    private $variabiliProcedimento;
    private $destinazione;
    private $classificazione;
    private $nomeOrig;
    private $PRAM_DB;
    private $GAFIERE_DB;

    public function getPRAM_DB() {
        return $this->PRAM_DB;
    }

    public function getGAFIERE_DB() {
        return $this->GAFIERE_DB;
    }

    public function setPRAM_DB($PRAM_DB) {
        $this->PRAM_DB = $PRAM_DB;
    }

    public function setGAFIERE_DB($GAFIERE_DB) {
        $this->GAFIERE_DB = $GAFIERE_DB;
    }

    public function getVariabiliTemplateUpload() {
        $this->variabiliTemplateUpload = new itaDictionary();
        $this->variabiliTemplateUpload->addField('NOMEUPLOAD', 'Variabili Template nome upload', 1, 'itaDictionary', $this->variabiliUpload);
        return $this->variabiliTemplateUpload;
    }

    public function getVariabiliDistintaBase() {
        return $this->variabiliDistinta;
    }

    public function getVariabiliDistinta() {
        $this->variabiliDistintaAllegati = new itaDictionary();
        $this->variabiliDistintaAllegati->addField('PRAALLEGATI', 'Variabili Distinta', 1, 'itaDictionary', $this->variabiliDistinta);
        return $this->variabiliDistintaAllegati;
    }

    public function getVariabiliRichiesteAccorpate() {
        return $this->variabiliRichiesteAccorpate;
    }

    public function getVariabiliRichiesta() {
        $this->variabiliRichiesta = new itaDictionary();
        if (is_a($this->variabiliBase, 'itaDictionary')) {
            $this->variabiliRichiesta->addField('PRABASE', 'Variabili Base Procedimento', 1, 'itaDictionary', $this->variabiliBase);
        }
        if (is_a($this->variabiliBaseAggiuntivi, 'itaDictionary')) {
            $this->variabiliRichiesta->addField('PRABASEAGG', 'Variabili Base Aggiuntive del Procedimento', 1, 'itaDictionary', $this->variabiliBaseAggiuntivi);
        }

        if (is_a($this->variabiliGenerali, 'itaDictionary')) {
            $this->variabiliRichiesta->addField('PRAGENERALI', 'Variabili Generali', 2, 'itaDictionary', $this->variabiliGenerali);
        }
        $this->variabiliRichiesta->addField('PRAESIBENTE', 'Variabili Esibente', 3, 'itaDictionary', $this->variabiliEsibente);

        if (is_a($this->variabiliCampiAggiuntivi, 'itaDictionary')) {
            $this->variabiliRichiesta->addField('PRAAGGIUNTIVI', 'Variabili Campi Aggiuntivi Procedimento', 4, 'itaDictionary', $this->variabiliCampiAggiuntivi);
        }
        if (is_a($this->variabiliTipiAggiuntivi, 'itaDictionary')) {
            $this->variabiliRichiesta->addField('PRATIPI', 'Variabili Tipi Aggiuntivi Procedimento', 5, 'itaDictionary', $this->variabiliTipiAggiuntivi);
        }

        $this->variabiliRichiesta->addField('LISTINO', 'Variabili Tariffe', 6, 'itaDictionary', $this->variabiliTariffe);

        if (is_a($this->variabiliRichiesteAccorpate, 'itaDictionary')) {
            $this->variabiliRichiesta->addField('PRAACCORPATE', 'Variabili Richieste Accorpate', 7, 'itaDictionary', $this->variabiliRichiesteAccorpate);
        }

        //if (is_a($this->variabiliAmbiente, 'itaDictionary')) {
        $this->variabiliRichiesta->addField('AMBIENTE', 'Variabili Ambiente', 8, 'itaDictionary', $this->variabiliAmbiente);
        //}
        if (is_a($this->variabiliParent, 'itaDictionary')) {
            $this->variabiliRichiesta->addField('PARENT', 'Variabili Pratica Unica', 8, 'itaDictionary', $this->variabiliParent);
        }

        if (is_a($this->variabiliTipologiaPassi, 'itaDictionary')) {
            $this->variabiliRichiesta->addField('PRATIPOPASSI', 'Variabili per tipologia passo', 9, 'itaDictionary', $this->variabiliTipologiaPassi);
        }

        if (is_a($this->variabiliDistinta, 'itaDictionary')) {
            $this->variabiliRichiesta->addField('PRAALLEGATI', 'Variabili Allegati', 10, 'itaDictionary', $this->variabiliDistinta);
        }

        if (is_a($this->variabiliSoggetti, 'itaDictionary')) {
            $this->variabiliRichiesta->addField('PRASOGGETTI', 'Variabili Soggetti', 11, 'itaDictionary', $this->variabiliSoggetti);
        }

        return $this->variabiliRichiesta;
    }

    public function getVariabiliGenerali() {
        return $this->variabiliGenerali;
    }

    public function getVariabiliUpload() {
        return $this->variabiliUpload;
    }

    public function setVariabiliRichiesta($variabiliRichiesta) {
        $this->variabiliRichiesta = $variabiliRichiesta;
    }

    public function setVariabiliUpload($variabiliUpload) {
        $this->variabiliUpload = $variabiliUpload;
    }

    public function setVariabiliGenerali($variabiliGenerali) {
        $this->variabiliGenerali = $variabiliGenerali;
    }

    public function getVariabiliBase() {
        return $this->variabiliBase;
    }

    public function setVariabiliBase($variabiliBase) {
        $this->variabiliBase = $variabiliBase;
    }

    public function getVariabiliCampiAggiuntivi() {
        return $this->variabiliCampiAggiuntivi;
    }

    public function setVariabiliCampiAggiuntivi($variabiliCampiAggiuntivi) {
        $this->variabiliCampiAggiuntivi = $variabiliCampiAggiuntivi;
    }

    public function getVariabiliTipiAggiuntivi() {
        return $this->variabiliTipiAggiuntivi;
    }

    public function setVariabiliTipiAggiuntivi($variabiliTipiAggiuntivi) {
        $this->variabiliTipiAggiuntivi = $variabiliTipiAggiuntivi;
    }

    public function getVariabiliAmbiente() {
        return $this->variabiliAmbiente;
    }

    public function setVariabiliAmbiente($variabiliAmbiente) {
        $this->variabiliAmbiente = $variabiliAmbiente;
    }

    public function setVariabiliTemplateUpload($variabiliTemplateUpload) {
        $this->variabiliTemplateUpload = $variabiliTemplateUpload;
    }

    public function getVariabiliSoggetti() {
        return $this->variabiliSoggetti;
    }

    public function setVariabiliSoggetti($variabiliSoggetti) {
        $this->variabiliSoggetti = $variabiliSoggetti;
    }

    public function getDati() {
        return $this->dati;
    }

    public function setDati($dati) {
        $this->dati = $dati;
    }

    /**
     * Assegna codice procedimento per il calcolo variabili
     * @param string $codiceProcedimento
     */
    public function setDatiRichiesta($dati) {
        $this->dati = $dati;
    }

    public function setDestinazione($destinazione) {
        $this->destinazione = $destinazione;
    }

    public function setClassificazione($classificazione) {
        $this->classificazione = $classificazione;
    }

    public function setNomeOrig($nomeOrig) {
        $this->nomeOrig = $nomeOrig;
    }

    public function loadVariabiliRichiesta() {
        $this->loadVariabiliGenerali();
        $this->loadVariabiliBaseRichiesta();
        $this->loadVariabiliCampiAggiuntiviRichiesta();
        $this->loadVariabiliTipiAggiuntiviRichiesta();
        $this->loadVariabiliTariffe();
        $this->loadVariabiliRichiesteAccorpate();
        $this->loadVariabiliAmbiente();
        $this->loadVariabiliSoggetti();
    }

    public function loadVariabiliGenerali() {
        $i = 1;
        $dizionario = new itaDictionary();
        $dizionario->addField('GIORNOSETTIMANA', '', $i++, 'base', "");
        $dizionario->addField('DATAODIERNA', '', $i++, 'base', date("d/m/Y"));
        $dizionario->addField('TIMESTAMP', '', $i++, 'base', date("Y-m-d") . " " . date("H:i:s"));
        $this->variabiliGenerali = $dizionario;
    }

    public function loadVariabiliDistinta($PassoDistinta) {
        $j = 0;
        $praLib = new praLib();
        $dizionario = new itaDictionary();
        foreach ($this->dati['Navigatore']['Ricite_tab_new'] as $passo) {
            if (
                $passo['ITEIDR'] == 0 && ($passo['ITEUPL'] == 1 || $passo['ITEMLT'] == 1) &&
                (
                ($PassoDistinta['ITECTP'] == '' && $passo['ITESEQ'] < $PassoDistinta['ITESEQ']) ||
                ($PassoDistinta['ITECTP'] != '' && $PassoDistinta['ITECTP'] == $passo['ITEKEY']) ||
                ($PassoDistinta == "")
                )
            ) {
                $Ricdoc_tab = $praLib->GetRicdoc($passo['ITEKEY'], "itekey", $this->PRAM_DB, true, $passo['RICNUM']);
                if ($Ricdoc_tab) {
                    foreach ($Ricdoc_tab as $Ricdoc_rec) {
                        //Se c'è il pdf sbustato faccio vedere solo il p7m
                        if ($Ricdoc_rec['DOCFLSERVIZIO'] == 1) {
                            continue;
                        }
                        $ricite_rec = $praLib->GetRicite($Ricdoc_rec['ITEKEY'], "itekey", $this->PRAM_DB, false, $Ricdoc_rec['DOCNUM']);
                        $j++;
                        $destinazioni = "";
                        $Metadati = unserialize($Ricdoc_rec['DOCMETA']);
                        if (isset($Metadati['DESTINAZIONE'])) {
                            foreach ($Metadati['DESTINAZIONE'] as $dest) {
                                $Anaddo_rec = $praLib->GetAnaddo($dest, "codice", false, $this->PRAM_DB);
                                $destinazioni .= $Anaddo_rec['DDONOM'] . "<br></br>";
                            }
                        }
                        $Anaddo_rec = $praLib->GetAnaddo($Metadati['DESTINAZIONE'], "codice", false, $this->PRAM_DB);
                        $Anacla_rec = $praLib->GetAnacla($Metadati['CLASSIFICAZIONE'], "codice", false, $this->PRAM_DB);
                        $dizionario->addFieldData('CLASSIFICAZIONE_' . str_pad($j, 2, "0", STR_PAD_LEFT), $Anacla_rec['CLADES']);
                        $dizionario->addFieldData('DESTINAZIONE_' . str_pad($j, 2, "0", STR_PAD_LEFT), $destinazioni);
                        $dizionario->addFieldData('NOTE_' . str_pad($j, 2, "0", STR_PAD_LEFT), $Metadati['NOTE']);
                        $dizionario->addFieldData('DOCNAME_' . str_pad($j, 2, "0", STR_PAD_LEFT), $Ricdoc_rec['DOCNAME']);
                        $dizionario->addFieldData('DESCPASSO_' . str_pad($j, 2, "0", STR_PAD_LEFT), $ricite_rec['ITEDES']);
                        $dizionario->addFieldData('TIPOPASSO_' . str_pad($j, 2, "0", STR_PAD_LEFT), $ricite_rec['ITECLT']);
                        //$dizionario->addFieldData('DESCPASSO', $Ricdoc_rec['DOCNAME']);
                    }
                }
            }
        }
        $this->variabiliDistinta = $dizionario;
    }

    public function loadVariabiliTemplateNomeUpload() {
        $i = 1;
        $praLib = new praLib();
        $Anaddo_rec = $praLib->GetAnaddo($this->destinazione, "codice", false, $this->PRAM_DB);
        $Anacla_rec = $praLib->GetAnacla($this->classificazione, "codice", false, $this->PRAM_DB);
        $dizionario = new itaDictionary();
        $codiceDest = $Anaddo_rec['DDOCDE'];
        if (!$codiceDest)
            $codiceDest = "_";
        $tipoCla = $Anacla_rec['CLATIP'];
        if (!$tipoCla)
            $tipoCla = "_";
        $dizionario->addField('CODICEDESTINAZIONE', '', $i++, 'base', $codiceDest);
        $dizionario->addField('TIPOALLEGATO', '', $i++, 'base', $tipoCla);
        $dizionario->addField('CODICECLASSIFICAZIONE', '', $i++, 'base', $Anacla_rec['CLACOD']);
        $dizionario->addField('NUMERORICHIESTA', '', $i++, 'base', $this->dati['Proric_rec']['RICNUM']);
        $dizionario->addField('NUMERORICHIESTAPADRE', '', $i++, 'base', $this->dati['Proric_rec']['RICRPA']);
        $dizionario->addField('NOMEFILEORIG', '', $i++, 'base', $this->nomeOrig);
        $dizionario->addField('NUMERORICHIESTAACCORPATA', '', $i++, 'base', $this->dati['Proric_rec']['RICRUN']);
        $this->variabiliUpload = $dizionario;
    }

    public function loadVariabiliEsibente() {
        $i = 1;
        $dizionario = new itaDictionary();
        foreach ($this->dati['Ricdag_tab_totali'] as $ricdag_rec) {
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_NOME") {
                $nome = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_COGNOME") {
                $cognome = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_PEC") {
                $pec = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_CODICEFISCALE_CFI") {
                $fiscale = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_RESIDENZAVIA") {
                $via = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_RESIDENZACIVICO") {
                $civico = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_RESIDENZACOMUNE") {
                $comune = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_RESIDENZACAP_CAP") {
                $cap = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_RESIDENZAPROVINCIA_PV") {
                $provincia = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_TELEFONO") {
                $telefono = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_PROVISCRIZIONE") {
                $sedeIsc = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_NUMISCRIZIONE") {
                $numIsc = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_CITY_PROGSOGG") {
                $progSog = $ricdag_rec['RICDAT'];
            }
            if ($ricdag_rec['DAGKEY'] == "ESIBENTE_RESIDENTE") {
                $residente = $ricdag_rec['RICDAT'];
            }
        }
        $dizionario->addField('COGNOME', '', $i++, 'base', $cognome);
        $dizionario->addField('NOME', '', $i++, 'base', $nome);
        $dizionario->addField('PEC', '', $i++, 'base', $pec);
        $dizionario->addField('CODICEFISCALE', ' Fiscale', $i++, 'base', $fiscale);
        $dizionario->addField('RESIDENZAVIA', '', $i++, 'base', $via);
        $dizionario->addField('RESIDENZACIVICO', '', $i++, 'base', $civico);
        $dizionario->addField('RESIDENZACOMUNE', '', $i++, 'base', $comune);
        $dizionario->addField('RESIDENZACAP', '', $i++, 'base', $cap);
        $dizionario->addField('RESIDENZAPROVINCIA', '', $i++, 'base', $provincia);
        $dizionario->addField('TELEFONO', '', $i++, 'base', $telefono);
        $dizionario->addField('SEDEISCRIZIONE', '', $i++, 'base', $sedeIsc);
        $dizionario->addField('NUMEROISCRIZIONE', '', $i++, 'base', $numIsc);
        $dizionario->addField('CITY_PROGSOGG', '', $i++, 'base', $progSog);
        $dizionario->addField('RESIDENTE', '', $i++, 'base', $residente);
        $this->variabiliEsibente = $dizionario;
    }

    public function loadVariabiliBaseRichiesta() {
        $i = 1;
        $dizionario = new itaDictionary();
        $dizionarioAggiuntivi = new itaDictionary();
        if ($this->dati['Anaspa_rec']) {
            $descrizione = $this->dati['Anaspa_rec']['SPADES'];
            $denominazione = $this->dati['Anaspa_rec']['SPADES'];
            $comune = $this->dati['Anaspa_rec']['SPACOM'];
            $indirizzo = $this->dati['Anaspa_rec']['SPAIND'];
            $civico = $this->dati['Anaspa_rec']['SPANCI'];
            $provincia = $this->dati['Anaspa_rec']['SPAPRO'];
            $cap = $this->dati['Anaspa_rec']['SPACAP'];
            $mail = $this->dati['Anaspa_rec']['SPAPEC'];
            $codiceCatasto = $this->dati['Anaspa_rec']['SPACCA'];
            $codiceIstat = $this->dati['Anaspa_rec']['SPAIST'];
            $Tesoreria = $this->dati['Anaspa_rec']['SPATES'];
            $dest = $this->dati['Anaspa_rec']['SPADEST'];
            $iban = $this->dati['Anaspa_rec']['SPAIBAN'];
            $banca = $this->dati['Anaspa_rec']['SPABANCA'];
            $swift = $this->dati['Anaspa_rec']['SPASWIFT'];
            $cc = $this->dati['Anaspa_rec']['SPACC'];
            $ccp = $this->dati['Anaspa_rec']['SPACCP'];
            $causalecc = $this->dati['Anaspa_rec']['SPACAUSALECC'];
            $causaleccp = $this->dati['Anaspa_rec']['SPACAUSALECCP'];
        } else {
            $descrizione = $this->dati['Anatsp_rec']['TSPDES'];
            $denominazione = $this->dati['Anatsp_rec']['TSPDEN'];
            $comune = $this->dati['Anatsp_rec']['TSPCOM'];
            $indirizzo = $this->dati['Anatsp_rec']['TSPIND'];
            $civico = $this->dati['Anatsp_rec']['TSPNCI'];
            $provincia = $this->dati['Anatsp_rec']['TSPPRO'];
            $cap = $this->dati['Anatsp_rec']['TSPCAP'];
            $mail = $this->dati['Anatsp_rec']['TSPPEC'];
            $codiceCatasto = $this->dati['Anatsp_rec']['TSPCCA'];
            $codiceIstat = $this->dati['Anatsp_rec']['TSPIST'];
            $Tesoreria = $this->dati['Anatsp_rec']['TSPTES'];
            $dest = $this->dati['Anatsp_rec']['TSPDEST'];
            $iban = $this->dati['Anatsp_rec']['TSPIBAN'];
            $banca = $this->dati['Anatsp_rec']['TSPBANCA'];
            $swift = $this->dati['Anatsp_rec']['TSPSWIFT'];
            $cc = $this->dati['Anatsp_rec']['TSPCC'];
            $ccp = $this->dati['Anatsp_rec']['TSPCCP'];
            $causalecc = $this->dati['Anatsp_rec']['TSPCAUSALECC'];
            $causaleccp = $this->dati['Anatsp_rec']['TSPCAUSALECCP'];
        }
        $dizionario->addField('SPT_DES', '', $i++, 'base', $descrizione);
        $dizionario->addField('SPT_DEN', '', $i++, 'base', $denominazione);
        $dizionario->addField('SPT_COM', '', $i++, 'base', $comune);
        $dizionario->addField('SPT_IND', '', $i++, 'base', $indirizzo);
        $dizionario->addField('SPT_CIV', '', $i++, 'base', $civico);
        $dizionario->addField('SPT_PRV', '', $i++, 'base', $provincia);
        $dizionario->addField('SPT_CAP', '', $i++, 'base', $cap);
        $dizionario->addField('SPT_PEC', '', $i++, 'base', $mail);
        $dizionario->addField('SPT_CCA', '', $i++, 'base', $codiceCatasto);
        $dizionario->addField('SPT_CIS', '', $i++, 'base', $codiceIstat);
        $dizionario->addField('SPT_TES', '', $i++, 'base', $Tesoreria);
        $dizionario->addField('SPT_DEST', '', $i++, 'base', $dest);
        $dizionario->addField('SPT_IBAN', '', $i++, 'base', $iban);
        $dizionario->addField('SPT_BANCA', '', $i++, 'base', $banca);
        $dizionario->addField('SPT_SWIFT', '', $i++, 'base', $swift);
        $dizionario->addField('SPT_CC', '', $i++, 'base', $cc);
        $dizionario->addField('SPT_CCP', '', $i++, 'base', $ccp);
        $dizionario->addField('SPT_CAUSALECC', '', $i++, 'base', $causalecc);
        $dizionario->addField('SPT_CAUSALECCP', '', $i++, 'base', $causaleccp);
        //

        $dizionario->addField('TSPDES', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPDES']);
        $dizionario->addField('TSPDEN', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPDEN']);
        $dizionario->addField('TSPCOM', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPCOM']);
        $dizionario->addField('TSPIND', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPIND']);
        $dizionario->addField('TSPNCI', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPNCI']);
        $dizionario->addField('TSPPRO', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPPRO']);
        $dizionario->addField('TSPCAP', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPCAP']);
        $dizionario->addField('TSPPEC', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPPEC']);
        $dizionario->addField('TSPWEB', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPWEB']);
        $dizionario->addField('TSPMOD', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPMOD']);
        $dizionario->addField('TSPCCA', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPCCA']);
        $dizionario->addField('TSPIST', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPIST']);
        $dizionario->addField('TSPTES', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPTES']);
        $dizionario->addField('TSPDEST', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPDEST']);
        $dizionario->addField('TSPIBAN', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPIBAN']);
        $dizionario->addField('TSPBANCA', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPBANCA']);
        $dizionario->addField('TSPSWIFT', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPSWIFT']);
        $dizionario->addField('TSPCC', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPCC']);
        $dizionario->addField('TSPCCP', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPCCP']);
        $dizionario->addField('TSPCAUSALECC', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPCAUSALECC']);
        $dizionario->addField('TSPCAUSALECCP', '', $i++, 'base', $this->dati['Anatsp_rec']['TSPCAUSALECCP']);

        $dizionario->addField('TSPRES_NOM', '', $i++, 'base', $this->dati['Ananom_tspres_rec']['NOMCOG'] . " " . $this->dati['Ananom_tspres_rec']['NOMNOM']);
        $dizionario->addField('TSPRES_ORARIO', '', $i++, 'base', $this->dati['Ananom_tspres_rec']['NOMANN']);
        $dizionario->addField('TSPRES_NOMEML', '', $i++, 'base', $this->dati['Ananom_tspres_rec']['NOMEML']);
        $dizionario->addField('TSPRES_NOMTEL', '', $i++, 'base', $this->dati['Ananom_tspres_rec']['NOMTEL']);
        $dizionario->addField('TSPRES_NOMFAX', '', $i++, 'base', $this->dati['Ananom_tspres_rec']['NOMFAX']);

        $dizionario->addField('SPADES', '', $i++, 'base', $this->dati['Anaspa_rec']['SPADES']);
        $dizionario->addField('SPACOM', '', $i++, 'base', $this->dati['Anaspa_rec']['SPACOM']);
        $dizionario->addField('SPAIND', '', $i++, 'base', $this->dati['Anaspa_rec']['SPAIND']);
        $dizionario->addField('SPANCI', '', $i++, 'base', $this->dati['Anaspa_rec']['SPANCI']);
        $dizionario->addField('SPAPRO', '', $i++, 'base', $this->dati['Anaspa_rec']['SPAPRO']);
        $dizionario->addField('SPACAP', '', $i++, 'base', $this->dati['Anaspa_rec']['SPACAP']);
        $dizionario->addField('SPACCA', '', $i++, 'base', $this->dati['Anaspa_rec']['SPACCA']);
        $dizionario->addField('SPAIST', '', $i++, 'base', $this->dati['Anaspa_rec']['SPAIST']);
        $dizionario->addField('SPATES', '', $i++, 'base', $this->dati['Anaspa_rec']['SPATES']);
        $dizionario->addField('SPADEST', '', $i++, 'base', $this->dati['Anaspa_rec']['SPADEST']);
        $dizionario->addField('SPAIBAN', '', $i++, 'base', $this->dati['Anaspa_rec']['SPAIBAN']);
        $dizionario->addField('SPABANCA', '', $i++, 'base', $this->dati['Anaspa_rec']['SPABANCA']);
        $dizionario->addField('SPASWIFT', '', $i++, 'base', $this->dati['Anaspa_rec']['SPASWIFT']);
        $dizionario->addField('SPACC', '', $i++, 'base', $this->dati['Anaspa_rec']['SPACC']);
        $dizionario->addField('SPACCP', '', $i++, 'base', $this->dati['Anaspa_rec']['SPACCP']);
        $dizionario->addField('SPACAUSALECC', '', $i++, 'base', $this->dati['Anaspa_rec']['SPACAUSALECC']);
        $dizionario->addField('SPACAUSALECCP', '', $i++, 'base', $this->dati['Anaspa_rec']['SPACAUSALECCP']);
        $dizionario->addField('SPADISABILITAPAGOPA', '', $i++, 'base', $this->dati['Anaspa_rec']['SPADISABILITAPAGOPA']);

        $dizionario->addField('SPARES_NOM', '', $i++, 'base', $this->dati['Ananom_spares_rec']['NOMCOG'] . " " . $this->dati['Ananom_spares_rec']['NOMNOM']);
        $dizionario->addField('SPARES_ORARIO', '', $i++, 'base', $this->dati['Ananom_spares_rec']['NOMANN']);
        $dizionario->addField('SPARES_NOMEML', '', $i++, 'base', $this->dati['Ananom_spares_rec']['NOMEML']);
        $dizionario->addField('SPARES_NOMTEL', '', $i++, 'base', $this->dati['Ananom_spares_rec']['NOMTEL']);
        $dizionario->addField('SPARES_NOMFAX', '', $i++, 'base', $this->dati['Ananom_spares_rec']['NOMFAX']);

//        $dizionario->addField('PRADES', 'Descrizione Procedimento', $i++, 'base', $this->dati['Anapra_rec']['PRADES__1'] . $this->dati['Anapra_rec']['PRADES__2'] . $this->dati['Anapra_rec']['PRADES__3'] . $this->dati['Anapra_rec']['PRADES__4']);
        $dizionario->addField('PRADES', 'Descrizione Procedimento', $i++, 'base', $this->dati['Oggetto']);
        $dizionario->addField('EVTDES', 'Descrizione Evento', $i++, 'base', $this->dati['DescrizioneEvento']);
        $dizionario->addField('TIPDES', 'Tipologia Procedimento', $i++, 'base', '');
        $dizionario->addField('SETDES', 'Settore', $i++, 'base', '');
        $dizionario->addField('ATTDES', 'Attività', $i++, 'base', '');

        $dizionario->addField('PRARES_NOM', '', $i++, 'base', $this->dati['Ananom_prares_rec']['NOMCOG'] . " " . $this->dati['Ananom_spares_rec']['NOMNOM']);
        $dizionario->addField('PRARES_ORARIO', '', $i++, 'base', $this->dati['Ananom_prares_rec']['NOMANN']);
        $dizionario->addField('PRARES_NOMEML', '', $i++, 'base', $this->dati['Ananom_prares_rec']['NOMEML']);
        $dizionario->addField('PRARES_NOMTEL', '', $i++, 'base', $this->dati['Ananom_prares_rec']['NOMTEL']);
        $dizionario->addField('PRARES_NOMFAX', '', $i++, 'base', $this->dati['Ananom_prares_rec']['NOMFAX']);

        $dizionario->addField('RICSOG', '', $i++, 'base', $this->dati['Proric_rec']['RICCOG'] . " " . $this->dati['Proric_rec']['RICNOM']);
        $dizionario->addField('RICCOG', '', $i++, 'base', $this->dati['Proric_rec']['RICCOG']);
        $dizionario->addField('RICNOM', '', $i++, 'base', $this->dati['Proric_rec']['RICNOM']);
        $dizionario->addField('RICVIA', '', $i++, 'base', $this->dati['Proric_rec']['RICVIA']);
        $dizionario->addField('RICCAP', '', $i++, 'base', $this->dati['Proric_rec']['RICCAP']);
        $dizionario->addField('RICCOM', '', $i++, 'base', $this->dati['Proric_rec']['RICCOM']);
        $dizionario->addField('RICPRV', '', $i++, 'base', $this->dati['Proric_rec']['RICPRV']);
        $dizionario->addField('RICNAZ', '', $i++, 'base', $this->dati['Proric_rec']['RICNAZ']);
        $dizionario->addField('RICNAS', '', $i++, 'base', $this->dati['Proric_rec']['RICNAS']);
        $dizionario->addField('RICFIS', '', $i++, 'base', $this->dati['Proric_rec']['RICFIS']);
        $dizionario->addField('RICEMA', '', $i++, 'base', $this->dati['Proric_rec']['RICEMA']);
        $data_compilazione = substr($this->dati['Proric_rec']['RICDRE'], 6, 2) . "/" . substr($this->dati['Proric_rec']['RICDRE'], 4, 2) . "/" . substr($this->dati['Proric_rec']['RICDRE'], 0, 4);
        $dizionario->addField('RICDRE', '', $i++, 'base', $data_compilazione);
        $dizionario->addField('RICORE', '', $i++, 'base', $this->dati['Proric_rec']['RICORE']);
        $data_inoltro = substr($this->dati['Proric_rec']['RICDAT'], 6, 2) . "/" . substr($this->dati['Proric_rec']['RICDAT'], 4, 2) . "/" . substr($this->dati['Proric_rec']['RICDAT'], 0, 4);
        $dizionario->addField('RICDAT', '', $i++, 'base', $data_inoltro);
        $dizionario->addField('RICTIM', '', $i++, 'base', $this->dati['Proric_rec']['RICTIM']);

        $dizionario->addField('RICNUM', '', $i++, 'base', $this->dati['Proric_rec']['RICNUM']);
        $dizionario->addField('RICNUM_FORMATTED', '', $i++, 'base', substr($this->dati['Proric_rec']['RICNUM'], 4, 6) . "/" . substr($this->dati['Proric_rec']['RICNUM'], 0, 4));
        $data_protocollo = substr($this->dati['Proric_rec']['RICDPR'], 6, 2) . "/" . substr($this->dati['Proric_rec']['RICDPR'], 4, 2) . "/" . substr($this->dati['Proric_rec']['RICDPR'], 0, 4);
        $dizionario->addField('RICDPR', '', $i++, 'base', $data_protocollo);
        $dizionario->addField('RICRPA', '', $i++, 'base', $this->dati['Proric_rec']['RICRPA']);
        $dizionario->addField('RICRPA_FORMATTED', '', $i++, 'base', substr($this->dati['Proric_rec']['RICRPA'], 4, 6) . "/" . substr($this->dati['Proric_rec']['RICRPA'], 0, 4));
        $dizionario->addField('RICRUN', '', $i++, 'base', $this->dati['Proric_rec']['RICRUN']);
        $dizionario->addField('RICRUN_FORMATTED', '', $i++, 'base', substr($this->dati['Proric_rec']['RICRUN'], 4, 6) . "/" . substr($this->dati['Proric_rec']['RICRUN'], 0, 4));
        $dizionario->addField('RICNPR_NUM', '', $i++, 'base', substr($this->dati['Proric_rec']['RICNPR'], 4));
        $dizionario->addField('RICNPR_ANNONUM', '', $i++, 'base', substr($this->dati['Proric_rec']['RICNPR'], 4) . "/" . substr($this->dati['Proric_rec']['RICNPR'], 0, 4));

        $dizionario->addField('RICEVE', '', $i++, 'base', $this->dati['Proric_rec']['RICEVE']);

        $dizionario->addField('PRANUM', '', $i++, 'base', $this->dati['Codice']);

        $RICRPA_PROCEDIMENTO = '';
        if ($this->dati['Proric_rec']['RICRPA']) {
            $sql = "SELECT RICPRO FROM PRORIC WHERE RICNUM = '{$this->dati['Proric_rec']['RICRPA']}'";
            $proric_rpa_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($proric_rpa_rec) {
                $RICRPA_PROCEDIMENTO = $proric_rpa_rec['RICPRO'];
            }
        }

        $dizionario->addField('RICRPA_PROCEDIMENTO', '', $i++, 'base', $RICRPA_PROCEDIMENTO);

        /*
         * Nuovo dati aggiuntivi di richiesta 
         * 
         */
        $sql = "
            SELECT
                RICDAG.ITEKEY AS ITEKEY,
                RICDAG.ITECOD AS ITECOD,
                RICDAG.DAGDES AS DAGDES,                
                RICDAG.DAGKEY AS DAGKEY,
                RICDAG.DAGALIAS AS DAGALIAS,
                RICDAG.DAGTIP AS DAGTIP,        
                RICDAG.RICDAT AS RICDAT,
                RICDAG.DAGSET AS DAGSET,
                (SELECT COUNT(R2.ROWID) FROM RICDAG R2 WHERE R2.DAGNUM=RICDAG.DAGNUM AND R2.DAGKEY=RICDAG.DAGKEY ) AS QUANTI_SET
            FROM
                RICDAG RICDAG
            WHERE RICDAG.DAGTIP = ''  AND RICDAG.DAGNUM='" . $this->dati['Proric_rec']['RICNUM'] . "'  AND RICDAG.ITECOD=RICDAG.ITEKEY"; // AND ITESEQ < '" . $currSeq . "' ";

        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $i = 0;
        if ($Ricdag_tab) {
            foreach ($Ricdag_tab as $chiave => $Ricdag_rec) {
                $chiaveCampo = $Ricdag_rec['DAGKEY'];
                $descrizioneCampo = $Ricdag_rec['DAGDES'] ? $Ricdag_rec['DAGDES'] : $Ricdag_rec['DAGKEY'];
                if ($Ricdag_rec['QUANTI_SET'] > 1) {
                    $suffix = end(explode("_", $Ricdag_rec['DAGSET']));
                    if (!$suffix) {
                        continue;
                    }
                    $suffix = ($suffix) ? "_" . $suffix : "";
                    $chiaveCampo = $chiaveCampo . $suffix;
                }
                $dizionarioAggiuntivi->addField($chiaveCampo, $descrizioneCampo, $i++, 'base', $Ricdag_rec['RICDAT']);
            }
        }
        /*
         * Fine Nuovo
         * 
         */


        $this->variabiliBase = $dizionario;
        $this->variabiliBaseAggiuntivi = $dizionarioAggiuntivi;
    }

    public function addVariabiliCampiAggiuntiviRichiesta($seq) {
        if (!is_a($this->variabiliCampiAggiuntivi, 'itaDictionary')) {
            $this->variabiliCampiAggiuntivi = new itaDictionary();
        }

        $dizionariTipologie = array();

        if (!is_a($this->variabiliTipologiaPassi, 'itaDictionary')) {
            $this->variabiliTipologiaPassi = new itaDictionary();
        } else {
            foreach ($this->variabiliTipologiaPassi->getData() as $key => $data) {
                $dizionariTipologie[$key] = $data;
            }
        }

        $currSeq = $seq;
        $sql = "
            SELECT
                RICDAG.ITEKEY AS ITEKEY,
                RICDAG.ITECOD AS ITECOD,
                RICDAG.DAGDES AS DAGDES,                
                RICDAG.DAGKEY AS DAGKEY,
                RICDAG.DAGALIAS AS DAGALIAS,
                RICDAG.DAGTIP AS DAGTIP,        
                RICDAG.RICDAT AS RICDAT,
                RICDAG.DAGSET AS DAGSET,
                RICITE.ITERDM AS ITERDM,
                RICITE.ITEMLT AS ITEMLT,
                RICITE.ITECLT AS ITECLT
            FROM
                RICITE RICITE
            LEFT OUTER JOIN 
                RICDAG RICDAG
            ON 
                RICITE.RICNUM=RICDAG.DAGNUM AND RICITE.ITEKEY=RICDAG.ITEKEY
                WHERE RICITE.RICNUM='" . $this->dati['Proric_rec']['RICNUM'] . "' AND ITESEQ = '" . $currSeq . "' ";
        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $i = 0;
        if ($Ricdag_tab) {
            $praLib = new praLib();
            $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
            foreach ($Ricdag_tab as $chiave => $Ricdag_rec) {
                if (!isset($dizionariTipologie[$Ricdag_rec['ITECLT']])) {
                    $dizionariTipologie[$Ricdag_rec['ITECLT']] = new itaDictionary();
                }

                $descrizioneCampo = $Ricdag_rec['DAGDES'] ? $Ricdag_rec['DAGDES'] : $Ricdag_rec['DAGKEY'];
                $chiaveCampo = $Ricdag_rec['DAGKEY'];
                if ($Ricdag_rec['ITERDM'] == 1 || $Ricdag_rec['ITEMLT'] == 1) {
                    $suffix = end(explode("_", $Ricdag_rec['DAGSET']));
                    $suffix = ($suffix) ? "_" . $suffix : "";
                    $chiaveCampo = $chiaveCampo . $suffix;
                }
                $this->variabiliCampiAggiuntivi->addField($chiaveCampo, $descrizioneCampo, $i++, 'base', $Ricdag_rec['RICDAT']);
                $dizionariTipologie[$Ricdag_rec['ITECLT']]->addField($chiaveCampo, $descrizioneCampo, $i++, 'base', $Ricdag_rec['RICDAT']);
            }
        }

        try {
            $j = 0;
            foreach ($dizionariTipologie as $codiceTipologia => $dictionaryTipologia) {
                $praclt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT CLTDES, CLTDIZIONARIO FROM PRACLT WHERE CLTCOD = '$codiceTipologia'", false);
                if ($praclt_rec['CLTDIZIONARIO'] == '1') {
                    $this->variabiliTipologiaPassi->addField($codiceTipologia, $praclt_rec['CLTDES'], $j++, 'itaDictionary', $dictionaryTipologia);
                }
            }
        } catch (Exception $e) {
            /*
             * Campo CLTDIZIONARIO non presente
             */
        }
    }

    public function loadVariabiliCampiAggiuntiviRichiesta() {
        $variabiliCampiAggiuntivi = new itaDictionary();
        $variabiliTipologiaPassi = new itaDictionary();
        $dizionariTipologie = array();

        $sql = "
            SELECT
                RICDAG.ITEKEY AS ITEKEY,
                RICDAG.ITECOD AS ITECOD,
                RICDAG.DAGDES AS DAGDES,                
                RICDAG.DAGKEY AS DAGKEY,
                RICDAG.DAGALIAS AS DAGALIAS,
                RICDAG.DAGTIP AS DAGTIP,        
                RICDAG.RICDAT AS RICDAT,
                RICDAG.DAGSET AS DAGSET,
                RICITE.ITERDM AS ITERDM,
                RICITE.ITEMLT AS ITEMLT,
                RICITE.ITECLT AS ITECLT
            FROM
                RICITE RICITE
            LEFT OUTER JOIN 
                RICDAG RICDAG
            ON 
                RICITE.RICNUM=RICDAG.DAGNUM AND RICITE.ITEKEY=RICDAG.ITEKEY
                WHERE RICITE.RICNUM='" . $this->dati['Proric_rec']['RICNUM'] . "'";
        //WHERE DAGTIP = ''  AND RICITE.RICNUM='" . $this->dati['Proric_rec']['RICNUM'] . "' ";

        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        $i = 0;
        if ($Ricdag_tab) {
            foreach ($Ricdag_tab as $chiave => $Ricdag_rec) {
                if (!isset($dizionariTipologie[$Ricdag_rec['ITECLT']])) {
                    $dizionariTipologie[$Ricdag_rec['ITECLT']] = new itaDictionary();
                }

                $chiaveCampo = $Ricdag_rec['DAGKEY'];
                if ($Ricdag_rec['ITERDM'] || $Ricdag_rec['ITEMLT']) {
                    $suffix = end(explode("_", $Ricdag_rec['DAGSET']));
                    if (!$suffix) {
                        continue;
                    }
                    $suffix = ($suffix) ? "_" . $suffix : "";
                    $chiaveCampo = $chiaveCampo . $suffix;
                }
                $descrizioneCampo = $Ricdag_rec['DAGDES'] ? $Ricdag_rec['DAGDES'] : $Ricdag_rec['DAGKEY'];
                $variabiliCampiAggiuntivi->addField($chiaveCampo, $descrizioneCampo, $i++, 'base', $Ricdag_rec['RICDAT']);
                $dizionariTipologie[$Ricdag_rec['ITECLT']]->addField($chiaveCampo, $descrizioneCampo, $i++, 'base', $Ricdag_rec['RICDAT']);
            }
            $this->variabiliCampiAggiuntivi = $variabiliCampiAggiuntivi;
        } else {
            $this->variabiliCampiAggiuntivi = null;
        }

        try {
            $j = 0;
            foreach ($dizionariTipologie as $codiceTipologia => $dictionaryTipologia) {
                $praclt_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT CLTDES, CLTDIZIONARIO FROM PRACLT WHERE CLTCOD = '$codiceTipologia'", false);
                if ($praclt_rec['CLTDIZIONARIO'] == '1') {
                    $variabiliTipologiaPassi->addField($codiceTipologia, $praclt_rec['CLTDES'], $j++, 'itaDictionary', $dictionaryTipologia);
                }
            }

            $this->variabiliTipologiaPassi = $variabiliTipologiaPassi;
        } catch (Exception $e) {
            /*
             * Campo CLTDIZIONARIO non presente
             */

            $this->variabiliTipologiaPassi = null;
        }
    }

    public function addVariabiliTipiAggiuntiviRichiesta($seq) {
        if (!is_a($this->variabiliTipiAggiuntivi, 'itaDictionary')) {
            $this->variabiliTipiAggiuntivi = new itaDictionary();
        }
        $currSeq = $seq;
        $sql = "
            SELECT
                RICDAG.ITEKEY AS ITEKEY,
                RICDAG.ITECOD AS ITECOD,
                RICDAG.DAGKEY AS DAGKEY,
                RICDAG.DAGDES AS DAGDES,                
                RICDAG.DAGTIP AS DAGTIP,
                RICDAG.RICDAT AS RICDAT
            FROM
                (
                    SELECT * FROM RICITE WHERE RICNUM='{$this->dati['Proric_rec']['RICNUM']}'
                ) RICITE
            LEFT OUTER JOIN 
                RICDAG RICDAG
            ON 
                RICITE.RICNUM=RICDAG.DAGNUM AND RICITE.ITEKEY=RICDAG.ITEKEY            
            WHERE 
                DAGTIP<>'' ORDER BY DAGSEQ";


        /*
         * La condizione nella select di RICITE "AND ITESEQ < '$currSeq'" è stata tolta  01/04/2016
         */


//        $sql = "
//            SELECT
//                RICDAG.ITEKEY AS ITEKEY,
//                RICDAG.ITECOD AS ITECOD,
//                RICDAG.DAGKEY AS DAGKEY,
//                RICDAG.DAGDES AS DAGDES,                
//                RICDAG.DAGTIP AS DAGTIP,
//                RICDAG.RICDAT AS RICDAT
//            FROM
//                RICITE RICITE
//            LEFT OUTER JOIN 
//                RICDAG RICDAG
//            ON 
//                RICITE.RICNUM=RICDAG.DAGNUM AND RICITE.ITEKEY=RICDAG.ITEKEY            
//            WHERE 
//                DAGTIP<>'' AND RICITE.RICNUM='" . $this->dati['Proric_rec']['RICNUM'] . "' ORDER BY DAGSEQ AND ITESEQ < '" . $currSeq . "'";

        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Ricdag_tab) {
            $i = 0;
            foreach ($Ricdag_tab as $Ricdag_rec) {
                if ($Ricdag_rec['DAGTIP'] == 'Sportello_Aggregato' && $Ricdag_rec['RICDAT']) {
                    $Anaspa_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANASPA WHERE SPACOD=" . $Ricdag_rec['RICDAT'], false);
                    if ($Anaspa_rec) {
                        $Ricdag_rec['RICDAT'] = $Anaspa_rec['SPADES'];
                    }
                }
                //if ($Ricdag_rec['DAGTIP'] == 'Denom_Fiera' && $Ricdag_rec['RICDAT']) {
                if (($Ricdag_rec['DAGTIP'] == 'Denom_Fiera' || $Ricdag_rec['DAGTIP'] == 'Denom_FieraBando' || $Ricdag_rec['DAGTIP'] == 'Denom_FieraPBando') && $Ricdag_rec['RICDAT']) {
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    $Ricdag_rec['RICDAT'] = $praLibGfm->GetDescDenomFiere($Ricdag_rec['RICDAT'], $this->GAFIERE_DB);
                }
                if (($Ricdag_rec['DAGTIP'] == 'Denom_MercatoBando' || $Ricdag_rec['DAGTIP'] == 'Denom_PIBando') && $Ricdag_rec['RICDAT']) {
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    $Ricdag_rec['RICDAT'] = $praLibGfm->GetDescDenomMercato($Ricdag_rec['RICDAT'], $this->GAFIERE_DB);
                }

                $this->variabiliTipiAggiuntivi->addField($Ricdag_rec['DAGTIP'], $Ricdag_rec['DAGDES'], $i++, 'base', $Ricdag_rec['RICDAT'], true);
            }
        }
    }

    public function loadVariabiliTipiAggiuntiviRichiesta() {
        if (!is_a($this->variabiliTipiAggiuntivi, 'itaDictionary')) {
            $this->variabiliTipiAggiuntivi = new itaDictionary();
        }

        $sql = "
            SELECT
                RICDAG.ITEKEY AS ITEKEY,
                RICDAG.ITECOD AS ITECOD,
                RICDAG.DAGKEY AS DAGKEY,
                RICDAG.DAGDES AS DAGDES,                
                RICDAG.DAGTIP AS DAGTIP,
                RICDAG.RICDAT AS RICDAT
            FROM
                (
		SELECT * FROM RICITE WHERE RICITE.RICNUM='" . $this->dati['Proric_rec']['RICNUM'] . "'
		) RICITE
            LEFT OUTER JOIN 
                RICDAG RICDAG
            ON 
                RICITE.RICNUM=RICDAG.DAGNUM AND RICITE.ITEKEY=RICDAG.ITEKEY            
            WHERE 
                DAGTIP<>'' ORDER BY DAGSEQ"; // AND ITESEQ < '" . $currSeq . "' 

        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);
        if ($Ricdag_tab) {
            $i = 0;
            foreach ($Ricdag_tab as $Ricdag_rec) {
                if ($Ricdag_rec['DAGTIP'] == 'Sportello_Aggregato' && $Ricdag_rec['RICDAT']) {
                    $Anaspa_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANASPA WHERE SPACOD=" . $Ricdag_rec['RICDAT'], false);
                    if ($Anaspa_rec) {
                        $Ricdag_rec['RICDAT'] = $Anaspa_rec['SPADES'];
                    }
                }
                if (($Ricdag_rec['DAGTIP'] == 'Denom_Fiera' || $Ricdag_rec['DAGTIP'] == 'Denom_FieraBando' || $Ricdag_rec['DAGTIP'] == 'Denom_FieraPBando') && $Ricdag_rec['RICDAT']) {
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    $Ricdag_rec['RICDAT'] = $praLibGfm->GetDescDenomFiere($Ricdag_rec['RICDAT'], $this->GAFIERE_DB);
                }
                if (($Ricdag_rec['DAGTIP'] == 'Denom_MercatoBando' || $Ricdag_rec['DAGTIP'] == 'Denom_PIBando') && $Ricdag_rec['RICDAT']) {
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    $Ricdag_rec['RICDAT'] = $praLibGfm->GetDescDenomMercato($Ricdag_rec['RICDAT'], $this->GAFIERE_DB);
                }


                $this->variabiliTipiAggiuntivi->addField($Ricdag_rec['DAGTIP'], $Ricdag_rec['DAGDES'], $i++, 'base', $Ricdag_rec['RICDAT'], true);
            }
        }
    }

    public function loadVariabiliTipiAggiuntiviBaseRichiesta() {
        if (!is_a($this->variabiliTipiAggiuntivi, 'itaDictionary')) {
            $this->variabiliTipiAggiuntivi = new itaDictionary();
        }

        $sql = "SELECT
                    RICDAG.ITEKEY AS ITEKEY,
                    RICDAG.ITECOD AS ITECOD,
                    RICDAG.DAGKEY AS DAGKEY,
                    RICDAG.DAGDES AS DAGDES,
                    RICDAG.DAGTIP AS DAGTIP,
                    RICDAG.RICDAT AS RICDAT
                FROM
                    RICDAG
                WHERE
                    RICDAG.DAGNUM = '" . $this->dati['Proric_rec']['RICNUM'] . "' AND
                    RICDAG.ITEKEY = RICDAG.ITECOD AND
                    DAGTIP <> ''
                ORDER BY DAGSEQ";

        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        $i = 0;
        foreach ($Ricdag_tab as $Ricdag_rec) {
            if ($Ricdag_rec['DAGTIP'] == 'Sportello_Aggregato' && $Ricdag_rec['RICDAT']) {
                $Anaspa_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANASPA WHERE SPACOD=" . $Ricdag_rec['RICDAT'], false);
                if ($Anaspa_rec) {
                    $Ricdag_rec['RICDAT'] = $Anaspa_rec['SPADES'];
                }
            }

            if (($Ricdag_rec['DAGTIP'] == 'Denom_Fiera' || $Ricdag_rec['DAGTIP'] == 'Denom_FieraBando' || $Ricdag_rec['DAGTIP'] == 'Denom_FieraPBando') && $Ricdag_rec['RICDAT']) {
                require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                $praLibGfm = new praLibGfm();
                $Ricdag_rec['RICDAT'] = $praLibGfm->GetDescDenomFiere($Ricdag_rec['RICDAT'], $this->GAFIERE_DB);
            }

            if (($Ricdag_rec['DAGTIP'] == 'Denom_MercatoBando' || $Ricdag_rec['DAGTIP'] == 'Denom_PIBando') && $Ricdag_rec['RICDAT']) {
                require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                $praLibGfm = new praLibGfm();
                $Ricdag_rec['RICDAT'] = $praLibGfm->GetDescDenomMercato($Ricdag_rec['RICDAT'], $this->GAFIERE_DB);
            }

            $this->variabiliTipiAggiuntivi->addField($Ricdag_rec['DAGTIP'], $Ricdag_rec['DAGDES'], $i++, 'base', $Ricdag_rec['RICDAT'], true);
        }
    }

    public function loadVariabiliTariffe() {
        $variabiliTariffe = new itaDictionary();
        $praLib = new praLib();

        $ricite_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICITE WHERE RICNUM = '" . $this->dati['Proric_rec']['RICNUM'] . "' AND ITEPAY = '1'", true);

        if ($ricite_tab) {
            $i = 0;

            foreach ($ricite_tab as $ricite_rec) {
                $variabiliTariffe->addField('TARIFFA_' . $ricite_rec['ITEKEY'], 'Tariffa Passo "' . $ricite_rec['ITEDES'] . '"', $i++, 'base', $ricite_rec['TARIFFA'], true);
            }

            $this->variabiliTariffe = $variabiliTariffe;
        } else {
            $this->variabiliTariffe = null;
        }
    }

    public function loadVariabiliRichiesteAccorpate() {
        $variabiliRichiesteAccorpate = new itaDictionary();
        $praLib = new praLib();
        $praLibEventi = new praLibEventi();
        $richiesteAccorpate = $praLib->GetRichiesteAccorpate($this->PRAM_DB, $this->dati['Proric_rec']['RICNUM']);

        if (!$richiesteAccorpate) {
            $this->variabiliRichiesteAccorpate = null;
            return;
        }

        $i = $k = 0;

        foreach ($richiesteAccorpate as $proric_rec) {
            $anaset_rec = $praLib->GetAnaset($proric_rec['RICSTT'], 'codice', $this->PRAM_DB);
            $anaatt_rec = $praLib->GetAnaatt($proric_rec['RICATT'], 'codice', $this->PRAM_DB);

            $numero = substr($proric_rec['RICNUM'], 4) . "/" . substr($proric_rec['RICNUM'], 0, 4);
            $descrizione = $praLibEventi->getOggettoProric($this->PRAM_DB, $proric_rec);
            $inizio = substr($proric_rec['RICDRE'], 6, 2) . "/" . substr($proric_rec['RICDRE'], 4, 2) . "/" . substr($proric_rec['RICDRE'], 0, 4) . "\n" . $proric_rec['RICORE'];

            $tipologia = $proric_rec['RICSTA'] != '99' ? "Inoltrata" : '';

            $numeroProtocollo = '';
            if ($proric_rec['RICNPR'] != 0) {
                $numeroProtocollo = substr($proric_rec['RICNPR'], 4) . '/' . substr($proric_rec['RICNPR'], 0, 4);
                $tipologia .= "\ncon Protocollo n. $numeroProtocollo";
            }

            $variabiliRichiesteAccorpate->addField('NUMERO_' . $k, 'Numero Pratica', $i++, 'base', $numero);
            $variabiliRichiesteAccorpate->addField('DESCRIZIONE_' . $k, 'Descrizione', $i++, 'base', $descrizione);
            $variabiliRichiesteAccorpate->addField('SETTORE_' . $k, 'Settore', $i++, 'base', $anaset_rec['SETDES']);
            $variabiliRichiesteAccorpate->addField('ATTIVITA_' . $k, 'Attività', $i++, 'base', $anaatt_rec['ATTDES']);
            $variabiliRichiesteAccorpate->addField('INIZIO_' . $k, 'Inizio', $i++, 'base', $inizio);
            $variabiliRichiesteAccorpate->addField('TIPOLOGIA_' . $k, 'Tipologia', $i++, 'base', $tipologia);

            $k++;
        }

        $this->variabiliRichiesteAccorpate = $variabiliRichiesteAccorpate;
    }

    public function loadVariabiliParent($datiParent) {
        $this->variabiliParent = $datiParent['Navigatore']['Dizionario_Richiesta_new'];
    }

    public function loadVariabiliAmbiente() {
        $variabiliAmbiente = new itaDictionary();
//        $praLib = new praLib();
//        require_once ITA_BASE_PATH . '/apps/Pratiche/praLibEnvVars.class.php';
//        $praLibEnvVars = new praLibEnvVars();

        $ambiente_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM VARIABILIAMBIENTE", true);

        if ($ambiente_tab) {
            $i = 0;

            foreach ($ambiente_tab as $ambiente_rec) {
                $chiave = $ambiente_rec['VARKEY'];
//                $variabiliAmbiente->addField($ambiente_rec['VARKEY'], $praLibEnvVars::$SISTEM_ENVIRONMENT_VARIABLES[$chiave]['ENVDES'], $i++, 'base', $ambiente_rec['VARVAL'], true);
                $variabiliAmbiente->addField($ambiente_rec['VARKEY'], "prova desc", $i++, 'base', $ambiente_rec['VARVAL'], true);
            }

            $this->variabiliAmbiente = $variabiliAmbiente;
        } else {
            $this->variabiliAmbiente = null;
        }
    }

    public function loadVariabiliSoggetti() {
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibSoggetti.class.php';

        $praLibSoggetti = new praLibSoggetti();
        $soggettiRichiesta = $praLibSoggetti->getSoggettiRichiesta($this->PRAM_DB, $this->dati['Proric_rec'], $this->dati['Navigatore']['Ricite_tab_new']);

        $this->variabiliSoggetti = new itaDictionary();

        $i = 0;
        foreach ($soggettiRichiesta as $RUOCOD => $soggetti) {
            $i++;
            $this->variabiliSoggetti->addField("SOGGETTO$RUOCOD", "Soggetto con ruolo $RUOCOD", $i, 'base', $soggetti);
        }

        return $this->variabiliSoggetti;
    }

    public function getVariabiliProcedimento() {
        $this->variabiliProcedimento = new itaDictionary();
        
        if (is_a($this->variabiliInformativa, 'itaDictionary')) {
            $this->variabiliProcedimento->addField('PRAINF', 'Variabili Informativa', 1, 'itaDictionary', $this->variabiliInformativa);
        }
        
        return $this->variabiliProcedimento;
    }

    public function loadVariabiliInformativa() {
        $variabiliInformativa = new itaDictionary();

        require_once ITA_SUAP_PATH . '/SUAP_praInf/praSchedaTemplate.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praElenco.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praInq.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praNor.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praReq.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praAdempi.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praTer.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praOneri.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praResp.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praDis.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praModu.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praAlle.class.php';
        require_once ITA_SUAP_PATH . '/SUAP_praInf/praProcCorr.class.php';

        $praElenco = new praElenco();
        $praInq = new praInq();
        $praNor = new praNor();
        $praReq = new praReq();
        $praAdempi = new praAdempi();
        $praTer = new praTer();
        $praOneri = new praOneri();
        $praResp = new praResp();
        $praDis = new praDis();
        $praModu = new praModu();
        $praAlle = new praAlle();
        $praProcCorr = new praProcCorr();

        $variabiliInformativa->addField('SERVIZIO', 'Servizio', $i++, 'base', 'Sportello delle Imprese', true);
        $variabiliInformativa->addField('ELENCO', 'Elenco richieste in corso', $i++, 'base', $praElenco->getHtml($this->dati), true);
        $variabiliInformativa->addField('INQUADRAMENTO', 'Inquadramento', $i++, 'base', $praInq->getHtml($this->dati), true);
        $variabiliInformativa->addField('NORMATIVA', 'Normativa', $i++, 'base', $praNor->getHtml($this->dati), true);
        $variabiliInformativa->addField('REQUISITI', 'Requisiti', $i++, 'base', $praReq->getHtml($this->dati), true);
        $variabiliInformativa->addField('ADEMPIMENTI', 'Adempimenti', $i++, 'base', $praAdempi->getHtml($this->dati), true);
        $variabiliInformativa->addField('TERMINI', 'Termini', $i++, 'base', $praTer->getHtml($this->dati), true);
        $variabiliInformativa->addField('ONERI', 'Oneri', $i++, 'base', $praOneri->getHtml($this->dati), true);
        $variabiliInformativa->addField('RESPONSABILE', 'Responsabile', $i++, 'base', $praResp->getHtml($this->dati), true);
        $variabiliInformativa->addField('UO_RESP_ISTRUTTORIA', 'Unità organizzativa responsabile dell\'istruttoria', $i++, 'base', "U.O. {$this->dati['Anauni_rec']['UNIDES']}", true);
        $variabiliInformativa->addField('DISCIPLINE', 'Discipline sanzionatorie', $i++, 'base', $praDis->getHtml($this->dati), true);
        $variabiliInformativa->addField('MODULISTICA', 'Modulistica', $i++, 'base', $praModu->getHtml($this->dati), true);
        $variabiliInformativa->addField('ALLEGATI', 'Allegati', $i++, 'base', $praAlle->getHtml($this->dati), true);
        $variabiliInformativa->addField('PROC_CORRELATI', 'Altri procedimenti correlati', $i++, 'base', $praProcCorr->getHtml($this->dati), true);

        $this->variabiliInformativa = $variabiliInformativa;
    }

}
