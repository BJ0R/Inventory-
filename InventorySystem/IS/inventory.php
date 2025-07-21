<?php
// frontend/inventory.php
session_start();
require_once 'db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: account.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISPSC Supply Office - Inventory Management</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
   <link rel="stylesheet" href="css/inventory.css">
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
        <div class="content-wrapper" id="mainContent">
            <div class="container-fluid">
                <h2 class="mb-4">Inventory Management</h2>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Inventory Items</span>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#addItemModal">
                            <i class="bi bi-plus-circle"></i> Add Item
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="search-container">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search items...">
                            <button class="search-button" id="searchButton"><i class="bi bi-search"></i> Search</button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Item Name</th>
                                        <th>Description</th>
                                        <th>Unit</th>
                                        <th>Quantity</th>
                                        <th>Date Added</th>
                                        <th>RIS Number</th>
                                        <th>Supplier</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="inventoryTableBody">
                                    </tbody>
                            </table>
                        </div>
                        
                        <div class="pagination" id="pagination">
                            </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Item Modal -->
        <div class="modal fade" id="addItemModal" tabindex="-1" aria-labelledby="addItemModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="addItemModalLabel">Add New Inventory Item</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="addItemForm">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="addItemName" class="form-label">Item Name *</label>
                                <input type="text" class="form-control" id="addItemName" name="item_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="addDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="addDescription" name="description" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="addUnit" class="form-label">Unit *</label>
                                <input type="text" class="form-control" id="addUnit" name="unit" required>
                            </div>
                            <div class="mb-3">
                                <label for="addQuantity" class="form-label">Quantity *</label>
                                <input type="number" step="0.01" class="form-control" id="addQuantity" name="quantity" required min="0">
                            </div>
                            <div class="mb-3">
                                <label for="addRisNumber" class="form-label">RIS Number</label>
                                <input type="text" class="form-control" id="addRisNumber" name="ris_number">
                            </div>
                            <div class="mb-3">
                                <label for="addSupplier" class="form-label">Supplier</label>
                                <input type="text" class="form-control" id="addSupplier" name="supplier">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Add Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Edit Item Modal -->
        <div class="modal fade" id="editItemModal" tabindex="-1" aria-labelledby="editItemModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="editItemModalLabel">Edit Inventory Item</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="editItemForm">
                        <input type="hidden" id="editItemId" name="id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="editItemName" class="form-label">Item Name *</label>
                                <input type="text" class="form-control" id="editItemName" name="item_name" required>
                            </div>
                            <div class="mb-3">
                                <label for="editDescription" class="form-label">Description</label>
                                <textarea class="form-control" id="editDescription" name="description" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label for="editUnit" class="form-label">Unit *</label>
                                <input type="text" class="form-control" id="editUnit" name="unit" required>
                            </div>
                            <div class="mb-3">
                                <label for="editQuantity" class="form-label">Quantity *</label>
                                <input type="number" step="0.01" class="form-control" id="editQuantity" name="quantity" required min="0">
                            </div>
                            <div class="mb-3">
                                <label for="editRisNumber" class="form-label">RIS Number</label>
                                <input type="text" class="form-control" id="editRisNumber" name="ris_number">
                            </div>
                            <div class="mb-3">
                                <label for="editSupplier" class="form-label">Supplier</label>
                                <input type="text" class="form-control" id="editSupplier" name="supplier">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Withdraw Item Modal -->
        <div class="modal fade" id="withdrawItemModal" tabindex="-1" aria-labelledby="withdrawItemModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="withdrawItemModalLabel">Withdraw Inventory Item</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="withdrawItemForm">
                        <input type="hidden" id="withdrawItemId" name="item_id">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Item Name</label>
                                <p class="form-control-static fw-bold" id="withdrawItemNameDisplay"></p>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Current Quantity</label>
                                <p class="form-control-static" id="withdrawCurrentQuantity"></p>
                            </div>
                            <div class="mb-3">
                                <label for="withdrawWsNumber" class="form-label">WS Number *</label>
                                <input type="text" class="form-control" id="withdrawWsNumber" name="ws_number" required>
                            </div>
                            <div class="mb-3">
                                <label for="withdrawQuantity" class="form-label">Quantity to Withdraw *</label>
                                <input type="number" step="0.01" class="form-control" id="withdrawQuantity" name="quantity_withdrawn" required min="0.01">
                            </div>
                            <div class="mb-3">
                                <label for="withdrawBalance" class="form-label">Balance After Withdrawal</label>
                                <input type="number" step="0.01" class="form-control" id="withdrawBalance" name="balance" readonly>
                            </div>
                            <div class="mb-3">
                                <label for="withdrawDate" class="form-label">Date Withdrawn *</label>
                                <input type="datetime-local" class="form-control" id="withdrawDate" name="date_withdrawn" required>
                            </div>
                            <div class="mb-3">
                                <label for="withdrawRemark" class="form-label">Remarks</label>
                                <textarea class="form-control" id="withdrawRemark" name="remark" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Withdraw Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div id="footer-container">
            <?php include 'components/footer.html'; ?>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/inventory.js"></script>
</body>
</html>