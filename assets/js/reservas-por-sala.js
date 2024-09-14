import { Grid, html } from "https://unpkg.com/gridjs?module";

/**
 * CALENDAR RENDER
 */
document.addEventListener("DOMContentLoaded", renderCalendar);

async function renderCalendar() {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);

  if (!urlParams.get("id") || !urlParams.get("id").length) return [];

  console.log(urlParams.get("id"));

  let response;
  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" +
        urlParams.get("id") +
        "/events"
    );
    console.log(response);
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  const raw_eventos = JSON.parse(response.data);

  console.log({ raw_eventos });

  const events = [];
  for (const evento of raw_eventos) {
    const start = getTimestampAsDateJsPattern(evento["inicio"]);
    const end = getTimestampAsDateJsPattern(evento["fim"]);

    let title = "Evento Sem Título";
    if (evento["descricao"]) title = evento["descricao"];
    if (evento["disciplina"][0]) title = evento["disciplina"][0];

    events.push({
      title,
      start,
      end,
      id: evento["id"],
    });
  }

  const calendarEl = document.getElementById("calendar");

  const calendar = new FullCalendar.Calendar(calendarEl, {
    headerToolbar: { center: "dayGridMonth,timeGridWeek" },
    initialView: "timeGridWeek",
    views: {
      week: {
        // name of view
        titleFormat: {
          month: "2-digit",
          day: "2-digit",
          year: "numeric",
        },
        // other view-specific options here
      },
    },
    eventClick: () => {},
    events,
  });
  calendar.setOption("locale", "br");
  calendar.render();
}

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

new gridjs.Grid({
  columns: [
    "ID",
    "Descrição",
    "Disciplina",
    "Inicio",
    "Fim",
    "Operador",
    "Solicitante",
    {
      name: "Ações",
      formatter: formatterHandler,
    },
  ],
  data: fetchDataHadler,
  pagination: {
    limit: 20,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

async function fetchDataHadler() {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);

  if (!urlParams.get("id") || !urlParams.get("id").length) return [];

  let response;
  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" +
        urlParams.get("id") +
        "/events"
    );
    console.log(response);
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  const eventos = JSON.parse(response.data);

  console.log(eventos);
  let eventos_tabela_arr = [];
  for (const evento of eventos) {
    const inicio_timestamp = getTimestampAsDateJsPattern(evento["inicio"]);
    const fim_timestamp = getTimestampAsDateJsPattern(evento["fim"]);

    const inicio = new Date(inicio_timestamp).toLocaleString("pt-BR", {
      timeZone: "America/Sao_Paulo",
    });
    const fim = new Date(fim_timestamp).toLocaleString("pt-BR", {
      timeZone: "America/Sao_Paulo",
    });

    eventos_tabela_arr.push([
      evento["id"],
      evento["descricao"],
      evento["disciplina"],
      inicio,
      fim,
      evento["operador"],
      evento["solicitante"],
    ]);
  }

  return eventos_tabela_arr;
}

function formatterHandler(_, row) {
  const html_content = `
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href='/vizualizar-objeto/?id=${row.cells[0].data}'>
      <i class="bi bi-ticket-detailed"></i>
    </a>
    <a class="btn btn-outline-secondary" href='/editar-reserva-por-sala/?id=${row.cells[0].data}'>
      <i class="bi bi-pencil"></i>
    </a>
    <a class="btn btn-outline-danger" href='/excluir-evento/?id=${row.cells[0].data}'>
      <i class="bi bi-trash"></i>
    </a>
  </div>  
      `;

  return html(html_content);
}

function getTimestampAsDateJsPattern(timestamp) {
  const d = new Date().getTime();

  const current_timestamp_length = d.toString().length;

  const in_timestamp_length = timestamp.toString().length;

  const length_diff = current_timestamp_length - in_timestamp_length;

  return parseInt(timestamp) * Math.pow(10, length_diff);
}
