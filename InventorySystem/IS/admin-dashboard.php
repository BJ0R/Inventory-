<?php
// frontend/dashboard.php
session_start();
require_once 'db_connect.php';

// Check if user is admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: account.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISPSC Supply Office - Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="css/admin-home.css">
</head>

<body>
    <div class="page-wrapper">
        <div id="nav-container">
            <?php include 'components/nav.php'; ?>
        </div>

        <div id="header-container">
            <?php include 'components/header.html'; ?>
        </div>

        <div class="content-wrapper">
            <div class="container-fluid">
                <h2 class="mb-4">Admin Dashboard</h2>

                <div class="row mb-4 g-3">
                    <div class="col-md-3 col-sm-6">
                        <div class="card stat-card" onclick="window.location.href='inventory.php'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Items</h5>
                                        <div class="stat-value" id="totalItems">0</div>
                                        <div class="trend-up" id="itemsTrend">
                                            <i class="bi bi-arrow-up"></i> <span id="itemsTrendValue">0%</span>
                                        </div>
                                    </div>
                                    <i class="bi bi-box-seam stat-icon text-primary"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card stat-card" onclick="window.location.href='inventory.php?filter=withdrawals'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Withdrawals</h5>
                                        <div class="stat-value" id="totalWithdrawals">0</div>
                                        <div class="trend-up" id="withdrawalsTrend">
                                            <i class="bi bi-arrow-up"></i> <span id="withdrawalsTrendValue">0%</span>
                                        </div>
                                    </div>
                                    <i class="bi bi-box-arrow-up stat-icon text-info"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card stat-card" onclick="window.location.href='admin-request.php?status=pending'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Pending Requests</h5>
                                        <div class="stat-value pending-requests" id="pendingRequests">0</div>
                                        <div class="trend-neutral" id="requestsTrend">
                                            <i class="bi bi-arrow-right"></i> <span id="requestsTrendValue">0%</span>
                                        </div>
                                    </div>
                                    <i class="bi bi-clock-history stat-icon text-warning"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 col-sm-6">
                        <div class="card stat-card" onclick="window.location.href='user-management.php'">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 class="card-title">Total Users</h5>
                                        <div class="stat-value" id="totalUsers">0</div>
                                        <div class="trend-up" id="usersTrend">
                                            <i class="bi bi-arrow-up"></i> <span id="usersTrendValue">0%</span>
                                        </div>
                                    </div>
                                    <i class="bi bi-people stat-icon text-success"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mb-4 g-3">
                    <div class="col-lg-8">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <span>Item Stock & Withdrawal Analysis</span>
                                <div class="filter-group"> <select id="itemTimeFilter" class="form-select form-select-sm filter-select">
                                        <option value="1">Today</option>
                                        <option value="7" selected>Last 7 Days</option>
                                        <option value="30">Last 30 Days</option>
                                        <option value="90">Last 90 Days</option>
                                    </select>
                                    <select id="itemSortFilter" class="form-select form-select-sm filter-select">
                                        <option value="stock">Sort by Stock</option>
                                        <option value="withdrawal">Sort by Withdrawal</option>
                                        <option value="name">Sort by Name</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="itemAnalysisChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center flex-wrap">
                                <span>Low Stock Items</span>
                                <div class="filter-group"> 
                                    <select id="lowStockFilter" class="form-select form-select-sm filter-select">
                                        <option value="5">Below 5</option>
                                        <option value="10">Below 10</option>
                                        <option value="15">Below 15</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="chart-container">
                                    <canvas id="lowStockChart"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Recent Activities</span>
                                <a href="activity-log.php" class="btn btn-sm btn-primary">View All</a>
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                <div id="recentActivities">
                                    </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <span>Pending Requests</span>
                                <a href="admin-request.php" class="btn btn-sm btn-primary">Manage All</a>
                            </div>
                            <div class="card-body" style="max-height: 300px; overflow-y: auto;">
                                <div id="pendingRequestsList">
                                    </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="footer-container">
            <?php include 'components/footer.html'; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/admin-dashboard.js"></script>
</body>
</html>
