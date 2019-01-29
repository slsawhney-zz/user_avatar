<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Drupal\user_avatar;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException;
use Drupal\Core\Link;

class UserAvatar
{
    protected $avatarApiUrl;
    protected $avatarSize;

    /**
     * The HTTP client to fetch the Image data with.
     *
     * @var \GuzzleHttp\ClientInterface
     */
    protected $httpClient;

    /**
     * Constructs a new UserAvatar object.
     *
     * @param \GuzzleHttp\ClientInterface $http_client
     *                                                 The Guzzle HTTP client
     */
    public function __construct(ClientInterface $http_client)
    {
        $this->httpClient = $http_client;
        $this->avatarApiUrl = \Drupal::config('user_avatar.adminsettings')->get('avatar_api_url');
        $this->avatarSize = \Drupal::config('user_avatar.adminsettings')->get('avatar_size');
    }

    /**
     * @param type Object
     */
    public function setUserAvatar($account)
    {
        $userEmail = $account->getEmail();
        $avatarPath = $this->avatarApiUrl.'/'.$this->avatarSize.'/'.$userEmail.'.png';

        try {
            $request = $this->httpClient->get($avatarPath, [
                        'headers' => [
                            'Content-Type' => 'text/plain',
                        ],
                    ]);
            $imageData = $request->getBody()->getContents();
            if ($imageData) {
                $picture_directory = file_default_scheme().'://pictures';

                file_prepare_directory($picture_directory, FILE_CREATE_DIRECTORY);
                $file = file_save_data($imageData, $picture_directory.'/'.$userEmail.'.png', FILE_EXISTS_RENAME);

                $avatarInformation = array(
                    'target_id' => $file->id(),
                    'alt' => 'User Avatar',
                );
                $account->set('user_picture', $avatarInformation);
            } else {
                $avatarConfigurationLink = Link::fromTextAndUrl(
                                            'Avatar Configuration',
                                            \Drupal\Core\Url::fromUri('internal:/admin/config/user_avatar/adminsettings')
                                        )->toString();
                drupal_set_message(t(
                    'An error occurred in Avatar processing at provided url (@avatarPath). Please check your @avatarConfigurationLink.',
                    array(
                        '@avatarPath' => $avatarPath,
                        '@avatarConfigurationLink' => $avatarConfigurationLink,
                    )), 'warning');
            }
        } catch (ClientException $e) {
            watchdog_exception('user_avatar', $e->getMessage());
        }
    }
}
