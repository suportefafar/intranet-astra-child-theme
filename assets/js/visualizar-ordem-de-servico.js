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
 * Adiciona um evento de clique ao botão com ID 'btn_insert_update'
 */
document
  .querySelector("#btn_insert_update")
  .addEventListener("click", (event) => {
    /*
     * Esse elemento é criado no formulário do CF7,
     * na página de administração
     */
    document.querySelector("#service_ticket_to_update").value =
      event.target.dataset.id;
    showInsertServiceTicketUpdateModal();
  });

/*
 * Adiciona um evento criado no CF7,
 * quando uma submissão é feita com sucesso
 */
document.addEventListener("onInsertUpdateSuccess", () => {
  window.location.reload();
});

/*
 *
 */
document
  .querySelector("#select_assigned_to")
  .addEventListener("change", (event) => {
    setAssignedToUserProp(event.target.value);
  });

async function setAssignedToUserProp(user_assigned_to_id) {
  showAlert("Por favor, aguarde....", "warning");

  try {
    const service_ticket_id = getURLParam("id");

    const service_ticket_response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" +
        service_ticket_id
    );

    console.log(service_ticket_response.data);

    const service_ticket = service_ticket_response.data;

    service_ticket.data.assigned_to = user_assigned_to_id;

    service_ticket.owner = service_ticket.owner.ID;
    service_ticket.data.place = [service_ticket.data.place.id];

    const response = await axios.put(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" +
        service_ticket_id,
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

  showAlert("Por favor, aguarde....", "warning");

  try {
    const response = await axios.delete(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" + id
    );

    console.log(response);

    showAlert("Excluído com sucesso!", "success", true, 3000);

    setTimeout(() => window.history.back(), 1000);
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
function showInsertServiceTicketUpdateModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarInsertServiceTicketUpdate")
  );
  modal.show();
}

function hideInsertServiceTicketUpdateModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarInsertServiceTicketUpdate")
  );
  modal.hide();
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
  var text = "";

  if (!navigator.clipboard) {
    alert(
      "ERRO: Erro no recurso 'navigator.clipboard'. Por favor, informe ao setor de Informática."
    );
    return;
  }

  // $(".title-info")
  //   .toArray()
  //   .forEach((element, index) => {
  //     //Retira a informação de "Status" e "Técnico"
  //     if (index < $(".title-info").toArray().length - 2) {
  //       text +=
  //         $(element).text().toUpperCase().trim() +
  //         ": " +
  //         $($(".info").toArray()[index])
  //           .text()
  //           .trim()
  //           .replace(/(\r\n|\n|\r)/gm, "") +
  //         ";          ";
  //     }
  //   });

  // navigator.clipboard.writeText(text.trim()).then(
  //   () => {
  //     showAlert("Copiado!", "success", true, 3000);
  //   },
  //   function (err) {
  //     console.log(err);
  //     showAlert("Falha ao copiar!", "danger");
  //   }
  // );
}
