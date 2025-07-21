<?php
// Get current page name for active state
$current_page = basename($_SERVER['PHP_SELF']);
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.8.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">

<style>
    :root {
        --primary-color: #520000;
        --secondary-color: #8f0002;
        --accent-color: hsl(47, 100%, 50%);
        --text-light: #ffffff;
        --sidebar-width: 250px;
        --sidebar-collapsed-width: 70px;
    }

    body {
        margin: 0;
        padding: 0;
        font-family: 'Times New Roman', serif;
        overflow-x: hidden; /* Prevent horizontal scrolling */
    }

    .sidebar.active ~ .main-content,
    .sidebar.active ~ footer {
        transform: translateX(var(--sidebar-width));
    }
    
    /* Ensure content stays above overlay */
    .main-content, footer {
        position: relative;
        z-index: 2;
        width: 100%;
        box-sizing: border-box; /* Include padding in width calculation */
    }

    /* Sidebar Styles */
    .sidebar {
        width: var(--sidebar-width);
        background-color: var(--primary-color);
        color: var(--text-light);
        position: fixed;
        height: 100vh;
        overflow-y: auto;
        overflow-x: hidden; /* Prevent horizontal scroll in sidebar */
        transition: all 0.3s ease;
        z-index: 1000;
        display: flex;
        flex-direction: column;
        left: 0;
        top: 0;
    }

    .sidebar.collapsed {
        width: var(--sidebar-collapsed-width);
    }

    .sidebar.collapsed .sidebar-brand span,
    .sidebar.collapsed .nav-link span,
    .sidebar.collapsed .logout-btn span,
    .sidebar.collapsed .admin-label {
        display: none;
    }

    .sidebar.collapsed .sidebar-brand {
        justify-content: center;
        padding: 1rem 0;
    }

    .sidebar.collapsed .sidebar-brand img {
        margin-right: 0;
    }

    .sidebar.collapsed .nav-link {
        justify-content: center;
        padding: 0.75rem;
    }

    .sidebar.collapsed .nav-link i {
        margin-right: 0;
        font-size: 1.4rem;
    }

    .sidebar.collapsed .logout-btn {
        justify-content: center;
    }

    .sidebar.collapsed .logout-btn i {
        margin-right: 0;
        font-size: 1.4rem;
    }

    .sidebar-brand {
        display: flex;
        align-items: center;
        padding: 1rem;
        margin-bottom: 1rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.3s ease;
    }

    .sidebar-brand img {
        height: 40px;
        margin-right: 10px;
        transition: all 0.3s ease;
    }

    .sidebar-brand span {
        white-space: nowrap;
        transition: all 0.3s ease;
        font-weight: bold;
    }

    .sidebar-nav {
        list-style: none;
        padding: 0;
        margin: 0;
        flex-grow: 1;
        width: 100%; /* Ensure nav takes full width */
    }

    .nav-item {
        margin-bottom: 0.5rem;
        width: 100%; /* Ensure items take full width */
    }

    .nav-link {
        display: flex;
        align-items: center;
        padding: 0.75rem 1rem;
        color: var(--text-light);
        text-decoration: none;
        border-radius: 4px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
        width: calc(100% - 2rem); /* Account for padding */
        margin: 0 0.5rem; /* Add some margin to prevent touching edges */
    }

    .nav-link::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 0;
        background: linear-gradient(90deg, var(--accent-color), rgba(255, 215, 0, 0.3));
        transition: width 0.3s ease;
        z-index: -1;
    }

    .nav-link:hover::before {
        width: 100%;
    }

    .nav-link i {
        margin-right: 10px;
        font-size: 1.2rem;
        min-width: 20px;
        transition: all 0.3s ease;
    }

    .nav-link:hover {
        color: var(--primary-color);
        transform: translateX(5px);
    }

    /* Admin Section */
    .admin-section {
        border-top: 1px solid rgba(255, 255, 255, 0.1);
        margin-top: auto;
        padding-top: 1rem;
        width: 100%; /* Ensure full width */
    }

    .admin-label {
        padding: 0.5rem 1rem;
        font-size: 0.8rem;
        color: rgba(255, 255, 255, 0.6);
        text-transform: uppercase;
        letter-spacing: 1px;
        transition: all 0.3s ease;
        width: calc(100% - 2rem); /* Account for padding */
    }

    .logout-btn {
        padding: 0.75rem;
        margin: 1rem;
        background-color: var(--secondary-color);
        color: white;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
        width: calc(100% - 2rem); /* Account for margin */
    }

    .logout-btn::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 0;
        background: linear-gradient(90deg, #ff4444, #cc0000);
        transition: width 0.3s ease;
        z-index: -1;
    }

    .logout-btn:hover::before {
        width: 100%;
    }

    .logout-btn i {
        margin-right: 8px;
        transition: all 0.3s ease;
    }

    .logout-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    /* Content Wrapper */
    .content-wrapper {
        margin-left: var(--sidebar-width);
        transition: margin-left 0.3s ease;
        padding: 20px;
        min-height: 100vh;
        width: calc(100% - var(--sidebar-width)); /* Ensure proper width calculation */
        box-sizing: border-box; /* Include padding in width */
    }

    .content-wrapper.collapsed {
        margin-left: var(--sidebar-collapsed-width);
        width: calc(100% - var(--sidebar-collapsed-width));
    }

    /* Toggle Button */
    .sidebar-toggle {
        position: fixed;
        top: 20px;
        left: 20px;
        background-color: var(--primary-color);
        color: var(--text-light);
        border: none;
        border-radius: 4px;
        padding: 0.5rem;
        cursor: pointer;
        z-index: 1001;
        transition: all 0.3s ease;
        display: none;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
    }

    .sidebar-toggle:hover {
        background-color: var(--secondary-color);
        transform: scale(1.1);
    }

    .sidebar-toggle.moved {
        left: calc(var(--sidebar-width) + 20px);
    }

    .sidebar-toggle.collapsed-moved {
        left: calc(var(--sidebar-collapsed-width) + 20px);
    }

    /* Sidebar Overlay for mobile */
    .sidebar-overlay {
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background-color: rgba(0, 0, 0, 0.5);
        z-index: 999;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .sidebar-overlay.active {
        display: block;
        opacity: 1;
    }

    /* Responsive Styles */
    
    /* Large screens - no toggle, full sidebar, no space */
    @media (min-width: 1025px) {
        .sidebar-toggle {
            display: none;
        }
        
        .content-wrapper {
            margin-left: var(--sidebar-width);
            width: calc(100% - var(--sidebar-width));
        }
        
        .sidebar {
            width: var(--sidebar-width);
        }
    }

    /* Medium screens (768px) - collapsed sidebar with icons, no toggle */
    @media (max-width: 1024px) and (min-width: 769px) {
        .sidebar {
            width: var(--sidebar-collapsed-width);
        }
        
        .sidebar .sidebar-brand span,
        .sidebar .nav-link span,
        .sidebar .logout-btn span,
        .sidebar .admin-label {
            display: none;
        }
        
        .sidebar .sidebar-brand {
            justify-content: center;
            padding: 1rem 0;
        }
        
        .sidebar .sidebar-brand img {
            margin-right: 0;
        }
        
        .sidebar .nav-link {
            justify-content: center;
            padding: 0.75rem;
        }
        
        .sidebar .nav-link i {
            margin-right: 0;
            font-size: 1.4rem;
        }
        
        .sidebar .logout-btn {
            justify-content: center;
        }
        
        .sidebar .logout-btn i {
            margin-right: 0;
            font-size: 1.4rem;
        }
        
        .content-wrapper {
            margin-left: var(--sidebar-collapsed-width);
            width: calc(100% - var(--sidebar-collapsed-width));
        }
        
        .sidebar-toggle {
            display: none;
        }
    }

    /* Small screens (425px and below) - hidden sidebar with toggle */
    @media (max-width: 768px) {
        .sidebar {
            left: -250px;
            width: var(--sidebar-width);
        }
        
        .sidebar.active {
            left: 0;
        }
        
        .sidebar .sidebar-brand span,
        .sidebar .nav-link span,
        .sidebar .logout-btn span,
        .sidebar .admin-label {
            display: block;
        }
        
        .sidebar .sidebar-brand {
            justify-content: flex-start;
            padding: 1rem;
        }
        
        .sidebar .sidebar-brand img {
            margin-right: 10px;
        }
        
        .sidebar .nav-link {
            justify-content: flex-start;
            padding: 0.75rem 1rem;
        }
        
        .sidebar .nav-link i {
            margin-right: 10px;
            font-size: 1.2rem;
        }
        
        .sidebar .logout-btn {
            justify-content: center;
        }
        
        .sidebar .logout-btn i {
            margin-right: 8px;
            font-size: 1rem;
        }
        
        .content-wrapper {
            margin-left: 0;
            width: 100%;
        }
        
        .sidebar-toggle {
            display: block;
        }
        
        .sidebar-toggle.moved {
            left: calc(var(--sidebar-width) + 20px);
        }
    }
</style>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<button class="sidebar-toggle" id="sidebarToggle">
    <i class="fas fa-bars"></i>
</button>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <img src="../images/logo.png" alt="ISPSC Logo" />
        <span>ISPSC - TAGUDIN</span>
    </div>

    <ul class="sidebar-nav">
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'admin-dashboard.php' ? 'active' : ''; ?>" href="admin-dashboard.php">
                <i class="fas fa-dashboard"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'summaryreport.php' ? 'active' : ''; ?>" href="summaryreport.php">
                <i class="fas fa-file-alt"></i>
                <span>Summary Report</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'inventory.php' ? 'active' : ''; ?>" href="inventory.php">
                <i class="fas fa-boxes"></i>
                <span>Inventory</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'activity-log.php' ? 'active' : ''; ?>" href="activity-log.php">
                <i class="fas fa-clipboard-list"></i>
                <span>Activity</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?php echo $current_page == 'admin-request.php' ? 'active' : ''; ?>" href="admin-request.php">
                <i class="fas fa-paper-plane"></i>
                <span>Requests</span>
            </a>
        </li>
    </ul>

    <div class="admin-section">
        <div class="admin-label">Admin</div>
        <ul class="sidebar-nav">
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'user-management.php' ? 'active' : ''; ?>" href="user-management.php">
                    <i class="fas fa-user-cog"></i>
                    <span>User Management</span>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $current_page == 'backup.php' ? 'active' : ''; ?>" href="backup.php">
                    <i class="fas fa-hdd"></i>
                    <span>Backup</span>
                </a>
            </li>
        </ul>
    </div>

    <button class="logout-btn" onclick="window.location.href='logout.php'">
        <i class="bi bi-box-arrow-right"></i>
        <span>Logout</span>
    </button>
</aside>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const mainContent = document.querySelector('.main-content') || document.getElementById('mainContent');
    const mainFooter = document.querySelector('footer') || document.getElementById('mainFooter');

    // Toggle functionality
    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function() {
            const isActive = sidebar.classList.toggle('active');
            sidebarOverlay.classList.toggle('active');
            sidebarToggle.classList.toggle('moved');
            
            // Update main content and footer for mobile
            if (window.innerWidth <= 768) {
                if (isActive) {
                    if (mainContent) mainContent.classList.add('no-sidebar');
                    if (mainFooter) mainFooter.classList.add('no-sidebar');
                } else {
                    if (mainContent) mainContent.classList.remove('no-sidebar');
                    if (mainFooter) mainFooter.classList.remove('no-sidebar');
                }
            }
        });
    }

    // Close sidebar when clicking overlay
    if (sidebarOverlay) {
        sidebarOverlay.addEventListener('click', function() {
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            sidebarToggle.classList.remove('moved');
            if (mainContent) mainContent.classList.remove('no-sidebar');
            if (mainFooter) mainFooter.classList.remove('no-sidebar');
        });
    }

    // Handle window resize
    function handleResize() {
        if (window.innerWidth > 768) {
            // Desktop - ensure normal state
            sidebar.classList.remove('active');
            sidebarOverlay.classList.remove('active');
            sidebarToggle.classList.remove('moved');
            if (mainContent) mainContent.classList.remove('no-sidebar');
            if (mainFooter) mainFooter.classList.remove('no-sidebar');
            
            // Check if we should be in collapsed mode (medium screens)
            if (window.innerWidth <= 1024) {
                sidebar.classList.add('collapsed');
                if (mainContent) mainContent.classList.add('collapsed');
                if (mainFooter) mainFooter.classList.add('collapsed');
            } else {
                sidebar.classList.remove('collapsed');
                if (mainContent) mainContent.classList.remove('collapsed');
                if (mainFooter) mainFooter.classList.remove('collapsed');
            }
        } else {
            // Mobile - ensure content takes full width when sidebar hidden
            sidebar.classList.remove('collapsed');
            if (mainContent) mainContent.classList.remove('collapsed');
            if (mainFooter) mainFooter.classList.remove('collapsed');
            
            if (!sidebar.classList.contains('active')) {
                if (mainContent) mainContent.classList.add('no-sidebar');
                if (mainFooter) mainFooter.classList.add('no-sidebar');
            }
        }
    }

    // Initial check
    handleResize();
    
    // Add resize listener
    window.addEventListener('resize', handleResize);
});
</script>


