<?php
/*
  Plugin Name: DW Woocommerce Booking Exporter
  Plugin URI: https://github.com/agenciadw/dw-woo-booking-exporter
  Description: Este plugin faz exportação dos pedidos do WooCommerce Booking em formatos CSV, Excel e PDF. Baseado originalmente no Woocommerce Booking Exporter.
  Version: 0.1.0
  Author: David William da Costa
  Author URI: https://github.com/agenciadw
  Text Domain: wbe-exporter
  Domain Path: /languages
  Requires at least: 5.0
  Requires PHP: 7.0
  License: GPL v2 or later
  License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
define('WBE_MODIFY_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WBE_MODIFY_PLUGIN_PATH', plugin_dir_path(__FILE__));

ini_set('max_execution_time', 0);
set_time_limit(0);
ini_set('memory_limit','500M'); 
if (!class_exists('Wbe_Exporter')) {
    class Wbe_Exporter {
        public function __construct() {
            $this->includes();
            $this->load_textdomain();
        }
        
        /**
         * Load plugin textdomain for translations
         */
        public function load_textdomain() {
            load_plugin_textdomain(
                'wbe-exporter',
                false,
                dirname(plugin_basename(__FILE__)) . '/languages/'
            );
        }
        
        /**
         * Add Plugin Include Files
         */
        private function includes() {
            include_once(WBE_MODIFY_PLUGIN_PATH . '/includes/WBE.php');
            include_once(WBE_MODIFY_PLUGIN_PATH . '/includes/WBE-ajaxified.php');
            include_once(WBE_MODIFY_PLUGIN_PATH . '/includes/woo-admin-custom-order.php');
        }
        public static function plugin_activation() {
			$wc_booking_email_subject = get_option("wc_booking_email_subject");
			$wc_booking_email_template = get_option("wc_booking_email_template");
			$wc_booking_email_sent_attachment = get_option("wc_booking_email_sent_attachment");

            if (!$wc_booking_email_subject)
                update_option("wc_booking_email_subject", esc_html__('Booking Email', 'wbe-exporter'));

            if (!$wc_booking_email_template)
                update_option("wc_booking_email_template", esc_html__("That's a booking report. ", 'wbe-exporter'));

            if (!$wc_booking_email_sent_attachment)
                update_option("wc_booking_email_sent_attachment", json_encode(array("csv","pdf")));
        }
        /**
         * Remove Cronjob At Deactivation
         */
        public static function cronjob_deactivation() {
            wp_clear_scheduled_hook('send_booking_emails_cronjob');
        }
    }
    $wbe_exporter = new Wbe_Exporter();
    register_activation_hook(__FILE__, array("Wbe_Exporter", "plugin_activation"));
    register_activation_hook(__FILE__, array("Wbe_Exporter", "cronjob_deactivation"));
}