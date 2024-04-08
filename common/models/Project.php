<?php

namespace app\common\models;

use yii\db\ActiveRecord;

class Project extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%project}}';
    }
    public function rules(): array
    {
        return [
            [['title', 'workspace_id'], 'required'],
            ['title', 'string', 'min' => 3, 'max' => 32]
        ];
    }
}
