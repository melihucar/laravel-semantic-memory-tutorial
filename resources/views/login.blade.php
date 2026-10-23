<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Log in · Semantic memory demo</title>
    <style>
        :root { --bg: #f6f6f8; --panel: #ffffff; --text: #1f2328; --muted: #6b7280; --line: #e6e7eb; --accent: #635bdf; --accent-soft: #eeedfd; --danger: #d93025; }
        @media (prefers-color-scheme: dark) {
            :root { --bg: #141418; --panel: #1c1c22; --text: #e8eaed; --muted: #9aa0a6; --line: #2c2c34; --accent: #8f88ff; --accent-soft: #2b2a42; --danger: #f28b82; }
        }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; padding: 16px; font: 14px/1.55 Inter, system-ui, -apple-system, sans-serif; background: var(--bg); color: var(--text); }
        main { width: 100%; max-width: 380px; }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 14px; padding: 28px; }
        .brand { display: flex; align-items: center; gap: 8px; font-weight: 600; margin-bottom: 20px; }
        .brand .dot { width: 10px; height: 10px; border-radius: 50%; background: var(--accent); }
        h1 { font-size: 20px; margin: 0 0 4px; }
        p { margin: 0 0 20px; color: var(--muted); }
        label { display: block; font-weight: 500; margin-bottom: 6px; }
        input[type=email], input[type=password] { width: 100%; padding: 10px 12px; margin-bottom: 14px; border: 1px solid var(--line); border-radius: 8px; background: var(--bg); color: inherit; font: inherit; }
        input:focus { outline: 2px solid var(--accent-soft); border-color: var(--accent); }
        .remember { display: flex; align-items: center; gap: 8px; margin-bottom: 18px; color: var(--muted); font-weight: 400; }
        button[type=submit] { width: 100%; padding: 10px; border: 0; border-radius: 8px; background: var(--accent); color: #fff; font: inherit; font-weight: 600; cursor: pointer; }
        .error { color: var(--danger); font-size: 13px; margin: -8px 0 12px; }
        .demo { margin-top: 16px; padding: 16px; border: 1px dashed var(--line); border-radius: 12px; font-size: 13px; color: var(--muted); }
        .demo strong { color: var(--text); }
        .demo ul { list-style: none; padding: 0; margin: 10px 0 0; display: grid; gap: 6px; }
        .demo button { width: 100%; text-align: left; padding: 8px 10px; border: 1px solid var(--line); border-radius: 8px; background: var(--panel); color: inherit; font: inherit; cursor: pointer; }
        .demo button:hover { border-color: var(--accent); }
        .demo .facts { display: block; font-size: 12px; color: var(--muted); }
    </style>
</head>
<body>
<main>
    <div class="card">
        <div class="brand"><span class="dot"></span> Memory demo</div>
        <h1>Log in</h1>
        <p>The assistant remembers each user separately.</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <label for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
            @error('email') <div class="error">{{ $message }}</div> @enderror

            <label for="password">Password</label>
            <input id="password" type="password" name="password" required autocomplete="current-password">

            <label class="remember"><input type="checkbox" name="remember"> Remember me</label>

            <button type="submit">Log in</button>
        </form>
    </div>

    @if (app()->isLocal())
        <div class="demo">
            <strong>Demo accounts</strong> (from the seeder). The password for each is <strong>password</strong>.
            <ul>
                @foreach ([
                    'alice@example.test' => 'Prefers TypeScript, uses macOS',
                    'bob@example.test' => 'Writes Python, uses Windows',
                    'carol@example.test' => 'No memories yet',
                ] as $email => $facts)
                    <li>
                        <button type="button" onclick="document.getElementById('email').value = '{{ $email }}'; document.getElementById('password').focus()">
                            {{ $email }} <span class="facts">{{ $facts }}</span>
                        </button>
                    </li>
                @endforeach
            </ul>
        </div>
    @endif
</main>
</body>
</html>
