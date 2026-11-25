<?php

namespace Tests\Feature;

use App\Models\QuranBookmark;
use App\Models\QuranKhatamProgram;
use App\Models\Surah;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuranWritesTest extends TestCase
{
    use RefreshDatabase;

    public function test_bookmark_toggle_is_scoped_to_user_and_preserves_last_read(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $other = User::factory()->create();
        $surah = $this->surah();
        $attributes = ['surah_id' => $surah->id, 'verse_number' => 1];
        $otherBookmark = QuranBookmark::create($attributes + ['user_id' => $other->id, 'is_last_read' => false]);
        $lastRead = QuranBookmark::create($attributes + ['user_id' => $user->id, 'is_last_read' => true]);

        $this->actingAs($user)->postJson(route('quran.bookmark'), $attributes + ['label' => 'Review'])
            ->assertOk()->assertExactJson(['bookmarked' => true]);
        $this->assertDatabaseHas('quran_bookmarks', $attributes + [
            'user_id' => $user->id, 'is_last_read' => false, 'label' => 'Review',
        ]);

        $this->postJson(route('quran.bookmark'), $attributes)
            ->assertOk()->assertExactJson(['bookmarked' => false]);
        $this->assertDatabaseMissing('quran_bookmarks', $attributes + ['user_id' => $user->id, 'is_last_read' => false]);
        $this->assertModelExists($otherBookmark);
        $this->assertModelExists($lastRead);
        $this->assertDatabaseCount('quran_bookmarks', 2);
    }

    public function test_last_read_replaces_only_current_users_marker(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $other = User::factory()->create();
        $surah = $this->surah();
        $attributes = ['surah_id' => $surah->id, 'verse_number' => 1];
        $bookmark = QuranBookmark::create($attributes + ['user_id' => $user->id, 'is_last_read' => false]);
        $otherLastRead = QuranBookmark::create($attributes + ['user_id' => $other->id, 'is_last_read' => true]);

        foreach ([1, 2, 2] as $verse) {
            $this->actingAs($user)->postJson(route('quran.last-read'), [
                'surah_id' => $surah->id, 'verse_number' => $verse,
            ])->assertOk()->assertExactJson(['success' => true]);
        }

        $this->assertSame(1, $user->quranBookmarks()->where('is_last_read', true)->count());
        $this->assertDatabaseHas('quran_bookmarks', [
            'user_id' => $user->id, 'surah_id' => $surah->id, 'verse_number' => 2, 'is_last_read' => true,
        ]);
        $this->assertModelExists($bookmark);
        $this->assertModelExists($otherLastRead);
        $this->assertDatabaseCount('quran_bookmarks', 3);
    }

    public function test_progress_rejects_another_users_program(): void
    {
        $program = $this->program(User::factory()->create());

        $this->actingAs(User::factory()->create(['is_active' => true]))
            ->postJson(route('quran.khatam.progress', $program->id), $this->progress())
            ->assertNotFound();

        $this->assertDatabaseCount('quran_khatam_progress', 0);
        $this->assertSame('active', $program->fresh()->status);
    }

    public function test_daily_duplicate_does_not_add_pages_even_when_juz_or_pages_differ(): void
    {
        $this->travelTo(now()->setDate(2026, 11, 25)->startOfDay());
        $user = User::factory()->create(['is_active' => true]);
        $program = $this->program($user);
        $url = route('quran.khatam.progress', $program->id);

        $this->actingAs($user)->postJson($url, $this->progress())
            ->assertOk()->assertExactJson(['success' => true, 'progress_percent' => 0.8, 'total_pages_read' => 5]);
        $this->postJson($url, array_replace($this->progress(), ['juz_number' => 2, 'pages_read' => 20]))
            ->assertOk()->assertExactJson([
                'success' => true, 'duplicate' => true, 'progress_percent' => 0.8, 'total_pages_read' => 5,
            ]);

        $this->assertDatabaseCount('quran_khatam_progress', 1);
        $this->assertSame('2026-11-25', $program->progress()->firstOrFail()->read_date->toDateString());
        $this->assertSame(5, $program->progress()->firstOrFail()->pages_read);

        $this->travel(1)->days();
        $this->postJson($url, $this->progress())->assertOk()->assertJsonPath('total_pages_read', 10);
        $this->assertDatabaseCount('quran_khatam_progress', 2);
    }

    public function test_distinct_ranges_and_programs_are_not_daily_duplicates(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $program = $this->program($user);
        $otherProgram = $this->program($user);
        $this->actingAs($user);

        foreach ([$this->progress(), array_replace($this->progress(), ['verse_to' => 6]),
            array_replace($this->progress(), ['verse_from' => 2]),
            array_replace($this->progress(), ['surah_number' => 2])] as $payload) {
            $this->postJson(route('quran.khatam.progress', $program->id), $payload)
                ->assertOk()->assertJsonMissingPath('duplicate');
        }
        $this->postJson(route('quran.khatam.progress', $otherProgram->id), $this->progress())
            ->assertOk()->assertJsonPath('total_pages_read', 5)->assertJsonMissingPath('duplicate');

        $this->assertDatabaseCount('quran_khatam_progress', 5);
    }

    public function test_progress_completes_program_at_604_pages(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        $program = $this->program($user);
        $program->progress()->create(array_replace($this->progress(), [
            'pages_read' => 599, 'read_date' => today()->subDay()->toDateString(),
        ]));

        $this->actingAs($user)->postJson(route('quran.khatam.progress', $program->id), $this->progress())
            ->assertOk()->assertExactJson(['success' => true, 'progress_percent' => 100, 'total_pages_read' => 604]);

        $this->assertSame('completed', $program->fresh()->status);
        $this->assertDatabaseCount('quran_khatam_progress', 2);
    }

    public function test_inactive_programs_reject_progress_before_payload_validation(): void
    {
        $user = User::factory()->create(['is_active' => true]);
        foreach (['completed', 'abandoned'] as $status) {
            $program = $this->program($user);
            $program->update(['status' => $status]);

            foreach ([$this->progress(), []] as $payload) {
                $this->actingAs($user)->postJson(route('quran.khatam.progress', $program->id), $payload)
                    ->assertUnprocessable()->assertExactJson(['error' => 'Program sudah tidak aktif.']);
            }
            $this->assertSame($status, $program->fresh()->status);
        }
        $this->assertDatabaseCount('quran_khatam_progress', 0);
    }

    private function surah(): Surah
    {
        return Surah::create([
            'number' => 1, 'name_arabic' => 'Al-Fatihah', 'name_latin' => 'Al-Fatihah',
            'revelation_type' => 'meccan', 'total_verses' => 7,
        ]);
    }

    private function program(User $user): QuranKhatamProgram
    {
        return $user->khatamPrograms()->create([
            'title' => 'Daily reading', 'target_days' => 30,
            'start_date' => today(), 'target_date' => today()->addDays(30),
        ]);
    }

    private function progress(): array
    {
        return ['surah_number' => 1, 'verse_from' => 1, 'verse_to' => 5, 'juz_number' => 1, 'pages_read' => 5];
    }
}
