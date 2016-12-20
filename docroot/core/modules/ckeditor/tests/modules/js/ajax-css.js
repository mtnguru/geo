(function (Drupal, ckeditor, editorSettings, $) {

  'use strict';

  Drupal.behaviors.ajaxCssForm = {

    attach: function (context) {
      // Initialize an inline CKEditor on the #edit-inline element if it
      // isn't editable already.
      $(context)
        .find('#edit-inline')
        .not('[contenteditable]')
        .each(function () {
          ckeditor.attachInlineEditor(this, editorSettings.formats.test_format);
        });
    }
  };

})(Drupal, Drupal.editors.ckeditor, drupalSettings.editor, jQuery);
