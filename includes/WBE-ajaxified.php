<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('WBE_ajaxified') && class_exists('WBE')) {
    class WBE_ajaxified extends WBE {
        public function __construct() {
            if (is_admin()) {

                add_action('wp_ajax_woo_bookings_ajax_csv_export', array($this, 'woo_bookings_ajax_csv_export'));
                add_action('wp_ajax_woo_bookings_delete_template', array($this, 'woo_bookings_delete_template_callback'));

            }
        }

        public function woo_bookings_ajax_csv_export() {

            if ( ! wp_verify_nonce( sanitize_text_field($_POST['woo_bookings_export_nonce']), 'woO_eXp_db_Nonce' ) ) {
                die( __( 'Security check Failed', 'wbe-exporter' ) ); 
            }

            if (isset($_POST['woo_bookings_export_csv']) && 'yes' == $_POST['woo_bookings_export_csv'] ) {

                $pageno = absint($_POST['woo_bookings_export_pageno']);
                
               if($pageno > 0){
                $this->export_booking_to_cv_pdf($pageno);
               }    
               
            }

		    die('Something wrong');
        }
        
        
        /**
         * Export Booking to cv or pdf 
         */


        public function export_booking_to_cv_pdf($pageno = 1) {

            //Add Delimiter
            $delimiter = ",";
            if (isset($_POST['delimiter']) && $_POST['delimiter']) {
                $delimiter = trim($_POST['delimiter']);
            }
            //coloums order
            $fieldArray = $existingArray = array();
            if (isset($_POST['coloums_order'])) {
                $existingArray = json_decode(stripslashes($_POST['coloums_order']));
            }
            //get file wanted type
            $file_type = "csv";
            if (isset($_POST['file_type'])) {
                $file_type = $_POST['file_type'];
            }
            //////////////////////////Prepare Fields Names Array//////////////////
            foreach ($existingArray as $item) {
                if (isset($_POST[$item]) && $_POST[$item]) {
                    $fieldArray[$item] = $_POST[$item];
                }
            }
            /////////////////////////Save Template/////////////////////////////////
            $saved_templates = array();
            if (get_option("booking_exporter_templates") && !empty(get_option("booking_exporter_templates")))
                $saved_templates = get_option("booking_exporter_templates");
            if (isset($_POST['save_template']) && $_POST['save_template'] && isset($_POST['template_name']) && ($template_name = $_POST['template_name'])) {
                $saved_templates[$template_name] = $fieldArray;
                update_option("booking_exporter_templates", $saved_templates);
                wp_redirect(admin_url() . '?page=booking-exporter&template=' . $template_name);
                exit;
            }
            ///////////////////////////////////Prepare data to be viewed/////////////
            // add csv data
            $dataArray = array();
            $to = $from = "";
            $prodArr = sanitize_text_field($_POST['booking_exporter_product']);
            $userArr = sanitize_text_field($_POST['booking_exporter_user']);
            //get from date
			$booking_form_date = sanitize_text_field($_POST['booking_from_date']);
            if ($booking_form_date) {
                $from = str_replace('-', '', $_POST['booking_from_date']) . '000000';
            }
            //get to date
			$booking_to_date = sanitize_text_field($_POST['booking_to_date']);
            if ($booking_to_date) {
                $to = str_replace('-', '', $_POST['booking_to_date']) . '235959';
            }
            //Get booking ids by date from/to if exists
            $booking_ids = $this->get_booking_ids_by_crietria($from, $to);
            
            $bookings_per_transaction = 300;
        
            $booking_start_no = ($pageno - 1)*$bookings_per_transaction;
            $ids_to_export = array_slice($booking_ids,$booking_start_no,$bookings_per_transaction);
            $total_pages = ceil(count($booking_ids)/$bookings_per_transaction);

            
            //Prepare Row Data
            $dataArray = $this->prepare_booking_rows_data($ids_to_export, $prodArr, $userArr, $existingArray);
            //Fields names row by column order
            $fieldArray = array_values(array_merge(array_flip($existingArray), $fieldArray));

            $file_url = '';
            /////////////////////////Create File///////////////////////////////////
            if (count($dataArray) == 0 && $pageno == 1) { 
                //if no rows
                wp_redirect(admin_url() . '?page=booking-exporter&booking=no');
                exit();
            } else{
                
                $filename = WBE_MODIFY_PLUGIN_PATH.'exports/woo-booking-export.csv';
                $file_url = WBE_MODIFY_PLUGIN_URL.'exports/woo-booking-export.csv';
                $file = '';
                if($pageno == 1){
                    $file = fopen($filename, "w");
                    fputcsv($file, $fieldArray, $delimiter);
                } else {
                    $file = fopen($filename, "a+");
                }
                
                foreach ($dataArray as $line) {
                    fputcsv($file, $line, $delimiter);
                }
                fclose($file);
                
            }

            $next_page_no = $pageno+1;
            $response = array(
                'total_pages' => $total_pages,
                'page_no' => $next_page_no,
                'file_type' => $file_type,
                'file_url' => $file_url
            );
            echo json_encode($response);
            die();
        }

        public function woo_bookings_delete_template_callback() {
			
            $template_name = sanitize_text_field( $_POST['template_name'] );
            $template_name = str_replace(' ', '_', $template_name );

            $saved_templates = get_option("booking_exporter_templates");
			
            if( isset( $template_name, $saved_templates ) ) {
                unset($saved_templates[$template_name]);
                update_option( 'booking_exporter_templates', $saved_templates );
                $response = array(
                    'success' => true,
                    'message' => 'Template deleted',
                    'key'     => $template_name
                );
            } else {
                $response = array(
                    'success' => false,
                    'message' => 'Template not found',
                );
            }
            wp_send_json( $response );
			// wp_die();
        }
        
    }
    $WBE_ajaxified = new WBE_ajaxified();
}