<?php

namespace App\Actions\Masters;

use App\Http\Requests\SchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Support\Arr;

class SaveSchoolClass
{
    public function execute(array $data): SchoolClass
    {
        $class = isset($data['id']) && $data['id']
            ? SchoolClass::findOrFail($data['id'])
            : new SchoolClass;

        $class->fill(Arr::except($data, ['id']));
        $class->save();

        return $class;
    }

    /**
     * @return array<string, mixed>
     */
    public static function validated(array $payload): array
    {
        $request = SchoolClassRequest::create('/', 'POST', $payload);
        $request->setContainer(app());
        $request->setRedirector(app('redirect'));
        $request->validateResolved();

        return $request->validated();
    }
}
