<?php
include("../config/db.php");

/* =========================
   CONFIG
========================= */
$ACCESS_TOKEN = "YOUR_WHATSAPP_ACCESS_TOKEN";
$PHONE_NUMBER_ID = "YOUR_PHONE_NUMBER_ID";

/* =========================
   WHATSAPP FUNCTION
========================= */
function sendWhatsApp($phone, $message, $ACCESS_TOKEN, $PHONE_NUMBER_ID) {

    $url = "https://graph.facebook.com/v19.0/$PHONE_NUMBER_ID/messages";

    $data = [
        "messaging_product" => "whatsapp",
        "to" => $phone,
        "type" => "text",
        "text" => ["body" => $message]
    ];

    $headers = [
        "Authorization: Bearer $ACCESS_TOKEN",
        "Content-Type: application/json"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
}
?>