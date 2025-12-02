<div class="wrap"  >
    <div id="icon-tools" class="icon32"></div>
    <h2><?php esc_html_e('WooCommerce Booking Exporter', 'wbe-exporter'); ?></h2>
    <?php
    $selected_template = $saved_templates = array();
    if (get_option("booking_exporter_templates") && !empty(get_option("booking_exporter_templates")))
        $saved_templates = get_option("booking_exporter_templates");

    $selected_template_name = "";
    if (isset($_GET['template']) && ($selected_template_name = $_GET['template']) && isset($saved_templates[$_GET['template']]))
        $selected_template = $saved_templates[$_GET['template']];
    if((isset($_POST['send_email_now_success']) && $_POST['send_email_now_success'] == 1 ) || (isset($_POST['save_schedule_success']) && $_POST['save_schedule_success'] == 1)){ ?>
        <script>
            $ = jQuery;
            $(document).ready(function () {
                $("#booking-export-tabs").tabs("option", "active", 2);
            });
        </script>
    <?php }elseif(isset($_POST['import_booking_templates'])){ ?>
        <script>
            $ = jQuery;
            $(document).ready(function () {
                $("#booking-export-tabs").tabs("option", "active", 1);
            });
        </script>
    <?php } ?>
    <div id="booking-export-tabs" > 
        <ul>
            <li><a href="#form-tab"><?php esc_html_e('Exporter', 'wbe-exporter'); ?></a></li>
            <li><a href="#import-export-tab"><?php esc_html_e('Templates', 'wbe-exporter'); ?></a></li>
            <li><a href="#emails-tab"><?php esc_html_e('Emails', 'wbe-exporter'); ?></a></li>
        </ul>
        <!--Form Tab-->
        <div id="form-tab">
            <?php require_once( WBE_MODIFY_PLUGIN_PATH . 'templates/booking-exporter-tab.php' ); ?>
        </div>
        <!--End Form Tab-->
        <!--Import & Export Tab-->
        <div id="import-export-tab">
            <?php require_once( WBE_MODIFY_PLUGIN_PATH . 'templates/import-export-tab.php' ); ?>
        </div>
        <!--End Import & Export Tab-->   
        <!--Emails Tab-->
        <div id="emails-tab">
            <?php require_once( WBE_MODIFY_PLUGIN_PATH . 'templates/emails-tab.php' ); ?>
        </div>
        <!--End Emails Tab-->
    </div>
</div>