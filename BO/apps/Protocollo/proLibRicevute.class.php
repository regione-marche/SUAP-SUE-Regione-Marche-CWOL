<?php

include_once ITA_BASE_PATH . '/apps/Protocollo/proLib.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibVariabili.class.php';
include_once ITA_BASE_PATH . '/apps/Protocollo/proLibMail.class.php';

class proLibRicevute {

    public static function getCorpoRicezione($anapro_rec) {
        $CorpoMail = '';
        $proLibMail = new proLibMail();
        $ElementiMail = $proLibMail->GetElementiTemplateMail($anapro_rec, 1, true);
        if ($ElementiMail) {
            $CorpoMail = $ElementiMail['BODYMAIL'];
        }
        return $CorpoMail;
    }

    public static function getOggettoRicezione($anapro_rec) {
        $Contenuto = '';
        $proLibMail = new proLibMail();
        $ElementiMail = $proLibMail->GetElementiTemplateMail($anapro_rec, 1, true);
        if ($ElementiMail) {
            $Contenuto = $ElementiMail['OGGETTOMAIL'];
        }
        if (!$Contenuto) {
            $Contenuto = 'Non Rispondere a questo Messaggio. Conferma di ricezione - PROTOCOLLO NUMERO: ' . (int) substr($anapro_rec['PRONUM'], 4) . '/' . substr($anapro_rec['PRONUM'], 0, 4);
        }
        return $Contenuto;
    }

    public static function ExGetCorpoRicezione($anapro_rec) {
        $proLib = new proLib();
        $anaent_29 = $proLib->GetAnaent('29');
        $anaent_53 = $proLib->GetAnaent('53');

        $ElencoTemplateMail = unserialize($anaent_53['ENTVAL']);
        $Contenuto = '';
        if ($ElencoTemplateMail[1]) {
            $Contenuto = $ElencoTemplateMail[1]['BODYMAIL'];
            // Qui sostituisco le variabili!
        }
        $proLibVar = new proLibVariabili();
        $proLibVar->setAnapro_rec($anapro_rec);
        $proLibVar->setCodiceProtocollo($anapro_rec['PRONUM']);
        $proLibVar->setTipoProtocollo($anapro_rec['PROPAR']);
        $dictionaryValues = $proLibVar->getVariabiliProtocollo()->getAllData();
        $wsep = '';
        foreach ($dictionaryValues as $key => $valore) {
            $search = '@{$' . $key . '}@';
            if ($valore) {
                if (strpos($Contenuto, $search) === 0) {
                    $wsep = '';
                }
            } else {
                $wsep = '';
            }
            $replacement = $wsep . $valore;
            $Contenuto = str_replace($search, $replacement, $Contenuto);
        }

        if (strpos($Contenuto, '@{$') !== false) {
            return false;
        }
        if (strpos($Contenuto, '}@') !== false) {
            return false;
        }
        return $Contenuto;
    }

    public static function ExGetOggettoRicezione($anapro_rec) {
        $Contenuto = 'Non Rispondere a questo Messaggio. Conferma di ricezione - PROTOCOLLO NUMERO: ' . (int) substr($anapro_rec['PRONUM'], 4) . '/' . substr($anapro_rec['PRONUM'], 0, 4);
        return $Contenuto;
    }

}

?>
