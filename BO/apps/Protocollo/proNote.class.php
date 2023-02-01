<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2014 Italsoft snc
 * @license
 * @version    03.01.2014
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

class proNoteManager {

    const NOTE_CLASS_FASCICOLO = "FASCICOLO";
    const NOTE_CLASS_SOTTOFASCICOLO = "SOTTOFASCICOLO";
    const NOTE_CLASS_PROTOCOLLO = "ANAPRO";
    const NOTE_CLASS_ITER = "ARCITE";
    const NOTE_CLASS_DOCUMENTI = "ANADOC";
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
     * @param type $proLib      Non Piu Usato
     * @param type $classe
     * @param type $chiave
     * @param type $filter
     * @return boolean|\proNoteManager
     */
    public static function getInstance($proLib, $classe, $chiave, $filter = '') {
        try {
            $obj = new proNoteManager();
        } catch (Exception $exc) {
            return false;
        }
        $obj->classe = $classe;
        $obj->chiave = $chiave;
        $obj->filter = $filter;
        $obj->noteObj = proNote::getInstance($obj, '', $classe);
        if ($obj->chiave) {
            if (!$obj->caricaNote()) {
                return false;
            }
        }
        return $obj;
    }

    public function __sleep() {
        return array(
            "note",
            "classe",
            "filter",
            "chiave",
            "noteObj",
        );
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
                        $noteObj = proNote::getInstance($this, $proLib, $note_rec['CLASSE']);
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

class proNote {

    protected $classe;

    /**
     * 
     * @param type $model    Non piu usato
     * @param type $proLib   Non piu usato
     * @param type $classe
     * @return boolean|\proNote_arcite
     */
    public static function getInstance($model, $proLib, $classe) {
        try {
            switch ($classe) {
                case proNoteManager::NOTE_CLASS_FASCICOLO:
                    $obj = new proNote_fascicolo();
                    break;
                case proNoteManager::NOTE_CLASS_SOTTOFASCICOLO:
                    $obj = new proNote_sottofascicolo();
                    break;
                case proNoteManager::NOTE_CLASS_PROTOCOLLO:
                    $obj = new proNote_anapro();
                    break;
                case proNoteManager::NOTE_CLASS_ITER:
                    $obj = new proNote_arcite();
                    break;
                default:
                    return false;
                    break;
            }
            $obj->classe = $classe;
        } catch (Exception $exc) {
            return false;
        }
        return $obj;
    }

    public function addNotaToDB($dati, $rowidClasse) {
        $proLib = new proLib();
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
            if (!$model->insertRecord($proLib->getPROTDB(), 'NOTE', $note_rec, $insert_Info)) {
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
                if (!$model->insertRecord($proLib->getPROTDB(), 'NOTECLAS', $noteclas_rec, $insert_Info2)) {
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
        $proLib = new proLib();
        $model = new itaModel();
        $delete_Info = "Cancellazione Nota:{$dati['OGGETTO']}";
        if (!$model->deleteRecord($proLib->getPROTDB(), 'NOTE', $dati['ROWID'], $delete_Info)) {
            return false;
        }

        $sql = "SELECT * FROM NOTECLAS WHERE NOTECLAS.ROWIDNOTE={$dati['ROWID']}";
        $Noteclas_tab = ItaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
        foreach ($Noteclas_tab as $Noteclas_rec) {
            $delete_Info = "Cancellazione Classificazioni Nota:{$dati['OGGETTO']} Classe:{$Noteclas_rec['CLASSE']}";
            if (!$model->deleteRecord($proLib->getPROTDB(), 'NOTECLAS', $Noteclas_rec['ROWID'], $delete_Info)) {
                return false;
            }
        }
    }

    public function updateNotaToDB($dati) {
        $proLib = new proLib();
        $model = new itaModel();
        $update_Info = "Aggiornamento Nota:{$dati['OGGETTO']}";
        $dati['DATAMOD'] = date('Ymd');
        $dati['ORAMOD'] = date('H:i:s');
        $dati['UTELOGMOD'] = App::$utente->getKey('nomeUtente');
        if (!$model->updateRecord($proLib->getPROTDB(), 'NOTE', $dati, $update_Info)) {
            return false;
        }
    }

    public function nullifyNotaToDB($dati) {
        $proLib = new proLib();
        $model = new itaModel();
        $update_Info = "Annullamento Nota:{$dati['OGGETTO']}";
        $dati['DATAANN'] = date('Ymd');
        $dati['ORAANN'] = date('H:i:s');
        $dati['UTELOGANN'] = App::$utente->getKey('nomeUtente');
        if (!$model->updateRecord($proLib->getPROTDB(), 'NOTE', $dati, $update_Info)) {
            return false;
        }
    }

}

class proNote_fascicolo extends proNote {

    public function addNotaToDB($chiave, $dati) {
        return false;
    }

    public function getSqlNote($chiave, $filter = '', $childLevel = 0) {
        if ($filter) {
            $whereFilter = "AND ( $filter )";
        }
        return "
            SELECT
                * 
            FROM
                (
                    SELECT 
                        NOTE.ROWID AS ROWID,
                        NOTE.OGGETTO,
                        NOTE.TESTO,
                        NOTE.DATAINS,
                        NOTE.ORAINS,
                        NOTE.DATAMOD,
                        NOTE.ORAMOD,
                        NOTE.DATAANN,
                        NOTE.ORAANN,
                        NOTE.UTELOG,
                        NOTE.UTELOGMOD,
                        NOTE.UTELOGANN,
                        NOTE.MEDCOD,
                        NOTECLAS.ROWIDNOTE,
                        NOTECLAS.ROWIDPADRE,
                        NOTECLAS.CLASSE,
                        NOTECLAS.ROWIDCLASSE,
                        ANAPRO.PRONUM,
                        ANAPRO.PROPAR
                    FROM
                        NOTE NOTE
                    LEFT OUTER JOIN
                        NOTECLAS NOTECLAS ON NOTECLAS.CLASSE =  'ANAPRO' AND NOTE.ROWID = NOTECLAS.ROWIDNOTE
                    LEFT OUTER JOIN
                        ANAPRO ANAPRO ON NOTECLAS.ROWIDCLASSE = ANAPRO.ROWID
                    WHERE
                        ANAPRO.PROFASKEY =  '{$chiave['PROFASKEY']}'
                UNION 
                    SELECT
                        NOTE.ROWID AS ROWID,
                        NOTE.OGGETTO,
                        NOTE.TESTO,
                        NOTE.DATAINS,
                        NOTE.ORAINS,
                        NOTE.DATAMOD,
                        NOTE.ORAMOD,
                        NOTE.DATAANN,
                        NOTE.ORAANN,
                        NOTE.UTELOG,
                        NOTE.UTELOGMOD,
                        NOTE.UTELOGANN,
                        NOTE.MEDCOD,
                        NOTECLAS.ROWIDNOTE,
                        NOTECLAS.ROWIDPADRE,
                        NOTECLAS.CLASSE,
                        NOTECLAS.ROWIDCLASSE,
                        ANAPRO.PRONUM,
                        ANAPRO.PROPAR

                    FROM
                        NOTE NOTE
                    LEFT OUTER JOIN
                        NOTECLAS NOTECLAS ON NOTECLAS.CLASSE =  'ARCITE' AND NOTE.ROWID = NOTECLAS.ROWIDNOTE
                    LEFT OUTER JOIN
                        ARCITE ARCITE ON NOTECLAS.ROWIDCLASSE = ARCITE.ROWID
                    LEFT OUTER JOIN
                        ANAPRO ANAPRO ON ANAPRO.PRONUM = ARCITE.ITEPRO AND ANAPRO.PROPAR = ARCITE.ITEPAR    
                    WHERE
                        ANAPRO.PROFASKEY =  '{$chiave['PROFASKEY']}'
                ) NOTE
                WHERE NOTE.DATAANN='' $whereFilter
                ORDER BY NOTE.DATAINS ASC, NOTE.ORAINS ASC
        ";
    }

    public function getNoteFormDB($chiave, $filter = '', $childLevel = 0) {
        $proLib = new proLib();
        $sql = $this->getSqlNote($chiave, $filter);
        return itaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
    }

}

class proNote_anapro extends proNote {

    public function addNotaToDB($chiave, $dati) {
        $proLib = new proLib();
        $Anapro_rec = $proLib->GetAnapro($chiave['PRONUM'], $tipo = 'codice', $chiave['PROPAR']);
        if (!$Anapro_rec) {
            return false;
        }
        $rowidClasse = $Anapro_rec['ROWID'];
        return parent::addNotaToDB($dati, $rowidClasse);
    }

    public function getSqlNote($chiave, $filter = '', $childLevel = 0) {
        if ($filter) {
            $whereFilter = "AND ( $filter )";
        }
        return "
            SELECT
                * 
            FROM
                (
                    SELECT 
                        NOTE.ROWID AS ROWID,
                        NOTE.OGGETTO,
                        NOTE.TESTO,
                        NOTE.DATAINS,
                        NOTE.ORAINS,
                        NOTE.DATAMOD,
                        NOTE.ORAMOD,
                        NOTE.DATAANN,
                        NOTE.ORAANN,
                        NOTE.UTELOG,
                        NOTE.UTELOGMOD,
                        NOTE.UTELOGANN,
                        NOTE.MEDCOD,
                        NOTECLAS.ROWIDNOTE,
                        NOTECLAS.ROWIDPADRE,
                        NOTECLAS.CLASSE,
                        NOTECLAS.ROWIDCLASSE
                    FROM
                        NOTE NOTE
                    LEFT OUTER JOIN
                        NOTECLAS NOTECLAS ON NOTECLAS.CLASSE =  'ANAPRO' AND NOTE.ROWID = NOTECLAS.ROWIDNOTE
                    LEFT OUTER JOIN
                        ANAPRO ANAPRO ON NOTECLAS.ROWIDCLASSE = ANAPRO.ROWID
                    WHERE
                        ANAPRO.PRONUM =  '{$chiave['PRONUM']}' AND ANAPRO.PROPAR =  '{$chiave['PROPAR']}'
                UNION 
                    SELECT
                        NOTE.ROWID AS ROWID,
                        NOTE.OGGETTO,
                        NOTE.TESTO,
                        NOTE.DATAINS,
                        NOTE.ORAINS,
                        NOTE.DATAMOD,
                        NOTE.ORAMOD,
                        NOTE.DATAANN,
                        NOTE.ORAANN,
                        NOTE.UTELOG,
                        NOTE.UTELOGMOD,
                        NOTE.UTELOGANN,
                        NOTE.MEDCOD,
                        NOTECLAS.ROWIDNOTE,
                        NOTECLAS.ROWIDPADRE,
                        NOTECLAS.CLASSE,
                        NOTECLAS.ROWIDCLASSE
                    FROM
                        NOTE NOTE
                    LEFT OUTER JOIN
                        NOTECLAS NOTECLAS ON NOTECLAS.CLASSE =  'ARCITE' AND NOTE.ROWID = NOTECLAS.ROWIDNOTE
                    LEFT OUTER JOIN
                        ARCITE ARCITE ON NOTECLAS.ROWIDCLASSE = ARCITE.ROWID
                    WHERE
                        ARCITE.ITEPRO =  '{$chiave['PRONUM']}' AND ARCITE.ITEPAR =  '{$chiave['PROPAR']}'
                ) NOTE
                WHERE NOTE.DATAANN='' $whereFilter
                ORDER BY NOTE.DATAINS DESC, NOTE.ORAINS DESC
        ";
    }

    public function getNoteFormDB($chiave, $filter = '', $childLevel = 0) {
        $proLib = new proLib();
        $sql = $this->getSqlNote($chiave, $filter);
        App::log($sql);
        return itaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
    }

}

class proNote_arcite extends proNote {

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
                NOTECLAS NOTECLAS ON NOTECLAS.CLASSE =  'ARCITE' AND NOTE.ROWID = NOTECLAS.ROWIDNOTE
            LEFT OUTER JOIN
                ARCITE ARCITE ON NOTECLAS.ROWIDCLASSE = ARCITE.ROWID
            WHERE
                ARCITE.ITEKEY =  '$chiave' AND DATAANN='' $whereFilter    
         ";
    }

    public function getNoteFormDB($chiave, $filter = '', $childLevel = 0) {
        $proLib = new proLib();
        $sql = $this->getSqlNote($chiave, $filter);
        return itaDB::DBSQLSelect($proLib->getPROTDB(), $sql, true);
    }

    public function addNotaToDB($chiave, $dati) {
        $proLib = new proLib();
        $Arcite_rec = $proLib->GetArcite($chiave, 'itekey', false);
        if (!$Arcite_rec) {
            return false;
        }
        $rowidClasse = $Arcite_rec['ROWID'];
        return parent::addNotaToDB($dati, $rowidClasse);
    }

}
