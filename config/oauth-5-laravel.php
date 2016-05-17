<?php

return [

	/*
	|--------------------------------------------------------------------------
	| oAuth Config
	|--------------------------------------------------------------------------
	*/

	/**
	 * Storage
	 */
	'storage' => '\\OAuth\\Common\\Storage\\Session',

	/**
	 * Consumers
	 */
	'consumers' => [

		'Thrones' => [
			'client_id'     => env('THRONES_CLIENT_ID'),
			'client_secret' => env('THRONES_CLIENT_SECRET'),
			'scope'         => [],
		]

	]

];