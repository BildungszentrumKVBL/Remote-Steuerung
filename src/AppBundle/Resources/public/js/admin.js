/**
 * @author Dominik Müller (Ashura) ashura@aimei.ch
 */

"use strict";

var observe = {
    actions: {
        moveMouse:   function (data) {
            if (data.command === null) {
                return;
            }

            var container    = $('[data-id=' + data.id + ']');
            var screen       = container.find('.phone-cover');
            var screenTop    = parseInt(screen.css('top'));
            var screenBottom = parseInt(screen.css('bottom'));
            var screenLeft   = parseInt(screen.css('left'));
            var screenRight  = parseInt(screen.css('right'));

            var button     = container.find('[data-button=' + data.command + ']');
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

            setTimeout(function () {
                button.find('.ripple-container').append(
                    '<div class="ripple ripple-on" style="' +
                    'left: 50%;' +
                    'top: 50%;' +
                    'background-color: rgb(255, 87, 34);' +
                    'transform: scale(20);' +
                    '"></div>');
            }, 900);

            setTimeout(function () {
                button.find('.ripple.ripple-on').remove();
                if (data.command === 'cmd_quieter' || data.command === 'cmd_quieter') {
                    return false;
                }
                app.actions.UpdateController(data.status, container);

                return false;
            }, 1200)
        },
        updateZulus: function (data) {
            var status = {};
            for (var i in data) {
                var zulu = data[i];
                if (typeof observe.zulus[zulu.id] === "undefined") {
                    status[zulu.id] = "new";
                } else {
                    status[zulu.id] = "ready";
                }
            }
            // Zulus that aren't listed anymore are not more occupied.
            for (i in observe.zulus) {
                if (typeof status[i] === 'undefined') {
                    status[i] = 'delete';
                }
            }
            for (var id in status) {
                if (status[id] === 'new') {
                    observe.zulus[id] = parseInt(id);
                    console.log(observe.zulus);
                    $.ajax(
                        {
                            'url':      Routing.generate(
                                'admin_observe_new_route',
                                {'zuluId': id}
                            ),
                            'complete': function (data) {
                                var zuluContainer = $('main').children('.container')
                                    .children('.row');
                                zuluContainer.append(data.responseText);
                                zuluContainer.find('.zulu').each(function () {
                                    if ($(this).hasClass('boot') === false) {
                                        $(this).addClass('boot booted');
                                    }
                                });
                            }
                        }
                    );
                } else if (status[id] === 'delete') {
                    zulu = $('[data-zuluid=' + id + ']');
                    zulu.removeClass('boot zulu');
                    delete observe.zulus[id];
                    setTimeout(function () {
                        zulu.parent().animate(
                            {
                                'width':   '0',
                                'height':  '0',
                                'padding': '0'
                            }, 500, "linear", function () {
                                $(this).remove();
                            });
                    }, 1200)
                }
            }
        },
        updateView:  function (data) {
            var $observationWindow = $('[data-id=' + data['id'] + ']');
            // Return if the view didn't change.
            if ($observationWindow.data('viewid') === data['view']['id']) {
                return;
            }
            var url = Routing.generate('admin_observe_update_route', {
                'userId': data['id'],
                'viewId': data['view']['id']
            });
            $.ajax(
                {
                    'url':      url,
                    'complete': function (response) {
                        var $controllerWrapper = $observationWindow.find('.fake-controller-wrapper');
                        $controllerWrapper.find('.boot.booted').removeClass('boot');
                        setTimeout(function () {
                            $observationWindow.find('.fake-controller-wrapper').html(response.responseText);
                            app.boot();
                            $observationWindow.data('viewid', data['view']['id']);
                        }, 700);
                    }
                }
            );
        }
    },

    init: function () {
        var zuluIds = [];
        $('.zulu').each(function () {
            var id      = parseInt(this.dataset.zuluid);
            zuluIds[id] = id;
        });
        // Set current state of observing zulus.
        observe.zulus = zuluIds;

        observe.websocket = WS.connect(_WS_URI);
        observe.websocket.on("socket/connect", function (session) {
            // Session is an Autobahn JS WAMP session.

            var url = window.location.origin + Routing.generate('admin_observe_route');
            session.subscribe("app/command", function (uri, data) {
                if (window.location.href === url) {
                    try {
                        var msg     = JSON.parse(data)["msg"];
                        var payload = JSON.parse(msg);
                        if (typeof payload.action !== 'undefined') {
                            try {
                                observe.actions[payload.action](payload.data);
                            } catch (e) {
                                console.warn('During the Action: "' + payload.action + '" went something wrong.');
                                console.warn('Message: ' + e);
                            }
                        }
                    } catch (e) {
                        console.log('Could not parse string. Error:' . e);
                    }
                }
            });

            console.log("Successfully Connected!");
        });

        observe.websocket.on("socket/disconnect", function (error) {

            console.log("Disconnected for " + error.reason + " with code " + error.code);
        });

    },

    close: function () {

    }
};
