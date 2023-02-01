<?php

/**
 *
 *
 * PHP Version 5
 *
 * @category   
 * @package    Protocollo
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2015 Italsoft snc
 * @license
 * @version    06.07.2016
 * @link
 * @see
 * @since
 * @deprecated
 * */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proSoggetto.class.php';

class proLibTitolario {

    public $PROT_DB;
    private $errCode;
    private $errMessage;
    public $proLib;

    function __construct() {
        $this->proLib = new proLib();
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function setErrCode($errCode) {
        $this->errCode = $errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function setErrMessage($errMessage) {
        $this->errMessage = $errMessage;
    }

    public function setPROTDB($PROT_DB) {
        $this->PROT_DB = $PROT_DB;
    }

    public function getPROTDB() {
        if (!$this->PROT_DB) {
            try {
                $this->PROT_DB = ItaDB::DBOpen('PROT');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->PROT_DB;
    }

    public function GetVersioni($soloValide = true) {
        $sql = "SELECT * 
                    FROM AACVERS
                    WHERE 1 = 1 ";
        if ($soloValide) {
            $sql.=" AND FLAG_DIS <> 1 ";
        }
        // where AND DATAFINE = ''
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
    }

    public function CheckVersioneUnica($soloValide = true) {
        $sql = "SELECT COUNT(ROWID)AS TOT_V FROM AACVERS WHERE 1 = 1 ";
        if ($soloValide) {
            $sql.=" AND FLAG_DIS <> 1 ";
        }
        $Versioni = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        if ($Versioni['TOT_V'] > 1) {
            return false;
        }
        return true;
    }

    public function GetVersione($codice, $tipo = 'codice') {
        switch ($tipo) {
            case 'codice':
                $sql = "SELECT * FROM AACVERS WHERE VERSIONE_T = $codice ";
                break;

            default:
                $sql = "SELECT * FROM AACVERS WHERE ROWID = $codice ";
                break;
        }

        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function GetTitpr($codice, $tipo = 'codice') {
        $sql = "SELECT * FROM ATD_TITOPR WHERE PROG_TITP = $codice ";
        return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
    }

    public function GetSqlLivello($Versione, $Prog_titpp, $SoloValidi = false) {
        $sql = "SELECT * FROM ATD_TITOPR 
                            WHERE VERSIONE_T = $Versione AND 
                            PROG_TITPP = $Prog_titpp ";
        if ($SoloValidi) {
            $sql.=" AND DATACESS = '' ";
        }
        $sql.=" ORDER BY TITP_CATEG,TITP_CLASS,TITP_FASCI,TITP_SUBFA ASC ";
// Prevedere la data di validita ?
        return $sql;
    }

    public function GetSqlLivelloFromNodo($Versione, $Prog_titpp) {
        $Padre_rec = $this->GetTitpr($Prog_titpp);
        $sql = "SELECT * FROM ATD_TITOPR 
                    WHERE VERSIONE_T = $Versione AND PROG_TITP <> $Prog_titpp  ";
        switch ($Padre_rec['NODO']) {
            case '1':
                $sql.=" AND TITP_CATEG = {$Padre_rec['TITP_CATEG']} ";
                $sql.=" AND TITP_FASCI = 0 ";
                $sql.=" AND TITP_SUBFA = 0 ";
                break;
            case '2':
                $sql.=" AND TITP_CATEG = {$Padre_rec['TITP_CATEG']} ";
                $sql.=" AND TITP_CLASS = {$Padre_rec['TITP_CLASS']} ";
                $sql.=" AND TITP_SUBFA = 0 ";
                break;
            case '3':
                $sql.=" AND TITP_CATEG = {$Padre_rec['TITP_CATEG']} ";
                $sql.=" AND TITP_CLASS = {$Padre_rec['TITP_CLASS']} ";
                $sql.=" AND TITP_FASCI = {$Padre_rec['TITP_FASCI']} ";
                break;
            case '0':
            default:
                $sql.="AND PROG_TITPP = $Prog_titpp AND NODO = 1 ";
                break;
        }
// Prevedere la data di validita ?
        $sql.= " ORDER BY TITP_CATEG,TITP_CLASS,TITP_FASCI,TITP_SUBFA ASC ";
        return $sql;
    }

    public function CopiaTitolario($VersioneScelta, $VersioneNuova, $SoloValidi = true) {
        /* Copio le Categorie */
        $sql = "SELECT * FROM ANACAT WHERE VERSIONE_T = $VersioneScelta ";
        if ($SoloValidi) {
            $sql.=" AND CATDAT = '' ";
        }
        $Anacat_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        foreach ($Anacat_tab as $Anacat_rec) {
            $NewAnacat_rec = $Anacat_rec;
            unset($NewAnacat_rec['ROWID']);
            unset($NewAnacat_rec['CATCOD_SUCC']);
            unset($NewAnacat_rec['VERSIONE_SUCC']);
            // Se prende quelle scadute le reinserisce con data vuota?
            $NewAnacat_rec['VERSIONE_T'] = $VersioneNuova;
            try {
                ItaDB::DBInsert($this->getPROTDB(), 'ANACAT', 'ROWID', $NewAnacat_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in duplicazione categoria.<br> " . $e->getMessage());
                return false;
            }
        }
        /* Copio le Classi */
        $sql = "SELECT * FROM ANACLA WHERE VERSIONE_T = $VersioneScelta ";
        if ($SoloValidi) {
            $sql.=" AND CLADAT = '' ";
        }
        $Anacla_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        foreach ($Anacla_tab as $Anacla_rec) {
            $NewAnacla_rec = $Anacla_rec;
            unset($NewAnacla_rec['ROWID']);
            unset($NewAnacla_rec['CLACCA_SUCC']);
            unset($NewAnacla_rec['VERSIONE_SUCC']);
            // Se prende quelle scadute le reinserisce con data vuota?
            $NewAnacla_rec['VERSIONE_T'] = $VersioneNuova;
            try {
                ItaDB::DBInsert($this->getPROTDB(), 'ANACLA', 'ROWID', $NewAnacla_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in duplicazione categoria.<br> " . $e->getMessage());
                return false;
            }
        }

        /* Copio le SottoClassi */
        $sql = "SELECT * FROM ANAFAS WHERE VERSIONE_T = $VersioneScelta ";
        if ($SoloValidi) {
            $sql.=" AND FASDAT = '' ";
        }
        $Anafas_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        foreach ($Anafas_tab as $Anafas_rec) {
            $NewAnafas_rec = $Anafas_rec;
            unset($NewAnafas_rec['ROWID']);
            unset($NewAnafas_rec['FASCCF_SUCC']);
            unset($NewAnafas_rec['VERSIONE_SUCC']);
            // Se prende quelle scadute le reinserisce con data vuota?
            $NewAnafas_rec['VERSIONE_T'] = $VersioneNuova;
            try {
                ItaDB::DBInsert($this->getPROTDB(), 'ANAFAS', 'ROWID', $NewAnafas_rec);
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in duplicazione categoria.<br> " . $e->getMessage());
                return false;
            }
        }


        return true;
    }

    /**
     * La funzione controlla se l'utente ha un titolario bloccato
     * Return:
     * true->  Ha un blocco del titolario
     * false-> Non ha alcun blocco del titolario.
     * 
     * NOTA.
     * Se "tipoProt" vuoto, e l'utente ha un tipo 
     * di blocco del titolario ritorna comunque true.
     * 
     * @param type $tipoProt
     * @return boolean
     */
    public function CheckUtenteBloccoTitolario($tipoProt = '') {
        $profilo = proSoggetto::getProfileFromIdUtente();
        if ($profilo['BLOC_TITOLARIO']) {
            if (!$tipoProt) {
                return true;
            }
            if (($profilo['BLOC_TITOLARIO'] == '1') || ($profilo['BLOC_TITOLARIO'] == '2' && $tipoProt == 'A') || ($profilo['BLOC_TITOLARIO'] == '3' && ($tipoProt == 'P' || $tipoProt == 'C'))) {
                return true;
            }
        }
        return false;
    }

    public function ControllaTitolarioProtocollo($ufficio = '', $tipoProt = '', $vesione_t, $codice1 = '', $codice2 = '', $codice3 = '') {
        if (!$ufficio) {
            $this->setErrCode(-1);
            $this->setErrMessage("Verifica su titolario impossibile. Ufficio non valorizzato.");
            return false;
        }
        if (!$codice1) {
            $this->setErrCode(-1);
            $this->setErrMessage("Verifica su titolario impossibile. Codice non valorizzato.");
            return false;
        }
        if (!$tipoProt) {
            $this->setErrCode(-1);
            $this->setErrMessage("Verifica su titolario impossibile. Tipologia protocollo non valorizzata.");
            return false;
        }
        /* Allineo i codici se serve.. */
        $codice1 = str_pad($codice1, 4, "0", STR_PAD_LEFT);
        if ($codice2) {
            $codice2 = str_pad($codice2, 4, "0", STR_PAD_LEFT);
        }
        if ($codice3) {
            $codice3 = str_pad($codice3, 4, "0", STR_PAD_LEFT);
        }

        if ($this->CheckUtenteBloccoTitolario($tipoProt)) {
            $titolario_tab = $this->CtrTitolarioFiltrato($ufficio, $codice1, $codice2, $codice3);
            if (!$titolario_tab) {
                $this->setErrCode(-1);
                $this->setErrMessage("Titolario non abilitato. ");
                return false;
            }
        }
        /*
         * effettuo il controllo dopo aver verificato che il titolaro è permesso
         * precedentemente se il titolario era permesso non faceva il controllo
         */
        if (!$this->proLib->GetAnacat($vesione_t, $codice1, 'codice')) {
            $this->setErrCode(-1);
            $this->setErrMessage("Categoria del titolario non valida. ");
            return false;
        }
        if ($codice2) {
            if (!$this->proLib->GetAnacla($vesione_t, $codice1 . $codice2, 'codice')) {
                $this->setErrCode(-1);
                $this->setErrMessage("Classificazione del titolario non valida. ");
                return false;
            }
        }
        if ($codice3) {
            if (!$this->proLib->GetAnafas($vesione_t, $codice1 . $codice2 . $codice3, 'fasccf')) {
                $this->setErrCode(-1);
                $this->setErrMessage("SottoClasse del titolario non valida. ");
                return false;
            }
        }
        return true;
    }

    private function CtrTitolarioFiltrato($uffcod, $catcod, $clacod = '', $fascod = '') {
        $sql = "SELECT * FROM UFFTIT WHERE UFFCOD='$uffcod' AND CATCOD='$catcod'";
        $sql.=" AND CLACOD='$clacod'";
        $sql.=" AND FASCOD='$fascod'";

        $ufftit_test = $this->proLib->getGenericTab($sql);
        if ($ufftit_test) {
            return $ufftit_test;
        }
        return false;
    }

    public function GetTreeTitolario($Versione, $filter = '', $expandedForce = '', $where = '', $soloValidi = false, $visLivello = 3) {
        $matrice = array();
        if (is_null($visLivello)) {
            $visLivello = 3;
        }

        $expanded = $filter ? 'true' : 'false';

        if ($where) {
            $expanded = 'true';
        }
        if ($expandedForce) {
            $expanded = $expandedForce;
        }

        $anaent_rec12 = $this->proLib->GetAnaent('12');
        $anaent_rec13 = $this->proLib->GetAnaent('13');

        $i = 1;
        $matrice = array();
        $parent = $i;

        $Versione_rec = $this->GetVersione($Versione);
        if (!$Versione_rec) {
            $this->setErrCode(-1);
            $this->setErrMessage("Versione $Versione non trovata.");
            return $matrice;
        }

        $sqlCat = "SELECT * FROM ANACAT WHERE VERSIONE_T = '" . $Versione_rec['VERSIONE_T'] . "'";
        if ($soloValidi == true) {
            $sqlCat.=" AND CATDAT = '' ";
        }
        if ($where['ANACAT']) {
            $sqlCat = $sqlCat . $where['ANACAT'];
        }
        $sqlCat.= " ORDER BY CATCOD ASC ";

        /*
         * Nodo zero per versione.
         */
        $matrice[$parent] = array(
            'level' => 0,
            'parent' => null,
            'isLeaf' => 'false',
            'loaded' => 'true',
            'expanded' => 'true',
            'INDICE' => $parent,
            'DESCRIZIONE' => '<div style="background-color:rgba(255, 188, 0, 0.3);">' . $Versione_rec['DESCRI'] . '</div>',
            'CLASSIFICAZIONE' => '<div style="background-color:rgba(255, 188, 0, 0.3);">' . $Versione_rec['DESCRI'] . '</div>',
            'VERSIONE_T' => $Versione_rec['VERSIONE_T'],
            'VERSIONE' => $Versione_rec['DESCRI_B'],
            'CATCOD' => '',
            'CLACOD' => '',
            'FASCOD' => '',
            'DECOD_DESCR' => $Versione_rec['DESCRI_B'],
            'DATAFINE' => $Versione_rec['DATAFINE'],
            'TIPO_CHIAVE' => 'AACVER',
            'VALORE_CHIAVE' => $Versione_rec['ROWID'],
            'CHIAVE' => 'AACVER:' . $Versione_rec['ROWID']
        );
        $parent = ++$i;

        if ($visLivello >= 1) {
            $anacat_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sqlCat, true);
            foreach ($anacat_tab as $k => $categoria) {
                $Figlio = false;
                $parent = ++$i;
                if ($anaent_rec12['ENTDE4'] != '1') {
                    $parent = ++$i;
                    $sqlCla = "SELECT * FROM ANACLA WHERE VERSIONE_T = '" . $Versione_rec['VERSIONE_T'] . "' AND CLACAT='" . $categoria['CATCOD'] . "'";
                    if ($soloValidi == true) {
                        $sqlCla.=" AND CLADAT='' ";
                    }
                    if ($where['ANACLA']) {
                        $sqlCla = $sqlCla . $where['ANACLA'];
                    }

//                    /* Piu annullati per data */
//                    if ($categoria['CATDAT'] && !$soloValidi) {
//                        $sqlCla.=" AND CLADAT <>'' ";
//                    }
//                    if (!$categoria['CATDAT']) {
//                        $sqlCla.=" AND CLADAT ='' ";
//                    }
                    /* Piu annullati per data */

                    $sqlCla.= ' ORDER BY CLACOD ASC ';
                    $anacla_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sqlCla, true);
                    if ($anacla_tab && $visLivello >= 2) {
                        foreach ($anacla_tab as $classe) {
                            $Figlio2 = false;
                            $iPadre = $i++;
                            if ($anaent_rec13['ENTDE4'] != '1') {
                                $iPadre = ++$i;
                                $parent2 = ++$i;
                                $sqlFas = "SELECT * FROM ANAFAS WHERE VERSIONE_T = '" . $Versione_rec['VERSIONE_T'] . "' AND FASCCA='" . $classe['CLACCA'] . "'";
                                if ($soloValidi == true) {
                                    $sqlFas.=" AND FASDAT='' ";
                                }
                                if ($where['ANAFAS']) {
                                    $sqlFas = $sqlFas . $where['ANAFAS'];
                                }

//                                /* Piu annullati per data */
//                                if ($categoria['CATDAT'] && !$soloValidi) {
//                                    $sqlCla.=" AND FASDAT <>'' ";
//                                }
//                                if (!$categoria['CATDAT']) {
//                                    $sqlCla.=" AND FASDAT ='' ";
//                                }
                                /* Piu annullati per data */

                                // Ordine?
                                $sqlFas.= ' ORDER BY FASCOD ASC ';
                                $anafas_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sqlFas, true);
                                if ($anafas_tab && $visLivello >= 3) {
                                    foreach ($anafas_tab as $fascicolo) {
                                        /* Ricerco il fascicolo */
                                        $DescrizioneFascicolo = $fascicolo['FASCOD'] . " - " . $fascicolo['FASDES'];
                                        if ($filter && strpos(strtolower($DescrizioneFascicolo), strtolower($filter)) === false) {
                                            continue;
                                        }
                                        $Figlio2 = true;
                                        $matrice[++$i] = array(
                                            'level' => 3,
                                            'parent' => $parent2,
                                            'isLeaf' => 'true',
                                            'loaded' => 'true',
                                            'expanded' => $expanded,
                                            'INDICE' => $i,
                                            'CLASSIFICAZIONE' => $DescrizioneFascicolo,
                                            'DESCRIZIONE' => $DescrizioneFascicolo,
                                            'VERSIONE_T' => $Versione_rec['VERSIONE_T'],
                                            'VERSIONE' => $Versione_rec['DESCRI_B'],
                                            'CATCOD' => $categoria['CATCOD'],
                                            'CLACOD' => $classe['CLACOD'],
                                            'FASCOD' => $fascicolo['FASCOD'],
                                            'CATDES' => $categoria['CATDES'],
                                            'CLADES' => $classe['CLADE1'] . $classe['CLADE2'],
                                            'FASDES' => $fascicolo['FASDES'],
                                            'DECOD_DESCR' => $fascicolo['FASDES'],
                                            'DATAFINE' => $fascicolo['FASDAT'],
                                            'TIPO_CHIAVE' => 'ANAFAS',
                                            'VALORE_CHIAVE' => $fascicolo['ROWID'],
                                            'CHIAVE' => "ANAFAS:" . $fascicolo['ROWID']
                                        );
                                    }
                                }
                            }

                            /* Ricerco la classe */
                            $DescrizioneClasse = $classe['CLACOD'] . " - " . $classe['CLADE1'] . $classe['CLADE2'];
                            $DescrTrovataClasse = strpos(strtolower($DescrizioneClasse), strtolower($filter));
                            if ($filter && $DescrTrovataClasse === false && $Figlio2 == false) {
                                continue;
                            }
                            $Figlio = true;
                            if (!$filter || ($filter && $iPadre > $parent) || ($DescrTrovataClasse)) {
                                $matrice[++$iPadre] = array(
                                    'level' => 2,
                                    'parent' => $parent,
                                    'isLeaf' => $Figlio2 ? 'false' : 'true',
                                    'loaded' => 'true',
                                    'expanded' => $expanded,
                                    'INDICE' => $iPadre,
                                    'CLASSIFICAZIONE' => $DescrizioneClasse,
                                    'DESCRIZIONE' => $DescrizioneClasse,
                                    'VERSIONE_T' => $Versione_rec['VERSIONE_T'],
                                    'VERSIONE' => $Versione_rec['DESCRI_B'],
                                    'CATCOD' => $categoria['CATCOD'],
                                    'CLACOD' => $classe['CLACOD'],
                                    'FASCOD' => '',
                                    'CATDES' => $categoria['CATDES'],
                                    'CLADES' => $classe['CLADE1'] . $classe['CLADE2'],
                                    'FASDES' => '',
                                    'DECOD_DESCR' => $classe['CLADE1'] . $classe['CLADE2'],
                                    'DATAFINE' => $classe['CLADAT'],
                                    'TIPO_CHIAVE' => 'ANACLA',
                                    'VALORE_CHIAVE' => $classe['ROWID'],
                                    'CHIAVE' => 'ANACLA:' . $classe['ROWID']
                                );
                            }
                        }
                    }
                }

                $DescrizioneCategoria = $categoria['CATCOD'] . " - " . $categoria['CATDES'];
                $DescTrovata = strpos(strtolower($DescrizioneCategoria), strtolower($filter));
                if ($filter && $DescTrovata === false && $Figlio == false) {
                    continue;
                }
                if (!$filter || ($filter && $i > $parent) || ($DescTrovata)) {
                    $matrice[$parent] = array(
                        'level' => 1,
                        'parent' => null,
                        'isLeaf' => $Figlio ? 'false' : 'true',
                        'loaded' => 'true',
                        'expanded' => $expanded,
                        'INDICE' => $parent,
                        'CLASSIFICAZIONE' => $DescrizioneCategoria,
                        'DESCRIZIONE' => $DescrizioneCategoria,
                        'VERSIONE_T' => $Versione_rec['VERSIONE_T'],
                        'VERSIONE' => $Versione_rec['DESCRI_B'],
                        'CATCOD' => $categoria['CATCOD'],
                        'CLACOD' => '',
                        'FASCOD' => '',
                        'CATDES' => $categoria['CATDES'],
                        'CLADES' => '',
                        'FASDES' => '',
                        'DECOD_DESCR' => $categoria['CATDES'],
                        'DATAFINE' => $categoria['CATDAT'],
                        'TIPO_CHIAVE' => 'ANACAT',
                        'VALORE_CHIAVE' => $categoria['ROWID'],
                        'CHIAVE' => 'ANACAT:' . $categoria['ROWID']
                    );
                }
            }
        }
        /*
         * Sorto per chiave per visualizzare bene la griglia
         */
        ksort($matrice);
        return $matrice;
    }

    public function NuovoElementoTitolario($nameForm, $ChiavePadre = '', $TipoChiavePadre = '') {
        if (!$ChiavePadre || !$TipoChiavePadre) {
            $this->setErrCode(-1);
            $this->setErrMessage("Elementi del titolario mancanti: Chiave e Tipologia Chiave.");
            return false;
        }
        $model = '';
        $RowidVersione = '';
        $RowidPadre = '';
        switch ($TipoChiavePadre) {
            case 'AACVER':
                $model = 'proAnacat';
                $RowidVersione = $ChiavePadre;
                break;
            case 'ANACAT':
                $model = 'proAnacla';
                $Anacat_rec = $this->proLib->GetAnacat('', $ChiavePadre, 'rowid');
                $Versione_rec = $this->GetVersione($Anacat_rec['VERSIONE_T']);
                $RowidVersione = $Versione_rec['ROWID'];
                $RowidPadre = $ChiavePadre;
                break;

            case 'ANACLA':
                $model = 'proAnafas';
                $Anacla_rec = $this->proLib->GetAnacla('', $ChiavePadre, 'rowid');
                $Versione_rec = $this->GetVersione($Anacla_rec['VERSIONE_T']);
                $RowidVersione = $Versione_rec['ROWID'];
                $RowidPadre = $ChiavePadre;
                break;

            default:
                $this->setErrCode(-1);
                $this->setErrMessage("Livello del titolario non gestibile.");
                return false;
                break;
        }

        itaLib::openDialog($model);
        $formObj = itaModel::getInstance($model);
        $formObj->setReturnModel($nameForm);
        $formObj->setReturnEvent('returnAggiuntaTitolario');
        $formObj->setEvent('DaTitolario');
        $formObj->setRowidVersione_T($RowidVersione);
        if ($RowidPadre) {
            $formObj->setRowidPadre($RowidPadre);
        }
        $formObj->parseEvent();
        $formObj->Nuovo();

        return true;
    }

    public function DettaglioElementoTitolario($nameForm, $ChiaveElemento = '', $TipoChiaveElemento = '') {
        if (!$ChiaveElemento || !$TipoChiaveElemento) {
            $this->setErrCode(-1);
            $this->setErrMessage("Elementi del titolario mancanti: Chiave e Tipologia Chiave.");
            return false;
        }

        $model = '';
        $RowidVersione = '';
        switch ($TipoChiaveElemento) {
            case 'ANACAT':
                $model = 'proAnacat';
                $Anacat_rec = $this->proLib->GetAnacat('', $ChiaveElemento, 'rowid');
                $Versione_rec = $this->GetVersione($Anacat_rec['VERSIONE_T']);
                $RowidVersione = $Versione_rec['ROWID'];
                break;
            case 'ANACLA':
                $model = 'proAnacla';
                $Anacla_rec = $this->proLib->GetAnacla('', $ChiaveElemento, 'rowid');
                $Versione_rec = $this->GetVersione($Anacla_rec['VERSIONE_T']);
                $RowidVersione = $Versione_rec['ROWID'];
                break;

            case 'ANAFAS':
                $model = 'proAnafas';
                $Anafas_rec = $this->proLib->GetAnafas('', $ChiaveElemento, 'rowid');
                $Versione_rec = $this->GetVersione($Anafas_rec['VERSIONE_T']);
                $RowidVersione = $Versione_rec['ROWID'];
                break;

            default:
                return true;
                break;
        }

        itaLib::openDialog($model);
        $formObj = itaModel::getInstance($model);
        $formObj->setReturnModel($nameForm);
        $formObj->setReturnEvent('returnAggiuntaTitolario');
        $formObj->setEvent('DaTitolario');
        $formObj->setRowidDettaglio($ChiaveElemento);
        $formObj->setRowidVersione_T($RowidVersione);
        $formObj->parseEvent();
        $formObj->Dettaglio();

        return true;
    }

    public function CheckCreaVersioneZero() {
        $Versione_rec = $this->GetVersione('0', 'codice');
        if (!$Versione_rec) {
            $sql = "SELECT * FROM ANACAT WHERE VERSIONE_T = '0' ";
            $Anacat_rec = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
            if ($Anacat_rec) {
                /* LockTable serve ? */
                $AccVers_rec = array();
                $AccVers_rec['VERSIONE_T'] = '0';
                $AccVers_rec['DESCRI'] = 'VERSIONE ATTUALE';
                $AccVers_rec['DESCRI_B'] = 'ATTUALE';
                try {
                    ItaDB::DBInsert($this->getPROTDB(), 'AACVERS', 'ROWID', $AccVers_rec);
                } catch (Exception $e) {
                    $this->setErrCode(-1);
                    $this->setErrMessage("Errore in inserimento Versione Zero.<br> " . $e->getMessage());
                    return false;
                }
            }
        }
        return true;
    }

    public function GetTitolarioPrecedente($versione_t = '') {
        $titolarioPrecedente = '';
        if ($versione_t === '') {
            $versione_t = $this->proLib->GetTitolarioCorrente();
        }
        $Versione_rec = $this->GetVersione($versione_t, 'codice');
        $oggi = $Versione_rec['DATAINIZ'];
        $sql = "SELECT * FROM AACVERS WHERE DATAINIZ<='$oggi' AND DATAFINE='' AND FLAG_DIS=0 AND VERSIONE_T <> $versione_t ORDER BY DATAINIZ DESC, VERSIONE_T DESC";
        $aacvers_tab = ItaDB::DBSQLSelect($this->getPROTDB(), $sql, true);
        if ($aacvers_tab) {
            $titolarioPrecedente = $aacvers_tab[0]['VERSIONE_T'];
        }
        return $titolarioPrecedente;
    }

    /**
     * 
     * @param type $rowid
     * @param type $TitolarioSucc
     *  array(
     *      VERSIONE_T
     *      CATEGORIA => 4
     *      CLASSE => 8
     *      SOTTOCLASSE => 12
     *      )
     * @return boolean
     */
    public function AggiornaTitolarioSucc($rowid, $tipo = '', $TitolarioSucc = array()) {
        if (!$rowid || !$TitolarioSucc) {
            $this->setErrCode(-1);
            $this->setErrMessage("Indicare il titolario da aggiornare " . $e->getMessage());
            return false;
        }
        // Sottoclasse
        if ($tipo == 'SOTTOCLASSE') {
            $AnafasPrec_rec = $this->proLib->GetAnafas('', $rowid, 'rowid');
            $AnafasPrec_rec['FASCCF_SUCC'] = $TitolarioSucc['SOTTOCLASSE'];
            $AnafasPrec_rec['VERSIONE_SUCC'] = $TitolarioSucc['VERSIONE_T'];
            try {
                ItaDB::DBUpdate($this->getPROTDB(), 'ANAFAS', 'ROWID', $AnafasPrec_rec);
                return true;
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in Aggiornamento ANAFAS precedente.<br> " . $e->getMessage());
                return false;
            }
        }
        // Classe
        if ($tipo == 'CLASSE') {
            $AnaclaPrec_rec = $this->proLib->GetAnacla('', $rowid, 'rowid');
            $AnaclaPrec_rec['CLACCA_SUCC'] = $TitolarioSucc['CLASSE'];
            $AnaclaPrec_rec['VERSIONE_SUCC'] = $TitolarioSucc['VERSIONE_T'];
            try {
                ItaDB::DBUpdate($this->getPROTDB(), 'ANACLA', 'ROWID', $AnaclaPrec_rec);
                return true;
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in Aggiornamento ANACLA precedente.<br> " . $e->getMessage());
                return false;
            }
        }
        // Categoria
        if ($tipo == 'CATEGORIA') {
            $AnacatPrec_rec = $this->proLib->GetAnacat('', $rowid, 'rowid');
            $AnacatPrec_rec['CATCOD_SUCC'] = $TitolarioSucc['CATEGORIA'];
            $AnacatPrec_rec['VERSIONE_SUCC'] = $TitolarioSucc['VERSIONE_T'];
            try {
                ItaDB::DBUpdate($this->getPROTDB(), 'ANACAT', 'ROWID', $AnacatPrec_rec);
                return true;
            } catch (Exception $e) {
                $this->setErrCode(-1);
                $this->setErrMessage("Errore in Aggiornamento ANACAT precedente.<br> " . $e->getMessage());
                return false;
            }
        }

        return true;
    }

    /**
     * 
     * @param type $versione_t
     * @param type $categoria 4 Caratteri
     * @param type $classe 8 Caratteri
     * @param type $sottoclasse 12 Caratteri
     * @return boolean
     */
    public function GetCollegamentoTitolarioSuccessivo($versione_t = '', $categoria = '', $classe = '', $sottoclasse = '') {
        if ($versione_t === '') {
            return false;
        }
        if ($categoria) {
            $sql = "SELECT * FROM ANACAT WHERE VERSIONE_SUCC = $versione_t AND CATCOD_SUCC = '$categoria' ORDER BY CATCOD DESC ";
            return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        }
        if ($classe) {
            $sql = "SELECT * FROM ANACLA WHERE VERSIONE_SUCC = $versione_t AND CLACCA_SUCC = '$classe' ORDER BY CLACCA DESC ";
            return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        }
        if ($sottoclasse) {
            $sql = "SELECT * FROM ANAFAS WHERE VERSIONE_SUCC = $versione_t AND FASCCF_SUCC = '$sottoclasse' ORDER BY FASCCF DESC ";
            return ItaDB::DBSQLSelect($this->getPROTDB(), $sql, false);
        }
    }

    public function getTitolarioCorrispondenteSucc($codice, $versione_t, $versione_succ) {
        $Titolario = array();
        $Titolario['CATEGORIA'] = '';
        $Titolario['CLASSE'] = '';
        $Titolario['SOTTOCLASSE'] = '';
        $strLen = strlen($codice);
        switch ($strLen) {
            /* Controllo se è categoria */
            case '4':
                $Anacat_rec = $this->proLib->GetAnacat($versione_t, $codice, 'codice');
                if ($Anacat_rec['CATCOD_SUCC']) {
                    if ($Anacat_rec['VERSIONE_SUCC'] === $versione_succ) {
                        $AnacatSucc_rec = $this->proLib->GetAnacat($Anacat_rec['VERSIONE_SUCC'], $Anacat_rec['CATCOD_SUCC'], 'codice');
                        if ($AnacatSucc_rec) {
                            $Titolario['CATEGORIA'] = $AnacatSucc_rec['CATCOD'];
                            $Titolario['CLASSE'] = $AnacatSucc_rec['CATCOD'];
                            $Titolario['SOTTOCLASSE'] = $AnacatSucc_rec['CATCOD'];
                            $Titolario['DESCRIZIONE'] = $AnacatSucc_rec['CATDES'];
                        }
                    }
                }
                break;
            /* Controllo se è classe */
            case '8':
                $Anacla_rec = $this->proLib->GetAnacla($versione_t, $codice, 'codice');
                if ($Anacla_rec['CLACCA_SUCC']) {
                    if ($Anacla_rec['VERSIONE_SUCC'] === $versione_succ) {
                        $AnaclaSucc_rec = $this->proLib->GetAnacla($Anacla_rec['VERSIONE_SUCC'], $Anacla_rec['CLACCA_SUCC'], 'codice');
                        if ($AnaclaSucc_rec) {
                            $Titolario['CATEGORIA'] = $AnaclaSucc_rec['CLACAT'];
                            $Titolario['CLASSE'] = $AnaclaSucc_rec['CLACCA'];
                            $Titolario['SOTTOCLASSE'] = $AnaclaSucc_rec['CLACCA'];
                            $Titolario['DESCRIZIONE'] = $AnaclaSucc_rec['CLADE1'] . $AnaclaSucc_rec['CLADE2'];
                        }
                    }
                }
                break;
            /* Controllo se è sottoclasse */
            case '12':
                $Anafas_rec = $this->proLib->GetAnafas($versione_t, $codice, 'fasccf');
                if ($Anafas_rec['FASCCF_SUCC']) {
                    if ($Anafas_rec['VERSIONE_SUCC'] === $versione_succ) {
                        $AnafasSucc_rec = $this->proLib->GetAnafas($Anafas_rec['VERSIONE_SUCC'], $Anafas_rec['FASCCF_SUCC'], 'fasccf');
                        if ($AnafasSucc_rec) {
                            $Titolario['CATEGORIA'] = substr($AnafasSucc_rec['FASCCF'], 0, 4);
                            $Titolario['CLASSE'] = $AnafasSucc_rec['FASCCA'];
                            $Titolario['SOTTOCLASSE'] = $AnafasSucc_rec['FASCCF'];
                            $Titolario['DESCRIZIONE'] = $AnafasSucc_rec['FASDES'];
                        }
                    }
                }
                break;
            default:
                break;
        }
        return $Titolario;
    }

    public function DecodTitolario($codiceTitolario = '', $versione_t = '') {
        $Titolario = array();
        if (strlen($codiceTitolario) === 4) {
            $classificazione = intval($codiceTitolario);
        } else if (strlen($codiceTitolario) === 8) {
            $classificazione = intval(substr($codiceTitolario, 0, 4)) . '.' . intval(substr($codiceTitolario, 4, 4));
        } else if (strlen($codiceTitolario) === 12) {
            $classificazione = intval(substr($codiceTitolario, 0, 4)) . '.' . intval(substr($codiceTitolario, 4, 4)) . '.' . intval(substr($codiceTitolario, 8, 4));
        } else {
            $classificazione = $codiceTitolario;
        }
        $Titolario['classificazione'] = $classificazione;

//        $sql = "SELECT * FROM ANACAT WHERE CATCOD = '{$codiceTitolario}'";
        $anacat_rec = $this->proLib->GetAnacat($versione_t, $codiceTitolario, 'codice');
        if ($anacat_rec) {
            $Titolario['classificazione_Descrizione'] = $anacat_rec['CATDES'];
        }
//        $sql = "SELECT * FROM ANACLA WHERE CLACCA = '{$codiceTitolario}'";
        $anacla_rec = $this->proLib->GetAnacla($versione_t, $codiceTitolario, 'codice');
        if ($anacla_rec) {
            $Titolario['classificazione_Descrizione'] = $anacla_rec['CLADE1'] . " " . $anacla_rec['CLADE2'];
        }
//        $sql = "SELECT * FROM ANAFAS WHERE FASCCF = '{$codiceTitolario}'";
        $anafas_rec = $this->proLib->GetAnafas($versione_t, $codiceTitolario, 'fasccf');
        if ($anafas_rec) {
            $Titolario['classificazione_Descrizione'] = $anafas_rec['FASDES'];
        }
        return $Titolario;
    }

    public function CheckTitolario($titolario = '', $versione_t = '') {
        $categoria = $classe = $sottoclasse = $fascicolo = '';
        $separatore = '.';
        if ($separatore != '') {
            $titExp = explode($separatore, $titolario);
            $titElenco = array();
            foreach ($titExp as $value) {
                if ($value != '') {
                    $titElenco[] = $value;
                }
            }
            if ($titElenco[0]) {
                $categoria = str_pad($titElenco[0], 4, "0", STR_PAD_LEFT);
            }
            if ($titElenco[1]) {
                $classe = str_pad($titElenco[1], 4, "0", STR_PAD_LEFT);
            }
            if ($titElenco[2]) {
                $sottoclasse = str_pad($titElenco[2], 4, "0", STR_PAD_LEFT);
            }
        }

        // Titolario Obbligatorio, quindi categoria deve essere presente.
        if (!$categoria) {
            $this->setErrCode("Errore");
            $this->setErrMessage("Titolario non presente.");
            return false;
        }
        //1.1 Controllo Categoria valida.
        //   La lunghezza viene portata a 4 quando viene assegnata.
        $anacat_rec = $this->proLib->GetAnacat($versione_t, $categoria, 'codice');
        if (!$anacat_rec) {
            $this->setErrCode("Errore");
            $this->setErrMessage("Categoria del Titolario inesistente. Controllare il titolario inserito.");
            return false;
        }
        //1.2 Controllo Classe valida.
        //   La lunghezza viene portata a 4 quando viene assegnata.
        if ($classe) {
            $codice = $categoria . $classe;
            $anacla_rec = $this->proLib->GetAnacla($versione_t, $codice, 'codice');
            if (!$anacla_rec) {
                $this->setErrCode("Errore");
                $this->setErrMessage("Classe del Titolario inesistente. Controllare il titolario inserito.");
                return false;
            }
        }
        //1.3 Controllo SottoClasse valida.
        if ($sottoclasse) {
            $codice = $categoria . $classe . $sottoclasse;
            $anafas_rec = $this->proLib->GetAnafas($versione_t, $codice, 'fasccf');
            if (!$anafas_rec) {
                $this->setErrCode("Errore");
                $this->setErrMessage("Sottoclasse del Titolario inesistente. Controllare il titolario inserito.");
                return false;
            }
        }

        return $categoria . $classe . $sottoclasse;
    }

}