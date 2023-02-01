<?php

/**
 * Interfaccia per la gestire i locker
 * @author l.pergolini
 */
interface itaLockerManager {

    //Applica un lock del record passato "$Record" per la tabella "$table"
    public function lock($db, $table, $Record, $mode = "", $wait = 0, $duration = 300);

    //Sblocca il record bloccato in precedenza $lockID chiave tabella per effettuare unLock
    public function unlock($lockID);

    //controlla da chi è bloccato il record
    public function lockedBy($db, $tableLocked, $Record);
    
    //Cancella i lock per la sessione
    public function unlockForSession();

}
