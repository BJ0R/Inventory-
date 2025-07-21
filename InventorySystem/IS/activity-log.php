<?php
// frontend/activity-log.php
session_start();
require_once 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISPSC Supply Office - Activity Log</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/activity-log.css">
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
                <h2 class="mb-4">Activity Log</h2>

                <div class="card">
                    <div class="card-header">
                        Activity History
                    </div>
                    <div class="card-body">
                        <div class="search-filter-container">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search activities...">
                            <select id="actionFilter" class="filter-select">
                                <option value="">All Actions</option>
                                <option value="added">Added</option>
                                <option value="edited">Edited</option>
                                <option value="withdrawn">Withdrawn</option>
                            </select>
                            <button class="search-button" id="searchButton"><i class="bi bi-search"></i> Search</button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table" id="activityTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Action</th>
                                        <th>Details</th>
                                        <th>Performed By</th>
                                        <th>Date/Time</th>
                                    </tr>
                                </thead>
                                <tbody id="activityTableBody">
                                    <!-- Activities will be loaded via AJAX -->
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
    <script src="js/activity-log.js"> </script>
</body>
</html>