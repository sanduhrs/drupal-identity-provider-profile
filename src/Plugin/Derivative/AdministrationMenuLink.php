<?php

namespace Drupal\identity_provider\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Extension\ModuleHandler;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Derivative class that provides the menu links for the Products.
 */
class AdministrationMenuLink extends DeriverBase implements ContainerDeriverInterface {

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandler
   */
  protected ModuleHandler $moduleHandler;

  /**
   * Creates a ProductMenuLink instance.
   *
   * @param string $base_plugin_id
   *   The base plugin id string.
   * @param \Drupal\Core\Extension\ModuleHandler $module_handler
   *   The module handler.
   */
  public function __construct(
      $base_plugin_id,
      ModuleHandler $module_handler
  ) {
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
      ContainerInterface $container,
      $base_plugin_id
  ) {
    return new static(
      $base_plugin_id,
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) {
    $links = [];

    // Management menu items.
    if ($this->moduleHandler->moduleExists('user')) {
      $links['identity_provider.entity_user_collection'] = [
        'title' => 'People',
        'description' => 'Manage user accounts, roles, and permissions.',
        'route_name' => 'entity.user.collection',
        'parent' => 'identity_provider.management',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('flood_control')) {
      $links['identity_provider.flood_control_unblock_form'] = [
        'title' => 'Flood',
        'description' => 'Manage flood blocks.',
        'route_name' => 'flood_control.unblock_form',
        'parent' => 'identity_provider.management',
        'weight' => '10',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('ban')) {
      $links['identity_provider.ban'] = [
        'title' => 'Bans',
        'description' => 'Manage bans from specific IP addresses.',
        'route_name' => 'ban.admin_page',
        'parent' => 'identity_provider.management',
        'weight' => '0',
      ] + $base_plugin_definition;
    }

    // Settings menu items.
    if ($this->moduleHandler->moduleExists('system')) {
      $links['identity_provider.site_settings'] = [
        'title' => 'Basic site settings',
        'description' => 'Change site name, email address, slogan, default front page and error pages.',
        'route_name' => 'system.site_information_settings',
        'weight' => '0',
        'parent' => 'identity_provider.settings',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('user')) {
      $links['identity_provider.entity_user_admin_form'] = [
        'title' => 'Account settings',
        'description' => 'Configure default user account settings, including fields, registration requirements, and email messages.',
        'route_name' => 'entity.user.admin_form',
        'parent' => 'identity_provider.settings',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('flood_control')) {
      $links['identity_provider.flood_control'] = [
        'title' => 'Flood control',
        'description' => 'Configure hidden flood control variables, like the login attempt limiters.',
        'route_name' => 'flood_control.settings',
        'parent' => 'identity_provider.settings',
        'weight' => '0',
      ] + $base_plugin_definition;
    }

    // Login menu items.
    if ($this->moduleHandler->moduleExists('login_security')) {
      $links['identity_provider.login_security'] = [
        'title' => 'Login security',
        'description' => 'Enable security options in the login flow of the site.',
        'route_name' => 'login_security.settings',
        'parent' => 'identity_provider.login',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('mail_login')) {
      $links['identity_provider.mail_login_admin'] = [
        'title' => 'Mail Login',
        'description' => 'Configure mail login options.',
        'route_name' => 'mail_login.admin',
        'parent' => 'identity_provider.login',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('password_policy')) {
      $links['identity_provider.password_policy'] = [
        'title' => 'Password policy',
        'description' => 'Create password policies.',
        'route_name' => 'entity.password_policy.collection',
        'parent' => 'identity_provider.login',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('tfa')) {
      $links['identity_provider.tfa'] = [
        'title' => 'Two-factor Authentication',
        'description' => 'TFA process and plugin settings.',
        'route_name' => 'tfa.settings',
        'parent' => 'identity_provider.login',
        'weight' => '0',
      ] + $base_plugin_definition;
    }

    // Webservice menu items.
    if ($this->moduleHandler->moduleExists('simple_oauth')) {
      $links['identity_provider.simple_oauth'] = [
        'title' => 'Simple OAuth',
        'description' => 'Configure the Simple OAuth settings and manage entities.',
        'route_name' => 'oauth2_token.settings',
        'parent' => 'identity_provider.webservice',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('webhooks')) {
      $links['identity_provider.entity_webhook_config_collection'] = [
        'title' => 'Webhooks',
        'description' => 'Manage Webhooks.',
        'route_name' => 'entity.webhook_config.collection',
        'parent' => 'identity_provider.webservice',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('simple_oauth')) {
      $links['identity_provider.consumers'] = [
        'title' => 'Consumers',
        'description' => 'Register and configure the decoupled consumers to your API.',
        'route_name' => 'entity.consumer.collection',
        'parent' => 'identity_provider.webservice',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('openapi')) {
      $links['identity_provider.openapi_downloads'] = [
        'title' => 'OpenAPI Resources',
        'description' => 'Manage OpenAPI Resources.',
        'route_name' => 'openapi.downloads',
        'parent' => 'identity_provider.webservice',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('jsonapi_extras')) {
      $links['identity_provider.jsonapi_extras_settings'] = [
        'title' => 'JSON:API Extras',
        'description' => 'Manage JSON:API Resources.',
        'route_name' => 'jsonapi_extras.settings',
        'parent' => 'identity_provider.webservice',
        'weight' => '0',
      ] + $base_plugin_definition;
    }

    // Other menu items.
    if ($this->moduleHandler->moduleExists('encrypt')) {
      $links['identity_provider.encrypt'] = [
        'title' => 'Encryption Profiles',
        'description' => 'Manage profiles that can be used to encrypt and decrypt data.',
        'route_name' => 'entity.encryption_profile.collection',
        'parent' => 'identity_provider.other',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('key')) {
      $links['identity_provider.keys'] = [
        'title' => 'Keys',
        'description' => 'Manage site-wide keys.',
        'route_name' => 'entity.key.collection',
        'parent' => 'identity_provider.other',
        'weight' => '0',
      ] + $base_plugin_definition;
    }
    if ($this->moduleHandler->moduleExists('gin_login')) {
      $links['identity_provider.gin_login'] = [
        'title' => 'Gin Login',
        'description' => 'Gin Login.',
        'route_name' => 'gin_login.configuration_form',
        'parent' => 'identity_provider.other',
        'weight' => '0',
      ] + $base_plugin_definition;
    }

    return $links;
  }

}
