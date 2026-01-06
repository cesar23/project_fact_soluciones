<!-- resources/views/comercio/footer.blade.php -->
@php
  $configuration_ecommerce = \App\Models\Tenant\ConfigurationEcommerce::first();
@endphp
<footer class="footer mt-auto py-3 custom-footer">
    <div class="container footer-container">
        <a class="footer-logo" href="{{ route('tenant.comercios.records') }}">
            <img src="{{ $logo_url }}" alt="Logo" class="logo">
</a>
        <div class="footer-links">
            <a href="{{ route('tenant.comercios.records') }}" class="footer-link">Inicio</a>
            <!-- <a href="#" class="footer-link">Contacto</a> -->
        </div>
<div class="footer-contact">
    <ul class="contact-details">
        <li>{{ $contact_name }}</li>
        <li>Correo: {{ $contact_email }}</li>
        <li>Celular: {{ $contact_phone }}</li>
        <li>Dirección: {{ $contact_address }}</li>
    </ul>
</div>
<div class="footer-social">
    <a href="{{ $link_facebook }}" class="social-icon facebook" target="_blank"><i class="bi bi-facebook"></i></a>
    <a href="{{ $link_twitter }}" class="social-icon twitter" target="_blank"><i class="bi bi-twitter"></i></a>
    <a href="{{ $link_instagram }}" class="social-icon instagram" target="_blank"><i class="bi bi-instagram"></i></a>
    <a href="javascript:void(0)" class="bi bi-google-play text-white" onclick="openQrModal()">
        
    </a>
</div>
    </div>
    <div class="text-center mt-3">
        <span class="text-muted">
          @if($configuration_ecommerce->copyrigth_text)
            {{ $configuration_ecommerce->copyrigth_text }}
          @else
            © 2025 Factura Perú. Todos los derechos reservados.
          @endif
          </span>
    </div>
</footer>

<!-- Modal para mostrar el QR (menos ancho) -->
<div class="modal fade" id="qrModal" tabindex="-1" role="dialog" aria-labelledby="qrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="qrModalLabel">Descarga nuestra app</h5>

      </div>
      <div class="modal-body text-center">
        <img id="qrImage" src="" alt="QR Code" class="img-fluid">
      </div>
      <div class="modal-footer">
      </div>
    </div>
  </div>
</div>

<script>
function openQrModal() {
  // Establecer la URL de la imagen
  document.getElementById('qrImage').src = '/users/download-qr-store';
  
  // Abrir el modal
  $('#qrModal').modal('show');
}
</script>
