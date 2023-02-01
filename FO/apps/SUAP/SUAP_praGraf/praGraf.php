<?php

class praGraf extends itaModelFO {

    public $praLib;
    public $praErr;
    public $PRAM_DB;
    public $idTabella;
    public $idGrafico;
    public $lista_enti;
    public $tipo;
    public $sportello;
    public $caption;
    public $subcaption;
    public $anno;

    public function __construct() {
        parent::__construct();

        try {
            /*
             * Caricamento librerie.
             */
            $this->praErr = new suapErr();
            $this->praLib = new praLib($this->praErr);
        } catch (Exception $e) {
            
        }
    }

    public function parseEvent() {
        $this->tipo = $this->config['tipo_graph'];
        $this->idGrafico = $this->config['idgrafico'];
        $this->idTabella = $this->config['idtabella'];
        $this->lista_enti = explode('-', $this->config['lista_enti']);
        $this->sportello = $this->config['sportello'];
        $this->caption = $this->config['titolo'];
        $this->subcaption = $this->config['sottotitolo'];
        $this->anno = $this->config['anno'];
        output::$html_out = '';
        output::appendHtml($this->createGraph());
        return output::$html_out;
    }

    public function createGraph() {
        $html = '';
        $html .= '<div id="' . $this->idGrafico . '" style="width: 100%; margin-bottom: 2em;"></div>';
        switch ($this->tipo) {
            case 'proc_tot':
                $html .= $this->createTableProcTot();
                break;
            case 'proc_mese':
                $html .= $this->createTableProcMese();
                break;
            case 'proc_sport':
                $html .= $this->createTableProcSport();
                break;
            case 'proc_sett':
                $html .= $this->createTableProcSett();
                break;
            case 'proc_segn':
                $html .= $this->createTableProcSegn();
                break;
            default:
                break;
        }
        $html .= '<script type="text/javascript">insData("' . $this->tipo . '", "' . $this->idTabella . '");</script>';
        return $html;
    }

    public function createTableProcTot() {
        // Leggo i dati
        $nomi = array();
        $valori = array();
        $sql = "SELECT * FROM PRORIC WHERE (RICSTA = '01' OR RICSTA = '91') AND RICRPA='' AND SUBSTRING(RICDRE,1,4) = '" . $this->anno . "'";
        if ($this->sportello != '') {
            $sql .= " AND RICTSP = " . $this->sportello;
        }
        $sql_1 = "SELECT * FROM ANATSP WHERE TSPCOD = " . $this->sportello;
        foreach ($this->lista_enti as $ente) {
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ente);
            $Proric_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
            $valori[] = count($Proric_tab);
            $Proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_1, false);
            $nomi[] = $Proric_rec['TSPDES'] . ' - ' . $Proric_rec['TSPCOM'] . ' (' . count($Proric_tab) . ')';
        }

        // Controllo se è tutto zero
        $zero = true;
        $totale = 0;
        foreach ($valori as $valore) {
            if ($valore)
                $zero = false;
            $totale += $valore; // Faccio la somma totale
        }
        if ($zero) {
            return '<script type="text/javascript">$("#' . $this->idGrafico . '").html("Non ci sono richieste da mostrare.");</script>';
        }

        $valori = $this->valoriPercento($valori);

        // Crea la tabella
        $table = '<div style="display: none; height: 1px;">';
        $table .= '<table id="' . $this->idTabella . '" class="ita-jqGraph {caption:\'' . $this->caption . '\',subcaption:\'' . $this->subcaption . '\',container:\'' . $this->idGrafico . '\',type:\'pie\'}">';
        $table .= ' <thead>
                      <tr>';

        foreach ($nomi as $nome) {
            $table .= '<th>' . $nome . '</th>';
        }

        $table .= '   </tr>
                    </thead>
                    <tbody>
                      <tr>';

        foreach ($valori as $valore) {
            $table .= '<td>' . $valore . '</td>';
        }

        $table .= '   </tr>
                    </tbody>';
        $table .= '</table>';
        $table .= '</div>';
        
        $table .= '<br /><div style="width: 100%; text-align: center; font-weight: bold; font-size: 16px; margin-bottom: 20px;">' . $this->caption . ': ' . $totale . '</div>';
        return $table;
    }

    public function createTableProcMese() {
        // Leggo i dati
        $nomi = array();
        $valori = array();
        $sql = "SELECT * FROM PRORIC WHERE (RICSTA = '01' OR RICSTA='91') AND RICRPA='' AND SUBSTRING(RICDRE,1,4) = '" . $this->anno . "'";
        if ($this->sportello != '') {
            $sql .= " AND RICTSP = " . $this->sportello;
        }
        $totrec = array();
        foreach ($this->lista_enti as $ente) {
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ente);
            $Proric_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
            $totrec[] = $Proric_tab;
        }

        $tempval = array();
        foreach ($totrec as $rec) {
            $tempval = array_merge($tempval, $rec);
        }

        $valori = $this->initValoriMesi($valori);

        $totale = 0;
        for ($i = 0; $i < count($tempval); $i++) {
            $mese = substr($tempval[$i]['RICDRE'], 4, 2);
            if (substr($tempval[$i]['RICDRE'], 0, 4) == $this->anno) {
                $valori[$mese] ++;
                $totale++;
            }
        }

        // Crea la tabella
        $table = '<div style="display: none; height: 1px;">';
        $table .= '<table id="' . $this->idTabella . '" class="ita-jqGraph {caption:\'' . $this->caption . '\',subcaption:\'' . $this->subcaption . '\',container:\'' . $this->idGrafico . '\',type:\'column\'}">';
        $table .= ' <thead><tr>';

        $table .= '<th>Richieste</th>';

        $table .= ' </tr></thead>
                    <tbody>
                        ';

        foreach ($valori as $valore) {
            $table .= '<tr><td>' . $valore . '</td></tr>';
        }

        $table .= '     
                    </tbody>';
        $table .= '</table></div>';
        $table .= '<br /><div style="width:100%;text-align:center;font-weight:bold;font-size:16px;margin-bottom:20px;">' . $this->caption . ': ' . $totale . '</div>';
        return $table;
    }

    public function createTableProcSport() {
        $sqlPratiche = "SELECT 
                            ANATSP.TSPCOD, ANATSP.TSPDES
                        FROM
                            ANATSP";
        $lista_pratiche = array();
        foreach ($this->lista_enti as $ente) {
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ente);
            $Anatsp_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sqlPratiche);
            foreach ($Anatsp_tab as $Anatsp_rec) {
                $lista_pratiche[$Anatsp_rec['TSPCOD']] = $Anatsp_rec['TSPDES'];
            }
        }

        $sql = "SELECT
                    PRORIC.RICTSP,
                    PRORIC.RICRPA
                FROM
                    PRORIC
                WHERE
                    (PRORIC.RICSTA = '01' OR PRORIC.RICSTA='91') AND RICRPA='' AND SUBSTRING(RICDRE,1,4) = '" . $this->anno . "'";
        $valori = array();
        $htmp = "";
        $totale = 0;
        foreach ($this->lista_enti as $ente) {
            // Inizializzo pratiche per ente
            foreach ($lista_pratiche as $cod => $des) {
                $valori[$ente][$cod] = 0;
                $htmp .= $cod;
            }

            $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ente);
            $Proric_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
            foreach ($Proric_tab as $Proric_rec) {
                $valori[$ente][$Proric_rec['RICTSP']] ++;
            }
        }

        // Pulizia di valori vuoti
        foreach ($lista_pratiche as $codice => $pratica) {
            $presente = 0;
            foreach ($valori as $ente => $pratiche) {
                if ($pratiche[$codice] > 0) {
                    $presente = $codice;
                    break;
                }
            }
            if (!$presente) {
                // cancella da valori e da lista
                foreach ($valori as $ente => $pratiche) {
                    unset($valori[$ente][$codice]);
                    unset($lista_pratiche[$codice]);
                }
            }
        }
        // Fine pulizia valori vuoti
        // Crea la tabella
        $table = '<div style="display: none; height: 1px;">';
        $table .= '<table id="' . $this->idTabella . '" class="ita-jqGraph {caption:\'' . $this->caption . '\',subcaption:\'' . $this->subcaption . '\',container:\'' . $this->idGrafico . '\',type:\'column\'}">';
        $table .= ' <thead><tr>';

        foreach ($lista_pratiche as $codice => $pratica) {
            $table .= '<th>' . $pratica . '</th>';
        }

        $table .= ' </tr></thead>
                    <tbody>';

        foreach ($valori as $ente => $pratiche) {
            $table .= '<tr><th>' . $ente . '</th>';
            foreach ($lista_pratiche as $codice => $descrizione) {
                $table .= '<td>' . $pratiche[$codice] . '</td>';
                $totale += $pratiche[$codice];
            }
            $table .= '</tr>';
        }

        $table .= ' </tbody>';
        $table .= '</table></div>';

        //
        // SELECT per selezionare la base
        //
        $table .= '<select style="margin: 0 auto; display: block;" name="seleziona_sportello" id="seleziona_sportello" onChange="sceltaSportello(\'' .
            $this->idTabella . '\');">';
        $cnt = 0;
        foreach ($lista_pratiche as $cod => $pratica) {
            if ($cnt == 0) {
                $table .= '<option selected="selected" value="' . $pratica . '">' . $pratica . '</option>';
                $cnt++;
                continue;
            }
            $table .= '<option value="' . $pratica . '">' . $pratica . '</option>';
        }
        $table .= '</select>';
        //
        // Fine SELECT
        //
        $table .= '<br /><div style="width:100%;text-align:center;font-weight:bold;font-size:16px;margin-bottom:20px;">' . $this->caption . ': ' . $totale . '</div>';
        return $table;
    }

    public function createTableProcSett() {
        // Leggo i dati
        $valori = array();
        $sql = "SELECT
                    ANASET.SETDES,
                    PRORIC.RICRPA
                FROM
                    PRORIC
                LEFT OUTER JOIN ANASET ON PRORIC.RICSTT = ANASET.SETCOD
                WHERE
                    (PRORIC.RICSTA = '01' OR PRORIC.RICSTA='91') AND RICRPA='' AND SUBSTRING(RICDRE,1,4) = '" . $this->anno . "'";
        if ($this->sportello != '') {
            $sql .= " AND PRORIC.RICTSP = " . $this->sportello;
        }
        $sql .= " ORDER BY
                    RICSTT";
        $totrec = array();
        foreach ($this->lista_enti as $ente) {
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ente);
            $Proric_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
            $totrec[] = $Proric_tab;
        }

        // 'Spiano' l'array
        $tempval = array();
        foreach ($totrec as $rec) {
            $tempval = array_merge($tempval, $rec);
        }

        for ($i = 0; $i < count($tempval); $i++) {
            if ($i == 0) {
                $valori[0]['nome'] = $tempval[0]['SETDES'];
                $valori[0]['conta'] = 1;
                continue;
            }
            $verifica = true;
            for ($j = 0; $j < count($valori); $j++) {
                // Se il settore è gia presente aggiungi 1..
                if ($valori[$j]['nome'] == $tempval[$i]['SETDES']) {
                    $valori[$j]['conta'] ++;
                    $verifica = false;
                    break;
                }
            }
            // .. altrimenti aggiungi settore in lista
            if ($verifica) {
                $index = count($valori);
                $valori[$index]['nome'] = $tempval[$i]['SETDES'];
                $valori[$index]['conta'] = 1;
            }
        }

        $totale = 0;
        for ($i = 0; $i < count($valori); $i++) {
            $valori[$i]['nome'] = $valori[$i]['nome'] . ' (' . $valori[$i]['conta'] . ')';
            $totale += $valori[$i]['conta'];
        }
        for ($i = 0; $i < count($valori); $i++) {
            $valori[$i]['conta'] = $valori[$i]['conta'] * 100 / $totale;
            $valori[$i]['conta'] = number_format($valori[$i]['conta'], 2);
        }
        // Crea la tabella
        $table .= '<div style="display: none; height: 1px;">';
        $table .= '<table id="' . $this->idTabella . '" class="ita-jqGraph {caption:\'' . $this->caption . '\',subcaption:\'' . $this->subcaption . '\',container:\'' . $this->idGrafico . '\',type:\'pie\'}">';
        $table .= ' <thead><tr>';

        for ($i = 0; $i < count($valori); $i++) {
            $table .= '<th>' . $valori[$i]['nome'] . '</th>';
        }

        $table .= ' </tr></thead>
                    <tbody>
                        <tr>';

        for ($i = 0; $i < count($valori); $i++) {
            $table .= '<td>' . $valori[$i]['conta'] . '</td>';
        }

        $table .= '     </tr>
                    </tbody>';
        $table .= '</table></div>';
        $table .= '<br/><div style="width:100%;text-align:center;font-weight:bold;font-size:16px;margin-bottom:20px;">' . $this->caption . ': ' . $totale . '</div>';
        return $table;
    }

    public function createTableProcSegn() {
        // Leggo i dati
        $nomi = array();
        $valori = array();
        $sql = "SELECT
					*
				FROM
					PRORIC, ANAPRA
				WHERE
					(PRORIC.RICSTA = '01' OR PRORIC.RICSTA='91') AND RICRPA='' AND SUBSTRING(RICDRE,1,4) = '" . $this->anno . "'
				  AND
					PRORIC.RICPRO = ANAPRA.PRANUM";
        if ($this->sportello != '') {
            $sql .= " AND PRORIC.RICTSP = " . $this->sportello;
        }
        //$sql_1 = "SELECT * FROM ANATSP WHERE TSPCOD = " . $this->sportello;
        $totrec = array();
        foreach ($this->lista_enti as $ente) {
            $this->PRAM_DB = ItaDB::DBOpen('PRAM', $ente);
            $Proric_tab = ItaDB::DBSQLSelect($this->PRAM_DB, $sql);
            $totrec[] = $Proric_tab;

            //$valori[] = count($Proric_tab);
            //$Proric_rec = ItaDB::DBSQLSelect($this->PRAM_DB, $sql_1, false);
            //$nomi[] = $Proric_rec['TSPDES'] . ' - ' . $Proric_rec['TSPCOM'] . ' (' . count($Proric_tab) . ')';
        }

        // 'Spiano' l'array
        $tempval = array();
        foreach ($totrec as $rec) {
            $tempval = array_merge($tempval, $rec);
        }

        for ($i = 0; $i < count($tempval); $i++) {
            /* if ($i == 0) {
              $valori[0]['nome'] = $tempval[0]['PRASEG'];
              $valori[0]['conta'] = 1;
              continue;
              } */
            if ($tempval[$i]['PRASEG'] == '') {
                $tempval[$i]['PRASEG'] = 'ALTRO';
            }

            $verifica = true;
            for ($j = 0; $j < count($valori); $j++) {
                // Se il settore è gia presente aggiungi 1..
                if ($valori[$j]['nome'] == $tempval[$i]['PRASEG']) {
                    $valori[$j]['conta'] ++;
                    $verifica = false;
                    break;
                }
            }
            // .. altrimenti aggiungi settore in lista
            if ($verifica) {
                $index = count($valori);
                $valori[$index]['nome'] = $tempval[$i]['PRASEG'];
                $valori[$index]['conta'] = 1;
            }
        }

        $totale = 0;
        for ($i = 0; $i < count($valori); $i++) {
            $valori[$i]['nome'] = $valori[$i]['nome'] . ' (' . $valori[$i]['conta'] . ')';
            $totale += $valori[$i]['conta'];
        }

        // Controllo se è tutto zero
        if ($totale == 0) {
            return '<script type="text/javascript">$("#' . $this->idGrafico . '").html("Non ci sono richieste da mostrare.");</script>';
        }

        for ($i = 0; $i < count($valori); $i++) {
            $valori[$i]['conta'] = $valori[$i]['conta'] * 100 / $totale;
            $valori[$i]['conta'] = number_format($valori[$i]['conta'], 2);
        }

        // Crea la tabella
        $table = '<div style="display: none; height: 1px;">';
        $table .= '<table id="' . $this->idTabella . '" class="ita-jqGraph {caption:\'' . $this->caption . '\',subcaption:\'' . $this->subcaption . '\',container:\'' . $this->idGrafico . '\',type:\'pie\'}">';
        $table .= ' <thead><tr>';

        for ($i = 0; $i < count($valori); $i++) {
            $table .= '<th>' . $valori[$i]['nome'] . '</th>';
        }

        $table .= ' </tr></thead>
                    <tbody>
                        <tr>';

        for ($i = 0; $i < count($valori); $i++) {
            $table .= '<td>' . $valori[$i]['conta'] . '</td>';
        }

        $table .= '     </tr>
                    </tbody>';
        $table .= '</table></div>';
        $table .= '<br/><div style="width:100%;text-align:center;font-weight:bold;font-size:16px;margin-bottom:20px;">' . $this->caption . ': ' . $totale . '</div>';
        return $table;
    }

    // Percentualizzare i valori
    public function valoriPercento($valori) {
        $somma = 0;
        for ($i = 0; $i < count($valori); $i++) {
            $somma += $valori[$i];
        }
        for ($i = 0; $i < count($valori); $i++) {
            $valori[$i] = $valori[$i] * 100 / $somma;
            $valori[$i] = number_format($valori[$i], 2);
        }
        return $valori;
    }

    public function initValoriMesi($valori) {
        $valori['01'] = 0;
        $valori['02'] = 0;
        $valori['03'] = 0;
        $valori['04'] = 0;
        $valori['05'] = 0;
        $valori['06'] = 0;
        $valori['07'] = 0;
        $valori['08'] = 0;
        $valori['09'] = 0;
        $valori['10'] = 0;
        $valori['11'] = 0;
        $valori['12'] = 0;
        return $valori;
    }

}

?>