<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */include_once './apps/Menu/menGetinidata.php';

// CARICO LE LIBRERIE NECESSARIE


include_once './apps/Menu/menGetinidata.php';

function menGes() {
    $menGes = new menGes();
    $menGes->parseEvent();
    return;
}

class menGes extends itaModel {

    public $ITW_DB;
    public $nameForm = 'menGes';
    public $seqMenu;
    public $curMenu;
    public $preMenu;

    function __construct() {
        parent::__construct();
        try {
            $this->ITW_DB = ItaDB::DBOpen('ITW');
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
        $this->perms = App::$utente->getKey($this->nameForm . '_perms');

        $this->seqMenu = App::$utente->getKey($this->nameForm . '_seqMenu');
        $this->curMenu = App::$utente->getKey($this->nameForm . '_curMenu');
        $this->preMenu = App::$utente->getKey($this->nameForm . '_preMenu');
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_seqMenu', $this->seqMenu);
            App::$utente->setKey($this->nameForm . '_curMenu', $this->curMenu);
            App::$utente->setKey($this->nameForm . '_preMenu', $this->preMenu);
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'callmodel':
                $this->curMenu = $_POST['menu'];
                $this->seqMenu = Array($this->curMenu);
                $this->preMenu = '';
                $menu_ini = $this->decodificaMenu($this->curMenu);
                if (!$menu_ini) {
                    Out::alert('Menù non ablitato');
                    break;
                }
                while (true) {
                    if (App::getConf('menuBackEnd.php') != '') {
                        $test = new menGetinidata($this->curMenu);
                        $puntiMenu = $test->getData();
                        $menuHtml = '<div style="font-size: 12px; text-decoration:none" class="sx ui-widget " onclick="itaGo(\'ItaCall\',this,{id:\'menGes_prevMenu\',event:\'onClick\',model:\'menGes\',menu:\'' . $this->preMenu . '\'}"">Indietro</div><br>';
                        foreach ($puntiMenu as $key => $value) {
                            if ($value['active'] == true) {
                                $phpURL = App::getConf('modelBackEnd.php');
                                $appRoute = App::getPath('appRoute.men');
                                $menu_sub = $phpURL . '/' . $appRoute . '/' . $key . '.ini';
                                if (file_exists($menu_sub)) {
                                    $menuHtml .= '<a style="font-size: 12px; text-decoration:none" class="sx ui-widget input" onclick="itaGo(\'ItaClick\',this,{event:\'onClick\'});" href="#menGes?menu=' . $key . '">' . $value['eqNMen'] . '. ' . $value['eqDesc'] . '</a><br>';
                                } else {
                                    $menuHtml .= '<a style="font-size: 12px; text-decoration:none" class="sx ui-widget input" onclick="itaGo(\'ItaClick\',this,{event:\'onClick\'});" href="#menGes?prog=' . $key . '">' . $value['eqNMen'] . '. ' . $value['eqDesc'] . '</a><br>';
                                }
                            }
                        }
                        Out::html('menuapp', $menuHtml);
                        Out::show('menuapp');
                        break;
                    }

                    if (App::getConf('menuBackEnd.eq') != '') {
                        $url = App::getConf('modelBackEnd.eq') . '/URL_MENGES';
                        $fp = new Snoopy;
                        $myPost['TOKEN'] = App::$utente->getKey('TOKEN');
                        $myPost['menu'] = $this->curMenu;
                        $myPost['ambiente'] = "itaEngine";
                        $fp->submit($url, $myPost);
                        Out::html('menuapp', '<span class="appPath"></span><div>'.$fp->results.'</span>');
                        Out::show('menuapp');
                        break;
                    }
                    if (App::getConf('menuBackEnd.html') != '') {
                        $htmlPath = App::getConf('modelBackEnd.html');
                        $filename = $htmlPath . '/' . $this->curMenu . '.html';
                        $handle = fopen($filename, "r");
                        if ($handle) {
                            $contents = fread($handle, filesize($filename));
                            fclose($handle);
                            Out::html('menuapp', $contents);
                            Out::show('menuapp');
                        } else {
                            Out::msgInfo("Attenzione", "Applicazione non disponibile.");
                        }
                        break;
                    }
                }
                Out::html('descrizionemenu', $menu_ini['Config']['Title']);
                $prevMenu_ini = $this->decodificaMenu($this->preMenu);
                if ($prevMenu_ini[$this->curMenu]['model']) {
                    $_POST = array();
                    $_POST['event'] = 'run';
                    if (isset($prevMenu_ini[$this->curMenu]['model'])) {
                        $model = $prevMenu_ini[$this->curMenu]['model'];
                        if ($model && model != "''") {
                            $phpURL = App::getConf('modelBackEnd.php');
                            $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
                            include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
                            $model();
                        }
                    }
                }
                break;
            case 'onClick':
                if ($_POST['menu']) {
                    $this->preMenu = $this->curMenu;
                    switch ($_POST['id']) {
                        case $this->nameForm . '_prevMenu':
                            array_pop($this->seqMenu);
                            if (count($this->seqMenu) > 0) {
                                $this->curMenu = $this->seqMenu[count($this->seqMenu) - 1];
                            }
                            break;
                        default:
                            $this->curMenu = $_POST['menu'];
                            array_push($this->seqMenu, $this->curMenu);
                            break;
                    }
                    $menu_ini = $this->decodificaMenu($this->curMenu);
                    while (true) {
                        if ($_POST['fg']){
                            ob_clean();
                            $test = new menGetinidata($this->curMenu);
                            $puntiMenu = $test->getData();
                            
                            $menuHtml = '';
                            foreach ($puntiMenu as $key => $value) {
                                //if($tot>15) break;
                                $tot++;
                                if ($value['active']) {
                                    $menu_sub = dirname(__FILE__).'/'.$key.'.ini';
                                    if (file_exists($menu_sub)) {
                                        $menuHtml .= '<li><a href="'.$key.'">'.$value['eqNMen'].'. '.$value['eqDesc'].'</a><ul></ul></li>';
                                    } 
                                    else {
                                        $menuHtml .= '<li><a href="#menGes?menu='.$this->curMenu.'&prog='.$key.'">' . $value['eqNMen'] . '. ' . $value['eqDesc'] . '</a></li>';
                                    }
                                }
                            }
                            echo $menuHtml;
                            exit;
                        }
                        if (App::getConf('menuBackEnd.php') != '') {
                            $test = new menGetinidata($this->curMenu);
                            $puntiMenu = $test->getData();
                            
                            $menuHtml = '<div style="font-size: 12px; text-decoration:none" class="sx ui-widget " onclick="itaGo(\'ItaCall\',this,{id:\'menGes_prevMenu\',event:\'onClick\',model:\'menGes\',menu:\'' . $this->preMenu . '\'})"">Indietro</div><br>';
                            foreach ($puntiMenu as $key => $value) {
                                if ($value['active'] == true) {
                                    $phpURL = App::getConf('modelBackEnd.php');
                                    $appRoute = App::getPath('appRoute.men');
                                    $menu_sub = $phpURL . '/' . $appRoute . '/' . $key . '.ini';
                                    if (file_exists($menu_sub)) {
                                        $menuHtml .= '<a style="font-size: 12px; text-decoration:none" class="sx ui-widget input" onclick="itaGo(\'ItaClick\',this,{event:\'onClick\'});" href="#menGes?menu=' . $key . '">' . $value['eqNMen'] . '. ' . $value['eqDesc'] . '</a><br>';
                                    } else {
                                        $menuHtml .= '<a style="font-size: 12px; text-decoration:none" class="sx ui-widget input" onclick="itaGo(\'ItaClick\',this,{event:\'onClick\'});" href="#menGes?prog=' . $key . '">' . $value['eqNMen'] . '. ' . $value['eqDesc'] . '</a><br>';
                                    }
                                }
                            }
                            Out::html('menuapp', $menuHtml);
                            break;
                        }
                        if (App::getConf('menuBackEnd.eq') != '') {
                            $url = App::getConf('modelBackEnd.eq') . '/URL_MENGES';
                            $fp = new Snoopy;
                            $url = App::getConf('modelBackEnd.eq') . '/URL_MENGES';
                            $myPost['TOKEN'] = $_POST['TOKEN'];
                            $myPost['menu'] = $this->curMenu;
                            $myPost['title'] = $menu_ini[$prog]['eqDesc'];
                            $myPost['eqPlus'] = $menu_ini[$prog]['eqPlus'];
                            $myPost['menuPost'] = $menu_ini[$prog]['post'];
                            $myPost['ambiente'] = "itaEngine";
                            if ($_POST['parentPlus']) {
                                $myPost['parentPlus'] = $_POST['parentPlus'];
                            }

                            $fp->submit($url, $myPost);
                            Out::html('menuapp', $fp->results);
                            break;
                        }
                        if (App::getConf('menuBackEnd.html') != '') {
                            $htmlPath = App::getConf('menuBackEnd.html');
                            $filename = $htmlPath . '/' . $this->curMenu . '.html';
                            $handle = fopen($filename, "r");
                            if ($handle) {
                                $contents = fread($handle, filesize($filename));
                                fclose($handle);
                                Out::html('menuapp', $contents);
                            } else {
                                Out::msgInfo("Attenzione", "Applicazione non disponibile.");
                            }
                            break;
                        }
                    }
                    Out::html('descrizionemenu', $menu_ini['Config']['Title']);
                    $prevMenu_ini = $this->decodificaMenu($this->preMenu);
                    if ($prevMenu_ini[$this->curMenu]['model']) {
                        $_POST = array();
                        $_POST['event'] = 'run';
                        if (isset($prevMenu_ini[$this->curMenu]['model'])) {
                            $model = $prevMenu_ini[$this->curMenu]['model'];
                            if ($model && $model != "''") {
                                $phpURL = App::getConf('modelBackEnd.php');
                                $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
                                include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
                                $model();
                            }
                        }
                    }
                }
                if (isset($_POST['prog'])) {
                    $prog = $_POST['prog'];
                    $menu_ini = $this->decodificaMenu($this->curMenu);
                    $eqProg = $menu_ini[$prog]['eqProg'];
                    $eqPlus = $menu_ini[$prog]['eqPlus'];
                    switch ($menu_ini[$prog]['eqType']) {
                        case 'prog':
                            $keyProg = ($eqPlus) ? $eqProg . "-" . $eqPlus : $eqProg;
                            $retPerms = $this->getPerms($this->curMenu, $keyProg);
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
                    //itaLib::openForm($model);
                    itaLib::openForm($model, "", true, "desktopBody", 'menuapp');
                    Out::hide('menuapp');
                    Out::show($model);
                    $phpURL = App::getConf('modelBackEnd.php');
                    $appRouteProg = App::getPath('appRoute.' . substr($model, 0, 3));
                    include_once $phpURL . '/' . $appRouteProg . '/' . $model . '.php';
                    $model();
                    break;
                }
                break;
        }
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
