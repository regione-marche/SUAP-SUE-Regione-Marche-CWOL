<?php
/**
 * Questa classe permette di creare una tabella HTML
 *
 * @author 		Andrea Vallorani <andrea.vallorani@email.it>
 */
class AV_Tabella extends AV_Tag{
  
  private $bicolor = array();
  private $cellePerRiga;
  private $statoRiga = 0;
  private $limitazioneRiga = array();
  public $titolo;
  public $head;
  public $body;
  public $foot;
	
  function __construct($cellePerRiga=1,$attributi=array(),$bicolor=array()){
  	  parent::__construct('table');
  	  $this->inline = FALSE;
  	  $this->limitazioneRiga['righe'] = 0;
  	  $this->limitazioneRiga['celle'] = 0;
  	  $this->cellePerRiga = $cellePerRiga;
  	  $this->inserisci($this->titolo = new AV_Tag('caption'));
  	  $this->inserisci($this->head = new AV_Tag('thead'));
   	  $this->head->inline = FALSE;
  	  $this->inserisci($this->foot = new AV_Tag('tfoot'));
  	  $this->foot->inline = FALSE;
  	  $this->body = new AV_Tag('tbody');
  	  $this->body->inline = FALSE;
  	  $this->inserisci($this->body);
	  if($attributi) $this->aggiungiAttributi($attributi);
	  if($bicolor) $this->bicolor = $bicolor;   	
	  $this->body->add(new AV_RigaTab());	  
  }

  function prendiContenuto(){
      if(!count($this->titolo->contenuto)) unset($this->contenuto[0]);  
      if(!count($this->head->contenuto)) unset($this->contenuto[1]);
      if(!count($this->foot->contenuto)) unset($this->contenuto[2]);
	  return parent::prendiContenuto();
  } 
  
  public function setRiga($num){ if(is_int($num)) $this->cellePerRiga = $num; }

  public function addTitolo($txt){			
		$this->titolo->svuota();
		$this->titolo->add($txt);	
  }
   
  public function addHead($titoli){
   		$args = (is_array($titoli)) ? $titoli : func_get_args();
		$args = array_pad($args, $this->cellePerRiga, '');
		$this->head->svuota();
		$this->head->add(new AV_RigaTab($args));
		$this->head->contenuto[0]->rigaTitolo();	
  }
  public function addFoot($voci,$spanO=1){
		$this->foot->svuota();
		$this->foot->add($riga=new AV_RigaTab());	
   		foreach($voci as $cella){
			$riga->add($cella);
	  		$riga->last()->span('o',$spanO);
		}
  }
  /**
  * Inserisce un oggetto AV_RigaTab nella tabella
  * 
  * @param 	AV_RigaTab 	$riga  oggetto da inserire
  * @access private
  */
  public function add($oggetto,$spanO=0,$spanV=0,$nowrap=FALSE){

       if($this->statoRiga >= $this->cellePerRiga){
         if($this->limitazioneRiga['righe']) {
               $this->statoRiga = $this->limitazioneRiga['celle'];
               $this->limitazioneRiga['righe']--;
         }
         else $this->statoRiga = 0;
         $r = new AV_RigaTab($oggetto);
         $this->body->add($r);
         $this->statoRiga++;
       }
       else{
         $this->body->last()->add($oggetto);
         $this->statoRiga++;
       }

	$riga = $this->body->last();

        if($spanO==-1){
            //echo "<br>$this->cellePerRiga $this->statoRiga<br>";
            $spanO = $this->cellePerRiga-$this->statoRiga+1;
            //echo $spanO.' ';
        }
        
	if($spanO > 1){
	  $riga->last()->span('o',$spanO);
	  $this->statoRiga += $spanO-1;
	} 
	if($spanV > 1){
	  $riga->last()->span('v',$spanV);
	  $this->limitazioneRiga['celle']++;
	  $this->limitazioneRiga['righe'] = $spanV-1;
	} 
  	if($this->bicolor and !$riga->isTitolo){
  		$i = count($this->contenuto)%2;
		$riga->aggiungiAttributo('style','background-color:'.$this->bicolor[$i]);
	}  
	if($nowrap) $riga->last()->aggiungiAttributo('nowrap','nowrap');
	return $riga;
  }
  
  public function r($n){
  	return $this->body->contenuto[$n];  
  }
  
} //end class AV_Tabella


/**
 * Questa classe permette di creare una riga di una tabella HTML
 *
 * @package 	Web
 * @subpackage 	HTML
 * @version     1.2.1 - 20/07/2006 16.09.00
 * @since       PHP 5.0.0
 * @author 		Andrea Vallorani <andrea.vallorani@email.it>
 */
class AV_RigaTab extends AV_Tag{
  
  public $isTitolo=FALSE;
  
  //$contenuto può essere o una stringa o un oggetto o array di entrambi 
  function __construct($contenuto=''){
    parent::__construct('tr');  
	if(is_array($contenuto)){
	  	foreach($contenuto as $oggetto) $this->add($oggetto);
	}
	elseif($contenuto) $this->add($contenuto);
	
	//la stampa nel file la voglio su più righe
	$this->inline = FALSE;
  }
  
  /**
  * Permette di inserire una cella nella riga
  * 
  * @param 	mixed 	$obj  Può essere un oggetto o una stringa ma non un array.
  * @param 	int 	$pos  Se 1 la cella viene posizionata alla fine, se 0 all'inizio della riga
  * @return mixed 	In caso di successo ritorna la cella inserita altrimenti 0 
  * @access public
  */
  public function add($obj='',$pos=1){
    if(is_array($obj)) return 0;
    if(is_a($obj,"AV_Cella")) $cella = $obj;
	else $cella = new AV_Cella($obj);
  	if($pos){
  		$this->inserisci($cella);  
	}
	else{
	  	array_unshift($this->contenuto,$cella); //inserisco in prima posizione
	}	  
  	return $cella; //ritorno la posizione dell'ultimo elemento inserito
  }
  
  /**
  * Trasforma la riga in una riga di titolo
  * 
  * @return void 
  * @access public
  */
  public function rigaTitolo(){
  	foreach($this->contenuto as $cella) $cella->cellaTitolo();  
  	$this->isTitolo=TRUE;
  	$this->impostaClass('intTab');
  }
  
  public function c($n){
  	return $this->contenuto[$n];  
  }
  
} //end class AV_RigaTab


/**
 * Questa classe permette di creare una cella di una tabella HTML
 *
 * @package 	Web
 * @subpackage 	HTML
 * @version     1.3.0 - 08/09/2006 16.17.30
 * @since       PHP 5.0.0
 * @author 		Andrea Vallorani <andrea.vallorani@email.it>
 */
class AV_Cella extends AV_Tag{
  
  function __construct($contenuto,$titolo=FALSE){
  	$this->inserisci($contenuto);
  	if($titolo) parent::__construct('th');
  	else parent::__construct('td');
  }
  
  public function cellaTitolo(){
    $this->tag = 'th';
  }
  
  public function span($come,$diQuanto=2){
    if($come=='v')$come='rowspan';
    elseif($come=='o') $come='colspan';
    if(is_numeric($diQuanto))$this->aggiungiAttributo($come,$diQuanto);
  }
  
  public function align($align='left'){
	$this->aggiungiAttributo('align',$align);
  }
  
  public function creaLink($url,$title=''){
    if($title) $title = array('title'=>$title);
    if(is_string($this->contenuto[0])){
	   $this->contenuto[0] = new AV_Link($this->contenuto[0],$url,$title);
	}
  }

} //end class AV_Cella


/**
 * Permette di creare un oggetto di tipo order o unorder list
 *
 * @package 	Web
 * @version     2.0.2 - 26/01/2009 19.26.37
 * @author 		Andrea Vallorani <andrea.vallorani@email.it>
 */
class AV_List extends AV_Tag{
 
  static protected $ULcounter=1;
  
  /**
  * Costruttore
  * @param array $elementi Array degli elementi da inserire nella lista
  * @param array $conf Configurazione: 
  						- $conf['ord'] = se presente e diverso da zero significa che si vuole una lista ordinata, altrimenti la lista sarà non ordinata
  						- $conf['ordinato'] = equivalente a $conf['ord']
  						- $conf['id'] = id da assegnare ala lista
  						- $conf['liClass'] = classe da associare agli elementi della lista
  						- $conf['liId'] = id da assegnare ad ogni elemento (all'id sarà accodato un numero progressivo per garantire l'univocità del valore dell'attributo')
  						
  * @param int $colonne Numero di colonne su cui suddivedere la lista
  * @return void 
  */
  function __construct($elementi=array(),$conf=array(),$colonne=1){
  	$this->inline = FALSE;
  	$i=0;
  	if($conf['id']) $this->impostaId($conf['id']);
  	else $this->impostaId('ul'.self::$ULcounter);
  	if($colonne>1){
		$tot = count($elementi);
		$altezzaRiga = '1.2';
		$maxCol = (int)($tot/$colonne);
		$widthCol = (int)(100/$colonne);
		$resto = $tot % $colonne;
		for($i=1;$i<=$colonne;$i++){
			$countCol[$i] = $maxCol;
			if($resto>0){
				$countCol[$i]++;
				$resto--;
			}	
		}
		$this->css="#".$this->prendiAttributo('id')." li{height:".$altezzaRiga."em}\n";
		if($countCol[2]) $this->css.="#".$this->prendiAttributo('id')." .primoCol{margin-top:-".number_format(($countCol[1]*$altezzaRiga),1)."em}\n";
	}
	$y=1;
	foreach((array)$elementi as $elemento){
	 	if($elemento){
		   	$li = new AV_Tag('li');
		   	$li->inline = FALSE;
		   	$li->inserisci($elemento);
		   	$classe = $conf['liClass'];
		   	if($colonne>1){
		   	 	$classe .= " col$y";
		   	 	if($primo){
					$classe .= " primoCol";
					$primo=false;
				}
		   	 	$countCol[$y]--;
				if($countCol[$y]==0){
					if($y>1) $this->css.="#".$this->prendiAttributo('id')." .col$y{margin-left:".($widthCol*($y-1))."%}\n";
					$y++;
					$primo=true;
				} 
			}
		   	if($conf['liId']) $li->impostaId($conf['liId'].self::$ULcounter.$i);
		   	if($classe) $li->impostaClass($classe);
		   	if($i==0){
				$li->impostaClass("primo ".$li->prendiAttributo('class'));	
			} 
		  	$this->inserisci($li);
		  	$i++;			
		}

	}
	self::$ULcounter++;
  	$tag = ($conf['ordinato'] or $conf['ord']) ? "ol" : "ul";
  	parent::__construct($tag);
  }
  
  function add($cont,$aCapoHTML=FALSE){
   	  $li = new AV_Tag('li');
   	  $li->add($cont);
  	  parent::add($li);  
  }
  
  function getCss(){ return $this->css; }
  
} //end class AV_List


/**
 * Permette di creare un oggetto di tipo <img>
 *
 * @package 	Web
 * @subpackage 	HTML
 * @version     1.2.1 - 17/08/2006 18.47.37
 * @since       PHP 5.0.0
 * @author 		Andrea Vallorani <andrea.vallorani@email.it>
 */
class AV_ImgHTML extends AV_Tag{
	
  static public $imgDir = 'img';
		
  function __construct($path,$titolo='img',$alt='',$attributi=array()){
    parent::__construct('img');
	$attributi['src']= AV_WebController::$dir . self::$imgDir.'/'.$path;
	if(!$alt)$alt=$titolo;
	$attributi['alt']=$alt;
	$attributi['title']=$titolo;	
	$this->aggiungiAttributi($attributi);
  }
	
}
class AV_ImgWeb extends AV_Tag{
	
  static public $imgDir = 'img';
		
  function __construct($path,$titolo='img',$alt='',$attributi=array()){
    parent::__construct('img');
    if(substr($path,0,4)=='http' or $attributi['absPath']){
     	if(isset($attributi['absPath'])) unset($attributi['absPath']);
		$attributi['src']= $path;	
	} 
	else $attributi['src'] = AV_WebController::$dir . self::$imgDir.'/'.$path;
	if(!$alt)$alt=$titolo;
	$attributi['alt']=$alt;
	$attributi['title']=$titolo;	
	$this->aggiungiAttributi($attributi);
	$this->shortTag = TRUE;
  }
	
}
class AV_Img extends AV_Tag{
	
  static public $imgDir = 'img';
		
  function __construct($path,$abs=false,$alt='',$attributi=array()){
    parent::__construct('img');
	$attributi['src']= ($abs) ? $path : AV_WebController::$dir . self::$imgDir.'/'.$path;
	if($alt) $attributi['alt']=$attributi['title']=$alt;
	$this->aggiungiAttributi($attributi);
	$this->shortTag = TRUE;
  }
	
}

/**
 * Permette di creare un oggetto di tipo <a>
 *
 * @package 	Web
 * @subpackage 	HTML
 * @version     1.1.1 - 17/08/2006 15.21.02
 * @since       PHP 5.0.0
 * @author 		Andrea Vallorani <andrea.vallorani@email.it>
 */
class AV_Link extends AV_Tag{
  
  function __construct($contenuto,$url='',$attributi=array()){
	    parent::__construct('a');
		$this->inserisci($contenuto);
		if($url) $attributi['href']=$url;	
		$this->aggiungiAttributi($attributi);
  }
  
}//end AV_Link

class AV_Ancora extends AV_Tag{
  
  function __construct($nome){
	    parent::__construct('a');
		$this->inserisci('');	
		$this->aggiungiAttributo('name','a'.$nome);
  }
  
}//end AV_Link

/**
* Permette di creare un oggetto di tipo <div>
*
* @package 		Web
* @subpackage 	HTML
* @version     	1.1.1 - 17/08/2006 15.21.02
* @since       	PHP 5.0.0
* @author 		Andrea Vallorani <andrea.vallorani@email.it>
*							
*/
class AV_Div extends AV_Tag{
  
  function __construct($testo='',$id='',$class=''){
	  parent::__construct('div');
	  $this->inline = FALSE;
	  if($testo)$this->inserisci($testo);
	  if($id) $this->impostaId($id);
	  if($class) $this->impostaClass($class);
  }
}//end AV_Div

class AV_DivClass extends AV_Tag{
  
  function __construct($class,$testo=''){
	  parent::__construct('div');
	  if(is_string($testo) and $testo!='') $this->inline = TRUE;
	  else $this->inline = FALSE;
	  if($testo)$this->inserisci($testo);
	  $this->impostaClass($class);
  }
}//end AV_Div

class AV_DivId extends AV_Tag{
  
  function __construct($id,$testo=''){
	  parent::__construct('div');
	  if(is_string($testo) and $testo!='') $this->inline = TRUE;
	  else $this->inline = FALSE;
	  if($testo)$this->inserisci($testo);
	  $this->impostaId($id);
  }
}//end AV_Div
/**
* Permette di creare un oggetto di tipo <span>
*  
* @package 		Web
* @subpackage 	HTML
* @version     	1.0.0 - 21/08/2006 12.57.20
* @since       	PHP 5.0.0
* @author 		Andrea Vallorani <andrea.vallorani@email.it>							
*/
class AV_Span extends AV_Tag{
  
  function __construct($class='',$testo=''){
	  parent::__construct('span');
	  $this->inline = TRUE;
	  if($testo)$this->inserisci($testo);
	  if($class) $this->impostaClass($class);
  }
}//end AV_Span
class AV_SpanId extends AV_Tag{
  
  function __construct($id='',$testo=''){
	  parent::__construct('span');
	  $this->inline = TRUE;
	  if($testo)$this->inserisci($testo);
	  if($id) $this->impostaId($id);
  }
}//end AV_Span

class AV_Riga extends AV_Tag{
  function __construct($class=''){
	  parent::__construct('hr');
	  $this->inline = TRUE;
	  if($class) $this->impostaClass($class);
  }
}//end AV_Span

/**
 * Permette di creare un oggetto di tipo iframe
 *
 * @package 	Web
 * @subpackage 	HTML
 * @version     1.0.0 - 08/01/2007 16.42.00
 * @since       PHP 5.0.0
 * @author 		Andrea Vallorani <andrea.vallorani@email.it>
 */
class AV_IFrame extends AV_Tag{
  
  function __construct($url,$altriAttributi=array()){
  	$this->inline = FALSE;
  	$this->aggiungiAttributo('src',$url);
  	$this->aggiungiAttributi($altriAttributi);
  	parent::__construct("iframe");
  }
} //end class AV_IFrame

class AV_Fieldset extends AV_Tag{
  
  function __construct($titolo='',$attributi=array()){
  	$this->inline = FALSE;
  	parent::__construct("fieldset");
  	if($titolo) $this->add("<legend>$titolo</legend>");
  	$this->aggiungiAttributi($attributi);
  }
} //end class AV_IFrame

?> 