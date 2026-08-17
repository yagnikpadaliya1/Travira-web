<?php
require_once 'config/db.php';
require_once 'includes/header.php';

// Handle search query from navbar or hero
$query = trim($_GET['query'] ?? '');

// Fetch packages (filtered if search query)
try {
    if ($query) {
        $sql  = "SELECT package_id, title, destination, price, image_path FROM Packages
                  WHERE title LIKE :q OR destination LIKE :q
                  ORDER BY package_id DESC LIMIT 12";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':q' => '%' . $query . '%']);
    } else {
        $sql  = "SELECT package_id, title, destination, price, image_path FROM Packages ORDER BY package_id DESC LIMIT 6";
        $stmt = $pdo->query($sql);
    }
    $packages = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error_message = "Error loading packages: " . $e->getMessage();
    $packages = [];
}

// Fetch slider images from packages (up to 5)
try {
    $sliderStmt = $pdo->query("SELECT image_path, destination FROM Packages WHERE image_path IS NOT NULL AND image_path != '' ORDER BY package_id DESC LIMIT 5");
    $sliderImages = $sliderStmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $sliderImages = [];
}

// Fallback slider images (Unsplash)
$fallbackSlides = [
    ['url' => 'https://images.unsplash.com/photo-1502602898657-3e91760cbb34?w=1600&q=80', 'label' => 'Paris, France'],
    ['url' => 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?w=1600&q=80', 'label' => 'Maldives'],
    ['url' => 'https://images.unsplash.com/photo-1518684079-3c830dcef090?w=1600&q=80', 'label' => 'Dubai, UAE'],
    ['url' => 'https://images.unsplash.com/photo-1476514525535-07fb3b4ae5f1?w=1600&q=80', 'label' => 'Swiss Alps'],
];
?>

<main class="page-content">

    <!-- ══ HERO IMAGE SLIDER ══ -->
    <section class="hero-slider" id="heroSlider">

        <!-- Slides -->
        <div class="hs-track" id="hsTrack">
            <?php if (!empty($sliderImages)): ?>
                <?php foreach ($sliderImages as $i => $slide): ?>
                    <div class="hs-slide <?php echo $i === 0 ? 'active' : ''; ?>"
                         style="background-image: url('<?php echo htmlspecialchars($slide['image_path']); ?>')">
                        <div class="hs-label"><?php echo htmlspecialchars($slide['destination']); ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($fallbackSlides as $i => $slide): ?>
                    <div class="hs-slide <?php echo $i === 0 ? 'active' : ''; ?>"
                         style="background-image: url('<?php echo $slide['url']; ?>')">
                        <div class="hs-label"><?php echo $slide['label']; ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Overlay + Content -->
        <div class="hs-overlay"></div>
        <div class="hs-content">
            <h1 class="hero-title">Discover your next great adventure.</h1>
            <p class="hero-subtitle">Explore breathtaking destinations and curated travel packages around the globe.</p>

            <!-- Hero Search Bar -->
            <div class="search-wrapper">
                <form action="index.php" method="GET" class="floating-search">
                    <div class="search-group">
                        <label for="query">Location</label>
                        <input type="text" id="query" name="query"
                               placeholder="Where are you going?"
                               value="<?php echo htmlspecialchars($query); ?>">
                    </div>
                    <div class="search-button-group">
                        <button type="submit" class="btn-search">
                            <span class="search-icon">🔍</span> Search
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Prev / Next arrows -->
        <button class="hs-arrow hs-prev" onclick="slideChange(-1)" aria-label="Previous">&#8249;</button>
        <button class="hs-arrow hs-next" onclick="slideChange(1)"  aria-label="Next">&#8250;</button>

        <!-- Dots -->
        <div class="hs-dots" id="hsDots"></div>

    </section>

    <!-- ══ PACKAGES SECTION ══ -->
    <section class="packages-section">
        <div class="section-header">
            <?php if ($query): ?>
                <h2>Results for "<?php echo htmlspecialchars($query); ?>"</h2>
                <p><a href="index.php" style="color:var(--primary);">← Clear search</a></p>
            <?php else: ?>
                <h2>Trending Destinations</h2>
                <p>Most popular choices for travelers this month</p>
            <?php endif; ?>
        </div>

        <?php if (!empty($error_message)): ?>
            <p class="error-msg"><?php echo $error_message; ?></p>
        <?php endif; ?>

        <div class="modern-grid">
            <?php if (count($packages) > 0): ?>
                <?php foreach ($packages as $pkg): ?>
                    <a href="package_details.php?id=<?php echo $pkg['package_id']; ?>" class="modern-card">
                        <div class="card-image-wrapper">
                            <img src="<?php echo htmlspecialchars($pkg['image_path']); ?>"
                                 alt="<?php echo htmlspecialchars($pkg['title']); ?>"
                                 class="card-image">
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
                    <p><?php echo $query ? 'No packages found for "' . htmlspecialchars($query) . '".' : 'No travel packages available right now.'; ?></p>
                </div>
            <?php endif; ?>
        </div>
    </section>

</main>

<script>
// ── Hero Slider ──
const slides = document.querySelectorAll('.hs-slide');
const dotsContainer = document.getElementById('hsDots');
let current = 0, timer;

// Build dots
slides.forEach((_, i) => {
    const d = document.createElement('button');
    d.className = 'hs-dot' + (i === 0 ? ' active' : '');
    d.setAttribute('aria-label', 'Slide ' + (i + 1));
    d.onclick = () => goTo(i);
    dotsContainer.appendChild(d);
});

function goTo(n) {
    slides[current].classList.remove('active');
    document.querySelectorAll('.hs-dot')[current].classList.remove('active');
    current = (n + slides.length) % slides.length;
    slides[current].classList.add('active');
    document.querySelectorAll('.hs-dot')[current].classList.add('active');
    resetTimer();
}

function slideChange(dir) { goTo(current + dir); }

function resetTimer() {
    clearInterval(timer);
    timer = setInterval(() => goTo(current + 1), 4500);
}

if (slides.length > 1) resetTimer();
</script>

<?php require_once 'includes/footer.php'; ?>