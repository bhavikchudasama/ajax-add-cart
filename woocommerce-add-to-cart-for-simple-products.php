<?php
/*
Plugin Name: Add to cart Ajax for Simple products
Description: Ajax based add to cart for Simple products in woocommerce.
Author: Bhavik Chudasama	
Version: 1.2.6
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
* Check if WooCommerce is active
**/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    /**
    * Create the section beneath the products tab
    **/

    add_filter( 'woocommerce_get_sections_products', 'wc_ajax_add_to_cart_simple_add_section' );
    function wc_ajax_add_to_cart_simple_add_section( $sections ) {

            $sections['wc_ajax_add_to_cart_simple'] = __( 'WC Ajax for Simple Products', 'text-domain' );
            return $sections;
    }
    
    add_filter( 'woocommerce_get_settings_products', 'wc_ajax_add_to_cart_simple_all_settings', 10, 2 );
    function wc_ajax_add_to_cart_simple_all_settings( $settings, $current_section ){
        /**
        * Check the current section is what we want
        **/
        if ( $current_section == 'wc_ajax_add_to_cart_simple' ) {
	 
                $settings_slider = array();

                // Add Title to the Settings
                $settings_slider[] = array( 'name' => __( 'WC Ajax for Simple Products Settings', 'text-domain' ), 'type' => 'title', 'desc' => __( 'The following options are used to configure WC Ajax for Variable Products', 'text-domain' ), 'id' => 'wc_ajax_add_to_cart_variable' );

                // Add first checkbox option
                $settings_slider[] = array(

                        'name'     => __( 'Add Selection option to Category Page', 'text-domain' ),
                        'desc_tip' => __( 'This will automatically insert Simple selection options on product Category Archive Page', 'text-domain' ),
                        'id'       => 'wc_ajax_add_to_cart_simple_category_page',
                        'type'     => 'checkbox',
                        'css'      => 'min-width:300px;',
                        'desc'     => __( 'Enable Varition select option on Category Archive page', 'text-domain' ),

                );

                $settings_slider[] = array( 'type' => 'sectionend', 'id' => 'wc_ajax_add_to_cart_simple' );

                return $settings_slider;

        /**
         * If not, return the standard settings
         **/

        } else {

                return $settings;

        }
    }
    
    $category_page = get_option( 'wc_ajax_add_to_cart_simple_category_page' );
    if(isset($category_page) && $category_page == "yes" ) {

            if ( ! function_exists( 'woocommerce_template_loop_add_to_cart' ) ) {

                    function woocommerce_template_loop_add_to_cart() {
                            global $product;

                            if ($product->product_type == "simple" ) {
                                    woocommerce_simple_add_to_cart();
                            }
                            else {
                                    woocommerce_get_template( 'loop/add-to-cart.php' );
                            }
                    }
            }
    }
    
    function ajax_add_to_cart_simple_script() {
        wp_enqueue_script( 'add-to-cart-simple_ajax', plugins_url() . '/woocommerce-add-to-cart-for-simple-products/js/add-to-cart-simple.js', array('jquery'), '', true );
        wp_enqueue_script( 'add-to-cart-fly-effect', plugins_url() . '/woocommerce-add-to-cart-for-simple-products/js/effect.min.js', array('jquery'), '', true );
    }
    add_action( 'wp_enqueue_scripts', 'ajax_add_to_cart_simple_script',99 );
    
    /* AJAX add to cart simple added by Bhavik */
    add_action( 'wp_ajax_woocommerce_add_to_cart_simple_rc', 'woocommerce_add_to_cart_simple_rc_callback' );
    add_action( 'wp_ajax_nopriv_woocommerce_add_to_cart_simple_rc', 'woocommerce_add_to_cart_simple_rc_callback' );
    
    function woocommerce_add_to_cart_simple_rc_callback(){
        
        ob_start();
        
        $product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
        $quantity = empty( $_POST['quantity'] ) ? 1 : apply_filters( 'woocommerce_stock_amount', $_POST['quantity'] );
        $passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
        
        if ( $passed_validation && WC()->cart->add_to_cart( $product_id, $quantity ) ) {
                do_action( 'woocommerce_ajax_added_to_cart', $product_id );
                if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 'yes' ) {
                        wc_add_to_cart_message( $product_id );
                }

                // Return fragments
                WC_AJAX::get_refreshed_fragments();
        } else {
                $this->json_headers();

                // If there was an error adding to the cart, redirect to the product page to show any errors
                $data = array(
                        'error' => true,
                        'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id )
                        );
                echo json_encode( $data );
        }
        die();
        
    }
    
}
