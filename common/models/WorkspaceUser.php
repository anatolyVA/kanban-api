<?php

namespace app\common\models;

use yii\db\ActiveRecord;

class WorkspaceUser extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%workspace_user}}';
    }

}
