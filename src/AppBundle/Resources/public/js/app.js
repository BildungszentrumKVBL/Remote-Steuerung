/**
 * @author Dominik Müller (Ashura) ashura@aimei.ch
 *
 *
 * Globals
 *
 * global: navigator
 * global: controller
 * global: observe
 * global: Routing
 * global: ServiceWorkerRegistration
 * global: history
 * global: screen
 */

"use strict";

var app = {
    isLoading: false,

    messaging: null,

    environment: {
        'isFullscreen':     false,
        'firebaseSenderID': null,
        'isSubscribed':     false
    },

    supports: {
        'passiveEvents':     false,
        'pushNotifications': false,
        'showNotifications': false
    },

    validation: {
        username: function (input) {
            if (input.value === '') {
                input.setCustomValidity('Bitte geben Sie Ihren Benutzernamen an.')
            } else {
                input.setCustomValidity('')
            }
        },
        password: function (input) {
            if (input.value === '') {
                input.setCustomValidity('Bitte geben Sie Ihr Passwort an.')
            } else {
                input.setCustomValidity('')
            }
        }
    },

    actions: {
        ChooseRoom:       function () {
            $('.link[href="/chooseRoom"]').trigger('click');
        },
        UpdateController: function (status, context) {
            if (status === false) {return;}

            if (typeof context === "undefined") {context = "body";}

            for (var i in controller.commands) {
                if (controller.commands.hasOwnProperty(i)) {
                    var commandId = controller.commands[i];
                    var $button   = $(context).find('[data-button=' + i + ']');
                    if (status[commandId]) {
                        $button.addClass('btn-raised');
                    } else {
                        $button.removeClass('btn-raised');
                    }
                } else {
                    console.error('Can not update controller! Commands are not loaded properly.');
                }
            }
        },
        updateNavigation: function (room) {
            if (typeof room === 'undefined' || room === null) {
                room = '';
            } else {
                room = ' ' + room;
            }
            $('.navbar-brand.link').html(
                '<i class="material-icons">memory</i> Remote Steuerung' + room
            );
        }
    },

    vibrate: function (delay, length) {
        if ('vibrate' in navigator) {
            delay  = delay || 0;
            length = length || 500;
            setTimeout(function () {
                navigator.vibrate(length);
            }, delay);
        }
    },

    init: function () {
        $.material.init();
        $('body').tooltip({'trigger': 'hover', 'selector': '[data-toggle="tooltip"]'});
        if (typeof observe !== 'undefined') {
            var origin = window.location.origin;
            if (window.location.href === origin + Routing.generate('admin_observe_route')) {
                observe.init();
            } else {
                observe.close();
            }
        }

        // Test passive events.
        try {
            var options = Object.defineProperty({}, 'passive', {
                get: function () {
                    app.supports.passiveEvents = true;
                }
            });

            window.addEventListener('test', null, options);
        } catch (e) {
            // The default value is already `false`.
        }

        app.boot();
        app.applyFancyTransitions();
        window.addEventListener('popstate', function (e) {
            app.unboot();
            if (e.state === null) {
                window.location.reload();
            }
            $('main').html(e.state);
            app.boot();
        });

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function () {
                navigator.serviceWorker.register(Routing.generate('service_worker_route')).then(function (registration) {
                    // Registration was successful
                    console.log('ServiceWorker registration successful with scope: ', registration.scope);
                }, function (err) {
                    // registration failed :(
                    console.log('ServiceWorker registration failed: ', err);
                });
            });
            if ('PushManager' in window) {
                this.supports.pushNotifications = true;
            } else {
                console.warn('PushNotifications sind nicht unterstützt.');
            }
            if ('showNotification' in ServiceWorkerRegistration.prototype) {
                this.supports.showNotifications = true;
            }
        }

    },

    updateNotification: function (currentState) {
        if (!app.supports.showNotifications) {
            console.warn('Notifications aren\'t supported.');
            return;
        }
        if (!app.supports.pushNotifications) {
            console.warn('Push messaging isn\'t supported.');
            return;
        }

        app.messaging.requestPermission()
            .then(function () {
                console.info('Notification permission granted.');
            })
            .catch(function (err) {
                console.warn('Unable to get permission to notify.', err);
            });

        app.messaging.getToken()
            .then(function (currentToken) {
                if (currentToken) {
                    $.ajax(
                        {
                            'url':    Routing.generate('notification_settings_route', {
                                'state': + !currentState, // Revert true/false and cast to integer
                                'token': currentToken
                            }),
                            'method': 'post',
                            'beforeSend': function () {
                                app.load();
                            },
                            'success': function (newState) {
                                var value = newState.state ? 'on' : 'off';
                                $('#notificationsToggle').text(newState['text']).val(value);
                            },
                            'complete': function() {
                                // Add delay so this function works... It's stupid.
                                setTimeout(function() {
                                    app.stopLoad();
                                }, 500);
                            }
                        }
                    );
                } else {
                    console.info('No Instance ID token available. Request permission to generate one.');
                }
            })
            .catch(function (err) {
                console.info('An error occurred while retrieving token. ', err);
            });
    },

    load: function () {
        if (app.isLoading) {
            return;
        }
        app.isLoading = true;
        $.snackbar({'timeout': 0, 'style': 'snack', 'content': 'Bitte warten...'});
    },

    stopLoad: function () {
        if (app.isLoading) {
            app.isLoading = false;
            $('.snackbar-opened').removeClass('snackbar-opened');
        }
    },

    /**
     * Initiate nice slide-in animation for material-design-paper-like look-and-feel.
     */
    boot: function () {
        app.updateEnvironment();
        app.setEventListeners();
        $('.bootable.boot-start').each(function (index) {
            var that = this;
            setTimeout(function () {
                $(that).addClass('boot').addClass('booted');
                $(that).removeClass('boot-start');
            }, 200 * index);
        });

        $('.slideDown').delay(1200).slideDown(600);
    },

    unboot: function (delay) {
        if (typeof delay === 'undefined') {
            delay = 0
        }
        setTimeout(function () {
            $('.booted').removeClass('boot');
            $('.open').removeClass('open');
        }, delay);
    },

    applyFancyTransitions: function () {
        $('body').on('click', '.link', function () {
            var address = this.href;
            if (address === window.location.href) {
                return false;
            }
            if (address.indexOf('/controller') === -1) {
                // When not visiting /controller, update Navigation.
                app.actions.updateNavigation();
            }
            app.unboot();
            $('.navbar-collapse.collapse').slideUp(200);
            $.ajax(
                {
                    'url':      address,
                    'method':   'get',
                    'complete': function (data) {
                        $('main').html(data.responseText);
                        history.pushState(data.responseText, 'Remote-Steuerung', address);
                        app.init(false);
                        if (address.endsWith(Routing.generate('logout_route'))) {
                            window.location.reload();
                        }
                    }
                }
            );

            return false;
        })
    },

    help: function () {
        $('#help').modal();
    },

    setEventListeners: function () {
        var $body = $('body');
        app.removeEventListeners();
        if (typeof controller !== 'undefined') {
            controller.setEventListeners();
        }
        $('.dropdown-menu').find('a').on('click', function () {
            $(this).closest('.dropdown.open').removeClass('open');
        });
        $body.on('click', '[data-toggle=snackbar]', function (e) {
            e.preventDefault();
            $('.snackbar-opened').removeClass('snackbar-opened');
            setTimeout(function () {
                e.isDefaultPrevented = function () {
                    return false;
                };
            }, 1500);

        });
        $body.on('click', '.navbar-toggle', function () {
            $('.navbar-collapse.collapse').slideToggle(300);
        });
        $body.on('click', '.password-show', function (e) {
            e.preventDefault();
            var $password = $(this).parent().siblings('.password');
            if ($password.attr('type') === 'password') {
                $password.attr('type', 'text');
                $(this).html('<i class="material-icons">visibility_off</i>');
            } else {
                $password.attr('type', 'password');
                $(this).html('<i class="material-icons">visibility</i>');
            }
        });

        $body.on('click', '.form-button', function () {
            if ($(this).closest('form')[0].checkValidity()) {
                app.load();
                app.unboot(500);
            }
        });

        $body.on('click', '#fullscreenToggle', function () {
            app.requestFullScreen();
        });

        $body.on(
            'click',
            '.filters .panel-heading, .status-api .panel-heading',
            function () {
                var that = this;
                var $sb  = $(this).siblings('.panel-body');
                if ($sb.is(':visible')) {
                    $sb.slideUp(400, function () {
                        $(that).addClass('closed');
                    });
                } else {
                    $(this).removeClass('closed');
                    $sb.slideDown();
                }
            });

        $body.on('submit', '.filters .panel-body .form-horizontal', function () {
            $(this).parent().siblings('.panel-heading').trigger('click');
            var form = this;
            $.ajax(
                {
                    'url':      Routing.generate('admin_filter_logs_route'),
                    'method':   'post',
                    'data':     $(form).serialize(),
                    'complete': function (data) {
                        var logContainer = $('.log-container');
                        logContainer.fadeOut(400, function () {
                            $(this).children().remove();
                            $(this).append(data.responseText).hide().fadeIn();
                        });
                    }
                }
            );
        });

        $body.on('submit', '.status-api .panel-body .form-horizontal', function () {
            $(this).parent().siblings('.panel-heading').trigger('click');
            var form             = this;
            var $statusContainer = $('.status-container');
            $.ajax(
                {
                    'url':        Routing.generate(
                        'admin_status_api_route',
                        {'building': $('#building_status').val()}
                    ),
                    'method':     'post',
                    'data':       $(form).serialize(),
                    'beforeSend': function () {
                        app.load();
                        $statusContainer.fadeOut(400, function () {
                            $(this).children().remove();
                        });
                    },
                    'complete':   function (data) {
                        app.stopLoad();
                        $statusContainer.append(data.responseText).hide().fadeIn();
                        app.boot();
                    }
                }
            );
        });

        $body.on('click', '#notificationsToggle', function () {
            var state = $(this).val() === 'on';
            app.updateNotification(state);
        });
    },

    removeEventListeners: function () {
        var $body     = $('body');
        var $dropdown = $('.dropdown');
        if (typeof controller !== 'undefined') {
            controller.removeEventListeners();
        }
        $dropdown.unbind('show.bs.dropdown');
        $dropdown.unbind('hide.bs.dropdown');
        $body.unbind('click');
        $body.unbind('mousedown touchstart');
        $body.unbind('submit');
        $('.dropdown-menu').unbind('click');
    },

    requestFullScreen: function () {
        var doc   = window.document;
        var docEl = doc.documentElement;

        var requestFullScreen = docEl.requestFullscreen ||
            docEl.mozRequestFullScreen ||
            docEl.webkitRequestFullScreen ||
            docEl.msRequestFullscreen;
        var cancelFullScreen  = doc.exitFullscreen ||
            doc.mozCancelFullScreen ||
            doc.webkitExitFullscreen ||
            doc.msExitFullscreen;

        if (
            !doc.fullscreenElement &&
            !doc.mozFullScreenElement &&
            !doc.webkitFullscreenElement &&
            !doc.msFullscreenElement
        ) {
            requestFullScreen.call(docEl);
        } else {
            cancelFullScreen.call(doc);
        }
    },

    updateEnvironment: function () {
        var calculatedHeight         = (screen.availHeight || screen.height - 30);
        app.environment.isFullscreen = calculatedHeight <= window.innerHeight;
        var $fullscreenToggle        = $('#fullscreenToggle');
        if (app.environment.isFullscreen && $fullscreenToggle.length !== 0) {
            $fullscreenToggle.prop('checked', true);
        }
    }
};
