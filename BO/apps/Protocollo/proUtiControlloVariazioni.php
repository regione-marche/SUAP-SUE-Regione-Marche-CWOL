<?php

/**
 * PHP Version 5
 *
 * @category   
 * @package    /apps/Protocollo
 * @author     Carlo Iesari <carlo.iesari@italsoft.eu>
 * @copyright  
 * @license 
 * @version    
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Accessi/accRic.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proRic.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';

function proUtiControlloVariazioni() {
    $proUtiControlloVariazioni = new proUtiControlloVariazioni();
    $proUtiControlloVariazioni->parseEvent();
    return;
}

class proUtiControlloVariazioni extends itaModel {

    public $accLib;
    public $proLib;
    public $nameForm = 'proUtiControlloVariazioni';

    function __construct() {
        parent::__construct();
        $this->accLib = new accLib();
        $this->proLib = new proLib();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                Out::select($this->nameForm . '_RICERCA[TIPO]', 1, '', 0, 'Tutti');
                Out::select($this->nameForm . '_RICERCA[TIPO]', 1, 'A', 0, 'Arrivo');
                Out::select($this->nameForm . '_RICERCA[TIPO]', 1, 'P', 0, 'Partenza');
                Out::select($this->nameForm . '_RICERCA[TIPO]', 1, 'C', 0, 'Doc. Formale');
                break;

            case 'onBlur':
                switch ($_POST['id']) {
                    case $this->nameForm . '_RICERCA[DANUMERO]':
                        if (intval($_POST[$this->nameForm . '_RICERCA']['DANUMERO'])) {
                            $num = str_pad($_POST[$this->nameForm . '_RICERCA']['DANUMERO'], 6, '0', STR_PAD_LEFT);
                        } else {
                            $num = '';
                        }

                        Out::valore($_POST['id'], $num);
                        break;

                    case $this->nameForm . '_RICERCA[ANUMERO]':
                        if (intval($_POST[$this->nameForm . '_RICERCA']['ANUMERO'])) {
                            $num = str_pad($_POST[$this->nameForm . '_RICERCA']['ANUMERO'], 6, '0', STR_PAD_LEFT);
                        } else {
                            $num = '';
                        }

                        Out::valore($_POST['id'], $num);
                        break;

                    case $this->nameForm . '_RICERCA[FIRMATARIO]':
                        $medcod = str_pad($_POST[$this->nameForm . '_RICERCA']['FIRMATARIO'], 6, '0', STR_PAD_LEFT);
                        $anamed_rec = $this->proLib->GetAnamed($medcod);

                        if ($anamed_rec) {
                            Out::valore($this->nameForm . '_RICERCA[FIRMATARIO]', $anamed_rec['MEDCOD']);
                            Out::valore($this->nameForm . '_RICERCA[FIRMATARIO_DECODE]', $anamed_rec['MEDNOM']);
                        } else {
                            Out::valore($this->nameForm . '_RICERCA[FIRMATARIO]', '');
                            Out::valore($this->nameForm . '_RICERCA[FIRMATARIO_DECODE]', '');
                        }
                        break;
                }

                break;

            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Pulisci':
                        Out::clearFields($this->nameForm);
                        Out::valore($this->nameForm . '_RICERCA[TIPO]', '');
                        break;

                    case $this->nameForm . '_Stampa':
                        $filters = $_POST[$this->nameForm . '_RICERCA'];

                        if (($filters['DANUMERO'] || $filters['ANUMERO']) && !$filters['ANNONUMERO']) {
                            Out::msgStop("Errore", "Inserire l'anno.");
                            Out::setFocus('', $this->nameForm . '_RICERCA[ANNONUMERO]');
                            break;
                        }

                        $SQL = $this->getSql($filters);
                        $anapro_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $SQL);

                        if (!count($anapro_tab)) {
                            Out::msgInfo("Attenzione", "La ricerca non ha prodotto risultati.");
                            break;
                        }

                        $anaent_rec = $this->proLib->GetAnaent('2');
                        include_once ITA_LIB_PATH . '/itaPHPCore/itaJasperReport.class.php';
                        $itaJR = new itaJasperReport();
                        $parameters = array("Sql" => $SQL, "Ente" => $anaent_rec['ENTDE1']);
                        $itaJR->runSQLReport($this->proLib->getPROTDB(), 'proUtiControlloVariazioni', 'PDF', $parameters);
                        break;

                    case $this->nameForm . '_RICERCA[UTENTEINSERIMENTO]_butt':
                        accRic::accRicUtenti($this->nameForm);
                        break;

                    case $this->nameForm . '_RICERCA[FIRMATARIO]_butt':
                        proRic::proRicAnamed($this->nameForm, " WHERE MEDUFF" . $this->proLib->getPROTDB()->isNotBlank());
                        break;

                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;

            case 'returnutenti':
                $utenti_rec = $this->accLib->GetUtenti($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_RICERCA[UTENTEINSERIMENTO]', $utenti_rec['UTELOG']);
                break;

            case 'returnanamed':
                $anamed_rec = $this->proLib->GetAnamed($_POST['retKey'], 'rowid');
                Out::valore($this->nameForm . '_RICERCA[FIRMATARIO]', $anamed_rec['MEDCOD']);
                Out::valore($this->nameForm . '_RICERCA[FIRMATARIO_DECODE]', $anamed_rec['MEDNOM']);
                break;
        }
    }

    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    public function getSql($filters) {
        $sql = "SELECT
                    ANAPRO.PROPAR,
                    " . $this->proLib->getPROTDB()->subString('ANAPRO.PRONUM', 1, 4) . " AS ANNO,
                    " . $this->proLib->getPROTDB()->subString('ANAPRO.PRONUM', 5, 10) . " AS NUMERO,
                    ANAPRO.PRODAR,
                    ANAPRO.PRORDA,
                    ANAPRO.PROUTE,
                    ANAPRO.PRONOM,
                    ANAPRO.PRORISERVA,
                    ANAPROSAVE.PROUTE AS PROUTEINS,
                    ANAOGG.OGGOGG,
                    ANADES.DESCOD,
                    ANADES.DESNOM
                FROM
                    ANAPRO
                LEFT OUTER JOIN ANADES ON
                    ANAPRO.PRONUM = ANADES.DESNUM AND ANAPRO.PROPAR = ANADES.DESPAR AND ANADES.DESTIPO = 'M'
                LEFT OUTER JOIN ANAOGG ON
                    ANAPRO.PRONUM = ANAOGG.OGGNUM AND ANAPRO.PROPAR = ANAOGG.OGGPAR
                LEFT OUTER JOIN ARCITE ON
                    ANAPRO.PRONUM = ARCITE.ITEPRO AND ANAPRO.PROPAR = ARCITE.ITEPAR
                LEFT OUTER JOIN UFFPRO ON
                    ANAPRO.PRONUM = UFFPRO.PRONUM AND ANAPRO.PROPAR = UFFPRO.UFFPAR
                LEFT OUTER JOIN
                    (
                        SELECT
                            PRONUM, PROPAR, PROUTE
                        FROM
                            ANAPROSAVE
                        WHERE
                            ROWID = (
                                SELECT MIN(ROWID)
                                FROM ANAPROSAVE A
                                WHERE A.PRONUM = ANAPROSAVE.PRONUM AND A.PROPAR = ANAPROSAVE.PROPAR
                            )
                    ) AS ANAPROSAVE
                ON
                    ANAPRO.PRONUM = ANAPROSAVE.PRONUM AND ANAPRO.PROPAR = ANAPROSAVE.PROPAR
                WHERE
                    ANAPRO.PRODAR <> ANAPRO.PRORDA";

        $where_profilo = proSoggetto::getSecureWhereFromIdUtente($this->proLib, '', array('VEDI_OGGRISERVATI' => 1));
//        $where_profilo = proSoggetto::getSecureWhereFromIdUtente($this->proLib, '');
        $sql .= " AND $where_profilo";


        if ($filters['DADATA']) {
            $sql .= " AND ANAPRO.PRODAR >= '{$filters['DADATA']}'";
        }

        if ($filters['ADATA']) {
            $sql .= " AND ANAPRO.PRODAR <= '{$filters['ADATA']}'";
        }

        if ($filters['ANNONUMERO']) {
            $daNumero = $filters['ANNONUMERO'] . '000000';
            $aNumero = $filters['ANNONUMERO'] . '999999';

            if ($filters['DANUMERO']) {
                $daNumero = $filters['ANNONUMERO'] . str_pad($filters['DANUMERO'], 6, '0', STR_PAD_LEFT);
            }

            if ($filters['ANUMERO']) {
                $aNumero = $filters['ANNONUMERO'] . str_pad($filters['ANUMERO'], 6, '0', STR_PAD_LEFT);
            }

            $sql .= " AND ANAPRO.PRONUM >= '$daNumero' AND ANAPRO.PRONUM <= '$aNumero'";
        }

        if ($filters['TIPO']) {
            $sql .= " AND ANAPRO.PROPAR = '{$filters['TIPO']}'";
        } else {
            //$sql .= " AND ANAPRO.PROPAR IN ( 'A', 'P', 'C' )";
            $sql .= " AND ( ANAPRO.PROPAR = 'A' OR ANAPRO.PROPAR = 'P' OR ANAPRO.PROPAR = 'C' )";
        }

        if ($filters['UTENTEINSERIMENTO']) {
            $sql .= " AND ANAPROSAVE.PROUTE = '{$filters['UTENTEINSERIMENTO']}'";
        }

        if ($filters['FIRMATARIO']) {
            $sql .= " AND ANADES.DESCOD = '{$filters['FIRMATARIO']}'";
        }

        $sql .= " GROUP BY ANAPRO.PRONUM, ANAPRO.PROPAR ORDER BY ANAPRO.PRONUM";

        return $sql;
    }

}
