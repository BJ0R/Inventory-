<?php
// frontend/backup.php
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
    <title>ISPSC Supply Office - Database Backup</title>
    <link
      rel="stylesheet"
      href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    />
    <link
      href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
      rel="stylesheet"
    />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css"
    />
    <link rel="stylesheet" href="css/backup.css" />
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
          <h2 class="mb-4">Database Backup</h2>

          <div class="row">
            <div class="col-md-6">
              <div class="card backup-card">
                <div class="card-body text-center">
                  <div class="backup-icon">
                    <i class="bi bi-database"></i>
                  </div>
                  <h4 class="card-title">Create Backup</h4>
                  <p class="card-text">
                    Generate a complete backup of the database
                  </p>
                  <button class="btn btn-primary" id="createBackupBtn">
                    <i class="bi bi-download"></i> Create Backup Now
                  </button>
                  <div class="progress d-none" id="backupProgress">
                    <div
                      class="progress-bar progress-bar-striped progress-bar-animated"
                      role="progressbar"
                      style="width: 0%"
                    ></div>
                  </div>
                </div>
              </div>
            </div>

            <div class="col-md-6">
              <div class="card backup-card">
                <div class="card-body text-center">
                  <div class="backup-icon">
                    <i class="bi bi-clock-history"></i>
                  </div>
                  <h4 class="card-title">Recent Backups</h4>
                  <div id="backupList">
                    <p class="text-muted">Loading backup history...</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="card mt-4">
            <div class="card-header">Backup Information</div>
            <div class="card-body">
              <div class="alert alert-info">
                <i class="bi bi-info-circle"></i>
                <strong>Important:</strong> Regular database backups are
                essential for data protection. We recommend creating backups
                before making major changes to the system.
              </div>
              <ul class="list-group list-group-flush">
                <li class="list-group-item">
                  <i class="bi bi-check-circle text-success"></i>
                  Backups include all tables: inventory, users, requests, and
                  activity logs
                </li>
                <li class="list-group-item">
                  <i class="bi bi-check-circle text-success"></i>
                  Backups are stored in the server's backup directory
                </li>
                <li class="list-group-item">
                  <i class="bi bi-check-circle text-success"></i>
                  Each backup is timestamped for easy identification
                </li>
              </ul>
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

    <!-- Backup Info Modal -->
    <div
      class="modal fade"
      id="backupInfoModal"
      tabindex="-1"
      aria-labelledby="backupInfoModalLabel"
      aria-hidden="true"
    >
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header text-white">
            <h5 class="modal-title" id="backupInfoModalLabel">
              Backup Details
            </h5>
            <button
              type="button"
              class="btn-close btn-close-white"
              data-bs-dismiss="modal"
              aria-label="Close"
            ></button>
          </div>
          <div class="modal-body" id="backupDetails">
            Loading backup details...
          </div>
          <div class="modal-footer">
            <button
              type="button"
              class="btn btn-secondary"
              data-bs-dismiss="modal"
            >
              Close
            </button>
            <button
              type="button"
              class="btn btn-primary"
              id="downloadBackupBtn"
            >
              <i class="bi bi-download"></i> Download
            </button>
          </div>
        </div>
      </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/backup.js"></script>
  </body>
</html>
