<?php
// summaryreport.php
session_start();
require_once 'db_connect.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>ISPSC Supply Office - Summary Report</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="css/report.css">
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
                <h2 class="mb-4">Inventory Summary Report</h2>

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Report Filters</span>
                        <button class="btn btn-success" id="downloadPdfBtn">
                            <i class="bi bi-download"></i> Download PDF
                        </button>
                    </div>
                    <div class="card-body">
                        <div class="filter-controls">
                            <div class="filter-group">
                                <label for="filterType">Report Type:</label>
                                <select id="filterType" class="form-select">
                                    <option value="all">All Items</option>
                                    <option value="added">Added Items</option>
                                    <option value="withdrawn">Withdrawn Items</option>
                                </select>
                            </div>
                            
                            <div class="filter-group">
                                <label for="timeRange">Time Range:</label>
                                <select id="timeRange" class="form-select">
                                    <option value="monthly">Monthly</option>
                                    <option value="yearly">Yearly</option>
                                    <option value="custom">Custom Range</option>
                                </select>
                            </div>
                            
                            <div class="filter-group" id="monthGroup">
                                <label for="month">Month:</label>
                                <select id="month" class="form-select">
                                    <option value="01">January</option>
                                    <option value="02">February</option>
                                    <option value="03">March</option>
                                    <option value="04">April</option>
                                    <option value="05">May</option>
                                    <option value="06">June</option>
                                    <option value="07">July</option>
                                    <option value="08">August</option>
                                    <option value="09">September</option>
                                    <option value="10">October</option>
                                    <option value="11">November</option>
                                    <option value="12">December</option>
                                </select>
                                <label for="year" style="margin-left: 10px;">Year:</label>
                                <select id="year" class="form-select">
                                    <?php
                                    $currentYear = date('Y');
                                    for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                        echo "<option value='$y'" . ($y == $currentYear ? ' selected' : '') . ">$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="filter-group" id="yearGroup" style="display: none;">
                                <label for="yearOnly">Year:</label>
                                <select id="yearOnly" class="form-select">
                                    <?php
                                    for ($y = $currentYear; $y >= $currentYear - 5; $y--) {
                                        echo "<option value='$y'" . ($y == $currentYear ? ' selected' : '') . ">$y</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            
                            <div class="filter-group" id="customRangeGroup" style="display: none;">
                                <label for="startDate">From:</label>
                                <input type="date" id="startDate" class="form-control">
                                <label for="endDate" style="margin-left: 10px;">To:</label>
                                <input type="date" id="endDate" class="form-control">
                            </div>
                        </div>
                        
                        <div class="search-container">
                            <input type="text" id="searchInput" class="search-input" placeholder="Search items...">
                            <button class="search-button" id="searchButton"><i class="bi bi-search"></i> Search</button>
                        </div>
                        
                        <div class="table-responsive">
                            <table class="table" id="summaryTable">
                                <thead>
                                    <tr id="tableHeaders">
                                        <!-- Headers will be populated by JavaScript based on filter type -->
                                    </tr>
                                </thead>
                                <tbody id="summaryTableBody">
                                    <!-- Data will be loaded via AJAX -->
                                </tbody>
                            </table>
                        </div>
                        
                        <div class="pagination-container">
                            <div class="pagination" id="pagination">
                                <!-- Pagination will be loaded via AJAX -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- PDF Preview Modal -->
        <div class="modal fade" id="pdfPreviewModal" tabindex="-1" aria-labelledby="pdfPreviewModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="pdfPreviewModalLabel">PDF Preview</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body" id="pdfPreviewContent">
                        <!-- PDF content will be loaded here -->
                        <div id="pdfPrintContainer"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="button" class="btn btn-primary no-print" id="printPdfBtn">
                            <i class="bi bi-printer"></i> Print
                        </button>
                        <button type="button" class="btn btn-success no-print" id="confirmDownloadPdfBtn">
                            <i class="bi bi-download"></i> Download PDF
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div id="footer-container">
            <?php include 'components/footer.html'; ?>
        </div>
        <!-- bot -->
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="js/summaryreport.js"> </script>
</body>
</html>