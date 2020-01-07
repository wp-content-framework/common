<?php
/**
 * WP_Framework_Common Classes Models Utility
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Classes\Models;

use Closure;
use Exception;
use WP_Framework;
use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Singleton;
use WP_Post;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Utility
 * @package WP_Framework_Common\Classes\Models
 */
class Utility implements \WP_Framework_Core\Interfaces\Singleton {

	use Singleton, Package;

	/**
	 * @var float $tick
	 */
	private $tick;

	/**
	 * @var array $active_plugins
	 */
	private $active_plugins = [];

	/**
	 * @var string $active_plugins_hash
	 */
	private $active_plugins_hash;

	/**
	 * @var string $framework_plugin_hash
	 */
	private $framework_plugin_hash;

	/**
	 * @return bool
	 */
	protected static function is_shared_class() {
		return true;
	}

	/**
	 * @param mixed $value
	 * @param array $args
	 *
	 * @return mixed
	 */
	public function value( $value, ...$args ) {
		return $value instanceof Closure ? $value( $this->app, ...$args ) : $value;
	}

	/**
	 * @return string
	 */
	public function uuid() {
		$pid  = getmypid();
		$node = $this->app->input->server( 'SERVER_ADDR', '0.0.0.0' );

		list( $time_mid, $time_low ) = explode( ' ', microtime() );

		return sprintf(
			'%08x%04x%04x%02x%02x%04x%08x',
			(int) $time_low,
			(int) substr( $time_mid, 2 ) & 0xffff,
			wp_rand( 0, 0xfff ) | 0x4000,
			wp_rand( 0, 0x3f ) | 0x80,
			wp_rand( 0, 0xff ),
			$pid & 0xffff,
			$node
		);
	}

	/**
	 * @param string $name
	 *
	 * @return bool
	 */
	public function defined( $name ) {
		return defined( $name ) && ! empty( constant( $name ) );
	}

	/**
	 * @param string $name
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public function definedv( $name, $default = null ) {
		if ( defined( $name ) ) {
			return constant( $name );
		}

		return $this->value( $default );
	}

	/**
	 * @param string $data
	 * @param string $key
	 *
	 * @return false|string
	 */
	public function create_hash( $data, $key ) {
		return hash_hmac( function_exists( 'hash' ) ? 'sha256' : 'sha1', $data, $key );
	}

	/**
	 * @return bool
	 */
	public function doing_ajax() {
		if ( $this->defined( 'REST_REQUEST' ) ) {
			return true;
		}

		if ( function_exists( 'wp_doing_ajax' ) ) {
			return wp_doing_ajax();
		}

		return $this->defined( 'DOING_AJAX' );
	}

	/**
	 * @return bool
	 */
	public function doing_cron() {
		return $this->defined( 'DOING_CRON' );
	}

	/**
	 * @return bool
	 */
	public function is_autosave() {
		return $this->defined( 'DOING_AUTOSAVE' );
	}

	/**
	 * @param bool $except_ajax
	 *
	 * @return bool
	 */
	public function is_admin( $except_ajax = true ) {
		return is_admin() && ( ! $except_ajax || ! $this->doing_ajax() );
	}

	/**
	 * @return bool
	 */
	public function is_front() {
		return $this->defined( 'WP_USE_THEMES' );
	}

	/**
	 * @return bool
	 */
	public function was_admin() {
		return $this->is_admin_url( $this->app->input->referer() );
	}

	/**
	 * @param string $url
	 *
	 * @return bool
	 */
	public function is_admin_url( $url ) {
		return $this->app->string->starts_with( $url, admin_url() );
	}

	/**
	 * @return bool
	 */
	public function is_changed_host() {
		return $this->app->input->host() !== $this->app->input->referer_host();
	}

	/**
	 * @return bool
	 */
	public function is_changed_admin() {
		return $this->is_admin() !== $this->was_admin();
	}

	/**
	 * @param array $unset
	 *
	 * @return array
	 */
	public function get_debug_backtrace( array $unset = [] ) {
		// @codingStandardsIgnoreStart
		$backtrace = debug_backtrace();
		// @codingStandardsIgnoreEnd

		foreach ( $backtrace as $key1 => $value ) {
			// 大量のデータになりがちな object と args を削除や編集
			unset( $backtrace[ $key1 ]['object'] );
			if ( ! empty( $backtrace[ $key1 ]['args'] ) ) {
				$backtrace[ $key1 ]['args'] = $this->parse_backtrace_args( $backtrace[ $key1 ]['args'] );
			} else {
				unset( $backtrace[ $key1 ]['args'] );
			}
			if ( ! empty( $unset ) ) {
				foreach ( array_keys( $value ) as $key2 ) {
					if ( in_array( $key2, $unset, true ) ) {
						unset( $backtrace[ $key1 ][ $key2 ] );
					}
				}
			}
		}

		return $backtrace;
	}

	/**
	 * @param array $args
	 *
	 * @return array
	 */
	private function parse_backtrace_args( array $args ) {
		return $this->app->array->map( $args, function ( $d ) {
			$type = gettype( $d );
			if ( 'array' === $type ) {
				return $this->parse_backtrace_args( $d );
			} elseif ( 'object' === $type ) {
				$type = get_class( $d );
			} elseif ( 'resource' !== $type && 'resource (closed)' !== $type && 'NULL' !== $type && 'unknown type' !== $type ) {
				if ( 'boolean' === $type ) {
					// @codingStandardsIgnoreStart
					$d = var_export( $d, true );
					// @codingStandardsIgnoreEnd
				}
				$type .= ': ' . $d;
			}

			return $type;
		} );
	}

	/**
	 * @param string $type
	 * @param bool $detect_text
	 *
	 * @return string
	 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
	 */
	public function parse_db_type( $type, $detect_text = false ) {
		switch ( true ) {
			case stristr( $type, 'TINYINT(1)' ) !== false:
			case stristr( $type, 'BIT' ) !== false:
			case stristr( $type, 'BOOLEAN' ) !== false:
				return 'bool';
			case stristr( $type, 'INT' ) !== false:
				return 'int';
			case stristr( $type, 'DECIMAL' ) !== false:
			case stristr( $type, 'DOUBLE' ) !== false:
			case stristr( $type, 'REAL' ) !== false:
				return 'number';
			case stristr( $type, 'FLOAT' ) !== false:
				return 'float';
			case $detect_text && stristr( $type, 'TEXT' ) !== false:
				return 'text';
		}

		return 'string';
	}

	/**
	 * @param array|string $tags
	 *
	 * @return bool
	 */
	public function has_shortcode( $tags ) {
		if ( empty( $tags ) ) {
			return false;
		}

		$post = get_post();
		if ( empty( $post ) || ! $post instanceof WP_Post ) {
			return false;
		}

		if ( ! is_array( $tags ) ) {
			$tags = [ $tags ];
		}

		$content = $post->post_content;
		foreach ( $tags as $tag ) {
			if ( has_shortcode( $content, $tag ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param WP_Framework $app
	 * @param string $name
	 * @param callable $func
	 * @param int $timeout
	 *
	 * @return bool
	 */
	public function lock_process( WP_Framework $app, $name, callable $func, $timeout = 60 ) {
		$name         = $name . '__LOCK_PROCESS__';
		$timeout_name = $name . 'TIMEOUT__';
		$option       = $app->option;
		$option->flush();
		$check = $option->get( $name );
		if ( ! empty( $check ) ) {
			$expired = $option->get( $timeout_name, 0 ) < time();
			if ( ! $expired ) {
				return false;
			}
		}

		$rand = md5( uniqid() );
		$option->set( $name, $rand );
		$option->flush();
		if ( $option->get( $name ) !== $rand ) {
			return false;
		}

		$option->set( $timeout_name, time() + $timeout );
		try {
			$func();
		} catch ( Exception $e ) {
			$app->log( $e );
		} finally {
			$option->delete( $name );
			$option->delete( $timeout_name );
		}

		return true;
	}

	/**
	 * @param bool $combine
	 *
	 * @return array
	 */
	public function get_active_plugins( $combine = true ) {
		$combine = $combine ? 1 : 0;
		if ( ! isset( $this->active_plugins[ $combine ] ) ) {
			$option = get_option( 'active_plugins', [] );
			if ( is_multisite() ) {
				$option = array_merge( $option, array_keys( get_site_option( 'active_sitewide_plugins' ) ) );
				$option = array_unique( $option );
			}
			$this->active_plugins[ $combine ] = $combine ? $this->app->array->combine( $option, null ) : array_values( $option );
		}

		return $this->active_plugins[ $combine ];
	}

	/**
	 * @return string
	 */
	public function get_active_plugins_hash() {
		if ( ! isset( $this->active_plugins_hash ) ) {
			$this->active_plugins_hash = sha1( $this->json_encode( $this->get_active_plugins( false ) ) );
		}

		return $this->active_plugins_hash;
	}

	/**
	 * @return array
	 */
	private function get_framework_plugins() {
		return $this->app->array->map( $this->app->get_instances(), function ( $instance ) {
			/** @var WP_Framework $instance */
			return $instance->plugin_name . '/' . $instance->get_plugin_version();
		} );
	}

	/**
	 * @return string
	 */
	public function get_framework_plugins_hash() {
		if ( ! isset( $this->framework_plugin_hash ) ) {
			$this->framework_plugin_hash = sha1( $this->json_encode( $this->get_framework_plugins() ) );
		}

		return $this->framework_plugin_hash;
	}

	/**
	 * @param string $plugin
	 *
	 * @return bool
	 */
	public function is_plugin_active( $plugin ) {
		return in_array( $plugin, $this->get_active_plugins( false ), true );
	}

	/**
	 * for debug
	 */
	public function timer_start() {
		$this->tick = microtime( true ) * 1000;
	}

	/**
	 * for debug
	 *
	 * @param string $format
	 */
	public function timer_tick( $format = '%12.8f' ) {
		if ( ! isset( $this->tick ) ) {
			$this->timer_start();

			return;
		}
		$now     = microtime( true ) * 1000;
		$elapsed = $now - $this->tick;
		// @codingStandardsIgnoreStart
		error_log( sprintf( $format, $elapsed ) );
		// @codingStandardsIgnoreEnd
		$this->tick = $now;
	}

	/**
	 * @param string $limit
	 *
	 * @return bool|int|string
	 */
	public function raise_memory_limit( $limit ) {
		if ( $this->compare_wp_version( '4.6.0', '>=' ) ) {
			$context = WP_FRAMEWORK_VENDOR_NAME;
			$filter  = function () use ( $limit, $context, &$filter ) {
				remove_filter( "{$context}_memory_limit", $filter );

				return $limit;
			};
			add_filter( "{$context}_memory_limit", $filter );

			return wp_raise_memory_limit( $context );
		}

		// @codingStandardsIgnoreStart
		ini_set( 'memory_limit', $limit );

		// @codingStandardsIgnoreEnd

		return $limit;
	}

	/**
	 * @param $data
	 * @param int $options
	 * @param int $depth
	 *
	 * @return string|false
	 */
	public function json_encode( $data, $options = 0, $depth = 512 ) {
		if ( function_exists( 'wp_json_encode' ) ) {
			return @wp_json_encode( $data, $options, $depth ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
		}

		return @json_encode( $data, $options, $depth ); // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged,WordPress.WP.AlternativeFunctions.json_encode_json_encode
	}
}
