# JobLink Rwanda

**A Rwanda-first job, internship, scholarship and freelance platform — built from scratch in plain PHP and MySQL.**

JobLink Rwanda connects job seekers, employers, freelancers and administrators in one mobile-first web application. It goes beyond a basic job board with transparent smart matching, an Opportunity Radar for external listings, an online CV builder, a Career Growth Centre, skill endorsements, portfolio proof, application tracking, scam-safety guidance, community discussions, interview preparation, messaging, analytics and a full admin content-management area.

No Composer packages, no paid APIs and no framework licence are required.

---

## Why this project exists

Finding work in Rwanda often means jumping between scattered websites, WhatsApp groups and unverified postings. JobLink Rwanda brings those paths together:

- Seekers get one place to build a profile, discover relevant roles, track applications and prepare for interviews.
- Employers get a workspace to publish jobs, review applicants, schedule interviews and see hiring analytics.
- Freelancers get a profile, service catalogue and project proposal board.
- Administrators get moderation, verification, reporting, content and platform-settings tools.

The product is deliberately honest: employers are not automatically trusted, external opportunities are clearly labelled, and skills are endorsed by people rather than self-declared as certified.

---

## Highlights

- **Smart matching** based on real skills, experience, education, location, work mode and preferences.
- **Opportunity Radar** for curated external jobs, internships, remote roles and scholarships with verification status.
- **CV builder** with three print-ready templates, plus PDF resume uploads and a downloadable HTML CV.
- **Career Growth Centre** with articles, skill guides and interview question practice.
- **Skill verification & portfolio** with endorsements, project links, certificates and public profiles.
- **Application tracker** with status history, interviews, alerts and withdrawal rules.
- **Scam protection** guidance, job reporting and admin moderation.
- **Community** discussions with comments, likes and reports.
- **Secure messaging** between seekers, employers and admins.
- **Role-based dashboards** for seeker, employer, freelancer and admin.
- **Analytics** for platform health, employer hiring funnel and admin moderation.
- **Admin CMS** for jobs, companies, categories, opportunities, articles, questions, ads, announcements and settings.
- **Mobile-first responsive UI** with light/dark themes, English/Kinyarwanda/French language switching and PWA/offline basics.
- **Rwanda-first data**: provinces, Kigali locations, RWF salary fields and local demo employers.

---

## Quick start

Requirements: PHP **8.1+** (8.3 recommended), MySQL 8+/MariaDB 10.4+, Apache with `mod_rewrite` or the PHP built-in server.

```bash
git clone https://github.com/niyogushimwa/job-finding.git
cd job-finding
cp .env.example .env       # Windows: copy .env.example .env
```

Create the database, then import the schema and seed data:

```bash
mysql -u root -p job_finding < database/schema.sql
mysql -u root -p job_finding < database/seed.sql
```

On Windows PowerShell:

```powershell
& "C:\xampp\mysql\bin\mysql.exe" -u root -D job_finding -e "SOURCE C:/xampp/htdocs/job-finding/database/schema.sql"
& "C:\xampp\mysql\bin\mysql.exe" -u root -D job_finding -e "SOURCE C:/xampp/htdocs/job-finding/database/seed.sql"
```

Run locally:

```bash
# XAMPP Apache: visit http://localhost/job-finding
# Or the built-in server:
C:\xampp\php\php.exe -S 127.0.0.1:8899 -t . index.php
```

Then open `http://127.0.0.1:8899/api/health`. A healthy install returns `"ok": true` and `"database": "connected"`.

Full setup details are in [docs/INSTALLATION.md](docs/INSTALLATION.md).

---

## Demo accounts

| Role | Email | Password | Landing page |
|---|---|---|---|
| Administrator | `admin@joblinkrwanda.rw` | `Admin@12345` | `/admin/dashboard` |
| Employer | `employer@joblinkrwanda.rw` | `Employer@123` | `/employer/dashboard` |
| Job seeker | `seeker@joblinkrwanda.rw` | `Seeker@123` | `/dashboard` |
| Freelancer | `freelancer@joblinkrwanda.rw` | `Freelancer@123` | `/freelancer/dashboard` |

Change these passwords before exposing the app publicly. Mail defaults to the log driver and writes to `storage/logs/mail.log`.

---

## Role quick tour

| Role | Start here |
|---|---|
| Job seeker | Complete the profile, add skills and portfolio projects, save jobs, set up alerts, build a CV, apply and track status. |
| Employer | Update the company, post a job, review applicants, shortlist, schedule an interview and open analytics. |
| Freelancer | Update the service catalogue, browse open projects and submit a proposal with a bid. |
| Admin | Verify an employer, moderate a pending job, resolve a report, curate external opportunities and edit site settings. |

User-facing walkthroughs are in [docs/USER_GUIDE.md](docs/USER_GUIDE.md) and [docs/EMPLOYER_GUIDE.md](docs/EMPLOYER_GUIDE.md).

---

## Architecture

```text
index.php                 Front controller / public entry point
app/
  Bootstrap.php           Autoloading, app boot
  Core/                   Router, Request, Controller, Database, Auth,
                          Validator, Mailer, Upload, Paginator, Support
  Controllers/            Public, auth, seeker, employer, freelancer,
                          admin, community, opportunity and API controllers
  Models/                 User, Job, Application, Admin and domain queries
  Views/                  Server-rendered PHP templates + partials
config/
  app.php                 Application configuration
  routes.php              147 declared routes
database/
  schema.sql              Normalized MySQL schema
  seed.sql                Repeatable demo and reference data
assets/                   CSS, vanilla JS and icons
docs/                     Installation, user, employer, API, testing, deployment
storage/logs/             Private writable logs
uploads/                  Private-by-rule user files with execution denied
tools/
  verify_routes.php       Route-to-controller verification helper
  integration_test.ps1    Local POST + database integration harness
sw.js, manifest.json      PWA service worker and manifest
```

The application uses PDO with prepared statements, bcrypt password hashes, per-session CSRF tokens, role middleware, rate limiting on sensitive forms, audit logging, validated uploads with randomized filenames and environment-based secrets.

---

## Documentation

- [Installation](docs/INSTALLATION.md)
- [Job seeker & freelancer guide](docs/USER_GUIDE.md)
- [Employer guide](docs/EMPLOYER_GUIDE.md)
- [JSON API](docs/API.md)
- [Testing report](docs/TESTING.md)
- [Deployment](docs/DEPLOYMENT.md)

---

## Testing status

Verified on Thu Nov 26 2026:

- 119 PHP files linted with 0 failures.
- 147 declared route handlers resolve to real controller actions.
- 23 public pages returned HTTP 200; 5 private paths returned HTTP 404.
- All four demo roles logged in and reached their role home pages.
- 46 authenticated workspace pages returned HTTP 200.
- 118 POST/database integration assertions passed with 0 failures.
- No new warnings or fatals in `storage/logs/php-error.log` after the final run.

Reproduce with:

```powershell
C:\xampp\php\php.exe tools\verify_routes.php
powershell -ExecutionPolicy Bypass -File tools\integration_test.ps1
```

> The integration harness writes real data and restores the seeded demo records at the end. Run it only on a disposable database.

---

## Portfolio description

> JobLink Rwanda is a full-stack, role-based job platform built with PHP 8 and MySQL to make opportunities, skills and hiring more accessible in Rwanda. I designed and implemented 147 routes across seeker, employer, freelancer and admin workspaces, including a skills-based matching engine, external Opportunity Radar, CV builder, application tracker, community, interview prep, secure messaging and an admin CMS. The project emphasizes trustworthy data: prepared statements, CSRF protection, RBAC, audited moderation, verified external listings and endorsed skills. It ships with normalized SQL migrations, repeatable seed data, documentation, PWA/i18n support and a 118-assertion integration harness.

### Demo talking points

1. **Start with the problem:** scattered, unverified opportunities make job searching risky and slow.
2. **Show the four dashboards:** seeker, employer, freelancer and admin in one codebase.
3. **Show matching:** scores come from real profile data, not decorative percentages.
4. **Show trust:** employer verification, external-listing labels, reports, moderation and audit logs.
5. **Show depth:** CV builder, skill endorsements, portfolio, application history, interviews, community and messaging.
6. **Show engineering:** prepared statements, CSRF, RBAC, rate limits, secure uploads, env secrets and no paid dependencies.
7. **Close with verification:** 147 routes resolve, 118 integration assertions pass, private paths are blocked and the error log stays clean.

---

## Deployment note

For production, set `APP_ENV=production`, `APP_DEBUG=false`, real `APP_URL` and SMTP credentials in `.env`, restrict `storage/` and `uploads/` from direct execution, enable HTTPS, and replace all demo passwords. See [docs/DEPLOYMENT.md](docs/DEPLOYMENT.md).
