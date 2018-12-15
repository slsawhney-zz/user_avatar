<?php

/**
 * @file
 * Contains \Drupal\user_avatar\Form\AvatarConfigurationForm.
 */

namespace Drupal\user_avatar\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

class AvatarConfigurationForm extends ConfigFormBase
{
    /**
     * {@inheritdoc}
     */
    protected function getEditableConfigNames()
    {
        return [
            'user_avatar.adminsettings',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getFormId()
    {
        return 'user_avatar_configuration_form';
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(array $form, FormStateInterface $form_state)
    {
        $configuration = $this->config('user_avatar.adminsettings');

        $form['avatar_api_url'] = [
            '#type' => 'textfield',
            '#title' => $this->t('Avatar Api URL'),
            '#description' => $this->t('Avatar Api URL from where Avatar has to fetch, like (https://api.adorable.io/avatars). Don\'t include slashes'),
            '#default_value' => $configuration->get('avatar_api_url'),
            '#required' => true,
        ];

        $form['avatar_size'] = [
            '#type' => 'number',
            '#title' => $this->t('Avatar Size'),
            '#min' => 10,
            '#description' => $this->t('Avatar Size (integer only)'),
            '#default_value' => $configuration->get('avatar_size'),
            '#required' => true,
        ];

        return parent::buildForm($form, $form_state);
    }

    /**
     * {@inheritdoc}
     */
    public function submitForm(array &$form, FormStateInterface $form_state)
    {
        parent::submitForm($form, $form_state);

        $this->config('user_avatar.adminsettings')
            ->set('avatar_api_url', $form_state->getValue('avatar_api_url'))
            ->set('avatar_size', $form_state->getValue('avatar_size'))
            ->save();
    }
}
