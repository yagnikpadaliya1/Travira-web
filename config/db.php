<?php
/**
 * MongoDB Atlas Database Connection
 * ---------------------------------
 * This replaces the old MySQL PDO connection.
 * Requires: mongodb/mongodb Composer package + PHP mongodb extension.
 *
 * Setup:
 * 1. Create a free cluster at https://www.mongodb.com/cloud/atlas
 * 2. Create a database user and whitelist your IP (or 0.0.0.0/0 for testing)
 * 3. Get the connection string and paste it below (or set MONGO_URI env var)
 * 4. Run: composer install
 * 5. Ensure the PHP mongodb extension is installed (pecl install mongodb)
 */

require_once __DIR__ . '/../vendor/autoload.php';

// ----------------------------------------------------------------
// REPLACE THIS with your real MongoDB Atlas connection string
// Example: mongodb+srv://username:password@cluster0.abcde.mongodb.net/?retryWrites=true&w=majority
// ----------------------------------------------------------------
$mongoUri = getenv('MONGO_URI') ?: 'mongodb+srv://preetp0270_db_user:47G322528A@travira.mhomq2e.mongodb.net/?appName=Travira';

try {
    $client = new MongoDB\Client($mongoUri, [
        'serverSelectionTimeoutMS' => 5000,
    ]);

    // Select the database
    $db = $client->selectDatabase('Travira_PHP');

    // Collections (equivalent to MySQL tables)
    $packagesCollection = $db->selectCollection('packages');
    $bookingsCollection = $db->selectCollection('bookings');
    $adminsCollection   = $db->selectCollection('admins');

} catch (Exception $e) {
    die("Database connection failed: " . $e->getMessage());
}

/**
 * Helper: Get the next auto-increment style integer ID for a collection.
 * MongoDB does not have AUTO_INCREMENT, so we generate sequential IDs.
 */
function getNextSequenceId(MongoDB\Collection $collection, string $idField): int
{
    $last = $collection->findOne(
        [],
        ['sort' => [$idField => -1], 'projection' => [$idField => 1]]
    );
    return $last && isset($last[$idField]) ? (int)$last[$idField] + 1 : 1;
}

/**
 * Helper: Convert MongoDB BSON document to plain associative array
 * and ensure integer IDs are proper PHP ints.
 */
function docToArray($doc): ?array
{
    if ($doc === null) {
        return null;
    }
    $arr = (array)$doc;
    // Convert BSON types to native PHP types where helpful
    if (isset($arr['package_id'])) {
        $arr['package_id'] = (int)$arr['package_id'];
    }
    if (isset($arr['booking_id'])) {
        $arr['booking_id'] = (int)$arr['booking_id'];
    }
    if (isset($arr['price'])) {
        $arr['price'] = (float)$arr['price'];
    }
    if (isset($arr['duration_days'])) {
        $arr['duration_days'] = (int)$arr['duration_days'];
    }
    if (isset($arr['number_of_people'])) {
        $arr['number_of_people'] = (int)$arr['number_of_people'];
    }
    return $arr;
}
?>
