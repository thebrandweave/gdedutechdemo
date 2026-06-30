<?php
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "http://localhost/gdedutechdemo/adminPanel/Admissions/");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, "PHPSESSID=qc7go8mpfttev1nv9ath52915m");
$html = curl_exec($ch);
curl_close($ch);

if (!$html) {
    die("Failed to fetch HTML via curl\n");
}

libxml_use_internal_errors(true);
$dom = new DOMDocument();
$dom->loadHTML($html);
$errors = libxml_get_errors();

echo "HTML LENGTH: " . strlen($html) . "\n";
echo "PARSING ERRORS: " . count($errors) . "\n";
foreach ($errors as $error) {
    echo "Line {$error->line}: {$error->message}";
}
?>
