<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Morgo AI') ?></title>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: system-ui, sans-serif; background: #f5f5f5; color: #333; }
        header { background: #1a1a2e; color: #fff; padding: 1rem 2rem; }
        header a { color: #fff; text-decoration: none; font-size: 1.25rem; font-weight: bold; }
        nav { display: flex; gap: 1.5rem; margin-top: 0.5rem; }
        nav a { color: #ccc; text-decoration: none; font-size: 0.9rem; }
        nav a:hover { color: #fff; }
        main { max-width: 1100px; margin: 2rem auto; padding: 0 1.5rem; }
        footer { text-align: center; padding: 2rem; color: #999; font-size: 0.85rem; }
    </style>
</head>
<body>
    <header>
        <a href="/">Morgo AI</a>
        <nav>
            <a href="/">Úvod</a>
        </nav>
    </header>

    <main>
        <?= $content ?>
    </main>

    <footer>
        &copy; <?= date('Y') ?> Morgo AI
    </footer>
</body>
</html>
