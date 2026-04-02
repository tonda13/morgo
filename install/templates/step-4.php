<h2 class="text-xl font-semibold mb-6">Krok 4 — Admin účet</h2>

<form method="POST" action="/install/step-4">
    <?= csrf_field() ?>

    <div class="mb-4">
        <label class="form-label">Jméno</label>
        <input type="text" name="display_name" value="Admin" class="form-input" required>
    </div>

    <div class="mb-4">
        <label class="form-label">E-mail</label>
        <input type="email" name="admin_email" class="form-input" required>
    </div>

    <div class="mb-2">
        <label class="form-label">Heslo</label>
        <input type="password" name="admin_password" id="admin-password" class="form-input" required minlength="8">
    </div>

    <div class="mb-6">
        <div id="password-strength" class="h-1 rounded mt-1 bg-gray-200 transition-all"></div>
        <p id="password-hint" class="text-xs text-gray-500 mt-1">Minimálně 8 znaků.</p>
    </div>

    <button type="submit" class="btn-primary">Pokračovat →</button>
</form>

<script>
document.getElementById('admin-password').addEventListener('input', function() {
    const val = this.value;
    const bar  = document.getElementById('password-strength');
    const hint = document.getElementById('password-hint');
    let strength = 0;
    if (val.length >= 8)  strength++;
    if (/[A-Z]/.test(val)) strength++;
    if (/[0-9]/.test(val)) strength++;
    if (/[^A-Za-z0-9]/.test(val)) strength++;

    const colors = ['bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-green-500'];
    const labels = ['Slabé', 'Slušné', 'Dobré', 'Silné'];
    bar.className = 'h-1 rounded mt-1 transition-all ' + (colors[strength - 1] || 'bg-gray-200');
    bar.style.width = (strength * 25) + '%';
    hint.textContent = strength > 0 ? labels[strength - 1] : 'Minimálně 8 znaků.';
});
</script>
