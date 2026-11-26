# Deployment

This project runs on a conventional LAMP stack. The instructions below assume a Linux VPS with Apache or Nginx + PHP-FPM and MySQL/MariaDB.

## 1. Prepare the server

```bash
sudo apt update
sudo apt install apache2 mysql-server php8.3 php8.3-mysql php8.3-mbstring php8.3-fileinfo php8.3-xml php8.3-curl
sudo a2enmod rewrite headers
sudo systemctl restart apache2
```

Install Composer is **not** required.

## 2. Deploy the code

```bash
sudo mkdir -p /var/www/joblinkrwanda
sudo chown -R $USER:www-data /var/www/joblinkrwanda
git clone https://github.com/niyogushimwa/job-finding.git /var/www/joblinkrwanda
cd /var/www/joblinkrwanda
cp .env.example .env
```

Edit `.env`:

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://jobs.example.rw
DB_HOST=127.0.0.1
DB_NAME=job_finding
DB_USER=joblink
DB_PASS=use-a-strong-password
MAIL_DRIVER=smtp
SMTP_HOST=smtp.example.com
SMTP_PORT=587
SMTP_USER=no-reply@example.com
SMTP_PASS=smtp-password
SMTP_ENCRYPTION=tls
```

Create the database/user and import schema + seed, then **change all demo passwords immediately**.

```bash
mysql -u root -p -e "CREATE DATABASE job_finding CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; CREATE USER 'joblink'@'127.0.0.1' IDENTIFIED BY 'use-a-strong-password'; GRANT ALL ON job_finding.* TO 'joblink'@'127.0.0.1'; FLUSH PRIVILEGES;"
mysql -u root -p job_finding < database/schema.sql
mysql -u root -p job_finding < database/seed.sql
```

## 3. Permissions

```bash
sudo chown -R www-data:www-data /var/www/joblinkrwanda
sudo find /var/www/joblinkrwanda -type d -exec chmod 755 {} \;
sudo find /var/www/joblinkrwanda -type f -exec chmod 644 {} \;
sudo chmod -R 775 storage/logs storage/cache uploads
```

`config/`, `database/`, `app/` and `storage/` are protected by `.htaccess` and must not be reachable directly. The front controller also refuses to serve any path outside `assets/`, `uploads/`, `manifest.json` and `sw.js`.

## 4. Apache virtual host

```apache
<VirtualHost *:80>
    ServerName jobs.example.rw
    Redirect permanent / https://jobs.example.rw/
</VirtualHost>

<VirtualHost *:443>
    ServerName jobs.example.rw
    DocumentRoot /var/www/joblinkrwanda

    SSLEngine on
    SSLCertificateFile /etc/letsencrypt/live/jobs.example.rw/fullchain.pem
    SSLCertificateKeyFile /etc/letsencrypt/live/jobs.example.rw/privkey.pem

    <Directory /var/www/joblinkrwanda>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/joblink-error.log
    CustomLog ${APACHE_LOG_DIR}/joblink-access.log combined
</VirtualHost>
```

Enable with `sudo a2ensite joblink.conf && sudo systemctl reload apache2`.

## 5. Nginx equivalent

Use PHP-FPM and send every unknown path to `index.php`:

```nginx
server {
    listen 443 ssl http2;
    server_name jobs.example.rw;
    root /var/www/joblinkrwanda;
    index index.php;

    ssl_certificate /etc/letsencrypt/live/jobs.example.rw/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/jobs.example.rw/privkey.pem;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
    }

    location ~ ^/(app|config|database|storage)(/|$) { deny all; }
    location ~ /\. { deny all; }
}
```

Nginx does not use `.htaccess`; the `deny` rules above are mandatory.

## 6. TLS and security checklist

- Serve only over HTTPS with HSTS after confirming the certificate works.
- Set `APP_DEBUG=false`; verify the error page does not leak stack traces.
- Change demo passwords and the demo employer/admin email addresses if desired.
- Keep `.env` out of the web root's public path (it is in the project root and blocked by both Apache and the front controller).
- Verify `https://your-domain/api/health` returns JSON.
- Verify `https://your-domain/.env` returns 403/404.
- Restrict database access to `127.0.0.1`.
- Set up automated off-site database backups (`mysqldump`) and include `uploads/`.
- Monitor `storage/logs/php-error.log` and `storage/logs/mail.log`.

## 7. Optional maintenance

`Opportunity::expireOld()` runs when the Opportunity Radar page is loaded and moves third-party listings with past deadlines to `expired`. For guaranteed expiry without traffic, your scheduler can call the same model from a small CLI script:

```bash
php -r "require 'app/bootstrap.php'; App\Models\Opportunity::expireOld();"
```

Run it daily with cron. Do not expose that command as a public URL.

## 8. Updating

```bash
cd /var/www/joblinkrwanda
git pull origin main
# If new SQL migrations are added:
mysql -u joblink -p job_finding < database/migrations/xxxx.sql
```

Keep `.env`, `storage/` and `uploads/` intact during deployments.

## 9. Rollback

Keep the previous release directory and a database dump. To roll back, repoint the document root and restore the dump:

```bash
mysql -u joblink -p job_finding < backup-before-deploy.sql
```
