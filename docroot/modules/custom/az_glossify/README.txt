This module was originally a clone of the glossify module.
The difference is this module preprocesses the file upon storing.
Drupal policy is to do this type of processing upon display.
This creates a performance penalty when rendering an article.

Do the processing in a preprocess module.
Use the topics vocabulary.
Display a short description in the popup
If a long description exists then take them to that page.
Extract a list of topics and assign those topics to the article.
