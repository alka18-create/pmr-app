<?php

namespace App\Actions\Masters;

use App\Http\Requests\SchoolClassRequest;
use App\Models\SchoolClass;
use Illuminate\Support\Arr;

class SaveSchoolClass
{
    public function execute(array $data): SchoolClass
    {
        $isNew = ! (isset($data['id']) && $data['id']);
        $class = $isNew
            ? new SchoolClass
            : SchoolClass::findOrFail($data['id']);

        $old = $isNew ? [] : $class->getAttributes();
        $class->fill(Arr::except($data, ['id']));
        $class->save();

        app(\App\Services\AuditLogger::class)->log(
            $isNew ? 'created' : 'updated',
            $class,
            $old,
            $class->getAttributes(),
        );

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
