<?php

namespace App\Traits\Filer;

use Filer as Uploader;
use App\Helpers\Filer\Form\Forms;
use Request;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use URL;

trait Filer
{
    /**
     * Upload field variable.
     **/
    protected $uploads = [];

    /**
     * Boot the Sorter trait for this model.
     *
     * @return void
     */
    public static function bootFiler()
    {
        static::saving(function ($model) {
            $model->upload($model);
        });
    }

    /**
     * Upload files and save to the table.
     *
     * @param Eloquent $model
     *
     * @return null
     */
    public function upload($model)
    {

        if (empty($model->uploads)) {
            return;
        }

        $model->uploadFiles();

    }

    /**
     * Upload single file save as single diamentional array.
     *
     * @return null
     *
     */
    public function uploadFiles()
    {

        foreach ($this->uploads as $field => $settings) {
            $files = [];

            if (is_array(Request::file($field))) {

                foreach (Request::file($field) as $file) {

                    if ($file instanceof UploadedFile) {
                        $files[] = Uploader::upload($file, $this->upload_folder . DIRECTORY_SEPARATOR . $field);
                    }

                }

            } elseif (Request::hasFile($field)) {

                $file = Request::file($field);

                if ($file instanceof UploadedFile) {
                    $files[] = Uploader::upload($file, $this->upload_folder . DIRECTORY_SEPARATOR . $field);
                }

            }

            $this->setFiles($field, $files);
        }

    }

    /**
     * Return upload_folder attribute for the model.
     *
     * @param string $value
     *
     * @return string
     */
    public function getUploadFolderAttribute($value)
    {

        if (!empty($value)) {
            return $value;
        }

        $folder                            = folder_new(null, null);
        $this->attributes['upload_folder'] = $folder;

        return $folder;

    }

    /**
     * Return url to upload the file.
     *
     * @param srting $field
     * @param srting|string $file
     *
     * @return null
     */
    public function getUploadURL($field, $file = 'file')
    {
        return guard_url('upload/' . $this->config . '/' . ($this->upload_folder) . '/' . $field . '/' . $file);
    }

    /**
     * Return url to upload the file.
     *
     * @param srting $field
     * @param srting|string $file
     *
     * @return null
     */
    public function getCropURL($field, $file = 'file')
    {
        return trans_url('crop/' . $this->config . '/' . ($this->upload_folder) . '/' . $field . '/' . $file);
    }

    /**
     * Return url to upload the file.
     *
     * @param srting $field
     * @param srting|string $file
     *
     * @return null
     */
    public function getFileURL($field, $file = 'file')
    {
        return trans_url('file/' . $this->config . '/' . ($this->upload_folder) . '/' . $field . '/' . $file);
    }

    /**
     * Set single file field after upload.
     *
     * @param srting $field
     * @param srting $value
     *
     * @return null
     */
    public function setFiles($field, $current)
    {

        if (empty($current)) {
            $current = [];
        }

        if (empty($current) && !Request::has($field)) {
            return;
        }

        if (Request::has($field)) {
            $prev = Request::get($field);
        } else {
            $prev = $this->getOriginalFile($field);
        }

        if (empty($prev)) {
            $prev = [];
        }

        $files = array_merge($current, $prev);

        $files = array_slice($files, 0, $this->getUploadFileCount($field));

        $this->setAttribute($field, $files);
    }

    /**
     * Get the original value of file field.
     *
     * @param string $field
     *
     * @return array
     */
    public function getOriginalFile($field)
    {
        $original = parent::getOriginal($field);

        return json_decode($original);
    }

    /**
     * Get the original value of file field.
     *
     * @param string $field
     *
     * @return array
     */
    public function getUploadFileCount($field)
    {
        return $this->uploads[$field]['count'];
    }

    /**
     * Return the main image for the record.
     *
     * @param type|string $size
     * @param type|string $field
     *
     * @return string path
     */
    public function defaultImage($field, $size = 'original', $pos = 0)
    {
        $image = $this->$field;

        if (!is_array($image) || empty($image)) {
            return 'img/default/' . $size . '.jpg';
        }

        $image = array_pull($image, $pos, head($image));

        return "image/{$size}/" . ($image['path']);
    }

    /**
     * Return the array of images for the data.
     *
     * @param type|string $size
     * @param type|string $field
     *
     * @return string path
     */
    public function getImages($field, $size = 'sm')
    {
        $image = $this->$field;

        if (!is_array($image) || empty($image)) {
            return ['img/default/' . $size . '.jpg'];
        }

        foreach ($image as $key => $img) {
            $image[$key] = "image/{$size}" . ($img['path']);
        }

        return $image;
    }

    /**
     * Return the main image for the record.
     *
     * @param type|string $field
     * @param type|bool $multiple
     * @param type|bool $download
     *
     * @return array path
     */
    public function getFile($field, $multiple = false,$download = false)
    {
        $files = $this->$field;

        $prefix = ($download) ? 'original' : 'download';

        if (empty($files)) {
            return [];
        }

        if($multiple)
        {
            $files = explode(',',$files);
        }

        if(!is_array($files)){
            return [
                'url' => url("{$prefix}/" . $files),
                'path' => $files,
            ];
        }

        $data = [];

        foreach ($files as $key => $file) {
            $data[$key]['url'] = url("{$prefix}/" . $file);
            $data[$key]['path'] = $file;
        }

        return $data;
    }

    /**
     * Display files inside a form.
     *
     * @param type|string $field
     * @param type|bool $multiple
     * @return string path
     */
    public function files($field,$multiple=false)
    {
        $form = new Forms($field, $this->config, $this->getFile($field,$multiple));
        return $form;
    }

}
