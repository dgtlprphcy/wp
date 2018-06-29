<?php
/**
 * @view Referrals Page
 * @var $table AutomateWoo\Referrals\Referrals_List_Table
 */

if ( ! defined( 'ABSPATH' ) ) exit;

?>

<div class="wrap automatewoo-page automatewoo-page--referrals">

	<h1><?php echo get_admin_page_title() ?></h1>

	<?php
	AutomateWoo\Referrals\Admin_Referrals_Controller::output_messages();

	$table->prepare_items();
	$table->display_section_nav();
	$table->display();

	?>
</div>
