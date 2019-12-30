<?php
/**
 * WP_Framework_Common Classes Models Setting
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Classes\Models;

use WP_Framework_Common\Traits\Package;
use WP_Framework_Core\Traits\Hook;
use WP_Framework_Core\Traits\Singleton;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Setting
 * @package WP_Framework_Common\Classes\Models
 */
class Setting implements \WP_Framework_Core\Interfaces\Singleton, \WP_Framework_Core\Interfaces\Hook {

	use Singleton, Hook, Package;

	/**
	 * @var array $groups
	 */
	private $groups = [];

	/**
	 * @var array $group_priority
	 */
	private $group_priority = [];

	/**
	 * @var array $settings
	 */
	private $settings = [];

	/**
	 * @var array $setting_priority
	 */
	private $setting_priority = [];

	/**
	 * initialize
	 */
	protected function initialize() {
		$data = $this->apply_filters( 'initialize_setting', $this->app->config->load( 'setting' ) );
		ksort( $data );
		foreach ( $data as $group_priority => $groups ) {
			foreach ( $groups as $group => $setting_set ) {
				ksort( $setting_set );

				if ( isset( $this->group_priority[ $group ] ) ) {
					$_group_priority                           = $this->group_priority[ $group ];
					$this->groups[ $group_priority ][ $group ] = $this->groups[ $_group_priority ][ $group ];
					unset( $this->groups[ $_group_priority ][ $group ] );
					if ( empty( $this->groups[ $_group_priority ] ) ) {
						unset( $this->groups[ $_group_priority ] );
					}
				} else {
					$this->groups[ $group_priority ][ $group ] = [];
				}
				$this->group_priority[ $group ] = $group_priority;
				foreach ( $setting_set as $setting_priority => $settings ) {
					$this->groups[ $group_priority ][ $group ] = array_merge( $this->groups[ $group_priority ][ $group ], array_keys( $settings ) );
					foreach ( $settings as $setting => $detail ) {
						$this->settings[ $setting_priority ][ $setting ] = $detail;
						$this->setting_priority[ $setting ]              = $setting_priority;
					}
				}
			}
		}
		asort( $this->group_priority );
	}

	/**
	 * @return array
	 */
	public function get_groups() {
		return $this->apply_filters( 'get_groups', array_keys( $this->group_priority ) );
	}

	/**
	 * @param string $group
	 *
	 * @return array
	 */
	public function get_settings( $group ) {
		if ( ! isset( $this->group_priority[ $group ], $this->groups[ $this->group_priority[ $group ] ] ) ) {
			return $this->apply_filters( 'get_settings', [], $group );
		}

		return $this->apply_filters( 'get_settings', $this->groups[ $this->group_priority[ $group ] ][ $group ], $group );
	}

	/**
	 * @param string $setting
	 * @param bool $detail
	 *
	 * @return array|false
	 */
	public function get_setting( $setting, $detail = false ) {
		if ( ! $this->is_setting( $setting ) ) {
			return $this->apply_filters( 'get_setting', false, $setting, $detail );
		}

		$data = $this->apply_filters( 'get_setting', $this->settings[ $this->setting_priority[ $setting ] ][ $setting ], $setting );
		if ( $detail ) {
			$data = $this->get_detail_setting( $setting, $data );
		}

		return $data;
	}

	/**
	 * @param string $setting
	 *
	 * @return bool
	 */
	public function remove_setting( $setting ) {
		if ( ! $this->is_setting( $setting ) ) {
			return true;
		}

		foreach ( $this->groups as $group_priority => $groups ) {
			foreach ( $groups as $group => $settings ) {
				$key = array_search( $setting, $settings, true );
				if ( false !== $key ) {
					unset( $this->groups[ $group_priority ][ $group ][ $key ] );
					if ( empty( $this->groups[ $group_priority ][ $group ] ) ) {
						unset( $this->groups[ $group_priority ][ $group ] );
						unset( $this->group_priority[ $group ] );
						if ( empty( $this->groups[ $group_priority ] ) ) {
							unset( $this->groups[ $group_priority ] );
						}
					}
					break;
				}
			}
		}

		return true;
	}

	/**
	 * @param string $setting
	 *
	 * @return bool
	 */
	public function is_setting_removed( $setting ) {
		if ( ! $this->is_setting( $setting ) ) {
			return true;
		}

		foreach ( $this->groups as $groups ) {
			foreach ( $groups as $settings ) {
				if ( false !== array_search( $setting, $settings, true ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param string $setting
	 * @param string $key
	 * @param mixed $value
	 *
	 * @return bool
	 */
	public function edit_setting( $setting, $key, $value ) {
		if ( ! $this->is_setting( $setting ) ) {
			return true;
		}
		$this->settings[ $this->setting_priority[ $setting ] ][ $setting ][ $key ] = $value;

		return true;
	}

	/**
	 * @param string $setting
	 * @param array $data
	 *
	 * @return array
	 */
	private function get_detail_setting( $setting, array $data ) {
		$data['key'] = $setting;
		$type        = $this->app->array->get( $data, 'type', '' );
		$default     = $this->app->array->get( $data, 'default', '' );
		$this->call_if_closure_with_result( $default, $default, $this->app );
		$default = $this->get_expression( $default, $type );
		if ( ! empty( $data['translate'] ) ) {
			$default = $this->translate( $default );
		}
		$data['info'] = [];
		if ( '' !== $default ) {
			$data['info'][] = $this->translate( 'default' ) . ' = ' . $default;
		}
		if ( isset( $data['min'] ) ) {
			$data['info'][] = $this->translate( 'min' ) . ' = ' . $this->get_expression( $data['min'], $type );
		}
		if ( isset( $data['max'] ) ) {
			$data['info'][] = $this->translate( 'max' ) . ' = ' . $this->get_expression( $data['max'], $type );
		}
		$data['name']        = $this->get_filter_prefix() . $data['key'];
		$data['saved']       = $this->app->get_option( $data['name'] );
		$data['placeholder'] = $default;
		$value               = $this->apply_filters( $data['key'], $default );
		$data['value']       = $value;
		$data['used']        = $this->get_expression( $value, $type );

		return $data;
	}

	/**
	 * @param mixed $value
	 * @param string $type
	 *
	 * @return mixed
	 */
	private function get_expression( $value, $type ) {
		switch ( $type ) {
			case 'bool':
				// @codingStandardsIgnoreStart
				return var_export( $value, true );
			// @codingStandardsIgnoreEnd
			case 'float':
				return round( $value, 6 );
			default:
				return $value;
		}
	}

	/**
	 * @param string $setting
	 *
	 * @return bool
	 */
	public function is_setting( $setting ) {
		return isset( $this->setting_priority[ $setting ], $this->settings[ $this->setting_priority[ $setting ] ] );
	}
}
