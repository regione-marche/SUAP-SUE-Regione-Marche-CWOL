<?php

/*
 * PHP Version 5
 *
 * @category
 * @package    Segreteria
 * @author     Michele Moscioni <michele.moscioni@italsoft.eu>
 * @author     Alessandro Mucci <alessandro.mucci@italsoft.eu>
 * @copyright  1987-2011 Italsoft srl
 * @license
 * @version    07.12.2016
 * @link
 * @see
 * @since
 * @deprecated
 */
include_once (ITA_LIB_PATH . '/itaPHPDocs/itaDictionary.class.php');
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibSerie.class.php';
include_once ITA_BASE_PATH . '/apps/Documenti/docLib.class.php';

class proLibVariabiliFascicolo {

    /**
     * Libreria di funzioni Dizionario fascicolo
     *
     * @param <type> $returnModel programma chiamante
     * @param <type> $where filtro da applicare alla query(formato sql-where)
     */
    public $anaorg_rec;
    public $extra_parm;
    private $legenda;
    private $variabiliBase;
    private $variabiliAnaorg;

    public function getAnaorg_rec() {
        return $this->anaorg_rec;
    }

    public function setAnaorg_rec($anaorg_rec) {
        $this->anaorg_rec = $anaorg_rec;
    }

    public function getExtra_parm() {
        return $this->extra_parm;
    }

    public function setExtra_parm($extra_parm) {
        $this->extra_parm = $extra_parm;
    }

    function __construct($anaorg_rec = array(), $extraParm = array()) {
        $this->anaorg_rec = $anaorg_rec;
        $this->extra_parm = $extraParm;

        $this->setVariabiliAnaorg();
        if ($this->anaorg_rec) {
            $this->ValorizzaVariabiliAll();
        }
        $this->setVariabiliBase();
    }

    public function ValorizzaVariabiliAll() {
        $this->valorizzaVariabiliAnaorg($this->anaorg_rec, $this->extra_parm);
        $this->setVariabiliBase();
        return $this->variabiliBase;
    }

    public function getITALWEB() {
        if (!$this->ITALWEB) {
            try {
                $this->ITALWEB = ItaDB::DBOpen('ITALWEB');
            } catch (Exception $e) {
                Out::msgStop("Errore", $e->getMessage());
            }
        }
        return $this->ITALWEB;
    }

    public function getLegenda() {
        $this->legenda = $this->getLegendaGenerico();
        return $this->legenda;
    }

    public function getVariabiliBase() {
        return $this->variabiliBase;
    }

    public function getVariabiliAnaorg() {
        return $this->variabiliAnaorg;
    }

    public function extractAllData() {
        //$variabili = array_merge($this->getVariabiliIndice()->getAlldata(), $this->getVariabiliAnavar()->getAlldata());
        return $this->getVariabiliBase()->getAllDataFormatted();
    }

    /**
     * Ritorna una legenda di campi per le pratiche on-line in formato adjacency per
     * I campi non sono riferiti a procedimento o pratica
     * @param type $tipo
     * @param type $markup
     * @return type
     */
    public function getLegendaGenerico($tipo = "adjacency", $markup = 'smarty') {
        return $this->variabiliBase->exportAdjacencyModel($markup);
    }

    public function setVariabiliBase() {
        $this->variabiliBase = $this->variabiliAnaorg;
    }

    public function setVariabiliAnaorg() {
        $i = 1;
        $dizionario = new itaDictionary();
        $dizionario->addField('CATEGORIA', 'Categoria/Titolo', $i++, 'base', '@{$CATEGORIA}@');
        $dizionario->addField('DESC_CATEGORIA', 'Descrizione Categoria/Titolo', $i++, 'base', '@{$DESC_CATEGORIA}@');
        $dizionario->addField('CATEGORIA_NROM', 'Numero Romano Categoria/Titolo', $i++, 'base', '@{$CATEGORIA_NROM}@');
        $dizionario->addField('CLASSE', 'Classe Titolario', $i++, 'base', '@{$CLASSE}@');
        $dizionario->addField('DESC_CLASSE', 'Classe Titolario', $i++, 'base', '@{$DESC_CLASSE}@');
        $dizionario->addField('SOTTOCLASSE', 'Sottoclasse Titolario', $i++, 'base', '@{$SOTTOCLASSE}@');
        $dizionario->addField('DESC_SOTTOCLASSE', 'Sottoclasse Titolario', $i++, 'base', '@{$DESC_SOTTOCLASSE}@');
        $dizionario->addField('ANNO', 'Anno del Fascicolo', $i++, 'base', '@{$ANNO}@');
        $dizionario->addField('PROGRESSIVO', 'Progressivo del Fascicolo nel titolario', $i++, 'base', '@{$PROGRESSIVO}@');
        $dizionario->addField('PROGRESSIVOUNICO', 'Progressivo Unico del Fascicolo', $i++, 'base', '@{$PROGRESSIVOUNICO}@');
        $dizionario->addField('CODICE_SERIE', 'Codice Serie del Fascicolo', $i++, 'base', '@{$CODICE_SERIE}@');
        $dizionario->addField('DESC_SERIE', 'Descrizione della Serie del Fascicolo', $i++, 'base', '@{$DESC_SERIE}@');
        $dizionario->addField('SIGLA_SERIE', 'Sgila della Serie del Fascicolo', $i++, 'base', '@{$SIGLA_SERIE}@');
        $dizionario->addField('PROG_SERIE', 'Progressivo della Serie del Fascicolo', $i++, 'base', '@{$PROG_SERIE}@');
        $this->variabiliAnaorg = $dizionario;

        return $this->variabiliAnaorg;
    }

    public function valorizzaVariabiliAnaorg($Anaorg_rec, $ExtraParam = array()) {

        $proLib = new proLib();
        $proLibSerie = new proLibSerie();

        $catNRom = '';
        $categoria = $classe = $sottoclasse = '';
        $descat = $descla = $dessotcla = '';
        if (strlen($Anaorg_rec['ORGCCF']) === 4) {
            $categoria = $Anaorg_rec['ORGCCF'];
        } else if (strlen($Anaorg_rec['ORGCCF']) === 8) {
            $categoria = substr($Anaorg_rec['ORGCCF'], 0, 4);
            $classe = substr($Anaorg_rec['ORGCCF'], 4, 4);
        } else if (strlen($Anaorg_rec['ORGCCF']) === 12) {
            $categoria = substr($Anaorg_rec['ORGCCF'], 0, 4);
            $classe = substr($Anaorg_rec['ORGCCF'], 4, 4);
            $sottoclasse = substr($Anaorg_rec['ORGCCF'], 8, 4);
        } else {
            $categoria = $Anaorg_rec['ORGCCF'];
        }
        if ($categoria) {
            $anacat_rec = $proLib->GetAnacat($Anaorg_rec['VERSIONE_T'], $categoria, 'codice');
            $descat = $anacat_rec['CATDES'];
            $catNRom = $anacat_rec['NUMROMANA'];
        }
        if ($classe) {
            $anacla_rec = $proLib->GetAnacla($Anaorg_rec['VERSIONE_T'], $classe, 'clacod');
            $descla = $anacla_rec['CLADE1'] . $anacla_rec['CLADE2'];
        }
        if ($sottoclasse) {
            $anafas_rec = $proLib->GetAnafas($Anaorg_rec['VERSIONE_T'], $sottoclasse, 'codice');
            $dessotcla = $anafas_rec['FASDES'];
        }
        $DescSerie = $SiglaSerie = '';
        if ($Anaorg_rec['CODSERIE']) {
            $Anaseriearc_rec = $proLibSerie->GetSerie($Anaorg_rec['CODSERIE'], 'codice');
            $DescSerie = $Anaseriearc_rec['DESCRIZIONE'];
            $SiglaSerie = $Anaseriearc_rec['SIGLA'];
        }

        $this->variabiliAnaorg->addFieldData('CATEGORIA', $categoria);
        $this->variabiliAnaorg->addFieldData('DESC_CATEGORIA', $descat);
        $this->variabiliAnaorg->addFieldData('CATEGORIA_NROM', $catNRom);
        $this->variabiliAnaorg->addFieldData('CLASSE', $classe);
        $this->variabiliAnaorg->addFieldData('DESC_CLASSE', $descla);
        $this->variabiliAnaorg->addFieldData('SOTTOCLASSE', $sottoclasse);
        $this->variabiliAnaorg->addFieldData('DESC_SOTTOCLASSE', $dessotcla);
        $this->variabiliAnaorg->addFieldData('ANNO', $Anaorg_rec['ORGANN']);
        $this->variabiliAnaorg->addFieldData('PROGRESSIVOUNICO', $Anaorg_rec['ORGKEY']);
        $this->variabiliAnaorg->addFieldData('PROGRESSIVO', $Anaorg_rec['ORGCOD']);
        $this->variabiliAnaorg->addFieldData('CODICE_SERIE', $Anaorg_rec['CODSERIE']);
        $this->variabiliAnaorg->addFieldData('DESC_SERIE', $DescSerie);
        $this->variabiliAnaorg->addFieldData('SIGLA_SERIE', $SiglaSerie);
        $this->variabiliAnaorg->addFieldData('PROG_SERIE', $Anaorg_rec['PROGSERIE']);
    }

}

?>