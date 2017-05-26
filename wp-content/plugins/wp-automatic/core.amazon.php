<?php 


// Main Class
require_once 'core.php';

Class WpAutomaticAmazon extends wp_automatic{

			/*
			 * ---* Get Amazon Post ---
			 */
			function amazon_get_post($camp) {
				
				// reading keywords that need to be processed
				$keywords = explode ( ',', $camp->camp_keywords );
				
				foreach ( $keywords as $keyword ) {
					
					$keyword = trim($keyword);
					
					if (trim ( $keyword ) != '') {
						$keyword = trim ( $keyword );
						echo ( '<br><b>Processing Keyword:</b>' . $keyword . '<hr>' );
						 
						//update last keyword
						update_post_meta($camp->camp_id, 'last_keyword', trim($keyword));
						
						// getting links from the db for that keyword
						$query = "select * from {$this->wp_prefix}automatic_amazon_links where link_keyword='{$camp->camp_id}_$keyword' and link_status ='0'";
						$res = $this->db->get_results ( $query );
						
						// when no links lets get new links
						if (count ( $res ) == 0) {
							$this->amazon_fetch_links ( $keyword, $camp );
							// getting links from the db for that keyword
							
							$res = $this->db->get_results ( $query );
						}
						
						//delete already posted items from other campaigns
						//deleting duplicated items
						$res_count = count($res);
						for($i=0;$i< $res_count ;$i++){
						
							$t_row = $res[$i];
							$t_link_url=$t_row->link_url;
						
							if( $this->is_duplicate($t_link_url) ){
									
								//duplicated item let's delete
								unset($res[$i]);
									
								echo '<br>Amazon Item ('. $t_row->link_title .') found cached but duplicated <a href="'.get_permalink($this->duplicate_id).'">#'.$this->duplicate_id.'</a>'  ;
									
								//delete the item
								$query = "delete from {$this->wp_prefix}automatic_amazon_links where link_id='{$t_row->link_id}'";
								$this->db->query ( $query );
									
							}else{
								break;
							}
							
						}
						
						// check again if valid links found for that keyword otherwise skip it
						if (count ( $res ) > 0) {
							
							// lets process that link
							$ret = $res [$i];
							
							echo '<br>Link:'.$ret->link_url;
				 
							
							$offer_title = $ret->link_title;
							$offer_desc = $ret->link_desc;
							$offer_url = $ret->link_url;
							$offer_price = trim($ret->link_price);
							$offer_img = $ret->link_img;
							
							$temp ['offer_title'] = $offer_title;
							$temp ['product_title'] = $offer_title;
							$temp ['offer_desc'] = $offer_desc;
							$temp ['product_desc'] = $offer_desc;
							$temp ['offer_url'] = $offer_url;
							$temp ['product_link'] = $offer_url;
							$temp ['source_link'] = $offer_url;
							$temp ['offer_price'] = $offer_price;
							$temp ['product_price'] = $offer_price;
							$temp ['offer_img'] = $offer_img;
							$temp ['product_img'] = $offer_img;
							$temp ['price_numeric'] = '00.00';
							$temp ['price_currency'] = '$';
							
							//increasing expiration date of the review 
							$ret->link_review = preg_replace('{exp\=20\d\d}', 'exp=2030', $ret->link_review);
							$ret->link_review = str_replace('http://', '//', $ret->link_review); 
							
							$temp ['review_link'] = $ret->link_review; 
							$temp ['review_iframe'] = '<iframe style="width:100%" class="wp_automatic_amazon_review" src="'.$ret->link_review.'" ></iframe>';
							
							//chart url 
							$enc_url = urldecode($offer_url);
							$enc_url = explode('?', $enc_url);
							$enc_parms =  $enc_url[1];
							$enc_parms_arr = explode('&',$enc_parms);
							
							$asin='';
							$tag = '' ;
							$subscription = '';
							
							foreach($enc_parms_arr as $param){
							
								if(stristr($param, 'creativeASIN')){
									$asin = str_replace('creativeASIN=', '', $param);
								}elseif(stristr($param, 'tag=')){
									$tag = str_replace('tag=', '', $param);
								}elseif( stristr($param, 'SubscriptionId')){
									$subscription = str_replace('SubscriptionId=', '', $param);
								}
							
							}
							
							$temp['product_asin'] = $asin;
							
							$region = $camp->camp_amazon_region;
							
							$chart_url = "http://www.amazon.$region/gp/aws/cart/add.html?AssociateTag=$tag&ASIN.1=$asin&Quantity.1=1&SubscriptionId=$subscription";
								
							
							$temp['chart_url'] = $chart_url;
							//price extraction 
							if(trim($ret->link_price) != ''){
								//good we have a price 
								$price_no_commas = str_replace(',', '', $offer_price);
								preg_match('{\d.*\d}is', ($price_no_commas),$price_matches);
							 
								$temp ['price_numeric'] = $price_matches[0];
								$temp ['price_currency'] =str_replace($price_matches[0], '', $offer_price);  
								
							}

							 
							$this->used_keyword = $ret->link_keyword;
							
							// update the link status to 1
							$query = "update {$this->wp_prefix}automatic_amazon_links set link_status='1' where link_id=$ret->link_id";
							$this->db->query ( $query );
							
							return $temp;
						} else {
							
							return false;
						}
					} // trim
				} // foreach keyword
			}
			
			/*
			 * ---* Get Amazon links ---
			 */
			function amazon_fetch_links($keyword, $camp) {
				echo "so I should now get some links from Amazon for keyword :" . $keyword;
				
				// ini
				$camp_opt = unserialize ( $camp->camp_options );
				
				if( stristr($camp->camp_general, 'a:') ) $camp->camp_general=base64_encode($camp->camp_general);
				$camp_general = unserialize ( base64_decode( $camp->camp_general) );
				
				$camp_general=array_map('stripslashes', $camp_general);
				$amazonPublic = get_option ( 'wp_amazonpin_abk', '' );
				$amazonSecret = get_option ( 'wp_amazonpin_apvtk', '' );
				$amazonAid = get_option ( 'wp_amazonpin_aaid', '' );
				
				if (trim ( $amazonPublic ) == '' || trim ( $amazonSecret ) == '' || trim ( $amazonAid ) == '') {
					$this->log ( 'Error', 'Amazon Public Key,Private Key and associate id required visit settings and add them' );
					echo '<br>Amazon Public Key,Private Key and associate id required visit settings and add them';
					return false;
				}
				
				// using clickbank
				$clickkey =   ( $keyword );
				
				// getting start
				$query = "select  * from {$this->wp_prefix}automatic_keywords where keyword_name='$keyword' and keyword_camp = {$camp->camp_id} ";
				$ret = $this->db->get_results ( $query );
				
				$row = $ret [0];
				$start = $row->amazon_start;
				
				
				// check if the start = -1 this means the keyword is exhausted
				if ($start == '-1' || $start == 11) {
					echo "<br>Keyword $keyword already exhausted and don't have any links to fetch";
					
					//check if it is reactivated or still deactivated
					if($this->is_deactivated($camp->camp_id, $keyword)){
						$start =1;
					}else{
						//still deactivated
						return false;
					}
					 
				}
				
				echo '<br>current page is ' . $start;
				
				// amazon
				
				$obj = new wp_automatic_AmazonProductAPI ( trim($amazonPublic), trim($amazonSecret) , trim($amazonAid), $camp->camp_amazon_region );
				
				try {
					
					//additional params
					$additionalParm=array();
					
					//node param
					if (in_array ( 'OPT_AMAZON_NODE', $camp_opt ) & trim ( $camp_general ['cg_am_node'] ) != '') {
						echo '<br>Specific node : ' . $camp_general ['cg_am_node'];
						$additionalParm['BrowseNode'] = $camp_general ['cg_am_node'];
					} 
					
		
					//min and max param
					$max = '';
					$min = '';
					
					if (in_array ( 'OPT_AM_PRICE', $camp_opt )) {
						$min = $camp_general ['cg_am_min'];
						$max = $camp_general ['cg_am_max'];
						
						echo '<br>Price range ' . $min . ' - ' . $max;
					}
					
					//search param
					if (in_array ( 'OPT_AMAZON_PARAM', $camp_opt )) {
						$additionalParm[$camp_general['cg_am_param_type']]=$camp_general['cg_am_param'];
					}
					
					//order param
					if (in_array ( 'OPT_AM_ORDER', $camp_opt )) {
						$additionalParm['Sort']=$camp_general['cg_am_order'];
					}
					
					//amazon merchant
					if (in_array ( 'OPT_AMAZON_MERCHANT', $camp_opt )) {
						$additionalParm['MerchantId']='Amazon';
					}
					
					$result = $obj->getItemByKeyword ( "$clickkey", $start, $camp->camp_amazon_cat, $additionalParm , $min, $max );
					
				} catch ( Exception $e ) {
					$this->log ( 'Amazon Error', $e->getMessage () );
					echo '<br>' . $e->getMessage ();
					return false;
				}
				
		 
				
				if ( isset($result->Items->Item) && count ( $result->Items->Item ) != 0) {
		
					
					 
					$pagesNum = $result->Items->TotalPages;
					
					 
					echo '<br>Available Pages:' . $pagesNum;
					
					$camp_cb_category = $camp->camp_cb_category;
				
				 
					echo '<ol>';
					foreach ( $result->Items->Item as $Item ) {
						
						 
						
				 		echo '<li>';
						
						// echo "Sales Rank : {$Item->SalesRank}<br>";
						echo "ASIN : {$Item->ASIN} ";
						echo " Link : <a href=\"{$Item->DetailPageURL}\">{$Item->ItemAttributes->Title}</a> <br>";
		
						$desc = '';
						@$desc = $Item->EditorialReviews->EditorialReview->Content;
						
		
						//Features existence 
						if(isset($Item->ItemAttributes->Feature)){
							echo '<br>Features found appending to the description';
							$desc .= implode( '<br>', (array) $Item->ItemAttributes->Feature );
						}
						
						 
						$desc = addslashes ( $desc );
						$title = addslashes ( $Item->ItemAttributes->Title );
						$linkUrl = (string)$Item->DetailPageURL;
						
					 
						 		
							if( $this->is_execluded($camp->camp_id,  $linkUrl) ){
								echo '<-- Execluded';
								continue;
							}
								
							  
							if (  ! $this->is_duplicate($linkUrl) ) {
								
								$price= '';
								
								@$price=$Item->Offers->Offer->OfferListing->Price->FormattedPrice;
								
								if(trim($price) == ''){
									@$price=$Item->ItemAttributes->ListPrice->FormattedPrice;
								}
								
								if(trim($price) == ''){
									@$price = $Item->OfferSummary->LowestNewPrice->FormattedPrice;
								}
								
								if(trim($price) == ''){
									@$price = $Item->OfferSummary->LowestCollectiblePrice->FormattedPrice;
								}
								
								if(trim($price) == ''){
									@$price = $Item->OfferSummary->LowestUsedPrice->FormattedPrice;
								}
								
								$imgurl = '';
								$imgurl=$Item->LargeImage->URL;
								
								if(trim($imgurl) == ''){
									//get it from the sets
									$imgurl=$Item->ImageSets->ImageSet[0]->LargeImage->URL;
								}
								
								//review url 
								$review = $Item->CustomerReviews->IFrameURL;
								
								 
								
								$query = "INSERT INTO {$this->wp_prefix}automatic_amazon_links ( link_url , link_title , link_keyword  , link_status ,link_desc,link_price,link_img,link_review)VALUES ( '$linkUrl', '$title', '{$camp->camp_id}_$keyword', '0','$desc','{$price}','{$imgurl}','{$review}')";
								$this->db->query ( $query );
							} else {
								echo ' <- duplicated <a href="'.get_edit_post_link($this->duplicate_id).'">#'.$this->duplicate_id.'</a>';
							}
							
							echo '</li>';
						 
					}
					
					echo '</ol>';
					
		 		} // if count
				
				if ( isset($result->Items->Item )  && count ( $result->Items->Item ) > 0) {
					// increment the start with 1
					$newstart = $start + 1;
					$query = "update {$this->wp_prefix}automatic_keywords set  amazon_start  = '$newstart' where keyword_name='$keyword'  and keyword_camp = {$camp->camp_id} ";
					$this->db->query ( $query );
					
					//if reached end of the items deactivate for an hour
					if($newstart == 11){
						
						if(! in_array('OPT_NO_DEACTIVATE', $camp_opt))
						$this->deactivate_key($camp->camp_id, $keyword);
					}
					
					return true;
				} else {
					// there was no links lets deactivate
					$newstart = '-1';
					$query = "update {$this->wp_prefix}automatic_keywords set amazon_start  = '$newstart' where keyword_name='$keyword'  and keyword_camp = {$camp->camp_id} ";
					$this->db->query ( $query );
					
					//deactivate key
					
					if(! in_array('OPT_NO_DEACTIVATE', $camp_opt))
					$this->deactivate_key($camp->camp_id, $keyword);
					
					echo '<br>No more items at amazon to get ';
					
					return false;
				}
			} // end func
			
}