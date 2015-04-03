(function ($, Drupal, Backbone) {

  "use strict";

  Drupal.webprofiler.views.CollectorView = Backbone.View.extend({
    template: _.template($("script#collector").html()),

    initialize: function () {
      _.bindAll(this, "render");
      this.listenTo(this.model, 'change:selected', this.render);
    },

    // TODO potrei ripristinare questo se chiamo
    // this.router.navigate("#collector/" + this.model.id);
    // events: {
    // 	'click': '_selectCollector'
    //    },

    // _selectCollector: function(ev) {
    //         if (!this.model.get('selected')) {
    //        	this.model.collection.resetSelected();
    //        	this.model.collection.selectByID(this.model.id);
    //      	}
    // },

    render: function () {
      this.$el.html(this.template(this.model.toJSON()));
      this.$el.toggleClass('selected', this.model.get('selected'));
      return this;
    }

  });

}(jQuery, Drupal, Backbone));
