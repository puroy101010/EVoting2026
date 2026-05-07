// Activity Log Management Module
const ActivityLogManager = {
    // Configuration
    config: {
        baseUrl: BASE_URL,
        endpoints: {
            load: "admin/activity/load",
            filter: "admin/activity/filter",
            loginDetails: "admin/login/attempt/details",
            otpOverride: "admin/otp/override",
        },
        selectors: {
            filterBox: "#filter_box",
            filterForm: "#filter_form",
            activityTable: "#activityTable tbody",
            recordSummary: ".record-summary",
            activePage: "#active_page",
        },
    },

    // Initialize the module
    init() {
        this.bindEvents();
        this.initializeFilters();
        this.loadActivities();
        this.loadFilterData();
    },

    // Bind all event handlers
    bindEvents() {
        $(document).ready(() => {
            $(`${this.config.selectors.filterBox} select`).chosen({
                width: "100%",
            });
        });

        $(document).on(
            "change",
            `${this.config.selectors.filterForm} select`,
            (e) => {
                this.loadActivities(
                    $(e.target).hasClass("active-page") ? false : true
                );
            }
        );

        $(document).on(
            "click",
            ".btn-navigator",
            this.handleNavigation.bind(this)
        );
        $(document).on("click", "#show_filter", this.showFilter.bind(this));
        $(document).on("click", "#hide_filter", this.hideFilter.bind(this));
        $(document).on(
            "click",
            "#btn_filter_reset",
            this.resetFilter.bind(this)
        );
        $(document).on(
            "click",
            ".failed-login-account",
            this.showLoginDetails.bind(this)
        );
        $(document).on(
            "click",
            ".btn-override-otp",
            this.overrideOtp.bind(this)
        );
    },

    // Initialize filters
    initializeFilters() {
        $(`${this.config.selectors.filterBox} select`).chosen({
            width: "100%",
        });
    },

    // Handle pagination navigation
    handleNavigation(e) {
        e.preventDefault();
        const href = $(e.currentTarget).attr("href");

        if (!href) return false;

        const page = href.substring(href.lastIndexOf("=") + 1);
        this.loadActivities(page);
    },

    // Get user display name based on role
    getUserDisplayName(activity) {
        const userMap = {
            superadmin: activity.adminName,
            admin: activity.adminName,
            stockholder: activity.stockholder,
            "corp-rep": activity.corpRep,
            "non-member": activity.nonmemberName,
        };
        return userMap[activity.role] || "";
    },

    // Parse activity data from JSON string
    parseActivityData(data) {
        if (!data) return null;

        if (typeof data === "string") {
            try {
                return JSON.parse(data);
            } catch (e) {
                return null;
            }
        }
        return data;
    },

    // Generate change details HTML
    generateChangeDetails(activityData) {
        if (!activityData || typeof activityData !== "object") return "";

        let details = "";
        Object.entries(activityData).forEach(([field, changes]) => {
            if (
                changes &&
                typeof changes === "object" &&
                "old" in changes &&
                "new" in changes
            ) {
                details += `<br> &nbsp;&nbsp;• <span class="text-muted">${field}</span>: <span class="text-danger">${changes.old}</span> → <span class="text-success">${changes.new}</span>`;
            }
        });
        return details;
    },

    // Activity description generators
    activityDescriptions: {
        "00001": () => "Logged in",
        // "00002": (activity) =>
        //   `Added a new stockholder '<span class="font-weight-bold">${activity.user?.stockholder?.stockholder}</span>' with account number <span class="font-weight-bold">${activity.user?.stockholder?.accountNo}</span>`,

        "00002": (activity) => activity.remarks,
        "00004": (activity) =>
            `Downloaded proxy form ${activity.proxyFormNoKey}`,
        "00005": (activity) =>
            `Account <span class="font-weight-bold">${activity.accountNo}</span> with email address <span class="font-weight-bold">${activity.email}</span> requested OTP. <a href="#" class="btn-override-otp badge badge-info">Override OTP</a>`,
        "00006": (activity) =>
            `Account number <span class="font-weight-bold text-primary failed-login-account">${activity.accountNo}</span> with email address <span class="font-weight-bold text-primary failed-login-account">${activity.email}</span> tried to log in. Invalid email or account number.`,
        "00007": (activity) =>
            `Account number <span class="font-weight-bold">${activity.accountNo}</span> with email address <span class="font-weight-bold">${activity.email}</span> entered an invalid OTP.`,
        "00025": (activity) => activity.remarks,
        "00026": (activity, manager) => {
            let desc = `Edited candidate — <span class="font-weight-bold">${
                activity.candidateName || ""
            }</span>`;
            const activityData = manager.parseActivityData(activity.data);
            desc += manager.generateChangeDetails(activityData);
            return desc;
        },
        "00027": (activity) => `${activity.remarks}`,
        "00028": (activity, manager) => {
            let desc = `Edited non-member account — <span class="font-weight-bold">${
                activity.remarks || ""
            }</span>`;
            const activityData = manager.parseActivityData(activity.data);
            desc += manager.generateChangeDetails(activityData);
            return desc;
        },

        "00029": (activity, manager) => {
            let desc = `Edited stockholder details— <span class="font-weight-bold">${
                activity.accountNo ||
                activity.user?.stockholder?.accountNo ||
                ""
            } ${activity.user?.stockholder?.stockholder || ""}</span>`;
            const activityData = manager.parseActivityData(activity.data);
            desc += manager.generateChangeDetails(activityData);
            return desc;
        },

        "00042": (activity, manager) => {
            let desc = `Edited admin account — <span class="font-weight-bold">${
                activity.remarks || ""
            }</span>`;
            const activityData = manager.parseActivityData(activity.data);
            desc += manager.generateChangeDetails(activityData);
            return desc;
        },

        "00047": (activity) =>
            `Generated a Stockholder Online <a target="_blank" href="${BASE_URL}admin/ballots/preview/${activity.ballotId}">ballot</a> form.`,
        "00073": (activity) =>
            `Viewed <a target="_blank" href="${BASE_URL}admin/ballots/preview/${activity.ballotId}">ballot</a> form`,
        "00094": (activity) =>
            `Successfully submitted a Stockholder Online <a target="_blank" href="${BASE_URL}admin/ballots/preview/${activity.ballotId}">ballot</a> form.`,
        "00095": (activity) =>
            `Successfully submitted a proxy <a target="_blank" href="${BASE_URL}admin/ballots/preview/${activity.ballotId}">ballot</a> form.`,
        "00096": (activity) =>
            `Added a new agenda <br><span class="font-italic">"${activity.remarks}"</span>`,
        "00098": (activity) =>
            `Added a new stock  -- <span class="font-weight-bold">${activity.remarks}</span>`,

        "00069": (activity) =>
            `Added a new agenda <br><span class="font-italic">"${activity.remarks}"</span>`,
        "00097": (activity, manager) => {
            let desc = "Edited agenda";
            const activityData = manager.parseActivityData(activity.data);
            desc += manager.generateChangeDetails(activityData);
            return desc;
        },

        "00070": (activity, manager) => {
            let desc = "Edited amendment";
            const activityData = manager.parseActivityData(activity.data);
            desc += manager.generateChangeDetails(activityData);
            return desc;
        },

        "00113": (activity, manager) => {
            let desc = activity.remarks;
            const activityData = manager.parseActivityData(activity.data);
            desc += manager.generateChangeDetails(activityData);
            return desc;
        },

        "000117": (activity) => activity.remarks,
        "000052": (activity) => activity.remarks,
        "000053": (activity) => activity.remarks,

        "00126": (activity) =>
            `Account number <span class="font-weight-bold text-primary failed-login-account">${activity.accountNo}</span> with email address <span class="font-weight-bold text-primary failed-login-account">${activity.email}</span> tried to log in. Email is inactive or not registered.`,
    },

    // Get activity description
    getActivityDescription(activity) {
        const generator = this.activityDescriptions[activity.activityCode];
        if (generator) {
            return generator(activity, this);
        }
        return activity.remarks || activity.activity || "Unknown activity";
    },
    // Load activities with pagination
    loadActivities(page = null) {
        const loadPageNo =
            page ||
            $(`${this.config.selectors.filterForm} [name=active_page]`).val();
        const data = $(this.config.selectors.filterForm).serialize();

        $.ajax({
            url: `${this.config.baseUrl}${this.config.endpoints.load}?page=${loadPageNo}`,
            method: "GET",
            dataType: "json",
            data: data,
            success: (response) => this.handleActivitiesSuccess(response),
            error: (xhr) => this.handleError(xhr),
        });
    },

    // Handle successful activities load
    handleActivitiesSuccess(data) {
        this.updatePagination(data.data);
        this.renderActivities(data.data.data);
        this.updateSummary(data.data);
    },

    // Handle AJAX errors
    handleError(xhr) {
        const statusHandlers = {
            400: () => alert(xhr.responseJSON?.message || "Bad Request"),
            401: () => alert(UNAUTHORIZED),
            403: () => alert(FORBIDDEN),
            419: () => alert(SESSION_TIMEOUT),
            500: () => alert(SERVER_ERROR),
        };

        const handler = statusHandlers[xhr.status];
        if (handler) {
            handler();
        } else {
            alert("An error occurred while processing your request.");
        }
    },

    // Update pagination controls
    updatePagination(paginationData) {
        const { current_page, last_page, next_page_url, prev_page_url } =
            paginationData;

        $(".page-item .btn-prev").attr("href", prev_page_url);
        $(".page-item .btn-next").attr("href", next_page_url);
        $(".active-page").text(`Page ${current_page} of ${last_page}`);

        let pages = "";
        for (let i = 1; i <= last_page; i++) {
            pages += `<option ${
                current_page == i ? "selected" : ""
            } value="${i}">${i}</option>`;
        }

        $(`${this.config.selectors.filterForm} [name=active_page]`)
            .html(pages)
            .trigger("chosen:updated");
    },

    // Show filter panel
    showFilter() {
        $("#show_filter").hide();
        $("#hide_filter").show();
        $("#filter_box").slideToggle();
    },

    // Hide filter panel
    hideFilter() {
        $("#hide_filter").hide();
        $("#show_filter").show();
        $("#filter_box").slideToggle();
    },

    // Reset filter form
    resetFilter() {
        $("#filter_form")[0].reset();
        $("#filter_form select").trigger("chosen:updated");
        this.loadActivities(true);
    },

    // Render activities table
    renderActivities(activities) {
        if (!activities.length) {
            $(this.config.selectors.activityTable).html(
                '<tr><td class="text-center text-muted" colspan="7">No data</td></tr>'
            );
            return;
        }

        let record = "";
        activities.forEach((activity) => {
            console.log(activity);
            const user = this.getUserDisplayName(activity);
            const accountNo = this.getAccountNumber(activity);
            const desc = this.getActivityDescription(activity);
            const icon = this.getActivityIcon(activity.severity);

            record += this.buildTableRow(activity, user, accountNo, desc, icon);
        });

        $(this.config.selectors.activityTable).html(record);
    },

    // Get account number from activity
    getAccountNumber(activity) {
        if (activity.user?.stockholder?.accountNo) {
            return activity.user.stockholder.accountNo;
        }
        if (activity.user?.stockholder_account?.accountKey) {
            return activity.user.stockholder_account.accountKey;
        }
        return "";
    },

    // Get activity icon based on severity
    getActivityIcon(severity) {
        return severity === "info"
            ? '<i class="fa fa-exclamation-circle text-primary" aria-hidden="true"></i>'
            : '<i class="fa fa-exclamation-circle text-danger" aria-hidden="true"></i>';
    },

    // Build table row HTML
    buildTableRow(activity, user, accountNo, desc, icon) {
        return `
      <tr data-id="${activity.logId}">
        <td class="td-padding text-nowrap">${icon} &nbsp;${activity.activityCode}</td>
        <td class="td-padding">${activity.createdAtFormatted}</td>
        <td class="td-padding">${user}</td>
        <td class="td-padding">${accountNo}</td>
        <td class="td-padding">${desc}</td>
        <td class="td-padding">${activity.category}</td>
        <td class="td-padding">${activity.action}</td>
      </tr>
    `;
    },
    // Update record summary
    updateSummary(data) {
        const showingRecordFrom = `${data.from || "0"} to ${data.to || "0"}`;
        const summary =
            data.total === 0
                ? "No record found"
                : `Showing records from ${showingRecordFrom} of <span class="font-weight-bold">${data.total}</span> records`;

        $(this.config.selectors.recordSummary).html(summary);
    },

    // Load filter data for dropdowns
    loadFilterData() {
        $.ajax({
            url: `${this.config.baseUrl}${this.config.endpoints.filter}`,
            method: "GET",
            dataType: "json",
            success: (data) => this.handleFilterDataSuccess(data),
            error: (xhr) => this.handleError(xhr),
        });
    },

    // Handle successful filter data load
    handleFilterDataSuccess(data) {
        // Populate users dropdown
        let userList = '<option value="">ALL</option>';
        data.users.forEach((user) => {
            userList += `<option value="${user.id}">${user.displayName}</option>`;
        });
        $("#filter_box [name=user]").html(userList).trigger("chosen:updated");

        // Populate categories dropdown
        let categoryList = '<option value="">ALL</option>';
        Object.values(data.categories).forEach((category) => {
            categoryList += `<option value="${category}">${category}</option>`;
        });
        $("#filter_box [name=category]")
            .html(categoryList)
            .trigger("chosen:updated");

        // Populate actions dropdown
        let actionList = '<option value="">ALL</option>';
        Object.values(data.actions).forEach((action) => {
            actionList += `<option value="${action}">${action}</option>`;
        });
        $("#filter_box [name=action]")
            .html(actionList)
            .trigger("chosen:updated");
    },

    // Show login details modal
    showLoginDetails(e) {
        const activityId = $(e.currentTarget).closest("tr").attr("data-id");
        const text = $(e.currentTarget).closest("td").text();

        $(".message-alert").html(text);

        $.ajax({
            url: `${this.config.baseUrl}${this.config.endpoints.loginDetails}`,
            method: "GET",
            dataType: "json",
            data: { id: activityId },
            success: (data) => this.handleLoginDetailsSuccess(data),
            error: (xhr) => this.handleError(xhr),
        });
    },

    // Handle successful login details load
    handleLoginDetailsSuccess(data) {
        let accountDetails = "";

        data.forEach((account) => {
            const accountInfo = this.getAccountInfo(account);
            accountDetails += this.buildAccountRow(accountInfo);
        });

        $("#login_details_modal tbody").html(
            data.length === 0
                ? '<tr><td class="text-muted text-center" colspan="5">No data</td></tr>'
                : accountDetails
        );

        $("#login_details_modal").modal("show");
    },

    // Get account information based on role
    getAccountInfo(account) {
        const accountInfo = {
            accountNo: "",
            stockholder: "",
            email: "",
            corpRep: "",
            corpRepEmail: "",
        };

        switch (account.role) {
            case "stockholder":
                accountInfo.accountNo =
                    account.stockholder_account?.stockholder?.accountNo || "";
                accountInfo.stockholder =
                    account.stockholder?.stockholder || "";
                accountInfo.email = account.email || "";
                break;

            case "corp-rep":
                accountInfo.accountNo =
                    account.stockholder_account?.stockholder?.accountNo || "";
                accountInfo.stockholder =
                    account.stockholder_account?.stockholder?.stockholder || "";
                accountInfo.email = account.email || "";
                accountInfo.corpRep =
                    account.stockholder_account?.corpRep || "";
                accountInfo.corpRepEmail = account.email || "";
                break;

            case "non-member":
                accountInfo.accountNo =
                    account.non_member_account?.nonmemberAccountNo || "";
                accountInfo.stockholder = `${
                    account.non_member_account?.firstName || ""
                } ${account.non_member_account?.lastName || ""}`.trim();
                break;
        }

        return accountInfo;
    },

    // Build account row HTML
    buildAccountRow(accountInfo) {
        return `
      <tr>
        <td>${accountInfo.accountNo}</td>
        <td>${accountInfo.stockholder}</td>
        <td>${accountInfo.email}</td>
        <td>${accountInfo.corpRep}</td>
        <td>${accountInfo.corpRepEmail}</td>
      </tr>
    `;
    },

    // Override OTP for an account
    overrideOtp(e) {
        const activityId = $(e.currentTarget).closest("tr").attr("data-id");

        $.ajax({
            url: `${this.config.baseUrl}${this.config.endpoints.otpOverride}`,
            method: "POST",
            dataType: "json",
            data: { id: activityId },
            success: (data) => this.handleSuccess(data),
            error: (xhr) => this.handleError(xhr),
        });
    },

    // Handle success responses
    handleSuccess(data) {
        if (typeof handleSuccess === "function") {
            handleSuccess(data);
        } else {
            alert(data.message || "Operation completed successfully");
        }
    },
};

// Initialize the ActivityLogManager when the document is ready
$(document).ready(() => {
    ActivityLogManager.init();
});
