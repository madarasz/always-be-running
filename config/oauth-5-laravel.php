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

		'ThronesDB' => [
			'client_id'     => env('THRONESDB_CLIENT_ID'),
			'client_secret' => env('THRONESDB_CLIENT_SECRET'),
			'scope'         => [],
		],

		'NetrunnerDB' => [
			'client_id'     => env('NETRUNNERDB_CLIENT_ID'),
			'client_secret' => env('NETRUNNERDB_CLIENT_SECRET'),
			'scope'         => [],
		]

	]

];