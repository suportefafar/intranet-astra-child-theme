import { Grid, html, h, PluginPosition } from "https://unpkg.com/gridjs?module";

let ITEMS = [];
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

document.querySelector("#btn_copy_emails").addEventListener("click", () => {
  console.log(ITEMS);
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
    { name: "Nome", formatter: nameColFormatter },
    "Email",
    { name: "Posição", formatter: positionColFormatter },
    {
      name: "Status",
      formatter: statusColFormatter,
    },
    {
      name: "Adimito em",
      formatter: (current) => new Date(current).toLocaleString(),
    },
  ],
  data: fetchDataHandler,
  pagination: {
    limit: 20,
    summary: true,
  },
  search: {
    selector: (cell, rowIndex, cellIndex) => {
      ITEMS.push(cell);

      return cell;
    },
  },
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

grid.plugin.add({
  id: "myfirstplugin",
  component: intranetFarAddFiltersPlugin,
  position: PluginPosition.Header,
});

function intranetFarAddFiltersPlugin() {
  return h("div", { class: "intranet-plugin-grid-js-container" }, [
    h("select", { id: "select_categoria_vinculo" }, [
      h("option", { value: "" }, "Todos"),
      h("option", {}, "Docente"),
      h("option", {}, "Técnico Administrativo"),
      h("option", {}, "Terceirizado"),
    ]),
    h("select", { id: "select_setor_vinculo" }, [
      h("option", {}, "ACT"),
      h("option", {}, "ALM"),
      h("option", {}, "FAS"),
      h("option", {}, "PFA"),
      h("option", {}, "PPGCA"),
      h("option", {}, "PPGCF"),
      h("option", {}, "PPGACT"),
      h("option", {}, "PPGMAF"),
      h("option", {}, "Colegiado de Farmácia"),
      h("option", {}, "Colegiado de Biomedicina"),
      h("option", {}, "Secretaria Geral"),
      h("option", {}, "Secretaria Executiva"),
      h("option", {}, "Diretoria"),
      h("option", {}, "Superintendência Administrativa"),
      h("option", {}, "Almoxarifado"),
      h("option", {}, "Biblioteca"),
      h("option", {}, "Contabilidade"),
      h("option", {}, "Compras"),
      h("option", {}, "Tecnologia da Informação e Suporte"),
      h("option", {}, "Centro de Memória"),
      h("option", {}, "Apoio Logístico e Operacional"),
      h("option", {}, "Biotério"),
      h("option", {}, "Gerenciamento Ambiental e Biossegurança"),
      h("option", {}, "Arquivo"),
      h("option", {}, "NAPq/CENEX"),
      h("option", {}, "Pessoal"),
      h("option", {}, "Patrimônio"),
      h("option", {}, "Assessoria de Assuntos Educacionais"),
    ]),
    h("select", { id: "select_status_vinculo" }, [
      h("option", {}, "Ativo"),
      h("option", {}, "Aposentado"),
      h("option", {}, "Desligado"),
      h("option", {}, "Removido"),
    ]),
  ]);
}

async function fetchDataHandler() {
  let response;

  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/users"
    );
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  const submissions = JSON.parse(response.data);

  console.log(submissions);

  let table_arr = [];
  for (const submission of submissions) {
    const {
      ID,
      display_name,
      user_email,
      workplace_extension,
      avatar_url,
      user_login,
      public_servant_bond_category,
      role,
      bond_status,
      user_registered,
    } = submission;

    if (ID === "1") continue;

    const name_column_data = JSON.stringify({
      display_name,
      workplace_extension,
      avatar_url,
      user_login,
    });

    const position_column_data = JSON.stringify({
      public_servant_bond_category,
      role,
    });

    table_arr.push([
      name_column_data,
      user_email,
      position_column_data,
      bond_status,
      user_registered,
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

function nameColFormatter(current) {
  const { display_name, workplace_extension, avatar_url, user_login } =
    JSON.parse(current);

  const html_content = `
    <div class="d-flex gap-2">
      <div>
        <img src="${avatar_url}" width="64" class="rounded-circle">
      </div>
      <div class="d-flex gap-2 flex-column justify-content-center">
        <strong class="mb-0">
          <a href="/membros/${user_login}/" target="blank" title="${display_name}">
            ${display_name}
          </a>
        </strong>
        ${
          workplace_extension
            ? `<div>
          <i class="bi bi-telephone text-muted" style="font-size: 1rem;"></i>
          <span class="text-muted" style="font-size: 1rem;">${workplace_extension}</span>
        <div>`
            : ""
        }
      </div>
    </div>`;

  return html(html_content);
}

function positionColFormatter(current) {
  const { public_servant_bond_category, role } = JSON.parse(current);

  return html(public_servant_bond_category + "<br />" + role.name);
}

function statusColFormatter(current) {
  let type = "text-bg-info";
  const current_lower = current ? current.toLowerCase() : "";

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
