tinymce.PluginManager.add('yii2-filemanager', function(editor, url) {
    editor.on('init', function (e) {
        var m = $('#fm-modal');
        $(this.editorContainer).filemanagerBrowse({"multiple":m.data('multiple'),"maxFileCount":m.data('maxfilecount'),"folderId":m.data('folderid'), "tinymce": editor});
    });

    // Add a button that opens a window
    editor.addButton('yii2-filemanager', {
        text: false,
        tooltip: 'Insert document',
        icon: 'newdocument',
        classes: 'fm-btn-browse',
        onclick: function(e) {
            var fileBrowse = $(editor.editorContainer).data('filemanagerBrowse');
            e.preventDefault();
            fileBrowse.renderTabContent('#fm-library-tab');
            $('#fm-modal').modal({
                show: 'false'
            });
            $browse = fileBrowse;
        }
    });

    // Adds a menu item to the tools menu
    editor.addMenuItem('yii2-filemanager', {
        icon: 'newdocument',
        text: 'Document',
        context: 'insert',
        classes: 'fm-btn-browse',
        prependToContext: true,
        onclick: function(e) {
            var fileBrowse = $(editor.editorContainer).data('filemanagerBrowse');
            e.preventDefault();
            fileBrowse.renderTabContent('#fm-library-tab');
            $('#fm-modal').modal({
                show: 'false'
            });
            $browse = fileBrowse;
        }
    });

    return {
        getMetadata: function () {
            return  {
                name: "File Manager for Yii2 - TinyMCE Integration",
                url: "https://github.com/dpodium/yii2-filemanager"
            };
        }
    };
});
