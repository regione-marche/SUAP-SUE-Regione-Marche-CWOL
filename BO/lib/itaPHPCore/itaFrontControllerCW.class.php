<?php
require_once ITA_BASE_PATH . '/apps/CityBase/cwbBpaGenHelper.class.php';
require_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';

class itaFrontControllerCW extends itaFrontController {
    protected $AUTOR_MODULO;                // Check autorizzazioni: modulo da controllare
    protected $AUTOR_NUMERO;                // Check autorizzazioni: numero autorizzazione da controllare    
    protected $wizardParameters;            // serve per passare dei parametri tra una form e l'altra nel caso venga usata all'interno di un wizard
    
    public function initHelper() {
        $this->helper = new cwbBpaGenHelper();
        $this->helper->setNameForm($this->nameForm);
        $this->helper->setModelName($this->nameFormOrig);
        $this->helper->setGridName($this->GRID_NAME);
        $this->helper->setDb($this->MAIN_DB);
    }
    
    public function parseEvent() {
        parent::parseEvent();
        switch($_POST['event']){
            case 'openportlet':
                itaLib::openForm($_POST['nameform'], '', true, $container = $_POST['context'] . "-content");
                break;
            case 'openform':
                Out::css($this->nameForm, 'overflow', 'visible');
                Out::css($this->nameForm . '_wrapper', 'overflow', 'visible');
                break;
        }
    }

    public function getWizardParameters() {
        return $this->wizardParameters;
    }

    public function setWizardParameters($wizardParameters) {
        $this->wizardParameters = $wizardParameters;
    }

    public function getAUTOR_MODULO() {
        return $this->AUTOR_MODULO;
    }

    public function getAUTOR_NUMERO() {
        return $this->AUTOR_NUMERO;
    }

    public function setAUTOR_MODULO($AUTOR_MODULO) {
        $this->AUTOR_MODULO = $AUTOR_MODULO;
    }

    public function setAUTOR_NUMERO($AUTOR_NUMERO) {
        $this->AUTOR_NUMERO = $AUTOR_NUMERO;
    }
}
?>
