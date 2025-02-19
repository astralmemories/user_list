<?php

namespace Drupal\user_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\user_list\Service\UserApiService;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * AJAX-enabled form to reload the user list.
 */
class UserListAjaxForm extends FormBase {

  protected $userApiService;

  public function __construct(UserApiService $userApiService) {
    $this->userApiService = $userApiService;
  }

  public static function create(ContainerInterface $container) {
    return new static($container->get('user_list.user_api_service'));
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'user_list_ajax_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Load Users'),
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'user-list-container',
        'effect' => 'fade',
      ],
    ];

    return $form;
  }

  /**
   * AJAX callback to update the user list.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    $users = $this->userApiService->getUsers();

    $render = [
      '#theme' => 'user_list',
      '#users' => $users['usuarios'],
    ];

    $response->addCommand(new ReplaceCommand('#user-list-container', $render));

    return $response;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // The form submission is handled via AJAX, so nothing needed here.
  }
}
