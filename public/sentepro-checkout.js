/**
 * SentePro Checkout Widget.
 *
 * Embed on your own site to open SentePro's hosted checkout in a popup
 * window or an in-page modal, instead of a full-page redirect:
 *
 *   <script src="https://sentepro.test/sentepro-checkout.js" defer></script>
 *   <button
 *     data-sentepro-checkout
 *     data-checkout-url="https://sentepro.test/pay/42"
 *     data-mode="popup"
 *     data-on-complete="onSentePropaymentComplete"
 *     data-on-close="onSentePropaymentClosed"
 *   >Pay Now</button>
 *
 * data-mode is "popup" (default) or "modal". Both callbacks are optional
 * global function names: on-complete receives {status, reference} and only
 * fires once the checkout page reports a terminal status; on-close fires
 * whenever the popup/modal closes for any reason, with no arguments.
 */
(function () {
    'use strict';

    var TERMINAL_STATUSES = ['completed', 'failed', 'refunded', 'partially_refunded'];

    function callByName(name) {
        var args = Array.prototype.slice.call(arguments, 1);
        if (name && typeof window[name] === 'function') {
            window[name].apply(window, args);
        }
    }

    function openPopup(url, trigger) {
        var popup = window.open(url, 'sentepro_checkout', 'width=480,height=760,resizable,scrollbars');

        if (!popup) {
            window.location.href = url;
            return;
        }

        var closed = false;
        var onClose = function () {
            if (closed) {
                return;
            }
            closed = true;
            clearInterval(watcher);
            window.removeEventListener('message', onMessage);
            callByName(trigger.getAttribute('data-on-close'));
        };

        var onMessage = function (event) {
            if (!event.data || event.data.source !== 'sentepro-checkout') {
                return;
            }
            if (TERMINAL_STATUSES.indexOf(event.data.status) !== -1) {
                callByName(trigger.getAttribute('data-on-complete'), { status: event.data.status, reference: event.data.reference });
                popup.close();
                onClose();
            }
        };

        window.addEventListener('message', onMessage);

        var watcher = setInterval(function () {
            if (popup.closed) {
                onClose();
            }
        }, 500);
    }

    function openModal(url, trigger) {
        var backdrop = document.createElement('div');
        backdrop.setAttribute('style', 'position:fixed;inset:0;background:rgba(15,23,42,0.75);display:flex;align-items:center;justify-content:center;z-index:2147483647;');

        var frame = document.createElement('iframe');
        frame.src = url;
        frame.setAttribute('style', 'width:480px;max-width:95vw;height:760px;max-height:90vh;border:0;border-radius:16px;background:#0f172a;');

        var closeButton = document.createElement('button');
        closeButton.setAttribute('aria-label', 'Close checkout');
        closeButton.textContent = '×';
        closeButton.setAttribute('style', 'position:absolute;top:16px;right:16px;width:36px;height:36px;border-radius:9999px;border:0;background:#ffffff;color:#0f172a;font-size:20px;cursor:pointer;');

        var wrapper = document.createElement('div');
        wrapper.setAttribute('style', 'position:relative;');
        wrapper.appendChild(frame);
        wrapper.appendChild(closeButton);
        backdrop.appendChild(wrapper);
        document.body.appendChild(backdrop);

        var closed = false;
        var close = function () {
            if (closed) {
                return;
            }
            closed = true;
            document.removeEventListener('keydown', onKeydown);
            window.removeEventListener('message', onMessage);
            backdrop.remove();
            callByName(trigger.getAttribute('data-on-close'));
        };

        var onKeydown = function (event) {
            if (event.key === 'Escape') {
                close();
            }
        };

        var onMessage = function (event) {
            if (!event.data || event.data.source !== 'sentepro-checkout') {
                return;
            }
            if (TERMINAL_STATUSES.indexOf(event.data.status) !== -1) {
                callByName(trigger.getAttribute('data-on-complete'), { status: event.data.status, reference: event.data.reference });
                close();
            }
        };

        backdrop.addEventListener('click', function (event) {
            if (event.target === backdrop) {
                close();
            }
        });
        closeButton.addEventListener('click', close);
        document.addEventListener('keydown', onKeydown);
        window.addEventListener('message', onMessage);
    }

    function handleClick(event) {
        var trigger = event.currentTarget;
        var url = trigger.getAttribute('data-checkout-url');

        if (!url) {
            return;
        }

        event.preventDefault();

        var mode = trigger.getAttribute('data-mode') || 'popup';

        if (mode === 'modal') {
            openModal(url, trigger);
        } else {
            openPopup(url, trigger);
        }
    }

    function init() {
        var triggers = document.querySelectorAll('[data-sentepro-checkout]');
        for (var i = 0; i < triggers.length; i++) {
            triggers[i].addEventListener('click', handleClick);
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
