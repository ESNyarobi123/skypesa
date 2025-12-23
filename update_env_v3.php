<?php
$envFile = __DIR__ . '/.env';
$content = file_get_contents($envFile);

$settings = [
    'MAIL_HOST' => 'skypesa.hosting.hollyn.online',
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
echo "Settings updated to skypesa.hosting.hollyn.online / 465 / ssl";
