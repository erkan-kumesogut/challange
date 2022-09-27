<?php

/**
 * @file
 * 
 * Main controller that manages logic between all 3 forms
 * 
 * it display/edit/delete records.
 *  
 */

namespace Drupal\todolist\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Form\FormBuilder;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\HtmlCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Ajax\PrependCommand;



class toDoListController extends ControllerBase
{

  protected $formBuilder;

  public function __construct(FormBuilder $formBuilder)
  {
    $this->formBuilder = $formBuilder;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('form_builder')
    );
  }

  public function displayTasks()
  {
    // displays add form and task list  
    $form['form'] = $this->formBuilder()->getForm('Drupal\todolist\Form\toDoListForm');
    $render_array = $this->formBuilder()->getForm('Drupal\todolist\Form\toDoListFormList');
    $form['form1'] = $render_array;

    return $form;

  }

  public function deleteTask($id)
  {
    //deletes selected task
    $query = \Drupal::database()
      ->delete('todolist')
      ->condition('id', $id)
      ->execute();

      // after process done render the list form to show updated list
      $response = new AjaxResponse();
      $render_array = \Drupal::formBuilder()->getForm('Drupal\todolist\Form\toDoListFormList');
      
      $response->addCommand(new HtmlCommand('.result_area', ''));
      $response->addCommand(new \Drupal\Core\Ajax\PrependCommand('.result_area', $render_array));
      return $response;
  }
  public function editTask($id)
  {
    //edits selected task 
    $query = \Drupal::database()->select('todolist', 't')
      ->fields('t', array('id', 'uid', 'task', 'status', 'created'))
      ->condition('id', $id, '=')
      ->orderBy('id', 'DESC')
      ->execute()->fetchAssoc();
// after process done render the list form to show updated list
    $render_array = \Drupal::formBuilder()->getForm('Drupal\todolist\Form\toDoListEditForm', $query);
    
    $response = new AjaxResponse();

    $response->addCommand(new PrependCommand('#task-' . $id, $render_array));
    return $response;
  }
}
