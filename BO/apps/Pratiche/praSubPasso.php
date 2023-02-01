<?php

/*
 * PHP Version 5
 *
 * @category   itaModel
 * @package    /apps/
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Simone Franchi <sfranchi@selecfi.it>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPassoCaratteristiche.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praCompPassoDett.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';

class praSubPasso extends itaModel {

    public $currGesnum;
    public $keyPasso;
    public $propas_rec;
    public $praLib;
    public $praReadOnly;
    public $workDate;
    public $workYear;

    function __construct() {
        parent::__construct();

        try{
            // Non capito dove è definita la variabile "profilo"
            $this->profilo = proSoggetto::getProfileFromIdUtente();
            
            
            $data = App::$utente->getKey('DataLavoro');
            if ($data != '') {
                $this->workDate = $data;
            } else {
                $this->workDate = date('Ymd');
            }
            $this->workYear = date('Y', strtotime($this->workDate));
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        
        
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_currGesnum', $this->currGesnum);
            App::$utente->setKey($this->nameForm . '_keyPasso', $this->keyPasso);
            App::$utente->setKey($this->nameForm . '_propas_rec', $this->propas_rec);
            App::$utente->setKey($this->nameForm . '_praReadOnly', $this->praReadOnly);
            App::$utente->setKey($this->nameForm . '_workDate', $this->workDate);
            App::$utente->setKey($this->nameForm . '_workYear', $this->workYear);
            
        }
    }

    public function postInstance() {
        parent::postInstance();
        $this->praLib = new praLib;
        $this->currGesnum = App::$utente->getKey($this->nameForm . "_currGesnum");
        $this->keyPasso = App::$utente->getKey($this->nameForm . "_keyPasso");
        $this->propas_rec = App::$utente->getKey($this->nameForm . "_propas_rec");
        $this->praReadOnly = App::$utente->getKey($this->nameForm . "_praReadonly");
    }

    public function getParentObj() {
        $model = $this->getReturnModelOrig();
        $form = $this->getReturnModel();
        /* @var $objModel itaModel */
        return itaModel::getInstance($model, $form);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }

        $model = $this->returnModelOrig;
        $form = $this->returnModel;
        /* @var $objModel itaModel */
        $objModel = itaModel::getInstance($model, $form);
        $objModel->setEvent($this->returnEvent);
        $objModel->parseEvent();
    }

    public function close() {
        parent::close();
        App::$utente->removeKey($this->nameForm . '_currGesnum');
        App::$utente->removeKey($this->nameForm . '_keyPasso');
        App::$utente->removeKey($this->nameForm . '_propas_rec');
        App::$utente->removeKey($this->nameForm . '_praReadonly');
        App::$utente->removeKey($this->nameForm . '_workDate');
        App::$utente->removeKey($this->nameForm . '_workYear');
        
    }

    public function getKeyPasso() {
        return $this->keyPasso;
    }

    public function setKeyPasso($keyPasso) {
        $this->keyPasso = $keyPasso;
    }

    public function getCurrGesnum() {
        return $this->currGesnum;
    }

    public function setCurrGesnum($currGesnum) {
        $this->currGesnum = $currGesnum;
    }

}