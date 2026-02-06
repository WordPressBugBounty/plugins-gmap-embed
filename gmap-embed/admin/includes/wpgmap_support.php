<?php if (!defined('ABSPATH')) {
	exit;
}

// Get plugin and system info for debug section
$gmap_embed_admin_plugin_data = get_plugin_data(WGM_PLUGIN_PATH . 'srm_gmap_embed.php');
$gmap_embed_admin_plugin_version = isset($gmap_embed_admin_plugin_data['Version']) ? $gmap_embed_admin_plugin_data['Version'] : 'Unknown';
?>

<style>
.wgm-support-page {
	max-width: 1200px;
	margin: 0 auto;
}

/* Hero Section */
.wgm-support-hero {
	background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
	color: #fff;
	padding: 30px 40px;
	border-radius: 12px;
	margin-bottom: 30px;
	display: flex;
	justify-content: space-between;
	align-items: center;
	flex-wrap: wrap;
	gap: 20px;
}
.wgm-support-hero h2 {
	margin: 0 0 8px 0;
	font-size: 24px;
	color: #fff;
}
.wgm-support-hero p {
	margin: 0;
	opacity: 0.9;
	font-size: 14px;
}
.wgm-hero-actions {
	display: flex;
	gap: 10px;
	flex-wrap: wrap;
}
.wgm-hero-btn {
	display: inline-flex;
	align-items: center;
	gap: 6px;
	padding: 10px 18px;
	border-radius: 6px;
	text-decoration: none;
	font-weight: 500;
	font-size: 13px;
	transition: all 0.2s;
}
.wgm-hero-btn.primary {
	background: #fff;
	color: #667eea;
}
.wgm-hero-btn.primary:hover {
	background: #f0f0f0;
}
.wgm-hero-btn.secondary {
	background: rgba(255,255,255,0.2);
	color: #fff;
}
.wgm-hero-btn.secondary:hover {
	background: rgba(255,255,255,0.3);
}

/* Section Headers */
.wgm-section-header {
	margin: 40px 0 20px 0;
	padding-bottom: 10px;
	border-bottom: 2px solid #eee;
}
.wgm-section-header h3 {
	margin: 0;
	font-size: 18px;
	color: #1d2327;
}

/* Documentation Grid */
.wgm-docs-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
	gap: 20px;
	margin-bottom: 30px;
}
.wgm-doc-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 10px;
	padding: 24px;
	transition: all 0.3s;
	text-decoration: none;
	display: block;
}
.wgm-doc-card:hover {
	border-color: #667eea;
	box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
	transform: translateY(-2px);
}
.wgm-doc-card-icon {
	font-size: 32px;
	margin-bottom: 12px;
}
.wgm-doc-card h4 {
	margin: 0 0 8px 0;
	color: #1d2327;
	font-size: 16px;
}
.wgm-doc-card p {
	margin: 0 0 12px 0;
	color: #666;
	font-size: 13px;
	line-height: 1.5;
}
.wgm-doc-card-link {
	color: #667eea;
	font-size: 13px;
	font-weight: 500;
}

/* Support Channels Grid */
.wgm-channels-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
	gap: 20px;
	margin-bottom: 30px;
}
.wgm-channel-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 10px;
	padding: 24px;
	text-align: center;
}
.wgm-channel-card.highlight {
	border-color: #667eea;
	background: linear-gradient(135deg, #f8f9ff 0%, #fff 100%);
}
.wgm-channel-icon {
	width: 50px;
	height: 50px;
	border-radius: 50%;
	display: inline-flex;
	align-items: center;
	justify-content: center;
	font-size: 22px;
	margin-bottom: 15px;
}
.wgm-channel-icon.chat { background: #fee2e2; color: #dc2626; }
.wgm-channel-icon.community { background: #e0e7ff; color: #4f46e5; }
.wgm-channel-icon.premium { background: #fef3c7; color: #d97706; }
.wgm-channel-card h4 {
	margin: 0 0 8px 0;
	font-size: 16px;
	color: #1d2327;
}
.wgm-channel-card p {
	margin: 0 0 15px 0;
	color: #666;
	font-size: 13px;
	line-height: 1.5;
}
.wgm-channel-links {
	display: flex;
	flex-direction: column;
	gap: 8px;
}
.wgm-channel-links a {
	color: #667eea;
	text-decoration: none;
	font-size: 13px;
}
.wgm-channel-links a:hover {
	text-decoration: underline;
}
.wgm-channel-btn {
	display: inline-block;
	padding: 10px 20px;
	background: #667eea;
	color: #fff;
	border-radius: 6px;
	text-decoration: none;
	font-size: 13px;
	font-weight: 500;
	transition: background 0.2s;
}
.wgm-channel-btn:hover {
	background: #5a6fd6;
	color: #fff;
}

/* Video Section */
.wgm-video-section {
	margin-bottom: 30px;
}
.wgm-video-wrapper {
	position: relative;
	padding-bottom: 56.25%;
	height: 0;
	overflow: hidden;
	border-radius: 10px;
	box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
.wgm-video-wrapper iframe {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
	height: 100%;
	border: 0;
}

/* Community Section */
.wgm-community-grid {
	display: grid;
	grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
	gap: 20px;
	margin-bottom: 30px;
}
.wgm-community-card {
	background: #fff;
	border: 1px solid #e0e0e0;
	border-radius: 10px;
	padding: 24px;
	display: flex;
	align-items: flex-start;
	gap: 15px;
}
.wgm-community-icon {
	width: 44px;
	height: 44px;
	border-radius: 10px;
	display: flex;
	align-items: center;
	justify-content: center;
	font-size: 20px;
	flex-shrink: 0;
}
.wgm-community-icon.github { background: #f3f4f6; color: #1f2937; }
.wgm-community-icon.review { background: #fef3c7; color: #d97706; }
.wgm-community-icon.facebook { background: #dbeafe; color: #2563eb; }
.wgm-community-content h4 {
	margin: 0 0 6px 0;
	font-size: 15px;
	color: #1d2327;
}
.wgm-community-content p {
	margin: 0 0 10px 0;
	color: #666;
	font-size: 13px;
	line-height: 1.4;
}
.wgm-community-content a {
	color: #667eea;
	text-decoration: none;
	font-size: 13px;
	font-weight: 500;
}
.wgm-community-content a:hover {
	text-decoration: underline;
}

/* System Info Collapsible */
.wgm-system-info {
	background: #f9fafb;
	border: 1px solid #e0e0e0;
	border-radius: 10px;
	margin-bottom: 30px;
}
.wgm-system-info-header {
	padding: 15px 20px;
	cursor: pointer;
	display: flex;
	justify-content: space-between;
	align-items: center;
}
.wgm-system-info-header h4 {
	margin: 0;
	font-size: 14px;
	color: #1d2327;
}
.wgm-system-info-content {
	display: none;
	padding: 0 20px 20px 20px;
}
.wgm-system-info-content.active {
	display: block;
}
.wgm-system-info-table {
	width: 100%;
	font-size: 13px;
}
.wgm-system-info-table td {
	padding: 8px 0;
	border-bottom: 1px solid #eee;
}
.wgm-system-info-table td:first-child {
	font-weight: 500;
	width: 200px;
	color: #666;
}
.wgm-copy-btn {
	padding: 8px 16px;
	background: #667eea;
	color: #fff;
	border: none;
	border-radius: 6px;
	cursor: pointer;
	font-size: 12px;
	margin-top: 15px;
}
.wgm-copy-btn:hover {
	background: #5a6fd6;
}

@media (max-width: 768px) {
	.wgm-support-hero {
		padding: 20px;
	}
	.wgm-docs-grid,
	.wgm-channels-grid,
	.wgm-community-grid {
		grid-template-columns: 1fr;
	}
}
</style>

<div class="wrap wgm-support-page">
	<h1 class="wp-heading-inline"><?php esc_html_e('Support & Documentation', 'gmap-embed'); ?></h1>
	<?php
	if (!_wgm_is_premium()) {
		echo '<a target="_blank" href="' . esc_url('https://wpgooglemap.com/pricing?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=upgrade-to-pro&utm_content=support-page-upgrade-btn') . '" class="button wgm_btn" style="float:right;width:auto;padding: 5px 7px;font-size: 11px;margin-left:5px;"><i style="line-height: 25px;" class="dashicons dashicons-star-filled"></i> ' . esc_html__('Upgrade ($19 only)', 'gmap-embed') . '</a>';
	}
	?>
	<hr class="wp-header-end">

	<!-- Hero Section -->
	<div class="wgm-support-hero">
		<div>
			<h2><?php esc_html_e('How can we help you?', 'gmap-embed'); ?></h2>
			<p><?php esc_html_e('Find answers in our documentation or get in touch with our support team.', 'gmap-embed'); ?></p>
		</div>
		<div class="wgm-hero-actions">
			<a href="<?php echo esc_url('https://tawk.to/chat/6083e29962662a09efc1acd5/1f41iqarp'); ?>" target="_blank" class="wgm-hero-btn primary">
				<span class="dashicons dashicons-format-chat"></span>
				<?php esc_html_e('Live Chat', 'gmap-embed'); ?>
			</a>
			<a href="<?php echo esc_url('https://wpgooglemap.com/documentation?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=documentation&utm_content=admin-support-hero-docs'); ?>" target="_blank" class="wgm-hero-btn secondary">
				<span class="dashicons dashicons-book"></span>
				<?php esc_html_e('Documentation', 'gmap-embed'); ?>
			</a>
			<a href="<?php echo esc_url('https://wpgooglemap.com/contact-us?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=support&utm_content=admin-support-hero-contact'); ?>" target="_blank" class="wgm-hero-btn secondary">
				<span class="dashicons dashicons-email"></span>
				<?php esc_html_e('Contact Us', 'gmap-embed'); ?>
			</a>
		</div>
	</div>

	<!-- Documentation Categories -->
	<div class="wgm-section-header">
		<h3><?php esc_html_e('📚 Documentation', 'gmap-embed'); ?></h3>
	</div>
	
	<div class="wgm-docs-grid">
		<a href="<?php echo esc_url('https://wpgooglemap.com/docs-category/installation?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=documentation&utm_content=admin-support-doc-category-link'); ?>" target="_blank" class="wgm-doc-card">
			<div class="wgm-doc-card-icon">📦</div>
			<h4><?php esc_html_e('Installation', 'gmap-embed'); ?></h4>
			<p><?php esc_html_e('Get started with plugin setup, API key configuration, and license activation.', 'gmap-embed'); ?></p>
			<span class="wgm-doc-card-link"><?php esc_html_e('Learn more →', 'gmap-embed'); ?></span>
		</a>
		
		<a href="<?php echo esc_url('https://wpgooglemap.com/docs-category/customization?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=documentation&utm_content=admin-support-doc-category-link'); ?>" target="_blank" class="wgm-doc-card">
			<div class="wgm-doc-card-icon">🎨</div>
			<h4><?php esc_html_e('Customization', 'gmap-embed'); ?></h4>
			<p><?php esc_html_e('Style your maps with themes, markers, directions, and custom CSS.', 'gmap-embed'); ?></p>
			<span class="wgm-doc-card-link"><?php esc_html_e('Learn more →', 'gmap-embed'); ?></span>
		</a>
		
		<a href="<?php echo esc_url('https://wpgooglemap.com/docs-category/troubleshooting?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=documentation&utm_content=admin-support-doc-category-link'); ?>" target="_blank" class="wgm-doc-card">
			<div class="wgm-doc-card-icon">🔧</div>
			<h4><?php esc_html_e('Troubleshooting', 'gmap-embed'); ?></h4>
			<p><?php esc_html_e('Fix common issues like map loading errors, API conflicts, and billing problems.', 'gmap-embed'); ?></p>
			<span class="wgm-doc-card-link"><?php esc_html_e('Learn more →', 'gmap-embed'); ?></span>
		</a>
		
		<a href="<?php echo esc_url('https://wpgooglemap.com/docs-category/faq?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=documentation&utm_content=admin-support-doc-category-link'); ?>" target="_blank" class="wgm-doc-card">
			<div class="wgm-doc-card-icon">❓</div>
			<h4><?php esc_html_e('FAQ', 'gmap-embed'); ?></h4>
			<p><?php esc_html_e('Find quick answers to frequently asked questions about the plugin.', 'gmap-embed'); ?></p>
			<span class="wgm-doc-card-link"><?php esc_html_e('Learn more →', 'gmap-embed'); ?></span>
		</a>
		
		<a href="<?php echo esc_url('https://wpgooglemap.com/docs-category/settings?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=documentation&utm_content=admin-support-doc-category-link'); ?>" target="_blank" class="wgm-doc-card">
			<div class="wgm-doc-card-icon">⚙️</div>
			<h4><?php esc_html_e('Settings', 'gmap-embed'); ?></h4>
			<p><?php esc_html_e('Configure global settings, map controls, and advanced options.', 'gmap-embed'); ?></p>
			<span class="wgm-doc-card-link"><?php esc_html_e('Learn more →', 'gmap-embed'); ?></span>
		</a>
	</div>

	<!-- Support Channels -->
	<div class="wgm-section-header">
		<h3><?php esc_html_e('💬 Get Support', 'gmap-embed'); ?></h3>
	</div>
	
	<div class="wgm-channels-grid">
		<div class="wgm-channel-card highlight">
			<div class="wgm-channel-icon chat">
				<span class="dashicons dashicons-format-chat"></span>
			</div>
			<h4><?php esc_html_e('Live Chat', 'gmap-embed'); ?></h4>
			<p><?php esc_html_e('Get instant help from our support team. Available 24/7 for quick questions and guidance.', 'gmap-embed'); ?></p>
			<a href="<?php echo esc_url('https://tawk.to/chat/6083e29962662a09efc1acd5/1f41iqarp'); ?>" target="_blank" class="wgm-channel-btn">
				<?php esc_html_e('Start Chat', 'gmap-embed'); ?>
			</a>
		</div>
		
		<div class="wgm-channel-card">
			<div class="wgm-channel-icon community">
				<span class="dashicons dashicons-groups"></span>
			</div>
			<h4><?php esc_html_e('Community Support', 'gmap-embed'); ?></h4>
			<p><?php esc_html_e('Get help from the community and our team on WordPress.org forum.', 'gmap-embed'); ?></p>
			<div class="wgm-channel-links">
				<a href="<?php echo esc_url('https://wordpress.org/support/plugin/gmap-embed/#new-topic-0'); ?>" target="_blank">
					→ <?php esc_html_e('WordPress.org Forum', 'gmap-embed'); ?>
				</a>
				<a href="<?php echo esc_url('https://github.com/milonfci/gmap-embed-lite/issues'); ?>" target="_blank">
					→ <?php esc_html_e('GitHub Issues', 'gmap-embed'); ?>
				</a>
			</div>
		</div>
		
		<div class="wgm-channel-card">
			<div class="wgm-channel-icon premium">
				<span class="dashicons dashicons-star-filled"></span>
			</div>
			<h4><?php esc_html_e('Priority Support', 'gmap-embed'); ?></h4>
			<p><?php esc_html_e('Premium users get priority support with faster response times.', 'gmap-embed'); ?></p>
			<a href="<?php echo esc_url('https://wpgooglemap.com/contact-us?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=support&utm_content=admin-support-priority-btn'); ?>" target="_blank" class="wgm-channel-btn">
				<?php esc_html_e('Contact Support', 'gmap-embed'); ?>
			</a>
		</div>
	</div>

	<!-- Video Tutorial -->
	<div class="wgm-section-header">
		<h3><?php esc_html_e('🎬 Video Tutorial', 'gmap-embed'); ?></h3>
	</div>
	
	<div class="wgm-video-section">
		<div class="wgm-video-wrapper">
			<iframe src="<?php echo esc_url('https://www.youtube.com/embed/ErRy5lqTPjY'); ?>"
				title="<?php esc_attr_e('WP Google Map Tutorial', 'gmap-embed'); ?>"
				allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
				allowfullscreen></iframe>
		</div>
	</div>

	<!-- Community & Contribute -->
	<div class="wgm-section-header">
		<h3><?php esc_html_e('🤝 Community & Contribute', 'gmap-embed'); ?></h3>
	</div>
	
	<div class="wgm-community-grid">
		<div class="wgm-community-card">
			<div class="wgm-community-icon github">
				<span class="dashicons dashicons-editor-code"></span>
			</div>
			<div class="wgm-community-content">
				<h4><?php esc_html_e('Report a Bug', 'gmap-embed'); ?></h4>
				<p><?php esc_html_e('Found a bug? Help us improve by reporting it on GitHub.', 'gmap-embed'); ?></p>
				<a href="<?php echo esc_url('https://github.com/milonfci/gmap-embed-lite/issues/new'); ?>" target="_blank">
					<?php esc_html_e('Create Issue →', 'gmap-embed'); ?>
				</a>
			</div>
		</div>
		
		<div class="wgm-community-card">
			<div class="wgm-community-icon review">
				<span class="dashicons dashicons-star-filled"></span>
			</div>
			<div class="wgm-community-content">
				<h4><?php esc_html_e('Leave a Review', 'gmap-embed'); ?></h4>
				<p><?php esc_html_e('Enjoying the plugin? Your review helps others discover it.', 'gmap-embed'); ?></p>
				<a href="<?php echo esc_url('https://wordpress.org/support/plugin/gmap-embed/reviews/#new-post'); ?>" target="_blank">
					<?php esc_html_e('Write Review →', 'gmap-embed'); ?>
				</a>
			</div>
		</div>
		
		<div class="wgm-community-card">
			<div class="wgm-community-icon facebook">
				<span class="dashicons dashicons-facebook"></span>
			</div>
			<div class="wgm-community-content">
				<h4><?php esc_html_e('Join Community', 'gmap-embed'); ?></h4>
				<p><?php esc_html_e('Connect with other users and get tips on Facebook.', 'gmap-embed'); ?></p>
				<a href="<?php echo esc_url('https://www.facebook.com/Google-Map-SRM-100856491527309'); ?>" target="_blank">
					<?php esc_html_e('Join Facebook →', 'gmap-embed'); ?>
				</a>
			</div>
		</div>
	</div>

	<!-- System Information -->
	<div class="wgm-system-info">
		<div class="wgm-system-info-header" onclick="this.nextElementSibling.classList.toggle('active'); this.querySelector('.dashicons').classList.toggle('dashicons-arrow-down-alt2'); this.querySelector('.dashicons').classList.toggle('dashicons-arrow-up-alt2');">
			<h4><span class="dashicons dashicons-info"></span> <?php esc_html_e('System Information (for debugging)', 'gmap-embed'); ?></h4>
			<span class="dashicons dashicons-arrow-down-alt2"></span>
		</div>
		<div class="wgm-system-info-content">
			<table class="wgm-system-info-table">
				<tr>
					<td><?php esc_html_e('Plugin Version', 'gmap-embed'); ?></td>
					<td><?php echo esc_html($gmap_embed_admin_plugin_version); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e('WordPress Version', 'gmap-embed'); ?></td>
					<td><?php echo esc_html(get_bloginfo('version')); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e('PHP Version', 'gmap-embed'); ?></td>
					<td><?php echo esc_html(phpversion()); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e('Active Theme', 'gmap-embed'); ?></td>
					<td><?php echo esc_html(wp_get_theme()->get('Name')); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e('Premium Status', 'gmap-embed'); ?></td>
					<td><?php echo _wgm_is_premium() ? esc_html__('Active', 'gmap-embed') : esc_html__('Free Version', 'gmap-embed'); ?></td>
				</tr>
				<tr>
					<td><?php esc_html_e('Memory Limit', 'gmap-embed'); ?></td>
					<td><?php echo esc_html(WP_MEMORY_LIMIT); ?></td>
				</tr>
			</table>
			<button type="button" class="wgm-copy-btn" id="wgm-copy-system-info">
				<span class="dashicons dashicons-clipboard" style="font-size: 14px; line-height: 1; vertical-align: middle;"></span>
				<?php esc_html_e('Copy to Clipboard', 'gmap-embed'); ?>
			</button>
		</div>
	</div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	var copyBtn = document.getElementById('wgm-copy-system-info');
	if (copyBtn) {
		copyBtn.addEventListener('click', function() {
			var info = 'Plugin: <?php echo esc_js($gmap_embed_admin_plugin_version); ?>\n' +
				'WordPress: <?php echo esc_js(get_bloginfo('version')); ?>\n' +
				'PHP: <?php echo esc_js(phpversion()); ?>\n' +
				'Theme: <?php echo esc_js(wp_get_theme()->get('Name')); ?>\n' +
				'Premium: <?php echo _wgm_is_premium() ? 'Yes' : 'No'; ?>\n' +
				'Memory: <?php echo esc_js(WP_MEMORY_LIMIT); ?>';
			
			if (navigator.clipboard && navigator.clipboard.writeText) {
				navigator.clipboard.writeText(info).then(function() {
					copyBtn.innerHTML = '<span class="dashicons dashicons-yes" style="font-size: 14px; line-height: 1; vertical-align: middle;"></span> <?php esc_html_e('Copied!', 'gmap-embed'); ?>';
					setTimeout(function() {
						copyBtn.innerHTML = '<span class="dashicons dashicons-clipboard" style="font-size: 14px; line-height: 1; vertical-align: middle;"></span> <?php esc_html_e('Copy to Clipboard', 'gmap-embed'); ?>';
					}, 2000);
				});
			} else {
				// Fallback for older browsers
				var textarea = document.createElement('textarea');
				textarea.value = info;
				document.body.appendChild(textarea);
				textarea.select();
				document.execCommand('copy');
				document.body.removeChild(textarea);
				copyBtn.innerHTML = '<span class="dashicons dashicons-yes" style="font-size: 14px; line-height: 1; vertical-align: middle;"></span> <?php esc_html_e('Copied!', 'gmap-embed'); ?>';
				setTimeout(function() {
					copyBtn.innerHTML = '<span class="dashicons dashicons-clipboard" style="font-size: 14px; line-height: 1; vertical-align: middle;"></span> <?php esc_html_e('Copy to Clipboard', 'gmap-embed'); ?>';
				}, 2000);
			}
		});
	}
});
</script>