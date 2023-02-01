<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlVis.class.php';

class praVis_admin extends itaModelFO {

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
        switch ($this->request['event']) {
            default:
                $this->disegnaFormRicerca();
                break;

            case 'elenca':
                $SQL = $this->creaSQLRicerca();

                if (!$SQL) {
                    $this->disegnaFormRicerca();
                    break;
                }

                $Proric_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $SQL, true);

                if (!$Proric_tab) {
                    output::addAlert('La ricerca non ha prodotto risultati.');
                    $this->disegnaFormRicerca();
                    break;
                }

                output::appendHtml("<br>");
                output::appendHtml("<div style=\"text-align: right; font-size: 1.2em;\">");
                output::appendHtml("<b>Totale richieste trovate: " . count($Proric_tab) . "</b>");
                output::appendHtml("</div>");

                output::addBr();

                $praHtmlVis = new praHtmlVis();

                if ($this->config['view'] == 0) {
                    $tableData = array('header' => array(), 'body' => array());
                    $tableData['header'][] = 'Numero<br />Richiesta';
                    $tableData['header'][] = 'N./Data<br />Protocollo';
                    $tableData['header'][] = 'Procedimento';
                    $tableData['header'][] = 'Dati Impresa';
                    $tableData['header'][] = 'Inizio<br />del';
                    $tableData['header'][] = 'Stato<br />Richiesta';
                    $tableData['header'][] = 'Stato';
                    $tableData['header'][] = 'Data<br />Acquisizione';
                    $tableData['header'][] = 'Data<br />Chiusura';
                    $tableData['header'][] = 'Codice Fiscale/<br />Nome Utente';

                    foreach ($Proric_tab as $Proric_rec) {
                        $datiRiga = $praHtmlVis->getDatiRiga($Proric_rec, array(
                            'config' => $this->config,
                            'PRAM_DB' => $this->PRAM_DB
                            ), '', '', '');

                        $tableRow = array();

                        $cellStyle = '';
                        if ($Proric_rec['RICRPA']) {
                            $cellStyle = 'style="color: blue;"';
                        }
                        
                        if ($Proric_rec['RICRUN']) {
                            $cellStyle = 'style="color: brown;"';
                        }

                        $textRiferimento = '';
                        if ($datiRiga['RIFERIMENTO_RICHIESTA']) {
                            $textRiferimento .= "<br /><small>Rif. {$datiRiga['RIFERIMENTO_RICHIESTA']}</small>";
                        }

                        if ($datiRiga['PRATICA_PADRE']) {
                            $textRiferimento .= "<br /><small><abbr title=\"Accorpata alla richiesta\">Acc.</abbr> {$datiRiga['PRATICA_PADRE']}</small>";
                        }

                        if ($datiRiga['SPORTELLO_AGGREGATO']) {
                            $textRiferimento .= "<br /><small>(<span title=\"Sportello aggregato\">{$datiRiga['SPORTELLO_AGGREGATO']}</span>)</small>";
                        }

                        $textProcedimento = "<b>{$datiRiga['NUMERO_PROCEDIMENTO']}</b> - {$datiRiga['OGGETTO']}";
                        if ($datiRiga['ATTIVITA']) {
                            $textProcedimento = "{$datiRiga['ATTIVITA']}<br />" . $textProcedimento;
                        }
                        if ($datiRiga['SETTORE']) {
                            $textProcedimento = "{$datiRiga['SETTORE']}<br />" . $textProcedimento;
                        }

                        $textInoltro = '';
                        if ($datiRiga['DATA_INOLTRO']) {
                            $textInoltro = "<br />il {$datiRiga['DATA_INOLTRO']} {$Proric_rec['RICTIM']}";
                        }

                        $textProtocollo = '';
                        if ($datiRiga['STATO_PRATICA_PROTOCOLLO']) {
                            $textProtocollo = "<br />con Prot. N. {$datiRiga['STATO_PRATICA_PROTOCOLLO']}";
                        }

                        $tableRow[] = "<div data-sortValue=\"{$Proric_rec['RICNUM']}\"><a href=\"{$datiRiga['COLLEGAMENTO_RICHIESTA']}\">{$datiRiga['NUMERO_RICHIESTA']}</a>$textRiferimento</div>";
                        $tableRow[] = "<div data-sortValue=\"{$Proric_rec['RICNPR']}\">{$datiRiga['NUMERO_PROTOCOLLO']}<br />{$datiRiga['DATA_PROTOCOLLO']}</div>";
                        $tableRow[] = "<div $cellStyle><a href=\"{$datiRiga['COLLEGAMENTO_RICHIESTA']}\" $cellStyle>" . $textProcedimento . '</a></div>';
                        $tableRow[] = "<div $cellStyle>" . $datiRiga['NOMINATIVO'] . '</div>';
                        $tableRow[] = "<div $cellStyle data-sortValue=\"{$Proric_rec['RICDRE']}\">" . $datiRiga['DATA_RICHIESTA'] . '<br />' . $Proric_rec['RICORE'] . '</div>';
                        $tableRow[] = "<div $cellStyle data-sortValue=\"{$Proric_rec['RICSTA']}\">" . $datiRiga['STATO_INOLTRO'] . $textInoltro . $datiRiga['ANNULLA_COMUNICA'] . '</div>';
                        $tableRow[] = "<div $cellStyle>" . $datiRiga['STATO_PRATICA'] . $textProtocollo . '</div>';
                        $tableRow[] = "<div $cellStyle>" . $datiRiga['DATA_ACQUISIZIONE'] . '</div>';
                        $tableRow[] = "<div $cellStyle>" . $datiRiga['DATA_CHIUSURA'] . '</div>';
                        $tableRow[] = "<div $cellStyle>" . $Proric_rec['RICFIS'] . '<br />' . $datiRiga['UTENTE_CMS'] . '</div>';

                        $tableData['body'][] = $tableRow;
                    }

                    output::addTable($tableData, array(
                        'sortable' => true,
                        'paginated' => true
                    ));
                } elseif ($this->config['view'] == 1) {
                    foreach ($Proric_tab as $Proric_rec) {
                        $datiRiga = $praHtmlVis->getDatiRiga($Proric_rec, array(
                            'config' => $this->config,
                            'PRAM_DB' => $this->PRAM_DB
                            ), '', '', '');

                        output::appendHtml("<div>");
                        output::appendHtml("<h3> Numero " . $datiRiga['NUMERO_RICHIESTA'] . "</h3></br>");
                        output::appendHtml("<h1><b>" . $datiRiga['NUMERO_PROTOCOLLO'] . "</b> - " . $datiRiga['OGGETTO'] . "</h1></br>");
                        output::appendHtml("<h3> Richiesta del  : " . $datiRiga['DATA_RICHIESTA'] . " ore " . $Proric_rec['RICORE'] . "</h3>");
                        output::appendHtml("<h3> Denominazione Impresa  : " . $datiRiga['DATI_IMPRESA']['DENOMIMPRESA'] . "</h3>");
                        output::appendHtml("<h3> Codice Fiscale Impresa  : " . $datiRiga['DATI_IMPRESA']['FISCALE'] . "</h3>");
                        output::appendHtml("<h3> Stato " . $datiRiga['STATO_INOLTRO'] . "</h3></br>");
                        output::appendHtml("<h3> Codice Fiscale : " . $Proric_rec['RICFIS'] . "<br>" . $datiRiga['UTENTE_CMS'] . "</h3>");
                        output::appendHtml("</div>");
                    }
                }
                break;
        }

        return output::$html_out;
    }

    private function disegnaFormRicerca() {
        output::addForm(ItaUrlUtil::GetPageUrl(array()), 'GET', array(
            'class' => 'italsoft-form--fixed',
            'id' => 'form1'
        ));

        output::addHidden('event', 'elenca');

        output::addInput('text', 'Utente', array(
            'name' => 'utente'
        ));

        output::addBr();

        output::addInput('text', 'N. Pratica', array(
            'name' => 'pratica',
            'size' => 6,
            'maxlength' => 6
        ));

        output::addInput('text', 'Anno', array(
            'name' => 'anno',
            'size' => 4,
            'maxlength' => 4
        ));

        output::addBr();

        output::addInput('text', 'Denominazione Impresa', array(
            'name' => 'denomImpresa',
            'size' => 15
        ));

        output::addBr();

        output::addInput('select', 'Stato', array(
            'name' => 'tipo'
            ), array(
            '' => 'Tutte',
            '99' => 'Richieste in corso',
            '98' => 'Richieste annullate',
            '01' => 'Richieste inoltrate',
            '91' => 'Inviate per la comunicazione Unica d\'impresa',
            '02' => 'Richieste acquisite',
            '03' => 'Richieste chiuse'
        ));

        output::addBr();

        $sportelloOptions = array('' => 'Tutti');

        $Anatsp_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANATSP ORDER BY TSPCOD", true);
        foreach ($Anatsp_tab as $Anatsp_rec) {
            $sportelloOptions[$Anatsp_rec['TSPCOD']] = $Anatsp_rec['TSPDES'];
        }

        output::addInput('select', 'Sportello', array(
            'name' => 'sportello'
            ), $sportelloOptions);

        output::addBr();

        $aggregatoOptions = array('' => 'Tutti');

        $Anaspa_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM ANASPA ORDER BY SPACOD", true);
        foreach ($Anaspa_tab as $Anaspa_rec) {
            $aggregatoOptions[$Anaspa_rec['SPACOD']] = $Anaspa_rec['SPADES'];
        }

        output::addInput('select', 'Aggregato', array(
            'name' => 'aggregato'
            ), $aggregatoOptions);

        output::addBr();

        output::addSubmit('Elenca');

        output::closeTag('form');
    }

    public function GetDatiImpresa($codice) {
        $Ricdag_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM='$codice'
                                  AND DAGTIP<>'' AND (DAGTIP = 'DenominazioneImpresa' OR DAGTIP = 'Codfis_InsProduttivo')", true);
        if ($Ricdag_tab) {
            foreach ($Ricdag_tab as $Ricdag_rec) {
                if ($Ricdag_rec['DAGTIP'] == "DenominazioneImpresa")
                    $Denominazione = $Ricdag_rec['RICDAT'];
                if ($Ricdag_rec['DAGTIP'] == "Codfis_InsProduttivo")
                    $Fiscale = $Ricdag_rec['RICDAT'];
            }
            return array(
                "DENOMIMPRESA" => $Denominazione,
                "FISCALE" => $Fiscale
            );
        }
    }

    private function creaSQLRicerca() {
        $anno = $this->request['anno'];
        $pratica = str_repeat("0", 6 - strlen($this->request['pratica'])) . $this->request['pratica'];

        if ($this->request['anno'] && $this->request['pratica']) {
            $whereRicnum = " AND RICNUM = $anno$pratica ";
        } else if ($this->request['anno'] && $this->request['pratica'] == "") {
            $whereAnno = " AND RICNUM LIKE '$anno%' ";
        } else if ($this->request['anno'] == "" && $this->request['pratica']) {
            $wherePratica = " AND RICNUM LIKE '%$pratica' ";
        }

        if ($this->request['denomImpresa']) {
            $joinImpresa = "INNER JOIN RICDAG ON RICDAG.DAGNUM = PRORIC.RICNUM AND (DAGKEY = 'IMPRESA_RAGIONESOCIALE' OR DAGKEY = 'IMPRESAINDIVIDUALE_RAGIONESOCIALE' OR DAGTIP = 'DenominazioneImpresa')
                            AND LOWER(RICDAG.RICDAT) LIKE '%" . strtolower($this->request['denomImpresa']) . "%'";
        }

        if ($this->request['sportello']) {
            $whereSportello = " AND RICTSP = " . $this->request['sportello'];
        }

        if ($this->request['aggregato']) {
            $whereAggregato = " AND RICSPA = " . $this->request['aggregato'];
        }

        if ($this->request['utente']) {
            $codFis = frontofficeApp::$cmsHost->getCodFisFromUtente($this->request['utente']);
            if (!$codFis) {
                output::addAlert('Utente non valido', 'Attenzione', 'warning');
                return false;
            }

            $whereUtente = " AND RICFIS = '$codFis' ";
        }

        switch ($this->request['tipo']) {
            case '02': //Acquisita
                $whereStato = " AND (PROGES.GESNUM IS NOT NULL AND PROGES.GESDCH='') OR (PROPAS.PRORIN IS NOT NULL AND PROPAS.PROFIN='')";
                break;

            case '03': //Chiusa
                $whereStato = "AND (PROGES.GESNUM IS NOT NULL AND PROGES.GESDCH<>'') OR (PROPAS.PRORIN IS NOT NULL AND PROPAS.PROFIN<>'')";
                break;

            default:
                if ($this->request['tipo']) {
                    $whereStato = " AND RICSTA = '{$this->request['tipo']}'";
                }
                break;
        }

        $sql = "SELECT 
                    PRORIC.*
                FROM 
                    PRORIC PRORIC
                LEFT OUTER JOIN PROGES PROGES ON PRORIC.RICNUM=PROGES.GESPRA
                LEFT OUTER JOIN PROPAS PROPAS ON PROPAS.PRORIN=PRORIC.RICNUM
                $joinImpresa
                WHERE 
                    PRORIC.RICSTA<>'OF' 
                    $whereRicnum
                    $whereAnno
                    $wherePratica
                    $whereSportello
                    $whereUtente
                    $whereStato
                    $whereAggregato
                GROUP BY 
                    PRORIC.RICNUM
                ORDER BY 
                    RICNUM DESC";

        return $sql;
    }

}
