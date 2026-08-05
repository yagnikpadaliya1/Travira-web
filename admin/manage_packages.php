<?php
/*
 * MANAGE PACKAGES — ADMIN PAGE
 * -----------------------------
 * Full CRUD (Create, Read, Update, Delete) for travel packages.
 *   - The form above the table handles both ADD and EDIT
 *   - Clicking "Edit" fills the form with existing data
 *   - Clicking "Delete" removes the package from the database
 */
session_start();

// Redirect to login if not authenticated
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: index.php');
    exit;
}

require_once '../config/db.php';

$message = '';

// --- DELETE ---
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM packages WHERE package_id = :id");
    $stmt->execute([':id' => (int)$_GET['delete']]);
    $message = 'Package deleted successfully.';
}

// --- CREATE or UPDATE ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title         = trim($_POST['title']);
    $destination   = trim($_POST['destination']);
    $price         = $_POST['price'];
    $duration_days = (int)$_POST['duration_days'];
    $description   = trim($_POST['description']);
    $image_path    = trim($_POST['image_path']);

    if (isset($_POST['package_id']) && !empty($_POST['package_id'])) {
        // UPDATE existing package
        $sql = "UPDATE packages
                SET title = :title, destination = :destination, price = :price,
                    duration_days = :days, description = :description, image_path = :image
                WHERE package_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title'       => $title,
            ':destination' => $destination,
            ':price'       => $price,
            ':days'        => $duration_days,
            ':description' => $description,
            ':image'       => $image_path,
            ':id'          => (int)$_POST['package_id'],
        ]);
        $message = 'Package updated successfully.';
    } else {
        // INSERT new package
        $sql = "INSERT INTO packages (title, destination, price, duration_days, description, image_path)
                VALUES (:title, :destination, :price, :days, :description, :image)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title'       => $title,
            ':destination' => $destination,
            ':price'       => $price,
            ':days'        => $duration_days,
            ':description' => $description,
            ':image'       => $image_path,
        ]);
        $message = 'New package added successfully.';
    }
    header('Location: manage_packages.php');
    exit;
}

// --- FETCH PACKAGE FOR EDITING ---
$edit_package = null;
if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM packages WHERE package_id = :id");
    $stmt->execute([':id' => (int)$_GET['edit']]);
    $edit_package = $stmt->fetch(PDO::FETCH_ASSOC);
}

// --- FETCH ALL PACKAGES for the table ---
$packages = $pdo->query("SELECT * FROM packages ORDER BY package_id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Packages — TravelSite Admin</title>
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
            <a href="manage_bookings.php">Bookings</a>
            <a href="manage_packages.php" class="active">Packages</a>
            <a href="logout.php" class="logout-btn">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </nav>
    </div>
</header>

<main class="admin-main">
    <div class="admin-wrapper">

        <div class="admin-page-header">
            <h1><?php echo $edit_package ? 'Edit Package' : 'Manage Packages'; ?></h1>
            <p><?php echo $edit_package ? 'Update the details for this travel package.' : 'Add new packages or edit existing ones.'; ?></p>
        </div>

        <?php if ($message): ?>
            <p class="alert-success"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <!-- ADD / EDIT FORM -->
        <div class="admin-form-card">
            <h2><?php echo $edit_package ? 'Edit Package' : 'Add New Package'; ?></h2>
            <form method="POST" action="manage_packages.php">

                <!-- Hidden field: only has a value when editing -->
                <input type="hidden" name="package_id"
                       value="<?php echo $edit_package ? $edit_package['package_id'] : ''; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="title">Package Title</label>
                        <input type="text" id="title" name="title" required
                               placeholder="e.g. Tropical Beaches"
                               value="<?php echo $edit_package ? htmlspecialchars($edit_package['title']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="destination">Destination</label>
                        <input type="text" id="destination" name="destination" required
                               placeholder="e.g. Bali, Indonesia"
                               value="<?php echo $edit_package ? htmlspecialchars($edit_package['destination']) : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="price">Price (USD)</label>
                        <input type="number" id="price" name="price" step="0.01" min="0" required
                               placeholder="e.g. 1200.00"
                               value="<?php echo $edit_package ? htmlspecialchars($edit_package['price']) : ''; ?>">
                    </div>
                    <div class="form-group">
                        <label for="duration_days">Duration (Days)</label>
                        <input type="number" id="duration_days" name="duration_days" min="1" required
                               placeholder="e.g. 5"
                               value="<?php echo $edit_package ? htmlspecialchars($edit_package['duration_days']) : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="image_path">Image Path</label>
                    <input type="text" id="image_path" name="image_path" required
                           placeholder="e.g. images/packages/bali.jpg"
                           value="<?php echo $edit_package ? htmlspecialchars($edit_package['image_path']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4" required
                              placeholder="Describe the trip itinerary..."><?php echo $edit_package ? htmlspecialchars($edit_package['description']) : ''; ?></textarea>
                </div>

                <div style="display:flex; gap:12px;">
                    <button type="submit" class="btn-primary" style="width:auto; padding:12px 28px;">
                        <?php echo $edit_package ? 'Update Package' : 'Add Package'; ?>
                    </button>
                    <?php if ($edit_package): ?>
                        <a href="manage_packages.php" class="btn-cancel">Cancel</a>
                    <?php endif; ?>
                </div>

            </form>
        </div>

        <!-- PACKAGES TABLE -->
        <div class="admin-section">
            <div class="section-title-row">
                <h2>All Packages (<?php echo count($packages); ?>)</h2>
            </div>
            <div class="table-wrapper">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>Image</th>
                            <th>Title</th>
                            <th>Destination</th>
                            <th>Price</th>
                            <th>Days</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($packages as $pkg): ?>
                        <tr>
                            <td><?php echo $pkg['package_id']; ?></td>
                            <td>
                                <img src="../<?php echo htmlspecialchars($pkg['image_path']); ?>"
                                     alt="<?php echo htmlspecialchars($pkg['title']); ?>"
                                     style="width:60px; height:45px; object-fit:cover; border-radius:6px;">
                            </td>
                            <td><?php echo htmlspecialchars($pkg['title']); ?></td>
                            <td><?php echo htmlspecialchars($pkg['destination']); ?></td>
                            <td>$<?php echo number_format($pkg['price'], 2); ?></td>
                            <td><?php echo $pkg['duration_days']; ?> days</td>
                            <td class="action-cell">
                                <a href="manage_packages.php?edit=<?php echo $pkg['package_id']; ?>"
                                   class="btn-action btn-save">Edit</a>
                                <a href="manage_packages.php?delete=<?php echo $pkg['package_id']; ?>"
                                   class="btn-action btn-delete"
                                   onclick="return confirm('Delete this package? All its bookings will also be removed.')">
                                   Delete
                                </a>
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