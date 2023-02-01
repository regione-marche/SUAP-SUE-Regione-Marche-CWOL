<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2019 Italsoft srl
 * @license
 * @version    07.05.2019
 * @link
 * @see
 * @since
 * @deprecated
 * */
require_once(ITA_LIB_PATH . '/itaPHPMPay/itaPHPMPay.class.php');

class itaPHPMPayManager {

    private $clientParam;

    public static function getInstance($clientParam) {
        try {
            $managerObj = new itaPHPMPayManager();
            $managerObj->setClientParam($clientParam);
            return $managerObj;
        } catch (Exception $exc) {
            return false;
        }
    }

    public function getClientParam() {
        return $this->clientParam;
    }

    public function setClientParam($clientParam) {
        $this->clientParam = $clientParam;
    }

    /**
     * Libreria di funzioni Generiche e Utility per Protocollo Italsoft
     *
     */
    private function setClientConfig($itaMpay) {
        $itaMpay->setUrlPID($this->clientParam['MAPY_URL_PID']);
        $itaMpay->setUrlRID($this->clientParam['MPAY_URL_RID']);
        $itaMpay->setCodicePortale($this->clientParam['MPAY_CODICE_PORTALE']);
        $itaMpay->setEncryptIV($this->clientParam['MPAY_ENCRYPT_IV']);
        $itaMpay->setEncryptKey($this->clientParam['MPAY_ENCRYPT_KEY']);
        $itaMpay->setTimeout($this->clientParam['MPAY_TIMEOUT']);
        $itaMpay->setDebug($this->clientParam['MPAY_DEBUG']);
    }

    public function InviaPagamento($dati) {
        $itaMpay = new itaPHPMPay();
        $this->setClientConfig($itaMpay);

        $url = ItaUrlUtil::GetPageUrl(array('event' => 'avvenutoPagamento', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
        $url_not = ItaUrlUtil::GetPageUrl(array('event' => 'notificaPagamento', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
        $url_back = ItaUrlUtil::GetPageUrl(array('event' => 'annullaPagamento', 'seq' => $dati['Ricite_rec']['ITESEQ'], 'ricnum' => $dati['Proric_rec']['RICNUM']));
        $itaMpay->setUrlNotifica($url_not);
        $itaMpay->setUrlBack($url_back);
        $itaMpay->setUrlRitorno($url);

        $request = array();

        $request['CommitNotifica'] = $this->clientParam['MPAY_COMMIT_NOTIFICA'];

        /*
         * User Data
         */
        $request['EmailUtente'] = $dati['Proric_rec']['RICEMA'];
        $request['IdentificativoUtente'] = $dati['Proric_rec']['RICFIS'];

        /*
         * Service Data
         */
        $request['CodiceUtente'] = "000RM";
        $request['CodiceEnte'] = "00936";
        $request['TipoUfficio'] = "";
        $request['CodiceUfficio'] = "";
        $request['TipologiaServizio'] = "SSS";
        $request['NumeroOperazione'] = time(); //?????????????????????????????????
        $request['NumeroDocumento'] = $dati['Ricite_rec']['ITEKEY'] . "_" . time();
        $request['AnnoDocumento'] = substr($dati['Proric_rec']['RICNUM'], 0, 4);
        $request['Valuta'] = "EUR";
        $request['Importo'] = round($dati['Ricite_rec']['TARIFFA'] * 100, 0);
        $request['ImportoMarcaDaBolloDigitale'] = "";
        $request['SegnaturaMarcaDaBolloDigitale'] = "";
        $request['TipoBolloDaErogare'] = "";
        $request['ProvinciaResidenza'] = "";
        $request['DatiSpecifici'] = "";

        $RID = $itaMpay->getRID($request);

        file_put_contents("/tmp/rid.log", $RID);

        $Bi = $itaMpay->getBufferInvio($RID);

        file_put_contents("/tmp/bi.log", $Bi);


        $param = array(
            'buffer' => $Bi
        );

        header('Location: ' . $this->clientParam['MPAY_URL_GEN'] . "?" . http_build_query($param));
        exit;

        return $ritorno;
    }

    public function RiceviPagamento($pid) {
        $itaMpay = new itaPHPMPay();
        $this->setClientConfig($itaMpay);

        $paymentData = $itaMpay->getPaymentData($pid);

        print_r("<pre>");
        print_r($paymentData);
        print_r("</pre>");
        exit();
    }

}

?>