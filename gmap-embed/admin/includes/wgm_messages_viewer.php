<?php
if ( isset( $_GET['message'] ) ) {
	?>
	<div class="message">
		<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible">
			<p>
				<strong>
					<?php
					$allowed_html = [
						'a'     => [
							'class' => [],
							'id'    => [],
							'title'    => [],
							'href'    => [],
						],
                        'i'=>[
	                        'style'    => []
                        ],
						'span'     => [
							'class' => [],
							'style'    => []
						],

					];
					$message_status = sanitize_text_field( wp_unslash( $_GET['message'] ) );
					switch ( $message_status ) {
						case 1:
							echo __( 'Map has been created Successfully. <a href="' . esc_url( 'https://youtu.be/9KZOUJ9Gdv8?t=255' ) . '" target="_blank"> See How to use >></a>', 'gmap-embed' );
							break;
						case 3:
							echo __( 'API key updated Successfully, Please click on <a href="' . esc_url( admin_url( 'admin.php?page=wpgmapembed-new' ) ) . '"><i style="color: green;">Add New</i></a> menu to add new map.', 'gmap-embed' );
							break;
						case 4:
							echo wp_kses( __( $message, 'gmap-embed' ),$allowed_html);
							break;
						case - 1:
							echo __( 'Map Deleted Successfully.', 'gmap-embed' );
							break;
					}
					?>
				</strong>
			</p>
			<button type="button" class="notice-dismiss"><span
						class="screen-reader-text">Dismiss this notice.</span>
			</button>
		</div>
	</div>
	<?php
}
?>
