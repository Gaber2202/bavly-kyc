<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>@yield('title') — {{ config('app.name') }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: system-ui, -apple-system, "Segoe UI", Tahoma, sans-serif;
            background: linear-gradient(145deg, #09090b 0%, #000 45%, #18181b 100%);
            color: #e4e4e7;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }
        .card {
            max-width: 28rem;
            width: 100%;
            border: 1px solid rgba(63, 63, 70, 0.65);
            border-radius: 1rem;
            padding: 2rem;
            background: rgba(24, 24, 27, 0.85);
        }
        .badge {
            font-size: 0.65rem;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(212, 175, 55, 0.95);
            font-weight: 600;
        }
        h1 {
            margin: 0.75rem 0 0.5rem;
            font-size: 1.375rem;
            color: #fafafa;
        }
        p {
            margin: 0 0 1.25rem;
            font-size: 0.9rem;
            line-height: 1.6;
            color: #a1a1aa;
        }
        a.btn {
            display: inline-block;
            padding: 0.55rem 1rem;
            border-radius: 0.5rem;
            background: rgba(212, 175, 55, 0.15);
            color: #f5e6b8;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.875rem;
            border: 1px solid rgba(212, 175, 55, 0.35);
        }
        a.btn:hover { background: rgba(212, 175, 55, 0.25); }
        .code { font-size: 2rem; font-weight: 700; color: #fafafa; margin-bottom: 0.25rem; }
    </style>
</head>
<body>
<div class="card">
    <p class="badge">Bavly KYC</p>
    @yield('content')
</div>
</body>
</html>
