<?php
// 1. Include the database connection and the header
require_once 'config/db.php';
require_once 'includes/header.php';

// 2. Fetch the package safely
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $package_id = $_GET['id'];

    try {
        $sql = "SELECT * FROM Packages WHERE package_id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindParam(':id', $package_id, PDO::PARAM_INT);
        $stmt->execute();
        $package = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $error_message = "Error loading package details: " . $e->getMessage();
    }
} else {
    $package = false; 
}
?>

<main class="page-content bg-light">
    <?php if (!empty($error_message)): ?>
        <div class="error-container">
            <p><?php echo $error_message; ?></p>
        </div>
    <?php elseif ($package): ?>
        
        <!-- Breadcrumb Navigation -->
        <div class="container breadcrumb">
            <a href="index.php">Home</a> &gt; 
            <a href="search.php?query=<?php echo urlencode($package['destination']); ?>"><?php echo htmlspecialchars($package['destination']); ?></a> &gt; 
            <span><?php echo htmlspecialchars($package['title']); ?></span>
        </div>

        <div class="container package-layout">
            
            <!-- Left Column: Heavy Content -->
            <div class="package-main">
                <h1 class="package-title"><?php echo htmlspecialchars($package['title']); ?></h1>
                <p class="package-location">📍 <?php echo htmlspecialchars($package['destination']); ?></p>
                
                <!-- Hero Image -->
                <div class="package-image-wrapper">
                    <img src="<?php echo htmlspecialchars($package['image_path']); ?>" alt="<?php echo htmlspecialchars($package['title']); ?>" class="package-image">
                </div>

                <!-- Description Section -->
                <section class="info-section">
                    <h2>Overview</h2>
                    <p class="package-description">
                        <?php echo nl2br(htmlspecialchars($package['description'] ?? 'Experience the trip of a lifetime. Full details coming soon!')); ?>
                    </p>
                </section>

                <!-- Features/Amenities Section (Mock Data for UI) -->
                <section class="info-section">
                    <h2>What's Included</h2>
                    <ul class="features-list">
                        <li>✈️ Round-trip Flights</li>
                        <li>🏨 4-Star Accommodation</li>
                        <li>🍳 Daily Breakfast</li>
                        <li>🗺️ Guided Tours</li>
                    </ul>
                </section>
            </div>

            <!-- Right Column: Sticky Booking Card -->
            <div class="package-sidebar">
                <div class="booking-card">
                    <div class="price-header">
                        <h2>$<?php echo htmlspecialchars($package['price']); ?></h2>
                        <span>/ per person</span>
                    </div>
                    
                    <div class="booking-info">
                        <p>✓ Free cancellation up to 48 hours</p>
                        <p>✓ Instant confirmation</p>
                    </div>

                    <!-- Book Now Button -->
                    <a href="book.php?package_id=<?php echo $package['package_id']; ?>" class="btn-primary btn-block">Book This Package</a>
                    
                    <p class="disclaimer">You won't be charged yet.</p>
                </div>
            </div>

        </div>

    <?php else: ?>
        <div class="error-container">
            <h2>Package Not Found</h2>
            <p>Sorry, we couldn't find the travel package you are looking for.</p>
            <a href="index.php" class="btn-primary" style="margin-top: 15px; display: inline-block;">Return to Home</a>
        </div>
    <?php endif; ?>
</main>

<?php
// 3. Include the footer
require_once 'includes/footer.php';
?>