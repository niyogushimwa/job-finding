-- ============================================================================
-- JobLink Rwanda — development seed data (safe to import after schema.sql)
-- Demo passwords: Admin@12345 / Employer@123 / Seeker@123 / Freelancer@123
-- Passwords are bcrypt hashes — never plain text.
-- ============================================================================

INSERT IGNORE INTO countries (id, name, iso2, currency, currency_symbol, phone_code) VALUES
(1, 'Rwanda', 'RW', 'RWF', 'RWF ', '+250'),
(2, 'Kenya', 'KE', 'KES', 'KSh ', '+254'),
(3, 'Uganda', 'UG', 'UGX', 'UGX ', '+256'),
(4, 'Tanzania', 'TZ', 'TZS', 'TZS ', '+255'),
(5, 'United States', 'US', 'USD', '$', '+1');

INSERT IGNORE INTO locations (id, country_id, name, region, type) VALUES
(1, 1, 'Kigali', 'Kigali City', 'city'),
(2, 1, 'Northern Province', 'Northern Province', 'province'),
(3, 1, 'Southern Province', 'Southern Province', 'province'),
(4, 1, 'Eastern Province', 'Eastern Province', 'province'),
(5, 1, 'Western Province', 'Western Province', 'province'),
(6, 1, 'Butare', 'Southern Province', 'city'),
(7, 1, 'Rwamagana', 'Eastern Province', 'city'),
(8, 2, 'Nairobi', 'Nairobi County', 'city'),
(9, 3, 'Kampala', 'Central Region', 'city');

INSERT IGNORE INTO industries (id, name, slug, description) VALUES
(1, 'Information Technology', 'information-technology', 'Software, infrastructure, data and digital services'),
(2, 'Finance & Banking', 'finance-banking', 'Banking, insurance, fintech and accounting'),
(3, 'Healthcare', 'healthcare', 'Hospitals, pharmaceuticals and public health'),
(4, 'Education', 'education', 'Schools, universities and training providers'),
(5, 'Agriculture', 'agriculture', 'Farming, agribusiness and food processing'),
(6, 'Tourism & Hospitality', 'tourism-hospitality', 'Hotels, travel and cultural experiences'),
(7, 'Construction', 'construction', 'Civil engineering and building works'),
(8, 'NGO & Development', 'ngo-development', 'Non-profits and international development');

INSERT IGNORE INTO job_categories (id, name, slug, description, icon) VALUES
(1, 'Software & IT', 'software-it', 'Development, data, DevOps and support roles', 'code'),
(2, 'Business & Management', 'business-management', 'Operations, strategy and administration', 'briefcase'),
(3, 'Finance & Accounting', 'finance-accounting', 'Accounting, audit and financial analysis', 'chart'),
(4, 'Health & Medicine', 'health-medicine', 'Clinical and public health opportunities', 'heart'),
(5, 'Education & Training', 'education-training', 'Teaching, research and training roles', 'graduation'),
(6, 'Marketing & Sales', 'marketing-sales', 'Growth, communications and commercial roles', 'rocket'),
(7, 'Internships', 'internships', 'Entry-level and internship opportunities', 'award'),
(8, 'Remote Work', 'remote-work', 'Work from anywhere opportunities', 'globe'),
(9, 'NGO & Development', 'ngo-development', 'Community and development programmes', 'users'),
(10, 'Engineering', 'engineering', 'Civil, electrical and mechanical engineering', 'settings');

INSERT IGNORE INTO opportunity_categories (id, name, slug, description, icon) VALUES
(1, 'New Jobs', 'new-jobs', 'Recently published employment opportunities', 'briefcase'),
(2, 'Internships', 'internships', 'Structured learning placements', 'award'),
(3, 'Scholarships', 'scholarships', 'Funded study opportunities', 'graduation'),
(4, 'Fellowships', 'fellowships', 'Professional fellowships and residencies', 'star'),
(5, 'Remote Opportunities', 'remote-opportunities', 'Work from anywhere', 'globe'),
(6, 'Volunteer Positions', 'volunteer-positions', 'Volunteer and community service', 'heart'),
(7, 'Freelance Projects', 'freelance-projects', 'Contract and project work', 'code'),
(8, 'Graduate Programmes', 'graduate-programmes', 'Graduate trainee schemes', 'rocket'),
(9, 'Competitions', 'competitions', 'Hackathons and competitions', 'star'),
(10, 'Training Programmes', 'training-programmes', 'Bootcamps and short courses', 'book'),
(11, 'New Jobs (External)', 'external-jobs', 'Opportunities discovered from trusted external sources', 'briefcase');

INSERT IGNORE INTO skills (id, name, slug, category) VALUES
(1, 'PHP', 'php', 'Programming'), (2, 'JavaScript', 'javascript', 'Programming'),
(3, 'MySQL', 'mysql', 'Databases'), (4, 'HTML & CSS', 'html-css', 'Web'),
(5, 'React', 'react', 'Programming'), (6, 'Python', 'python', 'Programming'),
(7, 'Data Analysis', 'data-analysis', 'Data'), (8, 'Communication', 'communication', 'Soft skills'),
(9, 'Project Management', 'project-management', 'Management'), (10, 'Accounting', 'accounting', 'Finance'),
(11, 'Nursing', 'nursing', 'Health'), (12, 'Graphic Design', 'graphic-design', 'Design'),
(13, 'Sales', 'sales', 'Business'), (14, 'French', 'french', 'Languages'),
(15, 'Kinyarwanda', 'kinyarwanda', 'Languages'), (16, 'English', 'english', 'Languages'),
(17, 'Research', 'research', 'Analysis'), (18, 'Teaching', 'teaching', 'Education');

-- Demo accounts (bcrypt hashes). Change these passwords before any real deployment.
INSERT IGNORE INTO users (id, name, email, password_hash, role, phone, status, email_verified_at, created_at) VALUES
(1, 'Platform Administrator', 'admin@joblinkrwanda.rw', '$2y$10$Q.vO76sAEO8nxfwhDBbC0O0YGBqwarwRK.Hr8leUqWrumRkJ7.YYa', 'admin', '+250788000001', 'active', NOW(), DATE_SUB(NOW(), INTERVAL 180 DAY)),
(2, 'Alice Umuhire', 'employer@joblinkrwanda.rw', '$2y$10$9A8TKqR4KXA3MlS0KQg4iumlz6maS1izN1kpCZbMfdoc5yDGaUje6', 'employer', '+250788000002', 'active', NOW(), DATE_SUB(NOW(), INTERVAL 120 DAY)),
(3, 'Jean Paul Niyo', 'seeker@joblinkrwanda.rw', '$2y$10$q9CxG22csNuPCljHATijSuvL5EZJdXz4XC9rhlVrsY0rIeqP2ABX6', 'seeker', '+250788000003', 'active', NOW(), DATE_SUB(NOW(), INTERVAL 90 DAY)),
(4, 'Grace Inkamena', 'freelancer@joblinkrwanda.rw', '$2y$10$fZ9LMgGa2597WZTlhPCFR.H7PxVTjoWOtBK5XVnj72I2R7eI/F8ny', 'freelancer', '+250788000004', 'active', NOW(), DATE_SUB(NOW(), INTERVAL 60 DAY));

INSERT IGNORE INTO notification_preferences (user_id) VALUES (1),(2),(3),(4);

INSERT IGNORE INTO companies (id, name, slug, description, industry_id, website, email, phone, location, country_id, company_size, founded_year, verification_status, verified_by, verified_at, featured) VALUES
(1, 'Kigali Tech Hub', 'kigali-tech-hub', 'Kigali Tech Hub builds digital products for African businesses and helps young developers grow their careers through mentorship and real projects.', 1, 'https://kigalitechhub.example.com', 'careers@kigalitechhub.example.com', '+250788100100', 'Kigali', 1, '11-50', 2018, 'verified', 1, NOW(), 1),
(2, 'Rwanda Finance Group', 'rwanda-finance-group', 'A growing financial services group offering banking, savings and digital payment solutions across Rwanda.', 2, 'https://rfg.example.com', 'hr@rfg.example.com', '+250788100200', 'Kigali', 1, '201-500', 2010, 'verified', 1, NOW(), 1),
(3, 'Imanzi Health Services', 'imanzi-health-services', 'Private healthcare provider focused on quality, accessible care and community health programmes.', 3, 'https://imanzihealth.example.com', 'jobs@imanzihealth.example.com', '+250788100300', 'Butare', 1, '51-200', 2015, 'pending', NULL, NULL, 0),
(4, 'Umurage Development Initiative', 'umurage-development-initiative', 'A Rwandan non-profit working with communities on education, livelihoods and climate resilience.', 8, 'https://umurage.example.org', 'info@umurage.example.org', '+250788100400', 'Kigali', 1, '11-50', 2016, 'verified', 1, NOW(), 1);

INSERT IGNORE INTO employers (id, user_id, company_id, job_title) VALUES (1, 2, 1, 'Head of People & Talent');

INSERT IGNORE INTO job_seekers (id, user_id, headline, summary, location, preferred_location, country_id, expected_salary_min, expected_salary_max, experience_years, education_level, employment_types, linkedin_url, github_url, profile_completion, open_to_work) VALUES
(1, 3, 'Frontend Developer & UI Enthusiast', 'Final-year computer science student passionate about building accessible web products. I enjoy turning complex problems into simple, useful interfaces.', 'Kigali', 'Kigali', 1, 800000, 1500000, 2, 'Bachelor', 'full_time,internship,remote', 'https://linkedin.com/in/example', 'https://github.com/example', 75, 1);

INSERT IGNORE INTO freelancer_profiles (id, user_id, title, bio, hourly_rate, project_rate, availability, response_time) VALUES
(1, 4, 'Brand Designer & Web Developer', 'I help startups build memorable brands and fast, beautiful websites. 6 years of experience across print and digital.', 15000, 1200000, 'available', 'Within 24 hours');

INSERT IGNORE INTO freelancer_services (id, freelancer_id, title, description, price, price_type) VALUES
(1, 1, 'Brand identity package', 'Logo, colour palette, typography and brand guidelines delivered in editable formats.', 850000, 'project'),
(2, 1, 'Responsive website design', 'A modern, mobile-first website design with developer handoff.', 1200000, 'project');

INSERT IGNORE INTO user_skills (user_id, skill_id, level, years) VALUES
(3, 2, 'advanced', 3), (3, 4, 'advanced', 3), (3, 3, 'intermediate', 2),
(3, 1, 'intermediate', 1), (3, 8, 'advanced', 4), (3, 16, 'advanced', 5),
(3, 15, 'expert', 5), (4, 12, 'expert', 6), (4, 4, 'advanced', 5);

INSERT IGNORE INTO skill_endorsements (skill_id, user_id, endorsed_by) VALUES
(2, 3, 2), (8, 3, 1), (12, 4, 2);

INSERT IGNORE INTO education (id, user_id, school, degree, field_of_study, start_year, end_year, grade) VALUES
(1, 3, 'University of Rwanda', 'Bachelor of Science', 'Computer Science', 2022, 2026, 'Second Class Upper'),
(2, 4, 'Kigali Independent University', 'Bachelor of Arts', 'Graphic Design', 2016, 2020, 'Distinction');

INSERT IGNORE INTO experience (id, user_id, company, title, start_date, end_date, is_current, description) VALUES
(1, 3, 'Kigali Tech Hub', 'Frontend Intern', '2025-06-01', NULL, 1, 'Building internal dashboards and improving accessibility across the product suite.'),
(2, 4, 'Freelance', 'Brand Designer', '2020-01-15', NULL, 1, 'Delivered over 60 brand and web projects for clients in Rwanda and Kenya.');

INSERT IGNORE INTO certifications (id, user_id, name, issuer, issued_year, credential_url, verified) VALUES
(1, 3, 'Responsive Web Design', 'freeCodeCamp', 2025, 'https://freecodecamp.org', 0),
(2, 4, 'Google UX Design Certificate', 'Google', 2023, 'https://coursera.org', 0);

INSERT IGNORE INTO portfolios (id, user_id, title, description, url, repository_url, tags, completed_at) VALUES
(1, 3, 'Community Jobs Board', 'A mobile-first job board built with PHP and MySQL for student associations.', 'https://example.com/project', 'https://github.com/example/jobs-board', 'PHP,MySQL,JavaScript', '2025-11-10'),
(2, 4, 'Umurage Brand Identity', 'Complete visual identity for a community development non-profit.', 'https://example.com/umurage', NULL, 'Branding,Design', '2025-03-01');

INSERT IGNORE INTO resumes (id, user_id, title, file_path, original_name, is_default) VALUES
(1, 3, 'Jean Paul - CV', '', 'jean-paul-cv.pdf', 1);

INSERT IGNORE INTO cv_documents (user_id, title, template, data) VALUES
(3, 'My Professional CV', 'modern', JSON_OBJECT('full_name', 'Jean Paul Niyo', 'email', 'seeker@joblinkrwanda.rw', 'phone', '+250788000003', 'headline', 'Frontend Developer & UI Enthusiast', 'summary', 'Final-year computer science student passionate about building accessible web products.'));

-- Published jobs. Dates are relative so the demo always looks current.
INSERT IGNORE INTO jobs (id, employer_id, company_id, category_id, industry_id, country_id, title, slug, description, requirements, responsibilities, benefits, location, employment_type, work_mode, experience_level, education_level, salary_min, salary_max, salary_currency, salary_period, deadline, status, featured, views_count, applications_count, published_at) VALUES
(1, 1, 1, 1, 1, 1, 'Frontend Developer (React)', 'frontend-developer-react-kigali-tech-hub',
 'Join our product team to build fast, accessible web applications used by thousands of Rwandan entrepreneurs. You will work closely with designers and backend engineers, ship regularly and learn from experienced mentors.',
 'At least 2 years of experience with JavaScript and React. Strong HTML, CSS and accessibility fundamentals. Experience with git and REST APIs. A portfolio of completed projects is an advantage.',
 'Build and maintain responsive interfaces. Collaborate with designers on implementation details. Write tests and participate in code reviews. Improve performance and accessibility.',
 'Health insurance, learning budget, flexible working hours and a supportive team.',
 'Kigali', 'full_time', 'hybrid', 'junior', 'Bachelor', 1200000, 2000000, 'RWF', 'monthly', DATE_ADD(CURDATE(), INTERVAL 21 DAY), 'published', 1, 486, 14, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(2, 1, 1, 7, 1, 1, 'Software Engineering Intern', 'software-engineering-intern-kigali-tech-hub',
 'A paid 6-month internship for students and recent graduates who want hands-on experience building real software with a supportive engineering team.',
 'Currently studying computer science or a related field. Basic knowledge of one programming language. Curiosity, discipline and good communication skills.',
 'Support feature development, write documentation, test applications and participate in weekly learning sessions.',
 'Monthly stipend, mentorship, certificate and possible full-time offer.',
 'Kigali', 'internship', 'onsite', 'entry', 'Bachelor', 300000, 300000, 'RWF', 'monthly', DATE_ADD(CURDATE(), INTERVAL 35 DAY), 'published', 1, 312, 26, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(3, 1, 2, 3, 2, 1, 'Financial Analyst', 'financial-analyst-rwanda-finance-group',
 'We are looking for a detail-oriented analyst to support budgeting, reporting and investment decisions across our growing finance business.',
 'Bachelor degree in finance, accounting or economics. 2-4 years of relevant experience. Advanced Excel and strong analytical writing.',
 'Prepare monthly management reports, build financial models and support the annual budgeting process.',
 'Competitive salary, performance bonus and pension contributions.',
 'Kigali', 'full_time', 'onsite', 'mid', 'Bachelor', 1800000, 2800000, 'RWF', 'monthly', DATE_ADD(CURDATE(), INTERVAL 14 DAY), 'published', 0, 198, 9, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(4, 1, 3, 4, 3, 1, 'Registered Nurse', 'registered-nurse-imanzi-health',
 'Provide high-quality patient care in our growing hospital network. You will work in a multidisciplinary team committed to dignity and evidence-based practice.',
 'Diploma or degree in nursing and a valid licence from the Rwanda Medical and Dental Council. Minimum 2 years of clinical experience.',
 'Assess patients, administer care, maintain accurate records and educate families.',
 'Shift allowances, uniform, insurance and continuous professional development.',
 'Butare', 'full_time', 'onsite', 'mid', 'Diploma', 900000, 1400000, 'RWF', 'monthly', DATE_ADD(CURDATE(), INTERVAL 28 DAY), 'published', 0, 145, 6, DATE_SUB(NOW(), INTERVAL 10 DAY)),
(5, 1, 4, 9, 8, 1, 'Community Development Officer', 'community-development-officer-umurage',
 'Lead community engagement for education and livelihoods programmes in the Southern Province.',
 'Degree in social sciences, development studies or a related field. 3 years of community work. Fluency in Kinyarwanda and English.',
 'Design community activities, train local volunteers, monitor progress and write field reports.',
 'Insurance, field travel allowance and a mission-driven team.',
 'Southern Province', 'full_time', 'onsite', 'mid', 'Bachelor', 1000000, 1500000, 'RWF', 'monthly', DATE_ADD(CURDATE(), INTERVAL 40 DAY), 'published', 0, 88, 4, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(6, 1, 1, 8, 1, 1, 'Remote UI/UX Designer', 'remote-ui-ux-designer-kigali-tech-hub',
 'Work fully remotely with our product team to design intuitive experiences for mobile-first African users.',
 'A strong portfolio, 3+ years of product design experience and proficiency with Figma. Understanding of design systems and accessibility.',
 'Run discovery, produce wireframes and high-fidelity designs, and collaborate with engineers.',
 'Fully remote, flexible hours and annual learning budget.',
 'Remote', 'remote', 'remote', 'mid', 'Bachelor', 1500000, 2500000, 'RWF', 'monthly', DATE_ADD(CURDATE(), INTERVAL 18 DAY), 'published', 1, 267, 11, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(7, 1, 1, 1, 1, 1, 'Data Entry & Support Assistant', 'data-entry-support-assistant-kigali-tech-hub',
 'Help our operations team keep accurate records and provide first-level support to platform users.',
 'High school certificate or above. Good typing speed and attention to detail. Basic computer literacy.',
 'Enter and verify data, respond to support requests and escalate issues.',
 'Friendly team, training and stable working hours.',
 'Kigali', 'part_time', 'onsite', 'entry', 'High School', NULL, NULL, 'RWF', 'monthly', DATE_ADD(CURDATE(), INTERVAL 12 DAY), 'published', 0, 103, 7, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 1, 2, 2, 2, 1, 'Graduate Trainee - Operations', 'graduate-trainee-operations-rwanda-finance-group',
 'A structured 12-month graduate programme rotating through operations, customer service and branch management.',
 'Recent graduate with a bachelor degree. Strong communication and analytical skills. No more than 1 year of experience.',
 'Complete rotations, deliver a capstone project and receive mentorship from senior managers.',
 'Monthly training allowance, mentorship and permanent placement for top performers.',
 'Kigali', 'full_time', 'onsite', 'entry', 'Bachelor', 700000, 700000, 'RWF', 'monthly', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 'published', 0, 176, 18, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(9, 1, 1, 6, 1, 1, 'Digital Marketing Specialist', 'digital-marketing-specialist-kigali-tech-hub',
 'Grow our audience across social and search channels with useful, locally relevant content.',
 'Experience running campaigns on Meta and Google. Strong writing skills and comfort with analytics.',
 'Plan campaigns, write content, track performance and report results.',
 'Performance bonus, flexible schedule and creative freedom.',
 'Kigali', 'full_time', 'hybrid', 'mid', 'Bachelor', 1100000, 1700000, 'RWF', 'monthly', DATE_ADD(CURDATE(), INTERVAL 24 DAY), 'published', 0, 134, 5, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(10, 1, 4, 5, 4, 1, 'Part-time English Tutor', 'part-time-english-tutor-umurage',
 'Teach practical English communication to young adults in our community learning centre.',
 'Excellent English, patience and experience tutoring or teaching. Kinyarwanda is an advantage.',
 'Prepare and deliver lessons, assess learners and give constructive feedback.',
 'Hourly pay, teaching materials provided.',
 'Kigali', 'part_time', 'onsite', 'entry', 'Bachelor', 5000, 7000, 'RWF', 'hourly', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 'published', 0, 76, 3, DATE_SUB(NOW(), INTERVAL 2 DAY));

INSERT IGNORE INTO job_skills (job_id, skill_id, is_required, weight) VALUES
(1,2,1,3),(1,4,1,3),(1,3,1,2),(1,8,0,1),(1,5,0,2),
(2,2,0,2),(2,4,0,2),(2,3,0,1),(2,8,1,2),
(3,10,1,3),(3,7,1,2),(3,8,0,1),
(4,11,1,3),(4,8,1,2),
(5,17,1,2),(5,15,1,3),(5,8,1,2),
(6,12,1,3),(6,8,1,2),(6,4,0,1),
(7,4,1,2),(7,8,1,1),
(8,8,1,3),(8,17,0,2),(8,16,1,2),
(9,13,1,2),(9,7,0,2),(9,8,1,2),
(10,16,1,3),(10,15,0,2),(10,18,0,2);

INSERT IGNORE INTO applications (id, job_id, user_id, cover_letter, status, match_score, created_at) VALUES
(1, 1, 3, 'I am excited about this role because I have been building React interfaces during my internship and would love to grow with your team.', 'under_review', 82, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 2, 3, 'I am a final-year computer science student looking for a structured internship where I can contribute and learn.', 'shortlisted', 78, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 6, 3, 'My design and frontend experience would allow me to work effectively across your product team.', 'submitted', 71, DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT IGNORE INTO application_history (application_id, from_status, to_status, changed_by, note) VALUES
(1, NULL, 'submitted', 3, 'Application submitted'),
(1, 'submitted', 'under_review', 2, 'CV reviewed'),
(2, NULL, 'submitted', 3, 'Application submitted'),
(2, 'submitted', 'shortlisted', 2, 'Strong academic background'),
(3, NULL, 'submitted', 3, 'Application submitted');

INSERT IGNORE INTO saved_jobs (user_id, job_id) VALUES (3, 6), (3, 8), (3, 9);

INSERT IGNORE INTO external_opportunities (id, title, organization, description, requirements, location, country_id, opportunity_category_id, deadline, salary, source_name, source_url, application_url, application_instructions, status, verification_status, verified_by, verified_at, fingerprint, is_featured, discovered_at) VALUES
(1, 'Mastercard Foundation Scholars Programme', 'Mastercard Foundation', 'Fully funded scholarship for academically talented young Africans to pursue undergraduate and master degrees at partner universities.', 'Open to African students with strong academic results and demonstrated financial need. Application requires transcripts and a personal statement.', 'Africa', 1, 3, DATE_ADD(CURDATE(), INTERVAL 60 DAY), 'Fully funded', 'Mastercard Foundation', 'https://mastercardfdn.org', 'https://mastercardfdn.org/scholars', 'Apply through the official scholars portal and upload your transcripts.', 'published', 'verified', 1, NOW(), MD5('mastercard-scholars-2027'), 1, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(2, 'Remote Data Annotation Project', 'Global Data Co.', 'Part-time remote project annotating image datasets for machine learning models. Flexible schedule, weekly payments.', 'Reliable internet, attention to detail and a computer. Prior annotation experience is an advantage.', 'Remote', NULL, 7, DATE_ADD(CURDATE(), INTERVAL 25 DAY), '$8 - $12 per hour', 'Global Data Co.', 'https://example-jobs.com/data', 'https://example-jobs.com/data/apply', 'Create an account and complete the short screening test.', 'published', 'unverified', NULL, NULL, MD5('global-data-annotation'), 0, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3, 'Young African Leaders Fellowship', 'African Leadership Network', 'A six-month virtual fellowship combining leadership training, mentorship and a community project.', 'University students and young professionals under 30 with a record of community contribution.', 'Remote', NULL, 4, DATE_ADD(CURDATE(), INTERVAL 50 DAY), 'Stipend available', 'African Leadership Network', 'https://example-fellowships.org', 'https://example-fellowships.org/apply', 'Submit your CV and a short motivation video.', 'published', 'verified', 1, NOW(), MD5('african-leaders-fellowship'), 1, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(4, 'Nursing Scholarship Programme', 'Rwanda Ministry of Health', 'Scholarship for outstanding nursing students in their final year of study.', 'Enrolled in an accredited nursing programme with a minimum GPA of 3.5.', 'Kigali', 1, 3, DATE_SUB(CURDATE(), INTERVAL 5 DAY), 'Tuition covered', 'Ministry of Health', 'https://moh.gov.rw', 'https://moh.gov.rw/scholarships', 'Submit through your faculty office.', 'expired', 'unverified', NULL, NULL, MD5('rwanda-nursing-scholarship'), 0, DATE_SUB(NOW(), INTERVAL 80 DAY));

INSERT IGNORE INTO articles (id, author_id, title, slug, excerpt, body, category, status, featured, views, published_at) VALUES
(1, 1, 'How to Write a CV That Gets Noticed in Rwanda', 'how-to-write-a-cv-that-gets-noticed-in-rwanda',
 'Practical CV writing advice for Rwandan job seekers, from structure to the details recruiters actually read.',
 '<h2>Start with clarity</h2><p>Recruiters often spend less than a minute on a first pass. Put your name, role and contact details at the top, followed by a short summary that says what you do and what value you bring.</p><h2>Show evidence, not adjectives</h2><p>Instead of writing "hard-working team player", describe a project you delivered, the tools you used and the result. Numbers help: users served, costs saved, time reduced.</p><h2>Keep it relevant</h2><p>Tailor your CV for each application. Highlight the skills listed in the job description and remove unrelated experience where possible. One or two pages is enough for most early-career roles.</p><h2>Proofread</h2><p>Spelling and formatting errors send the wrong signal. Ask a friend to review your CV, export it as PDF and name it clearly: Firstname-Lastname-CV.pdf.</p>',
 'CV Writing', 'published', 1, 1240, DATE_SUB(NOW(), INTERVAL 12 DAY)),
(2, 1, 'Ace Your First Job Interview: A Rwandan Graduate Guide', 'ace-your-first-job-interview-rwanda',
 'Prepare for common questions, research the organisation and present your experience with confidence.',
 '<h2>Before the interview</h2><p>Research the company, read the job description again and prepare three short stories about projects where you solved a problem, worked in a team and learned something new.</p><h2>Answering "tell us about yourself"</h2><p>Keep it to 60-90 seconds: your background, what you are skilled at, and why this role interests you. End by connecting your experience to their needs.</p><h2>Asking questions</h2><p>Good questions show genuine interest. Ask about the team, the first 90 days, and how success is measured. Avoid asking about salary in the first minute unless the interviewer raises it.</p><h2>Afterwards</h2><p>Send a short thank-you message within 24 hours and note what you would like to improve before the next conversation.</p>',
 'Interview Prep', 'published', 1, 986, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(3, 1, 'Finding Remote Work from Rwanda', 'finding-remote-work-from-rwanda',
 'A realistic guide to remote opportunities, reliable tools and building a portfolio that travels.',
 '<h2>Build proof of work</h2><p>Remote employers cannot see you in an office, so your portfolio, GitHub profile and written communication become your evidence. Publish small projects with clear README files.</p><h2>Apply deliberately</h2><p>Focus on roles that explicitly allow your timezone and country. A tailored application with a brief note about how you would contribute beats mass applications.</p><h2>Set up properly</h2><p>Invest in reliable internet, a quiet workspace and a professional email address. Be clear about your availability and preferred working hours.</p>',
 'Remote Work', 'published', 0, 654, DATE_SUB(NOW(), INTERVAL 20 DAY)),
(4, 1, 'Top Digital Skills for the Rwandan Job Market', 'top-digital-skills-rwandan-job-market',
 'The practical skills employers increasingly ask for across industries.',
 '<p>Data literacy, web fundamentals, cloud basics, digital communication and project management are valuable across sectors. Start with one skill, build a small project and share what you learned.</p>',
 'Digital Skills', 'published', 0, 512, DATE_SUB(NOW(), INTERVAL 30 DAY));

INSERT IGNORE INTO interview_questions (id, category, question, sample_answer, tips) VALUES
(1, 'common', 'Tell us about yourself.', 'I am a final-year computer science student with two years of hands-on web development experience. I built a job board for my student association and recently completed an internship where I improved accessibility across the product. I am looking for a role where I can grow while contributing to meaningful products.', 'Keep it under 90 seconds. Connect your background to the role.'),
(2, 'common', 'Why do you want to work here?', 'I have followed your work in digital inclusion, and your recent product launch solves a problem I saw firsthand while volunteering. I would love to contribute my frontend skills and learn from your team.', 'Research the company. Mention something specific.'),
(3, 'behavioral', 'Describe a difficult problem you solved.', 'During my internship, our contact form was losing submissions. I reproduced the issue, checked the server logs, found a validation conflict and fixed it with a small test to prevent regressions. The team saw a 40% increase in successful submissions.', 'Use the STAR method: Situation, Task, Action, Result.'),
(4, 'technical', 'What happens when you type a URL in the browser?', 'The browser resolves the domain through DNS, opens a TCP connection (often TLS), sends an HTTP request, the server responds with HTML, and the browser parses it, requests assets and renders the page.', 'Explain clearly, then go deeper where relevant.'),
(5, 'technical', 'How would you improve a slow website?', 'I would measure first using browser tools, then look at large images, blocking JavaScript, server response time and caching. I would prioritise changes by impact and re-measure after each fix.', 'Always start with measurement, not guessing.'),
(6, 'hr', 'What are your salary expectations?', 'Based on the responsibilities and the market for similar roles, I am looking for a range of 1.2 to 1.6 million RWF per month, but I am open to discussing the full package.', 'Give a researched range and stay flexible.'),
(7, 'hr', 'How do you handle feedback?', 'I see feedback as a way to improve. I listen, ask clarifying questions and turn it into concrete actions. After a code review, I usually write down the changes and apply them to future work.', 'Show that you act on feedback.');

INSERT IGNORE INTO career_tips (title, body, category) VALUES
('Tailor every application', 'Mirror the language of the job description in your CV and cover letter. Recruiters look for evidence that you understand the role.', 'Job Search'),
('Track your applications', 'Keep a simple record of where you applied and when. Follow up politely after 7-10 days if you have not heard back.', 'Job Search'),
('Build in public', 'Share what you learn through short posts or a portfolio. Your visible work often opens doors that applications cannot.', 'Personal Branding');

INSERT IGNORE INTO community_posts (id, user_id, category, title, body, likes_count, comments_count, status, created_at) VALUES
(1, 3, 'experience', 'My first internship application story', 'I applied to 12 places and heard back from two. The difference? I tailored my CV and wrote a short note about why their work mattered to me. Keep going — the right fit takes time.', 18, 4, 'published', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(2, 4, 'advice', 'Portfolio tips for designers in Rwanda', 'Three things clients ask for: clear process, before-and-after visuals, and a short description of the problem you solved. A simple website is enough to get started.', 24, 6, 'published', DATE_SUB(NOW(), INTERVAL 5 DAY)),
(3, 3, 'question', 'How do you prepare for a technical interview as a self-taught developer?', 'I have been learning through projects but feel nervous about live coding. What helped you build confidence?', 9, 3, 'published', DATE_SUB(NOW(), INTERVAL 1 DAY));

INSERT IGNORE INTO community_comments (id, post_id, user_id, parent_id, body) VALUES
(1, 1, 4, NULL, 'Congratulations! Tailoring really does make a difference.'),
(2, 1, 2, NULL, 'Great advice for students. We receive many applications each week.'),
(3, 2, 3, NULL, 'The point about showing your process is so true.');

INSERT IGNORE INTO community_likes (post_id, user_id) VALUES (1, 4), (1, 2), (2, 3), (3, 4);

INSERT IGNORE INTO success_stories (id, user_id, name, headline, story, status) VALUES
(1, 3, 'Jean Paul Niyo', 'From student project to product team', 'After completing an internship through JobLink Rwanda, Jean Paul joined a product team where he now builds accessible web tools for local businesses.', 'published');

INSERT IGNORE INTO advertisements (id, title, description, link, position, active) VALUES
(1, 'Build your professional profile', 'Complete your profile to get matched with relevant opportunities.', '/profile/edit', 'sidebar', 1);

INSERT IGNORE INTO platform_settings (setting_key, setting_value) VALUES
('site_name', 'JobLink Rwanda'),
('tagline', 'Your Skills Deserve an Opportunity.'),
('default_currency', 'RWF'),
('default_country', 'Rwanda'),
('jobs_require_approval', '1'),
('allow_registration', '1'),
('maintenance_mode', '0'),
('contact_email', 'hello@joblinkrwanda.rw'),
('min_password_length', '8');

INSERT IGNORE INTO announcements (id, admin_id, title, body, target_role, active) VALUES
(1, 1, 'Welcome to JobLink Rwanda', 'Complete your profile, add your skills and start discovering opportunities matched to you.', NULL, 1);

INSERT IGNORE INTO notifications (user_id, type, title, body, link) VALUES
(3, 'application', 'Your application is under review', 'Kigali Tech Hub reviewed your application for Frontend Developer (React).', '/applications'),
(3, 'match', 'A new job matches your profile', 'Remote UI/UX Designer was published recently.', '/jobs/6');
