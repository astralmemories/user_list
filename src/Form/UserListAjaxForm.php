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
    $form['user_list_wrapper'] = [
      '#type' => 'container',
      '#attributes' => ['id' => 'user-list-wrapper'],
    ];

    $form['actions'] = [
      '#type' => 'button',
      '#value' => $this->t('Load Users'),
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
        '#form' => $form,
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
      $user_list[] = [
        'name' => $user->getDisplayName(),
        'surname1' => 'N/A', // Modify if needed.
        'surname2' => 'N/A',
        'email' => $user->getEmail(),
      ];
    }

    return $user_list;
  }
}
