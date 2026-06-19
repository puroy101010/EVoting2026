@extends('layouts.admin')

@section('css')
<link rel="stylesheet" type="text/css" href="{{asset('css/admin/corporate-ui.css')}}?v={{ time() }}">
@endsection

@section('content')
<div class="content-wrapper">
  <section class="content-header">
    <div class="container-fluid">
      <!-- Modern Page Header -->
      <div class="corporate-page-header ultra-compact">
        <div class="d-flex justify-content-between align-items-start flex-wrap">
          <div>
            <h1 class="corporate-page-title ultra-compact">Online Accounts Management</h1>
            <nav aria-label="breadcrumb">
              <ol class="breadcrumb corporate-breadcrumb-modern ultra-compact">
                <li class="breadcrumb-item"><a href="{{asset('admin')}}">Dashboard</a></li>
                <li class="breadcrumb-item active" aria-current="page">Online Accounts</li>
              </ol>
            </nav>
          </div>

        </div>
      </div>
      <div class="corporate-table-container ultra-compact">
        <div class="table-responsive">
          <table id="onlineAccountsTable" class="table corporate-table ultra-compact">
            <thead class="text-nowrap">
              <tr>
                <th width="5%" class="th-padding">#</th>
                <th width="25%" class="th-padding">Full Name</th>
                <th width="25%" class="th-padding">Email</th>
                <th width="15%" class="th-padding">Actions</th>
              </tr>
            </thead>
            <tbody>
              @forelse ($onlineAccounts as $email => $accountName)
              <tr>
                <td class="td-padding">{{ $loop->iteration }}</td>
                <td class="full-name td-padding">
                  {{ $accountName }}
                </td>
                <td class="email td-padding">{{ $email }}</td>
                <td class="td-padding">
                  <button class="btn btn-sm btn-light ultra-compact btn-update-account"
                    data-email="{{ $email }}"
                    data-name="{{ $accountName }}"
                    data-toggle="modal"
                    data-target="#updateAccountModal">
                    <i class="fas fa-edit mr-1"></i> Update
                  </button>
                  <button class="btn btn-sm btn-light ultra-compact btn-view-stocks"
                    data-email="{{ $email }}"
                    data-toggle="modal"
                    data-target="#viewStocksModal">
                    Stockholder Online
                  </button>
                  <button class="btn btn-sm btn-light ultra-compact btn-view-proxy"
                    data-email="{{ $email }}"
                    data-toggle="modal"
                    data-target="#viewProxyModal">
                    Proxy
                  </button>
                </td>
              </tr>
              @empty
              <tr>
                <td colspan="4" class="corporate-empty-state">
                  <i class="fas fa-users"></i>
                  <h4>No Accounts Found</h4>
                  <p>No online accounts available.</p>
                </td>
              </tr>
              @endforelse
            </tbody>

          </table>
        </div>
      </div>

    </div>
</div>
</div>
</section>
<a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top"><i class="fas fa-chevron-up"></i></a>
</div>


<!-- View Stocks Modal -->
<div class="modal fade" id="viewStocksModal" size="lg">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title">Stockholder Online - <span id="modalEmailTitle"></span></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm corporate-table ultra-compact" id="stocksTable">
            <thead class="text-nowrap">
              <tr>
                <th width="40%" class="th-padding">Account</th>
                <th width="40%" class="th-padding">Stockholder</th>
                <th width="20%" class="th-padding">Role</th>
              </tr>
            </thead>
            <tbody id="stocksTableBody">
              <!-- Stocks data will be populated here -->
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>
<!-- END VIEW STOCKS MODAL -->

<!-- View Proxy Modal -->
<div class="modal fade" id="viewProxyModal" size="lg">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title">Proxy - <span id="modalProxyEmailTitle"></span></h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <div class="modal-body">
        <div class="table-responsive">
          <table class="table table-sm corporate-table ultra-compact" id="proxyTable">
            <thead class="text-nowrap">
              <tr>
                <th width="40%" class="th-padding">Account</th>
                <th width="40%" class="th-padding">Proxy Name</th>
                <th width="20%" class="th-padding">Role</th>
              </tr>
            </thead>
            <tbody id="proxyTableBody">
              <!-- Proxy data will be populated here -->
            </tbody>
          </table>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">
          <i class="fas fa-times mr-1"></i> Close
        </button>
      </div>
    </div>
  </div>
</div>
<!-- END VIEW PROXY MODAL -->

<!-- Update Account Modal -->
<div class="modal fade" id="updateAccountModal">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header corporate-modal-header ultra-compact">
        <h4 class="modal-title">Update Account</h4>
        <button type="button" class="close" data-dismiss="modal">&times;</button>
      </div>

      <form id="updateAccountForm">
        <div class="modal-body">
          <div class="form-group">
            <label for="updateAccountEmail" class="font-weight-bold">Email Address</label>
            <input type="email" class="form-control" id="updateAccountEmail">
            <small class="form-text text-muted">Email address cannot be changed.</small>
          </div>
          <div class="form-group">
            <label for="updateAccountName" class="font-weight-bold">Full Name</label>
            <input type="text" class="form-control" id="updateAccountName" required>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn corporate-btn-secondary ultra-compact" data-dismiss="modal">
            <i class="fas fa-times mr-1"></i> Cancel
          </button>
          <button type="submit" class="btn corporate-btn-primary ultra-compact">
            <i class="fas fa-save mr-1"></i> Save Changes
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
<!-- END UPDATE ACCOUNT MODAL -->

<script>





  $(document).ready(function() {
    $('[data-toggle="tooltip"]').tooltip();
  });

  // Handle View Stocks button click
  $(document).on('click', '.btn-view-stocks', function() {
    const email = $(this).attr('data-email');

    $('#modalEmailTitle').text(email);
    $('#stocksTableBody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

    // Fetch stockholder online data from API
    $.ajax({
      url: "{{ route('online-accounts.stocks', ['email' => ':email']) }}".replace(':email', email),
      method: 'GET',
      dataType: 'json',
      beforeSend: function() {
        // Show loading spinner
        $('#stocksTableBody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');
      },
      success: function(response) {
        if (response && Object.keys(response).length > 0) {
          let stocksHtml = '';

          // Iterate through each email group
          Object.entries(response).forEach(([emailKey, stockholderGroups]) => {
            // Iterate through each stockholder group
            Object.entries(stockholderGroups).forEach(([stockholderName, accountGroups]) => {
              // Iterate through each account number group
              Object.entries(accountGroups).forEach(([accountNo, details]) => {
                // Iterate through each detail record
                details.forEach(detail => {
                  stocksHtml += `
                    <tr>
                      <td class="td-padding">${detail.accountKey}</td>
                      <td class="td-padding">${detail.stockholderName || 'N/A'}</td>
                      <td class="td-padding"><span class="badge badge-info">${detail.emailRole}</span></td>
                    </tr>
                  `;
                });
              });
            });
          });

          $('#stocksTableBody').html(stocksHtml);
        } else {
          $('#stocksTableBody').html('<tr><td colspan="3" class="text-center">No stockholder accounts found.</td></tr>');
        }
      },
     
      statusCode: {
        404: function() {
          $('#stocksTableBody').html('<tr><td colspan="3" class="text-center">No stockholder accounts found.</td></tr>');
        },
        403: function() {
          $('#stocksTableBody').html('<tr><td colspan="3" class="text-center">You do not have permission to view stockholder accounts.</td></tr>');
        },
        500: function() {
          $('#stocksTableBody').html('<tr><td colspan="3" class="text-center">An error occurred while fetching stockholder accounts.</td></tr>');
        }
      }
    });
  });

  // Handle Update Account button click
  $(document).on('click', '.btn-update-account', function() {
    const email = $(this).attr('data-email');
    const name = $(this).attr('data-name');
  

    $('#updateAccountEmail').val(email);
    $('#updateAccountName').val(name);

    $('#updateAccountForm').attr('data-email', email);
  });

  // Handle Update Account form submission
  $('#updateAccountForm').on('submit', function(e) {
    e.preventDefault();

    const email = $('#updateAccountEmail').val();
    const name = $('#updateAccountName').val();
    const oldEmail = $(this).attr('data-email');

    if (!name.trim()) {
      alert('Please enter a full name.');
      return;
    }

    $.ajax({
      url: "{{ route('online-accounts.update', ['online_account' => ':email']) }}".replace(':email', oldEmail),
      method: 'PUT',
      data: { email: email, name: name },
      beforeSend: function() {
        showLoader('Updating account...');
      },
      success: function(response) {
        $('#updateAccountModal').modal('hide');
        location.reload();
      },
      complete: function() {
        hideLoader();
      },


      statusCode: {
        404: function() {
          alert('Account not found.');
        },
        403: function() {
          alert('You do not have permission to update this account.');
        },
        500: function() {
          alert('An error occurred while updating the account.');
        }
      }
    });

    alert('Update for ' + email + ' with name: ' + name);
    $('#updateAccountModal').modal('hide');
  });

  // Handle View Proxy button click
  $(document).on('click', '.btn-view-proxy', function() {
    const email = $(this).attr('data-email');

    $('#modalProxyEmailTitle').text(email);
    $('#proxyTableBody').html('<tr><td colspan="3" class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</td></tr>');

    // Fetch proxy data from API
    $.ajax({
      url: "{{ route('online-accounts.proxies', ['email' => ':email']) }}".replace(':email', email),
      method: 'GET',
      dataType: 'json',
      success: function(response) {
        if (response && Object.keys(response).length > 0) {
          let proxyHtml = '';

          // Iterate through proxy data by account key
          Object.entries(response).forEach(([accountKey, proxyList]) => {
            proxyList.forEach(proxy => {
              proxyHtml += `
                <tr>
                  <td class="td-padding">${proxy.accountKey}</td>
                  <td class="td-padding">${proxy.accountName || 'N/A'}</td>
                  <td class="td-padding"><span class="badge badge-primary">${proxy.proxyRole}</span></td>
                </tr>
              `;
            });
          });

          $('#proxyTableBody').html(proxyHtml);
        } else {
          $('#proxyTableBody').html('<tr><td colspan="3" class="text-center">No proxy accounts found.</td></tr>');
        }
      },
      error: function(xhr) {
        // Show dummy proxy data on error for demo/testing
        const dummyProxy = `
          <tr>
            <td class="td-padding">0002-1</td>
            <td class="td-padding">Maria Santos</td>
            <td class="td-padding"><span class="badge badge-primary">Board of Director Proxy</span></td>
          </tr>
          <tr>
            <td class="td-padding">0003-1</td>
            <td class="td-padding">Juan Dela Cruz</td>
            <td class="td-padding"><span class="badge badge-primary">Amendment Proxy</span></td>
          </tr>
        `;
        $('#proxyTableBody').html(dummyProxy);
      }
    });
  });
</script>



@endsection