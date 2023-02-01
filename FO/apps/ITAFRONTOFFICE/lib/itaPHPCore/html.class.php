<?php

require_once ITA_LIB_PATH . '/HtmlGenerator/Markup.php';
require_once ITA_LIB_PATH . '/HtmlGenerator/HtmlTag.php';

class html {

    private $html;

    const TREEVIEW_NORMAL = 0;
    const TREEVIEW_EXPANDED = 1;
    const TREEVIEW_ACCORDION = 2;

    function __construct($html = '') {
        $this->html = $html;

        \HtmlGenerator\Markup::$avoidXSS = true;
        \HtmlGenerator\Markup::$outputLanguage = ENT_HTML5;
        \HtmlGenerator\Markup::$inputEncoding = 'ISO-8859-1';
    }

    public function getHtml() {
        return $this->html;
    }

    public function setHtml($html) {
        $this->html = $html;
    }

    public function appendHtml($html) {
        $this->html .= $html . chr(10);
    }

    public function prependHtml($html) {
        $this->html = $html . chr(10) . $this->html;
    }

    public function openTag($tag, $attrs = array()) {
        $props = '';

        foreach ($attrs as $key => $value) {
            $props .= sprintf(' %s="%s"', $key, $value);
        }

        $this->appendHtml("<{$tag}{$props}>");
    }

    public function closeTag($tag) {
        $this->appendHtml("</{$tag}>");
    }

    public function addHidden($id, $value) {
        $div = \HtmlGenerator\HtmlTag::createElement('div')
            ->addElement('input')
            ->set('type', 'hidden')
            ->set('name', $id)
            ->set('id', $id)
            ->set('value', $value)
            ->addClass('ita-hidden');

        $this->appendHtml((string) $div);
    }

    public function addMsgInfo($title, $msg) {
        $div = \HtmlGenerator\HtmlTag::createElement('div')
            ->set('style', 'display: none;')
            ->set('title', $title)
            ->addClass('ita-alert')
            ->addElement('p')
            ->set('style', 'padding: 5px; color: red; font-size: 1.2em;')
            ->text($msg);

        $this->appendHtml((string) $div);
    }

    /*
     * Inizio implementazione nuove componenti.
     * * Accordion v (si integra nella treeview)
     * * Alert v
     * * Breadcrumb x
     * * Bullets
     * * Button v
     * * Callout
     * * Carousel
     * * Cookiebar
     * * Datepicker x
     * * Dialog x
     * * Dropdown x
     * * Entrypoint
     * * Grid
     * * Heading
     * * Hero
     * * Leads
     * * Linklist
     * * Megamenu
     * * Navscroll
     * * Offcanvas
     * * Pager
     * * Separator
     * * Skiplinks
     * * Table v
     * * Timeline
     * * Tooltip x
     * * Treeview v
     * 
     * + Input v
     * + Paragraph
     * + Br v
     */

    private function jsonAttr($array) {
        return str_replace('"', "'", json_encode($array, JSON_HEX_APOS));
    }

    /**
     * Aggiunge un messaggio.
     * 
     * @param type $message Il testo del messaggio.
     * @param type $title Il titolo del messaggio.
     * @param type $type Il tipo del messaggio. (info, success, warning, error)
     */
    public function addAlert($message, $title = '', $type = 'info') {
        $this->appendHtml(call_user_func_array(array($this, 'getAlert'), func_get_args()));
    }

    /**
     * Aggiunge un break di linea.
     * @param type $repeat Numero di br da aggiungere.
     */
    public function addBr($repeat = 1) {
        $this->appendHtml(call_user_func_array(array($this, 'getBr'), func_get_args()));
    }

    /**
     * Aggiunge un bottone.
     * 
     * @param type $text Testo del bottone.
     * @param type $href Se presente, il collegamento a cui far puntare il bottone.
     * @param type $style Lo stile del bottone. (default | primary | secondary )
     * @param type $ajax
     */
    public function addButton($text, $href = false, $style = 'default', $ajax = array(), $closeCurrentDialog = false) {
        $this->appendHtml(call_user_func_array(array($this, 'getButton'), func_get_args()));
    }

    /**
     * Aggiunge un'immagine.
     * 
     * @param type $src Indirizzo di origine dell'immagine.
     * @param type $size Dimensioni dell'immagine. Può essere un valore singolo
     * per H e W o un array con i due parametri distinti.
     * @param type $title Titolo dell'immagine.
     * @param type $href Eventuale collegamento a cui far puntare l'immagine.
     * @param type $target Target del collegamento. (es. _blank)
     */
    public function addImage($src, $size = 'auto', $title = '', $href = '', $target = '') {
        $this->appendHtml(call_user_func_array(array($this, 'getImage'), func_get_args()));
    }

    public function addInput($type = 'text', $label = '', $attrs = array(), $options = array()) {
        $this->appendHtml(call_user_func_array(array($this, 'getInput'), func_get_args()));
    }

    public function addLink($text, $href = false, $ajax = array()) {
        $this->appendHtml(call_user_func_array(array($this, 'getLink'), func_get_args()));
    }

    public function addForm($action = false, $method = 'GET', $attrs = array(), $ajax = false) {
        $this->appendHtml(call_user_func_array(array($this, 'getForm'), func_get_args()));
    }

    public function addScript($script) {
        $this->appendHtml(call_user_func_array(array($this, 'getScript'), func_get_args()));
    }

    public function addJSRedirect($uri, $timeout = 0, $waitWindow = true) {
        $this->appendHtml(call_user_func_array(array($this, 'getJSRedirect'), func_get_args()));
    }

    public function addJSWindow($uri) {
        $this->appendHtml(call_user_func_array(array($this, 'getJSWindow'), func_get_args()));
    }

    public function addSubmit($text, $name = '') {
        $this->appendHtml(call_user_func_array(array($this, 'getSubmit'), func_get_args()));
    }

    public function addTable($array, $options = array()) {
        $this->appendHtml(call_user_func_array(array($this, 'getTable'), func_get_args()));
    }

    public function addTreeView($array, $mode = self::TREEVIEW_NORMAL) {
        $this->appendHtml(call_user_func_array(array($this, 'getTreeView'), func_get_args()));
    }

    public function getAlert($message, $title = '', $type = 'info') {
        $div = \HtmlGenerator\HtmlTag::createElement('div')
            ->addClass("italsoft-alert italsoft-alert--$type");

        if ($title) {
            $div
                ->addElement('h2')
                ->text($title);
        }

        \HtmlGenerator\Markup::$avoidXSS = false;
        $div
            ->addElement('p')
            ->text($message);
        \HtmlGenerator\Markup::$avoidXSS = true;

        return (string) $div;
    }

    public function getBr($repeat = 1) {
        return str_repeat('<br />', $repeat);
    }

    public function getButton($text, $href = false, $style = 'default', $ajax = array(), $closeCurrentDialog = false) {
        $button = \HtmlGenerator\HtmlTag::createElement('button');

        if (false !== $href) {
            $button = \HtmlGenerator\HtmlTag::createElement('a')
                ->set('href', $href);
        }

        if ($style) {
            if (is_string($style)) {
                $button->addClass("italsoft-button--$style");
            } else if (is_array($style)) {
                foreach ($style as $name) {
                    $button->addClass("italsoft-button--$name");
                }
            }

            $button->addClass('italsoft-button');
        }

        if (count($ajax)) {
            $additionalOnClick = '';
            if ( $closeCurrentDialog ) {
                $additionalOnClick .= 'if ( $(this).closest(\'.ui-dialog-content\').length ) { $(this).closest(\'.ui-dialog-content\').dialog(\'close\'); }';
            }

            $event = isset($ajax['event']) ? $ajax['event'] : 'onClick';
            $button->set('onclick', "itaFrontOffice.ajax(ajax.action, ajax.model, '$event', this, " . $this->jsonAttr($ajax) . "); event.preventDefault(); $additionalOnClick");
        }

        \HtmlGenerator\Markup::$avoidXSS = false;

        $button->text($text);

        \HtmlGenerator\Markup::$avoidXSS = true;

        return (string) $button;
    }

    public function getImage($src, $size = 'auto', $title = '', $href = '', $target = '') {
        $width = $height = $size;
        if (is_array($size)) {
            list($width, $height) = $size;
        }

        $img = \HtmlGenerator\HtmlTag::createElement('img')
            ->addClass('italsoft-image')
            ->set('src', $src)
            ->set('style', "width: $width; height: $height; vertical-align: middle; margin: 0 .35em;");

        if ($href) {
            $a = \HtmlGenerator\HtmlTag::createElement('a')
                ->set('href', $href);

            $a->addElement($img);

            if ($target) {
                $a->set('target', $target);
            }

            $img = $a;
        }

        if ($title) {
            $img->set('title', $title);
        }

        return (string) $img;
    }

    public function getInput($type = 'text', $label = '', $attrs = array(), $options = array()) {
        $input_id = isset($attrs['id']) ? $attrs['id'] : 'input-' . rand(10000, 99999);

        $div = \HtmlGenerator\HtmlTag::createElement('div')
            ->addClass('italsoft-input-field');

        $lbl = \HtmlGenerator\HtmlTag::createElement();
        $input = \HtmlGenerator\HtmlTag::createElement();

        if (
            !isset($attrs['value']) &&
            isset($attrs['name']) &&
            ($value = frontOfficeApp::$cmsHost->getRequest($attrs['name']))
        ) {
            $attrs['value'] = $value;
        }

        if ($label) {
            if (is_string($label)) {
                $lbl = \HtmlGenerator\HtmlTag::createElement('label')
                    ->set('for', $input_id)
                    ->text($label);
            } else if (is_array($label)) {
                $lbl = \HtmlGenerator\HtmlTag::createElement('label')
                    ->set('for', $input_id);

                foreach ($label as $attr => $value) {
                    switch ($attr) {
                        case 'text':
                            $lbl->text($value);
                            break;

                        case 'class':
                            $lbl->addClass($value);
                            break;
                        
                        case 'style':                            
                            $lbl->set('style', $value);
                            break;
                        
                        default:
                            $lbl->set($value);
                            break;
                    }
                }
            }
        }

        switch ($type) {
            case 'checkbox':
                $value = $attrs['value'];
                $attrs['value'] = '1';

                if ($value == '1') {
                    $attrs['checked'] = true;
                }
            default:
            case 'text':
            case 'password':
            case 'radio':
            case 'hidden':
                if ($type === 'hidden') {
                    $div->removeClass('italsoft-input-field');
                }

                $input = \HtmlGenerator\HtmlTag::createElement('input')
                    ->set('type', $type);
                break;

            case 'select':
                $input = \HtmlGenerator\HtmlTag::createElement('select');

                foreach ($options as $key => $value) {
                    $option = $input
                        ->addElement('option')
                        ->set('value', $key)
                        ->text($value);

                    if (isset($attrs['value']) && $key == $attrs['value']) {
                        $option->set('selected', true);
                    }
                }
                break;

            case 'textarea':
                $input = \HtmlGenerator\HtmlTag::createElement('textarea');
                break;
        }

        if (isset($attrs['class'])) {
            $input->addClass($attrs['class']);
            unset($attrs['class']);
        }

        foreach ($attrs as $attribute => $value) {
            $input->set($attribute, $value);
        }

        $input
            ->set('id', $input_id)
            ->addClass('italsoft-input');

        switch ($type) {
            case 'datepicker':
                $input->addClass('italsoft-input--datepicker');
                break;
        }

        if (!in_array($type, array('checkbox', 'radio'))) {
            $div->addElement($lbl);
            $div->addElement($input);
        } else {
            $div->addElement($input);
            $div->addElement($lbl);
            $div->addClass('italsoft-input-field--boxed');
        }

        return (string) $div;
    }

    public function getLink($text, $href = false, $ajax = array()) {
        $link = \HtmlGenerator\HtmlTag::createElement('a');

        if (false !== $href) {
            $link->set('href', $href);
        }

        if (count($ajax)) {
            $event = isset($ajax['event']) ? $ajax['event'] : 'onClick';
            $link->set('onclick', "itaFrontOffice.ajax(ajax.action, ajax.model, '$event', this, " . $this->jsonAttr($ajax) . "); event.preventDefault();");
        }

        \HtmlGenerator\Markup::$avoidXSS = false;

        $link->text($text);

        \HtmlGenerator\Markup::$avoidXSS = true;

        return (string) $link;
    }

    public function getForm($action = false, $method = 'GET', $attrs = array(), $ajax = false) {
        $form = \HtmlGenerator\HtmlTag::createElement('form')
            ->addClass('italsoft-form');

        if ($method) {
            $form->set('method', $method);
        }

        if ($action === false) {
            $action = $_SERVER['REQUEST_URI'];
        }

        foreach ($attrs as $k => $v) {
            switch ($k) {
                case 'class':
                    $form->addClass($v);
                    break;

                default:
                    $form->set($k, $v);
                    break;
            }
        }

        if ($ajax === true) {
            $form->set('data-ajax', true);
        } else if ($ajax) {
            $form->set('data-ajax', $ajax);
        }

        $form->set('action', strtok($action, '?'));

        $returnHtml = str_replace('</form>', '', (string) $form);

        $queryParts = array();

        $actionQuery = parse_url($action, PHP_URL_QUERY);
        parse_str($actionQuery, $queryParts);
        foreach ($queryParts as $key => $value) {
            $returnHtml .= $this->getInput('hidden', '', array(
                'id' => $key,
                'name' => $key,
                'value' => $value
            ));
        }

        return $returnHtml;
    }

    public function getScript($js) {
        \HtmlGenerator\Markup::$avoidXSS = false;
        $script = \HtmlGenerator\HtmlTag::createElement('script')
            ->set('type', 'text/javascript')
            ->text($js);
        \HtmlGenerator\Markup::$avoidXSS = true;
        return (string) $script;
    }

    public function getJSRedirect($uri, $timeout = 0, $waitWindow = true) {
        $milliseconds = $timeout * 1000;
        if ($waitWindow) {
            return $this->getScript("jQuery( window ).load( function() { setTimeout(function() { window.location.replace('$uri'); }, $milliseconds); } );");
        } else {
            return $this->getScript("setTimeout(function() { window.location.replace('$uri'); }, $milliseconds);");
        }
    }

    public function getJSWindow($uri) {
        return $this->getScript("window.open('$uri', '', 'height=400, width=600, title=1, top=0, left=0, toolbar=yes, status=yes, scrollbars=yes, location=no, menubar=no, directories=no, resizable=yes');");
    }

    public function getSubmit($text, $name = '') {
        $button = \HtmlGenerator\HtmlTag::createElement('button')
            ->addClass('italsoft-button')
            ->set('type', 'submit');

        if ($name) {
            $button->set('name', $name);
        }

        $button->text($text);

        return (string) $button;
    }

    public function getTable($array, $options = array()) {
        if (!function_exists('html_table_elabora_tr')) {

            function html_table_elabora_tr($dataArray, &$tr, $record, $section) {
                $element = $section === 'body' ? 'td' : 'th';

                foreach ($record as $i => $column) {
                    \HtmlGenerator\Markup::$avoidXSS = false;

                    if (!is_array($column)) {
                        $td = $tr
                            ->addElement($element)
                            ->text($column);
                    } else {
                        $td = $tr
                            ->addElement($element)
                            ->text($column['text']);

                        if (isset($column['attrs']) && is_array($column['attrs'])) {
                            foreach ($column['attrs'] as $key => $value) {
                                $td->set($key, $value);
                            }
                        }
                    }

                    if (isset($dataArray['style'][$section][$i]) && $dataArray['style'][$section][$i]) {
                        $td->set('style', $dataArray['style'][$section][$i]);
                    }

                    \HtmlGenerator\Markup::$avoidXSS = true;
                }
            }

        }

        $table = \HtmlGenerator\HtmlTag::createElement('table')
            ->addClass('italsoft-table');

        $params = array_merge(array(
            'sortable' => false,
            'paginated' => false,
            'filters' => false,
            'ajax' => false,
            'selectable' => false,
            'attrs' => array()
            ), $options);

        if ($params['sortable']) {
            $table->addClass('italsoft-table--sortable');
        }

        if ($params['paginated']) {
            $table->addClass('italsoft-table--paginated');
        }

        if ($params['filters']) {
            $table->addClass('italsoft-table--filters');
        }

        if ($params['stickyHeaders']) {
            $table->addClass('italsoft-table--stickyheaders');
        }

        if ($params['buttonAdd']) {
            $table->addClass('italsoft-table--button-add');
        }

        if ($params['buttonEdit']) {
            $table->addClass('italsoft-table--button-edit');
        }

        if ($params['buttonDel']) {
            $table->addClass('italsoft-table--button-del');
        }

        if ($params['ajax']) {
            $table->set('data-ajax', true);
        }

        if ($params['selectable']) {
            $table->set('data-selectable', json_encode($params['selectable']));
        }

        if ($params['sort-column']) {
            $table->set('data-sort-column', $params['sort-column']);
            $table->set('data-sort-order', isset($params['sort-order']) ? $params['sort-order'] : 'asc');
        }

        foreach ($params['attrs'] as $key => $value) {
            $table->set($key, $value);
        }

        if (isset($array['caption'])) {
            $table
                ->addElement('caption')
                ->text($array['caption']);
        }

        if (isset($array['header'])) {
            ksort($array['header']);

            if (count($array['header']) >= 8) {
                $table->addClass('italsoft-table--large');
            }

            $tr = $table
                ->addElement('thead')
                ->addElement('tr');

            html_table_elabora_tr($array, $tr, $array['header'], 'header');
        }

        if (isset($array['body'])) {
            $tbody = $table->addElement('tbody');

            foreach ($array['body'] as $key => $record) {
                $tr = $tbody
                    ->addElement('tr')
                    ->set('data-key', $key);

                html_table_elabora_tr($array, $tr, $record, 'body');
            }
        }

        return (string) $table;
    }

    public function getTreeView($array, $mode = self::TREEVIEW_NORMAL) {
        if (!function_exists('html_treeview_recursive')) {

            function html_treeview_recursive($childs, $mode, $is_root = true, $is_active = false) {
                $ul = \HtmlGenerator\HtmlTag::createElement('ul');

                if ($is_root) {
                    $ul->addClass('italsoft-treeview');

                    if ($mode === html::TREEVIEW_ACCORDION) {
                        $ul->addClass('italsoft-treeview--accordion');
                    }
                } else if (!$is_active) {
                    $ul->set('style', 'display: none;');
                }

                foreach ($childs as $label => $params) {
                    /*
                     * Variabili utili per il calcolo del disegno.
                     */
                    $has_buttons = isset($params['buttons']);
                    $has_childs = (isset($params['childs']) && count($params['childs']));
                    $is_active = ($has_childs && isset($params['active']) && $params['active'] == true);
                    $is_link = isset($params['href']);
                    $is_ajax = isset($params['ajax']);
                    if (isset($params['label'])) {
                        $label = $params['label'];
                    }

                    $li = $ul
                        ->addElement('li')
                        ->addClass('italsoft-treeview-item');

                    if ($mode === html::TREEVIEW_ACCORDION) {
                        $mode = html::TREEVIEW_NORMAL;
//                        $is_active = true;
                    }

                    if ($mode === html::TREEVIEW_EXPANDED && $is_root) {
                        $is_active = true;
                    }

                    if ($is_active) {
                        $li->addClass('italsoft-treeview-item--open');
                    }

                    $content = $li->addElement('div');

                    if ($is_link) {
                        $text = $content
                            ->addElement('a')
                            ->set('href', $params['href']);
                    } else {
                        $text = $content
                            ->addElement('div');
                    }

                    $text->addClass('italsoft-treeview-text');

                    if (isset($params['title'])) {
                        $text->set('title', $params['title']);
                    }

                    if ($has_buttons || $has_childs || $is_link || $is_ajax) {
                        $text->addClass('italsoft-treeview-text--selectable');
                    }

                    /*
                     * Calcolo del padding per i button laterali.
                     * (+4em per ogni button)
                     */
                    if ($has_buttons || $has_childs) {
                        $padding = 1;

                        if ($has_buttons) {
                            $padding += (count($params['buttons']) * 4);
                        }

                        if ($has_childs) {
                            $padding += 4;
                        }

                        $text->set('style', "padding-right: {$padding}em;");
                    }

                    \HtmlGenerator\Markup::$avoidXSS = false;
                    $text->text($label);
                    \HtmlGenerator\Markup::$avoidXSS = true;

                    $btngroup = $content
                        ->addElement('span')
                        ->addClass('italsoft-treeview-button-group');

                    /*
                     * Button di dropdown per apertura.
                     */
                    if ($has_childs || $is_ajax) {
                        $btn = $btngroup
                            ->addElement('span')
                            ->addClass('italsoft-treeview-button')
                            ->addClass('italsoft-treeview-button--expand');

                        if ($is_ajax) {
                            $btn->set('data-ajax', $params['ajax']);
                        }
                    }

                    /*
                     * Button aggiuntivi.
                     */
                    if ($has_buttons) {
                        foreach ($params['buttons'] as $button) {
                            $btn = $btngroup
                                ->addElement('a')
                                ->addClass('italsoft-treeview-button')
                                ->set('href', $button['href'])
                                ->set('title', $button['title']);

                            if (isset($button['target'])) {
                                $btn->set('target', $button['target']);
                            }

                            if (isset($button['icon'])) {
                                $btn
                                    ->addElement('i')
                                    ->addClass('ionic')
                                    ->addClass($button['icon'])
                                    ->addClass('italsoft-icon');
                            } else if (isset($button['image'])) {
                                $btn
                                    ->addElement('img')
                                    ->addClass('italsoft-image')
                                    ->set('src', $button['image'])
                                    ->set('style', 'width: 24px; height: 24px;');
                            }

                            if (isset($button['label'])) {
                                $btn->addElement('br');
                                $btn
                                    ->addElement('span')
                                    ->text($button['label']);
                            }
                        }
                    }

                    /*
                     * Aggiunta di eventuali sottomenù.
                     */
                    if ($has_childs) {
                        $li->addElement(html_treeview_recursive($params['childs'], $mode, false, $is_active));
                    }
                }

                return $ul;
            }

        }

        return (string) html_treeview_recursive($array, $mode);
    }

}
