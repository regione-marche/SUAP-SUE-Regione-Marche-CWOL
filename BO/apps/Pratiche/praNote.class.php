<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Michele Moscioni <andrea.bufarini@italsoft.eu>
 * @author     Marco Camilletti <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    17.02.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praNoteManager {

    const NOTE_CLASS_PROGES = "PROGES";
    const NOTE_CLASS_PROPAS = "PROPAS";
    const NOTE_CLASS_PASDOC = "PASDOC";
    const ELENCO_NOTE_STATO_NUOVO = "NUOVO";
    const ELENCO_NOTE_STATO_DA_CANCELLARE = "DA_CANCELLARE";
    const ELENCO_NOTE_STATO_DA_ANNULLARE = "DA_ANNULLARE";
    const ELENCO_NOTE_STATO_MODIFICATO = "MODIFICATO";

    private $note;
    private $classe;
    private $filter;
    private $chiave;
    private $noteObj;

    /**
     * 
     * @param type $praLib               Non piu usato, ininfluente!!
     * @param type $classe
     * @param type $chiave
     * @param type $filter
     * @return boolean|\praNoteManager
     */
    public static function getInstance($praLib, $classe, $chiave, $filter = '') {
        try {
            $obj = new praNoteManager();
        } catch (Exception $exc) {
            return false;
        }

        $obj->classe = $classe;
        $obj->chiave = $chiave;
        $obj->filter = $filter;
        $obj->noteObj = praNote::getInstance($obj, '', $classe);
        if ($obj->chiave) {
            if (!$obj->caricaNote()) {
                return false;
            }
        }
        return $obj;
    }

    public function getChiave() {
        return $this->chiave;
    }

    public function setChiave($chiave) {
        $this->chiave = $chiave;
    }

    public function getClasse() {
        return $this->classe;
    }

    public function setClasse($classe) {
        $this->classe = $classe;
    }

    public function getFilter() {
        return $this->filter;
    }

    public function setFilter($filter) {
        $this->filter = $filter;
    }

    public function getNote() {
        return $this->note;
    }

    public function getNota($rowid) {
        return $this->note[$rowid];
    }

    public function setNote($note) {
        $this->note = $note;
    }

    public function caricaNote() {
        $this->note = array();
        $this->note = $this->noteObj->getNoteFormDB($this->chiave, $this->filter);
        return true;
    }

    public function getSqlNote() {
        return $this->noteObj->getSqlNote($this->chiave, $this->filter);
    }

    /**
     * 
     * @param array $dati
     */
    public function aggiungiNota($dati) {
        $dati['STATO'] = self::ELENCO_NOTE_STATO_NUOVO;
        $this->note[] = $dati;
    }

    public function cancellaNota($rowid) {
        $this->note[$rowid]['STATO'] = self::ELENCO_NOTE_STATO_DA_CANCELLARE;
    }

    public function annullaNota($rowid) {
        $this->note[$rowid]['STATO'] = self::ELENCO_NOTE_STATO_DA_ANNULLARE;
    }

    public function aggiornaNota($rowid, $dati) {
        $dati['STATO'] = self::ELENCO_NOTE_STATO_MODIFICATO;
        $this->note[$rowid] = array_merge($this->note[$rowid], $dati);
    }

    public function salvaNote() {
        $praLib = new praLib();
        $refresh = false;
        foreach ($this->note as $note_rec) {
            switch ($note_rec['STATO']) {
                case self::ELENCO_NOTE_STATO_NUOVO:
                case self::ELENCO_NOTE_STATO_DA_ANNULLARE:
                case self::ELENCO_NOTE_STATO_DA_CANCELLARE:
                case self::ELENCO_NOTE_STATO_MODIFICATO:
                    if ($note_rec['CLASSE'] == '') {
                        $noteObj = $this->noteObj;
                        $chiave = $this->chiave;
                    } else {
                        $noteObj = praNote::getInstance($this, $praLib, $note_rec['CLASSE']);
                        $chiave = $note_rec['CHIAVE'];
                    }
                    break;
            }
            switch ($note_rec['STATO']) {
                case self::ELENCO_NOTE_STATO_NUOVO:
                    $dati = array(
                        'OGGETTO' => $note_rec['OGGETTO'],
                        'TESTO' => $note_rec['TESTO']
                    );
                    $noteObj->addNotaToDB($note_rec['CHIAVE'], $dati);
                    $refresh = true;
                    break;
                case self::ELENCO_NOTE_STATO_DA_ANNULLARE:
                    $dati = array(
                        'ROWID' => $note_rec['ROWID'],
                        'OGGETTO' => $note_rec['OGGETTO']
                    );
                    $noteObj->nullifyNotaToDB($dati);
                    $refresh = true;
                    break;
                case self::ELENCO_NOTE_STATO_DA_CANCELLARE:
                    if ($note_rec['ROWID']) {
                        $noteObj->deleteNotaToDB($note_rec);
                    }
                    $refresh = true;
                    break;
                case self::ELENCO_NOTE_STATO_MODIFICATO:
                    $dati = array(
                        'ROWID' => $note_rec['ROWID'],
                        'OGGETTO' => $note_rec['OGGETTO'],
                        'TESTO' => $note_rec['TESTO']
                    );

                    $noteObj->updateNotaToDB($dati);
                    $refresh = true;
                    break;
            }
            $noteObj = null;
        }
        if ($refresh) {
            $this->caricaNote();
        }
    }

}

class praNote {

    protected $classe;

    /**
     * 
     * @param type $model     Non piu usato ininfluente
     * @param type $praLib    Non piu ustao ininfluente
     * @param type $classe
     * @return boolean|\praNote_pasdoc
     */
    public static function getInstance($model, $praLib, $classe) {
        try {
            switch ($classe) {
                case praNoteManager::NOTE_CLASS_PROPAS:
                    $obj = new praNote_propas();
                    break;
                case praNoteManager::NOTE_CLASS_PROGES:
                    $obj = new praNote_proges();
                    break;
                case praNoteManager::NOTE_CLASS_PASDOC:
                    $obj = new praNote_pasdoc();
                    break;
                default:
                    return false;
            }
            $obj->classe = $classe;
        } catch (Exception $exc) {
            return false;
        }
        return $obj;
    }

    public function addNotaToDB($dati, $rowidClasse) {
        $praLib = new praLib();
        $model = new itaModel();
        $note_rec = array();
        $note_rec['OGGETTO'] = $dati['OGGETTO'];
        $note_rec['TESTO'] = $dati['TESTO'];
        $note_rec['DATAINS'] = date('Ymd');
        $note_rec['ORAINS'] = date('H:i:s');
        $note_rec['DATAMOD'] = '';
        $note_rec['ORAMOD'] = '';
        $note_rec['UTELOG'] = App::$utente->getKey('nomeUtente');
        $note_rec['MEDCOD'] = '';
        try {
            $insert_Info = 'Inserimento: Nota';
            if (!$model->insertRecord($praLib->getPRAMDB(), 'NOTE', $note_rec, $insert_Info)) {
                return false;
            }
            $rowidNote = $model->getLastInsertId();
            $noteclas_rec = array();
            $noteclas_rec['ROWIDNOTE'] = $rowidNote;
            $noteclas_rec['ROWIDPADRE'] = 0;
            $noteclas_rec['CLASSE'] = $this->classe;
            $noteclas_rec['ROWIDCLASSE'] = $rowidClasse;
            try {
                $insert_Info2 = 'Inserimento: Classificazione Nota';
                if (!$model->insertRecord($praLib->getPRAMDB(), 'NOTECLAS', $noteclas_rec, $insert_Info2)) {
                    // TODO: CANCELLA TRANSAZIONE PRECEDENTE
                    return false;
                }
            } catch (Exception $e) {
                return false;
            }
        } catch (Exception $e) {
            return false;
        }
    }

    public function deleteNotaToDB($dati) {
        $praLib = new praLib();
        $model = new itaModel();
        $delete_Info = "Cancellazione Nota:{$dati['OGGETTO']}";
        if (!$model->deleteRecord($praLib->getPRAMDB(), 'NOTE', $dati['ROWID'], $delete_Info)) {
            return false;
        }

        $sql = "SELECT * FROM NOTECLAS WHERE NOTECLAS.ROWIDNOTE={$dati['ROWID']}";
        $Noteclas_tab = ItaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
        foreach ($Noteclas_tab as $Noteclas_rec) {
            $delete_Info = "Cancellazione Classificazioni Nota:{$dati['OGGETTO']} Classe:{$Noteclas_rec['CLASSE']}";
            if (!$model->deleteRecord($praLib->getPRAMDB(), 'NOTECLAS', $Noteclas_rec['ROWID'], $delete_Info)) {
                return false;
            }
        }
    }

    public function updateNotaToDB($dati) {
        $praLib = new praLib();
        $model = new itaModel();
        $update_Info = "Aggiornamento Nota:{$dati['OGGETTO']}";
        $dati['DATAMOD'] = date('Ymd');
        $dati['ORAMOD'] = date('H:i:s');
        $dati['UTELOGMOD'] = App::$utente->getKey('nomeUtente');
        if (!$model->updateRecord($praLib->getPRAMDB(), 'NOTE', $dati, $update_Info)) {
            return false;
        }
    }

    public function nullifyNotaToDB($dati) {
        $praLib = new praLib();
        $model = new itaModel();
        $update_Info = "Annullamento Nota:{$dati['OGGETTO']}";
        $dati['DATAANN'] = date('Ymd');
        $dati['ORAANN'] = date('H:i:s');
        $dati['UTELOGANN'] = App::$utente->getKey('nomeUtente');
        if (!$model->updateRecord($praLib->getPRAMDB(), 'NOTE', $dati, $update_Info)) {
            return false;
        }
    }

}

class praNote_propas extends praNote {

    public function addNotaToDB($chiave, $dati) {
        $praLib = new praLib();
        $Propas_rec = $praLib->GetPropas($chiave, 'propak', false);
        if (!$Propas_rec) {
            return false;
        }
        $rowidClasse = $Propas_rec['ROWID'];
        return parent::addNotaToDB($dati, $rowidClasse);
    }

    public function getSqlNote($chiave, $filter = '', $childLevel = 0) {
        if ($filter) {
            $whereFilter = "AND ( $filter )";
        }
        return "
            SELECT
                NOTE.* 
            FROM
                NOTE NOTE
            LEFT OUTER JOIN
                NOTECLAS NOTECLAS ON NOTECLAS.CLASSE =  'PROPAS' AND NOTE.ROWID = NOTECLAS.ROWIDNOTE
            LEFT OUTER JOIN
                PROPAS PROPAS ON NOTECLAS.ROWIDCLASSE = PROPAS.ROWID
            WHERE
                PROPAS.PROPAK =  '$chiave' AND DATAANN='' $whereFilter    
         ";
    }

    public function getNoteFormDB($chiave, $filter = '', $childLevel = 0) {
        $praLib = new praLib();
        $sql = $this->getSqlNote($chiave, $filter);
        return itaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
    }

}

class praNote_proges extends praNote {

    public function addNotaToDB($chiave, $dati) {
        $praLib = new praLib();
        $Proges_rec = $praLib->GetProges($chiave);
        if (!$Proges_rec) {
            return false;
        }
        $rowidClasse = $Proges_rec['ROWID'];
        return parent::addNotaToDB($dati, $rowidClasse);
    }

    public function getSqlNote($chiave, $filter = '', $childLevel = 0) {
        if ($filter) {
            $whereFilter = "AND ( $filter )";
        }
        return "
            SELECT 
                *
            FROM (
            SELECT
                NOTE.*,
                0 AS SEQUENZA,
                NOTECLAS.CLASSE AS  CLASSE,
                NOTECLAS.ROWIDCLASSE AS  ROWIDCLASSE
            FROM
                NOTE NOTE
            LEFT OUTER JOIN
                NOTECLAS NOTECLAS ON NOTECLAS.CLASSE =  'PROGES' AND NOTE.ROWID = NOTECLAS.ROWIDNOTE
            LEFT OUTER JOIN
                PROGES PROGES ON NOTECLAS.ROWIDCLASSE = PROGES.ROWID
            WHERE
                PROGES.GESNUM =  '$chiave' AND DATAANN='' $whereFilter
            UNION   
            SELECT
                NOTE.*,
                PROPAS.PROSEQ AS SEQUENZA,
                NOTECLAS.CLASSE AS  CLASSE,
                NOTECLAS.ROWIDCLASSE AS  ROWIDCLASSE
            FROM
                NOTE NOTE
            LEFT OUTER JOIN
                NOTECLAS NOTECLAS ON NOTECLAS.CLASSE =  'PROPAS' AND NOTE.ROWID = NOTECLAS.ROWIDNOTE
            LEFT OUTER JOIN
                PROPAS PROPAS ON NOTECLAS.ROWIDCLASSE = PROPAS.ROWID
            WHERE
                PROPAS.PRONUM LIKE '$chiave' AND DATAANN='' $whereFilter 
            ) N ORDER BY N.SEQUENZA , DATAINS,ORAINS      

         ";
    }

    public function getNoteFormDB($chiave, $filter = '', $childLevel = 0) {
        $praLib = new praLib();
        $sql = $this->getSqlNote($chiave, $filter);
        return itaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
    }

}

class praNote_pasdoc extends praNote {

    public function getSqlNote($chiave, $filter = '', $childLevel = 0) {
        if ($filter) {
            $whereFilter = "AND ( $filter )";
        }
        return "
            SELECT
                NOTE.* 
            FROM
                NOTE NOTE
            LEFT OUTER JOIN
                NOTECLAS NOTECLAS ON NOTE.ROWID = NOTECLAS.ROWIDNOTE
            WHERE
                PROPAS.PROPAK =  '$chiave' AND DATAANN='' $whereFilter    
         ";
    }

    public function getNoteFormDB($chiave, $filter = '', $childLevel = 0) {
        $praLib = new praLib();
        $sql = $this->getSqlNote($chiave, $filter);
        return itaDB::DBSQLSelect($praLib->getPRAMDB(), $sql, true);
    }

    public function addNotaToDB($chiave, $dati) {
        $praLib = new praLib();
        $Propas_rec = $praLib->GetPropas($chiave, 'propak', false);
        if (!$Propas_rec) {
            return false;
        }
        $rowidClasse = $Propas_rec['ROWID'];
        return parent::addNotaToDB($dati, $rowidClasse);
    }

}

