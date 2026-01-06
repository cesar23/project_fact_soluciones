@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <div>
                        <h4 class="card-title">
                            @if(isset($month))
                                Subdiarios - {{ \Carbon\Carbon::parse($month->month)->format('F Y') }}
                            @else
                                Subdiarios
                            @endif
                        </h4>
                    </div>
                    <div class="card-actions">
                        @if(isset($month))
                            <a href="{{ route('tenant.account_months.index', ['period_id' => $month->account_period_id]) }}" class="btn btn-secondary">
                                <i class="fa fa-arrow-left"></i> Volver a Meses
                            </a>
                            <button class="btn btn-warning" @click="checkAdjustments">
                                <i class="fa fa-calculator"></i> Verificar Ajustes
                            </button>
                            <a href="{{ route('tenant.account_sub_diaries.create', ['month_id' => $month->id]) }}" class="btn btn-primary">
                                <i class="fa fa-plus"></i> Nuevo Subdiario
                            </a>
                        @else
                            <button class="btn btn-warning" @click="checkAdjustments">
                                <i class="fa fa-calculator"></i> Verificar Ajustes
                            </button>
                            <a href="{{ route('tenant.account_periods.index') }}" class="btn btn-primary">
                                <i class="fa fa-list"></i> Ver Períodos
                            </a>
                        @endif
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Fecha</th>
                                    <th>Descripción</th>
                                    <th>Libro</th>
                                    <th>Completado</th>
                                    <th>Total Debe</th>
                                    <th>Total Haber</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, index) in records" :key="index">
                                    <td>@{{ row.code }}</td>
                                    <td>@{{ row.date }}</td>
                                    <td>@{{ row.description }}</td>
                                    <td>@{{ row.book_code }}</td>
                                    <td>
                                        <span v-if="row.complete" class="badge badge-success">Sí</span>
                                        <span v-else class="badge badge-danger">No</span>
                                    </td>
                                    <td>@{{ row.total_debit }}</td>
                                    <td>@{{ row.total_credit }}</td>
                                    <td class="text-right">
                                        <div class="dropdown">
                                            <button class="btn btn-default btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <button class="dropdown-item" type="button" @click.prevent="clickEdit(row.id)">
                                                    <i class="fas fa-edit"></i> Editar
                                                </button>
                                                <button class="dropdown-item" type="button" @click.prevent="clickDelete(row.id)">
                                                    <i class="fas fa-trash"></i> Eliminar
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="records.length === 0">
                                    <td colspan="8" class="text-center">No hay registros</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script type="text/javascript">
    var app = new Vue({
        el: '#main-wrapper',
        data: {
            resource: '/account/sub_diaries',
            records: [],
            loading: false,
            @if(isset($month))
            month_id: '{{ $month->id }}',
            @else
            month_id: null,
            @endif
        },
        created() {
            this.getRecords();
        },
        methods: {
            getRecords() {
                this.loading = true;
                let url = this.month_id ? `${this.resource}/records/${this.month_id}` : `${this.resource}/records`;
                axios.get(url)
                    .then(response => {
                        this.records = response.data.data;
                    })
                    .catch(error => {
                        console.log(error);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            },
            clickEdit(id) {
                // Implementar la edición
            },
            clickDelete(id) {
                swal({
                    title: "¿Está seguro?",
                    text: "Una vez eliminado, no podrá recuperar este subdiario",
                    icon: "warning",
                    buttons: true,
                    dangerMode: true,
                })
                .then((willDelete) => {
                    if (willDelete) {
                        this.loading = true;
                        axios.delete(`${this.resource}/${id}`)
                            .then(response => {
                                if (response.data.success) {
                                    swal("Éxito", response.data.message, "success");
                                    this.getRecords();
                                } else {
                                    swal("Error", response.data.message, "error");
                                }
                            })
                            .catch(error => {
                                console.log(error);
                                swal("Error", "Ocurrió un error al eliminar el subdiario", "error");
                            })
                            .finally(() => {
                                this.loading = false;
                            });
                    }
                });
            },
            checkAdjustments() {
                swal({
                    title: "¿Verificar ajustes automáticos?",
                    text: "Se verificarán todos los subdiarios y se aplicarán ajustes automáticos donde sea necesario",
                    icon: "info",
                    buttons: true,
                    dangerMode: false,
                })
                .then((willCheck) => {
                    if (willCheck) {
                        this.loading = true;
                        axios.post(`${this.resource}/check-adjustments`)
                            .then(response => {
                                if (response.data.success) {
                                    swal("Éxito", response.data.message, "success");
                                    this.getRecords();
                                } else {
                                    swal("Error", response.data.message, "error");
                                }
                            })
                            .catch(error => {
                                console.log(error);
                                swal("Error", "Ocurrió un error al verificar los ajustes", "error");
                            })
                            .finally(() => {
                                this.loading = false;
                            });
                    }
                });
            }
        }
    });
</script>
@endpush