<?php

namespace dpodium\filemanager;

use yii\web\AssetBundle;

class FilemanagerEditorAsset extends AssetBundle {

    public $sourcePath = '@dpodium/filemanager/assets';
    public $css = [
    ];
    public $js = [
        'js/yii2-filemanager.plugin.js',
    ];
    public $depends = [
        'dosamigos\tinymce\TinyMceAsset',
        'dpodium\filemanager\FilemanagerAsset',
    ];

    /**
     * uncomment in localhost for debug purpose
     */
//    public $publishOptions = [
//        'forceCopy' => true
//    ];
}
