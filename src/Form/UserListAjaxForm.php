<?php

namespace Drupal\user_list\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;

/**
 * Class UserListAjaxForm.
 */
class UserListAjaxForm extends FormBase {

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
    $users = $this->getUserList();

    $form['user_list_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'user-list-wrapper'],
      '#children' => [
        '#theme' => 'user_list',
        '#users' => $users,
        '#form' => $form,
      ],
    ];

    $form['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Cargar usuarios'),
      '#ajax' => [
        'callback' => '::ajaxCallback',
        'wrapper' => 'user-list-wrapper',
        'effect' => 'fade',
      ],
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // No submission logic needed.
  }

  /**
   * AJAX callback function.
   */
  public function ajaxCallback(array &$form, FormStateInterface $form_state) {
    \Drupal::logger('user_list')->debug('AJAX callback triggered.');

    try {
      $response = new AjaxResponse();
      $users = $this->getUserList();

      \Drupal::logger('user_list')->debug('Fetched users: ' . print_r($users, TRUE));

      $rendered_list = [
        '#theme' => 'user_list',
        '#users' => $users,
      ];

      $response->addCommand(new ReplaceCommand('#user-list-wrapper', \Drupal::service('renderer')->render($rendered_list)));

      return $response;
    } catch (\Exception $e) {
      \Drupal::logger('user_list')->error('AJAX error: ' . $e->getMessage());
      \Drupal::logger('user_list')->error('Stack trace: ' . $e->getTraceAsString());
      return new AjaxResponse(); // Prevent crashing the request.
    }
  }

  /**
   * Helper function to fetch users from the database.
   */
  private function getUserList() {
    $query = \Drupal::entityTypeManager()->getStorage('user')->getQuery();
    $query->condition('status', 1); // Only active users.
    $query->accessCheck(TRUE); // Explicitly check access permissions.

    $uids = $query->execute();

    if (empty($uids)) {
      return [];
    }

    $users = \Drupal::entityTypeManager()->getStorage('user')->loadMultiple($uids);

    $user_list = [];
    foreach ($users as $user) {
      $surname1 = $user->get('field_surname1')->value;
      $surname2 = $user->get('field_surname2')->value;

      $user_list[] = [
        'name' => $user->getDisplayName(),
        'surname1' => $surname1 ? $surname1 : 'N/A',
        'surname2' => $surname2 ? $surname2 : 'N/A',
        'email' => $user->getEmail(),
      ];
    }

    return $user_list;
  }
}
