<?php
/*
Plugin Name: Lean Lunch additional registration fields
Plugin URI: http://www.onstate.co.uk
Description: Ask user which company they belong to
Author: Onstate
Version: 0.2
Author URI: http://www.onstate.co.uk
*/

/**
 * Add custom field to registration form
 */

function show_company_field()
{
	global $wpdb;
	$groups = array();

	if ( in_array( 'groups/groups.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		$groups_table = _groups_get_tablename( 'group' );
		$group_capability_table = _groups_get_tablename( 'group_capability' );
		$capability_table = _groups_get_tablename( 'capability' );

		$getRegistrationGroupsSQL = "SELECT $groups_table.* FROM $capability_table
			INNER JOIN $group_capability_table ON ($group_capability_table.`capability_id` = $capability_table.`capability_id`) 
			INNER JOIN $groups_table ON ($groups_table.`group_id` = $group_capability_table.`group_id`) 
			WHERE capability = 'can_be_registered'
			ORDER BY `name` ASC";
		$groups = $wpdb->get_results( $getRegistrationGroupsSQL );
	}
?>
<?php if(count($groups)): ?>
	<div class="ll-company-perks">
	<h4 class="nomarg">Get your employee perks</h4>
	<p>If your company’s registered with us, make sure you tell us below to get all the benefits.</p>
	<p>
	<label>Please select your company<br/>
	<select name="companyscheme">
		<option value="">Please choose</option>
		<?php foreach($groups AS $group){
			?><option value="<?php echo $group->group_id; ?>"><?php echo $group->name; ?></option>
			<?php
			}
		?>
	</select>
	</label>
	</p>
	</div>
<?php endif; ?>
<?php
}

function register_extra_fields ( $user_id, $password = "", $meta = array() )
{
	//update_user_meta( $user_id, 'twitter', $_POST['twitter'] );
	$result = Groups_User_Group::create( array( 'user_id' => $user_id, 'group_id' => $_POST['companyscheme'] ) );

}

add_action('woocommerce_register_form','show_company_field');
add_action('user_register', 'register_extra_fields');
?>
