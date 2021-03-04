<?php

namespace AoDTechnologies\PluginAndThemeUpdateProxy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

class Common {
    private static $hasRequires = false;
    private static $hasWPGetThemes = false;

    public static function init() {
        self::$hasRequires = version_compare( get_bloginfo( 'version' ), '5.4.0', '>=' );
        self::$hasWPGetThemes = version_compare( get_bloginfo( 'version' ), '3.4.0', '>=' );
    }

    public static function removeDirectory( $file ) {
        if ( !is_dir( $file ) ) {
            @unlink( $file );
            return;
        }

        try {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator( $file, \FilesystemIterator::SKIP_DOTS ),
                \RecursiveIteratorIterator::CHILD_FIRST
            );
        } catch ( \UnexpectedValueException $e ) {
            return;
        }

        foreach ( $iterator as $item ) {
            if ( $item->isDir() ) {
                @rmdir( $item );

                continue;
            }

            @unlink( $item );
        }

        @rmdir( $file );
    }

    public static function createDirectoryIfNeeded($fileName) {
        $parts = explode( DIRECTORY_SEPARATOR, $fileName );
        array_pop( $parts );
        $dir = implode( DIRECTORY_SEPARATOR, $parts );

        if( !is_dir( $dir ) ) {
            mkdir( $dir, 0777, true );
        }
    }

    public static function createDownloadableFile($fileName, $fileSize, $downloadedFileName, $md5, $base64EncodedContents) {
        file_put_contents( $fileName, <<<EOT
<?php

namespace AoDTechnologies\PluginAndThemeUpdateProxy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    http_response_code( 404 );
    exit;
}

header( 'content-type: application/octet-stream' );
header( 'content-length: $fileSize' );
header( 'content-disposition: attachment; filename=$downloadedFileName' );
header( 'content-md5: $md5' );

echo base64_decode( '$base64EncodedContents' );

exit;

EOT
        );
    }

    public static function createDownloadableFileVersionCache($fileName, $version) {
        file_put_contents( $fileName, <<<EOT
<?php

namespace AoDTechnologies\PluginAndThemeUpdateProxy;

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    http_response_code( 404 );
    exit;
}

\$version = '$version';

EOT
        );
    }

    public static function getPlugins() {
        return get_plugins();
    }

    public static function getThemes() {
        if ( self::$hasWPGetThemes ) {
            return array_map( array( __CLASS__, 'mapThemeObjects' ), wp_get_themes( array(
                "errors" => null
            ) ) );
        }

        return get_themes();
    }

    public static function convertToObject($misc) {
        return (object) $misc;
    }

    private static function mapThemeObjects($theme) {
        $result = array(
            'Name' => $theme->Name,
            'ThemeURI' => $theme->ThemeURI,
            'Description' => $theme->Description,
            'Author' => $theme->Author,
            'AuthorURI' => $theme->AuthorURI,
            'Version' => $theme->Version,
            'Template' => $theme->Template,
            'Status' => $theme->Status,
            'Tags' => $theme->Tags,
            'TextDomain' => $theme->TextDomain,
            'DomainPath' => $theme->DomainPath
        );

        if ( self::$hasRequires ) {
            $result['RequiresWP'] = $theme->RequiresWP;
            $result['RequiresPHP'] = $theme->RequiresPHP;
        }

        return $result;
    }
}

Common::init();
