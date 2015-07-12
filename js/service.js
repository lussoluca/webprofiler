/**
 * @file
 * Service panel app.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.behaviors.webprofiler_service = {
    attach: function (context) {
      $('#edit-service-filter').once('edit-service-filter').each(function () {
        $(this).on('click', function (event) {
          var sid = $('#edit-sid').val(),
            initialized = $('#edit-initialized').val(),
            clazz = $('#edit-class').val(),
            tags = $('#edit-tags').val();

          $('table#wp-service-table tr').show();

          if(sid) {
            $('table#wp-service-table tbody tr:not([data-wp-service-id^="' + sid + '"])').hide();
          }

          if(clazz) {
            $('table#wp-service-table tbody tr:not([data-wp-service-class^="' + clazz + '"])').hide();
          }

          if(tags) {
            $('table#wp-service-table tbody tr:not([data-wp-service-tags^="' + tags + '"])').hide();
          }

          if(initialized && initialized != -1) {
            $('table#wp-service-table tbody tr:not([data-wp-service-initialized^="' + initialized + '"])').hide();
          }

          return false;
        });
      });

    }
  }
})
(jQuery, Drupal, drupalSettings);
