<h2 class="text-xl font-semibold mb-6">Krok 2 — Databáze</h2>

<form method="POST" action="/install/step-2" id="db-form">
    <?= csrf_field() ?>

    <div class="grid grid-cols-2 gap-4 mb-4">
        <div>
            <label class="form-label">Host</label>
            <input type="text" name="db_host" value="mysql" class="form-input" required>
        </div>
        <div>
            <label class="form-label">Port</label>
            <input type="text" name="db_port" value="3306" class="form-input" required>
        </div>
    </div>

    <div class="mb-4">
        <label class="form-label">Název databáze</label>
        <input type="text" name="db_database" value="morgocms" class="form-input" required>
    </div>

    <div class="mb-4">
        <label class="form-label">Uživatel</label>
        <input type="text" name="db_username" value="morgocms" class="form-input" required>
    </div>

    <div class="mb-6">
        <label class="form-label">Heslo</label>
        <input type="password" name="db_password" class="form-input">
    </div>

    <div id="test-result" class="mb-4 hidden"></div>

    <div class="flex gap-3">
        <button type="button" id="test-btn" class="btn-secondary">Otestovat připojení</button>
        <button type="submit" class="btn-primary">Pokračovat →</button>
    </div>
</form>

<script>
document.getElementById('test-btn').addEventListener('click', async () => {
    const form = document.getElementById('db-form');
    const data = new FormData(form);
    const result = document.getElementById('test-result');

    result.className = 'mb-4 p-3 rounded text-sm';
    result.textContent = 'Testuji připojení...';
    result.classList.remove('hidden');

    const response = await fetch('/install/test-db', {
        method: 'POST',
        headers: {'X-CSRF-Token': data.get('_csrf_token')},
        body: data,
    });
    const json = await response.json();

    if (json.ok) {
        result.classList.add('bg-green-50', 'text-green-700');
        result.textContent = '✓ Připojení úspěšné!';
    } else {
        result.classList.add('bg-red-50', 'text-red-700');
        result.textContent = '✗ Připojení selhalo. Zkontrolujte údaje.';
    }
});
</script>
