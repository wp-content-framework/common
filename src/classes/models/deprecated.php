<?php
/**
 * WP_Framework_Common Classes Models Deprecated
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Classes\Models;

use Exception;
use WP_Framework;
use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Interfaces\Singleton;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Deprecated
 * @package WP_Framework_Common\Classes\Models
 */
class Deprecated implements Singleton {

	use \WP_Framework_Core\Traits\Singleton, Package;

	/**
	 * @param string $class
	 * @param \WP_Framework_Core\Traits\Singleton $instance
	 * @param string $name
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function call( $class, $instance, $name, array $args ) {
		$class = trim( $class, '\\' );
		$map   = $this->app->get_config( 'deprecated' );
		if ( ! empty( $map ) && is_array( $map ) ) {
			$_class = $this->find_class( $class, $map );

			if ( $_class && $class !== $_class && class_exists( $_class ) && is_subclass_of( $_class, '\WP_Framework_Core\Interfaces\Singleton' ) ) {
				/** @var Singleton $_class */
				$_instance = $_class::get_instance( $this->app );
				array_unshift( $args, $instance );

				return call_user_func_array( [ $_instance, $name ], $args );
			}

			foreach ( class_uses( $class ) as $trait ) {
				$_class = $this->app->array->search( $map, $trait, '\\' . $trait, null );
				if ( $_class && class_exists( $_class ) && is_subclass_of( $_class, '\WP_Framework_Core\Interfaces\Singleton' ) ) {
					/** @var Singleton $_class */
					$_instance = $_class::get_instance( $this->app );
					array_unshift( $args, $instance );

					return call_user_func_array( [ $_instance, $name ], $args );
				}
			}
		}

		$this->not_found( $class, $name );

		return null;
	}

	/**
	 * @param string $class
	 * @param array $map
	 *
	 * @return mixed
	 */
	private function find_class( $class, array $map ) {
		$_class = $this->app->array->search( $map, $class, '\\' . $class, null );
		if ( ! $_class ) {
			foreach ( $map as $k => $v ) {
				if ( class_exists( $k ) && is_subclass_of( $class, $k ) ) {
					$_class = $v;
					break;
				}
			}
		}

		return $_class;
	}

	/**
	 * @param string $class
	 * @param string $name
	 */
	private function not_found( $class, $name ) {
		$messages = [
			'致命的なバグが発生しました。',
			'お手数ですがエラーを報告いただけると助かります。',
		];
		try {
			$github_repo = $this->app->get_config( 'config', 'github_repo' );
			if ( $github_repo ) {
				$messages[] = '<a href="' . esc_url( 'https://github.com/' . $github_repo . '/issues' ) . '" target="_blank">GitHub</a>';
			}
		} catch ( Exception $e ) {
			// @codingStandardsIgnoreStart
			error_log( $e->getMessage() );
			// @codingStandardsIgnoreEnd
		}
		$messages[] = sprintf( '<pre>you cannot access %s->%s</pre>', $class, $name );
		// @codingStandardsIgnoreStart
		$messages[] = '<pre>' . print_r( $this->app->utility->get_debug_backtrace(), true ) . '</pre>';
		WP_Framework::kill( $messages, __FILE__, __LINE__ );
		// @codingStandardsIgnoreEnd
	}

	/**
	 * @param string $name
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function __call( $name, array $args ) {
		WP_Framework::kill( sprintf( 'you cannot access %s', esc_html( $name ) ), __FILE__, __LINE__ );

		return null;
	}
}
