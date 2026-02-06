/**
 * WP Google Map Frontend Logic
 * Centralized script to handle map initialization, search, and directions.
 * @since 1.9.1
 */

(function ($) {
  "use strict";

  window.wgm_front = {
    instances: {},

    init: function (data) {
      var self = this;
      var id = data.count;
      this.instances[id] = data;

      // Define legacy config early for themes/templates
      window["wgm_config_" + id] = data.config;

      $(document).ready(function () {
        self.initMap(id);
        self.initLightbox(id);
        self.initSelect2();
      });
    },

    /**
     * Escape HTML strings to prevent XSS
     * @param {string} str
     * @returns {string}
     */
    escapeHTML: function (str) {
      if (!str) return "";
      return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    },

    /**
     * Remove theme-injected classes that might interfere with design
     * @param {string} selector
     * @param {string[]} allowed
     */
    sanitizeClasses: function (selector, allowed) {
      $(selector).each(function () {
        var $el = $(this);
        var classes = ($el.attr("class") || "").split(/\s+/);
        var filtered = classes.filter(function (c) {
          return (
            allowed.indexOf(c) !== -1 ||
            (c && c.indexOf("wgm-") === 0) ||
            (c && c.indexOf("wpgmap_") === 0)
          );
        });
        $el.attr("class", filtered.join(" "));
      });
    },

    initSelect2: function () {
      if ($.fn.select2) {
        $(".wgm-select2").each(function () {
          var $this = $(this);
          if (!$this.hasClass("select2-hidden-accessible")) {
            $this.select2({
              allowClear: true,
              width: "100%",
            });
          }
        });
      }
    },

    initMap: function (id) {
      var data = this.instances[id];
      var self = this;

      var initFn = function () {
        console.log("WGM: Initializing map " + id);
        if (typeof google !== "object" || typeof google.maps !== "object") {
          console.warn("WGM: Google Maps API not ready yet inside initFn.");
          return;
        }

        var mapDiv = document.getElementById("srm_gmp_embed_" + id);
        if (!mapDiv) {
          console.error("WGM: Map container not found: srm_gmp_embed_" + id);
          return;
        }

        console.log(
          "WGM: Found map container, proceeding with initialization."
        );
        try {
          var mapOptions = {
            center: new google.maps.LatLng(
              data.map_center.lat,
              data.map_center.lng
            ),
            zoom: parseInt(data.map_zoom),
            mapTypeId: google.maps.MapTypeId[data.map_type],
            scrollwheel: data.options.disable_mouse_wheel_zoom !== "Y",
            zoomControl: data.options.zoom_control,
            zoomControlOptions: {
              position:
                google.maps.ControlPosition[data.options.zoom_control_pos],
            },
            mapTypeControl: data.options.map_type_control,
            mapTypeControlOptions: {
              position:
                google.maps.ControlPosition[data.options.map_type_control_pos],
            },
            streetViewControl: data.options.street_view_control,
            streetViewControlOptions: {
              position:
                google.maps.ControlPosition[
                  data.options.street_view_control_pos
                ],
            },
            fullscreenControl: data.options.fullscreen_control,
            fullscreenControlOptions: {
              position:
                google.maps.ControlPosition[
                  data.options.fullscreen_control_pos
                ],
            },
            rotateControl: data.options.rotate_control,
            rotateControlOptions: {
              position:
                google.maps.ControlPosition[data.options.rotate_control_pos],
            },
            scaleControl: data.options.scale_control,
            scaleControlOptions: {
              position:
                google.maps.ControlPosition[data.options.scale_control_pos],
            },
            draggable: data.options.disable_mouse_dragging !== "Y",
            disableDoubleClickZoom:
              data.options.disable_mouse_double_click_zooming === "Y",
          };

          var map = new google.maps.Map(mapDiv, mapOptions);
          data.map = map;
          window["wgm_map_" + id] = map; // For legacy/theme compatibility

          // Apply Theme
          if (data.theme_json) {
            try {
              map.setOptions({ styles: JSON.parse(data.theme_json) });
            } catch (e) {
              console.error("WGM: Invalid map theme JSON:", e);
            }
          }

          // Load Markers
          self.loadMarkers(id);

          // Initialize Search and Directions once map is ready
          self.initSearch(id);
          self.initDirections(id);

          // Trigger hooks for themes or other extensions
          $(window).trigger("wgm_map_init_" + id, { map: map, data: data });
        } catch (e) {
          console.error("WGM: Error during map initialization:", e);
        }
      };

      if (typeof google === "object" && typeof google.maps === "object") {
        console.log("WGM: Google Maps API ready, initializing immediately.");
        initFn();
      } else {
        console.log("WGM: Google Maps API not ready, queuing initialization.");
        window.wgm_map_queue = window.wgm_map_queue || [];
        window.wgm_map_queue.push(initFn);
      }
    },

    loadMarkers: function (id) {
      var data = this.instances[id];
      var self = this;

      var ajaxData = {
        action: "wpgmapembed_p_get_markers_by_map_id",
        _wgm_p_nonce: data.nonces.marker_render,
        data: {
          map_id: data.map_id,
        },
      };

      $.post(data.ajax_url, ajaxData, function (response) {
        try {
          response =
            typeof response === "string" ? JSON.parse(response) : response;
        } catch (e) {
          console.error("WGM: Failed to parse markers response", e);
          return;
        }

        if (response.markers && response.markers.length > 0) {
          if (!window.custom_markers) window.custom_markers = {};
          if (!window.custom_marker_infowindows)
            window.custom_marker_infowindows = {};
          window.custom_markers[id] = [];
          window.custom_marker_infowindows[id] = [];

          // Legacy config global for themes
          window["wgm_config_" + id] = data.config;

          response.markers.forEach(function (markerData, i) {
            markerData.marker_idx = i;
            self.addMarker(id, markerData, i);
          });

          // Trigger event for themes to consume markers
          $(window).trigger("wgm_markers_loaded_" + id, {
            markers: response.markers,
          });

          // Search pre-fill logic if single marker
          if (
            response.markers.length === 1 &&
            data.config.enable_modern_direction
          ) {
            var singleAddr = response.markers[0].marker_name || "";
            $("#srm_gmap_to_" + id).val(
              self.escapeHTML(singleAddr.replace(/(<([^>]+)>)/gi, ""))
            );
          }
        }
      }).fail(function (xhr, status, error) {
        console.error("WGM: Failed to load markers: " + error);
      });
    },

    addMarker: function (id, markerData, index) {
      var data = this.instances[id];
      var self = this;
      var latlng = (markerData.lat_lng || "0,0").split(",");
      if (latlng.length < 2) {
        console.warn("WGM: Invalid coordinates for marker", markerData);
        return;
      }

      var anim = null;
      if (markerData.animation === "BOUNCE")
        anim = google.maps.Animation.BOUNCE;
      if (markerData.animation === "DROP") anim = google.maps.Animation.DROP;

      var marker = new google.maps.Marker({
        position: new google.maps.LatLng(
          parseFloat(latlng[0]),
          parseFloat(latlng[1])
        ),
        title: self.escapeHTML(markerData.marker_name),
        animation: anim,
        icon: markerData.icon === "" ? data.default_icon : markerData.icon,
        visible: !data.config.hide_markers,
        map: data.map,
      });

      marker.wgm_data = markerData;
      window.custom_markers[id][index] = marker;

      // Infowindow preparation
      var infowindow = new google.maps.InfoWindow({
        content: self.getInfoWindowContent(id, markerData),
        maxWidth: 320,
      });
      window.custom_marker_infowindows[id][index] = infowindow;

      // Auto-open infowindow if configured
      if (markerData.show_desc_by_default === "1") {
        self.closeAllInfoWindows(id);
        infowindow.open({ anchor: marker, shouldFocus: false });
      }

      // Handle marker click behavior
      if (markerData.have_marker_link === "1") {
        // If marker has a link, navigate to it on click
        marker.addListener("click", function () {
          var target =
            markerData.marker_link_new_tab === "1" ? "_blank" : "_self";
          window.open(markerData.marker_link, target);
        });
      } else {
        // If no link, always open infowindow on click (regardless of show_desc_by_default)
        marker.addListener("click", function () {
          self.closeAllInfoWindows(id);
          infowindow.open({ anchor: marker, shouldFocus: false });
        });
      }
    },

    getInfoWindowContent: function (id, markerData) {
      var data = this.instances[id];
      var self = this;
      var hasImg = markerData.marker_image && markerData.marker_image !== "";
      if (data.marker_listing_type === "basic_list") hasImg = false;

      var imgHtml = hasImg
        ? '<div class="wgm-iw-img-wrap"><img src="' +
          markerData.marker_image +
          '" class="wgm-infowindow-img wgm-lightbox-trigger" /></div>'
        : "";
      var titleHtml = markerData.marker_name
        ? '<p class="info_content_title wgm-infowindow-title">' +
          self.escapeHTML(markerData.marker_name) +
          "</p>"
        : "";

      var dirLink = "";
      if (data.config.enable_direction_link) {
        var dirAddr = (markerData.address || markerData.lat_lng).replace(
          /'/g,
          "&#39;"
        );
        dirLink =
          '<br><a href="#" class="wgm-get-dir-link wgm-dir-link-' +
          id +
          '" data-addr="' +
          dirAddr +
          '" style="display:inline-block;margin-top:5px;font-size:13px;">' +
          data.i18n.get_directions +
          "</a>";
      }

      var wrapperClass =
        "wgm-infowindow-wrapper" + (hasImg ? " wgm-has-img" : " wgm-no-img");
      return (
        '<div class="' +
        wrapperClass +
        '">' +
        imgHtml +
        '<div class="wgm-infowindow-content">' +
        titleHtml +
        '<div class="wgm-iw-desc">' +
        markerData.marker_desc +
        "</div>" +
        dirLink +
        "</div>" +
        "</div>"
      );
    },

    closeAllInfoWindows: function (id) {
      if (
        window.custom_marker_infowindows &&
        window.custom_marker_infowindows[id]
      ) {
        window.custom_marker_infowindows[id].forEach(function (iw) {
          if (iw) iw.close();
        });
      }
    },

    initSearch: function (id) {
      var data = this.instances[id];
      var self = this;
      var controlDiv = document.getElementById("wpgmap_search_control_" + id);
      var mapWrapper = document.getElementById("srm_gmp_embed_" + id);

      if (!controlDiv || !mapWrapper) return;

      // Sanitize classes to remove theme injection
      self.sanitizeClasses(controlDiv, ["wgm-search-control-container"]);
      var $cd = $(controlDiv);
      self.sanitizeClasses($cd.find(".wpgmap_search_box"), [
        "wpgmap_search_box",
      ]);
      self.sanitizeClasses($cd.find(".wpgmap_search_input"), [
        "wpgmap_search_input",
      ]);
      self.sanitizeClasses($cd.find(".wpgmap_search_select"), [
        "wpgmap_search_select",
      ]);
      self.sanitizeClasses($cd.find(".wpgmap_search_btn"), [
        "wpgmap_search_btn",
      ]);
      self.sanitizeClasses($cd.find(".wpgmap_search_clear"), [
        "wpgmap_search_clear",
      ]);

      // Placement logic
      if (data.config.placement === "above_map") {
        $(mapWrapper).before(controlDiv);
        $(controlDiv).show();
      } else if (data.config.placement === "below_map") {
        $(mapWrapper).after(controlDiv);
        $(controlDiv).show();
      } else {
        data.map.controls[google.maps.ControlPosition.TOP_CENTER].push(
          controlDiv
        );
        $(controlDiv).show();
      }

      var btn = $("#wpgmap_search_btn_" + id);
      var locInput = $("#wpgmap_search_loc_" + id);
      var keyInput = $("#wpgmap_search_key_" + id);
      var radSelect = $("#wpgmap_search_radius_" + id);

      var catInput = $("#wpgmap_search_cat_" + id);
      var catPanel = $("#wpgmap_filter_panel_" + id);
      var catToggle = $("#wpgmap_filter_toggle_" + id);

      var spinner = $("#wpgmap_search_spinner_" + id);
      var notFoundMsg = $("#wpgmap_not_found_msg_" + id);

      // Bind Close Event Globally for this instance
      notFoundMsg.find(".wgm-not-found-close").on("click", function (e) {
        e.preventDefault();
        notFoundMsg.stop(true, false).animate({ opacity: 0 }, 500, function () {
          $(this).removeClass("wgm-show").css("opacity", "");
        });
        if (window["wgm_not_found_timer_" + id]) {
          clearTimeout(window["wgm_not_found_timer_" + id]);
        }
      });
      var searchCircle = null;

      var adjustWidth = function () {
        var container = $(mapWrapper).closest(".wgm-map-listing-container");
        var containerWidth = container.width();
        var mapWidth = mapWrapper.offsetWidth;

        // Adjust input visibility based on map width
        var container = $(mapWrapper).closest(".wgm-map-listing-container");
        var containerWidth = container.width();
        var mapWidth = mapWrapper.offsetWidth;

        // Container-Aware Stacking Logic
        if (containerWidth < 600) {
          container.addClass("wgm-force-stack");
        } else {
          container.removeClass("wgm-force-stack");
        }

        if (mapWidth < 480) {
          if (keyInput.length) keyInput.hide();
        } else {
          if (keyInput.length) keyInput.show();
        }
        if (mapWidth < 380) {
          if (radSelect.length) radSelect.hide();
          if (catToggle.length) catToggle.hide();
        } else {
          if (radSelect.length) radSelect.show();
          if (catToggle.length) catToggle.show();
        }
      };

      adjustWidth();

      // Use ResizeObserver if available for better performance and theme compatibility
      if (typeof ResizeObserver !== "undefined") {
        var ro = new ResizeObserver(function () {
          adjustWidth();
        });
        ro.observe(mapWrapper);
      } else {
        $(window).on("resize", adjustWidth);
      }

      google.maps.event.addListenerOnce(data.map, "idle", adjustWidth);

      if (locInput.length && typeof google.maps.places !== "undefined") {
        var autocomplete = new google.maps.places.Autocomplete(locInput[0]);
        autocomplete.bindTo("bounds", data.map);
      }

      var getDistance = function (lat1, lon1, lat2, lon2) {
        var R = 6371; // km
        var dLat = ((lat2 - lat1) * Math.PI) / 180;
        var dLon = ((lon2 - lon1) * Math.PI) / 180;
        var a =
          Math.sin(dLat / 2) * Math.sin(dLat / 2) +
          Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2);
        return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
      };

      var updateMapAndList = function (
        centerLat,
        centerLng,
        radius,
        keyword,
        catId
      ) {
        var markers = window.custom_markers[id] || [];
        var filteredData = [];
        var bounds = new google.maps.LatLngBounds();
        var hasVisible = false;
        var hasLocation = centerLat !== null && centerLng !== null;

        if (searchCircle) searchCircle.setMap(null);
        if (window["wgm_center_marker_" + id])
          window["wgm_center_marker_" + id].setMap(null);

        if (hasLocation) {
          if (radius) {
            searchCircle = new google.maps.Circle({
              strokeColor: data.circle.stroke_color,
              strokeOpacity: data.circle.stroke_opacity,
              strokeWeight: data.circle.stroke_weight,
              fillColor: data.circle.fill_color,
              fillOpacity: data.circle.fill_opacity,
              map: data.map,
              center: { lat: centerLat, lng: centerLng },
              radius: radius * 1000,
            });
          }
          if (data.config.show_center_icon) {
            window["wgm_center_marker_" + id] = new google.maps.Marker({
              position: { lat: centerLat, lng: centerLng },
              map: data.map,
              title: "Search Center",
              icon: "https://maps.google.com/mapfiles/ms/icons/blue-dot.png",
            });
          }
        }

        markers.forEach(function (m, i) {
          var d = Object.assign({}, m.wgm_data);
          d.marker_idx = i;
          var isVisible = true;
          var distVal = 0;

          if (hasLocation) {
            var mLatLng = m.getPosition();
            var distKm = getDistance(
              centerLat,
              centerLng,
              mLatLng.lat(),
              mLatLng.lng()
            );
            if (radius && distKm > radius) isVisible = false;

            if (isVisible && data.config.show_distance) {
              distVal =
                data.config.distance_unit === "miles"
                  ? distKm * 0.621371
                  : distKm;
              var unit = data.config.distance_unit === "miles" ? "miles" : "km";
              d.distance = distVal.toFixed(2) + " " + unit;
              d.distance_html =
                '<span class="wgm-distance-badge">' + d.distance + "</span>";
            }
          }

          if (isVisible && keyword) {
            var hay = (
              (d.marker_name || "") +
              " " +
              (d.marker_desc || "") +
              " " +
              (d.address || "")
            ).toLowerCase();
            if (hay.indexOf(keyword) === -1) isVisible = false;
          }

          if (isVisible && catId && catId.length > 0) {
            var markerCats = d.category_id
              ? d.category_id.toString().split(",")
              : [];
            var selectedCats = Array.isArray(catId)
              ? catId
              : [catId.toString()];
            var logic = data.config.category_selection_logic || "OR";

            if (logic === "AND") {
              // Show if marker has ALL selected categories
              for (var j = 0; j < selectedCats.length; j++) {
                if (markerCats.indexOf(selectedCats[j].toString()) === -1) {
                  isVisible = false;
                  break;
                }
              }
            } else {
              // OR logic: Show if marker has ANY of the selected categories
              var found = false;
              for (var k = 0; k < selectedCats.length; k++) {
                if (markerCats.indexOf(selectedCats[k].toString()) !== -1) {
                  found = true;
                  break;
                }
              }
              if (!found) isVisible = false;
            }
          }

          m.setVisible(isVisible);
          if (isVisible) {
            filteredData.push(d);
            bounds.extend(m.getPosition());
            hasVisible = true;
          }
        });

        if (hasLocation && data.config.sort_by_distance) {
          filteredData.sort(function (a, b) {
            return (
              (parseFloat(a.distance) || 0) - (parseFloat(b.distance) || 0)
            );
          });
        }

        $(window).trigger("wgm_search_update_" + id, { markers: filteredData });

        if (hasVisible) {
          notFoundMsg.removeClass("wgm-show");
          if (hasLocation && radius) {
            data.map.fitBounds(searchCircle.getBounds());
          } else {
            data.map.fitBounds(bounds);
            if (data.map.getZoom() > 15) data.map.setZoom(15);
          }
        } else {
          notFoundMsg.addClass("wgm-show");

          // Clear any existing timeout
          if (window["wgm_not_found_timer_" + id]) {
            clearTimeout(window["wgm_not_found_timer_" + id]);
          }

          // Set 8s auto-hide timer
          window["wgm_not_found_timer_" + id] = setTimeout(function () {
            notFoundMsg
              .stop(true, false)
              .animate({ opacity: 0 }, 500, function () {
                $(this).removeClass("wgm-show").css("opacity", "");
              });
          }, 8000);

          if (hasLocation) {
            data.map.setCenter({ lat: centerLat, lng: centerLng });
            data.map.setZoom(10);
          }
        }
      };

      var executeSearch = function () {
        var loc = locInput.length ? locInput.val().trim() : "";
        var key = keyInput.length ? keyInput.val().trim().toLowerCase() : "";
        var rad =
          radSelect.length && radSelect.val()
            ? parseFloat(radSelect.val())
            : null;
        var cat =
          catInput.length && catInput.val()
            ? catInput.val().split(",") // Pass array of strings
            : null;

        if (loc) {
          spinner.show();
          var geocoder = new google.maps.Geocoder();
          geocoder.geocode({ address: loc }, function (results, status) {
            spinner.hide();
            if (status === "OK") {
              var pos = results[0].geometry.location;
              updateMapAndList(pos.lat(), pos.lng(), rad, key, cat);
            } else {
              alert("Geocode was not successful: " + status);
            }
          });
        } else {
          updateMapAndList(null, null, null, key, cat);
        }
      };

      btn.on("click", executeSearch);

      // Clear button functionality
      var clearBtn = $("#wpgmap_search_clear_" + id);

      // Show/hide clear button based on input values
      var toggleClearBtn = function () {
        var hasLocation = locInput.length && locInput.val().trim() !== "";
        var hasKeyword = keyInput.length && keyInput.val().trim() !== "";
        var hasRadius = radSelect.length && radSelect.val() !== "";
        var hasCategory = catInput.length && catInput.val() !== "";

        if (hasLocation || hasKeyword || hasRadius || hasCategory) {
          clearBtn.show();
        } else {
          clearBtn.hide();
        }
      };

      // Monitor input changes
      if (locInput.length) locInput.on("input", toggleClearBtn);
      if (keyInput.length) keyInput.on("input", toggleClearBtn);
      if (radSelect.length) radSelect.on("change", toggleClearBtn);
      if (catInput.length) catInput.on("change", toggleClearBtn);

      // Check initial values (for pre-loaded defaults)
      toggleClearBtn();

      // Clear button click handler
      clearBtn.on("click", function () {
        // Clear all inputs
        if (locInput.length) locInput.val("");
        if (keyInput.length) keyInput.val("");
        if (radSelect.length) radSelect.val("");
        if (catInput.length) catInput.val("");
        if (catPanel.length)
          catPanel.find(".wgm-front-cat-checkbox").prop("checked", false);

        // Hide clear button
        clearBtn.hide();

        // Remove search circle and center marker
        if (searchCircle) searchCircle.setMap(null);
        if (window["wgm_center_marker_" + id])
          window["wgm_center_marker_" + id].setMap(null);

        // Reset all markers to visible
        var markers = window.custom_markers[id] || [];
        markers.forEach(function (m) {
          m.setVisible(true);
        });

        // Hide not found message
        notFoundMsg.removeClass("wgm-show");

        // Trigger listing update with all markers
        var allMarkers = markers.map(function (m, i) {
          var d = Object.assign({}, m.wgm_data);
          d.marker_idx = i;
          return d;
        });
        $(window).trigger("wgm_search_update_" + id, { markers: allMarkers });

        // Fit bounds to show all markers
        if (markers.length > 0) {
          var bounds = new google.maps.LatLngBounds();
          markers.forEach(function (m) {
            bounds.extend(m.getPosition());
          });
          data.map.fitBounds(bounds);
        }
      });

      var triggerSearch = function (e) {
        if (e.key === "Enter") executeSearch();
      };
      locInput.on("keydown", triggerSearch);
      if (keyInput.length) keyInput.on("keydown", triggerSearch);

      // Category Tree Builder
      if (data.categories && catPanel.length) {
        var buildCategoryTree = function (parentId) {
          var html = "";
          var children = data.categories.filter(function (cat) {
            return cat.parent_id == parentId;
          });

          if (children.length > 0) {
            if (parentId != 0) {
              html += '<ul class="wgm-filter-sublist">';
            }
            children.forEach(function (cat) {
              html += '<li class="wgm-filter-item">';
              html += "<label>";
              html +=
                '<input type="checkbox" class="wgm-front-cat-checkbox" value="' +
                self.escapeHTML(cat.id) +
                '"> ';
              html +=
                '<span class="wgm-cat-name">' +
                self.escapeHTML(cat.name) +
                "</span>";
              html += "</label>";
              html += buildCategoryTree(cat.id);
              html += "</li>";
            });
            if (parentId != 0) {
              html += "</ul>";
            }
          }
          return html;
        };

        var listHtml =
          '<div class="wgm-filter-header">' +
          '<span class="wgm-filter-close" title="Close">&times;</span>' +
          "</div>" +
          '<ul class="wgm-filter-list">' +
          buildCategoryTree(0) +
          "</ul>";

        if (data.categories.length === 0)
          listHtml = '<p class="wgm-no-cats">No categories</p>';
        catPanel.html(listHtml);

        // Toggle Panel
        catToggle.on("click", function (e) {
          e.preventDefault();
          e.stopPropagation();
          catPanel.toggleClass("hidden");
        });

        // Prevent closing when clicking inside panel
        catPanel.on("click", function (e) {
          e.stopPropagation();
        });

        // Close Icon Click
        catPanel.on("click", ".wgm-filter-close", function (e) {
          e.preventDefault();
          e.stopPropagation();
          catPanel.addClass("hidden");
        });

        // Close on click outside
        // Prevent closing when clicking on labels/inputs specifically (extra safety)
        catPanel.on("click", "label, input", function (e) {
          e.stopPropagation();
        });

        // Close on click outside
        $(document).on("click", function (e) {
          if (
            $(e.target).closest("#wpgmap_filter_panel_" + id).length === 0 &&
            $(e.target).closest("#wpgmap_filter_toggle_" + id).length === 0
          ) {
            catPanel.addClass("hidden");
          }
        });

        // Handle Checkbox Change -> Update Hidden Input -> Trigger Search
        catPanel.on("change", ".wgm-front-cat-checkbox", function () {
          var $this = $(this);
          var isChecked = $this.prop("checked");
          var val = $this.val();

          // Cascading Logic: Check/Uncheck all children
          // 1. Find the parent <li> of the changed checkbox
          var $parentLi = $this.closest("li");
          // 2. Find any nested <ul> which contains children
          var $childrenList = $parentLi.find("ul");
          // 3. Find all checkboxes within that list and update them
          if ($childrenList.length) {
            $childrenList
              .find(".wgm-front-cat-checkbox")
              .prop("checked", isChecked);
          }

          var selectedIds = [];
          catPanel.find(".wgm-front-cat-checkbox:checked").each(function () {
            selectedIds.push($(this).val());
          });
          catInput.val(selectedIds.join(","));
          executeSearch(); // Trigger search/filter
        });
      }

      // Check initial values (for pre-loaded defaults)
      if (catInput.length && catInput.val()) {
        var preselected = catInput.val().split(",");
        preselected.forEach(function (pid) {
          catPanel
            .find('.wgm-front-cat-checkbox[value="' + pid + '"]')
            .prop("checked", true);
        });
        executeSearch();
      }
    },

    initDirections: function (id) {
      var data = this.instances[id];
      var self = this;
      var drawer = $("#wgm_direction_drawer_" + id);
      var legacyFromInput = $("#srm_gmap_from_" + id);
      var legacyToInput = $("#srm_gmap_to_" + id);

      // Define the open directions function for both modern drawer and legacy box
      window["wgm_open_directions_" + id] = function (destination) {
        if (drawer.length) {
          // Modern drawer exists
          var toInput = $("#wgm_dir_to_" + id);
          var fromInput = $("#wgm_dir_from_" + id);
          var toggleBtn = $("#wgm_drawer_toggle_" + id);
          toInput.val(destination);
          drawer.addClass("active");
          toggleBtn.fadeOut(200);
          fromInput.focus();
        } else if (legacyToInput.length) {
          // Legacy direction box exists
          legacyToInput.val(destination);
          legacyFromInput.focus();
          // Scroll to the legacy direction box
          $("html, body").animate(
            {
              scrollTop:
                legacyToInput.closest(".wgm-legacy-direction-box").offset()
                  .top - 20,
            },
            500
          );
        }
      };

      // Delegate events from infowindow and listing
      $(document).on("click", ".wgm-dir-link-" + id, function (e) {
        e.preventDefault();
        self.closeAllInfoWindows(id);
        window["wgm_open_directions_" + id]($(this).data("addr"));
      });

      $(document).on(
        "click",
        "#wgm_listing_area_" + id + " .wgm-get-direction-btn",
        function (e) {
          e.preventDefault();
          var idx = $(this).data("idx");
          var m = window.custom_markers[id][idx];
          if (m)
            window["wgm_open_directions_" + id](
              m.wgm_data.address || m.wgm_data.lat_lng
            );
        }
      );

      // Modern drawer initialization
      if (!drawer.length) {
        // No modern drawer, skip to legacy directions
        this.initLegacyDirections(id);
        return;
      }

      var toggleBtn = $("#wgm_drawer_toggle_" + id);
      var fromInput = $("#wgm_dir_from_" + id);
      var toInput = $("#wgm_dir_to_" + id);
      var goBtn = drawer.find(".wgm-dir-go-btn");
      var travelMode = "DRIVING";
      var resultsPanel = $("#wgm_dir_results_" + id);

      var directionsService = new google.maps.DirectionsService();
      var directionsRenderer = new google.maps.DirectionsRenderer({
        map: data.map,
        suppressMarkers: false,
        panel: resultsPanel[0],
      });

      toggleBtn.on("click", function () {
        drawer.addClass("active");
        toggleBtn.fadeOut(200);
      });
      drawer.find(".wgm-drawer-close").on("click", function () {
        drawer.removeClass("active");
        toggleBtn.fadeIn(200);
        directionsRenderer.setDirections({ routes: [] });
        resultsPanel.empty().removeClass("active");
      });

      if (typeof google.maps.places !== "undefined") {
        new google.maps.places.Autocomplete(fromInput[0]).bindTo(
          "bounds",
          data.map
        );
        new google.maps.places.Autocomplete(toInput[0]).bindTo(
          "bounds",
          data.map
        );
      }

      drawer.find(".wgm-travel-btn").on("click", function () {
        drawer.find(".wgm-travel-btn").removeClass("active");
        $(this).addClass("active");
        travelMode = $(this).data("mode");
      });

      drawer.find(".wgm-dir-adv-toggle").on("click", function () {
        drawer.find(".wgm-dir-adv-options").toggleClass("active");
      });

      var waypointsContainer = $("#wgm_waypoints_container_" + id);
      $("#wgm_add_waypoint_" + id).on("click", function () {
        var wpId = "wgm_wp_" + Date.now();
        var html =
          '<div class="wgm-dir-input-group wgm-waypoint-item" id="' +
          wpId +
          '">' +
          '<div class="wgm-dir-field-wrap">' +
          '<input type="text" class="wgm-dir-input wgm-waypoint-input" placeholder="' +
          data.i18n.via +
          '">' +
          '<button type="button" class="wgm-remove-waypoint-btn" title="' +
          data.i18n.remove_waypoint +
          '">' +
          '<svg viewBox="0 0 24 24" width="16" height="16"><path fill="currentColor" d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12 19 6.41z"></path></svg>' +
          "</button>" +
          "</div>" +
          "</div>";
        waypointsContainer.append(html);
        if (typeof google.maps.places !== "undefined") {
          new google.maps.places.Autocomplete(
            waypointsContainer.find("#" + wpId + " input")[0]
          ).bindTo("bounds", data.map);
        }
        $("#" + wpId + " .wgm-remove-waypoint-btn").on("click", function () {
          $("#" + wpId).remove();
        });
      });

      drawer.find(".wgm-current-loc-btn").on("click", function () {
        var btn = $(this);
        var targetInput = btn.siblings("input");
        if (navigator.geolocation) {
          btn.css("opacity", "0.5");
          navigator.geolocation.getCurrentPosition(
            function (position) {
              btn.css("opacity", "1");
              var geocoder = new google.maps.Geocoder();
              geocoder.geocode(
                {
                  location: {
                    lat: position.coords.latitude,
                    lng: position.coords.longitude,
                  },
                },
                function (results, status) {
                  if (status === "OK" && results[0])
                    targetInput.val(results[0].formatted_address);
                  else
                    targetInput.val(
                      position.coords.latitude + "," + position.coords.longitude
                    );
                }
              );
            },
            function (error) {
              btn.css("opacity", "1");
              console.error("Geolocation error:", error);
              alert(
                (data.i18n.geo_fail || "Unable to get your location") +
                  ": " +
                  error.message
              );
            }
          );
        } else {
          alert(data.i18n.geo_not_supported);
        }
      });

      goBtn.on("click", function () {
        var origin = fromInput.val().trim();
        var destination = toInput.val().trim();
        var waypoints = [];
        drawer.find(".wgm-waypoint-input").each(function () {
          var val = $(this).val().trim();
          if (val) waypoints.push({ location: val, stopover: true });
        });

        if (!origin || !destination) {
          alert(data.i18n.origin_dest_required);
          return;
        }

        var request = {
          origin: origin,
          destination: destination,
          waypoints: waypoints,
          optimizeWaypoints: true,
          travelMode: google.maps.TravelMode[travelMode],
          avoidTolls: drawer.find(".wgm-avoid-tolls").is(":checked"),
          avoidHighways: drawer.find(".wgm-avoid-highways").is(":checked"),
          avoidFerries: drawer.find(".wgm-avoid-ferries").is(":checked"),
        };

        directionsService.route(request, function (result, status) {
          if (status == "OK") {
            directionsRenderer.setDirections(result);
            resultsPanel.addClass("active");
          } else {
            alert("Directions request failed: " + status);
          }
        });
      });

      this.initLegacyDirections(id);
    },

    initLegacyDirections: function (id) {
      var data = this.instances[id];
      var legacySubmit = $("#wp_gmap_submit_" + id);
      var directionsService = new google.maps.DirectionsService();

      if (legacySubmit.length) {
        var legacyRenderer = new google.maps.DirectionsRenderer({
          map: data.map,
          panel: document.getElementById("wp_gmap_directions_" + id),
        });

        // Initialize Google Places Autocomplete on inputs (if Places API is available)
        var fromInput = document.getElementById("srm_gmap_from_" + id);
        var toInput = document.getElementById("srm_gmap_to_" + id);

        // Enable autocomplete on legacy direction inputs
        if (
          typeof google.maps.places !== "undefined" &&
          data.options.enable_direction_form_auto_complete
        ) {
          if (fromInput) {
            var fromAutocomplete = new google.maps.places.Autocomplete(
              fromInput,
              {
                fields: ["formatted_address", "geometry", "name"],
              }
            );
            fromAutocomplete.bindTo("bounds", data.map);
          }

          if (toInput) {
            var toAutocomplete = new google.maps.places.Autocomplete(toInput, {
              fields: ["formatted_address", "geometry", "name"],
            });
            toAutocomplete.bindTo("bounds", data.map);
          }
        }

        // Travel mode switcher for legacy box
        $(".wgm-legacy-travel-btn").on("click", function () {
          $(".wgm-legacy-travel-btn").removeClass("active");
          $(this).addClass("active");
          var mode = $(this).data("mode");
          $("#srm_gmap_mode_" + id).val(mode);
        });

        // Location buttons for legacy box
        $(".wgm-legacy-loc-btn").on("click", function () {
          var btn = $(this);
          var targetInput = btn.siblings("input");
          if (navigator.geolocation) {
            btn.css("opacity", "0.5");
            navigator.geolocation.getCurrentPosition(
              function (position) {
                btn.css("opacity", "1");
                var geocoder = new google.maps.Geocoder();
                geocoder.geocode(
                  {
                    location: {
                      lat: position.coords.latitude,
                      lng: position.coords.longitude,
                    },
                  },
                  function (results, status) {
                    if (status === "OK" && results[0])
                      targetInput.val(results[0].formatted_address);
                    else
                      targetInput.val(
                        position.coords.latitude +
                          "," +
                          position.coords.longitude
                      );
                  }
                );
              },
              function (error) {
                btn.css("opacity", "1");
                console.error("Geolocation error:", error);
                alert(
                  (data.i18n.geo_fail || "Unable to get your location") +
                    ": " +
                    error.message
                );
              }
            );
          } else {
            alert(
              data.i18n.geo_not_supported || "Geolocation is not supported"
            );
          }
        });

        legacySubmit.on("click", function () {
          var mode = $("#srm_gmap_mode_" + id).val(),
            from = $("#srm_gmap_from_" + id).val(),
            to = $("#srm_gmap_to_" + id).val();
          if (!from || !to) {
            $("#wp_gmap_results_" + id).hide();
            return;
          }
          $("#wp_gmap_loading_" + id).addClass("wgm-show");
          directionsService.route(
            {
              origin: from,
              destination: to,
              travelMode: google.maps.DirectionsTravelMode[mode],
              unitSystem:
                data.distance_unit_system === "METRIC"
                  ? google.maps.UnitSystem.METRIC
                  : google.maps.UnitSystem.IMPERIAL,
            },
            function (response, status) {
              $("#wp_gmap_loading_" + id).removeClass("wgm-show");
              if (status === "OK") {
                legacyRenderer.setDirections(response);
                $("#wp_gmap_results_" + id).show();
              } else {
                $("#wp_gmap_results_" + id).hide();
                alert("Directions request failed: " + status);
              }
            }
          );
        });
      }
    },

    initLightbox: function (id) {
      var data = this.instances[id];
      var overlay = $("#wgm-lightbox-overlay_" + id);
      var img = $("#wgm-lightbox-img_" + id);
      if (!overlay.length) return;

      // Scope to this instance's container to prevent conflicts with multiple maps
      var container = $(".wgm-map-id-" + data.map_id).eq(
        $(".wgm-map-id-" + data.map_id).index(
          $("#srm_gmp_embed_" + id).closest(".wgm-map-id-" + data.map_id)
        )
      );

      // Use instance-specific container instead of document
      container.on("click", ".wgm-lightbox-trigger", function (e) {
        e.stopPropagation();
        img.attr("src", $(this).attr("src"));
        overlay.addClass("show");
      });

      overlay.on("click", function (e) {
        if (e.target !== img[0]) overlay.removeClass("show");
      });
      overlay.find(".wgm-lightbox-close").on("click", function () {
        overlay.removeClass("show");
      });
      $(document).on("keydown", function (e) {
        if (e.key === "Escape") overlay.removeClass("show");
      });
    },
  };
})(jQuery);
