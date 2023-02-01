<?php

/**
 *
 * LIBRERIA PER PERMESSI APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    03.03.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';

class praPerms {

    private $praLib;
    private $accLib;

    function __construct() {
        $this->praLib = new praLib();
        $this->accLib = new accLib();
    }

    //Filtro i passi per gli utenti senza permessi
    public function filtraPassiView($passi) {
        foreach ($passi as $keyPasso => $passo) {
            if ($passo['PROVISIBILITA'] == "Privato") {
                //if($passo['PROUTEADD'] != "" && $passo['PROUTEADD'] != App::$utente->getKey('nomeUtente')) {
                if ($passo['PROUTEEDIT'] != "" && $passo['PROUTEEDIT'] != App::$utente->getKey('nomeUtente')) {
                    if ($this->CheckResponsabile($passo['PRORPA'])) {
                        unset($passi[$keyPasso]);
                        //New
                        $sql = "SELECT * FROM PROPAS WHERE PROKPRE=" . $passo['PROPAK'];
                        $Propas_tab = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql);
                        foreach ($passi as $PassoKey => $passo) {
                            foreach ($Propas_tab as $ProKey => $Propas_rec) {
                                if ($passo['ROWID'] == $Propas_rec['ROWID']) {
                                    unset($passi[$PassoKey]);
                                }
                            }
                        }
                        //
                    }
                }
            }
        }
        return $passi;
    }

    //Filtro i dati aggiuntivi per gli utenti senza permessi
    public function filtraDatiAggView($passi, $dati) {
        foreach ($passi as $keyPasso => $passo) {
            if ($passo['PROVISIBILITA'] == "Privato") {
                //if($passo['PROUTEADD'] != "" && $passo['PROUTEADD'] != App::$utente->getKey('nomeUtente')) {
                if ($passo['PROUTEEDIT'] != "" && $passo['PROUTEEDIT'] != App::$utente->getKey('nomeUtente')) {
                    if ($this->CheckResponsabile($passo['PRORPA'])) {
                        foreach ($dati as $keyDag => $dato) {
                            if (strcmp($dati[$keyDag]['DAGPAK'], $passi[$keyPasso]['PROPAK']) == 0) {
                                unset($dati[$keyDag]);
                            }
                        }
                    }
                }
            }
        }
        return $dati;
    }

    //Filtro gli allegati per gli utenti senza permessi
    public function filtraAllegatiView($passi, $allegati) {
        foreach ($passi as $keyPasso => $passo) {
            if ($passo['PROVISIBILITA'] == "Privato") {
                //if($passo['PROUTEADD'] != "" && $passo['PROUTEADD'] != App::$utente->getKey('nomeUtente')) {
                if ($passo['PROUTEEDIT'] != "" && $passo['PROUTEEDIT'] != App::$utente->getKey('nomeUtente')) {
                    if ($this->CheckResponsabile($passo['PRORPA'])) {
//                        $j = count($allegati);
//                        for ($i = 0; $i < $j; $i++) {
//                            if (strcmp($allegati[$i]['SEQ'], $passi[$keyPasso]['PROPAK']) == 0) {
//                                unset($allegati[$i]);
//                            }
//                            if (strcmp($allegati[$i]['parent'], $passi[$keyPasso]['PROPAK']) == 0) {
//                                unset($allegati[$i]);
//                            }
//                        }
                        //
                        // Rimuovo padre allegati
                        //
                        foreach ($allegati as $keyPadre => $allegato) {
                            if (strcmp($allegato['SEQ'], $passo['PROPAK']) == 0) {
                                unset($allegati[$keyPadre]);
                            }
                        }

                        //
                        // Rimuovo allegati foglie
                        //
                        foreach ($allegati as $keyAlle => $allegato) {
                            if (strcmp($allegato['parent'], $passo['PROPAK']) == 0) {
                                unset($allegati[$keyAlle]);
                            }
                        }
                    }
                }
            }
        }
        return $allegati;
    }

    //Filtro le comunicazioni per gli utenti senza permessi
    public function filtraComunicazioniView($passi, $comunicazioni_tab) {
        foreach ($passi as $keyPasso => $passo) {
            $partenza_rec = $padre_com = array();
            if ($passo['PROVISIBILITA'] == "Privato") {
                if ($passo['PROUTEEDIT'] != "" && $passo['PROUTEEDIT'] != App::$utente->getKey('nomeUtente')) {
                    if ($this->CheckResponsabile($passo['PRORPA'])) {

                        //
                        // Rimuovo il padre
                        //
                        foreach ($comunicazioni_tab as $keyComPadre => $comunicazioni_rec) {
                            if (strcmp($comunicazioni_rec['SEQ'], $passi[$keyPasso]['PROPAK']) == 0) {
                                $padre_com = $comunicazioni_tab[$keyComPadre];
                                unset($comunicazioni_tab[$keyComPadre]);
                            }
                        }

                        //
                        //Rimuovo la partenza
                        //
                        foreach ($comunicazioni_tab as $keyComPartenza => $comunicazioni_rec) {
                            if (strcmp($padre_com['SEQ'], $comunicazioni_rec['parent']) == 0) {
                                $partenza_rec = $comunicazioni_tab[$keyComPartenza];
                                unset($comunicazioni_tab[$keyComPartenza]);
                            }
                        }

                        //
                        //Rimuovo l'arrivo
                        //
                        foreach ($comunicazioni_tab as $keyComArrivo => $comunicazioni_rec) {
                            if (strpos($comunicazioni_rec['DESTINATARIO'], "ARRIVO") !== false) {
                                if (strcmp($partenza_rec['SEQ'], $comunicazioni_rec['parent']) == 0) {
                                    unset($comunicazioni_tab[$keyComArrivo]);
                                }
                            }
                        }
                    }
                }
            }
        }
        return $comunicazioni_tab;
    }

    //Controllo se l'utente loggato è il responsabile del passo
    public function CheckResponsabile($codiceResp) {
        if ($codiceResp) {
            $nascondi = false;
            $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
            if ($Utenti_rec) {
                if ($Utenti_rec['UTEANA__3'] != $codiceResp) {
                    $nascondi = true;
                }
            }
        }
        return $nascondi;
    }

    // Imposto i permessi del passo
    public function impostaPermessiPasso($propas_rec) {
        //if ($propas_rec['PROVISIBILITA'] == "Protetto" || $propas_rec['PROVISIBILITA'] == "") {
        if ($propas_rec['PROVISIBILITA'] == "Protetto") {
            //if($propas_rec['PROUTEADD'] != "" && $propas_rec['PROUTEADD'] != App::$utente->getKey('nomeUtente')) {
            //if ($propas_rec['PROUTEEDIT'] != "" && $propas_rec['PROUTEEDIT'] != App::$utente->getKey('nomeUtente')) {
            if (($propas_rec['PROUTEADD'] != "" && $propas_rec['PROUTEADD'] != App::$utente->getKey('nomeUtente')) && ($propas_rec['PROUTEEDIT'] != "" && $propas_rec['PROUTEEDIT'] != App::$utente->getKey('nomeUtente'))) {
                if ($this->CheckResponsabile($propas_rec['PRORPA'])) {
                    $perms['noEdit'] = "1";
                    $perms['noDelete'] = "1";
                }
            }
        } else {
            $perms['noEdit'] = "";
            $perms['noDelete'] = "";
        }
        return $perms;
    }

    public function checkUtenteGenerico($passo) {
        if ($passo['PROUTEEDIT'] == "@ADMIN@") {
            return true;
        }
        //if($passo['PROUTEADD'] != "" && $passo['PROUTEADD'] != App::$utente->getKey('nomeUtente')) {
        if ($passo['PROUTEEDIT'] != "" && $passo['PROUTEEDIT'] != App::$utente->getKey('nomeUtente')) {
            if ($this->CheckResponsabile($passo['PRORPA'])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Controlla se l'utente è un super admin di sportello
     * 
     * @param type $proges_rec
     * @return boolean
     */
    public function checkSuperUser($proges_rec) {
        if ($proges_rec) {
            $Utenti_rec = $this->accLib->GetUtenti(App::$utente->getKey('idUtente'));
            if ($proges_rec['GESTSP'] != 0) {
                $recordSportello = $this->praLib->GetAnatsp($proges_rec['GESTSP']);
                $gruppoSuperAdmin = str_pad($recordSportello['TSPSUPERADMIN'], 10, '0', STR_PAD_LEFT);
            } elseif ($proges_rec['GESSPA'] != 0) {
                $recordSportello = $this->praLib->GetAnaspa($proges_rec['GESSPA']);
                $gruppoSuperAdmin = str_pad($recordSportello['SPASUPERADMIN'], 10, '0', STR_PAD_LEFT);
            }
            if ($recordSportello['TSPSUPERADMIN'] != 0 || $recordSportello['SPASUPERADMIN'] != 0) {
                if ($Utenti_rec["UTEGRU"] != 0) {
                    $utegru = str_pad($Utenti_rec["UTEGRU"], 10, '0', STR_PAD_LEFT);
                    if ($utegru == $gruppoSuperAdmin) {
                        return true;
                    }
                }
                for ($i = 1; $i <= 30; $i++) {
                    if ($Utenti_rec["UTEGEX__$i"] != 0) {
                        $gruppo = str_pad($Utenti_rec["UTEGEX__$i"], 10, '0', STR_PAD_LEFT);
                        if ($gruppo == $gruppoSuperAdmin) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

}

?>