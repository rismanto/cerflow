# Cerflow — Claim-Evidence-Reasoning Learning Platform

Cerflow is a web-based learning platform designed to help students practice scientific argumentation through the **Claim-Evidence-Reasoning (CER)** framework. Students reconstruct conceptual connections by visually linking claims, evidence, and reasoning cards in an interactive workspace.

![Cerflow Banner](https://img.shields.io/badge/Stack-PHP_8%2B_|_MariaDB_|_Vanilla_JS-blue?style=for-the-badge)
![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)

## 🚀 Features

### For Teachers (Guru)
- **CER Map Studio**: A drag-and-drop editor to create complex CER modules with custom triplets.
- **Advanced Analytics**: Monitor student behavior in real-time, including time spent, connection patterns, and feedback usage.
- **Detailed Reporting**: View and export student scores and interaction logs to Excel for research analysis.
- **User Management**: Bulk import students via CSV and manage accounts easily.
- **Feature Controls**: Toggle feedback availability and other settings on a per-module basis.

### For Students (Siswa)
- **Interactive Workspace**: Reconstruct maps using a modern, touch-friendly interface with SVG-based visual connections.
- **Auto-Arrange**: Intelligent card sorting using FLIP animations to help organize complex thoughts.
- **Real-time Feedback**: Learn from mistakes with color-coded connection validation (if enabled by teacher).
- **Submission History**: Review past attempts and see how their reasoning has evolved.

## 🐳 Docker Setup (Recommended)

The easiest way to run Cerflow is using Docker. This will automatically set up the PHP 8.2 environment and a MySQL 8.0 database.

1. **Clone the Repository**:
   ```bash
   git clone https://github.com/rismanto/cerflow.git
   cd cerflow
   ```

2. **Run with Docker Compose**:
   ```bash
   docker compose up -d
   ```

3. **Access the App**:
   - Web App: [http://localhost:8080](http://localhost:8080)
   - phpMyAdmin: [http://localhost:8081](http://localhost:8081)
   - Database: Accessible on port `3306`
   - Default Login: `admin` / `admin123`

*The database is automatically initialized with the schema and seed data from `database.sql`.*

## 🛠️ Manual Installation (XAMPP/Laragon)

| Layer | Technology |
|---|---|
| **Backend** | PHP 8.0+ (Procedural + OOP Models) |
| **Database** | MariaDB / MySQL (PDO with Prepared Statements) |
| **Frontend** | Vanilla JavaScript (ES6+), Tailwind CSS |
| **Drag & Drop** | SortableJS |
| **Data Export** | SheetJS (XLSX) |
| **UI Components** | TomSelect (Searchable Dropdowns) |

## 📦 Installation

1. **Prerequisites**:
   - PHP 8.0 or higher
   - MySQL 5.7 or MariaDB 10.4
   - Web server (Apache/Nginx) or XAMPP/Laragon

2. **Clone the Repository**:
   ```bash
   git clone https://github.com/rismanto/cerflow.git
   cd cerflow
   ```

3. **Database Setup**:
   - Create a database named `cer_flow_db`.
   - Import the `database.sql` file:
     ```bash
     mysql -u root -p cer_flow_db < database.sql
     ```

4. **Configuration**:
   - Open `app/Config/Database.php` and update your credentials:
     ```php
     private $host = "localhost";
     private $db_name = "cer_flow_db";
     private $username = "root";
     private $password = "your_password";
     ```

5. **Default Login**:
   - **Username**: `admin`
   - **Password**: `admin123`

## 📂 Project Structure

```
cerflow/
├── app/                  # Core logic & database models
├── assets/               # CSS and JavaScript (admin.js, siswa.js)
├── partials/             # Reusable UI components (navbar, footer)
├── scratch/              # Development utility scripts
├── admin.php             # Map Studio Editor
├── siswa.php             # Student Workspace
├── report.php            # Analytics Dashboard
└── database.sql          # Core schema & seed data
```

## 📝 License

Distributed under the MIT License. See `LICENSE` for more information.

---
*Built with ❤️ for Educational Research.*
