function showConfirmModal(
  title = "Delete Folder?",
  body = "This can't be undone",
  confirm_btn_text = "Delete",
  confirm_btn_type = "danger",
  onAcceptCB = () => {},
  onDenyCB = () => {}
) {
  const intranetFafarConfirmModal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarConfirmModal")
  );

  const modal_title = document.querySelector(
    "#intranetFafarConfirmModal .modal-title"
  );
  modal_title.innerText = title;

  const modal_body = document.querySelector(
    "#intranetFafarConfirmModal .modal-body"
  );
  modal_body.innerText = body;

  const modal_btn_accept = document.querySelector("#btn_accept");
  modal_btn_accept.classList = "";
  modal_btn_accept.classList.add("btn");
  modal_btn_accept.classList.add("btn-" + confirm_btn_type);
  modal_btn_accept.innerText = confirm_btn_text;
  modal_btn_accept.addEventListener("click", onAcceptCB);

  const modal_btn_deny = document.querySelector("#btn_deny");
  modal_btn_deny.addEventListener("click", onDenyCB);

  intranetFafarConfirmModal.show();
}

function hideConfirmModal() {
  const intranetFafarConfirmModal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarConfirmModal")
  );
  intranetFafarConfirmModal.hide();
}
