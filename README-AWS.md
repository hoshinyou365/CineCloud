# CineCloud - Final AWS/RDS Version

## What changed
- Movies are loaded from MySQL/RDS instead of localStorage.
- Booking records are inserted into the `bookings` table.
- Confirmation page reads the saved booking from the database.
- Admin movie Add/Edit/Delete uses PHP APIs and MySQL.
- Existing HTML/CSS design is kept.

## Required server software
Amazon Linux + Apache + PHP + PHP MySQL driver + Git.

Example:
sudo dnf install -y httpd php php-mysqlnd git
sudo systemctl enable --now httpd

## Database
Run `database.sql` on your Amazon RDS MySQL database.

## Database configuration
Recommended: set environment variables for Apache/PHP:
CINECLOUD_DB_HOST=your-rds-endpoint
CINECLOUD_DB_NAME=cinecloud
CINECLOUD_DB_USER=admin
CINECLOUD_DB_PASS=your-password
CINECLOUD_DB_PORT=3306

Do not put the real RDS password in GitHub.

For a simple assignment deployment, you may instead edit api/db.php directly on the EC2 server after cloning. Never commit the real password to a public GitHub repository.

## Important
The website must be served through Apache/PHP, e.g.:
http://EC2-or-ALB/

Do NOT open index.html directly with file:// because PHP APIs need a web server.

## API endpoints
GET  api/movies.php
POST api/bookings.php
GET  api/booking_get.php?id=...
POST api/movie_create.php
POST api/movie_update.php
POST api/movie_delete.php
