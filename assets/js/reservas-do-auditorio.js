let EVENTS = [];

/*
 * LISTENER'S
 */

/*
 * Adiciona um listener para cada aba
 */
document
  .querySelectorAll("#ul_reservation_status_tabs .nav-link")
  .forEach((el) => {
    el.addEventListener("click", onClickTabHandler);
  });

/*
 * Adiciona um evento de clique à DOM e verifica se o elemento clicado
 * ou um de seus ancestrais possui uma das classes de interesse.
 */
document.addEventListener("click", (event) => {
  const target = event.target.closest(
    ".change-status, .show-details, .set-technical"
  );

  if (!target) return;

  const { id, status } = target.dataset;

  const actions = {
    "change-status": changeStatus,
    "show-details": showDetails,
    "set-technical": prepareToShowSetTechnicalModal,
  };

  for (const className in actions) {
    if (target.classList.contains(className)) {
      actions[className](id, status);
      break;
    }
  }
});

document
  .querySelector("#btn_set_technical")
  .addEventListener("click", setTechnicalHandler);

/*
 * Aguarda até que a DOM seja carregada para inserir os eventos no calendário
 */
document.addEventListener("DOMContentLoaded", () => {
  loadUI();
});

/*
 * UL TABS
 */

function onClickTabHandler(e) {
  const { dataset } = e.target;

  loadUI(dataset);
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

function getActivedTab() {
  const tabs = document.querySelectorAll(
    "#ul_reservation_status_tabs .nav-link"
  );

  for (const tab of tabs) {
    if (tab.classList.contains("active")) return tab;
  }

  return null;
}

async function loadUI(tab_dataset = null) {
  showAlert("Por favor, aguarde...", "warning", false, 0, true);

  if (!tab_dataset) {
    tab_dataset = document.querySelectorAll(
      "#ul_reservation_status_tabs .nav-link"
    )[0].dataset;
  }

  console.log(tab_dataset);

  changeActiveTab(tab_dataset.reservationStatus);

  const submissions = await getEventsByReservationStatus(
    tab_dataset.reservationStatusSlug,
    tab_dataset.reservationOrderBy,
    tab_dataset.reservationOrderHow
  );

  renderGridJS(submissions);

  hideAlert();
}

/*
 * GET EVENTS
 */
async function getEventsByReservationStatus(
  reservationStatus,
  order_by = null,
  order_how = "asc"
) {
  let status_param = reservationStatus ?? "";

  try {
    const response = await axios.get(
      `/wp-json/intranet/v1/submissions/auditorium/reservations?status=${status_param}&${
        order_by ? "order=" + order_by + "-" + order_how : ""
      }`
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

/*
 * TABLE RENDER
 */
const ptBR = {
  search: { placeholder: "Digite uma palavra-chave..." },
  order: {
    orderAsc: "Coluna em ordem crescente",
    orderDesc: "Coluna em ordem decrescente",
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
  order: true,
  resizable: true,
  autoWidth: true,
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
      actions,
      event_date,
      start_time,
      end_time,
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
      event_date,
      start_time,
      end_time,
      technical,
    });

    const action_column_data = JSON.stringify({
      id,
      permissions,
      actions,
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

  const type = getStatusStyle(status);

  return gridjs.html(`
    <div class="d-flex flex-column gap-1">
      <div>
        <strong>${desc}</strong>
      </div>

      <div>
        <small class="mt-1 text-secondary">
          <i class="bi bi-people"></i>
          <span>${public_prediction}</span>
        </small>
      </div>

      <div>
        <small class="mt-1 badge text-bg-${type}">${status}</small>
      </div>
    </div>
    `);
}

function applicantColFormatter(current) {
  const { applicant_name, applicant_phone, applicant_email } =
    JSON.parse(current);

  return gridjs.html(`
    <div class="d-flex flex-column gap-1">
      <div>
        <strong>
          <span class="me-1"><i class="bi bi-person"></i></span>
          <span>${applicant_name}</span>
        </strong>
      </div>

      <div>
        <span class="me-1"><i class="bi bi-telephone"></i></span>
        <span>
          <a href="tel:${applicant_phone}" class="text-decoration-none" target="blank" title="Ligar para ${applicant_phone}">
            ${applicant_phone}
          </a>
        </span>
      </div>

      <div>
        <span class="me-1"><i class="bi bi-envelope"></i></span>
        <span class="text-secondary">
          <a href="mailto:${applicant_email}" class="text-decoration-none" target="blank" title="Envie email para ${applicant_email}">
            ${applicant_email}
          </a>
        </span>
      </div>
    </div>
    `);
}

function executionColFormatter(current) {
  const { event_date, start_time, end_time, technical } = JSON.parse(current);

  return gridjs.html(`
    <div class="d-flex flex-column gap-1">
      <div>
        <strong>
          <span class="me-1"><i class="bi bi-calendar3"></i></span>
          <span>${formatDateToDDMMYYYY(event_date)}</span>
        </strong>
      </div>

      <div>
        <span class="me-1"><i class="bi bi-clock"></i></span>
        <span>${start_time} - ${end_time}</span>
      </div>

      <div>
        <span class="me-1"><i class="bi bi-person-gear"></i></span>
        <span>Técnico: ${technical ? technical.data.display_name : ""}</span>
      </div>
    </div>
    `);
}

function actionColFormatter(current) {
  const { id, actions } = JSON.parse(current);

  const buttonStyles = {
    show_details: {
      btnClass: "btn-outline-secondary show-details",
      icon: "bi bi-info-lg",
      title: "Detalhes",
      status: "",
    },
    approve: {
      btnClass: "btn-outline-success change-status",
      icon: "bi bi-hand-thumbs-up",
      title: "Aprovar",
      status: "Aguardando técnico",
    },
    disapprove: {
      btnClass: "btn-outline-danger change-status",
      icon: "bi bi-hand-thumbs-down",
      title: "Desaprovar",
      status: "Desaprovada",
    },
    cancel: {
      btnClass: "btn-outline-danger change-status",
      icon: "bi bi-x-octagon",
      title: "Cancelar",
      status: "Cancelada",
    },
    set_technical: {
      btnClass: "btn-outline-warning set-technical",
      icon: "bi bi-person-gear",
      title: "Escalar técnico",
      status: "Aguardando início",
    },
    finish: {
      btnClass: "btn-outline-success change-status",
      icon: "bi bi-check-lg",
      title: "Finalizar",
      status: "Finalizada",
    },
  };

  return gridjs.html(`
    <div class="d-flex gap-2">
      ${actions
        .map((action) => {
          const { btnClass, icon, title, func, status } =
            buttonStyles[action] || {};
          return btnClass
            ? `
          <a class="btn ${btnClass}" 
             href="#" 
             title="${title}"
             data-id="${id}" 
             data-status="${status}">
            <i class="${icon}"></i>
          </a>`
            : "";
        })
        .join("")}
    </div>
  `);
}

function getStatusStyle(status) {
  if (status === "Aguardando aprovação") return "warning";
  else if (status === "Aguardando técnico") return "info";
  else if (status === "Aguardando início") return "primary";
  else if (status === "Finalizada") return "success";
  else if (status === "Desaprovada") return "danger";
  else if (status === "Cancelada") return "secondary";
  else return "info";
}

async function getEventByID(id) {
  try {
    const response = await axios.get(`/wp-json/intranet/v1/submissions/${id}`);

    return response.data;
  } catch (error) {
    showAlert("Desculpe, houve um erro!", "danger");

    console.error(error.response?.data?.message || "Erro ao buscar evento");

    return null;
  }
}

async function getTechnicalUsers(
  sector = "tecnologia_da_informacao_e_suporte"
) {
  try {
    const response = await axios.get(
      `/wp-json/intranet/v1/users/by_sector/${sector}`
    );

    return response.data;
  } catch (error) {
    showAlert("Desculpe, houve um erro!", "danger");

    console.error(error.response?.data?.message || "Erro ao buscar evento");

    return null;
  }
}

async function setTechnical(reservation_id, technical_id) {
  try {
    const response = await axios.put(
      `/wp-json/intranet/v1/submissions/reservations/${reservation_id}/set_technical`,
      { technical_id },
      {
        headers: {
          "Content-Type": "application/json",
        },
      }
    );

    return response.data;
  } catch (error) {
    showAlert("Desculpe, houve um erro!", "danger");

    console.error(error.response?.data?.message || "Erro ao buscar evento");

    return null;
  }
}

async function updateReservation(id, reservation) {
  try {
    const response = await axios.put(
      `/wp-json/intranet/v1/submissions/${id}`,
      reservation,
      {
        headers: {
          "Content-Type": "application/json",
        },
      }
    );
    return response.data;
  } catch (error) {
    showAlert("Desculpe, houve um erro!", "danger");

    console.error(error.response?.data?.message || "Erro ao buscar evento");

    return null;
  }
}

async function changeStatus(id, status) {
  console.log("Alterando status...");

  showAlert("Por favor, aguarde...", "warning", false, 0, true);

  const reservation = await getEventByID(id);

  console.log(reservation);

  // Atualiza o status
  if (reservation.data) {
    reservation.data.status = status;
  } else {
    throw new Error("[1020] Estrutura de dados inesperada");
  }

  const response = await updateReservation(reservation.id, reservation);

  if (response) {
    showAlert("Atualizado com sucesso!", "success", true, 3000);

    loadUI();
  }
}

async function showDetails(id, status) {
  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  console.log("Show Details:");

  console.log({ id, status });

  const reservation = await getEventByID(id);

  console.log(reservation);

  document.querySelector("#modal_event_title").innerHTML =
    reservation.data.desc;

  document.querySelector(
    "#modal_event_status"
  ).innerHTML = `<small class="mt-1 badge text-bg-${getStatusStyle(
    reservation.data.status
  )}">${reservation.data.status}</small>`;

  document.querySelector("#modal_event_technical").innerHTML =
    reservation.data.technical;

  document.querySelector("#modal_event_applicant_name").innerHTML =
    reservation.data.applicant_name;

  document.querySelector("#modal_event_applicant_email").innerHTML =
    reservation.data.applicant_email;

  document.querySelector("#modal_event_applicant_phone").innerHTML =
    reservation.data.applicant_phone;

  document.querySelector("#modal_event_use_own_notebook").innerHTML =
    reservation.data.use_own_notebook[0];

  document.querySelector("#modal_event_use_fafar_notebook").innerHTML =
    reservation.data.use_fafar_notebook[0];

  document.querySelector("#modal_event_use_internet_access").innerHTML =
    reservation.data.use_internet_access[0];

  document.querySelector("#modal_event_use_musical_instruments").innerHTML =
    reservation.data.use_musical_instruments[0];

  document.querySelector("#modal_event_created_at").innerHTML = formatDateTime(
    reservation.created_at
  );

  hideAlert();

  showEventDetailsModal();
}

async function prepareToShowSetTechnicalModal(id, status) {
  console.log("Set Technical:");

  console.log({ id, status });

  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  const technical_users = await getTechnicalUsers();

  console.log(technical_users);

  document.querySelector("#reservation_id").value = id;

  showSetTechnicalModal();

  hideAlert();
}

async function setTechnicalHandler() {
  showAlert("Por favor, aguarde...", "warning", false, 0, true);

  const technical_id = document.querySelector("select[name='technical']").value;

  const reservation_id = document.querySelector("#reservation_id").value;

  console.log({ technical_id, reservation_id });

  if (!technical_id) {
    hideSetTechnicalModal();
    return;
  }

  const response = await setTechnical(reservation_id, technical_id);

  if (response) {
    showAlert("Atualizado com sucesso!", "success", true, 3000);

    loadUI();
  }

  hideSetTechnicalModal();
}

/*
 * Controle dos Modal's de Empréstimo e Devolução
 */
function showEventDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarReservationDetails")
  );

  modal.show();
}

function hideEventDetailsModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarReservationDetails")
  );
  modal.hide();
}

function showSetTechnicalModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarSetTechnical")
  );

  modal.show();
}

function hideSetTechnicalModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarSetTechnical")
  );
  modal.hide();
}
