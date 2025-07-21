<?php
// frontend/admin-request.php
session_start();
require_once 'db_connect.php';

// For demonstration, we'll skip authentication as requested
// In a real system, you would check for admin role here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISPSC Supply Office - Admin Request Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/admin-request.css">
</head>
<body>
    <div class="page-wrapper">
        <!-- Sidebar Navigation -->
        <div id="nav-container">
            <?php include 'components/nav.php'; ?>
        </div>

        <!-- Header -->
        <div id="header-container">
            <?php include 'components/header.html'; ?>
        </div>

        <!-- Main Content -->
        <div class="content-wrapper">
            <div class="container-fluid">
                <h2 class="mb-4">Request Management</h2>

                <div class="card">
                    <div class="card-header">
                        User Requests
                    </div>
                    <div class="card-body">
                        <div class="search-filter-container">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search requests...">
                            <select id="statusFilter" class="filter-select">
                                <option value="">All Statuses</option>
                                <option value="pending">Pending</option>
                                <option value="approved">Approved</option>
                                <option value="rejected">Rejected</option>
                                <option value="on_order">On Order</option>
                                <option value="partially_fulfilled">Partially Fulfilled</option>
                                <option value="ready_for_pickup">Ready for Pickup</option>
                                <option value="completed">Completed</option>
                                <option value="cancelled">Cancelled</option>
                            </select>
                            <button class="search-button" id="searchButton"><i class="bi bi-search"></i> Search</button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table" id="requestsTable">
                                <thead>
                                    <tr>
                                        <th>Request ID</th>
                                        <th>Item Name</th>
                                        <th>Requester</th>
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
        
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin-request.js"></script>
</body>
</html>