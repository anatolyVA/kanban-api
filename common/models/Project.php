<?php

namespace app\common\models;

use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $id
 * @property string $title
 * @property string $workspace_id
 * **/
class Project extends ActiveRecord
{

    public static function tableName(): string
    {
        return '{{%project}}';
    }

    public static function findById(string $id): ?Project
    {
        return self::findOne($id);
    }

    public function rules(): array
    {
        return [
            [['title', 'workspace_id'], 'required'],
            ['title', 'string', 'min' => 3, 'max' => 32]
        ];
    }

    public function getViews(): ActiveQuery
    {
        return $this->hasMany(View::class, ['project_id' => 'id']);
    }

    public function getWorkspace(): ActiveQuery
    {
        return $this->hasOne(Workspace::class, ['id' => 'workspace_id']);
    }
}
