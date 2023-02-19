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
	protected Client $client;

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

		$this->client = new Client();
	}

	/**
	 * Upload the files.
	 *
	 * @return mixed
	 * @throws \GuzzleHttp\Exception\GuzzleException
	 */
	public function run() {
		$response = $this->client->request( 'POST', $this->url . 'wp-json/wp-stockroom/v1/package', [
			'multipart' => [
				[
					'name'     => 'slug',
					'contents' => $this->slug,
				],
				[
					'name'     => 'package_type',
					'contents' => 'plugin', // TODO make dynamic.
				],
				[
					'name'     => 'version',
					'contents' => $this->version,
				],
				[
					'name'     => 'readme_file',
					'contents' => file_get_contents( $this->readmeFile ),
					'filename' => basename( $this->readmeFile ),
				],
				[
					'name'     => 'package_zip_file',
					'contents' => file_get_contents( $this->zipFile ),
					'filename' => basename( $this->zipFile ),
				],
			],
			'auth'      => [ $this->username, $this->password ],
			'verify'    => ( $this->url !== 'https://repository.lndo.site/' ), // Only for local debugging.
		] );

		return json_decode( $response->getBody()->getContents() );
	}
}

