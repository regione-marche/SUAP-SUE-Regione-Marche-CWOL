<?php

class itaShellExec {

    /**
     * Esegue un comando shell
     * 
     * @param string $cmd
     * @param string $args
     * @param string $procName
     * @param string $returnForm
     * @param string $returnId
     * @param string $returnEvent
     */
    static public function shellExec($cmd, $args, $hidden = false, $procName = 'itaEngine', $returnForm = '', $returnId = '', $returnEvent = '') {
        require_once ITA_LIB_PATH . '/itaPHPSmartAgent/SmartAgent.class.php';

        /* @var $smartAgent SmartAgent */
        $smartAgent = new SmartAgent();

        if ($smartAgent->isEnabled()) {
            $smartAgent->shellExec($cmd, $args, $hidden, $procName, $returnForm, $returnId, $returnEvent);
        } else {
            Out::codice("$('#appletIFrame').contents().find('#itaRunner')[0].openWordFromJs('" . $args . "', '" . $cmd . "');");
        }
    }

    /**
     * Esegue un comando shell con porta Alternativa funzione temporanea per problema
     * Accesso a reti microsoft da parte di utente system per smartagent service
     * 
     * @param string $cmd
     * @param string $args
     * @param string $procName
     * @param string $returnForm
     * @param string $returnId
     * @param string $returnEvent
     */
    static public function shellExecAlt($cmd, $args, $procName = 'itaEngine', $returnForm = '', $returnId = '', $returnEvent = '') {
        require_once ITA_LIB_PATH . '/itaPHPSmartAgent/SmartAgent.class.php';

        /* @var $smartAgent SmartAgent */
        $smartAgent = new SmartAgent();



        if ($smartAgent->isEnabled()) {
            /*
             * Porta Alternativa
             */
            $altport = (App::getConf('SmartAgent.altport')) ? App::getConf('SmartAgent.altport') : null;
            if ($altport !== null) {
                $smartAgent->setPort($altport);
            }
            $smartAgent->shellExec($cmd, $args, $procName, $returnForm, $returnId, $returnEvent);
        } else {
            Out::codice("$('#appletIFrame').contents().find('#itaRunner')[0].openWordFromJs('" . $args . "', '" . $cmd . "');");
        }
    }

    /**
     * Apre una Remote App (Terminal Server)
     * 
     * @param string $cmd
     * @param string $args
     * @param string $procName
     * @param string $returnForm
     * @param string $returnId
     * @param string $returnEvent
     */
    static public function remoteAppExec($cmd, $args, $procName = 'itaEngine', $returnForm = '', $returnId = '', $returnEvent = '') {
        require_once ITA_LIB_PATH . '/itaPHPSmartAgent/SmartAgent.class.php';

        /* @var $smartAgent SmartAgent */
        $smartAgent = new SmartAgent();

        if ($smartAgent->isEnabled()) {
            $smartAgent->remoteAppExec($cmd, $args, $procName, $returnForm, $returnId, $returnEvent);
        } else {
            Out::msgStop("ERRORE", "Impossibile aprire la RemoteApp: Smartagent non configurato");
        }
    }
    
    /**
     * Apre una Remote App utilizzando Myrtille
     * Esempio di chiamata CW
     * https://myrtille.gruppoapra.com/Myrtille/Default.aspx?__EVENTTARGET=&__EVENTARGUMENT=&domain=GRUPPOAPRA&user=administrator&password=R1focesq%24%25%26&server=srvrd01-demo.gruppoapra.com&connect=Connect&program=C%3A%5CProgram%20Files%20%28x86%29%5CCityware%5Ccityware.exe
     * 
     * @param array $options Opzioni
     *      - REMOTE_APP_PATH: Path remote app sul server
     *      - REMOTE_APP_ARGS: Parametri remote app
     *      - MYRTILLE_PATH: Path applicativo Myrtille
     *      - MYRTILLE_DOMAIN: Dominio su cui gira la remote app
     *      - MYRTILLE_USER: Username connessione alla remote app
     *      - MYRTILLE_PWD: Password connessione alla remote app
     *      - MYRTILLE_SRV: Server dove risulta installata la remote app
     */
    static public function remoteAppMyrtilleExec($options) {
        // Verifica precondizioni
        if (!isset($options['REMOTE_APP_PATH']) || strlen($options['REMOTE_APP_PATH']) === 0) {
            Out::msgStop("ERRORE", "Impossibile aprire la RemoteApp: Percorso Remote App non impostato");
            return;
        }        
        if (!isset($options['MYRTILLE_PATH']) || strlen($options['MYRTILLE_PATH']) === 0) {
            Out::msgStop("ERRORE", "Impossibile aprire la RemoteApp: Percorso applicativo Myrtille non impostato");
            return;
        }
        if (!isset($options['MYRTILLE_DOMAIN']) || strlen($options['MYRTILLE_DOMAIN']) === 0) {
            Out::msgStop("ERRORE", "Impossibile aprire la RemoteApp: Dominio Remote App non impostato");
            return;
        }
        if (!isset($options['MYRTILLE_USER']) || strlen($options['MYRTILLE_USER']) === 0) {
            Out::msgStop("ERRORE", "Impossibile aprire la RemoteApp: Username per connessione alla Remote App non impostato");
            return;
        }
        if (!isset($options['MYRTILLE_PWD']) || strlen($options['MYRTILLE_PWD']) === 0) {
            Out::msgStop("ERRORE", "Impossibile aprire la RemoteApp: Password per connessione alla Remote App non impostata");
            return;
        }
        if (!isset($options['MYRTILLE_SRV']) || strlen($options['MYRTILLE_SRV']) === 0) {
            Out::msgStop("ERRORE", "Impossibile aprire la RemoteApp: Server dove installata la Remote App non impostato");
            return;
        }
        
        // Compone url
        $url = $options['MYRTILLE_PATH'] . '?__EVENTTARGET=&__EVENTARGUMENT=&domain=' . $options['MYRTILLE_DOMAIN'];        
        //$url .= '&user=' . $options['MYRTILLE_USER'] . '&password=' . urlencode($options['MYRTILLE_PWD']);        
        $url .= '&user=' . $options['MYRTILLE_USER'] . '&passwordHash=' . $options['MYRTILLE_PWD'];                
        $url .= '&server=' . $options['MYRTILLE_SRV'] . '&connect=Connect';
        $url .= '&program=' . urlencode($options['REMOTE_APP_PATH']);
        if (isset($options['REMOTE_APP_ARGS'])) {
            $url .= '+' . urlencode($options['REMOTE_APP_ARGS']);
        }                        
        
        // Apre documento        
        if ($options['MYRTILLE_OPENMODE'] == 1) {
            // Effettua apertura su un nuovo tab del desktop (tramite iframe)
            $model = 'utiIFrame';
            $_POST = array();
            $_POST['event'] = 'openform';
            $_POST['returnModel'] = 'cwbLauncherMenu';
            $_POST['returnEvent'] = 'returnFromCityware';
            $_POST['retid'] = '';
            $_POST['src_frame'] = $url;
            $_POST['title'] = $options['MYRTILLE_TITLE'];
            $_POST['returnKey'] = 'cityware';            
            $_POST['fullscreen'] = 1;            
            itaLib::openForm($model, '', false, 'desktopBody', '', 'app');
            $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
            require_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
            $model();
        } else {
            // Effettua apertura su un nuovo tab del browser
            Out::openDocument($url);
        }        
    }
    
}
