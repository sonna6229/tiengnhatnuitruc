<?php

/**
 * Class instaScrape: allows to get a specific user items,specific tag items from Instagram
 * @author Mohammed Atef
 * @link http://www.deandev.com
 * @version 1.0
 * Copy Rights 2016
 */
  
Class InstaScrape{

	
	public $ch;
	
	/**
	 * @param curl handler $ch
	 */
	function __construct($ch){
		$this->ch = $ch;	
	}
	
	/**
	 * Get instagram pics for a specific user using his numeric ID
	 * 
	 * @param string $usrID : the user id
	 * @$itemsCount integer: number of items to return default to 12
	 * @param number $index : the start index of reurned items (id of the first item) by default starts from the first image
	 * 
	 * @return: array of items 
	 */
	function getUserItems($usrID,$itemsCount = 12,$index = 0){
		
		// Build post parameters
		$postFields="q=ig_user($usrID)+%7B+media.after($index%2C+$itemsCount)+%7B%0A++count%2C%0A++nodes+%7B%0A++++caption%2C%0A++++code%2C%0A++++comments+%7B%0A++++++count%0A++++%7D%2C%0A++++date%2C%0A++++dimensions+%7B%0A++++++height%2C%0A++++++width%0A++++%7D%2C%0A++++display_src%2C%0A++++id%2C%0A++++is_video%2C%0A++++likes+%7B%0A++++++count%0A++++%7D%2C%0A++++owner+%7B%0A++++++id%2C%0A++++++username%2C%0A++++++profile_pic_url%2C%0A++++++full_name%0A++++%7D%2C%0A++++thumbnail_src%2C%0A++++video_views%2C%0A++++tags%0A++%7D%2C%0A++page_info%0A%7D%0A+%7D&ref=users%3A%3Ashow";
		
		// Building headers
		$headers = array();
		$headers[] = "Cookie: csrftoken=75baf754117197c6b100997b3ae2b2a9";
		$headers[] = "Origin: https://www.instagram.com";
		$headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/49.0.2623.108 Chrome/49.0.2623.108 Safari/537.36";
		$headers[] = "X-Requested-With: XMLHttpRequest";
		$headers[] = "X-Csrftoken: 75baf754117197c6b100997b3ae2b2a9";
		$headers[] = "X-Instagram-Ajax: 1";
		$headers[] = "Content-Type: application/x-www-form-urlencoded; charset=UTF-8";
		$headers[] = "Accept: application/json, text/javascript, */*; q=0.01";
		$headers[] = "Referer: https://www.instagram.com/cnn/";
		$headers[] = "Authority: www.instagram.com";

		// Request excute
		curl_setopt($this->ch, CURLOPT_URL, "https://www.instagram.com/query/");
		curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
		curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postFields);
		curl_setopt($this->ch, CURLOPT_POST, 1);
		
		$exec = curl_exec($this->ch);
		$x=curl_error($this->ch);
		
		// Curl error check
		if(trim($exec) == ''){
			throw new Exception('Empty results from instagram call with possible curl error:'.$x);
		}
			
		// Verify returned result
		if(! stristr($exec, 'status": "ok')){
			throw new Exception('Unexpected page content from instagram'.$x);
		}
		
		$jsonArr = json_decode( $exec );
		
		if(isset( $jsonArr->status )){
			return $jsonArr;
		}else{
			throw new Exception('Can not get valid array from instagram'.$x);
		}
		
	}
	
	/**
	 * Get Instagram pics by a specific hashtag
	 * 
	 * @param string $hashTag Instagram Hashtag
	 * @param integer $itemsCount Number of items to return
	 * @param string $index Last cursor from a previous request for the same hashtag 
	 */
	function getItemsByHashtag($hashTag,$itemsCount,$index = 0){
		 
		 
		// Build post parameters
		if($index === 0){

			$url ="https://www.instagram.com/explore/tags/".urlencode(trim($hashTag))."/?__a=1";
			curl_setopt($this->ch, CURLOPT_URL, $url);
			
		}else{
			
			$postFields="q=ig_hashtag(". urlencode(trim($hashTag)) .")+%7B+media.after(".urlencode(trim($index))."%2C+".$itemsCount.")+%7B%0A++count%2C%0A++nodes+%7B%0A++++caption%2C%0A++++code%2C%0A++++comments+%7B%0A++++++count%0A++++%7D%2C%0A++++date%2C%0A++++dimensions+%7B%0A++++++height%2C%0A++++++width%0A++++%7D%2C%0A++++display_src%2C%0A++++id%2C%0A++++is_video%2C%0A++++likes+%7B%0A++++++count%0A++++%7D%2C%0A++++owner+%7B%0A++++++id%2C%0A++++username%2C%0A++++full_name%2C%0A++++profile_pic_url%0A++++%7D%2C%0A++++thumbnail_src%2C%0A++++video_views%0A++%7D%2C%0A++page_info%0A%7D%0A+%7D&ref=tags%3A%3Ashow";
			
			 
			// Building headers
			$headers = array();
			$headers[] = "Cookie: csrftoken=75baf754117197c6b100997b3ae2b2a9";
			$headers[] = "Origin: https://www.instagram.com";
			$headers[] = "User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/49.0.2623.108 Chrome/49.0.2623.108 Safari/537.36";
			$headers[] = "X-Requested-With: XMLHttpRequest";
			$headers[] = "X-Csrftoken: 75baf754117197c6b100997b3ae2b2a9";
			$headers[] = "X-Instagram-Ajax: 1";
			$headers[] = "Content-Type: application/x-www-form-urlencoded; charset=UTF-8";
			$headers[] = "Accept: application/json, text/javascript, */*; q=0.01";
			$headers[] = "Referer: https://www.instagram.com/cnn/";
			$headers[] = "Authority: www.instagram.com";
			
			// Request excute
			curl_setopt($this->ch, CURLOPT_URL, "https://www.instagram.com/query/");
			curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($this->ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($this->ch, CURLOPT_POSTFIELDS, $postFields);
			curl_setopt($this->ch, CURLOPT_POST, 1);
			
			
		}
		
	 
		
		
		$exec = curl_exec($this->ch);
		$x=curl_error($this->ch);
		
		 
		// Curl error check
		if(trim($exec) == ''){
			throw new Exception('Empty results from instagram call with possible curl error:'.$x);
		}
			
		// Verify returned result
		if(! stristr($exec, 'status": "ok') && ! stristr($exec, 'media"')){
			throw new Exception('Unexpected page content from instagram'.$x);
		}
		
		$jsonArr = json_decode( $exec );
		
		if($index == 0 && isset($jsonArr->tag)){
			$jsonArr = $jsonArr->tag;
			$jsonArr->status = 'ok';
			unset($jsonArr->name);
			unset($jsonArr->content_advisory);
			unset($jsonArr->top_posts);
			
		}
		 
		if(isset( $jsonArr->status )){
			
			//when no new items let's get the first page
			
			if(count($jsonArr->media->nodes) == 0 ){
				
				if($index === 0){
					
				}else{
					// index used let's return first page
					return $this->getItemsByHashtag($hashTag,$itemsCount);
				}
				
			}
			
			return $jsonArr;
			
			
			
		}else{
			throw new Exception('Can not get valid array from instagram'.$x);
		}
		
		
	}
	
	/**
	 * @param string $name the name of instagram user for example "cnn"
	 * @return: numeric id of the user
	 */
 	function getUserIDFromName($name){
 		
 		// Curl get
 		$x='error';
 		$url='https://www.instagram.com/'.trim($name).'/?__a=1';
 		curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
 		curl_setopt($this->ch, CURLOPT_URL, trim($url));
 		$exec   =   curl_exec($this->ch);
 		$cuinfo =   curl_getinfo($this->ch);
 		$http_code = $cuinfo['http_code'];
 		$x=curl_error($this->ch);
 		
 		
 		// Curl error check
 		if(trim($exec) == ''){
 			throw new Exception('Empty results from instagram call with possible curl error:'.$x);
 		}
 		
 		// If not found 
 		if($http_code == '404'){
 			throw new Exception('Instagram returned 404 make sure you have added a correct id. for example add "cnn" for instagram.com/cnn user');
 		};
 		
 		// Verify returned result 
 		if(! stristr($exec, 'id')){
 			throw new Exception('Unexpected page content from instagram'.$x);
 		}

 		// Extract the id
		preg_match('{id": "(.*?)"}', $exec,$matchs);
		$possibleID = $matchs[1];

		// Validate extracted id
		if(! is_numeric($possibleID) || trim($possibleID) == ''){
			throw new Exception('Can not extract the id from instagram page'.$x);
		}
		
		// Return ID
		return $possibleID;
 		
 		
 	}
 	
 	/**
 	 * 
 	 * @param string $itmID id of the item for example "BGUTAhbtLrA" for https://www.instagram.com/p/BGUTAhbtLrA/
 	 */
 	function getItemByID($itmID){
		
 		// Preparing uri
 		$url = "https://www.instagram.com/p/".trim($itmID)."/?__a=1";
 		
 		//curl get
 		$x='error';
 		 
 		curl_setopt($this->ch, CURLOPT_HTTPGET, 1);
 		curl_setopt($this->ch, CURLOPT_URL, trim($url));
 		 
 		$exec=curl_exec($this->ch);
 		$x=curl_error($this->ch);
 		
 		return json_decode( $exec ) ;
 		 
 		
 	}
	
}

  