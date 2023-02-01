<?php

/**
 *
 * LIBRERIA PER LA GESTIONE DELLE RICHIESTE DAL FRONT OFFICE ITALSOFT LOCALE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    19.07.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praFrontOfficeItalsoftLocal extends praFrontOfficeManager {

    /**
     *
     */
    public function scaricaPraticheNuove() {

        $this->retStatus = array(
            'Status' => true,
            'Lette' => 0,
            'Scaricate' => 0,
            'Errori' => 0,
            'Messages' => array()
        );

        /*
         * Preparo la query
         */
        $whereVisibilita = $this->praLib->GetWhereVisibilitaSportelloFO();

        $sql = "SELECT
            PRORIC.RICNUM AS RICNUM,
            PRORIC.ROWID AS ROWID,
            PRORIC.RICRES AS RICRES,
            PRORIC.RICTIM AS RICTIM,
            PRORIC.RICTSP AS RICTSP,
            PRORIC.RICSPA AS RICSPA,
            PRORIC.RICDAT AS RICDAT,
            PRORIC.RICSTA AS RICSTA,
            PRORIC.RICRPA AS RICRPA,
            CONCAT(ANAPRA.PRADES__1, ANAPRA.PRADES__2, ANAPRA.PRADES__3) AS PRADES__1,
            PRORIC.RICSTT AS RICSTT,
            PRORIC.RICATT AS RICATT,
            PRORIC.PROPAK AS PROPAK,
            PRORIC.RICPC AS RICPC,
            PRORIC.RICCOG AS RICCOG,
            PRORIC.RICNOM AS RICNOM,
            PRORIC.RICNPR AS RICNPR,
            PRORIC.RICDPR AS RICDPR,
            PRORIC.CODICEPRATICASW AS CODICEPRATICASW,
            PRORIC.RICUUID AS RICUUID,
            PRORIC.RICDRE AS RICDRE," .
                $this->praLib->getPRAMDB()->strConcat("RICCOG", "' '", "RICNOM") . " AS INTESTATARIO,
            PROGES.GESPRA AS GESPRA,
            PROPAS.PRORIN AS PRORIN
            FROM PRORIC PRORIC
               LEFT OUTER JOIN ANAPRA ON PRORIC.RICPRO=ANAPRA.PRANUM
               LEFT OUTER JOIN PROGES PROGES ON RICNUM=PROGES.GESPRA
               LEFT OUTER JOIN PROPAS PROPAS ON RICNUM=PROPAS.PRORIN
            WHERE (RICSTA = 01 OR RICSTA = 91)
            AND RICRUN = ''
            AND PROGES.GESPRA IS NULL
            AND PROPAS.PRORIN IS NULL" . $whereVisibilita;

        $richieste_tab_totali = $this->praLib->getGenericTab($sql);

        /*
         * Controllo le richieste da inserire
         */
        $richieste_tab = $this->controlloPratiche($richieste_tab_totali);

        /*
         * Chiamo scarica pratica e preparo l'array di PRAFOLIST
         */
        $this->retStatus['Lette'] = count($richieste_tab);
        foreach ($richieste_tab as $richieste_rec) {
            if (!$this->scaricaPratica($richieste_rec)) {
                $this->retStatus['Errori'] += 1;
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'][] = $this->getErrMessage();
            } else {
                $this->retStatus['Scaricate'] += 1;
            }
        }
    }

    /**
     * 
     * @param type $richieste_tab array delle richieste
     * @return type array con solo le pratiche da inserire
     */
    private function controlloPratiche($richieste_tab) {
        $richieste_tab_da_inserire = array();
        $sql = "SELECT * FROM PRAFOLIST WHERE FOTIPO = '" . praFrontOfficeManager::TYPE_FO_ITALSOFT_LOCAL . "'";
        $prafolist_tab = $this->praLib->GetGenericTab($sql);
        $array1 = array_column($richieste_tab, "RICNUM");
        $array2 = array_column($prafolist_tab, "FOIDPRATICA");
        $arrDiff = array_diff($array1, $array2);
        foreach ($arrDiff as $key => $ricnum) {
            $richieste_tab_da_inserire[] = $richieste_tab[$key];
        }
        return $richieste_tab_da_inserire;
    }

    /**
     * 
     * @param type $richieste_rec Record di PRORIC
     */
    public function scaricaPratica($richieste_rec) {

        $metadati = array();
        $metadati["PRORIC_REC"] = $richieste_rec;

        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRichiesta.class.php';
        $praLibRichiesta = praLibRichiesta::getInstance();
        $sql = "SELECT * FROM RICDAG WHERE DAGNUM = '" . $richieste_rec['RICNUM'] . "' AND RICDAT <> ''";
        $ricdag_tab = $this->praLib->getGenericTab($sql);
        $arrAggiuntivi = $praLibRichiesta->getSoggettiRichiesta($ricdag_tab);

        /*
         * Preparo l'array PRAFOLIST
         */
        $praFoList_rec = array(
            'FOTIPO' => praFrontOfficeManager::TYPE_FO_ITALSOFT_LOCAL,
            'FODATASCARICO' => date("Ymd"),
            'FOORASCARICO' => date("H:i:s"),
            'FOPRAKEY' => $richieste_rec['RICNUM'],
            'FOIDPRATICA' => $richieste_rec['RICNUM'],
            'FOTIPOSTIMOLO' => $this->getStimolo($richieste_rec),
            'FOPRASPACATA' => $richieste_rec['RICTSP'],
            'FOPRADESC' => $richieste_rec['PRADES__1'],
            'FOPRADATA' => $richieste_rec['RICDAT'],
            'FOPRAORA' => $richieste_rec['RICTIM'],
            'FOPROTDATA' => $richieste_rec['RICDPR'],
            'FOPROTORA' => "",
            'FOPROTNUM' => substr($richieste_rec['RICNPR'], 4),
            'FOESIBENTE' => $richieste_rec['RICCOG'] . " " . $richieste_rec['RICNOM'],
            'FODICHIARANTE' => $arrAggiuntivi['DICHIARANTE']['COGNOME'] . " " . $arrAggiuntivi['DICHIARANTE']['NOME'],
            'FODICHIARANTECF' => $arrAggiuntivi['DICHIARANTE']['FISCALE'],
            'FODICHIARANTEQUALIFICA' => $arrAggiuntivi['DICHIARANTE']['QUALIFICA'],
            'FOALTRORIFERIMENTODESC' => "Denominazione Impresa",
            'FOALTRORIFERIMENTO' => $arrAggiuntivi['IMPRESA']['DENOMINAZIONE'],
            'FOALTRORIFERIMENTOIND' => $arrAggiuntivi['IMPRESA']['VIA'] . " " . $arrAggiuntivi['IMPRESA']['CIVICO'],
            'FOALTRORIFERIMENTOCAP' => $arrAggiuntivi['IMPRESA']['CAP'],
            //'FOMETADATA' => json_encode($metadati),
            'FOMETADATA' => serialize($metadati),
            'FOCODICEPRATICASW' => $richieste_rec['CODICEPRATICASW']
        );

        if ($richieste_rec['RICUUID']) {
            $praFoList_rec['FOUUIDRICHIESTA'] = $richieste_rec['RICUUID'];
        }

        $data = array(
            "PRAFOLIST" => $praFoList_rec,
            "PRAFOFILES" => array(),
        );

        /*
         * Salvo l'array su PRAFOLIST e PRAFOFILES
         */
        $retSalva = $this->salvaPratica($data);
        if (!$retSalva) {
            return false;
        }
        return true;
    }

    /**
     * 
     * @param type $data array che contine il recordo PRAFOLIST
     * @return boolean
     */
    public function salvaPratica($data) {

        /*
         * Salvo record su PRAFOLIST
         */
        $praFoList_rec = $data['PRAFOLIST'];
        try {
            $nRows = ItaDB::DBInsert($this->praLib->getPRAMDB(), 'PRAFOLIST', 'ROWID', $praFoList_rec);
            if ($nRows == -1) {
                $this->setErrCode(-1);
                $this->setErrMessage("Inserimento su PRAFOLIST non avvenuto.");
                return false;
            }
        } catch (Exception $e) {
            $this->setErrCode(-1);
            $this->setErrMessage("Pratica " . $praFoList_rec['FOPRAKEY'] . " già riletta dal sistema: " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function getDescrizioneGeneraleRichiestaFo($prafolist_rec) {
        $pathAllegatiRichieste = $this->praLib->getPathAllegatiRichieste();
        $bodyResponsabile = $pathAllegatiRichieste . "attachments/" . $prafolist_rec['FOPRAKEY'] . "/body.txt";
        return file_get_contents($bodyResponsabile);
    }

    public function getAllegatiRichiestaFo($prafolist_rec, $allegatiInfocamere) {
        $allegati = $this->praLib->GetAllegatiPratica($prafolist_rec['FOPRAKEY']);
        if ($allegatiInfocamere) {
            $allegatiTabella = array_merge($allegati, $allegatiInfocamere);
        } else {
            $allegatiTabella = $allegati;
        }

        foreach ($allegatiTabella as $key => $allegato) {
            $allegatiTabella[$key]['ROW_ID'] = $key;
            $allegatiTabella[$key]['FILEFIL'] = $allegato['FILEINFO'];
            $allegatiTabella[$key]['FOPRAKEY'] = $prafolist_rec['FOPRAKEY'];
            $allegatiTabella[$key]['FOTIPO'] = $prafolist_rec['FOTIPO'];
        }
        return $allegatiTabella;
    }

    public function checkFoAcqPreconditions($param) {
        return true;
    }

    public function getDataModelAcq($praFoList_rec, $dati) {
        $dati['ELENCOALLEGATI'] = $this->praLib->GetAllegatiPratica($praFoList_rec['FOPRAKEY']);
        $dati['PRAFOLIST_REC'] = $praFoList_rec;
        $dati['PRORIC_REC'] = $this->getProricRec($praFoList_rec);
        unset($dati['ANADES']);
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLibRichiesta.class.php';
        $praLibRichiesta = praLibRichiesta::getInstance();
        $arrDati = $praLibRichiesta->getDatiRichiesta($dati);
        $arrDati["FILENAME"] = $dati['FILENAME'];
        if (isset($dati['PRAMAIL_REC'])) {
            $arrDati['PRAMAIL_REC'] = $dati['PRAMAIL_REC'];
        }
        if (isset($dati['archivio'])) {
            $arrDati['archivio'] = $dati['archivio'];
        }
        if (isset($dati['IDMAIL'])) {
            $arrDati['IDMAIL'] = $dati['IDMAIL'];
        }
        return array($arrDati);
    }

    public function openFormDatiEssenziali($praFoList_rec, $dati = array()) {
        $proric_rec = $this->praLib->GetProric($praFoList_rec['FOPRAKEY']);
        $model = 'praGestDatiEssenziali';
        $_POST['returnModel'] = "praCtrRichiesteFO";
        $_POST['returnEvent'] = 'returnDatiEssenziali';
        $_POST['datiMail']['Dati'] = $dati;
        $_POST['datiMail']['Dati']['PRORIC_REC'] = $proric_rec;
        $_POST['datiMail']['Dati']['PRAFOLIST_REC'] = $praFoList_rec;
        $_POST['isFrontOffice'] = true;
        itaLib::openForm($model);
        $objModel = itaModel::getInstance($model);
        $objModel->setEvent("openform");
        $objModel->parseEvent();
    }

    private function getStimolo($richieste_rec) {
        $stimolo = "";
        if ($richieste_rec ['RICSTA'] != "91" && $richieste_rec ['RICRPA'] == "" && $richieste_rec['PROPAK'] == "") {
            $stimolo = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_ITALSOFT_LOCAL][praFrontOfficeManager::STIMOLO_FO_PRESENTAZIONE_PRATICA];
        } else {
            if ($richieste_rec['RICSTA'] == "91") {
                $stimolo = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_ITALSOFT_LOCAL][praFrontOfficeManager::STIMOLO_FO_COMUNICA];
            } else if ($richieste_rec['RICRPA']) {
                $stimolo = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_ITALSOFT_LOCAL][praFrontOfficeManager::STIMOLO_FO_INVIO_INTEGRAZIONI];
                if ($richieste_rec['RICPC'] == "1") {
                    $stimolo = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_ITALSOFT_LOCAL][praFrontOfficeManager::STIMOLO_FO_RICHIESTA_COLLEGATA];
                }
            } else if ($richieste_rec['PROPAK']) {
                $stimolo = praFrontOfficeManager::$FRONT_OFFICE_TYPES_STIMOLI[praFrontOfficeManager::TYPE_FO_ITALSOFT_LOCAL][praFrontOfficeManager::STIMOLO_FO_PARERI_ESTERNI];
            }
        }
        return $stimolo;
    }

    public function getProricRec($prafolist_rec) {
        return $this->praLib->GetProric($prafolist_rec['FOIDPRATICA']);
    }

    public function getAllegato($prafolist_rec, $rowidAlle, $allegatiInfocamere) {
        $allegati = $this->praLib->GetAllegatiPratica($prafolist_rec['FOPRAKEY']);
        if ($allegatiInfocamere) {
            $allegatiTabella = array_merge($allegati, $allegatiInfocamere);
        } else {
            $allegatiTabella = $allegati;
        }
        return $allegatiTabella[$rowidAlle];
    }

    public function caricaRichiestaFO($prafolist_rec, $dati, $allegatiInfocamere) {
        $proric_rec = $this->getProricRec($prafolist_rec);
        $variante = false;
        if ($proric_rec['RICPC'] == "1") {
            $variante = true;
        }

        if ($proric_rec['RICSTA'] == "91" && !$allegatiInfocamere) {
            Out::msgQuestion("RICHIESTA CAMERA DI COMMERCIO!", "Hai ricevuto la mail di conferma dalla camera di commercio?", array(
                'F8-No' => array('id' => 'praCtrRichiesteFO_NoConfermaMail', 'model' => 'praCtrRichiesteFO', 'shortCut' => "f8"),
                'F5-Si' => array('id' => 'praCtrRichiesteFO_SiConfermaMail', 'model' => 'praCtrRichiesteFO', 'shortCut' => "f5")
                    ), "auto", "auto", "false"
            );
            return true;
        } else if (($proric_rec['RICRPA'] && !$variante) || $proric_rec['PROPAK']) {
            $ret_esito = null;
            if (!praFrontOfficeManager::caricaFascicoloFromPRAFOLIST($prafolist_rec['ROW_ID'], $ret_esito, $dati)) {
                $this->retStatus['Errori'] = 1;
                $this->retStatus['Status'] = false;
                $this->retStatus['Messages'] = "Errore di acquisizione: " . praFrontOfficeManager::$lasErrMessage;
                return false;
            }
        } else {
            $this->openFormDatiEssenziali($prafolist_rec, $dati);
            return true;
        }
        return $ret_esito;
    }

}
