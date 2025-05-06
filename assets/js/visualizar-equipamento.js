/*
 * LISTENER'S
 */
/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-loan-equipament' ou é filho de um elemento
 * com essa classe
 */
document.querySelector("#btn_delete").addEventListener("click", (event) => {
  const deleteButton = event.target;

  if (deleteButton) {
    const id = deleteButton.dataset.id;
    confirmDelete(id);
  }
});

/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-loan-equipament' ou é filho de um elemento
 * com essa classe
 */
document.querySelector("#btn_loan").addEventListener("click", (event) => {
  const loanButton = event.target;

  if (loanButton) {
    const id = loanButton.dataset.id;
    /*
     * Esse elemento é criado no formulário do CF7,
     * na página de administração
     */
    document.querySelector("#equipament_to_loan").value = id;
    showLoanOrReturnoEquipamentModal("intranetFafarLoanModal");
  }
});

/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-loan-return-equipament' ou é filho de um elemento
 * com essa classe
 */
document
  .querySelector("#btn_loan_return")
  .addEventListener("click", (event) => {
    const loanButton = event.target;

    if (loanButton) {
      const id = loanButton.dataset.id;
      /*
       * Esse elemento é criado no formulário do CF7,
       * na página de administração
       */
      document.querySelector("#equipament_to_return").value = id;
      showLoanOrReturnoEquipamentModal("intranetFafarLoanReturnModal");
    }
  });

/*
 * Adiciona um evento criado no CF7,
 * quando uma submissão é feita com sucesso
 */
document.addEventListener("onLoanSuccess", () => {
  window.location.reload();
});

/*
 * Adiciona um evento criado no CF7,
 * quando uma submissão é feita com sucesso
 */
document.addEventListener("onReturnLoanSuccess", () => {
  window.location.reload();
});

function confirmDelete(id) {
  showConfirmModal(
    "Excluir Disciplina?",
    "Essa ação não pode ser desfeita.",
    "Excluir",
    "danger",
    () => deleteSubmission(id)
  );
}

async function deleteSubmission(id) {
  hideConfirmModal();

  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  try {
    const response = await axios.delete(
      "/wp-json/intranet/v1/submissions/" + id
    );

    console.log(response);

    showAlert("Excluído com sucesso!", "success", true, 3000);

    setTimeout(() => (window.location = "./equipamentos"), 1000);
  } catch (error) {
    let error_msg = "[1010]Unknow error on try catch";

    if (error.response?.data?.message) {
      console.log(error.response.data);
      error_msg = error.response.data.message;
    } else {
      console.log(error);
    }

    showAlert(error_msg, "danger");
  }
}

/*
 * Controle dos Modal's de Empréstimo e Devolução
 */
function showLoanOrReturnoEquipamentModal(modal_id) {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById(modal_id)
  );

  modal.show();
}

function hideLoanOrReturnoEquipamentModal(modal_id) {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById(modal_id)
  );
  modal.hide();
}
