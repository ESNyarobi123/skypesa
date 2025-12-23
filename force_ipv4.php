<?php
$envFile = __DIR__ . '/.env';
$content = file_get_contents($envFile);

$settings = [
    'MAIL_HOST' => '109.123.240.147',
    'MAIL_PORT' => '465',
    'MAIL_ENCRYPTION' => 'ssl',
];

foreach ($settings as $key => $value) {
    if (preg_match("/^{$key}=/m", $content)) {
        $content = preg_replace("/^{$key}=.*/m", "{$key}={$value}", $content);
    } else {
        $content .= "\n{$key}={$value}";
    }
}

file_put_contents($envFile, $content);
echo "Settings updated to IP 109.123.240.147 / 465 / ssl";
