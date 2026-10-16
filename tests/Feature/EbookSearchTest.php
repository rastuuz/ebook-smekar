<?php

use App\Models\Category;
use App\Models\Ebook;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;

beforeEach(function () {
    $this->seed(DatabaseSeeder::class);
});

test('live search ajax mengembalikan struktur JSON yang benar', function () {
    $this->actingAs(User::where('nis', '18034')->first());

    $res = $this->getJson(route('ebooks.index', ['ajax' => 1]));
    $res->assertOk();
    $res->assertJsonStructure(['total', 'search', 'data' => ['*' => ['id', 'title', 'author', 'category', 'cover_url', 'videos_count', 'detail_url']]]);

    expect($res->json('total'))->toBe(Ebook::count());
});

test('live search ajax memfilter berdasarkan kata kunci', function () {
    $this->actingAs(User::where('nis', '18034')->first());
    $ebook = Ebook::first();

    $keyword = mb_substr($ebook->title, 0, 5);
    $res = $this->getJson(route('ebooks.index', ['ajax' => 1, 'q' => $keyword]));
    $res->assertOk();

    $titles = collect($res->json('data'))->pluck('title')->all();
    expect($titles)->toContain($ebook->title);
    expect($res->json('search'))->toBe($keyword);
});

test('live search ajax memfilter berdasarkan kategori', function () {
    $this->actingAs(User::where('nis', '18034')->first());
    $category = Category::has('ebooks')->first();

    $res = $this->getJson(route('ebooks.index', ['ajax' => 1, 'category_id' => $category->id]));
    $res->assertOk();

    expect($res->json('total'))->toBe($category->ebooks()->count());
    foreach ($res->json('data') as $item) {
        expect($item['category'])->toBe($category->name);
    }
});
