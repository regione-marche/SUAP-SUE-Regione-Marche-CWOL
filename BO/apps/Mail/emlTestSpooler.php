<?php

/**
 *
 * TEST PROTOCOLLAZIONE DIFFERITA
 *
 * PHP Version 5
 *
 * @category
 * @package    Pratiche
 * @author     
 * @copyright  1987-2018 Italsoft snc
 * @license
 * @version    05.02.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Mail/emlLib.class.php';
include_once ITA_BASE_PATH . '/apps/Mail/emlSpoolManager.class.php';
include_once(ITA_LIB_PATH . '/itaPHPCore/itaXML.class.php');

function emlTestSpooler() {
    $emlTestSpooler = new emlTestSpooler();
    $emlTestSpooler->parseEvent();
    return;
}

class emlTestSpooler extends itaModel {

    public $nameForm = "emlTestSpooler";

    function __construct() {
        parent::__construct();
        try {
            //
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
        }
    }

    function __destruct() {
        parent::__destruct();
        if ($this->close != true) {
            
        }
    }

    public function parseEvent() {
        parent::parseEvent();
        switch ($_POST['event']) {
            case 'openform':
                itaLib::openForm($this->nameForm, "", true, "desktopBody");
                Out::show($this->nameForm);
                break;
            case 'onClick':
                switch ($_POST['id']) {
                    case $this->nameForm . '_testXml':

                        $envelopeObj = emlSpoolManager::getEnvelopeInstance();
                        if (!$envelopeObj) {
                            Out::msgStop("Errore", "Istanza oggetto busta fallita.");
                            break;
                        }

                        $envelopeObj->setFromAddress('alessandro.mucci@italsoft.eu');


                        $envelopeObj->addToAddress('michele.moscioni@italsoft.eu');
                        $envelopeObj->addToAddress('mario.mazza@italsoft.eu');

                        $envelopeObj->addCcAddress('1@italsoft.eu');
                        $envelopeObj->addCcAddress('2@italsoft.eu');
                        $envelopeObj->addCcAddress('3@italsoft.eu');

                        $envelopeObj->addBccAddress('4@italsoft.eu');
                        $envelopeObj->addBccAddress('5@italsoft.eu');
                        $envelopeObj->addBccAddress('6@italsoft.eu');
                        $envelopeObj->addBccAddress('7@italsoft.eu');
                        $envelopeObj->addBccAddress('8@italsoft.eu');

                        $envelopeObj->setSubject("Oggetto di prova.... @òç#à°ù§*éè?^ì&%$£\"!|<>");

                        $envelopeObj->setBody("<pre>ciao ciao...... @òç#à°ù§*éè?^ì&%$£\"!|<> </pre>");

                        $envelopeObj->addAttachmentFromStream('test.pdf', '132321321321');

                        $envelopeObj->addExtraParameter('key1', 'value1');
                        $envelopeObj->addExtraParameter('key2', 'value2');
                        $envelopeObj->addExtraParameter('key3', 'value3');

                        Out::msgInfo("XML", htmlspecialchars($envelopeObj->getEnvelopeDataXML()));
                        Out::msgInfo("to", print_r($envelopeObj->getToAddresses(), true));
                        Out::msgInfo("from", $envelopeObj->getFromAddress());

                        break;
                    case $this->nameForm . '_callAddEnvelopesToPackage':
                        $envelopeObj = emlSpoolManager::getEnvelopeInstance();
                        if (!$envelopeObj) {
                            Out::msgStop("Errore", "Istanza oggetto busta fallita.");
                            break;
                        }

                        $envelopeObj->setFromAddress($_POST[$this->nameForm . '_mailaccount']);


                        $envelopeObj->addToAddress('michele.moscioni@italsoft.eu');
                        $envelopeObj->addToAddress('michele.moscioni@gmail.com');

                        $envelopeObj->addCcAddress('1@italsoft.eu');
                        $envelopeObj->addCcAddress('2@italsoft.eu');
                        $envelopeObj->addCcAddress('3@italsoft.eu');

                        $envelopeObj->addBccAddress('4@italsoft.eu');
                        $envelopeObj->addBccAddress('5@italsoft.eu');
                        $envelopeObj->addBccAddress('6@italsoft.eu');
                        $envelopeObj->addBccAddress('7@italsoft.eu');
                        $envelopeObj->addBccAddress('8@italsoft.eu');

                        $envelopeObj->setSubject('Oggetto di prova òç@ à°#èé[+*]ì^\'?=)(/&%$£"!\<<<&&&&>>>>\|');


                        $testBody = ' </div>
 
 <div class="refsect1 description" id="refsect1-function.htmlentities-description">
  <h3 class="title">Description<a class="genanchor" href="#refsect1-function.htmlentities-description"> ¶</a></h3>
  <div class="methodsynopsis dc-description">
   <span class="type">string</span> <span class="methodname"><strong>htmlentities</strong></span>
    ( <span class="methodparam"><span class="type">string</span> <code class="parameter">$string</code></span>
   [, <span class="methodparam"><span class="type">int</span> <code class="parameter">$flags</code><span class="initializer"> = ENT_COMPAT | ENT_HTML401</span></span>
   [, <span class="methodparam"><span class="type">string</span> <code class="parameter">$encoding</code><span class="initializer"> = ini_get("default_charset")</span></span>
   [, <span class="methodparam"><span class="type">bool</span> <code class="parameter">$double_encode</code><span class="initializer"> = <strong><code>TRUE</code></strong></span></span>
  ]]] )</div>

  <p class="para rdfs-comment">
   This function is identical to <span class="function"><a href="function.htmlspecialchars.php" class="function">htmlspecialchars()</a></span> in all
   ways, except with <span class="function"><strong>htmlentities()</strong></span>, all characters which
   have HTML character <<<<<<<>>>>>>>>&&&&&&entity equivalents are translated into these entities.
  </p>
  <p class="para">
   If you want to decode instead (the reverse) you can use
   <span class="function"><a href="function.html-entity-decode.php" class="function">html_entity_decode()</a></span>.
  </p>
 </div>';
                        $envelopeObj->setBody($testBody);

                        $envelopeObj->addAttachmentFromStream('test.pdf', '132321321321');

                        $envelopeObj->addExtraParameter('key1', 'value1');
                        $envelopeObj->addExtraParameter('key2', 'value2');
                        $envelopeObj->addExtraParameter('key3', 'value3');

                        $packageObj = emlSpoolManager::getPackageInstance();
                        if (!$packageObj) {
                            Out::msgStop("Errore", "Istanza oggetto pacchetto fallita.");
                            break;
                        }
                        $params['note'] = $_POST[$this->nameForm . '_note'];
                        $params['appcontext'] = $_POST[$this->nameForm . '_appcontext'];
                        $params['appkey'] = $_POST[$this->nameForm . '_appkey'];
                        $params['mailaccount'] = $_POST[$this->nameForm . '_mailaccount'];

                        // richiesta del package
                        $retCrete = $packageObj->createEnvelopesPackage($params);
                        if (!$retCrete) {
                            Out::msgStop("Errore", $packageObj->getErrMessage());
                            break;
                        }

                        // inserimenta prima envelopes
                        $ret = $packageObj->addEnvelopeToPackage($envelopeObj);
                        if (!$ret) {
                            Out::msgStop("Errore1", $packageObj->getErrMessage());
                            break;
                        }
                        // inserimenta seconda envelopes
                        $ret = $packageObj->addEnvelopeToPackage($envelopeObj);
                        if (!$ret) {
                            Out::msgStop("Errore2", $packageObj->getErrMessage());
                            break;
                        }

                        $ret = $packageObj->closeEnvelopesPackage();
                        if (!$ret) {
                            Out::msgStop("Errore3", $packageObj->getErrMessage());
                            break;
                        } else {
                            Out::msgInfo('', print_r($packageObj, true));
                        }
                        break;
                    case $this->nameForm . '_callCreateEnvelopesPackage':
                        $packageObj = emlSpoolManager::getPackageInstance();
                        if (!$packageObj) {
                            Out::msgStop("Errore", "Istanza oggetto pacchetto fallita.");
                            break;
                        }
                        $params['note'] = $_POST[$this->nameForm . '_note'];
                        $params['appcontext'] = $_POST[$this->nameForm . '_appcontext'];
                        $params['appkey'] = $_POST[$this->nameForm . '_appkey'];
                        $params['mailaccount'] = $_POST[$this->nameForm . '_mailaccount'];
                        $retCrete = $packageObj->createEnvelopesPackage($params);
                        if (!$retCrete) {
                            Out::msgStop("Errore", $packageObj->getErrMessage());
                            break;
                        }
                        Out::msgInfo("Creato Pacchetto", $packageObj->getPackageId());
                        break;
                    case $this->nameForm . '_callParseQueue':
                        $managerObj = new emlSpoolManager();
                        $ret = $managerObj->parsePackageQueue();
                        out::msgInfo('risultato', print_r($ret, true));
                        break;
                    case $this->nameForm . '_callStatusEnvelope':
                        $packageObj = emlSpoolManager::getPackageInstance();
                        if (!$packageObj) {
                            Out::msgStop("Errore", "Istanza oggetto pacchetto fallita.");
                            break;
                        }
                        $envelopeObj = emlSpoolManager::getEnvelopeInstance();
                        $retStatus = $envelopeObj->getEnvelopeStatus($_POST[$this->nameForm . '_envelopeId']);
                        Out::msgInfo("Status Envelope", print_r($retStatus, true));
                        break;
                    case $this->nameForm . '_callStatusPackage':
                        $packageObj = emlSpoolManager::getPackageInstance();
                        if (!$packageObj) {
                            Out::msgStop("Errore", "Istanza oggetto pacchetto fallita.");
                            break;
                        }
                        $params = array();
                        $params['PACKAGEID'] = $_POST[$this->nameForm . '_packageId'];
                        $params['DETAILEVP'] = $_POST[$this->nameForm . '_packageDetail'];
                        $params['DETAILLEVEL'] = $_POST[$this->nameForm . '_levelDetail'];

                        $retStatus = $packageObj->getPackageStatus($params);
                        Out::msgInfo("Status Package", print_r($retStatus, true));
                        break;
                    case $this->nameForm . '_disattivaEnvelope':
                        $packageObj = emlSpoolManager::getPackageInstance();
                        if (!$packageObj) {
                            Out::msgStop("Errore", "Istanza oggetto pacchetto fallita.");
                            break;
                        }
                        $envelopeObj = emlSpoolManager::getEnvelopeInstance();
                        $retStatus = $envelopeObj->disattivaEnvelope($_POST[$this->nameForm . '_envelopeId']);
                        Out::msgInfo("Status Envelope", print_r($retStatus, true));
                        break;
                }
        }
    }

    public function close() {
        $this->close = true;
        Out::closeDialog($this->nameForm);
    }

    public function returnToParent($close = true) {
        if ($close)
            $this->close();
    }

}
