<div class="wgm_setup_wizard_container">
    <div class="wgm_setup_wizard_wrap">

        <div class="wgm_setup_wizard_header">
            <img class="wgm_logo" src="<?php echo esc_url(plugins_url('../assets/images/gmap_embed_logo.jpg', __FILE__)); ?>" />
            <span class="wgm_plugin_name"><?php esc_html_e('Quick Setup Wizard', 'gmap-embed'); ?></span>
        </div>

        <ul class="wgm_setup_wizard_steps wgm_four" data-step="0">
            <li class="wgm_step">
                <div class="wgm_icon">
                    <i class="fas fa-key"></i>
                </div>
                <div class="wgm_step_name"><?php esc_html_e('CREATE YOUR API KEY', 'gmap-embed'); ?></div>
            </li>
            <li class="wgm_step">
                <div class="wgm_icon">
                    <i class="fas fa-language"></i>
                </div>
                <div class="wgm_step_name"><?php esc_html_e('MAP LANGUAGE & REGION', 'gmap-embed'); ?></div>
            </li>
        </ul>

        <div class="wgm_setup_body">
            <form class="wgm_setup_wizard_form" method="post" action="#">
                <!--Get API key-->
                <div class="wgm_setup_content wgm_box">
                    <div class="wgm_row">
                        <div class="wgm-col-full">
                            <span class="wgm_heading"><?php esc_html_e('Create an API Key from Google Maps Platform', 'gmap-embed'); ?></span>
                            <p class="wgm_text_center" style="margin-top: 20px;"><?php esc_html_e('Just click on the following button and follow steps, once done copy the API key', 'gmap-embed'); ?></p>
                        </div>

                        <div class="wgm-col-full" style="text-align: center;margin-top: 30px;margin-bottom: 20px;">
                            <div class="button button-secondary wgm_api_create_gplatform" title="Powered by Google Maps Platform" style="height: 40px;line-height: 40px;">
                                <img src="<?php echo esc_url(WGM_PLUGIN_URL . 'admin/assets/images/wgmp-platform-icon.png'); ?>" style="max-width: 30px; vertical-align: middle;">

                                <?php esc_html_e(' Get an API Key', 'gmap-embed'); ?>
                            </div>
                        </div>

                        <div class="wgm-col-full" style="margin-bottom: 20px;margin-top: 20px;">
                            <span class="wgm_heading" style="font-size: 20px;"><?php esc_html_e('Once done, enter your API key below', 'gmap-embed'); ?></span>
                        </div>

                        <div class="wgm-col-full" style="text-align: center">
                            <div class="wgm_w_60">
                                <input type="text" name="wgm_key" style=" text-align: center;width: 400px;padding: 3px 0;" autofocus placeholder="Enter your API KEY here" value="<?php echo esc_html(get_option('wpgmap_api_key')); ?>" size="60" id="wgm_key" />
                            </div>

                            <p class="wgm_text_center" style="margin-top: 20px;"><?php esc_html_e('This process managed by Google Cloud Platform and takes few minutes only!', 'gmap-embed'); ?></p>
                        </div>
                    </div>
                    <div class="wgm-col-full" style="text-align: center">
                        &nbsp;&nbsp;If you face any issues, please review our <a href="<?php echo esc_url('https://wpgooglemap.com/documentation/wp-google-map-quick-installation?utm_source=admin_setup_wizard&utm_medium=admin_link&utm_campaign=setup_wizard'); ?>" target="_blank"><i class="fas fa-external-link-alt"></i> <?php esc_html_e('Documentation', 'gmap-embed'); ?>
                        </a>
                    </div>
                </div>
                <!-- Language and Regional Setup-->
                <div id="wpgmap_lang_regional" class="wgm_setup_content wgm_box">
                    <div class="wgm_row">
                        <div class="wgm-col-full">
                            <span class="wgm_heading"><?php esc_html_e('Language and Regional Setup', 'gmap-embed'); ?></span>
                            <p class="wgm_text_center wgm_mb_40"><?php esc_html_e('You can customize your Google Map title & contents by setting up Language and Regional setup', 'gmap-embed'); ?></p>
                        </div>
                        <div class="wgm-col-full wgm_d_flex wgm_mb_15">
                            <label><?php esc_html_e('Map Language:', 'gmap-embed'); ?></label>
                            <div class="wgm_w_60">
                                <select id="wgm_gmap_lng" name="srm_gmap_lng" class="regular-text">
                                    <?php
                                    $wpgmap_languages = gmap_embed_get_languages();
                                    if (count($wpgmap_languages) > 0) {
                                        foreach ($wpgmap_languages as $lng_key => $language) {
                                            $selected = '';
                                            if (get_option('srm_gmap_lng', 'en') === $lng_key) {
                                                $selected = 'selected';
                                            }
                                            echo "<option value='" . esc_attr($lng_key) . "' " . esc_attr($selected) . '>' . esc_html($language) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <p class="description" id="tagline-description" style="font-style: italic;">
                                    <?php esc_html_e('Choose your desired map language', 'gmap-embed'); ?>
                                </p>
                            </div>
                        </div>
                        <br>
                        <div class="wgm-col-full wgm_d_flex">
                            <label><?php esc_html_e('Regional Area:', 'gmap-embed'); ?></label>
                            <div class="wgm_w_60">
                                <select id="wgm_region" name="wgm_region" class="regular-text">
                                    <?php
                                    $wpgmap_regions = gmap_embed_get_regions();
                                    if (count($wpgmap_regions) > 0) {
                                        foreach ($wpgmap_regions as $region_key => $region) {
                                            $selected = '';
                                            if (get_option('srm_gmap_region', 'US') === $region_key) {
                                                $selected = 'selected';
                                            }
                                            echo "<option value='" . esc_attr($region_key) . "' " . esc_attr($selected) . '>' . esc_html($region) . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                                <p class="description" id="tagline-description" style="font-style: italic;">
                                    <?php esc_html_e('Choose your regional area', 'gmap-embed'); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="wgm_setup_footer">
            <button id="wgm_prev" class="button wgm_btn" style="display: none;">
                &lt; <?php esc_html_e('Previous', 'gmap-embed'); ?></button>
            <button id="wgm_next" class="button wgm_btn" style="display: inline;"><?php esc_html_e('Next', 'gmap-embed'); ?> &gt;
            </button>
            <button id="wgm_save" style="display: none" class="button wgm_btn wpgmap-setup-wizard-save"><?php esc_html_e('Finish', 'gmap-embed'); ?></button>
            <div class="wgm-col-full wgm_d_flex">
                <a href="<?php echo esc_url(admin_url() . 'admin.php?page=wpgmapembed'); ?>">Skip Setup Wizard</a>
            </div>
        </div>
    </div>
</div>