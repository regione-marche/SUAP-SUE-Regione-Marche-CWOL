<?php

class praDatoTipizzato {

    static public $PARAMETRI_TIPIZZATO = array(
        'Rich_padre_variante' => array(
            'DOMUS_STATO' => 'Stato della pratica DOMUS con cui filtrare l\'elenco. Valori separati da ",".',
            'PROCEDIMENTO' => 'Filtro per procedimento. Valori separati da ",", range di valori con "-". (es. "000500,000600-000800,000930")'
        ),
        'Richiesta_padre' => array(
            'DOMUS_STATO' => 'Stato della pratica DOMUS con cui filtrare l\'elenco. Valori separati da ",".',
            'PROCEDIMENTO' => 'Filtro per procedimento. Valori separati da ",", range di valori con "-". (es. "000500,000600-000800,000930")'
        ),
        'Ruolo' => array(
            'CODICE' => 'Elenco dei codici ruolo da visualizzare. Valori separati da ",", range di valori con "-". (es. "001,003-005,007")'
        ),
        'Comune' => array(
            'REGIONE' => 'Filtro per il campo "REGIONE".',
            'PROVINCIA' => 'Filtro per il campo "PROVIN".',
            'CAMPO_PV' => 'DAGKEY del campo su cui ribaltare il valore "PROVIN".',
            'CAMPO_CAP' => 'DAGKEY del campo su cui ribaltare il valore "COAVPO".',
            'CAMPO_ISTAT' => 'DAGKEY del campo su cui ribaltare il valore "CISTAT".'
        ),
        'Indir_InsProduttivo' => array(
            'ANACAT' => 'Filtro per il campo "ANACAT". Può essere "VIEFO" (default), "VIEALL" o "VIEEVT".'
        ),
        'Codfis_Anades' => array(
            'PREFISSO_CAMPI' => 'Prefisso dei campi (es. DICHIARANTE) su cui ribaltare i valori.',
            'ALIAS_*' => 'Sintassi per alias rispetto ai nomi campi standard. (es. ALIAS_NOME => NOMINATIVO)'
        ),
        'Ricerca_Generica' => array(
            'CAPTION' => 'Caption per la tabella di ricerca.',
            'HEADER_{0-n}' => 'Definizione degli header.'
        ),
        'Tabella_Generica' => array(
            'CAPTION' => 'Caption per la tabella di ricerca.',
            'BUTTONADD' => '(0|1) Imposta la presenza del bottone "Aggiungi".',
            'BUTTONEDIT' => '(0|1) Imposta la presenza del bottone "Aggiungi".',
            'BUTTONDEL' => '(0|1) Imposta la presenza del bottone "Aggiungi".',
            'PAGER' => '(0|1) Imposta la presenza del pager.',
            'HEADER_{0-n}' => 'Definizione degli header.'
        )
    );

}
