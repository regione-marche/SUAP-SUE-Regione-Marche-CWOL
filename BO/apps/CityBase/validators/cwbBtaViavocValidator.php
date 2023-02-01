<?php

include_once ITA_BASE_PATH . '/apps/CityBase/validators/cwbBaseValidator.php';
include_once ITA_BASE_PATH . '/apps/CityBase/cwbLibDB_BTA.class.php';

class cwbBtaViavocValidator extends cwbBaseValidator {

    public function customValidate($data, $rules, $operation, $keyMapping, $tableName, $oldCurrentRecord, $modifiedFormData=null) {
        $libDB = new cwbLibDB_BTA();
        if ($operation !== itaModelService::OPERATION_DELETE) {
            if (strlen($data['CODELEMEN']) === 0) {
                $msg = "Codice Elemento obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['CODVOCEL']) === 0) {
                $msg = "Codice Voce obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if (strlen($data['PROGVIAVOC']) === 0) {
                $msg = "Progressivo obbligatorio.";
                $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
            }
            if ($data['PARI_DISP'] <> 'T') {
                if ($data['NUMCIV'] > $data['NUMCIV_F']) {
                    $msg = "Hai indicato un numero civico iniziale" . ' ' . $data['NUMCIV'] . " maggiore del finale" . ' ' . $data['NUMCIV_F'];
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if ($data['NUMCIV'] == $data['NUMCIV_F']) {
                    if ($data['SUBNCIV'] == 0 && $data['SUBNCIVF'] == 1) {
                        $msg = "Se indicato il sottonumero finale, va indicato anche quello iniziale.";
                        $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                    }
                }
                if ($data['SUBNCIV'] == 1 && $data['SUBNCIVF'] == 0) {
                    $msg = "Se indicato il sottonumero iniziale, va indicato anche quello finale.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
                if (($data['SUBNCIV'] == 1 && $data['SUBNCIVF'] == 1) && ($data['SUBNCIV'] > $data['SUBNCIVF'])) {
                    $msg = "Hai indicato un sottonumero iniziale" . ' ' . $data['SUBNCIV'] . " maggiore del finale" . ' ' . $data['SUBNCIVF'];
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }

                //TODO da gestire la parte di codice qui sotto, quando saranno gestiti i WARNING
                $filtri['CODELEMEN'] = trim($data['CODELEMEN']);
                $filtri['CODVIA'] = trim($data['CODVIA']);
                $filtri['PROGVIAVOC'] = trim($data['PROGVIAVOC']);
                $eleme = $libDB->leggiBtaViavocContrCivici($filtri, true);
                $controllo = 0;
                $totaele = count($eleme);
                if ($totaele > 0){
                  $controllo = $this->controllo_civici($data, $totaele, $eleme);  
                } 
                
                if ($controllo) {
                    $msg = "Stai caricando per questa via/elemento, un intervallo di numeri civici che si sovrappone ad un'altra registrazione.";
                    $this->addViolation($tableName, itaModelValidator::LEVEL_ERROR, $msg);
                }
            }
        }
    }

    public function controllo_civici($data, $totaele, $eleme) {
        $f_uscita = 0; // inizializzo variabile
        // Se lista vuota, esco con esito ok; posso caricare  la nuova associazione per quella via(non è presente quindi non genera
        // sovrapposizioni di numeri civici.
        if ($totaele === 0) {
            $f_uscita = 1; // setto flag di uscita a 1... 
        } else {
            if ($data['PARI_DISP'] == 'T') { // se non è vuota e sto associando "Tutta la via" esco con ERRORE.
                $f_uscita = 1;
            }
        }
        if ($f_uscita === 0) {
            for ($i = 1; $i <= $totaele; $i++) {
                //se sulla lista ho "Tutta la via" esco con ERRORE
                if ($eleme[$i - 1]['PARI_DISP'] == 'T') {
                    // ERRORE 2 (è già caricata tutta la via)
                    $f_uscita = 1;
                }

                //se sulla lista ho lato diverso da quello inserito, salto al record successivo.
                if (($eleme[$i - 1]['PARI_DISP'] <> $data['PARI_DISP']) && ($f_uscita === 0)) {
                    continue;
                }

                //i controlli successivi devono essere:
                //
            // se Numero Civico iniziale del record $eleme è compreso nell'intervallo del record che sto inserendo, esco con ERRORE        
                if ($eleme[$i - 1]['NUMCIV'] == $data['NUMCIV'] &&
                        ($data['F_SUBNCIV'] == 0 || $eleme[$i - 1]['F_SUBNCIV'] == 0 ||
                        $eleme[$i - 1]['SUBNCIV'] >= $data['SUBNCIV']) ||
                        ($eleme[$i - 1]['NUMCIV'] >= $data['NUMCIV']) && $f_uscita === 0) { // $eleme[Civico_iniziale] >= Civico iniziale record che sto inserendo
                    if ($eleme[$i - 1]['NUMCIV'] == $data['NUMCIV_F'] &&
                            ($data['F_SUBNCIVF'] == 0 || $eleme[$i - 1]['F_SUBNCIV'] == 0 ||
                            $eleme[$i - 1]['SUBNCIV'] <= $data['SUBNCIV_F']) ||
                            ($eleme[$i - 1]['NUMCIV'] <= $data['NUMCIV_F'] )) { //$eleme[Civico_iniziale] <= Civico finale record che sto inserendo      
                        //ERRORE 4 (civico già presente .... $eleme[Civico_iniziale)
                        $f_uscita = 1;
                    }
                }

                // se Numero Civico finale del record $eleme è compreso nell'intervallo del record che sto inserendo, esco con ERRORE    
                if ($eleme[$i - 1]['NUMCIV_F'] == $data['NUMCIV'] &&
                        ($data['F_SUBNCIV'] == 0 || $eleme[$i - 1]['F_SUBNCIVF'] == 0 ||
                        $eleme[$i - 1]['SUBNCIV_F'] >= $data['SUBNCIV']) ||
                        ($eleme[$i - 1]['NUMCIV_F'] >= $data['NUMCIV']) && $f_uscita === 0) { // $eleme[Civico_finale] >= Civico iniziale record che sto inserendo
                    if ($eleme[$i - 1]['NUMCIV_F'] == $data['NUMCIV_F'] &&
                            ($data['F_SUBNCIVF'] == 0 || $eleme[$i - 1]['F_SUBNCIVF'] == 0 ||
                            $eleme[$i - 1]['SUBNCIV_F'] <= $data['SUBNCIV_F']) ||
                            ($eleme[$i - 1]['NUMCIV_F'] < $data['NUMCIV_F'] )) { //$eleme[Civico_finale] <= Civico finale record che sto inserendo      
                        //ERRORE 5 (civico già presente... $eleme[Civico_finale) 
                        $f_uscita = 1;
                    }
                }
                // se l'intervallo del record che sto inserendo è compreso nell'intervallo della record $eleme, esco con ERRORE        
                if ($eleme[$i - 1]['NUMCIV'] == $data['NUMCIV'] &&
                        ($data['F_SUBNCIV'] == 0 || $eleme[$i - 1]['F_SUBNCIV'] == 0 ||
                        $data['SUBNCIV'] >= $eleme[$i - 1]['SUBNCIV']) ||
                        ($data['NUMCIV'] > $eleme[$i - 1]['NUMCIV']) && $f_uscita === 0) { // Civico iniziale record che sto inserendo > = $eleme[Civico_iniziale] 
                    if ($eleme[$i - 1]['NUMCIV_F'] == $data['NUMCIV_F'] &&
                            ($data['F_SUBNCIVF'] == 0 || $eleme[$i - 1]['F_SUBNCIVF'] == 0 ||
                            $data['SUBNCIV_F'] <= $eleme[$i - 1]['SUBNCIV_F']) ||
                            ($data['NUMCIV_F'] < $eleme[$i - 1]['NUMCIV_F]'] )) { //Civico finale record che sto inserendo  <= $eleme[Civico_finale]       
                        //ERRORE 6 (civico già presente... intervallo del record che sto inserendo compreso nell'intervallo del record $eleme 
                        $f_uscita = 1;
                    }
                }
            }
        }
        return $f_uscita;
    }

}
