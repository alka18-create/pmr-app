<?php

namespace App\Http\Resources;

use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Resources\MissingValue;

/**
 * Bentuk aman data anggota untuk halaman/endpoint publik.
 * View publik WAJIB lewat resource ini / Member::toPublicArray().
 *
 * @mixin Member
 */
class MemberPublicResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $public = $this->resource->toPublicArray();

        return array_filter(
            $public,
            fn ($value) => ! $value instanceof MissingValue
        );
    }
}
