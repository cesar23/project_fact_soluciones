/**
 *
 * SupportTicketsDetail
 *
 * Support.TicketsDetail page content scripts. Initialized from scripts.js file.
 *
 *
 */

class SupportTicketsDetail {
  constructor() {
    // Initialization of the page plugins
    if (typeof Quill !== 'undefined' && typeof Active !== 'undefined') {
      this._initQuill();
    } else {
      console.error('[CS] Quill or Quill.Active Module is undefined.');
    }

    if (jQuery().barrating) {
      this._initBarrating();
    } else {
      console.error('[CS] jQuery().barrating is undefined.');
    }
  }

  //Quill Editor initialize
  _initQuill() {
    Quill.register('modules/active', Active);
    const editorModules = {
      toolbar: [
        ['bold', 'italic', 'underline', 'strike'],
        [{header: [1, 2, 3, 4, 5, 6, false]}],
        [{list: 'ordered'}, {list: 'bullet'}],
        [{size: ['small', false, 'large', 'huge']}],
        [{color: []}],
        [{align: []}],
      ],
      active: {},
    };
    if (document.getElementById('quillEditorFilledEmail')) {
      new Quill('#quillEditorFilledEmail', {
        modules: editorModules,
        theme: 'bubble',
        placeholder: 'Answer',
      });
    }
  }

  // Rating initialize
  _initBarrating() {
    jQuery('.rating').each(function () {
      const current = jQuery(this).data('initialRating');
      const readonly = jQuery(this).data('readonly');
      const showSelectedRating = jQuery(this).data('showSelectedRating');
      const showValues = jQuery(this).data('showValues');
      jQuery(this).barrating({
        initialRating: current,
        readonly: readonly,
        showValues: showValues,
        showSelectedRating: showSelectedRating,
        onSelect: function (value, text) {},
        onClear: function (value, text) {},
      });
    });
  }
}
