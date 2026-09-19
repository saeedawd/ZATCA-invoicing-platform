<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title') | {{ config('app.name', 'فواتير زاتكا') }}</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=cairo:400,600,700&display=swap" rel="stylesheet" />
    <style>
        :root {
            --brand: #059669;
            --brand-dark: #047857;
            --surface: #F4F7F6;
            --text: #1e293b;
            --muted: #64748b;
            --line: #e2e8f0;
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Cairo, ui-sans-serif, system-ui, sans-serif;
            background:
                radial-gradient(ellipse 80% 60% at 100% 0%, rgba(16, 185, 129, 0.16), transparent 55%),
                radial-gradient(ellipse 60% 50% at 0% 100%, rgba(5, 150, 105, 0.1), transparent 50%),
                var(--surface);
            color: var(--text);
            display: flex;
            flex-direction: column;
        }
        .wrap {
            width: 100%;
            max-width: 40rem;
            margin: auto;
            padding: 2.5rem 1.25rem;
            text-align: center;
        }
        .brand {
            display: inline-flex;
            align-items: center;
            gap: 0.65rem;
            text-decoration: none;
            color: var(--brand-dark);
            font-weight: 700;
            font-size: 1.1rem;
            margin-bottom: 2rem;
        }
        .brand img { width: 2.25rem; height: 2.25rem; object-fit: contain; }
        .code {
            display: inline-block;
            font-size: 0.8rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            color: var(--brand-dark);
            background: rgba(5, 150, 105, 0.1);
            border: 1px solid rgba(5, 150, 105, 0.2);
            border-radius: 999px;
            padding: 0.35rem 0.85rem;
            margin-bottom: 1rem;
        }
        h1 {
            margin: 0 0 0.85rem;
            font-size: clamp(1.6rem, 4vw, 2.1rem);
            line-height: 1.35;
            font-weight: 700;
        }
        p {
            margin: 0 auto 1.75rem;
            max-width: 28rem;
            color: var(--muted);
            font-size: 1.05rem;
            line-height: 1.8;
        }
        .actions {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            justify-content: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 2.75rem;
            padding: 0.65rem 1.2rem;
            border-radius: 0.6rem;
            font-size: 0.9rem;
            font-weight: 700;
            text-decoration: none;
            transition: background .15s ease, border-color .15s ease, color .15s ease;
        }
        .btn-primary {
            background: var(--brand);
            color: #fff;
        }
        .btn-primary:hover { background: var(--brand-dark); }
        .btn-ghost {
            background: #fff;
            color: #334155;
            border: 1px solid var(--line);
        }
        .btn-ghost:hover {
            border-color: rgba(5, 150, 105, 0.35);
            color: var(--brand-dark);
        }
        .foot {
            margin-top: 2.5rem;
            font-size: 0.8rem;
            color: #94a3b8;
        }
        .foot a { color: var(--brand-dark); text-decoration: none; font-weight: 600; }
        .foot a:hover { text-decoration: underline; }
    </style>
</head>
<body>
    <main class="wrap">
        <a class="brand" href="{{ url('/') }}">
            <img src="{{ asset('logo.png') }}" alt="فواتير زاتكا" width="36" height="36">
            <span>{{ config('app.name', 'فواتير زاتكا') }}</span>
        </a>

        <div class="code">@yield('code')</div>
        <h1>@yield('heading')</h1>
        <p>@yield('message')</p>

        <div class="actions">
            @yield('actions')
        </div>

        <div class="foot">
            تحتاج مساعدة؟
            <a href="{{ url('/contact') }}">تواصل معنا</a>
            ·
            <a href="mailto:{{ config('seo.contact_to', 'info@zatca.app') }}" dir="ltr">{{ config('seo.contact_to', 'info@zatca.app') }}</a>
        </div>
    </main>
</body>
</html>
