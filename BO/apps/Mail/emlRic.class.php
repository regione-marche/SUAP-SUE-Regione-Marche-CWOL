<?php

/**
 *
 * Ricerche su Mail
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Marco Camilletti <marco.camilletti@italsoft.eu>
 * @copyright  1987-2012 Italsoft snc
 * @license
 * @version    28.09.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
class emlRic {

    static function emlRicToView($emlLib, $returnModel, $elencomail, $tipo, $returnEvent = 'returnFromEmlRicToView') {
        $datiGriglia = array();
        foreach ($elencomail as $id_mail) {
            $mailArchivio = $emlLib->getMailArchivio($id_mail, $tipo);
            if (!$mailArchivio) {
                continue;
            }
            $datiGriglia[] = array(
                'ID_MAIL' => $id_mail,
                'FROMADDR_MAIL' => $mailArchivio['FROMADDR'],
                'SUBJECT_MAIL' => $mailArchivio['SUBJECT'],
                'MSGDATE_MAIL' => $mailArchivio['MSGDATE']
            );
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Mail Visualizzabili',
            "width" => '850',
            "height" => '430',
            "rowNum" => '200',
            "rowList" => '[]',
            "arrayTable" => $datiGriglia,
            "colNames" => array(
                "Codice Mail",
                "Da",
                "Oggetto",
                "Data e Ora"
            ),
            "colModel" => array(
                array("name" => 'ID_MAIL', "width" => 600, "hidden" => "true"),
                array("name" => 'FROMADDR_MAIL', "width" => 200),
                array("name" => 'SUBJECT_MAIL', "width" => 500),
                array("name" => 'MSGDATE_MAIL', "width" => 120)
            )
        );
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = $returnEvent;
        $_POST['returnClosePortlet'] = true;
        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    /**
     * Ricerca su Archivio Account Email
     * @param type $returnModel
     * @param type $where
     * @param type $returnEvent
     */
    static function emlRicAccount($returnModel, $where = '', $returnEvent = '', $returnId = '') {
        $sql = "SELECT * FROM MAIL_ACCOUNT";
        if ($where != '') {
            $sql = $sql . ' ' . $where;
        }
        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Account Email',
            "width" => '550',
            "height" => '470',
            "sortname" => 'MAILADDR',
            "rowNum" => '20',
            "filterToolbar" => 'true',
            "rowList" => '[]',
            "colNames" => array(
                "Account",
                "Indirizzo Email"
            ),
            "colModel" => array(
                array("name" => 'NAME', "width" => 200),
                array("name" => 'MAILADDR', "width" => 300)
            ),
            "dataSource" => array(
                'sqlDB' => 'ITALWEB',
                'sqlQuery' => $sql
            )
        );
        $filterName = array('NAME', 'MAILADDR');
        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['filterName'] = $filterName;
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnAccount' . $returnEvent;
        $_POST['retid'] = $returnId;
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

    /**
     * 
     * @param type $returnModel
     * @param type $returnEvent
     * @param emlLib $emlLib
     * @param type $id
     * @param type $tipo
     */
    static function emlRicCollegate($returnModel, $returnEvent, $emlLib, $id, $tipo) {
        $matriceMail = $matriceRec = array();
        $mailarchivio_rec = $emlLib->getMailArchivio($id, $tipo);

        $mailarchivio_padre = ItaDB::DBSQLSelect($emlLib->getITALWEB(), sprintf("SELECT ROWID, IDMAIL, PECTIPO, SUBJECT, FROMADDR, MSGDATE FROM MAIL_ARCHIVIO WHERE IDMAIL = '%s'", $mailarchivio_rec['IDMAILPADRE']), false);
        if ($mailarchivio_padre) {
            $matriceRec = $mailarchivio_padre;
            $matriceRec['level'] = 0;
            $matriceRec['parent'] = null;
            $matriceRec['isLeaf'] = 'false';
            $matriceRec['expanded'] = 'true';
            $matriceRec['loaded'] = 'true';
            $matriceMail[] = $matriceRec;
            $sqlChilds = sprintf("SELECT ROWID, IDMAIL, PECTIPO, SUBJECT, FROMADDR, MSGDATE FROM MAIL_ARCHIVIO WHERE IDMAILPADRE = '%s'", $mailarchivio_padre['IDMAIL']);
        } else {
            $matriceRec = $mailarchivio_rec;
            $matriceRec['SUBJECT'] = '<span style="color: red;">' . $matriceRec['SUBJECT'] . '</span>';
            $matriceRec['level'] = 0;
            $matriceRec['parent'] = null;
            $matriceRec['isLeaf'] = 'false';
            $matriceRec['expanded'] = 'true';
            $matriceRec['loaded'] = 'true';
            $matriceMail[] = $matriceRec;
            $sqlChilds = sprintf("SELECT ROWID, IDMAIL, PECTIPO, SUBJECT, FROMADDR, MSGDATE FROM MAIL_ARCHIVIO WHERE IDMAILPADRE = '%s'", $mailarchivio_rec['IDMAIL']);
        }

        $mailarchivio_childs = ItaDB::DBSQLSelect($emlLib->getITALWEB(), $sqlChilds);

        if (count($mailarchivio_childs)) {
            foreach ($mailarchivio_childs as $mailarchivio_child_rec) {
                $matriceRec = $mailarchivio_child_rec;
                $matriceRec['level'] = 1;
                $matriceRec['parent'] = $matriceMail[0]['ROWID'];
                $matriceRec['isLeaf'] = 'true';
                $matriceRec['expanded'] = 'true';
                $matriceRec['loaded'] = 'true';

                if ($mailarchivio_child_rec['ROWID'] === $mailarchivio_rec['ROWID']) {
                    $matriceRec['SUBJECT'] = '<span style="color: red;">' . $matriceRec['SUBJECT'] . '</span>';
                }

                $matriceMail[] = $matriceRec;
            }
        }

        $model = 'utiRicDiag';
        $gridOptions = array(
            "Caption" => 'Elenco mail',
            "width" => '900',
            "height" => '400',
            "sortname" => "SUBJECT",
            "sortorder" => "asc",
            "rowNum" => '200',
            "rowList" => '[]',
            "treeGrid" => 'true',
            "ExpCol" => 'SUBJECT',
            "arrayTable" => $matriceMail,
            "colNames" => array(
                'ID',
                'Tipo',
                'Oggetto',
                'Mittente',
                'Data'
            ),
            "colModel" => array(
                array("name" => 'ROWID', "width" => 10, "hidden" => "true", "key" => "true"),
                array("name" => 'PECTIPO', "width" => 100),
                array("name" => 'SUBJECT', "width" => 300),
                array("name" => 'FROMADDR', "width" => 250),
                array("name" => 'MSGDATE', "width" => 150)
            )
        );

        $_POST = array();
        $_POST['event'] = 'openform';
        $_POST['gridOptions'] = $gridOptions;
        $_POST['returnModel'] = $returnModel;
        $_POST['returnEvent'] = 'returnLegame';
        if ($returnEvent != '')
            $_POST['returnEvent'] = $returnEvent;

        $_POST['retid'] = '';
        $_POST['returnKey'] = 'retKey';
        itaLib::openForm($model);
        $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
        $model();
    }

}
