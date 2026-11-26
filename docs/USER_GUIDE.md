# User Guide — Job Seekers & Freelancers

This guide explains how to get the most out of JobLink Rwanda. Every action writes to the database; there is no fake demo state.

## Create your account

1. Open **Register**.
2. Choose **Job seeker**, **Employer** or **Freelancer**.
3. Enter your name, email, password, phone and location.
4. Open the verification link from `storage/logs/mail.log` (local log mail driver) or your inbox if SMTP is configured.

Your dashboard shows your profile completion score. Completing more fields improves the job-match score and makes your public profile more useful.

## Build a profile that gets matched

Go to **Profile → Edit profile** and complete:

- headline and professional summary;
- location, preferred location and remote preference;
- years of experience and education level;
- employment types you accept;
- expected salary range;
- LinkedIn, GitHub and personal website links.

Then add:

- **My skills** — choose a skill, level and years of experience. Others can endorse skills on public profiles.
- **Profile entries** — education, work experience and certifications.
- **My portfolio** — projects with live links, repository links, tags and completion dates.
- **My CV** — use the built-in builder with repeatable sections, choose a template and download a print-ready copy. You can also upload a PDF résumé and set a default.
- **Avatar** — upload a JPG, PNG or WebP image (max 5 MB by default).

## Find the right opportunity

### Job search

Use **Find Jobs** to filter by keyword, location, category, industry, country, employment type, work mode, experience, salary, education, publish date and deadline. Results update without a full page reload when JavaScript is available, but the same filters work with ordinary form submission.

Save a search from the sidebar to return to it later. Save individual jobs with the bookmark button.

### Smart match score

On a job detail page, signed-in seekers and freelancers see a compatibility score (0–100) with a breakdown:

| Factor | Maximum points |
|---|---:|
| Skills (weighted, required skills matter more) | 42 |
| Experience | 14 |
| Education | 10 |
| Location / remote preference | 14 |
| Employment type | 10 |
| Skill category alignment | 10 |

The score is computed from your actual profile, not a random number. The page also lists skills you already have and skills you are missing, so you know what to improve.

### Opportunity Radar

**Opportunity Radar** aggregates third-party listings: internships, remote jobs, freelance projects, scholarships and fellowships. Each item shows the organisation, deadline, location, source and verification status. **Always apply through the official application link** and never pay a fee to apply.

Expired opportunities are automatically marked expired when the Radar page is opened.

## Apply and track

1. Open a job and select **Apply now**.
2. Write a tailored cover letter (at least 20 characters).
3. Choose a PDF résumé if you uploaded one.
4. Submit. The application count increases and the employer is notified.

Track every stage in **My applications**: submitted → under review → shortlisted → interview scheduled → interview completed → selected/rejected. The timeline shows who changed the status and when. You may withdraw an application while it is still early in the process.

## Alerts and notifications

Create **Job alerts** with keywords, location, category, employment type and daily or weekly frequency. In-app notifications appear in the header; open **Notifications** to review them. Notification preferences are under **Settings**.

## Messages and community

- **Messages** lets you start a conversation with another member and keeps threads private to participants.
- **Career Community** lets you share experiences, ask questions, like posts and comment. Report inappropriate posts; administrators review reports.

## Career Growth Centre

Read practical articles, tips and success stories. **Interview preparation** groups common, behavioural, technical and HR questions with sample answers and tips. Use it together with your portfolio and CV before interviews.

## Safety rules

- Never pay money to get a job or to receive an offer letter.
- Check the company's verified badge and website.
- Be careful with requests for sensitive documents before a formal offer.
- Use **Report this opportunity** on job pages or report community posts.
- Keep communication inside the platform where possible.

## Public profile

Your public profile lives at `/u/{your-id}` and is visible when **Open to work** is enabled. It shows skills, endorsements, portfolio, education and experience — never your email or phone to anonymous visitors. Share this URL in your portfolio or CV.

## Freelancer workspace

Freelancers get a dedicated dashboard with:

- a freelance profile (title, bio, hourly/project rates, availability, response time);
- services you offer;
- a projects board with budget and skill filters;
- one-click proposals with cover letter and bid;
- job recommendations from the same matching engine.

Accepted proposals and new messages generate in-app notifications.
