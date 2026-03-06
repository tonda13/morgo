<!DOCTYPE html>
<html lang="cs">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title ?? 'Morgo') ?></title>
    <link rel="icon" type="image/png" href="/assets/images/favicon.png">
    <link rel="stylesheet" href="/assets/css/app.css">
</head>
<body>
    <header>
        <a href="/" class="header-brand">
            <img src="/assets/images/logo.png" alt="Morgo logo">
            <span>Morgo</span>
        </a>
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
