<?php

namespace App\Actions\Articles;

use App\Models\Article;
use App\Services\AuditLogger;
use App\Services\ContentSanitizer;

class UpdateArticle
{
    public function execute(Article $article, array $data): Article
    {
        if (array_key_exists('content', $data)) {
            $data['content'] = ContentSanitizer::sanitize($data['content']);
        }

        $old = $article->getAttributes();
        $article->update($data);

        app(AuditLogger::class)->log(
            'updated',
            $article,
            $old,
            $article->getChanges(),
        );

        return $article;
    }
}
