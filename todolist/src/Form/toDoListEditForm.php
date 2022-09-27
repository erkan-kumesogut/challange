<?php

/**
 * @file 
 * 
 * Generates a form for editing selected content.
 * Results from toDOFormList clicked it used ajax to insert an input from this form. 
 * 
 */

namespace Drupal\todolist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;


class toDoListEditForm extends FormBase
{

  public function getFormId()
  {
    return 'toDoList_edit_form';
  }


  public function buildForm(array $form, FormStateInterface $form_state, $data = NULL)
  {
    $id = $form_state->getValue('id');


    $status = $form_state->getValue('status');
    $uid = \Drupal::currentUser()->id();
    $task = $form_state->getValue('task');


    $form['status'] = [
      '#type' => 'checkbox',
      '#default_value' => (isset($data['status'])) ? $data['status'] : $status,
      '#attributes' => array(
        'class' => ['txt-class'],
      ),
      '#prefix' => '<div class="update-status">',
      '#suffix' => '</div>',
      '#ajax' => [
        'callback' => '::changeTaskStatus',
        'event' => 'click',
      ],


    ];

    $form['task'] = [
      '#type' => 'textfield',
      '#size' => 255,
      '#required' => TRUE,
      '#attributes' => array(
        'class' => ['txt-class'],
      ),
      '#prefix' => '<div class="update-task">',
      '#suffix' => '</div>',
      '#default_value' => (isset($data['task'])) ? $data['task'] : $task,
      '#ajax' => [
        'callback' => '::saveData',
        'keypress' => true,
        'disable-refocus' => true,
      ],
    ];

    $form['id'] = [
      '#type' => 'hidden',
      '#attributes' => array(
        'class' => ['txt-class'],
      ),
      '#default_value' => (isset($data['id'])) ? $data['id'] : $id,

    ];

    $form['uid'] = [
      '#type' => 'hidden',
      '#attributes' => array(
        'class' => ['txt-class'],
      ),
      '#default_value' => (isset($data['uid'])) ? $data['uid'] : $uid,

    ];

    $form['#prefix'] = '<div class="form-div-edit" id="form-div-edit">';
    $form['#suffix'] = '</div>';
    return $form;
  }

  public function validateForm(array &$form, FormStateInterface $form_state)
    {
       
    }



  public function saveData(array $form, FormStateInterface $form_state)
  {
    //update task content
    $response = new AjaxResponse();
    $uid = \Drupal::currentUser()->id();

    $id = $form_state->getValue('id');
    $task = $form_state->getValue('task');

    $updated = \Drupal::time()->getRequestTime();

    if (!is_null($task) && $task != '') {

      $query = \Drupal::database()
        ->update('todolist')
        ->fields([
          'task' => $task,
          'updated' => $updated,
        ])
        ->condition('id', $id, '=')
        ->condition('uid', $uid, '=');
      $query->execute();
    }
    // after process done render the list form to show updated list
    $render_array = \Drupal::formBuilder()->getForm('Drupal\todolist\Form\toDoListFormList');
    $response->addCommand(new HtmlCommand('.result_area', ''));
    $response->addCommand(new \Drupal\Core\Ajax\PrependCommand('.result_area', $render_array));

    return $response;
  }

  public function changeTaskStatus(array $form, FormStateInterface $form_state)
  {


    // update task status
    $response = new AjaxResponse();
    $uid = \Drupal::currentUser()->id();
    $id = $form_state->getValue('id');
    $task = $form_state->getValue('task');
    $status = $form_state->getValue('status');


    $updated = \Drupal::time()->getRequestTime();
    if (!is_null($task) && $task != '') {
      $query = \Drupal::database()
        ->update('todolist')
        ->fields([
          'status' => $status,
          'updated' => $updated,
        ])
        ->condition('id', $id, '=')
        ->condition('uid', $uid, '=');
      $query->execute();
    }
    // after process done render the list form to show updated list
    $render_array = \Drupal::formBuilder()->getForm('Drupal\todolist\Form\toDoListFormList');
    $response->addCommand(new HtmlCommand('.result_area', ''));
    $response->addCommand(new \Drupal\Core\Ajax\PrependCommand('.result_area', $render_array));

    return $response;
  }

  
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
  }
}
