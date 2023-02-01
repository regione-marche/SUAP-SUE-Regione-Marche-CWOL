<?php

/**
 *
 * Componente BTA_NOTE
 *
 * PHP Version 5
 *
 * @category   CORE
 * @package    Cityware
 * @author     Luca Cardinali <l.cardinali@palinformatica.it>
 * @copyright  
 * @license
 * @version    
 * @link
 * @see
 * @since
 * 
 */
class cwbBtaNote {

    const BTA_NOTE = 'BTA_NOTE';

    private $lib;

    public function __construct() {
        $this->lib = new cwbLibDB_BTA();
    }

    public function getSqlCarica($prognote) {
        if (!$prognote) {
            return array();
        }

        $results = $this->lib->leggiBtaNote(array(
            'PROGNOTE' => $prognote
        ));
        return $results ? $results : array();
    }

    public function elaboraGrid($itaGrid, $nameForm) {
        $Result_tab_tmp = $itaGrid->getDataArray();
        return $this->elaboraRecord($Result_tab_tmp, $nameForm);
    }

    public function elaboraRecord($Result_tab_tmp, $nameForm) {
        foreach ($Result_tab_tmp as $key => $value) {
            // annotazione
            $Result_tab_tmp[$key]['ANNOTAZ'] = trim($Result_tab_tmp[$key]['ANNOTAZ']);

            // tipo nota
            $Result_tab_tmp[$key]['TIPONOTA'] = cwbLibHtml::addGridComponent($nameForm, array(
                        'type' => 'ita-select',
                        'model' => $nameForm,
                        'onChangeEvent' => true,
                        'id' => 'selectTipoNota',
                        'rowKey' => $value['RIGANOTA'],
                        'options' => array(
                            array(
                                'id' => 1,
                                'value' => 1,
                                'text' => 'Nota visibile e stampabile',
                                'selected' => $value['TIPONOTA'] == 1
                            ),
                            array(
                                'id' => 2,
                                'value' => 2,
                                'text' => 'Nota non stampabile',
                                'selected' => $value['TIPONOTA'] == 2
                            )
                        ),
                        'additionalData' => array(
                            'numRiga' => $key
                        )
            ));

            // gruppo nota
            // prendo tutti i grnote presenti nel db
            $listGruppoNote = $this->lib->leggiBtaGrnote(array());
            $gruppoNoteValues = array();
            $gruppoNoteValues[0] = array(
                'id' => 0,
                'value' => 0,
                'text' => 'Selezionare...',
                'selected' => $value['IDGRNOTE'] == 0
            );
            foreach ($listGruppoNote as $keyGR => $valueGR) {
                $gruppoNoteValues[$keyGR + 1] = array(
                    'id' => $valueGR['IDGRNOTE'],
                    'value' => $valueGR['IDGRNOTE'],
                    'text' => $valueGR['DESGRUPPO'],
                    'selected' => $value['IDGRNOTE'] == $valueGR['IDGRNOTE']
                );
            }

            $Result_tab_tmp[$key]['GRUPPO'] = cwbLibHtml::addGridComponent($nameForm, array(
                        'type' => 'ita-select',
                        'model' => $nameForm,
                        'onChangeEvent' => true,
                        'id' => 'selectGruppoNota',
                        'rowKey' => $value['RIGANOTA'],
                        'options' => $gruppoNoteValues,
                        'additionalData' => array(
                            'numRiga' => $key
                        )
            ));

            // natura nota
            if ($value['IDGRNOTE']) {
                $filtri = array(
                    'IDGRNOTE' => $value['IDGRNOTE']
                );
            } else {
                $filtri = array();
            }

            $ntNote = $this->lib->leggiBtaNtnote($filtri);

            $naturaNoteValues = array();
            $naturaNoteValues[0] = array(
                'id' => 0,
                'value' => 0,
                'text' => 'Selezionare...',
                'selected' => $value['NATURANOTA']
            );
            foreach ($ntNote as $keyNt => $valueNt) {
                $naturaNoteValues[$keyNt + 1] = array(
                    'id' => $valueNt['NATURANOTA'],
                    'value' => $valueNt['NATURANOTA'],
                    'text' => $valueNt['DESNATURA'],
                    'selected' => $value['NATURANOTA'] == $valueNt['NATURANOTA']
                );
            }

            $Result_tab_tmp[$key]['NATURANOTA'] = cwbLibHtml::addGridComponent($nameForm, array(
                        'type' => 'ita-select',
                        'model' => $nameForm,
                        'id' => 'selectNaturaNota',
                        'onChangeEvent' => true,
                        'rowKey' => $value['RIGANOTA'],
                        'options' => $naturaNoteValues,
                        'additionalData' => array(
                            'numRiga' => $key
                        )
            ));
        }

        return $Result_tab_tmp;
    }

    public function rimuoviElementoPerRigaNota($elements, $riganota) {
        if ($riganota) {
            $keyDeleted = null;
            $lastKey = count($elements) - 1;
            foreach ($elements as $key => $value) {
                if ($value['RIGANOTA'] == $riganota) {
                    // cancello l'elemento per riga
                    unset($elements[$key]);
                    $keyDeleted = $key;
                } else if ($value['RIGANOTA'] > $riganota) {
                    // per non lasciare buchi scorro tutti gli elementi dell'array successivi a 
                    // quello cancellato e i rispettivi campi riga di uno                    
                    $elements[$key - 1] = $value;
                    $elements[$key - 1]['RIGANOTA'] = $value['RIGANOTA'] - 1;
                }
            }
            // se il campo cancellato non è l'ultimo e quindi ho scorso tutto di uno
            // devo eliminare l'ultimo valore dell'array (che è duplicato su $elements[$lastKey - 1])
            if ($keyDeleted != $lastKey) {
                unset($elements[$lastKey]);
            }
        }
        ksort($elements);

        return $elements;
    }

    public function creaRigaVuota($prognote, $riganota) {
        $modelService = new itaModelService();
        $model = $modelService->define($this->lib->getCitywareDB(), 'BTA_NOTE');
        $model['PROGNOTE'] = $prognote;
        $model['TIPONOTA'] = 1;
        $model['NATURANOTA'] = '';
        $model['ANNOTAZ'] = '';
        $model['NOTA_EV'] = 0;
        $model['NOTA_UR'] = 0;
        $model['TABLENOTE'] = 'BTA_NOTE';
        $model['IDGRNOTE'] = 0;
        $model['NUOVO'] = true;
        $model['RIGANOTA'] = $riganota;
        return $model;
    }

    /**
     * Restituisce il model btaNote
     * @return string cwbBtaNote
     */
    public function getModel() {
        return 'cwbBtaNote';
    }

}

