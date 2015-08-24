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
        value,
        selector = '',
        unselected = [],
        filter = [],

        livefilter = function (e) {

          el = $(e).attr('id').replace('edit-', '');
          value = $(e).val();

          filter[el] = value.replace('/', '\/');
          selector = '';
          unselected = [];
          for (var key in filter) {
            if (filter[key].length > 2 || filter[key] !== '') {
              selector = selector + '[data-wp-' + key + ' *= ' + filter[key] + ']';
              unselected.push('[data-wp-' + key + ']:not([data-wp-' + key + ' *= ' + filter[key] + '])');
            }
            else {
              selector = selector + '[data-wp-' + key + ']';
            }
          }

          for (var elz in unselected) {
            $(unselected[elz]).addClass('is--hidden');
          }

          $(selector).removeClass('is--hidden');

          console.log(filter);
          console.log(selector);
          console.log(unselected);

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
    }
  };

}(jQuery, Drupal, Backbone));
