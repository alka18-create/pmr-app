<?php

namespace App\Actions\Masters;

use App\Http\Requests\BatchRequest;
use App\Models\Batch;
use Illuminate\Support\Arr;

class SaveBatch
{
    public function execute(array $data): Batch
    {
        $isNew = ! (isset($data['id']) && $data['id']);
        $batch = $isNew
            ? new Batch
            : Batch::findOrFail($data['id']);

        $old = $isNew ? [] : $batch->getAttributes();
        $batch->fill(Arr::except($data, ['id']));
        $batch->save();

        app(\App\Services\AuditLogger::class)->log(
            $isNew ? 'created' : 'updated',
            $batch,
            $old,
            $batch->getAttributes(),
        );

        return $batch;
    }

    /**
     * @return array<string, mixed>
     */
    public static function validated(array $payload): array
    {
        $request = BatchRequest::create('/', 'POST', $payload);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        return $request->validated();
    }
}
