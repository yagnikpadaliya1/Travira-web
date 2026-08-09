<?php
/*
 * MANAGE BOOKINGS — ADMIN PAGE
 * -----------------------------
 * Allows the admin to:
 *   - View all customer bookings
 *   - Update a booking's status (Pending / Confirmed / Cancelled)
 *   - Delete a booking
 */
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once '../config/db.php';

$message = '';

// --- Handle Status Update (POST form) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $booking_id = (int)$_POST['booking_id'];
    $new_status = $_POST['status'];
    $allowed    = ['Pending', 'Confirmed', 'Cancelled'];

    if (in_array($new_status, $allowed)) {
        $bookingsCollection->updateOne(
            ['booking_id' => $booking_id],
            ['$set' => ['status' => $new_status]]
        );
        $message = 'Booking #' . $booking_id . ' status updated to ' . $new_status . '.';
    }
    header('Location: manage_bookings.php');
    exit;
}

// --- Handle Delete ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $bookingsCollection->deleteOne(['booking_id' => (int)$_GET['delete']]);
    header('Location: manage_bookings.php');
    exit;
}

// --- Fetch All Bookings (with package title via $lookup) ---
$pipeline = [
    ['$sort' => ['booking_id' => -1]],
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

$bookings = [];
try {
    $cursor = $bookingsCollection->aggregate($pipeline);
    foreach ($cursor as $doc) {
        $bookings[] = docToArray($doc);
    }
} catch (Exception $e) {
    // empty
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Bookings — TravelSite Admin</title>
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
            <a href="dashboard.php">Dashboard</a>
            <a href="manage_bookings.php" class="active">Bookings</a>
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
            <h1>Manage Bookings</h1>
            <p><?php echo count($bookings); ?> total booking(s) found.</p>
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
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($bookings as $b): ?>
                    <tr>
                        <td>#<?php echo $b['booking_id']; ?></td>
                        <td>
                            <strong><?php echo htmlspecialchars($b['customer_name']); ?></strong><br>
                            <small><?php echo htmlspecialchars($b['customer_email']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($b['package_title'] ?? '—'); ?></td>
                        <td><?php echo date('M j, Y', strtotime($b['travel_date'])); ?></td>
                        <td><?php echo $b['number_of_people']; ?></td>
                        <td>
                            <?php
                            $status = $b['status'];
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
                        <td class="action-cell">
                            <!-- Status update form -->
                            <form method="POST" style="display:inline-flex; gap:6px; align-items:center;">
                                <input type="hidden" name="booking_id" value="<?php echo $b['booking_id']; ?>">
                                <select name="status" class="status-select">
                                    <option value="Pending"   <?php echo $b['status'] === 'Pending'   ? 'selected' : ''; ?>>Pending</option>
                                    <option value="Confirmed" <?php echo $b['status'] === 'Confirmed' ? 'selected' : ''; ?>>Confirmed</option>
                                    <option value="Cancelled" <?php echo $b['status'] === 'Cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                </select>
                                <button type="submit" name="update_status" class="btn-action btn-save">Save</button>
                            </form>
                            <!-- Delete link with confirmation -->
                            <a href="manage_bookings.php?delete=<?php echo $b['booking_id']; ?>"
                               class="btn-action btn-delete"
                               onclick="return confirm('Delete booking #<?php echo $b['booking_id']; ?>?')">
                               Delete
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    </div>
</main>

</body>
</html>
