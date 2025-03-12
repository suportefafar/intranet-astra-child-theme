function formatDateTime(datetimeStr) {
  const date = new Date(datetimeStr);

  // Ajusta para UTC-3 (Brasília)
  date.setHours(date.getHours() - 3);

  return new Intl.DateTimeFormat("pt-BR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
    second: "2-digit",
    hour12: false,
  }).format(date);
}

function removeAccents(str) {
  return str.normalize("NFD").replace(/[\u0300-\u036f]/g, "");
}
