/**
 * Admin Settings Logic
 * Handles export/import functionality, map search, and UI visibility toggles in the settings page.
 */
(function ($) {
  "use strict";

  $(function () {
    // --- Export Tab ---
    var $typeEl = $("#wgm_export_type");
    var $csvEl = $("#wgm_export_data_csv");
    var $jsonEl = $("#wgm_export_data_json");

    /**
     * Toggles visibility of export format options (CSV vs JSON).
     */
    function toggleExport() {
      var v = $typeEl.val() || "csv";
      if (v === "json") {
        $jsonEl.show();
        $csvEl.hide();
      } else {
        $jsonEl.hide();
        $csvEl.show();
      }
    }

    if ($typeEl.length) {
      $typeEl.on("change", toggleExport);
      toggleExport();
    }

    // --- Map Selection Visibility ---
    var $radiosMapSelection = $('input[name="wgm_map_selection"]');
    var $selectRow = $(".wgm_maps_select_row");

    /**
     * Toggles visibility of the map list selection row based on export mode.
     */
    function toggleMapRow() {
      var $checked = $('input[name="wgm_map_selection"]:checked');
      if (!$checked.length) return;
      if ($checked.val() === "selected") {
        $selectRow.show();
      } else {
        $selectRow.hide();
      }
    }

    $radiosMapSelection.on("change", toggleMapRow);
    toggleMapRow();

    /**
     * Toggles checked state for all map checkboxes in the list.
     * @param {boolean} checked - Whether to check or uncheck all items.
     */
    function setAll(checked) {
      $('.wgm-map-list input[type="checkbox"]').prop("checked", checked);
    }

    // Select All/None button handlers
    $("#wgm_select_all, #wgm_select_all_bottom").on("click", function () {
      setAll(true);
    });
    $("#wgm_select_none, #wgm_select_none_bottom").on("click", function () {
      setAll(false);
    });

    /**
     * Filters the list of maps based on search input.
     */
    $("#wgm_map_search").on("input", function () {
      var q = (this.value || "").toLowerCase();
      $(".wgm-map-item").each(function () {
        var $item = $(this);
        var label = $item.text().toLowerCase();
        if (label.indexOf(q) !== -1) {
          $item.show();
        } else {
          $item.hide();
        }
      });
    });

    // --- Import Tab ---
    var $importTypeEl = $("#wgm_import_type");
    var $fileEl = $("#wgm_import_file");
    var $dataTypeRow = $("#wgm_import_data_type_row");
    var $targetRadios = $('input[name="wgm_import_target"]');
    var $existingBox = $("#wgm_import_existing_maps");

    /**
     * Toggles visibility of the target selection box for imports.
     */
    function toggleImportTarget() {
      var $sel = $('input[name="wgm_import_target"]:checked');
      if (!$sel.length) return;
      if ($sel.val() === "existing") {
        $existingBox.show();
      } else {
        $existingBox.hide();
      }
    }

    $targetRadios.on("change", toggleImportTarget);
    toggleImportTarget();

    var $dataTypeEl = $("#wgm_import_data_type");
    var $targetRow = $("#wgm_import_target_row");
    var $uploadRow = $("#wgm_upload_row");
    var $sampleContainer = $("#wgm_sample_link_container");

    /**
     * Updates the dynamic sample file download link based on selected format and data type.
     */
    function updateSampleLink() {
      if (!$sampleContainer.length) return;
      var format = $importTypeEl.val();
      var dType = $dataTypeEl.val();
      var samples = (window.wgm_l && window.wgm_l.sample_files) || {};
      var key = "";

      if (format === "json") {
        key = "json";
      } else if (format === "csv") {
        if (dType === "markers") {
          key = "csv_markers";
        } else if (dType === "categories") {
          key = "csv_categories";
        }
      }

      if (key && samples[key]) {
        var s = samples[key];
        $sampleContainer
          .html('<a href="' + s.url + '" download>' + s.label + "</a>")
          .show();
      } else {
        $sampleContainer.hide().empty();
      }
    }

    /**
     * Updates visibility of the Target Map row based on chosen format/type.
     */
    function updateTargetVisibility() {
      var format = $importTypeEl.val();
      var dType = $dataTypeEl.val();

      updateSampleLink();

      // Default: hide target
      if (!format || (format === "csv" && !dType)) {
        $targetRow.hide();
        $uploadRow.hide();
        return;
      }

      $uploadRow.show();

      // If CSV and Categories, target map makes no sense
      if (format === "csv" && dType === "categories") {
        $targetRow.hide();
      } else {
        $targetRow.show();
      }
    }

    // Toggle file acceptance and data type row based on import format
    if ($importTypeEl.length) {
      $importTypeEl.on("change", function () {
        var v = $(this).val();
        if (!v) {
          $dataTypeRow.hide();
          $uploadRow.hide();
          $targetRow.hide();
        } else if (v === "json") {
          $fileEl.attr("accept", ".json,text/json");
          $dataTypeRow.hide();
          $uploadRow.show();
        } else {
          $fileEl.attr("accept", ".csv,text/csv");
          $dataTypeRow.show();
          // Keep uploadRow hidden until dType is selected if it's empty
          if (!$dataTypeEl.val()) {
            $uploadRow.hide();
          } else {
            $uploadRow.show();
          }
        }
        updateTargetVisibility();
      });

      $dataTypeEl.on("change", updateTargetVisibility);

      // Initial state
      updateTargetVisibility();
      if ($importTypeEl.val() === "csv") {
        $dataTypeRow.show();
      } else if ($importTypeEl.val() === "json") {
        $dataTypeRow.hide();
      } else {
        $dataTypeRow.hide();
        $uploadRow.hide();
        $targetRow.hide();
      }
    }

    /**
     * Unified alert helper with SweetAlert2 fallback.
     * @param {string} title - Alert title.
     * @param {string} text - Alert message.
     * @param {string} [type='info'] - Alert icon type.
     */
    function showAlert(title, text, type) {
      if (window.Swal && typeof window.Swal.fire === "function") {
        window.Swal.fire({ title: title, text: text, icon: type || "info" });
      } else {
        alert(title + "\n" + text);
      }
    }

    /**
     * Unified confirmation helper with SweetAlert2 fallback.
     * @param {string} title - Confirmation title.
     * @param {string} text - Detailed text.
     * @param {Function} callback - Success callback receiving a boolean result.
     */
    function showConfirm(title, text, callback) {
      if (window.Swal && typeof window.Swal.fire === "function") {
        var locales = (window.wgm_l && window.wgm_l.import_messages) || {};
        var confirmText = locales.confirm_button || "Continue";
        var cancelText = locales.cancel_button || "Cancel";

        window.Swal.fire({
          title: title,
          text: text,
          icon: "warning",
          showCancelButton: true,
          confirmButtonText: confirmText,
          cancelButtonText: cancelText,
        }).then(function (res) {
          callback(!!(res && res.isConfirmed));
        });
      } else {
        var ok = confirm(title + "\n" + text);
        callback(ok);
      }
    }

    /**
     * Converts raw bytes to a human-readable size string.
     * @param {number} bytes - Size in bytes.
     * @returns {string} Human readable size.
     */
    function bytesToSize(bytes) {
      var sizes = ["B", "KB", "MB", "GB", "TB"];
      if (bytes === 0) return "0 B";
      var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)), 10);
      return (
        Math.round((bytes / Math.pow(1024, i)) * 100) / 100 + " " + sizes[i]
      );
    }

    var $importForm = $('form[action*="wgm_import"]');
    var waitConfig = (window.wgm_l && window.wgm_l.settings_data) || {};
    var wgmMaxUpload = waitConfig.max_upload || 0;

    /**
     * Handles import form submission with file validation and confirmation.
     */
    if ($importForm.length) {
      $importForm.on("submit", function (e) {
        var $submitBtn = $importForm.find('button[type="submit"]');

        // Allow normal submission if validation already passed
        if ($importForm.data("validated")) return true;

        e.preventDefault();
        $submitBtn.prop("disabled", true);

        var locales = (window.wgm_l && window.wgm_l.import_messages) || {};
        var fileInput = document.getElementById("wgm_import_file");

        // Validate file selection
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
          var nfTitle = locales.no_file_title || "No file";
          var nfText =
            locales.no_file_text || "Please choose a file to import.";
          showAlert(nfTitle, nfText, "error");
          $submitBtn.prop("disabled", false);
          return false;
        }

        var file = fileInput.files[0];
        var type = $importTypeEl.val() || "json";
        var ext = (file.name || "").split(".").pop().toLowerCase();

        // Validate file extension
        if (
          (type === "json" && ext !== "json") ||
          (type === "csv" && ext !== "csv")
        ) {
          var ftTitle =
            locales.file_type_mismatch_title || "File type mismatch";
          var ftText =
            locales.file_type_mismatch_text || "Invalid file format.";
          showAlert(ftTitle, ftText, "error");
          $submitBtn.prop("disabled", false);
          return false;
        }

        // Validate file size
        if (wgmMaxUpload && file.size > wgmMaxUpload) {
          var lfTitle = locales.large_file_title || "Large file";
          var lfTpl =
            locales.large_file_text ||
            "File size {file} exceeds limit of {limit}.";
          var lfText = lfTpl
            .replace("{file}", bytesToSize(file.size))
            .replace("{limit}", bytesToSize(wgmMaxUpload));

          showConfirm(lfTitle, lfText, function (ok) {
            if (!ok) {
              $submitBtn.prop("disabled", false);
              return;
            }
            proceedConfirm();
          });
          return false;
        }

        proceedConfirm();

        /**
         * Checks for destructive actions and proceeds with submission.
         */
        function proceedConfirm() {
          var mode = $('input[name="wgm_import_mode"]:checked').val();
          var target = $('input[name="wgm_import_target"]:checked').val();
          var format = $importTypeEl.val();
          var dType = $dataTypeEl.val();
          var isDryRun = $('input[name="wgm_import_dry_run"]').is(":checked");

          if (!isDryRun && mode === "replace") {
            var dTitle = locales.destructive_title || "Destructive action";
            var dText =
              locales.destructive_text ||
              "Existing maps and markers will be deleted.";

            // Specialized warning for categories
            if (format === "csv" && dType === "categories") {
              dText =
                locales.category_replace_warning ||
                "Existing categories will be deleted. Maps and markers will NOT be affected.";
            }

            if (
              target === "new" ||
              (format === "csv" && dType === "categories")
            ) {
              showConfirm(dTitle, dText, function (ok) {
                if (ok) {
                  doSubmit();
                } else {
                  $submitBtn.prop("disabled", false);
                }
              });
              return;
            }
          }

          doSubmit();

          function doSubmit() {
            if (isDryRun) {
              // AJAX Submission for Dry Run
              var formData = new FormData($importForm[0]);
              formData.set("action", "wgm_import"); // Ensure action is correct for AJAX

              fetch(ajaxurl, {
                method: "POST",
                body: formData,
                credentials: "same-origin",
              })
                .then(function (r) {
                  if (!r.ok) throw new Error("Network response was not ok");
                  return r.json();
                })
                .then(function (json) {
                  $submitBtn.prop("disabled", false);
                  if (json.success) {
                    var title = locales.dryrun_title || "Dry Run Result";
                    var msg = "";
                    if (json.data.type === "json") {
                      msg = (
                        locales.dryrun_json_success ||
                        "Dry run completed successfully. {maps} maps, {markers} markers and {categories} categories would be imported."
                      )
                        .replace("{maps}", json.data.maps)
                        .replace("{markers}", json.data.markers)
                        .replace("{categories}", json.data.categories);
                    } else {
                      msg = (
                        locales.dryrun_success ||
                        "Dry run completed successfully. {count} items would be imported."
                      ).replace("{count}", json.data.count);
                    }
                    showAlert(title, msg, "success");
                  } else {
                    showAlert(
                      locales.preview_error_title || "Error",
                      (json.data && json.data.message) ||
                        locales.ajax_error ||
                        "Operation failed",
                      "error"
                    );
                  }
                })
                .catch(function (error) {
                  console.error("Dry run AJAX failed:", error);
                  $submitBtn.prop("disabled", false);
                  showAlert(
                    "Error",
                    locales.ajax_error || "Communication failed",
                    "error"
                  );
                });
            } else {
              // Normal Submission
              $importForm.data("validated", true).submit();
            }
          }
        }
      });
    }

    // --- Ajax Preview ---
    var $previewBox = $("#wgm_import_preview");
    var $mappingInput = $("#wgm_import_mapping");

    /**
     * Renders a preview table for CSV imports with column mapping controls.
     * @param {string[]} columns - List of CSV column names.
     * @param {Object[]} rows - Sample data rows.
     */
    function renderPreviewCsv(columns, rows, total_rows) {
      if (!$previewBox.length) return;
      var strLocales = (window.wgm_l && window.wgm_l.strings) || {};

      var html =
        '<div style="border:1px solid #eee;padding:12px;background:#fff;border-radius:4px;box-shadow: 0 2px 4px rgba(0,0,0,0.05);">';
      html +=
        '<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:12px; border-bottom:1px solid #f0f0f0; padding-bottom:8px;">';
      html +=
        '<strong style="font-size:14px;">' +
        (strLocales.csv_preview || "CSV Preview") +
        "</strong>";

      if (typeof total_rows !== "undefined") {
        var previewMsg =
          "Showing first " +
          rows.length +
          " rows out of " +
          total_rows +
          " total records.";
        html +=
          '<span style="font-size:12px; color:#666; background:#f5f5f5; padding:2px 8px; border-radius:12px;">' +
          previewMsg +
          "</span>";
      }
      html += "</div>";

      html +=
        '<div style="overflow-x:auto;border: 1px solid #f0f0f0;border-radius: 4px;margin-bottom: 20px;max-width:100%;box-sizing:border-box;display:grid;min-width:0;">';
      html +=
        '<table style="border-collapse:collapse;font-size:12px;background:#fff;table-layout:fixed;width:max-content;min-width:100%;"><tr>';

      for (var i = 0; i < columns.length; i++) {
        var col = columns[i].toLowerCase();
        var width = "120px"; // default

        if (col === "id" || col.indexOf("id") !== -1 || col === "parent_id") {
          width = "60px";
        } else if (
          col === "have_marker_link" ||
          col === "marker_link_new_tab" ||
          col === "show_desc_by_default"
        ) {
          width = "85px";
        } else if (col === "icon" || col === "animation") {
          width = "100px";
        } else if (col === "marker_desc" || col === "description") {
          width = "250px";
        } else if (
          col === "marker_name" ||
          col === "name" ||
          col === "address"
        ) {
          width = "180px";
        }

        html +=
          '<th style="border:1px solid #f0f0f0;padding:10px;background:#f9f9f9;text-align:left;font-weight:600;color:#555;width:' +
          width +
          ";min-width:" +
          width +
          ";max-width:" +
          width +
          ';">' +
          columns[i] +
          "</th>";
      }
      html += "</tr></thead>";
      html += "<tbody>";

      for (var r = 0; r < rows.length; r++) {
        html += "<tr>";
        for (var c = 0; c < columns.length; c++) {
          var col = columns[c].toLowerCase();
          var val =
            rows[r][columns[c]] !== undefined && rows[r][columns[c]] !== null
              ? rows[r][columns[c]]
              : "";

          var cellWidth = "120px"; // match default
          if (col === "id" || col.indexOf("id") !== -1 || col === "parent_id") {
            cellWidth = "60px";
          } else if (
            col === "have_marker_link" ||
            col === "marker_link_new_tab" ||
            col === "show_desc_by_default"
          ) {
            cellWidth = "85px";
          } else if (col === "icon" || col === "animation") {
            cellWidth = "100px";
          } else if (col === "marker_desc" || col === "description") {
            cellWidth = "250px";
          } else if (
            col === "marker_name" ||
            col === "name" ||
            col === "address"
          ) {
            cellWidth = "180px";
          }

          var tdStyle =
            "border:1px solid #f0f0f0;padding:10px;word-break:break-word;white-space:normal;vertical-align:top;line-height:1.4;width:" +
            cellWidth +
            ";min-width:" +
            cellWidth +
            ";max-width:" +
            cellWidth +
            ";";

          if (col === "id" || col.indexOf("id") !== -1 || col === "parent_id") {
            tdStyle += "text-align:center;";
          }

          html +=
            '<td style="' + tdStyle + '">' + (val + "").slice(0, 300) + "</td>";
        }
        html += "</tr>";
      }
      html += "</tbody></table></div>";

      // Mapping controls
      html +=
        '<div style="margin-top:20px;border-top:2px solid #f0f0f0;padding-top:16px;">';
      html +=
        '<strong style="display:block;font-size:14px;margin-bottom:4px;">' +
        (strLocales.column_mapping || "Column Mapping") +
        "</strong>";
      html +=
        '<p class="description" style="margin-bottom:16px;color:#666;">' +
        (strLocales.column_mapping_desc ||
          "Automated matching based on headers. Adjust manually if needed.") +
        "</p>";
      html +=
        '<div style="overflow-x:auto; padding-bottom:15px; margin-bottom:10px;">';
      html +=
        '<div style="display:flex; gap:15px; min-width:max-content; padding: 2px;">';

      var fieldOptions = [
        ["", "-- ignore --"],
        ["map_id", "map_id"],
        ["marker_name", "marker_name"],
        ["marker_desc", "marker_desc"],
        ["icon", "icon"],
        ["address", "address"],
        ["lat_lng", "lat_lng"],
        ["have_marker_link", "have_marker_link"],
        ["marker_link", "marker_link"],
        ["marker_link_new_tab", "marker_link_new_tab"],
        ["show_desc_by_default", "show_desc_by_default"],
        ["animation", "animation"],
        ["category_id", "category_id"],
        ["name", "name"],
        ["parent_id", "parent_id"],
        ["id", "id"],
        ["wpgmap_title", "wpgmap_title"],
        ["wpgmap_latlng", "wpgmap_latlng"],
        ["wpgmap_map_zoom", "wpgmap_map_zoom"],
        ["wpgmap_map_width", "wpgmap_map_width"],
        ["wpgmap_map_height", "wpgmap_map_height"],
      ];

      for (var i = 0; i < columns.length; i++) {
        var colName = columns[i].toLowerCase().replace(/[^a-z0-9]/g, "_");
        html +=
          '<div style="border:1px solid #ddd;padding:12px;background:#fcfcfc;border-radius:4px;display:flex;flex-direction:column;gap:8px;">';
        html +=
          '<div style="font-weight:600;font-size:13px;color:#333;overflow:hidden;text-overflow:ellipsis;" title="' +
          columns[i] +
          '">' +
          columns[i] +
          "</div>";
        html +=
          '<select data-col="' +
          columns[i] +
          '" class="wgm-map-select" style="width:100%;height:32px;border:1px solid #ccc;">';

        for (var fo = 0; fo < fieldOptions.length; fo++) {
          var fVal = fieldOptions[fo][0];
          var fLabel = fieldOptions[fo][1];
          // Try to auto-match
          var isSelected =
            colName === fLabel.toLowerCase() || colName === fVal.toLowerCase()
              ? " selected"
              : "";
          html +=
            '<option value="' +
            fVal +
            '"' +
            isSelected +
            ">" +
            fLabel +
            "</option>";
        }
        html += "</select>";
        html += "</div>";
      }
      html += "</div></div></div></div>";
      $previewBox.html(html);

      $previewBox.find(".wgm-map-select").on("change", updateMappingFromUI);
      // Run initial mapping update for auto-matched fields
      updateMappingFromUI();
    }

    /**
     * Updates the hidden JSON input for column mapping based on current UI selections.
     */
    function updateMappingFromUI() {
      var map = {};
      $previewBox.find(".wgm-map-select").each(function () {
        var col = $(this).data("col");
        var val = $(this).val() || "";
        if (val) map[col] = val;
      });
      $mappingInput.val(JSON.stringify(map));
    }

    // Handles file selection and AJAX preview for imports
    if ($fileEl.length) {
      $fileEl.on("change", function () {
        var f = this.files && this.files[0];
        if (!f) return;

        var reader = new FileReader();

        reader.onerror = function () {
          showAlert("Read Error", "Could not read the selected file.", "error");
        };

        reader.onload = function (ev) {
          var content = ev.target.result || "";
          var chunk = content.substr(0, 200000); // Read first 200KB for preview
          var previewType = $importTypeEl.val() || "json";
          var nonceVal = $('input[name="_wgm_import_nonce"]').val() || "";

          var fd = new FormData();
          fd.append("action", "wgm_import_preview");
          fd.append("preview_type", previewType);
          fd.append("content", chunk);
          fd.append("max_rows", 6);
          fd.append("_wgm_import_nonce", nonceVal);

          fetch(ajaxurl, {
            method: "POST",
            body: fd,
            credentials: "same-origin",
          })
            .then(function (r) {
              if (!r.ok) throw new Error("Network response was not ok");
              return r.json();
            })
            .then(function (json) {
              if (!json) return;
              if (json.success) {
                if (json.data.type === "csv") {
                  renderPreviewCsv(
                    json.data.columns,
                    json.data.rows,
                    json.data.total_rows
                  );
                } else if (json.data.type === "json") {
                  var strLocales = (window.wgm_l && window.wgm_l.strings) || {};
                  var out =
                    '<div style="border:1px solid #eee;padding:12px;background:#fff;border-radius:4px;max-width:100%;box-sizing:border-box;display:grid;min-width:0;">';
                  out +=
                    "<strong>" +
                    (strLocales.json_preview_label || "JSON Preview") +
                    "</strong>";
                  out += '<div style="margin-top:12px;color:#555;">';
                  out +=
                    "<div>Maps detected: <strong>" +
                    (json.data.maps ? json.data.maps.length : 0) +
                    "</strong></div>";
                  out +=
                    "<div>Markers detected: <strong>" +
                    (json.data.markers ? json.data.markers.length : 0) +
                    "</strong></div>";
                  out += "</div></div>";
                  $previewBox.html(out);
                }
              } else {
                var locales =
                  (window.wgm_l && window.wgm_l.import_messages) || {};
                var peTitle = locales.preview_error_title || "Preview error";
                var peText =
                  (json.data && json.data.message) ||
                  locales.preview_error_text ||
                  "Preview failed";
                showAlert(peTitle, peText, "error");
              }
            })
            .catch(function (error) {
              console.error("Import preview fetch failed:", error);
              showAlert(
                "Communication Error",
                "Failed to get preview from server.",
                "error"
              );
            });
        };
        reader.readAsText(f);
      });
    }
  });
})(jQuery);
