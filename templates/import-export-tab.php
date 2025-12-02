<div class="export-div">
    <h2><?php esc_html_e('Export', 'wbe-exporter'); ?></h2>
    <h4><?php esc_html_e('Select templates to be Exported', 'wbe-exporter'); ?>:</h4>
    <?php
    if(($saved_templates = get_option("booking_exporter_templates"))){ ?>
        <form method="post">
            <ul>
                <?php
                foreach ($saved_templates as $template_name => $template) { 
				$template_name_show = str_replace('_', ' ', $template_name);
?>
                    <li>
                        <input type="checkbox" name="<?php echo esc_attr($template_name); ?>" id="<?php echo esc_attr($template_name); ?>" class="<?php echo esc_attr($template_name); ?>">    
                        <label for="<?php echo esc_attr($template_name); ?>"><?php echo esc_html($template_name_show); ?></label>
                        <a href="#" id="wbe-delete-template" class="wbe-delete-template" title="Delete Template"><span class="dashicons dashicons-trash"></span></a>
                    </li>     
                <?php } ?>
            </ul>
            <input type="hidden" name="action" value="booking-exporter-export-templates" />    
            <button type="submit" class="button-primary export-templates-btn"><?php esc_html_e('Export', 'wbe-exporter'); ?></button>
        </form>
        <?php
    }else{ ?>
        <p><?php esc_html_e("No Templates To Export !!!", 'wbe-exporter'); ?></p>
    <?php } ?>
</div>
<div class="import-div">
    <h2><?php esc_html_e('Import', 'wbe-exporter'); ?></h2>
    <?php
    if (isset($_POST['import_booking_templates'])) {
        if ($_POST['import_booking_templates'] == 1) {
            echo '<div id="message" class="updated below-h2"><p>'.esc_html__('Template Saved Successfully!', 'wbe-exporter').'</p></div>';
        } else {
            echo '<div id="message" class="error below-h2"><p>'.esc_html($_POST['import_booking_templates']).'</p></div>';
        }
    } ?>
    <form method="post"  enctype="multipart/form-data" id="imported-form">
        <input type="file" name="imported-file" id="imported-file">
        <input type="hidden" name="action" value="booking-exporter-import-templates"  />  
        <p class="error" style="display:none;color: red;">
            <?php esc_html_e('Please upload import file.', 'wbe-exporter'); ?> 
        </p>
        <button type="submit" class="button-primary import-templates-btn"><?php esc_html_e('Import', 'wbe-exporter'); ?></button>
    </form>
</div>