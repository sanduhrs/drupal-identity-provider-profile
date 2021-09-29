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
    '_identity_provider_enable_cors' => [
      'display_name' => t('Enable CORS by default'),
    ],
  ];
  return $tasks;
}

/**
 * Generates the OAuth Keys.
 */
function _identity_provider_generate_keys() {
  // Build all the dependencies manually to avoid having to rely on the
  // container to be ready.
  $dir_name = 'keys';
  /** @var \Drupal\simple_oauth\Service\KeyGeneratorService $key_gen */
  $key_gen = \Drupal::service('simple_oauth.key.generator');
  /** @var \Drupal\simple_oauth\Service\Filesystem\FileSystemChecker $file_system_checker */
  $file_system_checker = \Drupal::service('simple_oauth.filesystem_checker');
  /** @var \Drupal\Core\File\FileSystem $file_system */
  $file_system = \Drupal::service('file_system');
  /** @var \Drupal\Core\Logger\LoggerChannelInterface $logger */
  $logger = \Drupal::service('logger.factory')->get('identity_provider');

  $relative_path = DRUPAL_ROOT . '/../' . $dir_name;
  if (!$file_system_checker->isDirectory($relative_path)) {
    $file_system->mkdir($relative_path);
  }
  $keys_path = $file_system->realpath($relative_path);
  $pub_filename = sprintf('%s/public.key', $keys_path);
  $pri_filename = sprintf('%s/private.key', $keys_path);
  $enc_filename = sprintf('%s/encrypt.key', $keys_path);

  if ($file_system_checker->fileExist($pub_filename) && $file_system_checker->fileExist($pri_filename)) {
    // 1. If the file already exists, then just set the correct permissions.
    $file_system->chmod($pub_filename, 0600);
    $file_system->chmod($pri_filename, 0600);
    $logger->info('Key pair for OAuth 2 token signing already exists.');
  }
  else {
    // 2. Generate the pair in the selected directory.
    try {
      $key_gen->generateKeys($keys_path);
    } catch (\Exception $e) {
      // Unable to generate files after all.
      $logger->error($e->getMessage());
      return;
    }
  }

  if ($file_system_checker->fileExist($enc_filename)) {
    // 1. If the file already exists, then just set the correct permissions.
    $file_system->chmod($enc_filename, 0600);
    $logger->info('Encryption key already exists.');
  }
  else {
    // 2. Generate the key in the selected directory.
    try {
      $data = base64_encode(random_bytes(32));
      file_put_contents($enc_filename, $data);
      $file_system->chmod($enc_filename, 0600);
    } catch (\Exception $e) {
      // Unable to generate file after all.
      $logger->error($e->getMessage());
      return;
    }
  }
}

/**
 * Alters the services.yml to enable CORS by default.
 */
function _identity_provider_enable_cors() {
  // Enable CORS for localhost.
  /** @var \Drupal\Core\DrupalKernelInterface $drupal_kernel */
  $drupal_kernel = \Drupal::service('kernel');
  $file_path = $drupal_kernel->getAppRoot() . '/' . $drupal_kernel->getSitePath();
  $filename = $file_path . '/services.yml';
  if (file_exists($filename)) {
    $services_yml = file_get_contents($filename);

    $yml_data = Yaml::decode($services_yml);
    if (empty($yml_data['parameters']['cors.config']['enabled'])) {
      $yml_data['parameters']['cors.config']['enabled'] = TRUE;
      $yml_data['parameters']['cors.config']['allowedHeaders'] = ['*'];
      $yml_data['parameters']['cors.config']['allowedMethods'] = ['*'];
      $yml_data['parameters']['cors.config']['allowedOrigins'] = ['localhost'];
      $yml_data['parameters']['cors.config']['allowedOriginsPatterns'] = ['/localhost:\d+/'];

      file_put_contents($filename, Yaml::encode($yml_data));
    }
  }
}
