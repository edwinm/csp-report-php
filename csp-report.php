<?php
/**
 * csp-report.php
 *
 * Content Security Policy report tool
 *
 * Copyright 2016 Edwin Martin
 *
 * License: MIT
 *
 */

/* Start config, edit the lines below */
/* First create an incoming webhook here: https://api.slack.com/incoming-webhooks */
/* Replace $webhookUrl with your webhook url */
$webhookUrl = "https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX";
$channel = "#csp";
/* End config */


$data = json_decode(file_get_contents('php://input'), true);

if ($data == null) {
    echo "<h1>Content Security Policy report tool</h1>";
    die;
}

$data = $data["csp-report"];

//$report = json_encode($data["csp-report"]);
$report = "";
$documentUri = "";
$effectiveDirective = "";

foreach ($data as $directive => $value) {
    switch ($directive) {
        case "document-uri":
            $documentUri = $value;
            break;
        case "effective-directive":
            $effectiveDirective = $value;
            break;
        default:
            $report .= "*$directive*:\n$value\n";
            break;
    }
}

$dataString = <<<EOT
{
    "text": "*Content Security Policy report*",
    "channel": "$channel",
    "attachments": [
        {
            "text": "*document-uri*:\n$documentUri\n*effective-directive*:\n$effectiveDirective\n$report",
            "color": "#7CD197",
            "mrkdwn_in": ["text", "pretext"]
        }
    ]
}
EOT;

$ch = curl_init($webhookUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $dataString);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($dataString))
);

$result = curl_exec($ch);
