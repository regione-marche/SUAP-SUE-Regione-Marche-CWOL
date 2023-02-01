<?php

class praNor extends praSchedaTemplate {

    public function getHtml($dati) {
        $html = new html();

        $PRAM_SOURCE = $dati['pramSource'];
        if ($dati['Anapra_rec']['ANAINF'] == '1') {
            $PRAM_SOURCE = $this->PRAM_DB;
        }

        $sql = "SELECT
                    *
                FROM
                    ITENOR ITENOR
                LEFT OUTER JOIN ANANOR ANANOR ON ITENOR.NORCOD = ANANOR.NORCOD
                WHERE
                    ITEPRA = '" . $dati['Codice'] . "'";

        $Itenor_tab = ItaDB::DBSQLSelect($PRAM_SOURCE, $sql, true);

        if (!$Itenor_tab) {
            return false;
        }

        $tableData = array(
            'header' => array(
                array('text' => 'Fonte', 'attrs' => array('style' => 'width: 15%;')),
                'Riferimento',
                array('text' => 'Allegato', 'attrs' => array('style' => 'width: 10%; text-align: center;')),
                array('text' => 'Link', 'attrs' => array('style' => 'width: 10%; text-align: center;'))
            ),
            'body' => array()
        );

        foreach ($Itenor_tab as $Itenor_rec) {
            $tableRow = array();

            $tableRow[] = $Itenor_rec['NORTIP'];
            $tableRow[] = $Itenor_rec['NORDES'];

            $linkNormativa = $linkCollegamento = $html->getImage(frontOfficeLib::getIcon('ban'), '28px');

            if ($Itenor_rec['NORFIL']) {
                $normativa = frontOfficeApp::encrypt($Itenor_rec['NORFIL']);
                $href = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'vediAllegato',
                        'procedi' => $dati['Anapra_rec']['PRANUM'],
                        'normativa' => $normativa,
                        'type' => 'nor'
                ));

                $linkNormativa = $html->getImage(frontOfficeLib::getFileIcon($Itenor_rec['NORFIL']), '28px', '', $href, '_blank');
            }

            if ($Itenor_rec['NORURL']) {
                $linkCollegamento = $html->getImage(frontOfficeLib::getIcon('link'), '28px', '', $Itenor_rec['NORURL'], '_blank');
            }

            $tableRow[] = '<div class="align-center">' . $linkNormativa . '</div>';
            $tableRow[] = '<div class="align-center">' . $linkCollegamento . '</div>';


            $tableData['body'][] = $tableRow;
        }

        $html->addTable($tableData);

        return $html->getHtml();
    }

}
