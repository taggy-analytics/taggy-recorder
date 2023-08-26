require('dotenv').config();
const Echo = require('laravel-echo').default;
const Pusher = require('pusher-js');
const { exec } = require('child_process');


global.Pusher = Pusher;

// Check command line arguments
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
        .listen('.TransactionsAdded', e => {
            handleEvent('TransactionsAdded', entity, e);
        })
        .listen('.TransactionsUpdated', e => {
            handleEvent('TransactionsUpdated', entity, e);
        })
        .listen('.TransactionsDeleted', e => {
            handleEvent('TransactionsDeleted', entity, e);
        })
        .error((error) => {
            handleEvent('SubscriptionFailed', entity);
            process.exit(1); // Exit the script with an error code.
        });

    echo.connector.pusher.connection.bind('state_change', function(states) {
        if (states.current === 'connected') {
            console.log('Connection state changed from ' + states.previous + ' to ' + states.current);
            handleEvent('ServerOnline', entity, { message: 'Server is online' });
        } else if (states.current === 'unavailable') {
            console.log('Connection state changed from ' + states.previous + ' to ' + states.current);
            handleEvent('ServerOffline', entity, { message: 'Server is offline' });
        } else {
            console.log('Connection state changed from ' + states.previous + ' to ' + states.current);
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
            console.error('Max reconnect attempts reached. Exiting...');
            process.exit(1);
            return;
        }

        console.log(`Attempt #${retryCount + 1} to reconnect...`);
        try {
            connectToPusher();
            clearInterval(intervalId);  // If successful, clear the interval
        } catch (err) {
            retryCount++;
            console.error('Error reconnecting:', err);
        }
    }, delay);
};

// Initially, try connecting to Pusher
connectToPusher();
