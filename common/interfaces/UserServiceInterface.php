<?php

namespace app\common\interfaces;

interface UserServiceInterface
{
    public function getAll();
    public function getOne($id);
    public function getProfile();
    public function getTasks();
}
