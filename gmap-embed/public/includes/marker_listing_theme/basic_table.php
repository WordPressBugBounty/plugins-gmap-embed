<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wpgmap-marker-listing" style="max-width: <?php echo esc_attr($wpgmap_map_width); ?>; margin: 20px auto 0;">
    <div class="wpgmap-marker-table-wrapper">
        <table id="wpgmap_marker_table_<?php echo esc_attr($count); ?>" class="wpgmap-marker-table"
            aria-describedby="wpgmap_marker_search_<?php echo esc_attr($count); ?>">
            <caption class="screen-reader-text"><?php esc_html_e('Map markers', 'gmap-embed'); ?></caption>
            <thead style="display:none;">
                <tr>
                    <th scope="col">Info</th>
                    <th scope="col">Image</th>
                </tr>
            </thead>
            <tbody>
                <tr class="wpgmap-marker-loading">
                    <td colspan="6">
                        <div class="wpgmap-spinner" aria-hidden="true">
                        </div>
                        <?php esc_html_e('Loading markers...', 'gmap-embed'); ?>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
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
        var $table_<?php echo esc_attr($count); ?> = $('#wpgmap_marker_table_<?php echo esc_attr($count); ?>');
        var $tbody_<?php echo esc_attr($count); ?> = $table_<?php echo esc_attr($count); ?>.find('tbody');
        var markersCache_<?php echo esc_attr($count); ?> = [];

        function renderRows(list) {
            $tbody_<?php echo esc_attr($count); ?>.empty();
            if (!list || list.length === 0) {
                $tbody_<?php echo esc_attr($count); ?>.append('<tr><td colspan="6" class="wpgmap-no-results"><?php echo esc_js(__('No markers found.', 'gmap-embed')); ?></td></tr>');
                return;
            }
            list.forEach(function (marker, idx) {
                var icon = 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png';
                if (marker.icon) {
                    if (marker.icon !== '') {
                        icon = marker.icon;
                    }
                }
                var image = '';
                if (marker.marker_image) {
                    if (marker.marker_image !== '') {
                        image = marker.marker_image;
                    }
                }
                var title = marker.marker_name ? marker.marker_name : '';
                var desc = marker.marker_desc ? marker.marker_desc.replace(/(<([^>]+)>)/gi, "") : '';
                var address = marker.address ? marker.address : '';
                //var distance = marker.distance ? marker.distance : '';
                var trClass = (idx % 2 === 0) ? 'odd' : 'even';
                var safeTitle = $('<div>').text(title).html();
                var safeDesc = $('<div>').text(desc).html();
                var safeAddress = $('<div>').text(address).html();
                //var safeDistance = $('<div>').text(distance).html();
                var mIdx = marker.marker_idx !== undefined ? marker.marker_idx : idx;
                var html = '<tr tabindex="0" data-marker-idx="' + mIdx + '" role="row" aria-label="' + safeTitle + '">';
                
                html += '<td style="padding: 15px !important;">';
                html += '<div class="wgm-item-row" style="display: flex !important; gap: 20px !important; align-items: flex-start !important;">';
                
                // 1. Content
                html += '<div class="wgm-item-content" style="flex: 1 !important; display: flex !important; flex-direction: column !important; gap: 10px !important;">';
                html += '<h3 class="wgm-item-title">' + safeTitle + '</h3>';
                
                html += '<div class="wgm-item-meta">';
                html += '<div class="wgm-item-meta-item">';
                html += '<img src="' + icon + '" alt="" loading="lazy" width="18" height="18" />';
                if (address) {
                    html += '<span>' + safeAddress + '</span>';
                }
                html += '</div>';
                if (marker.distance) {
                    html += '<span class="wgm-marker-distance-badge">' + marker.distance + '</span>';
                }
                html += '</div>';

                if (safeDesc) {
                    html += '<p class="wgm-item-desc">' + safeDesc + '</p>';
                }
                
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
                html += '</div>'; // end wgm-item-content

                // 2. Image (Right side)
                if (image) {
                    html += '<div class="wgm-item-card-image" style="width: 100px !important; flex-shrink: 0 !important;">';
                    html += '<img class="wgm-lightbox-trigger" src="' + image + '" alt="" loading="lazy" style="width: 100% !important; border-radius: 8px !important; object-fit: cover !important;" />';
                    html += '</div>';
                }

                html += '</div>'; // end wgm-item-row
                html += '</td>';
                html += '</tr>';

                html += '</tr>';
                $tbody_<?php echo esc_attr($count); ?>.append(html);
            });
        }

        // debounce helper
        function debounce(fn, delay) {
            var t;
            return function () {
                var args = arguments, ctx = this;
                clearTimeout(t);
                t = setTimeout(function () {
                    fn.apply(ctx, args);
                }, delay);
            };
        }

        // Fetch markers and render rows
        $.post(ajaxurl_<?php echo esc_attr($count); ?>, markerData_<?php echo esc_attr($count); ?>, function (response) {
            try {
                var res = typeof response === 'string' ? JSON.parse(response) : response;
                markersCache_<?php echo esc_attr($count); ?> = res.markers || [];
                renderRows(markersCache_<?php echo esc_attr($count); ?>);
            } catch (e) {
                $tbody_<?php echo esc_attr($count); ?>.empty().append('<tr><td colspan="6" class="wpgmap-no-results"><?php echo esc_js(__('Unable to load markers.', 'gmap-embed')); ?></td></tr>');
            }
        }).fail(function () {
            $tbody_<?php echo esc_attr($count); ?>.empty().append('<tr><td colspan="6" class="wpgmap-no-results"><?php echo esc_js(__('Failed to load markers.', 'gmap-embed')); ?></td></tr>');
        });

        // Delegated click handler for row actions
        $tbody_<?php echo esc_attr($count); ?>.on('click', '.wpgmap-show-btn, .wpgmap-focus-btn, tr[data-marker-idx]', function (e) {
            var $el = $(e.target);
            var $row = $(this).closest('tr[data-marker-idx]');
            if ($row.length === 0) {
                if ($(this).is('tr')) {
                    $row = $(this);
                }
            }
            var idx = parseInt($el.data('idx') !== undefined ? $el.data('idx') : $row.attr('data-marker-idx'), 10);
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

            if ($el.hasClass('wpgmap-focus-btn')) {
                if (typeof window.wgm_close_all_infowindows_<?php echo esc_attr($count); ?> === 'function') {
                    window.wgm_close_all_infowindows_<?php echo esc_attr($count); ?>();
                }
                mapInstance.panTo(markerObj.getPosition());
                mapInstance.setZoom(15);
                if (infoWindow) infoWindow.open({ anchor: markerObj, shouldFocus: false });
                if (markerObj.setAnimation) {
                    markerObj.setAnimation(google.maps.Animation.BOUNCE);
                }
                setTimeout(function () { 
                    if (markerObj.setAnimation) {
                        markerObj.setAnimation(null); 
                    }
                }, 700);
                return;
            }

            // default: show on map & open infowindow
            if (window.custom_marker_infowindows && window.custom_marker_infowindows[<?php echo esc_attr($count); ?>]) {
                window.custom_marker_infowindows[<?php echo esc_attr($count); ?>].forEach(function(iw){ if(iw) iw.close(); });
            }
            mapInstance.panTo(markerObj.getPosition());
            mapInstance.setZoom(15);
            if (infoWindow) infoWindow.open({ anchor: markerObj, shouldFocus: false });
        });

        // Keyboard support: Enter/Space on row triggers "Show"
        $tbody_<?php echo esc_attr($count); ?>.on('keydown', 'tr[data-marker-idx]', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                $(this).trigger('click');
            }
        });

        // Toggle description expansion on click
        $tbody_<?php echo esc_attr($count); ?>.on('click', '.wpgmap-marker-desc', function (e) {
            var $d = $(this);
            $d.toggleClass('expanded');
        });



        // Listen for Advanced Map Search Update
        $(window).on('wgm_search_update_<?php echo esc_attr($count); ?>', function(e, data) {
            if (data) {
                if (data.markers) {
                // Update cache so local text search works on this result set? 
                // Or maybe keep original cache?
                // If we update cache, we lose the 'all' list unless we store it separately.
                // For now, just render.
                renderRows(data.markers);
            }
            }
        });
    });
</script>