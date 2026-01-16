<form method="post" action="" class="booking-export-form">
    <div class="exporter-wrapper">
        <h3><?php esc_html_e('Filter exported data', 'wbe-exporter') ?>:</h3>
        <?php
        if (isset($_GET['booking']) && $_GET['booking'] == 'no') {
            echo '<div id="message" class="error below-h2"><p>'.esc_html__('There are no bookings that match your filter!', 'wbe-exporter').'</p></div>';
        } ?>
        <ul class="exporter-filter">
            <li>
                <label class="exporter-filter-label"><?php esc_html_e('Categories', 'wbe-exporter') ?></label>
                <select id="export_category" class="chosen-select" style="width: 33%;" name="booking_exporter_category[]" multiple>
                    <option value="all" selected><?php esc_html_e('All','wbe-exporter'); ?></option>
                    <?php
                    // Optimized: Only get categories that have booking products
                    global $wpdb;
                    $cats = $wpdb->get_results(
                        "SELECT DISTINCT t.term_id, t.name 
                        FROM {$wpdb->terms} t
                        INNER JOIN {$wpdb->term_taxonomy} tt ON t.term_id = tt.term_id
                        INNER JOIN {$wpdb->term_relationships} tr ON tt.term_taxonomy_id = tr.term_taxonomy_id
                        INNER JOIN {$wpdb->posts} p ON tr.object_id = p.ID
                        INNER JOIN {$wpdb->term_relationships} tr2 ON p.ID = tr2.object_id
                        INNER JOIN {$wpdb->term_taxonomy} tt2 ON tr2.term_taxonomy_id = tt2.term_taxonomy_id
                        INNER JOIN {$wpdb->terms} t2 ON tt2.term_id = t2.term_id
                        WHERE tt.taxonomy = 'product_cat'
                        AND p.post_type = 'product'
                        AND tt2.taxonomy = 'product_type'
                        AND t2.slug = 'booking'
                        ORDER BY t.name ASC"
                    );
                    
                    if ($cats) {
                        foreach($cats as $cat){ ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
                        <?php }
                    } else {
                        // Fallback to all categories if query fails
                        $cats = get_terms(array(
                            'taxonomy' => 'product_cat',
                            'hide_empty' => false,
                            'number' => 500 // Limit to 500 categories
                        ));
                        foreach($cats as $cat){ ?>
                            <option value="<?php echo esc_attr($cat->term_id); ?>"><?php echo esc_html($cat->name); ?></option>
                        <?php }
                    }
                    ?>
                </select>
            </li>
            <li>
                <label class="exporter-filter-label"><?php esc_html_e('Products', 'wbe-exporter') ?></label>
                <select id="export_product" class="chosen-select" style="width: 33%;" name="booking_exporter_product[]" multiple>
                    <option value="all" selected><?php esc_html_e('All','wbe-exporter'); ?></option>
                    <?php
                    // Use optimized cached query
                    global $wbe;
                    if (!$wbe) {
                        $wbe = new WBE();
                    }
                    $products = $wbe->get_cached_booking_products();
                    
                    if ($products) {
                        foreach ($products as $product) {
                            $product_title = $product->post_title;
                            // Add status indicator if not published
                            if ($product->post_status !== 'publish') {
                                $status_labels = array(
                                    'draft' => esc_html__(' (Rascunho)', 'wbe-exporter'),
                                    'trash' => esc_html__(' (Excluído)', 'wbe-exporter'),
                                    'pending' => esc_html__(' (Pendente)', 'wbe-exporter'),
                                    'private' => esc_html__(' (Privado)', 'wbe-exporter'),
                                );
                                $status_label = isset($status_labels[$product->post_status]) ? $status_labels[$product->post_status] : ' (' . $product->post_status . ')';
                                $product_title .= $status_label;
                            }
                        ?>
                            <option value="<?php echo esc_attr($product->ID); ?>"><?php echo esc_html($product_title); ?></option>
                        <?php 
                        }
                    }
                    ?>
                </select>
            </li>
            <li>
                <label class="exporter-filter-label"><?php esc_html_e('Users', 'wbe-exporter') ?></label>
                <select style="width: 33%;" class="chosen-select" name="booking_exporter_user[]" multiple>
                    <option value="all" selected><?php esc_html_e('All', 'wbe-exporter') ?></option>
                    <?php
                    // Optimized: Only get users who have orders (much faster)
                    global $wpdb;
                    $users_with_orders = $wpdb->get_results(
                        "SELECT DISTINCT u.ID, u.user_nicename 
                        FROM {$wpdb->users} u
                        INNER JOIN {$wpdb->postmeta} pm ON u.ID = pm.meta_value
                        WHERE pm.meta_key = '_customer_user'
                        ORDER BY u.user_nicename ASC
                        LIMIT 1000"
                    );
                    
                    if ($users_with_orders) {
                        foreach ($users_with_orders as $user) { ?>
                            <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->user_nicename); ?></option>
                        <?php }
                    } else {
                        // Fallback: get limited users if no orders found
                        $users = get_users(array(
                            'number' => 500, // Limit to 500 users
                            'fields' => array('ID', 'user_nicename') // Only get needed fields
                        ));
                        foreach ($users as $user) { ?>
                            <option value="<?php echo esc_attr($user->ID); ?>"><?php echo esc_html($user->user_nicename); ?></option>
                        <?php }
                    }
                    ?>
                </select>
            </li>
            <?php if (in_array('woocommerce-product-vendors/woocommerce-product-vendors.php', apply_filters('active_plugins', get_option('active_plugins')))) { ?>
                <li>
                    <label class="exporter-filter-label"><?php esc_html_e('Vendors', 'wbe-exporter'); ?></label>
                    <select style="width: 33%;" class="chosen-select" name="booking_exporter_vendors[]" multiple>
                        <option value="all" selected><?php esc_html_e('All', 'wbe-exporter'); ?></option>
                        <?php
                        $args = array(
                            'orderby' => 'name',
                            'order' => 'ASC',
                            'hide_empty' => false
                        );
                        if (version_compare(WC_VERSION, '3.0.0', '<')) {
                            $terms = get_terms('shop_vendor', $args);
                        } else {
                            $terms = get_terms('wcpv_product_vendors', $args);
                        }
                        foreach ($terms as $vendor) { ?>
                            <option value="<?php esc_attr_e($vendor->slug); ?>"><?php echo esc_html($vendor->name); ?></option>
                        <?php } ?>
                    </select>
                </li>
            <?php } ?>
            <li>
                <label ><?php esc_html_e('Delimiter', 'wbe-exporter'); ?>:</label>
                <input type="text" name="delimiter"   />
                <p><?php esc_html_e('By Default "," is delimiter ', 'wbe-exporter'); ?></p>
            </li>
        </ul>
        <div id="datepicker3" class="input-daterange">
            <span><?php esc_html_e('From date', 'wbe-exporter') ?></span>
            <input class="date-picker-admin" type="text" name="booking_from_date">
            <span><?php esc_html_e('To date', 'wbe-exporter') ?></span>
            <input class="date-picker-admin" type="text" name="booking_to_date">
        </div>
        <h3><?php esc_html_e('Choose the data you want to export', 'wbe-exporter') ?>:</h3>
        <button   class=" button-primary select-all-options"><?php esc_html_e('Select All', 'wbe-exporter') ?></button>
        <div class='export-fields-wrapper'>
            <ul class="exporter-data" data-ok="<?php esc_html_e('Ok', 'wbe-exporter') ?>" data-error="<?php esc_html_e('Please Enter Field Name', 'wbe-exporter') ?>" >
                <!--Order Section-->
                <li class="exporter-data-heading"><?php esc_html_e('Order', 'wbe-exporter') ?>:</li>
                <?php

                $order_fields = array(
                    "order_id"                  => esc_html__('Pedido', 'wbe-exporter'),
                    "order_status"              => esc_html__('Status', 'wbe-exporter'),
                    "order_note"                => esc_html__('Nota do Cliente', 'wbe-exporter'),
                    "customer_provided_note"    => esc_html__('Observações do Pedido', 'wbe-exporter'),
                    "order_sub_totals"          => esc_html__('Subtotal do Pedido', 'wbe-exporter'), //
                    "order_totals"              => esc_html__('Total do Pedido', 'wbe-exporter'),
                    "coupons"                   => esc_html__('Cupons', 'wbe-exporter'),
                    "billing_first_name"        => esc_html__('Primeiro Nome de Cobrança', 'wbe-exporter'),
                    "billing_second_name"       => esc_html__('Último Nome de Cobrança', 'wbe-exporter'),
                    "billing_company_name"      => esc_html__('Nome da Empresa de Cobrança', 'wbe-exporter'),
                    "billing_address_1"         => esc_html__('Endereço de Cobrança 1', 'wbe-exporter'),
                    "billing_address_2"         => esc_html__('Endereço de Cobrança 2', 'wbe-exporter'),
                    "billing_phone"             => esc_html__('Telefone de Cobrança', 'wbe-exporter'),
                    "billing_zip"               => esc_html__('CEP de Cobrança', 'wbe-exporter'),
                    "billing_city"              => esc_html__('Cidade de Cobrança', 'wbe-exporter'),
                    "billing_state"             => esc_html__('Estado de Cobrança', 'wbe-exporter'),
                    "billing_country"           => esc_html__('País de Cobrança', 'wbe-exporter'),
                    "shipping_first_name"       => esc_html__('Primeiro Nome de Envio', 'wbe-exporter'),
                    "shipping_second_name"      => esc_html__('Último Nome de Envio', 'wbe-exporter'),
                    "shipping_company_name"     => esc_html__('Nome da Empresa de Envio', 'wbe-exporter'),
                    "shipping_address_1"        => esc_html__('Endereço de Envio 1', 'wbe-exporter'),
                    "shipping_address_2"        => esc_html__('Endereço de Envio 2', 'wbe-exporter'),
                    "shipping_phone"            => esc_html__('Telefone de Envio', 'wbe-exporter'),
                    "shipping_zip"              => esc_html__('CEP de Envio', 'wbe-exporter'),
                    "shipping_city"             => esc_html__('Cidade de Envio', 'wbe-exporter'),
                    "shipping_state"            => esc_html__('Estado de Envio', 'wbe-exporter'),
                    "shipping_country"          => esc_html__('País de Envio', 'wbe-exporter'),
                    "shipping_cost"             => esc_html__('Custo de Envio', 'wbe-exporter'),
                    "payment_method"            => esc_html__('Método de Pagamento', 'wbe-exporter'),
                    "payment_method_title"      => esc_html__('Título do Método de Pagamento', 'wbe-exporter'),
                    "completed_date"            => esc_html__('Data de Pagamento do Pedido', 'wbe-exporter')
                );

                foreach ($order_fields as $field_key => $field_name) { ?>
                    <li><input type="checkbox" <?php echo (isset($selected_template[$field_key])) ? "checked" : ""; ?> name="<?php esc_attr_e($field_key); ?>" id="<?php esc_attr_e($field_key); ?>_checkbox" value="<?php esc_attr_e($field_name); ?>"><label for="<?php esc_attr_e($field_key); ?>_checkbox" class="exporter-data-label"><?php echo esc_html($field_name); ?></label></li>
                <?php } ?>
                <!--End Order Section-->
                <!--Product Section-->
                <?php
                $product_fields = array(
                    "product_id" => esc_html__('ID do Produto', 'wbe-exporter'),
                    "product_name" => esc_html__('Nome do Produto', 'wbe-exporter'),
                    "product_sku" => esc_html__('SKU do Produto', 'wbe-exporter'),
                    "product_res" => esc_html__('Recursos do Produto', 'wbe-exporter')
                ); ?>
                <li class="exporter-data-heading"><?php esc_html_e('Product', 'wbe-exporter') ?>:</li>
                <?php
                foreach ($product_fields as $field_key => $field_name) { ?>
                    <li><input type="checkbox" <?php echo (isset($selected_template[$field_key])) ? "checked" : ""; ?> name="<?php esc_attr_e($field_key); ?>" id="<?php esc_attr_e($field_key); ?>_checkbox" value="<?php esc_attr_e($field_name); ?>"><label for="<?php esc_attr_e($field_key); ?>_checkbox" class="exporter-data-label"><?php echo esc_html($field_name); ?></label></li>
                <?php } ?>
                <!--End Product Section-->  
                <?php if (in_array('woocommerce-product-addons/woocommerce-product-addons.php', apply_filters('active_plugins', get_option('active_plugins')))) { ?>
                    <li><input type="checkbox" <?php echo (isset($selected_template["product_addon"])) ? "checked" : ""; ?> name="product_addon" id="product_addon_checkbox" value="<?php esc_html_e('Detalhes Adicionais', 'wbe-exporter') ?>"><label for="product_addon_checkbox" class="exporter-data-label"><?php esc_html_e('Product Add-ons', 'wbe-exporter'); ?></label></li>
                <?php } ?>
                <?php if (in_array('woocommerce-product-vendors/woocommerce-product-vendors.php', apply_filters('active_plugins', get_option('active_plugins')))) { ?>
                    <li><input type="checkbox" <?php echo (isset($selected_template["product_vendor"])) ? "checked" : ""; ?> name="product_vendor" id="product_vendor_checkbox" value="<?php esc_html_e('Product Vendor', 'wbe-exporter') ?>"><label for="product_vendor_checkbox" class="exporter-data-label"><?php esc_html_e('Product Add-ons', 'wbe-exporter'); ?></label></li>
                <?php } ?>
                <!--Booking Section-->
                <?php
                $booking_fields = array(
                    "booking_id" => esc_html__('ID da Reserva', 'wbe-exporter'),
                    "order_person" => esc_html__('Responsável pela Reserva', 'wbe-exporter'),
                    "booking_start_date" => esc_html__('Data de Início da Reserva', 'wbe-exporter'),
                    "booking_end_date" => esc_html__('Data de Término da Reserva', 'wbe-exporter'),
                ); ?>
                <li class="exporter-data-heading"><?php esc_html_e('Booking', 'wbe-exporter'); ?>:</li>
                <?php
                foreach ($booking_fields as $field_key => $field_name) {  ?>
                    <li><input type="checkbox" <?php echo (isset($selected_template[$field_key])) ? "checked" : ""; ?> name="<?php esc_attr_e($field_key); ?>" id="<?php esc_attr_e($field_key); ?>_checkbox" value="<?php esc_attr_e($field_name); ?>"  ><label for="<?php esc_attr_e($field_key); ?>_checkbox" class="exporter-data-label"><?php echo esc_html($field_name); ?></label></li>
                <?php } ?>
                <!--End Booking Section-->  
                <!--User Section-->
                <?php
                $user_fields = array(
                    "user_id" => esc_html__('User ID', 'wbe-exporter'),
                    "user_email" => esc_html__('Email', 'wbe-exporter'),
                    "user_username" => esc_html__('Username', 'wbe-exporter'),
                    "user_roles" => esc_html__('User Roles', 'wbe-exporter'),
                ); ?>
                <li class="exporter-data-heading"><?php esc_html_e('User', 'wbe-exporter') ?>:</li>
                <?php
                foreach ($user_fields as $field_key => $field_name){ ?>
                    <li><input type="checkbox" <?php echo (isset($selected_template[$field_key])) ? "checked" : ""; ?> name="<?php esc_attr_e($field_key); ?>" id="<?php esc_attr_e($field_key); ?>_checkbox" value="<?php esc_attr_e($field_name); ?>"  ><label for="<?php esc_attr_e($field_key); ?>_checkbox" class="exporter-data-label"><?php echo esc_html($field_name); ?></label></li>
               <?php } ?>
                <!--End User Section--> 
                <!--Custom Section-->
                <?php
                do_action( 'wbe_add_custom_fields_in_export_tab', $selected_template);
                ?>
                <!--End Custom Section-->
            </ul>
            <div class="sort-div" >
                <ul id="sortable">
                    <?php foreach ($selected_template as $key => $value){ ?>
                        <li class='ui-state-default' id='<?php esc_attr_e($key); ?>'>
                            <span class='ui-icon ui-icon-arrowthick-2-n-s'></span>
                            <span class='text'><?php echo esc_html($value); ?></span>
                            <span class='edit_name'><i class='dashicons dashicons-edit'></i></span>
                        </li>
                    <?php } ?>
                </ul>
            </div>
        </div>
    </div>
   
    <input type="hidden" name="action" value="export" />
    <?php wp_nonce_field('wbe_export_action', 'wbe_export_nonce'); ?>
    <input type="hidden" name="coloums_order" class="coloums_order" value="<?php echo esc_attr(json_encode($selected_template)); ?>" />
    <input type="hidden" name="file_type" class="file_type" value="csv" />
    <div class="template_manage_wrapper">
        <input type="checkbox" value="1" id="save_template" class="save_template" name="save_template">
        <label for="save_template"><?php esc_html_e('Save settings as a template', 'wbe-exporter'); ?></label>
        <div class="save_template_wrapper" style="display:none;">
            <input type="text" value="<?php esc_attr_e($selected_template_name); ?>" placeholder="<?php esc_html_e('Enter template name', 'wbe-exporter'); ?>" class="template_name" name="template_name" >
            <input type="submit" class="button-primary save_template_btn" value="<?php esc_html_e('Save Template', 'wbe-exporter') ?>" />
        </div>
        <select name="templates" class="templates">
            <option value="" selected="" ><?php esc_html_e('Load Template', 'wbe-exporter'); ?></option>
            <?php
            foreach ($saved_templates as $template_name => $template) {
			$template_name_show = str_replace('_', ' ', $template_name);
 ?>
                <option value="<?php esc_attr_e($template_name); ?>" <?php echo ($selected_template_name == $template_name) ? "selected" : ""; ?>><?php echo esc_html($template_name_show); ?></option>
            <?php } ?>
        </select>
    </div>
    <p>
        <input type="submit" class="button-primary submit_btn export_csv" value="📄 <?php esc_html_e('Export CSV', 'wbe-exporter') ?>" />
        <input type="submit" class="button-primary submit_btn export_excel" value="📊 <?php esc_html_e('Export Excel', 'wbe-exporter') ?>" />
        <?php /* PDF desativado temporariamente */ ?>

        <input type="button" class="button-primary ajaxified_csv_export" value="🚀 <?php esc_html_e('Export CSV (1000+)', 'wbe-exporter') ?>" />
    </p>
    
    <div class="wbe-info-box" style="background: #dbeafe; border: 2px solid #2563eb; border-left: 5px solid #1e40af; padding: 16px 20px; margin: 20px 0; border-radius: 8px; box-shadow: 0 2px 4px rgba(37, 99, 235, 0.1);">
        <p style="margin: 0; font-size: 14px; color: #1e3a8a; font-weight: 600;">
            <strong style="font-size: 16px;">💡 Dica:</strong> Use <strong style="color: #059669;">CSV</strong> para compatibilidade universal ou <strong style="color: #2563eb;">Excel</strong> para análise de dados.
        </p>
    </div>

<div id="wbe_progressbar">
  <div></div>
</div>
</form>