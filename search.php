<?php
require_once 'config/db.php';

// Search query
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
$packages = [];
$error_message = '';

try {
    if ($query !== '') {
        // Case-insensitive search on title or destination
        $filter = [
            '$or' => [
                ['title'       => new MongoDB\BSON\Regex($query, 'i')],
                ['destination' => new MongoDB\BSON\Regex($query, 'i')],
            ]
        ];
        $cursor = $packagesCollection->find(
            $filter,
            [
                'sort' => ['price' => 1],
                'projection' => [
                    'package_id'    => 1,
                    'title'         => 1,
                    'destination'   => 1,
                    'price'         => 1,
                    'duration_days' => 1,
                    'image_path'    => 1,
                ]
            ]
        );
    } else {
        // Show all packages if no query
        $cursor = $packagesCollection->find(
            [],
            [
                'sort' => ['package_id' => 1],
                'projection' => [
                    'package_id'    => 1,
                    'title'         => 1,
                    'destination'   => 1,
                    'price'         => 1,
                    'duration_days' => 1,
                    'image_path'    => 1,
                ]
            ]
        );
    }

    foreach ($cursor as $doc) {
        $packages[] = docToArray($doc);
    }
} catch (Exception $e) {
    $error_message = "Search error: " . $e->getMessage();
}

require_once 'includes/header.php';
?>

<main class="page-content">

    <!-- Page Header -->
    <div class="search-page-hero">
        <div class="search-page-inner">
            <h1><?php echo $query ? 'Results for "' . htmlspecialchars($query) . '"' : 'All Destinations'; ?></h1>
            <form action="search.php" method="GET" class="search-bar-inline">
                <input type="text" name="query" placeholder="Search destinations or trips..." 
                       value="<?php echo htmlspecialchars($query); ?>" required>
                <button type="submit">🔍 Search</button>
            </form>
        </div>
    </div>

    <!-- Results Section -->
    <div class="packages-section">
        <?php if ($error_message): ?>
            <p class="error-msg"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <?php if (count($packages) === 0 && $query): ?>
            <div class="empty-state">
                <p>😕 No packages found for "<strong><?php echo htmlspecialchars($query); ?></strong>". Try a different search!</p>
                <a href="search.php" class="btn-primary" style="display:inline-block;margin-top:18px;width:auto;padding:12px 30px;">Browse All Packages</a>
            </div>
        <?php else: ?>
            <div class="results-count">
                <p><?php echo count($packages); ?> package<?php echo count($packages) !== 1 ? 's' : ''; ?> found</p>
            </div>
            <div class="modern-grid">
                <?php foreach ($packages as $pkg): ?>
                    <a href="package_details.php?id=<?php echo $pkg['package_id']; ?>" class="modern-card">
                        <div class="card-image-wrapper">
                            <img src="<?php echo htmlspecialchars($pkg['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($pkg['title']); ?>" class="card-image">
                            <div class="price-badge"><strong>$<?php echo number_format($pkg['price'], 2); ?></strong></div>
                        </div>
                        <div class="card-body">
                            <span class="location-tag">📍 <?php echo htmlspecialchars($pkg['destination']); ?></span>
                            <h3 class="card-title"><?php echo htmlspecialchars($pkg['title']); ?></h3>
                            <?php if (!empty($pkg['duration_days'])): ?>
                                <span class="duration-tag">🕐 <?php echo $pkg['duration_days']; ?> Days</span>
                            <?php endif; ?>
                            <p class="card-link">Explore package →</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</main>

<?php require_once 'includes/footer.php'; ?>
