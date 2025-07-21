 $(document).ready(function() {
            let currentPage = 1;
            let searchQuery = '';
            let currentFilterType = 'all';
            let currentTimeRange = 'monthly';
            let currentYear = new Date().getFullYear();
            let currentMonth = (new Date().getMonth() + 1).toString().padStart(2, '0');
            let currentStartDate = '';
            let currentEndDate = '';
            let currentPdfHtml = '';
            
            // Set current month in dropdown
            $('#month').val(currentMonth);
            
            // Initialize the report
            loadSummaryReport();
            
            // Time range change event
            $('#timeRange').change(function() {
                currentTimeRange = $(this).val();
                
                $('#monthGroup, #yearGroup, #customRangeGroup').hide();
                
                if (currentTimeRange === 'monthly') {
                    $('#monthGroup').show();
                } else if (currentTimeRange === 'yearly') {
                    $('#yearGroup').show();
                } else if (currentTimeRange === 'custom') {
                    $('#customRangeGroup').show();
                }
                
                loadSummaryReport();
            });
            
            // Filter type change event
            $('#filterType').change(function() {
                currentFilterType = $(this).val();
                loadSummaryReport();
            });
            
            // Month/year change events
            $('#month, #year, #yearOnly').change(function() {
                if (currentTimeRange === 'monthly') {
                    currentMonth = $('#month').val();
                    currentYear = $('#year').val();
                } else if (currentTimeRange === 'yearly') {
                    currentYear = $('#yearOnly').val();
                }
                
                loadSummaryReport();
            });
            
            // Custom date range change events
            $('#startDate, #endDate').change(function() {
                currentStartDate = $('#startDate').val();
                currentEndDate = $('#endDate').val();
                
                if (currentStartDate && currentEndDate) {
                    loadSummaryReport();
                }
            });
            
            // Search button click event
            $('#searchButton').click(function() {
                searchQuery = $('#searchInput').val();
                currentPage = 1;
                loadSummaryReport();
            });
            
            // Search input enter key event
            $('#searchInput').keypress(function(e) {
                if (e.which === 13) {
                    searchQuery = $('#searchInput').val();
                    currentPage = 1;
                    loadSummaryReport();
                }
            });
            
            // Download PDF button click event
            $('#downloadPdfBtn').click(function() {
                generatePDF(true);
            });
            
            // Confirm Download PDF button click event
            $('#confirmDownloadPdfBtn').click(function() {
                downloadPDF();
            });
            
            // Print button click event
            $('#printPdfBtn').click(function() {
                printPDF();
            });
            
            // Function to load summary report
            function loadSummaryReport() {
                const data = {
                    action: 'get_summary_report',
                    filter_type: currentFilterType,
                    time_range: currentTimeRange,
                    year: currentYear,
                    month: currentMonth,
                    start_date: currentStartDate,
                    end_date: currentEndDate,
                    search: searchQuery,
                    page: currentPage
                };
                
                $.ajax({
                    url: 'backend/summaryreport_api.php',
                    type: 'GET',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            renderTableHeaders(currentFilterType);
                            renderTableData(response.data.items);
                            renderPagination(response.data.totalPages);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error loading report: ' + error, 'danger');
                    }
                });
            }
            
            // Function to generate PDF preview or download directly
            function generatePDF(preview = true) {
                const data = {
                    action: 'generate_pdf',
                    filter_type: currentFilterType,
                    time_range: currentTimeRange,
                    year: currentTimeRange === 'monthly' ? currentYear : $('#yearOnly').val(),
                    month: currentMonth,
                    start_date: currentStartDate,
                    end_date: currentEndDate
                };
                
                $.ajax({
                    url: 'backend/summaryreport_api.php',
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            currentPdfHtml = response.html;
                            $('#pdfPrintContainer').html(response.html);
                            
                            if (preview) {
                                $('#pdfPreviewModal').modal('show');
                            } else {
                                downloadPDF();
                            }
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error generating PDF: ' + error, 'danger');
                    }
                });
            }
            
            // Function to download PDF
            function downloadPDF() {
                const element = document.createElement('div');
                element.innerHTML = currentPdfHtml;
                
                const opt = {
                    margin: 10,
                    filename: `ISPSC_Inventory_Report_${new Date().toISOString().slice(0,10)}.pdf`,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2 },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' }
                };
                
                html2pdf().set(opt).from(element).save();
                
                $('#pdfPreviewModal').modal('hide');
            }
            
            // Function to print PDF
            function printPDF() {
                const printWindow = window.open('', '_blank');
                printWindow.document.write(`
                    <!DOCTYPE html>
                    <html>
                    <head>
                        <title>Print Report</title>
                        <style>
                            body { font-family: Arial, sans-serif; margin: 20px; }
                            .report-header { display: flex; align-items: center; margin-bottom: 20px; }
                            .report-header img { height: 80px; margin-right: 20px; }
                            .report-title { text-align: center; flex-grow: 1; }
                            .report-title h1 { font-size: 18px; margin: 0; }
                            .report-title p { font-size: 14px; margin: 5px 0 0 0; }
                            table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                            th { background-color: #f2f2f2; }
                            .footer { text-align: center; margin-top: 30px; font-size: 12px; }
                        </style>
                    </head>
                    <body>
                        ${currentPdfHtml}
                    </body>
                    </html>
                `);
                
                printWindow.document.close();
                printWindow.focus();
                setTimeout(() => {
                    printWindow.print();
                    printWindow.close();
                }, 500);
            }
            
            // Function to render table headers based on filter type
            function renderTableHeaders(filterType) {
                const headers = $('#tableHeaders');
                headers.empty();
                
                if (filterType === 'added') {
                    headers.append(`
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Unit</th>
                        <th>Date Added</th>
                        <th>RIS Number</th>
                        <th>Supplier</th>
                    `);
                } else if (filterType === 'withdrawn') {
                    headers.append(`
                        <th>Item Name</th>
                        <th>RIS Number</th>
                        <th>Supplier</th>
                        <th>WS Number</th>
                        <th>Quantity Withdrawn</th>
                        <th>Balance</th>
                        <th>Date Withdrawn</th>
                        <th>Remarks</th>
                    `);
                } else {
                    headers.append(`
                        <th>Item Name</th>
                        <th>Description</th>
                        <th>Unit</th>
                        <th>Date Added</th>
                        <th>RIS Number</th>
                        <th>Supplier</th>
                        <th>WS Number</th>
                        <th>Quantity Withdrawn</th>
                        <th>Balance</th>
                        <th>Date Withdrawn</th>
                        <th>Remarks</th>
                    `);
                }
            }
            
            // Function to render table data
            function renderTableData(items) {
                const tableBody = $('#summaryTableBody');
                tableBody.empty();
                
                if (items.length === 0) {
                    const colSpan = $('#tableHeaders th').length;
                    tableBody.append(`<tr><td colspan="${colSpan}" class="text-center">No items found</td></tr>`);
                    return;
                }
                
                items.forEach(item => {
                    const row = $('<tr>');
                    
                    if (currentFilterType === 'added') {
                        row.append(`
                            <td>${escapeHtml(item.item_name)}</td>
                            <td>${escapeHtml(item.description) || '-'}</td>
                            <td>${escapeHtml(item.unit)}</td>
                            <td>${formatDateTime(item.date_added)}</td>
                            <td>${escapeHtml(item.ris_number) || '-'}</td>
                            <td>${escapeHtml(item.supplier) || '-'}</td>
                        `);
                    } else if (currentFilterType === 'withdrawn') {
                        row.append(`
                            <td>${escapeHtml(item.item_name)}</td>
                            <td>${escapeHtml(item.ris_number) || '-'}</td>
                            <td>${escapeHtml(item.supplier) || '-'}</td>
                            <td>${escapeHtml(item.ws_number)}</td>
                            <td>${item.quantity_withdrawn}</td>
                            <td>${item.balance}</td>
                            <td>${formatDateTime(item.date_withdrawn)}</td>
                            <td>${escapeHtml(item.remark) || '-'}</td>
                        `);
                    } else {
                        row.append(`
                            <td>${escapeHtml(item.item_name)}</td>
                            <td>${escapeHtml(item.description) || '-'}</td>
                            <td>${escapeHtml(item.unit)}</td>
                            <td>${formatDateTime(item.date_added)}</td>
                            <td>${escapeHtml(item.ris_number) || '-'}</td>
                            <td>${escapeHtml(item.supplier) || '-'}</td>
                            <td>${escapeHtml(item.ws_number) || '-'}</td>
                            <td>${item.quantity_withdrawn || '-'}</td>
                            <td>${item.balance || '-'}</td>
                            <td>${item.date_withdrawn ? formatDateTime(item.date_withdrawn) : '-'}</td>
                            <td>${escapeHtml(item.remark) || '-'}</td>
                        `);
                    }
                    
                    tableBody.append(row);
                });
            }
            
            // Function to render pagination
            function renderPagination(totalPages) {
                const pagination = $('#pagination');
                pagination.empty();
                
                if (totalPages <= 1) return;
                
                // Previous button
                const prevDisabled = currentPage <= 1 ? 'disabled' : '';
                pagination.append(`
                    <a href="#" data-page="${currentPage - 1}" ${prevDisabled ? 'class="disabled"' : ''}>
                        &laquo; Previous
                    </a>
                `);
                
                // Page numbers
                const startPage = Math.max(1, currentPage - 2);
                const endPage = Math.min(totalPages, currentPage + 2);
                
                if (startPage > 1) {
                    pagination.append(`
                        <a href="#" data-page="1">1</a>
                    `);
                    if (startPage > 2) {
                        pagination.append(`
                            <a class="disabled">...</a>
                        `);
                    }
                }
                
                for (let i = startPage; i <= endPage; i++) {
                    const active = i === currentPage ? 'active' : '';
                    pagination.append(`
                        <a href="#" data-page="${i}" class="${active}">${i}</a>
                    `);
                }
                
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        pagination.append(`
                            <a class="disabled">...</a>
                        `);
                    }
                    pagination.append(`
                        <a href="#" data-page="${totalPages}">${totalPages}</a>
                    `);
                }
                
                // Next button
                const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
                pagination.append(`
                    <a href="#" data-page="${currentPage + 1}" ${nextDisabled ? 'class="disabled"' : ''}>
                        Next &raquo;
                    </a>
                `);
                
                // Add event listeners to page links
                $('.pagination a').click(function(e) {
                    e.preventDefault();
                    if (!$(this).hasClass('disabled') && !$(this).hasClass('active')) {
                        currentPage = parseInt($(this).data('page'));
                        loadSummaryReport();
                    }
                });
            }
            
            // Helper function to format date/time
            function formatDateTime(dateTimeString) {
                if (!dateTimeString) return '';
                
                const date = new Date(dateTimeString);
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }
            
            // Helper function to escape HTML
            function escapeHtml(text) {
                if (!text) return '';
                return text
                    .replace(/&/g, "&amp;")
                    .replace(/</g, "&lt;")
                    .replace(/>/g, "&gt;")
                    .replace(/"/g, "&quot;")
                    .replace(/'/g, "&#039;");
            }
            
            // Function to show alert message
            function showAlert(message, type) {
                const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
                const alert = $(`
                    <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                `);
                
                $('.content-wrapper').prepend(alert);
                
                setTimeout(() => {
                    alert.alert('close');
                }, 5000);
            }
        });