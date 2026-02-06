<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wpgmap-marker-listing" style="max-width: <?php echo esc_attr($wpgmap_map_width); ?>; margin: 20px auto 0;">
    <ul id="wpgmap_marker_list_<?php echo esc_attr($count); ?>" class="wpgmap-marker-list" role="list">
        <!-- Marker items will be appended here by JS -->
    </ul>
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var markerData_<?php echo esc_attr($count); ?> = {
            action: 'wpgmapembed_p_get_markers_by_map_id',
            _wgm_p_nonce: '<?php echo esc_js(wp_create_nonce('wgm_marker_rander')); ?>',
            data: {
                map_id: '<?php echo intval($wgm_map_id); ?>'
            }
        };
        var ajaxurl_<?php echo esc_attr($count); ?> = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
        var $ul = $('#wpgmap_marker_list_<?php echo esc_attr($count); ?>');
        var markersCache_<?php echo esc_attr($count); ?> = [];

        function renderList(list) {
            $ul.empty();
            if (!list || list.length === 0) {
                $ul.append('<li class="wpgmap-no-results"><?php echo esc_js(__('No markers found.', 'gmap-embed')); ?></li>');
                return;
            }
            list.forEach(function (marker, idx) {
                var icon = 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png';
                if (marker.icon) {
                    if (marker.icon !== '') {
                        icon = marker.icon;
                    }
                }
                var title = marker.marker_name ? marker.marker_name : '';
                var desc = marker.marker_desc ? marker.marker_desc.replace(/(<([^>]+)>)/gi, "") : '';
                var address = marker.address ? marker.address : '';
                var liClass = (idx % 2 === 0) ? 'odd' : 'even';
                var safeTitle = $('<div>').text(title).html();
                var safeDesc = $('<div>').text(desc).html();
                var safeAddress = $('<div>').text(address).html();

                var mIdx = marker.marker_idx !== undefined ? marker.marker_idx : idx;
                var html = '<li tabindex="0" class="wgm-item-card" data-marker-idx="' + mIdx + '" role="listitem" aria-label="' + safeTitle + '">';
                
                // 1. Title
                html += '<h3 class="wgm-item-title">' + safeTitle + '</h3>';
                
                // 2. Meta Row (Icon + Address + Distance)
                html += '<div class="wgm-item-meta">';
                html += '<div class="wgm-item-meta-item">';
                html += '<img class="wgm-item-meta-icon" src="' + icon + '" alt="" loading="lazy" width="18" height="18" />';
                if (address) {
                    html += '<span class="wgm-item-address">' + safeAddress + '</span>';
                }
                html += '</div>';
                
                if (marker.distance) {
                    html += '<span class="wgm-marker-distance-badge">' + marker.distance + '</span>';
                }
                html += '</div>';

                // 3. Description
                if (safeDesc) {
                    html += '<p class="wgm-item-desc">' + safeDesc + '</p>';
                }
                
                // 4. Action Row
                html += '<div class="wgm-item-actions">';
                if (wgm_config_<?php echo esc_attr($count); ?>.enable_show_on_map_icon) {
                     html += '<button type="button" class="wgm-item-action-btn wgm-action-primary wpgmap-show-btn" data-idx="' + mIdx + '" title="<?php echo esc_attr__('Show on Map', 'gmap-embed'); ?>">';
                     html += '<svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"></path></svg>';
                     html += '</button>';
                }
                if (wgm_config_<?php echo esc_attr($count); ?>.enable_get_direction_icon) {
                    html += '<button type="button" class="wgm-item-action-btn wgm-get-direction-btn" data-idx="' + mIdx + '" title="<?php echo esc_attr__('Directions', 'gmap-embed'); ?>">';
                    html += '<svg viewBox="0 0 24 24"><path d="M21.71 11.29l-9-9c-.39-.39-1.02-.39-1.41 0l-9 9c-.39.39-.39 1.02 0 1.41l9 9c.39.39 1.02.39 1.41 0l9-9c.39-.38.39-1.01 0-1.41zM14 14.5V12h-4v3H8.5v-4.5c0-.55.45-1 1-1H14V7l4 4-4 4.5z"></path></svg>';
                    html += '</button>';
                }
                html += '</div>';

                html += '</li>';
                $ul.append(html);
            });
        }

        // Fetch markers and render marker listing
        $.post(ajaxurl_<?php echo esc_attr($count); ?>, markerData_<?php echo esc_attr($count); ?>, function (response) {
            try {
                var res = typeof response === 'string' ? JSON.parse(response) : response;
                markersCache_<?php echo esc_attr($count); ?> = res.markers || [];
                renderList(markersCache_<?php echo esc_attr($count); ?>);
            } catch (e) {
                $ul.empty().append('<li class="wpgmap-no-results"><?php echo esc_js(__('Unable to load markers.', 'gmap-embed')); ?></li>');
            }
        }).fail(function () {
            $ul.empty().append('<li class="wpgmap-no-results"><?php echo esc_js(__('Failed to load markers.', 'gmap-embed')); ?></li>');
        });

        // Delegated events for performance

        // Click on list item: pan + open infowindow
        $ul.on('click', 'li, .wpgmap-show-btn, .wpgmap-focus-btn', function (e) {
            var $target = $(e.currentTarget);
            // If a button inside li was clicked, find idx from button
            var idx = $target.data('idx');
            if (typeof idx === 'undefined') {
                idx = $(this).closest('li').data('marker-idx');
            }
            idx = parseInt(idx, 10);
            if (isNaN(idx)) return;
            var mapInstance = window['wgm_map_<?php echo esc_attr($count); ?>'];
            var markerObj = null;
            if (window.custom_markers) {
                if (window.custom_markers[<?php echo esc_attr($count); ?>]) {
                    markerObj = window.custom_markers[<?php echo esc_attr($count); ?>][idx];
                }
            }
            var infoWindow = null;
            if (window.custom_marker_infowindows) {
                 if (window.custom_marker_infowindows[<?php echo esc_attr($count); ?>]) {
                     infoWindow = window.custom_marker_infowindows[<?php echo esc_attr($count); ?>][idx];
                 }
            }
            if (!mapInstance || !markerObj) return;

            // If clicked the "Focus" ghost button, just pan & zoom without opening link-handlers
            if ($(e.target).hasClass('wpgmap-focus-btn')) {
                mapInstance.panTo(markerObj.getPosition());
                mapInstance.setZoom(15);
                if (infoWindow) infoWindow.open({ anchor: markerObj, shouldFocus: false });
                if (markerObj.getAnimation) {
                    if (markerObj.setAnimation) {
                        markerObj.setAnimation(google.maps.Animation.BOUNCE);
                    }
                }
                setTimeout(function () { 
                    if (markerObj.setAnimation) {
                        markerObj.setAnimation(null); 
                    }
                }, 700);
                return;
            }

            // Default action: show on map and open infowindow
            if (window.custom_marker_infowindows && window.custom_marker_infowindows[<?php echo esc_attr($count); ?>]) {
                window.custom_marker_infowindows[<?php echo esc_attr($count); ?>].forEach(function(iw){ if(iw) iw.close(); });
            }

            mapInstance.panTo(markerObj.getPosition());
            mapInstance.setZoom(15);
            if (infoWindow) {
                infoWindow.open({ anchor: markerObj, shouldFocus: false });
            }
        });

        // Keyboard support: Enter or Space on list items triggers click
        $ul.on('keydown', 'li', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            } else if (e.key === 'ArrowDown') {
                e.preventDefault();
                $(this).next().focus();
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                $(this).prev().focus();
            }
        });

        // Toggle "read more" when desc clicked (for long descriptions)
        $ul.on('click', '.wpgmap-marker-desc', function (e) {
            var $d = $(this);
            $d.toggleClass('expanded');
        });



        // Listen for Advanced Map Search Update
        $(window).on('wgm_search_update_<?php echo esc_attr($count); ?>', function(e, data) {
            if (data) {
                if (data.markers) {
                renderList(data.markers);
            }
            }
        });

    });
</script>