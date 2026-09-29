<?php

namespace App\Providers;

use App\Models\Leave;
use App\Models\PayrollItem;
use App\Models\PayrollRun;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
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
        View::composer('layouts.admin', function ($view): void {
            $user = Auth::user();
            $ticketUnreadCount = 0;
            $pendingLeaveCount = 0;
            $draftPayrollCount = 0;
            $unpaidPayrollCount = 0;

            if ($user && ($user->isFinance() || $user->hasHrAdminAccess())) {
                $query = $this->teamTicketQuery($user);
                $ticketUnreadCount = (clone $query)->unreadForTeam()->count();

                if ($user->isFinance()) {
                    $draftPayrollCount = PayrollRun::where('status', 'draft')->count();
                    $unpaidPayrollCount = PayrollItem::whereHas('payrollRun', fn (Builder $runs) => $runs
                        ->whereIn('status', ['processed', 'paid']))
                        ->where('payment_status', '!=', 'paid')
                        ->count();
                } else {
                    $pendingLeaveCount = Leave::where('status', 'pending')->count();
                }
            }

            $financeActionCount = $draftPayrollCount + $unpaidPayrollCount;
            $view->with(compact(
                'ticketUnreadCount',
                'pendingLeaveCount',
                'draftPayrollCount',
                'unpaidPayrollCount',
                'financeActionCount'
            ));
        });

        View::composer('layouts.karyawan', function ($view): void {
            $user = Auth::user();
            $ticketUnreadCount = $user
                ? Ticket::query()->where('user_id', $user->id)->unreadForEmployee()->count()
                : 0;

            $view->with('ticketUnreadCount', $ticketUnreadCount);
        });
    }

    private function teamTicketQuery(User $user): Builder
    {
        $query = Ticket::query();

        return $user->isFinance()
            ? $query->whereIn('category', Ticket::FINANCE_CATEGORIES)
            : $query->whereNotIn('category', Ticket::FINANCE_CATEGORIES);
    }
}
