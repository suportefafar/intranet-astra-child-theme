import { Grid, html } from "https://unpkg.com/gridjs?module";

/*
 * LISTENER'S
 */
/*
 * Adiciona um evento de clique à DOM,
 * e despara se o elemento que recebeu o clique tem
 * a classe 'btn-loan-equipament' ou é filho de um elemento
 * com essa classe
 */
document.addEventListener("click", (event) => {
  const deleteButton = event.target.closest(".btn-delete-submission");

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
document.addEventListener("click", (event) => {
  const loanButton = event.target.closest(".btn-loan-equipament");

  if (loanButton) {
    const id = loanButton.dataset.id;
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
document.addEventListener("click", (event) => {
  const loanButton = event.target.closest(".btn-loan-return-equipament");

  if (loanButton) {
    const id = loanButton.dataset.id;
    document.querySelector("#equipament_to_return").value = id;
    showLoanOrReturnoEquipamentModal("intranetFafarLoanReturnModal");
  }
});

/*
 * Adiciona um evento de clique no botão
 * de submit dentro do modal de empréstimo
 */
document.addEventListener("onLoanSuccess", () => {
  hideLoanOrReturnoEquipamentModal("intranetFafarLoanModal");
  renderGridJS();
});

/*
 * Adiciona um evento de clique no botão
 * de submit dentro do modal de devolução
 */
document.addEventListener("onReturnLoanSuccess", () => {
  hideLoanOrReturnoEquipamentModal("intranetFafarLoanReturnModal");
  renderGridJS();
});

/*
 * CHARTS RENDER
 */

/*
 * TABLE RENDER
 */
const ptBR = {
  search: { placeholder: "Digite uma palavra-chave..." },
  sort: {
    sortAsc: "Coluna em ordem crescente",
    sortDesc: "Coluna em ordem decrescente",
  },
  pagination: {
    previous: "Anterior",
    next: "Próxima",
    navigate: function (e, r) {
      return "Página " + e + " de " + r;
    },
    page: function (e) {
      return "Página " + e;
    },
    showing: "Mostrando",
    of: "de",
    to: "até",
    results: "resultados",
  },
  loading: "Carregando...",
  noRecordsFound: "Nenhum registro encontrado",
  error: "Ocorreu um erro ao buscar os dados",
};

const grid = new gridjs.Grid({
  columns: [
    "Patrimônio.",
    "Patrimônio Int.",
    "Tipo",
    "Marca",
    "Modelo",
    "IP",
    "Sala",
    "Responsável",
    {
      name: "Status",
      formatter: statusColFormatter,
    },
    {
      name: "Ações",
      formatter: actionColFormatter,
    },
  ],
  data: fetchDataHandler,
  pagination: {
    limit: 20,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

async function fetchDataHandler() {
  let response;

  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/equipaments"
    );
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  const submissions = JSON.parse(response.data);

  console.log(submissions);

  let table_arr = [];
  for (const submission of submissions) {
    const submission_data = submission.data;

    const prevent_write = submission_data.prevent_write ? "1" : "0";
    const prevent_exec = submission_data.prevent_exec ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const action_column_data = JSON.stringify({
      id: submission.id,
      permissions,
    });

    let status_text = submission_data.status[0];
    if (submission_data.on_loan) status_text = "Emprestado";

    table_arr.push([
      submission_data.asset,
      submission_data.internal_asset,
      submission_data.object_sub_type[0],
      submission_data.brand,
      submission_data.model,
      submission_data.ip.data?.address ?? "",
      submission_data.place.data.number,
      submission_data.applicant,
      status_text,
      action_column_data,
    ]);
  }

  return table_arr;
}

async function renderGridJS(data = []) {
  if (!data) data = [];

  if (data.length === 0) data = await fetchDataHandler();

  grid
    .updateConfig({
      data,
    })
    .forceRender();
}

function statusColFormatter(current, row) {
  let type = "text-bg-info";
  const current_lower = current.toLowerCase();

  if (current_lower === "emprestado") type = "text-bg-warning";
  else if (current_lower === "ativado") type = "text-bg-primary";
  else if (
    current_lower === "desativado" ||
    current_lower === "quebrado" ||
    current_lower === "desaparecido"
  )
    type = "text-bg-danger";

  return html(`<span class="badge ${type}">${current}</span>`);
}

function actionColFormatter(current, row) {
  const { id, permissions } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  console.log(current, row);

  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/visualizar-equipamento/?id=${id}" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>
      <button class="btn btn-outline-primary btn-loan-equipament" data-id="${id}" title="Emprestar">
        <i class="bi bi-arrow-up"></i>
      </button>
      <button class="btn btn-outline-primary btn-loan-return-equipament" data-id="${id}" title="Receber">
        <i class="bi bi-arrow-down"></i>
      </button>
      ${
        prevent_write
          ? ""
          : `
      <a class="btn btn-outline-secondary" href="/editar-equipamento/?id=${id}" title="Editar">
        <i class="bi bi-pencil"></i>
      </a>
      <button class="btn btn-outline-danger btn-delete-submission" data-id="${id}" title="Excluir">
        <i class="bi bi-trash"></i>
      </button> `
      }
    </div>`;

  return html(html_content);
}

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

  showAlert("Por favor, aguarde....", "warning");

  try {
    const response = await axios.delete(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" + id
    );

    console.log(response);

    showAlert("Excluído com sucesso!", "success", true, 3000);

    renderGridJS();
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
