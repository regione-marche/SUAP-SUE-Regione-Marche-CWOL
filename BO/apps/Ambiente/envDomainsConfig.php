<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function envDomainsConfig() {
    $envDomains = new envDomainsConfig();
    $envDomains->parseEvent();
    return;
}

class envDomainsConfig extends itaModel {

    function __construct() {
        parent::__construct();
    }

    function __destruct() {
        parent::__destruct();
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                $modelName = 'envDomains';
                itaLib::openForm($modelName);
                $modelInstance = itaModel::getInstance($modelName);
                $modelInstance->setCurrentDomain(App::$utente->getKey('ditta'));
                $modelInstance->setEvent('openform');
                $modelInstance->parseEvent();
                break;
        }
    }

}
