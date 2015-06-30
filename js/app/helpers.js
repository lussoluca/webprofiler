(function (drupalSettings) {

  Drupal.webprofiler.helpers = (function () {

    "use strict";

    var abbr = function (clazz) {
        var parts = clazz.split("\\"), result = [], size = (parts.length - 1);

        _.each(parts, function (item, key) {
          if (key < size) {
            result.push(item.substring(0, 1));
          } else {
            result.push(item);
          }
        });

        return '<abbr title="' + clazz + '">' + result.join("\\") + '</abbr>';
      },

      ideLink = function (file, line) {
        return drupalSettings.webprofiler.idelink.replace("@file", file).replace("@line", line);
      };

    return {
      abbr: abbr,
      ideLink: ideLink
    }

  })();

}(drupalSettings));
