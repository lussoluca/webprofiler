(function ($, Drupal, Backbone) {

    "use strict";

    /**
     * Define namespaces.
     */
    Drupal.webprofiler = {
        views: {},
        models: {},
        collectors: {},
        routers: {}
    };

    Drupal.behaviors.webprofiler = {
        attach: function (context) {
            var el,
                elz,
                key,
                sel,
                value,
                select,
                selector,
                unselected,
                filter = [],

                livefilter = function (e) {

                    el = $(e).attr('id').replace('edit-', '');
                    value = $(e).val();
                    filter[el] = value.replace('/', '\/');
                    selector = [];
                    unselected = [];

                    for (key in filter) {
                        if (filter[key].length > 2) {
                            select = filter[key].split(' ').filter(Boolean);
                            for (sel in select) {
                                selector.push('[data-wp-' + key + ' *= ' + select[sel] + ']');
                                unselected.push('[data-wp-' + key + ']:not([data-wp-' + key + ' *= ' + select[sel] + '])');
                            }
                        }
                        else {
                            selector.push('[data-wp-' + key + ']');
                        }
                    }

                    for (elz in unselected) {
                        $(unselected[elz]).addClass('is--hidden');
                    }
                    $(selector.join('')).removeClass('is--hidden');

                },

                clipboard = function (e, t) {
                    var clip = e.parent().find(t).get(0),
                        prompt = function () {
                            window.prompt("Copy to clipboard: Ctrl+C or cmd+C, Enter", clip.textContent)
                        };

                    if (navigator.userAgent.indexOf('Safari') != -1 && navigator.userAgent.indexOf('Chrome') == -1) {
                        prompt();
                    } else {
                        var temp = $('<textarea readonly class="js--textarea is--hidden" >' + clip.innerText + '</textarea>');
                            temp.appendTo(e.parent()).select();
                        try {
                            var successful = document.execCommand('copy');

                        } catch (err) {
                            prompt();
                        }
                        $('.js--textarea').remove();
                    }

                };


            $(context).find('#collectors').once('webprofiler').each(function () {
                new Drupal.webprofiler.routers.CollectorsRouter({el: $('#collectors')});
                Backbone.history.start({
                    pushState: false
                });
            });

            $(context).find('.js--live-filter').each(function () {
                $(this).on('keyup', function () {
                    livefilter($(this));
                });
                $(this).on('change', function () {
                    livefilter($(this));
                });
            });

            $(context).find('.js--panel-toggle').once('js--panel-toggle').each(function () {
                $(this).on('click', function () {
                    $(this).parent().parent().toggleClass('is--open');
                });
            });

            $(context).find('.js--clipboard-trigger').once('js--clipboard-trigger').each(function () {
                $(this).on('click', function () {
                        console.log('click');
                        clipboard($(this), '.js--clipboard-target')
                    }
                );
            });
        }
    };

}(jQuery, Drupal, Backbone));
