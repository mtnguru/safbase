/**
 * @file - az_maestro_base.js
 *
 * File that generates nuclets  proton, helium, lithium, helium - 1, 4, 7, 11
 */

(function ($) {

  Array.prototype.contains = function ( needle ) {
    for (var i in this) {
      if (this[i] == needle) return true;
    }
    return false;
  };

  Drupal.az_maestro = {};

  Drupal.az_maestro.baseC = function () {

    Drupal.AjaxCommands.prototype.loadYmlCommand = function(ajax, response, status) {
      Drupal.atomizer[response.component].loadYml(response);
    };

    Drupal.AjaxCommands.prototype.saveYmlCommand = function(ajax, response, status) {
      Drupal.atomizer[response.component].saveYml(response);
    };

    Drupal.AjaxCommands.prototype.renderNodeCommand = function(ajax, response, status) {
      Drupal.atomizer[response.component].renderNode(response);
    };

    var doAjax = function doAjax (url, data, successCallback, errorCallback) {
      $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify(data),
        contentType: "application/json; charset=utf-8",
        processData: false,
        success: function (response) {
          if (Array.isArray(response) && response.length > 0) {
            if (response[0].data && response[0].data.message) {
              alert(response[0].data.message);
            }
            if (successCallback) successCallback(response);
          } else {
            if (errorCallback) {
              errorCallback(response);
            } else if (successCallback) {
              successCallback(response);
            }
          }
          return false;
        },
        error: function (response) {
          alert('atomizer_base doAjax: ' + response.responseText);
          if (errorCallback) {
            errorCallback(response);
          } else if (successCallback) {
            successCallback(response);
          }
        }
      });
    };

    return {
      doAjax: doAjax,
    };

  };

})(jQuery);
