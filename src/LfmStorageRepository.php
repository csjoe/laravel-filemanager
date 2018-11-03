<?php

namespace UniSharp\LaravelFilemanager;

use Illuminate\Support\Facades\Storage;
use SebastianBergmann\CodeCoverage\Report\PHP;

class LfmStorageRepository implements RepositoryContract
{
    private $disk;
    private $path;
    private $helper;

    public function __construct($storage_path, $helper)
    {
        $this->helper = $helper;
        $this->disk = Storage::disk($this->helper->config('disk'));
        $this->path = $storage_path;
    }

    public function __call($function_name, $arguments)
    {
        // TODO: check function exists
        try {
          return $this->disk->$function_name($this->path, ...$arguments);
        } catch (\Exception $exception) {
          //error_log($exception->getMessage());
          return '';
        }
    }

    public function rootPath()
    {
        // storage_path('app')
        return $this->disk->getDriver()->getAdapter()->getPathPrefix();
    }

    public function move($new_lfm_path)
    {
        return $this->disk->move($this->path, $new_lfm_path->path('storage'));
    }

    public function save($file)
    {
        $this->disk->put($this->path, file_get_contents($file));
    }

    public function url($path)
    {
        return $this->disk->url($path);
    }

    public function makeDirectory()
    {
        $this->path = $this->path . "/";
        $this->disk->makeDirectory( $this->path, ...func_get_args());
        $this->disk->setVisibility( $this->path, 'public');
    }

    public function extension()
    {
        return pathinfo($this->path, PATHINFO_EXTENSION);
    }
}
