(function ($) {
  "use strict";

  function initIconSearch() {
    const searchInput = document.getElementById("wgm-icon-search");
    const clearBtn = document.querySelector(".wgm-icon-search-clear");
    const iconList = document.getElementById("wgm-icon-list");
    const noResults = document.getElementById("wgm-no-results");

    if (!searchInput || !iconList) {
      return false;
    }

    const icons = iconList.querySelectorAll("li[data-icon-name]");

    if (icons.length === 0) {
      return false;
    }

    function filterIcons() {
      const searchTerm = searchInput.value.toLowerCase().trim();
      let visibleCount = 0;

      icons.forEach(function (icon) {
        const iconName = icon.getAttribute("data-icon-name") || "";
        const matches = iconName.includes(searchTerm);

        if (matches) {
          icon.classList.remove("wgm-icon-hidden");
          visibleCount++;
        } else {
          icon.classList.add("wgm-icon-hidden");
        }
      });

      // Show/hide no results message
      if (visibleCount === 0 && searchTerm !== "") {
        noResults.classList.add("wgm-show");
      } else {
        noResults.classList.remove("wgm-show");
      }

      // Show/hide clear button
      if (searchTerm !== "") {
        clearBtn.style.display = "block";
      } else {
        clearBtn.style.display = "none";
      }
    }

    function clearSearch() {
      searchInput.value = "";
      filterIcons();
      searchInput.focus();
    }

    // Event listeners
    searchInput.addEventListener("input", filterIcons);
    searchInput.addEventListener("keydown", function (e) {
      if (e.key === "Escape") {
        clearSearch();
      }
    });

    clearBtn.addEventListener("click", clearSearch);

    // Focus search input
    setTimeout(function () {
      searchInput.focus();
    }, 100);

    return true;
  }

  // Watch for ThickBox content to be loaded
  $(document).on("thickbox:iframe:loaded thickbox:removed", function () {
    // Try to initialize when ThickBox loads
    setTimeout(initIconSearch, 200);
  });

  // Use MutationObserver to detect when icon selector is loaded
  const observer = new MutationObserver(function (mutations) {
    for (let mutation of mutations) {
      if (mutation.addedNodes.length) {
        for (let node of mutation.addedNodes) {
          if (
            node.nodeType === 1 &&
            (node.id === "TB_ajaxContent" ||
              (node.querySelector && node.querySelector("#wgm-icon-search")))
          ) {
            setTimeout(initIconSearch, 200);
            return;
          }
        }
      }
    }
  });

  // Start observing when document is ready
  $(document).ready(function () {
    // Observe the body for ThickBox content
    observer.observe(document.body, {
      childList: true,
      subtree: true,
    });

    // Also try to initialize immediately in case content is already loaded
    initIconSearch();
  });
})(jQuery);
