File Manager for Yii2
=====================

Installation
------------

### Install With Composer

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require dpodium/yii2-filemanager "dev-master"
```

or add

```
"dpodium/yii2-filemanager": "dev-master"
```

to the require section of your `composer.json` file.

Execute migration here:
```
yii migrate --migrationPath=@dpodium/filemanager/migrations
yii migrate/down --migrationPath=@dpodium/filemanager/migrations
```

Usage
-----

Once the extension is installed, simply modify your application configuration as follows:

Upload file in local:

```php
return [
	'modules' => [
            'gridview' => [
                'class' => '\kartik\grid\Module'
            ],
            'filemanager' => [
                'class' => 'dpodium\filemanager\Module',
                'storage' => ['local'],
                // This configuration will be used in 'filemanager/files/upload'
                // To support dynamic multiple upload
                // Default multiple upload is true, max file to upload is 10
                // If multiple set to true and maxFileCount is not set, unlimited multiple upload
                'filesUpload' => [
                    'multiple' => true,
                    'maxFileCount' => 30
                ],
                // in mime type format
                'acceptedFilesType' => [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                ],
                // MB
                'maxFileSize' => 8,
                // [width, height], suggested thumbnail size is 120X120
                'thumbnailSize' => [120,120] 
            ]
        ]
];
```

Upload file to AWS S3:

```php
return [
	'modules' => [
	    'gridview' => [
                'class' => '\kartik\grid\Module'
            ],
            'filemanager' => [ // do not change module to other name
                'class' => 'dpodium\filemanager\Module',
                // This configuration will be used in 'filemanager/files/upload'
                // To support dynamic multiple upload
                // Default multiple upload is true, max file to upload is 10
                // If multiple set to true and maxFileCount is not set, unlimited multiple upload
                'filesUpload' => [
                    'multiple' => true,
                    'maxFileCount' => 30
                ],
                'storage' => [
                    's3' => [
                        'key' => 'your aws s3 key',
                        'secret' => 'your aws s3 secret',
                        'bucket' => '',
                        'region' => '',
                        'proxy' => '192.168.16.1:10',
                        'cdnDomain' => '',
                        'prefixPath' => '',
                        'cacheTime' => '2592000'    //30 Days 
                    ]
                ],
                // in mime type format
                'acceptedFilesType' => [
                    'image/jpeg',
                    'image/png',
                    'image/gif',
                ],
                // MB
                'maxFileSize' => 8,
                // [width, height], suggested thumbnail size is 120X120
                'thumbnailSize' => [120,120] 
            ]
        ]
];
```

You can then access File Manager through the following URL:

```
http://localhost/path/to/index.php?r=filemanager/folders
http://localhost/path/to/index.php?r=filemanager/files
```

In order to use File Manager Browse feature:

```php
    use yii\helpers\Html;
    use yii\widgets\ActiveForm;
    use dpodium\filemanager\widgets\FileBrowse;

    // This is just an example to upload a banner
    $form = ActiveForm::begin();
    echo $form->field($model, 'banner_name');
    echo $form->field($model, 'banner_description');

    // if you would like to store file_identifier in your table
    echo $form->field($model, 'file_identifier')->widget(FileBrowse::className(), [
            'multiple' => false, // allow multiple upload
            'folderId' => 1 // set a folder to be uploaded to.
    ]);

    echo Html::submitButton('Submit', ['class' => 'btn btn-primary']);
    ActiveForm::end();

    // !important: modal must be rendered after form
    echo FileBrowse::renderModal();
```
