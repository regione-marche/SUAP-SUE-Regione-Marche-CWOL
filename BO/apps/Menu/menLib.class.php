<?php
include_once ITA_LIB_PATH . '/Cache/CacheFactory.class.php';

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of menLib
 *
 * @author michele
 */
class menLib {

    public $ITALSOFT_DB;
    public $ITALWEB_DB;
    public $iconaCons = '<span class="ita-icon ita-icon-check-green-16x16"></span>';
    public $iconaNega = '<span class="ita-icon ita-icon-check-red-16x16"></span>';
    public $defaultVis;
    public $defaultAcc;
    public $defaultMod;
    public $defaultIns;
    public $defaultDel;
    public $gestorePermessi = 'menAuthConfig_gruppo';
    public $utenteAttuale;
    
    private $programmi;
    private $tmpMenuIni = array();

    function __construct() {
        $this->ITALSOFT_DB = $this->getItalsoft();
        $this->ITALWEB_DB = $this->getItalweb();
        $this->defaultVis = App::getConf("Menu.visibilityDefault");
        $this->defaultAcc = App::getConf("Menu.accessoDefault");
        $this->defaultMod = App::getConf("Menu.modificaDefault");
        $this->defaultIns = App::getConf("Menu.inserimentoDefault");
        $this->defaultDel = App::getConf("Menu.cancellaDefault");
        $this->utenteAttuale = App::$utente->getIdUtente();
    }

    public function getItalsoft() {
        if (!$this->ITALSOFT_DB) {
            try {
                $this->ITALSOFT_DB = ItaDB::DBOpen('italsoft', '');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALSOFT_DB;
    }

    public function getItalweb() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    public function getIniPath() {
        return ITA_BASE_PATH . "/apps/Menu/resources";
    }

    static public function saveLastProg($param) {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }

        if (App::$clientEngine == 'itaMobile') {
            $context = "itaMobile";
        } else {
            $context = "";
        }

        $sql = "SELECT * FROM MEN_RECENTI WHERE UTECOD = '" . App::$utente->getIdUtente() . "' AND MENU='" . $param['MENU'] . "' AND PROG='" . $param['PROG'] . "' AND CONTEXT = '$context'";
        $Men_Recenti_rec = ItaDB::DBSQLSelect($ITALWEB_DB, $sql, false);
        if ($Men_Recenti_rec) {
            $Men_Recenti_rec['TEMPO'] = time();
            $nRows = ItaDB::DBUpdate($ITALWEB_DB, 'MEN_RECENTI', 'ROWID', $Men_Recenti_rec);
        } else {
            $Men_Recenti = array();
            $Men_Recenti_rec['UTECOD'] = App::$utente->getIdUtente();
            $Men_Recenti_rec['TEMPO'] = time();
            $Men_Recenti_rec['MENU'] = $param['MENU'];
            $Men_Recenti_rec['PROG'] = $param['PROG'];
            $Men_Recenti_rec['CONTEXT'] = $context;
            $nRows = ItaDB::DBInsert($ITALWEB_DB, 'MEN_RECENTI', 'ROWID', $Men_Recenti_rec);
        }

        $sql = "SELECT * FROM MEN_FREQUENTI WHERE FR_MENU='" . $param['MENU'] . "' AND FR_PROG='" . $param['PROG'] . "'";
        $Men_frequenti_rec = ItaDB::DBSQLSelect($ITALWEB_DB, $sql, false);
        if ($Men_frequenti_rec) {
            $Men_frequenti_rec['FR_QUANTE'] += 1;
            $nRows = ItaDB::DBUpdate($ITALWEB_DB, 'MEN_FREQUENTI', 'ROWID', $Men_frequenti_rec);
        } else {
            $Men_frequenti = array();
            $Men_frequenti_rec['FR_UTECOD'] = App::$utente->getIdUtente();
            $Men_frequenti_rec['FR_QUANTE'] = 1;
            $Men_frequenti_rec['FR_MENU'] = $param['MENU'];
            $Men_frequenti_rec['FR_PROG'] = $param['PROG'];
            $nRows = ItaDB::DBInsert($ITALWEB_DB, 'MEN_FREQUENTI', 'ROWID', $Men_frequenti_rec);
        }
    }

    static public function saveBookMark($param, $delete = false) {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $utecod = App::$utente->getIdUtente();
        $sql = "SELECT * FROM MEN_PREFERITI WHERE PR_MENU='" . $param['MENU'] . "' AND PR_PROG='" . $param['PROG'] . "' AND PR_UTECOD='$utecod'";
        $Men_Preferiti_rec = ItaDB::DBSQLSelect($ITALWEB_DB, $sql, false);
        if ($delete == false) {
            if (!$Men_Preferiti_rec) {
                $Men_Preferiti_rec = array();
                $Men_Preferiti_rec['PR_UTECOD'] = App::$utente->getIdUtente();
                $Men_Preferiti_rec['PR_MENU'] = $param['MENU'];
                $Men_Preferiti_rec['PR_PROG'] = $param['PROG'];
                $nRows = ItaDB::DBInsert($ITALWEB_DB, 'MEN_PREFERITI', 'ROWID', $Men_Preferiti_rec);
            }
        } else if ($delete == true) {
            if ($Men_Preferiti_rec) {
                $nRows = ItaDB::DBDelete($ITALWEB_DB, 'MEN_PREFERITI', 'ROWID', $Men_Preferiti_rec['ROWID']);
            }
        }
    }

    static public function loadRecent($limit = 10) {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }

        if (App::$clientEngine == 'itaMobile') {
            $sql = "SELECT * FROM MEN_RECENTI WHERE UTECOD=" . App::$utente->getIdUtente() . " AND CONTEXT = 'itaMobile' ORDER BY TEMPO DESC LIMIT $limit";
        } else {
            $sql = "SELECT * FROM MEN_RECENTI WHERE UTECOD=" . App::$utente->getIdUtente() . " AND CONTEXT = '' ORDER BY TEMPO DESC LIMIT $limit";
        }
        return ItaDB::DBSQLSelect($ITALWEB_DB, $sql, true);
    }

    static public function loadFrequent() {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $sql = "SELECT * FROM MEN_FREQUENTI WHERE FR_UTECOD=" . App::$utente->getIdUtente() . " ORDER BY FR_QUANTE DESC";
        return ItaDB::DBSQLSelect($ITALWEB_DB, $sql, true);
    }

    static public function loadBookMark() {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $sql = "SELECT * FROM MEN_PREFERITI WHERE PR_UTECOD=" . App::$utente->getIdUtente();
        return ItaDB::DBSQLSelect($ITALWEB_DB, $sql, true);
    }

    static public function getRecent($rowid) {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $sql = "SELECT * FROM MEN_RECENTI WHERE ROWID = $rowid";
        return ItaDB::DBSQLSelect($ITALWEB_DB, $sql, false);
    }

    static public function getPreferiti($rowid) {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $sql = "SELECT * FROM MEN_PREFERITI WHERE ROWID = $rowid";
        return ItaDB::DBSQLSelect($ITALWEB_DB, $sql, false);
    }

    static public function getFrequenti($rowid) {
        try {
            $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $sql = "SELECT * FROM MEN_FREQUENTI WHERE ROWID = $rowid";
        return ItaDB::DBSQLSelect($ITALWEB_DB, $sql, false);
    }

//----------------------------------------------------------------------------\\    

    /**
     *  Acquisisce dati da database e restituisce un albero di menu partendo
     *   dalla radice passata
     * @param String $root Radice
     * @param Bool $only_menu true = restituisce solo i menu, altrimenti anche 
     *                        i programmi
     * @param String $return_model Modello di ritorno
     * @return Array Un array in formato 'albero' 
     */
    public function getMenu($root = 'TI_MEN', $only_menu = false, $gruppo = '', $return_model = 'adjacency', $filtro = true) {
        $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $root . "'";
        $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        if (!$Ita_menu_rec) {
            Out::msgInfo("Attenzione", "Menu inesistente");
            return;
        }

        $sql = "SELECT * FROM ita_puntimenu WHERE pm_voce = '" . $root . "' AND me_id = " . $Ita_menu_rec['me_id'];
        $Ita_puntimenu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        $pm_id = $Ita_puntimenu_rec['pm_id'];
        if (!$pm_id) {
            $pm_d = -1;
        }

        $pm_descrizione = $Ita_menu_rec['me_descrizione'];

        $me_descrizione = $Ita_menu_rec['me_descrizione'];
        $chiave = $Ita_menu_rec['me_id'];

        $inc = 0;
        $albero = array();
        $albero[$inc]['INDICE'] = $inc;

        $albero[$inc]['PER_FLAGVIS'] = menLib::decodeFlag();
        $albero[$inc]['PER_FLAGACC'] = menLib::decodeFlag();
        $albero[$inc]['PER_FLAGEDT'] = menLib::decodeFlag();
        $albero[$inc]['PER_FLAGINS'] = menLib::decodeFlag();
        $albero[$inc]['PER_FLAGDEL'] = menLib::decodeFlag();

        if (!$filtro) {
            if (!$Ita_puntimenu_rec) {
                $pm_id = -1;  // Verrà usato nel salvataggio dei permessi
                $pm_descrizione = $me_descrizione;
                $albero[$inc]['PER_FLAGVIS'] = menLib::decodeFlag(1);
                $albero[$inc]['PER_FLAGACC'] = menLib::decodeFlag(1);
                $albero[$inc]['PER_FLAGEDT'] = menLib::decodeFlag(1);
                $albero[$inc]['PER_FLAGINS'] = menLib::decodeFlag(1);
                $albero[$inc]['PER_FLAGDEL'] = menLib::decodeFlag(1);
                $albero[$inc]['iconaVis'] = $this->iconaCons;
                $albero[$inc]['iconaAcc'] = $this->iconaCons;
                $albero[$inc]['iconaEdt'] = $this->iconaCons;
                $albero[$inc]['iconaIns'] = $this->iconaCons;
                $albero[$inc]['iconaDel'] = $this->iconaCons;
            } else {
                $pm_id = $Ita_puntimenu_rec['pm_id'];
                $pm_descrizione = $Ita_puntimenu_rec['pm_descrizione'];

                $sql = "SELECT * FROM MEN_PERMESSI WHERE PER_GRU = '" . $gruppo . "' AND PER_MEN = '" . $Ita_menu_root_rec['me_menu'] . "' AND PER_VME = '" . $Ita_puntimenu_rec['pm_voce'] . "'";
                $Men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
                if ($Men_permessi_rec) {
                    $albero[$inc]['PER_FLAGVIS'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGVIS']);
                    $albero[$inc]['PER_FLAGACC'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGACC']);
                    $albero[$inc]['PER_FLAGEDT'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGEDT']);
                    $albero[$inc]['PER_FLAGINS'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGINS']);
                    $albero[$inc]['PER_FLAGDEL'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGDEL']);
                }
                $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGVIS', $this->defaultVis);
                $albero[$inc]['iconaVis'] = $this->getIcona($privilegio);
                $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGACC', $this->defaultAcc);
                $albero[$inc]['iconaAcc'] = $this->getIcona($privilegio);
                $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGEDT', $this->defaultMod);
                $albero[$inc]['iconaEdt'] = $this->getIcona($privilegio);
                $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGINS', $this->defaultIns);
                $albero[$inc]['iconaIns'] = $this->getIcona($privilegio);
                $privilegio = $this->menLib->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGDEL', $this->defaultDel);
                $albero[$inc]['iconaDel'] = $this->getIcona($privilegio);
            }
        }

        $albero[$inc]['pm_voce'] = $root;
        $albero[$inc]['me_id'] = $chiave;
        $albero[$inc]['pm_id'] = $pm_id;
        $albero[$inc]['pm_descrizione'] = $pm_descrizione;
        $albero[$inc]['pm_sequenza'] = 0;
        $albero[$inc]['level'] = 0;
        $albero[$inc]['parent'] = NULL;
        $albero[$inc]['isLeaf'] = 'false';
        $albero[$inc]['expanded'] = 'true';
        $albero[$inc]['loaded'] = 'true';
        $save_count = count($albero);
        $albero = $this->caricaTreeLegami($chiave, $albero, 1, $inc, $only_menu, $filtro);
        if ($save_count == count($albero)) {
            $albero[$inc]['isLeaf'] = 'true';
        }
        return $albero;
    }

    /**
     * Acquisisce dati da database e restituisce un albero di menu partendo
     * dalla radice passata
     * 
     * @param type $root Radice
     * @param type $only_menu Restituisce solo i menu se true
     * @param type $gruppo
     * @param type $return_model
     * @param type $filtro Filtra o meno i menù da visualizzare
     * @param integer $limit Numero di livelli da caricare (-1 = tutti)
     * @param type $level USO INTERNO, non valorizzare
     * @param type $index USO INTERNO, non valorizzare
     * @param type $parent USO INTERNO
     * @return type
     */
    public function getMenu_ini($root = 'TI_MEN', $only_menu = false, $gruppo = '', $return_model = 'adjacency', $filtro = true, $limit = -1, $level = 0, $index = 0, $parent = null) {
        $data = array();

        if ($limit === 0) {
            return $data;
        } else if ($limit > 0) {
            $limit--;
        }

        $root_index = $index;

        if ($parent) {
            $root_index = $parent;
        }

        if ($level == 0) {
            $menu = $this->GetIta_menu_ini($root);

            $data[$index] = array(
                'INDICE' => $index,
                'pm_id' => $index,
                'level' => strval($level),
                'parent' => '',
                'isLeaf' => 'false',
                'expanded' => 'true',
                'loaded' => 'true',
                'pm_descrizione' => $menu['me_descrizione'],
                'pm_voce' => $root
            );

            if (!$filtro) {
                $data[$index] = array_merge($data[$index], array(
                    'PER_FLAGVIS' => menLib::decodeFlag(1),
                    'PER_FLAGACC' => menLib::decodeFlag(1),
                    'PER_FLAGEDT' => menLib::decodeFlag(1),
                    'PER_FLAGINS' => menLib::decodeFlag(1),
                    'PER_FLAGDEL' => menLib::decodeFlag(1),
                    'iconaVis' => $this->iconaCons,
                    'iconaAcc' => $this->iconaCons,
                    'iconaEdt' => $this->iconaCons,
                    'iconaIns' => $this->iconaCons,
                    'iconaDel' => $this->iconaCons
                ));
            }
        }

        $index++;

//        $punti = $this->GetIta_puntimenu_ini($root);
        $punti = $filtro ? $this->menuFiltrato_ini($root) : $this->GetIta_puntimenu_ini($root);

        foreach ($punti as $punto) {
            $is_menu = $punto['pm_categoria'] == 'ME' ? true : false;

            if ($only_menu && !$is_menu) {
                continue;
            }

            $data[$index] = array_merge($punto, array(
                'INDICE' => $index,
                'pm_id' => $index,
                'level' => strval($level + 1),
                'parent' => strval($root_index),
                'isLeaf' => $is_menu ? 'false' : 'true',
                'expanded' => 'false',
                'loaded' => 'true',
                'pm_descrizione' => $punto['pm_descrizione'],
                'pm_voce' => $punto['pm_voce'],
                'me_voce' => $root
            ));

            if (!$filtro) {
                $Men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, "SELECT * FROM MEN_PERMESSI WHERE PER_GRU = '" . $gruppo . "' AND PER_MEN = '" . $root . "' AND PER_VME = '" . $punto['pm_voce'] . "'", false);

                $data[$index] = array_merge($data[$index], array(
                    'PER_FLAGVIS' => $Men_permessi_rec ? menLib::decodeFlag($Men_permessi_rec['PER_FLAGVIS']) : menLib::decodeFlag(),
                    'PER_FLAGACC' => $Men_permessi_rec ? menLib::decodeFlag($Men_permessi_rec['PER_FLAGACC']) : menLib::decodeFlag(),
                    'PER_FLAGEDT' => $Men_permessi_rec ? menLib::decodeFlag($Men_permessi_rec['PER_FLAGEDT']) : menLib::decodeFlag(),
                    'PER_FLAGINS' => $Men_permessi_rec ? menLib::decodeFlag($Men_permessi_rec['PER_FLAGINS']) : menLib::decodeFlag(),
                    'PER_FLAGDEL' => $Men_permessi_rec ? menLib::decodeFlag($Men_permessi_rec['PER_FLAGDEL']) : menLib::decodeFlag(),
                    'iconaVis' => $this->getIcona($this->privilegiPuntoMenu($root, $punto, array($gruppo), 'PER_FLAGVIS', $this->defaultVis)),
                    'iconaAcc' => $this->getIcona($this->privilegiPuntoMenu($root, $punto, array($gruppo), 'PER_FLAGACC', $this->defaultAcc)),
                    'iconaEdt' => $this->getIcona($this->privilegiPuntoMenu($root, $punto, array($gruppo), 'PER_FLAGEDT', $this->defaultMod)),
                    'iconaIns' => $this->getIcona($this->privilegiPuntoMenu($root, $punto, array($gruppo), 'PER_FLAGINS', $this->defaultIns)),
                    'iconaDel' => $this->getIcona($this->privilegiPuntoMenu($root, $punto, array($gruppo), 'PER_FLAGDEL', $this->defaultDel))
                ));
            }

            if ($is_menu) {
                $ret = $this->getMenu_ini($punto['pm_voce'], $only_menu, $gruppo, $return_model, $filtro, $limit, $level + 1, $index);

                if (count($ret) > 0) {
                    $data[$index]['isLeaf'] = 'false';

                    if (!$filtro) {
                        $data[$index]['pm_descrizione'] = '<span style="font-weight: bold; color: darkred;">' . $data[$index]['pm_descrizione'] . '</span>';
                    }

                    $data = array_merge($data, $ret);
                    $index += count($ret) + 1;
                } else {
                    $index++;
                }
            } else {
                $index++;
            }
        }

        return $data;
    }

    /**
     *  Funzione ricorsiva per acquisire l'albero di menu
     * @param Int $chiave L'me_id da cercare su ita_puntimenu
     * @param Array $albero L'albero fino ad ora sviluppato
     * @param Int $level Livello di profondita
     * @param Int $indice Indice del padre (nell'array)
     * @param Bool $only_menu Se prendo solo i menu oppure anche i programmi
     * @param Bool $filtro Decide se applicare o meno il filtro permessi
     * @return Array L'albero di input con i risultati aggiunti 
     */
    public function caricaTreeLegami($chiave, $albero, $level, $indice, $only_menu = false, $filtro = true) {
        if ($level == 10) {
            return $albero;
        }

        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = '" . $chiave . "' ORDER BY pm_sequenza";
        $Ita_puntimenu_tab = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
        if ($Ita_puntimenu_tab) {
            foreach ($Ita_puntimenu_tab as $i => $Ita_puntimenu_rec) {
                if ($only_menu && $Ita_puntimenu_rec['pm_categoria'] != 'ME') {
                    continue;
                }

                $inc = count($albero);
                $albero[$inc] = $Ita_puntimenu_rec;
                $albero[$inc]['INDICE'] = $inc;
                $albero[$inc]['level'] = $level;
                $albero[$inc]['parent'] = $indice;
                $albero[$inc]['expanded'] = 'false';
                $albero[$inc]['loaded'] = 'false';
                $albero[$inc]['isLeaf'] = 'true';

                // Aquisizione privilegi
                $sql = "SELECT * FROM ita_menu WHERE me_id = '" . $chiave . "'";
                $Ita_menu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);

                if ($filtro) {
                    // Se è un menu, verifico se esiste effettivamente
                    if ($Ita_puntimenu_rec['pm_categoria'] == 'ME') {
                        $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $Ita_puntimenu_rec['pm_voce'] . "'";
                        $Ita_menu_giu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
                        if (!$Ita_menu_giu_rec) {
                            unset($albero[$inc]);
                            continue;
                        }
                    }

                    $utente = $this->utenteAttuale;
                    $gruppi = $this->getGruppi($utente);
                    $defaultVis = App::getConf("Menu.visibilityDefault");
                    $privilegio = $this->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, $gruppi, 'PER_FLAGVIS', $defaultVis);
                    if (!$privilegio) {
                        unset($albero[$inc]);
                        continue;
                    }
                } else {  // Sto amministrando tramite menAuthConfig!!!!!
                    $gruppo = isset($_POST[$this->gestorePermessi]) ? $_POST[$this->gestorePermessi] : $_POST['menAuthConfigIni_gruppo'];
                    $sql = "SELECT * FROM MEN_PERMESSI WHERE PER_GRU = '" . $gruppo . "' AND PER_MEN = '" . $Ita_menu_rec['me_menu'] . "' AND PER_VME = '" . $Ita_puntimenu_rec['pm_voce'] . "'";
                    $Men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
                    if ($Men_permessi_rec) {
                        $albero[$inc]['PER_FLAGVIS'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGVIS']);
                        $albero[$inc]['PER_FLAGACC'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGACC']);
                        $albero[$inc]['PER_FLAGEDT'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGEDT']);
                        $albero[$inc]['PER_FLAGINS'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGINS']);
                        $albero[$inc]['PER_FLAGDEL'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGDEL']);
                    } else {
                        $albero[$inc]['PER_FLAGVIS'] = menLib::decodeFlag();
                        $albero[$inc]['PER_FLAGACC'] = menLib::decodeFlag();
                        $albero[$inc]['PER_FLAGEDT'] = menLib::decodeFlag();
                        $albero[$inc]['PER_FLAGINS'] = menLib::decodeFlag();
                        $albero[$inc]['PER_FLAGDEL'] = menLib::decodeFlag();
                    }

                    // Icone per griglie 'permessi menu'
                    $privilegio = $this->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGVIS', $this->defaultVis);
                    $albero[$inc]['iconaVis'] = $this->getIcona($privilegio);
                    $privilegio = $this->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGACC', $this->defaultAcc);
                    $albero[$inc]['iconaAcc'] = $this->getIcona($privilegio);
                    $privilegio = $this->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGEDT', $this->defaultMod);
                    $albero[$inc]['iconaEdt'] = $this->getIcona($privilegio);
                    $privilegio = $this->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGINS', $this->defaultIns);
                    $albero[$inc]['iconaIns'] = $this->getIcona($privilegio);
                    $privilegio = $this->privilegiPuntoMenu($Ita_menu_rec['me_menu'], $Ita_puntimenu_rec, array($gruppo), 'PER_FLAGDEL', $this->defaultDel);
                    $albero[$inc]['iconaDel'] = $this->getIcona($privilegio);
                }
                // Fine acquisizione privilegi

                if ($Ita_puntimenu_rec['pm_categoria'] == 'ME') {
                    $albero[$inc]['isLeaf'] = 'false';

                    $sql = "SELECT * FROM ita_menu WHERE me_menu = '" . $Ita_puntimenu_rec['pm_voce'] . "'";
                    $Ita_menu_giu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
                    $me_id = $Ita_menu_giu_rec['me_id'];

                    $save_count = count($albero);

                    $albero = $this->caricaTreeLegami($me_id, $albero, $level + 1, $inc, $only_menu, $filtro);
                    if ($save_count == count($albero)) {
                        $albero[$inc]['isLeaf'] = 'true';
                    } else {
                        if (!$filtro) {
                            $albero[$inc]['pm_descrizione'] = "<span style=\"font-weight:bold;color:darkred;\">" . $albero[$inc]['pm_descrizione'] . "</span>";
                        }
                    }
                }
            }
        }
        return $albero;
    }

    /**
     *  Dato un utente, restituisce un array di gruppi a cui appartiene
     * @param String $utente Nome utente
     * @return Array Elenco di gruppi 
     */
    public function getGruppi($utente) {
        try {
            $ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $Utenti_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD = '$utente'", false);
        $gruppi[0] = $Utenti_rec['UTEGRU'];
        $j = 1;
        for ($i = 1; $i <= 30; $i++) {
            if ($Utenti_rec["UTEGEX__$i"] != 0) {
                $gruppi[$j] = $Utenti_rec["UTEGEX__$i"];
                $j++;
            }
        }
        return $gruppi;
    }

    /**
     *  Dato un utente, restituisce un array di nomi di gruppi a cui appartiene
     * @param String $utente Nome utente
     * @return Array Elenco di nomi di gruppi 
     */
    public function getNomiGruppi($utente) {
        try {
            $ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $Utenti_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD = '$utente'", false);
        $Gruppi_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM GRUPPI WHERE GRUCOD = '" . $Utenti_rec['UTEGRU'] . "'", false);
        $gruppi[0] = $Utenti_rec['UTEGRU'];
        $gruppi[0] = $Gruppi_rec['GRUDES'];
        $j = 1;
        for ($i = 1; $i <= 30; $i++) {
            if ($Utenti_rec["UTEGEX__$i"] != 0) {
                $Gruppi_rec = ItaDB::DBSQLSelect($ITW_DB, "SELECT * FROM GRUPPI WHERE GRUCOD = '" . $Utenti_rec["UTEGEX__$i"] . "'", false);
//                $gruppi[$j] = $Utenti_rec["UTEGEX__$i"];
                $gruppi[$j] = $Gruppi_rec['GRUDES'];
                $j++;
            }
        }
        return $gruppi;
    }

    public function privilegiModel($model, $gruppi, $flag, $baseDefault) {
        $sql = "
            SELECT
                ita_menu.me_menu AS me_menu,
                ita_puntimenu.*
            FROM
                ita_puntimenu ita_puntimenu
            LEFT OUTER JOIN 
                ita_menu ita_menu ON ita_puntimenu.me_id=ita_menu.me_id 
            WHERE
                ita_puntimenu.pm_model = '$model' AND ita_puntimenu.pm_categoria = 'PR'";
        $Ita_puntimenu_tab = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
        $fl = false;
        foreach ($Ita_puntimenu_tab as $Ita_puntimenu_rec) {

            $fl = $fl | $this->privilegiPuntoMenu($Ita_puntimenu_rec['me_menu'], $Ita_puntimenu_rec, $gruppi, $flag, $baseDefault);
        }
        return $fl;
    }

    /**
     *  Restituisce un permesso di un punto menu
     * @param String $me_menu Il menu a cui appartiene
     * @param Array $Ita_puntimenu_rec Il record del punto menu
     * @param Array $gruppi I gruppi per i quali verificare i permessi
     * @param String $flag Il permesso da verificare (es: PER_FLAGVIS)
     * @param Int $baseDefault Il valore di default del flag (valore di variabile globale)
     * @return Bool true: consenti, flase: nega
     */
    public function privilegiPuntoMenu($me_menu, $Ita_puntimenu_rec, $gruppi, $flag, $baseDefault) {
        if ($flag == 'PER_FLAGVIS') {
            $voceVisDefault = $Ita_puntimenu_rec['pm_flagvis'];
            $baseDefault = ($voceVisDefault == -1 ) ? $baseDefault : $voceVisDefault;
        }
        $pm_voce = $Ita_puntimenu_rec['pm_voce'];
        foreach ($gruppi as $key => $gruppo) {
            $sql = "SELECT * FROM MEN_PERMESSI WHERE
                    PER_MEN = '$me_menu' AND PER_VME = '$pm_voce' AND PER_GRU = '$gruppo'";
            $Men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
            if ($Men_permessi_rec) {
                if ($Men_permessi_rec[$flag] == 1) {
                    return true;
                }
            } else {
                if ($baseDefault == 1) {
                    return true;
                }
            }
        }
        return false;
    }

    private function searchModels_ini($root, $model) {
        $pm_models = array();

        $ita_puntimenu_tab = $this->GetIta_puntimenu_ini($root);

        foreach ($ita_puntimenu_tab as $ita_puntimenu_rec) {
            if ($ita_puntimenu_rec['pm_categoria'] == 'PR' && $ita_puntimenu_rec['pm_model'] == $model) {
                $pm_models[] = $ita_puntimenu_rec;
            }

            if ($ita_puntimenu_rec['pm_categoria'] == 'ME') {
                $pm_sub_models = $this->searchModels_ini($ita_puntimenu_rec['pm_voce'], $model);
                $pm_models = array_merge($pm_models, $pm_sub_models);
            }
        }

        return $pm_models;
    }

    public function privilegiModel_ini($model, $gruppi, $flag, $baseDefault, $root = null) {
        if (!$root) {
            $root = $this->getRootMenu();
        }

        $Ita_puntimenu_tab = $this->searchModels_ini($root, $model);
        $fl = false;
        foreach ($Ita_puntimenu_tab as $Ita_puntimenu_rec) {
            $fl = $fl | $this->privilegiPuntoMenu($Ita_puntimenu_rec['me_menu'], $Ita_puntimenu_rec, $gruppi, $flag, $baseDefault);
        }
        return $fl;
    }

    /**
     * Ritorna il valore finale di ogni permesso per un dato model.
     * Il valore finale è '1' se almeno uno dei punti menu è '1'.
     * 
     * @param string $model Nome del model
     * @param array $gruppi Array dei gruppi per cui verificare i permessi
     * @param string $root Menu radice
     * @param array $defaultExtPermessi Array dei permessi di default che sovrascrive
     * quelli definiti nel config.ini
     * @return array
     */
    public function privilegiGlobaliModel_ini($model, $gruppi, $root = null, $defaultExtPermessi = array(), $noCache = false) {
        if (!$root) {
            $root = $this->getRootMenu();
        }

        $uid = md5($model . implode('|', $gruppi) . $root);
        $cache = CacheFactory::newCache(CacheFactory::TYPE_FILE);
        $arrayPermessiCache = $cache->get("privilegiGlobaliModel-$uid");
        if ($arrayPermessiCache) {
            return $arrayPermessiCache;
        }

        $defaultBasePermessi = array(
            'PER_FLAGVIS' => $this->defaultVis,
            'PER_FLAGACC' => $this->defaultAcc,
            'PER_FLAGEDT' => $this->defaultMod,
            'PER_FLAGINS' => $this->defaultIns,
            'PER_FLAGDEL' => $this->defaultDel
        );

        $defaultPermessi = array_merge($defaultBasePermessi, $defaultExtPermessi);

        $arrayPermessi = array(
            'PER_FLAGVIS' => false,
            'PER_FLAGACC' => false,
            'PER_FLAGEDT' => false,
            'PER_FLAGINS' => false,
            'PER_FLAGDEL' => false
        );

        $ita_puntimenu_tab = $this->searchModels_ini($root, $model);

        foreach ($ita_puntimenu_tab as $ita_puntimenu_rec) {
            $me_menu = $ita_puntimenu_rec['me_menu'];
            $pm_voce = $ita_puntimenu_rec['pm_voce'];
            
            foreach ($gruppi as $gruppo) {
                $sql = "SELECT * FROM MEN_PERMESSI WHERE
                        PER_MEN = '$me_menu' AND PER_VME = '$pm_voce' AND PER_GRU = '$gruppo'";

                $men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);

                foreach (array_keys($arrayPermessi) as $flag) {
                    if ($arrayPermessi[$flag] === true) {
                        continue;
                    }

                    if ($men_permessi_rec) {
                        if ($men_permessi_rec[$flag] == 1) {
                            $arrayPermessi[$flag] = true;
                        }
                    } else {
                        $baseDefault = $defaultPermessi[$flag];

                        if ($flag == 'PER_FLAGVIS') {
                            $baseDefault = ($ita_puntimenu_rec['pm_flagvis'] == -1 ) ? $baseDefault : $ita_puntimenu_rec['pm_flagvis'];
                        }

                        if ($baseDefault == 1) {
                            $arrayPermessi[$flag] = true;
                        }
                    }
                }

                /*
                 * Controllo se tutti i permessi sono 'true'
                 */
                foreach ($arrayPermessi as $value) {
                    if ($value === false) {
                        /*
                         * Se almeno uno è 'false', vado al prossimo giro
                         */
                        continue 2;
                    }
                }

                break 2;
            }
        }

        $cache->set("privilegiGlobaliModel-$uid", $arrayPermessi, 30 * 60);

        return $arrayPermessi;
    }

    /**
     *
     * @param int $me_id    id del menu (ita_menu)
     * @param int $utente  codice utente
     * @param array $gruppi gruppi da filtrare
     * @return type 
     */
    public function menuFiltrato($me_id, $utente = '', $gruppi = '') {
        if ($utente == '' && $gruppi == '') {
            $utente = $this->utenteAttuale;
        }
        $Ita_puntimenu_tab = $this->GetIta_puntimenu($me_id);

        // Acquisisci i gruppi
        if ($gruppi == '') {
            $gruppi = $this->getGruppi($utente);
        }
        $Ita_menu_rec = $this->GetIta_menu($me_id, 'me_id');
        $me_menu = $Ita_menu_rec['me_menu'];

        $Def_puntimenu_tab = array();
        foreach ($Ita_puntimenu_tab as $key => $Ita_puntimenu_rec) {
            if ($this->privilegiPuntoMenu($me_menu, $Ita_puntimenu_rec, $gruppi, 'PER_FLAGVIS', $this->defaultVis)) {
                $Def_puntimenu_tab[] = $Ita_puntimenu_rec;
            }
        }
        return $Def_puntimenu_tab;
    }

    public function menuFiltrato_ini($me_menu, $utente = '', $gruppi = '') {
        if ($utente == '' && $gruppi == '') {
            $utente = $this->utenteAttuale;
        }
        $Ita_puntimenu_tab = $this->GetIta_puntimenu_ini($me_menu);

        // Acquisisci i gruppi
        if ($gruppi == '') {
            $gruppi = $this->getGruppi($utente);
        }

        $Def_puntimenu_tab = array();
        if(is_array($Ita_puntimenu_tab)){
            foreach ($Ita_puntimenu_tab as $Ita_puntimenu_rec) {
                if ($this->privilegiPuntoMenu($me_menu, $Ita_puntimenu_rec, $gruppi, 'PER_FLAGVIS', $this->defaultVis)) {
                    $Def_puntimenu_tab[] = $Ita_puntimenu_rec;
                }
            }
        }
        return $Def_puntimenu_tab;
    }

    /**
     *  Legge un record di permessi
     * @param String $gruppo Gruppo
     * @param String $menu Nome menu
     * @param String $voce Voce punto menu
     * @return Array Record permessi 
     */
    public function leggiPermessi($gruppo, $menu, $voce) {
        $sql = "SELECT * FROM MEN_PERMESSI WHERE PER_GRU = '" . $gruppo . "' AND PER_MEN = '" . $menu . "' AND PER_VME = '" . $voce . "'";
        $Men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
    }

    /**
     *  Decodifica un flag in stringa per inserire i valori nella select
     * @param String $flag '0', '1' o ''
     * @return string
     */
    static public function decodeFlag($flag = '') {
        $val = '';
        switch ($flag) {
            case '0':
                $val = 'Nega';
                break;
            case '1':
                $val = 'Consenti';
                break;
            default:
                $val = 'Seleziona..';
                break;
        }
        return $val;
    }

    /**
     *  Legge e restituisce i permessi decodificati di un punto menu
     * @param String $gruppo Il codice del gruppo
     * @param String $menu Il menu padre
     * @param String $voce Il codice del punto menu
     * @return Array I 5 valori in ordine come in tabella 
     */
    static public function filtraPermessi($gruppo, $menu, $voce) {
        $permessi = array();
        $Men_permessi_rec = $this->leggiPermessi($gruppo, $menu, $voce);
        if ($Men_permessi_rec) {
            $permessi['PER_FLAGVIS'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGVIS']);
            $permessi['PER_FLAGACC'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGACC']);
            $permessi['PER_FLAGEDT'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGEDT']);
            $permessi['PER_FLAGINS'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGINS']);
            $permessi['PER_FLAGDEL'] = menLib::decodeFlag($Men_permessi_rec['PER_FLAGDEL']);
        } else {
            $permessi['PER_FLAGVIS'] = menLib::decodeFlag();
            $permessi['PER_FLAGACC'] = menLib::decodeFlag();
            $permessi['PER_FLAGEDT'] = menLib::decodeFlag();
            $permessi['PER_FLAGINS'] = menLib::decodeFlag();
            $permessi['PER_FLAGDEL'] = menLib::decodeFlag();
        }
        return $permessi;
    }

    /**
     *  Salva i permessi di un punto menu
     * @param String $gruppo Il codice del gruppo
     * @param String $menu Il menu padre
     * @param String $voce Il codice del punto menu
     * @param Array  $permessi I permessi da impostare
     * @return Bool se l'operazione riesce o meno
     */
    public function salvaPermessi($gruppo, $menu, $voce, $permessi) {
        $sql = "SELECT * FROM MEN_PERMESSI WHERE PER_GRU = '" . $gruppo . "' AND PER_MEN = '" . $menu . "' AND PER_VME = '" . $voce . "'";
        $Men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        $result = $Men_permessi_rec;
        $Men_permessi_rec['PER_FLAGVIS'] = $permessi['PER_FLAGVIS'];
        $Men_permessi_rec['PER_FLAGACC'] = $permessi['PER_FLAGACC'];
        $Men_permessi_rec['PER_FLAGEDT'] = $permessi['PER_FLAGEDT'];
        $Men_permessi_rec['PER_FLAGINS'] = $permessi['PER_FLAGINS'];
        $Men_permessi_rec['PER_FLAGDEL'] = $permessi['PER_FLAGDEL'];
        // Se esiste già aggiorna, altrimenti inserisci
        if ($result) {
            return ItaDB::DBUpdate($this->ITALWEB_DB, 'MEN_PERMESSI', 'ROWID', $Men_permessi_rec);
        }
        $Men_permessi_rec['PER_GRU'] = $gruppo;
        $Men_permessi_rec['PER_MEN'] = $menu;
        $Men_permessi_rec['PER_VME'] = $voce;
        return ItaDB::DBInsert($this->ITALWEB_DB, 'MEN_PERMESSI', 'ROWID', $Men_permessi_rec);
    }

    /**
     *  Cancella un record dalla tabella dei permessi
     * @param String $gruppo Gruppo
     * @param String $menu Nome menu
     * @param String $voce Voce punto menu
     * @return type 
     */
    public function cancellaPermessi($gruppo, $menu, $voce) {
        $sql = "SELECT * FROM MEN_PERMESSI WHERE PER_GRU = '" . $gruppo . "' AND PER_MEN = '" . $menu . "' AND PER_VME = '" . $voce . "'";
        $Men_permessi_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        return ItaDB::DBDelete($this->ITALWEB_DB, 'MEN_PERMESSI', 'ROWID', $Men_permessi_rec['ROWID']);
    }

    /**
     *  Lancia il programma cliccato da uno dei menu utilizzati
     * @param String $menu Menu 'me_menu'
     * @param String $prog Programma 'pm_voce'
     * @return type 
     */
    public function lanciaProgramma($menu, $prog) {
        $Ita_menu_rec = $this->GetIta_menu($menu);
        $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $Ita_menu_rec['me_id'] . " AND pm_voce = '" . $prog . "'";
        $Ita_puntimenu_rec = ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
        if (!$Ita_puntimenu_rec['pm_model']) {
            Out::msgInfo("Attenzione", "Model non definito");
            return;
        }
        $visualizza = $this->privilegiPuntoMenu($menu, $Ita_puntimenu_rec, $this->getGruppi($this->utenteAttuale), 'PER_FLAGVIS', $this->defaultVis);
        if (!$visualizza) {  // Uteriore controllo!
            Out::msgInfo("Attenzione", "Non hai il permesso di visualizzare questa voce di menu.");
            return;
        }
        $accesso = $this->privilegiPuntoMenu($menu, $Ita_puntimenu_rec, $this->getGruppi($this->utenteAttuale), 'PER_FLAGACC', $this->defaultAcc);
        if (!$accesso) {
            Out::msgInfo("Attenzione", "Non hai il permesso di accesso.");
            return;
        }
        $model = $Ita_puntimenu_rec['pm_model'];
        $retPerms['noEdit'] = !$this->privilegiPuntoMenu($menu, $Ita_puntimenu_rec, $this->getGruppi($this->utenteAttuale), 'PER_FLAGEDT', $this->defaultMod);
        $retPerms['noInsert'] = !$this->privilegiPuntoMenu($menu, $Ita_puntimenu_rec, $this->getGruppi($this->utenteAttuale), 'PER_FLAGINS', $this->defaultIns);
        $retPerms['noDelete'] = !$this->privilegiPuntoMenu($menu, $Ita_puntimenu_rec, $this->getGruppi($this->utenteAttuale), 'PER_FLAGDEL', $this->defaultDel);



        //$_POST['perms'] = $retPerms;
        //$_POST['event'] = 'openform';
        if ($Ita_puntimenu_rec['pm_post']) {
            $arPost = explode("=", $Ita_puntimenu_rec['pm_post']);
            $_POST[$arPost[0]] = $arPost[1];
        }
        itaLib::openForm($model, "", true, "desktopBody", 'menuapp');
        /* @var $modelObj itaModel */
        $modelObj = itaModel::getInstance($model);
        //$modelObj->setModelData($_POST);
        $modelObj->setEvent('openform');
        $modelObj->setElementId('');
        $modelObj->setPerms($retPerms);
        $modelObj->parseEvent();


//        $phpURL = App::getConf('modelBackEnd.php');
//        $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
//        include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
//        $model();

        if ($_POST['noSave'] != true) {
            $this->setUltProg(array("MENU" => $menu, "PROG" => $prog));
        }
    }

    public function lanciaProgramma_ini($menu, $prog) {
        $Ita_puntimenu_rec = $this->GetIta_puntimenu_ini($menu, $prog);
        if (!$Ita_puntimenu_rec['pm_model']) {
            Out::msgInfo("Attenzione", "Model non definito");
            return;
        }
        $visualizza = $this->privilegiPuntoMenu($menu, $Ita_puntimenu_rec, $this->getGruppi($this->utenteAttuale), 'PER_FLAGVIS', $this->defaultVis);
        if (!$visualizza) {  // Uteriore controllo!
            Out::msgInfo("Attenzione", "Non hai il permesso di visualizzare questa voce di menu.");
            return;
        }
        $accesso = $this->privilegiPuntoMenu($menu, $Ita_puntimenu_rec, $this->getGruppi($this->utenteAttuale), 'PER_FLAGACC', $this->defaultAcc);
        if (!$accesso) {
            Out::msgInfo("Attenzione", "Non hai il permesso di accesso.");
            return;
        }
        $model = $Ita_puntimenu_rec['pm_model'];
        $retPerms['noEdit'] = !$this->privilegiPuntoMenu($menu, $Ita_puntimenu_rec, $this->getGruppi($this->utenteAttuale), 'PER_FLAGEDT', $this->defaultMod);
        $retPerms['noInsert'] = !$this->privilegiPuntoMenu($menu, $Ita_puntimenu_rec, $this->getGruppi($this->utenteAttuale), 'PER_FLAGINS', $this->defaultIns);
        $retPerms['noDelete'] = !$this->privilegiPuntoMenu($menu, $Ita_puntimenu_rec, $this->getGruppi($this->utenteAttuale), 'PER_FLAGDEL', $this->defaultDel);



        //$_POST['perms'] = $retPerms;
        //$_POST['event'] = 'openform';
        if ($Ita_puntimenu_rec['pm_post']) {
            $postParams = explode('&', $Ita_puntimenu_rec['pm_post']);
            foreach ($postParams as $postParam) {
                $arPost = explode("=", $postParam);
                $_POST[$arPost[0]] = $arPost[1];
            }
        }
        itaLib::openForm($model, "", true, "desktopBody", 'menuapp');
        /* @var $modelObj itaModel */
        $modelObj = itaModel::getInstance($model);
        //$modelObj->setModelData($_POST);
        $modelObj->setEvent('openform');
        $modelObj->setElementId('');
        $modelObj->setPerms($retPerms);
        $modelObj->parseEvent(); //?
//        $phpURL = App::getConf('modelBackEnd.php');
//        $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
//        include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
//        $model();

        if ($_POST['noSave'] != true) {
            $this->setUltProg(array("MENU" => $menu, "PROG" => $prog));
        }
    }

    /**
     *  Lancia un programma dal model (NON CONTROLLA I PROVILEGI, USARE CON ATTENZIONE|)
     * @param String $model Il model 
     */
    public function lanciaProgrammaModel($model) {
//        $retPerms['noEdit'] = false;
//        $retPerms['noInsert'] = false;
//        $retPerms['noDelete'] = false;
        $_POST['event'] = 'openform';
        itaLib::openForm($model, "", true, "desktopBody", 'menuapp');
        Out::hide('menuapp');
        Out::show($model);
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
        $model();
    }

// CANCELLARE??
//    /**
//     *  Riordina un array
//     * @param Array $arr L'array di input
//     */
//    static public function rep_array($arr) {
//        $newarr = array();
//        $i = 0;
//        foreach ($arr as $key => $val) {
//            $newarr[$i] = $val;
//            $i++;
//        }
//        return $newarr;
//    }

    /**
     * Restituisce un record ita_menu 
     * @param mixed $_Codice Voce menu(me_menu) o id (me_id)
     * @param string $Tipo Chiave di lettura, 'me_menu' o 'me_id'
     * @return array 
     */
    public function GetIta_menu($_Codice, $Tipo = 'me_menu') {
        if ($Tipo == 'me_menu') {
            $sql = "SELECT * FROM ita_menu WHERE me_menu ='$_Codice'";
        } else {
            $sql = "SELECT * FROM ita_menu WHERE me_id = $_Codice";
        }
        return ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
    }

    public function GetIta_menu_ini($menu) {
        $p = $this->getIniPath() . "/$menu.ini";
        if (!file_exists($p)) {
            return false;
        }
        $m = parse_ini_file($p, true);

//        return array('me_menu' => $menu, 'me_descrizione' => $m['Config']['me_descrizione']);
        return array_merge($m['Config'], array('me_menu' => $menu));
    }

    /**
     *  Restituisce un elenco di record della tabella ita_puntimenu
     * @param mixed $_Codice Valore di confronto (me_id di default)
     * @param String $Tipo Chiave di lettura (default: 'me_id')
     * @return Array Array di record ita_puntimenu
     */
    public function GetIta_puntimenu($_Codice, $Tipo = 'me_id') {
        if ($Tipo == 'me_id') {
            $sql = "SELECT * FROM ita_puntimenu WHERE me_id = " . $_Codice . " ORDER BY pm_sequenza";
        }
        return ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, true);
    }

    public function GetIta_puntimenu_ini($menu_parent, $pm_voce = false) {
        $p = $this->getIniPath() . "/$menu_parent.ini";
        if (!file_exists($p)) {
            return false;
        }
        $a = array();

        if ($this->tmpMenuIni[$menu_parent]) {
            $m = $this->tmpMenuIni[$menu_parent];
        } else {
            $m = parse_ini_file($p, true);
            $this->tmpMenuIni[$menu_parent] = $m;
        }

        foreach ($m as $k => $v) {
            if ($k !== 'Config' && $k !== 'Info') {
                $v['me_menu'] = $menu_parent;
                $v['pm_voce'] = $k;
                $v['pm_categoria'] = $v['pm_categoria'];
                $a[] = $v;
                if ($pm_voce && $pm_voce == $k) {
                    return $v;
                }
            }
        }
        return count($a) > 0 ? $a : false;
    }

    /**
     * Restituisce un record ita_puntimenu 
     * @param mixed $_Codice Valore di confronto (pm_id di default)
     * @param string $Tipo Chiave di lettura (default: 'pm_id')
     * @return array Un record ita_puntimenu
     */
    public function GetIta_puntimenu_rec($_Codice, $Tipo = 'pm_id') {
        if ($Tipo == 'pm_id') {
            $sql = "SELECT * FROM ita_puntimenu WHERE pm_id = " . $_Codice;
        }
        return ItaDB::DBSQLSelect($this->ITALSOFT_DB, $sql, false);
    }

    public function getIcona($permesso) {
        if ($permesso) {
            return $this->iconaCons;
        } else {
            return $this->iconaNega;
        }
    }

    /**
     * Chiama la registrazione dei dati dell'ultimo programma aperto e aggiorna le liste
     * @param type $param
     * @param type $refresh 
     */
    public function setUltProg($param, $refresh = true) {
        menLib::saveLastProg($param);
        if ($refresh) {
            $this->refreshMenPersonal();
        }
    }

    /**
     * Chiama la registrazione del programma preferito e aggiorna la lista dei preferiti
     * @param type $param
     * @param type $refresh 
     */
    public function setBookmark($param, $refresh = true) {
        menLib::saveBookMark($param);
        if ($refresh) {
            $this->refreshMenPersonal();
        }
    }

    public function unSetBookmark($param, $refresh = true) {
        menLib::saveBookMark($param, true);
        if ($refresh) {
            $this->refreshMenPersonal();
        }
    }

    /**
     * Aggiorna le liste della barra sinistra nella home
     */
    public function refreshMenPersonal() {
        $model = "menPersonal";
        $phpURL = App::getConf('modelBackEnd.php');
        $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
        include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
        $_POST = array();
        $_POST['event'] = "refresh";
        $model();
    }

    private function parseMenu($menuKey,$menuName,$gruppi){
        $programmi = array();
        $Ita_puntimenu_tab = $this->menuFiltrato_ini($menuKey,'',$gruppi);
        
        foreach($Ita_puntimenu_tab as $Ita_puntimenu_rec){
            if ($this->privilegiPuntoMenu($menuKey, $Ita_puntimenu_rec, $gruppi, 'PER_FLAGVIS', $this->defaultVis)) {
                $Ita_menu_rec_temp = $this->GetIta_menu_ini($Ita_puntimenu_rec['pm_voce'], 'me_menu');
                if($Ita_menu_rec_temp){
                    $programmi = array_merge($programmi, $this->parseMenu($Ita_puntimenu_rec['pm_voce'], $menuName . '>' . $Ita_puntimenu_rec['pm_descrizione'],$gruppi));
                }
                else{
                    $programmi[utf8_encode($menuName . '>' . $Ita_puntimenu_rec['pm_descrizione'])] = array(
                                                                                        'menu'=>utf8_encode($menuKey),
                                                                                        'prog'=>utf8_encode($Ita_puntimenu_rec['pm_voce'])
                                                                                    );
                }
            }
        }
        return $programmi;
    }
    
    private function initProgramList($gruppi,$force=false){
        if(!isSet($this->programmi)){
            if(stripos(ITA_DESKTOP, ':') !== false){
                $menuKey = substr(ITA_DESKTOP, stripos(ITA_DESKTOP, ':')+1);
            }
            else{
                $menuKey = 'TI_MEN';
            }
            
            $hash = md5(trim(implode('|',$gruppi)).trim($menuKey).trim(App::$utente->getKey('ditta')));
            
            $cache = CacheFactory::newCache(CacheFactory::TYPE_FILE);
            
            if($force){
                $programmi = null;
            }
            else{
                $programmi = $cache->get("MenuCache_".$hash);
            }
            if(!$programmi){
                $programmi = array();
                
                $Ita_puntimenu_tab = $this->menuFiltrato_ini($menuKey,'',$gruppi);
                foreach($Ita_puntimenu_tab as $Ita_puntimenu_rec){
                    if ($this->privilegiPuntoMenu($menuKey, $Ita_puntimenu_rec, $gruppi, 'PER_FLAGVIS', $this->defaultVis)) {
                        $Ita_menu_rec_temp = $this->GetIta_menu_ini($Ita_puntimenu_rec['pm_voce'], 'me_menu');
                        if($Ita_menu_rec_temp){
                            $programmi = array_merge($programmi, $this->parseMenu($Ita_puntimenu_rec['pm_voce'], $Ita_puntimenu_rec['pm_descrizione'],$gruppi));
                        }
                        else{
                            $programmi[utf8_encode($Ita_puntimenu_rec['pm_descrizione'])] = array(
                                                                                                'menu'=>utf8_encode($menuKey),
                                                                                                'prog'=>utf8_encode($Ita_puntimenu_rec['pm_voce'])
                                                                                            );
                        }
                    }
                }
                ksort($programmi, SORT_ASC);
                $cache->set("MenuCache_".$hash, $programmi, 24*60*60);
            }
            $this->programmi = $programmi;
        }
        return $this->programmi;
    }
    
    public function searchProgrammi($search,$limit=null){
        $programmi = $this->initProgramList($this->getGruppi($this->utenteAttuale));
        $programmiKeys = array_keys($programmi);
        
        $return = array();
        foreach($programmiKeys as $key){
            if(stripos($key, $search) !== false){
                $return[$key] = $programmi[$key];
            }
            
            if(isSet($limit) && count($return) >= $limit){
                break;
            }
        }
        return $return;
    }
    
    public function buildProgramListCache(){
        $userEnte = App::$utente->getKey('ditta');
        $italsoftDB = $this->ITALSOFT_DB;
        $italwebDB = $this->ITALWEB_DB;
        
        foreach(App::getEnti() as $ente){
            App::$utente->setKey('ditta', $ente['codice']);
            
            unset($this->ITALSOFT_DB);
            $this->ITALSOFT_DB = $this->getItalsoft();
            
            unset($this->ITALWEB_DB);
            $this->ITALWEB_DB = $this->getItalweb();
            
            $ITW_DB = ItaDB::DBOpen('ITW');
        
            $sql = 'SELECT UTECOD FROM UTENTI WHERE'
                    . '(DATAINIZ = ' . $ITW_DB->blank() . ' OR DATAINIZ < :DATE1) AND '
                    . '(DATAFINE = ' . $ITW_DB->blank() . ' OR DATAFINE > :DATE2) AND '
                    . 'UTESPA > :DATE3';
            $params = array(
                array(
                    'name' => 'DATE1',
                    'value' => date('Ymd'),
                    'type' => PDO::PARAM_STR
                ),
                array(
                    'name' => 'DATE2',
                    'value' => date('Ymd'),
                    'type' => PDO::PARAM_STR
                ),
                array(
                    'name' => 'DATE3',
                    'value' => date('Ymd'),
                    'type' => PDO::PARAM_STR
                )
            );
            $utenti = ItaDB::DBSQLSelect($ITW_DB, $sql, true, '', '', $params);

            $arrayGruppi = array();
            foreach($utenti as $utente){
                $gruppi = $this->getGruppi($utente['UTECOD']);

                if(stripos(ITA_DESKTOP, ':') !== false){
                    $menuKey = substr(ITA_DESKTOP, stripos(ITA_DESKTOP, ':')+1);
                }
                else{
                    $menuKey = 'TI_MEN';
                }

                $hash = md5(trim(implode('|',$gruppi)).trim($menuKey).trim(App::$utente->getKey('ditta')));

                $arrayGruppi[$hash] = $gruppi;
            }

            foreach($arrayGruppi as $hash=>$gruppi){
                $this->initProgramList($gruppi,true);
            }
        }
        
        if($userEnte !== null){
            App::$utente->setKey('ditta',$userEnte);
        }
        $this->ITALSOFT_DB = $italsoftDB;
        $this->ITALWEB_DB = $italwebDB;
    }

    public function getRootMenu() {
        if (stripos(ITA_DESKTOP, ':') !== false) {
            return substr(ITA_DESKTOP, stripos(ITA_DESKTOP, ':') + 1);
        } else {
            return 'TI_MEN';
        }
    }

}
