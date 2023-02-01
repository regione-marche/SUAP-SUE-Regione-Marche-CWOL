<?php

require_once(ITA_LIB_PATH . '/itaPHPAnagrafe/itaSolClient.class.php');
require_once(ITA_LIB_PATH . '/apps/Sviluppo/devLib.class.php');

class provider_SolWS extends provider_Abstract {

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
            $ret = $SolClient->ws_getSoggettoCompleto($codiceFiscale);
            
            
            
            
            if (!$ret) {
                if ($SolClient->getFault()) {
                    print_r("Fault", '<pre style="font-size:1.5em">' . $SolClient->getFault() . '</pre>');
                } elseif ($SolClient->getError()) {
                    print_r("Error", '<pre style="font-size:1.5em">' . $SolClient->getError() . '</pre>');
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
            $CittadiniLista[$key]['CODICEUNIVOCO'] = $record['codiceAnagrafico'];
            $CittadiniLista[$key]['COGNOME'] = $record['cognome'];
            $CittadiniLista[$key]['NOME'] = $record['nome'];
            $CittadiniLista[$key]['NOMINATIVO'] = trim($record['cognome']) . " " . trim($record['nome']);
            $CittadiniLista[$key]['DATANASCITA'] = substr($record['dataNascita'], 0, 4) . substr($record['dataNascita'], 5, 2) . substr($record['dataNascita'], 8, 2);
            $CittadiniLista[$key]['CODICEFISCALE'] = $record['codiceFiscale'];
            $CittadiniLista[$key]['PATERNITA'] = trim($record['cognomePadre']) . ' ' . trim($record['nomePadre']);
            $CittadiniLista[$key]['MATERNITA'] = trim($record['cognomeMadre']) . ' ' . trim($record['nomeMadre']);
            $CittadiniLista[$key]['LUOGONASCITA'] = $record['luogoNascita'];
            $CittadiniLista[$key]['PROVINCIANASCITA'] = $record['provinciaNascita'];
            $CittadiniLista[$key]['SESSO'] = $record['sesso'];
            $CittadiniLista[$key]['CIVICO'] = $record['civico'];
            $CittadiniLista[$key]['RESIDENZA'] = $ParametriEnte_rec['CITTA'];
            $CittadiniLista[$key]['CAP'] = $ParametriEnte_rec['CAP'];
            $CittadiniLista[$key]['PROVINCIA'] = $ParametriEnte_rec['PROVINCIA'];
            $CittadiniLista[$key]['INDIRIZZO'] = $record['indirizzo'];
            $CittadiniLista[$key]['FAMILY'] = $record['codiceFamiglia'];
            $CittadiniLista[$key]['TIPOFAMILY'] = $record['tipoApp'];
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
            $CittadiniLista[$key]['PROFESSIONE'] = $record['dsProfessione'];
            $CittadiniLista[$key]['TITOLOSTUDIO'] = $record['dsTitoloStudio'];
            $CittadiniLista[$key]['CITTADINANZA'] = $record['cittadinanza'];
            $CittadiniLista[$key]['CARTAIDENTITA'] = $record['numeroCartaIdentita'];
            $CittadiniLista[$key]['CARTAIDENTITARIL'] = substr($record['dtEmissioneCartaIdentita'], 0, 4) . substr($record['dtEmissioneCartaIdentita'], 5, 2) . substr($record['dtEmissioneCartaIdentita'], 8, 2);
            $CittadiniLista[$key]['CARTAIDENTITASCA'] = substr($record['dtScadenzaCartaIdentita'], 0, 4) . substr($record['dtScadenzaCartaIdentita'], 5, 2) . substr($record['dtScadenzaCartaIdentita'], 8, 2);
            $CittadiniLista[$key]['CODICEPREC'] = '';
            $CittadiniLista[$key]['LUOGOIMMI'] = $record['luogoImmigrazione'];
            $CittadiniLista[$key]['LUOGOEMI'] = $record['luogoEmigrazione'];
            $CittadiniLista[$key]['STATOCIT'] = $record['dsTipoApp'];
            $CittadiniLista[$key]['CODICEVIA'] = $record['indCodiceVia'];
        }
        return $CittadiniLista;
    }

    function getCittadinoFamiliari($ricParam) {
        $SolClient = new itaSolClient();
        $this->setClientConfig($SolClient);
        $codiceFiscale = $ricParam['CODICEFISCALE'];
        //$tipoApp[0] = $ricParam['TIPOFAMILY'];
        $tipoApp = array();
        $anagraTab = array();
        //if ($codiceFiscale != '' && $tipoApp != '') {
        if ($codiceFiscale != '') {
            $ret = $SolClient->ws_getComponenti($codiceFiscale, $tipoApp);
            
            
            if (!$ret) {
                if ($SolClient->getFault()) {
                    Out::msgStop("Fault", '<pre style="font-size:1.5em">' . $SolClient->getFault() . '</pre>');
                } elseif ($SolClient->getError()) {
                    Out::msgStop("Error", '<pre style="font-size:1.5em">' . $SolClient->getError() . '</pre>');
                }
                return false;
            }
            $anagraTab = $SolClient->getResult();
        }
        if ($anagraTab) {
            $righeFam = array();
            $indice = 0;
            foreach ($anagraTab as $anagraRec) {
                $righeFam[$indice]['CODICEFISCALE'] = $anagraRec['codiceFiscale'];
                $righeFam[$indice]['COGNOM'] = $anagraRec['cognome'];
                $righeFam[$indice]['NOME'] = $anagraRec['nome'];
//                $righeFam[$indice]['PARENTELA'] = $anagraRec['indrelaz'];
//                $righeFam[$indice]['STATOCIVILE'] = $anagraRec['dsStatoCivile'];
                $righeFam[$indice]['DATNAT'] = substr($anagraRec['dataNascita'], 8, 2) . '/' . substr($anagraRec['dataNascita'], 5, 2) . '/' . substr($anagraRec['dataNascita'], 0, 4);
                $ret = $SolClient->ws_getSoggettoCompleto($anagraRec['codiceFiscale']);
                if ($ret) {
                    $risultato = $SolClient->getResult();
                    if ($risultato['dataMorte']) {
                        $righeFam[$indice]['NOTE'] = 'Deceduto il ' . substr($risultato['dataMorte'], 8, 2) . '/' . substr($risultato['dataMorte'], 5, 2) . '/' . substr($risultato['dataMorte'], 0, 4);
                    }
                    if ($risultato['dataEmigrazione']) {
                        $dataEmi = $risultato['dataEmigrazione'];
                        $righeFam[$indice]['NOTE'] = 'Emigrato ' . $risultato['luogoEmigrazione'] . ' il ' . $dataEmi;
                    }
                    if ($risultato['dataImmigrazione']) {
                        $righeFam[$indice]['NOTE'] = $righeFam[$indice]['NOTE'] . ' Immigrato il ' . substr($risultato['dataImmigrazione'], 8, 2) . '/' . substr($risultato['dataImmigrazione'], 5, 2) . '/' . substr($risultato['dataImmigrazione'], 0, 4);
                    }
                    $righeFam[$indice]['DATAEMIGRAZIONE'] = substr($risultato['dataEmigrazione'], 0, 4) . substr($risultato['dataEmigrazione'], 5, 2) . substr($risultato['dataEmigrazione'], 8, 2);
                    $righeFam[$indice]['DATAIMMIGRAZIONE'] = substr($risultato['dataImmigrazione'], 0, 4) . substr($risultato['dataImmigrazione'], 5, 2) . substr($risultato['dataImmigrazione'], 8, 2);
                    $righeFam[$indice]['DATAMORTE'] = substr($risultato['dataMorte'], 0, 4) . substr($risultato['dataMorte'], 5, 2) . substr($risultato['dataMorte'], 8, 2);
                    $righeFam[$indice]['PARENTELA'] = $risultato['indrelaz'];
                    $righeFam[$indice]['STATOCIVILE'] = $risultato['dsStatoCivile'];
                }
                $indice = $indice + 1;
            }
        }
        return $righeFam;
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
//        $SolClient->setWebservices_uri("http://195.135.200.38/TimbroDigitale/services/SolWebServices");
//        $SolClient->setWebservices_wsdl("http://195.135.200.38/TimbroDigitale/services/SolWebServices?wsdl");
    }

    public function getVie() {
        return $vie;
    }

    public function getCittadinoVariazioni() {
        return $righeVar;
    }

}

?>
