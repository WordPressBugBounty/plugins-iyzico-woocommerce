<?php

namespace Iyzico\IyzipayWoocommerce\Database;

use Exception;
use Iyzico\IyzipayWoocommerce\Common\Helpers\Logger;

class DatabaseManager {
	private static $wpdb;
	private static Logger $logger;

	/**
	 * @param $wpdb
	 * @param Logger $logger
	 *
	 * @return void
	 */
	public static function init( $wpdb, Logger $logger ): void {
		self::$wpdb   = $wpdb;
		self::$logger = $logger;
	}

	private static function ensureInitialized(): void {
		if ( ! isset( self::$wpdb ) || self::$wpdb === null ) {
			global $wpdb;
			self::$wpdb = $wpdb;
		}
		if ( ! isset( self::$logger ) ) {
			self::$logger = new Logger();
		}
	}

	public static function createTables(): void {
		self::ensureInitialized();
		try {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

			$tables = self::getTableDefinitions();
			foreach ( $tables as $table_name => $columns ) {
				$sql = self::generateCreateTableSQL( $table_name, $columns );
				dbDelta( $sql );
			}

			self::$logger->info( 'Tables created successfully' );
		} catch ( Exception $e ) {
			self::$logger->error( 'Error creating tables: ' . $e->getMessage() );
		}
	}

	public static function dropTables(): void {
		self::ensureInitialized();
		try {
			$tables = self::getTableDefinitions();
			foreach ( $tables as $table_name => $columns ) {
				$sql = "DROP TABLE IF EXISTS " . self::$wpdb->prefix . $table_name . ";";
				self::$wpdb->query( $sql );
			}

			self::$logger->info( 'Tables dropped successfully' );
		} catch ( Exception $e ) {
			self::$logger->error( 'Error dropping tables: ' . $e->getMessage() );
		}
	}

	/**
	 * @throws Exception
	 */
	private static function getTableDefinitions(): array {
		$json_file = PLUGIN_PATH . "/includes/common/database/table_definitions.json";
		if ( ! file_exists( $json_file ) ) {
			self::$logger->error( 'Table definitions JSON file not found' );
			throw new Exception( "Table definitions file not found: $json_file" );
		}
		$json_content = file_get_contents( $json_file );
		$tables       = json_decode( $json_content, true );
		if ( json_last_error() !== JSON_ERROR_NONE ) {
			self::$logger->error( 'Error decoding table definitions JSON: ' . json_last_error_msg() );
			throw new Exception( "Error decoding table definitions JSON: " . json_last_error_msg() );
		}

		return $tables;
	}

	private static function generateCreateTableSQL( string $table_name, array $columns ): string {
		$sql        = "CREATE TABLE " . self::$wpdb->prefix . $table_name . " (";
		$primaryKey = '';

		foreach ( $columns as $column_name => $definition ) {
			if ( $column_name === 'PRIMARY KEY' ) {
				$primaryKey = "PRIMARY KEY ({$definition})";
			} else {
				$sql .= "`$column_name` $definition,";
			}
		}

		if ( $primaryKey ) {
			$sql .= $primaryKey;
		} else {
			$sql = rtrim( $sql, ',' );
		}

		$sql .= ") " . self::$wpdb->get_charset_collate() . ";";

		return $sql;
	}

	public function findUserCardKey( $customerId, $apiKey ) {
		$tableName = self::$wpdb->prefix . 'iyzico_card';
		$fieldName = 'card_user_key';

		$sql = self::$wpdb->prepare( "
			SELECT $fieldName
			FROM $tableName
			WHERE customer_id = %d AND api_key = %s
			ORDER BY iyzico_card_id DESC LIMIT 1;
		", $customerId, $apiKey );

		$result = self::$wpdb->get_col( $sql );

		return $result[0] ?? null;
	}

	public function saveUserCardKey( $customerId, $cardUserKey, $apiKey ) {
		$tableName = self::$wpdb->prefix . 'iyzico_card';

		return self::$wpdb->insert(
			$tableName,
			[
				'customer_id'   => $customerId,
				'card_user_key' => $cardUserKey,
				'api_key'       => $apiKey
			],
			[ '%d', '%s', '%s' ]
		);
	}


}