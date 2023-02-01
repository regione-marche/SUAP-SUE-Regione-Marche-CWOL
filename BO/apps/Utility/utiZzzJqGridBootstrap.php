<?php
include_once ITA_LIB_PATH  . '/itaPHPCore/itaFrontController.class.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_GENERIC.class.php';

function utiZzzJqGridBootstrap() {
    $utiZzzJqGridBootstrap = new utiZzzJqGridBootstrap();
    $utiZzzJqGridBootstrap->parseEvent();
    return;
}

class utiZzzJqGridBootstrap extends itaFrontController {

    public function parseEvent() {
        switch($_POST['event']){
            case 'onClick':
                switch($_POST['id']){
                    case $this->nameForm . '_injDB':
                        $this->testLoad('inj', 'db');
                        break;
                    case $this->nameForm . '_modalDB':
                        $this->testLoad('modal', 'db');
                        break;
                    case $this->nameForm . '_injArray':
                        $this->testLoad('inj', 'array');
                        break;
                    case $this->nameForm . '_modalArray';
                        $this->testLoad('modal', 'array');
                        break;
                }
                break;
            case 'add':
            case 'view':
            case 'dbClickRow':
            case 'select':
            case 'multiselect':
            case 'detail':
            case 'delete':
            case 'printPdf':
            case 'printXslx':
            case 'closePortlet':
                Out::msgInfo('Info', 'Scatenato l\'evento '.$_POST['event'].' da '.$_POST['id'].'.<br>Contenuto di $this->formData[\'returnData\']:<br>'.var_export($this->formData['returnData'], true));
                break;
        }
    }
    
    private function testLoad($out, $src){
        if($out == 'inj'){
            Out::html($this->nameForm . '_gridContainer', '');
            $model = cwbLib::innestaForm('utiJqGridCustom', $this->nameForm . '_gridContainer');
            $model->setReturnModel($this->nameFormOrig);
            $model->setReturnNameForm($this->nameForm);
        }
        else{
            $model = cwbLib::apriFinestra('utiJqGridCustom', $this->nameForm, null, null, null, $this->nameFormOrig, null);
        }
        
        
        
        if($src == 'db'){
            $dbName = cwbLib::getCitywareConnectionName();
            $sql = 'SELECT * FROM BOR_LIVELL';

            $colModel = array(
                array('name'=>'IDLIVELL', 'title'=>'ID', 'class'=>'{align:\'center\', fixed: true}', 'width'=>'80px'),
                array('name'=>'DES_LIVELL', 'title'=>'Descrizione')
            );
            $metadata = array(
                'caption'=>'Livelli organigramma',
                'shrinkToFit'=>false,
                'width'=>1000,
                'readerId'=>'IDLIVELL',
                'sortname'=>'IDLIVELL',
                'navGrid'=>true,
                'navButtonDel'=>true,
                'navButtonAdd'=>true,
                'navButtonEdit'=>true,
                'navButtonExcel'=>false,
                'navButtonPrint'=>false,
                'filterToolbar'=>true,
                'navButtonRefresh'=>true,
                'resizeToParent'=>true,
                'showInlineButtons'=>'{view: true, edit: true, delete: false}',
                'showAuditColumns'=>true,
                'showRecordStatus'=>false,
    //            'onSelectRow'=>true,
                'multiselect'=>true,
                'multiselectEvents'=>true,
                'navButtonExcel'=>true,
                'navButtonPrint'=>true,
                'rowNum'=>25,
                'rowList'=>'[25, 50, 100, 200, \'Tutte\']',
                'reloadOnResize'=>false
            );

            $model->setJqGridModel($colModel, $metadata);
            $model->setJqGridDataDB($sql, $dbName, array(), '', '');
        }
        else{
            //VALORIZZO L'ARRAY CON I DATI
            $data = array();
            for($i=0;$i<100;$i++){
                $data[] = array(
                    'COL1'=>rand(0,1000),
                    'COL2'=>rand(0,1000),
                    'COL3'=>md5(rand(0,1000)),
                    'COL4'=>sha1(rand(0,1000)),
                    'COL5'=>rand(0,1000),
                    'FLAG_DIS'=>rand(0,1),
                    'DATAOPER'=>rand(2000,2020).'-'.rand(1,12).'-'.rand(0,28),
                    'TIMEOPER'=>rand(0,23).':'.rand(0,59).':'.rand(0,59),
                    'CODUTE'=>substr(md5(rand()), 0, rand(5,10))
                );
            }


            //MODELLO DELLA TABELLA
            $colModel = array(
                array('name'=>'COL1', 'title'=>'Colonna 1', 'class'=>'{align:\'right\'}'),
                array('name'=>'COL2', 'title'=>'Colonna 2', 'class'=>'{align:\'center\'}'),
                array('name'=>'COL3', 'title'=>'Colonna 3', 'class'=>'{align:\'left\'}'),
                array('name'=>'COL4', 'title'=>'Colonna 4', 'class'=>'{fixed: true}', 'width'=>125),
                array('name'=>'COL5', 'title'=>'Colonna 5'),
            );

            //METADATI DELLA TABELLA
            $metadata = array(
                'caption'=>'Grid di test',
                'shrinkToFit'=>false,
                'width'=>1000,
                'sortname'=>'COL1',
                'navGrid'=>true,
                'navButtonDel'=>true,
                'navButtonAdd'=>true,
                'navButtonEdit'=>true,
                'navButtonExcel'=>false,
                'navButtonPrint'=>false,
                'filterToolbar'=>true,
                'navButtonRefresh'=>true,
                'resizeToParent'=>true,
                'showInlineButtons'=>'{view: true, edit: true, delete: false}',
                'showAuditColumns'=>false,
                'showRecordStatus'=>false,
                'onSelectRow'=>true,
//                'multiselect'=>true,
//                'multiselectEvents'=>true,
                'navButtonExcel'=>true,
                'navButtonPrint'=>true,
                'rowNum'=>25,
                'rowList'=>'[25, 50, 100, 200]',
                'reloadOnResize'=>false
            );

            $model->setJqGridModel($colModel, $metadata);
            $model->setJqGridDataArray($data);
        }
        
        $model->setReturnEvents('view', 'dbClickRow', 'select', 'multiselect', 'detail', 'delete', 'printPdf', 'printXslx', 'add', 'closePortlet');
        $model->setTitle('Grid di prova');
        $model->render();
    }
}

