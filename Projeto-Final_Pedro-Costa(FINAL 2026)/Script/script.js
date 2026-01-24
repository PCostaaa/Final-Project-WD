/* ----------------- NAVIGATION ----------------- */
const navMenu = document.getElementById("nav-menu"),
  navToggle = document.getElementById("nav-toggle"),
  navClose = document.getElementById("nav-close");

if (navToggle && navMenu)
  navToggle.addEventListener("click", () => navMenu.classList.add("show-menu"));
if (navClose && navMenu)
  navClose.addEventListener("click", () =>
    navMenu.classList.remove("show-menu"),
  );

/* ----------------- SEARCH ----------------- */
const search = document.getElementById("search"),
  searchBtn = document.getElementById("search-btn"),
  searchClose = document.getElementById("search-close");

if (searchBtn && search)
  searchBtn.addEventListener("click", () =>
    search.classList.add("show-search"),
  );
if (searchClose && search)
  searchClose.addEventListener("click", () =>
    search.classList.remove("show-search"),
  );

/* NUMERACAO CARRINHO */

function updateCartBadge() {
  try {
    const el = document.getElementById("cart-item-count");
    if (el && typeof count !== "undefined" && count !== null) {
      el.textContent = String(count);
      el.style.display = parseInt(count, 10) > 0 ? "inline-block" : "none";
    }
  } catch (e) {
    /* ignorar */
  }
}

document.addEventListener("DOMContentLoaded", () => {
  const el = document.getElementById("cart-item-count");
  if (el) updateCartBadge(parseInt(el.textContent || "0", 10));
});

/* ----------------- USERS ----------------- */
function openEditModal(id, username, email, role) {
  document.getElementById("editUserId").value = id;
  document.getElementById("editUsername").value = username;
  document.getElementById("editEmail").value = email;
  document.getElementById("editRole").value = role;
  new bootstrap.Modal(document.getElementById("editUserModal")).show();
}
function openDeleteModal(id) {
  document.getElementById("deleteUserId").value = id;
  new bootstrap.Modal(document.getElementById("deleteUserModal")).show();
}
document.getElementById("editUserForm")?.addEventListener("submit", (e) => {
  e.preventDefault();
  const f = e.target;
  fetch("admin_actions.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      action: "edit_user",
      id: f.id.value,
      username: f.username.value,
      email: f.email.value,
      role: f.role.value,
    }),
  })
    .then((r) => r.json())
    .then((d) => {
      if (d.success) location.reload();
      else alert(d.error);
    });
});
document.getElementById("deleteUserForm")?.addEventListener("submit", (e) => {
  e.preventDefault();
  const id = document.getElementById("deleteUserId").value;
  fetch("admin_actions.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({ action: "delete_user", id }),
  })
    .then((r) => r.json())
    .then((d) => {
      if (d.success) location.reload();
      else alert(d.error);
    });
});

/* ----------------- EVENTS ----------------- */
function openCreateEventModal() {
  openEventModal(false);
}
function openEditEventModal(
  id,
  title,
  description,
  date,
  time,
  location,
  price,
  tickets,
  imageUrl,
) {
  openEventModal(true, {
    id,
    title,
    description,
    event_date: date,
    event_time: time,
    location,
    ticket_price: price,
    available_tickets: tickets,
    image_url: imageUrl,
  });
}
function openEventModal(isEdit, data = {}) {
  const form = document.getElementById("eventForm");
  const title = document.getElementById("eventModalTitle");
  const idInput = document.getElementById("eventId");
  const imagePreview = document.getElementById("imagePreview");

  if (isEdit) {
    form.action = "edit_event.php";
    title.textContent = "Editar Evento";
    idInput.value = data.id;
    form.title.value = data.title;
    form.description.value = data.description;
    form.event_date.value = data.event_date;
    form.event_time.value = data.event_time;
    form.location.value = data.location;
    form.ticket_price.value = data.ticket_price;
    form.available_tickets.value = data.available_tickets;
    if (data.image_url) {
      imagePreview.src = data.image_url;
      imagePreview.style.display = "block";
    } else {
      imagePreview.style.display = "none";
    }
  } else {
    form.reset();
    form.action = "create_event.php";
    title.textContent = "Criar Evento";
    idInput.value = "";
    imagePreview.style.display = "none";
  }

  // Add image preview functionality
  const imageInput = document.getElementById("eventImage");
  if (imageInput) {
    imageInput.addEventListener("change", function (e) {
      const file = e.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function (e) {
          imagePreview.src = e.target.result;
          imagePreview.style.display = "block";
        };
        reader.readAsDataURL(file);
      } else {
        imagePreview.style.display = "none";
      }
    });
  }

  new bootstrap.Modal(document.getElementById("eventModal")).show();
}
function openDeleteEventModal(id) {
  if (confirm("Tem a certeza?")) {
    fetch("delete_event.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ id }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.success) location.reload();
        else alert(d.error);
      });
  }
}
document.getElementById("eventForm")?.addEventListener("submit", async (e) => {
  e.preventDefault();
  const form = e.target;
  const btn = document.getElementById("saveEventButton");
  btn.disabled = true;
  btn.textContent = "A Guardar...";
  const res = await fetch(form.action, {
    method: "POST",
    body: new FormData(form),
  });
  const data = await res.json();
  if (data.success) {
    bootstrap.Modal.getInstance(document.getElementById("eventModal")).hide();
    location.reload();
  } else alert(data.error);
  btn.disabled = false;
  btn.textContent = "Guardar";
});

/* ----------------- EVENT DETAILS MODAL ----------------- */
document.addEventListener("click", function (e) {
  const btn = e.target.closest(".open-details");
  if (!btn) return;

  const eventId = btn.getAttribute("data-event-id");
  const content = document.getElementById("eventDetailsContent");
  const buyBtn = document.getElementById("modalBuyTicketsBtn");
  content.innerHTML = "<p>A carregar detalhes do evento...</p>";
  buyBtn.classList.add("d-none");
  buyBtn.onclick = null;

  fetch("get_event_details.php?id=" + encodeURIComponent(eventId))
    .then((r) => r.json())
    .then((data) => {
      if (!data.success) {
        content.innerHTML =
          '<div class="alert alert-danger">Não foi possível carregar o evento.</div>';
        return;
      }
      const img = data.image_url
        ? `<img src="${escapeHtml(data.image_url)}" class="img-fluid rounded mb-3" alt="${escapeHtml(data.title)}">`
        : "";
      const date = data.event_date
        ? new Date(data.event_date).toLocaleDateString("pt-PT")
        : "—";
      const time = data.event_time ? data.event_time.substring(0, 5) : "—";
      content.innerHTML = `
        ${img}
        <h6 class="mb-2">${escapeHtml(data.title)}</h6>
        <p class="mb-1"><strong>Data:</strong> ${date} &nbsp; <strong>Hora:</strong> ${time}</p>
        <p class="mb-1"><strong>Local:</strong> ${escapeHtml(data.location || "—")}</p>
        <p class="mb-2"><strong>Preço:</strong> € ${Number(data.ticket_price).toFixed(2).replace(".", ",")}</p>
        <p class="mb-0"><strong>Disponíveis:</strong> ${parseInt(data.available_tickets ?? 0, 10)}</p>
      `;
      // Ativar botão COMPRAR no modal
      buyBtn.classList.remove("d-none");
      buyBtn.onclick = async () => {
        //Verifica se o USER está logado
        const checkLoginResponse = await checkLoginResponse.json();
        if (!loginStatus.isLoggedIn) {
          window.location.href =
            "login_Register.php?form=login&redirect=" +
            encodeURIComponent("events.php");
          return;
        }

        //Adiciona 1 bilhete ao carrinho
        try {
          const res = await fetch("add_to_cart.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: new URLSearchParams({ event_id: data.id, quantity: 1 }),
          });
          const out = await res.json();
          if (!out.success) {
            alert(out.error || "Não foi possível adicionar ao carrinho.");
            return;
          }
        } catch (e) {
          alert("Erro ao adicionar ao carrinho.");
          return;
        }

        window.location.href = "view_cart.php";
      };
    })
    .catch(() => {
      content.innerHTML =
        '<div class="alert alert-danger">Erro ao obter os detalhes.</div>';
    });
});

// helper simples para HTML-safe
function escapeHtml(s) {
  return String(s ?? "")
    .replaceAll("&", "&amp;")
    .replaceAll("<", "&lt;")
    .replaceAll(">", "&gt;")
    .replaceAll('"', "&quot;")
    .replaceAll("'", "&#39;");
}

const eventDetailsModal = document.getElementById("eventDetailsModal");
if (eventDetailsModal) {
  eventDetailsModal.addEventListener("show.bs.modal", async (e) => {
    // Button that triggered the modal
    const button = e.relatedTarget;
    const eventId = button.getAttribute("data-event-id");

    const modalTitle = eventDetailsModal.querySelector(".modal-title");
    const modalBody = eventDetailsModal.querySelector(".modal-body");
    const modalBuyBtn = eventDetailsModal.querySelector("#modalBuyTicketsBtn");

    // Clear previous content and show a loading message
    modalBody.innerHTML = "<p>A carregar detalhes do evento...</p>";

    try {
      // Fetch event details from a new PHP file
      const response = await fetch(`get_event_details.php?id=${eventId}`);
      const event = await response.json();

      if (response.ok && event.success) {
        // Format date and time
        const eventDate = new Date(event.event_date + "T" + event.event_time);
        const formattedDate = eventDate.toLocaleDateString("pt-PT", {
          day: "2-digit",
          month: "2-digit",
          year: "numeric",
        });
        const formattedTime = eventDate.toLocaleTimeString("pt-PT", {
          hour: "2-digit",
          minute: "2-digit",
        });

        // Update the modal's content with the event details
        modalTitle.textContent = event.title;
        modalBody.innerHTML = `
                    ${event.image_url ? `<img src="${event.image_url}" class="img-fluid rounded mb-3" alt="${event.title}">` : '<img src="https://placehold.co/400x250/E0E0E0/333333?text=Sem+Imagem" class="img-fluid rounded mb-3" alt="Sem Imagem">'}
                    <p><strong>Descrição:</strong> ${event.description}</p>
                    <p><strong>Data:</strong> ${formattedDate}</p>
                    <p><strong>Hora:</strong> ${formattedTime}</p>
                    <p><strong>Local:</strong> ${event.location}</p>
                    <p><strong>Preço:</strong> €${parseFloat(event.ticket_price).toFixed(2).replace(".", ",")}</p>
                    <p><strong>Bilhetes Disponíveis:</strong> ${event.available_tickets}</p>
                `;

        // Show the buy button and set the data attribute
        modalBuyBtn.classList.remove("d-none");
        modalBuyBtn.setAttribute("data-event-id", event.id);
      } else {
        modalBody.innerHTML =
          '<p class="text-danger">Não foi possível carregar os detalhes do evento.</p>';
      }
    } catch (error) {
      console.error("Error fetching event details:", error);
      modalBody.innerHTML =
        '<p class="text-danger">Ocorreu um erro ao carregar os detalhes.</p>';
    }
  });

  const modalBuyBtn = document.getElementById("modalBuyTicketsBtn");
  if (modalBuyBtn) {
    modalBuyBtn.addEventListener("click", async () => {
      const eventId = modalBuyBtn.getAttribute("data-event-id");

      // 1) Verificar login
      const checkLoginResponse = await fetch("check_login_status.php");
      const result = await checkLoginResponse.json();
      if (!result.isLoggedIn) {
        window.location.href =
          "login_register.php?form=login&redirect=" +
          encodeURIComponent("events.php");
        return;
      }

      // 2) Adicionar 1 bilhete ao carrinho
      try {
        const res = await fetch("add_to_cart.php", {
          method: "POST",
          credentials: "same-origin",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: new URLSearchParams({ event_id: eventId, quantity: 1 }),
        });
        const out = await res.json();
        if (out.success) {
          showCartToast("Adicionado ao carrinho.");
          updateCartBadge(out.cart_count);
        }
      } catch (e) {
        alert("Erro ao adicionar ao carrinho.");
        return;
      }

      // 3) Ir para o checkout (checkout → purchase_success.php)
      window.location.href = "view_cart.php";
    });
  }
}

// Adicionar ao Carrinho, mostrar toast
function showCartToast(msg = "Item adicionado ao carrinho.", isError = false) {
  const toastEl = document.getElementById("cartToast");
  const bodyEl = document.getElementById("cartToastBody");
  if (!toastEl || !bodyEl) return alert(msg);

  bodyEl.textContent = msg;
  toastEl.classList.toggle("text-bg-success", !isError);
  toastEl.classList.toggle("text-bg-danger", isError);

  bootstrap.Toast.getOrCreateInstance(toastE1, { delay: 2000 }).show();
  /* const toast = bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2000 });
  toast.show(); */
}

// Adicionar ao carrinho (fica na página + mostra toast)
// helper opcional (toast)
function showCartToast(msg = "Adicionado ao carrinho.", isError = false) {
  if (window.bootstrap && document.getElementById("cartToast")) {
    const toastEl = document.getElementById("cartToast");
    const bodyEl = document.getElementById("cartToastBody");
    bodyEl.textContent = msg;
    toastEl.classList.toggle("text-bg-success", !isError);
    toastEl.classList.toggle("text-bg-danger", isError);
    bootstrap.Toast.getOrCreateInstance(toastEl, { delay: 2000 }).show();
  } else {
    isError ? alert("Erro: " + msg) : alert(msg);
  }
}

document.querySelectorAll(".add-to-cart-btn").forEach((button) => {
  button.addEventListener("click", async (e) => {
    const btn = e.currentTarget; // <— importante
    const eventId = btn.getAttribute("data-event-id");
    const qtyInput = btn.closest(".d-flex")?.querySelector(".quantity-input");
    const quantity = qtyInput ? qtyInput.value || 1 : 1;

    // 1) Verificar login
    try {
      const r = await fetch("check_login_status.php", {
        credentials: "same-origin",
      });
      const st = await r.json();
      if (!st.isLoggedIn) {
        window.location.href =
          "login_register.php?form=login&redirect=" +
          encodeURIComponent(window.location.href);
        return;
      }
    } catch {
      showCartToast("Erro a verificar sessão.", true);
      return;
    }

    // 2) Adicionar ao carrinho (sem sair da página)
    try {
      const res = await fetch("add_to_cart.php", {
        method: "POST",
        credentials: "same-origin", // <— garante cookies/sessão
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: new URLSearchParams({ event_id: eventId, quantity }),
      });
      const out = await res.json();
      if (out.success) {
        showCartToast("Adicionado ao carrinho.");
        updateCartBadge(out.cart_count);
      } else {
        showCartToast(out.error || "Não foi possível adicionar.", true);
      }
    } catch {
      showCartToast("Erro ao adicionar ao carrinho.", true);
    }
  });
});

/* ----------------- PURCHASES ----------------- */
function openEditPurchaseModal(id, userId, eventId, quantity, status) {
  document.getElementById("editPurchaseId").value = id;
  document.getElementById("editPurchaseUser").value = userId;
  document.getElementById("editPurchaseEvent").value = eventId;
  document.getElementById("editPurchaseQuantity").value = quantity;
  document.getElementById("editPurchaseStatus").value = status;
  new bootstrap.Modal(document.getElementById("editPurchaseModal")).show();
}
function openDeletePurchaseModal(id) {
  document.getElementById("deletePurchaseId").value = id;
  new bootstrap.Modal(document.getElementById("deletePurchaseModal")).show();
}
document.getElementById("editPurchaseForm")?.addEventListener("submit", (e) => {
  e.preventDefault();
  const f = e.target;
  fetch("admin_actions.php", {
    method: "POST",
    headers: { "Content-Type": "application/x-www-form-urlencoded" },
    body: new URLSearchParams({
      action: "edit_purchase",
      id: f.purchase_id.value,
      user_id: f.user_id.value,
      event_id: f.event_id.value,
      quantity: f.quantity.value,
      status: f.status.value,
    }),
  })
    .then((r) => r.json())
    .then((d) => {
      if (d.success) {
        const row = document.querySelector(
          `tr[data-purchase-id="${f.purchase_id.value}"]`,
        );
        if (row) {
          row.querySelector(".purchase-quantity").textContent =
            f.quantity.value;
          row.querySelector(".purchase-status").textContent = f.status.value;
        }
        bootstrap.Modal.getInstance(
          document.getElementById("editPurchaseModal"),
        ).hide();
      } else alert(d.error);
    });
});
document
  .getElementById("deletePurchaseForm")
  ?.addEventListener("submit", (e) => {
    e.preventDefault();
    const id = document.getElementById("deletePurchaseId").value;
    fetch("admin_actions.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: new URLSearchParams({ action: "delete_purchase", id }),
    })
      .then((r) => r.json())
      .then((d) => {
        if (d.success) {
          document.querySelector(`tr[data-purchase-id="${id}"]`)?.remove();
          bootstrap.Modal.getInstance(
            document.getElementById("deletePurchaseModal"),
          ).hide();
        } else alert(d.error);
      });
  });

document.addEventListener("DOMContentLoaded", function () {
  const filterInput = document.getElementById("tableFilter");
  const table = document.querySelector(".purchase-history");
  const startDateInput = document.getElementById("startDate");
  const endDateInput = document.getElementById("endDate");
  const sortSelector = document.getElementById("sortSelector");

  function filterAndSortTable() {
    const filter = (filterInput?.value || "").toLowerCase();
    const startDate = startDateInput?.value;
    const endDate = endDateInput?.value;
    const sortBy = sortSelector?.value;

    // Convert rows to array for sorting
    let rows = Array.from(table.tBodies[0].rows);

    // Filter rows
    rows.forEach((row) => {
      const event = row.cells[0].textContent.toLowerCase();
      const status = row.cells[5].textContent.toLowerCase();
      const purchaseDate = row.cells[6].textContent; // format: YYYY-MM-DD or similar

      let show = !filter || event.includes(filter) || status.includes(filter);

      // Date filter
      if (show && (startDate || endDate)) {
        let rowDate = purchaseDate.split(" ")[0]; // get only date part if datetime
        if (startDate && rowDate < startDate) show = false;
        if (endDate && rowDate > endDate) show = false;
      }

      row.style.display = show ? "" : "none";
    });

    // Sort rows if needed
    let visibleRows = rows.filter((row) => row.style.display !== "none");

    function parseNumberPt(text) {
      // Trata valores ex"€12,50"
      return parseFloat(
        text
          .replace(/[^\d.,-]/g, "") // Mantem digitos, separadores e o sinal de menos
          .replace(/\./g, "") // Remove os pontos dos milhares
          .replace(",", "."), // converte a virgula decimal
      );
    }
    function parseDateYMD(s) {
      // espera "YYYY-MM-DD"
      const [y, m, d] = (s || "").split("-").map((n) => parseInt(n, 10));
      return isFinite(y) && isFinite(m) && isFinite(d)
        ? new Date(y, m - 1, d)
        : new Date(NaN);
    }
    function parseDateTimeYMDHM(dateStr, timeStr) {
      // combina "YYYY-MM-DD" + "HH:MM"
      const d = parseDateYMD(dateStr);
      if (isNaN(d)) return d;
      const [hh, mm] = (timeStr || "").split(":").map((n) => parseInt(n, 10));
      if (isFinite(hh) && isFinite(mm)) d.setHours(hh, mm, 0, 0);
      return d;
    }

    if (sortBy && sortBy !== "none") {
      let cmp;
      if (sortBy === "price") {
        // column 4: Total
        cmp = (a, b) =>
          parseNumberPt(a.cells[4].textContent) -
          parseNumberPt(b.cells[4].textContent);
      } else if (sortBy === "quantity") {
        // coluna 3: Quantidade
        cmp = (a, b) =>
          parseFloat(a.cells[3].textContent) -
          parseFloat(b.cells[3].textContent);
      } else if (sortBy === "date_event") {
        // colunas 1 + 2: Data + Hora
        cmp = (a, b) => {
          const da = parseDateTimeYMDHM(
            a.cells[1].textContent.trim(),
            a.cells[2].textContent.trim(),
          );
          const db = parseDateTimeYMDHM(
            b.cells[1].textContent.trim(),
            b.cells[2].textContent.trim(),
          );
          return da - db;
        };
      } else if (sortBy === "date_purchase") {
        // coluna 6: Data de compra (YYYY-MM-DD ou "YYYY-MM-DD HH:MM")
        cmp = (a, b) => {
          const sa = a.cells[6].textContent.trim().split(" ")[0];
          const sb = b.cells[6].textContent.trim().split(" ")[0];
          const da = parseDateYMD(sa),
            db = parseDateYMD(sb);
          return da - db;
        };
      }
      if (cmp) {
        visibleRows
          .sort(cmp)
          .forEach((row) => table.tBodies[0].appendChild(row));
      }
    }
  }

  if (filterInput) filterInput.addEventListener("keyup", filterAndSortTable);
  if (startDateInput)
    startDateInput.addEventListener("change", filterAndSortTable);
  if (endDateInput) endDateInput.addEventListener("change", filterAndSortTable);
  if (sortSelector) sortSelector.addEventListener("change", filterAndSortTable);
});

/* ----------------- ADMIN DASHBOARD ENHANCEMENTS ----------------- */

// Initialize admin dashboard when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  initializeAdminDashboard();
});

function initializeAdminDashboard() {
  // Animate statistics counters
  animateStatistics();

  // Add hover effects to management cards
  addCardHoverEffects();

  // Initialize table interactions
  initializeTableInteractions();

  // Add loading states for actions
  initializeActionButtons();
}

function animateStatistics() {
  const statNumbers = document.querySelectorAll(".stat-number");

  statNumbers.forEach((stat) => {
    const target = parseInt(stat.textContent.replace(/[^\d]/g, ""));
    if (!isNaN(target)) {
      animateCounter(stat, 0, target, 2000);
    }
  });
}

function animateCounter(element, start, end, duration) {
  const startTime = performance.now();
  const endTime = startTime + duration;

  function update(currentTime) {
    if (currentTime >= endTime) {
      element.textContent = end.toLocaleString();
      return;
    }

    const progress = (currentTime - startTime) / duration;
    const easeOutProgress = 1 - Math.pow(1 - progress, 3); // Ease out cubic
    const current = Math.floor(start + (end - start) * easeOutProgress);

    element.textContent = current.toLocaleString();

    requestAnimationFrame(update);
  }

  requestAnimationFrame(update);
}

function addCardHoverEffects() {
  const statCards = document.querySelectorAll(".stat-card");
  const managementCards = document.querySelectorAll(".management-card");

  statCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-8px) scale(1.02)";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0) scale(1)";
    });
  });

  managementCards.forEach((card) => {
    card.addEventListener("mouseenter", function () {
      this.style.transform = "translateY(-3px)";
    });

    card.addEventListener("mouseleave", function () {
      this.style.transform = "translateY(0)";
    });
  });
}

function initializeTableInteractions() {
  const tableRows = document.querySelectorAll(".table-row");

  tableRows.forEach((row) => {
    row.addEventListener("mouseenter", function () {
      this.style.transform = "scale(1.01)";
    });

    row.addEventListener("mouseleave", function () {
      this.style.transform = "scale(1)";
    });
  });
}

function initializeActionButtons() {
  const actionButtons = document.querySelectorAll(".btn-action");

  actionButtons.forEach((button) => {
    button.addEventListener("click", function () {
      // Add loading state
      const originalContent = this.innerHTML;
      this.innerHTML = '<i class="ri-loader-4-line rotating"></i>';
      this.disabled = true;

      // Simulate action (remove this setTimeout in production)
      setTimeout(() => {
        this.innerHTML = originalContent;
        this.disabled = false;
      }, 1000);
    });
  });
}

// Enhanced delete confirmation
function confirmDelete(message = "Tem certeza que deseja excluir este item?") {
  return new Promise((resolve) => {
    const modal = document.createElement("div");
    modal.className = "confirm-modal";
    modal.innerHTML = `
      <div class="confirm-modal-content">
        <div class="confirm-icon">
          <i class="ri-error-warning-line"></i>
        </div>
        <h3>Confirmação</h3>
        <p>${message}</p>
        <div class="confirm-buttons">
          <button class="btn-cancel">Cancelar</button>
          <button class="btn-confirm">Excluir</button>
        </div>
      </div>
    `;

    document.body.appendChild(modal);

    setTimeout(() => modal.classList.add("show"), 10);

    modal.querySelector(".btn-cancel").addEventListener("click", () => {
      modal.classList.remove("show");
      setTimeout(() => modal.remove(), 300);
      resolve(false);
    });

    modal.querySelector(".btn-confirm").addEventListener("click", () => {
      modal.classList.remove("show");
      setTimeout(() => modal.remove(), 300);
      resolve(true);
    });
  });
}

// Add CSS for confirmation modal
const confirmModalStyles = `
.confirm-modal {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 10000;
  opacity: 0;
  transition: opacity 0.3s ease;
}

.confirm-modal.show {
  opacity: 1;
}

.confirm-modal-content {
  background: white;
  border-radius: 1rem;
  padding: 2rem;
  max-width: 400px;
  width: 90%;
  text-align: center;
  transform: scale(0.9);
  transition: transform 0.3s ease;
}

.confirm-modal.show .confirm-modal-content {
  transform: scale(1);
}

.confirm-icon {
  font-size: 3rem;
  color: #ffc107;
  margin-bottom: 1rem;
}

.confirm-modal h3 {
  color: var(--title-color);
  margin-bottom: 0.5rem;
}

.confirm-modal p {
  color: var(--text-color);
  margin-bottom: 1.5rem;
}

.confirm-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
}

.confirm-buttons button {
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 0.5rem;
  font-weight: var(--font-medium);
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancel {
  background: #6c757d;
  color: white;
}

.btn-cancel:hover {
  background: #5a6268;
}

.btn-confirm {
  background: #dc3545;
  color: white;
}

.btn-confirm:hover {
  background: #c82333;
}
`;

// Inject confirmation modal styles
const style = document.createElement("style");
style.textContent = confirmModalStyles;
document.head.appendChild(style);

// Enhanced table search and filter functionality
function initializeTableSearch() {
  const searchInputs = document.querySelectorAll(".table-search");

  searchInputs.forEach((input) => {
    input.addEventListener("input", function () {
      const table =
        this.closest(".management-card").querySelector(".modern-table");
      const tbody = table.querySelector("tbody");
      const rows = tbody.querySelectorAll("tr");
      const searchTerm = this.value.toLowerCase();

      rows.forEach((row) => {
        const text = row.textContent.toLowerCase();
        const matches = text.includes(searchTerm);
        row.style.display = matches ? "" : "none";

        if (matches) {
          row.style.animation = "highlight 0.5s ease";
          setTimeout(() => (row.style.animation = ""), 500);
        }
      });
    });
  });
}

// Add highlight animation
const highlightStyles = `
@keyframes highlight {
  0% { background-color: rgba(255, 193, 7, 0.3); }
  100% { background-color: transparent; }
}
`;

const highlightStyle = document.createElement("style");
highlightStyle.textContent = highlightStyles;
document.head.appendChild(highlightStyle);

// Initialize table search when DOM is ready
document.addEventListener("DOMContentLoaded", function () {
  initializeTableSearch();
});

// Enhanced form validation
function validateForm(formId) {
  const form = document.getElementById(formId);
  if (!form) return true;

  const inputs = form.querySelectorAll(
    "input[required], select[required], textarea[required]",
  );
  let isValid = true;

  inputs.forEach((input) => {
    if (!input.value.trim()) {
      input.style.borderColor = "#dc3545";
      input.style.boxShadow = "0 0 0 0.2rem rgba(220, 53, 69, 0.25)";
      isValid = false;

      input.addEventListener(
        "input",
        function () {
          this.style.borderColor = "";
          this.style.boxShadow = "";
        },
        { once: true },
      );
    }
  });

  return isValid;
}

// Auto-refresh statistics (optional feature)
function refreshStatistics() {
  // This would typically make an AJAX call to get updated statistics
  console.log("Refreshing statistics...");

  // For demo purposes, just re-animate the counters
  animateStatistics();
}

// Refresh statistics every 5 minutes (optional)
setInterval(refreshStatistics, 300000);

// USER DASHBOARD ENHANCEMENTS
function initializeUserDashboard() {
  // Animate user statistics counters
  animateUserStatistics();

  // Initialize profile form enhancements
  initializeProfileForm();

  // Initialize purchase history table interactions
  initializePurchaseHistory();

  // Add user dashboard specific animations
  addUserDashboardAnimations();
}

function animateUserStatistics() {
  const statNumbers = document.querySelectorAll(".user-dashboard .stat-number");

  statNumbers.forEach((stat) => {
    const target = parseInt(
      stat.textContent.replace(/[^\d.,]/g, "").replace(",", ""),
    );
    if (!isNaN(target)) {
      animateCounter(stat, 0, target, 2000);
    }
  });
}

function initializeProfileForm() {
  const profileForm = document.querySelector(".profile-form-modern");

  if (profileForm) {
    // Add form validation feedback
    const inputs = profileForm.querySelectorAll(".form-input");

    inputs.forEach((input) => {
      input.addEventListener("blur", function () {
        validateInput(this);
      });

      input.addEventListener("input", function () {
        clearValidationError(this);
      });
    });

    // Handle form submission with AJAX
    profileForm.addEventListener("submit", function (e) {
      e.preventDefault(); // Prevent default form submission

      const submitBtn = this.querySelector(".btn-update-profile");
      const originalText = submitBtn.innerHTML;

      // Show loading state
      submitBtn.innerHTML =
        '<i class="ri-loader-4-line rotating"></i> Atualizando...';
      submitBtn.disabled = true;

      // Prepare form data
      const formData = new FormData(this);

      // Send AJAX request
      fetch("user.php", {
        method: "POST",
        body: formData,
      })
        .then((response) => {
          if (response.ok) {
            // Show success message
            showSuccessMessage("Perfil atualizado com sucesso!");

            // Reset button state
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;

            // Optionally reload the page after a delay to show updated data
            setTimeout(() => {
              window.location.reload();
            }, 1500);
          } else {
            throw new Error("Erro na atualização do perfil");
          }
        })
        .catch((error) => {
          console.error("Error:", error);
          showErrorMessage("Erro ao atualizar perfil. Tente novamente.");

          // Reset button state
          submitBtn.innerHTML = originalText;
          submitBtn.disabled = false;
        });
    });
  }
}

function validateInput(input) {
  const value = input.value.trim();
  let isValid = true;
  let errorMessage = "";

  if (input.type === "email") {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(value)) {
      isValid = false;
      errorMessage = "Por favor, insira um email válido.";
    }
  } else if (input.name === "username") {
    if (value.length < 3) {
      isValid = false;
      errorMessage = "O nome de utilizador deve ter pelo menos 3 caracteres.";
    }
  }

  if (!isValid) {
    showValidationError(input, errorMessage);
  } else {
    clearValidationError(input);
  }

  return isValid;
}

function showValidationError(input, message) {
  clearValidationError(input);

  input.style.borderColor = "#dc3545";
  input.style.boxShadow = "0 0 0 3px rgba(220, 53, 69, 0.1)";

  const errorElement = document.createElement("div");
  errorElement.className = "validation-error";
  errorElement.textContent = message;
  errorElement.style.cssText = `
    color: #dc3545;
    font-size: 0.8rem;
    margin-top: 0.25rem;
    display: block;
  `;

  input.parentNode.appendChild(errorElement);
}

function clearValidationError(input) {
  input.style.borderColor = "";
  input.style.boxShadow = "";

  const errorElement = input.parentNode.querySelector(".validation-error");
  if (errorElement) {
    errorElement.remove();
  }
}

function showSuccessMessage(message) {
  const successToast = document.createElement("div");
  successToast.className = "success-toast";
  successToast.innerHTML = `
    <i class="ri-check-circle-line"></i>
    ${message}
  `;
  successToast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 0.6rem;
    box-shadow: 0 8px 32px rgba(40, 167, 69, 0.3);
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: var(--font-medium);
    animation: slideInRight 0.5s ease-out;
  `;

  document.body.appendChild(successToast);

  setTimeout(() => {
    successToast.style.animation = "slideOutRight 0.5s ease-in";
    setTimeout(() => successToast.remove(), 500);
  }, 3000);
}

function showErrorMessage(message) {
  const errorToast = document.createElement("div");
  errorToast.className = "error-toast";
  errorToast.innerHTML = `
    <i class="ri-error-warning-line"></i>
    ${message}
  `;
  errorToast.style.cssText = `
    position: fixed;
    top: 20px;
    right: 20px;
    background: linear-gradient(135deg, #dc3545, #fd7e14);
    color: white;
    padding: 1rem 1.5rem;
    border-radius: 0.6rem;
    box-shadow: 0 8px 32px rgba(220, 53, 69, 0.3);
    z-index: 10000;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: var(--font-medium);
    animation: slideInRight 0.5s ease-out;
  `;

  document.body.appendChild(errorToast);

  setTimeout(() => {
    errorToast.style.animation = "slideOutRight 0.5s ease-in";
    setTimeout(() => errorToast.remove(), 500);
  }, 4000);
}

function initializePurchaseHistory() {
  const purchaseTable = document.querySelector(".user-dashboard .modern-table");

  if (purchaseTable) {
    // Add row highlighting on hover
    const rows = purchaseTable.querySelectorAll("tbody tr");

    rows.forEach((row) => {
      row.addEventListener("mouseenter", function () {
        this.style.transform = "scale(1.01)";
        this.style.zIndex = "10";
      });

      row.addEventListener("mouseleave", function () {
        this.style.transform = "scale(1)";
        this.style.zIndex = "";
      });
    });

    // Add click to expand functionality for mobile
    if (window.innerWidth <= 768) {
      rows.forEach((row) => {
        row.addEventListener("click", function () {
          this.classList.toggle("expanded");
        });
      });
    }
  }
}

function addUserDashboardAnimations() {
  // Add entrance animations for cards
  const cards = document.querySelectorAll(".user-dashboard .management-card");

  cards.forEach((card, index) => {
    card.style.opacity = "0";
    card.style.transform = "translateY(30px)";

    setTimeout(() => {
      card.style.transition = "all 0.6s cubic-bezier(0.4, 0, 0.2, 1)";
      card.style.opacity = "1";
      card.style.transform = "translateY(0)";
    }, index * 150);
  });
}

// Enhanced table search and filter for user purchases
function initializeUserTableSearch() {
  const searchInput = document.querySelector(".user-dashboard .table-search");
  const startDateInput = document.querySelector(".user-dashboard #startDate");
  const endDateInput = document.querySelector(".user-dashboard #endDate");
  const sortSelector = document.querySelector(".user-dashboard #sortSelector");

  function filterAndSortTable() {
    const table = document.querySelector(".user-dashboard .modern-table");
    if (!table) return;

    const tbody = table.tBodies[0];
    const rows = Array.from(tbody.rows);
    const searchTerm = searchInput ? searchInput.value.toLowerCase() : "";
    const startDate = startDateInput ? new Date(startDateInput.value) : null;
    const endDate = endDateInput ? new Date(endDateInput.value) : null;
    const sortBy = sortSelector ? sortSelector.value : "none";

    // Filter rows
    const visibleRows = rows.filter((row) => {
      const text = row.textContent.toLowerCase();
      const matchesSearch = !searchTerm || text.includes(searchTerm);

      // Date filtering
      let matchesDate = true;
      if (startDate || endDate) {
        const dateCell = row.cells[5]; // Purchase date column
        if (dateCell) {
          const purchaseDateText = dateCell.textContent.trim();
          const purchaseDate = new Date(
            purchaseDateText.split("/").reverse().join("-"),
          );

          if (startDate && purchaseDate < startDate) matchesDate = false;
          if (endDate && purchaseDate > endDate) matchesDate = false;
        }
      }

      return matchesSearch && matchesDate;
    });

    // Sort rows
    let cmp;
    if (sortBy === "date_purchase") {
      cmp = (a, b) => {
        const dateA = new Date(
          a.cells[5].textContent.split("/").reverse().join("-"),
        );
        const dateB = new Date(
          b.cells[5].textContent.split("/").reverse().join("-"),
        );
        return dateB - dateA;
      };
    } else if (sortBy === "date_event") {
      cmp = (a, b) => {
        const dateA = new Date(
          a.cells[1].textContent.split("/").reverse().join("-"),
        );
        const dateB = new Date(
          b.cells[1].textContent.split("/").reverse().join("-"),
        );
        return dateB - dateA;
      };
    } else if (sortBy === "price") {
      cmp = (a, b) => {
        const priceA = parseFloat(
          a.cells[3].textContent.replace("€", "").replace(",", "."),
        );
        const priceB = parseFloat(
          b.cells[3].textContent.replace("€", "").replace(",", "."),
        );
        return priceB - priceA;
      };
    } else if (sortBy === "quantity") {
      cmp = (a, b) => {
        const qtyA = parseInt(a.cells[2].textContent);
        const qtyB = parseInt(b.cells[2].textContent);
        return qtyB - qtyA;
      };
    }

    if (cmp) {
      visibleRows.sort(cmp).forEach((row) => tbody.appendChild(row));
    }

    // Update row visibility
    rows.forEach((row) => {
      row.style.display = visibleRows.includes(row) ? "" : "none";
    });
  }

  if (searchInput) searchInput.addEventListener("input", filterAndSortTable);
  if (startDateInput)
    startDateInput.addEventListener("change", filterAndSortTable);
  if (endDateInput) endDateInput.addEventListener("change", filterAndSortTable);
  if (sortSelector) sortSelector.addEventListener("change", filterAndSortTable);
}

// Initialize user dashboard when DOM is loaded
document.addEventListener("DOMContentLoaded", function () {
  if (document.querySelector(".user-dashboard")) {
    initializeUserDashboard();
    initializeUserTableSearch();
  }
});

// Add CSS for user dashboard animations
const userDashboardStyles = `
@keyframes slideInRight {
  from {
    transform: translateX(100%);
    opacity: 0;
  }
  to {
    transform: translateX(0);
    opacity: 1;
  }
}

@keyframes slideOutRight {
  from {
    transform: translateX(0);
    opacity: 1;
  }
  to {
    transform: translateX(100%);
    opacity: 0;
  }
}

@media (max-width: 768px) {
  .user-dashboard .modern-table tbody tr.expanded {
    background: linear-gradient(135deg, rgba(230, 75, 56, 0.05) 0%, rgba(248, 249, 250, 0.8) 100%);
  }

  .user-dashboard .modern-table tbody tr.expanded td {
    padding: 1rem;
  }
}
`;

// Inject user dashboard styles
const userStyle = document.createElement("style");
userStyle.textContent = userDashboardStyles;
document.head.appendChild(userStyle);
