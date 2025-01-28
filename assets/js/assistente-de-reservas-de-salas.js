import { Grid, html } from "https://unpkg.com/gridjs?module";

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

const gridjs = new Grid({
  columns: [
    "ID",
    "Numero",
    "Capacidade",
    {
      name: "Ações",
      formatter: formatterHandler,
    },
  ],
  data: () => [],
  pagination: {
    limit: 20,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

function formatterHandler(_, row) {
  const html_content = `
  <div class="d-flex gap-2">
    <a class="btn btn-outline-primary" href='/adicionar-reserva-por-sala/?id=${row.cells[0].data}'>
      <i class="bi bi-calendar-week"></i>
    </a>
  </div>  
      `;

  return html(html_content);
}

/**
 * 'Buscar Salas' FORM HANDLER
 */
const form = document.querySelector("#form-buscar-salas");

form.addEventListener("submit", onSubmitHandler);

async function onSubmitHandler(e) {
  e.preventDefault();

  const dia = document.querySelector("input[name='dia_evento']").value;
  const inicio = document.querySelector("input[name='inicio_evento']").value;
  const fim = document.querySelector("input[name='fim_evento']").value;
  const capacidade = document.querySelector("input[name='capacidade']").value;

  const data = { dia, inicio, fim, capacidade };

  let response = {};
  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/sala/available",
      { params: data }
    );
    console.log(response);
  } catch (error) {
    console.log(error);
    return;
  }

  const raw_salas = JSON.parse(response.data);

  const salas = [];
  for (const sala of raw_salas) {
    salas.push([sala["id"], sala["numero"], sala["capacidade"]]);
  }

  document.getElementById("table-wrapper").classList.remove("d-none");

  gridjs
    .updateConfig({
      search: true,
      data: salas,
    })
    .forceRender();
}
