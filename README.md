# Xnotes - The BICT Resource Hub

## Theme
Student Resource Hub - A centralized, interactive web application that facilitates the efficient sharing, organization, and retrieval of academic resources for BICT undergraduates at Rajarata University of Sri Lanka.

## Setup Instructions
1. Download or clone this repository to your local server directory (e.g., `htdocs` for XAMPP or `www` for WAMP).
2. Open your local database manager (e.g., phpMyAdmin).
3. Import the `database.sql` file provided in the root directory to create the `xnotes` database and its tables (`users`, `message`, `notes`).
4. (Optional) If your MySQL username/password is different from the default (`root` / `empty string`), update the database credentials in `includes/db.php`.
5. Open a web browser and navigate to the project directory (e.g., `http://localhost/project_folder`).

## Features
- **Secure User Authentication & Profile Management**: Registration, login, and OTP-based password reset using university emails (`@tec.rjt.ac.lk`).
- **Dynamic Resource Management System**: Users can upload, categorize, and browse academic materials (PDFs, PPTs, DOCs) based on specific subject modules.
- **Advanced Search and Filtering**: Real-time search functionality and category filtering to easily locate specific study materials.

## Technologies Used
- HTML5, CSS3, Bootstrap 5
- JavaScript (Vanilla)
- PHP 8
- MySQL
