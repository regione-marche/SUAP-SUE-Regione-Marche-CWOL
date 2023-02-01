<?php
/**
 * Classe base per un tag HTML.
 *
 * @author 		Andrea Vallorani <andrea.vallorani@email.it>
 */
class AV_Tag{
  
     /**
     * Nome del tag creato.
     * @var string
     */
    public $tag;
    
    /**
     * Contenuto del tag. Ogni elemento del vettore rappresenta una riga nell'output 
     * @var array
     */
    public $contenuto = array();
    
    /**
     * Indica se i contenuti dell'oggetto devono essere stampati su una o pi� righe.
     * TRUE = su una linea , FALSE = su pi� linee. (per default � TRUE)
     * @var boolean
     */
    public $inline = TRUE;

    /**
     * Attributi del tag. La chiave dell'array indica il nome dell'attributo mentre il suo contenuto indica
     * il valore dell'atributo. Ad esempio array('id'=>'prova') indica un attributo di nome id con valore prova.
     * @var array
     */
    protected $attributi = array();

    /**
     * Numero di ripetizioni di tab. Indica quanti tab devono essere stampati prima di ogni riga del tag.
     * Serve per stampare in maniera ordinata le righe.
     * @var int
     */
    public $nTab = 0;
    
    /**
     * Indica se i tabs devono essere stampati prima dell'oggetto tag.
     * @var boolean
     */
    public $usaTab = true;

    /**
     * Carattere da usare per la tabulazione
     * @var string
     */
    static public $tab = "\11";

    /**
     * Carattere da usare per il ritorno a capo. Default(\r\n per Windows)
     * @var string
     */
    static public $lineEnd = "\15\12";

    /**
     * Commento da inserire prima del tag
     * @var string
     */
    public $commento;
 
     /**
     * Se true e vuoto deve essere contratto
     * @var boolean
     */   
    public $shortTag=FALSE;
    
    /**
     * Construtor
     * Attenzione : se nel metodo figlio si ridefinisce il costruttore bisogna preoccuparsi 
	 * di settare la variabile tag.
     * o chiamando il costruttore padre :  parent::__construct($nomeTag);
     * o aggiungendo l'istruzione $this->tag = $nomeTag;
     *
     * @param string $nomeTag Nome del tag da creare
     */
    function __construct($nomeTag){ $this->tag = $nomeTag; }

    /**
     * Permette di aggiungere elementi all'interno del tag.
     * 
     * @param 	mixed 	$content  Pu� essere un oggetto AV_Tag o una stringa o un array di oggetti AV_Tag e/o stringhe
     * @param 	bool 	$aCapoHTML  Se TRUE inserisce dopo $content un ritorno a capo HTML (<br />)
     * @return 	object 	Ritorna il riferimento all'ultimo elemento inserito nel contenuto del tag se questi � un oggetto
     */
    public function add($content,$aCapoHTML=FALSE){
        $this->inserisci($content);
        if($aCapoHTML)$this->aCapo();
        if(is_object($content)) return $content;
        else{
	        $oggetto = end($this->contenuto);
	        if(is_object($oggetto)) return $oggetto;			
		}
    }

    /**
     * Aggiunge una stringa all'interno del tag dopo averla convertita nel charset con cui la pagina � costruita
     * 
     * @param 	string 	$str  Stringa da inserire nel tag
     * @return 	void
     */
    public function addText($str){
	 	$this->inserisci($str);
	}
    
    /**
     * Permette di aggiungere un elemento all'inizio del tag
     * @return 	object 	Ritorna il riferimento all'oggetto inserito
     */
    public function addFirst($content,$aCapoHTML=FALSE){
     	if(!is_array($content)){
	        array_unshift($this->contenuto,$content);
	        if($aCapoHTML)$this->aCapo();
			return $this->contenuto[0];
		}
		else throw new AV_Exception("Si pu� inserire solo un elemento alla volta con la funzione addFirst()");
    }
    
    /**
     * Ritorna l'ultimo elemento inserito nel tag
     * @return 	mixed Ultimo elemento
     */
	public function last(){ return end($this->contenuto); }
	
    /**
     * Ritorna il contenuto del tag
     * @return 	string 	Contenuto
     */   
    public function prendiContenuto(){ return $this->parsingContenuto($this->tag); }
	
	/**
     * Come la funzione prendiContenuto() solo che stampa a video il risultato
     * @return 	void
     */    
	public function stampa(){ echo $this->prendiContenuto(); }
    
    /**
     * Inserisce n ritorni a capo nel tag
     * 
     * @param   $n   Numero di a capo
     * @return  void
     */
    public function aCapo($n=1){
        $tagEnd = ' />';
        $this->inserisci(str_repeat("<br class=\"ita-br\"$tagEnd", $n));  	     
    }
      
	/**
     * Svuota il contenuto del tag
     * 
     * @return   void
     */  
    public function svuota(){ $this->contenuto = array(); } 
	
    /**
     * Ritorna una stringa contenente le tab da stampare per l'oggetto
     * 
     * @return   string
     */
    protected function prendiTabs(){ if($this->usaTab) return str_repeat(self::$tab, $this->nTab); }

    /**
     * Ritorna gli attributi dell'oggetto in un'unica stringa separati da uno spazio
     * @return   string  Stringa di attributi
     */
    public function prendiAttributi(){ return $this->prendiStringaAttributi($this->attributi); }
    
    /**
     * Stringa generica di attributi
     * @param 	array	Attributi
     * @return   string  Stringa di ttributi
     */
    protected function prendiStringaAttributi($attr){
     	$strAttr = '';
        foreach ($attr as $key => $value){
            $strAttr .= ' ' . $key . '="' . $value . '"';
        }
        return trim($strAttr);
    }

    /**
     * Ritorna un valido array di attributi, dove le chiavi dell'array sono i nomi degli attributi e i campi dell'array i valori
     * @param    mixed   $attributes  stringa o array associativo
     */
    protected function parsingAttributi($attributes){
        if (is_array($attributes)) {
            $ret = array();
            foreach ($attributes as $key => $value) {
                if (is_int($key)) {
                    $key = $value = strtolower($value);
                } else {
                    $key = strtolower($key);
                }
                $ret[$key] = htmlspecialchars($value,ENT_COMPAT,'ISO-8859-1');
            }
            return $ret;

        } elseif (is_string($attributes)) {
            $preg = "/(([A-Za-z_:]|[^\\x00-\\x7F])([A-Za-z0-9_:.-]|[^\\x00-\\x7F])*)" .
                "([ \\n\\t\\r]+)?(=([ \\n\\t\\r]+)?(\"[^\"]*\"|'[^']*'|[^ \\n\\t\\r]*))?/";
            if (preg_match_all($preg, $attributes, $regs)) {
                for ($counter=0; $counter<count($regs[1]); $counter++) {
                    $name  = $regs[1][$counter];
                    $check = $regs[0][$counter];
                    $value = $regs[7][$counter];
                    if (trim($name) == trim($check)) {
                        $arrAttr[strtolower(trim($name))] = strtolower(trim($name));
                    } else {
                        if (substr($value, 0, 1) == "\"" || substr($value, 0, 1) == "'") {
                            $value = substr($value, 1, -1);
                        }
                        $arrAttr[strtolower(trim($name))] = htmlspecialchars(trim($value),ENT_COMPAT,'ISO-8859-1');
                    }
                }
                return $arrAttr;
            }
        }
    } // end func parsingAttributi


	/**
	 * Permette di impostare velocemente nel tag l'attributo id con il valore fornito
	 * @param    string   $id  Valore del campo id
	 */
	public function impostaId($id){ $this->aggiungiAttributo('id',$id); }
	
	/**
	 * Permette di impostare velocemente nel tag l'attributo class con il valore fornito
	 * @param    string   $class  Valore del campo class
	 */
	public function impostaClass($class){ $this->aggiungiAttributo('class',$class); }

	/**
	 * Permette di accodare un valore all'attributo class
	 * @param    string   $class  Valore del campo class
	 */
    public function aggiungiClass($class){ $this->aggiungiAttributo('class',$this->prendiAttributo('class').' '.$class); }


   /**
     * Rimuove un dato attributo se esiste
     * 
     * @param     string    $attr   Nome attributo
     * @return    bool	esito
     */
    public function rimuoviAttributo($attr){
        $attr = strtolower($attr);
        if (isset($this->attributi[$attr])) {
            unset($this->attributi[$attr]);
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Ritorna il valore di un dato attributo
     * 
     * @param     string    $attr   Nome attributo
     * @return    string	Valore o NULL se non � stato trovato l'attributo
     */
    public function prendiAttributo($attr){
        $attr = strtolower($attr);
        if(isset($this->attributi[$attr])) return $this->attributi[$attr];
        else return NULL;
    }

    /**
     * Imposta o aggiunge uno o pi� attributi all'oggetto
     * @param    array   $attributi  array
     */
    public function aggiungiAttributi($attr){
        if(is_array($attr))$this->attributi = array_merge($this->attributi,$this->parsingAttributi($attr));
        else throw new AV_Exception("In ".__METHOD__." : il parametro attr deve essere un vettore");
    }
    
    /**
     * Imposta o aggiunge un attributo all'oggetto
     * @param    string   $nome  Nome dell'attributo
     * @param    string   $val   Valore dell'attributo
     */
    public function aggiungiAttributo($nome,$val){ $this->aggiungiAttributi(array($nome=>$val)); }

    /**
     * Imposta l'attributo style del tag in base allo stile fornito
     * @param    mixed   $attributes  stringa o array
     */
    public function impostaStile($stile){ 
	 	if(AV_WebController::getInstance()->progetto->pagina->usaStili()){
			$this->aggiungiAttributo('style',$stile); 	
		}
	}

    /**
     * Imposta lo stile di chiusura linea. Windows, Mac, Unix o altro.
     * 
     * @param   string  $style  "win", "mac", "unix" o altro.
     * @return  void
     */
    public function impostaFineLinea($style){
        switch ($style) {
            case 'win': self::$lineEnd = "\15\12"; break;
            case 'unix': self::$lineEnd = "\12"; break;
            case 'mac': self::$lineEnd = "\15"; break;
            default: self::$lineEnd = $style;
        }
    }
   
    /**
     * Itera l'array "contenuto", e ritorna una stringa con il contenuto del tag
     * 
     * @param string tag Nome del tag contenitore del testo 
     * @return  string
     */
    protected function parsingContenuto($tag){
        $righe =& $this->contenuto;
        
        $strAttr = $this->prendiAttributi();
        $strHtml = '';
        if($this->commento) $strHtml .= $this->prendiTabs()."<!-- ".$this->commento." -->".self::$lineEnd;
        $strHtml .= $this->prendiTabs()."<$tag";
	    if($strAttr) $strHtml .= " $strAttr";
	    
	    if($righe or !$this->shortTag){
	      $strHtml .= ">";
	      $tagEnd = '';
		  if(!$this->inline and $righe){
		    $strHtml.=self::$lineEnd;
		    $tagEnd = $this->prendiTabs();
		  } 
		  $tagEnd.="</$tag>";
		} 
	    else{
			$tagEnd = ' />';			
		}
		
        foreach($righe as $riga){
	        if (is_object($riga)) {           
	            if (is_subclass_of($riga, 'AV_Tag') or ($riga instanceof AV_Tag)) {
	                $riga->nTab = $this->nTab + 1;
	                if($this->inline) $riga->usaTab = FALSE;
	            }
	            if (method_exists($riga, 'prendiContenuto')) {
	                $strHtml .= $riga->prendiContenuto();
	            }
	        } else { 
	            //altrimenti inseriamo il testo cos� com'�
	            if(!$this->inline){
					if($this->usaTab) $strHtml .= $this->prendiTabs() . self::$tab;
					$strHtml .= $riga . self::$lineEnd;
				}  
	            else $strHtml .= $riga;
	        }
	    }
		
		$strHtml .= $tagEnd;
		if($this->usaTab) $strHtml .= self::$lineEnd;
	    return $strHtml;
    } // end func parsingContenuto
	
	protected function inserisci($oggetto,$filtroClasse=''){
		if(is_array($oggetto)){
		  foreach($oggetto as $elemento){
		    if($filtroClasse) $this->controlloClasse($oggetto,$filtroClasse);
			$this->contenuto[] = $elemento;
		  }
		}
		else{
		  if($filtroClasse) $this->controlloClasse($oggetto,$filtroClasse);
		  $this->contenuto[] =& $oggetto;
		} 
	}
	
	private function controlloClasse($oggetto,$classe){
	  	if(!is_a($oggetto,$classe)) throw new AV_Exception("L'oggetto ".__CLASS__." non pu� contenere un oggetto di tipo ".get_class($oggetto)." ma solo oggetti di tipo $classe");
	  	else return 1;
	}
	
} // end class AV_Tag

?>