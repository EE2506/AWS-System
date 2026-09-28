<?php

namespace App\Policies;

use App\Models\User;

class AnalyticsPolicy
{
    public function viewAnalytics(User $user): bool
    {
        return $user->is_active;
    }
}
