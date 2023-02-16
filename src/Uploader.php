<?php

namespace WP_Stockroom;

use GuzzleHttp\Client;

class Uploader {

	protected string $url;
	protected string $username;
	protected string $password;
	protected string $version;
	protected string $readmeFile;
	protected string $zipFile;
	protected string $slug;
	protected int $readmeFileId;
	protected int $fileZipId;

	/**
	 * @param string $url        Base url to a WP rest API
	 * @param string $username   Username that can upload to the rest API
	 * @param string $password   Preferably an application password
	 * @param string $readmefile The full path to the readme.txt.
	 * @param string $zipfile    The full path to the theme of plugin zip-file.
	 * @param string $slug       Slug of the plugin/theme defaults to the basename of the ZIP
	 */
	public function __construct( string $url, string $username, string $password, string $version, string $readmefile, string $zipfile, string $slug ) {
		$this->url        = ltrim( $url ) . '/'; // force trailing slash
		$this->username   = $username;
		$this->password   = $password;
		$this->version    = $version;
		$this->readmeFile = $readmefile;
		$this->zipFile    = $zipfile;
		$this->slug       = $slug;
	}

	public function run() {
		// Readme
		try {
			$client   = new Client();
			$response = $client->request( 'POST', $this->url . 'wp-json/wp/v2/media', [
				'multipart' => [
					[
						'name'     => 'title',
						'contents' => 'readmeeeee',
					],
					[
						'name'     => 'package',
						'contents' => $this->slug,
					],
					[
						'name'     => 'version',
						'contents' => $this->version,
					],
					[
						'name'     => 'file',
						'contents' => file_get_contents( $this->readmeFile ),
						'filename' => basename( $this->readmeFile ),
					],
				],
				'auth'      => [ $this->username, $this->password ],
			] );
			echo $response->getBody()->getContents();
		} catch ( \GuzzleHttp\Exception\ClientException $e ) {
			$response             = $e->getResponse();
			$responseBodyAsString = $response->getBody()->getContents();
		}

		// ZIP
		try {
			$client   = new Client();
			$response = $client->request( 'POST', $this->url . 'wp-json/wp/v2/media', [
				'multipart' => [
					[
						'name'     => 'title',
						'contents' => 'readmeeeee',
					],
					[
						'name'     => 'package',
						'contents' => $this->slug,
					],
					[
						'name'     => 'version',
						'contents' => $this->version,
					],
					[
						'name'     => 'file',
						'contents' => file_get_contents( $this->readmeFile ),
						'filename' => basename( $this->readmeFile ),
					],
				],
				'auth'      => [ $this->username, $this->password ],
			] );
			echo $response->getBody()->getContents();
		} catch ( \GuzzleHttp\Exception\ClientException $e ) {
			$response             = $e->getResponse();
			$responseBodyAsString = $response->getBody()->getContents();
		}
	}

}

