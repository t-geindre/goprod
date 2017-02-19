module.exports = function () {
    if (!("Notification" in window)) {
        // not supported, abort
        return function() {};
    }

    Notification.requestPermission();

    return function(message, options) {
        var notif = new Notification(message, options);
        var close = notif.close.bind(notif);
        notif.onclick = () => {
            if (options.click) {
                options.click();
            }
            close();
            window.focus()
        };
        setTimeout(close, 15000);
    }
}();
