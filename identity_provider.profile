<?php

/**
 * @file
 * Enables modules and site configuration.
 */

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Serialization\Yaml;

/**
 * Implements hook_form_FORM_ID_alter().
 */
function identity_provider_form_install_configure_form_alter(&$form, FormStateInterface $form_state) {
  // Add a value as example that one can choose an arbitrary site name.
  $form['site_information']['site_name']['#placeholder'] = t('Identity Provider');
}

/**
 * Implements hook_install_tasks().
 */
function identity_provider_install_tasks(&$install_state) {
  $tasks = [
    '_identity_provider_generate_keys' => [
      'display_name' => t('Generate OAuth 2 and encryption keys'),
    ],
  ];
  return $tasks;
}

/**
 * Implements hook_install_tasks_alter();
 */
function identity_provider_install_tasks_alter(&$tasks, $install_state) {
  $weighted_tasks = [
    '_identity_provider_generate_keys' => $tasks['_identity_provider_generate_keys'],
  ];
  unset($tasks['_identity_provider_generate_keys']);
  $tasks = array_merge($weighted_tasks, $tasks);
}

/**
 * Generates the OAuth Keys.
 */
function _identity_provider_generate_keys() {
  $relative_path = DRUPAL_ROOT . '/../keys/';
  if (!is_dir($relative_path)) {
    mkdir($relative_path);
  }

  if (file_exists($relative_path)
      && file_exists($relative_path . 'keys/private.key')
  ) {
    // 1. If the file already exists, then just set the correct permissions.
    chmod($relative_path . 'private.key', 0600);
    error_log('Key pair for OAuth 2 token signing already exists.');
  }
  else {
    // 2. Generate the pair in the selected directory.
    try {
      error_log('generate keys');

      // Generate Resource.
      $resource = openssl_pkey_new([
        "digest_alg" => "sha512",
        "private_key_bits" => 4096,
        "private_key_type" => OPENSSL_KEYTYPE_RSA,
      ]);

      // Get Private Key.
      openssl_pkey_export($resource, $pkey);
      // Get Public Key.
      $pubkey = openssl_pkey_get_details($resource);

      file_put_contents($relative_path . 'public.key', $pubkey['key']);
      file_put_contents($relative_path . 'private.key', $pkey);

      chmod($relative_path . 'public.key', 0600);
      chmod($relative_path . 'private.key', 0600);
    } catch (\Exception $e) {
      // Unable to generate files after all.
      error_log('not able to generate keys');
      return;
    }
  }

  if (file_exists($relative_path . 'encrypt.key')) {
    // 1. If the file already exists, then just set the correct permissions.
    chmod($relative_path . 'keys/encrypt.key', 0600);
  }
  else {
    // 2. Generate the key in the selected directory.
    try {
      $data = base64_encode(random_bytes(32));
      file_put_contents($relative_path . 'encrypt.key', $data);

      chmod($relative_path . 'encrypt.key', 0600);
    } catch (\Exception $e) {
      // Unable to generate file after all.
      error_log($e->getMessage());
      return;
    }
  }
}
