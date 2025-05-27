<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\WaterSentiment; 

class UssdController extends Controller
{
    public function handleUssd(Request $request)
    {
        Log::info("USSD Request: ", $request->all());
    
        $sessionId   = $request->input('sessionId');
        $serviceCode = $request->input('serviceCode');
        $phoneNumber = $request->input('phoneNumber');
        $text        = $request->input('text');
    
        if ($text == "") {
            $response  = "CON Welcome to Water Complaints Service\n";
            $response .= "1. Submit a complaint";
        } elseif ($text == "1") {
            $response = "CON Enter your complaint details:";
        } elseif (strpos($text, "1*") === 0) {
            $explodedText = explode("*", $text);
            $step = count($explodedText);
    
            if ($step == 2) {
                // Step 2: User enters complaint details
                $complaint = $explodedText[1] ?? '';
    
                if (empty($complaint)) {
                    $response = "END No complaint received. Please try again.";
                } else {
                    // Save complaint temporarily in session (if needed)
                    session([$sessionId . '_complaint' => $complaint]);
    
                    $response = "CON Please enter your county:";
                }
            } elseif ($step == 3) {
                // Step 3: User enters county
                $county = $explodedText[2] ?? '';
    
                if (empty($county)) {
                    $response = "END County cannot be empty. Please try again.";
                } else {
                    // Save county temporarily in session
                    session([$sessionId . '_county' => $county]);
    
                    $response = "CON Please enter your subcounty:";
                }
            } elseif ($step == 4) {
                // Step 4: User enters subcounty
                $subcounty = $explodedText[3] ?? '';
    
                if (empty($subcounty)) {
                    $response = "END Subcounty cannot be empty. Please try again.";
                } else {
                    // Save subcounty temporarily in session
                    session([$sessionId . '_subcounty' => $subcounty]);
    
                    $response = "CON Please enter your ward:";
                }
            } elseif ($step == 5) {
                // Step 5: User enters ward
                $complaint = session($sessionId . '_complaint', '');
                $county = session($sessionId . '_county', '');
                $subcounty = session($sessionId . '_subcounty', '');
                $ward = $explodedText[4] ?? '';
    
                if (empty($ward)) {
                    $response = "END Ward cannot be empty. Please try again.";
                } else {
                    $analyzed = $this->analyzeComplaint($complaint);
    
                    Log::info("Flask API Response: ", $analyzed);
    
                    if (isset($analyzed['sentiment']) && isset($analyzed['category'])) {
                        // Store complaint along with county, subcounty, and ward
                        $this->storeComplaint($complaint, $analyzed, $county, $subcounty, $ward);
                        $response = $this->generateResponseMessage($analyzed['sentiment']);
                    } else {
                        $response = "END Error analyzing complaint. Please try again.";
                    }
    
                    // Clear session data
                    session()->forget($sessionId . '_complaint');
                    session()->forget($sessionId . '_county');
                    session()->forget($sessionId . '_subcounty');
                }
            } else {
                $response = "END Invalid input. Please try again.";
            }
        } else {
            $response = "END Invalid option. Try again.";
        }
    
        return response($response, 200)->header('Content-Type', 'text/plain');
    }
    

    private function analyzeComplaint($complaintText)
    {
        $apiUrl = 'http://127.0.0.1:5001/analyze';

        try {
            Log::info("Sending complaint to Flask API: " . $complaintText);

            $response = Http::post($apiUrl, ['complaint' => $complaintText]);

            Log::info("Flask API Raw Response: " . $response->body());

            if ($response->successful()) {
                $jsonResponse = $response->json();
                Log::info("Flask API JSON Parsed Response: ", $jsonResponse);
                return $jsonResponse;
            } else {
                Log::error("Flask API request failed with status: " . $response->status());
                return ['error' => 'API request failed', 'status' => $response->status()];
            }
        } catch (\Exception $e) {
            Log::error("Flask API Error: " . $e->getMessage());
            return ['error' => 'API unreachable'];
        }
    }

    private function generateResponseMessage($sentiment)
    {
        $sentimentMap = [
            'positive' => 'pos',
            'neutral'  => 'neu',
            'negative' => 'neg'
        ];

        $shortSentiment = $sentimentMap[strtolower($sentiment)] ?? 'neu';

        $messages = [
            'pos' => "END 😊 Thank you for your positive feedback! We are committed to ensuring quality water service.",
            'neu' => "END 😐 Your feedback has been recorded. We will use it to improve our services.",
            'neg' => "END 😞 We are sorry for the inconvenience. Our team will look into this issue and improve our service."
        ];

        return $messages[$shortSentiment];
    }

    private function storeComplaint($complaintText, $analyzedData, $county, $subcounty, $ward)
    {
        $sentimentMap = [
            'positive' => 'pos',
            'neutral'  => 'neu',
            'negative' => 'neg'
        ];

        $shortSentiment = $sentimentMap[strtolower($analyzedData['sentiment'])] ?? 'neu';

        try {
            WaterSentiment::create([
                'original_caption'   => $complaintText, 
                'processed_caption'  => $analyzedData['processed_caption'] ?? $complaintText, 
                'timestamp'          => now()->setTimezone('Africa/Nairobi'),
                'overall_sentiment'  => $shortSentiment,
                'complaint_category' => $analyzedData['category'],
                'source'             => 'USSD Code',
                'county'             => $county,
                'subcounty'          => $subcounty,
                'ward'               => $ward
            ]);

            Log::info("Complaint stored successfully with county, subcounty, and ward.");
        } catch (\Exception $e) {
            Log::error("Database Error: " . $e->getMessage());
        }
    }
}