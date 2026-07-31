// assets/js/main.js
const Toast = {
    show(message, type = 'success') {
        let container = document.getElementById('toast-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'toast-container';
            document.body.appendChild(container);
        }
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.innerText = message;
        container.appendChild(toast);
        setTimeout(() => toast.remove(), 4000);
    }
};

const ANPRConfirm = (() => {
    let activeResolve = null;

    const ensureStyles = () => {
        if (document.getElementById('anpr-confirm-styles')) {
            return;
        }

        const style = document.createElement('style');
        style.id = 'anpr-confirm-styles';
        style.textContent = `
            .anpr-confirm-overlay {
                position: fixed;
                inset: 0;
                display: flex;
                align-items: center;
                justify-content: center;
                padding: 1rem;
                background: rgba(7, 10, 18, 0.72);
                backdrop-filter: blur(16px);
                -webkit-backdrop-filter: blur(16px);
                opacity: 0;
                visibility: hidden;
                pointer-events: none;
                transition: opacity 0.2s ease, visibility 0.2s ease;
                z-index: 2000;
            }

            .anpr-confirm-overlay.is-open {
                opacity: 1;
                visibility: visible;
                pointer-events: auto;
            }

            .anpr-confirm-dialog {
                width: min(100%, 460px);
                border-radius: 20px;
                border: 1px solid rgba(255, 255, 255, 0.12);
                background: linear-gradient(180deg, rgba(15, 23, 42, 0.98), rgba(8, 12, 24, 0.98));
                box-shadow: 0 32px 80px rgba(0, 0, 0, 0.45);
                transform: translateY(12px) scale(0.98);
                transition: transform 0.2s ease;
                overflow: hidden;
            }

            .anpr-confirm-overlay.is-open .anpr-confirm-dialog {
                transform: translateY(0) scale(1);
            }

            .anpr-confirm-header {
                padding: 1.25rem 1.35rem 0.85rem;
                display: flex;
                align-items: flex-start;
                gap: 0.9rem;
            }

            .anpr-confirm-icon {
                width: 42px;
                height: 42px;
                border-radius: 12px;
                display: grid;
                place-items: center;
                background: rgba(251, 90, 90, 0.14);
                color: #fb5a5a;
                flex-shrink: 0;
            }

            .anpr-confirm-icon svg {
                width: 20px;
                height: 20px;
            }

            .anpr-confirm-title {
                color: #eef2f7;
                font-size: 1.15rem;
                font-weight: 700;
                line-height: 1.2;
                margin-bottom: 0.25rem;
            }

            .anpr-confirm-message {
                color: #94a3b8;
                font-size: 0.95rem;
                line-height: 1.55;
                padding: 0 1.35rem 1.15rem 4.3rem;
            }

            .anpr-confirm-actions {
                display: flex;
                justify-content: flex-end;
                gap: 0.75rem;
                padding: 0 1.35rem 1.35rem;
            }

            .anpr-confirm-btn {
                border: 0;
                border-radius: 10px;
                padding: 0.75rem 1rem;
                font: inherit;
                font-weight: 700;
                cursor: pointer;
                transition: transform 0.15s ease, box-shadow 0.15s ease, background 0.15s ease;
            }

            .anpr-confirm-btn:focus-visible {
                outline: 2px solid #35e08c;
                outline-offset: 2px;
            }

            .anpr-confirm-btn:hover {
                transform: translateY(-1px);
            }

            .anpr-confirm-cancel {
                background: rgba(148, 163, 184, 0.14);
                color: #e2e8f0;
            }

            .anpr-confirm-cancel:hover {
                background: rgba(148, 163, 184, 0.22);
            }

            .anpr-confirm-confirm {
                background: linear-gradient(135deg, #fb5a5a, #ef4444);
                color: #fff;
                box-shadow: 0 10px 24px rgba(239, 68, 68, 0.22);
            }

            .anpr-confirm-confirm.is-neutral {
                background: linear-gradient(135deg, #35e08c, #14b8a6);
                box-shadow: 0 10px 24px rgba(53, 224, 140, 0.18);
            }

            .anpr-confirm-overlay[data-intent="danger"] .anpr-confirm-icon {
                background: rgba(251, 90, 90, 0.14);
                color: #fb5a5a;
            }

            .anpr-confirm-overlay[data-intent="neutral"] .anpr-confirm-icon {
                background: rgba(53, 224, 140, 0.14);
                color: #35e08c;
            }

            @media (max-width: 520px) {
                .anpr-confirm-header {
                    padding: 1.1rem 1.1rem 0.8rem;
                }

                .anpr-confirm-message {
                    padding: 0 1.1rem 1rem;
                }

                .anpr-confirm-actions {
                    padding: 0 1.1rem 1.1rem;
                    flex-direction: column-reverse;
                }

                .anpr-confirm-btn {
                    width: 100%;
                }
            }
        `;
        document.head.appendChild(style);
    };

    const ensureModal = () => {
        let overlay = document.getElementById('anpr-confirm-overlay');
        if (overlay) {
            return overlay;
        }

        ensureStyles();

        overlay = document.createElement('div');
        overlay.id = 'anpr-confirm-overlay';
        overlay.className = 'anpr-confirm-overlay';
        overlay.setAttribute('aria-hidden', 'true');
        overlay.innerHTML = `
            <div class="anpr-confirm-dialog" role="dialog" aria-modal="true" aria-labelledby="anpr-confirm-title">
                <div class="anpr-confirm-header">
                    <div class="anpr-confirm-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0Z"></path>
                            <line x1="12" y1="9" x2="12" y2="13"></line>
                            <line x1="12" y1="17" x2="12.01" y2="17"></line>
                        </svg>
                    </div>
                    <div>
                        <div class="anpr-confirm-title" id="anpr-confirm-title"></div>
                    </div>
                </div>
                <div class="anpr-confirm-message" id="anpr-confirm-message"></div>
                <div class="anpr-confirm-actions">
                    <button type="button" class="anpr-confirm-btn anpr-confirm-cancel" data-anpr-confirm-cancel>Cancel</button>
                    <button type="button" class="anpr-confirm-btn anpr-confirm-confirm" data-anpr-confirm-confirm>Confirm</button>
                </div>
            </div>
        `;
        document.body.appendChild(overlay);

        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                close(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && overlay.classList.contains('is-open')) {
                close(false);
            }
        });

        overlay.querySelector('[data-anpr-confirm-cancel]').addEventListener('click', () => close(false));
        overlay.querySelector('[data-anpr-confirm-confirm]').addEventListener('click', () => close(true));

        return overlay;
    };

    const close = (value) => {
        const overlay = document.getElementById('anpr-confirm-overlay');
        if (!overlay) {
            return;
        }

        overlay.classList.remove('is-open');
        overlay.setAttribute('aria-hidden', 'true');

        if (activeResolve) {
            const resolve = activeResolve;
            activeResolve = null;
            resolve(value);
        }
    };

    const open = ({ title, message, confirmText, cancelText, intent = 'danger' }) => {
        const overlay = ensureModal();
        const titleNode = overlay.querySelector('#anpr-confirm-title');
        const messageNode = overlay.querySelector('#anpr-confirm-message');
        const confirmNode = overlay.querySelector('[data-anpr-confirm-confirm]');
        const cancelNode = overlay.querySelector('[data-anpr-confirm-cancel]');

        titleNode.textContent = title || 'Confirm action';
        messageNode.textContent = message || 'Are you sure you want to continue?';
        confirmNode.textContent = confirmText || 'Confirm';
        cancelNode.textContent = cancelText || 'Cancel';
        overlay.dataset.intent = intent;
        confirmNode.classList.toggle('is-neutral', intent !== 'danger');

        overlay.classList.add('is-open');
        overlay.setAttribute('aria-hidden', 'false');

        setTimeout(() => confirmNode.focus(), 0);

        return new Promise((resolve) => {
            activeResolve = resolve;
        });
    };

    const bind = () => {
        document.addEventListener('click', (event) => {
            const trigger = event.target.closest('a[data-confirm]');
            if (!trigger) {
                return;
            }

            event.preventDefault();
            open({
                title: trigger.dataset.confirmTitle,
                message: trigger.dataset.confirm,
                confirmText: trigger.dataset.confirmText,
                cancelText: trigger.dataset.cancelText,
                intent: trigger.dataset.confirmIntent || 'danger'
            }).then((confirmed) => {
                if (confirmed) {
                    window.location.href = trigger.href;
                }
            });
        });

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('form[data-confirm]');
            if (!form) {
                return;
            }

            if (form.dataset.confirmed === '1') {
                form.dataset.confirmed = '0';
                return;
            }

            event.preventDefault();
            open({
                title: form.dataset.confirmTitle,
                message: form.dataset.confirm,
                confirmText: form.dataset.confirmText,
                cancelText: form.dataset.cancelText,
                intent: form.dataset.confirmIntent || 'danger'
            }).then((confirmed) => {
                if (confirmed) {
                    form.dataset.confirmed = '1';
                    form.submit();
                }
            });
        });
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bind, { once: true });
    } else {
        bind();
    }

    return { open };
})();