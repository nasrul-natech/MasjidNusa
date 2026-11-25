<?php

namespace Tests\Feature;

use App\Http\Middleware\CheckRole;
use App\Http\Middleware\EnsureTwoFactorVerified;
use App\Models\ManagementHistory;
use App\Models\OrganizationStructure;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Tests\TestCase;

class OrganizationIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            Authenticate::class, CheckRole::class, EnsureTwoFactorVerified::class, PermissionMiddleware::class,
        ]);
    }

    public function test_valid_hierarchy_can_be_created_reparented_and_detached(): void
    {
        $period = ManagementHistory::create(['title' => 'Current', 'start_year' => 2026]);
        $root = $this->node($period);
        $otherRoot = $this->node($period);

        $this->postJson(route('admin.operasional.organization.store'), [
            'management_history_id' => $period->id,
            'name' => 'New member', 'position' => 'Member', 'parent_id' => $root->id,
        ])->assertRedirect();
        $child = OrganizationStructure::where('name', 'New member')->firstOrFail();

        $this->updateNode($child, ['parent_id' => $otherRoot->id])->assertRedirect();
        $this->assertEquals($otherRoot->id, $child->fresh()->parent_id);

        $this->updateNode($child, ['parent_id' => null])->assertRedirect();
        $this->assertNull($child->fresh()->parent_id);
    }

    public function test_self_and_descendant_parents_are_rejected_without_mutation(): void
    {
        $period = ManagementHistory::create(['title' => 'Current', 'start_year' => 2026]);
        $root = $this->node($period);
        $child = $this->node($period, $root);
        $grandchild = $this->node($period, $child);
        $before = OrganizationStructure::orderBy('id')->get()->toArray();

        foreach ([$root, $child, $grandchild] as $parent) {
            $this->updateNode($root, ['parent_id' => $parent->id, 'name' => 'Must not save'])
                ->assertUnprocessable()->assertJsonValidationErrors('parent_id');
            $this->assertSame($before, OrganizationStructure::orderBy('id')->get()->toArray());
        }
    }

    public function test_cross_period_parent_is_rejected_on_create_and_update_without_mutation(): void
    {
        $period = ManagementHistory::create(['title' => 'Current', 'start_year' => 2026]);
        $otherPeriod = ManagementHistory::create(['title' => 'Other', 'start_year' => 2020]);
        $node = $this->node($period);
        $otherRoot = $this->node($otherPeriod);
        $before = OrganizationStructure::orderBy('id')->get()->toArray();

        $this->postJson(route('admin.operasional.organization.store'), [
            'management_history_id' => $period->id, 'name' => 'Rejected',
            'position' => 'Member', 'parent_id' => $otherRoot->id,
        ])->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $this->updateNode($node, ['parent_id' => $otherRoot->id])
            ->assertUnprocessable()->assertJsonValidationErrors('parent_id');

        $this->assertSame($before, OrganizationStructure::orderBy('id')->get()->toArray());
    }

    public function test_moving_node_with_children_cannot_leave_cross_period_edges(): void
    {
        $period = ManagementHistory::create(['title' => 'Current', 'start_year' => 2026]);
        $otherPeriod = ManagementHistory::create(['title' => 'Other', 'start_year' => 2020]);
        $root = $this->node($period);
        $this->node($period, $root);
        $otherRoot = $this->node($otherPeriod);
        $before = OrganizationStructure::orderBy('id')->get()->toArray();

        foreach ([null, $otherRoot->id] as $parentId) {
            $this->updateNode($root, [
                'management_history_id' => $otherPeriod->id, 'parent_id' => $parentId,
            ])->assertUnprocessable()->assertJsonValidationErrors('management_history_id');
            $this->assertSame($before, OrganizationStructure::orderBy('id')->get()->toArray());
        }
    }

    public function test_leaf_can_move_period_only_after_detaching_from_old_parent(): void
    {
        $period = ManagementHistory::create(['title' => 'Current', 'start_year' => 2026]);
        $otherPeriod = ManagementHistory::create(['title' => 'Other', 'start_year' => 2020]);
        $root = $this->node($period);
        $leaf = $this->node($period, $root);
        $before = $leaf->fresh()->getAttributes();

        $this->updateNode($leaf, ['management_history_id' => $otherPeriod->id])
            ->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $this->assertSame($before, $leaf->fresh()->getAttributes());

        $this->updateNode($leaf, ['management_history_id' => $otherPeriod->id, 'parent_id' => null])
            ->assertRedirect();
        $this->assertNull($leaf->fresh()->parent_id);
        $this->assertEquals($otherPeriod->id, $leaf->fresh()->management_history_id);
    }

    public function test_existing_ancestor_cycle_is_rejected_without_looping_or_mutation(): void
    {
        $period = ManagementHistory::create(['title' => 'Current', 'start_year' => 2026]);
        $first = $this->node($period);
        $second = $this->node($period, $first);
        $first->update(['parent_id' => $second->id]);
        $node = $this->node($period);
        $before = OrganizationStructure::orderBy('id')->get()->toArray();

        $this->updateNode($node, ['parent_id' => $first->id])
            ->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $this->assertSame($before, OrganizationStructure::orderBy('id')->get()->toArray());
    }

    private function node(ManagementHistory $period, ?OrganizationStructure $parent = null): OrganizationStructure
    {
        return OrganizationStructure::create([
            'management_history_id' => $period->id, 'name' => 'Member',
            'position' => 'Member', 'parent_id' => $parent?->id,
        ]);
    }

    private function updateNode(OrganizationStructure $node, array $changes)
    {
        return $this->putJson(route('admin.operasional.organization.update', $node), array_merge([
            'management_history_id' => $node->management_history_id,
            'name' => $node->name, 'position' => $node->position,
        ], $changes));
    }
}
