<?php

namespace App\Services;

use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;
use Illuminate\Support\Facades\Log;

class AzureProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * The scopes being requested.
     *
     * @var array
     */
    protected $scopes = [
        'openid',
        'profile',
        'email',
        'offline_access',
        'User.Read',
        'Mail.ReadWrite',
        'Mail.Send',
        'IMAP.AccessAsUser.All',
        'SMTP.Send'
    ];

    /**
     * The separating character for the requested scopes.
     *
     * @var string
     */
    protected $scopeSeparator = ' ';

    /**
     * Get the authentication URL for the provider.
     *
     * @param string $state
     * @return string
     */
    protected function getAuthUrl($state)
    {
        return $this->buildAuthUrlFromBase($this->getBaseAuthorizationUrl(), $state);
    }

    /**
     * Get the base authorization URL.
     *
     * @return string
     */
    protected function getBaseAuthorizationUrl()
    {
        $tenant = $this->getConfig('tenant', 'common');
        return "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/authorize";
    }

    /**
     * Get the token URL for the provider.
     *
     * @return string
     */
    protected function getTokenUrl()
    {
        $tenant = $this->getConfig('tenant', 'common');
        return "https://login.microsoftonline.com/{$tenant}/oauth2/v2.0/token";
    }

    /**
     * Get the raw user for the given access token.
     *
     * @param string $token
     * @return array
     */
    protected function getUserByToken($token)
    {
        try {
            $response = $this->getHttpClient()->get(
                'https://graph.microsoft.com/v1.0/me',
                [
                    'headers' => [
                        'Authorization' => 'Bearer ' . $token,
                        'Accept' => 'application/json',
                    ],
                ]
            );

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            \Log::error('Failed to get user from Microsoft Graph', [
                'error' => $e->getMessage(),
                'token_preview' => substr($token, 0, 20) . '...'
            ]);
            throw $e;
        }
    }

    /**
     * Map the raw user array to a Socialite User instance.
     *
     * @param array $user
     * @return \Laravel\Socialite\Two\User
     */
    protected function mapUserToObject(array $user)
    {
        return (new User)->setRaw($user)->map([
            'id' => $user['id'],
            'nickname' => null,
            'name' => $user['displayName'] ?? null,
            'email' => $user['userPrincipalName'] ?? $user['mail'] ?? null,
            'avatar' => null,
        ]);
    }

    /**
     * Get a configuration value.
     *
     * @param  string  $key
     * @param  mixed  $default
     * @return mixed
     */
    protected function getConfig($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }
}
