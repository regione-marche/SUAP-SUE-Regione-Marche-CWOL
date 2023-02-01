<?php

/**
 *
 * ANAGRAFICA VARIABILI D'AMBIENTE
 *
 * PHP Version 5
 *
 * @category   
 * @package    Pratiche
 * @author     Antimo Panetta <antimo.panetta@italsoft.eu>
 * @copyright  1987-2011 Italsoft snc
 * @license
 * @version    27.12.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praLibEnvVars {

    static $SISTEM_ENVIRONMENT_VARIABLES = array(
        "CATASTO_SEZIONE" => array('ENVDES' => 'Gestione della Sezione nella raccolta Dati Catasto'),
        "CARICAMENTO_AUTOCERTIFICAZIONE" => array('ENVDES' => 'Caricamento autocertificazione per accorpamento richieste'),
        "RICHIESTE_ACCORPABILI" => array('ENVDES' => 'Gestione richieste accorpabili'),
        "GIORNI_MANIFESTAZIONE" => array('ENVDES' => 'Caricamento giorni minimi di anticipo per manifestazioni'),
        "DISABILITA_CONFERMA_ANNULLA_RACCOLTA" => array('ENVDES' => 'Gestione conferma annullamento di una raccolta'),
        "DENOMINAZIONE_RESP_PRIVACY" => array('ENVDES' => 'Denominazione Responsabile Privacy'),
        "SEDE_RESP_PRIVACY" => array('ENVDES' => 'Sede Responsabile Privacy'),
        "MAIL_RESP_PRIVACY" => array('ENVDES' => 'Mail Responsabile Privacy'),
        "RICHIEDENTE_DATI_PRIVACY" => array('ENVDES' => 'Richiedente per Dati Privacy'),
        "ATTIVA_SEMPRE_PASSO_DIRITTI" => array('ENVDES' => 'Attiva sempre passo diritti di istruttoria'),
    );

}