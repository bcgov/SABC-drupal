<?php

/**
 * @file
 * Contains \Drupal\sabc_tool_tips\Controller\ToolTipsController.
 */

namespace Drupal\sabc_tool_tips\Controller;

use Drupal\Core\Controller\ControllerBase;
use \Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Drupal\node\Entity\Node;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use \Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Controller routines for Tool Tips routes.
 */
class ToolTipsController extends ControllerBase {

  /**
   * Returns Glossary Tool Tips.
   *
   * @return array
   *   A render Glossary content
   */
  public function getToolTips(Request $request) {
    //MAKE SURE WE HAVE POST VALUES
    //MAKE SURE WE HAVE POST VALUES
    // get your POST parameter
    $_POST = \Drupal::request()->request->all();

    if (!empty($_POST)) {
      $a = []; //NEW ARRAY OF PROPERLY FORMATTED VALUES
      $nids = [];
      $u = array_unique($_POST); //GET UNIQUE ARRAY VALUES NO POINT IN QUERYING FOR THE SAME VALUES IF WE HAVE THEM ALREADY
      //LOOP THROUGH OUR RETURNED POSTS AND FORMAT FOR THE PROPER TITLES AND PUSH INTO A NEW ARRAY
      foreach ($u as $v) {
        $val = ucwords(str_replace('_', ' ', $v));
        $val = ucfirst(str_replace('_', ' ', $val));
        $val = strtolower(str_replace('_', ' ', $val));
        array_push($a, $val);
      }

      // 	//MAKE SURE WE HAVE VALUES IN OUR NEW FORMATTED ARRAY
      if (!empty($a)) {

        //QUERY DB FOR NODES BASED ON ARRAY OF TITLES
        foreach ($a as $v1) {
          $entity = \Drupal::entityTypeManager()->getStorage('node');
          $query = $entity->getQuery();

          $id = $query->accessCheck(FALSE)
            ->condition('status', 1)
            ->condition('type', 'studentaid_bc_glossary')
            ->condition('title', $v1)
            ->execute();
          $id_val = array_values($id);
          array_push($nids, $id_val[0]);
        }

        $nodes = $entity->loadMultiple($nids);
        //PUSH BACK INTO AN ARRAY FOR RETURN
        $r = [];

        //LOOP THROUGH IF RETURN IS ARRAY AND NOT EMPTY
        if (is_array($nodes) && !empty($nodes)) {

          foreach ($nodes as $node) {
            //GET THE TITLE AND BODY AND PUT INTO AN ARRAY THAT WE WILL BE RETURNING
            $r[strtolower(str_replace(' ', '_', $node->getTitle()))] = $node->get('body')
              ->getValue()[0]['value'];
          }

          //OUTPUT RESULT AS JSON
          $json_output = json_encode($r);
          return new Response($json_output);
        }
        else {
          return new Response(FALSE);
        }
      }
      else {
        return new Response(FALSE);
      }
    }
    else {
      return new Response(FALSE);
    }
  }

}
