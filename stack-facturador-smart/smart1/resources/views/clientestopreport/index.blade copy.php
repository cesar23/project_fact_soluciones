@extends('tenant.layouts.app')

@section('title', 'Clientes Top')

@section('content')
<div class="container">
    <div class="card mt-4">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h3>Reporte de Clientes Top</h3>
            <a href="{{ route('tenant.reports.customers.export', ['start_date' => request('start_date'), 'end_date' => request('end_date'), 'include_nv' => request('include_nv')]) }}" class="el-button submit el-button--success el-button--small"><i class="fa fa-file-excel"></i> Exportar a Excel</a>
        </div>
        <div class="card-body">
            <div id="app">
                <top-customers-report :url="'{{ route('tenant.reports.customers.top') }}'" :include-nv="'{{ request('include_nv') }}'"></top-customers-report>
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
            <form method="GET" action="{{ route('tenant.reports.customers.top') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-3 mb-3">
                        <label for="start_date" class="form-label">Fecha de inicio</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>
                    <div class="col-md-3 mb-3">
                        <label for="end_date" class="form-label">Fecha de fin</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>
                    <div class="col-md-3 mb-3" style="margin-top: 25px;">
                        <label for="end_date" class="form-label">Incluir nota de venta</label>
                        <input type="checkbox" id="include_nv" name="include_nv"  value="1" {{ request('include_nv') == '1' ? 'checked' : '' }}>
                    </div>
                    <div class="col-md-3 d-flex align-items-end mb-3">
                        <button type="submit" class="btn btn-primary ">Filtrar</button>
                    </div>
                </div>
            </form>

            <!-- Mostrar los datos de los clientes top -->
            @if(isset($topCustomers))
                <div class="table-responsive mt-4">
                    <table class="table numeros">
                        <thead>
                            <tr>
                                <th class="nombre">#</th>
                                <th class="nombre">Nombre</th>
                                <th class="numeros">Compras</th>
                                <th class="numeros">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($topCustomers as $customer)
                                <tr>
                                    <td class="nombre">{{ $customer->id }}</td>
                                    <td class="nombre">{{ $customer->name }}</td>
                                    <td class="numeros">{{ $customer->purchases }}</td>
                                    <td class="numeros">{{ number_format($customer->total, 2) }}</td>
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
    window.reportTopCustomersUrl = "{{ route('tenant.reports.customers.top') }}";
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
