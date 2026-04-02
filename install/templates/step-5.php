<h2 class="text-xl font-semibold mb-6">Krok 5 — Nastavení webu</h2>

<form method="POST" action="/install/step-5">
    <?= csrf_field() ?>

    <div class="mb-4">
        <label class="form-label">Název webu</label>
        <input type="text" name="site_name" value="Morgo" class="form-input" required>
    </div>

    <div class="mb-6">
        <label class="form-label">URL webu</label>
        <input type="url" name="site_url" value="<?= esc_attr(site_url()) ?>" class="form-input" required>
        <p class="form-help">Včetně http:// nebo https://, bez lomítka na konci.</p>
    </div>

    <button type="submit" class="btn-primary">Dokončit instalaci →</button>
</form>
