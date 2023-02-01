<?php

class praDocCount extends itaModelFO {

    public $praLib;
    public $praErr;
    public $PRAM_DB;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);

            /*
             * Caricamento database.
             */
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', frontOfficeApp::getEnte());
        } catch (Exception $e) {
            
        }
    }

    public function parseEvent() {
        $userFiscale = frontOfficeApp::$cmsHost->getCodFisFromUtente();

        if ($userFiscale == "") {
            return;
        }

        $html = new html();
        $this->DisegnaPagina($userFiscale, $html);

        return output::$html_out = $html->getHtml();
    }

    function DisegnaPagina($cod_fis, $html) {
        $sql = "SELECT 
                    PRORIC.RICNUM AS RICNUM,
                    PRORIC.RICPRO AS RICPRO,
                    PRORIC.RICTIM AS RICTIM,
                    PRORIC.RICNPR AS RICNPR,
                    PRORIC.RICDPR AS RICDPR,
                    PRORIC.RICDAT AS RICDAT,
                    PRORIC.RICDRE AS RICDRE,
                    PRORIC.RICSTA AS RICSTA,
                    PRORIC.RICFIS AS RICFIS,
                    PRORIC.RICORE AS RICORE,
                    PROGES.GESDRE AS GESDRE,
                    PROGES.GESDCH AS GESDCH,
                    PROGES.GESNUM AS GESNUM,
                    PRORIC.RICRPA AS RICRPA,
                    PRORIC.RICAGE AS RICAGE
                FROM 
                    PRORIC PRORIC
                INNER JOIN PROGES PROGES ON PRORIC.RICNUM=PROGES.GESPRA
                WHERE 
                    PRORIC.RICFIS = '$cod_fis' AND
                    PRORIC.RICSTA<>'OF' 
                GROUP BY 
                    PRORIC.RICNUM";
        $Proric_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql, true);

        //
        $new = 0;
        foreach ($Proric_tab as $Proric_rec) {
            $Propas_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PROPAS WHERE PRONUM='" . $Proric_rec['GESNUM'] . "' AND PROPUBALL = 1 ORDER BY PROSEQ", true);
            foreach ($Propas_tab as $Propas_rec) {
                $Pasdoc_tab = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PASDOC WHERE PASKEY='" . $Propas_rec['PROPAK'] . "' AND PASPUB = 1", true);
                if ($Pasdoc_tab) {
                    $Ricsta_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM RICSTA WHERE RICNUM='" . $Proric_rec['RICNUM'] . "' AND PROPAK='" . $Propas_rec['PROPAK'] . "'", false);
                    if (!$Ricsta_rec) {
                        $new++;
                    }
                }
            }
        }

        if ($new == 0) {
            $msg = "<div style=\"display:inline-block;vertical-align:middle;color:red;font-size:1.3em;\">Non ci sono nuovi allegati da leggere.</div>";
            $imgNew = "";
        } else {
            $strCount = $new === 1 ? 'nuovo<br />allegato' : 'nuovi<br />allegati';
            $imgNew = "<div style=\"display:inline-block;\"><img src=\"" . frontOfficeLib::getIcon('new') . "\" width=\"65\" height=\"65\" style = \"border:0px;vertical-align:middle;margin-right:5px;\"></img></div>";
            $msg = "<div style=\"display:inline-block;vertical-align:middle;color:red;font-size:1.3em;\">Hai <b>$new</b> $strCount da leggere.</div>";
        }
        $href = ItaUrlUtil::GetPageUrl(array('p' => $this->config['doc_page']));
        $html->appendHtml("<a href=\"$href\">$imgNew$msg</a>");
    }

}
