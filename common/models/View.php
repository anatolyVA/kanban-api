<?php

namespace app\common\models;

use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;

/**
 * @property string $id
 * @property string $title
 * @property string $project_id
 *
 */
class View extends ActiveRecord
{
    public static function tableName(): string
    {
        return '{{%view}}';
    }

    public function rules(): array
    {
        return [
            [['title', 'project_id'], 'required'],
            ['title', 'string', 'min' => 3, 'max' => 32]
        ];
    }

    public static function findById(string $id): ?View
    {
        return self::findOne($id);
    }

    public function getProject(): ActiveQuery
    {
        return $this->hasOne(Project::class, ['id' => 'project_id']);
    }
    public function getCollections(): ActiveQuery
    {
        return $this->hasMany(Collection::class, ['view_id' => 'id']);
    }

    /**
     * @throws InvalidConfigException
     */
    public function getMembers(): ActiveQuery
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])
            ->viaTable('view_user', ['view_id' => 'id']);
    }

    public function isMember($uid): bool
    {
        $members = $this->members;
        foreach ($members as $member) {
            if ($member->id == $uid) {
                return true;
            }
        }
        return false;
    }
}