!! README.md is a work in progress !!

#Dependencies:

- d3.js: Webprofiler module requires D3 library to proper render data.
  Download https://github.com/mbostock/d3 into /libraries/d3/d3.min.js
  
- highlight.js: Webprofiler module requires highlight library to syntax highlight collected queries.
  Download http://highlightjs.org into /libraries/highlight

#IDE link

Every class name discovered while profiling (controller class, event class) are linked to an url for directly open in
an IDE, you can configure the url of those link based on the IDE you are using:

- Sublime text (2 and 3): see https://github.com/dhoulb/subl for Mac OS X
- Textmate: should be supported by default, use txmt://open?url=file://@file&line=@line as link
