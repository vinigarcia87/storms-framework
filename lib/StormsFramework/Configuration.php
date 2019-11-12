<?php
/**
 * Storms Framework (http://storms.com.br/)
 *
 * @author    Vinicius Garcia | vinicius.garcia@storms.com.br
 * @copyright (c) Copyright 2012-2019, Storms Websolutions
 * @license   GPLv2 - GNU General Public License v2 or later (http://www.gnu.org/licenses/gpl-2.0.html)
 * @package   Storms
 * @version   4.0.0
 *
 * StormsFramework\Configuration class
 * Set framework's definitions and configurations
 */

namespace StormsFramework;

use StormsFramework\Base;

class Configuration extends Base\Manager
{
	public function __construct() {
		parent::__construct( __CLASS__, STORMS_FRAMEWORK_VERSION, $this );
	}

	public static function set_defines() {

		// Define BasePath for Storms Framework
		if ( !defined( 'STORMS_FRAMEWORK_PATH' ) )
			define( 'STORMS_FRAMEWORK_PATH', dirname( dirname( __FILE__ ) ) );

		// Define the Storms Framework Version
		if ( !defined( 'STORMS_FRAMEWORK_VERSION' ) )
			define( 'STORMS_FRAMEWORK_VERSION', '4.0.0' );

        // Define the System Version
        if ( !defined( 'STORMS_SYSTEM_VERSION' ) )
            define( 'STORMS_SYSTEM_VERSION', 'YYYY.MM.DD' );

        // Define the System Commit ID
        if ( !defined( 'STORMS_SYSTEM_COMMIT' ) )
            define( 'STORMS_SYSTEM_COMMIT', '' );
	}
}
