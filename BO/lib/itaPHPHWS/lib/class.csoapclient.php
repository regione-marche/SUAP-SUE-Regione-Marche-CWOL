<?php
require_once('soap-wsse.php');

class CSoapClient extends SoapClient 
{
	private $mLogin;
	private $mPassword;
	
    function CSoapClient($wsdl, $options)
    {
    	$this->mLogin = '';
		if(isset($options['login'])) {
			$this->mLogin = $options['login'];
		}
		
		$this->mPassword = '';  
		if(isset($options['password']))
		{
			$this->mPassword = $options['password'];
		}
       
		$this->GetWSDLWithCURL($wsdl, $options);

        parent::__construct($wsdl, $options);
    }

    private function GetWSDLWithCURL($wsdl, $options)
    {
    	$basepath = $_SERVER['DOCUMENT_ROOT'].rtrim(dirname($_SERVER['SCRIPT_NAME']),'/');
		$cache_dir = $basepath.'/tmp/';
		$cache_url = 'http://'.$_SERVER['HTTP_HOST'].substr($basepath, strlen($_SERVER['DOCUMENT_ROOT'])).'/tmp/';

		$file = '';
		//print_r($options);
		if (isset($options['wsdl_local_copy']) && $options['wsdl_local_copy'] == true &&
			isset($options['login']) &&
			isset($options['password'])) 
		{
			$file = md5(uniqid()).'.xml';

			if (($fp = fopen($cache_dir.$file, "w")) == false) {
				throw new Exception('Impossibile creare il file di cache locale per il WSDL ('.$cache_dir.$file.')');
			}

			$ch = curl_init();
			$credit = ($options['login'].':'.$options['password']);
			curl_setopt($ch, CURLOPT_URL, $wsdl);
			curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($ch, CURLOPT_USERPWD, $credit);
			curl_setopt($ch, CURLOPT_TIMEOUT, 60);
			curl_setopt($ch, CURLOPT_FILE, $fp);
			
			// Per evitare rotture di gonadi se il certificato non è valido o, nei test, ci sono incongruenze
			// tra gli host
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); 
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); 

			if (($xml = curl_exec($ch)) === false) {
                fclose($fp);
                @unlink($cache_dir.$file);
                
                curl_close($ch);
                
                throw new Exception(curl_error($ch));
            }
           
            curl_close($ch);
            fclose($fp);
            $wsdl = $cache_url.$file;
        }
       
        unset($options['wsdl_local_copy']);
       
        parent::__construct($wsdl, $options);
       
        if(!empty($file))
        {
        	@unlink($cache_dir.$file);
        }
    }

	public function __doRequest($request, $location, $saction, $version, $one_way = 0) {
	
		$doc = new DOMDocument('1.0');
		$doc->loadXML($request);

		$objWSSE = new WSSESoap($doc);

		$objWSSE->addUserToken($this->mLogin, $this->mPassword, FALSE);
		print_r($objWSSE);
		$f = fopen('wsse.xml', 'w+t');
		fwrite($f, $objWSSE->saveXML());
		fclose($f);
		
		return parent::__doRequest($objWSSE->saveXML(), $location, $saction, $version, $one_way);
		
		//$doc = file_get_contents('wsse2.xml');
		//$doc = file_get_contents('wsse3.xml');
		//return parent::__doRequest($doc, $location, $saction, $version, $one_way);
	}
}
?>