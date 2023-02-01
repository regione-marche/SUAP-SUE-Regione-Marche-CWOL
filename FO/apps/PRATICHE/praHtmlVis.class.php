<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php';

class praHtmlVis {

    protected $html;
    protected $praLib;
    protected $praLibEventi;

    public function __construct() {
        $this->html = new html();
        $this->praLib = new praLib();
        $this->praLibEventi = new praLibEventi();
    }

    public function DisegnaPagina($dati, $extraParams) {
        output::appendHtml($this->addJsComunica());

        $visualizzaForm = $extraParams['TipoFiltro'] == '';
        if (isset($extraParams['config']['search_form']) && $extraParams['config']['search_form'] == 0) {
            $visualizzaForm = false;
        }

        if ($visualizzaForm) {
            $this->DisegnaFormRicerca($dati, $extraParams);
        }

        if ($extraParams['procedi']) {
            if ($extraParams['idTemplate'] != 'praInfCittadino') {
                output::appendHtml("<div style=\"font-size: 1.2em; color: blue;\">Richieste <b>" . $dati['Oggetto'] . "</b> in corso</div>");
                output::addBr();
            }
        }

        $sql = $this->getSql($dati, $extraParams);

        $Proric_tab = $this->getProric_tab($sql, $extraParams);
        

        if ($Proric_tab == "") {
            $count = 0;
        } else {
            $count = count($Proric_tab);
        }

        if ($count == 0 && $extraParams['falseOnEmpty'] == "1") {
            return false;
        }

        if ($extraParams['idTemplate'] != 'praInfCittadino') {
            output::appendHtml("<div style=\"font-size: 1.2em; text-align: right;\">");
            output::appendHtml("<b>Totale richieste trovate: " . $count . "</b>");
            output::appendHtml("</div>");
        }

        switch ($extraParams['config']['view']) {
            default:
            case 0:
                $tableData = array('body' => array());

                $tableData['header'] = $this->getTableHeaders($dati, $extraParams);

                if (!$extraParams['Ajax']) {
                    $tableData['body'] = $this->elaboraRecordsProric($Proric_tab, $extraParams);
                }

                output::addBr();
                output::addTable($tableData, array(
                    'sortable' => true,
                    'paginated' => true,
                    'ajax' => $extraParams['Ajax']
                ));
                break;

            case 1:
                output::addBr(2);

                $procedimentoIntegrazione = $this->praLib->GetProcedimentoIntegrazione($extraParams['PRAM_DB']);
                $Iteevt_rec_int = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM ITEEVT WHERE ITEPRA = '$procedimentoIntegrazione'", false);
                $anaparBlkIntegra_rec = $this->praLib->GetAnapar('BLOCK_INTEGRAZIONI', 'parkey', $extraParams['PRAM_DB'], false);

                foreach ($Proric_tab as $Proric_rec) {
                    $datiRiga = $this->getDatiRiga($Proric_rec, $extraParams, $procedimentoIntegrazione, $Iteevt_rec_int, $anaparBlkIntegra_rec['PARVAL']);

                    output::appendHtml("<div>");
                    output::appendHtml("<h3> N. Richiesta: " . $datiRiga['NUMERO_RICHIESTA'] . "</h3>");
                    output::appendHtml("<h3> N. Procedimento: " . $Proric_rec['RICPRO'] . " - " . $datiRiga['OGGETTO'] . "</h3>");
                    output::appendHtml("<h3> Richiesta del: " . $datiRiga['DATA_RICHIESTA'] . " ore " . $Proric_rec['RICORE'] . "</h3>");
                    output::appendHtml("<h3> Denominazione Impresa: " . $datiRiga['DATI_IMPRESA']['DENOMIMPRESA'] . "</h3>");
                    output::appendHtml("<h3> Codice Fiscale Impresa: " . $datiRiga['DATI_IMPRESA']['FISCALE'] . "</h3>");
                    output::appendHtml("<h3> Stato: " . $datiRiga['STATO_INOLTRO'] . "</h3>");

                    $textInoltro = '';
                    if ($Proric_rec['RICTIM']) {
                        $textInoltro = " ore {$Proric_rec['RICTIM']}";
                    }

                    output::appendHtml("<h3> Inoltro del: " . $datiRiga['DATA_INOLTRO'] . $textInoltro . "</h3>");
                    output::appendHtml("<h3> Stato Pratica: {$datiRiga['STATO_PRATICA']}</h3>");
                    output::appendHtml("<h3> Data Acquisizione: {$datiRiga['DATA_ACQUISIZIONE']}</h3>");
                    output::appendHtml("<h3> Data Chiusura: {$datiRiga['DATA_CHIUSURA']}</h3>");
                    output::appendHtml("<span><b>---------------------------------------------------------------------------------------------</b></span>");
                    output::appendHtml("</div>");
                }
                break;

            case 2:
                $tableData = array('body' => array());

                $tableHeaders = array();
                $tableHeaders[] = array('text' => 'Procedimento', 'attrs' => array('data-sorter' => 'false'));
                $tableHeaders[] = 'Inizio<br />del';

                $tableData['header'] = $tableHeaders;

                if (!$extraParams['Ajax']) {
                    $tableData['body'] = $this->elaboraRecordsProric($Proric_tab, $extraParams);
                }

                output::addBr();
                output::addTable($tableData, array(
                    'sortable' => true,
                    'paginated' => true,
                    'ajax' => $extraParams['Ajax']
                ));
                break;
        }

        if ($visualizzaForm) {
            output::closeTag('form');
        }

        return true;
    }

    public function DisegnaFormRicerca($dati, $extraParams) {
        output::addForm(ItaUrlUtil::GetPageUrl(array()), 'GET', array(
            'class' => 'italsoft-form--fixed',
            'id' => 'form1'
        ));

        output::addInput('text', 'Numero Richiesta', array(
            'id' => 'numero',
            'name' => 'numero',
            'value' => $extraParams['Numero'],
            'size' => 6,
            'maxlength' => 6
        ));

        output::addInput('text', 'Anno', array(
            'id' => 'anno',
            'name' => 'anno',
            'value' => $extraParams['Anno'],
            'size' => 4,
            'maxlength' => 4
        ));

        output::addBr();

        output::addInput('text', 'Denominazione Impresa', array(
            'id' => 'denomImpresa',
            'name' => 'denomImpresa',
            'value' => $extraParams['Impresa'],
            'size' => 40
        ));

        output::addBr();

        $parametriCNA = $this->praLib->GetParametriCNA($extraParams["PRAM_DB"]);
        $selectFiltroStato = array();
        $selectFiltroStato[''] = 'Tutte';
        $selectFiltroStato['99'] = 'Richieste in corso';

        if ($this->praLib->isEnableScadenza()){
            $selectFiltroStato['99A'] = 'Richieste in corso attive';
            $selectFiltroStato['99S'] = 'Richieste in corso scadute';
        }

        $selectFiltroStato['98'] = 'Richieste annullate';
        $selectFiltroStato['01'] = 'Richieste inoltrate';
        if (isset($parametriCNA['AGENZIA_CNA_ATTIVA']) && $parametriCNA['AGENZIA_CNA_ATTIVA'] === 'Si') {
            $selectFiltroStato['81'] = 'Richieste inoltrate ad Agenzia';
        }
        $selectFiltroStato['91'] = 'Richieste inviate per la comunicazione unica d\'impresa';
        $selectFiltroStato['02'] = 'Richieste acquisite';
        $selectFiltroStato['03'] = 'Richieste chiuse';
        $selectFiltroStato['PD'] = 'Richieste in attesa di protocollazione remota';
        $selectFiltroStato['WITHATTACH'] = 'Richieste con allegati';
        $selectFiltroStato['PROTOCOLLATE'] = 'Richieste protocollate';

        $parametriPravis = $this->praLib->GetParametriPravis($extraParams["PRAM_DB"]);
        if ($parametriPravis['PRAVIS_SELECTSTATI']) {
            $listaCustomFiltriStato = explode(',', $parametriPravis['PRAVIS_SELECTSTATI']);
            $listaCustomFiltriStato = array_map('trim', $listaCustomFiltriStato);
            foreach (array_keys($selectFiltroStato) as $statoKey) {
                if ($statoKey != '') {
                    if (!in_array($statoKey, $listaCustomFiltriStato)) {
                        unset($selectFiltroStato[$statoKey]);
                    }
                }
            }
        }

        output::addInput('select', 'Richieste da visualizzare', array(
            'id' => 'tipo',
            'name' => 'tipo',
            'value' => $extraParams['Tipo']
                ), $selectFiltroStato);

        output::addBr();

        output::addSubmit('Applica filtri');
    }

    protected function addJsComunica() {
        $content = '<div class="ui-widget-content ui-state-highlight" style="font-size:1.1em;margin:8px;padding:8px;">';
        $content .= 'L\\\'annullamento dell\\\'inoltro del file zip per la comunicazione unica d\\\'impresa permette di sbloccare la richiesta on-line per:<br>';
        $content .= '- inviare nuovamente il file zip dopo aver corretto eventuali errori o refusi,<br>';
        $content .= '- rispondere (NO) alla domanda di contestualità alla comunicazione unica per inviare direttamente all\\\'ente la richiesta.<br><br>';
        $content .= 'Confermi l\\\'annullamento?</div>';
        $content .= '</div>';
        $script = '<script type="text/javascript">';
        $script .= "
            function annullaComunica(url, richiesta){
                $('<div id =\"praVisCancelComunica\">$content</div>').dialog({
                title:\"Annullamento Inoltro Comunicazione unica d'impresa.\",
                bgiframe: true,
                resizable: false,
                height: 'auto',
                width: 'auto',
                modal: true,
                close: function(event, ui) {
                    $(this).dialog('destroy');
                },
                buttons: [
                    {
                        text: 'No',
                        class: 'italsoft-button italsoft-button--secondary',
                        click:  function() {
                            $(this).dialog('destroy');
                        }
                    },
                    {
                        text: 'Sì',
                        class: 'italsoft-button',
                        click:  function() {
                            $(this).dialog('destroy');
                            location.replace(url);
                        }
                    }
                ]
            });
            };";
        $script .= '</script>';

        return $script;
    }

    protected function getSql($dati, $extraParams, $orderBy = false) {
        $whereStato = $whereProc = $whereNumero = $whereAnno = $joinImpresa = "";

        foreach ($extraParams as &$value) {
            if (is_string($value)) {
                $value = addslashes($value);
            }
        }

        switch ($extraParams['Tipo']) {
            case "02": //Acquisita
                $field = "PROPAS.PROINI AS PROINI,
                          PROPAS.PROFIN AS PROFIN,
                          PROPAS.PRORIN AS PRORIN,";
                $join = "LEFT OUTER JOIN PROPAS PROPAS ON PROPAS.PRORIN=PRORIC.RICNUM";
                $whereStato = " AND ((PROGES.GESNUM IS NOT NULL AND PROGES.GESDCH='') OR (PROPAS.PRORIN IS NOT NULL AND PROPAS.PROFIN=''))";
                break;

            case "03": //Chiusa
                $field = "PROPAS.PROINI AS PROINI,
                          PROPAS.PROFIN AS PROFIN,
                          PROPAS.PRORIN AS PRORIN,";
                $join = "LEFT OUTER JOIN PROPAS PROPAS ON PROPAS.PRORIN=PRORIC.RICNUM";
                $whereStato = "AND ((PROGES.GESNUM IS NOT NULL AND PROGES.GESDCH<>'') OR (PROPAS.PRORIN IS NOT NULL AND PROPAS.PROFIN<>''))";
                break;

            case "WITHATTACH": //Con Allegati Pubblicati
                $field = "PROPAS.PROINI AS PROINI,
                          PROPAS.PROFIN AS PROFIN,
                          PROPAS.PRORIN AS PRORIN,";
                $join = " INNER JOIN PASDOC PASDOC ON PROGES.GESNUM = SUBSTRING(PASDOC.PASKEY, 1, 10) 
                          INNER JOIN PROPAS PROPAS ON PROPAS.PRONUM = PROGES.GESNUM";
                $whereStato = "AND PROPAS.PROPUBALL=1 AND PASDOC.PASPUB=1";
                break;

            case "PROTOCOLLATE": //Richieste protocollate
                $whereStato = "AND PRORIC.RICNPR <> 0";
                break;

            case "99A": //in corso e attive
            case "99S": //in corso e Scadute
                $whereStato = "AND RICSTA = '99'";
                break;

            default:
                if ($extraParams['Tipo']) {
                    if ($extraParams['Tipo'] == "PD") {
                        $whereStato = " AND RICSTA = '01' AND RICDATARPROT <> '' AND RICORARPROT <> ''";
                    } else {
                        $whereStato = "AND RICSTA = '" . $extraParams['Tipo'] . "'";
                    }
                }
                break;
        }

        if ($extraParams['TipoFiltro']) {
            $whereStato = "AND RICSTA = '" . $extraParams['TipoFiltro'] . "'";
        }

        if ($extraParams['Numero']) {
            $Numero = str_pad($extraParams['Numero'], 6, '0', STR_PAD_LEFT);
            $whereNumero = "AND RICNUM LIKE '____$Numero'";
        }

        if ($extraParams['Anno']) {
            $whereAnno = "AND RICNUM LIKE '" . $extraParams['Anno'] . "%'";
        }

        if ($extraParams['Impresa']) {
            $joinImpresa = "INNER JOIN RICDAG ON RICDAG.DAGNUM = PRORIC.RICNUM AND (
                                DAGKEY = 'ISCRIVENDO_COGNOME_NOME' OR
                                DAGTIP = 'DenominazioneImpresa' OR
                                DAGTIP = 'Codfis_InsProduttivo' OR
                                DAGKEY = 'IMPRESA_RAGIONESOCIALE' OR
                                DAGKEY = 'IMPRESAINDIVIDUALE_RAGIONESOCIALE' OR
                                DAGKEY = 'DICHIARANTE_COGNOME_NOME' OR
                                DAGKEY = 'DICHIARANTE_COGNOME' OR
                                DAGKEY = 'DICHIARANTE_NOME'
                            )
                            AND LOWER(RICDAG.RICDAT) LIKE '%" . strtolower($extraParams['Impresa']) . "%'";
        }

        if ($extraParams['procedi']) {
            $whereProc = "AND RICPRO = '" . $extraParams['procedi'] . "'";
        }

        if ($extraParams['config']['procedi']) {
            $whereProc = "AND RICPRO IN (" . $extraParams['config']['procedi'] . ")";
        }
        
//        $sql = "SELECT
//                    $field
//                    PRORIC.*,
//                    PROGES.GESNUM AS GESNUM,
//                    PROGES.GESDRE AS GESDRE,
//                    PROGES.GESDCH AS GESDCH
//                FROM 
//                    PRORIC PRORIC
//                LEFT OUTER JOIN PROGES PROGES ON PRORIC.RICNUM=PROGES.GESPRA
//                $join
//                $joinImpresa
//                WHERE 
//                    PRORIC.RICFIS = '" . $dati['fiscale'] . "' AND PRORIC.RICSTA<>'OF' $whereStato $whereProc $whereNumero $whereAnno
//                GROUP BY 
//                    PRORIC.RICNUM          
//                ";
//
//        if (!$orderBy) {
//            $orderBy = 'PRORIC.RICNUM DESC';
//        }
//        $sql .= " ORDER BY $orderBy";

        /*
         * Query Base
         */
        $sql = "SELECT * FROM (";
        $sql .= "SELECT
                    $field
                    PRORIC.*,
                    PROGES.GESNUM AS GESNUM,
                    PROGES.GESDRE AS GESDRE,
                    PROGES.GESDCH AS GESDCH
                FROM 
                    PRORIC PRORIC
                LEFT OUTER JOIN PROGES PROGES ON PRORIC.RICNUM=PROGES.GESPRA
                $join
                $joinImpresa
                WHERE 
                    PRORIC.RICFIS = '" . $dati['fiscale'] . "' AND PRORIC.RICSTA<>'OF' $whereStato $whereProc $whereNumero $whereAnno
                GROUP BY 
                    PRORIC.RICNUM
                ";
        /*
         * Query Richiedente
         */
        $sql .= " UNION 
                    SELECT 
                        PRORIC.*,
                        PROGES.GESNUM AS GESNUM,
                        PROGES.GESDRE AS GESDRE,
                        PROGES.GESDCH AS GESDCH
                    FROM 
                        RICSOGGETTI RICSOGGETTI
                    LEFT OUTER JOIN PRORIC PRORIC ON PRORIC.RICNUM=RICSOGGETTI.SOGRICNUM
                    LEFT OUTER JOIN PROGES PROGES ON RICSOGGETTI.SOGRICNUM=PROGES.GESPRA
                    $join
                    $joinImpresa
                    WHERE 
                        UPPER(SOGRICFIS) = '" . strtoupper($dati['fiscale']) . "' AND SOGRICRUOLO = '" . praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD'] . "' AND SOGRICDATA_FINE = ''
                        AND PRORIC.RICSTA<>'OF' $whereStato $whereProc $whereNumero $whereAnno
                    GROUP BY 
                        PRORIC.RICNUM
                ";

        /*
         * Query per ACL
         */
        /* FARE UNION PER RICACL, basta che ci sia un ACL attiva (vedere RICACLDATA_INIZIO e RICACLDATA_INIZIO) */
        /*
         * Controllo se attivata voce "ACL_ATTIVAZIONE" nella tabella ENV_CONFIG in ITAFRONOFFICE
         */
        $frontOfficeLib = new frontOfficeLib();
        $praLibAcl = new praLibAcl();
        $ITAFO_DB = ItaDB::DBOpen('ITAFRONTOFFICE', frontOfficeApp::getEnte());
        if ($praLibAcl->getAclDaParametriFO($frontOfficeLib, 'ACL_ATTIVAZIONE', $ITAFO_DB)) {

            $sql .= " UNION 
                        SELECT 
                            PRORIC.*,
                            PROGES.GESNUM AS GESNUM,
                            PROGES.GESDRE AS GESDRE,
                            PROGES.GESDCH AS GESDCH
                        FROM 
                            RICSOGGETTI RICSOGGETTI
                        LEFT OUTER JOIN PRORIC PRORIC ON PRORIC.RICNUM=RICSOGGETTI.SOGRICNUM
                        LEFT OUTER JOIN PROGES PROGES ON RICSOGGETTI.SOGRICNUM=PROGES.GESPRA
                        LEFT OUTER JOIN RICACL RICACL ON RICSOGGETTI.ROW_ID=RICACL.ROW_ID_RICSOGGETTI
                        $join
                        $joinImpresa
                        WHERE 
                            UPPER(SOGRICFIS) = '" . strtoupper($dati['fiscale']) . "' AND RICACL.RICACLDATA_FINE >= '" . date('Ymd') . "'
                            AND RICACL.RICACLDATA_INIZIO <= '" . date('Ymd') . "' 
                            AND RICACL.RICACLTRASHED = 0     
                            AND PRORIC.RICSTA<>'OF' 
                            AND ( 
                                (RICACL.RICACLATTIVA = 1 AND PRORIC.RICSTA='99') 
                                OR (RICACL.RICACLATTIVA = 2 AND (PRORIC.RICSTA='01' OR PRORIC.RICSTA='91') )
                                OR (RICACL.RICACLATTIVA = 3 ) 
                                )
                            $whereStato $whereProc $whereNumero $whereAnno  
                        GROUP BY 
                            PRORIC.RICNUM
                    ";
        }


        $sql .= ") R";
        if (!$orderBy) {
            $orderBy = 'R.RICNUM DESC';
        }

        $sql .= " ORDER BY $orderBy";
        return $sql;
    }

    public function GetDatiIscrivendo($codice, $PRAM_DB) {
        $Ricdag_rec_progsogg = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$codice' AND RICDAT <> '' AND DAGKEY = 'ISCRIVENDO_COGNOME_NOME'", false);
        return $Ricdag_rec_progsogg['RICDAT'];
    }

    public function GetDatiImpresa($codice, $PRAM_DB) {
        $Ricdag_rec_den = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$codice' AND DAGTIP <> '' AND DAGTIP = 'DenominazioneImpresa'", false);
        $Denominazione = $Ricdag_rec_den['RICDAT'];

        $Ricdag_rec_fis = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$codice' AND DAGTIP <> '' AND DAGTIP = 'Codfis_InsProduttivo'", false);
        $Fiscale = $Ricdag_rec_fis['RICDAT'];

        if ($Denominazione == "") {
            $Ricdag_rec_den = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$codice'
                                  AND DAGKEY = 'IMPRESA_RAGIONESOCIALE' AND RICDAT <> ''", false);

            if (!$Ricdag_rec_den) {
                $Ricdag_rec_den = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM=  '$codice'
                                  AND DAGKEY = 'IMPRESAINDIVIDUALE_RAGIONESOCIALE' AND RICDAT <> ''", false);

                if (!$Ricdag_rec_den) {
                    $Ricdag_rec_den = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$codice'
                                  AND DAGKEY = 'DICHIARANTE_COGNOME_NOME' AND RICDAT <> ''", false);

                    if (!$Ricdag_rec_den) {
                        $Ricdag_rec_denCog = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$codice'
                                  AND DAGKEY = 'DICHIARANTE_COGNOME' AND RICDAT <> ''", false);

                        $Ricdag_rec_denNom = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$codice'
                                  AND DAGKEY = 'DICHIARANTE_NOME' AND RICDAT <> ''", false);

                        $Ricdag_rec_den['RICDAT'] = $Ricdag_rec_denCog['RICDAT'] . " " . $Ricdag_rec_denNom['RICDAT'];
                    }
                }
            }
        }

        if ($Fiscale == "") {
            $Ricdag_rec_fis = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$codice'
                                  AND DAGKEY = 'IMPRESA_PARITAIVA_PIVA' AND RICDAT <> ''", false);

            if (!$Ricdag_rec_fis) {
                $Ricdag_rec_fis = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$codice'
                                  AND DAGKEY = 'IMPRESAINDIVIDUALE_PARITAIVA_PIVA' AND RICDAT <> ''", false);

                if (!$Ricdag_rec_fis) {
                    $Ricdag_rec_fis = ItaDB::DBSQLSelect($PRAM_DB, "SELECT * FROM RICDAG WHERE DAGNUM = '$codice'
                                  AND DAGKEY = 'DICHIARANTE_CODICEFISCALE_CFI' AND RICDAT <> ''", false);
                }
            }
        }

        $Denominazione = $Ricdag_rec_den['RICDAT'];
        $Fiscale = $Ricdag_rec_fis['RICDAT'];

        return array(
            "DENOMIMPRESA" => $Denominazione,
            "FISCALE" => $Fiscale
        );
    }

    public function getDatiRiga($Proric_rec, $extraParams, $procedimentoIntegrazione, $Iteevt_rec_int, $anaparBloccoIntegrazioniVal) {
        /*
         * Carico record ed altre informazioni aggiuntive.
         */

        $Anaset_rec = $this->praLib->GetAnaset($Proric_rec['RICSTT'], 'codice', $extraParams['PRAM_DB']);
        $Anaatt_rec = $this->praLib->GetAnaatt($Proric_rec['RICATT'], 'codice', $extraParams['PRAM_DB']);

        $bloccaIntegrazioni = true;

        /*
         * Azzero le variabili condizionali.
         */

        $riferimentoRichiesta = $praticaPadre = $numeroProtocollo = $dataProtocollo = $statoInoltro = $dataInoltro = $annullaComunica = $utenteCMS = $dataProtDiff = $sportelloAggregato = '';
        $datiImpresa = array();

        /*
         * Elaboro i dati.
         */

        $numeroRichiesta = intval(substr($Proric_rec['RICNUM'], 4, 6)) . '/' . substr($Proric_rec['RICNUM'], 0, 4);

        if ($Proric_rec['RICRPA']) {
            $riferimentoRichiesta = intval(substr($Proric_rec['RICRPA'], 4, 6)) . '/' . substr($Proric_rec['RICRPA'], 0, 4);
        }

        if ($Proric_rec['RICRUN']) {
            $praticaPadre = intval(substr($Proric_rec['RICRUN'], 4, 6)) . '/' . substr($Proric_rec['RICRUN'], 0, 4);
        }

        if ($Proric_rec['RICNPR'] != 0) {
            $numeroProtocollo = substr($Proric_rec['RICNPR'], 4) . '/' . substr($Proric_rec['RICNPR'], 0, 4);
            $dataProtocollo = frontOfficeLib::convertiData($Proric_rec['RICDPR']);
        }


        if ($extraParams['modo'] == 'cportal') {
            $nominativo = $this->GetDatiIscrivendo($Proric_rec['RICNUM'], $extraParams['PRAM_DB']);
        } else {
            $codiceDatiImpresa = $Proric_rec['RICRPA'] ?: $Proric_rec['RICNUM'];
            $datiImpresa = $this->GetDatiImpresa($codiceDatiImpresa, $extraParams['PRAM_DB']);
            $nominativo = $datiImpresa['DENOMIMPRESA'] . '<br>' . $datiImpresa['FISCALE'];
        }

        switch ($Proric_rec['RICSTA']) {
            case '01':
                $statoInoltro = 'Inoltrata';
                if ($Proric_rec['RICDATARPROT'] && $Proric_rec['RICORARPROT'] && $Proric_rec['RICNPR'] == 0) {
                    $dataProtDiff = substr($Proric_rec['RICDATARPROT'], 6, 2) . "/" . substr($Proric_rec['RICDATARPROT'], 4, 2) . "/" . substr($Proric_rec['RICDATARPROT'], 0, 4);
                    $statoInoltro = "In Attesa di protocollazione remota il $dataProtDiff alle " . $Proric_rec['RICORARPROT'];
                }
                break;

            case '91':
                $statoInoltro = 'Inviata per la comunicazione Unica d\'impresa';
                $href = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'annullaInfocamere',
                            'ricnum' => $Proric_rec['RICNUM']
                ));

                $annullaComunica = "<br><br><button class=\"italsoft-button\" onclick=\"annullaComunica('$href', '" . $Proric_rec['RICNUM'] . "'); return false;\">Annulla Inoltro a comunica</button>";
                break;

            case '81':
                $statoInoltro = 'Inoltrata ad Agenzia ' . $Proric_rec['RICAGE'];
                break;

            case '98':
                $statoInoltro = 'Annullata dal richiedente';
                break;

            case '99':
                $statoInoltro = 'Non completata la richiesta';
                break;
        }

        if ($Proric_rec['RICDAT'] != '' && $Proric_rec['RICDATARPROT'] == '' && $Proric_rec['RICORARPROT'] == '') {
            $dataInoltro = frontOfficeLib::convertiData($Proric_rec['RICDAT']);
        }

        /*
         * Elaborazione stato pratica.
         */

        $statoPratica = $statoPraticaProtocollo = $dataAcquisizione = $dataChiusura = '';
        $bloccaAnnullamento = false;

        if ($Proric_rec['RICRPA'] && $Proric_rec['RICPC'] == 0) {
            $Proges_rec_int = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT GESNUM FROM PROGES WHERE GESPRA = '{$Proric_rec['RICRPA']}'", false);

            if ($Proges_rec_int) {
                $Propas_tab_integra = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT PRORIN, PROPAK, PROINI, PROFIN FROM PROPAS WHERE PRONUM = '{$Proges_rec_int['GESNUM']}' AND PRORIN <> ''", true);

                foreach ($Propas_tab_integra as $Propas_rec_integra) {
                    if ($Propas_rec_integra['PRORIN'] == $Proric_rec['RICNUM']) {
                        $Pracom_rec_integra = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM PRACOM WHERE COMPAK = '{$Propas_rec_integra['PROPAK']}' AND COMTIP = 'A'", false);

                        if ($Pracom_rec_integra && $Pracom_rec_integra['COMPRT']) {
                            $statoPraticaProtocollo = substr($Pracom_rec_integra['COMPRT'], 4) . '/' . substr($Pracom_rec_integra['COMPRT'], 0, 4);
                        }

                        if ($Propas_rec_integra['PROINI']) {
                            $dataAcquisizione = frontOfficeLib::convertiData($Propas_rec_integra['PROINI']);
                            $statoPratica = 'Acquisita dall\'ente il ' . $dataAcquisizione;
                        }

                        if ($Propas_rec_integra['PROFIN']) {
                            $dataChiusura = frontOfficeLib::convertiData($Propas_rec_integra['PROFIN']);
                            $statoPratica = 'Chiusa il ' . $dataChiusura;
                        }
                    }
                }
            }
        } else {
            $Proges_rec = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT GESNPR, GESDRE, GESDCH, GESNUM, GESCLOSE FROM PROGES WHERE GESPRA = '{$Proric_rec['RICNUM']}'", false);

            if ($Proges_rec) {
                if ($Proges_rec['GESNPR']) {
                    $statoPraticaProtocollo = substr($Proges_rec['GESNPR'], 4) . '/' . substr($Proges_rec['GESNPR'], 0, 4);
                }

                $dataAcquisizione = frontOfficeLib::convertiData($Proges_rec['GESDRE']);

                if ($Proges_rec['GESDCH']) {
                    $dataChiusura = frontOfficeLib::convertiData($Proges_rec['GESDCH']);
                }

                $Prasta_rec = $this->praLib->GetPrasta($Proges_rec['GESNUM'], 'codice', $extraParams['PRAM_DB']);

                if ($Prasta_rec['STAFLAG'] === 'Chiusa Positivamente' || $Prasta_rec['STAFLAG'] === 'Chiusa Negativamente') {
                    $bloccaAnnullamento = true;
                }

                if ($Prasta_rec['STAPST'] != 0) {
                    $Prasta_rec['STADEX'] = substr($Prasta_rec['STADEX'], 0, -2);
                    $arrayDesc = explode(" - ", $Prasta_rec['STADEX']);
                    $lastDesc = end($arrayDesc);
                    $descEstesa = $Prasta_rec['STADES'] . " - $lastDesc";
                    //
                    if ($Proges_rec['GESCLOSE']) {
                        $statoPratica = $descEstesa;
                    } else {
                        switch ($Prasta_rec['STAPST']) {
                            case 1: //Descrizione breve
                                $statoPratica = $Prasta_rec['STADES'];
                                break;
                            case 2: //Descrizione estesa
                                $statoPratica = $descEstesa;
                                break;
                        }
                    }
//                    if ($Proges_rec['GESCLOSE']) {
//                        $Prasta_rec['STADEX'] = substr($Prasta_rec['STADEX'], 0, -2);
//                        $arrayDesc = explode(' - ', $Prasta_rec['STADEX']);
//                        $lastDesc = end($arrayDesc);
//
//                        $statoPratica = $Prasta_rec['STADES'] . ' - ' . $lastDesc;
//                    } else {
//                        $statoPratica = $Prasta_rec['STADES'];
//                    }
                } else {
                    $statoPratica = "Acquisita dall'ente il $dataAcquisizione";

                    if ($Proges_rec['GESDCH']) {
                        $statoPratica = "Chiusa il $dataChiusura";
                    }
                }
            }
        }

        /*
         * Elaboro i link.
         */

        /*
         * @Todo Verificare se la condizione andava effettivamente modificata.
         */
//        if ($descStato == "Chiusa") {
        if ($bloccaAnnullamento) {
            if ($anaparBloccoIntegrazioniVal == 'No') {
                $bloccaIntegrazioni = false;
            }
        } else {
            $bloccaIntegrazioni = false;
        }

        /*
         * Controllo Visibilita icona integrazione in caso di ACL
         */
        $vediIntAcl = false;
        $ricacl_tab = $this->praLib->GetAclSoggetto($Proric_rec['RICNUM'], 'codice', $extraParams['PRAM_DB']);
        foreach ($ricacl_tab as $ricacl_rec) {
            if ($ricacl_rec['RICACLMETA']) {
                $arrAcl = json_decode($ricacl_rec['RICACLMETA'], true);
                if (is_array($arrAcl)) {
                    foreach ($arrAcl['AUTORIZZAZIONE'] as $autorizzazione) {
                        if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_RICHIESTA' && $autorizzazione['INTEGRAZIONE_RICHIESTA']) {
                            $vediIntAcl = true;
                        }
                    }
                }
            }
        }

        /*
         * Controllo Visibilita icona integrazione in caso ESIBENTE
         */
        $vediIntEsibente = false;
        $datiUtente = frontOfficeApp::$cmsHost->getDatiUtente();
        if ($datiUtente['fiscale'] == $Proric_rec['RICFIS']) {
            $vediIntEsibente = true;
        }


        $collegamentoAnnullamento = $collegamentoIntegrazione = $collegamentoAllegati = '';

        $collegamentoRichiesta = ItaUrlUtil::GetPageUrl(array(
                    'p' => $extraParams['config']['online_page'],
                    'event' => 'navClick',
                    'direzione' => 'primoRosso',
                    'ricnum' => $Proric_rec['RICNUM']
        ));

        /*
         * Se è una richiesta accorpata, come padre prendiamo la richiesta unica
         */
        $hideIcon = $this->praLib->checkIconAnnInt($Proric_rec, $extraParams["PRAM_DB"]);
        $padre = $Proric_rec['RICNUM'];
//        if ($Proric_rec['RICRUN']) {
//            $padre = $Proric_rec['RICRUN'];
//        }

        /*
         * @Todo Verificare se la seconda condizione andava effettivamente modificata.
         */
//        if (in_array($Proric_rec['RICSTA'], array('01', '91')) && $statoPratica != 'Chiusa') {
        if (in_array($Proric_rec['RICSTA'], array('01', '91')) && !$bloccaAnnullamento && !$hideIcon) {
            $collegamentoAnnullamento = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'sceltaMotivo',
                        'ricnum' => $padre//$Proric_rec['RICNUM']
            ));
        }

        if (
                in_array($Proric_rec['RICSTA'], array('01', '91')) &&
                ($Proric_rec['RICRPA'] == '' || $Proric_rec['RICPC'] == "1") &&
                $procedimentoIntegrazione &&
                ($vediIntAcl || $vediIntEsibente) &&
                $bloccaIntegrazioni === false &&
                !$hideIcon
        ) {
            $collegamentoIntegrazione = ItaUrlUtil::GetPageUrl(array(
                        'p' => $extraParams['config']['online_page'],
                        'event' => 'openBlock',
                        'procedi' => $procedimentoIntegrazione,
                        'padre' => $padre,
                        'tipo' => 'integrazione',
                        'subproc' => $Iteevt_rec_int['IEVCOD'],
                        'subprocid' => $Iteevt_rec_int['ROWID']
            ));
        }

        $Propas_count = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT COUNT(*) AS C FROM PROPAS WHERE PRONUM = '{$Proric_rec['GESNUM']}' AND PROPUBALL = 1 ORDER BY PROSEQ", false);

        if ($Propas_count['C']) {
            $collegamentoAllegati = ItaUrlUtil::GetPageUrl(array(
                        'p' => $extraParams['config']['attachment_page'],
                        'event' => 'ricerca',
                        'parent' => 'ricerca',
                        'ricnum' => $Proric_rec['RICNUM'],
                        'gesnum' => $Proric_rec['GESNUM']
            ));
        }

        /*
         * Utente CMS
         */
        $Ricdag_rec = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT DAGVAL FROM RICDAG WHERE DAGNUM = '{$Proric_rec['RICNUM']}' AND DAGKEY = 'ESIBENTE_CMSUSER'", false);
        if ($Ricdag_rec) {
            $utenteCMS = $Ricdag_rec['DAGVAL'];
        }

        /*
         * Sportello aggregato
         */
        if ($Proric_rec['RICSPA']) {
            $Anaspa_rec = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT SPADES FROM ANASPA WHERE SPACOD = '{$Proric_rec['RICSPA']}'", false);
            if ($Anaspa_rec) {
                $sportelloAggregato = $Anaspa_rec['SPADES'];
            }
        }

        /*
         * Richieste accorpate
         */
        $richiesteAccorpate = array();
        if (!$Proric_rec['RICRUN']) {
            foreach ($this->praLib->GetRichiesteAccorpate($extraParams['PRAM_DB'], $Proric_rec['RICNUM']) as $proric_acc_rec) {
                $richiesteAccorpate[] = intval(substr($proric_acc_rec['RICNUM'], 4, 6)) . '/' . substr($proric_acc_rec['RICNUM'], 0, 4);
            }
        }

        /*
         * Ritorno i dati elaborati.
         */
        $arrParam = array('ricnum' => $Proric_rec['RICNUM']);
        return array(
            'NUMERO_RICHIESTA' => $numeroRichiesta,
            'RIFERIMENTO_RICHIESTA' => $riferimentoRichiesta,
            'PRATICA_PADRE' => $praticaPadre,
            'NUMERO_PROTOCOLLO' => $numeroProtocollo,
            'DATA_PROTOCOLLO' => $dataProtocollo,
            'SETTORE' => $Anaset_rec['SETDES'],
            'ATTIVITA' => $Anaatt_rec['ATTDES'],
            'SPORTELLO_AGGREGATO' => $sportelloAggregato,
            'NUMERO_PROCEDIMENTO' => $Proric_rec['RICPRO'],
            'OGGETTO' => $this->praLibEventi->getOggettoProric($extraParams['PRAM_DB'], $Proric_rec),
            'NOMINATIVO' => $nominativo,
            'DATA_RICHIESTA' => frontOfficeLib::convertiData($Proric_rec['RICDRE']),
            'STATO_INOLTRO' => $statoInoltro,
            'DATA_INOLTRO' => $dataInoltro,
            'ANNULLA_COMUNICA' => $annullaComunica,
            'STATO_PRATICA' => $statoPratica,
            'STATO_PRATICA_PROTOCOLLO' => $statoPraticaProtocollo,
            'DATA_ACQUISIZIONE' => $dataAcquisizione,
            'DATA_CHIUSURA' => $dataChiusura,
            'COLLEGAMENTO_RICHIESTA' => $collegamentoRichiesta,
            'COLLEGAMENTO_ANNULLAMENTO' => $collegamentoAnnullamento,
            'COLLEGAMENTO_INTEGRAZIONE' => $collegamentoIntegrazione,
            'COLLEGAMENTO_ALLEGATI' => $collegamentoAllegati,
            'DATI_IMPRESA' => $datiImpresa,
            'UTENTE_CMS' => $utenteCMS,
            'RICHIESTE_ACCORPATE' => $richiesteAccorpate,
            'SOGGETTI' => $this->praLib->GetRicsoggetti($arrParam, 'codice', $extraParams['PRAM_DB'], true),
            'ACLSOGGETTO' => $ricacl_tab
        );
    }

    public function elaboraRecordsProric($Proric_tab, $extraParams) {
        $procedimentoIntegrazione = $this->praLib->GetProcedimentoIntegrazione($extraParams['PRAM_DB']);
        $Iteevt_rec_int = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT * FROM ITEEVT WHERE ITEPRA = '$procedimentoIntegrazione'", false);
        $anaparBlkIntegra_rec = $this->praLib->GetAnapar('BLOCK_INTEGRAZIONI', 'parkey', $extraParams['PRAM_DB'], false);
        
        $returnData = array();

        foreach ($Proric_tab as $Proric_rec) {
            $datiRiga = $this->getDatiRiga($Proric_rec, $extraParams, $procedimentoIntegrazione, $Iteevt_rec_int, $anaparBlkIntegra_rec['PARVAL']);
            $tableRow = array();
            switch ($extraParams['config']['view']) {
                default:
                case 0:
                    $tableRow = $this->elaboraRecordProricTabella($Proric_rec, $datiRiga, $extraParams);
                    break;

                case 2:
                    $textProcedimento = "<b>{$datiRiga['NUMERO_PROCEDIMENTO']}</b> - {$datiRiga['OGGETTO']}";
                    if ($datiRiga['ATTIVITA']) {
                        $textProcedimento = "{$datiRiga['ATTIVITA']}<br />" . $textProcedimento;
                    }
                    if ($datiRiga['SETTORE']) {
                        $textProcedimento = "{$datiRiga['SETTORE']}<br />" . $textProcedimento;
                    }

                    $tableRow[] = "<div><a href=\"{$datiRiga['COLLEGAMENTO_RICHIESTA']}\">" . $textProcedimento . '</a></div>';
                    $tableRow[] = "<div data-sortValue=\"{$Proric_rec['RICDRE']}\">" . $datiRiga['DATA_RICHIESTA'] . '<br />' . $Proric_rec['RICORE'] . '</div>';
                    break;
            }

            $returnData[] = $tableRow;
        }

        return $returnData;
    }

    public function elaboraRecordProricTabella($Proric_rec, $datiRiga, $extraParams) {
        $isCPortal = $extraParams['modo'] == 'cportal';

//        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';
//        $praLib = new praLib();
//        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibDati.class.php';
//        $praLibDati = praLibDati::getInstance($praLib);
//        $dati = $praLibDati->prendiDati($Proric_rec['RICNUM'], '', '', true);


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

        if (count($datiRiga['RICHIESTE_ACCORPATE'])) {
            $titleAccorpate = implode('<br>', $datiRiga['RICHIESTE_ACCORPATE']);
            $textRiferimento .= "<br /><small class=\"italsoft-tooltip\" style=\"text-decoration: underline dotted;\" title=\"$titleAccorpate\">+" . count($datiRiga['RICHIESTE_ACCORPATE']) . " accorpat" . ($datiRiga['RICHIESTE_ACCORPATE'] === 1 ? 'a' : 'e') . "</small>";
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

        $rigaAnnullamento = $rigaIntegrazione = $rigaAllegati = '&ndash;';
        $descrizioneIntegrazione = 'Avvia procedura di integrazione';
        if ($extraParams['HideAnnullaIcon'] !== true) {
            if ($datiRiga['COLLEGAMENTO_ANNULLAMENTO']) {
                $rigaAnnullamento = $this->html->getImage(frontOfficeLib::getIcon('email-open'), '24px', 'Avvia procedura di annullamento', $datiRiga['COLLEGAMENTO_ANNULLAMENTO']);
            }
        } else {
            $descrizioneIntegrazione .= ' o annullamento';
        }

        if ($datiRiga['COLLEGAMENTO_INTEGRAZIONE']) {
            $rigaIntegrazione = $this->html->getImage(frontOfficeLib::getIcon('integra'), '24px', $descrizioneIntegrazione, $datiRiga['COLLEGAMENTO_INTEGRAZIONE']);
        }

        if ($datiRiga['COLLEGAMENTO_ALLEGATI']) {
            $rigaAllegati = $this->html->getImage(frontOfficeLib::getIcon('paperclip'), '24px', 'Vedi allegati pubblicati', $datiRiga['COLLEGAMENTO_ALLEGATI']);
        }

        $tableRow[] = "<div data-sortValue=\"{$Proric_rec['RICNUM']}\"><a href=\"{$datiRiga['COLLEGAMENTO_RICHIESTA']}\">{$datiRiga['NUMERO_RICHIESTA']}</a>$textRiferimento</div>";

        if (!$isCPortal) {
            $tableRow[] = "<div data-sortValue=\"{$Proric_rec['RICNPR']}\">{$datiRiga['NUMERO_PROTOCOLLO']}<br />{$datiRiga['DATA_PROTOCOLLO']}</div>";
        }

        /*
         * Creazione html cambio esibente solo in caso in cui l'utente loggato sia un DICHIARANTE e se abilitata la gestione
         */
        $datiUtente = frontOfficeApp::$cmsHost->getDatiUtente();
        $praLibAcl = new praLibAcl();
        if ($praLibAcl->isEnableACLButton('ACL_CAMBIO_ESIBENTE', $Proric_rec['RICNUM'])) {
//        if ($praLibAcl->isEnableACLButton_lento('ACL_CAMBIO_ESIBENTE', $dati)) {
            foreach ($datiRiga['SOGGETTI'] as $soggetto) {
                //if ($soggetto['SOGRICRUOLO'] == praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD'] && $soggetto['SOGRICFIS'] == $datiUtente['fiscale']) {
                if ($soggetto['SOGRICRUOLO'] != praRuolo::$SISTEM_SUBJECT_ROLES['ESIBENTE']['RUOCOD'] && strtoupper($soggetto['SOGRICFIS']) == strtoupper($datiUtente['fiscale'])) {
                    $cambiaEsibente = $this->getHtmlCambiaEsibente($Proric_rec, $soggetto['SOGRICRUOLO']);
                }
            }
        }

        
        /**
         * Se non è scaduta si gestisce bottone "Condividi" e bottone "Passi Assegnati"
         */
        if (! $this->praLib->isRichiestaScaduta($Proric_rec)){        
            if ($praLibAcl->isEnableACLButton('ACL_INTEGRAZIONE', $Proric_rec['RICNUM']) ||
                    $praLibAcl->isEnableACLButton('ACL_VISIBILITA', $Proric_rec['RICNUM']) ||
                    ($Proric_rec['RICSTA'] == '99' && $praLibAcl->isEnableACLButton('ACL_GESTIONE_PASSO', $Proric_rec['RICNUM']) )
            ) {

                if ($datiUtente['fiscale'] == $Proric_rec['RICFIS']) {
                    $gestACL = $this->getHtmlGestACL($Proric_rec);
                }
            }

            /*
             * Creazione html per elenco passi disponibili per l'utente
             */
            $htmlPassiDisponibili = "";
            if ($praLibAcl->isEnableACLButton('ACL_GESTIONE_PASSO', $Proric_rec['RICNUM'])) {
    //        if ($praLibAcl->isEnableACLButton_lento('ACL_GESTIONE_PASSO', $dati)) {
                foreach ($datiRiga['ACLSOGGETTO'] as $acl_rec) {
                    if ($acl_rec['RICACLMETA']) {
                        $arrAcl = json_decode($acl_rec['RICACLMETA'], true);
                        if (is_array($arrAcl)) {
                            foreach ($arrAcl['AUTORIZZAZIONE'] as $autorizzazione) {
                                if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_PASSO') {
                                    $htmlPassiDisponibili = $this->getHtmlPassiModificabili($Proric_rec);
                                    break 2;
                                }
                            }
                        }
                    }
                }
            }
            
            
        }


        /*
         * Attivata la gestione della scadenza
         */
        $dataScad = $this->praLib->getDataScadenza($Proric_rec);
//        if ($this->praLib->isEnableScadenza() && $Proric_rec['RICSTA'] == 99){
        if ($dataScad && $Proric_rec['RICFORZAINVIO'] == 0){
            $htmlScadenza = $this->getHtmlScadenzaRichiesta($dataScad);
        }
        
        
        $tableRow[] = "<div $cellStyle><a href=\"{$datiRiga['COLLEGAMENTO_RICHIESTA']}\" $cellStyle>" . $textProcedimento . '</a></div>' . $gestACL . $cambiaEsibente . $htmlPassiDisponibili;
        $tableRow[] = "<div $cellStyle>" . $datiRiga['NOMINATIVO'] . '</div>';
        $tableRow[] = "<div $cellStyle data-sortValue=\"{$Proric_rec['RICDRE']}\">" . $datiRiga['DATA_RICHIESTA'] . '<br />' . $Proric_rec['RICORE'] . '</div>';
        $tableRow[] = "<div $cellStyle data-sortValue=\"{$Proric_rec['RICSTA']}\">" . $datiRiga['STATO_INOLTRO'] . $textInoltro . $datiRiga['ANNULLA_COMUNICA'] . '</div>';
        $tableRow[] = "<div $cellStyle>" . $datiRiga['STATO_PRATICA'] . $textProtocollo . $htmlScadenza. '</div>';

        if (!$isCPortal) {
            if ($extraParams['HideAnnullaIcon'] !== true) {
                $tableRow[] = '<div class="align-center">' . $rigaAnnullamento . '</div>';
            }

            if ($extraParams['config']['integrazione'] == 1) {
                $tableRow[] = '<div class="align-center">' . $rigaIntegrazione . '</div>';
            }

            $tableRow[] = '<div class="align-center">' . $rigaAllegati . '</div>';
        }

        return $tableRow;
    }

    public function getTableHeaders($dati, $extraParams) {
        $isCPortal = $extraParams['modo'] == 'cportal';

        $tableHeaders = array();
        $tableHeaders[] = 'Numero<br />Richiesta';

        if (!$isCPortal) {
            $tableHeaders[] = 'N./Data<br />Protocollo';
        }

        $tableHeaders[] = array('text' => 'Procedimento', 'attrs' => array('data-sorter' => 'false'));
        $tableHeaders[] = $isCPortal ? 'Iscrivendo' : 'Dati<br />Impresa';
        $tableHeaders[] = 'Inizio<br />del';
        $tableHeaders[] = 'Stato<br />Inoltro';
        $tableHeaders[] = 'Stato<br />Pratica';

        if (!$isCPortal) {
            if ($extraParams['HideAnnullaIcon'] !== true) {
                $tableHeaders[] = array('text' => 'Annulla', 'attrs' => array('data-sorter' => 'false'));

                if ($extraParams['config']['integrazione'] == 1) {
                    $tableHeaders[] = array('text' => 'Integra', 'attrs' => array('data-sorter' => 'false'));
                }

                $tableHeaders[] = array('text' => 'Allegati<br />Pubblicati', 'attrs' => array('data-sorter' => 'false'));
            } else {
                if ($extraParams['config']['integrazione'] == 1) {
                    $tableHeaders[] = array('text' => 'Integra/Annulla', 'attrs' => array('data-sorter' => 'false'));
                }

                $tableHeaders[] = array('text' => 'Allegati<br />Pubblicati', 'attrs' => array('data-sorter' => 'false'));
            }
        }

        return $tableHeaders;
    }

    public function getTableData($dati, $extraParams) {
        $orderBy = false;

        if (isset($extraParams['column'])) {
            foreach ($extraParams['column'] as $col => $type) {
                switch ((int) $col) {
                    case 0:
                        $orderBy = 'RICNUM';
                        break;

                    case 1:
                        $orderBy = 'RICNPR';
                        break;

                    case 4:
                        $orderBy = 'RICDRE';
                        break;

                    case 5:
                        $orderBy = 'RICSTA';
                        break;
                }

                if ($orderBy) {
                    $orderBy .= ' ' . ((int) $type === 0 ? 'ASC' : 'DESC');
                }
            }
        }

        $sql = $this->getSql($dati, $extraParams, $orderBy);

        $Proric_tab = $this->getProric_tab($sql, $extraParams);
        $totalRows = count($Proric_tab);
        $Proric_tab = array_slice($Proric_tab, $extraParams['page'] * $extraParams['size'], $extraParams['size']);
        $tableBody = $this->elaboraRecordsProric($Proric_tab, $extraParams);

        return array($totalRows, $tableBody);
    }

    private function getHtmlCambiaEsibente($proric_rec, $ruolo) {
        $icon = "";
        $msg = "ESIBENTE: " . $proric_rec['RICCOG'] . " " . $proric_rec['RICNOM'] . "<br>" . $proric_rec['RICFIS'];
        $href = "<div style=\"border: 1px solid grey;width:100%;\" class=\"italsoft-button italsoft-button--secondary\"><div style=\"display:inline-block;vertical-align:middle;\">$msg</div></div>";
        if ($ruolo == praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD'] && ($proric_rec['RICSTA'] == '01' || $proric_rec['RICSTA'] == '91')) {
//            $icon = "<div style=\"margin-right:10px;display:inline-block;vertical-align:middle;\"><i style=\"color:white;font-size: 25px;\" class=\"icon ion-key italsoft-icon\"></i></div>";
            $icon = "<div style=\"margin-right:10px;float:left;vertical-align:middle;\"><i style=\"color:white;font-size: 25px;\" class=\"icon ion-key italsoft-icon\"></i></div>";
            $href = "<a href=\"\" style=\"width:100%;\" class=\"italsoft-button--primary italsoft-button\" onclick=\"itaFrontOffice.ajax(ajax.action, ajax.model, 'disegnaCambiaEsibente', this, {'event':'disegnaCambiaEsibente','ricnum':'" . $proric_rec['RICNUM'] . "','ruolo':'" . praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD'] . "'}); event.preventDefault(); \">
                        $icon
                        $msg
                     </a>";
//            $html = new html();
//            $href = $html->getButton($icon . "<div style=\"display:inline-block;vertical-align:middle;\" class=\"italsoft-tooltip\" title=\"Clicca per cambiare l'esibente\">$msg</div>", "", "primary", array(
//                'event' => 'disegnaCambiaEsibente',
//                'ricnum' => $proric_rec['RICNUM'],
//                'ruolo' => praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD'],
//            ));
        }
        return "<br><div>$href</div>";
    }

    private function getHtmlPassiModificabili($proric_rec) {
        $icon = "";
        $msg = "<div style=\"display:inline-block;vertical-align:middle;\" class=\"italsoft-tooltip\" title=\"Clicca per visualizzare l'elenco dei passi assegnati.\">Passi Assegnati</div>";
        if ($proric_rec['RICSTA'] == '99') {
            $icon = "<div style=\"padding-right:10px;float:left;vertical-align:middle;\"><i style=\"color:white;font-size: 25px;\" class=\"icon ion-folder italsoft-icon\"></i></div>";
            $href = "<a href=\"\" style=\"width:100%;\" class=\"italsoft-button--primary italsoft-button\" onclick=\"itaFrontOffice.ajax(ajax.action, ajax.model, 'disegnaPassiDisponibili', this, {'event':'disegnaPassiDisponibili','ricnum':'" . $proric_rec['RICNUM'] . "'}); event.preventDefault(); \">
                        $icon
                        $msg
                     </a>";
            return "<br><div>$href</div>";
        }
        return "";
    }

    private function getHtmlGestACL($proric_rec) {
//        $icon = "";
        $msg = "<div style=\"display:inline-block;vertical-align:middle;\" class=\"italsoft-tooltip\" title=\"Clicca per gestire la condivisione.\">Condividi</div>";
//        if ($proric_rec['RICSTA'] == '99') {
//        $icon = "<div style=\"padding-right:10px;display:inline-block;vertical-align:middle;\"><i style=\"color:white;font-size: 25px;\" class=\"icon ion-person-add italsoft-icon\"></i></div>";
        $icon = "<div style=\"padding-right:10px;float:left;vertical-align:middle;\"><i style=\"color:white;font-size: 25px;\" class=\"icon ion-person-add italsoft-icon\"></i></div>";
        $href = "<a href=\"\" style=\"width:100%;\" class=\"italsoft-button--primary italsoft-button\" onclick=\"itaFrontOffice.ajax(ajax.action, ajax.model, 'gestACL', this, {'event':'gestACL','ricnum':'" . $proric_rec['RICNUM'] . "'}); event.preventDefault(); \">
                        $icon
                        $msg
                     </a>";
        return "<br><div>$href</div>";
//        }
//        return "";
    }

    private function getHtmlScadenzaRichiesta($dataScad) {
        $msg = "<div style=\"display:inline-block;vertical-align:middle;color: white;\" class=\"italsoft-tooltip\" title=\"Scadenza.\">Termine ultimo per l'invio il <br>" . frontOfficeLib::convertiData($dataScad) . "</div>";
        $icon = "<div style=\"padding-right:10px;vertical-align:middle;color: white;\"><i style=\"font-size: 25px;\" class=\"icon ion-clock\"></i></div>";
        $color = "green";
//        $color = "#86ef86";
        
        if ($dataScad < date('Ymd')){
            $color = "red";
//            $color = "#f13a3a";
            $msg = "<div style=\"display:inline-block;vertical-align:middle;color: white;\" class=\"italsoft-tooltip\" title=\"Scadenza.\">Termine ultimo per l'invio scaduto il <br>" . frontOfficeLib::convertiData($dataScad) . "</div>";
        }

        $div = "<div style=\"border: 1px solid grey;background-color: " . $color . ";width:100%;\" class=\"italsoft-button italsoft-button--secondary\"><div style=\"display:inline-block;vertical-align:middle;\">"
                . $icon 
                . $msg
                . "</div></div>";
        
        return "<br><div>$div</div>";
    }
    
    private function getProric_tab($sql, $extraParams){
        $Proric_tab = ItaDB::DBSQLSelect($extraParams["PRAM_DB"], $sql, true);

        if ($extraParams['Tipo'] == '99A' || $extraParams['Tipo'] == '99S'){
            foreach ($Proric_tab as $key => $Proric_rec) {

                if ($extraParams['Tipo'] == '99A'){
                    if ($this->praLib->isRichiestaScaduta($Proric_rec)){
                        unset($Proric_tab[$key]);
                        continue;
                        
                    }
                }
                else if ($extraParams['Tipo'] == '99S'){
                    if (! $this->praLib->isRichiestaScaduta($Proric_rec)){
                        unset($Proric_tab[$key]);
                        continue;
                    }
                }
                
//                /**
//                 * Trovo la scadenza configurata
//                 */
//                $dataScad = $this->praLib->getDataScadenza($Proric_rec);
//                if ($dataScad){
//                    if ($extraParams['Tipo'] == '99A'){
//                        /**
//                         * Se scaduto e non impostato RICFORZAINVIO 
//                         */
//                        if ($dataScad < date('Ymd') && $Proric_rec['RICFORZAINVIO'] == 0){
//                            unset($Proric_tab[$key]);
//                            continue;
//                        }
//                    }
//                    else if ($extraParams['Tipo'] == '99S'){
//                        if ($dataScad >= date('Ymd') || $Proric_rec['RICFORZAINVIO'] == 1){
//                            unset($Proric_tab[$key]);
//                            continue;
//                        }
//                    }
//                }

            }

        }
        
        return $Proric_tab;
    }


    
}
