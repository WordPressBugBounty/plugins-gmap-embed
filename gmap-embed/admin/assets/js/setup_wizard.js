(function ($) {
  "use strict";

  /**
   * Setup wizard tabs
   */
  function wpgmapRenderTab(wgm_step = 0) {
    var wgm_contents = document.getElementsByClassName("wgm_setup_content"),
      wgm_prev = document.getElementById("wgm_prev"),
      wgm_nextElement = document.getElementById("wgm_next"),
      wgm_saveElement = document.getElementById("wgm_save");

    if (wgm_contents.length < 1) {
      return;
    }

    wgm_contents[wgm_step].style.display = "block";
    wgm_prev.style.display = wgm_step === 0 ? "none" : "inline";
    wgm_nextElement.style.display = "inline";
    wgm_saveElement.style.display = "none";
    if (wgm_step === wgm_contents.length - 1) {
      wgm_saveElement.style.display = "inline";
      wgm_nextElement.style.display = "none";
    }
    var wgm_container = document.getElementsByClassName(
      "wgm_setup_wizard_steps"
    );
    wgm_container[0].setAttribute("data-step", wgm_step);
  }

  wpgmapRenderTab(0);

  /**
   * Generating preview of final step
   *
   * @param api_key
   * @param language_full_name
   * @param regional_area_full_name
   */
  function wgp_generateFinalResponse(
    api_key,
    language_full_name,
    regional_area_full_name
  ) {
    // Escape output to prevent XSS
    $(".wgm_apikey").html("<p>" + $("<div>").text(api_key).html() + "</p>");
    $(".wgm_language").html(
      "<p>" + $("<div>").text(language_full_name).html() + "</p>"
    );
    $(".wgm_region").html(
      "<p>" + $("<div>").text(regional_area_full_name).html() + "</p>"
    );
  }

  /**
   * Next | Previous button click actions
   */
  $(document).on("click", "#wgm_next,#wgm_skip,#wgm_prev", function (e) {
    var wgm_container = document.getElementsByClassName(
        "wgm_setup_wizard_steps"
      ),
      wgm_StepNumber = parseInt(wgm_container[0].getAttribute("data-step")),
      wgm_contents = document.getElementsByClassName("wgm_setup_content");
    var wgm_api_key = $("#wgm_key").val(),
      wgm_language = $("#wgm_gmap_lng").val(),
      wgm_language_full_name = $("#wgm_gmap_lng option:selected").text(),
      wgm_regional_area = $("#wgm_region").val(),
      wgm_regional_area_full_name = $("#wgm_region option:selected")
        .text()
        .toUpperCase();

    if (e.target.id === "wgm_next" || e.target.id === "wgm_skip") {
      if (wgm_StepNumber == 0) {
        // Getting API key stage
        if (wgm_api_key === "") {
          Swal.fire({
            icon: "warning",
            title: wgm_l.setup_wizard.setup_validation_msg.apikey,
            html:
              "<a href='//www.youtube.com/watch?v=1G2VksP-uX0' target='_blank' style='text-decoration: none;'><i class='dashicons dashicons-youtube'></i> See video tutorial</a>" +
              "&nbsp;&nbsp;&nbsp;<a href='https://wpgooglemap.com/documentation/wp-google-map-quick-installation?utm_source=gmap-embed&utm_medium=wordpress-plugin&utm_campaign=documentation&utm_content=setup-wizard-quick-install-link' target='_blank' style='text-decoration: none;' ><i class='fas fa-external-link-alt'></i> See help manual</a>",
          });
          return false;
        }
      } else if (wgm_StepNumber == 1) {
        // Regional area stage
        if (wgm_language === "") {
          Swal.fire({
            title: wgm_l.setup_wizard.setup_validation_msg.language,
            icon: "warning",
          });
          return false;
        }
        if (wgm_regional_area === "") {
          Swal.fire({
            title: wgm_l.setup_wizard.setup_validation_msg.regionalarea,
            icon: "warning",
          });
          return false;
        }
      }
      wgp_generateFinalResponse(
        wgm_api_key,
        wgm_language_full_name,
        wgm_regional_area_full_name
      );
    }
    wgm_contents[wgm_StepNumber].style.display = "none";
    wgm_StepNumber =
      e.target.id === "wgm_prev" ? wgm_StepNumber - 1 : wgm_StepNumber + 1;
    if (wgm_StepNumber >= wgm_contents.length) {
      return false;
    }
    wpgmapRenderTab(wgm_StepNumber);
  });

  /**
   * Save setup wizard information
   */
  $(document).on("click", "#wgm_save", function (e) {
    Swal.showLoading();
    var wgm_api_key = document.getElementById("wgm_key").value,
      wgm_language = document.getElementById("wgm_gmap_lng").value,
      wgm_regional_area = document.getElementById("wgm_region").value;
    var data = {
      action: "wpgmapembed_save_setup_wizard",
      wgm_api_key: wgm_api_key,
      wgm_language: wgm_language,
      wgm_regional_area: wgm_regional_area,
      _wpnonce: wgm_l.nonces.wpgmapembed_save_setup_wizard,
    };
    $.post(ajaxurl, data, function (response) {
      try {
        if (typeof response === "string") response = JSON.parse(response);
      } catch (err) {
        Swal.hideLoading();
        console.error("WGM: Invalid setup response", err);
        Swal.fire({
          icon: "error",
          title: "Unexpected server response",
          text: "Please try again or contact support.",
        });
        return;
      }

      Swal.hideLoading();
      if (response.responseCode === 101) {
        Swal.fire({
          icon: "error",
          title: wgm_l.setup_wizard.setup_validation_msg.apikey,
        });
        return false;
      }
      if (response.responseCode === 102) {
        Swal.fire({
          icon: "error",
          title: wgm_l.setup_wizard.setup_validation_msg.language,
        });
        return false;
      }
      if (response.responseCode === 103) {
        Swal.fire({
          icon: "error",
          title: wgm_l.setup_wizard.setup_validation_msg.regionalarea,
        });
        return false;
      }
      if (response.responseCode === 200) {
        Swal.fire({
          icon: "success",
          title: wgm_l.setup_wizard.setup_validation_msg.success,
          confirmButtonText: "Let's Get Started",
          showCloseButton: true,
        }).then((result) => {
          window.location.replace(
            wgm_l.site_url.concat("/wp-admin/admin.php?page=wpgmapembed")
          );
        });
      } else {
        Swal.fire({
          icon: "error",
          title: wgm_l.setup_wizard.setup_validation_msg.failed_to_finish,
          text: response.message || "",
        });
        return false;
      }
    }).fail(function (xhr, status, error) {
      Swal.hideLoading();
      Swal.fire({
        icon: "error",
        title: "Connection Error",
        text: "Failed to communicate with the server: " + error,
      });
    });
  });
  /**
   * What we collect modal
   */
  $(document).on("click", "#wgm_wwc_notice", function (e) {
    e.preventDefault();
    Swal.fire({
      icon: "info",
      title: wgm_l.setup_wizard.wgm_wwc_msg.title,
      text: wgm_l.setup_wizard.wgm_wwc_msg.desc,
    });
  });
})(jQuery);
