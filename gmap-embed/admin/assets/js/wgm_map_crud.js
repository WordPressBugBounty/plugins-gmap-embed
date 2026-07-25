(function ($) {
  $(document).ready(function () {
    /**
     * Datatable to view map list
     *
     * @since 1.7.5
     */
    var wgm_map_list = $("#wgm_map_list_dt").DataTable({
      ajax: {
        url:
          ajaxurl +
          "?action=wgm_get_all_maps&_wpnonce=" +
          wgm_l.nonces.wgm_get_all_maps,
        error: function (xhr, error, code) {
          console.error("WGM: Failed to load map list", error, code);
        },
      },
      columns: [
        { data: "id" },
        { data: "title" },
        { data: "map_type" },
        { data: "width" },
        { data: "height" },
        { data: "shortcode" },
        { data: "action" },
      ],
      language: {
        emptyTable:
          "<b style='color: #d36d8c'>" +
          wgm_l.locales.dt.no_map_created +
          "</b>",
      },
      responsive: true,
    });

    /**
     * Datatable to view marker list
     *
     * @since 1.7.5
     */
    var wgm_map_id =
      typeof wgm_l.wgm_object === "undefined" ? 0 : wgm_l.wgm_object.map_id;
    var wgm_gmap_marker_list = $("#wgm_gmap_marker_list").DataTable({
      ajax: {
        url:
          ajaxurl +
          "?action=wgm_get_markers_by_map_id&map_id=" +
          wgm_map_id +
          "&_wpnonce=" +
          wgm_l.nonces.wgm_get_markers_by_map_id,
        error: function (xhr, error, code) {
          console.error("WGM: Failed to load marker list", error, code);
        },
      },
      columns: [
        { data: "id" },
        { data: "marker_name" },
        { data: "icon" },
        { data: "action" },
      ],
      language: {
        emptyTable:
          "<b style='color: #d36d8c'>" +
          wgm_l.locales.dt.no_marker_created +
          "</b>",
      },
      responsive: true,
    });

    /**
     * Clone Map
     * Confirms and duplicates a map (with its markers) via AJAX.
     *
     * @since 1.9.7
     */
    $(document.body).on("click", ".wpgmap-clone", function () {
      var $btn = $(this);
      var map_id = $btn.data("id");
      if (!map_id) return;

      Swal.fire({
        title: "Are you sure?",
        text: "This will create a duplicate of this map along with its markers.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, clone it!",
      }).then((result) => {
        if (!result.isConfirmed) return;

        $btn.prop("disabled", true).find("i").addClass("fa-spin");

        $.post(ajaxurl, {
          action: "wpgmapembed_clone_map",
          map_id: map_id,
          _wpnonce: wgm_l.nonces.wpgmapembed_clone_map,
        })
          .done(function (response) {
            try {
              if (typeof response === "string") {
                response = JSON.parse(response);
              }

              if (response.success) {
                Swal.fire("Cloned!", response.data.message, "success");
                wgm_map_list.ajax.reload();
              } else {
                Swal.fire("Error", response.data.message, "error");
              }
            } catch (err) {
              console.error("WGM: Error parsing map clone response", err);
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
              text: "Failed to clone map: " + error,
            });
          })
          .always(function () {
            $btn.prop("disabled", false).find("i").removeClass("fa-spin");
          });
      });
    });

    $(document.body).on("click", ".wpgmap-copy-to-clipboard", function () {
      var copyText = $(this).parent().parent().find(".wpgmap-shortcode");
      // Defensive: Ensure element exists and is input
      if (copyText.length && copyText[0].select) {
        copyText[0].select();
        // Escape value before copying to clipboard (for JS security, though shortcodes are safe)
        var safeVal = $("<div>").text(copyText.val()).html();
        if (navigator.clipboard && navigator.clipboard.writeText) {
          navigator.clipboard.writeText(copyText.val());
          $("#copy_to_clipboard_toaster").fadeIn(100).fadeOut(2000);
        } else {
          // Fallback for browsers that do not support navigator.clipboard
          copyText[0].select();
          document.execCommand("copy");
          $("#copy_to_clipboard_toaster").fadeIn(100).fadeOut(2000);
        }
      }
    });
  });
})(jQuery);
