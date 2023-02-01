<?php

class IstruttoriaScurcola extends praCustomClass {

    public function disegnaSelezioneZona() {
        $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');

        $praLibHtml = new praLibHtml();

        $sql = "SELECT ID_DESTINAZIONE, DESC_DESTINAZIONE FROM PRG_DESTINAZIONI_URBANISTICHE";
        $destinazione_tab = ItaDB::DBSQLSelect($ITALWEB_DB, $sql);

        $praLibElaborazioniDati = new praLibElaborazioneDati();
        $default = $value = '';
        if (($this->datoAggiuntivo['DAGVAL'] === '' || !isset($this->datoAggiuntivo['DAGVAL']) ) && $this->datoAggiuntivo['DAGDEF'] !== '') {
            $default = $praLibElaborazioniDati->elaboraValoreProdag($this->datoAggiuntivo, $this->dizionario);
        }

        $options = '';
        foreach ($destinazione_tab as $destinazione_rec) {
            if ($default == $destinazione_rec['ID_DESTINAZIONE']) {
                $value = $destinazione_rec['ID_DESTINAZIONE'];
            }

            $options .= '|' . $destinazione_rec['ID_DESTINAZIONE'] . ':' . $destinazione_rec['DESC_DESTINAZIONE'];
        }

        $this->datoAggiuntivo['DAGTIC'] = 'Select';
        $this->datoAggiuntivo['DAGDEF'] = $options;
        if ($value) {
            $this->datoAggiuntivo['DAGVAL'] = $value;

            foreach ($this->datiAggiuntivi as $k => $datoAggiuntivo) {
                if ($datoAggiuntivo['DAGKEY'] === 'SELEZIONE_ZONA_PRG') {
                    $this->datiAggiuntivi[$k]['DAGVAL'] = $value;
                }
            }

            $this->creaRisorseZona();
        }

        $html .= $praLibHtml->getProdagHtmlField($this->datoAggiuntivo, $this->callerForm->nameForm, 'DATIAGGIUNTIVI');

        return $html;
    }

    public function disegnaSelezioneDestUso() {
        $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');

        $praLibHtml = new praLibHtml();

        $sql = "SELECT ID_DESTINAZIONE, DESC_DESTINAZIONE FROM PRG_DESTINAZIONI_USO";
        $destinazione_tab = ItaDB::DBSQLSelect($ITALWEB_DB, $sql);

        $praLibElaborazioniDati = new praLibElaborazioneDati();
        $default = $value = '';
        if (($this->datoAggiuntivo['DAGVAL'] === '' || !isset($this->datoAggiuntivo['DAGVAL']) ) && $this->datoAggiuntivo['DAGDEF'] !== '') {
            $default = $praLibElaborazioniDati->elaboraValoreProdag($this->datoAggiuntivo, $this->dizionario);
        }

        $options = '';
        foreach ($destinazione_tab as $destinazione_rec) {
            if ($default == $destinazione_rec['ID_DESTINAZIONE']) {
                $value = $destinazione_rec['ID_DESTINAZIONE'];
            }

            $options .= '|' . $destinazione_rec['ID_DESTINAZIONE'] . ':' . $destinazione_rec['DESC_DESTINAZIONE'];
        }

        $this->datoAggiuntivo['DAGTIC'] = 'Select';
        $this->datoAggiuntivo['DAGDEF'] = $options;
        if ($value) {
            $this->datoAggiuntivo['DAGVAL'] = $value;
        }

        $html .= $praLibHtml->getProdagHtmlField($this->datoAggiuntivo, $this->callerForm->nameForm, 'DATIAGGIUNTIVI');

        return $html;
    }

    public function creaRisorseZona() {
        $ITALWEB_DB = ItaDB::DBOpen('ITALWEB');
        $praLibElaborazioniDati = new praLibElaborazioneDati();

        $dizionario = $this->dizionario;

        $selezioneZonaUrb = $selezioneZonaUso = '';
        foreach ($this->datiAggiuntivi as $datoAggiuntivo) {
            if ($datoAggiuntivo['DAGKEY'] === 'SELEZIONE_ZONA_PRG') {
                $selezioneZonaUrb = $datoAggiuntivo['DAGVAL'];
                if ($selezioneZonaUrb === '') {
                    $selezioneZonaUrb = $praLibElaborazioniDati->elaboraValoreProdag($datoAggiuntivo, $dizionario);
                }
            }

            if ($datoAggiuntivo['DAGKEY'] === 'SELEZIONE_DESTINAZIONE_PRG') {
                $selezioneZonaUso = $datoAggiuntivo['DAGVAL'];
                if ($selezioneZonaUso === '') {
                    $selezioneZonaUso = $praLibElaborazioniDati->elaboraValoreProdag($datoAggiuntivo, $dizionario);
                }
            }
        }

        if (!$selezioneZonaUrb) {
            $sql = "SELECT * FROM PRG_DESTINAZIONI_URBANISTICHE LIMIT 1";
        } else {
            $sql = "SELECT * FROM PRG_DESTINAZIONI_URBANISTICHE WHERE ID_DESTINAZIONE = '" . addslashes($selezioneZonaUrb) . "'";
        }

        $destinazione_rec = ItaDB::DBSQLSelect($ITALWEB_DB, $sql, false);
        $destinazione_rec_keys = array_keys($destinazione_rec);

        foreach ($destinazione_rec_keys as $key) {
            $dizionario["PRARISORSE.$key"] = $selezioneZonaUrb ? $destinazione_rec[$key] : '0';
        }

        if ($selezioneZonaUrb) {
            $this->callerForm->setDatoAggiuntivo('DESC_ZONA_PRG', $destinazione_rec['DESC_DESTINAZIONE']);
        }

        if ($selezioneZonaUso) {
            $sql = "SELECT * FROM PRG_DESTINAZIONI_USO WHERE ID_DESTINAZIONE = '" . addslashes($selezioneZonaUso) . "'";
            $destinazione_rec = ItaDB::DBSQLSelect($ITALWEB_DB, $sql, false);
            if ($destinazione_rec) {
                $dizionario["PRARISORSE.DESTINAZIONE_DUSO"] = $destinazione_rec['DESC_DESTINAZIONE'];
                $this->callerForm->setDatoAggiuntivo('DESC_DESTINAZIONE_PRG', $destinazione_rec['DESC_DESTINAZIONE']);
            }
        }

        $this->callerForm->setDizionario($dizionario);

        return true;
    }

}
