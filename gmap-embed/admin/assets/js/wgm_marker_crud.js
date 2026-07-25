(function ($) {
  $(function () {
    function generateMarkersListView() {
      $("#wgm_gmap_marker_list").DataTable().ajax.reload();
    }

    /**
     * Vanish a marker from Map view
     *
     * @param type string
     */
    function wgm_vanish_marker(type = "new") {
      var state = window.wgm_admin_map;
      if (!state) return;

      if (type === "new") {
        if (state.new_marker && typeof state.new_marker.setMap === "function") {
          state.new_marker.setMap(null);
        }
        state.new_marker = null;
      } else {
        if (
          state.existing_marker &&
          typeof state.existing_marker.setMap === "function"
        ) {
          state.existing_marker.setMap(null);
        }
        state.existing_marker = null;
      }
    }

    /* --------------------Marker add form reset---------------------- */
    function wpgmap_reset_marker_add_form() {
      $(
        "#wpgmap_marker_name,#wpgmap_marker_address,#wpgmap_marker_lat_lng,#wpgmap_marker_link,#wpgmap_marker_animation"
      ).val("");
      $("#wpgmap_marker_category").val([]).trigger("change");
      $("#wpgmap_marker_link_new_tab").prop("checked", false);
      $("#wpgmap_have_marker_link").val("0").change();
      $(document.body).find("#marker_errors,#marker_success").html("");
      $(document.body)
        .find("#wpgmap_marker_icon_preview")
        .attr(
          "src",
          "https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png"
        );
      $(document.body).find("#wpgmap_marker_image").val("");
      $(document.body)
        .find("#wpgmap_marker_image_preview")
        .attr("src", "")
        .hide();
      $(document.body).find("#wpgmap_remove_marker_image").hide();

      $(document.body)
        .find("#wpgmap_marker_icon")
        .val(
          "https://maps.gstatic.com/mapfiles/api-3/images/spotlight-poi2.png"
        );
      // Reset wp editor content
      tmce_setContent("", "wpgmap_marker_desc", "wpgmap_marker_desc");
    }

    // Pre-Loading markers list
    // generateMarkersListView();
    // Marker update or save
    $(document.body).on(
      "click",
      ".wpgmap_marker_add,.wpgmap_marker_update",
      function () {
        var is_update = false,
          action = "wpgmapembed_save_map_markers",
          save_update_message = "Marker saved successfully.",
          marker_id = 0,
          map_id = 0;
        is_update = $(this).hasClass("wpgmap_marker_update");
        if (is_update) {
          action = "wpgmapembed_update_map_markers";
          save_update_message = "Marker updated successfully.";
          marker_id = $(this).attr("markerid");
        }

        map_id = parseInt($(this).attr("mapid"));

        $("#marker_errors,#marker_success").html("");
        $(this).parent().find(".spinner").css("visibility", "visible");
        var parent = $("body .wgm_gmap_markers");
        var wpgmap_marker_link_new_tab = 0;

        // Handling checkboxes
        if (
          parent.find("#wpgmap_marker_link_new_tab").is(":checked") === true
        ) {
          wpgmap_marker_link_new_tab = 1;
        }

        var wpgmap_marker_name = parent.find("#wpgmap_marker_name").val();
        var wpgmap_marker_desc = tmce_getContent(
          "wpgmap_marker_desc",
          "wpgmap_marker_desc"
        );
        var wpgmap_marker_icon = parent.find("#wpgmap_marker_icon").val();
        var wpgmap_marker_image = parent.find("#wpgmap_marker_image").val();
        var wpgmap_marker_lat_lng = parent.find("#wpgmap_marker_lat_lng").val();
        var wpgmap_marker_address = parent.find("#wpgmap_marker_address").val();
        var wpgmap_marker_animation = parent
          .find("#wpgmap_marker_animation")
          .val();
        var wpgmap_marker_category = parent
          .find("#wpgmap_marker_category")
          .val();
        var wpgmap_have_marker_link = parent
          .find("#wpgmap_have_marker_link")
          .val();
        var wpgmap_marker_link = parent.find("#wpgmap_marker_link").val();
        var wpgmap_marker_infowindow_show = parent
          .find("#wpgmap_marker_infowindow_show")
          .val();

        // Handling front-end validation
        var has_error = false;
        var error_msg = [];
        parent.find("#wpgmap_marker_name").removeClass("wgm_error");
        if (wpgmap_marker_lat_lng === "") {
          parent.find("#wpgmap_marker_lat_lng").addClass("wgm_error");
          has_error = true;
          error_msg.push("Please input latitude, longitude correctly.");
        }

        // Escape error messages for XSS protection
        if (has_error) {
          var safeErrorMsg = error_msg.map(function (msg) {
            return $("<div>").text(msg).html();
          });
          $("#marker_errors").html(safeErrorMsg.join("<br/>"));
          parent.find(".spinner").css("visibility", "hidden");
          return false;
        }
        var map_markers_data = {
          wpgmap_marker_name: wpgmap_marker_name,
          wpgmap_marker_desc: wpgmap_marker_desc,
          wpgmap_marker_icon: wpgmap_marker_icon,
          wpgmap_marker_image: wpgmap_marker_image,
          wpgmap_marker_lat_lng: wpgmap_marker_lat_lng,
          wpgmap_marker_address: wpgmap_marker_address,
          wpgmap_marker_animation: wpgmap_marker_animation,
          wpgmap_marker_category: wpgmap_marker_category,
          wpgmap_have_marker_link: wpgmap_have_marker_link,
          wpgmap_marker_link: wpgmap_marker_link,
          wpgmap_marker_link_new_tab: wpgmap_marker_link_new_tab,
          wpgmap_marker_infowindow_show: wpgmap_marker_infowindow_show,
          wpgmap_marker_id: marker_id,
          wpgmap_map_id: map_id,
        };

        var nonce = is_update
          ? wgm_l.nonces.wpgmapembed_update_map_markers
          : wgm_l.nonces.wpgmapembed_save_map_markers;

        var ajaxData = {
          action: action,
          map_markers_data: map_markers_data,
          _wpnonce: nonce,
        };

        $.post(ajaxurl, ajaxData, function (response) {
          try {
            if (typeof response === "string") response = JSON.parse(response);
          } catch (e) {
            console.error("WGM: Failed to parse marker response", e);
            $(this).parent().find(".spinner").css("visibility", "hidden");
            return;
          }

          var state = window.wgm_admin_map;
          if (response.responseCode === 0) {
            Swal.fire({
              icon: "info",
              title: wgm_l.locales.sweet_alert.oops,
              html:
                wgm_l.locales.sweet_alert.notice_unlimited_marker +
                '<br><br><span style="font-size:25px;font-weight:bold;">Starting from $19 only</span><br><br><a target="_blank" href="' +
                wgm_l.get_p_v_url +
                '">Upgrade to Pro Version (Starting from $19 only)</a>',
            });
            parent.find(".spinner").css("visibility", "hidden");
            return false;
          }

          if (!is_update && state) {
            state.current_markers[parseInt(response.marker_id, 10)] =
              state.new_marker;
            state.current_infowindows[parseInt(response.marker_id, 10)] =
              state.new_marker_infowindow;
          }

          if (state) {
            state.new_marker = null;
            state.new_marker_infowindow = null;
            state.existing_marker_infowindow = null;
            state.is_marker_edit = false;

            if (!is_update) {
              state.no_of_markers++;
              if (state.no_of_markers >= 1 && wgm_l.is_premium_user !== "1") {
                $(".add_new_marker_btn_area .add_new_marker").css({
                  opacity: 0.5,
                });
                $(".add_new_marker_btn_area .wgm-pro-label").show();
              }
            }

            if (state.current_markers[response.marker_id]) {
              state.current_markers[response.marker_id].setDraggable(false);
            }
          }

          wpgmap_reset_marker_add_form();
          generateMarkersListView();
          parent.find(".spinner").css("visibility", "hidden");
          $("#marker_success").html(save_update_message);
          $(".wgm_marker_cancel").trigger("click");
        }).fail(function (xhr, status, error) {
          parent.find(".spinner").css("visibility", "hidden");
          alert("Failed to save marker: " + error);
        });
      }
    );

    $(document.body).on("click", ".add_new_marker", function () {
      var state = window.wgm_admin_map;
      if (state && state.no_of_markers >= 1 && wgm_l.is_premium_user !== "1") {
        Swal.fire({
          icon: "info",
          title: wgm_l.locales.sweet_alert.oops,
          html:
            wgm_l.locales.sweet_alert.notice_unlimited_marker +
            '<br><br><span style="font-size:25px;font-weight:bold;">Starting from $19 only</span><br><br><a target="_blank" href="' +
            wgm_l.get_p_v_url +
            '">Upgrade to Pro Version (Starting from $19 only)</a>',
        });
        return false;
      }
      $(document.body)
        .find(".add_new_marker_form")
        .addClass("wgm_active")
        .show();
      $(".wpgmap_marker_update")
        .removeClass("wpgmap_marker_update")
        .addClass("wpgmap_marker_add")
        .css("background-color", "#2271b1")
        .html(
          '<i class="dashicons dashicons-location" style="line-height: 1.6;"></i><b>Save Marker</b>'
        );
      $(document.body).find(".wgm_gmap_marker_list").hide();
      $(document.body).find("#marker_errors,#marker_success").html("");
      wpgmap_reset_marker_add_form();
    });

    $(document.body).on("click", ".wgm_marker_cancel", function () {
      $(document.body)
        .find(".add_new_marker_form")
        .removeClass("wgm_active")
        .hide();
      $(document.body).find(".wgm_gmap_marker_list").show();
      wpgmap_reset_marker_add_form();
      wgm_vanish_marker("new");
      if (window.wgm_admin_map) window.wgm_admin_map.is_marker_edit = false;
    });

    $(document.body)
      .find("#wpgmap_have_marker_link")
      .on("change", function () {
        if ($(document.body).find("#wpgmap_have_marker_link").val() === "1") {
          $("#wpgmap_marker_link_area").show();
        } else {
          $("#wpgmap_marker_link_area").hide();
        }
      });

    // Marker Image Remove Logic
    $(document.body).on("click", "#wpgmap_remove_marker_image", function (e) {
      e.preventDefault();
      $("#wpgmap_marker_image").val("");
      $("#wpgmap_marker_image_preview").attr("src", "").hide();
      $(this).hide();
    });

    /**
     * Clone Marker
     * Confirms and duplicates a marker via AJAX, then refreshes the marker
     * list and re-draws the pins on the map canvas.
     *
     * @since 1.9.7
     */
    $(document.body).on("click", ".wpgmap_marker_clone", function (e) {
      e.preventDefault();
      var $btn = $(this);
      var marker_id = $btn.attr("map_marker_id");
      if (!marker_id) return;

      Swal.fire({
        title: "Are you sure?",
        text: "This will create a duplicate of this marker.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, clone it!",
      }).then((result) => {
        if (!result.isConfirmed) return;

        $btn.prop("disabled", true).find("i").addClass("fa-spin");

        $.post(ajaxurl, {
          action: "wpgmapembed_clone_marker",
          marker_id: marker_id,
          _wpnonce: wgm_l.nonces.wpgmapembed_clone_marker,
        })
          .done(function (response) {
            try {
              if (typeof response === "string") {
                response = JSON.parse(response);
              }

              if (response.success) {
                Swal.fire("Cloned!", response.data.message, "success");
                generateMarkersListView();
                if (typeof window.loadMarkersOnMap === "function") {
                  window.loadMarkersOnMap();
                }
              } else {
                Swal.fire("Error", response.data.message, "error");
              }
            } catch (err) {
              console.error("WGM: Error parsing marker clone response", err);
              Swal.fire({
                icon: "error",
                title: "Error",
                text: "Invalid server response.",
              });
            }
          })
          .fail(function (xhr, status, error) {
            Swal.fire({
              icon: "error",
              title: "Network Error",
              text: "Failed to clone marker: " + error,
            });
          })
          .always(function () {
            $btn.prop("disabled", false).find("i").removeClass("fa-spin");
          });
      });
    });
  });
})(jQuery);
