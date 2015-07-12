(function (drupalSettings) {

  Drupal.webprofiler.helpers = (function () {

    "use strict";

    var abbr = function (clazz) {
        if(!clazz) {
         return null;
        }

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
        if(!file) {
          return null;
        }

        line = line || 0;

        return drupalSettings.webprofiler.idelink.replace("@file", file).replace("@line", line);
      },

      classLink = function (data) {
        var link = ideLink(data['file'], data['line']), clazz = abbr(data['class']), method = data['method'], output = '';

        output = clazz;
        if(method) {
          output += '::' + method;
        }

        if(link) {
          output = '<a href="' + link + '">' + output + '</a>';
        }

        return output;
      }

    return {
      classLink: classLink
    }

  })();

}(drupalSettings));
