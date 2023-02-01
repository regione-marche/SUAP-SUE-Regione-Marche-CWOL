<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of praErr
 *
 * @author Andrea
 */

class FiereMercati {

    public $workDate;

    function __construct() {
        $this->workDate = date("Ymd");
    }

    function GetSqlFiere($dati) {
        switch ($dati['Codice']) {
            case "500006": // Imprenditore Agricolo
                $whereAttivita = "  AND TIPOATTIVITA = 'PA'";
                break;
            case "500007": // Commercianti
                $whereAttivita = "  AND TIPOATTIVITA = 'C'";
                break;
            case "500008": // Artigiani
                $whereAttivita = "  AND TIPOATTIVITA = 'PR'";
                break;
        }
        $sql = "SELECT
                        FIERE.*,
                        ANAFIERE.FIERA AS NOMEFIERA
                     FROM 
                        FIERE
                     INNER JOIN 
                        ANAFIERE ON ANAFIERE.TIPO=FIERE.FIERA
                     WHERE
                        FIERE.DECENNALE = 0 AND
                        FIERE.BANDO = 1 AND
                        FIERE.BLOCCAPUBB = 0 AND
                        FIERE.DATATERMINE >= $this->workDate
                        $whereAttivita";
        return ItaDB::DBSQLSelect($dati['GAFIERE_DB'], $sql, true);
    }

    function GetSqlMercati($dati) {
        switch ($dati['Codice']) {
            case "500009":
                $whereAttivita = "  AND TIPOATTIVITA <> 'C'";
                break;
            case "500010":
                $whereAttivita = "  AND TIPOATTIVITA = 'C'";
                break;
        }
        $sql = "SELECT
                        BANDIM.*,
                        ANAMERC.MERCATO AS NOMEFIERA
                     FROM 
                        BANDIM
                     INNER JOIN 
                        ANAMERC ON ANAMERC.CODICE=BANDIM.FIERA
                     WHERE
                        BANDIM.DECENNALE = 1 AND
                        BANDIM.BANDO = 1 AND
                        BANDIM.BLOCCAPUBB = 0 AND
                        BANDIM.DATATERMINE >= $this->workDate
                        $whereAttivita";
        return ItaDB::DBSQLSelect($dati['GAFIERE_DB'], $sql, true);
    }

}

?>
