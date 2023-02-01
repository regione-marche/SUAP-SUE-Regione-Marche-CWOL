<?php

class praReq extends praSchedaTemplate {

    public function getHtml($dati) {
        $html = new html();

        $PRAM_SOURCE = $dati['pramSource'];
        if ($dati['Anapra_rec']['ANAINF'] == '1') {
            $PRAM_SOURCE = $this->PRAM_DB;
        }

        $sql = "SELECT DISTINCT ANAREQ.REQTIPO AS REQTIPO
                FROM ITEREQ ITEREQ
                LEFT OUTER JOIN ANAREQ ANAREQ ON ITEREQ.REQCOD = ANAREQ.REQCOD
                WHERE ITEPRA = '" . $dati['Codice'] . "' ORDER BY REQTIPO DESC";

        $Itereq_tipi_tab = ItaDB::DBSQLSelect($PRAM_SOURCE, $sql, true);

        if ($Itereq_tipi_tab) {
            $html->appendHtml("<div>");
            $html->appendHtml('<span class="infoText">Qui di seguito trovi i requisiti per l\'avvio della richiesta.</span><br />');
            $html->appendHtml("</div>");

            foreach ($Itereq_tipi_tab as $Itereq_tipi_rec) {
                $html->appendHtml('<br />');
                $html->appendHtml('<div>');
                $html->appendHtml('<div class="infoLabel">Requisito</div>');
                $html->appendHtml('<span class="infoText">' . $Itereq_tipi_rec['REQTIPO'] . '</span><br />');
                $html->appendHtml('</div>');

                $sql = "SELECT DISTINCT ANAREQ.REQAREA AS REQAREA
                        FROM ITEREQ ITEREQ
                        LEFT OUTER JOIN ANAREQ ANAREQ ON ITEREQ.REQCOD = ANAREQ.REQCOD
                        WHERE ITEPRA = '" . $dati['Codice'] . "' AND REQTIPO = '" . $Itereq_tipi_rec['REQTIPO'] . "' ORDER BY REQAREA";

                $Itereq_aree_tab = ItaDB::DBSQLSelect($PRAM_SOURCE, $sql, true);

                if ($Itereq_aree_tab) {
                    foreach ($Itereq_aree_tab as $Itereq_aree_rec) {
                        $tableData = array(
                            'header' => array(
                                $Itereq_aree_rec['REQAREA'], '<div class="align-center">Allegato</div>'
                            ),
                            'body' => array()
                        );

                        $sql = "SELECT * FROM ITEREQ ITEREQ
                                LEFT OUTER JOIN ANAREQ ANAREQ ON ITEREQ.REQCOD = ANAREQ.REQCOD
                                WHERE ITEPRA = '" . $dati['Codice'] . "' AND REQTIPO = '" . $Itereq_tipi_rec['REQTIPO'] . "' AND REQAREA = '" . $Itereq_aree_rec['REQAREA'] . "' ORDER BY REQDES";

                        $Itereq_tab = ItaDB::DBSQLSelect($PRAM_SOURCE, $sql, true);

                        if ($Itereq_tab) {
                            foreach ($Itereq_tab as $Itereq_rec) {
                                $href = '';
                                $image = frontOfficeLib::getIcon('ban');

                                if ($Itereq_rec['REQFIL']) {
                                    $requisito = frontOfficeApp::encrypt($Itereq_rec['REQFIL']);

                                    $href = ItaUrlUtil::GetPageUrl(array(
                                            'event' => 'vediAllegato',
                                            'procedi' => $dati['Anapra_rec']['PRANUM'],
                                            'requisito' => $requisito,
                                            'type' => 'req'
                                    ));

                                    $image = frontOfficeLib::getFileIcon($Itereq_rec['REQFIL']);
                                }

                                $tableData['body'][] = array(
                                    $Itereq_rec['REQDES'],
                                    '<div class="align-center">' . $html->getImage($image, '28px', '', $href, '_blank') . '</div>'
                                );
                            }
                        }

                        $html->addTable($tableData);
                    }
                }
            }
        } else {
            return false;
        }

        return $html->getHtml();
    }

}
