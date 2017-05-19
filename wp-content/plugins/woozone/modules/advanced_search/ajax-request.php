<?php

add_action('wp_ajax_WooZoneCategParameters', 'WooZoneCategParameters');
function WooZoneCategParameters() {

	global $WooZone;
	
	// retrive the item search parameters
	$ItemSearchParameters = $WooZone->amzHelper->getAmazonItemSearchParameters();
	
	// retrive the item search parameters
	$ItemSortValues = $WooZone->amzHelper->getAmazonSortValues();
	
	$html = array();
	$request = array(
		'categ' => isset($_REQUEST['categ']) ? $_REQUEST['categ'] : '',
		'nodeid' => isset($_REQUEST['nodeid']) ? $_REQUEST['nodeid'] : ''
	);

	$sort = array();

	$sort['relevancerank'] = 'Items ranked according to the following criteria: how often the keyword appears in the description, where the keyword appears (for example, the ranking is higher when keywords are found in titles), how closely they occur in descriptions (if there are multiple keywords), and how often customers purchased the products they found using the keyword.';
	$sort['salesrank'] = "Bestselling";
	$sort['pricerank'] = "Price: low to high";
	$sort['inverseprice'] = "Price: high to low";
	$sort['launch-date'] = "Newest arrivals";
	$sort['-launch-date'] = "Newest arrivals";
	$sort['sale-flag'] = "On sale";
	$sort['pmrank'] = "Featured items";
	$sort['price'] = "Price: low to high";
	$sort['-price'] = "Price: high to low";
	$sort['reviewrank'] = "Average customer review: high to low";
	$sort['titlerank'] = "Alphabetical: A to Z";
	$sort['-titlerank'] = "Alphabetical: Z to A";
	$sort['pricerank'] = "Price: low to high";
	$sort['inverse-pricerank'] = "Price: high to low";
	$sort['daterank'] = "Publication date: newer to older";
	$sort['psrank'] = "Bestseller ranking taking into consideration projected sales.The lower the value, the better the sales.";
	$sort['orig-rel-date'] = "Release date: newer to older";
	$sort['-orig-rel-date'] = "Release date: older to newer";
	$sort['releasedate'] = "Release date: newer to older";
	$sort['-releasedate'] = "Release date: older to newer";
	$sort['songtitlerank'] = "Most popular";
	$sort['uploaddaterank'] = "Date added";
	$sort['-video-release-date'] = "Release date: newer to older";
	$sort['-edition-sales-velocity'] = "Quickest to slowest selling products.";
	$sort['subslot-salesrank'] = "Bestselling";
	$sort['release-date'] = "Sorts by the latest release date from newer to older. See orig-rel-date, which sorts by the original release date.";
	$sort['-age-min'] = "Age: high to low";

	// print the title
	$html[] = '<h2>' . ( $request['categ'] ) . ' Search</h2>';

	// store categ into input, use in search FORM
	$html[] = '<input type="hidden" name="WooZoneParameter[categ]" value="' . ( $request['categ'] ) . '" />';

	// Keywords
	$html[] = '<div class="WooZoneParameterSection">';
	$html[] = 	'<label>' . __('Keywords', $WooZone->localizationName) .'</label>';
	$html[] = 	'<input type="text" size="22" name="WooZoneParameter[Keywords]">';
	$html[] = '</div>';

	// Keywords
	$args = array(
		'orderby' 	=> 'menu_order',
		'order' 	=> 'ASC',
		'hide_empty' => 0,
		'post_per_page' => '-1'
	);
	$categories = get_terms('product_cat', $args);
	  
	$args = array(
		'show_option_all'    => '',
		'show_option_none'   => 'Use category from Amazon',
		'orderby'            => 'ID', 
		'order'              => 'ASC',
		'show_count'         => 0,
		'hide_empty'         => 0, 
		'child_of'           => 0,
		'exclude'            => '',
		'echo'               => 0,
		'selected'           => 0,
		'hierarchical'       => 1, 
		'name'               => 'WooZone-to-category',
		'id'                 => 'WooZone-to-category',
		'class'              => 'postform',
		'depth'              => 0,
		'tab_index'          => 0,
		'taxonomy'           => 'product_cat',
		'hide_if_empty'      => false,
	);
	
	$html[] = '<div class="WooZoneParameterSection">';
	$html[] = 	'<label>' . __('Import in:', $WooZone->localizationName) .'</label>';
	$html[] = wp_dropdown_categories( $args );
	$html[] = '</div>';


	// BrowseNode
	if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'BrowseNode', $ItemSearchParameters[$request['categ']] ) ){
		
		$nodes = $WooZone->getBrowseNodes( $request['nodeid'] );
		
		//var_dump('<pre>',$nodes,'</pre>'); die;
		//if ( !empty($nodes) ) {

		$html[] = '<div class="WooZoneParameterSection">';
		$html[] = 	'<label>' . __('BrowseNode', $WooZone->localizationName) .'</label>';

		$html[] = 	'<div id="WooZoneGetChildrens">';
		$html[] = 	'<select name="WooZoneParameter[node]">';
		$html[] = '<option value="">' . __('All', $WooZone->localizationName) .'</option>';
		if ( !empty($nodes) && is_array($nodes) ) {
			foreach ($nodes as $key => $value){
				$html[] = '<option value="' . ( $value['BrowseNodeId'] ) . '">' . ( $value['Name'] ) . '</option>';
			}
		}
		$html[] = 	'</select>';
		$html[] = '</div>';
		//$html[] = 	'<input type="button" class="WooZone-button blue WooZoneGetChildNodes" value="' . __('Get Child Nodes', $WooZone->localizationName) .'" style="width: 100px; float: left;position: relative; bottom: -3px;" />';

		$html[] = 	'<div id="WooZoneGetChildrens"></div>';
		$html[] = 	'<p>Browse nodes are identify items categories</p>';
		$html[] = '</div>';
		
		//}
	}

	// Brand
	if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'Brand', $ItemSearchParameters[$request['categ']] ) ){
		$html[] = '<div class="WooZoneParameterSection">';
		$html[] = 	'<label>' . __('Brand', $WooZone->localizationName) .'</label>';
		$html[] = 	'<input type="text" size="22" name="WooZoneParameter[Brand]">';
		$html[] = 	'<p>Name of a brand associated with the item. You can enter all or part of the name. For example, Timex, Seiko, Rolex. </p>';
		$html[] = '</div>';
	}

	// Condition
	if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'Condition', $ItemSearchParameters[$request['categ']] ) ){
		$html[] = '<div class="WooZoneParameterSection">';
		$html[] = 	'<label>' . __('Condition', $WooZone->localizationName) .'</label>';
		$html[] = 	'<select name="WooZoneParameter[Condition]">';
		$html[] = 		'<option value="">All Conditions</option>';
		$html[] = 		'<option value="New">New</option>';
		$html[] = 		'<option value="Used">Used</option>';
		$html[] = 		'<option value="Collectible">Collectible</option>';
		$html[] = 		'<option value="Refurbished">Refurbished</option>';
		$html[] = 	'</select>';
		$html[] = 	'<p>Use the Condition parameter to filter the offers returned in the product list by condition type. By default, Condition equals "New". If you do not get results, consider changing the value to "All. When the Availability parameter is set to "Available," the Condition parameter cannot be set to "New."</p>';
		$html[] = '</div>';
	}

	// Manufacturer
	if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'Manufacturer', $ItemSearchParameters[$request['categ']] ) ){
		$html[] = '<div class="WooZoneParameterSection">';
		$html[] = 	'<label>' . __('Manufacturer', $WooZone->localizationName) .'</label>';
		$html[] = 	'<input type="text" size="22" name="WooZoneParameter[Manufacturer]">';
		$html[] = 	'<p>Name of a manufacturer associated with the item. You can enter all or part of the name.</p>';
		$html[] = '</div>';
	}

	// MaximumPrice
	if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'MaximumPrice', $ItemSearchParameters[$request['categ']] ) ){
		$html[] = '<div class="WooZoneParameterSection">';
		$html[] = 	'<label>' . __('Maximum Price', $WooZone->localizationName) .'</label>';
		$html[] = 	'<input type="text" size="22" name="WooZoneParameter[MaximumPrice]">';
		$html[] = 	'<p>Specifies the maximum price of the items in the response. Prices are in terms of the lowest currency denomination, for example, pennies. For example, 3241 represents $32.41.</p>';
		$html[] = '</div>';
	}

	// MinimumPrice
	if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'MinimumPrice', $ItemSearchParameters[$request['categ']] ) ){
		$html[] = '<div class="WooZoneParameterSection">';
		$html[] = 	'<label>' . __('Minimum Price', $WooZone->localizationName) .'</label>';
		$html[] = 	'<input type="text" size="22" name="WooZoneParameter[MinimumPrice]">';
		$html[] = 	'<p>Specifies the minimum price of the items to return. Prices are in terms of the lowest currency denomination, for example, pennies, for example, 3241 represents $32.41.</p>';
		$html[] = '</div>';
	}

	// MerchantId
	if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'MerchantId', $ItemSearchParameters[$request['categ']] ) ){
		$html[] = '<div class="WooZoneParameterSection">';
		$html[] = 	'<label>' . __('Merchant Id', $WooZone->localizationName) .'</label>';
		$html[] = 	'<input type="text" size="22" name="WooZoneParameter[MerchantId]">';
		$html[] = 	'<p>An optional parameter you can use to filter search results and offer listings to only include items sold by Amazon. By default, Product Advertising API returns items sold by various merchants including Amazon. Use the Amazon to limit the response to only items sold by Amazon.</p>';
		$html[] = '</div>';
	}

	// MinPercentageOff
	if( isset($ItemSearchParameters[$request['categ']]) && in_array( 'MinPercentageOff', $ItemSearchParameters[$request['categ']] ) ){
		$html[] = '<div class="WooZoneParameterSection">';
		$html[] = 	'<label>' . __('Min Percentage Off', $WooZone->localizationName) .'</label>';
		$html[] = 	'<input type="text" size="22" name="WooZoneParameter[MinPercentageOff]">';
		$html[] = 	'<p>Specifies the minimum percentage off for the items to return.</p>';
		$html[] = '</div>';
	}

	// Sort
	if( $request['categ'] != "All" ){
		$html[] = '<div class="WooZoneParameterSection">';
		$html[] = 	'<label>' . __('Sort', $WooZone->localizationName) .'</label>';
		$html[] = 	'<select name="WooZoneParameter[Sort]" class="WooZoneParameter-sort">';

		$curr_sort = array();
		if(isset($ItemSortValues[$request['categ']])){
			$curr_sort = $ItemSortValues[$request['categ']];
		}

		$first_sort_key = '';
		$first_sort_desc = '';
		$cc = 0; 
		foreach ( $sort as $key => $value ){
			if( isset($curr_sort) && in_array( $key, $curr_sort) ){
				if( $cc == 0 ){
					$first_sort_key = $key;
					$first_sort_desc = $value;
				}

				$html[] = '<option value="'. ( $key ) .'" data-desc="'. ( str_replace('"', "'", $value) ) .'">'. ( $key ) .'</option>';

				$cc++;
			}
		}

		$html[] = 	'</select>';
		$html[] = 	'<p id="WooZoneOrderDesc" style="width: 100%;">' . ( "<strong>" . ( $first_sort_key ) . ":</strong> " . $first_sort_desc ) . '</p>';
		$html[] = 	'<p>Means by which the items in the response are ordered.</p>';
		$html[] = '</div>';
	}

	// button
	$html[] = '<input type="submit" value="' . __('Search for items', 'Search for products') . '" class="WooZone-form-button WooZone-form-button-info" >';

	die(json_encode(array(
		'status' 	=> 'valid',
		'html'		=> implode("\n", $html)
	)));
}

add_action('wp_ajax_WooZoneLaunchSearch', 'WooZoneLaunchSearch_callback');
function WooZoneLaunchSearch_callback() {
	global $WooZone;

	$plugin_uri = $WooZone->cfg['paths']['plugin_dir_url'] . 'modules/bulk_products_import/';
    $amz_setup = $WooZone->getAllSettings('array', 'amazon');

	$requestData = array(
		'params' => isset($_REQUEST['params']) ? $_REQUEST['params'] : '',
		'page' => isset($_REQUEST['page']) ? (int)($_REQUEST['page']) : '',
		'node' => isset($_REQUEST['node']) ? $_REQUEST['node'] : '',
	);

    $currentQueue = array();
	$your_products = (array) $WooZone->getAllProductsMeta('array', '_amzASIN');
    if( empty($your_products) ){
        $your_products = array();
    } else {
        $your_products = array_unique($your_products);
    }

	$parameters = array();
	parse_str( ( $requestData['params'] ), $parameters);

	if( isset($parameters['WooZoneParameter'])) {
		$parameters = $parameters['WooZoneParameter'];
	}

	// option parameters
	$optionalParameters = $parameters;
	// remove from optional parameters any other unecesarry keys
	$notValidOptional = array('categ', 'Keywords', 'node');
	if( count($optionalParameters) > 0 ){
		foreach ($optionalParameters as $key => $value){
			if( in_array( $key, $notValidOptional) ) unset($optionalParameters[$key]);
		}
	}

	// clear the empty array
	$optionalParameters = array_filter($optionalParameters);

    $_optionalParameters = array();
	if( count($optionalParameters) > 0 ){
		foreach ($optionalParameters as $key => $value){
			$_optionalParameters[$key] = $value;
		}
    }
    if ( 1 ) {
		// if node is send, chain to request
		if( isset($requestData['node']) && trim($requestData['node']) != "" ){
			$_optionalParameters['BrowseNode'] = $requestData['node'];
		}

		// set the page
		if ( isset($requestData['page']) && trim($requestData['page']) != "" ){
		  $_optionalParameters['ItemPage'] = $requestData['page'];
        }
	}
	
	$provider = 'amazon';
	$rsp = $WooZone->get_ws_object( $provider )->api_make_request(array(
		'amz_settings'			=> $WooZone->amz_settings,
		'from_file'				=> str_replace($WooZone->cfg['paths']['plugin_dir_path'], '', __FILE__),
		'from_func'				=> __FUNCTION__ != __METHOD__ ? __METHOD__ : __FUNCTION__,
		'requestData'			=> array(
			'category'					=> $parameters['categ'],
			'page'						=> $requestData['page'],
			'keyword'					=> $parameters['Keywords'],
		),
		'optionalParameters'	=> $_optionalParameters,
		'responseGroup'			=> 'Large' . ( $parameters['categ'] == 'Apparel' ? ',Variations' : ''),
		'method'				=> 'search',
	));
	$response = $rsp['response'];
    //var_dump('<pre>', $response, '</pre>');  echo __FILE__ . ":" . __LINE__;die . PHP_EOL;   

	$requestData['debug_level'] = isset($_REQUEST['debug_level']) ? (int)$_REQUEST['debug_level'] : 0;
	// print some debug if requested
	if( $requestData['debug_level'] > 0 ) {
		if( $requestData['debug_level'] == 1) var_dump('<pre>', $response['Items']['Request'],'</pre>');
		if( $requestData['debug_level'] == 2) var_dump('<pre>', $requestData, $response ,'</pre>');
	}

	$respStatus = $WooZone->amzHelper->is_amazon_valid_response( $response );
	if ( $respStatus['status'] != 'valid' ) { // error occured!
		die('<div class="error" style="float: left;margin: 10px;padding: 6px;">' . 'Amazon Error: ' . $respStatus['code'] . ' - ' . $respStatus['msg'] . '</div>');
	}

	//if($response['Items']['Request']['IsValid'] == 'False') {
	//	die('<div class="error" style="float: left;margin: 10px;padding: 6px;">Amazon error id: <bold>' . ( $response['Items']['Request']['Errors']['Error']['Code'] ) . '</bold>: <br /> ' . ( $response['Items']['Request']['Errors']['Error']['Message'] ) . '</div>');
	//}
	//elseif(count($response['Items']) > 0){
	if (1) {
	    
        $do_parent_setting = !isset($amz_setup['variation_force_parent'])
            || ( isset($amz_setup['variation_force_parent']) && $amz_setup['variation_force_parent'] != 'no' )
            ? true : false;

		if (isset($response['Items']['TotalResults']) && $response['Items']['TotalResults'] >= 1) {
			//echo'<pre>'; print_r($parameters['categ']); echo'</pre>';  
			$totalPages = ( $parameters['categ'] == 'All' ? 5 : 10 );
	?>
			<div class="WooZone-execution-queue">
				<div class="WooZone-queue-table">
					<div id="WooZone-execution-queue-title">
						<?php _e('Execution Queue:', $WooZone->localizationName);?>
					</div>
					<div id="WooZone-execution-queue-list"><?php _e('No item(s) yet', $WooZone->localizationName);?></div>
					<div>
						<a class="WooZone-form-button-small WooZone-form-button-success" id="WooZone-advance-import-btn" target="_blank" href="#">Import product(s)</a>
					</div>
				</div>
				<div class="WooZone-execution-queue-msg"></div>
			</div>

			<div class="resultsTopBar">
				<h2>
					Showing <?php echo $requestData['page'];?> - <?php echo $response['Items']["TotalPages"];?> of <span id="WooZone-totalPages"><?php echo $response['Items']["TotalResults"];?></span> Results <em>(The limit from Amazon is <code><?php echo $totalPages;?></code> pages for your search)</em>
				</h2>

				<div class="WooZone-pagination">
					<span>View page:</span>
					<select id="WooZone-page">
						<?php
						for( $p = 1; $p <= $totalPages; $p++ ){
							echo '<option value="' . ( $p ) . '" ' . ( $p == $requestData['page'] ? 'selected' : '' ) . '> ' . ( $p ) . ' </option>';
						}
						?>
					</select>
				</div>
			</div>

		<?php
		}	// don't show paging if total results it's not bigget than 1
			if (isset($response['Items']['Item']) && count($response['Items']['Item']) > 0){
		?>

		<table class="WooZone-items-list" border="0" cellspacing="0" cellpadding="0">
			<thead>
				<tr>
					<th width="30"><input type="checkbox" id="WooZone-items-select-all" /></th>
					<th align="left"><?php _e('Product name', $WooZone->localizationName);?></th>
                    <th width="80"><?php _e('ASIN', $WooZone->localizationName);?></th>
					<th width="20"><?php _e('Image', $WooZone->localizationName);?></th>
					<th width="60"><?php _e('Price', $WooZone->localizationName);?></th>
					<th width="100"><?php _e('View', $WooZone->localizationName);?></th>
				</tr>
			</thead>
			<tbody>

			<?php
				$cc = 0;
				foreach ($response['Items']['Item'] as $key => $value){

					if($response['Items']['TotalResults'] == 1) {
						$value = $response['Items']['Item'];
					}
					if(($cc++ + 1) > $response['Items']['TotalResults']) continue;
   
                    $__asin_css = '';
                    
                    $asin = $value['ASIN'];
                    // product is a variation child => try to find parent variation
                    $do_parent = $do_parent_setting;
                    if ( $do_parent ) {
                        if ( !isset($value['ParentASIN']) || empty($value['ParentASIN']) ) {
                            $do_parent = false;
                        }
                    }
                    if ( $do_parent ) {
                        $__asin_css = 'variation_parent';
                        $value['ASIN'] = $value['ParentASIN'];
                        //$value['ItemAttributes']['Title'] = '[variation parent ASIN: '.$value['ParentASIN'].'] '
                        //    . $value['ItemAttributes']['Title'];
                    }

					$thumb = isset($value['SmallImage']['URL']) ? $value['SmallImage']['URL'] : '';
					if(trim($thumb) == ""){
						// try to find image as first image from image sets
						if ( isset($value['ImageSets'], $value['ImageSets']['ImageSet'], $value['ImageSets']['ImageSet'][0], $value['ImageSets']['ImageSet'][0]['SmallImage'], $value['ImageSets']['ImageSet'][0]['SmallImage']['URL']) )
							$thumb = $value['ImageSets']['ImageSet'][0]['SmallImage']['URL'];
					}
					
					$full_img = isset($value['LargeImage']['URL']) ? $value['LargeImage']['URL'] : '';
					if(trim($full_img) == ""){
						// try to find image as first image from image sets
						if ( isset($value['ImageSets'], $value['ImageSets']['ImageSet'], $value['ImageSets']['ImageSet'][0], $value['ImageSets']['ImageSet'][0]['LargeImage'], $value['ImageSets']['ImageSet'][0]['LargeImage']['URL']) )
							$full_img = $value['ImageSets']['ImageSet'][0]['LargeImage']['URL'];
					}
					
					$orig_thumb = $thumb;
					//$thumb = $WooZone->image_resize( $thumb, 50, 50, 2);

					$blocked = '';
					if( !empty($your_products) ){
						if( in_array($value['ASIN'], $your_products) ){
							$blocked = 'blocked"';
						}
					}
                    $your_products[] = $value['ASIN'];
                    $your_products = array_unique($your_products);

                    $inqueue = '';
                    if( !empty($currentQueue) ){
                        if( in_array($value['ASIN'], $currentQueue) ){
                            $inqueue = 'blocked"';
                        }
                    }
                    $currentQueue[] = $value['ASIN'];
                    $currentQueue = array_unique($currentQueue);
                    
                    $__tr_css = trim(implode(' ', array($__asin_css, $blocked)));
		?>

					<tr id="WooZone-item-row-<?php echo $value['ASIN'];?>" class="<?php echo $__tr_css;?>" data-asin="<?php echo $asin; ?>">
						<td align="center">
							<?php
							if( trim($blocked) == '' ) {
							?>
								<input type="checkbox" class="WooZone-items-select" value="<?php echo $value['ASIN'];?>" />
							<?php
							}else{
							    if ( trim($inqueue) == '' ) {
                                    echo '<i style="font-size: 12px;">' . __('Already Imported', $WooZone->localizationName) . '</i>';
							    } else {
                                    echo '<i style="font-size: 12px;">' . __('(Duplicate) Already exists in ASIN column', $WooZone->localizationName) . '</i>';
                                }
							}
							?>
							</td>
						<td><?php echo $value['ItemAttributes']['Title'];?></td>
						<td align="center" class="asin"><?php echo $value['ASIN'];?></td>
						<td align="center"><a class="WooZone-tooltip" href="#" data-img="<?php echo $full_img;?>"><img id="WooZone-item-img-<?php echo $value['ASIN'];?>" src="<?php echo $thumb;?>" height="30"></a></td>
						<td align="center">
							<div class="WooZone-item-price-block">
								<?php
									if($parameters['categ'] == 'Apparel'){
										echo isset($value['VariationSummary']['LowestPrice']['FormattedPrice']) ? $value['VariationSummary']['LowestPrice']['FormattedPrice'] : '';
									}else{
										echo isset($value['Offers']['Offer']['OfferListing']['Price']['FormattedPrice']) ? $value['Offers']['Offer']['OfferListing']['Price']['FormattedPrice'] : '';
									}
								?>
							</div>
						</td>
						<td align="center"><a href="<?php echo $value['DetailPageURL'];?>" target="_blank" class="WooZone-form-button-small WooZone-form-button-info"><?php _e('View', $WooZone->localizationName);?></a></td>
					</tr>
		<?php
				} // end foreach
				echo '</tbody></table>'; // close the table
		} // end if have products

		else{

			if( isset($response['Items']['Request']['Errors']['Error']['Message']) ){
				echo '<div class="WooZone-message error">';
				echo 	$response['Items']['Request']['Errors']['Error']['Message'];
				echo '</div>';
			}
		}
        
        if ( $do_parent_setting ) {
        ?>
            <div class="infoDetailsBar WooZone-callout WooZone-callout-info">
                    <?php _e('ASIN column:<br/>
                    - if italic font & purple color = the variation parent product ASIN; the row (containing the column also) represents a variation child product, so we\'ll import the variation parent product as you\'ve setted in [ Amazon config module / Import Setup tab / Force import parent if is variation option ].<br />
                    - if default font & color = product ASIN; we have a simple product (no variations) or a variation parent product already', $WooZone->localizationName); ?>
            </div>
        <?php
        }
	}
	die(); // this is required to return a proper result
}

add_action('wp_ajax_WooZoneGetChildNodes', 'WooZoneGetChildNodes');
function WooZoneGetChildNodes() {
	global $WooZone;

	$request = array(
		'nodeid' => isset($_REQUEST['ascensor']) ? $_REQUEST['ascensor'] : ''
	);

	$nodes = $WooZone->getBrowseNodes( $request['nodeid'] );
	//var_dump('<pre>',$nodes,'</pre>'); die;  
	// Apparel & Accessories

 	$html = array();
	
	if ( empty($nodes) ) {
		die(json_encode(array(
			'status' 	=> 'valid',
			'html'		=> implode("\n", $html)
		)));
	}

	$has_nodes = false;
	//$html[] = '<div class="WooZoneParameterSection">';
	$html[] = 	'<select name="WooZoneParameter[node]" style="margin: 10px 0px 0px 0px;">';
	$html[] = '<option value="">' . __('All', $WooZone->localizationName) .'</option>';
	foreach ($nodes as $key => $value){
		if( isset($value['BrowseNodeId']) && trim($value['BrowseNodeId']) != "" )
			$has_nodes = true;
			
		$html[] = '<option value="' . ( $value['BrowseNodeId'] ) . '">' . ( $value['Name'] ) . '</option>';
	}
	$html[] = 	'</select>';
	//$html[] = 	'<input type="button" class="WooZone-button blue WooZoneGetChildNodes" value="' . __('Get Child Nodes', $WooZone->localizationName) .'" style="width: 100px; float: left;position: relative; bottom: -3px;" />';
	//$html[] = '</div>';
	
	if( $has_nodes == false ){
		$html = array();
	}

	die(json_encode(array(
		'status' 	=> 'valid',
		'html'		=> implode("\n", $html)
	)));
}