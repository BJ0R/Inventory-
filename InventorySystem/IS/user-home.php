<?php
// frontend/user-home.php
session_start();
require_once 'db_connect.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Default user ID for demo
}
$currentUserId = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISPSC Supply Office - Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/user-home.css">
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
                <h2 class="mb-4">Dashboard</h2>

                <!-- Welcome Card -->
                <div class="welcome-card">
                    <div class="welcome-title" id="welcomeTitle">Welcome, User!</div>
                    <div class="welcome-message">Here's a quick overview of your requests and system activity.</div>
                    <div>
                        <a href="available-items.php" class="quick-action-btn">
                            <i class="bi bi-plus-circle"></i> Request New Item
                        </a>
                        <a href="my-requests.php" class="quick-action-btn">
                            <i class="bi bi-list-ul"></i> View My Requests
                        </a>
                    </div>
                </div>

                <!-- Request Status Overview -->
                <div class="card">
                    <div class="card-header">
                        Your Request Status
                    </div>
                    <div class="card-body">
                        <div class="row" id="requestStatsContainer">
                            <!-- Stats will be loaded via AJAX -->
                            <div class="col-md-12 text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mt-3">
                            <a href="my-requests.php" class="btn btn-primary">
                                <i class="bi bi-arrow-right"></i> Go to My Requests
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recently Completed Requests -->
                <div class="card">
                    <div class="card-header">
                        Recently Completed Requests
                    </div>
                    <div class="card-body">
                        <div id="recentRequestsContainer">
                            <!-- Recent requests will be loaded via AJAX -->
                            <div class="text-center py-4">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                            </div>
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
        <div id="botleg-container">
<?php include 'chatbot.php'; ?>
        
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            const currentUserId = <?php echo $currentUserId; ?>;
            
            // Load dashboard data
            loadDashboardData(currentUserId);

            // Function to load dashboard data
            function loadDashboardData(userId) {
                $.ajax({
                    url: 'backend/user-home_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_dashboard_data',
                        user_id: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            renderDashboard(response.data);
                        } else {
                            showError(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        showError('Error loading dashboard data: ' + error);
                    }
                });
            }

            // Function to render dashboard data
            function renderDashboard(data) {
                // Update welcome message
                $('#welcomeTitle').text('Welcome, ' + data.user.username + '!');

                // Render request stats
                const statsHtml = `
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">${data.stats.total_requests}</div>
                            <div class="stat-label">Total Requests</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">${data.stats.pending_requests}</div>
                            <div class="stat-label">Pending</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">${data.stats.approved_requests}</div>
                            <div class="stat-label">Approved</div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="stat-card">
                            <div class="stat-value">${data.stats.completed_requests}</div>
                            <div class="stat-label">Completed</div>
                        </div>
                    </div>
                `;
                $('#requestStatsContainer').html(statsHtml);

                // Render recent completed requests
                if (data.recent_requests.length > 0) {
                    let recentHtml = '';
                    data.recent_requests.forEach(request => {
                        recentHtml += `
                            <div class="recent-item">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <strong>${request.item_name}</strong>
                                        <div class="text-muted small">Quantity: ${request.quantity_requested}</div>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">Completed</span>
                                        <div class="text-muted small">${formatDate(request.completed_date)}</div>
                                    </div>
                                </div>
                            </div>
                        `;
                    });
                    $('#recentRequestsContainer').html(recentHtml);
                } else {
                    $('#recentRequestsContainer').html('<div class="text-center py-3 text-muted">No recently completed requests</div>');
                }
            }

            // Helper function to format date
            function formatDate(dateString) {
                const date = new Date(dateString);
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
            }

            // Function to show error message
            function showError(message) {
                const errorHtml = `
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle"></i> ${message}
                    </div>
                `;
                $('#requestStatsContainer').html(errorHtml);
                $('#recentRequestsContainer').html(errorHtml);
            }
        });
    </script>
</body>
</html>