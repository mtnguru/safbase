/**
 * @file
 * Claro's enhancement for autocomplete form element.
 */

(($, Drupal) => {
  Drupal.behaviors.au_claroAutoCompete = {
    attach(context) {
      $(context)
        .find('input.form-autocomplete')
        .once('au_claroAutoComplete')
        .each((index, value) => {
          const $input = $(value);
          const timeout = 400;
          let classRemoveTimeout;
          const classRemove = $autoCompleteElem => {
            $autoCompleteElem.removeClass('is-autocompleting');
          };

          $input.on('input autocompletesearch autocompleteresponses', event => {
            if (event && event.type && event.type === 'autocompletesearch') {
              $(event.target).addClass('is-autocompleting');
            }
            clearTimeout(classRemoveTimeout);
            classRemoveTimeout = setTimeout(
              classRemove,
              timeout,
              $(event.target),
            );
          });
        });
    },
  };
})(jQuery, Drupal);
