<?php
defined( 'ABSPATH' ) || exit;
if (
    isset($_GET['tag']) && $_GET['tag'] === 'edit'
) {
    if (
        !isset($_GET['wgm_map_create_nonce']) ||
        !wp_verify_nonce(sanitize_text_field(wp_unslash($_GET['wgm_map_create_nonce'])), 'wgm_create_map')
    ) {
        wp_die(esc_html__('Invalid request. Nonce verification failed.', 'gmap-embed'));
    }
}
$wpgmap_map_id = isset($_GET['id']) ? intval(sanitize_text_field(wp_unslash($_GET['id']))) : 0;
?>
<div style="text-align: right;margin-top:10px;" class="add_new_marker_btn_area">
    <button type="button" value="New Marker" class="button button-primary add_new_marker"
        style="margin-bottom: 10px;"><i class="dashicons dashicons-plus" style="margin: 5px 0 0 0;"></i>
        <?php esc_html_e('New Marker', 'gmap-embed'); ?>
    </button>
    <?php
    if (!_wgm_is_premium()) {
        ?>
        <sup class="wgm-pro-label" style="top: -4px; display: none;"><?php esc_html_e('Pro', 'gmap-embed'); ?></sup>
        <?php
    }
    ?>
</div>

<div class="wgm_gmap_marker_list" style="display: block" map_id="<?php echo esc_attr($wpgmap_map_id); ?>">
    <table id="wgm_gmap_marker_list" class="display" style="width:100%">
        <thead>
            <tr>
                <th><?php esc_html_e('ID', 'gmap-embed'); ?></th>
                <th><?php esc_html_e('Marker Name', 'gmap-embed'); ?></th>
                <th><?php esc_html_e('Icon', 'gmap-embed'); ?></th>
                <th><?php esc_html_e('Action', 'gmap-embed'); ?></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <div class="wgm_marker_quick_guide">
        <div class="wgm_guide_header">
            <span class="dashicons dashicons-editor-help"></span>
            <h3><?php esc_html_e('How to Create a Marker', 'gmap-embed'); ?></h3>
        </div>
        <div class="wgm_guide_steps">
            <div class="wgm_guide_step">
                <div class="wgm_step_icon"><span class="dashicons dashicons-location"></span></div>
                <div class="wgm_step_text">
                    <strong><?php esc_html_e('1. Add Location', 'gmap-embed'); ?></strong>
                    <p><?php esc_html_e('Use Address Search or input Lat/Lng or Right-Click on the Map to place a marker pin.', 'gmap-embed'); ?></p>
                </div>
            </div>
            <div class="wgm_guide_step">
                <div class="wgm_step_icon"><span class="dashicons dashicons-edit"></span></div>
                <div class="wgm_step_text">
                    <strong><?php esc_html_e('2. Customize', 'gmap-embed'); ?></strong>
                    <p><?php esc_html_e('Fill in the Title, Description, select an Icon, etc.', 'gmap-embed'); ?></p>
                </div>
            </div>
            <div class="wgm_guide_step">
                <div class="wgm_step_icon"><span class="dashicons dashicons-cloud-saved"></span></div>
                <div class="wgm_step_text">
                    <strong><?php esc_html_e('3. Save', 'gmap-embed'); ?></strong>
                    <p><?php 
                        /* translators: 1: Save Marker text, 2: Save Map text */
                        echo sprintf(esc_html__('Click %1$s to add it to your list, then %2$s.', 'gmap-embed'), '<b>' . esc_html__('Save Marker', 'gmap-embed') . '</b>', '<b>' . esc_html__('Save Map', 'gmap-embed') . '</b>'); 
                    ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<table class="wgm_gmap_properties add_new_marker_form" style="display: none;width:100%">

    <tr>
        <td>
            <label for="wpgmap_marker_name"><b><?php esc_html_e('Title', 'gmap-embed'); ?>
                </b></label><br />
            <input id="wpgmap_marker_name" name="wpgmap_marker_name" type="text" class="regular-text">
        </td>
    </tr>

    <tr>
        <td>
            <label for="wpgmap_marker_desc"><b><?php esc_html_e('Description', 'gmap-embed'); ?></b></label><br />
            <?php
            echo (_wgm_is_premium() === false) ? '<button type="button" class="button wgm_enable_premium" style="opacity: .7;" data-notice="' .
                // translators: %s: Premium version URL.
                esc_html(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to add <b> Images in marker InfoWindow </b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=markers-add-media-lock'))) .
                '"><span class="dashicons dashicons-admin-media" style="line-height: 1.5;"></span> ' . esc_html__('Add Media', 'gmap-embed') . ' </button><sup class="wgm-pro-label" style="top: -45px;display: block;width: 23px;left: 107px;">' . esc_html__('Pro', 'gmap-embed') . '</sup>' : '';
            wp_editor(
                '',
                'wpgmap_marker_desc',
                [
                    'textarea_name' => 'wpgmap_marker_desc',
                    'textarea_rows' => '3',
                    'media_buttons' => _wgm_is_premium() === true,
                    'quicktags' => _wgm_is_premium() === true,
                ]
            );
            ?>
        </td>
    </tr>

    <tr style="padding-top: 10px;">
        <td>
            <span style="float: left;">
                <b><?php esc_html_e('Marker Icon', 'gmap-embed'); ?></b> &nbsp;
            </span>
            <?php //phpscs:ignore PluginCheck.CodeAnalysis.ImageFunctions.NonEnqueuedImage ?>
            <img src="<?php echo esc_attr(plugin_dir_url(__FILE__) . '../assets/images/markers/default.png'); ?>"
                id="wpgmap_marker_icon_preview" style="float: left;max-width: 20px;">
            <?php
            $gmap_embed_ajax_url = add_query_arg(
                [
                    'action' => 'wpgmapembed_get_marker_icons',
                    'from' => 'create',
                    '_wpnonce' => wp_create_nonce('wpgmapembed_get_marker_icons'),
                    'type' => 'image',
                    'TB_iframe' => false,
                    'width' => 650,
                    'height' => 500
                ],
                admin_url('admin-ajax.php')
            );
            ?>
            <button style="float: left;margin: 0 9px;" class="button"
                onclick="tb_show('<?php esc_html_e('Choose marker icon', 'gmap-embed'); ?>', '<?php echo esc_url($gmap_embed_ajax_url); ?>')">
                <?php esc_html_e('Choose Icon', 'gmap-embed'); ?>
            </button>
            <button style="float: left" class="button"
                id="wpgmap_upload_marker_icon"><?php esc_html_e('Upload Icon', 'gmap-embed'); ?></button>
            <input type="hidden" name="wpgmap_marker_icon" id="wpgmap_marker_icon"
                value="<?php echo esc_attr(plugin_dir_url(__FILE__) . '../assets/images/markers/default.png'); ?>" />
            <div style="clear: both;"></div>
        </td>
    </tr>

    <tr>
        <td style="padding-top: 20px;">
            <span style="float: left;">
                <b><?php esc_html_e('Marker Image', 'gmap-embed'); ?></b> &nbsp;
            </span>
            <?php
            if (!_wgm_is_premium()) {
                ?>
                <sup class="wgm-pro-label" style="top: -4px;"><?php esc_html_e('Pro', 'gmap-embed'); ?></sup>
                <?php
            }
            ?>
            <div style="float:left; margin-right: 10px;">
                <img src="" id="wpgmap_marker_image_preview" style="max-width: 50px; display: none;">
            </div>
            <?php
            $gmap_embed_marker_image_btn_class = 'button';
            $gmap_embed_marker_image_btn_style = 'float: left';
            $gmap_embed_marker_image_data_notice = '';
            if (!_wgm_is_premium()) {
                $gmap_embed_marker_image_btn_class .= ' wgm_enable_premium';
                $gmap_embed_marker_image_btn_style .= '; opacity: .7';
                // translators: %s: Premium version URL.
                $gmap_embed_marker_image_data_notice = ' data-notice="' . esc_attr(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to add <b>Marker Images</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=settings-license-error-msg'))) . '"';
            }
            ?>
            <button style="<?php echo esc_attr($gmap_embed_marker_image_btn_style); ?>" class="<?php echo esc_attr($gmap_embed_marker_image_btn_class); ?>"
                id="wpgmap_upload_marker_image" <?php echo esc_attr($gmap_embed_marker_image_data_notice); ?>><?php esc_html_e('Upload Image', 'gmap-embed'); ?></button>
            <button style="float: left; margin-left: 5px; display: none; color: #a00;" class="button"
                id="wpgmap_remove_marker_image"><?php esc_html_e('Remove', 'gmap-embed'); ?></button>
            <input type="hidden" name="wpgmap_marker_image" id="wpgmap_marker_image" value="" />
        </td>
    </tr>

    <tr>
        <td>
            <label for="wpgmap_marker_address"><b><?php esc_html_e('Address', 'gmap-embed'); ?></b></label><br />
            <input id="wpgmap_marker_address" name="wpgmap_marker_address" type="text" class="regular-text">
        </td>
    </tr>

    <tr>
        <td>
            <label for="wpgmap_marker_lat_lng"><b>
                    <?php esc_html_e('Latitude,Longitude', 'gmap-embed'); ?><span
                        class="required-star">*</span></b></label><br />
            <input id="wpgmap_marker_lat_lng" name="wpgmap_marker_lat_lng" type="text" class="regular-text">
        </td>
    </tr>

    <tr>
        <td>
            <label for="wpgmap_marker_animation"><b><?php esc_html_e('Animation', 'gmap-embed'); ?></b></label><br />
            <select name="wpgmap_marker_animation" id="wpgmap_marker_animation" class="regular-text">
                <option value=""><?php esc_html_e('None', 'gmap-embed'); ?></option>
                <option value="BOUNCE"><?php esc_html_e('BOUNCE', 'gmap-embed'); ?></option>
                <option value="DROP"><?php esc_html_e('DROP', 'gmap-embed'); ?></option>
            </select>
        </td>
    </tr>

    <tr>
        <td style="padding: 10px 0;">
            <label for="wpgmap_marker_category"><b><?php esc_html_e('Category', 'gmap-embed'); ?></b>
            <?php
            if (!_wgm_is_premium()) {
                ?>
                &nbsp;<sup class="wgm-pro-label"><?php esc_html_e('Pro', 'gmap-embed'); ?></sup>
                <?php
            }
            ?>
            </label>
            <br />
            <?php
            $gmap_embed_category_select_class = 'regular-text wgm-select2';
            $gmap_embed_category_select_disabled = '';
            $gmap_embed_category_wrapper_class = '';
            $gmap_embed_category_data_notice = '';
            
            if (!_wgm_is_premium()) {
                $gmap_embed_category_select_class .= ' wgm_enable_premium';
                $gmap_embed_category_select_disabled = ' disabled="disabled"';
                $gmap_embed_category_wrapper_class = ' wgm-locked-feature';
                // translators: %s: Premium version URL.
                $gmap_embed_category_data_notice = ' data-notice="' . esc_attr(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b>assign Categories to markers</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=settings-license-error-msg'))) . '"';
            }
            ?>
            <div class="<?php echo esc_attr($gmap_embed_category_wrapper_class); ?>" <?php echo esc_attr($gmap_embed_category_data_notice); ?>>
                <select name="wpgmap_marker_category[]" id="wpgmap_marker_category" class="<?php echo esc_attr($gmap_embed_category_select_class); ?>" multiple="multiple" <?php echo esc_attr($gmap_embed_category_select_disabled); ?>>
                    <?php
                    global $wpdb;
                    $gmap_embed_admin_categories = wp_cache_get('gmap_embed_categories', 'gmap_embed');
                    if (false === $gmap_embed_admin_categories) {
                        // phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
                        $gmap_embed_admin_categories = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}wgm_categories ORDER BY name ASC");
                        wp_cache_set('gmap_embed_categories', $gmap_embed_admin_categories, 'gmap_embed');
                    }
                    if ($gmap_embed_admin_categories) {
                        foreach ($gmap_embed_admin_categories as $gmap_embed_cat) {
                            echo '<option value="' . esc_attr($gmap_embed_cat->id) . '">' . esc_html($gmap_embed_cat->name) . '</option>';
                        }
                    }
                    ?>
                </select>
            </div>
        </td>
    </tr>

    <tr>
        <td>
            <label for="wpgmap_have_marker_link"><?php esc_html_e('Link', 'gmap-embed'); ?></label>&nbsp;
            <select name="wpgmap_have_marker_link" id="wpgmap_have_marker_link">
                <option value="1"><?php esc_html_e('Yes', 'gmap-embed'); ?></option>
                <option value="0" selected="selected"><?php esc_html_e('No', 'gmap-embed'); ?></option>
            </select>
            <br />
            <div id="wpgmap_marker_link_area" style="display: none;">
                <input id="wpgmap_marker_link" name="wpgmap_marker_link"
                    placeholder="<?php esc_attr_e('Enter link here', 'gmap-embed'); ?>" type="text"
                    class="regular-text" style="margin: 5px 0;">
                <br />
                <label>
                    <input type="checkbox" id="wpgmap_marker_link_new_tab" name="wpgmap_marker_link_new_tab"
                        class="alignleft" style="margin: 2px 5px 0px 0px;" />
                    <span class="alignleft"><?php esc_html_e('Open link in new tab', 'gmap-embed'); ?></span>
                </label>
            </div>
        </td>
    </tr>
    <tr>
        <td>

            <label>
                <span
                    class="alignleft"><?php esc_html_e('Open marker description by default', 'gmap-embed'); ?></span>&nbsp;
                <select name="wpgmap_marker_infowindow_show" id="wpgmap_marker_infowindow_show">
                    <option value="1"><?php esc_html_e('Yes', 'gmap-embed'); ?></option>
                    <option value="0" selected="selected">
                        <?php esc_html_e('No, Open description on click', 'gmap-embed'); ?>
                    </option>
                </select>
            </label>
        </td>
    </tr>


    <?php $wpgmap_map_id = (isset($_GET['tag']) && sanitize_text_field(wp_unslash($_GET['tag'])) === 'edit') ? intval(sanitize_text_field(wp_unslash($_GET['id']))) : $wpgmap_map_id; ?>
    <tr>
        <td>
            <button class=" button button-primary button-large wgm_marker_cancel" type="button">
                <i class="dashicons dashicons-no-alt" style="line-height: 1.6;"></i>
                <b><?php esc_html_e('Cancel', 'gmap-embed'); ?></b>
            </button>
            <button class=" button button-primary button-large wpgmap_marker_add" type="button" markerid="0"
                mapid="<?php echo esc_attr($wpgmap_map_id); ?>">
                <i class="dashicons dashicons-location" style="line-height: 1.6;"></i>
                <b><?php esc_html_e('Save Marker', 'gmap-embed'); ?></b>
            </button>
            <span class="spinner alignleft"></span>
        </td>
    </tr>
</table>

<div class="gmap_embed_message_area alignleft wgm-col-full">
    <div id="marker_errors" style="color: red;"></div>
    <div id="marker_success" style="color: green;font-weight: bold;"></div>
</div>