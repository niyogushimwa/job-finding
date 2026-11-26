# Installation

JobLink Rwanda is a plain PHP 8.1+ + MySQL/MariaDB application (PHP 8.3 recommended). There are no Composer packages to install and no paid services are required.

## 1. Requirements

- PHP **8.1+** with extensions: `pdo_mysql`, `mbstring`, `fileinfo`, `openssl`, `json`, `session`.
- MySQL 8.0+ or MariaDB 10.4+.
- Apache 2.4 with `mod_rewrite` (recommended) **or** the PHP built-in server for local development.
- A writable `storage/logs` and `uploads` directory.

## 2. Get the code

```bash
git clone https://github.com/niyogushimwa/job-finding.git
cd job-finding
```

If you are using XAMPP, place the folder here:

```
C:\xampp\htdocs\job-finding
```

## 3. Configure the environment

Copy the example file and edit it:

```bash
cp .env.example .env       # Linux / macOS
copy .env.example .env     # Windows PowerShell
```

Minimum local configuration:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost/job-finding

DB_HOST=127.0.0.1
DB_PORT=3306
DB_NAME=job_finding
DB_USER=root
DB_PASS=

MAIL_DRIVER=log
```

> `.env` is excluded from Git. Never commit real database passwords, SMTP credentials or API keys.

For a domain deployment, set `APP_ENV=production`, `APP_DEBUG=false` and the real `APP_URL`.

## 4. Create the database

Start MySQL/MariaDB, then create the database:

```sql
CREATE DATABASE job_finding CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Import the schema and demo data:

```bash
mysql -u root -p job_finding < database/schema.sql
mysql -u root -p job_finding < database/seed.sql
```

On Windows PowerShell, `<` redirection is not supported. Use:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -D job_finding -e "SOURCE C:/xampp/htdocs/job-finding/database/schema.sql"
& "C:\xampp\mysql\bin\mysql.exe" -u root -D job_finding -e "SOURCE C:/xampp/htdocs/job-finding/database/seed.sql"
```

The seed file is safe to import again: it uses `INSERT ... ON DUPLICATE KEY UPDATE` for reference data and creates demo users only if their emails do not exist.

## 5. Run the application

### Option A — XAMPP / Apache

1. Start Apache and MySQL from the XAMPP control panel.
2. Visit `http://localhost/job-finding`.
3. Pretty URLs work through `.htaccess` without extra configuration.

### Option B — PHP built-in server

From the project root:

```bash
C:\xampp\php\php.exe -S 127.0.0.1:8899 -t . index.php
```

Visit `http://127.0.0.1:8899`.

> Use the XAMPP PHP binary on Windows: the PHP installed on `PATH` may not have `pdo_mysql` enabled.

## 6. Demo accounts

| Role | Email | Password |
|---|---|---|
| Administrator | `admin@joblinkrwanda.rw` | `Admin@12345` |
| Employer | `employer@joblinkrwanda.rw` | `Employer@123` |
| Job seeker | `seeker@joblinkrwanda.rw` | `Seeker@123` |
| Freelancer | `freelancer@joblinkrwanda.rw` | `Freelancer@123` |

Change these passwords before exposing the application publicly.

## 7. Verify the installation

Open these URLs:

- `http://localhost/job-finding/api/health` → JSON with `"ok": true` and `"database": "connected"`.
- `http://localhost/job-finding/jobs` → job search page.
- `http://localhost/job-finding/opportunity-radar` → external opportunities.
- `http://localhost/job-finding/career-centre` → career articles.

## 8. First admin tasks

1. Log in with the administrator account.
2. Open **Admin → Employers** and verify the demo employer company. This makes its jobs show as trusted.
3. Open **Admin → Jobs** and publish any `pending_review` jobs.
4. Open **Admin → External opportunities** and verify curated third-party listings.
5. Open **Admin → Settings** to update contact details and platform notices.

## 9. Common problems

| Symptom | Fix |
|---|---|
| `Database connection failed` | Check `.env` credentials and that MySQL is running. |
| 404 on every page except the homepage | Enable `mod_rewrite` / `AllowOverride All`, or use the PHP built-in server router. |
| CSS/JS missing | Check `APP_URL` and that `assets/` is readable by the web server. |
| Uploads fail | Make `uploads/` writable and check `UPLOAD_MAX_SIZE`. |
| Blank page | Check `storage/logs/php-error.log` and set `APP_DEBUG=true` temporarily. |
| Emails not delivered | Expected locally: `MAIL_DRIVER=log` writes to `storage/logs/mail.log`. |

## 10. Directory overview

```
app/
  Bootstrap.php, Core/, Controllers/, Models/, Views/
assets/
  css/style.css, js/app.js, img/icon.svg
config/
  app.php, routes.php
database/
  schema.sql, seed.sql
docs/
  installation, user, employer, API, testing and deployment guides
storage/
  logs/          (private, writable)
uploads/
  .htaccess      (blocks script execution)
```
