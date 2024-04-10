<?php

namespace app\common\models;

use yii\db\ActiveRecord;

class Comment extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%comment}}';
    }
}