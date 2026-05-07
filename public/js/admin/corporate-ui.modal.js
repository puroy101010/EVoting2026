const corporateModal = {
  loader: `<div class="corporate-fb-loading" id="corporateRightModalLoading">
        <div class="corporate-fb-loading-header">
            <div class="corporate-fb-loading-avatar"></div>
            <div class="corporate-fb-loading-lines">
                <div class="corporate-fb-loading-line short"></div>
                <div class="corporate-fb-loading-line medium"></div>
            </div>
        </div>
        <div class="corporate-fb-loading-content">
            <div class="corporate-fb-loading-line long"></div>
            <div class="corporate-fb-loading-line full"></div>
            <div class="corporate-fb-loading-line medium"></div>
            <div class="corporate-fb-loading-line long"></div>
        </div>
        <div class="corporate-fb-loading-table">
            <div class="corporate-fb-loading-table-row">
                <div class="corporate-fb-loading-table-cell short"></div>
                <div class="corporate-fb-loading-table-cell medium"></div>
                <div class="corporate-fb-loading-table-cell long"></div>
            </div>
            <div class="corporate-fb-loading-table-row">
                <div class="corporate-fb-loading-table-cell medium"></div>
                <div class="corporate-fb-loading-table-cell short"></div>
                <div class="corporate-fb-loading-table-cell full"></div>
            </div>
            <div class="corporate-fb-loading-table-row">
                <div class="corporate-fb-loading-table-cell long"></div>
                <div class="corporate-fb-loading-table-cell full"></div>
                <div class="corporate-fb-loading-table-cell short"></div>
            </div>
        </div>
    </div>`,

  init(options) {
    this.title = options.title || "Default Title";
    this.subtitle = options.subtitle || "Default Subtitle";
    this.icon = options.icon || "info-circle";
    this.showLoader = options.showLoader || false;
    this.loader = options.loader || this.loader;
    this.showFooter = options.showFooter || true;
    this.size = options.size || "md";
    this.backdrop = options.backdrop || true;
    this.keyboard = options.keyboard || true;
    this.showCloseButton = options.showCloseButton || true;
    this.closeButtonAction = options.closeButtonAction || (() => { });
    this.footer = options.footer || {};
    this.content = options.content || (() => { });
  },

  setHtmlContent(content) {
    $("#corporateRightModalContent").html(content);
  },

  hideLoading() {
    $("#corporateRightModalLoading").hide();
  },

  showLoading() {
    this.setHtmlContent(this.loader);
  },

  setup() {
    this.showLoading();

    // Apply size class to the modal
    const modal = $("#corporateRightSlideModal");
    modal.removeClass('modal-sm modal-md modal-lg modal-xl');
    modal.addClass(`modal-${this.size}`);

    $("#corporateRightSlideModal").modal({
      backdrop: this.backdrop,
      keyboard: this.keyboard
    });
    if (this.showFooter) {
      $("#corporateRightSlideModalFooter").show();
    } else {
      $("#corporateRightSlideModalFooter").hide();
    }
    $('#corporateRightSlideModalTitle').text(this.title);
    $('#corporateRightSlideModalSubtitle').text(this.subtitle);
    this.generateFooterButtons(this.footer.buttons || []);
    this.bindEvents();
  },

  bindEvents() {
    $(document).on("click", "#closeModalFooterButton", () => {
      $("#corporateRightSlideModal").modal("hide");
    });
  },

  generateFooterButtons(buttons = []) {
    const modalFooter = document.getElementById("corporateRightSlideModalFooter");
    modalFooter.innerHTML = "";
    buttons.forEach((button) => {
      const btn = document.createElement("button");
      btn.id = button.id || "#";
      btn.className = button.class || "";
      btn.innerHTML = button.label || "Button";
      const liElem = document.createElement("li");
      liElem.className = button.icon || "";
      btn.insertBefore(liElem, btn.firstChild);
      modalFooter.appendChild(btn);
      btn.addEventListener("click", () => {
        if (typeof button.action === "function") {
          button.action();
        }
      });
    });
  },

  setFooterHtml(footerHtml) {
    console.log("Footer HTML: ", footerHtml);
    $("#corporateRightSlideModalFooter").html(footerHtml);
  },

  open() {
    this.setup();
    if (typeof this.content === "function") {
      this.content();
    } else {
      this.setHtmlContent(this.content);
    }
  },

  close() {
    $("#corporateRightSlideModal").modal("hide");
  }
};
