<?php

namespace dpodium\filemanager\controllers;

use Yii;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\web\Response;
use yii\helpers\Json;
use yii\web\UploadedFile;
use yii\helpers\ArrayHelper;
use yii\imagine\Image;
//use dpodium\filemanager\models\Files;
//use dpodium\filemanager\models\FilesSearch;
//use dpodium\filemanager\models\Folders;
//use dpodium\filemanager\models\FilesRelationship;
//use dpodium\filemanager\models\FilesTag;
use dpodium\filemanager\components\Filemanager;
use dpodium\filemanager\FilemanagerAsset;
use dpodium\filemanager\components\S3;
use dpodium\filemanager\widgets\Gallery;

/**
 * FilesController implements the CRUD actions for Files model.
 */
class FilesController extends Controller {

    public function behaviors() {
        return [
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

    /**
     * Lists all Files models.
     * @return mixed
     */
    public function actionIndex($view = 'list') {
        if (!in_array($view, ['list', 'grid'])) {
            throw new \Exception('Invalid view.');
        }

        FilemanagerAsset::register($this->view);
        $searchModel = new $this->module->models['filesSearch'];
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // lazy loading
        if ($view == 'grid' && \Yii::$app->request->isAjax) {
            echo Gallery::widget([
                'dataProvider' => $dataProvider,
                'viewFrom' => 'full-page'
            ]);
            \Yii::$app->end();
        }

        $folders = $this->module->models['folders'];
        $folderArray = ArrayHelper::merge(['' => Yii::t('filemanager', 'All')], ArrayHelper::map($folders::find()->all(), 'folder_id', 'category'));
        return $this->render('index', [
                    'model' => $searchModel,
                    'dataProvider' => $dataProvider,
                    'folderArray' => $folderArray,
                    'uploadType' => Filemanager::TYPE_FULL_PAGE,
                    'view' => $view,
                    'viewFrom' => 'full-page'
        ]);
    }

    /**
     * Updates an existing Files model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param integer $id
     * @return mixed
     */
    public function actionUpdate($id, $view = 'list') {
        if (!in_array($view, ['list', 'grid'])) {
            throw new \Exception('Invalid view.');
        }

        FilemanagerAsset::register($this->view);
        $model = $this->findModel($id);
        $filesRelationship = $this->module->models['filesRelationship'];
        $tagArray = $filesRelationship::getTagIdArray($id);
        $model->tags = ArrayHelper::getColumn($tagArray, 'id');
        $editableTagsLabel = ArrayHelper::getColumn($tagArray, 'value');
        $filesTag = $this->module->models['filesTag'];
        $tags = ArrayHelper::map($filesTag::find()->asArray()->all(), 'tag_id', 'value');

        if (Yii::$app->request->post('hasEditable')) {
            $post = [];
            $post['Files'] = Yii::$app->request->post('Files');

            if ($model->load($post)) {
                foreach ($post['Files'] as $attribute => $value) {
                    if ($attribute === 'tags') {
                        $tagModel = new $this->module->models['filesTag'];
                        $tagRelationshipModel = new $this->module->models['filesRelationship'];
                        $saveTags = $tagModel->saveTag($model->tags);
                        if (isset($saveTags['error'])) {
                            echo Json::encode(['output' => '', 'message' => $saveTags['error']]);
                            return;
                        }
                        $tagRelationshipModel->saveRelationship($model->file_id, $saveTags);
                        $editableTagsLabel = ArrayHelper::getColumn($filesRelationship::getTagIdArray($id), 'value');
                        $result = Json::encode(['output' => implode(', ', $editableTagsLabel), 'message' => '']);
                    } else {
                        $model->$attribute = \yii\helpers\Html::encode($model->$attribute);
                        if ($model->update(true, [$attribute]) !== false) {
                            $model->touch('updated_at');
                            $result = Json::encode(['output' => $model->$attribute, 'message' => '']);
                        } else {
                            $result = Json::encode(['output' => $model->$attribute, 'message' => $model->errors[$attribute]]);
                        }
                    }
                }
                echo $result;
            }
            return;
        }

        if (Yii::$app->request->post('uploadType')) {
            echo $this->renderAjax('update', [
                'model' => $model,
                'tags' => $tags,
                'editableTagsLabel' => $editableTagsLabel,
                'uploadType' => 'modal',
                'view' => $view
            ]);
            return;
        } else {
            return $this->render('update', [
                        'model' => $model,
                        'tags' => $tags,
                        'editableTagsLabel' => $editableTagsLabel,
                        'uploadType' => 'full-page',
                        'view' => $view
            ]);
        }
    }

    /**
     * Deletes an existing Files model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param integer $id
     * @return mixed
     */
    public function actionDelete($id) {
        $model = $this->findModel($id);

        if (isset($this->module->storage['s3'])) {
            $files = [
                ['Key' => $model->url . '/' . $model->src_file_name],
                ['Key' => $model->url . '/' . $model->thumbnail_name],
            ];

            $s3 = new S3();
            $s3->delete($files);
        } else {
            $file = Yii::getAlias($model->storage_id) . $model->object_url . $model->src_file_name;
            $thumb = Yii::getAlias($model->storage_id) . $model->object_url . $model->thumbnail_name;

            if (file_exists($file)) {
                unlink($file);
            }

            if (file_exists($thumb)) {
                unlink($thumb);
            }
        }

        $model->delete();

        if (Yii::$app->request->isAjax) {
            echo Json::encode(['status' => true]);
            \Yii::$app->end();
        }
        return $this->redirect(['index']);
    }

    public function actionUpload() {
        FilemanagerAsset::register($this->view);

        $model = new $this->module->models['files'];
        $model->scenario = 'upload';
        $folders = $this->module->models['folders'];
        $folderArray = ArrayHelper::map($folders::find()->all(), 'folder_id', 'category');

        if (Yii::$app->request->isAjax) {
            if (!in_array(Yii::$app->request->post('uploadType'), [Filemanager::TYPE_FULL_PAGE, Filemanager::TYPE_MODAL])) {
                echo Json::encode(['error' => Yii::t('filemanager', 'Invalid value: {variable}', ['variable' => 'uploadType'])]);
                \Yii::$app->end();
            }

            Yii::$app->response->getHeaders()->set('Vary', 'Accept');

            $file = UploadedFile::getInstances($model, 'upload_file');
            if (!$file) {
                echo Json::encode(['error' => Yii::t('filemanager', 'File not found.')]);
                \Yii::$app->end();
            }

            $model->folder_id = Yii::$app->request->post('uploadTo');
            $folder = $folders::find()->select(['path', 'storage'])->where('folder_id=:folder_id', [':folder_id' => $model->folder_id])->one();

            if (!$folder) {
                echo Json::encode(['error' => Yii::t('filemanager', 'Invalid folder location.')]);
                \Yii::$app->end();
            }

            $model->upload_file = $file[0];
            
            //
            //Handling filename
            //
            $extension = '.' . $file[0]->getExtension();
            $file[0]->name= substr($file[0]->name, 0, (strlen($file[0]->name) - strlen($extension)));
            
            if(preg_match('/^[-0-9\p{L}\p{Nd}\p{M}]+$/u', $file[0]->name) === 0){   
                $file[0]->name = preg_replace('~[\p{P}\p{S}]~u', '-', $file[0]->name);
                $file[0]->name = preg_replace('/[-]+/', '-', $file[0]->name);
            }    
            $file[0]->name = $file[0]->name . $extension;
            //
            //End
            //
            
            $model->filename = $file[0]->name;
            list($width, $height) = getimagesize($file[0]->tempName);
            $model->dimension = ($width && $height) ? $width . 'X' . $height : null;
            // Too large size will cause memory exhausted issue when create thumbnail
            if (!is_null($model->dimension)) {
                if (($width > 2272 || $height > 1704)) {
                    echo Json::encode(['error' => Yii::t('filemanager', 'File dimension at most 2272 X 1704.')]);
                    \Yii::$app->end();
                }
            }
            $model->mime_type = $file[0]->type;

            $model->url = $folder->path;
            //$extension = '.' . $file[0]->getExtension();

            $uploadResult = ['status' => true, 'error_msg' => ''];
            $transaction = \Yii::$app->db->beginTransaction();
            if (isset($this->module->storage['s3'])) {
                $model->object_url = '/';
                $model->host = isset($this->module->storage['s3']['host']) ? $this->module->storage['s3']['host'] : null;
                $model->storage_id = $this->module->storage['s3']['bucket'];
                $this->saveModel($model, $extension, $folder->storage);
                $uploadResult = $this->uploadToS3($model, $file[0], $extension);
            } else {
                $model->object_url = '/' . $folder->path . '/';
                $model->storage_id = $this->module->directory;
                $this->saveModel($model, $extension, $folder->storage);
                $uploadResult = $this->uploadToLocal($model, $file[0], $extension);
            }

            if (!$uploadResult['status']) {
                $transaction->rollBack();
                echo Json::encode(['error' => $uploadResult['error_msg']]);
                \Yii::$app->end();
            }
            $transaction->commit();
            // if upload type = 1, render edit bar below file input container
            // if upload type = 2, switch active tab to Library for user to select file
            Yii::$app->response->format = Response::FORMAT_JSON;
            if (Yii::$app->request->post('uploadType') == Filemanager::TYPE_FULL_PAGE) {
                $fileType = $model->mime_type;
                if ($model->dimension) {
                    $fileType = 'image';
                }
                $html = Filemanager::renderEditUploadedBar($model->file_id, $model->object_url, $model->src_file_name, $fileType);
                return ['status' => 1, 'message' => 'Upload Success', 'type' => Yii::$app->request->post('uploadType'), 'html' => $html];
            } else {
                return ['status' => 1, 'message' => 'Upload Success', 'type' => Yii::$app->request->post('uploadType')];
            }
            return;
        }

        $multiple = false;
        $maxFileCount = 0;
        if ($this->module->filesUpload['multiple'] != false) {
            $multiple = true;
            $maxFileCount = isset($this->module->filesUpload['maxFileCount']) ? $this->module->filesUpload['maxFileCount'] : 0;
        }

        return $this->render('upload', [
                    'model' => $model,
                    'folderArray' => $folderArray,
                    'multiple' => $multiple,
                    'maxFileCount' => $maxFileCount
        ]);
    }

    public function actionUploadTab($ajaxRequest = true) {
        $model = new $this->module->models['files'];
        $model->scenario = 'upload';
        $folderArray = [];

        $multiple = strtolower(Yii::$app->request->post('multiple')) === 'true' ? true : false;
        $maxFileCount = $multiple ? Yii::$app->request->post('maxFileCount') : 1;
        $folders = $this->module->models['folders'];
        $folderId = Yii::$app->request->post('folderId');

        if (!$folders::find()->where('folder_id=:folder_id', [':folder_id' => $folderId])->exists()) {
            $folderArray = ArrayHelper::map($folders::find()->all(), 'folder_id', 'category');
        } else {
            $model->folder_id = $folderId;
        }

        $uploadView = $this->renderAjax('_file-input', [
            'model' => $model,
            'folderArray' => $folderArray,
            'uploadType' => Filemanager::TYPE_MODAL,
            'multiple' => $multiple,
            'maxFileCount' => $maxFileCount
        ]);

        if ($ajaxRequest === true) {
            echo $uploadView;
            \Yii::$app->end();
        }

        return $uploadView;
    }

    public function actionLibraryTab() {
        $searchModel = new $this->module->models['filesSearch'];
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        if (Yii::$app->request->getQueryParam('page')) {
            echo Gallery::widget([
                'dataProvider' => $dataProvider,
                'viewFrom' => 'modal'
            ]);
            \Yii::$app->end();
        }

        echo $this->renderAjax('_grid-view', [
            'model' => $searchModel,
            'dataProvider' => $dataProvider,
            'uploadType' => Filemanager::TYPE_MODAL,
            'viewFrom' => 'modal'
        ]);
        \Yii::$app->end();
    }

    public function actionUse() {
        $fileId = Yii::$app->request->post('id');
        $model = $this->findModel($fileId);
        $fileType = $model->mime_type;
        if ($model->dimension) {
            $src = $model->object_url . $model->thumbnail_name;
            $fileType = 'image';
        } else {
            $src = $model->object_url . $model->src_file_name;
        }

        $toolArray = [
            ['tagType' => 'i', 'options' => ['class' => 'fa-icon fa fa-times fm-remove', 'title' => \Yii::t('filemanager', 'Remove')]]
        ];
        $gridBox = new \dpodium\filemanager\components\GridBox([
            'src' => $src,
            'fileType' => $fileType,
            'toolArray' => $toolArray,
            'thumbnailSize' => $this->module->thumbnailSize
        ]);
        $selectedFileView = $gridBox->renderGridBox();

        Yii::$app->response->format = Response::FORMAT_JSON;
        return ArrayHelper::merge($model->attributes, ['selectedFile' => $selectedFileView]);
    }

    /**
     * Finds the Files model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $id
     * @return Files the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id) {
        $filesModel = $this->module->models['files'];
        if (($model = $filesModel::findOne($id)) !== null) {
            return $model;
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }
    }

    protected function saveModel(&$model, $extension, $folderStorage) {
        $model->filename = str_replace($extension, '', $model->filename);

        if ($model->validate()) {
            $model->scenario = 'afterValidate';
            $model->caption = str_replace(" ", "_", $model->filename);
            $model->caption = str_replace(["\"", "'"], "", $model->filename);
            $model->alt_text = $model->caption;
            $model->src_file_name = $model->caption . $extension;
            $model->thumbnail_name = $model->src_file_name;
            $model->file_identifier = md5($folderStorage . '/' . $model->url . '/' . $model->src_file_name);

            if ($model->save()) {
                return true;
            }
        }

        $errors = [];
        foreach ($model->errors as $err) {
            $errors[] = $model->src_file_name . ': ' . $err[0];
        }
        echo Json::encode(['error' => implode('<br>', $errors)]);
        \Yii::$app->end();
    }

    protected function uploadToLocal($model, $file, $extension) {
        if (!file_exists(Yii::getAlias($model->storage_id) . '/' . $model->url)) {
            // File mode : 0755, Ref: http://php.net/manual/en/function.chmod.php
            mkdir(Yii::getAlias($model->storage_id) . '/' . $model->url, 0755, true);
        }

        if (!$file->saveAs(Yii::getAlias($model->storage_id) . '/' . $model->url . '/' . $model->src_file_name)) {
            return [
                'status' => false,
                'error_msg' => Yii::t('filemanager', 'Upload fail due to some reasons.')
            ];
        }
        $uploadThumbResult = ['status' => true, 'error_msg' => ''];
        if ($model->dimension) {
            $thumbnailSize = $this->module->thumbnailSize;
            $model->thumbnail_name = 'thumb_' . str_replace($extension, '', $model->src_file_name) . '_' . $thumbnailSize[0] . 'X' . $thumbnailSize[1] . $extension;
            $uploadThumbResult = $this->createThumbnail($model, Yii::getAlias($model->storage_id) . '/' . $model->url . '/' . $model->src_file_name);
            $model->update(false, ['dimension', 'thumbnail_name']);
        }

        return $uploadThumbResult;
    }

    protected function uploadToS3($model, $file, $extension) {
        $s3 = new S3();
        $result = $s3->upload($file, $model->src_file_name, $model->url);

        if (!$result['status']) {
            return [
                'status' => false,
                'error_msg' => Yii::t('filemanager', 'Upload fail due to some reasons.')
            ];
        }
        
        //Remove filename name from object URL
        $model->object_url = substr($result['objectUrl'], 0, strrpos( $result['objectUrl'], '/'));
        $model->object_url = $model->object_url . '/';
        //End
        
        //$model->object_url = str_replace($model->src_file_name, '', $result['objectUrl']);
        $uploadThumbResult = ['status' => true, 'error_msg' => ''];
        if ($model->dimension) {
            $thumbnailSize = $this->module->thumbnailSize;
            $model->thumbnail_name = 'thumb_' . str_replace($extension, '', $model->src_file_name) . '_' . $thumbnailSize[0] . 'X' . $thumbnailSize[1] . $extension;
            $uploadThumbResult = $this->createThumbnail($model, $file->tempName);
        }
        $model->update(false, ['object_url', 'dimension', 'thumbnail_name']);

        return $uploadThumbResult;
    }

    protected function createThumbnail($model, $file) {
        $thumbnailSize = $this->module->thumbnailSize;
        $thumbnailFile = Image::thumbnail($file, $thumbnailSize[0], $thumbnailSize[1]);

        if (isset($this->module->storage['s3'])) {
            $s3 = new S3();
            $result = $s3->uploadThumbnail($thumbnailFile, $model->thumbnail_name, $model->url, $model->mime_type);
            if (!$result['status']) {
                return [
                    'status' => false,
                    'error_msg' => Yii::t('filemanager', 'Fail to create thumbnail.')
                ];
            }
        } else {
            if (!file_exists(Yii::getAlias($model->storage_id) . '/' . $model->url)) {
                mkdir(Yii::getAlias($model->storage_id) . '/' . $model->url, 0755, true);
            }
            $result = $thumbnailFile->save(Yii::getAlias($model->storage_id) . '/' . $model->url . '/' . $model->thumbnail_name);
            if (!$result) {
                return [
                    'status' => false,
                    'error_msg' => Yii::t('filemanager', 'Fail to create thumbnail.')
                ];
            }
        }

        return ['status' => true, 'error_msg' => ''];
    }

}
