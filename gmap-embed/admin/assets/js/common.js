(function ($) {
  $(document).ready(function () {
    "use strict";

    /** Constraints */

    /** Common Functions */
    function isGuttenbergActive() {
      return document.body.classList.contains("block-editor-page");
    }

    /** Classic Editor Google Map Select Popup box */
    if (!isGuttenbergActive()) {
      // To load Maps List
      var loadSrmGmapsList = function () {
        $("#wgm_all_maps").find(".spinner").addClass("is-active");
        $("#wpgmapembed_list").html("");
        var data = {
          action: "wpgmapembed_popup_load_map_data",
          _wpnonce: wgm_l.nonces.wpgmapembed_popup_load_map_data,
        };
        jQuery
          .post(ajaxurl, data, function (response) {
            $("#wgm_all_maps").find(".spinner").removeClass("is-active");
            $("#wpgmapembed_list").html(response);
          })
          .fail(function (xhr, status, error) {
            $("#wgm_all_maps").find(".spinner").removeClass("is-active");
            console.error("WGM: Failed to load maps list", error);
          });
      };

      // Loading Map/List on window load
      $(window).on("load", loadSrmGmapsList);

      // Removing Popup Box
      var removeSrmGmapPopup = function () {
        if (typeof self.parent !== "undefined" && self.parent.tb_remove) {
          self.parent.tb_remove();
        }
      };

      // Inserting ShortCode From List on click insert button
      $(document.body).on("click", ".wpgmap-insert-shortcode", function () {
        var shortcode = $(this)
          .parent()
          .parent()
          .find(".wpgmap-shortcode")
          .val();
        if (!tinyMCE.activeEditor || tinyMCE.activeEditor.isHidden()) {
          $("textarea#content").val(shortcode);
        } else {
          tinyMCE.execCommand("mceInsertContent", false, shortcode);
        }
        removeSrmGmapPopup();
      });

      // On Click fire removing Popup Box(removeSrmGmapPopup)
      $(document.body).on("click", ".wp_gmap_close_btn", removeSrmGmapPopup);

      // On Escape fire removing Popup Box(removeSrmGmapPopup)
      $(document).keyup(function (e) {
        if (e.which === 27) {
          removeSrmGmapPopup();
        }
      });
    }

    /** Map related functions/event listeners */

    // ******* Remove Single Map *******
    $(document.body).on("click", ".wgm_wpgmap_delete", function () {
      if (!confirm("Are you sure to Delete")) {
        return false;
      }
      $("#wp-gmap-nav").find(".spinner").addClass("is-active");
      var btn_class = $(this);
      btn_class.prop("disabled", true);
      var post_id = parseInt($(this).data("id"), 10);

      // Validate post_id is numeric
      if (isNaN(post_id) || post_id <= 0) {
        alert("Invalid map ID.");
        $("#wp-gmap-nav").find(".spinner").removeClass("is-active");
        btn_class.prop("disabled", false);
        return false;
      }
      var data = {
        action: "wpgmapembed_remove_wpgmap",
        post_id: post_id,
        _wpnonce: wgm_l.nonces.wpgmapembed_remove_wpgmap,
      };

      jQuery
        .post(ajaxurl, data, function (response) {
          $("#wp-gmap-nav").find(".spinner").removeClass("is-active");
          btn_class.prop("disabled", false);

          try {
            if (typeof response === "string") {
              response = JSON.parse(response);
            }
            if (response.responseCode === 1) {
              window.location.reload();
            } else {
              alert(
                response.message ||
                  "Something went wrong, could not delete, please try again."
              );
            }
          } catch (e) {
            console.error("WGM: Error parsing delete response", e);
            alert("Unexpected server response.");
          }
        })
        .fail(function (xhr, status, error) {
          $("#wp-gmap-nav").find(".spinner").removeClass("is-active");
          btn_class.prop("disabled", false);
          alert("Network error: " + error);
        });
    });

    // ******* Handle Placement Button Clicks *******
    $(document.body).on("click", ".wgm-placement-btn", function () {
      var $btn = $(this);
      var value = $btn.data("value");

      // Update active state in UI
      $btn.siblings().removeClass("active");
      $btn.addClass("active");

      // Update hidden input value
      $("#wpgmap_marker_listing_placement").val(value);
    });

    /**
     * Get Checkbox Value Helper
     */
    function getCheckboxValue($parent, selector) {
      return $parent.find(selector).is(":checked") ? 1 : 0;
    }

    // ************** Save, Update and Insert Button
    $(document.body).on(
      "click",
      "#wp-gmap-embed-save,#wp-gmap-embed-update",
      function () {
        var $btn = $(this);
        var $parentMsg = $("body .wpgmap_msg_error");
        var $spinner = $btn.parent().find(".spinner");

        $parentMsg.html("");
        $btn.prop("disabled", true);
        $spinner.addClass("is-active");

        var btn_id = $btn.attr("id");
        var $container =
          btn_id === "wp-gmap-embed-save"
            ? $("#wp-gmap-new")
            : $("#wp-gmap-edit");

        if (!$container.length) {
          $btn.prop("disabled", false);
          $spinner.removeClass("is-active");
          return;
        }

        var map_data = {
          wpgmap_title: $container.find("#wpgmap_title").val(),
          wpgmap_heading_class: $container.find("#wpgmap_heading_class").val(),
          wpgmap_show_heading: getCheckboxValue(
            $container,
            "#wpgmap_show_heading"
          ),
          wpgmap_latlng: $container.find("#wpgmap_latlng").val(),
          wpgmap_map_zoom: $container.find("#wpgmap_map_zoom").val(),
          wpgmap_disable_zoom_scroll: getCheckboxValue(
            $container,
            "#wpgmap_disable_zoom_scroll"
          ),
          wpgmap_map_width: $container.find("#wpgmap_map_width").val(),
          wpgmap_map_height: $container.find("#wpgmap_map_height").val(),
          wpgmap_map_type: $container.find("#wpgmap_map_type").val(),
          wpgmap_show_infowindow: getCheckboxValue(
            $container,
            "#wpgmap_show_infowindow"
          ),
          wpgmap_enable_direction: getCheckboxValue(
            $container,
            "#wpgmap_enable_direction"
          ),
          wpgmap_enable_modern_direction: getCheckboxValue(
            $container,
            "#wpgmap_enable_modern_direction"
          ),
          wpgmap_direction_drawer_width: $container
            .find("#wpgmap_direction_drawer_width")
            .val(),
          wpgmap_marker_listing_width: $container
            .find("#wpgmap_marker_listing_width")
            .val(),
          wpgmap_center_lat_lng: $container
            .find("#wpgmap_center_lat_lng")
            .val(),
          wgm_theme_json: $container.find("#wgm_theme_json").val(),
          wpgmap_marker_listing_style: $container
            .find("#wpgmap_marker_listing_style input[type='radio']:checked")
            .val(),
          wpgmap_marker_listing_placement: $container
            .find("#wpgmap_marker_listing_placement")
            .val(),

          // Store Locator
          wgm_enable_store_locator: getCheckboxValue(
            $container,
            "#wgm_enable_store_locator"
          ),
          wgm_enable_title_search: getCheckboxValue(
            $container,
            "#wgm_enable_title_search"
          ),
          wgm_enable_category_filter: getCheckboxValue(
            $container,
            "#wgm_enable_category_filter"
          ),
          wgm_title_search_placeholder: $container
            .find("#wgm_title_search_placeholder")
            .val(),
          wgm_address_search_placeholder: $container
            .find("#wgm_address_search_placeholder")
            .val(),
          wgm_default_address: $container.find("#wgm_default_address").val(),
          wgm_default_radius: $container.find("#wgm_default_radius").val(),
          wgm_not_found_message: $container
            .find("#wgm_not_found_message")
            .val(),
          wgm_hide_markers_until_search: getCheckboxValue(
            $container,
            "#wgm_hide_markers_until_search"
          ),
          wgm_show_center_icon: getCheckboxValue(
            $container,
            "#wgm_show_center_icon"
          ),
          wgm_show_distance: getCheckboxValue($container, "#wgm_show_distance"),
          wgm_distance_unit: $container.find("#wgm_distance_unit").val(),
          wgm_store_locator_placement: $container
            .find("#wgm_store_locator_placement")
            .val(),
          wgm_sort_by_distance: getCheckboxValue(
            $container,
            "#wgm_sort_by_distance"
          ),
          wgm_enable_show_on_map_icon: getCheckboxValue(
            $container,
            "#wgm_enable_show_on_map_icon"
          ),
          wgm_enable_get_direction_icon: getCheckboxValue(
            $container,
            "#wgm_enable_get_direction_icon"
          ),
          wgm_enable_direction_link: getCheckboxValue(
            $container,
            "#wgm_enable_direction_link"
          ),

          // Circle Settings
          wgm_radius_circle_stroke_color: $container
            .find("#wgm_radius_circle_stroke_color")
            .val(),
          wgm_radius_circle_stroke_opacity: $container
            .find("#wgm_radius_circle_stroke_opacity")
            .val(),
          wgm_radius_circle_stroke_weight: $container
            .find("#wgm_radius_circle_stroke_weight")
            .val(),
          wgm_radius_circle_fill_color: $container
            .find("#wgm_radius_circle_fill_color")
            .val(),
          wgm_radius_circle_fill_opacity: $container
            .find("#wgm_radius_circle_fill_opacity")
            .val(),

          // Map Controls
          wpgmap_zoom_control: $container.find("#wpgmap_zoom_control").val(),
          wpgmap_zoom_control_pos: $container
            .find("#wpgmap_zoom_control_pos")
            .val(),
          wpgmap_map_type_control: $container
            .find("#wpgmap_map_type_control")
            .val(),
          wpgmap_map_type_control_pos: $container
            .find("#wpgmap_map_type_control_pos")
            .val(),
          wpgmap_street_view_control: $container
            .find("#wpgmap_street_view_control")
            .val(),
          wpgmap_street_view_control_pos: $container
            .find("#wpgmap_street_view_control_pos")
            .val(),
          wpgmap_fullscreen_control: $container
            .find("#wpgmap_fullscreen_control")
            .val(),
          wpgmap_fullscreen_control_pos: $container
            .find("#wpgmap_fullscreen_control_pos")
            .val(),
          wpgmap_rotate_control: $container
            .find("#wpgmap_rotate_control")
            .val(),
          wpgmap_rotate_control_pos: $container
            .find("#wpgmap_rotate_control_pos")
            .val(),
          wpgmap_scale_control: $container.find("#wpgmap_scale_control").val(),
          wpgmap_scale_control_pos: $container
            .find("#wpgmap_scale_control_pos")
            .val(),
          marker_orderby_field: $container
            .find("#wpgmap_marker_orderby_field")
            .val(),
          marker_orderby_dir: $container
            .find("#wpgmap_marker_orderby_dir")
            .val(),
        };

        // Validation
        if (!map_data.wpgmap_latlng || map_data.wpgmap_latlng.trim() === "") {
          $parentMsg.html(
            '<div class="error notice notice-error is-dismissible"><p>Please input Latitude and Longitude</p></div>'
          );
          $btn.prop("disabled", false);
          $spinner.removeClass("is-active");
          return false;
        }

        if (btn_id === "wp-gmap-embed-save") {
          map_data.action_type = "save";
        } else {
          map_data.action_type = "update";
          map_data.post_id = $container.find("#wpgmap_map_id").val();
        }

        var ajaxParams = {
          action: "wpgmapembed_save_map_data",
          map_data: map_data,
          _wpnonce: wgm_l.nonces.wpgmapembed_save_map_data,
        };

        jQuery
          .post(ajaxurl, ajaxParams, function (response) {
            $spinner.removeClass("is-active");
            $btn.prop("disabled", false);

            try {
              if (typeof response === "string") {
                response = JSON.parse(response);
              }

              if (response.responseCode === 0) {
                $parentMsg.html(
                  '<div class="error notice notice-error is-dismissible"><p>' +
                    $("<div>").text(response.message).html() +
                    "</p></div>"
                );
              } else {
                if (btn_id === "wp-gmap-embed-save") {
                  window.location.href =
                    "?page=wpgmapembed&tag=edit&id=" +
                    encodeURIComponent(response.post_id) +
                    "&message=1&wgm_map_create_nonce=" +
                    encodeURIComponent(wgm_l.wgm_map_create_nonce);
                } else {
                  $parentMsg.html(
                    '<div class="success notice notice-success is-dismissible"><p>' +
                      $("<div>").text(response.message).html() +
                      "</p></div>"
                  );
                }
              }
            } catch (e) {
              console.error("WGM: Error parsing save response", e);
              $parentMsg.html(
                '<div class="error notice notice-error is-dismissible"><p>Unexpected server response.</p></div>'
              );
            }
          })
          .fail(function (xhr, status, error) {
            $spinner.removeClass("is-active");
            $btn.prop("disabled", false);
            $parentMsg.html(
              '<div class="error notice notice-error is-dismissible"><p>Network error: ' +
                error +
                "</p></div>"
            );
          });
      }
    );

    /**
     * To view premium notice
     *
     * @since 1.7.5
     */
    $(document.body)
      .find(".wgm_enable_premium")
      .on("click", function () {
        var wgm_notice_text = $(this).attr("data-notice");
        Swal.fire({
          icon: "info",
          showCloseButton: true,
          title: wgm_l.locales.sweet_alert.oops,
          html:
            wgm_notice_text +
            '<br><br><span style="font-size:25px;font-weight:bold;">Starting from $19 only</span><br><br><a target="_blank" href="' +
            wgm_l.get_p_v_url +
            '">Upgrade to Pro</a>',
          confirmButtonText: "Close",
        });
        return false;
      });

    /**
     * Map Controls Customization Toggle
     */
    $(document.body).on("click", "#wgm-map-controls-toggle", function () {
      var container = $("#wgm-map-controls-container");
      var header = $(this);

      header.toggleClass("active");
      container.slideToggle(300);
    });

    /**
     * Settings tab active/inactive rendering
     */
    $(document.body).on("click", ".wgm-settings-menu li", function (e) {
      e.preventDefault();
      var $this = $(this);
      $this.siblings().removeClass("active");
      $this.addClass("active");
      var wgm_tab_id = $this.attr("data-tab");
      $(".wgm_settings_tabs").hide();
      $("#" + wgm_tab_id).show();
    });

    /**
     * Toggle Direction Drawer Width Row visibility based on Modern Direction Checkbox
     */
    $(document.body).on(
      "change",
      "#wpgmap_enable_modern_direction",
      function () {
        if ($(this).is(":checked")) {
          $("#wgm_direction_drawer_width_row").show();
        } else {
          $("#wgm_direction_drawer_width_row").hide();
        }
      }
    );

    /**
     * Popup window for create Google Map API key
     *
     * @since 1.9.0
     */
    $(document.body)
      .find(".wgm_api_create_gplatform")
      .on("click", function (e) {
        e.preventDefault();
        const wgmDimensions = {
          width: 570,
          height: 700,
        };

        var wgmPopupLeft = (screen.width - wgmDimensions.width) / 2;
        var wgmPopupTop = (screen.height - wgmDimensions.height) / 2;

        window.open(
          "https://console.cloud.google.com/google/maps-hosted",
          "WP Google Map - Create your API key",
          "resizable=yes,width=" +
            wgmDimensions.width +
            ",height=" +
            wgmDimensions.height +
            ",left=" +
            wgmPopupLeft +
            ",top=" +
            wgmPopupTop
        );
        return false;
      });
  });
})(jQuery);
