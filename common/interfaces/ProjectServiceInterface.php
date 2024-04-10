<?php

namespace app\common\interfaces;

interface ProjectServiceInterface
{
    public function create(string $title, string $current_user_id);
    public function delete(string $id, string $current_user_id);
    public function rename(string $project_id, string $current_user_id, string $title);

}