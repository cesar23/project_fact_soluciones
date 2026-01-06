@extends('tenant.layouts.app')

@section('title', 'Items Top')

@section('content')
<div class="container">
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Reporte de Items Top</h3>
            <a href="{{ route('tenant.reports.items.export', ['start_date' => request('start_date'), 'end_date' => request('end_date')]) }}" class="el-button submit el-button--success el-button--small"><i class="fa fa-file-excel"></i> Exportar a Excel</a>
            </div>
        <div class="card-body">
            <div id="app">
                <top-items-report :url="'{{ route('tenant.reports.items.top') }}'"></top-items-report>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger mt-3">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <!-- Filtros -->
            <form method="GET" action="{{ route('tenant.reports.items.top') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label for="start_date" class="form-label">Fecha de inicio</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label for="end_date" class="form-label">Fecha de fin</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-4 d-flex align-items-end mb-3">
                        <button type="submit" class="btn btn-primary ">Filtrar</button>
                    </div>
                </div>
            </form>

            <!-- Mostrar los datos de los items top -->
            @if(isset($topItems))
                <div class="table-responsive mt-4">
                    <table class="table  numeros">
                        <thead>
                            <tr>
                                <th class="nombre">#</th>
                                <th class="nombre" >Nombre</th>
                                <th>Ventas</th>
                                <th>Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topItems as $item)
                                <tr>
                                    <td class="nombre">{{ $item->id }}</td>
                                    <td class="nombre">{{ $item->description }}</td>
                                    <td class="numeros">{{ $item->sales_count }}</td>
                                    <td class="numeros">{{ number_format($item->total_sales, 2) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.reportTopItemsUrl = "{{ route('tenant.reports.items.top') }}";
</script>
@endpush

<style>
.container {
    max-width: 1000px;
    margin: 0 auto;
}

.card {
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid #e9ecef;
    padding: 15px;
    font-size: 1.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.card-body {
    padding: 15px;
}

.table-responsive {
    margin-top: 20px;
}

.table {
    margin-bottom: 0;
}
.numeros {
    text-align: right;
}
.nombre {
    text-align: left;
}
.table th, .table td {

    vertical-align: middle;
}

.form-label {
    font-weight: bold;
    margin-bottom: 5px;
}

.btn-primary {
    background-color: #007bff;
    border-color: #007bff;
}

.btn-primary:hover {
    background-color: #0056b3;
    border-color: #004085;
}

.btn-success {
    background-color: #28a745;
    border-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
    border-color: #1e7e34;
}
</style>
