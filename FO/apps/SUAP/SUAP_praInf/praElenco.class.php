<?php

class praElenco extends praSchedaTemplate {

    public function getHtml($dati) {
        $tmp_output = output::$html_out;
        output::$html_out = '';

        require_once ITA_PRATICHE_PATH . '/PRATICHE_italsoft/praHtmlVis.class.php';
        $praHtmlVis = new praHtmlVis();

        if (!$praHtmlVis->DisegnaPagina($dati, array(
                "PRAM_DB" => $this->PRAM_DB,
                "Tipo" => "",
                "TipoFiltro" => "99",
                "procedi" => $dati['Codice'],
                "falseOnEmpty" => "1",
                "config" => $this->config,
                "modo" => "cportal",
                'idTemplate' => $this->config['idTemplate']
            ))
        ) {
            $return = output::$html_out;
            output::$html_out = $tmp_output;
            return false;
        }

        $return = output::$html_out;
        output::$html_out = $tmp_output;
        return $return;
    }

}
