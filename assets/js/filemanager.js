jQuery(document).ajaxSuccess(function (event, xhr, settings) {
    // To-do-list: add checking only for filemanager ajax upload success
    if (xhr.responseJSON != undefined) {
        if (xhr.responseJSON.type != undefined) {
            if (xhr.responseJSON.type == 1) {
                jQuery('.edit-uploaded-container').append(xhr.responseJSON.html);
            } else if (xhr.responseJSON.type == 2) {
                var uploadTabId = jQuery('#fm-upload-tab').attr('href');
                var libraryTabId = jQuery('#fm-library-tab').attr('href');
                jQuery(uploadTabId).html('');
                jQuery(libraryTabId).html('');
                jQuery('#fm-library-tab').click();
            }
        }
    }
});

jQuery(document).on('click', '.btn-copy', function (e) {
    e.preventDefault();
    var textSelector = document.querySelector('.copy-object-url');
    var range = document.createRange();
    range.selectNode(textSelector);
    window.getSelection().removeAllRanges();
    window.getSelection().addRange(range);

    try {
        // Now that we've selected the anchor text, execute the copy command  
        var successful = document.execCommand('copy');
        var msg = successful ? 'successfully' : 'unsuccessfully';
        alert('Object Url was copy to clipboard ' + msg);
    } catch (err) {
        alert('Oops, unable to copy!');
    }

    // Remove the selections - NOTE: Should use   
    // removeRange(range) when it is supported  
    window.getSelection().removeAllRanges();
});

var $browse, $gallery;

jQuery(document).on('submit', '#fm-modal form#fm-search-form', function (e) {
    e.preventDefault();
    var postData = '?' + jQuery(this).serialize();
    var tabId = jQuery('#fm-modal #fm-library-tab').attr('href');
    jQuery(tabId).html('');
    $browse.renderTabContent('#fm-library-tab', postData);
    return false;
});

var gridBox = function () {
    "use strict";
    (function (jQuery) {
        jQuery(document).on('change', '.tool-box input[name=fm-gallery-group]:radio', function () {
            var selectedItem;

            jQuery(this).prop('checked', true);
            jQuery('div.selected').removeClass('selected');
            selectedItem = jQuery(this).closest('.fm-section-item');
            selectedItem.addClass('selected');

            if ($gallery.viewFrom === 'modal') {
                $gallery.renderFileInfo(jQuery(this).data('url'));
            } else {
                window.location = jQuery(this).data('url');
            }
        });

        jQuery('.tool-box .fm-remove').click(function () {
            var inputId = $browse.element.find(".fm-btn-browse").attr('for');
            $browse.element.removeClass('attached');
            $browse.element.find('.fm-browse-selected-view').html('');
            $browse.element.find('#' + inputId).val('');
            $browse.element.find('#' + inputId).blur();
        });

        jQuery('.tool-box .fm-use').click(function () {
            $browse.useFile(jQuery(this));
        });

        jQuery('.tool-box .fm-delete').click(function () {
            $browse.deleteFile(jQuery(this));
        });
    })(window.jQuery);
};

(function ($) {
    "use strict";

    var FilemanagerBrowse, FilemanagerGallery, FilemanagerModal;
    var FilemanagerModal = $('#fm-modal');

    var FilemanagerBrowse = function (element, options) {
        var self = $browse = this;
        self.element = $(element);
        self.multiple = options.multiple;
        self.maxFileCount = options.maxFileCount;
        self.folderId = options.folderId;

        self.element.find(".fm-btn-browse").on('click', function (e) {
            e.preventDefault();
            self.renderTabContent('#fm-upload-tab');
        });

        FilemanagerModal.find("#fm-library-tab").on('click', function (e) {
            e.preventDefault();
            self.renderTabContent('#fm-library-tab');
        });

        FilemanagerModal.find("#fm-upload-tab").on('click', function (e) {
            e.preventDefault();
            self.renderTabContent('#fm-upload-tab');
        });

        var renderAjax = true, scrollAtBottom = false;
        FilemanagerModal.find(".modal-body").on('scroll', function (e) {
            var elem = $(e.currentTarget);
            var ajaxUrl = $('.fm-section #fm-next-page a').attr('href');

            if (elem[0].scrollHeight - elem.scrollTop() == elem.outerHeight() && ajaxUrl != undefined) {
                scrollAtBottom = true;
            } else {
                return false;
            }

            if (renderAjax === true && scrollAtBottom === true) {
                renderAjax = false;
                scrollAtBottom = false;
                jQuery('.fm-gallery-loading').removeClass('hide');
                jQuery.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    dataType: 'html',
                    complete: function () {
                        renderAjax = true;
                        jQuery('.fm-gallery-loading').addClass('hide');
                    },
                    success: function (html) {
                        $('.fm-gallery .fm-section:last #fm-next-page').remove();
                        $('.fm-gallery').append(html);
                    }
                });
            }
        });
    };

    FilemanagerBrowse.prototype = {
        constructor: FilemanagerBrowse,
        renderTabContent: function (tabId, postData) {
            if (postData == undefined) {
                postData = '';
            }

            var $selectedTab = FilemanagerModal.find(tabId);
            var ajaxUrl = $selectedTab.data('url');
            $selectedTab.tab('show');

            if (tabId === '#fm-upload-tab') {
                postData = {
                    multiple: this.multiple,
                    maxFileCount: this.maxFileCount,
                    folderId: this.folderId
                };
            } else {
                ajaxUrl += postData;
            }

            if (jQuery($selectedTab.attr('href')).html() == '') {
                jQuery.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    dataType: 'html',
                    data: postData,
                    success: function (html) {
                        jQuery($selectedTab.attr('href')).html(html);
                    }
                });
            }
        },
        useFile: function ($this) {
            jQuery.ajax({
                url: $this.data('url'),
                type: 'POST',
                data: {
                    id: $this.data('id')
                },
                dataType: 'json',
                success: function (data) {
                    jQuery('input[name^="Filemanager"]').each(function () {
                        var index = jQuery(this).prop('name').match(/\[(.*?)\]/)[1];
                        jQuery(this).val(data[index]);
                    });

                    var inputId = $browse.element.find(".fm-btn-browse").attr('for');
                    $browse.element.addClass('attached');
                    $browse.element.find('.fm-browse-selected-view').html(data.selectedFile);
                    $browse.element.find('#' + inputId).val(data['file_identifier']);
                    $browse.element.find('#' + inputId).blur();
                }
            });
            FilemanagerModal.modal('hide');
        },
        deleteFile: function ($this) {
            jQuery.ajax({
                url: $this.data('url'),
                type: 'POST',
                dataType: 'json',
                success: function (data) {
                    var libraryTabId = jQuery('#fm-library-tab').attr('href');
                    jQuery(libraryTabId).html('');
                    $browse.renderTabContent('#fm-library-tab');
                }
            });
        }
    };

    $.fn.filemanagerBrowse = function (option) {
        var args = Array.apply(null, arguments);
        args.shift();
        return this.each(function () {
            var $this = $(this), data = $this.data('filemanagerBrowse'), options = typeof option === 'object' && option;

            if (!data) {
                $this.data('filemanagerBrowse', (data = new FilemanagerBrowse(this, $.extend({}, $.fn.filemanagerBrowse.defaults, options, $(this).data()))));
            }

            if (typeof option === 'string') {
                data[option].apply(data, args);
            }
        });
    };
    $.fn.filemanagerBrowse.defaults = {};
    $.fn.filemanagerBrowse.Constructor = FilemanagerBrowse;

    var FilemanagerGallery = function (gallery, options) {
        var self = $gallery = this;

        self.$fileInfo = FilemanagerModal.find('.fm-file-info');
        self.$btnUse = $(gallery).find('.tool-box .fm-use');
        self.$btnView = $(gallery).find('input[name=fm-gallery-group]:radio');
        self.viewFrom = options.viewFrom;

        var renderAjax = true, scrollAtBottom = false;
        $(window).scroll(function () {
            var wintop = $(window).scrollTop(), docheight = $(document).height(), winheight = $(window).height();
            var scrolltrigger = 0.95;
            var ajaxUrl = $('.fm-section #fm-next-page a').attr('href');

            if ((wintop / (docheight - winheight)) > scrolltrigger && ajaxUrl != undefined) {
                scrollAtBottom = true;
            } else {
                return false;
            }

            if (renderAjax === true && scrollAtBottom === true) {
                renderAjax = false;
                scrollAtBottom = false;
                jQuery('.fm-gallery-loading').removeClass('hide');
                jQuery.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    dataType: 'html',
                    complete: function () {
                        renderAjax = true;
                        jQuery('.fm-gallery-loading').addClass('hide');
                    },
                    success: function (html) {
                        $('.fm-gallery .fm-section:last #fm-next-page').remove();
                        $('.fm-gallery').append(html);
                    }
                });
            }
        });
    };

    FilemanagerGallery.prototype = {
        constructor: FilemanagerGallery,
        renderFileInfo: function (url) {
            var fileInfo = this.$fileInfo;
            fileInfo.html('');
            jQuery('.fm-file-info-loading .fm-loading').removeClass('hide');
            jQuery.ajax({
                url: url,
                type: 'POST',
                data: {
                    uploadType: this.viewFrom
                },
                complete: function () {
                    jQuery('.fm-file-info-loading .fm-loading').addClass('hide');
                },
                dataType: 'html',
                success: function (html) {
                    fileInfo.html(html);
                }
            });
        }
    };

    $.fn.filemanagerGallery = function (option) {
        var args = Array.apply(null, arguments);
        args.shift();
        return this.each(function () {
            var $this = $(this), data = $this.data('filemanagerGallery'), options = typeof option === 'object' && option;

            if (!data) {
                $this.data('filemanagerGallery', (data = new FilemanagerGallery(this, $.extend({}, $.fn.filemanagerGallery.defaults, options, $(this).data()))));
            }

            if (typeof option === 'string') {
                data[option].apply(data, args);
            }
        });
    };
    $.fn.filemanagerGallery.defaults = {};
    $.fn.filemanagerGallery.Constructor = FilemanagerGallery;

})(window.jQuery);