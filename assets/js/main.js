/**
 * Lunch in the Park — global JavaScript
 */

(function () {
    'use strict';

    initMobileNav();
    initFormEnhancements();
    initPrintPanel();
    initContactDirectory();
    initContactDialogs();
    initVerifyModal();
    initThursdayDateInputs();
    initConfirmDelete();

    function initMobileNav() {
        var toggle = document.querySelector('[data-nav-toggle]');
        var nav = document.getElementById('site-nav');

        if (!toggle || !nav) {
            return;
        }

        toggle.addEventListener('click', function () {
            var isOpen = nav.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            toggle.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
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
            toggle.setAttribute('aria-label', 'Open menu');
        });
    }

    function initPrintPanel() {
        var buttons = document.querySelectorAll('[data-print-panel]');

        buttons.forEach(function (button) {
            button.addEventListener('click', function () {
                window.print();
            });
        });
    }

    function initContactDirectory() {
        var directories = document.querySelectorAll('[data-contact-directory]');

        directories.forEach(function (section) {
            var searchInput = section.querySelector('[data-contact-directory-search]');
            var cards = section.querySelectorAll('[data-contact-card]');
            var emptyMsg = section.querySelector('[data-contact-directory-empty]');

            if (searchInput && cards.length) {
                searchInput.addEventListener('input', function () {
                    var query = searchInput.value.trim().toLowerCase();
                    var visible = 0;

                    cards.forEach(function (card) {
                        var blob = (card.getAttribute('data-search') || '').toLowerCase();
                        var match = query === '' || blob.indexOf(query) !== -1;
                        card.hidden = !match;
                        card.setAttribute('data-hidden', match ? 'false' : 'true');
                        if (match) {
                            visible += 1;
                        }
                    });

                    if (emptyMsg) {
                        emptyMsg.hidden = visible > 0;
                    }
                });
            }

            var printBtn = section.querySelector('[data-print-contact-directory]');
            if (printBtn) {
                printBtn.addEventListener('click', function () {
                    document.body.classList.add('is-printing-directory');
                    window.print();
                });
            }
        });

        window.addEventListener('afterprint', function () {
            document.body.classList.remove('is-printing-directory');
        });
    }

    function initContactDialogs() {
        document.querySelectorAll('[data-open-dialog]').forEach(function (button) {
            button.addEventListener('click', function () {
                var id = button.getAttribute('data-open-dialog');
                var dialog = id ? document.getElementById(id) : null;
                if (dialog && typeof dialog.showModal === 'function') {
                    dialog.showModal();
                }
            });
        });

        document.querySelectorAll('[data-close-dialog]').forEach(function (button) {
            button.addEventListener('click', function () {
                var dialog = button.closest('dialog');
                if (dialog) {
                    dialog.close();
                }
            });
        });

        document.querySelectorAll('.contact-edit-dialog').forEach(function (dialog) {
            dialog.addEventListener('click', function (event) {
                if (event.target === dialog) {
                    dialog.close();
                }
            });
        });

        document.querySelectorAll('[data-google-calendar-reminder]').forEach(function (button) {
            button.addEventListener('click', function () {
                var sourceId = button.getAttribute('data-notes-source');
                var textarea = sourceId ? document.getElementById(sourceId) : null;
                var notes = textarea ? textarea.value.trim() : '';
                var params = new URLSearchParams({
                    action: 'TEMPLATE',
                    text: 'LITP Reminder',
                    details: notes
                });
                window.open(
                    'https://calendar.google.com/calendar/render?' + params.toString(),
                    '_blank',
                    'noopener,noreferrer'
                );
            });
        });

        if (window.location.hash === '#edit-my-info') {
            var ownCard = document.getElementById('edit-my-info');
            if (ownCard) {
                var editBtn = ownCard.querySelector('[data-open-dialog]');
                if (editBtn) {
                    editBtn.click();
                }
            }
        }

        document.querySelectorAll('dialog[open]').forEach(function (dialog) {
            if (typeof dialog.showModal === 'function') {
                dialog.showModal();
            }
        });
    }

    function initVerifyModal() {
        var modal = document.querySelector('[data-verify-modal]');
        if (!modal) {
            return;
        }

        var apiUrl = modal.getAttribute('data-verify-prompt-url');
        var errorEl = modal.querySelector('[data-verify-modal-error]');
        document.body.classList.add('has-verify-modal');

        function closeModal() {
            modal.hidden = true;
            document.body.classList.remove('has-verify-modal');
        }

        function showError(message) {
            if (!errorEl) {
                return;
            }
            errorEl.textContent = message;
            errorEl.hidden = false;
        }

        modal.querySelectorAll('[data-verify-modal-dismiss]').forEach(function (el) {
            el.addEventListener('click', closeModal);
        });

        var editBtn = modal.querySelector('[data-verify-prompt-edit]');
        if (editBtn) {
            editBtn.addEventListener('click', function () {
                closeModal();
                var ownEdit = document.getElementById('edit-my-info');
                if (ownEdit) {
                    ownEdit.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                var openBtn = document.querySelector('[data-open-dialog^="contact-edit-"]');
                if (openBtn) {
                    openBtn.click();
                }
            });
        }

        var confirmBtn = modal.querySelector('[data-verify-prompt-confirm]');
        if (confirmBtn && apiUrl) {
            confirmBtn.addEventListener('click', function () {
                confirmBtn.disabled = true;
                fetch(apiUrl, { method: 'POST', credentials: 'same-origin' })
                    .then(function (response) {
                        return response.json().then(function (data) {
                            return { ok: response.ok, data: data };
                        });
                    })
                    .then(function (result) {
                        if (result.ok && result.data.ok) {
                            closeModal();
                            return;
                        }
                        showError(result.data.error || 'Could not save. Please try again.');
                        confirmBtn.disabled = false;
                    })
                    .catch(function () {
                        showError('Could not save. Please try again.');
                        confirmBtn.disabled = false;
                    });
            });
        }
    }

    function initThursdayDateInputs() {
        var inputs = document.querySelectorAll('[data-thursday-only]');

        inputs.forEach(function (input) {
            var hint = input.parentElement
                ? input.parentElement.querySelector('[data-thursday-hint]')
                : null;

            function validateThursday() {
                if (!input.value) {
                    input.setCustomValidity('');
                    if (hint) {
                        hint.hidden = true;
                    }
                    return;
                }

                var parts = input.value.split('-');
                var date = new Date(
                    parseInt(parts[0], 10),
                    parseInt(parts[1], 10) - 1,
                    parseInt(parts[2], 10)
                );
                var isThursday = date.getDay() === 4;

                if (!isThursday) {
                    input.setCustomValidity('Please choose a Thursday.');
                    if (hint) {
                        hint.hidden = false;
                    }
                } else {
                    input.setCustomValidity('');
                    if (hint) {
                        hint.hidden = true;
                    }
                }
            }

            input.addEventListener('change', validateThursday);
            input.addEventListener('input', validateThursday);
            validateThursday();
        });
    }

    function initConfirmDelete() {
        document.querySelectorAll('[data-confirm-delete]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                var message = form.getAttribute('data-confirm-delete') || 'Are you sure?';
                if (!window.confirm(message)) {
                    event.preventDefault();
                }
            });
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
