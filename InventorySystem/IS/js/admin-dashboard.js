  $(document).ready(function() {
            // Initialize charts
            let lowStockChart, itemAnalysisChart;

            // Load all dashboard data
            loadDashboardStats();
            loadLowStockChart();
            loadItemAnalysisChart();
            loadRecentActivities();
            loadPendingRequests();

            // Event listeners for filters
            $('#lowStockFilter').change(loadLowStockChart);
            $('#itemTimeFilter, #itemSortFilter').change(loadItemAnalysisChart);

            // Function to load dashboard stats
            function loadDashboardStats() {
                $.ajax({
                    url: 'backend/admin-dashboard_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_dashboard_stats'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#totalItems').text(response.data.total_items);
                            $('#itemsTrendValue').text(response.data.items_trend + '%');

                            $('#totalWithdrawals').text(response.data.total_withdrawals);
                            $('#withdrawalsTrendValue').text(response.data.withdrawals_trend + '%');

                            $('#pendingRequests').text(response.data.pending_requests);
                            $('#requestsTrendValue').text(response.data.requests_trend + '%');

                            $('#totalUsers').text(response.data.total_users);
                            $('#usersTrendValue').text(response.data.users_trend + '%');
                        }
                    }
                });
            }

            // Function to load low stock chart
            function loadLowStockChart() {
                const threshold = $('#lowStockFilter').val();

                $.ajax({
                    url: 'backend/admin-dashboard_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_low_stock_data',
                        threshold: threshold
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const ctx = document.getElementById('lowStockChart').getContext('2d');

                            if (lowStockChart) {
                                lowStockChart.destroy();
                            }

                            lowStockChart = new Chart(ctx, {
                                type: 'doughnut',
                                data: {
                                    labels: response.data.labels,
                                    datasets: [{
                                        data: response.data.quantities,
                                        backgroundColor: [
                                            'rgba(255, 99, 132, 0.7)',
                                            'rgba(54, 162, 235, 0.7)',
                                            'rgba(255, 206, 86, 0.7)',
                                            'rgba(75, 192, 192, 0.7)',
                                            'rgba(153, 102, 255, 0.7)',
                                            'rgba(255, 159, 64, 0.7)'
                                        ],
                                        borderWidth: 1
                                    }]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    plugins: {
                                        legend: {
                                            position: 'right'
                                        },
                                        tooltip: {
                                            callbacks: {
                                                label: function(context) {
                                                    return `${context.label}: ${context.raw} units`;
                                                }
                                            }
                                        }
                                    }
                                }
                            });
                        }
                    }
                });
            }

            // Function to load item analysis chart
            function loadItemAnalysisChart() {
                const days = $('#itemTimeFilter').val();
                const sortBy = $('#itemSortFilter').val();

                $.ajax({
                    url: 'backend/admin-dashboard_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_item_analysis_data',
                        days: days,
                        sort_by: sortBy
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const ctx = document.getElementById('itemAnalysisChart').getContext('2d');

                            if (itemAnalysisChart) {
                                itemAnalysisChart.destroy();
                            }

                            itemAnalysisChart = new Chart(ctx, {
                                type: 'bar',
                                data: {
                                    labels: response.data.labels,
                                    datasets: [{
                                            label: 'Current Stock',
                                            data: response.data.stock_levels,
                                            backgroundColor: 'rgba(54, 162, 235, 0.7)',
                                            borderColor: 'rgba(54, 162, 235, 1)',
                                            borderWidth: 1,
                                            yAxisID: 'y'
                                        },
                                        {
                                            label: 'Total Withdrawn',
                                            data: response.data.withdrawn_amounts,
                                            backgroundColor: 'rgba(255, 99, 132, 0.7)',
                                            borderColor: 'rgba(255, 99, 132, 1)',
                                            borderWidth: 1,
                                            yAxisID: 'y1'
                                        }
                                    ]
                                },
                                options: {
                                    responsive: true,
                                    maintainAspectRatio: false,
                                    scales: {
                                        y: {
                                            type: 'linear',
                                            display: true,
                                            position: 'left',
                                            title: {
                                                display: true,
                                                text: 'Stock Levels'
                                            },
                                            beginAtZero: true
                                        },
                                        y1: {
                                            type: 'linear',
                                            display: true,
                                            position: 'right',
                                            title: {
                                                display: true,
                                                text: 'Withdrawn Amount'
                                            },
                                            grid: {
                                                drawOnChartArea: false
                                            },
                                            beginAtZero: true
                                        }
                                    }
                                }
                            });
                        }
                    }
                });
            }

            // Function to load recent activities
            function loadRecentActivities() {
                $.ajax({
                    url: 'backend/admin-dashboard_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_recent_activities'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            let html = '';
                            response.data.forEach(activity => {
                                html += `
                                    <div class="activity-item">
                                        <div class="fw-bold">${activity.action} - ${activity.item_name || 'System'}</div>
                                        <div>${activity.details}</div>
                                        <div class="activity-time">
                                            ${activity.performed_by} • ${formatDateTime(activity.performed_at)}
                                        </div>
                                    </div>
                                `;
                            });
                            $('#recentActivities').html(html);
                        }
                    }
                });
            }

            // Function to load pending requests
            function loadPendingRequests() {
                $.ajax({
                    url: 'backend/admin-dashboard_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_pending_requests'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            let html = '';
                            response.data.forEach(request => {
                                html += `
                                    <div class="request-item d-flex justify-content-between align-items-center">
                                        <div>
                                            <div class="fw-bold">${request.item_name}</div>
                                            <div>Requested by: ${request.username}</div>
                                            <div>Qty: ${request.quantity_requested} ${request.unit}</div>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-muted small">${formatDate(request.request_date)}</div>
                                            <button class="btn btn-sm btn-primary mt-1" onclick="window.location.href='admin-request.php'">
                                                Review
                                            </button>
                                        </div>
                                    </div>
                                `;
                            });
                            $('#pendingRequestsList').html(html);
                        }
                    }
                });
            }

            // Helper function to format date/time
            function formatDateTime(dateTimeString) {
                const date = new Date(dateTimeString);
                return date.toLocaleDateString() + ' ' + date.toLocaleTimeString([], {
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            // Helper function to format date
            function formatDate(dateString) {
                return new Date(dateString).toLocaleDateString();
            }
        });