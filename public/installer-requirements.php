<?php
declare(strict_types=1);

$currentPhpVersion = PHP_VERSION;
$appName = 'Slotara';
$envPaths = [
    __DIR__ . '/../.env',
    __DIR__ . '/../.env.example',
];

foreach ($envPaths as $envPath) {
    if (file_exists($envPath)) {
        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || str_starts_with($line, '#')) {
                continue;
            }

            [$key, $value] = array_pad(explode('=', $line, 2), 2, '');
            $key = trim($key);
            $value = trim($value);
            $value = trim($value, "\"'");

            if ($key === 'APP_NAME' && $value !== '') {
                $appName = $value;
                break 2;
            }
        }
    }
}

$requirements = [
    [
        'name' => 'PHP Version >= 8.2',
        'check' => version_compare($currentPhpVersion, '8.2.0', '>='),
        'detail' => "Current version: {$currentPhpVersion}",
    ],
    [
        'name' => 'BCMath Extension',
        'check' => extension_loaded('bcmath'),
    ],
    [
        'name' => 'Ctype Extension',
        'check' => extension_loaded('ctype'),
    ],
    [
        'name' => 'JSON Extension',
        'check' => extension_loaded('json'),
    ],
    [
        'name' => 'Mbstring Extension',
        'check' => extension_loaded('mbstring'),
    ],
    [
        'name' => 'OpenSSL Extension',
        'check' => extension_loaded('openssl'),
    ],
    [
        'name' => 'PDO Extension',
        'check' => extension_loaded('pdo'),
    ],
    [
        'name' => 'PDO MySQL Extension',
        'check' => extension_loaded('pdo_mysql'),
    ],
    [
        'name' => 'Tokenizer Extension',
        'check' => extension_loaded('tokenizer'),
    ],
    [
        'name' => 'XML Extension',
        'check' => extension_loaded('xml'),
    ],
    [
        'name' => 'ZIP Extension',
        'check' => extension_loaded('zip'),
    ],
];

$allPassed = true;
foreach ($requirements as $requirement) {
    if (!$requirement['check']) {
        $allPassed = false;
        break;
    }
}

$steps = [
    ['label' => 'Welcome', 'state' => 'completed'],
    ['label' => 'Requirements', 'state' => 'current'],
    ['label' => 'Permissions', 'state' => 'pending'],
    ['label' => 'Database', 'state' => 'pending'],
    ['label' => 'Admin', 'state' => 'pending'],
    ['label' => 'Finish', 'state' => 'pending'],
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?> Installer » Requirements</title>
    <script src="/public/js/tailwindcss.js"></script>
    <style>
        body {
            min-height: 100vh;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    <?= htmlspecialchars($appName, ENT_QUOTES, 'UTF-8') ?> Installer
                </h2>
                <div class="mt-4">
                    <div class="flex items-center justify-center space-x-2">
                        <?php foreach ($steps as $index => $step) : ?>
                            <?php
                            $dotClass = match ($step['state']) {
                                'completed' => 'bg-green-500 text-white',
                                'current' => 'bg-blue-500 text-white',
                                default => 'bg-gray-300 text-gray-600',
                            };
                            $connectorClass = $step['state'] === 'completed' ? 'bg-green-500' : 'bg-gray-300';
                            ?>
                            <div class="flex items-center">
                                <div class="relative flex items-center justify-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm <?= $dotClass ?>">
                                        <?= $index + 1 ?>
                                    </div>
                                </div>
                                <?php if ($index !== array_key_last($steps)) : ?>
                                    <div class="w-12 h-0.5 <?= $connectorClass ?>"></div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="bg-white shadow-xl rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-3">Server Requirements</h3>
                <p class="text-sm text-gray-500 mb-4">
                    Your server must meet the following requirements to proceed with the installation.
                </p>

                <div class="space-y-3">
                    <?php foreach ($requirements as $requirement) : ?>
                        <?php
                        $isMet = $requirement['check'];
                        $rowClass = $isMet
                            ? 'bg-green-50 border-green-100 text-green-700'
                            : 'bg-red-50 border-red-100 text-red-700';
                        ?>
                        <div class="flex items-center justify-between px-4 py-3 rounded-xl border <?= $rowClass ?>">
                            <div>
                                <p class="text-sm font-medium"><?= htmlspecialchars($requirement['name'], ENT_QUOTES, 'UTF-8') ?></p>
                                <?php if (!empty($requirement['detail'])) : ?>
                                    <span class="text-xs text-gray-500">
                                        <?= htmlspecialchars($requirement['detail'], ENT_QUOTES, 'UTF-8') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <span class="text-sm font-semibold">
                                <?= $isMet ? '✓ Passed' : '✗ Failed' ?>
                            </span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if (!$allPassed) : ?>
                    <div class="mt-6 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded text-sm">
                        Please fix the requirements before proceeding.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>

