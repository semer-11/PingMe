<?php

use Illuminate\Support\Facades\Http;

function sendSms($phone, $message)
{
    $response = Http::post(
        'https://hahu.io/api/send/sms?secret=' . env('HAHU_API_KEY') . '&mode=devices&phone=251' . $phone . '&message=' . $message . '&sim=1&device=' . env('DEVICE')

    )->object();

    return $response;
}


function gemini($text)
{
    try {
        $fullText = 'Take the below message and if the message takes about having class,exam or presentation respond me "Yes" otherwise "No" I don\'t need explanation just "Yes" or "No" \n' . $text;
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent?key=" . env("GEMINI_API_KEY");
        $data['contents']['parts']['text'] = $fullText;
        $response = Http::post($url, $data)->object()->candidates[0]->content->parts[0]->text;
        return $response;
    } catch (\Throwable $th) {
        return "No";
    }
}
