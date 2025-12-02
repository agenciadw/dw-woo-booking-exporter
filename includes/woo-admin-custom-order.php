<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if ( in_array( 'woocommerce-admin-custom-order-fields/woocommerce-admin-custom-order-fields.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    if( !class_exists('WBE_ADMIN_CUSTOM_ORDER_FIELDS') ) {
        class WBE_ADMIN_CUSTOM_ORDER_FIELDS {

            private static $instance = null;
            
            public function __construct() {
                add_action( 'wbe_add_custom_fields_in_export_tab', array($this, 'wbe_admin_custom_order_fields') );
            }

            public static function getInstance() {
                if (self::$instance == null) {
                    self::$instance = new WBE_ADMIN_CUSTOM_ORDER_FIELDS();
                }
                return self::$instance;
            }

            public function wbe_admin_custom_order_fields( $selected_template ) { ?>
                <li class="exporter-data-heading"><?php esc_html_e('WooCommerce Admin Custom Order Fields', 'wbe-exporter') ?>:</li>
                <?php 
                    $admin_order_fields = get_option( 'wc_admin_custom_order_fields', true ); 
                    if( count($admin_order_fields) == 0 ) {
                        echo "<li>".__('No fields found please create first;', 'wbe-exporter' )."</li>";
                    }
                ?>
                <?php foreach ( $admin_order_fields as $key => $admin_order_fields ) : ?>
                    <li>
                        <input type="checkbox" <?php echo (isset($selected_template['custom-order-field_'.$key])) ? "checked" : ""; ?>  name="custom-order-field_<?php echo esc_attr($key); ?>" id="custom-order-field_id_<?php echo esc_attr($key); ?>" value="<?php echo esc_attr($admin_order_fields['label']); ?>">
                        <label for="custom-order-field_<?php echo esc_attr($key); ?>" class="exporter-data-label"><?php echo esc_attr($admin_order_fields['label']); ?></label>
                    </li>
                <?php endforeach; ?>
                </li>
            <?php }

        }
        $WBE_ADMIN_CUSTOM_ORDER_FIELDS = WBE_ADMIN_CUSTOM_ORDER_FIELDS::getInstance();
    }
}
