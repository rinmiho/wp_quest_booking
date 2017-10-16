<?php
/*
Plugin Name: Quest Booking
Description: Allows to book quests
Version: 0.1
Author: Alina Kukhareva
*/

/**
 * Plugin version
 *
 * @var string
 */
define('QB_VERSION', '0.1');

/**
 * Path to the plugin directory
 *
 * @var string
 */
define('QB_DOCUMENT_ROOT', dirname(__FILE__));

/**
 * Add a page to the admin menu
 */
add_action('admin_menu', 'qb_menu');

function qb_menu() {
	add_menu_page('Бронь Квестов', 'Бронь Квестов', 'manage_options', 'quest-booking', 'print_backend_bookings_table', 'dashicons-calendar-alt');
}

/**
 * Print table with bookings at the backend
 */
function print_backend_bookings_table()
{
	echo '<h1>Бронь квестов</h1>';
	global $wpdb;
	// this adds the prefix which is set by the user upon instillation of wordpress
	$table_name = $wpdb->prefix . "quest_bookings";
	// this will get the data from your table
	$allBookings = $wpdb->get_results( "SELECT * FROM $table_name" );
	if ($allBookings)
	{
		echo
		'<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th>№</th>
				<th>Квест</th>
				<th>Дата</th>
				<th>Время</th>
				<th>Имя</th>
				<th>Телефон</th>
				<th>Опции</th>
			</tr>
		</thead>
		<tbody>
		<tr>';
		foreach ($allBookings as $singleOrder)
		{
			echo
				'<td>' .  $singleOrder->id . '</td>
				<td>' . get_the_title($singleOrder->quest_ID) . '</td>
				<td>' . $singleOrder->date . '</td>
				<td>' . $singleOrder->time . '</td>
				<td>' . $singleOrder->name . '</td>
				<td>' . $singleOrder->phone . '</td>
				<td><a style="cursor: pointer" id="' . $singleOrder->id . '" class="buttonConfirmDelete">Отменить бронь</a></td></tr>';
		}
		echo '</tbody></table>';
	}
	else
	{
		echo '<h3>Еще никто не забронировал себе время.</h3>';
	}
}



function dobsondev_ajax_tester_ajax_script() { ?>
	<script type="text/javascript" >
		jQuery(document).ready(function($) {
			$( '.buttonConfirmDelete' ).click( function(event) {
				var id = event.target.id;
				if (confirm('Вы точно хотите отменить бронь? Это действие нельзя отменить.'))
				{
					$.ajax({
					method: "POST",
					url: ajaxurl,
					data: { 'action': 'dobsondev_ajax_tester_approal_action', 'id': id }
					})
						.success(function( data ) {
							location.reload();
						})
						.fail(function( data, err, msg ) {
							console.log('Failed AJAX Call :( /// Return Data: ' + data);
							console.log(msg);
							console.log(err);
							console.log(data);
						});
				}

			});
		});

	</script> <?php
}
add_action( 'admin_footer', 'dobsondev_ajax_tester_ajax_script' ); // Write our JS below here

function dobsondev_ajax_tester_ajax_handler() {
	global $wpdb; // this is how you get access to the database

	echo $_POST['id'] ;
	$bookingID = intval( $_POST['id'] );
	$table_name = $wpdb->prefix . "quest_bookings";
	$wpdb->delete( $table_name, array( 'id' => $_POST['id']  ) );
	$data = $_POST['id'] ;

	wp_die(); // this is required to terminate immediately and return a proper response
}
add_action( 'wp_ajax_dobsondev_ajax_tester_approal_action', 'dobsondev_ajax_tester_ajax_handler'  );

/**
 * Creates quest_bookings table during plugin installation
 */
function quest_booking_install()
{
	global $wpdb;

	//$wpdb->prefix - adds 'wp_' prefix to the name of the table
	$table_name = $wpdb->prefix . 'quest_bookings';

	$charset_collate = $wpdb->get_charset_collate();

	//Если такой таблицы нет (пользователь активирует плагин первый раз)
	if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name)
	{
		// тут мы добавляем таблицу в базу данных
		/**
		 * tynytext max contains 255 characters
		 * smallint max 65535
		 */
		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			date tinytext NOT NULL,
			time tinytext NOT NULL,
			name tinytext NOT NULL,
			phone tinytext NOT NULL,
			quest_ID bigint NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

}

function quest_booking_uninstall()
{
	global $wpdb;
	$table_name = $wpdb->prefix . 'quest_bookings';
	$sql = "DROP TABLE IF EXISTS $table_name";
	$wpdb->query($sql);
	//delete_option('e34s_time_card_version');
}

/**
 * Register a hook to call installation
 */
register_activation_hook(__FILE__, 'quest_booking_install' );
//register_uninstall_hook(__FILE__, 'quest_booking_uninstall');
register_deactivation_hook(__FILE__, 'quest_booking_uninstall');
