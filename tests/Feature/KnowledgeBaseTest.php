<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\KnowledgeBaseArticle;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KnowledgeBaseTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_create_and_edit_an_article(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        $this->actingAs($superAdmin)->post('/knowledge-base', [
            'title' => 'Getting started',
            'body' => 'Welcome to SentePro.',
            'status' => 'published',
        ])->assertRedirect(route('knowledge-base.index'));

        $article = KnowledgeBaseArticle::first();
        $this->assertSame('Getting started', $article->title);
        $this->assertSame('published', $article->status);
        $this->assertSame($superAdmin->id, $article->created_by);

        $this->actingAs($superAdmin)->put("/knowledge-base/{$article->id}", [
            'title' => 'Getting started (updated)',
            'body' => 'Welcome to SentePro, updated.',
            'status' => 'draft',
        ])->assertRedirect(route('knowledge-base.index'));

        $this->assertSame('Getting started (updated)', $article->fresh()->title);
        $this->assertSame('draft', $article->fresh()->status);
    }

    public function test_a_business_admin_cannot_create_an_article(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $this->actingAs($admin)->post('/knowledge-base', [
            'title' => 'Should fail',
            'body' => 'Not allowed.',
            'status' => 'published',
        ])->assertForbidden();
    }

    public function test_regular_users_only_see_published_articles_in_the_index(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        KnowledgeBaseArticle::create(['title' => 'Published Article', 'body' => 'Visible.', 'status' => 'published']);
        KnowledgeBaseArticle::create(['title' => 'Draft Article', 'body' => 'Hidden.', 'status' => 'draft']);

        $response = $this->actingAs($admin)->get('/knowledge-base');

        $response->assertOk();
        $response->assertSee('Published Article');
        $response->assertDontSee('Draft Article');
    }

    public function test_a_regular_user_cannot_view_a_draft_article_directly(): void
    {
        $business = Business::factory()->create(['status' => 'approved']);
        $admin = User::factory()->businessAdmin($business)->create();

        $article = KnowledgeBaseArticle::create(['title' => 'Draft Article', 'body' => 'Hidden.', 'status' => 'draft']);

        $this->actingAs($admin)->get("/knowledge-base/{$article->id}")->assertForbidden();
    }

    public function test_super_admin_sees_draft_articles_in_the_index(): void
    {
        $superAdmin = User::factory()->superAdmin()->create();

        KnowledgeBaseArticle::create(['title' => 'Draft Article', 'body' => 'Hidden.', 'status' => 'draft']);

        $response = $this->actingAs($superAdmin)->get('/knowledge-base');

        $response->assertOk();
        $response->assertSee('Draft Article');
    }
}
