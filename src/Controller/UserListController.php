<?php

namespace Drupal\user_list\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\user_list\Service\UserApiService;

class UserListController extends ControllerBase {

  protected $userApiService;

  public function __construct(UserApiService $userApiService) {
    $this->userApiService = $userApiService;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('user_list.user_api_service'));
  }

  public function content() {
    $users = $this->userApiService->getUsers();
    return [
      '#theme' => 'user_list',
      '#users' => $users['usuarios'],
    ];
  }

  public function ajaxCallback() {
    $response = new \Drupal\Core\Ajax\AjaxResponse();
    $users = $this->userApiService->getUsers();
    
    $response->addCommand(new \Drupal\Core\Ajax\ReplaceCommand(
      '#user-list-container',
      [
        '#theme' => 'user_list',
        '#users' => $users['usuarios'],
      ]
    ));
  
    return $response;
  }  
}
