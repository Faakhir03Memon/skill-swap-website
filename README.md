# SkillSwap Platform

A premium, modern skill exchange platform for students built with PHP and MySQL.

## Features
- **Modern UI**: Dark mode, glassmorphism, and responsive design.
- **AI Skill Matching**: Intelligent recommendation system for skill swaps.
- **Exam System**: Verified tests to prove expertise.
- **Certification**: Automatic generation of professional certificates.
- **Leaderboard**: Global ranking based on contributions and points.
- **Admin Dashboard**: Full control over users, skills, and exams.

## Setup Instructions
1. Import the `database.sql` file into your MySQL database (e.g., via phpMyAdmin).
2. Configure database credentials in `includes/db.php`.
3. Run `seed.php` once to create the initial admin user and seed skills.
4. Access the website via your local server (e.g., XAMPP, WAMP).

## Credentials
- **Admin Email**: `skill@admin.com`
- **Admin Password**: `skill@access.com`

## Project Structure
- `/assets`: CSS and JS files.
- `/includes`: Core logic and DB connection.
- `/admin`: Administrative tools.
- `/student`: Student portal and features.
- `index.php`: Landing page.
- `login.php` / `register.php`: Authentication.
