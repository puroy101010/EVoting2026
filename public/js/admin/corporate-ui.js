// Corporate UI - Dynamic Context Menu System
// ------------------------------------------
// This file contains the dynamic context menu system for the admin UI.
// All context menu logic is now modular and reusable.

(function ($) {
  // Context Menu Manager
  const ContextMenuManager = {
    /**
     * Programmatically show a context menu with custom actions and data.
     * @param {Object} options - { actions: Array, data: Object, trigger: HTMLElement|jQuery, position: {left,top} }
     */
    show(options) {
      const $menu = $(`#${this.menuId}`);
      this.hideMenu();
      let $trigger = null;
      if (options.trigger) {
        $trigger = $(options.trigger);
        this.currentTrigger = $trigger[0];
      } else {
        // Fallback: create a virtual trigger at position
        $trigger = $(
          '<div style="position:fixed;left:0;top:0;width:1px;height:1px;"></div>'
        );
        $("body").append($trigger);
        this.currentTrigger = $trigger[0];
      }
      // Attach data to trigger for menu item click handlers
      $trigger.data("context-menu-data", options.data || {});
      // Expose recordId globally if present
      if (options.data && options.data.recordId !== undefined) {
        this.recordId = options.data.recordId;
      } else {
        this.recordId = undefined;
      }
      this.activeMenu = $menu;
      this.buildMenuItems($menu, options.actions, $trigger, options.data);
      this.positionAndShowMenu($trigger, $menu, options.position);
    },
    activeMenu: null,
    currentTrigger: null,
    menuId: "dynamic-context-menu",

    init() {
      console.log("Initializing Dynamic Context Menu System...");
      this.ensureMenuExists();
      this.bindEvents();
      this.createClickOutsideHandler();
      console.log("Context Menu System initialized successfully");
    },
    ensureMenuExists() {
      if (!$(`#${this.menuId}`).length) {
        $("body").append(
          `<div id="${this.menuId}" class="corporate-modern-context-menu" style="display: none;">
            <ul class="corporate-context-menu-list"></ul>
          </div>`
        );
      }
    },    bindEvents() {
      // Modern context menu items handle their own clicks through action functions
      // No global click handler needed - actions are bound directly when menu is built
      $(document).on("click", ".corporate-modern-context-menu", (e) => {
        e.stopPropagation();
      });
    },

    createClickOutsideHandler() {
      $(document).on("click", (e) => {
        if (
          this.activeMenu &&
          !$(e.target).closest(
            ".corporate-modern-context-menu, [data-context-menu]"
          ).length
        ) {
          this.hideMenu();
        }
      });
      $(document).on("keydown", (e) => {
        if (e.key === "Escape" && this.activeMenu) {
          this.hideMenu();
          if (this.currentTrigger) {
            $(this.currentTrigger).focus();
          }
        }
      });
      $(document).on("keydown", ".corporate-context-menu-item", (e) => {
        if (!this.activeMenu) return;
        const $items = this.activeMenu.find(
          ".corporate-context-menu-item:not(.disabled)"
        );
        const currentIndex = $items.index(e.target);
        switch (e.key) {
          case "ArrowDown":
            e.preventDefault();
            const nextIndex =
              currentIndex < $items.length - 1 ? currentIndex + 1 : 0;
            $items.eq(nextIndex).focus();
            break;
          case "ArrowUp":
            e.preventDefault();
            const prevIndex =
              currentIndex > 0 ? currentIndex - 1 : $items.length - 1;
            $items.eq(prevIndex).focus();
            break;
          case "Enter":
          case " ":
            e.preventDefault();
            if (!$(e.target).hasClass("disabled")) {
              $(e.target).click();
            }
            break;
        }
      });
    },
    buildMenuItems($menu, menuConfig, $trigger, extraData) {
      $menu.empty();
      // Use trigger data or extraData for itemId/itemName
      const itemId =
        (extraData && extraData.recordId) || $trigger.data("item-id");
      const itemName =
        (extraData && extraData.recordName) || $trigger.data("item-name");

      // Store context data globally like Infinitek does
      this.contextData = {
        recordId: itemId,
        recordName: itemName,
        ...(extraData || {}),
      };

      // Create the list container if it doesn't exist
      if (!$menu.find(".corporate-context-menu-list").length) {
        $menu.append('<ul class="corporate-context-menu-list"></ul>');
      }
      const $list = $menu.find(".corporate-context-menu-list");
      $list.empty();

      // Process items like Infinitek does
      menuConfig.forEach((item) => {
        this.createMenuItem(item, this.contextData);
      });
    },
    createMenuItem(item, data) {
      // Handle separators like Infinitek
      if (item === "divider" || item === "separator" || item.separator) {
        this.createSeparator();
        return;
      }

      // Validate item like Infinitek does
      if (typeof item !== "object" || !item.label) {
        console.warn("ContextMenu: Invalid item format", item);
        return;
      }

      const $menu = $(`#${this.menuId}`);
      const $list = $menu.find(".corporate-context-menu-list");
      const li = $("<li>");
      const button = $("<button>", {
        type: "button",
        class: this.buildItemClassName(item, data),
        html: this.buildItemContent(item),
        tabindex: this.isItemDisabled(item, data) ? -1 : 0,
        title: item.tooltip || "",
      });

      // Add click handler if not disabled (like Infinitek)
      if (!this.isItemDisabled(item, data)) {
        button.on("click", () => {
          if (typeof item.action === "function") {
            item.action(data);
          }
          this.hideMenu();
        });
      }

      li.append(button);
      $list.append(li);
    },

    // Create separator element like Infinitek
    createSeparator() {
      const $menu = $(`#${this.menuId}`);
      const $list = $menu.find(".corporate-context-menu-list");
      const li = $("<li>", {
        class: "corporate-context-menu-separator",
      });
      $list.append(li);
    },

    // Build item class name like Infinitek
    buildItemClassName(item, data) {
      let className = "corporate-context-menu-item";

      if (item.class || item.className) {
        className += " " + (item.class || item.className);
      }

      if (item.danger) {
        className += " danger";
      }

      if (this.isItemDisabled(item, data)) {
        className += " disabled";
      }

      return className;
    },

    // Build item content HTML like Infinitek
    buildItemContent(item) {
      const iconHtml = item.icon
        ? `<i class="${item.icon} corporate-menu-icon"></i>`
        : "";

      let content = iconHtml + item.label;

      // Add badge if present
      if (item.badge) {
        const badgeClass = `corporate-context-menu-badge ${
          item.badge.class || ""
        }`;
        content += `<span class="${badgeClass}">${item.badge.text}</span>`;
      }

      return content;
    },

    // Check if item is disabled like Infinitek
    isItemDisabled(item, data) {
      if (typeof item.disabled === "function") {
        return item.disabled(data);
      }
      return Boolean(item.disabled);
    },

    positionAndShowMenu($trigger, $menu, customPosition) {
      const triggerRect = $trigger[0].getBoundingClientRect();
      const menuWidth = 220;
      $menu.css({
        position: "fixed",
        left: "-9999px",
        top: "-9999px",
        display: "block",
      });
      const menuHeight = $menu[0].offsetHeight || 200;
      let left, top;
      if (
        customPosition &&
        typeof customPosition.left === "number" &&
        typeof customPosition.top === "number"
      ) {
        left = customPosition.left;
        top = customPosition.top;
      } else {
        left = triggerRect.left;
        top = triggerRect.bottom + 5;
        if (left + menuWidth > window.innerWidth) {
          left = triggerRect.right - menuWidth;
          $menu.addClass("left");
        } else {
          $menu.removeClass("left");
        }
        if (top + menuHeight > window.innerHeight) {
          top = triggerRect.top - menuHeight - 5;
          $menu.addClass("above");
        } else {
          $menu.removeClass("above");
        }
      }
      $menu.css({
        position: "fixed",
        left: `${left}px`,
        top: `${top}px`,
        zIndex: 10001,
        display: "none",
      });
      $menu.fadeIn(150, () => {
        $menu.addClass("show");
        setTimeout(() => {
          const $firstEnabled = $menu.find(
            ".corporate-context-menu-item:not(.disabled):first"
          );
          if ($firstEnabled.length) {
            $firstEnabled.focus();
          }
        }, 50);
      });
    },

    hideMenu() {
      if (this.activeMenu) {
        this.activeMenu.removeClass("show above left").fadeOut(100, () => {
          //   if ($.contains(document, this.activeMenu[0])) {
          //     this.activeMenu.empty();
          //   }
        });
        this.activeMenu = null;
        this.currentTrigger = null;
      }    },
    
    // Get context data like Infinitek does
    getContextData() {
      return this.contextData || {};
    },

    // Get record ID like Infinitek does
    getRecordId() {
      return this.contextData ? this.contextData.recordId : null;
    },

    // Additional helper to get record name
    getRecordName() {
      return this.contextData ? this.contextData.recordName : null;
    },
  };

  // Expose globally for manual re-init if needed
  window.CtxMenu = ContextMenuManager;
  $(function () {
    ContextMenuManager.init();
  });
})(jQuery);
