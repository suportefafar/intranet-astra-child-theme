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
    "Número",
    "Descrição",
    {
      name: "Andar",
      formatter: (current) => current + "ª",
    },
    "Bloco",
    "Capacidade",
    {
      name: "Tipo",
      formatter: typeColFormatter,
    },
    {
      name: "Ações",
      formatter: actionColFormatter,
    },
  ],
  data: fetchDataHandler,
  pagination: {
    limit: 25,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

async function fetchDataHandler() {
  try {
    const response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/object/place"
    );

    const submissions = JSON.parse(response.data);

    console.log(submissions);

    return submissions.map(({ id, data }) => {
      const {
        number = "N/A",
        desc = "N/A",
        floor = 0,
        block = 0,
        capacity = 0,
        object_sub_type = "N/A",
      } = data;

      const prevent_write = data.prevent_write ? "1" : "0";
      const prevent_exec = data.prevent_exec ? "1" : "0";
      const permissions = `${prevent_write}${prevent_exec}`;

      return [
        number,
        desc,
        floor,
        block,
        parseInt(capacity),
        object_sub_type,
        JSON.stringify({ id, permissions }),
      ];
    });
  } catch (error) {
    console.error(
      "Erro ao buscar dados:",
      error.response?.data?.message || error
    );
    return [];
  }
}

function typeColFormatter(current) {
  switch (current[0]) {
    case "auditorium":
      return "Auditório";
    case "classroom":
      return "Sala de aula";
    case "general":
      return "Geral";
    case "lab":
      return "Laboratório";
    case "multimedia_room":
      return "Multimídia";
    case "professor_office":
      return "Gabinete";
    default:
      return "--";
  }
}

function actionColFormatter(current, row) {
  const { id, permissions, object_sub_type } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  const html_content = `
    <div class="d-flex gap-2">

      <a class="btn btn-outline-secondary" href="/visualizar-objeto/?id=${id}" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>

      ${
        object_sub_type === "classroom" || object_sub_type === "auditorium"
          ? `<a class="btn btn-outline-primary" href="/reservas/?sala=${id}" title="Eventos">
          <i class="bi bi-calendar-week"></i>
        </a>`
          : ""
      }
      
      ${
        prevent_write
          ? ""
          : `

      <a class="btn btn-outline-secondary" href="/editar-sala/?id=${id}" title="Editar">
        <i class="bi bi-pencil"></i>
      </a>

      <button class="btn btn-outline-danger btn-delete-submission" data-id="${id}" title="Excluir">
        <i class="bi bi-trash"></i>
      </button> 
      `
      }
    </div>`;

  return gridjs.html(html_content);
}

async function renderGridJS(data = []) {
  if (data.length === 0) data = await fetchDataHandler();

  grid.updateConfig({ data }).forceRender();
}

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
  showAlert("Por favor, aguarde....", "warning");

  try {
    await axios.delete(
      `https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/${id}`
    );
    showAlert("Excluído com sucesso!", "success", true, 3000);
    renderGridJS();
  } catch (error) {
    const error_msg =
      error.response?.data?.message || "[1010] Erro desconhecido";
    console.error("Erro ao excluir:", error_msg);
    showAlert(error_msg, "danger");
  }
}
