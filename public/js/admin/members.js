$(document).ready(function () {
  $(document).on("shown.bs.modal", "#assignProxyBodModal", function () {
    $("#assignProxyBodModal [name=proxyFormNo]").focus();
  });

  $(document).on("shown.bs.modal", "#addMemberModal", function () {
    if ($("#addMemberModal [name=stockholder]").val() === "") {
      $("#addMemberModal [name=stockholder]").focus();
    }
  });

  $(document).on("click", "#btn_export_record", function () {
    location.href = BASE_URL + "admin/stockholder/export";
  });

  $(document).on("click", ".btn-assign-amendment-proxy", function () {
    assign_amendment_proxy($(this));
  });

  load_stockholder();
  load_filter_data_users();
  load_option_assignees();

  // initialize chosen select
  $("select").chosen({ width: "100%" });
  $("#form_edit_member select").chosen({ width: "100%" });
  $("#proxy_assigner_select, #spa_assigner_select").chosen({
    width: "100%",
    allow_single_deselect: true,
  });
  $("[name=assigner]").chosen({ width: "100%", allow_single_deselect: true });

  //done 2023-08-28
  $(document).on("submit", "#form_add_member", function (e) {
    e.preventDefault();
    $.ajax({
      url: BASE_URL + "admin/stockholder",
      method: "POST",
      dataType: "json",
      data: $("#form_add_member").serialize(),
      beforeSend: function () {
        $("#addMemberModal")
          .find("#btn_submit_member")
          .text("Submitting . . . ")
          .attr("disabled", true);
      },
      complete: function () {
        $("#addMemberModal")
          .find("#btn_submit_member")
          .text("Submit")
          .attr("disabled", false);
      },

      success: function (data) {
        load_stockholder();
        Swal.fire({
          icon: "success",
          title: "Success",
          text: data.message,
        }).then(() => {
          $("#addMemberModal").modal("hide");
        });
      },
      error: function (xhr) {
        handleError(xhr);
      },
    });
  });

  //done 2023-08-28
  $(document).on("change", "#filter_form select", function () {
    if ($(this).attr("name") !== "active_page") {
      $("#filter_form [name=active_page]").val(1).trigger("chosen:updated");
    }

    load_stockholder();
  });

  // //done 2023-08-28
  $(document).on("click", ".btn-proxyholder-bod", function () {
    let id = $(this).closest("tr").attr("data-account-id");
    load_proxyhoder_bod(id);
  });

  $(document).on("click", ".btn-proxyholder-amendment", function () {
    let id = $(this).closest("tr").attr("data-account-id");
    load_proxyhoder_amendment(id);
  });

  // done 2023-08-29
  $(document).on("click", "#show_filter", function (e) {
    $(this).hide();

    $("#hide_filter").show();

    $("#filter_box").slideToggle();
  });

  // done 2023-08-29
  $(document).on("click", "#hide_filter", function () {
    $(this).hide();
    $("#show_filter").show();
    $("#filter_box").slideToggle();
  });

  // done 2023-08-29
  $(document).on("click", "#btn_filter_reset", function () {
    reset_filter();
    load_stockholder();
  });

  // done 2023-08-28
  $(document).on("click", ".btn-navigator", function (e) {
    e.preventDefault();

    if ($(this).attr("href") === "") {
      return false;
    }

    let url = $(this)
      .attr("href")
      .substring($(this).attr("href").lastIndexOf("=") + 1);

    load_stockholder(url);
  });

  // done 2023-08-28
  $(document).on("submit", "#form_edit_member", function (e) {
    e.preventDefault();

    let submitBtn = $(this).find("#btn_submit_edit_member");

    $.ajax({
      url: BASE_URL + "admin/stockholder/" + $(this).find("[name=id]").val(),
      method: "PUT",
      dataType: "json",
      data: $(this).serialize(),
      beforeSend: function () {
        submitBtn.attr("disabled", true);
      },
      complete: function () {
        submitBtn.attr("disabled", false);
      },
      success: function (data) {
        Swal.fire({ icon: "success", title: "Success", text: data.message })
          .then(() => {
            load_stockholder();
          })
          .then(() => {
            $("#edit_member_modal").modal("hide");
          });
      },
      error: function (xhr) {
        handleError(xhr);
      },
    });
  });

  // done 2023-08-23
  function getAccountNoDetails(accountNo) {
    $.ajax({
      url: BASE_URL + "admin/stockholder/" + accountNo,
      method: "GET",
      dataType: "json",
      data: { account_no: accountNo },
      beforeSend: function () {
        $("#form_add_member").trigger("reset");
      },
      error: function (xhr) {
        handleError(xhr);
      },

      statusCode: {
        200: function (data) {
          if (data["stockholder"] !== null) {
            setFieldsForExistingStockholder(data["stockholder"]);
            setFieldForCorpRep(data["stockholder"]["accountType"]);
            populateSuffixOptions(data["suffixes"]);
            setFieldAccountTypeVoteInPerson(
              data["stockholder"]["accountType"],
              data["stockholder"]["voteInPerson"]
            );
          } else {
            setFieldsForNewStockholder(accountNo);
            setFieldForCorpRep();
            populateSuffixOptions();
            setFieldAccountTypeVoteInPerson();
          }

          $("#addMemberModal select").trigger("chosen:updated");

          $("#addMemberModal").modal("show");
        },
      },
    });
  }

  // done 2023-08-28
  $(document).on("click", "#btn_add_member", function () {
    const accountNo = prompt("Account number:");

    if (accountNo === null) {
      return false;
    }

    if (accountNo.trim() === "") {
      return alert("Account number is required");
    }

    getAccountNoDetails(accountNo);
  });

  // done 2023-08-2023
  $(document).on("change", "#addMemberModal [name=account_type]", function () {
    let accountType = $(this).children("option:selected").val();

    setFieldForCorpRep(accountType);
    setFieldAccountTypeVoteInPerson(accountType);

    $("#addMemberModal select").trigger("chosen:updated");
  });

  // done 2023-08-28
  function setFieldsForExistingStockholder(stockholder) {
    $("#addMemberModal [name=stockholder]")
      .val(stockholder["stockholder"])
      .attr("readonly", true);
    $("#addMemberModal [name=account_number]")
      .val(stockholder["accountNo"])
      .attr("readonly", true);
    $("#addMemberModal [name=email]")
      .val(stockholder["user"]["email"])
      .attr("readonly", true);

    $(".form-corp-only").toggle(stockholder["accountType"] !== "indv");
  }

  // done 2023-08-27
  function setFieldsForNewStockholder(accountNo) {
    $("#addMemberModal [name=stockholder]").attr("readonly", false);
    $("#addMemberModal [name=account_number]")
      .attr("readonly", false)
      .val(accountNo);
    $("#addMemberModal [name=email]").attr("readonly", false);
  }

  // done 2023-08-27
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

  // done 2023-08-28
  function setFieldForCorpRep(accountType = null) {
    let isIndividual = accountType === "indv";
    let isDisabled = isIndividual ? true : false;

    $(".form-corp-only").toggle(!isIndividual).find("input").val("").prop({
      readonly: isDisabled,
      disabled: isDisabled,
    });
  }

  // done 2023-08-27
  function setFieldAccountTypeVoteInPerson(
    accountType = null,
    voteInPerson = null
  ) {
    let voteInPersonField = $("#addMemberModal [name=vote_in_person]");

    if (accountType === null) {
      voteInPersonField
        .val("")
        .find("option")
        .removeAttr("selected")
        .attr("disabled", false);

      $("#addMemberModal [name=account_type]")
        .val("")
        .find("option")
        .removeAttr("selected")
        .attr("disabled", false);
    } else {
      if (voteInPerson === null) {
        if (accountType === "indv") {
          voteInPersonField
            .find("option[value=stockholder]")
            .attr("disabled", false)
            .prop("selected", true)
            .siblings()
            .removeAttr("selected")
            .attr("disabled", true);

          return;
        }

        voteInPersonField
          .val("")
          .find("option")
          .attr("disabled", false)
          .removeAttr("selected");

        voteInPersonField.trigger("chosen:updated");
      } else {
        voteInPersonField
          .find("option[value=" + voteInPerson + "]")
          .attr("disabled", false)
          .prop("selected", true)
          .siblings()
          .removeAttr("selected")
          .attr("disabled", true);
        $(
          "#addMemberModal [name=account_type] option[value=" +
          accountType +
          "]"
        )
          .attr("disabled", false)
          .prop("selected", true)
          .siblings()
          .removeAttr("selected")
          .attr("disabled", true);
      }
    }
  }

  $(document).on("submit", "#import_member", function (e) {
    e.preventDefault();

    let formData = new FormData();

    formData.append("excel_member", $("[name=excel_member]")[0].files[0]);

    $.ajax({
      url: BASE_URL + "stockholder/import",
      method: "POST",
      dataType: "json",
      contentType: false,
      cache: false,
      processData: false,
      data: formData,

      xhr: function () {
        var xhr = new window.XMLHttpRequest();
        xhr.upload.addEventListener(
          "progress",
          function (evt) {
            if (evt.lengthComputable) {
              var percentComplete = evt.loaded / evt.total;
              percentComplete = parseInt(percentComplete * 100);
              $("#myprogress").text(percentComplete + "%");
              $("#myprogress").css("width", percentComplete + "%");
            }
          },
          false
        );
        return xhr;
      },

      beforeSend: function () {
        $("#btn_upload").text("Uploading . . . ").attr("disabled", true);
      },
      complete: function () {
        $("#btn_upload").text("Upload").attr("disabled", false);
      },
      success: function (data) {
        load_stockholder();
        Swal.fire({
          icon: "success",
          title: "Uploaded!",
          text: data.message,
        });
      },
      error: function (xhr) {
        handleError(xhr);
      },
    });
  });
});

// done 2023-08-27
function load_option_assignees() {
  $.ajax({
    url: BASE_URL + "admin/stockholder/assignee",
    dataType: "json",
    success: function (data) {
      let assigneeList = '<option value=""></option>';
      for (let assignee of data["assignees"]) {
        assigneeList += `<option ${assignee["disabled"]} value="${assignee["id"]}">${assignee["accountNo"]} ${assignee["stockholder"]} </option>`;
      }

      $("[name=assignee]").html(assigneeList).trigger("chosen:updated");
    },
    error: function (xhr) {
      handleError(xhr);
    },
  });
}

// done 2023-08-28
function load_filter_data_users() {
  $.ajax({
    url: BASE_URL + "admin/stockholder/user",
    dataType: "json",
    success: function (data) {
      let users = '<option value="">All</option>';
      for (let userId in data.users) {
        if (data.users.hasOwnProperty(userId)) {
          let currentUser = data.users[userId];
          users += `<option value="${currentUser["account_no"]}">${currentUser["account_key"]} ${currentUser["full_name"]} </option>`;
        }
      }
      $("#filter_form [name=accounts]").html(users).trigger("chosen:updated");
    },
    error: function (xhr) {
      handleError(xhr);
    },
  });
}

// done 2023-08-28
function editMember(id) {
  $.ajax({
    url: BASE_URL + `admin/stockholder/${id}/edit`,
    method: "GET",
    dataType: "json",
    beforeSend: function () {
      $("#form_edit_member").trigger("reset");
    },

    success: function (data) {
      try {
        $("#edit_member_modal [name=id]").val(id);

        $(".stock-details-wrapper").toggle(data["role"] === "corp-rep");

        if (data["role"] === "corp-rep") {
          $("#edit_member_modal .modal-title").text("EDIT STOCK DETAILS");

          let account = data["stockholder_account"];
          let stockholder = account["stockholder"];

          $("#edit_member_modal [name=stockholder]")
            .val(stockholder["stockholder"])
            .attr("readonly", false);
          $("#edit_member_modal [name=account_number]")
            .val(stockholder["accountNo"])
            .attr("readonly", true);
          $("#edit_member_modal [name=email]")
            .val(stockholder["user"]["email"])
            .attr("readonly", true);
          $("#edit_member_modal [name=account_type]")
            .attr("disabled", true)
            .find("option[value=" + stockholder["accountType"] + "]")
            .prop("selected", true)
            .attr("disabled", false)
            .siblings()
            .removeAttr("selected")
            .attr("disabled", true);
          $("#edit_member_modal [name=vote_in_person]")
            .attr("disabled", true)
            .find("option[value=" + stockholder["voteInPerson"] + "]")
            .attr("disabled", false)
            .prop("selected", true)
            .siblings()
            .removeAttr("selected")
            .attr("disabled", true);

          // set fields for stockholer accounts
          $("#edit_member_modal [name=suffix]")
            .html("<option>" + account["suffix"] + "</option>")
            .attr("disabled", true);
          $("#edit_member_modal [name=delinquent]")
            .attr("disabled", false)
            .find("option[value=" + account["isDelinquent"] + "]")
            .attr("disabled", false)
            .prop("selected", true)
            .siblings()
            .removeAttr("selected")
            .attr("disabled", false);
          $("#edit_member_modal [name=corp_rep]")
            .val(account["corpRep"])
            .attr("disabled", stockholder["accountType"] === "indv");
          $("#edit_member_modal [name=corp_rep_email]")
            .val(data["email"])
            .attr("disabled", stockholder["accountType"] === "indv");
          $("#edit_member_modal [name=auth_signatory]")
            .val(account["authSignatory"])
            .attr("disabled", stockholder["accountType"] === "indv");
        } else {
          $("#edit_member_modal .modal-title").text("EDIT STOCKHOLDER");
          $("#edit_member_modal [name=stockholder]")
            .val(data["stockholder"]["stockholder"])
            .attr("readonly", false);
          $("#edit_member_modal [name=account_number]")
            .val(data["stockholder"]["accountNo"])
            .attr("readonly", true);
          $("#edit_member_modal [name=email]")
            .val(data["email"])
            .attr("readonly", false);
          $(
            "#edit_member_modal [name=account_type] option[value=" +
            data["stockholder"]["accountType"] +
            "]"
          )
            .prop("selected", true)
            .attr("disabled", false)
            .siblings()
            .removeAttr("selected")
            .attr("disabled", true);
          $("#edit_member_modal [name=vote_in_person]")
            .attr("disabled", data["stockholder"]["accountType"] === "indv")
            .find('option[value="' + data["stockholder"]["voteInPerson"] + '"]')
            .prop("selected", true)
            .attr("disabled", false)
            .siblings()
            .removeAttr("selected")
            .attr("disabled", data["stockholder"]["accountType"] === "indv");

          // clear fields for stockholer accounts
          $("#edit_member_modal [name=suffix]")
            .html("<option></option>")
            .attr("disabled", true);
          $("#edit_member_modal [name=delinquent]")
            .attr("disabled", true)
            .find('option[value=""]')
            .attr("disabled", false)
            .prop("selected", true)
            .siblings()
            .removeAttr("selected")
            .attr("disabled", true);
          $("#edit_member_modal [name=corp_rep]")
            .val("")
            .attr("disabled", true);
          $("#edit_member_modal [name=corp_rep_email]")
            .val("")
            .attr("disabled", true);
          $("#edit_member_modal [name=auth_signatory]")
            .val("")
            .attr("disabled", true);
        }

        $("#edit_member_modal select").trigger("chosen:updated");

        $("#edit_member_modal").modal("show");
      } catch (err) {
        alert(err);
      }
    },

    error: function () {
      handleError(xhr);
    },
  });
}
// done 2023-08-29
function handleDisplayStockholder(user, counter) {
  let accountType =
    user["stockholder"]["accountType"] === "indv" ? "INDV" : "CORP.";

  return `<tr data-id="${user["id"]}" class="row-stockholder">
                    <td class="td-padding">#${counter}</td>
                    <td class="accountKey td-padding text-nowrap td-account-no">${user["stockholder"]["accountNo"]
    }</td>
                    <td class="td-padding td-stockholder"> ${user["stockholder"]["stockholder"]
    }</td>
                    <td class="td-padding">${user["email"] || ""} </td>
                    <td class="stockholder td-padding"></td>
                    <td class="email td-padding"></td>
                    <td class="td-padding">${accountType}</td>
                
                    <td class="status"></td>
                    <td class="text-right">
                    <button class="btn btn-sm btn-success btn-edit-member" onclick="editMember(${user["id"]
    })"><i class="far fa-edit text-white"></i></button>
                    </td>
                </tr>`;
}

// done 2023-08-29
function handleDisplayCorpRep(user, counter) {
  let status =
    user["stockholder_account"]["isDelinquent"] == "1"
      ? '<span class="badge badge-danger">Delinquent</span>'
      : '<span class="badge badge-success">Active</span>';

  let stockholder = user["stockholder_account"]["stockholder"];

  return `<tr data-id="${user["id"]}" data-account-id="${user["stockholder_account"]["accountId"]
    }" >
                    <td class="td-padding">#${counter}</td>
                    <td class="accountKey td-padding text-nowrap td-account-no">${user["stockholder_account"]["accountKey"]
    }</td>
                    <td class="td-padding td-stockholder">${stockholder["stockholder"]
    }</td>
                    <td class="td-padding">${stockholder["user"]["email"] || ""
    }</td>
                    <td class="stockholder td-padding td-stockholder">${user["stockholder_account"]["corpRep"] || ""
    }</td>
                    <td class="email td-padding">${user["email"] || "---"}</td>
                    <td class="td-padding">${stockholder["accountType"] === "indv" ? "INDV" : "CORP"
    }</td>
                    <td class="status">${status}</td>
                    <td class="text-right">
                            <div class="btn-group" role="group" aria-label="Button group with nested dropdown">
                            <button type="button" class="btn btn-success btn-sm btn-edit-member" onclick="editMember(${user["id"]
    })"><i class="far fa-edit text-white"></i> </button>
                            <div class="btn-group" role="group">
                                <button id="btnGroupDrop1" type="button" class="btn btn-success dropdown-toggle btn-sm" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    Proxy
                                </button>
                                <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">
                                <a class="dropdown-item btn-proxyholder-bod" href="#">BOD</a>
                                <a class="dropdown-item btn-proxyholder-amendment" href="#">Amendment</a>
                                <a class="dropdown-item btn-proxyholder-history" href="#" data-id="${user["stockholder_account"]["accountId"]}" data-account-no="${user["stockholder_account"]['accountKey']}" data-proxy-type="BOD">BOD History</a>
                                <a class="dropdown-item btn-proxyholder-history" href="#" data-id="${user["stockholder_account"]["accountId"]}" data-account-no="${user["stockholder_account"]['accountKey']}" data-proxy-type="Amendment">Amendment History</a>
                                </div>
                            </div>
                            </div>
                        </td></tr>`;
}

// done 2023-08-29
function displayStockholder(data) {
  let record = "";
  let counter = data["data"]["from"];

  let currentPage = data["data"]["current_page"];
  let lastPage = data["data"]["last_page"];
  let nextPageUrl = data["data"]["next_page_url"];
  let prevPageUrl = data["data"]["prev_page_url"];

  $(".page-item .btn-prev").attr("href", prevPageUrl);
  $(".page-item .btn-next").attr("href", nextPageUrl);
  $(".active-page").text(`Page ${currentPage} of ${lastPage}`);

  for (let user of data["data"]["data"]) {
    if (user["role"] === "stockholder") {
      record += handleDisplayStockholder(user, counter);
      counter++;
      continue;
    }

    record += handleDisplayCorpRep(user, counter);
    counter++;
  }

  $("#memberTable tbody").html(
    data["data"].length === 0
      ? '<tr><td class="text-center text-muted" colspan="9">No data</td></tr>'
      : record
  );

  let showingRecordFrom = `${data["data"]["from"] || "0"} to ${data["data"]["to"] || "0"
    }`;

  let summary =
    data["data"]["total"] === 0
      ? "No record found"
      : `Showing records from ${showingRecordFrom} of <span class="font-weight-bold">${data["data"]["total"]}</span> records`;

  $(".record-summary").html(summary);

  let pages = "";

  for (let i = 1; i <= data["data"]["last_page"]; i++) {
    pages += `<option ${data["data"]["current_page"] == i ? "selected" : ""
      } value="${i}">${i}</option>`;
  }

  $("#filter_form [name=active_page]").html(pages).trigger("chosen:updated");
}

// done 2023-08-29
function load_stockholder(page = null) {
  try {
    let filterData = $("#filter_form").serialize();
    let loadPageNo = page || $("#filter_form [name=active_page]").val();
    $.ajax({
      url: BASE_URL + `admin/stockholder/load?page=${loadPageNo}`,
      method: "GET",
      dataType: "json",
      data: filterData,
      beforeSend: function () {
        $("#memberTable tbody").html(
          '<tr><td class="text-center text-muted" colspan="10">Loading</td></tr>'
        );
      },
      complete: function () {
        $("#memberTable tbody").css("opacity", 1);
      },

      success: function (data) {
        displayStockholder(data);
      },
      error: function (xhr) {
        handleError(xhr);
      },
    });
  } catch (err) {
    alert(err);
  }
}

// done 2023-08-28
function showProxyBoad(data) {
  let assignor = "";
  let assignee = "";
  let objAssignor = data["proxy_board"]["assignor"];
  let objAssignee = data["proxy_board"]["assignee"];

  $("#btnCancelBodProxy").attr("data-id", data["proxy_board"]["proxyBodId"]);

  switch (objAssignor["role"]) {
    case "stockholder":
      assignor = `${objAssignor["stockholder"]["accountNo"]} ${objAssignor["stockholder"]["stockholder"]} <span class="badge badge-success">stockholder</span>`;
      break;
    case "corp-rep":
      assignor = `${objAssignor["stockholder_account"]["accountKey"]} ${objAssignor["stockholder_account"]["corpRep"]} <span class="badge badge-success">corporate representative</span>`;
      break;
  }

  switch (objAssignee["role"]) {
    case "stockholder":
      assignee = `${objAssignee["stockholder"]["accountNo"]} ${objAssignee["stockholder"]["stockholder"]} <span class="badge badge-success">stockholder</span>`;
      break;
    case "corp-rep":
      assignee = `${objAssignee["stockholder_account"]["accountKey"]} ${objAssignee["stockholder_account"]["stockholder"]["stockholder"]} |  ${objAssignee["stockholder_account"]["corpRep"]} <span class="badge badge-success">corporate representative</span>`;
      break;
    case "non-member":
      assignee = `${objAssignee["non_member_account"]["nonmemberAccountNo"]} ${objAssignee["non_member_account"]["firstName"]} ${objAssignee["non_member_account"]["lastName"]} <span class="badge badge-success">non-member</span>`;
      break;
  }

  $("#assignProxyForm .assignee-details .assignee-details-stock").text(
    data.accountKey + " " + data.stockholder.stockholder
  );
  $("#assignProxyForm .assignee-details .assignee-details-form-no").text(
    data["proxy_board"]["proxyBodFormNo"]
  );
  $("#assignProxyForm .assignee-details .assignee-details-assignor").html(
    assignor
  );
  $("#assignProxyForm .assignee-details .assignee-details-assignee").html(
    assignee
  );
}

function showProxyAmendment(data) {
  let assignor = "";
  let assignee = "";
  let objAssignor = data["proxy_amendment"]["assignor"];
  let objAssignee = data["proxy_amendment"]["assignee"];

  $("#btnCancelAmendmentProxy").attr(
    "data-id",
    data["proxy_amendment"]["proxyAmendmentId"]
  );

  switch (objAssignor["role"]) {
    case "stockholder":
      assignor = `${objAssignor["stockholder"]["accountNo"]} ${objAssignor["stockholder"]["stockholder"]} <span class="badge badge-success">stockholder</span>`;
      break;
    case "corp-rep":
      assignor = `${objAssignor["stockholder_account"]["accountKey"]} ${objAssignor["stockholder_account"]["corpRep"]} <span class="badge badge-success">corporate representative</span>`;
      break;
  }

  assignee = `${objAssignee["non_member_account"]["nonmemberAccountNo"]} ${objAssignee["non_member_account"]["firstName"]} ${objAssignee["non_member_account"]["lastName"]} <span class="badge badge-success">non-member</span>`;

  $("#assignProxyFormAmendment .assignee-details .assignee-details-stock").text(
    data.accountKey + " " + data.stockholder.stockholder
  );
  $(
    "#assignProxyFormAmendment .assignee-details .assignee-details-form-no"
  ).text("A-" + data["proxy_amendment"]["proxyAmendmentFormNo"]);
  $(
    "#assignProxyFormAmendment .assignee-details .assignee-details-assignor"
  ).html(assignor);
  $(
    "#assignProxyFormAmendment .assignee-details .assignee-details-assignee"
  ).html(assignee);
}

// done 2023-08-28
function showAssignBodForm(id, data) {
  $("#assignProxyForm")[0].reset();
  $("#assignProxyForm select").trigger("chosen:updated");

  let assignor = `<option value="${data["stockholder"]["userId"]}">${data["stockholder"]["stockholder"]}<option>`;

  $("#assignProxyForm .account-to-assign").val(
    data.accountKey + " " + data.stockholder.stockholder
  );

  if (data["stockholder"]["accountType"] == "corp") {
    assignor = "<option></option>";
    assignor += `<option value="${data["stockholder"]["userId"]}"> ${data["stockholder"]["accountNo"]}  ${data["stockholder"]["stockholder"]} (SH/CS)</option>`;

    for (let account of data["stockholder"]["stockholder_accounts"]) {
      assignor += `<option ${account["corpRep"] !== null ? "" : "disabled"
        } value="${account["userId"]}"> ${account["accountKey"]} ${data["stockholder"]["stockholder"]
        } | ${account["corpRep"] === null ? "---no corp rep---" : account["corpRep"]
        } (CR)</option>`;
    }
  }

  $("#assignProxyForm [name=accountToAssign]").val(id);
  $("#assignProxyForm [name=assignor]")
    .html(assignor)
    .trigger("chosen:updated");
}

function showAssignAmendmentForm(id, data) {
  $("#assignProxyFormAmendment")[0].reset();
  $("#assignProxyFormAmendment select").trigger("chosen:updated");

  let assignor = `<option value="${data["stockholder"]["userId"]}">${data["stockholder"]["stockholder"]}<option>`;

  $("#assignProxyFormAmendment .account-to-assign").val(
    data.accountKey + " " + data.stockholder.stockholder
  );

  if (data["stockholder"]["accountType"] == "corp") {
    assignor = "<option></option>";
    assignor += `<option value="${data["stockholder"]["userId"]}"> ${data["stockholder"]["accountNo"]}  ${data["stockholder"]["stockholder"]} (SH/CS)</option>`;

    for (let account of data["stockholder"]["stockholder_accounts"]) {
      assignor += `<option ${account["corpRep"] !== null ? "" : "disabled"
        } value="${account["userId"]}"> ${account["accountKey"]} ${data["stockholder"]["stockholder"]
        } | ${account["corpRep"] === null ? "---no corp rep---" : account["corpRep"]
        } (CR)</option>`;
    }
  }

  $("#assignProxyFormAmendment [name=refNo]").val("A-" + data["accountKey"]);
  $("#assignProxyFormAmendment [name=accountToAssign]").val(id);
  $("#assignProxyFormAmendment [name=assignor]")
    .html(assignor)
    .trigger("chosen:updated");
}

// done 2023-08-28
function load_proxyhoder_bod(id) {
  $.ajax({
    url: BASE_URL + "admin/bod-proxy/" + id,
    method: "GET",
    dataType: "json",
    success: function (data) {
      $("#assignProxyForm .assign-stock-form").toggle(
        data["proxy_board"] === null
      );
      $("#assignProxyForm .assignee-details").toggle(
        data["proxy_board"] !== null
      );

      if (data["proxy_board"] !== null) {
        showProxyBoad(data);
        $("#assignProxyBodModal").modal("show");
        return;
      }
      showAssignBodForm(id, data);
      $("#assignProxyBodModal").modal("show");
    },
    error: function (xhr) {
      handleError(xhr);
    },
  });
}

function load_proxyhoder_amendment(id) {
  $.ajax({
    url: BASE_URL + "admin/amendment-proxy/" + id,
    method: "GET",
    dataType: "json",
    success: function (data) {
      $("#assignProxyFormAmendment .assign-stock-form").toggle(
        data["proxy_amendment"] === null
      );
      $("#assignProxyFormAmendment .assignee-details").toggle(
        data["proxy_amendment"] !== null
      );

      if (data["proxy_amendment"] !== null) {
        showProxyAmendment(data);
        $("#assignProxyAmendmentModal").modal("show");
        return;
      }

      showAssignAmendmentForm(id, data);
      $("#assignProxyAmendmentModal").modal("show");
    },
    error: function (xhr) {
      handleError(xhr);
    },
  });
}

// done 2023-08-28
function reset_filter() {
  $("#filter_form [name=active_page]").html('<option value="1">1</option>');
  $("#filter_form")[0].reset();
  $("#filter_form select").trigger("chosen:updated");
}

// done 2023-08-29
function assign_bod_proxy(btn) {
  let assignBtn = $(btn);

  $.ajax({
    url: BASE_URL + "admin/bod-proxy",
    method: "POST",
    dataType: "json",
    data: $("#assignProxyForm").serialize(),
    beforeSend: function () {
      assignBtn.attr("disabled", true);
    },
    complete: function () {
      assignBtn.attr("disabled", false);
    },
    success: function (data) {
      Swal.fire({
        icon: "success",
        title: "Success",
        text: data.message,
      }).then(() => {
        load_stockholder();
        $("#assignProxyBodModal").modal("hide");
      });
    },
    error: function (xhr) {
      handleError(xhr);
    },
  });
}

function assign_amendment_proxy(btn) {
  let assignBtn = btn;

  $.ajax({
    url: BASE_URL + "admin/amendment-proxy",
    method: "POST",
    dataType: "json",
    data: $("#assignProxyFormAmendment").serialize(),
    beforeSend: function () {
      assignBtn.attr("disabled", true);
    },
    complete: function () {
      assignBtn.attr("disabled", false);
    },
    success: function (data) {
      Swal.fire({
        icon: "success",
        title: "Success",
        text: data.message,
      }).then(() => {
        load_stockholder();
        $("#assignProxyAmendmentModal").modal("hide");
      });
    },
    error: function (xhr) {
      handleError(xhr);
    },
  });
}

// done 2023-08-29
function cancel_bod_proxy(thisElem) {
  const btnCancel = $(thisElem);
  const accountId = $(thisElem).attr("data-id");

  // Hide the modal before showing SweetAlert
  $("#assignProxyBodModal").modal("hide");

  // First show reason selection
  Swal.fire({
    title: "Select Cancellation Reason",
    text: "Please select a reason for cancelling this proxy:",
    icon: "question",
    input: "select",
    inputOptions: {
      quorum: "Quorum",
      encoding_error: "Encoding Error"
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
    }
  }).then((reasonResult) => {
    if (reasonResult.isConfirmed && reasonResult.value) {
      const selectedReason = reasonResult.value;
      const reasonText = selectedReason === "quorum" ? "Quorum" : "Encoding Error";

      // Then show remarks input
      Swal.fire({
        title: "Add Remarks",
        html: `<div class="text-left mb-3">
                 <p><strong>Reason:</strong> ${reasonText}</p>
                 <label for="swal-input1" class="form-label">Remarks (Optional):</label>
               </div>`,
        input: "textarea",
        inputPlaceholder: "Enter additional remarks or details about the cancellation...",
        inputAttributes: {
          "aria-label": "Remarks",
          maxlength: 500,
          rows: 4
        },
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Continue",
        cancelButtonText: "Back",
        allowOutsideClick: false,
        focusConfirm: false
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
                url: BASE_URL + `admin/bod-proxy/${accountId}/cancel`,
                method: "POST",
                dataType: "json",
                data: {
                  reason: selectedReason,
                  remarks: remarks,
                  _token: $('meta[name="csrf-token"]').attr('content')
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
                    load_stockholder();
                    $("#assignProxyBodModal").modal("hide");
                  });
                },
                error: function (xhr) {
                  handleError(xhr);
                },
              });
            }
          });
        } else if (remarksResult.dismiss === Swal.DismissReason.cancel) {
          // User clicked "Back", restart the process
          cancel_bod_proxy(thisElem);
        }
      });
    }
  });
}

function cancel_amendment_proxy(thisElem) {
  const btnCancel = $(thisElem);
  const accountId = $(thisElem).attr("data-id");

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
      encoding_error: "Encoding Error"
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
    }
  }).then((reasonResult) => {
    if (reasonResult.isConfirmed && reasonResult.value) {
      const selectedReason = reasonResult.value;
      const reasonText = selectedReason === "quorum" ? "Quorum" : "Encoding Error";

      // Then show remarks input
      Swal.fire({
        title: "Add Remarks",
        html: `<div class="text-left mb-3">
                 <p><strong>Reason:</strong> ${reasonText}</p>
                 <label for="swal-input1" class="form-label">Remarks (Optional):</label>
               </div>`,
        input: "textarea",
        inputPlaceholder: "Enter additional remarks or details about the cancellation...",
        inputAttributes: {
          "aria-label": "Remarks",
          maxlength: 500,
          rows: 4
        },
        showCancelButton: true,
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#d33",
        confirmButtonText: "Continue",
        cancelButtonText: "Back",
        allowOutsideClick: false,
        focusConfirm: false
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
                url: BASE_URL + `admin/amendment-proxy/${accountId}/cancel`,
                method: "POST",
                dataType: "json",
                data: {
                  reason: selectedReason,
                  remarks: remarks,
                  _token: $('meta[name="csrf-token"]').attr('content')
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
                    load_stockholder();
                    $("#assignProxyAmendmentModal").modal("hide");
                  });
                },
                error: function (xhr) {
                  handleError(xhr);
                },
              });
            }
          });
        } else if (remarksResult.dismiss === Swal.DismissReason.cancel) {
          // User clicked "Back", restart the process
          cancel_amendment_proxy(thisElem);
        }
      });
    }
  });
}










