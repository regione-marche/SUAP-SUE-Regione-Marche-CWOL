<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

// CARICO LE LIBRERIE NECESSARIE


include_once './apps/Menu/menGetinidata.php';
include_once './apps/Menu/menLib.class.php';

function menButton() {
    $menButton = new menButton();
    $menButton->parseEvent();
    return;
}

class menButton extends itaModel {

    public $ITW_DB;
    public $nameForm = 'menButton';
    public $menLib;

    function __construct() {
        parent::__construct();
        try {
            $this->ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->perms = App::$utente->getKey($this->nameForm . '_perms');
        $this->menLib = new menLib();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openButton':
                ob_clean();                
                $menuId  = $_POST['menuId'];
                $menuKey = $_POST['rootMenu'];
                header("Content-Type: text/html; charset=ISO-8859-1\n");                
                echo($this->getMenuContent($menuKey,true));
                exit;
            case 'onClick':
                if (isset($_POST['prog'])) {
                    $prog = $_POST['prog'];
                    $menu = $_POST['menu'];
                    $menu_ini = $this->decodificaMenu($menu);
                    $eqProg = $menu_ini[$prog]['eqProg'];
                    $eqPlus = $menu_ini[$prog]['eqPlus'];
                    switch ($menu_ini[$prog]['eqType']) {
                        case 'prog':
                            $keyProg = ($eqPlus) ? $eqProg . "-" . $eqPlus : $eqProg;
                            $retPerms = $this->getPerms($menu, $keyProg);
                            $model = $menu_ini[$prog]['model'];
                            $_POST['event'] = 'openform';
                            $_POST['perms'] = $retPerms;
                            if ($menu_ini[$prog]['post']) {
                                $arPost = explode("=", $menu_ini[$prog]['post']);
                                $_POST[$arPost[0]] = $arPost[1];
                            }
                            break;
                        case 'eqprog':
                            $model = 'e2pAdapter';
                            $_POST['event'] = "openform";
                            $_POST['url'] = $menu_ini[$prog]['model'];
                            $_POST['title'] = $menu_ini[$prog]['eqDesc'];
                            $_POST['eqPlus'] = $menu_ini[$prog]['eqPlus'];
                            $_POST['menuPost'] = $menu_ini[$prog]['post'];
                            break;
                    }
                    itaLib::openForm($model, "", true, "desktopBody", 'menuapp');
                    Out::hide('menuapp');
                    Out::show($model);
                    $phpURL = App::getConf('modelBackEnd.php');
                    $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
                    include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
                    $model();
                    if ($_POST['noSave'] != true) {
                        $this->menLib->setUltProg(array("MENU" => $menu, "PROG" => $prog));
                    }
                    break;
                }
                if ($_POST['menu']) {
                    $menuKey = $_POST['menu'];
                    ob_clean();
                    header("Content-Type: text/html; charset=ISO-8859-1\n");                                    
                    echo $this->getMenuContent($menuKey);
                    exit;
                }
                break;
        }
    }

    function getMenuContent($menuKey,$rootContent=false,$maxRows=15) {
        $menuObj = new menGetinidata($menuKey);
        $puntiMenu = $menuObj->getData();
        $menuContent='';
        if ($rootContent)
            $menuContent .= '<ul class="col">';
        foreach ($puntiMenu as $key => $value) {
            if ($rootContent && $tot && $tot % $maxRows == 0)
                $menuContent.='</ul><ul class="col">';
            $tot++;
            if ($value['active']) {
                $menu_sub = dirname(__FILE__) . '/../../apps/Menu/' . $key . '.ini';
                if (file_exists($menu_sub)) {
                    $menuContent .= '<li><a href="' . $key . '">' . $value['eqNMen'] . '. ' . $value['eqDesc'] . '</a><ul></ul></li>';
                } else {
                    $menuContent .= '<li><a href="#menButton?menu='.$menuKey.'&prog=' . $key . '">' . $value['eqNMen'] . '. ' . $value['eqDesc'] . '</a></li>';
                }
            }
        }
        if ($rootContent) 
            $menuContent.='</ul>';
        return $menuContent;
    }

    function decodificaMenu($menu) {
        $phpURL = App::getConf('modelBackEnd.php');
        $appRoute = App::getPath('appRoute.men');
        $menu_ini = parse_ini_file($phpURL . '/' . $appRoute . '/' . $menu . '.ini', true);
        if ($menu_ini) {
            return $menu_ini;
        } else {
            return null;
        }
    }

    function getPerms($menu, $prog) {
        $flNoe = false;
        $flNoc = false;
        $idUtente = App::$utente->getKey('idUtente');
        $Utenti_rec = ItaDB::DBSQLSelect($this->ITW_DB, "SELECT * FROM UTENTI WHERE UTECOD = '$idUtente'", false);
        for ($i = 1; $i <= 30; $i++) {
            if ($Utenti_rec["UTEGEX__$i"] != 0) {
                $gruppo = str_pad($Utenti_rec["UTEGEX__$i"], 10, '0', STR_PAD_LEFT);
                $sql = "SELECT * FROM APLGRU WHERE APLGRU = '$gruppo' AND APLPRG = '$menu|$prog'";
                $Aplgru_rec = ItaDB::DBSQLSelect($this->ITW_DB, $sql, false);
                if ($Aplgru_rec) {
                    if ($Aplgru_rec['APLNOE'] != '') {
                        $flNoe = true;
                    }
                    if ($Aplgru_rec['APLNOC'] != '') {
                        $flNoc = true;
                    }
                } else {
                    $flNoe = true;
                    $flNoc = true;
                }
                if ($flNoe == false && $flNoc == false) {
                    break;
                }
            }
        }
        return array('noEdit' => $flNoe, 'noDelete' => $flNoc);
    }
}

?>
