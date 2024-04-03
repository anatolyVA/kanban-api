<?php

namespace app\common\models;

use yii\db\ActiveRecord;

/**
 * @property int $id
 * @property int $project_id
 * @property string $title
 * @property string $status
 * @property string $performer_id
 * */
class Task extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%task}}';
    }
}
