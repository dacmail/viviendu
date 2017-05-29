<?php
/**
 * Init wwcAmazonSyncronize
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		0.1
 */
if (class_exists('wwcAmazonSyncronize') != true) {
class wwcAmazonSyncronize
{
    /*
     * Some required plugin information
     */
    const VERSION = '1.0';

	/**
	 * cfg
	 *
	 * @var array
	 */
	private $cfg = array(
		//'available_setup' => array('hourly', 'twicedaily', 'daily')
	);
    private static $rules_default = array('price', 'title', 'url'); // rules available till you save sync settings first time!

	/**
	 * WooZone
	 *
	 * @var array
	 */
	public $the_plugin = null;
    private $alias = '';
	
	private $settings;

	/**
	 * WooZone
	 *
	 * @var db
	 */
	private $db = null;
    
    private static $sql_chunk_limit = 2000;
    
    private static $log_email_to = '';
    private static $log_send_mail = false;
    private static $log_save_file = false;


	/**
	 * @params main class object
	 */
	public function __construct($WooZone)
	{
		global $wpdb;

		$this->db = $wpdb;

		$this->the_plugin = $WooZone;
        $this->alias = $this->the_plugin->alias;
		
		$this->settings = $this->the_plugin->getAllSettings('array', 'amazon');

		$ss = get_option($this->alias . '_sync', array());
		$ss = maybe_unserialize($ss);
		$ss = $ss !== false ? $ss : array();
		$this->cfg['available_setup'] = array_merge(array(
			'sync_products_per_request'				=> 20, // Products to sync per each cron request
			'sync_hour_start'								=> '',
			'sync_recurrence'								=> 24,
			'sync_fields'										=> array(),
		), $ss);

        $this->updateSyncRules();
        
        // ajax  helper
        //add_action('wp_ajax_WooZoneSyncProd', array( &$this, 'ajax_request' ));
	}

    // store into cfg array, no returns
    public function updateSyncRules()
    {
        $this->cfg['available_setup']['sleep'] = 1; // Pause between products in seconds. Default is 1
        if ( !isset($this->cfg['available_setup']['sync_products_per_request'])
            || empty($this->cfg['available_setup']['sync_products_per_request']) ) {
            $this->cfg['available_setup']['sync_products_per_request'] = 20; // Products to sync per each cron request
        }

        /*$this->cfg['sync_rules'] = array(
            'title'         => isset($this->cfg['available_setup']['title']) ? true : false,
            'reviews'       => isset($this->cfg['available_setup']['reviews']) ? true : false,
            'price'         => isset($this->cfg['available_setup']['price']) ? true : false,
            'url'           => isset($this->cfg['available_setup']['url']) ? true : false,
            'content'       => isset($this->cfg['available_setup']['desc']) ? true : false,
            'sku'           => isset($this->cfg['available_setup']['sku']) ? true : false,
            'sales_rank'    => isset($this->cfg['available_setup']['sales_rank']) ? true : false,
            'image'         => isset($this->cfg['available_setup']['image']) ? true : false
        );*/
        foreach (array('price', 'title', 'url', 'desc', 'sku', 'sales_rank', 'reviews', 'short_desc') as $rule) {
            $this->cfg['sync_rules']["$rule"] = !isset($this->cfg['available_setup']['sync_fields'])
                && in_array($rule, self::$rules_default) ? true : false;
            $this->cfg['sync_rules']["$rule"] = isset($this->cfg['available_setup']['sync_fields'])
                && in_array($rule, $this->cfg['available_setup']['sync_fields']) ? true : $this->cfg['sync_rules']["$rule"];
        }
        return $this->cfg;
    }


    /**
     * Sync products!
     */
	public function updateTheProduct( $asins=array(), $return='die' )
	{
	    $sep = PHP_EOL;
        $asins_notfound = array();
        $asins_updated = array();
		$asins_details = array();
		
		$prod_key = '_amzASIN';
        
        $is_from_cron = isset($_REQUEST['asin']) || isset($_REQUEST['id']) ? false : true;
 
		if ( empty($asins) ) {
		    if (1) {
                $ret = array(
                    'status' => 'invalid',
                    'msg' => "No ASINs provided!",
                );
                if( $return == 'print_return' ){
                    $ret['msg'] = str_replace('[sep]', '<br />', $ret['msg']);
                    echo $ret['msg']; die('<br />stop.');
                } else if( $return == 'return' ){
                    $ret['msg'] = str_replace('[sep]', PHP_EOL, $ret['msg']);
                    return $ret;
                } else{
                    die(json_encode($ret));
                }
            }
		}

        $this->updateSyncRules();

		//$delete_unavailable_products = !isset($this->settings['delete_unavailable_products'])
		//	|| ('yes' == $this->settings['delete_unavailable_products']) ? true : false;
		$delete_unavailable_products = (int) $this->the_plugin->sync_tries_till_trash;
		if ( isset($this->settings['fix_issue_sync'], $this->settings['fix_issue_sync']['trash_tries']) ) {
			$delete_unavailable_products = (int) $this->settings['fix_issue_sync']['trash_tries'];
		}

		if ( !is_array($asins) ) {
			global $wpdb;
			
			$post_id = $wpdb->get_var("select a.post_id from $wpdb->postmeta as a where 1=1 and a.meta_key = '$prod_key' and a.meta_value='" . ($asins) . "';");
			
			$asins = array($post_id => $asins);
		}
		//var_dump('<pre>', $asins, '</pre>'); die('debug...'); 
 
		$provider = 'amazon';
		$rsp = $this->the_plugin->get_ws_object( $provider )->api_make_request(array(
			'amz_settings'			=> $this->the_plugin->amz_settings,
			'from_file'				=> str_replace($this->the_plugin->cfg['paths']['plugin_dir_path'], '', __FILE__),
			'from_func'				=> __FUNCTION__ != __METHOD__ ? __METHOD__ : __FUNCTION__,
			'requestData'			=> array(
				'asin'					=> $asins,
			),
			'optionalParameters'	=> array(),
			'responseGroup'			=> 'Large,ItemAttributes,OfferFull,Variations,Reviews,PromotionSummary,SalesRank',
			'method'				=> 'lookup',
		));
		$products = $rsp['response'];

        $respStatus = $this->the_plugin->amzHelper->is_amazon_valid_response( $products );
        if ( $respStatus['status'] != 'valid' ) { // error occured!
        //if($products['Items']["Request"]["IsValid"] != "True"){
            if (1) {
 
                // remove ASINs not found on Amazon anymore!
                $asins_notfound = array_diff($asins, array());
                if ( count($asins_notfound) > 0 ) {
                    
                    // asins not found in db!
                    $__sync_prod_notfound = get_option('WooZone_sync_prod_notfound', true);
                    if ( !is_array($__sync_prod_notfound) || empty($__sync_prod_notfound) )
                        $__sync_prod_notfound = array();
                    $__sync_prod_notfound = $__sync_prod_notfound + $asins_notfound;
                    $__sync_prod_notfound = array_unique($__sync_prod_notfound);
    
                    update_option('WooZone_sync_prod_notfound', $__sync_prod_notfound);
              
                    foreach ($asins_notfound as $localID => $asin) {
                        $this->trash_post( $localID, $delete_unavailable_products ); // product & product variations if it's the case!
    
                        update_post_meta( $localID, "_amzaff_sync_last_status", 0 );
                        update_post_meta( $localID, "_amzaff_sync_last_status_msg", 'error (not found)' );
                        update_post_meta( $localID, "_amzaff_sync_last_date", $this->the_plugin->last_update_date() );
                        
                        update_post_meta( $localID, "_amzaff_sync_hits_prev", (int) get_post_meta($localID, "_amzaff_sync_hits_prev", true) + 1 );
                    }
                    
                    // update WooZone_remaining_at, take this as marker for features sync
                    $last_one = array_keys($asins);
                    $last_one = end($last_one);
                    if ( $is_from_cron ) {
                        update_option('WooZone_sync_last_updated_product', $last_one);
                    }
                }

                $ret = array(
                    'status' => 'invalid',
                    'msg' => ''
                        . ( true
                            ? 'Amazon Error (IsValid is False): '
                            . '[sep]' . $respStatus['code'] . ' - ' . $respStatus['msg'] //( isset($products['Items']['Request']['Errors']['Error']['Message']) ? $products['Items']['Request']['Errors']['Error']['Message'] : serialize($products['Items']['Request']['Errors']['Error']) )
                            : ''
                        )
                        . ( true
                            ? '[sep]' . 'Products - (ID, ASIN) pairs: '
                            . '[sep]' . implode(', ', array_map(array($this->the_plugin, 'prepareForPairView'), $asins, array_keys($asins)))
                            : ''
                        )
                );
                if( $return == 'print_return' ){
                    $ret['msg'] = str_replace('[sep]', '<br />', $ret['msg']);
                    echo $ret['msg']; die('<br />stop.');
                } else if( $return == 'return' ){
                    $ret['msg'] = str_replace('[sep]', PHP_EOL, $ret['msg']);
                    return $ret;
                } else{
                    die(json_encode($ret));
                }
            }
        }
            
		if(1){
			$arrProds = array();
			/*
  			if ( isset($products['Items']['Request']['ItemLookupRequest']['ItemId'])
				&& !is_array($products['Items']['Request']['ItemLookupRequest']['ItemId'])
				|| ( is_array($products['Items']['Request']['ItemLookupRequest']['ItemId']) && count($products['Items']['Request']['ItemLookupRequest']['ItemId']) <= 1 ) ) {
  				$arrProds[] = $products['Items']['Item'];
  			} else {
  				$arrProds = $products['Items']['Item'];  				
  			}
			*/
            if ( isset($products['Items']['Item']['ASIN']) ) {
                $arrProds[] = $products['Items']['Item'];
            } else {
  				$arrProds = $products['Items']['Item'];  				
  			}
            $arrProds = (array) $arrProds;
 
            if ( empty($arrProds) ) {
                // update WooZone_remaining_at, take this as marker for features sync
                if ( $is_from_cron ) {
                    update_option('WooZone_sync_last_updated_product', end(array_keys($asins)));
                }
            }

			foreach ($arrProds as $thisProd) { // products loop
 
				$localID = 0;
				if(count($thisProd) > 0) { // product is amazon valid
					// start creating return array
					$retProd = array();
	
					$retProd = $this->the_plugin->amzHelper->build_product_data( $thisProd );

					/*					
					$retProd['ASIN'] = $thisProd['ASIN'];
	
                    // start creating return array
                    $retProd['hasGallery'] = 'false';
                     
                    // has gallery: get gallery images
                    if(isset($thisProd['ImageSets']) && count($thisProd['ImageSets']) > 0){
                        
                        //$count = 0;
                        foreach ($thisProd['ImageSets']["ImageSet"] as $key => $value){
                            
                            if( isset($value['LargeImage']['URL']) ){
                                $retProd['hasGallery'] = 'true';
                                break;
                            }
                            //$count++;
                        }
                    }

					// CustomerReviews url
					if($thisProd['CustomerReviews']['HasReviews']){
						$retProd['CustomerReviewsURL'] = $thisProd['CustomerReviews']['IFrameURL'];
					}
	
					// DetailPageURL
					$retProd['DetailPageURL'] = $thisProd['DetailPageURL'];
	
					// product title
					$retProd['Title'] = isset($thisProd['ItemAttributes']['Title']) ? $thisProd['ItemAttributes']['Title'] : '';
	
					// Binding
					$retProd['Binding'] = isset($thisProd['ItemAttributes']['Binding']) ? $thisProd['ItemAttributes']['Binding'] : '';
	
					// ProductGroup
					$retProd['ProductGroup'] = isset($thisProd['ItemAttributes']['ProductGroup']) ? $thisProd['ItemAttributes']['ProductGroup'] : '';
	
					// SKU
					$retProd['SKU'] = isset($thisProd['ItemAttributes']['SKU']) ? $thisProd['ItemAttributes']['SKU'] : '';
	
					// Feature
					$retProd['Feature'] = isset($thisProd['ItemAttributes']['Feature']) ? $thisProd['ItemAttributes']['Feature'] : '';
					
					// EditorialReviews
					$retProd['EditorialReviews'] = isset($thisProd['EditorialReviews']['EditorialReview']['Content']) ? $thisProd['EditorialReviews']['EditorialReview']['Content'] : '';
					
					// The product Offers
					$retProd['Offers'] = isset($thisProd['Offers']) ? $thisProd['Offers'] : array();
					$retProd['OfferSummary'] = isset($thisProd['OfferSummary']) ? $thisProd['OfferSummary'] : array();
					
					// The product Item attribues
					$retProd['ItemAttributes'] = isset($thisProd['ItemAttributes']) ? $thisProd['ItemAttributes'] : array();
					
					// The product VariationSummary
					$retProd['VariationSummary'] = isset($thisProd['VariationSummary']) ? $thisProd['VariationSummary'] : array();

					// Product Sales Rank
					$retProd['SalesRank'] = isset($thisProd['SalesRank']) ? $thisProd['SalesRank'] : 999999;
					*/

					$requestData = array();
					$requestData['debug_level'] = isset($_REQUEST['debug_level']) ? (int)$_REQUEST['debug_level'] : 0;
					// print some debug if requested
					if( $requestData['debug_level'] > 0 ) {
						if( $requestData['debug_level'] == 1) var_dump('<pre>', $retProd,'</pre>');
						if( $requestData['debug_level'] == 2) var_dump('<pre>', $product ,'</pre>');
						die;
					}
  
					foreach ($asins as $code_key => $code_value) {
						if( $retProd['ASIN'] == $code_value){
							$localID = $code_key;
							$asins_updated[$localID] = $retProd['ASIN'];
							$asins_details[$localID]['Title'] = $retProd['Title'];
                            break;
						}
					}

                    if ( $localID <= 0 ) continue 1;
  
                    // update product!
				    $this->the_plugin->updateWooProduct($retProd, $this->cfg['sync_rules'], $localID);
	
                    // product meta!
                    update_post_meta( $localID, "_amzaff_sync_last_status", 1 );
                    update_post_meta( $localID, "_amzaff_sync_last_status_msg", 'success' );
                    update_post_meta( $localID, "_amzaff_sync_last_date", $this->the_plugin->last_update_date() );
                    update_post_meta( $localID, "_amzaff_sync_hits", (int) get_post_meta($localID, "_amzaff_sync_hits", true) + 1 );
                    update_post_meta( $localID, "_amzaff_sync_hits_prev", (int) get_post_meta($localID, "_amzaff_sync_hits_prev", true) + 1 );
					update_post_meta( $localID, "_amzaff_sync_trash_tries", 0 );

                    // update WooZone_remaining_at, take this as marker for features sync
                    if ( $is_from_cron ) {
                        update_option('WooZone_sync_last_updated_product', $localID);
                    }
                    
                    // new cycle => first product updated date
                    if ( $is_from_cron ) {
                        $first_updated_date = get_option('WooZone_sync_first_updated_date', '');
                        $last_updated_product = get_option('WooZone_sync_last_updated_product', 0);
                        if ( empty($last_updated_product) ) {
                            update_option('WooZone_sync_first_updated_date', $this->the_plugin->last_update_date());
                        }
                    }

					/*$product_meta['product'] = array();
					$product_meta['product']['price_update_date'] = get_post_meta($localID, "_price_update_date", true);
					$product_meta['product']['sales_price'] = get_post_meta($localID, "_sale_price", true);
					$product_meta['product']['regular_price'] = get_post_meta($localID, "_regular_price", true);
					$product_meta['product']['price'] = get_post_meta($localID, "_price", true);
					
					if ( empty($product_meta['product']['sales_price']) && empty($product_meta['product']['regular_price']) ) {
						$product_meta['product']['variation_price'] = array('min' => get_post_meta($localID, "_min_variation_price", true), 'max' => get_post_meta($localID, "_max_variation_price", true));
					}
  
					if( $return == 'print_return' ){
						echo 'Update, OK - ' . ( $localID ) . ' <br />';
					}else{
						die(json_encode(array(
							'status' 	=> 'valid',
							'data' 		=> array(
								'regular_price' => (isset($product_meta['product']['regular_price']) && (float)$product_meta['product']['regular_price'] > 0 ? woocommerce_price( $product_meta['product']['regular_price'] ) : '&#8211;'),
								'sales_price' => (isset($product_meta['product']['sales_price']) && (float)$product_meta['product']['sales_price'] > 0 ? woocommerce_price( $product_meta['product']['sales_price'] ) : '&#8211;'),
								'last_sync_date' => $last_sync_date,
								'variation_price' => array(
									'min' => (isset($product_meta['product']['variation_price']['min']) && (float)$product_meta['product']['variation_price']['min'] > 0 ? woocommerce_price( $product_meta['product']['variation_price']['min'] ) : '&#8211;'),
									'max' => (isset($product_meta['product']['variation_price']['max']) && (float)$product_meta['product']['variation_price']['max'] > 0 ? woocommerce_price( $product_meta['product']['variation_price']['max'] ) : '&#8211;')
								)
							)
						)));
					}*/
					
					if( (int)$this->cfg['available_setup']['sleep'] > 0 ) {
					    sleep( (int)$this->cfg['available_setup']['sleep'] );
                    }

				} // end product is amazon valid
			} // end products loop

			// remove ASINs not found on Amazon anymore!
			$asins_notfound = array_diff($asins, $asins_updated);
			if ( count($asins_notfound) > 0 ) {
				
                // asins not found in db!
    			$__sync_prod_notfound = get_option('WooZone_sync_prod_notfound', true);
    			if ( !is_array($__sync_prod_notfound) || empty($__sync_prod_notfound) )
    				$__sync_prod_notfound = array();
    			$__sync_prod_notfound = $__sync_prod_notfound + $asins_notfound;
    			$__sync_prod_notfound = array_unique($__sync_prod_notfound);

    			update_option('WooZone_sync_prod_notfound', $__sync_prod_notfound);
                
    			foreach ($asins_notfound as $localID => $asin) {
    			    $this->trash_post( $localID, $delete_unavailable_products ); // product & product variations if it's the case!

                    update_post_meta( $localID, "_amzaff_sync_last_status", 0 );
                    update_post_meta( $localID, "_amzaff_sync_last_status_msg", 'error (not found)' );
                    update_post_meta( $localID, "_amzaff_sync_last_date", $this->the_plugin->last_update_date() );
                    
                    update_post_meta( $localID, "_amzaff_sync_hits_prev", (int) get_post_meta($localID, "_amzaff_sync_hits_prev", true) + 1 );
    			}
                
                // update WooZone_remaining_at, take this as marker for features sync
                $last_one = array_keys($asins);
                $last_one = end($last_one);
                if ( $is_from_cron ) {
                    update_option('WooZone_sync_last_updated_product', $last_one);
                }
			}
            
            if (1) {
                $ret = array(
                    'status'            => count($asins) == count($asins_notfound) ? 'invalid' : 'valid',
                    'asins'             => $asins,
                    'asins_notfound'    => $asins_notfound,
                    'asins_updated'     => $asins_updated,
                    'asins_details'     => $asins_details,
                );
                $ret = array_merge($ret, array(
                    'msg' => ''
                        . ( count($asins) != count($asins_updated)
                            ? 'Amazon Error: '
                            . '[sep]' . $respStatus['code'] . ' - ' . $respStatus['msg'] //( isset($products['Items']['Request']['Errors']['Error']['Message']) ? $products['Items']['Request']['Errors']['Error']['Message'] : serialize($products['Items']['Request']['Errors']['Error']) )
                            : ''
                        )
                        . ( !empty($asins_updated)
                            ? '[sep]' . 'Products synced - (ID, ASIN) pairs: '
                            . '[sep]' . implode(', ', array_map(array($this->the_plugin, 'prepareForPairView'), $asins_updated, array_keys($asins_updated)))
                            : ''
                        )
                        . ( !empty($asins_notfound)
                            ? '[sep]' . 'Products Not synced - (ID, ASIN) pairs: '
                            . '[sep]' . implode(', ', array_map(array($this->the_plugin, 'prepareForPairView'), $asins_notfound, array_keys($asins_notfound)))
                            : ''
                        )
                ));
                if( $return == 'print_return' ){
                    $ret['msg'] = str_replace('[sep]', '<br />', $ret['msg']);
                    echo $ret['msg']; die('<br />stop.');
                } else if( $return == 'return' ){
                    $ret['msg'] = str_replace('[sep]', PHP_EOL, $ret['msg']);
                    return $ret;
                } else{
                    die(json_encode($ret));
                }
            }
		}
	}

    private function update_the_products( $products, $return='die' ) {
        $this->updateSyncRules();

        $updStats = array();
        if (count($products) > 0) {

            $amz_products = array(); 
            foreach ($products as $key => $value){
                $amz_products["$key"] = $value->meta_value;
            }

            $amz_products = array_unique($amz_products);
            foreach (array_chunk($amz_products, 10, true) as $products) {
                $updStats[] = $this->updateTheProduct( $products, $return );
            }
            
            if (1) {
                $ret = array(
                    'status' => 'valid',
                    'products' => $products,
                    'msg' => 'see chunks_status key for details.',
                    'chunks_status' => $updStats,
                );
            }
        } else {
            if (1) {
                $ret = array(
                    'status' => 'invalid',
                    'products' => $products,
                    'msg' => 'No products selected - maybe all products are already updated!',
                );
            }
        }

        if (1) {
            if( $return == 'print_return' ){
                $ret['msg'] = str_replace('[sep]', '<br />', $ret['msg']);
                echo $ret['msg'];
            } else if( $return == 'return' ){
                $ret['msg'] = str_replace('[sep]', PHP_EOL, $ret['msg']);
                return $ret;
            } else{
                die(json_encode($ret));
            }
        }
    }

    private function get_products() {
        $this->updateSyncRules();

        $products = $this->select_products();
        //var_dump('<pre>', $products, '</pre>'); die('debug...'); 
        
        $products = $this->filter_products($products);
        //var_dump('<pre>', $products, '</pre>'); die('debug...');
        
        return $products;
    }

    private function select_products() {
        global $wpdb;
		
		$prod_key = '_amzASIN';
   
        $last_updated_product = (int) get_option('WooZone_sync_last_updated_product', 0);

        // get products (simple or just parents without variations)
        $sql = trim("
            select
                p.ID, pm.meta_value
            from
                $wpdb->posts as p
            left join
                $wpdb->postmeta as pm on p.ID = pm.post_id
            where 1=1
                %s
                and p.post_type in ('product', 'product_variation')
                and p.post_status = 'publish'
                and pm.meta_key = '$prod_key' and !isnull(pm.meta_value)
            order by p.ID asc;
        ");

        $clause = array();
        $clause[] = "and p.ID > $last_updated_product";
        
        $sql = sprintf($sql, implode(' ', $clause));
        $res = $wpdb->get_results( $sql, OBJECT_K );
        //var_dump('<pre>', $res, '</pre>'); die('debug...');
        
        if ( empty($res) ) return array();
        return $res;
    }
    
    private function filter_products( $products=array() ) {
        if ( empty($products) ) return array();
        
        global $wpdb;
        
        $last_updated_product = (int) get_option('WooZone_sync_last_updated_product', 0);
        
        // range size
        $nrOfProducts = (int) $this->cfg['available_setup']['sync_products_per_request'];
        //if( $nrOfProducts == 0 ) $nrOfProducts = 10;

        // products IDs
        $productsId = array_keys($products);
 
        // get products _amzaff_sync_last_date
        $prods2lastdate = array();
        foreach (array_chunk($productsId, self::$sql_chunk_limit, true) as $current) {

            $currentP = implode(',', array_map(array($this->the_plugin, 'prepareForInList'), $current));

            $sql = "SELECT pm.post_id, pm.meta_value FROM $wpdb->postmeta as pm WHERE 1=1 AND pm.meta_key = '_amzaff_sync_last_date' AND pm.post_id IN ($currentP) ORDER BY pm.post_id ASC;";
            
            $clause = array();
            $clause[] = "and pm.post_id > $last_updated_product";
            
            $sql = sprintf($sql, implode(' ', $clause));
            $res = $wpdb->get_results( $sql, OBJECT_K );
            $prods2lastdate = $prods2lastdate + $res; //array_replace($prods2lastdate, $res);
        }
        //var_dump('<pre>', $prods2lastdate, '</pre>'); die('debug...'); 
 
        $current_time = time();
        $recurrence = (int) ( $this->cfg['available_setup']['sync_recurrence'] * 3600 );
        $selectedProducts = array();
        $added = 0;
        foreach ($products as $key => $val) {

            if ( $nrOfProducts && ($added >= $nrOfProducts) ) break 1;

            $last_sync_date = isset($prods2lastdate["$key"]) ? $prods2lastdate["$key"]->meta_value : false;
            if ( empty($last_sync_date) || ( $current_time >= ($last_sync_date + $recurrence) ) ) {
                $selectedProducts["$key"] = $val;
                $added++;
            }
        }
        //var_dump('<pre>', $selectedProducts, '</pre>'); die('debug...'); 
        return $selectedProducts;
    }


    /**
     * Cronjobs methods
     */
    public function cron_full_cycle( $pms, $return='die' ) {
        $ret = array('status' => 'failed');

		if ( !WooZone()->can_import_products() || WooZone()->is_aateam_demo_keys() ) {
			//$ret = array_merge($ret, array(
			//    'status'            => 'done',
			//));
			return $ret;
		}

        $current_cron_status = $pms['status']; //'new'; //
        $current_time = time(); // GMT current time
        $first_updated_date = (int) get_option('WooZone_sync_first_updated_date', 0);
        $recurrence = (int) ( $this->cfg['available_setup']['sync_recurrence'] * 3600 );
        //var_dump('<pre>', $current_time, $first_updated_date, $recurrence, $current_time >= ( $first_updated_date + $recurrence ), '</pre>'); die('debug...'); 
        
        // recurrence interval fulfilled
        if ( /*1 || */$current_time >= ( $first_updated_date + $recurrence ) ) {
            
            // assurance verification: reset in any case after more than 3 times the current setted recurrence interval
            $do_reset = $current_time >= ( $first_updated_date + $recurrence * 3 ) ? true : false;
            $current_cycle_done = isset($pms['verify'], $pms['verify']['sync_products'])
                && $pms['verify']['sync_products'] == 'stop' ? true : false;
            
            // current cycle not yet completed and not yet reached assurance verification
            if ( !$current_cycle_done && !$do_reset ) {
                return $ret;
            }
            
            // here we can save WooZone_sync_cycle_stats to log before reset them bellow...
            if ( self::$log_send_mail || self::$log_save_file ) {

                $logStat = $this->save_log();
                $ret = array_merge($ret, array(
                    'logStat'        => $logStat,
                ));
            }
            
            update_option('WooZone_sync_last_updated_product', 0);
            update_option('WooZone_sync_first_updated_date', time());
            update_option('WooZone_sync_currentlist_last_product', $this->currentlist_last_product());
            update_option('WooZone_sync_currentlist_nb_products', $this->currentlist_last_product(true));
            
            $cycle_stats = get_option('WooZone_sync_cycle_stats', array());
			$cycle_stats = is_array($cycle_stats) ? $cycle_stats : array();
            $cycle_stats = array_merge($cycle_stats, array(
                'start_time'        => '',
                'end_time'          => '',
            ));
            update_option('WooZone_sync_cycle_stats', $cycle_stats);

            $ret = array_merge($ret, array(
                'status'        => 'done',
            ));

            // depedency
            if ( isset($pms['depedency'], $pms['depedency']["$current_cron_status"])
                && !empty($pms['depedency']["$current_cron_status"]) ) {
                $ret = array_merge($ret, array(
                    'depedency' => $pms['depedency']["$current_cron_status"]
                ));
            }
        }
        return $ret;
    }
    public function cron_small_bulk( $pms, $return='die' ) {
        $ret = array('status' => 'failed');

		if ( !WooZone()->can_import_products() || WooZone()->is_aateam_demo_keys() ) {
			//$ret = array_merge($ret, array(
			//    'status'            => 'done',
			//));
			return $ret;
		}

        $current_cron_status = $pms['status']; //'new'; //
        
        $currentlist_last_product = (int) get_option('WooZone_sync_currentlist_last_product', 0);
        $products = $this->get_products();
        $first_from_current = (int) current(array_keys($products));
        //var_dump('<pre>', $currentlist_last_product, $products, $first_from_current, '</pre>'); die('debug...');

        if (1) {
            $cycle_stats = get_option('WooZone_sync_cycle_stats', array());
			$cycle_stats = is_array($cycle_stats) ? $cycle_stats : array();
            if ( !isset($cycle_stats['start_time']) || empty($cycle_stats['start_time']) ) {
                $cycle_stats = array_merge($cycle_stats, array(
                    'start_time'        => time(),
                ));
                update_option('WooZone_sync_cycle_stats', $cycle_stats);
            }
        }

        // no more products to sync or ( current products cycle last product ID is less then first product from current selected products list )
        if ( empty($products) || $currentlist_last_product < $first_from_current ) {
            $ret = array_merge($ret, array(
                'status'        => 'stop',
            ));
            
            $cycle_stats = array_merge($cycle_stats, array(
                'end_time'          => time(),
            ));
            update_option('WooZone_sync_cycle_stats', $cycle_stats);
            
            // depedency
            if ( isset($pms['depedency'], $pms['depedency']["$current_cron_status"])
                && !empty($pms['depedency']["$current_cron_status"]) ) {
                $ret = array_merge($ret, array(
                    'depedency' => $pms['depedency']["$current_cron_status"]
                ));
            }
        } else {
            $products_status = $this->update_the_products( $products, $return );
            
            $ret = array_merge($ret, array(
                'status'            => 'done',
                'products_status'   => $products_status,
            ));
        }
        return $ret;
    }
    
    private function currentlist_last_product( $count=false ) {
        global $wpdb;
        
		$prod_key = '_amzASIN';
		
        $sql = trim("
            select
                " . ( $count ? "count(p.ID)" : "p.ID" ) . "
            from
                $wpdb->posts as p
            left join
                $wpdb->postmeta as pm on p.ID = pm.post_id
            where 1=1
                and p.post_type in ('product', 'product_variation')
                and p.post_status = 'publish'
                and pm.meta_key = '$prod_key' and !isnull(pm.meta_value)
            " . ( $count ? "" : "order by p.ID desc limit 1" ) . ";
        ");
        //var_dump('<pre>', $sql, '</pre>'); die('debug...'); 
            
        $res = $wpdb->get_var( $sql );
        return $res;
    }

    private function trash_post( $post_id, $do_trash=-1 ) {
        if ( empty($post_id) ) return true;
        global $wpdb;
		
		$allowed_tries = (int) $do_trash;
		$do_trash = ( -1 ==  $allowed_tries ? false : true );
		
		// don't trash unavailable products
		if ( !$do_trash ) {
			return false;
		}
		
		update_post_meta( $post_id, "_amzaff_sync_trash_tries", (int) get_post_meta($post_id, "_amzaff_sync_trash_tries", true) + 1 );
		$sync_trash_tries = (int) get_post_meta($post_id, "_amzaff_sync_trash_tries", true);
		if ( $sync_trash_tries < $allowed_tries ) {
			return false;
		}
		
		// still some tries till trash product

        // delete the product if no longer available on Amazon
        wp_trash_post( $post_id );
 
        // delete all variations of this product also
        
        // get product variations (only childs, no parents)
        $sql_childs = "SELECT p.ID, p.post_parent FROM $wpdb->posts as p WHERE 1=1 AND p.ID = '$post_id' AND p.post_status = 'publish' AND p.post_parent > 0 AND p.post_type = 'product_variation' ORDER BY p.ID ASC;";
        $res_childs = $wpdb->get_results( $sql_childs, OBJECT_K );
        //var_dump('<pre>',$res_childs,'</pre>');  
        
        foreach ( (array) $res_childs as $child_id => $child ) {
            wp_trash_post( $child_id );
        }
        return true;
    }
    
    private function save_log() {
        global $wpdb;
        
        $ret = array();
        
        $opt_sync = $this->alias . '_sync';
        $sql = "select o.option_name, o.option_value from $wpdb->options as o where 1=1 and o.option_name regexp '^$opt_sync' order by o.option_name asc;";
        $res = $wpdb->get_results($sql, OBJECT_K);
        
        $msg = array();
        foreach ( (array) $res as $opt_name => $opt ) {
            if ( in_array($opt_name, array('WooZone_sync_prod_notfound')) ) {
                continue 1;
            }
            $opt_val = maybe_unserialize($opt->option_value);
            $msg["$opt_name"] = $opt_val;
        }
        
        if ( self::$log_send_mail ) {
            $sendMailStat = $this->log_send_mail( $msg );
        }
        if ( self::$log_save_file ) {
            $saveFileStat = $this->log_save_file( $msg );
        }
        
        return array_merge($ret, array(
            'msg'               => $msg,
            'sendMailStat'      => $sendMailStat,
            'saveFileStat'      => $saveFileStat,
        ));
    }
    private function log_send_mail( $msg=array() ) {
        // send email
        add_filter('wp_mail_content_type', array($this->the_plugin, 'set_content_type'));
        //add_filter('wp_mail_content_type',create_function('', 'return "text/html"; '));
        
        $current_time = time();
        $current_time = $this->the_plugin->last_update_date(true);
        $email_to = self::$log_email_to;
        $subject = sprintf(__('Products Sync - full cycle (%s)', $this->the_plugin->localizationName), $current_time);
        
        $html = $this->log_build_msg( $msg, array('sep' => '<br />', 'current_time' => $current_time) );
        //$html = '<p>The <em>HTML</em> message</p>';
        
        $sendStat = wp_mail( $email_to, $subject, $html );

        // reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
        remove_filter('wp_mail_content_type', array($this->the_plugin, 'set_content_type'));

        return array(
            'mailStat'          => $sendStat,
            'mailFields'        => compact( 'email_to', 'subject' ), //compact( 'email_to', 'subject', 'html' ),
        );
    }
    private function log_save_file( $msg=array() ) {
        $logFolder = $this->the_plugin->cfg['paths']['plugin_dir_path'] . 'log/';

        $html = $this->log_build_msg( $msg, array('sep' => PHP_EOL) );
 
        $saveStat = file_put_contents( $logFolder . 'log-sync.txt', $html, FILE_APPEND );
        return array(
            'saveStat'          => $saveStat,
            'saveFields'        => '', //compact( 'html' ),
        );
    }
    private function log_build_msg( $msg=array(), $pms ) {
        extract($pms);

        if ( empty($current_time) ) {
            $current_time = time();
            $current_time = $this->the_plugin->last_update_date(true);
        }
        
        $subject = sprintf(__('Products Sync - full cycle (%s)', $this->the_plugin->localizationName), $current_time);

        $html = array();
        $html[] = '###########################################################';
        $html[] = '## ' . $subject . $sep;
        ob_start();
        
        var_dump('<pre>',$msg,'</pre>'); 
        
        $html[] = ob_get_contents();
        ob_end_clean();
        
        $html[] = $sep.$sep;
        
        $html = implode($sep, $html);
        return $html;
    }


    /**
     * Sync products - old methods!
     */
    /*
    public function updateTheProducts( $return='die' )
    {
        $this->updateSyncRules(); 
  
        $__products = $this->getAllPublishProducts();
        //var_dump('<pre>', $__products, '</pre>'); die('debug...');

        $products = $this->extract_products_by_request_size( $__products );
        //var_dump('<pre>', $products, '</pre>'); die('debug...'); 
  
        if(count($products) > 0) {
            $amz_products = array(); 
            foreach ($products as $key => $value){
                $asin = get_post_meta( $value['ID'], '_amzASIN', true );
                $amz_products[$value['ID']] = $asin;
            }
            $amz_products = array_unique($amz_products);
            foreach (array_chunk($amz_products, 10, true) as $products) {
                $this->updateTheProduct( $products, $return );
            }  
        }else{
            if (1) {
                $ret = array(
                    'status' => 'invalid',
                    'msg' => 'No products selected - maybe all products are already updated!'
                );
                if( $return == 'print_return' ){
                    $ret['msg'] = str_replace('[sep]', '<br />', $ret['msg']);
                    echo $ret['msg'];
                } else if( $return == 'return' ){
                    $ret['msg'] = str_replace('[sep]', PHP_EOL, $ret['msg']);
                    return $ret;
                } else{
                    die(json_encode($ret));
                }
            }
        }
    }
    
    private function extract_products_by_request_size( $products=array() )
    {
        if(empty($products)){
            return array();
        }

        // range size
        $nrOfProducts = (int) $this->cfg['available_setup']['sync_products_per_request'];
        //if( $nrOfProducts == 0 ) $nrOfProducts = 10;

        // Sync all products on same request
        if( $nrOfProducts == 0 ){
            return $products;
        }

        if(1){
            $last_updated_product = get_option('WooZone_sync_last_updated_product', true);

            $selectedProducts = array();
            $added = 0;
            $start_from_here = false;
            foreach ($products as $key => $value) {
                
                if ( $added >= $nrOfProducts ) break 1;

                if( $start_from_here == true && $added < $nrOfProducts ){
                    $selectedProducts[] = $value;
                    $added++;
                }
                
                if( $value['ID'] == $last_updated_product ){
                    $start_from_here = true;
                }
            }
            
            // if the searched products was to the finish and don't take enough products add first X products
            if( count($selectedProducts) < $nrOfProducts ){
                $selectedProducts = array_merge( $selectedProducts, array_slice($products, 0, ($nrOfProducts - count($selectedProducts)) ) );  
            }
            
            return $selectedProducts;
        }
    }
    
    final protected function getAllPublishProducts()
    {
    
        $ret = array();
        $args = array();
        $args['post_type'] = 'product';

        $args['meta_key'] = '_amzASIN';
        $args['meta_value'] = '';
        $args['meta_compare'] = '!=';

        // show all posts
        $args['fields'] = 'ids';
        $args['posts_per_page'] = '-1';
        
        // order by
        $args['order'] = 'ASC'; // because default is 'DESC' and we need old products to be synced first!
        $args['orderby'] = 'ID'; // post_date default order by field don't have an index!
        
        $loop = new WP_Query( $args );
        $cc = 0;
        $html = array(); 
        while ( $loop->have_posts() ) : $loop->the_post();
            global $post;
        
            // add product only if not been updated in recurrence time
            $last_sync_date = get_post_meta($post, '_amzaff_sync_last_date', true);
            
            //if ( 1 ) {
            if ( ( time() >= ($last_sync_date + (3600 * $this->cfg['available_setup']["sync_recurrence"])) )
                || empty($last_sync_date) ) {
                $ret[] = array( 'ID' => $post );
            }
            
        endwhile;
        return $ret;
    }

    public function updateTheTask()
    {
        $recc = $this->cfg['available_setup']['sync_recurrence'];
        $start_hour = $this->cfg['available_setup']['sync_hour_start'];
  
        // now remove the current task from queue
        $this->removeCurrentTask();

        // create timestamp from hour
        $str_date = date('y-m-d');
        $timestamp = strtotime( $str_date );
        $timestamp = $timestamp + ($start_hour * 60);
   
        // add new task
        wp_schedule_event( $timestamp, $recc, 'WooZone_SyncProducts_event');

        die('OK');
    }

    public function removeCurrentTask()
    {
        wp_clear_scheduled_hook( 'WooZone_SyncProducts_event' );
    } 
    */

    
    /**
     * Ajax requests
     */
    public function ajax_request() {
        global $wpdb;

        $request = array(
            'id'            => isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0,
            'asin'          => isset($_REQUEST['asin']) ? (int)$_REQUEST['asin'] : '',
        );
        extract($request);

        $ret = array_merge($ret, array(
            'status'    => 'valid',
            'msg'       => '',
        ));
        die(json_encode($ret));
    }
}
}

// reload Cronjob
//add_action('wp_ajax_WooZoneSyncUpdate', 'WooZoneSyncUpdate');
/*function WooZoneSyncUpdate() {
	global $WooZone;

	$sync = new wwcAmazonSyncronize($WooZone);
	$sync->updateTheTask();
}*/

// update the Products - choosen by Cronjob
//add_action('WooZone_SyncProducts_event', 'WooZone_SyncProducts_event');
/*function WooZone_SyncProducts_event() {
	global $WooZone;

	$sync = new wwcAmazonSyncronize($WooZone);
	$sync->updateTheProducts( 'print_return' );
}*/

//require_once( 'tail.php' );