<?php

namespace app\common\interfaces;

interface ProjectServiceInterface
{
    public function create(string $title);
    public function delete(int $project_id);
    public function rename(int $project_id, string $title);
    public function invite(int $project_id, array $user_ids);
    public function exclude(int $project_id, array $user_ids);
    public function exit(int $project_id);
}