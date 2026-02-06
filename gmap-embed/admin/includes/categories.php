<?php
if (!defined('ABSPATH')) {
	exit;
}

global $wpdb;
// Fetch categories for the parent dropdown
$gmap_embed_admin_categories = wp_cache_get('wgm_categories_list', 'gmap-embed');
if (false === $gmap_embed_admin_categories) {
	// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching, WordPress.DB.PreparedSQL.InterpolatedNotPrepared, PluginCheck.Security.DirectDB.UnescapedDBParameter
	$gmap_embed_admin_categories = $wpdb->get_results("SELECT id, name FROM {$wpdb->prefix}wgm_categories ORDER BY name ASC");
	wp_cache_set('wgm_categories_list', $gmap_embed_admin_categories, 'gmap-embed', 12 * HOUR_IN_SECONDS);
}
?>

<div class="wrap wp-gmap-wrap wgm-categories-page">
    <h1 class="wp-heading-inline"><?php esc_html_e('Categories', 'gmap-embed'); ?></h1>
    <hr class="wp-header-end">

    <?php if (!_wgm_is_premium()) : ?>
        <!-- Pro Upgrade Banner for Categories -->
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 15px 20px; border-radius: 8px; margin: 15px 0; box-shadow: 0 4px 6px rgba(0,0,0,0.1); border-left: 5px solid #ffd700; position: relative; z-index: 100;">
            <div style="display: flex; align-items: center; gap: 15px;">
                <span class="dashicons dashicons-star-filled" style="font-size: 28px; color: #ffd700; width: 28px; height: 28px;"></span>
                <div style="flex: 1;">
                    <h3 style="margin: 0 0 5px 0; color: white; font-size: 16px;"><?php esc_html_e('Unlock Marker Categories (Premium Feature)', 'gmap-embed'); ?></h3>
                    <p style="margin: 0; font-size: 13px; opacity: 0.95; line-height: 1.4;">
                        <?php 
                        echo sprintf(
                            /* translators: %s: Premium version URL. */
                            esc_html__('Organize your markers into categories and filter them on the frontend. This feature and many more are available in the %s.', 'gmap-embed'),
                            '<a href="' . esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=categories-page-banner') . '" target="_blank" style="color: #ffd700; text-decoration: underline; font-weight: bold;">' . esc_html__('Premium Version', 'gmap-embed') . '</a>'
                        );
                        ?>
                    </p>
                </div>
                <a href="<?php echo esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=categories-page-upgrade-btn'); ?>" target="_blank" class="button" style="background: #ffd700; color: #764ba2; border: none; padding: 8px 20px; font-weight: bold; box-shadow: 0 2px 4px rgba(0,0,0,0.2); height: auto; line-height: 1.4; border-radius: 4px;"><?php esc_html_e('Upgrade Now', 'gmap-embed'); ?></a>
            </div>
        </div>
    <?php endif; ?>

    <div class="wgm-row wgm-categories-container <?php echo !_wgm_is_premium() ? 'wgm-locked-feature' : ''; ?>">
        <!-- Add/Edit Category Form -->
        <div class="wgm-col-4 wgm-category-form-col" style="flex: 0 0 350px;">
            <div class="postbox wgm-category-postbox">
                <div class="postbox-header">
                    <h2 class="hndle ui-sortable-handle" id="wgm-category-form-title"><?php esc_html_e('Add New Category', 'gmap-embed'); ?></h2>
                </div>
                <div class="inside wgm-category-inside">
                    <form id="wgm-category-form">
                        <input type="hidden" name="category_id" id="wgm-category-id" value="0">
                        
                        <div class="wgm-field-group">
                            <label for="wgm-category-name"><b><?php esc_html_e('Category Name', 'gmap-embed'); ?></b></label>
                            <input type="text" id="wgm-category-name" name="name" class="regular-text" style="width: 100%;" required <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>>
                        </div>

                        <div class="wgm-field-group">
                            <label><b><?php esc_html_e('Category Icon', 'gmap-embed'); ?></b></label>
                            <div class="wgm-icon-picker-wrap">
                                <img id="wgm-category-icon-preview" src="<?php echo esc_url(WGM_PLUGIN_URL . 'admin/assets/images/markers/default.png'); ?>" width="32" height="32" style="background: #f0f0f0; padding: 2px; border: 1px solid #ddd; border-radius: 4px; object-fit: contain;">
                                <div class="wgm-icon-actions">
                                    <button type="button" class="button" id="wgm-category-icon-upload" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>><?php esc_html_e('Choose Icon', 'gmap-embed'); ?></button>
                                    <button type="button" class="button" id="wgm-category-icon-remove" style="display: none; color: #a00;" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>><?php esc_html_e('Remove', 'gmap-embed'); ?></button>
                                </div>
                                <input type="hidden" name="icon" id="wgm-category-icon-url" value="">
                            </div>
                        </div>

                        <div class="wgm-field-group">
                            <label for="wgm-category-parent"><b><?php esc_html_e('Parent Category', 'gmap-embed'); ?></b></label>
                            <select id="wgm-category-parent" name="parent_id" style="width: 100%;" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>>
                                <option value="0"><?php esc_html_e('None', 'gmap-embed'); ?></option>
                                <?php if ($gmap_embed_admin_categories) : ?>
                                    <?php foreach ($gmap_embed_admin_categories as $gmap_embed_cat) : ?>
                                        <option value="<?php echo intval($gmap_embed_cat->id); ?>"><?php echo esc_html($gmap_embed_cat->name); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="wgm-form-actions">
                            <button type="submit" class="button button-primary" id="wgm-category-submit" <?php echo !_wgm_is_premium() ? 'disabled="disabled"' : ''; ?>>
                                <?php esc_html_e('Save Category', 'gmap-embed'); ?>
                            </button>
                            <button type="button" class="button" id="wgm-category-cancel" style="display: none;">
                                <?php esc_html_e('Cancel', 'gmap-embed'); ?>
                            </button>
                            <span class="spinner" id="wgm-category-spinner"></span>
                        </div>
                    </form>
                </div>
                <?php if (!_wgm_is_premium()) : ?>
                    <div class="wgm-lock-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.2); backdrop-filter: blur(1.1px); border-radius: inherit; z-index: 10;">
                        <span class="dashicons dashicons-lock" style="font-size: 50px; color: #764ba2; width: 50px; height: 50px; opacity: 0.6;"></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Categories List Table -->
        <div class="wgm-col-8 wgm-category-list-col" style="flex: 1;">
            <div class="postbox wgm-category-postbox">
                <div class="postbox-header">
                    <h2 class="hndle ui-sortable-handle"><?php esc_html_e('All Categories', 'gmap-embed'); ?></h2>
                </div>
                <div class="inside wgm-category-inside">
                    <div class="wgm-table-container">
                        <table id="wgm-categories-table" class="display" style="width:100%">
                            <thead>
                                <tr>
                                    <th><?php esc_html_e('ID', 'gmap-embed'); ?></th>
                                    <th><?php esc_html_e('Icon', 'gmap-embed'); ?></th>
                                    <th><?php esc_html_e('Name', 'gmap-embed'); ?></th>
                                    <th><?php esc_html_e('Parent', 'gmap-embed'); ?></th>
                                    <th><?php esc_html_e('Action', 'gmap-embed'); ?></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
                <?php if (!_wgm_is_premium()) : ?>
                    <div class="wgm-lock-overlay" style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.4); backdrop-filter: blur(1.1px); border-radius: inherit; z-index: 10;">
                        <span class="dashicons dashicons-lock" style="font-size: 50px; color: #764ba2; width: 50px; height: 50px; opacity: 0.6;"></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<style>
    .wgm_error { border-color: #d63638 !important; box-shadow: 0 0 2px rgba(214, 54, 56, .8) !important; }
    #wgm-categories-table_wrapper { padding-top: 10px; }
    .wgm-edit-category, .wgm-delete-category { margin-right: 5px !important; }
</style>
