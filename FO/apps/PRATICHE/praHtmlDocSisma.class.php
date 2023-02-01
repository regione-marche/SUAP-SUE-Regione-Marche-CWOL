<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibDomus.class.php';

class praHtmlDocSisma {

    protected $html;
    protected $praLib;
    protected $errCode;
    protected $errMessage;

    public function __construct() {
        $this->html = new html();
        $this->praLib = new praLib();
    }

    function getErrCode() {
        return $this->errCode;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function DisegnaPagina($dati, $extraParams = array()) {

        $datiAggiuntiviSelect = array(
            'COMUNEDESTINATARIO',
            'INTER_LOCALITA',
            'INTER_VIA',
            'INTER_CIV',
            'INTER_CAP',
            'TIPO_INTERVENTO'
        );

        $Ricdag_ric_tab = ItaDB::DBSQLSelect($extraParams['PRAM_DB'], "SELECT DAGKEY, RICDAT FROM RICDAG WHERE DAGNUM = '{$dati['ricnum']}' AND DAGKEY IN ('" . implode("', '", $datiAggiuntiviSelect) . "')");
        foreach ($Ricdag_ric_tab as $Ricdag_ric_rec) {
            $datiAggiuntivi[$Ricdag_ric_rec['DAGKEY']] = htmlspecialchars($Ricdag_ric_rec['RICDAT'], ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
        }

        $praLibDomus = new praLibDomus();

        /*
         * LEggo i Protocolli della Richiesta
         */
        $infoPratica = $praLibDomus->getDocumentiFascicolo($dati['ricnum']);
        if (!$infoPratica) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore Lettura Documenti Fascicolo: " . $praLibDomus->getErrMessage());
            return false;
        }

        /*
         * Record Richiesta
         */
        $Proric_rec = $this->praLib->GetProric($dati['ricnum'], 'codice', $extraParams['PRAM_DB']);

        /*
         * Record Procedimento
         */
        $Anapra_rec = $this->praLib->GetAnapra($Proric_rec['RICPRO'], 'codice', $extraParams['PRAM_DB']);

        $TIPOLOGIA_INTERVENTO = $datiAggiuntivi['TIPO_INTERVENTO'] === 'a' ? 'Autorizzazione sismica' : $datiAggiuntivi['TIPO_INTERVENTO'] === 'b' ? 'Deposito' : '';
        if ($Proric_rec['RICNPR'] != 0) {
            $numeroProtocollo = substr($Proric_rec['RICNPR'], 4) . '/' . substr($Proric_rec['RICNPR'], 0, 4);
            $dataProtocollo = frontOfficeLib::convertiData($Proric_rec['RICDPR']);
        }
        $codiceDatiImpresa = $Proric_rec['RICRPA'] ?: $Proric_rec['RICNUM'];
        $datiImpresa = $this->GetDatiImpresa($codiceDatiImpresa, $extraParams['PRAM_DB']);
        $nominativo = $datiImpresa['DENOMIMPRESA'] . ' C.F.: ' . $datiImpresa['FISCALE'];
        $comuneDestinatario = $datiAggiuntivi['COMUNEDESTINATARIO'] . ' ' . $datiAggiuntivi['INTER_CAP'];
        $textUbicazione = '';

        if ($datiAggiuntivi['INTER_LOCALITA']) {
            $textUbicazione .= $datiAggiuntivi['INTER_LOCALITA'] . ' ';
            $textUbicazione .= $datiAggiuntivi['INTER_VIA'] . ' ';
            $textUbicazione .= $datiAggiuntivi['INTER_CIV'];
        }
        if ($Proric_rec['RICCONFDATA']) {
            $dataAcquisizione = frontOfficeLib::convertiData($Proric_rec['RICCONFDATA']);
            $statoPratica = "Acquisita dall'ente il $dataAcquisizione";
        }
        $statoPratica .= ' con stato ' . $infoPratica['Stato'];

        output::appendHtml("<div style=\"font-size:1.3em;text-decoration:underline;\"><b>RIEPILOGO RICHIESTA ON-LINE</b></div><br>");
        output::appendHtml("<div style=\"font-size:1.2em;\"><span style=\"display:inline-block;text-align:right;width:180px;\"> <b>Numero Progetto :  </b></span> {$infoPratica['NumeroRichiestaInterno']}</div>");
        if ($TIPOLOGIA_INTERVENTO) {
            output::appendHtml("<div style=\"font-size:1.2em;\"><span style=\"display:inline-block;text-align:right;width:180px;\"> <b>Tipo Procedimento :  </b></span> $TIPOLOGIA_INTERVENTO</div>");
        }
        output::appendHtml("<div style=\"font-size:1.2em;\"><span style=\"display:inline-block;text-align:right;width:180px;\"> <b>Fascicolo :  </b></span> {$infoPratica['CodiceFascicolo']}</div>");
        output::appendHtml("<div style=\"font-size:1.2em;\"><span style=\"display:inline-block;text-align:right;width:180px;\"> <b>Protocollo e Data :  </b></span> $numeroProtocollo $dataProtocollo</div>");
        output::appendHtml("<div style=\"font-size:1.2em;\"><span style=\"display:inline-block;text-align:right;width:180px;\"> <b>Committente :  </b></span> $nominativo</div>");
        output::appendHtml("<div style=\"font-size:1.2em;\"><span style=\"display:inline-block;text-align:right;width:180px;\"> <b>Comune :  </b></span> $comuneDestinatario</div>");
        output::appendHtml("<div style=\"font-size:1.2em;\"><span style=\"display:inline-block;text-align:right;width:180px;\"> <b>Indirizzo :  </b></span> $textUbicazione</div>");
        output::appendHtml("<div style=\"font-size:1.2em;\"><span style=\"display:inline-block;text-align:right;width:180px;\"> <b>Stato Pratica :  </b></span> $statoPratica </div>");

//        output::appendHtml("<div style=\"font-size:1.2em;\"><b>Numero / Anno:</b> " . substr($infoPratica['NumeroRichiesta'], 4) . "/" . substr($infoPratica['NumeroRichiesta'], 0, 4) . "</div>");
//        output::appendHtml("<div style=\"font-size:1.2em;\"><b>Descrizione:</b> " . $Anapra_rec['PRADES__1'] . $Anapra_rec['PRADES__2'] . $Anapra_rec['PRADES__3'] . "</div>");
//        output::appendHtml("<div style=\"font-size:1.2em;\"><b>Comune:</b> " . $infoPratica['DescrizioneComune'] . "</div>");
        $variante = 'No';
        if ($infoPratica['IsVariante']) {
            $variante = 'Si';
        }
        output::appendHtml("<div style=\"font-size:1.2em;\"><b><span style=\"display:inline-block;text-align:right;width:180px;\"> Variante :  </b></span> $variante</div>");
        output::appendHtml("<div style=\"font-size:1.2em;\"><b><span style=\"display:inline-block;text-align:right;width:180px;\"> Stato :  </b></span> " . $infoPratica['Stato'] . "</div>");
        output::appendHtml("<br>");

        if ($infoPratica['Protocolli']) {
            $tableData = array(
                'header' => array(
                    array('text' => 'Genere<br /> Validità', 'attrs' => array('width' => '5%')),
                    array('text' => 'Segnatura', 'attrs' => array('width' => '25%', 'data-sorter' => 'false')),
                    array('text' => 'Tipo Documento', 'attrs' => array('width' => '15%', 'data-sorter' => 'false')),
                    array('text' => 'Documenti', 'attrs' => array('width' => '15%', 'data-sorter' => 'false')),
                ),
                'body' => array(),
                'style' => array(
                    'body' => array('text-align: center;', '', '', '', '', '', 'word-break: break-all;', 'text-align: center;', 'text-align: center;')
                )
            );

            $arrProtocolli = array();
            if (!isset($infoPratica['Protocolli']['ProtocolloPratica'][0])) {
                $arrProtocolli[] = $infoPratica['Protocolli']['ProtocolloPratica'];
            }else{
                $arrProtocolli = $infoPratica['Protocolli']['ProtocolloPratica'];
            }
            foreach ($arrProtocolli as $protocollo) {

                /*
                 * Per ogni protocollo leggo gli allegati
                 */
                $getAlleagtiPrt = $praLibDomus->getDocumentiProtocollo($protocollo['DocNumber'], $infoPratica['IstatComune']);
                if (!$getAlleagtiPrt) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore Lettura Allegati : " . $praLibDomus->getErrMessage());
                    return false;
                }

                $tableRow = array();
                $tableRow[] = "<div style=\"\">{$protocollo['GenereValidita']}</div>";
                $tableRow[] = "<div style=\"\">{$protocollo['Segnatura']}</div>";
                $tableRow[] = "<div style=\"\">{$protocollo['TipoDocumento']}</div>";

                /*
                 * Se ci sono gli allegati, Attivo la scritta Scarica Allegati
                 */
                $desc_node = "Non ci sono Allegati";
                if ($getAlleagtiPrt['DocumentoProtocollo']) {
                    $hrefScaricaAllegati = ItaUrlUtil::GetPageUrl(array(
                                'event' => 'dettaglio',
                                //'parent' => 'ricerca',
                                'docnumber' => $protocollo['DocNumber'],
                                'istat' => $infoPratica['IstatComune'],
                    ));
                    $imgZip = "<div style=\"display:inline-block;\"><img src=\"" . frontOfficeLib::getIcon('file-archive') . "\" width=\"24\" height=\"24\" style = \"border:0px;vertical-align:middle;margin-right:5px;\"></img></div>";
                    $msg = "<div style=\"display:inline-block;vertical-align:middle;font-size:1.1em;\">Scarica Documenti</div>";
                    $desc_node = "<a href=\"$hrefScaricaAllegati\">$imgZip$msg</a>";
                }
                $tableRow[] = $desc_node;

                $tableData['body'][] = $tableRow;
            }
            output::addTable($tableData, array('sortable' => true, 'paginated' => true));

            output::appendHtml("<div style=\"text-align:center;\">");
            output::appendHtml("<div style=\"margin:10px;display:inline-block;\">
                                 <button style=\"cursor:pointer;\" name=\"tornaElenco\" class=\"italsoft-button\" type=\"button\" onClick=\"javascript:history.back()\">
                                    <i class=\"icon ion-arrow-return-left italsoft-icon\"></i>
                                    <div class=\"\" style=\"display:inline-block;\"><b>Torna Elenco</b></div>
                                </button>
                          </div>");
            output::appendHtml("</div>");
        }
        return true;
    }

    public function Dettaglio($dati, $extraParams = array()) {
        $praLibDomus = new praLibDomus();

        /*
         * Prendo gli allegati del protocollo
         */
        $getAllegatiPrt = $praLibDomus->getDocumentiProtocollo($dati['docnumber'], $dati['istat']);
        if (!$getAllegatiPrt) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore Lettura Allegati : " . $praLibDomus->getErrMessage());
            return false;
        }

        /*
         * Se il file zip esiste, lo cancello
         */
        $fileZip = ITA_FRONTOFFICE_TEMP . "allegati_protocollo_" . $dati['docnumber'] . ".zip";
        if (file_exists($fileZip)) {
            if (!@unlink($fileZip)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore cancellazione file zip precedente: $fileZip");
                return false;
            }
        }

        /*
         * Creo il file zip
         */
        $archiv = new ZipArchive();
        if (!$archiv->open($fileZip, ZipArchive::CREATE)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore creazione file zip: $fileZip");
            return false;
        }

        /*
         * Normalizzi array Allegati in caso di uno o più allegati
         */
        $allegati = array();
        if (!isset($getAllegatiPrt['DocumentoProtocollo'][0])) {
            $allegati[0]['File'] = $getAllegatiPrt['DocumentoProtocollo']['File'];
            $allegati[0]['IsPrincipale'] = $getAllegatiPrt['DocumentoProtocollo']['IsPrincipale'];
        } else {
            $allegati = $getAllegatiPrt['DocumentoProtocollo'];
        }

        /*
         * Mi scorro gli allegati e li aggiungo all'archivo zip
         */
        foreach ($allegati as $allegato) {
            $streamDecode = base64_decode($allegato['File']['Stream']);
            $nomeFile = $allegato['File']['Nome'] . "." . $allegato['File']['Estensione'];
            if (!file_put_contents(ITA_FRONTOFFICE_TEMP . $nomeFile, $streamDecode)) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore download allegato: $nomeFile");
                return false;
            }
            $archiv->addFile(ITA_FRONTOFFICE_TEMP . $nomeFile, $nomeFile);
        }
        $archiv->close();

        /*
         * Eseguo il downlaod el file
         */
        $frontOfficeLib = new frontOfficeLib();
        $frontOfficeLib->scaricaFile($fileZip, $fileZip, false);

        /*
         * Cancello lo zip dopo il download
         */
        if (!@unlink($fileZip)) {
            $this->setErrCode(-1);
            $this->setErrMessage("Errore cancellazione file zip: $fileZip");
            return false;
        }
        exit();
        return true;
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

//    public function GestioneAllegato($dati, $extraParams = array()) {
//        
//    }
}
