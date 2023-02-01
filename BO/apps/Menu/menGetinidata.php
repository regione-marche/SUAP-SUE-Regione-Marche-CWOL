<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of menGetinidata
 *
 * @author michele
 */
class menGetinidata {

    private $menuData;
    private $menu;
    private $ITW_DB;

    public function __construct($menu) {
        try {
            $this->ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->menu = $menu;
    }

    public function getData() {
        list($usec, $sec) = explode(" ", microtime());
        $phpURL = App::getConf('modelBackEnd.php');
        $appRoute = App::getPath('appRoute.men');
        $menu_ini = parse_ini_file($phpURL . '/' . $appRoute . '/' . $this->menu . '.ini', true);
        $cod_ute = App::$utente->getKey('idUtente');
        $Utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD='" . $cod_ute . "'", false);
        $puntiMenu = Array();
        foreach ($menu_ini as $key => $value) {
            if ($key == 'Config')
                continue;
            $puntiMenu[$key] = $value;
            if (array_key_exists('active', $value)) {
                continue;
            }
            $puntiMenu[$key]['active'] = false;
            for ($i = 1; $i < 30; $i++) {
                $campo = 'UTEGEX__' . $i;
                $gruppo = $Utenti_rec[$campo];
                if ($gruppo == 0)
                    continue;
                $gruppo = str_pad($gruppo, 10, "0", STR_PAD_LEFT);
                $sequenza = str_pad($value['eqNMen'], 6, "0", STR_PAD_LEFT);
                $sql = "SELECT * FROM APLGRU WHERE APLGRU = '" . $gruppo . "' AND APLMEN = '" . $this->menu . "' AND APLSEQ = '" . $sequenza . "'";
                $Aplgru_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                if ($Aplgru_rec['APLOFF'] == "") {
                    $puntiMenu[$key]['active'] = true;
                    break;
                }
            }
        }
        return $puntiMenu;
    }

    public function getConfig() {
        $phpURL = App::getConf('modelBackEnd.php');
        $appRoute = App::getPath('appRoute.men');
        $menu_ini = parse_ini_file($phpURL . '/' . $appRoute . '/' . $this->menu . '.ini', true);
        return $menu_ini['Config'];
    }
    
    
}

?>
