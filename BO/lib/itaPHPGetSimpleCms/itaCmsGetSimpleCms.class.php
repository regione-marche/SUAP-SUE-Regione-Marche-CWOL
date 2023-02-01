<?php

require_once(ITA_LIB_PATH . '/itaPHPCore/itaCms.php');

/**
 *
 * Implementazione del cms getSimpleCms
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPAlfcity
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    25.10.2016
 * @link
 * @see
 * 
 */
class itaCmsGetSimpleCms implements itaCms {
    /*
     * Torna il content della pagina/componente. Prima cerca la pagina con quell'id e se non la trova ricerca sui component
     * 
     * @params String $id nome della pagina/componente da chiamare
     * $params String/array $params i parametri da passare se ci sono
     * 
     * return String Codice html/php della pagina/componente cercato
     */

    public function getContent($id, $params) {
        $component_out = $this->getPage($id);

        if (!$component_out) {
            $component_out = $this->getComponent($id, $params);
        }

        return $component_out;
    }

    /*
     * Torna il footer
     */

    public function getFooter() {
        return $this->execute('get_footer', get_page_slug(false));
    }

    /*
     * Torna l'header
     */

    public function getHeader() {
        return $this->execute('get_header', get_page_slug(false));
    }

    /*
     * Torna il menù
     */

    public function getNavigation() {
        return $this->execute('get_navigation', get_page_slug(false));
    }

    public function getStyleURL() {
        return $this->execute('get_theme_url', $id);
    }

    /*
     * Esegue la import del file index
     */

    public function loadCms() {
        ob_end_clean();
        ob_start();
        $savedir = getcwd();
        chdir(ITA_BASE_PATH . '/cms');
        require_once $this->getCmsRequire();
        if (!chdir($savedir)) {
            App::log("ERRORE");
        }
        ob_end_clean();
        ob_start();
    }

    /*
     * Ritorna il path dell'index su cui eseguire la require del cms
     */
    public function getCmsRequire() {
        return ITA_BASE_PATH . '/cms/index.php';
    }

    private function getPage($id) {
        return $this->execute('getPageContent', $id);
    }

    private function getComponent($id, $params) {
        // setto i parametri su una variabile globale, se ci sono, per utilizzarli all'interno del component
        if ($params) {
            global $getSimpleCmsComponentParams;
            $getSimpleCmsComponentParams = $params;
        }

        $toReturn = $this->execute('get_component', $id);
        if ($params) {
            $getSimpleCmsComponentParams = null;
            unset($getSimpleCmsComponentParams);
        }

        return $toReturn;
    }

    private function execute($methodName, $param) {
        ob_start();
        $savedir = getcwd();
        chdir(ITA_BASE_PATH . '/cms');

        if ($param) {
            $methodName($param);
        } else {
            $methodName();
        }

        chdir($savedir);
        $component_out = ob_get_clean();
        ob_end_clean();
        ob_start();

        return $component_out;
    }

}

?>