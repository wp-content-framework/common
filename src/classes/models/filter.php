<?php
/**
 * WP_Framework_Common Classes Models Filter
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
use WP_Framework_Core\Traits\Hook;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Filter
 * @package WP_Framework_Common\Classes\Models
 */
class Filter implements Singleton, \WP_Framework_Core\Interfaces\Hook {

	use \WP_Framework_Core\Traits\Singleton, Hook, Package;

	/**
	 * @var array $target_app
	 */
	private $target_app = [];

	/**
	 * for debug
	 * @var array $elapsed
	 */
	private $elapsed = [];

	/**
	 * @var bool $is_running
	 */
	private $is_running = false;

	/**
	 * initialize
	 */
	protected function initialize() {
		foreach ( $this->apply_filters( 'filter', $this->app->config->load( 'filter' ) ) as $class => $tags ) {
			$this->register_class_filter( $class, $tags );
		}
	}

	/**
	 * @param string $class
	 * @param array $tags
	 */
	public function register_class_filter( $class, array $tags ) {
		if ( empty( $class ) || ! is_array( $tags ) ) {
			return;
		}
		foreach ( $tags as $tag => $methods ) {
			$this->register_filter( $class, $tag, $methods );
		}
	}

	/**
	 * @param string $class
	 * @param string $tag
	 * @param array $methods
	 */
	public function register_filter( $class, $tag, array $methods ) {
		$tag = $this->app->string->replace( $tag, [
			'prefix'    => $this->get_filter_prefix(),
			'framework' => $this->get_framework_filter_prefix(),
		] );
		if ( empty( $class ) || empty( $tag ) || ! is_array( $methods ) ) {
			return;
		}
		foreach ( $methods as $key => $value ) {
			list( $method, $params ) = $this->parse_method_params( $key, $value );
			if ( empty( $method ) || ! is_string( $method ) || ! is_array( $params ) ) {
				continue;
			}
			list( $priority, $accepted_args ) = $this->get_filter_params( $params );
			add_filter( $tag, function () use ( $tag, $class, $method ) {
				return $this->call_filter_callback( $tag, $class, $method, func_get_args() );
			}, $priority, $accepted_args );
		}
	}

	/**
	 * @param string $class
	 *
	 * @return false|WP_Framework|Singleton
	 */
	private function get_target_app( $class ) {
		if ( ! $this->app->is_uninstall() && ! $this->app->system->is_enough_version() ) {
			return false;
		}
		if ( ! isset( $this->target_app[ $class ] ) ) {
			$app = false;
			if ( strpos( $class, '->' ) !== false ) {
				$app = $this->get_target_app_by_arrow_access( $class );
			} else {
				if ( isset( $this->app->$class ) ) {
					$app = $this->app->$class;
				}
			}
			if ( false === $app ) {
				$app = $this->get_target_singleton( $class );
			}
			$this->target_app[ $class ] = $app;
		}

		return $this->target_app[ $class ];
	}

	/**
	 * @param $class
	 *
	 * @return bool|WP_Framework
	 */
	private function get_target_app_by_arrow_access( $class ) {
		$app      = $this->app;
		$exploded = explode( '->', $class );
		foreach ( $exploded as $property ) {
			if ( isset( $app->$property ) ) {
				$app = $app->$property;
			} else {
				return false;
			}
		}

		return $app;
	}

	/**
	 * @param string|Singleton $class
	 *
	 * @return bool|\WP_Framework_Core\Traits\Singleton
	 */
	private function get_target_singleton( $class ) {
		if ( class_exists( $class ) && is_subclass_of( $class, '\WP_Framework_Core\Interfaces\Singleton' ) ) {
			try {
				return $class::get_instance( $this->app );
			} catch ( Exception $e ) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
			}
		}

		return false;
	}

	/**
	 * @param mixed $key
	 * @param mixed $value
	 *
	 * @return array
	 */
	private function parse_method_params( $key, $value ) {
		if ( is_int( $key ) && is_string( $value ) ) {
			return [ $value, [] ];
		}
		if ( is_string( $key ) && is_int( $value ) ) {
			return [ $key, [ $value ] ];
		}

		return [ $key, $value ];
	}

	/**
	 * @param array $params
	 *
	 * @return array
	 */
	private function get_filter_params( array $params ) {
		$priority      = 10;
		$accepted_args = 100;
		if ( is_array( $params ) ) {
			if ( count( $params ) >= 1 ) {
				$priority = $params[0];
			}
			if ( count( $params ) >= 2 ) {
				$accepted_args = $params[1];
			}
		}

		return [ $priority, $accepted_args ];
	}

	/**
	 * @param string $tag
	 * @param string $class
	 * @param string $method
	 * @param array $args
	 *
	 * @return mixed
	 */
	private function call_filter_callback( $tag, $class, $method, array $args ) {
		return $this->run( $tag, $class, $method, function ( $args ) use ( $class, $method ) {
			$result = empty( $args ) ? null : reset( $args );
			$app    = $this->get_target_app( $class );
			if ( empty( $app ) ) {
				return $result;
			}

			if ( $app->is_filter_callable( $method ) ) {
				return $app->filter_callback( $method, $args );
			}

			return $result;
		}, $args );
	}

	/**
	 * @param string $tag
	 * @param string $class
	 * @param string $method
	 * @param callable $callback
	 * @param array $args
	 *
	 * @return mixed
	 */
	private function run( $tag, $class, $method, $callback, $args ) {
		if ( $this->is_running ) {
			$result          = $callback( $args );
			$this->elapsed[] = [
				'tag'     => $tag,
				'class'   => $class,
				'method'  => $method,
				'elapsed' => 0,
			];
		} else {
			$this->is_running = true;

			$start           = microtime( true ) * 1000;
			$result          = $callback( $args );
			$elapsed         = microtime( true ) * 1000 - $start;
			$this->elapsed[] = [
				'tag'     => $tag,
				'class'   => $class,
				'method'  => $method,
				'elapsed' => $elapsed,
			];

			$this->is_running = false;
		}

		return $result;
	}

	/**
	 * for debug
	 * @return float
	 */
	public function get_elapsed() {
		return $this->app->array->sum( $this->elapsed, function ( $item ) {
			return $item['elapsed'];
		} );
	}

	/**
	 * for debug
	 * @return array
	 */
	public function get_elapsed_details() {
		$elapsed = $this->get_elapsed();

		return $this->app->array->map( $this->elapsed, function ( $item ) use ( $elapsed ) {
			return sprintf( '%10.6fms (%5.2f%%) : [%s] %s->%s', $item['elapsed'], ( $item['elapsed'] / $elapsed ) * 100, $item['tag'], $item['class'], $item['method'] );
		} );
	}
}
