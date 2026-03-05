<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\VehicleRegistrationReminder;
use App\Models\VehicleNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;

class VehicleNotificationController extends Controller
{
    /**
     * Return all vehicle registration reminders ordered by date.
     */
    public function index()
    {
        $notifications = VehicleNotification::with('vehicle')
            ->orderBy('date')
            ->get()
            ->map(fn($n) => [
                'id'          => $n->id,
                'vehicleId'   => $n->vehicle_id,
                'vehicleCode' => $n->vehicle?->vehicle_code,
                'make'        => $n->vehicle?->make,
                'model'       => $n->vehicle?->model,
                'year'        => $n->vehicle?->year,
                'date'        => $n->date?->format('Y-m-d'),
                'note'        => $n->note,
                'createdAt'   => $n->created_at?->toDateTimeString(),
            ]);

        return response()->json(['notifications' => $notifications]);
    }

    /**
     * Send the registration reminder email for a specific notification.
     */
    public function sendEmail($id)
    {
        $notification = VehicleNotification::with('vehicle')->find($id);

        if (!$notification) {
            return response()->json(['error' => 'Notification not found'], 404);
        }

        $recipient = env('NOTIFICATION_EMAIL', config('mail.from.address', 'admin@example.com'));

        try {
            Mail::to($recipient)->send(new VehicleRegistrationReminder($notification));

            return response()->json([
                'message' => 'Reminder email sent successfully',
                'sentTo'  => $recipient,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send vehicle registration reminder: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to send email', 'message' => $e->getMessage()], 500);
        }
    }
}
