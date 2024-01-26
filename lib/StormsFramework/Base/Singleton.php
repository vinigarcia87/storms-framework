<?php

namespace StormsFramework\Base;

trait Singleton
{
	/**
	 * To hold an instance of the class
	 * @var $instance
	 */
	private static $instance;

	public static function get_instance() {
		static $instance = [];

		$called_class = get_called_class();

		if( ! isset( $instance[ $called_class ] ) ) {
			$instance[ $called_class ] = new $called_class();
		}

		return $instance[ $called_class ];
	}
}
