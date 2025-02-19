<?php

namespace Drupal\user_list\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\user\Entity\User;

class UserApiService {

  protected $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function getUsers($filters = [], $page = 1, $limit = 5) {
    $query = $this->entityTypeManager->getStorage('user')->getQuery()
      ->condition('status', 1) // Only active users
      ->condition('uid', 1, '!=') // Exclude Super Admin (uid 1)
      ->accessCheck(TRUE)
      ->range(($page - 1) * $limit, $limit)
      ->sort('uid', 'ASC');

    $uids = $query->execute();

    if (empty($uids)) {
      return [
        'usuarios' => [],
        'total' => 0,
        'current_page' => $page,
        'per_page' => $limit,
      ];
    }

    $users = $this->entityTypeManager->getStorage('user')->loadMultiple($uids);
    $user_list = [];

    foreach ($users as $user) {
      $user_list[] = [
        'name' => $user->hasField('field_first_name') ? $user->get('field_first_name')->value : 'N/A',
        'surname1' => $user->hasField('field_last_name') ? $user->get('field_last_name')->value : 'N/A',
        'surname2' => $user->hasField('field_second_last_name') ? $user->get('field_second_last_name')->value : 'N/A',
        'email' => $user->getEmail(),
      ];
    }

    return [
      'usuarios' => $user_list,
      'total' => count($uids),
      'current_page' => $page,
      'per_page' => $limit,
    ];
  }
}
