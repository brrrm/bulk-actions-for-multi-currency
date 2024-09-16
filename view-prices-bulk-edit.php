<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<fieldset class="inline-edit-col-right">
	<div id="garbo-fields-bulk" class="inline-edit-col">

		<h4><?php _e( 'Prices', 'woocommerce' ); ?></h4>

		<?php foreach($wmcCurrencies as $currency_key => $currency): ?>
			<?php if($currency_key == $default_currency){
				continue; 
			} ?>
			<div class="inline-edit-group currency <?php echo $currency_key; ?>">
				<label class="alignleft">
					<span class="title"><?php printf(__( 'Price %s', 'woocommerce' ), $currency_key); ?></span>
					<span class="input-text-wrap">
						<select class="change_regular_price change_to <?php echo $currency_key; ?>" name="change_regular_price_<?php echo $currency_key; ?>">
							<?php
							$options = array(
								''  => __( '— No change —', 'woocommerce' ),
								'1' => __( 'Change to:', 'woocommerce' ),
								'2' => __( 'Increase existing price by (fixed amount or %):', 'woocommerce' ),
								'3' => __( 'Decrease existing price by (fixed amount or %):', 'woocommerce' ),
							);
							foreach ( $options as $key => $value ) {
								echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
							}
							?>
						</select>
					</span>
				</label>
				<label class="change-input" style="display: none;">
					<input type="text" name="_regular_price_<?php echo $currency_key; ?>" class="text regular_price <?php echo $currency_key; ?>" placeholder="<?php printf( esc_attr__( 'Enter price (%s)', 'woocommerce' ), get_woocommerce_currency_symbol() ); ?>" value="" />
				</label>
			</div>

			<div class="inline-edit-group currency <?php echo $currency_key; ?>">
				<label class="alignleft">
					<span class="title"><?php printf(__( 'Sale %s', 'woocommerce' ), $currency_key); ?></span>
					<span class="input-text-wrap">
						<select class="change_sale_price change_to <?php echo $currency_key; ?>" name="change_sale_price_<?php echo $currency_key; ?>">
							<?php
							$options = array(
								''  => __( '— No change —', 'woocommerce' ),
								'1' => __( 'Change to:', 'woocommerce' ),
								'2' => __( 'Increase existing sale price by (fixed amount or %):', 'woocommerce' ),
								'3' => __( 'Decrease existing sale price by (fixed amount or %):', 'woocommerce' ),
								'4' => __( 'Set to regular price decreased by (fixed amount or %):', 'woocommerce' ),
							);
							foreach ( $options as $key => $value ) {
								echo '<option value="' . esc_attr( $key ) . '">' . esc_html( $value ) . '</option>';
							}
							?>
						</select>
					</span>
				</label>
				<label class="change-input" style="display: none;">
					<input type="text" name="_sale_price_<?php echo $currency_key; ?>" class="text sale_price <?php echo $currency_key; ?>" placeholder="<?php printf( esc_attr__( 'Enter sale price (%s)', 'woocommerce' ), get_woocommerce_currency_symbol() ); ?>" value="" />
				</label>
			</div>
		<?php endforeach; ?>
	</div>
</fieldset>
