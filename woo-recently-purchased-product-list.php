<?php
/*
Plugin Name: Woo recently sold items list
Description: With this plugin you can show recently sold items list for woocommerce under single product page and with shortcode you can show anywhere.
Plugin URI: https://www.codewithmehedi.com
Version: 1.0.0
Author: Mehedi Hasan
Author URI: https://codewithmehedi.com/contact/
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
Text Domain: wcrspl
Domain Path: /languages
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

// enqueue scripts
function wcrspl_scripts_register() {

  wp_enqueue_style( 'wcrspl_custom_css', plugins_url('scripts/wcrspl-style.css', __FILE__) );
  wp_enqueue_script( 'wcrspl_custom_script', plugins_url('scripts/wcrspl-custom.js', __FILE__), array('jquery'), null, true );

}
add_action('wp_enqueue_scripts','wcrspl_scripts_register');

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    // Put your plugin code here
   add_action('woocommerce_after_single_product',function(){
       ?>
       <h2 class="wcrspl-section-title"><?php esc_html_e( 'Recently Sold Items', 'wcrspl') ?></h2>
       <?php
       // Get 10 most recent order ids in date descending order.
// This is where you run the code and display the output
$after_date = date('Y-m-d', strtotime('-7 days'));

$args = array(

    'numberposts' => 3,
    'post_status' => 'wc-completed',
    'date_query' => array(
        'after' => $after_date,
        'inclusive' => true,
    ),
);

$orders = wc_get_orders($args);
$products = [];

foreach ( $orders as $order ) {
    $items = $order->get_items();

    foreach ( $items as $item ) {
        array_push( $products, $item->get_product_id() ); 

    }
}
$products = count( $products ) ? $products : [0];
$query_args = array(
    'posts_per_page' => 3,
    'post_status' => 'publish',
    'post_type' => 'product',
    'post__in' => $products,
    'orderby' => 'DESC',
);
$recentlyOrderedItems = new WP_Query($query_args);?>
<ul class="wcrspl-items">
<?php 

if ( $recentlyOrderedItems->have_posts() ):
    while ( $recentlyOrderedItems->have_posts() ): $recentlyOrderedItems->the_post();?>
				    <!-- code here -->
                    <li class="wcrspl-item">
                        
                        <a class="wcrspl-img-permalink" href="<?php echo esc_url(get_the_permalink()) ;?>"> <?php the_post_thumbnail('small')?></a>
                        <h2 class="wcrspl-item-header"><a href="<?php echo esc_url(get_the_permalink());?>"> <?php esc_html(the_title(),'wcrspl')  ?></a></h2>
                        <p class="wcrspl-item-price">
                            <?php global $woocommerce;
                            $product = new WC_Product(get_the_ID());
                            echo $product->get_price_html();
                            ?>
                        </p>
                        <p class="wcrspl-item-view">
                            <a class="button add_to_cart_button" href="<?php echo esc_url($product->add_to_cart_url());?>"><?php esc_html__('View Product','wcrspl') ?></a>
                        </p>
                    </li>
		        <?php endwhile;else:
                    esc_html_e("Sorry no orders found", 'wcrspl');
                ?>
	    <!-- code here -->

	<?php endif; ?>
 </ul>

<?php
});
}
else{
    add_action('admin_notices', function() {
        echo '<div class="notice notice-error"><p>'.esc_html__('Stock Status Bar For Woocommerce requires WooCommerce to be installed and active. You can download', 'wcrspl').' <a href="https://woocommerce.com/" target="_blank">WooCommerce</a> '.esc_html__('here.','wcrspl').'</p></div>';   
    });
}
