<?php

/**
 * ajax -> users -> image delete
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

try {

  // delete avatar/cover image
  $response = delete_avatar_cover_image($_POST['hanlde']);

  // return
  return_json(['file' => $response]);
} catch (Exception $e) {
  modal("ERROR", __("Error"), $e->getMessage());
}
