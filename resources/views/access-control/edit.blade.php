@extends('layout.dashboard-layout')

@section('content')
<div class="main-content">
    <section class="section">
        <div class="section-header">
            <h1>Configure {{ $role->name }}</h1>
        </div>

        <div class="section-body">
            <form method="POST" action="{{ route('access-control.update', $role) }}">
                @csrf
                @method('PUT')

                <div class="card">
                    <div class="card-header">
                        <h4>Module Access</h4>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th style="width: 40%">Module</th>
                                        <th>View Only</th>
                                        <th>View + Edit</th>
                                        <th>Discovered Routes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($moduleRows as $module)
                                        <tr>
                                            <td>
                                                <strong>{{ $module['module_label'] }}</strong>
                                                <div class="text-muted small">{{ $module['module_key'] }}</div>
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" name="modules[{{ $module['module_key'] }}]" value="view_only"
                                                    {{ $module['access_level'] === 'view_only' ? 'checked' : '' }}>
                                            </td>
                                            <td class="text-center">
                                                <input type="radio" name="modules[{{ $module['module_key'] }}]" value="view_edit"
                                                    {{ $module['access_level'] === 'view_edit' ? 'checked' : '' }}>
                                            </td>
                                            <td>
                                                @foreach ($module['routes'] as $route)
                                                    <div class="small">{{ $route['route_name'] }} <span class="text-muted">({{ $route['action'] }})</span></div>
                                                @endforeach
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer text-right">
                        <a href="{{ route('access-control.index') }}" class="btn btn-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary">Save Permissions</button>
                    </div>
                </div>
            </form>
        </div>
    </section>
</div>
@endsection
