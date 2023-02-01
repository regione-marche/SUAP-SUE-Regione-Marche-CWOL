<?php

require_once(ITA_LIB_PATH . '/itaPHPAnagrafe/itaSolClient.class.php');
require_once './apps/Sviluppo/devLib.class.php';

class provider_SolWS {

    private $lastExitCode;
    private $lastMessage;

    function __construct() {
        
    }

    function getProviderType() {
        return "SolWS";
    }

    public function getCittadiniLista($ricParam) {
        if (!isset($ricParam['SOLORESIDENTI'])) {
            $ricParam['SOLORESIDENTI'] = true;
        }
        $SolClient = new itaSolClient();
        $this->setClientConfig($SolClient);
        $codiceFiscale = $ricParam['CODICEFISCALE'];
        $cognome = $ricParam['COGNOME'];
        $nome = $ricParam['NOME'];
        $risultato = array();
        if ($codiceFiscale != '') {
            $ret = $SolClient->ws_getSoggetto($codiceFiscale);
            if (!$ret) {
                if ($SolClient->getFault()) {
                    Out::msgStop("Fault", '<pre style="font-size:1.5em">' . $SolClient->getFault() . '</pre>');
                } elseif ($SolClient->getError()) {
                    Out::msgStop("Error", '<pre style="font-size:1.5em">' . $SolClient->getError() . '</pre>');
                }
                return false;
            }
            $risultato[] = $SolClient->getResult();
        } else {
            if ($cognome != '' || $nome != '') {
                $ret = $SolClient->ws_getSoggetto1($cognome, $nome);
                if (!$ret) {
                    if ($SolClient->getFault()) {
                        print_r("Fault", '<pre style="font-size:1.5em">' . $SolClient->getFault() . '</pre>');
                    } elseif ($SolClient->getError()) {
                        print_r("Error", '<pre style="font-size:1.5em">' . $SolClient->getError() . '</pre>');
                    }
                    return false;
                }
                $risultato = $SolClient->getResult();
            } else {
                return false;
            }
        }
        $CittadiniLista = array();
        $ITALWEB_DB = ItaDB::DBOpen('ITALWEBDB', false);
        $ParametriEnte_rec = ItaDB::DBSQLSelect($ITALWEB_DB, "SELECT * FROM PARAMETRIENTE WHERE CODICE='" . App::$utente->getKey('ditta') . "'", false);
        foreach ($risultato as $key => $record) {
            if ($ricParm['SOLORESIDENTI'] == true) {
                if ($record['isAire'] != '') {
                    continue;
                }
                if ($record['dataMorte'] != '') {
                    continue;
                }
            }
            $CittadiniLista[$key]['ROWID'] = '';
            $CittadiniLista[$key]['CODICEUNIVOCO'] = '';
            $CittadiniLista[$key]['COGNOME'] = $record['cognome'];
            $CittadiniLista[$key]['NOME'] = $record['nome'];
            $CittadiniLista[$key]['NOMINATIVO'] = trim($record['cognome']) . " " . trim($record['nome']);
            $CittadiniLista[$key]['DATANASCITA'] = substr($record['dataNascita'], 0, 4) . substr($record['dataNascita'], 5, 2) . substr($record['dataNascita'], 8, 2);
            $CittadiniLista[$key]['CODICEFISCALE'] = $record['codiceFiscale'];
            $CittadiniLista[$key]['LUOGONASCITA'] = $record['luogoNascita'];
            $CittadiniLista[$key]['PROVINCIANASCITA'] = $record['provinciaNascita'];
            $CittadiniLista[$key]['SESSO'] = $record['sesso'];
            $CittadiniLista[$key]['CIVICO'] = $record['civico'];
            $CittadiniLista[$key]['RESIDENZA'] = $ParametriEnte_rec['CITTA'];
            $CittadiniLista[$key]['CAP'] = $ParametriEnte_rec['CAP'];
            $CittadiniLista[$key]['PROVINCIA'] = $ParametriEnte_rec['PROVINCIA'];
            $CittadiniLista[$key]['INDIRIZZO'] = $record['indirizzo'];
            $CittadiniLista[$key]['FAMILY'] = '';
            $CittadiniLista[$key]['PATERNITA'] = '';
            $CittadiniLista[$key]['MATERNITA'] = '';
            $CittadiniLista[$key]['CONIUGE'] = '';
            if ($record['isAire'] != '') {
                $CittadiniLista[$key]['AIRE'] = '1';
            } else {
                $CittadiniLista[$key]['AIRE'] = '';
            }
            //$CittadiniLista[$key]['AIRE'] = $record['isAire'];
            $CittadiniLista[$key]['DATAIMMIGRAZIONE'] = substr($record['dataImmigrazione'], 0, 4) . substr($record['dataImmigrazione'], 5, 2) . substr($record['dataImmigrazione'], 8, 2);
            $CittadiniLista[$key]['DATAEMIGRAZIONE'] = substr($record['dataEmigrazione'], 0, 4) . substr($record['dataEmigrazione'], 5, 2) . substr($record['dataEmigrazione'], 8, 2);
            $CittadiniLista[$key]['DATADECESSO'] = $record['dataMorte'];
            if ($record['dataMorte'] != '') {
                $CittadiniLista[$key]['DECESSO'] = '1';
            } else {
                $CittadiniLista[$key]['DECESSO'] = '';
            }
        }
        return $CittadiniLista;
    }

    function getCittadinoFamiliari($param) {
        return array();
    }

    function getLastExitCode() {
        return $this->lastExitCode;
    }

    function getLastMessage() {
        return $this->lastMessage;
    }

    function setLastMessage() {
        $this->lastMessage = print_r($this->getLastOutput(), true);
    }

    private function setClientConfig($SolClient) {
        $devLib = new devLib();
        $uri = $devLib->getEnv_config('SOLWSCONNECTION', 'codice', 'WSSOLENDPOINT', false);
        $uri2 = $uri['CONFIG'];
        $SolClient->setWebservices_uri($uri2);
        $wsdl = $devLib->getEnv_config('SOLWSCONNECTION', 'codice', 'WSSOLWSDL', false);
        $wsdl2 = $wsdl['CONFIG'];
        $SolClient->setWebservices_wsdl($wsdl2);
        //$SolClient->setWebservices_uri("http://192.168.0.7:8080/TimbroDigitale_Poggio/services/SolWebServices");
        //$SolClient->setWebservices_wsdl("http://192.168.0.7:8080/TimbroDigitale_Poggio/services/SolWebServices?wsdl");
    }

}

?>
