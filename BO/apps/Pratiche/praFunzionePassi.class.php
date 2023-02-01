<?php

/**
 *
 * 
 *
 * PHP Version 5
 *
 * @category   
 * @package    Pratiche
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    13.04.2015
 * @link
 * @see
 * @since
 * @deprecated
 * */
class praFunzionePassi {

    const FUN_GEST_ASS = "GEST_ASS";
    const FUN_GEST_GEN = "GEST_GEN";
    const FUN_GEST_RES = "GEST_RES";
    const FUN_GEST_DIP = "GEST_DIP";

    static $FUNZIONI_BASE = array(
        "GEST_ASS" => array('DESCRIZIONE' => "Gestione Assegnazioni Passi Pratica", 'TIPONODOPROT' => 'ASS', 'GESTIONEPROT' => true),
        "GEST_GEN" => array('DESCRIZIONE' => "Gestione Generica Passi Pratica", 'TIPONODOPROT' => 'TRX', 'GESTIONEPROT' => true),
        "GEST_RES" => array('DESCRIZIONE' => "Restituzione Passi Pratica", 'TIPONODOPROT' => 'TRX', 'GESTIONEPROT' => true),
        "GEST_DIP" => array('DESCRIZIONE' => "Gestione Dati Istanza Pratica", 'METADATI' => array(
                'CLASSE' => 'Classe da richiamare per il disegno',
                'CARICAMENTO' => 'Tipo di caricamento della form (permanente|dinamico)'
            ))
    );

    const FUN_FO_ANA_SOGGETTO = "FO_ANA_SOGGETTO";
    const FUN_FO_DATI_CATASTALI = "FO_DATI_CATASTALI";
    const FUN_FO_PASSO_CARTELLA = "FO_PASSO_CARTELLA";
    const FUN_FO_PASSO_MPAY = "FO_PASSO_MPAY";
    const FUN_FO_PASSO_ACC_DOMANDA = "FO_PASSO_ACC_DOMANDA";
    const FUN_FO_PASSO_ACC_SCELTA = "FO_PASSO_ACC_SCELTA";
    const FUN_FO_PASSO_PAGOPA = "FO_PASSO_PAGOPA";

    static $FUNZIONI_FRONT_OFFICE = array(
        self::FUN_FO_ANA_SOGGETTO => array(
            'DESCRIZIONE' => "Raccolta dati Angrafica Soggetto",
            'METADATI' => array(
                "PREFISSO_CAMPI" => "Prefisso Nomi campi Anagrafici",
                "CAMPO_RUOLO" => "Nome del campo Ruolo"
            )
        ),
        self::FUN_FO_DATI_CATASTALI => array(
            'DESCRIZIONE' => "Raccolta dati Catastali",
            'METADATI' => array(
                "TIPO_BASE_DATI" => "Tipologia Controllo (italsoft|helix)",
                "CONTROLLO_DATI_DA_WS" => "Controllo dati catastali (1=attivo|0=disattivo)",
                "DEFAULT_TIPOLOGIA" => "Tipologia dato (Terreno/Fabbricato)",
            )
        ),
        self::FUN_FO_PASSO_CARTELLA => array(
            'DESCRIZIONE' => "Passo upload Cartella",
            'METADATI' => array(
                'LIMITE_UPLOAD_CARTELLA' => 'Limite di spazio disponibile nella cartella (MB)'
            )
        ),
        self::FUN_FO_PASSO_MPAY => array(
            'DESCRIZIONE' => "Passo Pagamento MPAY",
            'METADATI' => array(
                'ISTANZA_PAR' => "Codice Istanza Parametri Generali",
                'CODICE_UTENTE' => "Codice Utente",
                'CODICE_ENTE' => "Codice Ente",
                'TIPO_UFFICIO' => "Tipo Ufficio",
                'CODICE_UFFICIO' => "Codice Ufficio",
                'TIPOLOGIA_SERVIZIO' => "Tipologia Servizio"
            )
        ),
        self::FUN_FO_PASSO_ACC_DOMANDA => array(
            'DESCRIZIONE' => 'Passo domanda accorpamento',
            'METADATI' => array()
        ),
        self::FUN_FO_PASSO_ACC_SCELTA => array(
            'DESCRIZIONE' => 'Passo scelta pratica principale',
            'METADATI' => array()
        ),
        self::FUN_FO_PASSO_PAGOPA => array(
            'DESCRIZIONE' => "Passo Pagamento Pago PA",
            'METADATI' => array(
                'TIPOLOGIA_SERVIZIO' => "Tipologia Servizio",
                'ATTIVA_MODULO_1' => "Attiva Modulo 1 (0/1)",
                'ATTIVA_MODULO_3' => "Attiva Modulo 3 (0/1)",
            )
        )
    );

    static public function getFunzioni_base($codice) {
        if (isset(self::$FUNZIONI_BASE[$role])) {
            $funzione_base = self::$FUNZIONI_BASE[$codice];
            return $funzione_base;
        } else {
            return false;
        }
    }

    static public function getCurrAssegnazione($Codice, $profilo) {
        include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
        /* @var $praLib praLib */
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        $proges_rec = $praLib->GetProges($Codice, 'codice', false);
        if (!$proges_rec) {
            return false;
        }
        $procedimento = $proges_rec['GESNUM'];
        $sql = "SELECT
                    PROPAS.ROWID AS ROWID,
                    PROPAS.PROSEQ AS PROSEQ,
                    PROPAS.PRORIS AS PRORIS,
                    PROPAS.PROGIO AS PROGIO,
                    PROPAS.PROTPA AS PROTPA,
                    PROPAS.PRODTP AS PRODTP,
                    PROPAS.PRODPA AS PRODPA,
                    PROPAS.PROFIN AS PROFIN,
                    PROPAS.PROVPA AS PROVPA,
                    PROPAS.PROVPN AS PROVPN,
                    PROPAS.PROPAK AS PROPAK,
                    PROPAS.PROCTR AS PROCTR,
                    PROPAS.PROQST AS PROQST,
                    PROPAS.PROPUB AS PROPUB,
                    PROPAS.PROALL AS PROALL,
                    PROPAS.PRORPA AS PRORPA,
                    PROPAS.PROSTAP AS PROSTAP,
                    PROPAS.PROPART AS PROPART,
                    PROPAS.PROSTCH AS PROSTCH,
                    PROPAS.PROSTATO AS PROSTATO,                    
                    PROPAS.PROPCONT AS PROPCONT,
                    PROPAS.PROVISIBILITA AS PROVISIBILITA,
                    PROPAS.PRODATEADD AS PRODATEADD,
                    PROPAS.PROUTEADD AS PROUTEADD,
                    PROPAS.PROUTEEDIT AS PROUTEEDIT,
                    PROPAS.PROINI AS PROINI," .
                $PRAM_DB->strConcat('ANANOM.NOMCOG', "' '", "ANANOM.NOMNOM") . " AS RESPONSABILE,
                    PROPAS.PROOPE AS PROOPE
                FROM PROPAS
                    LEFT OUTER JOIN ANANOM ON ANANOM.NOMRES=PROPAS.PRORPA
               WHERE 
                    PROPAS.PRONUM = '$procedimento' AND
                    PROPAS.PROOPE<>''
               ORDER BY
                    PROSEQ";
        $passi_ass = ItaDB::DBSQLSelect($PRAM_DB, $sql);
        if (!$passi_ass) {
            return false;
        }
        $curr_assegnazione = array();
        foreach ($passi_ass as $passi_rec) {
            /*
             * IL PASSO ASSEGNAZIONE NON ANCORA APERTO
             */
            if ($passi_rec['PROINI'] == '' && $passi_rec['PROFIN'] == '') {
                $curr_assegnazione = $passi_rec;
                break;
            }

            /*
             * PASSO ASSEGNAZIONE APERTO MA NON CHIUSO (NON SI DOVREBBE VERIFICARE)
             */
            if ($passi_rec['PROINI'] != '' && $passi_rec['PROFIN'] == '') {
                $curr_assegnazione = $passi_rec;
                break;
            }
        }
        return $curr_assegnazione;
    }

    static public function getFunzioniAssegnazione($Codice, $rowidPasso = 0, $profilo) {
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        if ($rowidPasso == 0) {
            $curr_assegnazione = self::getCurrAssegnazione($Codice, $profilo);
        } else {
            $curr_assegnazione = $praLib->GetPropas($rowidPasso, 'rowid');
        }
        /*
         * Pratica mai assegnata, da assegnare
         * perche ritorna false
         * 
         */
        if ($curr_assegnazione === false) {
            $funcArr['ASSEGNA'] = true;
            $funcArr['PRENDIINCARICO'] = false;
            $funcArr['RESTITUISCI'] = false;
            $funcArr['RIAPRI'] = false;
            return $funcArr;
        } else {
            /*
             * Assegnata ad altro soggetto
             */
            if ($curr_assegnazione) {
                if ($curr_assegnazione['PRORPA'] != $profilo['COD_ANANOM']) {
                    $funcArr['ASSEGNA'] = false;
                    $funcArr['PRENDIINCARICO'] = false;
                    $funcArr['RESTITUISCI'] = false;
                    $funcArr['RIAPRI'] = false;
                    return $funcArr;
                    /*
                     * Assegnata a soggetto corrente
                     * 
                     */
                } else if ($curr_assegnazione['PRORPA'] == $profilo['COD_ANANOM']) {
                    /*
                     * Per assegnazione
                     * 
                     */
                    if ($curr_assegnazione['PROFIN']) {
                        $funcArr['ASSEGNA'] = false;
                        $funcArr['PRENDIINCARICO'] = false;
                        $funcArr['RESTITUISCI'] = false;
                        $funcArr['RIAPRI'] = false;
                        if ($curr_assegnazione['PROOPE'] == self::FUN_GEST_GEN || $curr_assegnazione['PROOPE'] == self::FUN_GEST_RES) {
                            $funcArr['RIAPRI'] = true;
                            return $funcArr;
                        }
                    } else {
                        if ($curr_assegnazione['PROOPE'] == self::FUN_GEST_ASS) {
                            $funcArr['ASSEGNA'] = true;
                            $funcArr['PRENDIINCARICO'] = false;
                            $funcArr['RESTITUISCI'] = true;
                            $funcArr['RIAPRI'] = false;
                            return $funcArr;
                            /*
                             * Da gestire
                             * 
                             */
                        } else if ($curr_assegnazione['PROOPE'] == self::FUN_GEST_GEN) {
                            $funcArr['ASSEGNA'] = false;
                            $funcArr['PRENDIINCARICO'] = true;
                            $funcArr['RESTITUISCI'] = true;
                            $funcArr['RIAPRI'] = false;
                            return $funcArr;
                        } else if ($curr_assegnazione['PROOPE'] == self::FUN_GEST_RES) {
                            $funcArr['ASSEGNA'] = true;
                            $funcArr['PRENDIINCARICO'] = true;
                            $funcArr['RESTITUISCI'] = false;
                            $funcArr['RIAPRI'] = false;
                            return $funcArr;
                        }
                    }
                    /*
                     * 
                     * 
                     */
                }
                /*
                 * Ci sono assgnazioni ma nessuna attiva
                 * 
                 */
            } else {
                $funcArr['ASSEGNA'] = false;
                $funcArr['PRENDIINCARICO'] = false;
                $funcArr['RESTITUISCI'] = false;
                return $funcArr;
            }
        }
    }

    static public function riapriCaricoAssegnazione($model, $rowidPasso, $profilo, $motivazione) {
        /*
         * Carico lib
         * 
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();
        /*
         * Leggo dati del passo da riaprire
         */
        $proges_rec = $praLib->GetProges($Codice);
        $propas_rec = $praLib->GetPropas($rowidPasso, 'rowid');
        if (!$propas_rec) {
            Out::msgStop("Errore in riapertura presa in carico", "Lettura passo da gestire fallita.");
            return false;
        }
        $propas_rec['PROINI'] = "";
        $propas_rec['PROFIN'] = "";
        $update_Info = "Oggetto: Aggiorno passo assegnazione per riapertura presa in carico" . $propas_rec['PROPAK'];
        if (!$model->updateRecord($PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
            Out::msgStop("Errore in Aggiornamento", "Chiusura passo assegnazione fallito.");
            return false;
        }
        return true;
    }

    static public function prendiInCaricoAssegnazione($model, $Codice, $profilo, $motivazione) {
        /*
         * Carico lib
         * 
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();

        /*
         * Trovo l'assegnazione corrente della pratica
         * 
         */
        $curr_assegnazione = self::getCurrAssegnazione($Codice, $profilo);

        /*
         * Leggo dati da assegnazione corrente
         */
        $proges_rec = $praLib->GetProges($Codice);
        $propas_rec = $praLib->GetPropas($curr_assegnazione['ROWID'], 'rowid');
        if (!$propas_rec) {
            Out::msgStop("Errore in presa in carico", "Lettura passo da gestire fallita.");
            return false;
        }
        $propas_rec['PROINI'] = date('Ymd');
        $propas_rec['PROFIN'] = date('Ymd');
        $update_Info = "Oggetto: Aggiorno passo assegnazione per presa in carico" . $propas_rec['PROPAK'];
        if (!$model->updateRecord($PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
            Out::msgStop("Errore in Aggiornamento", "Chiusura passo assegnazione fallito.");
            return false;
        }

        return true;
    }

    static public function annullaInCaricoAssegnazione($model, $Codice, $idPasso, $profilo, $motivazione) {
        /*
         * Carico lib
         * 
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();

        /*
         * Leggo dati da assegnazione corrente
         */
        $proges_rec = $praLib->GetProges($Codice);
        $propas_rec = $praLib->GetPropas($idPasso, 'rowid');
        if (!$propas_rec) {
            Out::msgStop("Errore in annullamento presa in carico", "Lettura passo da gestire fallita.");
            return false;
        }
        $propas_rec['PROINI'] = '';
        $propas_rec['PROFIN'] = '';
        $update_Info = "Oggetto: Aggiorno passo annullamento per presa in carico" . $propas_rec['PROPAK'];
        if (!$model->updateRecord($PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
            Out::msgStop("Errore in Aggiornamento", "Chiusura passo assegnazione fallito.");
            return false;
        }

        return true;
    }

    static public function restituisciAssegnazione($model, $Codice, $profilo, $motivazione) {
        /*
         * Carico lib
         * 
         */
        include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';
        $praLib = new praLib();
        $PRAM_DB = $praLib->getPRAMDB();

        /*
         * Trovo l'assegnazione corrente della pratica
         * 
         */
        $curr_assegnazione = self::getCurrAssegnazione($Codice, $profilo);


        /*
         * Leggo dati da assegnazione corrente
         */
        $proges_rec = $praLib->GetProges($Codice);
        $propas_rec = $praLib->GetPropas($curr_assegnazione['ROWID'], 'rowid');
        if (!$propas_rec) {
            Out::msgStop("Errore in restituzione", "Lettura passo da restituire fallita.");
            return false;
        }
        $nomeUtente = $propas_rec['PROUTEADD'];
        $mittente = proSoggetto::getProfileFromNomeUtente($nomeUtente);
        $codMittente = $mittente['COD_ANANOM'];
        if (!$mittente) {
            Out::msgStop("Errore in restituzione", "Lettura mittente passo da restituire fallita.");
            return false;
        }
        $ananom_rec = $praLib->GetAnanom($codMittente);
        if (!$ananom_rec) {
            Out::msgStop("Errore in restituzione", "Lettura dati mittente passo da restituire fallita.");
            return false;
        }
        $praclt_rec = $praLib->GetPraclt($propas_rec['PROCLT']);
        if (!$praclt_rec) {
            Out::msgStop("Errore in restituzione", "Lettura tipo passo da restituire restituire fallita.");
            return false;
        }


        $propas_rec['PROINI'] = date('Ymd');
        $propas_rec['PROFIN'] = date('Ymd');

        $update_Info = "Oggetto: Aggiorno passo assegnazione per restituzione" . $propas_rec['PROPAK'];
        if (!$model->updateRecord($PRAM_DB, 'PROPAS', $propas_rec, $update_Info)) {
            Out::msgStop("Errore in Aggiornamento", "Chiusura passo assegnazione fallito.");
            return false;
        }

        //
        //Inserisco Nuovo Passo Gestione/Assegnazione
        //
        $pratica = substr($Codice, 4) . "/" . substr($Codice, 0, 4);
        $propas_new_rec = array();
        $propas_new_rec['PRONUM'] = $Codice;
        $propas_new_rec['PROPRO'] = $proges_rec['GESPRO'];
        $propas_new_rec['PRORES'] = $ananom_rec['NOMRES'];
        $propas_new_rec['PROSEQ'] = 9999;
        $propas_new_rec['PRORPA'] = $ananom_rec['NOMRES'];
        $propas_new_rec['PROUOP'] = $ananom_rec['NOMOPE'];
        $propas_new_rec['PROSET'] = $ananom_rec['NOMSET'];
        $propas_new_rec['PROSER'] = $ananom_rec['NOMSER'];
        $propas_new_rec['PRODPA'] = "RESTITUZIONE PRATICA N." . $pratica . " - " . $motivazione;
        $propas_new_rec['PROCLT'] = $propas_rec['PROCLT'];
        $propas_new_rec['PROOPE'] = praFunzionePassi::FUN_GEST_RES; //$propas_rec['PROOPE'];
        $propas_new_rec['PRODTP'] = $praclt_rec['CLTDES'];
        $propas_new_rec['PROPAK'] = $praLib->PropakGenerator($Codice);
        $propas_new_rec['PROUTEADD'] = $propas_new_rec['PROUTEEDIT'] = App::$utente->getKey('nomeUtente');
        $propas_new_rec['PRODATEADD'] = $propas_new_rec['PRODATEEDIT'] = date("d/m/Y") . " " . date("H:i:s");
        $insert_Info = "Oggetto: Inserisco passo " . $praclt_rec['CLTCOD'] . "-" . $praclt_rec['CLTDES'] . "  alla pratica " . $Codice;
        if (!$model->insertRecord($PRAM_DB, 'PROPAS', $propas_new_rec, $insert_Info)) {
            Out::msgStop("Inserimento passo restituzione", "Inserimento data set PROPAS fallito");
            return false;
        }

        //
        //Ordino la sequnza dei passi dopo il nuvo inserito
        //
        if (!$praLib->ordinaPassi($Codice)) {
            Out::msgStop("Errore", "Errore nel riordinare i passi della pratica n. $Codice");
            return false;
        }
        return true;
    }

}

?>
