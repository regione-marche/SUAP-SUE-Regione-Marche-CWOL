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
include_once ITA_BASE_PATH . '/apps/Pratiche/praSubPasso.php';

function praSubPassoCaratteristiche() {
    $praSubPassoCaratteristiche = new praSubPassoCaratteristiche();
    $praSubPassoCaratteristiche->parseEvent();
    return;
}

class praSubPassoCaratteristiche extends praSubPasso {

    public $nameForm = 'praSubPassoCaratteristiche';
    public $passAlle = array();
    public $gridAllegati = array();

    function __construct() {
        parent::__construct();
    }

    public function postInstance() {
        parent::postInstance();
   }

    function __destruct() {
        parent::__destruct();
    }

    public function close() {
        parent::close();
    }

    public function returnToParent($close = true) {
        parent::returnToParent($close);
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
        }
    }

    public function abilitaAllegatiAllaPubblicazione($flag = 1) {
        if ($flag == 1) {
            foreach ($this->passAlle as $key => $alle) {
                if ($alle['isLeaf'] == "true") {
                    $this->passAlle[$key]['PUBBLICA'] = "<span class=\"ita-icon ita-icon-no-publish-24x24\">Allegato non pubblicato</span>";
                }
            }
        } else {
            foreach ($this->passAlle as $key => $alle) {
                $this->passAlle[$key]['PUBBLICA'] = "";
            }
        }
        $this->CaricaGriglia($this->gridAllegati, $this->passAlle);
        Out::msgInfo('........albilitaAllegatiAllaPubblicazione........', "{$this->gridAllegati} -- $flag -- {$this->passAlle}");
    }

    public function abilitaAllegatiAllaConferenzaServizi($flag = 1) {
        if ($flag == 1) {
            foreach ($this->passAlle as $key => $alle) {
                $ext = pathinfo($alle['FILENAME'], PATHINFO_EXTENSION);
                if ($alle['isLeaf'] == 'true' && strtolower($ext) != 'p7m') {
                    $this->passAlle[$key]['CDS'] = $this->praLib->getIconCds(0, $_POST[$this->nameForm . '_PROPAS']['PROFLCDS']);
                }
            }
        } else {
            foreach ($this->passAlle as $key => $alle) {
                if ($alle['isLeaf'] == 'true' && strtolower($ext) != 'p7m') {
                    $this->passAlle[$key]['CDS'] = "";
                    if ($this->passAlle[$key]['PASFLCDS'] == 1) {
                        $this->passAlle[$key]['PASFLCDS'] = 0;
                    }
                }
            }
        }

        $this->CaricaGriglia($this->gridAllegati, $this->passAlle);

        //Out::msgInfo('abilitaAllegatiAllaConferenzaServizi', "{$this->gridAllegati} -- $flag -- {$this->passAlle}");
    }

    public function CaricaGriglia($griglia, $appoggio, $tipo = '1', $pageRows = '20') {
        $arrayGrid = array();
        foreach ($appoggio as $arrayRow) {
            unset($arrayRow['PASMETA']);
            $arrayGrid[] = $arrayRow;
        }
        $ita_grid01 = new TableView(
                $griglia, array('arrayTable' => $arrayGrid,
            'rowIndex' => 'idx')
        );
        if ($tipo == '1') {
            $ita_grid01->setPageNum(1);
            $ita_grid01->setPageRows($pageRows);
        } else {
            $ita_grid01->setPageNum($_POST['page']);
            $ita_grid01->setPageRows($_POST['rows']);
        }
        TableView::enableEvents($griglia);
        TableView::clearGrid($griglia);
        $ita_grid01->getDataPage('json');
        return;
    }
}
