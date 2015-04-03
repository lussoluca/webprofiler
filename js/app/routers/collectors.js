(function ($, Drupal, drupalSettings, Backbone) {

  "use strict";

  var collectors = new Drupal.webprofiler.collectors.Collectors(drupalSettings.webprofiler.collectors);

  Drupal.webprofiler.routers.CollectorsRouter = Backbone.Router.extend({
    routes: {
      ':id': 'selectCollector'
    },
    selectCollector: function (id) {
      var collectors = this.collectors, layout = this.layout;

      collectors.resetSelected();
      collectors.selectByID(id);

      var deferred = collectors.get(id).fetch();
      deferred.done(function () {
        layout.setDetails(collectors.get(id));
      });
    },
    initialize: function (options) {
      this.collectors = collectors;
      this.layout = Drupal.webprofiler.views.Layout.getInstance({
        el: options.el,
        router: this
      });
      this.layout.render();
    }
  });

}(jQuery, Drupal, drupalSettings, Backbone));
