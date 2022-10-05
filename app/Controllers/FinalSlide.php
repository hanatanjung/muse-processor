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
        $client->setAuthConfig(FCPATH . '../src/credentials/google-oauth.json');
        $client->setScopes('email');
        $client->addScope(Drive::DRIVE);

        $redirect_uri = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
        $client->setRedirectUri($redirect_uri);

        // add "?logout" to the URL to remove a token from the session
        if (isset($_REQUEST['logout'])) {
            unset($_SESSION['token']);
        }

        /************************************************
         * If we have a code back from the OAuth 2.0 flow,
         * we need to exchange that with the
         * Google\Client::fetchAccessTokenWithAuthCode()
         * function. We store the resultant access token
         * bundle in the session, and redirect to ourself.
         ************************************************/
        if (isset($_GET['code'])) {
            $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
            $client->setAccessToken($token);

            // store in the session also
            $_SESSION['token'] = $token;

            // redirect back to the example
            header('Location: ' . filter_var($redirect_uri, FILTER_SANITIZE_URL));
        }

        // set the access token as part of the client
        if (!empty($_SESSION['token'])) {
            $client->setAccessToken($_SESSION['token']);
            if ($client->isAccessTokenExpired()) {
                unset($_SESSION['token']);
            }
        } else {
            $authUrl = $client->createAuthUrl();
        }

        if ($client->getAccessToken()) {
            $token_data = $client->verifyIdToken();
        }

        $service = new Drive($client);

        return view('final-slide', [
            'authUrl' => $authUrl ?? '',
            'files' => $service ? $service->files->listFiles([
                'driveId' => '1UVt8oGJu-5w5KPZD5VYnXKUgiDuCXMdy',
                'fields' => 'files(id,size)'
            ]) : false
        ]);
    }

    public function generate()
    {

    }
}
