<!-- Módulos dos Eventos -->
<div class="modal fade" id="eventModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="eventForm" method="post" enctype="multipart/form-data">
        <div class="modal-header">
          <h5 class="modal-title" id="eventModalTitle">Criar Evento</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id" id="eventId">

          <div class="row g-3">
            <!-- Título -->
            <div class="col-12">
              <label for="eventTitle" class="form-label">
                <i class="ri-calendar-event-line me-2"></i>Título do Evento
              </label>
              <input type="text" name="title" id="eventTitle" class="form-control" placeholder="Digite o título do evento" required>
            </div>

            <!-- Descrição -->
            <div class="col-12">
              <label for="eventDescription" class="form-label">
                <i class="ri-file-text-line me-2"></i>Descrição
              </label>
              <textarea name="description" id="eventDescription" class="form-control" rows="3" placeholder="Descreva o evento..." required></textarea>
            </div>

            <!-- Imagem -->
            <div class="col-12">
              <label for="eventImage" class="form-label">
                <i class="ri-image-line me-2"></i>Imagem do Evento
              </label>
              <input type="file" name="event_image" id="eventImage" class="form-control" accept="image/*">
              <div class="form-text">
                <small class="text-muted">
                  <i class="ri-information-line me-1"></i>
                  Tamanho recomendado: 400x250 pixels. A imagem será automaticamente ajustada para manter as proporções.
                </small>
              </div>
              <img id="imagePreview" src="#" alt="Preview" class="d-none mt-2" style="max-width: 200px; max-height: 125px; border-radius: 0.5rem;">
            </div>

            <!-- Data e Hora -->
            <div class="col-md-6">
              <label for="eventDate" class="form-label">
                <i class="ri-calendar-line me-2"></i>Data
              </label>
              <input type="date" name="event_date" id="eventDate" class="form-control" required>
            </div>

            <div class="col-md-6">
              <label for="eventTime" class="form-label">
                <i class="ri-time-line me-2"></i>Hora
              </label>
              <input type="time" name="event_time" id="eventTime" class="form-control" required>
            </div>

            <!-- Local -->
            <div class="col-12">
              <label for="eventLocation" class="form-label">
                <i class="ri-map-pin-line me-2"></i>Local
              </label>
              <input type="text" name="location" id="eventLocation" class="form-control" placeholder="Local do evento" required>
            </div>

            <!-- Preço e Bilhetes -->
            <div class="col-md-6">
              <label for="eventPrice" class="form-label">
                <i class="ri-money-euro-circle-line me-2"></i>Preço (€)
              </label>
              <input type="number" step="0.01" name="ticket_price" id="eventPrice" class="form-control" placeholder="0.00" min="0" required>
            </div>

            <div class="col-md-6">
              <label for="eventTickets" class="form-label">
                <i class="ri-ticket-line me-2"></i>Bilhetes Disponíveis
              </label>
              <input type="number" name="available_tickets" id="eventTickets" class="form-control" placeholder="100" min="1" required>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="ri-close-line me-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-primary" id="saveEventButton">
            <i class="ri-save-line me-1"></i>Guardar Evento
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Módulo para editar USER -->
<div class="modal fade" id="editUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <form id="editUserForm">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-user-settings-line me-2"></i>Editar Utilizador
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="id" id="editUserId">

          <div class="row g-2">
            <!-- Username -->
            <div class="col-12">
              <label for="editUsername" class="form-label">
                <i class="ri-user-line me-2"></i>Username
              </label>
              <input type="text" name="username" id="editUsername" class="form-control" required>
            </div>

            <!-- Email -->
            <div class="col-12">
              <label for="editEmail" class="form-label">
                <i class="ri-mail-line me-2"></i>Email
              </label>
              <input type="email" name="email" id="editEmail" class="form-control" required>
            </div>

            <!-- Role -->
            <div class="col-12">
              <label for="editRole" class="form-label">
                <i class="ri-shield-user-line me-2"></i>Posição
              </label>
              <select name="role" id="editRole" class="form-select" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="ri-close-line me-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="ri-save-line me-1"></i>Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Módulo para apagar USER -->
<div class="modal fade" id="deleteUserModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <form id="deleteUserForm">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-user-unfollow-line me-2"></i>Apagar Utilizador
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body text-center">
          <input type="hidden" name="id" id="deleteUserId">
          <div class="mb-3">
            <i class="ri-alert-line text-warning" style="font-size: 3rem;"></i>
          </div>
          <p class="mb-1">Tem a certeza?</p>
          <p class="text-muted small mb-0">Esta ação não pode ser desfeita.</p>
        </div>

        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
            <i class="ri-close-line me-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-danger" id="confirmDeleteUser">
            <i class="ri-user-unfollow-line me-1"></i>Eliminar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Módulo para editar compras -->
<div class="modal fade" id="editPurchaseModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <form id="editPurchaseForm">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-shopping-cart-line me-2"></i>Editar Compra
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body">
          <input type="hidden" name="purchase_id" id="editPurchaseId">

          <div class="row g-2">
            <!-- ID Utilizador -->
            <div class="col-12">
              <label for="editPurchaseUser" class="form-label">
                <i class="ri-user-line me-2"></i>ID Utilizador
              </label>
              <input type="number" name="user_id" id="editPurchaseUser" class="form-control" required>
            </div>

            <!-- ID Evento -->
            <div class="col-12">
              <label for="editPurchaseEvent" class="form-label">
                <i class="ri-calendar-event-line me-2"></i>ID Evento
              </label>
              <input type="number" name="event_id" id="editPurchaseEvent" class="form-control" required>
            </div>

            <!-- Quantidade -->
            <div class="col-12">
              <label for="editPurchaseQuantity" class="form-label">
                <i class="ri-numbers-line me-2"></i>Quantidade
              </label>
              <input type="number" name="quantity" id="editPurchaseQuantity" class="form-control" min="1" required>
            </div>

            <!-- Status -->
            <div class="col-12">
              <label for="editPurchaseStatus" class="form-label">
                <i class="ri-information-line me-2"></i>Status
              </label>
              <select name="status" id="editPurchaseStatus" class="form-select" required>
                <option value="pending">Pendente</option>
                <option value="completed">Completado</option>
                <option value="cancelled">Cancelado</option>
              </select>
            </div>
          </div>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            <i class="ri-close-line me-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-primary">
            <i class="ri-save-line me-1"></i>Guardar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>

<!-- Delete Purchase Modal -->
<div class="modal fade" id="deletePurchaseModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered modal-sm">
    <div class="modal-content">
      <form id="deletePurchaseForm">
        <div class="modal-header">
          <h5 class="modal-title">
            <i class="ri-delete-bin-line me-2"></i>Apagar Compra
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>

        <div class="modal-body text-center">
          <input type="hidden" name="purchase_id" id="deletePurchaseId">
          <div class="mb-3">
            <i class="ri-alert-line text-warning" style="font-size: 3rem;"></i>
          </div>
          <p class="mb-1">Tem a certeza?</p>
          <p class="text-muted small mb-0">Esta ação não pode ser desfeita.</p>
        </div>

        <div class="modal-footer justify-content-center">
          <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">
            <i class="ri-close-line me-1"></i>Cancelar
          </button>
          <button type="submit" class="btn btn-danger" id="confirmDeletePurchase">
            <i class="ri-delete-bin-line me-1"></i>Eliminar
          </button>
        </div>
      </form>
    </div>
  </div>
</div>
