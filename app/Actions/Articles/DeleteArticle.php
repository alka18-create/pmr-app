<?php

namespace App\Actions\Articles;

use App\Models\Article;
use App\Services\AuditLogger;

class DeleteArticle
{
    public function execute(Article $article): void
    {
        $old = $article->getAttributes();
        $article->delete();

        app(AuditLogger::class)->log('deleted', $article, $old, []);
    }
}
