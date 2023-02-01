<?php

require_once ITA_LIB_PATH . '/itaPHPGit/Git.php';


/**
 *
 * CLient Git Wrapper
 *
 * PHP Version 5
 *
 * @category   extended library 
 * @package    lib/itaPHPGit
 * @author     Biagioli/Moscioni
 * @copyright  
 * @license
 * @version    30.03.2017
 * @link
 * @see
 * 
 */
class itaGit {
    
    const ERR_MISSING_REMOTE_SOURCE = "Sorgente remota Git non specificata";
    const ERR_MISSING_WORKING_DIR = "Working directory non specificata";
    const ERR_MISSING_GIT_EXECUTABLE = "Percorso eseguibile Git non specificato";
    const ERR_NULL_REPOSITORY = "Repository Git non inizializzato";
    
    private $remoteSource;
    private $workingDir;
    private $defaultRemote;
    private $defaultBranch;
    private $repository;
    
    /**
     * Costruttore
     * @param array $info Parametri inizializzazione:
     *      remoteSource: url repository remoto
     *      workingDir: Working Directory
     *      gitBinPath: Path eseguibile git
     */
    public function __construct($info) {     
        $this->setRemoteSource($info['remoteSource']);
        $this->setWorkingDir($info['workingDir']);
        $this->setGitBinPath($info['gitBinPath']);
        $this->setDefaultRemote($info['defaultRemote']);
        $this->setDefaultBranch($info['defaultBranch']);
        
        // Inizializza reporitory
        $this->repository = new GitRepo($this->getWorkingDir(), true, false);
    }
       
    /**
     * Clona repository remoto
     * @param safeMode se true, non effettua operazione se la cartella esiste (senza dare errore)
     */
    public function cloneRemoteRepository($safeMode = false) {        
                
        // Precondizioni
        $this->doPreconditions();
        
        // In modalità "safe", se la working directory non esiste, esce senza fare nulla
        if ($safeMode && $this->isWorkingDirAValidGitRepository()) {
            return;
        }
        
        // Clona repository        
        $this->repository->clone_remote($this->remoteSource, '-b ' . $this->getDefaultBranch());          
    }
    
    /**
     * Esegue istruzione 'git status'
     * @return string Messaggio (testo)
     */
    public function status() {

        // Precondizioni
        $this->doPreconditions();
        
        // Esegue istruzione 'git status'
        $strResult = $this->repository->status();
        
        return $strResult;
    }
    
    /**
     * Restituisce elenco delle modifiche da applicare
     * @param boolean $cloneRepositoryIfNotExists Se true, effettua operazione di clone in assenza della working directory
     * @return array Array delle modifiche
     */
    public function fetch($cloneRepositoryIfNotExists = false) {
        
        // Precondizioni
        $this->doPreconditions();
        
        // Se la working directory non è un repository git, ed è impostato il flag $cloneRepositoryIfNotExists, 
        // lancia comando clone prima di chiamare la fetch
        if ($cloneRepositoryIfNotExists && !$this->isWorkingDirAValidGitRepository()) {
            $this->cloneRemoteRepository();
        }
        
        // Aggiorna indice
        $strResult = $this->repository->fetch();
        
        // Ricava differenze
        //$strResult = $this->repository->run("diff --name-only ..origin");
        $strResult = $this->repository->run("diff --name-status ..origin/" . $this->getDefaultBranch());
        
        // Adatta risultato
        $result = array();        
        
        if (strlen($strResult) > 0) {
            $result = explode("\n", $strResult);
        }        
        
        $resultFinal=array();
        foreach ($result as $value) {
            if (strlen($value) > 0) {
                $resultFinal[] = explode("\t", $value);
            }            
        }
                
        return $resultFinal;
    }        
    
    /**
     * Effettua operazione di checkout del branch specificato
     * @param string $branch Nome branch
     * @param boolean $cloneRepositoryIfNotExists Se true, effettua operazione di clone in assenza della working directory
     */
    public function checkout($branch, $cloneRepositoryIfNotExists = false) {
        
        // Precondizioni
        $this->doPreconditions();
        
        // Se la working directory non è un repository git, ed è impostato il flag $cloneRepositoryIfNotExists, 
        // lancia comando clone prima di chiamare la fetch
        if ($cloneRepositoryIfNotExists && !$this->isWorkingDirAValidGitRepository()) {
            $this->cloneRemoteRepository();
        }
        
        // Controlla che il parametro branch sia valorizzato
        if (!$branch) {
            throw new Exception(self::ERR_MISSING_REMOTE_SOURCE);
        }
        
        // Effettua operazione di checkout
        $this->repository->checkout($branch);
    }
    
    /**
     * Allinea Working Directory
     * @param string $remote Nome remote (se non specificato, prende 'origin')
     * @param string $branch Se non specificato, scarica dall'unico branch
     * @param boolean $force Se true, effettua una pull forzata
     * @param boolean $cloneRepositoryIfNotExists Se true, effettua operazione di clone in assenza della working directory
     * @return string Esito testuale dell'operazione
     */
    public function pull($remote = "", $branch = "", $force = false, $cloneRepositoryIfNotExists = false) {
        
        // Imposta parametri di default, se presenti
        if ((!$remote) && $this->getDefaultRemote()) {
            $remote = $this->getDefaultRemote();
        }
        if ((!$branch) && $this->getDefaultBranch()) {
            $branch = $this->getDefaultBranch();
        }
        
        // Precondizioni
        $this->doPreconditions();
        
        // Se la working directory non è un repository git, ed è impostato il flag $cloneRepositoryIfNotExists, 
        // lancia comando clone prima di chiamare la fetch
        if ($cloneRepositoryIfNotExists && !$this->isWorkingDirAValidGitRepository()) {
            $this->cloneRemoteRepository();
        }
        
        // Controlla se deve essere fatta una pull forzata
        if ($force) {
            $strResult = $this->repository->run("reset --hard HEAD");
        }

        /*
         * Controllo se sono su un ramo differente rispetto a $branch.
         */

        if ($branch != $this->getCurrentBranch()) {
            /*
             * Rimuovo eventuali modifiche per poter effettuare il checkout.
             */

            $this->repository->run('reset --hard HEAD');
            $this->repository->run("checkout $branch");
        }
        
        // Allinea Working Directory
        $strResult = $this->repository->pull($remote, $branch);        
        
        return $strResult;
    }
    
    /**
     * Restituisce tag corrispondente all'ultima build
     * @return string Tag name
     */
    public function getCurrentBuildTag() {
        return $this->repository->run('describe --abbrev=0 --tags');
    }
    
    /**
     * Restituisce elenco di tutti i tag
     * @return array
     */
    public function getAllTags() {
        return $this->repository->list_tags();        
    }
    
    /**
     * Effettua la commit di tutti i file presenti nel repository
     * @param string $msg Messaggio della commit
     * @return string Messaggio di risposta della commit
     */
    public function commit($msg) {
        $this->repository->add();   // Aggiunge tutti i file in stage
        $strResult = $this->repository->commit($msg, false);
        return $strResult;       
    }
    
    /**
     * Allinea Working Directory
     * @param string $remote Nome remote (se non specificato, prende 'origin')
     * @param string $branch Se non specificato, scarica dall'unico branch
     * @return string Esito testuale dell'operazione
     */
    public function push($remote = "", $branch = "") {
        
        // Imposta parametri di default, se presenti
        if ((!$remote) && $this->getDefaultRemote()) {
            $remote = $this->getDefaultRemote();
        }
        if ((!$branch) && $this->getDefaultBranch()) {
            $branch = $this->getDefaultBranch();
        }
        // Precondizioni
        $this->doPreconditions();
        
        // Invia modifiche
        $strResult = $this->repository->push($remote, $branch);        
        
        return $strResult;
    }

    /**
     * Ritorna il ramo corrente.
     * @return string Nome del ramo attuale.
     */
    public function getCurrentBranch() {
        return $this->repository->active_branch();
    }

    /**
     * Ritorna la lista di tutti i rami.
     * @return array Array dei rami.
     */
    public function getAllBranches() {
        return array_merge($this->getLocalBranches(), $this->getRemoteBranches());
    }

    /**
     * Ritorna la lista dei rami locali.
     * @return array Array dei rami.
     */
    public function getLocalBranches() {
        return $this->repository->list_branches();
    }

    /**
     * Ritorna la lista dei rami nei remotes.
     * @return array Array dei rami.
     */
    public function getRemoteBranches() {
        return $this->repository->list_remote_branches();
    }

    /**
     * Effettua un fetch semplice.
     */
    public function fetchOnly() {
        return $this->repository->fetch();
    }

    private function doPreconditions() {
        if (!$this->getRemoteSource()) {
            throw new Exception(self::ERR_MISSING_REMOTE_SOURCE);
        }
        if (!$this->getWorkingDir()) {
            throw new Exception(self::ERR_MISSING_WORKING_DIR);
        }
        if (!$this->getGitBinPath()) {
            throw new Exception(self::ERR_MISSING_GIT_EXECUTABLE);
        }
    }
    
    private function isWorkingDirAValidGitRepository() {
        return file_exists($this->getWorkingDir() . '/.git');
    }
    
    public function getRemoteSource() {
        return $this->remoteSource;
    }

    public function getWorkingDir() {
        return $this->workingDir;
    }
   
    public function getGitBinPath() {
        return Git::get_bin();
    }

    public function setRemoteSource($remoteSource) {
        $this->remoteSource = $remoteSource;
    }

    public function setWorkingDir($workingDir) {
        $this->workingDir = $workingDir;
    }
    
    public function setGitBinPath($gitBinPath) {        
        Git::set_bin($gitBinPath ? escapeshellarg($gitBinPath) : '');
    }
    
    public function getDefaultRemote() {
        return $this->defaultRemote;
    }

    public function getDefaultBranch() {
        return $this->defaultBranch;
    }

    public function setDefaultRemote($defaultRemote) {
        $this->defaultRemote = $defaultRemote;
    }

    public function setDefaultBranch($defaultBranch) {
        $this->defaultBranch = $defaultBranch;
    }

}

?>