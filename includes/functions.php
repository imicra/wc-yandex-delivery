<?php
/**
 * Functions
 */

/**
 * Input value for hold cost from delivery api.
 */
function imicra_shipping_rate_cost( $method ) {
  if ( strstr( $method->id, IMYAD_PLUGIN_ID ) ) {
    echo '<input type="hidden" name="imwcyad_cost" id="imwcyad_cost" />';
  }
}
add_action( 'woocommerce_after_shipping_rate', 'imicra_shipping_rate_cost' );
