@extends('layouts.app')
@section('title','Usuario')
@section('page-title','Detalle de Usuario')

@section('content')
<div class="flex gap-3 mb-4">
    <a href="{{ route('users.edit',$user) }}" class="btn-primary">Editar</a>
    <a href="{{ route('users.index') }}" class="btn-secondary">← Volver</a>
</div>
<div class="card max-w-md">
    <div class="card-header">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold">{{ strtoupper(substr($user->full_name,0,1)) }}</div>
            <div><h3 class="font-semibold">{{ $user->full_name }}</h3><p class="text-xs text-gray-500">{{ $user->email }}</p></div>
        </div>
        @if($user->is_active)<span class="badge-green">Activo</span>@else<span class="badge-gray">Inactivo</span>@endif
    </div>
    <div class="card-body text-sm space-y-3">
        <div class="flex justify-between"><span class="text-gray-500">Usuario</span><span class="font-mono">{{ $user->username }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Rol</span><span class="{{ $user->rol==='administrador'?'badge-red':($user->rol==='gerencia'?'badge-blue':'badge-gray') }}">{{ $user->rol }}</span></div>
        <div class="flex justify-between"><span class="text-gray-500">Departamento</span><span class="badge-purple">{{ $user->department }}</span></div>
        @if($user->employee)<div class="flex justify-between"><span class="text-gray-500">Empleado vinculado</span><span><a href="{{ route('employees.show',$user->employee) }}" class="text-blue-600 hover:underline">{{ $user->employee->nombre }}</a></span></div>@endif
        <div><p class="text-gray-500">Registrado</p><p>{{ $user->created_at->format('d/m/Y') }}</p></div>
    </div>
</div>
@endsection
