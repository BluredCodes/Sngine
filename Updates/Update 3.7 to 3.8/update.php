<?php

/**
 * update wizard
 * 
 * @package Sngine
 * @author Zamblek
 */

// set execution time
set_time_limit(0); /* unlimited max execution time */


// set ABSPATH & BASEPATH
define('ABSPATH', __DIR__ . '/');
define('BASEPATH', dirname($_SERVER['PHP_SELF']));


// get system version & exceptions
define('SYS_UPDATE_VER', '3.8');
require(ABSPATH . 'includes/sys_ver.php');
require(ABSPATH . 'includes/exceptions.php');


// check config file
if (!file_exists(ABSPATH . 'includes/config.php')) {
  /* the config file doesn't exist -> start the installer */
  header('Location: ./install');
}


// get config file
require(ABSPATH . 'includes/config.php');


// set debugging settings
if (DEBUGGING) {
  ini_set("display_errors", true);
  error_reporting(E_ALL ^ E_NOTICE);
} else {
  ini_set("display_errors", false);
  error_reporting(0);
}


// get functions
require_once(ABSPATH . 'includes/functions.php');


// update
if (isset($_POST['submit'])) {

  // check purchase code
  try {
    $licence_key = get_licence_key($_POST['purchase_code']);
    if (is_empty($_POST['purchase_code']) || $licence_key === false) {
      _error("Error", "Please enter a valid purchase code");
    }
    $session_hash = $licence_key;
  } catch (Exception $e) {
    _error("Error", $e->getMessage());
  }


  // init database connection
  try {
    $db = init_db_connection();
  } catch (Exception $e) {
    _error('DB_ERROR');
  }


  // update database tables
  $structure = "

    CREATE TABLE `monetization_plans` (
      `plan_id` int(10) NOT NULL AUTO_INCREMENT,
      `node_id` int(10) unsigned NOT NULL,
      `node_type` varchar(32) NOT NULL,
      `title` varchar(256) NOT NULL,
      `price` float NOT NULL,
      `period_num` int(10) unsigned NOT NULL,
      `period` varchar(32) NOT NULL,
      `custom_description` text,
      `plan_order` int(10) unsigned NOT NULL DEFAULT '1',
      PRIMARY KEY (`plan_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

    CREATE TABLE `games_genres` (
      `genre_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
      `genre_name` varchar(256) NOT NULL,
      `genre_description` text NOT NULL,
      `genre_order` int(10) unsigned NOT NULL DEFAULT '1',
      PRIMARY KEY (`genre_id`)
    ) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

    INSERT INTO `games_genres` (`genre_id`, `genre_name`, `genre_description`, `genre_order`) VALUES
    (1, 'Action', '', 1),
    (2, 'Adventure', '', 2),
    (3, 'Animation', '', 3),
    (4, 'Comedy', '', 4),
    (5, 'Crime', '', 5),
    (6, 'Documentary', '', 6),
    (7, 'Drama', '', 7),
    (8, 'Family', '', 8),
    (9, 'Fantasy', '', 9),
    (10, 'History', '', 10),
    (11, 'Horror', '', 11),
    (12, 'Musical', '', 12),
    (13, 'Mythological', '', 13),
    (14, 'Romance', '', 14),
    (15, 'Sport', '', 15),
    (16, 'TV Show', '', 16),
    (17, 'Thriller', '', 17),
    (18, 'War', '', 18);

    # Changing table developers_apps_users indexes
    ALTER TABLE `developers_apps_users`
      ADD UNIQUE INDEX `app_id_user_id` USING BTREE (app_id, user_id),
      DROP INDEX `page_id_user_id`;

    # Changing table users_searches fields, indexes
    ALTER TABLE `users_searches`
      MODIFY COLUMN `node_id` INT(10) UNSIGNED NOT NULL  COMMENT '' AFTER `user_id`,
      ADD UNIQUE INDEX `user_id_node_id_node_type` USING BTREE (user_id, node_id, node_type),
      DROP INDEX `node_id_node_type`;

    # Changing table posts fields
    ALTER TABLE `posts`
      ADD COLUMN `tips_enabled` ENUM('0','1') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT '0'  COMMENT '' AFTER `processing`;

    # Changing table groups fields, indexes
    ALTER TABLE `groups`
      ADD COLUMN `group_monetization_min_price` FLOAT NOT NULL DEFAULT 0  COMMENT '' AFTER `group_monetization_enabled`,
      MODIFY COLUMN `group_date` DATETIME NOT NULL  COMMENT '' AFTER `group_monetization_min_price`,
      DROP COLUMN `group_monetization_price`,
      ADD UNIQUE INDEX `group_name` USING BTREE (group_name),
      DROP INDEX `username`;

    # Changing table system_reactions fields
    ALTER TABLE `system_reactions`
      ADD COLUMN `reaction_order` INT(10) UNSIGNED NOT NULL DEFAULT 1  COMMENT '' AFTER `image`;

    # Changing table users_sessions fields
    ALTER TABLE `users_sessions`
      ADD COLUMN `session_type` ENUM('W','A','I') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'W'  COMMENT '' AFTER `session_date`,
      MODIFY COLUMN `user_id` INT(10) UNSIGNED NOT NULL  COMMENT '' AFTER `session_type`,
      MODIFY COLUMN `user_ip` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL  COMMENT '' AFTER `user_id`,
      MODIFY COLUMN `user_browser` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `user_ip`,
      ADD COLUMN `user_os_version` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `user_os`,
      ADD COLUMN `user_device_name` VARCHAR(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL DEFAULT NULL  COMMENT '' AFTER `user_os_version`;

    # Changing table subscribers fields, indexes
    ALTER TABLE `subscribers`
      ADD COLUMN `plan_id` INT(10) UNSIGNED NOT NULL  COMMENT '' AFTER `node_type`,
      MODIFY COLUMN `time` DATETIME NOT NULL  COMMENT '' AFTER `plan_id`,
      ADD UNIQUE INDEX `user_id_node_id_node_type` USING BTREE (user_id, node_id, node_type),
      DROP INDEX `user_id_following_id`;

    # Changing table users fields
    ALTER TABLE `users`
      ADD COLUMN `user_monetization_min_price` FLOAT NOT NULL DEFAULT 0  COMMENT '' AFTER `user_monetization_enabled`,
      MODIFY COLUMN `user_monetization_balance` FLOAT NOT NULL DEFAULT 0  COMMENT '' AFTER `user_monetization_min_price`,
      DROP COLUMN `user_monetization_price`;

    # Changing table pages fields, indexes
    ALTER TABLE `pages`
      ADD COLUMN `page_monetization_min_price` FLOAT NOT NULL DEFAULT 0  COMMENT '' AFTER `page_monetization_enabled`,
      MODIFY COLUMN `page_date` DATETIME NOT NULL  COMMENT '' AFTER `page_monetization_min_price`,
      DROP COLUMN `page_monetization_price`,
      ADD UNIQUE INDEX `page_name` USING BTREE (page_name),
      DROP INDEX `username`;

    # Changing table bank_transfers fields
    ALTER TABLE `bank_transfers`
      ADD COLUMN `plan_id` INT(10) UNSIGNED NULL DEFAULT NULL  COMMENT '' AFTER `post_id`,
      MODIFY COLUMN `price` FLOAT NULL DEFAULT NULL  COMMENT '' AFTER `plan_id`,
      DROP COLUMN `node_id`,
      DROP COLUMN `node_type`;

    # Changing table events_members indexes
    ALTER TABLE `events_members`
      ADD UNIQUE INDEX `event_id_user_id` USING BTREE (event_id, user_id),
      DROP INDEX `group_id_user_id`;

    # Changing table games fields
    ALTER TABLE `games`
      ADD COLUMN `genres` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL  COMMENT '' AFTER `description`,
      MODIFY COLUMN `source` MEDIUMTEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL  COMMENT '' AFTER `genres`;
      
	";
  $db->multi_query($structure) or _error("Error", $db->error);


  // flush multi_queries
  do {
  } while (mysqli_more_results($db) && mysqli_next_result($db));


  // update tables collections
  $get_db_tbls = $db->query("show tables") or _error("Error", $db->error);
  while ($db_tbl = $get_db_tbls->fetch_array()) {
    foreach ($db_tbl as $key => $value) {
      $db->query("ALTER TABLE $value CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
    }
  }


  // insert new system options
  $db->query("INSERT INTO system_options (option_name, option_value) VALUES
        ('watch_enabled', '0'),
        ('tips_enabled', '1'),
        ('tips_permission', 'everyone'),
        ('tips_min_amount', '10'),
        ('tips_max_amount', '100'),
        ('allow_animated_images', '1'),
        ('system_description_pages', 'Discover pages'),
        ('system_description_groups', 'Discover groups'),
        ('system_description_events', 'Discover events'),
        ('system_description_games', 'Discover new games'),
        ('system_morning_message', 'Write it on your heart that every day is the best day in the year'),
        ('system_afternoon_message', 'May Your Good Afternoon Be Light, Blessed, Productive And Happy'),
        ('system_evening_message', 'We hope you are enjoying your evening'),
        ('moneypoolscash_enabled', '0'),
        ('moneypoolscash_api_key', ''),
        ('moneypoolscash_merchant_email', '')
        ") or _error("Error", $db->error);


  // update system settings
  update_system_options([
    'session_hash' => secure($session_hash)
  ], false);


  // create config file
  $config_string = '<?php  
	define("DB_NAME", \'' . DB_NAME . '\');
	define("DB_USER", \'' . DB_USER . '\');
	define("DB_PASSWORD", \'' . DB_PASSWORD . '\');
	define("DB_HOST", \'' . DB_HOST . '\');
	define("DB_PORT", \'' . DB_PORT . '\');
	define("SYS_URL", \'' . SYS_URL . '\');
	define("DEBUGGING", false);
	define("DEFAULT_LOCALE", \'en_us\');
	define("LICENCE_KEY", \'' . $licence_key . '\');
	?>';
  $config_file = 'includes/config.php';
  $handle = fopen($config_file, 'w') or _error("System Error", "Cannot create the config file");
  fwrite($handle, $config_string);
  fclose($handle);


  // finished!
  _error("System Updated", "Sngine has been updated to " . SYS_UPDATE_VER);
}

?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title><?php echo SYS_NAME ?> &rsaquo; Update (v<?php echo SYS_UPDATE_VER ?>)</title>
  <link rel="shortcut icon" href="includes/assets/js/core/installer/favicon.png" />
  <link href="https://fonts.googleapis.com/css?family=Karla:400,700&display=swap" rel="stylesheet" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" integrity="sha512-1ycn6IcaQQ40/MKBW2W4Rhis/DbILU74C1vSrLJxCq57o941Ym01SwNsOMqvEBFlcgUa6xLiPY/NS5R+E6ztJQ==" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
  <link rel="stylesheet" href="includes/assets/js/core/installer/wizard.css">
</head>

<body>
  <main class="my-5">
    <div class="container">
      <form id="wizard" method="post" class="position-relative">
        <!-- Step 1 -->
        <h3>
          <div class="media">
            <div class="bd-wizard-step-icon"><i class="fas fa-cubes"></i></div>
            <div class="media-body">
              <div class="bd-wizard-step-title">Update</div>
              <div class="bd-wizard-step-subtitle">Sngine (v<?php echo SYS_UPDATE_VER ?>)</div>
            </div>
          </div>
        </h3>
        <section>
          <div class="content-wrapper">
            <h3 class="section-heading">Welcome!</h3>
            <p>
              Welcome to <strong><?php echo SYS_NAME ?></strong> updating process! Just fill in the information below.
            </p>
            <div class="row mt-4">
              <div class="form-group col-12">
                <label for="purchase_code">Your Purchase Code</label>
                <input type="text" name="purchase_code" id="purchase_code" class="form-control" placeholder="xxx-xx-xxxx">
                <div class="invalid-feedback">
                  This field can't be empty
                </div>
              </div>
            </div>
          </div>
        </section>
        <!-- Step 1 -->
        <!-- Submit -->
        <div style="display: none;">
          <button class="btn btn-primary" name="submit" type="submit" id="wizard-submittion">Submit</button>
        </div>
        <!-- Submit -->
        <!-- Loader -->
        <div id="loader" style="display: none;">
          <div class="wizard-loader">
            Updating<span class="spinner-grow spinner-grow-sm ml-3"></span>
          </div>
        </div>
        <!-- Loader -->
      </form>
    </div>
  </main>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>
  <script src="includes/assets/js/core/installer/jquery.steps.min.js"></script>
  <script type="text/javascript">
    // handle wizard
    var wizard = $("#wizard");
    wizard.steps({
      headerTag: "h3",
      bodyTag: "section",
      transitionEffect: "none",
      titleTemplate: '#title#',
      onFinished: function(event, currentIndex) {
        /* check details */
        if ($('input[type="text"]').val() == "") {
          $('input[type="text"]').addClass("is-invalid");
          return false;
        }
        $("#loader").slideDown();
        $("#wizard-submittion").click();
        return true;
      },
      labels: {
        finish: "Update",
      }
    });

    // handle inputs
    $('input[type="text"]').on('change', function() {
      if ($(this).val() == "") {
        $(this).addClass("is-invalid");
      } else {
        $(this).removeClass("is-invalid");
      }
    });
  </script>
</body>

</html>