<?php
/*
 * ADMIN DASHBOARD
 * ---------------
 * Protected page — only accessible after login.
 * Shows summary statistics and a table of recent bookings.
 */
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once '../config/db.php';

// --- Fetch summary statistics ---
$total_bookings  = $bookingsCollection->countDocuments([]);
$total_pending   = $bookingsCollection->countDocuments(['status' => 'Pending']);
$total_confirmed = $bookingsCollection->countDocuments(['status' => 'Confirmed']);
$total_packages  = $packagesCollection->countDocuments([]);

// --- Fetch the 8 most recent bookings (with package title via lookup) ---
$pipeline = [
    ['$sort'  => ['booking_id' => -1]],
    ['$limit' => 8],
    [
        '$lookup' => [
            'from'         => 'packages',
            'localField'   => 'package_id',
            'foreignField' => 'package_id',
            'as'           => 'package'
        ]
    ],
    [
        '$unwind' => [
            'path'                       => '$package',
            'preserveNullAndEmptyArrays' => true
        ]
    ],
    [
        '$project' => [
            'booking_id'       => 1,
            'customer_name'    => 1,
            'customer_email'   => 1,
            'travel_date'      => 1,
            'number_of_people' => 1,
            'status'           => 1,
            'package_title'    => '$package.title',
        ]
    ]
];

$recent_bookings = [];
try {
    $cursor = $bookingsCollection->aggregate($pipeline);
    foreach ($cursor as $doc) {
        $recent_bookings[] = docToArray($doc);
    }
} catch (Exception $e) {
    // Fallback: empty list
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard — TravelSite Admin</title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>

<!-- Admin Navigation Bar -->
<header class="admin-nav">
    <div class="admin-nav-inner">
        <a href="dashboard.php" class="logo">
            <i class="fa-solid fa-plane-departure"></i> TravelSite Admin
        </a>
        <nav class="admin-links">
            <a href="dashboard.php" class="active">Dashboard</a>
            <a href="manage_bookings.php">Bookings</a>
            <a href="manage_packages.php">Packages</a>
            <a href="logout.php" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </nav>
    </div>
</header>

<main class="admin-main">
    <div class="admin-wrapper">

        <div class="admin-page-header">
            <h1>Dashboard</h1>
            <p>Welcome back, <strong><?php echo htmlspecialchars($_SESSION['admin_username']); ?></strong>!</p>
        </div>

        <!-- Summary Statistics Cards -->
        <div class="stats-grid">
            <div class="stat-card stat-blue">
                <div class="stat-icon"><i class="fa-solid fa-suitcase-rolling"></i></div>
                <div class="stat-info">
                    <h2><?php echo $total_bookings; ?></h2>
                    <p>Total Bookings</p>
                </div>
            </div>
            <div class="stat-card stat-yellow">
                <div class="stat-icon"><i class="fa-solid fa-clock"></i></div>
                <div class="stat-info">
                    <h2><?php echo $total_pending; ?></h2>
                    <p>Pending</p>
                </div>
            </div>
            <div class="stat-card stat-green">
                <div class="stat-icon"><i class="fa-solid fa-circle-check"></i></div>
                <div class="stat-info">
                    <h2><?php echo $total_confirmed; ?></h2>
                    <p>Confirmed</p>
                </div>
            </div>
            <div class="stat-card stat-purple">
                <div class="stat-icon"><i class="fa-solid fa-map-location-dot"></i></div>
                <div class="stat-info">
                    <h2><?php echo $total_packages; ?></h2>
                    <p>Packages</p>
                </div>
            </div>
        </div>

        <!-- Recent Bookings Table -->
        <div class="admin-section">
            <div class="section-title-row">
                <h2>Recent Bookings</h2>
                <a href="manage_bookings.php" class="view-all-link">View All →</a>
            </div>
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Customer</th>
                            <th>Package</th>
                            <th>Travel Date</th>
                            <th>People</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_bookings as $row): ?>
                        <tr>
                            <td>#<?php echo $row['booking_id']; ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($row['customer_name']); ?></strong><br>
                                <small><?php echo htmlspecialchars($row['customer_email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($row['package_title'] ?? '—'); ?></td>
                            <td><?php echo date('M j, Y', strtotime($row['travel_date'])); ?></td>
                            <td><?php echo $row['number_of_people']; ?></td>
                            <td>
                                <?php
                                // Assign badge class based on booking status
                                $status = $row['status'];
                                if ($status === 'Confirmed') {
                                    $badge = 'badge-confirmed';
                                } elseif ($status === 'Cancelled') {
                                    $badge = 'badge-cancelled';
                                } else {
                                    $badge = 'badge-pending';
                                }
                                ?>
                                <span class="badge <?php echo $badge; ?>">
                                    <?php echo htmlspecialchars($status); ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</main>

</body>
</html>
