<?php

/**
 * @file 
 * 
 * A form form that lists form results for current user. 
 * 
 */

namespace Drupal\todolist\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Link;


class toDoListFormList extends FormBase
{
    

    public function getFormId()
    {
        return 'toDoList_table_form';
    }

    public function buildForm(array $form, FormStateInterface $form_state,$state = NULL)
    {
        // get current users todo list item from db
        $uid = \Drupal::currentUser()->id();

        $query = \Drupal::database()
            ->select('todolist', 't')
            ->fields('t', array('id', 'uid', 'task', 'status', 'created'))
            ->condition('uid', $uid, '=');
        //filter by status if needed 
        if (!is_null($state) && in_array($state, [0, 1])) {
            $query = $query->condition('status', $state, '=');
        }

        $query = $query
            ->orderBy('id', 'DESC')
            ->execute()
            ->fetchAllAssoc('id');



        // Create the row element and fill data from tthe database
        $rows = array();
        foreach ($query as $row) {

            //generate ajax links for edit and delete fucntions 

            $edit = Url::fromRoute('todolist.edit_task', ['id' => $row->id], []);
            $delete =  Url::fromRoute('todolist.delete_task', ['id' => $row->id], []);
            $edit_link = Link::fromTextAndUrl($row->task, $edit);
            $delete_link = Link::fromTextAndUrl(t('Delete'), $delete);
            $edit_link = $edit_link->toRenderable();
            $delete_link  = $delete_link->toRenderable();
            $edit_link['#attributes'] = ['class' => 'use-ajax edit-link'];
            $delete_link['#attributes'] = ['class' => 'use-ajax delete-link'];



            $rows[] = array('id' => $row->id, 'uid' => $row->uid, 'task' => $row->task, 'status' => $row->status, 'created' => $row->created, 'delete' => render($delete_link), 'edit' => render($edit_link));
        }
   
        // a custom theme for displaying list items todolist-edit-theme.html.twig/ 
        $form['todolist_theme'] = array(
            '#theme' => 'todolist_edit_theme',
            '#content' => $rows,

            '#cache' => [
                'max-age' => 0,
            ],
            '#attached' => [
                'library' => [
                  'todolist/todolist',
                ],
              ],

        );

     

        $form['#prefix'] = '<div class="result_area">';
        $form['#suffix'] = '</div>';




        return $form;
    }

    public function validateForm(array &$form, FormStateInterface $form_state)
    {
       
    }




    public function submitForm(array &$form, FormStateInterface $form_state)
    {
    }


}
