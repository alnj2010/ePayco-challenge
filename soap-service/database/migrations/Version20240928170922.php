<?php

declare(strict_types=1);

namespace Database\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use LaravelDoctrine\Migrations\Schema\Table;
use LaravelDoctrine\Migrations\Schema\Builder;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240928170922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        (new Builder($schema))->create('clients', function (Table $table) {
            $table->increments('id');
            $table->string('document', 20);
            $table->string('email', 50);
            $table->string('name');
            $table->string('phone');
            $table->float('balance');

            $table->unique('email');
            $table->unique('document');
        });
    }

    public function down(Schema $schema): void
    {
        (new Builder($schema))->drop('clients');

    }
}
