<?php
/**
 * @author    MandarinCMS <info@jiiworks.net>
 * @link      http://www.jiiworks.net/
 * @copyright 2015 MandarinCMS
 */
 
if( !defined( 'BASED_TREE_URI') ) exit();

class ThunderSliderWooCommerce{
	
	const ARG_REGULAR_PRICE_FROM = "reg_price_from";
	const ARG_REGULAR_PRICE_TO = "reg_price_to";
	const ARG_SALE_PRICE_FROM = "sale_price_from";
	const ARG_SALE_PRICE_TO = "sale_price_to";
	const ARG_IN_STOCK_ONLY = "instock_only";		
	const ARG_FEATURED_ONLY = "featured_only";		
	
	const META_REGULAR_PRICE = "_regular_price";
	const META_SALE_PRICE = "_sale_price";
	const META_SKU = "_sku";	//can be 'instock' or 'outofstock'
	const META_STOCK = "_stock";	//can be 'instock' or 'outofstock'
	
	
	const SORTBY_NUMSALES = "meta_num_total_sales";
	const SORTBY_REGULAR_PRICE = "meta_num__regular_price";
	const SORTBY_SALE_PRICE = "meta_num__sale_price";
	const SORTBY_FEATURED = "meta__featured";
	const SORTBY_SKU = "meta__sku";
	const SORTBY_STOCK = "meta_num_stock";
	
	/**
	 * 
	 * return true / false if the woo commerce exists
	 */
	public static function isWooCommerceExists(){
		
		if(class_exists( 'Woocommerce' ))
			return(true);
		
		return(false);
	}
	
	
	/**
	 * compare wc current version to given version
	 */
	public static function version_check( $version = '1.0' ) {
		if(self::isWooCommerceExists()){
			global $woocommerce;
			if(version_compare($woocommerce->version, $version, '>=')){
				return true;
			}
		}
		return false;
	}
	
	
	/**
	 * 
	 * get wc post types
	 */
	public static function getCustomPostTypes(){
		$arr = array();
		$arr["product"] = __("Product", 'thunderslider');
		$arr["product_variation"] = __("Product Variation", 'thunderslider');
		
		return($arr);
	}
	
	/**
	 * 
	 * get price query
	 */
	private static function getPriceQuery($priceFrom, $priceTo, $metaTag){
		
			if(empty($priceFrom))
				$priceFrom = 0;
				
			if(empty($priceTo))
				$priceTo = 9999999999;
			
			$query = array( 'key' => $metaTag,
								   'value' => array( $priceFrom, $priceTo),
								   'type' => 'numeric',
								   'compare' => 'BETWEEN');
		
		return($query);
	}
	
	
	/**
	 * 
	 * get meta query for filtering woocommerce posts. 
	 */
	public static function getMetaQuery($args){
		
		$regPriceFrom = ThunderSliderFunctions::getVal($args, self::ARG_REGULAR_PRICE_FROM);
		$regPriceTo = ThunderSliderFunctions::getVal($args, self::ARG_REGULAR_PRICE_TO);
		
		$salePriceFrom = ThunderSliderFunctions::getVal($args, self::ARG_SALE_PRICE_FROM);
		$salePriceTo = ThunderSliderFunctions::getVal($args, self::ARG_SALE_PRICE_TO);
		
		$inStockOnly = ThunderSliderFunctions::getVal($args, self::ARG_IN_STOCK_ONLY);
		$featuredOnly = ThunderSliderFunctions::getVal($args, self::ARG_FEATURED_ONLY);
		
		$arrQueries = array();
		$tax_query = array();
		
		//get regular price array
		if(!empty($regPriceFrom) || !empty($regPriceTo)){
			$arrQueries[] = self::getPriceQuery($regPriceFrom, $regPriceTo, self::META_REGULAR_PRICE);
		}
		
		//get sale price array
		if(!empty($salePriceFrom) || !empty($salePriceTo)){
			$arrQueries[] = self::getPriceQuery($salePriceFrom, $salePriceTo, self::META_SALE_PRICE);
		}
		
		if($inStockOnly == "on"){
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'outofstock',
				'operator' => 'NOT IN',
			);
		}
		
		if($featuredOnly == "on"){
			$tax_query[] = array(
				'taxonomy' => 'product_visibility',
				'field'    => 'name',
				'terms'    => 'featured',
			);
		}
		
		
		$query = array();
		if(!empty($arrQueries)){
			$query = array("meta_query"=>$arrQueries);
		}
		
		if(!empty($tax_query)){
			$query['tax_query'] = $tax_query;
		}
		
		return($query);			
	}
	
	
	/**
	 * 
	 * get sortby function including standart mcms sortby array
	 */
	public static function getArrSortBy(){
		
		$arrSortBy = array();
		$arrSortBy[self::SORTBY_REGULAR_PRICE] = __("Regular Price", 'thunderslider');
		$arrSortBy[self::SORTBY_SALE_PRICE] = __("Sale Price", 'thunderslider');
		$arrSortBy[self::SORTBY_NUMSALES] = __("Number Of Sales", 'thunderslider');
		$arrSortBy[self::SORTBY_FEATURED] = __("Featured Products", 'thunderslider');
		$arrSortBy[self::SORTBY_SKU] = __("SKU", 'thunderslider');
		$arrSortBy[self::SORTBY_STOCK] = __("Stock Quantity", 'thunderslider');
		
		return($arrSortBy);
	}
	
	
}	//end of the class
	
?>