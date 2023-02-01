<?php

class provider_Italsoft extends provider_Abstract {

    function __construct() {
        
    }

    function getProviderType() {
        return "Italsoft";
    }

    public function getVie() {
        try {
            $ANEL_DB = ItaDB::DBOpen('ANEL');
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = "Apertura archivio Anagrafe fallita!";
            return false;
        }
        $listaVie = itaDB::DBSQLSelect($ANEL_DB, $this->creaSqlVie(), true);
        $vie = array();
        foreach ($listaVie as $key => $via) {
            $vie[$key]['CODICEVIA'] = $via['CODIND'];
            $vie[$key]['TOPONIMO'] = $via['SPECIE'];
            $vie[$key]['NOMEVIA'] = $via['INDIR'];
        }
        return $vie;
    }

    public function getCittadiniLista($ricParam) {
//        if (!isset($ricParam['SOLORESIDENTI'])) {
//            $ricParam['SOLORESIDENTI'] = true;
//        }
        try {
            $ANEL_DB = ItaDB::DBOpen('ANEL');
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = "Apertura archivio Anagrafe fallita!";
            return false;
        }
        if ($ricParam['NOME'] == '' && $ricParam['COGNOME'] == '' && $ricParam['CODICEFISCALE'] == '' && $ricParam['CODICEVIA'] == '') {
            return false;
        }
        $listaCittadini_tab = itaDB::DBSQLSelect($ANEL_DB, $this->creaSqlAna($ricParam, $ANEL_DB), true);
        foreach ($listaCittadini_tab as $key => $record) {
            $codtri = $record['CODICEUNIVOCO'];
            $sql = "SELECT 
                    ANAGRA.ROWID AS ROWID,
                    ANAGRA.GGMOR AS GGMOR,
		    ANAGRA.AUSILI AS AUSILI
                    FROM ANAGRA WHERE ANAGRA.CODTRI = $codtri";
            $Anagra_rec = ItaDB::DBSQLSelect($ANEL_DB, $sql, false);
            if ($Anagra_rec['AUSILI'] != '') {
                $listaCittadini_tab[$key]['AIRE'] = $Anagra_rec['AUSILI'];
            } else {
                $listaCittadini_tab[$key]['AIRE'] = '';
            }
            if ($Anagra_rec['GGMOR'] != 0) {
                $listaCittadini_tab[$key]['DECESSO'] = '1';
            } else {
                $listaCittadini_tab[$key]['DECESSO'] = '';
            }
            $sql = "SELECT
                    IMMIGR.ROWID AS ROWID,
                    (IMMIGR.AAEMIG*10000+IMMIGR.MMEMIG*100+IMMIGR.GGEMIG) AS DATAEMIGRAZIONE,
                    (IMMIGR.AAIMMI*10000+IMMIGR.MMIMMI*100+IMMIGR.GGIMMI) AS DATAIMMIGRAZIONE
                    FROM IMMIGR WHERE IMMIGR.CODTRI = $codtri AND AAEMIG<>0";

            $Immigr_rec = ItaDB::DBSQLSelect($ANEL_DB, $sql, false);
            $listaCittadini_tab[$key]['DATAEMIGRAZIONE'] = $Immigr_rec['DATAEMIGRAZIONE'];
            $listaCittadini_tab[$key]['DATAIMMIGRAZIONE'] = $Immigr_rec['DATAIMMIGRAZIONE'];

            $sql = "SELECT
                    MATRIM.CONIUG AS CONIUGE
                    FROM MATRIM WHERE MATRIM.CODTRI = $codtri";

            $Matrim_rec = ItaDB::DBSQLSelect($ANEL_DB, $sql, false);
            $listaCittadini_tab[$key]['CONIUGE'] = $Matrim_rec['CONIUGE'];
            $listaCittadini_tab[$key]['PROFESSIONE'] = $record['PROFESSIONE'];
            $listaCittadini_tab[$key]['TITOLOSTUDIO'] = $record['TITOLOSTUDIO'];
            $listaCittadini_tab[$key]['CITTADINANZA'] = $record['CITTADINANZA'];
            $listaCittadini_tab[$key]['CARTAIDENTITA'] = $record['CARTAIDENTITA'];
            $listaCittadini_tab[$key]['CARTAIDENTITARIL'] = $record['CARTAIDENTITARIL'];
            $listaCittadini_tab[$key]['CARTAIDENTITASCA'] = '';
            $listaCittadini_tab[$key]['CODICEPREC'] = $codtri;
            $listaCittadini_tab[$key]['LUOGOIMMI'] = '';
            $listaCittadini_tab[$key]['LUOGOEMI'] = '';
            $listaCittadini_tab[$key]['DATADECESSO'] = $record['DATADECESSO'];
            
        }
        if (!$listaCittadini_tab) {
            $this->lastExitCode = -1;
            $this->lastMessage = "Nessun record trovato";
            return false;
        }
        $this->lastExitCode = 0;
        $this->lastMessage = "";
        return $listaCittadini_tab;
    }

    public function getCittadinoFamiliari($param) {
        try {
            $ANEL_DB = ItaDB::DBOpen('ANEL');
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = "Apertura archivio Anagrafe fallita!";
            return false;
        }
        $family = $param['FAMILY'];
        $anagraTab = ItaDB::DBSQLSelect($ANEL_DB, "SELECT * FROM ANAGRA WHERE FAMILY = $family ORDER BY COGNOM, NOME", true);
        $righeFam = array();
        $indice = 0;
        if ($anagraTab) {
            foreach ($anagraTab as $anagraRec) {
                $via = $anagraRec['CODIND'];
                $anindiRec = ItaDB::DBSQLSelect($ANEL_DB, "SELECT * FROM ANINDI WHERE CODIND = '$via'", false);
                $parentela = $anagraRec['CODREL'];
                $relparRec = ItaDB::DBSQLSelect($ANEL_DB, "SELECT * FROM RELPAR WHERE CODREL = '$parentela'", false);
                $statoCivile = $anagraRec['CODSTA'];
                $scivilRec = ItaDB::DBSQLSelect($ANEL_DB, "SELECT * FROM SCIVIL WHERE CODSTA = '$statoCivile'", false);
                $lavoro = ItaDB::DBSQLSelect($ANEL_DB, "SELECT * FROM LAVORO WHERE CODTRI = " . $anagraRec['CODTRI'], false);
                $righeFam[$indice]['CODICEFISCALE'] = $lavoro['FISCAL'];
                $righeFam[$indice]['COGNOM'] = $anagraRec['COGNOM'];
                $righeFam[$indice]['NOME'] = $anagraRec['NOME'];
                $righeFam[$indice]['PARENTELA'] = $relparRec['DESREL'];
                $righeFam[$indice]['STATOCIVILE'] = $scivilRec['DESSTA'];
                $righeFam[$indice]['DATNAT'] = sprintf("%02d", $anagraRec['GGNAT']) . '/' . sprintf("%02d", $anagraRec['MMNAT']) . '/' . sprintf("%04d", $anagraRec['AANAT']);
                $codtri = $anagraRec['CODTRI'];
                $morteRec = ItaDB::DBSQLSelect($ANEL_DB, "SELECT * FROM MORTE WHERE CODTRI = $codtri", false);
                if ($morteRec) {
                    $righeFam[$indice]['NOTE'] = 'Deceduto il ' . sprintf("%02d", $anagraRec['GGMOR']) . '/' . sprintf("%02d", $anagraRec['MMMOR']) . '/' . sprintf("%04d", $anagraRec['AAMOR']);
                }
                $immigrRec = ItaDB::DBSQLSelect($ANEL_DB, "SELECT * FROM IMMIGR WHERE CODTRI = $codtri ORDER BY AAEMIG DESC", true);
                $dataEmig = $dataImmig = $dataMorte = '';
                if ($anagraRec['AAMOR']){
                    $dataMorte = sprintf("%04d", $anagraRec['AAMOR']) . sprintf("%02d", $anagraRec['MMMOR']) . sprintf("%02d", $anagraRec['GGMOR']);
                }
                if ($immigrRec) {
                    $dataEmig = sprintf("%04d", $immigrRec[0]['AAEMIG']) . sprintf("%02d", $immigrRec[0]['MMEMIG']) . sprintf("%02d", $immigrRec[0]['GGEMIG']);
                    $dataImmig = sprintf("%04d", $immigrRec[0]['AAIMMI']) . sprintf("%02d", $immigrRec[0]['MMIMMI']) . sprintf("%02d", $immigrRec[0]['GGIMMI']);
                    if ($dataEmig > $dataImmig) {
                        $righeFam[$indice]['NOTE'] = 'Emigrato a ' . $immigrRec['COMEMI'] . ' il ' . sprintf("%02d", $immigrRec[0]['GGEMIG']) . '/' . sprintf("%02d", $immigrRec[0]['MMEMIG']) . '/' . sprintf("%04d", $immigrRec[0]['AAEMIG']);
                    }
                }
                $righeFam[$indice]['DATAEMIGRAZIONE'] = $dataEmig;
                $righeFam[$indice]['DATAIMMIGRAZIONE'] = $dataImmig;
                $righeFam[$indice]['DATAMORTE'] = $dataMorte;
                $righeFam[$indice]['DATADECESSO'] = $dataMorte;
                
                $indice = $indice + 1;
            }
        }
        return $righeFam;
    }

    public function getCittadinoVariazioni($param) {
        try {
            $ANEL_DB = ItaDB::DBOpen('ANEL');
        } catch (Exception $e) {
            $this->lastExitCode = -2;
            $this->lastMessage = "Apertura archivio Anagrafe fallita!";
            return false;
        }
        $codtri = $param['CODICEUNIVOCO'];
        $variazioniTab = ItaDB::DBSQLSelect($ANEL_DB, "SELECT * FROM VFCODT WHERE VCODTR = $codtri ORDER BY VDATA DESC", true);
        $righeVar = array();
        $indice = 0;
        if ($variazioniTab) {
            foreach ($variazioniTab as $variazioniRec) {
                $righeVar[$indice]['DATA'] = sprintf("%02d", $variazioniRec['VGGVA']) . '/' . sprintf("%02d", $variazioniRec['VMMVA']) . '/' . sprintf("%04d", $variazioniRec['VAAVA']);
                $righeVar[$indice]['CODICE'] = $variazioniRec['VCODVA'];
                switch ($variazioniRec['VCODVA']) {
                    case '01':
                        $des = 'TESSERA ELETTORALE';
                        break;
                    case '02':
                        $des = 'E/U FAMIGLIA';
                        break;
                    case '04':
                        $des = 'LIBRETTO DI LAVORO';
                        break;
                    case '05':
                        $des = 'CARTA D\'IDENTITA';
                        break;
                    case '16':
                        $des = 'CENSIMENTO';
                        break;
                    case '17':
                        $des = 'POP. INASAIA RESIDENTI';
                        break;
                    case '18':
                        $des = 'POP. INASAIA EMIGRATI';
                        break;
                    case '19':
                        $des = 'POP. INASAIA DEFUNTI';
                        break;
                    case '20':
                        $des = 'TESSERA ELETTORALE';
                        break;
                    case '21':
                        $des = 'MORTE';
                        break;
                    case '22':
                        $des = 'MATRIMONIO';
                        break;
                    case '23':
                        $des = 'SCIOGLIMENTO MATRIMONIO';
                        break;
                    case '24':
                        $des = 'VARIAZ. DI CITTADINANZA';
                        break;
                    case '25':
                        $des = 'ISCRIZ.ANAGRAFICA';
                        $des .= ' - ' . trim($variazioniRec['VCOMU']);
                        break;
                    case '26':
                        $des = 'EMIGRAZIONE';
                        $des .= ' - ' . trim($variazioniRec['VCOMU']);
                        break;
                    case '27':
                        $des = 'VARIAZIONE DI INDIRIZZO';
                        $anindiRec = ItaDB::DBSQLSelect($ANEL_DB, "SELECT * FROM ANINDI WHERE CODIND = '" . $variazioniRec['VCODIN'] . "'", false);
                        $des .= ' - ' . trim($anindiRec['SPECIE']) . ' ' . $anindiRec['INDIR'] . ' ' . $variazioniRec['VSERIE'];
                        break;
                    case '28':
                        $des = 'COGNOME E NOME';
                        break;
                    case '29':
                        $des = 'LUOGO E DATA DI NASCITA';
                        break;
                    case '30':
                        $des = 'PROFESSIONE';
                        break;
                    case '31':
                        $des = 'TITOLO DI STUDIO';
                        break;
                    case '32':
                        $des = 'INDIRIZZO ALL\'ESTERO';
                        break;
                    case '33':
                        $des = 'SESSO';
                        break;
                    case '34':
                        $des = 'PERDITA DIRITTO ELETT.';
                        break;
                    case '35':
                        $des = 'ACQUISTO DIR.ELETTORALE';
                        break;
                    case '37':
                        $des = 'IRREPERIBILITA\'';
                        break;
                    case '38':
                        $des = 'ISCRIZIONE AIRE';
                        break;
                    case '39':
                        $des = 'RINNOVO PERM.DI SOGGIORNO';
                        break;
                    default:
                        $des = 'Variazione non utile al fine dei controlli.';
                }
                $righeVar[$indice]['EVENTO'] = $des;
                $indice = $indice + 1;
            }
        }
        return $righeVar;
    }

    function getLastExitCode() {
        return $this->lastExitCode;
    }

    function getLastMessage() {
        return $this->lastMessage;
    }

    function setLastMessage() {
        $this->lastMessage = print_r($this->getLastOutput(), true);
    }

    private function creaSqlAna($ricParm, $ANEL_DB) {
        $where = "";
        if ($ricParm['SOLORESIDENTI'] == true) {
            $where = "ANAGRA.AUSILI='' AND ANAGRA.GGMOR=0";
        } else {
            $where = "1=1";
        }

        if ($ricParm['CODICEFISCALE']) {
            $where .= " AND LAVORO.FISCAL='" . $ricParm['CODICEFISCALE'] . "'";
        } else {

            if ($ricParm['CODICEVIA']) {
                $where = " ANAGRA.CODIND ='" . $ricParm['CODICEVIA'] . "'";
                if ($ricParm['DALCIVICO']) {
                    $ricParm['DALCIVICO'] = str_pad(trim($ricParm['DALCIVICO']), 4, " ", STR_PAD_LEFT);
                    $where .= " AND ANAGRA.CIVICO >= '" . $ricParm['DALCIVICO'] . "'";
                }
                if ($ricParm['ALCIVICO']) {
                    $ricParm['ALCIVICO'] = str_pad(trim($ricParm['ALCIVICO']), 4, " ", STR_PAD_LEFT);
                    $where .= " AND ANAGRA.CIVICO <= '" . $ricParm['ALCIVICO'] . "'";
                }
            } else {
                $arrNome[] = addslashes(trim($ricParm['COGNOME']));
                $arrNome[] = addslashes(trim($ricParm['NOME']));
                if ($arrNome[0]) {
                    if (!$arrNome[1]) {
                        $arrNome[0] .='%';
                        $where .= " AND ANAGRA.COGNOM LIKE '" . $arrNome[0] . "'";
                    } else {
                        $where .= " AND ANAGRA.COGNOM = '" . $arrNome[0] . "'";
                        $arrNome[1] .='%';
                    }
                }
                if ($arrNome[1]) {
                    $where .= " AND ANAGRA.NOME LIKE '" . $arrNome[1] . "'";
                }
            }
        }

        $sql = "SELECT 
                    ANAGRA.ROWID AS ROWID,
                    ANAGRA.CODTRI AS CODICEUNIVOCO,
                    ANAGRA.COGNOM AS COGNOME,
                    ANAGRA.NOME AS NOME,
                    (" . $ANEL_DB->strConcat("ANAGRA.COGNOM", "' '", "ANAGRA.NOME") . ") AS NOMINATIVO,
                    (ANAGRA.AANAT*10000+ANAGRA.MMNAT*100+ANAGRA.GGNAT) AS DATANASCITA,
                    (ANAGRA.AAMOR*10000+ANAGRA.MMMOR*100+ANAGRA.GGMOR) AS DATADECESSO,
                    ANAGRA.INDNAT AS LUOGONASCITA,
                    ANAGRA.PROVNA AS PROVINCIANASCITA,
                    ANAGRA.SEX AS SESSO,
                    ANAGRA.CODIND AS CODICEVIA,
                    ANAGRA.CIVICO AS CIVICO,
                    ANAGRA.FAMILY AS FAMILY,
                    ANAGRA.NAZION AS CITTADINANZA,
                    ANAGRA.CARTID AS CARTAIDENTITA,
                    (ANAGRA.AACART*10000+ANAGRA.MMCART*100+ANAGRA.GGCART) AS CARTAIDENTITARIL,

                    LAVORO.FISCAL AS CODICEFISCALE,
                    LAVORO.PATERN AS PATERNITA,
                    LAVORO.MATERN AS MATERNITA,
                    LAVORO.TSTUDI AS TITOLOSTUDIO,
                    LAVORO.PROFES AS PROFESSIONE,
                    
                    ANACIT.RESID AS RESIDENZA,
                    ANACIT.CAP AS CAP,
                    ANACIT.ITAEST AS PROVINCIA,
  
                    (" . $ANEL_DB->strConcat("ANINDI.SPECIE", "' '", "ANINDI.INDIR") . ") AS INDIRIZZO
                    
                FROM
                    ANAGRA
                    LEFT OUTER JOIN LAVORO ON LAVORO.CODTRI=ANAGRA.CODTRI 
                    LEFT OUTER JOIN ANACIT ON ANACIT.CODCIT=ANAGRA.CODCIT  
                    LEFT OUTER JOIN ANINDI ON ANINDI.CODIND=ANAGRA.CODIND  
                    
                WHERE " . $where . "
                ORDER BY ANAGRA.COGNOM, ANAGRA.NOME";



//        $sql = "SELECT 
//                    ANAGRA.ROWID AS ROWID,
//                    ANAGRA.CODTRI AS CODICEUNIVOCO,
//                    ANAGRA.COGNOM AS COGNOME,
//                    ANAGRA.NOME AS NOME,
//                    (" . $ANEL_DB->strConcat("ANAGRA.COGNOM", "' '", "ANAGRA.NOME") . ") AS NOMINATIVO,
//                    (ANAGRA.AANAT*10000+ANAGRA.MMNAT*100+ANAGRA.GGNAT) AS DATANASCITA,
//                    (ANAGRA.AAMOR*10000+ANAGRA.MMMOR*100+ANAGRA.GGMOR) AS DATADECESSO,
//                    LAVORO.FISCAL AS CODICEFISCALE,
//                    LAVORO.PATERN AS PATERNITA,
//                    LAVORO.MATERN AS MATERNITA,
//                    ANAGRA.INDNAT AS LUOGONASCITA,
//                    ANAGRA.PROVNA AS PROVINCIANASCITA,
//                    ANAGRA.SEX AS SESSO,
//                    ANAGRA.CIVICO AS CIVICO,
//                    ANAGRA.FAMILY AS FAMILY,
//                    ANACIT.RESID AS RESIDENZA,
//                    ANACIT.CAP AS CAP,
//                    ANACIT.ITAEST AS PROVINCIA,
//                    MATRIM.CONIUG AS CONIUGE,
//                    (" . $ANEL_DB->strConcat("ANINDI.SPECIE", "' '", "ANINDI.INDIR") . ") AS INDIRIZZO
//                FROM
//                    ANAGRA
//                LEFT OUTER JOIN LAVORO ON LAVORO.CODTRI=ANAGRA.CODTRI
//                LEFT OUTER JOIN ANACIT ON ANACIT.CODCIT=ANAGRA.CODCIT
//                LEFT OUTER JOIN ANINDI ON ANINDI.CODIND=ANAGRA.CODIND
//                LEFT OUTER JOIN MATRIM ON MATRIM.CODTRI=ANAGRA.CODTRI
//                WHERE " . $where . "
//                ORDER BY ANAGRA.COGNOM, ANAGRA.NOME";
        return $sql;
    }

    private function creaSqlVie() {
        $sql = "SELECT * FROM ANINDI ORDER BY SPECIE, INDIR";
        return $sql;
    }

}

?>
