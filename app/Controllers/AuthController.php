<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\Validator;
use App\Models\User;

final class AuthController extends Controller
{
    public function register(): void
    {
        $this->render('auth.register', ['selectedRole' => (string) $this->request->query('role', 'seeker')], 'Create your free account');
    }

    public function store(): void
    {
        $post = $this->request->post;
        $role = (string) ($post['role'] ?? 'seeker');
        if (!in_array($role, ['seeker', 'employer', 'freelancer'], true)) {
            $role = 'seeker';
        }
        $rules = [
            'name' => 'required|min:2|max:150',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'terms' => 'required',
        ];
        if ($role === 'employer') {
            $rules['company_name'] = 'required|min:2|max:180';
        }
        $errors = Validator::make($post, $rules);
        if ($errors) {
            old_input($post);
            flash('error', implode(' ', $errors));
            redirect('register?role=' . $role);
        }
        if (User::emailExists((string) $post['email'])) {
            flash('error', 'An account with that email already exists. Please log in.');
            redirect('register?role=' . $role);
        }
        $userId = User::register([
            'name' => trim((string) $post['name']),
            'email' => (string) $post['email'],
            'password' => (string) $post['password'],
            'role' => $role,
            'phone' => trim((string) ($post['phone'] ?? '')),
            'location' => trim((string) ($post['location'] ?? 'Kigali')),
            'headline' => trim((string) ($post['headline'] ?? '')),
            'company_name' => trim((string) ($post['company_name'] ?? '')),
            'company_description' => trim((string) ($post['company_description'] ?? '')),
        ]);
        $user = User::find($userId);
        if ($user) {
            Auth::login($user);
        }
        $this->sendVerification($user ?? []);
        clear_old();
        flash('success', 'Welcome to JobLink Rwanda! Your account is ready.');
        redirect($role === 'employer' ? 'employer/company' : ($role === 'freelancer' ? 'freelancer/dashboard' : 'dashboard'));
    }

    private function sendVerification(array $user): void
    {
        if (empty($user['email']) || empty($user['verification_token'])) {
            return;
        }
        Mailer::send(
            $user['email'],
            'Verify your JobLink Rwanda email',
            'Hello ' . $user['name'] . ",\n\nPlease verify your email address by opening this link:\n" . url('verify-email?token=' . $user['verification_token'])
        );
    }

    public function login(): void
    {
        $this->render('auth.login', [], 'Welcome back');
    }

    public function authenticate(): void
    {
        $email = mb_strtolower(trim((string) $this->request->post('email', '')));
        $password = (string) $this->request->post('password', '');
        $ip = $this->request->ip();
        $attempt = Database::one('SELECT * FROM login_attempts WHERE email = :email AND ip = :ip', ['email' => $email, 'ip' => $ip]);
        if ($attempt && !empty($attempt['locked_until']) && strtotime($attempt['locked_until']) > time()) {
            $minutes = (int) config('security.login_lock_minutes');
            flash('error', "Too many failed attempts. Please try again in $minutes minutes.");
            redirect('login');
        }
        [$ok, $result] = Auth::attempt($email, $password);
        if ($ok) {
            Database::delete('login_attempts', 'email = :email AND ip = :ip', ['email' => $email, 'ip' => $ip]);
            if (!empty($this->request->post('remember'))) {
                $token = bin2hex(random_bytes(32));
                Database::update('users', ['remember_token' => hash('sha256', $token)], 'id = :id', ['id' => $result['id']]);
                setcookie('remember_me', (int) $result['id'] . ':' . $token, time() + 86400 * (int) config('security.remember_days'), '/', '', false, true);
            }
            flash('success', 'Welcome back, ' . explode(' ', (string) $result['name'])[0] . '!');
            redirect(match ($result['role']) {
                'admin' => 'admin/dashboard', 'employer' => 'employer/dashboard',
                'freelancer' => 'freelancer/dashboard', default => 'dashboard',
            });
        }
        if ($attempt) {
            $count = (int) $attempt['attempts'] + 1;
            $locked = $count >= (int) config('security.login_max_attempts') ? date('Y-m-d H:i:s', time() + 900) : null;
            Database::update('login_attempts', ['attempts' => $count, 'locked_until' => $locked], 'email = :email AND ip = :ip', ['email' => $email, 'ip' => $ip]);
        } else {
            Database::insert('login_attempts', ['email' => $email, 'ip' => $ip, 'attempts' => 1, 'updated_at' => date('Y-m-d H:i:s')]);
        }
        old_input(['email' => $email]);
        flash('error', (string) $result);
        redirect('login');
    }

    public function logout(): void
    {
        Auth::logout();
        flash('success', 'You have been logged out.');
        redirect();
    }

    public function forgot(): void { $this->render('auth.forgot', [], 'Reset your password'); }

    public function sendResetLink(): void
    {
        $email = mb_strtolower(trim((string) $this->request->post('email', '')));
        $user = User::findByEmail($email);
        if ($user) {
            Database::query("UPDATE password_resets SET used_at = NOW() WHERE email = :email AND used_at IS NULL", ['email' => $email]);
            $token = bin2hex(random_bytes(32));
            Database::insert('password_resets', [
                'email' => $email, 'token' => $token,
                'expires_at' => date('Y-m-d H:i:s', time() + 3600), 'created_at' => date('Y-m-d H:i:s'),
            ]);
            Mailer::send($email, 'Reset your JobLink password',
                "Hello {$user['name']},\n\nUse this link to choose a new password (valid for 1 hour):\n" . url('reset-password?token=' . $token));
        }
        flash('success', 'If an account exists for that email, a reset link has been sent.');
        redirect('login');
    }

    public function resetForm(): void
    {
        $token = (string) $this->request->query('token', '');
        $row = Database::one('SELECT * FROM password_resets WHERE token = :token AND used_at IS NULL AND expires_at > NOW()', ['token' => $token]);
        if (!$row) {
            flash('error', 'This reset link is invalid or has expired.');
            redirect('forgot-password');
        }
        $this->render('auth.reset', ['token' => $token], 'Choose a new password');
    }

    public function resetPassword(): void
    {
        $token = (string) $this->request->post('token', '');
        $row = Database::one('SELECT * FROM password_resets WHERE token = :token AND used_at IS NULL AND expires_at > NOW()', ['token' => $token]);
        if (!$row) {
            flash('error', 'This reset link is invalid or has expired.');
            redirect('forgot-password');
        }
        $errors = Validator::make($this->request->post, ['password' => 'required|min:8|confirmed']);
        if ($errors) {
            flash('error', implode(' ', $errors));
            redirect('reset-password?token=' . urlencode($token));
        }
        Database::begin();
        try {
            Database::update('users', ['password_hash' => password_hash((string) $this->request->post('password'), PASSWORD_BCRYPT)], 'email = :email', ['email' => $row['email']]);
            Database::update('password_resets', ['used_at' => date('Y-m-d H:i:s')], 'id = :id', ['id' => $row['id']]);
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
        $user = User::findByEmail($row['email']);
        if ($user) {
            Auth::login($user);
        }
        flash('success', 'Your password has been updated.');
        redirect('dashboard');
    }

    public function verifyEmail(): void
    {
        $token = (string) $this->request->query('token', '');
        $user = Database::one('SELECT id FROM users WHERE verification_token = :token', ['token' => $token]);
        if (!$user) {
            flash('error', 'Invalid verification link.');
        } else {
            Database::update('users', ['email_verified_at' => date('Y-m-d H:i:s'), 'verification_token' => null], 'id = :id', ['id' => $user['id']]);
            Auth::refresh();
            flash('success', 'Your email address has been verified.');
        }
        redirect('dashboard');
    }

    public function selectRole(): void
    {
        $this->render('auth.select_role', [], 'Choose how you want to use JobLink');
    }

    public function updateRole(): void
    {
        $role = (string) $this->request->post('role', '');
        if (!in_array($role, ['seeker', 'employer', 'freelancer'], true)) {
            flash('error', 'Please select a valid account type.');
            redirect('select-role');
        }
        $userId = (int) Auth::id();
        Database::begin();
        try {
            Database::update('users', ['role' => $role], 'id = :id', ['id' => $userId]);
            if ($role === 'seeker' && !Database::scalar('SELECT COUNT(*) FROM job_seekers WHERE user_id = :id', ['id' => $userId])) {
                Database::insert('job_seekers', ['user_id' => $userId]);
            }
            if ($role === 'employer' && !Database::scalar('SELECT COUNT(*) FROM employers WHERE user_id = :id', ['id' => $userId])) {
                Database::insert('companies', ['name' => Auth::user()['name'] . ' Company', 'slug' => slugify(Auth::user()['name'] . '-' . $userId), 'verification_status' => 'unverified', 'location' => 'Kigali', 'country_id' => 1]);
                Database::insert('employers', ['user_id' => $userId, 'company_id' => (int) Database::connection()->lastInsertId()]);
            }
            if ($role === 'freelancer' && !Database::scalar('SELECT COUNT(*) FROM freelancer_profiles WHERE user_id = :id', ['id' => $userId])) {
                Database::insert('freelancer_profiles', ['user_id' => $userId, 'title' => 'Freelance Professional']);
            }
            Database::commit();
        } catch (\Throwable $e) {
            Database::rollBack();
            throw $e;
        }
        Auth::refresh();
        User::logAudit($userId, 'role_change', 'Account type changed to ' . $role);
        flash('success', 'Your account type has been updated.');
        redirect($role === 'employer' ? 'employer/dashboard' : ($role === 'freelancer' ? 'freelancer/dashboard' : 'dashboard'));
    }
}
