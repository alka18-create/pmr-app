<?php

namespace Tests\Feature;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_dibuat_dengan_slug_otomatis(): void
    {
        $member = Member::factory()->create([
            'full_name' => 'Ayu Lestari',
            'slug' => null,
        ]);

        $this->assertSame('ayu-lestari', $member->slug);
    }

    public function test_slug_unik_bila_bentrok(): void
    {
        Member::factory()->create(['full_name' => 'Budi', 'slug' => 'budi']);
        $second = Member::factory()->create(['full_name' => 'Budi', 'slug' => null]);

        $this->assertSame('budi-2', $second->slug);
    }

    public function test_to_public_array_hanya_mengandung_field_publik(): void
    {
        $member = Member::factory()->create()->load(['batch', 'schoolClass', 'position']);
        $public = $member->toPublicArray();

        $expectedKeys = [
            'id',
            'full_name',
            'slug',
            'gender',
            'photo_path',
            'status',
            'joined_at',
            'batch',
            'school_class',
            'position',
        ];

        $this->assertEqualsCanonicalizing($expectedKeys, array_keys($public));

        $internal = Member::internalFields();
        foreach ($internal as $field) {
            $this->assertArrayNotHasKey($field, $public);
        }

        $this->assertStringNotContainsString($member->nisn, json_encode($public));
        $this->assertStringNotContainsString($member->address, json_encode($public));
        $this->assertStringNotContainsString($member->phone, json_encode($public));
    }

    public function test_to_public_array_bisa_diresolvi_tanpa_relasi(): void
    {
        $member = Member::factory()->create();
        $public = $member->toPublicArray();

        $this->assertArrayHasKey('full_name', $public);
        $this->assertArrayNotHasKey('batch', $public);
    }

    public function test_factory_menghasilkan_data_valid(): void
    {
        $member = Member::factory()->create();

        $this->assertInstanceOf(Gender::class, $member->gender);
        $this->assertSame(MemberStatus::Aktif, $member->status);
        $this->assertNotNull($member->batch_id);
        $this->assertNotNull($member->class_id);
        $this->assertNotNull($member->joined_at);
    }

    public function test_member_soft_delete(): void
    {
        $member = Member::factory()->create();
        $id = $member->id;
        $member->delete();

        $this->assertSoftDeleted('members', ['id' => $id]);
        $this->assertNull(Member::find($id));
        $this->assertNotNull(Member::withTrashed()->find($id));
    }
}
