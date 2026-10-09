<?php

namespace App\Actions\Masters;

use App\Http\Requests\BatchRequest;
use App\Models\Batch;
use Illuminate\Support\Arr;

class SaveBatch
{
    public function execute(array $data): Batch
    {
        $batch = isset($data['id']) && $data['id']
            ? Batch::findOrFail($data['id'])
            : new Batch;

        $batch->fill(Arr::except($data, ['id']));
        $batch->save();

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
