<?php

/*
Plugin Name:  Garbo bulk-actions for WMC
Plugin URI:   https://www.garbo.nl
Description:  Plan delivery routes
Version:      1.0
Author:       Garbo
Author URI:   https://www.garbo.nl
License:      GPL2
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  garbo_bulk_mc
Domain Path:  /languages
*/


add_action( 'admin_enqueue_scripts', 'garbo_bulk_mc_enqueue_scripts' );
function garbo_bulk_mc_enqueue_scripts($hook){
	if($hook != 'edit.php' && !empty($_GET['post_type']) && $_GET['post_type'] === 'product'){
		return;
	}

	wp_enqueue_script('garbo-bulk-mc-actions', plugins_url('assets/garbo-bulk-actions.js', __FILE__), ['jquery'], '0.1', []);
	wp_enqueue_style('garbo-bulk-mc-actions-styles', plugins_url('assets/garbo-bulk-actions.css', __FILE__), [], '0.1');
}



/**
 * Custom bulk edit - form.
 *
 * @param string $column_name Column being shown.
 * @param string $post_type Post type being shown.
 */
function garbo_bulk_mc_bulk_edit_custom_box( $column_name, $post_type ) {
	if ( 'price' !== $column_name || 'product' !== $post_type ) {
		return;
	}

	$multiCurrencySettings = WOOMULTI_CURRENCY_Data::get_ins();
	$wmcCurrencies = $multiCurrencySettings->get_list_currencies();
	$default_currency = $multiCurrencySettings->get_default_currency();
	include plugin_dir_path( __FILE__ ) . '/view-prices-bulk-edit.php';
}
add_action( 'bulk_edit_custom_box', 'garbo_bulk_mc_bulk_edit_custom_box', 10, 2 );


function garbo_bulk_mc_edit_save($post_id, $post ){	// Check nonce.
	// first check if we are dealing with the bulk edit form or just the regular post->save hook
	if ( ! isset( $_REQUEST['woocommerce_quick_edit_nonce'] ) || ! wp_verify_nonce( $_REQUEST['woocommerce_quick_edit_nonce'], 'woocommerce_quick_edit_nonce' ) ) {
		return $post_id;
	}

	$product = wc_get_product($post);
	// Handle price - remove dates and set to lowest.
	$change_price_product_types    = apply_filters( 'woocommerce_bulk_edit_save_price_product_types', ['simple', 'external', 'variation'] );
	$can_product_type_change_price = false;

	if(!in_array($product->get_type(), $change_price_product_types)){
		if($product->get_type() == 'variable'){
			// for variable products, load their children.
			$children = $product->get_children();
			foreach ( $children as $child_id ) {
				$variant = wc_get_product($child_id);
				garbo_bulk_mc_edit_save($variant->get_id(), $variant);
			}
		}else{
			// otherwise, don't do anything
			return $post_id;
		}
	}

	$regular_price_changed = garbo_set_new_price( $product, 'regular' );
	$sale_price_changed    = garbo_set_new_price( $product, 'sale' );

	if($regular_price_changed || $sale_price_changed){
		$product->save();
	}
}
add_action( 'woocommerce_product_bulk_and_quick_edit', 'garbo_bulk_mc_edit_save', 10, 2);



/**
 * Set the new regular or sale price if requested.
 *
 * @param WC_Product $product The product to set the new price for.
 * @param string     $price_type 'regular' or 'sale'.
 * @return bool true if a new price has been set, false otherwise.
 */
function garbo_set_new_price( $product, $price_type ) {
	$request_data = $_REQUEST;
	$multiCurrencySettings = WOOMULTI_CURRENCY_Data::get_ins();
	$wmcCurrencies = $multiCurrencySettings->get_list_currencies();
	$default_currency = $multiCurrencySettings->get_default_currency();
	$wmc_prices = json_decode($product->get_meta("_{$price_type}_price_wmcp"), true);
	if(!isset($wmc_prices)){
		$wmc_prices = array_fill_keys(array_keys($wmcCurrencies), null);
	}

	$some_price_changed = false;
	
	foreach($wmcCurrencies as $currency_code => $currency){
		if($currency_code == $default_currency){ 
			if($product->get_type() != 'variation'
				|| (empty( $request_data["change_{$price_type}_price"]) || !isset($request_data["_{$price_type}_price"])) )
			{
				continue;
			}
		}elseif (empty( $request_data["change_{$price_type}_price_{$currency_code}"]) 
			|| !isset( $request_data["_{$price_type}_price_{$currency_code}"]) )
		{
			continue;
		}
		
		
		if($currency_code == $default_currency){
			$old_price = (float) $product->{"get_{$price_type}_price"}();
			$change_price = absint($request_data[ "change_{$price_type}_price" ]);
			$raw_price = wc_clean(wp_unslash($request_data[ "_{$price_type}_price" ]));
		}else{
			$old_price = (float) $wmc_prices[$currency_code];
			$change_price  = absint( $request_data[ "change_{$price_type}_price_{$currency_code}" ] );
			$raw_price     = wc_clean( wp_unslash( $request_data[ "_{$price_type}_price_{$currency_code}" ] ) );
		}

		$is_percentage = (bool) strstr( $raw_price, '%' );
		$price         = wc_format_decimal( $raw_price );

		switch ( $change_price ) {
			case 1:
				$new_price = $price;
				break;
			case 2:
				if ( $is_percentage ) {
					$percent   = $price / 100;
					$new_price = $old_price + ( $old_price * $percent );
				} else {
					$new_price = $old_price + $price;
				}
				break;
			case 3:
				if ( $is_percentage ) {
					$percent   = $price / 100;
					$new_price = max( 0, $old_price - ( $old_price * $percent ) );
				} else {
					$new_price = max( 0, $old_price - $price );
				}
				break;
			case 4:
				if ( 'sale' !== $price_type ) {
					break;
				}
				
				if($currency_code == $default_currency){
					$regular_price = $product->get_regular_price();
				}else{
					$regular_price = json_decode($product->get_meta("_regular_price_wmcp"), true)[$currency_code];	
				}

				if ( $is_percentage && is_numeric( $regular_price ) ) {
					$percent   = $price / 100;
					$new_price = max( 0, $regular_price - ( round( $regular_price * $percent, wc_get_price_decimals() ) ) );
				} else {
					$new_price = max( 0, (float) $regular_price - (float) $price );
				}
				break;

			default:
				break;
		}

		if ( isset( $new_price ) && $new_price !== $old_price ) {
			$some_price_changed = true;
			if(in_array($currency_code, ['NOK', 'SEK'])){
				$new_price = round( $new_price, 0 );
			}else{
				$new_price = round( $new_price, wc_get_price_decimals() );
			}

			if($currency_code == $default_currency){
				$product->{"set_{$price_type}_price"}( $new_price );
			}else{
				// when zero, set value to none
				$wmc_prices[$currency_code] = ($new_price === 0.00)? null : $new_price;
			}
		}
	}

	$product->update_meta_data("_{$price_type}_price_wmcp", json_encode($wmc_prices) );
	return $some_price_changed;
}


