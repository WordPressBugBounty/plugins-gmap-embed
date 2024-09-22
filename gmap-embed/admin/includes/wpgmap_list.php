<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
require_once WGM_PLUGIN_PATH . '/includes/helper.php';
?>
<script type="text/javascript">
	var wgp_api_key = '<?php echo esc_html( get_option( 'wpgmap_api_key' ) ); ?>';
</script>
<div class="wrap">
	<h1 class="wp-heading-inline"><?php esc_html_e( 'All Maps', 'gmap-embed' ); ?></h1>
	<?php
	if ( _wgm_can_add_new_map() ) {
		?>
		<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpgmapembed-new' ) ); ?>" class="page-title-action">Add New</a>
		<?php
	} else {
		echo '<a href="#" class="page-title-action wgm_enable_premium" style="opacity: .3" data-notice="' . esc_html__( sprintf( __( 'You need to upgrade to the <a target="_blank" href="%s">Premium</a> Version to <b> Create Unlimited Maps</b>.', 'gmap-embed' ), esc_url( 'https://wpgooglemap.com/pricing?utm_source=admin_map_list&utm_medium=admin_link&utm_campaign=add_new_map' ) ) ) . '">Add New</a><sup class="wgm-pro-label">Pro</sup>';
	}
	if ( ! _wgm_is_premium() ) {
		echo '<a target="_blank" href="' . esc_url( 'https://wpgooglemap.com/pricing?utm_source=admin_map_list&utm_medium=admin_link&utm_campaign=header_menu' ) . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-left:5px;"><i style="line-height: 25px;" class="dashicons dashicons-star-filled"></i> Upgrade ($29 only)</a>';
	}
	echo '<a target="_blank" href="' . esc_url( 'https://tawk.to/chat/6083e29962662a09efc1acd5/1f41iqarp' ) . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-right:5px;background-color: #cb5757 !important;color: white !important;"><i style="line-height: 28px;" class="dashicons dashicons-format-chat"></i> ' . esc_html__( 'LIVE Chat', 'gmap-embed' ) . '</a>';
	echo '<a href="' . esc_url( admin_url( 'admin.php?page=wpgmapembed-support' ) ) . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-right:5px;"><i style="line-height: 25px;" class="dashicons  dashicons-editor-help"></i> ' . esc_html__( 'Documentation', 'gmap-embed' ) . '</a>';
	?>
	<hr class="wp-header-end">
	<div id="gmap_container_inner">
		<?php require_once WGM_PLUGIN_PATH . 'admin/includes/wgm_messages_viewer.php'; ?>
		<!---------------------------Maps List-------------->
		<div id="wgm_all_maps" style="padding:5px;">
			<table id="wgm_map_list_dt" class="stripe hover row-border order-column" style="width:100%">
				<thead>
				<tr style="text-align: left;">
					<th style="width: 6% !important;">ID</th>
					<th style="min-width: 20%;">Title</th>
					<th style="width: 6% !important;">Type</th>
					<th style="width: 6% !important;">Width</th>
					<th style="width: 6% !important;">Height</th>
					<th style="width: 15% !important;">Shortcode</th>
					<th style="min-width: 12% !important;">Action</th>
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
    font-family: arial;">Copied to Clipboard</div>
    <span style="font-size: small;font-style: italic;float: right;font-size:10px;">
<?php echo 'v.'.WGM_PLUGIN_VERSION;?>
    </span>
