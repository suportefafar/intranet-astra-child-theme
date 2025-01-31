import { Grid, html } from "https://unpkg.com/gridjs?module";

let EVENTS = [];

/*
 * LISTENER'S
 */

/*
 * Aguarda até que a DOM seja carregada para inserir os eventos no calendário
 */
document.addEventListener("DOMContentLoaded", () => {
  loadUI();
});

async function loadUI(reservationStatus = false) {
  let CURRENT_RESERVATION_STATUS = null;

  if (reservationStatus) {
    CURRENT_RESERVATION_STATUS = reservationStatus;
  } else {
    CURRENT_RESERVATION_STATUS = document.querySelectorAll(
      "#ul_reservation_status_tabs .nav-link"
    )[0].dataset.reservationStatus;
  }

  console.log(CURRENT_RESERVATION_STATUS);

  renderReservationStatusTabs(CURRENT_RESERVATION_STATUS);

  if (CURRENT_RESERVATION_STATUS === "Todas") CURRENT_RESERVATION_STATUS = "";

  const submissions = await getEventsByReservationStatus(
    CURRENT_RESERVATION_STATUS
  );

  renderGridJS(submissions);
}

/*
 * GET EVENTS
 */
async function getEventsByReservationStatus(reservationStatus) {
  let status_param = reservationStatus ?? "";

  try {
    const response = await axios.get(
      "https://intranet.farmacia.ufmg.br/wp-json/intranet/v1/submissions/auditorium/reservations?status=" +
        status_param
    );

    console.log(response);

    return response.data;
  } catch (error) {
    console.error(
      "Failed to fetch reservations:",
      error.response?.data?.message || error
    );

    return [];
  }
}

/**
 * UL TABS RENDER
 */

function renderReservationStatusTabs(reservation_status = null) {
  const tabs = document.querySelectorAll(
    "#ul_reservation_status_tabs .nav-link"
  );

  tabs.forEach((el) => {
    el.addEventListener("click", onClickPlaceTabHanlder);
  });

  if (reservation_status) {
    changeActiveTab(reservation_status);
  }
}

function onClickPlaceTabHanlder(e) {
  const { reservationStatus } = e.target.dataset;

  loadUI(reservationStatus);
}

function changeActiveTab(reservationStatus) {
  const tabs = document.querySelectorAll(
    "#ul_reservation_status_tabs .nav-link"
  );

  tabs.forEach((el) => {
    el.classList.remove("active");
    if (el.dataset.reservationStatus === reservationStatus)
      el.classList.add("active");
  });
}

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
    { name: "Evento", formatter: aboutEventColFormatter },
    { name: "Solicitante", formatter: applicantColFormatter },
    { name: "Execução", formatter: executionColFormatter },
    { name: "Ações", formatter: actionColFormatter },
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

function renderGridJS(data = []) {
  if (!data) data = [];

  if (Array.isArray(data) && data.length === 0) data = [];

  grid
    .updateConfig({
      data: fetchDataHandler(data),
    })
    .forceRender();
}

function fetchDataHandler(submissions) {
  console.log(submissions);

  let table_arr = [];
  for (const submission of submissions) {
    const { id, data, created_at } = submission;
    const {
      desc,
      public_prediction,
      applicant_name,
      applicant_email,
      applicant_phone,
      technical,
      status,
      event_date__1,
      start_time__1,
      end_time__1,
    } = data;

    const prevent_write = data.prevent_write ? "1" : "0";
    const prevent_exec = data.prevent_exec ? "1" : "0";
    const permissions = prevent_write + prevent_exec;

    const about_event_column_data = JSON.stringify({
      desc,
      public_prediction,
      status,
    });

    const applicant_column_data = JSON.stringify({
      applicant_name,
      applicant_phone,
      applicant_email,
    });

    const execution_column_data = JSON.stringify({
      event_date: event_date__1,
      start_time: start_time__1,
      end_time: end_time__1,
      technical,
    });

    const action_column_data = JSON.stringify({
      id,
      permissions,
    });

    table_arr.push([
      about_event_column_data,
      applicant_column_data,
      execution_column_data,
      action_column_data,
    ]);
  }

  return table_arr;
}

function aboutEventColFormatter(current) {
  const { desc, public_prediction, status } = JSON.parse(current);

  let type = "info";
  if (status === "Aguardando aprovação") type = "warning";
  else if (status === "Aguardando técnico") type = "info";
  else if (status === "Aguardando início") type = "primary";
  else if (status === "Finalizada") type = "success";
  else if (status === "Desaprovada") type = "danger";
  else if (status === "Cancelada") type = "secondary";

  return html(`
    <div>
      <strong>${desc}</strong>
      <br />
      <small class="mt-1 text-secondary">
        <i class="bi bi-people"></i>
        <span>${public_prediction}</span>
      </small>
      <br />
      <small class="mt-1 badge text-bg-${type}">${status}</small>
    </div>
    `);
}

function applicantColFormatter(current) {
  const { applicant_name, applicant_phone, applicant_email } =
    JSON.parse(current);

  return html(`
    <div>
      <strong>
        <span class="me-1"><i class="bi bi-person"></i></span>
        <span>${applicant_name}</span>
      </strong>

      <br />
      <span class="me-1"><i class="bi bi-telephone"></i></span>
      <span>
        <a href="tel:${applicant_phone}" class="text-decoration-none" target="blank" title="Ligar para ${applicant_phone}">
          ${applicant_phone}
        </a>
      </span>

      <br />
      <span class="me-1"><i class="bi bi-envelope"></i></span>
      <span class="text-secondary">
        <a href="mailto:${applicant_email}" class="text-decoration-none" target="blank" title="Envie email para ${applicant_email}">
          ${applicant_email}
        </a>
      </span>
    </div>
    `);
}

function executionColFormatter(current) {
  const { event_date, start_time, end_time, technical } = JSON.parse(current);

  const event_date_locale = new Date(event_date).toLocaleDateString();

  return html(`
    <div>
      <strong>
        <span class="me-1"><i class="bi bi-calendar3"></i></span>
        <span>${event_date_locale}</span>
      </strong>
      
      <br />
      <span class="me-1"><i class="bi bi-clock"></i></span>
      <span>${start_time} - ${end_time}</span>
      
      <br />
      <span class="me-1"><i class="bi bi-person-gear"></i></span>
      <span>Técnico: ${technical ?? ""}</span>
    </div>
    `);
}

function actionColFormatter(current) {
  const { id, permissions } = JSON.parse(current);

  const prevent_write = parseInt(permissions.split("")[0]);

  //console.log(current);

  const html_content = `
    <div class="d-flex gap-2">
      <a class="btn btn-outline-secondary" href="#" title="Detalhes">
        <i class="bi bi-info-lg"></i>
      </a>
      <a class="btn btn-outline-success" href="#" title="Aprovar">
        <i class="bi bi-hand-thumbs-up"></i>
      </a>
      <a class="btn btn-outline-danger" href="#" title="Desaprovar">
        <i class="bi bi-hand-thumbs-down"></i>
      </a>
      <a class="btn btn-outline-danger" href="#" title="Cancelar">
        <i class="bi bi-x-octagon"></i>
      </a>
      <a class="btn btn-outline-warning" href="#" title="Escalar técnico">
        <i class="bi bi-person-gear"></i>
      </a>
      <a class="btn btn-outline-success" href="#" title="Finalizar">
        <i class="bi bi-check-lg"></i>
      </a>
    </div>`;

  return html(html_content);
}

function parseToLocalDateTime(dateString) {
  // Parse the input string into components
  const [datePart, timePart] = dateString.split(" ");
  const [year, month, day] = datePart.split("-").map(Number);
  const [hours, minutes, seconds] = timePart.split(":").map(Number);

  // Create a UTC Date object
  const utcDate = new Date(
    Date.UTC(year, month - 1, day, hours, minutes, seconds)
  );

  // Adjust the time to GMT-3
  const gmtMinus3Date = new Date(utcDate.getTime() - 3 * 60 * 60 * 1000);

  return gmtMinus3Date.toLocaleString();
}

/*
 * Controle dos Modal's de Empréstimo e Devolução
 */
function showEventDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarEventDetailsModal")
  );

  modal.show();
}

function hideEventDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarEventDetailsModal")
  );
  modal.hide();
}
