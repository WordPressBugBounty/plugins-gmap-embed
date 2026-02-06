(function ($) {
  "use strict";

  /**
   * Internal State Management
   */
  var state = {
    map: null,
    infowindow: null,
    icon: "https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png",
    current_markers: {},
    current_infowindows: {},
    new_marker: null,
    new_marker_infowindow: null,
    existing_marker: null,
    existing_marker_infowindow: null,
    is_marker_edit: false,
    no_of_markers: 0,
  };

  // Expose necessary functions to window for other scripts (e.g., TinyMCE, Icon Selector)
  window.wgm_admin_map = state;

  /**
   * Select element by ID helper
   *
   * @param {string} id - The ID of the element to select
   * @return {HTMLElement|null} The DOM element or null
   * @since 1.0.0
   */
  function _wgm_e(id) {
    return document.getElementById(id);
  }

  /**
   * Attach click handler to marker
   *
   * @param {google.maps.Marker} marker - The marker object
   * @param {Function} handler - The event handler function
   * @since 1.0.0
   */
  function wgm_attachClickHandler(marker, handler) {
    marker.addListener("click", handler);
  }

  /**
   * Generates and updates infowindow content for new or existing markers.
   * Handles both visual and text editor modes for description.
   */
  window.wgm_generate_infowindow = function () {
    var markerNameVal = $("#wpgmap_marker_name").val();
    var safeMarkerName = markerNameVal
      ? $("<div>").text(markerNameVal).html()
      : "";
    var titleHtml =
      '<p class="info_content_title" style="font-size:16px;font-weight:bold;margin:0 0 5px 0;">' +
      safeMarkerName +
      "</p>";
    var descHtml = tmce_getContent("wpgmap_marker_desc", "wpgmap_marker_desc");

    if (state.is_marker_edit) {
      var marker_id = parseInt($(".wpgmap_marker_update").attr("markerid"), 10);
      state.existing_marker_infowindow = state.current_infowindows[marker_id];

      if (!state.existing_marker_infowindow) {
        state.existing_marker_infowindow = new google.maps.InfoWindow();
        state.current_infowindows[marker_id] = state.existing_marker_infowindow;
      }

      var editIcon =
        '<span class="wgm_marker_edit_iw" data-markerid="' +
        marker_id +
        '" style="float:right; cursor:pointer;" title="Edit Marker"><i class="dashicons dashicons-edit"></i></span>';
      state.existing_marker_infowindow.setContent(
        editIcon + titleHtml + descHtml
      );

      if (
        state.existing_marker &&
        $("#wpgmap_marker_infowindow_show").val() === "1"
      ) {
        state.existing_marker_infowindow.open({
          anchor: state.existing_marker,
          shouldFocus: false,
        });
      }
    } else {
      if (!state.new_marker_infowindow) {
        state.new_marker_infowindow = new google.maps.InfoWindow();
      }

      state.new_marker_infowindow.setContent(titleHtml + descHtml);

      if (
        state.new_marker &&
        $("#wpgmap_marker_infowindow_show").val() === "1"
      ) {
        state.new_marker_infowindow.open({
          anchor: state.new_marker,
          shouldFocus: false,
        });
      }
    }
  };

  /**
   * Wrapper for generating infowindow
   */
  function wgm_openInfoWindow() {
    wgm_generate_infowindow();
  }

  /**
   * Defining Map event listeners
   *
   * @param {google.maps.Map} map - The map object
   */
  function wgm_addMapListeners(map) {
    map.addListener("center_changed", function () {
      $("#wpgmap_center_lat_lng").val(
        map.center.lat() + "," + map.center.lng()
      );
    });

    map.addListener("zoom_changed", function () {
      $("#wpgmap_map_zoom").val(map.zoom);
    });
  }

  /**
   * Initializes listeners for an already existing map instance
   */
  function wgm_generateAlreadyInitializedMap(map_type, center_lat, center_lng) {
    if (state.map && typeof state.map.setMapTypeId === "function") {
      state.map.setMapTypeId(
        google.maps.MapTypeId[map_type] || google.maps.MapTypeId.ROADMAP
      );
      state.map.setCenter({ lat: center_lat, lng: center_lng });
      wgm_addMapListeners(state.map);
    }
  }

  /**
   * Generate map settings object based on map type
   *
   * @param {string} map_type
   * @param {number} center_lat
   * @param {number} center_lng
   * @param {number} zoom
   * @returns {object} Google Maps options object
   * @since 1.0.0
   */
  function wgm_setMapSettingsByMapType(map_type, center_lat, center_lng, zoom) {
    var wgm_gmap_settings = {
      center: { lat: center_lat, lng: center_lng },
      zoom: zoom,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
    };
    if (map_type == "ROADMAP") {
      wgm_gmap_settings.mapTypeId = google.maps.MapTypeId.ROADMAP;
    } else if (map_type == "SATELLITE") {
      wgm_gmap_settings.mapTypeId = google.maps.MapTypeId.SATELLITE;
    } else if (map_type == "HYBRID") {
      wgm_gmap_settings.mapTypeId = google.maps.MapTypeId.HYBRID;
    } else if (map_type == "TERRAIN") {
      wgm_gmap_settings.mapTypeId = google.maps.MapTypeId.TERRAIN;
    }
    return wgm_gmap_settings;
  }

  /**
   * Reverse geocoding to update address field from coordinates
   */
  function wgm_reverse_geocode(latLng) {
    if (typeof google === "undefined" || !google.maps.Geocoder) return;
    var geocoder = new google.maps.Geocoder();
    geocoder.geocode({ location: latLng }, function (results, status) {
      if (status === "OK") {
        if (results[0]) {
          $("#wpgmap_marker_address").val(results[0].formatted_address);
        }
      } else {
        console.warn("WGM: Geocoder failed due to: " + status);
      }
    });
  }

  /**
   * Close all open infowindows on the map
   */
  function wgm_close_all_infowindows() {
    Object.values(state.current_infowindows).forEach(function (iw) {
      if (iw) iw.close();
    });
    if (state.new_marker_infowindow) state.new_marker_infowindow.close();
    if (state.existing_marker_infowindow)
      state.existing_marker_infowindow.close();
  }

  /**
   * Create a new marker at a specific location
   */
  function wgm_createMarkerAt(latLng) {
    if (!state.map) return;
    var baseOptions = {
      position: latLng,
      draggable: true,
      map: state.map,
      animation: google.maps.Animation ? google.maps.Animation.DROP : undefined,
    };

    state.new_marker = new google.maps.Marker(baseOptions);
    window.wgm_generate_infowindow();

    state.new_marker.addListener("click", function () {
      if (state.new_marker_infowindow) {
        var isOpen = !!state.new_marker_infowindow.getMap();
        wgm_close_all_infowindows();
        if (!isOpen) {
          state.new_marker_infowindow.open({
            anchor: state.new_marker,
            shouldFocus: false,
          });
        }
      }
    });

    if (state.new_marker_infowindow) {
      wgm_close_all_infowindows();
      state.new_marker_infowindow.open({
        anchor: state.new_marker,
        shouldFocus: false,
      });
    }
    wgm_addMarkerDragendListener(state.new_marker);
  }

  /**
   * Attach dragend listener to a marker to update coordinates and address
   *
   * @param {google.maps.Marker} marker
   * @since 1.0.0
   */
  function wgm_addMarkerDragendListener(marker) {
    marker.addListener("dragend", function (markerLocation) {
      var lat = markerLocation.latLng.lat();
      var lng = markerLocation.latLng.lng();
      _wgm_e("wpgmap_marker_lat_lng").value = lat + "," + lng;
      wgm_reverse_geocode(markerLocation.latLng);
    });
  }

  /**
   * Initialize Map and Autocomplete functionality
   */
  window.wgm_initAutocomplete = function (
    id,
    input,
    center_lat,
    center_lng,
    map_type,
    zoom
  ) {
    if (state.map) {
      wgm_generateAlreadyInitializedMap(map_type, center_lat, center_lng);
      return false;
    }

    var mapDiv = _wgm_e(id);
    if (!mapDiv) return;

    var gmapSettings = wgm_setMapSettingsByMapType(
      map_type,
      center_lat,
      center_lng,
      zoom
    );

    state.map = new google.maps.Map(mapDiv, gmapSettings);

    if (typeof wgm_theme_json !== "undefined" && wgm_theme_json.length > 0) {
      try {
        state.map.setOptions({ styles: JSON.parse(wgm_theme_json) });
      } catch (e) {
        console.error("WGM: Failed to parse theme JSON", e);
      }
    }

    // Address Autocomplete for Marker Form
    var markerAddressInput = document.getElementById("wpgmap_marker_address");
    if (markerAddressInput) {
      var markerAutocomplete = new google.maps.places.Autocomplete(
        markerAddressInput
      );
      markerAutocomplete.bindTo("bounds", state.map);
      markerAutocomplete.addListener("place_changed", function () {
        var place = markerAutocomplete.getPlace();
        if (!place.geometry) return;

        var lat = place.geometry.location.lat();
        var lng = place.geometry.location.lng();
        $("#wpgmap_marker_lat_lng").val(lat + "," + lng);

        var latLng = new google.maps.LatLng(lat, lng);
        if (state.is_marker_edit) {
          if (state.existing_marker) state.existing_marker.setPosition(latLng);
        } else {
          if (state.new_marker) {
            state.new_marker.setPosition(latLng);
          } else {
            wgm_createMarkerAt(latLng);
          }
        }
        state.map.panTo(latLng);
      });
    }

    google.maps.event.addListener(state.map, "rightclick", function (event) {
      generateMarkerInfoByRightClick(event);
    });

    var wgm_input = document.getElementById(input);
    if (wgm_input) {
      var wgm_searchBox = new google.maps.places.SearchBox(wgm_input);
      state.map.controls[google.maps.ControlPosition.TOP_LEFT].push(wgm_input);

      state.map.addListener("bounds_changed", function () {
        wgm_searchBox.setBounds(state.map.getBounds());
      });

      wgm_searchBox.addListener("places_changed", function () {
        var wgm_places = wgm_searchBox.getPlaces();
        if (wgm_places.length === 0) return;

        var wgm_bounds = new google.maps.LatLngBounds();
        wgm_places.forEach(function (place) {
          if (!place.geometry) return;

          $("#wpgmap_latlng").val(
            place.geometry.location.lat() + "," + place.geometry.location.lng()
          );

          if (place.geometry.viewport) {
            wgm_bounds.union(place.geometry.viewport);
          } else {
            wgm_bounds.extend(place.geometry.location);
          }
        });
        state.map.fitBounds(wgm_bounds);
      });
    }

    // Load Markers from Server
    var ajaxData = {
      action: "wpgmapembed_get_markers_by_map_id",
      _wpnonce: wgm_l.nonces.wpgmapembed_get_markers_by_map_id,
      data: {
        map_id: wgm_l.wgm_object.map_id,
      },
    };

    $.post(ajaxurl, ajaxData, function (response) {
      try {
        if (typeof response === "string") response = JSON.parse(response);
      } catch (e) {
        console.error("WGM: Failed to parse markers response", e);
        return;
      }

      if (!response || !response.markers) return;

      state.no_of_markers = response.markers.length;

      if (state.no_of_markers === 0) {
        $(".wgm_marker_create_hints").show();
      }

      if (state.no_of_markers >= 1 && wgm_l.is_premium_user !== "1") {
        $(".add_new_marker_btn_area .add_new_marker").css({ opacity: 0.5 });
        $(".add_new_marker_btn_area .wgm-pro-label").show();
      }

      response.markers.forEach(function (marker) {
        var coords = marker.lat_lng.split(",");
        var markerOptions = {
          position: new google.maps.LatLng(coords[0], coords[1]),
          title: marker.marker_name,
          map: state.map,
          animation: google.maps.Animation
            ? google.maps.Animation.DROP
            : undefined,
          icon: marker.icon || undefined,
        };

        var customMarker = new google.maps.Marker(markerOptions);

        if (marker.have_marker_link === "1") {
          wgm_attachClickHandler(customMarker, function () {
            var target =
              marker.marker_link_new_tab === "1" ? "_blank" : "_self";
            window.open(marker.marker_link, target);
          });
        }

        customMarker.addListener("click", function () {
          var iw = state.current_infowindows[parseInt(marker.id, 10)];
          if (iw) {
            var isOpen = !!iw.getMap();
            wgm_close_all_infowindows();
            if (!isOpen) {
              iw.open({
                anchor: state.current_markers[parseInt(marker.id, 10)],
                shouldFocus: false,
              });
            }
          }
        });

        // Infowindow Content
        var safeName = marker.marker_name
          ? $("<div>").text(marker.marker_name).html()
          : "";
        var titleHtml =
          '<p class="info_content_title" style="font-size:16px;font-weight:bold;margin:0 0 5px 0;">' +
          safeName +
          "</p>";
        var editIcon =
          '<span class="wgm_marker_edit_iw" data-markerid="' +
          marker.id +
          '" style="float:right; cursor:pointer;" title="Edit Marker"><i class="dashicons dashicons-edit"></i></span>';

        var iw = new google.maps.InfoWindow({
          content: editIcon + titleHtml + marker.marker_desc,
        });

        if (marker.show_desc_by_default === "1") {
          iw.open({ anchor: customMarker, shouldFocus: false });
        }

        state.current_markers[parseInt(marker.id, 10)] = customMarker;
        state.current_infowindows[parseInt(marker.id, 10)] = iw;
      });
    }).fail(function (xhr, status, error) {
      console.error("WGM: Failed to load markers", error);
    });

    wgm_addMapListeners(state.map);
  };

  /**
   * Handle Right Click on Map to Create/Edit Marker
   */
  function generateMarkerInfoByRightClick(event) {
    if (!$(".add_new_marker_form").hasClass("wgm_active")) return false;

    if (state.new_marker) {
      alert("Please save current marker first!");
      return false;
    }

    var lat = event.latLng.lat();
    var lng = event.latLng.lng();

    var baseOptions = {
      position: event.latLng,
      draggable: true,
      map: state.map,
      animation: google.maps.Animation ? google.maps.Animation.DROP : undefined,
    };

    if (state.is_marker_edit) {
      if (
        state.existing_marker &&
        typeof state.existing_marker.setMap === "function"
      ) {
        state.existing_marker.setMap(null);
      }

      state.existing_marker = new google.maps.Marker(baseOptions);
      window.wgm_generate_infowindow();

      state.existing_marker.addListener("click", function () {
        if (state.existing_marker_infowindow) {
          var isOpen = !!state.existing_marker_infowindow.getMap();
          wgm_close_all_infowindows();
          if (!isOpen) {
            state.existing_marker_infowindow.open({
              anchor: state.existing_marker,
              shouldFocus: false,
            });
          }
        }
      });

      if (state.existing_marker_infowindow) {
        wgm_close_all_infowindows();
        state.existing_marker_infowindow.open({
          anchor: state.existing_marker,
          shouldFocus: false,
        });
      }
      wgm_addMarkerDragendListener(state.existing_marker);
    } else {
      wgm_createMarkerAt(event.latLng);
    }

    $("#wpgmap_marker_lat_lng").val(lat + "," + lng);
    wgm_reverse_geocode(event.latLng);
  }

  /**
   * Set content for TinyMCE editor or Textarea
   *
   * @param {string} content
   * @param {string} editor_id
   * @param {string} textarea_id
   */
  /**
   * Set content for TinyMCE editor or Textarea
   *
   * @param {string} content
   * @param {string} editor_id
   * @param {string} textarea_id
   */
  function tmce_setContent(content, editor_id, textarea_id) {
    if (typeof editor_id == "undefined") {
      editor_id = wpActiveEditor;
    }
    if (typeof textarea_id == "undefined") {
      textarea_id = editor_id;
    }

    if (
      jQuery("#wp-" + editor_id + "-wrap").hasClass("tmce-active") &&
      tinyMCE.get(editor_id)
    ) {
      content = content.replace(/&gt;/g, ">").replace(/&lt;/g, "<");
      return tinyMCE.get(editor_id).setContent(content);
    } else {
      return jQuery("#" + textarea_id).val(content);
    }
  }
  window.tmce_setContent = tmce_setContent;

  /**
   * Get content from TinyMCE editor or Textarea
   *
   * @param {string} editor_id
   * @param {string} textarea_id
   */
  function tmce_getContent(editor_id, textarea_id) {
    if (typeof editor_id == "undefined") {
      editor_id = wpActiveEditor;
    }
    if (typeof textarea_id == "undefined") {
      textarea_id = editor_id;
    }

    if (
      jQuery("#wp-" + editor_id + "-wrap").hasClass("tmce-active") &&
      tinyMCE.get(editor_id)
    ) {
      return tinyMCE.get(editor_id).getContent();
    } else {
      return jQuery("#" + textarea_id).val();
    }
  }
  window.tmce_getContent = tmce_getContent;

  /**
   * Change current marker icon
   */
  window.wpgmapChangeCurrentMarkerIcon = function (elem) {
    if (!elem || !elem.src) return;
    var icon_url = elem.src;
    $("#wpgmap_marker_icon").val(icon_url);
    $("#wpgmap_marker_icon_preview").attr("src", icon_url);
    $("#TB_closeWindowButton").click();

    if (state.is_marker_edit) {
      if (state.existing_marker) state.existing_marker.setIcon(icon_url);
    } else {
      if (state.new_marker) state.new_marker.setIcon(icon_url);
    }
  };

  /**
   * Document Ready Handler
   */
  jQuery(document).ready(function ($) {
    // Initialize Select2
    if ($.fn.select2) {
      $(".wgm-select2").select2({
        placeholder: "Select categories",
        allowClear: true,
        width: "100%",
      });
    }
    /**
     * On zoom level change, render map with new zoom level LIVE
     *
     * @since 1.0.0
     */
    $(document.body)
      .find("#wpgmap_map_zoom")
      .on("keyup", function (element) {
        // var point = wgm_marker1.getPosition(); // Get marker position
        if (state.map) {
          state.map.panTo(state.map.center); // Pan map to that position
          var current_zoom = parseInt(
            document.getElementById("wpgmap_map_zoom").value
          );
          if (!isNaN(current_zoom)) {
            setTimeout(function () {
              state.map.setZoom(current_zoom);
            }, 900); // Zoom in after 900 ms
          }
        }
      });

    /**
     * On title field text change, update map title LIVE
     *
     * @since 1.0.0
     */
    $(document.body)
      .find("#wpgmap_title")
      .on("keyup", function (element) {
        var _wpgmap_title = $(this).val();
        $("#wpgmap_heading_preview")
          .css({ display: "block" })
          .html(
            _wpgmap_title
              .replace(/&/g, "&amp;")
              .replace(/</g, "&lt;")
              .replace(/>/g, "&gt;")
              .replace(/"/g, "&quot;")
              .replace(/'/g, "&#039;")
          );
      });

    /**
     * On map type change, render different types of map LIVE
     *
     * @since 1.0.0
     */
    $(document.body)
      .find("#wpgmap_map_type")
      .on("change", function (element) {
        // wgm_marker1.setMap(null);
        var map_type = $(this).val();
        if (state.map) {
          state.map.setMapTypeId(map_type.toLowerCase());
        }
      });

    /**
     * On map theme presets change, render different types of map based on theme
     *
     * @since 1.8.6
     */
    $(document.body)
      .find("#wpgmap_map_theme")
      .on("change", function (element) {
        if (state.map) {
          var val = $(this).val();
          if (val) {
            try {
              var wgm_theme_json = JSON.parse(val);
              state.map.setOptions({ styles: wgm_theme_json });
              $(document.body).find("#wgm_theme_json").val(val);
            } catch (e) {
              console.error("Invalid theme JSON");
            }
          }
        }
      });

    /**
     * On map theme presets JSON blur, render different types of map based on theme
     *
     * @since 1.8.6
     */
    $(document.body)
      .find("#wgm_theme_json")
      .on("blur", function (element) {
        if (state.map) {
          var val = $(this).val();
          if (val) {
            try {
              var wgm_theme_json = JSON.parse(val);
              state.map.setOptions({ styles: wgm_theme_json });
            } catch (e) {
              console.error("Invalid theme JSON");
            }
          }
        }
      });

    /**
     * Rendering tab contents
     *
     * @since 1.0.0
     */
    $(document.body)
      .find(".wgm_wpgmap_tab li")
      .on("click", function (e) {
        e.preventDefault();
        $(".wgm_wpgmap_tab li").removeClass("active");
        $(this).addClass("active");

        $(".wp-gmap-tab-contents").addClass("hidden");
        var wpgmap_id = $(this).attr("id");
        $("." + wpgmap_id).removeClass("hidden");
        if (wpgmap_id === "wgm_gmap_markers") {
          $(".wgm_gmap_marker_list").css("display", "block");
          $(".add_new_marker_form").css("display", "none");
        } else {
          $(".wgm_gmap_marker_list").css("display", "none");
        }
      });

    // ========================================For Media Upload in Marker===================================
    $("#wpgmap_upload_marker_icon").click(function () {
      var custom_uploader;
      if (custom_uploader) {
        custom_uploader.open();
        return;
      }

      custom_uploader = wp.media.frames.file_frame = wp.media({
        title: "Choose Image",
        button: {
          text: "Choose Image",
        },
        multiple: false,
      });

      custom_uploader.on("select", function () {
        var attachment = custom_uploader
          .state()
          .get("selection")
          .first()
          .toJSON();

        var data = {
          action: "wpgmapembed_save_marker_icon",
          _wpnonce: wgm_l.nonces.wpgmapembed_save_marker_icon,
          data: {
            icon_url: attachment.url,
          },
        };

        $.post(ajaxurl, data, function (response) {
          try {
            if (typeof response === "string") {
              response = JSON.parse(response);
            }
          } catch (e) {
            console.error("Failed to parse response", e);
            return;
          }

          $(document.body).find("#wpgmap_marker_icon").val(response.icon_url);
          $(document.body)
            .find("#wpgmap_marker_icon_preview")
            .attr("src", response.icon_url);
          var elm = {};
          elm.src = response.icon_url;
          wpgmapChangeCurrentMarkerIcon(elm);
        }).fail(function (xhr, status, error) {
          alert("Failed to save marker icon: " + error);
        });
      });

      // Open the uploader dialog
      custom_uploader.open();
    });

    // ========================================For Media Upload in Marker Image===================================
    $("#wpgmap_upload_marker_image").click(function (e) {
      e.preventDefault();

      // Check if this is a premium feature
      if ($(this).hasClass("wgm_enable_premium")) {
        return; // Let the common.js handler show the premium notice
      }

      var custom_uploader;
      if (custom_uploader) {
        custom_uploader.open();
        return;
      }

      custom_uploader = wp.media.frames.file_frame = wp.media({
        title: "Choose Marker Image",
        button: {
          text: "Choose Image",
        },
        multiple: false,
      });

      custom_uploader.on("select", function () {
        var attachment = custom_uploader
          .state()
          .get("selection")
          .first()
          .toJSON();

        $(document.body).find("#wpgmap_marker_image").val(attachment.url);
        $(document.body)
          .find("#wpgmap_marker_image_preview")
          .attr("src", attachment.url)
          .show();
        $(document.body).find("#wpgmap_remove_marker_image").show();
      });

      // Open the uploader dialog
      custom_uploader.open();
    });

    // ==============================
    // Create 'keyup_event' tinymce plugin
    if (
      typeof tinymce !== "undefined" &&
      tinymce.PluginManager &&
      typeof tinymce.PluginManager.add === "function"
    ) {
      tinymce.PluginManager.add("keyup_event", function (editor, _url) {
        if (editor.id === "wpgmap_marker_desc") {
          // Create keyup event
          editor.on("keyup", function () {
            wgm_generate_infowindow();
          });
        }
      });
    }

    $("#wpgmap_marker_name,#wpgmap_marker_desc").on(
      "keyup",
      function (element) {
        wgm_generate_infowindow();
      }
    );

    $("#wpgmap_marker_link").on("blur", function () {
      var url = $(this).val();
      if (state.is_marker_edit) {
        if (state.existing_marker) {
          state.existing_marker.url = url;
          google.maps.event.clearListeners(state.existing_marker, "click");
          state.existing_marker.addListener("click", function () {
            var target = $("#wpgmap_marker_link_new_tab").is(":checked")
              ? "_blank"
              : "_self";
            window.open(this.url, target);
          });
        }
      } else {
        if (state.new_marker) {
          state.new_marker.url = url;
          google.maps.event.clearListeners(state.new_marker, "click");
          state.new_marker.addListener("click", function () {
            var target = $("#wpgmap_marker_link_new_tab").is(":checked")
              ? "_blank"
              : "_self";
            window.open(this.url, target);
          });
        }
      }
    });

    function generateMarkersListView() {
      $("#wgm_gmap_marker_list").DataTable().ajax.reload();
    }

    // Marker delete
    $(document.body).on("click", ".wpgmap_marker_trash", function (e) {
      e.preventDefault();
      var $parent = $(this).closest("tr");
      $parent.find(".spinner").css("visibility", "visible");

      if (confirm("Are you sure to delete?")) {
        var marker_id = $(this).attr("map_marker_id");
        var ajaxData = {
          action: "wpgmapembed_delete_marker",
          _wpnonce: wgm_l.nonces.wpgmapembed_delete_marker,
          data: { marker_id: marker_id },
        };

        $.post(ajaxurl, ajaxData, function (response) {
          try {
            if (typeof response === "string") response = JSON.parse(response);
          } catch (err) {
            console.error("WGM: Invalid delete response", err);
            $parent.find(".spinner").css("visibility", "hidden");
            return;
          }

          generateMarkersListView();
          $parent.find(".spinner").css("visibility", "hidden");
          if (state.current_markers[parseInt(marker_id, 10)]) {
            state.current_markers[parseInt(marker_id, 10)].setMap(null);
          }
          $("#marker_success").html("Marker removed successfully.");
          state.no_of_markers--;

          if (state.no_of_markers === 0 && wgm_l.is_premium_user !== "1") {
            $(".add_new_marker_btn_area .add_new_marker").css({ opacity: 1 });
            $(".add_new_marker_btn_area .wgm-pro-label").hide();
          }
        }).fail(function (xhr, status, error) {
          $parent.find(".spinner").css("visibility", "hidden");
          alert("Failed to delete marker: " + error);
        });
      } else {
        $parent.find(".spinner").css("visibility", "hidden");
      }
    });

    // Marker view
    $(document.body).on("click", ".wpgmap_marker_view", function (e) {
      e.preventDefault();
      var id = $(this).attr("map_marker_id");
      var marker = state.current_markers[id];
      if (marker && state.map) {
        state.map.panTo(marker.getPosition());
      }
    });

    // Generic Marker Data Loader
    function wgm_load_marker_data(marker_id, $spinner_context) {
      if ($spinner_context) {
        $spinner_context.css("visibility", "visible");
      }

      state.is_marker_edit = true;
      state.existing_marker = state.current_markers[marker_id];

      var ajaxData = {
        action: "wpgmapembed_get_marker_data_by_marker_id",
        _wpnonce: wgm_l.nonces.wpgmapembed_get_marker_data_by_marker_id,
        data: { marker_id: marker_id },
      };

      $.post(ajaxurl, ajaxData, function (response) {
        try {
          if (typeof response === "string") response = JSON.parse(response);
        } catch (err) {
          console.error("WGM: Invalid marker data response", err);
          if ($spinner_context) {
            $spinner_context.css("visibility", "hidden");
          }
          return;
        }

        $("#wpgmap_marker_name").val(response.marker_name);
        $("#wpgmap_marker_address").val(response.address);
        $("#wpgmap_marker_lat_lng").val(response.lat_lng);
        $("#wpgmap_marker_link").val(response.marker_link);
        $("#wpgmap_marker_icon").val(response.icon);
        $("#wpgmap_marker_image").val(response.marker_image);

        if (response.marker_image) {
          $("#wpgmap_marker_image_preview")
            .attr("src", response.marker_image)
            .show();
          $("#wpgmap_remove_marker_image").show();
        } else {
          $("#wpgmap_marker_image_preview").hide();
          $("#wpgmap_remove_marker_image").hide();
        }

        if (response.have_marker_link === "1") {
          $("#wpgmap_marker_link_area").show();
        } else {
          $("#wpgmap_marker_link_area").hide();
        }

        $("#wpgmap_marker_link_new_tab").prop(
          "checked",
          response.marker_link_new_tab === "1"
        );
        $("#wpgmap_marker_animation").val(response.animation);

        if (response.category_id) {
          var selectedCats = response.category_id.toString().split(",");
          $("#wpgmap_marker_category").val(selectedCats).trigger("change");
        } else {
          $("#wpgmap_marker_category").val([]).trigger("change");
        }

        $("#wpgmap_marker_infowindow_show")
          .val(response.show_desc_by_default)
          .change();
        $("#wpgmap_have_marker_link").val(response.have_marker_link).change();

        $(".wpgmap_marker_add,.wpgmap_marker_update").attr(
          "markerid",
          marker_id
        );
        $(".wpgmap_marker_add")
          .removeClass("wpgmap_marker_add")
          .addClass("wpgmap_marker_update")
          .css("background-color", "#00a2f3")
          .html(
            '<i class="dashicons dashicons-location" style="line-height: 1.6;"></i><b>Update Marker</b>'
          );

        tmce_setContent(
          response.marker_desc,
          "wpgmap_marker_desc",
          "wpgmap_marker_desc"
        );

        if ($spinner_context) {
          $spinner_context.css("visibility", "hidden");
        }

        // Switch to General tab or show form based on context
        $(".wgm_wpgmap_tab li").removeClass("active");
        $("#wgm_gmap_markers").addClass("active");

        $(".wp-gmap-tab-contents").addClass("hidden");
        $(".wgm_gmap_markers").removeClass("hidden");

        $(".add_new_marker_form").show();
        $(".wgm_gmap_marker_list").hide();
        $("#marker_errors,#marker_success").html("");
        $("#wpgmap_marker_icon_preview").attr("src", response.icon);

        if (state.existing_marker) {
          state.map.panTo(state.existing_marker.getPosition());
          state.existing_marker.setDraggable(true);
          wgm_addMarkerDragendListener(state.existing_marker);
        }

        state.existing_marker_infowindow = new google.maps.InfoWindow({
          content:
            '<p class="info_content_title" style="font-size:16px;font-weight:bold;">' +
            response.marker_name +
            "</p>" +
            response.address,
        });
      }).fail(function (xhr, status, error) {
        if ($spinner_context) {
          $spinner_context.css("visibility", "hidden");
        }
        alert("Failed to load marker data: " + error);
      });
    }

    // Marker Edit via List
    $(document.body).on("click", ".wpgmap_marker_edit", function (e) {
      e.preventDefault();
      var $parent = $(this).closest("tr");
      var marker_id = $(this).attr("map_marker_id");
      wgm_load_marker_data(marker_id, $parent.find(".spinner"));
    });

    // Marker Edit via InfoWindow
    $(document.body).on("click", ".wgm_marker_edit_iw", function (e) {
      e.preventDefault();
      var marker_id = $(this).data("markerid");
      wgm_load_marker_data(marker_id, null);
    });
  });
})(jQuery);
