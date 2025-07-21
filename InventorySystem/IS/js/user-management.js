
        $(document).ready(function() {
            let currentPage = 1;
            let searchQuery = '';
            let userIdToDelete = null;
            let addUserModal = new bootstrap.Modal(document.getElementById('addUserModal'));
            let editUserModal = new bootstrap.Modal(document.getElementById('editUserModal'));
            let deleteUserModal = new bootstrap.Modal(document.getElementById('deleteUserModal'));

            // Load users on page load
            loadUsers(currentPage, searchQuery);

            // Search button click event
            $('#searchButton').click(function() {
                searchQuery = $('#searchInput').val();
                currentPage = 1;
                loadUsers(currentPage, searchQuery);
            });

            // Search input enter key event
            $('#searchInput').keypress(function(e) {
                if (e.which === 13) {
                    searchQuery = $('#searchInput').val();
                    currentPage = 1;
                    loadUsers(currentPage, searchQuery);
                }
            });

            // Add user form submission
            $('#addUserForm').submit(function(e) {
                e.preventDefault();
                const submitBtn = $('#addUserSubmitBtn');
                submitBtn.prop('disabled', true);
                submitBtn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Adding...');
                
                $.ajax({
                    url: 'backend/user-management_api.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=add_user',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Properly hide the modal and clean up backdrop
                            hideModalProperly(addUserModal);
                            $('#addUserForm')[0].reset();
                            loadUsers(currentPage, searchQuery);
                            showAlert('User added successfully!', 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error adding user: ' + error, 'danger');
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false);
                        submitBtn.html('Add User');
                    }
                });
            });

            // Edit user form submission
            $('#editUserForm').submit(function(e) {
                e.preventDefault();
                $.ajax({
                    url: 'backend/user-management_api.php',
                    type: 'POST',
                    data: $(this).serialize() + '&action=update_user',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            // Properly hide the modal and clean up backdrop
                            hideModalProperly(editUserModal);
                            loadUsers(currentPage, searchQuery);
                            showAlert('User updated successfully!', 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error updating user: ' + error, 'danger');
                    }
                });
            });

            // Delete confirmation
            $('#confirmDelete').click(function() {
                if (userIdToDelete) {
                    $.ajax({
                        url: 'backend/user-management_api.php',
                        type: 'POST',
                        data: {
                            action: 'delete_user',
                            id: userIdToDelete
                        },
                        dataType: 'json',
                        success: function(response) {
                            if (response.success) {
                                // Properly hide the modal and clean up backdrop
                                hideModalProperly(deleteUserModal);
                                loadUsers(currentPage, searchQuery);
                                showAlert('User deleted successfully!', 'success');
                            } else {
                                showAlert(response.message, 'danger');
                            }
                        },
                        error: function(xhr, status, error) {
                            showAlert('Error deleting user: ' + error, 'danger');
                        }
                    });
                }
            });

            // Function to properly hide modal and clean up backdrop
            function hideModalProperly(modalInstance) {
                // Hide the modal
                modalInstance.hide();
                
                // Force remove backdrop and modal-open class after a short delay
                setTimeout(function() {
                    // Remove any remaining backdrop elements
                    $('.modal-backdrop').remove();
                    
                    // Remove modal-open class from body to restore scrolling
                    $('body').removeClass('modal-open');
                    
                    // Reset body padding and overflow styles that Bootstrap might have set
                    $('body').css({
                        'padding-right': '',
                        'overflow': ''
                    });
                }, 300); // Wait for Bootstrap's transition to complete
            }

            // Function to load users
            function loadUsers(page, query = '') {
                $.ajax({
                    url: 'backend/user-management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_users',
                        page: page,
                        search: query
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            renderUsers(response.data.users);
                            renderPagination(response.data.totalPages, page);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error loading users: ' + error, 'danger');
                    }
                });
            }

            // Function to render users table
            function renderUsers(users) {
                const tableBody = $('#usersTableBody');
                tableBody.empty();

                if (users.length === 0) {
                    tableBody.append('<tr><td colspan="6" class="text-center">No users found</td></tr>');
                    return;
                }

                users.forEach(user => {
                    const statusClass = user.status === 'active' ? 'status-active' : 
                                      (user.status === 'inactive' ? 'status-inactive' : 'status-pending');
                    const roleClass = user.role === 'admin' ? 'role-admin' : 'role-user';

                    const row = `
                        <tr>
                            <td>${user.id}</td>
                            <td>${user.username}</td>
                            <td>${user.email}</td>
                            <td><span class="${roleClass}">${user.role}</span></td>
                            <td><span class="${statusClass}">${user.status}</span></td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-btn" data-id="${user.id}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-danger btn-sm delete-btn" data-id="${user.id}" data-name="${user.username}">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });

                // Add event listeners to edit buttons
                $('.edit-btn').click(function() {
                    const userId = $(this).data('id');
                    editUser(userId);
                });

                // Add event listeners to delete buttons
                $('.delete-btn').click(function() {
                    userIdToDelete = $(this).data('id');
                    $('#deleteUserName').text('User: ' + $(this).data('name'));
                    deleteUserModal.show();
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
                for (let i = 1; i <= totalPages; i++) {
                    const active = i === currentPage ? 'active' : '';
                    pagination.append(`
                        <a href="#" class="page-link ${active}" data-page="${i}">${i}</a>
                    `);
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
                        loadUsers(currentPage, searchQuery);
                    }
                });
            }

            // Function to edit a user
            function editUser(userId) {
                $.ajax({
                    url: 'backend/user-management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_user',
                        id: userId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const user = response.data;
                            $('#editUserId').val(user.id);
                            $('#editUsername').val(user.username);
                            $('#editEmail').val(user.email);
                            $('#editRole').val(user.role);
                            $('#editStatus').val(user.status);
                            editUserModal.show();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error loading user: ' + error, 'danger');
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

            // Enhanced modal cleanup when closed manually
            $('.modal').on('hidden.bs.modal', function() {
                // Reset form
                $(this).find('form')[0].reset();
                
                // Ensure backdrop is completely removed
                setTimeout(function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css({
                        'padding-right': '',
                        'overflow': ''
                    });
                }, 100);
            });

            // Additional cleanup on page visibility change (handles edge cases)
            $(document).on('visibilitychange', function() {
                if (!document.hidden) {
                    // Clean up any lingering modal artifacts when page becomes visible
                    if (!$('.modal.show').length) {
                        $('.modal-backdrop').remove();
                        $('body').removeClass('modal-open');
                        $('body').css({
                            'padding-right': '',
                            'overflow': ''
                        });
                    }
                }
            });
        });
