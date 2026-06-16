// ============================================================================
// STOCKHOLDER MANAGEMENT - MEMBERS.JS
// Optimized procedural architecture with functional grouping
// ============================================================================

// ============================================================================
// 0. CONFIGURATION & CONSTANTS
// ============================================================================

const SELECTORS = {
    // Modals
    addMemberModal: "#addMemberModal",
    editMemberModal: "#edit_member_modal",
    authSignatoryModal: "#authSignatoryModal",
    corpRepModal: "#corpRepModal",
    bodProxyModal: "#assignProxyBodModal",
    amendmentProxyModal: "#assignProxyAmendmentModal",

    // Forms
    addMemberForm: "#form_add_member",
    editMemberForm: "#form_edit_member",
    authSignatoryForm: "#form_auth_signatory",
    corpRepForm: "#form_corp_rep",
    importForm: "#import_member",
    filterForm: "#filter_form",
    proxyForm: "#assignProxyForm",
    proxyFormAmendment: "#assignProxyFormAmendment",

    // Buttons & Elements
    btnAddMember: "#btn_add_member",
    btnExportRecord: "#btn_export_record",
    btnFilterShow: "#show_filter",
    btnFilterHide: "#hide_filter",
    btnFilterReset: "#btn_filter_reset",
    btnCancelBodProxy: "#btnCancelBodProxy",
    btnCancelAmendmentProxy: "#btnCancelAmendmentProxy",

    // Tables
    memberTable: "#memberTable",
    memberTableBody: "#memberTable tbody",

    // Filter elements
    filterBox: "#filter_box",
    filterPageSelect: "#filter_form [name=active_page]",
};

const CLASSES = {
    btnEditStockholder: ".btn-edit-stockholder",
    btnAuthSignatory: ".btn-auth-signatory",
    btnChangeCorpRep: ".btn-change-corp-rep",
    btnProxyBod: ".btn-proxyholder-bod",
    btnProxyAmendment: ".btn-proxyholder-amendment",
    btnNavigator: ".btn-navigator",
    btnAssignAmendmentProxy: ".btn-assign-amendment-proxy",
};

const MODAL_DEFAULTS = {
    width: "100%",
    allowSingleDeselect: true,
};

// ============================================================================
// 1. INITIALIZATION & SETUP
// ============================================================================

$(document).ready(function () {
    initializeChosenPlugins();
    registerEventHandlers();
    initializeDataLoading();
});

function initializeChosenPlugins() {
    const chosenOptions = { width: MODAL_DEFAULTS.width };
    const chosenOptionsWithDeselect = {
        width: MODAL_DEFAULTS.width,
        allow_single_deselect: MODAL_DEFAULTS.allowSingleDeselect,
    };

    //select2 initialization

    $("select").chosen(chosenOptions);
    $(SELECTORS.editMemberModal + " select").chosen(chosenOptions);
    $("#proxy_assigner_select, #spa_assigner_select").chosen(
        chosenOptionsWithDeselect,
    );
    $("[name=assigner]").chosen(chosenOptionsWithDeselect);

    // Initialize assignor selects for proxy modals
    $(SELECTORS.proxyForm + " [name=assignor]").chosen(
        chosenOptionsWithDeselect,
    );
    $(SELECTORS.proxyFormAmendment + " [name=assignor]").chosen(
        chosenOptionsWithDeselect,
    );
}

function registerEventHandlers() {
    // Modal focus handlers
    $(document).on("shown.bs.modal", SELECTORS.bodProxyModal, () => {
        $(SELECTORS.bodProxyModal + " [name=proxyFormNo]").focus();
    });

    $(document).on("shown.bs.modal", SELECTORS.addMemberModal, function () {
        if ($(SELECTORS.addMemberModal + " [name=stockholder]").val() === "") {
            $(SELECTORS.addMemberModal + " [name=stockholder]").focus();
        }
    });

    // Button handlers
    $(document).on("click", SELECTORS.btnExportRecord, () => {
        location.href = BASE_URL + "admin/stockholder/export";
    });

    $(document).on("click", SELECTORS.btnAddMember, handleAddMemberButtonClick);
    $(document).on(
        "click",
        CLASSES.btnEditStockholder,
        handleEditStockholderClick,
    );
    $(document).on("click", CLASSES.btnAuthSignatory, handleAuthSignatoryClick);
    $(document).on("click", CLASSES.btnChangeCorpRep, handleChangeCorpRepClick);
    $(document).on("click", CLASSES.btnProxyBod, handleBodProxyClick);
    $(document).on(
        "click",
        CLASSES.btnProxyAmendment,
        handleAmendmentProxyClick,
    );
    $(document).on("click", CLASSES.btnAssignAmendmentProxy, (e) => {
        assign_amendment_proxy($(e.currentTarget));
    });

    // Filter handlers
    registerFilterHandlers();

    // Pagination
    $(document).on("click", CLASSES.btnNavigator, handlePaginationClick);

    // Form submissions
    $(document).on("submit", SELECTORS.addMemberForm, handleAddMemberSubmit);
    $(document).on(
        "submit",
        SELECTORS.editMemberForm,
        handleEditMemberFormSubmit,
    );
    $(document).on(
        "submit",
        SELECTORS.authSignatoryForm,
        handleAuthSignatorySubmit,
    );
    $(document).on("submit", SELECTORS.corpRepForm, handleCorpRepSubmit);
    $(document).on("submit", SELECTORS.importForm, handleImportMemberSubmit);

    // Status switch handler
    $(document).on("change", ".status-switch", handleStatusSwitchChange);

    // Account type change
    $(document).on(
        "change",
        SELECTORS.addMemberModal + " [name=account_type]",
        handleAccountTypeChange,
    );
}

function registerFilterHandlers() {
    $(document).on("change", SELECTORS.filterForm + " select", function () {
        if ($(this).attr("name") !== "active_page") {
            $(SELECTORS.filterPageSelect).val(1).trigger("chosen:updated");
        }
        loadStockholders();
    });

    $(document).on("click", SELECTORS.btnFilterShow, () => {
        toggleFilterVisibility($(this).attr("data-visible") === "true");
    });

    $(document).on("click", SELECTORS.btnFilterReset, () => {
        resetFilter();
        loadStockholders();
    });
}

function toggleFilterVisibility(show) {
    $(SELECTORS.btnFilterShow)
        .toggleClass("hidden", show)
        .attr("data-visible", !show);
    $(SELECTORS.btnFilterShow).text(show ? "Show Filter" : "Hide Filter");

    $(SELECTORS.filterBox).slideToggle();
}

function resetFilter() {
    $(SELECTORS.filterPageSelect).html('<option value="1">1</option>');
    $(SELECTORS.filterForm)[0].reset();
    $(SELECTORS.filterForm + " select").trigger("chosen:updated");
}

function initializeDataLoading() {
    loadStockholders();
    loadFilterDataUsers();
    loadOptionAssignees();
}

// ============================================================================
// 1.5 EVENT HANDLER HELPERS
// ============================================================================

function handleAddMemberButtonClick() {
    const accountNo = prompt("Account number:");
    if (accountNo === null) return;
    if (accountNo.trim() === "") {
        alert("Account number is required");
        return;
    }
    getAccountNoDetails(accountNo);
}

function handleEditStockholderClick() {
    const userId = $(this).attr("data-user-id");
    editStockholder(userId);
}

function handleAuthSignatoryClick(e) {
    e.preventDefault();
    const userId = $(this).attr("data-user-id");

    loadAuthSignatory(userId);
}

function handleChangeCorpRepClick(e) {
    e.preventDefault();
    const userId = $(this).attr("data-user-id");
    loadCorpRep(userId);
}

function handleBodProxyClick() {
    const accountId = $(this).attr("data-account-id");
    loadProxyBod(accountId);
}

function handleAmendmentProxyClick() {
    const accountId = $(this).attr("data-account-id");
    loadProxyAmendment(accountId);
}

function handlePaginationClick(e) {
    e.preventDefault();
    const href = $(this).attr("href");
    if (!href) return;
    const url = href.substring(href.lastIndexOf("=") + 1);
    loadStockholders(url);
}

function handleAccountTypeChange() {
    const accountType = $(this).children("option:selected").val();
    setFieldForCorpRep(accountType);
    setFieldAccountTypeVoteInPerson(accountType);
    $(SELECTORS.addMemberModal + " select").trigger("chosen:updated");
}

function handleStatusSwitchChange(e) {
    e.preventDefault();

    const switchElement = $(this);
    const isChecked = switchElement.is(":checked");
    const userId = switchElement.data("user-id");
    const statusText = isChecked ? "Active" : "Delinquent";
    const delinquentValue = isChecked ? 0 : 1;

    // Revert the switch back to its original state
    switchElement.prop("checked", !isChecked);

    // Show confirmation dialog
    Swal.fire({
        title: "Confirm Status Change",
        html: `<div class="text-left">
            <p>Are you sure you want to mark this account as <strong>${statusText}</strong>?</p>
            <p style="font-size: 0.9rem; color: #666; margin-top: 1rem;">This action will update the stockholder's account status.</p>
        </div>`,
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "#27ae60",
        cancelButtonColor: "#d33",
        confirmButtonText: "Yes, Change Status",
        cancelButtonText: "Cancel",
    }).then((result) => {
        if (result.isConfirmed) {
            submitStatusChange(
                userId,
                delinquentValue,
                switchElement,
                statusText,
            );
        }
    });
}

function submitStatusChange(
    userId,
    delinquentValue,
    switchElement,
    statusText,
) {
    performAjax({
        url: `${BASE_URL}admin/stockholder/${userId}`,
        method: "PUT",
        data: {
            delinquent: delinquentValue,
            id: userId,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        beforeSend: () => {
            switchElement.attr("disabled", true);
        },
        success: (response) => {
            // Update the switch state
            switchElement.prop("checked", delinquentValue === 0);

            // Update the label text
            switchElement
                .closest(".custom-control")
                .find(".switch-label")
                .text(statusText);

            Swal.fire({
                icon: "success",
                title: "Status Updated",
                text: `Account status has been changed to ${statusText}`,
                timer: 1500,
            });
        },
        error: (xhr) => {
            // Revert the switch back
            switchElement.prop("checked", delinquentValue !== 0);
            handleError(xhr);
        },
        complete: () => {
            switchElement.attr("disabled", false);
        },
    });
}

// ============================================================================
// 2. DATA LOADING FUNCTIONS
// ============================================================================

/**
 * Generic AJAX helper for consistent error handling
 */
function performAjax(config) {
    const defaults = {
        method: "GET",
        dataType: "json",
    };

    return $.ajax({ ...defaults, ...config });
}

function loadStockholders(page = null) {
    const filterData = $("#filter_form").serialize();
    const pageNo = page || $(SELECTORS.filterPageSelect).val();
    const tableBody = $(SELECTORS.memberTableBody);

    tableBody.html(
        '<tr><td class="text-center text-muted" colspan="10">Loading...</td></tr>',
    );

    performAjax({
        url: `${BASE_URL}admin/stockholder/load?page=${pageNo}`,
        data: filterData,
        success: displayStockholder,
        error: handleError,
        complete: () => tableBody.css("opacity", 1),
    });
}

function loadOptionAssignees() {
    performAjax({
        url: `${BASE_URL}admin/stockholder/assignee`,
        success: (data) => {
            const options = buildOptionsList(data.onlineAccounts, "");
            $("[name=assignee]").html(options).trigger("chosen:updated");
        },
        error: handleError,
    });
}

function loadFilterDataUsers() {
    performAjax({
        url: `${BASE_URL}admin/stockholder/user`,
        success: (data) => {
            const options = buildFilterUserOptions(data.users);
            $(SELECTORS.filterForm + " [name=accounts]")
                .html(options)
                .trigger("chosen:updated");
        },
        error: handleError,
    });
}

function buildOptionsList(items, defaultValue = "") {
    let html = `<option value="">${defaultValue}</option>`;
    if (items) {
        Object.entries(items).forEach(([key, value]) => {
            html += `<option value="${key}">${value} (${key})</option>`;
        });
    }
    return html;
}

function buildFilterUserOptions(users) {
    let html = '<option value="">All</option>';
    for (let userId in users) {
        if (users.hasOwnProperty(userId)) {
            const user = users[userId];
            html += `<option value="${user.account_no}">${user.account_key} ${user.full_name}</option>`;
        }
    }
    return html;
}

function getAccountNoDetails(accountNo) {
    performAjax({
        url: `${BASE_URL}admin/stockholder/${accountNo}`,
        data: { account_no: accountNo },
        beforeSend: () => $(SELECTORS.addMemberForm).trigger("reset"),
        statusCode: {
            200: (data) => handleAccountDetailsResponse(data, accountNo),
        },
        error: handleError,
    });
}

function handleAccountDetailsResponse(data, accountNo) {
    if (data.stockholder !== null) {
        setFieldsForExistingStockholder(data.stockholder);
        setFieldForCorpRep(data.stockholder.accountType);
        populateSuffixOptions(data.suffixes);
        setFieldAccountTypeVoteInPerson(
            data.stockholder.accountType,
            data.stockholder.voteInPerson,
        );
    } else {
        setFieldsForNewStockholder(accountNo);
        setFieldForCorpRep();
        populateSuffixOptions();
        setFieldAccountTypeVoteInPerson();
    }

    $(SELECTORS.addMemberModal + " select").trigger("chosen:updated");
    $(SELECTORS.addMemberModal).modal("show");
}

// ============================================================================
// 3. FORM POPULATION & VALIDATION
// ============================================================================

function populateStockholderForm(data) {
    const modal = SELECTORS.editMemberModal;
    const stockholder = data.stockholder;
    const isIndividual = stockholder.accountType === "indv";

    const accountTypeOption = `${modal} [name=account_type] option[value=${stockholder.accountType}]`;

    $(modal + " .modal-title").text("EDIT STOCKHOLDER");
    $(modal + " [name=stockholder]")
        .val(stockholder.stockholder)
        .attr("readonly", false);
    $(modal + " [name=account_number]")
        .val(stockholder.accountNo)
        .attr("readonly", true);

    $(accountTypeOption)
        .prop("selected", true)
        .attr("disabled", false)
        .siblings()
        .removeAttr("selected")
        .attr("disabled", true);
    $(modal + " [name=vote_in_person]")
        .attr("disabled", isIndividual)
        .find('option[value="' + stockholder.voteInPerson + '"]')
        .prop("selected", true)
        .attr("disabled", false)
        .siblings()
        .removeAttr("selected")
        .attr("disabled", isIndividual);

    showStockholderDetailsSection();
}

// function populateStockForm(data) {
//     const modal = SELECTORS.editMemberModal;
//     const account = data.stockholder_account;
//     const stockholder = account.stockholder;

//     $(modal + " .modal-title").text("EDIT STOCK STATUS");

//     $(modal + " [id=edit_stock_details_title]").text(
//         stockholder.accountNo +
//             "-" +
//             account.suffix +
//             " | " +
//             stockholder.stockholder,
//     );

//     $(modal + " [name=delinquent]")
//         .attr("disabled", false)
//         .find("option[value=" + account.isDelinquent + "]")
//         .attr("disabled", false)
//         .prop("selected", true)
//         .siblings()
//         .removeAttr("selected")
//         .attr("disabled", false);

//     // showCorpRepDetailsSection();
// }

function populateSuffixOptions(suffixes = []) {
    let suffixMaxValue = 50;
    let suffixSelect = '<option value="">-Suffix-</option>';

    for (let suf = 1; suf <= suffixMaxValue; suf++) {
        suffixSelect += suffixes.includes(suf)
            ? `<option disabled>${suf}</option>`
            : `<option value="${suf}">${suf}</option>`;
    }

    $("#addMemberModal [name=suffix]").html(suffixSelect);
}

function setFieldsForExistingStockholder(stockholder) {
    const selector = SELECTORS.addMemberModal + " ";
    $(selector + "[name=stockholder]")
        .val(stockholder.stockholder)
        .attr("readonly", true);
    $(selector + "[name=account_number]")
        .val(stockholder.accountNo)
        .attr("readonly", true);
    $(selector + "[name=email]").val(stockholder.user.email);
    $(".form-corp-only").toggle(stockholder.accountType !== "indv");
}

function setFieldsForNewStockholder(accountNo) {
    const selector = SELECTORS.addMemberModal + " ";
    $(selector + "[name=stockholder]").attr("readonly", false);
    $(selector + "[name=account_number]")
        .attr("readonly", false)
        .val(accountNo);
    $(selector + "[name=email]");
}

function setFieldForCorpRep(accountType = null) {
    const isIndividual = accountType === "indv";
    const isDisabled = isIndividual;

    $(".form-corp-only").toggle(!isIndividual).find("input").val("").prop({
        readonly: isDisabled,
        disabled: isDisabled,
    });
}

function setFieldAccountTypeVoteInPerson(
    accountType = null,
    voteInPerson = null,
) {
    const voteField = $(SELECTORS.addMemberModal + " [name=vote_in_person]");
    const accountField = $(SELECTORS.addMemberModal + " [name=account_type]");

    if (accountType === null) {
        voteField
            .val("")
            .find("option")
            .removeAttr("selected")
            .attr("disabled", false);
        accountField
            .val("")
            .find("option")
            .removeAttr("selected")
            .attr("disabled", false);
        return;
    }

    if (voteInPerson === null) {
        if (accountType === "indv") {
            voteField
                .find("option[value=stockholder]")
                .attr("disabled", false)
                .prop("selected", true)
                .siblings()
                .removeAttr("selected")
                .attr("disabled", true);
            return;
        }
        voteField
            .val("")
            .find("option")
            .attr("disabled", false)
            .removeAttr("selected")
            .trigger("chosen:updated");
    } else {
        voteField
            .find("option[value=" + voteInPerson + "]")
            .attr("disabled", false)
            .prop("selected", true)
            .siblings()
            .removeAttr("selected")
            .attr("disabled", true);
        accountField
            .find("option[value=" + accountType + "]")
            .attr("disabled", false)
            .prop("selected", true)
            .siblings()
            .removeAttr("selected")
            .attr("disabled", true);
    }
}

function validateEditMemberData(data) {
    if (!data || !data.role) {
        throw new Error("Invalid response: missing role information");
    }

    if (data.role === "corp-rep" && !data.stockholder_account) {
        throw new Error(
            "Invalid response: missing corporate representative data",
        );
    }

    if (data.role === "stockholder" && !data.stockholder) {
        throw new Error("Invalid response: missing stockholder data");
    }
}

// ============================================================================
// 4. MEMBER EDIT OPERATIONS
// ============================================================================

function editStockholder(userId) {
    $.ajax({
        url: BASE_URL + `admin/stockholder/${userId}/edit`,
        method: "GET",
        dataType: "json",
        beforeSend: function () {
            $("#form_edit_member").trigger("reset");
            showLoader("Loading member data...");
        },
        success: function (data) {
            try {
                validateEditMemberData(data);
                setupEditMemberModal(data, userId);
                displayEditMemberModal();
            } catch (err) {
                handleEditMemberError(err);
            }
        },
        error: function (xhr) {
            handleError(xhr);
        },
        complete: function () {
            hideLoader();
        },
    });
}

function setupEditMemberModal(data, userId) {
    $(SELECTORS.editMemberModal + " [name=id]").val(userId);
    const isCorpRep = data.role === "corp-rep";

    $(".stock-details-wrapper").toggle(isCorpRep);

    $("#form_edit_member").trigger("reset");

    if (!isCorpRep) {
        populateStockholderForm(data);
    } else {
        throw new Error(
            "Editing corporate representatives is not allowed through this interface. Please use the 'Change Corp Rep' button instead.",
        );
    }
}

function displayEditMemberModal() {
    $(SELECTORS.editMemberModal + " select").trigger("chosen:updated");
    $(SELECTORS.editMemberModal).modal("show");
}

function handleEditMemberError(err) {
    console.error("Edit member error:", err);
    Swal.fire({
        icon: "error",
        title: "Error",
        text: err.message || "Failed to load member data",
    });
}

// ============================================================================
// 4.5 AUTHORIZED SIGNATORY OPERATIONS
// ============================================================================

function loadAuthSignatory(userId) {
    performAjax({
        url: `${BASE_URL}admin/stockholder/${userId}/auth-signatory`,
        beforeSend: () => showLoader("Loading authorized signatory..."),
        success: (data) => {
            setupAuthSignatoryModal(data, userId);
            $(SELECTORS.authSignatoryModal).modal("show");
        },
        error: (xhr) => {
            handleError(xhr);
        },
        complete: () => hideLoader(),
    });
}

function setupAuthSignatoryModal(data, userId) {
    $(SELECTORS.authSignatoryForm + " [name=id]").val(userId);

    // Extract from the response structure: data.authorizedSignatory
    const authSig = data.authorizedSignatory || {};
    const authSignatoryName = authSig.stockholder.authorizedSignatory || "";
    const authSignatoryEmail = authSig.email || "";
    const accountNo = authSig.stockholder.accountNo || "";
    const stockholders = authSig.stockholder.stockholder || "";

    $(SELECTORS.authSignatoryForm + " [name=auth_signatory]").val(
        authSignatoryName,
    );
    $(SELECTORS.authSignatoryForm + " [name=auth_signatory_email]").val(
        authSignatoryEmail,
    );

    $("#auth_sig_stockholder_text").text(accountNo + " | " + stockholders);
}

function handleAuthSignatorySubmit(e) {
    e.preventDefault();

    const id = $(this).find("[name=id]").val();
    const authSignatory = $(this).find("[name=auth_signatory]").val();
    const authSignatoryEmail = $(this)
        .find("[name=auth_signatory_email]")
        .val();

    const submitBtn = $(
        SELECTORS.authSignatoryForm + " #btn_submit_auth_signatory",
    );
    const spinner = $(SELECTORS.authSignatoryForm + " .submit-loading");

    performAjax({
        url: `${BASE_URL}admin/stockholder/${id}/auth-signatory`,
        method: "POST",
        data: {
            auth_signatory: authSignatory,
            auth_signatory_email: authSignatoryEmail,
            _token: $(SELECTORS.authSignatoryForm + " [name=_token]").val(),
        },
        beforeSend: () => {
            submitBtn.attr("disabled", true);
            spinner.show();
        },
        success: (response) => {
            Swal.fire({
                icon: "success",
                title: "Success",
                text:
                    response.message ||
                    "Authorized Signatory updated successfully",
            }).then(() => {
                $(SELECTORS.authSignatoryModal).modal("hide");
                loadStockholders();
            });
        },
        error: (xhr) => {
            handleError(xhr);
        },
        complete: () => {
            submitBtn.attr("disabled", false);
            spinner.hide();
        },
    });
}

function loadCorpRep(userId) {
    performAjax({
        url: `${BASE_URL}admin/stockholder/${userId}/corporate-rep`,
        beforeSend: () => showLoader("Loading corporate representative..."),
        success: (data) => {
            setupCorpRepModal(data, userId);
            $(SELECTORS.corpRepModal).modal("show");
        },
        error: (xhr) => {
            handleError(xhr);
        },
        complete: () => hideLoader(),
    });
}

function setupCorpRepModal(data, userId) {
    $(SELECTORS.corpRepForm + " [name=id]").val(userId);

    // Extract from the response structure: data.corpRepresentative
    const corpRepData = data.corpRepresentative || {};
    const corpRepName = corpRepData.stockholder_account.corpRep || "";
    const corpRepEmail = corpRepData.email || "";
    const stockholderName =
        corpRepData.stockholder_account.stockholder.stockholder || "";
    const accountNo = corpRepData.stockholder_account.accountKey || "";

    $(SELECTORS.corpRepForm + " #corp_rep_stockholder_text").text(
        accountNo + " | " + stockholderName,
    );

    $(SELECTORS.corpRepForm + " [name=corp_rep]").val(corpRepName);
    $(SELECTORS.corpRepForm + " [name=corp_rep_email]").val(corpRepEmail);
}

function handleCorpRepSubmit(e) {
    e.preventDefault();

    const userId = $(this).find("[name=id]").val();
    const corpRep = $(this).find("[name=corp_rep]").val();
    const corpRepEmail = $(this).find("[name=corp_rep_email]").val();

    const submitBtn = $(SELECTORS.corpRepForm + " #btn_submit_corp_rep");
    const spinner = $(SELECTORS.corpRepForm + " .submit-loading");

    performAjax({
        url: `${BASE_URL}admin/stockholder/${userId}/corporate-rep`,
        method: "POST",
        data: {
            corp_rep: corpRep,
            corp_rep_email: corpRepEmail,
            _token: $(SELECTORS.corpRepForm + " [name=_token]").val(),
        },
        beforeSend: () => {
            submitBtn.attr("disabled", true);
            spinner.show();
        },
        success: (response) => {
            Swal.fire({
                icon: "success",
                title: "Success",
                text:
                    response.message ||
                    "Corporate Representative updated successfully",
            }).then(() => {
                $(SELECTORS.corpRepModal).modal("hide");
                loadStockholders();
            });
        },
        error: (xhr) => {
            handleError(xhr);
        },
        complete: () => {
            submitBtn.attr("disabled", false);
            spinner.hide();
        },
    });
}

// ============================================================================
// 5. TABLE DISPLAY FUNCTIONS
// ============================================================================

function displayStockholder(data) {
    const paginationData = data["data"];
    const records = buildTableRows(
        paginationData["data"],
        paginationData["from"],
    );

    updateTableBody(records, paginationData.length === 0);
    updatePaginationControls(paginationData);
    updateRecordSummary(paginationData);
}

function buildTableRows(users, startCounter) {
    return users
        .map((user, index) => {
            const counter = startCounter + index;
            return buildStockRows(user, counter);
        })
        .join("");
}

function updateTableBody(records, isEmpty) {
    const emptyRow =
        '<tr><td class="text-center text-muted" colspan="9">No data</td></tr>';

    $(SELECTORS.memberTableBody).html(isEmpty ? emptyRow : records);
}

function updatePaginationControls(data) {
    $(".page-item .btn-prev").attr("href", data.prev_page_url);
    $(".page-item .btn-next").attr("href", data.next_page_url);
    $(".active-page").text(`Page ${data.current_page} of ${data.last_page}`);

    const pageOptions = Array.from({ length: data.last_page }, (_, i) => i + 1)
        .map(
            (page) =>
                `<option ${data.current_page === page ? "selected" : ""} value="${page}">${page}</option>`,
        )
        .join("");

    $(SELECTORS.filterPageSelect).html(pageOptions).trigger("chosen:updated");
}

function updateRecordSummary(data) {
    const summary =
        data.total === 0
            ? "No record found"
            : `Showing records from ${data.from} to ${data.to} of <span class="font-weight-bold">${data.total}</span> records`;

    $(".record-summary").html(summary);
}

function getAccountTypeLabel(accountType) {
    return accountType === "indv" ? "Indv" : "Corp.";
}

function buildAuthSignatoryDisplay(stockholder) {
    const authSignatory =
        stockholder.accountType === "corp"
            ? stockholder.authorizedSignatory
            : stockholder.stockholder;

    if (stockholder.accountType === "corp") {
        const authSignatoryContent = authSignatory
            ? `<strong style="color: #000; border-bottom: 1px solid currentColor;">${authSignatory || ""}</strong>`
            : `<span style="color: #e67e22; font-style: italic; display: inline-block; padding: 2px 6px; background-color: #fff3e0; border-radius: 3px;"><i class="fas fa-plus-circle" style="margin-right: 4px;"></i>Add auth. sig</span>`;
        return `<a href="#" data-user-id="${stockholder.userId}" class="text-decoration-none btn-auth-signatory" >
                    <div>${authSignatoryContent}</div>
                    <div style="font-size: 0.85rem; color: #6c757d;">${stockholder.user.email || ""}</div>
                </a>`;
    } else {
        return `<div><strong>${authSignatory}</strong></div>
                <div style="font-size: 0.85rem; color: #6c757d;">${stockholder.user.email || ""}</div>`;
    }
}

function buildCorpRepDisplay(id, stockholder, corpRep, userEmail) {
    if (stockholder.accountType === "corp") {
        const corpRepContent = corpRep
            ? `<strong>${corpRep}</strong>`
            : `<span style="color: #68a578; font-style: italic; display: inline-block; padding: 2px 6px; background-color: #fff3e0; border-radius: 3px;"><i class="fas fa-plus-circle" style="margin-right: 4px;"></i>Add corp. rep</span>`;

        return `<a class="btn-change-corp-rep" href="#" data-user-id="${id}">
                    <div><span style="color: #000; border-bottom: 1px solid currentColor; ">${corpRepContent}</span></div>
                    <div style="font-size: 0.85rem; color: #6c757d;">${userEmail || ""}</div>
                </a>`;
    } else {
        return `<div><strong></strong></div>
                <div style="font-size: 0.85rem; color: #6c757d;"></div>`;
    }
}

function buildStockRows(user, counter) {
    const { id, stockholder_account } = user;
    const { accountId, accountKey, corpRep, isDelinquent } =
        stockholder_account;

    const stockholder = stockholder_account.stockholder;
    const status = getStatusSwitch(id, isDelinquent);
    const accountType = getAccountTypeLabel(stockholder.accountType);
    const authSignatoryDisplay = buildAuthSignatoryDisplay(stockholder);
    const corpRepDisplay = buildCorpRepDisplay(
        id,
        stockholder,
        corpRep,
        user.email,
    );
    const dropdownId = `proxyDropdown_${accountId}`;

    return `<tr data-user-id="${id}" data-account-id="${accountId}">
                <td class="td-padding">#${counter}</td>
                <td class="accountKey td-padding text-nowrap td-account-no">
                  ${accountKey}
                </td>
                <td class="td-padding">
                    <a href="#" class="btn-edit-stockholder" style="color: #000; border-bottom: 1px solid currentColor;" data-user-id="${stockholder.userId}">${stockholder.stockholder}
                </a></td>
                <td class="td-padding">
                    ${authSignatoryDisplay}
                </td>
                <td class="stockholder td-padding td-stockholder">
                    ${corpRepDisplay}
                </td>
                <td class="td-padding">${accountType}</td>
                <td class="status">${status}</td>
                <td>${buildProxyActions(accountId, accountKey)}</td>
            </tr>`;
}

function getStatusSwitch(userId, isDelinquent) {
    // Handle various input types (1, "1", true, etc.)
    const isDeliq = [1, "1", true].includes(isDelinquent);
    const switchId = `statusSwitch_${userId}`;

    return `
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input status-switch" id="${switchId}" data-user-id="${userId}" ${!isDeliq ? "checked" : ""} title="${isDeliq ? "Delinquent" : "Active"}">
            <label class="custom-control-label" for="${switchId}" style="margin-bottom: 0; cursor: pointer;">
                <span class="switch-label">${isDeliq ? "Delinquent" : "Active"}</span>
            </label>
        </div>
    `;
}

function buildProxyActions(accountId, accountKey) {
    const proxyDropdownId = `proxyDropdown_${accountId}`;
    const historyDropdownId = `historyDropdown_${accountId}`;
    return `
        <div style="display: flex; gap: 0.5rem;">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" id="${proxyDropdownId}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    Proxy
                </button>
                <div class="dropdown-menu" aria-labelledby="${proxyDropdownId}">
                    <a class="dropdown-item btn-proxyholder-amendment" href="#" data-account-id="${accountId}">Amendment</a>
                    <a class="dropdown-item btn-proxyholder-bod disabled" href="#" data-account-id="${accountId}" disabled>BOD</a>
                </div>
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle" id="${historyDropdownId}" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                    History
                </button>
                <div class="dropdown-menu" aria-labelledby="${historyDropdownId}">
                    <a class="dropdown-item btn-proxyholder-history" href="#" data-account-id="${accountId}" data-account-no="${accountKey}" data-proxy-type="Amendment">Amendment History</a>
                    <a class="dropdown-item btn-proxyholder-history disabled" href="#" data-account-id="${accountId}" data-account-no="${accountKey}" data-proxy-type="BOD" disabled>BOD History</a>
     
                </div>
            </div>
        </div>
        `;
}

// ============================================================================
// 6. PROXY MANAGEMENT - COMMON FUNCTIONS
// ============================================================================

function buildUserDisplay(userObject) {
    const { role } = userObject;

    const displayConfigs = {
        stockholder: () => {
            const { accountNo, stockholder } = userObject.stockholder;
            return `${accountNo} ${stockholder} <span class="badge badge-success">stockholder</span>`;
        },
        "corp-rep": () => {
            const { accountKey, corpRep } = userObject.stockholder_account;
            return `${accountKey} ${corpRep} <span class="badge badge-success">corporate representative</span>`;
        },
        "non-member": () => {
            const { nonmemberAccountNo, firstName, lastName } =
                userObject.non_member_account;
            return `${nonmemberAccountNo} ${firstName} ${lastName} <span class="badge badge-success">non-member</span>`;
        },
    };

    return displayConfigs[role] ? displayConfigs[role]() : "";
}

// ============================================================================
// 7. BOD PROXY OPERATIONS
// ============================================================================

function loadProxyBod(id) {
    performAjax({
        url: `${BASE_URL}admin/bod-proxy/${id}`,
        success: (data) => {
            setupBodProxyModal(data, id);
            $(SELECTORS.bodProxyModal).modal("show");
        },
        error: handleError,
    });
}

// Backward compatibility alias
const load_proxyhoder_bod = loadProxyBod;

function setupBodProxyModal(data, id) {
    const hasProxyBoard = data.proxy_board !== null;

    updateBodProxyFormVisibility(hasProxyBoard);

    if (hasProxyBoard) {
        showProxyBod(data);
    } else {
        showAssignBodForm(id, data);
    }
}

function updateBodProxyFormVisibility(hasProxyBoard) {
    $(SELECTORS.proxyForm + " .assign-stock-form").toggle(!hasProxyBoard);
    $(SELECTORS.proxyForm + " .assignee-details").toggle(hasProxyBoard);
}

function showProxyBod(data) {
    const proxyBoard = data.proxy_board;
    const assignor = buildUserDisplay(proxyBoard.assignor);
    const assignee = buildUserDisplay(proxyBoard.assignee);
    const stockKey = `${data.accountKey} ${data.stockholder.stockholder}`;
    const detailsForm = $(SELECTORS.proxyForm + " .assignee-details");

    $(SELECTORS.btnCancelBodProxy).attr("data-id", proxyBoard.proxyBodId);

    detailsForm.find(".assignee-details-stock").text(stockKey);
    detailsForm
        .find(".assignee-details-form-no")
        .text(proxyBoard.proxyBodFormNo);
    detailsForm.find(".assignee-details-assignor").html(assignor);
    detailsForm.find(".assignee-details-assignee").html(assignee);
}

function showAssignBodForm(id, data) {
    $(SELECTORS.proxyForm)[0].reset();
    $(SELECTORS.proxyForm + " select").trigger("chosen:updated");

    const stockholder = data.stockholder.stockholder;
    const accountKey = `${data.accountKey} ${data.stockholder.stockholder}`;
    let assignorHtml = `<option value="${data.stockholder.userId}">${stockholder}</option>`;

    $(SELECTORS.proxyForm + " .account-to-assign").val(accountKey);

    if (data.stockholder.accountType === "corp") {
        assignorHtml = "<option></option>";
        assignorHtml += `<option value="${data.stockholder.userId}">${data.stockholder.accountNo} ${stockholder} (SH/CS)</option>`;

        data.stockholder.stockholder_accounts.forEach((account) => {
            const disabled = account.corpRep !== null ? "" : "disabled";
            const displayName = `${account.accountKey} ${data.stockholder.stockholder} | ${account.corpRep || "---no corp rep---"}(CR)`;
            assignorHtml += `<option ${disabled} value="${account.userId}">${displayName}</option>`;
        });
    }

    $(SELECTORS.proxyForm + " [name=accountToAssign]").val(id);
    $(SELECTORS.proxyForm + " [name=assignor]")
        .html(assignorHtml)
        .trigger("chosen:updated");
}

function assignBodProxy(btn) {
    const assignBtn = $(btn);

    performAjax({
        url: `${BASE_URL}admin/bod-proxy`,
        method: "POST",
        data: $(SELECTORS.proxyForm).serialize(),
        beforeSend: () => assignBtn.attr("disabled", true),
        complete: () => assignBtn.attr("disabled", false),
        success: (data) => {
            Swal.fire({
                icon: "success",
                title: "Success",
                text: data.message,
            }).then(() => {
                loadStockholders();
                $(SELECTORS.bodProxyModal).modal("hide");
            });
        },
        error: handleError,
    });
}

// Backward compatibility alias
const assign_bod_proxy = assignBodProxy;

function cancelBodProxy(thisElem) {
    const btnCancel = $(thisElem);
    const accountId = $(thisElem).attr("data-id");

    $("#assignProxyBodModal").modal("hide");

    showCancellationReasonDialog()
        .then((reason) => reason && showCancellationRemarksDialog(reason))
        .then((data) => data && showCancellationConfirmDialog(data))
        .then(
            (confirmed) =>
                confirmed && submitBodProxyCancellation(accountId, confirmed),
        )
        .catch((error) => {
            if (error?.retry) {
                cancelBodProxy(thisElem);
            } else if (error) {
                handleError(error);
            }
        });
}

function showCancellationReasonDialog() {
    return new Promise((resolve, reject) => {
        Swal.fire({
            title: "Select Cancellation Reason",
            text: "Please select a reason for cancelling this proxy:",
            icon: "question",
            input: "select",
            inputOptions: getCancellationReasons(),
            inputPlaceholder: "Select a reason",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Continue",
            cancelButtonText: "Cancel",
            inputValidator: (value) =>
                value ? null : "Please select a reason for cancellation",
        }).then((result) => {
            if (result.isConfirmed) {
                resolve(result.value);
            } else {
                reject(null);
            }
        });
    });
}

function showCancellationRemarksDialog(reason) {
    const reasonText = getCancellationReasonText(reason);

    return new Promise((resolve, reject) => {
        Swal.fire({
            title: "Add Remarks",
            html: `<div class="text-left mb-3">
               <p><strong>Reason:</strong> ${reasonText}</p>
               <label class="form-label">Remarks (Optional):</label>
             </div>`,
            input: "textarea",
            inputPlaceholder:
                "Enter additional remarks or details about the cancellation...",
            inputAttributes: {
                "aria-label": "Remarks",
                maxlength: 500,
                rows: 4,
            },
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Continue",
            cancelButtonText: "Back",
            allowOutsideClick: false,
            focusConfirm: false,
        }).then((result) => {
            if (result.isConfirmed) {
                resolve({ reason, reasonText, remarks: result.value || "" });
            } else {
                reject({ retry: true });
            }
        });
    });
}

function showCancellationConfirmDialog(data) {
    const { reasonText, remarks } = data;

    return new Promise((resolve, reject) => {
        Swal.fire({
            title: "Confirm Proxy Cancellation",
            html: `<div class="text-left">
               <p><strong>Reason:</strong> ${reasonText}</p>
               <p><strong>Remarks:</strong> ${remarks || "None"}</p>
               <br>
               <p class="text-warning">Are you sure you want to cancel this proxy?</p>
             </div>`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, Cancel Proxy",
            cancelButtonText: "No, Keep Proxy",
        }).then((result) => {
            if (result.isConfirmed) {
                resolve(data);
            } else {
                reject(null);
            }
        });
    });
}

function submitBodProxyCancellation(accountId, data) {
    $.ajax({
        url: `${BASE_URL}admin/bod-proxy/${accountId}/cancel`,
        method: "POST",
        dataType: "json",
        data: {
            reason: data.reason,
            remarks: data.remarks,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            Swal.fire({
                icon: "success",
                title: "Proxy Cancelled Successfully",
                html: `<div class="text-left">
                 <p>${response.message}</p>
                 <p><strong>Reason:</strong> ${data.reasonText}</p>
                 ${data.remarks ? `<p><strong>Remarks:</strong> ${data.remarks}</p>` : ""}
               </div>`,
            }).then(() => {
                loadStockholders();
                $("#assignProxyBodModal").modal("hide");
            });
        },
        error: handleError,
    });
}

function getCancellationReasons() {
    return {
        quorum: "Quorum",
        encoding_error: "Encoding Error",
    };
}

function getCancellationReasonText(reason) {
    const reasons = getCancellationReasons();
    return reasons[reason] || reason;
}

// ============================================================================
// 8. AMENDMENT PROXY OPERATIONS
// ============================================================================

function loadProxyAmendment(accountId) {
    performAjax({
        url: `${BASE_URL}admin/amendment-proxy/${accountId}`,
        success: (data) => {
            const hasProxy = data.proxy_amendment !== null;
            $(SELECTORS.proxyFormAmendment + " .assign-stock-form").toggle(
                !hasProxy,
            );
            $(SELECTORS.proxyFormAmendment + " .assignee-details").toggle(
                hasProxy,
            );

            if (hasProxy) {
                showProxyAmendment(data);
            } else {
                showAssignAmendmentForm(accountId, data);
            }

            $(SELECTORS.amendmentProxyModal).modal("show");
        },
        error: handleError,
    });
}

// Backward compatibility alias
const load_proxyhoder_amendment = loadProxyAmendment;

function showProxyAmendment(data) {
    const amendment = data.proxy_amendment;
    const assignor = buildUserDisplay(amendment.assignor);
    const assignee = buildUserDisplay(amendment.assignee);
    const detailsForm = $(SELECTORS.proxyFormAmendment + " .assignee-details");
    const stockKey = `${data.accountKey} ${data.stockholder.stockholder}`;

    $(SELECTORS.btnCancelAmendmentProxy).attr(
        "data-proxy-id",
        amendment.proxyAmendmentId,
    );

    detailsForm.find(".assignee-details-stock").text(stockKey);
    detailsForm
        .find(".assignee-details-form-no")
        .text(amendment.proxyAmendmentFormNo);
    detailsForm.find(".assignee-details-assignor").html(assignor);
    detailsForm.find(".assignee-details-assignee").html(assignee);
}

function showAssignAmendmentForm(accountId, data) {
    $(SELECTORS.proxyFormAmendment)[0].reset();
    $(SELECTORS.proxyFormAmendment + " select").trigger("chosen:updated");

    const stockholder = data.stockholder.stockholder;
    let assignorHtml = `<option value="${data.stockholder.userId}">${stockholder}</option>`; // Default assignor option for individual stockholder. This will be overridden if the account type is corporate to include corp rep options.
    const accountKey = `${data.accountKey} ${data.stockholder.stockholder}`;

    $(SELECTORS.proxyFormAmendment + " .account-to-assign").val(accountKey);

    if (data.stockholder.accountType === "corp") {
        const authSignatory = data.stockholder.authorizedSignatory
            ? ` | ${data.stockholder.authorizedSignatory} (SH/CS)`
            : `(SH/CS)`;
        const disabledAuthSignatory =
            data.stockholder.authorizedSignatory === null ? "disabled" : "";
        assignorHtml = "<option></option>";
        assignorHtml += `<option value="${data.stockholder.userId}" ${disabledAuthSignatory}>${data.stockholder.accountNo} ${stockholder} ${authSignatory} </option>`;

        data.stockholder.stockholder_accounts.forEach((account) => {
            const disabled = account.corpRep !== null ? "" : "disabled";
            const displayName = `${account.accountKey} ${data.stockholder.stockholder} | ${account.corpRep || "---no corp rep---"}(CR)`;
            assignorHtml += `<option ${disabled} value="${account.userId}">${displayName}</option>`;
        });
    }

    $(SELECTORS.proxyFormAmendment + " [name=refNo]").val("");
    $(SELECTORS.proxyFormAmendment + " [name=accountToAssign]").val(accountId);
    $(SELECTORS.proxyFormAmendment + " [name=assignor]")
        .html(assignorHtml)
        .trigger("chosen:updated");
}

function assignAmendmentProxy(btn) {
    const assignBtn = $(btn);

    performAjax({
        url: `${BASE_URL}admin/amendment-proxy`,
        method: "POST",
        data: $(SELECTORS.proxyFormAmendment).serialize(),
        beforeSend: () => assignBtn.attr("disabled", true),
        complete: () => assignBtn.attr("disabled", false),
        success: (data) => {
            Swal.fire({
                icon: "success",
                title: "Success",
                text: data.message,
            }).then(() => {
                loadStockholders();
                $(SELECTORS.amendmentProxyModal).modal("hide");
            });
        },
        error: handleError,
    });
}

// Backward compatibility alias
const assign_amendment_proxy = assignAmendmentProxy;

function cancelAmendmentProxy(thisElem) {
    const btnCancel = $(thisElem);
    const accountId = $(thisElem).attr("data-id");

    $("#assignProxyAmendmentModal").modal("hide");

    showCancellationReasonDialog()
        .then((reason) => reason && showAmendmentRemarksDialog(reason))
        .then((data) => data && showAmendmentConfirmDialog(data))
        .then(
            (confirmed) =>
                confirmed &&
                submitAmendmentProxyCancellation(accountId, confirmed),
        )
        .catch((error) => {
            if (error?.retry) {
                cancelAmendmentProxy(thisElem);
            } else if (error) {
                handleError(error);
            }
        });
}

function showAmendmentRemarksDialog(reason) {
    const reasonText = getCancellationReasonText(reason);

    return new Promise((resolve, reject) => {
        Swal.fire({
            title: "Add Remarks",
            html: `<div class="text-left mb-3">
               <p><strong>Reason:</strong> ${reasonText}</p>
               <label class="form-label">Remarks (Optional):</label>
             </div>`,
            input: "textarea",
            inputPlaceholder:
                "Enter additional remarks or details about the cancellation...",
            inputAttributes: {
                "aria-label": "Remarks",
                maxlength: 500,
                rows: 4,
            },
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Continue",
            cancelButtonText: "Back",
            allowOutsideClick: false,
            focusConfirm: false,
        }).then((result) => {
            if (result.isConfirmed) {
                resolve({ reason, reasonText, remarks: result.value || "" });
            } else {
                reject({ retry: true });
            }
        });
    });
}

function showAmendmentConfirmDialog(data) {
    const { reasonText, remarks } = data;

    return new Promise((resolve, reject) => {
        Swal.fire({
            title: "Confirm Proxy Cancellation",
            html: `<div class="text-left">
               <p><strong>Reason:</strong> ${reasonText}</p>
               <p><strong>Remarks:</strong> ${remarks || "None"}</p>
               <br>
               <p class="text-warning">Are you sure you want to cancel this proxy?</p>
             </div>`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, Cancel Proxy",
            cancelButtonText: "No, Keep Proxy",
        }).then((result) => {
            if (result.isConfirmed) {
                resolve(data);
            } else {
                reject(null);
            }
        });
    });
}

function submitAmendmentProxyCancellation(accountId, data) {
    $.ajax({
        url: `${BASE_URL}admin/amendment-proxy/${accountId}/cancel`,
        method: "POST",
        dataType: "json",
        data: {
            reason: data.reason,
            remarks: data.remarks,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            Swal.fire({
                icon: "success",
                title: "Proxy Cancelled Successfully",
                html: `<div class="text-left">
                 <p>${response.message}</p>
                 <p><strong>Reason:</strong> ${data.reasonText}</p>
                 ${data.remarks ? `<p><strong>Remarks:</strong> ${data.remarks}</p>` : ""}
               </div>`,
            }).then(() => {
                loadStockholders();
                $("#assignProxyAmendmentModal").modal("hide");
            });
        },
        error: handleError,
    });
}

// ============================================================================
// 9. FORM SUBMISSION HANDLERS
// ============================================================================

function handleAddMemberSubmit(e) {
    e.preventDefault();

    const submitBtn = $(SELECTORS.addMemberModal).find("#btn_submit_member");

    performAjax({
        url: `${BASE_URL}admin/stockholder`,
        method: "POST",
        data: $(SELECTORS.addMemberForm).serialize(),
        beforeSend: () =>
            submitBtn.text("Submitting...").attr("disabled", true),
        complete: () => submitBtn.text("Submit").attr("disabled", false),
        success: (data) => {
            loadStockholders();
            Swal.fire({
                icon: "success",
                title: "Success",
                text: data.message,
            }).then(() => {
                $(SELECTORS.addMemberModal).modal("hide");
            });
        },
        error: handleError,
    });
}

function handleEditMemberFormSubmit(e) {
    e.preventDefault();

    const form = $(e.target);
    const userId = form.find("[name=id]").val();
    const submitBtn = form.find("#btn_submit_edit_member");

    performAjax({
        url: `${BASE_URL}admin/stockholder/${userId}`,
        method: "PUT",
        data: form.serialize(),
        beforeSend: () => submitBtn.attr("disabled", true),
        complete: () => submitBtn.attr("disabled", false),
        success: (data) => {
            Swal.fire({ icon: "success", title: "Success", text: data.message })
                .then(() => loadStockholders())
                .then(() => $(SELECTORS.editMemberModal).modal("hide"));
        },
        error: handleError,
    });
}

function handleImportMemberSubmit(e) {
    e.preventDefault();

    const formData = new FormData();
    const fileInput = $("[name=excel_member]")[0];

    if (!fileInput || !fileInput.files[0]) {
        alert("Please select a file");
        return;
    }

    formData.append("excel_member", fileInput.files[0]);

    const progressBar = $("#myprogress");
    const uploadBtn = $("#btn_upload");

    $.ajax({
        url: `${BASE_URL}stockholder/import`,
        method: "POST",
        data: formData,
        contentType: false,
        processData: false,
        cache: false,

        xhr: function () {
            const xhr = new window.XMLHttpRequest();
            xhr.upload.addEventListener(
                "progress",
                (evt) => {
                    if (evt.lengthComputable) {
                        const percentComplete = Math.round(
                            (evt.loaded / evt.total) * 100,
                        );
                        progressBar
                            .text(percentComplete + "%")
                            .css("width", percentComplete + "%");
                    }
                },
                false,
            );
            return xhr;
        },

        beforeSend: () => uploadBtn.text("Uploading...").attr("disabled", true),
        complete: () => uploadBtn.text("Upload").attr("disabled", false),

        success: (data) => {
            loadStockholders();
            Swal.fire({
                icon: "success",
                title: "Uploaded!",
                text: data.message,
            });
        },
        error: handleError,
    });
}

// ============================================================================
// 10. UTILITY FUNCTIONS
// ============================================================================

function showStockholderDetailsSection() {
    $("#edit_stockholder_details_section").show();
    $("#edit_stock_details_section").hide();
    $("#edit_corp_rep_section").hide();
}

// function showCorpRepDetailsSection() {
//     $("#edit_stockholder_details_section").hide();
//     $("#edit_stock_details_section").show();
//     $("#edit_corp_rep_section").hide();
// }

function populateSuffixOptions(suffixes = []) {
    let suffixSelect = '<option value="">-Suffix-</option>';

    for (let suf = 1; suf <= 50; suf++) {
        suffixSelect += suffixes.includes(suf)
            ? `<option disabled>${suf}</option>`
            : `<option value="${suf}">${suf}</option>`;
    }

    $(SELECTORS.addMemberModal + " [name=suffix]").html(suffixSelect);
}

function showLoader(message = "Loading...") {
    const loaderId = "app-loader-overlay";

    // Remove existing loader if any
    $("#" + loaderId).remove();

    // Create and show loader
    const loaderHtml = `
        <div id="${loaderId}" style="
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 9999;
        ">
            <div style="
                background: white;
                padding: 2rem;
                border-radius: 8px;
                text-align: center;
                box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
            ">
                <div style="
                    border: 4px solid #f3f3f3;
                    border-top: 4px solid #27ae60;
                    border-radius: 50%;
                    width: 40px;
                    height: 40px;
                    animation: spin 1s linear infinite;
                    margin: 0 auto 1rem;
                "></div>
                <p style="margin: 0; color: #333; font-size: 14px;">${message}</p>
            </div>
            <style>
                @keyframes spin {
                    0% { transform: rotate(0deg); }
                    100% { transform: rotate(360deg); }
                }
            </style>
        </div>
    `;

    $("body").append(loaderHtml);
}

function hideLoader() {
    $("#app-loader-overlay").fadeOut(300, function () {
        $(this).remove();
    });
}

function getErrorMessage(error) {
    if (!error) return "An error occurred. Please try again.";

    // Handle specific error statuses
    if (error.status === 419) {
        return "Your session has expired. Please reload the page to continue.";
    }
    if (error.status === 401) {
        return "Your session has expired. Please login again.";
    }
    if (error.status === 403) {
        return "You do not have permission to perform this action.";
    }

    try {
        if (error.responseJSON?.message) {
            return error.responseJSON.message;
        }
        if (error.responseJSON?.errors) {
            return Object.values(error.responseJSON.errors).flat().join(", ");
        }
    } catch (e) {
        // Continue to fallback
    }

    return "An error occurred. Please try again.";
}

function handleError(error) {
    console.error("Error:", error);

    const errorMessage = getErrorMessage(error);
    const errorTitle = error?.status ? `Error ${error.status}` : "Error";

    // Handle CSRF token mismatch (419)
    if (error.status === 419) {
        Swal.fire({
            icon: "warning",
            title: "Session Expired",
            text: errorMessage,
            confirmButtonText: "Reload Page",
            allowOutsideClick: false,
        }).then(() => {
            location.reload();
        });
        return;
    }

    // Handle unauthorized (401)
    if (error.status === 401) {
        Swal.fire({
            icon: "error",
            title: "Unauthorized",
            text: errorMessage,
            confirmButtonText: "Login",
            allowOutsideClick: false,
        }).then(() => {
            window.location.href = BASE_URL + "login";
        });
        return;
    }

    // Handle forbidden (403)
    if (error.status === 403) {
        Swal.fire({
            icon: "error",
            title: "Access Denied",
            text: errorMessage,
        });
        return;
    }

    // Handle not found (404)
    if (error.status === 404) {
        Swal.fire({
            icon: "error",
            title: "Not Found",
            text: "The requested record was not found.",
        });
        return;
    }

    // Default error handling
    Swal.fire({
        icon: "error",
        title: errorTitle,
        text: errorMessage,
    });
}

function cancelAmendmentProxy(thisElem) {
    const btnCancel = $(thisElem);
    const proxyId = $(thisElem).attr("data-proxy-id");

    // Hide the modal before showing SweetAlert
    $("#assignProxyAmendmentModal").modal("hide");

    // First show reason selection
    Swal.fire({
        title: "Select Cancellation Reason",
        text: "Please select a reason for cancelling this proxy:",
        icon: "question",
        input: "select",
        inputOptions: {
            quorum: "Quorum",
            encoding_error: "Encoding Error",
        },
        inputPlaceholder: "Select a reason",
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Continue",
        cancelButtonText: "Cancel",
        inputValidator: (value) => {
            if (!value) {
                return "Please select a reason for cancellation";
            }
        },
    }).then((reasonResult) => {
        if (reasonResult.isConfirmed && reasonResult.value) {
            const selectedReason = reasonResult.value;
            const reasonText =
                selectedReason === "quorum" ? "Quorum" : "Encoding Error";

            // Then show remarks input
            Swal.fire({
                title: "Add Remarks",
                html: `<div class="text-left mb-3">
                 <p><strong>Reason:</strong> ${reasonText}</p>
                 <label for="swal-input1" class="form-label">Remarks (Optional):</label>
               </div>`,
                input: "textarea",
                inputPlaceholder:
                    "Enter additional remarks or details about the cancellation...",
                inputAttributes: {
                    "aria-label": "Remarks",
                    maxlength: 500,
                    rows: 4,
                },
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Continue",
                cancelButtonText: "Back",
                allowOutsideClick: false,
                focusConfirm: false,
            }).then((remarksResult) => {
                if (remarksResult.isConfirmed) {
                    const remarks = remarksResult.value || "";

                    // Final confirmation with all details
                    Swal.fire({
                        title: "Confirm Proxy Cancellation",
                        html: `<div class="text-left">
                     <p><strong>Reason:</strong> ${reasonText}</p>
                     <p><strong>Remarks:</strong> ${remarks || "None"}</p>
                     <br>
                     <p class="text-warning">Are you sure you want to cancel this proxy?</p>
                   </div>`,
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#d33",
                        cancelButtonColor: "#6c757d",
                        confirmButtonText: "Yes, Cancel Proxy",
                        cancelButtonText: "No, Keep Proxy",
                    }).then((confirmResult) => {
                        if (confirmResult.isConfirmed) {
                            $.ajax({
                                url:
                                    BASE_URL +
                                    `admin/amendment-proxy/${proxyId}/cancel`,
                                method: "POST",
                                dataType: "json",
                                data: {
                                    reason: selectedReason,
                                    remarks: remarks,
                                    _token: $('meta[name="csrf-token"]').attr(
                                        "content",
                                    ),
                                },
                                beforeSend: function () {
                                    btnCancel.attr("disabled", true);
                                },
                                complete: function () {
                                    btnCancel.attr("disabled", false);
                                },
                                success: function (data) {
                                    Swal.fire({
                                        icon: "success",
                                        title: "Proxy Cancelled Successfully",
                                        html: `<div class="text-left">
                             <p>${data.message}</p>
                             <p><strong>Reason:</strong> ${reasonText}</p>
                             ${remarks ? `<p><strong>Remarks:</strong> ${remarks}</p>` : ""}
                           </div>`,
                                    }).then(() => {
                                        loadStockholders();
                                        $("#assignProxyAmendmentModal").modal(
                                            "hide",
                                        );
                                    });
                                },
                                error: function (xhr) {
                                    handleError(xhr);
                                },
                            });
                        }
                    });
                } else if (
                    remarksResult.dismiss === Swal.DismissReason.cancel
                ) {
                    // User clicked "Back", restart the process
                    cancelAmendmentProxy(thisElem);
                }
            });
        }
    });
}
