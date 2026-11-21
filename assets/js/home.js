(() => {
    'use strict';

    const dateElement = document.getElementById('visit-date');
    const timeElement = document.getElementById('visit-time');

    if (!dateElement || !timeElement || typeof Intl === 'undefined') {
        return;
    }

    const serverTime = Date.parse(timeElement.dateTime);

    if (!Number.isFinite(serverTime)) {
        return;
    }

    // Follow the server clock instead of relying on the device's local timezone.
    const startedAt = performance.now();
    const dateFormatter = new Intl.DateTimeFormat('ms-MY', {
        timeZone: 'Asia/Kuala_Lumpur',
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
    const timeFormatter = new Intl.DateTimeFormat('ms-MY', {
        timeZone: 'Asia/Kuala_Lumpur',
        hour: '2-digit',
        minute: '2-digit',
        hourCycle: 'h23',
    });
    const machineDateFormatter = new Intl.DateTimeFormat('en-CA', {
        timeZone: 'Asia/Kuala_Lumpur',
        year: 'numeric',
        month: '2-digit',
        day: '2-digit',
    });

    const updateClock = () => {
        const now = new Date(serverTime + performance.now() - startedAt);
        dateElement.textContent = dateFormatter.format(now);
        const dateParts = Object.fromEntries(
            machineDateFormatter.formatToParts(now).map(({ type, value }) => [type, value])
        );
        dateElement.dateTime = `${dateParts.year}-${dateParts.month}-${dateParts.day}`;
        timeElement.textContent = timeFormatter.format(now).replace('.', ':');
        timeElement.dateTime = now.toISOString();
    };

    updateClock();
    window.setInterval(updateClock, 1000);
})();
