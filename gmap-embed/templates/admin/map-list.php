<?php
if (!defined('ABSPATH')) {
	exit;
}
?>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e('All Maps', 'gmap-embed'); ?></h1>
	<?php
	if (_wgm_can_add_new_map()) {
		?>
		<a href="<?php echo esc_url(admin_url('admin.php?page=wpgmapembed-new')); ?>"
			class="page-title-action"><?php esc_html_e('Add New', 'gmap-embed'); ?></a>
		<?php
	} else {
		// translators: %s: Premium version URL.
		echo '<a href="#" class="page-title-action wgm_enable_premium" style="opacity: .3" data-notice="' . esc_html(sprintf(__('You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b> Create Unlimited Maps</b>.', 'gmap-embed'), esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=map-list-add-new-lock'))) . '">' . esc_html__('Add New', 'gmap-embed') . '</a><sup class="wgm-pro-label">' . esc_html__('Pro', 'gmap-embed') . '</sup>';
	}
	if (!_wgm_is_premium()) {
		echo '<a target="_blank" href="' . esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=map-list-header-upgrade-btn') . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-left:5px;"><i style="line-height: 25px;" class="dashicons dashicons-star-filled"></i> ' . esc_html__('Upgrade ($19 only)', 'gmap-embed') . '</a>';
	}
	echo '<a target="_blank" href="' . esc_url('https://tawk.to/chat/6083e29962662a09efc1acd5/1f41iqarp') . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-right:5px;background-color: #cb5757 !important;color: white !important;"><i style="line-height: 28px;" class="dashicons dashicons-format-chat"></i> ' . esc_html__('LIVE Chat', 'gmap-embed') . '</a>';
	echo '<a href="' . esc_url(admin_url('admin.php?page=wpgmapembed-support')) . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-right:5px;"><i style="line-height: 25px;" class="dashicons  dashicons-editor-help"></i> ' . esc_html__('Documentation', 'gmap-embed') . '</a>';
	?>
	<hr class="wp-header-end">
	<div id="gmap_container_inner">
		<?php require_once WGM_PLUGIN_PATH . 'admin/includes/wgm_messages_viewer.php'; ?>
		<!---------------------------Maps List-------------->
		<div id="wgm_all_maps" style="padding:5px;">
			<table id="wgm_map_list_dt" class="stripe hover row-border order-column" style="width:100%">
				<thead>
					<tr style="text-align: left;">
						<th style="width: 6% !important;"><?php esc_html_e('ID', 'gmap-embed'); ?></th>
						<th style="min-width: 20%;"><?php esc_html_e('Title', 'gmap-embed'); ?></th>
						<th style="width: 6% !important;"><?php esc_html_e('Type', 'gmap-embed'); ?></th>
						<th style="width: 6% !important;"><?php esc_html_e('Width', 'gmap-embed'); ?></th>
						<th style="width: 6% !important;"><?php esc_html_e('Height', 'gmap-embed'); ?></th>
						<th style="width: 15% !important;"><?php esc_html_e('Shortcode', 'gmap-embed'); ?></th>
						<th style="min-width: 12% !important;"><?php esc_html_e('Action', 'gmap-embed'); ?></th>
					</tr>
				</thead>
				<tbody>
				</tbody>
			</table>
		</div>
	</div>
	<div id="copy_to_clipboard_toaster" style="bottom: 0; display: none;
	right: 0;
	position: fixed;
	background-color: #e15c10;
	color: white;
	font-size: 13px;
	padding: 5px;
	border-radius: 2px;
	z-index: 999;
	box-shadow: 0 0 5px gray;
	font-family: arial;"><?php esc_html_e('Copied to Clipboard', 'gmap-embed'); ?></div>
	<span style="font-size: small;font-style: italic;float: right;font-size:10px;">
		<?php echo 'v.' . esc_html(WGM_PLUGIN_VERSION); ?>
	</span>
</div>
