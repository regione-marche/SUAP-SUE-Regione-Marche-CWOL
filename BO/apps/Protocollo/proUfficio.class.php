<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author Michele Moscioni <michele.moscioni@italsoft.eu>
  * @copyright  1987-2013 Italsoft snc
 * @license
 * @version    16.07.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proOrgLayout.class.php';

class proUfficio {
    public $PROT_DB;
    /* @var $proLib proLib */
    public $proLib;
    public $ufficio;
    public $orgKey;
    public $orgKeyLayout;
    private $lastExitCode;
    private $lastMessage;

    /**
     * 
     * @param type $proLib
     * @param type $codProt
     * @return boolean|\praAggiuntivi
     */
    public static function getInstance($proLib, $ufficio = '') {
        try {
            $obj = new proUfficio();
        } catch (Exception $exc) {
            App::log($exc);
            return false;
        }
        if (!$proLib) {
            return false;
        }
        $obj->proLib = $proLib;
        if (!$obj->caricaUfficio($ufficio)) {
            return false;
        }
        return $obj;
    }

    public function getLastExitCode() {
        return $this->lastExitCode;
    }

    public function getLastMessage() {
        return $this->lastMessage;
    }

    public function getUfficio() {
        return $this->ufficio;
    }

    public function getOrgKey() {
        return $this->orgKey;
    }

    public function getOrgKeyLayout() {
        return $this->orgKeyLayout;
    }

    public function caricaUfficio($ufficio) {
        $anauff_rec = $this->proLib->GetAnauff($ufficio);
        if(!$anauff_rec){
            return false;
        }

        $descrizioneServizio='';
        if($anauff_rec['UFFSER']){
            $anaservizi_rec = $this->proLib->getAnaservizi($anauff_rec['UFFSER']);
            if($anaser_rec){
                $descrizioneServizio=$anaservizi_rec['SERDES'];                
            }
        }
        $this->ufficio=array();
        $this->ufficio['CODICEUFFICIO']=$anauff_rec['UFFCOD'];
        $this->ufficio['DESCRIZIONEUFFICIO']=$anauff_rec['UFFDES'];
        $this->ufficio['CODICESOGGETTO']='';
        $this->ufficio['DESCRIZIONESOGGETTO']='';
        $this->ufficio['CODICERESPONSABILEUFFICIO']=$anauff_rec['UFFRES'];
        $this->ufficio['SCARICA']='';
        $this->ufficio['LIVELLOPROTEZIONE']='';
        $this->ufficio['GESTISCI']='';
        $this->ufficio['CODICESERVIZIO']=$anauff_rec['UFFSER'];
        $this->ufficio['SERVIZIO']=$descrizioneServizio;
        $this->ufficio['CODICERUOLO']='';                
        $this->ufficio['RUOLO']='';
            if ($this->ufficio['LIVELLOPROTEZIONE'] == 0) {
                $this->ufficio['LIVELLOPROTEZIONE'] = 1;
            }

        $this->caricaOrgkey($this->ufficio);
        return true;
    }

    public function caricaOrgkey($ufficio) {
        $arrKey = array();
        foreach (proOrgLayout::caricaLayout() as $orgNode) {
            switch ($orgNode) {
                case "ENTE" :
                    $arrKey[] = $this->proLib->getAOOAmministrazione();
                    break;
                case "SETTORE" :
                    $arrKey[] = $ufficio['CODICESERVIZIO'];
                    break;
                case "UFFICIO" :
                    $arrKey[] = $ufficio['CODICEUFFICIO'];
                    break;
                case "SOGGETTO" :
                    $arrKey[] = $ufficio['CODICESOGGETTO'];
                    break;
            }
        }
        $this->orgKey = implode(".", $arrKey);
        $this->orgKeyLayout = implode(".", proOrgLayout::caricaLayout());
        return true;
    }

}

?>
