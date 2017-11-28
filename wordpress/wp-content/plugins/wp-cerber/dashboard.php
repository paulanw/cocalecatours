<?php
/*
 	Copyright (C) 2015-17 CERBER TECH INC., Gregory Markov, https://wpcerber.com

    Licenced under the GNU GPL.

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/*

*========================================================================*
|                                                                        |
|	       ATTENTION!  Do not change or edit this file!                  |
|                                                                        |
*========================================================================*

*/











if ( ! defined( 'WPINC' ) ) { exit; }

require_once( dirname( __FILE__ ) . '/cerber-tools.php' );

/*
	Display lockouts in dashboard for admins
*/
function cerber_show_lockouts($args = array(), $echo = true){
	global $wpdb, $crb_assets_url;

	//$wp_cerber->deleteGarbage();

	if (!empty($args['per_page'])) $per_page = $args['per_page'];
	else $per_page = cerber_get_per_page();

	$limit = (cerber_get_pn() - 1) * $per_page.','.$per_page;

	if ($rows = $wpdb->get_results('SELECT * FROM '. CERBER_BLOCKS_TABLE . ' ORDER BY block_until DESC LIMIT '.$limit)) {

	    //$total=$wpdb->get_var('SELECT count(ip) FROM '. CERBER_BLOCKS_TABLE);
		$total= cerber_blocked_num();
		$list=array();
		$base_url = cerber_admin_link('activity');

		foreach ($rows as $row) {
			$ip = '<a href="'.$base_url.'&filter_ip='.$row->ip.'">'.$row->ip.'</a>';

			$ip_info = cerber_get_ip_info($row->ip,true);
			if ( isset( $ip_info['hostname'] ) ) {
				$hostname = $ip_info['hostname'];
			}
			else {
				$ip_id    = cerber_get_id_ip( $row->ip );
				$hostname = '<img data-ip-id="' . $ip_id . '" class="crb-no-hostname" src="' . $crb_assets_url . 'ajax-loader.gif" />' . "\n";
			}

			if ( lab_lab() ) {
				$single_ip = str_replace( '*', '1', $row->ip );
				$country = '</td><td>' . crb_country_html( null, $single_ip );
			}
			else {
				$country = '';
            }

			$list[] = '<td>' . $ip . '</td><td>' . $hostname . $country . '</td><td>' . cerber_date( $row->block_until ) . '</td><td>' . $row->reason . '</td><td><a href="' . wp_nonce_url( add_query_arg( array( 'lockdelete' => $row->ip ) ), 'control', 'cerber_nonce' ) . '">' . __( 'Remove', 'wp-cerber' ) . '</a></td>';

		}

		//$titles = '<tr><th>'.__('IP','wp-cerber').'</th><th>'.__('Hostname','wp-cerber').'</th><th>'.__('Expires','wp-cerber').'</th><th>'.__('Reason','wp-cerber').'</th><th>'.__('Action','wp-cerber').'</th></tr>';

		$heading = array(
			__( 'IP', 'wp-cerber' ),
			__( 'Hostname', 'wp-cerber' ),
			__( 'Country', 'wp-cerber' ),
			__( 'Expires', 'wp-cerber' ),
			__( 'Reason', 'wp-cerber' ),
			__( 'Action', 'wp-cerber' ),
		);

		if ( !lab_lab() ) {
			unset( $heading[2] );
		}

		$titles = '<tr><th>' . implode( '</th><th>', $heading ) . '</th></tr>';

		$table = '<table class="widefat crb-table cerber-margin"><thead>'.$titles.'</thead><tfoot>'.$titles.'</tfoot>'.implode('</tr><tr>',$list).'</tr></table>';

		if (empty($args['no_navi'])) $table .= cerber_page_navi($total,$per_page);

		//echo '<h3>'.sprintf(__('Showing last %d records from %d','wp-cerber'),count($rows),$total).'</h3>';
		$showing = '<h3>'.sprintf(__('Showing last %d records from %d','wp-cerber'),count($rows),$total).'</h3>';

		$view = '<p><b>'.__('Hint','wp-cerber').':</b> ' . __('To view activity, click on the IP','wp-cerber').'</p>';
	}
	else {
		$table = '';
		$view = '<p>'.sprintf(__('No lockouts at the moment. The sky is clear.','wp-cerber')).'</p>';
	}
	$ret = $table.'<div class="cerber-margin">'.$view.'</div>';

	if ($echo) echo $ret;
	else return $ret;
}

/*
	ACL management form in dashboard
*/
function cerber_acl_form(){
	global $wp_cerber;
	echo '<h2>'.__('White IP Access List','wp-cerber').'</h2><p><span style="color:green;" class="dashicons-before dashicons-thumbs-up"></span> '.__('These IPs will never be locked out','wp-cerber').' - <a target="_blank" href="https://wpcerber.com/using-ip-access-lists-to-protect-wordpress/">Know more</a></p>'.
	     cerber_acl_get_table('W');
	echo '<h2>'.__('Black IP Access List','wp-cerber').'</h2><p><span style="color:red;" class="dashicons-before dashicons-thumbs-down"></span> '.__('Nobody can log in or register from these IPs','wp-cerber').' - <a target="_blank" href="https://wpcerber.com/using-ip-access-lists-to-protect-wordpress/">Know more</a></p>'.
	     cerber_acl_get_table('B');

	$user_ip = $wp_cerber->getRemoteIp();
	$link = cerber_admin_link( 'activity' ) . '&filter_ip=' . $user_ip;
	$name = crb_country_html(null, $user_ip);

	echo '<p><b><span class="dashicons-before dashicons-star-filled"></span> '.__('Your IP','wp-cerber').': </b><a href="'.$link.'">'.$user_ip.'</a> '.$name.'</p>';
	echo '<h4 style="margin-top: 2em;"><span class="dashicons-before dashicons-info"></span> Possible values for entries in the access lists with examples</h4>
	<p>Single IPv6 address: <b>2001:0db8:85a3:0000:0000:8a2e:0370:7334</b>
	<p>Single IPv4 address: <b>192.168.5.22</b>
	<p>IPv4 addresses range with dash: <b>192.168.1.45 - 192.168.22.165</b>
	<p>IPv4 CIDR: <b>192.168.128.0/24</b>
	<p>IPv4 subnet Class A: <b>192.*.*.*</b>
	<p>IPv4 subnet Class B: <b>192.168.*.*</b>
	<p>IPv4 subnet Class C: <b>192.168.77.*</b>';
}
/*
	Create HTML to display ACL area: table + form
*/
function cerber_acl_get_table($tag){
	global $wpdb;
	$activity_url = cerber_admin_link('activity');
	if ($rows = $wpdb->get_results('SELECT * FROM '. CERBER_ACL_TABLE . " WHERE tag = '".$tag."' ORDER BY ip_long_begin, ip")) {
		foreach ($rows as $row) $list[]='<td>'.$row->ip.'</td><td><a class="delete_entry" href="javascript:void(0)" data-ip="'.$row->ip.'">'.__('Remove','wp-cerber').'</a></td><td><a href="'.$activity_url.'&filter_ip='.urlencode($row->ip).'">'.__('Check for activity','wp-cerber').'</a></td>';
		$ret = '<table id="acl_'.$tag.'" class="acl-table"><tr>'.implode('</tr><tr>',$list).'</tr></table>';
	}
	else $ret='<p style="text-align: center;">- <i>'.__('List is empty','wp-cerber').'</i> -</p>';
	$ret = '<div class="acl-wrapper"><div class="acl-items">'
	       .$ret.'</div><form action="" method="post">
	       <table><tr><td><input type="text" name="add_acl_'.$tag.'" required placeholder="Enter IP address, IPv4 addresses range or subnet"> 
	       </td><td><input type="submit" class="button button-primary" value="'.__('Add IP to the list','wp-cerber').'" ></td></tr></table>'
	       .wp_nonce_field('cerber_dashboard','cerber_nonce')
	       .'</form></div>';
	return $ret;
}
/*
	Handle actions with items in ACLs in the dashboard
*/
add_action('admin_init','cerber_acl_form_process');
function cerber_acl_form_process(){

	if ( $_SERVER['REQUEST_METHOD'] != 'POST' || !isset( $_POST['cerber_nonce'] ) ) return;
	if ( !current_user_can( 'manage_options' ) || !wp_verify_nonce( $_POST['cerber_nonce'], 'cerber_dashboard' ) ) return;

	//if (!current_user_can('manage_options')) return;
	//if (!isset($_POST['cerber_nonce']) || !wp_verify_nonce($_POST['cerber_nonce'],'cerber_dashboard')) return;

	if ($_SERVER['REQUEST_METHOD']=='POST') {
		if (!empty($_POST['add_acl_W'])) {
			$ip = cerber_parse_ip($_POST['add_acl_W']);
			if (!$ip) cerber_admin_notice( __('Incorrect IP address or IP range','wp-cerber'));
			elseif (cerber_add_white($ip)) {
				if (is_array($ip)) $ip = $ip['range'];
				cerber_admin_message( sprintf(__('Address %s was added to White IP Access List','wp-cerber'),$ip));
			}
		}
		if (!empty($_POST['add_acl_B'])) {
			$ip = cerber_parse_ip($_POST['add_acl_B']);
			if (!$ip) cerber_admin_notice( __('Incorrect IP address or IP range','wp-cerber'));
			else {
				if ( cerber_is_myip( $ip ) || cerber_is_ip_in_range( $ip ) ) {
					if (is_array($ip)) $ip = $ip['range'];
					cerber_admin_notice( __( "You can't add your IP address", 'wp-cerber' ) . ' ' . $ip );
				}
				elseif (cerber_add_black($ip)) {
					if (is_array($ip)) $ip = $ip['range'];
					cerber_admin_message(sprintf(__('Address %s was added to Black IP Access List','wp-cerber'),$ip));
				}
			}
		}
	}
}
/*
	Get all entries from access lists
*/
function cerber_acl_all($fields='*'){
	global $wpdb;
	return $wpdb->get_results('SELECT '.$fields.' FROM '. CERBER_ACL_TABLE , ARRAY_N);
}

/*
	AJAX admin requests is landing here
*/
add_action('wp_ajax_cerber_ajax', 'cerber_admin_ajax');
function cerber_admin_ajax() {

	$admin = false;
    if ( current_user_can( 'manage_options' ) ) {
		$admin = true;
	}

	$response = array();

	if ($admin && isset($_REQUEST['acl_delete'])){

		check_ajax_referer( 'crb-ajax-admin', 'ajax_nonce' );
		$ip = $_REQUEST['acl_delete'];
		if ( ! $ip = cerber_parse_ip( $ip ) ) {
			wp_die();
		}
		if ( cerber_acl_remove( $ip ) ) {
			if ( is_string( $ip ) ) {
				$deleted = $ip;
			}
			else {
				$deleted = $ip['range'];
			}
			$response['deleted_ip'] = $deleted;
		}
		else {
			$response['error'] = 'Unable to delete';
		}

	}
	elseif ( isset( $_REQUEST['crb_ajax_slug'] ) && isset( $_REQUEST['crb_ajax_list'] ) ) {

	    check_ajax_referer('crb-ajax-admin','ajax_nonce');
		$slug = $_REQUEST['crb_ajax_slug'];
		$response['slug'] = $slug;
		$list = array_unique( $_REQUEST['crb_ajax_list'] );

		/*
		$list = array_map(function ($ip_id){
			return cerber_get_ip_id( $ip_id );
        }, $list);
		$list = array_filter( $list, function ( $ip ) {
			if (filter_var( $ip, FILTER_VALIDATE_IP )){
			    return true;
            }
		});*/

		$ip_list = array();
		foreach ( $list as $ip_id ) {
			if ($ip = filter_var( cerber_get_ip_id( $ip_id ), FILTER_VALIDATE_IP )){
				$ip_list[ $ip_id ] = $ip;
				// Set elements for frontend
				$response['data'][ $ip_id ] = '';
			}
			else {
				$response['data'][ $ip_id ] = '-';
            }
		}

		switch($slug){
            case 'hostname':
	            foreach ( $ip_list as $ip_id => $ip ) {
		            $ip_info = cerber_get_ip_info( $ip );
		            $response['data'][ $ip_id ] = $ip_info['hostname'];
	            }
                break;
            case 'country':
	            if ($country_list = lab_get_country($ip_list, false)) {
		            foreach ( $country_list as $ip_id => $country ) {
			            if ( $country ) {
				            $response['data'][ $ip_id ] = cerber_get_flag_html( $country ) . cerber_country_name( $country );
			            }
			            else {
				            $response['data'][ $ip_id ] = __( 'Unknown', 'wp-cerber' );
			            }
		            }
	            }
	            break;

		}

	}
    elseif ( $admin && isset( $_REQUEST['dismiss_info'] ) ) {
		if ( isset( $_REQUEST['button_id'] ) && ( $_REQUEST['button_id'] == 'lab_ok' || $_REQUEST['button_id'] == 'lab_no' ) ) {
			lab_user_opt_in( $_REQUEST['button_id'] );
		}
		else {
			update_site_option( 'cerber_admin_info', '' );
		}
	}

	echo json_encode( $response );
	wp_die();
}
/*
 * Retrieve extended IP information
 * @since 2.2
 *
 */
function cerber_get_ip_info($ip, $cache_only = false){

	//$ip_id = str_replace('.','-',$ip);
	//$ip_id = str_replace(':','_',$ip_id); // IPv6

	$ip_id = cerber_get_id_ip($ip);

	$ip_info = @unserialize(get_transient($ip_id)); // lazy way
	if ($cache_only) return $ip_info;

	if (empty($ip_info['hostname'])) {
		$ip_info = array();
		$hostname = @gethostbyaddr( $ip );
		if ( $hostname ) {
			$ip_info['hostname'] = $hostname;
		} else {
			$ip_info['hostname'] = __( 'unknown', 'wp-cerber' );
		}
		set_transient( $ip_id, serialize( array( 'hostname' => $hostname ) ), 24 * 3600 );
	}
	return $ip_info;
}


/*
	Admin dashboard actions
*/
//add_action('admin_init','cerber_admin_request');
add_action('wp_loaded','cerber_admin_request'); // @since 5.6
function cerber_admin_request(){

    if ( !is_admin() ) return;
	if ( !isset( $_REQUEST['cerber_nonce'] ) ) return;
	if ( !current_user_can( 'manage_options' ) || !wp_verify_nonce( $_REQUEST['cerber_nonce'], 'control' ) ) return;

	if ( $_SERVER['REQUEST_METHOD'] == 'GET') {
		if ( isset( $_GET['testnotify'] ) ) {
			//$to = implode(', ',cerber_get_email());
			$to = cerber_get_email();
			if ( cerber_send_notify( $_GET['testnotify'] ) ) {
				cerber_admin_message( __( 'Message has been sent to ', 'wp-cerber' ) . ' ' . $to );
			}
			else {
				cerber_admin_notice( __( 'Unable to send notification email', 'wp-cerber' ) . ' ' . $to );
			}
			wp_safe_redirect( remove_query_arg( 'testnotify' ) ); // mandatory!
			exit; // mandatory!
		}
		elseif ( isset( $_GET['lockdelete'] ) ) {
			$ip = $_GET['lockdelete'];
			if ( cerber_block_delete( $ip ) ) {
				cerber_admin_message( sprintf( __( 'Lockout for %s was removed', 'wp-cerber' ), $ip ) );
			}
		}
		elseif ( isset( $_GET['export_activity'] ) ) {
			cerber_export_activity();
		}
		elseif ( isset( $_GET['subscribe'] ) ) {
			$mode = ( 'on' == $_GET['subscribe'] ) ? 'on' : 'off';
			cerber_subscribe( $mode );
			wp_safe_redirect( remove_query_arg( 'subscribe' ) ); // mandatory!
			exit; // mandatory!
		}
		elseif ( isset( $_GET['citadel'] ) && $_GET['citadel'] == 'deactivate' ) {
			cerber_disable_citadel();
		}
		elseif ( isset( $_GET['load_settings'] ) && $_GET['load_settings'] == 'default' ) {
			cerber_load_defaults();
			cerber_admin_message( __( 'Settings saved', 'wp-cerber' ) );
			wp_safe_redirect( remove_query_arg( array( 'load_settings', 'cerber_nonce' ) ) ); // mandatory!
			exit; // mandatory!
		}
		elseif ( isset( $_GET['force_repair_db'] ) ) {
			cerber_create_db();
			cerber_upgrade_db( true );
			cerber_admin_message( 'Cerber\'s tables has been upgraded' );
			wp_safe_redirect( remove_query_arg( array( 'force_repair_db', 'cerber_nonce' ) ) ); // mandatory!
			exit; // mandatory!
		}
		elseif ( isset( $_GET['force_check_nodes'] ) ) {
			$best = lab_check_nodes( true );
			cerber_admin_message( 'Cerber Lab\'s nodes has been checked. The closest node: ' . $best );
			wp_safe_redirect( remove_query_arg( array( 'force_check_nodes', 'cerber_nonce' ) ) ); // mandatory!
			exit; // mandatory!
		}
	}

	if ( $_SERVER['REQUEST_METHOD'] == 'POST') {
		if ( isset( $_POST['crb_geo_rules'] ) ) {
			crb_save_geo_rules();
		}
		elseif ( isset( $_POST['cerber_license'] ) ) {
			$lic = preg_replace( "/[^A-Z0-9]/i", '', $_POST['cerber_license'] );
			if ( !empty($lic) && strlen( $lic ) != LAB_KEY_LENGTH ) {
				return;
			}
			lab_update_key($lic);
			lab_validate_lic();
		}
    }


}

/**
 * Generate export CSV file using $_GET parameters (via cerber_activity_query())
 *
 * @since 4.16
 *
 */
function cerber_export_activity() {
	global $wpdb;

	//'per_page' = 0 means retrieve full data set, is used for export
	list( $query, $per_page, $falist, $ip, $filter_login, $user_id, $search ) = cerber_activity_query( array('per_page' => 0) );

	if ( $rows = $wpdb->get_results( $query ) ) {
		$total = $wpdb->get_var( "SELECT FOUND_ROWS()" );

		$fname = rawurlencode('wp-cerber-activity'); // encode non-ASCII symbols

		header($_SERVER["SERVER_PROTOCOL"].' 200 OK');
		header("Content-type: application/force-download");
		header("Content-Type: application/octet-stream");
		header("Content-Disposition: attachment; filename*=UTF-8''{$fname}.csv");

		echo '"Generated by:","WP Cerber security plugin"'."\r\n";
		echo '"Date:","'.cerber_date(time()).'"'."\r\n";
		echo '"Total rows:","'.$total.'"'."\r\n";
		echo '"Website:","'.get_option( 'blogname' ).'"'."\r\n";
		if ($ip) echo '"Filter by IP:","'.$ip.'"'."\r\n";
		elseif (!empty($_GET['filter_ip'])) echo '"Filter by IP:","'.$_GET['filter_ip'].'"'."\r\n"; // workaround
		if ($user_id) {
			$user = get_userdata($user_id);
			echo '"Filter by user:","'.$user->display_name.'"'."\r\n";
		}
		if ($search) echo '"Search results for:","'.$search.'"'."\r\n";

		echo "\r\n";

		$heading = array(__('IP address','wp-cerber'),__('Date','wp-cerber'),__('Event','wp-cerber'),__('Local User','wp-cerber'),__('User login','wp-cerber'),__('User ID','wp-cerber'),__('Username used','wp-cerber'),'Unix timestamp','Session ID');
		foreach ($heading as &$item) {
			$item = '"' . str_replace('"', '""', trim($item)) . '"';
		}
		echo implode(',', $heading) . "\r\n";

		$labels = cerber_get_labels('activity');

		foreach ($rows as $row) {
			if (!empty($row->details)) {
			    $details = explode('|',$row->details);
			}
			else {
				$details = array('','','','','');
            }
			$values = array();
			$values[] = $row->ip;
			$values[] = cerber_date($row->stamp);
			$values[] = $labels[$row->activity];
			$values[] = $row->display_name;
			$values[] = $row->ulogin;
			$values[] = $row->user_id;
			$values[] = $row->user_login;
			$values[] = $row->stamp;
			$values[] = $row->session_id;
			$values[] = $details[4];
			$values[] = $details[0];
			foreach ($values as &$value) {
				$value = '"' . str_replace('"', '""', trim($value)) . '"';
			}
			$line = implode(',', $values) . "\r\n";
			echo $line;
		}
		exit;
	}
	else wp_die('Nothing to export');
}
/*
 * Display activities in the WP Dashboard
 * @since 1.0
 *
 */
function cerber_show_activity($args = array(), $echo = true){
	global $wpdb, $crb_assets_url, $wp_cerber, $wp_roles;

	$labels = cerber_get_labels('activity');
	$status_labels = cerber_get_labels('status');

	$base_url = cerber_admin_link('activity');
	$right_links = '';
	$export_link = '';
	$table = '';
	$ret = '';

	list( $query, $per_page, $falist, $filter_ip, $filter_login, $user_id, $search ) = cerber_activity_query( $args );

	$ip_text = cerber_ip_extra_view( $filter_ip );
	$user_cache = array();

	if ( $rows = $wpdb->get_results( $query ) ) {

		$total = $wpdb->get_var( "SELECT FOUND_ROWS()" );
		//$crb_assets_url = plugin_dir_url( CERBER_FILE ) . 'assets/';
		$list    = array();
		$tbody   = '';
		$roles   = $wp_roles->roles;
		$country = '';
		$geo     = lab_lab();

		foreach ($rows as $row) {

			$ip_id = cerber_get_id_ip($row->ip);

			$activity = '<span class="crb-activity actv' . $row->activity . '">'.$labels[ $row->activity ].'</span>';
			/*
			if ($row->activity == 50 ) {
				$activity .= ' <b>'.htmlspecialchars($row->user_login).'</b>';
            }*/

			if ( empty( $args['no_details'] ) && $row->details ) {
				$details = explode( '|', $row->details );
				if ( ! empty( $details[0] ) ) {
					$activity .= ' <span class = "act-details">' . $status_labels[ $details[0] ] . '</span>';
				}
				//elseif ($row->activity == 50 && $details[4]) $activity .= ' '.$details[4];

				if ( isset( $details[4] ) && ( $row->activity < 10 || $row->activity > 12 ) ) {
					$activity .= '<p class="act-url">URL: ' . $details[4] . '</p>';
				}

			}
			$activity = '<div class="crb'.$row->activity.'">'.$activity.'</div>';

			if ( $row->user_id ) {
				if ( isset( $user_cache[ $row->user_id ] ) ) {
					$name = $user_cache[ $row->user_id ];
				} elseif ( $u = get_userdata( $row->user_id ) ) {

					if ( ! is_multisite() && $u->roles ) {
						$r = array();
						foreach ( $u->roles as $role ) {
							$r[] = $roles[ $role ]['name'];
						}
						$r = '<span class="act-role">' . implode( ', ', $r ) . '</span>';
					}

					$name = '<a href="' . $base_url . '&filter_user=' . $row->user_id . '"><b>' . $u->display_name . '</b></a><p>' . $r . '</p>';

					if ( 1 == 1 ) {
						$avatar = get_avatar( $row->user_id, 32 );
						$name   = '<table class="crb-avatar"><tr><td>' . $avatar . '</td><td>' . $name . '</td></tr></table>';
					}
				} else {
					$name = '';
				}

				$user_cache[ $row->user_id ] = $name;
			} else {
				$name = '';
			}

			$ip = '<a href="'.$base_url.'&filter_ip='.$row->ip.'">'.$row->ip.'</a>';
			$username = '<a href="'.$base_url.'&filter_login='.urlencode($row->user_login).'">'.$row->user_login.'</a>';

			$ip_info = cerber_get_ip_info($row->ip,true);
			if (isset($ip_info['hostname'])) $hostname = $ip_info['hostname'];
			else {
				$hostname = '<img data-ip-id="'.$ip_id .'" class="crb-no-hostname" src="'.$crb_assets_url.'ajax-loader.gif" />'."\n";
			}

			$tip='';

			$acl = cerber_acl_check($row->ip);
			if ($acl == 'W') $tip = __('White IP Access List','wp-cerber');
			elseif ($acl == 'B') $tip = __('Black IP Access List','wp-cerber');

			if ( cerber_block_check( $row->ip ) ) {
				$block = ' color-blocked ';
				$tip .= ' ' . __( 'Locked out', 'wp-cerber' );
			}
			else $block='';

			if ( ! empty( $args['date'] ) && $args['date'] == 'ago' ) {
				$date = cerber_ago_time( $row->stamp );
			}
			else {
				$date = '<span title="'.$row->stamp.' / '.$row->session_id.' / '.$row->activity .'">'.cerber_date( $row->stamp ).'<span/>';
			}

			if ( $geo ) {
			    /*
				if ( $row->country ) {
					$code = $row->country;
				} else {
					$code = lab_get_country( $row->ip );
				}

				if ( $code ) {
					//$country = cerber_get_flag_html( $code ) . '<a href="'.$base_url.'&filter_country='.$code.'">'.cerber_country_name( $code ).'</a>';
					$country = cerber_get_flag_html( $code ) . cerber_country_name( $code );
				} else {
					$country = '<img data-ip-id="' . $ip_id . '" class="crb-no-country" src="' . $crb_assets_url . 'ajax-loader.gif" />' . "\n";
				}

				$country = '</td><td>' . $country;
				*/
				$country = '</td><td>' . crb_country_html($row->country, $row->ip);
			}

			$tbody .= '<tr class="acrow'.$row->activity.'"><td><div class="act-icon ip-acl' . $acl . ' ' . $block . '" title="' . $tip . '"></div>' . $ip . '</td><td>' . $hostname . $country . '</td><td>' . $date . '</td><td class="acinfo">' . $activity . '</td><td>' . $name . '</td><td>' . $username . '</td></tr>';
		}

		//$titles = '<tr><th><div class="act-icon"></div>' . __( 'IP', 'wp-cerber' ) . '</th><th>' . __( 'Hostname', 'wp-cerber' ) . '</th><th>' . __( 'Date', 'wp-cerber' ) . '</th><th>' . __( 'Event', 'wp-cerber' ) . '</th><th>' . __( 'Local User', 'wp-cerber' ) . '</th><th>' . __( 'Username used', 'wp-cerber' ) . '</th></tr>';

        $heading = array(
			'<div class="act-icon"></div>' . __( 'IP', 'wp-cerber' ),
			__( 'Hostname', 'wp-cerber' ),
	        __( 'Country', 'wp-cerber' ),
			__( 'Date', 'wp-cerber' ),
			__( 'Event', 'wp-cerber' ),
			__( 'Local User', 'wp-cerber' ),
			__( 'Username used', 'wp-cerber' )
		);

		if ( !lab_lab() ) {
			unset( $heading[2] );
		}

		$titles = '<tr><th>' . implode( '</th><th>', $heading ) . '</th></tr>';

		$table  = '<table id="crb-activity" class="widefat crb-table cerber-margin"><thead>' . $titles . '</thead><tfoot>' . $titles . '</tfoot><tbody>' . $tbody . '</tbody></table>';

		if (empty($args['no_navi'])) $table .= cerber_page_navi($total, $per_page);

		//$legend  = '<p>'.sprintf(__('Showing last %d records from %d','wp-cerber'),count($rows),$total);

		$info = $ip_text;

		if (empty($args['no_export'])) $export_link = '<a class="button button-secondary cerber-button" href="'.wp_nonce_url(add_query_arg('export_activity',1),'control','cerber_nonce').'"><span class="dashicons dashicons-download" style="vertical-align: middle;"></span> '.__('Export','wp-cerber').'</a>';
	}
	else {
		$info = '';
		$table = '<p class="cerber-margin">'.__('No activity has been logged.','wp-cerber').'</p>';
	}

	if (empty($args['no_navi'])) {

		unset( $labels[13], $labels[14], $labels[15] );
		$labels = array( 0 => __( 'All events', 'wp-cerber' ) ) + $labels;

		if (!empty($_GET['filter_activity']) && !is_array($_GET['filter_activity'])) {
		    $selected = absint($_GET['filter_activity']);
		}
		else $selected = 0;

		$filters = '<form style="float: left; width: auto;" action="">'
		           . cerber_select('filter_activity', $labels, $selected)
		           .'<input type="text" value="'.$search.'" name="search_activity" placeholder="'.__('Search for IP or username','wp-cerber').'"><input type="submit" value="'.__('Filter','wp-cerber').'" class="button">'
		           .'
		           <!-- Preserve values -->
		           <input type="hidden" name="filter_ip" value="'.htmlspecialchars($filter_ip).'" >
		           <input type="hidden" name="filter_user" value="'.$user_id.'" >
		           <input type="hidden" name="filter_login" value="'.$filter_login.'" >
		           
		           <input type="hidden" name="page" value="cerber-security" >
		           <input type="hidden" name="tab" value="activity">
		           </form>';

		$right_links = '<div style="float: right; width: auto; line-height: 26px;">'.cerber_subscribe_link().$export_link.'</div>';

		$top_bar = '<div id = "activity-filter">'.$filters.$right_links.'</div><br style="clear: both;">';

		$ret = '<div class="cerber-margin">' . $top_bar . $info . '</div>'.$ret;
	}

	$ret .= $table;

	if ($echo) echo $ret;
	else return $ret;

}

/**
 * Parse arguments and create SQL query for retrieving rows from activity log
 *
 * @param array $args Optional arguments to use them instead of using $_GET
 *
 * @return array
 * @since 4.16
 */
function cerber_activity_query($args = array()){
	global $wpdb;

	$ret = array_fill( 0, 7, '' );
	$where = array();
	$falist = array();

	$filter = null;
	if (!empty($args['filter_activity'])) $filter = $args['filter_activity'];
	elseif (isset($_GET['filter_activity'])) $filter = $_GET['filter_activity'];

	if ($filter) { // Multiple activities can be requested this way: &filter_activity[]=11&filter_activity[]=7
		if (is_array($filter)) {
			$falist = array_filter(array_map('absint',$filter));
			$filter = implode(',',$falist);
		}
		else {
			$filter = absint($filter);
			$falist = array($filter); // for further using in links
		}
		$where[] = 'log.activity IN ('.$filter.')';
	}
	$ret[2] = $falist;

	if ( ! empty( $_GET['filter_ip'] ) ) {
		$filter = trim( $_GET['filter_ip'] );
		$range = cerber_any2range( $filter );
		if ( is_array( $range ) ) {
			$where[] = $wpdb->prepare( '(log.ip_long >= %d AND log.ip_long <= %d)', $range['begin'], $range['end'] );
		} elseif ( cerber_is_ip_or_net( $filter ) ) {
			$where[] = $wpdb->prepare( 'log.ip = %s', $filter );
			//$ip_extra = $filter;
		} else {
			$where[] = "ip = 'produce-no-result'";
		}
		$ret[3] = $_GET['filter_ip'];
	}

	if (!empty($_GET['filter_login'])) {
		$where[] = $wpdb->prepare('log.user_login = %s',$_GET['filter_login']);
		$ret[4] = htmlspecialchars($_GET['filter_login']);
	}
	if (!empty($_GET['filter_user'])) {
		$user_id = absint($_GET['filter_user']);
		$ret[5] = $user_id;
		$where[] = 'log.user_id = '.$user_id;
	}
	if (!empty($_GET['search_activity'])) {
		$search = stripslashes_deep($_GET['search_activity']);
		$ret[6] = htmlspecialchars($search);
		$search = '%'.$search.'%';
		$where[] = $wpdb->prepare('(log.ip LIKE %s OR log.user_login LIKE %s)', $search, $search);
	}
	if (!empty($_GET['filter_country'])) {
		$country = substr($_GET['filter_country'], 0, 3);
		$ret[7] = htmlspecialchars($country);
		$where[] = 'log.country = "'.$country.'"';
	}

	if (!empty($where)) $where = 'WHERE '.implode(' AND ',$where);
	else $where = '';

	// Limits, if specified
	if (isset($args['per_page'])) $per_page = $args['per_page'];
	else $per_page = cerber_get_per_page();
	$per_page = absint($per_page);
	$ret[1] = $per_page;

	if ( $per_page ) {
		$limit = ' LIMIT ' . ( cerber_get_pn() - 1 ) * $per_page . ',' . $per_page;
		$ret[0] = 'SELECT SQL_CALC_FOUND_ROWS * FROM ' . CERBER_LOG_TABLE . " log {$where} ORDER BY stamp DESC {$limit}";
	}
	else {
		$ret[0] = 'SELECT SQL_CALC_FOUND_ROWS log.*,u.display_name,u.user_login ulogin FROM ' . CERBER_LOG_TABLE . ' log LEFT JOIN '.$wpdb->users . " u ON (log.user_id = u.ID) {$where} ORDER BY stamp DESC";
	}

	//$ret[0] = 'SELECT SQL_CALC_FOUND_ROWS log.*,u.display_name,u.user_login ulogin FROM ' . CERBER_LOG_TABLE . ' log LEFT JOIN ' . $wpdb->users . " u ON (log.user_id = u.ID) {$where} ORDER BY stamp DESC {$limit}";

	return $ret;
/*
	return array(
		//'SELECT SQL_CALC_FOUND_ROWS * FROM ' . CERBER_LOG_TABLE . " {$where} ORDER BY stamp DESC {$limit}",
		'SELECT SQL_CALC_FOUND_ROWS log.*,u.display_name,u.user_login ulogin FROM ' . CERBER_LOG_TABLE . ' log LEFT JOIN '.$wpdb->users . " u ON (log.user_id = u.ID) {$where} ORDER BY stamp DESC {$limit}",
		$per_page,
		$falist,
		$ip_extra,
		$user_id,
	);
*/
}
/*
 * Detailed information about IP address
 * @since 2.7
 *
 */

function cerber_ip_extra_view($ip){
	global $wp_cerber;
	//if (!cerber_is_ip_or_net($ip)) return '';
	if ( !@inet_pton( $ip ) ) {
		return '';
	}
	$tip = ' ';
	$acl = cerber_acl_check( $ip );
	if ( $acl == 'W' ) {
		$tip .= '<span class="color-green ip-info-label">'.__( 'White IP Access List', 'wp-cerber' ).'</span> ';
	} elseif ( $acl == 'B' ) {
		$tip .= '<span class="color-black ip-info-label">'.__( 'Black IP Access List', 'wp-cerber' ).'</span> ';
	}
	if ( cerber_block_check( $ip ) ) {
		$tip .= '<span class="color-blocked ip-info-label">'.__( 'Locked out', 'wp-cerber' ).'</span> ';
	}

	// Filter activity by ...

	/*$labels = cerber_get_labels('activity');
	foreach ($labels as $tag => $label) {
		//if (in_array($tag,$falist)) $links[] = '<b>'.$label.'</b>';
		$links[] = '<a href="'.$base_url.'&filter_activity='.$tag.'">'.$label.'</a>';
	}
	$filters = implode(' | ',$links);*/

	$whois = '';
	$country = '';
	$abuse = '';
	$network = '';
	$network_info = '';

	if (cerber_get_options('ip_extra')) {
		$ip_info = cerber_ip_whois_info($ip);
		if (isset($ip_info['whois'])) $whois = '<div id="whois">' . $ip_info['whois'] . '</div>';
		if (isset($ip_info['error'])) $whois = '<div id="whois">' . $ip_info['error'] . '</div>';
		if (isset($ip_info['country'])) $country = $ip_info['country'];
		if (!empty($ip_info['data']['abuse-mailbox'])) $abuse = '<p>'.__('Abuse email:','wp-cerber').' <a href="mailto:'.$ip_info['data']['abuse-mailbox'].'">'.$ip_info['data']['abuse-mailbox'].'</a></p>';
		if (!empty($ip_info['data']['network'])) {
			$network = $ip_info['data']['network'];
			$range = cerber_any2range($network);
			$network_info = '<p>'.__('Network:','wp-cerber').' '.$network.' &nbsp; <a href="'.cerber_admin_link('activity',array('filter_ip'=>$range['range'])).'">'.__('Check for activity','wp-cerber').'</a></p>';
		}
	}

	$form = '';
	//if (!cerber_is_myip($ip) && !cerber_acl_check($ip)) $form = '<form action="" method="post"><input type="hidden" name="add_acl_B" value="'.$ip.'"><input type="submit" class="button button-primary cerber-button" value="'.__('Add IP to the Black List','wp-cerber').'" >'.wp_nonce_field('cerber_dashboard','cerber_nonce').'</form>';

	if (!cerber_is_myip($ip) && !cerber_acl_check($ip)) {

		if ( $network ) {
			$net_button = '<button type="submit" value="' . $network . '" name="add_acl_B" class="button button-primary cerber-button">';
		} else {
			$net_button = '<button disabled="disabled" class="button button-secondary cerber-button">';
		}
		$net_button .= '<span class="dashicons-before dashicons-networking"></span> ' . __( 'Add network to the Black List', 'wp-cerber' ) . '</button> ';

		$form = '<form id="add-acl-black" action="" method="post">
				<!-- <input type="hidden" name="add_acl_B" value=""> -->
				<button type="submit" value="'.$ip.'" name="add_acl_B" class="button button-primary cerber-button"><span class="dashicons-before dashicons-desktop"></span> '.__('Add IP to the Black List','wp-cerber').'</button> '.
		        $net_button.
		        wp_nonce_field('cerber_dashboard','cerber_nonce').
		        '</form>';
	}

	$ret = '<div id="ip-extra">
			<table>
			<tr><td><p><span id = "ip-address">' . $ip . '</span><span id = "ip-country">' . $country .'</span>'. $tip .'</p>' . $network_info . $abuse . '</td><td>' . $form . '</td></tr>
			</table>
			</div>';

	return $ret.$whois;
}

/*
	Add admin menu, init admin stuff
*/
if ( ! is_multisite() ) {
	add_action( 'admin_menu', 'cerber_admin_menu' );
}
else {
	add_action( 'network_admin_menu', 'cerber_admin_menu' );  // only network wide menu allowed in multisite mode
}
function cerber_admin_menu() {

	if ( cerber_is_admin_page() ) {
		cerber_check_environment();
		cerber_watchdog();
	}

	$hook = add_menu_page( __( 'WP Cerber Security', 'wp-cerber' ), __( 'WP Cerber', 'wp-cerber' ), 'manage_options', 'cerber-security', 'cerber_settings_page', 'dashicons-shield', '100' );
	add_action( 'load-' . $hook, "cerber_screen_options" );
	add_submenu_page( 'cerber-security', __( 'Cerber Dashboard', 'wp-cerber' ), __( 'Dashboard' ), 'manage_options', 'cerber-security', 'cerber_settings_page' );

	if (lab_lab()) {
	    add_submenu_page( 'cerber-security', __( 'Cerber Security Rules', 'wp-cerber' ), __( 'Security Rules', 'wp-cerber' ), 'manage_options', 'cerber-rules', 'cerber_rules_page' );
	}

	add_submenu_page( 'cerber-security', __( 'Cerber antispam settings', 'wp-cerber' ), __( 'Antispam', 'wp-cerber' ), 'manage_options', 'cerber-recaptcha', 'cerber_recaptcha_page' );
	add_submenu_page( 'cerber-security', __( 'Cerber tools', 'wp-cerber' ), __( 'Tools', 'wp-cerber' ), 'manage_options', 'cerber-tools', 'cerber_tools_page' );

}

add_action( 'admin_bar_menu', 'cerber_admin_bar' );
function cerber_admin_bar( $wp_admin_bar ) {
	if (!is_multisite()) return;
	$args = array(
		'parent' => 'network-admin',
		'id'    => 'cerber_admin',
		'title' => __('WP Cerber','wp-cerber'),
		'href'  => cerber_admin_link(),
	);
	$wp_admin_bar->add_node( $args );
}

/*
	Check if currently displayed page is a Cerber admin dashboard page with optional checking a set of GET params
*/
function cerber_is_admin_page( $force = true, $params = array() ) {

	if ( ! is_admin() ) {
		return false;
	}

	$ret = false;

	if ( isset( $_GET['page'] ) && false !== strpos( $_GET['page'], 'cerber-' ) ) {
		$ret = true;
		if ( $params ) {
			foreach ( $params as $param => $value ) {
				if ( ! isset( $_GET[ $param ] ) || $_GET[ $param ] != $value ) {
					$ret = false;
					break;
				}
			}
		}
	}
	if ( $ret || !$force) {
		return $ret;
	}

	if ( ! $screen = get_current_screen() ) {
		return false;
	}
	if ( $screen->base == 'plugins' ) {
		return true;
	}
	/*
	if ($screen->parent_base == 'options-general') return true;
	if ($screen->parent_base == 'settings') return true;
	*/
	return false;
}

// Users -------------------------------------------------------------------------------------

add_filter('users_list_table_query_args' , function ($args) {
	global $wp_cerber;
	if ( $wp_cerber->getSettings( 'usersort' ) && empty( $args['orderby'] ) ) {
		$args['orderby'] = 'user_registered';
		$args['order'] = 'desc';
    }
    return $args;
});

/*
	Add custom columns to the Users admin screen
*/
add_filter( 'manage_users_columns', function ( $columns ) {
	return array_merge( $columns,
		array(
			'cbcc' => __( 'Comments', 'wp-cerber' ),
			'cbla' => __( 'Last login', 'wp-cerber' ),
			'cbfl' => '<span title="In last 24 hours">' . __( 'Failed login attempts', 'wp-cerber' ) . '</span>',
			'cbdr' => __( 'Registered', 'wp-cerber' )
		) );
} );
add_filter( 'manage_users_sortable_columns', function ( $sortable_columns ) {
	$sortable_columns['cbdr'] = 'user_registered';

	return $sortable_columns;
} );
/*
	Display custom columns on the Users screen
*/
add_filter( 'manage_users_custom_column' , function ($value, $column, $user_id) {
	global $wpdb, $user_ID;
	$ret = $value;
	switch ($column) {
		case 'cbcc' : // to get this work we need add filter 'preprocess_comment'
			if ($com = get_comments(array('author__in' => $user_id)))	$ret = count($com);
			else $ret = 0;
		break;
		case 'cbla' :
			//$row = $wpdb->get_row('SELECT MAX(stamp) FROM '.CERBER_LOG_TABLE.' WHERE user_id = '.absint($user_id));
			$row = $wpdb->get_row('SELECT * FROM '.CERBER_LOG_TABLE.' WHERE activity = 5 AND user_id = '.absint($user_id) . ' ORDER BY stamp DESC LIMIT 1');
			if ($row) {
				$act_link = cerber_admin_link('activity');
				if ( $country = crb_country_html( $row->country, $row->ip ) ) {
					$country = '<br>' . $country;
				} else {
					$country = '';
                }
				$ret = '<a href="'.$act_link.'&filter_user='.$user_id.'">'.cerber_date($row->stamp).'</a>'.$country;
			}
			else $ret=__('Never','wp-cerber');
		break;
		case 'cbfl' :
			$u      = get_userdata( $user_id );
			$failed = $wpdb->get_var( 'SELECT COUNT(user_id) FROM ' . CERBER_LOG_TABLE . ' WHERE user_login = \'' . $u->user_login . '\' AND activity = 7 AND stamp > ' . ( time() - 24 * 3600 ) );
			if ( $failed ) {
				$act_link = cerber_admin_link( 'activity' );
				$ret      = '<a href="' . $act_link . '&filter_login=' . $u->user_login . '&filter_activity=7">' . $failed . '</a>';
			}
			else {
				$ret = $failed;
			}
		break;
		case 'cbdr' :
			$time = strtotime($wpdb->get_var("SELECT user_registered FROM  $wpdb->users WHERE id = ".$user_id));
			if ($time < (time() - DAY_IN_SECONDS)){
				$ret = cerber_date($time);
            }
            else {
	            $ret = cerber_ago_time($time);
            }
			if ($rm = get_user_meta($user_id, '_crb_reg_', true)){
				if ($rm['IP']) {
					$act_link = cerber_admin_link( 'activity', array( 'filter_ip' => $rm['IP'] ) );
				    $ret .= '<br><a href="'.$act_link.'">'.$rm['IP'].'</a>';
					if ( $country = crb_country_html( null, $rm['IP'] ) ) {
						$ret .= '<br>' . $country;
					}
				}
				$uid = absint( $rm['user'] );
				if ( $uid ) {
					$name = $wpdb->get_var( 'SELECT meta_value FROM ' . $wpdb->usermeta . ' WHERE user_id  = ' . $uid . ' AND meta_key = "nickname"' );
					if (!$user_ID) {
					    $user_ID = get_current_user_id();
				    }
				    if ($user_ID == $uid){
					    $name .= ' (' . __( 'You', 'wp-cerber' ) . ')';
                    }
					$ret .= '<br>' . $name;
				}
            }
		break;
	}
	return $ret;
}, 10, 3);

/*
 	Registering admin widgets
*/
if (!is_multisite()) add_action( 'wp_dashboard_setup', 'cerber_widgets' );
else add_action( 'wp_network_dashboard_setup', 'cerber_widgets' );
function cerber_widgets() {
	if (!current_user_can('manage_options')) return;
	if (current_user_can( 'manage_options')) {
		wp_add_dashboard_widget( 'cerber_quick', __('Cerber Quick View','wp-cerber'), 'cerber_quick_w');
	}
}
/*
	Cerber Quick View widget
*/
function cerber_quick_w(){
	global $wpdb, $wp_cerber;

	$dash = cerber_admin_link();
	$act = cerber_admin_link('activity');
	$acl = cerber_admin_link('acl');
	$loc = cerber_admin_link('lockouts');

	$failed = $wpdb->get_var('SELECT count(ip) FROM '. CERBER_LOG_TABLE .' WHERE activity IN (7) AND stamp > '.(time() - 24 * 3600));
	$failed_prev = $wpdb->get_var('SELECT count(ip) FROM '. CERBER_LOG_TABLE .' WHERE activity IN (7) AND stamp > '.(time() - 48 * 3600).' AND stamp < '.(time() - 24 * 3600));

	$failed_ch = cerber_percent($failed_prev,$failed);

	$locked = $wpdb->get_var('SELECT count(ip) FROM '. CERBER_LOG_TABLE .' WHERE activity IN (10,11) AND stamp > '.(time() - 24 * 3600));
	$locked_prev = $wpdb->get_var('SELECT count(ip) FROM '. CERBER_LOG_TABLE .' WHERE activity IN (10,11) AND stamp > '.(time() - 48 * 3600).' AND stamp < '.(time() - 24 * 3600));

	$locked_ch = cerber_percent($locked_prev,$locked);

	//$lockouts = $wpdb->get_var('SELECT count(ip) FROM '. CERBER_BLOCKS_TABLE);

    $lockouts = cerber_blocked_num(); 
	if ($last = $wpdb->get_var('SELECT MAX(stamp) FROM '.CERBER_LOG_TABLE.' WHERE  activity IN (10,11)')) {
		//$last = cerber_date($last);
		$last = cerber_ago_time( $last );
	}
	else $last = __('Never','wp-cerber');
	$w_count = $wpdb->get_var('SELECT count(ip) FROM '. CERBER_ACL_TABLE .' WHERE tag ="W"' );
	$b_count = $wpdb->get_var('SELECT count(ip) FROM '. CERBER_ACL_TABLE .' WHERE tag ="B"' );

	if (cerber_is_citadel()) $citadel = '<span style="color:#FF0000;">'.__('active','wp-cerber').'</span> (<a href="'.wp_nonce_url(add_query_arg(array('citadel' => 'deactivate')),'control','cerber_nonce').'">'.__('deactivate','wp-cerber').'</a>)';
	else {
		if ($wp_cerber->getSettings('ciperiod')) $citadel = __('not active','wp-cerber');
		else $citadel = __('disabled','wp-cerber');
	}

	echo '<div class="cerber-widget">';

	echo '<table style="width:100%;"><tr><td style="width:50%; vertical-align:top;"><table><tr><td class="bigdig">'.$failed.'</td><td class="per">'.$failed_ch.'</td></tr></table><p>'.__('failed attempts','wp-cerber').' '.__('in 24 hours','wp-cerber').'<br/>(<a href="'.$act.'&filter_activity=7">'.__('view all','wp-cerber').'</a>)</p></td>';
	echo '<td style="width:50%; vertical-align:top;"><table><tr><td class="bigdig">'.$locked.'</td><td class="per">'.$locked_ch.'</td></tr></table><p>'.__('lockouts','wp-cerber').' '.__('in 24 hours','wp-cerber').'<br/>(<a href="'.$act.'&filter_activity[]=10&filter_activity[]=11">'.__('view all','wp-cerber').'</a>)</p></td></tr></table>';

	echo '<table id="quick-info"><tr><td>'.__('Lockouts at the moment','wp-cerber').'</td><td>'.$lockouts.'</td></tr>';
	echo '<tr><td>'.__('Last lockout','wp-cerber').'</td><td>'.$last.'</td></tr>';
	echo '<tr><td style="padding-top:15px;">'.__('White IP Access List','wp-cerber').'</td><td style="padding-top:15px;"><b>'.$w_count.' '._n('entry','entries',$w_count,'wp-cerber').'</b></td></tr>';
	echo '<tr><td>'.__('Black IP Access List','wp-cerber').'</td><td><b>'.$b_count.' '._n('entry','entries',$b_count,'wp-cerber').'</b></td></tr>';
	echo '<tr><td style="padding-top:15px;">'.__('Citadel mode','wp-cerber').'</td><td style="padding-top:15px;"><b>'.$citadel.'</b></td></tr>';
	$dev = $wp_cerber->getSettings('pbdevice');
	if (!$dev || $dev == 'N') echo '<tr><td style="padding-top:15px;">'.__('Push notifications','wp-cerber').'</td><td style="padding-top:15px;"><b>not configured</b></td></tr>';
	echo '</table></div>';

	echo '<div class="wilinks">
	<a href="'.$dash.'"><span class="dashicons dashicons-dashboard"></span> ' . __('Dashboard','wp-cerber').'</a> |
	<a href="'.$act.'"><span class="dashicons dashicons-welcome-view-site"></span> ' . __('Activity','wp-cerber').'</a> |
	<a href="'.$loc.'"><span class="dashicons dashicons-shield"></span> ' . __('Lockouts','wp-cerber').'</a> |
	<a href="'.$acl.'"><span class="dashicons dashicons-admin-network"></span> ' . __('Access Lists','wp-cerber').'</a>
	</div>';
	if ( $new = cerber_check_version() ) {
		echo '<div class="up-cerber">' . $new['msg'] . '</div>';
	}
}

/*
	Show Help tab screen
*/
function cerber_show_help() {
    global $crb_assets_url;

	?>
	<div id="crb-help">
        <table id="admin-help">
            <tr><td>

                    <img style="width: 120px; float: left; margin-right: 30px; margin-bottom: 30px;" src="<?php echo $crb_assets_url . 'wrench.png' ?>"/>

                    <h3 style="font-size: 150%;">How to configure the plugin</h3>

                    <p style="font-size: 120%;">To get the most out of Cerber Security, you need to configure the plugin properly</p>

                    <p style="font-size: 120%;">Please read this first: <a href="https://wpcerber.com/getting-started/">Getting Started Guide</a></p>

                    <p style="clear: both;"></p>

                    <h3>Do you have a question or need help?</h3>

                    <p>Support is provided on the WordPress forum for free, though please note that it is free support hence it is
                        not always possible to answer all questions on a timely manner, although I do try.</p>

                    <p><span class="dashicons-before dashicons-book-alt"></span> <a href="https://wpcerber.com/toc/" target="_blank">Read articles on wpcerber.com</a></p>
                    <p><span class="dashicons-before dashicons-format-chat"></span> <a href="https://wordpress.org/support/plugin/wp-cerber">Get answer on the support forum</a></p>


                    <form style="margin-top: 2em;" action="https://wpcerber.com" target="_blank">
                        <h3>Search plugin documentation on wpcerber.com</h3>
                        <input type="text" style="width: 80%;" name="s" placeholder="Enter term to search"><input type="submit" value="Search" class="button button-primary">
                    </form>

                </td>
                <td>
                    <h3>What is IP address of your computer?</h3>

                    <p>To find out your current IP address go to this page: <a href="https://wpcerber.com/what-is-my-ip/">What is my IP</a>. If you see a different IP address on the Activity tab for your login or logout events you probably need to check <b><?php _e('My site is behind a reverse proxy','wp-cerber'); ?></b>.</p>

                    <h3>Setting up antispam protection</h3>

                    <p>
                        Cerber antispam and bot detection engine is capable to protect virtually any form on a website. It’s a great alternative to reCAPTCHA.
                    </p>
                    <p>
                        <span class="dashicons-before dashicons-book-alt"> <a href="https://wpcerber.com/antispam-for-wordpress-contact-forms/">Find out more about antispam protection</a>
                    </p>


                    <h3>Mobile and browser notifications with Pushbullet</h3>

                    <p>
                        WP Cerber allows you to easily enable desktop and mobile notifications and get notifications instantly and for free. In a desktop browser, you will get popup messages even if you logged out of your WordPress.
                        Before you start receiving notifications you need to install a free Pushbullet mobile application on your mobile device or free browser extension available for Chrome, Firefox and Opera.
                    </p>
                    <p><span class="dashicons-before dashicons-book-alt"></span>
                        <a href="https://wpcerber.com/wordpress-mobile-and-browser-notifications-pushbullet/">A three steps instruction how to set up push notifications</a>
                    </p>
                    <p><span class="dashicons-before dashicons-book-alt"></span>
                        <a href="https://wpcerber.com/wordpress-notifications-made-easy/">How to get alerts for specific activity on your website</a>
                    </p>

                </td>
            </tr>
        </table>

		<h3>What is Drill down IP?</h3>

		<p>
			To get extra information like country, company, network info, abuse contact etc. for a specific IP address,
			the plugin makes requests to a limited set of external WHOIS servers which are maintained by appropriate
			Registry. All Registry are accredited by ICANN, so there are no reasons for security concerns. Retrieved
			information isn't storing in the database, but it is caching for up to 24 hours to avoid excessive requests and
			get faster response.
		</p>
		<p><span class="dashicons-before dashicons-info" style="vertical-align: middle;"></span> <a
				href="http://wpcerber.com?p=194">Read more in the Security Blog</a></p>

		<h3>What is Cerber Lab?</h3>

		<p>
			Cerber Laboratory is a forensic team at Cerber Tech Inc. The team studies and analyzes
			patterns of hacker and botnet attacks, malware, vulnerabilities in major plugins and how they are
			exploitable on WordPress powered websites.
		</p>
			<p><span class="dashicons-before dashicons-info" style="vertical-align: middle;"></span>
			<a href="https://wpcerber.com/cerber-laboratory/">Know more</a>
			</p>

		<h3>Do you have an idea for a cool new feature that you would love to see in WP Cerber?</h3>

		<p>
			Feel free to submit your ideas here: <a href="http://wpcerber.com/new-feature-request/">New Feature
				Request</a>.
		</p>

		<h3>Are you ready to translate this plugin into your language?</h3>

		<p>I would appreciate that! Please, <a href="http://wpcerber.com/support/">notify me</a></p>

		<h3 style="margin: 40px 0 40px 0;">Check out other plugins from the trusted author</h3>

		<div>

			<a href="https://wordpress.org/plugins/plugin-inspector/">

				<img src="<?php echo $crb_assets_url . 'inspector.png' ?>"
				     style="float: left; width: 128px; margin-right: 20px;"/>
			</a>
			<h3>Plugin for inspecting code of plugins on your site: <a
					href="https://wordpress.org/plugins/plugin-inspector/">Plugin Inspector</a></h3>
			<p style="font-size: 110%">The Plugin Inspector plugin is an easy way to check plugins installed on your
				WordPress and make sure
				that plugins does not use deprecated WordPress functions and some unsafe functions like eval,
				base64_decode, system, exec etc. Some of those functions may be used to load malicious code (malware)
				from the external source directly to the site or WordPress database.
			</p>
			<p style="font-size: 110%">Plugin Inspector allows you to view all the deprecated functions complete with
				path, line number,
				deprecation function name, and the new recommended function to use. The checks are run through a simple
				admin page and all results are displayed at once. This is very handy for plugin developers or anybody
				who want to know more about installed plugins.
			</p>
		</div>

		<div style="margin: 40px 0 40px 0;">
			<a href="https://wordpress.org/plugins/goo-translate-widget/">
				<img src="<?php echo $crb_assets_url . 'goo-translate.png' ?>"
				     style="float: left; width: 128px; margin-right: 20px;"/>
			</a>

			<h3>Plugin to quick translate site: <a href="https://wordpress.org/plugins/goo-translate-widget/">Google
					Translate Widget</a></h3>
			<p style="font-size: 110%">Google Translate Widget expands your global reach quickly and easily. Google Translate is a free
				multilingual machine translation service provided by Google to translate websites. And now you can allow
				visitors around of the world to get your site in their native language. Just put widget on the sidebar
				with one click.</p>

		</div>

	</div>
	<?php
}

/**
 *
 * Dashboard v.1
 *
 * @since 4.0
 *
 */
function cerber_show_dashboard() {

	echo '<div style="padding-right: 30px;">';

	$kpi_list = cerber_calculate_kpi(1);

	$kpi_show = '';
	foreach ($kpi_list as $kpi){
		$kpi_show .= '<td>'.$kpi[1].'</td><td><span style="z-index: 10;">'.$kpi[0].'</span></td>';
    }

	$kpi_show = '<table id = "crb-kpi" class="cerber-margin"><tr>'.$kpi_show.'</tr></table>';

    // TODO: add link "send daily report to my email"
	echo '<div>' . $kpi_show . '<p style="text-align: right; margin: 0;">' . __( 'in the last 24 hours', 'wp-cerber' ) . '</p></div>';

	//$total = $wpdb->get_var( 'SELECT count(ip) FROM ' . CERBER_LOG_TABLE );
	//if ($total > $num) $l1 = 'Last ' . $num . ' suspect events are displayed';

	$links   = array();
	$links[] = '<a href="' . cerber_admin_link( 'activity' ) . '">' . __( 'View all', 'wp-cerber' ) . '</a>';

	$labels  = cerber_get_labels('activity');
	$set     = array( 5 );
	foreach ( $set as $item ) {
		$links[] = '<a href="' . cerber_admin_link( 'activity' ) . '&filter_activity=' . $item . '">' . $labels[ $item ] . '</a>';
	}

	$links[] = '<a href="' . cerber_activity_link( array( 2 ) ) . '">' . __( 'User registered', 'wp-cerber' ) . '</a>';
	$links[] = '<a href="' . cerber_activity_link( crb_get_activity_set( 'suspicious' ) ) . '">' . __( 'All suspicious activity', 'wp-cerber' ) . '</a>';


	$nav_links = '<span style="display: inline-block; margin-left: 1em;">' . implode(' &nbsp;|&nbsp; ',$links) . '</span>';

	echo '<table class="cerber-margin"><tr><td><h2 style="margin-bottom:0.5em;">' . __( 'Activity', 'wp-cerber' ) . '</h2></td><td>' . $nav_links . '</td></tr></table>';

	cerber_show_activity( array(
		'filter_activity' => array( 1, 2, 5, 10, 11, 12, 16, 17, 18, 19, 40, 41, 42, 50, 51, 52, 53, 54, 70, 71 ),
		'per_page'        => 10,
		'no_navi'         => true,
		'no_export'       => true,
		'no_details'      => true,
		'date'            => 'ago'
	) );


	//$total = $wpdb->get_var( 'SELECT count(ip) FROM ' . CERBER_BLOCKS_TABLE );
	//if ($total > $num) $l2 = '<p>Last ' . $num . ' lockouts of '.$total.' are displayed</p>';

	$view = '<a href="' . cerber_admin_link( 'lockouts' ) . '">' . __( 'View all', 'wp-cerber' ) . '</a>';
	echo '<table class="cerber-margin" style="margin-top:2em;"><tr><td><h2 style="margin-bottom:0.5em;">' . __( 'Recently locked out IP addresses', 'wp-cerber' ) . '</h3></td><td> | ' . $view . '</td></tr></table>';

	cerber_show_lockouts( array(
		'per_page' => 10,
		'no_navi'  => true
	) );

	echo '</div>';
}


/*
	Admin aside bar
*/
function cerber_show_aside($page){
	global $crb_assets_url;

	if (in_array($page,array('activity','lockouts'))) return;

	$aside = array();

	if (in_array($page,array('main'))) {
		$aside[]='<div class="crb-box">
			<h3>'.__('Confused about some settings?','wp-cerber').'</h3>'
			.__('You can easily load default recommended settings using button below','wp-cerber').'
			<p style="text-align:center;">
				<input type="button" class="button button-primary" value="'.__('Load default settings','wp-cerber').'" onclick="button_default_settings()" />
				<script type="text/javascript">function button_default_settings(){
					if (confirm("'.__('Are you sure?','wp-cerber').'")) {
						click_url = "'.wp_nonce_url(add_query_arg(array('load_settings'=>'default')),'control','cerber_nonce').'";
						window.location = click_url.replace(/&amp;/g,"&");
					}
				}</script>
			</p>
			<p><i>* '.__("doesn't affect Custom login URL and Access Lists",'wp-cerber').'</i></p>
			<p style="text-align: center; font-size: 110%;"><a href="https://wpcerber.com/getting-started/" target="_blank">' . __( 'Getting Started Guide', 'wp-cerber' ) . '</a></p>
		</div>';
	}
/*
	$aside[] = '<div class="crb-box" id = "crb-subscribe">
			<div class="crb-box-inner">
			<h3>Be in touch with developer</h3>
			<p>Receive updates and helpful ideas to protect your website, blog, or business online.</p>
			<p>
			<span class="dashicons-before dashicons-email-alt"></span> &nbsp; <a href="https://wpcerber.com/subscribe-newsletter/" target="_blank">Subscribe to Cerber\'s newsletter</a></br>
			<span class="dashicons-before dashicons-twitter"></span> &nbsp; <a href="https://twitter.com/wpcerber">Follow Cerber on Twitter</a></br>
			<span class="dashicons-before dashicons-facebook"></span> &nbsp; <a href="https://www.facebook.com/wpcerber/">Follow Cerber on Facebook</a>
			</p>
			</div>
			</div>
	';*/

	$aside[] = '
    <a class="crb-button-one" href="https://wpcerber.com/subscribe-newsletter/" target="_blank"><span class="dashicons dashicons-email-alt"></span> Subscribe to Cerber\'s newsletter</a>
    <a class="crb-button-one" style="background-color: #1DA1F2;" href="https://twitter.com/wpcerber" target="_blank"><span class="dashicons dashicons-twitter"></span> Follow Cerber on Twitter</a>
    <a class="crb-button-one" style="background-color: #3B5998;" href="https://www.facebook.com/wpcerber/" target="_blank"><span class="dashicons dashicons-facebook"></span> Follow Cerber on Facebook</a>
	';

	/*
	if (!lab_lab() && !in_array($page,array('geo'))) {
		$aside[] = '<div class="crb-box" id = "crb-donate">
			<div class="crb-box-inner">
			<h3>' . __( 'Donate', 'wp-cerber' ) . '</h3>
			<p>Hi! It\'s Gregory. I am an author of this plugin. Please consider making a donation to support the continued development and free support of this plugin because I spend my free time for that. Any help is greatly appreciated. Thanks!</p>
			
			<div style="text-align:center;">
			<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
			<input type="hidden" name="cmd" value="_s-xclick">
			<input type="hidden" name="hosted_button_id" value="SR8RJXFU35EW8">
			<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG_global.gif" border="0" name="submit" alt="PayPal – The safer, easier way to pay online.">
			<img alt="" border="0" src="https://www.paypalobjects.com/en_US/i/scr/pixel.gif" width="1" height="1">
			</form>
			</div>
			
			</div>
			</div>';
	}
*/
	/*$aside[] = '<div class="crb-box" id = "crb-jetflow">
			<div class="crb-box-inner">
			<h3>Automate WordPress</h3>
			<p>Create automation scenarios without coding knowledge with the jetFlow.io plugin. Customize your WordPress in no time. No programming knowledge needed anymore.</p>
			<p><span class="dashicons-before dashicons-layout"></span> &nbsp; <a href="http://jetflow.io/" target="_blank">Download the jetFlow.io plugin</a></p>
			</div>
			</div>
	';*/

	$aside[] = '<div class="crb-box" id = "crb-blog">
			<div class="crb-box-inner">
			<!-- <h3><span class="dashicons-before dashicons-lightbulb"></span> Read Cerber\'s blog</h3> --> 
			<h3>WordPress security blog</h3>			
			<p><a href="https://wpcerber.com/wordpress-ip-address-detection/" target="_blank">Solving problem with incorrect IP address detection</a>
			<p><a href="https://wpcerber.com/antispam-for-wordpress-contact-forms/" target="_blank">Antispam protection for WordPress forms</a>
			<p><a href="https://wpcerber.com/wordpress-mobile-and-browser-notifications-pushbullet/" target="_blank">Instant mobile and browser notifications</a>
			<p><a href="https://wpcerber.com/wordpress-notifications-made-easy/" target="_blank">WordPress notifications made easy</a>
			<p><a href="https://wpcerber.com/why-its-important-to-restrict-access-to-rest-api/" target="_blank">Why it’s important to restrict access to the WP REST API</a>
			<p><a href="https://wpcerber.com/why-we-need-to-use-custom-login-url/" target="_blank">Why you need to use Custom login URL</a>
			<p><a href="https://wpcerber.com/using-ip-access-lists-to-protect-wordpress/" target="_blank">How IP Access Lists works</a>
			<p><a href="https://wpcerber.com/hardening-wordpress-with-wp-cerber/" target="_blank">Hardening WordPress with WP Cerber</a>
		
		</div>
		</div>';

	if ( $a = get_site_option( '_cerber_activated', null ) ) {
		$a = maybe_unserialize($a);
		if ( ! empty( $a['time'] ) && $a['time'] < ( time() - WEEK_IN_SECONDS ) ) {
			$aside[] = '<a href="https://wordpress.org/support/plugin/wp-cerber/reviews/#new-post" target="_blank"><img style="width: 290px;" src="' . $crb_assets_url . 'rateit2.png" /></a>';
		}
	}

	echo '<div id="crb-aside">'.implode(' ',$aside).'</div>';
}

/*
	Displaying notices in the dashboard
*/
add_action( 'admin_notices', 'cerber_show_admin_notice', 999 );
add_action( 'network_admin_notices', 'cerber_show_admin_notice', 999 );
function cerber_show_admin_notice(){
	global $cerber_shown;
	$cerber_shown = false;

	if (cerber_is_citadel() && current_user_can('manage_options')) {
		echo '<div class="update-nag crb-alarm"><p>'.
		__('Attention! Citadel mode is now active. Nobody is able to log in.','wp-cerber').
		' &nbsp; <a href="'.wp_nonce_url(add_query_arg(array('citadel' => 'deactivate')),'control','cerber_nonce').'">'.__('Deactivate','wp-cerber').'</a>'.
		' | <a href="' . cerber_admin_link('activity') . '">' . __('View Activity','wp-cerber') . '</a>' .
		     '</p></div>';
	}

	if (!cerber_is_admin_page()) return;

	//if ($notices = get_site_option('cerber_admin_notice'))
	//	echo '<div class="update-nag crb-note"><p>'.$notices.'</p></div>'; // class="updated" - green, class="update-nag" - yellow and above the page title,
	//if ($notices = get_site_option('cerber_admin_message'))
	//	echo '<div class="updated" style="overflow: auto;"><p>'.$notices.'</p></div>'; // class="updated" - green, class="update-nag" - yellow and above the page title,

	$all = array();
	if ( ! empty( $_GET['settings-updated'] ) ) {
		$all[] = array( __( 'Settings saved', 'wp-cerber' ), 'updated' );
	}

	if ( $notice = get_site_option( 'cerber_admin_notice' ) ) {
		if ( is_array( $notice ) ) {
			$notice = '<p>' . implode( '</p><p>', $notice ) . '</p>';
		}
		$all[] = array( $notice, 'error' ); // red
	}
	if ( $notice = get_site_option( 'cerber_admin_message' ) ) {
		if ( is_array( $notice ) ) {
			$notice = '<p>' . implode( '</p><p>', $notice ) . '</p>';
		}
		$all[] = array( $notice, 'updated' ); // green
	}


	// yellow #ffb900;
	if ($all) {
		$cerber_shown = true;
		foreach ( $all as $notice ) {
			echo '<div id="setting-error-settings_updated" class="' . $notice[1] . ' settings-error notice is-dismissible"> 
		<p>' . $notice[0] . '</p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
		}
	}

	update_site_option('cerber_admin_notice', null);
	update_site_option('cerber_admin_message', null);

	if ($notice = get_site_option('cerber_admin_info')) { // need to be dismissed manually
		$cerber_shown = true;
		echo '<div class="updated cerber-msg" style="overflow: auto;"><p>'.$notice.'</p></div>';
	}
}

/**
 * Detects currently displaying Tab on admin page
 *
 * @param string $default
 * @param array $available
 *
 * @return string
 */
function cerber_get_tab( $default = '', $available = array() ) {

	$tab = isset( $_GET['tab'] ) ? $_GET['tab'] : $default;

	if ( ! in_array( $tab, $available ) ) {
		$tab = $default;
	}

	return $tab;
}

/**
 *
 * Generates a link for subscribing on a currently displaying Activity page
 *
 * @return string Link for using in the Dashboard, HTML
 */
function cerber_subscribe_link() {
	$args = array_values(cerber_subscribe_params());

	// All activities, without any filter is not allowed
	$empty = array_filter($args);
	if (empty($empty)) return '';

	$subs = get_site_option( '_cerber_subs' );

	// Limit on number of subscriptions
	if ( count( $subs ) > 50 ) return '';

	$mode = 'on';
	if ( $subs ) {
		$hash = sha1( json_encode( $args ) );
		if ( recursive_search_key( $subs, $hash ) ) {
			$mode = 'off';
		}
	}
	$link = wp_nonce_url( add_query_arg( 'subscribe', $mode ), 'control', 'cerber_nonce' );
	if ($mode == 'on') $text = __('Subscribe','wp-cerber');
	else $text = __('Unsubscribe','wp-cerber');

	return '<span class="dashicons dashicons-email" style="vertical-align: middle;"></span> <a id="subscribe-me" href="' . $link . '" style="margin-right: 1.5em;">'.$text.'</a>';
}

/**
 * Managing the list of subscriptions
 *
 * @param string $mode Add or delete a subscription
 * @param string $hash If specified, subscription with given hash will be removed
 */
function cerber_subscribe( $mode = 'on', $hash = null ) {
	if ($hash) {
		$mode = 'off';
	}
	else {
		$args = array_values( cerber_subscribe_params() );
		$hash = sha1( json_encode( $args ) );
	}

	$subs = get_site_option( '_cerber_subs' );

	if ( ! $subs ) {
		$subs = array();
	}

	if ( $mode == 'on' ) {
		$subs[ $hash ] = $args;
		$msg           = __( "You've subscribed", 'wp-cerber' );
	} else {
		unset( $subs[ $hash ] );
		$msg = __( "You've unsubscribed", 'wp-cerber' );
	}

	if ( update_site_option( '_cerber_subs', $subs ) ) {
		cerber_admin_message( $msg );
	}
}

// Unsubscribe with hash without nonce
add_action('admin_init',function(){
	if (!empty($_GET['unsubscribeme'])){
		cerber_subscribe('off',$_GET['unsubscribeme']);
		wp_safe_redirect(remove_query_arg('unsubscribeme'));
		exit;
	}
});

/*
	Pagination
*/
function cerber_page_navi($total,$per_page = 20){
	$max_links = 10;
	$page = cerber_get_pn();
	$last_page = ceil($total / $per_page);
	$ret = '';
	if($last_page > 1){
		$start =1 + $max_links * intval(($page-1)/$max_links);
		$end = $start + $max_links - 1;
		if ($end > $last_page) $end = $last_page;
		if ($start > $max_links) $links[]='<a href="'.esc_url(add_query_arg('pagen',$start - 1)).'" class="arrows"><b>&laquo;</b></a>';
		for ($i=$start; $i <= $end; $i++) {
			if($page!=$i) $links[]='<a href="'.esc_url(add_query_arg('pagen',$i)).'" >'.$i.'</a>';
			else $links[]='<a class="active" style="font-size: 16px;">'.$i.'</a> ';
		}
		if($end < $last_page) $links[]='<a href="'.esc_url(add_query_arg('pagen',$i)).'" class="arrows">&raquo;</a>'; // &#10141;
		$ret = '<table class="cerber-margin" style="margin-top:1em; border-collapse: collapse;"><tr><td><div class="pagination">'.implode(' ',$links).'</div></td><td><span style="margin-left:2em;"><b>'.$total.' '._n('entry','entries',$total,'wp-cerber').'</b></span></td></tr></table>';
	}
	return $ret;
}
function cerber_get_pn(){
	$page = 1;
	if ( isset( $_GET['pagen'] ) ) {
		$page = absint( $_GET['pagen'] );
		if ( ! $page ) {
			$page = 1;
		}
	}
	return $page;
}
/*
	Plugins screen links
*/
add_filter('plugin_action_links','cerber_action_links',10,4);
function cerber_action_links($actions, $plugin_file, $plugin_data, $context){
	if($plugin_file == cerber_plug_in()){
		$link[] = '<a href="' . cerber_admin_link() . '">' . __('Dashboard','wp-cerber') . '</a>';
		$link[] = '<a href="' . cerber_admin_link('main') . '">' . __('Main settings','wp-cerber') . '</a>';
		$actions = array_merge ($link,$actions);
	}
	return $actions;
}
/*
 * Create database diagnostic report
 *
 *
 */
function cerber_db_diag(){
    global $wpdb,$wp_cerber;
	$ret = array();

	$ret[]= 'Database name: '.DB_NAME;

    $pool = $wpdb->get_row('SHOW VARIABLES LIKE "innodb_buffer_pool_size"');
	$pool_size = round($pool->Value / 1048576);
	$inno = 'InnoDB buffer pool size: <b>'.$pool_size.' MB</b>';
	if ($pool_size < 16) $inno .= ' Your pool size is extremely small!';
	elseif ($pool_size < 64) $inno .= ' It seems that your pool size is too small.';
	$ret[]= $inno;

	$ret[]= cerber_table_info(CERBER_LOG_TABLE);
	$ret[]= cerber_table_info(CERBER_ACL_TABLE);
	$ret[]= cerber_table_info(CERBER_BLOCKS_TABLE);

	if ($wp_cerber->getRemoteIp() == '127.0.0.1') $ret[] = '<p style="color: #DF0000;">It seems that we are unable to get IP addresses.</p>';

	if ($errors = get_site_option( '_cerber_db_errors')){
		$err = '<p style="color: #DF0000;">Some minor DB errors were detected</p><textarea>'.print_r($errors,1).'</textarea>';
		update_site_option( '_cerber_db_errors', '');
	}
	else $err = '';

	return $err.implode('<br>',$ret);
}

function cerber_wp_diag(){
	global $wp_version, $wpdb;

	$ret = array();

	$ret[] = 'WordPress version: ' . $wp_version;
	$ret[] = 'WordPress options table: '.$wpdb->prefix.'options';
	$ret[] = '<br>Active plugins:<br>';
	$list = get_option('active_plugins');
	foreach($list as $plugin) {
		$data = get_plugin_data(WP_PLUGIN_DIR.'/'.$plugin);
		$ret[] = '- '.$data['Name'].' v. '.$data['Version'];
	}

	return implode("<br>",$ret);
}

/**
 * Creates mini report about given database table
 *
 * @param $table
 *
 * @return string
 */
function cerber_table_info( $table ) {
	global $wpdb;
	if (!cerber_is_table($table)){
		return '<p style="color: #DF0000;">ERROR. Database table ' . $table . ' not found! Click repair button below.</p>';
	}
	$cols = $wpdb->get_results( "SHOW FULL COLUMNS FROM " . $table );

	$columns    = '<table><tr><th style="width: 30%">Field</th><th style="width: 30%">Type</th><th style="width: 30%">Collation</th></tr>';
	foreach ( $cols as $column ) {
		$column    = obj_to_arr_deep( $column );
		$field     = array_shift( $column );
		$type      = array_shift( $column );
		$collation = array_shift( $column );
		$columns  .= '<tr><td><b>' . $field . '<b></td><td>' . $type . '</td><td>' . $collation . '</td></tr>';
	}
	$columns .= '</table>';

	$rows = absint( $wpdb->get_var( 'SELECT COUNT(*) FROM ' . $table ) );

	$sts = $wpdb->get_row( 'SHOW TABLE STATUS WHERE NAME = "' . $table .'"');
	$status = '<table>';
	foreach ( $sts as $key => $value ) {
		$status .= '<tr><td><b>' . $key . '<b></td><td>' . $value . '</td></tr>';
	}
	$status .= '</table>';

	return '<p style="font-size: 110%;">Table: <b>' . $table . '</b>, rows: ' . $rows . '</p><table class="diag-table"><tr><td class="diag-td">' . $columns . '</td><td class="diag-td">'. $status.'</td></tr></table>';
}


/*
function add_some_pointers() {
	?>
	<script type="text/javascript">
		jQuery(document).ready( function($) {
			var options = {'content':'<h3>Info</h3><p>Cerber will request WHOIS database for extra information when you click on IP.</p>','position':{'edge':'right','align':'center'}};
			if ( ! options ) return;
			options = $.extend( options, {
				close: function() {
					//to do
				}
			});

			//$("#ip_extra").click(function(){
			//	$(this).pointer( options ).pointer('open');
			//});

			$('#subscribe-me').pointer( options ).pointer('open');

		});
	</script>
	<?php
}
add_action('admin_enqueue_scripts', 'cerber_admin_enqueue');
function cerber_admin_enqueue($hook) {
	wp_enqueue_style( 'wp-pointer' );
	wp_enqueue_script( 'wp-pointer' );
}
*/


add_action( 'admin_enqueue_scripts', 'cerber_admin_assets', 9999 );
function cerber_admin_assets() {

	$assets_url = plugin_dir_url( __FILE__ ) . 'assets';

	if ( cerber_is_admin_page() ) {

		wp_register_style( 'crb_multi_css', $assets_url . '/multi/multi.css', null, CERBER_VER );
		wp_enqueue_style( 'crb_multi_css' );
		wp_enqueue_script( 'crb_multi_js', $assets_url . '/multi/multi.min.js', array(), CERBER_VER );

	}

	if ( ! defined( 'CERBER_BETA' ) ) {
		wp_enqueue_script( 'cerber_js', $assets_url . '/admin.js', array( 'jquery' ), CERBER_VER, true );
	}

	wp_register_style( 'cerber_css', $assets_url . '/admin.css', null, CERBER_VER );
	wp_enqueue_style( 'cerber_css' );

	// Select2
	//wp_register_style( 'select2css', $assets_url . '/select2/dist/css/select2.min.css' );
	//wp_enqueue_style( 'select2css' );
	//wp_enqueue_script( 'select2js', $assets_url . '/select2/dist/js/select2.min.js', null, null, true );

}

/*
 * JS & CSS for admin head
 *
 */
add_action('admin_head', 'cerber_admin_head' );
function cerber_admin_head(){
    global $assets_url, $crb_assets_url, $crb_ajax_loader;

    $assets_url = plugin_dir_url( CERBER_FILE ) . 'assets/';
	$crb_assets_url = plugin_dir_url( CERBER_FILE ) . 'assets/';
	$crb_ajax_loader = $crb_assets_url . 'ajax-loader.gif';

    if (defined('CERBER_BETA')) :
	    ?>
	    <style type="text/css" media="all">
		    <?php readfile(dirname(__FILE__).'/assets/admin.css'); ?>
	    </style>
	    <?php
    endif;

	?>
    <style type="text/css" media="all">
        /* Aliens messages */
        .update-nag,
        #setting-error-tgmpa {
            display: none;
        }

        /* Cerber's messages */
        div.wrap .update-nag,
        .crb-alarm {
            /*display: initial;*/
            display: inline-block;
        }
    </style>
	<?php
    //return;
    if (lab_lab()):
        ?>
        <style type="text/css" media="all">
            .actv5, .actv10, .actv11, .actv12, .actv16, .actv17, .actv18, .actv19, .actv41, .actv42, .actv53, .actv54,  .actv70 {
                padding: 0;
                border-left: none;
                background-color: initial;
            }

            /* New */
            .actv11 {
                font-weight: bold;
            }
            #crb-activity td {
                padding-top: 0.5em;
                padding-bottom: 0.5em;
            }
            #crb-activity td.acinfo div {
                padding: 0.1em 0 0.1em 0.7em;
            }
            .crb10, .crb11, .crb12, .crb16, .crb17, .crb18, .crb19, .crb41, .crb42, .crb51, .crb52, .crb53, .crb54, .crb70, .crb71 {
                /*border-left: 4px solid #FF5733;*/
                font-weight: bold;
                border-left: 6px solid #FF5733;
                padding-bottom: 2px;
            }
            .crb10, .crb11 {
                display: inline-block;
                background-color: #FF5733;
            }
            .crb5 {
                /*border-left: 4px solid #FF5733;*/
                border-left: 6px solid #51AE43;
                padding-bottom: 2px;
            }
            .act-url{
                font-weight: normal;
            }
        </style>
        <?php
    endif;
}
/*
 * JS & CSS for admin footer
 *
 */
add_action( 'admin_footer', 'cerber_admin_footer' );
function cerber_admin_footer() {
    global $crb_ajax_loader;
	//add_some_pointers();

	$ajax_nonce = wp_create_nonce( 'crb-ajax-admin' );
	if (lab_lab()) {
		$crb_lab_available = 'true';
    }
    else {
	    $crb_lab_available = 'false';
    }
	?>
    <script type="text/javascript">
        crb_ajax_nonce = '<?php echo $ajax_nonce; ?>';
        crb_ajax_loader = '<?php echo $crb_ajax_loader; ?>';
        crb_lab_available = <?php echo $crb_lab_available; ?>;
    </script>

    <?php

	if ( defined( 'CERBER_BETA' ) && cerber_is_admin_page( false ) ) :
		?>
        <script type="text/javascript">
			<?php readfile( dirname( __FILE__ ) . '/assets/admin.js' ); ?>
        </script>
		<?php
	endif;

}

add_filter( 'admin_footer_text','cerber_footer_text1');
function cerber_footer_text1($text){
	if (!cerber_is_admin_page(false)) return $text;
	return 'If you like how <strong>WP Cerber</strong> protects your website, please <a target="_blank" href="https://wordpress.org/support/plugin/wp-cerber/reviews/#new-post">leave it a &#9733; &#9733; &#9733; &#9733; &#9733; rating</a>. Thanks!';
}
add_filter( 'update_footer','cerber_footer_text2', 1000);
function cerber_footer_text2($text){
	if ( ! cerber_is_admin_page( false ) ) {
		return $text;
	}
	if ( lab_lab() ) {
		$pr = 'PRO';
		$support = '<a target="_blank" href="https://wpcerber.com/support/">Get Support</a>';
	}
	else {
		$pr = '';
		$support = '<a target="_blank" href="https://wordpress.org/support/plugin/wp-cerber">Support Forum</a>';
	}
	return 'WP Cerber Security '.$pr.' '.CERBER_VER.'. | ' . $support;
}

/*
 * Add per admin screen settings
 * @since 3.0
 *
 */
function cerber_screen_options() {
	if (!empty($_GET['tab'])) $tab = $_GET['tab'];
	else $tab = '';
	if ( !in_array( $tab, array( 'lockouts', 'activity' ) ) ) {
		return;
	}
	$args = array(
		//'label' => __( 'Number of items per page:' ),
		'default' => 50,
		'option' => 'cerber_screen_'.$tab,
	);
	add_screen_option( 'per_page', $args );
	// add_screen_option( 'layout_columns', array('max' => 2, 'default' => 2) );
}
/*
 * Allows to save options to the user meta
 * @since 3.0
 *
 */
add_filter('set-screen-option', 'cerber_save_screen_option', 10, 3);
function cerber_save_screen_option($status, $option, $value) {
	if (!empty($_GET['tab'])) $tab = $_GET['tab'];
	else $tab = 'activity';
	if ( 'cerber_screen_'.$tab == $option ) return $value;
	return $status;
}
/*
 * Retrieve option for current screen
 * @since 3.0
 *
 */
function cerber_get_per_page(){
	if (is_multisite()) return 50; // temporary workaround
	$screen = get_current_screen();
	$screen_option = $screen->get_option('per_page', 'option');
	if ($screen_option == 'cerber_screen_') $screen_option = 'cerber_screen_activity';
	$per_page = get_user_meta(get_current_user_id(), $screen_option, true);
	if ( empty ( $per_page) || $per_page < 1 ) {
		$per_page = $screen->get_option( 'per_page', 'default' );
	}
	return $per_page;
}

function cerber_rules_page(){

	$tab = cerber_get_tab( 'geo', array( 'geo' ) );

	?>
    <div class="wrap">

        <h2><?php _e( 'Security Rules', 'wp-cerber' ) ?></h2>

        <h2 class="nav-tab-wrapper cerber-tabs">
			<?php

			echo '<a href="' . cerber_admin_link('geo') . '" class="nav-tab ' . ( $tab == 'geo' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-admin-site"></span> ' . __('Countries') . '</a>';
			//echo '<a href="' . cerber_admin_link('diagnostic') . '" class="nav-tab ' . ( $tab == 'diagnostic' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-admin-tools"></span> ' . __('Diagnostic') . '</a>';
			//echo '<a href="' . cerber_admin_link('license') . '" class="nav-tab ' . ( $tab == 'license' ? 'nav-tab-active' : '') . '"><span class="dashicons dashicons-id-alt"></span> ' . __('License') . '</a>';

			echo lab_indicator();
			?>
        </h2>

		<?php

		cerber_show_aside( 'geo' );

		echo '<div class="crb-main">';

		switch ($tab){
			case 'geo':
				cerber_show_geo_rules();
				break;
			default: cerber_show_geo_rules();
		}

		echo '</div>';

		?>
    </div>
	<?php
}

function cerber_show_geo_rules(){
    global $wp_cerber;

/*
	echo "\n";
	foreach ( cerber_get_country_list() as $code => $i ) {
	    echo '
		a[data-value="'.$code.'"]{
			background: url("flags/'.strtolower($code).'.gif") no-repeat left;
        }'."\n";
	}*/

	$rules = cerber_geo_rule_set();

	$tablinks = '';
	$tabs = '';
	$first = true;
	$opt = $wp_cerber->getSettings();

	foreach ( $rules as $rule_id => $rule ) {

		$config = cerber_geo_rules($rule_id);

		$selector = cerber_country_form($config, $rule_id, $rule);

	    if ($first) {
	        $b_class = 'active';
		    $t_style = '';
        }
        else {
	        $b_class = '';
	        $t_style = 'style= "display: none;"';
        }

		if ( ! empty( $config['list'] ) ) {
			$num = count( $config['list'] );
			if ( $config['type'] == 'W' ) {
				$info = sprintf( _n( 'Permitted for one country', 'Permitted for %d countries', $num, 'wp-cerber' ), $num );
			}
			else {
				$info = sprintf( _n( 'Blocked for one country', 'Blocked for %d countries', $num, 'wp-cerber' ), $num );
			}
			if ($num == 1) {
				$info .= ' (' . current( $config['list'] ) . ')';
				//$info .= ' (' . cerber_get_flag_html($c) . $c . ')';
			}
		}
		else {
			$info = __( 'No rule', 'wp-cerber' );
		}

		$note = '';
		switch ( $rule_id ) {
			case 'geo_register':
				if ( !get_option( 'users_can_register' ) ) {
					$note = 'Registration is disabled in the General Settings';
				}
				break;
			case 'geo_restapi':
				if ( $opt['norest'] ) {
					$note = 'REST API is disabled in the Hardening settings of the plugin';
				}
				break;
			case 'geo_xmlrpc':
				if ( $opt['xmlrpc'] ) {
					$note = 'XML-RPC is disabled in the Hardening settings of the plugin';
				}
				break;
		}
		if ($note) $note = '<p><span class="dashicons-before dashicons-warning"></span> Warning: '.$note.'</p>';

	    //$tablinks .= '<button class="tablinks '.$b_class.'" data-rule-id="'.$rule_id.'">'.$rule['name'].'</button>';
		$tablinks .= '<div class="tablinks '.$b_class.'" data-rule-id="'.$rule_id.'">'.$rule['name'].'<br><span>'.$info.'</span></div>';

		$tabs .= '<div id="tab-' . $rule_id . '" class="vtabcontent" '.$t_style.'>'.$note.$selector.'</div>';

		$first = false;
	}

	echo '<form method="post" action="">';

	echo '<table class="vtable" style="width: 100%; border-collapse: collapse;"><tr><td style="width: 20%"><div class="vtabs">'.$tablinks.'</div></td><td>'.$tabs.'
    <p style="margin-left: 3em;"><input type="submit" class="button button-primary" value="Save all rules"></p>
    </td></tr></table>';

	//echo '<div class="vtabs">'.$buttons.'</div>';
	//echo '<div class="vtabs-content">'.$tabs.'</div>';

	echo wp_nonce_field('control','cerber_nonce');
	echo '<input type="hidden" name="crb_geo_rules" value="1"></form>';


	// Script for tabbed layout

	reset($rules);
    $first_id = 'countries-'.key($rules);

	?>

        <script type="text/javascript">
            //var select_element = document.getElementById( '<?php echo $first_id; ?>' );
            //multi( select_element );

            jQuery(document).ready(function ($) {
                $("#<?php echo $first_id; ?>").multi({'search_placeholder': '<?php _e( 'Start typing here to find a country', 'wp-cerber' ); ?>'});

                $('.tablinks').click(function () {

                    // Tabs...

                    var rule_id = $(this).data('rule-id');
                    $('.vtabcontent').hide();
                    $('#tab-' + rule_id).show();

                    $( ".tablinks" ).removeClass( "active" );
                    $( this ).addClass( "active" );

                    // Multiselect...

                    $('.multi-wrapper').remove();
                    document.querySelector('.crb-select-multi').removeAttribute('data-multijs');
                    $('.crb-select-multi').removeAttr('data-multijs');

                    $( '#countries-' + rule_id ).multi({'search_placeholder': 'Start typing here to find a country'});
                });
            });

        </script>

    <?php

}

/**
 * Generates GEO rule form
 *
 * @param array $config  saved rule configuration
 * @param string $rule_id
 * @param array $rule
 *
 * @return string   HTML code of form
 */
function cerber_country_form( $config = array(), $rule_id = '', $rule = array() ) {

	//$ret = '<form action="" method="post" id="form-' . $rule_id . '">';

    $ret = '';

    $ret .= '<select id="countries-' . $rule_id . '" name="crb-' . $rule_id . '-list[]" class="crb-select-multi" style="display: none;" multiple="multiple">';

    if (!empty($config['list'])){
        $selected = $config['list'];
    }
    else {
	    $selected = null;
    }

	foreach ( cerber_get_country_list() as $code => $country ) {
		if ( $selected && in_array( $code, $selected ) ) {
			$sel = 'selected';
		} else {
			$sel = '';
		}
		$ret .= '<option ' . $sel . ' value="' . $code . '">' . $country . '</option>';
	}

	if (!empty($config['type']) && $config['type'] == 'B'){
		$w = '';
		$b = 'checked="checked"';
    }
    else {
	    $w = 'checked="checked"';
	    $b = '';
    }

	if (!empty($rule['desc'])) {
		$desc = $rule['desc'];
    }
    else{
	    $desc = '<span style="text-transform: lowercase;">'.$rule['name'].'</span>';
    }


	$ret .= '
        </select>
        <p><i>' . __( 'Click on a country name to add it to the list of selected countries', 'wp-cerber' ) . '</i></p>
        
        <p style="margin-top: 2em;">
        <input type="radio" value="W" name="crb-'.$rule_id.'-type" id="geo-type-'.$rule_id.'-W" '.$w.'>
        <label for="geo-type-' . $rule_id . '-W">' . sprintf( __( 'Selected countries are allowed to %s other countries are not allowed', 'wp-cerber' ), $desc ) . '</label>
        <p>
        <input type="radio" value="B" name="crb-'.$rule_id.'-type" id="geo-type-'.$rule_id.'-B" '.$b.'>
        <label for="geo-type-' . $rule_id . '-B">' . sprintf( __( 'Selected countries are not allowed to %s other countries are allowed', 'wp-cerber' ), $desc ) . '</label>
        </p>';

	//$ret .= '<p style="text-align: right;"><input type="submit" class="button button-primary" value="Save the rule"></p>'.wp_nonce_field('cerber_dashboard','cerber_nonce');
	//$ret .= '</form>';

	return $ret;

}

function cerber_geo_rule_set(){
	$rules = array(
		'geo_submit'   => array( 'name' => __( 'Submit forms', 'wp-cerber' ) ),
		'geo_comment'  => array( 'name' => __( 'Post comments', 'wp-cerber' ) ),
		'geo_login'    => array( 'name' => __( 'Log in to the website', 'wp-cerber' ) ),
		'geo_register' => array( 'name' => __( 'Register on the website', 'wp-cerber' ) ),
		'geo_xmlrpc'   => array( 'name' => __( 'Use XML-RPC', 'wp-cerber' ) ),
		'geo_restapi'  => array( 'name' => __( 'Use REST API', 'wp-cerber' ) ),
	);

	return $rules;
}

function crb_save_geo_rules(){
    global $cerber_country_names, $wp_cerber;

    if (!lab_lab()) return;

    $geo = array();
	$check = array_keys($cerber_country_names);

	// Preserve admin country to be blocked
	$admin_country = lab_get_country($wp_cerber->getRemoteIp(), false);

	foreach ( cerber_geo_rule_set() as $rule_id => $rule ) {
		if ( ! empty( $_POST[ 'crb-' . $rule_id . '-list' ] ) && ! empty( $_POST[ 'crb-' . $rule_id . '-type' ] ) ) {
			$list = array_intersect( $_POST[ 'crb-' . $rule_id . '-list' ], $check );

			if ( $_POST[ 'crb-' . $rule_id . '-type' ] == 'B' ) {
				$type = 'B';
				if ( ( $key = array_search( $admin_country, $list ) ) !== false ) {
					unset( $list[ $key ] );
				}
			}
            else{
	            $type = 'W';
	            if ( ( $key = array_search( $admin_country, $list ) ) === false ) {
		            array_push( $list, $admin_country );
	            }
            }

			$geo[$rule_id]['list'] = $list;
			$geo[$rule_id]['type'] = $type;
		}
	}

	if ( update_site_option( 'geo_rule_set', $geo ) ) {
		cerber_admin_message( __( 'Security rules have been updated', 'wp-cerber' ) );
	}
}

function crb_country_html($code = null, $ip = null){
	global $assets_url;

	if (!lab_lab()){
	    return '';
    }

	if ( ! $code ) {
		if ( $ip ) {
			$code = lab_get_country( $ip );
		} else {
			return '';
		}
	}

	if ( $code ) {
		//$country = cerber_get_flag_html( $code ) . '<a href="'.$base_url.'&filter_country='.$code.'">'.cerber_country_name( $code ).'</a>';
		$ret = cerber_get_flag_html( $code ) . cerber_country_name( $code );
	}
	else {
		$ip_id = cerber_get_id_ip($ip);
		$ret = '<img data-ip-id="' . $ip_id . '" class="crb-no-country" src="' . $assets_url . 'ajax-loader.gif" />' . "\n";
	}

	return $ret;
}
