<?php

class praVariabili {

    private $dati;
    //--------------------
    private $variabiliRichiesta;
    private $variabiliBase;
    private $variabiliCampiAggiuntivi;
    private $variabiliTipiAggiuntivi;

    function __construct() {
        
    }

    /**
     * Assegna codice procedimento per il calcolo variabili
     * @param string $codiceProcedimento 
     */
    public function setDatiRichiesta($dati) {
        $this->dati = $dati;
    }

    public function getVariabiliRichiesta($PRAM_DB = "") {
        $dizBase = $this->getVariabiliBaseRichiesta($PRAM_DB);
        $dizAggi = $this->getVariabiliCampiAggiuntiviRichiesta($PRAM_DB);
        $dizTipi = $this->getVariabiliTipiAggiuntiviRichiesta($PRAM_DB);
        $dizionarioRichiesta = new itaDictionary();
        if ($dizBase) {
            $dizionarioRichiesta->addField('PRABASE', 'Variabili Base Procedimento', 1, 'itaDictionary', $dizBase);
        }
        if ($dizAggi) {
            $dizionarioRichiesta->addField('PRAAGGIUNTIVI', 'Variabili Campi Aggiuntivi Procedimento', 2, 'itaDictionary', $dizAggi);
        }
        if ($dizTipi) {
            $dizionarioRichiesta->addField('PRATIPI', 'Variabili Tipi Aggiuntivi Procedimento', 3, 'itaDictionary', $dizTipi);
        }
        $this->variabiliRichiesta = $dizionarioRichiesta;
        return $this->variabiliRichiesta;
    }

    public function getVariabiliBaseRichiesta() {
        $i = 1;
        $dizionario = new itaDictionary();

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

        $dizionario->addField('SPARES_NOM', '', $i++, 'base', $this->dati['Ananom_spares_rec']['NOMCOG'] . " " . $this->dati['Ananom_spares_rec']['NOMNOM']);
        $dizionario->addField('SPARES_ORARIO', '', $i++, 'base', $this->dati['Ananom_spares_rec']['NOMANN']);
        $dizionario->addField('SPARES_NOMEML', '', $i++, 'base', $this->dati['Ananom_spares_rec']['NOMEML']);
        $dizionario->addField('SPARES_NOMTEL', '', $i++, 'base', $this->dati['Ananom_spares_rec']['NOMTEL']);
        $dizionario->addField('SPARES_NOMFAX', '', $i++, 'base', $this->dati['Ananom_spares_rec']['NOMFAX']);

        $dizionario->addField('PRADES', 'Descrizione Procedimento', $i++, 'base', $this->dati['PRADES']);
        $dizionario->addField('TIPDES', 'Tipologia Procedimento', $i++, 'base', $this->dati['Anatip_rec']['TIPDES']);
        $dizionario->addField('SETDES', 'Settore', $i++, 'base', $this->dati['Anaset_rec']['SETDES']);
        $dizionario->addField('ATTDES', 'Attività', $i++, 'base', $this->dati['Anaatt_rec']['ATTDES']);

        $dizionario->addField('PRARES_NOM', '', $i++, 'base', $this->dati['Ananom_prares_rec']['NOMCOG'] . " " . $this->dati['Ananom_spares_rec']['NOMNOM']);
        $dizionario->addField('PRARES_ORARIO', '', $i++, 'base', $this->dati['Ananom_prares_rec']['NOMANN']);
        $dizionario->addField('PRARES_NOMEML', '', $i++, 'base', $this->dati['Ananom_prares_rec']['NOMEML']);
        $dizionario->addField('PRARES_NOMTEL', '', $i++, 'base', $this->dati['Ananom_prares_rec']['NOMTEL']);
        $dizionario->addField('PRARES_NOMFAX', '', $i++, 'base', $this->dati['Ananom_prares_rec']['NOMFAX']);

        $dizionario->addField('RICSOG', 'Demoninazione Intestatario Richiesta', $i++, 'base', $this->dati['Proric_rec']['RICCOG'] . " " . $this->dati['Proric_rec']['RICNOM']);

        $dizionario->addField('RICCOG', 'Cognome Intestatario Richiesta', $i++, 'base', $this->dati['Proric_rec']['RICCOG']);
        $dizionario->addField('RICNOM', 'Nome Intestatario Richiesta', $i++, 'base', $this->dati['Proric_rec']['RICNOM']);
        $dizionario->addField('RICVIA', 'Indirizzo Intestatario Richiesta', $i++, 'base', $this->dati['Proric_rec']['RICVIA']);
        $dizionario->addField('RICCAP', 'Cap Intestatario Richiesta', $i++, 'base', $this->dati['Proric_rec']['RICCAP']);
        $dizionario->addField('RICCOM', 'Comune Intestatario Richiesta', $i++, 'base', $this->dati['Proric_rec']['RICOM']);
        $dizionario->addField('RICPRV', 'Provincia Intestatario Richiesta', $i++, 'base', $this->dati['Proric_rec']['RICPRV']);
        $dizionario->addField('RICNAZ', 'Nazione Intestatario Richiesta', $i++, 'base', $this->dati['Proric_rec']['RICNAZ']);
        $dizionario->addField('RICNAS', 'Data nascita Intestatario Richiesta', $i++, 'base', $this->dati['Proric_rec']['RICNAS']);


        $dizionario->addField('RICFIS', 'Codice Fiscale/P.Iva Intestatario Richiesta', $i++, 'base', $this->dati['Proric_rec']['RICFIS']);
        $dizionario->addField('RICEMA', 'Email Intestatario Pratica', $i++, 'base', $this->dati['Proric_rec']['RICEMA']);


        $dizionario->addField('RICNUM', '', $i++, 'base', $this->dati['Proric_rec']['RICNUM']);
        $dizionario->addField('RICNUM_FORMATTED', '', $i++, 'base', substr($this->dati['Proric_rec']['RICNUM'], 4, 6) . "/" . substr($this->dati['Proric_rec']['RICNUM'], 0, 4));
        $this->variabiliBase = $dizionario;
        return $this->variabiliBase;
    }

    public function getVariabiliCampiAggiuntiviRichiesta($PRAM_DB = "") {
        $variabiliCampiAggiuntivi = new itaDictionary();

        $currSeq = $this->dati['seq'];
        $sql = "
            SELECT
                RICDAG.ITEKEY AS ITEKEY,
                RICDAG.ITECOD AS ITECOD,
                RICDAG.DAGDES AS DAGDES,                
                RICDAG.DAGKEY AS DAGKEY,
                RICDAG.DAGTIP AS DAGTIP,        
                RICDAG.RICDAT AS RICDAT,
                RICITE.ITERDM AS ITERDM,
                RICITE.ITEMLT AS ITEMLT
            FROM
                RICITE RICITE
            LEFT OUTER JOIN 
                RICDAG RICDAG
            ON 
                RICITE.ITEKEY=RICDAG.ITEKEY            
            WHERE DAGTIP = ''  AND RICITE.RICNUM='" . $this->dati['Proric_rec']['RICNUM'] . "' AND ITESEQ < '" . $currSeq . "' ";

        $Ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        $i = 0;
        if ($Ricdag_tab) {
            foreach ($Ricdag_tab as $chiave => $Ricdag_rec) {
                // Nuovo
                $chiaveCampo = $Ricdag_rec['DAGKEY'];
                if ($Ricdag_rec['ITERDM'] || $Ricdag_rec['ITEMLT']) {
                    $suffix = end(explode("_", $Ricdag_rec['DAGSET']));
                    $suffix = ($suffix) ? "_" . $suffix : "";
                    $chiaveCampo = $chiaveCampo . $suffix;
                }
                // Nuovo
                
                // Vecchio
                //$descrizioneCampo = $Ricdag_rec['DAGDES'] ? $Itedag_rec['DAGDES'] : $Itedag_rec['DAGKEY'];
                //$variabiliCampiAggiuntivi->addField($Itedag_rec['DAGKEY'], $descrizioneCampo, $i++, 'base', $Ricdag_rec['RICDAT']);
                
                // Nuovo
                //$descrizioneCampo = $Ricdag_rec['DAGDES'] ? $Itedag_rec['DAGDES'] : $Itedag_rec['DAGKEY'];
                $variabiliCampiAggiuntivi->addField($chiaveCampo, $descrizioneCampo, $i++, 'base', $Ricdag_rec['RICDAT']);
                // Nuovo
            }
            $this->variabiliCampiAggiuntivi = $variabiliCampiAggiuntivi;
        } else {
            $this->variabiliCampiAggiuntivi = null;
        }
        return $this->variabiliCampiAggiuntivi;
    }

    public function getVariabiliTipiAggiuntiviRichiesta($PRAM_DB = "") {
        $variabiliTipiDato = new itaDictionary();
        $currSeq = $this->dati['seq'];
        $sql = "
            SELECT
                RICDAG.ITEKEY AS ITEKEY,
                RICDAG.ITECOD AS ITECOD,
                RICDAG.DAGKEY AS DAGKEY,
                RICDAG.DAGDES AS DAGDES,                
                RICDAG.DAGTIP AS DAGTIP,
                RICDAG.RICDAT AS RICDAT
            FROM
                RICITE RICITE
            LEFT OUTER JOIN 
                RICDAG RICDAG
            ON 
                RICITE.ITEKEY=RICDAG.ITEKEY            
            WHERE 
                DAGTIP<>'' AND RICITE.RICNUM='" . $this->dati['Proric_rec']['RICNUM'] . "' AND ITESEQ < '" . $currSeq . "' 
            ORDER BY
                DAGSEQ";

        $Ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);
        if ($Ricdag_tab) {
            $i = 0;
            foreach ($Ricdag_tab as $chiave => $Ricdag_rec) {
                $variabiliTipiDato->addField($Ricdag_rec['DAGTIP'], $Ricdag_rec['DAGDES'], $i++, 'base', $Ricdag_rec['RICDAT'], true);
            }
            $this->variabiliTipiAggiuntivi = $variabiliTipiDato;
        } else {
            $this->variabiliTipiAggiuntivi = null;
        }
        return $this->variabiliTipiAggiuntivi;
    }

    function elaboraTabella($html, $dati) {
        $dom = new DOMDocument;
        //
        // Documento template
        //
        @$dom->loadHTML($html);

        //
        // Tabella template
        //
        $table = $dom->getElementsByTagName('table');

        //
        // Dom di Output
        //
        $printDOM = new DOMDocument();
        $cloned = $table->item(0)->cloneNode(TRUE);
        $tbody = $cloned->getElementsByTagName('tbody')->item(0);
        $trToRemove = $tbody->getElementsByTagName('tr');
        while ($trToRemove->length > 0) {
            $tbody->removeChild($trToRemove->item(0));
        }
        $printDOM->appendChild($printDOM->importNode($cloned, TRUE));


        $links = $dom->getElementsByTagName('tr');
        $trArr = array();
        foreach ($links as $id => $link) {

            $class = $link->getAttribute('class');
            if ($class == 'ita-table-header') {
                $printDOM->getElementsByTagName('tbody');
                $printDOM->documentElement->appendChild($printDOM->importNode($link, TRUE));
            }
        }

        foreach ($links as $id => $link) {
            $class = $link->getAttribute('class');
            if ($class !== 'ita-table-header' && $class !== 'ita-table-footer') {
                $tmpDOM = new DOMDocument();

                $cloned = $link->cloneNode(TRUE);

                $tmpDOM->appendChild($tmpDOM->importNode($cloned, TRUE));

                $stringTR = $tmpDOM->saveHtml();

                $tmpDOM = null;
                $printDOM->getElementsByTagName('tbody');
                foreach ($dati as $key => $recordDati) {
                    //
                    // Qui Sostitisco
                    //
                    $contenutoFinale = $this->sostituisciVariabili($stringTR, $recordDati);
                    $tmpDOM = new DOMDocument();
                    $tmpDOM->loadHTML($contenutoFinale);
                    $trNode = $tmpDOM->getElementsByTagName('tr')->item(0);
                    $printDOM->documentElement->appendChild($printDOM->importNode($trNode, TRUE));
                }
            }
        }
        foreach ($links as $id => $link) {
            $class = $link->getAttribute('class');
            if ($class == 'ita-table-footer') {
                $printDOM->getElementsByTagName('tbody');
                $printDOM->documentElement->appendChild($printDOM->importNode($link, TRUE));
            }
        }

        return $printDOM->saveHtml();
    }

}

?>
