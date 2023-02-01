<?php

interface ItaUrlUtilInterface {
    /*
     * Ritorna l'indirizzo completo di una pagina con i parametri indicati.
     */

    public static function GetPageUrl($data);

    /*
     * Ritorna l'indirizzo di base per l'accesso alle risorse pubbliche.
     */

    public static function UrlInc();

    public static function GetAbsolutePageUrl($data);

    public static function GetRelativePageUrl($data);
}
