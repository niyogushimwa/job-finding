<?php
declare(strict_types=1);

use App\Core\Auth;
use App\Core\Csrf;
use App\Core\Router;

/** All application routes. Middleware runs in the declared order. */
return static function (Router $router): void {
    $router->middleware('auth', static function () {
        if (!Auth::check()) {
            flash('error', 'Please log in to access that page.');
            redirect('login');
        }
        return true;
    });

    $router->middleware('guest', static function () {
        if (Auth::check()) {
            redirect(Auth::role() === 'admin' ? 'admin/dashboard' : (Auth::role() === 'employer' ? 'employer/dashboard' : 'dashboard'));
        }
        return true;
    });

    $router->middleware('role', static function ($request, $roles) {
        if (!Auth::hasRole(...explode(',', (string) $roles))) {
            abort(403, 'Your account type cannot access this area.');
        }
        return true;
    });

    $router->middleware('csrf', static function ($request) {
        if (!$request->isPost()) {
            return true;
        }
        Csrf::check((string) $request->post('_csrf', ''));
        return true;
    });

    $all = ['auth', 'csrf'];

    // ---------------- Public ----------------
    $router->get('/', 'HomeController@index');
    $router->get('/jobs', 'JobController@index');
    $router->get('/jobs/{id}/{slug?}', 'JobController@show');
    $router->get('/companies', 'CompanyController@index');
    $router->get('/companies/{id}/{slug?}', 'CompanyController@show');
    $router->get('/opportunity-radar', 'OpportunityController@radar');
    $router->get('/opportunity-radar/{id}', 'OpportunityController@showExternal');
    $router->get('/internships', 'OpportunityController@internships');
    $router->get('/remote-jobs', 'OpportunityController@remote');
    $router->get('/freelance', 'OpportunityController@freelance');
    $router->get('/scholarships', 'OpportunityController@scholarships');
    $router->get('/career-centre', 'CareerController@index');
    $router->get('/career-centre/{slug}', 'CareerController@article');
    $router->get('/interview-prep', 'CareerController@interviewPrep');
    $router->get('/community', 'CommunityController@index');
    $router->get('/community/{id}', 'CommunityController@show');
    $router->get('/about', 'StaticController@about');
    $router->get('/contact', 'StaticController@contact');
    $router->post('/contact', 'StaticController@sendContact', ['csrf']);
    $router->get('/help', 'StaticController@help');
    $router->get('/privacy', 'StaticController@privacy');
    $router->get('/terms', 'StaticController@terms');
    $router->post('/newsletter', 'StaticController@newsletter', ['csrf']);
    $router->get('/u/{id}/{slug?}', 'SeekerController@publicProfile');

    // ---------------- Authentication ----------------
    $router->get('/register', 'AuthController@register', ['guest']);
    $router->post('/register', 'AuthController@store', ['guest', 'csrf']);
    $router->get('/login', 'AuthController@login', ['guest']);
    $router->post('/login', 'AuthController@authenticate', ['guest', 'csrf']);
    $router->post('/logout', 'AuthController@logout', ['csrf']);
    $router->get('/forgot-password', 'AuthController@forgot', ['guest']);
    $router->post('/forgot-password', 'AuthController@sendResetLink', ['guest', 'csrf']);
    $router->get('/reset-password', 'AuthController@resetForm', ['guest']);
    $router->post('/reset-password', 'AuthController@resetPassword', ['guest', 'csrf']);
    $router->get('/verify-email', 'AuthController@verifyEmail', ['auth']);
    $router->get('/select-role', 'AuthController@selectRole', ['auth']);
    $router->post('/select-role', 'AuthController@updateRole', ['auth', 'csrf']);

    // ---------------- JSON API ----------------
    $router->get('/api/jobs', 'ApiController@jobs');
    $router->get('/api/opportunities', 'ApiController@opportunities');
    $router->get('/api/notifications', 'ApiController@notifications', ['auth']);
    $router->post('/api/notifications/read', 'ApiController@readNotification', $all);
    $router->get('/api/match/{id}', 'ApiController@match', ['auth']);
    $router->post('/api/saved-searches', 'ApiController@saveSearch', $all);
    $router->get('/api/health', 'ApiController@health');

    // ---------------- Job actions ----------------
    $router->post('/jobs/{id}/save', 'JobController@toggleSave', $all);
    $router->get('/jobs/{id}/apply', 'JobController@applyForm', ['auth']);
    $router->post('/jobs/{id}/apply', 'JobController@apply', ['auth', 'role:seeker,freelancer', 'csrf']);
    $router->post('/jobs/{id}/report', 'JobController@report', $all);

    // ---------------- Seeker ----------------
    $router->get('/dashboard', 'SeekerController@dashboard', ['auth']);
    $router->get('/profile', 'SeekerController@profile', ['auth']);
    $router->get('/profile/edit', 'SeekerController@editProfile', ['auth']);
    $router->post('/profile/edit', 'SeekerController@updateProfile', $all);
    $router->post('/profile/avatar', 'SeekerController@uploadAvatar', $all);
    $router->get('/my-cv', 'SeekerController@cv', ['auth']);
    $router->post('/my-cv/save', 'SeekerController@saveCv', $all);
    $router->get('/my-cv/download', 'SeekerController@downloadCv', ['auth']);
    $router->post('/resumes', 'SeekerController@uploadResume', $all);
    $router->get('/resumes/{id}/download', 'SeekerController@downloadResume', ['auth']);
    $router->post('/resumes/{id}/default', 'SeekerController@defaultResume', $all);
    $router->get('/applications', 'SeekerController@applications', ['auth']);
    $router->post('/applications/{id}/withdraw', 'SeekerController@withdraw', $all);
    $router->get('/saved-jobs', 'SeekerController@savedJobs', ['auth']);
    $router->get('/recommended-jobs', 'SeekerController@recommended', ['auth']);
    $router->get('/job-alerts', 'SeekerController@alerts', ['auth']);
    $router->post('/job-alerts', 'SeekerController@createAlert', $all);
    $router->post('/job-alerts/{id}/delete', 'SeekerController@deleteAlert', $all);
    $router->get('/my-skills', 'SeekerController@skills', ['auth']);
    $router->post('/my-skills', 'SeekerController@addSkill', $all);
    $router->post('/my-skills/{id}/delete', 'SeekerController@deleteSkill', $all);
    $router->post('/my-skills/{id}/endorse', 'SeekerController@endorseSkill', $all);
    $router->get('/my-portfolio', 'SeekerController@portfolio', ['auth']);
    $router->post('/my-portfolio', 'SeekerController@addPortfolio', $all);
    $router->post('/my-portfolio/{id}/delete', 'SeekerController@deletePortfolio', $all);
    $router->post('/profile/entries', 'SeekerController@addEntry', $all);
    $router->post('/profile/entries/{id}/delete', 'SeekerController@deleteEntry', $all);
    $router->get('/notifications', 'SeekerController@notifications', ['auth']);
    $router->get('/settings', 'SeekerController@settings', ['auth']);
    $router->post('/settings', 'SeekerController@updateSettings', $all);
    $router->post('/account/deactivate', 'SeekerController@deactivate', $all);
    $router->post('/companies/{id}/follow', 'CompanyController@follow', $all);

    // ---------------- Messages ----------------
    $router->get('/messages', 'MessageController@index', ['auth']);
    $router->get('/messages/{thread}', 'MessageController@thread', ['auth']);
    $router->post('/messages/{thread}/send', 'MessageController@send', $all);
    $router->post('/messages/start', 'MessageController@start', $all);

    // ---------------- Employer ----------------
    $router->get('/employer/dashboard', 'EmployerController@dashboard', ['auth', 'role:employer,admin']);
    $router->get('/employer/company', 'EmployerController@company', ['auth', 'role:employer,admin']);
    $router->post('/employer/company', 'EmployerController@updateCompany', ['auth', 'role:employer,admin', 'csrf']);
    $router->get('/employer/jobs/create', 'EmployerController@createJob', ['auth', 'role:employer,admin']);
    $router->post('/employer/jobs', 'EmployerController@storeJob', ['auth', 'role:employer,admin', 'csrf']);
    $router->get('/employer/jobs', 'EmployerController@manageJobs', ['auth', 'role:employer,admin']);
    $router->get('/employer/jobs/{id}/edit', 'EmployerController@editJob', ['auth', 'role:employer,admin']);
    $router->post('/employer/jobs/{id}/edit', 'EmployerController@updateJob', ['auth', 'role:employer,admin', 'csrf']);
    $router->post('/employer/jobs/{id}/status', 'EmployerController@changeStatus', ['auth', 'role:employer,admin', 'csrf']);
    $router->post('/employer/jobs/{id}/delete', 'EmployerController@deleteJob', ['auth', 'role:employer,admin', 'csrf']);
    $router->get('/employer/applicants', 'EmployerController@applicants', ['auth', 'role:employer,admin']);
    $router->get('/employer/applicants/{id}', 'EmployerController@applicantDetail', ['auth', 'role:employer,admin']);
    $router->post('/employer/applications/{id}/status', 'EmployerController@updateApplication', ['auth', 'role:employer,admin', 'csrf']);
    $router->get('/employer/candidates', 'EmployerController@candidates', ['auth', 'role:employer,admin']);
    $router->get('/employer/shortlisted', 'EmployerController@shortlisted', ['auth', 'role:employer,admin']);
    $router->get('/employer/interviews', 'EmployerController@interviews', ['auth', 'role:employer,admin']);
    $router->post('/employer/interviews', 'EmployerController@scheduleInterview', ['auth', 'role:employer,admin', 'csrf']);
    $router->post('/employer/interviews/{id}/status', 'EmployerController@interviewStatus', ['auth', 'role:employer,admin', 'csrf']);
    $router->get('/employer/analytics', 'EmployerController@analytics', ['auth', 'role:employer,admin']);

    // ---------------- Freelancer ----------------
    $router->get('/freelancer/dashboard', 'FreelancerController@dashboard', ['auth', 'role:freelancer']);
    $router->get('/freelancer/profile', 'FreelancerController@profile', ['auth', 'role:freelancer']);
    $router->post('/freelancer/profile', 'FreelancerController@updateProfile', ['auth', 'role:freelancer', 'csrf']);
    $router->get('/freelancer/services', 'FreelancerController@services', ['auth', 'role:freelancer']);
    $router->post('/freelancer/services', 'FreelancerController@addService', ['auth', 'role:freelancer', 'csrf']);
    $router->post('/freelancer/services/{id}/delete', 'FreelancerController@deleteService', ['auth', 'role:freelancer', 'csrf']);
    $router->get('/freelancer/projects', 'FreelancerController@projects', ['auth', 'role:freelancer']);
    $router->post('/freelancer/projects/{id}/apply', 'FreelancerController@applyProject', ['auth', 'role:freelancer', 'csrf']);

    // ---------------- Community ----------------
    $router->post('/community', 'CommunityController@create', $all);
    $router->post('/community/{id}/comment', 'CommunityController@comment', $all);
    $router->post('/community/{id}/like', 'CommunityController@like', $all);
    $router->post('/community/{id}/report', 'CommunityController@report', $all);

    // ---------------- Administration ----------------
    $admin = ['auth', 'role:admin'];
    $adminPost = ['auth', 'role:admin', 'csrf'];
    $router->get('/admin/dashboard', 'AdminController@dashboard', $admin);
    $router->get('/admin/users', 'AdminController@users', $admin);
    $router->post('/admin/users/{id}/status', 'AdminController@userStatus', $adminPost);
    $router->get('/admin/employers', 'AdminController@employers', $admin);
    $router->post('/admin/employers/{id}/verify', 'AdminController@verifyEmployer', $adminPost);
    $router->get('/admin/jobs', 'AdminController@jobs', $admin);
    $router->post('/admin/jobs/{id}/moderate', 'AdminController@moderateJob', $adminPost);
    $router->get('/admin/external-opportunities', 'AdminController@external', $admin);
    $router->get('/admin/external-opportunities/create', 'AdminController@createExternal', $admin);
    $router->post('/admin/external-opportunities', 'AdminController@storeExternal', $adminPost);
    $router->get('/admin/external-opportunities/{id}/edit', 'AdminController@editExternal', $admin);
    $router->post('/admin/external-opportunities/{id}/edit', 'AdminController@updateExternal', $adminPost);
    $router->post('/admin/external-opportunities/{id}/status', 'AdminController@externalStatus', $adminPost);
    $router->post('/admin/external-opportunities/{id}/verify', 'AdminController@verifyExternal', $adminPost);
    $router->post('/admin/external-opportunities/{id}/delete', 'AdminController@deleteExternal', $adminPost);
    $router->get('/admin/categories', 'AdminController@categories', $admin);
    $router->post('/admin/categories', 'AdminController@storeCategory', $adminPost);
    $router->post('/admin/categories/{id}/delete', 'AdminController@deleteCategory', $adminPost);
    $router->get('/admin/verification', 'AdminController@verification', $admin);
    $router->get('/admin/reports', 'AdminController@reports', $admin);
    $router->post('/admin/reports/{id}/resolve', 'AdminController@resolveReport', $adminPost);
    $router->get('/admin/content', 'AdminController@content', $admin);
    $router->post('/admin/content/articles', 'AdminController@storeArticle', $adminPost);
    $router->post('/admin/content/articles/{id}/delete', 'AdminController@deleteArticle', $adminPost);
    $router->post('/admin/content/questions', 'AdminController@storeQuestion', $adminPost);
    $router->get('/admin/ads', 'AdminController@ads', $admin);
    $router->post('/admin/ads', 'AdminController@storeAd', $adminPost);
    $router->post('/admin/ads/{id}/toggle', 'AdminController@toggleAd', $adminPost);
    $router->get('/admin/analytics', 'AdminController@analytics', $admin);
    $router->get('/admin/settings', 'AdminController@settings', $admin);
    $router->post('/admin/settings', 'AdminController@updateSettings', $adminPost);
    $router->post('/admin/announcements', 'AdminController@storeAnnouncement', $adminPost);
    $router->get('/admin/audit-logs', 'AdminController@auditLogs', $admin);
};

