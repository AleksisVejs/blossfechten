<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\ContactFormSubmitted;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'message' => ['required', 'string', 'min:10', 'max:3000'],
        ]);

        Log::info('Contact form submission', [
            'name' => $data['name'],
            'email' => $data['email'],
            'message' => $data['message'],
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        $recipient = config('mail.contact_recipient')
            ?: env('CONTACT_RECIPIENT', 'viitinsh@gmail.com');

        try {
            $sentMessage = Mail::to($recipient)->send(new ContactFormSubmitted(
                senderName: $data['name'],
                senderEmail: $data['email'],
                messageBody: $data['message'],
            ));

            Log::info('Contact form email handed off to SMTP', [
                'recipient' => $recipient,
                'from' => config('mail.from.address'),
                'message_id' => $sentMessage?->getMessageId(),
            ]);
        } catch (Throwable $e) {
            // Don't fail the user-facing request if SMTP is down — message
            // is already in the log and can be replayed manually.
            Log::error('Contact form email send failed', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'exception' => $e::class,
            ]);

            return response()->json([
                'message' => 'contact_form_send_failed',
            ], 500);
        }

        if (!$sentMessage) {
            // Be defensive: treat a non-exceptional "no sent message" as failure.
            Log::error('Contact form email send returned an empty result', [
                'recipient' => $recipient,
            ]);

            return response()->json([
                'message' => 'contact_form_send_failed',
            ], 500);
        }

        return response()->json([
            'message' => 'contact_form_send_success',
        ], 201);
    }
}
