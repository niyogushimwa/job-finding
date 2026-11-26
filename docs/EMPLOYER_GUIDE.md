# Employer Guide

JobLink Rwanda connects verified Rwandan employers with prepared candidates. Employers can publish jobs, moderate applications, schedule interviews and measure hiring performance.

## 1. Complete your company profile

After registration, open **Employer → Company profile** and add:

- official company name and description;
- industry, country, physical location and address;
- website, email and phone;
- company size and founding year;
- logo and banner images (JPG/PNG/WebP, max 5 MB).

Submit the profile for verification through **Admin → Employers** (administrator action). A verified badge increases candidate trust and appears on jobs and company pages.

## 2. Post a job

Go to **Employer → Post a job** and complete:

| Section | What to include |
|---|---|
| Basics | Clear title, category, industry, country and location |
| Details | Description, responsibilities, requirements and benefits |
| Terms | Employment type, work mode, experience level, education, salary range and period |
| Skills | Select required/optional skills; weights drive the match score |
| Deadline | Optional date; jobs expire automatically from search after the deadline |

Choose **Save draft** to keep working, or **Submit for review**. Administrators publish approved jobs. Administrator accounts can publish directly.

Editing a job syncs its skill list. Changing status to **Closed** or **Archived** removes it from public search; deleting is a soft delete that preserves historical applications.

## 3. Review applicants

**Employer → Applicants** lists every application with:

- applicant name, headline and location;
- experience, education and skills;
- match score computed at submission time;
- cover letter, uploaded résumé and public profile link.

Filter by job, status, skill or education. Open an applicant to see the CV, profile details and the full status history.

Update the status through the dropdown:

```
submitted → under_review → shortlisted → interview_scheduled
→ interview_completed → selected (or rejected)
```

Every change is recorded in `application_history` and generates an in-app notification to the candidate. Applicants can also withdraw early-stage applications.

## 4. Find candidates

**Employer → Find candidates** searches the seeker pool by keyword, location, skill, education and experience. Open a candidate's public profile to review their portfolio and endorsements, then use **Messages** to contact them.

**Shortlisted** gives a focused pipeline of everyone who has moved beyond initial review.

## 5. Schedule interviews

In **Employer → Interviews**:

1. Choose an application from one of your jobs.
2. Enter date/time, duration (15–240 minutes), location or meeting link, and notes.
3. Submit. The application automatically moves to `interview_scheduled` and the candidate is notified.

Mark interviews completed or cancelled. Completed interviews move the application to `interview_completed`.

## 6. Analytics

**Employer → Analytics** shows:

- total jobs, published jobs and views;
- applications and shortlisted candidates;
- upcoming interviews;
- applications by status;
- application trend over the last 30 days;
- most-viewed jobs.

Use these numbers to improve weak listings: low views usually mean a weak title/location, while high views with few applications usually mean requirements or salary need adjustment.

## 7. Hiring safely

- Verify identity and documents before making an offer.
- Never ask candidates to pay recruitment fees.
- Keep conversations and offers on record in the platform.
- Report suspicious candidate behaviour to administrators.
- Respect candidate privacy: profile contact details are limited by design.

## 8. Operational notes

- New jobs default to `pending_review` until an administrator approves them.
- Applications are unique per job and user; duplicate submissions are blocked.
- All mutating requests require CSRF tokens and role checks.
- Uploaded logos and résumés are renamed randomly and stored outside the web root's executable paths.
