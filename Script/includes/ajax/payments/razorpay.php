<?php

/**
 * ajax -> payments -> razorpay
 * 
 * @package Sngine
 * @author Zamblek
 */

// fetch bootstrap
require('../../../bootstrap.php');

// check AJAX Request
is_ajax();

// user access
user_access(true, true);

// check if Razorpay enabled
if (!$system['razorpay_enabled']) {
  modal("MESSAGE", __("Error"), __("This feature has been disabled by the admin"));
}

try {

  switch ($_POST['handle']) {
    case 'packages':
      // valid inputs
      if (!isset($_POST['razorpay_payment_id'])) {
        _error(400);
      }
      if (!isset($_POST['package_id']) || !is_numeric($_POST['package_id'])) {
        _error(400);
      }

      // get package
      $package = $user->get_package($_POST['package_id']);
      if (!$package) {
        _error(400);
      }
      /* check if user already subscribed to this package */
      if ($user->_data['user_subscribed'] && $user->_data['user_package'] == $package['package_id']) {
        modal("SUCCESS", __("Subscribed"), __("You already subscribed to this package, Please select different package"));
      }

      // check payment
      $payment = razorpay_check($_POST['razorpay_payment_id'], $package['price']);
      if ($payment) {
        /* update user package */
        $user->update_user_package($package['package_id'], $package['name'], $package['price'], $package['verification_badge_enabled']);
        /* return */
        return_json(array('callback' => 'window.location.href = "' . $system['system_url'] . '/upgraded";'));
      }
      break;

    case 'wallet':
      // valid inputs
      if (!isset($_POST['razorpay_payment_id'])) {
        _error(400);
      }
      if (!isset($_POST['price']) || !is_numeric($_POST['price'])) {
        _error(400);
      }

      // check payment
      $payment = razorpay_check($_POST['razorpay_payment_id'], $_POST['price']);
      if ($payment) {
        /* update user wallet balance */
        $_SESSION['wallet_replenish_amount'] = $_POST['price'];
        $db->query(sprintf("UPDATE users SET user_wallet_balance = user_wallet_balance + %s WHERE user_id = %s", secure($_SESSION['wallet_replenish_amount']), secure($user->_data['user_id'], 'int'))) or _error('SQL_ERROR_THROWEN');
        /* wallet transaction */
        $user->wallet_set_transaction($user->_data['user_id'], 'recharge', 0, $_SESSION['wallet_replenish_amount'], 'in');
        /* redirect*/
        return_json(array('callback' => 'window.location.href = "' . $system['system_url'] . '/wallet?wallet_replenish_succeed";'));
      }
      break;

    case 'donate':
      // valid inputs
      if (!isset($_POST['razorpay_payment_id'])) {
        _error(400);
      }
      if (!isset($_POST['post_id']) || !is_numeric($_POST['post_id'])) {
        _error(400);
      }
      if (!isset($_POST['price']) || !is_numeric($_POST['price'])) {
        _error(400);
      }

      // check payment
      $payment = razorpay_check($_POST['razorpay_payment_id'], $_POST['price']);
      if ($payment) {
        /* funding donation */
        $user->funding_donation($_POST['post_id'], $_POST['price']);
        /* redirect */
        return_json(array('callback' => 'window.location.href = "' . $system['system_url'] . '/posts/' . $_POST['post_id'] . '";'));
      }
      break;

    case 'subscribe':
      // valid inputs
      if (!isset($_POST['razorpay_payment_id'])) {
        _error(400);
      }

      // get plan
      $monetization_plan = $user->get_monetization_plan($_POST['plan_id'], true);
      if (!$monetization_plan) {
        _error(400);
      }
      /* check if user already subscribed to this node */
      if ($user->is_subscribed($monetization_plan['node_id'], $monetization_plan['node_type'])) {
        modal("SUCCESS", __("Subscribed"), __("You already subscribed to this") . " " . __($_POST['node_type']));
      }

      // check payment
      $payment = razorpay_check($_POST['razorpay_payment_id'], $monetization_plan['price']);
      if ($payment) {
        /* subscribe to node */
        $node_link = $user->subscribe($_POST['plan_id']);
        /* redirect */
        return_json(array('callback' => 'window.location.href = "' . $system['system_url'] . $node_link . '";'));
      }
      break;

    case 'paid_post':
      // valid inputs
      if (!isset($_POST['razorpay_payment_id'])) {
        _error(400);
      }

      // get post
      $post = $user->get_post($_POST['post_id'], false, false, true);
      if (!$post) {
        throw new Exception(__("This post is not available"));
      }
      if (!$post['needs_payment']) {
        throw new Exception(__("This post doesn't need payment"));
      }

      // check payment
      $payment = razorpay_check($_POST['razorpay_payment_id'], $monetization_plan['price']);
      if ($payment) {
        /* unlock paid post */
        $post_link = $user->unlock_paid_post($_POST['post_id']);
        /* redirect */
        return_json(array('callback' => 'window.location.href = "' . $system['system_url'] . $post_link . '";'));
      }
      break;

    case 'movies':
      // valid inputs
      if (!isset($_POST['razorpay_payment_id'])) {
        _error(400);
      }

      // get movie
      $movie = $user->get_movie($_POST['movie_id']);
      /* check if user already paid to this movie */
      if ($movie['can_watch']) {
        modal("SUCCESS", __("Paid"), __("You already paid to this movie"));
      }

      // check payment
      $payment = razorpay_check($_POST['razorpay_payment_id'], $movie['price']);
      if ($payment) {
        /* movie payment */
        $movie_link = $user->movie_payment($movie['movie_id']);
        /* redirect */
        return_json(array('callback' => 'window.location.href = "' . $system['system_url'] . $movie_link . '";'));
      }
      break;

    default:
      _error(400);
      break;
  }
} catch (Exception $e) {
  modal("ERROR", __("Error"), $e->getMessage());
}
