<?php

namespace Rotaz\FilamentAccounts\Providers;

use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use function Termwind\render;

class Wso2Provider extends AbstractProvider implements ProviderInterface
{


    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase('https://localhost:9443/oauth2/authorize', $state);
    }
    public function getScopes()
    {
        return ['openid'];
    }

    protected function getTokenUrl()
    {
        return 'https://localhost:9443/oauth2/token';
    }

    public function getAccessTokenResponse($code)
    {

        $response = $this->getHttpClient()->post($this->getTokenUrl(), [
            RequestOptions::HEADERS => $this->getTokenHeaders($code),
            'verify' => false,
            RequestOptions::FORM_PARAMS => $this->getTokenFields($code),
        ]);

        Log::debug('Retrived access token ' , [
            'access_token' => $response->getBody()->getContents(),
        ]);

        return json_decode($response->getBody(), true);
    }

    protected function getTokenHeaders($code)
    {
        return [
            'Content-Type' => 'application/x-www-form-urlencoded',
            'Authorization' => ' Basic ' . base64_encode($this->clientId.':'.$this->clientSecret),
        ];
    }


    protected function getUserByToken($token)
    {

     dd($token);

        return json_decode($response->getBody(), true);
    }
    protected function getTokenFields($code)
    {
            $fields = [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => $this->redirectUrl
            ];

            if ($this->usesPKCE()) {
                $fields['code_verifier'] = $this->request->session()->pull('code_verifier');
            }
            return $fields;
    }
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id'       => $user['id'],
            'nickname' => $user['display_name'],
            'name'     => $user['display_name'],
            'avatar'   => !empty($user['images']) ? $user['images'][0]['url'] : null,
        ]);
    }
}
