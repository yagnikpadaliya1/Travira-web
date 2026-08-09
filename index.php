<?php
// 1. Include database and header
require_once 'config/db.php';
require_once 'includes/header.php';

// 2. Fetch packages (latest 6)
try {
    $cursor = $packagesCollection->find(
        [],
        [
            'sort'  => ['package_id' => -1],
            'limit' => 6,
            'projection' => [
                'package_id'  => 1,
                'title'       => 1,
                'destination' => 1,
                'price'       => 1,
                'image_path'  => 1,
            ]
        ]
    );
    $packages = [];
    foreach ($cursor as $doc) {
        $packages[] = docToArray($doc);
    }
} catch (Exception $e) {
    $error_message = "Error loading packages: " . $e->getMessage();
    $packages = [];
}
?>

<main class="page-content">
    <!-- Immersive Hero Section -->
    <section class="modern-hero">
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h1 class="hero-title">Discover your next great adventure.</h1>
            <p class="hero-subtitle">Explore breathtaking destinations and curated travel packages around the globe.</p>
            
            <!-- Floating Search Bar -->
            <div class="search-wrapper">
                <form action="search.php" method="GET" class="floating-search">
                    <div class="search-group">
                        <label for="query">Location</label>
                        <input type="text" id="query" name="query" placeholder="Where are you going?" required>
                    </div>
                    <div class="search-group divider">
                        <label for="dates">Dates</label>
                        <input type="text" id="dates" placeholder="Add dates" disabled>
                    </div>
                    <div class="search-button-group">
                        <button type="submit" class="btn-search">
                            <span class="search-icon">🔍</span> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Packages Grid Section -->
    <section class="packages-section">
        <div class="section-header">
            <h2>Trending Destinations</h2>
            <p>Most popular choices for travelers this month</p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <p class="error-msg"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <div class="modern-grid">
            <?php if (count($packages) > 0): ?>
                <?php foreach ($packages as $pkg): ?>
                    <a href="package_details.php?id=<?php echo $pkg['package_id']; ?>" class="modern-card">
                        <div class="card-image-wrapper">
                            <img src="<?php echo htmlspecialchars($pkg['image_path']); ?>" alt="<?php echo htmlspecialchars($pkg['title']); ?>" class="card-image">
                            <!-- Floating Price Badge -->
                            <div class="price-badge">
                                <strong>$<?php echo htmlspecialchars($pkg['price']); ?></strong>
                            </div>
                        </div>
                        <div class="card-body">
                            <span class="location-tag">📍 <?php echo htmlspecialchars($pkg['destination']); ?></span>
                            <h3 class="card-title"><?php echo htmlspecialchars($pkg['title']); ?></h3>
                            <p class="card-link">Explore package &rarr;</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <p>No travel packages available right now. We are plotting new adventures!</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php
// 3. Include footer
require_once 'includes/footer.php';
?>
