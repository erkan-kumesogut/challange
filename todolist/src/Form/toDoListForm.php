<?php

/**
 * @file 
 * 
 * a form collects entries to create a to do list
 * 
 * it also manages varuous filtes
 * 
 * 
 */

namespace Drupal\todolist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\InvokeCommand;
use Drupal\Core\Ajax\HtmlCommand;


class toDoListForm extends FormBase
{


    public function getFormId()
    {
        return 'toDoList_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state)
    {

        $uid = \Drupal::currentUser()->id();

        $form['task'] = [
            '#type' => 'textfield',
            '#title' => 'Add a new tasks to your toDo List',
            '#size' => 255,
            '#description' => t('Your entry will be added to toDo list!'),
            '#required' => TRUE,
            '#ajax' => [
                'callback' => '::saveData',
                'keypress' => true,
                'disable-refocus' => true
            ],
        ];


        $form['uid'] = [
            '#type' => 'hidden',
            '#value' => $uid,

        ];

        $form['status'] = [
            '#type' => 'hidden',
            '#value' => 1,

        ];

        $form['actions']['all'] = array(
            '#type' => 'button',
            '#value' => $this
                ->t('All'),
            '#prefix' => '<div class="f-buttons">',
            '#suffix' => '</div>',
            '#ajax' => [
                'callback' => '::showAll',
                'event' => 'click',
                'wrapper' => 'show-r',

            ],
        );
        
        $form['actions']['active'] = array(
            '#type' => 'button',
            '#value' => $this
                ->t('Active'),
            '#prefix' => '<div class="f-buttons">',
            '#suffix' => '</div>',
            '#ajax' => [
                'callback' => '::showActive',
                'event' => 'click',
                'wrapper' => 'show-active',
                
            ],
        );
       
        $form['actions']['completed'] = array(
            '#type' => 'button',
            '#value' => $this
                ->t('Completed'),
            '#prefix' => '<div class="f-buttons">',
            '#suffix' => '</div>',
            '#ajax' => [
                'callback' => '::showCompleted',
                'event' => 'click',
                'wrapper' => 'show-completed', 
               
            ],
        );

        $form['actions']['delete_completed'] = array(
            '#type' => 'button',
            '#value' => $this
                ->t('Delete All Completed'),
            '#prefix' => '<div class="f-buttons">',
            '#suffix' => '</div>',
            '#ajax' => [
                'callback' => '::deleteCompleted',
                'event' => 'click',
                'wrapper' => 'show-completed',
               
            ],
        );

        return $form;
    }




    public function validateForm(array &$form, FormStateInterface $form_state)
    {
  
    }






    public function saveData(array &$form, FormStateInterface $form_state)
    {
        // saves a new task and updates the list
        $uid = \Drupal::currentUser()->id();
        $task = $form_state->getValue('task');
        $status = $form_state->getValue('status');
        $created = \Drupal::time()->getRequestTime();
        


        if (!is_null($task) && $task != '') {

     
            $query = \Drupal::database()->insert('todolist');
            $query->fields([
                'uid',
                'task',
                'status',
                'created',
                'updated',
            ]);
            $query->values([
                $uid,
                $task,
                $status,
                $created,
                $created,
            ]);
            $query->execute();
        }
        $response = new AjaxResponse();


        $render_array = \Drupal::formBuilder()->getForm('Drupal\todolist\Form\toDoListFormList');

        $response->addCommand(new HtmlCommand('.result_area', ''));
        $response->addCommand(new \Drupal\Core\Ajax\PrependCommand('.result_area', $render_array));
        $response->addCommand(new InvokeCommand('#edit-task', 'val', ['']));


        return $response;
    }

    public function deleteCompleted(array &$form, FormStateInterface $form_state)
    {

        // deletes all compated tasks
        $uid = \Drupal::currentUser()->id();

        $query = \Drupal::database()
        ->delete('todolist')
        ->condition('uid', $uid)
        ->condition('status', 0)
        ->execute();
        $response = new AjaxResponse();
        $render_array = \Drupal::formBuilder()->getForm('Drupal\todolist\Form\toDoListFormList');
        
        $response->addCommand(new HtmlCommand('.result_area', ''));
        $response->addCommand(new \Drupal\Core\Ajax\PrependCommand('.result_area', $render_array));
        return $response;
      
    }



    public function showAll(array $form, FormStateInterface $form_state)
    {   
        // if filtered displays all tasks 
        $response = new AjaxResponse();

        $render_array = \Drupal::formBuilder()->getForm('Drupal\todolist\Form\toDoListFormList');

        $response->addCommand(new HtmlCommand('.result_area', ''));
        $response->addCommand(new \Drupal\Core\Ajax\PrependCommand('.result_area', $render_array));



        return $response;
    }



    public function showActive(array $form, FormStateInterface $form_state)
    {
        //filters and display active tasks only
        $response = new AjaxResponse();
        $render_array = \Drupal::formBuilder()->getForm('Drupal\todolist\Form\toDoListFormList', 1);

        $response->addCommand(new HtmlCommand('.result_area', ''));
        $response->addCommand(new \Drupal\Core\Ajax\PrependCommand('.result_area', $render_array));

        return $response;
    }

    public function showCompleted(array $form, FormStateInterface $form_state)
    {
        //filters and display complated tasks only
        $response = new AjaxResponse();

        $render_array = \Drupal::formBuilder()->getForm('Drupal\todolist\Form\toDoListFormList', 0);

        $response->addCommand(new HtmlCommand('.result_area', ''));
        $response->addCommand(new \Drupal\Core\Ajax\PrependCommand('.result_area', $render_array));

        return $response;
    }



    public function submitForm(array &$form, FormStateInterface $form_state)
    {
    }
}
