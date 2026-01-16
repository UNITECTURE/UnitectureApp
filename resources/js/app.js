import './bootstrap';

document.addEventListener('DOMContentLoaded', function () {
    const toggles = document.querySelectorAll('[data-collapse-toggle]');

    toggles.forEach(toggle => {
        toggle.addEventListener('click', function () {
            const targetId = this.getAttribute('data-collapse-toggle');
            const target = document.getElementById(targetId);
            const icon = this.querySelector('[data-accordion-icon]');

            if (target) {
                target.classList.toggle('hidden');

                // Rotate icon if it exists
                if (icon) {
                    icon.classList.toggle('rotate-180');
                }
            }
        });
    });
});
