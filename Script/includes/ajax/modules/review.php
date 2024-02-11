<?php

/**
 * ajax -> modules -> review
 * 
 * @package Sngine
 * @author Zamblek
 */

// fetch bootstrap
require('../../../bootstrap.php');

// check AJAX Request
is_ajax();

// user access
user_access(true);

// check demo account
if ($user->_data['user_demo']) {
  modal("ERROR", __("Demo Restriction"), __("You can't do this with demo account"));
}

// valid inputs
if (!isset($_REQUEST['id']) || !is_numeric($_REQUEST['id'])) {
  _error(400);
}

try {

  // initialize the return array
  $return = array();

  switch ($_REQUEST['do']) {
    case 'review':
      // get page
      $page = $user->get_page($_REQUEST['id']);
      if (!$page) {
        _error(400);
      }
      /* assign variables */
      $smarty->assign('page', $page);

      // prepare publisher
      $return['publisher'] = $smarty->fetch("ajax.review.publisher.tpl");
      $return['callback'] = "$('#modal').modal('show'); $('.modal-content:last').html(response.publisher);";
      break;

    case 'publish-review':
      // add review
      $review = $user->add_review($_REQUEST['id'], $_POST['rating'], $_POST['review'], $_POST['photos']);

      // return
      $return['callback'] = "window.location = '{$system['system_url']}/pages/{$review['page_name']}/reviews';";
      break;

    case 'reply':
      // get review
      $review = $user->get_review($_REQUEST['id']);
      if (!$review) {
        _error(400);
      }
      /* assign variables */
      $smarty->assign('review', $review);

      // prepare publisher
      $return['publisher'] = $smarty->fetch("ajax.review.reply.publisher.tpl");
      $return['callback'] = "$('#modal').modal('show'); $('.modal-content:last').html(response.publisher);";
      break;

    case 'publish-reply':
      // add reply
      $user->add_review_reply($_REQUEST['id'], $_POST['reply']);

      // return
      $return['callback'] = "window.location.reload();";
      break;

    default:
      _error(400);
      break;
  }

  // return & exit
  return_json($return);
} catch (ValidationException $e) {
  return_json(array('error' => true, 'message' => $e->getMessage()));
} catch (Exception $e) {
  modal("ERROR", __("Error"), $e->getMessage());
}
