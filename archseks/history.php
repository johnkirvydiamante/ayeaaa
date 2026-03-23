<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];
$query = mysqli_query($conn, "SELECT * FROM reservations WHERE student_id = '$uid' ORDER BY date DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History | CCS Sit-in Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        :root {
            --ccs-purple-dark: #3a235c;
            --ccs-purple: #4b2c82;
            --ccs-gold: #ffcc00;
            --text-muted: #64748b;
            --shadow-lg: 0 20px 35px -10px rgba(0,0,0,0.15);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f5 100%);
            min-height: 100vh;
        }
        .professional-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 1px solid rgba(75,44,130,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 40px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            height: 80px;
        }
        .logo-area { display: flex; align-items: center; gap: 12px; }
        .uc-logo { width: 50px; height: 50px; object-fit: contain; }
        .system-title { display: flex; flex-direction: column; }
        .system-title h1 { font-size: 1rem; font-weight: 600; color: var(--ccs-purple); }
        .system-title h1 span { font-weight: 400; color: var(--text-muted); }
        .system-title .college-name { font-size: 0.7rem; color: var(--text-muted); }
        .nav-links { display: flex; gap: 32px; align-items: center; }
        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
        }
        .nav-links a:hover { color: var(--ccs-purple); }
        .nav-links a.active { color: var(--ccs-purple); font-weight: 600; }
        .btn-logout {
            background: var(--ccs-gold);
            color: var(--ccs-purple-dark) !important;
            padding: 8px 20px !important;
            border-radius: 40px;
            font-weight: 700 !important;
        }
        .history-container { max-width: 1300px; margin: 40px auto; padding: 0 20px; }
        .history-card {
            background: white;
            border-radius: 32px;
            box-shadow: var(--shadow-lg);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%);
            color: white;
            padding: 28px 32px;
        }
        .card-header h2 { font-size: 1.8rem; font-weight: 700; display: flex; align-items: center; gap: 12px; }
        .card-header h2 i { color: var(--ccs-gold); }
        .card-body { padding: 32px; }
        .table-controls { display: flex; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 16px; }
        .search-box {
            padding: 8px 14px; border: 1.5px solid #e2e8f0;
            border-radius: 40px; font-family: 'Inter', sans-serif;
            width: 250px;
        }
        .data-table { width: 100%; border-collapse: collapse; font-size: 0.85rem; }
        .data-table thead { background: linear-gradient(135deg, var(--ccs-purple) 0%, #6b4c9e 100%); color: white; }
        .data-table th, .data-table td { padding: 14px 16px; text-align: left; border-bottom: 1px solid #eef2f6; }
        .data-table tbody tr:hover { background: #fff0b5; }
        .status-badge {
            display: inline-block; padding: 4px 12px;
            border-radius: 40px; font-size: 0.7rem; font-weight: 600;
        }
        .status-completed { background: #10b981; color: white; }
        .status-active { background: var(--ccs-gold); color: var(--ccs-purple-dark); }
        .view-btn {
            background: transparent; border: 1.5px solid var(--ccs-purple);
            color: var(--ccs-purple); padding: 6px 14px;
            border-radius: 40px; font-size: 0.75rem; font-weight: 600;
            cursor: pointer;
        }
        .view-btn:hover { background: var(--ccs-purple); color: white; }
        @media (max-width: 768px) {
            .header-container { flex-direction: column; height: auto; padding: 12px 20px; }
            .nav-links { flex-wrap: wrap; justify-content: center; gap: 16px; }
            .table-controls { flex-direction: column; }
            .search-box { width: 100%; }
            .data-table { font-size: 0.75rem; }
            .data-table th, .data-table td { padding: 10px 12px; }
        }
    </style>
</head>
<body>
<header class="professional-header">
    <div class="header-container">
        <div class="logo-area">
            <img src="uc.logo.png" alt="University of Cebu" class="uc-logo">
            <div class="system-title">
                <h1>College of Computer Studies <span>| Sit-in Monitoring System</span></h1>
            </div>
        </div>
        <div class="nav-links">
            <a href="dashboard.php">Home</a>
            <a href="edit_profile.php">Edit Profile</a>
            <a href="history.php" class="active">History</a>
            <a href="reservation.php">Reservation</a>
            <a href="logout.php" class="btn-logout">Log out</a>
        </div>
    </div>
</header>

<div class="history-container">
    <div class="history-card">
        <div class="card-header"><h2><i class="fas fa-history"></i> History Information</h2><p></p></div>
        <div class="card-body">
            <div class="table-controls">
                <div><input type="text" id="searchInput" class="search-box" placeholder="Search..."></div>
            </div>
            <div style="overflow-x: auto;">
                <table class="data-table" id="historyTable">
                    <thead><tr><th>ID Number</th><th>Sit Purpose</th><th>Laboratory</th><th>Login Time</th><th>Logout Time</th><th>Date</th><th>Status</th><th>Action</th></tr></thead>
                    <tbody id="tableBody">
                        <?php if (mysqli_num_rows($query) > 0): ?>
                            <?php while($row = mysqli_fetch_assoc($query)): ?>
                                <?php
                                $status = 'Active';
                                $statusClass = 'status-active';
                                $logoutTime = '--:--';
                                if (!empty($row['time_out']) && $row['time_out'] != '0000-00-00 00:00:00') {
                                    $status = 'Completed';
                                    $statusClass = 'status-completed';
                                    $logoutTime = date('h:i A', strtotime($row['time_out']));
                                }
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['student_id']); ?></td>
                                    <td><?php echo htmlspecialchars($row['purpose']); ?></td>
                                    <td><?php echo htmlspecialchars($row['lab']); ?></td>
                                    <td><?php echo date('h:i A', strtotime($row['time_in'])); ?></td>
                                    <td><?php echo $logoutTime; ?></td>
                                    <td><?php echo date('M d, Y', strtotime($row['date'])); ?></td>
                                    <td><span class="status-badge <?php echo $statusClass; ?>"><?php echo $status; ?></span></td>
                                    <td><button class="view-btn" onclick="alert('Details for reservation #<?php echo $row['id']; ?>')"><i class="fas fa-eye"></i> View</button></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="8" style="text-align:center; padding:60px;"><i class="fas fa-calendar-alt" style="font-size:3rem; opacity:0.5;"></i><p>No history records found.</p></td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<script>
    document.getElementById('searchInput')?.addEventListener('keyup', function() {
        const term = this.value.toLowerCase();
        document.querySelectorAll('#tableBody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
</script>
</body>
</html>