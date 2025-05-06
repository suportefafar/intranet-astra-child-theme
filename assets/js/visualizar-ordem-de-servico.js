/*
 * LISTENER'S
 */
/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-loan-equipament' ou é filho de um elemento
 * com essa classe
 */
const btn_delete = document.querySelector("#btn_delete");
if (btn_delete) {
  btn_delete.addEventListener("click", (event) => {
    const deleteButton = event.target;

    if (deleteButton) {
      const id = deleteButton.dataset.id;
      confirmDelete(id);
    }
  });
}

/*
 * Adiciona um evento criado no CF7,
 * quando uma submissão é feita com sucesso
 */
document.addEventListener("onInsertUpdateSuccess", () => {
  goToReservationList();
});

/*
 * Listener para o select de técnico atribuido
 */
const select_assigned_to = document.querySelector("#select_assigned_to");
if (select_assigned_to) {
  select_assigned_to.addEventListener("change", (event) => {
    setAssignedToUserProp(event.target.value);
  });
}

/*
 * Listener para o botão copiar dados da OS
 */
const btn_copy_data = document.querySelector("#btn_copy_data");
if (btn_copy_data) {
  btn_copy_data.addEventListener("click", copyToClipboard);
}

async function setAssignedToUserProp(user_assigned_to_id) {
  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  try {
    const service_ticket_id = getURLParam("id");

    const service_ticket_response = await axios.get(
      "/wp-json/intranet/v1/submissions/" + service_ticket_id
    );

    console.log(service_ticket_response.data);

    const service_ticket = service_ticket_response.data;

    service_ticket.data.assigned_to = user_assigned_to_id;

    service_ticket.owner = service_ticket.owner.ID;
    if (service_ticket.data.place)
      service_ticket.data.place = [service_ticket.data.place.id];

    const response = await axios.put(
      "/wp-json/intranet/v1/submissions/" + service_ticket_id,
      service_ticket,
      {
        headers: {
          "Content-Type": "application/json",
        },
      }
    );

    showAlert("Atualizado!", "success", true, 3000);
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

function confirmDelete(id) {
  showConfirmModal(
    "Excluir Ordem de Serviço?",
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

    goToReservationList();
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

function getURLParam(param) {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  return urlParams.get(param);
}

/*
 * Copiar dados da ordem de serviço para o DEMAI
 */
function copyToClipboard() {
  if (!navigator.clipboard) {
    showAlert("Seu navegador não tem suporte à essa funcionalidade.");
    return;
  }

  const text = "OS:" + OS_NUMBER + "; Relato:" + USER_REPORT;

  navigator.clipboard.writeText(text.trim()).then(
    () => {
      showAlert("Copiado!", "success", true, 3000);
    },
    (err) => {
      console.log(err);
      showAlert("Falha ao copiar!", "danger");
    }
  );
}

function goToReservationList() {
  window.location = "/ordens-de-servico-recebidas/";
}
