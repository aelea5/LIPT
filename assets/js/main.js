/**
 * Lunch in the Park — global JavaScript
 */

(function () {
    'use strict';

    initMobileNav();
    initFormEnhancements();

    function initMobileNav() {
        var toggle = document.querySelector('[data-nav-toggle]');
        var nav = document.getElementById('site-nav');

        if (!toggle || !nav) {
            return;
        }

        toggle.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });

        document.addEventListener('click', function (event) {
            if (!nav.classList.contains('is-open')) {
                return;
            }
            if (nav.contains(event.target) || toggle.contains(event.target)) {
                return;
            }
            nav.classList.remove('is-open');
            toggle.setAttribute('aria-expanded', 'false');
        });
    }

    function initFormEnhancements() {
        var forms = document.querySelectorAll('form[data-enhanced]');

        forms.forEach(function (form) {
            form.addEventListener('submit', function () {
                var submit = form.querySelector('[type="submit"]');
                if (submit && !submit.disabled) {
                    submit.disabled = true;
                    submit.setAttribute('aria-busy', 'true');
                }
            });
        });
    }
})();
