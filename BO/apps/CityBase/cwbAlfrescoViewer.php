<?php
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenTab.class.php';
include_once ITA_LIB_PATH . '/itaPHPAlfcity/itaDocumentaleAlfrescoUtils.class.php';
include_once ITA_LIB_PATH . '/itaPHPDocViewer/itaDocViewer.class.php';

include_once ITA_LIB_PATH . '/itaPHPVersign/itaP7m.class.php';
include_once ITA_LIB_PATH . '/itaPHPDocer/itaDocerClientFactory.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/itaPHPDocumentaleUtils.class.php';

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGD.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiDownload.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSdi.class.php';

function cwbAlfrescoViewer() {
    $cwbAlfrescoViewer = new cwbAlfrescoViewer();
    $cwbAlfrescoViewer->parseEvent();
    return;
}

class cwbAlfrescoViewer extends cwbBpaGenTab {
    const JSON_DECODE_PATH = '/lib/itaPHPAlfcity/cwbAlfrescoViewer.decode.json';
    
    const DOCER_TYPE_ID = 'GENERICO';
    const DOCER_TIPO_COMPONENTE_PRINCIPALE = 'PRINCIPALE';
    const DOCER_TIPO_COMPONENTE_ALLEGATO = 'ALLEGATO';
    const DOCER_TIPO_COMPONENTE_ANNESSO = 'ANNESSO';
    const DOCER_TIPO_COMPONENTE_ANNOTAZIONE = 'ANNOTAZIONE';
    
    private static $tipiFatture = array(
        'SDI_CP_RICEZ',
        'SDI_CP_CONFRIC',
        'SDI_CP_RIFIUTO',
        'SDI_CA_FLUSSO',
        'SDI_CA_NOTIF_NS',
        'SDI_CA_NOTIF_RC',
        'SDI_CP_FATT',
        'SDI_CA_FATT',
        'SDI_CP_ACCETTAZ',
        'SDI_CA_NOTIF_MC',
        'SDI_CA_NOTIFNEA',
        'SDI_CA_NOTIFNER',
        'SDI_CA_NOTIF_DT',
        'SDI_CA_NOTIF_AT'
    );
    
    private $TREE_NAME;
    private $GRID_NAME_METADATI_ALFRESCO;
    private $GRID_NAME_ASPETTI_ALFRESCO;
    
    private $libDB_BGD;
    
    private $typeInfo;
    private $documentale;
    
    private $documentUUID;
    private $parents;
    private $treeData;
    private $metadataDictionary;
    private $previewFile;
    
    public function __construct($nameFormOrig = null, $nameForm = null) {
        if (!isSet($nameForm) || !isSet($nameFormOrig)) {
            $nameFormOrig = 'cwbAlfrescoViewer';
            $nameForm = isSet($_POST['nameform']) ? $_POST['nameform'] : $nameFormOrig;
        }
        parent::__construct($nameFormOrig, $nameForm);
    }
    
    public function openDocument($uuid){
        try{
            if(empty($uuid)){
                throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Fornire UUID del documento da aprire');
            }

            $this->documentUUID = $uuid;
            $this->buildTreeData($uuid);

            $this->mostraDocumentoUUID($uuid);
        }
        catch(ItaException $e){
            Out::msgStop('Errore', $e->getNativeErroreDesc());
            $this->close();
        }
        catch(Exception $e){
            Out::msgStop('Errore', $e->getMessage());
            $this->close();
        }
    }
    
    protected function initVars() {
        $this->TABLE_NAME = 'FFE_CP3_FATT';
        $this->TABLE_VIEW = 'FFE_CP3_FATT_V01';
        $this->TREE_NAME = 'treeDocumenti';
        
        $this->GRID_NAME_METADATI_ALFRESCO = 'gridMetadatiAlfresco';
        $this->GRID_NAME_ASPETTI_ALFRESCO = 'gridAspettiAlfresco';
        
        $this->skipAuth = true;
        $this->noCrud = true;
        
        $this->libDB = new cwbLibDB_GENERIC();
        $this->libDB_BGD = new cwbLibDB_BGD();
        
        $this->actionAfterNew = self::GOTO_LIST;
        $this->actionAfterModify = self::GOTO_LIST;
        $this->actionAfterDelete = self::GOTO_LIST;
        
        $this->documentale = new itaDocumentale('ALFCITY');
        $this->documentale->setUtf8_encode(true);
        $this->documentale->setUtf8_decode(true);
        
        $this->documentUUID = cwbParGen::getFormSessionVar($this->nameForm, 'documentUUID');
        $this->treeData = cwbParGen::getFormSessionVar($this->nameForm, 'treeData');
        $this->previewFile = cwbParGen::getFormSessionVar($this->nameForm, 'previewFile');
    }
    
    protected function preDestruct() {
        if(!$this->close){
            cwbParGen::setFormSessionVar($this->nameForm, 'documentUUID', $this->documentUUID);
            cwbParGen::setFormSessionVar($this->nameForm, 'treeData', $this->treeData);
            cwbParGen::setFormSessionVar($this->nameForm, 'previewFile', $this->previewFile);
        }
        else{
            if(is_file($this->previewFile)){
                $this->cleanPreviewFile();
            }
        }
    }
    
    protected function preParseEvent() {
        switch($_POST['event']){
            case 'openform':
                $this->initTable();
                break;
        }
    }
    
    protected function customParseEvent() {
        switch($_POST['event']){
            case 'onClick':
                switch($_POST['id']){
                    case (preg_match('/^'.$this->nameForm.'_download_([0-9]*)$/', $_POST['id'], $matches) ? $_POST['id'] : null):
                        $this->download($matches[1]);
                        break;
                    case (preg_match('/^'.$this->nameForm.'_print_([0-9]*)$/', $_POST['id'], $matches) ? $_POST['id'] : null):
                        $this->printPDF($matches[1]);
                        break;
                    case (preg_match('/^'.$this->nameForm.'_p7m_([0-9]*)$/', $_POST['id'], $matches) ? $_POST['id'] : null):
                        $this->showP7m($matches[1]);
                        break;
                    case $this->nameForm . '_downloadAll':
                        $this->downloadAll();
                        break;
                }
                break;
            case 'dbClickRow':
                switch($_POST['id']){
                    case $this->nameForm . '_' . $this->TREE_NAME:
                        $index = $_POST['rowid'];
                        $this->mostraDocumento($index);
                        break;
                }
                break;
        }
    }
    
    private function initTable(){
        $downloadIco = cwbLibHtml::getHtmlClickableObject($this->nameForm, $this->nameForm.'_downloadAll', '<span class="ui-icon ui-icon-download"></span>', array(), 'Scarica tutti i documenti');
        Out::gridSetColumnHeader($this->nameForm, $this->TREE_NAME, 'DOWNLOAD', $downloadIco);
        Out::show($this->nameForm . '_divGestione');
    }
    
    protected function postElenca() {
        $this->treeData = null;
    }
    
    protected function postAltraRicerca() {
        $this->treeData = null;
    }
    
    private function clearDetails(){
        TableView::clearGrid($this->nameForm . '_' . $this->TREE_NAME);
        TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME_METADATI_ALFRESCO);
        TableView::clearGrid($this->nameForm . '_' . $this->GRID_NAME_ASPETTI_ALFRESCO);
        Out::html($this->nameForm . '_filePreview','');
    }
    
    protected function dettaglio() {
        if(!is_array($this->treeData)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'TreeData non valorizzata');
        }
        
        $this->clearDetails();
        $this->cleanPreviewFile();
        
        $grd = new TableView($this->nameForm . '_' . $this->TREE_NAME, array('arrayTable'=>$this->treeData, 'rowIndex' => 'CUSTOMKEY'));
        $grd->setPageNum(1);
        $grd->setPageRows(1000000000);
        $grd->treeAddChildren();
        
        $this->setVisDettaglio();
    }
    
    private function mostraDocumento($index){
        $data = $this->treeData[$index];
            
        if($data['skip'] === true){
            return;
        }
        try{
            $this->popolaTabellaMetadatiAlfresco($data, false);
            $this->mostraFile($data);
        }
        catch(ItaException $e){
            Out::msgStop('Errore', $e->getNativeErroreDesc());
        }
        catch(Exception $e){
            Out::msgStop('Errore', $e->getMessage());
        }
    }
    
    private function getMetadataDescription($type, $key){
        $idtipdoc = $this->libDB_BGD->leggiBgdTipdoc(array('ALIAS'=>$type), false);
        if(empty($type)){
            return false;
        }
        $idtipdoc = $idtipdoc['IDTIPDOC'];
        
        if(!isSet($this->metadataDictionary)){
            $this->metadataDictionary = array();
        }
        if(!isSet($this->metadataDictionary[$type])){
            $this->metadataDictionary[$type] = array();
            
            $result = $this->libDB_BGD->leggiBgdMetdocAspetti(array('IDTIPDOC'=>$idtipdoc));
            foreach($result as $value){
                $this->metadataDictionary[$type][strtoupper(trim($value['CHIAVE']))] = trim($value['DESCRIZIONE']);
            }
            
            $result = $this->libDB_BGD->leggiBgdAsptdc(array('IDTIPDOC'=>$idtipdoc));
            foreach($result as $value){
                $this->metadataDictionary[$type][strtoupper(trim($value['ALIAS_ASP']))] = trim($value['DESCRIZIONE_ASP']);
            }
        }
        $key = str_replace('HAS_ASPECT_','',strtoupper(trim($key)));
        return (isSet($this->metadataDictionary[$type][$key]) ? $this->metadataDictionary[$type][$key] : $key);
    }
    
    private function getMetadatiAlfresco($data){
        if(!$this->documentale->queryByUUID($data['UUID'])){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Impossibile trovare l\'allegato con UUID '.$data['UUID']);
        }

        $result = $this->documentale->getResult();

        $return = array();
        $return['METADATI'] = array();
        $return['ASPETTI'] = array();
        if(is_array($result['QUERYRESULT'][0]['RESULTS'][0]['RESULT'][0]['COLUMNS'][0]['COLUMN'])){
            foreach($result['QUERYRESULT'][0]['RESULTS'][0]['RESULT'][0]['COLUMNS'][0]['COLUMN'] as $meta){
                $row = array();
                $row['KEY'] = $meta['NAME'][0]['@textNode'];
                $row['VALUE'] = $meta['VALUE'][0]['@textNode'];

                if(stripos($row['KEY'], 'has_aspect') === false){
                    $row['METADATA'] = $this->getMetadataDescription($data['TYPE'], $row['KEY']);
                    $return['METADATI'][] = $row;
                }
                else{
                    $row['METADATA'] = $this->getMetadataDescription($data['TYPE'], $row['KEY']);
                    $return['ASPETTI'][] = $row;
                }
            }
        }
        
        return $return;
    }
    
    private function popolaTabellaMetadatiAlfresco($data, $editable=false){
        $meta = $this->getMetadatiAlfresco($data);
        
        $metadati = array();
        $aspetti = array();
        
        foreach($meta['METADATI'] as $value){
            if($value['KEY'] == 'created' || $value['KEY'] == 'modified' || $value['KEY'] == 'node-uuid'){
                continue;
            }
            
            $row = $value;
            if($editable){
                $component = array(
                    'id' => $row['KEY'],
                    'type' => 'ita-edit',
                    'model' => $this->nameForm,
                    'rowKey' => $data['uuid'],
                    'onChangeEvent' => true,
                    'properties' => array(
                        'value' => $row['VALUE'],
                        'style' => 'width: 100%'
                    )
                );
                $row['VALUE'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            }
            $metadati[] = $row;
        }
        
        foreach($meta['ASPETTI'] as $value){
            $row = $value;
            $component = array(
                'id' => $row['KEY'],
                'type' => 'ita-checkbox',
                'model' => $this->nameForm,
                'rowKey' => $data['uuid'],
                'onChangeEvent' => true,
                'properties' => array()
            );
            if($row['VALUE'] == 'true'){
                $component['properties']['checked'] = '';
            }
            if(!$editable){
                $component['properties']['disabled'] = '';
            }
            $row['VALUE'] = cwbLibHtml::addGridComponent($this->nameForm, $component);
            $aspetti[] = $row;
        }

        $helper = new cwbBpaGenHelper();
        $helper->setNameForm($this->nameForm);
        $helper->setGridName($this->GRID_NAME_METADATI_ALFRESCO);

        $tableName = $this->nameForm . '_' . $this->GRID_NAME_METADATI_ALFRESCO;

        TableView::clearGrid($tableName);
        $metadataGrid = $helper->initializeTableArray($metadati);
        $metadataGrid->setPageRows(10000);
        $metadataGrid->getDataPage('json');

        TableView::enableEvents($tableName);
        cwbLibHtml::attivaJSElemento($tableName);


        $helper = new cwbBpaGenHelper();
        $helper->setNameForm($this->nameForm);
        $helper->setGridName($this->GRID_NAME_ASPETTI_ALFRESCO);

        $tableName = $this->nameForm . '_' . $this->GRID_NAME_ASPETTI_ALFRESCO;

        TableView::clearGrid($tableName);
        $aspettiGrid = $helper->initializeTableArray($aspetti);
        $metadataGrid->setPageRows(10000);
        $aspettiGrid->getDataPage('json');

        TableView::enableEvents($tableName);
        cwbLibHtml::attivaJSElemento($tableName);
    }
    
    private function getFileAlfresco($data){
        if (!$this->documentale->contentByUUID($data['UUID'])) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Impossibile recuperare l\'allegato con UUID '.$data['UUID']);
        }
        $ContenutoFile = $this->documentale->getResult();
        if (!$ContenutoFile) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Impossibile scaricare il contenuto dell\'allegato con UUID '.$data['UUID']);
        }
        
        return $ContenutoFile;
    }
    
    private function mostraFile($data){        
        $ContenutoFile = $this->getFileAlfresco($data);
        $this->cleanPreviewFile();
//        if(preg_match('/(.*)\.p7m$/i', $data['FILENAME'], $matches)){
//            $p7mPath = itaLib::getUploadPath() . '/'. $data['FILENAME'];
//            file_put_contents($p7mPath, $ContenutoFile);
//            $p7m = itaP7m::getP7mInstance($p7mPath);
//            if($p7m !== false){
//                $data['FILENAME'] = $matches[1];
//                $ContenutoFile = file_get_contents($p7m->getContentFileName());
//            }
//        }
        $this->previewFile = itaLib::getUploadPath() . '/'. $data['FILENAME'];
        
        file_put_contents($this->previewFile, $ContenutoFile);

        $docViewer = new itaDocViewer($this->nameForm, false, $this->nameForm . '_filePreview', $this->nameForm . '_fileDownload');
        $docViewer->setFiles(itaLib::getUploadPath() . '/'. $data['FILENAME']);
        $docViewer->previewFile(0);
    }
    
    private function cleanPreviewFile(){
        if(is_file($this->previewFile)){
            unlink($this->previewFile);
            unset($this->previewFile);
        }
    }
    
//    protected function setVisDettaglio() {
//        parent::setVisDettaglio();
//                
//        if(empty($this->treeData[1]['id_docer'])){
//            Out::show($this->nameForm . '_DETTAGLIO_caricaDocER');
//            Out::hide($this->nameForm . '_DETTAGLIO_aggiornaDocER');
//            Out::hide($this->nameForm . '_DETTAGLIO_annullaDocER');
//            Out::hide($this->nameForm . '_DETTAGLIO_fascicolaDocER');
//            Out::hide($this->nameForm . '_DETTAGLIO_protocollaDocER');
//            Out::hide($this->nameForm . '_DETTAGLIO_allegaDocER');
//        }
//        else{
//            Out::hide($this->nameForm . '_DETTAGLIO_caricaDocER');
//            Out::show($this->nameForm . '_DETTAGLIO_aggiornaDocER');
//            Out::show($this->nameForm . '_DETTAGLIO_fascicolaDocER');
//            Out::show($this->nameForm . '_DETTAGLIO_protocollaDocER');
//            Out::show($this->nameForm . '_DETTAGLIO_allegaDocER');
//            
//            $metadati = $this->leggiDettaglioDocer($this->treeData[1]['id_docer']);
//            if(empty($metadati['ANNULL_REGISTRAZ'])){
//                Out::show($this->nameForm . '_DETTAGLIO_annullaDocER');
//                Out::hide($this->nameForm . '_DETTAGLIO_ripristinaDocER');
//            }
//            else{
//                Out::hide($this->nameForm . '_DETTAGLIO_annullaDocER');
//                Out::show($this->nameForm . '_DETTAGLIO_ripristinaDocER');
//            }
//        }
//    }
    
    protected function setVisNuovo() {
        parent::setVisNuovo();
        Out::show($this->nameForm . '_DETTAGLIO_caricaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_aggiornaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_annullaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_ripristinaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_fascicolaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_protocollaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_allegaDocER');
    }
    
    protected function setVisRicerca() {
        parent::setVisRicerca();
        Out::hide($this->nameForm . '_DETTAGLIO_caricaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_aggiornaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_annullaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_ripristinaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_fascicolaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_protocollaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_allegaDocER');
    }
    
    protected function setVisRisultato() {
        parent::setVisRisultato();
        Out::hide($this->nameForm . '_DETTAGLIO_caricaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_aggiornaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_annullaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_ripristinaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_fascicolaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_protocollaDocER');
        Out::hide($this->nameForm . '_DETTAGLIO_allegaDocER');
    }
    
    
    
    
    
    /* ###################################################################################################################
     * ################################################## METODI NUOVI ###################################################
     * ###################################################################################################################
     */
    
    private function getTypeInfo($type){
        if(!isSet($this->typeInfo)){
            $this->typeInfo = json_decode(file_get_contents(ITA_BASE_PATH . self::JSON_DECODE_PATH), true);
        }
        
        return $this->typeInfo[$type];
    }
    
    private function getElementInfo($element){
        $uuid = $element['UUID'][0]['@textNode'];
        
        $type = $element['TYPE'][0]['@textNode'];
        $type = preg_match('/^{.*?}([A-Za-z0-9_]*)_type$/', $type, $matches);
        $type = strtoupper($matches[1]);
        
        $typeInfo = $this->getTypeInfo($type);
        
        $name = null;
        $nomeFile = null;
        $nomeFlusso = null;
        $comDescrizione = null;
        foreach($element['COLUMNS'][0]['COLUMN'] as $metadata){
            if($metadata['NAME'][0]['@textNode'] == 'name'){
                $name = trim($metadata['VALUE'][0]['@textNode']);
                break;
            }
            if($metadata['NAME'][0]['@textNode'] == 'nome_file'){
                $nomeFile = trim($metadata['VALUE'][0]['@textNode']);
                if(!empty($nome)){
                    break;
                }
            }
            if($metadata['NAME'][0]['@textNode'] == 'nome_flusso'){
                $nomeFlusso = trim($metadata['VALUE'][0]['@textNode']);
                if(!empty($nome) || !empty($nomeFile)){
                    break;
                }
            }
            if($metadata['NAME'][0]['@textNode'] == 'com_descrizione'){
                $comDescrizione = trim($metadata['VALUE'][0]['@textNode']);
                if(!empty($nome) || !empty($nomeFile) || !empty($nomeFlusso)){
                    break;
                }
            }
            
        }
        
        $return = array();
        $return['UUID'] = $uuid;
        $return['TYPE'] = $type;
        $return['TYPE_DESC'] = $typeInfo['DESC'];
        $return['NAME'] = ($name ?: $nomeFile ?: $nomeFlusso ?: $comDescrizione);
        $return['PRINT'] = (in_array($type, self::$tipiFatture) && strtolower(substr(trim($return['NAME']), -3)) == 'xml');
        $return['P7M'] = (strtolower(substr(trim($return['NAME']), -3)) == 'p7m');
        $return['CHILDREN_TYPES'] = $typeInfo['CHILDREN'];
        
        return $return;
    }
    
    private function buildTreeData($uuid){
        if(!$this->documentale->queryByUUID($uuid)){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Impossibile trovare l\'allegato con UUID '.$uuid);
        }
        
        $document = $this->documentale->getResult();
        if(empty($document['QUERYRESULT'][0]['RESULTS'][0]['RESULT'])){
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Impossibile trovare l\'allegato con UUID '.$uuid);
        }
        
        $searchParent = true;
        $this->parents = array();
        while($searchParent){
            $searchParent = false;
            foreach($document['QUERYRESULT'][0]['RESULTS'][0]['RESULT'][0]['COLUMNS'][0]['COLUMN'] as $metadata){
                if($metadata['NAME'][0]['@textNode'] == 'ger_uuid_padre'){
                    $uuidPadre = trim($metadata['VALUE'][0]['@textNode']);
                    if(!empty($uuidPadre)){
                        $this->parents[] = $uuidPadre;
                        $this->documentale->queryByUUID($uuidPadre);
                        $document = $this->documentale->getResult();
                        $searchParent = true;
                    }
                    break;
                }
            }
        }

        $treeInfo = $this->getTreeInfo($document['QUERYRESULT'][0]['RESULTS'][0]['RESULT'][0]);
        
        $this->treeData = $this->generateTreeStructure($treeInfo);
    }
    
    private function getTreeInfo($parentElement){
        $treeInfo = $this->getElementInfo($parentElement);
        $treeInfo['CHILDREN'] = $this->getTreeInfoChildren($parentElement, $treeInfo);
        
        return $treeInfo;
    }
    
    private function getTreeInfoChildren($parentElement, $parentInfo){
        $return = array();
        
        if(is_array($parentInfo['CHILDREN_TYPES'])){
            foreach($parentInfo['CHILDREN_TYPES'] as $childType){
                $childTypeInfo = $this->getTypeInfo($childType);
                $child = array(
                    'UUID'=>null,
                    'TYPE'=>$childType,
                    'TYPE_DESC'=>$childTypeInfo['DESC'],
                    'NAME'=>$childTypeInfo['DESC'],
                    'PRINT'=>false,
                    'P7M'=>false,
                    'CHILDREN_TYPES'=>$childTypeInfo['CHILDREN'],
                    'CHILDREN'=>array()
                );

                $this->documentale->query($childType, cwbParGen::getCodente(), cwbParGen::getCodAoo(), array(), array('ger_uuid_padre'=>$parentInfo['UUID']));
//                $this->documentale->query($childType, '042002', 'atdaa', array(), array('ger_uuid_padre'=>$parentInfo['UUID']));
                $children = $this->documentale->getResult();
                if(isSet($children['QUERYRESULT'][0]['RESULTS'][0]['RESULT'])){
                    foreach($children['QUERYRESULT'][0]['RESULTS'][0]['RESULT'] as $row){
                        $rowInfo = $this->getElementInfo($row);
                        $rowInfo['CHILDREN'] = $this->getTreeInfoChildren($row, $rowInfo);
                        $child['CHILDREN'][] = $rowInfo;
                    }
                }
//                $this->documentale->query('SDI_CP_FATT', '042002', 'atdaa', array(), array());
                if(!empty($child['CHILDREN'])){
                    $return[] = $child;
                }
            }
        }
        
        return $return;
    }
    
    private function generateTreeStructure($elementInfo, &$i=1, $parentKey=0, $level=0, &$return=array()){
        $key = $i;
        $level++;
        $i++;
        
        $return[$key] = array();
        $return[$key]['CUSTOMKEY'] = $key;
        $return[$key]['UUID'] = $elementInfo['UUID'];
        $return[$key]['TYPE'] = $elementInfo['TYPE'];
        $return[$key]['FILENAME'] = $elementInfo['NAME'];
        
        if($key == 1){
            $return[$key]['DOCUMENTO'] = '<b>'.$elementInfo['TYPE_DESC'].':</b> '.$elementInfo['NAME'];
        }
        elseif(!isSet($elementInfo['UUID'])){
            $return[$key]['DOCUMENTO'] = '<b>'.$elementInfo['TYPE_DESC'].'</b>';
        }
        else{
            $return[$key]['DOCUMENTO'] = $elementInfo['NAME'];
        }
        if(isSet($elementInfo['UUID'])){
            $return[$key]['DOWNLOAD'] = cwbLibHtml::getHtmlClickableObject($this->nameForm, $this->nameForm.'_download_'.$key, '<span class="ui-icon ui-icon-download"></span>', array(), 'Scarica documento');
            if($elementInfo['PRINT']){
                $return[$key]['DOWNLOAD'].= cwbLibHtml::getHtmlClickableObject($this->nameForm, $this->nameForm.'_print_'.$key, '<span class="ui-icon ui-icon-print"></span>', array(), 'Scarica stampa PDF del documento');
            }
            if($elementInfo['P7M']){
                $return[$key]['DOWNLOAD'].= cwbLibHtml::getHtmlClickableObject($this->nameForm, $this->nameForm.'_p7m_'.$key, '<span class="ita-icon ita-icon-shield-blue-16x16" style="display: inline-block"></span>', array(), 'Documento firmato digitalmente: clicka per visualizzare');
            }
        }
        else{
            $return[$key]['DOWNLOAD'] = '';
        }
        
        $return[$key]['skip'] = (!isSet($elementInfo['UUID']) ? true : false);
        $return[$key]['level'] = $level;
        $return[$key]['parent'] = ($key == 1 ? '' : $parentKey);
        $return[$key]['isLeaf'] = (empty($elementInfo['CHILDREN']) ? 'true' : 'false');
        $return[$key]['loaded'] = 'true';
        $return[$key]['expanded'] = 'false';
        
        foreach($elementInfo['CHILDREN'] as $child){
            $this->generateTreeStructure($child, $i, $key, $level, $return);
        }
        return $return;
    }
    
    private function mostraDocumentoUUID($uuid){
        foreach($this->treeData as $k=>$row){
            if($row['UUID'] == $uuid){
                if($this->treeData[$k]['skip'] != true){
                    $index = $this->treeData[$k]['parent'];
                    for($i=count($this->treeData); $i>0; $i--){
                        if($i == $index){
                            $this->treeData[$i]['expanded'] = 'true';
                            $index = $this->treeData[$i]['parent'];
                        }
                        else{
                            $this->treeData[$i]['expanded'] = 'false';
                        }
                    }
                    $this->dettaglio();

                    $this->mostraDocumento($k);
                    Out::setRowSelection($this->nameForm . '_' . $this->TREE_NAME, $k, 'id', false);
                }
                break;
            }
        }
    }
    
    private function download($key){
        $ContenutoFile = $this->getFileAlfresco($this->treeData[$key]);
        $filePath = itaLib::getUploadPath() . '/'. $this->treeData[$key]['FILENAME'];
        
        file_put_contents($filePath, $ContenutoFile);
        
        $otrToken = utiDownload::getOTR($this->treeData[$key]['FILENAME'], $filePath, true, true);
        Out::openDocument($otrToken);
    }
    
    private function printPDF($key){
        $xml = $this->getFileAlfresco($this->treeData[$key]);
        $filePath = itaLib::getUploadPath() . '/' . $this->treeData[$key]['FILENAME'];
        
        if(strtolower(substr($this->treeData[$key]['FILENAME'], -3)) == 'xml' && preg_match('/<([A-Za-z0-9]*):(FatturaElettronica|FileMetadati|MetadatiInvioFile|RicevutaScarto|NotificaScarto|RicevutaImpossibilitaRecapito|AttestazioneTrasmissioneFattura|ScartoEsitoCommittente|RicevutaConsegna|NotificaEsito|NotificaMancataConsegna|NotificaEsitoCommittente|NotificaDecorrenzaTermini).*?versione="([A-Z0-9\.]*)".*?>/i', $xml, $matches)){
            $libSDI = new proLibSdi();
            $filePath.= '.pdf';
            
            if($libSDI->sdiXmlToPdf($xml, $filePath) == false){
                Out::msgStop('Errore', 'Errore nella conversione dell\'xml in pdf');
                return;
            }
        }
        else{
            file_put_contents($filePath, $xml);
        }
        
        $otrToken = utiDownload::getOTR($this->treeData[$key]['FILENAME'].'.pdf', $filePath, true, true);
        Out::openDocument($otrToken);
    }
    
    private function showP7m($key){
        $p7m = $this->getFileAlfresco($this->treeData[$key]);
        $filePath = itaLib::createAppsTempPath('alfrescoViewerP7m') . '/' . $this->treeData[$key]['FILENAME'];
        
        file_put_contents($filePath, $p7m);
        
        itaLib::openForm('utiP7m');
        $model = itaModel::getInstance('utiP7m', 'utiP7m');
        $model->setEvent("openform");
        $model->setFile($filePath);
        $model->setFileOriginale($this->treeData[$key]['FILENAME']);
        $model->setShowPreview(true);
        $model->parseEvent();
    }
    
    private function downloadAll(){
        $filePath = itaLib::getUploadPath() . '/'.time().rand().'.zip';
        
        $zip = new ZipArchive;
        $res = $zip->open($filePath, ZipArchive::CREATE);
        
        foreach($this->treeData as $file){
            if(!empty($file['UUID'])){
                $content = $this->getFileAlfresco($file);
                $zip->addFromString($file['FILENAME'], $content);
            }
        }
        $zip->close();
        
        $otrToken = utiDownload::getOTR(basename($filePath), $filePath, true, true);
        Out::openDocument($otrToken);
    }
}

?>