<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AuditLogController extends Controller
{
    public function index(Request $request): View
    {
        $u       = auth()->user();
        $action  = $request->query('action');
        $userId  = $request->query('user_id');
        $from    = $request->query('from');
        $to      = $request->query('to');

        $logs = AuditLog::query()
            ->when(!$u->isSuperAdmin(), fn ($q) => $q->where('client_id', $u->client_id))
            ->when($action, fn ($q) => $q->where('action', $action))
            ->when($userId, fn ($q) => $q->where('user_id', $userId))
            ->when($from, fn ($q) => $q->whereDate('created_at', '>=', $from))
            ->when($to,   fn ($q) => $q->whereDate('created_at', '<=', $to))
            ->with('user')
            ->orderByDesc('id')
            ->paginate(50)
            ->withQueryString();

        $users = User::when(!$u->isSuperAdmin(), fn ($q) => $q->where('client_id', $u->client_id))
            ->orderBy('name')->get();

        return view('admin.audit.index', [
            'logs'    => $logs,
            'users'   => $users,
            'action'  => $action,
            'userId'  => $userId,
            'from'    => $from,
            'to'      => $to,
            'actions' => ['VIEW', 'CREATE', 'UPDATE', 'DELETE'],
        ]);
    }
}
