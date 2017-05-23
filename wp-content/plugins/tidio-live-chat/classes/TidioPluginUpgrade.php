<?php

class TidioPluginUpgrade {
	
	private $apiHost = 'http://www.tidioelements.com';
	
	private static $pluginId = 'chat';
	
	public static $userAccessKey = null;
	
	public function init(){
		
		if(empty($_GET['userAccountUpgrade']) || self::getUserAccessKey()){
			
			return false;
			
		}
		
		$userAccessKey = $_GET['userAccountUpgrade'];
		
		$projectUrl = get_option('siteurl');
		
		//
		
		$apiData = $this->getApiData('/apiUser/addProject', array(
			'userAccessKey' => $userAccessKey,
			'projectUrl' => $projectUrl,
			'importExternalPlugin' => get_option('tidio-'.self::$pluginId.'-private-key')
		), true);
		
		if(!$apiData[0]){
			
			return false;
			
		}
		
		$apiData = $apiData[1];
		
		update_option('tidio-'.self::$pluginId.'-user-access-key', $userAccessKey);
		
		update_option('tidio-'.self::$pluginId.'-public-key', $apiData['public_key']);
		
		update_option('tidio-'.self::$pluginId.'-private-key', $apiData['private_key']);
		
		self::$userAccessKey = $userAccessKey;
		
		return true;
		
	}
	
	public static function getUserAccessKey(){
		
		if(self::$userAccessKey!==null){
			
			return self::$userAccessKey;
			
		}
		
		$userAccessKey = get_option('tidio-'.self::$pluginId.'-user-access-key');
		
		if(!$userAccessKey){
			
			self::$userAccessKey = false;
			
			return self::$userAccessKey;
			
		}
		
		self::$userAccessKey = $userAccessKey;
			
		return self::$userAccessKey;
		
	}
	
	private function getApiData($url, $attr = array(), $showUrl = false){
		
		$attr['platform'] = 'wordpress';
		
		$attr['platformType'] = 'chat';
		
		//
		
		$apiUrl = $this->apiHost.$url.'?'.http_build_query($attr);
				
		$apiData = $this->getUrlData($apiUrl);
		
		$apiData = json_decode($apiData, true);
		
		if(!$apiData['status'])
			
			return array(false, $apiData['value']);
			
		return array(true, $apiData['value']);
		
	}
	
	private function getUrlData($url, $postData = null){
				
		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
		
		if($postData){
			curl_setopt($ch,CURLOPT_POST, count($postData));
			curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($postData));
		}
		
		//
			
		$data = curl_exec($ch);
		curl_close($ch);
		
		return $data;
		
	}
	
}