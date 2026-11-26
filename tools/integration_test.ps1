$ErrorActionPreference = 'Stop'

# Local integration harness for a disposable JobLink Rwanda database.
# Override with JOBLINK_BASE_URL, JOBLINK_MYSQL, JOBLINK_DB_USER, JOBLINK_DB_NAME if needed.
$base    = if ($env:JOBLINK_BASE_URL) { $env:JOBLINK_BASE_URL } else { 'http://127.0.0.1:8899' }
$mysql   = if ($env:JOBLINK_MYSQL) { $env:JOBLINK_MYSQL } else { 'C:\xampp\mysql\bin\mysql.exe' }
$dbUser  = if ($env:JOBLINK_DB_USER) { $env:JOBLINK_DB_USER } else { 'root' }
$dbName  = if ($env:JOBLINK_DB_NAME) { $env:JOBLINK_DB_NAME } else { 'job_finding' }
$tmpDir  = Join-Path $env:TEMP 'joblink-integration'
New-Item -ItemType Directory -Force -Path $tmpDir | Out-Null
$bodyOut = Join-Path $tmpDir 'post-response.json'
$resultOut = Join-Path $tmpDir 'post_results.txt'
$cvJson = Join-Path $tmpDir 'cv-test.json'

$seeker     = Join-Path $tmpDir 'cookies-seeker.txt'
$employer   = Join-Path $tmpDir 'cookies-employer.txt'
$freelancer = Join-Path $tmpDir 'cookies-freelancer.txt'
$admin      = Join-Path $tmpDir 'cookies-admin.txt'
$guest      = Join-Path $tmpDir 'cookies-guest.txt'
$register   = Join-Path $tmpDir 'cookies-register.txt'

$results = @()

function DbScalar {
    param([string]$Sql)
    $out = & $mysql -u $dbUser -D $dbName -N -e $Sql 2>$null
    if ($LASTEXITCODE -ne 0 -or -not $out) { return $null }
    return ($out | Select-Object -First 1).Trim()
}

function Add-Result {
    param([string]$Name, [string]$Status, [string]$Detail)
    $script:results += "$Status`t$Name`t$Detail"
}

function Get-Token {
    param([string]$Jar, [string]$Path = '/dashboard')
    $html = & curl.exe -s -b $Jar ($base + $Path)
    $match = [regex]::Match($html, 'name="_csrf" value="([^"]+)"')
    if (-not $match.Success) { throw "CSRF token not found on $Path for $Jar" }
    return $match.Groups[1].Value
}

function Login-Role {
    param([string]$Role, [string]$Jar, [string]$Email, [string]$Password, [string]$HomePath)
    $loginPage = & curl.exe -s -c $Jar ($base + '/login')
    $match = [regex]::Match($loginPage, 'name="_csrf" value="([^"]+)"')
    if (-not $match.Success) { throw "CSRF token missing on login page for $Role" }
    $code = (& curl.exe -s -o NUL -w '%{http_code}' -b $Jar -c $Jar -X POST ($base + '/login') `
        --data-urlencode ('_csrf=' + $match.Groups[1].Value) `
        --data-urlencode ('email=' + $Email) `
        --data-urlencode ('password=' + $Password) | Out-String).Trim()
    if ($code -ne '302') { throw "Login failed for $Role (HTTP $code)" }
    $homeCode = (& curl.exe -s -o NUL -w '%{http_code}' -b $Jar ($base + $HomePath) | Out-String).Trim()
    if ($homeCode -ne '200') { throw "Role home failed for $Role (HTTP $homeCode)" }
}

function Invoke-Post {
    param(
        [string]$Name,
        [string]$Jar,
        [string]$Path,
        [hashtable]$Fields = @{},
        [string]$Expected = '302',
        [string[]]$Headers = @(),
        [switch]$Json,
        [string]$Token
    )

    if (-not $Token) { $Token = Get-Token $Jar '/dashboard' }

    $outTarget = if ($Json) { $bodyOut } else { 'NUL' }
    $argsList = @('-s', '-o', $outTarget, '-w', '%{http_code}', '-b', $Jar, '-c', $Jar, '-X', 'POST', ($base + $Path))
    foreach ($header in $Headers) { $argsList += @('-H', $header) }
    $argsList += @('--data-urlencode', ('_csrf=' + $Token))

    foreach ($key in $Fields.Keys) {
        $value = $Fields[$key]
        if ($value -is [array]) {
            foreach ($item in $value) { $argsList += @('--data-urlencode', ("$key=$item")) }
        } elseif ($value -is [string] -and $value.StartsWith('@')) {
            # curl's name@filename form avoids native argument quoting of JSON payloads.
            $argsList += @('--data-urlencode', ("$key$value"))
        } else {
            $argsList += @('--data-urlencode', ("$key=$value"))
        }
    }

    $code = ((& curl.exe @argsList) | Out-String).Trim()
    $allowed = $Expected.Split(',')
    $ok = $allowed -contains $code
    $detail = "code=$code"

    if ($Json) {
        $body = (Get-Content $bodyOut -Raw).Trim()
        $detail += " body=$body"
        try {
            $parsed = $body | ConvertFrom-Json
            if (-not $parsed.ok) { $ok = $false }
        } catch { $ok = $false }
    }

    Add-Result $Name $(if ($ok) { 'PASS' } else { 'FAIL' }) $detail
}

function Assert-Db {
    param([string]$Name, [string]$Sql, [int]$Expected = 1)
    $value = DbScalar $Sql
    $actual = if ($null -eq $value) { 0 } else { [int]$value }
    if ($actual -eq $Expected) {
        Add-Result $Name 'PASS' "db=$actual expected=$Expected"
    } else {
        Add-Result $Name 'FAIL' "db=$actual expected=$Expected"
    }
}

Write-Output 'Preparing sessions and CSRF tokens...'
Login-Role 'seeker' $seeker 'seeker@joblinkrwanda.rw' 'Seeker@123' '/dashboard'
Login-Role 'employer' $employer 'employer@joblinkrwanda.rw' 'Employer@123' '/employer/dashboard'
Login-Role 'freelancer' $freelancer 'freelancer@joblinkrwanda.rw' 'Freelancer@123' '/freelancer/dashboard'
Login-Role 'admin' $admin 'admin@joblinkrwanda.rw' 'Admin@12345' '/admin/dashboard'

$tSeeker     = Get-Token $seeker '/dashboard'
$tEmployer   = Get-Token $employer '/employer/dashboard'
$tFreelancer = Get-Token $freelancer '/freelancer/dashboard'
$tAdmin      = Get-Token $admin '/admin/dashboard'
& curl.exe -s -c $guest ($base + '/contact') | Out-Null
$tGuest      = Get-Token $guest '/contact'
& curl.exe -s -c $register ($base + '/register') | Out-Null
$tRegister   = Get-Token $register '/register'

Write-Output 'Running POST integration tests...'
& $mysql -u $dbUser -D $dbName -e "DELETE FROM password_resets WHERE email='integration-test@joblinkrwanda.rw';" 2>$null | Out-Null

# Public forms
Invoke-Post 'public/contact' $guest '/contact' @{
    name='Integration Tester'; email='integration@example.com'; subject='Integration test'; message='This is an automated integration test message.'
} '302' @() $tGuest
Invoke-Post 'public/newsletter' $guest '/newsletter' @{ email='integration@example.com' } '302' @() $tGuest
Assert-Db 'public/contact DB write' "SELECT COUNT(*) FROM contact_messages WHERE email='integration@example.com'"
Assert-Db 'public/newsletter DB write' "SELECT COUNT(*) FROM newsletter_subscribers WHERE email='integration@example.com'"

# Authentication lifecycle on a disposable account
Invoke-Post 'auth/register' $register '/register' @{
    role='seeker'; name='Integration Test User'; email='integration-test@joblinkrwanda.rw'
    password='Test@12345'; password_confirmation='Test@12345'; location='Kigali'; terms='1'
} '302' @() $tRegister
$testUserId = [int](DbScalar "SELECT id FROM users WHERE email='integration-test@joblinkrwanda.rw' LIMIT 1")
Assert-Db 'auth/register DB write' "SELECT COUNT(*) FROM users WHERE email='integration-test@joblinkrwanda.rw'"
Invoke-Post 'auth/logout' $register '/logout' @{} '302' @() $tRegister
Invoke-Post 'auth/forgot-password' $guest '/forgot-password' @{ email='integration-test@joblinkrwanda.rw' } '302' @() $tGuest
$resetToken = DbScalar "SELECT token FROM password_resets WHERE email='integration-test@joblinkrwanda.rw' AND used_at IS NULL ORDER BY id DESC LIMIT 1"
Invoke-Post 'auth/reset-password' $guest '/reset-password' @{
    token=$resetToken; password='Reset@12345'; password_confirmation='Reset@12345'
} '302' @() $tGuest
Invoke-Post 'auth/select-role' $guest '/select-role' @{ role='freelancer' } '302' @() $tGuest
Invoke-Post 'auth/deactivate' $guest '/account/deactivate' @{} '302' @() $tGuest
Assert-Db 'auth/reset-password DB write' "SELECT COUNT(*) FROM password_resets WHERE token='$resetToken' AND used_at IS NOT NULL"

# Job actions and JSON APIs (seeker)
Invoke-Post 'jobs/save (on)' $seeker '/jobs/4/save' @{} '200' @() $tSeeker -Json
Invoke-Post 'jobs/save (off)' $seeker '/jobs/4/save' @{} '200' @() $tSeeker -Json
Invoke-Post 'jobs/apply' $seeker '/jobs/9/apply' @{ cover_letter='I am applying for this role as part of an end-to-end integration test of the application workflow.' } '302' @() $tSeeker
$newAppId = [int](DbScalar "SELECT id FROM applications WHERE user_id=3 AND job_id=9 ORDER BY id DESC LIMIT 1")
Assert-Db 'jobs/apply DB write' "SELECT COUNT(*) FROM applications WHERE user_id=3 AND job_id=9"
Invoke-Post 'jobs/report' $seeker '/jobs/4/report' @{ reason='scam'; details='Integration test report' } '302' @() $tSeeker
Assert-Db 'jobs/report DB write' "SELECT COUNT(*) FROM reports WHERE reporter_id=3 AND type='job' AND details='Integration test report'"
Invoke-Post 'api/saved-searches' $seeker '/api/saved-searches' @{
    name='Integration test search'; query_params='q=php&location=Kigali'
} '200' @('Accept: application/json') $tSeeker -Json
Assert-Db 'api/saved-searches DB write' "SELECT COUNT(*) FROM saved_searches WHERE user_id=3 AND name='Integration test search'"
$notifId = [int](DbScalar "SELECT id FROM notifications WHERE user_id=3 ORDER BY id DESC LIMIT 1")
Invoke-Post 'api/notifications/read' $seeker '/api/notifications/read' @{ id=$notifId } '200' @() $tSeeker -Json
Assert-Db 'api/notifications/read DB write' "SELECT COUNT(*) FROM notifications WHERE id=$notifId AND is_read=1"
Invoke-Post 'companies/follow' $seeker '/companies/1/follow' @{} '302' @() $tSeeker
Assert-Db 'companies/follow DB write' "SELECT COUNT(*) FROM company_followers WHERE user_id=3 AND company_id=1"

# Seeker profile writes
Invoke-Post 'seeker/profile/edit' $seeker '/profile/edit' @{
    name='Jean Paul Niyo'; phone='+250788000003'; headline='Frontend Developer & UI Enthusiast'
    summary='Final-year computer science student passionate about building accessible web products. I enjoy turning complex problems into simple, useful interfaces.'
    location='Kigali'; preferred_location='Kigali'; country_id='1'; gender=''; date_of_birth=''
    expected_salary_min='800000'; expected_salary_max='1500000'; salary_period='monthly'
    experience_years='2'; education_level='Bachelor'; availability=''; linkedin_url='https://linkedin.com/in/example'
    github_url='https://github.com/example'; website_url=''; remote_only='0'; open_to_work='1'
    'employment_types[]'=@('full_time','internship','remote')
} '302' @() $tSeeker
Invoke-Post 'seeker/job-alerts/create' $seeker '/job-alerts' @{
    name='Integration test alert'; keywords='PHP, React'; location='Kigali'; category_id='1'
    employment_type='full_time'; frequency='daily'; active='1'
} '302' @() $tSeeker
$alertId = [int](DbScalar "SELECT id FROM job_alerts WHERE user_id=3 AND name='Integration test alert' ORDER BY id DESC LIMIT 1")
Assert-Db 'seeker/job-alerts DB write' "SELECT COUNT(*) FROM job_alerts WHERE id=$alertId"
Invoke-Post 'seeker/my-skills/add' $seeker '/my-skills' @{ skill_id='5'; level='advanced'; years='3' } '302' @() $tSeeker
$skillId = [int](DbScalar "SELECT id FROM user_skills WHERE user_id=3 AND skill_id=5 ORDER BY id DESC LIMIT 1")
Assert-Db 'seeker/my-skills DB write' "SELECT COUNT(*) FROM user_skills WHERE id=$skillId"
Invoke-Post 'seeker/my-skills/endorse' $employer "/my-skills/$skillId/endorse" @{} '302' @() $tEmployer
Assert-Db 'seeker/my-skills/endorse DB write' "SELECT COUNT(*) FROM skill_endorsements WHERE user_id=3 AND endorsed_by=2 AND skill_id=5"
Invoke-Post 'seeker/my-portfolio/add' $seeker '/my-portfolio' @{
    title='Integration test project'; description='Portfolio entry created by the integration test.'; url='https://example.com/project'
    repository_url='https://github.com/example/project'; tags='PHP, MySQL'; completed_at='2026-11-20'
} '302' @() $tSeeker
$portfolioId = [int](DbScalar "SELECT id FROM portfolios WHERE user_id=3 AND title='Integration test project' ORDER BY id DESC LIMIT 1")
Assert-Db 'seeker/my-portfolio DB write' "SELECT COUNT(*) FROM portfolios WHERE id=$portfolioId"
Invoke-Post 'seeker/profile-entry/education' $seeker '/profile/entries' @{
    type='education'; school='Test University'; degree='BSc'; field_of_study='Computer Science'
    start_year='2020'; end_year='2024'; grade='A'
} '302' @() $tSeeker
$eduId = [int](DbScalar "SELECT id FROM education WHERE user_id=3 AND school='Test University' ORDER BY id DESC LIMIT 1")
Assert-Db 'seeker/profile-entry/education DB write' "SELECT COUNT(*) FROM education WHERE id=$eduId"
Invoke-Post 'seeker/profile-entry/experience' $seeker '/profile/entries' @{
    type='experience'; company='Test Company'; title='Test Developer'; start_date='2024-01-01'
    end_date='2026-01-01'; description='Integration test experience entry.'
} '302' @() $tSeeker
$expId = [int](DbScalar "SELECT id FROM experience WHERE user_id=3 AND company='Test Company' ORDER BY id DESC LIMIT 1")
Assert-Db 'seeker/profile-entry/experience DB write' "SELECT COUNT(*) FROM experience WHERE id=$expId"
Invoke-Post 'seeker/profile-entry/certification' $seeker '/profile/entries' @{
    type='certification'; name='Test Certification'; issuer='Test Issuer'; issued_year='2025'
    credential_id='TEST-123'; credential_url='https://example.com/certificate'
} '302' @() $tSeeker
$certId = [int](DbScalar "SELECT id FROM certifications WHERE user_id=3 AND name='Test Certification' ORDER BY id DESC LIMIT 1")
Assert-Db 'seeker/profile-entry/certification DB write' "SELECT COUNT(*) FROM certifications WHERE id=$certId"
Set-Content -Path $cvJson -Value '{"full_name":"Jean Paul Niyo","headline":"Frontend Developer","summary":"Integration test CV.","skills":[{"name":"PHP","level":"Advanced"}]}' -NoNewline -Encoding Ascii
Invoke-Post 'seeker/cv/save' $seeker '/my-cv/save' @{
    title='Integration Test CV'; template='modern'; data=('@' + $cvJson)
} '302' @() $tSeeker
Assert-Db 'seeker/cv/save DB write' "SELECT COUNT(*) FROM cv_documents WHERE user_id=3 AND title='Integration Test CV'"
Invoke-Post 'seeker/settings' $seeker '/settings' @{
    name='Jean Paul Niyo'; email='seeker@joblinkrwanda.rw'; phone='+250788000003'
    email_enabled='1'; match_enabled='1'; deadline_enabled='1'; message_enabled='1'
    application_enabled='1'; in_app_enabled='1'
} '302' @() $tSeeker

# Messages
Invoke-Post 'messages/start' $seeker '/messages/start' @{
    recipient_id='2'; subject='Integration test conversation'; body='Hello, this is an integration test message.'
} '302' @() $tSeeker
$threadId = [int](DbScalar "SELECT id FROM message_threads WHERE subject='Integration test conversation' ORDER BY id DESC LIMIT 1")
Assert-Db 'messages/start DB write' "SELECT COUNT(*) FROM thread_participants WHERE thread_id=$threadId" 2
Invoke-Post 'messages/send (seeker)' $seeker "/messages/$threadId/send" @{ body='Follow-up from the seeker in the integration test.' } '302' @() $tSeeker
Invoke-Post 'messages/send (employer)' $employer "/messages/$threadId/send" @{ body='Reply from the employer in the integration test.' } '302' @() $tEmployer
Assert-Db 'messages/send DB write' "SELECT COUNT(*) FROM messages WHERE thread_id=$threadId" 3

# Community
Invoke-Post 'community/create' $seeker '/community' @{
    title='Integration test discussion'; category='experience'; body='This discussion was created by an automated integration test to verify posting, commenting, liking and reporting.'
} '302' @() $tSeeker
$postId = [int](DbScalar "SELECT id FROM community_posts WHERE title='Integration test discussion' ORDER BY id DESC LIMIT 1")
Assert-Db 'community/create DB write' "SELECT COUNT(*) FROM community_posts WHERE id=$postId"
Invoke-Post 'community/comment' $employer "/community/$postId/comment" @{ body='Integration test comment on the discussion.' } '302' @() $tEmployer
Assert-Db 'community/comment DB write' "SELECT COUNT(*) FROM community_comments WHERE post_id=$postId AND body='Integration test comment on the discussion.'"
Invoke-Post 'community/like' $seeker "/community/$postId/like" @{} '302' @() $tSeeker
Assert-Db 'community/like DB write' "SELECT COUNT(*) FROM community_likes WHERE post_id=$postId AND user_id=3"
Invoke-Post 'community/report' $employer "/community/$postId/report" @{ reason='spam'; details='Integration test report' } '302' @() $tEmployer
$reportId = [int](DbScalar "SELECT id FROM reports WHERE reporter_id=2 AND type='community_post' AND details='Integration test report' ORDER BY id DESC LIMIT 1")
Assert-Db 'community/report DB write' "SELECT COUNT(*) FROM reports WHERE id=$reportId"

# Employer
Invoke-Post 'employer/company/update' $employer '/employer/company' @{
    name='Kigali Tech Hub'; industry_id='1'; website='https://kigalitechhub.example.com'
    email='careers@kigalitechhub.example.com'; phone='+250788000002'; country_id='1'
    location='Kigali'; address='Kigali, Rwanda'; company_size='11-50'; founded_year='2018'
    description='Kigali Tech Hub builds digital products for African businesses and helps young developers grow their careers through mentorship and real projects.'
} '302' @() $tEmployer
Invoke-Post 'employer/jobs/create' $employer '/employer/jobs' @{
    title='Integration test job'; description='This opportunity was created by an automated integration test to verify the employer job posting workflow.'
    responsibilities='Test the workflow.'; requirements='PHP and attention to detail.'
    location='Kigali'; deadline='2026-12-31'; employment_type='full_time'; work_mode='hybrid'
    experience_level='entry'; education_level='Bachelor'; salary_min='500000'; salary_max='900000'
    salary_currency='RWF'; salary_period='monthly'; category_id='1'; industry_id='1'; country_id='1'
    status='draft'; 'skills[]'=@('1','5'); 'skills_required[]'=@('1')
} '302' @() $tEmployer
$newJobId = [int](DbScalar "SELECT id FROM jobs WHERE title='Integration test job' ORDER BY id DESC LIMIT 1")
Assert-Db 'employer/jobs/create DB write' "SELECT COUNT(*) FROM jobs WHERE id=$newJobId"
Assert-Db 'employer/jobs/create skills DB write' "SELECT COUNT(*) FROM job_skills WHERE job_id=$newJobId" 2
Invoke-Post 'employer/jobs/edit' $employer "/employer/jobs/$newJobId/edit" @{
    title='Integration test job'; description='This opportunity was created by an automated integration test to verify the employer job posting workflow.'
    requirements='PHP, MySQL and attention to detail.'; location='Kigali'; deadline='2026-12-31'
    employment_type='full_time'; work_mode='hybrid'; experience_level='entry'; education_level='Bachelor'
    salary_min='500000'; salary_max='900000'; salary_currency='RWF'; salary_period='monthly'
    category_id='1'; industry_id='1'; country_id='1'; status='draft'
    'skills[]'=@('1','5'); 'skills_required[]'=@('1')
} '302' @() $tEmployer
Invoke-Post 'employer/jobs/status' $employer "/employer/jobs/$newJobId/status" @{ status='pending_review' } '302' @() $tEmployer
Assert-Db 'employer/jobs/status DB write' "SELECT COUNT(*) FROM jobs WHERE id=$newJobId AND status='pending_review'"
Invoke-Post 'employer/applications/status' $employer "/employer/applications/$newAppId/status" @{
    status='shortlisted'; note='Integration test status update.'
} '302' @() $tEmployer
Assert-Db 'employer/applications/status DB write' "SELECT COUNT(*) FROM applications WHERE id=$newAppId AND status='shortlisted'"
Invoke-Post 'employer/interviews/schedule' $employer '/employer/interviews' @{
    application_id=$newAppId; scheduled_at='2026-12-05T10:00'; duration_minutes='45'
    location='Kigali head office'; notes='Integration test interview'
} '302' @() $tEmployer
$interviewId = [int](DbScalar "SELECT id FROM interviews WHERE notes='Integration test interview' ORDER BY id DESC LIMIT 1")
Assert-Db 'employer/interviews/schedule DB write' "SELECT COUNT(*) FROM interviews WHERE id=$interviewId"
Invoke-Post 'employer/interviews/status' $employer "/employer/interviews/$interviewId/status" @{ status='completed' } '302' @() $tEmployer
Assert-Db 'employer/interviews/status DB write' "SELECT COUNT(*) FROM interviews WHERE id=$interviewId AND status='completed'"
# Moderation must happen while the job is still visible to admins.
Invoke-Post 'admin/jobs/moderate' $admin "/admin/jobs/$newJobId/moderate" @{ status='published' } '302' @() $tAdmin
Assert-Db 'admin/jobs/moderate DB write' "SELECT COUNT(*) FROM jobs WHERE id=$newJobId AND status='published'"
Invoke-Post 'employer/jobs/delete' $employer "/employer/jobs/$newJobId/delete" @{} '302' @() $tEmployer
Assert-Db 'employer/jobs/delete DB write' "SELECT COUNT(*) FROM jobs WHERE id=$newJobId AND deleted_at IS NOT NULL"

# Freelancer
Invoke-Post 'freelancer/profile/update' $freelancer '/freelancer/profile' @{
    name='Grace Inkamena'; phone='+250788000004'; title='Brand Designer & Web Developer'
    bio='I help startups build memorable brands and fast, beautiful websites. 6 years of experience across print and digital.'
    hourly_rate='15000'; project_rate='1200000'; currency='RWF'; availability='available'; response_time='Within 24 hours'
} '302' @() $tFreelancer
Invoke-Post 'freelancer/services/add' $freelancer '/freelancer/services' @{
    title='Integration test service'; description='Service created by the integration test.'; price='250000'; price_type='project'
} '302' @() $tFreelancer
$serviceId = [int](DbScalar "SELECT id FROM freelancer_services WHERE title='Integration test service' ORDER BY id DESC LIMIT 1")
Assert-Db 'freelancer/services/add DB write' "SELECT COUNT(*) FROM freelancer_services WHERE id=$serviceId"
Invoke-Post 'freelancer/services/delete' $freelancer "/freelancer/services/$serviceId/delete" @{} '302' @() $tFreelancer
Assert-Db 'freelancer/services/delete DB write' "SELECT COUNT(*) FROM freelancer_services WHERE id=$serviceId" 0
$projectSql = @"
INSERT INTO freelance_projects (client_id, title, description, budget, currency, deadline, category_id, skills_required, status)
VALUES (2, 'Integration test project', 'Project created by the integration test to verify the freelancer proposal workflow.', 450000, 'RWF', '2026-12-31', 1, 'PHP,React', 'open');
"@
& $mysql -u $dbUser -D $dbName -e $projectSql 2>$null | Out-Null
$testProjectId = [int](DbScalar "SELECT id FROM freelance_projects WHERE title='Integration test project' ORDER BY id DESC LIMIT 1")
Assert-Db 'freelancer/projects/setup' "SELECT COUNT(*) FROM freelance_projects WHERE id=$testProjectId"
Invoke-Post 'freelancer/projects/apply' $freelancer "/freelancer/projects/$testProjectId/apply" @{
    cover_letter='I am submitting this proposal as part of an end-to-end integration test of the freelancer workflow.'
    bid_amount='450000'
} '302' @() $tFreelancer
Assert-Db 'freelancer/projects/apply DB write' "SELECT COUNT(*) FROM freelance_proposals WHERE project_id=$testProjectId AND bid_amount=450000"

# Admin
Invoke-Post 'admin/users/status' $admin "/admin/users/$testUserId/status" @{ status='suspended' } '302' @() $tAdmin
Assert-Db 'admin/users/status DB write' "SELECT COUNT(*) FROM users WHERE id=$testUserId AND status='suspended'"
Invoke-Post 'admin/employers/verify' $admin '/admin/employers/1/verify' @{ status='verified' } '302' @() $tAdmin
Invoke-Post 'admin/external/create' $admin '/admin/external-opportunities' @{
    title='Integration test opportunity'; organization='Integration Test Org'
    description='External opportunity created by the integration test.'
    application_url='https://example.com/apply'; source_url='https://example.com'
    deadline='2026-12-31'; status='draft'; verification_status='unverified'; salary='Competitive'
    currency='RWF'; source_name='Test source'; location='Kigali'
} '302' @() $tAdmin
$externalId = [int](DbScalar "SELECT id FROM external_opportunities WHERE title='Integration test opportunity' ORDER BY id DESC LIMIT 1")
Assert-Db 'admin/external/create DB write' "SELECT COUNT(*) FROM external_opportunities WHERE id=$externalId"
Invoke-Post 'admin/external/edit' $admin "/admin/external-opportunities/$externalId/edit" @{
    title='Integration test opportunity'; organization='Integration Test Org'
    description='External opportunity updated by the integration test.'
    application_url='https://example.com/apply'; source_url='https://example.com'
    deadline='2026-12-31'; status='draft'; verification_status='unverified'; salary='Updated salary'
    currency='RWF'; source_name='Test source'; location='Kigali'
} '302' @() $tAdmin
Invoke-Post 'admin/external/status' $admin "/admin/external-opportunities/$externalId/status" @{ status='published' } '302' @() $tAdmin
Invoke-Post 'admin/external/verify' $admin "/admin/external-opportunities/$externalId/verify" @{ status='verified' } '302' @() $tAdmin
Assert-Db 'admin/external/verify DB write' "SELECT COUNT(*) FROM external_opportunities WHERE id=$externalId AND verification_status='verified'"
Invoke-Post 'admin/external/delete' $admin "/admin/external-opportunities/$externalId/delete" @{} '302' @() $tAdmin
Assert-Db 'admin/external/delete DB write' "SELECT COUNT(*) FROM external_opportunities WHERE id=$externalId" 0
Invoke-Post 'admin/categories/create' $admin '/admin/categories' @{
    type='job'; name='Integration Test Category'; slug='integration-test-category'; parent_id='1'
    icon='code'; description='Category created by the integration test.'
} '302' @() $tAdmin
$categoryId = [int](DbScalar "SELECT id FROM job_categories WHERE slug='integration-test-category' ORDER BY id DESC LIMIT 1")
Assert-Db 'admin/categories/create DB write' "SELECT COUNT(*) FROM job_categories WHERE id=$categoryId"
Invoke-Post 'admin/categories/delete' $admin "/admin/categories/$categoryId/delete" @{ type='job' } '302' @() $tAdmin
Assert-Db 'admin/categories/delete DB write' "SELECT COUNT(*) FROM job_categories WHERE id=$categoryId" 0
Invoke-Post 'admin/reports/resolve' $admin "/admin/reports/$reportId/resolve" @{
    status='resolved'; resolution='Integration test resolution.'
} '302' @() $tAdmin
Assert-Db 'admin/reports/resolve DB write' "SELECT COUNT(*) FROM reports WHERE id=$reportId AND status='resolved'"
Invoke-Post 'admin/content/article' $admin '/admin/content/articles' @{
    title='Integration test article'; slug='integration-test-article'; category='Testing'
    status='published'; excerpt='Created by an integration test.'; body='Full body of the integration test article.'; featured='1'
} '302' @() $tAdmin
$articleId = [int](DbScalar "SELECT id FROM articles WHERE slug='integration-test-article' ORDER BY id DESC LIMIT 1")
Assert-Db 'admin/content/article DB write' "SELECT COUNT(*) FROM articles WHERE id=$articleId"
Invoke-Post 'admin/content/article/delete' $admin "/admin/content/articles/$articleId/delete" @{} '302' @() $tAdmin
Assert-Db 'admin/content/article/delete DB write' "SELECT COUNT(*) FROM articles WHERE id=$articleId" 0
Invoke-Post 'admin/content/question' $admin '/admin/content/questions' @{
    category='technical'; question='Integration test question?'; sample_answer='Integration test sample answer.'; tips='Integration test tips.'; active='1'
} '302' @() $tAdmin
Assert-Db 'admin/content/question DB write' "SELECT COUNT(*) FROM interview_questions WHERE question='Integration test question?'"
Invoke-Post 'admin/ads/create' $admin '/admin/ads' @{
    title='Integration test ad'; description='Ad created by the integration test.'
    link='https://example.com'; position='sidebar'; active='1'; starts_at=''; ends_at=''
} '302' @() $tAdmin
$adId = [int](DbScalar "SELECT id FROM advertisements WHERE title='Integration test ad' ORDER BY id DESC LIMIT 1")
Assert-Db 'admin/ads/create DB write' "SELECT COUNT(*) FROM advertisements WHERE id=$adId"
Invoke-Post 'admin/ads/toggle' $admin "/admin/ads/$adId/toggle" @{} '302' @() $tAdmin
Assert-Db 'admin/ads/toggle DB write' "SELECT COUNT(*) FROM advertisements WHERE id=$adId AND active=0"
Invoke-Post 'admin/settings' $admin '/admin/settings' @{
    site_name='JobLink Rwanda'; tagline='Integration test tagline'; contact_email='hello@joblinkrwanda.rw'
    min_password_length='8'; radar_note='Verify before you apply.'; jobs_require_approval='1'
} '302' @() $tAdmin
Assert-Db 'admin/settings DB write' "SELECT COUNT(*) FROM platform_settings WHERE setting_key='tagline' AND setting_value='Integration test tagline'"
Invoke-Post 'admin/announcements' $admin '/admin/announcements' @{
    title='Integration test announcement'; body='This announcement was posted by the integration test.'
    target_role='seeker'; active='1'
} '302' @() $tAdmin
$announcementId = [int](DbScalar "SELECT id FROM announcements WHERE title='Integration test announcement' ORDER BY id DESC LIMIT 1")
Assert-Db 'admin/announcements DB write' "SELECT COUNT(*) FROM announcements WHERE id=$announcementId"

# Deletes that double as route tests
Invoke-Post 'seeker/job-alerts/delete' $seeker "/job-alerts/$alertId/delete" @{} '302' @() $tSeeker
Invoke-Post 'seeker/my-skills/delete' $seeker "/my-skills/$skillId/delete" @{} '302' @() $tSeeker
Invoke-Post 'seeker/my-portfolio/delete' $seeker "/my-portfolio/$portfolioId/delete" @{} '302' @() $tSeeker
Invoke-Post 'seeker/profile-entry/education/delete' $seeker "/profile/entries/$eduId/delete" @{ type='education' } '302' @() $tSeeker
Invoke-Post 'seeker/profile-entry/experience/delete' $seeker "/profile/entries/$expId/delete" @{ type='experience' } '302' @() $tSeeker
Invoke-Post 'seeker/profile-entry/certification/delete' $seeker "/profile/entries/$certId/delete" @{ type='certification' } '302' @() $tSeeker
Invoke-Post 'community/like (remove)' $seeker "/community/$postId/like" @{} '302' @() $tSeeker

Write-Output 'Cleaning up integration test data...'
$cleanup = @"
DELETE FROM users WHERE email='integration-test@joblinkrwanda.rw';
DELETE FROM password_resets WHERE email='integration-test@joblinkrwanda.rw';
DELETE FROM contact_messages WHERE email='integration@example.com';
DELETE FROM newsletter_subscribers WHERE email='integration@example.com';
DELETE FROM saved_jobs WHERE user_id=3 AND job_id=4;
DELETE FROM saved_searches WHERE user_id=3 AND name='Integration test search';
DELETE FROM applications WHERE user_id=3 AND job_id=9;
DELETE FROM job_alerts WHERE user_id=3 AND name='Integration test alert';
DELETE FROM user_skills WHERE user_id=3 AND skill_id=5;
DELETE FROM skill_endorsements WHERE user_id=3 AND endorsed_by=2 AND skill_id=5;
DELETE FROM portfolios WHERE user_id=3 AND title='Integration test project';
DELETE FROM education WHERE user_id=3 AND school='Test University';
DELETE FROM experience WHERE user_id=3 AND company='Test Company';
DELETE FROM certifications WHERE user_id=3 AND name='Test Certification';
UPDATE cv_documents SET title='My Professional CV', template='modern', data=JSON_OBJECT('full_name', 'Jean Paul Niyo', 'email', 'seeker@joblinkrwanda.rw', 'phone', '+250788000003', 'headline', 'Frontend Developer & UI Enthusiast', 'summary', 'Final-year computer science student passionate about building accessible web products.') WHERE user_id=3;
DELETE FROM company_followers WHERE user_id=3 AND company_id=1;
DELETE FROM message_threads WHERE subject='Integration test conversation';
DELETE FROM reports WHERE details='Integration test report';
DELETE FROM community_posts WHERE title='Integration test discussion';
DELETE FROM interviews WHERE notes='Integration test interview';
DELETE FROM jobs WHERE title='Integration test job';
DELETE FROM freelancer_services WHERE title='Integration test service';
DELETE FROM freelance_proposals WHERE bid_amount=450000;
DELETE FROM freelance_projects WHERE title='Integration test project';
DELETE FROM external_opportunities WHERE title='Integration test opportunity';
DELETE FROM job_categories WHERE slug='integration-test-category';
DELETE FROM articles WHERE slug='integration-test-article';
DELETE FROM interview_questions WHERE question='Integration test question?';
DELETE FROM advertisements WHERE title='Integration test ad';
DELETE FROM announcements WHERE title='Integration test announcement';
DELETE FROM moderation_logs WHERE notes='Integration test resolution.';
UPDATE platform_settings SET setting_value='Your Skills Deserve an Opportunity.', updated_by=1 WHERE setting_key='tagline';
UPDATE applications SET status='under_review' WHERE id=1;
UPDATE applications SET status='shortlisted' WHERE id=2;
UPDATE applications SET status='submitted' WHERE id=3;
DELETE FROM application_history WHERE id > 16;
DELETE FROM notifications WHERE id > 4;
DELETE FROM audit_logs WHERE id > 16;
"@
& $mysql -u $dbUser -D $dbName -e $cleanup 2>$null | Out-Null

$script:results | Set-Content -Path $resultOut -Encoding UTF8
$pass = @($results | Where-Object { $_ -like 'PASS*' }).Count
$fail = @($results | Where-Object { $_ -like 'FAIL*' }).Count
Write-Output "POST integration tests complete: $pass passed, $fail failed."
Write-Output "Results saved to $resultOut"
if ($fail -gt 0) {
    Write-Output 'Failures:'
    $results | Where-Object { $_ -like 'FAIL*' } | ForEach-Object { Write-Output $_ }
    exit 1
}
