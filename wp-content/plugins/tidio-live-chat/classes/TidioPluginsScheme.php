<?php

class TidioPluginsScheme {
	
	public static $pluginUsed;
	
	public static $plugins;
	
	// In this method we used hierarchy like this - integrator, visual-editor and else first definied plugin
	
	public static function usePlugin($pluginId){
		
		if(self::$pluginUsed){
			
			return false;
			
		}
		
		if($pluginId=='integrator'){
			
			self::$pluginUsed = $pluginId;
			
			return true;
			
		} else if($pluginId=='visual-editor' && !self::findPlugin('integrator')){
			
			self::$pluginUsed = $pluginId;
			
			return true;
			
		} else if(!self::findPlugin('integrator') && !self::findPlugin('visual-editor')) {
			
			self::$pluginUsed = $pluginId;
			
			return true;
			
		}
		
		return false;		
		
		
	}
	
	// Plugin status
	
	public static function compatibilityPlugin($pluginName){
		
		if($pluginName=='integrator'){
			
			return true;
			
		}
		
		$plugins = self::getPlugins();
		
		if(count($plugins)==1){
			
			return true;
			
		} else {
			
			return false;
			
		}
		
	}
	
	public static function getPlugins(){
		
		if(self::$plugins){
			
			return self::$plugins;
			
		}
				
		$tidioPlugins = get_option('tidio-plugins');
						
		if(!$tidioPlugins){
			
			return array();
			
		} else {
		
			$tidioPlugins = json_decode($tidioPlugins, true);
		
		}
		
		self::$plugins = $tidioPlugins;
		
		//
		
		return $tidioPlugins;
		
	}
	
	public static function registerPlugin($pluginName){

		$tidioPlugins = get_option('tidio-plugins');
							
		if(strstr($tidioPlugins, '"'.$pluginName.'"')){
		
			return false;
			
		}
						
		if(!$tidioPlugins){
			
			$tidioPlugins = array();
			
			$tidioPlugins[] = $pluginName;
			
		} else if($tidioPlugins){
			
			$tidioPlugins = json_decode($tidioPlugins, true);
			
			if(!self::findPlugin($pluginName, $tidioPlugins)){
				
				$tidioPlugins[] = $pluginName;
				
			}
						
		}
		
		//
		
		$tidioPlugins = json_encode($tidioPlugins);
				
		$tidioPlugins = update_option('tidio-plugins', $tidioPlugins);
				
		return true;
		
	}
	
	private static function findPlugin($pluginName, $plugins = null){
		
		if(!$plugins){
			
			$plugins = self::getPlugins();
			
		}

		foreach($plugins as $ePlugin){
				
			if($ePlugin==$pluginName){
					
				return true;
					
			}
				
		}
		
		return false;
	}
			
}