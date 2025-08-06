/*
 * Adiciona evento de clique para exclusão
 */
document.addEventListener("click", (event) => {
  const deleteButton = event.target.closest(".btn-delete-submission");
  if (deleteButton) confirmDelete(deleteButton.dataset.id);
});

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
    navigate: (e, r) => `Página ${e} de ${r}`,
    page: (e) => `Página ${e}`,
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
    "Cód.",
    "Nome",
    "Turma",
    "Curso",
    "Natureza",
    "Vagas",
    { name: "Ações", formatter: actionColFormatter },
  ],
  search: {
    server: {
      url: (prev, keyword) => {
        const url = `${prev}?keyword=${keyword}`;
        console.log(url); // Debugging: Log the search URL
        return url;
      },
    },
  },
  pagination: {
    limit: 10, // Number of rows per page
    server: {
      url: (prev, page, limit) => {
        let url = `${prev}?limit=${limit}&offset=${page + 1}`;
        if (url.indexOf("keyword") > -1)
          url = `${prev}&limit=${limit}&offset=${page + 1}`;
        console.log(url);
        return url;
      },
    },
    summary: true, // Show pagination summary
  },
  server: {
    url: "/wp-json/intranet/v1/submissions/object/class_subject",
    then: renderDataOnTable,
    total: (data) => data.count,
  },
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

function renderDataOnTable(data) {
  // Early return if data is invalid or empty
  if (!data || !Array.isArray(data.results)) {
    return [];
  }

  // Map through the results and transform each submission
  return data.results.map((submission) => {
    const { id, data } = submission;
    const {
      code = "N/A",
      name_of_subject = "N/A",
      group = "N/A",
      course = [],
      nature_of_subject = [],
      number_vacancies_offered = 0,
    } = data;

    const prevent_write = data.prevent_write ? "1" : "0";
    const prevent_exec = data.prevent_exec ? "1" : "0";
    const permissions = `${prevent_write}${prevent_exec}`;

    return [
      code,
      name_of_subject,
      group,
      course.join(", "),
      nature_of_subject.join(","),
      number_vacancies_offered,
      JSON.stringify({ id, permissions }),
    ];
  });
}

async function renderGridJS(data = []) {
  if (data.length === 0) data = await fetchDataHandler();

  grid.updateConfig({ data }).forceRender();
}

function actionColFormatter(current) {
  const { id, permissions } = JSON.parse(current);
  const prevent_write = parseInt(permissions[0]);

  return gridjs.html(`
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="/visualizar-objeto/?id=${id}" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>
      <a class="btn btn-outline-info" href="/reservas-por-disciplina/?id=${id}" target="blank" title="Reservas dessa disciplina">
        <i class="bi bi-calendar-event"></i>
      </a>
      ${
        prevent_write
          ? ""
          : `
        <a class="btn btn-outline-secondary" href="/editar-disciplina/?id=${id}" title="Editar">
          <i class="bi bi-pencil"></i>
        </a>
        <button class="btn btn-outline-danger btn-delete-submission" data-id="${id}" title="Excluir">
          <i class="bi bi-trash"></i>
        </button>`
      }
    </div>
  `);
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
  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  try {
    await axios.delete(`/wp-json/intranet/v1/submissions/${id}`);
    showAlert("Excluído com sucesso!", "success", true, 3000);
    renderGridJS();
  } catch (error) {
    const error_msg =
      error.response?.data?.message || "[1010] Erro desconhecido";
    console.error("Erro ao excluir:", error_msg);
    showAlert(error_msg, "danger");
  }
}
