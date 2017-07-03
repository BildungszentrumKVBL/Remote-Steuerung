/**
 * @author Dominik Müller (Ashura) ashura@aimei.ch
 */

"use strict";

var observe = {
    actions: {
        moveMouse:   function(data) {
            var commands = controller.commands;
            var buttonName;
            for (var command in commands) {
                if (commands[command] == data.command) {
                    buttonName = command;
                }
            }
            if (buttonName === null) {
                return;
            }

            var container    = $('[data-id=' + data.id + ']');
            var screen       = container.find('.phone-cover');
            var screenTop    = parseInt(screen.css('top'));
            var screenBottom = parseInt(screen.css('bottom'));
            var screenLeft   = parseInt(screen.css('left'));
            var screenRight  = parseInt(screen.css('right'));

            var button     = container.find('[data-button=' + buttonName + ']');
            var buttonRec  = button[0].getBoundingClientRect();
            // Middle of Button on screen
            var buttonTop  = buttonRec.top - (buttonRec.height / 2);
            var buttonLeft = buttonRec.left + (buttonRec.width / 2);

            var mouse     = container.find('.mouse-cursor');
            var mouseRec  = mouse[0].getBoundingClientRect();
            // Top left of mouse on screen
            var mouseTop  = mouseRec.top - (buttonRec.height);
            var mouseLeft = mouseRec.left;

            var moveTop   = buttonTop - mouseTop;
            var moveRight = buttonLeft - mouseLeft;

            screen.css(
                {
                    'top':    screenTop + moveTop + 'px',
                    'bottom': screenBottom - moveTop + 'px',
                    'left':   screenLeft + moveRight + 'px',
                    'right':  screenRight - moveRight + 'px'
                }
            );

            setTimeout(function() {
                button.find('.ripple-container').append(
                    '<div class="ripple ripple-on" style="' +
                    'left: 50%;' +
                    'top: 50%;' +
                    'background-color: rgb(255, 87, 34);' +
                    'transform: scale(20);' +
                    '"></div>');
            }, 900);

            setTimeout(function() {
                button.find('.ripple.ripple-on').remove();
            }, 1200)
        },
        updateZulus: function(data) {
            var status = {};
            for (var i in data) {
                var zulu = data[i];
                if (typeof observe.zulus[zulu.id] === "undefined") {
                    status[zulu.id] = "new";
                }
            }
            for (var i in observe.zulus) {
                var zuluId = observe.zulus[i];
                if (typeof status[zuluId] === 'undefined') {
                    status[zuluId] = 'delete';
                }
            }
            for (var id in status) {
                if (status[id] === 'new') {
                    $.ajax(
                        {
                            'url':      Routing.generate('admin_observe_new_route', {'zuluId': id}),
                            'complete': function(data) {
                                var zuluContainer = $('main').children('.container').children('.row');
                                zuluContainer.append(data.responseText);
                                zuluContainer.find('.zulu').each(function() {
                                    if ($(this).hasClass('boot') === false) {
                                        $(this).addClass('boot booted');
                                    }
                                });
                            }
                        }
                    );
                } else if (status[id] === 'delete') {
                    var zulu = $('[data-zuluid=' + id + ']');
                    zulu.removeClass('boot zulu');
                    setTimeout(function() {
                        zulu.parent().remove();
                    }, 1200)
                }
            }
        }
    },

    init: function() {
        var zuluIds = [];
        $('.zulu').each(function() {
            zuluIds.push(this.dataset.zuluid);
        });
        observe.zulus = zuluIds;

        var websocket = WS.connect(_WS_URI);
        websocket.on("socket/connect", function(session) {
            //session is an Autobahn JS WAMP session.

            session.subscribe("app/command", function(uri, payload) {
                if (window.location.href === window.location.origin + Routing.generate('admin_observe_route')) {
                    try {
                        payload = JSON.parse(payload.msg);
                    } catch (e) {

                    }
                    if (typeof payload.action !== 'undefined') {
                        observe.actions[payload.action](payload.data);
                    }
                }
            });

            session.publish("app/command", {msg: "This is a message!"});

            // session.unsubscribe("app/command");

            console.log("Successfully Connected!");
        });

        websocket.on("socket/disconnect", function(error) {
            //error provides us with some insight into the disconnection: error.reason and error.code

            console.log("Disconnected for " + error.reason + " with code " + error.code);
        });

    },

    close: function() {
    }
};
