# Testing report

**Project:** JobLink Rwanda  
**Date:** Thu Nov 26 2026  
**Environment:** Windows, XAMPP, PHP 8.1.4 CLI, MariaDB 10.4.24, PHP built-in server at `http://127.0.0.1:8899`  
**Database:** `job_finding` (seeded demo data)

> Run the integration script only against a disposable or restored database. It writes real rows, then removes them and restores the seeded demo records.

## Summary

| Check | Result |
|---|---|
| PHP syntax lint | 119 files checked, 0 failures |
| Route target verification | 147 declared handlers resolve to real controller actions |
| Public page smoke test | 23/23 returned HTTP 200 |
| Private-path protection | 5/5 returned HTTP 404 |
| Health API | `{"ok":true,...,"database":"connected"}` |
| Role login + home page | 4/4 roles: login `302`, role home `200` |
| Authenticated workspace pages | 46/46 returned HTTP 200 (seeker 15, employer 10, freelancer 6, admin 15) |
| POST + database integration | 118 assertions passed, 0 failed |
| PHP error log after final run | empty (no new warnings, notices or fatals) |
| Test-data cleanup | verified; demo records restored |

## Commands used

From the project root:

```powershell
# 1. Verify all declared routes point to existing controller methods.
C:\xampp\php\php.exe tools\verify_routes.php

# 2. Lint every PHP file.
$files = Get-ChildItem -Recurse -Filter *.php -File
$fail = 0
foreach ($f in $files) {
  C:\xampp\php\php.exe -l $f.FullName | Out-Null
  if ($LASTEXITCODE -ne 0) { $fail++ }
}
"Linted $($files.Count) PHP files; failures=$fail"

# 3. Start the local server if it is not already running.
C:\xampp\php\php.exe -S 127.0.0.1:8899 -t . index.php

# 4. Run the end-to-end POST/DB harness.
powershell -ExecutionPolicy Bypass -File tools\integration_test.ps1
```

The integration harness writes its detailed pass/fail lines to:

```
%TEMP%\joblink-integration\post_results.txt
```

Optional environment overrides for the harness:

```powershell
$env:JOBLINK_BASE_URL = 'http://127.0.0.1:8899'
$env:JOBLINK_MYSQL    = 'C:\xampp\mysql\bin\mysql.exe'
$env:JOBLINK_DB_USER  = 'root'
$env:JOBLINK_DB_NAME  = 'job_finding'
```

## Coverage by area

| Area | What was exercised |
|---|---|
| Public forms | Contact message and newsletter signup, including database writes |
| Authentication | Registration, logout, forgot-password token, password reset, role selection and self-deactivation |
| Job seeker | Save/unsave job, apply, report job, saved searches, notifications, follow company, profile edit, job alerts, skills, endorsements, portfolio, education/experience/certification entries, CV save, account settings |
| Messages | Start thread, participant rows, two-way replies, message history |
| Community | Create post, comment, like/unlike, report |
| Employer | Company update, job create/edit/status/soft-delete, job-skill joins, application status update, interview schedule/status |
| Admin | User status, employer verification, job moderation, external opportunity CRUD + verification, categories, reports, articles, interview questions, ads, settings and announcements |
| Freelancer | Profile update, service add/delete, project proposal with dynamically created project |
| JSON APIs | Saved searches, notification read, job save toggle; each response checked for HTTP 200 and `ok:true` |
| Security | CSRF token required for every POST, role middleware, private paths blocked, randomized upload filenames and `.htaccess` execution denial reviewed in code |

## Representative HTTP results

```text
seeker:     login=302 home=200
employer:   login=302 home=200
freelancer: login=302 home=200
admin:      login=302 home=200

public pages checked: 23
private paths checked: 5
health: {"ok":true,"app":"JobLink Rwanda","version":"1.0.0","database":"connected",...}
All public/private smoke checks passed.

POST integration tests complete: 118 passed, 0 failed.
```

Private paths confirmed blocked with HTTP 404:

```text
/.env
/config/app.php
/database/schema.sql
/storage/logs/php-error.log
/uploads/.htaccess
```

## Cleanup verification

After the final harness run the following were confirmed in MySQL:

```text
users_test = 0        contact = 0        freelance_projects = 0
cv_documents title   = My Professional CV
platform tagline     = Your Skills Deserve an Opportunity.
audit_logs count     = 16
application statuses = 1 under_review, 2 shortlisted, 3 submitted
```

## Notes and limitations

- Password reset and contact mail use the log driver locally; messages are written to `storage/logs/mail.log` rather than delivered over SMTP.
- The automated harness verifies server behaviour and database state. Responsive layout, theme switching, language switching and PWA install behaviour were implemented and checked manually in the browser during development.
- File upload handling is implemented with randomized names, MIME checks and execution-denial rules; the automated harness focuses on form/JSON writes rather than multipart upload payloads.
- Demo passwords are for local development only and must be changed before public deployment.
