<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Mailer;
use App\Core\Validator;

final class StaticController extends Controller
{
    public function about(): void
    {
        $stats = [
            'countries' => Database::scalar('SELECT COUNT(*) FROM countries WHERE active = 1'),
            'categories' => Database::scalar('SELECT COUNT(*) FROM job_categories WHERE active = 1'),
            'jobs' => Database::scalar("SELECT COUNT(*) FROM jobs WHERE status = 'published'"),
        ];
        $this->render('static.about', ['pageStats' => $stats], 'About Us');
    }

    public function contact(): void
    {
        $this->render('static.contact', [], 'Contact Us');
    }

    public function sendContact(): void
    {
        $this->csrf();
        $errors = Validator::make($this->request->post, [
            'name' => 'required|min:2|max:150',
            'email' => 'required|email',
            'message' => 'required|min:10|max:5000',
        ]);
        if ($errors) {
            old_input($this->request->post);
            flash('error', 'Please correct the highlighted fields.');
            redirect('contact');
        }
        Database::insert('contact_messages', [
            'name' => trim($this->request->post('name')),
            'email' => trim($this->request->post('email')),
            'subject' => trim((string) $this->request->post('subject', 'General enquiry')),
            'message' => trim($this->request->post('message')),
            'created_at' => date('Y-m-d H:i:s'),
        ]);
        Mailer::send((string) config('mail.from'), 'New contact message', $this->request->post('name') . ' wrote: ' . $this->request->post('message'));
        clear_old();
        flash('success', 'Thank you — your message has been received. We will reply soon.');
        redirect('contact');
    }

    public function help(): void { $this->render('static.help', [], 'Help Centre'); }
    public function privacy(): void { $this->render('static.privacy', [], 'Privacy Policy'); }
    public function terms(): void { $this->render('static.terms', [], 'Terms and Conditions'); }

    public function newsletter(): void
    {
        $this->csrf();
        $email = trim((string) $this->request->post('email', ''));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Please enter a valid email address.');
        } else {
            Database::query(
                "INSERT INTO newsletter_subscribers (email, name, subscribed_at) VALUES (:email, :name, NOW())
                 ON DUPLICATE KEY UPDATE status = 'active'",
                ['email' => $email, 'name' => trim((string) $this->request->post('name', ''))]
            );
            flash('success', 'You are subscribed to the weekly career newsletter.');
        }
        redirect();
    }
}
