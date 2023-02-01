<?php

require_once ITA_LIB_PATH . '/PHP_XLSXWriter/xlsxwriter_customized.class.php';

class itaXlsxWriter {

    const FORMAT_STRING = 'string';
    const FORMAT_INTEGER = 'integer';
    const FORMAT_DECIMAL2 = '#,##0.00';
    const FORMAT_DECIMAL5 = '#,##0.00000';
    const FORMAT_DATE = 'DD/MM/YYYY';
    const FORMAT_DATETIME = 'datetime';
    const FORMAT_PRICE = 'price';
    const FORMAT_DOLLAR = 'dollar';
    const FORMAT_EURO = 'euro';
    const STYLE_FONT = 'font';
    const STYLE_FONTSIZE = 'font-size';
    const STYLE_FONTSTYLE = 'font-style';
    const STYLE_BORDER = 'border';
    const STYLE_BORDERSTYLE = 'border-style';
    const STYLE_BORDERCOLOR = 'border-color';
    const STYLE_COLOR = 'color';
    const STYLE_FILL = 'fill';
    const STYLE_HALIGN = 'halign';
    const STYLE_VALIGN = 'valign';
    const STYLE_WRAPTEXT = 'wrap_text';
    const ORDER_ASC = 1;
    const ORDER_DESC = -1;

    private $xslxWriter;
    private $db;
    private $data;
    private $metadata;
    private $customHeaders;
    private $customFooter;
    private $sheetNames;
    private $sheetOrder;

    public function __construct($db = null) {
        $this->db = $db;
    }

    /**
     * Setta i dati da stampare sull'xlsx da una query
     * @param <string> $sql
     * @param <array> $sqlParams
     * @param <int> $sheet (Opzionale) Forza i dati come destinati ad un certo foglio
     * @throws <ItaException>
     */
    public function setDataFromSQL($sql, $sqlParams = array(), $sheet = null) {
        if (!isSet($this->db)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non è stato trasmesso il db da cui recuperare i dati');
        } else {
            $params = array();
            if (is_array(reset($sqlParams))) {
                foreach ($sqlParams as $value) {
                    if ($value['type'] != 1) {
                        $params[':' . $value['name']] = "'" . $value['value'] . "'";
                    } else {
                        $params[':' . $value['name']] = $value['value'];
                    }
                }
            } else {
                foreach ($sqlParams as $key => $value) {
                    $params[':' . $key] = "'" . $value . "'";
                }
            }

            $keys = array_keys($params);
            usort($keys, 'self::sortByLen');

            $values = array();
            foreach ($keys as $key) {
                $values[] = $params[$key];
            }

            $sql = str_replace($keys, $values, $sql);

            $data = ItaDB::DBSQLSelect($this->db, $sql);


            if (!is_array($this->data)) {
                $this->data = array();
            }

            if (!isSet($sheet)) {
                if (!empty($this->sheetNames) && is_array($this->sheetNames)) {
                    $sheet = reset($this->sheetNames);
                } else {
                    $sheet = 'Sheet1';
                    $this->sheetNames = array('Sheet1');
                }
            }

            $this->data[$sheet] = $data;
        }
    }

    /**
     * Setta i dati da stampare sull'xlsx da un array
     * @param <array> $data
     * @param <int> $sheet (Opzionale) Forza i dati come destinati ad un certo foglio
     */
    public function setDataFromArray($data, $sheet = null) {
        if (!is_array($this->data)) {
            $this->data = array();
        }

        if (!isSet($sheet)) {
            if (!empty($this->sheetNames) && is_array($this->sheetNames)) {
                $sheet = reset($this->sheetNames);
            } else {
                $sheet = 'Sheet1';
                $this->sheetNames = array('Sheet1');
            }
        }

        $this->data[$sheet] = $data;
    }

    /**
     * Restituisce l'array dei dati
     * @return <array>
     */
    public function getData() {
        return $this->data;
    }

    /**
     * Permette di settare il nome di uno o più fogli.
     * @param <array> $sheetNames
     */
    public function setSheetNames($sheetNames) {
        $this->sheetNames = array_values($sheetNames);
    }

    /**
     * Permette di impostare degli header custom multiriga per ogni foglio
     * @param <string> $sheet Nome del foglio per il quale si vuole impostare l'header
     * @param <array> $header array di array, contiene le descrizioni dell'header. Es:
     *                $header = array(
     *                    array('Anno', 'Importi',   '',          '',          'Beneficiario'),
     *                    array('',     'Importo 1', 'Importo 2', 'Importo 3', '')
     *                );
     * @param <array> $style array di array che contiene lo stile per ogni campo di ogni riga. Usa le costanti itaXlsxWriter::STYLE_* Es:
     *                $style = array(
     *                    array(
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00', STYLE_FONTSTYLE=>'bold'),
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                    ),
     *                    array(
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                        array(itaXlsxWriter::STYLE_FILL=>'#FFFF00'),
     *                    )
     *                )
     * @param <array> $merge array di array che contiene le informazioni sulle celle da unire. Ogni array deve contenere le chiavi 'startRow', 'startCol', 'endRow', 'endCol'. Es:
     *                $merge = array(
     *                    array(
     *                        'startRow'=>0,
     *                        'startCol'=>0,
     *                        'endRow'=>1,
     *                        'endCol'=>0,
     *                    ),
     *                    array(
     *                        'startRow'=>0,
     *                        'startCol'=>1,
     *                        'endRow'=>0,
     *                        'endCol'=>3,
     *                    ),
     *                    array(
     *                        'startRow'=>0,
     *                        'startCol'=>4,
     *                        'endRow'=>1,
     *                        'endCol'=>4,
     *                    )
     *                )
     */
    public function setCustomHeader($sheet, $header, $style = null, $merge = null) {
        if (!isSet($this->customHeaders[$sheet])) {
            $this->customHeaders[$sheet] = array();
        }

        array_walk_recursive($header, function(&$v, $k) {
            $v = utf8_encode($v);
        });
        $this->customHeaders[$sheet]['header'] = $header;
        $this->customHeaders[$sheet]['style'] = (is_array($style) ? $style : array());
        $this->customHeaders[$sheet]['merge'] = (is_array($merge) ? $merge : array());
    }

    public function setCustomFooter($sheet, $footer, $style = null, $merge = null) {
        if (!isSet($this->customFooter[$sheet])) {
            $this->customFooter[$sheet] = array();
        }

        array_walk_recursive($footer, function(&$v, $k) {
            $v = utf8_encode($v);
        });
        $this->customFooter[$sheet]['footer'] = $footer;
        $this->customFooter[$sheet]['style'] = (is_array($style) ? $style : array());
        $this->customFooter[$sheet]['merge'] = (is_array($merge) ? $merge : array());
    }

    /**
     * Permette di settare i metadati per i singoli campi, prende un array di array nella seguente forma
     * @param <array> $metadata: array dei campi presenti. Ogni campo ha i seguenti metadati:
     *                          <string>    'name'          Descrizione testuale della colonna (quanto si vede sulla testata)
     *                          <integer>   'width'         Larghezza della colonna
     *                          <integer>   'sheet'         Pagina su cui inserire la colonna (parte da 0)
     *                          <string>    'format'        Tipo di dato, usare le costanti itaXlsxWriter::FORMAT_*
     *                          <string>    'orderBy'       Indica se i dati vanno ordinati per un dato campo. Prende itaXlsxWriter::ORDER_*
     *                          <array>     'headerStyle'   Stile dell'header, prende campi con chiave itaXlsxWriter::STYLE_*
     *                          <array>     'fieldStyle'    Stile del campo, prende campi con chiave itaXlsxWriter::STYLE_*
     *                          <string>    'calculated'    Formula per un campo calcolato
     *                          <array>     'callback'      Array contenente oggetto, funzione e dati aggiuntivi dell'oggetto da richiamare per calcolare il campo.
     *                                                      La funzione da implementare avrà la seguente firma: function($row, $data=null) dove:
     *                                                          $row = Dati grezzi della riga su cui aggiungere il campo
     *                                                          $data = Eventuale array di dati aggiuntivi da passare alla funzione
     * 
     *                  array(
     *                      'NOME_CAMPO'=>array(
     *                          'name'=>'Descrizione campo',
     *                          'width'=>200,
     *                          'sheet'=>0,
     *                          'format'=>itaXlsxWriter::FORMAT_STRING,
     *                          'headerStyle'=>array(
     *                              itaXlsxWriter::STYLE_COLOR=>'#FF0000',
     *                              itaXlsxWriter::STYLE_HALIGN=>'center',
     *                          ),
     *                          'fieldStyle'=>array(
     *                              ...
     *                          ),
     *                          ...
     *                      ),
     *                      'NOME_CAMPO_CALCOLATO'=>array(
     *                          'name'=>'Descrizione campo calcolato',
     *                          'width'=>100,
     *                          'calculated'=>'<CAMPO1> <CAMPO2>+<CAMPO3>'
     *                      ),
     *                      'NOME_CAMPO_FUNZIONE'=>array(
     *                          'name'=>'Descrizione campo calcolato',
     *                          'width'=>100,
     *                          'callback'=>array(
     *                              'object'=>$this,
     *                              'function'=>'calcolaImporto',
     *                              'data'=>array(
     *                                  0,
     *                                  'test'
     *                              )
     *                          ),
     *                          ...
     *                      ),
     *                      ...
     *                  )
     * @param <int> $sheet (Opzionale) Forza i metadati come destinati ad un certo foglio
     */
    public function setRenderFieldsMetadata($metadata, $sheet = null) {
        if (!isSet($sheet)) {
            $this->metadata = array();
        }
        foreach ($metadata as $k => $v) {
            if (isSet($sheet)) {
                $s = $sheet;
                unset($v['sheet']);
            } elseif (isSet($v['sheet'])) {
                $s = $v['sheet'];
                unset($v['sheet']);
            } else {
                $s = 'Sheet1';
            }

            $this->metadata[$s][$k] = $v;
        }
    }

    public function setRenderFieldsFromModel($progint, $sheet = null) {
        if (!isSet($this->db)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non è stato trasmesso il db da cui recuperare i dati');
        }
        $campi = ItaDB::DBSQLSelect($this->db, 'SELECT * FROM BGE_EXCELD WHERE PROGINT = ' . $progint . ' ORDER BY PROG_RIGA ASC');

        $metadata = array();
        foreach ($campi as $field) {
            $metadata[$field['NOMECAMPOE']] = json_decode($field['COL_META'], true);
            $metadata[$field['NOMECAMPOE']]['name'] = $field['NOMECOLON'];

            if (isSet($metadata[$field['NOMECAMPOE']]['callback']) && is_string($metadata[$field['NOMECAMPOE']]['callback']['object'])) {
                $model = $metadata[$field['NOMECAMPOE']]['callback']['object'];
                $appRoute = App::getPath('appRoute.' . substr($model, 0, 3));
                require_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $model . '.php';

                $metadata[$field['NOMECAMPOE']]['callback']['object'] = new $model();
            }
        }

        $this->setRenderFieldsMetadata($metadata, $sheet);
    }

    /**
     * Renderizza i dati passati in maniera grezza (comportamento legacy)
     * @throws <ItaException>
     */
    public function createRaw() {
        if (empty($this->data)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non sono presenti dati');
        }

        $this->xslxWriter = new XLSXWriter();
        if (!empty($this->sheetNames[0])) {
            $sheet = $this->sheetNames[0];
        } else {
            $sheet = 'Sheet1';
        }
        $header = array();
        foreach (array_keys($this->data[$sheet][0]) as $field) {
            $header[$field] = 'string';
        }
        $this->xslxWriter->writeSheetHeader($sheet, $header);
        foreach ($this->data[$sheet] as $row) {
            $this->xslxWriter->writeSheetRow($sheet, $row);
        }
    }

    /**
     * Renderizza i dati passati secondo i valori impostati con setRenderFieldsMetadata o setRenderFieldsFromModel
     * @throws <ItaException>
     */
    public function createCustom() {
        if (empty($this->data)) {
            throw ItaException::newItaException(ItaException::TYPE_ERROR_PHP, -1, 'Non sono presenti dati');
        }
        if (empty($this->metadata)) {
            $this->createRaw();
        } else {
            $this->xslxWriter = new XLSXWriter();
            $sheetHeaders = array();
            $sheetData = array();
            $sheetOrder = array();
            foreach ($this->metadata as $sheet => $fields) {
                if (isSet($this->data[$sheet])) {
                    $dataSource = $this->data[$sheet];
                } elseif (isSet($this->data['Sheet1'])) {
                    $dataSource = $this->data['Sheet1'];
                } else {
                    $dataSource = array();
                }
                foreach ($fields as $field => $metadata) {
                    if (empty($metadata['name']))
                        $metadata['name'] = $field;
                    if (!isSet($metadata['width']))
                        $metadata['width'] = 10;
                    if (!isSet($metadata['sheet']))
                        $metadata['sheet'] = 0;
                    if (!isSet($metadata['format']))
                        $metadata['format'] = null;
                    $sheetName = (isSet($this->sheetNames[$sheet]) ? $this->sheetNames[$sheet] : 'Foglio ' . ($sheet + 1));

                    if (!isSet($sheetHeaders[$sheet])) {
                        $sheetHeaders[$sheet] = array();
                        $sheetHeaders[$sheet]['sheet'] = $sheetName;
                        $sheetHeaders[$sheet]['style'] = array();
                        $sheetHeaders[$sheet]['style']['widths'] = array();
                        $sheetHeaders[$sheet]['fields'] = array();
                    }
                    if (!isSet($sheetData[$sheet])) {
                        $sheetData[$sheet] = array();
                        $sheetData[$sheet]['sheet'] = $sheetName;
                        $sheetData[$sheet]['style'] = array();
                        $sheetData[$sheet]['data'] = array();
                    }
                    if (!isSet($sheetOrder[$sheet])) {
                        $sheetOrder[$sheet] = array();
                    }

                    $style = $metadata['headerStyle'];
                    if (empty($style)) {
                        $style = array();
                    }
                    $sheetHeaders[$sheet]['style'][] = $style;
                    $sheetHeaders[$sheet]['style']['widths'][] = $metadata['width'];

                    $style = (empty($metadata['fieldStyle']) ? array() : $metadata['fieldStyle']);
                    $sheetData[$sheet]['style'][] = $style;
                    $type = $metadata['format'];
                    foreach ($dataSource as $k => $v) {
                        if (isSet($metadata['calculated'])) {
                            preg_match_all("/([+\-\/*]|'.*?'|<.*?>|%.*?%)/", $metadata['calculated'], $matches);

                            $result = '';
                            for ($i = 0; $i < count($matches[1]); $i++) {
                                $op1 = $matches[1][$i];
                                $op2 = $matches[1][$i + 2];
                                $operation = $matches[1][$i + 1];
                                if ($operation == '*' || $operation == '/') {
                                    if (strpos($op1, '<') === 0) {
                                        $op1 = floatval($v[substr($op1, 1, -1)]);
                                    } elseif (strpos($op1, '%') === 0) {
                                        $op1 = floatval($v[substr($op1, 1, -1)]);
                                    } elseif (strpos($op1, '\'') === 0) {
                                        $op1 = floatval(substr($op1, 1, -1));
                                    } else {
                                        $op1 = 1;
                                    }

                                    if (strpos($op2, '<') === 0) {
                                        $op2 = floatval($v[substr($op2, 1, -1)]);
                                    } elseif (strpos($op2, '%') === 0) {
                                        $op2 = floatval($v[substr($op2, 1, -1)]);
                                    } elseif (strpos($op2, '\'') === 0) {
                                        $op2 = floatval(substr($op2, 1, -1));
                                    } else {
                                        $op2 = 1;
                                    }

                                    if ($operation == '*') {
                                        $matches[1][$i] = $op1 * $op2;
                                    } else {
                                        $matches[1][$i] = $op1 / $op2;
                                    }
                                    unset($matches[1][$i + 1]);
                                    unset($matches[1][$i + 2]);
                                    $matches[1] = array_values($matches[1]);
                                    $i -= 1;
                                }
                            }
                            for ($i = 0; $i < count($matches[1]); $i++) {
                                $op1 = $matches[1][$i];
                                $op2 = $matches[1][$i + 2];
                                $operation = $matches[1][$i + 1];

                                if ($operation == '+' || $operation == '-') {
                                    if (strpos($op1, '<') === 0) {
                                        $op1 = floatval($v[substr($op1, 1, -1)]);
                                    } elseif (strpos($op1, '%') === 0) {
                                        $op1 = floatval($v[substr($op1, 1, -1)]);
                                    } elseif (strpos($op1, '\'') === 0) {
                                        $op1 = floatval(substr($op1, 1, -1));
                                    } elseif (!is_numeric($op1)) {
                                        $op1 = 0;
                                    }

                                    if (strpos($op2, '<') === 0) {
                                        $op2 = floatval($v[substr($op2, 1, -1)]);
                                    } elseif (strpos($op2, '%') === 0) {
                                        $op2 = floatval($v[substr($op2, 1, -1)]);
                                    } elseif (strpos($op2, '\'') === 0) {
                                        $op2 = floatval(substr($op2, 1, -1));
                                    } elseif (!is_numeric($op2)) {
                                        $op2 = 0;
                                    }

                                    if ($operation == '+') {
                                        $matches[1][$i] = $op1 + $op2;
                                    } else {
                                        $matches[1][$i] = $op1 - $op2;
                                    }
                                    unset($matches[1][$i + 1]);
                                    unset($matches[1][$i + 2]);
                                    $matches[1] = array_values($matches[1]);
                                    $i -= 1;
                                }
                            }
                            foreach ($matches[1] as $chunk) {
                                if (strpos($chunk, '<') === 0) {
                                    $chunk = $v[substr($chunk, 1, -1)];
                                }
                                if (strpos($chunk, '%') === 0) {
                                    $chunk = $v[substr($chunk, 1, -1)];
                                } elseif (strpos($chunk, '\'') === 0) {
                                    $chunk = substr($chunk, 1, -1);
                                }
                                $result .= $chunk;
                            }
                        } elseif (isSet($metadata['callback'])) {
                            $object = $metadata['callback']['object'];
                            if (is_string($object)) {
                                $appRoute = App::getPath('appRoute.' . substr($object, 0, 3));
                                require_once App::getConf('modelBackEnd.php') . '/' . $appRoute . '/' . $object . '.php';
                                $object = new $object();
                            }
                            $function = $metadata['callback']['function'];
                            $additionalData = (isSet($metadata['callback']['data']) ? $metadata['callback']['data'] : null);
                            $result = $object->$function($v, $additionalData);
                        } else {
                            $result = $v[$field];
                        }
                        if ($type == null && is_int($result)) {
                            $result = intval($result);
                            $type = self::FORMAT_INTEGER;
                        } elseif ($type == null && is_numeric($result)) {
                            $result = floatval($result);
                            $type = self::FORMAT_DECIMAL5;
                        } elseif ($type == null) {
                            $type = self::FORMAT_STRING;
                        }
                        $sheetData[$sheet]['data'][$k][] = $result;
                    }
                    $sheetHeaders[$sheet]['fields'][] = array(
                        'name' => $metadata['name'],
                        'format' => $type
                    );
                    $sheetOrder[$sheet][] = ($metadata['orderBy'] === self::ORDER_ASC || $metadata['orderBy'] === self::ORDER_DESC ? $metadata['orderBy'] : 0);
                }
                foreach ($sheetData as $sheet => $data) {
                    foreach ($data['data'] as $k => $row) {
                        $empty = true;
                        foreach ($row as $v) {
                            if ($v !== null && $v !== '') {
                                $empty = false;
                                break;
                            }
                        }
                        if ($empty) {
                            unset($sheetData[$sheet]['data'][$k]);
                        }
                    }
                }
                foreach ($sheetData as $sheet => &$data) {
                    foreach ($data['data'] as &$row) {
                        $format = array_values($sheetHeaders[$sheet]['fields']);
                        for ($i = 0; $i < count($row); $i++) {
                            switch ($format[$i]['format']) {
                                case self::FORMAT_DATE:
                                    $value = strtotime($row[$i]);
                                    $row[$i] = ($value ? date('Y-m-d', $value) : '');
                                    break;
                                case self::FORMAT_DATETIME:
                                    $value = strtotime($row[$i]);
                                    $row[$i] = ($value ? date('Y-m-d H:i:s', $value) : '');
                                    break;
                                case self::FORMAT_DECIMAL2:
                                    $row[$i] = round($row[$i], 2);
                                    break;
                                case self::FORMAT_DECIMAL5:
                                    $row[$i] = round($row[$i], 5);
                                    break;
                                case self::FORMAT_DOLLAR:
                                    $row[$i] = round($row[$i], 2);
                                    break;
                                case self::FORMAT_EURO:
                                    $row[$i] = round($row[$i], 2);
                                    break;
                                case self::FORMAT_INTEGER:
                                    $row[$i] = intval($row[$i]);
                                    break;
                                case self::FORMAT_PRICE:
                                    $row[$i] = round($row[$i], 2);
                                    break;
                                case self::FORMAT_STRING:
                                    $row[$i] = utf8_encode($row[$i]);
                                    break;
                            }
                        }
                    }
                    unset($row);
                }
                unset($data);
            }

            $startingRow = array();
            $endingRow = array();
            foreach ($sheetHeaders as $k => $sheet) {
                if (empty($this->customHeaders[$k])) {
                    $header = array(array());
                    foreach($sheet['fields'] as $k2 => $v){
                        $header[0][$k2] = $v['name'];
                    }
                    $this->setCustomHeader($k, $header);
                }
                $sheet['style']['suppress_row'] = true;
                $startingRow[$sheet['sheet']] = 1;
                $endingRow[$sheet['sheet']] = 0;
                
                $fields = array();
                foreach ($sheet['fields'] as $k2 => $v) {
                    $fields[$k2] = $v['format'];
                }
                $this->xslxWriter->writeSheetHeader($sheet['sheet'], $fields, $sheet['style']);
            }

            if (is_array($this->customHeaders)) {
                foreach ($this->customHeaders as $k => $sheet) {
                    $sheetName = $sheetHeaders[$k]['sheet'];
                    for ($i = 0; $i < count($sheet['header']); $i++) {
                        $customHeaderData = $sheet['header'][$i];
                        $customHeaderStyle = $sheet['style'][$i];
                        if (!isSet($customHeaderStyle['format'])) {
                            $customHeaderStyle['format'] = array();
                            for ($j = 0; $j < count($customHeaderData); $j++) {
                                $customHeaderStyle['format'][] = 'string';
                            }
                        }

                        $this->xslxWriter->writeSheetRow($sheetName, $customHeaderData, $customHeaderStyle);
                        $startingRow[$sheetName] ++;
                        $endingRow[$sheetName] ++;
                    }
                    foreach ($sheet['merge'] as $merge) {
                        $this->xslxWriter->markMergedCell($sheetName, $merge['startRow'], $merge['startCol'], $merge['endRow'], $merge['endCol']);
                    }
                }
            }

            foreach (array_keys($sheetData) as $i) {
                $this->sheetOrder = array();
                if (is_array($sheetOrder[$i])) {
                    foreach ($sheetOrder[$i] as $k => $v) {
                        if ($v == self::ORDER_ASC || $v == self::ORDER_DESC) {
                            $this->sheetOrder[$k] = $v;
                        }
                    }
                }
                if (!empty($this->sheetOrder)) {
                    usort($sheetData[$i]['data'], array($this, 'sortFields'));
                }
            }

            foreach ($sheetData as $sheet) {
                $sheetName = $sheet['sheet'];
                $rowStyle = $sheet['style'];
                foreach ($sheet['data'] as $row) {
                    $this->xslxWriter->writeSheetRow($sheetName, $row, $rowStyle);
                }
                $endingRow[$sheetName] += count($sheet['data']);
            }

            if (is_array($this->customFooter)) {
                foreach ($this->customFooter as $k => $sheet) {
                    $sheetName = $sheetHeaders[$k]['sheet'];
                    $this->xslxWriter->writeSheetRow($sheetName, array(), array());

                    for ($i = 0; $i < count($sheet['footer']); $i++) {
                        $customFooterData = array_map(function($cell) use ($startingRow, $endingRow, $sheetName) {
                            return str_replace(array(':startingRow', ':endingRow'), array($startingRow[$sheetName], $endingRow[$sheetName]), $cell);
                        }, $sheet['footer'][$i]);
                        $customFooterStyle = $sheet['style'][$i];
                        if (!isSet($customFooterStyle['format'])) {
                            $customFooterStyle['format'] = array();
                            for ($j = 0; $j < count($customFooterData); $j++) {
                                $customFooterStyle['format'][] = 'string';
                            }
                        }

                        $this->xslxWriter->writeSheetRow($sheetName, $customFooterData, $customFooterStyle);
                    }
                    foreach ($sheet['merge'] as $merge) {
                        $merge['startRow'] += $endingRow[$sheetName] + 2;
                        $merge['endRow'] += $endingRow[$sheetName] + 2;
                        $this->xslxWriter->markMergedCell($sheetName, $merge['startRow'], $merge['startCol'], $merge['endRow'], $merge['endCol']);
                    }
                }
            }
        }
    }

    public function sortFields($a, $b) {
        foreach ($this->sheetOrder as $field => $sort) {
            $r = 0;
            if (is_numeric($a[$field])) {
                $r = $a[$field] - $b[$field];
            } else {
                $r = strcmp($a[$field], $b[$field]);
            }
            $r *= $sort;
            if ($r > 0) {
                return 1;
            } elseif ($r < 0) {
                return -1;
            }
        }
        return 0;
    }

    /**
     * Scrive l'xlsx su file
     * @param <string> $path
     */
    public function writeToFile($path) {
        if (empty($this->xslxWriter)) {
            $this->createCustom();
        }
        $this->xslxWriter->writeToFile($path);
    }

    /**
     * Restituisce l'xlsx sotto forma di binario
     * @return <string>
     */
    public function writeToString() {
        if (empty($this->xslxWriter)) {
            $this->createCustom();
        }
        return $this->xslxWriter->writeToString();
    }

    private static function sortByLen($str1, $str2, $order = 'ASC') {
        $order = ($order == 'ASC' ? 1 : -1);
        return (strlen($str2) - strlen($str1)) * $order;
    }

    public static function getCalculateMatches($calculated, &$matches) {
        preg_match_all("/([+\-\/*]|'.*?'|<.*?>|%.*?%)/", $calculated, $matches);
    }

    public static function calculatedToArray($calculated) {
        $array = array();

        if (!empty($calculated)) {
            self::getCalculateMatches($calculated, $matches);
            /*
              preg_match_all("/([+\-\/*]|'.*?'|<.*?>|%.*?%)/", $model, $matches);
             * 
             */
            foreach ($matches[1] as $chunk) {
                if (strpos($chunk, '<') === 0) {
                    $array[] = array('FIELD' => substr($chunk, 1, -1));
                } elseif (strpos($chunk, '%') === 0) {
                    $array[] = array('EXTRAFIELD' => substr($chunk, 1, -1));
                } elseif (strpos($chunk, '\'') === 0) {
                    $array[] = array('STRING' => substr($chunk, 1, -1));
                } else {
                    $array[] = array('OPERATION' => $chunk);
                }
            }
        }

        return $array;
    }

}
