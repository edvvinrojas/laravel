@extends('layouts.app')
@section('title','Notificaciones')
@section('page-title','Notificaciones')

@section('content')
<div class="max-w-2xl">
    <div class="flex justify-end mb-4">
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf @method('PATCH')
            <button class="btn-secondary">Marcar todas como leídas</button>
        </form>
    </div>

    <div class="card">
        <ul class="divide-y divide-gray-100">
        @forelse($notifications as $n)
        <li class="px-5 py-4 flex items-start gap-3 {{ $n->is_read ? 'opacity-60' : 'bg-blue-50/50' }}">
            @php
            $ic=['COBRANZA_VENCIDA'=>'bg-red-100 text-red-600','COBRANZA_POR_VENCER'=>'bg-yellow-100 text-yellow-600','TICKET_URGENTE'=>'bg-red-100 text-red-600','COMPRA_PENDIENTE'=>'bg-orange-100 text-orange-600','VACACION_PENDIENTE'=>'bg-blue-100 text-blue-600','RENTA_POR_VENCER'=>'bg-purple-100 text-purple-600','SISTEMA'=>'bg-gray-100 text-gray-600','INFO'=>'bg-blue-100 text-blue-600'];
            @endphp
            <div class="w-8 h-8 rounded-full flex-shrink-0 flex items-center justify-center {{ $ic[$n->type]??'bg-gray-100 text-gray-500' }}">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center justify-between gap-2">
                    <p class="text-sm font-medium text-gray-900">{{ $n->title }}</p>
                    <span class="text-xs text-gray-400 flex-shrink-0">{{ $n->created_at->diffForHumans() }}</span>
                </div>
                @if($n->message)<p class="text-sm text-gray-600 mt-0.5">{{ $n->message }}</p>@endif
                @if($n->link)<a href="{{ $n->link }}" class="text-xs text-blue-600 hover:underline mt-1 inline-block">Ver más →</a>@endif
            </div>
            @if(!$n->is_read)
            <div class="w-2 h-2 bg-blue-500 rounded-full mt-1.5 flex-shrink-0"></div>
            @endif
        </li>
        @empty
        <li class="px-5 py-12 text-center text-gray-400">
            <svg class="w-10 h-10 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            <p>Sin notificaciones</p>
        </li>
        @endforelse
        </ul>
        @if($notifications->hasPages())
        <div class="px-5 py-3 border-t border-gray-100">{{ $notifications->links() }}</div>
        @endif
    </div>
</div>
@endsection
