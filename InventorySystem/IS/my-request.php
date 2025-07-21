<?php
// frontend/my-request.php
session_start();
require_once 'db_connect.php';

// For demonstration, we'll use a session user ID
// In a real system, this would come from authentication
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = mt_rand(1, 1000); // Simulate a user ID
}
$currentUserId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISPSC Supply Office - My Requests</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
 <link rel="stylesheet" href="css/my-request.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Sidebar Navigation -->
        <div id="nav-container">
            <?php include 'components/user-nav.php'; ?>
        </div>

        <!-- Header -->
        <div id="header-container">
            <?php include 'components/header.html'; ?>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            <div class="container-fluid">
                <h2 class="mb-4">My Requests</h2>

                <div class="card">
                    <div class="card-header">
                        My Item Requests
                    </div>
                    <div class="card-body">
                        <div class="search-container">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search my requests...">
                            <button class="search-button" id="searchButton"><i class="bi bi-search"></i> Search</button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table" id="requestsTable">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Item Name</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                        <th>Request Date</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="requestsTableBody">
                                    <!-- Requests will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="pagination" id="pagination">
                            <!-- Pagination will be loaded via AJAX -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div id="footer-container">
            <?php include 'components/footer.html'; ?>
        </div>
           <!-- botleg -->
            <?php include 'chatbot.php'; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
 $(document).ready(function() {
            let currentPage = 1;
            let searchQuery = '';
            const currentUserId = <?php echo $currentUserId; ?>;

            // Load requests on page load
            loadRequests(currentPage, searchQuery);

            // Search button click event
            $('#searchButton').click(function() {
                searchQuery = $('#searchInput').val();
                currentPage = 1;
                loadRequests(currentPage, searchQuery);
            });

            // Search input enter key event
            $('#searchInput').keypress(function(e) {
                if (e.which === 13) {
                    searchQuery = $('#searchInput').val();
                    currentPage = 1;
                    loadRequests(currentPage, searchQuery);
                }
            });

            // Function to load requests
            function loadRequests(page, query = '') {
                $.ajax({
                    url: 'backend/my-requests_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_requests',
                        page: page,
                        search: query,
                        user_id: currentUserId
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
                    tableBody.append('<tr><td colspan="6" class="text-center">No requests found</td></tr>');
                    return;
                }

                requests.forEach(request => {
                    const statusClass = `status-${request.status.toLowerCase().replace(' ', '_')}`;
                    const canCancel = request.status === 'pending' || request.status === 'approved';
                    
                    const row = `
                        <tr>
                            <td>${request.id}</td>
                            <td>${request.item_name}</td>
                            <td>${request.quantity_requested}</td>
                            <td><span class="${statusClass}">${formatStatus(request.status)}</span></td>
                            <td>${formatDate(request.request_date)}</td>
                            <td>
                                ${canCancel ? 
                                    `<button class="btn btn-danger btn-sm cancel-btn" data-id="${request.id}" data-name="${request.item_name}">
                                        <i class="bi bi-x-circle"></i> Cancel
                                    </button>` : 
                                    '<span class="text-muted">No action</span>'
                                }
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });

                // Add event listeners to cancel buttons
                $('.cancel-btn').click(function() {
                    const requestId = $(this).data('id');
                    const itemName = $(this).data('name');
                    
                    if (confirm(`Are you sure you want to cancel your request for ${itemName}?`)) {
                        cancelRequest(requestId);
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
                        loadRequests(currentPage, searchQuery);
                    }
                });
            }

            // Function to cancel a request
            function cancelRequest(requestId) {
                $.ajax({
                    url: 'backend/my-requests_api.php',
                    type: 'POST',
                    data: {
                        action: 'cancel_request',
                        id: requestId,
                        user_id: currentUserId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            loadRequests(currentPage, searchQuery);
                            showAlert(response.message, 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error cancelling request: ' + error, 'danger');
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
    </script>
</body>
</html>