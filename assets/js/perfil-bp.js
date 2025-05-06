console.log({ teamId, teamOwnerId, labTeamUpdatePermission });

// Esconde o botão de adicionar colaborador, se for o caso
if (!labTeamUpdatePermission)
  document.querySelector("#btn-new-line").classList.add("d-none");

/*
 * Listener do botão para abir o modal de colaboradores
 */
document
  .querySelector("#btn-collaborators-list")
  .addEventListener("click", () => {
    loadCollaboratorsTable();
    showCollaboratorsListModal();
  });

/**
 * Carregar lista de colaboradores
 */
async function loadCollaboratorsTable() {
  const response = await getLaboratoryTeam();

  const laboratoryTeam = response.data;

  console.log(laboratoryTeam);

  // Quando existe o usuário acabou de adicionar um novo colaborador
  if (labTeamUpdatePermission) {
    document.querySelector("#btn-new-line").classList.remove("d-none");
  }

  const tbody = document.querySelector("#table-collaborators-list tbody");

  tbody.innerHTML = "";

  for (const collaborator of laboratoryTeam.data.collaborators) {
    const tr = document.createElement("tr");

    tr.innerHTML = `
       <tr>
        <td>
            <a href="/membros/${
              collaborator.user_login
            }" target="_blank" title="Mostrar perfil">
                ${collaborator.display_name}
            </a>
        </td>
        <td>
        ${
          labTeamUpdatePermission
            ? `
            <a class="btn btn-outline-danger btn-delete-submission" data-id="${collaborator.ID}" title="Excluir">
                <i class="bi bi-trash"></i>
            </a>`
            : ``
        }
        </td>
      </tr>`;

    tbody.append(tr);
  }
}

// Obtem o objeto da equipe de laboratório
async function getLaboratoryTeam() {
  hideConfirmModal();

  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  try {
    const response = await axios.get(
      `/wp-json/intranet/v1/submissions/laboratory-team/${teamOwnerId}`
    );

    hideAlert();

    return response;
  } catch (error) {
    const error_msg =
      error.response?.data?.message || "[1010] Erro desconhecido";
    console.error("Erro ao adicionar:", error_msg);
    showAlert(error_msg, "danger");
  }
}

function showCollaboratorsListModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarCollaboratorsList")
  );

  modal.show();
}

function hideCollaboratorsListModal() {
  const modal = bootstrap.Modal.getOrCreateInstance(
    document.getElementById("intranetFafarCollaboratorsList")
  );
  modal.hide();
}

/**
 * Adiciona uma nova linha na tabela de colaboradores
 */
document.querySelector("#btn-new-line").addEventListener("click", () => {
  insertNewLine();
  document.querySelector("#btn-new-line").classList.add("d-none");
});

async function insertNewLine() {
  const response = await getPossibleCollaborators();

  const new_collaborators = response.data;

  console.log(new_collaborators);

  const tbody = document.querySelector("#table-collaborators-list tbody");

  const tr = document.createElement("tr");

  const options_obj = new_collaborators.results.map((collaborator) => {
    return `<option value="${collaborator.ID}">${collaborator.display_name}</option>`;
  });

  tr.innerHTML = `
      <td>
          <select class="form-select" aria-label="Select de técnicos" id="new-collaborator">
            <option selected>Selecione uma técnico</option>
            ${options_obj}
          </select>
      </td>
      <td>
        <a class="btn btn-outline-success" id="btn-save-collaborator" title="Salvar">
            <i class="bi bi-check-lg"></i>
        </a>
      </td>
  `;

  tbody.append(tr);
}

// Obtem o objeto da equipe de laboratório
async function getPossibleCollaborators() {
  hideConfirmModal();

  showAlert("Por favor, aguarde....", "warning", false, 0, true);

  try {
    const response = await axios.get(
      `/wp-json/intranet/v1/submissions/laboratory-team/new_collaborators/${teamId}`
    );

    hideAlert();

    return response;
  } catch (error) {
    const error_msg =
      error.response?.data?.message || "[1010] Erro desconhecido";
    console.error("Erro ao adicionar:", error_msg);
    showAlert(error_msg, "danger");
  }
}

/**
 * Adiciona evento de clique para salvar
 */
document.addEventListener("click", (event) => {
  const saveButton = event.target.closest("#btn-save-collaborator");
  if (saveButton) {
    addCollaborator();
  }
});

/**
 * Adiciona evento de clique para remover o colaborador
 */
document.addEventListener("click", (event) => {
  const deleteButton = event.target.closest(".btn-delete-submission");
  if (deleteButton) removeCollaborator(deleteButton.dataset.id);
});

/**
 * Atualizar time do laboratório
 */
const API_CONFIG = {
  baseUrl: "/wp-json/intranet/v1/submissions",
  endpoints: {
    add: "laboratory-team/{teamId}/add",
    remove: "laboratory-team/{teamId}/remove",
  },
};

/**
 * Generic function to update team collaborators
 * @param {'add'|'remove'} action - The action to perform
 * @param {string} collaborator_id - The collaborator ID
 */
async function updateTeamCollaborator(action, collaborator_id) {
  hideConfirmModal();
  showAlert("Por favor, aguarde...", "warning", false, 0, true);

  try {
    // Validate action
    if (!["add", "remove"].includes(action)) {
      throw new Error("Invalid action specified");
    }

    // Build endpoint URL
    const endpoint = API_CONFIG.endpoints[action].replace("{teamId}", teamId);
    const url = `${API_CONFIG.baseUrl}/${endpoint}`;

    const response = await axios.put(url, { collaborator_id });

    // Validate response
    if (response.status >= 200 && response.status < 300) {
      const actionMessage = action === "add" ? "Adicionado" : "Removido";
      showAlert(`${actionMessage} com sucesso!`, "success", true, 3000);
    } else {
      throw new Error(response.data?.message || "Invalid response from server");
    }
  } catch (error) {
    const errorCode = error.response?.status || "N/A";
    const errorMsg =
      error.response?.data?.message ||
      error.message ||
      "[1010] Erro desconhecido";

    console.error(`Erro ao ${action} colaborador (${errorCode}):`, errorMsg);
    showAlert(errorMsg, "danger");
  }
}

// Specific functions for better semantics
async function addCollaborator() {
  const new_collaborator_select = document.querySelector("#new-collaborator");
  await updateTeamCollaborator("add", new_collaborator_select.value);
  loadCollaboratorsTable();
}

async function removeCollaborator(collaborator_id) {
  await updateTeamCollaborator("remove", collaborator_id);
  loadCollaboratorsTable();
}
