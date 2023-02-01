<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaDocumentale.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaMimeTypeUtils.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenModel.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';

function cwbBtaConsoleDocumentale() {
    $cwbAlfcityTest = new cwbBtaConsoleDocumentale();
    $cwbAlfcityTest->parseEvent();
    return;
}

class cwbBtaConsoleDocumentale extends cwbBpaGenModel {

    const DIV_METADATI_RICERCA = 'divMetadati';
    const DIV_METADATI_GESTIONE = 'divMetadatiGestione';
    const ALFRESCO_PLACE = '/app:company_home/cm:cityware/cm:ENTE_COD_ENTE/cm:AOO_COD_AOO/cm:AREA';
    const ALFRESCO_PLACE_MODULO = '/cm:MODULO';

    private $documentale;
    private $tipoDocumentale;
    private $tipoComponente;
    private $libBgd;
    private $pathDocumentoCaricato;
    private $conversioneArea;
    private $conversioneModulo;
    private $listeMultiValues = array();
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbBtaConsoleDocumentale';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }

    public function initVars() {
        $this->GRID_NAME = 'gridDocumenti';
        $this->noCrud = true;

        $this->tipoComponente = array(
            1 => 'ita-edit',
            //     2 => 'ita-number',
            2 => 'ita-edit', // i numeri li metto di tipo testo perché sennò di default mette 0 sul campo 
            3 => 'ita-decimal',
            4 => 'ita-edit-date',
            // 6 => 'ita-checkbox',
            6 => 'ita-select' // uso la combo al posto del checkbox sul boolean per avere anche l'opzione 'vuota'
        );

        $this->conversioneModulo = array(
            "PI" => "protocollo",
            "DE" => "delibere",
            "GE" => "base",
            "OR" => "base",
            "ES" => "fepa-in",
        );

        $this->conversioneArea = array(
            "A" => "media",
            "B" => "base",
            "F" => "base",
            "D" => "people"
        );

        $this->libBgd = new cwbLibDB_BGD();
    }

    protected function preConstruct() {
        if ($_POST['documentaleType']) {
            $this->tipoDocumentale = $_POST['documentaleType'];
        } else {
            $this->tipoDocumentale = cwbParGen::getFormSessionVar($this->nameForm, '_tipoDocumentale');
        }
        $this->documentale = new itaDocumentale($this->tipoDocumentale);
        $this->pathDocumentoCaricato = cwbParGen::getFormSessionVar($this->nameForm, '_pathDocumentoCaricato');
        $this->listeMultiValues = cwbParGen::getFormSessionVar($this->nameForm, '_listeMultiValues');
    }

    protected function customParseEvent() {
        switch ($_POST['event']) {
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_TIPO_DOCUMENTO':
                        $this->visualizzaMetadatiRicerca();
                        break;
                    case $this->nameForm . '_CONSOLE[TIPO_DOCUMENTO]':
                        $this->visualizzaMetadatiGestione();
                        break;
                }
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Scarica':
                        $this->scaricaDocumento();
                        break;
                    case $this->nameForm . '_UPLOAD_upld':
                        $this->caricaDocumento();
                        break;
                }
                break;
            case 'addGridRow':
                //controllo se l'id finisce per una delle chiavi della grid
                foreach ($this->listeMultiValues as $key => $value) {
                    if (preg_match('/' . $key . '$/', $_POST['id'])) {
                        // trovato l'array corrispondente alla grid che ha generato l'evento di add
                        $this->addGridRow($key);

                        break;
                    }
                }

                break;
            case 'afterSaveCell':
                //controllo se l'id finisce per una delle chiavi della grid
                foreach ($this->listeMultiValues as $key => $value) {
                    if (preg_match('/' . $key . '$/', $_POST['id'])) {
                        // trovato l'array corrispondente alla grid che ha generato l'evento di add
                        $this->editGridRow($key);

                        break;
                    }
                }

                break;
        }
    }

    protected function postDestruct() {
        if ($this->close != true) {
            cwbParGen::setFormSessionVar($this->nameForm, '_tipoDocumentale', $this->tipoDocumentale);
            cwbParGen::setFormSessionVar($this->nameForm, '_pathDocumentoCaricato', $this->pathDocumentoCaricato);
            cwbParGen::setFormSessionVar($this->nameForm, '_listeMultiValues', $this->listeMultiValues);
        }
    }

    protected function elenca() {
        try {
            $aspects = array();
            $props = array();
            $results = array();
            if ($_POST[$this->nameForm . '_UUID']) {
                if ($this->documentale->queryByUUID($_POST[$this->nameForm . '_UUID'])) {
                    $results = $this->estraiRisultato($this->documentale->getResult());
                }
            } else {
                $codEnte = '042002'; // TODO da sessione
                $codAoo = 'atdaa'; // TODO da sessione
                if (!$_POST[$this->nameForm . '_TIPO_DOCUMENTO']) {
                    Out::msgStop("Errore", "Selezionare tipo documento");

                    return;
                }

                $metdocs = $this->libBgd->leggiBgdTipdocChiave($_POST[$this->nameForm . '_TIPO_DOCUMENTO']);

                if (!$metdocs) {
                    Out::msgStop("Errore", "Selezionare tipo documento");

                    return;
                }

                $fullText = null;
                if ($_POST[$this->nameForm . '_FULLTEXT']) {
                    $fullText = $_POST[$this->nameForm . '_FULLTEXT'];
                }

                if ($_POST[$this->nameForm . '_DATA_CREAZIONE_DA'] && $_POST[$this->nameForm . '_DATA_CREAZIONE_A']) {
                    $t1 = strtotime($_POST[$this->nameForm . '_DATA_CREAZIONE_DA']);
                    $t2 = strtotime($_POST[$this->nameForm . '_DATA_CREAZIONE_A']);
                    $props['cm:created'] = '#GTE*DATE*' . date("Y-m-d", $t1) . 'T00:00:00+02:00#LTE*DATE*' . date("Y-m-d", $t2) . 'T00:00:00+02:00';
                }

                $this->popolaMetadatiRicerca($metdocs, $props, $aspects);

                $filters = array(
                    'IDTIPDOC' => $_POST[$this->nameForm . '_TIPO_DOCUMENTO']
                );
                $aspetti = $this->libBgd->leggiBgdAsptdc($filters);

                foreach ($aspetti as $aspetto) {
                    $alias = strtolower($aspetto['ALIAS_ASP']);
                    if ($_POST[$this->nameForm . '_has_aspect_' . $alias] === '1') {
                        $metdocs = $this->libBgd->leggiBgdMetdoc(array('IDTIPDOC' => $aspetto['IDASPECT']));
                        $aspects[$alias] = 1;
                        $this->popolaMetadatiRicerca($metdocs, $props, $aspects);
                    } else if ($_POST[$this->nameForm . '_has_aspect_' . $alias] === '0') {
                        $aspects[$alias] = 0;
                    }
                }

                if ($this->documentale->query($metdocs['ALIAS'], $codEnte, $codAoo, $aspects, $props, $fullText)) {
                    $results = $this->estraiRisultato($this->documentale->getResult());
                }
            }

            $ita_grid01 = $this->helper->initializeTableArray($results);

            if (!$this->helper->getDataPage($ita_grid01)) {
                Out::msgStop("Selezione", "Nessun record trovato.");
            } else {
                $this->setVisRisultato();
                TableView::enableEvents($this->nameForm . '_' . $this->GRID_NAME);

                out::show($this->nameForm . '_Scarica');
            }
        } catch (ItaException $e) {
            Out::msgStop("Errore lettura lista", $e->getCompleteErrorMessage(), '600', '600');
        } catch (Exception $e) {
            Out::msgStop("Errore lettura lista", $e->getMessage(), '600', '600');
        }
    }

    private function addGridRow($key) {
        $this->listeMultiValues[$key][] = array('VALORE' => "");

        $this->caricaGridMetadatoMultiplo($key, $this->listeMultiValues[$key]);
    }

    private function editGridRow($gridname) {
        foreach ($this->listeMultiValues[$gridname] as $key => $value) {
            if ($value['id'] == $_POST['rowid']) {
                $this->listeMultiValues[$gridname][$key]['VALORE'] = $_POST['value'];
                break;
            }
        }
    }

    protected function dettaglio($index) {
        Out::valore($this->nameForm . "_CONSOLE[UUID_SELECT]", null);
        $uuid = $index;
        if ($this->documentale->queryByUUID($uuid)) {
            Out::valore($this->nameForm . "_CONSOLE[UUID_SELECT]", $uuid);
            $results = $this->estraiRisultato($this->documentale->getResult());
            $this->setVisDettaglio();

            Out::hide($this->nameForm . '_divUpload');

            $selezionato = null;
            if ($results) {
                $doc_type = substr($results[0]['TIPO_DOC'], 0, strrpos($results[0]['TIPO_DOC'], "_type"));

                $tipdoc = $this->libBgd->leggiBgdTipdoc(array('ALIAS' => $doc_type));

                $selezionato = $tipdoc[0]['IDTIPDOC'];

                $this->initComboTipo("_CONSOLE[TIPO_DOCUMENTO]", $selezionato);

                $totalMetdoc = $this->visualizzaMetadatiGestione($selezionato);

                foreach ($totalMetdoc as $value) {
                    $key = $value['CHIAVE'];
                    $val = $results[0][$key];

                    if (array_key_exists($key, $results[0])) {
                        $val = $results[0][$key];
                        if ($value['MULTIPLO']) {
                            // se è multiplo aggiungo il record in memoria nel caso di modifiche/cancellazioni 
                            // da tenere allineate e carico i dati in grid
                            $this->caricaGridMetadatoMultiplo($key, $val);
                        } else {
                            if ($value['TIPO_METADATO'] == 6 || preg_match('/has_aspect/', $key)) {
                                if ($val == 'true') {
                                    $val = 1;
                                } else if ($val == 'false') {
                                    $val = 0;
                                } else {
                                    $val = null;
                                }
                            }

                            Out::valore($this->nameForm . '_CONSOLE[' . $key . ']', $val);
                        }
                    } else {
                        if ($value['MULTIPLO']) {
                            $this->listeMultiValues[$key] = array();
                        }
                    }
                }

                Out::disableField($this->nameForm . '_CONSOLE[TIPO_DOCUMENTO]');
            }
        }
    }

    private function caricaGridMetadatoMultiplo($gridname, $records) {
        // Pulisco grid storico per essere sicuro di avere la situazione pulita.
        $helper = new cwbBpaGenHelper();
        $helper->setGridName($gridname);
        $helper->setNameForm($this->nameForm);

        TableView::clearGrid($this->nameForm . '_' . $gridname);

        foreach ($records as $key => $value) {
            if (!$value['id']) {
                // aggiungo una chiave random per riconoscerli
                $records[$key]['id'] = uniqid($gridname, true);
            }
        }

        $this->listeMultiValues[$gridname] = $records;

        $ita_grid01 = $helper->initializeTableArray($records);
        $ita_grid01->getDataPage('json');

        TableView::enableEvents($this->nameForm . '_' . $gridname);
    }

    private function popolaMetadatiRicerca($metdocs, &$props, &$aspects) {
        foreach ($metdocs as $metadato) {
            $valore = $_POST[$this->nameForm . '_' . $metadato['CHIAVE']];
            if ($valore != null && $valore != '') {
                $props[$metadato['CHIAVE']] = $valore;
                // TODO se è data va formattata
            }
        }
    }

    protected function postNuovo() {
        out::hide($this->nameForm . '_Scarica');
        Out::html($this->nameForm . '_' . self::DIV_METADATI_RICERCA, '');
        Out::html($this->nameForm . '_' . self::DIV_METADATI_GESTIONE, '');

        $this->initComboTipo("_CONSOLE[TIPO_DOCUMENTO]");
        Out::show($this->nameForm . '_divUpload');

        Out::enableField($this->nameForm . '_CONSOLE[TIPO_DOCUMENTO]');

        $this->pathDocumentoCaricato = null;
    }

    private function calcDataToInsert(&$aliasTipDoc, &$aspects, &$props, $toInsert, $idTipoDoc) {
        $tipDoc = $this->libBgd->leggiBgdTipdocChiave($idTipoDoc);
        $filter = array('IDTIPDOC' => $tipDoc['IDTIPDOC']);

        $metdocs = $this->libBgd->leggiBgdMetdoc($filter);
        unset($toInsert['TIPO_DOCUMENTO']);

        $aliasTipDoc = $tipDoc['ALIAS'];
        $aspects = array();
        $props = array();

        // mi salvo i nomi di tutti i metadati di tipo data, perché poi li devo formattare prima di inserirli in alfresco
        foreach ($metdocs as $value) {
            if ($value['TIPO_METADATO'] == 4) {
                $metadatoTipoData[] = $value['CHIAVE'];
            }
        }

        // metadati degli aspetti (stesso filtro di sopra)        
        $aspetti = $this->libBgd->leggiBgdAsptdc($filter);
        foreach ($aspetti as $aspetto) {
            $metdocs = $this->libBgd->leggiBgdMetdoc(array('IDTIPDOC' => $aspetto['IDASPECT']));
            foreach ($metdoc as $value) {
                if ($value['TIPO_METADATO'] == 4) {
                    $metadatoTipoData[] = $value['CHIAVE'];
                }
            }
        }

        foreach ($toInsert as $key => $value) {
            if (preg_match('/has_aspect/', $key)) {
                // se la chiave contiene has_aspect è un aspetto
                $metadato = substr($key, strrpos($key, 'has_aspect_') + strlen('has_aspect_'));
                $aspects[$metadato] = ($value == 1 ? 1 : 0);
            } else {
                if (in_array($key, $metadatoTipoData)) {
                    $props[$key] = '*DATE*' . date('d-m-Y', strtotime($value));
                } else {
                    $props[$key] = $value;
                }
            }
        }

        $this->addCollectionData($props);
    }

    protected function aggiorna($validate = true) {
        //scrivo in alfresco
        $toInsert = $_POST[$this->nameForm . '_CONSOLE'];
        $this->calcDataToInsert($aliasTipDoc, $aspects, $props, $toInsert, $toInsert['TIPO_DOCUMENTO_hidden']);

        $areaCw = $this->conversioneArea[strtoupper($toInsert['com_area_cityware'])];
        $moduloCW = $this->conversioneModulo[strtoupper($toInsert['com_modulo_cityware'])];
        if (!$areaCw) {
            Out::msgStop("Errore", "Inserire Area Cityware ");
            return;
        }
//        if (!$moduloCW) {
//            Out::msgStop("Errore", "Inserire Modulo Cityware ");
//            return;
//        }
        $this->getAlfrescoPlace($areaCw, $moduloCW);

        if ($this->documentale->updateDocumentMetadata($toInsert['UUID_SELECT'], $aliasTipDoc, $aspects, $props)) {
            $this->pathDocumentoCaricato = null;
            $this->setVisRicerca();
            Out::msgInfo("Avviso", "Documento Aggiornato ");
        } else {
            Out::msgStop("Errore", "Errore Inserimento in Alfresco " . $this->documentale->getErrMessage());
        }
    }

    protected function aggiungi($validate = true) {
        //scrivo in alfresco
        $toInsert = $_POST[$this->nameForm . '_CONSOLE'];
        $this->calcDataToInsert($aliasTipDoc, $aspects, $props, $toInsert, $toInsert['TIPO_DOCUMENTO']);

        $file = file_get_contents($this->pathDocumentoCaricato);

        if (!$file) {
            Out::msgStop("Errore", "Inserire un documento");
            return;
        }

        $token = App::$utente->getKey('TOKEN') . "-";
        $fileName = substr($this->pathDocumentoCaricato, strrpos($this->pathDocumentoCaricato, $token) + strlen($token));

        $ext = itaMimeTypeUtils::estraiEstensione($fileName);

        $areaCw = $this->conversioneArea[strtoupper($toInsert['com_area_cityware'])];
        $moduloCW = $this->conversioneModulo[strtoupper($toInsert['com_modulo_cityware'])];
        if (!$areaCw) {
            Out::msgStop("Errore", "Inserire Area Cityware ");
            return;
        }
//        if (!$moduloCW) {
//            Out::msgStop("Errore", "Inserire Modulo Cityware ");
//            return;
//        }
        $place = $this->getAlfrescoPlace($areaCw, $moduloCW);

        if ($this->documentale->insertDocument($aliasTipDoc, $place, $fileName, itaMimeTypeUtils::getMimeTypes($ext), $file, $aspects, $props)) {
            $this->pathDocumentoCaricato = null;
            $this->setVisRicerca();
            Out::msgInfo("Avviso", "Documento inserito uuid: " . $this->documentale->getResult());
        } else {
            Out::msgStop("Errore", "Errore Inserimento in Alfresco " . $this->documentale->getErrMessage());
        }
    }

    private function getAlfrescoPlace($areaCw, $moduloCW) {
        $codEnte = '042002'; // TODO da sessione
        $codAoo = 'atdaa'; // TODO da sessione
        $place = str_replace('COD_ENTE', $codEnte, self::ALFRESCO_PLACE);
        $place = str_replace('COD_AOO', $codAoo, $place);
        $place = str_replace('AREA', $areaCw, $place);
        if ($moduloCW) {
            $place .= str_replace('MODULO', $moduloCW, self::ALFRESCO_PLACE_MODULO);
        }

        return $place;
    }

    private function addCollectionData(&$props) {
        // inserisco le collection concatenando i value con | come separator
        foreach ($this->listeMultiValues as $campo => $values) {
            if ($values) {
                $data = '';
                $sep = '';
                foreach ($values as $value) {
                    $data .= $sep . $value['VALORE'];
                    $sep = '|';
                }

                $props[$campo] = $data;
            }
        }
    }

    protected function postAltraRicerca() {
        out::hide($this->nameForm . '_Scarica');
    }

    protected function postApriForm() {
        $this->initComboTipo('_TIPO_DOCUMENTO');
        out::hide($this->nameForm . '_Scarica');

        Out::codice("pluploadActivate('" . $this->nameForm . "_UPLOAD_upld_uploader');");
    }

    private function initComboTipo($nomeCampo, $selezionato = null) {
        $results = $this->libBgd->leggiBgdTipdoc(array('escludeAspetti' => true, 'escludeNonEsportabili' => true));

        Out::select($this->nameForm . $nomeCampo, 1, null, !$selezionato ? 1 : 0, 'Selezionare...');
        foreach ($results as $value) {
            Out::select($this->nameForm . $nomeCampo, 1, $value['IDTIPDOC'], $selezionato == $value['IDTIPDOC'], $value['DESCRIZIONE']);
            if ($selezionato == $value['IDTIPDOC']) {
                Out::valore($this->nameForm . '_CONSOLE[TIPO_DOCUMENTO_hidden]', $value['IDTIPDOC']);
            }
        }
    }

    private function estraiRisultato($queryResult) {
        $results = array();

        if ($queryResult['QUERYRESULT'] && $queryResult['QUERYRESULT'][0]['RESULTS']) {
            $data = $queryResult['QUERYRESULT'][0]['RESULTS'][0]['RESULT'];

            foreach ($data as $keyRec => $record) {
                $results[$keyRec]['UUID'] = $record['UUID'][0]['@textNode'];
                $results[$keyRec]['TIPO_DOC'] = substr($record['TYPE'][0]['@textNode'], strrpos($record['TYPE'][0]['@textNode'], '}') + 1);

                foreach ($record['COLUMNS'][0]['COLUMN'] as $keyMet => $metadato) {
                    $returnValue = null;
                    if ($metadato['ISMULTIVALUE'][0]['@textNode']) {
                        $values = explode("|", $metadato['VALUES'][0]['@textNode']);
                        $value = array();
                        foreach ($values as $value) {
                            $returnValue[] = array('VALORE' => $value);
                        }
                    } else {
                        $returnValue = $metadato['VALUE'][0]['@textNode'];
                    }
                    $results[$keyRec][$metadato['NAME'][0]['@textNode']] = $returnValue;
                }
            }
        }

        return $results;
    }

    private function visualizzaMetadatiRicerca() {
        $this->visualizzaMetadati(self::DIV_METADATI_RICERCA, $_POST[$this->nameForm . '_TIPO_DOCUMENTO']);
    }

    private function generateComponent($metdocs, $alias, $title, $isAspetto = false, $isGestione = false) {
        $components = array();
        if ($metdocs) {
            if ($isAspetto) {
                // se sono in gestione aggiungo CONSOLE_ al nome cosi finisce nell'array
                if ($isGestione) {
                    $id = 'CONSOLE[' . 'has_aspect_' . strtolower($alias) . ']';
                } else {
                    $id = 'has_aspect_' . strtolower($alias);
                }

                $components[] = array(
                    'type' => 'ita-select',
                    'id' => $id,
                    'label' => array('text' => 'Abilita ' . $title, 'position' => 'sx', 'style' => 'width:300px'),
                    'newline' => 1,
                    'options' => $this->generateNullableCheckboxOption($divId)
                );
            }

            foreach ($metdocs as $metadato) {
                if ($metadato['MULTIPLO']) {
                    $component = array(
                        'type' => 'jqgrid',
                        'id' => $metadato['CHIAVE'],
                        'label' => array('text' => $metadato['DESCRIZIONE'], 'position' => 'sx', 'style' => 'width:300px'),
                        'newline' => 1,
                        'columns' => array(
                            0 => array('id' => 'VALORE', 'label' => 'Valore', 'width' => "300px", 'class' => '{editable: true,cellEdit:true}'),
                            1 => array('id' => 'id', 'label' => 'Id', 'class' => '{hidden: true}')
                        ),
                        'properties' => array('rowNum' => '10', 'readerId' => "'id'", 'navGrid' => 'true', 'navButtonAdd' => 'true', 'resizeToParent' => 'true', 'cellEdit' => 'true')
                    );
                    $this->listeMultiValues[$metadato['CHIAVE']] = array();
                    $components[] = $component;
                } else {
                    if (array_key_exists($metadato['TIPO_METADATO'], $this->tipoComponente)) {
                        if ($isGestione) {
                            $id = 'CONSOLE[' . $metadato['CHIAVE'] . ']';
                        } else {
                            $id = $metadato['CHIAVE'];
                        }
                        $component = array(
                            'type' => $this->tipoComponente[$metadato['TIPO_METADATO']],
                            'id' => $id,
                            'label' => array('text' => $metadato['DESCRIZIONE'], 'position' => 'sx', 'style' => 'width:300px'),
                            'newline' => 1
                        );

                        if ($metadato['TIPO_METADATO'] == 6) {
                            // se è boolean uso l'ita-select al posto del checkbox per avere l'opzione null
                            $component['options'] = $this->generateNullableCheckboxOption($divId);
                        }

                        if ($metadato['DIMENSIONE'] > 0 && $metadato['DIMENSIONE'] < 101) {
                            // se è boolean uso l'ita-select al posto del checkbox per avere l'opzione null
                            $component['properties'] = array('size' => $metadato['DIMENSIONE']);
                        } else if ($metadato['DIMENSIONE'] > 100 && $metadato['TIPO_METADATO'] == 1) {
                            // se è un ita-edit e la size è maggiore di 100, sovrascrivo il type in multiline
                            $component['type'] = 'ita-edit-multiline';
                            $component['rows'] = 2;
                            $component['cols'] = 80;
                        }

                        $components[] = $component;
                    }
                }
            }
            if ($components) {
                $formComponents = array(
                    'type' => 'fieldset',
                    'id' => 'div_' . $alias,
                    'title' => $title,
                    'children' => $components);

                return $formComponents;
            }
        }

        return null;
    }

    private function generateNullableCheckboxOption($id) {
        return $options = array(
            array(
                'id' => 'nessuno_' . $id,
                'value' => null,
                'text' => 'Selezionare...',
                'selected' => 1
            ), array(
                'id' => 'si_' . $id,
                'value' => 1,
                'text' => 'SI',
                'selected' => 0
            ), array(
                'id' => 'no_' . $id,
                'value' => 0,
                'text' => 'NO',
                'selected' => 0
            )
        );
    }

    private function caricaDocumento() {
        $origFile = $_POST['file'];
        $uplFile = itaLib::getUploadPath() . "/" . App::$utente->getKey('TOKEN') . "-" . $_POST['file'];
        $this->pathDocumentoCaricato = $uplFile;

        Out::valore($this->nameForm . '_UPLOAD', $origFile);
    }

    private function scaricaDocumento() {
        $uuid = $_POST[$this->nameForm . '_' . $this->GRID_NAME]['gridParam']['selrow'];
        if ($uuid && $this->documentale->contentByUUID($uuid)) {
            $corpoFile = $this->documentale->getResult();
            if ($this->documentale->queryByUUID($uuid)) {
                $metadata = $this->estraiRisultato($this->documentale->getResult());
                $fileName = $metadata[0]['name'];

                $this->downloadFile($corpoFile, $fileName);
            }
        }
    }

    private function downloadFile($corpo, $fileName) {
        cwbLib::downloadDocument($fileName, $corpo, true);
    }

    private function visualizzaMetadatiGestione($idTipDoc = null) {
        if (!$idTipDoc) {
            $idTipDoc = $_POST[$this->nameForm . '_CONSOLE']['TIPO_DOCUMENTO'];
        }
        return $this->visualizzaMetadati(self::DIV_METADATI_GESTIONE, $idTipDoc, true);
    }

    private function visualizzaMetadati($divName, $idtipdoc, $isGestione = false) {
        $this->listeMultiValues = array();
        $totalMetdoc = array();
        Out::html($this->nameForm . '_' . $divName, '');

        $components = array();

        $filters = array(
            'IDTIPDOC' => $idtipdoc
        );

        // metadati 'diretti'
        $metdocs = $this->libBgd->leggiBgdMetdoc($filters);

        $totalMetdoc = $metdocs;

        $components[] = $this->generateComponent($metdocs, 'divMetadatiSpecifici', 'Metadati', false, $isGestione);

        // metadati degli aspetti (stesso filtro di sopra)        
        $aspetti = $this->libBgd->leggiBgdAsptdc($filters);

        foreach ($aspetti as $aspetto) {
            $metdocs = $this->libBgd->leggiBgdMetdoc(array('IDTIPDOC' => $aspetto['IDASPECT']));
            $components[] = $this->generateComponent($metdocs, $aspetto['ALIAS_ASP'], $aspetto['DESCRIZIONE_ASP'], true, $isGestione);

            $totalMetdoc = array_merge($totalMetdoc, $metdocs);
            $totalMetdoc[] = array('CHIAVE' => 'has_aspect_' . strtolower($aspetto['ALIAS_ASP'])); // aggiungo l'aspetto per passarlo ai metodi successivi
        }

        if ($components) {
            cwbLibHtml::componentiDinamici($this->nameForm, $divName, $components);

            cwbLibHtml::attivaJSElemento($this->nameForm . '_' . $divName);
        }

        return $totalMetdoc;
    }

}

