<?php

/**
 *
 * PHP Version 5
 *
 * @category
 * @package
 * @author     Mario Mazza <mario.mazza@italsoft.eu>
 * @copyright  1987-2012 Italsoft srl
 * @license
 * @version    05.12.2012
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Ambiente/envLibProtocolla.class.php';
include_once ITA_BASE_PATH . '/apps/Accessi/accLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class proIntegrazioni {

    static function GetMetedatiProt($codice, $tipo = "PRATICA", $tipoCom = "") {
        if (!$codice) {
            return false;
        }
        $PRAM_DB = ItaDB::DBOpen('PRAM');
        try {
            $ISOLA_DB = ItaDB::DBOpen('ISOLA');
        } catch (Exception $exc) {
            $ISOLA_DB = false;
        }
        switch ($tipo) {
            case 'PRATICA':
                $proges_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT GESMETA FROM PROGES WHERE GESNUM = '$codice'", false);
                $metadati = unserialize($proges_rec['GESMETA']);
                break;
            case 'PASSO':
                $pracom_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT COMMETA FROM PRACOM WHERE COMPAK = '$codice' AND COMTIP = '$tipoCom'", false);
                $metadati = unserialize($pracom_rec['COMMETA']);
                break;
            case 'RICHIESTA':
                $proric_rec = ItaDB::DBSQLSelect($PRAM_DB, "SELECT RICMETA FROM PRORIC WHERE RICNUM = '$codice'", false);
                $metadati = unserialize($proric_rec['RICMETA']);
                break;
            case 'PERMESSO':
                if (!$ISOLA_DB) {
                    break;
                }
                $Isola_rec = ItaDB::DBSQLSelect($ISOLA_DB, "SELECT ISOLAMETA FROM ISOLA WHERE ROWID = '$codice'", false);
                $metadati = unserialize($Isola_rec['ISOLAMETA']);
                break;
            case 'ZTL_COMUNICAZIONE':
                if (!$ISOLA_DB) {
                    break;
                }
                $Comunicazioni_rec = ItaDB::DBSQLSelect($ISOLA_DB, "SELECT COMMETA FROM COMUNICAZIONI WHERE ROWID = '$codice'", false);
                $metadati = unserialize($Comunicazioni_rec['COMMETA']);
                break;

            default:
                break;
        }
        if (!$metadati) {
            return;
        }
        $retMeta = array();
        switch ($metadati['DatiProtocollazione']['TipoProtocollo']['value']) {
            case 'Paleo4':
            case 'Paleo':
            case 'Paleo41':
                $retMeta['Data'] = substr($metadati['DatiProtocollazione']['Data']['value'], 0, 4) . substr($metadati['DatiProtocollazione']['Data']['value'], 5, 2) . substr($metadati['DatiProtocollazione']['Data']['value'], 8, 2);
                $retMeta['CodiceWS'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
                $retMeta['Segnatura'] = $metadati['DatiProtocollazione']['Segnatura']['value'];
                break;
            case 'WSPU':
                $retMeta['Data'] = substr($metadati['DatiProtocollazione']['Data']['value'], 0, 4) . substr($metadati['DatiProtocollazione']['Data']['value'], 5, 2) . substr($metadati['DatiProtocollazione']['Data']['value'], 8, 2);
                $retMeta['CodiceWS'] = $metadati['DatiProtocollazione']['codiceProtocollo']['value'];
                $retMeta['Segnatura'] = $metadati['DatiProtocollazione']['proNum']['value'] . "/" . $metadati['DatiProtocollazione']['Anno']['value'];
                break;
            case 'Infor':
                $retMeta['Data'] = $metadati['DatiProtocollazione']['Data']['value'];
                $retMeta['CodiceWS'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
                $retMeta['Segnatura'] = $metadati['DatiProtocollazione']['Segnatura']['value'];
                break;
            case 'Kibernetes':
            case 'Leonardo':
            case 'E-Lios':
                $retMeta['Data'] = substr($metadati['DatiProtocollazione']['Data']['value'], 0, 4) . substr($metadati['DatiProtocollazione']['Data']['value'], 5, 2) . substr($metadati['DatiProtocollazione']['Data']['value'], 8, 2);
                $retMeta['Ora'] = '';
                break;
            case 'Manuale':
                $retMeta['idMail'] = $metadati['DatiProtocollazione']['IdMailRichiesta']['value'];
                $retMeta['Data'] = $metadati['DatiProtocollazione']['Data']['value'];
                $retMeta['Numero'] = $metadati['DatiProtocollazione']['Numero']['value'];
                $retMeta['Oggetto'] = $metadati['DatiProtocollazione']['Oggetto']['value'];
                break;
            case 'Italsoft-remoto':
                $retMeta['Data'] = $metadati['DatiProtocollazione']['Data']['value'];
                $retMeta['Oggetto'] = $metadati['DatiProtocollazione']['Oggetto']['value'];
                break;
            case 'Jiride':
            case 'Iride':
                if (strlen($metadati['DatiProtocollazione']['Data']['value']) == 8) {
                    $retMeta['Data'] = $metadati['DatiProtocollazione']['Data']['value'];
                    $retMeta['Ora'] = '';
                } else {
                    $retMeta['Data'] = substr($metadati['DatiProtocollazione']['Data']['value'], 0, 4) . substr($metadati['DatiProtocollazione']['Data']['value'], 5, 2) . substr($metadati['DatiProtocollazione']['Data']['value'], 8, 2);
                    $retMeta['Ora'] = date('H:i:s', strtotime($metadati['DatiProtocollazione']['Data']['value']));
                }
                $retMeta['CodiceWS'] = $metadati['DatiProtocollazione']['IdDocumento']['value'];
                $retMeta['Segnatura'] = $metadati['DatiProtocollazione']['proNum']['value'] . "/" . $metadati['DatiProtocollazione']['Anno']['value'];
                $retMeta['Aggregato'] = $metadati['DatiProtocollazione']['Aggregato']['value'];
                $retMeta['CodAmm'] = $metadati['DatiProtocollazione']['CodAmm']['value'];
                $retMeta['CodAoo'] = $metadati['DatiProtocollazione']['CodAoo']['value'];
                $retMeta['codiceIstanza'] = $metadati['DatiProtocollazione']['codiceIstanza']['value'];
            case 'HyperSIC':
                $retMeta['CodiceWS'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
                break;
            case 'Sici':
                $retMeta['Data'] = date("Ymd", strtotime($metadati['DatiProtocollazione']['Data']['value']));
                $retMeta['Segnatura'] = $metadati['DatiProtocollazione']['proNum']['value'] . "/" . $metadati['DatiProtocollazione']['Anno']['value'];
                break;
            case 'CiviliaNext':
                $retMeta['Data'] = substr($metadati['DatiProtocollazione']['Data']['value'], 0, 4) . substr($metadati['DatiProtocollazione']['Data']['value'], 5, 2) . substr($metadati['DatiProtocollazione']['Data']['value'], 8, 2);
                $retMeta['CodiceWS'] = $metadati['DatiProtocollazione']['DocNumber']['value'];
                break;
            default:
                break;
        }
        $retMeta['Anno'] = $metadati['DatiProtocollazione']['Anno']['value'];
        $retMeta['ProNum'] = $metadati['DatiProtocollazione']['proNum']['value'];
        $retMeta['Protocollo'] = substr($metadati['DatiProtocollazione']['proNum']['value'], 4); //n. protocollo senza la data
        $retMeta['TipoProtocollo'] = $metadati['DatiProtocollazione']['TipoProtocollo']['value'];
        $retMeta['Segnatura'] = $metadati['DatiProtocollazione']['Segnatura']['value'];
        return $retMeta;
    }

    static function VediProtocollo($codice, $tipo = "PRATICA", $tipoCom = "") {
        $arrayDati = proIntegrazioni::GetArrayDatiProtocollo($codice, $tipo, $tipoCom);
        if ($arrayDati['Status'] == "-1") {
            return $arrayDati;
        }
        $arrayNormalizzato = proIntegrazioni::NormalizzaArray($arrayDati);
        $html = proIntegrazioni::Array2Html($arrayNormalizzato);

        /*
         * ARRAY NORMALIZZATO DI RITORNO:
         * 
         * array(
         *       'Status' => 0,
         *       'Message' => Ricerca avvenuta con successo,
         *       'RetValue' => array(
         *          'DatiProtocollo' => array(
         *              'TipoProtocollo' => 'Paleo' || 'WSPU' || 'Infor',
         *              'NumeroProtocollo' => 123,
         *              'Data' => 20130107,
         *              'Segnatura' => 158-08/01/2004-REG1 || ... ,
         *              'Anno' => 2012,
         *              'Classifica' => array(DATI CLASSIFICAZIONE),
         *              'Oggetto' => Pratica n°... del .... soggetto .... comune... ,
         *              'DocumentiAllegati' => array(
         *                                      [1] => nome file 1,
         *                                      [2] => nome file 2
         *                                      )
         *              )
         *          )
         * );
         */


        return $html;
    }

    static function VediDocumento($codice, $tipo = "PRATICA", $tipoCom = "") {
        $metaDati = proIntegrazioni::GetMetedatiProt($codice, $tipo, $tipoCom);
        switch ($metaDati['TipoProtocollo']) {
            case 'Jiride':
                $model = 'proJiride.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proJiride = new proJiride();
                $param = array();
                $param['IdDocumento'] = $metaDati['CodiceWS'];
                $arrayDati = $proJiride->LeggiDocumento($param);
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Jiride';
                if ($arrayDati['Status'] != "0") {
                    return $arrayDati;
                }
                break;
            case 'Paleo4':
                $model = 'proPaleo4.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proPaleo = new proPaleo4();
                $param = array();
                $param['Docnumber'] = $metaDati['CodiceWS'];
                $arrayDati = $proPaleo->LeggiProtocollo($param);
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Paleo4';
                if ($arrayDati['Status'] != "0") {
                    return $arrayDati;
                }
                break;
            case 'Paleo41':
                $model = 'proPaleo41.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proPaleo = new proPaleo41();
                $param = array();
                $param['Docnumber'] = $metaDati['CodiceWS'];
                $arrayDati = $proPaleo->LeggiProtocollo($param);
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Paleo4';
                if ($arrayDati['Status'] != "0") {
                    return $arrayDati;
                }
                break;
        }

        if ($arrayDati['RetValue']['DatiProtocollo']['NumeroProtocollo']) {
            proIntegrazioni::updatePracomConNumProt($arrayDati, $codice, $tipoCom);
        }

        $arrayNormalizzato = proIntegrazioni::NormalizzaArray($arrayDati);
        $html = proIntegrazioni::Array2Html($arrayNormalizzato);
        return $html;
    }

    static function NormalizzaArray($arrayDati) {
        switch ($arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo']) {
            case 'Paleo4':
            case 'Paleo':
            case 'Paleo41':
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 0, 4) . substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 5, 2) . substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 8, 2);
                $arrayDati['RetValue']['DatiProtocollo']['DataDoc'] = substr($arrayDati['RetValue']['DatiProtocollo']['DataDoc'], 0, 4) . substr($arrayDati['RetValue']['DatiProtocollo']['DataDoc'], 5, 2) . substr($arrayDati['RetValue']['DatiProtocollo']['DataDoc'], 8, 2);
                $arrayDati['RetValue']['DatiProtocollo']['DescrizioneTipoProtocollo'] = "";
                //unset($arrayDati['RetValue']['DatiProtocollo']['DocNumber']);
                break;
            case 'WSPU':
                $arrayDati['RetValue']['DatiProtocollo']['DescrizioneTipoProtocollo'] = "";
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 0, 4) . substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 5, 2) . substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 8, 2);
                break;
            case 'Infor':
                $arrayDati['RetValue']['DatiProtocollo']['DescrizioneTipoProtocollo'] = "";
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 6, 4) . substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 3, 2) . substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 0, 2);
                break;
            case 'Jiride':
            case 'Iride':
                switch ($arrayDati['RetValue']['Dati']['Origine']) {
                    case "A":
                        $arrayDati['RetValue']['DatiProtocollo']['DescrizioneTipoProtocollo'] = "ARRIVO";
                        break;
                    case "P":
                        $arrayDati['RetValue']['DatiProtocollo']['DescrizioneTipoProtocollo'] = "PARTENZA";
                        break;
                    case "I":
                        $arrayDati['RetValue']['DatiProtocollo']['DescrizioneTipoProtocollo'] = "INTERNO";
                        break;
                }
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 0, 4) . substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 5, 2) . substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 8, 2);
                $arrayDati['RetValue']['DatiProtocollo']['DataDoc'] = substr($arrayDati['RetValue']['DatiProtocollo']['DataDoc'], 0, 4) . substr($arrayDati['RetValue']['DatiProtocollo']['DataDoc'], 5, 2) . substr($arrayDati['RetValue']['DatiProtocollo']['DataDoc'], 8, 2);
                break;
            case 'HyperSIC':
                switch ($arrayDati['RetValue']['Dati']['Origine']) {
                    case "A":
                        $arrayDati['RetValue']['DatiProtocollo']['DescrizioneTipoProtocollo'] = "ARRIVO";
                        break;
                    case "P":
                        $arrayDati['RetValue']['DatiProtocollo']['DescrizioneTipoProtocollo'] = "PARTENZA";
                        break;
                }
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 6, 4) . substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 3, 2) . substr($arrayDati['RetValue']['DatiProtocollo']['Data'], 0, 2);
                break;
            case 'Italsoft-ws':
                $arrayDati['RetValue']['Dati']['IdPratica'] = $arrayDati['RetValue']['DatiProtocollo']['CodiceFascicolo'];
                break;
            case 'Sici':
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = date('Ymd', strtotime($arrayDati['RetValue']['DatiProtocollo']['Data']));
                break;
            case 'Leonardo':
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = date('Ymd', strtotime($arrayDati['RetValue']['DatiProtocollo']['Data']));
                break;
            case 'E-Lios':
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = date('Ymd', strtotime($arrayDati['RetValue']['DatiProtocollo']['Data']));
                break;
            case 'Kibernetes':
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = date('Ymd', strtotime($arrayDati['RetValue']['DatiProtocollo']['Data']));
                break;
            case 'CiviliaNext':
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = date('Ymd', strtotime($arrayDati['RetValue']['DatiProtocollo']['Data']));
                $arrayDati['RetValue']['DatiProtocollo']['DescrizioneTipoProtocollo'] = $arrayDati['RetValue']['Dati']['result'][0]['tipoProtocollo'];
                break;
            default:
                break;
        }
        return $arrayDati;
    }

    static function Array2Html($arrayNormalizzato) {
        //Out::msgInfo("array normal", print_r($arrayNormalizzato, true));
        $datiProtocollo = $arrayNormalizzato['RetValue']['DatiProtocollo'];
        $html .= "<div>";

        $html .= "<span style=\"font-size:1.2em;font-weight:bold;\">Tipo</span>" . " : ";
        $html .= "<span style=\"font-size:1.2em;\">" . $datiProtocollo['TipoProtocollo'] . "</span><br>";

        /*
         * Dati Documento Protocollo
         */
        $htmlIdProt = "";
        if (isset($datiProtocollo['DocNumber']) && $datiProtocollo['DocNumber']) {
            $htmlIdProt .= "&nbsp;&nbsp;&nbsp;<span style=\"font-size:1.2em;font-weight:bold;\">Id</span>" . " : ";
            $htmlIdProt .= "<span style=\"font-size:1.2em;\">" . $datiProtocollo['DocNumber'] . "</span>";
        }

        $htmlDataIdProt = "";
        if ($datiProtocollo['DataDoc']) {
            $htmlDataIdProt .= "&nbsp;&nbsp;&nbsp;<span style=\"font-size:1.2em;font-weight:bold;\">Data</span>" . " : ";
            $data = substr($datiProtocollo['DataDoc'], 6, 2) . "/" . substr($datiProtocollo['DataDoc'], 4, 2) . "/" . substr($datiProtocollo['DataDoc'], 0, 4);
            $htmlDataIdProt .= "<span style=\"font-size:1.2em;\">$data</span><br>";
        } else {
            $htmlDataIdProt = "<br>";
        }

        /*
         * Dati Protocollo/Documento
         */
        if ($datiProtocollo['NumeroProtocollo']) {
            $html .= "<span style=\"font-size:1.2em;font-weight:bold;\">PROTOCOLLO:&nbsp;Numero/Anno</span>" . " : ";
            $html .= "<span style=\"font-size:1.2em;\">" . $datiProtocollo['NumeroProtocollo'] . "/" . $datiProtocollo['Anno'] . "</span>";
        } elseif ($datiProtocollo['DocNumber']) {
            $html .= "<span style=\"font-size:1.2em;font-weight:bold;\">DOCUMENTO:&nbsp;</span>";
        }
        //
//        $html .= "<span style=\"font-size:1.2em;font-weight:bold;\">Anno</span>" . " : ";
//        $html .= "<span style=\"font-size:1.2em;\">" . $datiProtocollo['Anno'] . "</span>";
        //
        $data = substr($datiProtocollo['Data'], 6, 2) . "/" . substr($datiProtocollo['Data'], 4, 2) . "/" . substr($datiProtocollo['Data'], 0, 4);
        if ($datiProtocollo['NumeroProtocollo'] == "") {
            $data = "";
        }
        if ($data) {
            $html .= "&nbsp;&nbsp;&nbsp;<span style=\"font-size:1.2em;font-weight:bold;\">Data</span>" . " : ";
        }
        $html .= "<span style=\"font-size:1.2em;\">$data</span>$htmlIdProt" . $htmlDataIdProt;

        /*
         * Dati Documento
         */
//        if (isset($datiProtocollo['DocNumber']) && $datiProtocollo['DocNumber']) {
//            $html .= "<span style=\"font-size:1.2em;font-weight:bold;\">N. Documento Protocollo</span>" . " : ";
//            $html .= "<span style=\"font-size:1.2em;\">" . $datiProtocollo['DocNumber'] . "</span><br>";
//        }
        //
//        if ($datiProtocollo['DataDoc']) {
//            $html .= "<span style=\"font-size:1.2em;font-weight:bold;\">Data Documento</span>" . " : ";
//            $data = substr($datiProtocollo['DataDoc'], 6, 2) . "/" . substr($datiProtocollo['DataDoc'], 4, 2) . "/" . substr($datiProtocollo['DataDoc'], 0, 4);
//            $html .= "<span style=\"font-size:1.2em;\">$data</span><br>";
//        }

        /*
         * Fascicolazione
         */
        $htmlAnnoPratica = $htmlValueAnnoPratica = "";
        if (isset($arrayNormalizzato['RetValue']['Dati']['AnnoPratica']) && $arrayNormalizzato['RetValue']['Dati']['AnnoPratica']) {
            $htmlAnnoPratica .= "/Anno";
            $htmlValueAnnoPratica = $arrayNormalizzato['RetValue']['Dati']['AnnoPratica'];
        }
        $htmlAnnoNumPratica = "";
        if (isset($arrayNormalizzato['RetValue']['Dati']['AnnoNumeroPratica']) && $arrayNormalizzato['RetValue']['Dati']['AnnoNumeroPratica']) {
            $htmlAnnoNumPratica .= "&nbsp;&nbsp;&nbsp;<span style=\"font-size:1.2em;font-weight:bold;\">Anno/Numero Pratica</span>" . " : ";
            $htmlAnnoNumPratica .= "<span style=\"font-size:1.2em;\">" . $arrayNormalizzato['RetValue']['Dati']['AnnoNumeroPratica'] . "</span><br>";
        } else {
            $htmlAnnoNumPratica = "<br>";
        }

        $htmlNumFascicolo = "";
        if (isset($datiProtocollo['NumeroFascicolo']) && $datiProtocollo['NumeroFascicolo'] && $datiProtocollo['AnnoFascicolo']) {
            $htmlNumFascicolo .= "&nbsp;&nbsp;&nbsp;<span style=\"font-size:1.2em;font-weight:bold;\">FASCICOLO:&nbsp;Numero/Anno</span>" . " : ";
            $htmlNumFascicolo .= "<span style=\"font-size:1.2em;\">" . $datiProtocollo['NumeroFascicolo'] . "/" . $datiProtocollo['AnnoFascicolo'] . "</span><br>";
        }
        $htmlNumPratica = "";
        if (isset($arrayNormalizzato['RetValue']['Dati']['NumeroPratica']) && $arrayNormalizzato['RetValue']['Dati']['NumeroPratica']) {
            $htmlNumPratica .= "<span style=\"font-size:1.2em;font-weight:bold;\">FASCICOLO:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Numero$htmlAnnoPratica</span>" . " : ";
            $htmlNumPratica .= "<span style=\"font-size:1.2em;\">" . $arrayNormalizzato['RetValue']['Dati']['NumeroPratica'] . "/$htmlValueAnnoPratica</span>";
        }
        $htmlIdFascicolo = "";
        if (isset($arrayNormalizzato['RetValue']['Dati']['IdPratica']) && $arrayNormalizzato['RetValue']['Dati']['IdPratica']) {
            $htmlIdFascicolo .= "&nbsp;&nbsp;&nbsp;<span style=\"font-size:1.2em;font-weight:bold;\">Id</span>" . " : ";
            $htmlIdFascicolo .= "<span style=\"font-size:1.2em;\">" . $arrayNormalizzato['RetValue']['Dati']['IdPratica'] . "</span>";
        } else {
            $htmlIdFascicolo = "<br>";
        }


        $html .= $htmlNumFascicolo . $htmlNumPratica . $htmlIdFascicolo . $htmlAnnoNumPratica;
        /*
         * fine Fascicolazione
         */

        if ($datiProtocollo['DescrizioneTipoProtocollo']) {
            $html .= "<span style=\"font-size:1.2em;font-weight:bold;\">Tipo Documento</span>" . " : ";
            $html .= "<span style=\"font-size:1.2em;\">" . $datiProtocollo['DescrizioneTipoProtocollo'] . "</span><br>";
        }

        /*
         * Oggetto protocollo
         */
        if ($datiProtocollo['Oggetto']) {
            $html .= "<div style=\"display:inline-block;\">";
            $html .= "<span style=\"display:inline-block;font-size:1.2em;font-weight:bold;\">Oggetto</span>" . " : ";
            $html .= "<span style=\"display:inline-block;display:inline-block;font-size:1.2em;\">" . $datiProtocollo['Oggetto'] . "</span>";
            $html .= "</div><br>";
        }

        /*
         * Segnatura Protocollo
         */
        if ($datiProtocollo['Segnatura']) {
            $html .= "<div style=\"display:inline-block;\">";
            $html .= "<span style=\"display:inline-block;font-size:1.2em;font-weight:bold;\">Segnatura</span>" . " : ";
            $html .= "<span style=\"display:inline-block;display:inline-block;font-size:1.2em;\">" . $datiProtocollo['Segnatura'] . "</span>";
            $html .= "</div><br>";
        }

        /*
         * Classificazione - Titolario
         */
        $classifica = $datiProtocollo['Classifica'];
        if ($classifica) {
            $html .= "<br><span style=\"margin:5px;color:red;font-size:1.4em;font-weight:bold;\">Classifica</span>";
            $html .= "<div class=\"ita-box ui-widget-content ui-corner-all\">";
            if (is_array($classifica)) {
                foreach ($classifica as $key => $value) {
                    if ($key == "fascicoli") {
                        $html .= "<br><span style=\"margin:5px;color:red;font-size:1.3em;font-weight:bold;\">Fascicoli</span><br>";
                        foreach ($classifica[$key] as $keyFas => $fascicolo) {
                            if ($keyFas == "classificazione") {
                                $html .= "<br><span style=\"margin:5px;color:red;font-size:1.3em;font-weight:bold;\">Classificazione</span><br>";
                                $html .= "<div class=\"ita-box ui-widget-content ui-corner-all\">";
                                foreach ($value[$keyFas] as $keyCla => $classificazione) {
                                    $html .= "<span style=\"margin:5px;font-size:1.2em;font-weight:bold;\">$keyCla</span>" . " : ";
                                    $html .= "<span style=\"margin:5px;font-size:1.2em;\">$classificazione</span><br>";
                                }
                                $html .= "</div>";
                            } else {
                                $html .= "<span style=\"margin:5px;font-size:1.2em;font-weight:bold;\">$keyFas</span>" . " : ";
                                $html .= "<span style=\"margin:5px;font-size:1.2em;\">$fascicolo</span><br>";
                            }
                        }
                    } else {
                        $html .= "<span style=\"margin:5px;font-size:1.2em;font-weight:bold;\">$key</span>" . " : ";
                        $html .= "<span style=\"margin:5px;font-size:1.2em;\">$value</span><br>";
                    }
                }
            } else {
                $html .= "<span style=\"margin:5px;font-size:1.2em;\">$classifica</span><br>";
            }
            $html .= "</div>";
        }

        /*
         * Ufficio Destinatario
         */
        if ($datiProtocollo['InCaricoA'] || $arrayNormalizzato['RetValue']['Dati']['InCaricoA']) {
            if ($datiProtocollo['InCaricoA']) {
                $inCaricoA = $datiProtocollo['InCaricoA'];
                $inCaricoADesc = $datiProtocollo['InCaricoA_Descrizione'];
            } elseif ($arrayNormalizzato['RetValue']['Dati']['InCaricoA']) {
                $inCaricoA = $arrayNormalizzato['RetValue']['Dati']['InCaricoA'];
                $inCaricoADesc = $arrayNormalizzato['RetValue']['Dati']['InCaricoA_Descrizione'];
            }
            $html .= "<br><span style=\"margin:5px;color:red;font-size:1.4em;font-weight:bold;\">In Carico A</span>";
            $html .= "<div class=\"ita-box ui-widget-content ui-corner-all\">";
            $html .= "<span style=\"margin:5px;font-size:1.2em;\">$inCaricoA - $inCaricoADesc</span><br>";
            $html .= "</div>";
        }

        /*
         * Allegati
         */
        $allegati = $datiProtocollo['DocumentiAllegati'];
        if ($allegati) {
            $i = 0;
            $html .= "<br><span style=\"margin:5px,color:red;font-size:1.4em;font-weight:bold;\">Allegati</span>";
            $html .= "<div style=\"width:95%;\">";
            $html .= "<table id=\"tableAllProt\">";
            $html .= "<tr>";
            $html .= '<th></th>';
            $html .= '<th>Nome</th>';
            $html .= "</tr>";
            $html .= "<tbody>";
            foreach ($allegati as $value) {
                if ($value) {
                    $i += 1;
                    $html .= "<tr>";
                    $html .= "<td>$i</td>";
                    $html .= "<td>$value</td>";
                    $html .= "</tr>";
                }
            }
            $html .= "</tbody>";
            $html .= "</table>";
            $html .= "</div>";
//            $html .= "<div class=\"ita-box ui-widget-content ui-corner-all\">";
//            $i = 0;
//            foreach ($allegati as $value) {
//                if ($value) {
//                    $i += 1;
//                    $html .= "<span style=\"margin:5px;font-size:1.2em;font-weight:bold;\">$i</span>" . " - ";
//                    $html .= "<span style=\"margin:5px;font-size:1.2em;\">$value</span><br>";
//                }
//            }
//            $html .= "</div>";
        }
        $html .= "</div>";
        return $html;
    }

    static function GetArrayDatiProtocollo($codice, $tipo, $tipoCom) {
        $metaDati = proIntegrazioni::GetMetedatiProt($codice, $tipo, $tipoCom);
        switch ($metaDati['TipoProtocollo']) {
            case 'Paleo4':
                $model = 'proPaleo4.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proPaleo = new proPaleo4();
                $param = array();
                $param['Docnumber'] = $metaDati['CodiceWS'];
                $arrayDati = $proPaleo->LeggiProtocollo($param);
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Paleo4';
                if ($arrayDati['Status'] != "0") {
                    //return false;
                    return $arrayDati;
                }
                break;
            case 'Paleo41':
                $model = 'proPaleo41.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proPaleo = new proPaleo41();
                $param = array();
                $param['Docnumber'] = $metaDati['CodiceWS'];
                $arrayDati = $proPaleo->LeggiProtocollo($param);
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Paleo41';
                if ($arrayDati['Status'] != "0") {
                    //return false;
                    return $arrayDati;
                }
                break;
            case 'Paleo':
                $model = 'proPaleo.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proPaleo = new proPaleo();
                $arrayDati = $proPaleo->CercaDocumentoProtocollo($metaDati['CodiceWS']);
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Paleo';
                if ($arrayDati['Status'] != "0") {
                    //return false;
                    return $arrayDati;
                }
                break;
            case 'WSPU':
                $model = 'proHWS.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proHWS = new proHWS();
                $param = array();
                $param['numeroDocumento'] = $metaDati['ProNum'];
                $param['annoCompetenza'] = $metaDati['Anno'];
                $arrayDati = $proHWS->CercaDocumentoProtocollo($param);
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'WSPU';
                if ($arrayDati['Status'] != "0") {
                    //return false;
                    return $arrayDati;
                }
                break;
            case 'Infor':
                $model = 'proInforJProtocollo.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proInforJProtocollo = new proInforJProtocollo();
                $param = array();
                $param['DocNumber'] = $metaDati['CodiceWS'];
                $param['Anno'] = $metaDati['Anno'];
                $arrayDati = $proInforJProtocollo->leggiProtocollo($param);
                $allegati = $arrayDati['RetValue']['DocumentiAllegati'];
                foreach ($allegati as $alle) {
                    $arrayDati['RetValue']['DatiProtocollo']['DocumentiAllegati'][] = $alle['nomeFile'];
                }
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Infor';
                if ($arrayDati['Status'] != "0") {
                    //return false;
                    return $arrayDati;
                }
                break;
            case 'Iride':
                $model = 'proIride.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proIride = new proIride();
                $param = array();
                $param['NumeroProtocollo'] = $metaDati['ProNum'];
                $param['AnnoProtocollo'] = $metaDati['Anno'];
                $arrayDati = $proIride->LeggiProtocollo($param);
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Iride';
                if ($arrayDati['Status'] != "0") {
                    //return false;
                    return $arrayDati;
                }
                break;
            case 'Jiride':
                $model = 'proJiride.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proJiride = new proJiride();
                $proJiride->setKeyConfigParams($metaDati['codiceIstanza']);
                $param = array();
                $param['NumeroProtocollo'] = $metaDati['ProNum'];
                $param['AnnoProtocollo'] = $metaDati['Anno'];
                $arrayDati = $proJiride->LeggiProtocollo($param);
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Jiride';
                if ($arrayDati['Status'] != "0") {
                    return $arrayDati;
                    //return false;
                }
                break;
            case 'E-Lios':
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'E-Lios';
                $arrayDati['RetValue']['DatiProtocollo']['NumeroProtocollo'] = $metaDati['ProNum'];
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = $metaDati['Data'];
                $arrayDati['RetValue']['DatiProtocollo']['Anno'] = $metaDati['Anno'];
                break;
            case 'HyperSIC':
                $model = 'proHyperSIC.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proHyperSIC = new proHyperSIC();
                $param = array();
                $param['codice'] = $metaDati['CodiceWS'];
                $arrayDati = $proHyperSIC->GetProtocolloGenerale($param);
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'HyperSIC';
                if ($arrayDati['Status'] != "0") {
                    //return false;
                    return $arrayDati;
                }
                break;
            case 'Manuale':
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Manuale';
                $arrayDati['RetValue']['DatiProtocollo']['NumeroProtocollo'] = $metaDati['Numero'];
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = $metaDati['Data'];
                $arrayDati['RetValue']['DatiProtocollo']['Anno'] = $metaDati['Anno'];
                $arrayDati['RetValue']['DatiProtocollo']['Oggetto'] = $metaDati['Oggetto'];
                $arrayDati['RetValue']['DatiProtocollo']['idMail'] = $metaDati['idMail'];
                $praFascicolo = new praFascicolo($codice);
                $allegati = $praFascicolo->getAllegatiProtocollaPratica();
                foreach ($allegati['Allegati'] as $alle) {
                    $arrayDati['RetValue']['DatiProtocollo']['DocumentiAllegati'][] = $alle['Documento']['Nome'];
                }
                break;
            case 'Italsoft-remoto':
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Italsoft-remoto';
                $arrayDati['RetValue']['DatiProtocollo']['NumeroProtocollo'] = $metaDati['ProNum'];
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = $metaDati['Data'];
                $arrayDati['RetValue']['DatiProtocollo']['Anno'] = $metaDati['Anno'];
                $arrayDati['RetValue']['DatiProtocollo']['Oggetto'] = $metaDati['Oggetto'];

                //
                $ditta = App::$utente->getKey('ditta');
                $accLib = new accLib();
                $enteProtRec_rec = $accLib->GetEnv_Utemeta(App::$utente->getKey('idUtente'), 'codice', 'ITALSOFTPROTREMOTO');
                if ($enteProtRec_rec) {
                    $meta = unserialize($enteProtRec_rec['METAVALUE']);
                    if ($meta['TIPO'] && $meta['DITTA']) {
                        $ditta = $meta['DITTA'];
                    }
                }
                if (!$ditta) {
                    return false;
                }
                if ($tipo == "PRATICA") {
                    $tipoCom = "A";
                }
                $PROTDB = ItaDB::DBOpen('PROT', $ditta);
                $allegati_tab = ItaDB::DBSQLSelect($PROTDB, "SELECT * FROM ANADOC WHERE DOCNUM = '" . $metaDati['Anno'] . $metaDati['ProNum'] . "' AND DOCPAR = '$tipoCom'", true);
                foreach ($allegati_tab as $alle) {
                    $arrayDati['RetValue']['DatiProtocollo']['DocumentiAllegati'][] = $alle['DOCNAME'];
                }
                break;
            case 'Italsoft-ws':
                $model = 'proItalprot.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proItalprot = new proItalprot();
                $param = array();
                $param['NumeroProtocollo'] = $metaDati['ProNum'];
                $param['AnnoProtocollo'] = $metaDati['Anno'];
                $param['Segnatura'] = $metaDati['Segnatura'];
                $arrayDati = $proItalprot->LeggiProtocollo($param);
                if ($arrayDati['Status'] != "0") {
                    //return false;
                    return $arrayDati;
                }
                if ($arrayDati['MsgDestroy']) {
                    Out::msgStop("Token", $arrayDati['MsgDestroy']);
                }
                break;
            case 'Sici':
                $model = 'proSici.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proSici = new proSici();
                $param = array();
                $param['NumeroProtocollo'] = $metaDati['ProNum'];
                $param['AnnoProtocollo'] = $metaDati['Anno'];
                $arrayDati = $proSici->LeggiProtocollo($param);
                if ($arrayDati['Status'] != "0") {
                    //return false;
                    return $arrayDati;
                }
                break;
            case 'Leonardo':
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Leonardo';
                $arrayDati['RetValue']['DatiProtocollo']['NumeroProtocollo'] = $metaDati['ProNum'];
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = $metaDati['Data'];
                $arrayDati['RetValue']['DatiProtocollo']['Anno'] = $metaDati['Anno'];
                break;
            case 'Kibernetes':
                $arrayDati['RetValue']['DatiProtocollo']['TipoProtocollo'] = 'Kibernetes';
                $arrayDati['RetValue']['DatiProtocollo']['NumeroProtocollo'] = $metaDati['ProNum'];
                $arrayDati['RetValue']['DatiProtocollo']['Data'] = $metaDati['Data'];
                $arrayDati['RetValue']['DatiProtocollo']['Anno'] = $metaDati['Anno'];
                break;
            case 'CiviliaNext':
                $model = 'proCiviliaNext.class';
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                include_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';
                $proCiviliaNext = new proCiviliaNext();
                $param = array();
                $param['NumeroProtocollo'] = $metaDati['ProNum'];
                $param['AnnoProtocollo'] = $metaDati['Anno'];
                $param['Docnumber'] = $metaDati['CodiceWS'];
                $arrayDati = $proCiviliaNext->LeggiProtocollo($param);
                if ($arrayDati['Status'] != "0") {
                    //return false;
                    return $arrayDati;
                }
                break;
        }
        return $arrayDati;
    }

    static function updatePracomConNumProt($arrayDati, $codice, $tipoCom) {
        $praLib = new praLib();
        $pracom_rec = ItaDB::DBSQLSelect($praLib->getPRAMDB(), "SELECT * FROM PRACOM WHERE COMPAK = '$codice' AND COMTIP = '$tipoCom'", false);
        $metadati = unserialize($pracom_rec['COMMETA']);
        if ($pracom_rec['COMPRT'] == "") {
            /*
             * Valorizza Numero e data protocollo su PRACOM
             */
            $pracom_rec['COMPRT'] = $arrayDati['RetValue']['DatiProtocollo']['Anno'] . $arrayDati['RetValue']['DatiProtocollo']['NumeroProtocollo'];
            $pracom_rec['COMDPR'] = date("Ymd", strtotime($arrayDati['RetValue']['DatiProtocollo']['Data']));

            /*
             * Valorizzo i metadati con numero e data protocollo
             */
            $metadati['DatiProtocollazione']['proNum']['value'] = $arrayDati['RetValue']['DatiProtocollo']['NumeroProtocollo'];
            $metadati['DatiProtocollazione']['proNum']['status'] = 1;
            $metadati['DatiProtocollazione']['proNum']['msg'] = "";

            $metadati['DatiProtocollazione']['Anno']['value'] = $arrayDati['RetValue']['DatiProtocollo']['Anno'];
            $metadati['DatiProtocollazione']['Anno']['status'] = 1;
            $metadati['DatiProtocollazione']['Anno']['msg'] = "";

            $metadati['DatiProtocollazione']['Data']['value'] = $arrayDati['RetValue']['DatiProtocollo']['Data'];
            $metadati['DatiProtocollazione']['Data']['status'] = 1;
            $metadati['DatiProtocollazione']['Data']['msg'] = "";

            $pracom_rec['COMMETA'] = serialize($metadati);
            try {
                $nrow = ItaDB::DBUpdate($praLib->getPRAMDB(), "PRACOM", "ROWID", $pracom_rec);
                if ($nrow == -1) {
                    return false;
                }
            } catch (Exception $exc) {
                Out::msgStop("Errore", $exc->getMessage());
                return false;
            }
        }
        return true;
    }

}

?>