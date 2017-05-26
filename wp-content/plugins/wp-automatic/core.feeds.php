<?php

/*
 * ---* feed process camp ---
 */

// Main Class
require_once 'core.php';

Class WpAutomaticFeeds extends wp_automatic{

function feeds_get_post($camp) {

	// feeds
	$feeds = $camp->feeds;
	$feeds = explode ( "\n", $feeds );

	$msg = "Processing " . count ( $feeds ) . " Feeds for this campaign " . get_the_title ( $camp->camp_id );
	echo '<br>' . $msg;

	if (count ( $feeds ) > 0) {
		$this->log ( 'Process Feeds', $msg );
	}

	$n = 0;
	foreach ( $feeds as $feed ) {
		
		if($n == 3){
			echo '<br>Processed 3 feeds with nothing to find, will die now, Please activate rotating if you are not.';
			break;
		}
		
		if (trim ( $feed ) != '') {
			
			// process feed
			echo '<b><br><br>Processing Feed:</b> ' . $feed;

			update_post_meta($camp->camp_id, 'last_feed', trim($feed));
			 
			$cont = $this->feed_process_link ( $feed, $camp );
			$n++;


			if (trim ( $cont ['cont'] ) != '') {
				return $cont;
			}
		}
	}

	return false;
}
	
/*
 * ---* processing feed link ---
 */
function feed_process_link($feed, $camp) {
	
	// ini
	if( stristr($camp->camp_general, 'a:') ) $camp->camp_general=base64_encode($camp->camp_general);
	$camp_general=unserialize( base64_decode( $camp->camp_general));
	$camp_general=array_map('stripslashes', $camp_general);
	$camp_opt = unserialize ( $camp->camp_options );
	
	$feedMd5 = md5($feed);
	$isItemsEndReached =  get_post_meta ( $camp->camp_id , $feedMd5.'_isItemsEndReached',1);
	$lastProcessedFeedUrl = get_post_meta ( $camp->camp_id , $feedMd5.'_lastProcessedFeedUrl',1);
	$lastFirstFeedUrl = get_post_meta ( $camp->camp_id , $feedMd5.'_lastFirstFeedUrl',1);
	
	// check last time adition
	$feed = trim ( $feed );
	$myfeed = addslashes ( $feed );
	
	//removed @ v3.24
	/*
	$query = "select * from {$this->wp_prefix}automatic_feeds_list where feed='$myfeed' and camp_id = '$camp->camp_id' limit 1";
	$feeds = $this->db->get_results ( $query );
	$feed_o = $feeds [0];
	*/

	// report processed feed
	$this->log ( 'Process Feed', '<a href="' . $feed . '">' . $feed . '</a>' );

	// If force feed
	
	if(in_array('OPT_FEED_FORCE', $camp_opt)){
	
		if(! function_exists('wp_automatic_force_feed')){
			add_action('wp_feed_options', 'wp_automatic_force_feed', 10, 1);
			function wp_automatic_force_feed($feed) {
				$feed->force_feed(true);
			}
		}
	}
	
	// Wrong feed length fix
	if( ! function_exists('wp_automatic_setup_curl_options') ){
		//feed timeout
		function wp_automatic_setup_curl_options( $curl ) {
			if ( is_resource( $curl ) ) {
				curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 'Expect:' ) );
			}
		}
	}
	
	// loading SimplePie
	include_once (ABSPATH . WPINC . '/feed.php');

	// Add action to fix the problem of curl transfer closed without complete data
	add_action( 'http_api_curl', 'wp_automatic_setup_curl_options' );
	if( ! function_exists('wp_automatic_wp_feed_options') ){
		function wp_automatic_wp_feed_options($args){

			$args->set_useragent('Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Ubuntu Chromium/41.0.2272.76 ');
				
		}
		add_action('wp_feed_options','wp_automatic_wp_feed_options');
	}

	// Trim returned feed content because some feed add empty spaces before feed content 
	if(!function_exists('wp_automatic_trim_feed_content')){
		function wp_automatic_trim_feed_content($args){
			
			 
			$args['body'] = trim($args['body']);
			
			return $args;
		}
	}
	
	add_filter('http_response', 'wp_automatic_trim_feed_content');

	if(stristr($feed, 'news.google') && ! function_exists('wp_automatic_feed_options') ){
		
		// Fix Google news image stripped
		echo '<br>Google news feed found, disabling sanitization...';
		
		function wp_automatic_feed_options( $feed) {
		
			$feed->set_sanitize_class( 'SimplePie_Sanitize' );
			$feed->sanitize = new SimplePie_Sanitize;
		
		}
		add_action( 'wp_feed_options', 'wp_automatic_feed_options' );
	}
	
	// Fetch feed content
	$rss = fetch_feed ( $feed );

	 
	// Remove added filter
	remove_filter('http_response', 'wp_automatic_trim_feed_content');

	if (! is_wp_error ( $rss )){ // Checks that the object is created correctly
		// Figure out how many total items there are, but limit it to 5.
		$maxitems = $rss->get_item_quantity ();
		
		// Build an array of all the items, starting with element 0 (first element).
		$rss_items = $rss->get_items ( 0, $maxitems );
		
		 
		//remove the expect again as it makes jetpack publicize to not work
		remove_action('http_api_curl', 'wp_automatic_setup_curl_options');
			
			
	}else{
		$error_string = $rss->get_error_message();
		echo '<br><strong>Error:</strong>' . $error_string ;
	}
	
	if (!isset($maxitems) || $maxitems == 0)
		return false;
	else
			
	//reverse order if exists
	if(in_array('OPT_FEED_REVERSE', $camp_opt)){
		echo '<br>Reversing order';
		$rss_items = array_reverse($rss_items);
	}

	// Loop through each feed item and display each item as a hyperlink.
	$i  = 0;
	$i2 = 0; // refer to items number in feed
	 
	foreach ( $rss_items as $item ) :
	
	 
	$url = esc_url ( $item->get_permalink () );
	$original_url = $url; // used for exclusion because the effective_url may change below
	
	//google news links correction 
	if(stristr($url, 'news.google')){
		$urlParts = explode('url=', $url);
		$correctUrl = $urlParts[1];
		$url = $correctUrl;
		
	}
	
	//Google alerts links correction
	if(stristr($feed	, 'alerts/feeds') && stristr($feed, 'google')){
		preg_match('{url\=(.*?)[&]}', $url,$urlMatches);
		$correctUrl = $urlMatches[1];
		
		if( trim($correctUrl) != '' ){
			$url =$correctUrl;
		}
	}
	
	//check if no new links: the last first url is the same
	if($i2 == 0){
		
		if($isItemsEndReached == 'yes'){
			
			//Last time there were no new links check if the case didn't change
			if( $lastFirstFeedUrl == md5($url) ){
				
				//still no new links stop checking now
				echo '<br>First url in the feed:'.$url;
				echo '<br>This link was the same as the last time we did not find new links so ... skipping till new posts get added <a href="#" class="wp_automatic_ajax" data-action="wp_automatic_ajax" data-function="forget_lastFirstFeedUrl" data-data="'.$feedMd5.'" data-camp="'.$camp->camp_id.'" >Forget this fact Now</a>.';
				
				//delete transient cache
				delete_transient('feed_'.$feedMd5);
				
				return false;
				
			}else{
				//new links found remove the isItemsEndReached flag
				delete_post_meta($camp->camp_id,$feedMd5.'_isItemsEndReached');
			}
		}
		
	}
	
	//Record first feed url
	if($i2 == 0){
		update_post_meta ( $camp->camp_id , $feedMd5.'_lastFirstFeedUrl',md5($url));
	}
	
	// one more link, increase index2
	$i2++;
		
	if(trim($url) == ''){
		echo '<br>item have no url skipping';
		continue;
	}

	// current post url	
	echo '<br>Post url: '.$url;
	
	// post date
	$wpdate= $item->get_date ( "Y-m-d H:i:s");
	echo '<br>- Published: '.$wpdate;
	
	// post categories 
	$cats=  ($item->get_categories());
		
	// separate categories with commas	
	$cat_str = '';
	if(isset($cats)){
		foreach($cats as $cat ){
			if(trim($cat_str) != '') $cat_str.= ',';
			$cat_str.= $cat->term;
		}
	}
		
	// fix empty titles
	if(trim($item->get_title ()) == ''){
		echo '<--Empty title skipping';
		continue;
	}
		
	// check if execluded link due to exact match does not exists
	if( $this->is_execluded($camp->camp_id, $url)){
		echo '<-- Excluded link';
		continue;
	}
		
	// check if older than minimum date
	if($this->is_link_old($camp->camp_id,  strtotime($wpdate) )){
		echo '<--old post execluding...';
		continue;
	}
		
	// check media images
	unset($media_image_url);
	$enclosure = $item->get_enclosure();
 	$enclosure_link = $enclosure->link;
	if(isset($enclosure->type)  && stristr($enclosure->type, 'image') && isset($enclosure->link) ){
		$media_image_url = $enclosure->link;
	}
	
	// Duplicate check
	if (! $this->is_duplicate($url) ) {

		echo '<-- new link';
	 
		$title =  strip_tags( $item->get_title () );
		
	 
		//check if there is a post published with the same title
		if(in_array('OPT_FEED_TITLE_SKIP',$camp_opt)  ){
			if($this->is_title_duplicate($title,$camp->camp_post_type)){
				echo '<-- duplicate title skipping..';
				continue;
			}
		}


		$i ++;
		
		// Date
		$date = $item->get_date ( 'j F Y | g:i a' );
		$wpdate= $item->get_date ( "Y-m-d H:i:s");

		// post content
		$html = $item->get_content ();
 
		
		$postAuthor = $item->get_author();
		$authorName = $postAuthor->name;
		
		if(trim($authorName) == ''){
			$authorName = $postAuthor->email;
		}
		
		if(trim($authorName) == ''){
			$authorName = $postAuthor->link;
		}
		
		$res['author'] = $authorName;
		$res['author_link'] = $postAuthor->link;
 
	 	 
		// If empty content make content = title
		if(trim($html) == ''){
			if( trim($title) != '' ) $html =$title;
		}
		
		// loging the feeds 
		$md5 = md5 ( $url );
		$query = "insert into {$this->wp_prefix}automatic_feeds_links (link,camp_id) values ('$md5','$camp->camp_id')";
		$this->db->query ( $query );

		//if not image escape it
		$res ['cont'] = $html;
		$res ['original_content']=$html;
		$res ['title'] = $title;
		$res ['original_title'] = $title;
		$res ['matched_content'] = $html;
		$res ['source_link'] = $url;
		$res ['publish_date'] = $date;
		$res ['wpdate']=$wpdate;
		$res ['cats'] = $cat_str;
		$res ['tags'] = '';
		$res ['enclosure_link'] = $enclosure_link;
		
		
		//custom atributes
		$arr=array();
		$arrValues = array_values($item->data['child']);
		
		foreach ($arrValues as $arrValue){
			if(is_array($arrValue)){
				$arr = array_merge($arr,$arrValue);
			}
		}
		
		$res['attributes'] = $arr;

		 
		// check now if full feeds is needed
		$camp_opt = unserialize ( $camp->camp_options );

		// FULL CONTENT
		if (in_array ( 'OPT_FULL_FEED', $camp_opt )) {
			 	
			// get content
			
			// test url
			//$url ="http://www.torontosun.com/2016/07/23/giovinco-ends-goal-drought-with-hat-trick-in-tfc-win";
				
			curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
			curl_setopt ( $this->ch, CURLOPT_URL, trim ( html_entity_decode($url)  ) );
			$original_cont = $this->curl_exec_follow( $this->ch );
			$x = curl_error ( $this->ch );
			$url = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL);
				
			//strip js from html
			//$original_cont = preg_replace('{<script.*?script>}s', '', $original_cont);

			// Redability instantiate
			require_once 'inc/wp_automatic_readability/wp_automatic_Readability.php';
			
			// If not UTF-8
			if(in_array('OPT_FEED_CONVERT_ENC', $camp_opt)   ){
				echo '<br>Converting encoding from '.$camp_general['cg_feed_encoding']. ' to utf-8';
				$convertedContent = iconv( trim($camp_general['cg_feed_encoding']) , "UTF-8", $original_cont);
				$wp_automatic_Readability = new wp_automatic_Readability ( $convertedContent, $url );
			}else{
				$wp_automatic_Readability = new wp_automatic_Readability ( $original_cont, $url );
			}
			
			$wp_automatic_Readability->debug = false;
			$result = $wp_automatic_Readability->init ();
				
			if ($result) {
				
				// Redability title
				$title = $wp_automatic_Readability->getTitle ()->textContent;
				
				// Redability Content
				$content = $wp_automatic_Readability->getContent ()->innerHTML;

				// Remove  wp_automatic_Readability attributes
				$content = preg_replace('{ wp_automatic_Readability\=".*?"}s', '', $content);
				
				// Fix iframe if exists
				preg_match_all('{<iframe[^<]*/>}s', $content,$ifrMatches);
				$iframesFound = $ifrMatches[0];
				
				foreach ($iframesFound as $iframeFound){
					
					$correctIframe  = str_replace('/>','></iframe>',$iframeFound);
					$content = str_replace($iframeFound, $correctIframe, $content);
					
				}
				
				 
				// Cleaning redability for better memory
				unset($wp_automatic_Readability);
				unset($result);
					
				// Check existence of title words in the content
				$title_arr=explode(' ', $title);

				$valid='';
				$nocompare=array('is','Is','the','The','this','This','and','And','or','Or','in','In','if','IF','a','A','|','-');
				foreach($title_arr as $title_word){
					
					if(strlen($title_word) > 3){
					
						if(! in_array($title_word, $nocompare) &&  preg_match('/\b'. preg_quote(trim($title_word),'/') .'\b/ui', $content)){
							echo '<br>Word '.$title_word .' exists';
							
							 
							//echo $content;
							$valid='yeah';
							break;
						}else{
							echo '<br>Word '.$title_word .' does not exists';
						}
						
					}
				}

				if(trim($valid) != ''){

					$res ['cont'] = $content;
					$res ['matched_content'] = $content;
					$res ['og_img'] = '';
					
					if(! in_array('OPT_FEED_TITLE_NO', $camp_opt)){
						$res ['title'] = $title;
						$res['original_title'] = $title;
					} 
					
					// let's find og:image may be the content we got has no image
					preg_match('{<meta[^<]*?property=["|\']og:image["|\'][^<]*?>}s', $html,$plain_og_matches);

					if(@stristr($plain_og_matches[0], 'og:image')){
						preg_match('{content=["|\'](.*?)["|\']}s', $plain_og_matches[0],$matches);
						$og_img = $matches[1];

						if(trim($og_img) !=''){
							$res ['og_img']=$og_img ;
						}

					}// If og:image
 	
				}else{
					echo '<br>Can not make sure if the returned content is the full content.. using excerpt instead.';
				}

			} else {
				echo '<br>Looks like we couldn\'t find the full content. :( returning summary';
			}
			
		// Class or ID extraction
		
		}elseif(in_array ( 'OPT_FEED_CUSTOM', $camp_opt )){
				
			echo '<br>Extracting content from original post for ';
			
			$cg_selector=$camp_general['cg_custom_selector'];
			$cg_selecotr_data=$camp_general['cg_feed_custom_id'];

			echo $cg_selector . ' = "'.$cg_selecotr_data.'"';
				
			//dom class
			require_once 'inc/sxmldom_simple_html_dom.php';
			
			//test url
			//$url = "http://www.torontosun.com/2016/07/23/giovinco-ends-goal-drought-with-hat-trick-in-tfc-win";
				
			//get content
			$x='error';
			curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
			curl_setopt ( $this->ch, CURLOPT_URL, trim ( html_entity_decode(  $url   ) ));
				
			//encoding
			if(in_array('OPT_FEED_ENCODING' , $camp_opt  )){
				echo '<br>Clearing encoding..';
				curl_setopt($this->ch, CURLOPT_ENCODING , "");
			}
			
			// Source page html
			$original_cont = $this->curl_exec_follow ( $this->ch );
			$x = curl_error ( $this->ch );
			$url = curl_getinfo($this->ch, CURLINFO_EFFECTIVE_URL); // Final url
			
			// Simple Html Dom (memory leaker)
			$original_html = sxmldom_str_get_html($original_cont);
 
			if(method_exists($original_html, 'find')){

				if($cg_selector != 'xpath'){
					$ret = $original_html->find('*['.$cg_selector.'='.trim($cg_selecotr_data).']');
				}else{
					$ret = $original_html->find( trim($cg_selecotr_data) );
				}

				$extract='';

				foreach ($ret as $itm ) {
						
					if(in_array('OPT_SELECTOR_INNER', $camp_opt)){
						$extract = $extract . $itm->innertext ;
					}else{
						$extract = $extract . $itm->outertext ;
					}

					if(in_array('OPT_SELECTOR_SINGLE', $camp_opt)){
						break;
					}
						
				}
 
				if(trim($extract) == ''){
					echo '<br>Nothing found to extract for this rule' ;
					 
				}else{
					echo '<br>Rule one extracted ' . strlen($extract) .' charchters ';
				}


				//good we found the first rule let's append rule 2
				$cg_selector2=$camp_general['cg_custom_selector2'];
				$cg_selecotr_data2=$camp_general['cg_feed_custom_id2'];

				if(trim($cg_selecotr_data2) != ''){
						
					echo '<br>Rule 2:'. $cg_selector2 . ' = "'.$cg_selecotr_data2.'"';

					if($cg_selector2 != 'xpath'){
						$ret2 = $original_html->find('*['.$cg_selector2.'='.trim($cg_selecotr_data2).']');
					}else{
						$ret2 = $original_html->find( trim($cg_selecotr_data2) );
					}
						
						
					$extract2='';
					foreach ($ret2 as $itm2 ) {

						if(in_array('OPT_SELECTOR_INNER2', $camp_opt)){
							$extract2 = $extract2 . $itm2->innertext ;
						}else{
							$extract2 = $extract2 . $itm2->outertext ;
						}

						if(in_array('OPT_SELECTOR_SINGLE2', $camp_opt)){
							break;
						}

					}
						
					if(trim($extract2 == '')){
						echo '<br>Nothing found to extract for this rule';
					}else{
						echo '<br>Rule two extracted ' . strlen($extract2) .' charchters ';
						
						$separator  = '';
						
						if(trim($extract) != '') $separator = '<br>';
						
						$extract = $extract.   $separator    . $extract2;
					}
						
						
				}

				//good we found the first rule let's append rule 3
				$cg_selector3=$camp_general['cg_custom_selector3'];
				$cg_selecotr_data3=$camp_general['cg_feed_custom_id3'];

				if(trim($cg_selecotr_data3) != ''){

					echo '<br>Rule 3:'. $cg_selector3 . ' = "'.$cg_selecotr_data3.'"';

					if($cg_selector3 != 'xpath'){
						$ret3 = $original_html->find('*['.$cg_selector3.'='.trim($cg_selecotr_data3).']');
					}else{
						$ret3 = $original_html->find( trim($cg_selecotr_data3) );
					}
						

					$extract3='';
					foreach ($ret3 as $itm3 ) {

						if(in_array('OPT_SELECTOR_INNER3', $camp_opt)){
							$extract3 = $extract3 . $itm3->innertext ;
						}else{
							$extract3 = $extract3 . $itm3->outertext ;
						}



						if(in_array('OPT_SELECTOR_SINGLE3', $camp_opt)){
							break;
						}

					}

					if(trim($extract3 == '')){
						echo '<br>Nothing found to extract for this rule';
					}else{
						echo '<br>Rule two extracted ' . strlen($extract3) .' charchters ';
						
						$separator  = '';
						
						if(trim($extract) != '') $separator = '<br>';
						
						
						$extract = $extract .$separator . $extract3;
					}


				}
					
				if(trim($extract) != ''){
					$res ['cont'] = $extract;
					$res ['matched_content'] = $extract;
				}

			}else{
				echo '<br>could not parse the content returning summary' ;
			}
			
			// Finished extracting by id/class let's free memory from simpleDom leaking
			$original_html->clear();
			unset($original_html);
			
				
		// REGEX EXTRACT	
		}elseif(in_array ( 'OPT_FEED_CUSTOM_R', $camp_opt )){
				
			echo '<br>Extracting content using REGEX ';
			$cg_feed_custom_regex = html_entity_decode($camp_general['cg_feed_custom_regex']);
			$cg_feed_custom_regex2= html_entity_decode($camp_general['cg_feed_custom_regex2']);
				
			if(trim($cg_feed_custom_regex) != '' ){

				$finalmatch = '';
				$finalmatch2 = '';

				//we have a regex
				echo '<br>Regex1:'. htmlspecialchars($cg_feed_custom_regex);

				//get content
				$x='error';
				curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
				curl_setopt ( $this->ch, CURLOPT_URL, trim ( html_entity_decode( $url) ) );
				$original_cont = $this->curl_exec_follow( $this->ch );
				$x = curl_error ( $this->ch );
				 

				//extracting
				if(trim($original_cont) !=''){
					preg_match_all('{'.$cg_feed_custom_regex.'}is', $original_cont,$matchregex);
						
					for( $i=1 ; $i < count($matchregex);$i++ ){

						foreach($matchregex[$i] as $newmatch){
								
							if(trim($newmatch) !=''){
								if(trim($finalmatch) !=''){
									$finalmatch.='<br>'.$newmatch;
								}else{
									$finalmatch.=$newmatch;
								}
							}
								
						}
					}
						

					if(trim($cg_feed_custom_regex2) != ''){
						echo '<br>Extracting cotnent for regex2:'.htmlspecialchars($cg_feed_custom_regex2);

						if(in_array('OPT_REGEX_TWO', $camp_opt)){
							preg_match_all('{'.$cg_feed_custom_regex2.'}is', $finalmatch,$matchregex2);
						}else{
							preg_match_all('{'.$cg_feed_custom_regex2.'}is', $original_cont,$matchregex2);
						}

							
							

						for( $i=1 ; $i < count($matchregex2);$i++ ){
								
							foreach($matchregex2[$i] as $newmatch){
								if(trim($newmatch) !=''){
									if(trim($finalmatch2) !=''){
										$finalmatch2.='<br>'.$newmatch;
									}else{
										$finalmatch2.=$newmatch;
									}
								}
							}

						}


						if(in_array('OPT_REGEX_TWO', $camp_opt)){
							$finalmatch =$finalmatch2;

						}else{
							$finalmatch.=$finalmatch2;
						}


					}
						

						
				}

				if(trim($finalmatch) != ''){
					//overwirte
					echo '<br>'.  strlen($finalmatch) . ' chars extracted using REGEX';
					$res ['cont'] = $finalmatch;
					$res ['matched_content'] = $finalmatch;
						

				}else{
					echo '<br>Nothing extracted using REGEX using summary instead..';
				}


			}

		}

		// Stripping content using id or class from $res[cont]
		if(in_array('OPT_STRIP_CSS', $camp_opt)){
				
			echo '<br>Stripping content using ';
				
			$cg_selector=$camp_general['cg_custom_strip_selector'];
			$cg_selecotr_data=$camp_general['cg_feed_custom_strip_id'];

			echo $cg_selector . ' = "'.$cg_selecotr_data.'"';

			//dom class
			require_once 'inc/sxmldom_simple_html_dom.php';
				
			$original_html = sxmldom_str_get_html($res['cont']);
				
			if(method_exists($original_html, 'find')){
					
				$ret = $original_html->find('*['.$cg_selector.'='.trim($cg_selecotr_data).']');
					
				foreach ($ret as $itm ) {
					$itm->outertext = '' ;
				}

				//rule2
				$cg_selector=$camp_general['cg_custom_strip_selector2'];
				$cg_selecotr_data=$camp_general['cg_feed_custom_strip_id2'];

				if(trim($cg_selector) != '' && trim($cg_selecotr_data) != ''){
						
					echo '<br>Striping content using '.$cg_selector .'="'.$cg_selecotr_data.'"';
						
					$ret = $original_html->find('*['.$cg_selector.'='.trim($cg_selecotr_data).']');

					foreach ($ret as $itm ) {
						$itm->outertext = '' ;
					}
						
				}

				//rule3
				$cg_selector=$camp_general['cg_custom_strip_selector3'];
				$cg_selecotr_data=$camp_general['cg_feed_custom_strip_id3'];

				if(trim($cg_selector) != '' && trim($cg_selecotr_data) != ''){

					echo '<br>Striping content using '.$cg_selector .'="'.$cg_selecotr_data.'"';

					$ret = $original_html->find('*['.$cg_selector.'='.trim($cg_selecotr_data).']');

					foreach ($ret as $itm ) {
						$itm->outertext = '' ;
					}

				}

				//overwirte
				$res ['matched_content'] = $res ['cont'] = $original_html->save();
 
				
				$original_html->clear();
				unset($original_html);
				 

			}else{
				echo '<br>Can not parse final html to strip by id/class';
			}
				
		}

		// Stripping content using REGEX
		if(in_array('OPT_STRIP_R', $camp_opt)){
			$current_content =$res ['matched_content']  ;
			$current_title=$res['title'];
			$cg_post_strip = html_entity_decode($camp_general['cg_post_strip']);
				
			$cg_post_strip=explode("\n", $cg_post_strip);
			$cg_post_strip=array_filter($cg_post_strip);
				
			foreach($cg_post_strip as $strip_pattern){
				if(trim($strip_pattern) != ''){

					//$strip_pattern ='<img[^>]+\\>';

					echo '<br>Stripping:'.htmlentities($strip_pattern);
					$current_content= preg_replace('{'.trim($strip_pattern).'}is', '', $current_content);
						
					$current_title= preg_replace('{'.trim($strip_pattern).'}is', '', $current_title);

				}
			}
				
				 
			if(trim($current_content) !=''){
				$res ['matched_content'] =$current_content ;
				$res ['cont'] =$current_content ;
			}
				
			if(trim($current_title) !=''){
				$res ['matched_title'] =$current_title ;
				$res ['original_title'] =$current_title ;
				$res ['title'] =$current_title ;

			}
				
		}// end regex replace
		
		// validate content size
		
		//MUST CONTENT
		if(in_array('OPT_MUST_CONTENT', $camp_opt)){
		
			if(trim($res['cont']) == ''){
				echo '<--No content excluding';
				$this->link_execlude( $camp->camp_id, $original_url );
				continue;
			}
		
		}
		
		//limit content
		if(in_array('OPT_MIN_LENGTH', $camp_opt)){
		
			$contentTextual = strip_tags($res['cont']);
			$contentTextual = str_replace(' ', '', $contentTextual);
			
			if(function_exists('mb_strlen'))
			{
				$contentLength = mb_strlen($contentTextual);
			}else{
				$contentLength = strlen($contentTextual);
			}
			
			unset($contentTextual);
		
			echo '<br>Content length:'.$contentLength;
		
			if( $contentLength < $camp_general['cg_min_length'] ){
				echo '<--Shorter than the minimum... Excluding';
				$this->link_execlude( $camp->camp_id, $original_url );
				continue;
		
			}
		
		}
 
		// Entity decode 
		if(in_array('OPT_FEED_ENTITIES', $camp_opt)){
			echo '<br>Decoding html entities';
				
			//php 5.3 and lower convert &nbsp; to invalid charchters that broke everything
				
			$res ['original_title'] = str_replace('&nbsp;', ' ', $res ['original_title']);
			$res ['matched_content'] = str_replace('&nbsp;', ' ', $res ['matched_content']);
				
			$res ['original_title'] = html_entity_decode($res ['original_title']);
			$res ['title'] =          html_entity_decode($res ['title']) ;
				
				
			$res ['matched_content'] = html_entity_decode($res ['matched_content']);
			$res ['cont'] = $res ['matched_content'];
		
		}// end entity decode
		
		
		// Clean googleads and <script tag
		$res['cont'] = preg_replace('{<ins.*?ins>}s', '', $res['cont']);
		
		if(! in_array('OPT_FEED_SCRIPT', $camp_opt))
		$res['cont'] = preg_replace('{<script.*?script>}s', '', $res['cont']);
		
		$res['cont'] = preg_replace('{\(adsbygoogle.*?\);}s', '', $res['cont']);
		$res ['matched_content'] = $res['cont'];
  
		// Extract tags from original source
		if(in_array('OPT_ORIGINAL_TAGS', $camp_opt) && trim($camp_general['cg_feed_custom_id_tag']) != ''){
				
			echo '<br>Extracting original post tags ';
				
			$cg_selector_tag=$camp_general['cg_custom_selector_tag'];
			$cg_selecotr_data_tag=$camp_general['cg_feed_custom_id_tag'];

			echo ' for '.$cg_selector_tag . ' = '.$cg_selecotr_data_tag;
				
			if( trim($original_cont) == ''){

				//get content
				curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
				curl_setopt ( $this->ch, CURLOPT_URL, trim ( html_entity_decode( $url ) ) );
				$original_cont = $this->curl_exec_follow ( $this->ch );
					
			}

			//dom class
			require_once 'inc/sxmldom_simple_html_dom.php';

			$original_html_tag = sxmldom_str_get_html($original_cont);
				
			unset($ret);
			if(method_exists($original_html_tag, 'find')){
					
				if($cg_selector_tag != 'xpath'){
					$ret = $original_html_tag->find('*['.$cg_selector_tag.'='.trim($cg_selecotr_data_tag).']');
				}else{
					$ret = $original_html_tag->find( trim($cg_selecotr_data_tag) );
				}
					
				$extract='';
					
				foreach ($ret as $itm ) {

					if(in_array('OPT_SELECTOR_INNER_TAG', $camp_opt)){
						$extract = $extract . $itm->innertext ;
					}else{
						$extract = $extract . $itm->outertext ;
					}
						
					if(in_array('OPT_SELECTOR_SINGLE_TAG', $camp_opt)){
						break;
					}

				}
					
					
					
				if(trim($extract) == ''){
					echo '<br>Nothing found to extract for this tag rule';
				}else{
					echo '<br>Tag Rule extracted ' . strlen($extract) .' charchters ';
					
					echo $extract;
					 
						
					if(stristr($extract, '<a')){
						preg_match_all('{<a .*?>(.*?)</a}', $extract,$tags_matches);

						$tags_founds = $tags_matches[1];
						$tags_founds = array_map('strip_tags', $tags_founds); 
						 
						$tags_str = implode(',', $tags_founds);

						echo ' found tags:'.$tags_str;
						$res['tags'] =$tags_str;

							
					}
						
				}
					
					
			}

			$original_html_tag->clear();
			unset($original_html_tag);
				
		}elseif(in_array('OPT_ORIGINAL_TAGS', $camp_opt)){
			
			echo '<br>You must add a valid ID/Class to Extract tags, No tags will get extracted.';
			
		}//extract tags

		//extract author from original source
		if(in_array('OPT_ORIGINAL_AUTHOR', $camp_opt) && trim( $camp_general['cg_feed_custom_id_author'] ) != '' ){

			echo '<br>Extracting original post author ';

			$cg_selector_author=$camp_general['cg_custom_selector_author'];
			$cg_selecotr_data_author=$camp_general['cg_feed_custom_id_author'];

			echo ' for '.$cg_selector_author . ' = '.$cg_selecotr_data_author;

			if( trim($original_cont) == ''){
			
				//get content
				curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
				curl_setopt ( $this->ch, CURLOPT_URL, trim ( html_entity_decode( $url ) ) );
				$original_cont = $this->curl_exec_follow ( $this->ch );
					
			}
				 
			//dom class
			require_once 'inc/sxmldom_simple_html_dom.php';
			$original_html_author = sxmldom_str_get_html($original_cont);
 
			unset($ret);
			if(method_exists($original_html_author, 'find')){
					
				if($cg_selector_author != 'xpath'){
					$ret = $original_html_author->find('*['.$cg_selector_author.'='.trim($cg_selecotr_data_author).']');
				}else{
					$ret = $original_html_author->find( trim($cg_selecotr_data_author) );
				}
					
				$extract='';
					
				foreach ($ret as $itm ) {

					if(in_array('OPT_SELECTOR_INNER_AUTHOR', $camp_opt)){
						$extract = $extract . $itm->innertext ;
					}else{
						$extract = $extract . $itm->outertext ;
					}

					if(in_array('OPT_SELECTOR_SINGLE_AUTHOR', $camp_opt)){
						break;
					}

				}
				
				// Validate returned author
				if(trim($extract) == ''){
					echo '<br>Nothing found to extract for this author rule';
				}else{
					echo '<br>author Rule extracted ' . strlen($extract) .' charchters ';
					
					 
					if(stristr($extract, '<a')){
						preg_match_all('{<a .*?>(.*?)</a}', $extract,$author_matches);

						$author_founds = $author_matches[1];
						$author_str = strip_tags($author_founds[0]);

						echo ' found author:'.$author_str;
						$res['author'] =$author_str;
					}

				}
				
				// Finished author extraction let's clear simpleDom
				$original_html_author->clear();
				unset($original_html_author);
			}


		}elseif(in_array('OPT_ORIGINAL_AUTHOR', $camp_opt)){
			
			if( trim($res['author']) == '' ){
			
				echo '<br>You must add a valid ID/Class to Extract Author, No Author will get extracted.';
			}
			
		}//extract author


		if(! in_array('OPT_ENCLUSURE', $camp_opt)){
			if(  isset($media_image_url)    &&    ! stristr($res['cont'], '<img') ){
				echo '<br>enclosure image:'.$media_image_url;
				$res['cont'] = '<img src="'.$media_image_url.'" /><br>' . $res['cont'];
					
			}
		}

		// Part to custom field OPT_FEED_PTF
		
		// Extracted custom fields ini
		$customFieldsArr = array();
		
		If( in_array('OPT_FEED_PTF', $camp_opt) ){
			
			echo '<br>Specific Part to custom field extraction';
			
			// Load rules
			$cg_part_to_field = trim( html_entity_decode( $camp_general['cg_part_to_field'] ) );
			$cg_part_to_field_parts = explode("\n", $cg_part_to_field);
			
			// Process rules
			foreach ( $cg_part_to_field_parts as $cg_part_to_field_part ){
				echo '<br>Rule:'.htmlentities($cg_part_to_field_part);
				
				// Validate format | 
				if( ! stristr($cg_part_to_field_part, '|')){
					echo '<- Wrong format...';
					continue;
				}
				
				// Parse rule
				$rule_parts = explode('|', $cg_part_to_field_part);
				$rule_method = trim( $rule_parts[0] );
				$rule_value  = trim( $rule_parts[1] );
				$rule_field  = trim( $rule_parts[2] );
				
				$rule_single = 0;
				@$rule_single = $rule_parts[3];
				
				// Validate rule
				if(trim($rule_method) == '' || trim($rule_value) == '' || trim($rule_field) == ''){
					echo '<- Wrong format...';
					continue;
				}
				
				// Validate rule method: class,id,regex,xpath
				if( $rule_method != 'id' && $rule_method != 'class' && $rule_method != 'regex' && $rule_method != 'xpath' ){
					echo '<- Wrong Method:'.$rule_method;
					continue;
				}
				
				// Now everything is valid let's execute the rule
				if( trim($original_cont) == ''){
				
					//get content
					curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
					curl_setopt ( $this->ch, CURLOPT_URL, trim ( html_entity_decode( $url ) ) );
					$original_cont = $this->curl_exec_follow ( $this->ch );
						
				}
				
				// id,class,xPath
				if( $rule_method == 'id' || $rule_method == 'class' || $rule_method == 'xpath' ){
					
					// Dom object
					$doc = new DOMDocument;
					
					// Load Dom
					@$doc->loadHTML($original_cont);
					
					// xPath object
					$xpath = new DOMXPath($doc);
					
					// xPath query
					if($rule_method != 'xpath'){
						$xpathMatches = $xpath->query("//*[@".$rule_method."='".$rule_value."']");
					}else{
						$xpathMatches = $xpath->query("$rule_value");
					}
					 
					// Single item ? 
					if($rule_single){
						$xpathMatches = array($xpathMatches->item(0));
					}
					
					// Rule result ini
					$rule_result = '';
					
					foreach ($xpathMatches as $xpathMatch){
						$rule_result.=  $xpathMatch->nodeValue;
					}
					 
					// Store field to be added
					if(trim($rule_result) != ''){
						echo ' <--'.$this->chars_count($rule_result). ' chars extracted';
						
						$customFieldsArr[] = array( $rule_field , $rule_result );
					}
					
				}else{
					
					// Regex extract
					$matchregex = array();
					$finalmatch = '';
					
					// Match
					preg_match_all('{'.trim($rule_value).'}is', $original_cont,$matchregex);
					
					 
					// Read matches
					for( $i=1 ; $i < count($matchregex);$i++ ){
					
						foreach($matchregex[$i] as $newmatch){
					
							if(trim($newmatch) !=''){
								if(trim($finalmatch) !=''){
									$finalmatch.=''.$newmatch;
								}else{
									$finalmatch.=$newmatch;
								}
							}
					
						}
					}// foreachMatches
					
					
					// Store field to be added
					if(trim($finalmatch) != ''){
						
						echo ' <--'.$this->chars_count($finalmatch). ' chars extracted';
					 	$customFieldsArr[] = array( $rule_field , $finalmatch );
					 	
					}
					
					
				}
				  
			}//foreach rule
			
			
		}//if part to field enabled
		
		$res['custom_fields'] = $customFieldsArr;
		
		//og:image check

		//$url="kenh14.vn/kham-pha/5-hoi-chung-benh-ky-quac-nghe-thoi-cung-toat-mo-hoi-hot-20151219200950753.chn";
	
		$currentOgImage = '' ; // for og:image found check 
		
		if( in_array('OPT_FEEDS_OG_IMG', $camp_opt)){
				
			if(in_array('OPT_FULL_FEED', $camp_opt)){
				
					
			}elseif(in_array('OPT_FEED_CUSTOM', $camp_opt)) {
					
				//valid original cont
					
			}elseif( in_array('OPT_FEED_CUSTOM_R', $camp_opt)){
					
				//valid original cont
					
			}else{
					
				//get content
					
				curl_setopt ( $this->ch, CURLOPT_HTTPGET, 1 );
				curl_setopt ( $this->ch, CURLOPT_URL, trim ( html_entity_decode( $url ) ) );
				$original_cont = $this->curl_exec_follow ( $this->ch );
		 			
			}
				
			//getting the og:image
			//let's find og:image

			// if no http
			$original_cont = str_replace('content="//', 'content="http://', $original_cont );

			echo '<br>Extracting og:image :';
				
			//let's find og:image may be the content we got has no image
			preg_match('{<meta[^<]*?property=["|\']og:image["|\'][^<]*?>}s', $original_cont,$plain_og_matches);
			
			 
			if(stristr($plain_og_matches[0], 'og:image')){
				preg_match('{content=["|\'](.*?)["|\']}s', $plain_og_matches[0],$matches);
				$og_img = $matches[1];
					
				if(trim($og_img) !=''){
					
					$res ['og_img']=$og_img ;
					echo $og_img;
					$currentOgImage = $og_img;
						
				}
					
			}
				
				
		}
		
			
		//check if image or not
		if( in_array ( 'OPT_MUST_IMAGE', $camp_opt ) &&   ! stristr($res['cont'], '<img') && trim($currentOgImage) == '' ) {
			
			echo '<br>Post contains no images skipping it ...';
			
			// Excluding it 
			$this->link_execlude($camp->camp_id, $original_url);

		}else{
			 
			//fix images
			$pars=parse_url($url);
			$host = $pars['host'];

			/*preg_match_all('{<img.*?src[\s]*=[\s]*["|\'](.*?)["|\'].*?>}is', $res['cont'] , $matches); */
			
			preg_match_all('{src[\s]*=[\s]*["|\'](.*?)["|\'].*?>}is', $res['cont'] , $matches);
			
			$img_srcs =  ($matches[1]);
			
			 
			
			foreach ($img_srcs as $img_src){
					
				$original_src = $img_src;

				// ../ remove
				if(stristr($img_src, '../')){
					$img_src = str_replace('../', '', $img_src);
				}
					
				if(stristr($img_src, 'http:') || stristr($img_src, 'www.') || stristr($img_src, 'https:')  || stristr($img_src, 'data:image') ){
					//valid image
				}else{
					//not valid image i.e relative path starting with a / or not or //
					$img_src = trim($img_src);
						
					if(preg_match('{^//}', $img_src)){
							
						$img_src = 'http:'.$img_src;
							
					}elseif( preg_match('{^/}', $img_src) ){
						$img_src = 'http://'.$host.$img_src;
					}else{
						$img_src = 'http://'.$host.'/'.$img_src;
					}
						

					$reg_img = '{["|\'][\s]*'.preg_quote($original_src,'{').'[\s]*["|\']}s';

					$res['cont'] = preg_replace( $reg_img, '"'.$img_src.'"', $res['cont']);
						
				}
					
			}
				
				
			//Fix relative links
			$res['cont'] = str_replace('href="../', 'href="http://'.$host.'/', $res['cont']);
			$res['cont'] = preg_replace('{href="/(\w)}', 'href="http://'.$host.'/$1', $res['cont']);
				
			//converting encoding
			//get original encoding

			if(in_array('OPT_FEED_CONVERT_ENC', $camp_opt) && ! in_array ( 'OPT_FULL_FEED', $camp_opt ) ){
				echo '<br>Converting encoding from '.$camp_general['cg_feed_encoding']. ' to utf-8';
				$res['cont'] = iconv( trim($camp_general['cg_feed_encoding']) , "UTF-8", $res['cont']);
			}
				
			 
			return $res;
		}

	}else{

		//duplicated link
		echo ' <-- duplicate in post <a href="'. admin_url('post.php?post='.$this->duplicate_id.'&action=edit') .'">#'.$this->duplicate_id.'</a>';

	}
	endforeach
	;

	echo '<br>End of feed items reached.';
	
	// Set isItemsEndReached flag to yes
	update_post_meta($camp->camp_id,$feedMd5.'_isItemsEndReached' , 'yes'  );
	 
} // end function


/*
 * ---* Feed Exists function ---
 */
	

function feed_link_exists($link, $camp_id) {
	$feed = md5 ( $link );
	echo '<br>-Link:' . $link;
	$query = "select * from {$this->wp_prefix}automatic_feeds_links where link = '$feed' and camp_id = '$camp_id'";
	if (count ( $this->db->get_results ( $query ) )) {
		echo '<---duplicate ';
		return true;
	} else {
		echo '<----new';
		return false;
	}
}
	

}