<?php

namespace app\traits;
use yii\db\ColumnSchemaBuilder;
use yii\db\Connection;

trait UuidTypeTrait
{
    /**
     * @return Connection the database connection to be used for schema building.
     */
    abstract protected function getDb();

    /**
     * Creates a uuid column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     */
    public function uuid(): ColumnSchemaBuilder
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid');
    }
}