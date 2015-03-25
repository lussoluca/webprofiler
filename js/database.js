/**
 * @file
 * Database panel app.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.behaviors.webprofiler_database = {
    attach: function (context) {
      $(context).find('.wp-query-info-button').once('wp_query_info_button').each(function () {
        $(this).on('click', function (event) {
          $(this).toggleClass('open');
          $('.wp-query-info', $(this).parent()).toggle();
        });
      });

      $(context).find('.wp-query-explain-button').once('wp_query_explain_button').each(function () {
        $(this).on('click', function (event) {
          var position = $(this).attr('data-wp-query-position'), wrapper = $(this).parent();
          var url = Drupal.url('admin/reports/profiler/database_explain/' + drupalSettings.webprofiler.token + '/' + position);

          $.getJSON(url, function (data) {
            _.templateSettings.variable = "wp";

            var template = _.template(
              $("#wp-query-explain-template").html()
            );

            wrapper.html(template(data));
          });
        });
      });

      $(context).find('#edit-query-filter').once('query_filter').each(function () {
        $(this).on('click', function (event) {
          var queryType = $('#edit-query-type').val(),
            queryCaller = $('#edit-query-caller').val();

          $('.wp-query').show();

          if (queryType) {
            $('.wp-query:not([data-wp-query-type^="' + queryType + '"])').hide();
          }

          if (queryCaller) {
            $('.wp-query:not([data-wp-query-caller^="' + queryCaller + '"])').hide();
          }

          return false;
        });
      });

      if (typeof hljs != "undefined") {
        hljs.initHighlightingOnLoad();
      }
    }
  }
})
(jQuery, Drupal, drupalSettings);
