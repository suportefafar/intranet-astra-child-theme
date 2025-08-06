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

function confirmDelete(id) {
  showConfirmModal(
    "Excluir Sala?",
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

    setTimeout(() => (window.location = "./salas"), 1000);
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

document
  .querySelector("form.wpcf7-form")
  .addEventListener("submit", function (event) {
    const departament_assigned_to = document.querySelector(
      "select[name='departament_assigned_to']"
    );
    const apoio_logistico_e_operacional_type = document.querySelector(
      "select[name='apoio_logistico_e_operacional_type']"
    );
    const tecnologia_da_informacao_e_suporte_type = document.querySelector(
      "select[name='tecnologia_da_informacao_e_suporte_type']"
    );

    let type = apoio_logistico_e_operacional_type.value;
    if (departament_assigned_to.value === "TI") {
      type = tecnologia_da_informacao_e_suporte_type.value;
    }

    document.querySelector("input[name='type']").value = type;
  });
