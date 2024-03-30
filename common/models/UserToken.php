<?php

namespace app\common\models;

use yii\db\ActiveRecord;

/**
 * @property string user_id
 * @property string refresh_token
 * @property string user_ip
 * @property string expiration_date
 */
class UserToken extends ActiveRecord
{
    public static function tableName(): string
    {
        return "{{%user_token}}";
    }

    public function rules(): array
    {
        return [
            [['user_id', 'refresh_token', 'user_ip', 'expiration_date'], 'required'],
        ];
    }
}
