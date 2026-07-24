// This file is part of Moodle - http://moodle.org/

/**
 * Candidate connection status and reconnect UI.
 *
 * @module quizaccess_proctorcore/recovery_ui
 */
define([], function() {
    const BANNER_ID = 'quizaccess-proctorcore-status';

    const ensureBanner = () => {
        let banner = document.getElementById(BANNER_ID);
        if (banner) {
            return banner;
        }
        banner = document.createElement('div');
        banner.id = BANNER_ID;
        banner.className = 'quizaccess-proctorcore-status is-connected';
        banner.setAttribute('role', 'status');
        banner.setAttribute('aria-live', 'polite');

        const text = document.createElement('span');
        text.className = 'quizaccess-proctorcore-status-text';
        banner.appendChild(text);

        const page = document.getElementById('page') || document.body;
        page.insertBefore(banner, page.firstChild);
        return banner;
    };

    const setState = (state, message, reconnectUrl, reconnectLabel) => {
        const banner = ensureBanner();
        banner.className = 'quizaccess-proctorcore-status is-' + state;
        banner.querySelector('.quizaccess-proctorcore-status-text').textContent = message;

        const oldButton = banner.querySelector('.quizaccess-proctorcore-reconnect');
        if (oldButton) {
            oldButton.remove();
        }
        if (state === 'interrupted' && reconnectUrl) {
            const button = document.createElement('a');
            button.className = 'btn btn-primary btn-sm quizaccess-proctorcore-reconnect';
            button.href = reconnectUrl;
            button.textContent = reconnectLabel;
            banner.appendChild(button);
        }
    };

    return {
        init: function(config) {
            const strings = config.strings || {};
            const reconnectUrl = String(config.reconnectUrl || '');

            setState('connected', strings.connected || 'Proctoring connection active', '', '');
            window.addEventListener('proctorcore:heartbeat', function() {
                setState('connected', strings.connected || 'Proctoring connection active', '', '');
            });
            window.addEventListener('proctorcore:connectionlost', function() {
                setState('lost', strings.lost || 'Connection lost', '', '');
            });
            window.addEventListener('proctorcore:interrupted', function(event) {
                const detail = event.detail || {};
                setState('interrupted',
                    strings.interrupted || 'Session interrupted. Reconnect to continue.',
                    detail.reconnectUrl || reconnectUrl,
                    strings.reconnect || 'Reconnect and continue');
            });
            window.addEventListener('offline', function() {
                setState('lost', strings.lost || 'Connection lost', '', '');
            });
            window.addEventListener('online', function() {
                setState('reconnecting', strings.reconnecting || 'Reconnecting…', '', '');
            });
        },
    };
});
