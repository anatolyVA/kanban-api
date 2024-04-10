<?php

namespace app\common\models;

use yii\db\ActiveRecord;

class ViewUser extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%view_user}}';
    }

}