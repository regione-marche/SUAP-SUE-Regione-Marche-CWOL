<?php

class provider_CityWare extends provider_Abstract {

    function __construct() {
        
    }

    function getProviderType() {
        return "CityWare";
    }

    public function getVie() {
        try {
            $ANEL_DB = ItaDB::DBOpen('CITYWARE', '');
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = "Apertura archivio Anagrafe fallita!";
            return false;
        }
        $listaVie = itaDB::DBSQLSelect($ANEL_DB, $this->creaSqlVie(), true);
        $vie = array();
        foreach ($listaVie as $key => $via) {
            $vie[$key]['CODICEVIA'] = $via['CODVIA'];
            $vie[$key]['TOPONIMO'] = $via['TOPONIMO'];
            $vie[$key]['NOMEVIA'] = $via['DESVIA'];
        }
        return $vie;
    }

    public function getCittadiniLista($ricParam) {
        if (!isset($ricParam['SOLORESIDENTI'])) {
            $ricParam['SOLORESIDENTI'] = true;
        }
        try {
            $ANEL_DB = ItaDB::DBOpen('CITYWARE', '');
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = "Apertura archivio Anagrafe fallita!";
            return false;
        }
        if (!$ricParam['RESIDENTICOMPLETI']) {
            if ($ricParam['NOME'] == '' && $ricParam['COGNOME'] == '' && $ricParam['CODICEFISCALE'] == '' && $ricParam['CODICEVIA'] == '' && $ricParam['NATODAL'] == '' && $ricParam['PROGSOGG'] == 0) {
                return false;
            }
        }
        $datiComune = $this->datiEnte($ANEL_DB);

        //Out::msgInfo('',$this->creaSqlAna($ricParam, $ANEL_DB));
        //
        
        try {
            $listaCittadini_tab = itaDB::DBSQLSelect($ANEL_DB, utf8_encode($this->creaSqlAna($ricParam, $ANEL_DB)), true);
        } catch (Exception $exc) {
            Out::msgStop("!ERR", $exc->getMessage());
            return;
        }
//Out::msgInfo('',print_r($listaCittadini_tab,true));
        $listaCittadini = array();
        foreach ($listaCittadini_tab as $key => $record) {
            if ($record['FAMIGLIA'] && trim($record['DESVIA']) . trim($record['DESINDEST']) != '') {
                $listaCittadini[$key]['STATOCIT'] = trim($record['STATOCITB']);
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
                $listaCittadini[$key]['STATOCIVILE'] = $record['DESSTACIV'];
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
                $listaCittadini[$key]['PROFESSIONE'] = $record['DESPROF'];
                $listaCittadini[$key]['TITOLOSTUDIO'] = $record['DESTITSTU'];
                $listaCittadini[$key]['CITTADINANZA'] = $record['CITTADINANZA'];
                $listaCittadini[$key]['CARTAIDENTITA'] = $record['NUMCIDALF'] . $record['NUMCID'];
                $listaCittadini[$key]['CARTAIDENTITARIL'] = substr($record['DTRILCID'], 0, 4) . substr($record['DTRILCID'], 5, 2) . substr($record['DTRILCID'], 8, 2);
                $listaCittadini[$key]['CARTAIDENTITASCA'] = substr($record['DTSCACID'], 0, 4) . substr($record['DTSCACID'], 5, 2) . substr($record['DTSCACID'], 8, 2);
                $listaCittadini[$key]['CODICEPREC'] = $record['CODPROVEN'];
                $listaCittadini[$key]['LUOGOIMMI'] = $record['LUOGO_IMMI'];
                $listaCittadini[$key]['LUOGOEMI'] = $record['LUOGO_EMI'];
                $listaCittadini[$key]['LUOGOMORTE'] = $record['LUOGO_MORTE'];
                $listaCittadini[$key]['RELPAR'] = $record['DESRELPAR'];
            }
        }
        if (!$listaCittadini) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Nessun record trovato";
            return false;
        }
        $this->lastExitCode = 0;
        $this->lastMessage = "";

        //Out::msgInfo('listaCittadini',print_r($listaCittadini,true));        
        //out::msgInfo('record', print_r($record, true));

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

    function getCittadinoVariazioni($param) {
        try {
            $ANEL_DB = ItaDB::DBOpen('CITYWARE', '');
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = "Apertura archivio Anagrafe fallita!";
            return false;
        }
        $variazioniTab = itaDB::DBSQLSelect($ANEL_DB, $this->creaSqlVar($param, $ANEL_DB), true);
        //Out::msgInfo('',print_r($variazioniTab,true));
        $righeVar = array();
        $indice = 0;
        if ($variazioniTab) {
            foreach ($variazioniTab as $variazioniRec) {
                $righeVar[$indice]['DATA'] = substr($variazioniRec['DATAVARDOM'], 8, 2) . '/' . substr($variazioniRec['DATAVARDOM'], 5, 2) . '/' . substr($variazioniRec['DATAVARDOM'], 0, 4);
                $righeVar[$indice]['EVENTO'] = 'VARIAZIONE INDIRIZZO - ' . strtoupper(trim($variazioniRec['DESVIA']) . ' ' . $variazioniRec['NUMCIV']);
                $indice++;
            }
        }
        $variazioniDoc = itaDB::DBSQLSelect($ANEL_DB, $this->creaSqlDoc($param, $ANEL_DB), true);
        if ($variazioniDoc) {
            foreach ($variazioniDoc as $variazioniRec) {
                if ($variazioniRec['DESDOCUM'] != '') {
                    $righeVar[$indice]['DATA'] = substr($variazioniRec['DTRILDOC'], 8, 2) . '/' . substr($variazioniRec['DTRILDOC'], 5, 2) . '/' . substr($variazioniRec['DTRILDOC'], 0, 4);
                    $righeVar[$indice]['EVENTO'] = trim($variazioniRec['DESDOCUM']);
                    if ($variazioniRec['DTSCADOC']) {
                        $righeVar[$indice]['EVENTO'] .= " - Scadenza " . substr($variazioniRec['DTSCADOC'], 8, 2) . '/' . substr($variazioniRec['DTSCADOC'], 5, 2) . '/' . substr($variazioniRec['DTSCADOC'], 0, 4);
                    }
                }
                $indice++;
            }
        }
        $variazioniMatrim = itaDB::DBSQLSelect($ANEL_DB, $this->creaSqlVarMatrim($param), true);
        if ($variazioniMatrim) {
            foreach ($variazioniMatrim as $variazioneRec) {
                $righeVar[$indice]['DATA'] = substr($variazioneRec['DATAMATRI'], 8, 2) . '/' . substr($variazioneRec['DATAMATRI'], 5, 2) . '/' . substr($variazioneRec['DATAMATRI'], 0, 4);
                $righeVar[$indice]['EVENTO'] = 'MATRIMONIO CON  - ' . strtoupper(trim($variazioneRec['CONI_C']) . ' ' . $variazioneRec['CONI_N']);
                $indice++;
            }
        }
        $variazioniDivorzio = itaDB::DBSQLSelect($ANEL_DB, $this->creaSqlVarDivorzio($param), true);
        if ($variazioniDivorzio) {
            foreach ($variazioniDivorzio as $variazioneRec) {
                $righeVar[$indice]['DATA'] = substr($variazioneRec['DATADIVOR'], 8, 2) . '/' . substr($variazioneRec['DATADIVOR'], 5, 2) . '/' . substr($variazioneRec['DATADIVOR'], 0, 4);
                $righeVar[$indice]['EVENTO'] = 'DIVORZIO DA  - ' . strtoupper(trim($variazioneRec['CONI_C']) . ' ' . $variazioneRec['CONI_N']);
                $indice++;
            }
        }
        $variazioniVedovanza = itaDB::DBSQLSelect($ANEL_DB, $this->creaSqlVarVedovanza($param), true);
        if ($variazioniVedovanza) {
            foreach ($variazioniVedovanza as $variazioneRec) {
                $righeVar[$indice]['DATA'] = substr($variazioneRec['DATAVEDOV'], 8, 2) . '/' . substr($variazioneRec['DATAVEDOV'], 5, 2) . '/' . substr($variazioneRec['DATAVEDOV'], 0, 4);
                $righeVar[$indice]['EVENTO'] = 'VEDOVO/A DI  - ' . strtoupper(trim($variazioneRec['CONI_C']) . ' ' . $variazioneRec['CONI_N']);
                $indice++;
            }
        }
        //Out::msgInfo('', print_r($variazioniDoc, true));
        return $righeVar;
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

    private function creaSqlAna($ricParm, $ANEL_DB) {
        $sql = "SELECT f.statocit, f.statocitb , rp.desrelpar, sc.desstaciv, n.desnazi AS res_estera,
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
                       lmo.deslocal AS luogo_morte,
                       cid.numcidalf, cid.numcid,  cid.dtrilcid,cid.dtscacid,
                       ex.codproven,
                       a.*  FROM cityware.dan_anagra_v01 a 
                    LEFT JOIN cityware.dta_flagan f ON a.famiglia_t = f.famiglia_t AND a.motivo_c = f.motivo_c
                    LEFT JOIN cityware.dta_relpar rp ON a.relpar = rp.relpar AND a.sesso = rp.sesso
                    LEFT JOIN cityware.dsc_nasci na ON a.progsogg = na.progsogg
                    LEFT JOIN cityware.dsc_morte mo ON a.progsogg = mo.progsogg
                    LEFT JOIN cityware.dta_staciv sc ON a.staciv = sc.staciv
                    LEFT JOIN cityware.bta_local le ON a.estnaz = le.codnazpro AND a.estloc = le.codlocal
                    LEFT JOIN cityware.bta_nazion n ON le.istnazpro = n.codna_ist
                    LEFT JOIN cityware.dan_immi i ON a.progsogg = i.progsogg AND a.dataimmi = i.dataimmi
                    LEFT JOIN cityware.bta_local lim ON i.immi_pro = lim.codnazpro AND i.immi_loc = lim.codlocal
                    LEFT JOIN cityware.dan_emi e ON a.progsogg = e.progsogg AND a.dataemi = e.dataemi
                    LEFT JOIN cityware.bta_local lem ON e.emi_pro = lem.codnazpro AND e.emi_loc = lem.codlocal
                    LEFT JOIN cityware.bta_local lmo ON mo.mor_pro = lmo.codnazpro AND mo.mor_loc = lmo.codlocal
                    LEFT JOIN cityware.bta_prof pr ON a.codprof = pr.codprof
                    LEFT JOIN cityware.dta_titstu ts ON a.codtitstu = ts.codtitstu
                    LEFT JOIN cityware.dan_carte cid ON a.progsogg = cid.progsogg 
                        AND dtrilcid = (SELECT MAX(dtrilcid) AS m FROM cityware.dan_carte WHERE progsogg = a.progsogg  AND tipcarta = 'CI')
                        AND numcid = (SELECT MAX(numcid) AS m FROM cityware.dan_carte WHERE progsogg = a.progsogg  AND tipcarta = 'CI' AND s_docum = 1
                        AND dtrilcid = (SELECT MAX(dtrilcid) AS m FROM cityware.dan_carte WHERE progsogg = a.progsogg  AND tipcarta = 'CI'))
                    LEFT JOIN cityware.xb_key ex ON a.progsogg = ex.progsogg";

        if ($ricParm['RESIDENTICOMPLETI']) {
            $where = " WHERE f.statocit ='Residente'";
        } else {

            if ($ricParm['CODICEFISCALE'] || $ricParm['PROGSOGG']) {
                if ($ricParm['CODICEFISCALE']) {
                    $where = " WHERE a.codfiscale ='" . $ricParm['CODICEFISCALE'] . "'";
                }
                if ($ricParm['PROGSOGG']) {
                    $where = " WHERE a.progsogg = " . $ricParm['PROGSOGG'];
                }
            } else {
                if ($ricParm['CODICEVIA']) {
                    $where = " WHERE a.codvia =" . $ricParm['CODICEVIA'];
                    if ($ricParm['DALCIVICO']) {
                        $where .= " AND a.numciv >= " . $ricParm['DALCIVICO'];
                    }
                    if ($ricParm['ALCIVICO']) {
                        $where .= " AND a.numciv <= " . $ricParm['ALCIVICO'];
                    }
                } else {
                    if ($ricParm['NATODAL'] != '' && $ricParm['NATOAL'] != '') {
                        $where .= " WHERE (a.anno * 10000 + a.mese * 100 + a.giorno) >= " . $ricParm['NATODAL'] . " AND (a.anno * 10000 + a.mese * 100 + a.giorno) <= " . $ricParm['NATOAL'];
                    } else {
//                        $arrNome[] = addslashes(trim($ricParm['COGNOME']));
//                        $arrNome[] = addslashes(trim($ricParm['NOME']));
                        $arrNome[] = trim($ricParm['COGNOME']);
                        $arrNome[] = trim($ricParm['NOME']);
                        if ($arrNome[0] != '' && $arrNome[1] != '') {
                            $where .= " WHERE upper(a.cognome) LIKE '" . pg_escape_string($arrNome[0]) . "' AND upper(a.nome) = '" . pg_escape_string($arrNome[1]) . "'";
                        }
                        if ($arrNome[0] != '' && $arrNome[1] == '') {
                            $where .= " WHERE upper(a.cognome) LIKE '" . pg_escape_string($arrNome[0]) . "'";
                        }
                        if ($arrNome[0] == '' && $arrNome[1] != '') {
                            $where .= " WHERE upper(a.nome) = '" . pg_escape_string($arrNome[1]) . "'";
                        }
                    }
                }
            }
        }
        $sql .= " AND a.famiglia_t IN ('AN', 'CV', 'FE')";
        $order = " ORDER BY a.cognome, a.nome";
//        Out::msgInfo('',$sql . $where . $order);
        return $sql . $where . $order;
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

    private function creaSqlVar($ricParm, $ANEL_DB) {
        $sql = "SELECT d.* FROM dan_vardom_v01 d 
                    WHERE progsogg = " . $ricParm['CODICEUNIVOCO'];
        return $sql;
    }

    private function creaSqlDoc($ricParm, $ANEL_DB) {
        $sql = "SELECT doc.desdocum, doc.numdocum, doc.dtrildoc, doc.dtscadoc
                       FROM cityware.dan_docum 
		       LEFT JOIN cityware.dta_pers per ON per.chiave = '1'
                       LEFT JOIN cityware.dan_docum_v01 doc ON dan_docum.progsogg = doc.progsogg AND doc.tipdoc IN(per.ps_tipdoc, per.cs_tipdoc, ri_tipdoc) AND doc.s_docum = 1
                       WHERE dan_docum.progsogg = " . $ricParm['CODICEUNIVOCO'];
        return $sql;
    }

    private function creaSqlVarMatrim($ricParm) {
        $sql = "SELECT * FROM dsc_matri WHERE progsogg = " . $ricParm['CODICEUNIVOCO'];
        return $sql;
    }

    private function creaSqlVarDivorzio($ricParm) {
        $sql = "SELECT * FROM dsc_divor WHERE progsogg = " . $ricParm['CODICEUNIVOCO'];
        return $sql;
    }

    private function creaSqlVarVedovanza($ricParm) {
        $sql = "SELECT * FROM dan_vedov WHERE progsogg = " . $ricParm['CODICEUNIVOCO'];
        return $sql;
    }

    private function creaSqlVie() {
        $sql = "SELECT v.* FROM bta_vie v 
                    WHERE flag_dis = 0 ORDER BY toponimo,desvia";
        return $sql;
    }

    public function datiEnte($ANEL_DB) {
        $sql = "SELECT * FROM bor_client";
        $datiEnte = itaDB::DBSQLSelect($ANEL_DB, $sql, false);
        return $datiEnte;
    }

    public function getStatoMatrimonio($ricParam) {
        $statoMatrimonio = array();
        try {
            $ANEL_DB = ItaDB::DBOpen('CITYWARE', '');
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = "Apertura archivio Anagrafe fallita!";
            return false;
        }
        if ($ricParam['CODICEUNIVOCO'] == '' || $ricParam['ALLADATA'] == '') {
            return false;
        }
        //
        //  CONTROLLO SU TABELLA MATRIMONIO
        //
        $sql = "SELECT MAX(datamatri) AS datamatri FROM dsc_matri WHERE progsogg = " . $ricParam['CODICEUNIVOCO'] . " AND datamatri <= '" . $ricParam['ALLADATA'] . "'";
        try {
            $result = itaDB::DBSQLSelect($ANEL_DB, $sql, false);
        } catch (Exception $exc) {
            Out::msgStop("!ERR", $exc->getMessage());
            return;
        }
        $dataMatrimonio = $result['DATAMATRI'];
        //
        //  CONTROLLO SU TABELLA DIVORZIO
        //
        $sql = "SELECT MAX(datadivor) AS datadivor FROM dsc_divor WHERE progsogg = " . $ricParam['CODICEUNIVOCO'] . " AND datadivor <= '" . $ricParam['ALLADATA'] . "'";
        try {
            $result = itaDB::DBSQLSelect($ANEL_DB, $sql, false);
        } catch (Exception $exc) {
            Out::msgStop("!ERR", $exc->getMessage());
            return;
        }
        $dataDivorzio = $result['DATADIVOR'];
        //
        //  CONTROLLO SU TABELLA VEDOVANZA
        //
        $sql = "SELECT MAX(datavedov) AS datavedov FROM dan_vedov WHERE progsogg = " . $ricParam['CODICEUNIVOCO'] . " AND datavedov <= '" . $ricParam['ALLADATA'] . "'";
        try {
            $result = itaDB::DBSQLSelect($ANEL_DB, $sql, false);
        } catch (Exception $exc) {
            Out::msgStop("!ERR", $exc->getMessage());
            return;
        }
        $dataVedovanza = $result['DATAVEDOV'];
        //
        $statoMatrimonio['DATAMATRIMONIO'] = $dataMatrimonio;
        $statoMatrimonio['DATADIVORZIO'] = $dataDivorzio;
        $statoMatrimonio['DATAVEDOVANZA'] = $dataVedovanza;
        return $statoMatrimonio;
    }

}

?>
