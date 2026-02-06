/**
 * Admin Map Editor Logic
 * Handles the marker listing style carousel and map initialization in the admin area.
 */
(function ($) {
  "use strict";

  $(function () {
    var $carousel = $("#wpgmap_marker_listing_style");

    // Initialize Marker Listing Style Carousel if it exists
    if ($carousel.length) {
      var $track = $carousel.find(".wgm-style-track");
      var $cards = $track.find(".wgm-style-card");
      var $prev = $carousel.find(".wgm-carousel-prev");
      var $next = $carousel.find(".wgm-carousel-next");

      // Card gap as defined in CSS: 10px
      var gap = 10;

      /**
       * Calculates carousel dimensions and visibility metrics.
       * @returns {Object} Carousel dimensions and visible card count.
       */
      var sizes = function () {
        var viewportW = $carousel.find(".wgm-style-viewport").innerWidth();
        var $firstCard = $cards.first();
        var cardW = $firstCard.length ? $firstCard.outerWidth(true) : 0;

        var visible = 1;
        if (cardW > 0) {
          visible = Math.max(1, Math.floor((viewportW + gap) / (cardW + gap)));
        }

        return {
          viewportW: viewportW,
          cardW: cardW,
          visible: visible,
          total: $cards.length,
        };
      };

      var state = {
        index: 0,
      };

      /**
       * Updates the enabled/disabled state of carousel navigation buttons.
       */
      var updateButtons = function () {
        var s = sizes();
        var maxIndex = Math.max(0, s.total - s.visible);
        $prev.prop("disabled", state.index <= 0);
        $next.prop("disabled", state.index >= maxIndex);
      };

      /**
       * Slides the carousel track to the specified card index.
       * @param {number} index - The target card index to slide to.
       */
      var slideTo = function (index) {
        var s = sizes();
        index = Math.max(0, Math.min(index, Math.max(0, s.total - s.visible)));
        var translateX = index * (s.cardW + gap);
        $track.css("transform", "translateX(-" + translateX + "px)");
        state.index = index;
        updateButtons();
      };

      // Initialization: Start at the first item
      slideTo(0);

      // Previous button click handler
      $prev.on("click", function () {
        slideTo(state.index - 1);
      });

      // Next button click handler
      $next.on("click", function () {
        slideTo(state.index + 1);
      });

      /**
       * Handles click and keyboard interactions on style cards.
       * Sets the corresponding radio button and updates ARIA states.
       */
      $carousel.find(".wgm-style-card").on("click keypress", function (e) {
        if (e.type === "keypress" && e.which !== 13 && e.which !== 32) return;

        var $card = $(this);

        // Skip if the card represents a premium feature not available
        if ($card.hasClass("wgm_enable_premium")) {
          return;
        }

        // Update radio buttons within the same container
        var $container = $card.closest("#wpgmap_marker_listing_style");
        $container.find("input[type=radio]").prop("checked", false);
        $card.find("input[type=radio]").prop("checked", true).trigger("change");

        // Update ARIA states for accessibility
        $container.find(".wgm-style-card").attr("aria-checked", "false");
        $card.attr("aria-checked", "true");

        // Ensure the selected card is fully visible in the viewport
        var s = sizes();
        var cardIndex = $card.index();
        var minVisible = state.index;
        var maxVisible = state.index + s.visible - 1;

        if (cardIndex < minVisible) {
          slideTo(cardIndex);
        } else if (cardIndex > maxVisible) {
          slideTo(cardIndex - s.visible + 1);
        }
      });

      // Recalculate and adjust carousel position on window resize
      $(window).on("resize", function () {
        var $selected = $carousel.find('.wgm-style-card[aria-checked="true"]');
        var s = sizes();
        var selIndex = $selected.length ? $selected.index() : 0;
        var maxPossibleIndex = Math.max(0, s.total - s.visible);
        var newIndex = Math.max(0, Math.min(selIndex, maxPossibleIndex));
        slideTo(newIndex);
      });
    }

    /**
     * Initializes the Google Map for the admin editor.
     * Uses localized data for initial coordinates, type, and zoom.
     */
    var wgm_init_admin_map = function () {
      try {
        if (
          typeof google === "undefined" ||
          typeof google.maps === "undefined"
        ) {
          return;
        }

        if (typeof window.wgm_initAutocomplete !== "function") {
          console.warn("wgm_initAutocomplete is not defined.");
          return;
        }

        // Extract configuration from localized data with defaults
        var editData = (window.wgm_l && window.wgm_l.edit_data) || {};
        var lat = editData.center_lat || 0;
        var lng = editData.center_lng || 0;
        var type = editData.map_type || "ROADMAP";
        var zoom = editData.map_zoom || 10;

        window.wgm_initAutocomplete(
          "wgm_map",
          "wgm_pac_input",
          lat,
          lng,
          type,
          zoom,
          "edit"
        );

        // Automatically open info window if setting is enabled
        if (
          $("#wpgmap_show_infowindow").is(":checked") &&
          typeof window.wgm_openInfoWindow === "function"
        ) {
          window.wgm_openInfoWindow();
        }
      } catch (error) {
        console.error("Failed to initialize admin map:", error);
      }
    };

    // Initialize map immediately if Google Maps is already loaded, otherwise queue it
    if (typeof google === "object" && typeof google.maps === "object") {
      wgm_init_admin_map();
    } else {
      window.wgm_map_queue = window.wgm_map_queue || [];
      window.wgm_map_queue.push(wgm_init_admin_map);
    }
  });
})(jQuery);
