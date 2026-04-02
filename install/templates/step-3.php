<h2 class="text-xl font-semibold mb-6">Krok 3 — Vytvoření databáze</h2>
<p class="text-gray-600 mb-6">Kliknutím na tlačítko spustíte vytvoření tabulek databáze.</p>

<form method="POST" action="/install/step-3">
    <?= csrf_field() ?>
    <button type="submit" class="btn-primary">Spustit migrace →</button>
</form>
