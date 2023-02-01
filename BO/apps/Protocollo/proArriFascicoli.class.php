<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibFascicolo.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';

/**
 * Description of proArriFascicoli
 *
 * @author pc-asus
 */
class proArriFascicoli {

    public function CtrFascicoloPre($model) {
        App::log('CtrFascicoloPre');
        $proLib = new proLib();
        $formData = $model->getFormData();

        $Anapro_rec = $formData[$model->nameForm . "_ANAPRO"];
        /* Se protocollo già esistente verifico se è gia fascicolato. */
        if ($Anapro_rec['ROWID']) {
            $AnaproOrig_rec = $proLib->GetAnapro($Anapro_rec['ROWID'], 'rowid');
            if ($AnaproOrig_rec['PROFASKEY']) {
                App::log('CtrFascicoloPre gia fas');
                return false;
            }
        }
        
        // Decodifica del titolario.
        $Anapro_rec['PROCCA'] = $Anapro_rec['PROCAT'] . $formData[$model->nameForm . "_Clacod"];
        $Anapro_rec['PROCCF'] = $Anapro_rec['PROCCA'] . $formData[$model->nameForm . "_Fascod"];
        // Protocollo Collegato
        $Anapro_rec['PROPRE'] = $formData[$model->nameForm . "_Propre2"] * 1000000 + $formData[$model->nameForm . "_Propre1"];
        if ($Anapro_rec['PROPRE'] && $Anapro_rec['PROPARPRE']) {
                App::log('CtrFascicoloPre leggo precedente');
            $AnaproPre_rec = $proLib->GetAnapro($Anapro_rec['PROPRE'], 'codice', $Anapro_rec['PROPARPRE']);
            App::log($AnaproPre_rec);            
            if ($AnaproPre_rec['PROFASKEY']) {
                if ($Anapro_rec['PROCCF'] && $AnaproPre_rec['PROCCF'] == $Anapro_rec['PROCCF']) {
/*
 *  Sospeso per fascicoli multipli
 *  se necessario collegare al i pfascicoli di presenza
 * 
 */
//                    $this->ChiediFascicolaProPre($model, $AnaproPre_rec);
                } else {
                    $Messaggio = 'Titolario del protocollo collegato differente da questo protocollo.<br>Continuare con la registrazione?';
                    Out::msgQuestion("Fascicolazione", $Messaggio, array(
                        'F8-Annulla' => array('id' => $model->nameForm . '_AnnullaRegistra',
                            'model' => $model->nameForm, 'shortCut' => "f8"),
                        'F5-Conferma' => array('id' => $model->nameForm . '_ContinuaRegistraCtrFascicolo',
                            'model' => $model->nameForm, 'shortCut' => "f5")
                            )
                    );
                }
                return true;
            }
        }
        return false;
    }

    public function ChiediFascicolaProPre($model, $AnaproPre_rec) {
        $proLib = new proLib();
        $proLibFascicolo = new proLibFascicolo();
        $retIterStato = proSoggetto::getIterStato($AnaproPre_rec['PRONUM'], $AnaproPre_rec['PROPAR']);
        $permessiFascicolo = $proLibFascicolo->GetPermessiFascicoli();
        /*
         * Se permessi attivati per la movimentazione del fascicolo e protocollo in gestione può inserirlo nel fascicolo
         */
        $fl_movimenta = false;
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_GESTIONE_DOCUMENTI] && ($retIterStato['GESTIONE'] || $retIterStato['RESPONSABILE'])) {
            $fl_movimenta = true;
        }
        if ($permessiFascicolo[proLibFascicolo::PERMFASC_VISIBILITA_ARCHIVISTA]) {
            $fl_movimenta = true;
        }
        if (!$fl_movimenta) {
            return false;
        }
        $Anaorg_rec = $proLib->GetAnaorg($AnaproPre_rec['PROFASKEY'], 'orgkey');

        $protCollegato = substr($AnaproPre_rec['PRONUM'], 4) . '/' . substr($AnaproPre_rec['PRONUM'], 0, 4) . ' ' . $AnaproPre_rec['PROPAR'];
        $Messaggio = '';
        $Messaggio.='Il protocollo collegato <b>' . $protCollegato . '</b> si trova nel fascicolo ';
        $Messaggio.= '<b>' . $Anaorg_rec['ORGCOD'] . '</b> del <b>' . $Anaorg_rec['ORGANN'] . '</b>.';

        $Orgconn_rec = $proLib->GetOrgConn($AnaproPre_rec['PRONUM'], 'codice', $AnaproPre_rec['PROPAR']);
        if ($Orgconn_rec['PROPARPARENT'] == 'F') {
            $ProGes_rec = $proLib->GetProges($Anaorg_rec['ORGKEY'], 'codice');
            $Messaggio.= '<br>Con oggetto:<br>' . $ProGes_rec['GESOGG'] . '';
            $Messaggio.='<br><br><b>Vuoi inserire questo protocollo nello stesso fascicolo?</b>';
        } else {
            $AnaproSottoFascicolo_rec = $proLib->GetAnapro($Orgconn_rec['PRONUMPARENT'], 'codice', $Orgconn_rec['PROPARPARENT']);
            $AnaOgg_rec = $proLib->GetAnaogg($Orgconn_rec['PRONUMPARENT'], $Orgconn_rec['PROPARPARENT']);
            $Sottofascicolo = str_replace($AnaproSottoFascicolo_rec['PROFASKEY'] . '-', '', $AnaproSottoFascicolo_rec['PROSUBKEY']);
            $Messaggio.= '<br>Nel sottofascicolo: <b>' . $Sottofascicolo . '</b>';
            $Messaggio.= '<br>Oggetto: ' . $AnaOgg_rec['OGGOGG'] . '';
            $Messaggio.='<br><br><b>Vuoi inserire questo protocollo nello stesso fascicolo?</b>';
        }

        Out::msgQuestion("Fascicolazione", $Messaggio, array(
            'F8-Annulla' => array('id' => $model->nameForm . '_AnnullaFascicolaProtoPre',
                'model' => $model->nameForm, 'shortCut' => "f8"),
            'F5-Conferma' => array('id' => $model->nameForm . '_ConfermaFascicolaProtoPre',
                'model' => $model->nameForm, 'shortCut' => "f5")
                )
        );
        return true;
    }

    public function FascicolaProPre($model,$anapro_rec) {
        $proLib = new proLib();
        $proLibFascicolo = new proLibFascicolo();

        $AnaproPre_rec = $proLib->GetAnapro($anapro_rec['PROPRE'], 'codice', $anapro_rec['PROPARPRE']);
        $Orgconn_rec = $proLib->GetOrgConn($AnaproPre_rec['PRONUM'], 'codice', $AnaproPre_rec['PROPAR']);
        $fascicolo_rec = $proLib->GetAnaorg($AnaproPre_rec['PROFASKEY'], 'orgkey');

        $pronumR = $Orgconn_rec['PRONUMPARENT'];
        $proparR = $Orgconn_rec['PROPARPARENT'];

        if (!$proLibFascicolo->insertDocumentoFascicolo($model, $fascicolo_rec['ORGKEY'], $anapro_rec['PRONUM'], $anapro_rec['PROPAR'], $pronumR, $proparR)) {
            Out::msgStop("Attenzione! Nuovo Fascicolo", $proLibFascicolo->getErrMessage());
            return false;
        } else {
            Out::msgBlock('', 3000, true, 'Protocollo fascicolato correttamente.');
        }
        return true;
    }

}

?>
