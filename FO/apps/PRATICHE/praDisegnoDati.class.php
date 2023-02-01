<?php

require_once ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php';
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praDisegnoRaccolta.class.php';

class praDisegnoDati {

    static public function prendiDatiDaRichiesta($dati) {
        $dati['ReadOnly'] = $dati['ricdat'];
        $dati['Dizionario'] = $dati['Navigatore']['Dizionario_Richiesta_new'];
        return $dati;
    }

    static public function prendiDatiDaProcedimento($itepas_rec, $itedag_tab) {
        $Ricite_rec = $itepas_rec;
        $Ricite_rec['RICNUM'] = date('Y000000');

        $Ricdag_tab = array();
        foreach ($itedag_tab as $itedag_rec) {
            $Ricdag_rec = array();
            $Ricdag_rec['DAGNUM'] = $Ricite_rec['RICNUM'];
            $Ricdag_rec['ITECOD'] = $itedag_rec['ITECOD'];
            $Ricdag_rec['ITEKEY'] = $itedag_rec['ITEKEY'];
            $Ricdag_rec['DAGDES'] = $itedag_rec['ITDDES'];
            $Ricdag_rec['DAGSEQ'] = $itedag_rec['ITDSEQ'];
            $Ricdag_rec['DAGKEY'] = $itedag_rec['ITDKEY'];
            $Ricdag_rec['DAGALIAS'] = $itedag_rec['ITDALIAS'];
            $Ricdag_rec['DAGVAL'] = $itedag_rec['ITDVAL'];
            $Ricdag_rec['DAGTIP'] = $itedag_rec['ITDTIP'];
            $Ricdag_rec['DAGCTR'] = $itedag_rec['ITDCTR'];
            $Ricdag_rec['DAGNOT'] = $itedag_rec['ITDNOT'];
            $Ricdag_rec['DAGLAB'] = $itedag_rec['ITDLAB'];
            $Ricdag_rec['DAGTIC'] = $itedag_rec['ITDTIC'];
            $Ricdag_rec['DAGROL'] = $itedag_rec['ITDROL'];
            $Ricdag_rec['DAGVCA'] = $itedag_rec['ITDVCA'];
            $Ricdag_rec['DAGREV'] = $itedag_rec['ITDREV'];
            $Ricdag_rec['DAGLEN'] = $itedag_rec['ITDLEN'];
            $Ricdag_rec['DAGDIM'] = $itedag_rec['ITDDIM'];
            $Ricdag_rec['DAGDIZ'] = $itedag_rec['ITDDIZ'];
            $Ricdag_rec['DAGACA'] = $itedag_rec['ITDACA'];
            $Ricdag_rec['DAGPOS'] = $itedag_rec['ITDPOS'];
            $Ricdag_rec['DAGMETA'] = $itedag_rec['ITDMETA'];
            $Ricdag_rec['DAGLABSTYLE'] = $itedag_rec['ITDLABSTYLE'];
            $Ricdag_rec['DAGFIELDSTYLE'] = $itedag_rec['ITDFIELDSTYLE'];
            $Ricdag_rec['DAGFIELDCLASS'] = $itedag_rec['ITDFIELDCLASS'];
            $Ricdag_rec['DAGSET'] = $itedag_rec['ITEKEY'] . '_01';
            $Ricdag_rec['DAGCLASSE'] = $itedag_rec['ITDCLASSE'];
            $Ricdag_rec['DAGMETODO'] = $itedag_rec['ITDMETODO'];
            $Ricdag_tab[] = $Ricdag_rec;
        }

        usort($Ricdag_tab, function ($Ricdag1, $Ricdag2) {
            return $Ricdag1['DAGSEQ'] == $Ricdag2['DAGSEQ'] ? 0 : $Ricdag1['DAGSEQ'] < $Ricdag2['DAGSEQ'] ? -1 : 1;
        });

        $dati = array(
            'Ricite_rec' => $Ricite_rec,
            'Ricdag_tab' => $Ricdag_tab,
            'Dizionario' => new itaDictionary,
            'PRAM_DB' => ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte()),
            'ReadOnly' => false
        );

        return $dati;
    }

    static public function prendiDatiDaXMLProcedimento($xmlPath) {
        $arrayRecords = array('ITEPAS' => array(), 'ITEDAG' => array());

        $reader = new XMLReader();

        if (!$reader->open($xmlPath)) {
            return false;
        }

        while ($reader->read()) {
            if ($reader->nodeType == XMLReader::ELEMENT) {
                switch ($reader->name) {
                    case 'ITEPAS':
                    case 'ITEDAG':
                        $itaXML = new itaXML();

                        if (!$itaXML->setXmlFromString($reader->readOuterXml())) {
                            return false;
                        }

                        $xmlArray = $itaXML->getArray();

                        if (!$xmlArray) {
                            return false;
                        }

                        foreach ($xmlArray[$reader->name][0] as $campo => $arrayValue) {
                            if ($campo == '@attributes') {
                                continue;
                            }

                            $dataEncodeAttr = ($arrayValue[0]['@attributes']['dataencode']) ? $arrayValue[0]['@attributes']['dataencode'] : '';
                            if (strpos($campo, 'META') !== false) {
                                $dataEncodeAttr = 'base64';
                            }

                            switch ($dataEncodeAttr) {
                                case 'ent':
                                    $Record_rec[$campo] = html_entity_decode($arrayValue[0]['@textNode']);
                                    break;

                                case 'base64':
                                    $Record_rec[$campo] = base64_decode(utf8_decode($arrayValue[0]['@textNode']));
                                    break;

                                default:
                                    $Record_rec[$campo] = utf8_decode($arrayValue[0]['@textNode']);
                                    break;
                            }
                        }

                        $arrayRecords[$reader->name][] = $Record_rec;
                        continue;
                }
            }
        }

        $reader = null;

        return self::prendiDatiDaProcedimento($arrayRecords['ITEPAS'][0], $arrayRecords['ITEDAG']);
    }

}
