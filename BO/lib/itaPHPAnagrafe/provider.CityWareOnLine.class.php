
<?php

require_once ITA_BASE_PATH . '/apps/CityBase/cwbLib.class.php';
require_once ITA_BASE_PATH . '/apps/CityPeople/cwdLibDB_DAN.class.php';

class provider_CityWareOnLine extends provider_Abstract {

    public $libDB_DAN;

    function __construct() {
        $this->libDB_DAN = new cwdLibDB_DAN();
    }

    function getProviderType() {
        return "CityWareOnLine";
    }

    public function getCittadiniLista($ricParam) {

        switch ($ricParam['OPERAZIONE']) {
            case 'CONSULTA':
                if ($ricParam['CODICEFISCALE']) {
                    $risultato_tab = array();
                    $filtri['CODFISCALE'] = $ricParam['CODICEFISCALE'];
                    $filtri['APR'] = 1;
                    $filtri['AIRE'] = 1;
                    $filtri['TEMP'] = 1;
                    $filtri['NRESI'] = 1;
                    $risultato_tab = $this->libDB_DAN->leggiDanAnagraV01($filtri, '', true);
                    if ($risultato_tab) {
                        $externalFilter = array();
                        $externalFilter['CODFISCALE'] = array("PERMANENTE" => false, "VALORE" => $ricParam['CODICEFISCALE'], 'HTMLELEMENT' => 'ricCodFiscale');
                        cwbLib::apriFinestraRicerca('cwdDanRicerca', $this->returnModel, 'returnFromDanRicerca', $_POST['id'], true, $externalFilter, $this->returnModel);
                    }
                    Out::closeDialog('utiVediAnel');
                    return $this->elaboraRecord($risultato_tab);  // PER EVITARE MESSAGGIO DI UTIVEDIANEL DI CODICE FISCALE NON TROVATO
                } if ($ricParam['PROGSOGG']) {
                    $filtri['PROGSOGG'] = $ricParam['PROGSOGG']; //Prendo anche i dati da anpr, così come si fa dal suggest
                    //$risultato_tab = $this->libDB_DAN->leggiDanAnagraANPRV01($filtri, array(), false, true);
                    $risultato_tab = $this->libDB_DAN->leggiDanAnagraV01($filtri, '', true);
                    $soggetto = $this->elaboraRecord($risultato_tab);
                    return $soggetto[0];
                } else {
                    $externalFilter = array();
                    cwbLib::apriFinestraRicerca('cwdDanRicerca', 'utiVediAnel', 'returnFromDanRicerca', $_POST['id'], false, $externalFilter, $this->returnModel);
                    Out::closeDialog('utiVediAnel');
                    $risultato_tab[0] = array('PROGSOGG' => 0);  // PER EVITARE MESSAGGIO DI UTIVEDIANEL DI CODICE FISCALE NON TROVATO
                    return $risultato_tab;
                }
                break;
            case 'RETURNDATI':
                if ($ricParam['CODICEFISCALE']) {
                    $risultato_tab = array();
                    $filtri['CODFISCALE'] = $ricParam['CODICEFISCALE'];
                    $filtri['APR'] = 1;
                    $filtri['AIRE'] = 1;
                    $filtri['TEMP'] = 1;
                    $filtri['NRESI'] = 1;
                    $risultato_tab = $this->libDB_DAN->leggiDanAnagraV01($filtri, '', true);
                    Out::closeDialog('utiVediAnel');
                    return $this->elaboraRecord($risultato_tab);
                }
                break;
        }
    }

    public function elaboraRecord($risultato_tab) {

        try {
            $ANEL_DB = ItaDB::DBOpen('CITYWARE', '');
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = "Apertura archivio Anagrafe fallita!";
            return false;
        }

        $sql = "SELECT * FROM bor_client";
        $datiComune = itaDB::DBSQLSelect($ANEL_DB, $sql, false);

        foreach ($risultato_tab as $key => $record) {
            if ($record['FAMIGLIA'] && trim($record['DESVIA']) . trim($record['DESINDEST']) != '') {
                $listaCittadini[$key]['CODICEUNIVOCO'] = $record['PROGSOGG'];
                $listaCittadini[$key]['COGNOME'] = $record['COGNOME'];
                $listaCittadini[$key]['NOME'] = $record['NOME'];
                $listaCittadini[$key]['NOMINATIVO'] = $record['COGNOME'] . ' ' . $record['NOME'];
                $listaCittadini[$key]['DATANASCITA'] = $record['ANNO'] * 10000 + $record['MESE'] * 100 + $record['GIORNO'];
                $listaCittadini[$key]['DATADECESSO'] = substr($record['DATAMORTE'], 0, 4) . substr($record['DATAMORTE'], 5, 2) . substr($record['DATAMORTE'], 8, 2);
                $listaCittadini[$key]['CODICEFISCALE'] = $record['CODFISCALE'];
                $listaCittadini[$key]['PATERNITA'] = trim($record['PATER_C']) . ' ' . trim($record['PATER_N']);
                $listaCittadini[$key]['MATERNITA'] = trim($record['MATER_C']) . ' ' . trim($record['MATER_N']);
                $listaCittadini[$key]['LUOGONASCITA'] = $record['DESLOCAL'];
                $listaCittadini[$key]['PROVINCIANASCITA'] = $record['PROVINCIA'];
                $listaCittadini[$key]['SESSO'] = $record['SESSO'];
                $listaCittadini[$key]['CIVICO'] = $record['NUMCIV'] . $record['SUBNCIV'];
                $listaCittadini[$key]['FAMILY'] = $record['FAMIGLIA'];
                $listaCittadini[$key]['TIPOFAMILY'] = $record['FAMIGLIA_T'];
                if (trim($record['CONI_C']) != '') {
                    $listaCittadini[$key]['CONIUGE'] = $record['CONI_C'] . '-' . $record['CONI_N'];
                    $listaCittadini[$key]['DATAMATRIMONIO'] = substr($record['DATAMATRI'], 0, 4) . substr($record['DATAMATRI'], 5, 2) . substr($record['DATAMATRI'], 8, 2);
                } else {
                    $listaCittadini[$key]['CONIUGE'] = '';
                    $listaCittadini[$key]['DATAMATRIMONIO'] = '';
                }
                if ($record['FAMIGLIA_T'] == 'FE') {
                    $listaCittadini[$key]['INDIRIZZO'] = trim($record['DESINDEST']) . '-' . trim($record['INDEST2']);
                    $listaCittadini[$key]['AIRE'] = '1';
                    $listaCittadini[$key]['RESIDENZA'] = '';
                    $listaCittadini[$key]['CAP'] = '';
                    $listaCittadini[$key]['PROVINCIA'] = '';
                } else {
                    $listaCittadini[$key]['INDIRIZZO'] = strtoupper(trim($record['TOPONIMO']) . ' ' . trim($record['DESVIA']));
                    $listaCittadini[$key]['AIRE'] = '';
                    $listaCittadini[$key]['RESIDENZA'] = trim($datiComune['DESLOCAL']);
                    $listaCittadini[$key]['CAP'] = trim($datiComune['CAP']);
                    $listaCittadini[$key]['PROVINCIA'] = trim($datiComune['PROVINCIA']);
                }
                $listaCittadini[$key]['CODICEVIA'] = $record['CODVIA'];
                $listaCittadini[$key]['DATAEMIGRAZIONE'] = substr($record['DATAEMI'], 0, 4) . substr($record['DATAEMI'], 5, 2) . substr($record['DATAEMI'], 8, 2);
                $listaCittadini[$key]['DATAIMMIGRAZIONE'] = substr($record['DATAIMMI'], 0, 4) . substr($record['DATAIMMI'], 5, 2) . substr($record['DATAIMMI'], 8, 2);
            }
        }
        return $listaCittadini;
    }

    function getCittadinoFamiliari($param) {
        try {
            $ANEL_DB = ItaDB::DBOpen('CITYWARE', '');
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = "Apertura archivio Anagrafe fallita!";
            return false;
        }
        $anagraTab = itaDB::DBSQLSelect($ANEL_DB, utf8_encode($this->creaSqlFam($param, $ANEL_DB)), true);
        $righeFam = array();
        //Out::msgInfo('',print_r($anagraTab,true));
        $indice = 0;
        if ($anagraTab) {
            foreach ($anagraTab as $anagraRec) {
                $righeFam[$indice]['CODICEFISCALE'] = $anagraRec['CODFISCALE'];
                $righeFam[$indice]['CODICEUNIVOCO'] = $anagraRec['PROGSOGG'];
                $righeFam[$indice]['COGNOM'] = $anagraRec['COGNOME'];
                $righeFam[$indice]['NOME'] = $anagraRec['NOME'];
                $righeFam[$indice]['PARENTELA'] = $anagraRec['DESRELPAR'];
                $righeFam[$indice]['STATOCIVILE'] = $anagraRec['DESSTACIV'];
                $righeFam[$indice]['DATNAT'] = sprintf("%02d", $anagraRec['GIORNO']) . '/' . sprintf("%02d", $anagraRec['MESE']) . '/' . sprintf("%04d", $anagraRec['ANNO']);
                $righeFam[$indice]['DATANASCITA'] = $anagraRec['ANNO'] * 10000 + $anagraRec['MESE'] * 100 + $anagraRec['GIORNO'];
                $righeFam[$indice]['STATOCIT'] = trim($anagraRec['STATOCIT']);
                $righeFam[$indice]['LUOGONASCITA'] = $anagraRec['DESLOCAL'];

                if ($anagraRec['DATAMORTE']) {
                    $righeFam[$indice]['NOTE'] = $righeFam[$indice]['NOTE'] . ' Deceduto il ' . substr($anagraRec['DATAMORTE'], 8, 2) . '/' . substr($anagraRec['DATAMORTE'], 5, 2) . '/' . substr($anagraRec['DATAMORTE'], 0, 4);
                }

                if ($anagraRec['DATAEMI']) {
                    $dataEmi = $anagraRec['DATAEMI'];
                    $righeFam[$indice]['NOTE'] = $righeFam[$indice]['NOTE'] . ' Emigrato ' . $anagraRec['LUOGO_EMI'] . ' il ' . $dataEmi;
//                    if ($record['FAMIGLIA_T'] == 'FE') {
//                        $righeFam[$indice]['NOTE'] = 'Emigrato ' . $anagraRec['LUOGO_EMI'] . ' il ' . $dataEmi;
//                    } else {
//                        $righeFam[$indice]['NOTE'] = trim($anagraRec['STATOCITB']) . ' a ' . trim($anagraRec['LUOGO_EMI']) . ' il ' . $dataEmi;
//                    }
                }

                if ($anagraRec['DATAIMMI']) {
                    $righeFam[$indice]['NOTE'] = $righeFam[$indice]['NOTE'] . ' Immigrato il ' . substr($anagraRec['DATAIMMI'], 8, 2) . '/' . substr($anagraRec['DATAIMMI'], 5, 2) . '/' . substr($anagraRec['DATAIMMI'], 0, 4);
                }
                $righeFam[$indice]['DATAEMIGRAZIONE'] = substr($anagraRec['DATAEMI'], 0, 4) . substr($anagraRec['DATAEMI'], 5, 2) . substr($anagraRec['DATAEMI'], 8, 2);
                $righeFam[$indice]['DATAIMMIGRAZIONE'] = substr($anagraRec['DATAIMMI'], 0, 4) . substr($anagraRec['DATAIMMI'], 5, 2) . substr($anagraRec['DATAIMMI'], 8, 2);
                $righeFam[$indice]['DATAMORTE'] = substr($anagraRec['DATAMORTE'], 0, 4) . substr($anagraRec['DATAMORTE'], 5, 2) . substr($anagraRec['DATAMORTE'], 8, 2);
                $indice = $indice + 1;
            }
        }
        return $righeFam;
    }

    private function creaSqlFam($ricParm, $ANEL_DB) {
//        $sql = "SELECT f.statocitb , rp.desrelpar, sc.desstaciv, n.desnazi AS res_estera,
//                       a.*  FROM dan_anagra_v01 a 
//                    LEFT JOIN dta_flagan f ON a.famiglia_t = f.famiglia_t AND a.motivo_c = f.motivo_c
//                    LEFT JOIN dta_relpar rp ON a.relpar = rp.relpar AND a.sesso = rp.sesso
//                    LEFT JOIN dta_staciv sc ON a.staciv = sc.staciv
//                    LEFT JOIN bta_local le ON a.estnaz = le.codnazpro AND a.estloc = le.codlocal
//                    LEFT JOIN bta_nazion n ON le.istnazpro = n.codna_ist
//                    WHERE a.famiglia =" . $ricParm['FAMILY'];

        $sql = "SELECT f.statocit, f.statocitb, rp.desrelpar, sc.desstaciv, n.desnazi AS res_estera,
                       na.pater_c, na.pater_n, na.mater_c, na.mater_n,
                       pr.desprof, ts.destitstu,
                       a.desnazi AS cittadinanza,
                       CASE WHEN i.flag_uff = 1 THEN 'Iscr.altri motivi'
                            WHEN i.flag_ric = 1 THEN 'Ricomparsa'
                            ELSE lim.deslocal 
                       END AS luogo_immi,
                       CASE WHEN e.flag_uff = 1 THEN 'Canc.altri motivi'
                            WHEN e.flag_irr = 1 THEN 'Irreperibilità'
                            ELSE lem.deslocal 
                       END AS luogo_emi,
                       cid.numcidalf, cid.numcid,  cid.dtrilcid,cid.dtscacid,
                       ex.codproven,
                       a.*  FROM cityware.dan_anagra_v01 a 
                    LEFT JOIN cityware.dta_flagan f ON a.famiglia_t = f.famiglia_t AND a.motivo_c = f.motivo_c
                    LEFT JOIN cityware.dta_relpar rp ON a.relpar = rp.relpar AND a.sesso = rp.sesso
                    LEFT JOIN cityware.dsc_nasci na ON a.progsogg = na.progsogg
                    LEFT JOIN cityware.dta_staciv sc ON a.staciv = sc.staciv
                    LEFT JOIN cityware.bta_local le ON a.estnaz = le.codnazpro AND a.estloc = le.codlocal
                    LEFT JOIN cityware.bta_nazion n ON le.istnazpro = n.codna_ist
                    LEFT JOIN cityware.dan_immi i ON a.progsogg = i.progsogg AND a.dataimmi = i.dataimmi
                    LEFT JOIN cityware.bta_local lim ON i.immi_pro = lim.codnazpro AND i.immi_loc = lim.codlocal
                    LEFT JOIN cityware.dan_emi e ON a.progsogg = e.progsogg AND a.dataemi = e.dataemi
                    LEFT JOIN cityware.bta_local lem ON e.emi_pro = lem.codnazpro AND e.emi_loc = lem.codlocal
                    LEFT JOIN cityware.bta_prof pr ON a.codprof = pr.codprof
                    LEFT JOIN cityware.dta_titstu ts ON a.codtitstu = ts.codtitstu
                    LEFT JOIN cityware.dan_carte cid ON a.progsogg = cid.progsogg 
                        AND dtrilcid = (SELECT MAX(dtrilcid) AS m FROM cityware.dan_carte WHERE progsogg = a.progsogg  AND tipcarta = 'CI')
                        AND numcid = (SELECT MAX(numcid) AS m FROM cityware.dan_carte WHERE progsogg = a.progsogg  AND tipcarta = 'CI' AND s_docum = 1
                        AND dtrilcid = (SELECT MAX(dtrilcid) AS m FROM cityware.dan_carte WHERE progsogg = a.progsogg  AND tipcarta = 'CI'))
                    LEFT JOIN cityware.xb_key ex ON a.progsogg = ex.progsogg
                    WHERE a.famiglia =" . $ricParm['FAMILY'];

        $sql .= " AND a.famiglia_t = '" . $ricParm['TIPOFAMILY'] . "'";
        $order = " ORDER BY a.cognome, a.nome";
        return $sql . $where . $order;
    }

}

?>
