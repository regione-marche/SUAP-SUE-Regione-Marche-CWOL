<?php

include_once ITA_BASE_PATH . '/apps/Pratiche/praLib.class.php';
include_once ITA_LIB_PATH . '/itaPHPCore/DiagramView.class.php';

class praLibDiagramma {

    const DIAGRAMMA_PROCEDIMENTO = 1;
    const DIAGRAMMA_FASCICOLO = 2;
    const STATO_GRUPPO_NASCOSTO = 0;
    const STATO_GRUPPO_VISIBILE = 1;


    private $praLib;
    private $idDiagramma;
    private $currentSchema;
    private $schemaFascicolo = array(
        'ITEPAS' => 'PROPAS',
        'ITECOD' => 'PRONUM',
        'ITEKEY' => 'PROPAK',
        'ITESEQ' => 'PROSEQ',
        'ITEQST' => 'PROQST',
        'ITEDES' => 'PRODPA',
        'ITEVPA' => 'PROVPA',
        'ITEVPN' => 'PROVPN',
        'ITEVPADETT' => 'PROVPADETT',
        'ITEVPADESC' => 'PROVPADESC',
        'ITEDIAGGRUPPI' => 'PRODIAGGRUPPI',
        'ITEDIAGPASSIGRUPPI' => 'PRODIAGPASSIGRUPPI',
        'ROW_ID_ITEDIAGGRUPPI' => 'ROW_ID_PRODIAGGRUPPI'
    );

    public static $STATI_GRUPPO = array(
        self::STATO_GRUPPO_NASCOSTO => 'Nascosto',
        self::STATO_GRUPPO_VISIBILE => 'Visibile'
    );
    

    public function __construct($idDiagramma, $tipologiaDiagramma) {
        switch ($tipologiaDiagramma) {
            case self::DIAGRAMMA_PROCEDIMENTO:
                $campiProcedimento = array_keys($this->schemaFascicolo);
                $this->currentSchema = array_combine($campiProcedimento, $campiProcedimento);
                break;

            case self::DIAGRAMMA_FASCICOLO:
                $this->currentSchema = $this->schemaFascicolo;
                break;

            default:
                throw new Exception('Tipologia diagramma non valido');
        }

        $this->idDiagramma = $idDiagramma;
        $this->praLib = new praLib;
    }

    public function mostraNodi($codice) {
        $sql = "SELECT {$this->currentSchema['ITEKEY']} FROM {$this->currentSchema['ITEPAS']} WHERE {$this->currentSchema['ITECOD']} = '$codice'";
        $nodi = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql);

        foreach ($nodi as $nodo) {
            DiagramView::diagramShowElement($this->idDiagramma, $nodo[$this->currentSchema['ITEKEY']]);
        }
    }

    public function mostraNodiGruppo($idGruppo) {
        $sql = "SELECT * FROM {$this->currentSchema['ITEDIAGPASSIGRUPPI']} WHERE {$this->currentSchema['ROW_ID_ITEDIAGGRUPPI']} = '$idGruppo'";
        $nodiGruppo = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql);

        foreach ($nodiGruppo as $nodoGruppo) {
            DiagramView::diagramShowElement($this->idDiagramma, $nodoGruppo[$this->currentSchema['ITEKEY']]);
        }
    }

    public function nascondiNodiGruppo($idGruppo) {
        $sql = "SELECT * FROM {$this->currentSchema['ITEDIAGPASSIGRUPPI']} WHERE {$this->currentSchema['ROW_ID_ITEDIAGGRUPPI']} = '$idGruppo'";
        $nodiGruppo = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql);

        foreach ($nodiGruppo as $nodoGruppo) {
            DiagramView::diagramHideElement($this->idDiagramma, $nodoGruppo[$this->currentSchema['ITEKEY']]);
        }
    }

    public function generaDiagramma($codice, $jsonDiagramma = false) {
        $diagramData = new DiagramData();

        if ($jsonDiagramma) {
            $diagramData->importJSON($jsonDiagramma);
        }

        $sql = "SELECT * FROM {$this->currentSchema['ITEPAS']} WHERE {$this->currentSchema['ITECOD']} = '$codice' ORDER BY {$this->currentSchema['ITESEQ']}";
        $passiDiagramma = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql);

        $diagramPaths = $diagramData->getPaths();

        foreach ($passiDiagramma as $passoDiagramma) {
            $diagramData->addNode(array(
                'id' => $passoDiagramma[$this->currentSchema['ITEKEY']],
                'type' => $passoDiagramma[$this->currentSchema['ITEQST']] == 1 ? DiagramView::FLOWCHART_QUESTION : DiagramView::FLOWCHART_ACTION,
                'text' => $passoDiagramma[$this->currentSchema['ITEDES']]
            ));

            $arrayDestinazioni = $this->getDestinazioniPasso($passoDiagramma);

            foreach ($diagramPaths as $path) {
                list($sourceNode, ) = explode('.', $path['source']);
                list($targetNode, ) = explode('.', $path['target']);
                if ($sourceNode == $passoDiagramma[$this->currentSchema['ITEKEY']]) {
                    $pathExists = false;

                    foreach ($arrayDestinazioni as $destinazione) {
                        if ($targetNode == $destinazione['id']) {
                            $pathExists = true;
                        }
                    }

                    if (!$pathExists) {
                        $diagramData->removePath($path);
                    }
                }
            }

            foreach ($arrayDestinazioni as $destinazione) {
                if (!$diagramData->edgeExists($passoDiagramma[$this->currentSchema['ITEKEY']], $destinazione['id'])) {
                    $diagramData->addEdge($passoDiagramma[$this->currentSchema['ITEKEY']], $destinazione['id'], $destinazione['label']);
                } else {
                    $diagramData->updateEdgeLabel($passoDiagramma[$this->currentSchema['ITEKEY']], $destinazione['id'], $destinazione['label']);
                }
            }
        }

        foreach ($diagramData->getNodes() as $node) {
            if ($node['type'] == DiagramView::FLOWCHART_EDGE_BREAK) {
                continue;
            }

            $nodeExists = false;

            foreach ($passiDiagramma as $passoDiagramma) {
                if ($passoDiagramma[$this->currentSchema['ITEKEY']] == $node['id']) {
                    $nodeExists = true;
                    break;
                }
            }

            if (!$nodeExists) {
                $diagramData->removeNode($node['id']);
            }
        }

        return $diagramData->getJSON();
    }

    public function getDestinazioniPasso($passoDiagramma) {
        $destinazioniPasso = array();

        switch ($passoDiagramma[$this->currentSchema['ITEQST']]) {
            case 0:
                if ($passoDiagramma[$this->currentSchema['ITEVPA']]) {
                    $destinazioniPasso[] = array(
                        'id' => $passoDiagramma[$this->currentSchema['ITEVPA']],
                        'label' => ''
                    );
                }
                break;

            case 1:
                if ($passoDiagramma[$this->currentSchema['ITEVPA']]) {
                    $destinazioniPasso[] = array(
                        'id' => $passoDiagramma[$this->currentSchema['ITEVPA']],
                        'label' => 'Sì'
                    );
                }

                if ($passoDiagramma[$this->currentSchema['ITEVPN']]) {
                    $destinazioniPasso[] = array(
                        'id' => $passoDiagramma[$this->currentSchema['ITEVPN']],
                        'label' => 'No'
                    );
                }
                break;

            case 2:
                $sql = "SELECT * FROM {$this->currentSchema['ITEVPADETT']} WHERE {$this->currentSchema['ITEKEY']} = '" . $passoDiagramma[$this->currentSchema['ITEKEY']] . "'";
                $passiDestinazione = ItaDB::DBSQLSelect($this->praLib->getPRAMDB(), $sql);

                if (!$passiDestinazione) {
                    break;
                }

                foreach ($passiDestinazione as $passoDestinazione) {
                    $destinazioniPasso[] = array(
                        'id' => $passoDestinazione[$this->currentSchema['ITEVPA']],
                        'label' => $passoDestinazione[$this->currentSchema['ITEVPADESC']]
                    );
                }

                break;
        }

        return $destinazioniPasso;
    }

}
