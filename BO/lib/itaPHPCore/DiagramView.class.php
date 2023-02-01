<?php

class DiagramView {

    const FLOWCHART_EDGE_BREAK = 'edgeBreak';
    const FLOWCHART_START = 'start';
    const FLOWCHART_QUESTION = 'question';
    const FLOWCHART_ACTION = 'action';
    const FLOWCHART_OUTPUT = 'output';

    private $diagramID;
    public static $spawnYOffset = 20;
    public static $defaults = array(
        self::FLOWCHART_START => array('w' => 110, 'h' => 50),
        self::FLOWCHART_QUESTION => array('w' => 90, 'h' => 90),
        self::FLOWCHART_ACTION => array('w' => 130, 'h' => 50),
        self::FLOWCHART_OUTPUT => array('w' => 130, 'h' => 50)
    );
    public static $nodesType = array(
        self::FLOWCHART_START => 'Inizio',
        self::FLOWCHART_QUESTION => 'Domanda',
        self::FLOWCHART_ACTION => 'Azione',
        self::FLOWCHART_OUTPUT => 'Output'
    );

    function __construct($diagramID = '') {
        $this->diagramID = $diagramID;
    }

    private static function jsPlumbCommand($command) {
        Out::codice("jsPlumbToolkit.ready(function () { $command });");
    }

    public function importJSON($json) {
        self::diagramImportJSON($this->diagramID, $json);
    }

    public static function diagramImportJSON($diagramId, $json) {
        $script .= "  var pan = jsPlumbInstances.Renderers['$diagramId'].getPan();";
        $script .= "  jsPlumbInstances['$diagramId'].clear();";
        $script .= "  jsPlumbInstances['$diagramId'].load({type: \"json\", data: $json});";
        $script .= "  jsPlumbInstances.Renderers['$diagramId'].refresh();";
        $script .= "  itaJsPlumbHelper.setPan('$diagramId', pan[0], pan[1]);";
        self::jsPlumbCommand($script);
    }

    public function clear() {
        self::diagramClear($this->diagramID);
    }

    public static function diagramClear($diagramId) {
        self::jsPlumbCommand("jsPlumbInstances['$diagramId'].clear();");
    }

    public function setSelection($id) {
        self::diagramSetSelection($this->diagramID, $id);
    }

    public static function diagramSetSelection($diagramId, $id) {
        self::diagramClearSelection($diagramId);
        self::jsPlumbCommand("jsPlumbInstances['$diagramId'].setSelection('$id');");
        self::jsPlumbCommand("jsPlumbInstances.Renderers['$diagramId'].centerOn('$id');");
    }

    public function clearSelection() {
        self::diagramClearSelection($this->diagramID);
    }

    public static function diagramClearSelection($diagramId) {
        self::jsPlumbCommand("jsPlumbInstances['$diagramId'].clearSelection();");
    }

    public function setLayout($type) {
        self::diagramSetLayout($this->diagramID, $type);
    }

    public static function diagramSetLayout($diagramId, $type) {
        self::jsPlumbCommand("jsPlumbInstances.Renderers['$diagramId'].setLayout({type: '$type', parameters: { padding: [100, 100], spacing: 'compress' }});");
        self::jsPlumbCommand("jsPlumbInstances.Renderers['$diagramId'].setLayout({type: 'Absolute'});");
        self::jsPlumbCommand("jsPlumbInstances.Renderers['$diagramId'].refresh();");
    }

    public function setPan($x, $y) {
        self::diagramSetPan($this->diagramID, $x, $y);
    }

    public static function diagramSetPan($diagramId, $x, $y) {
        self::jsPlumbCommand("itaJsPlumbHelper.setPan('$diagramId', $x, $y);");
    }

    public function setZoom($zoom) {
        self::diagramSetZoom($this->diagramID, $zoom);
    }

    public static function diagramSetZoom($diagramId, $zoom) {
        self::jsPlumbCommand("itaJsPlumbHelper.setZoom('$diagramId', $zoom);");
    }

    public static function diagramSetNodeBorderColor($diagramId, $nodeId, $borderColor) {
        self::jsPlumbCommand("jsPlumbInstances['$diagramId'].updateNode('$nodeId', { borderColor: '$borderColor' });");
    }

    public static function diagramUnsetNodeBorderColor($diagramId, $nodeId) {
        self::jsPlumbCommand("jsPlumbInstances['$diagramId'].updateNode('$nodeId', { borderColor: '' });");
    }

    public static function diagramSetNodeBackgroundColor($diagramId, $nodeId, $backgroundColor) {
        self::jsPlumbCommand("jsPlumbInstances['$diagramId'].updateNode('$nodeId', { backgroundColor: '$backgroundColor' });");
    }

    public static function diagramUnsetNodeBackgroundColor($diagramId, $nodeId) {
        self::jsPlumbCommand("jsPlumbInstances['$diagramId'].updateNode('$nodeId', { backgroundColor: '' });");
    }

    public static function diagramSetNodeTextColor($diagramId, $nodeId, $textColor) {
        self::jsPlumbCommand("jsPlumbInstances['$diagramId'].updateNode('$nodeId', { textColor: '$textColor' });");
    }

    public static function diagramUnsetNodeTextColor($diagramId, $nodeId) {
        self::jsPlumbCommand("jsPlumbInstances['$diagramId'].updateNode('$nodeId', { textColor: '' });");
    }

    public static function diagramSetPathColor($diagramId, $source, $target, $color) {
        self::jsPlumbCommand("itaJsPlumbHelper.setPathPaintStyle(jsPlumbInstances['$diagramId'], '$source', '$target', '$color');");
    }

    public static function diagramHideElement($diagramId, $elementId) {
        self::jsPlumbCommand("itaJsPlumbHelper.setVisible('$elementId', false, '$diagramId');");
    }

    public static function diagramShowElement($diagramId, $elementId) {
        self::jsPlumbCommand("itaJsPlumbHelper.setVisible('$elementId', true, '$diagramId');");
    }

}

class DiagramData {

    private $data = array(
        'nodes' => array(),
        'ports' => array(),
        'edges' => array(),
        'groups' => array()
    );

    /*
     * Ritorna tutti i nodi.
     */

    public function getNodes() {
        return $this->data['nodes'];
    }

    /*
     * Ritorna tutti i singoli collegamenti, inclusi quelli fra
     * "edgeBreak" (nodi di spezzamento del collegamento).
     */

    public function getEdges() {
        return $this->data['edges'];
    }

    /*
     * Ritorna tutti i collegamenti fra nodi, escludendo gli intermezzi
     * con gli "edgeBreak" (nodi di spezzamento del collegamento).
     */

    public function getPaths() {
        $returnArray = array();

        foreach ($this->data['edges'] as $edge) {
            if ($this->portExists($edge['source'])) {
                $returnArray[] = array(
                    'source' => $edge['source'],
                    'target' => $this->getEdgeFinalTarget($edge)
                );
            }
        }

        return $returnArray;
    }

    /*
     * Ritorna il nodo con le coordinate nell'asse verticale più basse.
     */

    public function getBottomMostNode() {
        $bottomMostNode = null;
        $x = 0;

        foreach ($this->data['nodes'] as $node) {
            if ($node['type'] === DiagramView::FLOWCHART_EDGE_BREAK) {
                continue;
            }

            if (($node['top'] + $node['h']) > $x) {
                $x = $node['top'] + $node['h'];
                $bottomMostNode = $node;
            }
        }

        return $bottomMostNode;
    }

    /*
     * Verifica l'esistenza di un nodo.
     */

    public function nodeExists($id) {
        foreach ($this->data['nodes'] as $node) {
            if ($node['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    /*
     * Verifica l'esistenza di una porta (elemento di ingresso/uscita in un nodo).
     */

    public function portExists($id) {
        foreach ($this->data['ports'] as $port) {
            if ($port['id'] === $id) {
                return $port;
            }
        }

        return false;
    }

    /*
     * Verifica l'esistenza di un collegamento.
     */

    public function edgeExists($source, $target) {
        $source_id = "$source.O$target";
        $target_id = "$target.I$source";

        foreach ($this->data['edges'] as $edge) {
            if ($edge['source'] === $source_id && $this->getEdgeFinalTarget($edge) === $target_id) {
                return true;
            }
        }

        return false;
    }

    /*
     * Ritorna l'effettivo target di un collegamento saltando i vari "edgeBreak"
     * (nodi di spezzamento del collegamento).
     */

    public function getEdgeFinalTarget($source_edge) {
        foreach ($this->data['edges'] as $edge) {
            if ($edge['source'] === $source_edge['target']) {
                return $this->getEdgeFinalTarget($edge);
            }
        }

        return $source_edge['target'];
    }

    /*
     * Aggiorna la label di un collegamento dati il suo punto di origine e destinazione.
     */

    public function updateEdgeLabel($source, $target, $label = '') {
        $source_id = "$source.O$target";
        $target_id = "$target.I$source";

        foreach ($this->data['edges'] as $edge) {
            if ($edge['source'] === $source_id && $this->getEdgeFinalTarget($edge) === $target_id) {
                return $this->updateEdgeLabelRecursive($edge, $label);
            }
        }

        return false;
    }

    private function updateEdgeLabelRecursive($source_edge, $label) {
        if (isset($source_edge['data']['label'])) {
            foreach ($this->data['edges'] as $k => $edge) {
                if ($edge['source'] === $source_edge['source'] && $edge['target'] === $source_edge['target']) {
                    $this->data['edges'][$k]['data']['label'] = $label;
                    return true;
                }
            }
        }

        foreach ($this->data['edges'] as $edge) {
            if ($edge['source'] === $source_edge['target']) {
                return $this->updateEdgeLabelRecursive($edge, $label);
            }
        }

        return false;
    }

    public function addNode($params = array()) {
        if ($this->nodeExists($params['id'])) {
            foreach ($this->data['nodes'] as $i => $node) {
                if ($node['id'] === $params['id']) {
                    $params = array_merge($node, $params);
                    if (isset($params['type']) && isset(DiagramView::$defaults[$params['type']])) {
                        $params = array_merge(DiagramView::$defaults[$params['type']], $params);
                    }

                    $this->data['nodes'][$i] = $params;
                    break;
                }
            }
        } else {
            if (isset($params['type']) && isset(DiagramView::$defaults[$params['type']])) {
                $params = array_merge(DiagramView::$defaults[$params['type']], $params);
            }

            if (!isset($params['top']) || !isset($params['left'])) {
                $bottomMostNode = $this->getBottomMostNode();
                if ($bottomMostNode) {
                    if (!isset($params['top'])) {
                        $params['top'] = $bottomMostNode['top'] + $bottomMostNode['h'] + DiagramView::$spawnYOffset;
                    }

                    if (!isset($params['left'])) {
                        $params['left'] = $bottomMostNode['left'];
                    }
                }
            }

            $this->data['nodes'][] = $params;
        }
    }

    public function removeNode($id) {
        foreach ($this->data['nodes'] as $i => $node) {
            if ($node['id'] === $id) {
                unset($this->data['nodes'][$i]);

                /*
                 * Ripristino l'indice dei valori.
                 */
                $this->data['nodes'] = array_values($this->data['nodes']);

                $this->removeEdgesByNode($id);
                $this->removePortsByNode($id);
                return true;
            }
        }

        return false;
    }

    public function addEdge($source, $target, $label = '') {
        $this->addPort($source, "O$target");
        $this->addPort($target, "I$source");

        $this->data['edges'][] = array(
            'source' => "$source.O$target",
            'target' => "$target.I$source",
            'data' => $label ? array('label' => $label) : array()
        );
    }

    public function removeEdgesByNode($id) {
        $count = count($this->data['edges']);

        foreach ($this->getPaths() as $path) {
            if (strpos($path['source'], $id) === 0 || strpos($path['target'], $id) === 0) {
                $this->removePath($path);
            }
        }

        return ($count !== count($this->data['edges']));
    }

    public function removePortsByNode($id) {
        $count = count($this->data['ports']);

        foreach ($this->data['ports'] as $i => $port) {
            if (strpos($port['id'], $id) === 0) {
                unset($this->data['ports'][$i]);
            }
        }

        $this->data['ports'] = array_values($this->data['ports']);

        return ($count !== count($this->data['ports']));
    }

    public function addPort($nodeid, $anchorid, $data = array()) {
        $data['id'] = "$nodeid.$anchorid";

        if ($this->portExists("$nodeid.$anchorid")) {
            foreach ($this->data['ports'] as $i => $port) {
                if ($port['id'] === "$nodeid.$anchorid") {
                    $this->data['ports'][$i] = array_merge($port, $data);
                    break;
                }
            }
        } else {
            $this->data['ports'][] = $data;
        }

        $data['id'] = $anchorid;
        $anchors = array();

        if ($this->nodeExists($nodeid)) {
            foreach ($this->data['nodes'] as $i => $node) {
                if ($node['id'] === $nodeid && isset($node['anchors'])) {
                    $anchors = $node['anchors'];
                }
            }
        }

        foreach ($anchors as $i => $anchor) {
            if ($anchor['id'] === $anchorid) {
                /*
                 * L'anchor è già presente
                 */

                $anchors[$i] = array_merge($anchor, $data);
                $this->addNode(array('id' => $nodeid, 'anchors' => $anchors));
                return true;
            }
        }

        $anchors[] = $data;
        $this->addNode(array('id' => $nodeid, 'anchors' => $anchors));
        return true;
    }

    public function removePort($portid) {
        list($nodeid, $anchorid) = explode('.', $portid);

        foreach ($this->data['ports'] as $i => $port) {
            if ($port['id'] == $portid) {
                unset($this->data['ports'][$i]);
            }
        }

        if ($nodeid && $anchorid) {
            foreach ($this->data['nodes'] as $i => $node) {
                if ($node['id'] != $nodeid) {
                    continue;
                }

                if (!isset($node['anchors'])) {
                    continue;
                }

                foreach ($node['anchors'] as $j => $anchor) {
                    if ($anchor['id'] != $anchorid) {
                        continue;
                    }

                    unset($this->data['nodes'][$i]['anchors'][$j]);
                    $this->data['nodes'][$i]['anchors'] = array_values($this->data['nodes'][$i]['anchors']);
                }
            }
        }

        $this->data['ports'] = array_values($this->data['ports']);
        return true;
    }

    public function removePath($path) {
        $this->removePort($path['source']);
        $this->removePort($path['target']);

        foreach ($this->data['edges'] as $i => $edge) {
            if ($edge['source'] == $path['source'] && $this->getEdgeFinalTarget($edge) == $path['target']) {
                unset($this->data['edges'][$i]);
                $this->removePathEdge($edge);
            }
        }

        $this->data['edges'] = array_values($this->data['edges']);
        return true;
    }

    private function removePathEdge($source_edge) {
        foreach ($this->data['edges'] as $i => $edge) {
            if ($edge['source'] === $source_edge['target']) {
                $this->removeNode($source_edge['target']);
                unset($this->data['edges'][$i]);
                return $this->removePathEdge($edge);
            }
        }

        return true;
    }

    public function importJSON($json) {
        if (!mb_detect_encoding($json, 'UTF-8', true)) {
            $json = utf8_encode($json);
        }

        $this->data = json_decode($json, true);
    }

    public function getJSON() {

        /*
         * Calcolo il posizionamento degli anchor
         */
        foreach ($this->data['nodes'] as $i => $node) {
            if (!isset($node['anchors'])) {
                continue;
            }

            $n_in = 0;
            $n_out = 0;

            /*
             * Calcolo quanti edge in ingresso e quanti in uscita
             */
            foreach ($node['anchors'] as $anchor) {
                if (isset($anchor['x']) && isset($anchor['y'])) {
                    continue;
                }

                if (strpos($anchor['id'], 'I') === 0) {
                    $n_in++;
                }

                if (strpos($anchor['id'], 'O') === 0) {
                    $n_out++;
                }
            }

            switch ($node['type']) {
                case DiagramView::FLOWCHART_QUESTION:
                    $step_width_x = 0;
                    $middle_x = DiagramView::$defaults[DiagramView::FLOWCHART_QUESTION]['w'] / 2;
                    $in_start_x = $middle_x;
                    $out_start_x = $middle_x;
                    break;

                default:
                    $step_width_x = 20;
                    $middle_x = DiagramView::$defaults[$node['type']]['w'] / 2;
                    $in_start_x = $middle_x - (($n_in - 1 ) / 2) * $step_width_x;
                    $out_start_x = $middle_x - (($n_out - 1 ) / 2) * $step_width_x;
                    break;
            }

            foreach ($node['anchors'] as $j => $anchor) {
                if (isset($anchor['x']) && isset($anchor['y'])) {
                    continue;
                }

                if (strpos($anchor['id'], 'I') === 0) {
                    $this->data['nodes'][$i]['anchors'][$j]['y'] = 0;
                    $this->data['nodes'][$i]['anchors'][$j]['x'] = $in_start_x - 5;

                    $in_start_x += $step_width_x;
                }

                if (strpos($anchor['id'], 'O') === 0) {
                    $this->data['nodes'][$i]['anchors'][$j]['y'] = DiagramView::$defaults[$node['type']]['h'] - 10;
                    $this->data['nodes'][$i]['anchors'][$j]['x'] = $out_start_x - 5;

                    $out_start_x += $step_width_x;
                }

                $this->addPort($node['id'], $anchor['id'], $this->data['nodes'][$i]['anchors'][$j]);
            }
        }

        /*
         * Correggo gli edge senza tipologia
         */
        foreach ($this->data['edges'] as $i => $edge) {
            list(, $targetAnchor) = explode('.', $edge['target']);
            if (substr($targetAnchor, 0, 1) == 'I') {
                if (!isset($this->data['edges'][$i]['data'])) {
                    $this->data['edges'][$i]['data'] = array();
                }

                $this->data['edges'][$i]['data']['type'] = 'connection';
            }
        }

        array_walk_recursive($this->data, function(&$item) {
            if (!mb_detect_encoding($item, 'UTF-8', true)) {
                $item = utf8_encode($item);
            }
        });

        return utf8_decode(json_encode($this->data));
    }

}
