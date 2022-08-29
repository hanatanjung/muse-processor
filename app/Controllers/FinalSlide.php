<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;

class FinalSlide extends BaseController
{
    use ResponseTrait;

    protected $helpers = ['url', 'form'];

    public function index()
    {
        return view('final-slide');
    }

    public function generate()
    {

    }
}
