<?php

namespace app\common\models;

use yii\db\ActiveRecord;

class Status extends ActiveRecord
{
    public static function tableName(): string
    {
        return "{{%status}}";
    }
}
