<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Protocollo Lib Variazioni
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    15.11.2017
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

class proLibVariazioni {

    private $errCode;
    private $errMessage;
    private $proLib;

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    function __construct() {
        $this->proLib = new proLib();
    }

    public function getLastAnaproSave($pronum, $propar, $savedata = '', $saveora = null) {
        if (!$pronum || !$propar) {
            $this->setErrCode(-1);
            $this->setErrMessage('Parametri di protocollo mancanti.');
            return false;
        }
        if ($savedata == '') {
            $savedata = date('Ymd');
        }
        /*
         * Prendo il primo record su anaprosave,
         * con data/ora successive al versamento.
         * E' il protocollo come era prima della variazione.
         */
        $sql = "SELECT * FROM ANAPROSAVE "
                . " WHERE PRONUM=$pronum AND PROPAR='$propar' ";
        if ($saveora) {
            $sql.=" AND SAVEORA >= '$saveora' ";
        }
        $sql.=" AND SAVEDATA >= '$savedata' ORDER BY SAVEDATA ASC, SAVEORA ASC ";
        /* Altrimenti utilizzo la data di modifica */
//                . " AND PRORDA <= '$savedata' AND PROROR<='$saveora' ORDER BY PRORDA DESC, PROROR DESC ";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    public function getAnapro($pronum, $propar, $savedata = '', $saveora = '') {
        $sql = "SELECT * FROM ANAPROSAVE "
                . " WHERE PRONUM=$pronum AND PROPAR='$propar' "
                . " AND SAVEDATA='$savedata' AND SAVEORA='$saveora' ";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    public function getVariazioni($AnaproSave_rec) {

        $ElencoVariazioni = array();
        /*
         * Carico Anaogg
         */
        $ElencoVariazioni['ANAPRO'] = $AnaproSave_rec;

        $ElencoVariazioni['ANADES'] = $this->GetAnades($AnaproSave_rec['PRONUM'], 'codice', true, $AnaproSave_rec['PROPAR'], '', '', $AnaproSave_rec['SAVEDATA'], $AnaproSave_rec['SAVEORA']);

        $ElencoVariazioni['ANANOM'] = $this->GetAnanom($AnaproSave_rec['PRONUM'], false, $AnaproSave_rec['PROPAR'], $AnaproSave_rec['SAVEDATA'], $AnaproSave_rec['SAVEORA']);

        $ElencoVariazioni['ANAOGG'] = $this->getAnaogg($AnaproSave_rec['PRONUM'], $AnaproSave_rec['PROPAR'], $AnaproSave_rec['SAVEDATA'], $AnaproSave_rec['SAVEORA']);

        $ElencoVariazioni['PROMITAGG'] = $this->getPromitagg($AnaproSave_rec['PRONUM'], $AnaproSave_rec['PROPAR'], $AnaproSave_rec['SAVEDATA'], $AnaproSave_rec['SAVEORA']);

        $ElencoVariazioni['UFFPRO'] = $this->GetUffpro($AnaproSave_rec['PRONUM'], $AnaproSave_rec['PROPAR'], $AnaproSave_rec['SAVEDATA'], $AnaproSave_rec['SAVEORA']);

        $ElencoVariazioni['ANADOC'] = $this->GetAnadoc($AnaproSave_rec['PRONUM'], 'codice', true, $AnaproSave_rec['PROPAR'], '', $AnaproSave_rec['SAVEDATA'], $AnaproSave_rec['SAVEORA']);
        // ANADOC!
        $ElencoVariazioni['FASCICOLI'] = $this->EstraiFascicoliProtocollo($AnaproSave_rec['PRONUM'], $AnaproSave_rec['PROPAR'], '', $AnaproSave_rec['SAVEDATA'], $AnaproSave_rec['SAVEORA']);

        return $ElencoVariazioni;
    }

    /*
     * ANAOGG: una versione sola
     */

    public function getAnaogg($pronum, $propar, $savedata = '', $saveora = '') {
        $sql = "SELECT * FROM ANAOGGSAVE "
                . " WHERE OGGNUM=$pronum AND OGGPAR='$propar' "
                . " AND SAVEDATA='$savedata' AND SAVEORA='$saveora' ";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, false);
    }

    /*
     * ANADES
     */

    public function GetAnades($codice, $tipo = 'codice', $multi = false, $tipoProt = '', $destipo = '', $where = '', $savedata = '', $saveora = '') {
        switch ($tipo) {
            case 'codice':
                $sql = "SELECT * FROM ANADESSAVE WHERE DESNUM=$codice";
                if ($tipoProt != '') {
                    $sql.=" AND DESPAR='$tipoProt'";
                }
                if ($destipo == 'D') {
                    $sql.=" AND (DESTIPO='' OR DESTIPO='D')";
                } else if ($destipo == 'M') {
                    $sql.=" AND DESTIPO='M'";
                } else if ($destipo == 'T') {
                    $sql.=" AND DESTIPO='T'";
                }
                $sql.=" AND SAVEDATA='$savedata' AND SAVEORA='$saveora' ";
                $sql.=" $where ORDER BY DESNOM";
                break;

            default:
                $sql = "SELECT * FROM ANADESSAVE WHERE ROWID='$codice'";
                break;
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    /*
     * ANANOM
     */

    public function GetAnanom($codice, $multi = false, $tipoProt = '', $savedata = '', $saveora = '') {
        $sql = "SELECT * FROM ANANOMSAVE WHERE NOMNUM=$codice";
        if ($tipoProt != '') {
            $sql.=" AND NOMPAR='$tipoProt'";
        }
        $sql.=" AND SAVEDATA='$savedata' AND SAVEORA='$saveora' ";
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    /*
     * PROMITAGG
     */

    public function getPromitagg($codice, $tipo = 'codice', $multi = true, $tipoProt = "", $savedata = '', $saveora = '') {
        if ($tipo == 'codice') {
            $whereTipo = " AND SAVEDATA='$savedata' AND SAVEORA='$saveora' ";
            if ($tipoProt) {
                $whereTipo.= " AND PROPAR='$tipoProt'";
            }
            $sql = "SELECT * FROM PROMITAGGSAVE WHERE PRONUM=$codice $whereTipo ORDER BY PRONOM";
        } else {
            $sql = "SELECT * FROM PROMITAGGSAVE WHERE ROWID=$codice";
            $multi = false;
        }
        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    /*
     * UFFPRO
     */

    public function GetUffpro($codice, $tipo = 'codice', $multi = true, $uffpar = '', $savedata = '', $saveora = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM UFFPROSAVE WHERE PRONUM=$codice";
            if ($uffpar) {
                $sql.=" AND UFFPAR='$uffpar'";
            }
            $sql.=" AND SAVEDATA='$savedata' AND SAVEORA='$saveora' ";
            $sql.=" ORDER BY UFFCOD";
        } else {
            $sql = "SELECT * FROM UFFPROSAVE WHERE ROWID=$codice";
            $multi = false;
        }
        $uffpro_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
        return $uffpro_tab;
    }

    /*
     * ANASPE
     */

    public function GetAnaspe($codice, $tipo = 'codice', $multi = false, $tipoProt = '', $savedata = '', $saveora = '') {
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANASPESAVE WHERE PRONUM=$codice";
            if ($tipoProt != '') {
                $sql.=" AND PROPAR='$tipoProt'";
            }
            $sql.=" AND SAVEDATA='$savedata' AND SAVEORA='$saveora' ";
        } else {
            $sql = "SELECT * FROM ANASPESAVE WHERE ROWID='$codice'";
        }
        $anaspe_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
        return $anaspe_tab;
    }

    public function GetAnadoc($codice, $tipo = 'codice', $multi = false, $tipoProt = '', $where = '', $savedata = '', $saveora = '') {
        if ($savedata || $saveora) {
            $where = " AND SAVEDATA='$savedata' AND SAVEORA='$saveora' " . $where;
        }
        if ($tipo == 'codice') {
            $sql = "SELECT * FROM ANADOCSAVE WHERE DOCKEY LIKE '" . $codice . $tipoProt . "%' $where";
        } else if ($tipo == 'protocollo') {
            $sql = "SELECT * FROM ANADOCSAVE WHERE DOCNUM = '$codice' AND DOCPAR = '$tipoProt' $where";
        } else if ($tipo == 'docsha2') {
            $sql = "SELECT * FROM ANADOCSAVE WHERE DOCSHA2 = '$codice'";
        } else if ($tipo == 'docrowidbase') {
            $sql = "SELECT * FROM ANADOCSAVE WHERE DOCROWIDBASE = '$codice' AND DOCROWIDBASE <> '' ";
        } else {
            $sql = "SELECT * FROM ANADOCSAVE WHERE ROWID='$codice'";
        }

        return ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, $multi);
    }

    /**
     * Estrae i Fascicoli a cui appartiene un protocollo alla data indicata
     * 
     * @param type $Pronum
     * @param type $Propar
     * @param type $savedata
     * @param type $saveora
     * @return type
     */
    public function EstraiFascicoliProtocollo($Pronum, $Propar, $savedata = '', $saveora = '') {
        /*
         * Estrazione Fascicoli
         */
        $sql = "SELECT ORGCONN.*,
                     ANAORG.ROWID AS ROWID_ANAORG,
                     ANAORG.ORGCOD,
                     ANAORG.ORGANN,
                     ANAORG.ORGDES,
                     ANAORG.ORGCCF,
                     ANAORG.ORGDES
                FROM ORGCONN 
            LEFT OUTER JOIN ANAORG ON ORGCONN.ORGKEY = ANAORG.ORGKEY
            WHERE PRONUM = $Pronum AND PROPAR = '$Propar' AND CONNDATAINS <= '$savedata' AND CONNORAINS <= '$saveora'
                    AND (
                        (CONNDATAANN = '' )
                      OR
                      (CONNDATAANN <> '' AND CONNDATAANN > '$savedata' ) 
                      ) ";
        $Fascicoli_tab = ItaDB::DBSQLSelect($this->proLib->getPROTDB(), $sql, true);
        foreach ($Fascicoli_tab as $key => $Fascicoli_rec) {
            $AnaoggFas_rec = $this->getAnaogg($Fascicoli_rec['PRONUM'], $Fascicoli_rec['PROPAR'], $savedata, $saveora);
            $Fascicoli_tab[$key]['OGGOGG_FASCICOLO'] = $AnaoggFas_rec['OGGOGG'];
            if ($Fascicoli_rec['PROPARPARENT'] == 'N') {
                $AnaproSottoFas_rec = $this->getAnapro($Fascicoli_rec['PRONUMPARENT'], $Fascicoli_rec['PRONUMPARENT'], $savedata, $saveora);
                $AnaoggSottoFas_rec = $this->getAnaogg($Fascicoli_rec['PRONUMPARENT'], $Fascicoli_rec['PRONUMPARENT'], $savedata, $saveora);
                $Fascicoli_tab[$key]['PROSUBKEY_SOTTOFAS'] = $AnaproSottoFas_rec['PROSUBKEY'];
                $Fascicoli_tab[$key]['OGGOGG_SOTTOFAS'] = $AnaoggSottoFas_rec['OGGOGG'];
                $Fascicoli_tab[$key]['CODICE_SOTTOFAS'] = $AnaproSottoFas_rec['PROSUBKEY'];
                $Fascicoli_tab[$key]['OGGETTO_SOTTOFAS'] = $AnaoggSottoFas_rec['OGGOGG'];
            }
        }
        return $Fascicoli_tab;
    }

    public function caricaAltriDestinatari($codice, $propar, $elabora = true, $savedata = '', $saveora = '') {
        $proAltriDestinatari = $this->GetAnades($codice, 'codice', true, $propar, 'D', " AND DESCUF=''", $savedata, $saveora);
        return $proAltriDestinatari;
    }

}
