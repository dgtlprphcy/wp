<?php

namespace AutomateWoo;

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * @class Action_Memberships_Abstract
 * @since 2.8
 */
abstract class Action_Memberships_Abstract extends Action {

	function init() {
		$this->group = __( 'Memberships', 'automatewoo' );
	}

}
