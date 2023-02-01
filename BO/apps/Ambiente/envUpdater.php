<?php

include_once ITA_LIB_PATH . '/itaPHPCore/itaLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPLogger/itaPHPLogUtilities.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenHelper.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibHtml.class.php';
include_once ITA_BASE_PATH . '/updater/itaUpdaterLib.class.php';
include_once ITA_BASE_PATH . '/updater/itaDownloader.class.php';
include_once ITA_BASE_PATH . '/updater/itaDiffer.class.php';
include_once ITA_BASE_PATH . '/updater/itaSavePointFactory.class.php';
include_once ITA_BASE_PATH . '/updater/itaConfigUpdater.class.php';
//include_once ITA_BASE_PATH . '/apps/Ambiente/envLibPatch.class.php';
if (!class_exists('modulesTest')) {
    include_once ITA_BASE_PATH . '/updater/lib/updater/modulesTest.php';
}

function envUpdater() {
    $envUpdater = new envUpdater();
    $envUpdater->parseEvent();
    return;
}

class envUpdater extends itaModel {

    public $nameForm = "envUpdater";
    public $helper;
    public $updaterLib;
    public $envLibPatch;
    public $patchesArray;
    public $patchGrid;
    public $updaterConfig;
    public $selectedPatch;
    public $filterPatchToApply;

    const UPDATER_BRANCH = 'dist-clienti';
    const UPDATER_BRANCH_TEST = 'fake-clienti';

    function __construct() {
        parent::__construct();
        $this->private = false;
        $this->helper = new cwbBpaGenHelper();
        $this->helper->setNameForm($this->nameForm);
        $this->helper->setGridName('savepoint_grid');
        $this->patchGrid = $this->nameForm . '_patch_grid';
        $this->updaterConfig = App::$utente->getKey($this->nameForm . '_updaterConfig');
        $this->patchesArray = App::$utente->getKey($this->nameForm . '_patchesArray');
        $this->filterPatchToApply = App::$utente->getKey($this->nameForm . '_filterPatchToApply') ? 1 : 0;
        $this->selectedPatch = App::$utente->getKey($this->nameForm . '_selectedPatch');
        try {
            $this->updaterLib = new itaUpdaterLib(null, $this->updaterConfig);
        } catch (ItaException $e) {
            Out::msgStop("Errore", $e->getNativeErroreDesc());
            itaLib::closeForm($this->nameForm);
        }
    }

    public function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_updaterConfig', $this->updaterConfig);
            App::$utente->setKey($this->nameForm . '_patchesArray', $this->patchesArray);
            App::$utente->setKey($this->nameForm . '_filterPatchToApply', $this->filterPatchToApply);
            App::$utente->setKey($this->nameForm . '_selectedPatch', $this->selectedPatch);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->initDefaultValues();
                $this->showInfo();
                $this->initCheckModules();
                $this->checkDefaultBranch();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_tabConf':
                        $this->showInfo();
                        $this->initCheckModules();
                        break;
                    case $this->nameForm . '_modulesDetails':
                        $this->showModulesDetails();
                        break;
                    case $this->nameForm . '_modulesClose':
                        Out::hide($this->nameForm . '_divModulesResult');
                        break;
                    case $this->nameForm . '_tabUpd':
                        $this->showUpd();
                        break;
                    case $this->nameForm . '_tabSavepoints':
                        $this->showSavepoints();
                        break;
                    case $this->nameForm . '_menu_logs':
                        itaPHPLogUtilities::openLogViewerText($this->updaterLib->getLogFilePath());
                        break;
                    case $this->nameForm . '_upd_blocco_btn':
                        $this->bloccaConfirm();
                        break;
                    case $this->nameForm . '_modal_conferma':
                        $this->blocca($_POST[$this->nameForm . '_modal_inputText']);
                        break;
                    case $this->nameForm . '_upd_sblocco_btn':
                        $this->sblocca();
                        break;
                    case $this->nameForm . '_upd_check_btn':
                        $this->checkRemote();
                        break;
                    case $this->nameForm . '_upd_download_btn':
                        $this->downloadRemote();
                        break;
                    case $this->nameForm . '_upd_checkWD_btn':
                        $this->checkWorkingDir();
                        break;
                    case $this->nameForm . '_upd_downloadWD_btn':
                        $this->downloadWorkingDir();
                        break;
                    case $this->nameForm . '_upd_downloadWD_force':
                        $this->downloadWorkingDir(true);
                        break;
                    case $this->nameForm . '_upd_migrations_btn':
                        Out::msgInfo('Attenzione', 'Funzione non implementata.');
                        /*
                         * Riabilitare la chiamata una volta completato lo sviluppo
                         * della funzionalità.
                         */
                        //$this->migrate();
                        break;
                    case $this->nameForm . '_upd_auto_btn':
                        $this->autoUpdate();
                        break;
                    case $this->nameForm . '_menu_details':
                        $timestamp = $_POST[$this->nameForm . '_savepoint_grid']['gridParam']['selrow'];
                        $this->vediDettagli($timestamp);
                        break;
                    case $this->nameForm . '_menu_apply':
                        $timestamp = $_POST[$this->nameForm . '_savepoint_grid']['gridParam']['selrow'];
                        $this->confirmApply($timestamp);
                        break;
                    case $this->nameForm . '_details_apply':
                        $timestamp = $_POST[$this->nameForm . '_details_name'];
                        $this->confirmApply($timestamp);
                        break;
                    case $this->nameForm . '_details_revert':
                        $timestamp = $_POST[$this->nameForm . '_details_name'];
                        $this->confirmRevert($timestamp);
                        break;
                    case $this->nameForm . '_ConfermaApply':
                        $timestamp = $_POST[$this->nameForm . '_details_name'];
                        $this->applicaSavepoint($timestamp);
                        $this->showSavepoints();
                        break;
                    case $this->nameForm . '_ConfermaRevert':
                        $timestamp = $_POST[$this->nameForm . '_details_name'];
                        $this->revertSavepoint($timestamp);
                        $this->showSavepoints();
                        break;
                    case $this->nameForm . '_ClearCacheAPC':
                        $this->clearCache('apc');
                        break;
                    case $this->nameForm . '_ClearCacheFile':
                        $this->clearCache('file');
                        break;
                    case $this->nameForm . '_ViewBuildHistory':
                        $this->viewBuildHistory();
                        break;
                    case $this->nameForm . '_tabPatch':
                        $this->showPatch();
                        break;
                    case $this->nameForm . '_VisualizzaContenutoPatch':
                        $this->viewPatchInfo();
                        break;
                    case $this->nameForm . '_details_back':
                        $this->showSavepoints();
                        break;
                    case $this->nameForm . '_forced_to_closed':
                        $this->close();
                        break;
                    case $this->nameForm . '_AnnullaPersonalizzato':
                        $this->close();
                        break;

                    case $this->nameForm . '_ApplicaPatch':
                        $patchInfo = $this->patchesArray[$this->selectedPatch];
                        include_once ITA_BASE_PATH . '/apps/Ambiente/envLibPatch.class.php';
                        $this->envLibPatch = new envLibPatch();
                        if (!$this->envLibPatch->applyPatch($patchInfo['PATCH_DEFT'], $patchInfo['PATCH_DEFD'])) {
                            Out::msgStop('Errore', $this->envLibPatch->getErrMessage());
                            break;
                        }

                        $this->caricaGrigliaPatch(true);
                        Out::msgBlock($this->nameForm, 1500, '', "Patch applicata con successo.");
                        break;

                    case $this->nameForm . '_MarcaPatch':
                        $patchInfo = $this->patchesArray[$this->selectedPatch];
                        include_once ITA_BASE_PATH . '/apps/Ambiente/envLibPatch.class.php';
                        $this->envLibPatch = new envLibPatch();
                        $patch_applt_rec = array(
                            'PATCH_DEFT_ID' => $patchInfo['PATCH_DEFT']['ROW_ID'],
                            'PATCH_NAME' => $patchInfo['PATCH_DEFT']['PATCH_NAME'],
                            'APPL_MODE' => envLibPatch::APPL_MODE_FORCED
                        );

                        if (!$this->envLibPatch->insertPatchAppl($patch_applt_rec)) {
                            Out::msgStop('Errore', $this->envLibPatch->getErrMessage());
                            break;
                        }

                        $this->caricaGrigliaPatch(true);
                        Out::msgBlock($this->nameForm, 1500, '', "Patch marcata con successo.");
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }
                break;

            case 'onClickTablePager':
                switch ($_POST['id']) {
                    case $this->nameForm . '_savepoint_grid':
                        $this->initSavepoints();
                        break;
                    case $this->patchGrid:
                        $this->caricaGrigliaPatch();
                        break;
                }
                break;
            case 'onChange':
                switch ($_POST['id']) {
                    case $this->nameForm . '_patch_filter_toapply':
                        $this->filterPatchToApply = $_POST[$_POST['id']];
                        $this->caricaGrigliaPatch();
                        break;
                }
                break;
            case 'dbClickRow':
                switch ($_POST['id']) {
                    case $this->nameForm . '_savepoint_grid':
                        $timestamp = $_POST['rowid'];
                        $this->vediDettagli($timestamp);
                        break;
                }
                break;
            case 'cellSelect':
                switch ($_POST['id']) {
                    case $this->patchGrid:
                        switch ($_POST['colName']) {
                            case 'CTXMENU':
                                $this->selectedPatch = $_POST['rowid'];

                                $patchInfo = $this->patchesArray[$this->selectedPatch];

                                $html = '<div style="max-height: 200px;">';

                                if ($patchInfo['PATCH_DEFT']['PATCH_CONTEXT']) {
                                    $html .= '<b>AMBITO:</b> ';
                                    $html .= str_replace(';', '; ', $patchInfo['PATCH_DEFT']['PATCH_CONTEXT']) . '<br>';
                                }

                                if ($patchInfo['PATCH_DEFT']['PATCH_NOTES']) {
                                    $html .= '<b>NOTE:</b> ';
                                    $html .= str_replace(';', '; ', $patchInfo['PATCH_DEFT']['PATCH_NOTES']) . '<br>';
                                }

                                $html .= '<b>FILE:</b>';
                                foreach ($patchInfo['PATCH_DEFD'] as $patch_defd_rec) {
                                    $html .= '<br>- ' . $patch_defd_rec['FILENAME'];
                                }

                                $html .= '<br></div>';

                                if (!$this->patchesArray[$_POST['rowid']]['PATCH_APPL']) {
                                    $arrayAzioni['Applica'] = array(
                                        'id' => $this->nameForm . '_ApplicaPatch',
                                        "style" => 'padding: 10px 5px; line-height: 1.2em;',
                                        'metaData' => "fitWidth: true, iconLeft: 'ui-icon ui-icon-circle-b-check'",
                                        'model' => $this->nameForm
                                    );

                                    $arrayAzioni['Segna come applicata'] = array(
                                        'id' => $this->nameForm . '_MarcaPatch',
                                        "style" => 'padding: 10px 5px; line-height: 1.2em;',
                                        'metaData' => "fitWidth: true, iconLeft: 'ui-icon ui-icon-marker'",
                                        'model' => $this->nameForm
                                    );
                                }

                                $arrayAzioni[''] = array(
                                    "style" => 'margin-bottom: -45px; border: 0; display: block; min-width: 400px;'
                                );

                                Out::msgQuestion('Info', $html, $arrayAzioni, 'auto', 'auto', 'true', false, true, true);
                                break;
                        }
                        break;
                }
                break;
        }
    }

    private function initInfo() {
        $diskSpace = disk_free_space(ITA_BASE_PATH);

        if ($diskSpace < (1024 * 1024 * 1024)) {
            $bottoni = array(
                'Conferma' => array('id' => $this->nameForm . '_ConfermaSpazioEsaurito', 'model' => $this->nameForm)
            );

            Out::msgQuestion('Spazio in esaurimento', '<b>Attenzione, lo spazio disponibile è inferiore ad 1GB!</b>', $bottoni, 'auto', 'auto', 'false');
            Out::addClass($this->nameForm . '_conf_diskSpace', 'ui-state-error');
        }

        $spacePrefix = array('B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $i = 0;
        while ($diskSpace > 1024) {
            $diskSpace /= 1024;
            $i++;
        }
        $diskSpace = round($diskSpace, 2) . ' ' . $spacePrefix[$i];

        $cacheInfo = $this->getCacheInfo();

        Out::html($this->nameForm . '_divAPCInfoContent', '<pre>' . print_r($cacheInfo['apcInfo'], true) . '</pre>');

        Out::valore($this->nameForm . '_conf_cartellaInstallazione', realpath(ITA_BASE_PATH));
        Out::valore($this->nameForm . '_conf_diskSpace', $diskSpace);
        Out::valore($this->nameForm . '_conf_versioneCorrente', AppUtility::getVersion());

        // Build Info
        $buildInfo = AppUtility::getBuildInfo();
        if (count($buildInfo) > 0) {
            $buildVersion = ((isset($buildInfo['latest']) && isset($buildInfo['latest']['version'])) ? $buildInfo['latest']['version'] : '');
            $buildDate = ((isset($buildInfo['latest']) && isset($buildInfo['latest']['date'])) ? $buildInfo['latest']['date'] : '');
            if ($buildVersion) {
                Out::show($this->nameForm . '_conf_build_version_field');
                Out::show($this->nameForm . '_conf_build_date_field');
                Out::valore($this->nameForm . '_conf_build_version', $buildVersion);
                Out::valore($this->nameForm . '_conf_build_date', $buildDate);
            } else {
                Out::hide($this->nameForm . '_conf_build_version_field');
                Out::hide($this->nameForm . '_conf_build_date_field');
            }
        }

        switch ($this->updaterLib->getDistChannel()) {
            case 'git':
                Out::show($this->nameForm . '_conf_distChannel_field');
                Out::show($this->nameForm . '_divGit');

                Out::hide($this->nameForm . '_divFtp');

                Out::valore($this->nameForm . '_conf_distChannel', $this->updaterLib->getDistChannel());
                Out::valore($this->nameForm . '_conf_git_url', $this->updaterConfig['remoteSource']);
                Out::valore($this->nameForm . '_conf_git_workingDir', $this->updaterConfig['workingDir']);
                Out::valore($this->nameForm . '_conf_git_binPath', $this->updaterConfig['gitBinPath']);
                Out::valore($this->nameForm . '_conf_git_remote', $this->updaterConfig['defaultRemote']);
                Out::valore($this->nameForm . '_conf_git_branch', $this->updaterConfig['defaultBranch']);
                break;
            case 'ftp':
                Out::show($this->nameForm . '_conf_distChannel_field');
                Out::show($this->nameForm . '_divFtp');

                Out::hide($this->nameForm . '_divGit');

                Out::valore($this->nameForm . '_conf_distChannel', $this->updaterLib->getDistChannel());
                Out::valore($this->nameForm . '_conf_ftp_host', $this->updaterConfig['ftpHost']);
                Out::valore($this->nameForm . '_conf_ftp_port', $this->updaterConfig['ftpPort']);
                Out::valore($this->nameForm . '_conf_ftp_user', $this->updaterConfig['ftpUser']);
                Out::valore($this->nameForm . '_conf_ftp_passive', $this->updaterConfig['ftpPassive']);
                Out::valore($this->nameForm . '_conf_ftp_home', $this->updaterConfig['ftpHome']);
                break;
            default:
                Out::hide($this->nameForm . '_conf_distChannel');
                Out::hide($this->nameForm . '_divFtp');
                Out::hide($this->nameForm . '_divGit');
        }
    }

    private function showInfo() {
        $this->initInfo();

        Out::show($this->nameForm . '_conf');
        Out::hide($this->nameForm . '_upd');
        Out::hide($this->nameForm . '_savepoints');
        Out::hide($this->nameForm . '_details');

        Out::hide($this->nameForm . '_menu_details');
        Out::hide($this->nameForm . '_menu_apply');
        Out::hide($this->nameForm . '_patch');
    }

    private function initCheckModules() {
        $modulesTest = new modulesTest();

        if ($modulesTest->getStatus()) {
            Out::html($this->nameForm . '_divModulesCheck', '<span style="font-weight: bolder; color: green; font-size: 20px; margin-left: 5px;" class="ui-icon ui-icon-check"></span>');
        } else {
            $html = '<span style="font-weight: bolder; color: red; font-size: 20px; margin-left: 5px;" class="ui-icon ui-icon-closethick"></span>';
            $html .= cwbLibHtml::getHtmlClickableObject($this->nameForm, $this->nameForm . '_modulesDetails', '<span class="ui-icon ui-icon-search"></span>');
            Out::html($this->nameForm . '_divModulesCheck', $html);

            $html = '<span class="ita-header-content">Stato dei moduli PHP<span>';
            $html .= cwbLibHtml::getHtmlClickableObject($this->nameForm, $this->nameForm . '_modulesClose', '<span style="float: right;" class="ui-icon ui-icon-close"></span>');
            Out::html($this->nameForm . '_titleModulesResult', $html);
        }

        Out::hide($this->nameForm . '_divModulesResult');
    }

    private function showModulesDetails() {
        $modulesTest = new modulesTest();

        $results = $modulesTest->getResults();

        $html = '<table style="margin-left: 100px">';
        $html .= '<tr><th style="width: 100px; text-align: center;"><b>Modulo</b></th><th style="width: 100px; text-align: center;"><b>Status</b></th><th style="text-align: center;"><b>Errore</b></th></tr>';
        foreach ($results as $module => $results) {
            $html .= '<tr>';
            $html .= '<td><b>' . $module . '</b></td>';
            switch ($results) {
                case modulesTest::STATUS_OK:
                    $html .= '<td style="text-align: center;"><span style="font-weight: bolder; color: green; font-size: 20px; margin-left: 5px;" class="ui-icon ui-icon-check"></span></td>';
                    $html .= '<td></td>';
                    break;
                case modulesTest::STATUS_NOTLOADED:
                    $html .= '<td style="text-align: center;"><span style="font-weight: bolder; color: red; font-size: 20px; margin-left: 5px;" class="ui-icon ui-icon-closethick"></span></td>';
                    $html .= '<td>Modulo non presente</td>';
                    break;
                case modulesTest::STATUS_WRONGVERSION:
                    $html .= '<td style="text-align: center;"><span style="font-weight: bolder; color: red; font-size: 20px; margin-left: 5px;" class="ui-icon ui-icon-closethick"></span></td>';
                    $html .= '<td>Versione del modulo non compatibile</td>';
                    break;
            }
            $html .= '</tr>';
        }
        $html .= '</table>';

        Out::html($this->nameForm . '_divModuleResultsContent', $html);
        Out::show($this->nameForm . '_divModulesResult');
    }

    private function showPatch() {
        Out::msgStop('Attenzione', 'Funzione sperimentale, procedere solo se sicuri del suo utilizzo.');

        $this->loadPatchData();

        $buildInfo = AppUtility::getBuildInfo();

        $buildVersion = isset($buildInfo['latest']) && isset($buildInfo['latest']['version']) ? $buildInfo['latest']['version'] : '';
        Out::valore($this->nameForm . '_patch_build_tag', $buildVersion);
        Out::valore($this->nameForm . '_patch_filter_toapply', $this->filterPatchToApply);

        TableView::enableEvents($this->patchGrid);
        TableView::reload($this->patchGrid);

        Out::hide($this->nameForm . '_conf');
        Out::hide($this->nameForm . '_upd');
        Out::hide($this->nameForm . '_savepoints');
        Out::hide($this->nameForm . '_details');

        Out::hide($this->nameForm . '_menu_details');
        Out::hide($this->nameForm . '_menu_apply');
        Out::show($this->nameForm . '_patch');
    }

    private function loadPatchData() {
        $this->patchesArray = array();
        include_once ITA_BASE_PATH . '/apps/Ambiente/envLibPatch.class.php';
        $this->envLibPatch = new envLibPatch();
        foreach ($this->envLibPatch->getFTPPatchList() as $ftpPatchPath) {
            $patchInfo = $this->envLibPatch->getFTPPatchInfo($ftpPatchPath);
            if (!$this->envLibPatch->checkPatchCompatibility($patchInfo['PATCH_DEFT'])) {
                continue;
            }

            $this->patchesArray[$patchInfo['PATCH_DEFT']['PATCH_NAME']] = $patchInfo;
        }
    }

    private function caricaGrigliaPatch($reloadData = false) {
        if ($reloadData) {
            $this->loadPatchData();
        }

        TableView::clearGrid($this->patchGrid);

        $gridData = array();
        foreach ($this->patchesArray as $patchInfo) {
            if ($this->filterPatchToApply && $patchInfo['PATCH_APPL']) {
                continue;
            }

            $applIcon = $patchInfo['PATCH_APPL'] ? '<i class="ui-icon ui-icon-circle-b-check" style="color: green; font-size: 1.8em;"></i>' : '<i class="ui-icon ui-icon-circle-b-close" style="color: red; font-size: 1.8em;"></i>';

            $gridData[$patchInfo['PATCH_DEFT']['PATCH_NAME']] = array(
                'PATCH_NAME' => $patchInfo['PATCH_DEFT']['PATCH_NAME'],
                'AUTHOR' => $patchInfo['PATCH_DEFT']['AUTHOR'],
                'CREATED' => $patchInfo['PATCH_DEFT']['DATAINSER'] . ' ' . $patchInfo['PATCH_DEFT']['TIMEINSER'],
                'DESCRIPTION' => $patchInfo['PATCH_DEFT']['PATCH_DES'],
                'APPLIED' => $applIcon,
                'CTXMENU' => '<i class="ui-icon ui-icon-gear" style="font-size: 1.8em;"></i>'
            );
        }

        $gridObj = new TableView($this->patchGrid, array(
            'arrayTable' => $gridData,
            'rowIndex' => 'idx'
        ));

        $gridObj->setPageNum($_POST['page'] ?: $_POST[$id]['gridParam']['page'] ?: 1);
        $gridObj->setPageRows($_POST['rows'] ?: $_POST[$id]['gridParam']['rowNum'] ?: 999);
        $gridObj->setSortIndex($_POST['sidx'] ?: '');
        $gridObj->setSortOrder($_POST['sord'] ?: '');

        $gridObj->getDataPage('json');
    }

    private function initUpd() {
        if (AppUtility::getApplicationLock()) {
            Out::hide($this->nameForm . '_upd_blocco_div');
            Out::show($this->nameForm . '_upd_sblocco_div');
        } else {
            Out::show($this->nameForm . '_upd_blocco_div');
            Out::hide($this->nameForm . '_upd_sblocco_div');
        }
    }

    private function initDefaultValues() {
        $this->filterPatchToApply = 1;

        $this->updaterConfig = array(
            'distChannel' => Config::getConf('updater.distChannel'),
            'remoteSource' => Config::getConf('updater.gitRemoteSource'),
            'workingDir' => Config::getConf('updater.gitWorkingDir'),
            'gitBinPath' => Config::getConf('updater.gitBinPath'),
            'defaultRemote' => Config::getConf('updater.gitDefaultRemote'),
            'defaultBranch' => Config::getConf('updater.gitDefaultBranch'),
            'ftpHost' => Config::getConf('updater.ftpHost'),
            'ftpUser' => Config::getConf('updater.ftpUser'),
            'ftpPassword' => Config::getConf('updater.ftpPassword'),
            'ftpPort' => Config::getConf('updater.ftpPort'),
            'ftpPassive' => Config::getConf('updater.ftpPassive'),
            'ftpHome' => Config::getConf('updater.ftpHome')
        );
    }

    private function showUpd() {
        $this->initUpd();

        Out::hide($this->nameForm . '_conf');
        Out::show($this->nameForm . '_upd');
        Out::show($this->nameForm . '_ClearCache');
        Out::hide($this->nameForm . '_savepoints');
        Out::hide($this->nameForm . '_details');

        Out::hide($this->nameForm . '_menu_details');
        Out::hide($this->nameForm . '_menu_apply');
        Out::hide($this->nameForm . '_patch');
    }

    private function initSavepoints() {
        $dirArray = $this->updaterLib->getSavePointNamesArray();
        $currentSavepoint = $this->updaterLib->getCurrentSavePointName();

        $table = array();
        foreach ($dirArray as $savepoint) {
            $row = array();
            $row['TIMESTAMP'] = $savepoint;
            $row['DATETIME'] = date('H:i:s - d/m/Y', $savepoint);
            $row['ACTIVE'] = ($savepoint == $currentSavepoint);
            $table[] = $row;
        }

        $grid = $this->helper->initializeTableArray($table);
        $this->helper->getDataPage($grid);
        TableView::enableEvents($this->nameForm . '_savepoint_grid');
    }

    private function forceToClose() {
        
    }

    private function showSavepoints() {
        $this->initSavepoints();

        Out::hide($this->nameForm . '_conf');
        Out::hide($this->nameForm . '_upd');
        Out::show($this->nameForm . '_savepoints');
        Out::hide($this->nameForm . '_details');

        Out::show($this->nameForm . '_menu_details');
        Out::show($this->nameForm . '_menu_apply');
        Out::hide($this->nameForm . '_patch');
    }

    private function showDetails() {
        Out::hide($this->nameForm . '_conf');
        Out::hide($this->nameForm . '_upd');
        Out::hide($this->nameForm . '_savepoints');
        Out::show($this->nameForm . '_details');

        Out::hide($this->nameForm . '_menu_details');
        Out::hide($this->nameForm . '_menu_apply');
        Out::hide($this->nameForm . '_patch');
    }

    private function bloccaConfirm() {
        Out::msgInputText($this->nameForm, "Conferma blocco applicativo", "Questa operazione bloccherà l'uso dell'applicativo per tutti gli utenti."
                . "Se si è sicuri di voler proseguire inserire il messaggio che verrà visualizzato fintanto che il blocco sarà attivo.");

        $bottoni = array(
            'Annulla' => array('id' => $formName . '_modal_annulla', 'model' => $formName, 'class' => 'ita-button ita-element-animate ui-corner-all ui-state-default ui-state-hover'),
            'Conferma' => array('id' => $formName . '_modal_conferma', 'model' => $formName, 'class' => 'ita-button ita-element-animate ui-corner-all ui-state-default ui-state-hover')
        );
    }

    private function blocca($msg) {
        $this->updaterLib->setLock($msg);
        $this->updaterLib->log("Creazione di un blocco applicativo con descrizione : " . $msg);

        $this->setInfo('Applicativo bloccato.');

        $this->initUpd();
    }

    private function sblocca() {
        $this->updaterLib->removeLock();
        $this->updaterLib->log("Eliminazione del blocco applicativo");

        $this->setInfo('Applicativo sbloccato.');

        $this->initUpd();
    }

    private function checkRemote() {
        $result = $this->updaterLib->checkRemoteRepository();
        $this->updaterLib->log("Controllo del server remoto per differenze con la working dir", $result);

        if ($result['result'] === false) {
            Out::msgStop("Errore", $result['error']);
        } else {
            if ($result['result']['statistics']['totFiles']) {
                $messageTemplate = <<<MESSAGE
Il download comporterà  le seguenti modifiche:
<div style="padding-left: 4em;">    
    %d file aggiunti
    %d file copiati
    %d file eliminati
    %d file modificati
    %d file rinominati
    %d file cambiati
    %d file che se modificati comporterebbero la chiusura della console per aggiornamento della stessa 
   </div>
MESSAGE;
                $message = sprintf(
                        $messageTemplate, $result['result']['statistics']['added'], $result['result']['statistics']['copied'], $result['result']['statistics']['deleted'], $result['result']['statistics']['modified'], $result['result']['statistics']['renamed'], $result['result']['statistics']['changed'], $result['result']['types']['potentialChangedUpdaterFile']
                );
            } else {
                $message = 'Nessun aggiornamento presente.';
            }

            $this->setInfo('Verifica del repository effettuata.', $message);
        }
    }

    private function downloadRemote() {
        $result = $this->updaterLib->downloadFromRemoteRepository();
        $this->updaterLib->log("Download dal server remoto per aggiornamento della working dir", $result);

        if ($result['result'] === false) {
            Out::msgStop("Errore", $result['error']);
        } else {
            if ($result['result']['statistics']['totFiles']) {
                $messageTemplate = <<<MESSAGE
Il download è stato eseguito con le seguenti modifiche:
<div style="padding-left: 4em;">    %d file aggiunti
    %d file copiati
    %d file eliminati
    %d file modificati
    %d file rinominati
    %d file cambiati</div>
MESSAGE;
                $message = sprintf(
                        $messageTemplate, $result['result']['statistics']['added'], $result['result']['statistics']['copied'], $result['result']['statistics']['deleted'], $result['result']['statistics']['modified'], $result['result']['statistics']['renamed'], $result['result']['statistics']['changed']
                );
                //Verifica se ho aggiornato dei file del updater 
                if ($result['result']["types"]["allUpdaterFile"]) {
                    $bottoni = array(
                        'Conferma' => array('id' => $this->nameForm . '_forced_to_closed', 'model' => $this->nameForm)
                    );

                    Out::msgQuestion("Nuova versione della console di aggiornamento", "E' necessario riaprire la console di gestione per proseguire con aggiornamento dell'applicativo", $bottoni);
                }
            } else {
                $message = 'Nessun aggiornamento scaricato.';
            }

            $this->setInfo('Download dal repository remoto effettuato.', $message);
        }
        return $result;
    }

    private function checkWorkingDir() {
        $diff = $this->updaterLib->differCompare();
        $this->updaterLib->log("Controllo delle differenze fra la cartella di installazione dell'applicativo e la working dir", $diff);

        $msg = "<b>File da cancellare (" . count($diff['D']) . "):</b>";
        foreach ($diff['D'] as $file) {
            $msg .= "\r\n- " . $file;
        }
        $msg .= "\r\n\r\n<b>File da aggiungere (" . count($diff['A']) . "):</b>";
        foreach ($diff['A'] as $file) {
            $msg .= "\r\n- " . $file;
        }
        $msg .= "\r\n\r\n<b>File da modificare (" . count($diff['M']) . "):</b>";
        foreach ($diff['M'] as $file) {
            $msg .= "\r\n- " . $file;
        }

        $this->setInfo('Controllo dei file da aggiornare effettuato.', $msg);
    }

    private function downloadWorkingDir($force = false) {
        if (AppUtility::getApplicationLock() === false) {
            Out::msgStop("Errore", "Prima di proseguire con l'aggiornamento è necessario bloccare l'applicativo");
            return;
        }

        $savePointFactory = itaSavePointFactory::getInstance();
        try {
            $diff = $this->updaterLib->createAndApplySavepoint($force);

            $this->updaterLib->log("Applicazione delle differenze dalla working dir alla cartella d'installazione del software", $diff);

            $msg = "<b>File cancellati (" . count($diff['D']) . "):</b>";
            foreach ($diff['D'] as $file) {
                $msg .= "\r\n- " . $file;
            }
            $msg .= "\r\n\r\n<b>File aggiunti (" . count($diff['A']) . "):</b>";
            foreach ($diff['A'] as $file) {
                $msg .= "\r\n- " . $file;
            }
            $msg .= "\r\n\r\n<b>File modificati (" . count($diff['M']) . "):</b>";
            foreach ($diff['M'] as $file) {
                $msg .= "\r\n- " . $file;
            }

            $this->setInfo('Aggiornamento effettuato con successo.', $msg);

//            $preTest = $this->updaterLib->checkTests();
//            if($preTest['esito'] === false){
//                $this->updaterLib->log("Errore bloccante prima dell'esecuzione dell'aggiornamento.", $preTest);
//                $this->setInfo("Errore bloccante prima dell'esecuzione dell'aggiornamento.", print_r($preTest,true));
//                return;
//            }
//            $savePoint = $savePointFactory->createNewSavePoint($force);
//            $this->updaterLib->log("Tentativo di creazione di un nuovo savepoint",$savePoint);
        } catch (ItaException $e) {
            if ($e->getNativeErrorCode() == -99) {
                $bottoni = array(
                    'Annulla' => array('id' => $this->nameForm . '_upd_downloadWD_annulla', 'model' => $this->nameForm),
                    'Conferma' => array('id' => $this->nameForm . '_upd_downloadWD_force', 'model' => $this->nameForm)
                );
                Out::msgQuestion("Attenzione", "Attualmente non si sta usando l'ultimo savepoint disponibile, proseguire comporterà "
                        . "l'eliminazione di tutti i savepoints successivi a quello attuale. Sei sicuro di voler proseguire?", $bottoni);
            } else {
                Out::msgInfo("Impossibile applicare aggiornamenti", $e->getNativeErroreDesc());
            }
            return;
        }

//        try {
//            $savePoint->applyDiff();
//
//            $diff = $savePoint->getSavePointDiff();
//            $this->updaterLib->log("Applicazione delle differenze dalla working dir alla cartella d'installazione del software",$diff);
//            
//            $msg = "<b>File cancellati (" . count($diff['D']) . "):</b>";
//            foreach ($diff['D'] as $file) {
//                $msg .= "\r\n- " . $file;
//            }
//            $msg .= "\r\n\r\n<b>File aggiunti (" . count($diff['A']) . "):</b>";
//            foreach ($diff['A'] as $file) {
//                $msg .= "\r\n- " . $file;
//            }
//            $msg .= "\r\n\r\n<b>File modificati (" . count($diff['M']) . "):</b>";
//            foreach ($diff['M'] as $file) {
//                $msg .= "\r\n- " . $file;
//            }
//            
////            $postTest = $this->updaterLib->checkTests();
////            if($postTest['esito'] === false){
////                $this->updaterLib->log("Errore bloccante dopo l'esecuzione dell'aggiornamento.",$postTest);
////                Out::valore($this->nameForm . '_details_name', $savePoint->getName());
////                $bottoni = array(
////                                'Revert' => array('id' => $this->nameForm . '_ConfermaRevert', 'model' => $this->nameForm),
////                                'Continua' => array('id' => $this->nameForm . '_AnnullaRevert', 'model' => $this->nameForm)
////                            );
////                Out::msgQuestion("Errore", "Errore bloccante dopo l'esecuzione dell'aggiornamento.<br>". print_r($postTest,true), $bottoni);
////                return;
////            }
////            
////            $warning = $this->updaterLib->compareTests($preTest, $postTest);
//            if(!empty($warning)){
//                $this->setInfo('Aggiornamento effettuato con avvisi.', $msg."<br>Warning:<br>".print_r($warning,true));
//            }
//            else{
//                $this->setInfo('Aggiornamento effettuato con successo.', $msg);
//            }
//        } catch (ItaException $e) {
//            Out::msgInfo("Impossibile applicare aggiornamenti", $e->getNativeErroreDesc());
//        }
    }

    private function migrate() {
        $model = "envMigrationConsole";
        itaLib::openDialog($model);
        $formObj = itaModel::getInstance($model);
        if (!$formObj) {
            Out::msgStop("Errore", "Apertura console migrations fallita");
            return;
        }
        $formObj->setEvent('openform');
        $formObj->parseEvent();
    }

    private function autoUpdate() {
        //TODO
        Out::msgInfo("Funzionalità non disponibile", "La funzionalità selezionata non è al momento disponibile");
    }

    private function vediDettagli($timestamp = null) {
        if (!isSet($timestamp) || $timestamp == 'null') {
            Out::msgStop("Errore", "Selezionare un savepoint dalla griglia se si intende vederne i dettagli");
            return;
        }

        $currentSavePoint = $this->updaterLib->getCurrentSavePointName();
        if ($currentSavePoint == $timestamp) {
            Out::show($this->nameForm . '_details_revert');
            Out::hide($this->nameForm . '_details_apply');
        } else {
            Out::hide($this->nameForm . '_details_revert');
            Out::show($this->nameForm . '_details_apply');
        }

        $savepointFactory = itaSavePointFactory::getInstance();
        try {
            $savepoint = $savepointFactory->getSpecificSavePoint($timestamp);
        } catch (ItaException $e) {
            Out::msgStop("Errore", $e->getNativeErroreDesc());
        }

        $nome = $timestamp;
        $data = date("d-m-Y", $timestamp);
        $ora = date("H:i:s", $timestamp);
        $files = $savepoint->getSavePointDiff();
        $metadata = $savepoint->getSavePointMetaData();
        $hash = $savepoint->getSavePointHash();

        Out::valore($this->nameForm . '_details_name', $nome);
        Out::valore($this->nameForm . '_details_hash', $hash);
        Out::valore($this->nameForm . '_details_date', $data);
        Out::valore($this->nameForm . '_details_time', $ora);

        if (isSet($files['A']) && count($files['A']) > 0) {
            $html = "<ul>";
            foreach ($files['A'] as $file)
                $html .= "<li style=\"list-style-type: disc; padding-left: 10px; margin-left: 25px\">$file</li>";
            $html .= "</ul>";

            Out::html($this->nameForm . '_details_filesA_writeArea', $html);
            Out::show($this->nameForm . '_details_filesA_div');
        } else {
            Out::hide($this->nameForm . '_details_filesA_div');
        }

        if (isSet($files['D']) && count($files['D']) > 0) {
            $html = "<ul>";
            foreach ($files['D'] as $file)
                $html .= "<li style=\"list-style-type: disc; padding-left: 10px; margin-left: 25px\">$file</li>";
            $html .= "</ul>";

            Out::html($this->nameForm . '_details_filesD_writeArea', $html);
            Out::show($this->nameForm . '_details_filesD_div');
        } else {
            Out::hide($this->nameForm . '_details_filesD_div');
        }

        if (isSet($files['M']) && count($files['M']) > 0) {
            $html = "<ul>";
            foreach ($files['M'] as $file)
                $html .= "<li style=\"list-style-type: disc; padding-left: 10px; margin-left: 25px\">$file</li>";
            $html .= "</ul>";

            Out::html($this->nameForm . '_details_filesM_writeArea', $html);
            Out::show($this->nameForm . '_details_filesM_div');
        } else {
            Out::hide($this->nameForm . '_details_filesM_div');
        }

        if (isSet($metadata) && ((is_array($metadata) && !empty($metadata)) || (!is_array($metadata) && trim($metadata) != ''))) {
            Out::html($this->nameForm . '_details_metadata_writeArea', '<pre>' . print_r($metadata, true) . '</pre>');
            Out::show($this->nameForm . '_details_metadata_div');
        } else {
            Out::hide($this->nameForm . '_details_metadata_div');
        }

        $this->showDetails();
    }

    private function applicaSavepoint($timestamp = null) {
        if (!isSet($timestamp) || $timestamp == 'null') {
            Out::msgStop("Errore", "Selezionare un savepoint che si intende applicare");
            return;
        }

        try {
            $this->updaterLib->log("Applicazione del savepoint " . $timestamp . " da " . $this->updaterLib->getCurrentSavePointName());
            $this->updaterLib->applySavePoint($timestamp);
        } catch (ItaException $ex) {
            Out::msgStop("Errore", "Errore durante l'applicazione del savepoint: " . $ex->getNativeErroreDesc());
        }
    }

    private function revertSavepoint($timestamp = null) {
        if (!isSet($timestamp) || $timestamp == 'null') {
            Out::msgStop("Errore", "Selezionare un savepoint che si intende annullare");
            return;
        }

        if ($this->updaterLib->getCurrentSavePointName() !== $timestamp) {
            Out::msgStop("Errore", "Il savepoint selezionato per l'annullamento non è quello attualmente attivo");
        }

        $spFactory = itaSavePointFactory::getInstance();
        $savepoint = $spFactory->getCurrentSavePointName();

        $this->updaterLib->log("Revert del savepoint " . $this->updaterLib->getCurrentSavePointName());
        $savepoint->revertDiff();
    }

    private function confirmApply($timestamp = null) {
        if (!isSet($timestamp) || $timestamp == 'null') {
            Out::msgStop("Errore", "Selezionare un savepoint che si intende applicare");
            return;
        }

        Out::valore($this->nameForm . '_details_name', $timestamp);
        $bottoni = array(
            'Annulla' => array('id' => $this->nameForm . '_AnnullaApply', 'model' => $this->nameForm),
            'Conferma' => array('id' => $this->nameForm . '_ConfermaApply', 'model' => $this->nameForm)
        );
        Out::msgQuestion("Continua", "Sei sicuro di voler applicare il savepoint " . $timestamp . "?", $bottoni);
    }

    private function confirmRevert($timestamp = null) {
        if (!isSet($timestamp) || $timestamp == 'null') {
            Out::msgStop("Errore", "Selezionare un savepoint che si intende applicare");
            return;
        }

        Out::valore($this->nameForm . '_details_name', $timestamp);
        $bottoni = array(
            'Annulla' => array('id' => $this->nameForm . '_AnnullaRevert', 'model' => $this->nameForm),
            'Conferma' => array('id' => $this->nameForm . '_ConfermaRevert', 'model' => $this->nameForm)
        );
        Out::msgQuestion("Continua", "Sei sicuro di voler annullare il savepoint " . $timestamp . " tornando al precedente?", $bottoni);
    }

    private function setInfo($info, $message = '') {
        if (!is_string($info))
            $info = print_r($info, true);
        if (!is_string($message))
            $message = print_r($message, true);

        $html = sprintf('<b>[%s] %s</b>', date('d/m H:i:s'), $info);

        if (!empty($message)) {
            $html .= sprintf('<br />%s', nl2br($message));
        }

        Out::html($this->nameForm . '_infoBox', $html);
    }

    private function clearCache($type) {
        if (AppUtility::getApplicationLock() === false) {
            Out::msgStop("Errore", "Prima di proseguire con la pulizia della cache è necessario bloccare l'applicativo");
            return;
        }

        include_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';

        switch ($type) {
            case 'apc':
                $cache = CacheFactory::newCache(CacheFactory::TYPE_APC);
                break;
            case 'file':
                $cache = CacheFactory::newCache(CacheFactory::TYPE_FILE);
                break;
        }
        $cache->clear();
    }

    private function viewBuildHistory() {
        $buildHistory = AppUtility::getBuildHistory();
        if (!$buildHistory) {
            Out::msgInfo("INFO", "Nessuna informazione relativa alla build history disponibile");
            return;
        }

        $buildHistoryArray = array();
        krsort($buildHistory);
        foreach ($buildHistory as $key => $value) {
            $buildHistoryArray[] = array("versione" => $key, "data" => $value['date']);
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco delle build applicate',
            "width" => '350',
            "height" => '350',
            "rowNum" => '20',
            "rowList" => '[]',
            "arrayTable" => $buildHistoryArray,
            "colNames" => array(
                "Versione",
                "Data applicazione"
            ),
            "colModel" => array(
                array("name" => 'versione', "width" => 150),
                array("name" => 'data', "width" => 190)
            ),
            "pgbuttons" => 'false',
            "pginput" => 'false',
            'navButtonEdit' => 'false'
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        Out::setDialogTitle("utiRicDiag", "Build History");
        $model();
    }

    private function getCacheInfo() {
        $cacheInfo = array(
            "apcInfo" => "",
            "fileInfo" => ""
        );

        include_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';
        $cache = CacheFactory::newCache(CacheFactory::TYPE_FILE);
        $cacheInfo['fileInfo'] = $cache->getInfo();
        $cacheAPC = CacheFactory::newCache(CacheFactory::TYPE_APC);
        $cacheInfo['apcInfo'] = $cacheAPC->getInfo;
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function close() {
        parent::close();
        itaLib::closeForm($this->nameForm);
    }

    public function checkDefaultBranch() {
        Out::hide($this->nameForm . '_divReleaseCW');
        $defaultBranch = Config::getConf('updater.gitDefaultBranch');
        
        if (
            self::UPDATER_BRANCH != $defaultBranch &&
            self::UPDATER_BRANCH_TEST != $defaultBranch 
        ) {
            if (method_exists(Out, 'setInputTooltip')) {
                Out::setInputTooltip($this->nameForm . '_conf_git_branch', 'Si sta utilizzando un ramo personalizzato!');
            }

            Out::msgQuestion('Attenzione', "Si sta utilizzando l'updater con un ramo <b>personalizzato</b>. (<i>$defaultBranch</i>)<br>Procedere con questo ramo?", array(
                'F8 - Annulla' => array('id' => $this->nameForm . '_AnnullaPersonalizzato', 'model' => $this->nameForm, 'shortCut' => "f8"),
                'F5 - Conferma' => array('id' => $this->nameForm . '_ConfermaPersonalizzato', 'model' => $this->nameForm, 'shortCut' => "f5")
                    ), 'auto', 'auto', 'false');
            return false;
        }

        $isCityware = itaHooks::isActive('citywareHook.php');
        if (!$isCityware) {
            Out::show($this->nameForm . '_divReleaseCW');
            Out::html($this->nameForm . '_divReleaseCW', '<b>Moduli CityWare non collegati</b>');
            return true;
        }

        try {
            $ITALWEBDB = ItaDB::DBOpen('ITALWEBDB', '');
            $domains_rec = ItaDB::DBSQLSelect($ITALWEBDB, 'SELECT CODICE FROM DOMAINS ORDER BY SEQUENZA ASC', false);
            $enteCityware = $domains_rec['CODICE'];
        } catch (Exception $e) {
            Out::msgStop('Errore', 'Errore durante la lettura dei DOMAINS, impossibile proseguire: ' . $e->getMessage());
            $this->close();
            return false;
        }

        try {
            include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BGE.class.php';
            $cwbLibBGE = new cwbLibDB_BGE();
            $cwbLibBGE->setEnte($enteCityware);
            $cwbLibBGE->leggi("SELECT * FROM BGE_RELEASE WHERE COD_PRODOTTO = 1", $result);
        } catch (Exception $e) {
            Out::msgStop('Errore', 'Errore durante la lettura della release BGE_RELEASE, impossibile proseguire: ' . $e->getMessage());
            $this->close();
            return false;
        }

        if (!count($result)) {
            Out::msgStop('Errore', 'Record su BGE_RELEASE non presente, impossibile proseguire.');
            $this->close();
            return false;
        }

        if (!$result['VERSIONE']) {
            Out::msgStop('Errore', 'Versione software su BGE_RELEASE non presente, impossibile proseguire.');
            $this->close();
            return false;
        }

        if (method_exists(Out, 'setInputTooltip')) {
            Out::setInputTooltip($this->nameForm . '_conf_git_branch', 'Il ramo è stato impostato attraverso il numero di release Cityware.');
        }

        $versioneParts = explode('.', $result['VERSIONE']);

        if (count($versioneParts) !== 2) {
            Out::msgStop('Errore', 'Versione software su BGE_RELEASE con formato non valido (' . $result['VERSIONE'] . ').<br>Verificare che il formato sia "XX.YY".');
            $this->close();
            return false;
        }

        $versioneCW = str_pad((int) $versioneParts[0], 2, '0', STR_PAD_LEFT) . '.' . str_pad((int) $versioneParts[1], 2, '0', STR_PAD_LEFT);

        Out::show($this->nameForm . '_divReleaseCW');
        Out::html($this->nameForm . '_divReleaseCW', 'Versione CityWare corrente (BGE_RELEASE): <b>' . $versioneCW . '</b>');

        $this->updaterConfig['defaultBranch'] = $defaultBranch . '-' . $versioneCW;
        Out::valore($this->nameForm . '_conf_git_branch', $this->updaterConfig['defaultBranch']);
    }

}
