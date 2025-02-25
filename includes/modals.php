<!-- MODAL PARA BUSCAR CLIENTE -->
<div class="modal fade" id="modalClientes" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar Cliente</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="buscar_cliente" class="form-control" placeholder="Buscar cliente...">
                <div id="spinner_clientes" class="text-center mt-3" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
                <ul id="lista_clientes" class="list-group mt-2"></ul>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA BUSCAR PRODUCTO -->
<div class="modal fade" id="modalProductos" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="text" id="buscar_producto" class="form-control" placeholder="Buscar producto...">
                <div id="spinner_productos" class="text-center mt-3" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
                <ul id="lista_productos_modal" class="list-group mt-2"></ul>
            </div>
        </div>
    </div>
</div>
