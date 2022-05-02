homioPiAssign('message', {
    send: (content, type = 'info', duration = 2500) => {
        const messageId = 'homiopi-message-' + Date.now();
        const $message = $(`<div id="${messageId}" data-message-type="${type}" class="message animate-in"><div class="message-inner">${content}</div></div>`);
        $message.appendTo('.message-area');

        setTimeout(() => {
            $message.removeClass('animate-in');
            setTimeout(() => {
                $message.addClass('animate-out');
                setTimeout(() => {
                    $message.remove();
                }, 600);
            }, duration);
        }, 10);
    },

    changeContent: (messageId, content) => {
        const $message = (`#homiopi-message-${messageId}`);

        return $message.html(content);
    },

    info: (content) => {
        homioPi.message.send(content, 'info');
    },

    error: (content) => {
        homioPi.message.send(content, 'error');
    },

    warning: (content) => {
        homioPi.message.send(content, 'warning');
    }
})
