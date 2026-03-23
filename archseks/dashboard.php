<?php
session_start();
include('config.php');

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$uid = $_SESSION['user_id'];
// Fetch FRESH data from the database to ensure updates show immediately
$query = mysqli_query($conn, "SELECT * FROM users WHERE id_number = '$uid'");
$user = mysqli_fetch_assoc($query);

if (!$user) { die("Error: Student data not found."); }

$currentPhoto = !empty($user['profile_photo']) ? $user['profile_photo'] : 'default.png';

// Cache fix for the profile photo
$photoPath = "uploads/" . $currentPhoto;
$version = file_exists($photoPath) ? filemtime($photoPath) : time();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | CCS Sit-in Monitoring System</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts: Inter for modern typography -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700;14..32,800&display=swap" rel="stylesheet">
    <style>
        /* =============================================
           ROOT VARIABLES - CCS DEPARTMENT COLORS (Purple & Gold)
           ============================================= */
        :root {
            --ccs-purple-dark: #3a235c;
            --ccs-purple: #4b2c82;
            --ccs-purple-light: #6b4c9e;
            --ccs-gold: #ffcc00;
            --ccs-gold-dark: #e6b800;
            --ccs-gold-light: #fff0b5;
            --bg-gray: #f4f7f6;
            --text-dark: #1e293b;
            --text-muted: #64748b;
            --shadow-sm: 0 1px 3px rgba(0,0,0,0.05);
            --shadow-md: 0 10px 25px -5px rgba(0,0,0,0.08), 0 8px 10px -6px rgba(0,0,0,0.02);
            --shadow-lg: 0 20px 35px -10px rgba(0,0,0,0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #eef2f5 100%);
            min-height: 100vh;
        }

        /* =============================================
           PROFESSIONAL HEADER WITH UC LOGO
           ============================================= */
        .professional-header {
            background: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            border-bottom: 1px solid rgba(75, 44, 130, 0.1);
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

        /* Left side - UC Logo */
        .logo-area {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .uc-logo {
            width: 50px;
            height: 50px;
            object-fit: contain;
        }

        .system-title {
            display: flex;
            flex-direction: column;
        }

        .system-title h1 {
            font-size: 1rem;
            font-weight: 600;
            color: var(--ccs-purple);
            letter-spacing: -0.3px;
            line-height: 1.3;
        }

        .system-title h1 span {
            font-weight: 400;
            color: var(--text-muted);
        }

        .system-title .college-name {
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 0.5px;
        }

        /* Center - Navigation Links */
        .nav-links {
            display: flex;
            gap: 32px;
            align-items: center;
        }

        .nav-links a {
            text-decoration: none;
            color: var(--text-muted);
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.2s ease;
            position: relative;
            padding: 4px 0;
        }

        .nav-links a:hover {
            color: var(--ccs-purple);
        }

        .nav-links a.active {
            color: var(--ccs-purple);
            font-weight: 600;
        }

        .nav-links a.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 2px;
            background: var(--ccs-gold);
            border-radius: 2px;
        }

        /* Logout Button */
        .btn-logout {
            background: var(--ccs-gold);
            color: var(--ccs-purple-dark) !important;
            padding: 8px 20px !important;
            border-radius: 40px;
            font-weight: 700 !important;
            transition: all 0.2s ease;
        }

        .btn-logout:hover {
            background: var(--ccs-gold-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            color: var(--ccs-purple-dark) !important;
        }

        /* =============================================
           DASHBOARD CONTAINER
           ============================================= */
        .dashboard-container {
            display: flex;
            gap: 24px;
            max-width: 1400px;
            margin: 40px auto;
            padding: 0 40px;
        }

        /* Student Info Card */
        .info-card {
            flex: 1;
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .info-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .card-header {
            background: linear-gradient(135deg, var(--ccs-purple) 0%, var(--ccs-purple-dark) 100%);
            color: white;
            padding: 16px 20px;
            font-weight: 700;
            font-size: 1.1rem;
            letter-spacing: 0.5px;
        }

        .card-header i {
            margin-right: 8px;
            color: var(--ccs-gold);
        }

        .card-body {
            padding: 24px;
        }

        /* Profile Image Section */
        .profile-section {
            text-align: center;
            margin-bottom: 24px;
        }

        .profile-img-container {
            width: 140px;
            height: 140px;
            margin: 0 auto 16px;
            border-radius: 50%;
            overflow: hidden;
            border: 4px solid var(--ccs-gold);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
            background: #f0f0f0;
        }

        .profile-img-container img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* Student Info List */
        .student-info-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .student-info-list li {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #eef2f6;
            font-size: 0.9rem;
            color: var(--text-dark);
        }

        .student-info-list li:last-child {
            border-bottom: none;
        }

        .student-info-list li i {
            width: 32px;
            font-size: 1.1rem;
            color: var(--ccs-purple);
        }

        .student-info-list li strong {
            font-weight: 700;
            color: var(--text-dark);
            min-width: 70px;
        }

        .student-info-list li span {
            color: var(--text-muted);
        }

        /* Announcement & Rules Cards */
        .content-card {
            flex: 1;
            background: white;
            border-radius: 24px;
            box-shadow: var(--shadow-md);
            overflow: hidden;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .content-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
        }

        .announcement-content {
            padding: 20px;
        }

        .announcement-meta {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--ccs-gold-light);
        }

        .announcement-meta i {
            font-size: 1.2rem;
            color: var(--ccs-purple);
        }

        .announcement-date {
            font-size: 0.8rem;
            color: var(--text-muted);
        }

        .announcement-title {
            font-size: 1rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 8px;
        }

        .announcement-text {
            color: var(--text-muted);
            line-height: 1.6;
            font-size: 0.9rem;
        }

        /* Rules List */
        .rules-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .rules-list li {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 14px 0;
            border-bottom: 1px solid #eef2f6;
            font-size: 0.9rem;
            color: var(--text-muted);
            line-height: 1.5;
        }

        .rules-list li:last-child {
            border-bottom: none;
        }

        .rules-list li i {
            color: var(--ccs-gold);
            font-size: 1rem;
            margin-top: 2px;
        }

        .rules-list li span {
            flex: 1;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .dashboard-container {
                flex-direction: column;
                max-width: 700px;
                padding: 0 20px;
            }
        }

        @media (max-width: 768px) {
            .header-container {
                padding: 0 20px;
                height: auto;
                flex-direction: column;
                gap: 12px;
                padding: 12px 20px;
            }
            .nav-links {
                flex-wrap: wrap;
                justify-content: center;
                gap: 16px;
            }
            .logo-area {
                justify-content: center;
            }
            .system-title h1 {
                font-size: 0.9rem;
                text-align: center;
            }
            .dashboard-container {
                margin: 20px auto;
            }
            .student-info-list li {
                flex-wrap: wrap;
            }
            .student-info-list li strong {
                min-width: 60px;
            }
        }

        @media (max-width: 480px) {
            .dashboard-container {
                padding: 0 16px;
            }
            .card-body {
                padding: 16px;
            }
            .profile-img-container {
                width: 100px;
                height: 100px;
            }
            .uc-logo {
                width: 40px;
                height: 40px;
            }
        }

        /* Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .dashboard-container > div {
            animation: fadeIn 0.4s ease-out forwards;
        }

        .dashboard-container > div:nth-child(1) { animation-delay: 0.05s; }
        .dashboard-container > div:nth-child(2) { animation-delay: 0.1s; }
        .dashboard-container > div:nth-child(3) { animation-delay: 0.15s; }
    </style>
</head>
<body>

<header class="professional-header">
    <div class="header-container">
        <!-- Left: UC Logo and System Title -->
        <div class="logo-area">
            <img src="uc.logo.png" alt="University of Cebu" class="uc-logo">
            <div class="system-title">
                <h1>College of Computer Studies <span>| Sit-in Monitoring System</span></h1>
            </div>
        </div>
        
        <!-- Center: Navigation Links -->
        <div class="nav-links">
            <a href="dashboard.php" class="active">Home</a>
            <a href="edit_profile.php">Edit Profile</a>
            <a href="history.php">History</a>
            <a href="reservation.php">Reservation</a>
            <a href="logout.php" class="btn-logout">Log out</a>
        </div>
    </div>
</header>

<div class="dashboard-container">
    <!-- Student Information Card -->
    <div class="info-card">
        <div class="card-header">
            <i class="fas fa-user-graduate"></i> Student Information
        </div>
        <div class="card-body">
            <div class="profile-section">
                <div class="profile-img-container">
                    <img src="uploads/<?php echo $currentPhoto; ?>?v=<?php echo $version; ?>" 
                         alt="Profile Photo"
                         onerror="this.src='uploads/default.png'">
                </div>
            </div>
            
            <ul class="student-info-list">
                <li>
                    <i class="fas fa-id-card"></i>
                    <strong>ID:</strong>
                    <span><?php echo htmlspecialchars($user['id_number']); ?></span>
                </li>
                <li>
                    <i class="fas fa-user"></i>
                    <strong>Name:</strong>
                    <span><?php echo strtoupper(htmlspecialchars($user['first_name'] . " " . (!empty($user['middle_name']) ? substr($user['middle_name'], 0, 1) . ". " : "") . $user['last_name'])); ?></span>
                </li>
                <li>
                    <i class="fas fa-graduation-cap"></i>
                    <strong>Course:</strong>
                    <span><?php echo htmlspecialchars($user['course']); ?></span>
                </li>
                <li>
                    <i class="fas fa-layer-group"></i>
                    <strong>Year:</strong>
                    <span><?php echo htmlspecialchars($user['course_level']); ?></span>
                </li>
                <li>
                    <i class="fas fa-envelope"></i>
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($user['email']); ?></span>
                </li>
                <li>
                    <i class="fas fa-home"></i>
                    <strong>Address:</strong>
                    <span><?php echo htmlspecialchars($user['address']); ?></span>
                </li>
                <li>
                    <i class="fas fa-clock"></i>
                    <strong>Session:</strong>
                    <span><?php echo isset($user['session_count']) ? htmlspecialchars($user['session_count']) : '30'; ?> remaining</span>
                </li>
            </ul>
        </div>
    </div>

    <!-- Announcement Card -->
    <div class="content-card">
        <div class="card-header">
            <i class="fas fa-bullhorn"></i> Announcement
        </div>
        <div class="announcement-content">
            <div class="announcement-meta">
                <i class="fas fa-user-circle"></i>
                <div>
                    <div class="announcement-title">CCS Admin</div>
                    <div class="announcement-date"><i class="far fa-calendar-alt"></i> February 11, 2026</div>
                </div>
            </div>
            <div class="announcement-text">
                <p>Important Announcement We are Ecxited to annouce the launch of our new website!🎉 Explore our latest products and services now!</p>
                <p style="margin-top: 12px; color: var(--ccs-purple); font-weight: 500;">
                </p>
            </div>
        </div>
    </div>

    <!-- Rules and Regulations Card -->
    <div class="content-card">
        <div class="card-header">
            <i class="fas fa-gavel"></i> Rules and Regulation
        </div>
        <div class="card-body">
            <ul class="rules-list">
                <li>
                    <i class="fas fa-volume-mute"></i>
                    <span>Maintain silence and proper decorum within the laboratory premises.</span>
                </li>
                <li>
                    <i class="fas fa-gamepad"></i>
                    <span>Games are strictly prohibited during sit-in sessions.</span>
                </li>
                <li>
                    <i class="fas fa-power-off"></i>
                    <span>Properly shut down computers after use to preserve equipment.</span>
                </li>
                <li>
                    <i class="fas fa-utensils"></i>
                    <span>No food or drinks allowed inside the laboratory.</span>
                </li>
                <li>
                    <i class="fas fa-hand-peace"></i>
                    <span>Follow the designated schedule and log in/out properly.</span>
                </li>
                <li>
                    <i class="fas fa-charging-station"></i>
                    <span>Report any technical issues to the laboratory staff immediately.</span>
                </li>
            </ul>
        </div>
    </div>
</div>

</body>
</html>