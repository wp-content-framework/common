<?php
/**
 * WP_Framework_Common Configs Setting
 *
 * @author Technote
 * @copyright Technote All Rights Reserved
 * @license http://www.opensource.org/licenses/gpl-2.0.php GNU General Public License, version 2
 * @link https://technote.space
 */

if ( ! defined( 'WP_CONTENT_FRAMEWORK' ) ) {
	exit;
}

return [

	'999' => [
		'Others' => [
			'10' => [
				'use_filesystem_credentials' => [
					'label'   => 'Whether to use filesystem credentials',
					'type'    => 'bool',
					'default' => false,
				],
			],
		],
	],

];