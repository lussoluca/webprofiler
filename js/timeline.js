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
                var data = drupalSettings.webprofiler.time.events;
                /* var lanes = [];
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
                 w = $('#timeline').width(),
                 h = (laneLength * 12.2) - m[0] - m[2],
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
                 .attr("width", w)
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
                 .attr("height", 10);*/


                var parts = [],
                    dataL = data.length,
                    perL,
                    labelW = [],
                    rowW,
                    scalePadding,
                    endTime = parseInt(data[(dataL - 1)].endtime);

                for (var j = 0; j < dataL; j++) {
                    perL = data[j].periods.length;
                    for (var k = 0; k < perL; k++) {
                        parts.push({
                            "lane": j,
                            "category": data[j].category,
                            "memory": data[j].memory,
                            "name": data[j].name,
                            "start": data[j].periods[k].start,
                            "end": data[j].periods[k].end
                        });
                    }
                }

                var tooltipCtrl = function (d, i) {
                    tooltip.html('<span class="tooltip__content">memory usage: ' + d.memory + '</span>');
                    tooltip
                        .style("display", 'block')
                        .style("left", (d3.event.layerX - 87 ) + "px")
                        .style("top", ((d.lane + 1) * 22 ) + "px")
                        .style("opacity", .9);
                };

                var xscale = d3.scale.linear().domain([0, endTime]).range([0, 1000]),
                    container = d3.select('#timeline').append('svg').attr('height', (dataL + 1 ) * 22 + 'px').attr('width', '100%').attr('class', 'timeline__canvas');


                //tooltops
                var tooltip = d3.select('#timeline')
                    .append("div")
                    .attr("class", "tooltip");


// rows
                var rows = d3.select('.timeline__canvas')
                    .append('g')
                    .attr('class', 'timeline__rows')
                    .attr('x', 0)
                    .attr('y', 0)
                    .selectAll('g')
                    .data(data)
                    .enter()
                    .append('rect')
                    .attr('class', 'timeline__row')
                    .attr('x', 0)
                    .attr('y', function (d, i) {
                        return (i * 22);
                    })
                    .attr('height', 22)
                    .attr('width', '100%')
                    .each(function (d) {
                        rowW = this.getBoundingClientRect().width;
                    });

// scale
                var scale = d3.select('.timeline__canvas')
                    .append('g')
                    .attr('class', 'timeline__scale')
                    .attr('id', 'timeline__scale')
                    .attr('x', 0)
                    .attr('y', 0)
                    .selectAll('g')
                    .data(data)
                    .enter()
                    .append('a')
                    .attr('xlink:href', function (d) {
                        return Drupal.webprofiler.helpers.ideLink(d.link);
                    })
                    .attr('class', function (d) {
                        return 'timeline__label ' + d.category
                    })
                    .attr('x', xscale(5))
                    .attr('y', function (d, i) {
                        return (((i + 1) * 22) - 5)
                    });

                scale.append('title')
                    .text(function (d) {
                        return d.name;
                    });

                scale.append('text')
                    .attr('x', xscale(5))
                    .attr('y', function (d, i) {
                        return (((i + 1) * 22) - 5)
                    })
                    .text(function (d) {
                        return Drupal.webprofiler.helpers.shortLink(d.name);
                    })
                    .each(function (d) {
                        labelW.push(this.getBoundingClientRect().width);
                    });

                scalePadding = Math.max.apply(null, labelW) + 10;

                scale.insert('rect', 'title')
                    .attr('x', 0)
                    .attr('y', function (d, i) {
                        return (i * 22)
                    })

                    .attr('height', 22)
                    .attr('stroke', 'transparent')
                    .attr('strokw-width', 1)
                    .attr('width', scalePadding);

// times
                var events = d3.select('.timeline__canvas')
                    .insert('g', '.timeline__scale')
                    .attr('class', 'timeline__parts')
                    .attr('x', 0)
                    .attr('y', 0)
                    .selectAll('g')
                    .data(parts)
                    .enter();

                events.append('rect').attr('class', function (d) {
                    return 'timeline__period--' + d.category
                })
                    .attr('x', function (d) {
                        return parseInt(d.start) + scalePadding
                    })
                    .attr('y', function (d) {
                        return d.lane * 22
                    })
                    .attr('height', 22)
                    .attr('width', function (d) {
                        return Math.max(parseInt(d.end - d.start), 1)
                    });

                events.append('rect').attr('class', function (d) {
                    return 'timeline__period-trigger'
                })
                    .attr('x', function (d) {
                        return parseInt(d.start) + scalePadding - 5
                    })
                    .attr('y', function (d) {
                        return d.lane * 22
                    })
                    .attr('height', 22)
                    .attr('width', function (d) {
                        return Math.max(parseInt(d.end - d.start), 1) + 11
                    })
                    .on("mouseover", function (d, i) {
                        tooltipCtrl(d, i);
                    })
                    .on("mouseout", function (d) {
                        tooltip
                            .style("display", 'none');
                    });

// Draw X-axis grid lines
                var axis = d3.select('.timeline__parts').append('g')
                    .selectAll("line")
                    .data(xscale.ticks(10))
                    .enter()
                    .append("line")
                    .attr("class", "timeline__scale--x")
                    .attr("x1", xscale)
                    .attr("x2", xscale)
                    .attr("y1", 0)
                    .attr("y2", data.length * 22)
                    .attr("transform", "translate( " + scalePadding + " , 0)")
                    .style("stroke", "#ccc");

                var xAxis = d3.svg.axis().scale(xscale).orient('bottom').tickFormat(function (d, i) {
                    return d + ' ms'
                });

                var axe = d3.select('.timeline__parts').append('g')
                    .attr("class", "axis")
                    .attr('transform', 'translate(' + scalePadding + ', ' + dataL * 22 + ')')
                    .call(xAxis);

                var zoom = d3.select('.timeline__canvas')
                    .call(
                    d3.behavior.zoom()
                        .scaleExtent([1, 1])
                        .x(xscale)
                        .on("zoom", function () {
                            var t = d3.event.translate,
                                tx = t[0];

                            tx = tx > 0 ? 0 : tx;
                            tx = tx < -(endTime - scalePadding + 175 ) ? -(endTime - scalePadding + 175 ) : tx;
                            d3.select('.timeline__parts').attr("transform", "translate( " + tx + " , 0)");
                        }));


            }
        }
    }

})(jQuery, Drupal, drupalSettings);
