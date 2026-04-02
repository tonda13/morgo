/**
 * MorgoCMS Admin — Vanilla JS entry point
 */

// Flash zprávy — auto-dismiss po 5 sekundách
document.addEventListener('DOMContentLoaded', () => {
    const flashes = document.querySelectorAll('[data-flash]');
    flashes.forEach(flash => {
        setTimeout(() => {
            flash.style.transition = 'opacity 0.5s ease';
            flash.style.opacity = '0';
            setTimeout(() => flash.remove(), 500);
        }, 5000);

        // Manuální zavření
        const closeBtn = flash.querySelector('[data-flash-close]');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => flash.remove());
        }
    });

    // Potvrzení před destruktivní akcí
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            const message = el.dataset.confirm || 'Opravdu chcete provést tuto akci?';
            if (!confirm(message)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    });

    // Dropdown menu
    document.querySelectorAll('[data-dropdown-toggle]').forEach(trigger => {
        trigger.addEventListener('click', (e) => {
            e.stopPropagation();
            const targetId = trigger.dataset.dropdownToggle;
            const dropdown = document.getElementById(targetId);
            if (dropdown) {
                dropdown.classList.toggle('hidden');
            }
        });
    });

    // Zavřít dropdown při kliku mimo
    document.addEventListener('click', () => {
        document.querySelectorAll('[data-dropdown]').forEach(d => {
            d.classList.add('hidden');
        });
    });

    // Mobile sidebar toggle
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('admin-sidebar');
    if (sidebarToggle && sidebar) {
        sidebarToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
        });
    }

    // DELETE formuláře z tlačítek (method override)
    document.querySelectorAll('[data-method="delete"]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            const url = btn.dataset.url || btn.getAttribute('href');
            const message = btn.dataset.confirm || 'Opravdu chcete smazat tuto položku?';
            if (!confirm(message)) return;

            const form = document.createElement('form');
            form.method = 'POST';
            form.action = url;

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';
            form.appendChild(methodInput);

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_csrf_token';
            csrfInput.value = document.querySelector('meta[name="csrf-token"]')?.content || '';
            form.appendChild(csrfInput);

            document.body.appendChild(form);
            form.submit();
        });
    });
});
