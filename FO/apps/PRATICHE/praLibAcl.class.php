<?php

/**
 * Description of praLibAcl
 *
 * @author Simone Franchi
 */
class praLibAcl {

    private $errCode;
    private $errMessage;

    function getErrCode() {
        return $this->errCode;
    }

    function getErrMessage() {
        return $this->errMessage;
    }

    function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    static $CURRENT_ROLES = array("ESIBENTE", "DICHIARANTE", "IMPRESA", "IMPRESAINDIVIDUALE");

    /**
     * 
     * @param type $arraySoggetto
     * @param type $PRAM_DB
     * @return boolean
     */
    public function caricaSoggetto($arraySoggetto, $PRAM_DB, $rowidProric, $rowidPasso = 0) {
        $praLibAudit = new praLibAudit();
        $arraySoggetto['SOGRICFIS'] = strtoupper($arraySoggetto['SOGRICFIS']);
        try {
            if ($arraySoggetto['SOGRICUUID']) {
                $sql = "SELECT * FROM RICSOGGETTI WHERE SOGRICUUID = '" . $arraySoggetto['SOGRICUUID'] . "' ";
            } else {
                $sql = "SELECT * FROM RICSOGGETTI WHERE SOGRICNUM = " . $arraySoggetto['SOGRICNUM'] . " ";
            }
            $sql .= " AND SOGRICFIS = '" . $arraySoggetto['SOGRICFIS'] . "' AND SOGRICRUOLO = '" . $arraySoggetto['SOGRICRUOLO'] . "' "
                    . " AND SOGRICDATA_FINE = '' ";
            $soggetto_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
            if (!$soggetto_rec) {
                $nrow = ItaDB::DBInsert($PRAM_DB, "RICSOGGETTI", 'ROW_ID', $arraySoggetto);
                if ($nrow != 1) {
                    $this->setErrMessage("Errore Caricamento Soggetto: " . $arraySoggetto['SOGRICFIS']);
                    return false;
                }
                $praLibAudit->logEqEvent(array(
                    'ROWID_PRORIC' => $rowidProric,
                    'ROWID_PASSO' => $rowidPasso,
                    'RICFIS' => $arraySoggetto['SOGRICFIS'],
                    'Key' => "ROW_ID",
                    'Operazione' => eqAudit::OP_INS_RECORD,
                    'Estremi' => "Inserito Soggetto in RICSOGGETTI con codice Fiscale " . $arraySoggetto['SOGRICFIS']
                ));
            }
        } catch (Exception $e) {
            $this->setErrMessage("Errore Caricamento Soggetto: " . $arraySoggetto['SOGRICFIS'] . "-->" . $e->getMessage());
            return false;
        }
        return true;
    }

    public function cancellaSoggetto($ricsoggetti_rec, $PRAM_DB, $rowidProric, $rowidPasso = 0) {
        $praLibAudit = new praLibAudit();

        try {
            if ($ricsoggetti_rec) {
                $nrow = ItaDb::DBDelete($PRAM_DB, 'RICSOGGETTI', 'ROW_ID', $ricsoggetti_rec['ROW_ID']);
                if ($nrow == 0) {
                    $this->setErrMessage("Errore Cancellazione Soggetto ");
                    return false;
                }
                $praLibAudit->logEqEvent(array(
                    'ROWID_PRORIC' => $rowidProric,
                    'ROWID_PASSO' => $rowidPasso,
                    'RICFIS' => $ricsoggetti_rec['SOGRICFIS'],
                    'Key' => "ROW_ID",
                    'Operazione' => eqAudit::OP_DEL_RECORD,
                    'Estremi' => "Cancellato Soggetto in RICSOGGETTI con codice Fiscale " . $ricsoggetti_rec['SOGRICFIS']
                ));
            }
        } catch (Exception $e) {
            $this->setErrMessage("Errore Cancellazione Soggetto: " . $ricsoggetti_rec['SOGRICFIS'] . "-->" . $e->getMessage());
            return false;
        }

        return true;
    }

    public function cessaSoggetto($ricsoggetti_rec, $PRAM_DB, $rowidProric, $rowidPasso = 0) {
        $praLibAudit = new praLibAudit();
        try {
            if ($ricsoggetti_rec) {
                $ricsoggetti_rec['SOGRICDATA_FINE'] = date("Ymd");
                $nrow = ItaDB::DBUpdate($PRAM_DB, 'RICSOGGETTI', 'ROW_ID', $ricsoggetti_rec);
                if ($nrow == 0) {
                    $this->setErrMessage("Errore Aggiornamento Cessazione Soggetto " . $ricsoggetti_rec['SOGRICFIS']);
                    return false;
                }

                $praLibAudit->logEqEvent(array(
                    'ROWID_PRORIC' => $rowidProric,
                    'ROWID_PASSO' => $rowidPasso,
                    'RICFIS' => $ricsoggetti_rec['SOGRICFIS'],
                    'Key' => "ROW_ID",
                    'Operazione' => eqAudit::OP_UPD_RECORD,
                    'Estremi' => "Cessato Soggetto in RICSOGGETTI con codice Fiscale " . $ricsoggetti_rec['SOGRICFIS'] . " e rowid PRORIC $rowidProric."
                ));
            }
        } catch (Exception $e) {
            $this->setErrMessage("Errore Cessazione Soggetto: " . $ricsoggetti_rec['SOGRICFIS'] . "-->" . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 
     * @param type $ricnum
     * @param type $prefissoTipoSoggetto
     * @param type $PRAM_DB
     * @param type $ricuuid
     * @return boolean
     */
    public function sincronizzaSoggetto($dati, $prefissoTipoSoggetto) {

        // Salvo i soggetti già presenti nel vettore $arraySogEsistenti
        $sqlSoggetti = "SELECT * FROM RICSOGGETTI WHERE SOGRICNUM = " . $dati['Proric_rec']['RICNUM']
                . " AND SOGRICRUOLO = '" . praRuolo::$SISTEM_SUBJECT_ROLES[$prefissoTipoSoggetto]['RUOCOD'] . "'";
        $arraySogEsistenti = ItaDB::DBSQLSelect($dati['PRAM_DB'], $sqlSoggetti, true);
//        if ($ricsoggetti_tab){
//            $arraySogEsistenti = $ricsoggetti_tab;
//        }


        $sql = "SELECT * FROM RICDAG WHERE DAGNUM =  " . $dati['Proric_rec']['RICNUM'] . " "
                . " AND RICDAG.DAGKEY LIKE '" . $prefissoTipoSoggetto . "\_%'";

        try {
            $campi_tab = ItaDB::DBSQLSelect($dati['PRAM_DB'], $sql, true);
            if ($campi_tab) {
                $arrayDichiaranti = array();

                foreach ($campi_tab as $campi_rec) {

                    list($baseName, $index) = explode("-", $campi_rec['DAGKEY']);
                    if ($index === null) {
                        $index = '';
                    }
                    list($prefisso, $campo, $tipo) = explode("_", $baseName);

                    if ($campo == 'RAGIONESOCIALE' || $campo == 'COGNOME' || $campo == 'NOME' || $campo == 'CODICEFISCALE' ||
                            $campo == 'PARTITAIVA') {
                        $arrayDichiaranti[$campi_rec['DAGSET']][$campo] = $campi_rec['RICDAT'];
                    }
                }
            }

            // Cancellare RICSOGGETTI, scorrendo $arraySogEsistenti
            if ($arraySogEsistenti) {
                foreach ($arraySogEsistenti as $soggetto) {
                    $trovato = false;

                    if ($arrayDichiaranti) {
                        foreach ($arrayDichiaranti as $dichiarante) {
                            $codFisc = $this->getCodFiscSoggetto($dichiarante['CODICEFISCALE'], $dichiarante['PARTITAIVA']);

                            if ($codFisc == $soggetto['SOGRICFIS']) {
                                $trovato = true;
                                break;
                            }
                        }
                    }

                    if (!$trovato) {
                        // Si cancella record di RICSOGGETTI e operazione si salva in RICOPERAZ
                        if (!$this->cancellaSoggetto($soggetto, $dati['PRAM_DB'], $dati['Proric_rec']['ROWID'], $dati['Ricite_rec']['ROWID'])) {
                            // Il messaggio viene gestito nel metodo caricaSoggetto
                            return false;
                        }
                    }
                }
            }


            // Scorro dichiaranti trovati
            if ($arrayDichiaranti) {

                foreach ($arrayDichiaranti as $dichiarante) {
                    if ($dichiarante['CODICEFISCALE'] || $dichiarante['PARTITAIVA']) {
                        $codFisc = $this->getCodFiscSoggetto($dichiarante['CODICEFISCALE'], $dichiarante['PARTITAIVA']);
                        $nominativo = $this->getNominativo($dichiarante['RAGIONESOCIALE'], $dichiarante['COGNOME'], $dichiarante['NOME']);

                        $arraySoggetto = array(
                            'SOGRICNUM' => $dati['Proric_rec']['RICNUM'],
                            'SOGRICUUID' => $dati['Proric_rec']['RICUUID'],
                            'SOGRICFIS' => $codFisc,
                            'SOGRICDENOMINAZIONE' => $nominativo,
                            'SOGRICRUOLO' => praRuolo::$SISTEM_SUBJECT_ROLES[$prefissoTipoSoggetto]['RUOCOD'],
                            'SOGRICRICDATA_INIZIO' => $dati['Proric_rec']['RICDRE'],
                            'SOGRICDATA_FINE' => '',
                            'SOGRICNOTE' => ''
                        );

                        if (!$this->caricaSoggetto($arraySoggetto, $dati['PRAM_DB'], $dati['Proric_rec']['ROWID'], $dati['Ricite_rec']['ROWID'])) {
                            // Il messaggio viene gestito nel metodo caricaSoggetto
                            return false;
                        }
                    }
                }
            }
        } catch (Exception $e) {
            $this->setErrMessage("Errore Caricamento Soggetto Dichiarante -->" . $e->getMessage());
            return false;
        }

        return true;
    }

    public function getCodFiscSoggetto($campoCF, $campoPIva) {
        $codFisc = $campoCF;
        if (!$codFisc) {
            $codFisc = $campoPIva;
        }

        if ($codFisc === null) {
            $codFisc = '';
        }

        return $codFisc;
    }

    public function getNominativo($ragSoc, $cognome, $nome) {
        $nominativo = $ragSoc;
        if (!$nominativo) {
            $nominativo = $cognome . " " . $nome;
        }
        return $nominativo;
    }

    public function getAclPasso($dati) {
        $perms = array();
        $perms['Insert'] = false;
        $perms['Edit'] = false;
        $perms['Delete'] = false;

        //Controllo se è esibente
        if ($dati['Proric_rec']['RICFIS'] == $dati['Fiscale']) {
            $perms['Insert'] = true;
            $perms['Edit'] = true;
            $perms['Delete'] = true;
        }


        foreach ($dati['Ricacl_tab'] as $Ricacl_rec) {
            if ($Ricacl_rec['RICACLMETA']) {
                $arrAcl = json_decode($Ricacl_rec['RICACLMETA'], true);
                if (is_array($arrAcl)) {
                    foreach ($arrAcl['AUTORIZZAZIONE'] as $autorizzazione) {
                        if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_PASSO' && $autorizzazione['ROW_ID_PASSO'] == $dati['Ricite_rec']['ROWID']) {
                            $perms['Insert'] = $autorizzazione['INSERISCI'];
                            $perms['Edit'] = $autorizzazione['MODIFICA'];
                            $perms['Delete'] = $autorizzazione['CANCELLA'];
                        }
                    }
                }
            }
        }
        return $perms;
    }

    public function caricaAcl($arrayAcl, $PRAM_DB, $rowidProric = 0, $cf = '') {
        $praLibAudit = new praLibAudit();
        try {
            $nrow = ItaDB::DBInsert($PRAM_DB, "RICACL", 'ROW_ID', $arrayAcl);
            if ($nrow != 1) {
                $this->setErrMessage("Errore Caricamento Regola di Condivisione ");
                return false;
            }

            $idRicAcl = ItaDb::DBLastId($PRAM_DB);

            $praLibAudit->logEqEvent(array(
                'ROWID_PRORIC' => $rowidProric,
                'ROWID_PASSO' => $arrayAcl['ROW_ID_PASSO'],
                'RICFIS' => $cf,
                'Key' => "ROW_ID",
                'Operazione' => eqAudit::OP_INS_RECORD,
                'Estremi' => "Inserito ACL con ID = " . $idRicAcl . " per il soggetto con codice Fiscale " . $cf . " e rowid PRORIC = $rowidProric."
            ));
        } catch (Exception $e) {
            $this->setErrMessage("Errore Caricamento Regola di condivisione --> " . $e->getMessage());
            return false;
        }
        return true;
    }

    public function getPassiDisponibili($dati) {
        $praLib = new praLib();
        $arrPassiDisponibili = array();
        foreach ($dati['Ricacl_tab'] as $acl_rec) {
            if ($acl_rec['RICACLMETA']) {
                $arrAcl = json_decode($acl_rec['RICACLMETA'], true);
                if (is_array($arrAcl)) {
                    foreach ($arrAcl['AUTORIZZAZIONE'] as $autorizzazione) {
                        if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == 'GESTIONE_PASSO') {
                            $arrPassiDisponibili[] = $praLib->GetRicite($autorizzazione['ROW_ID_PASSO'], "rowid", $dati["PRAM_DB"], false);
                        }
                    }
                }
            }
        }
        return $arrPassiDisponibili;
    }

    public function getHtmlIcona($passiDisponibili, $idRicIte) {
        $icon = "locked";
        foreach ($passiDisponibili as $passo_rec) {
            if ($idRicIte == $passo_rec['ROWID']) {
                $icon = "edit";
                break;
            }
        }
        return "<i style=\"padding-left:2px;float:right;\" class=\"icon ion-$icon italsoft-icon\"></i>";
    }

    public function cessaRicAcl($ricAcl_rec, $PRAM_DB, $rowidProric = 0, $cf = '') {
        $praLibAudit = new praLibAudit();
        try {
            if ($ricAcl_rec) {
                $ricAcl_rec['RICACLTRASHED'] = 1;
                $nrow = ItaDB::DBUpdate($PRAM_DB, 'RICACL', 'ROW_ID', $ricAcl_rec);
                if ($nrow == 0) {
                    $this->setErrMessage("Errore Aggiornamento Cessazione Regola di Condivisione con ROW_ID = " . $ricAcl_rec['ROW_ID']);
                    return false;
                }
                $praLibAudit->logEqEvent(array(
                    'ROWID_PRORIC' => $rowidProric,
                    'ROWID_PASSO' => $ricAcl_rec['ROW_ID_PASSO'],
                    'RICFIS' => $cf,
                    'Key' => "ROW_ID",
                    'Operazione' => eqAudit::OP_DEL_RECORD,
                    'Estremi' => "Cessato ACL con ID = " . $ricAcl_rec['ROW_ID'] . " per il soggetto con codice Fiscale " . $cf . " e rowid PRORIC $rowidProric."
                ));
            }

            //Vedere se RICSOGGETTO ha altre RICACL, altrimenti si potrebbe anche cancellare !!!!
        } catch (Exception $e) {
            $this->setErrMessage("Errore Cessazione Regola di Condivisione -->" . $e->getMessage());
            return false;
        }

        return true;
    }

    public function modificaRicAcl($ricAcl_rec, $PRAM_DB, $rowidProric = 0, $cf = '') {
        $praLibAudit = new praLibAudit();
        try {
            if ($ricAcl_rec) {
                $nrow = ItaDB::DBUpdate($PRAM_DB, 'RICACL', 'ROW_ID', $ricAcl_rec);
                if ($nrow == -1) {
                    $this->setErrMessage("Errore Modifica Regola di Condivisione con ROW_ID = " . $ricAcl_rec['ROW_ID']);
                    return false;
                }
                $praLibAudit->logEqEvent(array(
                    'ROWID_PRORIC' => $rowidProric,
                    'ROWID_PASSO' => $ricAcl_rec['ROW_ID_PASSO'],
                    'RICFIS' => $cf,
                    'Key' => "ROW_ID",
                    'Operazione' => eqAudit::OP_UPD_RECORD,
                    'Estremi' => "Modificato ACL con ID = " . $ricAcl_rec['ROW_ID'] . " per il soggetto con codice Fiscale " . $cf . " e rowid PRORIC $rowidProric."
                ));
            }
        } catch (Exception $e) {
            $this->setErrMessage("Errore Modifica Regola di Condivisione -->" . $e->getMessage());
            return false;
        }

        return true;
    }

    /**
     * 
     * @param type $tipoAcl: Valori ACL_CAMBIO_ESIBENTE; ACL_INTEGRAZIONE; ACL_VISIBILITA; ACL_GESTIONE_PASSO
     * @param type $dati
     * @return boolean
     */
//    public function isEnableACLButton_lento($tipoAcl, $dati) {  //        $dati['Proric_rec']['RICTSP']
//
//        /*
//         * Controllo se attivata voce "ACL_ATTIVAZIONE" nella tabella ENV_CONFIG in ITAFRONOFFICE
//         */
//        $frontOfficeLib = new frontOfficeLib();
//        if (!$this->getAclDaParametriFO($frontOfficeLib, 'ACL_ATTIVAZIONE', $dati['ITAFRONTOFFICE_DB'])) {
//            return false;
//        }
//
//
//        /**
//         * Controllo se voce nello Sportello On-Line è attivato
//         */
//        if ($dati['Anatsp_rec']) {
//            /**
//             * Si analizza il campo ANATSP.TSPMETAJSON
//             */
//            if ($dati['Anatsp_rec']['TSPMETAJSON']) {
//                if ($this->getAclSportello($dati['Anatsp_rec']['TSPMETAJSON'], $tipoAcl)) {
//                    return true;
//                }
//            }
//        }
//
//
//        /**
//         * controllo se voce nei Parametri Vari FO è attivata
//         */
//        if ($this->getAclDaParametriFO($frontOfficeLib, $tipoAcl, $dati['ITAFRONTOFFICE_DB'])) {
//            return true;
//        }
//
//        return false;
//    }

    /**
     * 
     * @param type $tipoAcl: Valori ACL_CAMBIO_ESIBENTE; ACL_INTEGRAZIONE; ACL_VISIBILITA; ACL_GESTIONE_PASSO
     * @param type $ricnum
     * @return boolean
     */
    public function isEnableACLButton($tipoAcl, $ricnum) {  //        $dati['Proric_rec']['RICTSP']
        $PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
        /*
         * Controllo se attivata voce "ACL_ATTIVAZIONE" nella tabella ENV_CONFIG in ITAFRONOFFICE
         */
        $frontOfficeLib = new frontOfficeLib();
        $ITAFO_DB = ItaDB::DBOpen('ITAFRONTOFFICE', frontOfficeApp::getEnte());
        if (!$this->getAclDaParametriFO($frontOfficeLib, 'ACL_ATTIVAZIONE', $ITAFO_DB)) {
            return false;
        }


        /**
         * Controllo se voce nello Sportello On-Line è attivato
         */
        $sql = "SELECT ANATSP.* FROM ANATSP "
                . "LEFT JOIN PRORIC ON PRORIC.RICTSP = ANATSP.TSPCOD "
                . "WHERE PRORIC.RICNUM = '" . $ricnum . "'";

        $anatsp_rec = ItaDB::DBSQLSelect($PRAM_DB, $sql, false);
        if ($anatsp_rec) {
            /**
             * Si analizza il campo ANATSP.TSPMETAJSON
             */
            if ($anatsp_rec['TSPMETAJSON']) {
                if ($this->getAclSportello($anatsp_rec['TSPMETAJSON'], $tipoAcl)) {
                    return true;
                }
            }
        }


        /**
         * controllo se voce nei Parametri Vari FO è attivata
         */
        if ($this->getAclDaParametriFO($frontOfficeLib, $tipoAcl, $ITAFO_DB)) {
            return true;
        }

        return false;
    }

    private function getAclSportello($metaJson, $tipoAcl) {
        $attivo = false;
        $arrayACL = json_decode($metaJson);

        foreach ($arrayACL as $aclElement) {

            foreach ($aclElement as $key => $acl) {

                switch ($key) {
                    case $tipoAcl:
                        if ($acl == 'S') {
                            $attivo = true;
                        }
                        break;

//                    case 'ACL_CAMBIO_ESIBENTE':
//                        Out::valore($this->nameForm . '_ACLCAMBIOESIBENTE', $acl);
//                        break;
//                    case 'ACL_INTEGRAZIONE':
//                        Out::valore($this->nameForm . '_ACLINTEGRAZIONE', $acl);
//                        break;
//                    case 'ACL_VISIBILITA':
//                        Out::valore($this->nameForm . '_ACLVISUALIZZAZIONE', $acl);
//                        break;
//                    case 'ACL_GESTIONE_PASSO':
//                        Out::valore($this->nameForm . '_ACLGESTIONEPASSI', $acl);
//                        break;
                }
            }
        }

        return $attivo;
    }

    public function getAclDaParametriFO($frontOfficeLib, $tipoAcl, $ITAFRONTOFFICE_DB) {

        $attivaACL = false;
        $envconfig_tab = $frontOfficeLib->getEnv_config("ACL", $ITAFRONTOFFICE_DB);
        if ($envconfig_tab) {
            foreach ($envconfig_tab as $envconfig_rec) {

                if ($envconfig_rec['CHIAVE'] == $tipoAcl) {
                    if ($envconfig_rec['CONFIG'] == 'Si') {
                        $attivaACL = true;
                    }
                    break;
                }
            }
        }
        return $attivaACL;
    }

    public function checkAclAttiva($aclRichiesta, $tipoAut, $tipoAcl = '', $cf = '', $rowidPasso = '') {
        foreach ($aclRichiesta as $Ricacl_rec) {
            if ($Ricacl_rec['RICACLMETA']) {
                $arrAcl = json_decode($Ricacl_rec['RICACLMETA'], true);
                if (is_array($arrAcl)) {
                    foreach ($arrAcl['AUTORIZZAZIONE'] as $autorizzazione) {
                        if ($tipoAut == 'GESTIONE_RICHIESTA_INTEGRAZIONE') {
                            if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == "GESTIONE_RICHIESTA") {
                                if ($tipoAcl == 'INTEGRAZIONE_RICHIESTA') {
                                    if ($autorizzazione[$tipoAcl] == 1) {
                                        return true;
                                    }
                                }
                            }
                        } else if ($tipoAut == 'GESTIONE_RICHIESTA_VISUALIZZAZIONE') {
                            if ($autorizzazione['TIPO_AUTORIZZAZIONE'] == "GESTIONE_RICHIESTA" && $tipoAcl == '' && !isset($autorizzazione['INTEGRAZIONE_RICHIESTA'])) {
                                if ($cf == $Ricacl_rec['SOGRICFIS']) {
                                    return true;
                                }
                            }
                        } else if ($tipoAut == 'GESTIONE_PASSO') {
                            if ($Ricacl_rec['ROW_ID_PASSO'] == $rowidPasso) {
                                return true;
                            }
                        }

//                        //switch ($tipoAut) {
//                        switch ($autorizzazione['TIPO']) {
//                            case 'GESTIONE_RICHIESTA':
//                                if ($tipoAcl == 'INTEGRAZIONE_RICHIESTA') {
//                                    if ($autorizzazione[$tipoAcl] == 1) {
//                                        return true;
//                                    }
//                                } else {
//                                    if ($cf == $Ricacl_rec['SOGRICFIS']) {
//                                        return true;
//                                    }
//                                }
//                                break;
//                            case 'GESTIONE_PASSO':
//                                if ($Ricacl_rec['ROW_ID_PASSO'] == $rowidPasso) {
//                                    return true;
//                                }
//                                break;
//
//                            default:
//                                break;
//                        }
                    }
                }
            }
        }
        return false;
    }

}
