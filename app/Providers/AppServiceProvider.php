<?php

namespace App\Providers;

use App\Models\ListMember;
use App\Models\Task;
use App\Models\TaskList;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(['components.layouts.app', 'partials.sidebar', 'partials.topbar'], function ($view): void {
            if (! Auth::check()) {
                return;
            }

            $userId = (int) Auth::id();
            $owned = TaskList::where('owner_id', $userId)->pluck('id');
            $memberIds = ListMember::where('user_id', $userId)->pluck('list_id');
            $ids = $owned->merge($memberIds)->unique()->values()->all() ?: [0];

            if (! isset($view->getData()['projects'])) {
                $view->with('projects', TaskList::whereIn('id', $ids)->orderBy('name')->get());
            }

            if (! isset($view->getData()['overdueCount'])) {
                $view->with('overdueCount', Task::whereIn('list_id', $ids)
                    ->where('is_completed', false)
                    ->whereNotNull('deadline')
                    ->where('deadline', '<', now())
                    ->count());
            }
        });
    }
}
