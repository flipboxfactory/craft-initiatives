<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/initiatives/blob/master/LICENSE.md
 * @link       https://github.com/flipboxfactory/initiatives
 */

namespace flipbox\craft\initiatives\migrations;

use craft\db\Migration as InstallMigration;
use craft\records\Element as ElementRecord;
use craft\records\User as UserRecord;
use flipbox\craft\initiatives\records\Initiative as InitiativeRecord;
use flipbox\craft\initiatives\records\UserAssociation as TargetRecord;

class Install extends InstallMigration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->createTables();
        $this->createIndexes();
        $this->addForeignKeys();

        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        // Delete tables
        $this->dropTableIfExists(TargetRecord::tableName());
        $this->dropTableIfExists(InitiativeRecord::tableName());

        return true;
    }

    /**
     * Creates the tables.
     *
     * @return void
     */
    protected function createTables()
    {
        $this->createTable(InitiativeRecord::tableName(), [
            'id' => $this->primaryKey(),
            'settings' => $this->text(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(TargetRecord::tableName(), [
            'elementId' => $this->integer()->notNull(),
            'userId' => $this->integer()->notNull(),
            'status' => $this->enum('status', [
                TargetRecord::STATUS_ACTIVE,
                TargetRecord::STATUS_ERROR,
                TargetRecord::STATUS_COMPLETE
            ])->defaultValue(TargetRecord::STATUS_ACTIVE)->notNull(),
            'sortOrder' => $this->smallInteger()->unsigned(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'dateCreated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);
    }

    /**
     * Creates the indexes.
     *
     * @return void
     */
    protected function createIndexes()
    {
        // TargetRecord
        $this->addPrimaryKey(
            $this->db->getPrimaryKeyName(TargetRecord::tableName(), ['elementId', 'userId']),
            TargetRecord::tableName(),
            ['elementId', 'userId']
        );
        $this->createIndex(
            $this->db->getIndexName(TargetRecord::tableName(), 'elementId', false),
            TargetRecord::tableName(),
            'elementId',
            false
        );
        $this->createIndex(
            $this->db->getIndexName(TargetRecord::tableName(), 'userId', false),
            TargetRecord::tableName(),
            'userId',
            false
        );
    }

    /**
     * Adds the foreign keys.
     *
     * @return void
     */
    protected function addForeignKeys()
    {
        // TargetRecord
        $this->addForeignKey(
            $this->db->getForeignKeyName(InitiativeRecord::tableName(), 'id'),
            InitiativeRecord::tableName(),
            'id',
            ElementRecord::tableName(),
            'id',
            'CASCADE'
        );

        // TargetRecord
        $this->addForeignKey(
            $this->db->getForeignKeyName(TargetRecord::tableName(), 'elementId'),
            TargetRecord::tableName(),
            'elementId',
            InitiativeRecord::tableName(),
            'id',
            'CASCADE'
        );
        $this->addForeignKey(
            $this->db->getForeignKeyName(TargetRecord::tableName(), 'userId'),
            TargetRecord::tableName(),
            'userId',
            UserRecord::tableName(),
            'id',
            'CASCADE'
        );
    }
}
