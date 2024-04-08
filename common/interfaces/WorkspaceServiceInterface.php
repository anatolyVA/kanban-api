<?php

namespace app\common\interfaces;

interface WorkspaceServiceInterface
{
    public function create(string $title, string $current_user_id);
    public function delete(string $id, string $current_user_id);
    public function rename(string $workspace_id, string $current_user_id, string $title);
    public function invite(string $workspace_id, string $current_user_id, array $user_ids);
    public function exclude(string $workspace_id, string $current_user_id, array $user_ids);
    public function exit(string $workspace_id, string $current_user_id);
}