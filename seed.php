<?php
/**
 * Database Seeder for Travira (MongoDB Atlas)
 * -------------------------------------------
 * Run once: php seed.php
 * Creates admin user + sample packages.
 */

require_once __DIR__ . '/config/db.php';

echo "Seeding Travira database...\n\n";

// ---------- Admin ----------
$adminExists = $adminsCollection->findOne(['username' => 'admin']);

if (!$adminExists) {
    $hash = password_hash('admin123', PASSWORD_BCRYPT);
    $adminsCollection->insertOne([
        'username'      => 'admin',
        'password_hash' => $hash,
        'created_at'    => new MongoDB\BSON\UTCDateTime(),
    ]);
    echo "✓ Admin user created (username: admin / password: admin123)\n";
} else {
    echo "• Admin user already exists – skipped\n";
}

// ---------- Packages ----------
$samplePackages = [
    [
        'package_id'    => 1,
        'title'         => 'Bali Paradise Escape',
        'destination'   => 'Bali, Indonesia',
        'price'         => 1299.00,
        'duration_days' => 7,
        'description'   => "Experience the magic of Bali with pristine beaches, ancient temples, and vibrant culture. Includes luxury villa stay, daily breakfast, and guided tours to Ubud and Tanah Lot.",
        'image_path'    => 'images/packages/bali.jpg',
    ],
    [
        'package_id'    => 2,
        'title'         => 'Swiss Alps Adventure',
        'destination'   => 'Swiss Alps, Switzerland',
        'price'         => 1899.00,
        'duration_days' => 6,
        'description'   => "Breathtaking mountain views, scenic train rides, and charming alpine villages. Perfect for nature lovers and adventure seekers.",
        'image_path'    => 'images/packages/alps.jpg',
    ],
    [
        'package_id'    => 3,
        'title'         => 'Dubai Luxury Getaway',
        'destination'   => 'Dubai, UAE',
        'price'         => 1599.00,
        'duration_days' => 5,
        'description'   => "Discover the dazzling city of Dubai. Stay in a 5-star hotel, visit Burj Khalifa, desert safari, and enjoy world-class shopping.",
        'image_path'    => 'images/packages/dubai.jpg',
    ],
    [
        'package_id'    => 4,
        'title'         => 'Italian Romance Tour',
        'destination'   => 'Rome & Florence, Italy',
        'price'         => 1749.00,
        'duration_days' => 8,
        'description'   => "Walk through history in Rome and fall in love with the art of Florence. Includes guided Colosseum tour, Vatican visit, and authentic Italian dining experiences.",
        'image_path'    => 'images/packages/itali.jpg',
    ],
];

$existingCount = $packagesCollection->countDocuments([]);

if ($existingCount === 0) {
    $packagesCollection->insertMany($samplePackages);
    echo "✓ Inserted " . count($samplePackages) . " sample packages\n";
} else {
    echo "• Packages collection already has data ($existingCount documents) – skipped\n";
}

echo "\nDone! You can now log in at /admin with admin / admin123\n";
