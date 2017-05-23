<?php

class TidioChatOptions {
	
	private $apiHost = 'http://www.tidioelements.com/';
	
	private $siteUrl;
	
	public function __construct(){
				
		$this->siteUrl = get_option('siteurl');
				
	}
	
	public function getChatSettings(){

		$chatSettings = get_option('tidio-chat-settings');
								
		if($chatSettings)
			
			return json_decode($chatSettings, true);
			
		//	
					 
		$chatSettings = array(
			'email' => get_bloginfo('admin_email'),
			'base_color' => '#2E4255',
			'online_message' => 'Chat with us',
			'offline_message' => 'Leave a message',
			'language' => 'en',
			'translate' => null
		);
		
		update_option('tidio-chat-settings', json_encode($chatSettings));
		
		return $chatSettings;
		

	}
	
	public function getPrivateKey(){
		
		$tidioPrivateKey = get_option('tidio-chat-private-key');
		
		if(empty($tidioPrivateKey)){
		
			$tidioPrivateKey = md5(SECURE_AUTH_KEY.md5(microtime()).mt_rand(1,1000000000).'.liveChat');
			
			update_option('tidio-chat-private-key', $tidioPrivateKey);
		
		}
		
		return $tidioPrivateKey;

	}
	
	public function getPublicKey(){
		
		$tidioPublicKey = get_option('tidio-chat-public-key');
				
		if(!empty($tidioPublicKey))
			
			return $tidioPublicKey;
			
		//
		
		$apiData = $this->getContentData($this->apiHost.'apiExternalPlugin/accessPlugin', array(
			'privateKey' => $this->getPrivateKey(),
			'url' => $this->siteUrl,
			'pluginData' =>  json_encode($this->chatSettings)
		));

		$apiData = json_decode($apiData, true);
		
		if(!empty($apiData) || $apiData['status']){
			
			$tidioPublicKey = $apiData['value']['public_key'];
			
			update_option('tidio-chat-public-key', $tidioPublicKey);
			
		}
				
		//
		
		return $tidioPublicKey;

	}
	
	private function getContentData($url, $postData = array()){
			
		$postData['_ip'] = $_SERVER['REMOTE_ADDR'];
			
		//	
				
		$ch = curl_init();
	
		curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
		
		if(!empty($postData)){
			curl_setopt($ch,CURLOPT_POST, count($postData));
			curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($postData));
		}
		
		//
			
		$data = curl_exec($ch);
		curl_close($ch);
		
		return $data;
		
	}
	
}