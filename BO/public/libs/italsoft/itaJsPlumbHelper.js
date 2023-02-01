var itaJsPlumbHelper = (function () {
    function getSelectedNodes(id) {
        var selectedNodes = [];

        $(jsPlumbInstances[id].getSelection().getAll()).each(function () {
            if (this.objectType !== 'Node' || this.data.type === 'edgeBreak') {
                return;
            }

            selectedNodes.push(this.data.id);
        });

        return selectedNodes;
    }

    function getActionBarButton(id, icon, text) {
        return $('<button type="button" id="' + id + '" name="' + id + '" class="ita-button ita-element-animate { iconLeft: \'ui-icon ' + icon + '\' }" style="width: 100%; padding: 3px 3px; margin: 0 0 4px;" value="' + text + '"></button>');
    }

    function getDistance(x1, y1, x2, y2) {
        return Math.abs(x1 - x2) + Math.abs(y1 - y2);
    }

    function getAnchorPosition(x, y, w, h, node) {
        if (x < 0) {
            x = 0;
        } else if (x > w) {
            x = w;
        }

        if (y < 0) {
            y = 0;
        } else if (y > h) {
            y = h;
        }

        if ($(node).is('.flowchart-question')) {
            var t = getDistance(x, y, w / 2, 0),
                r = getDistance(x, y, w, h / 2),
                b = getDistance(x, y, w / 2, h),
                l = getDistance(x, y, 0, h / 2),
                arrValues = [t, r, b, l],
                m = Math.min.apply(Math, arrValues);

            switch (m) {
                case arrValues[0]:
                    x = w / 2;
                    y = 0;
                    break;

                case arrValues[1]:
                    x = w;
                    y = h / 2;
                    break;

                case arrValues[2]:
                    x = w / 2;
                    y = h;
                    break;

                case arrValues[3]:
                    x = 0;
                    y = h / 2;
                    break;
            }

            return {left: x, top: y};
        }

        if (x > 0 && x < w && y > 0 && y < w) {
            /*
             * Trovo qual è il punto più vicino
             */

            var arrValues = [x, w - x, y, h - y],
                m = Math.min.apply(Math, arrValues);

            switch (m) {
                case arrValues[0]:
                    x = 0;
                    break;

                case arrValues[1]:
                    x = w;
                    break;

                case arrValues[2]:
                    y = 0;
                    break;

                case arrValues[3]:
                    y = h;
                    break;
            }
        }

        x = Math.round(x / 10) * 10;
        y = Math.round(y / 10) * 10;

        return {left: x, top: y};
    }

    function updateNodeAnchor(e, ui) {
        if (e.type === 'dragstop') {
            return;
        }

        var id = $(this).closest('.ita-flowchart').attr('id'),
            node = this.parentNode,
            info = jsPlumbInstances.Renderers[id].getObjectInfo(this),
            data = info.obj.data,
            w = node.clientWidth - 10,
            h = node.clientHeight - 10;

        /*
         * Correggo la posizione a seconda dello zoom
         */
        var scale = parseFloat(node.parentNode.style.transform.replace(/[^0-9.]/g, ''));

        ui.position.left /= scale;
        ui.position.top /= scale;

        ui.position = getAnchorPosition(ui.position.left, ui.position.top, w, h, node);

        data.x = ui.position.left;
        data.y = ui.position.top;

        jsPlumbInstances[id].updatePort(info.obj, data);
        jsPlumbInstances.Renderers[id].repaint(info.obj.getNode());
    }

    function repositionNodeAnchor(ui) {
        var id = $(this).closest('.ita-flowchart').attr('id'),
            node = this.parentNode,
            info = jsPlumbInstances.Renderers[id].getObjectInfo(this),
            data = info.obj.data,
            w = node.clientWidth - 10,
            h = node.clientHeight - 10;

        ui.position = getAnchorPosition(ui.position.left, ui.position.top, w, h, node);

        data.x = ui.position.left;
        data.y = ui.position.top;

        jsPlumbInstances[id].updatePort(info.obj, data);
        jsPlumbInstances.Renderers[id].repaint(info.obj.getNode());
    }

    function selectEdgeEndpoint(endpoint, toolkit) {
        if (endpoint.objectType == 'Node') {
            var $node = $('[data-jtk-node-id="' + endpoint.data.id + '"]');

            if ($node.is('.ita-jtk-selected')) {
                return;
            }

            $('[data-jtk-node-id="' + endpoint.data.id + '"]').addClass('ita-jtk-selected');

            var edges = endpoint.getEdges();
            for (var i in edges) {
                toolkit.addToSelection(edges[i]);

                if (edges[i].target.data.id != endpoint.data.id) {
                    selectEdgeEndpoint(edges[i].target, toolkit);
                }

                if (edges[i].source.data.id != endpoint.data.id) {
                    selectEdgeEndpoint(edges[i].source, toolkit);
                }
            }
        }

        if (endpoint.objectType == 'Port') {
            var node = endpoint.getNode();

            $('[data-jtk-node-id="' + node.data.id + '"] [data-port-id="' + endpoint.data.id + '"]').addClass('ita-jtk-selected');
        }
    }

    function edgeTapFunction(toolkit, params) {
        toolkit.clearSelection();
        $('.ita-jtk-selected').removeClass('ita-jtk-selected');

        toolkit.addToSelection(params.edge);
        itaJsPlumbHelper.selectEdgeEndpoint(params.edge.source, toolkit);
        itaJsPlumbHelper.selectEdgeEndpoint(params.edge.target, toolkit);
    }

    function edgeContextmenuFunction(toolkit, params) {
        if ($(params.e.target).is('.jtk-overlay')) {
            $middlePointTarget = $(params.e.target);
        } else {
            $middlePointTarget = $(params.e.target).closest('svg');
        }

        var halfNodeSize = 10 / 2,
            midX = parseInt($middlePointTarget.css('left')) + ($middlePointTarget.width() / 2),
            midY = parseInt($middlePointTarget.css('top')) + ($middlePointTarget.height() / 2);

        var node = toolkit.addNode({
            left: midX - halfNodeSize,
            top: midY - halfNodeSize,
            type: 'edgeBreak'
        });

        toolkit.connect({
            source: params.edge.source,
            target: node
        });

        toolkit.connect({
            source: node,
            target: params.edge.target,
            data: params.edge.data
        });

        toolkit.removeEdge(params.edge);

        itaJsPlumbHelper.selectEdgeEndpoint(node, toolkit);

        params.e.preventDefault();
        return false;
    }

    function setPan(id, x, y) {
        if ($(protSelector('#' + id)).is(':visible')) {
            jsPlumbInstances.Renderers[id].setPan(x, y);
        } else {
            if (!jsPlumbInstances.ViewInfo[id]) {
                jsPlumbInstances.ViewInfo[id] = {};
            }

            jsPlumbInstances.ViewInfo[id].pan = [x, y];
        }
    }

    function setZoom(id, zoom) {
        if ($(protSelector('#' + id)).is(':visible')) {
            jsPlumbInstances.Renderers[id].setZoom(zoom);
        } else {
            if (!jsPlumbInstances.ViewInfo[id]) {
                jsPlumbInstances.ViewInfo[id] = {};
            }

            jsPlumbInstances.ViewInfo[id].zoom = zoom;
        }
    }

    function activate(id) {
        if (typeof jsPlumbInstances[id] === 'undefined') {
            return;
        }

        jsPlumbInstances.Renderers[id].refresh();
        jsPlumbInstances.Renderers[id].getMiniview().invalidate();

        if (!jsPlumbInstances.ViewInfo[id]) {
            var selectedNodes = jsPlumbInstances[id].getSelection().getNodes();

            /*
             * Il center viene effettuato due volte in quanto
             * al primo comando non viene effettivamente eseguito.
             */

            if (selectedNodes.length) {
                jsPlumbInstances.Renderers[id].centerOn(selectedNodes[0]);
                jsPlumbInstances.Renderers[id].centerOn(selectedNodes[0]);
            } else {
                jsPlumbInstances.Renderers[id].centerContent();
                jsPlumbInstances.Renderers[id].centerContent();
            }
            return;
        }

        if (jsPlumbInstances.ViewInfo[id].pan) {
            jsPlumbInstances.Renderers[id].setPan(jsPlumbInstances.ViewInfo[id].pan[0], jsPlumbInstances.ViewInfo[id].pan[1]);
            delete jsPlumbInstances.ViewInfo[id].pan;
        }

        if (jsPlumbInstances.ViewInfo[id].zoom) {
            jsPlumbInstances.Renderers[id].setZoom(jsPlumbInstances.ViewInfo[id].zoom);
            delete jsPlumbInstances.ViewInfo[id].pan;
        }
    }

    function clearCanvasSelection(toolkit, flowchart) {
        var selectedNodes = toolkit.getSelection().getNodes();

        if (selectedNodes.length) {
            itaGo('ItaForm', flowchart, {
                event: 'onClick',
                validate: true,
                selectionNode: selectedNodes[0].data.id,
                selectionAction: 0
            });
        }

        toolkit.clearSelection();
        $('.ita-jtk-selected').removeClass('ita-jtk-selected');
    }

    function showSelectionButtons(selectionButtons) {
        for (var i in selectionButtons) {
            selectionButtons[i].show();
        }
    }

    function hideSelectionButtons(selectionButtons) {
        for (var i in selectionButtons) {
            selectionButtons[i].hide();
        }
    }

    function setPathPaintStyle(toolkit, source, target, color) {
        if (source.indexOf('.') === -1) {
            source = toolkit.getNode(source).getPorts()[0];
        }

        if (target.indexOf('.') === -1) {
            target = toolkit.getNode(target).getPorts()[0];
        }

        var path = toolkit.getPath({source: source, target: target});

        path.eachEdge(function (i, edge) {
            var data = edge.data;
            data.strokeColor = color;

            toolkit.connect({
                source: edge.source,
                target: edge.target,
                data: data
            });

            toolkit.removeEdge(edge);
        });
    }

    function getEdgeBreakFinalNode(id, edgeBreak, direction) {
        var edges = jsPlumbInstances[id].getNode(edgeBreak).getAllEdges();

        for (var i in edges) {
            if (edges[i][direction].objectType === 'Node' && edges[i][direction].data.type === 'edgeBreak' && edges[i][direction].id !== edgeBreak.id) {
                return getEdgeBreakFinalNode(id, edges[i][direction], direction);
            }

            if (edges[i][direction].objectType === 'Port') {
                return edges[i][direction].getNode();
            }
        }

        return false;
    }

    function setVisible(element, visible, diagramId) {
        var edgeNode,
            renderer = jsPlumbInstances.Renderers[diagramId];

        renderer.setVisible(element, visible);

        var edges = jsPlumbInstances[diagramId].getNode(element).getAllEdges();

        for (var i in edges) {
            for (var x = 0; x < 2; x++) {
                var direction = x === 0 ? 'source' : 'target';

                if (edges[i][direction].objectType === 'Node' && edges[i][direction].data.type === 'edgeBreak') {
                    /*
                     * Se il nodo dall'altra parte è nascosto, non procedo in caso di setVisible a true.
                     */

                    edgeNode = getEdgeBreakFinalNode(diagramId, edges[i][direction], direction);
                    
                    if (!renderer.isVisible(edgeNode) && visible == true) {
                        continue;
                    }

                    if (renderer.isVisible(edges[i][direction]) == visible) {
                        continue;
                    }

                    setVisible(edges[i][direction], visible, diagramId, true);
                }
            }
        }

        if (!arguments[3] && visible) {
            jsPlumbInstances.Renderers[diagramId].refresh();
        }
    }

    return {
        getActionBarButton: getActionBarButton,
        getSelectedNodes: getSelectedNodes,
        updateNodeAnchor: updateNodeAnchor,
        repositionNodeAnchor: repositionNodeAnchor,
        selectEdgeEndpoint: selectEdgeEndpoint,
        edgeTapFunction: edgeTapFunction,
        edgeContextmenuFunction: edgeContextmenuFunction,
        setPan: setPan,
        setZoom: setZoom,
        activate: activate,
        clearCanvasSelection: clearCanvasSelection,
        showSelectionButtons: showSelectionButtons,
        hideSelectionButtons: hideSelectionButtons,
        setPathPaintStyle: setPathPaintStyle,
        setVisible: setVisible
    };
})();