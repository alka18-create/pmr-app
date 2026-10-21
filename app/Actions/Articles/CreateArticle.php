<?php

namespace App\Actions\Articles;

use App\Models\Article;
use App\Services\AuditLogger;
use App\Services\ContentSanitizer;

class CreateArticle
{
    public function execute(array $data, ?\Illuminate\Http\UploadedFile $cover = null): Article
    {
        if (array_key_exists('content', $data)) {
            $data['content'] = ContentSanitizer::sanitize($data['content']);
        }

        $article = Article::create($data);

        if ($cover) {
            $path = $cover->store('articles/'.$article->id, 'public');
            $article->update(['cover_path' => $path]);
        }

        app(AuditLogger::class)->log(
            'created',
            $article,
            [],
            $article->getAttributes(),
        );

        return $article;
    }
}
