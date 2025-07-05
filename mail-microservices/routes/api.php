<?php
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;
use App\Mail\SubscriptionExpirationReminder;
use Illuminate\Http\Request;

Route::post('/send-reminder', function (Request $request) {
    $validated = $request->validate([
        'email' => 'required|email',
        'nom' => 'required|string',
        'dateend' => 'required|date',
        
    ]);

    Mail::to($validated['email'])->send(
        new SubscriptionExpirationReminder($validated)
    );

    return response()->json(['status' => 'Email sent successfully']);
});