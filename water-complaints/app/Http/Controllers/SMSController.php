<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SMSController extends Controller
{
    public function receive(Request $request)
    {
        $message = $request->input('text'); // the SMS text
        $from = $request->input('from'); // sender's phone number

        // Optionally analyze or store complaint
        Complaint::create([
            'user_phone' => $from,
            'complaint' => $message,
            'source' => 'sms',
        ]);

        return response('Complaint received via SMS.', 200);
    }
}
