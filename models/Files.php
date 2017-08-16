<?php

namespace dpodium\filemanager\models;

use Yii;
use yii\db\ActiveRecord;
use yii\behaviors\TimestampBehavior;

/**
 * This is the model class for table "files".
 *
 * @property integer $file_id
 * @property string $file_identifier
 * @property string $host
 * @property string $url
 * @property string $storage_id
 * @property string $object_url
 * @property string $thumbnail_name
 * @property string $src_file_name
 * @property string $mime_type
 * @property string $caption
 * @property string $alt_text
 * @property string $description
 * @property string $dimension
 * @property integer $folder_id
 * @property integer $created_at
 * @property integer $updated_at
 *
 * @property Folders $folder
 * @property FilesRelationship[] $filesRelationships
 */
class Files extends \yii\db\ActiveRecord {

    public $upload_file;
    public $tags;
    public $filename;

    public function behaviors() {
        return [
                [
                'class' => TimestampBehavior::className(),
                'attributes' => [
                    ActiveRecord::EVENT_BEFORE_INSERT => ['created_at', 'updated_at'],
                    ActiveRecord::EVENT_BEFORE_UPDATE => ['updated_at'],
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'files';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        // convert MB to byte
        $maxSize = (int) \Yii::$app->controller->module->maxFileSize * 1000 * 1024;
        $extensions = \Yii::$app->controller->module->getMimeType();
        return [
                [['object_url', 'url', 'mime_type', 'folder_id', 'storage_id', 'filename'], 'required'],
                [['src_file_name', 'file_identifier'], 'required', 'on' => 'afterValidate'],
                [['folder_id'], 'integer'],
                [['url', 'thumbnail_name', 'description'], 'string', 'max' => 255],
                [['src_file_name', 'caption', 'alt_text'], 'string', 'max' => 64],
                [['mime_type'], 'string', 'max' => 100],
                [['file_identifier'], 'string', 'max' => 32],
                [['dimension'], 'string', 'max' => 12],
            // unique filename
            ['src_file_name', 'unique', 'targetAttribute' => ['src_file_name', 'folder_id'], 'message' => Yii::t('filemanager', 'This {attribute} already been taken.')],
            //
            [['upload_file', 'tags'], 'safe'],
            // validate file type and size
            ['upload_file', 'file', 'skipOnEmpty' => false, 'on' => 'upload',
                'mimeTypes' => \Yii::$app->controller->module->acceptedFilesType,
                'wrongMimeType' => Yii::t('filemanager', 'Invalid file type. Available file types are {types}', ['types' => implode(',', \Yii::$app->controller->module->acceptedFilesType)]),
                'extensions' => $extensions,
                'wrongExtension' => Yii::t('filemanager', 'Invalid file extension. Available file extension are {ext}', ['ext' => implode(',', $extensions)]),
                'maxSize' => $maxSize,
                'tooBig' => Yii::t('filemanager', 'Limit is {limit}MB', ['limit' => \Yii::$app->controller->module->maxFileSize])
            ],
            // validate src_file_name
            // /^[a-zA-Z0-9_-]+$/
            ['filename', 'match', 'pattern' => '/^[-0-9\p{L}\p{Nd}\p{M}]+$/u', 'message' => Yii::t('filemanager', 'Filename can only contain alphanumeric characters and dashes.')]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'file_id' => Yii::t('filemanager', 'File ID'),
            'file_identifier' => Yii::t('filemanager', 'File Identifier'),
            'host' => Yii::t('filemanager', 'host'),
            'object_url' => Yii::t('filemanager', 'Object Url'),
            'url' => Yii::t('filemanager', 'Url'),
            'thumbnail_name' => Yii::t('filemanager', 'Thumbnail File Name'),
            'src_file_name' => Yii::t('filemanager', 'File Name'),
            'mime_type' => Yii::t('filemanager', 'Mime Type'),
            'caption' => Yii::t('filemanager', 'Title'),
            'alt_text' => Yii::t('filemanager', 'Alternative Text'),
            'description' => Yii::t('filemanager', 'Description'),
            'dimension' => Yii::t('filemanager', 'Dimension'),
            'storage_id' => Yii::t('filemanager', 'Storage ID'),
            'folder_id' => Yii::t('filemanager', 'Folder ID'),
            'created_at' => Yii::t('filemanager', 'Created At'),
            'updated_at' => Yii::t('filemanager', 'Updated At'),
            //
            'upload_to' => Yii::t('filemanager', 'Upload To'),
            'upload_file' => '',
            'tags' => Yii::t('filemanager', 'Tags'),
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFolder() {
        return $this->hasOne(Folders::className(), ['folder_id' => 'folder_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getFilesRelationships() {
        return $this->hasMany(FilesRelationship::className(), ['file_id' => 'file_id']);
    }

    public function getFileUrl($thumbnail = false) {
        $domain = $this->object_url;
        if ($thumbnail) {
            return $domain . $this->thumbnail_name;
        }
        return $domain . $this->src_file_name;
    }

}
