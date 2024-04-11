<?php

namespace app\common\interfaces;

interface WorkspaceInviteServiceInterface
{
    public function accept(string $workspace_id);
    public function decline(string $workspace_id);
    public function getAll(): array;
}