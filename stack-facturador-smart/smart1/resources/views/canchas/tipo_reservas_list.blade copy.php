<table class="table">
    <thead>
        <tr>
            <th>Nombre</th>
            <th>Ubicación</th>
            <th>Descripción</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($tiposReservas as $tipo)
            <tr>
                <td>{{ $tipo->nombre }}</td>
                <td>{{ $tipo->ubicacion }}</td>
                <td>{{ $tipo->capacidad }}</td>
                <td>
                    <!-- Botón para editar -->
                    <a href="{{ route('tenant.canchas_tipo.edit', $tipo->id) }}" class="btn btn-sm btn-warning">Editar</a>
                    <!-- Botón para eliminar -->
                    <form action="{{ route('tenant.canchas_tipo.destroy', $tipo->id) }}" method="POST" style="display:inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Eliminar</button>
                    </form>
                </td>
            </tr>
        @endforeach
    </tbody>
</table>


<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('form[id^="editTipoReservasForm"]').forEach(form => {
        form.addEventListener('submit', function(event) {
            console.log('Formulario enviado:', event.target.id);
        });
    });
});
</script>
