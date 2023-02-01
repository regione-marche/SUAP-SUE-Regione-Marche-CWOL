var GoogleClient = (function () {
    var google_client_id = '648970161163-akaik4v1uc6onp4b0qqjlo0vj2onof8p.apps.googleusercontent.com';
    var google_api_key = 'AIzaSyB_8LHL2UBtSp1nI84CdOCQyXwW1J6eLCM';
    var google_scope = 'https://www.googleapis.com/auth/calendar.readonly';

    var authorized_callback;

    function initLibrary(callback) {
        var po = document.createElement('script');
        po.type = 'text/javascript';
        po.src = '//apis.google.com/js/api.js';
        po.onload = callback;
        var s = document.getElementsByTagName('script')[0];
        s.parentNode.insertBefore(po, s);
    }

    function handleClientLoad() {
        if (typeof gapi === 'undefined') {
            /**
             * Effettuo il caricamento della libreria se non trovo
             * l'oggetto 'gapi'.
             */
            initLibrary(handleClientLoad);
            return;
        }

        /**
         * Imposto i dati necessari al caricamento della libreria
         * ed effettuo una prima autorizzazione immediata per verificare
         * se l'utente sia già loggato o meno su Google.
         */
        gapi.load('client', function () {
            gapi.client.setApiKey(google_api_key);
            window.setTimeout(function () {
                gapi.auth.authorize({
                    client_id: google_client_id,
                    scope: google_scope,
                    immediate: true
                }, handleAuthResult);
            }, 1);
        });
    }

    function handleAuthResult(authResult) {
        /**
         * Gestisco il risultato del check dell'autorizzazione.
         * Se ho una funzione di callback, la chiamo ritornando
         * 'true' se autorizzato, 'false' in caso contrario.
         */
        if (typeof authorized_callback === 'function') {
            authorized_callback(!!(authResult && !authResult.error));
            authorized_callback = null;
        }
    }

    return {
        load: function (callback) {
            authorized_callback = callback;

            handleClientLoad();
        },
        signIn: function (callback) {
            authorized_callback = callback;

            /**
             * Chiamo l'autenticazione per l'accesso a Google.
             */
            gapi.auth.authorize({
                client_id: google_client_id,
                scope: google_scope,
                immediate: false
            }, handleAuthResult);
        },
        signOut: function () {
            gapi.auth.signOut();
        }
    };
})();