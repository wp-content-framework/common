<?php
/**
 * WP_Framework_Common Classes Models Config
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
 * Class Config
 * @package WP_Framework_Common\Classes\Models
 */
class Config implements \WP_Framework_Core\Interfaces\Singleton {

	use Singleton, Package;

	/**
	 * @var mixed[] $configs
	 */
	private $configs = [];

	/**
	 * @param string $name
	 *
	 * @return array
	 */
	public function load( $name ) {
		if ( ! isset( $this->configs[ $name ] ) ) {
			$plugin_config = $this->load_config_file( $this->app->define->plugin_configs_dir, $name );
			$configs       = [];
			if ( 'config' === $name ) {
				$required_php_version       = isset( $plugin_config['required_php_version'] ) ? $plugin_config['required_php_version'] : WP_FRAMEWORK_REQUIRED_PHP_VERSION;
				$required_wordpress_version = isset( $plugin_config['required_wordpress_version'] ) ? $plugin_config['required_wordpress_version'] : WP_FRAMEWORK_REQUIRED_WP_VERSION;
				foreach ( $this->app->get_packages() as $package ) {
					$_config = $package->get_config( $name );
					if ( isset( $_config['required_php_version'] ) && version_compare( $required_php_version, $_config['required_php_version'], '<' ) ) {
						$required_php_version = $_config['required_php_version'];
					}
					if ( isset( $_config['required_wordpress_version'] ) && version_compare( $required_wordpress_version, $_config['required_wordpress_version'], '<' ) ) {
						$required_wordpress_version = $_config['required_wordpress_version'];
					}
					$configs = array_replace_recursive( $configs, $_config );
				}
				$configs                               = array_replace_recursive( $configs, $plugin_config );
				$configs['required_php_version']       = $required_php_version;
				$configs['required_wordpress_version'] = $required_wordpress_version;
			} else {
				foreach ( $this->app->get_packages() as $package ) {
					$configs = array_replace_recursive( $configs, $package->get_config( $name ) );
				}
				$configs = array_replace_recursive( $configs, $plugin_config );
			}
			$this->configs[ $name ] = $configs;
		}

		return $this->configs[ $name ];
	}

	/**
	 * @param string $name
	 * @param string|null $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function get( $name, $key = null, $default = null ) {
		return isset( $key ) ? $this->app->array->get( $this->load( $name ), $key, $default ) : $this->load( $name );
	}

	/**
	 * @param string $name
	 * @param string $key
	 * @param mixed $value
	 */
	public function set( $name, $key, $value ) {
		$this->load( $name );
		$this->configs[ $name ] = $this->app->array->set( $this->configs[ $name ], $key, $value );
	}

	/**
	 * @param string $dir
	 * @param string $name
	 *
	 * @return array
	 */
	private function load_config_file( $dir, $name ) {
		$path = rtrim( $dir, DS ) . DS . $name . '.php';
		if ( ! file_exists( $path ) ) {
			return [];
		}
		/** @noinspection PhpIncludeInspection */
		$config = include $path;
		if ( ! is_array( $config ) ) {
			$config = [];
		}

		return $config;
	}
}
