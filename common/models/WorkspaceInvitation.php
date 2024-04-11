<?php

namespace app\common\models;

use yii\db\ActiveRecord;

class WorkspaceInvitation extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%workspace_invitation}}';
    }
    public function rules(): array
    {
        return [
            [['workspace_id', 'user_id'], 'required']
        ];
    }
}
