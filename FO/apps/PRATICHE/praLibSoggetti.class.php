<?php

require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praRuolo.class.php';

class praLibSoggetti {

    public function getSoggettiFromAnades($PRAM_DB, $codiceFiscale = null) {
        if (is_null($codiceFiscale)) {
            $codiceFiscale = frontOfficeApp::$cmsHost->getCodFisFromUtente();
        }

        $sql = "SELECT
                ANADES.*
            FROM ANADES
            LEFT OUTER JOIN PROGES ON PROGES.GESNUM = ANADES.DESNUM
            LEFT OUTER JOIN PRORIC ON PRORIC.RICNUM = PROGES.GESPRA
            WHERE
                PROGES.GESNUM IS NOT NULL AND PRORIC.RICSTA = '01' AND
                PRORIC.RICFIS = '$codiceFiscale' AND ANADES.DESFIS <> '' AND
                ANADES.DESNASCIT != ''
            ORDER BY PRORIC.RICDAT DESC, PRORIC.RICDAT DESC";

        $anades_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql, true);

        return $anades_tab;
    }

    /**
     * Ritorna tutti i soggetti inseriti nella richiesta.
     * L'array di ritorno è suddiviso per codice ruolo dei soggetti.
     *
     * Es.
     * Array(
     *   '000001' => Array(
     *     Array( 'NOME' => '', 'COGNOME' => '', ... )
     *   ),
     *   '000002' => Array(
     *     Array( 'NOME' => '', 'COGNOME' => '', ... ),
     *     Array( 'NOME' => '', 'COGNOME' => '', ... )
     *   )
     * )
     * 
     * @param type $PRAM_DB
     * @param type $proric_rec Record della richiesta.
     * @param type $ricite_tab Record dei passi.
     * @return type
     */
    public function getSoggettiRichiesta($PRAM_DB, $proric_rec, $ricite_tab) {
        $soggettiRuolo = $this->getSoggettiRichiestaBase($PRAM_DB, $proric_rec);

        foreach ($ricite_tab as $ricite_rec) {
            $soggettiPasso = $this->getSoggettiPasso($PRAM_DB, $ricite_rec);
            foreach ($soggettiPasso as $RUOCOD => $soggetti) {
                if (!isset($soggettiRuolo[$RUOCOD])) {
                    $soggettiRuolo[$RUOCOD] = array();
                }

                $soggettiRuolo[$RUOCOD] = array_merge($soggettiRuolo[$RUOCOD], $soggetti);
            }
        }

        return $soggettiRuolo;
    }

    /**
     * Ritorna i soggetti inseriti nella richiesta (non legati ad un passo).
     * Per il formato di ritorno, vedere il metodo 'getSoggettiRichiesta'.
     * 
     * @param type $PRAM_DB
     * @param type $proric_rec Record della richiesta.
     * @return type
     */
    public function getSoggettiRichiestaBase($PRAM_DB, $proric_rec) {
        $ruoli = array();

        foreach (praRuolo::$SISTEM_SUBJECT_ROLES as $prefix => $role) {
            $codiceRuolo = $role['RUOCOD'];
            $soggettiRuolo = $this->getSoggettiPassoConPrefisso($PRAM_DB, array(
                'RICNUM' => $proric_rec['RICNUM'],
                'ITEKEY' => $proric_rec['RICPRO']
                ), $prefix);

            if (!count($soggettiRuolo)) {
                continue;
            }

            if (!isset($ruoli[$codiceRuolo])) {
                $ruoli[$codiceRuolo] = array();
            }

            $ruoli[$codiceRuolo] = array_merge($ruoli[$codiceRuolo], $soggettiRuolo);
        }

        return $ruoli;
    }

    /**
     * Ritorna tutti i soggetti presenti in un passo.
     * Per il formato di ritorno, vedere il metodo 'getSoggettiRichiesta'.
     * 
     * @param type $PRAM_DB
     * @param type $ricite_rec Record del passo.
     * @return array
     */
    public function getSoggettiPasso($PRAM_DB, $ricite_rec) {
        $ruoli = array();

        if ($ricite_rec['ITWDOW'] == '1') {
            return $ruoli;
        }

        if ($ricite_rec['ITEUPL'] != '1' && $ricite_rec['ITEMLT'] != '1' && $ricite_rec['ITEDAT'] != '1') {
            return $ruoli;
        }

        $praLib = new praLib;
        $praclt_rec = $praLib->GetPraclt($ricite_rec['ITECLT'], 'codice', $PRAM_DB);

        if ($praclt_rec['CLTOPEFO'] == praLibStandardExit::FUN_FO_ANA_SOGGETTO) {
            $CLTMETA = unserialize($praclt_rec['CLTMETA']);
            $METAOPEFO = $CLTMETA['METAOPEFO'];
            $prefix = $METAOPEFO['PREFISSO_CAMPI'];
            $aliasSoggettoMeta = array_flip($METAOPEFO);

            /*
             * Se non è impostato un codice ruolo
             * verifico se il prefisso è standard.
             */

            if (!$METAOPEFO['CAMPO_RUOLO'] && isset(praRuolo::$SISTEM_SUBJECT_ROLES[$prefix])) {
                $codiceRuolo = praRuolo::$SISTEM_SUBJECT_ROLES[$prefix]['RUOCOD'];
                $ruoli[$codiceRuolo] = $this->getSoggettiPassoConPrefisso($PRAM_DB, $ricite_rec, $prefix, false, $aliasSoggettoMeta);
                return $ruoli;
            }

            /*
             * Cerco il campo RUOLO per ogni DAGSET
             */

            $sql = "SELECT DAGVAL, DAGSET, RICDAT FROM RICDAG
                    WHERE
                        DAGNUM = '{$ricite_rec['RICNUM']}' AND
                        ITEKEY = '{$ricite_rec['ITEKEY']}' AND 
                        DAGKEY = '{$METAOPEFO['CAMPO_RUOLO']}'";

            $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql);
            foreach ($ricdag_tab as $ricdag_rec) {
                $codiceRuolo = $ricdag_rec['RICDAT'] ?: $ricdag_rec['DAGVAL'];
                if (!$codiceRuolo) {
                    continue;
                }

                if (!isset($ruoli[$codiceRuolo])) {
                    $ruoli[$codiceRuolo] = array();
                }

                $ruoli[$codiceRuolo] = array_merge($ruoli[$codiceRuolo], $this->getSoggettiPassoConPrefisso($PRAM_DB, $ricite_rec, $prefix, $ricdag_rec['DAGSET'], $aliasSoggettoMeta));
            }

            return $ruoli;
        }

        foreach (praRuolo::$SISTEM_SUBJECT_ROLES as $prefix => $role) {
            $codiceRuolo = $role['RUOCOD'];
            $soggettiRuolo = $this->getSoggettiPassoConPrefisso($PRAM_DB, $ricite_rec, $prefix);

            if (!count($soggettiRuolo)) {
                continue;
            }

            if (!isset($ruoli[$codiceRuolo])) {
                $ruoli[$codiceRuolo] = array();
            }

            $ruoli[$codiceRuolo] = array_merge($ruoli[$codiceRuolo], $soggettiRuolo);
        }

        return $ruoli;
    }

    /**
     * Ritorna i soggetti con un dato prefisso per il passo specificato.
     * E' possibile utilizzare una mappatura dei campi utilizzati come ALIAS.
     * Es. Array( 'CODICE_FISCALE' => 'ALIAS_CODICEFISCALE_CFI' )
     * il campo PREFIX_CODICE_FISCALE sarà impostato come PREFIX_CODICEFISCALE_CFI.
     * 
     * @param type $PRAM_DB
     * @param type $ricite_rec Record del passo da elaborare.
     * @param type $prefix Prefisso dei campi del soggetto.
     * @param type $dagset Filtro per DAGSET.
     * @param type $alias Mappatura campi ALIAS.
     * @return type
     */
    public function getSoggettiPassoConPrefisso($PRAM_DB, $ricite_rec, $prefix, $dagset = false, $alias = array()) {
        $soggetti = array();

        $sql = "SELECT
                    RICDAG.*
                FROM RICDAG
                WHERE
                    DAGNUM = '{$ricite_rec['RICNUM']}' AND
                    ITEKEY = '{$ricite_rec['ITEKEY']}' AND 
                    RICDAG.DAGKEY LIKE '$prefix\_%'";

        if ($dagset) {
            $sql .= " AND DAGSET = '$dagset'";
        }

        $ricdag_tab = ItaDB::DBSQLSelect($PRAM_DB, $sql);

        foreach ($ricdag_tab as $ricdag_rec) {
            $uniqid = $ricdag_rec['ITEKEY'] . $ricdag_rec['DAGSET'];
            list(, $fieldname) = explode('_', $ricdag_rec['DAGKEY'], 2);

            if (isset($alias[$fieldname]) && substr($alias[$fieldname], 0, 6) === 'ALIAS_') {
                list(, $fieldname) = explode('_', $alias[$fieldname], 2);
            }

            if (!isset($soggetti[$uniqid])) {
                $soggetti[$uniqid] = array();
            }

            $soggetti[$uniqid][$fieldname] = $ricdag_rec['RICDAT'];
        }

        return array_values($soggetti);
    }

}
