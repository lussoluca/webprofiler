/**
 * @file
 * Database panel app.
 */
(function ($, Drupal, drupalSettings) {

    "use strict";

    Drupal.behaviors.webprofiler_database = {
        attach: function (context) {
            $('.wp-query-info-button').once(function () {
                $(this).on('click', function (event) {
                    $(this).toggleClass('open');
                    $('.wp-query-info', $(this).parent()).toggle();
                });
            });

            $('.wp-query-explain-button').once(function () {
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

            $('#edit-query-filter').once(function () {
                $(this).on('click', function (event) {
                    var queryType = $('#edit-query-type').val(), queryCaller = $('#edit-query-caller').val();

                    if (queryType != '' || queryCaller != '') {
                        $(".wp-query").each(function () {
                            $(this).hide();
                        });

                        if (queryType == '') {
                            $('*[data-wp-query-caller="' + queryCaller + '"]').show();
                        } else if (queryCaller == '') {
                            $('*[data-wp-query-type="' + queryType + '"]').show();
                        } else {
                            $('*[data-wp-query-type="' + queryType + '"][data-wp-query-caller="' + queryCaller + '"]').show();
                        }
                    } else {
                        $(".wp-query").each(function () {
                            $(this).show();
                        });
                    }

                    return false;
                });
            });

            hljs.initHighlightingOnLoad();

            // sqlparser
            var sqlparser_data = drupalSettings.webprofiler.sqlparser.data;
            var sqlparser_graphic = drupalSettings.webprofiler.sqlparser.graphic;

            // graphic
            var array_svg = new Array(sqlparser_graphic.length);
            for (var graphic_number = 0; graphic_number < sqlparser_graphic.length; graphic_number++) {

                var name = sqlparser_graphic[graphic_number];
                array_svg[graphic_number] = d3.select("#graphic-"+name).append("svg").attr("width",350).attr("height",250);
                array_svg[graphic_number].append("g").attr("id",name);

                Donut3D.draw(name, sqlparser_data[graphic_number], 120, 100, 100, 60, 30, 0.4);
            };
        }
    }
})
(jQuery, Drupal, drupalSettings);
