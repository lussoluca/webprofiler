(function ($, Drupal, Backbone) {

  "use strict";

  Drupal.webprofiler.views.Layout = Backbone.View.extend({
    template: _.template('<div id="overview"><ul></ul></div><div id="details">Choose a collector.</div><div style="clear:both"></div>'),

    /**
     *
     * @returns {Drupal.webprofiler.views.Layout}
     */
    render: function () {
      this.$el.html(this.template());

      if(this.currentDetails) {
        this.currentDetails.setElement(this.$('#details')).render();
      }

      this.overview.setElement(this.$('#overview ul')).render();
      return this;
    },

    /**
     *
     * @param options
     */
    initialize: function (options) {
      options.router.collectors.on('request', this.beginSync);
      options.router.collectors.on('sync', this.finishSync);

      this.overview = new Drupal.webprofiler.views.CollectorsList({
        collection: options.router.collectors,
        router: options.router
      });
    },

    /**
     *
     * @param collector
     */
    setDetails: function (collector) {
      if (this.currentDetails) this.currentDetails.remove();
      this.currentDetails = new Drupal.webprofiler.views.DetailsView({model: collector});
      this.render();
    },

    /**
     *
     */
    beginSync: function () {
      $('.collectors-loading').fadeIn({duration: 100});
    },

    /**
     *
     */
    finishSync: function () {
      $('.collectors-loading').fadeOut({duration: 100});
    }
  });

  var instance;
  Drupal.webprofiler.views.Layout.getInstance = function (options) {
    if (!instance) {
      instance = new Drupal.webprofiler.views.Layout({
        el: options.el,
        router: options.router,
        collection: options.router.collectors
      });
    }

    return instance;
  }

}(jQuery, Drupal, Backbone));
