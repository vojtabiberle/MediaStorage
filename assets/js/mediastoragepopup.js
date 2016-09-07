;(function ($) {
    'use strict';

    $.fn.mediaStoragePopup = function (options, callback) {

        if ($.isFunction(options)) {
            callback = options;
            options = null;
        }

        // OPTIONS
        var opt = $.extend({}, $.fn.mediaStoragePopup.defaults, options);

        var $mediaStorage = this
            ;

        // ===============================
        // ========= PUBLIC ==============

        return $mediaStorage.each(function(){
            init(this);
        });

        // ===============================
        // ========= PRIVATE =============
        function init(element) {
            var $element = $(element);
            $element.prepend('<div class="popupContainer"></div>');
            $element.bind('click', function(e){
                e.preventDefault();
                $('.popupContainer').bPopup({
                    loadUrl: ''
                });
            });
        };

        function triggerEvent(func, arg) {
            $.isFunction(func) && func.call($mediaStorage, arg);
        };
    };

    // ===============================
    // ========= DEFAULT =============
    $.fn.mediaStoragePopup.defaults = {
        // events
          onOpen: false
        , onClose: false
        , onUploadFile: false
    };
})(jQuery);