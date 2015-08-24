/**
 * @file
 * Database panel app.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.behaviors.webprofiler_database = {
    attach: function (context) {
      $(context).find('.wp-query-explain-button').once('wp_query_explain_button').each(function () {
        $(this).on('click', function () {
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

      if (typeof hljs != "undefined") {
        $('code.sql').each(function(i, block) {
          hljs.highlightBlock(block);
        });
      }
    }
  }
})
(jQuery, Drupal, drupalSettings);
