// Load Pusher from CDN if not already loaded
function loadPusher() {
    return new Promise((resolve, reject) => {
        if (window.Pusher) {
            resolve(window.Pusher);
            return;
        }
        
        const script = document.createElement('script');
        script.src = 'https://js.pusher.com/7.2/pusher.min.js';
        script.async = true;
        script.onload = () => {
            if (window.Pusher) {
                resolve(window.Pusher);
            } else {
                reject(new Error('Pusher failed to load'));
            }
        };
        script.onerror = () => {
            reject(new Error('Failed to load Pusher script'));
        };
        document.head.appendChild(script);
    });
}

// Initialize Echo
async function initializeEcho() {
    try {
        // Load Pusher first
        await loadPusher();
        
        // Import Laravel Echo
        const EchoModule = await import('laravel-echo');
        
        // Get CSRF token
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
        
        // Initialize Echo with Pusher
        window.Echo = new EchoModule.default({
            broadcaster: 'pusher',
            key: window.Laravel?.echo?.key || process.env.MIX_PUSHER_APP_KEY || 'your-pusher-key',
            wsHost: window.Laravel?.echo?.host || window.location.hostname,
            wsPort: window.Laravel?.echo?.port || 6001,
            wssPort: window.Laravel?.echo?.port || 6001,
            forceTLS: false,
            disableStats: true,
            enabledTransports: ['ws', 'wss'],
            auth: {
                headers: {
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                }
            },
            authorizer: (channel) => {
                return {
                    authorize: (socketId, callback) => {
                        axios.post('/broadcasting/auth', {
                            socket_id: socketId,
                            channel_name: channel.name
                        }, {
                            headers: {
                                'X-CSRF-TOKEN': token,
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => {
                            callback(false, response.data);
                        })
                        .catch(error => {
                            callback(true, error);
                        });
                    }
                };
            }
        });
        
        console.log('Echo initialized successfully');
        
        // Dispatch an event to notify that Echo is ready
        const event = new Event('echo:ready');
        window.dispatchEvent(event);
        
        // Log connection status
        window.Echo.connector.pusher.connection.bind('connected', () => {
            console.log('Pusher connected');
        });
        
        window.Echo.connector.pusher.connection.bind('disconnected', () => {
            console.warn('Pusher disconnected');
        });
        
        window.Echo.connector.pusher.connection.bind('error', (err) => {
            console.error('Pusher error:', err);
        });
        
    } catch (error) {
        console.error('Failed to initialize Echo:', error);
    }
}

// Start the initialization process
initializeEcho();
