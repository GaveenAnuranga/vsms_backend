<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { max-width: 600px; margin: 0 auto; padding: 24px; }
        .header { background: #1976d2; color: #fff; padding: 16px 24px; border-radius: 8px 8px 0 0; }
        .body { border: 1px solid #e0e0e0; border-top: none; padding: 24px; border-radius: 0 0 8px 8px; }
        .field { margin-bottom: 12px; }
        .label { font-weight: bold; color: #555; }
        .deadline { font-size: 1.2em; color: #d32f2f; font-weight: bold; }
        .note-box { background: #fff8e1; border-left: 4px solid #ffc107; padding: 12px 16px; margin-top: 16px; border-radius: 4px; }
        .footer { margin-top: 24px; font-size: 0.85em; color: #999; }
    </style>
</head>
<body>
<div class="container">
    <div class="header">
        <h2 style="margin:0">Vehicle Registration Reminder</h2>
    </div>
    <div class="body">
        <p>This is a reminder that the following vehicle requires registration.</p>

        @if ($notification->vehicle)
        <div class="field"><span class="label">Vehicle Code:</span> {{ $notification->vehicle->vehicle_code }}</div>
        <div class="field"><span class="label">Vehicle:</span> {{ $notification->vehicle->year }} {{ $notification->vehicle->make }} {{ $notification->vehicle->model }}</div>
        @endif

        <div class="field">
            <span class="label">Registration Deadline:</span>
            <span class="deadline">{{ $notification->date->format('d M Y') }}</span>
        </div>

        @if ($notification->note)
        <div class="note-box">
            <strong>Note:</strong><br>
            {{ $notification->note }}
        </div>
        @endif

        <div class="footer">
            <p>Please ensure the vehicle is registered before the deadline to avoid penalties.<br>
            This reminder was sent by the Vehicle Sales Management System.</p>
        </div>
    </div>
</div>
</body>
</html>
