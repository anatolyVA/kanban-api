<?php

namespace app\traits;
use yii\base\NotSupportedException;
use yii\db\ColumnSchemaBuilder;
use yii\db\Connection;

trait UuidTypeTrait
{
    /**
     * @return Connection the database connection to be used for schema building.
     */
    abstract protected function getDb(): Connection;

    /**
     * Creates a uuid column.
     * @return ColumnSchemaBuilder the column instance which can be further customized.
     * @throws NotSupportedException
     */
    public function uuid(): ColumnSchemaBuilder
    {
        return $this->getDb()->getSchema()->createColumnSchemaBuilder('uuid');
    }
}