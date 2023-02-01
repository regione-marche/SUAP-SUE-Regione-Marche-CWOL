<?php

/**
 *
 * Utility generiche Cityware
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Massimo Biagioli <m.biagioli@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
include_once ITA_LIB_PATH . '/itaPHPCore/itaFrontController.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbModelHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BOR.class.php';
include_once ITA_BASE_PATH . '/lib/Cache/CacheFactory.class.php';


class cwbLib {

    const ORDER_ASC = 'asc';
    const ORDER_DESC = 'desc';
    const CONNECTION_NAME = 'CITYWARE';
    const CONNECTION_NAME_NUMERATORI = 'CITYWARE_NUMERATORI';
    const CONNECTION_NAME_LOCK = 'CITYWARE_LOCK';

    /**
     * Restituisce nome connessione Cityware
     * @param bool OBSOLETO $numeratori Se true, considera connessione relativa a numeratori NON UTILIZZARE
     * @param string $ente DomainCode connessione
     * @param string $connectionName DNome della sessione di cityware da aprire 
     * @return string Nome connessione
     */
    public static function getCitywareConnectionName($numeratori = false, $ente = null, $connectionName = NULL) {
        if (isset($connectionName)) {
            $returnConnectionName = $connectionName;
        } else {
            if (isSet($ente)) {
                $domainCode = $ente;
            } else {
                $domainCode = App::$utente->getKey('ditta');
            }
            // Cerca una connessione [CITYWARE + DomainCode]
            // Se non presente, va il fallback sulla connessione Cityware
            $toSearch = self::CONNECTION_NAME . $domainCode;
            $dbParm = parse_ini_file(ITA_CONFIG_PATH . '/connections.ini', true);

            $returnConnectionName = "";
            if (isset($dbParm[$toSearch])) {
                $returnConnectionName = $toSearch;
            } else {
                $returnConnectionName = self::CONNECTION_NAME;
            }
        }

        return $returnConnectionName;
    }

    /**
     * Apertura finestra di ricerca
     * @param type $model Nome model da aprire
     * @param type $ownerNameForm Nome form chiamante
     * @param type $returnEvent Evento di ritorno
     * @param type $returnId Id dell'elemento che ha scatenato l'evento
     * @param type $ownerModelName model name chiamante (in caso di finestra con alias che ne chiama un altra è diverso da $ownerNameForm)
     */
    public static function apriFinestraRicerca($model, $ownerNameForm, $returnEvent, $returnId, $searchOpenElenco = false, $externalParams = null, $ownerModelName = null, $alias = '', $postData = array()) {
        $objModel = self::apriFinestra($model, $ownerNameForm, $returnEvent, $returnId, $externalParams, $ownerModelName, $alias, $postData);

        if (method_exists($objModel, 'setFlagSearch')) {
            $objModel->setFlagSearch(true);
        }

        if (method_exists($objModel, 'setSearchOpenElenco')) {
            $objModel->setSearchOpenElenco($searchOpenElenco);
        }

        $objModel->parseEvent();
        return $objModel;
    }

    public static function apriFinestraDettaglioRecord($model, $ownerNameForm, $returnEvent, $returnId, $index = 'new', $returnData = false, $ownerModelName = null, $alias = '', $postData = array(), $viewMode = false) {
        $objModel = self::apriFinestra($model, $ownerNameForm, $returnEvent, $returnId, null, $ownerModelName, $alias, $postData);

        $objModel->setViewMode($viewMode);
        $objModel->setSearchOpenElenco(false);
        $objModel->setApriDettaglio($index);
        $objModel->setReturnDataFlag($returnData);
        return $objModel;
    }

    /**
     * Restituisce dati al chiamante (quando la finestra è chiamata in ricerca da un'altra)
     * @param string $returnModel Nome model di ritorno
     * @param string $returnEvent Nome evento di ritorno
     * @param string $returnId Id di ritorno
     * @param string $currentRecord Record corrente
     * @param string $nameForm Nome form chiamante
     * @param string $returnNameForm Nome form di rientro (se si usa un alias può essere diversa da $returnModel)
     */
    public static function ricercaEsterna($returnModel, $returnEvent, $returnId, $currentRecord, $nameForm, $returnNameForm = null, $close = true) {
        /* @var $objParent itaFrontController */
        if ($close) {
            Out::closeDialog($nameForm);
        }
        $_POST['nameform'] = null;
        $objParent = itaFrontController::getInstance($returnModel, $returnNameForm);
        $objParent->setEvent($returnEvent);
        $objParent->setElementId($returnId);
        $objParent->setFormData(array('returnData' => $currentRecord));
        $objParent->parseEvent();
        return $objParent;
    }

    public static function apriFinestra($model, $ownerNameForm, $returnEvent, $returnId, $externalParams, $ownerModelName = null, $alias = '', $postData = array()) {
        if ($ownerModelName === null) {
            $ownerModelName = $ownerNameForm;
        }

        if (!$alias) {
            // calcolo l'alias perché se apro un lookup di una form già aperta come pagina, me la chiude.
            // quindi l'alias nei lookup diventa obbligatorio
            $alias = $model . time() . rand(); // Tolto i due '_' (separatori) che davano problemi
        }
        if (is_array($postData)) {
            $_POST = array_merge($_POST, $postData);
        }

        itaLib::openDialog($model, true, true, 'desktopBody', '', '', $alias);
        $objModel = itaFrontController::getInstance($model, $alias);
        $objModel->setEvent('openform');
        $objModel->setReturnModel($ownerModelName);
        $objModel->setReturnNameForm($ownerNameForm);
        $objModel->setReturnEvent($returnEvent);

        // Se apro una finestra che estende itaFrontController, non ha il metodo setExternalParams
        if (method_exists($objModel, 'setExternalParams')) {
            $objModel->setExternalParams($externalParams);
        }

        $objModel->setReturnId($returnId);
        $objModel->setNameForm($alias);
        $_POST['nameform'] = $alias;
//        $objModel->initHelper(); // reinizializzo l'helper perché ho cambiato il nameForm 

        return $objModel;
    }

    /**
     * Innesta form in un div.
     * @param type $model Nome del model da innestare
     * @param type $container Contenitore dove viene innestata la form
     * @param type $onlyOnce
     * @param type $alias
     */
    public static function innestaForm($model, $container, $onlyOnce = false, $alias = '') {
        if (!$alias) {
            $alias = $model . '_' . time() . '_' . rand();
        }

        itaLib::openInner($model, true, false, $container, '', '', $alias);
        $objModel = itaFrontController::getInstance($model, $alias);
        $_POST['nameform'] = $alias;

        return $objModel;
    }

    /**
     * Apertura finestra di dettaglio
     * @param type $model Nome model
     * @param type $nameForm Nome form
     * @param type $returnEvent Evento di ritorno
     * @param type $returnId Id dell'elemento che ha scatenato l'evento
     */
    public static function apriFinestraDettaglio($model, $nameForm, $returnEvent, $returnId, $masterRecord, $externalParams = null, $ownerModelName = null, $alias = '', $postData = array()) {
        $objModel = self::apriFinestra($model, $nameForm, $returnEvent, $returnId, $externalParams, $ownerModelName, $alias, $postData);

        $objModel->setMasterRecord($masterRecord);
        $objModel->setFlagSearch(false);
        $objModel->parseEvent();

        return $objModel;
    }

    /**
     * Apre il visualizzatore di documenti
     * @param array $files Array di files da visualizzare
     */
    public static function apriVisualizzatoreDocumenti($files, $singleMode = false, $title = 'Elenco documenti') {
//        $alias = 'cwbDocViewer_' . time();
//        itaLib::openApp('cwbDocViewer', true, true, 'desktopBody', "", '', $alias);
//        $objModel = itaFrontController::getInstance('cwbDocViewer', $alias);
//        $objModel->setFiles($files);
//        $objModel->setSingleMode($singleMode);
//        $objModel->setEvent('openform');
//        $objModel->parseEvent();
//        return $objModel;

        include_once ITA_LIB_PATH . '/itaPHPDocViewer/itaDocViewerBootstrap.class.php';
        $docViewer = new itaDocViewerBootstrap();
        foreach ($files as $filename) {
            $docViewer->addFile($filename['NOME']);
        }

        $docViewer->openViewer(itaDocViewerBootstrap::DOCVIEWER_TAB, false, true, false, false, $title);


        return $docViewer;
    }

    /**
     * Apre il visualizzatore di documenti
     * @param array $files Array di files da visualizzare
     */
    public static function apriVisualizzatoreDocumentiDialog($files, $singleMode = false) {
        $objModel = self::apriFinestra("cwbDocViewer", null, null, null, null);
        $objModel->setSingleMode($singleMode);
        $objModel->setFiles($files);
        $objModel->parseEvent();

        return $objModel;
    }

    /**
     * Apre il visualizzatore di documenti
     * @param   array $listaCombo Array usato per valorizzare la combo. 
     *          string $nomeField Nome campo
     *          string $cod Campo codice
     *          string $desc Campo descrittivo
     *          interger $line Riga correte
     *          boolean $bVuoto Aggiunge riga vuota
     *          interger $tutti Aggiunge la voce tutti con codice indicato (es. 99)
     */
    public static function initComboGen($listaCombo, $nomeField, $cod = 'COD', $desc = 'DESC', $line = 0, $bVuoto = false, $tutti = 0) {
        if ($bVuoto == true) {
            array_unshift($listaCombo, array($cod => 0, $desc => ''));
        }
        if ($tutti > 0) {
            $listaCombo[count($listaCombo)] = array($cod => $tutti, $desc => 'Tutti');
        }

        for ($index = 0; $index < count($listaCombo); $index++) {
            Out::select($nomeField, 1, $listaCombo[$index][$cod], ($index == $line) ? 1 : 0, $listaCombo[$index][$desc]);
        }
    }

    /**
     * Decodifica lookup per ricerca singola     
     * @param string $searchNameForm Nome form su cui cercare
     * @param string $ownerNameForm Nome form di rientro della ricerca
     * @param array $ownerModel Modello di rientro (nel caso $ownerNameForm fosse un alias va passato il modello originale)
     * @param mixed $codValue valore del campo codice. Se chiave multipla passare un array posizionale di valori 
     * @param mixed $codField id del campo codice. Se chiave multipla passare un array posizionale di valori 
     * @param mixed codFieldTablename nome del campo codice del db su cui applicare il filtro per il caricamento 
     * @param string $desValue valore del campo descrizione
     * @param string $desField id del campo descrizione 
     * @param string $desFieldTablename nome del campo descrizione sul db su cui applicare il filtro per il caricamento 
     * @param string $returnEvent Evento di ritorno
     * @param string $returnId Id dell'elemento che ha scatenato l'evento
     * @param boolean $searchButton true per il bottone di apertura finestra di rierca 
     * @param boolean $openElenco se true parte direttamente sulla pagina dei risultati 
     * @param array $externalFilters Filtri permanenti da passare al lookup
     * @param string $libName Nome della lib in cui lanciare la lettura Sql Default calcolata dalla "$nameForm"
     * @param string $loadMethodSingleValue Nome del metodi singolo di caricamento. Es leggiBtaGrunazChiave. Default calcolata dalla "$nameForm"
     * @param string $loadMethodMultipleValue Nome del metodi multuiplo di caricamento. Es leggiBtaGrunaz. Default calcolata dalla "$nameForm"
     */
    public static function decodificaLookup($searchNameForm, $ownerNameForm, $ownerModel, $codValue, $codField, $codFieldTablename, $desValue, $desField = null, $desFieldTablename, $returnEvent, $returnId, $searchButton = false, $openElenco = true, $externalFilters = array(), $libName = "", $loadMethodSingleValue = "", $loadMethodMultipleValue = "") {
        if (!$searchNameForm) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati 'nameForm' nel metodo decodificaLookup");
        }
        if (!$returnEvent) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati 'returnEvent' nel metodo decodificaLookup");
        }
        if (!$returnId) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, "Prerequisiti non rispettati 'returnId' nel metodo decodificaLookup");
        }
        $lib = self::createLib($searchNameForm, $libName);
        if (!$loadMethodSingleValue) {
            $loadMethodSingleValue = cwbModelHelper::loadRowMethodNameByModelName($searchNameForm);
        }
        if (!$loadMethodMultipleValue) {
            $loadMethodMultipleValue = cwbModelHelper::loadMethodNameByModelName($searchNameForm);
        }
        if ($searchNameForm == 'cwdDtaRelpar') {
            $loadMethodSingleValue .= 'LookUp';
        }

        // se non sono array li trasformo in array di 1
        if (is_array($codValue) == false) {
            $codValue = array($codValue);
        }

        if (is_array($codField) == false) {
            $codField = array($codField);
        }
        if (is_array($codFieldTablename) == false) {
            $codFieldTablename = array($codFieldTablename);
        }

        // se è un array controllo che i valori passati siano diversi da null 
        if (count(array_filter($codValue, function($var) {
                            return !is_null($var);
                        })) !== count($codValue)) {
            $codValue = null;
        }
        if (count(array_filter($codField)) !== count($codField)) {
            $codField = null;
        }
        if (count(array_filter($codFieldTablename)) !== count($codFieldTablename)) {
            $codFieldTablename = null;
        }

        $openedRicerca = false;

        if ($searchButton == true) {
            $row = null;
            $filters = array();
            self::putExternalFilters($externalFilters, $filters);

            self::apriFinestraRicerca($searchNameForm, $ownerNameForm, $returnEvent, $returnId, $openElenco, $filters, $ownerModel);
            $openedRicerca = true;
        } else if ($codValue) {
            $row = call_user_func_array(array($lib, $loadMethodSingleValue), $codValue);
            // non posso passare gli $externalFilters alla chiamata per chiave quindi li filtro dopo
            if ($row && $externalFilters) {
                foreach ($externalFilters as $key => $value) {
                    // se il valore di un filtro esterno non corrisponde, svuoto row
                    if (trim($row[$key]) != trim($value)) {
                        $row = null;
                        break;
                    }
                }
            }
        } else if ($desValue) {
            $filters = array();
            if ($externalFilters) {
                $filters = $externalFilters;
            }

            $filters[$desFieldTablename] = $desValue;

            $array = $lib->$loadMethodMultipleValue($filters, true);

            if (count($array) == 1) {
                $row = $array[0];
            } else if (count($array) > 1) {
                $row = null;
                $filters = array($desFieldTablename => array("PERMANENTE" => true, "VALORE" => $desValue));
                self::apriFinestraRicerca($searchNameForm, $ownerNameForm, $returnEvent, $returnId, $openElenco, $filters, $ownerModel);
                $openedRicerca = true;
            } else {
                $row = null;
            }
        }

        if ($row) {
            if ($codField) {
                foreach ($codField as $key => $value) {
                    Out::valore($value, $row[$codFieldTablename[$key]]);
                }
            }
            if ($desField) {
                Out::valore($desField, $row[$desFieldTablename]);
            }
        } else if (!$openedRicerca) {
            if ($codField) {
                foreach ($codField as $key => $value) {
                    Out::valore($value, "");
                }
            }
            if ($desField) {
                Out::valore($desField, '');
            }
        }

        if ($row) {
            $_POST['riga'] = $row;
        }

        if ($array) {
            $_POST['array'] = $array;
        }
        return $row;
    }

    private static function createLib($model, $libName = '') {
        if (!$libName) {
            $libName = cwbModelHelper::libNameByModelName($model);
        }
        $area = cwbModelHelper::moduleByModelName($model);
        include_once ITA_BASE_PATH . "/$area/$libName.class.php";
        $lib = new $libName;
        return $lib;
    }

    private static function putExternalFilters($externalFilters, &$filters) {
        if ($externalFilters) {
            foreach ($externalFilters as $key => $value) {
                if (!isset($value['VALORE'])) {
                    $filters[$key] = array('PERMANENTE' => true, 'VALORE' => $value);
                } else {
                    $filters[$key] = array(
                        'PERMANENTE' => (isSet($value['PERMANENTE']) && $value['PERMANENTE'] === false ? false : true),
                        'VALORE' => $value['VALORE'],
                        'HTMLELEMENT' => (!empty($value['HTMLELEMENT']) ? $value['HTMLELEMENT'] : $key)
                    );
                }
            }
        }
    }

    public static function downloadDocument($fileName, $corpo, $forceDownload = false) {
        if (!@is_dir(itaLib::getPrivateUploadPath())) {
            if (!itaLib::createPrivateUploadPath()) {
                Out::msgStop("Visualizza Allegato", "Creazione ambiente di lavoro temporaneo fallita");
            }
        }
        $pathTmp = itaLib::getPrivateUploadPath() . '/';
        file_put_contents($pathTmp . $fileName, $corpo);

        $randName = md5(rand() * time()) . "." . pathinfo($fileName, PATHINFO_EXTENSION);

        $downloadKey = md5(rand() * time());
        App::$utente->setKey($downloadKey . "_DATAFILE", $pathTmp . $randName);
        App::$utente->setKey($downloadKey . "_FILENAME", $fileName);
        Out::openDocument(utiDownload::getUrl($fileName, $pathTmp . $fileName, $forceDownload));
    }

    /**
     * Ricerca all'interno di un array bidimensionale
     * @param array $array Array su cui effettuare la ricerca
     * @param array $search Chiavi da cercare (chiave: chiave - Valore: valore)
     * @param boolean $and Se si specificano pi? chiavi, si vuole che siano soddisfatte tutte o solo una?
     * @return array: array di elementi che corrispondono a quanto ricercato.
     */
    public static function searchInMultiArray($array, $search, $and = true) {
        $return = array();
        foreach ($array as $arrKey => $element) {
            $in = false;
            foreach ($search as $key => $value) {
                if ($and) {
                    if (isSet($element[$key]) && $element[$key] === $value) {
                        $in = true;
                    } else {
                        $in = false;
                        break;
                    }
                } else {
                    if (isSet($element[$key]) && $element[$key] === $value) {
                        $in = true;
                        break;
                    }
                }
            }
            if ($in) {
                $return[$arrKey] = $element;
            }
        }
        return $return;
    }

    /**
     * Ricerca dentro un array passato le stringhe che rispondono al like (case insensitive)
     * @param array $source
     * @param string $search
     * $param bool $caseSensitive
     * @return array array dei risultati (chiave=>valore) che rispondono al like
     */
    public static function searchInArrayAsLike($source, $search, $caseSensitive = false) {
        $return = array();

        $search = str_replace(array('_', '%'), array('.?', '.*?'), $search);
        $search = '/^' . $search . '$/';
        if ($caseSensitive === false) {
            $search .= 'i';
        }

        foreach ($source as $k => $v) {
            if (preg_match($search, $v)) {
                $return[$k] = $v;
            }
        }
        return $return;
    }

    public static function apriApp($model, $ownerNameForm, $returnEvent, $returnId, $externalParams, $ownerModelName = null, $alias = '', $postData = array()) {
        if ($ownerModelName === null) {
            $ownerModelName = $ownerNameForm;
        }

        if (!$alias) {
            // calcolo l'alias perché se apro un lookup di una form già aperta come pagina, me la chiude.
            // quindi l'alias nei lookup diventa obbligatorio
            $alias = $model . '_' . time() . '_' . rand();
        }

        itaLib::openApp($model, true, true, 'desktopBody', '', '', $alias);
        $objModel = itaFrontController::getInstance($model, $alias);
        $objModel->setEvent('openform');
        $objModel->setReturnModel($ownerModelName);
        $objModel->setReturnNameForm($ownerNameForm);
        $objModel->setReturnEvent($returnEvent);

        // Se apro una finestra che estende itaFrontController, non ha il metodo setExternalParams
        if (method_exists($objModel, 'setExternalParams')) {
            $objModel->setExternalParams($externalParams);
        }

        $objModel->setReturnId($returnId);
        $objModel->setNameForm($alias);
        $_POST['nameform'] = $alias;
        if (is_array($postData)) {
            $_POST = array_merge($_POST, $postData);
        }
//        $objModel->initHelper(); // reinizializzo l'helper perché ho cambiato il nameForm 

        return $objModel;
    }

    /**
     * Apertura finestra di dettaglio
     * @param type $model Nome model
     * @param type $nameForm Nome form
     * @param type $returnEvent Evento di ritorno
     * @param type $returnId Id dell'elemento che ha scatenato l'evento
     */
    public static function apriAppDettaglio($model, $nameForm, $returnEvent, $returnId, $masterRecord, $externalParams = null, $ownerModelName = null, $alias = '', $postData = array()) {
        $objModel = self::apriApp($model, $nameForm, $returnEvent, $returnId, $externalParams, $ownerModelName, $alias, $postData);

        $objModel->setMasterRecord($masterRecord);
        $objModel->setFlagSearch(false);
        $objModel->parseEvent();
    }

    public static function rewriteHeader(){
        $dbLib = new cwbLibDB_BOR();
        
        $onClick = "onclick=\"javascript:itaGo('ItaCall','',{asyncCall:true,bloccaui:false,event:'openform',model:'menCwbConfig'});\"";
        $text = '';
        
        $enti = cwbParGen::getBorEnti();
        if(count($enti) > 1){
            $enteAttuale = cwbParGen::getProgEnte();
            foreach($enti as $ente){
                if($ente['PROGENTE'] == $enteAttuale){
                    $enteAttuale = $ente;
                    break;
                }
            }
            
            $text .= '<a href="#" '.$onClick.'>' . $ente['DESENTE'] . '</a> - ';
        }
        
        $text .= 'Es.Contabile <a href="#" '.$onClick.'>' . cwbParGen::getAnnoContabile() . '</a>';
        
            $codute = strtoupper(trim(cwbParGen::getUtente()));
        $text .= '<br>Utente: '.$codute;
        
        $filtri = array('CODUTE'=>$codute);
        $gesaut = $dbLib->leggiModoGesaut($filtri, false);
                
        if($gesaut['MODO_GESAUT'] == 1){
            $ruolo = cwbParGen::getRuolo();
            if(!empty($ruolo)){
                $filtri = array('KRUOLO'=>$ruolo);
                $ruolo = $dbLib->leggiGeneric('BOR_RUOLI', $filtri, false);

                if(!empty($ruolo)){
                    $text .= ' - Ruolo: <a href="#" '.$onClick.'>'.$ruolo['DES_RUOLO'].'</a>';
                }
            }
            else{
                $text .= ' - Ruolo: <a href="#" '.$onClick.'>Non specificato</a>';
            }
        }

        
        Out::html('citywareRightHeader', $text);
    }
}
