function handleViewProxyHistory(data) {
  corporateModal.init({
    title: `Proxy Assignment History`,
    subtitle: `${data.proxyType} - Account: ${data.accountNo || '-'}`,
    icon: "fas fa-history",
    showLoader: true,
    showFooter: true,
    size: "xl",
    backdrop: true,
    keyboard: true,
    showCloseButton: true,
    closeButtonAction: () => corporateModal.close(),
    data: data,
    footer: {
      buttons: [
        // {
        //   label: "Export History",
        //   class: "btn corporate-btn-info btn-sm",
        //   id: "exportHistoryButton",
        //   icon: "fas fa-download mr-1",
        //   action: () => exportProxyHistory(data.recordId)
        // },
        {
          label: "Close",
          class: "btn corporate-btn-secondary",
          id: "closeModalFooterButton",
          icon: "fas fa-times mr-1",
          action: () => corporateModal.close()
        }
      ]
    },
    content: () => {
      // Simulate API call - replace with actual endpoint
      // setTimeout(() => {
      //     corporateModal.hideLoading();
      //     corporateModal.setHtmlContent(generateBODHistoryRows());
      // }, 800);

      // Uncomment below for actual API integration

      $.ajax({
        url: `${BASE_URL}admin/proxy/history/${data.recordId}`,
        method: "GET",
        data: { proxyType: data.proxyType },
        dataType: "json",
        success: (response) => {
          corporateModal.hideLoading();
          corporateModal.setHtmlContent(generateBODHistoryRows(response));
        },
        error: (xhr) => {
          corporateModal.hideLoading();
          corporateModal.setHtmlContent(`
            <div class="alert alert-danger">
              <i class="fas fa-exclamation-triangle mr-2"></i>
              Failed to load proxy history. Please try again.
            </div>
          `);
          handleError(xhr);
        }
      });

    }
  });
  corporateModal.open();
}


// Helper function for exporting history (placeholder)
function exportProxyHistory(recordId) {
  // Implement export functionality
  console.log('Exporting proxy history for record ID:', recordId);

  // Example implementation
  const link = document.createElement('a');
  link.href = `${BASE_URL}admin/bod-proxy/export-history?account_id=${recordId}`;
  link.download = `proxy_history_${recordId}_${new Date().toISOString().split('T')[0]}.xlsx`;
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
}


function generateBODHistoryRows(proxyHistory) {



  proxyHistory = proxyHistory.history;



  // Helper function to get status badge with modern styling
  function getStatusBadge(status) {
    const statusConfig = {
      'assigned': { class: 'badge-info', icon: 'fas fa-user-check' },
      'cancelled': { class: 'badge-danger', icon: 'fas fa-ban' },
      'verified': { class: 'badge-success', icon: 'fas fa-check-double' },
      'unverified': { class: 'badge-warning', icon: 'fas fa-hourglass-half' }
    };

    const config = statusConfig[status] || { class: 'badge-secondary', icon: 'fas fa-question-circle' };
    return `<span class="badge ${config.class} badge-modern">
              <i class="${config.icon} mr-1"></i>${status}
            </span>`;
  }

  // Helper function to get type badge
  function getTypeBadge(type) {
    const typeConfig = {
      'stockholder': { class: 'badge-primary', abbr: 'SH' },
      'corp-rep': { class: 'badge-info', abbr: 'CR' },
      'non-member': { class: 'badge-warning', abbr: 'NM' },

    };

    const config = typeConfig[type] || { class: 'badge-secondary', abbr: '--' };
    return `<span class="badge ${config.class} badge-sm corporate-type-badge" title="${type}">
              ${config.abbr}
            </span>`;
  }

  // Helper function to format date time
  function formatDateTime(dateTime) {
    if (!dateTime) return '<span class="text-muted">--</span>';
    const date = new Date(dateTime);
    return `<div class="datetime-display">
              <div class="date-part">${date.toLocaleDateString('en-US', {
      year: 'numeric',
      month: 'short',
      day: 'numeric'
    })}</div>
              <div class="time-part text-muted">${date.toLocaleTimeString('en-US', {
      hour: '2-digit',
      minute: '2-digit'
    })}</div>
            </div>`;
  }

  // Generate table rows
  const historyRows = proxyHistory.map((record, index) => {
    return `
      <tr class="corporate-table-row ${record.status.toLowerCase()}">
     
        <td class="account-cell">
          <div class="account-info">
            <span class="account-number font-weight-bold">${record.formNo}</span> <br>
            <small class="text-muted">ID: ${record.id}</small>
          </div>
        </td>
        <td class="person-cell">
          <div class="person-info">
            <div class="person-name">${record.assignorName}</div>
            ${getTypeBadge(record.assignorType)}
          </div>
        </td>
        <td class="person-cell">
          <div class="person-info">
            <div class="person-name">${record.assigneeName}</div>
            ${getTypeBadge(record.assigneeType)}
          </div>
        </td>
        <td class="text-center status-cell">
          ${getStatusBadge(record.status)}
        </td>
        <td class="cancellation-cell">
          <div class="cancellation-content">
            ${record.cancellationReason ?
        `<div class="cancellation-info">
                   <span class="cancellation-reason">${record.cancellationReason}</span>
                   ${record.cancellationBy ?
          `<div class="cancelled-by">
                        <i class="fas fa-user-times text-muted mr-1"></i>
                        <small class="text-muted">${record.cancellationBy}</small>
                      </div>
                      
          ` : ''
        }
                 </div>` :
        '<span class="text-muted">--</span>'
      }
          </div>
        </td>
        <td class="remarks-cell">
          <div class="remarks-content">
            <small class="remarks-text" title="${record.remarks}">
              ${record.remarks || '<em class="text-muted"></em>'}
            </small>
          </div>
        </td>
        <td class="datetime-cell">
          <small class="text-muted">${formatDateTime(record.createdAt)}</small>
        </td>
      </tr>
    `;
  }).join('');

  const content = `
    <div class="corporate-form-section bod-proxy-history">
      <div class="section-header mb-4">
        <h6 class="corporate-section-title">
          <i class="fas fa-history mr-2 text-primary"></i>
          Proxy Assignment History
        </h6>
        <p class="text-muted mb-0">Complete history of Board of Director proxy assignments for this account</p>
      </div>
      
      <!-- Desktop/Large Screen Table -->
      <div class="table-responsive corporate-table-wrapper d-none d-lg-block">
        <table class="table table-hover corporate-table corporate-history-table">
          <thead class="corporate-table-header">
            <tr>
      
              <th width="140">Form No.</th>
              <th width="200">Assignor</th>
              <th width="200">Assignee</th>
              <th width="100" class="text-center">Status</th>
              <th width="140">Cancellation</th>
              <th>Remarks</th>
              <th width="160">Date Created</th>
            </tr>
          </thead>
          <tbody>
            ${historyRows}
          </tbody>
        </table>
      </div>

      <!-- Mobile/Tablet Card Layout -->
      <div class="d-lg-none mobile-cards-container">
        ${proxyHistory.map((record, index) => {
    return `
            <div class="mobile-history-card ${record.status.toLowerCase()}">
              <div class="mobile-card-header">
          
                <div class="mobile-card-account">
                  <span class="account-number-mobile">${record.formNo}</span>
                </div>
                <div class="mobile-card-status">
                  ${getStatusBadge(record.status)}
                </div>
              </div>
              
              <div class="mobile-card-body">
                <div class="mobile-info-row">
                  <div class="mobile-info-item">
                    <label class="mobile-label">Assignor</label>
                    <div class="mobile-value">
                      <span class="person-name-mobile">${record.assignorName}</span>
                      ${getTypeBadge(record.assignorType)}
                    </div>
                  </div>
                  
                  <div class="mobile-info-item">
                    <label class="mobile-label">Assignee</label>
                    <div class="mobile-value">
                      <span class="person-name-mobile">${record.assigneeName}</span>
                      ${getTypeBadge(record.assigneeType)}
                    </div>
                  </div>
                </div>
                
                ${record.cancellationReason ?
        `<div class="mobile-info-row">
                     <div class="mobile-info-item full-width">
                       <label class="mobile-label">Cancellation</label>
                       <div class="mobile-value cancellation-mobile">
                         <span class="mobile-cancellation-reason">${record.cancellationReason}</span>
                         ${record.cancelledBy ?
          `<div class="mobile-cancelled-by">
                              <i class="fas fa-user-times text-muted mr-1"></i>
                              <small class="text-muted">${record.cancelledBy}</small>
                            </div>` : ''
        }
                         ${record.cancelledAt ?
          `<div class="mobile-cancelled-at">
                              <i class="fas fa-clock text-muted mr-1"></i>
                              <small class="text-muted">${formatDateTime(record.cancelledAt).replace(/<[^>]*>/g, '').trim()}</small>
                            </div>` : ''
        }
                       </div>
                     </div>
                   </div>` : ''
      }
                
                <div class="mobile-info-row">
                  <div class="mobile-info-item full-width">
                    <label class="mobile-label">Remarks</label>
                    <div class="mobile-value remarks-mobile">
                      ${record.remarks || '<em class="text-muted">No remarks</em>'}
                    </div>
                  </div>
                </div>
                
                <div class="mobile-info-row">
                  <div class="mobile-info-item full-width">
                    <label class="mobile-label">Date Created</label>
                    <div class="mobile-value">
                      ${formatDateTime(record.createdAt)}
                    </div>
                  </div>
                </div>
              </div>
            </div>
          `;
  }).join('')}
      </div>
      
      <div class="history-summary mt-3">
        <div class="row">
          <div class="col-md-6">
            <small class="text-muted">
              <i class="fas fa-info-circle mr-1"></i>
              Showing ${proxyHistory.length} proxy assignment records
            </small>
          </div>
          <div class="col-md-6 text-right">
            <small class="text-muted">
              Last updated: ${formatDateTime(new Date().toISOString())}
            </small>
          </div>
        </div>
      </div>
    </div>

    <style>
      /* BOD Proxy History Desktop Table Styles */
      .bod - proxy - history.corporate - history - table {
    font- size: 0.875rem;
  min - width: 1000px;
  table - layout: auto;
  width: 100 %;
}
      
      .bod - proxy - history.corporate - table - wrapper {
  max - height: 600px;
  overflow - x: auto;
  overflow - y: auto;
  border: 1px solid #dee2e6;
  border - radius: 8px;
  position: relative;
}
      
      .bod - proxy - history.corporate - table - wrapper:: -webkit - scrollbar {
  width: 8px;
  height: 8px;
}
      
      .bod - proxy - history.corporate - table - wrapper:: -webkit - scrollbar - track {
  background: #f1f1f1;
  border - radius: 4px;
}
      
      .bod - proxy - history.corporate - table - wrapper:: -webkit - scrollbar - thumb {
  background: #c1c1c1;
  border - radius: 4px;
}
      
      .bod - proxy - history.corporate - table - wrapper:: -webkit - scrollbar - thumb:hover {
  background: #a8a8a8;
}
      
      .bod - proxy - history.corporate - table - header th {
  position: sticky;
  top: 0;
  z - index: 10;
  background: #343a40!important;
  border - bottom: 2px solid #495057;
  font - weight: 600;
  color: #ffffff!important;
  font - size: 0.8rem;
  text - transform: uppercase;
  letter - spacing: 0.5px;
  padding: 0.75rem 0.5rem;
}
      
      .bod - proxy - history.corporate - history - table td {
  padding: 0.75rem 0.5rem;
  vertical - align: middle;
  border - bottom: 1px solid #e9ecef;
  word - wrap: break-word;
  overflow - wrap: break-word;
}
      
      .bod - proxy - history.corporate - table - row.cancelled {
  background - color: rgba(220, 53, 69, 0.05);
}
      
      .bod - proxy - history.corporate - table - row.expired {
  background - color: rgba(255, 193, 7, 0.05);
}
      
      .bod - proxy - history.corporate - table - row.active {
  background - color: rgba(40, 167, 69, 0.05);
}
      
      .bod - proxy - history.account - number {
  font - weight: 600;
  color: #495057;
  font - family: 'Monaco', 'Menlo', monospace;
}
      
      .bod - proxy - history.person - name {
  font - weight: 500;
  color: #212529;
  margin - bottom: 2px;
}
      
      .bod - proxy - history.corporate - type - badge {
  font - size: 0.65rem;
  padding: 0.2rem 0.4rem;
  font - weight: 600;
}
      
      .bod - proxy - history.badge - modern {
  font - size: 0.75rem;
  padding: 0.3rem 0.6rem;
  border - radius: 6px;
  font - weight: 500;
}

      /* BOD Proxy History Cancellation column styles */
      .bod - proxy - history.cancellation - cell {
  text - align: center;
  vertical - align: middle;
  min - width: 180px;
}
      
      .bod - proxy - history.cancellation - content {
  display: flex;
  justify - content: center;
  align - items: center;
}
      
      .bod - proxy - history.cancellation - info {
  text - align: center;
  max - width: 200px;
}
      
      .bod - proxy - history.cancellation - reason {
  font - size: 0.8rem;
  color: #dc3545;
  font - weight: 500;
  background - color: rgba(220, 53, 69, 0.1);
  padding: 0.25rem 0.5rem;
  border - radius: 4px;
  border: 1px solid rgba(220, 53, 69, 0.2);
  display: inline - block;
  margin - bottom: 0.25rem;
}
      
      .bod - proxy - history.cancelled - by,
      .bod - proxy - history.cancelled - at {
  font - size: 0.7rem;
  color: #6c757d;
  margin - top: 0.2rem;
  line - height: 1.2;
}
      
      .bod - proxy - history.cancelled - by i,
      .bod - proxy - history.cancelled - at i {
  font - size: 0.65rem;
}

      /* BOD Proxy History Remarks column styles */
      .bod - proxy - history.remarks - cell {
  min - width: 200px;
  width: auto;
}
      
      .bod - proxy - history.remarks - content {
  width: 100 %;
}
      
      .bod - proxy - history.remarks - text {
  max - width: 350px;
  overflow: hidden;
  text - overflow: ellipsis;
  white - space: nowrap;
  color: #6c757d;
  cursor: pointer;
  transition: all 0.3s ease;
  min - height: 1.2em;
  display: block;
}
      
      .bod - proxy - history.remarks - text:hover {
  white - space: normal;
  max - width: none;
  background - color: rgba(0, 123, 255, 0.1);
  padding: 0.25rem;
  border - radius: 4px;
  position: relative;
  z - index: 10;
}

      /* BOD Proxy History DateTime display styles */
      .bod - proxy - history.datetime - display {
  font - size: 0.8rem;
}
      
      .bod - proxy - history.date - part {
  font - weight: 500;
  color: #495057;
}
      
      .bod - proxy - history.time - part {
  font - size: 0.75rem;
  color: #868e96;
}
      
      .bod - proxy - history.record - number {
  display: inline - block;
  width: 24px;
  height: 24px;
  line - height: 24px;
  background: linear - gradient(135deg, #007bff, #0056b3);
  color: white;
  border - radius: 50 %;
  font - size: 0.75rem;
  font - weight: 600;
}

      /* BOD Proxy History Mobile Card Styles */
      .bod - proxy - history.mobile - cards - container {
  padding: 0;
  max - height: 600px;
  overflow - y: auto;
  overflow - x: hidden;
  display: flex;
  flex - direction: column;
  gap: 1rem;
}
      
      .bod - proxy - history.mobile - cards - container:: -webkit - scrollbar {
  width: 6px;
}
      
      .bod - proxy - history.mobile - cards - container:: -webkit - scrollbar - track {
  background: #f1f1f1;
  border - radius: 3px;
}
      
      .bod - proxy - history.mobile - cards - container:: -webkit - scrollbar - thumb {
  background: #c1c1c1;
  border - radius: 3px;
}
      
      .mobile - cards - container:: -webkit - scrollbar - thumb:hover {
  background: #a8a8a8;
}
      
      .bod - proxy - history.mobile - history - card {
  background: #fff;
  border - radius: 12px;
  border: 1px solid #e9ecef;
  margin - bottom: 0;
  overflow: hidden;
  box - shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
  transition: all 0.3s ease;
  word - wrap: break-word;
  overflow - wrap: break-word;
  position: relative;
  width: 100 %;
  min - height: auto;
}
      
      .bod - proxy - history.mobile - history - card:hover {
  box - shadow: 0 4px 16px rgba(0, 0, 0, 0.12);
  transform: translateY(-2px);
}
      
      .bod - proxy - history.mobile - history - card.cancelled {
  border - left: 4px solid #dc3545;
  background - color: rgba(220, 53, 69, 0.02);
}
      
      .bod - proxy - history.mobile - history - card.expired {
  border - left: 4px solid #ffc107;
  background - color: rgba(255, 193, 7, 0.02);
}
      
      .bod - proxy - history.mobile - history - card.active {
  border - left: 4px solid #28a745;
  background - color: rgba(40, 167, 69, 0.02);
}
      
      .bod - proxy - history.mobile - card - header {
  background: linear - gradient(135deg, #f8f9fa, #e9ecef);
  padding: 1rem;
  display: flex;
  justify - content: space - between;
  align - items: center;
  border - bottom: 1px solid #e9ecef;
}
      
      .bod - proxy - history.record - number - mobile {
  display: inline - block;
  width: 28px;
  height: 28px;
  line - height: 28px;
  background: linear - gradient(135deg, #007bff, #0056b3);
  color: white;
  border - radius: 50 %;
  font - size: 0.8rem;
  font - weight: 600;
  text - align: center;
}
      
      .bod - proxy - history.account - number - mobile {
  font - weight: 600;
  color: #495057;
  font - family: 'Monaco', 'Menlo', monospace;
  font - size: 0.9rem;
}
      
      .bod - proxy - history.mobile - card - body {
  padding: 1rem;
}
      
      .bod - proxy - history.mobile - info - row {
  display: flex;
  gap: 1rem;
  margin - bottom: 1rem;
  width: 100 %;
  clear: both;
}
      
      .bod - proxy - history.mobile - info - row: last - child {
  margin - bottom: 0;
}
      
      .bod - proxy - history.mobile - info - item {
  flex: 1;
  min - width: 0;
  width: 100 %;
}
      
      .bod - proxy - history.mobile - info - item.full - width {
  flex: 100 %;
  width: 100 %;
}
      
      .bod - proxy - history.mobile - label {
  display: block;
  font - size: 0.75rem;
  font - weight: 600;
  color: #6c757d;
  text - transform: uppercase;
  letter - spacing: 0.5px;
  margin - bottom: 0.25rem;
}
      
      .bod - proxy - history.mobile - value {
  font - size: 0.875rem;
  color: #212529;
  word - wrap: break-word;
  overflow - wrap: break-word;
  hyphens: auto;
  line - height: 1.4;
}
      
      .bod - proxy - history.person - name - mobile {
  font - weight: 500;
  display: block;
  margin - bottom: 0.25rem;
  word - wrap: break-word;
  overflow - wrap: break-word;
}
      
      .bod - proxy - history.remarks - mobile {
  line - height: 1.4;
  color: #6c757d;
  word - wrap: break-word;
  overflow - wrap: break-word;
  white - space: pre - wrap;
  max - height: 120px;
  overflow - y: auto;
  padding: 0.5rem;
  background - color: #f8f9fa;
  border - radius: 4px;
  border: 1px solid #e9ecef;
}

      /* BOD Proxy History Mobile cancellation styles */
      .bod - proxy - history.cancellation - mobile {
  text - align: center;
}
      
      .bod - proxy - history.mobile - cancellation - reason {
  font - size: 0.85rem;
  color: #dc3545;
  font - weight: 500;
  background - color: rgba(220, 53, 69, 0.1);
  padding: 0.3rem 0.6rem;
  border - radius: 4px;
  border: 1px solid rgba(220, 53, 69, 0.2);
  display: inline - block;
  margin - bottom: 0.5rem;
}
      
      .bod - proxy - history.mobile - cancelled - by,
      .bod - proxy - history.mobile - cancelled - at {
  font - size: 0.75rem;
  color: #6c757d;
  margin - top: 0.3rem;
  line - height: 1.3;
  text - align: center;
}
      
      .bod - proxy - history.mobile - cancelled - by i,
      .bod - proxy - history.mobile - cancelled - at i {
  font - size: 0.7rem;
  margin - right: 0.25rem;
}
      
      .remarks - mobile:: -webkit - scrollbar {
  width: 4px;
}
      
      .remarks - mobile:: -webkit - scrollbar - track {
  background: #f1f1f1;
  border - radius: 2px;
}
      
      .remarks - mobile:: -webkit - scrollbar - thumb {
  background: #c1c1c1;
  border - radius: 2px;
}

      /* Shared Styles */
      .section - header {
  border - bottom: 2px solid #e9ecef;
  padding - bottom: 1rem;
}
      
      .history - summary {
  background - color: #f8f9fa;
  border - radius: 6px;
  padding: 0.75rem 1rem;
  border: 1px solid #e9ecef;
  max - width: 100 %;
  overflow - x: auto;
}
      
      .history - summary:: -webkit - scrollbar {
  height: 4px;
}
      
      .history - summary:: -webkit - scrollbar - track {
  background: #f1f1f1;
  border - radius: 2px;
}
      
      .history - summary:: -webkit - scrollbar - thumb {
  background: #c1c1c1;
  border - radius: 2px;
}

/* BOD Proxy History Responsive Breakpoints */
@media(max - width: 1199.98px) {
        .bod - proxy - history.corporate - history - table {
    min - width: 1000px;
  }
        
        .bod - proxy - history.remarks - text {
    max - width: 300px;
  }
}

@media(max - width: 1399.98px) {
        .bod - proxy - history.corporate - history - table {
    font - size: 0.8rem;
  }
        
        .bod - proxy - history.corporate - table - header th {
    font - size: 0.75rem;
    padding: 0.6rem 0.4rem;
  }
        
        .bod - proxy - history.corporate - history - table td {
    padding: 0.6rem 0.4rem;
  }
}

@media(max - width: 991.98px) {
        .bod - proxy - history.mobile - info - row {
    flex - direction: column;
    gap: 0.75rem;
  }
        
        .bod - proxy - history.mobile - card - header {
    flex - direction: column;
    gap: 0.5rem;
    text - align: center;
  }
        
        .bod - proxy - history.history - summary.text - right {
    text - align: left!important;
    margin - top: 0.5rem;
  }
        
        .bod - proxy - history.mobile - cards - container {
    max - height: 500px;
    gap: 0.75rem;
  }
        
        .bod - proxy - history.mobile - history - card {
    margin - bottom: 0;
  }
}

@media(max - width: 767.98px) {
        .bod - proxy - history.table - responsive {
    display: none!important;
  }
        
        .bod - proxy - history.mobile - cards - container {
    display: block!important;
  }
        
        .bod - proxy - history.mobile - card - body {
    padding: 0.75rem;
  }
        
        .bod - proxy - history.mobile - card - header {
    padding: 0.75rem;
  }
        
        .bod - proxy - history.section - header h6 {
    font - size: 1.1rem;
  }
        
        .bod - proxy - history.section - header p {
    font - size: 0.875rem;
  }
        
        .bod - proxy - history - modal {
    margin: 0.5rem;
  }
        
        .bod - proxy - history - modal.modal - dialog {
    margin: 0;
    max - width: none;
    width: 100 %;
    height: 100vh;
  }
        
        .bod - proxy - history - modal.modal - content {
    height: 100vh;
    border - radius: 0;
  }
        
        .bod - proxy - history - modal.modal - header {
    padding: 0.75rem 1rem;
  }
        
        .bod - proxy - history - modal.modal - title {
    font - size: 1.1rem;
  }
        
        .bod - proxy - history - modal.modal - body {
    padding: 0.75rem;
  }
}

@media(max - width: 575.98px) {
        .bod - proxy - history.mobile - history - card {
    margin - bottom: 0;
  }
        
        .bod - proxy - history.mobile - info - row {
    gap: 0.5rem;
  }
        
        .bod - proxy - history.mobile - cards - container {
    gap: 0.5rem;
  }
}

@media(min - width: 768px) {
        .bod - proxy - history.table - responsive {
    display: block!important;
  }
        
        .bod - proxy - history.mobile - cards - container {
    display: none!important;
  }
}

      /* BOD Proxy History Scrollbar styles for webkit browsers */
      .bod - proxy - history.table - container:: -webkit - scrollbar,
      .bod - proxy - history.modal - body:: -webkit - scrollbar,
      .bod - proxy - history.remarks - mobile:: -webkit - scrollbar {
  width: 8px;
  height: 8px;
}
      
      .bod - proxy - history.table - container:: -webkit - scrollbar - track,
      .bod - proxy - history.modal - body:: -webkit - scrollbar - track,
      .bod - proxy - history.remarks - mobile:: -webkit - scrollbar - track {
  background: #f1f1f1;
  border - radius: 4px;
}
      
      .bod - proxy - history.table - container:: -webkit - scrollbar - thumb,
      .bod - proxy - history.modal - body:: -webkit - scrollbar - thumb,
      .bod - proxy - history.remarks - mobile:: -webkit - scrollbar - thumb {
  background: #c1c1c1;
  border - radius: 4px;
}
      
      .bod - proxy - history.table - container:: -webkit - scrollbar - thumb: hover,
      .bod - proxy - history.modal - body:: -webkit - scrollbar - thumb: hover,
      .bod - proxy - history.remarks - mobile:: -webkit - scrollbar - thumb:hover {
  background: #a8a8a8;
}
    </style >
  `;

  return content;
}

$(document).on('click', '.btn-proxyholder-history', function () {
  handleViewProxyHistory({
    recordId: $(this).data('id'),
    accountNo: $(this).data('account-no'),
    proxyType: $(this).data('proxy-type')
  });
})