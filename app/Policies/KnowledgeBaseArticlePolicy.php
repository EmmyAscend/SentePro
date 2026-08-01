<?php

namespace App\Policies;

use App\Models\KnowledgeBaseArticle;
use App\Models\User;

class KnowledgeBaseArticlePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Defense in depth on a direct URL guess at a draft — super admins bypass
     * this entirely via the Gate::before bypass, same as everywhere else.
     */
    public function view(User $user, KnowledgeBaseArticle $article): bool
    {
        return $article->status === 'published';
    }

    /**
     * Authoring articles is super-admin only, granted via the Gate::before
     * bypass — same always-false-in-body pattern as SettlementMethodPolicy.
     */
    public function manage(User $user): bool
    {
        return false;
    }
}
