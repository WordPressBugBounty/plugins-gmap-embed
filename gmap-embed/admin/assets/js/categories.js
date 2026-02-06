(function ($) {
  $(document).ready(function () {
    "use strict";

    /**
     * Initialize DataTable for Categories
     */
    var table = $("#wgm-categories-table").DataTable({
      responsive: true,
      processing: true,
      ajax: {
        url: ajaxurl,
        data: {
          action: "wgm_get_categories",
          _wpnonce: wgm_l.nonces.wgm_get_categories,
        },
      },
      columns: [
        { data: "id" },
        { data: "icon" },
        { data: "name" },
        { data: "parent" },
        { data: "action" },
      ],
      order: [[0, "desc"]],
    });

    /**
     * Handle Category Form Submission
     * Adds or updates a category via AJAX.
     */
    $("#wgm-category-form").on("submit", function (e) {
      e.preventDefault();
      var $spinner = $("#wgm-category-spinner");
      var $submit = $("#wgm-category-submit");

      $spinner.addClass("is-active");
      $submit.prop("disabled", true);

      var categoryId = parseInt($("#wgm-category-id").val(), 10) || 0;
      var action = categoryId > 0 ? "wgm_update_category" : "wgm_save_category";
      var nonce =
        categoryId > 0
          ? wgm_l.nonces.wgm_update_category
          : wgm_l.nonces.wgm_save_category;

      var categoryData = {
        id: categoryId,
        name: $("#wgm-category-name").val(),
        icon: $("#wgm-category-icon-url").val(),
        parent_id: $("#wgm-category-parent").val(),
      };

      $.post(ajaxurl, {
        action: action,
        category_data: categoryData,
        _wpnonce: nonce,
      })
        .done(function (response) {
          $spinner.removeClass("is-active");
          $submit.prop("disabled", false);

          try {
            if (typeof response === "string") {
              response = JSON.parse(response);
            }

            if (response.success) {
              Swal.fire({
                icon: "success",
                title: "Success!",
                text: response.data.message,
                timer: 2000,
                showConfirmButton: false,
              });
              resetForm();
              table.ajax.reload();

              // Dynamic Dropdown Update
              if (categoryId === 0) {
                // Add new category to parent dropdown
                if (response.data.id && categoryData.name) {
                  var newOption = new Option(
                    categoryData.name,
                    response.data.id
                  );
                  $("#wgm-category-parent").append(newOption);
                }
              } else {
                // Update existing category name in parent dropdown
                $(
                  "#wgm-category-parent option[value='" + categoryId + "']"
                ).text(categoryData.name);
              }
            } else {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: response.data.message || "Something went wrong",
              });
            }
          } catch (err) {
            console.error("WGM: Error parsing category save response", err);
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Invalid server response.",
            });
          }
        })
        .fail(function (xhr, status, error) {
          $spinner.removeClass("is-active");
          $submit.prop("disabled", false);
          Swal.fire({
            icon: "error",
            title: "Network Error",
            text: "An error occurred while processing your request: " + error,
          });
        });
    });

    /**
     * Handle Edit Category Click
     * Fetches category data and populates the form for editing.
     */
    $(document.body).on("click", ".wgm-edit-category", function () {
      var id = $(this).data("id");
      var $spinner = $("#wgm-category-spinner");
      $spinner.addClass("is-active");

      $.post(ajaxurl, {
        action: "wgm_get_category_data",
        id: id,
        _wpnonce: wgm_l.nonces.wgm_get_category_data,
      })
        .done(function (response) {
          $spinner.removeClass("is-active");

          try {
            if (typeof response === "string") {
              response = JSON.parse(response);
            }

            if (response.success) {
              var data = response.data;
              $("#wgm-category-id").val(data.id);
              $("#wgm-category-name").val(data.name);
              $("#wgm-category-parent").val(data.parent_id);
              $("#wgm-category-icon-url").val(data.icon);

              if (data.icon) {
                $("#wgm-category-icon-preview").attr("src", data.icon);
                $("#wgm-category-icon-remove").show();
              } else {
                $("#wgm-category-icon-preview").attr(
                  "src",
                  wgm_l.plugin_url + "admin/assets/images/markers/default.png"
                );
                $("#wgm-category-icon-remove").hide();
              }

              $("#wgm-category-form-title").text("Edit Category: " + data.name);
              $("#wgm-category-submit").text("Update Category");
              $("#wgm-category-cancel").show();

              // Disable itself in parent dropdown to prevent circular reference
              $("#wgm-category-parent option").prop("disabled", false);
              $("#wgm-category-parent option[value='" + data.id + "']").prop(
                "disabled",
                true
              );

              $("html, body").animate(
                { scrollTop: $("#wgm-category-form").offset().top - 100 },
                500
              );
            } else {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: response.data.message || "Could not fetch category data",
              });
            }
          } catch (err) {
            console.error("WGM: Error parsing category data response", err);
            Swal.fire({
              icon: "error",
              title: "Error",
              text: "Invalid server response.",
            });
          }
        })
        .fail(function (xhr, status, error) {
          $spinner.removeClass("is-active");
          Swal.fire({
            icon: "error",
            title: "Network Error",
            text: "Failed to fetch category data: " + error,
          });
        });
    });

    /**
     * Handle Delete Category Click
     * Confirms and deletes a category via AJAX.
     */
    $(document.body).on("click", ".wgm-delete-category", function () {
      var id = $(this).data("id");

      Swal.fire({
        title: "Are you sure?",
        text: "You won't be able to revert this!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#d33",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
      }).then((result) => {
        if (result.isConfirmed) {
          $.post(ajaxurl, {
            action: "wgm_delete_category",
            id: id,
            _wpnonce: wgm_l.nonces.wgm_delete_category,
          })
            .done(function (response) {
              try {
                if (typeof response === "string") {
                  response = JSON.parse(response);
                }

                if (response.success) {
                  Swal.fire("Deleted!", response.data.message, "success");
                  table.ajax.reload();

                  // Remove deleted category from parent dropdown
                  $("#wgm-category-parent option[value='" + id + "']").remove();
                } else {
                  Swal.fire("Error", response.data.message, "error");
                }
              } catch (err) {
                console.error(
                  "WGM: Error parsing category delete response",
                  err
                );
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
                text: "Failed to delete category: " + error,
              });
            });
        }
      });
    });

    /**
     * Handle Cancel Button Click
     * Resets the form to its initial state.
     */
    $("#wgm-category-cancel").on("click", function () {
      resetForm();
    });

    /**
     * Reset Form
     * Clears all fields and resets UI elements.
     */
    function resetForm() {
      $("#wgm-category-id").val(0);
      $("#wgm-category-name").val("");
      $("#wgm-category-parent").val(0);
      $("#wgm-category-parent option").prop("disabled", false);
      $("#wgm-category-icon-url").val("");
      $("#wgm-category-icon-preview").attr(
        "src",
        wgm_l.plugin_url + "admin/assets/images/markers/default.png"
      );
      $("#wgm-category-icon-remove").hide();
      $("#wgm-category-form-title").text("Add New Category");
      $("#wgm-category-submit").text("Save Category");
      $("#wgm-category-cancel").hide();
    }

    /**
     * Media Uploader for Category Icon
     * Handles opening the WP Media Library to select an icon.
     */
    var mediaUploader;
    $("#wgm-category-icon-upload").on("click", function (e) {
      e.preventDefault();
      if (mediaUploader) {
        mediaUploader.open();
        return;
      }
      mediaUploader = wp.media.frames.file_frame = wp.media({
        title: "Choose Category Icon",
        button: { text: "Choose Icon" },
        multiple: false,
      });
      mediaUploader.on("select", function () {
        var attachment = mediaUploader
          .state()
          .get("selection")
          .first()
          .toJSON();
        $("#wgm-category-icon-url").val(attachment.url);
        $("#wgm-category-icon-preview").attr("src", attachment.url);
        $("#wgm-category-icon-remove").show();
      });
      mediaUploader.open();
    });

    /**
     * Remove Category Icon
     * Clears the selected icon.
     */
    $("#wgm-category-icon-remove").on("click", function () {
      $("#wgm-category-icon-url").val("");
      $("#wgm-category-icon-preview").attr(
        "src",
        wgm_l.plugin_url + "admin/assets/images/markers/default.png"
      );
      $(this).hide();
    });
  });
})(jQuery);
