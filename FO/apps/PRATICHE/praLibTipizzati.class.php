<?php

class praLibTipizzati {

    public $praErr;
    public $PRAM_DB;

    function __construct($libErr = null) {
        
    }

    function __destruct() {
        
    }

    public function decodificaTipizzato($dati, $Ricdag_rec) {
        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';
        $praLib = new praLib();
        $this->PRAM_DB = $dati['PRAM_DB'];
        $arr_aggiuntivi = array();
        if ($Ricdag_rec['DAGTIP'] == "IscrivendiSint" && $Ricdag_rec['RICDAT']) {
            $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
            $arrayNucleoXML = $praLib->getNucleoFamiliareCityWare($progSogg, $dati['Codice']);
            foreach ($arrayNucleoXML['LIST'][0]['ROW'] as $iscrivendo) {
                if ($iscrivendo['PROGSOGG_N'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {
                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_ISCRIVENDO_CODICEFISCALE_CFI";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . " ISCRIVENDO_CODICEFISCALE_CFI";
                    $Ricdag_rec_tipo['RICDAT'] = $iscrivendo['CODFISCALE'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;

                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_ISCRIVENDO_COGNOME";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . "  ISCRIVENDO_COGNOME";
                    $Ricdag_rec_tipo['RICDAT'] = $iscrivendo['COGNOME'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;

                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_ISCRIVENDO_NOME";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . "  ISCRIVENDO_NOME";
                    $Ricdag_rec_tipo['RICDAT'] = $iscrivendo['NOME'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;

                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_ISCRIVENDO_SESSO_SEX";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . " ISCRIVENDO_SESSO_SEX";
                    $Ricdag_rec_tipo['RICDAT'] = $iscrivendo['SESSO'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;

                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_ISCRIVENDO_NASCITADATA_DATA";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . " ISCRIVENDO_NASCITADATA_DATA";
                    $Ricdag_rec_tipo['RICDAT'] = $iscrivendo['DATA_NASCITA'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;
                    break;
                }
            }
            if ($arr_aggiuntivi) {
                return $this->aggiornaCampi($Ricdag_rec, $arr_aggiuntivi);
            }
        }

        if ($Ricdag_rec['DAGTIP'] == 'Istituti' && $Ricdag_rec['RICDAT']) {
            $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
            $arrayIstituti = $praLib->getIstitutiScolasticiCityware($progSogg, $dati['Codice']);
            foreach ($arrayIstituti['LIST'][0]['ROW'] as $istituto) {
                if ($istituto['ISTITU2'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {

                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_DESISTITU";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . "  DESISTITU";
                    $Ricdag_rec_tipo['RICDAT'] = $istituto['DESISTITU'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;

                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_SIGLAISTI";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . " SIGLAISTI";
                    $Ricdag_rec_tipo['RICDAT'] = $istituto['SIGLAISTI'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;
                    break;
                }
            }
            if ($arr_aggiuntivi) {
                return $this->aggiornaCampi($Ricdag_rec, $arr_aggiuntivi);
            }
        }

        if ($Ricdag_rec['DAGTIP'] == 'Servizi' && $Ricdag_rec['RICDAT']) {
            $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
            $arrayServizi = $praLib->getServiziCityware($progSogg, $dati['Codice']);
            foreach ($arrayServizi['LIST'][0]['ROW'] as $servizio) {
                if ($servizio['KFEECP'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {

                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_DES_SERVIZIO";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . " DES_SERVIZIO";
                    $Ricdag_rec_tipo['RICDAT'] = $servizio['DES_SERVIZIO'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;
                    break;
                }
            }
            if ($arr_aggiuntivi) {
                return $this->aggiornaCampi($Ricdag_rec, $arr_aggiuntivi);
            }
        }

        if ($Ricdag_rec['DAGTIP'] == 'Percorsi_ferm' && $Ricdag_rec['RICDAT']) {
            $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
            $arrayPercorsiFerm = $praLib->getPercorsiFermCityWare($progSogg, $dati['Codice'], $Ricdag_rec['DAGKEY']);
            foreach ($arrayPercorsiFerm['LIST'][0]['ROW'] as $percorso) {
                if ($percorso['CHIAVEFEE'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {
                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_DESPERCORA";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . " PERCORSO_DESPERCORA";
                    $Ricdag_rec_tipo['RICDAT'] = $percorso['DESPERCORA'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;

                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_DESFERMATA";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . " PERCORSO_DESFERMATA";
                    $Ricdag_rec_tipo['RICDAT'] = $percorso['DESFERMATA'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;

                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_CHIAVEFEE";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . " PERCORSO_CHIAVEFEE";
                    $Ricdag_rec_tipo['RICDAT'] = $percorso['CHIAVEFEE'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;

                    break;
                }
            }
            if ($arr_aggiuntivi) {
                return $this->aggiornaCampi($Ricdag_rec, $arr_aggiuntivi);
            }
        }

        if ($Ricdag_rec['DAGTIP'] == 'Tipo_pasto' && $Ricdag_rec['RICDAT']) {
            $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
            $arrayTipoPasto = $praLib->getTipoPastoCityWare();
            foreach ($arrayTipoPasto['LIST'][0]['ROW'] as $tipoPasto) {
                if ($tipoPasto['CLASSIFN'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {

                    $Ricdag_rec_tipo = array();
                    $Ricdag_rec_tipo['DAGKEY'] = $Ricdag_rec['DAGKEY'] . "_DESTPAS";
                    $Ricdag_rec_tipo['DAGDES'] = $Ricdag_rec['DAGDES'] . " TIPOPASTO_DESTPAS";
                    $Ricdag_rec_tipo['RICDAT'] = $tipoPasto['DESTPAS'][0]['@textNode'];
                    $arr_aggiuntivi[] = $Ricdag_rec_tipo;
                    break;
                }
            }
            if ($arr_aggiuntivi) {
                return $this->aggiornaCampi($Ricdag_rec, $arr_aggiuntivi);
            }
            return true;
        }
    }

    private function aggiornaCampi($Ricdag_rec, $arr_aggiuntivi) {
        foreach ($arr_aggiuntivi as $aggiuntivo) {
            $sql = "
                    SELECT
                        *
                    FROM
                        RICDAG
                    WHERE 
                        ITEKEY='" . $Ricdag_rec['ITEKEY'] . "' AND 
                        DAGKEY='" . $aggiuntivo['DAGKEY'] . "' AND 
                        DAGNUM='" . $Ricdag_rec['DAGNUM'] . "'";
            $Ricdag_rec_tipo = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
            if ($Ricdag_rec_tipo) {
                try {
                    $Ricdag_rec_tipo['DAGDES'] = $aggiuntivo['DAGDES'];
                    $Ricdag_rec_tipo['RICDAT'] = $aggiuntivo['RICDAT'];
                    $nRowsTipo = ItaDB::DBUpdate($this->PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec_tipo);
                } catch (Exception $e) {
                    return false;
                }
            } else {
                $Ricdag_rec_tipo = array();
                $Ricdag_rec_tipo['DAGNUM'] = $Ricdag_rec['DAGNUM'];
                $Ricdag_rec_tipo['ITECOD'] = $Ricdag_rec['ITECOD'];
                $Ricdag_rec_tipo['ITEKEY'] = $Ricdag_rec['ITEKEY'];
                $Ricdag_rec_tipo['DAGSEQ'] = 9999;
                $Ricdag_rec_tipo['DAGSET'] = $Ricdag_rec['DAGSET'];
                $Ricdag_rec_tipo['DAGROL'] = 1;
                $Ricdag_rec_tipo['DAGKEY'] = $aggiuntivo['DAGKEY'];
                $Ricdag_rec_tipo['DAGDES'] = $aggiuntivo['DAGDES'];
                $Ricdag_rec_tipo['RICDAT'] = $aggiuntivo['RICDAT'];
                try {
                    $nRowsTipo = ItaDB::DBInsert($this->PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec_tipo);
                } catch (Exception $e) {
                    return false;
                }
            }
        }
        return true;
    }

//
//
//            $sql = "SELECT * FROM RICDAG WHERE ITEKEY = '" . $dati['Ricite_rec']['ITEKEY'] . "' AND DAGKEY = 'ISCRIVENDO_COGNOME_NOME' AND DAGNUM = '" . $dati['Ricite_rec']['RICNUM'] . "'";
//            $Ricdag_rec_tipo = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
//            if ($Ricdag_rec_tipo) {
//                $progSogg = frontOfficeApp::$cmsHost->getAltriDati("CITY_PROGSOGG");
//                $arrayNucleoXML = $this->praLib->getNucleoFamiliareCityWare($progSogg, $dati['Proric_rec']['RICPRO']);
//                foreach ($arrayNucleoXML['LIST'][0]['ROW'] as $iscivendo) {
//                    if ($iscivendo['PROGSOGG_N'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {
//                        $Ricdag_rec_tipo['RICDAT'] = $iscivendo['COGNOME'][0]['@textNode'] . " " . $iscivendo['NOME'][0]['@textNode'];
//                        $nRowsTipo = ItaDB::DBUpdate($this->PRAM_DB, "RICDAG", 'ROWID', $Ricdag_rec_tipo);
//                        break;
//                    }
//                }
//            }
//        }
//        if ($Ricdag_rec['DAGTIP'] == 'Istituti' && $Ricdag_rec['RICDAT']) {
//            $arrayIstituti = $praLib->getIstitutiScolasticiCityware($progSogg, $this->dati['Codice']);
//            foreach ($arrayIstituti['LIST'][0]['ROW'] as $istituto) {
//                if ($istituto['ISTITU2'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_DESISTITU", $descrizioneCampo . " DESISTITU", $i++, 'base', $istituto['DESISTITU'][0]['@textNode']);
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_SIGLAISTI", $descrizioneCampo . " SIGLAISTI", $i++, 'base', $istituto['SIGLAISTI'][0]['@textNode']);
//                    break;
//                }
//            }
//        }
//        if ($Ricdag_rec['DAGTIP'] == 'Servizi' && $Ricdag_rec['RICDAT']) {
//            $arrayServizi = $praLib->getServiziCityware($progSogg, $this->dati['Codice']);
//            foreach ($arrayServizi['LIST'][0]['ROW'] as $servizio) {
//                if ($servizio['KFEECP'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_DES_SERVIZIO", $descrizioneCampo . " DES_SERVIZIO", $i++, 'base', $servizio['DES_SERVIZIO'][0]['@textNode']);
//                    break;
//                }
//            }
//        }
//        if ($Ricdag_rec['DAGTIP'] == 'IscrivendiSint' && $Ricdag_rec['RICDAT']) {
//            $arrayNucleoXML = $praLib->getNucleoFamiliareCityWare($progSogg, $this->dati['Codice']);
//            foreach ($arrayNucleoXML['LIST'][0]['ROW'] as $iscivendo) {
//                if ($iscivendo['PROGSOGG_N'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_ISCRIVENDO_CODICEFISCALE_CFI", $descrizioneCampo . " ISCRIVENDO_CODICEFISCALE_CFI", $i++, 'base', $iscivendo['CODFISCALE'][0]['@textNode']);
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_ISCRIVENDO_COGNOME", $descrizioneCampo . " ISCRIVENDO_COGNOME", $i++, 'base', $iscivendo['COGNOME'][0]['@textNode']);
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_ISCRIVENDO_NOME", $descrizioneCampo . " ISCRIVENDO_NOME", $i++, 'base', $iscivendo['NOME'][0]['@textNode']);
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_ISCRIVENDO_SESSO_SEX", $descrizioneCampo . " ISCRIVENDO_SESSO_SEX", $i++, 'base', $iscivendo['SESSO'][0]['@textNode']);
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_ISCRIVENDO_NASCITADATA_DATA", $descrizioneCampo . " ISCRIVENDO_NASCITADATA_DATA", $i++, 'base', $iscivendo['DATA_NASCITA'][0]['@textNode']);
//                    break;
//                }
//            }
//        }
//
//        if ($Ricdag_rec['DAGTIP'] == 'Percorsi_ferm' && $Ricdag_rec['RICDAT']) {
//            $arrayPercorsiFerm = $praLib->getPercorsiFermCityWare($progSogg, $this->dati['Codice'], $chiaveCampo);
//            foreach ($arrayPercorsiFerm['LIST'][0]['ROW'] as $percorso) {
//                if ($percorso['CHIAVEEE'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_DESPRECORA", $descrizioneCampo . " PERCORSO_DESPRECORA", $i++, 'base', $percorso['DESPRECORA'][0]['@textNode']);
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_DESFERMATA", $descrizioneCampo . " PERCORSO_DESFERMATA", $i++, 'base', $percorso['DESFERMATA'][0]['@textNode']);
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_CHIAVEFEE", $descrizioneCampo . " PERCORSO_CHIAVEFEE", $i++, 'base', $percorso['CHIAVEFEE'][0]['@textNode']);
//                    break;
//                }
//            }
//        }
//
//        if ($Ricdag_rec['DAGTIP'] == 'Tipo_pasto' && $Ricdag_rec['RICDAT']) {
//            $arrayTipoPasto = $praLib->getTipoPastoCityWare();
//            foreach ($arrayTipoPasto['LIST'][0]['ROW'] as $tipoPasto) {
//                if ($tipoPasto['CLASSIFN'][0]['@textNode'] == $Ricdag_rec['RICDAT']) {
//                    $this->variabiliCampiAggiuntivi->addField($chiaveCampo . "_DESTPAS", $descrizioneCampo . " TIPOPASTO_DESTPAS", $i++, 'base', $tipoPasto['DESTPAS'][0]['@textNode']);
//                    break;
//                }
//            }
//        }
}

?>