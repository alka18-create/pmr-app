<?php

namespace App\Actions\Articles;

use App\Models\Article;
use App\Services\AuditLogger;
use App\Services\ContentSanitizer;

class CreateArticle
{
    public function execute(array $data): Article
    {
        if (array_key_exists('content', $data)) {
            $data['content'] = ContentSanitizer::sanitize($data['content']);
        }

        $article = Article::create($data);

        app(AuditLogger::class)->log(
            'created',
            $article,
            [],
            $article->getAttributes(),
        );

        return $article;
    }
}
