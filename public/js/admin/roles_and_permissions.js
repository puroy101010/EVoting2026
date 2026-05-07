function getRoleMenuConfig($trigger) {
    const userCount = parseInt($trigger.data("role-users"), 10) || 0;
    return [
        {
            action: handleUserAssignedToThisRole,
            label: "View Users",
            icon: "fas fa-users",
            disabled: false,
            badge: {
                text: userCount.toString(),
                class: userCount > 0 ? "info" : "secondary"
            }
        },
        {
            action: handleViewPermissions,
            label: "View Permissions",
            icon: "fas fa-shield-alt",
            disabled: false,
            separator: false
        }
    ];
}

// Helper function to get module-specific icons
function getModuleIcon(module) {
    const iconMap = {
        candidate: "fas fa-user-tie",
        agenda: "fas fa-clipboard-list",
        amendment: "fas fa-file-contract",
        stockholder: "fas fa-users",
        "non member": "fas fa-user-friends",
        "admin account": "fas fa-user-shield",
        "superadmin account": "fas fa-crown",
        user: "fas fa-user",
        role: "fas fa-user-tag",
        permission: "fas fa-key",
        setting: "fas fa-cog",
        report: "fas fa-chart-bar"
    };
    return iconMap[module.toLowerCase()] || "fas fa-cube";
}

function generateRolePermissionRows(permissions, data) {
    const moduleMap = {};
    permissions.forEach((p) => {
        const module = p.module || "Other";
        if (!moduleMap[module]) {
            moduleMap[module] = {
                permissions: [],
                description: p.description || ""
            };
        }
        moduleMap[module].permissions.push(p);
        if (p.description && p.description.length > moduleMap[module].description.length) {
            moduleMap[module].description = p.description;
        }
    });

    let content = `<div class="corporate-form-section"><h6 class="corporate-section-title"><i class="fas fa-shield-alt mr-2"></i>Role Permissions</h6><div class="corporate-permissions-groups">`;

    Object.keys(moduleMap).sort().forEach((module) => {
        const moduleData = moduleMap[module];
        const moduleIcon = getModuleIcon(module);
        content += `<div class="corporate-permission-group"><div class="corporate-permission-group-header"><div class="d-flex align-items-center justify-content-between"><div class="d-flex align-items-center"><span class="corporate-permission-group-icon mr-3"><i class="${moduleIcon}"></i></span><div><h6 class="corporate-permission-group-title">${module.toUpperCase()}</h6></div></div><div class="corporate-permission-count"><span class="badge badge-light">${moduleData.permissions.length} permissions</span></div></div></div><div class="corporate-permission-group-items">`;

        moduleData.permissions.forEach((permission) => {
            const isGranted = permission.has_role !== false;
            const permissionId = `perm_${permission.id || Math.random().toString(36).substr(2, 9)}`;
            content += `<div class="corporate-permission-item"><div class="d-flex align-items-center justify-content-between"><div class="corporate-permission-info"><div class="corporate-permission-name">${permission.name}</div><div class="corporate-permission-desc">${permission.description || "No description available"}</div></div><div class="corporate-permission-control"><label class="corporate-modern-toggle" for="${permissionId}"><input type="checkbox" id="${permissionId}" ${isGranted ? "checked" : ""} data-permission-id="${permission.id}" data-permission-name="${permission.name}" data-role-id="${data.recordId}"><span class="corporate-toggle-slider"><span class="corporate-toggle-button"></span></span></label></div></div></div>`;
        });
        content += `</div></div>`;
    });
    content += `</div></div>`;
    return content;
}


function generateRoleUserRows(users) {
    const list = Array.isArray(users) ? users : (users && users.users ? users.users : []);
    const count = list.length;

    const makeInitials = (u) => {
        const f = (u.firstName || u.firstname || "").trim();
        const l = (u.lastName || u.lastname || "").trim();
        return (f.charAt(0) + l.charAt(0)).toUpperCase() || (u.email ? u.email.charAt(0).toUpperCase() : "U");
    };

    let content = `<div class="corporate-form-section">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="corporate-section-title mb-0">
                <i class="fas fa-users mr-2"></i>Users 
                <span class="badge badge-primary ml-2">${count}</span>
            </h6>
            <div class="flex-grow-1 ml-3" style="max-width:280px;">
                <div class="input-group input-group-sm">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                    </div>
                    <input type="text" class="form-control corporate-user-filter border-left-0" 
                           placeholder="Search users..." aria-label="Search users" />
                </div>
            </div>
        </div>
        <div class="corporate-users-list" data-user-count="${count}">`;

    if (count === 0) {
        content += `<div class="corporate-user-item text-center text-muted py-4">
            <i class="fas fa-user-slash fa-2x mb-3 opacity-50"></i>
            <div>No users assigned to this role.</div>
        </div>`;
    } else {
        list.forEach((user, index) => {
            const fullName = `${(user.firstName || user.firstname || "").trim()} ${(user.lastName || user.lastname || "").trim()}`.trim() || "(No Name)";
            const email = (user.email || "").trim();
            const initials = makeInitials(user);
            const role = user.role || "";
            const status = user.isActive;
            const lastLogin = user.last_login ? new Date(user.last_login).toLocaleDateString() : "";

            content += `<div class="corporate-user-item py-3 px-3 border-bottom" 
                            data-user-name="${fullName.toLowerCase()}" 
                            data-user-email="${email.toLowerCase()}"
                            data-user-role="${role.toLowerCase()}">
                <div class="d-flex align-items-center w-100">
                    <div class="corporate-user-avatar mr-3 d-flex align-items-center justify-content-center text-uppercase flex-shrink-0" 
                         style="width:42px;height:42px;border-radius:50%;background:linear-gradient(135deg, var(--corporate-primary, #0d47a1) 0%, #1976d2 100%);color:white;font-weight:700;font-size:14px;box-shadow:0 2px 8px rgba(13,71,161,0.2);">
                        ${initials}
                    </div>
                    <div class="flex-grow-1 min-w-0">
                        <div class="d-flex align-items-center justify-content-between"> 
                            <div class="corporate-user-name font-weight-bold text-truncate mb-1" 
                                 style="max-width:220px;color:#2c3e50;">${fullName}</div>
                            <div class="d-flex align-items-center">
                                ${status === 1 ?
                    '<span class="badge badge-success badge-pill px-2">Active</span>' :
                    '<span class="badge badge-secondary badge-pill px-2">Inactive</span>'
                }
                            </div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="small text-muted text-truncate" style="max-width:280px;">
                                ${email ? `<i class="fas fa-envelope mr-1"></i>${email}` : '<i class="fas fa-envelope-slash mr-1"></i>No email'}
                            </div>
                            ${lastLogin ? `<div class="small text-muted">Last: ${lastLogin}</div>` : ''}
                        </div>
                        ${role ? `<div class="small mt-1"><span class="badge badge-outline-primary">${role}</span></div>` : ''}
                        ${user.id ? `<div class="small text-muted mt-1">ID: ${user.id}</div>` : ''}
                    </div>
                    <div class="ml-2 text-muted">
                        <i class="fas fa-chevron-right" style="font-size:12px;"></i>
                    </div>
                </div>
            </div>`;
        });
    }

    content += `</div></div>`;
    return content;
}

function initRoleUserListFilter() {
    const input = document.querySelector('#corporateRightSlideModal .corporate-user-filter');
    if (!input) return;

    const items = Array.from(document.querySelectorAll('#corporateRightSlideModal .corporate-user-item[data-user-name]'));
    const usersList = document.querySelector('#corporateRightSlideModal .corporate-users-list');

    input.addEventListener('input', () => {
        const query = input.value.trim().toLowerCase();
        let visibleCount = 0;

        items.forEach(item => {
            if (!query) {
                item.style.display = '';
                visibleCount++;
                return;
            }

            const name = item.getAttribute('data-user-name') || '';
            const email = item.getAttribute('data-user-email') || '';
            const role = item.getAttribute('data-user-role') || '';

            const matches = name.includes(query) || email.includes(query) || role.includes(query);
            item.style.display = matches ? '' : 'none';
            if (matches) visibleCount++;
        });

        // Show "no results" message if needed
        const noResults = usersList.querySelector('.no-search-results');
        if (visibleCount === 0 && query && items.length > 0) {
            if (!noResults) {
                const noResultsDiv = document.createElement('div');
                noResultsDiv.className = 'no-search-results text-center text-muted py-4';
                noResultsDiv.innerHTML = `
                    <i class="fas fa-search fa-2x mb-3 opacity-50"></i>
                    <div>No users found matching "${query}"</div>
                `;
                usersList.appendChild(noResultsDiv);
            }
        } else if (noResults) {
            noResults.remove();
        }
    });
}

function handleViewPermissions(data) {
    corporateModal.init({
        title: `Permissions`,
        subtitle: `Role: ${data.recordName}`,
        icon: "fas fa-shield-alt",
        showLoader: false,
        showFooter: true,
        size: "lg",
        backdrop: true,
        keyboard: true,
        showCloseButton: true,
        closeButtonAction: () => corporateModal.close(),
        data: data,
        footer: {
            buttons: [
                {
                    label: "Close",
                    class: "btn corporate-btn-secondary",
                    id: "closeModalFooterButton",
                    icon: "fas fa-times mr-1"
                },
                {
                    label: "Save Changes",
                    class: "btn corporate-btn-primary",
                    id: "savePermissionsButton",
                    icon: "fas fa-save mr-1",
                    action: () => savePermissionChanges(data.recordId)
                }
            ]
        },
        content: () => {
            $.ajax({
                url: `${BASE_URL}admin/role/permissions?id=${data.recordId}`,
                method: "GET",
                dataType: "json",
                success: (response) => {
                    corporateModal.hideLoading();
                    corporateModal.setHtmlContent(generateRolePermissionRows(response.permissions, data));
                }
            });
        }
    });
    corporateModal.open();
}

function handleUserAssignedToThisRole(data) {
    corporateModal.init({
        title: `${data.recordName.charAt(0).toUpperCase() + data.recordName.slice(1)} Users`,
        subtitle: `List of users assigned to this role`,
        icon: "fas fa-shield-alt",
        showLoader: false,
        showFooter: true,
        size: "lg",
        backdrop: true,
        keyboard: true,
        showCloseButton: true,
        closeButtonAction: () => corporateModal.close(),
        data: data,
        footer: {
            buttons: [
                {
                    label: "Close",
                    class: "btn corporate-btn-secondary",
                    id: "closeModalFooterButton",
                    icon: "fas fa-times mr-1"
                }
            ]
        },
        content: () => {
            $.ajax({
                url: `${BASE_URL}admin/role/${data.recordId}/users`,
                method: "GET",
                dataType: "json",
                success: (response) => {
                    corporateModal.hideLoading();
                    corporateModal.setHtmlContent(generateRoleUserRows(response));
                    initRoleUserListFilter();
                }
            });
        }
    });
    corporateModal.open();
}

function initPermissionToggles() {
    const toggles = document.querySelectorAll('#corporateRightSlideModal input[type="checkbox"][data-permission-id]');
    toggles.forEach((toggle) => {
        toggle.addEventListener("change", function () {
            const permissionId = this.getAttribute("data-permission-id");
            const permissionName = this.getAttribute("data-permission-name");
            const isEnabled = this.checked;
            const item = this.closest(".corporate-permission-item");
            if (item) {
                if (isEnabled) {
                    item.classList.add("permission-enabled");
                    item.classList.remove("permission-disabled");
                } else {
                    item.classList.add("permission-disabled");
                    item.classList.remove("permission-enabled");
                }
            }
            console.log(`Permission ${permissionName} (ID: ${permissionId}) ${isEnabled ? "enabled" : "disabled"}`);
        });
    });
}

function savePermissionChanges(roleId) {
    Swal.fire({
        title: "Save Permission Changes?",
        text: "Are you sure you want to save these permission changes?",
        icon: "question",
        showCancelButton: true,
        confirmButtonColor: "var(--corporate-primary)",
        cancelButtonColor: "#6c757d",
        confirmButtonText: "Yes, save changes!"
    }).then((result) => {
        if (result.isConfirmed) {
            actuallySavePermissionChanges(roleId);
            return;
        }
        corporateModal.close();
    });
}

function actuallySavePermissionChanges(roleId) {
    const footerButtons = document.querySelectorAll("#corporateRightSlideModal button");
    const toggles = document.querySelectorAll('#corporateRightSlideModal input[type="checkbox"][data-permission-id]');
    const permissions = [];

    toggles.forEach((toggle) => {
        const permissionId = toggle.getAttribute("data-permission-id");
        const isGranted = toggle.checked;
        permissions.push({
            permission_id: permissionId,
            granted: isGranted
        });
    });

    const requestData = {
        _token: $('meta[name="csrf-token"]').attr("content") || document.querySelector('input[name="_token"]')?.value,
        role_id: roleId,
        permissions: permissions
    };

    return $.ajax({
        url: `${BASE_URL}admin/role/` + roleId,
        method: "PUT",
        data: requestData,
        dataType: "json",
        statusCode: {
            200: (response) => {
                Swal.fire({
                    title: "Success",
                    text: response.message,
                    icon: "success",
                    confirmButtonColor: "var(--corporate-primary)"
                }).then(() => corporateModal.close());
            },
            204: (response) => {
                Swal.fire({
                    title: "No Changes",
                    text: response.message,
                    icon: "info",
                    confirmButtonColor: "var(--corporate-primary)"
                }).then(() => corporateModal.close());
            },
            403: (response) => {
                Swal.fire({
                    title: "Forbidden",
                    text: response.message,
                    icon: "error",
                    confirmButtonColor: "var(--corporate-primary)"
                }).then(() => corporateModal.close());
            },
            422: (xhr) => {
                Swal.fire({
                    title: "Validation Error",
                    text: getErrorMessage(xhr, "Please check your input."),
                    icon: "error",
                    confirmButtonColor: "var(--corporate-primary)"
                });
            },
            500: (xhr) => {
                Swal.fire({
                    title: "Server Error",
                    text: getErrorMessage(xhr, "An unexpected error occurred."),
                    icon: "error",
                    confirmButtonColor: "var(--corporate-primary)"
                });
            }
        }
    });
}

function handleUsers(data) {
    if (typeof Swal !== "undefined") {
        Swal.fire({
            title: "View Users",
            text: `View users with role: ${data.recordName} (ID: ${data.recordId})`,
            icon: "info",
            confirmButtonText: "OK",
            confirmButtonColor: "var(--corporate-primary)"
        });
    } else {
        alert(`View Users with Role: ${data.recordName} (ID: ${data.recordId})`);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    $(".show-role-menu").on("click", function (e) {
        e.preventDefault();
        e.stopPropagation();
        const $btn = $(this);
        const menuConfig = getRoleMenuConfig($btn);
        if (typeof CtxMenu !== "undefined") {
            CtxMenu.show({
                actions: menuConfig,
                data: {
                    recordId: $btn.data("item-id") || "",
                    recordName: $btn.data("item-name") || ""
                },
                trigger: this
            });
        } else {
            console.error("CtxMenu is not available");
        }
    });
});
