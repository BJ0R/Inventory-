  $(document).ready(function() {
            let currentPage = 1;
            let searchQuery = '';
            let statusFilter = '';

            // Load requests on page load
            loadRequests(currentPage, searchQuery, statusFilter);

            // Search button click event
            $('#searchButton').click(function() {
                searchQuery = $('#searchInput').val();
                statusFilter = $('#statusFilter').val();
                currentPage = 1;
                loadRequests(currentPage, searchQuery, statusFilter);
            });

            // Search input enter key event
            $('#searchInput').keypress(function(e) {
                if (e.which === 13) {
                    searchQuery = $('#searchInput').val();
                    statusFilter = $('#statusFilter').val();
                    currentPage = 1;
                    loadRequests(currentPage, searchQuery, statusFilter);
                }
            });

            // Status filter change event
            $('#statusFilter').change(function() {
                searchQuery = $('#searchInput').val();
                statusFilter = $(this).val();
                currentPage = 1;
                loadRequests(currentPage, searchQuery, statusFilter);
            });

            // Function to load requests
            function loadRequests(page, query = '', status = '') {
                $.ajax({
                    url: 'backend/admin-request_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_requests',
                        page: page,
                        search: query,
                        status: status
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            renderRequests(response.data.requests);
                            renderPagination(response.data.totalPages, page);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error loading requests: ' + error, 'danger');
                    }
                });
            }

            // Function to render requests table
            function renderRequests(requests) {
                const tableBody = $('#requestsTableBody');
                tableBody.empty();

                if (requests.length === 0) {
                    tableBody.append('<tr><td colspan="7" class="text-center">No requests found</td></tr>');
                    return;
                }

                requests.forEach(request => {
                    const statusClass = `status-${request.status.toLowerCase().replace(' ', '_')}`;
                    
                    const row = `
                        <tr>
                            <td>${request.id}</td>
                            <td>${request.item_name}</td>
                            <td>${request.username}</td>
                            <td>${request.quantity_requested}</td>
                            <td><span class="${statusClass}">${formatStatus(request.status)}</span></td>
                            <td>${formatDate(request.request_date)}</td>
                            <td>
                                <select class="status-select" data-id="${request.id}" data-current="${request.status}">
                                    <option value="pending" ${request.status === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="approved" ${request.status === 'approved' ? 'selected' : ''}>Approved</option>
                                    <option value="rejected" ${request.status === 'rejected' ? 'selected' : ''}>Rejected</option>
                                    <option value="on_order" ${request.status === 'on_order' ? 'selected' : ''}>On Order</option>
                                    <option value="partially_fulfilled" ${request.status === 'partially_fulfilled' ? 'selected' : ''}>Partially Fulfilled</option>
                                    <option value="ready_for_pickup" ${request.status === 'ready_for_pickup' ? 'selected' : ''}>Ready for Pickup</option>
                                    <option value="completed" ${request.status === 'completed' ? 'selected' : ''}>Completed</option>
                                    <option value="cancelled" ${request.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select>
                                <button class="btn btn-primary btn-sm update-btn" data-id="${request.id}">
                                    <i class="bi bi-check"></i> Update
                                </button>
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });

                // Add event listeners to update buttons
                $('.update-btn').click(function() {
                    const requestId = $(this).data('id');
                    const newStatus = $(`select[data-id="${requestId}"]`).val();
                    const currentStatus = $(`select[data-id="${requestId}"]`).data('current');
                    
                    if (newStatus !== currentStatus) {
                        updateRequestStatus(requestId, newStatus);
                    }
                });
            }

            // Function to render pagination
            function renderPagination(totalPages, currentPage) {
                const pagination = $('#pagination');
                pagination.empty();

                if (totalPages <= 1) return;

                // Previous button
                const prevDisabled = currentPage <= 1 ? 'disabled' : '';
                pagination.append(`
                    <a href="#" class="page-link ${prevDisabled}" data-page="${currentPage - 1}">
                        &laquo; Previous
                    </a>
                `);

                // Page numbers
                const startPage = Math.max(1, currentPage - 2);
                const endPage = Math.min(totalPages, currentPage + 2);
                
                if (startPage > 1) {
                    pagination.append('<a href="#" class="page-link" data-page="1">1</a>');
                    if (startPage > 2) {
                        pagination.append('<span class="page-link disabled">...</span>');
                    }
                }
                
                for (let i = startPage; i <= endPage; i++) {
                    const active = i === currentPage ? 'active' : '';
                    pagination.append(`
                        <a href="#" class="page-link ${active}" data-page="${i}">${i}</a>
                    `);
                }
                
                if (endPage < totalPages) {
                    if (endPage < totalPages - 1) {
                        pagination.append('<span class="page-link disabled">...</span>');
                    }
                    pagination.append(`<a href="#" class="page-link" data-page="${totalPages}">${totalPages}</a>`);
                }

                // Next button
                const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
                pagination.append(`
                    <a href="#" class="page-link ${nextDisabled}" data-page="${currentPage + 1}">
                        Next &raquo;
                    </a>
                `);

                // Add event listeners to page links
                $('.page-link').click(function(e) {
                    e.preventDefault();
                    if (!$(this).hasClass('disabled') && !$(this).hasClass('active')) {
                        currentPage = parseInt($(this).data('page'));
                        loadRequests(currentPage, searchQuery, statusFilter);
                    }
                });
            }

            // Function to update request status
            function updateRequestStatus(requestId, newStatus) {
                $.ajax({
                    url: 'backend/admin-request_api.php',
                    type: 'POST',
                    data: {
                        action: 'update_request_status',
                        id: requestId,
                        status: newStatus
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            loadRequests(currentPage, searchQuery, statusFilter);
                            showAlert('Request status updated successfully!', 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error updating request status: ' + error, 'danger');
                    }
                });
            }

            // Helper function to format status
            function formatStatus(status) {
                return status.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            }

            // Helper function to format date
            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
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