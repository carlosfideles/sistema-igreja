<?php

namespace App\Http\Middleware;

use App\Models\MemberTransfer;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareUnreadNotifications
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $unreadNotifications = collect();

        if (Auth::check()) {
            $user = Auth::user();
            if ($user->isLocal()) {
                $accessibleChurchIds = $user->churches()->pluck('churches.id')->toArray();
                $unreadNotifications = MemberTransfer::with(['member', 'fromChurch', 'toChurch'])
                    ->whereIn('status', ['approved', 'rejected'])
                    ->where('local_notified', false)
                    ->where(function ($q) use ($user, $accessibleChurchIds) {
                        $q->where('transferred_by', $user->id)
                          ->orWhereIn('from_church_id', $accessibleChurchIds);
                    })
                    ->orderBy('approved_at', 'desc')
                    ->orderBy('updated_at', 'desc')
                    ->get();
            }
        }

        View::share('unreadTransferNotifications', $unreadNotifications);

        return $next($request);
    }
}
