<?php

namespace app\common\models;

use yii\db\ActiveRecord;

class Task extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%task}}';
    }
}
