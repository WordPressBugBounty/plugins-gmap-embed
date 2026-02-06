<?php
/**
 * Carousel Marker Listing Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>
<!-- Carousel Marker Listing -->
<?php wp_enqueue_style('wgm-swiper-css'); ?>
<div class="wpgmap-marker-listing wpgmap-carousel-container" 
     id="wpgmap_carousel_container_<?php echo esc_attr($count); ?>"
     aria-label="<?php echo esc_attr__('Markers Carousel', 'gmap-embed'); ?>">
    
    <div class="swiper wpgmap-swiper-<?php echo esc_attr($count); ?>">
        <div class="swiper-wrapper" id="wpgmap_carousel_wrapper_<?php echo esc_attr($count); ?>">
            <!-- Slides will be injected here via JavaScript -->
        </div>
        
        <!-- Navigation Buttons -->
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
        
        <!-- Pagination -->
        <div class="swiper-pagination"></div>
    </div>
</div>

<?php wp_enqueue_script('wgm-swiper-js'); ?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var ajaxData_<?php echo esc_attr($count); ?> = {
            action: 'wpgmapembed_p_get_markers_by_map_id',
            _wgm_p_nonce: '<?php echo esc_js(wp_create_nonce('wgm_marker_render')); ?>',
            data: { map_id: '<?php echo intval($wgm_map_id); ?>' }
        };
        var ajaxurl_<?php echo esc_attr($count); ?> = '<?php echo esc_url(admin_url('admin-ajax.php')); ?>';
        var markersCache_<?php echo esc_attr($count); ?> = [];
        var swiper_<?php echo esc_attr($count); ?> = null;

        var wgm_categories_<?php echo esc_attr($count); ?> = <?php echo wp_json_encode($data['categories']); ?>;

        function getCategoryBadges(catIds) {
            if (!catIds) return '';
            var ids = catIds.toString().split(',');
            var badges = '';
            ids.forEach(function(id) {
                var cat = wgm_categories_<?php echo esc_attr($count); ?>.find(function(c) { return c.id == id; });
                if (cat) {
                    badges += '<span class="wgm-category-badge">' + cat.name + '</span>';
                }
            });
            return badges;
        }
        
        function escHtml(s) { 
            return $('<div>').text(s || '').html(); 
        }

        function buildSlide(marker, idx) {
            var mIdx = marker.marker_idx !== undefined ? marker.marker_idx : idx;
            var icon = marker.icon || 'https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png';
            var image = marker.marker_image || '';
            var title = marker.marker_name || '<?php echo esc_js(__('Untitled Marker', 'gmap-embed')); ?>';
            var address = marker.address || '';
            var catBadges = getCategoryBadges(marker.category_id);
            
            // Clean description for excerpt
            var rawDesc = (marker.marker_desc || '').replace(/(<([^>]+)>)/gi, "");
            var excerpt = rawDesc.length > 80 ? rawDesc.substring(0, 80).trim() + '...' : rawDesc;

            var imageHtml = image ? 
                '<div class="wgm-card-media">' +
                    '<img src="' + escHtml(image) + '" alt="' + escHtml(title) + '" class="wgm-card-img wgm-lightbox-trigger" />' +
                    '<div class="wgm-card-badge">' +
                        '<img src="' + escHtml(icon) + '" alt="" width="24" height="24" />' +
                    '</div>' +
                '</div>' : 
                '<div class="wgm-card-media no-image">' +
                    '<div class="wgm-card-placeholder"><svg viewBox="0 0 24 24" width="48" height="48"><path fill="currentColor" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"></path></svg></div>' +
                    '<div class="wgm-card-badge">' +
                        '<img src="' + escHtml(icon) + '" alt="" width="24" height="24" />' +
                    '</div>' +
                '</div>';

            return '<div class="swiper-slide">' +
                '<div class="wgm-carousel-card wgm-item-card" data-idx="' + mIdx + '">' +
                    imageHtml +
                    '<div class="wgm-card-body">' +
                        '<h3 class="wgm-item-title">' + escHtml(title) + '</h3>' +
                        
                        '<div class="wgm-location-row">' +
                            '<div class="wgm-location-icon-col">' +
                                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" class="wgm-location-pin-main"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"></path></svg>' +
                            '</div>' +
                            '<div class="wgm-location-info-col">' +
                                (address ? '<div class="wgm-item-address">' + escHtml(address) + '</div>' : '') +
                                (marker.distance ? '<div class="wgm-distance-wrapper"><span class="wgm-marker-distance-badge">' + marker.distance + '</span></div>' : '') +
                                (catBadges ? '<div class="wgm-carousel-cat">' + catBadges + '</div>' : '') +
                            '</div>' +
                        '</div>' +
                        
                        '<p class="wgm-item-desc">' + escHtml(excerpt) + '</p>' +
                        
                        '<div class="wgm-item-actions">' +
                            (wgm_config_<?php echo esc_attr($count); ?>.enable_show_on_map_icon ? '<button class="wgm-item-action-btn wgm-action-primary wgm-show-on-map-btn" data-idx="' + marker.marker_idx + '" title="<?php echo esc_attr__('Show on Map', 'gmap-embed'); ?>">' +
                                '<svg viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"></path></svg>' +
                            '</button>' : '') +
                            (wgm_config_<?php echo esc_attr($count); ?>.enable_get_direction_icon ? '<button class="wgm-item-action-btn wgm-get-direction-btn" data-idx="' + marker.marker_idx + '" title="<?php echo esc_attr__('Directions', 'gmap-embed'); ?>">' +
                                '<svg viewBox="0 0 24 24"><path d="M21.71 11.29l-9-9c-.39-.39-1.02-.39-1.41 0l-9 9c-.39.39-.39 1.02 0 1.41l9 9c.39.39 1.02.39 1.41 0l9-9c.39-.38.39-1.01 0-1.41zM14 14.5V12h-4v3H8.5v-4.5c0-.55.45-1 1-1H14V7l4 4-4 4.5z"></path></svg>' +
                            '</button>' : '') +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>';
        }

        function initializeSwiper() {
            if (swiper_<?php echo esc_attr($count); ?>) {
                swiper_<?php echo esc_attr($count); ?>.destroy(true, true);
            }

            // Detect placement from container classes
            var container = $('.wgm-map-listing-container.wgm-map-id-<?php echo esc_attr($wgm_map_id); ?>');
            var isLeftRight = container.hasClass('wgm-placement-left_map') || container.hasClass('wgm-placement-right_map');
            var carouselContainer = $('#wpgmap_carousel_container_<?php echo esc_attr($count); ?>');

            // Configure based on placement
            var swiperConfig = {
                slidesPerView: 1,
                spaceBetween: 20,
                breakpointsBase: 'container',
                grabCursor: true,
                loop: false,
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                    dynamicBullets: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                on: {
                    init: function() {
                        // Add custom class to disable grid layout
                        carouselContainer.addClass('wgm-swiper-active');
                    }
                }
            };

            // Only add breakpoints for above/below placements
            if (!isLeftRight) {
                swiperConfig.breakpoints = {
                    600: { slidesPerView: 2 },
                    900: { slidesPerView: 3 },
                    1200: { slidesPerView: 4 }
                };
            }
            // For left/right, slidesPerView stays at 1 always

            swiper_<?php echo esc_attr($count); ?> = new Swiper('.wpgmap-swiper-<?php echo esc_attr($count); ?>', swiperConfig);
        }

        function handleShowOnMap(idx) {
            var marker = null;
            if (window.custom_markers) {
                if (window.custom_markers[<?php echo esc_attr($count); ?>]) {
                    marker = window.custom_markers[<?php echo esc_attr($count); ?>][idx];
                }
            }
            var iw = null;
            if (window.custom_marker_infowindows) {
                 if (window.custom_marker_infowindows[<?php echo esc_attr($count); ?>]) {
                     iw = window.custom_marker_infowindows[<?php echo esc_attr($count); ?>][idx];
                 }
            }
            var map = window['wgm_map_<?php echo esc_attr($count); ?>'];

            if (marker) {
                if (map) {
                if (window.wgm_front && typeof window.wgm_front.closeAllInfoWindows === 'function') {
                    window.wgm_front.closeAllInfoWindows(<?php echo esc_attr($count); ?>);
                } else if (typeof window.wgm_close_all_infowindows_<?php echo esc_attr($count); ?> === 'function') {
                    window.wgm_close_all_infowindows_<?php echo esc_attr($count); ?>();
                }
                
                map.panTo(marker.getPosition());
                if (map.getZoom() < 15) map.setZoom(15);
                
                if (iw) {
                    iw.open({
                        anchor: marker,
                        shouldFocus: false
                    });
                }

                // Bounce animation
                if (marker.setAnimation) {
                    marker.setAnimation(google.maps.Animation.BOUNCE);
                    setTimeout(function() { marker.setAnimation(null); }, 750);
                }

                // Scroll to map if needed
                $('html, body').animate({
                    scrollTop: $("#srm_gmp_embed_<?php echo esc_attr($count); ?>").offset().top - 100
                }, 500);
            }
            }
        }

        function renderCarousel(markers) {
            markersCache_<?php echo esc_attr($count); ?> = markers;
            var $wrapper = $('#wpgmap_carousel_wrapper_<?php echo esc_attr($count); ?>');
            $wrapper.empty();

            if (markersCache_<?php echo esc_attr($count); ?>.length > 0) {
                markersCache_<?php echo esc_attr($count); ?>.forEach(function (marker, i) {
                    $wrapper.append(buildSlide(marker, i));
                });
                initializeSwiper();
            } else {
                $wrapper.append('<div class="wgm-no-markers"><?php echo esc_js(__('No markers to display', 'gmap-embed')); ?></div>');
            }
        }

        // Listen for initial Markers Load
        $(window).on('wgm_markers_loaded_<?php echo esc_attr($count); ?>', function(e, data) {
            if (data && data.markers) {
                renderCarousel(data.markers);
            }
        });

        // Click Events
        $(document).on('click', '#wpgmap_carousel_container_<?php echo esc_attr($count); ?> .wgm-show-on-map-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $card = $(this).closest('.wgm-carousel-card');
            var idx = $card.data('idx');
            handleShowOnMap(idx);
        });

        $(document).on('click', '#wpgmap_carousel_container_<?php echo esc_attr($count); ?> .wgm-get-direction-btn', function(e) {
            e.preventDefault();
            e.stopPropagation();
            var $card = $(this).closest('.wgm-carousel-card');
            var idx = $card.data('idx');
            var count = '<?php echo esc_attr($count); ?>';
            
            var mapMarkers = window.custom_markers ? window.custom_markers[count] : null;
            if (mapMarkers && mapMarkers[idx]) {
                var m = mapMarkers[idx];
                var destination = m.wgm_data.address || m.wgm_data.lat_lng;
                if (typeof window["wgm_open_directions_" + count] === 'function') {
                    window["wgm_open_directions_" + count](destination);
                }
            }
        });

        $(document).on('click', '#wpgmap_carousel_container_<?php echo esc_attr($count); ?> .wgm-carousel-card', function() {
            var idx = $(this).data('idx');
            handleShowOnMap(idx);
        });

        // Listen for Search Updates
        $(window).on('wgm_search_update_<?php echo esc_attr($count); ?>', function(e, data) {
            if (data) {
                if (data.markers) {
                markersCache_<?php echo esc_attr($count); ?> = data.markers;
                var $wrapper = $('#wpgmap_carousel_wrapper_<?php echo esc_attr($count); ?>');
                $wrapper.empty();
                
                if (markersCache_<?php echo esc_attr($count); ?>.length > 0) {
                    markersCache_<?php echo esc_attr($count); ?>.forEach(function (marker, i) {
                        $wrapper.append(buildSlide(marker, i));
                    });
                    initializeSwiper();
                } else {
                    $wrapper.append('<div class="wgm-no-markers"><?php echo esc_js(__('No markers found in this area', 'gmap-embed')); ?></div>');
                }
            }
            }
        });
    });
</script>
