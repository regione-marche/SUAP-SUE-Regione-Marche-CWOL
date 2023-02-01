<?php

/**
 *
 * Template minimo per sviluppo model
 *
 *  * PHP Version 5
 *
 * @category   itaModel
 * @package    Accessi
 * @author     Carlo Iesari <carlo@iesari.me>
 * @copyright  1987-2012 Italsoft sRL
 * @license
 * @version    19.01.2015
 * @link
 * @see
 * @since
 * @deprecated
 **/

function accBottomBar() {
    $accBottomBar = new accBottomBar();
    $accBottomBar->parseEvent();
    return;
}

class accBottomBar extends itaModel {

    public $nameForm = "accBottomBar";

    function __construct() {
        parent::__construct();
		$this->private = false;
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($this->event) {
            case 'openform':
				Out::html($this->nameForm . '_copyright', '©2015 <a href="http://www.italsoft.eu/" target="_blank">italsoft</a> <em>Yes we can</em>');
				Out::html($this->nameForm . '_aboutLink', 'About');
				Out::html($this->nameForm . '_storeLink', 'Store');
				Out::html($this->nameForm . '_supportLink', 'Support');
				Out::html($this->nameForm . '_docsLink', 'Docs');
				Out::html($this->nameForm . '_blogLink', 'News');
				Out::html($this->nameForm . '_facebookLink', 'Facebook');
				Out::codice('$("#' . $this->nameForm . ' br").remove();');
                break;
            case 'onBlur':
                break;
            case 'onClick':
                switch ($this->elementId) {
					case $this->nameForm . '_aboutLink':
						Out::msgInfo("About itaEngine","itaEngine v. 5.1","auto","auto","");
						break;
                    case 'close-portlet':
                        $this->returnToParent();
                        break;
                }

                break;
        }
    }
    
    public function close() {
        parent::close();
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close=true) {
        if ($close) $this->close();
    }
    
    

}

?>