<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Services\MenuService;

class MenuController extends Controller
{
    public function __construct(
        private readonly MenuService $menus,
    ) {
    }

    public function index()
    {
        $this->authorize('viewAny', Menu::class);

        $menus = collect(Menu::LOCATIONS)->map(fn ($location) => $this->menus->menu($location));

        return view('admin.menus.index', ['menus' => $menus]);
    }

    public function edit(Menu $menu)
    {
        $this->authorize('view', $menu);

        $items = $menu->items()
            ->with(['translations', 'page.translations', 'productCategory.translations', 'children.translations'])
            ->orderBy('position')
            ->orderBy('id')
            ->get();

        $statuses = $items->mapWithKeys(fn ($item) => [$item->id => $this->menus->validity($item)]);

        return view('admin.menus.edit', [
            'menu' => $menu,
            'items' => $items,
            'statuses' => $statuses,
        ]);
    }
}
