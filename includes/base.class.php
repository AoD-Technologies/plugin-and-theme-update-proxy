<?php

namespace AoDTechnologies\PluginAndThemeUpdateProxy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once( 'common.class.php' );

class PluginAndThemeUpdateProxyBase {
	protected $underscoreTextDomain;

	protected $optionsPageName;

	protected $authenticationTokensOptionName;

	protected $sourceOptionName;
	protected $source;

	protected static $cacheDirectory = WP_CONTENT_DIR . DIRECTORY_SEPARATOR . 'ptup-cache';
	protected static $blogURL;
	protected static $instance;

	public static function getVersion() {
		return '1.07';
	}

	public static function getTextDomain() {
		return 'plugin-and-theme-update-proxy';
	}

	public static function getName() {
		return __( 'Plugin and Theme Update Proxy', self::getTextDomain() );
	}

	public static function getUserAgent() {
		return 'PluginAndThemeUpdateProxy/' . self::getVersion() . '; ' . self::$blogURL;
	}

	public static function init() {
		self::$blogURL = get_bloginfo( 'url' );
	}

	public static function uninstall() {
		$underscoreTextDomain = str_replace( '-', '_', self::getTextDomain() );

		delete_option( "{$underscoreTextDomain}_authentication_tokens" );
		delete_option( "{$underscoreTextDomain}_source" );

		Common::removeDirectory( self::$cacheDirectory );

		return $underscoreTextDomain;
	}

	public function __construct($actionsAndFiltersPriority = 10) {
		$this->underscoreTextDomain = str_replace( '-', '_', self::getTextDomain() );

		$this->optionsPageName = self::getTextDomain();

		$this->authenticationTokensOptionName = "{$this->underscoreTextDomain}_authentication_tokens";
		$this->sourceOptionName = "{$this->underscoreTextDomain}_source";

		add_action( 'plugins_loaded', array( $this, 'actionsAndFilters' ), $actionsAndFiltersPriority );
	}

	public function actionsAndFilters() {
		add_action( 'init', array( $this, 'maybeRunMigrations' ) );

		add_filter( 'http_request_args', array( $this, 'detectPluginUpdaterRequest' ), 1337, 2 );
		add_filter( 'http_request_args', array( $this, 'detectThemeUpdaterRequest' ), 1337, 2 );

		add_filter( 'auto_update_plugin', array( $this, 'allowAutoUpdates' ), 1337, 2 );
		add_filter( 'auto_update_theme', array( $this, 'allowAutoUpdates' ), 1337, 2 );

		add_action( 'requests-requests.before_request', array( $this, 'addAuthenticationTokenToDownloadPackageRequest' ), 1337, 2 );

		if ( is_admin() ) {
			add_action( 'admin_menu', array( $this, 'addOptionsPage' ) );

			add_action( 'admin_notices', array( $this, 'renderAdminNotices' ) );

			add_action( 'wp_ajax_ptup_available_plugins', array( $this, 'ajaxAvailablePluginsEndpoint' ) );
			add_action( 'wp_ajax_ptup_available_themes', array( $this, 'ajaxAvailableThemesEndpoint' ) );
			add_action( 'wp_ajax_ptup_authentication_tokens', array( $this, 'ajaxAuthenticationTokensEndpoint' ) );
			add_action( 'wp_ajax_ptup_sources', array( $this, 'ajaxSourcesEndpoint' ) );

			add_action( 'wp_ajax_ptup_hosted', array( $this, 'ajaxHostedEndpoint' ) );
			add_action( 'wp_ajax_nopriv_ptup_hosted', array( $this, 'ajaxHostedEndpoint' ) );

			add_action( 'wp_ajax_ptup_download_package', array( $this, 'ajaxDownloadPackageEndpoint' ) );
			add_action( 'wp_ajax_nopriv_ptup_download_package', array( $this, 'ajaxDownloadPackageEndpoint' ) );
		}
	}

	public function maybeRunMigrations() {
		$storedVersion = get_option( "{$this->underscoreTextDomain}_version", '0' );

		// Migration code for already active plugins
		if ( version_compare( $storedVersion, self::getVersion(), '<' ) ) {
			$this->runMigrations( $storedVersion );

			update_option( "{$this->underscoreTextDomain}_version", self::getVersion() );
		}
	}

	protected function runMigrations($storedVersion) {
		switch ( $storedVersion ) {
			case '0':
				$this->loadSource();
				if ( $this->source !== null ) {
					$this->source->selectedThemes = [];
					$this->saveSource( json_encode( $this->source ) );
				}
			// All intermediate versions until the next upgrade go here
			case '1.07':
				// The next upgrade code goes here
			default:
				break;
		}
	}

	protected function loadAuthenticationTokens() {
		if ( empty( $this->authenticationTokens ) ) {
			$this->authenticationTokens = json_decode( get_option( $this->authenticationTokensOptionName, '[]' ) );
		}
	}

	protected function loadSource() {
		if ( empty( $this->source ) ) {
			$this->source = json_decode( get_option( $this->sourceOptionName, 'null' ) );
		}
	}

	protected function isUpdaterRequest($url, $type) {
		$url_details = @parse_url( $url );
		return isset( $url_details['host'] ) && $url_details['host'] === 'api.wordpress.org' && isset( $url_details['path'] ) && strpos( $url_details['path'], "/{$type}/update-check/" ) === 0;
	}

	public function detectPluginUpdaterRequest($parsed_args, $url) {
		if ( $this->isUpdaterRequest($url, 'plugins')) {
			add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pushPluginUpdates' ), 1337 );
			remove_filter( 'http_request_args', array( $this, 'detectPluginUpdaterRequest' ), 1337 );
		}

		return $parsed_args;
	}

	public function detectThemeUpdaterRequest($parsed_args, $url) {
		if ( $this->isUpdaterRequest($url, 'themes')) {
			add_filter( 'pre_set_site_transient_update_themes', array( $this, 'pushThemeUpdates' ), 1337 );
			remove_filter( 'http_request_args', array( $this, 'detectThemeUpdaterRequest' ), 1337 );
		}

		return $parsed_args;
	}

	public function allowAutoUpdates($update, $item) {
		if ( isset( $item->package ) ) {
			$query = @parse_url( $item->package, PHP_URL_QUERY );
			if ( $query !== false && $query !== null ) {
				$params = array();
				@parse_str( $query, $params );
				if ( isset( $params['action'] ) && $params['action'] === 'ptup_download_package' ) {
					return null;
				}
			}
		}

		return $update;
	}

	public function addAuthenticationTokenToDownloadPackageRequest(&$url, &$headers) {
		$query = @parse_url( $url, PHP_URL_QUERY );
		if ( $query !== false && $query !== null ) {
			$params = array();
			@parse_str( $query, $params );
			if ( isset( $params['action'] ) && $params['action'] === 'ptup_download_package' ) {
				$headers['x-ptup-authentication-token'] = $params['x_ptup_authentication_token'];
				$url = remove_query_arg( 'x_ptup_authentication_token', $url );
			}
		}
	}

	protected function pushUpdate($source, $value, $type, $checked, $errorMessages) {
		if ( count( $checked ) === 0 ) {
			return;
		}

		$data = $type === 'plugin' ? $source->selectedPlugins : $source->selectedThemes;
		if ( $data === null || count( $data ) === 0 ) {
			return;
		}

		$body = array_intersect_key( $checked, array_flip( $data ) );
		if ( count( $body ) === 0 ) {
			return;
		}

		$headers = $this->recursionCheck( $source );
		if ( $headers === null ) {
			return;
		}

		$result = wp_remote_post( add_query_arg( 'type', $type, $source->updateURL ), array(
			'httpversion' => '1.1',
			'user-agent' => self::getUserAgent(),
			'headers' => $headers,
			'sslverify' => !$source->skipSSLCertificateChecks,
			'body' => json_encode( $body )
		) );

		if ( is_wp_error( $result ) ) {
			$errorMessages[] = sprintf( __( 'Error contacting source "%1$s" for updates: %2$s', self::getTextDomain() ), $source->label, $result->get_error_message() );
		} else {
			switch ( $result['response']['code'] ) {
				case 200:
					$response = json_decode( $result['body'], true );
					if ( $response === null ) {
						$errorMessages[] = sprintf( __( 'Received invalid update data from source "%1$s"', self::getTextDomain() ), $source->label );
						break;
					}

					foreach ( $response as $subkey => $availableUpdates ) {
						foreach ( $availableUpdates as &$update ) {
							switch ($type) {
								case 'plugin':
									$update = (object) $update;
									if ( $subkey === 'response' ) {
										if ( isset( $checked[$update->slug] ) ) {
											$checked[$update->slug] = $update->new_version;
										}
									} else if ( isset( $value->no_update ) && isset( $value->no_update[$update->slug] ) && version_compare( $value->no_update[$update->slug]->new_version, $update->new_version, '<=' ) ) {
										$update = null;
									}
									break;
								case 'theme':
									$update = (array) $update;
									if ( $subkey === 'response' ) {
										if ( isset( $checked[$update['slug']] ) ) {
											$checked[$update['slug']] = $update['new_version'];
										}
									} else if ( isset( $value->no_update ) && isset( $value->no_update[$update['slug']] ) && version_compare( $value->no_update[$update['slug']]['new_version'], $update['new_version'], '<=' ) ) {
										$update = null;
									}
									break;
								default:
									break;
							}
						}

						$value->$subkey = array_merge( $value->$subkey, array_filter( $availableUpdates, function ($update) {
							return $update !== null;
						} ) );
					}
					break;
				default:
					$errorMessages[] = sprintf( __( 'Received unexpected HTTP status while checking update source "%1$s": %2$s %3$s', self::getTextDomain() ), $source->label, $result['response']['code'], $result['response']['message'] );
					break;
			}
		}
	}

	protected function pushUpdatesForType($value, $type, $checked, $errorMessages) {
		$this->pushUpdate($this->source, $value, $type, $checked, $errorMessages);
	}

	protected function pushUpdates($value, $type) {
		$this->loadSource();

		switch ($type) {
			case 'plugin':
				$currentData = Common::getPlugins();
				break;
			case 'theme':
				$currentData = Common::getThemes();
				break;
			default:
				return $value;
		}

		$checked = array();
		foreach( $currentData as $file => $data ) {
			$checked[$file] = $data['Version'];
		}

		$errorMessages = array();

		$this->pushUpdatesForType($value, $type, $checked, $errorMessages);

		if ( isset( $value->response ) && isset( $value->no_update) ) {
			$value->no_update = array_diff_key( $value->no_update, $value->response );
		}

		if ( count( $errorMessages ) > 0 ) {
			set_transient( "{$this->underscoreTextDomain}_push_{$type}_updates_result", array(
				'type' => 'error',
				'message' => join( '<br>', $errorMessages )
			), HOUR_IN_SECONDS );
		}

		return $value;
	}

	public function pushPluginUpdates($value) {
		$result = $this->pushUpdates($value, 'plugin');

		add_filter( 'http_request_args', array( $this, 'detectPluginUpdaterRequest' ), 1337, 2 );
		remove_filter( 'pre_set_site_transient_update_plugins', array( $this, 'pushPluginUpdates' ), 1337 );

		return $result;
	}

	public function pushThemeUpdates($value) {
		$result = $this->pushUpdates($value, 'theme');

		add_filter( 'http_request_args', array( $this, 'detectThemeUpdaterRequest' ), 1337, 2 );
		remove_filter( 'pre_set_site_transient_update_themes', array( $this, 'pushThemeUpdates' ), 1337 );

		return $result;
	}

	public function addOptionsPage() {
		$hookSuffix = add_options_page( 'Plugin and Theme Update Proxy', 'Plugin and Theme Update Proxy', 'install_plugins', $this->optionsPageName, array( $this, 'renderOptionsPage' ) );
		if ( $hookSuffix !== false ) {
			add_action( "load-{$hookSuffix}", array( $this, 'loadOptionsPage' ) );
		}
	}

	public function renderOptionsPage() {
?>
		<div class="wrap">
			<h1><?php echo self::getName() ?></h1>
			<div id="ptup-root"></div>
			<script type="text/javascript">
				window.PTUP = {
					ajaxURL: <?php echo json_encode( admin_url( 'admin-ajax.php' ) ); ?>
				};
			</script>
		</div>
<?php
	}

	public function loadOptionsPage() {
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueueAdminApp' ) );

		$this->loadAuthenticationTokens();
		$this->loadSource();
	}

	public function enqueueAdminApp() {
		require_once( __DIR__ . '/../adminEnqueueScripts.php' );
	}

	public function renderAdminNotices() {
		$pushPluginUpdatesResult = get_transient( "{$this->underscoreTextDomain}_push_plugin_updates_result" );
		if ( $pushPluginUpdatesResult !== false ) {
			delete_transient( "{$this->underscoreTextDomain}_push_plugin_updates_result" );

?>
<div id="<?php echo esc_attr( self::getTextDomain() . '_push-plugin-updates-notice' ); ?>" class="notice notice-<?php echo esc_attr( $pushPluginUpdatesResult['type'] ); ?> is-dismissible">
	<p><?php echo $pushPluginUpdatesResult['message']; ?></p>
</div>
<?php
		}

		$pushThemeUpdatesResult = get_transient( "{$this->underscoreTextDomain}_push_theme_updates_result" );
		if ( $pushThemeUpdatesResult !== false ) {
			delete_transient( "{$this->underscoreTextDomain}_push_theme_updates_result" );

?>
<div id="<?php echo esc_attr( self::getTextDomain() . '_push-theme-updates-notice' ); ?>" class="notice notice-<?php echo esc_attr( $pushThemeUpdatesResult['type'] ); ?> is-dismissible">
	<p><?php echo $pushThemeUpdatesResult['message']; ?></p>
</div>
<?php
		}

		$getSourcePluginsResult = get_transient( "{$this->underscoreTextDomain}_get_source_plugins_result" );
		if ( $getSourcePluginsResult !== false ) {
			delete_transient( "{$this->underscoreTextDomain}_get_source_plugins_result" );

?>
<div id="<?php echo esc_attr( self::getTextDomain() . '_get-source-plugins-notice' ); ?>" class="notice notice-<?php echo esc_attr( $getSourcePluginsResult['type'] ); ?> is-dismissible">
	<p><?php echo $getSourcePluginsResult['message']; ?></p>
</div>
<?php
		}

		$getSourceThemesResult = get_transient( "{$this->underscoreTextDomain}_get_source_themes_result" );
		if ( $getSourceThemesResult !== false ) {
			delete_transient( "{$this->underscoreTextDomain}_get_source_themes_result" );

?>
<div id="<?php echo esc_attr( self::getTextDomain() . '_get-source-themes-notice' ); ?>" class="notice notice-<?php echo esc_attr( $getSourceThemesResult['type'] ); ?> is-dismissible">
	<p><?php echo $getSourceThemesResult['message']; ?></p>
</div>
<?php
		}
	}

	public function ajaxHostedEndpoint() {
		if ( !isset( $_SERVER['HTTP_X_PTUP_AUTHENTICATION_TOKEN'] ) ) {
			status_header( 401 );
			exit;
		}

		$authenticationToken = sanitize_text_field( $_SERVER['HTTP_X_PTUP_AUTHENTICATION_TOKEN'] );

		$this->loadAuthenticationTokens();

		$allowedData = null;
		$type = sanitize_text_field( $_GET['type'] );
		switch ($type) {
			case 'plugin':
				foreach ( $this->authenticationTokens as $possibleAuthenticationToken ) {
					if ( $possibleAuthenticationToken->value === $authenticationToken ) {
						if ( $possibleAuthenticationToken->enabled ) {
							$allowedData = $possibleAuthenticationToken->selectedPlugins;
						}
						break;
					}
				}
				break;
			case 'theme':
				foreach ( $this->authenticationTokens as $possibleAuthenticationToken ) {
					if ( $possibleAuthenticationToken->value === $authenticationToken ) {
						if ( $possibleAuthenticationToken->enabled ) {
							$allowedData = $possibleAuthenticationToken->selectedThemes;
						}
						break;
					}
				}
				break;
			default:
				status_header(400);
				exit;
		}

		if ( $allowedData === null ) {
			status_header( 403 );
			exit;
		}

		$body = json_decode( file_get_contents( 'php://input' ), true );

		$result = null;

		$checked = $body !== null ? array_intersect_key( $body, array_flip( $allowedData ) ) : array_map( function() { return '0'; }, array_flip( $allowedData ) );
		if ( count( $checked ) > 0 ) {
			$result = array(
				'response' => array(),
				'no_update' => array()
			);

			$all = $type === 'plugin' ? Common::getPlugins() : Common::getThemes();

			switch ($type) {
				case 'plugin':
					wp_update_plugins();
					break;
				case 'theme':
					wp_update_themes();
					break;
				default:
					break;
			}

			$currentUpdates = get_site_transient( "update_{$type}s" );
			$updates = $currentUpdates !== false ? array_map( array( __NAMESPACE__ . '\Common', 'convertToObject' ), array_intersect_key( array_merge( isset( $currentUpdates->no_update ) ? $currentUpdates->no_update : array(), isset( $currentUpdates->response ) ? $currentUpdates->response : array() ), $checked ) ) : array();

			if ( count( $updates ) > 0 ) {
				foreach ( $updates as $file => $data ) {
					if ( isset( $data->package ) ) {
						$updatePackageFileName = self::$cacheDirectory . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $file;
						$updatePackageVersionFileName = "{$updatePackageFileName}.version.php";

						$version = false;
						include ( $updatePackageVersionFileName );
						if ( version_compare( $version, $data->new_version, '<' ) ) {
							$version = addslashes( $data->new_version );

							$baseName = basename( explode( '/', $file )[0], '.php' );
							$tmpFile = download_url( $data->package );

							if ( is_wp_error( $tmpFile ) ) {
								// Uh oh! Skip this update for now...
								continue;
							}

							$tmpFileSize = filesize( $tmpFile );
							$contentMD5 = addslashes( base64_encode( md5_file( $tmpFile, true ) ) );
							$tmpFileContents = addslashes( base64_encode( file_get_contents( $tmpFile ) ) );
							unlink( $tmpFile );

							Common::createDirectoryIfNeeded( $updatePackageFileName );
							Common::createDownloadableFile( $updatePackageFileName, $tmpFileSize, "{$baseName}.{$data->new_version}.zip", $contentMD5, $tmpFileContents );
							Common::createDownloadableFileVersionCache( $updatePackageVersionFileName, $version );
						}

						$subkey = version_compare( $data->new_version, $checked[$file], '>' ) ? 'response' : 'no_update';

						$result[$subkey][$file] = $data;
						if ( !isset( $result[$subkey][$file]->name ) && isset( $all[$file] ) ) {
							$result[$subkey][$file]->name = $all[$file]['Name'];
						}

						$result[$subkey][$file]->package = admin_url( 'admin-ajax.php' ) . '?' . http_build_query( array(
							'action' => 'ptup_download_package',
							'type' => $type,
							'package' => $file,
							'x_ptup_authentication_token' => $authenticationToken // Note: This will be converted to a header when the download request is sent
						), null, ini_get( 'arg_separator.output' ), PHP_QUERY_RFC3986 );
					} else {
						// If this happens, the update is broken! Skip it...
					}
				}
			}

			$current = array_intersect_key( $all, array_diff_key( $checked, array_merge( $result['no_update'], $result['response'] ) ) );

			if ( count( $current ) > 0 ) {
				foreach ( $current as $file => $data ) {
					$updatePackageFileName = self::$cacheDirectory . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $file;
					$updatePackageVersionFileName = "{$updatePackageFileName}.version.php";

					$baseName = basename( explode( '/', $file )[0], '.php' );

					$version = false;
					include ( $updatePackageVersionFileName );
					if ( version_compare( $version, $data['Version'], '<' ) ) {
						$version = addslashes( $data['Version'] );

						$zipFileName = "{$baseName}.{$data['Version']}.zip";
						$tmpFile = wp_tempnam( $zipFileName );

						$zip = new \ZipArchive();
						if ( $zip->open( $tmpFile, \ZipArchive::CREATE | \ZipArchive::OVERWRITE ) !== true ) {
							// Uh oh! Skip this update for now...
							unlink( $tmpFile );
							continue;
						}

						$sourceDir = $type === 'plugin' ? WP_PLUGIN_DIR : get_theme_root();

						if ( $type === 'plugin' && strpos( $file, '/' ) === false ) {
							$zip->addFile( $sourceDir . DIRECTORY_SEPARATOR . $file, $file );
						} else {
							$path = $sourceDir . DIRECTORY_SEPARATOR . $baseName;
							$files = new \RecursiveIteratorIterator( new \RecursiveDirectoryIterator( $path ), \RecursiveIteratorIterator::LEAVES_ONLY );

							foreach ( $files as $name => $fileObject ) {
								if ( !$fileObject->isDir() ) {
									$filePath = $fileObject->getRealPath();
									$relativePath = substr( $filePath, strlen( $sourceDir ) + 1 );
									$zip->addFile( $filePath, $relativePath );
								}
							}
						}

						$zip->close();

						$tmpFileSize = filesize( $tmpFile );
						$contentMD5 = addslashes( base64_encode( md5_file( $tmpFile, true ) ) );
						$tmpFileContents = addslashes( base64_encode( file_get_contents( $tmpFile ) ) );
						unlink( $tmpFile );

						Common::createDirectoryIfNeeded( $updatePackageFileName );
						Common::createDownloadableFile( $updatePackageFileName, $tmpFileSize, $zipFileName, $contentMD5, $tmpFileContents );
						Common::createDownloadableFileVersionCache( $updatePackageVersionFileName, $version );
					}

					$subkey = version_compare( $data['Version'], $checked[$file], '>' ) ? 'response' : 'no_update';

					$result[$subkey][$file] = new \stdClass();
					$result[$subkey][$file]->slug = $baseName;
					$result[$subkey][$file]->plugin = $file;
					$result[$subkey][$file]->name = $data['Name'];
					$result[$subkey][$file]->new_version = $data['Version'];
					$result[$subkey][$file]->url = isset( $data["{ucfirst($type)}URI"] ) ? $data["{ucfirst($type)}URI"] : ( isset( $data['AuthorURI'] ) ? $data['AuthorURI'] : '' );
					$result[$subkey][$file]->package = admin_url( 'admin-ajax.php' ) . '?' . http_build_query( array(
						'action' => 'ptup_download_package',
						'type' => $type,
						'package' => $file,
						'x_ptup_authentication_token' => $authenticationToken // Note: This will be converted to a header when the download request is sent
					), null, ini_get( 'arg_separator.output' ), PHP_QUERY_RFC3986 );
				}
			}

			if ( count( $result['response'] ) === 0 ) {
				$result['response'] = new \stdClass();
			}

			if ( count( $result['no_update'] ) === 0 ) {
				$result['no_update'] = new \stdClass();
			}
		} else {
			$result = array(
				'response' => new \stdClass(),
				'no_update' => new \stdClass()
			);
		}

		wp_send_json( $result );
		exit;
	}

	public function ajaxDownloadPackageEndpoint() {
		if ( !isset( $_SERVER['HTTP_X_PTUP_AUTHENTICATION_TOKEN'] ) ) {
			status_header( 401 );
			exit;
		}

		$authenticationToken = sanitize_text_field( $_SERVER['HTTP_X_PTUP_AUTHENTICATION_TOKEN'] );

		$this->loadAuthenticationTokens();

		$allowedData = null;
		$type = sanitize_text_field( $_GET['type'] );
		switch ($type) {
			case 'plugin':
				foreach ( $this->authenticationTokens as $possibleAuthenticationToken ) {
					if ( $possibleAuthenticationToken->value === $authenticationToken ) {
						if ( $possibleAuthenticationToken->enabled ) {
							$allowedData = $possibleAuthenticationToken->selectedPlugins;
						}
						break;
					}
				}
				break;
			case 'theme':
				foreach ( $this->authenticationTokens as $possibleAuthenticationToken ) {
					if ( $possibleAuthenticationToken->value === $authenticationToken ) {
						if ( $possibleAuthenticationToken->enabled ) {
							$allowedData = $possibleAuthenticationToken->selectedThemes;
						}
						break;
					}
				}
				break;
			default:
				status_header(400);
				exit;
		}

		if ( $allowedData === null ) {
			status_header( 403 );
			exit;
		}

		if ( !isset( $_GET['package'] ) ) {
			status_header( 400 );
			exit;
		}

		$package = sanitize_text_field( $_GET['package'] );
		if ( !in_array( $package, $allowedData ) ) {
			status_header( 403 );
			exit;
		}

		include( self::$cacheDirectory . DIRECTORY_SEPARATOR . $type . DIRECTORY_SEPARATOR . $package );

		status_header( 404 );
		exit;
	}

	public function ajaxAvailablePluginsEndpoint() {
		if ( ! current_user_can( 'manage_options' ) ) {
			status_header( 404 );
			exit;
		}

		switch ( $_SERVER['REQUEST_METHOD'] ) {
			case 'GET':
				$plugins = array();
				foreach ( Common::getPlugins() as $pluginId => $pluginData ) {
					$plugin = array(
						'id' => $pluginId
					);

					foreach ( $pluginData as $key => $value ) {
						$plugin[lcfirst($key)] = is_array( $value ) ? array_map( 'html_entity_decode', $value ) : html_entity_decode( $value );
					}

					$plugins[] = $plugin;
				}

				wp_send_json( $plugins );
				break;
			default:
				status_header( 404 );
				break;
		}

		exit;
	}

	public function ajaxAvailableThemesEndpoint() {
		if ( ! current_user_can( 'manage_options' ) ) {
			status_header( 404 );
			exit;
		}

		switch ( $_SERVER['REQUEST_METHOD'] ) {
			case 'GET':
				$themes = array();

				foreach ( Common::getThemes() as $themeId => $themeData ) {
					$theme = array(
						'id' => $themeId
					);

					foreach ( $themeData as $key => $value ) {
						$theme[lcfirst($key)] = is_array( $value ) ? array_map( 'html_entity_decode', $value ) : html_entity_decode( $value );
					}

					$themes[] = $theme;
				}

				wp_send_json( $themes );
				break;
			default:
				status_header( 404 );
				break;
		}

		exit;
	}

	public function ajaxAuthenticationTokensEndpoint() {
		if ( ! current_user_can( 'manage_options' ) ) {
			status_header( 404 );
			exit;
		}

		switch ( $_SERVER['REQUEST_METHOD'] ) {
			case 'GET':
				$this->loadAuthenticationTokens();

				wp_send_json( $this->authenticationTokens );
				break;
			case 'POST':
				$authenticationTokens = file_get_contents( 'php://input' );
				if ( json_decode( $authenticationTokens ) === null ) {
					status_header( 400 );
				} else {
					update_option( $this->authenticationTokensOptionName, $authenticationTokens );
				}
				break;
			default:
				status_header( 404 );
				break;
		}

		exit;
	}

	protected function expandUpdate(&$update, $type) {
		$update = (object) $update;
	}

	protected function fetchDetails($source, $type, $errorMessages) {
		$headers = $this->recursionCheck( $source );
		if ( $headers === null ) {
			return;
		}

		$result = wp_remote_get( add_query_arg( 'type', $type, $source->updateURL ), array(
			'httpversion' => '1.1',
			'user-agent' => self::getUserAgent(),
			'headers' => $headers,
			'sslverify' => !$source->skipSSLCertificateChecks,
		) );

		if ( is_wp_error( $result ) ) {
			$errorMessages[] = sprintf( __( 'Error contacting source "%1$s" for available %2$ss: %3$s', self::getTextDomain() ), !empty( $source->label ) ? $source->label : $source->updateURL, $type, $result->get_error_message() );
		} else {
			switch ( $result['response']['code'] ) {
				case 200:
					$response = json_decode( $result['body'], true );
					if ( $response === null ) {
						$errorMessages[] = sprintf( __( 'Received invalid %1$s data from source "%2$s"', self::getTextDomain() ), $type, !empty( $source->label ) ? $source->label : $source->updateURL );
						break;
					}

					$availableUpdates = array_merge( $response['response'], $response['no_update'] );
					foreach ( $availableUpdates as $slug => &$update ) {
						$this->expandUpdate( $update, $type );
					}

					switch ($type) {
						case 'plugin':
							$source->plugins = $availableUpdates;
							break;
						case 'theme':
							$source->themes = $availableUpdates;
							break;
						default:
							break;
					}
					break;
				default:
					$errorMessages[] = sprintf( __( 'Received unexpected HTTP status while checking update source "%1$s" for %2$ss: %3$s %4$s', self::getTextDomain() ), !empty( $source->label ) ? $source->label : $source->updateURL, $type, $result['response']['code'], $result['response']['message'] );
					break;
			}
		}
	}

	protected function fetchSourceDetailsForType($type, $errorMessages) {
		$this->fetchDetails($this->source, $type, $errorMessages);
	}

	protected function fetchSourceDetails() {
		$this->loadSource();

		$pluginErrorMessages = array();
		$themeErrorMessages = array();

		$this->fetchSourceDetailsForType('plugin', $pluginErrorMessages);
		$this->fetchSourceDetailsForType('theme', $themeErrorMessages);

		if ( count( $pluginErrorMessages ) > 0 ) {
			set_transient( "{$this->underscoreTextDomain}_get_source_plugins_result", array(
				'type' => 'error',
				'message' => join( '<br>', $pluginErrorMessages )
			), HOUR_IN_SECONDS );
		}

		if ( count( $themeErrorMessages ) > 0 ) {
			set_transient( "{$this->underscoreTextDomain}_get_source_themes_result", array(
				'type' => 'error',
				'message' => join( '<br>', $themeErrorMessages )
			), HOUR_IN_SECONDS );
		}

		return $this->source;
	}

	protected function saveSource($source) {
		update_option( $this->sourceOptionName, $source );
	}

	public function ajaxSourcesEndpoint() {
		if ( ! current_user_can( 'manage_options' ) ) {
			status_header( 404 );
			exit;
		}

		switch ( $_SERVER['REQUEST_METHOD'] ) {
			case 'GET':
				wp_send_json( $this->fetchSourceDetails() );
				break;
			case 'POST':
				$source = file_get_contents( 'php://input' );
				$parsedSource = json_decode( $source );
				if ( $parsedSource === null || ( count( $parsedSource->selectedPlugins ) + count( $parsedSource->selectedThemes ) ) > 1 ) {
					status_header( 400 );
				} else {
					$this->saveSource( $source );
					wp_send_json( $this->fetchSourceDetails() );
				}
				break;
			default:
				status_header( 404 );
				break;
		}

		exit;
	}

	protected function recursionCheck($source) {
		$recursionCheck = null;

		if ( isset( $_SERVER['HTTP_X_PTUP_RECURSION_CHECK'] ) ) {
			$recursionCheck = array_map( 'trim', explode( ',', $_SERVER['HTTP_X_PTUP_RECURSION_CHECK'] ) );
			if ( in_array( $source->authenticationToken, $recursionCheck ) ) {
				return null;
			}
		} else {
			$recursionCheck = array();
		}

		$recursionCheck[] = $source->authenticationToken;

		return array(
			'Content-Type' => 'application/json',
			'X-PTUP-Authentication-Token' => $source->authenticationToken,
			'X-PTUP-Recursion-Check' => join( ',', $recursionCheck )
		);
	}
}

PluginAndThemeUpdateProxyBase::init();
