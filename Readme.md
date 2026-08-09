Travira Web – MongoDB Atlas Edition

Travel booking website converted from local MySQL to MongoDB Atlas.

Requirements





PHP 8.0+



Composer



PHP MongoDB extension (ext-mongodb)



A free MongoDB Atlas account

Setup

1. Install PHP MongoDB extension

# On Ubuntu / Debian
sudo pecl install mongodb
# then add "extension=mongodb.so" to php.ini

# On Windows (XAMPP)
# Download the matching dll from https://pecl.php.net/package/mongodb
# and enable it in php.ini

2. Install Composer dependencies

cd Travira-web
composer install

3. Configure MongoDB Atlas connection





Create a free cluster at https://www.mongodb.com/cloud/atlas



Create a database user (username + password)



Network Access → Add IP Address → Allow Access from Anywhere (0.0.0.0/0) for testing



Click Connect → Drivers → copy the connection string



Open config/db.php and replace the placeholder URI:

$mongoUri = getenv('MONGO_URI') ?: 'mongodb+srv://YOUR_USERNAME:YOUR_PASSWORD@YOUR_CLUSTER.mongodb.net/?retryWrites=true&w=majority';

Or set the environment variable:

export MONGO_URI="mongodb+srv://user:pass@cluster0.xxxxx.mongodb.net/?retryWrites=true&w=majority"

4. Seed the database (admin + sample packages)

Run once:

php seed.php

This creates:





Admin user → username: admin / password: admin123



A few sample travel packages

5. Run the site

Point your web server (Apache / Nginx / PHP built-in server) to the project root.

php -S localhost:8000

Then open http://localhost:8000

Collections







Collection



Purpose





packages



Travel packages





bookings



Customer bookings





admins



Admin login accounts

Default Admin Credentials





Username: admin



Password: admin123

Notes





All old MySQL / PDO connection code has been removed.



Integer IDs (package_id, booking_id) are maintained for compatibility with the existing UI.



The $lookup aggregation pipeline is used for joining bookings ↔ packages.

