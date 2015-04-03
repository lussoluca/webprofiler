(function ($, Drupal, Backbone) {

  "use strict";

  Drupal.webprofiler.views.DetailsView = Backbone.View.extend({
    el: '#details',
    render: function () {
      var template = _.template($("script#" + this.model.get('name')).html());

      this.$el.html(template(this.model.toJSON()));
      return this;
    }
  });

}(jQuery, Drupal, Backbone));
