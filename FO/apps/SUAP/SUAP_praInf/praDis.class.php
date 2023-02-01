<?php

class praDis extends praSchedaTemplate {

    public $PRAM_DB_R;

    public function getHtml($dati) {
        $html = new html();

        $PRAM_SOURCE = $dati['pramSource'];
        if ($dati['Anapra_rec']['ANAINF'] == '1') {
            $PRAM_SOURCE = $this->PRAM_DB;
        }

        $sql = "SELECT *
              FROM ITEDIS ITEDIS LEFT OUTER JOIN ANADIS ANADIS ON ITEDIS.DISCOD = ANADIS.DISCOD";
        $sql .= " WHERE ITEPRA = '" . $dati['Codice'] . "'";
        $Itedis_tab = ItaDB::DBSQLSelect($PRAM_SOURCE, $sql, true);

        if (!$Itedis_tab) {
            return false;
        }

        $tableData = array(
            'header' => array(
                array('text' => 'Fonte', 'attrs' => array('style' => 'width: 15%;')),
                'Riferimento',
                array('text' => 'Allegato', 'attrs' => array('style' => 'width: 10%; text-align: center;'))
            ),
            'body' => array()
        );

        foreach ($Itedis_tab as $Itedis_rec) {
            $tableRow = array();

            $tableRow[] = $Itedis_rec['DISTIP'];
            $tableRow[] = $Itedis_rec['DISDES'];

            $linkDisciplina = $html->getImage(frontOfficeLib::getIcon('ban'), '28px');

            if ($Itedis_rec['DISFIL']) {
                $disciplina = frontOfficeApp::encrypt($Itedis_rec['DISFIL']);
                $href = ItaUrlUtil::GetPageUrl(array(
                        'event' => 'vediAllegato',
                        'procedi' => $dati['Anapra_rec']['PRANUM'],
                        'disciplina' => $disciplina,
                        'type' => 'dis'
                ));

                $linkDisciplina = $html->getImage(frontOfficeLib::getFileIcon($Itedis_rec['DISFIL']), '28px', '', $href, '_blank');
            }

            $tableRow[] = '<div class="align-center">' . $linkDisciplina . '</div>';

            $tableData['body'][] = $tableRow;
        }

        $html->addTable($tableData);

        return $html->getHtml();
    }

}
