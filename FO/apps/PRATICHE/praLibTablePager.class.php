<?php

class praLibTablePager {

    private $praLib;
    private $errCode;
    private $errMessage;

    public function __construct($praLib) {
        $this->praLib = $praLib;
    }

    public function getErrCode() {
        return $this->errCode;
    }

    public function getErrMessage() {
        return $this->errMessage;
    }

    public function parseRequest($request) {
        $countResult = 0;
        $elencoTableBody = array();

        switch ($request['id']) {
            case 'ricComuni':
                if (!$this->ricComuni($request, $elencoTableBody, $countResult)) {
                    return false;
                }
                break;

            case 'ricCatasto':
                if (!$this->ricCatasto($request, $elencoTableBody, $countResult)) {
                    return false;
                }
                break;
        }

        $data = array($countResult, $elencoTableBody);
        foreach ($data[1] as &$record) {
            foreach ($record as &$value) {
                $value = utf8_encode($value);
            }
        }

        output::$ajax_out = $data;
        output::ajaxSendResponse();

        return true;
    }

    private function ricComuni($request, &$data, &$count) {
        $sql = "SELECT COMUNE, PROVIN, COAVPO, CISTAT FROM COMUNI WHERE 1 = 1";

        if ($request['tablefilter_0']) {
            $sql .= " AND UCASE(COMUNE) LIKE UCASE('%" . addslashes($request['tablefilter_0']) . "%')";
        }

        if ($request['tablefilter_1']) {
            $sql .= " AND UCASE(PROVIN) LIKE UCASE('%" . addslashes($request['tablefilter_1']) . "%')";
        }

        if ($request['tablefilter_2']) {
            $sql .= " AND COAVPO LIKE '%" . addslashes($request['tablefilter_2']) . "%'";
        }

        if ($request['tablefilter_3']) {
            $sql .= " AND CISTAT LIKE '%{" . addslashes($request['tablefilter_3']) . "%'";
        }

        if ($request['filterRegione']) {
            $sql .= " AND REGIONE = '" . addslashes($request['filterRegione']) . "'";
        }

        if ($request['filterProvincia']) {
            $sql .= " AND PROVIN = '" . addslashes($request['filterProvincia']) . "'";
        }

        if ($request['filterEscludi']) {
            $listaISTAT = array_map('trim', explode(',', $request['filterEscludi']));
            $sql .= " AND CISTAT NOT IN ('" . implode("', '", $listaISTAT) . "')";
        }

        if (isset($request['column'][0])) {
            $sql .= " ORDER BY COMUNE " . ($request['column'][0] == '0' ? 'ASC' : 'DESC');
        } elseif (isset($request['column'][1])) {
            $sql .= " ORDER BY PROVIN " . ($request['column'][1] == '0' ? 'ASC' : 'DESC');
        } elseif (isset($request['column'][2])) {
            $sql .= " ORDER BY COAVPO " . ($request['column'][2] == '0' ? 'ASC' : 'DESC');
        } elseif (isset($request['column'][3])) {
            $sql .= " ORDER BY CISTAT " . ($request['column'][3] == '0' ? 'ASC' : 'DESC');
        }

        try {
            $COMUNI_DB = ItaDB::DBOpen('COMUNI', '');
            $comuni_tab = ItaDB::DBSQLSelect($COMUNI_DB, $sql, true);
        } catch (Exception $e) {
            $this->errCode = -1;
            $this->errMessage = 'Errore ' . __CLASS__ . ' ricComuni: ' . $e->getMessage();
            return false;
        }

        foreach (array_slice($comuni_tab, $request['page'] * $request['size'], $request['size']) as $comuni_rec) {
            $data[] = array(
                $comuni_rec['COMUNE'],
                $comuni_rec['PROVIN'],
                $comuni_rec['COAVPO'],
                $comuni_rec['CISTAT']
            );
        }

        $count = count($comuni_tab);

        return true;
    }

    private function ricCatasto($request, &$data, &$count) {
        $CATA_DB = $this->praLib->checkExistCatasto();
        if (!$CATA_DB) {
            return false;
        }

        $sql = "SELECT TIPOIMMOBILE, FOGLIO, NUMERO, SUB FROM LEGAME WHERE 1 = 1";

        if ($request['tablefilter_0']) {
            $sql .= " AND UCASE(TIPOIMMOBILE) LIKE UCASE('%" . addslashes($request['tablefilter_0']) . "%')";
        }

        if ($request['tablefilter_1']) {
            $sql .= " AND FOGLIO LIKE '%" . addslashes($request['tablefilter_1']) . "%'";
        }

        if ($request['tablefilter_2']) {
            $sql .= " AND NUMERO LIKE '%" . addslashes($request['tablefilter_2']) . "%'";
        }

        if ($request['tablefilter_3']) {
            $sql .= " AND SUB LIKE '%" . addslashes($request['tablefilter_3']) . "%'";
        }

        if (isset($request['column'][0])) {
            $sql .= " ORDER BY TIPOIMMOBILE " . ($request['column'][0] == '0' ? 'ASC' : 'DESC');
        } elseif (isset($request['column'][1])) {
            $sql .= " ORDER BY FOGLIO " . ($request['column'][1] == '0' ? 'ASC' : 'DESC');
        } elseif (isset($request['column'][2])) {
            $sql .= " ORDER BY NUMERO " . ($request['column'][2] == '0' ? 'ASC' : 'DESC');
        } elseif (isset($request['column'][3])) {
            $sql .= " ORDER BY SUB " . ($request['column'][3] == '0' ? 'ASC' : 'DESC');
        }

        try {
            $legame_tab = ItaDB::DBSQLSelect($CATA_DB, $sql, true);
        } catch (Exception $e) {
            $this->errCode = -1;
            $this->errMessage = 'Errore ' . __CLASS__ . ' ricCatasto: ' . $e->getMessage();
            return false;
        }

        foreach (array_slice($legame_tab, $request['page'] * $request['size'], $request['size']) as $legame_rec) {
            $data[] = array(
                $legame_rec['TIPOIMMOBILE'],
                $legame_rec['FOGLIO'],
                $legame_rec['NUMERO'],
                $legame_rec['SUB']
            );
        }

        $count = count($legame_tab);

        return true;
    }

}
