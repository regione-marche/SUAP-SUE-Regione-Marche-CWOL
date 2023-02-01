<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praHtmlLegenda
 *
 * @author Andrea
 */
class praHtmlGridAllegati {

    function GetGrid($dati, $results, $extraParms) {
        $html = new html();
        $praLib = new praLib();

        $tableData = array(
            'header' => array(
                array('text' => 'N.', 'attrs' => array('width' => '10%')),
                array('text' => 'Allegato', 'attrs' => array('width' => '35%')),
                array('text' => 'Pdf Acquisito', 'attrs' => array('width' => '10%')),
                array('text' => 'Info', 'attrs' => array('width' => '40%'))
            ),
            'body' => array()
        );


        //if ($dati['Ricite_rec']['ITEFILE'] == 0 && $dati['Consulta'] != true) {
        if ($dati['Ricite_rec']['ITEFILE'] == 0 && ( $dati['Consulta'] != true || ($dati['Consulta'] == true && $dati['permessiPasso']['Insert'] == 1) )) {
            $tableData['header'][] = array('text' => 'Cancella', 'attrs' => array('width' => '5%', 'data-sortable' => 'false'));
        }

        $i = 0;

        foreach ($results as $Allegato) {
            $tableRow = array();

            $ricdoc_rec = $praLib->GetRicdoc($Allegato, "codice", $extraParms['PRAM_DB']);

            if ($ricdoc_rec['DOCFLSERVIZIO'] == 1) {
                continue;
            }

            $Est = strtolower(pathinfo($Allegato, PATHINFO_EXTENSION));

            if ($Est != 'info' && $Est != 'err') {
                $i += 1;

                //
                // Numero Allegato
                //

                $tableRow[] = "<div style=\"text-align: center;\">$i</div>";

                //
                //Img Vedi Allegato
                //

                $Metadati = unserialize($ricdoc_rec['DOCMETA']);

                $allegatoImage = frontOfficeLib::getFileIcon($ricdoc_rec['DOCNAME']);
                if ($dati['Ricite_rec']['RICERF'] == 1) {
                    $fileErr = $Allegato . '.err';
                    if (file_exists($dati['CartellaAllegati'] . '/' . $fileErr)) {
                        /*
                         * @Todo Sostituire con immagine di 'warning'
                         */
                        $allegatoImage = frontOfficeLib::getIcon('ban');
                    }
                }

                $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'vediAllegato',
                            'seq' => $dati['Ricite_rec']['ITESEQ'],
                            'file' => $Allegato,
                            'ricnum' => $dati['Proric_rec']['RICNUM']
                ));

                $textAllegati = '<a href="' . $allegatoHref . '" style="display: block;">';
                $textAllegati .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin-top: 5px; word-break: break-all;">';
                $textAllegati .= '  <div style="position: absolute; left: 0; top: -4px;">';
                $textAllegati .= $html->getImage($allegatoImage, '24px', 'Vedi allegato');
                if ($ricdoc_rec['DOCRIS']) {
                    $textAllegati .= '<div style="position: absolute; bottom: -5px; right: -5px;">' . $html->getImage(frontOfficeLib::getIcon('lock'), '18px') . '</div>';
                }
                $textAllegati .= '  </div>';
                $textAllegati .= $ricdoc_rec['DOCNAME'];
                $textAllegati .= '</div>';
                $textAllegati .= '</a>';

                $tableRow[] = $textAllegati;

                //
                //Vedi PDF Sbustato se presente
                //
                $textAcquisito = '';
                if ($Est == "p7m") {
                    //$pdfFileName = pathinfo($Allegato, PATHINFO_FILENAME);
                    $pdfFileName = $praLib->GetP7MFileContentName($Allegato);
                    if (is_file($dati['CartellaAllegati'] . "/" . $pdfFileName)) {
                        $ricdoc_rec_pdf = $praLib->GetRicdoc($pdfFileName, "codice", $extraParms['PRAM_DB']);

                        $hRef = ItaUrlUtil::GetPageUrl(array(
                                    'event' => 'vediAllegato',
                                    'seq' => $dati['Ricite_rec']['ITESEQ'],
                                    'file' => $pdfFileName,
                                    'ricnum' => $dati['Proric_rec']['RICNUM']
                        ));

                        $textAcquisito = '<a href="' . $hRef . '" style="display: block;">';
                        $textAcquisito .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin-top: 5px;">';
                        $textAcquisito .= '  <div style="position: absolute; left: 0; top: -4px;">';
                        $textAcquisito .= $html->getImage(frontOfficeLib::getFileIcon('.pdf'), '24px', 'Vedi PDF acquisito');
                        $textAcquisito .= '  </div>';
                        $textAcquisito .= $ricdoc_rec_pdf['DOCNAME'];
                        $textAcquisito .= '</div>';
                        $textAcquisito .= '</a>';
                    }
                }

                $tableRow[] = $textAcquisito;

                /*
                 * Info Allegato
                 */
                $tmpHtml = new html();
                $this->GetHtmlGridInfo($ricdoc_rec, $tmpHtml, $praLib, $Metadati, $extraParms);
                $tableRow[] = $tmpHtml->getHtml();

                /*
                 * Img Cancella Allegato
                 */
//                if ($dati['Ricite_rec']['ITEFILE'] == 0 && $dati['Consulta'] != true) {
                if ($dati['Ricite_rec']['ITEFILE'] == 0 && ( $dati['Consulta'] != true || ($dati['Consulta'] == true && $dati['permessiPasso']['Insert'] == 1) )) {
                    $immagineCancella = frontOfficeLib::getIcon('error');
                    if ($dati['Ricite_rec']['ITEFILE'] == 1) {
                        $immagineCancella = frontOfficeLib::getIcon('empty');
                    }

                    $urlCan = ItaUrlUtil::GetPageUrl(array(
                                'event' => 'cancellaAllegato',
                                'seq' => $dati['Ricite_rec']['ITESEQ'],
                                'allegato' => $Allegato,
                                'ricnum' => $dati['Proric_rec']['RICNUM']
                    ));

                    $tableRow[] = '<div class="align-center">' . $html->getImage($immagineCancella, '24px', 'Cancella allegato', $urlCan) . '</div>';
                }

                $tableData['body'][] = $tableRow;
            }
        }

        $html->addTable($tableData, array('sortable' => true, 'paginated' => true));

        return $html->getHtml();
    }

    function GetGridInviiAgenzia($dati, $extraParms) {
        $html = new html();
        $arrayNote = unserialize($dati['Ricite_rec']['RICNOT']);

        if (isset($arrayNote['INVIOAGENZIA'])) {
            $tableData = array(
                'header' => array('Responso', 'Utente', 'Agenzia', 'Data', 'Ora', 'Hash'),
                'body' => array()
            );

            foreach ($arrayNote['INVIOAGENZIA'] as $invio) {
                foreach ($invio as $dati) {
                    $tableRow = array();

                    $tableRow[] = $html->getImage(frontOfficeLib::getIcon(($dati['RESPONSE'] === 'OK' ? 'email' : 'ban')), '24px');
                    $tableRow[] = $dati['UTENTE'];
                    $tableRow[] = $dati['AGENZIA'];
                    $tableRow[] = frontOfficeLib::convertiData($dati['DATA']);
                    $tableRow[] = $dati['ORE'];
                    $tableRow[] = $dati['HASH'];

                    $tableData['body'][] = $tableRow;
                }
            }

            $html->addTable($tableData, array('sortable' => true, 'paginated' => true));

            return $html->getHtml();
        }
    }

    function GetGridRiepilogo($dati, $extraParms) {
        $html = new html();
        $praLib = new praLib();

        $tableData = array(
            'caption' => 'Riepilogo allegati',
            'header' => array('Passo', 'Allegati'),
            'body' => array()
        );

        //
        //Mi preparo l'array dei passi in base se trovo almeno un ITECOMPSEQ
        //
        $arrayPassiRapporto = $dati['Navigatore']['Ricite_tab_new'];
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $key => $record) {
            if ($record['ITECOMPSEQ'] != 0) {
                $arrayPassiRapporto[$key] = $record;
                $arrayPassiRapporto = $praLib->array_sort($arrayPassiRapporto, "ITECOMPSEQ");
                break;
            }
        }

        /*
         * Cerco il passo cartella upload
         */
        $praLibAllegati = praLibAllegati::getInstance($praLib);
        $passoCartella = $praLibAllegati->getPassoCartella($dati);

        //
        //Mi scorro i passi e butto fuori l'html dei mancanti e presenti
        //
        foreach ($arrayPassiRapporto as $key => $record) {
            if ($passoCartella && $passoCartella['ITEKEY'] == $record['ITEKEY']) {
                /*
                 * Escluso il passo cartella
                 */
                continue;
            }

            //if ($record['ITEUPL'] == 1 || $record['ITEMLT'] == 1 || $record['ITEDAT'] == 1 || $record['ITERDM'] == 1) {
            if ($record['ITEIDR'] == 0) {
                $tableRow = array();

                $Ricdoc_tab = $praLib->GetRicdoc($record['ITEKEY'], "itekey", $extraParms['PRAM_DB'], true, $record['RICNUM']);
                if (!$Ricdoc_tab) {
                    continue;
                }

                $hrefVaiPasso = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'navClick',
                            'seq' => $record['ITESEQ'],
                            'ricnum' => $dati['Proric_rec']['RICNUM']
                ));

                $tableRow[] = "<a title=\"Vai al Passo\" href=\"$hrefVaiPasso\">" . ($key + 1) . " - " . $record['ITEDES'] . "</a>";

                $textAllegati = '';

                foreach ($Ricdoc_tab as $Ricdoc_rec) {
                    //Se c'è il pdf sbustato faccio vedere solo il p7m
                    if ($Ricdoc_rec['DOCFLSERVIZIO'] == 1) {
                        continue;
                    }

                    $allegatoImage = frontOfficeLib::getFileIcon($Ricdoc_rec['DOCNAME']);
                    if ($record['RICERF'] == 1) {
                        $allegatoImage = frontOfficeLib::getIcon('ban');
                    }

                    $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                                'event' => 'vediAllegato',
//                            'seq' => $record['ITESEQ'],
                                'seq' => $dati['Ricite_rec']['ITESEQ'],
                                'file' => $Ricdoc_rec['DOCUPL'],
                                'ricnum' => $dati['Proric_rec']['RICNUM']
                    ));

                    if ($textAllegati !== '') {
                        $textAllegati .= '<br />';
                    }

                    $textAllegati .= '<a href="' . $allegatoHref . '" style="display: block;">';
                    $textAllegati .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin-top: 5px;">';
                    $textAllegati .= '  <div style="position: absolute; left: 0; top: -4px;">';
                    $textAllegati .= $html->getImage($allegatoImage, '24px', 'Vedi allegato');
                    $textAllegati .= '  </div>';
                    $textAllegati .= $Ricdoc_rec['DOCNAME'];
                    $textAllegati .= '</div>';
                    $textAllegati .= '</a>';
                }

                $tableRow[] = $textAllegati;
                $tableData['body'][] = $tableRow;
            }
        }

        $html->addTable($tableData, array('sortable' => true, 'paginated' => true));

        return $html->getHtml();
    }

    function GetGridDistinta($dati, $extraParms) {
        $html = new html();
        $praLib = new praLib();

        $tableData = array(
            'caption' => 'Riepilogo allegati non accorpati',
            'header' => array(array('text' => 'Passo', 'attrs' => array('width' => '25%')), 'Allegati'),
            'body' => array()
        );

        //
        //Mi scorro tutti i passi e trovo il passo rapporto per non far vedere il passo upload del rapporto
        //
        foreach ($dati['Ricite_tab'] as $key => $record) {
            if ($record['ITEDRR'] == 1) {
                $Passo_rapporto = $record;
                break;
            }
        }

        //
        //Mi scorro i passi e tolgo dall'array i facoltativi senza allegati caricati
        //
        $ricite_tab = $dati['Navigatore']['Ricite_tab_new'];
        foreach ($ricite_tab as $key => $record) {
            if (
                    $record['ITEIDR'] == 0 && ($record['ITEUPL'] == 1 || $record['ITEMLT'] == 1) &&
                    (
                    ($dati['Ricite_rec']['ITECTP'] == '' && $record['ITESEQ'] < $dati['seq']) ||
                    ($dati['Ricite_rec']['ITECTP'] != '' && $dati['Ricite_rec']['ITECTP'] == $record['ITEKEY'])
                    )
            ) {

                if ((!$Passo_rapporto || $Passo_rapporto['ITEKEY'] != $record['ITECTP']) && $record['ITEOBL'] == 0) {
                    $Ricdoc_tab = $praLib->GetRicdoc($record['ITEKEY'], "itekey", $extraParms['PRAM_DB'], true, $record['RICNUM']);
                    if (!$Ricdoc_tab) {
                        unset($ricite_tab[$key]);
                    }
                }
            }
        }

        //
        //Mi scorro i passi e butto fuori l'html dei mancanti e presenti
        //
        foreach ($ricite_tab as $key => $record) {
            if (
                    $record['ITEIDR'] == 0 && ($record['ITEUPL'] == 1 || $record['ITEMLT'] == 1) &&
                    (
                    ($dati['Ricite_rec']['ITECTP'] == '' && $record['ITESEQ'] < $dati['seq']) ||
                    ($dati['Ricite_rec']['ITECTP'] != '' && $dati['Ricite_rec']['ITECTP'] == $record['ITEKEY'])
                    )
            ) {
                if ($Passo_rapporto['ITEKEY'] != $record['ITECTP']) {
                    $tableRow = array();

                    $Ricdoc_tab = $praLib->GetRicdoc($record['ITEKEY'], "itekey", $extraParms['PRAM_DB'], true, $record['RICNUM']);
                    if (!$Ricdoc_tab) {
                        if ($record['ITEOBL'] == 0 || $record['RICOBL'] == 0) {
                            continue;
                        }
                    }

                    $hrefVaiPasso = ItaUrlUtil::GetPageUrl(array(
                                'event' => 'navClick',
                                'seq' => $record['ITESEQ'],
                                'ricnum' => $dati['Proric_rec']['RICNUM']
                    ));

                    $tableRow[] = "<a title=\"Vai al passo\" href=\"$hrefVaiPasso\">" . ($key + 1) . " - " . $record['ITEDES'] . "</a>";

                    $textAllegati = '';

                    if ($Ricdoc_tab) {
                        foreach ($Ricdoc_tab as $i => $Ricdoc_rec) {
                            //Se c'è il pdf sbustato faccio vedere solo il p7m
                            if ($Ricdoc_rec['DOCFLSERVIZIO'] == 1) {
                                continue;
                            }

                            $Metadati = unserialize($Ricdoc_rec['DOCMETA']);

                            $allegatoImage = frontOfficeLib::getFileIcon($Ricdoc_rec['DOCNAME']);
                            if ($record['RICERF'] == 1) {
                                $allegatoImage = frontOfficeLib::getIcon('ban');
                            }

                            $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                                        'event' => 'vediAllegato',
                                        'seq' => $dati['Ricite_rec']['ITESEQ'],
                                        'file' => $Ricdoc_rec['DOCUPL'],
                                        'ricnum' => $dati['Proric_rec']['RICNUM']
                            ));

                            if ($textAllegati !== '') {
                                $textAllegati .= '<br />';
                            }

                            $textAllegati .= '<div>';
                            $textAllegati .= '<a href="' . $allegatoHref . '" style="display: block;">';
                            $textAllegati .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin-top: 5px;">';
                            $textAllegati .= '  <div style="position: absolute; left: 0; top: -4px;">';
                            $textAllegati .= $html->getImage($allegatoImage, '24px', 'Vedi allegato');
                            if ($Ricdoc_rec['DOCRIS']) {
                                $textAllegati .= '<div style="position: absolute; bottom: -5px; right: -5px;">' . $html->getImage(frontOfficeLib::getIcon('lock'), '18px') . '</div>';
                            }
                            $textAllegati .= '  </div>';
                            $textAllegati .= $Ricdoc_rec['DOCNAME'];
                            $textAllegati .= '</div>';
                            $textAllegati .= '</a><br />';
                            $textAllegati .= '</div><div>';

                            $textAllegati .= '<br />';
                            $tmpHtml = new html();
                            $this->GetHtmlGridInfo($Ricdoc_rec, $tmpHtml, $praLib, $Metadati, $extraParms);
                            $textAllegati .= $tmpHtml->getHtml();

                            $textAllegati .= '</div>';
                        }
                    } else {
                        if ($record['ITEMLT'] == 1) {
                            $upl = "Allegati Mancanti";
                        } elseif ($record['ITEUPL'] == 1) {
                            $upl = "Allegato Mancante";
                        }

                        $textAllegati = "<span style=\"color: red; font-size: 1em;\">$upl</span>";
                    }

                    $tableRow[] = $textAllegati;
                    $tableData['body'][] = $tableRow;
                }
            }
        }

        $html->addTable($tableData, array('sortable' => true, 'paginated' => true));

        return $html->getHtml();
    }

    function GetGridRapporto($dati, $extraParms, $titoloTabella = 'Riepilogo allegati rapporto completo') {
        $html = new html();
        $praLib = new praLib();

        $tableData = array(
            'caption' => $titoloTabella,
            'header' => array('Passo', array('text' => 'Allegati', 'attrs' => array('style' => 'width: 60%'))),
            'body' => array()
        );

        /*
         * Mi preparo l'array dei passi in base se trovo almeno un ITECOMPSEQ
         */
        $arrayPassiRapporto = $dati['Navigatore']['Ricite_tab_new'];
        foreach ($dati['Navigatore']['Ricite_tab_new'] as $key => $record) {
            if ($record['ITECOMPSEQ'] != 0) {
                $arrayPassiRapporto[$key] = $record;
                $arrayPassiRapporto = $praLib->array_sort($arrayPassiRapporto, "ITECOMPSEQ");
                break;
            }
        }

        /*
         * Mi scorro i passi e verifico se quelli facoltativi hanno l'allegato
         * e se si li metto nel rapporto
         */
        foreach ($arrayPassiRapporto as $key1 => $record) {
            if ($record['ITEIDR'] == 1 && ($record['ITEOBL'] == 0 || $record['RICOBL'] == 0)) {
                $Ricdoc_tab = $praLib->GetRicdoc($record['ITEKEY'], "itekey", $extraParms['PRAM_DB'], true, $record['RICNUM']);
                if (!$Ricdoc_tab) {
                    unset($arrayPassiRapporto[$key1]);
                }
            }
        }

        /*
         * Mi scorro i passi e butto fuori l'html dei mancanti e presenti
         */
        foreach ($arrayPassiRapporto as $key => $record) {
            if ($record['ITEIDR'] == 1) {
                $tableRow = array();

                $hrefVaiPasso = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'navClick',
                            'seq' => $record['ITESEQ'],
                            'ricnum' => $dati['Proric_rec']['RICNUM']
                ));

                $tableRow[] = '<a href="' . $hrefVaiPasso . '" title="Vai al passo">' . ($key + 1) . ' - ' . $record['ITEDES'] . '</a>';

                $textAllegati = '';
                $Ricdoc_tab = $praLib->GetRicdoc($record['ITEKEY'], "itekey", $extraParms['PRAM_DB'], true, $record['RICNUM']);

                /*
                 * Rimuovo dalla tab il file AUTOCERTIFICAZIONE_ACCORPA
                 */
                foreach ($Ricdoc_tab as $k => $Ricdoc_rec) {
                    $metaData = unserialize($Ricdoc_rec['DOCMETA']);
                    if ($metaData['AUTOCERTIFICAZIONE_ACCORPATA']) {
                        unset($Ricdoc_tab[$k]);
                    }
                }

                if ($Ricdoc_tab) {
                    foreach ($Ricdoc_tab as $Ricdoc_rec) {
                        if (pathinfo($Ricdoc_rec['DOCNAME'], PATHINFO_EXTENSION) == 'p7m') {
                            $pdfFileName = pathinfo($Ricdoc_rec['DOCUPL'], PATHINFO_FILENAME);
                            if (file_exists($dati['CartellaAllegati'] . '/' . $pdfFileName)) {
                                continue;
                            }
                        }

                        $allegatoImage = frontOfficeLib::getFileIcon($Ricdoc_rec['DOCNAME']);
                        if ($record['RICERF'] == 1) {
                            $allegatoImage = frontOfficeLib::getIcon('ban');
                        }

                        $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                                    'event' => 'vediAllegato',
                                    'seq' => $dati['Ricite_rec']['ITESEQ'],
                                    'file' => $Ricdoc_rec['DOCUPL'],
                                    'ricnum' => $dati['Proric_rec']['RICNUM']
                        ));

                        if ($textAllegati !== '') {
                            $textAllegati .= '<br />';
                        }

                        $textAllegati .= '<a href="' . $allegatoHref . '" style="display: block;">';
                        $textAllegati .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin-top: 5px;">';
                        $textAllegati .= '  <div style="position: absolute; left: 0; top: -4px;">';
                        $textAllegati .= $html->getImage($allegatoImage, '24px', 'Vedi allegato');
                        $textAllegati .= '  </div>';
                        $textAllegati .= $Ricdoc_rec['DOCNAME'];
                        $textAllegati .= '</div>';
                        $textAllegati .= '</a>';
                    }
                } else {
                    if ($record['ITEMLT'] == 1) {
                        $textAllegati = 'Allegati Mancanti';
                    } elseif ($record['ITEUPL'] == 1 || $record['ITEDAT'] == 1) {
                        $textAllegati = 'Allegato Mancante';
                    } else if ($record['ITEDIS'] == 1) {
                        $Seq_passo = str_repeat("0", $dati['seqlen'] - strlen($record['ITESEQ'])) . $record['ITESEQ'];
                        $results = $praLib->searchFile($dati['CartellaAllegati'], $dati['Proric_rec']['RICNUM'] . "_C" . $Seq_passo);

                        if ($results && strpos($dati['Proric_rec']['RICSEQ'], "." . $record['ITESEQ'] . ".") !== false) {
                            $allegatoImage = frontOfficeLib::getFileIcon('.pdf');

                            $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                                        'event' => 'vediAllegato',
                                        'seq' => $dati['Ricite_rec']['ITESEQ'],
                                        'file' => $results[0],
                                        'ricnum' => $dati['Proric_rec']['RICNUM']
                            ));

                            if ($textAllegati !== '') {
                                $textAllegati .= '<br />';
                            }

                            $textAllegati .= '<a href="' . $allegatoHref . '" style="display: block;">';
                            $textAllegati .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin-top: 5px;">';
                            $textAllegati .= '  <div style="position: absolute; left: 0; top: -4px;">';
                            $textAllegati .= $html->getImage($allegatoImage, '24px', 'Vedi allegato');
                            $textAllegati .= '  </div>';
                            $textAllegati .= $results[0];
                            $textAllegati .= '</div>';
                            $textAllegati .= '</a>';
                        } else {
                            $textAllegati = 'Allegato Mancante';
                        }
                    }
                }

                $tableRow[] = $textAllegati;
                $tableData['body'][] = $tableRow;
            }
        }

        $html->addTable($tableData, array('sortable' => true, 'paginated' => true));

        return $html->getHtml();
    }

    function GetImgAllegato($Est) {
        switch ($Est) {
            case 'pdf':
                $image = "pdf.jpg";
                break;
            case 'p7m':
                $image = "p7m.png";
                break;
            case 'jpg':
            case 'jpeg':
            case 'gif':
            case 'png':
                $image = "photo.jpg";
                break;
            case 'doc':
            case 'docx':
                $image = "word.jpg";
                break;
            case 'txt':
                $image = "txt.gif";
                break;
            case 'xls':
                $image = "excel.jpg";
                break;
            default:
                $image = "file-256.png";
                break;
        }
        return $image;
    }

    function GetHtmlGridInfo($ricdoc_rec, $html, $praLib, $Metadati, $extraParms) {
        $cellStyle = 'style="padding: .45em;"';
        $html->appendHtml("<table class=\"italsoft-table\" style=\"font-size: .95em;\">");

        if ($Metadati['CLASSIFICAZIONE']) {
            $html->appendHtml('<thead>');
            $html->appendHtml('<tr>');
            $html->appendHtml("<th $cellStyle>Classificazione: </th>");
            $html->appendHtml('</tr>');
            $html->appendHtml('</thead>');
            $html->appendHtml('<tbody>');
            $html->appendHtml('<tr>');
            $Anacla_rec = $praLib->GetAnacla($Metadati['CLASSIFICAZIONE'], "codice", false, $extraParms['PRAM_DB']);
            $html->appendHtml("<td $cellStyle>" . $Anacla_rec['CLADES'] . "</td>");
            $html->appendHtml('</tr>');
            $html->appendHtml('</tbody>');
        }

        if ($Metadati['DESTINAZIONE']) {
            $html->appendHtml('<thead>');
            $html->appendHtml('<tr>');
            $html->appendHtml("<th $cellStyle>Destinazioni: </th>");
            $html->appendHtml('</tr>');

            $html->appendHtml('</thead>');
            $html->appendHtml('<tbody>');
            $html->appendHtml('<tr>');

            $destinazioni = "";
            foreach ($Metadati['DESTINAZIONE'] as $dest) {
                $Anaddo_rec = $praLib->GetAnaddo($dest, "codice", false, $extraParms['PRAM_DB']);
                if ($Anaddo_rec) {
                    $destinazioni .= ' - ' . $Anaddo_rec['DDONOM'] . "<br>";
                }
            }
            $html->appendHtml("<td $cellStyle>$destinazioni</td>");
            $html->appendHtml('</tr>');
            $html->appendHtml('</tbody>');
        }

        if ($Metadati['NOTE']) {
            $html->appendHtml('<thead>');
            $html->appendHtml('<tr >');
            $html->appendHtml("<th $cellStyle>Note: </th>");
            $html->appendHtml('</tr>');
            $html->appendHtml('</thead>');
            $html->appendHtml('<tbody>');
            $html->appendHtml('</tr>');
            $html->appendHtml("<td $cellStyle>" . $Metadati['NOTE'] . "</td>");
            $html->appendHtml('</tr>');
            $html->appendHtml('</tbody>');
        }

        $html->appendHtml('</table>');
    }

    function GetGridAltriAllegati($dati, $result) {
        $html = new html();

        $tableData = array(
            'header' => array('N.', 'Allegato'),
            'body' => array()
        );

        $count = 0;

        foreach ($result as $Allegato) {
            $count++;

            $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'vediAllegato',
                        'procedi' => $dati['Codice'],
                        'allegato' => frontOfficeApp::encrypt($Allegato['FILENAME']),
                        'type' => 'all'
            ));

            $textAllegati = '<a href="' . $allegatoHref . '" style="display: block;">';
            $textAllegati .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin-top: 5px;">';
            $textAllegati .= '  <div style="position: absolute; left: 0; top: -4px;">';
            $textAllegati .= $html->getImage(frontOfficeLib::getFileIcon($Allegato['FILENAME']), '24px', 'Vedi allegato');
            $textAllegati .= '  </div>';
            $textAllegati .= $Allegato['NOTE'];
            $textAllegati .= '</div>';
            $textAllegati .= '</a>';

            $tableData['body'][] = array(
                $count,
                $textAllegati
            );
        }

        $html->addTable($tableData);

        return $html->getHtml();
    }

    function GetGridPassi($arrayPassiRaccolte, $Proric_rec, $extraParms) {
        $html = new html();
        $praLib = new praLib();

        //
        // Mi scorro i passi e verifico se quelli facoltativi hanno l'allegato e se si li metto nel rapporto
        //
        foreach ($arrayPassiRaccolte as $itekey => $record) {
            //if ($record['ITEIDR'] == 1 && ($record['ITEOBL'] == 0 || $record['RICOBL'] == 0)) {
            if ($record['ITEOBL'] == 0 && $record['RICOBL'] == 0) {
                if (strpos($Proric_rec['RICSEQ'], chr(46) . $record['ITESEQ'] . chr(46)) === false) {
                    unset($arrayPassiRaccolte[$itekey]);
                }
            }
        }

        $tableData = array(
            'caption' => 'ATTENZIONE CI SONO PASSI NON COMPILATI!',
            'header' => array('Passo', ''),
            'body' => array()
        );

        //
        // Mi scorro i passi delle raccolte e butto fuori l'html degli eseguiti e non
        //
        foreach ($arrayPassiRaccolte as $itekey => $record) {
            $ricite_rec = $praLib->GetRicite($itekey, "itekey", $extraParms['PRAM_DB'], false, $Proric_rec['RICNUM']);

            if (strpos($Proric_rec['RICSEQ'], chr(46) . $record['ITESEQ'] . chr(46)) === false) {
                $hrefVaiPasso = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'navClick',
                            'seq' => $record['ITESEQ'],
                            'ricnum' => $ricite_rec['RICNUM']
                ));

                $tableData['body'][] = array(
                    '<a href="' . $hrefVaiPasso . '" title="Vai al passo">' . $ricite_rec['ITEDES'] . '</a>',
                    'Passo non compilato'
                );
            }
        }

        if (count($tableData['body'])) {
            $html->addTable($tableData, array('sortable' => true, 'paginated' => true));
        }

        return $html->getHtml();
    }

    function GetGridAllegatiNews($dataDetail_tab, $extraParms) {
        $html = new html();

        $tableData = array(
            'header' => array('Allegato', 'Descrizione', 'Destinazioni'),
            'body' => array()
        );

        $lastClass = false;
        $multiClass = true;
        $classificazioni = array();
        foreach ($dataDetail_tab as $dataDetail_rec) {
            $classificazioni[$dataDetail_rec['PASCLAS']] = $dataDetail_rec['CLADES'];
        }
        if (count($classificazioni) === 1 && key($classificazioni) === '') {
            $multiClass = false;
        }

        $html->appendHtml('<h2 style="margin: 1em; text-align: center;">Allegati articolo</h2>');

        if ($multiClass && count($classificazioni) > 1) {
            $html->appendHtml('<div class="grid" style="max-width: none; padding: 0;">');
            $html->appendHtml('<div class="col-3-12">');

            $indexData = array(
                'header' => array('Indice Classificazioni'),
                'body' => array()
            );

            foreach ($classificazioni as $codice => $descrizione) {
                $descrizione = $descrizione ?: 'Senza classificazione';

                $indexData['body'][] = array(
                    "<a href=\"#classificazione_$codice\">$descrizione</a>"
                );
            }

            $html->addTable($indexData);

            $html->appendHtml('</div>');
            $html->appendHtml('<div class="col-9-12" style="padding: 0;">');
        }

        foreach ($dataDetail_tab as $dataDetail_rec) {
            if ($multiClass && $lastClass !== $dataDetail_rec['PASCLAS']) {
                $lastClass = $dataDetail_rec['PASCLAS'];

                $tableData['body'][] = array(
                    array(
                        'text' => '<div style="text-align: center; font-weight: bold;" id="classificazione_' . $dataDetail_rec['PASCLAS'] . '">' . ($dataDetail_rec['CLADES'] ?: '<i>Senza classificazione</i>') . '</div>',
                        'attrs' => array(
                            'colspan' => 3,
                            'style' => 'background-color: #e8e8e8'
                        )
                    )
                );
            }

            $tableRow = array();

            $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'gestioneAllegato',
                        'operation' => 'view',
                        'htok' => md5($dataDetail_rec['PASFIL']),
                        'fileIdE' => frontOfficeApp::encrypt($dataDetail_rec['ROWID'])
            ));

            $textAllegati = '<a href="' . $allegatoHref . '" target="_blank" style="display: block; word-break: break-all;">';
            $textAllegati .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin-top: 5px;">';
            $textAllegati .= '  <div style="position: absolute; left: 0; top: -4px;">';
            $textAllegati .= $html->getImage(frontOfficeLib::getFileIcon($dataDetail_rec['PASFIL']), '24px', 'Vedi allegato');
            $textAllegati .= '  </div>';
            $textAllegati .= $dataDetail_rec['PASNAME'] ?: $dataDetail_rec['PASNOT'];
            $textAllegati .= '</div>';
            $textAllegati .= '</a>';

            $tableRow[] = $textAllegati;

            $formAction = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'verificaSHA',
                        'infoUpload' => itaCrypt::encrypt(json_encode(array(
                            'idAllegato' => $dataDetail_rec['ROWID']
                        )))
            ));

            $textDescrizione = $dataDetail_rec['PASNOTE'] ?: $dataDetail_rec['PASNOT'] . '<br /><br />SHA-2: ' . $dataDetail_rec['PASSHA2'] . '<br><br>';

            $textDescrizione .= $html->getForm($formAction, 'POST', array(
                'enctype' => 'multipart/form-data',
                'style' => 'text-align: right;'
            ));

            $textDescrizione .= '<a href="#"><label style="cursor: pointer; font-size: .8em;">';
            $textDescrizione .= '<input type="file" name="verificaAllegato" style="display: none;" onchange="$(\'body\').addClass(\'italsoft-loading\'); this.form.submit();">';
            $textDescrizione .= $html->getImage(frontOfficeLib::getIcon('search'), '18px') . '<span style="display: inline-block; vertical-align: middle;">verifica SHA</span>';
            $textDescrizione .= '</label></a>';
            $textDescrizione .= '</form>';
            $tableRow[] = $textDescrizione;


            $strDest = $textDest = "";
            if ($dataDetail_rec['PASDEST']) {
                $arrayDest = unserialize($dataDetail_rec['PASDEST']);
                if (is_array($arrayDest)) {
                    $praLib = new praLib();
                    $praLibAllegati = praLibAllegati::getInstance($praLib);
                    $strDest = $praLibAllegati->getStringDestinatari($dataDetail_rec['PASDEST'], $extraParms['PRAM_DB']);
                }

                $textDest = '<div style="text-align: center;">';
                $textDest .= '<span class="italsoft-tooltip italsoft-button italsoft-button--circled" title="' . htmlentities(nl2br($strDest), ENT_COMPAT | ENT_HTML5, 'ISO-8859-1') . '">';
                $textDest .= "<b>" . count($arrayDest) . "</b>";
                $textDest .= '</span>';
                $textDest .= '</div>';
            }
            $tableRow[] = $textDest;

            $tableData['body'][] = $tableRow;
        }

        $html->addTable($tableData);

        if ($multiClass && count($classificazioni) > 1) {
            $html->appendHtml('</div>');
            $html->appendHtml('</div>');
        }

        $html->addBr();

        return $html->getHtml();
    }

    function GetGridPareri($Pareri_tab, $extraParms) {
        $html = new html();

        $tableData = array(
            'caption' => 'Pareri Espressi/Integrazioni Richieste',
            'header' => array('Data', 'Mittente', 'Descrizione', 'Note', 'Allegati'),
            'body' => array()
        );

        foreach ($Pareri_tab as $Pareri_rec) {
            $tableRow = array();

            $tableRow[] = frontOfficeLib::convertiData($Pareri_rec['COMDAT']);
            $tableRow[] = $Pareri_rec['COMNOM'] . "<br>" . $Pareri_rec['COMMLD'];
            $tableRow[] = $Pareri_rec['PRODPA'];
            $tableRow[] = $Pareri_rec['COMNOT'];

            $textAllegati = '';

            $pasdoc_tab = ItaDB::DBSQLSelect($extraParms['PRAM_DB'], "SELECT * FROM PASDOC WHERE PASKEY = '" . $Pareri_rec['PROPAK'] . "' ORDER BY ROWID", true);
            foreach ($pasdoc_tab as $pasdoc_rec) {
                $allegatoHref = ItaUrlUtil::GetPageUrl(array(
                            'event' => 'gestioneAllegato',
                            'operation' => 'view',
                            'htok' => md5($pasdoc_rec['PASFIL']),
                            'fileIdE' => frontOfficeApp::encrypt($pasdoc_rec['ROWID'])
                ));

                if ($textAllegati !== '') {
                    $textAllegati .= '<br />';
                }

                $textAllegati .= '<a href="' . $allegatoHref . '" style="display: block;">';
                $textAllegati .= '<div style="position: relative; padding-left: 36px; min-height: 24px; margin-top: 5px;">';
                $textAllegati .= '  <div style="position: absolute; left: 0; top: -4px;">';
                $textAllegati .= $html->getImage(frontOfficeLib::getFileIcon($pasdoc_rec['PASFIL']), '24px', 'Vedi allegato');
                $textAllegati .= '  </div>';
                $textAllegati .= $pasdoc_rec['PASNAME'] ?: $pasdoc_rec['PASNOT'];
                $textAllegati .= '</div>';
                $textAllegati .= '</a>';
            }

            $tableRow[] = $textAllegati;
            $tableData['body'][] = $tableRow;
        }

        $html->addTable($tableData, array('sortable' => true, 'paginated' => true));

        return $html->getHtml();
    }

}
