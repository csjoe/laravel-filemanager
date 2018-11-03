<?php

namespace UniSharp\LaravelFilemanager\Controllers;

use Illuminate\Http\File;
use Intervention\Image\Facades\Image;
use UniSharp\LaravelFilemanager\Events\ImageIsCropping;
use UniSharp\LaravelFilemanager\Events\ImageWasCropped;

class CropController extends LfmController
{
    /**
     * Show crop page.
     *
     * @return mixed
     */
    public function getCrop()
    {
        return view('laravel-filemanager::crop')
            ->with([
                'working_dir' => $this->helper->request('working_dir'),
                'img' => $this->lfm->pretty($this->helper->request('img'))
            ]);
    }

    /**
     * Crop the image (called via ajax).
     */
    public function getCropimage($overWrite = true)
    {
        $image_name = $this->helper->request('img');
        $image_path = $this->lfm->setName($image_name)->path('absolute');
        $crop_path = $image_path;

        if (!$overWrite) {
            $fileParts = explode('.', $image_name);
            $fileParts[count($fileParts) - 2] = $fileParts[count($fileParts) - 2] . '_cropped_' . time();
            $crop_path = $this->lfm->setName(implode('.', $fileParts))->path('absolute');
        }

        event(new ImageIsCropping($image_path));

        $crop_info = $this->helper->request_only(['dataWidth', 'dataHeight', 'dataX', 'dataY']);
        $sys_crop_path = sys_get_temp_dir() . "/" . $image_name;

        // crop image
        $original_image = $this->lfm->pretty($image_name);
        Image::make($original_image->get())
            ->crop(...array_values($crop_info))
            ->save($sys_crop_path);
        $file = new File($sys_crop_path);

        $this->lfm->setName(str_replace($this->lfm->path('url'),'', $crop_path))->thumb(false)->storage->save($file);

        // make new thumbnail
        $this->lfm->makeThumbnail($image_name);

        event(new ImageWasCropped($image_path));
    }

    public function getNewCropimage()
    {
        $this->getCropimage(false);
    }
}
