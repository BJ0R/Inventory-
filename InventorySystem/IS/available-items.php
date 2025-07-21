<?php
// frontend/available-items.php
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
    <title>ISPSC Supply Office - Available Items</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/available-items.css">
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
                <h2 class="mb-4">Available Items</h2>

                <div class="card">
                    <div class="card-header">
                        Items Available for Request
                    </div>
                    <div class="card-body">
                        <div class="search-container">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search items...">
                            <button class="search-button" id="searchButton"><i class="bi bi-search"></i> Search</button>
                        </div>

                        <div class="table-responsive">
                            <table class="table" id="itemsTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="itemsTableBody">
                                    <!-- Items will be loaded via AJAX -->
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

            // Load items on page load
            loadItems(currentPage, searchQuery);

            // Search button click event
            $('#searchButton').click(function() {
                searchQuery = $('#searchInput').val();
                currentPage = 1;
                loadItems(currentPage, searchQuery);
            });

            // Search input enter key event
            $('#searchInput').keypress(function(e) {
                if (e.which === 13) {
                    searchQuery = $('#searchInput').val();
                    currentPage = 1;
                    loadItems(currentPage, searchQuery);
                }
            });

            // Function to load items
            function loadItems(page, query = '') {
                $.ajax({
                    url: 'backend/available-items_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_items',
                        page: page,
                        search: query
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            renderItems(response.data.items);
                            renderPagination(response.data.totalPages, page);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error loading items: ' + error, 'danger');
                    }
                });
            }

            // Function to render items table
            function renderItems(items) {
                const tableBody = $('#itemsTableBody');
                tableBody.empty();

                if (items.length === 0) {
                    tableBody.append('<tr><td colspan="4" class="text-center">No items found</td></tr>');
                    return;
                }

                items.forEach(item => {
                    const row = `
                        <tr>
                            <td>${item.id}</td>
                            <td>${item.item_name}</td>
                            <td>${item.description || '-'}</td>
                            <td>
                                <div class="quantity-control">
                                    <button class="quantity-btn decrease-btn" data-id="${item.id}">-</button>
                                    <input type="number" class="quantity-input" id="quantity-${item.id}" value="1" min="1">
                                    <button class="quantity-btn increase-btn" data-id="${item.id}">+</button>
                                    <button class="request-btn" data-id="${item.id}" data-name="${item.item_name}">
                                        Request
                                    </button>
                                </div>
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });

                // Add event listeners to quantity buttons
                $('.increase-btn').click(function() {
                    const itemId = $(this).data('id');
                    const input = $(`#quantity-${itemId}`);
                    input.val(parseInt(input.val()) + 1);
                });

                $('.decrease-btn').click(function() {
                    const itemId = $(this).data('id');
                    const input = $(`#quantity-${itemId}`);
                    const currentVal = parseInt(input.val());
                    if (currentVal > 1) {
                        input.val(currentVal - 1);
                    }
                });

                // Add event listeners to request buttons
                $('.request-btn').click(function() {
                    const itemId = $(this).data('id');
                    const itemName = $(this).data('name');
                    const quantity = $(`#quantity-${itemId}`).val();

                    requestItem(itemId, itemName, quantity);
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
                        loadItems(currentPage, searchQuery);
                    }
                });
            }

            // Function to request an item
            function requestItem(itemId, itemName, quantity) {
                $.ajax({
                    url: 'backend/available-items_api.php',
                    type: 'POST',
                    data: {
                        action: 'request_item',
                        item_id: itemId,
                        user_id: currentUserId,
                        quantity: quantity
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            showAlert(`Request for ${quantity} ${itemName} submitted successfully!`, 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error submitting request: ' + error, 'danger');
                    }
                });
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