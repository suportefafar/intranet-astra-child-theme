import { Grid, html } from "https://unpkg.com/gridjs?module";

let EVENTS = [];

document.addEventListener("DOMContentLoaded", () => loadUI());

async function loadUI(place_id = false) {
  const queryString = window.location.search;
  const urlParams = new URLSearchParams(queryString);
  const place = urlParams.get("place");

  if (place_id) {
    place_id = place_id;
  } else if (place) {
    place_id = place;
  } else {
    place_id = document.querySelectorAll("#ul_place_tabs .nav-link")[0].dataset
      .placeId;
  }

  renderPlacesTabs(place_id);

  const submissions = await getEventsByPlaceID(place_id);

  //renderCalendar(submissions);
  updateTableData(submissions);
}

/**
 * GET EVENTS
 */
async function getEventsByPlaceID(place_id) {
  let response;
  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/" +
        place_id +
        "/events"
    );
    console.log({ response });
  } catch (error) {
    console.log(error.response.data.message);
    return [];
  }

  return JSON.parse(response.data);
}

/**
 * UL TABS RENDER
 */

function renderPlacesTabs(place_id = null) {
  const tabs = document.querySelectorAll("#ul_place_tabs .nav-link");

  tabs.forEach((el) => {
    el.addEventListener("click", onClickPlaceTabHanlder);
  });

  if (place_id) {
    changeActiveTab(place_id);
  }
}

async function onClickPlaceTabHanlder(e) {
  const place_id = e.target.dataset.placeId;

  loadUI(place_id);
}

function changeActiveTab(placeId) {
  const tabs = document.querySelectorAll("#ul_place_tabs .nav-link");

  tabs.forEach((el) => {
    el.classList.remove("active");
    if (el.dataset.placeId === placeId) el.classList.add("active");
  });
}

/**
 * CALENDAR RENDER
 */

const calendarEl = document.getElementById("calendar");

const calendar = new FullCalendar.Calendar(calendarEl, {
  locale: "pt-br",
  headerToolbar: {
    left: "prev,next",
    center: "title",
    right: "multiMonthYear,dayGridMonth,timeGridWeek,timeGridDay,listWeek", // user can switch between the two
  },
  initialView: "timeGridWeek",
  views: {
    week: {
      titleFormat: {
        month: "2-digit",
        day: "2-digit",
        year: "numeric",
      },
      eventClassNames: ["cursor-pointer", "text-decoration-none"],
    },
  },
  hiddenDays: [0],
  eventClick: viewEvent,
  events: [
    {
      title: "my recurring STRING event",
      rrule:
        "DTSTART:20240201T113000\nRRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20241201;BYDAY=MO,FR",
    },
  ],
});

calendar.render();

function renderCalendar(submissions) {
  const arr = [];
  for (const submission of submissions) {
    let title = "--";
    if (submission["discipline"] && submission["discipline"].code)
      title = submission["discipline"].code;
    else if (submission["desc"]) title = submission["desc"];

    arr.push({
      id: submission["id"],
      title,
      start: getTimestampAsDateJsPattern(submission["start"]),
      end: getTimestampAsDateJsPattern(submission["end"]),
      id: submission["id"],
      color: "#F2B600",
    });
  }

  const sources = calendar.getEventSources();

  if (sources.length > 0) sources.forEach((s) => s.remove());

  calendar.addEventSource(arr);

  calendar.refetchEvents();
}

async function viewEvent(info) {
  const { id, title, start, end } = info.event;
  // const { cod_usuario } = info.event.extendedProps;
  // const current_user = document.querySelector("#UsuarioID").value;

  const event = await getEventByID(id);

  document.querySelector("#modal_event_title").innerHTML = title;
  document.querySelector("#modal_event_start").innerHTML = new Date(
    start
  ).toLocaleString();
  document.querySelector("#modal_event_end").innerHTML = new Date(
    end
  ).toLocaleString();
  document.querySelector("#modal_event_owner").innerHTML =
    event.owner.data.display_name;

  const m = new bootstrap.Modal("#exampleModal", {
    keyboard: false,
  });
  m.show();

  console.log(event);
}

/**
 * END
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
    "Descrição",
    {
      name: "Disciplina",
      formatter: (_, row) => row.cells[2].data.code,
    },
    {
      name: "Dia",
      formatter: (_, row) => getDateStr(row.cells[3].data),
    },
    {
      name: "De",
      formatter: (_, row) => getHourStr(row.cells[4].data),
    },
    {
      name: "Até",
      formatter: (_, row) => getHourStr(row.cells[5].data),
    },
    "Operador",
    "Solicitante",
    {
      name: "Ações",
      formatter: formatterHandler,
    },
  ],
  data: [],
  pagination: {
    limit: 20,
    summary: true,
  },
  search: true,
  sort: true,
  resizable: true,
  language: ptBR,
}).render(document.getElementById("table-wrapper"));

function updateTableData(submissions) {
  let table_arr = [];
  for (const submission of submissions) {
    table_arr.push([
      submission["id"],
      submission["desc"],
      submission["discipline"],
      submission["start"],
      submission["start"],
      submission["end"],
      submission["applicant"],
      submission["owner"],
    ]);
  }

  renderGridJS(table_arr);
}

function renderGridJS(data = []) {
  if (!data) data = [];

  grid
    .updateConfig({
      data,
    })
    .forceRender();
}

function formatterHandler(_, row) {
  const html_content = `
  <div class="d-flex gap-2">
    <a class="btn btn-outline-secondary" href='/vizualizar-objeto/?id=${row.cells[0].data}' title='Detalhes'>
      <i class="bi bi-info-lg"></i>
    </a>
    <a class="btn btn-outline-secondary" href='/editar-reserva-por-sala/?id=${row.cells[0].data}' title='Editar'>
      <i class="bi bi-pencil"></i>
    </a>
    <a class="btn btn-outline-danger" href='/excluir-evento/?id=${row.cells[0].data}' title='Excluir'>
      <i class="bi bi-trash"></i>
    </a>
  </div>  
      `;

  return html(html_content);
}

/**
 * END
 */

/**
 * EXTRA
 */

async function getEventByID(id) {
  let response;
  try {
    response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/events/" + id
    );
    console.log({ response });
  } catch (error) {
    console.log(error.response.data.message);
    return false;
  }
  return JSON.parse(response.data);
}

function getFullDateStr(s) {
  const d = new Date(getTimestampAsDateJsPattern(s));

  return d.toLocaleString();
}

function getDateStr(s) {
  const d = new Date(getTimestampAsDateJsPattern(s));

  return d.toLocaleString().split(",")[0];
}

function getHourStr(s) {
  const d = new Date(getTimestampAsDateJsPattern(s));

  return d.toLocaleString().split(",")[1].slice(0, 6);
}

function getTimestampAsDateJsPattern(timestamp) {
  const d = new Date().getTime();

  const current_timestamp_length = d.toString().length;

  const in_timestamp_length = timestamp.toString().length;

  const length_diff = current_timestamp_length - in_timestamp_length;

  return parseInt(timestamp) * Math.pow(10, length_diff);
}
