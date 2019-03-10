<?php
/**
 * WP_Framework_Common Classes Models Utility
 *
 * @version 0.0.26
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

namespace WP_Framework_Common\Classes\Models;

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

/**
 * Class Utility
 * @package WP_Framework_Common\Classes\Models
 */
class Utility implements \WP_Framework_Core\Interfaces\Singleton {

	use \WP_Framework_Core\Traits\Singleton, \WP_Framework_Common\Traits\Package;

	/**
	 * @var string[] $_replace_time
	 */
	private $_replace_time;

	/**
	 * @var string[][] $_snake_cache
	 */
	private $_snake_cache = [];

	/**
	 * @var string[] $_camel_cache
	 */
	private $_camel_cache = [];

	/**
	 * @var string[] $_studly_cache
	 */
	private $_studly_cache = [];

	/**
	 * @return bool
	 */
	protected static function is_shared_class() {
		return true;
	}

	/**
	 * @param array $array
	 * @param bool $preserve_keys
	 *
	 * @return array
	 */
	public function flatten( array $array, $preserve_keys = false ) {
		$return = [];
		array_walk_recursive( $array, function ( $v, $k ) use ( &$return, $preserve_keys ) {
			if ( $preserve_keys ) {
				$return[ $k ] = $v;
			} else {
				$return[] = $v;
			}
		} );

		return $return;
	}

	/**
	 * @return string
	 */
	public function uuid() {
		$pid  = getmypid();
		$node = $this->app->input->server( 'SERVER_ADDR', '0.0.0.0' );
		list( $timeMid, $timeLow ) = explode( ' ', microtime() );

		return sprintf( "%08x%04x%04x%02x%02x%04x%08x", (int) $timeLow, (int) substr( $timeMid, 2 ) & 0xffff,
			mt_rand( 0, 0xfff ) | 0x4000, mt_rand( 0, 0x3f ) | 0x80, mt_rand( 0, 0xff ), $pid & 0xffff, $node );
	}

	/**
	 * @param string $c
	 *
	 * @return bool
	 */
	public function defined( $c ) {
		if ( defined( $c ) ) {
			$const = @constant( $c );
			if ( $const ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $c
	 * @param null $default
	 *
	 * @return mixed|null
	 */
	public function definedv( $c, $default = null ) {
		if ( defined( $c ) ) {
			$const = @constant( $c );

			return $const;
		}

		return $default;
	}

	/**
	 * @param mixed $obj
	 *
	 * @return array
	 */
	public function get_array_value( $obj ) {
		if ( $obj instanceof \stdClass ) {
			$obj = get_object_vars( $obj );
		} elseif ( $obj instanceof \JsonSerializable ) {
			$obj = (array) $obj->jsonSerialize();
		} elseif ( $obj instanceof \Traversable ) {
			$obj = iterator_to_array( $obj );
		} elseif ( ! is_array( $obj ) ) {
			if ( method_exists( $obj, 'to_array' ) ) {
				$obj = (array) $obj->to_array();
			} elseif ( method_exists( $obj, 'toArray' ) ) {
				$obj = (array) $obj->toArray();
			} elseif ( method_exists( $obj, 'toJson' ) ) {
				$obj = json_decode( $obj->toJson(), true );
			}
		}
		if ( ! is_array( $obj ) || empty( $obj ) ) {
			return [];
		}

		return $obj;
	}

	/**
	 * @param mixed $value
	 *
	 * @return array
	 */
	public function array_wrap( $value ) {
		if ( is_null( $value ) ) {
			return [];
		}

		return is_array( $value ) ? $value : [ $value ];
	}

	/**
	 * @param array|object $array
	 * @param string $key
	 * @param mixed $default
	 *
	 * @return mixed
	 */
	public function array_get( $array, $key, $default = null ) {
		$array = $this->get_array_value( $array );

		if ( is_null( $key ) ) {
			return $array;
		}

		if ( array_key_exists( $key, $array ) ) {
			return $array[ $key ];
		}

		if ( strpos( $key, '.' ) === false ) {
			return $default;
		}

		foreach ( explode( '.', $key ) as $segment ) {
			$a = $this->get_array_value( $array );
			if ( array_key_exists( $segment, $a ) ) {
				$array = $a[ $segment ];
			} else {
				return $default;
			}
		}

		return $array;
	}

	/**
	 * @param array|object $array
	 * @param string $key
	 * @param mixed ...$keys
	 *
	 * @return mixed
	 */
	public function array_search( $array, $key, ...$keys ) {
		$array = $this->get_array_value( $array );
		if ( count( $keys ) > 0 ) {
			$default = array_pop( $keys );
		} else {
			$default = null;
		}

		array_unshift( $keys, $key );
		foreach ( $keys as $key ) {
			if ( array_key_exists( $key, $array ) ) {
				return $array[ $key ];
			}
		}

		return $default;
	}

	/**
	 * @param array $array
	 * @param string $key
	 * @param mixed $value
	 */
	public function array_set( array &$array, $key, $value ) {
		$array[ $key ] = $value;
	}

	/**
	 * @param array|object $array
	 * @param string $key
	 * @param mixed $default
	 * @param bool $filter
	 *
	 * @return array
	 */
	public function array_pluck( $array, $key, $default = null, $filter = false ) {
		$array = $this->get_array_value( $array );

		return array_map( function ( $d ) use ( $key, $default ) {
			is_object( $d ) and $d = (array) $d;

			return is_array( $d ) && array_key_exists( $key, $d ) ? $d[ $key ] : $default;
		}, $filter ? array_filter( $array, function ( $d ) use ( $key ) {
			is_object( $d ) and $d = (array) $d;

			return is_array( $d ) && array_key_exists( $key, $d );
		} ) : $array );
	}

	/**
	 * @param array|object $array
	 * @param string|callable $callback
	 *
	 * @return array
	 */
	public function array_map( $array, $callback ) {
		$array = $this->get_array_value( $array );

		foreach ( $array as $key => $value ) {
			$array[ $key ] = is_callable( $callback ) ? $callback( $value, $key ) : ( is_string( $callback ) && method_exists( $value, $callback ) ? $value->$callback( $key ) : null );
		}

		return $array;
	}

	/**
	 * @param array|object $array
	 * @param string $key
	 *
	 * @return array
	 */
	public function array_pluck_unique( $array, $key ) {
		return array_unique( $this->array_pluck( $array, $key, null, true ) );
	}

	/**
	 * @param array $array
	 * @param string|null $key
	 * @param string|null $value
	 *
	 * @return array
	 */
	public function array_combine( array $array, $key, $value = null ) {
		if ( isset( $key ) ) {
			$keys   = $this->array_pluck( $array, $key );
			$values = empty( $value ) ? $array : $this->array_pluck( $array, $value );
		} else {
			$keys   = array_unique( $array );
			$values = $keys;
		}

		return array_combine( $keys, $values );
	}

	/**
	 * @param string $string
	 * @param array $data
	 *
	 * @return string
	 */
	public function replace( $string, array $data ) {
		foreach ( $data as $k => $v ) {
			$string = str_replace( '${' . $k . '}', $v, $string );
		}

		return $string;
	}

	/**
	 * @param string $string
	 *
	 * @return string
	 */
	public function replace_time( $string ) {
		if ( ! isset( $this->_replace_time ) ) {
			$this->_replace_time = [];
			foreach (
				[
					'Y',
					'y',
					'M',
					'm',
					'n',
					'D',
					'd',
					'H',
					'h',
					'i',
					'j',
					's',
				] as $t
			) {
				$this->_replace_time[ $t ] = date_i18n( $t );
			}
		}

		return $this->replace( $string, $this->_replace_time );
	}

	/**
	 * @param string $string
	 * @param string $delimiter
	 *
	 * @return array
	 */
	public function explode( $string, $delimiter = ',' ) {
		return array_filter( array_unique( array_map( 'trim', explode( $delimiter, $string ) ) ) );
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
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	public function starts_with( $haystack, $needle ) {
		if ( '' === $haystack || '' === $needle ) {
			return false;
		}
		if ( $haystack === $needle ) {
			return true;
		}

		return strncmp( $haystack, $needle, strlen( $needle ) ) === 0;
	}

	/**
	 * @param string $haystack
	 * @param string $needle
	 *
	 * @return bool
	 */
	public function ends_with( $haystack, $needle ) {
		if ( '' === $haystack || '' === $needle ) {
			return false;
		}
		if ( $haystack === $needle ) {
			return true;
		}

		return substr_compare( $haystack, $needle, - strlen( $needle ) ) === 0;
	}

	/**
	 * @param string $haystack
	 * @param string|array $needles
	 *
	 * @return bool
	 */
	public function contains( $haystack, $needles ) {
		foreach ( (array) $needles as $needle ) {
			if ( $needle !== '' && mb_strpos( $haystack, $needle ) !== false ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	public function lower( $value ) {
		return mb_strtolower( $value, 'UTF-8' );
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	public function camel( $value ) {
		if ( ! isset( $this->_camel_cache[ $value ] ) ) {
			$this->_camel_cache[ $value ] = lcfirst( $this->studly( $value ) );
		}

		return $this->_camel_cache[ $value ];
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	public function studly( $value ) {
		if ( ! isset( $this->_studly_cache[ $value ] ) ) {
			$_value                        = ucwords( str_replace( [ '-', '_' ], ' ', $value ) );
			$this->_studly_cache[ $value ] = str_replace( ' ', '', $_value );
		}

		return $this->_studly_cache[ $value ];
	}

	/**
	 * @param string $value
	 * @param string $delimiter
	 *
	 * @return string
	 */
	public function snake( $value, $delimiter = '_' ) {
		if ( ! isset( $this->_snake_cache[ $value ][ $delimiter ] ) ) {
			$_value = $value;
			if ( ! ctype_lower( $_value ) ) {
				$_value = preg_replace( '/\s+/u', '', ucwords( $_value ) );
				$_value = $this->lower( preg_replace( '/(.)(?=[A-Z])/u', '$1' . $delimiter, $_value ) );
			}
			$this->_snake_cache[ $value ][ $delimiter ] = $_value;
		}

		return $this->_snake_cache[ $value ][ $delimiter ];
	}

	/**
	 * @param string $value
	 *
	 * @return string
	 */
	public function kebab( $value ) {
		return $this->snake( $value, '-' );
	}

	/**
	 * @return bool
	 */
	public function doing_ajax() {
		if ( $this->definedv( 'REST_REQUEST' ) ) {
			return true;
		}

		if ( function_exists( 'wp_doing_ajax' ) ) {
			return wp_doing_ajax();
		}

		return $this->definedv( 'DOING_AJAX' );
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
	public function was_admin() {
		return $this->is_admin_url( $this->app->input->referer() );
	}

	/**
	 * @param string $url
	 *
	 * @return bool
	 */
	public function is_admin_url( $url ) {
		return $this->starts_with( $url, admin_url() );
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
		$backtrace = debug_backtrace();
		foreach ( $backtrace as $k => $v ) {
			// 大量のデータになりがちな object と args を削除や編集
			unset( $backtrace[ $k ]['object'] );
			if ( ! empty( $backtrace[ $k ]['args'] ) ) {
				$backtrace[ $k ]['args'] = $this->parse_backtrace_args( $backtrace[ $k ]['args'] );
			} else {
				unset( $backtrace[ $k ]['args'] );
			}
			if ( ! empty( $unset ) ) {
				foreach ( $v as $key => $value ) {
					if ( in_array( $key, $unset ) ) {
						unset( $backtrace[ $k ][ $key ] );
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
		return $this->array_map( $args, function ( $d ) {
			$type = gettype( $d );
			if ( 'array' === $type ) {
				return $this->parse_backtrace_args( $d );
			} elseif ( 'object' === $type ) {
				$type = get_class( $d );
			} elseif ( 'resource' !== $type && 'resource (closed)' !== $type && 'NULL' !== $type && 'unknown type' !== $type ) {
				if ( 'boolean' === $type ) {
					$d = var_export( $d, true );
				}
				$type .= ': ' . $d;
			}

			return $type;
		} );
	}

	/**
	 * @param string $dir
	 * @param bool $split
	 * @param string $relative
	 * @param array $ignore
	 *
	 * @return array
	 */
	public function scan_dir_namespace_class( $dir, $split = false, $relative = '', array $ignore = [ 'base.php' ] ) {
		$dir  = rtrim( $dir, DS );
		$list = [];
		if ( is_dir( $dir ) ) {
			foreach ( scandir( $dir ) as $file ) {
				if ( $file === '.' || $file === '..' || in_array( $file, $ignore ) ) {
					continue;
				}

				$path = rtrim( $dir, DS ) . DS . $file;
				if ( is_file( $path ) ) {
					if ( $this->ends_with( $file, '.php' ) ) {
						if ( $split ) {
							$list[] = [ $relative, ucfirst( $this->app->get_page_slug( $file ) ) ];
						} else {
							$list[] = $relative . ucfirst( $this->app->get_page_slug( $file ) );
						}
					}
				} elseif ( is_dir( $path ) ) {
					$list = array_merge( $list, $this->scan_dir_namespace_class( $path, $split, $relative . ucfirst( $file ) . '\\', $ignore ) );
				}
			}
		}

		return $list;
	}

	/**
	 * @param string $type
	 * @param bool $detect_text
	 *
	 * @return string
	 */
	public function parse_db_type( $type, $detect_text = false ) {
		switch ( true ) {
			case stristr( $type, 'TINYINT(1)' ) !== false:
				return 'bool';
			case stristr( $type, 'INT' ) !== false:
				return 'int';
			case stristr( $type, 'BIT' ) !== false:
				return 'bool';
			case stristr( $type, 'BOOLEAN' ) !== false:
				return 'bool';
			case stristr( $type, 'DECIMAL' ) !== false:
				return 'number';
			case stristr( $type, 'FLOAT' ) !== false:
				return 'float';
			case stristr( $type, 'DOUBLE' ) !== false:
				return 'number';
			case stristr( $type, 'REAL' ) !== false:
				return 'number';
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
		if ( empty( $post ) || ! $post instanceof \WP_Post ) {
			return false;
		}
		! is_array( $tags ) and $tags = [ $tags ];
		$content = $post->post_content;
		foreach ( $tags as $tag ) {
			if ( has_shortcode( $content, $tag ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	public function is_valid_tinymce_color_picker() {
		global $wp_version;

		return version_compare( $wp_version, '4.0.0', '>=' );
	}

	/**
	 * @return bool
	 */
	public function can_use_block_editor() {
		global $wp_version;

		return version_compare( $wp_version, '5.0.0', '>=' );
	}

	/**
	 * @return bool
	 */
	public function is_block_editor() {
		if ( ! is_admin() ) {
			return false;
		}
		$current_screen = get_current_screen();

		return ( method_exists( $current_screen, 'is_block_editor' ) && $current_screen->is_block_editor() ) || ( function_exists( 'is_gutenberg_page' ) && is_gutenberg_page() );
	}

	/**
	 * @param \WP_Framework $app
	 * @param string $name
	 * @param callable $func
	 * @param int $timeout
	 *
	 * @return bool
	 */
	public function lock_process( \WP_Framework $app, $name, callable $func, $timeout = 60 ) {
		$name         .= '__LOCK_PROCESS__';
		$timeout_name = $name . 'TIMEOUT__';
		$option       = $app->option;
		$option->reload_options();
		$check = $option->get( $name );
		if ( ! empty( $check ) ) {
			$expired = $option->get( $timeout_name, 0 ) < time();
			if ( ! $expired ) {
				return false;
			}
		}
		$rand = md5( uniqid() );
		$option->set( $name, $rand );
		$option->reload_options();
		if ( $option->get( $name ) != $rand ) {
			return false;
		}
		$option->set( $timeout_name, time() + $timeout );
		try {
			$func();
		} catch ( \Exception $e ) {
			$app->log( $e );
		} finally {
			$option->delete( $name );
			$option->delete( $timeout_name );
		}

		return true;
	}

	/**
	 * @param \WP_Framework $app
	 *
	 * @return bool
	 */
	public function delete_upload_dir( \WP_Framework $app ) {
		return $this->delete_dir( $app->define->upload_dir );
	}

	/**
	 * @see https://qiita.com/algo13/items/34bb9750f0e450109a03
	 *
	 * @param $dir
	 *
	 * @return bool
	 */
	private function delete_dir( $dir ) {
		clearstatcache( true, $dir );
		if ( is_file( $dir ) ) {
			return @unlink( $dir );
		} elseif ( is_link( $dir ) ) {
			return @unlink( $dir ) || ( '\\' === DS && @rmdir( $dir ) );
		} elseif ( $this->is_junction( $dir ) ) {
			return @rmdir( $dir );
		} elseif ( is_dir( $dir ) ) {
			$failed = false;
			foreach ( new \FilesystemIterator( $dir ) as $file ) {
				/** @var \DirectoryIterator $file */
				if ( ! $this->delete_dir( $file->getPathname() ) ) {
					$failed = true;
				}
			}

			return ! $failed && @rmdir( $dir );
		}

		return true;
	}

	/**
	 * @param string $check
	 *
	 * @return bool
	 */
	private function is_junction( $check ) {
		if ( '\\' !== DS ) {
			return false;
		}

		$stat = @lstat( $check );

		return $stat !== false && ! ( $stat['mode'] & 0xC000 );
	}

	/**
	 * @param string $path
	 *
	 * @return bool
	 */
	public function file_exists( $path ) {
		return file_exists( $path );
	}

	/**
	 * @param \WP_Framework $app
	 * @param string $path
	 *
	 * @return string
	 */
	private function get_upload_file_path( \WP_Framework $app, $path ) {
		return $app->define->upload_dir . DS . ltrim( str_replace( '/', DS, $path ), DS );
	}

	/**
	 * @param \WP_Framework $app
	 * @param string $path
	 *
	 * @return string
	 */
	private function get_upload_file_link( \WP_Framework $app, $path ) {
		return $app->define->upload_url . '/' . ltrim( str_replace( DS, '/', $path ), '/' );
	}

	/**
	 * @param \WP_Framework $app
	 * @param string $path
	 *
	 * @return bool
	 */
	public function upload_file_exists( \WP_Framework $app, $path ) {
		return $this->file_exists( $this->get_upload_file_path( $app, $path ) );
	}

	/**
	 * @param \WP_Framework $app
	 * @param string $path
	 * @param mixed $data
	 *
	 * @throws \Exception
	 */
	public function create_upload_file( \WP_Framework $app, $path, $data ) {
		$path = $this->get_upload_file_path( $app, $path );
		@mkdir( dirname( $path ), 0700, true );
		if ( false === @file_put_contents( $path, $data, 0644 ) ) {
			throw new \Exception( 'Failed to create .htaccess file.' );
		}
	}

	/**
	 * @param \WP_Framework $app
	 * @param string $path
	 * @param callable $generator
	 *
	 * @return bool
	 */
	public function create_upload_file_if_not_exists( \WP_Framework $app, $path, $generator ) {
		if ( ! $this->upload_file_exists( $app, $path ) ) {
			if ( isset( $generator ) && is_callable( $generator ) ) {
				try {
					$this->create_upload_file( $app, $path, $generator() );
				} catch ( \Exception $e ) {
					return false;
				}
			} else {
				return false;
			}
		}

		return true;
	}

	/**
	 * @param \WP_Framework $app
	 * @param string $path
	 *
	 * @return bool
	 */
	public function delete_upload_file( \WP_Framework $app, $path ) {
		return @unlink( $this->get_upload_file_path( $app, $path ) );
	}

	/**
	 * @param \WP_Framework $app
	 * @param string $path
	 * @param callable|null $generator
	 *
	 * @return bool|string
	 */
	public function get_upload_file_contents( \WP_Framework $app, $path, $generator = null ) {
		if ( $this->create_upload_file_if_not_exists( $app, $path, $generator ) ) {
			return @file_get_contents( $this->get_upload_file_path( $app, $path ) );
		}

		return false;
	}

	/**
	 * @param \WP_Framework $app
	 * @param string $path
	 * @param callable|null $generator
	 *
	 * @return string|false
	 */
	public function get_upload_file_url( \WP_Framework $app, $path, $generator = null ) {
		if ( $this->create_upload_file_if_not_exists( $app, $path, $generator ) ) {
			return $this->get_upload_file_link( $app, $path );
		}

		return false;
	}

	/**
	 * @param string $message
	 * @param null|array $override_allowed_html
	 *
	 * @return string
	 */
	public function strip_tags( $message, $override_allowed_html = null ) {
		$allowed_html = [
			'a'      => [ 'href' => true, 'target' => true, 'rel' => true ],
			'b'      => [],
			'br'     => [],
			'sub'    => [],
			'sup'    => [],
			'strong' => [],
			's'      => [],
			'u'      => [],
			'em'     => [],
			'h1'     => [],
			'h2'     => [],
			'h3'     => [],
			'h4'     => [],
			'h5'     => [],
			'h6'     => [],
		];
		if ( ! empty( $override_allowed_html ) && is_array( $override_allowed_html ) ) {
			$allowed_html = array_replace_recursive( $allowed_html, $override_allowed_html );
		}

		return wp_kses( $message, $allowed_html );
	}

	/**
	 * @param string $plugin
	 *
	 * @return bool
	 */
	public function is_active_plugin( $plugin ) {
		return in_array( $plugin, (array) get_option( 'active_plugins', [] ) ) || ( is_multisite() && ( $plugins = get_site_option( 'active_sitewide_plugins' ) ) && isset( $plugins[ $plugin ] ) );
	}
}
