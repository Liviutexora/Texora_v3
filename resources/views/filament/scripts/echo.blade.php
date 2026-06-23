
@vite('resources/js/app.js')
<script>
    'use strict';
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.Echo === 'undefined') {
            console.error("Echo is not initialized!");
            return;
        }

        const userId = parseInt("{{ auth()->id() ?? 'null' }}");
        if (userId) {
            const userChannel = `user.${userId}`;
            window.Echo.private(userChannel).listen('.new-notification', (e) => {
                console.log("Private Event Received:", e);
                alert(`Private Notification Received: ${e.message}`);
            });
            console.log(`Listening for private events on channel: ${userChannel}`);

            window.Echo.channel('test-channel').listen('.test-event', (e) => {
                console.log("Public Test Event Received:", e);
                alert('Public Test Event Received')
            });

        } else {
            console.log('Not logged in. Cannot subscribe to private channel.');
        }
    });
</script>