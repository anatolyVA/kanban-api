<?php

namespace app\common\models;

use yii\db\ActiveRecord;

class ProjectUser extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%project_user}}';
    }

}
