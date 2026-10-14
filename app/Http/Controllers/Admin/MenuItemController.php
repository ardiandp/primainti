<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SaveMenuItemRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Models\Page;
use App\Models\ProductCategory;
use App\Services\AuditLogger;
use App\Services\MenuService;

class MenuItemController extends Controller
{
    public function create(Menu $menu)
    {
        $this->authorize('create', MenuItem::class);

        return view('admin.menu-items.create', [
            'menu' => $menu,
            'item' => new MenuItem(['menu_id' => $menu->id, 'link_type' => 'url', 'position' => 0]),
            'options' => $this->options($menu),
        ]);
    }

    public function store(SaveMenuItemRequest $request)
    {
        $this->authorize('create', MenuItem::class);

        $validated = $request->validated();

        $item = MenuItem::create($this->attributes($validated));

        $this->syncTranslations($item, $validated['translations'] ?? []);

        AuditLogger::log('menu_item.create', $item, null, [
            'menu_id' => $item->menu_id,
            'title' => $item->title,
            'link_type' => $item->link_type,
        ]);

        MenuService::flushTrees();

        return redirect()->route('admin.menus.edit', $item->menu_id)->with('status', 'Item menu ditambah.');
    }

    public function edit(MenuItem $menuItem)
    {
        $this->authorize('update', $menuItem);

        $menuItem->load('translations');

        return view('admin.menu-items.edit', [
            'menu' => $menuItem->menu,
            'item' => $menuItem,
            'options' => $this->options($menuItem->menu),
        ]);
    }

    public function update(SaveMenuItemRequest $request, MenuItem $menuItem)
    {
        $this->authorize('update', $menuItem);

        $validated = $request->validated();

        $old = ['title' => $menuItem->title, 'link_type' => $menuItem->link_type];

        // Item tidak berpindah menu lewat form ini.
        $menuItem->update($this->attributes($validated, $menuItem->menu_id));

        $this->syncTranslations($menuItem, $validated['translations'] ?? []);

        AuditLogger::log('menu_item.update', $menuItem, $old, [
            'title' => $menuItem->title,
            'link_type' => $menuItem->link_type,
        ]);

        MenuService::flushTrees();

        return redirect()->route('admin.menus.edit', $menuItem->menu_id)->with('status', 'Item menu diperbarui.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $this->authorize('delete', $menuItem);

        $menuId = $menuItem->menu_id;

        // Anak ikut terhapus aman: parent dinolkan (bukan cascade) agar tidak
        // menghapus sunyi; jadikan top-level.
        $menuItem->children()->update(['parent_id' => null]);
        $menuItem->translations()->delete();
        $menuItem->delete();

        AuditLogger::log('menu_item.delete', $menuItem, ['id' => $menuItem->id], null);

        MenuService::flushTrees();

        return redirect()->route('admin.menus.edit', $menuId)->with('status', 'Item menu dihapus.');
    }

    /** @return array<string, mixed> */
    private function attributes(array $validated, ?int $menuId = null): array
    {
        return [
            'menu_id' => $menuId ?? $validated['menu_id'],
            'parent_id' => $validated['parent_id'] ?? null,
            'title' => $validated['title'],
            'link_type' => $validated['link_type'],
            'url' => $validated['link_type'] === 'url' ? $validated['url'] : null,
            'page_id' => $validated['link_type'] === 'page' ? $validated['page_id'] : null,
            'product_category_id' => $validated['link_type'] === 'product_category'
                ? $validated['product_category_id']
                : null,
            'route_name' => $validated['link_type'] === 'route' ? $validated['route_name'] : null,
            'opens_in_new_tab' => (bool) ($validated['opens_in_new_tab'] ?? false),
            'position' => (int) ($validated['position'] ?? 0),
        ];
    }

    /** @param array<string, array{title: string}> $translations */
    private function syncTranslations(MenuItem $item, array $translations): void
    {
        $item->loadMissing('translations');

        foreach ($translations as $locale => $data) {
            if (! in_array($locale, ['id', 'en'], true)) {
                continue;
            }

            $translation = $item->translations->firstWhere('locale', $locale);

            if ($translation === null) {
                $item->translations()->create(['locale' => $locale, 'title' => $data['title']]);
            } else {
                $translation->update(['title' => $data['title']]);
            }
        }

        // Terjemahan yang dikosongkan dihapus (fallback ke label default).
        $item->translations()
            ->whereNotIn('locale', array_keys($translations))
            ->delete();
    }

    /** @return array<string, mixed> */
    private function options(Menu $menu)
    {
        $label = fn ($model, $fallback) => $model->translationFor('id')?->name
            ?? $model->translations->first()?->name
            ?? $fallback;

        return [
            'parents' => $menu->items()
                ->with('translations')
                ->whereNull('parent_id')
                ->orderBy('position')
                ->get()
                ->mapWithKeys(fn ($parent) => [$parent->id => $parent->labelFor('id')]),
            'pages' => Page::with('translations')->orderBy('id')->get()
                ->mapWithKeys(fn ($page) => [
                    $page->id => ($page->translations->firstWhere('locale', 'id')?->title
                        ?? $page->translations->first()?->title
                        ?? ('Halaman #'.$page->id))." [{$page->status}]",
                ]),
            'categories' => ProductCategory::with('translations')->orderBy('id')->get()
                ->mapWithKeys(fn ($category) => [$category->id => $label($category, 'Kategori #'.$category->id)]),
            'routes' => collect(MenuService::ROUTE_ALLOWLIST)->mapWithKeys(fn ($route) => [$route => $route]),
        ];
    }
}
