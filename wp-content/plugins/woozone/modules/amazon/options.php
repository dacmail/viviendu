<?php
/**
 * Dummy module return as json_encode
 * http://www.aa-team.com
 * =======================
 *
 * @author		Andrei Dinca, AA-Team
 * @version		0.1 - in development mode
 */

/*
http://docs.aws.amazon.com/AWSECommerceService/latest/DG/CHAP_response_elements.html 
$('div.informaltable > table tr').each(function(i, el) {
	var $this = $(el), $td = $this.find('td:first'),
	$a = $td.find('a'), text = $a.attr('name');

	if ( typeof text == 'undefined' || text == '' ){
		text = $td.find('.code').text();
	}
	if ( typeof text != 'undefined' && text != '' ) {
		var text2 = text; //text.match(/([A-Z]?[^A-Z]*)/g).slice(0,-1).join(' ');
		console.log( '\''+text+'\' => \''+text+'\',' );
	}
});
*/  
function __WooZone_attributesList() {
	$attrList = array(
'About' => 'About',
'AboutMe' => 'AboutMe',
'Actor' => 'Actor',
'AdditionalName' => 'AdditionalName',
'AlternateVersion' => 'AlternateVersion',
'Amount' => 'Amount',
'Artist' => 'Artist',
'ASIN' => 'ASIN',
'AspectRatio' => 'AspectRatio',
'AudienceRating' => 'AudienceRating',
'AudioFormat' => 'AudioFormat',
'Author' => 'Author',
'Availability' => 'Availability',
'AvailabilityAttributes' => 'AvailabilityAttributes',
'Benefit' => 'Benefit',
'Benefits' => 'Benefits',
'BenefitType' => 'BenefitType',
'BenefitDescription' => 'BenefitDescription',
'Bin' => 'Bin',
'Binding' => 'Binding',
'BinItemCount' => 'BinItemCount',
'BinName' => 'BinName',
'BinParameter' => 'BinParameter',
'Brand' => 'Brand',
'BrowseNodeId' => 'BrowseNodeId',
'CartId' => 'CartId',
'CartItem' => 'CartItem',
'CartItemId' => 'CartItemId',
'CartItems' => 'CartItems',
'Category' => 'Category',
'CEROAgeRating' => 'CEROAgeRating',
'ClothingSize' => 'ClothingSize',
'Code' => 'Code',
'Collection' => 'Collection',
'CollectionItem' => 'CollectionItem',
'CollectionParent' => 'CollectionParent',
'Color' => 'Color',
'Comment' => 'Comment',
'ComponentType' => 'ComponentType',
'Condition' => 'Condition',
'CorrectedQuery' => 'CorrectedQuery',
'CouponCombinationType' => 'CouponCombinationType',
'Creator' => 'Creator',
'CurrencyAmount' => 'CurrencyAmount',
'CurrencyCode' => 'CurrencyCode',
'Date' => 'Date',
'DateAdded' => 'DateAdded',
'DateCreated' => 'DateCreated',
'Department' => 'Department',
'Details' => 'Details',
'Director' => 'Director',
'EAN' => 'EAN',
'EANList' => 'EANList',
'EANListElement' => 'EANListElement',
'Edition' => 'Edition',
'EditorialReviewIsLinkSuppressed' => 'EditorialReviewIsLinkSuppressed',
'EISBN' => 'EISBN',
'EligibilityRequirement' => 'EligibilityRequirement',
'EligibilityRequirementDescription' => 'EligibilityRequirementDescription',
'EligibilityRequirements' => 'EligibilityRequirements',
'EligibilityRequirementType' => 'EligibilityRequirementType',
'EndDate' => 'EndDate',
'EpisodeSequence' => 'EpisodeSequence',
'ESRBAgeRating' => 'ESRBAgeRating',
'Feature' => 'Feature',
'Feedback' => 'Feedback',
'Fitment' => 'Fitment',
'FitmentAttribute' => 'FitmentAttribute',
'FitmentAttributes' => 'FitmentAttributes',
'FixedAmount' => 'FixedAmount',
'Format' => 'Format',
'FormattedPrice' => 'FormattedPrice',
'Genre' => 'Genre',
'GroupClaimCode' => 'GroupClaimCode',
'HardwarePlatform' => 'HardwarePlatform',
'HazardousMaterialType' => 'HazardousMaterialType',
'Height' => 'Height',
'HelpfulVotes' => 'HelpfulVotes',
'HMAC' => 'HMAC',
'IFrameURL' => 'IFrameURL',
'Image' => 'Image',
'IsAdultProduct' => 'IsAdultProduct',
'IsAutographed' => 'IsAutographed',
'ISBN' => 'ISBN',
'IsCategoryRoot' => 'IsCategoryRoot',
'IsEligibleForSuperSaverShipping' => 'IsEligibleForSuperSaverShipping',
'IsEligibleForTradeIn' => 'IsEligibleForTradeIn',
'IsEmailNotifyAvailable' => 'IsEmailNotifyAvailable',
'IsFit' => 'IsFit',
'IsInBenefitSet' => 'IsInBenefitSet',
'IsInEligibilityRequirementSet' => 'IsInEligibilityRequirementSet',
'IsLinkSuppressed' => 'IsLinkSuppressed',
'IsMemorabilia' => 'IsMemorabilia',
'IsNext' => 'IsNext',
'IsPrevious' => 'IsPrevious',
'ItemApplicability' => 'ItemApplicability',
'ItemDimensions' => 'ItemDimensions',
'IssuesPerYear' => 'IssuesPerYear',
'IsValid' => 'IsValid',
'ItemAttributes' => 'ItemAttributes',
'ItemPartNumber' => 'ItemPartNumber',
'Keywords' => 'Keywords',
'Label' => 'Label',
'Language' => 'Language',
'Languages' => 'Languages',
'LargeImage' => 'LargeImage',
'LastModified' => 'LastModified',
'LegalDisclaimer' => 'LegalDisclaimer',
'Length' => 'Length',
'ListItemId' => 'ListItemId',
'ListPrice' => 'ListPrice',
'LoyaltyPoints' => 'LoyaltyPoints',
'Manufacturer' => 'Manufacturer',
'ManufacturerMaximumAge' => 'ManufacturerMaximumAge',
'ManufacturerMinimumAge' => 'ManufacturerMinimumAge',
'ManufacturerPartsWarrantyDescription' => 'ManufacturerPartsWarrantyDescription',
'MaterialType' => 'MaterialType',
'MaximumHours' => 'MaximumHours',
'MediaType' => 'MediaType',
'MediumImage' => 'MediumImage',
'MerchandisingMessage' => 'MerchandisingMessage',
'MerchantId' => 'MerchantId',
'Message' => 'Message',
'MetalType' => 'MetalType',
'MinimumHours' => 'MinimumHours',
'Model' => 'Model',
'MoreOffersUrl' => 'MoreOffersUrl',
'MPN' => 'MPN',
'Name' => 'Name',
'Nickname' => 'Nickname',
'Number' => 'Number',
'NumberOfDiscs' => 'NumberOfDiscs',
'NumberOfIssues' => 'NumberOfIssues',
'NumberOfItems' => 'NumberOfItems',
'NumberOfPages' => 'NumberOfPages',
'NumberOfTracks' => 'NumberOfTracks',
'OccasionDate' => 'OccasionDate',
'OfferListingId' => 'OfferListingId',
'OperatingSystem' => 'OperatingSystem',
'OtherCategoriesSimilarProducts' => 'OtherCategoriesSimilarProducts',
'PackageQuantity' => 'PackageQuantity',
'ParentASIN' => 'ParentASIN',
'PartBrandBins' => 'PartBrandBins',
'PartBrowseNodeBins' => 'PartBrowseNodeBins',
'PartNumber' => 'PartNumber',
'PartnerName' => 'PartnerName',
'Platform' => 'Platform',
'Price' => 'Price',
'ProductGroup' => 'ProductGroup',
'ProductTypeSubcategory' => 'ProductTypeSubcategory',
'Promotion' => 'Promotion',
'PromotionId' => 'PromotionId',
'Promotions' => 'Promotions',
'PublicationDate' => 'PublicationDate',
'Publisher' => 'Publisher',
'PurchaseURL' => 'PurchaseURL',
'Quantity' => 'Quantity',
'Rating' => 'Rating',
'RegionCode' => 'RegionCode',
'RegistryName' => 'RegistryName',
'RelatedItem' => 'RelatedItem',
'RelatedItems' => 'RelatedItems',
'RelatedItemsCount' => 'RelatedItemsCount',
'RelatedItemPage' => 'RelatedItemPage',
'RelatedItemPageCount' => 'RelatedItemPageCount',
'Relationship' => 'Relationship',
'RelationshipType ' => 'RelationshipType ',
'ReleaseDate' => 'ReleaseDate',
'RequestId' => 'RequestId',
'Role' => 'Role',
'RunningTime' => 'RunningTime',
'SalesRank' => 'SalesRank',
'SavedForLaterItem' => 'SavedForLaterItem',
'SearchBinSet' => 'SearchBinSet',
'SearchBinSets' => 'SearchBinSets',
'SeikodoProductCode' => 'SeikodoProductCode',
'ShipmentItems' => 'ShipmentItems',
'Shipments' => 'Shipments',
'SimilarProducts' => 'SimilarProducts',
'SimilarViewedProducts' => 'SimilarViewedProducts',
'Size' => 'Size',
'SKU' => 'SKU',
'SmallImage' => 'SmallImage',
'Source' => 'Source',
'StartDate' => 'StartDate',
'StoreId' => 'StoreId',
'StoreName' => 'StoreName',
'Studio' => 'Studio',
'SubscriptionLength' => 'SubscriptionLength',
'Summary' => 'Summary',
'SwatchImage' => 'SwatchImage',
'TermsAndConditions' => 'TermsAndConditions',
'ThumbnailImage' => 'ThumbnailImage',
'TinyImage' => 'TinyImage',
'Title' => 'Title',
'TopItem' => 'TopItem',
'TopItemSet' => 'TopItemSet',
'TotalCollectible' => 'TotalCollectible',
'TotalItems' => 'TotalItems',
'TotalNew' => 'TotalNew',
'TotalOfferPages' => 'TotalOfferPages',
'TotalOffers' => 'TotalOffers',
'TotalPages' => 'TotalPages',
'TotalRatings' => 'TotalRatings',
'TotalRefurbished' => 'TotalRefurbished',
'TotalResults' => 'TotalResults',
'TotalReviewPages' => 'TotalReviewPages',
'TotalReviews' => 'TotalReviews',
'Totals' => 'Totals',
'TotalTimesRead' => 'TotalTimesRead',
'TotalUsed' => 'TotalUsed',
'TotalVotes' => 'TotalVotes',
'Track' => 'Track',
'TradeInValue' => 'TradeInValue',
'TransactionDate' => 'TransactionDate',
'TransactionDateEpoch' => 'TransactionDateEpoch',
'TransactionId' => 'TransactionId',
'TransactionItem' => 'TransactionItem',
'TransactionItemId' => 'TransactionItemId',
'TransactionItems' => 'TransactionItems',
'Type' => 'Type',
'UPC' => 'UPC',
'UPCList' => 'UPCList',
'UPCListElement' => 'UPCListElement',
'URL' => 'URL',
'URLEncodedHMAC' => 'URLEncodedHMAC',
'UserAgent' => 'UserAgent',
'UserId' => 'UserId',
'VariationAttribute' => 'VariationAttribute',
'VariationDimension' => 'VariationDimension',
'Warranty' => 'Warranty',
'WEEETaxValue' => 'WEEETaxValue',
'Weight' => 'Weight',
'Width' => 'Width',
'Year' => 'Year'
	);
	return $attrList;
}

function __WooZone_imageSizes() {
	global $WooZone;
	
	$ret = array();
	$list = $WooZone->u->get_image_sizes();
	foreach ($list as $k => $v) {
		$ret["$k"] = $k . ' ' . sprintf( '(%s X %s)', $v['width'], $v['height'] );
	}
	return $ret;
}

function __WooZoneAffIDsHTML( $istab = '' )
{
	global $WooZone;
	
	$html         = array();
	$img_base_url = $WooZone->cfg['paths']["plugin_dir_url"] . 'modules/amazon/images/flags/';
	
	$config = $WooZone->settings();
	
	$config = $WooZone->build_amz_settings(array(
		'AccessKeyID'			=> 'zzz',
		'SecretAccessKey'		=> 'zzz',
		'country'				=> 'com',
	));
 
	require_once( $WooZone->cfg['paths']['plugin_dir_path'] . 'aa-framework/amz.helper.class.php' );
	if ( class_exists('WooZoneAmazonHelper') ) {
		//$theHelper = WooZoneAmazonHelper::getInstance( $aiowaff );
		$theHelper = new WooZoneAmazonHelper( $WooZone );
	}
	$what = 'main_aff_id';
	$list = is_object($theHelper) ? $theHelper->get_countries( $what ) : array();
	
	ob_start();
?>
	<style type="text/css">
		.WooZone-form .WooZone-form-row .WooZone-form-item.large .WooZone-div2table {
			display: table;
			width: 420px;
		}
			.WooZone-form .WooZone-form-row .WooZone-form-item.large .WooZone-div2table .WooZone-div2table-tr {
				display: table-row;
			}
				.WooZone-form .WooZone-form-row .WooZone-form-item.large .WooZone-div2table .WooZone-div2table-tr > div {
					display: table-cell;
					padding: 5px;
				}
	</style>
	<div class="panel-body <?php echo WooZone()->alias;?>-panel-body <?php echo WooZone()->alias;?>-form-row <?php echo ($istab!='' ? ' '.$istab : ''); ?>">
	<label class="<?php echo  WooZone()->alias;?>-form-label">Your Affiliate IDs</label>
	<div class="<?php echo  WooZone()->alias;?>-form-item large">
	<span class="formNote">Your Affiliate ID probably ends in -20, -21 or -22. You get this ID by signing up for Amazon Associates.</span>
	<div class="<?php echo  WooZone()->alias;?>-aff-ids <?php echo  WooZone()->alias;?>-div2table">
		<?php
		foreach ($list as $globalid => $country_name) {
			$flag = 'com' == $globalid ? 'us' : $globalid;
			$flag = strtoupper($flag);
		?>
		<div class="<?php echo  WooZone()->alias;?>-div2table-tr">
			<div>
				<img src="<?php echo $img_base_url . $flag; ?>-flag.gif" height="20">
			</div>
			<div>
				<input type="text" value="<?php echo isset($config['AffiliateID']["$globalid"]) ? $config['AffiliateID']["$globalid"] : ''; ?>" name="AffiliateID[<?php echo $globalid; ?>]" id="AffiliateID[<?php echo $globalid; ?>]" placeholder="ENTER YOUR AFFILIATE ID FOR <?php echo $flag; ?>">
			</div>
			<div class="WooZone-country-name">
				<?php echo $country_name; ?>
			</div>
		</div>
		<?php
		}
		?>
	</div>
<?php
	$html[] = ob_get_clean();

	$html[] = '<h3>Some hints and information:</h3>';
	$html[] = '- The link will use IP-based Geolocation to geographically target your visitor to the Amazon store of his/her country (according to their current location). <br />';
	$html[] = '- You don\'t have to specify all affiliate IDs if you are not registered to all programs. <br />';
	$html[] = '- The ASIN is unfortunately not always globally unique. That\'s why you sometimes need to specify several ASINs for different shops. <br />';
	$html[] = '- If you have an English website, it makes most sense to sign up for the US, UK and Canadian programs. <br />';
	$html[] = '</div>';
	$html[] = '</div>';
	
	return implode("\n", $html);
}

function __WooZone_attributes_clean_duplicate( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row attr-clean-duplicate' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label for="clean_duplicate_attributes" class="WooZone-form-label">' . __('Clean duplicate attributes:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['clean_duplicate_attributes']) ) {
		$val = $options['clean_duplicate_attributes'];
	}
		
	ob_start();
?>
	<div class="WooZone-form-item">
		<select id="clean_duplicate_attributes" name="clean_duplicate_attributes" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv){
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $vv ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-attributescleanduplicate" value="' . ( __('clean Now ', $WooZone->localizationName) ) . '">
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';

	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		$("body").on("click", "#WooZone-attributescleanduplicate", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_AttributesCleanDuplicate',
				'sub_action'	: 'attr_clean_duplicate'
			}, function(response) {

				var $box = $('.attr-clean-duplicate'), 
					$res = $box.find('.WooZone-response-options');
				$res.html( response.msg_html ).show();
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

function __WooZone_category_slug_clean_duplicate( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row category-slug-clean-duplicate' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="clean_duplicate_category_slug">' . __('Clean duplicate category slug:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['clean_duplicate_category_slug']) ) {
		$val = $options['clean_duplicate_category_slug'];
	}
		
	ob_start();
?>
	<div class="WooZone-form-item">
		<select id="clean_duplicate_category_slug" name="clean_duplicate_category_slug" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv){
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $vv ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-categoryslugcleanduplicate" value="' . ( __('clean Now ', $WooZone->localizationName) ) . '">
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';

	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		$("body").on("click", "#WooZone-categoryslugcleanduplicate", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_CategorySlugCleanDuplicate',
				'sub_action'	: 'category_slug_clean_duplicate'
			}, function(response) {

				var $box = $('.category-slug-clean-duplicate'), $res = $box.find('.WooZone-response-options');
				$res.html( response.msg_html ).show();
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

function __WooZone_clean_orphaned_amz_meta( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row clean_orphaned_amz_meta' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="clean_orphaned_amz_meta">' . __('Clean orphaned AMZ meta:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['clean_orphaned_amz_meta']) ) {
		$val = $options['clean_orphaned_amz_meta']; 
	}
		
	ob_start();
?>
	<div class="WooZone-form-item">
		<select id="clean_orphaned_amz_meta" name="clean_orphaned_amz_meta" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv){
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $vv ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-cleanduplicateamzmeta" value="' . ( __('clean Now ', $WooZone->localizationName) ) . '">
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';
	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {

		$("body").on("click", "#WooZone-cleanduplicateamzmeta", function(){
			console.log( $('#AccessKeyID').val() ); 
			var tokenAnswer = prompt('Please enter security token - The security token is your AccessKeyID');
			if( tokenAnswer == $('#AccessKeyID').val() ) {
				var confirm_response = confirm("CAUTION! PERFORMING THIS ACTION WILL DELETE ALL YOUR AMAZON PRODUCT METAS! THIS ACTION IS IRREVERSIBLE! Are you sure you want to clear all amazon product meta?");
				if( confirm_response == true ) {
					$.post(ajaxurl, {
						'action' 		: 'WooZone_clean_orphaned_amz_meta',
						'sub_action'	: 'clean_orphaned_amz_meta'
					}, function(response) {
						
						var $box = $('.clean_orphaned_amz_meta'), $res = $box.find('.WooZone-response-options');
						$res.html( response.msg_html ).show();
						if ( response.status == 'valid' )
							return true;
						return false;
					}, 'json');
				}
			} else {
				alert('Security token invalid!');
			}
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

function __WooZone_delete_zeropriced_products( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row  delete_zeropriced_products' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="delete_zeropriced_products">' . __('Delete zero priced products:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['delete_zeropriced_products']) ) {
		$val = $options['delete_zeropriced_products']; 
	}
		
	ob_start();
?>
	<div class="WooZone-form-item">
		<select id="delete_zeropriced_products" name="delete_zeropriced_products" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv){
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $vv ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-delete_zeropriced_products" value="' . ( __('delete now! ', $WooZone->localizationName) ) . '">
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';
	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		$("body").on("click", "#WooZone-delete_zeropriced_products", function(){
			var confirm_response = confirm("Are you sure you want to delete all zero priced products?");
			if( confirm_response == true ) {

				var loop_max = 10, // number of max steps (10 products will be made per step => total = 10 * 10 = 100 products)
					  loop_step = 0; // current step
				var $box = $('.delete_zeropriced_products'), $res = $box.find('.WooZone-response-options');

				function __doit() {
					loop_step++;
					if ( loop_step > loop_max ) {
						$res.append( 'WORK DONE. If there are posts remained, try again.' ).show();
						return true;
					}
					
					$res.append( 'WORK IN PROGRESS...' ).show();

					$.post(ajaxurl, {
						'action' 		: 'WooZone_delete_zeropriced_products',
						'sub_action'	: 'delete_zeropriced_products'
					}, function(response) {

						$res.html( response.msg_html ).show();

						var remained = parseInt( response.nb_remained );
						if ( remained ) {
							__doit();
						} else {
							$res.append( 'WORK DONE.' ).show();
						}

						//if ( response.status == 'valid' ) {
						//	return true;
						//}
						//return false;
					}, 'json');
				}
				__doit();

			} // end confirm
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

function __WooZone_clean_orphaned_prod_assets( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row clean_orphaned_prod_assets' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="clean_orphaned_prod_assets">' . __('Clean orphaned WooZone Product Assets:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['clean_orphaned_prod_assets']) ) {
		$val = $options['clean_orphaned_prod_assets']; 
	}
		
	ob_start();
?>
	<div class="WooZone-form-item">
		<select id="clean_orphaned_prod_assets" name="clean_orphaned_prod_assets" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv){
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $vv ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-clean_orphaned_prod_assets" value="' . ( __('clean Now', $WooZone->localizationName) ) . '">
	<span class="WooZone-form-note" style="display: inline-block; margin-left: 1.5rem;">This option will clean orphan product assets from woozone tables: wp_amz_assets & wp_amz_products.</span>
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';
	//$html[] = '<span class="WooZone-form-note" style="/* margin-left: 20rem; */">This Affiliate id will be use in API request and if user are not from any of available amazon country.</span>';
	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		$("body").on("click", "#WooZone-clean_orphaned_prod_assets", function(){
			var confirm_response = confirm("Are you sure you want to delete all orphaned amazon products assets?");
			if( confirm_response == true ) {
				$.post(ajaxurl, {
					'action'        : 'WooZone_clean_orphaned_prod_assets',
					'sub_action'    : 'clean_orphaned_prod_assets'
				}, function(response) {
					var $box = $('.clean_orphaned_prod_assets'), $res = $box.find('.WooZone-response-options');
					$res.html( response.msg_html ).show();
					if ( response.status == 'valid' )
						return true;
					return false;
				}, 'json');
			}
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

function __WooZone_clean_orphaned_prod_assets_wp( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row clean_orphaned_prod_assets_wp' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="clean_orphaned_prod_assets_wp">' . __('Clean orphaned Wordpress Product Attachments:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['clean_orphaned_prod_assets_wp']) ) {
		$val = $options['clean_orphaned_prod_assets_wp']; 
	}
		
	ob_start();
?>
	<div class="WooZone-form-item">
		<select id="clean_orphaned_prod_assets_wp" name="clean_orphaned_prod_assets_wp" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv){
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $vv ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-clean_orphaned_prod_assets_wp" value="' . ( __('clean Now', $WooZone->localizationName) ) . '">
	<span class="WooZone-form-note" style="display: inline-block; margin-left: 1.5rem; color: red;">This option will clean orphan product assets from wordpress tables: wp_posts & wp_postmeta.</span>
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';
	//$html[] = '<span class="WooZone-form-note" style="/* margin-left: 20rem; */">This Affiliate id will be use in API request and if user are not from any of available amazon country.</span>';
	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		$("body").on("click", "#WooZone-clean_orphaned_prod_assets_wp", function(){
			var confirm_response = confirm("Are you sure you want to delete all orphaned wordpress products attachments?");
			if( confirm_response == true ) {
				$.post(ajaxurl, {
					'action'        : 'WooZone_clean_orphaned_prod_assets_wp',
					'sub_action'    : 'clean_orphaned_prod_assets_wp'
				}, function(response) {
					var $box = $('.clean_orphaned_prod_assets_wp'), $res = $box.find('.WooZone-response-options');
					$res.html( response.msg_html ).show();
					if ( response.status == 'valid' )
						return true;
					return false;
				}, 'json');
			}
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

function __WooZone_fix_product_attributes( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row fix-product-attributes' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="fix_product_attributes">' . __('Fix Product Attributes (woocommerce 2.4 update):', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['fix_product_attributes']) ) {
		$val = $options['fix_product_attributes'];
	}
		
	ob_start();
?>
	<div class="WooZone-form-item">
		<select id="fix_product_attributes" name="fix_product_attributes" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv){
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $vv ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-fix_product_attributes" value="' . ( __('fix Now ', $WooZone->localizationName) ) . '">
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';
	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>'

		$("body").on("click", "#WooZone-fix_product_attributes", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_product_attributes',
				'sub_action'	: 'fix_product_attributes'
			}, function(response) {

				var $box = $('.fix-product-attributes'), $res = $box.find('.WooZone-response-options');
				$res.html( response.msg_html ).show();
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

function __WooZone_fix_node_childrens( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row fix-node-childrens' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="fix_node_childrens">' . __('Clear Search old Node Childrens:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['fix_node_childrens']) ) {
		$val = $options['fix_node_childrens'];
	}
		
	ob_start();
?>
	<div class="WooZone-form-item">
		<select id="fix_node_childrens" name="fix_node_childrens" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv){
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $vv ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-fix_node_childrens" value="' . ( __('Clear Now ', $WooZone->localizationName) ) . '">
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';
	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>'

		$("body").on("click", "#WooZone-fix_node_childrens", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_node_childrens',
				'sub_action'	: 'fix_node_childrens'
			}, function(response) {

				var $box = $('.fix-node-childrens'), $res = $box.find('.WooZone-response-options');
				$res.html( response.msg_html ).show();
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

function __WooZone_amazon_countries( $istab = '', $is_subtab='', $what='array' ) {
	global $WooZone;
	
	$html         = array();
	$img_base_url = $WooZone->cfg['paths']["plugin_dir_url"] . 'modules/amazon/images/flags/';
	
	$config = $WooZone->settings();
	
	$config = $WooZone->build_amz_settings(array(
		'AccessKeyID'			=> 'zzz',
		'SecretAccessKey'		=> 'zzz',
		'country'				=> 'com',
	));
	require_once( $WooZone->cfg['paths']['plugin_dir_path'] . 'aa-framework/amz.helper.class.php' );
	if ( class_exists('WooZoneAmazonHelper') ) {
		//$theHelper = WooZoneAmazonHelper::getInstance( $aiowaff );
		$theHelper = new WooZoneAmazonHelper( $WooZone );
	}
	$list = is_object($theHelper) ? $theHelper->get_countries( $what ) : array();
	
	if ( in_array($what, array('country', 'main_aff_id')) ) {
		return $list;
	}
	return implode(', ', array_values($list));
}

// WooZone_insane_last_reports Warning: Illegal string offset 'request_amazon' issue
function __WooZone_fix_issue_request_amazon( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row fix_issue_request_amazon2' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="fix_issue_request_amazon">' . __('Fix Request Amazon Issue:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['fix_issue_request_amazon']) ) {
		$val = $options['fix_issue_request_amazon'];
	}
		
	ob_start();
?>
		<select id="fix_issue_request_amazon" name="fix_issue_request_amazon" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv){
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $vv ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-fix_issue_request_amazon" value="' . ( __('fix Now ', $WooZone->localizationName) ) . '">
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>'

		$("body").on("click", "#WooZone-fix_issue_request_amazon", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_issues',
				'sub_action'	: 'fix_issue_request_amazon'
			}, function(response) {

				var $box = $('.fix_issue_request_amazon2'), $res = $box.find('.WooZone-response-options');
				$res.html( response.msg_html ).show();
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

// Fix Sync Issue
function __WooZone_fix_issue_sync( $istab = '' ) {
	global $WooZone;
   
	$html = array();

	$options = $WooZone->getAllSettings('array', 'amazon');

	$html[] = '<div class="WooZone-bug-fix WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row fix_issue_sync-wrapp' . ($istab!='' ? ' '.$istab : '') . '" style="line-height: 35px;">';

	// products in trash after X tries
	$val_trash = $WooZone->sync_tries_till_trash;
	if ( isset($options['fix_issue_sync'], $options['fix_issue_sync']['trash_tries']) ) {
		$val_trash = $options['fix_issue_sync']['trash_tries'];
	}
	
	$html[] = '<div>';
	$html[] = '<label style="display: inline; float: none;" for="fix_issue_sync-trash_tries">' . __('Put amazon products in trash when syncing after: ', $WooZone->localizationName) . '</label>';

	ob_start();
?>
		<select id="fix_issue_sync-trash_tries" name="fix_issue_sync[trash_tries]" style="width: 120px; margin-left: 18px;">
			<?php
			foreach (array(1 => 'First try', 2 => 'Second try', 3 => 'Third try', 4 => '4th try', 5 => '5th try', -1 => 'Never') as $kk => $vv){
				echo '<option value="' . ( $kk ) . '" ' . ( $val_trash == $kk ? 'selected="selected"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	//$html[] = '<input type="button" class="WooZone-button green" style="width: 160px;" id="fix_issue_sync-save_setting" value="' . ( __('Verify how many', $WooZone->localizationName) ) . '">';
	$html[] = '<span style="margin: 0px; margin-left: 10px; display: block;" class="response_save"></span>';
	$html[] = '</div>';
	
	// restore products with status
	$val_restore = 'publish';
	if ( isset($options['fix_issue_sync'], $options['fix_issue_sync']['restore_status']) ) {
		$val_restore = $options['fix_issue_sync']['restore_status'];
	}
	
	$html[] = '<div>';
	$html[] = '<input type="button" class="WooZone-form-button-small WooZone-form-button-primary" style="vertical-align:middle;line-height:12px;" id="fix_issue_sync-fix_now" value="' . ( __('Restore now', $WooZone->localizationName) ) . '">';
	$html[] = '<label style="display: inline; float: none;" for="fix_issue_sync-restore_status">' . __('trashed amazon products (and variations) | their NEW status: ', $WooZone->localizationName) . '</label>';

	ob_start();
?>
		<select id="fix_issue_sync-restore_status" name="fix_issue_sync[restore_status]" style="width: 120px; margin-left: 18px;">
			<?php
			foreach (array('publish' => 'Publish', 'draft' => 'Draft') as $kk => $vv){
				echo '<option value="' . ( $kk ) . '" ' . ( $val_restore == $kk ? 'selected="selected"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<span style="margin: 0px; margin-left: 10px; display: block;" class="response_fixnow"></span>';
	$html[] = '</div>';

	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>'

		$("body").on("click", "#fix_issue_sync-save_setting", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_issues',
				'sub_action'	: 'sync_tries_trash'
			}, function(response) {

				var $box = $('.fix_issue_sync-wrapp'), $res = $box.find('.response_save');
				$res.html( response.msg_html );
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});

		// restore status
		$("body").on("click", "#fix_issue_sync-fix_now", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_issues',
				'sub_action'	: 'sync_restore_status',
				'what'			: 'verify'
			}, function(response) {

				var $box = $('.fix_issue_sync-wrapp'), $res = $box.find('.response_fixnow');
				$res.html( response.msg_html );
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
		
		$("body").on("click", "#fix_issue_sync-fix_now_cancel", function(){
			var $box = $('.fix_issue_sync-wrapp'), $res = $box.find('.response_fixnow');
			$res.html('');
		});

		$("body").on("click", "#fix_issue_sync-fix_now_doit", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_issues',
				'sub_action'	: 'sync_restore_status',
				'what'			: 'doit',
				'post_status'	: $('#fix_issue_sync-restore_status').val(),
			}, function(response) {

				var $box = $('.fix_issue_sync-wrapp'), $res = $box.find('.response_fixnow');
				$res.html( response.msg_html );
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
   	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

// reset products stats
function __WooZone_reset_products_stats( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row reset_products_stats2' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="reset_products_stats">' . __('Reset products stats:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['reset_products_stats']) ) {
		$val = $options['reset_products_stats'];
	}
		
	ob_start();
?>
		<select id="reset_products_stats" name="reset_products_stats" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv){
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $vv ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-reset_products_stats" value="' . ( __('reset Now ', $WooZone->localizationName) ) . '">
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>'

		$("body").on("click", "#WooZone-reset_products_stats", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_issues',
				'sub_action'	: 'reset_products_stats'
			}, function(response) {

				var $box = $('.reset_products_stats2'), $res = $box.find('.WooZone-response-options');
				$res.html( response.msg_html ).show();
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

// from version 9.0 options prefix changed from wwcAmzAff to WooZone
function __WooZone_options_prefix_change( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row options_prefix_change2' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="options_prefix_change">' . __('Version 9.0 options prefix change:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['options_prefix_change']) ) {
		$val = $options['options_prefix_change'];
	}
		
	ob_start();
?>
		<select id="options_prefix_change" name="options_prefix_change" style="width:240px; margin-left: 18px;">
			<?php
			$arr_sel = array(
				//'default' 		=> 'Default (keep new version 9.0 settings)',
				'use_new'		=> 'Keep new version 9.0 settings',
				'use_old'		=> 'Restore old version prior to 9.0 settings'
			);
			foreach ($arr_sel as $kk => $vv){
				echo '<option value="' . ( $kk ) . '" ' . ( $val == $kk ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			} 
			?>
		</select>&nbsp;&nbsp;
<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-options_prefix_change" value="' . ( __('do it now', $WooZone->localizationName) ) . '">
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';

	// view page button
	ob_start();
?>
	<script>
	(function($) {
		var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>'

		$("body").on("click", "#WooZone-options_prefix_change", function(){

			$.post(ajaxurl, {
				'action' 			: 'WooZone_fix_issues',
				'sub_action'	: 'options_prefix_change',
				'what'			: $('#options_prefix_change').val()
			}, function(response) {

				var $box = $('.options_prefix_change2'), $res = $box.find('.WooZone-response-options');
				$res.html( response.msg_html ).show();
				if ( response.status == 'valid' ) {
					window.location.reload();
					return true;
				}
				return false;
			}, 'json');
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

// from version 9.0 options prefix changed from wwcAmzAff to WooZone
function __WooZone_unblock_cron( $istab = '' ) {
	global $WooZone;
   
	$html = array();
	
	$html[] = '<div class="WooZone-bug-fix panel-body WooZone-panel-body WooZone-form-row unblock_cron' . ($istab!='' ? ' '.$istab : '') . '">';

	$html[] = '<label class="WooZone-form-label" for="options_prefix_change">' . __('Unblock CRON jobs:', $WooZone->localizationName) . '</label>';

	$options = $WooZone->getAllSettings('array', 'amazon');
	$val = '';
	if ( isset($options['unblock_cron']) ) {
		$val = $options['unblock_cron'];
	}

?>
	<select id="unblock_cron" name="unblock_cron" style="width:120px; margin-left: 18px;">
			<?php
			foreach (array('yes' => 'YES', 'no' => 'NO') as $kk => $vv) {
				echo '<option value="' . ( $vv ) . '" ' . ( $val == $kk ? 'selected="true"' : '' ) . '>' . ( $vv ) . '</option>';
			}
			?>
		</select>&nbsp;&nbsp;
	<?php
	$html[] = ob_get_contents();
	ob_end_clean();

	$html[] = '<input type="button" class="' . ( WooZone()->alias ) . '-form-button-small ' . ( WooZone()->alias ) . '-form-button-primary" id="WooZone-unblock_cron" value="' . ( __('Unblock Now ', $WooZone->localizationName) ) . '">
	<div style="width: 100%; display: none; margin-top: 10px; " class="WooZone-response-options  WooZone-callout WooZone-callout-info"></div>';

	$html[] = '</div>';

	// view page button
	ob_start();
	?>
	<script>
	(function($) {
		var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>'

		$("body").on("click", "#WooZone-unblock_cron", function(){

			$.post(ajaxurl, {
				'action' 		: 'WooZone_fix_issues',
				'sub_action'	: 'unblock_cron'
			}, function(response) {

				var $box = $('.unblock_cron'), $res = $box.find('.WooZone-response-options');
				$res.html( response.msg_html ).show();
				if ( response.status == 'valid' )
					return true;
				return false;
			}, 'json');
		});
	})(jQuery);
	</script>
<?php
	$__js = ob_get_contents();
	ob_end_clean();
	$html[] = $__js;
  
	return implode( "\n", $html );
}

function __WooZone_productinpost_extra_css() {
/*
.wb-buy {
	width: 176px;
	height: 28px;
	background: url(images/buy.gif) no-repeat top left;
	text-indent: -99999px;
	overflow: hidden;
	display: block;
	opacity: 0.7;
	transition: opacity 350ms ease;
}
*/  
	ob_start();
?>
.wb-box {
	background-color: #f9f9f9;
	border: 1px solid #ecf0f1;
	border-radius: 5px;
	font-family: 'Open Sans', sans-serif;
	margin: 20px auto;
	padding: 2%;
	width: 90%;
	max-width: 660px;
	font-family: 'Open Sans';
}
<?php
	$html = ob_get_contents();
	ob_end_clean();
	return $html;
}

function __WooZone_asof_font_size($min=0.1, $max=2.0, $step=0.1) {
	$newarr = array();
	for ($i=$min; $i <= $max; $i += $step, $i = (float) number_format($i, 1)) {
		$newarr[ "$i" ] = $i . ' em';
	}
	return $newarr;
}

function __WooZone_cache_images( $action='default', $istab = '', $is_subtab='' ) {
    global $WooZone;
    
    $req['action'] = $action;

    if ( $req['action'] == 'getStatus' ) {
            return '';
    }

    $html = array();
    
    ob_start();
?>
<div class="WooZone-form-row WooZone-im-cache <?php echo ($istab!='' ? ' '.$istab : ''); ?><?php echo ($is_subtab!='' ? ' '.$is_subtab : ''); ?>">

    <label><?php _e('Images Cache', 'psp'); ?></label>
    <div class="WooZone-form-item large">
        <span style="margin:0px 0px 0px 10px" class="response"><?php //echo __WooZone_cache_images( 'getStatus' ); ?></span><br />
        <input type="button" class="WooZone-form-button WooZone-form-button-danger" style="width: 160px;" id="WooZone-im-cache-delete" value="<?php _e('Clear cache', 'psp'); ?>">
        <span class="formNote">&nbsp;</span>

    </div>
</div>
<?php
    $htmlRow = ob_get_contents();
    ob_end_clean();
    $html[] = $htmlRow;
    
    // view page button
    ob_start();
?>
    <script>
    (function($) {
        var ajaxurl = '<?php echo admin_url('admin-ajax.php');?>';
        
        $(document).ready(function() {
            get_status();
        });

        $("body").on("click", "#WooZone-im-cache-delete", function(){
        	cache_delete();
        });
        
        function get_status() {
            $.post(ajaxurl, {
                'action'        : 'WooZone_images_cache',
                'sub_action'    : 'getStatus'
            }, function(response) {

                var $box = $('.WooZone-im-cache'), $res = $box.find('.response');
                $res.html( response.msg_html );
                if ( response.status == 'valid' )
                    return true;
                return false;
            }, 'json');
        };
        
        function cache_delete() {
            $.post(ajaxurl, {
                'action'        : 'WooZone_images_cache',
                'sub_action'    : 'cache_delete'
            }, function(response) {

                var $box = $('.WooZone-im-cache'), $res = $box.find('.response');
                $res.html( response.msg_html );
                if ( response.status == 'valid' )
                    return true;
                return false;
            }, 'json');
        }
    })(jQuery);
    </script>
<?php
    $__js = ob_get_contents();
    ob_end_clean();
    $html[] = $__js;

    return implode( "\n", $html );
}

global $WooZone;
echo json_encode(array(
	$tryed_module['db_alias'] => array(
		
		/* define the form_sizes  box */
		'amazon' => array(
			'title' => 'Amazon settings',
			'icon' => '{plugin_folder_uri}images/amazon.png',
			'size' => 'grid_4', // grid_1|grid_2|grid_3|grid_4
			'header' => true, // true|false
			'toggler' => false, // true|false
			'buttons' => true, // true|false
			'style' => 'panel', // panel|panel-widget
			
				// tabs
				'tabs'	=> array(
					'__tab1'	=> array(__('Amazon SETUP', $WooZone->localizationName), 'protocol, country, AccessKeyID, SecretAccessKey, AffiliateId, main_aff_id, buttons, help_required_fields, help_available_countries, amazon_requests_rate'),
					'__tab2'	=> array(__('Plugin SETUP', $WooZone->localizationName), 'disable_amazon_checkout, onsite_cart, cross_selling, cross_selling_nbproducts, cross_selling_choose_variation, checkout_type, checkout_email, checkout_email_mandatory, export_checkout_emails, 90day_cookie, remove_gallery, remove_featured_image_from_gallery, show_short_description, redirect_time, show_review_tab, redirect_checkout_msg, product_buy_is_amazon_url, frontend_show_free_shipping, frontend_show_coupon_text, charset, services_used_forip, product_buy_text, remote_amazon_images, images_sizes_allowed, productinpost_additional_images, productinpost_extra_css, product_countries, product_countries_main_position, product_countries_maincart, product_countries_countryflags, product_buy_button_open_in, asof_font_size, delete_attachments_at_delete_post, cache_remote_images'),
					'__tab3'	=> array(__('Import SETUP', $WooZone->localizationName), 'price_setup, merchant_setup, product_variation, import_price_zero_products, default_import, import_type, ratio_prod_validate, item_attribute, selected_attributes, attr_title_normalize, cron_number_of_images, number_of_images, rename_image, spin_at_import, spin_max_replacements, create_only_parent_category, variation_force_parent'),
					'__tab4'	=> array(__('BUG Fixes', $WooZone->localizationName), ''),
					'__tab5'	=> array(__('DEBUG', $WooZone->localizationName), 'debug_ip'),
				),
			
			// create the box elements array
			'elements' => array(

				'disable_amazon_checkout' => array(
                    'type' => 'select',
                    'std' => 'no',
                    'size' => 'large',
                    'force_width' => '100',
                    'title' => 'Disable amazon checkout?',
                    'desc' => 'Choose Yes if you want to disable the redirect fuction to the amazon checkout.<br /><div style="color: red;">You need to take care of the checkout and shipment process and provide another way of making commisions though amazon, by implementing a custom solution.<br />
Clients can still add the products from amazon into your cart on your website, but you need to process their orders and then order yourself the products to amazon manually (using a credit card) and then make yourself the shippments to your clients.<br />
Basically, your amazon products will be just like regular woocommerce products which you can re-sell.</div>',
                    'options' => array(
                        'no' => 'NO',
                        'yes' => 'YES'
                    )
                ),

				'services_used_forip' => array(
					'type' => 'select',
					'std' => 'www.geoplugin.net',
					'size' => 'large',
					'force_width' => '380',
					'title' => 'External server country detection or use local:',
					'desc' => 'We use an external server for detecting client country per IP address or you can try local IP detection. ( www.telize.com was shut down on November 15th, 2015 || api.hostip.info not working anymore )',
					'options' => array(
						'local_csv'                 => 'Local IP detection (plugin local csv file with IP range lists)',
						//'api.hostip.info'           => 'api.hostip.info',
						'www.geoplugin.net' 		=> 'www.geoplugin.net',
						//'www.telize.com'			=> 'www.telize.com',
						'ipinfo.io' 				=> 'ipinfo.io',
					)
				),
				
				'charset' 	=> array(
					'type' 		=> 'text',
					'std' 		=> '',
					'size' 		=> 'large',
					'force_width'=> '400',
					'title' 	=> __('Server Charset:', $WooZone->localizationName),
					'desc' 		=> __('Server Charset (used by php-query class)', $WooZone->localizationName)
				),

				'product_buy_is_amazon_url' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Show Amazon Url as Buy Url',
					'desc' => 'If you choose YES then the product buy url will be the original amazon product url (the On-site Cart option must be set to "No" also in order for this to work!).',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				'frontend_show_free_shipping' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Show Free Shipping',
					'desc' => 'Show Free Shipping text on frontend.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				'frontend_show_coupon_text' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Show Coupon',
					'desc' => 'Show Coupon text on frontend.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				'onsite_cart' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'On-site Cart',
					'desc' => 'This option will allow your customers to add multiple Amazon Products into Cart and checkout trought Amazon\'s system with all at once.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				/*'checkout_type' => array(
					'type' => 'select',
					'std' => '_self',
					'size' => 'large',
					'force_width' => '200',
					'title' => 'Checkout type:',
					'desc' => 'This option will allow you to setup how the Amazon Checkout process will happen. If you wish to open the amazon products into a new tab, or in the same tab.',
					'options' => array(
						'_self' => 'Self - into same tab',
						'_blank' => 'Blank - open new tab'
					)
				),*/
				
				'checkout_email' => array(
                    'type' => 'select',
                    'std' => 'no',
                    'size' => 'large',
                    'force_width' => '100',
                    'title' => 'Checkout E-mail:',
                    'desc' => 'Ask the user e-mail address before the checkout process (redirect to amazon) happens and store it for later export in CSV format.',
                    'options' => array(
                        'no' => 'NO',
                        'yes' => 'YES'
                    )
                ),
                
				'checkout_email_mandatory' => array(
                    'type' => 'select',
                    'std' => 'no',
                    'size' => 'large',
                    'force_width' => '100',
                    'title' => 'Checkout E-mail Mandatory:',
                    'desc' => 'Make "Checkout E-mail" option above mandatory in order to checkout.',
                    'options' => array(
                        'no' => 'NO',
                        'yes' => 'YES'
                    )
                ),
                
				'export_checkout_emails' => array(
                    'type' => 'html',
                    'html' => '<div class="panel-body WooZone-panel-body WooZone-form-row  __tab2 " style="display: block;">
						<label for="export_checkout_emails" class="WooZone-form-label">Export Checkout Emails:</label>
						<div class="WooZone-form-item">
							<a href="'. ( admin_url( 'admin.php?page=' . WooZone()->alias ) ) .'&do=export_emails#!/amazon" id="export_checkout_emails" class="WooZone-form-button-small WooZone-form-button-info">Export Emails</a>
							<span class="WooZone-form-note">Export as CSV checkout emails sent by customers.</span>
						</div>
					</div>',
                ),
				
				'item_attribute' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Import Attributes',
					'desc' => 'This option will allow to import or not the product item attributes.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				'selected_attributes' 	=> array(
					'type' 		=> 'multiselect_left2right',
					'std' 		=> array(),
					'size' 		=> 'large',
					'rows_visible'	=> 18,
					'force_width'=> '300',
					'title' 	=> __('Select attributes', $WooZone->localizationName),
					'desc' 		=> __('Choose what attributes to be added on import process.', $WooZone->localizationName),
					'info'		=> array(
						'left' => 'All Amazon Attributes list',
						'right' => 'Your chosen items from list'
					),
					'options' 	=> __WooZone_attributesList()
				),
				
				'attr_title_normalize' => array(
					'type' => 'select',
					'std' => 'no',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Beautify attribute title',
					'desc' => 'separate attribute title words by space',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				'90day_cookie' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => '90 days cookies',
					'desc' => 'This option will activate the 90 days cookies feature',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				'price_setup' => array(
					'type' => 'select',
					'std' => 'only_amazon',
					'size' => 'large',
					'force_width' => '290',
					'title' => 'Prices setup',
					'desc' => 'Get product offer price from Amazon or other Amazon sellers.',
					'options' => array(
						'only_amazon' => 'Only Amazon',
						'amazon_or_sellers' => 'Amazon OR other sellers (get lowest price)'
					)
				),
				
				'merchant_setup' => array(
					'type' => 'select',
					'std' => 'amazon_or_sellers',
					'size' => 'large',
					'force_width' => '290',
					'title' => 'Import product from merchant',
					'desc' => 'Get products: A. only from Amazon or B. from (Amazon and other sellers).<br /><div style="color: red;">ATTENTION: If you choose "Only Amazon" then only product which have Amazon among their sellers will be imported!</div>',
					'options' => array(
						'only_amazon' => 'Only Amazon',
						'amazon_or_sellers' => 'Amazon and other sellers'
					)
				),
				
				'import_price_zero_products' => array(
					'type' => 'select',
					'std' => 'no',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Import products with price 0',
					'desc' => 'Choose Yes if you want to import products with price 0',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				'product_variation' => array(
					'type' => 'select',
					'std' => 'yes_5',
					'size' => 'large',
					'force_width' => '160',
					'title' => 'Variation',
					'desc' => 'Get product variations. Be carefull about <code>Yes All variations</code> one product can have a lot of variation, execution time is dramatically increased!',
					'options' => array(
						'no'        => 'NO',
						'yes_1'     => 'Yes 1 variation',
						'yes_2'     => 'Yes 2 variations',
						'yes_3'     => 'Yes 3 variations',
						'yes_4'     => 'Yes 4 variations',
						'yes_5'     => 'Yes 5 variations',
						'yes_10'    => 'Yes 10 variations',
						'yes_all'   => 'Yes All variations'
					)
				),
				
				'default_import' => array(
					'type' => 'select',
					'std' => 'publish',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Import as',
					'desc' => 'Default import products with status "publish" or "draft"',
					'options' => array(
						'publish' => 'Publish',
						'draft' => 'Draft'
					)
				),
				
				'import_type' => array(
					'type' => 'select',
					'std' => 'default',
					'size' => 'large',
					'force_width' => '280',
					'title' => 'Image Import type',
					'options' => array(
						'default' => 'Default - download images at import',
						'asynchronous' => 'Asynchronous image download'
					)
				),
				'ratio_prod_validate' 	=> array(
					'type' 		=> 'select',
					'std'		=> 90,
					'size' 		=> 'large',
					'title' 	=> __('Ratio product validation:', $WooZone->localizationName),
					'force_width'=> '100',
					'desc' 		=> __('The minimum percentage of total assets download (product + variations) from which a product is considered valid!', $WooZone->localizationName),
					'options'	=> $WooZone->doRange( range(10, 100, 5) )
				),
				'cron_number_of_images' => array(
					'type' => 'text',
					'std' => '100',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Cron number of images',
					'desc' => 'The number of images your cronjob file will download at each execution.'
				),
				'number_of_images' => array(
					'type' => 'text',
					'std' => 'all',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Number of images',
					'desc' => 'How many images to download for each products. Default is <code>all</code>'
				),
				/*'number_of_images_variation' => array(
					'type' => 'text',
					'std' => 'all',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Number of images for variation',
					'desc' => 'How many images to download for each product variation. Default is <code>all</code>'
				),*/
				'rename_image' => array(
					'type' => 'select',
					'std' => 'product_title',
					'size' => 'large',
					'force_width' => '130',
					'title' => 'Image names',
					'options' => array(
						'product_title' => 'Product title',
						'random' => 'Random number'
					)
				),

				'remove_gallery' => array(
					'type' => 'select',
					'std' => 'no',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Gallery',
					'desc' => 'Show gallery in product description.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				 'remove_featured_image_from_gallery' => array(
					'type' => 'select',
					'std' => 'no',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Remove featured image from product gallery',
					'desc' => 'Remove featured image from product gallery if the theme does not support it',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				'show_short_description' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Product Short Description',
					'desc' => 'Show product short description.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				'show_review_tab' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Review tab',
					'desc' => 'Show Amazon reviews tab in product description.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				'redirect_checkout_msg' => array(
					'type' => 'textarea',
					'std' => 'You will be redirected to {amazon_website} to complete your checkout!',
					'size' => 'large',
					'force_width' => '160',
					'title' => 'Checkout message',
					'desc' => 'Message for checkout redirect box.'
				),
				'redirect_time' => array(
					'type' => 'text',
					'std' => '3',
					'size' => 'large',
					'force_width' => '120',
					'title' => 'Redirect in',
					'desc' => 'How many seconds to wait before redirect to Amazon!'
				),
				
				'product_buy_text'   => array(
					'type'      => 'text',
					'std'       => '',
					'size'      => 'large',
					'force_width'=> '400',
					'title'     => __('Button buy text', $WooZone->localizationName),
					'desc'      => __('(global) This text will be shown on the button linking to the external product. (global) = all external products; external products = those with "On-site Cart" option value set to "No"', $WooZone->localizationName)
				),
							
				'product_buy_button_open_in' => array(
					'type' => 'select',
					'std' => '_self',
					'size' => 'large',
					'force_width' => '200',
					'title' => 'Product buy button open in:',
					'desc' => 'This option will allow you to setup how the product buy button will work. You can choose between opening in the same tab or in a new tab.' ,
					'options' => array(
						'_self' => 'Same tab',
						'_blank' => 'New tab'
					)
				),
				
				'spin_at_import' => array(
					'type' => 'select',
					'std' => 'no',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Spin on Import',
					'desc' => 'Choose YES if you want to auto spin post, page content at amazon import',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				'spin_max_replacements' => array(
					'type' => 'select',
					'std' => '10',
					'force_width' => '150',
					'size' => 'large',
					'title' => 'Spin max replacements',
					'desc' => 'Choose the maximum number of replacements for auto spin post, page content at amazon import.',
					'options' => array(
						'10' 		=> '10 replacements',
						'30' 		=> '30 replacements',
						'60' 		=> '60 replacements',
						'80' 		=> '80 replacements',
						'100' 		=> '100 replacements',
						'0' 		=> 'All possible replacements',
					)
				),
				
				'create_only_parent_category' => array(
					'type' => 'select',
					'std' => 'no',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Create only parent categories on Import',
					'desc' => 'This option will create only parent categories from Amazon on import instead of the whole category tree',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				/*'selected_category_tree' => array(
					'type' => 'select',
					'std' => 'no',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Create only selected category tree on Import',
					'desc' => 'This option will create only selected categories based on browsenodes on import instead of the whole category tree',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),*/
				
				'variation_force_parent' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Force import parent if is variation',
					'desc' => 'This option will force import parent if the product is a variation child.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				/* remote amazon images */
				'remote_amazon_images' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Remote amazon images',
					'desc' => 'Choose YES if you don\'t want to download on your local server the amazon images for products, but use them external.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				'images_sizes_allowed' 	=> array(
					'type' 		=> 'multiselect_left2right',
					'std' 		=> array(), //array('thumbnail', 'medium', 'shop_thumbnail', 'shop_catalog'),
					'size' 		=> 'large',
					'rows_visible'	=> 8,
					'force_width'=> '150',
					'title' 	=> __('Select remote image sizes', $WooZone->localizationName),
					'desc' 		=> __('Choose what remote image sizes you want.', $WooZone->localizationName),
					'info'		=> array(
						'left' => 'All image sizes',
						'right' => 'Your chosen image sizes from list'
					),
					'options' 	=> __WooZone_imageSizes()
				),
				
				/*'clean_duplicate_attributes' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Clean duplicate attributes',
					'desc' => 'Clean duplicate attributes.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),*/
			   
				'clean_duplicate_attributes_now' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Clean duplicate attributes Now',
					'html' => __WooZone_attributes_clean_duplicate( '__tab4' )
				),
				
				'clean_duplicate_category_slug_now' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Clean duplicate category slug Now',
					'html' => __WooZone_category_slug_clean_duplicate( '__tab4' )
				),
				
				'delete_all_zero_priced_products' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Delete all products with price zero',
					'html' => __WooZone_delete_zeropriced_products( '__tab4' )
				),
				
				'clean_orphaned_amz_meta' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Clean orphaned Amz meta Now',
					'html' => __WooZone_clean_orphaned_amz_meta( '__tab4' )
				),
				
				'clean_orphaned_products_assets' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Clean orphaned WooZone Product Assets Now',
					'html' => __WooZone_clean_orphaned_prod_assets( '__tab4' )
				),
				
				'clean_orphaned_products_assets_wp' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Clean orphaned Wordpress Product Attachments Now',
					'html' => __WooZone_clean_orphaned_prod_assets_wp( '__tab4' )
				),
				
				'fix_product_attributes_now' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Fix Product Attributes (after woocommerce 2.4 update)',
					'html' => __WooZone_fix_product_attributes( '__tab4' )
				),
				
				'fix_node_children' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Clear Search old Node Childrens',
					'html' => __WooZone_fix_node_childrens( '__tab4' )
				),
				
				/* Amazon Config */
				'protocol' => array(
					'type' => 'select',
					'std' => '',
					'size' => 'large',
					'force_width' => '200',
					'title' => 'Request Type',
					'desc' => 'How the script should make the request to Amazon API.',
					'options' => array(
						'auto' => 'Auto Detect',
						'soap' => 'SOAP',
						'xml' => 'XML (over cURL, streams, fsockopen)'
					)
				),
				'country' => array(
					'type' => 'select',
					'std' => '',
					'size' => 'large',
					'force_width' => '150',
					'title' => 'Amazon locations',
					'desc' => 'All possible locations.',
					'options' => __WooZone_amazon_countries( '__tab1', '__subtab1', 'country' )
				),
				'help_required_fields' => array(
					'type' => 'message',
					'status' => 'info',
					'html' => 'The following fields are required in order to send requests to Amazon and retrieve data about products and listings. If you do not already have access keys set up, please visit the <a href="https://aws-portal.amazon.com/gp/aws/developer/account/index.html?ie=UTF8&amp;action=access-key#access_credentials" target="_blank">AWS Account Management</a> page to create and retrieve them.'
				),
				'AccessKeyID' => array(
					'type' => 'text',
					'std' => '',
					'size' => 'large',
					'title' => 'Access Key ID',
					'force_width' => '250',
					'desc' => 'Are required in order to send requests to Amazon API.'
				),
				'SecretAccessKey' => array(
					'type' => 'text',
					'std' => '',
					'size' => 'large',
					'force_width' => '400',
					'title' => 'Secret Access Key',
					'desc' => 'Are required in order to send requests to Amazon API.'
				),
				'AffiliateId' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Affiliate Information',
					'html' => __WooZoneAffIDsHTML( '__tab1' )
				),
				'main_aff_id' => array(
					'type' => 'select',
					'std' => '',
					'force_width' => '150',
					'size' => 'large',
					'title' => 'Main Affiliate ID',
					'desc' => 'This Affiliate id will be use in API request and if user are not from any of available amazon country.',
					'options' => __WooZone_amazon_countries( '__tab1', '__subtab1', 'main_aff_id' )
				),
				'buttons' => array(
					'type' => 'buttons',
					'options' => array(
						'check_amz' => array(
							'type' => 'button',
							'value' => 'Check Amazon AWS Keys',
							'color' => 'info',
							'action' => 'WooZoneCheckAmzKeys'
						)
					)
				),
				'help_available_countries' => array(
					'type' => 'message',
					'status' => 'info',
					'html' => '
							<strong>Available countries: &nbsp;</strong>
							'.__WooZone_amazon_countries( '__tab1', '__subtab1', 'string' ).'
						'
				),
				'amazon_requests_rate' => array(
					'type' => 'select',
					'std' => '1',
					'force_width' => '200',
					'size' => 'large',
					'title' => 'Amazon requests rate',
					'desc' => 'The number of <a href="https://affiliate-program.amazon.com/gp/advertising/api/detail/faq.html" target="_blank">amazon requests per second</a> based on 30-day sales for your account.',
					'options' => array(
						'0.10' => '1 req per 10sec',
						'0.20' => '1 req per 5sec',
						'0.25' => '1 req per 4sec',
						'0.5' => '1 req per 2sec',
						'1' => '1 req per sec - till 2299$',
						'2' => '2 req per sec - till 9999$',
						'3' => '3 req per sec - till 19999$',
						'5' => '5 req per sec - from 20000$',
					)
				),
				
				'fix_issue_request_amazon_now' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Fix Request Amazon Issue',
					'html' => __WooZone_fix_issue_request_amazon( '__tab4' )
				),
				
                'fix_issue_sync' => array(
                    'type' => 'html',
                    'std' => '',
                    'size' => 'large',
                    'title' => 'Sync Issue',
                    'html' => __WooZone_fix_issue_sync( '__tab4' )
                ),

				'reset_products_stats_now' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Reset products stats',
					'html' => __WooZone_reset_products_stats( '__tab4' )
				),
				
				'options_prefix_change_now' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Version 9.0 options prefix change',
					'html' => __WooZone_options_prefix_change( '__tab4' )
				),
				
				'unblock_cron' => array(
					'type' => 'html',
					'std' => '',
					'size' => 'large',
					'title' => 'Unblock CRON jobs',
					'html' => __WooZone_unblock_cron( '__tab4' )
				),
				
				/* Product in post */
				'productinpost_additional_images' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Product in post: Show Additional Images',
					'desc' => 'Product in post: Show Additional Images',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				'productinpost_extra_css' => array(
					'type' => 'textarea',
					'std' => '',
					'size' => 'large',
					'force_width' => '560',
					'title' => 'Product in post: Extra CSS',
					'desc' => 'Product in post: Extra CSS for frontend boxes' . PHP_EOL . '<div style="height: 100px; overflow: auto;"><pre>' . __WooZone_productinpost_extra_css() . '</pre></div>'
				),
				
				/* product available countries */
				'product_countries' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Activate Product Availability by Country Box',
					'desc' => 'Choose YES if you want to activate product Availability by countries functionality',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				'product_countries_main_position' => array(
					'type' => 'select',
					'std' => 'before_add_to_cart',
					'size' => 'large',
					'force_width' => '500',
					'title' => 'Product Availability by <br/> Country Box',
					'desc' => 'This box will be positioned on product details page. Select where to display it:',
					'options' => array(
						'before_title_and_thumb'			=> 'Before Title and Thumb',
						'before_add_to_cart'					=> 'Before Add to Cart Button',
						'before_woocommerce_tabs'	=> 'Before Woocommerce Tabs',
						'as_woocommerce_tab'			=> 'As New Woocommerce Tab - COUNTRIES AVAILABLITY',
					)
				),
				'product_countries_maincart' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Show Country Flag on Cart Page?',
					'desc' => 'Choose YES if you want to show the current selected country for each product on cart page',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				'product_countries_countryflags' => array(
					'type' => 'select',
					'std' => 'no',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Country Flags as Links?',
					'desc' => 'Choose YES if you want to show the country flags as links, on product details page.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				/*'product_countries_where' 	=> array(
					'type' 		=> 'multiselect_left2right',
					'std' 			=> array('maincart', 'minicart'),
					'size' 		=> 'large',
					'rows_visible'	=> 2,
					'force_width'=> '300',
					'title' 	=> __('Where product current selected country is showed?', $WooZone->localizationName),
					'desc' 		=> __('Choose where you want to have an indicator of product current selected country', $WooZone->localizationName),
					'info'		=> array(
						'left' => 'Extra zones',
						'right' => 'Your chosen extra zones'
					),
					'options' 	=> array(
						'maincart'			=> 'frontend main cart page',
						'minicart'			=> 'frontend mini cart box'
					)
				),*/

				'asof_font_size' => array(
					'type' => 'select',
					'std' => '0.6',
					'size' => 'large',
					'force_width' => '100',
					'title' => '"As Of" text font size',
					'desc' => 'Choose the font size (in em) for "as of" text',
					'options' => __WooZone_asof_font_size()
				),
				'delete_attachments_at_delete_post' => array(
					'type' => 'select',
					'std' => 'no',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Delete attachments also when you delete product?',
					'desc' => '<span style="color: red;">ATTENTION: If you choose YES, then all product attachements will be removed from database (and from your hard-drive if don\'t use the "remote images" option). So you must be sure that you\'re product attachments aren\'t used in other posts, without being directly attached to them.</span>',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				'cross_selling' => array(
					'type' => 'select',
					'std' => 'yes',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Cross-selling',
					'desc' => 'Show Frequently Bought Together box.',
					'options' => array(
						'yes' => 'YES',
						'no' => 'NO'
					)
				),
				
				'cross_selling_nbproducts' => array(
					'type' => 'select',
					'std' => '3',
					'size' => 'large',
					'force_width' => '100',
					'title' => 'Cross-selling Nb Products',
					'desc' => 'Choose how many products do you want to display in your "Frequently Bought Together box" box.',
					'options' => $WooZone->doRange( range(3, 10, 1) )
				),

				'cross_selling_choose_variation' => array(
					'type' => 'select',
					'std' => 'first',
					'size' => 'large',
					'force_width' => '200',
					'title' => 'Cross-selling Variable Product',
					'desc' => 'If we encounter variable products when we try to build the cross sell box, we must choose one of their coresponding variation children to be, because you cannot buy main variable products, but only one of their variations. We also don\'t take into consideration variations without a valid non-zero price. So choose here which variation should we get for each encountered variable product.',
					'options' => array(
						'first' => 'First variation',
						'lowest_price' => 'Lowest price variation',
						'highest_price' => 'Highest price variation'
					)
				),
				
				'debug_ip' => array(
					'type' => 'textarea',
					'std' => '',
					'size' => 'large',
					'force_width' => '160',
					'title' => 'Debug IP List',
					'desc' => 'You need to enter the IPs (separated by comma) for which you want to activate the plugin debug mode.<br/><em>For now debug mode only display the amazon response message for "frequently bought togheter" or "cross sell" frontend box.</em>'
				),
				
				'cache_remote_images' => array(
					'type' => 'select',
					'std' => 'file',
					'size' => 'large',
					'force_width' => '200',
					'title' => 'Cache Product Images?',
					'desc' => 'Here you choose if you want to cache the requests made by wordpress hooks when loading the page, for retrieving the products images. Wordpress make a lot of requests throught its\' functions to retrieve product image urls, sizes and those consumes your page resource. So we\' implemented this cache sysmte to help you.',
					'options' => array(
						'wpoption' => 'Use wp_options table',
						'file' => 'Use a hdd file',
						'none' => 'Disable caching'
					)
				),
				
				'__html_cache_remote_images' => array(
					'type' => 'html',
					'html' => __WooZone_cache_images( 'default', '__tab2', '' )
				),
			)
		)
	)
));