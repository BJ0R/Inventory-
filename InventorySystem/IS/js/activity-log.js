$(document).ready(function() {
            let currentPage = 1;
            let searchQuery = '';
            let actionFilter = '';

            // Load activities on page load
            loadActivities(currentPage, searchQuery, actionFilter);

            // Search button click event
            $('#searchButton').click(function() {
                searchQuery = $('#searchInput').val();
                actionFilter = $('#actionFilter').val();
                currentPage = 1;
                loadActivities(currentPage, searchQuery, actionFilter);
            });

            // Search input enter key event
            $('#searchInput').keypress(function(e) {
                if (e.which === 13) {
                    searchQuery = $('#searchInput').val();
                    actionFilter = $('#actionFilter').val();
                    currentPage = 1;
                    loadActivities(currentPage, searchQuery, actionFilter);
                }
            });

            // Action filter change event
            $('#actionFilter').change(function() {
                searchQuery = $('#searchInput').val();
                actionFilter = $(this).val();
                currentPage = 1;
                loadActivities(currentPage, searchQuery, actionFilter);
            });

            // Function to load activities
            function loadActivities(page, query = '', action = '') {
                $.ajax({
                    url: 'backend/activity-log_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_activities',
                        page: page,
                        search: query,
                        filter_action: action
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            renderActivities(response.data.activities);
                            renderPagination(response.data.totalPages, page);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error loading activities: ' + error, 'danger');
                    }
                });
            }

            // Function to render activities table
            function renderActivities(activities) {
                const tableBody = $('#activityTableBody');
                tableBody.empty();

                if (activities.length === 0) {
                    tableBody.append('<tr><td colspan="6" class="text-center">No activities found</td></tr>');
                    return;
                }

                activities.forEach(activity => {
                    const actionClass = `action-${activity.action}`;
                    const row = `
                        <tr>
                            <td>${activity.id}</td>
                            <td><span class="${actionClass}">${capitalizeFirstLetter(activity.action)}</span></td>
                            <td>${activity.details || 'No details'}</td>
                            <td>${activity.performed_by || 'System'}</td>
                            <td>${formatDateTime(activity.performed_at)}</td>
                        </tr>
                    `;
                    tableBody.append(row);
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
                        loadActivities(currentPage, searchQuery, actionFilter);
                    }
                });
            }

            // Helper function to capitalize first letter
            function capitalizeFirstLetter(string) {
                return string.charAt(0).toUpperCase() + string.slice(1);
            }

            // Helper function to format date/time
            function formatDateTime(dateTimeString) {
                const date = new Date(dateTimeString);
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