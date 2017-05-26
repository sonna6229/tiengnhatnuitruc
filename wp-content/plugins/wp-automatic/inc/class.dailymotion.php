<?php
/**
 * Class:DailyMotion to get videos from dailyMotion
 * @author sweetheatmn (sweetheatmn@gmail.com)
 * @version 1.0.0 
 * Last update: 12 December
 */

class wpAutomaticDailyMotion{
	
	public $ch; //curl handle
	
	function __construct(&$ch){
		
		$this->ch = $ch;
		
	}
	
	function getVideosByKeyword($keyword,$page=1){
		
		$keyword = urlencode(trim($keyword));
		
		$url = "https://api.dailymotion.com/videos?fields=id,thumbnail_url%2Ctitle,description,duration,genre,likes_total,views_total,created_time,owner.screenname,owner.avatar_360_url,owner.username&search=$keyword&page=$page&limit=2";
 
 		//curl get
		$x='error';
		 
		curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
		curl_setopt($this->ch, CURLOPT_URL, trim($url));
		 
		$exec=curl_exec($this->ch);
		$x=curl_error($this->ch);
		
		//Validate response
		$this->validateResponse($exec);
		
		//read json
		$json = json_decode($exec);
		
		if(isset($json->list)){
			return $json->list;
		}else{
			throw new Exception('JSON does not contain the a list');
		}
		
	}
	
	function getVideosByUser(){
		
	}
	
	/**
	 * Function validateResponse: validates the returned response if json or not
	 * @param  $exec
	 * @throws Exception
	 */
	private function validateResponse(&$exec){
		
		if (trim($exec) == ''){
			throw new Exception('Empty reply from the source with possible curl error '.$x);
		}
		
		if(! stristr($exec, '{')){
			throw new Exception('Reply returned but not a JSON ');
		}
		
		if(stristr($exec, '"error"')){
			throw new Exception('Source returned an Error '.$exec);
		}
		
	}
	
	
}