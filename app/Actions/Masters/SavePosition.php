<?php

namespace App\Actions\Masters;

use App\Http\Requests\PositionRequest;
use App\Models\Position;
use Illuminate\Support\Arr;

class SavePosition
{
    public function execute(array $data): Position
    {
        $isNew = ! (isset($data['id']) && $data['id']);
        $position = $isNew
            ? new Position
            : Position::findOrFail($data['id']);

        $old = $isNew ? [] : $position->getAttributes();
        $position->fill(Arr::except($data, ['id']));
        $position->save();

        app(\App\Services\AuditLogger::class)->log(
            $isNew ? 'created' : 'updated',
            $position,
            $old,
            $position->getAttributes(),
        );

        return $position;
    }

    /**
     * @return array<string, mixed>
     */
    public static function validated(array $payload): array
    {
        $request = PositionRequest::create('/', 'POST', $payload);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        return $request->validated();
    }
}
