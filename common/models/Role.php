<?php

namespace app\common\models;

use yii\db\ActiveRecord;

class Role extends ActiveRecord
{
    public static function tableName(): string
    {
        return "{{%role}}";
    }

    public static function findIdentity($title): Role|null
    {
        return self::findOne($title);
    }

}