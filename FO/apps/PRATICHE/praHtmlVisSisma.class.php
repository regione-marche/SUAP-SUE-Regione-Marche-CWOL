<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlVis.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibDomus.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibSoggetti.class.php';

class praHtmlVisSisma extends praHtmlVis {

    public $autorizSisma;
    private $imagesPath;
    private $praLibSoggetti;

    public function __construct() {
        parent::__construct();

        if (file_exists(ITA_PRATICHE_PATH . '/PRATICHE_italsoft/customClass/AutorizSisma/AutorizSisma.class.php')) {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/customClass/AutorizSisma/AutorizSisma.class.php';
            $this->autorizSisma = new AutorizSisma();
        }

        $this->imagesPath = ITA_SUAP_PUBLIC . '/PRATICHE_italsoft/customClass/AutorizSisma/resources/images';

        $iconSize = '20px';

        $html = new html();
        $this->iconaBianca = $html->getImage($this->imagesPath . '/Legenda_CasaBianca.png', $iconSize);
        $this->iconaBlu = $html->getImage($this->imagesPath . '/Legenda_CasaBlu.png', $iconSize);
        $this->iconaGialla = $html->getImage($this->imagesPath . '/Legenda_CasaGialla.png', $iconSize);
        $this->iconaGrigia = $html->getImage($this->imagesPath . '/Legenda_CasaGrigia.png', $iconSize);
        $this->iconaRossa = $html->getImage($this->imagesPath . '/Legenda_CasaRossa.png', $iconSize);
        $this->iconaVerde = $html->getImage($this->imagesPath . '/Legenda_CasaVerde.png', $iconSize);
        $this->iconaFascicoloDomus = $html->getImage(frontOfficeLib::getIcon('paperclip'), $iconSize);

        $this->praLibSoggetti = new praLibSoggetti();
    }

    public function DisegnaPagina($dati, $extraParams) {
        $htmlLegenda = <<<HTML
<table>
    <tr><td>{$this->iconaBianca}</td><td>In compilazione</td></tr>
    <tr><td>{$this->iconaGialla}</td><td>Documento inviato/attesa di presa in carico</td></tr>
    <tr><td>{$this->iconaVerde}</td><td>Depositato/Autorizzazione/Esito verifica NTC/Presa d'atto</td></tr>
    <tr><td>{$this->iconaBlu}</td><td>In istruttoria/Sorteggiata</td></tr>
    <tr><td>{$this->iconaRossa}</td><td>Richiesta integrazione/Art. 10bis241/91/Atti_sospensivi_del_Procedimento/Sospensone_lavori</td></tr>
    <tr><td>{$this->iconaGrigia}</td><td>Archiviato/rigettato/irricevibile</td></tr>
    <tr><td>{$this->iconaFascicoloDomus}</td><td>Funzione per la visualizzazione del fascicolo Domus</td></tr>
</table><br><br>
HTML;

        output::appendHtml($htmlLegenda);

        parent::DisegnaPagina($dati, $extraParams);

        output::$html_out = str_replace('Denominazione Impresa', 'Committente', output::$html_out);
        output::$html_out = str_replace('<table ', '<table style="font-size: 14px;" ', output::$html_out);
    }

    public function getDatiRiga($Proric_rec, $extraParams, $procedimentoIntegrazione, $Iteevt_rec_int, $anaparBloccoIntegrazioniVal) {
        $arrayRecord = parent::getDatiRiga($Proric_rec, $extraParams, $procedimentoIntegrazione, $Iteevt_rec_int, $anaparBloccoIntegrazioniVal);

        $datiAggiuntiviSelect = array(
            'COMUNEDESTINATARIO',
            'INTER_LOCALITA',
            'INTER_VIA',
            'INTER_CIV',
            'INTER_CAP',
            'TIPO_INTERVENTO'
        );

        $datiAggiuntivi = array();

        /*
         * Sovrascrivo i dati della pratica con i riferimenti alla pratica
         * DOMUS.
         */

        if ($Proric_rec['RICCONFCONTEXT'] === 'DOMUS') {
            $statoPratica = $statoPraticaProtocollo = '';

            if ($Proric_rec['RICCONFDATA']) {
                $dataAcquisizione = frontOfficeLib::convertiData($Proric_rec['RICCONFDATA']);
                $statoPratica = "Acquisita dall'ente il $dataAcquisizione";
            }

            $praLibDomus = new praLibDomus();
            $infoPratica = $praLibDomus->getPratica($Proric_rec['RICNUM']);

            if ($infoPratica) {
                $statoPratica .= ' con stato ' . $infoPratica['Stato'];
                $arrayRecord['NUMERO_PROTOCOLLO'] .= '<br><span style="color: darkmagenta;">' . $infoPratica['CodiceFascicolo'] . '</span>';

                if (
                        (
                        !isset($this->autorizSisma->tipologiaPerStatoPratica[$infoPratica['CodStato']]) ||
                        !count($this->autorizSisma->tipologiaPerStatoPratica[$infoPratica['CodStato']])
                        )
                ) {
                    $arrayRecord['COLLEGAMENTO_INTEGRAZIONE'] = '';
                }

                $arrayRecord['NUMERO_PROGETTO'] = $infoPratica['NumeroRichiestaInterno'];
            } else {
                $arrayRecord['COLLEGAMENTO_INTEGRAZIONE'] = '';
            }

            $arrayRecord['STATO_PRATICA'] = $statoPratica;
            $arrayRecord['STATO_PRATICA_PROTOCOLLO'] = $statoPraticaProtocollo;
        }

        $nomeIcona = $this->autorizSisma->getIconaPerStatoPratica($infoPratica['CodStato']) ?: 'iconaBianca';
        if ($nomeIcona && isset($this->{$nomeIcona})) {
            $currentStato = $arrayRecord['STATO_PRATICA'];
            $arrayRecord['STATO_PRATICA'] = '<div style="text-align: center;">' . $this->{$nomeIcona} . '</div>';
            if ($currentStato) {
                $arrayRecord['STATO_PRATICA'] .= '<br>' . $currentStato;
            }
        }

        $Ricdag_ric_tab = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT DAGKEY, RICDAT FROM RICDAG WHERE DAGNUM = '{$Proric_rec['RICNUM']}' AND DAGKEY IN ('" . implode("', '", $datiAggiuntiviSelect) . "')");
        foreach ($Ricdag_ric_tab as $Ricdag_ric_rec) {
            $datiAggiuntivi[$Ricdag_ric_rec['DAGKEY']] = htmlspecialchars($Ricdag_ric_rec['RICDAT'], ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
        }

        $textUbicazione = '';

        if ($datiAggiuntivi['INTER_LOCALITA']) {
            $textUbicazione .= $datiAggiuntivi['INTER_LOCALITA'] . '<br>';
        }

        $textUbicazione .= $datiAggiuntivi['INTER_VIA'] . ' ';
        $textUbicazione .= $datiAggiuntivi['INTER_CIV'];

        $arrayRecord['RICHIESTA_COMUNE'] = $datiAggiuntivi['COMUNEDESTINATARIO'] . ' ' . $datiAggiuntivi['INTER_CAP'];
        $arrayRecord['RICHIESTA_LOCALIZZAZIONE'] = $textUbicazione;

        $arrayRecord['TIPOLOGIA_INTERVENTO'] = $datiAggiuntivi['TIPO_INTERVENTO'] === 'a' ? 'Autorizzazione sismica' : $datiAggiuntivi['TIPO_INTERVENTO'] === 'b' ? 'Deposito' : '';

        $Ricite_tab = $this->praLib->GetRicite($Proric_rec['RICNUM'], 'ricnum', $extraParams['PRAM_DB'], true);

        $ruoliSoggetto = array();

        $soggettiRichiesta = $this->praLibSoggetti->getSoggettiRichiesta($extraParams['PRAM_DB'], $Proric_rec, $Ricite_tab);
        foreach ($soggettiRichiesta as $RUOCOD => $soggettiRuolo) {
            foreach ($soggettiRuolo as $soggetto) {
                if ($soggetto['CODICEFISCALE_CFI'] === frontOfficeApp::$cmsHost->getAltriDati('FISCALE')) {
                    if (praRuolo::getSystemSubjectRoleFields($RUOCOD)) {
                        $ruoliSoggetto[$RUOCOD] = praRuolo::$SISTEM_SUBJECT_ROLES[praRuolo::getSystemSubjectRoleFields($RUOCOD)]['RUODES'];
                    } else {
                        /*
                         * Ruolo personalizzato
                         */
                        $Anaruo_rec = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT RUODES FROM ANARUO WHERE RUOCOD = '$RUOCOD'", false);
                        $ruoliSoggetto[$RUOCOD] = $Anaruo_rec['RUODES'];
                    }

                    continue 2;
                }
            }
        }

        $arrayRecord['RUOLI_SOGGETTO'] = implode('<hr>', $ruoliSoggetto);

        /*
         * Chiamo il ws per vedere se ci sono protocolli ed in caso attivo la Graffetta
         */
        $arrayRecord['COLLEGAMENTO_ALLEGATI'] = "";
        if ($Proric_rec['RICSTA'] == '01') {
            require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibDomus.class.php';
            $praLibDomus = new praLibDomus();
            $infoPratica = $praLibDomus->getDocumentiFascicolo($Proric_rec['RICNUM']);
            if ($infoPratica) {
                if ($infoPratica['Protocolli']) {
                    $collegamentoAllegati = ItaUrlUtil::GetPageUrl(array(
                                'p' => $extraParams['config']['attachment_page'],
                                'event' => 'ricerca',
                                'parent' => 'ricerca',
                                'ricnum' => $Proric_rec['RICNUM'],
                    ));
                    $arrayRecord['COLLEGAMENTO_ALLEGATI'] = $collegamentoAllegati;
                }
            }
        }
        return $arrayRecord;
    }

    public function elaboraRecordProricTabella($Proric_rec, $datiRiga, $extraParams) {
        $tableRowData = parent::elaboraRecordProricTabella($Proric_rec, $datiRiga, $extraParams);
        $SISMATableRowData = array();

        $SISMATableRowData[0] = $tableRowData[0]; // Numero richiesta
        $SISMATableRowData[1] = $datiRiga['NUMERO_PROGETTO'];
        $SISMATableRowData[2] = $datiRiga['TIPOLOGIA_INTERVENTO'];
        $SISMATableRowData[3] = $tableRowData[1]; // Numero fascicolo e data protocollo
        $SISMATableRowData[4] = $tableRowData[2]; // Procedimento
        $SISMATableRowData[5] = $tableRowData[3]; // Dati impresa (committente)
        $SISMATableRowData[6] = $datiRiga['RICHIESTA_COMUNE'];
        $SISMATableRowData[7] = $datiRiga['RICHIESTA_LOCALIZZAZIONE'];
        $SISMATableRowData[8] = $tableRowData[6]; // Stato pratica
        $SISMATableRowData[9] = $datiRiga['RUOLI_SOGGETTO'];
        $SISMATableRowData[10] = $tableRowData[7]; // Integra/annulla
        $SISMATableRowData[11] = $tableRowData[8]; // Allegati pubblicati

        return $SISMATableRowData;
    }

    public function getTableHeaders($dati, $extraParams) {
        $tableHeaders = array(
            'Numero<br>Richiesta',
            'Numero<br>Progetto',
            'Tipo<br>Procedimento',
            'N. Fascicolo<br>Data Protocollo',
            'Procedimento',
            'Committente',
            'Comune',
            'Indirizzo',
            'Stato<br>Pratica',
            'Ruolo Soggetto<br>Autenticato'
        );

        if ($extraParams['HideAnnullaIcon'] !== true) {
            $tableHeaders[] = array('text' => 'Annulla', 'attrs' => array('data-sorter' => 'false'));

            if ($extraParams['config']['integrazione'] == 1) {
                $tableHeaders[] = array('text' => 'Integra', 'attrs' => array('data-sorter' => 'false'));
            }
        } else {
            if ($extraParams['config']['integrazione'] == 1) {
                $tableHeaders[] = array('text' => 'Integra/<br>Annulla', 'attrs' => array('data-sorter' => 'false'));
            }
        }

        $tableHeaders[] = array('text' => 'Visualizza<br />Fascicolo', 'attrs' => array('data-sorter' => 'false'));

        return $tableHeaders;
    }

}
