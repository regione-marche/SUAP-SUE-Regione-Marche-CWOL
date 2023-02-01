<?php

class praProcCorr extends praSchedaTemplate {

    public function getHtml($dati) {
        $praLibEventi = new praLibEventi();
        $Iteevt_rec = $praLibEventi->getIteevt($this->PRAM_DB, frontOfficeApp::$cmsHost->getRequest('subprocid'));

        if (!$Iteevt_rec['IEVSTT'] && !$Iteevt_rec['IEVATT']) {
            return false;
        }
        //
        $PRAM_SOURCE = $this->PRAM_DB;

        //

        $html = new html();

        $sql = "SELECT
                    ANAPRA.PRANUM,
                    ITEEVT.ROWID AS ROWID_ITEEVT
                FROM
                    ANAPRA
                LEFT OUTER JOIN ITEEVT ON ITEEVT.ITEPRA = ANAPRA.PRANUM
                WHERE
                    ( ITEEVT.IEVDVA IS NULL OR ITEEVT.IEVDVA = '' OR ITEEVT.IEVDVA <= " . date('Ymd') . " ) AND
                    ( ITEEVT.IEVAVA IS NULL OR ITEEVT.IEVAVA = '' OR ITEEVT.IEVAVA >= " . date('Ymd') . " ) AND
                    ( ANAPRA.PRADVA IS NULL OR ANAPRA.PRADVA = '' OR ANAPRA.PRADVA <= " . date('Ymd') . " ) AND
                    ( ANAPRA.PRAAVA IS NULL OR ANAPRA.PRAAVA = '' OR ANAPRA.PRAAVA >= " . date('Ymd') . " ) AND
                    ITEEVT.IEVCOD <> '" . $Iteevt_rec['IEVCOD'] . "' AND
                    ITEEVT.IEVTSP = '" . $Iteevt_rec['IEVTSP'] . "' AND 
                    ITEEVT.IEVSTT = '" . $Iteevt_rec['IEVSTT'] . "' AND 
                    ITEEVT.IEVATT = '" . $Iteevt_rec['IEVATT'] . "' AND
                    ITEEVT.IEVTIP <> '' AND
                    ANAPRA.PRAOFFLINE = 0";

        $Proc_correlati_tab = ItaDB::DBSQLSelect($PRAM_SOURCE, $sql, true);

        if (!count($Proc_correlati_tab)) {
            return false;
        }

        require_once (ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praLibEventi.class.php');

        $html->appendHtml('<ul>');
        foreach ($Proc_correlati_tab as $Proc_correlati_rec) {
            $Iteevt_rec = $praLibEventi->getIteevt($PRAM_SOURCE, $Proc_correlati_rec['ROWID_ITEEVT']);
            $OggettoEvento = $praLibEventi->getOggetto($PRAM_SOURCE, $Proc_correlati_rec['PRANUM'], $Iteevt_rec);
            $href = ItaUrlUtil::GetPageUrl(array('p' => $this->config['info_page'], 'event' => 'openBlock', 'procedi' => $Proc_correlati_rec['PRANUM'], 'subproc' => $Iteevt_rec['IEVCOD'], 'subprocid' => $Iteevt_rec['ROWID'], 'rnd' => $rnd));
            $html->appendHtml("<li><a href=\"" . $href . "\" title=\"Clicca per vedere le info di questo procedimento\">" . $OggettoEvento . "</a></li>");
        }
        $html->appendHtml('</ul>');

        return $html->getHtml();
    }

}
