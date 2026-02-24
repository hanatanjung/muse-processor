<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class Ppt extends BaseController
{
    use ResponseTrait;

    protected $helpers = ['url', 'form'];

    public function index()
    {
        return view('upload-images');
    }

    public function upload()
    {
        $images = $this->request->getFileMultiple('images');
        $stored_images = [];
        foreach($images as $img)
        {
            if ($img->isValid() && ! $img->hasMoved())
            {
                $name = $img->getName();
                $number_string = explode('_', $name)[1];
                $number_array = explode('-', $number_string);
                $verse_num = intval($number_array[0]);
                $page_num = isset($number_array[1]) ? intval($number_array[1]) : 1;

                $targetPath = ROOTPATH . 'public/temp';
                $img->move($targetPath);

                $stored_images[$verse_num][$page_num] = [
                    'url'  => base_url('temp/' . $name),
                    'path' => $targetPath . '/' . $name
                ];
            }
        }

        $firstImagePath = $stored_images[1][1]['path'];
        list($width, $height) = getimagesize($firstImagePath);

        return $this->setResponseFormat('json')->respond([
            'success' => true,
            'title' => $this->request->getVar('filename'),
            'files' => $stored_images,
            'width' => $width,
            'height' => $height
        ]);
    }

    public function delete()
    {
        $filenames = $this->request->getJSON(true);

        foreach ($filenames as $pages) {
            foreach ($pages as $file) {
                if (isset($file['path']) && file_exists($file['path'])) {
                    unlink($file['path']);
                }
            }
        }

        return $this->setResponseFormat('json')->respond([
            'success' => true,
        ]);
    }
}
