<?php

/**
 *  Programma Popolamento passo dati catastali
 *
 *
 * @category   Library
 * @package    /apps/Interni
 * @author     Mario Mazza
 * @copyright  1987-2013 Italsoft srl
 * @license
 * @version    16.04.2013
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once ITA_BASE_PATH . '/apps/Base/basLib.class.php';
include_once ITA_BASE_PATH . '/apps/Utility/utiIcons.class.php';

function devStripClosingTag() {
    $devStripClosingTag = new devStripClosingTag();
    $devStripClosingTag->parseEvent();
    return;
}

class devStripClosingTag extends itaModel {

    public $nameForm = "devStripClosingTag";
    public $path;

    function __construct() {
        parent::__construct();
        try {
            //
            // carico le librerie
            //
            $this->basLib = new basLib();
            $this->path = App::$utente->getKey($this->nameForm . '_path');
        } catch (Exception $e) {
            App::log($e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            App::$utente->setKey($this->nameForm . '_path', $this->path);
        }
    }

    /*
     *  Gestione degli eventi
     */

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $this->OpenRicerca();
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_Elabora':
                        $this->path = $this->formData[$this->nameForm . '_path'];
                        $dir = ITA_BASE_PATH . $this->path;
                        if (!is_dir($dir)){
                            Out::msgStop("Attenzione", "La path $dir non è un percorso corretto.");
                            break;
                        }
                        Out::msgQuestion("Elaborazione", 'Confermi la cancellazione del tag di chiusura ?> e spazi successivi per i programmi php nalla path ' . $dir . ' ?', array(
                            'Annulla' => array('id' => $this->nameForm . '_AnnullaElabora', 'model' => $this->nameForm),
                            'Conferma' => array('id' => $this->nameForm . '_ConfermaElabora', 'model' => $this->nameForm)
                                )
                        );
                        break;
                    case $this->nameForm . '_ConfermaElabora':
                        $this->StripTag();
                        break;
                }
                break;
            case 'close-portlet':
                $this->returnToParent();
                break;
        }
    }

    public function StripTag() {

        $cartella = ITA_BASE_PATH . $this->path;
        $list = scandir($cartella);
        $n=0;
        foreach ($list as $file) {
            $ext = pathinfo($file, PATHINFO_EXTENSION);
            if ($ext != 'php') {
                continue;
            }
            $destFile = $cartella . DIRECTORY_SEPARATOR . pathinfo($file, PATHINFO_BASENAME);
            $content = file_get_contents($destFile);
            $pos = strrpos($content, "?>");
            if ($pos === false){
                continue;
            }
            $pre = substr($content, 0, $pos);
            $post = substr($content, $pos + 2);
            if (trim($post) == '') {
                if (!file_put_contents($destFile, $pre)) {
                    Out::msgStop("Errore", "Errore nella scrittura del file $file.<br />Procedura Interrotta.");
                    return false;
                }
                $n++;
            }
        }

        Out::msgInfo("ok", "Operazione conclusa con successo. $n file elaborati. Verificare.");
        return true;
    }

    /**
     *  Gestione dell'evento della chiusura della finestra
     */
    public function returnToParent($close = true) {
        if ($close) {
            $this->close();
        }
    }

    /**
     * Chiusura della finestra dell'applicazione
     */
    public function close() {
        $this->close = true;
        App::$utente->removeKey($this->nameForm . '_path');
        Out::closeDialog($this->nameForm);
    }

    public function OpenRicerca() {
        Out::clearFields($this->nameForm . '_divRicerca');
        Out::show($this->nameForm . '_divRicerca', '');
        Out::show($this->nameForm . '_Elabora');
        Out::setFocus('', $this->nameForm . '_path');
    }

}
