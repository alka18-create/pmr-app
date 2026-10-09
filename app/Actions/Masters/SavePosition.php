<?php

namespace App\Actions\Masters;

use App\Http\Requests\PositionRequest;
use App\Models\Position;
use Illuminate\Support\Arr;

class SavePosition
{
    public function execute(array $data): Position
    {
        $position = isset($data['id']) && $data['id']
            ? Position::findOrFail($data['id'])
            : new Position;

        $position->fill(Arr::except($data, ['id']));
        $position->save();

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
