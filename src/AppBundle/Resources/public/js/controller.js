/**
 * @author Dominik Müller (Ashura) ashura@aimei.ch
 */

"use strict";

var controller = {

    /**
     * Mapping of ZuluInterface
     */
    commands: {
        'cmd_streamNotebook':     1,
        'cmd_streamExternal':     2,
        'cmd_freezeProjector':    3,
        'cmd_blankProjector':     4,
        'cmd_soundNotebook':      5,
        'cmd_soundExternal':      6,
        'cmd_quieter':            7,
        'cmd_louder':             8,
        'cmd_freezeVisualizer':   9,
        'cmd_blankVisualizer':    10,
        'cmd_shutdownVisualizer': 11,
        'cmd_shutdownAll':        12
    },

    view: null,

    views: null,

    startTouchX: null,

    currentTouchX: null,

    touchTrigger: false,

    releaseActions: null,

    // Button that requests data from the user.
    requestingButton: null,

    // Element that triggers requirement modal
    requestingElements: [],

    // Daten vom requirement modal nachgefragt wird.
    requestedData: null,

    nextView: function () {
        var length   = controller.views.length;
        var pos      = controller.views.map(function (e) {
            return e.id;
        }).indexOf(controller.view.id);
        var targetId = pos + 1;
        if (targetId === length) { // position starts with 0. If the position is equal to the length, the view should be index 0.
            targetId = 0;
        }
        controller.view = controller.views[targetId];
        controller.updateView();
    },

    previousView: function () {
        var length   = controller.views.length;
        var pos      = controller.views.map(function (e) {
            return e.id;
        }).indexOf(controller.view.id);
        var targetId = pos - 1;
        if (pos === 0) {
            targetId = length - 1;
        }
        controller.view = controller.views[targetId];
        controller.updateView();
    },

    updateView: function () {
        var url  = Routing.generate('controller_route', {'view': controller.view.id});
        var link = document.createElement('a');
        link.classList.add('link');
        link.classList.add('hidden');
        link.setAttribute('href', url);
        $('main').append(link);
        $(link).trigger('click');
        link.parentNode.removeChild(link);
    },

    setEventListeners: function () {
        var $body = $('body');
        $('.btn-controller').on('click', function () {
            var command = this.dataset.button;
            if (app.isLoading) {
                return;
            }

            controller.requestingButton = null;
            var obj; // Temp value
            if (!controller.requestedData) {
                for (var i in this.dataset) { // Handling von requirement Daten.
                    if (i.startsWith('reqVariable')) {
                        var name = i.replace('reqVariable', '');
                        obj      = { // Objekt zur Modal, generierung wird erstellt
                            'name':  name.toLowerCase(),
                            'label': this.dataset['reqLabel' + name],
                            'type':  this.dataset['reqType' + name]
                        };
                        controller.requestingElements.push(obj);
                    }
                }
                // Lade Modal mit den vorbereiteten Objekten
                if (controller.requestingElements.length > 0) {
                    controller.requestingButton = this;
                    var $requirements           = $('#requirements');
                    for (i in controller.requestingElements) {
                        obj         = controller.requestingElements[i];
                        var element = '<div class="form-group">' +
                            '<label class="control-label" style="width: 100%;">' +
                            obj.label +
                            '<input class="form-control" required type="' +
                            obj.type + '" name="' + obj.name + '"></label>' +
                            '</div>';
                        $requirements.find('.fields').append(element);
                    }
                    $requirements.modal();
                    return;
                }
            }


            $.ajax(
                {
                    'url':        Routing.generate(
                        'send_commands_route',
                        {'command': command}
                    ),
                    'method':     'POST',
                    'beforeSend': function () {
                        app.load();
                    },
                    'data':       controller.requestedData,
                    'dataType':   'json',
                    'complete':   function (data) {
                        app.stopLoad();
                        data = JSON.parse(data.responseText);
                        if (typeof data.action !== 'undefined') {
                            if (typeof data.data !== 'undefined') {
                                app.actions[data.action](data.data);
                            } else {
                                app.actions[data.action]();
                            }
                        }
                        controller.requestedData      = null;
                        controller.requestingElements = [];
                    }
                }
            );
        });

        $body.on('click', '#requirementsHelper', function () {
            var $requirements = $('#requirements');
            var $form         = $requirements.find('.fields');
            if (!$form[0].checkValidity()) {
                return false;
            }
            controller.requestedData      = $form.serializeArray();
            controller.requestingElements = [];
            $form.children().remove();
            $(controller.requestingButton).click();
            $requirements.modal('hide');

            return false;
        });

        $('#requirements').on('hidden.bs.modal', function () {
            controller.requestingElements = [];
            $('#requirements').find('.fields').children().remove();
        });

        $body.on('touchstart', '#controller-wrapper', function (e) {
            controller.startTouchX = e.originalEvent.touches[0].pageX;
            controller.view        = $('#controller-wrapper').data('view');
        });

        $body.on('touchmove', '#controller-wrapper', app.supports.passiveEvents
            ? {passive: true} : false, function (e) {
            e.preventDefault();
            controller.currentTouchX = e.originalEvent.touches[0].pageX;
            if (controller.startTouchX + 40 < controller.currentTouchX) {
                document.querySelector('.direction-hint.left').classList.add('view');
                controller.releaseAction = controller.previousView;
            } else if (controller.startTouchX - 40 > controller.currentTouchX) {
                document.querySelector('.direction-hint.right').classList.add('view');
                controller.releaseAction = controller.nextView;
            } else {
                var view = document.querySelector('.direction-hint.view');
                if (view) {
                    view.classList.remove('view');
                }
                controller.releaseAction = null;
            }
        });

        $body.on('touchend', '#controller-wrapper', function (e) {
            if (e.originalEvent.touches.length === 0) { // Kein Touchpunkt mehr - Losgelassen
                controller.startTouchX   = null;
                controller.currentTouchX = null;
                if (controller.releaseAction) {
                    controller.releaseAction();
                    controller.releaseAction = null;
                }
            }
        });

        $body.on('change', '#building', function () {
            var $selected = $(this).find('option:selected');
            var $room     = $('#room');
            var value     = $selected.attr('value');
            var $form     = $room.parent().parent();
            $('.btn.btn-primary.form-submit').attr('disabled', 'disabled');

            $room.children().remove();
            $room.attr('disabled', 'disabled');
            $room.append('<option>Auswählen</option>');

            if (typeof value !== 'undefined') {
                setTimeout(function () {
                    $form.slideDown();
                }, 500);

                $.ajax(
                    {
                        'url':      Routing.generate('get_zulus_route', {'building': value}),
                        'method':   'GET',
                        'complete': function (data) {
                            var zulus = data.responseJSON;
                            for (var i in zulus) {
                                var zulu = zulus[i];
                                $room.append(
                                    '<option value="' + zulu.room + '">' +
                                    zulu.room +
                                    '</option>'
                                );
                            }
                            $room.removeAttr('disabled');
                        }
                    }
                );
            } else {
                $form.slideUp();
            }
        });
        $body.on('change', '#room', function () {
            var $selected = $(this).find('option:selected');
            var $button   = $('.btn.btn-primary.form-submit');
            var value     = $selected.attr('value');

            if (typeof value !== 'undefined') {
                $button.removeAttr('disabled');
            } else {
                $button.attr('disabled', 'disabled');
            }
        });
    },

    removeEventListeners: function () {
        $('.btn-controller').unbind('click');
        var $body = $('body');
        $body.unbind('change');
        $body.unbind('touchstart');
        $body.unbind('touchmove');
        $body.unbind('touchend');
    }
};