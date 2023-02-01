<?php
/**
 * Questa classe permette di creare un elemento <form>
 *
 * @author Andrea Vallorani <andrea.vallorani@email.it>
 */

class AV_Form extends AV_Tag{
    /**
     * Queste costanti rappresentano le grandezze di default dei vari oggetti del form
     * se le grandezze non vengono specificate vengono presi questi valori
     */
    const inputL = '20' ; //larghezza input text
	const maxFileSize = '1048576' ; // 1 MB
	const textAreaW = '40' ; //larghezza textArea
	const textAreaH = '5' ;  //altezza textArea

	public $oggettiPerRiga;
	public $inCell = FALSE;
	private $statoRiga = 0;
	static private $contatore = 1;
	private $layout;
	private $sigla;
	
    /**
     * Construct
     *
     * @param string 	$action  	URL a cui vengono inviati i dati del form
     * @param string 	$id    		Nome del form
     * @param string 	$method  	Metodo di invio dei dati (post o get, default = post)
     * @param string 	$layout  	Organizzazione dei campi.Pu� valere 'table','line' o niente. (Default niente) 
     * @param string 	$enctype 	Tipo di codifica dei dati. Valori possibili sono:
	 *									- app = application/x-www-form-urlencoded
	 *									- data = multipart/form-data (da usare per l'upload di file)
	 *									- text = text/plain
     * @return void
     */
    function __construct($action,$oggettiPerRiga=2,$layout='table',$method='post',$id='',$enctype='')
    {
      	parent::__construct('form');
      	$this->inline = FALSE;
      	$this->layout = $layout;
      	$this->oggettiPerRiga = $oggettiPerRiga;
        $attributi = array('action'=>$action,'method'=>$method);
        if($id){
			//$attributi['id'] = 'form_'.$id;
              $attributi['id'] = $id;
			$this->sigla = $id;
		}
		switch($enctype){
		  	case 'app': $attributi['enctype']='application/x-www-form-urlencoded'; break;
		  	case 'data': $attributi['enctype']='multipart/form-data'; break;
		  	case 'text': $attributi['enctype']='text/plain'; break;
		}
        
        $this->aggiungiAttributi($attributi);
        
        switch($layout){
		  case 'table':
		  	$this->inserisci(new AV_Tabella($oggettiPerRiga,array('class'=>'fTable')));
		  break;
		  case 'fieldset':
		  	$this->fieldsetColonne = $oggettiPerRiga;
		  	if($oggettiPerRiga>1){
				$this->inserisci(new AV_Tabella($oggettiPerRiga));
		  		for($i=0;$i<$this->fieldsetColonne;$i++) $this->contenuto[0]->add(' ');	
			}
		  	break;
		}
    }
	
	/**
     * Inserisce un nuovo fieldset
     *
     * @param string $titolo Titolo del fieldset che verr� inserito nel tag legend
     * @param int $colonna Numero della colonna dove dovr� essere inserito il fieldset
     * @param array $attr Attributi da associare al fieldset
     * @param int $elPerRiga Numero di elementi per riga della tabella del fieldset
     * @return void
     */
    function addFieldset($titolo='',$colonna=1,$attr=array(),$elPerRiga=''){
     	$colonna--;
     	if($this->layout=='fieldset'){
     	 	if($this->contenuto[0]->body){
				$this->contenuto[0]->body->last()->contenuto[$colonna]->add($f1=new AV_Fieldset($titolo,(array)$attr));
			}
			else{
				$this->inserisci($f1=new AV_Fieldset($titolo.' ',(array)$attr));
			}
			$this->currentFieldset = $f1;				
			if(!$elPerRiga) $elPerRiga = $this->oggettiPerRiga;
			$f1->add(new AV_Tabella($elPerRiga,array('class'=>'fTable')));
		}
	}
	
	function stopFieldset(){
	 	if($this->contenuto[0]->body){
			$this->contenuto[0]->add($su=new AV_DivClass('submit'),2);
		}
		else $this->inserisci($su=new AV_DivClass('submit'));
		$this->currentFieldset = $su;
	}
		
	/**
     * Reimposta il numero di oggetti per riga. Non funziona se il layout � di tipo fieldset.
     *
     * @param int $n Nuovo numero
     * @return void
     */	
	function setOggettiPerRiga($n){
		switch($this->layout){
			case 'table': $this->contenuto[0]->setRiga((int)$n); break;
			case 'line': $this->oggettiPerRiga = (int)$n; break;
		}
	}
    
    function setLayout($val){
	 	$this->layout=$val;
		$this->inserisci(new AV_Tabella($this->oggettiPerRiga));
	}
    
    /**
     * Ritorna il contenuto in HTML del form
     *
     * @param boolena $soloBody Indica se tornare solo il corpo di form o anche il tag form (default=FALSE)
     * @return string HTML del form
     */	
    function prendiContenuto($soloBody=FALSE){
     	//$body = new AV_DivClass('formBody','&nbsp;');
		//$body->add($this->contenuto);
		//$this->svuota();
		//$this->inserisci($body);
		//return ($soloBody) ? $body->prendiContenuto() : parent::prendiContenuto();
        return parent::prendiContenuto();

    }
	
    public function add($oggetto,$spanO=0,$spanV=0){
      	if(is_object($oggetto) and $id=$oggetto->prendiAttributo('id')){
	      	if(array_key_exists($id,$this->contenuto)){
				throw new AV_Exception("Oggetto con attributo id='$id' gi� presente. Utilizzare un'altro valore per id");	
			}
		}
	  	switch($this->layout){
		  case 'table':
		   		if(is_object($oggetto) and $oggetto->prendiAttributo('type')=='hidden') $this->inserisci($oggetto); 
		   		else{
		   		 	if($this->inCell) $this->contenuto[0]->body->last()->last()->add($oggetto);
					else $this->contenuto[0]->add($oggetto,$spanO,$spanV);
				} 
		  break;
		  case 'fieldset':
		  		if(is_object($oggetto)) $attr = $oggetto->prendiAttributo('type');
		   		if(is_object($oggetto) and $attr=='hidden') $this->inserisci($oggetto);
				elseif(!is_object($this->currentFieldset->contenuto[1])) $this->currentFieldset->add($oggetto);
		   		else{
		   		 	$this->currentFieldset->contenuto[1]->add($oggetto,$spanO,$spanV);
				}
		  break;
		  case 'line':
		  	  if($this->statoRiga >= $this->oggettiPerRiga){
				 if(!(is_object($oggetto) and $oggetto->prendiAttributo('type')=='hidden')) $this->aCapo(); 
				 $this->statoRiga = 0;
			  }
		      $this->inserisci($oggetto);
			  if($spanO) $this->statoRiga+=$spanO;
			  else $this->statoRiga++;
		  break;
		}
	}

    /**
     * Aggiunge al contenuto del form un input text
     *
     * @param string 	$id      		Nome del box
     * @param string 	$label    		Label che lo precede
     * @param mixed  	$val     		Valore inserito di default
     * @param int    	$maxlenght 		Massimo numero di caratteri inseribili
     * @param int    	$size      		Lunghezza (in caratteri)
     * @param bool   	$disabilitato 	Indica se la text box � disabilitata
     * @param bool   	$soloLettura  	Indica se la text box � di sola lettura
     * @return void
     */
    function text($id,$label='',$val='',$maxlenght='',$size=self::inputL,$disabilitato=FALSE,$soloLettura=FALSE,$spanO='',$spanV='')
    {     
		if(is_numeric($maxlenght)) $attributi['maxlength'] = $maxlenght;
		if(is_numeric($size)) $attributi['size'] = $size;
		if($disabilitato) $attributi['disabled'] = "disabled";
		if($soloLettura) $attributi['readonly'] = "readonly";
		if($val)$attributi['value'] = $val;
		if($label)$this->add($this->creaLabel($id,$label));
        $this->add(new AV_Input('text',$this->sigla.$id,$attributi),$spanO,$spanV);
    }
    
    function textAndButton($id,$label='',$valText='',$maxlenght='',$size=self::inputL,$urlButton,$contButton,$spanO=''){
		if(is_numeric($maxlenght)) $attributi['maxlength'] = $maxlenght;
		if(is_numeric($size)) $attributi['size'] = $size;
		if($valText!=='' or $valText!==null)$attributi['value'] = $valText;
		if($label)$this->add($this->creaLabel($id,$label));
		$teb = new AV_DivClass('textAndButton',new AV_Input('text',$this->sigla.$id,$attributi));
		$teb->add($b=new AV_Button('button',$this->sigla.$id.'button',$contButton));
	   	$b->aggiungiAttributo('onclick',$urlButton);
        $this->add($teb,$spanO);	
	}

    /**
     * Aggiunge al contenuto del form un input submit e opzionalmente un tasto annulla
     *
     * @param  string  	$val     		Testo che appare sul bottone
     * @param  bool   	$disabilitato 	Indica se il bottone � disabilitata
     * @return void
     */
    public function submit($val='Invia',$annulla='',$disabilitato=FALSE,$spanO=''){     
     	if($this->layout=='fieldset') $this->stopFieldset();
		$this->add($div=new AV_DivClass('azioni'),$spanO);
      	if($annulla){
		    if(!($url = $_SERVER['REFERER'])) $url = $_SERVER['PHP_SELF'];
		    if(is_array($annulla)){
				$linkAnnulla = $annulla['url'];	
				$annulla = $annulla['label'];	
			}
			else $linkAnnulla='javascript:history.back();';
		    $div->add(new AV_Link($annulla,$linkAnnulla,array('class'=>'annulla_form')));
		}
		$attributi['value'] = $val;
		$attributi['class'] = 'invia_form';
		$attributi['id'] = "submit".self::$contatore++;
        $div->add($s=self::creaSubmit($val,$disabilitato,$attributi),$spanO);
        return $s;
    }
    
    static public function creaSubmit($val='Invia',$disabilitato=FALSE,$attributi=array()){
		if($disabilitato) $attributi['disabled'] = "disabled";
		$attributi['value'] = $val;
		$attributi['class'] = 'invia_form';
        return new AV_Input('submit',$attributi['id'],$attributi);		
	}
    /**
     * Aggiunge al contenuto del form un input reset
     *
     * @param  string  	$val     		Testo che appare sul bottone
     * @param  bool   	$disabilitato 	Indica se il bottone � disabilitata
     * @return void
     */
    function reset($val='Cancella',$disabilitato=FALSE)
    {     
		if($disabilitato) $attributi['disabled'] = "disabled";
		$attributi['value'] = $val;
 		$id = "bottoneReset".$this->contatore++;
        $this->add(new AV_Input('reset',$this->sigla.$id,$attributi));
    }
    
    /**
     * Aggiunge al contenuto del form un input file
     *
     * @param  string  	$val     		Testo che appare sul bottone
     * @param  bool   	$disabilitato 	Indica se il bottone � disabilitata
     * @return void
     */
    function file($id,$label='',$disabilitato=FALSE,$size='')
    {    
	  	$attributi = array(); 
		if($disabilitato) $attributi['disabled'] = "disabled";
		if($label)$this->add($this->creaLabel($id,$label));
		if($size) $attributi['size'] = $size;
        $this->add(new AV_Input('file',$this->sigla.$id,$attributi));
    }
    
    /**
     * Aggiunge una input password al form
     *
     * @param string 	$id      		the string used in the 'name' attribute
     * @param string 	$desc     		the string used as the label
     * @param int    	$maxlenght 		Massimo numero di caratteri inseribili
     * @param int    	$size      		an integer used in the 'size' attribute
     * @return void
     *
     * @access public
     */
    function password($id='password',$label='Password : ',$maxlenght='',$size=self::inputL,$spanO=0)
    {	
		if(is_numeric($maxlenght)) $attributi['maxlength'] = $maxlenght;
		if(is_numeric($size)) $attributi['size'] = $size;
		if($label)$this->add($this->creaLabel($id,$label));
        $this->add(new AV_Input('password',$this->sigla.$id,$attributi),$spanO);
    }
    
    /* Aggiunge al contenuto del form un input hidden
     *
     * @param  string  	$id     Identificativo del campo
     * @param  string   $val 	Valore del campo
     * @return void
     */
    function hidden($id,$val='')
    {     
		$attributi['value'] = $val;
        $this->add(new AV_Input('hidden',$this->sigla.$id,$attributi));
    }                      
               
	 /**
     * Aggiunge una textarea al form
     *
     * @param string 	$id      		the string used in the 'name' attribute
     * @param string 	$desc     		the string used as the label
     * @param int    	$maxlenght 		Massimo numero di caratteri inseribili
     * @param int    	$size      		an integer used in the 'size' attribute
     * @return void
     *
     * @access public
     */
    function textarea($id,$label='',$txt='',$rows=self::textAreaH,$cols=self::textAreaW,$attr=array(),$spanO=0,$spanV=0)
    {	
        if($label)$this->add($this->creaLabel($id,$label));
		$this->add(new AV_TextArea($this->sigla.$id,$txt,$rows,$cols,$attr),$spanO,$spanV);
    }
    
    /**
     * Aggiunge una select al form
     *
     * @param string 	$id      		Nome dell'oggetto
     * @param string 	$desc     		the string used as the label
     * @param int    	$maxlenght 		Massimo numero di caratteri inseribili
     * @param int    	$size      		an integer used in the 'size' attribute
     * @return void
     *
     * @access public
     */
    function select($id,$label='',$opzioni=array(),$default='',$size=1,$titolo='',$multi=FALSE,$spanO=0)
    {	
        if($label)$this->add($this->creaLabel($id,$label));
		$this->add(new AV_Select($this->sigla.$id,$opzioni,$default,$size,$titolo,$multi),$spanO);
    }
    
    function select2($id,$label='',$scelte=array(),$desc_scelte=array(),$default='',$size=1,$multi=FALSE,$spanO=0,$gruppi=array())
    {	
        if($label)$this->add($this->creaLabel($id,$label));
		$this->add(new AV_Select2($this->sigla.$id,$scelte,$desc_scelte,$default,$size,$multi,$gruppi),$spanO);
    }
    
    /**
     * Aggiunge un gruppo di elementi radio al form
     *
     *
     * @access public
     */
    function radio($id,$scelte,$label='',$inline=FALSE,$default=''){	
        $gruppoRadio = new AV_Tag('span');
        $n=count($scelte);
        $i=1;
        foreach($scelte as $value=>$desc){
          $attributi = array();
          if($value==$default) $attributi['checked'] = 'checked';
          $attributi['value'] = $value;
          $attributi['class'] = 'radio';
          $gruppoRadio->add(new AV_Input('radio',$this->sigla.$id,$attributi));
          $gruppoRadio->add(" $desc ");
          if(!$inline and $i<>$n) $gruppoRadio->aCapo();
          $i++;
		}
		if($label)$this->add($this->creaLabel($id,$label));
		$this->add($gruppoRadio);
    }
    
    function checkBox($scelte,$label='',$inLinea=FALSE,$spanO=0,$disp=1){
     	if($disp==1){
	        $gruppo = new AV_Span('scelte');
	        $gruppo->inline=FALSE;			
		}
		else $gruppo = new AV_Tabella($disp*2);
        foreach($scelte as $name=>$val){
          $attributi = array();
          if($val['check']) $attributi['checked'] = 'checked';
          if($val['disabled']) $attributi['disabled'] = 'disabled';
          if(empty($val['value'])) $val['value'] = 1;
          $attributi['value'] = $val['value'];
          $gruppo->add(new AV_Input('checkbox',$this->sigla.$name,$attributi));
          $gruppo->add($val['label']);
          if(!$inLinea) $gruppo->aCapo();
		}
		if($label)$this->add($this->creaLabel($this->sigla.$name,$label),$spanO);
		$this->add($gruppo);
		return $gruppo;
	}
	
	function check($nome,$scelte,$label='',$inLinea=FALSE,$selezionate=array(),$disabilitate=array()){
		$gruppo = new AV_Span('scelte');
        $gruppo->inline=FALSE;
        foreach($scelte as $val=>$desc){
          $attributi = array();
          if(in_array($val,$selezionate)) $attributi['checked'] = 'checked';
          if(in_array($val,$disabilitate)) $attributi['disabled'] = 'disabled';
          $attributi['value'] = $val;
          $gruppo->add(new AV_Input('checkbox',$this->sigla.$nome,$attributi));
          $gruppo->add($desc);
          if(!$inLinea) $gruppo->aCapo();
		}
		if($label)$this->add($this->creaLabel($this->sigla.$nome,$label));
		$this->add($gruppo);
	}
	
    function creaLabel($for,$txt){
	   $label = new AV_Tag('label');
	   $label->aggiungiAttributo('for',$this->sigla.$for);
	   $txt = explode("::",$txt);
	   if($txt[1]){
			$label->add($i=new AV_Span('info','&nbsp;'));
			$i->aggiungiAttributo('onClick',"alert('$txt[1]')");
	   } 
	   $label->addText($txt[0]);
	   return $label;
	}
	
	function button($contenuto,$onclick,$id='',$spanO=0){
	   if(!$id) $id='b'.$this->contatore++;
	   $b = self::creaButton($contenuto,$onclick,$id);
	   $this->add($b,$spanO);
	   return $b;
	}
	
	static public function creaButton($contenuto,$onclick,$id=''){
	    $b = new AV_Button('button',$id,$contenuto);
	    $b->aggiungiAttributo('onclick',$onclick);
        return $b;		
	}
	//accoda al form una linea HTML della grandezza del form per dividere i contenuti
	function rigaDivisoria(){
	   $this->add('<hr>',$this->oggettiPerRiga);
	}
	
	//torna l'ultimo elemento inserito
	function last(){
		switch($this->layout){
			  case 'fieldset': return $this->currentFieldset->contenuto[1]->body->last()->last()->last(); break;
			  case 'table': return $this->contenuto[0]->body->last()->last()->last(); break;
			  case 'line': return parent::last(); break;
		}
	}
}

/**
 * Questa classe permette di creare un elemento HTML Input
 *
 * @public
 */
class AV_Input extends AV_Tag{  

  //tag <label> che precede il tag <input>
  private $label='';  
  private $id;
  
  function __construct($tipo,$id,$attr,$label=''){
    parent::__construct('input');
  	$attr['type'] = $tipo;
    $this->id = $attr['name'] = $attr['id'] = $id;
	$this->aggiungiAttributi($attr);
	$this->label = $label; 
	$this->shortTag = TRUE;
  }
  
  function setName($nome){
	 $this->impostaId($nome);
	 $this->aggiungiAttributo('name',$nome);
  }
} //end class AV_Input

/**
 * Questa classe permette di creare un elemento HTML TextArea
 *
 * @public
 */
class AV_TextArea extends AV_Tag{  

  function __construct($id,$txt='',$rows='5',$cols='40',$attr=array()){
    $attr['name'] = $attr['id'] = $id;
    $attr['rows'] = $rows;
    $attr['cols'] = $cols;
	$this->aggiungiAttributi($attr); 
	$this->inserisci($txt);
  }
  
  public function prendiContenuto(){
    return $this->parsingContenuto("textarea");
  }
  
} //end class AV_TextArea

class AV_Label extends AV_Tag{

  function __construct($for,$txt,$attr=array()){
    $attr['for'] = $for;
    $attr['id'] = $for."_lbl";
	$this->aggiungiAttributi($attr);
	$this->inserisci($txt);
	parent::__construct('label');
  }

} //end class

/**
 * Questa classe permette di creare un elemento HTML Select
 *
 * @public
 */
class AV_Select extends AV_Tag{  
  function __construct($id,$opzioni,$default='',$size=1,$titolo='',$multi=FALSE){
    $this->inline = FALSE;
    $attr['name'] = $attr['id'] = $id;
    $attr['size'] = $size;
    if($multi){
		$attr['multiple'] = 'multiple';	
		$attr['name'] .= '[]';
	}
    if($titolo)$this->inserisci("<option value=\"\">$titolo</option>");
    if(is_array($opzioni)){
	  foreach($opzioni as $key=>$val){
	    $sel = '';
	    if($default==$key) $sel=' selected="selected"';
	    $this->inserisci("<option value=\"$key\"$sel>$val</option>");
	  }
	}
	$this->aggiungiAttributi($attr); 
  }
  
  public function prendiContenuto(){
    return $this->parsingContenuto("select");
  }
  
} //end class AV_Select

class AV_Select2 extends AV_Tag{  
  function __construct($id,$scelte=array(),$descrizioni=array(),$default='',$size=1,$multi=FALSE,$gruppi=array()){
    $this->inline = FALSE;
    $attr['name'] = $attr['id'] = $id;
    $attr['size'] = $size;
    if($multi)$attr['multiple'] = 'multiple';
    if(is_array($scelte)){
     	$n=count($scelte);
     	$default = (array)$default;
     	if(count($gruppi)){
			$gruppoTitolo = key($gruppi);
			$gruppoNum = current($gruppi);	
		} 
		for($i=0;$i<$n;$i++){
			$gruppoNum--;	
		 	if($gruppoTitolo){
				$this->inserisci('<optgroup label="'.$gruppoTitolo.'">');
				$gruppoTitolo='';
			} 
		    $sel = (in_array($i,$default)) ? ' selected="selected"' : '';
		    $this->inserisci('<option value="'.$scelte[$i].'"'.$sel.'>'.$descrizioni[$i].'</option>');
		    if($gruppi and $gruppoNum==0){
				$this->inserisci('</optgroup>');
				$gruppoNum = next($gruppi);
				$gruppoTitolo = key($gruppi);
			}
		}
	}
	$this->aggiungiAttributi($attr); 
  }
  public function prendiContenuto(){ return $this->parsingContenuto("select"); }
  
} //end class AV_Select2

class AV_Button extends AV_Tag{
	function __construct($tipo,$id,$contenuto=''){
		parent::__construct('button');
		$attr['type'] = $tipo;
	    $this->id = $attr['name'] = $attr['id'] = $id;
		$this->aggiungiAttributi($attr);
		$this->shortTag = TRUE;
		if($contenuto) $this->add($contenuto);
	}
}
class AV_LinkButton extends AV_Tag{
	function __construct($testo,$url,$disable=false){
		parent::__construct('button');
		$this->add($testo);
		$attr['type'] = 'button';
		$attr['class'] = 'linkButton';
		if($disable){
			$attr['disabled'] = 'disabled';
			$attr['class'] .= ' disabled';	
		} 
		$attr['onclick'] = "javascript:location.href='$url'";
		$this->aggiungiAttributi($attr);
		$this->shortTag = TRUE;
	}
}

?>
