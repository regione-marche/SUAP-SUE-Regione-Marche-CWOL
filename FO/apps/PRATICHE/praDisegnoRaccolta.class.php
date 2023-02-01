<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praDisegnoDati.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibCustomClass.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibSostituzioni.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibTemplate.class.php';

class praDisegnoRaccolta {

    private $praLib;
    private $praLibSostituzioni;
    private $column = false;
    private $contaColumn;
    private $requiredLabel = '*';
    public function __construct() {
        $this->praLib = new praLib();
        $this->praLibSostituzioni = new praLibSostituzioni();
    }

    public function getDisegnoRaccoltaRichiesta($dati, $arrayValori = array(), $arrayDefaults = array()) {
        $dati = praDisegnoDati::prendiDatiDaRichiesta($dati);
        return $this->getDisegnoRaccolta($dati, $arrayValori, $arrayDefaults);
    }

    public function getDisegnoRaccoltaProcedimento($itepas_rec, $itedag_tab, $arrayValori = array(), $arrayDefaults = array()) {
        $dati = praDisegnoDati::prendiDatiDaProcedimento($itepas_rec, $itedag_tab);
        return $this->getDisegnoRaccolta($dati, $arrayValori, $arrayDefaults);
    }

    public function getDisegnoRaccoltaXMLProcedimento($xmlPath, $arrayValori = array(), $arrayDefaults = array()) {
        $dati = praDisegnoDati::prendiDatiDaXMLProcedimento($xmlPath);
        return $this->getDisegnoRaccolta($dati, $arrayValori, $arrayDefaults);
    }

    private function assegnaValoriDatiAggiuntivi($dati, $arrayValori = array(), $arrayDefaults = array()) {
        foreach ($dati['Ricdag_tab'] as $k => $Ricdag_rec) {
            if (isset($arrayValori[$Ricdag_rec['DAGKEY']])) {
                $dati['Ricdag_tab'][$k]['RICDAT'] = $arrayValori[$Ricdag_rec['DAGKEY']];
            }

            if (isset($arrayDefaults[$Ricdag_rec['DAGKEY']])) {
                $dati['Ricdag_tab'][$k]['DAGVAL'] = $arrayDefaults[$Ricdag_rec['DAGKEY']];
            }
        }

        return $dati;
    }

    public function getRequiredLabel() {
        return $this->requiredLabel;
    }

    public function setRequiredLabel($requiredLabel) {
        $this->requiredLabel = $requiredLabel;
    }

    /**
     * Effettua il disegno dei dati aggiuntivi.
     * 
     * @param type $dati
     * $dati = array(
     *     'Ricite_rec' => array(...),
     *     'Ricdag_tab' => array(...),
     *     'Dizionario' => itaDictionary,
     *     'ReadOnly' => (boolean),
     *     'PRAM_DB' => (ItaDB),
     *     'Prefix' => (string) 'raccolta',
     * // Per richieste online / tipizzati
     *     'Anapra_rec' => array(...),
     *     'Proric_rec' => array(...),
     *     'Praclt_rec' => array(...),
     *     'Anaspa_tab' => array(...),
     *     'countObl' => array(...),
     *     'countEsg' => array(...),
     *     'BandiFiere_tab' => array(...),
     *     'BandiFiereP_tab' => array(...),
     *     'BandiMercati_tab' => array(...),
     *     'BandiPosteggiIsolati_tab' => array(...)
     * )
     * @param array $arrayValori Array CHIAVE => VALORE per la valorizzazione dei campi
     * @param array $arrayDefaults Array CHIAVE => VALORE per i default value dei campi
     * @return string HTML
     */
    public function getDisegnoRaccolta($dati, $arrayValori = array(), $arrayDefaults = array()) {
        $dati = $this->assegnaValoriDatiAggiuntivi($dati, $arrayValori, $arrayDefaults);

        $html = new html();
        if (!$dati['Prefix']) {
            $dati['Prefix'] = 'raccolta';
        }

        /*
         * Metto l'html prima del div template se la sua posizione è Inizio
         */

        foreach ($dati['Ricdag_tab'] as $key => $Ricdag_rec) {
            $Ricdag_rec = $this->praLib->ctrRicdagRec($Ricdag_rec, $dati['Dizionario']->getAlldataPlain("", "."));
            $br = "";

            if ($Ricdag_rec['DAGACA'] == 1) {
                $br = "<br>";
            }

            $meta = unserialize($Ricdag_rec["DAGMETA"]);
            if ($Ricdag_rec['DAGDIZ'] == "H" && $meta['HTMLPOS'] == "Inizio") {
                $dictionaryValues_pre = $dati['Dizionario']->getAllData();
                $dictionaryValues = str_replace("\\n", chr(13), $dictionaryValues_pre);
                $template = $this->praLib->elaboraTabelleTemplate($Ricdag_rec["DAGVAL"], $dictionaryValues, true);
                $defaultValue = $this->praLib->valorizzaTemplate($template, $dictionaryValues);
                $html->appendHtml("<div class=\"ita-html-container\" id=\"{$Ricdag_rec['DAGKEY']}\">" . $defaultValue . "</div>");
                $html->appendHtml($br);
            }
        }

        if ($dati['Ricite_rec']['ITECOL'] != 0) {
            $this->column = $dati['Ricite_rec']['ITECOL'];
        }

        if ($this->column) {
            $html->appendHtml("<table class=\"tableAutocert\">");
        }

        $this->contaColumn = 0;

        foreach ($dati['Ricdag_tab'] as $key => $Ricdag_rec) {
            $this->getDisegnoDato($Ricdag_rec, $html, $dati);
        }

        /*
         * Se ci sono le colonne chiudo la tabella
         */

        if ($this->column) {
            if (($this->column - $this->contaColumn) && $this->contaColumn != 0) {
                $html->appendHtml(str_repeat("<td></td>", $this->column - $this->contaColumn));
                $html->appendHtml("<tr>");
            }
            $html->appendHtml("</table>");
        }

        /*
         * Metto l'html dopo del div template se la sua posizione è Fine
         */

        foreach ($dati['Ricdag_tab'] as $key => $Ricdag_rec) {
            $Ricdag_rec = $this->praLib->ctrRicdagRec($Ricdag_rec, $dati['Dizionario']->getAlldataPlain("", "."));
            $br = "";

            if ($Ricdag_rec['DAGACA'] == 1) {
                $br = "<br>";
            }

            $meta = unserialize($Ricdag_rec["DAGMETA"]);
            if ($Ricdag_rec['DAGDIZ'] == "H" && $meta['HTMLPOS'] == "Fine") {
                $dictionaryValues_pre = $dati['Dizionario']->getAllData();
                $dictionaryValues = str_replace("\\n", chr(13), $dictionaryValues_pre);
                $template = $this->praLib->elaboraTabelleTemplate($Ricdag_rec["DAGVAL"], $dictionaryValues, true);
                $defaultValue = $this->praLib->valorizzaTemplate($template, $dictionaryValues);
                $html->appendHtml("<div class=\"ita-html-container\" id=\"{$Ricdag_rec['DAGKEY']}\">" . $defaultValue . "</div>");
                $html->appendHtml($br);
            }
        }

        return $html->getHtml();
    }

    public function getDisegnoDato($Ricdag_rec, $html, $dati) {
        $fieldPrefix = $dati['Prefix'];
        $Ricdag_rec = $this->praLib->ctrRicdagRec($Ricdag_rec, $dati['Dizionario']->getAlldataPlain("", "."));
        $styleLblBO = $Ricdag_rec['DAGLABSTYLE'];
        $styleFldBO = $Ricdag_rec['DAGFIELDSTYLE'];
        $classFldBO = $Ricdag_rec['DAGFIELDCLASS'];
        $meta = unserialize($Ricdag_rec["DAGMETA"]);
        $value = $readonly = $campoObl = $br = "";
        $defaultValue = $classPosLabel = $disabled = $checked = "";
        if ($Ricdag_rec['DAGDIZ'] == "C") {
            $defaultValue = $Ricdag_rec['DAGVAL'];
        } elseif ($Ricdag_rec['DAGDIZ'] == "D") {
            $defaultKey = $Ricdag_rec['DAGVAL'];
            if ($Ricdag_rec['DAGTIC'] == 'Select') {
                if (strpos($defaultKey, '^' !== false)) {
                    list($optionsKey, $defaultKey) = explode("^", $defaultKey);
                }
            }
            $defaultValue = $dati['Dizionario']->getData($defaultKey);
        } elseif ($Ricdag_rec['DAGDIZ'] == "T") {
            $defaultKey = $Ricdag_rec['DAGVAL'];
            if ($Ricdag_rec['DAGTIC'] == 'Select') {
                if (strpos($defaultKey, '^' !== false)) {
                    list($optionsKey, $defaultKey) = explode("^", $defaultKey);
                }
            }
            $defaultValue_pre = $this->praLib->elaboraTemplateDefault($defaultKey, $dati['Dizionario']->getAllData());
            $defaultValue = str_replace("\\n", chr(13), $defaultValue_pre);
        } elseif ($Ricdag_rec['DAGDIZ'] == "H" && $meta['HTMLPOS'] == "Default") {
            $dictionaryValues_pre = $dati['Dizionario']->getAllData();
            $dictionaryValues = str_replace("\\n", chr(13), $dictionaryValues_pre);
            $template = $this->praLib->elaboraTabelleTemplate($Ricdag_rec["DAGVAL"], $dictionaryValues, true);
            $defaultValue = $this->praLib->valorizzaTemplate($template, $dictionaryValues);
        }

        /*
         * Metto un br quando ce il flag a capo tranne se è il primo campo
         */

        if ($Ricdag_rec['DAGACA'] == 1 && $this->column == 0) {
            $br = "<br id=\"{$Ricdag_rec['DAGKEY']}_acapo\">";
        }

        /*
         * Metto un * quando i lcampo è obbligatorio
         */

        if (strpos($Ricdag_rec['DAGCTR'], $Ricdag_rec['DAGKEY'])) {
            $campoObl = $this->requiredLabel;
        }

        /*
         * Se ci sono le coleonne metto tr e td
         */

        if ($this->column) {
            if ($this->contaColumn == 0) {
                $html->appendHtml("<tr>");
            }
            $this->contaColumn += 1;
            //if ($Ricdag_rec['DAGACAPO']) {
            if ($Ricdag_rec['DAGACA']) {
                $colspan = "colspan=\"" . $this->column - $this->contaColumn . "\"";
                $this->contaColumn = $this->column;
            }

            $html->appendHtml("<td  $colspan>");
        }

        /*
         * Switch per tipizzati
         */

        switch ($Ricdag_rec['DAGTIP']) {
            case 'Sportello_Aggregato':
            case 'Codfis_Dichiarante':
            case 'Codfis_Anades':
            case 'Indir_InsProduttivo':
            case 'Denom_Fiera':
            case 'Posteggi_fiera':
            case 'Richiesta_padre':
            case 'Istituti':
            case 'Servizi':
            case 'Iscrivendi':
            case 'IscrivendiSint':
            case 'Percorsi_ferm':
            case 'Tipo_pasto':
            case 'Tipo_segnalazione':
            case 'Evento_Richiesta':
            case 'Richiesta_unica':
            case 'Denom_FieraBando':
            case 'Denom_FieraPBando':
            case 'Denom_MercatoBando':
            case 'Denom_PIBando':
            case 'Forma_Giuridica':
            case 'Carica':
            case 'Qualifica':
            case 'Rich_padre_variante':
            case 'Ruolo':
            case 'Comune':
            case 'Denom_MercatoBando':
            case 'Ricerca_Generica':
            case 'Tabella_Generica':
                $Ricdag_rec['DAGTIC'] = $Ricdag_rec['DAGTIP'];
                break;

            case 'Cap_InsProduttivo':
            case 'Comune_InsProduttivo':
            case 'Prov_InsProduttivo':
                return;
        }

        switch ($Ricdag_rec['DAGTIC']) {
            case 'Html':
                $html->appendHtml("<div class=\"ita-html-container\" id=\"{$Ricdag_rec['DAGKEY']}\">");
                break;

            case 'Tabella_Generica':
                $html->appendHtml("<div>");
                break;

            default:
                $html->appendHtml("<div class=\"ita-field\">");
                break;
        }

        /*
         * Posizione Label
         */

        switch ($Ricdag_rec['DAGPOS']) {
            case "":
            case "Sinistra":
                $classPosLabel = "ita-label-sx";
                break;
            case "Destra":
                $classPosLabel = "ita-label-dx";
                break;
            case "Sopra":
                $classPosLabel = "ita-label-top";
                break;
            case "Sotto":
                $classPosLabel = "ita-label-bot";
                break;
            case "Nascosta":
                $classPosLabel = "ita-label-hidden";
                break;
        }

        /*
         * Override del campo
         */

        $fl_disegno_std = false;

        /* @var $objTemplate praLibCustomClass */
        $objTemplate = praLibCustomClass::getInstance($this->praLib);

        if ($objTemplate->checkEseguiEvento('ONCHANGE', $Ricdag_rec)) {
            $classFldBO .= ' italsoft-ajax-onchange';
        }

        if ($objTemplate) {
            $datiPasso = array(
                'Ricdag_rec' => $Ricdag_rec,
                'styleLblBO' => $styleLblBO,
                'styleFldBO' => $styleFldBO,
                'classFldBO' => $classFldBO,
                'defaultValue' => $defaultValue,
                'classPosLabel' => $classPosLabel,
                'br' => $br,
                'campoObl' => $campoObl
            );

            $retTemplateCampo = $objTemplate->eseguiDisegnoCampo($dati, $datiPasso);

            if ($retTemplateCampo === false) {
                $fl_disegno_std = true;
            }

            $html->appendHtml($retTemplateCampo);
        } else {
            $fl_disegno_std = true;
        }

        if ($fl_disegno_std) {
            switch ($Ricdag_rec['DAGTIC']) {
                case 'Percorsi_ferm':
                    $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
                    if ($progSogg) {
                        $arrayPercorsiFerm = $this->praLib->getPercorsiFermCityWare($progSogg, $dati['Codice'], $Ricdag_rec['DAGKEY']);
                        //if ($dati['ReadOnly'] == 1) {
                        if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                            $disabled = "disabled=\"disabled\"";
                        }
                        $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                        $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                        $html->appendHtml("<select name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}_" . $Ricdag_rec['DAGKEY'] . "\" $disabled>");
                        $html->appendHtml("<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>");
                        $arrayOptions = array();
                        foreach ($arrayPercorsiFerm['LIST'][0]['ROW'] as $key => $percorso) {
                            $arrayOptions[$key]['PERCORSIFERMATE_DESPERCORA'] = $percorso['DESPERCORA'][0]['@textNode'];
                            $arrayOptions[$key]['PERCORSIFERMATE_DESFERMATA'] = $percorso['DESFERMATA'][0]['@textNode'];
                            $arrayOptions[$key]['PERCORSIFERMATE_CHIAVEFEE'] = $percorso['CHIAVEFEE'][0]['@textNode'];
                        }
                        foreach ($arrayOptions as $option) {
                            $Sel = "";
                            if ($Ricdag_rec['RICDAT'] == $option['PERCORSIFERMATE_CHIAVEFEE']) {
                                $Sel = "selected";
                            }
                            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"" . $option['PERCORSIFERMATE_CHIAVEFEE'] . "\">" . $option['PERCORSIFERMATE_DESPERCORA'] . " - " . $option['PERCORSIFERMATE_DESFERMATA'] . "</option>");
                        }
                        $html->appendHtml("</select>");
                    }
                    break;

                case 'Tipo_pasto':
                    $arrayTipoPasto = $this->praLib->getTipoPastoCityWare();
                    //if ($dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $disabled = "disabled=\"disabled\"";
                    }
                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<select name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}_" . $Ricdag_rec['DAGKEY'] . "\" $disabled>");
                    $html->appendHtml("<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>");
                    $arrayOptions = array();
                    foreach ($arrayTipoPasto['LIST'][0]['ROW'] as $key => $percorso) {
                        $arrayOptions[$key]['TIPOPASTO_DESTPAS'] = $percorso['DESTPAS'][0]['@textNode'];
                        $arrayOptions[$key]['TIPOPASTO_CLASSIFN'] = $percorso['CLASSIFN'][0]['@textNode'];
                        $arrayOptions[$key]['TIPOPASTO_TPAS_DFT'] = $percorso['TPAS_DFT'][0]['@textNode'];
                    }
                    foreach ($arrayOptions as $option) {
                        $Sel = "";
                        if ($Ricdag_rec['RICDAT'] == $option['TIPOPASTO_CLASSIFN']) {
                            $Sel = "selected";
                        }
                        $html->appendHtml("<option $Sel class=\"optSelect\" value=\"" . $option['TIPOPASTO_CLASSIFN'] . "\">" . $option['TIPOPASTO_DESTPAS'] . "</option>");
                    }
                    $html->appendHtml("</select>");
                    break;

                case 'IscrivendiSint':
                    $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
                    if ($progSogg) {
                        $arrayNucleoXML = $this->praLib->getNucleoFamiliareCityWare($progSogg, $dati['Codice']);
                        //if ($dati['ReadOnly'] == 1) {
                        if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                            $disabled = "disabled=\"disabled\"";
                        }
                        $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                        $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                        $html->appendHtml("<select name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}_" . $Ricdag_rec['DAGKEY'] . "\" $disabled>");
                        $html->appendHtml("<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>");
                        $arrayOptions = array();
                        foreach ($arrayNucleoXML['LIST'][0]['ROW'] as $key => $iscrivendo) {
                            $arrayOptions[$key]['ISCRIVENDO_PROGSOGG'] = $iscrivendo['PROGSOGG_N'][0]['@textNode'];
                            $arrayOptions[$key]['ISCRIVENDO_COGNOME'] = $iscrivendo['COGNOME'][0]['@textNode'];
                            $arrayOptions[$key]['ISCRIVENDO_NOME'] = $iscrivendo['NOME'][0]['@textNode'];
                            $arrayOptions[$key]['ISCRIVENDO_NASCITADATA_DATA'] = $iscrivendo['DATA_NASCITA'][0]['@textNode'];
                        }

                        foreach ($arrayOptions as $option) {
                            $Sel = "";
                            if ($Ricdag_rec['RICDAT'] == $option['ISCRIVENDO_PROGSOGG']) {
                                $Sel = "selected";
                            }
                            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"" . $option['ISCRIVENDO_PROGSOGG'] . "\">" . $option['ISCRIVENDO_COGNOME'] . " " . $option['ISCRIVENDO_NOME'] . "</option>");
                        }
                        $html->appendHtml("</select>");
                    }
                    break;

                case 'Iscrivendi':
                    $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
                    if ($progSogg) {
                        $arrayIstitutiXML = $this->praLib->getNucleoFamiliareCityWare($progSogg);
                        $readonly = "readonly=\"readonly\"";
                        $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                        $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                        $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                        $html->appendHtml("<input id=\"{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]\" style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"{$Ricdag_rec['DAGLEN']}\" size=\"{$Ricdag_rec['DAGDIM']}\" value=\"$value\" name=\"{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]\" />");

                        //if ($dati['ReadOnly'] == 1) {
                        if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                            break;
                        }
                        $Iscrivendi_tab = array();
                        foreach ($arrayIstitutiXML['LIST'][0]['ROW'] as $key => $iscivendo) {
                            if ($iscivendo['RELAZIONE'][0]['@textNode'] != "FG") {
                                continue;
                            }
                            $Iscrivendi_tab[$key]['ISCRIVENDO_PROGSOGG'] = $iscivendo['PROGSOGG_N'][0]['@textNode'];
                            $Iscrivendi_tab[$key]['ISCRIVENDO_COGNOME'] = $iscivendo['COGNOME'][0]['@textNode'];
                            $Iscrivendi_tab[$key]['ISCRIVENDO_NOME'] = $iscivendo['NOME'][0]['@textNode'];
                            $Iscrivendi_tab[$key]['ISCRIVENDO_NASCITADATA_DATA'] = $iscivendo['DATA_NASCITA'][0]['@textNode'];
                            $Iscrivendi_tab[$key]['ISCRIVENDO_NASCITACOMUNE'] = $iscivendo['LUOGO_NASCITA'][0]['@textNode'];
                            $Iscrivendi_tab[$key]['ISCRIVENDO_CODICEFISCALE_CFI'] = $iscivendo['CODFISCALE'][0]['@textNode'];
                            $Iscrivendi_tab[$key]['ISCRIVENDO_RELAZIONEFAM'] = $iscivendo['RELAZIONE'][0]['@textNode'];
                            $Iscrivendi_tab[$key]['ISCRIVENDO_SESSO_SEX'] = $iscivendo['SESSO'][0]['@textNode'];
                        }
                        if ($Iscrivendi_tab) {
                            $display = "inline-block";
                            if ($dati['ReadOnly'] == 1) {
                                $display = "none";
                            }

                            $html->appendHtml("<div id=\"div_elencoCodice_iscrivente_ric\" class=\"ui-widget ui-widget-content ui-corner-all\" style=\"margin-top:4px;width:100%;\">");

                            $html->appendHtml('<table id="tabella_allegati110" class="ita-table tabella_allegati tabella_filtri tablesorter" border="1" cellpadding="0" cellspacing="0">');
                            $html->appendHtml("<caption>Elenco Iscrivendi</caption>");
                            $html->appendHtml('<thead>');
                            $html->appendHtml('<th>Codice</th>');
                            $html->appendHtml('<th>Cognome</th>');
                            $html->appendHtml('<th>Nome</th>');
                            $html->appendHtml('<th>Data di<br>Nascita</th>');
                            $html->appendHtml('<th>Comune di<br>Nascita</th>');
                            $html->appendHtml('<th>Codice Fiscale</th>');
                            $html->appendHtml('<th>Relazione</th>');
                            $html->appendHtml('<th>Sesso</th>');
                            $html->appendHtml('</thead>');
                            $html->appendHtml('<tbody>');
                            foreach ($Iscrivendi_tab as $elemento) {
                                $html->appendHtml("<tr>");
                                $html->appendHtml("<td data-ita-edit-ref=\"{$fieldPrefix}[ISCRIVENDO_PROGSOGG]\">" . $elemento['ISCRIVENDO_PROGSOGG'] . "</td>");
                                $html->appendHtml("<td data-ita-edit-ref=\"{$fieldPrefix}[ISCRIVENDO_COGNOME]\">" . $elemento['ISCRIVENDO_COGNOME'] . "</td>");
                                $html->appendHtml("<td data-ita-edit-ref=\"{$fieldPrefix}[ISCRIVENDO_NOME]\">" . $elemento['ISCRIVENDO_NOME'] . "</td>");
                                $html->appendHtml("<td data-ita-edit-ref=\"{$fieldPrefix}[ISCRIVENDO_NASCITADATA_DATA]\">" . $elemento['ISCRIVENDO_NASCITADATA_DATA'] . "</td>");
                                $html->appendHtml("<td data-ita-edit-ref=\"{$fieldPrefix}[ISCRIVENDO_NASCITACOMUNE]\">" . $elemento['ISCRIVENDO_NASCITACOMUNE'] . "</td>");
                                $html->appendHtml("<td data-ita-edit-ref=\"{$fieldPrefix}[ISCRIVENDO_CODICEFISCALE_CFI]\">" . $elemento['ISCRIVENDO_CODICEFISCALE_CFI'] . "</td>");
                                $html->appendHtml("<td data-ita-edit-ref=\"{$fieldPrefix}[ISCRIVENDO_RELAZIONEFAM]\">" . $elemento['ISCRIVENDO_RELAZIONEFAM'] . "</td>");
                                $html->appendHtml("<td data-ita-edit-ref=\"{$fieldPrefix}[ISCRIVENDO_SESSO_SEX]\">" . $elemento['ISCRIVENDO_SESSO_SEX'] . "</td>");
                                $html->appendHtml("</tr>");
                            }
                            $html->appendHtml('</tbody>');
                            $html->appendHtml('</table>');
                            $itaTableSorter = new itaTableSorter();
                            $html->appendHtml($itaTableSorter->mostraPager("110", '1', '10'));
                            $html->appendHtml("</div>");
                        }
                    }
                    break;

                case 'Istituti':
                    $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
                    if ($progSogg) {
                        $arrayIstituti = $this->praLib->getIstitutiScolasticiCityware($progSogg, $dati['Codice']);
                        //if ($dati['ReadOnly'] == 1) {
                        if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                            $disabled = "disabled=\"disabled\"";
                        }

                        $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                        $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                        $html->appendHtml("<select class=\"italsoft-input $classFldBO\" style=\"$styleFldBO\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" $disabled>");
                        $html->appendHtml("<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>';");
                        $arrayOptions = array();
                        foreach ($arrayIstituti['LIST'][0]['ROW'] as $key => $istituto) {
                            $arrayOptions[$key]['DESISTITU'] = $istituto['DESISTITU'][0]['@textNode'];
                            $arrayOptions[$key]['ISTITU2'] = $istituto['ISTITU2'][0]['@textNode'];
                            $arrayOptions[$key]['FL_ZONA'] = $istituto['FL_ZONA'][0]['@textNode'];
                            $arrayOptions[$key]['SIGLAISTI'] = $istituto['SIGLAISTI'][0]['@textNode'];
                        }

                        foreach ($arrayOptions as $option) {
                            $Sel = "";
                            if ($Ricdag_rec['RICDAT'] == $option['ISTITU2']) {
                                $Sel = "selected";
                            }
                            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"" . $option['ISTITU2'] . "\">" . $option['DESISTITU'] . "</option>';");
                        }
                        $html->appendHtml("</select>");
                    }
                    break;

                case 'Servizi':
                    $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
                    if ($progSogg) {
                        $arrayServizi = $this->praLib->getServiziCityware($progSogg, $dati['Codice']);
                        //if ($dati['ReadOnly'] == 1) {
                        if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                            $disabled = "disabled=\"disabled\"";
                        }
                        $html->appendHtml("<label class=\"$classPosLabel ita-label\">" . $Ricdag_rec['DAGDES'] . "</label>");
                        $html->appendHtml("<select name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}_" . $Ricdag_rec['DAGKEY'] . "\" $disabled>");
                        $html->appendHtml("<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>';");
                        $arrayOptions = array();
                        foreach ($arrayServizi['LIST'][0]['ROW'] as $key => $servizio) {
                            $arrayOptions[$key]['DES_SERVIZIO'] = $servizio['DES_SERVIZIO'][0]['@textNode'];
                            $arrayOptions[$key]['KFEECP'] = $servizio['KFEECP'][0]['@textNode'];
                        }

                        foreach ($arrayOptions as $option) {
                            $Sel = "";
                            if ($Ricdag_rec['RICDAT'] == $option['KFEECP']) {
                                $Sel = "selected";
                            }
                            $html->appendHtml("<option $Sel class=\"optSelect\" value=\"" . $option['KFEECP'] . "\">" . $option['DES_SERVIZIO'] . "</option>';");
                        }
                        $html->appendHtml("</select>");
                    }
                    break;

                case 'Richiesta_unica':
                    $passiObbligatoriPerAccorpa = ((int) $dati['countObl']) - 1;

                    /*
                     * Visualizza il messaggio solo se passo di finalizzazione. (ITERICUNI)
                     */
                    if ((int) $dati['countEsg'] < $passiObbligatoriPerAccorpa && $dati['Ricite_rec']['ITERICUNI'] == '1') {
                        $html->appendHtml("<div style=\"text-align: center; color: red; font-weight: bold; font-size: 1.4em;\">Eseguire tutti i passi obbligatori per accorpare la Richiesta.</div><br />");
                        $buttonConferma = '';
                        break;
                    }

                    $readonly = "readonly=\"readonly\"";

                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $sql = "SELECT 
                                PRORIC.ROWID,
                                PRORIC.RICSEQ,
                                ANASET.SETDES,
                                ANAATT.ATTDES,
                                RICPRO,
                                RICDESCR,
                                RICEVE,
                                RICNUM,
                                RICDRE,
                                RICORE,
                                RICDAT,
                                RICTIM,
                                " . $dati["PRAM_DB"]->strConcat("ANAPRA.PRADES__1", "ANAPRA.PRADES__2", "ANAPRA.PRADES__3") . " AS PRADES
                            FROM
                                PRORIC
                            LEFT OUTER JOIN ANAPRA ON PRORIC.RICPRO = ANAPRA.PRANUM
                            LEFT OUTER JOIN ANASET ON PRORIC.RICSTT = ANASET.SETCOD
                            LEFT OUTER JOIN ANAATT ON PRORIC.RICATT = ANAATT.ATTCOD
                            WHERE
                                RICFIS = '" . $dati['Proric_rec']['RICFIS'] . "'
                            AND
                                ( RICSTA = '99' )
                            AND
                                RICNUM != '" . $dati['Proric_rec']['RICNUM'] . "'
                            ORDER BY
                                RICNUM DESC";

                    $proric_tab = ItaDB::DBSQLSelect($dati["PRAM_DB"], $sql, true);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align: right; display: inline-block; align: right; $styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<input style=\"$styleFldBO vertical-align: middle;\" class=\"$classFldBO\" $readonly maxlength=\"{$Ricdag_rec['DAGLEN']}\" size=\"{$Ricdag_rec['DAGDIM']}\" value=\"$value\" id=\"{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]\" name=\"{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]\" />");

                    if (!$proric_tab) {
                        $html->appendHtml("<div style=\"text-align: center; font-weight: bold; font-size: 1.3em;\">Non ci sono Richieste in corso che possono accorpare questa Richiesta.</div><br />");
                        $buttonConferma = '';
                        break;
                    }

                    //if ($dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        break;
                    }

                    $datiTabellaLookup = array(
                        'header' => array(
                            array('text' => 'Numero', 'attrs' => array('style' => 'width: 10%;')),
                            'Descrizione',
                            array('text' => 'Settore', 'attrs' => array('style' => 'width: 10%;')),
                            array('text' => 'Attività', 'attrs' => array('style' => 'width: 10%;')),
                            array('text' => 'Data/ora inizio', 'attrs' => array('style' => 'width: 10%;'))
                        ),
                        'body' => array()
                    );

                    require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php';
                    $praLibEventi = new praLibEventi();

                    foreach ($proric_tab as $proric_rec) {
                        $Ricite_passo_ricuni = ItaDB::DBSQLSelect($dati["PRAM_DB"], "SELECT ITESEQ FROM RICITE WHERE RICNUM = '" . $proric_rec['RICNUM'] . "' AND ITERICUNI = '1'", false);

                        if (!$Ricite_passo_ricuni) {
                            continue;
                        }

                        if ($this->praLib->checkEsecuzionePasso($proric_rec, $Ricite_passo_ricuni)) {
                            continue;
                        }

                        $ricnum = substr($proric_rec['RICNUM'], 4) . "/" . substr($proric_rec['RICNUM'], 0, 4);
                        $descProc = "<b>" . $proric_rec['RICPRO'] . "</b> - " . $praLibEventi->getOggettoProric($dati['PRAM_DB'], $proric_rec);
                        $data_inizio = substr($proric_rec['RICDRE'], 6, 2) . "/" . substr($proric_rec['RICDRE'], 4, 2) . "/" . substr($proric_rec['RICDRE'], 0, 4);
                        $inizio = $data_inizio . "<br>" . $proric_rec['RICORE'];

                        $datiTabellaLookup['body'][] = array(
                            array(
                                'text' => $ricnum,
                                'attrs' => array(
                                    'data-ita-edit-ref' => $fieldPrefix . '[RICHIESTA_UNICA_FORMATTED]'
                                )
                            ),
                            $descProc,
                            $proric_rec['SETDES'],
                            $proric_rec['ATTDES'],
                            $inizio,
                            array(
                                'text' => $proric_rec['RICNUM'],
                                'attrs' => array(
                                    'class' => 'ita-hidden-cell',
                                    'data-ita-edit-ref' => $fieldPrefix . '[RICHIESTA_UNICA]'
                                )
                            )
                        );
                    }

                    $this->disegnaRicerca($html, 'ricPratica' . $Ricdag_rec['DAGSEQ'], $datiTabellaLookup, array(
                        'sortable' => true,
                        'paginated' => true,
                        'filters' => true
                            ), 'Elenco Richieste');
                    break;

                case 'Rich_padre_variante':
                case 'Richiesta_padre':
                    $readonly = "readonly=\"readonly\"";
                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $metadata = unserialize($Ricdag_rec['DAGMETA']);

                    $whereProcedimento = '';
                    if ($metadata['PARAMSTIPODATO']['PROCEDIMENTO']) {
                        $queryProcedimento = array();
                        $codiceProcedimenti = explode(',', $metadata['PARAMSTIPODATO']['PROCEDIMENTO']);
                        foreach ($codiceProcedimenti as $codiceProcedimento) {
                            list($rangeStart, $rangeEnd) = explode('-', $codiceProcedimento, 2);
                            if ($rangeEnd) {
                                $queryProcedimento[] = "RICPRO >= '$rangeStart' AND RICPRO <= '$rangeEnd'";
                            } else {
                                $queryProcedimento[] = "RICPRO = '$codiceProcedimento'";
                            }
                        }

                        $whereProcedimento .= " AND (" . implode(' OR ', $queryProcedimento) . ")";
                    }

                    $sql = "SELECT 
                                PRORIC.ROWID,
                                RICNUM,
                                RICDRE,
                                RICORE,
                                RICDAT,
                                RICNPR,
                                RICDPR,
                                RICTIM," .
                            $dati["PRAM_DB"]->strConcat("ANAPRA.PRADES__1", "ANAPRA.PRADES__2", "ANAPRA.PRADES__3") . " AS PRADES,
                                RICCONFCONTEXT
                            FROM
                                PRORIC
                            LEFT OUTER JOIN ANAPRA ON PRORIC.RICPRO=ANAPRA.PRANUM
                            WHERE
                                RICFIS = '" . $dati['Proric_rec']['RICFIS'] . "' AND (RICSTA = '01' OR RICSTA = '91') AND RICRPA=''
                                $whereProcedimento 
                            ORDER BY
                                RICNUM DESC";
                    $proric_tab = ItaDB::DBSQLSelect($dati["PRAM_DB"], $sql, true);

                    $anaparBlkIntegra_rec = $this->praLib->GetAnapar("BLOCK_INTEGRAZIONI", "parkey", $dati['PRAM_DB'], false);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<input style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"{$Ricdag_rec['DAGLEN']}\" size=\"{$Ricdag_rec['DAGDIM']}\" value=\"$value\" name=\"{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]\" id=\"{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]\" />");

                    if ($metadata['PARAMSTIPODATO']['DOMUS_STATO']) {
                        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibDomus.class.php';
                        $praLibDomus = new praLibDomus();

                        $filtroStatiDomus = explode(',', $metadata['PARAMSTIPODATO']['DOMUS_STATO']);
                        $filtroStatiDomus = array_map('trim', $filtroStatiDomus);
                        $filtroStatiDomus = array_map('strtolower', $filtroStatiDomus);

                        foreach ($proric_tab as $k => $proric_rec) {
                            if ($proric_rec['RICCONFCONTEXT'] != 'DOMUS') {
                                unset($proric_tab[$k]);
                                continue;
                            }

                            $infoPratica = $praLibDomus->getPratica($proric_rec['RICNUM']);
                            if (!$infoPratica || !$infoPratica['CodStato']) {
                                unset($proric_tab[$k]);
                                continue;
                            }

                            if (!in_array(strtolower($infoPratica['CodStato']), $filtroStatiDomus)) {
                                unset($proric_tab[$k]);
                                continue;
                            }
                        }
                    }

                    //if ($proric_tab && $dati['ReadOnly'] != 1) {
                    if ($proric_tab && !$this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $datiTabellaLookup = array(
                            'header' => array(
                                array('text' => 'Numero', 'attrs' => array('style' => 'width: 10%;')),
                                'Descrizione',
                                array('text' => 'Oggetto', 'attrs' => array('style' => 'width: 25%;')),
                                array('text' => 'Committente', 'attrs' => array('style' => 'width: 10%;')),
                                array('text' => 'Localizzazione', 'attrs' => array('style' => 'width: 10%;')),
                                array('text' => 'Data/ora inizio', 'attrs' => array('style' => 'width: 10%;')),
                                array('text' => 'Data/ora inoltro', 'attrs' => array('style' => 'width: 10%;')),
                                array('text' => 'Numero/data protocollo', 'attrs' => array('style' => 'width: 10%;'))
                            ),
                            'body' => array(),
                            'style' => array(
                                'body' => array(
                                    'font-size: .9em;',
                                    'font-size: .9em;',
                                    'font-size: .9em;',
                                    'font-size: .9em;',
                                    'font-size: .9em;',
                                    'font-size: .9em;',
                                    'font-size: .9em;',
                                    'font-size: .9em;'
                                )
                            )
                        );

                        $datiAggiuntiviSelect = array(
                            'COMUNEDESTINATARIO',
                            'TIPOLOGIA_COSTRUZIONE',
                            'TIPOLOGIA_INTERVENTO',
                            'DENUNCIA_01',
                            'DENUNCIA_02',
                            'CHECK_PREFABB',
                            'DICHIARANTE_NOME',
                            'DICHIARANTE_COGNOME',
                            'IMPRESA_RAGIONESOCIALE',
                            'INTER_LOCALITA',
                            'INTER_VIA',
                            'INTER_CIV',
                            'INTER_CAP',
                            'DESCR_OPERE_STRUTTURALI'
                        );

                        foreach ($proric_tab as $proric_rec) {
                            $Proges_rec = ItaDB::DBSQLSelect($dati["PRAM_DB"], "SELECT * FROM PROGES WHERE GESPRA='" . $proric_rec['RICNUM'] . "'", false);
                            $Ricdag_ric_tab = ItaDB::DBSQLSelect($dati['PRAM_DB'], "SELECT DAGKEY, RICDAT FROM RICDAG WHERE DAGNUM = '{$proric_rec['RICNUM']}' AND DAGKEY IN ('" . implode("', '", $datiAggiuntiviSelect) . "')");

                            $datiAggiuntivi = array();
                            foreach ($Ricdag_ric_tab as $Ricdag_ric_rec) {
                                $datiAggiuntivi[$Ricdag_ric_rec['DAGKEY']] = htmlspecialchars($Ricdag_ric_rec['RICDAT'], ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
                            }

                            if ($Proges_rec['GESDCH']) {
                                if ($anaparBlkIntegra_rec['PARVAL'] == "Si") {
                                    continue;
                                }
                            }

                            $ricnum = substr($proric_rec['RICNUM'], 4) . "/" . substr($proric_rec['RICNUM'], 0, 4);
                            $data_inizio = substr($proric_rec['RICDRE'], 6, 2) . "/" . substr($proric_rec['RICDRE'], 4, 2) . "/" . substr($proric_rec['RICDRE'], 0, 4);
                            $inizio = $data_inizio . "<br>" . $proric_rec['RICORE'];
                            $data_inoltro = substr($proric_rec['RICDAT'], 6, 2) . "/" . substr($proric_rec['RICDAT'], 4, 2) . "/" . substr($proric_rec['RICDAT'], 0, 4);
                            $inoltro = $data_inoltro . "<br>" . $proric_rec['RICTIM'];

                            $textProtocollo = '';
                            if ($proric_rec['RICNPR'] != 0) {
                                $numeroProtocollo = substr($proric_rec['RICNPR'], 4) . '/' . substr($proric_rec['RICNPR'], 0, 4);
                                $dataProtocollo = frontOfficeLib::convertiData($proric_rec['RICDPR']);
                                $textProtocollo = "$numeroProtocollo<br>$dataProtocollo";
                            }

                            $textUbicazione = $datiAggiuntivi['COMUNEDESTINATARIO'] . ' ';
                            $textUbicazione .= $datiAggiuntivi['INTER_CAP'] . '<br>';
                            if ($datiAggiuntivi['INTER_LOCALITA']) {
                                $textUbicazione .= $datiAggiuntivi['INTER_LOCALITA'] . '<br>';
                            }
                            $textUbicazione .= $datiAggiuntivi['INTER_VIA'] . ' ';
                            $textUbicazione .= $datiAggiuntivi['INTER_CIV'];

                            $textCommittente = $datiAggiuntivi['IMPRESA_RAGIONESOCIALE'] ?: $datiAggiuntivi['DICHIARANTE_NOME'] . ' ' . $datiAggiuntivi['DICHIARANTE_COGNOME'];

                            $tableRecord = array(
                                array(
                                    'text' => $ricnum,
                                    'attrs' => array(
                                        'data-ita-edit-ref' => "{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]"
                                    )
                                ),
                                $proric_rec['PRADES'],
                                $datiAggiuntivi['DESCR_OPERE_STRUTTURALI'],
                                $textCommittente,
                                $textUbicazione,
                                $inizio,
                                $inoltro,
                                $textProtocollo,
                                array(
                                    'text' => $proric_rec['RICNUM'],
                                    'attrs' => array(
                                        'class' => 'ita-hidden-cell',
                                        'data-ita-edit-ref' => $fieldPrefix . '[RICHIESTA_PADRE]'
                                    )
                                ),
                                array(
                                    'text' => $proric_rec['RICNUM'],
                                    'attrs' => array(
                                        'class' => 'ita-hidden-cell',
                                        'data-ita-edit-ref' => $fieldPrefix . '[RICHIESTA_PADRE_VARIANTE]'
                                    )
                                ),
                                array(
                                    'text' => $proric_rec['RICNUM'],
                                    'attrs' => array(
                                        'class' => 'ita-hidden-cell',
                                        'data-ita-edit-ref' => $fieldPrefix . '[PRATICA_INIZIALE]'
                                    )
                                )
                            );

                            foreach ($datiAggiuntiviSelect as $key) {
                                $tableRecord[] = array(
                                    'text' => $datiAggiuntivi[$key],
                                    'attrs' => array(
                                        'class' => 'ita-hidden-cell',
                                        'data-ita-edit-ref' => $fieldPrefix . '[' . $key . ']'
                                    )
                                );
                            }

                            $datiTabellaLookup['body'][] = $tableRecord;
                        }

                        $this->disegnaRicerca($html, 'ricRichiesteOnline' . $Ricdag_rec['DAGSEQ'], $datiTabellaLookup, array(
                            'sortable' => true,
                            'paginated' => true,
                            'filters' => true
                                ), 'Elenco Richieste');
                    }

                    break;

                case 'Codfis_Dichiarante':
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = "readonly=\"readonly\"";
                    }

                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");

                    $sql = "
						SELECT
							MAX(PRORIC.RICNUM) AS RICNUM,
							RICDAG.RICDAT
						FROM 
							PRORIC
						LEFT OUTER JOIN
							RICDAG
						ON
							RICNUM=RICDAG.DAGNUM AND RICDAG.DAGKEY='DICHIARANTE_CODICEFISCALE_CFI' AND RICDAG.RICDAT<>''
						WHERE 
							RICFIS = '" . $dati['Proric_rec']['RICFIS'] . "' AND 
							RICRPA = '' AND 		
							(RICSTA = '01' OR RICSTA = '91' OR RICSTA = '99') AND
							RICDAG.RICDAT IS NOT NULL AND
							RICRPA = ''
						GROUP BY RICDAG.RICDAT";

                    $proric_tab = ItaDB::DBSQLSelect($dati["PRAM_DB"], $sql, true);
                    $Ricdag_tadDef = array();
                    foreach ($proric_tab as $key => $proric_rec) {
                        $sql1 = "
							SELECT
								ROWID,
								DAGNUM,
								DAGKEY,
								DAGSET,
								RICDAT 
							FROM 
								RICDAG 
							WHERE 
								DAGNUM='" . $proric_rec['RICNUM'] . "' AND 
								DAGSET=(SELECT MIN(R2.DAGSET) FROM RICDAG R2 WHERE R2.DAGNUM='" . $proric_rec['RICNUM'] . "' AND R2.DAGKEY='DICHIARANTE_CODICEFISCALE_CFI' AND R2.RICDAT='" . addslashes($proric_rec['RICDAT']) . "') AND
								DAGKEY LIKE 'DICHIARANTE_%'";

                        $ricdag_tab = ItaDB::DBSQLSelect($dati["PRAM_DB"], $sql1, true);
                        foreach ($ricdag_tab as $ricdag_rec) {
                            $Ricdag_tadDef[$ricdag_rec['DAGSET']]["DEFAULT_" . $ricdag_rec['DAGKEY']] = htmlspecialchars($ricdag_rec['RICDAT'], ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
                        }
                    }

                    $html->appendHtml("<input style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"{$Ricdag_rec['DAGLEN']}\" size=\"{$Ricdag_rec['DAGDIM']}\" value=\"$value\" name=\"{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]\" id=\"{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]\" />");
                    //if ($Ricdag_tadDef && $dati['ReadOnly'] != 1) {
                    if ($Ricdag_tadDef && !$this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $datiTabellaLookup = array(
                            'header' => array(
                                'Cod. Fiscale',
                                'Cognome',
                                'Nome',
                                'Data di<br />nascita',
                                'Comune di<br / >nascita',
                                'Provincia di<br / >nascita'
                            ),
                            'body' => array()
                        );

                        foreach ($Ricdag_tadDef as $elemento) {
                            $arrayCogNom = array();
                            $cognome = $elemento['DEFAULT_DICHIARANTE_COGNOME'];
                            $nome = $elemento['DEFAULT_DICHIARANTE_NOME'];

                            if (isset($elemento['DEFAULT_DICHIARANTE_COGNOME_NOME'])) {
                                $arrayCogNom = explode(" ", $elemento['DEFAULT_DICHIARANTE_COGNOME_NOME']);
                                $cognome = $arrayCogNom[0];
                                $nome = $arrayCogNom[1];
                            }

                            $datiTabellaLookup['body'][] = array(
                                array('text' => $elemento['DEFAULT_DICHIARANTE_CODICEFISCALE_CFI'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_CODICEFISCALE_CFI]')),
                                array('text' => $cognome, 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_COGNOME]')),
                                array('text' => $nome, 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_NOME]')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_NASCITADATA_DATA'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_NASCITADATA_DATA]')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_NASCITACOMUNE'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_NASCITACOMUNE]')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_NASCITAPROVINCIA_PV'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_NASCITAPROVINCIA_PV]')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_CITTADINANZA'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_CITTADINANZA]', 'class' => 'ita-hidden-cell')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_SESSO_SEX'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_SESSO_SEX]', 'class' => 'ita-hidden-cell')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_NASCITANAZIONE'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_NASCITANAZIONE]', 'class' => 'ita-hidden-cell')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_RESIDENZACOMUNE'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_RESIDENZACOMUNE]', 'class' => 'ita-hidden-cell')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_RESIDENZACAP_CAP'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_RESIDENZACAP_CAP]', 'class' => 'ita-hidden-cell')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_RESIDENZAPROVINCIA_PV'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_RESIDENZAPROVINCIA_PV]', 'class' => 'ita-hidden-cell')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_RESIDENZAVIA'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_RESIDENZAVIA]', 'class' => 'ita-hidden-cell')),
                                array('text' => $elemento['DEFAULT_DICHIARANTE_RESIDENZACIVICO'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[DEFAULT_DICHIARANTE_RESIDENZACIVICO]', 'class' => 'ita-hidden-cell'))
                            );
                        }

                        $this->disegnaRicerca($html, 'ricPraticaPadre' . $Ricdag_rec['DAGSEQ'], $datiTabellaLookup, array(
                            'sortable' => true,
                            'paginated' => true,
                            'filters' => true
                                ), 'Elenco dichiaranti');
                    }
                    break;

                case 'Codfis_Anades':
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = 'readonly="readonly"';
                    }

                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");

                    require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibSoggetti.class.php';
                    $praLibSoggetti = new praLibSoggetti();
                    $anades_tab = $praLibSoggetti->getSoggettiFromAnades($dati['PRAM_DB'], $dati['Proric_rec']['RICFIS']);

                    /*
                     * Controllo se il passo ha funzione di anagrafica soggetto
                     * per trovare i campi su cui ribaltare i dati
                     */

                    require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praRuolo.class.php';

                    $campi_alias = array();
                    $campi_prefisso = 'DEFAULT';
                    if ($dati['Praclt_rec']['CLTOPEFO'] == 'FO_ANA_SOGGETTO') {
                        $metadata_praclt = unserialize($dati['Praclt_rec']['CLTMETA']);
                        if ($metadata_praclt && isset($metadata_praclt['METAOPEFO'])) {
                            $campi_prefisso = $metadata_praclt['METAOPEFO']['PREFISSO_CAMPI'];

                            foreach ($metadata_praclt['METAOPEFO'] as $metakey => $metavalue) {
                                if (strpos($metakey, 'ALIAS_') === 0 && $metavalue) {
                                    $campi_alias[substr($metakey, 6)] = $metavalue;
                                }
                            }
                        }
                    }

                    $metadata = unserialize($Ricdag_rec['DAGMETA']);
                    if ($metadata['PARAMSTIPODATO']['PREFISSO_CAMPI']) {
                        $campi_prefisso = $metadata['PARAMSTIPODATO']['PREFISSO_CAMPI'];
                    }
                    if ($metadata['PARAMSTIPODATO']) {
                        foreach ($metadata['PARAMSTIPODATO'] as $metakey => $metavalue) {
                            if (strpos($metakey, 'ALIAS_') === 0 && $metavalue) {
                                $campi_alias[substr($metakey, 6)] = $metavalue;
                            }
                        }
                    }

                    $html->appendHtml("<input style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"{$Ricdag_rec['DAGLEN']}\" size=\"{$Ricdag_rec['DAGDIM']}\" value=\"$value\" name=\"{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]\" id=\"{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]\" />");

                    //if ($anades_tab && $dati['ReadOnly'] != 1) {
                    if ($anades_tab && !$this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $datiTabellaLookup = array(
                            'header' => array(
                                'Cod. Fiscale',
                                'Cognome',
                                'Nome',
                                'Data di<br />nascita',
                                'Comune di<br / >nascita',
                                'Provincia di<br / >nascita'
                            ),
                            'body' => array()
                        );

                        $codiciFiscaliUtenti = array();
                        foreach ($anades_tab as $anades_rec) {
                            if (in_array($anades_rec['DESFIS'], $codiciFiscaliUtenti)) {
                                continue;
                            }

                            $codiciFiscaliUtenti[] = $anades_rec['DESFIS'];

                            if ($anades_rec['DESNASDAT']) {
                                $anades_rec['DESNASDAT'] = substr($anades_rec['DESNASDAT'], 6, 2) . '/' . substr($anades_rec['DESNASDAT'], 4, 2) . '/' . substr($anades_rec['DESNASDAT'], 0, 4);
                            }

                            /*
                             * Campi visualizzati
                             */

                            $tabellaLookupRecord = array(
                                $anades_rec['DESFIS'],
                                $anades_rec['DESCOGNOME'],
                                $anades_rec['DESNOME'],
                                $anades_rec['DESNASDAT'],
                                $anades_rec['DESNASCIT'],
                                $anades_rec['DESNASPROV']
                            );

                            /*
                             * Campi nascosti per mappatura soggetto
                             */

                            foreach (praRuolo::$SUBJECT_BASE_FIELDS as $nome_campo => $nome_colonna) {
                                if (isset($campi_alias[$nome_campo]) && $campi_alias[$nome_campo]) {
                                    $nome_campo = $campi_alias[$nome_campo];
                                }

                                $nome_campo_completo = "{$campi_prefisso}_{$nome_campo}";
                                $tabellaLookupRecord[] = array('text' => $anades_rec[$nome_colonna], 'attrs' => array('data-ita-edit-ref' => "{$fieldPrefix}[$nome_campo_completo]", 'class' => 'ita-hidden-cell'));
                            }

                            $datiTabellaLookup['body'][] = $tabellaLookupRecord;
                        }

                        $this->disegnaRicerca($html, 'ricCodfisAnades' . $Ricdag_rec['DAGSEQ'], $datiTabellaLookup, array(
                            'sortable' => true,
                            'paginated' => true,
                            'filters' => true
                                ), 'Elenco soggetti');
                    }
                    break;

                case 'Ruolo':
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = 'readonly="readonly"';
                    }

                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");

                    $sql = "SELECT RUOCOD, RUODES FROM ANARUO WHERE 1 = 1";
                    $metadata = unserialize($Ricdag_rec['DAGMETA']);
                    if ($metadata['PARAMSTIPODATO']['CODICE']) {
                        $FiltriQuery = array();
                        $CodiciRuolo = explode(',', $metadata['PARAMSTIPODATO']['CODICE']);
                        foreach ($CodiciRuolo as $CodiceRuolo) {
                            list($RangeStart, $RangeEnd) = explode('-', $CodiceRuolo, 2);
                            if ($RangeEnd) {
                                $FiltriQuery[] = "RUOCOD >= $RangeStart AND RUOCOD <= $RangeEnd";
                            } else {
                                $FiltriQuery[] = "RUOCOD = $CodiceRuolo";
                            }
                        }

                        $sql .= " AND (" . implode(' OR ', $FiltriQuery) . ")";
                    }

                    $anaruo_tab = ItaDB::DBSQLSelect($dati['PRAM_DB'], $sql . " ORDER BY RUODES", true);
                    if ($anaruo_tab) {
                        $readonly = 'readonly="readonly"';
                    }

                    $html->appendHtml("<input type=\"hidden\" value=\"$value\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" />");

                    $valueDescRuolo = '';
                    if ($anaruo_tab) {
                        foreach ($anaruo_tab as $anaruo_rec) {
                            if ($anaruo_rec['RUOCOD'] == $value) {
                                $valueDescRuolo = $anaruo_rec['RUODES'];
                            }
                        }
                    }

                    $html->appendHtml("<input type=\"text\" value=\"$valueDescRuolo\" $readonly size=\"50\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "_DESCRIZIONEDELRUOLO]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "_DESCRIZIONEDELRUOLO]\" />");

                    //if ($anaruo_tab && $dati['ReadOnly'] != 1) {
                    if ($anaruo_tab && !$this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $html->appendHtml("<div style=\"display: inline-block; vertical-align: middle; padding: 5px 8px;\">
                                                    <span title=\"Cancella\" style=\"cursor: pointer;\" class=\"icon ion-close italsoft-icon\" onclick=\"jQuery('#{$fieldPrefix}\\\\[" . $Ricdag_rec['DAGKEY'] . "_DESCRIZIONEDELRUOLO\\\\]').val('');\"></span>
                                                 </div>");

                        $elencoTableData = array(
                            'caption' => 'Elenco dei ruoli',
                            'header' => array('Descrizione'),
                            'body' => array()
                        );

                        foreach ($anaruo_tab as $anaruo_rec) {
                            $elencoTableData['body'][] = array(
                                array('text' => $anaruo_rec['RUOCOD'], 'attrs' => array('class' => 'ita-hidden-cell', 'data-ita-edit-ref' => $fieldPrefix . '[' . $Ricdag_rec['DAGKEY'] . ']')),
                                array('text' => $anaruo_rec['RUODES'], 'attrs' => array('class' => 'ita-hidden-cell', 'data-ita-edit-ref' => $fieldPrefix . '[' . $Ricdag_rec['DAGKEY'] . '_DESCRIZIONEDELRUOLO]')),
                                array('text' => $anaruo_rec['RUODES'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[' . $Ricdag_rec['DAGKEY'] . '_DESCRIZIONE]'))
                            );
                        }

                        $this->disegnaRicerca($html, 'ricElencoRuoli' . $Ricdag_rec['DAGSEQ'], $elencoTableData, array(
                            'sortable' => true,
                            'filters' => true,
                            'paginated' => true
                        ));
                    }
                    break;

                case 'Comune':
                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<input type=\"text\" style=\"$styleFldBO\" class=\"$classFldBO\" readonly=\"readonly\" maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" />");

                    $html->appendHtml("<div style=\"display: inline-block; vertical-align: middle; padding: 5px 8px;\">
                                                    <span title=\"Cancella\" style=\"cursor: pointer;\" class=\"icon ion-close italsoft-icon\" onclick=\"jQuery('#{$fieldPrefix}\\\\[" . $Ricdag_rec['DAGKEY'] . "\\\\]').val('');\"></span>
                                                 </div>");

                    $elencoTableData = array(
                        'header' => array(
                            'Comune',
                            'Provincia',
                            'CAP',
                            'ISTAT'
                        ),
                        'body' => array()
                    );

                    $attrs = array(
                        'id' => 'ricComuni'
                    );

                    $sql = "SELECT COMUNE, PROVIN, COAVPO, CISTAT FROM COMUNI WHERE 1 = 1";

                    $metadata = unserialize($Ricdag_rec['DAGMETA']);

                    if ($metadata['PARAMSTIPODATO']['REGIONE']) {
                        $sql .= " AND REGIONE = '" . addslashes($metadata['PARAMSTIPODATO']['REGIONE']) . "'";
                        $attrs['data-filter-regione'] = $metadata['PARAMSTIPODATO']['REGIONE'];
                    }

                    if ($metadata['PARAMSTIPODATO']['PROVINCIA']) {
                        $sql .= " AND PROVIN = '" . addslashes($metadata['PARAMSTIPODATO']['PROVINCIA']) . "'";
                        $attrs['data-filter-provincia'] = $metadata['PARAMSTIPODATO']['PROVINCIA'];
                    }

                    if ($metadata['PARAMSTIPODATO']['ESCLUDI']) {
                        $listaISTAT = array_map('trim', explode(',', $metadata['PARAMSTIPODATO']['ESCLUDI']));
                        $sql .= " AND CISTAT NOT IN ('" . implode("', '", $listaISTAT) . "')";
                        $attrs['data-filter-escludi'] = $metadata['PARAMSTIPODATO']['ESCLUDI'];
                    }

                    $dagkey_pv = $metadata['PARAMSTIPODATO']['CAMPO_PV'] ?: $Ricdag_rec['DAGKEY'] . '_PV';
                    $dagkey_cap = $metadata['PARAMSTIPODATO']['CAMPO_CAP'] ?: $Ricdag_rec['DAGKEY'] . '_CAP';
                    $dagkey_istat = $metadata['PARAMSTIPODATO']['CAMPO_ISTAT'] ?: $Ricdag_rec['DAGKEY'] . '_ISTAT';

                    $comuni_tab = ItaDB::DBSQLSelect(ItaDB::DBOpen('COMUNI', ''), $sql);
                    if (count($comuni_tab) < 500) {
                        foreach ($comuni_tab as $comuni_rec) {
                            $elencoTableData['body'][] = array(
                                array('text' => $comuni_rec['COMUNE'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[' . $Ricdag_rec['DAGKEY'] . ']')),
                                array('text' => $comuni_rec['PROVIN'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[' . $dagkey_pv . ']')),
                                array('text' => $comuni_rec['COAVPO'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[' . $dagkey_cap . ']')),
                                array('text' => $comuni_rec['CISTAT'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[' . $dagkey_istat . ']'))
                            );
                        }

                        $this->disegnaRicerca($html, 'ricComuni' . $Ricdag_rec['DAGSEQ'], $elencoTableData, array(
                            'sortable' => true,
                            'filters' => true
                        ));
                        break;
                    }

                    $this->disegnaRicerca($html, 'ricComuni' . $Ricdag_rec['DAGSEQ'], $elencoTableData, array(
                        'sortable' => true,
                        'filters' => true,
                        'paginated' => true,
                        'ajax' => true,
                        'attrs' => $attrs,
                        'selectable' => array(
                            $fieldPrefix . '[' . $Ricdag_rec['DAGKEY'] . ']',
                            $fieldPrefix . '[' . $dagkey_pv . ']',
                            $fieldPrefix . '[' . $dagkey_cap . ']',
                            $fieldPrefix . '[' . $dagkey_istat . ']'
                        )
                            ), 'Elenco Comuni');
                    break;

                case 'Indir_InsProduttivo':
                    $meta = unserialize($Ricdag_rec["DAGMETA"]);
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = "readonly=\"readonly\"";
                    }
                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");

                    $ITALWEB_DB = ItaDB::DBOpen('ITALWEB', frontOfficeApp::getEnte());
                    $sql = "SELECT ANADES FROM ANA_COMUNE WHERE ANACAT='VIEFO' ORDER BY ANADES";
                    //
                    switch ($meta['PARAMSTIPODATO']['ANACAT']) {
                        case "VIEEVT":
                            $sql = "SELECT ANADES FROM ANA_COMUNE WHERE ANACAT='" . $meta['PARAMSTIPODATO']['ANACAT'] . "' ORDER BY ANADES";
                            break;
                        case "VIEALL":
                            $sql = "SELECT * FROM (SELECT ANADES, ANACAT FROM ANA_COMUNE WHERE ANACAT='VIEEVT' ORDER BY ANADES) AS EVT
                                                    UNION 
                                                SELECT * FROM (SELECT ANADES, ANACAT FROM ANA_COMUNE WHERE ANACAT='VIEFO' ORDER BY ANADES) AS FO
                                                ";
                            break;
                        default:
                            $sql = "SELECT ANADES FROM ANA_COMUNE WHERE ANACAT='VIEFO' ORDER BY ANADES";
                            break;
                    }

                    $indirizzi_tab = ItaDB::DBSQLSelect($ITALWEB_DB, $sql, true);
                    if ($indirizzi_tab) {
                        $readonly = "readonly=\"readonly\"";
                    }
                    $html->appendHtml("<input type=\"$type\" style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" />");
                    //if ($indirizzi_tab && $dati['ReadOnly'] != 1) {
                    if ($indirizzi_tab && !$this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $html->appendHtml("<div style=\"display: inline-block; vertical-align: middle; padding: 5px 8px;\">
                                                    <span title=\"Cancella\" style=\"cursor: pointer;\" class=\"icon ion-close italsoft-icon\" onclick=\"jQuery('#{$fieldPrefix}\\\\[" . $Ricdag_rec['DAGKEY'] . "\\\\]').val('');\"></span>
                                                 </div>");

                        $elencoTableData = array(
                            'caption' => 'Elenco delle vie',
                            'header' => array('Descrizione'),
                            'body' => array()
                        );

                        foreach ($indirizzi_tab as $indirizzi_rec) {
                            if ($indirizzi_rec['ANACAT'] == 'VIEEVT') {
                                $elencoTableData['body'][] = array(array('text' => $indirizzi_rec['ANADES'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[' . $Ricdag_rec['DAGKEY'] . ']', 'style' => 'font-weight: bold;')));
                            } else {
                                $elencoTableData['body'][] = array(array('text' => $indirizzi_rec['ANADES'], 'attrs' => array('data-ita-edit-ref' => $fieldPrefix . '[' . $Ricdag_rec['DAGKEY'] . ']')));
                            }
                        }

                        $this->disegnaRicerca($html, 'ricElencoVie' . $Ricdag_rec['DAGSEQ'], $elencoTableData, array(
                            'sortable' => true,
                            'filters' => true,
                            'paginated' => true
                        ));
                    }
                    break;

                case 'Sportello_Aggregato':
                    //if ($dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $disabled = "disabled=\"disabled\"";
                    }
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\">" . $Ricdag_rec['DAGDES'] . "</label>");
                    $html->appendHtml("<select name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}_" . $Ricdag_rec['DAGKEY'] . "\" $disabled>");
                    $html->appendHtml("<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>");
                    $arrayOptions = $dati['Anaspa_tab'];
                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);
                    foreach ($arrayOptions as $key => $option) {
                        $Sel = "";
                        if ($value == $option['SPACOD']) {
                            $Sel = "selected";
                        }
                        $html->appendHtml("<option $Sel class=\"optSelect\" value=\"" . $option['SPACOD'] . "\">" . $option['SPADES'] . "</option>");
                    }
                    $html->appendHtml("</select>");
                    break;

                case 'Evento_Richiesta':
                    //if ($dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $disabled = "disabled=\"disabled\"";
                    }

                    $html->appendHtml("<label class=\"$classPosLabel ita-label\">" . $Ricdag_rec['DAGDES'] . "</label>");
                    $html->appendHtml("<select name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}_" . $Ricdag_rec['DAGKEY'] . "\" $disabled>");

                    $arrayOptions = array();

                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php');
                    $praLibEventi = new praLibEventi();
                    $Iteevt_tab = $praLibEventi->getEventi($dati['PRAM_DB'], $dati['Anapra_rec']['PRANUM'], $dati['Proric_rec']['RICDRE']);
                    foreach ($Iteevt_tab as $Iteevt_rec) {
                        $arrayOptions[$Iteevt_rec['IEVCOD']] = $praLibEventi->getOggetto($dati['PRAM_DB'], $dati['Anapra_rec'], $Iteevt_rec);
                    }

                    foreach ($arrayOptions as $value => $option) {
                        $Sel = "";

                        if ($dati['Proric_rec']['RICEVE'] == $value) {
                            $Sel = "selected";
                        }

                        $html->appendHtml("<option $Sel class=\"optSelect\" value=\"$value\">$option</option>");
                    }
                    $html->appendHtml("</select>");
                    break;

                case 'Denom_FieraBando':
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    $html->appendHtml($praLibGfm->GetHtmlPassoFiereBando($dati['ReadOnly'], $Ricdag_rec, $dati['BandiFiere_tab']));
                    break;

                case 'Denom_FieraPBando':
                    $suffix = ITA_DB_SUFFIX;
                    if (file_exists(ITA_SUAP_PATH . "/SUAP_italsoft/FiereMercatiBando.$suffix.class.php")) {
                        require ITA_SUAP_PATH . "/SUAP_italsoft/FiereMercatiBando.$suffix.class.php";
                        $obj = new FiereMercati();
                        $dati['BandiFiereP_tab'] = $obj->GetSqlFiere($dati);
                    }
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    $html->appendHtml($praLibGfm->GetHtmlPassoFiereBando($dati['ReadOnly'], $Ricdag_rec, $dati['BandiFiereP_tab']));
                    break;

                case 'Denom_MercatoBando':
                    $suffix = ITA_DB_SUFFIX;
                    if (file_exists(ITA_SUAP_PATH . "/SUAP_italsoft/FiereMercatiBando.$suffix.class.php")) {
                        require ITA_SUAP_PATH . "/SUAP_italsoft/FiereMercatiBando.$suffix.class.php";
                        $obj = new FiereMercati();
                        $dati['BandiMercati_tab'] = $obj->GetSqlMercati($dati);
                    }
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    $html->appendHtml($praLibGfm->GetHtmlPassoFiereBando($dati['ReadOnly'], $Ricdag_rec, $dati['BandiMercati_tab']));
                    break;

                case 'Denom_PIBando':
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    $html->appendHtml($praLibGfm->GetHtmlPassoFiereBando($dati['ReadOnly'], $Ricdag_rec, $dati['BandiPosteggiIsolati_tab']));
                    break;

                case 'Denom_Fiera':
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    $html->appendHtml($praLibGfm->GetHtmlPassoFiere($dati, $Ricdag_rec));
                    break;

                case 'Posteggi_fiera':
                    require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibGfm.class.php');
                    $praLibGfm = new praLibGfm();
                    $html->appendHtml($praLibGfm->GetHtmlPassoPosteggiFiere($dati, $Ricdag_rec));
                    break;

                case 'Data':
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = "readonly=\"readonly\"";
                    }
                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<input class=\"ita-datepicker ita-edit $classFldBO\" style=\"$styleFldBO\" $readonly maxlength=\"10\" size=\"12\" value=\"$value\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\"/>");
                    break;

                case 'Time':
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = "readonly=\"readonly\"";
                    }
                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);
                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<input class=\"ita-time ita-edit $classFldBO\" style=\"$styleFldBO\" $readonly maxlength=\"5\" size=\"7\" value=\"$value\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\"/>");
                    break;

                case 'Importo':
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = "readonly=\"readonly\"";
                    }

                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];

                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<input type=\"text\" class=\"italsoft-input--currency ita-edit\" style=\"text-align: right; $styleFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\"/> &euro;");
                    break;

                case 'Hidden':
                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);
                    $html->appendHtml("<input type=\"hidden\" class=\"$classFldBO\" maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\"/>");
                    break;

                case 'Password':
                    $type = "password";
                case 'Text':
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = "readonly=\"readonly\"";
                    }
                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<input type=\"$type\" style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\"/>");
                    break;

                case 'TextArea':
                    $cols = $meta['ATTRIBUTICAMPO']['COLS'];
                    $rows = $meta['ATTRIBUTICAMPO']['ROWS'];
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = "readonly";
                    }

                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    //$html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<textarea class=\"ita-edit $classFldBO\" style=\"$styleFldBO\" $readonly cols=\"$cols\" rows=\"$rows\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\">$value</textarea>");
                    break;

                case 'Select':
                    //if ($dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $disabled = "disabled=\"disabled\"";
                    }
                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<select class=\"italsoft-input $classFldBO\" style=\"$styleFldBO\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" $disabled>");
                    $html->appendHtml("<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>");

                    $optionsDefaults = $Ricdag_rec['DAGVAL'];
                    if (strpos($optionsDefaults['DAGVAL'], '^') !== false) {
                        list($optionsDefaults, $skip) = explode("^", $optionsDefaults);
                    }
                    $arrayOptions = explode("|", $optionsDefaults);

                    switch ($Ricdag_rec['DAGDIZ']) {
                        case 'D':
                            $arrayOptions = array();
                            $DatiDizionario = $dati['Dizionario']->getAllData();
                            list($Key1, $Key2) = explode(".", $optionsDefaults);

                            foreach ($DatiDizionario[$Key1] as $KeyDict => $ValDict) {
                                if (strpos($KeyDict, $Key2 . '_') === 0 && strlen($KeyDict) === (strlen($Key2) + 3)) {
                                    $Index = intval(substr($KeyDict, -2));
                                    $arrayOptions[$Index] = $ValDict;
                                }
                            }
                            break;

                        case 'T':
                            $DatiDizionario = $dati['Dizionario']->getAllData();
                            $strTemplate = $optionsDefaults;
                            $arrayOptions = array();

                            /*
                             * Ricavo le variabili dal template
                             */
                            preg_match_all('/@{.*?\$([A-Z0-9._]*).*?}@/', $strTemplate, $Matches);

                            /*
                             * Ciclo le variabili matchate, per ogni variabili
                             * ciclo il suo dizionario in cerca delle chiavi
                             * formate da "VARIABILE" + "_XY".
                             * Utilizzo il valore XY come indice per $arrayOptions,
                             * ed aggiungo come valore il template (o l'$arrayOptions al medesimo indice se presente)
                             * sostituendo la variabile
                             * con il valore da dizionario
                             */
                            foreach ($Matches[1] as $matchKey => $Match) {
                                list($Key1, $Key2) = explode(".", $Match);
                                foreach ($DatiDizionario[$Key1] as $KeyDict => $ValDict) {
                                    if (strpos($KeyDict, $Key2 . '_') === 0 && strlen($KeyDict) === (strlen($Key2) + 3)) {
                                        $Index = intval(substr($KeyDict, -2));
                                        $arrayOptions[$Index] = str_replace($Match, $Match . '_' . substr($KeyDict, -2), (isset($arrayOptions[$Index]) ? $arrayOptions[$Index] : $strTemplate));
                                    }
                                }
                            }

                            foreach ($arrayOptions as $optKey => $Option) {
                                $valoreTemplate = $this->praLib->valorizzaTemplate($Option, $DatiDizionario);

                                if (!trim($valoreTemplate)) {
                                    unset($arrayOptions[$optKey]);
                                } else {
                                    $arrayOptions[$optKey] = $valoreTemplate;
                                }
                            }

                            ksort($arrayOptions);
                            break;
                    }

                    foreach ($arrayOptions as $key => $option) {
                        $default = false;
                        if (substr($option, 0, 1) == "#") {
                            $option = str_replace("#", "", $option);
                            if ($Ricdag_rec['RICDAT'] == "") {
                                $default = true;
                            }
                        }
                        $nodevalue = $option;
                        if (strpos($option, ":")) {
                            list($option, $nodevalue) = explode(":", $option);
                        }
                        $Sel = "";
                        // $stripRicdat = stripslashes($Ricdag_rec['RICDAT']);

                        $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                        if ($value == $option || $default) {
                            $Sel = "selected";
                        }



//                            if ($Ricdag_rec['RICDAT'] == $option)
//                                $Sel = "selected";
                        $html->appendHtml("<option $Sel class=\"optSelect\" value=\"$option\">$nodevalue</option>");
                    }

                    $html->appendHtml("</select>");
                    break;

                case 'Html';
                    $meta = unserialize($Ricdag_rec["DAGMETA"]);
                    if ($meta['HTMLPOS'] == "Default") {
                        $html->appendHtml($defaultValue);
                    }
                    break;

                case 'RadioButton';
                    $br = '';
                    break;

                case 'RadioGroup';
                    foreach ($dati['Ricdag_tab'] as $key => $RicdagRadioButton_rec) {
                        $RicdagRadioButton_rec = $this->praLib->ctrRicdagRec($RicdagRadioButton_rec, $dati['Dizionario']->getAlldataPlain("", "."));

                        /*
                         * Riassegno gli style e la posizione della label perchè devono prendere quelli di ciascun RadioButton
                         */
                        $styleLblBO = $RicdagRadioButton_rec['DAGLABSTYLE'];
                        $styleFldBO = $RicdagRadioButton_rec['DAGFIELDSTYLE'];
                        switch ($RicdagRadioButton_rec['DAGPOS']) {
                            case "":
                            case "Sinistra":
                                $classPosLabel = "ita-label-sx";
                                break;
                            case "Destra":
                                $classPosLabel = "ita-label-dx";
                                break;
                            case "Sopra":
                                $classPosLabel = "ita-label-top";
                                break;
                            case "Sotto":
                                $classPosLabel = "ita-label-bot";
                                break;
                        }

                        //
                        if ($RicdagRadioButton_rec['DAGTIC'] !== 'RadioButton') {
                            continue;
                        }
                        if (is_array($RicdagRadioButton_rec['DAGMETA'])) {
                            $metaRadioButton = $RicdagRadioButton_rec['DAGMETA'];
                        } else {
                            $metaRadioButton = unserialize($RicdagRadioButton_rec['DAGMETA']);
                        }
                        if ($metaRadioButton['ATTRIBUTICAMPO']['NAME'] !== $Ricdag_rec['DAGKEY']) {
                            continue;
                        }

                        /*
                         * Setting del value di ritorno e dell attributo checked
                         */
                        $defaultValueRadioGroup = $Ricdag_rec['DAGVAL'];
                        $returnValueRadioButton = $metaRadioButton['ATTRIBUTICAMPO']['RETURNVALUE'];

                        $brRadioButton = "";
                        if ($RicdagRadioButton_rec['DAGACA'] == 1) {
                            $brRadioButton = "<br>";
                        }

                        $disabled = '';
                        //if ($RicdagRadioButton_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                        if ($this->praLib->getReadOnly($dati, $RicdagRadioButton_rec['DAGROL'])) {
                            $disabled = "disabled";
                        }

                        $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                        $checked = "";
                        if ($value === $returnValueRadioButton) {
                            $checked = "checked";
                        } else {
                            if ($value === '' && $defaultValueRadioGroup !== '' && $returnValueRadioButton == $defaultValueRadioGroup) {
                                $checked = "checked";
                            }
                        }
                        $etichetta = $RicdagRadioButton_rec['DAGLAB'] ? $RicdagRadioButton_rec['DAGLAB'] : $RicdagRadioButton_rec['DAGDES'];
                        if ($classPosLabel != "ita-label-bot" && $classPosLabel != "ita-label-dx") {
                            $html->appendHtml("<label class=\"ita-label $classPosLabel\" style=\"$styleLblBO\">$etichetta $campoObl</label>");
                            $html->appendHtml("<input class=\"ita-edit $classFldBO\" style=\"margin-right:20px;$styleFldBO\" type=\"radio\" $checked $disabled value=\"$returnValueRadioButton\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" />");
                        } else {
                            if ($classPosLabel == 'ita-label-dx') {
                                $classPosLabel = '';
                                $styleLblBO = 'display: inline-block;' . $styleLblBO;
                            }
                            $html->appendHtml("<input class=\"ita-edit $classFldBO\" style=\"margin-right:20px;$styleFldBO\" type=\"radio\" $checked $disabled value=\"$returnValueRadioButton\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" />");
                            $html->appendHtml("<label class=\"ita-label $classPosLabel\" style=\"$styleLblBO\">$etichetta $campoObl</label>");
                        }
                        $html->appendHtml($brRadioButton);
                    }
                    break;

                case 'CheckBox';
                    $returnValues = $meta['ATTRIBUTICAMPO']['RETURNVALUES'];
                    $arrayReturVal = explode("/", $returnValues);
                    $valCheched = $arrayReturVal[0];
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $disabled = "disabled";
                    }
                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);
                    if ($value == "1" || $value == "On" || $value == "Si" || $Ricdag_rec['RICDAT'] == "1" || $Ricdag_rec['RICDAT'] == "On" || $Ricdag_rec['RICDAT'] == "Si") {
                        $checked = "checked=\"yes\"";
                    } else {
                        if ($value == "" && $defaultValue && $defaultValue == $valCheched) {
                            $checked = "checked=\"yes\"";
                        }
                    }

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];

                    if ($dati['Ricite_rec']['ITESOSTF'] == '1') {
                        $sql = "SELECT ITEMETA FROM RICITE WHERE RICNUM = '{$dati['Ricite_rec']['RICNUM']}' AND RICSHA2SOST != '' AND ITEATE LIKE '%\"PRAAGGIUNTIVI." . $Ricdag_rec['DAGKEY'] . "\"%'";
                        $ricite_rec_upload = ItaDB::DBSQLSelect($dati["PRAM_DB"], $sql, false);
                        $data_file_sostituzione = unserialize($ricite_rec_upload['ITEMETA']);
                        if ($data_file_sostituzione[$this->praLibSostituzioni->metaKey]) {
                            $styleLblBO .= ' max-width: 600px;';
                            $etichetta = $this->praLibSostituzioni->htmlLabelCheckbox($data_file_sostituzione[$this->praLibSostituzioni->metaKey], $dati['PRAM_DB']);
                        }
                    }

                    $labelFor = "{$fieldPrefix}[{$Ricdag_rec['DAGKEY']}]";
                    if ($classPosLabel != "ita-label-bot" && $classPosLabel != "ita-label-dx") {
                        $html->appendHtml("<label for=\"$labelFor\" class=\"ita-label $classPosLabel\" style=\"$styleLblBO\">$etichetta $campoObl</label>");
                        $html->appendHtml("<input class=\"ita-edit $classFldBO\" style=\"margin-right:20px;$styleFldBO\" type=\"checkbox\" $checked $disabled name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" />");
                    } else {
                        if ($classPosLabel == 'ita-label-dx') {
                            $classPosLabel = '';
                            $styleLblBO = 'display: inline-block;' . $styleLblBO;
                        }
                        $html->appendHtml("<input class=\"ita-edit $classFldBO\" style=\"margin-right:20px;$styleFldBO\" type=\"checkbox\" $checked $disabled name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" />");
                        $html->appendHtml("<label for=\"$labelFor\" class=\"ita-label $classPosLabel\" style=\"$styleLblBO\">$etichetta $campoObl</label>");
                    }
                    break;

                case 'Forma_Giuridica':
                    $arrayOptions = $this->praLib->GetArrayFormaGiuridica();
                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    //if ($dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $disabled = "disabled=\"disabled\"";
                    }
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<select name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}_" . $Ricdag_rec['DAGKEY'] . "\" $disabled>");
                    $html->appendHtml("<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>");
                    foreach ($arrayOptions as $key => $option) {
                        $Sel = "";
                        if ($Ricdag_rec['RICDAT'] == $option['CODICE']) {
                            $Sel = "selected";
                        }
                        $html->appendHtml("<option $Sel class=\"optSelect\" value=\"" . $option['CODICE'] . "\">" . $option['DESCRIZIONE'] . "</option>");
                    }
                    $html->appendHtml("</select>");
                    break;

                case 'Carica':
                    $arrayOptions = $this->praLib->GetArrayCariche();
                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    //if ($dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $disabled = "disabled=\"disabled\"";
                    }
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<select name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}_" . $Ricdag_rec['DAGKEY'] . "\" $disabled>");
                    $html->appendHtml("<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>");
                    foreach ($arrayOptions as $key => $option) {
                        $Sel = "";
                        if ($Ricdag_rec['RICDAT'] == $option['CODICE']) {
                            $Sel = "selected";
                        }
                        $html->appendHtml("<option $Sel class=\"optSelect\" value=\"" . $option['CODICE'] . "\">" . $option['DESCRIZIONE'] . "</option>");
                    }
                    $html->appendHtml("</select>");
                    break;

                case 'Qualifica':
                    $arrayOptions = $this->praLib->GetArrayQualifiche();
                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    //if ($dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $disabled = "disabled=\"disabled\"";
                    }
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<select name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}_" . $Ricdag_rec['DAGKEY'] . "\" $disabled>");
                    $html->appendHtml("<option class=\"optSelect\" value=\"\">Seleziona una scelta</option>");
                    foreach ($arrayOptions as $key => $option) {
                        $Sel = "";
                        if ($Ricdag_rec['RICDAT'] == $option['CODICE']) {
                            $Sel = "selected";
                        }
                        $html->appendHtml("<option $Sel class=\"optSelect\" value=\"" . $option['CODICE'] . "\">" . $option['DESCRIZIONE'] . "</option>");
                    }
                    $html->appendHtml("</select>");
                    break;

                case 'Ricerca_Generica':
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = "readonly=\"readonly\"";
                    }

                    $value = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue);

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<input type=\"$type\" style=\"$styleFldBO\" class=\"$classFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" value=\"$value\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" />");

                    //if ($dati['ReadOnly'] != 1) {
                    if (!$this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $html->appendHtml("<div style=\"display: inline-block; vertical-align: middle; padding: 5px 8px;\">");
                        $html->appendHtml("<span title=\"Cancella\" style=\"cursor: pointer;\" class=\"icon ion-close italsoft-icon\" onclick=\"jQuery('#{$fieldPrefix}\\\\[" . $Ricdag_rec['DAGKEY'] . "\\\\]').val('');\"></span>");
                        $html->appendHtml("</div>");

                        $id = 'ric' . $Ricdag_rec['DAGKEY'] . $Ricdag_rec['DAGSEQ'];
                        $onClick = "onclick=\"itaFrontOffice.ajax(ajax.action, ajax.model, 'onClick', this, { id: '{$Ricdag_rec['DAGKEY']}_butt'}); event.preventDefault();\"";

                        $html->appendHtml("
                            <div style=\"display: inline-block; vertical-align: middle; margin-left: 5px; font-size: 1.6em; cursor: pointer;\" $onClick>
                                <span title=\"Ricerca\" class=\"icon ion-search italsoft-icon\" data-ricerca=\"$id\"></span>
                            </div>
                        ");
                    }
                    break;

                case 'Button':
                    //if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                    if ($this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        $readonly = "disabled=\"disabled\"";
                    }

                    $metadataCampo = unserialize($Ricdag_rec['DAGMETA']);
                    $eventName = $metadataCampo['ATTRIBUTICAMPO']['RETURNVALUE'] ?: $Ricdag_rec['DAGKEY'];
                    $eventValue = $this->setFieldValue($dati, $Ricdag_rec, $defaultValue) ?: $Ricdag_rec['DAGKEY'];
                    $classFormSubmit = 'ita-form-submit';
                    $onClick = '';

                    if (strpos($eventName, ':') !== false) {
                        list($param, $eventName) = explode(':', $eventName);
                        if (strtolower($param) === 'ajax') {
                            $classFormSubmit = '';
                            $onClick = "onclick=\"itaFrontOffice.ajax(ajax.action, ajax.model, '$eventValue', this); event.preventDefault();\"";
                        }
                    }

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<button type=\"submit\" style=\"$styleFldBO\" class=\"italsoft-button $classFormSubmit $classFldBO\" $readonly value=\"$eventValue\" name=\"$eventName\" id=\"{$Ricdag_rec['DAGKEY']}\" $onClick>$etichetta</button>");
                    break;

                case 'Tabella_Generica':
                    $headersDef = array();
                    $metadataCampo = unserialize($Ricdag_rec['DAGMETA']);

                    $tableData = array(
                        'caption' => $metadataCampo['PARAMSTIPODATO']['CAPTION'] ?: '',
                        'header' => array(),
                        'body' => array()
                    );

                    foreach ($metadataCampo['PARAMSTIPODATO'] as $key => $value) {
                        if (strpos($key, 'HEADER_') === 0) {
                            list(, $idx) = explode('_', $key, 2);
                            $headersDef[(int) $idx] = json_decode($value, true);
                        }
                    }

                    ksort($headersDef);

                    foreach ($headersDef as $i => $header) {
                        $tableData['header'][$i] = array('text' => $header['TITLE'], 'attrs' => array('data-key' => $header['KEY']));

                        if (isset($header['FILTER']) && !$header['FILTER']) {
                            $tableData['header'][$i]['attrs']['class'] = 'filter-false';
                        }

                        if (isset($header['SORTER']) && !$header['SORTER']) {
                            $tableData['header'][$i]['attrs']['data-sorter'] = 'false';
                        }
                    }

                    if ($Ricdag_rec['RICDAT'] && is_array($Ricdag_rec['RICDAT'])) {
                        foreach ($Ricdag_rec['RICDAT'] as $i => $record) {
                            $tableData['body'][$i] = array();
                            foreach ($headersDef as $header) {
                                $tableData['body'][$i][] = isset($record[$header['KEY']]) ? $record[$header['KEY']] : '';
                            }
                        }
                    }

                    $tableParams = array(
                        'sortable' => true,
                        'filters' => isset($metadataCampo['PARAMSTIPODATO']['FILTERS']) ? (boolean) $metadataCampo['PARAMSTIPODATO']['FILTERS'] : false,
                        'paginated' => isset($metadataCampo['PARAMSTIPODATO']['PAGINATED']) ? (boolean) $metadataCampo['PARAMSTIPODATO']['PAGINATED'] : true,
                        'ajax' => isset($metadataCampo['PARAMSTIPODATO']['AJAX']) ? (boolean) $metadataCampo['PARAMSTIPODATO']['AJAX'] : false,
                        'attrs' => array('id' => $Ricdag_rec['DAGKEY'], 'style' => 'width: 100%;')
                    );

                    //if ($dati['ReadOnly'] != 1) {
                    if (!$this->praLib->getReadOnly($dati, $Ricdag_rec['DAGROL'])) {
                        if ($metadataCampo['PARAMSTIPODATO']['BUTTONADD']) {
                            $tableParams['buttonAdd'] = true;
                        }

                        if ($metadataCampo['PARAMSTIPODATO']['BUTTONEDIT']) {
                            $tableParams['buttonEdit'] = true;
                        }

                        if ($metadataCampo['PARAMSTIPODATO']['BUTTONDEL']) {
                            $tableParams['buttonDel'] = true;
                        }
                    }

                    if ($metadataCampo['PARAMSTIPODATO']['SORTNAME']) {
                        $tableParams['sort-column'] = $metadataCampo['PARAMSTIPODATO']['SORTNAME'];
                    }

                    if ($metadataCampo['PARAMSTIPODATO']['SORTORDER']) {
                        $tableParams['sort-order'] = $metadataCampo['PARAMSTIPODATO']['SORTORDER'];
                    }

                    $html->addTable($tableData, $tableParams);
                    break;

                case 'FileUpload':
                    if ($Ricdag_rec['DAGROL'] == 1 || $dati['ReadOnly'] == 1) {
                        $readonly = "readonly=\"readonly\"";
                    }

                    $etichetta = $Ricdag_rec['DAGLAB'] ? $Ricdag_rec['DAGLAB'] : $Ricdag_rec['DAGDES'];
                    $html->appendHtml("<label class=\"$classPosLabel ita-label\" style=\"text-align:right;display:inline-block;align:right;$styleLblBO\">$etichetta $campoObl</label>");
                    $html->appendHtml("<input type=\"file\" class=\"ita-edit\" style=\"text-align: right; $styleFldBO\" $readonly maxlength=\"" . $Ricdag_rec['DAGLEN'] . "\" size=\"" . $Ricdag_rec['DAGDIM'] . "\" name=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\" id=\"{$fieldPrefix}[" . $Ricdag_rec['DAGKEY'] . "]\"/>;");
                    break;

                default:
                    break;
            }
        }

        if (trim($Ricdag_rec['DAGNOT'])) {
            $html->appendHtml('<span class="italsoft-tooltip--click" title="' . htmlentities(nl2br($Ricdag_rec['DAGNOT']), ENT_COMPAT | ENT_HTML5, 'ISO-8859-1') . '">');
            $html->appendHtml($html->getImage(frontOfficeLib::getIcon('info'), '25px'));
            $html->appendHtml('</span>');
        }

        $html->appendHtml("</div>");
        $html->appendHtml($br);
        if ($this->column) {
            $html->appendHtml("</td>");
            if ($this->column == $this->contaColumn) {
                $html->appendHtml("</tr>");
                $this->contaColumn = 0;
            }
        }
    }

    private function setFieldValue($dati, $Ricdag_rec, $defaultValue) {
        $value = "";
        if ($Ricdag_rec['RICDAT'] != "" || $dati['ReadOnly'] == 1) {
            $value = $Ricdag_rec['RICDAT'];
        } else {
            if ($defaultValue !== '') {
                $value = $defaultValue;
            }
        }
        return htmlspecialchars($value, ENT_COMPAT | ENT_HTML401, 'ISO-8859-1');
    }

    public function disegnaRicerca($html, $id, $tableData, $tableDataParams, $title = 'Elenco') {
        $html->appendHtml("
<div style=\"display: inline-block; vertical-align: middle; margin-left: 5px; font-size: 1.6em; cursor: pointer;\">
    <span title=\"$title\" class=\"icon ion-search italsoft-icon italsoft-button-ricerca\" data-ricerca=\"$id\"></span>
</div>
");

        $tableDataParams['stickyHeaders'] = true;
        $html->appendHtml("<div id=\"$id\" style=\"display: none;\">");
        $html->addTable($tableData, $tableDataParams);
        $html->appendHtml("</div>");
    }

}
