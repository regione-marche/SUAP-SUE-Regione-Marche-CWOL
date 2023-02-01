<?php

/**
 *
 * CLASSE DIZIONARIO CAMPI PER SOSTITUZIONE VARIABILI
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    09.02.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
class praTipiAllegato {

    /**
     * Classe Statica per la restituzione della'array con i tipi alle
     *
     * @param <type> $returnModel programma chiamante
     */
    public $Tipi = array();

    public function setTipi($Tipi) {
        $this->$Tipi = $Tipi;
    }

    public function getTipi() {
        if (!$this->Tipi) {
            $this->Tipi = array();
            $this->Tipi[0]['valore'] = "CI-COPIA CARTA DI IDENTITA'";
            $this->Tipi[1]['valore'] = 'CPS-COPIA PERMESSO DI SOGGIORNO';
            $this->Tipi[2]['valore'] = 'PL-PLANIMETRIA';
            $this->Tipi[3]['valore'] = 'PS-PROCURA SPECIALE';
            $this->Tipi[4]['valore'] = 'RT-RELAZIONE TECNICA/ASSEVERAZIONE';
            $this->Tipi[5]['valore'] = '01-CERTIFICATO DI AGIBILITA';
            $this->Tipi[6]['valore'] = "02-CERTIFICATO DI DESTINAZIONE D'USO";
            $this->Tipi[7]['valore'] = '03-TITOLO DI GODIMENTO DEI LOCALI';
            $this->Tipi[8]['valore'] = '05-CERTIFICAZIONE DI EMISSIONE FUMI IN ATMOSFERA';
            $this->Tipi[9]['valore'] = '06-ASSEVERAZIONE ACUSTICO AMBIENTALE';
            $this->Tipi[10]['valore'] = '07-ATTESTAZIONE PAGAMENTI';
            $this->Tipi[11]['valore'] = '12-ATTO NOTARILE';
            $this->Tipi[12]['valore'] = '13-TITOLO ABITATIVO';
            $this->Tipi[13]['valore'] = '14-CONTRATTO';
            $this->Tipi[14]['valore'] = '15-DICHIARAZIONE DI SUCCESSIONE';
            $this->Tipi[15]['valore'] = '99-';
        }
        return $this->Tipi;
    }

    public function CaricaTipi($arrayTipi, $returnModel) {
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Tipi Allegato',
            "width" => '450',
            "height" => '430',
            "sortname" => "valore",
            "sortorder" => "desc",
            "rowNum" => '200',
            "rowList" => '[]',
            "arrayTable" => $arrayTipi,
            "colNames" => array(
                "DESCRIZIONE"
            ),
            "colModel" => array(
                array("name" => 'valore', "width" => 400)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = "returnTipiAllegati";
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;
        $_POST['retid'] = $contenuto;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}

?>