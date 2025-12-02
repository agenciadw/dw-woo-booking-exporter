<form  method="post" >
    <?php
    if (isset($_POST['send_email_now_success']) && $_POST['send_email_now_success'] == 1) {
        echo '<div id="message" class="updated below-h2"><p>'.esc_html__('Email sent successfully !', 'wbe-exporter').'!!</p></div>';
    } ?>
    <h3><?php esc_html_e('Send Email Right Now', 'wbe-exporter') ?></h3>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Send Email Now To', 'wbe-exporter') ?>:</th>
            <td>
                <input type="email" name="sent_email" required=""  class="regular-text">
                <input type="hidden" name="action" value="send-email-now"/>    
                <button type="submit" class="button-primary import-templates-btn"><?php esc_html_e('Send Email ', 'wbe-exporter'); ?></button>
            </td>  
        </tr>
    </table>
</form>
<hr>
<form  method="post" >
    <h3><?php _e('Send Schedule Emails', 'wbe-exporter') ?></h3>
    <?php
    if (isset($_POST['save_schedule_success']) && $_POST['save_schedule_success'] == 1) {
        echo '<div id="message" class="updated below-h2"><p>'.esc_html__('Setting saved successfully!', 'wbe-exporter').'!!</p></div>';
    } ?>
    <table class="form-table">
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Emails', 'wbe-exporter') ?>:</th>
            <td>
                <input type="text" name="cronjob_emails" value="<?php echo get_option("wc_booking_cronjob_emails"); ?>" style="width:60%">
                <p><?php esc_html_e('Add emails separated by ",".', 'wbe-exporter'); ?></p>
            </td>
        </tr>
        <?php 
            $filter_dates = get_option('wc_bookings_filter_date_schedule_email', true);
            $checked  = isset($filter_dates['now_on']) && $filter_dates['now_on'] == 1 ? 'checked' : "";
            $to_value = isset($filter_dates['now_on']) && empty($filter_dates['now_on']) ? $filter_dates['to_value'] : "";
        ?> 
        <tr valign="top">
            <th scope="row"><?php echo __('Filter by Date', 'wbe-exporter'); ?></th>
            <td>
                <span><?php esc_html_e('From', 'wbe-exporter');?></span>
                <input type="date" name="email_booking_from_date" value="<?php echo esc_attr($filter_dates['from_value']); ?>">
                <span><?php esc_html_e('To', 'wbe-exporter');?></span>
                <input type="date" name="email_booking_to_date" value="<?php echo esc_attr($to_value); ?>">
                <input type="checkbox" name="now_on" value="now_on" <?php echo esc_attr($checked); ?>>
                <label><?php esc_html_e('Now on', 'wbe-exporter');?></label>
                <br />
                <p><?php esc_html_e('Leave blank for all Bookings', 'wbe-exporter');?></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php echo __('Filter by Time Period', 'wbe-exporter'); ?></th>
            <td>
                <select name="cronjob-timeperiod">
                    <option value=""><?php esc_html_e('None', 'wbe-exporter') ?></option>
                    <option value="day" <?php echo (get_option("wc_bookings_filter_time_period") == 'day') ? 'selected' : ''; ?>><?php esc_html_e('Last Day', 'wbe-exporter'); ?></option>
                    <option value="week"  <?php echo (get_option("wc_bookings_filter_time_period") == 'week') ? 'selected' : ''; ?>><?php esc_html_e('Last week', 'wbe-exporter'); ?></option>
                    <option value="month" <?php echo (get_option("wc_bookings_filter_time_period") == 'month') ? 'selected' : ''; ?>><?php esc_html_e('Last Month', 'wbe-exporter'); ?></option>
                </select>
                <br />
                <p><span><?php esc_html_e('Either time-period or date filter will work, if both have valid values then date filter will be ignored.', 'wbe-exporter');?></span></p>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Cronjob Schedule', 'wbe-exporter'); ?>:</th>
            <td>
                <select name="cronjob-schedule">
                    <option value=""><?php esc_html_e('None', 'wbe-exporter') ?></option>
                    <option value="daily" <?php echo (get_option("wc_booking_cronjob_schedule") == 'daily') ? 'selected' : ''; ?>><?php esc_html_e('Daily', 'wbe-exporter'); ?></option>
                    <option value="weekly"  <?php echo (get_option("wc_booking_cronjob_schedule") == 'weekly') ? 'selected' : ''; ?>><?php esc_html_e('Weekly', 'wbe-exporter'); ?></option>
                    <option value="monthly" <?php echo (get_option("wc_booking_cronjob_schedule") == 'monthly') ? 'selected' : ''; ?>><?php esc_html_e('Monthly', 'wbe-exporter'); ?></option>
                </select>
            </td>
            
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Template', 'wbe-exporter') ?>: </th>
            <td>
            <?php 
                $saved_templates = get_option("booking_exporter_templates", true);
                $template_option = get_option("template-cron-email");
            ?>
            <select name="template-cron-email">
            <option value="all-fields" selected><?php esc_html_e('All fields', 'wbe-exporter') ?></option>
                <?php foreach( $saved_templates as $template_name => $saved_template ) : ?>
                <option value="<?php echo esc_attr($template_name); ?>" <?php echo ($template_name == $template_option) ? "selected" : ''; ?>><?php echo esc_attr($template_name); ?></option>
                <?php endforeach; ?>
            </select>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Email Subject', 'wbe-exporter') ?>: </th>
            <td>
                <input type="text" name="email_subject" required="" value="<?php echo esc_attr(get_option("wc_booking_email_subject")); ?>" style="width:60%">
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Email Template', 'wbe-exporter'); ?>:</th>
            <td>
                <?php wp_editor(stripslashes(get_option('wc_booking_email_template')), 'email_template', array('textarea_name' => 'email_template', 'media_buttons' => false)); ?>
            </td>
        </tr>
        <tr valign="top">
            <th scope="row"><?php esc_html_e('Attached Files To Email', 'wbe-exporter'); ?>:</th>
            <?php
            $sent=array();
            if(get_option('wc_booking_email_sent_attachment'))
                $sent= json_decode(get_option('wc_booking_email_sent_attachment')); ?>
            <td>
                <input type="checkbox" value="pdf" id="sent_attachment_pdf" class="sent_attachment_pdf" name="sent_attachment[]" <?php echo (in_array("pdf", $sent)) ? "checked" : ""; ?>>
                <label for="sent_attachment_pdf"><?php esc_html_e('PDF', 'wbe-exporter'); ?></label>
                <br>
                <input type="checkbox" value="csv" id="sent_attachment_csv" class="sent_attachment_csv" name="sent_attachment[]"  <?php echo (in_array("csv", $sent)) ? "checked" : ""; ?>>
                <label for="sent_attachment_csv"><?php esc_html_e('CSV', 'wbe-exporter'); ?></label>
            </td>
        </tr>
    </table>
    <input type="hidden" name="action" value="save-cronjob-email"   /> 
    <button type="submit" class="button-primary import-templates-btn"><?php esc_html_e('Save Settings', 'wbe-exporter'); ?></button>
</form>