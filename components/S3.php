<?php

namespace dpodium\filemanager\components;

use Aws\S3\S3Client;
use Aws\S3\Exception\S3Exception;
use yii\base\InvalidConfigException;

class S3 {

    private $key;
    private $secret;
    private $bucket;
    protected $s3;

    public function __construct() {
        $module = \Yii::$app->getModule('filemanager');

        $this->key = isset($module->storage['s3']['key']) ? $module->storage['s3']['key'] : '';
        $this->secret = isset($module->storage['s3']['secret']) ? $module->storage['s3']['secret'] : '';
        $this->bucket = isset($module->storage['s3']['bucket']) ? $module->storage['s3']['bucket'] : '';

        if ($this->key == '') {
            throw new InvalidConfigException('Key cannot be empty!');
        }
        if ($this->secret == '') {
            throw new InvalidConfigException('Secret cannot be empty!');
        }
        if ($this->bucket == '') {
            throw new InvalidConfigException('Bucket cannot be empty!');
        }

        $param = [
            'version' => 'latest',
            'credentials' => [
                'key' => $this->key,
                'secret' => $this->secret
            ]
        ];

        if (isset($module->storage['s3']['version'])) {
            $param['version'] = $module->storage['s3']['version'];
        }

        if (isset($module->storage['s3']['region'])) {
            $param['region'] = $module->storage['s3']['region'];
        }

        if (isset($module->storage['s3']['proxy'])) {
            $param['http']['proxy'] = $module->storage['s3']['proxy'];
        }

        $this->s3 = new S3Client($param);
    }

    public function upload($file, $fileName, $path) {
        $result['status'] = false;

        try {
            $cacheTime = \Yii::$app->getModule('filemanager')->storage['s3']['cacheTime'];            
            $uploadResult = $this->s3->putObject([
                'Bucket' => $this->bucket,
                'Key' => $path . '/' . $fileName,
                'SourceFile' => $file->tempName,
                'ContentType' => $file->type,
                'ACL' => 'public-read',
                'CacheControl' => 'max-age='.$cacheTime, 
            ]);

            $result['status'] = true;
            $result['objectUrl'] = $uploadResult['ObjectURL'];
            $result['uploadResult'] = $uploadResult;
        } catch (S3Exception $e) {
            echo $e . "\nThere was an error uploading the file.\n";
        }

        return $result;
    }

    public function uploadThumbnail($file, $fileName, $path, $fileType) {
        $result['status'] = false;

        try {
            $cacheTime = \Yii::$app->getModule('filemanager')->storage['s3']['cacheTime'];           
            $uploadResult = $this->s3->putObject([
                'Body' => $file,
                'Bucket' => $this->bucket,
                'Key' => $path . '/' . $fileName,
                'ContentType' => $fileType,
                'ACL' => 'public-read',
                'CacheControl' => 'max-age='.$cacheTime 
            ]);

            $result['status'] = true;
            $result['objectUrl'] = $uploadResult['ObjectURL'];
            $result['uploadResult'] = $uploadResult;
        } catch (S3Exception $e) {
            echo $e . "\nThere was an error uploading the file.\n";
        }

        return $result;
    }

    public function delete($files) {
        $result['status'] = false;
        $objects = [];

        foreach ($files as $fileKey) {
            $objects[] = $fileKey;
        }
        try {
            $deleteResult = $this->s3->deleteObjects([
                'Bucket' => $this->bucket,
                'Delete' => ['Objects' => $objects],
            ]);
            $result['status'] = true;
            $result['data'] = $deleteResult;
        } catch (S3Exception $e) {
            echo $e . "\nThere was an error uploading the file.\n";
        }

        return $result;
    }

    public function listObject() {
        $result = [];
        $iterator = $this->s3->getIterator('ListObjects', array(
            'Bucket' => $this->bucket
        ));

        foreach ($iterator as $object) {
            $result[] = $object['Key'];
        }

        return $result;
    }

}
