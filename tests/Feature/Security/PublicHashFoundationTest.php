<?php

namespace Tests\Feature\Security;

use App\Models\Testing\PublicHashFixtureRecord;
use App\Support\PublicHash\PublicHashGenerator;
use App\Support\PublicHash\PublicHashResolver;
use App\Support\PublicHash\PublicHashValidationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class PublicHashFoundationTest extends TestCase
{
    use RefreshDatabase;

    protected PublicHashGenerator $generator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->generator = app(PublicHashGenerator::class);
    }

    public function test_generator_returns_sixteen_character_base62_hash(): void
    {
        $hash = $this->generator->generate();

        $this->assertSame(16, strlen($hash));
        $this->assertMatchesRegularExpression('/^[0-9A-Za-z]{16}$/', $hash);
        $this->assertTrue($this->generator->isValid($hash));
    }

    public function test_generator_rejects_prefixed_hash(): void
    {
        $this->assertFalse($this->generator->isValid('INV_8jkP2Ld93QmT6Rw'));

        $this->expectException(PublicHashValidationException::class);
        $this->generator->assertValid('QT_8jkP2Ld93QmT6RwX');
    }

    public function test_generator_rejects_invalid_characters_and_short_values(): void
    {
        $this->assertFalse($this->generator->isValid('short'));
        $this->assertFalse($this->generator->isValid('8jkP2Ld93QmT6Rw!'));

        $this->expectException(PublicHashValidationException::class);
        $this->generator->assertValid('not-valid-hash!!');
    }

    public function test_trait_assigns_public_id_on_create(): void
    {
        $record = PublicHashFixtureRecord::query()->create(['label' => 'fixture']);

        $this->assertNotNull($record->public_id);
        $this->assertTrue($this->generator->isValid($record->public_id));
        $this->assertSame('public_id', $record->getRouteKeyName());
    }

    public function test_public_id_is_immutable_once_assigned(): void
    {
        $record = PublicHashFixtureRecord::query()->create(['label' => 'immutable']);

        $this->expectException(PublicHashValidationException::class);

        $record->update(['public_id' => $this->generator->generate()]);
    }

    public function test_resolver_resolves_by_public_hash(): void
    {
        $record = PublicHashFixtureRecord::query()->create(['label' => 'resolve']);

        $resolved = app(PublicHashResolver::class)->resolve(
            PublicHashFixtureRecord::class,
            $record->public_id,
        );

        $this->assertTrue($record->is($resolved));
    }

    public function test_numeric_fallback_resolves_only_when_enabled(): void
    {
        $record = PublicHashFixtureRecord::query()->create(['label' => 'fallback']);
        $resolver = app(PublicHashResolver::class);

        Config::set('public_hashes.numeric_fallback_enabled', true);
        Log::spy();

        $resolved = $resolver->resolve(PublicHashFixtureRecord::class, (string) $record->id);
        $this->assertTrue($record->is($resolved));
        Log::shouldHaveReceived('info')->with('public_hash.numeric_fallback', \Mockery::subset([
            'model' => PublicHashFixtureRecord::class,
            'numeric_id' => (string) $record->id,
        ]));

        Config::set('public_hashes.numeric_fallback_enabled', false);

        $this->expectException(\Illuminate\Database\Eloquent\ModelNotFoundException::class);
        $resolver->resolve(PublicHashFixtureRecord::class, (string) $record->id);
    }

    public function test_backfill_dry_run_reports_missing_hashes(): void
    {
        PublicHashFixtureRecord::withoutEvents(function () {
            PublicHashFixtureRecord::query()->forceCreate([
                'label' => 'missing',
                'public_id' => null,
            ]);

            PublicHashFixtureRecord::query()->forceCreate([
                'label' => 'blank',
                'public_id' => '',
            ]);
        });

        Config::set('public_hashes.route_exposed_models', [PublicHashFixtureRecord::class]);

        Artisan::call('public-hash:backfill', [
            '--model' => PublicHashFixtureRecord::class,
            '--dry-run' => true,
        ]);

        $output = Artisan::output();

        $this->assertStringContainsString('2 row(s) missing public_id', $output);
        $this->assertStringContainsString('Dry run: would backfill 2 row(s)', $output);
        $this->assertSame(2, PublicHashFixtureRecord::query()->whereNull('public_id')->orWhere('public_id', '')->count());
    }

    public function test_backfill_fills_missing_public_id_values(): void
    {
        $record = PublicHashFixtureRecord::withoutEvents(function () {
            return PublicHashFixtureRecord::query()->forceCreate([
                'label' => 'needs-hash',
                'public_id' => null,
            ]);
        });

        Artisan::call('public-hash:backfill', [
            '--model' => PublicHashFixtureRecord::class,
        ]);

        $record->refresh();

        $this->assertNotNull($record->public_id);
        $this->assertTrue($this->generator->isValid($record->public_id));
        $this->assertStringContainsString('Backfilled 1 row(s)', Artisan::output());
    }

    public function test_backfill_is_idempotent_and_preserves_existing_hashes(): void
    {
        $record = PublicHashFixtureRecord::query()->create(['label' => 'existing']);
        $original = $record->public_id;

        Artisan::call('public-hash:backfill', [
            '--model' => PublicHashFixtureRecord::class,
        ]);

        $record->refresh();

        $this->assertSame($original, $record->public_id);
        $this->assertStringContainsString('0 row(s) missing public_id', Artisan::output());
    }

    public function test_audit_command_reports_configured_model_coverage(): void
    {
        PublicHashFixtureRecord::query()->create(['label' => 'audit']);

        Config::set('public_hashes.route_exposed_models', [PublicHashFixtureRecord::class]);

        Artisan::call('public-hash:audit', [
            '--model' => PublicHashFixtureRecord::class,
        ]);

        $output = Artisan::output();

        $this->assertStringContainsString('PublicHashFixtureRecord', $output);
        $this->assertStringContainsString('public_hash_fixture_records', $output);
        $this->assertStringContainsString('public_id', $output);
        $this->assertStringContainsString('yes', $output);
    }
}
