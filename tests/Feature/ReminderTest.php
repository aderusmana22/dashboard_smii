<?php

namespace Tests\Feature;

use App\Jobs\SendReminderEmail;

use Tests\TestCase;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReminderEmail;
use App\Models\DOC\Reminder;
use Database\Seeders\ReminderSeeder;

class ReminderTest extends TestCase
{

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->seed(ReminderSeeder::class); // Menjalankan seeder
    }

    public function test_reminder_email_is_sent()
{
    // Jalankan seeder atau logika yang memicu pengiriman email
    $seeder = new ReminderSeeder();
    $seeder->run();

    // Assert that a mailable was queued
    Mail::assertQueued(SendReminderEmail::class, function ($mail) {
        return $mail->hasTo('recipient@example.com'); // Ganti dengan email yang sesuai
    });
}
}
