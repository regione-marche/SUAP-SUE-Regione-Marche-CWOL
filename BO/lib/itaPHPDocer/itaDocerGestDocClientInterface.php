<?php

/**
 *
 * Interfaccia Gestione Documentale DocER - Servizio Gestione Documentale
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPDocer
 * @author     
 * @copyright  1987-2017 Italsoft srl
 * @license
 * @version    19.10.2017
 * @link
 * @see
 * @since
 * */
interface itaDocerGestDocClientInterface {

    /**
     * Permette di ottenere la lista degli id dei document-type definiti 
     * nella configurazione della Business Logic e visibili per una data AOO.
     * @param array $param
     *                 - TOKEN
     *                 - COD_ENTE
     *                 - codiceAOO
     * @return array Tipi di documento configurati per Ente/AOO
     */
    public function ws_getDocumentTypesByAOO($param);

    /**
     * Permette la creazione di un?anagrafica Ente nel DMS e del relativo Gruppo Ente.
     * @param array $param
     *                 - TOKEN
     *                 - COD_ENTE
     *                - DES_ENTE (descrizione)
     *                - ENABLED (true o false)
     *                 - metadati extra
     * @return esito
     */
    public function ws_createEnte($param);

    /**
     * Permette di ottenere il profilo di un'anagrafica Ente del DMS.
     * @param array $param
     *                 - TOKEN
     *                 - COD_ENTE
     * @return array Dati Ente
     */
    public function ws_getEnte($param);

    /**
     * Permette la modifica del profilo di un'anagrafica Ente
     * @param array $param
     *                 - TOKEN
     *                 - DES_ENTE
     */
    public function ws_updateEnte($param);

    /**
     * Permette annullamento logico del profilo di un'anagrafica Ente
     * @param array $param
     *                 - TOKEN
     *                 - COD_ENTE
     */
    public function ws_deleteEnte($param);

    /**
     * Permette di ottenere il profilo di un'anagrafica AOO del DMS.
     * @param array $param
     *                 - TOKEN
     *                 - aooId:
     *                      COD_AOO (codice AOO)
     *                      COD_ENTE (codice ente padre
     *                      metadati extra
     * @return array Dati AOO
     */
    public function ws_getAOO($param);

    /**
     * permette di recuperare il profilo di un utente del DMS.
     * @param type $param :
     *              -TOKEN
     *              -GROUP_ID
     */
    public function ws_getGroup($param);

    /**
     * Permette la creazione dei gruppi nel DMS.
     * @param array $param
     *                 - TOKEN
     *                 - groupInfo:
     *                      GROUP_ID (id del gruppo)
     *                      GROUP_NAME (nome del gruppo)
     *                      PARENT_GROUP_ID (id del gruppo padre)
     *                      GRUPPO_STRUTTURA: flag (true/false) che indica se il gruppo appartiene alla struttura dell?ente
      oppure rappresenta un gruppo ?di servizio?
     *                      metadati extra
     * @return esito
     */
    public function ws_createGroup($param);

    /**
     * Permette la modifica del profilo di un gruppo nel DMS.
     * @param array $param
     *                 - TOKEN
     *                 - groupInfo[]
     * @return esito
     */
    public function ws_updateGroup($param);

    /**
     * Permette annullamento logico del profilo di un gruppo nel DMS.
     * @param array $param
     *                 - TOKEN
     *                 - groupInfo[]
     * @return esito
     */
    public function ws_deleteGroup($param);

    /**
     * Permette di recuperare il profilo di un utente del DMS.
     * @param type $param
     *                 - TOKEN
     *                 - userId
     */
    public function ws_getUser($param);

    /**
     * Permette di recuperare la lista degli id degli utenti appartenenti ad un gruppo del DMS.
     * @param string  - TOKEN
     *                - groupId 
     */
    public function ws_getUsersOfGroup($param);
    
    /**
     * Permette di recuperare la lista degli id dei gruppi appartenenti ad un utente del DMS.
     * @param string  - TOKEN
     *                - userId 
     */
    public function ws_getGroupsOfUser($param);
    
    /**
     * Permette di modificare la lista degli utenti assegnati ad un gruppo del DMS.
     * @param string 
     *               - TOKEN   
     *               - groupId 
     *               - usersToAdd[]
     *               - usersToRemove[]
     */
    public function ws_updateUsersOfGroup($param);
    
    /**
     * Permette di modificare la lista degli utenti assegnati ad un gruppo del DMS.
     * @param string 
     *               - TOKEN   
     *               - userId 
     *               - groupsToAdd[]
     *               - groupsToRemove[]
     */
    public function ws_updateGroupsOfUser($param);
    
    /**
     * Permette la creazione degli utenti nel DMS.
     * @param array $param
     *                 - TOKEN
     *                 - userInfo:
     *                      USER_ID (id dell'utente)
     *                      FULL_NAME (nome completo dell'utente)
     *                      COD_ENTE (l'Ente primario di appartenenza)
     *                      COD_AOO (la AOO primaria di appartenenza)
     *                      USER_PASSWORD (è possibile assegnare un default in fase di creazione ma non fa parte del profilo)
     *                      FIRST_NAME (nome dell'utente)
     *                      LAST_NAME (cognome dell'utente)
     *                      EMAIL_ADDRESS (indirizzo email dell'utente)
     *                      metadati extra
     * @return esito
     */
    public function ws_createUser($param);

    /**
     * Permette la modifica del profilo di un utente nel DMS.
     * @param userId
     * @param array $param
     *                 - TOKEN
     *                 - userInfo[]
     * @return esito
     */
    public function ws_updateUser($param);

    /**
     * Permette annullamento logico di un utente nel DMS.
     * @param userId
     * @param array $param
     *                 - TOKEN
     *                 - userId
     * @return esito
     */
    public function ws_deleteUser($param);

    /**
     * Permette di assegnare gli utenti ad un gruppo del DMS.
     * @param array $param
     *                 - TOKEN
     *                 - groupId
     *                 - users[] 
     * @return esito
     */
    public function ws_setUsersOfGroup($param);
    
    /**
     * Permette di assegnare i gruppi ad un utente del DMS.
     * @param array $param
     *                 - TOKEN
     *                 - userId
     *                 - groups[] 
     * @return esito
     */
    public function ws_setGroupsOfUser($param);
    
    /**
     * Permette la creazione di un Documento nel DMS.
     * @param array $param
     *                 - TOKEN
     *                 - metadata[] https://docs.google.com/spreadsheets/d/1YNTvT6b0AghPobOe3mujNBLjZVX5XqBeYUEDVVHJRmw/edit#gid=285648568
     *                      Obbligatorio in creazione: TYPE_ID,COD_ENTE,COD_AOO,DOCNAME
     *                      Metadati di Base
     *                      Metadati di Firma
     *                      Metadati dei Corrispondenti
     *                      Metadati di Protocollo Mittente 
     *                      Metadati per la conservazione sostitutiva
     *                  - file
     * @return esito
     */
    public function ws_createDocument($param);

    /**
     * Permette la modifica del profilo di un Documento nel DMS.
     * @param array $param
     *                 - TOKEN
     *                 - DOCID
     *                 - metadata[] https://docs.google.com/spreadsheets/d/1YNTvT6b0AghPobOe3mujNBLjZVX5XqBeYUEDVVHJRmw/edit#gid=285648568
     *                      Metadati di Base
     *                      Metadati di Firma
     *                      Metadati dei Corrispondenti
     *                      Metadati di Protocollo Mittente 
     *                      Metadati per la conservazione sostitutiva
     */
    public function ws_updateProfileDocument($param);
    
    /**
     * Questo metodo permette di recuperare il profilo di un Documento del DMS.
     * @param array $param
     *                 - TOKEN
     *                 - DOCID
     * @return array Dati Profilo documento
     */
    public function ws_getProfileDocument($param);
    
    /**
     * Permette di ottenere il profilo di un'anagrafica voce di Titolario del DMS.
     * @param array $param
     *                 - TOKEN
     *                 - titolarioId[]
     * @return esito
     */
    public function ws_getTitolario($param);

    /**
     * Permette di eseguire delle ricerche sulla collezione delle anagrafiche del DMS.
     * @param array $param
     *                 - TOKEN
     *                 - titolarioId: 
     *                      CLASSIFICA (classifica della voce Titolario)
     *                      COD_ENTE (codice Ente padre)
     *                      COD_AOO (codice AOO padre)
     * @return esito
     */
    public function ws_searchAnagrafiche($param);

    /**
     * Permette di eseguire le ricerche sui Documenti del DMS. 
     * array $param
     *           - TOKEN
     *           - searchCriteria
     * TYPE_ID,COD_ENTE,COD_AOO,DOCNAME,ABSTRACT,CREATION_DATE,TIPO_COMPONENTE,ARCHIVE_TYPE,DOC_URL,APP_VERSANTE
     * DOC_HASH,STATO_BUSINESS,STATO_ARCHIVISTICO,DOCNUM,DOCNUM_PRINC,DOCNUM_RECORD,VISTO,AUTHOR_ID,TYPIST_ID,DOC_VERSION,UD_VERSION
     *           - keywords[] collezione di stringhe dove ogni stringa è una parola chiave da ricercare nel file del documento.
     *           - maxRows indica il massimo numero di risultati che devono essere restituiti
     *           - orderby[] Criteri di ordinamento dei risultati
     * return esito
     */
    public function ws_searchDocuments($param);
    
    /**
     * Effettua il download del documento specificato 
     * array $param
     *           - TOKEN
     *           - docId
     * return esito
     */
    public function ws_downloadDocument($param);
    
    /**
     * Permette di assegnare le ACL di un Documento del DMS,
     * @param string 
     *          - $param[docId] id del documneto 
     *          - acls[] key 2:Read Only Access, 1:Normal Access 0 Full Access
     */
    public function ws_setACLDocument($param);

    /**
     * Permette di recuperare i diritti di un Documento del DMS.
     * @param string 
     *          - TOKEN
     *          - $param[docId] id del documneto 
     *          - acls[] key 2:Read Only Access, 1:Normal Access 0 Full Access
     */
    public function ws_getACLDocument($param);

    /**
     * Permette la creazione di un'anagrafica Fascicolo nel DMS.
     * @param string 
     *          - TOKEN
     *          - fascicoloInfo:
     *                  PROGR_FASCICOLO (progressivo del Fascicolo)
     *                  ANNO_FASCICOLO (anno del Fascicolo)
     *                  PARENT_PROGR_FASCICOLO (progressivo del Fascicolo ?padre? o di livello superiore)
     *                  CLASSIFICA (classifica della voce Titolario padre)
     *                  COD_AOO (codice AOO padre)
     *                  COD_ENTE (codice Ente padre)
     *                  DES_FASCICOLO (descrizione)
     *                  ENABLED (true o false)
     *                  DATA_APERTURA (data di apertura del fascicolo)
     *                  DATA_CHIUSURA (data di chiusura del fascicolo)
     *                  CF_PERSONA (codice fiscale della persona di riferimento per il fascicolo)
     *                  CF_AZIENDA (cofice fiscale dell?azienda di riferimento del fascicolo)
     *                  ID_PROC (id del procedimento a cui si riferisce il fascicolo)
     *                  ID_IMMOBILE (id dell?immobile a cui si riferisce il fascicolo)
     */
    public function ws_createFascicolo($param);

    /**
     * Permette la modifica del profilo di un?anagrafica Fascicolo nel DMS.
     * @param type - fascicoloId:
     *                  PROGR_FASCICOLO (progressivo del Fascicolo)
     *                  ANNO_FASCICOLO (anno del Fascicolo)
     *                  COD_ENTE (codice Ente padre)
     *                  COD_AOO (codice AOO padre)
     *                  CLASSIFICA (classifica della voce Titolario padre)
     *              - fascicoloInfo:
     *                  DES_FASCICOLO (descrizione del Fascicolo)
     *                  ENABLED (true o false)
     *                  DATA_APERTURA (data di apertura del fascicolo)
     *                  DATA_CHIUSURA (data di chiusura del fascicolo)
     *                  CF_PERSONA (codice fiscale della persona di riferimento per il fascicolo)
     *                  CF_AZIENDA (cofice fiscale dell?azienda di riferimento del fascicolo)
     *                  ID_PROC (id del procedimento a cui si riferisce il fascicolo)
     *                  ID_IMMOBILE (id dell?immobile a cui si riferisce
     */
    public function ws_updateFascicolo($param);

    /**
     * Permette annullamento logico  del profilo di un?anagrafica Fascicolo nel DMS.
     * @param type - fascicoloId:
     *                  PROGR_FASCICOLO (progressivo del Fascicolo)
     *                  ANNO_FASCICOLO (anno del Fascicolo)
     *                  COD_ENTE (codice Ente padre)
     *                  COD_AOO (codice AOO padre)
     *                  CLASSIFICA (classifica della voce Titolario padre)
     */
    public function ws_deleteFascicolo($param);

    /**
     * Permette di ottenere il profilo di un'anagrafica Fascicolo del DMS.
     * @param type - fascicoloId:
     *                  PROGR_FASCICOLO (progressivo del Fascicolo)
     *                  ANNO_FASCICOLO (anno del Fascicolo)
     *                  COD_ENTE (codice Ente padre)
     *                  COD_AOO (codice AOO padre)
     *                  CLASSIFICA (classifica della voce Titolario padre)
     */
    public function ws_getFascicolo($param);

    /**
     * Permette di recuperare le ACL esplicite di un Fascicolo del DMS.
     * @param string 
     *          - TOKEN
     *          - $param[fascicoloId] id del fascicolo COD_ENTE-COD_AOO-CLASSIFICA-ANNO_FASCICOLO-PROGR_FASCICOLO
     *          - acls[] key 2:Read Only Access, 1:Normal Access 0 Full Access
     */
    public function ws_getACLFascicolo($param);

    /**
     * Permette di impostare le ACL esplicite ad un Fascicolo del DMS. Le precedenti ACL del
     * Fascicolo vengono sovrascritte con la nuova lista specificata.
     * @param string 
     *          - TOKEN
     *          - $param[fascicoloId] id del fascicolo COD_ENTE-COD_AOO-CLASSIFICA-ANNO_FASCICOLO-PROGR_FASCICOLO
     *          - acls[] key 2:Read Only Access, 1:Normal Access 0 Full Access
     */
    public function ws_setACLFascicolo($param);

    /**
     * Permette l'assegnazione dei metadati di protocollazione ad un Documento del DMS.
     * @param string 
     *          - TOKEN
     *          - DOC_ID
     *          - metadata[] https://docs.google.com/spreadsheets/d/1YNTvT6b0AghPobOe3mujNBLjZVX5XqBeYUEDVVHJRmw/edit#gid=285648568
     *          Metadati di Protocollazione
     *          Metadati di Annullamento della Protocollazione
     *          Metadati di Firma   
     *          Metadati dei Corrispondenti
     *          Metadati di Protocollo Mittente
     */
    public function ws_protocollaDocumento($param);

    /**
     * Permette la creazione di un'anagrafica voce di Titolario nel DMS.
     * @param string 
     *          - TOKEN
     *          - TITOLARIOINFO[]:
     *                  CLASSIFICA (classifica della voce di titolario)
     *                  COD_ENTE (codice ente padre)
     *                  COD_AOO (codice AOO padre)
     *                  PARENT_CLASSIFICA (classifica della voce Titolario padre)
     *                  DES_TITOLARIO (descrizione) 
     *                  ENABLED (true o false)
     */
    public function ws_createTitolario($param);

    /**
     * Permette la modifica del profilo di un'anagrafica Titolario nel DMS.
     * @param string 
     *          - TOKEN
     *          - TITOLARIOID[]:
     *                  CLASSIFICA (classifica della voce di titolario)
     *                  COD_ENTE (codice ente padre)
     *                  COD_AOO (codice AOO padre)
     *          - TITOLARIOINFO[]:
     *                  PARENT_CLASSIFICA (classifica della voce Titolario padre)
     *                  DES_TITOLARIO (descrizione) 
     *                  ENABLED (true o false)
     *                 
     */
    public function ws_udpateTitolario($param);

    /**
     * Permette annullamento logico del profilo di un'anagrafica Titolario nel DMS.
     * @param array $param
     *                 - TOKEN
     *                 - titolarioId
     * @return esito
     */
    public function ws_deleteTitolario($param);

    /**
     * Permette la fascicolazione di un Documento e di tutti i suoi related nel DMS.
     * @param type $param
     *                 - TOKEN
     *                 - DOCID
     *                  metadata: https://docs.google.com/spreadsheets/d/1YNTvT6b0AghPobOe3mujNBLjZVX5XqBeYUEDVVHJRmw/edit#gid=285648568
     *                      Metadati di Classificazione CLASSIFICA,PIANO_CLASS,COD_ENTE,COD_AOO
     *                      Metadati di Fascicolazione PROGR_FASCICOLO,ANNO_FASCICOLO,CLASSIFICA,FASC_SECONDARI,COD_ENTE,COD_AOO
     * @return esito
     */
    public function ws_fascicolaDocumento($param);

    /**
     * Permette la modifica dei metadati di registrazione particolare del profilo di un Documento nel
     * @param type $param
     *                       - TOKEN
     *                       - DOCID
     *                          metadata[] https://docs.google.com/spreadsheets/d/1YNTvT6b0AghPobOe3mujNBLjZVX5XqBeYUEDVVHJRmw/edit#gid=285648568
     *                              ID_REGISTRO: è l'identificativo del registro particolare assegnato al documento
     *                              Metadati di Registrazione 
     *                              Metadati di Annullamento della Registrazione
     *                              Metadati di Firma
     *                              Metadati dei Corrispondenti        
     * @return esito     
     */
    public function ws_registraDocumento($param);
    
    /**
     * Permette l'aggiunta di una anagrafica custom
     * @param type $param
     *                       - TOKEN
     *                       - customInfo[]                         
     *                              TYPE_ID
     *                              COD_ENTE
     *                              COD_AOO
     *                              [codice_anagrafica_custom]
     *                              [descrizione_anagrafica_custom]   
     *                              [metadati extra]     
     * @return esito     
     */
    public function ws_createAnagraficaCustom($param);
    
    /**
     * Permette la modifica di una anagrafica custom
     * @param type $param
     *                       - TOKEN
     *                       - customId[]
     *                              TYPE_ID
     *                              COD_ENTE
     *                              COD_AOO
     *                              [codice_anagrafica_custom]
     *                       - customInfo[]                         
     *                              ENABLED
     *                              [descrizione_anagrafica_custom]        
     *                              [metadati extra]
     * @return esito     
     */
    public function ws_updateAnagraficaCustom($param);
    
    /**
     * Permette la modifica dei metadati di registrazione particolare del profilo di un Documento nel
     * @param type $param
     *                       - TOKEN
     *                       - DOCID
     *                       - RELATED[]
     * @return esito     
     */
    public function ws_addRelated($param);
}
