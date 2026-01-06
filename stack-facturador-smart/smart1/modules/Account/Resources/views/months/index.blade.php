@extends('tenant.layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-12">
            <div class="card card-primary">
                <div class="card-header">
                    <div>
                        <h4 class="card-title">Meses Contables - Período {{ $period->year->format('Y') }}</h4>
                    </div>
                    <div class="card-actions">
                        <a href="{{ route('tenant.account_periods.index') }}" class="btn btn-secondary">
                            <i class="fa fa-arrow-left"></i> Volver a Períodos
                        </a>
                        <button type="button" class="btn btn-primary" @click="clickCreate">
                            <i class="fa fa-plus"></i> Nuevo Mes
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Mes</th>
                                    <th>Total Debe</th>
                                    <th>Total Haber</th>
                                    <th>Balance</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(row, index) in records" :key="index">
                                    <td>@{{ row.month_name }}</td>
                                    <td>@{{ row.total_debit }}</td>
                                    <td>@{{ row.total_credit }}</td>
                                    <td>@{{ row.balance }}</td>
                                    <td class="text-right">
                                        <div class="dropdown">
                                            <button class="btn btn-default btn-sm" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                <i class="fas fa-ellipsis-v"></i>
                                            </button>
                                            <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">
                                                <button class="dropdown-item" type="button" @click.prevent="clickViewSubDiaries(row.id)">
                                                    <i class="fas fa-book"></i> Ver Subdiarios
                                                </button>
                                                <button class="dropdown-item" type="button" @click.prevent="clickCreateSubDiary(row.id)">
                                                    <i class="fas fa-plus"></i> Crear Subdiario
                                                </button>
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
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="monthModal" tabindex="-1" role="dialog" aria-labelledby="monthModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="monthModalLabel">@{{ titleModal }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="month">Mes</label>
                        <el-date-picker
                          v-model="form.month"
                          type="month"
                          placeholder="Seleccionar mes">
                        </el-date-picker>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-primary" @click="submit">Guardar</button>
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
            resource: '/account/months',
            records: [],
            titleModal: 'Nuevo Mes',
            form: {
                id: null,
                month: null,
                account_period_id: '{{ $period->id }}'
            },
            loading: false
        },
        created() {
            this.getRecords();
        },
        methods: {
            getRecords() {
                this.loading = true;
                axios.get(`${this.resource}/records/{{ $period->id }}`)
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
            clickCreate() {
                this.titleModal = 'Nuevo Mes';
                this.form = {
                    id: null,
                    month: null,
                    account_period_id: '{{ $period->id }}'
                };
                $('#monthModal').modal('show');
            },
            clickEdit(id) {
                this.titleModal = 'Editar Mes';
                this.loading = true;
                axios.get(`${this.resource}/record/${id}`)
                    .then(response => {
                        this.form = response.data.data;
                    })
                    .catch(error => {
                        console.log(error);
                    })
                    .finally(() => {
                        this.loading = false;
                    });
                $('#monthModal').modal('show');
            },
            clickDelete(id) {
                swal({
                    title: "¿Está seguro?",
                    text: "Una vez eliminado, no podrá recuperar este mes",
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
                                swal("Error", "Ocurrió un error al eliminar el mes", "error");
                            })
                            .finally(() => {
                                this.loading = false;
                            });
                    }
                });
            },
            clickViewSubDiaries(id) {
                window.location.href = `/account/sub_diaries/month/${id}`;
            },
            clickCreateSubDiary(id) {
                window.location.href = `/account/sub_diaries/create/${id}`;
            },
            submit() {
                this.loading = true;
                axios.post(this.resource, this.form)
                    .then(response => {
                        if (response.data.success) {
                            swal("Éxito", response.data.message, "success");
                            $('#monthModal').modal('hide');
                            this.getRecords();
                        } else {
                            swal("Error", response.data.message, "error");
                        }
                    })
                    .catch(error => {
                        console.log(error);
                        swal("Error", "Ocurrió un error al guardar el mes", "error");
                    })
                    .finally(() => {
                        this.loading = false;
                    });
            }
        }
    });
</script>
@endpush 