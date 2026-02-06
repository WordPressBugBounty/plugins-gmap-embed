<?php
defined( 'ABSPATH' ) || exit;
?>
<div class="wpgmap-marker-listing wpgmap-adv-table-listing" style="max-width: <?php echo esc_attr($wpgmap_map_width); ?>; margin: 20px auto 0;">
    <div class="wgm-table-responsive">
    <table id="wpgmap_marker_adv_table_<?php echo esc_attr($count); ?>" class="display"
        style="width:100%;border-collapse:collapse;">
        <caption class="screen-reader-text"><?php esc_html_e('Map markers', 'gmap-embed'); ?></caption>
        <thead>
            <tr>
                <th scope="col"><?php esc_html_e('Icon', 'gmap-embed'); ?></th>
                <th scope="col"><?php esc_html_e('Image', 'gmap-embed'); ?></th>
                <th scope="col"><?php esc_html_e('Title', 'gmap-embed'); ?></th>
                <th scope="col"><?php esc_html_e('Description', 'gmap-embed'); ?></th>
                <th scope="col"><?php esc_html_e('Address', 'gmap-embed'); ?></th>
                <th scope="col"><?php esc_html_e('Actions', 'gmap-embed'); ?></th>
            </tr>
        </thead>
        <tbody>
            <!-- Marker rows will be appended here by JS -->
        </tbody>
    </table>
    </div>
    </div>

    <?php
    wp_enqueue_style('wgm-datatable-css');
    wp_enqueue_script('wgm-datatable-js');
    ?>
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
        var markersCache_<?php echo esc_attr($count); ?> = [];

        // helper to escape text for cell content
        function escHtml(s) { return $('<div>').text(s || '').html(); }

        // Declare table variable in outer scope for event handlers
        var table;

        // Fetch markers and initialize DataTable
        $.post(ajaxurl_<?php echo esc_attr($count); ?>, markerData_<?php echo esc_attr($count); ?>, function (response) {
            try {
                var res = typeof response === 'string' ? JSON.parse(response) : response;
                markersCache_<?php echo esc_attr($count); ?> = res.markers || [];

                var dataSet = markersCache_<?php echo esc_attr($count); ?>.map(function (marker, idx) {
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
                    var title = marker.marker_name || '';
                    if (marker.distance) {
                        title += ' <div class="wgm-dist-badge-wrap"><span class="wgm-marker-distance-badge">' + marker.distance + '</span></div>';
                    }
                    var desc = (marker.marker_desc || '').replace(/(<([^>]+)>)/gi, "");
                    var address = marker.address || '';
                    //var distance = marker.distance || '';



                    var mIdx = marker.marker_idx !== undefined ? marker.marker_idx : idx;
                    return [
                        '<img class="wpgmap-marker-icon" src="' + escHtml(icon) + '" alt="" loading="lazy" width="36" height="36" />',
                        image ? '<img class="wpgmap-marker-image wgm-lightbox-trigger" src="' + escHtml(image) + '" alt="" loading="lazy" width="36" height="36" />' : '',
                        escHtml(title),
                        escHtml(desc),
                        escHtml(address),
                        '<div class="wgm-item-actions" style="margin-top:0 !important; justify-content: center !important;">' +
                            (wgm_config_<?php echo esc_attr($count); ?>.enable_show_on_map_icon ? '<button type="button" class="wgm-item-action-btn wgm-action-primary wpgmap-show-btn" data-idx="' + mIdx + '" title="<?php echo esc_attr__('Show on Map', 'gmap-embed'); ?>"><svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"></path></svg></button>' : '') +
                            (wgm_config_<?php echo esc_attr($count); ?>.enable_get_direction_icon ? '<button type="button" class="wgm-item-action-btn wgm-get-direction-btn" data-idx="' + mIdx + '" title="<?php echo esc_attr__('Directions', 'gmap-embed'); ?>"><svg viewBox="0 0 24 24"><path d="M21.71 11.29l-9-9c-.39-.39-1.02-.39-1.41 0l-9 9c-.39.39-.39 1.02 0 1.41l9 9c.39.39 1.02.39 1.41 0l9-9c.39-.38.39-1.01 0-1.41zM14 14.5V12h-4v3H8.5v-4.5c0-.55.45-1 1-1H14V7l4 4-4 4.5z"></path></svg></button>' : '') +
                        '</div>'
                    ];
                });

                table = $('#wpgmap_marker_adv_table_<?php echo esc_attr($count); ?>').DataTable({
                    data: dataSet,
                    columns: [
                        { title: "Icon", orderable: false },
                        { title: "Image", orderable: false },
                        { title: "Title" },
                        { title: "Description", orderable: false },
                        { title: "Address" },
                        { title: "Actions", orderable: false }
                        //{ title: "Distance" }
                    ],
                    paging: true,
                    searching: true,
                    info: true,
                    ordering: true,
                    responsive: true,
                    autoWidth: false,
                    language: { emptyTable: "<?php echo esc_js(__('No markers found.', 'gmap-embed')); ?>" },
                    createdRow: function (row, data, dataIndex) {
                        $(row).attr('tabindex', 0).attr('data-marker-idx', dataIndex).addClass(dataIndex % 2 === 0 ? 'odd' : 'even');
                    },
                    drawCallback: function () {
                        // improve keyboard focus visibility
                        $('#wpgmap_marker_adv_table_<?php echo esc_attr($count); ?> tbody tr').off('focus').on('focus', function () {
                            $(this).addClass('focused-row');
                        }).off('blur').on('blur', function () {
                            $(this).removeClass('focused-row');
                        });
                    }
                });

                // Click action for "Show on map" buttons
                $('#wpgmap_marker_adv_table_<?php echo esc_attr($count); ?> tbody').on('click', '.wpgmap-show-btn', function (e) {
                    e.stopPropagation();
                    var idx = parseInt($(this).attr('data-idx'), 10);
                    if (isNaN(idx)) return;
                    showMarkerOnMap(idx);
                });

                // Row click / keyboard Enter/Space should also show marker
                $('#wpgmap_marker_adv_table_<?php echo esc_attr($count); ?> tbody').on('click', 'tr', function () {
                    var rowIdx = table.row(this).index();
                    if (rowIdx !== undefined) {
                        if (!isNaN(rowIdx)) {
                            showMarkerOnMap(rowIdx);
                        }
                    }
                }).on('keydown', 'tr', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        var rowIdx = table.row(this).index();
                        if (rowIdx !== undefined) {
                            if (!isNaN(rowIdx)) {
                                showMarkerOnMap(rowIdx);
                            }
                        }
                    }
                });

                // Centralized function to pan to marker and open infowindow
                function showMarkerOnMap(idx) {
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
                    
                    // Close current before opening new
                    if (window.custom_marker_infowindows && window.custom_marker_infowindows[<?php echo esc_attr($count); ?>]) {
                        window.custom_marker_infowindows[<?php echo esc_attr($count); ?>].forEach(function(iw){ if(iw) iw.close(); });
                    }

                    mapInstance.panTo(markerObj.getPosition());
                    if (mapInstance.getZoom() < 14) mapInstance.setZoom(15);
                    if (infoWindow) {
                        infoWindow.open({ anchor: markerObj, shouldFocus: false });
                    }
                    // brief bounce to highlight
                    if (markerObj.setAnimation) {
                        markerObj.setAnimation(google.maps.Animation.BOUNCE);
                        setTimeout(function () { markerObj.setAnimation(null); }, 700);
                    }
                }

                // Listen for Advanced Map Search Update
                $(window).on('wgm_search_update_<?php echo esc_attr($count); ?>', function(e, data) {
                    if (data) {
                        if (data.markers) {
                        table.clear();
                        // Rebuild dataSet for new markers
                        var newDataSet = data.markers.map(function (marker, idx) {
                            var mIdx = marker.marker_idx !== undefined ? marker.marker_idx : idx;
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
                            var title = marker.marker_name || '';
                            if (marker.distance) {
                                title += ' <div class="wgm-dist-badge-wrap"><span class="wgm-marker-distance-badge">' + marker.distance + '</span></div>';
                            }
                            var desc = (marker.marker_desc || '').replace(/(<([^>]+)>)/gi, "");
                            var address = marker.address || '';
                            return [
                                '<img class="wpgmap-marker-icon" src="' + escHtml(icon) + '" alt="" loading="lazy" width="36" height="36" />',
                                image ? '<img class="wpgmap-marker-image wgm-lightbox-trigger" src="' + escHtml(image) + '" alt="" loading="lazy" width="36" height="36" />' : '',
                                escHtml(title),
                                escHtml(desc),
                                escHtml(address),
                                '<div class="wgm-item-actions" style="margin-top:0 !important; justify-content: center !important;">' +
                                    (wgm_config_<?php echo esc_attr($count); ?>.enable_show_on_map_icon ? '<button type="button" class="wgm-item-action-btn wgm-action-primary wpgmap-show-btn" data-idx="' + mIdx + '" title="<?php echo esc_attr__('Show on Map', 'gmap-embed'); ?>"><svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"></path></svg></button>' : '') +
                                    (wgm_config_<?php echo esc_attr($count); ?>.enable_get_direction_icon ? '<button type="button" class="wgm-item-action-btn wgm-get-direction-btn" data-idx="' + mIdx + '" title="<?php echo esc_attr__('Directions', 'gmap-embed'); ?>"><svg viewBox="0 0 24 24"><path d="M21.71 11.29l-9-9c-.39-.39-1.02-.39-1.41 0l-9 9c-.39.39-.39 1.02 0 1.41l9 9c.39.39 1.02.39 1.41 0l9-9c.39-.38.39-1.01 0-1.41zM14 14.5V12h-4v3H8.5v-4.5c0-.55.45-1 1-1H14V7l4 4-4 4.5z"></path></svg></button>' : '') +
                                '</div>'
                            ];
                        });
                        table.rows.add(newDataSet);
                        table.draw();
                    }
                    }
                });

            } catch (e) {
                // if parse fails: show friendly fallback row
                var $tbody = $('#wpgmap_marker_adv_table_<?php echo esc_attr($count); ?> tbody');
                $tbody.empty().append('<tr><td colspan="6" class="wpgmap-no-results"><?php echo esc_js(__('Unable to load markers.', 'gmap-embed')); ?></td></tr>');
            }
        }).fail(function () {
            var $tbody = $('#wpgmap_marker_adv_table_<?php echo esc_attr($count); ?> tbody');
            $tbody.empty().append('<tr><td colspan="6" class="wpgmap-no-results"><?php echo esc_js(__('Failed to load markers.', 'gmap-embed')); ?></td></tr>');
        });
    });
    </script>