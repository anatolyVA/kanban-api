<?php

namespace app\common\interfaces;

interface ProjectServiceInterface
{
    public function create(string $title);
    public function delete(int $project_id);
    public function update(int $project_id, string $title);
    public function invite(int $project_id, string|array $user_id);
    public function exclude(int $project_id, string|array $user_id);
    public function exit(int $project_id);
}