<?php namespace App\Controllers;

use CodeIgniter\API\ResponseTrait;
use Google\Client;
use Google\Service\Drive;

class FinalSlide extends BaseController
{
    use ResponseTrait;

    protected $helpers = ['url', 'form'];

    public function index()
    {
        $client = new Client();
        $client->setAuthConfig(FCPATH . '../src/google-client-key.json');
        $client->addScope(Drive::DRIVE);

        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $client->setRedirectUri($redirect_uri);

        return view('final-slide');
    }

    public function generate()
    {

    }
}
