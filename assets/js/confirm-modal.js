function showConfirmModal(
  title = "Delete Folder?",
  body = "This can't be undone",
  confirm_btn_text = "Delete",
  confirm_btn_type = "danger",
  onAcceptCB = () => {},
  onDenyCB = () => {},
  allowHTML = false
) {
  // Validate modal existence
  const modalEl = document.getElementById("intranetFafarConfirmModal");
  if (!modalEl) {
    console.error("Confirm modal element not found");
    return;
  }

  // Cache DOM elements
  const elements = {
    title: modalEl.querySelector(".modal-title"),
    body: modalEl.querySelector(".modal-body"),
    acceptBtn: modalEl.querySelector("#btn_accept"),
    denyBtn: modalEl.querySelector("#btn_deny"),
  };

  // Validate required elements
  if (Object.values(elements).some((el) => !el)) {
    console.error("One or more modal elements are missing");
    return;
  }

  // Set modal content
  elements.title.textContent = title;
  elements.body[allowHTML ? "innerHTML" : "textContent"] = body;

  // Configure buttons
  const configureButton = (btnElement, { text, type }) => {
    btnElement.className = `btn btn-${type}`;
    btnElement.textContent = text;
  };

  configureButton(elements.acceptBtn, {
    text: confirm_btn_text,
    type: confirm_btn_type,
  });
  configureButton(elements.denyBtn, { text: "Cancelar", type: "secondary" });

  // Event handler cleanup system
  const cleanup = () => {
    elements.acceptBtn.removeEventListener("click", handleAccept);
    elements.denyBtn.removeEventListener("click", handleDeny);
    modalEl.removeEventListener("hidden.bs.modal", cleanup);
  };

  // Create modal instance
  const modal = bootstrap.Modal.getOrCreateInstance(modalEl);

  // Event handlers
  const handleAccept = () => {
    onAcceptCB();
    modal.hide();
  };

  const handleDeny = () => {
    onDenyCB();
    modal.hide();
  };

  // Remove previous listeners and add new ones
  cleanup(); // Cleanup any existing listeners
  elements.acceptBtn.addEventListener("click", handleAccept);
  elements.denyBtn.addEventListener("click", handleDeny);
  modalEl.addEventListener("hidden.bs.modal", cleanup);

  // Show modal
  modal.show();
}

function hideConfirmModal() {
  const modalEl = document.getElementById("intranetFafarConfirmModal");
  if (modalEl) {
    bootstrap.Modal.getInstance(modalEl)?.hide();
  }
}

// Attach to the global window object
window.showConfirmModal = showConfirmModal;
window.hideConfirmModal = hideConfirmModal;

//console.log("Módulo de modal carregado!");
