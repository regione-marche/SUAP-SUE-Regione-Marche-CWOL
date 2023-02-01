<?php

/**
 * Description of praLibSostituzioni
 *
 * @author Carlo Iesari <carlo@iesari.me>
 */
require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLib.class.php';

class praLibSostituzioni {

    public $metaKey = 'DOC_SOST_DATA';
    private $praLib;

    public function __construct() {
        $this->praLib = new praLib();
    }

    private function domChildsToArray($node) {
        $array = array();
        /* @var $node DOMNode */
        if ($node->hasChildNodes()) {
            foreach ($node->childNodes as $columnNode) {
                if ($columnNode->nodeType === XML_ELEMENT_NODE) {
                    $array[$columnNode->nodeName] = $columnNode->nodeValue;
                }
            }
        }
        return $array;
    }

    public function estraiDocumentiSostituibili($ricrpa) {
        $xmlinfo = $this->praLib->getCartellaAttachmentPratiche($ricrpa) . '/XMLINFO.xml';

        /* @var $XML DOMDocument */
        $XML = DOMDocument::load($xmlinfo);

        if (!$XML) {
            return false;
        }

        $root = $XML->childNodes->item(0);

        $itekeys = array();
        $ricdoc_tab = array();

        /* @var $table DOMNode */
        foreach ($root->childNodes as $table) {
            switch ($table->localName) {
                case 'RICITE':
                    /* @var $record DOMNode */
                    foreach ($table->childNodes as $record) {
                        if ($record->nodeName === 'RECORD') {
                            $ricite_rec = $this->domChildsToArray($record);

                            if (($ricite_rec['ITEUPL'] == '1' || $ricite_rec['ITEMLT'] == '1') && $ricite_rec['ITEIDR'] != '1') {
                                $itekeys[$ricite_rec['ITEKEY']] = $ricite_rec;
                            }
                        }
                    }
                    break;

                case 'RICDOC':
                    /* @var $record DOMNode */
                    foreach ($table->childNodes as $record) {
                        if ($record->nodeName === 'RECORD') {
                            $ricdoc_rec = $this->domChildsToArray($record);

                            if (in_array($ricdoc_rec['ITEKEY'], array_keys($itekeys))) {
                                $ricdoc_rec['ricite_rec'] = $itekeys[$ricdoc_rec['ITEKEY']];
                                $ricdoc_tab[] = $ricdoc_rec;
                            }
                        }
                    }
                    break;
            }
        }

        return $ricdoc_tab;
    }

    public function controllaPassoSostituzione($ricnum, $PRAM_DB) {
        $ricite_sql = "SELECT
                            *
                        FROM
                            RICITE
                        WHERE
                            RICNUM = '$ricnum'
                        AND
                            ITESOSTF = '1'";

        $ricite_rec = ItaDB::DBSQLSelect($PRAM_DB, $ricite_sql, false);

        return $ricite_rec ? $ricite_rec : false;
    }

    public function getDatiDocumento($arr, $PRAM_DB) {
        $return = array();

        foreach ($arr as $key => $value) {
            switch ($key) {
                case 'CLASSIFICAZIONE':
                    $anacla_rec = $this->praLib->GetAnacla($value, 'codice', false, $PRAM_DB);
                    $return['Classificazione'] = $anacla_rec['CLADES'];
                    break;

                case 'DESTINAZIONE':
                    foreach ($value as $ddocod) {
                        $anaddo_rec = $this->praLib->GetAnaddo($ddocod, 'codice', false, $PRAM_DB);
                        $return['Destinazione'] = (isset($return['Destinazione']) ? $return['Destinazione'] . '<br>' : '') . $anaddo_rec['DDONOM'];
                    }
                    break;

                case 'PASSO':
                    $return['Passo'] = $value;
                    break;

                case 'NOTE':
                    $return['Note'] = $value;
                    break;

                case 'FILENAME':
                    $return['File'] = $value;
                    break;
            }
        }

        return $return;
    }

    public function htmlLabelCheckbox($dati, $PRAM_DB) {
        $style = 'padding: 0 20px 4px 0;';

        $html = '<table style="border-bottom: 1px solid #bbb; text-align: left; min-width: 300px; margin: -2px 0 10px; display: block; padding-bottom: 15px;">';

        foreach ($this->getDatiDocumento($dati, $PRAM_DB) as $label => $value) {
            $html .= "<tr><th style=\"$style\">$label</th><td style=\"$style\">$value</td></tr>";
        }

        $html .= '</table>';

        return $html;
    }

    public function moltiplicaDatiPasso($ricite_rec, $ricdoc_tab, $PRAM_DB) {
        $ricdag_sql = "SELECT
                            *
                        FROM
                            RICDAG
                        WHERE
                            DAGNUM = '{$ricite_rec['RICNUM']}'
                        AND
                            ITEKEY = '{$ricite_rec['ITEKEY']}'";

        $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, $ricdag_sql, true);

        $seq = 10;

        foreach ($ricdag_tab as $ricdag_rec) {
            $dati_ricdag = unserialize($ricdag_rec['DAGMETA']);

            if ($dati_ricdag['HTMLPOS'] == 'Inizio') {
                $seq += 10;
            }
        }

        foreach ($ricdoc_tab as $k => $ricdoc_rec) {
            foreach ($ricdag_tab as $ricdag_rec) {
                $dati_ricdag = unserialize($ricdag_rec['DAGMETA']);

                if ($dati_ricdag['HTMLPOS'] == 'Inizio' || $dati_ricdag['HTMLPOS'] == 'Fine') {
                    continue;
                }

                $dati_ricdoc = unserialize($ricdoc_rec['DOCMETA']);

                $dati_documento = array();
                $dati_documento['PASSO'] = $ricdoc_rec['ricite_rec']['ITEDES'];
                $dati_documento['FILENAME'] = $ricdoc_rec['DOCNAME'];
                if (isset($dati_ricdoc['CLASSIFICAZIONE'])) {
                    $dati_documento['CLASSIFICAZIONE'] = $dati_ricdoc['CLASSIFICAZIONE'];
                }
                if (isset($dati_ricdoc['DESTINAZIONE'])) {
                    $dati_documento['DESTINAZIONE'] = $dati_ricdoc['DESTINAZIONE'];
                }
                if (isset($dati_ricdoc['NOTE'])) {
                    $dati_documento['NOTE'] = $dati_ricdoc['NOTE'];
                }

                unset($ricdag_rec['ROWID']);
                $ricdag_rec['DAGKEY'] .= "_$k";
                $ricdag_rec['DAGSEQ'] = $seq;
//                $ricdag_rec['DAGLAB'] = str_replace(array('FILE_DOCNAME'), array($html), $ricdag_rec['DAGLAB']);

                try {
                    ItaDB::DBInsert($PRAM_DB, 'RICDAG', 'ROWID', $ricdag_rec);
                } catch (Exception $e) {
                    return false;
                }

                if (!$this->aggiungiPassoCaricamento($ricite_rec, $ricdag_rec, $ricdoc_rec, $dati_documento, $PRAM_DB)) {
                    return false;
                }

                $seq += 10;
            }
        }

        foreach ($ricdag_tab as $ricdag_rec) {
            $dati_ricdag = unserialize($ricdag_rec['DAGMETA']);

            if ($dati_ricdag['HTMLPOS'] == 'Fine') {
                $ricdag_rec['DAGSEQ'] = $seq;
                $seq += 10;
                ItaDB::DBUpdate($PRAM_DB, 'RICDAG', 'ROWID', $ricdag_rec);
                continue;
            }

            if ($dati_ricdag['HTMLPOS'] == 'Inizio') {
                continue;
            }

            try {
                ItaDB::DBDelete($PRAM_DB, 'RICDAG', 'ROWID', $ricdag_rec['ROWID']);
            } catch (Exception $e) {
                return false;
            }
        }

        $this->praLib->ordinaPassiPratica($ricite_rec['RICNUM'], $PRAM_DB);

        return true;
    }

    private function aggiungiPassoCaricamento($ricite_rec, $ricdag_rec, $ricdoc_rec, $dati, $PRAM_DB) {
        /*
         * Aggiungo i dati del vecchio documento ai metadati del passo
         */

        $orig_metadata = unserialize($ricdoc_rec['ricite_rec']['ITEMETA']);
        $metadata = is_array($orig_metadata) ? $orig_metadata : array();
        $metadata[$this->metaKey] = $dati;

        /*
         * Creo il passo
         */

        $ricite_rec_new = array(
            'RICNUM' => $ricite_rec['RICNUM'],
            'ITECOD' => $ricite_rec['ITECOD'],
            'ITERES' => '000001',
            'ITEDES' => 'Sostituzione ' . $ricdoc_rec['DOCNAME'],
            'ITESEQ' => $ricite_rec['ITESEQ'] . '.' . str_pad(trim($ricdag_rec['DAGSEQ'], '0'), 2, '0', STR_PAD_LEFT),
            'ITECLT' => '000003', // Allegato
            'ITEUPL' => '1', // Upload
            'ITEPUB' => '1', // Passo di Avvio Richiesta
            'ITEOBL' => '1',
            'ITEEXT' => $ricdoc_rec['ricite_rec']['ITEEXT'],
            'ITEQALLE' => '1',
            'ITEQCLA' => '0',
            'ITEQDEST' => '0',
            'ITEQNOTE' => '1',
//            'ITEMETA' => $ricdoc_rec['ricite_rec']['ITEMETA'],
            'ITEMETA' => serialize($metadata),
            'ITEKEY' => $this->praLib->keyGenerator($ricite_rec['ITECOD']),
            'RICSHA2SOST' => $ricdoc_rec['DOCSHA2'],
            'ITEATE' => serialize(array(
                array(
                    'CAMPO' => 'PRAAGGIUNTIVI.' . $ricdag_rec['DAGKEY'],
                    'CONDIZIONE' => '==',
                    'VALORE' => '1',
                    'OPERATORE' => ''
                )
            ))
        );

        try {
            ItaDB::DBInsert($PRAM_DB, 'RICITE', 'ROWID', $ricite_rec_new);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }

    public function aggiungiPassi($ricnum, $ricrpa, $PRAM_DB) {
        if (!$ricrpa) {
            // Se non è una integrazione, esco
            return true;
        }

        $ricite_rec = $this->controllaPassoSostituzione($ricnum, $PRAM_DB);

        if (!$ricite_rec) {
            // Nessun passo *speciale*, esco
            return true;
        }

        if (($ricdoc_tab = $this->estraiDocumentiSostituibili($ricrpa)) === false) {
            // Errore
            return false;
        }

        if (!$this->moltiplicaDatiPasso($ricite_rec, $ricdoc_tab, $PRAM_DB)) {
            // Errore
            return false;
        }

        return true;
    }

}
