<?php
/**
 * WP_Framework_Common Classes Models Input
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Classes\Models;

use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Singleton;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Input
 * @package WP_Framework_Common\Classes\Models
 * @SuppressWarnings(PHPMD.Superglobals)
 */
class Input implements \WP_Framework_Core\Interfaces\Singleton {

	use Singleton, Package;

	/**
	 * @var array $input
	 */
	private $input = null;

	/**
	 * @var string $php_input
	 */
	private $php_input = null;

	/**
	 * @return bool
	 */
	protected static function is_shared_class() {
		return true;
	}

	/**
	 * @return array
	 */
	public function all() {
		if ( ! isset( $this->input ) ) {
			$this->input = array_merge( $_GET, $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.NonceVerification.Recommended
		}

		return $this->input;
	}

	/**
	 * @param string|int|array|null $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_GET : $this->app->array->get( $_GET, $key, $default ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_get( $key, $value ) {
		$_GET[ $key ] = $this->add_magic_quotes( $value );
	}

	/**
	 * @param string $key
	 */
	public function delete_get( $key ) {
		unset( $_GET[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * @param string|int|array|null $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function post( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_POST : $this->app->array->get( $_POST, $key, $default ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_post( $key, $value ) {
		$_POST[ $key ] = $this->add_magic_quotes( $value );
	}

	/**
	 * @param string $key
	 */
	public function delete_post( $key ) {
		unset( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
	}

	/**
	 * @param string|int|array|null $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function request( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_REQUEST : $this->app->array->get( $_REQUEST, $key, $default ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_request( $key, $value ) {
		$_REQUEST[ $key ] = $this->add_magic_quotes( $value );
	}

	/**
	 * @param string $key
	 */
	public function delete_request( $key ) {
		unset( $_REQUEST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	}

	/**
	 * @param string|int|array|null $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function file( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_FILES : $this->app->array->get( $_FILES, $key, $default );
	}

	/**
	 * @param string|int|array|null $key
	 *
	 * @return bool
	 */
	public function is_uploaded_file( $key ) {
		return $this->app->array->exists( $_FILES, $key ) && is_uploaded_file( $this->file( $key ) );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_file( $key, $value ) {
		$_FILES[ $key ] = $value;
	}

	/**
	 * @param string $key
	 */
	public function delete_file( $key ) {
		unset( $_FILES[ $key ] );
	}

	/**
	 * @param string|int|array|null $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function cookie( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_COOKIE : $this->app->array->get( $_COOKIE, $key, $default );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_cookie( $key, $value ) {
		$_COOKIE[ $key ] = $value;
	}

	/**
	 * @param string $key
	 */
	public function delete_cookie( $key ) {
		unset( $_COOKIE[ $key ] );
	}

	/**
	 * @param string|int|array|null $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function server( $key = null, $default = null ) {
		return func_num_args() === 0 ? $_SERVER : $this->app->array->get( $_SERVER, strtoupper( $key ), $default );
	}

	/**
	 * @param string $key
	 * @param mixed $value
	 */
	public function set_server( $key, $value ) {
		$_SERVER[ $key ] = $value;
	}

	/**
	 * @param string $key
	 */
	public function delete_server( $key ) {
		unset( $_SERVER[ $key ] );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 * @SuppressWarnings(PHPMD.ShortMethodName)
	 */
	public function ip( $default = '0.0.0.0' ) {
		return $this->server( 'HTTP_X_FORWARDED_FOR', $this->server( 'REMOTE_ADDR', $default ) );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function user_agent( $default = '' ) {
		return $this->server( 'HTTP_USER_AGENT', $default );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function method( $default = 'GET' ) {
		return strtoupper( $this->server( 'REQUEST_METHOD', $this->request( '_method', $default ) ) );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function referer( $default = '' ) {
		return $this->server( 'HTTP_REFERER', $default );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function referer_host( $default = '' ) {
		$referer = $this->referer();
		if ( empty( $referer ) ) {
			return $default;
		}

		return $this->app->array->get( wp_parse_url( $referer ), 'host', $default );
	}

	/**
	 * @param string $default
	 *
	 * @return string
	 */
	public function host( $default = '' ) {
		return $this->server( 'HTTP_HOST', $default );
	}

	/**
	 * @return bool
	 */
	public function is_post() {
		return ! in_array( $this->method(), [
			'GET',
			'HEAD',
			'TRACE',
			'OPTIONS',
		], true );
	}

	/**
	 * @return bool|string
	 */
	public function php_input() {
		if ( ! isset( $this->php_input ) ) {
			$this->php_input = file_get_contents( 'php://input' );
		}

		return $this->php_input;
	}

	/**
	 * @param array $args
	 *
	 * @return string
	 */
	public function get_current_url( array $args = [] ) {
		$url = $this->get_current_host() . $this->get_current_path();
		if ( ! empty( $args ) ) {
			$url = add_query_arg( $args, $url );
		}

		return $url;
	}

	/**
	 * @return string
	 */
	public function get_current_host() {
		return ( is_ssl() ? 'https://' : 'http://' ) . $this->server( 'HTTP_HOST' );
	}

	/**
	 * @return string
	 */
	public function get_current_path() {
		return $this->server( 'REQUEST_URI' );
	}

	/**
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	private function add_magic_quotes( $value ) {
		if ( is_array( $value ) ) {
			foreach ( $value as $key => $item ) {
				$value[ $key ] = $this->add_magic_quotes( $item );
			}
		} else {
			$value = addslashes( $value );
		}

		return $value;
	}
}
