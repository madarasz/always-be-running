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

		'NetrunnerDB' => [
			'client_id'     => env('NETRUNNERDB_CLIENT_ID'),
			'client_secret' => env('NETRUNNERDB_CLIENT_SECRET'),
			'scope'         => [],
		]

	]

];