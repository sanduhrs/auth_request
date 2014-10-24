<?php

/**
 * @file
 * Contains \Drupal\auth_request\AuthRequestAuth.
 */

namespace Drupal\auth_request;

use \Drupal\Core\Entity\EntityManagerInterface;
use \Drupal\user\UserAuthInterface;
use \Drupal\Core\Password\PasswordInterface;
use \Drupal\Core\Http\Client;
use \GuzzleHttp;
use \GuzzleHttp\Exception\ClientException;
use \Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Validates user authentication credentials.
 */
class AuthRequestAuth implements UserAuthInterface {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The password service.
   *
   * @var \Drupal\Core\Password\PasswordInterface
   */
  protected $passwordChecker;

  /**
   * The http client service.
   *
   * @var \Drupal\Core\Http\Client
   */
  protected $http_client;

  /**
   * Constructs a AuthRequestAuth object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The user storage.
   * @param \Drupal\Core\Password\PasswordInterface $password_checker
   *   The password service.
   * @param \Drupal\Core\Http\Client $http_client
   *   The password service.
   */
  public function __construct(EntityManagerInterface $entity_manager, PasswordInterface $password_checker, Client $http_client) {
    $this->entityManager = $entity_manager;
    $this->passwordChecker = $password_checker;
    $this->http_client = $http_client;
  }

  /**
   * {@inheritdoc}
   */
  public function authenticate($username, $password) {
    $uid = FALSE;

    $client = $this->http_client;
    try {
      // @todo Make configurable
      $client->get('http://109.239.48.8:8090/auth', [
        'auth' => [$username, $password],
      ]);

      // Successful authentication.
      // Try to load local account
      $account_search = $this->entityManager->getStorage('user')->loadByProperties(['name' => $username]);
      if ($account = reset($account_search)) {
        // Local account exists
        if ($account->id() > 0) {
          $uid = $account->id();
        }
      }
      else {
        // Create local account
        $this->entityManager->getStorage('user')
          ->create([
            'name' => $username,
            'status' => 1,
          ])
          ->enforceIsNew(TRUE)
          ->save();

        // Load local account
        $account_search = $this->entityManager->getStorage('user')->loadByProperties(['name' => $username]);
        if ($account = reset($account_search)) {
          if ($account->id() > 0) {
            $uid = $account->id();
          }
        }
      }
    }
    catch (ClientException $e) {
      // Unsuccessful authentication
    }

    return $uid;
  }

}
