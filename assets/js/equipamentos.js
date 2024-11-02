import { Grid, html } from "https://unpkg.com/gridjs?module";

/**
 * CHARTS RENDER
 */

/**
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
    "ID",
    "Patrimônio.",
    "Patrimônio Int.",
    "Tipo",
    "Marca",
    "Modelo",
    "Sala",
    "Responsável",
    {
      name: "Ações",
      formatter: formatterHandler,
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
    const prevent_write = submission["prevent_write"] ? "1" : "0";
    const prevent_exec = submission["prevent_exec"] ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    table_arr.push([
      submission["id"],
      submission["asset"],
      submission["internal_asset"],
      submission["object_sub_type"][0],
      submission["brand"],
      submission["model"],
      submission["place"]["number"],
      submission["applicant"],
      permissions,
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

function formatterHandler(_, row) {
  const id = row.cells[0].data;

  const prevent_write = parseInt(row.cells.at(-1).data.split("")[0]);

  console.log(_, row);

  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/vizualizar-objeto/?id=${id}" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>
      <button class="btn btn-outline-primary btn-loan-equipament" data-id="${id}" title="Emprestar">
        <i class="bi bi-arrow-down-up"></i>
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
    document.querySelector("#equipament").value = id;
    showLoanModal("Emprestar Equipamento");
  }
});

function confirmDelete(id) {
  showConfirmModal(
    "Excluir Disciplina?",
    "Essa ação não pode ser refeita.",
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
 * Modal de empréstimos
 */

function showLoanModal(
  title = "Delete Folder?",
  body = "This can't be undone",
  confirm_btn_text = "Delete",
  confirm_btn_type = "danger",
  onAcceptCB = () => {},
  onDenyCB = () => {}
) {
  const intranetFafarLoanModal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarLoanModal")
  );

  const modal_title = document.querySelector(
    "#intranetFafarLoanModal .modal-title"
  );
  modal_title.innerText = title;

  // const modal_body = document.querySelector(
  //   "#intranetFafarLoanModal .modal-body"
  // );
  // modal_body.innerText = body;

  // const modal_btn_accept = document.querySelector("#btn_accept");
  // modal_btn_accept.classList = "";
  // modal_btn_accept.classList.add("btn");
  // modal_btn_accept.classList.add("btn-" + confirm_btn_type);
  // modal_btn_accept.innerText = confirm_btn_text;
  // modal_btn_accept.addEventListener("click", onAcceptCB);

  // const modal_btn_deny = document.querySelector("#btn_deny");
  // modal_btn_deny.addEventListener("click", onDenyCB);

  intranetFafarLoanModal.show();
}

function hideLoanModal() {
  const intranetFafarLoanModal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarLoanModal")
  );
  intranetFafarLoanModal.hide();
}
