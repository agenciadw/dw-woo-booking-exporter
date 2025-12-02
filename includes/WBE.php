<?php
if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}
if (!class_exists('WBE')) {
    class WBE
    {
        public function __construct()
        {
            if (is_admin()) {
                if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) && (in_array('woocommerce-bookings/woocommmerce-bookings.php', apply_filters('active_plugins', get_option('active_plugins'))) || in_array('woocommerce-bookings/woocommerce-bookings.php', apply_filters('active_plugins', get_option('active_plugins'))))) {
                    add_action('admin_init', array($this, 'wbe_download_csv'));
                    // Call the html code
                    add_action('admin_menu', array($this, 'booking_settings'));

                    add_action('current_screen', array($this, 'wbe_enqueue_scripts'));

                    //monthely&weekly cronjob schedule
                    add_filter('cron_schedules', array($this, 'wbe_add_new_cron_schedules'));

                    add_action('wp_ajax_product_filter', array($this, 'product_filter_callback'));

                    add_action('wbe_add_custom_fields_in_export_tab', array($this, 'wbe_add_cfe_fields_in_export_tab'));

                    add_filter('wbe_add_custom_field_labels', array($this, 'wbe_add_cfe_field_labels'));

                    add_filter('wbe_add_custom_field_data', array($this, 'wbe_add_cfe_field_data'), 10, 5);
                } else {
                    add_action('admin_notices', array($this, 'wbe_inactive_plugin_notice'));
                }
            }
            //send booking emails cronjob
            add_action("send_booking_emails_cronjob", array($this, "send_booking_emails_cronjob"));
        }

        public function wbe_add_cfe_field_data($rowArray, $existingArray, $user, $order, $obj)
        {



            $shipping_fields = $this->get_cfe_custom_fields('shipping');
            $billing_fields = $this->get_cfe_custom_fields('billing');
            $additional_fields = $this->get_cfe_custom_fields('additional');

            if (!empty($shipping_fields) || !empty($billing_fields) || !empty($additional_fields)) {

                if (!empty($billing_fields)) {

                    foreach ($billing_fields as $field_key => $field_data) {

                        if (in_array($field_key, $existingArray)) {

                            $custom_value = get_post_meta($obj->order_id, $field_key, true);
                            if (!empty($custom_value)) {
                                if (is_array($custom_value)) {
                                    $rowArray[$field_key] = implode(',', (array)$custom_value);
                                } else {
                                    $rowArray[$field_key] = $custom_value;
                                }
                            } else {
                                $rowArray[$field_key] = esc_html__('N/A', 'wbe-exporter');
                            }
                        }
                    }
                }

                if (!empty($shipping_fields)) {

                    foreach ($shipping_fields as $field_key => $field_data) {

                        if (in_array($field_key, $existingArray)) {

                            $custom_value = get_post_meta($obj->order_id, $field_key, true);
                            if (!empty($custom_value)) {
                                if (is_array($custom_value)) {
                                    $rowArray[$field_key] = implode(',', (array)$custom_value);
                                } else {
                                    $rowArray[$field_key] = $custom_value;
                                }
                            } else {
                                $rowArray[$field_key] = esc_html__('N/A', 'wbe-exporter');
                            }
                        }
                    }
                }

                if (!empty($additional_fields)) {

                    foreach ($additional_fields as $field_key => $field_data) {

                        if (in_array($field_key, $existingArray)) {

                            $custom_value = get_post_meta($obj->order_id, $field_key, true);
                            if (!empty($custom_value)) {
                                if (is_array($custom_value)) {
                                    $rowArray[$field_key] = implode(',', (array)$custom_value);
                                } else {
                                    $rowArray[$field_key] = $custom_value;
                                }
                            } else {
                                $rowArray[$field_key] = esc_html__('N/A', 'wbe-exporter');
                            }
                        }
                    }
                }
            }


            return $rowArray;
        }

        public function wbe_add_cfe_field_labels($fields)
        {

            $shipping_fields = $this->get_cfe_custom_fields('shipping');
            $billing_fields = $this->get_cfe_custom_fields('billing');
            $additional_fields = $this->get_cfe_custom_fields('additional');

            if (!empty($shipping_fields) || !empty($billing_fields) || !empty($additional_fields)) {

                if (!empty($billing_fields)) {
                    foreach ($billing_fields as $field_key => $field_data) {
                        $fields[$field_key] = $field_data['label'];
                    }
                }

                if (!empty($shipping_fields)) {
                    foreach ($shipping_fields as $field_key => $field_data) {
                        $fields[$field_key] = $field_data['label'];
                    }
                }

                if (!empty($additional_fields)) {
                    foreach ($additional_fields as $field_key => $field_data) {
                        $fields[$field_key] = $field_data['label'];
                    }
                }
            }
            return $fields;
        }

        public function wbe_add_cfe_fields_in_export_tab($selected_template)
        {

            $shipping_fields = $this->get_cfe_custom_fields('shipping');
            $billing_fields = $this->get_cfe_custom_fields('billing');
            $additional_fields = $this->get_cfe_custom_fields('additional');

            if (!empty($shipping_fields) || !empty($billing_fields) || !empty($additional_fields)) {
?>
                <li class="exporter-data-heading"><?php esc_html_e('Checkout Field Editor Fields', 'wbe-exporter') ?>:</li>
                <?php
                if (!empty($billing_fields)) {
                ?> <li><b><?php _e('Billing Fields', 'wbe-exporter'); ?></b></li> <?php
                                                                                    foreach ($billing_fields as $field_key => $field_data) { ?>
                        <li><input type="checkbox" <?php echo (isset($selected_template[$field_key])) ? "checked" : ""; ?> name="<?php esc_attr_e($field_key); ?>" id="<?php esc_attr_e($field_key); ?>_checkbox" value="<?php esc_attr_e($field_data['label']); ?>"><label for="<?php esc_attr_e($field_key); ?>_checkbox" class="exporter-data-label"><?php echo esc_html($field_data['label']); ?></label></li>
                    <?php
                                                                                    }
                                                                                }
                                                                                if (!empty($shipping_fields)) {
                    ?> <li><b><?php _e('Shipping Fields', 'wbe-exporter'); ?></b></li> <?php
                                                                                        foreach ($shipping_fields as $field_key => $field_data) { ?>
                        <li><input type="checkbox" <?php echo (isset($selected_template[$field_key])) ? "checked" : ""; ?> name="<?php esc_attr_e($field_key); ?>" id="<?php esc_attr_e($field_key); ?>_checkbox" value="<?php esc_attr_e($field_data['label']); ?>"><label for="<?php esc_attr_e($field_key); ?>_checkbox" class="exporter-data-label"><?php echo esc_html($field_data['label']); ?></label></li>
                    <?php
                                                                                        }
                                                                                    }
                                                                                    if (!empty($additional_fields)) {
                    ?> <li><b><?php _e('Additional Fields', 'wbe-exporter'); ?></b></li> <?php
                                                                                            foreach ($additional_fields as $field_key => $field_data) { ?>
                        <li><input type="checkbox" <?php echo (isset($selected_template[$field_key])) ? "checked" : ""; ?> name="<?php esc_attr_e($field_key); ?>" id="<?php esc_attr_e($field_key); ?>_checkbox" value="<?php esc_attr_e($field_data['label']); ?>"><label for="<?php esc_attr_e($field_key); ?>_checkbox" class="exporter-data-label"><?php echo esc_html($field_data['label']); ?></label></li>
                <?php
                                                                                            }
                                                                                        }
                ?>
                </li>
            <?php
            }
        }

        public function get_cfe_custom_fields($tab = 'billing')
        {
            // 'billing', 'shipping', 'additional'

            if (!function_exists('woocommerce_init_checkout_field_editor')) {
                return array();
            }

            $core_fields = array_filter(get_option('wc_fields_' . $tab, array()));

            if ($tab != 'additional' && (empty($core_fields) || sizeof($core_fields) == 0)) {
                $core_fields =  WC()->countries->get_address_fields(WC()->countries->get_base_country(), $tab . '_');
            }

            $custom_fields = array();

            if (!empty($core_fields)) {

                if ($tab === 'billing' || $tab === 'shipping') {
                    foreach ($core_fields as $field_key => $field_data) {

                        if (!in_array($field_key, array(
                            'billing_first_name', 'billing_last_name', 'billing_company', 'billing_address_1', 'billing_address_2', 'billing_city', 'billing_state', 'billing_country', 'billing_postcode', 'billing_phone', 'billing_email',
                            'shipping_first_name', 'shipping_last_name', 'shipping_company', 'shipping_address_1', 'shipping_address_2', 'shipping_city', 'shipping_state', 'shipping_country', 'shipping_postcode', 'customer_note', 'order_comments'
                        ))) {
                            $custom_fields[$field_key] = $field_data;
                        }
                    }
                } else if ($tab === 'additional') {
                    $custom_fields = $core_fields;

                    unset($custom_fields['order_comments']);
                }
            }

            return $custom_fields;
        }
        public function wbe_enqueue_scripts()
        {
            $current_screen = get_current_screen();
            if ($current_screen->id == 'toplevel_page_booking-exporter') {
                // date picker assets
                add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_js_date_range_picker'));
                add_action('admin_footer', array($this, 'product_filter_javascript'));
            }
        }
        /**
         * monthely&weekly cronjob schedule
         * @param type $schedules
         * @return type
         */
        public function wbe_add_new_cron_schedules($schedules)
        {
            // add a 'weekly' interval
            $schedules['weekly'] = array(
                'interval' => 604800,
                'display' => esc_html__('Weekly', 'wbe-exporter')
            );
            // add a 'monthly' interval
            $schedules['monthly'] = array(
                'interval' => 2635200,
                'display' => esc_html__('monthly', 'wbe-exporter')
            );
            return $schedules;
        }
        /**         *
         * Send Booking Emails
         */
        public function send_booking_emails_cronjob()
        {
            if ($emails = get_option("wc_booking_cronjob_emails")) {
                $this->send_email_now($emails);
            }
        }

        public function send_email_with_template($template, $emails)
        {
            if (!$emails) {
                return;
            }
            $template_fields = get_option('booking_exporter_templates');
            $emails = explode(',', $emails);

            if (!in_array($template, array_keys($template_fields))) {
                return;
            }
            $filter_dates = get_option('wc_bookings_filter_date_schedule_email', true);
            $from_value = "";
            $to_value   = "";
            if ($filter_dates['schedule_email_date_filter'] == on) {
                $from_value = isset($filter_dates['from']) ? $filter_dates['from'] : "";
                $to_value = isset($filter_dates['now_on']) && $filter_dates['now_on'] == 1 ? "" : $filter_dates['from'];
            }

            $filter_time_period = get_option('wc_bookings_filter_time_period', true);

            if (!empty($filter_time_period) && ('day' == $filter_time_period || 'week' == $filter_time_period || 'month' == $filter_time_period)) {

                $last_day_date = strtotime("-1 days");
                $to = date("Y-m-d", $last_day_date);
                $to_value   = str_replace('-', '', $to) . '235959';
                if ('day' == $filter_time_period) {

                    $from = date("Y-m-d", $last_day_date);
                    $from_value =  str_replace('-', '', $from) . '000000';
                } else if ('week' == $filter_time_period) {
                    // get date-object one week back from yesterday
                    $one_week_back = strtotime('-1 week', $last_day_date);
                    $from = date("Y-m-d", $one_week_back);
                    $from_value =  str_replace('-', '', $from) . '000000';
                } else if ('month' == $filter_time_period) {
                    // get date-object one month back from yesterday
                    $one_month_back = strtotime('-1 month', $last_day_date);
                    $from = date("Y-m-d", $one_month_back);
                    $from_value =  str_replace('-', '', $from) . '000000';
                }
            }

            $template_array = $template_fields[$template];
            $booking_ids = $this->get_booking_ids_by_crietria($from_value, $to_date);
            $dataArray = $this->prepare_booking_rows_data($booking_ids, array(), array(), array_keys($template_array));
            $fieldArray = array_values(array_merge(array_flip(array_keys($template_array)), $template_array));
            $attachments = array();
            $subject = get_option("wc_booking_email_subject");
            $content = get_option("wc_booking_email_template");
            $sent = array();

            if (get_option('wc_booking_email_sent_attachment')) {
                $sent = json_decode(get_option('wc_booking_email_sent_attachment'));

                if (!empty($emails) && !empty($dataArray)) {
                    //create csv if exists
                    if (in_array('csv', $sent)) {
                        $filename = 'booking.csv';
                        $file = fopen($filename, "w");
                        fputcsv($file, $fieldArray);
                        foreach ($dataArray as $line) {
                            fputcsv($file, $line);
                        }
                        fclose($file);
                        ob_end_clean();
                        $attachments[] = $filename;
                    }
                    //create pdf if exists
                    if (in_array('pdf', $sent)) {
                        $upload_dir = wp_upload_dir();
                        $PdfName = $upload_dir["basedir"] . "/" . 'booking.pdf';
                        ob_start();
                        require_once(WBE_MODIFY_PLUGIN_PATH . "templates/pdf-template.php");
                        $file_content = ob_get_contents();
                        //clean buffer include mpdf
                        ob_end_clean();
                        include(WBE_MODIFY_PLUGIN_PATH . '/vendor/mpdf60/mpdf.php');
                        //create pdf and download
                        $mpdf = new mPDF('c');
                        $mpdf->WriteHTML($file_content);
                        $mpdf->AddPage();
                        $mpdf->Output($PdfName, 'F');
                        $attachments[] = $PdfName;
                    }
                    foreach ($emails as $email) {
                        if ($email) {
                            wp_mail($email, $subject, stripslashes($content), array('Content-Type: text/html; charset=UTF-8'), $attachments);
                        }
                    }
                    $_POST['send_email_now_success'] = 1;
                }
                foreach ($attachments as $attachment) {
                    unlink($attachment);
                }
            }
        }

        public function enqueue_admin_js_date_range_picker($hook)
        {
            // Enqueue Styles
            wp_enqueue_style('wbe-jquery-ui', plugins_url('../css/jquery-ui.min.css', __FILE__), array(), '1.7.0');
            wp_enqueue_style('wbe-jquery-ui-theme', plugins_url('../css/jquery-ui.theme.min.css', __FILE__), array(), '1.7.0');
            wp_enqueue_style('wbe-chosen', plugins_url('../css/chosen.min.css', __FILE__), array(), '1.7.0');
            wp_enqueue_style('wbe-plugin', plugins_url('../css/plugin.css', __FILE__), array(), '1.7.0');
            wp_enqueue_style('wbe-admin-enhanced', plugins_url('../css/wbe-admin-enhanced.css', __FILE__), array(), '1.7.0');
            
            // Enqueue Scripts
            wp_enqueue_script('wbe-jquery-ui', plugins_url('../js/jquery-ui.min.js', __FILE__), array('jquery'), '1.7.0');
            wp_enqueue_script('wbe-chosen', plugins_url('../js/chosen.jquery.min.js', __FILE__), array('jquery'), '1.7.0');
            wp_enqueue_script('plugin-daterangepicker', plugins_url('../js/plugin.js', __FILE__), array('jquery'), '1.7.0', true);

            // Localize script with security nonce
            wp_localize_script('plugin-daterangepicker', 'woo_bookings_export', array(
                'nonce' => wp_create_nonce('woO_eXp_db_Nonce'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'strings' => array(
                    'error' => esc_html__('Ocorreu um erro. Por favor, tente novamente.', 'wbe-exporter'),
                    'success' => esc_html__('Operação realizada com sucesso!', 'wbe-exporter'),
                )
            ));
        }
        public function booking_settings()
        {
            add_menu_page(esc_html__('Woocommerce Booking Exporter', 'wbe-exporter'), esc_html__('Booking Exporter', 'wbe-exporter'), 'administrator', 'booking-exporter', array($this, 'booking_exporter_function'), plugins_url('../img/icon.png', __FILE__));
        }
        public function booking_exporter_function()
        {
            include(WBE_MODIFY_PLUGIN_PATH . '/templates/admin-page.php');
        }
        public function wbe_download_csv()
        {
            // Security check: verify user has permission
            if (!current_user_can('manage_woocommerce')) {
                wp_die(esc_html__('Você não tem permissão para acessar esta página.', 'wbe-exporter'));
            }

            if (isset($_POST['action']) && $_POST['action'] == 'export') {
                // Security: verify nonce
                if (!isset($_POST['wbe_export_nonce']) || !wp_verify_nonce($_POST['wbe_export_nonce'], 'wbe_export_action')) {
                    wp_die(esc_html__('Erro de segurança. Por favor, recarregue a página e tente novamente.', 'wbe-exporter'));
                }
                $this->export_booking_to_cv_pdf();
            } elseif (isset($_POST['action']) && $_POST['action'] == 'booking-exporter-export-templates') {
                if (!isset($_POST['wbe_template_nonce']) || !wp_verify_nonce($_POST['wbe_template_nonce'], 'wbe_template_action')) {
                    wp_die(esc_html__('Erro de segurança. Por favor, recarregue a página e tente novamente.', 'wbe-exporter'));
                }
                $this->export_booking_templates();
            } elseif (isset($_POST['action']) && $_POST['action'] == 'booking-exporter-import-templates') {
                if (!isset($_POST['wbe_import_nonce']) || !wp_verify_nonce($_POST['wbe_import_nonce'], 'wbe_import_action')) {
                    wp_die(esc_html__('Erro de segurança. Por favor, recarregue a página e tente novamente.', 'wbe-exporter'));
                }
                $this->import_booking_templates();
            } elseif (isset($_POST['action']) && $_POST['action'] == 'send-email-now') {
                if (!isset($_POST['wbe_email_nonce']) || !wp_verify_nonce($_POST['wbe_email_nonce'], 'wbe_email_action')) {
                    wp_die(esc_html__('Erro de segurança. Por favor, recarregue a página e tente novamente.', 'wbe-exporter'));
                }
                if (isset($_POST['sent_email']) && $_POST['sent_email'])
                    $this->send_email_now($_POST['sent_email']);
            } elseif (isset($_POST['action']) && $_POST['action'] == 'save-cronjob-email') {
                if (!isset($_POST['wbe_cronjob_nonce']) || !wp_verify_nonce($_POST['wbe_cronjob_nonce'], 'wbe_cronjob_action')) {
                    wp_die(esc_html__('Erro de segurança. Por favor, recarregue a página e tente novamente.', 'wbe-exporter'));
                }
                $this->save_cronjob();
            }
        }
        /**
         * Save Cronjob
         */
        public function save_cronjob()
        {

            if (isset($_POST['cronjob_emails']))
                update_option("wc_booking_cronjob_emails", wc_clean($_POST['cronjob_emails']));

            if (isset($_POST['email_subject']))
                update_option("wc_booking_email_subject", wc_clean($_POST['email_subject']));

            if (isset($_POST['template-cron-email']))
                update_option("template-cron-email", wc_clean($_POST['template-cron-email']));

            if (isset($_POST['email_template']))
                update_option("wc_booking_email_template", wc_clean($_POST['email_template']));

            if (isset($_POST['cronjob-schedule'])) {
                wp_clear_scheduled_hook('send_booking_emails_cronjob');
                update_option("wc_booking_cronjob_schedule", wc_clean($_POST['cronjob-schedule']));
                if ($_POST['cronjob-schedule']) {
                    wp_schedule_event(current_time('timestamp'), wc_clean($_POST['cronjob-schedule']), 'send_booking_emails_cronjob');
                }
            }

            if (isset($_POST['cronjob-timeperiod']))
                update_option("wc_bookings_filter_time_period", wc_clean($_POST['cronjob-timeperiod']));

            if (isset($_POST['email_booking_from_date']) || isset($_POST['email_booking_to_date']) || isset($_POST['now_on'])) {
                if ($_POST['email_booking_from_date'] || $_POST['email_booking_to_date']) {
                    $filter = 'on';
                } else {
                    $filter = 'off';
                }
                $now_on = isset($_POST['now_on']) ? true : false;
                $from_date = sanitize_text_field($_POST['email_booking_from_date']);
                $to_date = isset($_POST['email_booking_to_date']) ? sanitize_text_field($_POST['email_booking_to_date']) : date("Y-m-d");
                $filter_date = array(
                    'schedule_email_date_filter' => $filter,
                    'from'          => str_replace('-', '', $from_date) . '000000',
                    'from_value'    => $from_date,
                    'to'            => str_replace('-', '', $to_date) . '235959',
                    'to_value'      => $to_date,
                    'now_on'        => $now_on,
                );
                update_option('wc_bookings_filter_date_schedule_email', wc_clean($filter_date));
            }
            if (isset($_POST['sent_attachment'])) :
                update_option("wc_booking_email_sent_attachment", json_encode($_POST['sent_attachment']));
            else :
                update_option("wc_booking_email_sent_attachment", "");
            endif;
            $_POST['save_schedule_success'] = 1;
        }
        /**
         * Send Emails Now
         */
        public function send_email_now($emails = '')
        {
            if (!$emails)
                return;
            $emails = explode(',', $emails);

            $template_option = get_option("template-cron-email");
            $fields = '';
            if ('all-fields' == $template_option || empty($template_option)) {
                $fields = $this->get_fields();
            } else {
                $template_fields = get_option('booking_exporter_templates');
                if (!in_array($template_option, array_keys($template_fields))) {
                    return;
                }
                $fields = $template_fields[$template_option];
            }

            $filter_dates = get_option('wc_bookings_filter_date_schedule_email', true);
            $from_value = '';
            $to_value   = '';
            if ($filter_dates['schedule_email_date_filter'] == on) {
                $from_value = isset($filter_dates['from']) ? $filter_dates['from'] : "";
                $to_value = isset($filter_dates['now_on']) && $filter_dates['now_on'] == 1 ? "" : $filter_dates['from'];
            }

            $filter_time_period = get_option('wc_bookings_filter_time_period', true);

            if (!empty($filter_time_period) && ('day' == $filter_time_period || 'week' == $filter_time_period || 'month' == $filter_time_period)) {

                $last_day_date = strtotime("-1 days");
                $to = date("Y-m-d", $last_day_date);
                $to_value   = str_replace('-', '', $to) . '235959';
                if ('day' == $filter_time_period) {

                    $from = date("Y-m-d", $last_day_date);
                    $from_value =  str_replace('-', '', $from) . '000000';
                } else if ('week' == $filter_time_period) {
                    // get date-object one week back from yesterday
                    $one_week_back = strtotime('-1 week', $last_day_date);
                    $from = date("Y-m-d", $one_week_back);
                    $from_value =  str_replace('-', '', $from) . '000000';
                } else if ('month' == $filter_time_period) {
                    // get date-object one month back from yesterday
                    $one_month_back = strtotime('-1 month', $last_day_date);
                    $from = date("Y-m-d", $one_month_back);
                    $from_value =  str_replace('-', '', $from) . '000000';
                }
            }

            $booking_ids = $this->get_booking_ids_by_crietria($from_value, $to_value);
            //Prepare Row Data
            $dataArray = $this->prepare_booking_rows_data($booking_ids, array(), array(), array_keys($fields));
            //Fields names row by column order
            $fieldArray = array_values(array_merge(array_flip(array_keys($fields)), $fields));
            $attachments = array();
            $subject = get_option("wc_booking_email_subject");
            $content = get_option("wc_booking_email_template");
            $sent = array();
            if (get_option('wc_booking_email_sent_attachment'))
                $sent = json_decode(get_option('wc_booking_email_sent_attachment'));

            if (!empty($emails) && !empty($dataArray)) {
                //create csv if exists
                if (in_array('csv', $sent)) {
                    $filename = 'booking.csv';
                    $file = fopen($filename, "w");
                    fputcsv($file, $fieldArray);
                    foreach ($dataArray as $line) {
                        fputcsv($file, $line);
                    }
                    fclose($file);
                    ob_end_clean();
                    $attachments[] = $filename;
                }
                //create pdf if exists
                if (in_array('pdf', $sent)) {
                    $upload_dir = wp_upload_dir();
                    $PdfName = $upload_dir["basedir"] . "/" . 'booking.pdf';
                    ob_start();
                    require_once(WBE_MODIFY_PLUGIN_PATH . "templates/pdf-template.php");
                    $file_content = ob_get_contents();
                    //clean buffer include mpdf
                    ob_end_clean();
                    include(WBE_MODIFY_PLUGIN_PATH . '/vendor/mpdf60/mpdf.php');
                    //create pdf and download
                    $mpdf = new mPDF('c');
                    $mpdf->WriteHTML($file_content);
                    $mpdf->AddPage();
                    $mpdf->Output($PdfName, 'F');
                    $attachments[] = $PdfName;
                }
                foreach ($emails as $email) {
                    if ($email) {
                        wp_mail($email, $subject, stripslashes($content), array('Content-Type: text/html; charset=UTF-8'), $attachments);
                    }
                }
                $_POST['send_email_now_success'] = 1;
            }
            foreach ($attachments as $attachment) {
                unlink($attachment);
            }
        }
        /**
         * Get Fields Array
         * @return type
         */
        public function get_fields()
        {
            $vendor_array = $addon_array = array();
            $fields = array(
                "order_id" => esc_html__('ID do Pedido', 'wbe-exporter'),
                "order_status" => esc_html__('Status', 'wbe-exporter'),
                "order_note" => esc_html__('Nota do Cliente', 'wbe-exporter'),
                "customer_provided_note" => esc_html__('Nota Fornecida pelo Cliente', 'wbe-exporter'),
                "order_sub_totals" => esc_html__('Subtotais do Pedido', 'wbe-exporter'),
                "order_totals" => esc_html__('Totais do Pedido', 'wbe-exporter'),
                "coupons"   =>  esc_html__('Cupons', 'wbe-exporter'),
                "billing_first_name" => esc_html__('Primeiro Nome de Cobrança', 'wbe-exporter'),
                "billing_second_name" => esc_html__('Último Nome de Cobrança', 'wbe-exporter'),
                "billing_company_name" => esc_html__('Nome da Empresa de Cobrança', 'wbe-exporter'),
                "billing_address_1" => esc_html__('Endereço de Cobrança 1', 'wbe-exporter'),
                "billing_address_2" => esc_html__('Endereço de Cobrança 2', 'wbe-exporter'),
                "billing_phone" => esc_html__('Telefone de Cobrança', 'wbe-exporter'),
                "billing_zip" => esc_html__('CEP de Cobrança', 'wbe-exporter'),
                "billing_city" => esc_html__('Cidade de Cobrança', 'wbe-exporter'),
                "billing_state" => esc_html__('Estado de Cobrança', 'wbe-exporter'),
                "billing_country" => esc_html__('País de Cobrança', 'wbe-exporter'),
                "shipping_first_name" => esc_html__('Primeiro Nome de Envio', 'wbe-exporter'),
                "shipping_second_name" => esc_html__('Último Nome de Envio', 'wbe-exporter'),
                "shipping_company_name" => esc_html__('Nome da Empresa de Envio', 'wbe-exporter'),
                "shipping_address_1" => esc_html__('Endereço de Envio 1', 'wbe-exporter'),
                "shipping_address_2" => esc_html__('Endereço de Envio 2', 'wbe-exporter'),
                "shipping_phone" => esc_html__('Telefone de Envio', 'wbe-exporter'),
                "shipping_zip" => esc_html__('CEP de Envio', 'wbe-exporter'),
                "shipping_city" => esc_html__('Cidade de Envio', 'wbe-exporter'),
                "shipping_state" => esc_html__('Estado de Envio', 'wbe-exporter'),
                "shipping_country" => esc_html__('País de Envio', 'wbe-exporter'),
                "shipping_cost" => esc_html__('Custo de Envio', 'wbe-exporter'),
                "product_id" => esc_html__('ID do Produto', 'wbe-exporter'),
                "product_name" => esc_html__('Nome do Produto', 'wbe-exporter'),
                "product_sku" => esc_html__('SKU do Produto', 'wbe-exporter'),
                "product_res" => esc_html__('Recursos do Produto', 'wbe-exporter'),
                "payment_method"            => esc_html__('Método de Pagamento', 'wbe-exporter'),
                "payment_method_title"      => esc_html__('Título do Método de Pagamento', 'wbe-exporter'),
                "completed_date"            => esc_html__('Data de Pagamento do Pedido', 'wbe-exporter')
            );
            if (in_array('woocommerce-product-addons/woocommerce-product-addons.php', apply_filters('active_plugins', get_option('active_plugins'))))
                $addon_array = array("product_addon" => esc_html__('Detalhes Adicionais', 'wbe-exporter'));
            $fields = array_merge($fields, $addon_array);
            if (in_array('woocommerce-product-vendors/woocommerce-product-vendors.php', apply_filters('active_plugins', get_option('active_plugins'))))
                $vendor_array = array("product_vendor" => esc_html__('Product Vendor', 'wbe-exporter'));
            $fields = array_merge($fields, $vendor_array);
            $booking_fields = array(
                "booking_id" => esc_html__('ID da Reserva', 'wbe-exporter'),
                "order_person" => esc_html__('Informações da Pessoa', 'wbe-exporter'),
                "booking_start_date" => esc_html__('Data de Início da Reserva', 'wbe-exporter'),
                "booking_end_date" => esc_html__('Data de Término da Reserva', 'wbe-exporter'),
            );
            $fields = array_merge($fields, $booking_fields);
            $user_fields = array(
                "user_id" => esc_html__('User ID', 'wbe-exporter'),
                "user_email" => esc_html__('Email', 'wbe-exporter'),
                "user_username" => esc_html__('Username', 'wbe-exporter'),
                "user_roles" => esc_html__('User Roles', 'wbe-exporter'),
            );
            $fields = array_merge($fields, $user_fields);

            $fields = apply_filters('wbe_add_custom_field_labels', $fields);

            return $fields;
        }
        /**
         * Import Booking Templates From json in txt file   
         */
        public function import_booking_templates()
        {
            $saved_templates = array(); //get tsaved templates
            if (get_option("booking_exporter_templates") && !empty(get_option("booking_exporter_templates")))
                $saved_templates = get_option("booking_exporter_templates");
            if (isset($_FILES["imported-file"])) {

                $json_str = file_get_contents($_FILES["imported-file"]["tmp_name"]);
                $json_str = json_decode($json_str, true);

                if ($json_str == null) {
                    $_POST['import_booking_templates'] = esc_html__('Failed to read file content', 'wbe-exporter');
                } else {
                    foreach ($json_str as $template_name => $template) {
                        $saved_templates[$template_name] = $template;
                    }
                    update_option("booking_exporter_templates", $saved_templates);
                    $_POST['import_booking_templates'] = 1;
                }
            } else {
                $_POST['import_booking_templates'] = esc_html__('Please upload import file.', 'wbe-exporter');
            }
        }
        /**
         * Export Booking Templates to json in txt file 
         */
        public function export_booking_templates()
        {
            $exported_array = $saved_templates = array();
            if (get_option("booking_exporter_templates") && !empty(get_option("booking_exporter_templates")))
                $saved_templates = get_option("booking_exporter_templates");

            foreach ($_POST as $choosen_template => $value) {
                if (isset($saved_templates[$choosen_template])) {
                    $exported_array[$choosen_template] = $saved_templates[$choosen_template];
                }
            }
            if ($exported_array) {
                ini_set('memory_limit', '512M'); // or you could use 1G
                ini_set('max_execution_time', 200);
                header("Content-Type: text/html");
                header("Content-Disposition: attachment; filename=booking-templates.txt");
                $filename = 'test.csv';
                $file = fopen($filename, "w");
                fwrite($file, json_encode($exported_array));
                fclose($file);
                readfile($filename);
                unlink($filename);
                die();
            }
        }
        /**
         * Export Booking to cv or pdf 
         */

        /* public function export_booking_to_cv_pdf() {
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
                $file_type = sanitize_text_field($_POST['file_type']);
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
            $prodArr = $_POST['booking_exporter_product'];
            $userArr = $_POST['booking_exporter_user'];
            //get from date
            if ($_POST['booking_from_date']) {
                $from = str_replace('-', '', sanitize_text_field($_POST['booking_from_date'])) . '000000';
            }
            //get to date
            if ($_POST['booking_to_date']) {
                $to = str_replace('-', '', sanitize_text_field($_POST['booking_to_date'])) . '235959';
            }
            //Get booking ids by date from/to if exists
            $booking_ids = $this->get_booking_ids_by_crietria($from, $to);
            //Prepare Row Data
            $dataArray = $this->prepare_booking_rows_data($booking_ids, $prodArr, $userArr, $existingArray);
            //Fields names row by column order
            $fieldArray = array_values(array_merge(array_flip($existingArray), $fieldArray));
            /////////////////////////Create File///////////////////////////////////
            if (count($dataArray) == 0) { //if no rows Redirect again to this page
                wp_redirect(admin_url() . '?page=booking-exporter&booking=no');
                exit;
            } elseif ($file_type == "csv") { //if wanted file was csv
                ini_set('memory_limit', '512M'); // or you could use 1G
                ini_set('max_execution_time', 200);
                header("Content-Type: application/csv");
                header("Content-Disposition: attachment; filename=booking.csv");
                $filename = 'test.csv';
                $file = fopen($filename, "w");
                fputcsv($file, $fieldArray, $delimiter);
                foreach ($dataArray as $line) {
                    fputcsv($file, $line, $delimiter);
                }
                fclose($file);
                // send file to browser
                readfile($filename);
                unlink($filename);
                die();
            } elseif ($file_type == "pdf") {//if wanted file was pdf
                //get content of pdf
                ob_start();
                $dataArrays = array_chunk($dataArray,200);
                require_once(WBE_MODIFY_PLUGIN_PATH . "templates/pdf-template.php");
                $file_content = ob_get_contents();
                //clean buffer include mpdf
                ob_end_clean();
                
                //create pdf and download
               
                
                $mpdf->AddPage();
                $mpdf->Output('booking.pdf', 'D');
                exit;
            }
        } */

        //Custom Export Booking to cv or pdf - Início
        
        /**
         * Process and expand custom fields (Responsável pela Reserva and Detalhes Adicionais)
         * @param array $dataArray Data rows
         * @param array $fieldArray Field headers
         * @return array Processed data with expanded fields
         */
        private function process_custom_fields($dataArray, &$fieldArray)
        {
            $reservaIndex = array_search('Responsável pela Reserva', $fieldArray);
            $detalhesIndex = array_search('Detalhes Adicionais', $fieldArray);
            
            $newRows = [];
            $allNewHeaders = [];
            
            // Check if custom fields exist
            if ($reservaIndex !== false && $detalhesIndex !== false) {
                foreach ($dataArray as $row) {
                    $reservaData = explode(' - ', $row[$reservaIndex]);
                    $detalhesData = explode('-', $row[$detalhesIndex]);
                    
                    $mergedData = array_merge($reservaData, $detalhesData);
                    $dataAssociative = [];
                    
                    foreach ($mergedData as $data) {
                        $parts = explode(':', $data, 2);
                        
                        // Verify we have both: key and value
                        if (count($parts) === 2) {
                            list($key, $value) = $parts;
                            $columnName = trim($key);
                            $dataAssociative[$columnName] = trim($value);
                            
                            if (!in_array($columnName, $allNewHeaders)) {
                                $allNewHeaders[] = $columnName;
                            }
                        }
                    }
                    
                    $newData = [];
                    foreach ($allNewHeaders as $headerName) {
                        $newData[] = $dataAssociative[$headerName] ?? '';
                    }
                    
                    // Remove old values and insert new ones
                    unset($row[$reservaIndex], $row[$detalhesIndex]);
                    $row = array_merge($row, $newData);
                    
                    $newRows[] = $row;
                }
                
                // Update headers
                $fieldArray = array_diff($fieldArray, ['Responsável pela Reserva', 'Detalhes Adicionais']);
                $fieldArray = array_merge($fieldArray, $allNewHeaders);
            } else {
                // If fields don't exist, use data as is
                $newRows = $dataArray;
            }
            
            return $newRows;
        }

        public function export_booking_to_cv_pdf()
        {
            //Add Delimiter
            $delimiter = ",";
            if (isset($_POST['delimiter']) && $_POST['delimiter']) {
                $delimiter = sanitize_text_field(trim($_POST['delimiter']));
                // Validate delimiter (must be single character)
                if (strlen($delimiter) != 1) {
                    $delimiter = ",";
                }
            }
            
            //coloums order
            $fieldArray = $existingArray = array();
            if (isset($_POST['coloums_order'])) {
                $json_data = stripslashes($_POST['coloums_order']);
                $existingArray = json_decode($json_data);
                
                // Validate JSON decode
                if (json_last_error() !== JSON_ERROR_NONE) {
                    wp_die(esc_html__('Erro ao processar os campos selecionados. Por favor, tente novamente.', 'wbe-exporter'));
                }
            }

            //get file wanted type
            $file_type = "csv";
            if (isset($_POST['file_type'])) {
                $file_type = sanitize_text_field($_POST['file_type']);
                
                // Validate file type
                $allowed_types = array('csv', 'excel', 'pdf');
                if (!in_array($file_type, $allowed_types)) {
                    $file_type = "csv";
                }
            }
            
            // DEBUG: Adicionar aviso no topo da página para testar
            if ($file_type == "excel") {
                error_log('WBE: Excel export detected!');
                // Forçar headers de debug
                add_action('admin_notices', function() {
                    echo '<div class="notice notice-info"><p>DEBUG: Exportando como EXCEL (file_type: excel)</p></div>';
                });
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
            $prodArr = $_POST['booking_exporter_product'];
            $userArr = $_POST['booking_exporter_user'];
            //get from date
            if ($_POST['booking_from_date']) {
                $from = str_replace('-', '', sanitize_text_field($_POST['booking_from_date'])) . '000000';
            }
            //get to date
            if ($_POST['booking_to_date']) {
                $to = str_replace('-', '', sanitize_text_field($_POST['booking_to_date'])) . '235959';
            }
            //Get booking ids by date from/to if exists
            $booking_ids = $this->get_booking_ids_by_crietria($from, $to);
            //Prepare Row Data
            $dataArray = $this->prepare_booking_rows_data($booking_ids, $prodArr, $userArr, $existingArray);
            //Fields names row by column order
            $fieldArray = array_values(array_merge(array_flip($existingArray), $fieldArray));

            /////////////////////////Create File///////////////////////////////////
            if (count($dataArray) == 0) { //if no rows Redirect again to this page
                wp_redirect(admin_url() . '?page=booking-exporter&booking=no');
                exit;
            } elseif ($file_type == "csv") { //if wanted file was csv
                
                ini_set('memory_limit', '512M');
                ini_set('max_execution_time', 200);
                
                // Process custom fields
                $newRows = $this->process_custom_fields($dataArray, $fieldArray);
                
                // Create CSV file
                $filename = 'booking-' . date('Y-m-d-His') . '.csv';
                $file = fopen($filename, "w");
                
                // Add UTF-8 BOM for Excel compatibility
                fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
                
                fputcsv($file, $fieldArray, $delimiter);
                foreach ($newRows as $line) {
                    fputcsv($file, $line, $delimiter);
                }
                fclose($file);
                
                // Send file to browser
                header("Content-Type: text/csv; charset=utf-8");
                header("Content-Disposition: attachment; filename=booking.csv");
                header("Content-Length: " . filesize($filename));
                readfile($filename);
                unlink($filename);
                die();
            } elseif ($file_type == "excel") { //if wanted file was excel
                
                ini_set('memory_limit', '512M');
                ini_set('max_execution_time', 200);
                
                // Process custom fields
                $newRows = $this->process_custom_fields($dataArray, $fieldArray);
                
                // Include Excel library
                require_once(WBE_MODIFY_PLUGIN_PATH . '/vendor/SimpleXLSXGen.php');
                
                // Prepare data for Excel
                $excelData = array();
                $excelData[] = array_values($fieldArray); // Header row
                
                foreach ($newRows as $line) {
                    $excelData[] = array_values($line);
                }
                
                // Create Excel file
                $filename = 'booking-' . date('Y-m-d-His') . '.xlsx';
                $xlsx = SimpleXLSXGen::fromArray($excelData);
                $xlsx->saveToFile($filename);
                
                // Download file
                header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
                header('Content-Disposition: attachment; filename="booking.xlsx"');
                header('Content-Length: ' . filesize($filename));
                header('Cache-Control: max-age=0');
                readfile($filename);
                unlink($filename);
                die();
            } elseif ($file_type == "pdf") {
                //get content of pdf
                ob_start();
                $dataArrays = array_chunk($dataArray, 200);
                require_once(WBE_MODIFY_PLUGIN_PATH . "templates/pdf-template.php");
                $file_content = ob_get_contents();
                //clean buffer include mpdf
                ob_end_clean();

                //create pdf and download
                $mpdf->AddPage();
                $mpdf->Output('booking.pdf', 'D');
                exit;
            }
        }

        //Custom Export Booking to cv or pdf - Fim

        public function product_filter_callback()
        {
            $catIds = $_POST['catIds'];
            $data = '';
            if (in_array('all', $catIds) || $catIds == null) {
                $data .= '<option value="all" selected>All</option>';
                $products = get_posts(array(
                    'post_type' => 'product',
                    'numberposts' => -1,
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_type',
                            'field' => 'slug',
                            'terms' => 'booking',
                        )
                    ),
                ));
                foreach ($products as $product) {
                    $data .= '<option value="' . esc_attr($product->ID) . '">' . esc_html($product->post_title) . '</option>';
                }
            } else {
                $counter = 0;
                $products = get_posts(array(
                    'post_type' => 'product',
                    'numberposts' => -1,
                    'tax_query' => array(
                        'relation' => 'AND',
                        array(
                            'taxonomy' => 'product_cat',
                            'field' => 'id',
                            'terms' => $catIds,
                        ),
                        array(
                            'taxonomy' => 'product_type',
                            'field' => 'slug',
                            'terms' => 'booking',
                        )
                    ),
                ));
                foreach ($products as $product) {
                    if ($counter == 0) {
                        $data .= '<option value="' . esc_attr($product->ID) . '" selected >' . esc_html($product->post_title) . '</option>';
                    } else {
                        $data .= '<option value="' . esc_attr($product->ID) . '">' . esc_html($product->post_title) . '</option>';
                    }
                    $counter++;
                }
            }
            echo $data;
            wp_die(); // this is required to terminate immediately and return a proper response
        }
        /**
         * Get Booking ids according to date from/to if exists
         */
        public function get_booking_ids_by_crietria($from = "", $to = "")
        {
            $arr = array();
            global $wpdb;
            $postsT = $wpdb->prefix . 'posts';
            $postmetaT = $wpdb->prefix . 'postmeta';
            $query = "
                    SELECT p.ID
                    FROM $postsT p
                    JOIN $postmetaT pm ON p.ID = pm.post_id";
            if ($from || $to) {
                $query .= " JOIN $postmetaT pm2 ON p.ID = pm2.post_id";
            }

            $query .= " WHERE p.post_type = 'wc_booking'
                    AND pm.meta_key = '_booking_order_item_id'
                    AND pm.meta_value != ''
                ";
            if ($from && $to) {
                $query .= " AND pm2.meta_key = '_booking_start' AND pm2.meta_value != '' AND pm2.meta_value BETWEEN $from AND $to";
            } elseif ($from) {
                $query .= " AND pm2.meta_key = '_booking_start' AND pm2.meta_value != '' AND pm2.meta_value >= $from";
            } elseif ($to) {
                $query .= " AND pm2.meta_key = '_booking_start' AND pm2.meta_value != '' AND pm2.meta_value <= $to";
            }
            $bookings = $wpdb->get_results($query);
            foreach ($bookings as $booking) {
                if (isset($_POST['booking_exporter_vendors']) && !in_array('all', $_POST['booking_exporter_vendors'])) {
                    $bookingObj = get_wc_booking($booking->ID);
                    if (version_compare(WC_VERSION, '3.0.0', '<')) {
                        $terms = wp_get_post_terms($bookingObj->product_id, 'shop_vendor');
                    } else {
                        $terms = wp_get_post_terms($bookingObj->product_id, 'wcpv_product_vendors');
                    }
                    if (count($terms) > 0) {
                        $flag = false;
                        foreach ($terms as $vendor) {
                            if (in_array($vendor->slug, $_POST['booking_exporter_vendors'])) {
                                $flag = true;
                            }
                        }
                        if ($flag) {
                            $arr[] = $booking->ID;
                        }
                    }
                } else {
                    $arr[] = $booking->ID;
                }
            }
            return $arr;
        }
        /**
         * Prepare Rows Data
         */
        public function prepare_booking_rows_data($booking_ids, $prodArr, $userArr, $existingArray)
        {

            $dataArray = array();
            $arrchuck = array_chunk($booking_ids, 999);

            foreach ($arrchuck as $booking_idsbatch) {
                foreach ($booking_idsbatch as $id) {
                    $rowArray = array();
                    $obj = get_wc_booking($id);
                    $order = wc_get_order($obj->order_id);
                    $currency = get_woocommerce_currency_symbol();
                    $productgeting_withouterror = wc_get_product($obj->product_id);
                    if (!empty($productgeting_withouterror)) {
                        $product = new WC_Product($obj->product_id);
                    }
                    $wc_booking_resource_label = get_post_meta($obj->product_id, 'wc_booking_resource_label', true);
                    $checkresource = get_post_meta($id, '_booking_resource_id', true);
                    if ($checkresource != 0) {
                        $resources_get_the_title = $wc_booking_resource_label . ': ' . get_the_title($checkresource);
                    } else {
                        $resources_get_the_title = esc_html__('N/A', 'wbe-exporter');
                    }
                    $user = new \stdClass();
                    if (!empty($order) and !empty($product)) {
                        if ($obj->customer_id != 0) {
                            $user = get_userdata($obj->customer_id);
                        } else {
                            if (version_compare(WC_VERSION, '3.0.0', '<')) {
                                $user->user_login = esc_html__('N/A', 'wbe-exporter');
                                $user->ID = esc_html__('N/A', 'wbe-exporter');
                                $user->user_email = get_post_meta($obj->order_id, '_billing_email', true);
                            } else {
                                if (!empty($order->get_billing_email())) {
                                    $user->user_login = esc_html__('N/A', 'wbe-exporter');
                                    $user->ID = esc_html__('N/A', 'wbe-exporter');
                                    $user->user_email = $order->get_billing_email();
                                } else {
                                    $user->user_email = esc_html__('N/A', 'wbe-exporter');
                                    $user->user_login = esc_html__('N/A', 'wbe-exporter');
                                    $user->ID = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                        }
                        // Create records
                        if ((in_array('all', $prodArr) || in_array($obj->product_id, $prodArr) || $prodArr == null) && (in_array('all', $userArr) || in_array($obj->customer_id, $userArr) || $userArr == null)) {
                            $rowArray = array();
                            if (in_array('order_id', $existingArray)) {
                                $rowArray['order_id'] = $obj->order_id;
                            }
                            if (in_array('order_status', $existingArray)) {
                                $rowArray['order_status'] = $order->get_status();
                            }
                            if (in_array('order_person', $existingArray)) {
                                $args = array(
                                    'posts_per_page' => -1,
                                    'post_type' => 'bookable_person',
                                    'post_parent' => version_compare(WC_VERSION, '3.0.0', '<') ? $product->id : $product->get_id(),
                                );
                                $personsTypes = get_posts($args);
                                $personsInfo = get_post_meta($id, '_booking_persons', true);
                                if (count($personsTypes) > 0 or count($personsInfo) > 0) {
                                    $infoArr = array();
                                    $personsInfo = get_post_meta($id, '_booking_persons', true);
                                    foreach ($personsInfo as $key => $value) {
                                        if ($key != 0) {
                                            $infoArr[] = get_the_title($key) . ': ' . $value;
                                        } else {
                                            $sing = null;
                                            if ($value > 1) {
                                                $sing = 's';
                                            }
                                            $infoArr[] = 'Person' . $sing . ' : ' . $value;
                                        }
                                    }
                                    $rowArray['order_person'] = implode(' - ', $infoArr);
                                } else {
                                    $rowArray['order_person'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('order_note', $existingArray)) {
                                $customer_note = esc_html__('N/A', 'wbe-exporter');
                                $notes = $order->get_customer_order_notes();
                                foreach ($notes as $note) {
                                    $customer_note = str_replace(',', '', $note->comment_content);
                                }
                                $rowArray['order_note'] = $customer_note;
                            }


                            if (in_array('customer_provided_note', $existingArray)) {
                                if ($order->get_customer_note()) {
                                    $rowArray['customer_provided_note'] = $order->get_customer_note();
                                } else {
                                    $rowArray['customer_provided_note'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('billing_company_name', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_billing_company', true)) {
                                    $rowArray['billing_company_name'] = get_post_meta($obj->order_id, '_billing_company', true);
                                } else {
                                    $rowArray['billing_company_name'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('shipping_company_name', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_shipping_company', true)) {
                                    $rowArray['shipping_company_name'] = get_post_meta($obj->order_id, '_shipping_company', true);
                                } else {
                                    $rowArray['shipping_company_name'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('order_sub_totals', $existingArray)) {
                                $rowArray['order_sub_totals'] = $order->get_subtotal();
                            }
                            if (in_array('order_totals', $existingArray)) {
                                $rowArray['order_totals'] = $order->get_total();
                            }
                            if (in_array('coupons', $existingArray)) {
                                $coupons  = $order->get_coupon_codes();
                                $coupons  = count($coupons) > 0 ? implode(',', $coupons) : '';
                                $discount = $order->get_total_discount();
                                if (!empty($coupons) && !empty($discount)) {
                                    $coupons = array($coupons . ' (' . $discount . ')');
                                }
                                if (!empty($coupons))
                                    $rowArray['coupons'] = implode(',', $coupons);
                                else
                                    $rowArray['coupons'] = esc_html__('N/A', 'wbe-exporter');
                            }


                            if (in_array('payment_method', $existingArray)) {
                                $rowArray['payment_method'] = get_post_meta($obj->order_id, '_payment_method', true);
                            }

                            if (in_array('payment_method_title', $existingArray)) {
                                $rowArray['payment_method_title'] = get_post_meta($obj->order_id, '_payment_method_title', true);
                            }

                            if (in_array('completed_date', $existingArray)) {
                                $rowArray['completed_date'] = get_post_meta($obj->order_id, '_completed_date', true);
                            }

                            if (in_array('billing_first_name', $existingArray)) {
                                $rowArray['billing_first_name'] = get_post_meta($obj->order_id, '_billing_first_name', true);
                            }
                            if (in_array('billing_second_name', $existingArray)) {
                                $rowArray['billing_second_name'] = get_post_meta($obj->order_id, '_billing_last_name', true);
                            }
                            if (in_array('billing_address_1', $existingArray)) {
                                $rowArray['billing_address_1'] = get_post_meta($obj->order_id, '_billing_address_1', true);
                            }
                            if (in_array('billing_address_2', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_billing_address_2', true)) {
                                    $rowArray['billing_address_2'] = get_post_meta($obj->order_id, '_billing_address_2', true);
                                } else {
                                    $rowArray['billing_address_2'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('billing_phone', $existingArray)) {
                                $rowArray['billing_phone'] = get_post_meta($obj->order_id, '_billing_phone', true);
                            }
                            if (in_array('billing_zip', $existingArray)) {
                                $rowArray['billing_zip'] = get_post_meta($obj->order_id, '_billing_postcode', true);
                            }
                            if (in_array('billing_city', $existingArray)) {
                                $rowArray['billing_city'] = get_post_meta($obj->order_id, '_billing_city', true);
                            }
                            if (in_array('billing_state', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_billing_state', true)) {
                                    $rowArray['billing_state'] = get_post_meta($obj->order_id, '_billing_state', true);
                                } else {
                                    $rowArray['billing_state'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('billing_country', $existingArray)) {
                                $rowArray['billing_country'] = get_post_meta($obj->order_id, '_billing_country', true);
                            }
                            if (in_array('shipping_first_name', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_shipping_first_name', true)) {
                                    $rowArray['shipping_first_name'] = get_post_meta($obj->order_id, '_shipping_first_name', true);
                                } else {
                                    $rowArray['shipping_first_name'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('shipping_second_name', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_shipping_last_name', true)) {
                                    $rowArray['shipping_second_name'] = get_post_meta($obj->order_id, '_shipping_last_name', true);
                                } else {
                                    $rowArray['shipping_second_name'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('shipping_address_1', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_shipping_address_1', true)) {
                                    $rowArray['shipping_address_1'] = get_post_meta($obj->order_id, '_shipping_address_1', true);
                                } else {
                                    $rowArray['shipping_address_1'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('shipping_address_2', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_shipping_address_2', true)) {
                                    $rowArray['shipping_address_2'] = get_post_meta($obj->order_id, '_shipping_address_2', true);
                                } else {
                                    $rowArray['shipping_address_2'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('shipping_phone', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_shipping_phone', true)) {
                                    $rowArray['shipping_phone'] = get_post_meta($obj->order_id, '_shipping_phone', true);
                                } else {
                                    $rowArray['shipping_phone'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('shipping_zip', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_shipping_postcode', true)) {
                                    $rowArray['shipping_zip'] = get_post_meta($obj->order_id, '_shipping_postcode', true);
                                } else {
                                    $rowArray['shipping_zip'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('shipping_city', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_shipping_city', true)) {
                                    $rowArray['shipping_city'] = get_post_meta($obj->order_id, '_shipping_city', true);
                                } else {
                                    $rowArray['shipping_city'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('shipping_state', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_shipping_state', true)) {
                                    $rowArray['shipping_state'] = get_post_meta($obj->order_id, '_shipping_state', true);
                                } else {
                                    $rowArray['shipping_state'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('shipping_country', $existingArray)) {
                                if (get_post_meta($obj->order_id, '_shipping_country', true)) {
                                    $rowArray['shipping_country'] = get_post_meta($obj->order_id, '_shipping_country', true);
                                } else {
                                    $rowArray['shipping_country'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('shipping_cost', $existingArray)) {
                                if (!empty($order->get_total_shipping()) && (!empty($order->get_total_shipping()) && $order->get_total_shipping() != 0)) {
                                    $rowArray['shipping_cost'] = $order->get_total_shipping();
                                } else {
                                    $rowArray['shipping_cost'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('product_id', $existingArray)) {
                                $rowArray['product_id'] = version_compare(WC_VERSION, '3.0.0', '<') ? $product->id : $product->get_id();
                            }
                            if (in_array('product_name', $existingArray)) {
                                $rowArray['product_name'] = $product->get_title();
                            }
                            if (in_array('product_sku', $existingArray)) {
                                if ($product->get_sku()) {
                                    $rowArray['product_sku'] = $product->get_sku();
                                } else {
                                    $rowArray['product_sku'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('product_res', $existingArray)) {
                                $rowArray['product_res'] = $resources_get_the_title;
                            }
                            if (in_array('product_addon', $existingArray)) {
                                $addonsArr = get_post_meta(version_compare(WC_VERSION, '3.0.0', '<') ? $product->id : $product->get_id(), '_product_addons', true);
                                if ($addonsArr != false && count($addonsArr) > 0) {
                                    if (version_compare(WC_VERSION, '3.0.0', '<')) {
                                        $typesArr = array();
                                        $itemsArr = array();
                                        $metaArr = array();
                                        $infoArr = array();
                                        foreach ($addonsArr as $addon) {
                                            $typesArr[] = $addon['name'];
                                        }
                                        $items = $order->get_items();
                                        foreach ($items as $key => $value) {
                                            $itemsArr[] = $key;
                                        }
                                        foreach ($itemsArr as $itemId) {
                                            $metadata = $order->has_meta($itemId);
                                            $metaArr = array_merge($metaArr, $metadata);
                                        }
                                        foreach ($typesArr as $type) {
                                            foreach ($metaArr as $meta) {
                                                if (strpos($meta['meta_key'], $type) !== false) {
                                                    $infoArr[] = $meta['meta_key'] . ': ' . $meta['meta_value'];
                                                }
                                            }
                                        }
                                    } else {
                                        $infoArr = array();
                                        foreach ($addonsArr as $addon) {
                                            $typesArr[] = $addon['name'];
                                        }
                                        $items = $order->get_items();
                                        foreach ($typesArr as $type) {
                                            foreach ($items as $itemObj) {
                                                $itemMetaArr = $itemObj->get_formatted_meta_data();
                                                foreach ($itemMetaArr as $itemMetaObj) {
                                                    if (strpos($itemMetaObj->key, $type) !== false) {
                                                        $infoArr[] = $itemMetaObj->key . ': ' . $itemMetaObj->value;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    if (count($infoArr)) {
                                        $rowArray['product_addon'] = implode(' - ', $infoArr);
                                    } else {
                                        $rowArray['product_addon'] = esc_html__('N/A', 'wbe-exporter');
                                    }
                                } else {
                                    $rowArray['product_addon'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('product_vendor', $existingArray)) {
                                if (version_compare(WC_VERSION, '3.0.0', '<')) {
                                    $terms = wp_get_post_terms($product->id, 'shop_vendor');
                                } else {
                                    $terms = wp_get_post_terms($product->get_id(), 'wcpv_product_vendors');
                                }
                                if (count($terms) > 0) {
                                    $vendorsArr = array();
                                    foreach ($terms as $vendor) {
                                        $vendorsArr[] = $vendor->name;
                                    }
                                    $rowArray['product_vendor'] = implode(' - ', $vendorsArr);
                                } else {
                                    $rowArray['product_vendor'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('booking_id', $existingArray)) {
                                $rowArray['booking_id'] = $id;
                            }
                            if (in_array('booking_start_date', $existingArray)) {
                                if ($obj->start) {
                                    $rowArray['booking_start_date'] = date("H:i j F Y", $obj->start);
                                } else {
                                    $rowArray['booking_start_date'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('booking_end_date', $existingArray)) {
                                if ($obj->end) {
                                    $rowArray['booking_end_date'] = date("H:i j F Y", $obj->end);
                                } else {
                                    $rowArray['booking_end_date'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('user_id', $existingArray)) {
                                if (!empty($user)) {
                                    $rowArray['user_id'] = $user->ID;
                                } else {
                                    $rowArray['user_id'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('user_email', $existingArray)) {
                                if (!empty($user)) {
                                    $rowArray['user_email'] = $user->user_email;
                                } else {
                                    $rowArray['user_email'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('user_username', $existingArray)) {
                                if (!empty($user)) {
                                    $rowArray['user_username'] = $user->user_login;
                                } else {
                                    $rowArray['user_username'] = esc_html__('N/A', 'wbe-exporter');
                                }
                            }
                            if (in_array('user_roles', $existingArray)) {
                                if (!empty($user->roles))
                                    $rowArray['user_roles'] = implode(',', (array)$user->roles);
                                else
                                    $rowArray['user_roles'] = esc_html__('N/A', 'wbe-exporter');
                            }


                            // woocommerce admin custom order fields add-on
                            $admin_order_fields = get_option('wc_admin_custom_order_fields', true);
                            if (is_array($admin_order_fields) || is_object($admin_order_fields)) {
                                foreach ($admin_order_fields as $key => $aof) {
                                    if (in_array('custom-order-field_' . $key, $existingArray)) {
                                        $aof_data = get_post_meta($obj->order_id, '_wc_acof_' . $key, true);
                                        if (is_array($aof_data)) {
                                            if (count($aof_data) == 1) {
                                                $aof_value = $aof_data[0];
                                            } else {
                                                $aof_value = implode(", ", $aof_data);
                                            }
                                        } else {
                                            $aof_value = $aof_data;
                                        }
                                        $rowArray['custom-order-field_' . $key] = $aof_value;
                                    }
                                }
                            }
                            // 
                            $rowArray = apply_filters('wbe_add_custom_field_data', $rowArray, $existingArray, $user, $order, $obj);
                            //order row by column order
                            if (!empty($rowArray)) {
                                $dataArray[] = array_values(array_merge(array_flip($existingArray), $rowArray));
                            }
                        }
                    }
                }

                return $dataArray;
            }
        }
        public function product_filter_javascript()
        { ?>
            <script type="text/javascript">
                $ = jQuery;
                $(document).ready(function() {
                    $('#export_category').on('change', function() {
                        var data = {
                            'action': 'product_filter',
                            'catIds': $("#export_category").val()
                        };
                        $.post(ajaxurl, data, function(response) {
                            $('#export_product').html(response).trigger("chosen:updated");;
                        });
                    });
                });
            </script>
        <?php
        }
        public function wbe_inactive_plugin_notice()
        { ?>
            <div id="message" class="error">
                <p><?php printf(esc_html__('WooCommerce Booking Exporter requires WooCommerce and WooCommerce Booking to be installed!', 'wbe-exporter')); ?></p>
            </div>
<?php
        }
    }
    $wbe = new WBE();
}
