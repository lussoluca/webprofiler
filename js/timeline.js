/**
 * @file
 * Timeline panel app.
 */
(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.behaviors.webprofiler_timeline = {
    attach: function (context) {
      if (typeof d3 != "undefined") {

        // data
        var events = drupalSettings.webprofiler.time.events;
        var lanes = [];
        var items = [];
        for (var j = 0; j < events.length; j++) {
          lanes.push(events[j].name);
          for (var k = 0; k < events[j].periods.length; k++) {
            items.push({
              "lane": j,
              "category": events[j].category,
              "start": events[j].periods[k].start,
              "end": events[j].periods[k].end
            });
          }
        }
        var laneLength = lanes.length,
          timeBegin = 0,
          timeEnd = drupalSettings.webprofiler.time.endtime;

        var m = [20, 5, 15, 261], //top right bottom left
          w = 1062 - m[1] - m[3],
          h = 800 - m[0] - m[2],
          miniHeight = laneLength * 12 + 50,
          mainHeight = h - miniHeight - 50;

        // scales
        var x = d3.scale.linear()
          .domain([timeBegin, timeEnd])
          .range([0, w]);
        var x1 = d3.scale.linear()
          .range([0, w]);
        var y1 = d3.scale.linear()
          .domain([0, laneLength])
          .range([0, mainHeight]);
        var y2 = d3.scale.linear()
          .domain([0, laneLength])
          .range([0, miniHeight]);

        // timeline
        var timeline = d3.select("#timeline")
          .append("svg")
          .attr("width", w + m[1] + m[3])
          .attr("height", h + m[0] + m[2])
          .attr("class", "timeline");

        var mini = timeline.append("g")
          .attr("transform", "translate(" + m[3] + ",0)")
          .attr("width", w)
          .attr("height", miniHeight)
          .attr("class", "mini");

        mini.append("g").selectAll(".laneLines")
          .data(items)
          .enter().append("line")
          .attr("x1", 0)
          .attr("y1", function (d) {
            return y2(d.lane);
          })
          .attr("x2", w + 5)
          .attr("y2", function (d) {
            return y2(d.lane);
          })
          .attr("stroke", "lightgray");

        mini.append("g").selectAll(".laneText")
          .data(lanes)
          .enter().append("text")
          .text(function (d) {
            return d;// + ' ~10 ms/~ 21.3 MB';
          })
          .attr("x", -m[1])
          .attr("y", function (d, i) {
            return y2(i + .5);
          })
          .attr("dy", ".5ex")
          .attr("text-anchor", "end")
          .attr("class", "laneText");

        mini.append("g").selectAll("miniItems")
          .data(items)
          .enter().append("rect")
          .attr("class", function (d) {
            return d.category;
          })
          .attr("x", function (d) {
            return x(d.start);
          })
          .attr("y", function (d) {
            return y2(d.lane + .5) - 5;
          })
          .attr("width", function (d) {
            return x(d.end - d.start + 5);
          })
          .attr("height", 10);
      }
    }
  }

})(jQuery, Drupal, drupalSettings);
