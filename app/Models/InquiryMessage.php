<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InquiryMessage extends Model
{
    use HasFactory;

    //boot
    protected static function boot()
    {
        parent::boot();
        static::created(function ($inquiry) {
            try {

                $query_link = admin_url('inquiry-messages/' . $inquiry->id . '/edit');
                $message_to_admin = <<<EOD
                    <p>Dear Admin,</p>
                    <p>You have a new inquiry from {$inquiry->customer_name}.</p>
                    <p><b>Subject:</b> {$inquiry->subject}</p>
                    <p><b>Message:</b> {$inquiry->message}</p>
                    <p>{$inquiry->message}</p>
                    <p><b>Customer Email:</b> {$inquiry->customer_email}</p>
                    <p><b>Customer Phone:</b> {$inquiry->customer_phone}</p>
                    <p><b>Click</b> <a href="{$query_link}">here</a> to respond to this inquiry.</p>
                    <p>Thank you.</p>
                EOD;
                $data['body'] = $message_to_admin;
                $data['data'] = $data['body'];
                $data['name'] = 'Admin';
                $data['email'] = [
                    'tukundanen@yahoo.com',
                    'mubs0x@gmail.com',
                    'isaac@8technologies.net',
                ];
                $date = date('Y-m-d');
                $data['subject'] = env('APP_NAME') . " - New Inquiry: " . $inquiry->subject . " from " . $inquiry->customer_name . " at " . $date;
                try {
                    Utils::mail_sender($data);
                } catch (\Throwable $th) {
                }
                $sms_to_admin = 'New Inquiry: ' . $inquiry->subject . ' from ' . $inquiry->customer_name . '. Click ' . $query_link . ' to respond.';
                try {
                    Utils::send_sms([
                        'to' => '+256706638494',
                        'message' => $sms_to_admin
                    ]);
                } catch (\Throwable $th) {
                }
            } catch (\Exception $e) {
            }
        });

        //updated
        static::updated(function ($inquiry) {
            //notify customer
            if ($inquiry->notify_customer == 'Yes') {
                $message_to_customer = <<<EOD
                    <p>Dear {$inquiry->customer_name},</p>
                    <p>Your inquiry with subject: <b>{$inquiry->subject}</b> has been responded to.</p>
                    <p><b>Response:</b> {$inquiry->response}</p>
                    <p>Thank you.</p>
                EOD;
                $data['body'] = $message_to_customer;
                $data['data'] = $data['body'];
                $data['name'] = $inquiry->customer_name;
                $data['email'] = [$inquiry->customer_email];
                $date = date('Y-m-d');
                $data['subject'] = env('APP_NAME') . " - Inquiry Response: " . $inquiry->subject . " at " . $date;
                try {
                    Utils::mail_sender($data);
                } catch (\Throwable $th) {
                }
                $sms_to_customer = 'Your inquiry with subject: ' . $inquiry->subject . ' has been responded to. Check your email for details.';
                try {
                    Utils::send_sms([
                        'to' => $inquiry->customer_phone,
                        'message' => $sms_to_customer
                    ]);
                } catch (\Throwable $th) {
                }
            }
        });
    }
}
