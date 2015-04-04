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
      new Drupal.webprofiler.routers.CollectorsRouter({el: $('#collectors')});
      Backbone.history.start({
        pushState: false
      });
    }
  };

}(jQuery, Drupal, Backbone));
