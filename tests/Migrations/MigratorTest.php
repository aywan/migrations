<?php

/**
 * Spiral Framework.
 *
 * @license   MIT
 * @author    Anton Titov (Wolfy-J)
 */

declare(strict_types=1);

namespace Spiral\Migrations\Tests;

use Spiral\Migrations\Capsule;
use Spiral\Migrations\State;

abstract class MigratorTest extends BaseTest
{
    public function testConfigure(): void
    {
        $this->assertFalse($this->migrator->isConfigured());

        $this->migrator->configure();
        $this->assertTrue($this->db->hasTable('migrations'));
    }

    //no errors expected
    public function testConfigureTwice(): void
    {
        $this->assertFalse($this->migrator->isConfigured());

        $this->migrator->configure();
        $this->assertTrue($this->db->hasTable('migrations'));

        $this->migrator->configure();
    }

    public function testGetEmptyMigrations(): void
    {
        $this->migrator->configure();
        $this->assertSame([], $this->migrator->getMigrations());
    }

    public function testRepository(): void
    {
        $this->assertSame($this->repository, $this->migrator->getRepository());
    }

    public function testRunUnconfigured(): void
    {
        $this->expectException(\Spiral\Migrations\Exception\MigrationException::class);
        $this->expectExceptionMessage("Unable to run migration, Migrator not configured");

        $this->migrator->run();
    }

    public function testRollbackUnconfigured(): void
    {
        $this->expectException(\Spiral\Migrations\Exception\MigrationException::class);
        $this->expectExceptionMessage("Unable to run migration, Migrator not configured");

        $this->migrator->rollback();
    }

    public function testCapsule(): void
    {
        $capsule = new Capsule($this->db);

        $s = $this->schema('test');
        $s->primary('id');
        $s->save();

        $this->assertTrue($capsule->getTable('test')->exists());
    }

    public function testCapsuleException(): void
    {
        $this->expectException(\Spiral\Migrations\Exception\CapsuleException::class);
        $this->expectExceptionMessageMatches("/Migration operation expected to be an instance of `.+`, `.+` given/");

        $capsule = new Capsule($this->db);

        $capsule->execute([
            $this
        ]);
    }

    public function testNoState(): void
    {
        $this->expectException(\Spiral\Migrations\Exception\MigrationException::class);
        $this->expectExceptionMessage("Unable to get migration state, no state are set");

        $x = new TestMigration();
        $x->up();
    }

    public function testNoCapsule(): void
    {
        $this->expectException(\Spiral\Migrations\Exception\MigrationException::class);
        $this->expectExceptionMessage("Unable to get table blueprint, no capsule are set");

        $x = new TestMigration();
        $x->getTable();
    }

    public function testNoCapsule2(): void
    {
        $this->expectException(\Spiral\Migrations\Exception\MigrationException::class);
        $this->expectExceptionMessage("Unable to get database, no capsule are set");

        $x = new TestMigration();
        $x->down();
    }

    public function testDatabase(): void
    {
        $x = new TestMigration();
        $this->assertSame($this->db, $x->withCapsule(new Capsule($this->db))->down());
    }

    public function testState(): void
    {
        $x = new TestMigration();

        $s = new State('name', new \DateTime());
        $this->assertSame($s, $x->withState($s)->up());
    }
}
