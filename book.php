<?php
require_once 'config/db.php';

$package    = null;
$success    = false;
$error_msg  = '';
$name       = '';
$email      = '';

// Fetch the package being booked
if (isset($_GET['package_id']) && is_numeric($_GET['package_id'])) {
    $package_id = (int)$_GET['package_id'];
    try {
        $doc = $packagesCollection->findOne(['package_id' => $package_id]);
        $package = docToArray($doc);
    } catch (Exception $e) {
        $error_msg = "Error loading package: " . $e->getMessage();
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pkg_id    = (int)($_POST['package_id'] ?? 0);
    $name      = trim($_POST['customer_name'] ?? '');
    $email     = trim($_POST['customer_email'] ?? '');
    $date      = trim($_POST['travel_date'] ?? '');
    $people    = (int)($_POST['number_of_people'] ?? 1);

    if ($pkg_id && $name && $email && $date && $people >= 1) {
        try {
            $booking_id = getNextSequenceId($bookingsCollection, 'booking_id');

            $bookingsCollection->insertOne([
                'booking_id'       => $booking_id,
                'package_id'       => $pkg_id,
                'customer_name'    => $name,
                'customer_email'   => $email,
                'travel_date'      => $date,
                'number_of_people' => $people,
                'status'           => 'Pending',
                'created_at'       => new MongoDB\BSON\UTCDateTime(),
            ]);
            $success = true;
        } catch (Exception $e) {
            $error_msg = "Booking failed: " . $e->getMessage();
        }
    } else {
        $error_msg = "Please fill in all fields correctly.";
    }
}

require_once 'includes/header.php';
?>

<main class="page-content">
    <div class="booking-page">
        <div class="container">

            <?php if ($success): ?>
                <!-- Success State -->
                <div class="booking-success">
                    <div class="success-icon">✅</div>
                    <h1>Booking Confirmed!</h1>
                    <p>Thank you, <strong><?php echo htmlspecialchars($name); ?></strong>! Your trip is being prepared.</p>
                    <p>We'll send confirmation details to <strong><?php echo htmlspecialchars($email); ?></strong> shortly.</p>
                    <a href="index.php" class="btn-primary" style="display:inline-block;margin-top:24px;width:auto;padding:14px 36px;">Back to Home</a>
                </div>

            <?php elseif ($package): ?>
                <div class="booking-layout">
                    <!-- Left: Package Summary -->
                    <div class="booking-summary-card">
                        <img src="<?php echo htmlspecialchars($package['image_path']); ?>" 
                             alt="<?php echo htmlspecialchars($package['title']); ?>" class="summary-img">
                        <div class="summary-body">
                            <span class="location-tag">📍 <?php echo htmlspecialchars($package['destination']); ?></span>
                            <h2><?php echo htmlspecialchars($package['title']); ?></h2>
                            <?php if (!empty($package['duration_days'])): ?>
                                <p class="summary-duration">🕐 <?php echo $package['duration_days']; ?> Days</p>
                            <?php endif; ?>
                            <div class="summary-price">$<?php echo number_format($package['price'], 2); ?> <span>/ per person</span></div>
                        </div>
                    </div>

                    <!-- Right: Booking Form -->
                    <div class="booking-form-card">
                        <h1>Complete Your Booking</h1>
                        <p class="form-subtitle">Fill in your details below to reserve your spot.</p>

                        <?php if ($error_msg): ?>
                            <p class="error-msg"><?php echo htmlspecialchars($error_msg); ?></p>
                        <?php endif; ?>

                        <form method="POST" action="book.php" class="booking-form">
                            <input type="hidden" name="package_id" value="<?php echo $package['package_id']; ?>">

                            <div class="form-group">
                                <label for="customer_name">Full Name</label>
                                <input type="text" id="customer_name" name="customer_name" 
                                       placeholder="e.g. John Smith" required>
                            </div>

                            <div class="form-group">
                                <label for="customer_email">Email Address</label>
                                <input type="email" id="customer_email" name="customer_email" 
                                       placeholder="e.g. john@example.com" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="travel_date">Travel Date</label>
                                    <input type="date" id="travel_date" name="travel_date" 
                                           min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="number_of_people">Travelers</label>
                                    <input type="number" id="number_of_people" name="number_of_people" 
                                           value="1" min="1" max="20" required>
                                </div>
                            </div>

                            <div class="booking-total">
                                <span>Estimated Total</span>
                                <strong id="total-price">$<?php echo number_format($package['price'], 2); ?></strong>
                            </div>

                            <button type="submit" class="btn-primary">Confirm Booking</button>
                        </form>
                    </div>
                </div>

            <?php else: ?>
                <div class="error-container">
                    <h2>Package Not Found</h2>
                    <p>Please choose a valid travel package first.</p>
                    <a href="index.php" class="btn-primary" style="display:inline-block;margin-top:18px;width:auto;padding:12px 28px;">Browse Packages</a>
                </div>
            <?php endif; ?>

        </div>
    </div>
</main>

<script>
// Live price calculator
const peopleInput = document.getElementById('number_of_people');
const totalEl = document.getElementById('total-price');
const basePrice = <?php echo $package ? $package['price'] : 0; ?>;

if (peopleInput && totalEl) {
    peopleInput.addEventListener('input', () => {
        const n = parseInt(peopleInput.value) || 1;
        totalEl.textContent = '$' + (basePrice * n).toLocaleString('en-US', {minimumFractionDigits:2, maximumFractionDigits:2});
    });
}
</script>

<?php require_once 'includes/footer.php'; ?>
