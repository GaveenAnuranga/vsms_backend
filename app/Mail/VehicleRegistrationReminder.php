<?php

namespace App\Mail;

use App\Models\VehicleNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VehicleRegistrationReminder extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public VehicleNotification $notification) {}

    public function envelope(): Envelope
    {
        $vehicle = $this->notification->vehicle;
        $label = $vehicle ? "{$vehicle->make} {$vehicle->model} ({$vehicle->vehicle_code})" : "Vehicle #{$this->notification->vehicle_id}";

        return new Envelope(subject: "Reminder: Register {$label} by {$this->notification->date->format('d M Y')}");
    }

    public function content(): Content
    {
        return new Content(view: 'emails.vehicle-registration-reminder');
    }

    public function attachments(): array
    {
        return [];
    }
}
