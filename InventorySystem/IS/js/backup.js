 $(document).ready(function() {
            // Load backup list on page load
            loadBackupList();

            // Create backup button click event
            $('#createBackupBtn').click(function() {
                createBackup();
            });

            // Download backup button in modal
            $('#downloadBackupBtn').click(function() {
                const backupFile = $(this).data('file');
                if (backupFile) {
                    window.location.href = 'backend/backup_api.php?action=download_backup&file=' + backupFile;
                }
            });

            // Function to create a new backup
            function createBackup() {
                const btn = $('#createBackupBtn');
                const progress = $('#backupProgress');
                
                btn.prop('disabled', true);
                progress.removeClass('d-none');
                
                // Simulate progress (will be replaced with real progress from server)
                let progressPercent = 0;
                const progressInterval = setInterval(() => {
                    progressPercent += 5;
                    $('.progress-bar').css('width', progressPercent + '%');
                    
                    if (progressPercent >= 100) {
                        clearInterval(progressInterval);
                    }
                }, 200);
                
                $.ajax({
                    url: 'backend/backup_api.php',
                    type: 'POST',
                    data: {
                        action: 'create_backup'
                    },
                    dataType: 'json',
                    success: function(response) {
                        clearInterval(progressInterval);
                        $('.progress-bar').css('width', '100%');
                        
                        if (response.success) {
                            showAlert('Backup created successfully!', 'success');
                            loadBackupList();
                            
                            // Update the modal with new backup details
                            $('#backupDetails').html(`
                                <h5>${response.data.filename}</h5>
                                <p><strong>Size:</strong> ${response.data.size}</p>
                                <p><strong>Created:</strong> ${response.data.created_at}</p>
                                <p><strong>Tables backed up:</strong> ${response.data.tables.join(', ')}</p>
                            `);
                            $('#downloadBackupBtn').data('file', response.data.filename);
                            $('#backupInfoModal').modal('show');
                        } else {
                            showAlert(response.message, 'danger');
                        }
                    },
                    error: function(xhr, status, error) {
                        clearInterval(progressInterval);
                        showAlert('Error creating backup: ' + error, 'danger');
                    },
                    complete: function() {
                        btn.prop('disabled', false);
                        setTimeout(() => {
                            progress.addClass('d-none');
                            $('.progress-bar').css('width', '0%');
                        }, 1000);
                    }
                });
            }

            // Function to load backup list
            function loadBackupList() {
                $.ajax({
                    url: 'backend/backup_api.php',
                    type: 'GET',
                    data: {
                        action: 'get_backups'
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            if (response.data.length > 0) {
                                let html = '<div class="list-group">';
                                response.data.forEach(backup => {
                                    html += `
                                        <a href="#" class="list-group-item list-group-item-action backup-item" 
                                           data-file="${backup.filename}" 
                                           data-size="${backup.size}" 
                                           data-date="${backup.created_at}">
                                            <div class="d-flex w-100 justify-content-between">
                                                <h6 class="mb-1">${backup.filename}</h6>
                                                <small>${backup.size}</small>
                                            </div>
                                            <small class="text-muted">${backup.created_at}</small>
                                        </a>
                                    `;
                                });
                                html += '</div>';
                                $('#backupList').html(html);
                                
                                // Add click event to backup items
                                $('.backup-item').click(function(e) {
                                    e.preventDefault();
                                    const file = $(this).data('file');
                                    const size = $(this).data('size');
                                    const date = $(this).data('date');
                                    
                                    $('#backupDetails').html(`
                                        <h5>${file}</h5>
                                        <p><strong>Size:</strong> ${size}</p>
                                        <p><strong>Created:</strong> ${date}</p>
                                    `);
                                    $('#downloadBackupBtn').data('file', file);
                                    $('#backupInfoModal').modal('show');
                                });
                            } else {
                                $('#backupList').html('<p class="text-muted">No backups found</p>');
                            }
                        } else {
                            $('#backupList').html('<p class="text-danger">Error loading backups</p>');
                        }
                    },
                    error: function() {
                        $('#backupList').html('<p class="text-danger">Error loading backups</p>');
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
        });