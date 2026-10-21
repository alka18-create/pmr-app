<?php

namespace App\Actions\Programs;

use App\Models\Program;
use App\Services\AuditLogger;

class SaveProgram
{
    /**
     * Buat program baru, atau perbarui bila $program diberikan.
     *
     * @param  array<string, mixed>  $data
     */
    public function execute(array $data, ?Program $program = null, ?int $ownerId = null): Program
    {
        if ($program) {
            $old = $program->getAttributes();
            $program->update($data);

            app(AuditLogger::class)->log('updated', $program, $old, $program->getAttributes());

            return $program;
        }

        $program = Program::create($data + ['owner_id' => $ownerId]);

        app(AuditLogger::class)->log('created', $program, [], $program->getAttributes());

        return $program;
    }
}
