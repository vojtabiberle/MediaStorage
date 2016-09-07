$(document).ready(function(){
    $('form').find("[data-mediastorage-filechoicer-init]").each(function() {
        var $ms = $(this);
        var data = $ms.data('mediastorage-filechoicer-init');
        var name = data.name;

        $(this).find('.remover').bind('click', function() {
            var removed = $($ms.find('[data-mediastorage-role="removed-template"]')).clone();
            removed.removeAttr('data-role');
            var uid = $(this).data('mediastorage-uid');
            removed.val(uid);
            $('div[id="' + uid + '"]').replaceWith(removed);
        });

        $(this).find('.setPrimary').bind('click', function() {
            var uid = $(this).data('mediastorage-uid');
            $ms.find('[data-mediastorage-role="primary-uid"]').val(uid);
        });

        $(this).find('.unsetPrimary').bind('click', function() {
            var uid = $(this).data('mediastorage-uid');
            var current = $ms.find('[data-mediastorage-role="primary-uid"]').val();
            if (uid == current) {
                $ms.find('[data-mediastorage-role="primary-uid"]').val(false);
            }
        });
    });

    var bLazy = new Blazy();
});