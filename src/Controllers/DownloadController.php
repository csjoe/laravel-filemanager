<?php

namespace UniSharp\LaravelFilemanager\Controllers;

class DownloadController extends LfmController
{
    public function getDownload()
    {
        return response()->download($this->lfm->setName($this->helper->request('file'))->url());
    }
}
