<?php

/**
 * Description of envLibPortlet
 *
 * @author Carlo Iesari <carlo@iesari.me>
 */
class envLibPortlet {

    private $errMessage;
    private $errCode;
    public $ITALWEB_DB;

    function __construct() {
        
    }

    public function setITALWEB_DB($ITALWEB_DB) {
        $this->ITALWEB_DB = $ITALWEB_DB;
    }

    public function getITALWEB_DB() {
        if (!$this->ITALWEB_DB) {
            try {
                $this->ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB_DB;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getConfigRecord() {
        $sql = "SELECT * FROM ENV_PROFILI WHERE UTECOD = " . App::$utente->getIdUtente() . " AND ELEMENTO = 'ita-controlpad'";
        $env_profili_rec = ItaDB::DBSQLSelect($this->getITALWEB_DB(), $sql, false);

        if (!$env_profili_rec) {
            $env_profili_rec = array(
                'UTECOD' => App::$utente->getIdUtente(),
                'ELEMENTO' => 'ita-controlpad',
                'CONFIG' => serialize(array(
                    'sortablecol' => array(
                        'env_controlpad_1' => array(
                            'order' => ''
                        ),
                        'env_controlpad_2' => array(
                            'order' => ''
                        )
                    )
                ))
            );

            ItaDB::DBInsert($this->ITALWEB_DB, 'ENV_PROFILI', 'ROWID', $env_profili_rec);

            $env_profili_rec = ItaDB::DBSQLSelect($this->ITALWEB_DB, $sql, false);
        }

        return $env_profili_rec;
    }

    public function caricaPortlet($portletId, $aperturaEnv = false, $asApp = false) {
        $portletFile = App::getAppFolder('env') . "/portlets/" . $portletId . "/" . $portletId . ".class.php";

        if (!file_exists($portletFile)) {
            return false;
        }

        include_once $portletFile;
        $portletModel = new $portletId();

        switch ($asApp) {
            case false:
                $html = $portletModel->load();
                Out::html("env_controlpad_1", $html, 'append');
                $portletModel->run();
                Out::codice('$("#' . $portletId . '").addClass("ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
                        .find( ".ita-portlet-header" ).addClass( "ui-widget-header ui-corner-all")
                        .end().find( ".ita-portlet-content" );');

                Out::codice('$("#ita-controlpad").find("#' . $portletId . '").find(".ita-portlet-header .ita-portlet-plus").click(function() {
                            $( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
                            $( this ).parents( ".ita-portlet:first" ).find( ".ita-portlet-content" ).toggle();
                        });');

                Out::codice('$("#ita-controlpad").find("#' . $portletId . '").find(".ita-portlet-header .ita-portlet-trash").click(function() {
                            var helper_id="' . $portletId . '";
                            itaGo(\'ItaCall\',\'\',{asyncCall:false,bloccaui:true,event:\'iconTrashClick\',model:\'envControlPad\',id:helper_id});
                        });');
                break;

            case true:
                /*
                 * Apro il model HTML
                 */
                $model = $portletModel->model;
                itaLib::openApp($model);
                Out::setAppTitle($model, '<u>' . $portletModel->title . '</u>');

                /*
                 * Evento per chiusura sull'icona della tab
                 */
                $itaGo = 'itaGo( \'ItaCall\', \'\', {asyncCall:false,bloccaui:true,event:\'iconTrashClick\',model:\'envControlPad\',id:helper_id})';
                $onClick = 'var helper_id = "' . $portletId . '"; ' . $itaGo . ';';
                Out::codice('$( "#portlet-close-icon-' . $model . '" ).click( function() { ' . $onClick . ' } );');

                /*
                 * Avvio la chiamata asincrona per 'openportletapp'
                 */
                $portletModel->runApp();
                break;
        }

        if (!$aperturaEnv) {
            $this->caricaPortletConfig($portletId, $asApp);
        }
    }

    private function caricaPortletConfig($portletId, $asApp) {
        $env_profili_rec = $this->getConfigRecord();
        $config = unserialize($env_profili_rec['CONFIG']);

        switch ($asApp) {
            default:
            case false:
                $controlpad = 'env_controlpad_1';
                break;

            case true:
                $controlpad = 'env_controlpad_2';
                break;
        }

        if (!isset($config['sortablecol'][$controlpad])) {
            $config['sortablecol'][$controlpad] = array('order' => '');
        }

        $portlet_attivi = explode(',', $config['sortablecol'][$controlpad]['order']);
        $portlet_attivi[] = $portletId;
        $portlet_attivi = array_filter(array_unique($portlet_attivi));
        $config['sortablecol'][$controlpad]['order'] = implode(',', $portlet_attivi);

        $env_profili_rec['CONFIG'] = serialize($config);

        ItaDB::DBUpdate($this->ITALWEB_DB, 'ENV_PROFILI', 'ROWID', $env_profili_rec);
    }

    public function rimuoviPortlet($portletId) {
        $env_profili_rec = $this->getConfigRecord();
        $config = unserialize($env_profili_rec['CONFIG']);
        $controlpads = array('env_controlpad_1', 'env_controlpad_2');

        foreach ($controlpads as $controlpad) {
            if (!isset($config['sortablecol'][$controlpad])) {
                $config['sortablecol'][$controlpad] = array('order' => '');
            }

            $portlet_attivi = explode(',', $config['sortablecol'][$controlpad]['order']);

            $portlet_attivi = array_filter(array_unique($portlet_attivi));

            $portlet_key = array_search($portletId, $portlet_attivi);

            if ($portlet_key !== false) {
                unset($portlet_attivi[$portlet_key]);

                switch ($controlpad) {
                    case 'env_controlpad_1':
                        Out::delContainer($portletId);
                        break;

                    case 'env_controlpad_2':
                        $portletFile = App::getAppFolder('env') . "/portlets/" . $portletId . "/" . $portletId . ".class.php";

                        if (file_exists($portletFile)) {
                            include_once $portletFile;
                            $portletModel = new $portletId();
                            itaLib::closeForm($portletModel->model);
                        }

                        break;
                }
            }

            $config['sortablecol'][$controlpad]['order'] = implode(',', $portlet_attivi);
        }

        $env_profili_rec['CONFIG'] = serialize($config);

        ItaDB::DBUpdate($this->ITALWEB_DB, 'ENV_PROFILI', 'ROWID', $env_profili_rec);
    }

    public function getPortletAttivi() {
        $Env_profili_rec = $this->getConfigRecord();
        $Controlpad_config = unserialize($Env_profili_rec['CONFIG']);
        $portlet_attivi = explode(',', $Controlpad_config['sortablecol']['env_controlpad_1']['order']);
        if (isset($Controlpad_config['sortablecol']['env_controlpad_2']['order'])) {
            $portlet_attivi_app = explode(',', $Controlpad_config['sortablecol']['env_controlpad_2']['order']);
            $portlet_attivi = array_merge($portlet_attivi, $portlet_attivi_app);
        }
        return $portlet_attivi;
    }

    public function getPortletInfo($idPortlet) {
        $portletInfo = array();
        $Env_profili_rec = $this->getConfigRecord();
        $Controlpad_config = unserialize($Env_profili_rec['CONFIG']);

        $portlet_attivi = explode(',', $Controlpad_config['sortablecol']['env_controlpad_1']['order']);

        if (in_array($idPortlet, $portlet_attivi)) {
            $portletInfo['type'] = 'portlet';
        } else {
            if (isset($Controlpad_config['sortablecol']['env_controlpad_2']['order'])) {
                $portlet_attivi_app = explode(',', $Controlpad_config['sortablecol']['env_controlpad_2']['order']);
                if (in_array($idPortlet, $portlet_attivi_app)) {
                    $portletInfo['type'] = 'app';
                }
            }
        }

        return $portletInfo;
    }

    public function checkActivePortlet($idPortlet) {
        $portlet_attivi = $this->getPortletAttivi();
        if (in_array($idPortlet, $portlet_attivi)) {
            return true;
        } else {
            return false;
        }
    }

}
