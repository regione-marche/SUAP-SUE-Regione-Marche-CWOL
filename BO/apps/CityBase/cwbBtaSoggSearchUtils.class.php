<?php

include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA_SOGG.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtLibDB_TCA.class.php';
include_once ITA_BASE_PATH . '/apps/CityTax/cwtTributiHelper.php';

class cwbBtaSoggSearchUtils {

    const SOGGETTI_MODEL = 'cwbBtaSoggSearch';
    const DIV_DATI = 'divRiga2';
    const BTA_SOGG_MODE = 1; // default
    const TCA_SOGG_MODE = 2;

    public static function popolaCampi($nameForm, $chiave, $modalita = cwbBtaSoggSearchUtils::BTA_SOGG_MODE, $escludiOmnis = false) {
        if ($modalita === cwbBtaSoggSearchUtils::BTA_SOGG_MODE && !$escludiOmnis) {
            $indirizzo = cwbBtaSoggSearchUtils::leggiIndirizzoSoggettoDaProgsogg($chiave);
        }
        $soggetto = cwbBtaSoggSearchUtils::leggiSoggettoDaChiave($chiave, $modalita);

        if ($modalita === cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
            $_POST[$nameForm . '_SOGGSEARCH_NOMINATIVO'] = $soggetto['COGNOME'] . ' ' . $soggetto['NOME'];
            $_POST[$nameForm . '_SOGGSEARCH_NOMINATIVO_HIDDEN'] = $soggetto['COGNOME'] . ' ' . $soggetto['NOME'];
            $_POST[$nameForm . '_SOGGSEARCH_CHIAVE'] = $soggetto['PROGSOGG'];
            $_POST[$nameForm . '_SOGGSEARCH_MATRICOLA'] = $soggetto['PROGSOGG'];

            Out::valore($nameForm . '_SOGGSEARCH_NOMINATIVO', $soggetto['COGNOME'] . ' ' . $soggetto['NOME']);
            Out::valore($nameForm . '_SOGGSEARCH_NOMINATIVO_HIDDEN', $soggetto['COGNOME'] . ' ' . $soggetto['NOME']);
            Out::valore($nameForm . '_SOGGSEARCH_CHIAVE', $soggetto['PROGSOGG']); // HIDDEN
            Out::valore($nameForm . '_SOGGSEARCH_MATRICOLA', $soggetto['PROGSOGG']);
            if ($soggetto['TIPOPERS'] === 'F') {
                Out::valore($nameForm . '_SOGGSEARCH_CFPIVA', $soggetto['CODFISCALE']);
                Out::valore($nameForm . '_SOGGSEARCH_CFPIVA_HIDDEN', $soggetto['CODFISCALE']);
                $_POST[$nameForm . '_SOGGSEARCH_CFPIVA_HIDDEN'] = $soggetto['CODFISCALE'];
                $_POST[$nameForm . '_SOGGSEARCH_CFPIVA'] = $soggetto['CODFISCALE'];

                $provincia = trim($soggetto['PROVINCIA']) ? (' (' . trim($soggetto['PROVINCIA']) . ')') : '';
                $dataN = str_pad($soggetto['GIORNO'], 2, "0", STR_PAD_LEFT) . '/' . str_pad($soggetto['MESE'], 2, "0", STR_PAD_LEFT) . '/' . $soggetto['ANNO'];

                if ($soggetto['SESSO'] == 'F') {
                    $natoa = " <b>Nata il</b> ";
                } else {
                    $natoa = " <b>Nato il</b> ";
                }

                $testo = " <b>Sesso</b> " . trim($soggetto['SESSO']) . $natoa . $dataN . '<b> a</b> ' . trim($soggetto['DESLOCAL']) . $provincia;
            } else {
                Out::valore($nameForm . '_SOGGSEARCH_CFPIVA', $soggetto['PARTIVA']);
                Out::valore($nameForm . '_SOGGSEARCH_CFPIVA_HIDDEN', $soggetto['PARTIVA']);
                $_POST[$nameForm . '_SOGGSEARCH_CFPIVA_HIDDEN'] = $soggetto['PARTIVA'];
                $_POST[$nameForm . '_SOGGSEARCH_CFPIVA'] = $soggetto['PARTIVA'];
                $testo = '';
            }

            $desIndir = trim($indirizzo['INDIRIZZO_COMPLETO']) ? (" <b>Indirizzo</b> " . trim($indirizzo['INDIRIZZO_COMPLETO'])) : '';
            $desLocalita = trim($indirizzo['DESLOCAL_PROVINCIA']) && trim($indirizzo['DESLOCAL_PROVINCIA']) != '()' ? (" <b>Località</b> " . trim($indirizzo['DESLOCAL_PROVINCIA'])) : '';
            $testo .= $desIndir . $desLocalita;
        } else if ($modalita === cwbBtaSoggSearchUtils::TCA_SOGG_MODE) {
            
        }

        Out::html($nameForm . '_' . self::DIV_DATI, $testo);
    }

    // ESEGUE LA QUERY E SE C'è UN SOLO RECORD LO SELEZIONA IN AUTOMATICO
    public static function forzaSelezione($nameForm, $input, $modalita = cwbBtaSoggSearchUtils::BTA_SOGG_MODE, $matricolaCheck = null) {
        if ($modalita === cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
            $libDB = new cwbLibDB_BTA_SOGG();

            if ($input['NOMINATIVO']) {
                $nominativo = str_replace(" ", "", $input['NOMINATIVO']);
            }

            $filtri = array(
                'NOME_RIC' => $nominativo,
                'CODFISCALEORPARTIVA' => $input['CFPIVA'],
                'PROGSOGG' => $input['CHIAVE'] ? $input['CHIAVE'] : $input['MATRICOLA']
            );

            if (!$filtri['NOME_RIC'] && !$filtri['CODFISCALEORPARTIVA'] && !$filtri['PROGSOGG']) {
                //array di valori null
                return false;
            }

            $soggetti = $libDB->leggiBtaSogg($filtri);

            if (count($soggetti) === 1) {
                cwbBtaSoggSearchUtils::popolaCampi($nameForm, $soggetti[0]['PROGSOGG'], $modalita);
                return $soggetti[0];
            } else {
                if ($matricolaCheck) {
                    // trucco per aggirare il problema di evento onChange che parte dopo aver selezionato
                    // un soggetto dal suggest e se il soggetto è doppio dopo averlo selezionato si svuota in automatico
                    $soggetto = null;
                    foreach ($soggetti as $value) {
                        if ($value['PROGSOGG'] == $matricolaCheck) {
                            $soggetto = $value;
                            break;
                        }
                    }
                    if ($soggetto) {
                        cwbBtaSoggSearchUtils::popolaCampi($nameForm, $soggetto['PROGSOGG'], $modalita);
                        return $soggetto;
                    }
                }
            }
        } else if ($modalita === cwbBtaSoggSearchUtils::TCA_SOGG_MODE) {
            $libDB = new cwtLibDB_TCA();

            $filtri = array(
                'NOMERAGCAT' => $input['NOMINATIVO'],
                'CODFISCALE' => $input['CFPIVA'],
            );

            $soggetti = $libDB->leggiTcaSogg($filtri);

            if (count($soggetti) === 1) {
                $soggetto = $soggetti[0];
                $chiave = $soggetto['CODADMIN'] . '|' . $soggetto['SEZIONE'] . '|' . $soggetto['IDSOGGETTO'] . '|' . $soggetto['TIPOSOGG'];

                cwbBtaSoggSearchUtils::popolaCampi($nameForm, $chiave, $modalita);
                return $soggetti[0];
            } else {
                if ($matricolaCheck) {
                    // trucco per aggirare il problema di evento onChange che parte dopo aver selezionato
                    // un soggetto dal suggest e se il soggetto è doppio dopo averlo selezionato si svuota in automatico
                    $soggetto = null;
                    foreach ($soggetti as $value) {
                        if ($value['PROGSOGG'] == $matricolaCheck) {
                            $soggetto = $value;
                            break;
                        }
                    }
                    if ($soggetto) {
                        $chiave = $soggetto['CODADMIN'] . '|' . $soggetto['SEZIONE'] . '|' . $soggetto['IDSOGGETTO'] . '|' . $soggetto['TIPOSOGG'];

                        cwbBtaSoggSearchUtils::popolaCampi($nameForm, $chiave, $modalita);
                        return $soggetto;
                    }
                }
            }
        }

        if (!$soggetti) {
            // soggetto non trovato
            // svuoto tutto tranne la stringa scritta in modo da poterla correggere
            cwbBtaSoggSearchUtils::pulisciValori($nameForm, $input['NOMINATIVO'], $input['CFPIVA']);
            cwbBtaSoggSearchUtils::msgNonTrovato($nameForm);
        } else {
            // trovato piu di un soggetto
            // svuoto tutto tranne la stringa scritta in modo da poterla correggere
            cwbBtaSoggSearchUtils::pulisciValori($nameForm, $input['NOMINATIVO'], $input['CFPIVA']);
            cwbBtaSoggSearchUtils::msgTrovatiMultipli($nameForm);
        }

        return false;
    }

    // se non trovo risultati, lascio la stringa scritta e svuoto tutto il resto ($escludeNominativo o $exludeCf)
    public static function pulisciValori($nameForm, $escludeNominativo = false, $exludeCf = false) {
        Out::valore($nameForm . '_SOGGSEARCH_MATRICOLA', '');
        $_POST[$nameForm . '_SOGGSEARCH_MATRICOLA'] = '';

        Out::valore($nameForm . '_SOGGSEARCH_CHIAVE', ''); // HIDDEN
        $_POST[$nameForm . '_SOGGSEARCH_CHIAVE'] = '';

        if (!$escludeNominativo) {
            Out::valore($nameForm . '_SOGGSEARCH_NOMINATIVO', '');
            $_POST[$nameForm . '_SOGGSEARCH_NOMINATIVO'] = '';
        }

        if (!$exludeCf) {
            Out::valore($nameForm . '_SOGGSEARCH_CFPIVA', '');
            $_POST[$nameForm . '_SOGGSEARCH_CFPIVA'] = '';
        }

        Out::valore($nameForm . '_SOGGSEARCH_NOMINATIVO_HIDDEN', '');
        $_POST[$nameForm . '_SOGGSEARCH_NOMINATIVO_HIDDEN'] = '';

        Out::valore($nameForm . '_SOGGSEARCH_CFPIVA_HIDDEN', '');
        $_POST[$nameForm . '_SOGGSEARCH_CFPIVA_HIDDEN'] = '';

        Out::html($nameForm . '_' . self::DIV_DATI, "");
    }

    public static function disabilitaCampiSuggest($nameForm) {
        Out::disableField($nameForm . '_SOGGSEARCH_NOMINATIVO');
        Out::disableField($nameForm . '_SOGGSEARCH_CFPIVA');
        Out::disableField($nameForm . '_SOGGSEARCH_MATRICOLA');
    }

    public static function msgNonTrovato($nameForm) {
        Out::html($nameForm . '_' . self::DIV_DATI, "Nessun Soggetto Trovato");
    }

    public static function msgTrovatiMultipli($nameForm) {
        Out::html($nameForm . '_' . self::DIV_DATI, "Il soggetto non è univoco, sceglierne uno dalla combo dei suggerimenti");
    }

    public static function leggiSoggettoDaChiave($chiave, $modalita = cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
        if ($modalita === cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
            $libDB = new cwbLibDB_BTA_SOGG();
            return $libDB->leggiBtaSoggChiave($chiave);
        } else if ($modalita === cwbBtaSoggSearchUtils::TCA_SOGG_MODE) {
            $libDB = new cwtLibDB_TCA();
            return $libDB->leggiTcaSoggChiave($chiave);
        }
    }

    // solo per bta_sogg
    public static function leggiIndirizzoSoggettoDaProgsogg($progsogg) {
        $cwtTributiHelper = new cwtTributiHelper();
        return $cwtTributiHelper->indirizzoSoggetto($progsogg);
    }

    public static function leggiChiaveSelezionata($nameForm) {
        return $_POST[$nameForm . '_SOGGSEARCH_CHIAVE'];
    }

    public static function setPostSoggettoSelezionato($nameForm, $sogg, $modalita = cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
        if ($modalita === cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
            $_POST[$nameForm . '_SOGGSEARCH_NOMINATIVO'] = $sogg['COGNOME'] . ' ' . $sogg['NOME'];
            $_POST[$nameForm . '_SOGGSEARCH_CFPIVA'] = $sogg['CODFISCALE'];
            $_POST[$nameForm . '_SOGGSEARCH_NOMINATIVO_HIDDEN'] = $sogg['COGNOME'] . ' ' . $sogg['NOME'];
            $_POST[$nameForm . '_SOGGSEARCH_CFPIVA_HIDDEN'] = $sogg['CODFISCALE'];
            $_POST[$nameForm . '_SOGGSEARCH_CHIAVE'] = $sogg['PROGSOGG'];
            $_POST[$nameForm . '_SOGGSEARCH_MATRICOLA'] = $sogg['PROGSOGG'];
        } else if ($modalita === cwbBtaSoggSearchUtils::TCA_SOGG_MODE) {
            // TODO
//            $_POST[$nameForm . '_SOGGSEARCH_NOMINATIVO'] = $sogg['NOMINATIVO'];
//            $_POST[$nameForm . '_SOGGSEARCH_CFPIVA'] = $sogg['CFPIVA'];
//            $_POST[$nameForm . '_SOGGSEARCH_CHIAVE'] = $sogg['CHIAVE'];
        }
    }

    public static function leggiSoggettoSelezionato($nameForm, $modalita = cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
        $sogg = array(
            'NOMINATIVO' => $_POST[$nameForm . '_SOGGSEARCH_NOMINATIVO'],
            'CFPIVA' => $_POST[$nameForm . '_SOGGSEARCH_CFPIVA'],
            'CHIAVE' => $_POST[$nameForm . '_SOGGSEARCH_CHIAVE']
        );

        if ($modalita === cwbBtaSoggSearchUtils::BTA_SOGG_MODE) {
            $sogg['MATRICOLA'] = $_POST[$nameForm . '_SOGGSEARCH_MATRICOLA'];
        }

        return $sogg;
    }

    public static function innestaComponenteSoggetto($divContainer, $nameFormContainer = null, $aliasNameFormContainer = null, $modalita = cwbBtaSoggSearchUtils::BTA_SOGG_MODE, $escludiOmnis = false) {
        // innesto il componente soggetti
        $formObj = cwbLib::innestaForm(self::SOGGETTI_MODEL, $divContainer);
        if (!$formObj) {
            Out::msgStop("Errore", "Errore apertura form soggetti");
            return null;
        }
        $formObj->setNameFormContainer($nameFormContainer);
        $formObj->setAliasNameFormContainer($aliasNameFormContainer);
        $formObj->setModalitaComponente($modalita);
        $formObj->setEscludiOmnis($escludiOmnis);
        $formObj->parseEvent();

        return $formObj;
    }

    public static function chiudiComponenteSoggetto($aliasNameForm = null) {
        $objModel = itaFrontController::getInstance(self::SOGGETTI_MODEL, $aliasNameForm);
        $objModel->setEvent("onClick");
        $objModel->setElementId("close-portlet");
        $objModel->parseEvent();
    }

    public static function cambiaNomeTitolo($nameForm, $title) {
        Out::html($nameForm . '_divSoggettoSearch_boxHeader_title', $title);
    }

}
