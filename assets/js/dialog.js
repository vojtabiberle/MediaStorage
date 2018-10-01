var Dialog = Dialog || {};

    Dialog.settings = {
        frameWidth      : null,
        contextMessage  : {
            containerSelector   : null,
            animationDelay      : 300,
            messageSelector     : null,
            closeDelay          : 3000,
            messages            : {
                addedFile : 'Uploading file {{$file}}',
                complete  : '{{$file}} was uploaded successfuly',
                error     : 'Error handled {{$message}}',
                deletedFile  : 'File was deleted successfuly',
            },
        },

        cardTemplate :
        '<div class="card-container">' +
            '<div class="mediastorage-frame" data-toggle="tooltip" title="{{$fileName}}">' +
                '<div class="overlay">' +
                    '<span ' +
                        'data-mediastorage-uid="{{$fileUID}}"' +
                        'data-mediastorage-name="{{$fileName}}"' +
                        'data-mediastorage-url="{{$fileLink}}"' +
                        'data-mediastorage-full-url="{{$fileFullLink}}"' +
                        'class="glyphicon glyphicon-ok"></span>' +
                    '<span ' +
                        'data-mediastorage-remove-url="{{$fileDeleteLink}}"' +
                        'class="glyphicon glyphicon-trash"></span>' +
                '</div>' +
                '<div class="content">' +
                    '<img src="{{$fileLink}}">' +
                '</div>' +
                '<div class="footer">' +
                    '<a href="{{$fileLink}}">{{$fileName}}</a>' +
                '</div>' +
            '</div>'+
        '</div>',

        thumbnailTemplate :
        '<div id="{{$fileUID}}" class="mediastorage-frame image" data-original-title="{{$fileName}}">' +
            '<input data-mediastorage-role="removed-template" type="hidden">' +
            '<div class="content">' +
                '<img src="{{$fileLink}}">' +
            '</div>' +
            '<div class="footer">' +
                '<a href="{{$fileLink}}">{{$fileName}}</a>' +
            '</div>' +
            '<input type="hidden" name="{{$name}}" value="{{$fileUID}}">' +
            '<div class="overlay">' +
                '<a href="#remove" class="remover glyphicon glyphicon-trash" data-mediastorage-uid="{{$fileUID}}"></a>' +
            '</div>' +
        '</div>',
        gallerySelector : null
    };

    Dialog.getStaticAttributes = function() {
        Dialog.settings.frameWidth                       = parseFloat($('.mediastorage-frame').css('width').replace('px', ''));
        Dialog.settings.contextMessage.containerSelector = $('#mediastorage-error-container');
        Dialog.settings.contextMessage.messageSelector   = $('#error-type');
        Dialog.settings.gallerySelector                  = $('#snippet-gridMediaViewControl-gridMediaFiles')

    };

    Dialog.translate = function(sentence, words) {
        var s = sentence;
        $.each(words, function(index, value) {
            s = s.split('{{$'+ value[0] +'}}').join(value[1]);
        });

        return s;
    };

    Dialog.init = function() {
        Dialog.getStaticAttributes();
        Dialog.setFrameMargin();
        Dialog.frameOperations();
        Dialog.uploadForm();
        Dialog.toolTip();
        Dialog.windowResizeEvent();
    };

    Dialog.windowResizeEvent = function() {
        $(window).resize(function() {
            Dialog.setFrameMargin();
        });
    };

    Dialog.toolTip = function(element) {

        if (typeof element === 'undefined') {
            var element = $('.card-container');
        }

        element.find('.mediastorage-frame').tooltip();
    };

    // rewrite
    Dialog.setFrameMargin = function() {
        var galleryWidth, maxFramesInGallery, possibleMargin, margin, frameMargin, doubleMargin;

        // dynamic width, need to check every window resize
        galleryWidth        = parseFloat($('#mediastorage-gallery-list').css('width').replace('px', ''));
        maxFramesInGallery  = galleryWidth / Dialog.settings.frameWidth;

        possibleMargin      = maxFramesInGallery - parseInt(maxFramesInGallery);
        margin              = (Dialog.settings.frameWidth - 4) * possibleMargin;
        frameMargin         = margin / parseInt(maxFramesInGallery);
        doubleMargin        = frameMargin / 2;

        $('.mediastorage-frame').css({
            'margin-left' : doubleMargin + 'px',
            'margin-right' : doubleMargin + 'px',
            'margin-top' : doubleMargin + 'px',
            'margin-bottom' : doubleMargin + 'px'
        });

        // first fourth frames has margin top 0, could be done with css todo
        $('.mediastorage-frame:lt('+ parseInt(maxFramesInGallery) +')').css('margin-top', '0px');
    };

    Dialog.frameOperations = function(element) {

        if (typeof element === 'undefined') {
            var element = $('.card-container');
        }


        $('.mediastorage-frame .overlay .glyphicon.glyphicon-ok').click(function() {
            if($.magnificPopup.instance.currItem.el.parent().hasClass('inline-file-choice')) {
                var container = $.magnificPopup.instance.currItem.el.parent();
                var containerInput = container
                    .find('input[data-mediastorage-role="removed-template"]');

                var inputName = JSON.parse(container.attr('data-mediastorage-filechoicer-init'));

                var card = Dialog.translate(
                    Dialog.settings.thumbnailTemplate,
                    [
                        ['fileName', $(this).attr('data-mediastorage-name')],
                        ['fileLink', $(this).attr('data-mediastorage-url')],
                        ['fileUID', $(this).attr('data-mediastorage-uid')],
                        ['fileFullLink', $(this).attr('data-mediastorage-full-url')],
                        ['name', inputName.name],
                    ]
                );

                card = $(card);

                container.find('div[class="image"]').remove();
                container.find('div[class="warning"]').remove();
                containerInput.remove();
                container.prepend(card);

                card.find('.remover').bind('click', function() {
                    var removed = $(card.find('[data-mediastorage-role="removed-template"]')).clone();
                    removed.removeAttr('data-role');
                    var uid = $(this).data('mediastorage-uid');
                    removed.val(uid);
                    removed.attr('name', inputName.name.replace('used', 'removed'));
                    $('div[id="' + uid + '"]').replaceWith(removed);
                });

                $.magnificPopup.instance.close();
            }
        });

        element.find('.glyphicon.glyphicon-trash').click(function() {

            var cardContainer = $(this).parent().parent();

            $.ajax({
                method: "GET",
                url: $(this).data('mediastorage-remove-url')
            })
            .done(function(response) {
                cardContainer.fadeOut(300, function() {
                    var response = JSON.parse(response);

                    if(response.success) {

                        Dialog.contextMessage(
                            Dialog.settings.contextMessage.messages.deletedFile,
                            'success'
                        );

                        $(this).parent().remove();

                        galleryWidth        = parseFloat($('#mediastorage-gallery-list').css('width').replace('px', ''));
                        maxFramesInGallery  = galleryWidth / Dialog.settings.frameWidth;
                        $('.mediastorage-frame:lt('+ parseInt(maxFramesInGallery) +')').css('margin-top', '0px');

                    } else {
                        Dialog.contextMessage(
                            Dialog.translate(
                                Dialog.settings.contextMessage.messages.error,
                                [['message', response.message]]
                            ),
                            'error'
                        );
                    }
                });
            });
        });
    };

    Dialog.contextMessage = function(message, type) {


        if (typeof type === 'undefined') {
            var type = 'info';
        }

        var contextIsOpened = Dialog.settings.contextMessage.containerSelector.css('display') === 'none';

        if(!contextIsOpened) {
            Dialog.settings.contextMessage.containerSelector.slideUp(
                Dialog.settings.contextMessage.animationDelay,
                function() {
                    Dialog.contextWriteMessage(message, type);
                }
            );
        } else {
            Dialog.contextWriteMessage(message, type);
        }
    };

    Dialog.contextWriteMessage = function(message, type) {

        switch(type) {
            case 'success':
                type = 'bg-success';
                break;
            case 'error':
                type = 'bg-danger';
                break;
            case 'info':
                type = 'bg-warning';
        }
        Dialog.settings.contextMessage.messageSelector.removeClass().addClass(type);
        Dialog.settings.contextMessage.messageSelector.html(message);
        Dialog.settings.contextMessage.containerSelector.slideDown(
            Dialog.settings.contextMessage.animationDelay
        );


        clearTimeout(); setTimeout(
            function() {
                Dialog.settings.contextMessage.containerSelector.slideUp(Dialog.settings.contextMessage.animationDelay)
            },
            Dialog.settings.contextMessage.closeDelay
        );
    };

    Dialog.uploadForm = function() {
        $("div#mediastorage-upload-form").dropzone({
            //TODO: musí se získat z konfigurace pomocí {plink nebo jiné obezličky}
            url: "/administrace/media/grid?do=ajaxUploadControl-uploadForm-submit",
            paramName: "upload[]",
            init: function() {
                this.on("addedfile", function(f) {
                    Dialog.contextMessage(
                        Dialog.translate(
                            Dialog.settings.contextMessage.messages.addedFile,
                            [['file', f.name]]
                        )
                    );
                });

                this.on("complete", function(c) {

                    var response = JSON.parse(c.xhr.response);

                    if(typeof response.message === 'undefined') {
                        Dialog.contextMessage(
                            Dialog.translate(
                                Dialog.settings.contextMessage.messages.complete,
                                [['file', c.name]]
                            ),
                            'success'
                        );
                        
                        var card = Dialog.translate(
                            Dialog.settings.cardTemplate,
                            [
                                ['fileName', response.fileName],
                                ['fileLink', response.fileLink],
                                ['fileUID', response.fileUID],
                                ['fileDeleteLink', response.fileDeleteLink],
                                ['fileFullLink', response.fileFullLink]
                            ]
                        );

                        var newCard = $(card)

                        Dialog.settings.gallerySelector.prepend(newCard);
                        Dialog.setFrameMargin();
                        Dialog.frameOperations(newCard);
                        Dialog.toolTip(newCard);

                    } else {

                        Dialog.contextMessage(
                            Dialog.translate(
                                Dialog.settings.contextMessage.messages.error,
                                [['message', response.message]]
                            ),
                            'error'
                        );
                    }
                });
            },
            previewTemplate: '<div style="display:none"></div>'
        });
    };


$(document).ready(function() {
    $('.mediastorage-dialog, .mediastorage.inline-file-choice a.adder').magnificPopup({
        type: 'ajax',
        callbacks: {
            ajaxContentAdded: function() {
                Dialog.init();
                var bLazy = new Blazy({
                    container: '#mediastorage-dialog-container'
                });
            },
        }
    });
});
