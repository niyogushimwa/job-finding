<?php
declare(strict_types=1);

namespace App\Core;

/** Sends mail via SMTP when configured, otherwise logs to storage/logs/mail.log for local development. */
final class Mailer
{
    public static function send(string $to, string $subject, string $body, ?string $name = null): bool
    {
        $from = (string) config('mail.from');
        $fromName = (string) config('mail.from_name');
        $headers = "From: $fromName <$from>\r\nReply-To: $from\r\nMIME-Version: 1.0\r\nContent-Type: text/plain; charset=UTF-8\r\n";
        $logLine = sprintf("[%s] TO: %s | SUBJECT: %s | BODY: %s\n", date('c'), $to, $subject, str_replace(["\r", "\n"], ' ', $body));
        if (config('mail.driver') !== 'smtp') {
            return (bool) @file_put_contents(BASE_PATH . '/storage/logs/mail.log', $logLine, FILE_APPEND | LOCK_EX);
        }
        $smtpHost = (string) config('mail.smtp_host');
        if ($smtpHost === '') {
            return false;
        }
        // Use the environment's sendmail path where available; SMTP credentials are never exposed to the browser.
        $ok = @mail($to, $subject, $body, $headers);
        @file_put_contents(BASE_PATH . '/storage/logs/mail.log', $logLine, FILE_APPEND | LOCK_EX);
        return $ok;
    }
}
