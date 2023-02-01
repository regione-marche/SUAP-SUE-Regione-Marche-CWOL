<?php

/**
 *
 * LIBRERIA PER APPLICATIVO PRATICHE
 *
 * PHP Version 5
 *
 * @category   Class
 * @package    Pratiche
 * @author     Andrea Bufarini <andrea.bufarini@italsoft.eu>
 * @copyright  1987-2018 Italsoft snc
 * @license
 * @version    17.04.2018
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Pratiche/praRuolo.class.php';
include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';

class praLibTributaria {

    /**
     * Libreria di funzioni Generiche e Utility per Pratiche
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $PRAM_DB;
    public $praLib;
    private $errMessage;
    private $errCode;
    static public $TIPO_SEGNALAZIONE = array(
        '' => 'Nessuna',
        'ALTRO' => 'Altro',
        'APERTURA' => 'Apertura',
        'CESSAZIONE' => 'Cessazione',
        'MODIFICHE' => 'Modifiche',
        'SUBENTRO' => 'Subentro',
        'TRASFORMAZIONE' => 'Trasformazione',
        'FIERE' => 'Fiera',
    );

    function __construct() {
        try {
            $this->PRAM_DB = ItaDB::DBOpen('PRAM');
            $this->praLib = new praLib();
        } catch (Exception $e) {
            Out::msgStop("Errore", $e->getMessage());
            return;
        }
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function setPRAMDB($PRAM_DB) {
        $this->PRAM_DB = $PRAM_DB;
    }

    public function getPRAMDB() {
        return $this->PRAM_DB;
    }

    function GetNomeAlbo($gesnum, $ruocod) {
        $prefix = praRuolo::getSystemSubjectRoleFields($ruocod);
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND DAGKEY = '" . $prefix . "_ORDINEAPPARTENENZA" . "' AND DAGVAL <> ''", false);
        if (!$prodag_rec) {
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND DAGKEY = 'TECNICO_PROCURA_ED_020' AND DAGVAL <> ''", false);
            if (!$prodag_rec) {
                $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND DAGKEY = 'CILA_DICHIAR_PROGET_F_002' AND DAGVAL <> ''", false); // per proc 841
            }
        }
        $albo = "6"; //ALTRO
        if ($prodag_rec['DAGVAL']) {
            if (strpos(strtoupper($prodag_rec['DAGVAL']), "AGR") !== false) {
                $albo = "1"; //AGRONOMO
            } else if (strpos(strtoupper($prodag_rec['DAGVAL']), "ARC") !== false) {
                $albo = "2"; //ARCHITETTO
            } else if (strpos(strtoupper($prodag_rec['DAGVAL']), "GEO") !== false) {
                $albo = "3"; //GEOMETRA
            } else if (strpos(strtoupper($prodag_rec['DAGVAL']), "ING") !== false) {
                $albo = "4"; //INGEGNERE
            } else if (strpos(strtoupper($prodag_rec['DAGVAL']), "PERIT") !== false) {
                $albo = "5"; //PERITO
            }
        }
        return $albo;
    }

    function GetNumIscrizioneAlbo($professionista) {
        $prefix = praRuolo::getSystemSubjectRoleFields($professionista['DESRUO']);
        //
        $numero = $professionista['DESORDISCRIZIONE'];
        if ($numero == "") {
            $numero = $professionista['DESNUMISCRIZIONE'];
            if ($numero == "") {
                $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $professionista['DESNUM'] . "' AND DAGKEY = '" . $prefix . "_ORDINEISCRIZIONE" . "' AND DAGVAL <> ''", false);
                if (!$prodag_rec) {
                    $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $professionista['DESNUM'] . "' AND DAGKEY = '" . $prefix . "_NUMISCRIZIONE" . "' AND DAGVAL <> ''", false);
                    if (!$prodag_rec) {
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $professionista['DESNUM'] . "' AND DAGKEY = 'CILA_DICHIAR_PROGET_F_003B' AND DAGVAL <> ''", false);
                        if (!$prodag_rec) {
                            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $professionista['DESNUM'] . "' AND DAGKEY = 'TECNICO_PROCURA_ED_022' AND DAGVAL <> ''", false);
                        }
                    }
                }
            }
        }


        if ($prodag_rec) {
            $numero = $prodag_rec['DAGVAL'];
        }

        //return str_pad($numero, 10, " ", STR_PAD_RIGHT);
        return $numero;
    }

    function GetSessoByCF($codFisc) {
        $val = substr(trim($codFisc), 9, 2);
        if ($val > 0 && $val <= 31) {
            return "M";
        } elseif ($val > 40 && $val <= 71) {
            return "F";
        } else {
            return "";
        }
    }

    function GetSessoRichiesta($anades_rec) {
        if ($anades_rec['DESSESSO']) {
            return $anades_rec['DESSESSO'];
        }
        if ($anades_rec['DESSESSO'] == "") {
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $anades_rec['DESNUM'] . "' AND DAGKEY = 'DICHIARANTE_SESSO_SEX" . "' AND DAGVAL <> ''", false);
            if (!$prodag_rec) {
                $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $anades_rec['DESNUM'] . "' AND DAGKEY = 'DEFAULT_DICHIARANTE_SESSO_SEX" . "' AND DAGVAL <> ''", false);
            }
            return $prodag_rec['DAGVAL'];
        }
    }

    function GetQualifica($gesnum) {
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND DAGKEY = 'DICHIARANTE_TITOLARITA' AND DAGVAL <> ''", false);
        if (!$prodag_rec) {
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND DAGKEY = 'DICHIARANTE_QUALIFICA' AND DAGVAL <> ''", false);
            if (!$prodag_rec) {
                $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND DAGKEY = 'SCIA_ART5_F_032' AND DAGVAL <> ''", false);
                if (!$prodag_rec) {
                    $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND DAGKEY = '853_10_ED_070' AND DAGVAL <> ''", false);
                    if (!$prodag_rec) {
                        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND DAGKEY = 'PDC_RICH_F_051' AND DAGVAL <> ''", false);
                    }
                }
            }
        }

        $qualifica = "3"; //TITOLARE DI ALTRO DIRITTO SULL'IMMOBILE
        if ($prodag_rec['DAGVAL']) {
            if (strpos(strtoupper($prodag_rec['DAGVAL']), "PROPRIET") !== false) {
                $qualifica = "1"; //PROPRIETARIO
            } else if (strpos(strtoupper($prodag_rec['DAGVAL']), "USUFRUTTUAR") !== false) {
                $qualifica = "2"; //USUFRUTTUARIO
            } else if (strpos(strtoupper($prodag_rec['DAGVAL']), "TITOLAR") !== false) {
                $qualifica = "3"; //TITOLARE DI ALTRO DIRITTO SULL'IMMOBILE
            } else if (strpos(strtoupper($prodag_rec['DAGVAL']), "RAPPR") !== false) {
                $qualifica = "4"; //RAPPRESENTANTE LEGALE
            }
        }
        return $qualifica;
    }

    function GetTipoRichiesta($gespro) {
        switch ($gespro) {
            case "000841":
            case "000842":
            case "000843":
            case "000844":
            case "600032":
            case "000610":
            case "000611":
            case "000622":
                $tipo = "1";
                break;
            case "000853":
            case "600036":
            case "600040":
            case "000620":
                $tipo = "0";
                break;
            default:
                $tipo = "1";
                break;
        }
        return $tipo;
    }

    function GetTipologiaRichiesta($gespro) {
        switch ($gespro) {
            case "600030":
                $tipo = "1";
                break;
            case "000610":
            case "000611":
            case "000622":
            case "000853":
            case "600036":
            case "600038":
            case "610005":
                $tipo = "0";
                break;
            default:
                $tipo = "0";
                break;
        }
        return $tipo;
    }

    function GetIndirizzoRichiesta($gesnum) {
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND DAGKEY = 'INTER_VIA' AND DAGVAL <> ''", false);
        if (!$prodag_rec) {
            $anades_recDich = $this->praLib->GetAnades($gesnum, "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD']);
            if ($anades_recDich) {
                $indirizzo = $anades_recDich['DESIND'];
            }
        } else {
            $indirizzo = $prodag_rec['DAGVAL'];
        }
        return utf8_decode($indirizzo);
    }

    function GetCivicoRichiesta($gesnum) {
        $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '$gesnum' AND DAGKEY = 'INTER_CIV' AND DAGVAL <> ''", false);
        if (!$prodag_rec) {
            $anades_recDich = $this->praLib->GetAnades($gesnum, "ruolo", false, praRuolo::$SISTEM_SUBJECT_ROLES['DICHIARANTE']['RUOCOD']);
            if ($anades_recDich) {
                $civico = $anades_recDich['DESCIV'];
            }
        } else {
            $civico = $prodag_rec['DAGVAL'];
        }
        return $civico;
    }

    function GetPartitaIvaImpresa($anades_rec) {
        $piva = $anades_rec['DESPIVA'];
        if ($piva == "" || strlen($piva) != 11) {
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $anades_rec['DESNUM'] . "' AND DAGKEY = 'IMPRESAESEC_PARTITA_PIVA' AND DAGVAL <> ''", false);
            if (strlen($prodag_rec['DAGVAL']) == 11) {
                $piva = $prodag_rec['DAGVAL'];
            }
        }
        return $piva;
    }

    function GetCognomeDichiarante($anades_rec) {
        $cogn = $anades_rec['DESCOGNOME'];
        if ($cogn == "") {
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $anades_rec['DESNUM'] . "' AND DAGKEY = 'DICHIARANTE_COGNOME" . "' AND DAGVAL <> ''", false);
            if ($prodag_rec) {
                $cogn = $prodag_rec['DAGVAL'];
            }
            if ($cogn == "") {
                $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $anades_rec['DESNUM'] . "' AND DAGKEY = 'DEFAULT_DICHIARANTE_COGNOME" . "' AND DAGVAL <> ''", false);
                if ($prodag_rec['DAGVAL']) {
                    $cogn = $prodag_rec['DAGVAL'];
                }
            }
        }
        $cogn = trim($cogn);
        return str_pad($cogn, 26, " ", STR_PAD_RIGHT);
    }

    function GetNomeDichiarante($anades_rec) {
        $nome = $anades_rec['DESNOME'];
        if ($nome == "") {
            $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $anades_rec['DESNUM'] . "' AND DAGKEY = 'DICHIARANTE_NOME" . "' AND DAGVAL <> ''", false);
            if ($prodag_rec) {
                $nome = $prodag_rec['DAGVAL'];
            }
            if ($nome == "") {
                $prodag_rec = ItaDB::DBSQLSelect($this->PRAM_DB, "SELECT * FROM PRODAG WHERE DAGNUM = '" . $anades_rec['DESNUM'] . "' AND DAGKEY = 'DEFAULT_DICHIARANTE_NOME" . "' AND DAGVAL <> ''", false);
                if ($prodag_rec['DAGVAL']) {
                    $nome = $prodag_rec['DAGVAL'];
                }
            }
        }
        $nome = trim($nome);
        return str_pad($nome, 25, " ", STR_PAD_RIGHT);
    }

    function GetProtocollo($Proges_rec) {
        $prot = $Proges_rec['GESNPR'];
        if ($prot == 0) {
            $prot = $Proges_rec['GESPRA'];
            if ($prot == "") {
                $prot = $Proges_rec['GESNUM'];
            }
        }
        return str_pad($prot, 20, " ", STR_PAD_RIGHT);
    }

    function GetDatiCatastali($gesnum) {
        $sql = "SELECT 
                    PRAIMM.*,
                    (SELECT FOGLIO FROM PRAIMM WHERE PRONUM = '$gesnum' AND FOGLIO <> '' LIMIT 1) AS FOGLIOPRINC,
                    (SELECT PARTICELLA FROM PRAIMM WHERE PRONUM = '$gesnum' AND FOGLIO <> '' LIMIT 1) AS PARTICELLAPRINC
            FROM
                    PRAIMM
                    WHERE
            PRONUM = '$gesnum' AND 
            FOGLIO = '' AND PARTICELLA = ''";
        return ItaDB::DBSQLSelect($this->PRAM_DB, $sql, false);
    }

}
