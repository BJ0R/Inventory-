 $(document).ready(function() {
            let currentPage = 1;
            let searchQuery = '';
            let itemIdToWithdraw = null;
            
            // Initialize Bootstrap modal instances
            let addItemModal = new bootstrap.Modal(document.getElementById('addItemModal'));
            let editItemModal = new bootstrap.Modal(document.getElementById('editItemModal'));
            let withdrawItemModal = new bootstrap.Modal(document.getElementById('withdrawItemModal'));

            // Load inventory on page load
            loadInventory(currentPage, searchQuery);

            // Search button click event
            $('#searchButton').click(function() {
                searchQuery = $('#searchInput').val();
                currentPage = 1;
                loadInventory(currentPage, searchQuery);
            });

            // Search input enter key event
            $('#searchInput').keypress(function(e) {
                if (e.which === 13) {
                    searchQuery = $('#searchInput').val();
                    currentPage = 1;
                    loadInventory(currentPage, searchQuery);
                }
            });

            // Add item form submission
            $('#addItemForm').submit(function(e) {
                e.preventDefault();
                addItem();
            });

            // Edit item form submission
            $('#editItemForm').submit(function(e) {
                e.preventDefault();
                updateItem();
            });

            // Withdraw item form submission
            $('#withdrawItemForm').submit(function(e) {
                e.preventDefault();
                withdrawItem();
            });

            // Calculate balance when quantity changes
            $('#withdrawQuantity').on('input', function() {
                calculateBalance();
            });

            // Generate RIS number when modal is shown
            $('#addItemModal').on('shown.bs.modal', function() {
                generateRisNumber();
            });

            // Generate WS number when modal is shown
            $('#withdrawItemModal').on('shown.bs.modal', function() {
                generateWsNumber();
            });

            // Function to properly hide modal and clean up backdrop
            function hideModalProperly(modalInstance) {
                modalInstance.hide();
                
                setTimeout(function() {
                    $('.modal-backdrop').remove();
                    $('body').removeClass('modal-open');
                    $('body').css({
                        'padding-right': '',
                        'overflow': ''
                    });
                }, 300);
            }

            // Function to generate RIS number
            function generateRisNumber() {
                $.ajax({
                    url: 'backend/inventory-management_api.php',
                    type: 'GET',
                    data: {
                        action: 'generate_ris_number'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#addRisNumber').val(response.ris_number);
                        }
                    }
                });
            }

            // Function to generate WS number
            function generateWsNumber() {
                $.ajax({
                    url: 'backend/inventory-management_api.php',
                    type: 'GET',
                    data: {
                        action: 'generate_ws_number'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#withdrawWsNumber').val(response.ws_number);
                        }
                    }
                });
            }

            // Function to load inventory
            function loadInventory(page, query = '') {
                $.ajax({
                    url: 'backend/inventory-management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_inventory',
                        page: page,
                        search: query
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            renderInventory(response.data.items);
                            renderPagination(response.data.totalPages, page);
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error loading inventory: ' + error, 'danger');
                    }
                });
            }

            // Function to render inventory table
            function renderInventory(items) {
                const tableBody = $('#inventoryTableBody');
                tableBody.empty();

                if (items.length === 0) {
                    tableBody.append('<tr><td colspan="9" class="text-center">No inventory items found</td></tr>');
                    return;
                }

                items.forEach(item => {
                    const quantity = parseFloat(item.quantity);
                    const quantityClass = quantity < 5 ? (quantity < 1 ? 'very-low-quantity' : 'low-quantity') : '';

                    const row = `
                        <tr class="${quantityClass}">
                            <td>${item.id}</td>
                            <td>${item.item_name}</td>
                            <td>${item.description || '-'}</td>
                            <td>${item.unit}</td>
                            <td>${item.quantity}</td>
                            <td>${formatDate(item.date_added)}</td>
                            <td>${item.ris_number || '-'}</td>
                            <td>${item.supplier || '-'}</td>
                            <td class="actions-cell">
                                <button class="btn btn-warning btn-sm edit-btn" data-id="${item.id}">
                                    <i class="bi bi-pencil"></i> Edit
                                </button>
                                <button class="btn btn-info btn-sm withdraw-btn" data-id="${item.id}" data-name="${item.item_name}" data-quantity="${item.quantity}">
                                    <i class="bi bi-box-arrow-right"></i> Withdraw
                                </button>
                            </td>
                        </tr>
                    `;
                    tableBody.append(row);
                });

                // Add event listeners to edit buttons
                $('.edit-btn').click(function() {
                    const itemId = $(this).data('id');
                    editItem(itemId);
                });

                // Add event listeners to withdraw buttons
                $('.withdraw-btn').click(function() {
                    itemIdToWithdraw = $(this).data('id');
                    $('#withdrawItemNameDisplay').text($(this).data('name'));
                    $('#withdrawCurrentQuantity').text($(this).data('quantity'));
                    $('#withdrawItemId').val(itemIdToWithdraw);
                    
                    const now = new Date();
                    const formattedDateTime = now.toISOString().slice(0, 16);
                    $('#withdrawDate').val(formattedDateTime);
                    
                    withdrawItemModal.show();
                });
            }

            // Function to render pagination
            function renderPagination(totalPages, currentPage) {
                const pagination = $('#pagination');
                pagination.empty();

                if (totalPages <= 1) return;

                const prevDisabled = currentPage <= 1 ? 'disabled' : '';
                pagination.append(`
                    <a href="#" class="page-link ${prevDisabled}" data-page="${currentPage - 1}">
                        &laquo; Previous
                    </a>
                `);

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

                const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
                pagination.append(`
                    <a href="#" class="page-link ${nextDisabled}" data-page="${currentPage + 1}">
                        Next &raquo;
                    </a>
                `);

                $('.page-link').click(function(e) {
                    e.preventDefault();
                    if (!$(this).hasClass('disabled') && !$(this).hasClass('active')) {
                        currentPage = parseInt($(this).data('page'));
                        loadInventory(currentPage, searchQuery);
                    }
                });
            }

            // Function to add a new item
            function addItem() {
                const formData = $('#addItemForm').serialize();
                
                $.ajax({
                    url: 'backend/inventory-management_api.php',
                    type: 'POST',
                    data: formData + '&action=add_item',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            hideModalProperly(addItemModal);
                            $('#addItemForm')[0].reset();
                            loadInventory(currentPage, searchQuery);
                            showAlert('Item added successfully!', 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error adding item: ' + error, 'danger');
                    }
                });
            }

            // Function to edit an item
            function editItem(itemId) {
                $.ajax({
                    url: 'backend/inventory-management_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_item',
                        id: itemId
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            const item = response.data;
                            $('#editItemId').val(item.id);
                            $('#editItemName').val(item.item_name);
                            $('#editDescription').val(item.description);
                            $('#editUnit').val(item.unit);
                            $('#editQuantity').val(item.quantity);
                            $('#editRisNumber').val(item.ris_number);
                            $('#editSupplier').val(item.supplier);
                            editItemModal.show();
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error loading item: ' + error, 'danger');
                    }
                });
            }

            // Function to update an item
            function updateItem() {
                const formData = $('#editItemForm').serialize();
                
                $.ajax({
                    url: 'backend/inventory-management_api.php',
                    type: 'POST',
                    data: formData + '&action=update_item',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            hideModalProperly(editItemModal);
                            loadInventory(currentPage, searchQuery);
                            showAlert('Item updated successfully!', 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error updating item: ' + error, 'danger');
                    }
                });
            }

            // Function to withdraw an item
            function withdrawItem() {
                const currentQuantity = parseFloat($('#withdrawCurrentQuantity').text());
                const quantityToWithdraw = parseFloat($('#withdrawQuantity').val());

                if (quantityToWithdraw <= 0 || isNaN(quantityToWithdraw)) {
                    showAlert('Please enter a valid quantity to withdraw.', 'danger');
                    return;
                }

                if (quantityToWithdraw > currentQuantity) {
                    showAlert('Quantity to withdraw cannot be greater than current quantity.', 'danger');
                    return;
                }

                const formData = $('#withdrawItemForm').serialize();
                
                $.ajax({
                    url: 'backend/inventory-management_api.php',
                    type: 'POST',
                    data: formData + '&action=withdraw_item',
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            hideModalProperly(withdrawItemModal);
                            $('#withdrawItemForm')[0].reset();
                            loadInventory(currentPage, searchQuery);
                            showAlert('Item withdrawn successfully!', 'success');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        showAlert('Error withdrawing item: ' + error, 'danger');
                    }
                });
            }

            // Function to calculate balance
            function calculateBalance() {
                const currentQuantity = parseFloat($('#withdrawCurrentQuantity').text());
                const quantityToWithdraw = parseFloat($('#withdrawQuantity').val());
                let balance = currentQuantity - quantityToWithdraw;
                if (isNaN(balance) || balance < 0) {
                    balance = 0;
                }
                $('#withdrawBalance').val(balance.toFixed(2));
            }

            // Function to format date
            function formatDate(dateString) {
                const options = { year: 'numeric', month: 'numeric', day: 'numeric', hour: '2-digit', minute: '2-digit' };
                return new Date(dateString).toLocaleDateString(undefined, options);
            }

            // Function to display alerts
            function showAlert(message, type) {
                const alertDiv = `<div class="alert alert-${type} alert-dismissible fade show fixed-top m-3" role="alert" style="z-index: 1060;">
                                    ${message}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                  </div>`;
                $('body').append(alertDiv);
                setTimeout(() => {
                    $('.alert').alert('close');
                }, 5000);
            }
        });