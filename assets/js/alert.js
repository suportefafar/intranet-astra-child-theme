/*
 * A div 'intranetFafarLiveAlertPlaceholder' utilizado como âncora nesse arquivo é inserido no 'functions.php'
 */
function showAlert(
  message = "Operação realizada com sucesso!",
  type = "success",
  autohide = false,
  delay = 5000,
  loadingIcon = false
) {
  hideAlert();

  let alert_icon_class = "d-none";

  if (type === "success") {
    alert_icon_class = "bi-check-square-fill";
  } else if (type === "danger") {
    alert_icon_class = "bi-x-octagon-fill";
  } else if (type === "warning") {
    alert_icon_class = "bi-exclamation-diamond-fill";
  } else if (type === "info") {
    alert_icon_class = "bi-info-circle-fill";
  }

  let icon = `<i class="bi ${alert_icon_class}"></i>`;

  if (loadingIcon) {
    icon = `<div class="spinner-border spinner-border-sm" role="status">
              <span class="visually-hidden">Carregando...</span>
            </div>`;
  }

  const wrapper = document.createElement("div");
  wrapper.innerHTML = [
    `<div id="intranetFafarAlert" class="alert alert-${type} alert-dismissible d-flex align-items-center gap-1" role="alert">`,
    icon,
    `   <div class="mx-1">${message}</div>`,
    '   <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>',
    "</div>",
  ].join("");

  const alertPlaceholder = document.getElementById(
    "intranetFafarLiveAlertPlaceholder"
  );
  alertPlaceholder.append(wrapper);

  if (autohide) setTimeout(hideAlert, delay);
}

function hideAlert() {
  const alertPlaceholder = document.getElementById(
    "intranetFafarLiveAlertPlaceholder"
  );
  alertPlaceholder.innerHTML = "";
}

// Attach to the global window object
window.showAlert = showAlert;
window.hideAlert = hideAlert;

//console.log("Módulo de alerta carregado!");
