<!DOCTYPE html>
<html>
<head>
    <title>{{ __('Test Email') }}</title>
</head>
<body>
    <h2>Hello, {{ $data['name'] }} 👋</h2>
    <p>{{ __('This is a test email from :app.', ['app' => config('app.name')]) }}</p>
</body>
</html>
