require('dotenv').config();
const Echo = require('laravel-echo').default;
const Pusher = require('pusher-js');
const { exec } = require('child_process');

global.Pusher = Pusher;

if (process.argv.length < 4) {
    console.error('Please provide an entity and user token. Usage: node echo.js [entity] [userToken]');
    process.exit(1);
}

const entity = process.argv[2];
const userToken = process.argv[3];

const connectToPusher = () => {
    let echo = new Echo({
        broadcaster: 'pusher',
        key: process.env.PUSHER_MOTHERSHIP_APP_KEY,
        cluster: 'mt1',
        wsHost: process.env.PUSHER_MOTHERSHIP_HOST,
        wssPort: process.env.PUSHER_MOTHERSHIP_PORT,
        forceTLS: process.env.PUSHER_MOTHERSHIP_SCHEME === 'https',
        enabledTransports: ['ws', 'wss'],
        authEndpoint: process.env.PUSHER_MOTHERSHIP_AUTH_URL,
        auth: {
            headers: {
                Authorization: `Bearer ${userToken}`,
                // We have to fake an AJAX request so that Laravel doesn't redirect to the login page
                // Using "Accept: application/json" does not work because Echo sets a "Accept: */*" header
                "X-Requested-With": "XMLHttpRequest"
            },
        },
    });

    const handleEvent = (eventType, entityId, eventData = {}) => {
        exec(`php ./artisan taggy:handle-mothership-websockets-event ${eventType} ${entityId} --data="${JSON.stringify(eventData).replace(/"/g, '\\"')}"`, (error, stdout, stderr) => {
            if (error) {
                console.error(`Error running the Laravel command for ${eventType}:`, error.message);
            }
        });
    };

    echo.private(`entities.${entity}`)
        .listen('.TransactionsAdded', e => handleEvent('TransactionsAdded', entity, e))
        .subscribed(() => { handleEvent('SubscriptionSucceeded', entity)})
        .error((error) => {
            handleEvent('SubscriptionFailed', entity);
            process.exit(1);
        });

    echo.connector.pusher.connection.bind('state_change', (states) => {
        if (states.current === "disconnected" || states.current === "unavailable" || states.current === "connecting") {
            subscriptionSuccessful = false;
            handleEvent('Disconnected', entity, { message: 'Server is offline' });
        }
    });

};

const attemptReconnect = () => {
    let retryCount = 0;
    const maxRetries = 10000;
    const delay = 5000;  // Attempt a reconnect every 5 seconds

    const intervalId = setInterval(() => {
        if (retryCount >= maxRetries) {
            clearInterval(intervalId);
            process.exit(1);
            return;
        }

        try {
            connectToPusher();
            clearInterval(intervalId);
        } catch (err) {
            retryCount++;
        }
    }, delay);
};

connectToPusher();
