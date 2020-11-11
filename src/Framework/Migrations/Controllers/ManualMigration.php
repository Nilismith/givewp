<?php

namespace Give\Framework\Migrations\Controllers;

use Exception;
use Give\Framework\Migrations\Actions\ClearCompletedUpgrade;
use Give\Framework\Migrations\Actions\ManuallyRunMigration;
use Give\Framework\Migrations\Contracts\Migration;
use Give\Framework\Migrations\MigrationsRegister;
use Give_Admin_Settings;

/**
 * Class ManualMigration
 *
 * Handles and admin request to manually trigger migrations
 *
 * @since 2.9.2
 */
class ManualMigration {
	/**
	 * @var MigrationsRegister
	 */
	private $migrationsRegister;

	/**
	 * ManualMigration constructor.
	 *
	 * @param  MigrationsRegister  $migrationsRegister
	 *
	 * @since 2.9.2
	 */
	public function __construct( MigrationsRegister $migrationsRegister ) {
		$this->migrationsRegister = $migrationsRegister;
	}

	/**
	 * @since 2.9.2
	 */
	public function __invoke() {
		if ( ! empty( $_GET['give-run-migration'] ) ) {
			$migrationToRun = $_GET['give-run-migration'];
		}

		if ( ! empty( $_GET['give-clear-update'] ) ) {
			$migrationToClear = $_GET['give-clear-update'];
		}

		$hasMigration = isset( $migrationToRun ) || isset( $migrationToClear );

		if ( $hasMigration && ! current_user_can( 'manage_options' ) ) {
			give()->notices->register_notice(
				[
					'id'          => 'invalid-migration-permissions',
					'description' => 'You do not have the permissions to manually run or clear migrations',
				]
			);

			return;
		}

		if ( isset( $migrationToRun ) ) {
			$this->runMigration( $migrationToRun );
		}

		if ( isset( $migrationToClear ) ) {
			$this->clearMigration( $migrationToClear );
		}
	}

	/**
	 * Runs the given automatic migration
	 *
	 * @since 2.9.2
	 *
	 * @param string $migrationId
	 */
	private function runMigration( $migrationId ) {
		if ( ! $this->migrationsRegister->hasMigration( $migrationId ) ) {
			give()->notices->register_notice(
				[
					'id'          => 'invalid-migration-id',
					'description' => "There is no migration with the ID: {$migrationId}",
				]
			);

			return;
		}

		/** @var Migration $migration */
		$migration = give( $this->migrationsRegister->getMigration( $migrationId ) );

		/** @var ManuallyRunMigration $manualRunner */
		$manualRunner = give( ManuallyRunMigration::class );

		$manualRunner( $migration );

		give()->notices->register_notice(
			[
				'id'          => 'automatic-migration-run',
				'description' => "The {$migrationId} migration was manually triggered",
			]
		);
	}

	/**
	 * Clears the manual migration so it may be run again
	 *
	 * @since 2.9.2
	 *
	 * @param string $migrationToClear
	 */
	private function clearMigration( $migrationToClear ) {
		/** @var ClearCompletedUpgrade $clearUpgrade */
		$clearUpgrade = give( ClearCompletedUpgrade::class );

		try {
			$clearUpgrade( $migrationToClear );
		} catch ( Exception $exception ) {
			give()->notices->register_notice(
				[
					'id'          => 'clear-migration-failed',
					'description' => "Unable to reset migration. Error: {$exception->getMessage()}",
				]
			);
		}

		give()->notices->register_notice(
			[
				'id'          => 'automatic-migration-cleared',
				'description' => "The {$migrationToClear} update was cleared and may be run again.",
			]
		);
	}
}
